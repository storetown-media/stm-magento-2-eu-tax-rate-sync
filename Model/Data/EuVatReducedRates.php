<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Model\Data;

/**
 * Curated principal reduced VAT rate per EU member state.
 *
 * Single source of truth shared by the live API source (VatApiEu) and the
 * static Fallback, so the two can never drift apart.
 *
 * Why curated and not taken from the API: most member states apply SEVERAL
 * reduced rates (super-reduced, reduced, intermediate/parking). The module
 * models exactly ONE reduced rate per country, and the rate type is not
 * machine-readable in the upstream feeds. These values are the principal
 * reduced rate applied to the broadest set of everyday reduced-category goods
 * (books, medicines, basic foodstuffs). For multi-rate countries this is a
 * deliberate judgement call — merchants can adjust the created rate in
 * Stores → Tax Zones and Rates.
 *
 * Last reviewed: June 2026.
 * Sources: European Commission VAT rates database (TEDB) and national reforms
 * (e.g. RO 5/9 % → 11 % from 2025-08-01; SK old 10 % abolished, 5 % retained).
 */
final class EuVatReducedRates
{
    /**
     * ISO-3166-1 alpha-2 country code => principal reduced rate (percent).
     * 0.0 means the country has no reduced rate (e.g. Denmark).
     *
     * @var array<string, float>
     */
    public const MAP = [
        'AT' => 10.0,  // Austria (also 13 % special)
        'BE' => 6.0,   // Belgium (also 12 % intermediate)
        'BG' => 9.0,   // Bulgaria
        'CY' => 5.0,   // Cyprus (also 9 %, 3 % super-reduced)
        'CZ' => 12.0,  // Czechia (single reduced rate since 2024)
        'DE' => 7.0,   // Germany
        'DK' => 0.0,   // Denmark (no reduced rate)
        'EE' => 9.0,   // Estonia (also 13 % accommodation)
        'ES' => 10.0,  // Spain (also 4 % super-reduced)
        'FI' => 14.0,  // Finland (also 10 %) — upstream feeds sometimes report 13.5 % in error
        'FR' => 5.5,   // France (also 10 % intermediate, 2.1 % super-reduced)
        'GR' => 13.0,  // Greece (also 6 %)
        'HR' => 13.0,  // Croatia (also 5 %)
        'HU' => 18.0,  // Hungary (also 5 %)
        'IE' => 13.5,  // Ireland (also 9 %, 4.8 % super-reduced)
        'IT' => 10.0,  // Italy (also 5 %, 4 % super-reduced)
        'LT' => 9.0,   // Lithuania (also 5 %)
        'LU' => 8.0,   // Luxembourg (also 3 % super-reduced, 14 % parking)
        'LV' => 12.0,  // Latvia (also 5 %)
        'MT' => 7.0,   // Malta (also 5 %, 12 %)
        'NL' => 9.0,   // Netherlands
        'PL' => 8.0,   // Poland (also 5 %)
        'PT' => 13.0,  // Portugal (also 6 %)
        'RO' => 11.0,  // Romania (5/9 % merged into 11 % from 2025-08-01)
        'SE' => 12.0,  // Sweden (also 6 %)
        'SI' => 9.5,   // Slovenia (also 5 %)
        'SK' => 5.0,   // Slovakia (old 10 % abolished 2025; 5 % for food/books/medicines, 19 % otherwise)
    ];

    /**
     * Principal reduced rate for a country code, or 0.0 if unknown / none.
     *
     * @param string $countryCode ISO-3166-1 alpha-2
     * @return float
     */
    public static function forCountry(string $countryCode): float
    {
        return self::MAP[strtoupper($countryCode)] ?? 0.0;
    }
}
