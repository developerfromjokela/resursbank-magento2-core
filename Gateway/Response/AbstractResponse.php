<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Response;

use JsonException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Resursbank\Core\Helper\Log;
use function is_bool;
use function is_string;

/**
 * General methods to handle response from command request.
 *
 * @package Resursbank\Core\Gateway\Response
 */
abstract class AbstractResponse implements HandlerInterface, EcomResponseInterface
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
     * @inheritdoc
     * @throws ValidatorException
     * @throws JsonException
     */
    public function handle(
        array $handlingSubject,
        array $response
    ): void {
        /** @var PaymentDataObjectInterface $payment */
        $payment = SubjectReader::readPayment($handlingSubject);

        // Process response.
        $this->process(
            $payment,
            $response,
            $this->getReference($response),
            $this->wasSuccessful($response)
        );
    }

    /**
     * @inheritDoc
     */
    public function getReference(
        array $response
    ): string {
        if (!isset($response['reference'])) {
            throw new ValidatorException(
                __('Missing reference in response.')
            );
        }

        if (!is_string($response['reference'])) {
            throw new ValidatorException(
                __('Reference must be a string.')
            );
        }

        if ($response['reference'] === '') {
            throw new ValidatorException(
                __('Missing reference value.')
            );
        }

        return $response['reference'];
    }

    /**
     * @inheritDoc
     */
    public function wasSuccessful(
        array $response
    ): bool {
        if (!isset($response['status'])) {
            throw new ValidatorException(
                __('Missing status in response.')
            );
        }

        if (!is_bool($response['status'])) {
            throw new ValidatorException(
                __('Status must be a bool.')
            );
        }

        return $response['status'];
    }

    /**
     * @inheritdoc
     * @throws JsonException
     */
    public function process(
        PaymentDataObjectInterface $payment,
        array $response,
        string $reference,
        bool $status
    ): void {
        $this->log->info(
            'Response for ' .
            $payment->getOrder()->getOrderIncrementId() .
            ' :: ' .
            json_encode($response, JSON_THROW_ON_ERROR)
        );
    }
}
