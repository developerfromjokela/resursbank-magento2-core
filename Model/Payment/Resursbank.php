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
     * @param PaymentMethodInterface $model
     */
    public function setResursModel(
        PaymentMethodInterface $model
    ): void {
        $this->resursModel = $model;
    }

    /**
     * @return PaymentMethodInterface
     */
    public function getResursModel(): PaymentMethodInterface
    {
        return $this->resursModel;
    }

    /**
     * min_order_total and max_order_total need to be read before adapter
     * information is made available to value handlers. At the time of writing
     * there is no way for us to resolve these values from our value handlers
     * when the payment methods are rendered. This is important since we do not
     * wish to render methods outside the scope of the min / max values.
     *
     * NOTE: The values are resolved in
     * Plugin/Payment/Helper/Data.php :: getResursModel() to support the custom
     * Swish value override without adding an overriding constructor to this
     * class (see the notes on setResursModel above for further information).
     *
     * @param string $field
     * @param null $storeId
     * @return float|mixed|null
     */
    public function getConfigData($field, $storeId = null)
    {
        $result = parent::getConfigData($field, $storeId);

        if (isset($this->resursModel)) {
            if ($field === 'min_order_total') {
                $result = $this->resursModel->getMinOrderTotal();
            } elseif ($field === 'max_order_total') {
                $result = $this->resursModel->getMaxOrderTotal();
            }
        }

        return $result;
    }
}
