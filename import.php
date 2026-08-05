<?php

declare(strict_types=1);

/**
 * Jednorázový import katastrálních dat z ČÚZK do lokální SQLite databáze.
 *
 * Stáhne předpřipravené GML sady (GetSpatialDataSet) pro 4 katastrální území,
 * rozbalí je, vyparsuje parcely a uloží je do db/parcels.sqlite.
 * Spouští se ručně z terminálu: php import.php
 */
const ZONINGS = [
    659541 => 'Jičín',
    725838 => 'Popovice u Jičína',
    740225 => 'Robousy',
    776530 => 'Valdice',
];

const CUZK_WFS_URL = 'https://services.cuzk.gov.cz/wfs/inspire-cpx-wfs.asp';

const CUZK_CODELIST_URL = 'https://services.cuzk.cz/registry/codelist/';

const DATABASE_PATH = 'db/parcels.sqlite';

/** Jmenné prostory potřebné pro čtení GML záznamu parcely. */
const XML_NAMESPACES = [
    'cp'     => 'http://inspire.ec.europa.eu/schemas/cp/4.0',
    'cp-ext' => 'http://services.cuzk.cz/xsd/inspire/cp-ext/4.0',
    'gml'    => 'http://www.opengis.net/gml/3.2',
    'xlink'  => 'http://www.w3.org/1999/xlink',
];

require __DIR__ . '/src/Parcel.php';

function buildDatasetUrl(int $zoningCode): string
{
    return CUZK_WFS_URL . '?' . http_build_query([
        'service'            => 'WFS',
        'version'            => '2.0.0',
        'request'            => 'GetFeature',
        'storedquery_id'     => 'http://inspire.ec.europa.eu/operation/download/GetSpatialDataSet',
        'CRS'                => 'http://www.opengis.net/def/crs/EPSG/0/4258',
        'DataSetIdNamespace' => 'CZ-00025712-CUZK_CPX',
        'DataSetIdCode'      => 'CPX.SD.' . $zoningCode,
        'Language'           => 'cze',
        'zipped'             => 'true',
    ]);
}

function downloadDataset(int $zoningCode): string
{
    if (!is_dir('data')) {
        mkdir('data');
    }

    $path = 'data/' . $zoningCode . '.zip';

    if (file_exists($path)) {
        return $path;
    }

    $data = file_get_contents(buildDatasetUrl($zoningCode));

    if ($data === false) {
        throw new RuntimeException("Stažení k.ú. {$zoningCode} se nezdařilo.");
    }

    if (substr($data, 0, 2) !== 'PK') {
        throw new RuntimeException(
            "ČÚZK nevrátil ZIP pro k.ú. {$zoningCode}: " . substr($data, 0, 200)
        );
    }

    file_put_contents($path, $data);

    return $path;
}
/**
 * Převede jeden <gml:posList> na prstenec bodů ve tvaru [lon, lat].
 *
 * Zdroj má souřadnice v pořadí "lat lon lat lon ..." (EPSG:4258), GeoJSON je
 * chce naopak jako [lon, lat] — proto se dvojice prohazuje. Je to přesně ta
 * past, na kterou tenhle server chytil projekt už třikrát (viz docs/roadmap.md).
 */
function posListToRing(string $posList): array
{
    $numbers = preg_split('/\s+/', trim($posList));
    $ring    = [];

    for ($i = 0; $i + 1 < count($numbers); $i += 2) {
        $ring[] = [(float) $numbers[$i + 1], (float) $numbers[$i]];
    }

    return $ring;
}

/**
 * Sestaví z <gml:Polygon> pole prstenců pro GeoJSON.
 *
 * První prstenec je vnější obrys, každý další je díra (<gml:interior>) — přesně
 * v tomto pořadí je očekává GeoJSON. Díru má zhruba desetina parcel (ověřeno:
 * 103 z 1053 ve Valdicích), takže je to běžný případ, ne výjimka.
 */
function polygonToRings(SimpleXMLElement $polygon): array
{
    $rings = [];

    foreach ($polygon->xpath('gml:exterior/gml:LinearRing/gml:posList') as $posList) {
        $rings[] = posListToRing((string) $posList);
    }

    foreach ($polygon->xpath('gml:interior/gml:LinearRing/gml:posList') as $posList) {
        $rings[] = posListToRing((string) $posList);
    }

    if ($rings === []) {
        throw new RuntimeException('Polygon bez jediného prstence souřadnic.');
    }

    return $rings;
}

/**
 * Spočítá obalový obdélník ze všech bodů vnějšího prstence.
 *
 * Stačí vnější prstenec — díry leží uvnitř něj, takže hranice posunout nemohou.
 * Vrací [min_lon, min_lat, max_lon, max_lat]; tyto čtyři hodnoty nahrazují
 * prostorový index R-Tree, který ve zdejším buildu SQLite chybí.
 */
function ringBoundingBox(array $ring): array
{
    $lons = array_column($ring, 0);
    $lats = array_column($ring, 1);

    return [min($lons), min($lats), max($lons), max($lats)];
}

/**
 * Otevře GML soubor uvnitř staženého ZIPu, aniž by se archiv rozbaloval na disk.
 *
 * Wrapper zip:// čte přímo z archivu, takže si ušetříme ~318 MB dočasných
 * souborů a jejich úklid. Jméno vnitřního souboru se čte z archivu, ne odhaduje
 * — je to '<kód>.xml', ne '.gml', jak by člověk u GML dat čekal.
 */
function openDataset(string $zipPath): XMLReader
{
    $archive = new ZipArchive();

    if ($archive->open($zipPath) !== true) {
        throw new RuntimeException("Archiv {$zipPath} se nepodařilo otevřít.");
    }

    $entry = $archive->getNameIndex(0);
    $archive->close();

    if ($entry === false) {
        throw new RuntimeException("Archiv {$zipPath} je prázdný.");
    }

    $reader = new XMLReader();

    if (!$reader->open('zip://' . realpath($zipPath) . '#' . $entry)) {
        throw new RuntimeException("Soubor {$entry} v {$zipPath} nejde číst.");
    }

    return $reader;
}

/**
 * Vytáhne z GML záznamu hodnotu atributu xlink:href a z ní poslední část URL.
 *
 * Číselníkové hodnoty nejsou v datech uvedeny textem, ale odkazem, například
 * ".../LandTypeValue/BuiltUpArea" — nás zajímá jen koncové 'BuiltUpArea',
 * český popisek se k němu připojí až z registru číselníků.
 */
function codeFromXlink(SimpleXMLElement $parcel, string $element): ?string
{
    $nodes = $parcel->xpath($element);

    if ($nodes === false || $nodes === []) {
        return null;
    }

    $href = (string) $nodes[0]->attributes(XML_NAMESPACES['xlink'])['href'];

    return $href === '' ? null : basename($href);
}

/**
 * Prochází dataset a postupně vydává jednotlivé parcely.
 *
 * Je to generátor (`yield`), ne pole — v paměti je vždy jen jedna parcela,
 * takže Jičín s 12 284 parcelami zabere stejně málo jako Valdice s 1 053.
 * Ostatní prvky v souboru (budovy, hranice, geodetické body) se přeskakují;
 * u Valdic je parcel jen 1 053 z 8 511 prvků.
 */
function parseParcels(XMLReader $reader, int $zoningCode): Generator
{
    while ($reader->read()) {
        if ($reader->nodeType !== XMLReader::ELEMENT
            || $reader->name !== 'cp-ext:CadastralParcel') {
            continue;
        }

        $parcel = new SimpleXMLElement($reader->readOuterXml());

        foreach (XML_NAMESPACES as $prefix => $uri) {
            $parcel->registerXPathNamespace($prefix, $uri);
        }

        $reference = (string) $parcel->xpath('cp:nationalCadastralReference')[0];
        $polygons  = $parcel->xpath('cp:geometry/gml:Polygon');

        if ($polygons === []) {
            throw new RuntimeException("Parcela {$reference} nemá geometrii.");
        }

        $rings = polygonToRings($polygons[0]);
        [$minLon, $minLat, $maxLon, $maxLat] = ringBoundingBox($rings[0]);

        $areaValue = $parcel->xpath('cp:areaValue');

        yield new Parcel(
            ref:          $reference,
            zoningCode:   $zoningCode,
            parcelNumber: (string) $parcel->xpath('cp:label')[0],
            areaM2:       $areaValue === [] ? null : (int) (string) $areaValue[0],
            landType:     codeFromXlink($parcel, 'cp-ext:landType'),
            landUse:      codeFromXlink($parcel, 'cp-ext:landUse'),
            geometry:     json_encode(['type' => 'Polygon', 'coordinates' => $rings]),
            minLon:       $minLon,
            minLat:       $minLat,
            maxLon:       $maxLon,
            maxLat:       $maxLat,
        );
    }
}

/**
 * Stáhne z registru ČÚZK český číselník a vrátí ho jako kód => [popisek, definice].
 *
 * Registr se řídí výhradně parametrem ?format=json — přípona .json ani hlavička
 * Accept na něj nemají vliv a vrátil by HTML. Popisky se nepřekládají u nás,
 * přebírá se úřední znění ČÚZK.
 */
function downloadCodelist(string $codelist): array
{
    $url = CUZK_CODELIST_URL . $codelist . 'Value?format=json';
    $raw = @file_get_contents($url);

    if ($raw === false) {
        throw new RuntimeException("Číselník {$codelist} se nepodařilo stáhnout z {$url}.");
    }

    $items = json_decode($raw, true)['codelist']['containeditems'] ?? null;

    if (!is_array($items) || $items === []) {
        throw new RuntimeException("Číselník {$codelist} má neočekávaný tvar.");
    }

    $values = [];

    foreach ($items as $item) {
        $values[basename($item['value']['id'])] = [
            $item['value']['label']['text'],
            $item['value']['definition']['text'] ?? null,
        ];
    }

    return $values;
}

/**
 * Vytvoří prázdnou databázi se schématem podle docs/roadmap.md.
 *
 * Existující soubor se maže — import je jednorázový a opakovaný běh má dát
 * stejný výsledek, ne přírůstek nad starými daty.
 */
function createDatabase(string $path): PDO
{
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }

    if (file_exists($path)) {
        unlink($path);
    }

    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec('
        CREATE TABLE zonings (
            code INTEGER PRIMARY KEY,
            name TEXT NOT NULL
        );

        CREATE TABLE codelist_values (
            codelist   TEXT NOT NULL,
            code       TEXT NOT NULL,
            label      TEXT NOT NULL,
            definition TEXT,
            PRIMARY KEY (codelist, code)
        );

        CREATE TABLE parcels (
            ref           TEXT PRIMARY KEY,
            zoning_code   INTEGER NOT NULL REFERENCES zonings(code),
            parcel_number TEXT NOT NULL,
            area_m2       INTEGER,
            land_type     TEXT,
            land_use      TEXT,
            geometry      TEXT NOT NULL,
            min_lon       REAL NOT NULL,
            min_lat       REAL NOT NULL,
            max_lon       REAL NOT NULL,
            max_lat       REAL NOT NULL
        );

        CREATE INDEX ix_parcels_bbox   ON parcels(min_lon, max_lon, min_lat, max_lat);
        CREATE INDEX ix_parcels_zoning ON parcels(zoning_code);
    ');

    return $pdo;
}

/**
 * Uloží parcely jednoho katastrálního území a vrátí jejich počet.
 *
 * Vše v jedné transakci na území — bez ní SQLite potvrzuje každý řádek zvlášť,
 * což je u 17 256 parcel rozdíl mezi sekundami a minutami. Dělení po územích
 * (ne jedna transakce přes celý import) znamená, že chyba u posledního území
 * nezahodí práci odvedenou na předchozích.
 */
function storeParcels(PDO $pdo, XMLReader $reader, int $zoningCode): int
{
    $insert = $pdo->prepare('
        INSERT INTO parcels
            (ref, zoning_code, parcel_number, area_m2, land_type, land_use,
             geometry, min_lon, min_lat, max_lon, max_lat)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');

    $pdo->beginTransaction();

    try {
        $count = 0;

        foreach (parseParcels($reader, $zoningCode) as $parcel) {
            $insert->execute([
                $parcel->ref,
                $parcel->zoningCode,
                $parcel->parcelNumber,
                $parcel->areaM2,
                $parcel->landType,
                $parcel->landUse,
                $parcel->geometry,
                $parcel->minLon,
                $parcel->minLat,
                $parcel->maxLon,
                $parcel->maxLat,
            ]);
            $count++;
        }

        $pdo->commit();

        return $count;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

try {
    $pdo = createDatabase(DATABASE_PATH);

    $insertZoning = $pdo->prepare('INSERT INTO zonings (code, name) VALUES (?, ?)');

    foreach (ZONINGS as $code => $name) {
        $insertZoning->execute([$code, $name]);
    }

    $insertValue = $pdo->prepare('
        INSERT INTO codelist_values (codelist, code, label, definition) VALUES (?, ?, ?, ?)
    ');

    foreach (['LandType', 'LandUse'] as $codelist) {
        $values = downloadCodelist($codelist);

        foreach ($values as $code => [$label, $definition]) {
            $insertValue->execute([$codelist, $code, $label, $definition]);
        }

        echo "Číselník {$codelist}: " . count($values) . " hodnot", PHP_EOL;
    }

    foreach (ZONINGS as $code => $name) {
        $reader = openDataset(downloadDataset($code));
        $count  = storeParcels($pdo, $reader, $code);
        $reader->close();

        echo "{$name}: {$count} parcel", PHP_EOL;
    }

    echo 'Hotovo: ' . DATABASE_PATH . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, 'Chyba: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}