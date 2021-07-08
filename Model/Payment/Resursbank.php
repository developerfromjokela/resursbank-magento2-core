<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Payment;

use Magento\Payment\Model\Method\Adapter;
use Resursbank\Core\Api\Data\PaymentMethodInterface;

class Resursbank extends Adapter
{
    /**
     * Default title.
     *
     * @var string
     */
    public const TITLE = 'Resurs Bank';

    /**
     * Payment method code prefix.
     *
     * @var string
     */
    public const CODE_PREFIX = 'resursbank_';

    /**
     * Default payment method code.
     *
     * @var string
     */
    public const CODE = self::CODE_PREFIX . 'default';

    /**
     * @var PaymentMethodInterface|null
     */
    private $resursModel;

    /**
     * When we create an instance of this payment method we will assign an
     * instance of the Resurs Bank payment method model
     * (see Plugin/Payment/Helper/Data.php :: getMethod()). We will use this
     * model instance to collect information such as title and command flags.
     *
     * NOTE: We could achieve the same thing by dependency inject the repository
     * in this class, but that would mean we be required to make a complex
     * relay call to the parent constructor. Since the design pattern for the
     * payment method adapters keep changing we should avoid that for now.
     *
     * @param PaymentMethodInterface $model
     */
    public function setResursModel(
        PaymentMethodInterface $model
    ): void {
        $this->resursModel = $model;
    }

    /**
     * Overrides the vanilla method to extract title from this payment method.
     * Instead of utilising a value handler we return the title applied through
     * setTitle above when this instance is created.
     *
     * @inheritdoc
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function getTitle(): string
    {
        return $this->resursModel !== null
    }

    /**
     * Some implementations will utilise getConfigData directly, thus avoiding
     * the specified title value handlers and our overriding behaviour to
     * correct the title of this payment method instance (see setTitle and
     * getTitle above).
     *
     * This payment method was implemented to ensure the correct title is
     * displayed on the order view:
     * vendor/magento/module-payment/view/adminhtml/templates/info/default.phtml
     *
     * @inheritdoc
     */
    public function getConfigData(
        $field,
        $storeId = null
    ): string {
        return $field === 'title' ?
            $this->getTitle() :
            (string) parent::getConfigData($field, $storeId);
    }

    /**
     * Check if payment can be voided. Methods which automatically debit
     * payments cannot be voided.
     *
     * @return bool
     */
    public function canVoid(): bool
    {
        $result = ($this->resursModel instanceof PaymentMethodInterface) ?
            !$this->isAutoDebitMethod($this->resursModel) :
            parent::canVoid();

        return $result;
    }

    /**
     * Check whether or not the payment method will debit automatically. This
     * method is utilised to resolve various flags for our payment methods.
     *
     * @param PaymentMethodInterface $method
     * @return bool
     */
    private function isAutoDebitMethod(
        PaymentMethodInterface $method
    ): bool {
        return (
            $method->getType() === 'PAYMENT_PROVIDER' &&
            (
                $method->getSpecificType() === 'INTERNET' ||
                $method->getSpecificType() === 'SWISH'
            )
        );
    }
}
