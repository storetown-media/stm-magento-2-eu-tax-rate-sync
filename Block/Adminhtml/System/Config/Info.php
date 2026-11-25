<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\PackageInfoFactory;

/**
 * Admin info block for TaxSync module
 */
class Info extends Field
{
    /** @var PackageInfoFactory */
    private PackageInfoFactory $packageInfoFactory;

    /**
     * @param Context $context
     * @param PackageInfoFactory $packageInfoFactory
     * @param mixed[] $data
     */
    public function __construct(
        Context $context,
        PackageInfoFactory $packageInfoFactory,
        array $data = []
    ) {
        $this->packageInfoFactory = $packageInfoFactory;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $packageInfo = $this->packageInfoFactory->create();
        $version = $packageInfo->getVersion('Storetown_TaxSync') ?: '1.0.0';
        $logoUrl = $this->getViewFileUrl('Storetown_TaxSync::images/storetown-logo.png');

        // phpcs:disable Generic.Files.LineLength
        return <<<HTML
<div style="border:1px solid #e3e3e3;border-radius:8px;padding:20px;margin:10px 0;background:linear-gradient(135deg,#fafafa 0%,#fff 100%);">
    <div style="display:flex;align-items:center;gap:20px;margin-bottom:20px;">
        <img src="{$logoUrl}" alt="Storetown Media" style="max-width:200px;height:auto;">
        <div>
            <h2 style="margin:0 0 5px 0;color:#1a1a6c;font-size:18px;">EU Tax Rate Sync</h2>
            <span style="background:#f37b20;color:#fff;padding:3px 10px;border-radius:12px;font-size:12px;">Version {$version}</span>
        </div>
    </div>
    <div style="background:#f8f9fa;border-radius:6px;padding:15px;margin-bottom:15px;">
        <h3 style="margin:0 0 10px 0;color:#1a1a6c;font-size:14px;">Funktions&uuml;bersicht</h3>
        <ul style="margin:0;padding-left:20px;color:#555;font-size:13px;line-height:1.8;">
            <li><strong>Automatischer Import</strong> aller EU-Mehrwertsteuers&auml;tze (27 L&auml;nder + CH, GB, NO)</li>
            <li><strong>Standard- und erm&auml;&szlig;igte S&auml;tze</strong> werden separat importiert</li>
            <li><strong>Tax Rules</strong> werden automatisch erstellt und verkn&uuml;pft</li>
            <li><strong>Cron-basierte Synchronisierung</strong> (t&auml;glich, w&ouml;chentlich, monatlich)</li>
            <li><strong>E-Mail-Benachrichtigung</strong> bei &Auml;nderungen der Steuers&auml;tze</li>
            <li><strong>CLI-Befehle</strong>: <code>bin/magento tax:sync:run</code> und <code>tax:sync:status</code></li>
        </ul>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
        <div style="background:#e8f5e9;border-radius:6px;padding:12px;">
            <h4 style="margin:0 0 8px 0;color:#2e7d32;font-size:13px;">Datenquellen</h4>
            <p style="margin:0;font-size:12px;color:#555;">
                Standards&auml;tze: live von VAT-API.eu<br>
                Erm&auml;&szlig;igte S&auml;tze und Fallback: gepflegte Liste (Nov 2026)
            </p>
        </div>
        <div style="background:#e3f2fd;border-radius:6px;padding:12px;">
            <h4 style="margin:0 0 8px 0;color:#1565c0;font-size:13px;">Letzte Aktualisierung</h4>
            <p style="margin:0;font-size:12px;color:#555;">
                Die Standards&auml;tze werden bei jedem Sync<br>
                neu abgerufen.
            </p>
        </div>
    </div>
    <div style="border-top:1px solid #e3e3e3;padding-top:15px;display:flex;justify-content:space-between;align-items:center;">
        <div style="font-size:12px;color:#777;">
            <strong>Kostenlose Extension</strong> von Storetown Media<br>
            E-Commerce L&ouml;sungen f&uuml;r Magento, Shopware &amp; WooCommerce
        </div>
        <div>
            <a href="https://www.storetown-media.de" target="_blank" style="display:inline-block;background:#1a1a6c;color:#fff;padding:8px 16px;border-radius:5px;text-decoration:none;font-size:12px;margin-right:8px;">Website</a>
            <a href="https://www.storetown-media.de/kontakt" target="_blank" style="display:inline-block;background:#f37b20;color:#fff;padding:8px 16px;border-radius:5px;text-decoration:none;font-size:12px;">Support</a>
        </div>
    </div>
</div>
HTML;
        // phpcs:enable Generic.Files.LineLength
    }
}
