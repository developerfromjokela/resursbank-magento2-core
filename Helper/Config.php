<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * NOTE: For an explanations of $scopeCode / $scopeType arguments please see
 * the AbstractConfig class.
 *
 * @package Resursbank\Core\Helper
 */
class Config extends AbstractConfig
{
    /**
     * @var string
     */
    public const GROUP = 'api';

    /**
     * @var string
     */
    public const METHODS_GROUP = 'methods';

    /**
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return int
     */
    public function getEnvironment(
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): int {
        return (int) $this->get(
            self::GROUP,
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
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): string {
        return (string) $this->get(
            self::GROUP,
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
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): string {
        return (string) $this->get(
            self::GROUP,
            'password_' . $this->getEnvironment($scopeCode, $scopeType),
            $scopeCode,
            $scopeType
        );
    }

    /**
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function isDebugEnabled(
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): bool {
        return $this->isEnabled(
            self::GROUP,
            'debug',
            $scopeCode,
            $scopeType
        );
    }

    /**
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function roundTaxPercentage(
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): bool {
        return $this->isEnabled(
            self::GROUP,
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
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
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
    public function autoSyncPaymentMethods(
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): bool {
        return $this->isEnabled(
            self::METHODS_GROUP,
            'auto_sync_method',
            $scopeCode,
            $scopeType
        );
    }

    /**
     * Retrieve configured sort order of specified payment method.
     *
     * @param string $code
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return int
     */
    public function getMethodSortOrder(
        string $code,
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): int {
        return (int) $this->get(
            self::METHODS_GROUP,
            "{$code}/sort_order",
            $scopeCode,
            $scopeType
        );
    }
}
