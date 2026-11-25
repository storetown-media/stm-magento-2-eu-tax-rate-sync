<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Api;

interface TaxRateSourceInterface
{
    /**
     * Fetch VAT rates from external source
     *
     * @return array<int, array{country: string, standard: float, reduced: float}>
     */
    public function fetchRates(): array;

    /**
     * Get source name for logging
     */
    public function getSourceName(): string;

    /**
     * Check if source is available
     */
    public function isAvailable(): bool;
}
