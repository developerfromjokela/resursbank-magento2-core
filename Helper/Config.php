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
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;

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
    public const DEBUG_GROUP = 'debug';

    /** @var string  */
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
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return string
     */
    public function getFlow(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): string {
        return (string) $this->get(
            self::API_GROUP,
            'flow',
            $scopeCode,
            $scopeType
        );
    }

    /**
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return int
     */
    public function getEnvironment(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): int {
        return (int) $this->get(
            self::API_GROUP,
            'environment',
            $scopeCode,
            $scopeType
        );
    }

    /**
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return string
     */
    public function getUsername(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): string {
        return (string) $this->get(
            self::API_GROUP,
            'username_' . $this->getEnvironment($scopeCode, $scopeType),
            $scopeCode,
            $scopeType
        );
    }

    /**
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return string
     */
    public function getPassword(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): string {
        return $this->encryptor->decrypt(
            (string) $this->get(
                self::API_GROUP,
                'password_' . $this->getEnvironment($scopeCode, $scopeType),
                $scopeCode,
                $scopeType
            )
        );
    }

    /**
     * @return bool
     */
    public function isDebugEnabled(): bool
    {
        return $this->isEnabled(
            self::DEBUG_GROUP,
            'enabled',
            null,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
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
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return string
     */
    public function getDefaultCountry(
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): string {
        return (string) $this->reader->getValue(
            'general/country/default',
            $scopeType,
            $scopeCode
        );
    }

    /**
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
        );
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
}
