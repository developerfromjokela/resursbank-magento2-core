<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Command;

use Magento\Framework\Exception\ValidatorException;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\GatewayCommand;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\ErrorMapper\ErrorMessageMapperInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;
use Resursbank\Core\Helper\Log;

/**
 * @package Resursbank\Core\Gateway\Command
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Gateway extends GatewayCommand
{
    /**
     * @var Log
     */
    private $log;

    /**
     * @param BuilderInterface $requestBuilder
     * @param TransferFactoryInterface $transferFactory
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param Log $log
     * @param HandlerInterface|null $handler
     * @param ValidatorInterface|null $validator
     * @param ErrorMessageMapperInterface|null $errorMessageMapper
     */
    public function __construct(
        BuilderInterface $requestBuilder,
        TransferFactoryInterface $transferFactory,
        ClientInterface $client,
        LoggerInterface $logger,
        Log $log,
        HandlerInterface $handler = null,
        ValidatorInterface $validator = null,
        ErrorMessageMapperInterface $errorMessageMapper = null
    ) {
        $this->log = $log;

        parent::__construct(
            $requestBuilder,
            $transferFactory,
            $client,
            $logger,
            $handler,
            $validator,
            $errorMessageMapper
        );
    }

    /**
     * @param array $data
     * @return void
     * @throws CommandException
     * @throws ClientException
     * @throws ConverterException
     * @throws ValidatorException
     */
    public function execute(
        array $data
    ): void {
        /** @var PaymentDataObjectInterface $payment */
        $payment = $this->getPayment($data);

        if ($this->isEnabled($payment)) {
            parent::execute($data);
        } else {
            $this->log->info(
                'Skipping ' . $payment->getOrder()->getOrderIncrementId()
            );
        }
    }

    /**
     * Resolve PaymentDataObjectInterface object from anonymous array.
     *
     * @param array $data
     * @return PaymentDataObjectInterface
     * @throws ValidatorException
     */
    public function getPayment(
        array $data
    ): PaymentDataObjectInterface {
        if (!isset($data['payment'])) {
            throw new ValidatorException(__('Missing payment data.'));
        }

        /** @var PaymentDataObjectInterface $payment */
        $payment = $data['payment'];

        if (!($payment instanceof PaymentDataObjectInterface)) {
            throw new ValidatorException(
                __(
                    'Payment data must be instance of ' .
                    PaymentDataObjectInterface::class
                )
            );
        }

        return $data['payment'];
    }

    /**
     * Conditions for our gateway commands to be available.
     *
     * @param PaymentDataObjectInterface $payment
     * @return bool
     */
    private function isEnabled(
        PaymentDataObjectInterface $payment
    ): bool {
        return $payment->getOrder()->getGrandTotalAmount() > 0;
    }
}
