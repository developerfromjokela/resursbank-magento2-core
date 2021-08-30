<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\ValueHandler;

use Resursbank\Core\Helper\ValueHandlerSubjectReader;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Model\MethodInterface;

/**
 * The default payment action, 'authorize', will simply forward the client to
 * the gateway to proceed with their payment.
 *
 * The action 'authorize_capture' will be applied on methods where payment is
 * automatically debited following authorization (for example credit card
 * payments). This action will automatically generate an invoice in Magento and
 * thus prohibit actions like order cancellation.
 */
class PaymentAction implements ValueHandlerInterface
{
    /**
     * @var ValueHandlerSubjectReader
     */
    private ValueHandlerSubjectReader $reader;

    /**
     * @param ValueHandlerSubjectReader $reader
     */
    public function __construct(
        ValueHandlerSubjectReader $reader
    ) {
        $this->reader = $reader;
    }

    /**
     * @inheridoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(
        array $subject,
        $storeId = null
    ) {
        return $this->reader->getAdditional(
            $subject,
            'method_payment_action'
        ) ?? MethodInterface::ACTION_AUTHORIZE;
    }
}
