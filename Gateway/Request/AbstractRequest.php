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
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Exception\MissingCredentialsException;
use Resursbank\Core\Helper\Api;
use Resursbank\Core\Helper\Api\Credentials as CredentialsHelper;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Model\Api\Credentials;

/**
 * Common methods and properties to construct request data.
 *
 * @package Resursbank\Core\Gateway\Request
 */
abstract class AbstractRequest implements BuilderInterface, EcomRequestInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @var Log
     */
    protected $log;

    /**
     * @var CredentialsHelper
     */
    protected $credentialsHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * AbstractRequest constructor.
     * @param Api $api
     * @param Log $log
     * @param CredentialsHelper $credentialsHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Api $api,
        Log $log,
        CredentialsHelper $credentialsHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->api = $api;
        $this->log = $log;
        $this->credentialsHelper = $credentialsHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     * @throws MissingCredentialsException
     * @throws NoSuchEntityException
     * @throws ValidatorException
     */
    public function build(
        array $buildSubject
    ): array {
        /** @var PaymentDataObjectInterface $payment */
        $payment = SubjectReader::readPayment($buildSubject);

        /** @var string $reference */
        $reference = $payment->getOrder()->getOrderIncrementId();

        /** @var Credentials $credentials */
        $credentials = $this->getCredentials($payment);

        // Debug log.
        $this->logInfo($credentials, $reference);

        return compact('credentials', 'reference');
    }

    /**
     * Resolve credentials from active configuration.
     *
     * @param PaymentDataObjectInterface $payment
     * @return Credentials
     * @throws MissingCredentialsException
     * @throws NoSuchEntityException
     * @throws ValidatorException
     */
    protected function getCredentials(
        PaymentDataObjectInterface $payment
    ): Credentials {
        /** @var string $storeCode */
        $storeCode = $this->storeManager->getStore(
            $payment->getOrder()->getStoreId()
        )->getCode();

        /** @var Credentials $credentials */
        $credentials = $this->credentialsHelper->resolveFromConfig($storeCode);

        if (!$this->credentialsHelper->hasCredentials($credentials)) {
            throw new MissingCredentialsException(
                'Failed to obtain API credentials for order ' .
                $payment->getOrder()->getOrderIncrementId()
            );
        }

        return $credentials;
    }
}
