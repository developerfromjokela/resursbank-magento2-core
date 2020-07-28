<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Command;

use Magento\Framework\Exception\LocalizedException;
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
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;

/**
 * Gateway command execution.
 *
 * @package Resursbank\Core\Gateway\Command
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Gateway extends GatewayCommand
{
    /**
     * @var Log
     */
    protected $log;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Gateway constructor.
     * @param BuilderInterface $requestBuilder
     * @param TransferFactoryInterface $transferFactory
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param Log $log
     * @param Config $config
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
        Config $config,
        HandlerInterface $handler = null,
        ValidatorInterface $validator = null,
        ErrorMessageMapperInterface $errorMessageMapper = null
    ) {
        $this->log = $log;
        $this->config = $config;

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
     * Execute gateway command.
     *
     * @param array $commandSubject
     * @return void
     * @throws CommandException
     * @throws ClientException
     * @throws ConverterException
     * @throws LocalizedException
     */
    public function execute(
        array $commandSubject
    ): void {
        /** @var PaymentDataObjectInterface $payment */
        $payment = $this->getPayment($commandSubject);

        if ($this->isEnabled($payment)) {
            parent::execute($commandSubject);
        } else {
            $this->log->info(
                'Skipping ' . $payment->getOrder()->getOrderIncrementId()
            );
        }
    }

    /**
     * Check if gateway commands are enabled.
     *
     * @param PaymentDataObjectInterface $payment
     * @return bool
     */
    protected function isEnabled(
        PaymentDataObjectInterface $payment
    ): bool {
        return $payment->getOrder()->getGrandTotalAmount() > 0;
    }

    /**
     * Resolve payment data from commandSubject,
     *
     * @param array $commandSubject
     * @return PaymentDataObjectInterface
     * @throws LocalizedException
     */
    protected function getPayment(
        array $commandSubject
    ): PaymentDataObjectInterface {
        if (!isset($commandSubject['payment']) ||
            !$commandSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new LocalizedException(
                __('Payment data object should be provided')
            );
        }

        return $commandSubject['payment'];
    }
}
