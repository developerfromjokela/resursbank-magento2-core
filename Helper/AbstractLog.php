<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Exception;
use InvalidArgumentException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * @package Resursbank\Checkout\Helper
 */
abstract class AbstractLog extends AbstractHelper
{
    /**
     * @var Logger
     */
    protected $log;

    /**
     * Channel name for the Logger.
     *
     * @var string
     */
    protected $loggerName = '';

    /**
     * @var DirectoryList
     */
    protected $directories;

    /**
     * Filename where entries are stored.
     *
     * @var string
     */
    protected $file = '';

    /**
     * @param DirectoryList $directories
     * @param Context $context
     * @throws FileSystemException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function __construct(
        DirectoryList $directories,
        Context $context
    ) {
        $this->directories = $directories;

        $this->initLog();

        parent::__construct($context);
    }

    /**
     * @param string $text
     * @param bool $force
     * @return self
     */
    public function info(string $text, bool $force = false): self
    {
        if ($force || $this->isEnabled()) {
            $this->log->info($text);
        }

        return $this;
    }

    /**
     * @param array|string|Exception $text
     * @param bool $force
     * @return self
     */
    public function error(string $text, $force = false): self
    {
        if ($force || $this->isEnabled()) {
            $this->log->error($text);
        }

        return $this;
    }

    /**
     * @param Exception $error
     * @param bool $force
     * @return self
     */
    public function exception(Exception $error, $force = false): self
    {
        if ($force || $this->isEnabled()) {
            $this->log->error(
                $error->getFile() . ' :: ' . $error->getLine() . '   -   '
                . $error->getMessage() . '   |   ' . $error->getTraceAsString()
            );
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * Initialize log handler.
     *
     * @throws FileSystemException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    private function initLog()
    {
        $this->log = new Logger($this->loggerName);
        $this->log->pushHandler(new StreamHandler(
            $this->directories->getPath('var') . "/log/{$this->file}.log",
            Logger::INFO,
            false
        ));
    }
}
