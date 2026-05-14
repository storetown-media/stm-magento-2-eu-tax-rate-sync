<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Tax\Model\ResourceModel\Calculation\Rate\CollectionFactory;
use Storetown\TaxSync\Helper\Config;
use Storetown\TaxSync\Model\ApiSourcePool;
use Storetown\TaxSync\Model\TaxSyncService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command to display Tax Sync status
 */
class StatusCommand extends Command
{
    /**
     * @param Config $config
     * @param ApiSourcePool $apiSourcePool
     * @param TaxSyncService $syncService
     * @param CollectionFactory $rateCollectionFactory
     * @param State $state
     */
    public function __construct(
        private Config $config,
        private ApiSourcePool $apiSourcePool,
        private TaxSyncService $syncService,
        private CollectionFactory $rateCollectionFactory,
        private State $state
    ) {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName('tax:sync:status')
            ->setDescription('Show Tax Sync status and current rates');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            // Area code already set, safe to ignore
            unset($e);
        }

        // Configuration status
        $output->writeln('<info>Configuration:</info>');
        $enabled = $this->config->isEnabled()
            ? '<info>Yes</info>'
            : '<comment>No</comment>';
        $output->writeln('  Enabled:     ' . $enabled);
        $output->writeln('  Frequency:   ' . $this->config->getSyncFrequency());
        $output->writeln('  API Source:  ' . $this->config->getApiSource());
        $nonEu = $this->config->includeNonEuCountries() ? 'Yes' : 'No';
        $output->writeln('  Non-EU:      ' . $nonEu);
        $lastSync = $this->syncService->getLastSyncDate() ?: 'Never';
        $output->writeln('  Last Sync:   ' . $lastSync);
        $output->writeln('');

        // API source availability
        $output->writeln('<info>API Sources:</info>');
        foreach ($this->apiSourcePool->getAllSources() as $name => $source) {
            $status = $source->isAvailable()
                ? '<info>Available</info>'
                : '<error>Unavailable</error>';
            $output->writeln("  {$name}: {$status}");
        }
        $output->writeln('');

        // Current rates
        $output->writeln('<info>Current Tax Rates:</info>');

        $collection = $this->rateCollectionFactory->create();
        $collection->addFieldToFilter('code', ['like' => '%-%']);
        $collection->setOrder('code', 'ASC');

        if ($collection->getSize() === 0) {
            $output->writeln('  <comment>No European tax rates found</comment>');
            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['Code', 'Country', 'Rate %']);

        foreach ($collection as $rate) {
            $table->addRow([
                $rate->getCode(),
                $rate->getTaxCountryId(),
                number_format((float)$rate->getRate(), 2),
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
