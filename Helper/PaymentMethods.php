<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\Model\Payment\Resursbank;
use Resursbank\Core\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethod as EcomPaymentMethod;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;
use Resursbank\Ecom\Module\PaymentMethod\Repository as EcomRepository;
use Throwable;
use JsonException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\PaymentMethods\Converter;
use Resursbank\Core\Model\Api\Credentials as CredentialsModel;
use Resursbank\Core\Model\Payment\Resursbank as Method;
use Resursbank\Core\Model\PaymentMethodFactory;
use Resursbank\Core\Model\PaymentMethodRepository as Repository;
use stdClass;

use function json_decode;
use function strlen;
use function str_starts_with;
use function is_array;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentMethods extends AbstractHelper
{
    /**
     * @param Context $context
     * @param Api $api
     * @param PaymentMethodFactory $methodFactory
     * @param Converter $converter
     * @param Repository $repository
     * @param Credentials $credentials
     * @param SearchCriteriaBuilder $searchBuilder
     * @param Log $log
     * @param Config $config
     * @param Mapi $mapi
     */
    public function __construct(
        Context $context,
        private readonly Api $api,
        private readonly PaymentMethodFactory $methodFactory,
        private readonly Converter $converter,
        private readonly Repository $repository,
        private readonly Credentials $credentials,
        private readonly SearchCriteriaBuilder $searchBuilder,
        private readonly Log $log,
        private readonly Config $config,
        private readonly Mapi $mapi
    ) {
        parent::__construct(context: $context);
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
     * @throws ValidatorException
     * @throws JsonException
     * @noinspection BadExceptionsProcessingInspection
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @noinspection PhpMultipleClassDeclarationsInspection
     */
    public function sync(
        CredentialsModel $credentials
    ): void {
        /**
         * Payment method sort order.
         *
         * NOTE: At the time of writing some method sorting through won't
         * work properly if we start our sort_order at a value lower than
         * 20. I assigned 100 as the starting value since I cannot find the
         * root cause of the problem at the moment. Sorting sis done in
         * magento/module-payment/Model/PaymentMethodList.php ~ 50. At that
         * point all is well, but on frontend all of our methods with a
         * sort_order value of < 20 will be forced to render first.
         */
        $sortOrder = 100;

        // Fetch methods from the API and store them in our db.
        foreach ($this->fetch($credentials) as $methodData) {
            // Convert data.
            $data = $this->converter->convert(
                $this->resolveMethodDataArray($methodData)
            );

            // Validate converted data.
            $this->validateData($data);

            try {
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
                $method = $this->methodFactory->create();
            }

            /**
             * Magentos rendering component for the payment method list will
             * randomly sort the methods incorrectly unless we space them a bit.
             */
            $method->setSortOrder($sortOrder += 10);

            // Overwrite data on method model instance and update db entry.
            $this->syncMethodData(
                $this->fill($method, $data, $credentials),
                $credentials
            );
        }
    }

    /**
     * Sync method to database. This method will be utilised by submodules to
     * execute processes related to syncing payment methods. This is also the
     * reason we include the CredentialsModel instance, to allow for submodules
     * to more easily interact with the API utilising the Credentials associated
     * with the payment method.
     *
     * @param PaymentMethodInterface $method
     * @param CredentialsModel $credentials
     * @return PaymentMethodInterface
     * @throws AlreadyExistsException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function syncMethodData(
        PaymentMethodInterface $method,
        CredentialsModel $credentials /** @phpstan-ignore-line */
    ): PaymentMethodInterface {
        $this->log->info(
            'Synced payment method "' . $method->getRaw() . '"'
        );
        // Update / insert method data in database.
        return $this->repository->save($method);
    }

    /**
     * Fetch payment methods from deprecated APIs, not utilised by modern APIs.
     *
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
     * Generate payment method code.
     *
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
     * @param array $data
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
     * @param array $data
     * @param CredentialsModel $credentials
     * @return PaymentMethodInterface
     * @throws ValidatorException
     */
    private function fill(
        PaymentMethodInterface $method,
        array $data,
        CredentialsModel $credentials
    ): PaymentMethodInterface {
        $country = $credentials->getCountry();

        if ($country === null) {
            throw new ValidatorException(
                __('Credentials has no country code assigned.')
            );
        }

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
            $country
        );

        return $method;
    }

    /**
     * Verify that payment method code is for a Resurs Bank method.
     *
     * @param string $code
     * @return bool
     */
    public function isResursBankMethod(
        string $code
    ): bool {
        return str_starts_with(haystack: $code, needle: Method::CODE_PREFIX);
    }

    /**
     * Shorthand method to check if an order was paid using Resurs Bank.
     *
     * @param OrderInterface $order
     * @return bool
     */
    public function isResursBankOrder(OrderInterface $order)
    {
        $payment = $order->getPayment();

        return (
            $payment instanceof OrderPaymentInterface &&
            $this->isResursBankMethod($payment->getMethod())
        );
    }

    /**
     * Fetch payment methods.
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return array<PaymentMethodInterface>
     * @throws ValidatorException
     */
    public function getMethodsByCredentials(
        ?string $scopeCode = null,
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    ): array {
        if ($this->config->isMapiActive(scopeCode: $scopeCode, scopeType: $scopeType)) {
            return $this->mapi->getMapiMethods(
                storeId: $this->config->getStore(scopeCode: $scopeCode, scopeType: $scopeType)
            );
        }

        $result = [];

        $credentials = $this->credentials->resolveFromConfig(
            $scopeCode,
            $scopeType
        );

        if ($this->credentials->hasCredentials($credentials)) {
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
            $result = $this->repository->getList($searchCriteria)->getItems();
        }

        return $result;
    }

    /**
     * Retrieve list of valid customer types for a payment method instance.
     *
     * @param PaymentMethodInterface $method
     * @return array<string>
     */
    public function getCustomerTypes(
        PaymentMethodInterface $method
    ): array {
        $result = [];

        try {
            $data = $this->getRaw($method);

            if (isset($data['customerType'])) {
                $result = is_array($data['customerType']) ?
                    $data['customerType'] :
                    [$data['customerType']];
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * Retrieve decoded raw value.
     *
     * @param PaymentMethodInterface $method
     * @return array
     */
    public function getRaw(
        PaymentMethodInterface $method
    ): array {
        $result = [];

        try {
            $rawValue = $method->getRaw();

            $result = $rawValue !== null ?
                json_decode($rawValue, true, 512, JSON_THROW_ON_ERROR) :
                [];
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }
}
