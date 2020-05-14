<?php
/**
 * Copyright 2016 Resurs Bank AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class Log
 *
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
     * @var Config
     */
    protected $config;

    /**
     * @param DirectoryList $directories
     * @param Context $context
     * @param Config $config
     * @throws FileSystemException
     */
    public function __construct(
        DirectoryList $directories,
        Context $context,
        Config $config
    ) {
        $this->directories = $directories;
        $this->config = $config;

        $this->log = new Logger($this->loggerName);
        $this->log->pushHandler(new StreamHandler(
            $this->directories->getPath('var') . "/log/{$this->file}.log",
            Logger::INFO,
            false
        ));

        parent::__construct($context);
    }

    /**
     * Log info message.
     *
     * @param array|string|Exception $text
     * @param bool                   $force
     * @return self
     * @throws Exception
     */
    public function info($text, bool $force = false): self
    {
        if ($force || $this->isEnabled()) {
            $this->log->info($this->prepareMessage($text));
        }

        return $this;
    }

    /**
     * Log error message.
     *
     * @param array|string|Exception $text
     * @param bool                   $force
     * @return self
     * @throws Exception
     */
    public function error($text, $force = false): self
    {
        if ($force || $this->isEnabled()) {
            $this->log->error($this->prepareMessage($text));
        }

        return $this;
    }

    /**
     * Prepare message before adding it to a log file.
     *
     * @param array|string|Exception $text
     * @return string
     */
    public function prepareMessage($text): string
    {
        $result = '';

        if (is_array($text)) {
            $result = json_encode($text);
        } elseif ($text instanceof Exception) {
            $result = $text->getFile()
                . ' :: '
                . $text->getLine()
                . '   -   '
                . $text->getMessage()
                . '   -   '
                . $text->getTraceAsString();
        }

        return (string) $result;
    }

    /**
     * Check if debugging is enabled.
     *
     * @return bool
     * @throws Exception
     */
    public function isEnabled(): bool
    {
        return $this->config->isDebugEnabled();
    }
}
