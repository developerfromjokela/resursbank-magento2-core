<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model;

use function is_int;
use JsonException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\AbstractModel;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Model\ResourceModel\PaymentMethod as Resource;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PaymentMethod extends AbstractModel implements PaymentMethodInterface
{
    /**
     * Initialize model.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function _construct(): void
    {
        $this->_init(resourceModel: Resource::class);
    }

    /**
     * @inheritDoc
     */
    public function getMethodId(): ?int
    {
        $result = $this->getData(key: self::METHOD_ID);

        return $result === null ? null : (int)$result;
    }

    /**
     * @inheritDoc
     *
     * @throws ValidatorException
     */
    public function setMethodId(?int $methodId): PaymentMethodInterface
    {
        if (is_int($methodId) && $methodId < 0) {
            throw new ValidatorException(__(
                'Method ID must be be an integer that\'s more or equal ' .
                'to 0, or null. Use null to create a new database entry.'
            ));
        }

        $this->setData(key: self::METHOD_ID, value: $methodId);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): ?string
    {
        $result = $this->getData(self::IDENTIFIER);

        return $result === null ? null : (string)$result;
    }

    /**
     * @inheritDoc
     *
     * @throws ValidatorException
     */
    public function setIdentifier(string $identifier): PaymentMethodInterface
    {
        if ($identifier === '') {
            throw new ValidatorException(
                __('Identifier cannot be an empty string.')
            );
        }

        $this->setData(key: self::IDENTIFIER, value: $identifier);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCode(): ?string
    {
        $result = $this->getData(self::CODE);

        return $result === null ? null : (string)$result;
    }

    /**
     * @inheritDoc
     *
     * @throws ValidatorException
     */
    public function setCode(string $code): PaymentMethodInterface
    {
        if ($code === '') {
            throw new ValidatorException(
                __('Code cannot be an empty string.')
            );
        }

        $this->setData(key: self::CODE, value: $code);

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @phpstan-ignore-next-line Incompatible magic Magento getter.
     */
    public function getActive(): ?bool
    {
        $result = $this->getData(self::ACTIVE);

        return $result === null ? null : (bool)$result;
    }

    /**
     * @inheritDoc
     */
    public function setActive(bool $state): PaymentMethodInterface
    {
        $this->setData(key: self::ACTIVE, value: $state);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTitle(?string $default = null): ?string
    {
        $result = $this->getData(self::TITLE);

        return $result === null ? $default : (string)$result;
    }

    /**
     * @inheritDoc
     */
    public function setTitle(string $title): PaymentMethodInterface
    {
        $this->setData(key: self::TITLE, value: $title);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder(?int $default = null): ?int
    {
        $result = $this->getData(self::SORT_ORDER);

        return $result === null ? $default : (int)$result;
    }

    /**
     * @inheritDoc
     *
     * @throws ValidatorException
     */
    public function setSortOrder(int $order): PaymentMethodInterface
    {
        if ($order < 0) {
            throw new ValidatorException(
                __('Sort order cannot be lower than 0.')
            );
        }

        $this->setData(key: self::SORT_ORDER, value: $order);

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @phpstan-ignore-next-line Incompatible magic Magento getter.
     */
    public function getMinOrderTotal(): ?float
    {
        $result = $this->getData(self::MIN_ORDER_TOTAL);

        return $result === null ? null : (float)$result;
    }

    /**
     * @inheritDoc
     *
     * @throws ValidatorException
     */
    public function setMinOrderTotal(float $total): PaymentMethodInterface
    {
        if ($total < 0.0) {
            throw new ValidatorException(
                __('Minimum order total cannot be lower than 0.')
            );
        }

        $this->setData(key: self::MIN_ORDER_TOTAL, value: $total);

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @phpstan-ignore-next-line Incompatible magic Magento getter.
     */
    public function getMaxOrderTotal(): ?float
    {
        $result = $this->getData(self::MAX_ORDER_TOTAL);

        return $result === null ? null : (float)$result;
    }

    /**
     * @inheritDoc
     *
     * @throws ValidatorException
     */
    public function setMaxOrderTotal(float $total): PaymentMethodInterface
    {
        if ($total < 0.0) {
            throw new ValidatorException(
                __('Maximum order total cannot be lower than 0.')
            );
        }

        $this->setData(key: self::MAX_ORDER_TOTAL, value: $total);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatus(): ?string
    {
        $result = $this->getData(self::ORDER_STATUS);

        return $result === null ? null : (string)$result;
    }

    /**
     * @inheritDoc
     */
    public function setOrderStatus(string $status): PaymentMethodInterface
    {
        $this->setData(key: self::ORDER_STATUS, value: $status);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRaw(): ?string
    {
        $result = $this->getData(self::RAW);

        return $result === null ? null : (string)$result;
    }

    /**
     * @inheritDoc
     *
     * @throws JsonException
     * @noinspection PhpMultipleClassDeclarationsInspection
     */
    public function setRaw(string $value): PaymentMethodInterface
    {
        if ($value === '') {
            return $this;
        }

        // We want to store the encoded value but this lets us confirm its JSON.
        json_decode(
            json: $value,
            associative: true,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        $this->setData(key: self::RAW, value: $value);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws JsonException
     * @noinspection PhpMultipleClassDeclarationsInspection
     */
    public function getType(): ?string
    {
        $raw = (string) $this->getRaw();

        if ($raw === '') {
            return null;
        }

        $raw = json_decode(
            json: $raw,
            associative: true,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        return $raw['type'] ?? null;
    }

    /**
     * @inheritdoc
     *
     * @throws JsonException
     * @noinspection PhpMultipleClassDeclarationsInspection
     */
    public function getSpecificType(): ?string
    {
        $raw = (string) $this->getRaw();

        if ($raw === '') {
            return null;
        }

        $raw = json_decode(
            json: $raw,
            associative: true,
            depth: 512,
            flags: JSON_THROW_ON_ERROR
        );

        return $raw['specificType'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getSpecificCountry(): ?string
    {
        $result = $this->getData(self::SPECIFIC_COUNTRY);

        return $result === null ? null : (string)$result;
    }

    /**
     * @inheritdoc
     *
     * @throws ValidatorException
     */
    public function setSpecificCountry(
        string $countryIso
    ): PaymentMethodInterface {
        if (!preg_match(pattern: '/\A[a-z]{2}\z/i', subject: $countryIso)) {
            throw new ValidatorException(__(
                'Country ISO must be 2 characters long in the following ' .
                'format: [a-zA-Z]. Lowercase chars will be cast to uppercase.'
            ));
        }

        $this->setData(
            key: self::SPECIFIC_COUNTRY,
            value: strtoupper($countryIso)
        );

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?int
    {
        $result = $this->getData(self::CREATED_AT);

        return $result === null ? null : (int)$result;
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(int $timestamp): PaymentMethodInterface
    {
        $this->setData(key: self::CREATED_AT, value: $timestamp);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): ?int
    {
        $result = $this->getData(self::UPDATED_AT);

        return $result === null ? null : (int)$result;
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(int $timestamp): PaymentMethodInterface
    {
        $this->setData(key: self::UPDATED_AT, value: $timestamp);

        return $this;
    }
}
