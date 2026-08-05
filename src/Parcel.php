<?php

declare(strict_types=1);

/**
 * Jedna parcela připravená k zápisu do tabulky `parcels`.
 *
 * Vlastnosti odpovídají 1:1 sloupcům tabulky (viz schéma v docs/roadmap.md).
 * Objekt je neměnný — jakmile parser parcelu sestaví, nikdo ji už nepřepíše.
 */
final class Parcel
{
    public function __construct(
        public readonly string  $ref,           // '776530-st. 96/7'
        public readonly int     $zoningCode,    // 776530
        public readonly string  $parcelNumber,  // 'st. 96/7'
        public readonly ?int    $areaM2,        // 19
        public readonly ?string $landType,      // 'BuiltUpArea'
        public readonly ?string $landUse,       // 'Road' | null
        public readonly string  $geometry,      // hotový GeoJSON, ne pole
        public readonly float   $minLon,
        public readonly float   $minLat,
        public readonly float   $maxLon,
        public readonly float   $maxLat,
    ) {
    }
}
