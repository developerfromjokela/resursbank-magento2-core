<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\ValidatorException;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\PaymentMethods\Converter;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\Core\Model\PaymentMethodFactory;
use Resursbank\Core\Model\PaymentMethodRepository as Repository;
use stdClass;

/**
 * @package Resursbank\Core\Helper
 */
class PaymentMethods extends AbstractHelper
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var PaymentMethodFactory
     */
    private $methodFactory;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Context $context
     * @param Api $api
     * @param PaymentMethodFactory $methodFactory
     * @param Converter $converter
     * @param Repository $repository
     */
    public function __construct(
        Context $context,
        Api $api,
        PaymentMethodFactory $methodFactory,
        Converter $converter,
        Repository $repository
    ) {
        $this->api = $api;
        $this->methodFactory = $methodFactory;
        $this->converter = $converter;
        $this->repository = $repository;

        parent::__construct($context);
    }

    /**
     * @param Credentials $credentials
     * @throws Exception
     * @return void
     */
    public function sync(Credentials $credentials): void
    {
        foreach ($this->fetch($credentials) as $method) {
            $data = $this->converter->convert(
                $this->resolveMethodDataArray($method)
            );

            $this->validateData($data);

            $method = $this->methodFactory->create();

            $method->setIdentifier(
                $data[PaymentMethodInterface::IDENTIFIER]
            )->setTitle(
                $data[PaymentMethodInterface::TITLE]
            )->setMinOrderTotal(
                $data[PaymentMethodInterface::MIN_ORDER_TOTAL]
            )->setMaxOrderTotal(
                $data[PaymentMethodInterface::MAX_ORDER_TOTAL]
            );

            $this->repository->save($method);
        }
    }

    /**
     * @param Credentials $credentials
     * @return array
     * @throws IntegrationException
     */
    public function fetch(Credentials $credentials): array
    {
        try {
            $methods = $this->api->getConnection($credentials)
                ->getPaymentMethods();
        } catch (Exception $e) {
            throw new IntegrationException(__($e->getMessage()));
        }

        if (!is_array($methods)) {
            throw new IntegrationException(
                __('Failed to fetch payment methods from API. Expected Array.')
            );
        }

        return $methods;
    }

    /**
     * The data returns from ECom when fetching payment methods is described
     * as mixed. We can therefore not be certain what we get back and need to
     * properly convert the data to an array for further processing.
     *
     * @param mixed $data
     * @return array
     * @throws IntegrationException
     */
    private function resolveMethodDataArray($data): array
    {
        $result = $data;

        if ($data instanceof stdClass) {
            $result = (array) $result;
        }

        if (!is_array($result)) {
            throw new IntegrationException(
                __('Unexpected payment method data retruned from ECom.')
            );
        }

        return $result;
    }

    /**
     * Validate array with converted payment method data.
     *
     * @param array $data
     * @throws ValidatorException
     */
    private function validateData(array $data): void
    {
        if (!isset($data[PaymentMethodInterface::IDENTIFIER])) {
            throw new ValidatorException(
                __('Missing identifier index.')
            );
        }

        if (!isset($data[PaymentMethodInterface::MIN_ORDER_TOTAL])) {
            throw new ValidatorException(
                __('Missing min_order_total index.')
            );
        }

        if (!isset($data[PaymentMethodInterface::MAX_ORDER_TOTAL])) {
            throw new ValidatorException(
                __('Missing max_order_total index.')
            );
        }

        if (!isset($data[PaymentMethodInterface::TITLE])) {
            throw new ValidatorException(
                __('Missing title index.')
            );
        }
    }
}
