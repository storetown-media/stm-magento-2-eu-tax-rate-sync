<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Model;

use Storetown\TaxSync\Api\TaxRateSourceInterface;
use Storetown\TaxSync\Helper\Config;
use Storetown\TaxSync\Logger\Logger;

/**
 * Pool of available tax rate API sources
 */
class ApiSourcePool
{
    /**
     * @param Config $config
     * @param Logger $logger
     * @param array $sources
     */
    public function __construct(
        private Config $config,
        private Logger $logger,
        private array $sources = []
    ) {
    }

    /**
     * Get the best available tax rate source.
     *
     * @return TaxRateSourceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSource(): TaxRateSourceInterface
    {
        $preferredSource = $this->config->getApiSource();

        // Try preferred source first
        if (isset($this->sources[$preferredSource]) && $this->sources[$preferredSource]->isAvailable()) {
            $this->logger->info("Using preferred source: {$preferredSource}");
            return $this->sources[$preferredSource];
        }

        // Try other sources
        foreach ($this->sources as $name => $source) {
            if ($name === 'fallback') {
                continue; // Skip fallback for now
            }

            if ($source->isAvailable()) {
                $this->logger->info("Using alternative source: {$name}");
                return $source;
            }
        }

        // Use fallback (with defensive check)
        if (!isset($this->sources['fallback'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('No API source available and no fallback configured')
            );
        }

        $this->logger->warning('All API sources unavailable, using fallback');
        return $this->sources['fallback'];
    }

    /**
     * Get all registered sources.
     *
     * @return array
     */
    public function getAllSources(): array
    {
        return $this->sources;
    }
}
