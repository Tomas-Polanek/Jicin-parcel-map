<?php

declare(strict_types=1);

/**
 * Směrovač pro vestavěný vývojový server PHP.
 *
 * Spouští se takto:  php -S localhost:8000 -t public public/router.php
 *
 * Vestavěný server umí sám poslat jen soubory, které na disku existují.
 * Adresa /api/parcels žádnému souboru neodpovídá, takže by skončila 404 —
 * tenhle skript ji proto předá api/index.php. Vrácené `false` znamená
 * "obsluž si to sám", takže statické soubory (HTML, JS, CSS) zůstávají
 * plně na serveru a neprotékají přes PHP.
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (str_starts_with($path, '/api/')) {
    require __DIR__ . '/api/index.php';
    return true;
}

return false;
