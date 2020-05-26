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
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\PaymentMethods\Converter;
use Resursbank\Core\Model\Api\Credentials as CredentialsModel;
use Resursbank\Core\Model\PaymentMethodFactory;
use Resursbank\Core\Model\PaymentMethodRepository as Repository;
use stdClass;

/**
 * @package Resursbank\Core\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentMethods extends AbstractHelper
{
    /**
     * @var string
     */
    const CODE_PREFIX = 'resursbank_';

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
     * @var Credentials
     */
    private $credentials;

    /**
     * @param Context $context
     * @param Api $api
     * @param PaymentMethodFactory $methodFactory
     * @param Converter $converter
     * @param Repository $repository
     * @param Credentials $credentials
     */
    public function __construct(
        Context $context,
        Api $api,
        PaymentMethodFactory $methodFactory,
        Converter $converter,
        Repository $repository,
        Credentials $credentials
    ) {
        $this->api = $api;
        $this->methodFactory = $methodFactory;
        $this->converter = $converter;
        $this->repository = $repository;
        $this->credentials = $credentials;

        parent::__construct($context);
    }

    /**
     * Fetch available methods from Resurs Bank through the ECom API adapter
     * and synchronize them to our database. We do this both for data integrity
     * and improved latency.
     *
     * @param CredentialsModel $credentials
     * @return void
     * @throws AlreadyExistsException
     * @throws IntegrationException
     * @throws StateException
     * @throws ValidatorException
     */
    public function sync(
        CredentialsModel $credentials
    ): void {
        foreach ($this->fetch($credentials) as $methodData) {
            // Convert data.
            $data = $this->converter->convert(
                $this->resolveMethodDataArray($methodData)
            );

            // Validate converted data.
            $this->validateData($data);

            try {
                /** @var PaymentMethodInterface $method */
                $method = $this->repository->getByCode(
                    $this->getCode(
                        $data[PaymentMethodInterface::IDENTIFIER],
                        $credentials
                    )
                );
            } catch (NoSuchEntityException $e) {
                $method = $this->methodFactory->create();
            }

            // Overwrite data on method model instance and update db entry.
            $this->repository->save(
                $this->fill($method, $data, $credentials)
            );
        }
    }

    /**
     * @param CredentialsModel $credentials
     * @return array
     * @throws IntegrationException
     */
    public function fetch(CredentialsModel $credentials): array
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
     * Generate payment method code based on provided identifier value and
     * Credentials data model instance.
     *
     * @param string $identifier
     * @param CredentialsModel $credentials
     * @return string
     * @throws ValidatorException
     */
    public function getCode(
        string $identifier,
        CredentialsModel $credentials
    ): string {
        if ($identifier === '') {
            throw new ValidatorException(
                __('Cannot generate payment method code without identifier.')
            );
        }

        return self::CODE_PREFIX .
            strtolower($identifier) .
            '_' .
            $this->credentials->getMethodSuffix($credentials);
    }

    /**
     * The data returned from ECom when fetching payment methods is described
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
                __('Unexpected payment method data returned from ECom.')
            );
        }

        return $result;
    }

    /**
     * Validate converted payment method data.
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

        if (!isset($data[PaymentMethodInterface::RAW])) {
            throw new ValidatorException(
                __('Missing raw index.')
            );
        }
    }

    /**
     * Fill Payment Method data model with data from anonymous array.
     *
     * NOTE: The data array supplied to this method should always be validated
     * using the validateData method.
     *
     * @param PaymentMethodInterface $method
     * @param array $data
     * @param CredentialsModel $credentials
     * @return PaymentMethodInterface
     * @throws ValidatorException
     * @throws StateException
     */
    private function fill(
        PaymentMethodInterface $method,
        array $data,
        CredentialsModel $credentials
    ): PaymentMethodInterface {
        $method->setIdentifier(
            $data[PaymentMethodInterface::IDENTIFIER]
        )->setTitle(
            $data[PaymentMethodInterface::TITLE]
        )->setMinOrderTotal(
            $data[PaymentMethodInterface::MIN_ORDER_TOTAL]
        )->setMaxOrderTotal(
            $data[PaymentMethodInterface::MAX_ORDER_TOTAL]
        )->setRaw(
            $data[PaymentMethodInterface::RAW]
        )->setCode(
            $this->getCode(
                $data[PaymentMethodInterface::IDENTIFIER],
                $credentials
            )
        )->setActive(
            true
        )->setSpecificCountry(
            $this->credentials->getCountry($credentials)
        );

        return $method;
    }
}
