<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Configuration helper for TaxSync module
 */
class Config extends AbstractHelper
{
    /** @var string */
    private const XML_PATH_ENABLED = 'tax_sync/general/enabled';

    /** @var string */
    private const XML_PATH_FREQUENCY = 'tax_sync/general/sync_frequency';

    /** @var string */
    private const XML_PATH_API_SOURCE = 'tax_sync/general/api_source';

    /** @var string */
    private const XML_PATH_INCLUDE_NON_EU = 'tax_sync/general/include_non_eu';

    /** @var string */
    private const XML_PATH_KEEP_MANUAL = 'tax_sync/general/keep_manual';

    /** @var string */
    private const XML_PATH_CREATE_RULES = 'tax_sync/rules/create_rules';

    /** @var string */
    private const XML_PATH_CUSTOMER_TAX_CLASSES = 'tax_sync/rules/customer_tax_classes';

    /** @var string */
    private const XML_PATH_PRODUCT_TAX_CLASSES = 'tax_sync/rules/product_tax_classes';

    /** @var string */
    private const XML_PATH_PRODUCT_TAX_CLASSES_REDUCED = 'tax_sync/rules/product_tax_classes_reduced';

    /** @var string */
    private const XML_PATH_EMAIL_ENABLED = 'tax_sync/notifications/email_enabled';

    /** @var string */
    private const XML_PATH_EMAIL_RECIPIENT = 'tax_sync/notifications/email_recipient';

    /** @var string */
    private const XML_PATH_ADMIN_NOTIFICATION = 'tax_sync/notifications/admin_notification';

    /** @var string */
    private const XML_PATH_LAST_SYNC = 'tax_sync/manual/last_sync';

    /**
     * Check if module is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get configured sync frequency.
     *
     * @return string
     */
    public function getSyncFrequency(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_FREQUENCY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get cron schedule expression based on frequency.
     *
     * @return string
     */
    public function getCronSchedule(): string
    {
        return match ($this->getSyncFrequency()) {
            'daily' => '0 3 * * *',
            'weekly' => '0 3 * * 1',
            'monthly' => '0 3 1 * *',
            default => '0 3 * * 1',
        };
    }

    /**
     * Get configured API source key.
     *
     * @return string
     */
    public function getApiSource(): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_API_SOURCE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if non-EU countries should be included.
     *
     * @return bool
     */
    public function includeNonEuCountries(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_INCLUDE_NON_EU,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if rates changed by hand are kept.
     *
     * @return bool
     */
    public function isKeepManualRates(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_KEEP_MANUAL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if automatic tax rule creation is enabled.
     *
     * @return bool
     */
    public function isCreateRulesEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CREATE_RULES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get configured customer tax class IDs.
     *
     * @return int[]
     */
    public function getCustomerTaxClassIds(): array
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_TAX_CLASSES,
            ScopeInterface::SCOPE_STORE
        );
        if (empty($value)) {
            return [];
        }
        return array_map('intval', explode(',', $value));
    }

    /**
     * Get configured product tax class IDs.
     *
     * @return int[]
     */
    public function getProductTaxClassIds(): array
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_TAX_CLASSES,
            ScopeInterface::SCOPE_STORE
        );
        if (empty($value)) {
            return [];
        }
        return array_map('intval', explode(',', $value));
    }

    /**
     * Get configured reduced product tax class IDs.
     *
     * @return int[]
     */
    public function getReducedProductTaxClassIds(): array
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_TAX_CLASSES_REDUCED,
            ScopeInterface::SCOPE_STORE
        );
        if (empty($value)) {
            return [];
        }
        return array_map('intval', explode(',', $value));
    }

    /**
     * Check if email notifications are enabled.
     *
     * @return bool
     */
    public function isEmailNotificationEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EMAIL_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get email recipient address.
     *
     * @return string|null
     */
    public function getEmailRecipient(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_RECIPIENT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if admin panel notifications are enabled.
     *
     * @return bool
     */
    public function isAdminNotificationEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ADMIN_NOTIFICATION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get last sync date from config.
     *
     * @return string|null
     */
    public function getLastSyncDate(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_LAST_SYNC);
    }

    /**
     * Get list of EU country codes.
     *
     * @return string[]
     */
    public function getEuCountries(): array
    {
        return [
            'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
            'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
            'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'
        ];
    }

    /**
     * Get list of non-EU country codes.
     *
     * @return string[]
     */
    public function getNonEuCountries(): array
    {
        return ['CH', 'GB', 'NO'];
    }

    /**
     * Get all country codes (EU + optionally non-EU).
     *
     * @return string[]
     */
    public function getAllCountries(): array
    {
        $countries = $this->getEuCountries();

        if ($this->includeNonEuCountries()) {
            $countries = array_merge($countries, $this->getNonEuCountries());
        }

        return $countries;
    }
}
