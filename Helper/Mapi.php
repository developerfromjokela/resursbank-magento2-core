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
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Locale\Resolver as Locale;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Resursbank\Core\Model\Cache\Ecom as Cache;
use Resursbank\Core\Model\Cache\Type\Resursbank as ResursbankCacheType;
use Resursbank\Core\Model\Payment\Resursbank;
use Resursbank\Core\Model\PaymentMethod;
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
use Resursbank\Ecom\Lib\Model\PaymentMethod as EcomPaymentMethod;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;
use Resursbank\Ecom\Module\PaymentMethod\Repository as EcomRepository;
use Throwable;
use JsonException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\ValidatorException;
use Resursbank\Core\Model\PaymentMethodFactory;

use function str_starts_with;

/**
 * Mapi related business logic.
 */
class Mapi extends AbstractHelper
{
    /**
     * @param Context $context
     * @param PaymentMethodFactory $methodFactory
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
     */
    public function __construct(
        Context $context,
        private readonly PaymentMethodFactory $methodFactory,
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
     * @return void
     */
    public function connect(
        ?Jwt $jwtAuth = null,
        ?Environment $env = null
    ): void {
        try {
            if ($env === null) {
                $env = $this->config->getMapiEnvironment(
                    scopeCode: $this->scope->getId(),
                    scopeType: $this->scope->getType()
                );
            }

            if ($jwtAuth === null) {
                $jwtAuth = new Jwt(
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
                );
            }
            $logPath = $this->getLogPath();

            if (!is_dir(filename: $logPath)) {
                $this->file->mkdir(dir: $logPath);
            }

            EcomConfig::setup(
                logger: $this->getLogger(),
                cache: $this->getCache(),
                jwtAuth: $jwtAuth,
                logLevel: $this->config->getLogLevel(
                    scopeCode: $this->scope->getId(),
                    scopeType: $this->scope->getType()
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
            cacheType: ResursbankCacheType::TYPE_IDENTIFIER
        ) ? $this->cache : new None();
    }

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return sprintf(
            'Magento %s | Resursbank_Core_MAPI %s |',
            $this->productMetadata->getVersion(),
            $this->version->getComposerVersion(module: 'Resursbank_Core')
        );
    }

    /**
     * Resolve MAPI payment method converted to PaymentMethod
     *
     * @param int|string $id
     * @param string $storeId
     * @return PaymentMethod|null
     */
    public function getMapiMethodById(
        int|string $id,
        string $storeId
    ): ?PaymentMethod {
        try {
            return $this->convertMapiMethod(
                method: EcomRepository::getById(
                    storeId: $storeId,
                    paymentMethodId: $id
                )
            );
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return null;
    }

    /**
     * Resolve list of MAPI payment methods.
     *
     * @param string $storeId
     * @return array
     */
    public function getMapiMethods(string $storeId): array
    {
        $result = [];

        try {
            $methods = EcomRepository::getPaymentMethods(storeId: $storeId);

            foreach ($methods as $method) {
                $result[] = $this->convertMapiMethod(method: $method);
            }
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return $result;
    }

    /**
     * Convert EcomPaymentMethod to PaymentMethod.
     *
     * @param EcomPaymentMethod $method
     * @return PaymentMethod
     * @throws JsonException
     * @throws ValidatorException
     */
    private function convertMapiMethod(
        EcomPaymentMethod $method
    ): PaymentMethod {
        $result = $this->methodFactory->create();
        $result->setCode(code: Resursbank::CODE_PREFIX . $method->id);
        $result->setActive(state: true);
        $result->setSortOrder(order: $method->sortOrder);
        $result->setTitle(title: $method->name);
        $result->setMinOrderTotal(total: $method->minPurchaseLimit);
        $result->setMaxOrderTotal(total: $method->maxPurchaseLimit);
        $result->setOrderStatus(status: Order::STATE_PENDING_PAYMENT);
        $result->setRaw(value: json_encode(value: [
            'type' => $this->getMapiType(type: $method->type),
            'specificType' => $this->getMapiSpecificType(type: $method->type),
            'customerType' => $this->getCustomerTypes(method: $method)
        ], flags: JSON_THROW_ON_ERROR));

        return $result;
    }

    /**
     * Resolve array of available customer types for MAPI method.
     *
     * @param EcomPaymentMethod $method
     * @return array
     */
    private function getCustomerTypes(EcomPaymentMethod $method): array
    {
        $result = [];

        if ($method->enabledForLegalCustomer) {
            $result[] = 'LEGAL';
        }

        if ($method->enabledForNaturalCustomer) {
            $result[] = 'NATURAL';
        }

        return $result;
    }

    /**
     * Convert MAPI "type" to old "specificType". Essentially, drop the prefix
     * "RESURS_" if it exists, this will match the "specificType" property from
     * the deprecated APIs.
     *
     * @param Type $type
     * @return string
     */
    private function getMapiSpecificType(Type $type): string
    {
        return (str_starts_with(haystack: $type->value, needle: 'RESURS_')) ?
            substr(string: $type->value, offset: 8) : $type->value;
    }

    /**
     * Resolve "PAYMENT_PROVIDER" as type for external payment methods to mimic
     * some behavior established by the deprecated API integrations.
     *
     * @param Type $type
     * @return string
     */
    private function getMapiType(Type $type): string
    {
        return str_starts_with(haystack: $type->value, needle: 'RESURS_') ?
            'INTERNAL' : 'PAYMENT_PROVIDER';
    }
}
