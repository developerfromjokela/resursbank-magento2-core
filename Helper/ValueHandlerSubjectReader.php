<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Model\MethodInterface;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Model\Payment\Resursbank;

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
     * @param array<mixed> $subject
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

    /**
     * @param array<mixed> $subject
     * @return MethodInterface|null
     * @throws LocalizedException
     */
    public function getMethodInstance(
        array $subject
    ): ?MethodInterface {
        return (
            isset($subject['payment']) &&
            $subject['payment'] instanceof PaymentDataObject &&
            $subject['payment']->getPayment() &&
            $subject['payment']->getPayment()->getMethod() &&
            $subject['payment']->getPayment()->getMethodInstance() instanceof MethodInterface
        ) ? $subject['payment']->getPayment()->getMethodInstance() : null;
    }

    /**
     * @param array<mixed> $subject
     * @return PaymentMethodInterface|null
     * @throws LocalizedException
     */
    public function getResursModel(
        array $subject
    ): ?PaymentMethodInterface {
        $method = $this->getMethodInstance($subject);

        return $method instanceof Resursbank ? $method->getResursModel() : null;
    }

    /**
     * Check whether the payment method will debit automatically. This method is
     * utilised to resolve various flags for our payment methods.
     *
     * @param PaymentMethodInterface $method
     * @return bool
     */
    public function isDebited(
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
