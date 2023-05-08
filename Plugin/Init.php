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
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Scope;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\Ecom\Config as EcomConfig;
use Resursbank\Ecom\Lib\Api\Environment as EnvironmentType;
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
     * @var Config
     */
    private Config $config;
    /**
     * @var Scope
     */
    private Scope $scope;
    /**
     * @var Credentials
     */
    private Credentials $credentials;
    /**
     * @var Log
     */
    private Log $log;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $loggerInterface;

    /**
     * @param Config $config
     * @param DirectoryList $directoryList
     * @param File $file
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param Scope $scope
     * @param Credentials $credentials
     * @param Log $log
     * @throws FileSystemException
     */
    public function __construct(
        Config $config,
        private DirectoryList $directoryList,
        private File $file,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        Scope $scope,
        Credentials $credentials,
        Log $log,
    ) {
        $this->config = $config;
        $this->scope = $scope;
        $this->credentials = $credentials;
        $this->log = $log;
        $this->logger = $logger;
        $this->loggerInterface = $logger;
        $this->storeManager = $storeManager;
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
        $environment = $this->credentials->getEnvironment();
        $jwtScope = $environment === EnvironmentType::PROD
            ? EcomScope::MERCHANT_API
            : EcomScope::MOCK_MERCHANT_API;

        try {
            EcomConfig::setup(
                logger: $this->getLogger(),
                jwtAuth: new Jwt(
                    clientId: $this->config->getClientId(
                        scopeCode: $this->scope->getId()
                    ),
                    clientSecret: $this->config->getClientSecret(
                        scopeCode: $this->scope->getId()
                    ),
                    scope: $jwtScope,
                    grantType: GrantType::CREDENTIALS,
                ),
                logLevel: $this->config->getLogLevel(scopeCode: $this->storeManager->getStore()->getCode()),
                isProduction: $environment === EnvironmentType::PROD
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
            $logger->error(message: $error->getMessage());
        }

        return $logger;
    }
}
