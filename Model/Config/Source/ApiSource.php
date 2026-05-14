<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * API source options for admin configuration
 */
class ApiSource implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'euvatrates', 'label' => __('EU VAT Rates API (Recommended)')],
            ['value' => 'fallback', 'label' => __('Static Fallback (No API)')],
            ['value' => 'vatcomply', 'label' => __('VatComply API (Deprecated - Not Working)')],
        ];
    }
}
