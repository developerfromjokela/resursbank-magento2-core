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
use Resursbank\Core\Api\LogInterface;
use Throwable;

use function is_string;

abstract class AbstractLog extends AbstractHelper implements LogInterface
{
    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var DirectoryList
     */
    private DirectoryList $directories;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * Name of the channel for the Logger (overwritten by child class).
     *
     * @var string
     */
    protected string $loggerName = '';

    /**
     * Filename where entries are stored (overwritten by child class).
     *
     * @var string
     */
    protected string $file = '';

    /**
     * @param DirectoryList $directories
     * @param Config $config
     * @param Context $context
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function __construct(
        DirectoryList $directories,
        Config $config,
        Context $context
    ) {
        $this->directories = $directories;
        $this->config = $config;

        $this->initLog();

        parent::__construct($context);
    }

    /**
     * @param Logger $logger
     * @return LogInterface
     */
    public function setLogger(
        Logger $logger
    ): LogInterface {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param string $text
     * @param bool $force
     * @return LogInterface
     */
    public function info(
        string $text,
        bool $force = false
    ): LogInterface {
        if ($force || $this->isEnabled()) {
            $this->logger->info($text);
        }

        return $this;
    }

    /**
     * @param string $text
     * @param bool $force
     * @return LogInterface
     */
    public function error(
        string $text,
        bool $force = false
    ): LogInterface {
        if ($force || $this->isEnabled()) {
            $this->logger->error($text);
        }

        return $this;
    }

    /**
     * @param Throwable $error
     * @param bool $force
     * @return LogInterface
     */
    public function exception(
        Throwable $error,
        bool $force = false
    ): LogInterface {
        /**
         * NOTE: The order of these checks are important, changing them may
         * introduce a circular dependency error (since Log classes need to log
         * Exceptions sometimes).
         */
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
        return $this->config->isLoggingEnabled();
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
