<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;

/**
 * @package Resursbank\Checkout\Helper
 */
class Log extends AbstractLog
{
    /**
     * @inheritDoc
     */
    protected $loggerName = 'Resursbank Core Log';

    /**
     * @inheritDoc
     */
    protected $file = 'resursbank_core';

    /**
     * @var Config
     */
    private $config;

    /**
     * @inheritDoc
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
