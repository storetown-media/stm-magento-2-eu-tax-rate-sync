<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Backend model for sync_frequency field.
 *
 * Translates the frequency selection (daily/weekly/monthly) into a cron
 * expression and writes it to the config path that crontab.xml reads.
 */
class CronFrequency extends Value
{
    private const CRON_SCHEDULE_PATH = 'tax_sync/general/cron_schedule';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param WriterInterface $configWriter
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        private WriterInterface $configWriter,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * After saving the frequency, write the corresponding cron expression.
     *
     * @return CronFrequency
     */
    public function afterSave(): CronFrequency
    {
        $cronExpr = match ($this->getValue()) {
            'daily' => '0 3 * * *',
            'weekly' => '0 3 * * 1',
            'monthly' => '0 3 1 * *',
            default => '0 3 * * 1',
        };

        $this->configWriter->save(self::CRON_SCHEDULE_PATH, $cronExpr);

        return parent::afterSave();
    }
}
