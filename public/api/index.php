<?php

declare(strict_types=1);

require __DIR__ . '/../../src/Database.php';
require __DIR__ . '/../../src/parcels.php';

header('Content-Type: application/json; charset=utf-8');

/** Ukončí požadavek chybovou odpovědí v JSONu. */
function fail(int $status, string $message): never
{
    http_response_code($status);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Přečte parametr `bbox` ve tvaru "west,south,east,north".
 *
 * Pořadí je lon,lat — stejné, jaké používá GeoJSON i metoda toBBoxString()
 * v Leafletu, takže klient nemusí nic přehazovat. Kontroluje se počet hodnot,
 * jejich číselnost i to, že obdélník není naruby; chybný vstup vrací 400,
 * ne prázdný výsledek (tichá prázdná odpověď je přesně to, čím nás celou dobu
 * mátl server ČÚZK).
 */
function readBbox(string $value): array
{
    $parts = explode(',', $value);

    if (count($parts) !== 4) {
        fail(400, 'Parametr bbox musí mít tvar west,south,east,north.');
    }

    foreach ($parts as $part) {
        if (!is_numeric($part)) {
            fail(400, 'Parametr bbox smí obsahovat jen čísla.');
        }
    }

    [$west, $south, $east, $north] = array_map('floatval', $parts);

    if ($west >= $east || $south >= $north) {
        fail(400, 'Parametr bbox má převrácené hranice.');
    }

    return [$west, $south, $east, $north];
}

$route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

try {
    $pdo = openDatabase();

    switch ($route) {
        case '/api/parcels':
            $bbox = readBbox($_GET['bbox'] ?? fail(400, 'Chybí parametr bbox.'));
            echo parcelsInBbox($pdo, ...$bbox);
            break;

        case '/api/parcel':
            $ref = $_GET['ref'] ?? fail(400, 'Chybí parametr ref.');
            $parcel = parcelDetail($pdo, $ref);

            if ($parcel === null) {
                fail(404, "Parcela {$ref} nenalezena.");
            }

            echo json_encode($parcel, JSON_UNESCAPED_UNICODE);
            break;

        case '/api/zonings':
            echo json_encode(
                $pdo->query('SELECT code, name FROM zonings ORDER BY name')->fetchAll(),
                JSON_UNESCAPED_UNICODE
            );
            break;

        default:
            fail(404, 'Neznámý endpoint.');
    }
} catch (Throwable $e) {
    error_log((string) $e);
    fail(500, 'Chyba serveru.');
}
