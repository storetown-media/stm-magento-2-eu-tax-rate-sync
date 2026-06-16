<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Model\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Phrase;
use Storetown\TaxSync\Api\TaxRateSourceInterface;
use Storetown\TaxSync\Logger\Logger;

/**
 * EU VAT Rates API source — DEPRECATED.
 *
 * euvatrates.com is no longer maintained: its rates.json reports
 * "last_updated":"2016-01-01" and serves outdated rates (e.g. RO 20 % instead
 * of the current 21 %). Critically, the endpoint still answers with HTTP 200
 * and structurally valid JSON, so a naive availability probe would happily
 * select it and never fall through to a fresh source.
 *
 * To close that trap this source is now permanently reported as unavailable;
 * the pool uses {@see VatApiEu} or the static Fallback instead. The class is
 * retained for backwards compatibility with stored `api_source` config values.
 */
class EuVatRates implements TaxRateSourceInterface
{
    /** @var string */
    private const API_URL = 'https://euvatrates.com/rates.json';

    /**
     * @param Curl $curl
     * @param Logger $logger
     */
    public function __construct(
        private Curl $curl,
        private Logger $logger
    ) {
    }

    /**
     * @inheritdoc
     */
    public function fetchRates(): array
    {
        $this->curl->setTimeout(30);
        $this->curl->get(self::API_URL);

        $response = $this->curl->getBody();
        $data = json_decode($response, true);

        if (!$data || !isset($data['rates'])) {
            throw new LocalizedException(
                new Phrase('Invalid API response from euvatrates.com')
            );
        }

        $rates = [];
        foreach ($data['rates'] as $country => $rateData) {
            // Handle country code mapping (EL = GR for Greece)
            $countryCode = $country;
            if ($country === 'EL') {
                $countryCode = 'GR';
            }

            // Skip duplicate entries
            if (isset($rateData['iso_duplicate'])) {
                continue;
            }

            $standardRate = (float) ($rateData['standard_rate'] ?? 0);
            // reduced_rate can be false in some countries (e.g. Denmark)
            $reducedRate = $rateData['reduced_rate'] !== false
                ? (float) ($rateData['reduced_rate'] ?? 0)
                : 0.0;

            $rates[] = [
                'country' => $countryCode,
                'standard' => $standardRate,
                'reduced' => $reducedRate,
            ];
        }

        $this->logger->info('EuVatRates: Fetched ' . count($rates) . ' rates');

        return $rates;
    }

    /**
     * @inheritdoc
     */
    public function getSourceName(): string
    {
        return 'EU VAT Rates API';
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        // Permanently unavailable: the upstream feed is frozen at 2016 data but
        // still returns HTTP 200, so we must not let it be selected.
        return false;
    }
}
