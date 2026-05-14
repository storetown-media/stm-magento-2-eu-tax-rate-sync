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

/**
 * Admin block for manual sync button
 */
class SyncButton extends Field
{
    /** @var string */
    protected $_template = 'Storetown_TaxSync::system/config/sync_button.phtml';

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    /**
     * Get rendered button HTML.
     *
     * @return string
     */
    public function getButtonHtml(): string
    {
        $button = $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setData([
                'id' => 'tax_sync_button',
                'label' => __('Sync Now'),
                'onclick' => 'setLocation(\'' . $this->getSyncUrl() . '\')',
            ]);

        return $button->toHtml();
    }

    /**
     * Get sync controller URL.
     *
     * @return string
     */
    public function getSyncUrl(): string
    {
        return $this->getUrl('taxsync/sync/run');
    }
}
