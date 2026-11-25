<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Model;

use Magento\Framework\FlagManager;

/**
 * Remembers which value the module wrote for each tax rate
 *
 * A rate whose value differs from the remembered one was changed by hand.
 */
class RateMemory
{
    /** @var string */
    private const FLAG_CODE = 'storetown_tax_sync_rates';

    /** @var float */
    private const TOLERANCE = 0.001;

    /** @var array<string, array{written?: float, reported?: float}>|null */
    private ?array $entries = null;

    /**
     * @param FlagManager $flagManager
     */
    public function __construct(
        private FlagManager $flagManager
    ) {
    }

    /**
     * Read the stored values again.
     *
     * @return void
     */
    public function load(): void
    {
        $data = $this->flagManager->getFlagData(self::FLAG_CODE);
        $this->entries = is_array($data) ? $data : [];
    }

    /**
     * Store the values.
     *
     * @return void
     */
    public function save(): void
    {
        if ($this->entries !== null) {
            $this->flagManager->saveFlag(self::FLAG_CODE, $this->entries);
        }
    }

    /**
     * Whether the rate no longer has the value the module gave it.
     *
     * A rate without a remembered value comes from a version before 1.5.3 and
     * counts as unchanged.
     *
     * @param string $code
     * @param float $currentRate
     * @return bool
     */
    public function isChangedByHand(string $code, float $currentRate): bool
    {
        $written = $this->entries[$code]['written'] ?? null;

        return $written !== null && abs($currentRate - (float) $written) > self::TOLERANCE;
    }

    /**
     * Remember the value the module wrote or confirmed.
     *
     * @param string $code
     * @param float $rate
     * @return void
     */
    public function remember(string $code, float $rate): void
    {
        $this->entries[$code] = ['written' => $rate];
    }

    /**
     * Whether the value of the data source is to be reported for a kept rate.
     *
     * True only once per value, so that a kept rate does not cause a message on every run.
     *
     * @param string $code
     * @param float $sourceRate
     * @return bool
     */
    public function shouldReport(string $code, float $sourceRate): bool
    {
        $reported = $this->entries[$code]['reported'] ?? null;
        if ($reported !== null && abs($sourceRate - (float) $reported) <= self::TOLERANCE) {
            return false;
        }
        $this->entries[$code]['reported'] = $sourceRate;

        return true;
    }
}
