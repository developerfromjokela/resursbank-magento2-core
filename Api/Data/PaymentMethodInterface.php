<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Api\Data;

interface PaymentMethodInterface
{
    /**
     * @var string
     */
    public const METHOD_ID = 'method_id';

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
    public const SORT_ORDER = 'sort_order';

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
     * @param int|null $methodId - Use null to create a new entry.
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
     * Whether or not the payment method is active.
     *
     * @param bool|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return bool|null
     */
    public function getActive(?bool $default = null): ?bool;

    /**
     * Set whether or not payment method is active.
     *
     * @param bool $state
     * @return self
     */
    public function setActive(bool $state): self;

    /**
     * Get payment method title.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getTitle(?string $default = null): ?string;

    /**
     * Set payment method title.
     *
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self;

    /**
     * Get payment method sort order.
     *
     * @param int|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return int|null
     */
    public function getSortOrder(?int $default = null): ?int;

    /**
     * Set payment method sort order.
     *
     * @param int $order
     * @return self
     */
    public function setSortOrder(int $order): self;

    /**
     * Get payment method minimum order total (cart total required to make the
     * payment method available at checkout).
     *
     * @param float|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return float|null
     */
    public function getMinOrderTotal(?float $default = null): ?float;

    /**
     * Set payment method minimum order total (cart total required to make
     * payment method available at checkout).
     *
     * @param float $total
     * @return self
     */
    public function setMinOrderTotal(float $total): self;

    /**
     * Get payment method maximum order total (cart total limit to make payment
     * method available at checkout).
     *
     * @param float|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return float|null
     */
    public function getMaxOrderTotal(?float $default = null): ?float;

    /**
     * Set payment method maximum order total (cart total limit to make payment
     * method available at checkout).
     *
     * @param float $total
     * @return self
     */
    public function setMaxOrderTotal(float $total): self;

    /**
     * Get payment method default order status.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getOrderStatus(?string $default = null): ?string;

    /**
     * Set payment method default order status.
     *
     * @param string $status
     * @return self
     */
    public function setOrderStatus(string $status): self;

    /**
     * Get complete raw API data defining the method at Resurs Bank.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getRaw(?string $default = null): ?string;

    /**
     * Set complete raw API data defining the method at Resurs Bank.
     *
     * @param string $value
     * @return self
     */
    public function setRaw(string $value): self;

    /**
     * From the raw API response, fetch the method type.
     *
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * Get payment method country restriction.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getSpecificCountry(?string $default = null): ?string;

    /**
     * Set payment method country restriction.
     *
     * @param string $countryIso
     * @return self
     */
    public function setSpecificCountry(string $countryIso): self;

    /**
     * Get entry creation time.
     *
     * @param int|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return int|null
     */
    public function getCreatedAt(?int $default = null): ?int;

    /**
     * Set entry creation time.
     *
     * @param int $timestamp - Must be a valid MySQL timestamp.
     * @return self
     */
    public function setCreatedAt(int $timestamp): self;

    /**
     * Get entry update time.
     *
     * @param int|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return int|null
     */
    public function getUpdatedAt(?int $default = null): ?int;

    /**
     * Set entry update time.
     *
     * @param int $timestamp - Must be a valid MySQL timestamp.
     * @return self
     */
    public function setUpdatedAt(int $timestamp): self;
}
