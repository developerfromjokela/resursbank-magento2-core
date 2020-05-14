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
use InvalidArgumentException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;

/**
 * Class Log
 *
 * @package Resursbank\Checkout\Helper
 */
class Log extends AbstractLog
{
    /**
     * Channel name for the Logger.
     *
     * @var string
     */
    protected $loggerName = 'Resursbank Core Log';

    /**
     * Filename where entries are stored.
     *
     * @var string
     */
    protected $file = 'resursbank_core';

    /**
     * @var Config
     */
    private $config;

    /**
     * @param DirectoryList $directories
     * @param Context $context
     * @param Config $config
     * @throws FileSystemException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function __construct(
        DirectoryList $directories,
        Context $context,
        Config $config
    ) {
        $this->config = $config;

        parent::__construct($directories, $context);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->isDebugEnabled();
    }
}
