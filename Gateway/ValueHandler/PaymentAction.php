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

class PaymentAction implements ValueHandlerInterface
{
    /**
     * @var ValueHandlerSubjectReader
     */
    private $reader;

    /**
     * @param ValueHandlerSubjectReader $reader
     */
    public function __construct(
        ValueHandlerSubjectReader $reader
    ) {
        $this->reader = $reader;
    }

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
