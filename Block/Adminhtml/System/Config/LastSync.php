<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Admin block displaying last sync date
 */
class LastSync extends Field
{
    /**
     * @param Context $context
     * @param TimezoneInterface $timezone
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        private TimezoneInterface $timezone,
        private ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $lastSync = $this->scopeConfig->getValue('tax_sync/manual/last_sync');

        if (!$lastSync) {
            return '<span style="color: #999;">' . __('Never synced') . '</span>';
        }

        // Stored as a UTC "Y-m-d H:i:s" string via DateTime::gmtDate(). Parse it
        // explicitly and convert to the configured timezone. Do NOT pass the
        // string to TimezoneInterface::date(): that parses via the locale's SHORT
        // format and mangles ISO datetimes (wrong date, dropped seconds).
        try {
            $date = new \DateTime((string) $lastSync, new \DateTimeZone('UTC'));
            $date->setTimezone(new \DateTimeZone($this->timezone->getConfigTimezone()));
            $formattedDate = $date->format('d.m.Y H:i:s');
        } catch (\Exception $e) {
            $formattedDate = (string) $lastSync;
        }
        return '<span style="font-weight: bold;">' . $formattedDate . '</span>';
    }
}
