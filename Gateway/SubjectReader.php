<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway;

use InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader as Helper;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Helper\Api\Credentials as CredentialsHelper;
use Resursbank\Core\Model\Api\Credentials;
use function is_string;

/**
 * Methods to read data from anonymous subject array.
 *
 * @package Resursbank\Core\Gateway
 */
class SubjectReader
{
    /**
     * @var CredentialsHelper
     */
    private $credentialsHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CredentialsHelper $credentialsHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CredentialsHelper $credentialsHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->credentialsHelper = $credentialsHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(
        array $subject
    ): PaymentDataObjectInterface {
        return Helper::readPayment($subject);
    }

    /**
     * Retrieve API credentials from subject.
     *
     * @param array $subject
     * @return Credentials
     * @throws InvalidArgumentException
     * @throws NoSuchEntityException
     * @throws ValidatorException
     */
    public function readCredentials(
        array $subject
    ): Credentials {
        /** @var PaymentDataObjectInterface $payment */
        $payment = $this->readPayment($subject);

        // @todo We might be able to pick up store_id from the $subject directly.
        /** @var string $storeCode */
        $storeCode = $this->storeManager->getStore(
            $payment->getOrder()->getStoreId()
        )->getCode();

        /** @var Credentials $credentials */
        $credentials = $this->credentialsHelper->resolveFromConfig($storeCode);

        if (!$this->credentialsHelper->hasCredentials($credentials)) {
            throw new ValidatorException(
                __('Failed to obtain API credentials for order ' .
                    $payment->getOrder()->getOrderIncrementId())
            );
        }

        return $credentials;
    }

    /**
     * Resolve payment / order reference from anonymous array.
     *
     * @param array $data
     * @return string
     * @throws ValidatorException
     */
    public function readReference(
        array $data
    ): string {
        if (!isset($data['reference'])) {
            throw new ValidatorException(
                __('Missing reference.')
            );
        }

        if (!is_string($data['reference'])) {
            throw new ValidatorException(
                __('Requested reference must be a string.')
            );
        }

        if ($data['reference'] === '') {
            throw new ValidatorException(
                __('Missing reference value.')
            );
        }

        return $data['reference'];
    }
}
