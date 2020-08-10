<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Request;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Resursbank\Core\Gateway\SubjectReader;

/**
 * @package Resursbank\Core\Gateway\Request
 */
abstract class AbstractRequest implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return array
     * @throws NoSuchEntityException
     * @throws ValidatorException
     */
    public function build(
        array $buildSubject
    ): array {
        /** @var PaymentDataObjectInterface $payment */
        $payment = $this->subjectReader->readPayment($buildSubject);

        return [
            'credentials' => $this->subjectReader->readCredentials(
                $buildSubject
            ),
            'reference' => $payment->getOrder()->getOrderIncrementId()
        ];
    }
}
