<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Cron;

use Storetown\TaxSync\Helper\Config;
use Storetown\TaxSync\Logger\Logger;
use Storetown\TaxSync\Model\TaxSyncService;

/**
 * Cron job for scheduled tax rate synchronization
 */
class SyncTaxRates
{
    /**
     * @param Config $config
     * @param TaxSyncService $syncService
     * @param Logger $logger
     */
    public function __construct(
        private Config $config,
        private TaxSyncService $syncService,
        private Logger $logger
    ) {
    }

    /**
     * Execute scheduled sync.
     *
     * @return void
     */
    public function execute(): void
    {
        if (!$this->config->isEnabled()) {
            $this->logger->info('Tax Sync is disabled, skipping cron execution');
            return;
        }

        $this->logger->info('Starting scheduled tax rate sync');

        try {
            $result = $this->syncService->sync();

            $this->logger->info('Scheduled sync completed', [
                'source' => $result['source'],
                'created' => $result['created'],
                'updated' => $result['updated'],
                'skipped' => $result['skipped'],
                'kept' => $result['kept'],
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Scheduled sync failed: ' . $e->getMessage());
        }
    }
}
