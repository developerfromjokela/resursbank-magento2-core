<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Scope;
use Resursbank\Ecom\Config as EcomConfig;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope as EcomScope;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Log\LoggerInterface as EcomLoggerInterface;
use Resursbank\Ecom\Lib\Log\NoneLogger;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Throwable;

/**
 * Handles initial init of Ecom+.
 */
class Init
{
    /**
     * @param Config $config
     * @param DirectoryList $directoryList
     * @param File $file
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param Scope $scope
     * @param Log $log
     * @throws FileSystemException
     */
    public function __construct(
        private Config $config,
        private DirectoryList $directoryList,
        private File $file,
        private LoggerInterface $logger,
        private StoreManagerInterface $storeManager,
        private Scope $scope,
        private Log $log,
    ) {
        $this->loggerInterface = $logger;
        $logPath = $this->getLogPath();

        if (!is_dir(filename: $logPath)) {
            $this->file->mkdir(dir: $logPath);
        }
    }

    /**
     * Perform initial setup of Ecom+
     * @return void
     */
    public function beforeLaunch(): void
    {
        try {
            EcomConfig::setup(
                logger: $this->getLogger(),
                jwtAuth: new Jwt(
                    clientId: $this->config->getClientId(
                        scopeCode: $this->scope->getId(type: $this->scope->getType())
                    ),
                    clientSecret: $this->config->getClientSecret(
                        scopeCode: $this->scope->getId(type: $this->scope->getType())
                    ),
                    scope: EcomScope::MOCK_MERCHANT_API,
                    grantType: GrantType::CREDENTIALS,
                ),
                logLevel: $this->config->getLogLevel(scopeCode: $this->storeManager->getStore()->getCode())
            );
        } catch (Throwable $e) {
            $this->log->exception(error: $e);
        }
    }

    /**
     * @return string
     * @throws FileSystemException
     */
    private function getLogPath(): string
    {
        return $this->directoryList->getPath(code: 'var') . '/log/resursbank';
    }

    /**
     * Fetch a logger instance.
     *
     * @return EcomLoggerInterface
     */
    private function getLogger(): EcomLoggerInterface
    {
        $logger = new NoneLogger();

        if (!$this->config->isLoggingEnabled()) {
            return $logger;
        }

        try {
            $logger = new FileLogger(path: $this->getLogPath());
        } catch (Throwable $error) {
            $this->logger->error(message: $error->getMessage());
        }

        return $logger;
    }
}
