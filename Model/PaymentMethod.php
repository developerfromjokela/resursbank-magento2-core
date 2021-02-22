<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model;

use JsonException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\AbstractModel;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Model\ResourceModel\PaymentMethod as Resource;
use function is_int;

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
     */
    protected function _construct(): void
    {
        $this->_init(Resource::class);
    }

    /**
     * @inheritDoc
     */
    public function getMethodId(?int $default = null): ?int
    {
        $result = $this->getData(self::METHOD_ID);

        return $result === null ? $default : (int)$result;
    }

    /**
     * @throws ValidatorException
     * @inheritDoc
     */
    public function setMethodId(?int $methodId): PaymentMethodInterface
    {
        if (is_int($methodId) && $methodId < 0) {
            throw new ValidatorException(__(
                'Method ID must be be an integer that\'s more or equal ' .
                'to 0, or null. Use null to create a new database entry.'
            ));
        }

        $this->setData(self::METHOD_ID, $methodId);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(?string $default = null): ?string
    {
        $result = $this->getData(self::IDENTIFIER);

        return $result === null ? $default : (string)$result;
    }

    /**
     * @throws ValidatorException
     * @inheritDoc
     */
    public function setIdentifier(string $identifier): PaymentMethodInterface
    {
        if ($identifier === '') {
            throw new ValidatorException(
                __('Identifier cannot be an empty string.')
            );
        }

        $this->setData(self::IDENTIFIER, $identifier);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCode(?string $default = null): ?string
    {
        $result = $this->getData(self::CODE);

        return $result === null ? $default : (string)$result;
    }

    /**
     * @throws ValidatorException
     * @inheritDoc
     */
    public function setCode(string $code): PaymentMethodInterface
    {
        if ($code === '') {
            throw new ValidatorException(
                __('Code cannot be an empty string.')
            );
        }

        $this->setData(self::CODE, $code);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getActive(?bool $default = null): ?bool
    {
        $result = $this->getData(self::ACTIVE);

        return $result === null ? $default : (bool)$result;
    }

    /**
     * @inheritDoc
     */
    public function setActive(bool $state): PaymentMethodInterface
    {
        $this->setData(self::ACTIVE, $state);

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
        $this->setData(self::TITLE, $title);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMinOrderTotal(?float $default = null): ?float
    {
        $result = $this->getData(self::MIN_ORDER_TOTAL);

        return $result === null ? $default : (float)$result;
    }

    /**
     * @throws ValidatorException
     * @inheritDoc
     */
    public function setMinOrderTotal(float $total): PaymentMethodInterface
    {
        if ($total < 0.0) {
            throw new ValidatorException(
                __('Minimum order total cannot be lower than 0.')
            );
        }

        $this->setData(self::MIN_ORDER_TOTAL, $total);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMaxOrderTotal(?float $default = null): ?float
    {
        $result = $this->getData(self::MAX_ORDER_TOTAL);

        return $result === null ? $default : (float)$result;
    }

    /**
     * @throws ValidatorException
     * @inheritDoc
     */
    public function setMaxOrderTotal(float $total): PaymentMethodInterface
    {
        if ($total < 0.0) {
            throw new ValidatorException(
                __('Maximum order total cannot be lower than 0.')
            );
        }

        $this->setData(self::MAX_ORDER_TOTAL, $total);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOrderStatus(?string $default = null): ?string
    {
        $result = $this->getData(self::ORDER_STATUS);

        return $result === null ? $default : (string)$result;
    }

    /**
     * @inheritDoc
     */
    public function setOrderStatus(string $status): PaymentMethodInterface
    {
        $this->setData(self::ORDER_STATUS, $status);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRaw(?string $default = null): ?string
    {
        $result = $this->getData(self::RAW);

        return $result === null ? $default : (string)$result;
    }

    /**
     * @throws JsonException
     * @inheritDoc
     */
    public function setRaw(string $value): PaymentMethodInterface
    {
        // We want to store the encoded value but this lets us confirm its JSON.
        json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        $this->setData(self::RAW, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSpecificCountry(?string $default = null): ?string
    {
        $result = $this->getData(self::SPECIFIC_COUNTRY);

        return $result === null ? $default : (string)$result;
    }

    /**
     * @throws ValidatorException
     * @inheritDoc
     */
    public function setSpecificCountry(
        string $countryIso
    ): PaymentMethodInterface {
        if (!preg_match('/\A[a-z]{2}\z/i', $countryIso)) {
            throw new ValidatorException(__(
                'Country ISO must be 2 characters long in the following ' .
                'format: [a-zA-Z]. Lowercase chars will be cast to uppercase.'
            ));
        }

        $this->setData(self::SPECIFIC_COUNTRY, strtoupper($countryIso));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(?int $default = null): ?int
    {
        $result = $this->getData(self::CREATED_AT);

        return $result === null ? $default : (int)$result;
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(int $timestamp): PaymentMethodInterface
    {
        $this->setData(self::CREATED_AT, $timestamp);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(?int $default = null): ?int
    {
        $result = $this->getData(self::UPDATED_AT);

        return $result === null ? $default : (int)$result;
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(int $timestamp): PaymentMethodInterface
    {
        $this->setData(self::UPDATED_AT, $timestamp);

        return $this;
    }
}
