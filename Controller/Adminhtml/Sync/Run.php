<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Controller\Adminhtml\Sync;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Storetown\TaxSync\Model\TaxSyncService;

/**
 * Admin controller for manual tax rate sync
 */
class Run extends Action
{
    /** @var string */
    public const ADMIN_RESOURCE = 'Storetown_TaxSync::config';

    /**
     * @param Context $context
     * @param TaxSyncService $syncService
     */
    public function __construct(
        Context $context,
        private TaxSyncService $syncService
    ) {
        parent::__construct($context);
    }

    /**
     * Execute sync action and redirect back to config.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->syncService->sync();

            if ($result['success']) {
                $message = __(
                    'Tax rates synced successfully. Created: %1, Updated: %2, Skipped: %3',
                    $result['created'],
                    $result['updated'],
                    $result['skipped']
                );
                $this->messageManager->addSuccessMessage($message);

                // Show Tax Rules info if enabled
                $rulesCreated = $result['rules_created'] ?? 0;
                $rulesSkipped = $result['rules_skipped'] ?? 0;
                if ($rulesCreated > 0 || $rulesSkipped > 0) {
                    $rulesMessage = __(
                        'Tax Rules: Created/Updated: %1, Skipped: %2',
                        $rulesCreated,
                        $rulesSkipped
                    );
                    $this->messageManager->addSuccessMessage($rulesMessage);
                }

                if (!empty($result['changes'])) {
                    foreach ($result['changes'] as $change) {
                        $oldRate = $change['old_rate'] !== null
                            ? number_format($change['old_rate'], 2)
                            : 'NEW';
                        $changeMsg = __(
                            '%1: %2%% > %3%%',
                            $change['code'],
                            $oldRate,
                            number_format($change['new_rate'], 2)
                        );
                        $this->messageManager->addNoticeMessage($changeMsg);
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(
                    __('Tax rate sync failed. Check var/log/tax_sync.log for details.')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error: %1', $e->getMessage()));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(
            'adminhtml/system_config/edit',
            ['section' => 'tax_sync']
        );
    }
}
