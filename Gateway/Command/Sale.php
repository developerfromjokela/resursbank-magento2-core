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
class Sale implements CommandInterface
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
     * @param array<mixed> $commandSubject
     * @return ResultInterface|null
     */
    public function execute(
        array $commandSubject
    ): ?ResultInterface {
        try {
            $data = SubjectReader::readPayment($commandSubject);

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
