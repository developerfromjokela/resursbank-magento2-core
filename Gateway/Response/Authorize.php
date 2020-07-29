<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Response;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * Handle response from authorize request.
 *
 * @package Resursbank\Core\Gateway\Response
 */
class Authorize extends AbstractResponse
{
    /**
     * @inheritdoc
     */
    public function process(
        PaymentDataObjectInterface $payment,
        array $response,
        string $reference,
        bool $status
    ): void {
        if ($status) {
            // Close transaction.
            $payment->getPayment()->setTransactionId($reference);
            $payment->getPayment()->setIsTransactionClosed(false);
        }

        parent::process($payment, $response, $reference, $status);
    }
}
