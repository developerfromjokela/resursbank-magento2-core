<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api\Payment;

use Exception;
use InvalidArgumentException;
use Resursbank\Core\Model\Api\Payment\Item\Validation\ArtNo;
use Resursbank\Core\Model\Api\Payment\Item\Validation\Description;
use Resursbank\Core\Model\Api\Payment\Item\Validation\Quantity;
use Resursbank\Core\Model\Api\Payment\Item\Validation\Type;
use Resursbank\Core\Model\Api\Payment\Item\Validation\UnitAmountWithoutVat;
use Resursbank\Core\Model\Api\Payment\Item\Validation\UnitMeasure;
use Resursbank\Core\Model\Api\Payment\Item\Validation\VatPct;

/**
 * Information representing a single line in a payment payload.
 *
 * NOTE: all validation routines are separated into individual classes because
 * they all are responsible for their own ruleset and routine. All validation
 * classes are called statically to avoid dependencies in this class.
 */
class Item
{
    /**
     * Unit measure value.
     */
    public const UNIT_MEASURE = 'st';

    /**
     * Data key representing SKU.
     *
     * @var string
     */
    public const KEY_ART_NO = 'artNo';

    /**
     * Data key representing description.
     *
     * @var string
     */
    public const KEY_DESCRIPTION = 'description';

    /**
     * Data key representing quantity.
     *
     * @var string
     */
    public const KEY_QUANTITY = 'quantity';

    /**
     * Data key representing unit measure.
     *
     * @var string
     */
    public const KEY_UNIT_MEASURE = 'unitMeasure';

    /**
     * Data key representing price excl. tax.
     *
     * @var string
     */
    public const KEY_UNIT_AMOUNT_WITHOUT_VAT = 'unitAmountWithoutVat';

    /**
     * Data key representing tax percentage.
     *
     * @var string
     */
    public const KEY_VAT_PCT = 'vatPct';

    /**
     * Data key representing item type.
     *
     * @var string
     */
    public const KEY_TYPE = 'type';

    /**
     * Data key representing total incl. tax.
     *
     * @var string
     */
    public const KEY_TOTAL_AMOUNT_INCL_VAT = 'totalAmountInclVat';

    /**
     * Shipping item type identifier.
     *
     * @var string
     */
    public const TYPE_SHIPPING = 'SHIPPING_FEE';

    /**
     * General item type identifier.
     *
     * @var string
     */
    public const TYPE_PRODUCT = 'ORDER_LINE';

    /**
     * Discount item type identifier.
     *
     * @var string
     */
    public const TYPE_DISCOUNT = 'DISCOUNT';

    /**
     * @var string
     */
    private string $artNo = '';

    /**
     * @var string
     */
    private string $description = '';

    /**
     * @var float
     */
    private float $quantity = 0.0;

    /**
     * Unit measurement, for example "kg" or "cup".
     *
     * NOTE: This value is always required, even if the item specification does
     * not rely on a unit measurement value.
     *
     * @var string
     */
    private string $unitMeasure = self::UNIT_MEASURE;

    /**
     * Unit price without VAT (excl. tax).
     *
     * @var float
     */
    private float $unitAmountWithoutVat = 0.0;

    /**
     * Tax percentage.
     *
     * @var int
     */
    private int $vatPct = 0;

    /**
     * Item type specification.
     *
     * ORDER_LINE = general, usually products.
     * SHIPPING_FEE = shipping.
     * DISCOUNT = discount (negative).
     *
     * @var string
     */
    private string $type = self::TYPE_PRODUCT;

    /**
     * Total amount including vat.
     *
     * @var float
     */
    private float $totalAmountInclVat = 0.0;

    /**
     * @param string $artNo
     * @param string $description
     * @param float $quantity
     * @param string $unitMeasure
     * @param float $unitAmountWithoutVat
     * @param int $vatPct
     * @param string $type
     * @param float $totalAmountInclVat
     * @param ArtNo $artNoValidator
     * @param Description $descriptionValidator
     * @param Quantity $quantityValidator
     * @param UnitMeasure $unitMeasureValidator
     * @param UnitAmountWithoutVat $amountValidator
     * @param VatPct $vatPctValidator
     * @param Type $typeValidator
     * @throws Exception
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        string $artNo,
        string $description,
        float $quantity,
        string $unitMeasure,
        float $unitAmountWithoutVat,
        int $vatPct,
        string $type,
        float $totalAmountInclVat,
        private readonly ArtNo $artNoValidator,
        private readonly Description $descriptionValidator,
        private readonly Quantity $quantityValidator,
        private readonly UnitMeasure $unitMeasureValidator,
        private readonly UnitAmountWithoutVat $amountValidator,
        private readonly VatPct $vatPctValidator,
        private readonly Type $typeValidator
    ) {
        $this->setArtNo(value: $artNo)
            ->setDescription(value: $description)
            ->setQuantity(value: $quantity)
            ->setUnitMeasure(value: $unitMeasure)
            ->setUnitAmountWithoutVat(value: $unitAmountWithoutVat)
            ->setVatPct(value: $vatPct)
            ->setType(value: $type)
            ->setTotalAmountInclVat(value: $totalAmountInclVat);
    }

    /**
     * Set article number
     *
     * @param string $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setArtNo(string $value): Item
    {
        $this->artNoValidator->validate(value: $value);

        $this->artNo = $value;

        return $this;
    }

    /**
     * Get article number
     *
     * @return string
     */
    public function getArtNo(): string
    {
        return $this->artNo;
    }

    /**
     * Set description
     *
     * @param string $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setDescription(string $value): Item
    {
        $this->descriptionValidator->validate(value: $value);

        $this->description = $value;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set quantity
     *
     * @param float $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setQuantity(float $value): Item
    {
        $this->quantityValidator->validate(value: $value);

        $this->quantity = $value;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * Set unitMeasure
     *
     * @param string $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setUnitMeasure(string $value): Item
    {
        $this->unitMeasureValidator->validate(value: $value);

        $this->unitMeasure = $value;

        return $this;
    }

    /**
     * Get unitMeasure
     *
     * @return string
     */
    public function getUnitMeasure(): string
    {
        return $this->unitMeasure;
    }

    /**
     * Set unit amount excluding VAT
     *
     * @param float $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setUnitAmountWithoutVat(float $value): Item
    {
        $this->amountValidator->validate(value: $value);

        $this->unitAmountWithoutVat = $value;

        return $this;
    }

    /**
     * Get unit amount excluding VAT
     *
     * @return float
     */
    public function getUnitAmountWithoutVat(): float
    {
        return $this->unitAmountWithoutVat;
    }

    /**
     * Set VAT in percent
     *
     * @param int $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setVatPct(int $value): Item
    {
        $this->vatPctValidator->validate(value: $value);

        $this->vatPct = $value;

        return $this;
    }

    /**
     * Get VAT in percent
     *
     * @return int
     */
    public function getVatPct(): int
    {
        return $this->vatPct;
    }

    /**
     * Set type
     *
     * @param string $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setType(string $value): Item
    {
        $this->typeValidator->validate(value: $value);

        $this->type = $value;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set total amount including VAT
     *
     * @param float $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setTotalAmountInclVat(float $value): Item
    {
        $this->amountValidator->validate(value: $value);

        $this->totalAmountInclVat = $value;

        return $this;
    }

    /**
     * Get total amount including VAT
     *
     * @return float
     */
    public function getTotalAmountInclVat(): float
    {
        return $this->totalAmountInclVat;
    }

    /**
     * Retrieve object data converted to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::KEY_ART_NO => $this->getArtNo(),
            self::KEY_DESCRIPTION => $this->getDescription(),
            self::KEY_QUANTITY => $this->getQuantity(),
            self::KEY_UNIT_MEASURE => $this->getUnitMeasure(),
            self::KEY_UNIT_AMOUNT_WITHOUT_VAT => $this->getUnitAmountWithoutVat(),
            self::KEY_VAT_PCT => $this->getVatPct(),
            self::KEY_TYPE => $this->getType(),
            self::KEY_TOTAL_AMOUNT_INCL_VAT => $this->getTotalAmountInclVat()
        ];
    }
}
