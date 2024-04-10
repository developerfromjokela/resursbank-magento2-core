<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Locale\Resolver as Locale;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Resursbank\Core\Model\Cache\Ecom as Cache;
use Resursbank\Core\Model\Cache\Type\Resursbank as ResursbankCacheType;
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
use Resursbank\Ecom\Module\PaymentHistory\DataHandler\DataHandlerInterface;
use Resursbank\Ecom\Module\PaymentHistory\DataHandler\VoidDataHandler;
use Throwable;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Basic API integration.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Ecom extends AbstractHelper
{
    /**
     * @param Context $context
     * @param Log $log
     * @param Scope $scope
     * @param Config $config
     * @param DirectoryList $directoryList
     * @param File $file
     * @param LoggerInterface $logger
     * @param Cache $cache
     * @param ProductMetadataInterface $productMetadata
     * @param Version $version
     * @param Locale $locale
     * @param StateInterface $cacheState
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        private readonly Log $log,
        private readonly Scope $scope,
        private readonly Config $config,
        private readonly DirectoryList $directoryList,
        private readonly File $file,
        private readonly LoggerInterface $logger,
        private readonly Cache $cache,
        private readonly ProductMetadataInterface $productMetadata,
        private readonly Version $version,
        private readonly Locale $locale,
        private readonly StateInterface $cacheState
    ) {
        parent::__construct(context: $context);
    }

    /**
     * Setup ECom connection.
     *
     * @param Jwt|null $jwtAuth
     * @param Environment|null $env
     * @param string|null $scopeCode
     * @param string|null $scopeType
     * @param EcomScope|null $scope
     * @return void
     */
    public function connect(
        ?Jwt $jwtAuth = null,
        ?Environment $env = null,
        ?string $scopeCode = null,
        ?string $scopeType = null,
        ?EcomSCope $scope = null
    ): void {
        try {
            $scopeType = $scopeType ?? $this->scope->getType();
            $scopeCode = $scopeCode ?? $this->scope->getId();

            if ($env === null) {
                $env = $this->config->getApiEnvironment(
                    scopeCode: $scopeCode,
                    scopeType: $scopeType
                );
            }

            $clientId = $this->config->getClientId(
                scopeCode: $scopeCode,
                scopeType: $scopeType
            );

            $clientSecret = $this->config->getClientSecret(
                scopeCode: $scopeCode,
                scopeType: $scopeType
            );

            if ($jwtAuth === null && $clientId !== '' && $clientSecret !== '') {
                $jwtAuth = new Jwt(
                    clientId: $clientId,
                    clientSecret: $clientSecret,
                    scope: $scope ?? $this->getScope(environment: $env),
                    grantType: GrantType::CREDENTIALS,
                );
            }
            $logPath = $this->getLogPath();

            // phpcs:ignore
            if (!is_dir(filename: $logPath)) {
                $this->file->mkdir(dir: $logPath);
            }

            EcomConfig::setup(
                logger: $this->getLogger(),
                cache: $this->getCache(),
                jwtAuth: $jwtAuth,
                paymentHistoryDataHandler: $this->getPaymentHistoryDataHandler(),
                logLevel: $this->config->getLogLevel(
                    scopeCode: $scopeCode,
                    scopeType: $scopeType
                ),
                userAgent: $this->getUserAgent(),
                isProduction: $env === Environment::PROD,
                language: $this->getLanguage()
            );
        } catch (Throwable $e) {
            $this->log->exception(error: $e);
        }
    }

    /**
     * Place-holder for plugins in separate modules.
     *
     * @return DataHandlerInterface
     */
    public function getPaymentHistoryDataHandler(): DataHandlerInterface
    {
        return new VoidDataHandler();
    }

    /**
     * Fetch scope for specified environment.
     *
     * @param Environment $environment
     * @return EcomScope
     */
    public function getScope(Environment $environment): EcomScope
    {
        return $environment === Environment::PROD ?
            EcomScope::MERCHANT_API :
            EcomScope::MOCK_MERCHANT_API;
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
     * @throws LocalizedException
     * @throws NoSuchEntityException
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

        return Language::tryFrom(value: $code) ?? Language::EN;
    }

    /**
     * Resolve cache based on whether cache is activated in Magento.
     *
     * @return CacheInterface
     */
    private function getCache(): CacheInterface
    {
        return $this->cacheState->isEnabled(
            cacheType: ResursbankCacheType::TYPE_IDENTIFIER
        ) ? $this->cache : new None();
    }

    /**
     * Get user agent.
     *
     * @return string
     */
    public function getUserAgent(): string
    {
        return sprintf(
            'Magento %s | Resursbank_Core %s |',
            $this->productMetadata->getVersion(),
            $this->version->getComposerVersion(module: 'Resursbank_Core')
        );
    }

    /**
     * Configure Ecom to utilise API account associated with supplied entity.
     *
     * Since the original connect() method above will execute early in the
     * request cycle it will use credentials from the Default config scope. When
     * viewing/manipulating the payment through the admin panel, we need to
     * re-configure Ecom to use the API account associated with the order
     * instead, to support setups using multiple accounts.
     *
     * @param OrderInterface|Invoice|Creditmemo $entity
     * @return void
     */
    public function connectAftershop(
        OrderInterface|Invoice|Creditmemo $entity
    ): void {
        $this->connect(
            scopeCode: $entity->getStore()->getCode(),
            scopeType: ScopeInterface::SCOPE_STORES
        );
    }
}
