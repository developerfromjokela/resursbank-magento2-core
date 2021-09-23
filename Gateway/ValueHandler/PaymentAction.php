<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\ValueHandler;

use Exception;
use Resursbank\Core\Helper\ValueHandlerSubjectReader;
use Resursbank\Core\Helper\Log;
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
     * @var Log
     */
    private Log $log;

    /**
     * @param ValueHandlerSubjectReader $reader
     * @param Log $log
     */
    public function __construct(
        ValueHandlerSubjectReader $reader,
        Log $log
    ) {
        $this->reader = $reader;
        $this->log = $log;
    }

    /**
     * @param array<mixed> $subject
     * @param int|null $storeId
     * @return string
     * @inheridoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(
        array $subject,
        $storeId = null
    ): string {
        $result = MethodInterface::ACTION_AUTHORIZE;

        try {
            $method = $this->reader->getResursModel($subject);

            if ($method !== null && $this->reader->isDebited($method)) {
                $result = MethodInterface::ACTION_AUTHORIZE_CAPTURE;
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }
}
