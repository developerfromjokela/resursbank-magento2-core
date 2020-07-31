<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Response;

use Magento\Framework\Exception\ValidatorException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

/**
 * Describes methods required to handle responses from outgoing API calls.
 *
 * @package Resursbank\Core\Gateway\Response
 */
interface EcomResponseInterface
{
    /**
     * Process response from API call.
     *
     * @param PaymentDataObjectInterface $payment
     * @param array $response
     * @param string $reference
     * @param bool $status
     * @return void
     */
    public function process(
        PaymentDataObjectInterface $payment,
        array $response,
        string $reference,
        bool $status
    ): void;

    /**
     * Retrieve payment reference from anonymous array containing response data.
     *
     * @param array $response
     * @return string
     * @throws ValidatorException
     */
    public function getReference(
        array $response
    ): string;

    /**
     * Retrieve status from anonymous array containing response data.
     *
     * @param array $response
     * @return bool
     * @throws ValidatorException
     */
    public function wasSuccessful(
        array $response
    ): bool;
}
