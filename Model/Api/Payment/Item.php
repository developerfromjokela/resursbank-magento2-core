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
     * @var ArtNo
     */
    private ArtNo $artNoValidator;

    /**
     * @var Description
     */
    private Description $descriptionValidator;

    /**
     * @var Quantity
     */
    private Quantity $quantityValidator;

    /**
     * @var UnitMeasure
     */
    private UnitMeasure $unitMeasureValidator;

    /**
     * @var UnitAmountWithoutVat
     */
    private UnitAmountWithoutVat $amountValidator;

    /**
     * @var VatPct
     */
    private VatPct $vatPctValidator;

    /**
     * @var Type
     */
    private Type $typeValidator;

    /**
     * @param string $artNo
     * @param string $description
     * @param float $quantity
     * @param string $unitMeasure
     * @param float $unitAmountWithoutVat
     * @param int $vatPct
     * @param string $type
     * @param ArtNo $artNoValidator
     * @param Description $descriptionValidator
     * @param Quantity $quantityValidator
     * @param UnitMeasure $unitMeasureValidator
     * @param UnitAmountWithoutVat $amountValidator
     * @param VatPct $vatPctValidator
     * @param Type $typeValidator
     * @throws Exception
     * @throws InvalidArgumentException
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
        ArtNo $artNoValidator,
        Description $descriptionValidator,
        Quantity $quantityValidator,
        UnitMeasure $unitMeasureValidator,
        UnitAmountWithoutVat $amountValidator,
        VatPct $vatPctValidator,
        Type $typeValidator
    ) {
        $this->artNoValidator = $artNoValidator;
        $this->descriptionValidator = $descriptionValidator;
        $this->quantityValidator = $quantityValidator;
        $this->unitMeasureValidator = $unitMeasureValidator;
        $this->amountValidator = $amountValidator;
        $this->vatPctValidator = $vatPctValidator;
        $this->typeValidator = $typeValidator;

        $this->setArtNo($artNo)
            ->setDescription($description)
            ->setQuantity($quantity)
            ->setUnitMeasure($unitMeasure)
            ->setUnitAmountWithoutVat($unitAmountWithoutVat)
            ->setVatPct($vatPct)
            ->setType($type);
    }

    /**
     * @param string $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setArtNo(string $value): Item
    {
        $this->artNoValidator->validate($value);

        $this->artNo = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getArtNo(): string
    {
        return $this->artNo;
    }

    /**
     * @param string $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setDescription(string $value): Item
    {
        $this->descriptionValidator->validate($value);

        $this->description = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param float $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setQuantity(float $value): Item
    {
        $this->quantityValidator->validate($value);

        $this->quantity = $value;

        return $this;
    }

    /**
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * @param string $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setUnitMeasure(string $value): Item
    {
        $this->unitMeasureValidator->validate($value);

        $this->unitMeasure = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnitMeasure(): string
    {
        return $this->unitMeasure;
    }

    /**
     * @param float $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setUnitAmountWithoutVat(float $value): Item
    {
        $this->amountValidator->validate($value);

        $this->unitAmountWithoutVat = $value;

        return $this;
    }

    /**
     * @return float
     */
    public function getUnitAmountWithoutVat(): float
    {
        return $this->unitAmountWithoutVat;
    }

    /**
     * @param int $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setVatPct(int $value): Item
    {
        $this->vatPctValidator->validate($value);

        $this->vatPct = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getVatPct(): int
    {
        return $this->vatPct;
    }

    /**
     * @param string $value
     * @return Item
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function setType(string $value): Item
    {
        $this->typeValidator->validate($value);

        $this->type = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Retrieve object data converted to array.
     *
     * @return array<mixed>
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
            self::KEY_TYPE => $this->getType()
        ];
    }
}
