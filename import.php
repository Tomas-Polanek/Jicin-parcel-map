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
try {
    foreach (ZONINGS as $code => $name) {
        $path = downloadDataset($code);
        echo "{$name}: {$path}", PHP_EOL;
    }
} catch (RuntimeException $e) {
    fwrite(STDERR, 'Chyba: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}