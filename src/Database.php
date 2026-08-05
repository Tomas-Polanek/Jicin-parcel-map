<?php

declare(strict_types=1);

/**
 * Otevře databázi vytvořenou skriptem import.php.
 *
 * Aplikace do databáze nikdy nezapisuje — soubor je výsledek importu, ne
 * živé úložiště. Cesta se skládá z __DIR__, aby fungovala nezávisle na tom,
 * z jaké složky server běží.
 */
function openDatabase(): PDO
{
    $path = __DIR__ . '/../db/parcels.sqlite';

    if (!file_exists($path)) {
        throw new RuntimeException('Databáze neexistuje, spusťte nejdřív: php import.php');
    }

    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
}
