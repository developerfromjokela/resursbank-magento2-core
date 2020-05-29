<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * @package Resursbank\Core\Helper
 */
class Config extends AbstractConfig
{
    /**
     * @var string
     */
    public const GROUP = 'api';

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
            'methods',
            'auto_sync_method',
            $scopeCode,
            $scopeType
        );
    }
}
