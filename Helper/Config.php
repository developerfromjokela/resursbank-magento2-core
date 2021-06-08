<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

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
        return (string) $this->get(
            self::API_GROUP,
            'password_' . $this->getEnvironment($scopeCode, $scopeType),
            $scopeCode,
            $scopeType
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
     * Defines whether or not delete orders which were canceled during the
     * checkout process when an error occurs with the payment (for example if
     * the client fails to sign using the BankId). This ensures there are no
     * gaps in the increment id:s of the orders.
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
