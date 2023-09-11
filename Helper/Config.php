<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Resursbank\Ecom\Lib\Api\Environment;
use Resursbank\Ecom\Lib\Log\LogLevel;
use Resursbank\Ecom\Module\Store\Models\Store;
use Resursbank\Ecom\Module\Store\Repository;
use Resursbank\RBEcomPHP\ResursBank;
use Throwable;

/**
 * NOTE: For an explanations of $scopeCode / $scopeType arguments please see
 * the AbstractConfig class.
 */
class Config extends AbstractConfig
{
    /**
     * @var string
     */
    public const API_GROUP = 'api';

    /**
     * @var string
     */
    public const METHODS_GROUP = 'methods';

    /**
     * @var string
     */
    public const ADVANCED_GROUP = 'advanced';

    /**
     * @var string
     */
    public const LOGGING_GROUP = 'logging';

    /** @var string */
    public const API_FLOW_OPTION_MAPI = 'mapi';

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @param EncryptorInterface $encryptor
     * @param ScopeConfigInterface $reader
     * @param WriterInterface $writer
     * @param Context $context
     */
    public function __construct(
        EncryptorInterface $encryptor,
        ScopeConfigInterface $reader,
        WriterInterface $writer,
        Context $context
    ) {
        $this->encryptor = $encryptor;

        parent::__construct($reader, $writer, $context);
    }

    /**
     * Get configured API flow.
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return string
     */
    public function getFlow(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): string {
        return (string)$this->get(
            self::API_GROUP,
            'flow',
            $scopeCode,
            $scopeType
        );
    }

    /**
     * Get configured API environment.
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return int
     */
    public function getEnvironment(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): int {
        return (int)$this->get(
            self::API_GROUP,
            'environment',
            $scopeCode,
            $scopeType
        );
    }

    /**
     * Convert configured environment to MAPI value.
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return Environment
     */
    public function getApiEnvironment(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): Environment {
        $env = $this->getEnvironment(scopeCode: $scopeCode, scopeType: $scopeType);

        return $env === ResursBank::ENVIRONMENT_PRODUCTION ?
            Environment::PROD : Environment::TEST;
    }

    /**
     * Get configured API username (utilised for old APIs).
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return string
     */
    public function getUsername(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): string {
        return (string)$this->get(
            self::API_GROUP,
            'username_' . $this->getEnvironment($scopeCode, $scopeType),
            $scopeCode,
            $scopeType
        );
    }

    /**
     * Get configured API password (utilised for old APIs).
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return string
     */
    public function getPassword(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): string {
        return $this->encryptor->decrypt(
            (string)$this->get(
                self::API_GROUP,
                'password_' . $this->getEnvironment($scopeCode, $scopeType),
                $scopeCode,
                $scopeType
            )
        );
    }

    /**
     * Get configured Client ID (utilised for modern APIs).
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return string
     */
    public function getClientId(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): string {
        return (string)$this->get(
            group: self::API_GROUP,
            key: 'client_id_' . $this->getEnvironment(scopeCode: $scopeCode, scopeType: $scopeType),
            scopeCode: $scopeCode,
            scopeType: $scopeType
        );
    }

    /**
     * Get configured API secret (utilised for modern APIs).
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @param int|null $environment
     * @return string
     */
    public function getClientSecret(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES,
        ?int $environment = null
    ): string {
        /* When fetching stores we may need to resolve secret for a specified
           environment. See \Resursbank\Core\Controller\Adminhtml\Data\Stores::getRequestData */
        if ($environment === null) {
            $environment = $this->getEnvironment(
                scopeCode: $scopeCode,
                scopeType: $scopeType
            );
        }

        return $this->encryptor->decrypt(
            data: (string)$this->get(
                group: self::API_GROUP,
                key: sprintf('client_secret_%d', $environment),
                scopeCode: $scopeCode,
                scopeType: $scopeType
            )
        );
    }

    /**
     * Check whether custom logs are enabled.
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function isLoggingEnabled(
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): bool {
        return $this->isEnabled(
            group: self::LOGGING_GROUP,
            key: 'enabled',
            scopeCode: $scopeCode,
            scopeType: $scopeType
        );
    }

    /**
     * Checks if MAPI flow is active.
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function isMapiActive(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): bool {
        return $this->getFlow(
            scopeCode: $scopeCode,
            scopeType: $scopeType
        ) === self::API_FLOW_OPTION_MAPI;
    }

    /**
     * Check whether to round tax values (required for complex setups).
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function roundTaxPercentage(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): bool {
        return $this->isEnabled(
            self::ADVANCED_GROUP,
            'round_tax_percentage',
            $scopeCode,
            $scopeType
        );
    }

    /**
     * Resolve configured country code (not part of our own config).
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return string
     */
    public function getDefaultCountry(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): string {
        return (string)$this->reader->getValue(
            'general/country/default',
            $scopeType,
            $scopeCode
        );
    }

    /**
     * Whether to automatically sync data to DB (utilised for old APIs).
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function autoSyncData(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): bool {
        return $this->isEnabled(
            self::API_GROUP,
            'auto_sync_data',
            $scopeCode,
            $scopeType
        ) && !$this->isMapiActive(scopeCode: $scopeCode, scopeType: $scopeType);
    }

    /**
     * Whether to delete orders which were canceled during the checkout process
     * when an error occurs with the payment (for example if the client fails to
     * sign using the BankId). This ensures there are no gaps in the increment
     * id:s of the orders.
     *
     * NOTE: Only works if the customer is still in the same session as the
     * canceled order when it was created.
     *
     * @param null|string $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function isReuseErroneouslyCreatedOrdersEnabled(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): bool {
        return $this->isEnabled(
            self::ADVANCED_GROUP,
            'reuse_erroneously_created_orders',
            $scopeCode,
            $scopeType
        );
    }

    /**
     * Fetch configured log level.
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return LogLevel
     */
    public function getLogLevel(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): LogLevel {
        return LogLevel::from(
            value: (int)$this->get(
                group: self::ADVANCED_GROUP,
                key: 'log_level',
                scopeCode: $scopeCode,
                scopeType: $scopeType
            )
        );
    }

    /**
     * Fetch configured store id.
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return string
     */
    public function getStore(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): string {
        $result = $this->get(
            group: self::API_GROUP,
            key: 'store',
            scopeCode: $scopeCode,
            scopeType: $scopeType
        );

        if ($result === null) {
            try {
                $result = Repository::getStores()->getSingleStoreId();
            } catch (Throwable) { // phpcs:ignore
                // Circular dependency prevents logging.
            }
        }

        return (string) $result;
    }
}
