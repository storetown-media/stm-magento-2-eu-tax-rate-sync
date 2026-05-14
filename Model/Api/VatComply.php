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
 * VatComply API - DEPRECATED
 *
 * Note: VatComply.com no longer provides a VAT rates endpoint.
 * The /rates endpoint only returns currency exchange rates.
 * This class is kept for backwards compatibility but will always
 * return isAvailable() = false, falling through to EuVatRates or Fallback.
 */
class VatComply implements TaxRateSourceInterface
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
        $this->logger->warning('VatComply API no longer provides VAT rates - use EuVatRates or Fallback');
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getSourceName(): string
    {
        return 'VatComply API (Deprecated)';
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        // Always return false - this API no longer provides VAT rates
        return false;
    }
}
