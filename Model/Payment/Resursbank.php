<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Payment;

use Magento\Payment\Model\Method\Adapter;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Magento\Payment\Model\MethodInterface;

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
     * NOTE: We need the Resurs Bank method model applied within the adapter
     * to reach values otherwise handled by configured value handlers. At the
     * time of writing the payment method instance nor code is made available to
     * the value handler. Thus we cannot extract values associated with our
     * dynamic methods from their table though the value handlers.
     *
     * @param PaymentMethodInterface $model
     */
    public function setResursModel(
        PaymentMethodInterface $model
    ): void {
        $this->resursModel = $model;
    }

    /**
     * Resolve payment method title from attached Resurs Bank method model.
     *
     * @inheritdoc
     * @noinspection PhpMissingParentCallCommonInspection
     */
    public function getTitle(): string
    {
        return ($this->resursModel instanceof PaymentMethodInterface) ?
            $this->resursModel->getTitle(self::TITLE) :
            '';
    }

    /**
     * If the selected payment method was automatically debited at Resurs Bank
     * we want to utilise the "authorize_and_capture" action to automatically
     * create an invoice in Magento for the purchase.
     *
     * @return string
     */
    public function getConfigPaymentAction(): string
    {
        return $this->isDebited() ?
            MethodInterface::ACTION_AUTHORIZE_CAPTURE :
            parent::getConfigPaymentAction();
    }

    /**
     * While the base Adapter class implements a getTitle() method this is not
     * always called to extract the title value. Sometimes the getConfigData
     * method will instead be called for the same purpose.
     *
     *
     * @inheritdoc
     */
    public function getConfigData(
        $field,
        $storeId = null
    ) {
        return $field === 'title' ?
            $this->getTitle() :
            parent::getConfigData($field, $storeId);
    }

    /**
     * Check if payment method can utilise "sale" command to automatically
     * create an invoice after authorization.
     *
     * @return bool
     */
    public function canSale(): bool
    {
        return ($this->resursModel instanceof PaymentMethodInterface) ?
            $this->isDebited() :
            parent::canSale();
    }

    /**
     * Check whether or not the payment method will debit automatically. This
     * method is utilised to resolve various flags for our payment methods.
     *
     * @return bool
     */
    private function isDebited(): bool {
        $result = false;

        if ($this->resursModel instanceof PaymentMethodInterface) {
            $result = (
                $this->resursModel->getType() === 'PAYMENT_PROVIDER' &&
                (
                    $this->resursModel->getSpecificType() === 'INTERNET' ||
                    $this->resursModel->getSpecificType() === 'SWISH'
                )
            );
        }

        return $result;
    }
}
