<?php

declare(strict_types=1);

/**
 * Největší výřez, pro který se ještě posílají parcely, ve čtverečních stupních.
 *
 * Nad touto plochou se vrátí prázdný seznam s příznakem `zoom_in` a mapa
 * uživatele vyzve, ať přiblíží. Hodnota je doladěná měřením celého řetězce
 * v prohlížeči (stažení + JSON.parse + vykreslení), ne odhadnutá:
 *
 *   plocha    šířka     parcel   odpověď   celkem
 *   2,0e-4    1,3 km     1 331   0,55 MB     46 ms
 *   1,0e-3    2,9 km     5 669   2,36 MB    209 ms   <- zvolený limit
 *   2,0e-3    4,1 km    11 121   4,58 MB    350 ms
 *   1,35e-2  10,7 km    17 256   6,75 MB    559 ms   (celý rozsah dat)
 *
 * Zvoleno 1,0e-3, protože pauza kolem 200 ms po dotažení pohybu ještě splyne
 * s pohybem samotným, kdežto půl sekundy už je vidět. Měřeno na rychlém stroji,
 * takže je v hodnotě schválně rezerva — na pomalejším počítači recenzenta
 * poroste celý řetězec úměrně.
 *
 * Limit se nestal zbytečným ani po nasazení canvas rendereru na klientovi:
 * drží aplikaci plynulou i kdyby přibyla další katastrální území, kde by počet
 * parcel rostl, aniž by se cokoliv jiného změnilo.
 */
const MAX_BBOX_AREA = 0.001;

/**
 * Vrátí parcely v zadaném výřezu jako GeoJSON FeatureCollection.
 *
 * Odpověď se skládá spojováním řetězců, ne přes json_encode() celého pole:
 * geometrie je v databázi už uložená jako hotový GeoJSON, takže by ji
 * json_encode() musel nejdřív rozebrat a znovu složit. Takto je sestavení
 * 17 000 prvků otázkou jednotek milisekund.
 *
 * Vlastnosti (`properties`) zůstávají prázdné záměrně — mapa potřebuje jen
 * tvar a identifikátor, popisky se dotahují až kliknutím přes parcelDetail().
 */
function parcelsInBbox(PDO $pdo, float $west, float $south, float $east, float $north): string
{
    if (($east - $west) * ($north - $south) > MAX_BBOX_AREA) {
        return '{"type":"FeatureCollection","zoom_in":true,"features":[]}';
    }

    $statement = $pdo->prepare('
        SELECT ref, geometry
        FROM parcels
        WHERE min_lon < :east AND max_lon > :west
          AND min_lat < :north AND max_lat > :south
    ');

    $statement->execute([
        'west' => $west, 'south' => $south, 'east' => $east, 'north' => $north,
    ]);

    $features = [];

    foreach ($statement as $row) {
        $features[] = '{"type":"Feature","id":' . json_encode($row['ref'])
            . ',"geometry":' . $row['geometry'] . ',"properties":{}}';
    }

    return '{"type":"FeatureCollection","zoom_in":false,"features":['
        . implode(',', $features) . ']}';
}

/**
 * Vrátí údaje o jedné parcele pro informační panel, nebo null když neexistuje.
 *
 * Číselníky se připojují LEFT JOINem — druh pozemku i způsob využití mohou
 * v datech chybět (ČÚZK je u části parcel nevede) a taková parcela musí přesto
 * jít zobrazit. Vlastník mezi údaji není: ve svobodných datech ČÚZK není
 * dostupný, viz vědomé rozhodnutí v docs/plan.md.
 */
function parcelDetail(PDO $pdo, string $ref): ?array
{
    $statement = $pdo->prepare('
        SELECT p.ref,
               p.parcel_number,
               p.area_m2,
               z.name       AS zoning_name,
               land_type.label AS land_type_label,
               land_use.label  AS land_use_label,
               land_type.definition AS land_type_definition
        FROM parcels p
        JOIN zonings z ON z.code = p.zoning_code
        LEFT JOIN codelist_values land_type
               ON land_type.codelist = \'LandType\' AND land_type.code = p.land_type
        LEFT JOIN codelist_values land_use
               ON land_use.codelist = \'LandUse\'  AND land_use.code = p.land_use
        WHERE p.ref = :ref
    ');

    $statement->execute(['ref' => $ref]);

    return $statement->fetch() ?: null;
}
