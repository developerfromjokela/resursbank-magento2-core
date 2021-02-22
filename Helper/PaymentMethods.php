<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Exception;
use function is_array;
use JsonException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Store\Model\ScopeInterface;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\PaymentMethods\Converter;
use Resursbank\Core\Model\Api\Credentials as CredentialsModel;
use Resursbank\Core\Model\Payment\Resursbank as Method;
use Resursbank\Core\Model\PaymentMethodFactory;
use Resursbank\Core\Model\PaymentMethodRepository as Repository;
use stdClass;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @noinspection EfferentObjectCouplingInspection
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
     * @var Credentials
     */
    private $credentials;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @param Context $context
     * @param Api $api
     * @param PaymentMethodFactory $methodFactory
     * @param Converter $converter
     * @param Repository $repository
     * @param Credentials $credentials
     * @param SearchCriteriaBuilder $searchBuilder
     */
    public function __construct(
        Context $context,
        Api $api,
        PaymentMethodFactory $methodFactory,
        Converter $converter,
        Repository $repository,
        Credentials $credentials,
        SearchCriteriaBuilder $searchBuilder
    ) {
        $this->api = $api;
        $this->methodFactory = $methodFactory;
        $this->converter = $converter;
        $this->repository = $repository;
        $this->credentials = $credentials;
        $this->searchBuilder = $searchBuilder;

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
     * @throws JsonException
     * @noinspection BadExceptionsProcessingInspection
     */
    public function sync(
        CredentialsModel $credentials
    ): void {
        /**
         * Deactivate methods currently tracked in the database prior to syncing
         * entries from the API. This is to ensure methods which have been
         * deactivated in the API (thus no longer included in the result from
         * our API call to fetch payment methods) won't be listed in checkout.
         */
        $this->deactivateMethods();

        // Fetch methods from the API and store them in our db.
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
            } catch (NoSuchEntityException $error) {
                // NOTE: NoSuchEntityException is expected if the requested
                // method does not exist within the database, which is why we
                // just ignore it here and create a clean Method data model.
                /** @noinspection PhpUndefinedMethodInspection */
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
     * @return array<stdClass>
     * @throws IntegrationException
     */
    public function fetch(
        CredentialsModel $credentials
    ): array {
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
     * Deactivate all methods tracked in the db.
     *
     * @throws AlreadyExistsException
     */
    public function deactivateMethods(): void
    {
        foreach ($this->getActiveMethods() as $method) {
            $this->repository->save($method->setActive(false));
        }
    }

    /**
     * Retrieve collection of all active methods tracked in our db.
     *
     * @return PaymentMethodInterface[]
     */
    public function getActiveMethods(): array
    {
        $searchCriteria = $this->searchBuilder->addFilter(
            PaymentMethodInterface::ACTIVE,
            true
        )->create();

        return $this->repository->getList($searchCriteria)->getItems();
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

        return Method::CODE_PREFIX .
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
     * @return array<array>
     * @throws IntegrationException
     */
    private function resolveMethodDataArray(
        $data
    ): array {
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
     * @param array<mixed> $data
     * @throws ValidatorException
     */
    private function validateData(
        array $data
    ): void {
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
     * @param array<mixed> $data
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

    /**
     * Check if the payment method code starts with "resursbank_" to determine
     * whether or not it belong to us.
     *
     * @param string $code
     * @return bool
     */
    public function isResursBankMethod(
        string $code
    ): bool {
        return strpos($code, Method::CODE_PREFIX) === 0;
    }

    /**
     * NOTE: If not provided a Credentials model instance it will be resolved
     * from the configuration.
     *
     * @param null|CredentialsModel $credentials
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return array<PaymentMethodInterface>
     * @throws ValidatorException
     */
    public function getMethodsByCredentials(
        ?CredentialsModel $credentials = null,
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): array {
        // Automatically resolve credentials for active API account.
        if ($credentials === null) {
            $credentials = $this->credentials->resolveFromConfig(
                $scopeCode,
                $scopeType
            );
        }

        // Construct query to extract methods from database.
        $searchCriteria = $this->searchBuilder->addFilter(
            PaymentMethodInterface::ACTIVE,
            true
        )->addFilter(
            PaymentMethodInterface::CODE,
            "%{$this->credentials->getMethodSuffix($credentials)}",
            'like'
        )->create();

        // Execute query.
        return $this->repository->getList($searchCriteria)->getItems();
    }
}
