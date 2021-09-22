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
     * @return bool
     * @inheridoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(
        array $subject,
        $storeId = null
    ): bool {
        $result = false;

        try {
            $method = $this->reader->getResursModel($subject);

            $result = ($method !== null && $this->reader->isDebited($method));
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }
}
