<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Test\Unit\Model\Api\Payment;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Resursbank\Core\Model\Api\Payment\Item;
use Resursbank\Core\Model\Api\Payment\Item\Validation\ArtNo;
use Resursbank\Core\Model\Api\Payment\Item\Validation\Description;
use Resursbank\Core\Model\Api\Payment\Item\Validation\Quantity;
use Resursbank\Core\Model\Api\Payment\Item\Validation\Type;
use Resursbank\Core\Model\Api\Payment\Item\Validation\UnitAmountWithoutVat;
use Resursbank\Core\Model\Api\Payment\Item\Validation\UnitMeasure;
use Resursbank\Core\Model\Api\Payment\Item\Validation\VatPct;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class ItemTest extends TestCase
{
    /**
     * @var Item
     */
    private Item $item;

    /**
     * @var array<string, mixed>
     */
    private $data = [
        Item::KEY_ART_NO => 'pastrami',
        Item::KEY_DESCRIPTION => 'A very good thing',
        Item::KEY_QUANTITY => 91854.88,
        Item::KEY_UNIT_MEASURE => 'tub',
        Item::KEY_UNIT_AMOUNT_WITHOUT_VAT => 5.67,
        Item::KEY_VAT_PCT => 25,
        Item::KEY_TYPE => Item::TYPE_DISCOUNT
    ];

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function setUp(): void
    {

        $artNoValidator = new ArtNo();
        $descriptionValidator = new Description();
        $quantityValidator = new Quantity();
        $unitMeasureValidator = new UnitMeasure();
        $amountValidator = new UnitAmountWithoutVat();
        $vatPctValidator = new VatPct();
        $typeValidator = new Type();

        $this->item = new Item(
            $this->data[Item::KEY_ART_NO],
            $this->data[Item::KEY_DESCRIPTION],
            $this->data[Item::KEY_QUANTITY],
            $this->data[Item::KEY_UNIT_MEASURE],
            $this->data[Item::KEY_UNIT_AMOUNT_WITHOUT_VAT],
            $this->data[Item::KEY_VAT_PCT],
            $this->data[Item::KEY_TYPE],
            $artNoValidator,
            $descriptionValidator,
            $quantityValidator,
            $unitMeasureValidator,
            $amountValidator,
            $vatPctValidator,
            $typeValidator
        );
    }

    /**
     * Assert that values assigned to properties through the constructor were
     * actually assigned properly.
     *
     * @return void
     */
    public function testValuesAssignedByConstructor(): void
    {
        static::assertSame(
            $this->item->getArtNo(),
            $this->data[Item::KEY_ART_NO]
        );

        static::assertSame(
            $this->item->getDescription(),
            $this->data[Item::KEY_DESCRIPTION]
        );

        static::assertSame(
            $this->item->getQuantity(),
            $this->data[Item::KEY_QUANTITY]
        );

        static::assertSame(
            $this->item->getUnitMeasure(),
            $this->data[Item::KEY_UNIT_MEASURE]
        );

        static::assertSame(
            $this->item->getUnitAmountWithoutVat(),
            $this->data[Item::KEY_UNIT_AMOUNT_WITHOUT_VAT]
        );

        static::assertSame(
            $this->item->getVatPct(),
            $this->data[Item::KEY_VAT_PCT]
        );

        static::assertSame(
            $this->item->getType(),
            $this->data[Item::KEY_TYPE]
        );
    }

    /**
     * Assert that the setArtNo method will assign a value to the artNo prop.
     *
     * @return void
     * @throws Exception
     */
    public function testSetArtNo(): void
    {
        $this->item->setArtNo('geno1');

        self::assertSame('geno1', $this->item->getArtNo());
    }

    /**
     * Assert that the setArtNo method allows values with a length of 1.
     *
     * @return void
     * @throws Exception
     */
    public function testSetArtNoMinLength(): void
    {
        $this->item->setArtNo('t');

        static::assertSame('t', $this->item->getArtNo());
    }

    /**
     * Assert that the setArtNo method allows values with a length of 100.
     *
     * @return void
     * @throws Exception
     */
    public function testSetArtNoMaxLength(): void
    {
        $test = 'asdsgdfhdfghdgfghergegf343fg3rg3rg3rg3rg3g3fgwf22f3gerg3g' .
            'r3rg3rgegerg4gefgedgdfgdfgdfgdfgdfgdfgdfgdf';

        $this->item->setArtNo($test);

        static::assertSame($test, $this->item->getArtNo());
    }

    /**
     * Assert that an instance of InvalidArgumentException is thrown if the
     * setArtNo method is provided a value containing illegal chars.
     *
     * @return void
     * @throws Exception
     */
    public function testSetArtNoThrowsOnInvalidChars(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setArtNo('Only a-z, 0-9 please');
    }

    /**
     * Assert that an instance of InvalidArgumentException is thrown if the
     * setArtNo method is provided a value which contain too many chars.
     *
     * @return void
     * @throws Exception
     */
    public function testSetArtNoThrowsOnTooLongValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setArtNo(
            'dslfndfjlgjog3jrouh4g9ujofjowdlfjojdo029868kgjefghkuhdfksdkfhi' .
            '43958t94t9grigh49uht9ghufkhguihuiddiugfhdskgh'
        );
    }

    /**
     * Assert that an instance of InvalidArgumentException is thrown if the
     * setArtNo method is provided a value which contain too few chars.
     *
     * @return void
     * @throws Exception
     */
    public function testSetArtNoThrowsOnTooShortValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setArtNo('');
    }

    /**
     * Assert that the setDescription method will assign a value to the
     * description prop.
     *
     * @return void
     * @throws Exception
     */
    public function testSetDescription(): void
    {
        $this->item->setDescription('2 ducks no more, no less.');

        self::assertSame(
            '2 ducks no more, no less.',
            $this->item->getDescription()
        );
    }

    /**
     * Assert that the setDescription method allows values with a length of 1.
     *
     * @return void
     * @throws Exception
     */
    public function testSetDescriptionMinLength(): void
    {
        $this->item->setDescription('b');

        static::assertSame('b', $this->item->getDescription());
    }

    /**
     * Assert that the setDescription method allows values with a length of 255.
     *
     * @return void
     * @throws Exception
     */
    public function testSetDescriptionMaxLength(): void
    {
        $test = 'sdf sd Fsdf t34 tefg dF gfgh h4ht gdf #%terte3 t3fgsdtgD F ' .
            'GDFg dfg dfgdf gDFG dfg!Q_Sd sdfs- dfsd- fsdf sdfdsfsd fsdF#¤64' .
            '/%6789789sdfsdf,df SDFsd fsdfY&/()/()=/)(9 sdfsd fsd fsdf sdf sd' .
            'f sdfs dfsdf sdfsdf SDFSD FSDf w443 fsdf dsfG Dg dfgdfgsdf   DF' .
            'dgfe.';

        $this->item->setDescription($test);

        static::assertSame($test, $this->item->getDescription());
    }

    /**
     * Assert that an instance of InvalidArgumentException is thrown if the
     * setDescription method is provided a value which contain too many chars.
     *
     * @return void
     * @throws Exception
     */
    public function testSetDescriptionThrowsOnTooLongValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setDescription(
            'o029868kgjefgh3958t94t9grigkhguihuiddiugfhdskgh dsG 35YGef g3 ' .
            '5geF ERThd fghw2t342t ergef gdfg dfg 3w #¤AS DDFg 45y f TEG ' .
            'dfgh fgh fghDbdfg hdfgh_Df gdf gdfg -12! r4ty6 4TYH %RYh ' .
            'dfghf ghjfgbdfg dfg dfgdgdfgd gdfg .dfgdbdfb_d fg 09e56ef ' .
            'gd ? =  asdf sef wf sdf sdfdsdf sdfs'
        );
    }

    /**
     * Assert that an instance of InvalidArgumentException is thrown if the
     * setDescription method is provided a value which contain too few chars.
     *
     * @return void
     * @throws Exception
     */
    public function testSetDescriptionThrowsOnTooShortValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setDescription('');
    }

    /**
     * Assert that the setQuantity method will assign a value to the quantity
     * prop.
     *
     * @return void
     * @throws Exception
     */
    public function testSetQuantity(): void
    {
        $this->item->setQuantity(65.7765);

        self::assertSame(65.7765, $this->item->getQuantity());
    }

    /**
     * Assert that the setQuantity method accepts 0 value.
     *
     * @return void
     * @throws Exception
     */
    public function testSetQuantityAllow0(): void
    {
        $this->item->setQuantity(0);

        static::assertSame(0.0, $this->item->getQuantity());
    }

    /**
     * Assert that the setQuantity method accepts and converts integer values to
     * floats.
     *
     * @return void
     * @throws Exception
     */
    public function testSetQuantityTypeConversion(): void
    {
        $this->item->setQuantity(4);

        static::assertSame(4.0, $this->item->getQuantity());
    }

    /**
     * Assert that an instance of InvalidArgumentException is thrown if the
     * setQuantity method is provided a value which contain too many scale
     * digits.
     *
     * @return void
     * @throws Exception
     */
    public function testSetQuantityThrowsExceedScale(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setQuantity(1234123123456788);
    }

    /**
     * Assert that an instance of InvalidArgumentException is thrown if the
     * setQuantity method is provided a value which contain too many precision
     * digits.
     *
     * @return void
     * @throws Exception
     */
    public function testSetQuantityThrowsOnExceedPrecision(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setQuantity(55.123456);
    }

    /**
     * Assert that any negative value provided to the setQuantity method will
     * cause an instance of InvalidArgumentException to be thrown.
     *
     * @return void
     * @throws Exception
     */
    public function testSetQuantityThrowsOnNegativeValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setQuantity(-12);
    }

    /**
     * Assert that the setUnitMeasure method will assign a value to the
     * unitMeasure prop.
     *
     * @return void
     * @throws Exception
     */
    public function testSetUnitMeasure(): void
    {
        $this->item->setUnitMeasure('styck');

        self::assertSame('styck', $this->item->getUnitMeasure());
    }

    /**
     * Assert that the setUnitMeasure method allows values with a length of 1.
     *
     * @return void
     * @throws Exception
     */
    public function testSetUnitMeasureMinLength(): void
    {
        $this->item->setUnitMeasure('g');

        static::assertSame('g', $this->item->getUnitMeasure());
    }

    /**
     * Assert that the setUnitMeasure method allows values with a length of 15.
     *
     * @return void
     * @throws Exception
     */
    public function testSetUnitMeasureMaxLength(): void
    {
        $test = 'e3dfg56qwe12345';

        $this->item->setUnitMeasure($test);

        static::assertSame($test, $this->item->getUnitMeasure());
    }

    /**
     * Assert that an instance of InvalidArgumentException is thrown if the
     * setUnitMeasure method is provided a value which contain too many chars.
     *
     * @return void
     * @throws Exception
     */
    public function testSetUnitMeasureThrowsOnTooLongValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setUnitMeasure('123456qwer123q4w');
    }

    /**
     * Assert that an instance of InvalidArgumentException is thrown if the
     * setUnitMeasure method is provided a value which contain too few chars.
     *
     * @return void
     * @throws Exception
     */
    public function testSetUnitMeasureThrowsOnTooShortValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setUnitMeasure('');
    }

    /**
     * Assert that the setUnitAmountWithoutVat method will assign a value to the
     * unitAmountWithoutVat prop.
     *
     * @return void
     * @throws Exception
     */
    public function testSetUnitAmountWithoutVat(): void
    {
        $this->item->setUnitAmountWithoutVat(123123.4545);

        self::assertSame(123123.4545, $this->item->getUnitAmountWithoutVat());
    }

    /**
     * Assert that the setUnitAmountWithoutVat method accepts 0 value.
     *
     * @return void
     * @throws Exception
     */
    public function testSetUnitAmountWithoutVatAllow0(): void
    {
        $this->item->setUnitAmountWithoutVat(0);

        static::assertSame(0.0, $this->item->getUnitAmountWithoutVat());
    }

    /**
     * Assert that the setUnitAmountWithoutVat method accepts and converts
     * integer values to floats.
     *
     * @return void
     * @throws Exception
     */
    public function testSetUnitAmountWithoutVatTypeConversion(): void
    {
        $this->item->setUnitAmountWithoutVat(10);

        static::assertSame(10.0, $this->item->getUnitAmountWithoutVat());
    }

    /**
     * Assert that the setUnitAmountWithoutVat method accepts negative values.
     *
     * @return void
     * @throws Exception
     */
    public function testSetUnitAmountWithoutVatAcceptsNegativeValue(): void
    {
        $this->item->setUnitAmountWithoutVat(-15.67);

        static::assertSame(-15.67, $this->item->getUnitAmountWithoutVat());
    }

    /**
     * Assert that an instance of InvalidArgumentException is thrown if the
     * setUnitAmountWithoutVat method is provided a value which contain too many
     * scale digits.
     *
     * @return void
     * @throws Exception
     */
    public function testSetUnitAmountWithoutVatThrowsExceedScale(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setUnitAmountWithoutVat(123123122231423);
    }

    /**
     * Assert that an instance of InvalidArgumentException is thrown if the
     * setUnitAmountWithoutVat method is provided a value which contain too many
     * precision digits.
     *
     * @return void
     * @throws Exception
     */
    public function testSetUnitAmountWithoutVatThrowsOnExceedPrecision(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setUnitAmountWithoutVat(12.223456);
    }

    /**
     * Assert that the setVatPct method will assign a value to the vatPct prop.
     *
     * @return void
     * @throws Exception
     */
    public function testSetVatPct(): void
    {
        $this->item->setVatPct(25);

        self::assertSame(25, $this->item->getVatPct());
    }

    /**
     * Assert that the setVatPct method accepts all the values specified by the
     * validator.
     *
     * @return void
     * @throws Exception
     */
    public function testSetVatPctAllowedValues(): void
    {
        $this->item->setVatPct(0);
        static::assertSame(0, $this->item->getVatPct());

        $this->item->setVatPct(6);
        static::assertSame(6, $this->item->getVatPct());

        $this->item->setVatPct(12);
        static::assertSame(12, $this->item->getVatPct());

        $this->item->setVatPct(25);
        static::assertSame(25, $this->item->getVatPct());

        $this->item->setVatPct(8);
        static::assertSame(8, $this->item->getVatPct());

        $this->item->setVatPct(15);
        static::assertSame(15, $this->item->getVatPct());

        $this->item->setVatPct(10);
        static::assertSame(10, $this->item->getVatPct());

        $this->item->setVatPct(14);
        static::assertSame(14, $this->item->getVatPct());

        $this->item->setVatPct(24);
        static::assertSame(24, $this->item->getVatPct());
    }

    /**
     * Assert that any value provided to the setVatPct method not included in
     * the list of valid values will cause an instance of
     * InvalidArgumentException to be thrown.
     *
     * @return void
     * @throws Exception
     */
    public function testSetVatPctThrowsOnDisallowedValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setVatPct(4);
    }

    /**
     * Assert that any negative value provided to the setVatPct method will
     * cause an instance of InvalidArgumentException to be thrown.
     *
     * @return void
     * @throws Exception
     */
    public function testSetVatPctThrowsOnNegativeValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setVatPct(-15);
    }

    /**
     * Assert that the setType method will assign a value to the type prop.
     *
     * @return void
     * @throws Exception
     */
    public function testSetType(): void
    {
        $this->item->setType(Item::TYPE_PRODUCT);

        self::assertSame(Item::TYPE_PRODUCT, $this->item->getType());
    }

    /**
     * Assert that the setType method accepts all the values specified by the
     * validator.
     *
     * @return void
     * @throws Exception
     */
    public function testSetTypeAllowedValues(): void
    {
        $this->item->setType(Item::TYPE_DISCOUNT);
        static::assertSame(Item::TYPE_DISCOUNT, $this->item->getType());

        $this->item->setType(Item::TYPE_PRODUCT);
        static::assertSame(Item::TYPE_PRODUCT, $this->item->getType());

        $this->item->setType(Item::TYPE_SHIPPING);
        static::assertSame(Item::TYPE_SHIPPING, $this->item->getType());
    }

    /**
     * Assert that any value provided to the setType method not included in
     * the list of valid values will cause an instance of
     * InvalidArgumentException to be thrown.
     *
     * @return void
     * @throws Exception
     */
    public function testSetTypeThrowsOnDisallowedValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->setType('fruit');
    }

    /**
     * Assert that the toArray method will return the expected value.
     *
     * @return void
     * @throws Exception
     */
    public function testToArray(): void
    {
        $data = $this->item->toArray();

        static::assertSame(
            $this->data[Item::KEY_ART_NO],
            $data[Item::KEY_ART_NO]
        );

        static::assertSame(
            $this->data[Item::KEY_DESCRIPTION],
            $data[Item::KEY_DESCRIPTION]
        );

        static::assertSame(
            $this->data[Item::KEY_QUANTITY],
            $data[Item::KEY_QUANTITY]
        );

        static::assertSame(
            $this->data[Item::KEY_UNIT_MEASURE],
            $data[Item::KEY_UNIT_MEASURE]
        );

        static::assertSame(
            $this->data[Item::KEY_UNIT_AMOUNT_WITHOUT_VAT],
            $data[Item::KEY_UNIT_AMOUNT_WITHOUT_VAT]
        );

        static::assertSame(
            $this->data[Item::KEY_VAT_PCT],
            $data[Item::KEY_VAT_PCT]
        );

        static::assertSame(
            $this->data[Item::KEY_TYPE],
            $data[Item::KEY_TYPE]
        );
    }
}
