<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Storetown\TaxSync\Model\TaxSyncService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI command for manual VAT rate synchronization
 */
class SyncCommand extends Command
{
    /**
     * @param TaxSyncService $syncService
     * @param State $state
     */
    public function __construct(
        private TaxSyncService $syncService,
        private State $state
    ) {
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this->setName('tax:sync:run')
            ->setDescription('Manually sync European VAT rates')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force update all rates');
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

        $output->writeln('<info>Starting VAT rate synchronization...</info>');
        $output->writeln('');

        $forceUpdate = $input->getOption('force');
        $result = $this->syncService->sync($forceUpdate);

        if (!$result['success']) {
            $output->writeln('<error>Sync failed!</error>');
            return Command::FAILURE;
        }

        $output->writeln("<comment>Source: {$result['source']}</comment>");
        $output->writeln('');

        // Show changes if any
        if (!empty($result['changes'])) {
            $output->writeln('<info>Changes:</info>');

            $table = new Table($output);
            $table->setHeaders(['Country', 'Code', 'Old Rate', 'New Rate']);

            foreach ($result['changes'] as $change) {
                $oldRate = $change['old_rate'] !== null
                    ? number_format($change['old_rate'], 2) . '%'
                    : 'NEW';
                $table->addRow([
                    $change['country'],
                    $change['code'],
                    $oldRate,
                    number_format($change['new_rate'], 2) . '%',
                ]);
            }

            $table->render();
            $output->writeln('');
        }

        // Summary
        $output->writeln('<info>Summary:</info>');
        $output->writeln("  <comment>Tax Rates:</comment>");
        $output->writeln("    Created: {$result['created']}");
        $output->writeln("    Updated: {$result['updated']}");
        $output->writeln("    Skipped: {$result['skipped']}");
        $output->writeln("    Errors:  {$result['errors']}");

        if (isset($result['rules_created']) || isset($result['rules_skipped'])) {
            $output->writeln("  <comment>Tax Rules:</comment>");
            $output->writeln("    Created: " . ($result['rules_created'] ?? 0));
            $output->writeln("    Skipped: " . ($result['rules_skipped'] ?? 0));
        }

        return Command::SUCCESS;
    }
}
