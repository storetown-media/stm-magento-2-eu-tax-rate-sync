<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Model;

use Magento\AdminNotification\Model\InboxFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Storetown\TaxSync\Helper\Config;
use Storetown\TaxSync\Logger\Logger;

/**
 * Service for email and admin panel notifications
 */
class NotificationService
{
    /**
     * @param Config $config
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param InboxFactory $inboxFactory
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param Escaper $escaper
     */
    public function __construct(
        private Config $config,
        private TransportBuilder $transportBuilder,
        private StateInterface $inlineTranslation,
        private StoreManagerInterface $storeManager,
        private InboxFactory $inboxFactory,
        private DateTime $dateTime,
        private Logger $logger,
        private Escaper $escaper
    ) {
    }

    /**
     * Notify about rate changes and kept rates via email and admin notification.
     *
     * @param array<int,array{country:string,code:string,old_rate:float|null,new_rate:float}> $changes
     * @param array<int,array{country:string,code:string,current_rate:float,source_rate:float}> $kept
     * @return void
     */
    public function notifyRateChanges(array $changes, array $kept = []): void
    {
        if (empty($changes) && empty($kept)) {
            return;
        }

        // Email notification
        $recipient = (string) $this->config->getEmailRecipient();
        if ($this->config->isEmailNotificationEnabled() && $recipient !== '') {
            $this->sendEmailNotification($changes, $kept, $recipient);
        }

        // Admin notification
        if ($this->config->isAdminNotificationEnabled()) {
            if (!empty($changes)) {
                $this->addAdminNotification($changes);
            }
            if (!empty($kept)) {
                $this->addKeptNotification($kept);
            }
        }
    }

    /**
     * Notify about sync errors via admin notification.
     *
     * @param string $errorMessage
     * @return void
     */
    public function notifyError(string $errorMessage): void
    {
        if ($this->config->isAdminNotificationEnabled()) {
            $inbox = $this->inboxFactory->create();
            $inbox->addMajor(
                (string) __('Tax Sync Error'),
                (string) __('Automatic VAT rate synchronization failed: %1', $errorMessage),
                'https://docs.magento.com/user-guide/tax/tax-rules.html'
            );
        }
    }

    /**
     * Send email notification about rate changes.
     *
     * @param array<int,array{country:string,code:string,old_rate:float|null,new_rate:float}> $changes
     * @param array<int,array{country:string,code:string,current_rate:float,source_rate:float}> $kept
     * @param string $recipient
     * @return void
     */
    private function sendEmailNotification(array $changes, array $kept, string $recipient): void
    {
        try {
            $this->inlineTranslation->suspend();

            $changesHtml = $this->buildChangesHtml($changes) . $this->buildKeptHtml($kept);
            $store = $this->storeManager->getStore();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('tax_sync_rate_changed')
                ->setTemplateOptions([
                    'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                    'store' => $store->getId(),
                ])
                ->setTemplateVars([
                    'changes' => $changesHtml,
                    'store_name' => $store->getName(),
                    'sync_date' => $this->dateTime->gmtDate('d.m.Y H:i'),
                ])
                ->setFromByScope('general')
                ->addTo($recipient)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();

            $this->logger->info('Email notification sent');
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email: ' . $e->getMessage());
            $this->inlineTranslation->resume();
        }
    }

    /**
     * Add admin panel notification about rate changes.
     *
     * @param array<int,array{country:string,code:string,old_rate:float|null,new_rate:float}> $changes
     * @return void
     */
    private function addAdminNotification(array $changes): void
    {
        $count = count($changes);
        $countries = array_unique(array_column($changes, 'country'));
        $countriesList = implode(', ', $countries);

        $inbox = $this->inboxFactory->create();
        $inbox->addNotice(
            (string) __('VAT rates updated (%1 changes)', $count),
            (string) __(
                'VAT rates for the following countries have been updated: %1. '
                . 'Please review the changes under Stores > Tax Zones and Rates.',
                $countriesList
            ),
            'https://docs.magento.com/user-guide/tax/tax-rules.html'
        );

        $this->logger->info('Admin notification added');
    }

    /**
     * Add admin panel notification about rates that were changed by hand and kept.
     *
     * @param array<int,array{country:string,code:string,current_rate:float,source_rate:float}> $kept
     * @return void
     */
    private function addKeptNotification(array $kept): void
    {
        $list = [];
        foreach ($kept as $rate) {
            $list[] = sprintf(
                '%s %s%% (%s%%)',
                $rate['code'],
                number_format($rate['current_rate'], 2),
                number_format($rate['source_rate'], 2)
            );
        }

        $inbox = $this->inboxFactory->create();
        $inbox->addNotice(
            (string) __('VAT rates changed by hand were kept (%1)', count($kept)),
            (string) __(
                'The data source has a different value (in brackets) for these rates: %1. '
                . 'Please check them under Stores > Tax Zones and Rates.',
                implode(', ', $list)
            ),
            'https://docs.magento.com/user-guide/tax/tax-rules.html'
        );

        $this->logger->info('Admin notification about kept rates added');
    }

    /**
     * Build HTML table rows for email notification.
     *
     * @param array<int,array{country:string,code:string,old_rate:float|null,new_rate:float}> $changes
     * @return string
     */
    private function buildChangesHtml(array $changes): string
    {
        $html = '';

        foreach ($changes as $change) {
            $oldRate = $change['old_rate'] !== null
                ? number_format($change['old_rate'], 2) . '%'
                : (string) __('New');
            $newRate = number_format($change['new_rate'], 2) . '%';

            $html .= $this->buildRowHtml($change['country'], $change['code'], $oldRate, $newRate);
        }

        return $html;
    }

    /**
     * Build HTML table rows for rates that were changed by hand and kept.
     *
     * @param array<int,array{country:string,code:string,current_rate:float,source_rate:float}> $kept
     * @return string
     */
    private function buildKeptHtml(array $kept): string
    {
        $html = '';

        foreach ($kept as $rate) {
            $html .= $this->buildRowHtml(
                $rate['country'],
                $rate['code'],
                number_format($rate['current_rate'], 2) . '%',
                (string) __(
                    '%1 in the data source, not applied (changed by hand)',
                    number_format($rate['source_rate'], 2) . '%'
                )
            );
        }

        return $html;
    }

    /**
     * Build one HTML table row.
     *
     * @param string $country
     * @param string $code
     * @param string $oldRate
     * @param string $newRate
     * @return string
     */
    private function buildRowHtml(string $country, string $code, string $oldRate, string $newRate): string
    {
        $cells = array_values((array) $this->escaper->escapeHtml([$country, $code, $oldRate, $newRate]));
        $tdStyle = 'padding:8px;border:1px solid #ddd;';
        $tdRight = $tdStyle . 'text-align:right;';

        return sprintf(
            '<tr><td style="%s">%s</td>'
            . '<td style="%s">%s</td>'
            . '<td style="%s">%s</td>'
            . '<td style="%sfont-weight:bold;">%s</td></tr>',
            $tdStyle,
            $cells[0],
            $tdStyle,
            $cells[1],
            $tdRight,
            $cells[2],
            $tdRight,
            $cells[3]
        );
    }
}
