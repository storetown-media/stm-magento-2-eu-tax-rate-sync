<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Tax\Api\Data\TaxClassInterfaceFactory;
use Magento\Tax\Api\Data\TaxRateInterfaceFactory;
use Magento\Tax\Api\Data\TaxRuleInterfaceFactory;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Model\ResourceModel\Calculation\Rate\CollectionFactory as RateCollectionFactory;
use Magento\Tax\Model\ResourceModel\Calculation\Rule\CollectionFactory as RuleCollectionFactory;
use Storetown\TaxSync\Helper\Config;
use Storetown\TaxSync\Logger\Logger;

/**
 * Main service for synchronizing EU VAT tax rates
 */
class TaxSyncService
{
    /** @var string */
    private const LOCK_NAME = 'storetown_tax_sync';

    /** @var int */
    private const LOCK_TIMEOUT = 300;

    /** @var array */
    private array $changes = [];

    /** @var array */
    private array $ruleChanges = [];

    /** @var int[]|null */
    private ?array $customerTaxClassIds = null;

    /** @var array */
    private array $productTaxClassIdsByType = [];

    /**
     * @param Config $config
     * @param ApiSourcePool $apiSourcePool
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param TaxRuleRepositoryInterface $taxRuleRepository
     * @param TaxRateInterfaceFactory $taxRateFactory
     * @param TaxRuleInterfaceFactory $taxRuleFactory
     * @param RateCollectionFactory $rateCollectionFactory
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param TaxClassRepositoryInterface $taxClassRepository
     * @param TaxClassInterfaceFactory $taxClassDataFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param NotificationService $notificationService
     * @param WriterInterface $configWriter
     * @param DateTime $dateTime
     * @param LockManagerInterface $lockManager
     * @param Logger $logger
     */
    public function __construct(
        private Config $config,
        private ApiSourcePool $apiSourcePool,
        private TaxRateRepositoryInterface $taxRateRepository,
        private TaxRuleRepositoryInterface $taxRuleRepository,
        private TaxRateInterfaceFactory $taxRateFactory,
        private TaxRuleInterfaceFactory $taxRuleFactory,
        private RateCollectionFactory $rateCollectionFactory,
        private RuleCollectionFactory $ruleCollectionFactory,
        private TaxClassRepositoryInterface $taxClassRepository,
        private TaxClassInterfaceFactory $taxClassDataFactory,
        private SearchCriteriaBuilder $searchCriteriaBuilder,
        private NotificationService $notificationService,
        private WriterInterface $configWriter,
        private DateTime $dateTime,
        private LockManagerInterface $lockManager,
        private Logger $logger
    ) {
    }

    /**
     * Synchronize VAT rates from the configured API source.
     *
     * @param bool $forceUpdate
     * @return array
     */
    public function sync(bool $forceUpdate = false): array
    {
        $this->changes = [];
        $this->ruleChanges = [];
        $result = [
            'success' => false,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'rules_created' => 0,
            'rules_skipped' => 0,
            'changes' => [],
            'source' => '',
        ];

        if (!$this->lockManager->lock(self::LOCK_NAME, self::LOCK_TIMEOUT)) {
            $this->logger->warning('Sync already in progress, skipping');
            $result['errors']++;
            return $result;
        }

        try {
            $source = $this->apiSourcePool->getSource();
            $result['source'] = $source->getSourceName();

            $rates = $source->fetchRates();

            if (empty($rates)) {
                throw new LocalizedException(
                    new Phrase('No rates received from API')
                );
            }

            $allowedCountries = $this->config->getAllCountries();
            $createRules = $this->config->isCreateRulesEnabled();

            foreach ($rates as $rateData) {
                if (!in_array($rateData['country'], $allowedCountries)) {
                    continue;
                }

                // Standard rate
                $standardRateResult = $this->processRate(
                    $rateData['country'],
                    'Standard',
                    $rateData['standard'],
                    $forceUpdate
                );
                $result[$standardRateResult['status']]++;

                // Create Tax Rule for Standard rate
                if ($createRules && $standardRateResult['rate_id']) {
                    $ruleResult = $this->processRule(
                        $rateData['country'],
                        'Standard',
                        (int) $standardRateResult['rate_id']
                    );
                    $result[$ruleResult]++;
                }

                // Reduced rate (if exists)
                if ($rateData['reduced'] > 0) {
                    $reducedRateResult = $this->processRate(
                        $rateData['country'],
                        'Reduced',
                        $rateData['reduced'],
                        $forceUpdate
                    );
                    $result[$reducedRateResult['status']]++;

                    // Create Tax Rule for Reduced rate
                    if ($createRules && $reducedRateResult['rate_id']) {
                        $ruleResult = $this->processRule(
                            $rateData['country'],
                            'Reduced',
                            (int) $reducedRateResult['rate_id']
                        );
                        $result[$ruleResult]++;
                    }
                }
            }

            // Update last sync timestamp
            $this->configWriter->save(
                'tax_sync/manual/last_sync',
                $this->dateTime->gmtDate()
            );

            $result['success'] = true;
            $result['changes'] = $this->changes;

            // Send notifications if there were changes
            if (!empty($this->changes)) {
                $this->notificationService->notifyRateChanges($this->changes);
            }

            $this->logger->info('Sync completed', $result);
        } catch (\Exception $e) {
            $result['errors']++;
            $this->logger->error('Sync failed: ' . $e->getMessage());
            $this->notificationService->notifyError($e->getMessage());
        } finally {
            $this->lockManager->unlock(self::LOCK_NAME);
        }

        return $result;
    }

    /**
     * Process a single tax rate (create or update).
     *
     * @param string $country
     * @param string $type
     * @param float $rate
     * @param bool $forceUpdate
     * @return array
     */
    private function processRate(string $country, string $type, float $rate, bool $forceUpdate): array
    {
        $code = "{$country}-{$type}";
        $result = ['status' => 'errors', 'rate_id' => null];

        try {
            $existingRate = $this->findExistingRate($code);

            if ($existingRate) {
                $currentRate = (float) $existingRate->getRate();
                $result['rate_id'] = (int) $existingRate->getId();

                // Check if rate changed (or force update)
                if ($forceUpdate || abs($currentRate - $rate) > 0.001) {
                    $existingRate->setRate($rate);
                    $this->taxRateRepository->save($existingRate);

                    $this->changes[] = [
                        'country' => $country,
                        'code' => $code,
                        'old_rate' => $currentRate,
                        'new_rate' => $rate,
                    ];

                    $this->logger->info("Updated {$code}: {$currentRate}% > {$rate}%");
                    $result['status'] = 'updated';
                    return $result;
                }

                $result['status'] = 'skipped';
                return $result;
            }

            // Create new rate
            $taxRate = $this->taxRateFactory->create();
            $taxRate->setCode($code)
                ->setTaxCountryId($country)
                ->setTaxRegionId(0)
                ->setTaxPostcode('*')
                ->setRate($rate);

            $savedRate = $this->taxRateRepository->save($taxRate);
            $result['rate_id'] = (int) $savedRate->getId();

            $this->changes[] = [
                'country' => $country,
                'code' => $code,
                'old_rate' => null,
                'new_rate' => $rate,
            ];

            $this->logger->info("Created {$code}: {$rate}%");
            $result['status'] = 'created';
            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Error processing {$code}: " . $e->getMessage());
            return $result;
        }
    }

    /**
     * Process a single tax rule (create or update).
     *
     * @param string $country
     * @param string $type
     * @param int $rateId
     * @return string
     */
    private function processRule(string $country, string $type, int $rateId): string
    {
        $ruleCode = "Tax Rule {$country} {$type}";

        try {
            // Check if rule already exists
            $existingRule = $this->findExistingRule($ruleCode);

            if ($existingRule) {
                // Rule exists, check if rate is already assigned
                $existingRateIds = $existingRule->getTaxRateIds() ?: [];
                // Cast all IDs to int for proper comparison
                $existingRateIds = array_map('intval', $existingRateIds);

                if (in_array($rateId, $existingRateIds, true)) {
                    $this->logger->debug("Rule {$ruleCode} already has rate {$rateId}");
                    return 'rules_skipped';
                }

                // Add rate to existing rule
                $existingRateIds[] = $rateId;
                $existingRule->setTaxRateIds(array_unique($existingRateIds));
                $this->taxRuleRepository->save($existingRule);

                $this->logger->info("Updated rule {$ruleCode} with rate {$rateId}");
                return 'rules_created';
            }

            // Create new rule
            $customerTaxClassIds = $this->getCustomerTaxClassIds();
            $productTaxClassIds = $this->getProductTaxClassIds($type);

            if (empty($customerTaxClassIds) || empty($productTaxClassIds)) {
                $this->logger->warning("Cannot create rule {$ruleCode}: No tax classes found");
                return 'rules_skipped';
            }

            $taxRule = $this->taxRuleFactory->create();
            $taxRule->setCode($ruleCode)
                ->setTaxRateIds([$rateId])
                ->setCustomerTaxClassIds($customerTaxClassIds)
                ->setProductTaxClassIds($productTaxClassIds)
                ->setPriority(0)
                ->setPosition(0);

            $this->taxRuleRepository->save($taxRule);

            $this->ruleChanges[] = [
                'code' => $ruleCode,
                'rate_id' => $rateId,
            ];

            $this->logger->info("Created rule {$ruleCode}");
            return 'rules_created';
        } catch (\Exception $e) {
            $this->logger->error("Error processing rule {$ruleCode}: " . $e->getMessage());
            return 'rules_skipped';
        }
    }

    /**
     * Find existing tax rate by code.
     *
     * @param string $code
     * @return object|null
     */
    private function findExistingRate(string $code): ?object
    {
        $collection = $this->rateCollectionFactory->create();
        $collection->addFieldToFilter('code', $code);
        $item = $collection->getFirstItem();

        return $item->getId() ? $item : null;
    }

    /**
     * Find existing tax rule by code.
     *
     * @param string $code
     * @return object|null
     */
    private function findExistingRule(string $code): ?object
    {
        $collection = $this->ruleCollectionFactory->create();
        $collection->addFieldToFilter('code', $code);
        $item = $collection->getFirstItem();

        return $item->getId() ? $item : null;
    }

    /**
     * Get Customer Tax Class IDs.
     *
     * Returns configured class IDs or defaults to "Retail Customer" (ID: 3).
     *
     * @return array
     */
    private function getCustomerTaxClassIds(): array
    {
        if ($this->customerTaxClassIds !== null) {
            return $this->customerTaxClassIds;
        }

        $configuredIds = $this->config->getCustomerTaxClassIds();
        if (!empty($configuredIds)) {
            $this->customerTaxClassIds = $configuredIds;
            return $this->customerTaxClassIds;
        }

        // Find default customer tax classes (type = CUSTOMER)
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('class_type', 'CUSTOMER')
                ->create();

            $taxClasses = $this->taxClassRepository->getList($searchCriteria);
            $ids = [];

            foreach ($taxClasses->getItems() as $taxClass) {
                $ids[] = $taxClass->getClassId();
            }

            $this->customerTaxClassIds = $ids;
            $this->logger->info('Found Customer Tax Classes: ' . implode(', ', $ids));
        } catch (\Exception $e) {
            $this->logger->error('Error finding customer tax classes: ' . $e->getMessage());
            // Fallback to default Magento customer tax class ID
            $this->customerTaxClassIds = [3];
        }

        return $this->customerTaxClassIds;
    }

    /**
     * Get Product Tax Class IDs based on rate type.
     *
     * Standard and Reduced rates MUST use different Product Tax Classes,
     * otherwise Magento sums all tax rules for the same class (e.g. 19% + 7% = 26%).
     *
     * @param string $rateType
     * @return array
     */
    private function getProductTaxClassIds(string $rateType): array
    {
        if (isset($this->productTaxClassIdsByType[$rateType])) {
            return $this->productTaxClassIdsByType[$rateType];
        }

        if ($rateType === 'Reduced') {
            // 1. Check admin config for reduced tax classes
            $configuredIds = $this->config->getReducedProductTaxClassIds();
            if (!empty($configuredIds)) {
                $this->productTaxClassIdsByType[$rateType] = $configuredIds;
                return $configuredIds;
            }

            // 2. Find or create "Reduced Rate Goods" tax class
            $reducedClassId = $this->findOrCreateReducedTaxClass();
            $this->productTaxClassIdsByType[$rateType] = [$reducedClassId];
            return [$reducedClassId];
        }

        // Standard: use configured product tax classes or fallback to Taxable Goods (ID: 2)
        $configuredIds = $this->config->getProductTaxClassIds();
        if (!empty($configuredIds)) {
            $this->productTaxClassIdsByType[$rateType] = $configuredIds;
            return $configuredIds;
        }

        $this->productTaxClassIdsByType[$rateType] = [2];
        return [2];
    }

    /**
     * Find existing "Reduced Rate Goods" tax class or create it.
     *
     * @return int
     */
    private function findOrCreateReducedTaxClass(): int
    {
        // Search for existing "Reduced Rate Goods"
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('class_type', 'PRODUCT')
                ->create();

            $taxClasses = $this->taxClassRepository->getList($searchCriteria);

            foreach ($taxClasses->getItems() as $taxClass) {
                if (stripos($taxClass->getClassName(), 'Reduced') !== false) {
                    $this->logger->info(
                        'Found existing reduced tax class: '
                        . $taxClass->getClassName() . ' (ID ' . $taxClass->getClassId() . ')'
                    );
                    return (int) $taxClass->getClassId();
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug('Search for reduced tax class failed: ' . $e->getMessage());
        }

        // Create new "Reduced Rate Goods" tax class
        try {
            $taxClass = $this->taxClassDataFactory->create();
            $taxClass->setClassName('Reduced Rate Goods')
                ->setClassType('PRODUCT');

            $classId = (int) $this->taxClassRepository->save($taxClass);

            $this->logger->info('Created "Reduced Rate Goods" tax class with ID ' . $classId);
            return $classId;
        } catch (\Exception $e) {
            $this->logger->error('Failed to create Reduced Rate Goods tax class: ' . $e->getMessage());
            // Fallback to Taxable Goods - not ideal but prevents crash
            return 2;
        }
    }

    /**
     * Get the date of the last successful sync.
     *
     * @return string|null
     */
    public function getLastSyncDate(): ?string
    {
        return $this->config->getLastSyncDate();
    }
}
