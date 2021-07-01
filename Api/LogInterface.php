<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Api;

use \Exception;
use Monolog\Logger;

interface LogInterface
{
    /**
     * @param Logger $logger
     * @return self
     */
    public function setLogger(Logger $logger): self;

    /**
     * @param string $text
     * @param bool $force
     * @return self
     */
    public function info(string $text, bool $force = false): self;

    /**
     * @param string $text
     * @param bool $force
     * @return self
     */
    public function error(string $text, bool $force = false): self;

    /**
     * @param Exception $error
     * @param bool $force
     * @return self
     */
    public function exception(Exception $error, bool $force = false): self;

    /**
     * @return bool
     */
    public function isEnabled(): bool;
}
