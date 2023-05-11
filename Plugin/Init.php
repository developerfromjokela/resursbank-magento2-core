<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin;

use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Locale\Resolver as Locale;
use Psr\Log\LoggerInterface;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Scope;
use Resursbank\Core\Model\Cache\Ecom as Cache;
use Resursbank\Core\Model\Cache\Type\Resursbank;
use Resursbank\Ecom\Config as EcomConfig;
use Resursbank\Ecom\Lib\Api\Environment;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope as EcomScope;
use Resursbank\Ecom\Lib\Cache\CacheInterface;
use Resursbank\Ecom\Lib\Cache\None;
use Resursbank\Ecom\Lib\Locale\Language;
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
     * @param Scope $scope
     * @param Log $log
     * @param Cache $cache
     * @param Locale $locale
     * @param StateInterface $cacheState
     * @param DriverInterface $filesystemDriver
     * @throws FileSystemException
     */
    public function __construct(
        private readonly Config $config,
        private readonly DirectoryList $directoryList,
        private readonly File $file,
        private readonly LoggerInterface $logger,
        private readonly Scope $scope,
        private readonly Log $log,
        private readonly Cache $cache,
        private readonly Locale $locale,
        private readonly StateInterface $cacheState,
        private readonly DriverInterface $filesystemDriver
    ) {
        $logPath = $this->getLogPath();

        if (!$this->filesystemDriver->isDirectory(path: $logPath)) {
            $this->file->mkdir(dir: $logPath);
        }
    }

    /**
     * Perform initial setup of Ecom+
     *
     * @return void
     */
    public function beforeLaunch(): void
    {
        try {
            $env = $this->config->getMapiEnvironment(
                scopeCode: $this->scope->getId(),
                scopeType: $this->scope->getType()
            );

            EcomConfig::setup(
                logger: $this->getLogger(),
                jwtAuth: new Jwt(
                    clientId: $this->config->getClientId(
                        scopeCode: $this->scope->getId(),
                        scopeType: $this->scope->getType()
                    ),
                    clientSecret: $this->config->getClientSecret(
                        scopeCode: $this->scope->getId(),
                        scopeType: $this->scope->getType()
                    ),
                    scope: $env === Environment::PROD ?
                        EcomScope::MERCHANT_API : EcomScope::MOCK_MERCHANT_API,
                    grantType: GrantType::CREDENTIALS,
                ),
                logLevel: $this->config->getLogLevel(
                    scopeCode: $this->scope->getId(),
                    scopeType: $this->scope->getType()
                ),
                cache: $this->getCache(),
                isProduction: $env === Environment::PROD,
                language: $this->getLanguage()
            );
        } catch (Throwable $e) {
            $this->log->exception(error: $e);
        }
    }

    /**
     * Get path to log directory inside Magento directory.
     *
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

        if (!$this->config->isLoggingEnabled(
            scopeCode: $this->scope->getId(),
            scopeType: $this->scope->getType()
        )
        ) {
            return $logger;
        }

        try {
            $logger = new FileLogger(path: $this->getLogPath());
        } catch (Throwable $error) {
            $this->logger->error(message: $error->getMessage());
        }

        return $logger;
    }

    /**
     * Retrieve language.
     *
     * @return Language
     */
    private function getLanguage(): Language
    {
        $code = strtok(string: $this->locale->getLocale(), token: '_');

        return Language::tryFrom(value: $code) ?? Language::en;
    }

    /**
     * Resolve cache based on whether cache is activated in Magento.
     *
     * @return CacheInterface
     */
    private function getCache(): CacheInterface
    {
        return $this->cacheState->isEnabled(
            cacheType: Resursbank::TYPE_IDENTIFIER
        ) ? $this->cache : new None();
    }
}
