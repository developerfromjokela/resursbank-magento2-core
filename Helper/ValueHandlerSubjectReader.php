<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Payment\Gateway\Data\PaymentDataObject;

/**
 * Simplifies process to extract dynamic information from payment method
 * instance attached to an order (we store payment method title and some
 * specific flags on the instance generated during checkout, so we may access
 * this data elsewhere, like the admin panel).
 */
class ValueHandlerSubjectReader extends AbstractHelper
{
    /**
     * Resolve additional data applied on the payment method instance (see
     * Resursbank\Core\Model\Payment\Resursbank :: getInfoInstance()).
     *
     * @param array $subject
     * @param string $key
     * @return mixed
     */
    public function getAdditional(
        array $subject,
        string $key
    ) {
        $result = null;

        if (isset($subject['payment']) &&
            $subject['payment'] instanceof PaymentDataObject
        ) {
            $result = $subject['payment']
                ->getPayment()
                ->getAdditionalInformation($key);
        }

        return $result;
    }
}
