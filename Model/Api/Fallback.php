<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Model\Api;

use Storetown\TaxSync\Api\TaxRateSourceInterface;
use Storetown\TaxSync\Logger\Logger;

/**
 * Fallback with static EU VAT rates
 *
 * Last updated: January 2025
 *
 * Sources:
 * - European Commission VAT rates database
 * - https://ec.europa.eu/taxation_customs/tedb/
 */
class Fallback implements TaxRateSourceInterface
{
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
        $this->logger->info('Using fallback static rates (January 2025)');

        return [
            // EU Member States (alphabetical by country code)
            ['country' => 'AT', 'standard' => 20.00, 'reduced' => 10.00],  // Austria
            ['country' => 'BE', 'standard' => 21.00, 'reduced' => 6.00],   // Belgium
            ['country' => 'BG', 'standard' => 20.00, 'reduced' => 9.00],   // Bulgaria
            ['country' => 'CY', 'standard' => 19.00, 'reduced' => 5.00],   // Cyprus
            ['country' => 'CZ', 'standard' => 21.00, 'reduced' => 12.00],  // Czech Republic
            ['country' => 'DE', 'standard' => 19.00, 'reduced' => 7.00],   // Germany
            ['country' => 'DK', 'standard' => 25.00, 'reduced' => 0.00],   // Denmark (no reduced rate)
            ['country' => 'EE', 'standard' => 22.00, 'reduced' => 9.00],   // Estonia
            ['country' => 'ES', 'standard' => 21.00, 'reduced' => 10.00],  // Spain
            ['country' => 'FI', 'standard' => 25.50, 'reduced' => 14.00],  // Finland (increased 2024)
            ['country' => 'FR', 'standard' => 20.00, 'reduced' => 5.50],   // France
            ['country' => 'GR', 'standard' => 24.00, 'reduced' => 13.00],  // Greece
            ['country' => 'HR', 'standard' => 25.00, 'reduced' => 13.00],  // Croatia
            ['country' => 'HU', 'standard' => 27.00, 'reduced' => 18.00],  // Hungary
            ['country' => 'IE', 'standard' => 23.00, 'reduced' => 13.50],  // Ireland
            ['country' => 'IT', 'standard' => 22.00, 'reduced' => 10.00],  // Italy
            ['country' => 'LT', 'standard' => 21.00, 'reduced' => 9.00],   // Lithuania
            ['country' => 'LU', 'standard' => 17.00, 'reduced' => 8.00],   // Luxembourg
            ['country' => 'LV', 'standard' => 21.00, 'reduced' => 12.00],  // Latvia
            ['country' => 'MT', 'standard' => 18.00, 'reduced' => 7.00],   // Malta
            ['country' => 'NL', 'standard' => 21.00, 'reduced' => 9.00],   // Netherlands
            ['country' => 'PL', 'standard' => 23.00, 'reduced' => 8.00],   // Poland
            ['country' => 'PT', 'standard' => 23.00, 'reduced' => 13.00],  // Portugal
            ['country' => 'RO', 'standard' => 19.00, 'reduced' => 9.00],   // Romania
            ['country' => 'SE', 'standard' => 25.00, 'reduced' => 12.00],  // Sweden
            ['country' => 'SI', 'standard' => 22.00, 'reduced' => 9.50],   // Slovenia
            ['country' => 'SK', 'standard' => 23.00, 'reduced' => 10.00],  // Slovakia

            // Non-EU Countries
            ['country' => 'CH', 'standard' => 8.10, 'reduced' => 2.60],    // Switzerland (2024)
            ['country' => 'GB', 'standard' => 20.00, 'reduced' => 5.00],   // United Kingdom
            ['country' => 'NO', 'standard' => 25.00, 'reduced' => 15.00],  // Norway
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSourceName(): string
    {
        return 'Fallback (Static - Jan 2025)';
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return true; // Always available
    }
}
