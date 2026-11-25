<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Model\Data;

/**
 * Curated static VAT rates for the supported non-EU countries.
 *
 * vat-api.eu only covers the 27 EU member states, so these rates are maintained
 * here and appended by the live source as well as the static Fallback. They are
 * only imported when the "Include Non-EU Countries" setting is enabled — the
 * filtering happens downstream in TaxSyncService.
 *
 * Last reviewed: June 2026.
 *
 * @see EuVatReducedRates for the EU reduced-rate map.
 */
class NonEuVatRates
{
    /**
     * ISO-3166-1 alpha-2 => ['standard' => float, 'reduced' => float].
     *
     * @var array<string, array{standard: float, reduced: float}>
     */
    public const RATES = [
        'CH' => ['standard' => 8.10, 'reduced' => 2.60],   // Switzerland
        'GB' => ['standard' => 20.00, 'reduced' => 5.00],  // United Kingdom
        'NO' => ['standard' => 25.00, 'reduced' => 15.00], // Norway
    ];
}
