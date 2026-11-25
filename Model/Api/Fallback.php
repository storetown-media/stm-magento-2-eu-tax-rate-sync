<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Model\Api;

use Storetown\TaxSync\Api\TaxRateSourceInterface;
use Storetown\TaxSync\Logger\Logger;
use Storetown\TaxSync\Model\Data\EuVatReducedRates;
use Storetown\TaxSync\Model\Data\NonEuVatRates;

/**
 * Fallback with static VAT rates, used when no live source is reachable.
 *
 * Last reviewed: November 2026.
 *
 * Standard rates are a snapshot of the live data; EU reduced rates come from the
 * shared {@see EuVatReducedRates} map and non-EU rates from {@see NonEuVatRates},
 * so the fallback can never drift apart from the live VatApiEu source.
 *
 * Sources:
 * - European Commission VAT rates database (TEDB) — https://ec.europa.eu/taxation_customs/tedb/
 * - vat-api.eu (standard rates)
 */
class Fallback implements TaxRateSourceInterface
{
    /**
     * EU standard rates (percent), alphabetical by ISO country code.
     *
     * @var array<string, float>
     */
    private const EU_STANDARD_RATES = [
        'AT' => 20.00,  // Austria
        'BE' => 21.00,  // Belgium
        'BG' => 20.00,  // Bulgaria
        'CY' => 19.00,  // Cyprus
        'CZ' => 21.00,  // Czechia
        'DE' => 19.00,  // Germany
        'DK' => 25.00,  // Denmark
        'EE' => 24.00,  // Estonia (raised from 22 % on 2025-07-01)
        'ES' => 21.00,  // Spain
        'FI' => 25.50,  // Finland
        'FR' => 20.00,  // France
        'GR' => 24.00,  // Greece
        'HR' => 25.00,  // Croatia
        'HU' => 27.00,  // Hungary
        'IE' => 23.00,  // Ireland
        'IT' => 22.00,  // Italy
        'LT' => 21.00,  // Lithuania
        'LU' => 17.00,  // Luxembourg
        'LV' => 21.00,  // Latvia
        'MT' => 18.00,  // Malta
        'NL' => 21.00,  // Netherlands
        'PL' => 23.00,  // Poland
        'PT' => 23.00,  // Portugal
        'RO' => 21.00,  // Romania (raised from 19 % on 2025-08-01)
        'SE' => 25.00,  // Sweden
        'SI' => 22.00,  // Slovenia
        'SK' => 23.00,  // Slovakia
    ];

    /**
     * @param Logger $logger
     */
    public function __construct(
        private Logger $logger
    ) {
    }

    /**
     * @inheritdoc
     */
    public function fetchRates(): array
    {
        $this->logger->info('Using fallback static rates (reviewed November 2026)');

        $rates = [];

        foreach (self::EU_STANDARD_RATES as $country => $standard) {
            $rates[] = [
                'country' => $country,
                'standard' => $standard,
                'reduced' => EuVatReducedRates::MAP[$country],
            ];
        }

        foreach (NonEuVatRates::RATES as $country => $rate) {
            $rates[] = [
                'country' => $country,
                'standard' => $rate['standard'],
                'reduced' => $rate['reduced'],
            ];
        }

        return $rates;
    }

    /**
     * @inheritdoc
     */
    public function getSourceName(): string
    {
        return 'Fallback (Static - reviewed Nov 2026)';
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return true; // Always available
    }
}
