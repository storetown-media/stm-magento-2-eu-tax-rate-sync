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
     *
     * @return array<int, array{value: string, label: \Magento\Framework\Phrase}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'vatapieu', 'label' => __('VAT-API.eu (Recommended)')],
            ['value' => 'fallback', 'label' => __('Static Fallback (No API)')],
            ['value' => 'euvatrates', 'label' => __('EU VAT Rates API (Deprecated - outdated data)')],
            ['value' => 'vatcomply', 'label' => __('VatComply API (Deprecated - Not Working)')],
        ];
    }
}
