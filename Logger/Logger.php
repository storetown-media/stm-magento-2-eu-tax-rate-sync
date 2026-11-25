<?php
/**
 * Copyright (c) Storetown Media. All rights reserved.
 * See LICENSE for license details.
 */
declare(strict_types=1);

namespace Storetown\TaxSync\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * Logger of the module, writes to var/log/tax_sync.log
 *
 * Passes everything on to the Monolog channel configured in etc/di.xml, because
 * Monolog marks its own logger class as final.
 */
class Logger extends AbstractLogger
{
    /**
     * @param LoggerInterface $channel
     */
    public function __construct(
        private LoggerInterface $channel
    ) {
    }

    /**
     * Pass a log entry on to the channel.
     *
     * The message has no native type, so that the signature fits psr/log 1, 2 and 3.
     *
     * @param mixed $level
     * @param string|\Stringable $message
     * @param mixed[] $context
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $this->channel->log($level, $message, $context);
    }
}
