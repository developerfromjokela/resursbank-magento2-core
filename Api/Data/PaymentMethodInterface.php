<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Api\Data;

/**
 * @package Resursbank\Core\Api\Data
 */
interface PaymentMethodInterface
{
    /**
     * @var string
     */
    public const METHOD_ID = 'method_id';

    /**
     * @var string
     */
    public const ACCOUNT_ID = 'account_id';

    /**
     * @var string
     */
    public const IDENTIFIER = 'identifier';

    /**
     * @var string
     */
    public const CODE = 'code';

    /**
     * @var string
     */
    public const ACTIVE = 'active';

    /**
     * @var string
     */
    public const TITLE = 'title';

    /**
     * @var string
     */
    public const MIN_ORDER_TOTAL = 'min_order_total';

    /**
     * @var string
     */
    public const MAX_ORDER_TOTAL = 'max_order_total';

    /**
     * @var string
     */
    public const ORDER_STATUS = 'order_status';

    /**
     * @var string
     */
    public const RAW = 'raw';

    /**
     * @var string
     */
    public const SPECIFIC_COUNTRY = 'specificcountry';

    /**
     * @var string
     */
    public const CREATED_AT = 'created_at';

    /**
     * @var string
     */
    public const UPDATED_AT = 'updated_at';

    /**
     * Get account ID the payment method is linked to.
     *
     * @param int|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return int|null
     */
    public function getAccountId(?int $default = null): ?int;

    /**
     * Set account ID the payment method is linked to.
     *
     * @param int $accountId
     * @return self
     */
    public function setAccountId(int $accountId): self;

    /**
     * Get ID of payment method.
     *
     * @param int|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return int|null
     */
    public function getMethodId(?int $default = null): ?int;

    /**
     * Set ID of payment method.
     *
     * @param int|null $methodId - Give null to create a new entry.
     * @return self
     */
    public function setMethodId(?int $methodId): self;

    /**
     * Get payment method identifier.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getIdentifier(?string $default = null): ?string;

    /**
     * Set payment method identifier.
     *
     * @param string $identifier
     * @return self
     */
    public function setIdentifier(string $identifier): self;

    /**
     * Get payment method code.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getCode(?string $default = null): ?string;

    /**
     * Set payment method code.
     *
     * @param string $code - Must be unique.
     * @return self
     */
    public function setCode(string $code): self;

    /**
     * Get the active state of a payment method.
     *
     * @param bool|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return bool|null
     */
    public function getActive(?bool $default = null): ?bool;

    /**
     * Set the active state of a payment method.
     *
     * @param bool $state
     * @return self
     */
    public function setActive(bool $state): self;

    /**
     * Get the title of a payment method.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getTitle(?string $default = null): ?string;

    /**
     * Set title of a payment method.
     *
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self;

    /**
     * Get the minimum order total for a payment method.
     *
     * @param float|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return float|null
     */
    public function getMinOrderTotal(?float $default = null): ?float;

    /**
     * Set the minimum order total for a payment method.
     *
     * @param float $total
     * @return self
     */
    public function setMinOrderTotal(float $total): self;

    /**
     * Get the maximum order total for a payment method.
     *
     * @param float|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return float|null
     */
    public function getMaxOrderTotal(?float $default = null): ?float;

    /**
     * Set the maximum order total for a payment method.
     *
     * @param float $total
     * @return self
     */
    public function setMaxOrderTotal(float $total): self;

    /**
     * Get order status for a payment method.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getOrderStatus(?string $default = null): ?string;

    /**
     * Set order status for a payment method.
     *
     * @param string $status
     * @return self
     */
    public function setOrderStatus(string $status): self;

    /**
     * Get the raw value of a payment method.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getRaw(?string $default = null): ?string;

    /**
     * Set the raw value of a payment method.
     *
     * @param string $value
     * @return self
     */
    public function setRaw(string $value): self;

    /**
     * Get the country the payment method can be used with.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getSpecificCountry(?string $default = null): ?string;

    /**
     * Set the country the payment method can be used with.
     *
     * @param string $countryIso
     * @return self
     */
    public function setSpecificCountry(string $countryIso): self;

    /**
     * Get the time when the event was created.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getCreatedAt(?string $default = null): ?string;

    /**
     * Set the time when the event entry was created.
     *
     * @param string $timestamp - Must be a valid MySQL timestamp.
     * @return self
     */
    public function setCreatedAt(string $timestamp): self;

    /**
     * Get the time when the event was updated.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getUpdatedAt(?string $default = null): ?string;

    /**
     * Set the time when the event entry was updated.
     *
     * @param string $timestamp - Must be a valid MySQL timestamp.
     * @return self
     */
    public function setUpdatedAt(string $timestamp): self;
}
