<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger as MonologLogger;

/**
 * Log handler for TaxSync module
 */
class Handler extends Base
{
    /** @var int */
    protected $loggerType = MonologLogger::INFO;

    /** @var string */
    protected $fileName = '/var/log/tax_sync.log';
}
