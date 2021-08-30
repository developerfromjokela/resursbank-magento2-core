<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Payment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\Adapter;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Model\InfoInterface;

class Resursbank extends Adapter
{
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
    private ?PaymentMethodInterface $resursModel;

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
     * the value handler, thus we cannot extract values associated with our
     * dynamic methods from their table through the value handlers (so we do
     * not know what method we should resolve values for).
     *
     * @param PaymentMethodInterface $model
     */
    public function setResursModel(
        PaymentMethodInterface $model
    ): void {
        $this->resursModel = $model;
    }

    /**
     * We append custom values to the payment info instance later passed to our
     * value handlers.
     *
     * NOTE: Some values, like method_title will not be available at the initial
     * checkout phase but appended later (during debug it will appear as though
     * title cannot be resolved, though it eventually is).
     *
     * @inheridoc
     * @throws LocalizedException
     */
    public function getInfoInstance()
    {
        /**
         * NOTE: The use of Info Instance is deprecated but there is no clear
         * replacement procedure described. It seems as though assignData should
         * be utilised though that simply feeds the info instance object. There
         * is a separate issue for this.
         */
        $result = parent::getInfoInstance();

        if ($result instanceof InfoInterface &&
            $this->resursModel instanceof PaymentMethodInterface
        ) {
            // Method title.
            $result->setAdditionalInformation(
                'method_title',
                $this->resursModel->getTitle()
            );

            // Swish and PSP methods are debited automatically.
            $result->setAdditionalInformation(
                'method_payment_action',
                (
                    $this->isDebited() ?
                    MethodInterface::ACTION_AUTHORIZE_CAPTURE :
                    MethodInterface::ACTION_AUTHORIZE
                )
            );

            /**
             * This flag is required in order for payment action
             * 'authorize_capture' to function properly. Basically 'sale' is the
             * command utilised for action 'authorize_capture' while the command
             * 'authorize' is utilised for the payment action 'authorize'.
             */
            $result->setAdditionalInformation(
                'method_can_sale',
                $this->isDebited()
            );
        }

        return $result;
    }

    /**
     * Check whether the payment method will debit automatically. This method is
     * utilised to resolve various flags for our payment methods.
     *
     * @return bool
     */
    private function isDebited(): bool
    {
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
