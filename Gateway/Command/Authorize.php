<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Command;

use Exception;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Resursbank\Core\Helper\Log;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Authorize implements CommandInterface
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @param Log $log
     */
    public function __construct(
        Log $log
    ) {
        $this->log = $log;
    }

    /**
     * @param array<mixed> $subject
     * @return ResultInterface|null
     */
    public function execute(
        array $subject
    ): ?ResultInterface {
        try {
            $data = SubjectReader::readPayment($subject);

            /** @phpstan-ignore-next-line */
            $data->getPayment()
                ->setTransactionId($data->getOrder()->getOrderIncrementId())
                ->setIsTransactionClosed(false);
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return null;
    }
}
