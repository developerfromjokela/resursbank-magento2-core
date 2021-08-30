<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\ValueHandler;

use Resursbank\Core\Helper\ValueHandlerSubjectReader;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;

/**
 * This flag will be utilised by Magento to ensure the payment action
 * 'authorize_capture' may be executed during checkout. So essentially this
 * flag will evaluate to true for any method which is debited automatically by
 * the gateway.
 */
class CanSale implements ValueHandlerInterface
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
            'method_can_sale'
        ) ?? false;
    }
}
