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
use Magento\Framework\Exception\ValidatorException;
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
    protected $logger;

    /**
     * @var DirectoryList
     */
    protected $directories;

    /**
     * Channel name for the Logger.
     *
     * @var string
     */
    protected $loggerName = '';

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
     * @throws ValidatorException
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
     * @param Logger $logger
     * @return $this
     */
    public function setLogger(Logger $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param string $text
     * @param bool $force
     * @return self
     */
    public function info(
        string $text,
        bool $force = false
    ): self {
        if ($force || $this->isEnabled()) {
            $this->logger->info($text);
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
            $this->logger->error($text);
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
            $this->logger->error(
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
     * @throws ValidatorException
     */
    private function initLog(): void
    {
        if (!is_string($this->loggerName) || $this->loggerName === '') {
            throw new ValidatorException(__(
                'Cannot proceed without logger name.'
            ));
        }

        if (!is_string($this->file) || $this->file === '') {
            throw new ValidatorException(__(
                'Cannot proceed without logfile.'
            ));
        }

        $logger = new Logger($this->loggerName);
        $logger->pushHandler(new StreamHandler(
            $this->directories->getPath('var') . "/log/{$this->file}.log",
            Logger::INFO,
            false
        ));

        $this->setLogger($logger);
    }
}
