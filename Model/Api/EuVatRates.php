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
 * EU VAT Rates API source
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
        try {
            $this->curl->setTimeout(5);
            $this->curl->get(self::API_URL);
            return $this->curl->getStatus() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
}
