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
use Storetown\TaxSync\Model\Data\EuVatReducedRates;
use Storetown\TaxSync\Model\Data\NonEuVatRates;

/**
 * VAT-API.eu source — actively maintained, free, no API key.
 *
 * Endpoint returns a JSON array of all 27 EU member states:
 *   [{"country":"Romania","country_code":"RO","standard_rate":21,
 *     "reduced_rates":[0,11],"currency":"EUR"}, ...]
 *
 * Strategy (hybrid):
 * - standard rate: taken live from the API (this is the legally critical,
 *   frequently changing value and is reliable upstream).
 * - reduced rate: taken from the curated {@see EuVatReducedRates} map, because
 *   the API exposes several reduced rates per country without a type marker.
 */
class VatApiEu implements TaxRateSourceInterface
{
    /** @var string */
    private const API_URL = 'https://vat-api.eu/api/v1/rates';

    /** Minimum number of countries expected in a healthy response. */
    private const MIN_EXPECTED_COUNTRIES = 20;

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

        $data = json_decode($this->curl->getBody(), true);

        if (!is_array($data) || $data === []) {
            throw new LocalizedException(
                new Phrase('Invalid API response from vat-api.eu')
            );
        }

        $rates = [];
        foreach ($data as $entry) {
            if (!is_array($entry) || !isset($entry['country_code'], $entry['standard_rate'])) {
                continue;
            }

            // Defensive country-code normalisation (EL is sometimes used for Greece).
            $countryCode = strtoupper((string) $entry['country_code']);
            if ($countryCode === 'EL') {
                $countryCode = 'GR';
            }

            $standardRate = (float) $entry['standard_rate'];
            if ($standardRate <= 0) {
                // Never import a 0 % standard rate — skip and let the operator notice.
                $this->logger->warning("VatApiEu: skipping {$countryCode} (invalid standard rate)");
                continue;
            }

            $reducedRate = EuVatReducedRates::MAP[$countryCode] ?? 0.0;
            if (!array_key_exists($countryCode, EuVatReducedRates::MAP)) {
                $this->logger->warning(
                    "VatApiEu: no curated reduced rate for {$countryCode}, importing standard only"
                );
            }

            $rates[] = [
                'country' => $countryCode,
                'standard' => $standardRate,
                'reduced' => $reducedRate,
            ];
        }

        // Guard against a degraded-but-HTTP-200 response serving partial data.
        // Applies to the live EU set only; non-EU rates are static and added below.
        if (count($rates) < self::MIN_EXPECTED_COUNTRIES) {
            throw new LocalizedException(
                new Phrase(
                    'vat-api.eu returned only %1 usable rates, expected at least %2.',
                    [count($rates), self::MIN_EXPECTED_COUNTRIES]
                )
            );
        }

        $this->logger->info('VatApiEu: Fetched ' . count($rates) . ' EU rates');

        // vat-api.eu is EU-only; append the supported non-EU countries from
        // curated static data. The "Include Non-EU" setting is applied downstream.
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
        return 'VAT-API.eu';
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
