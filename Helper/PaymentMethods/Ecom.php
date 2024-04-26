<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper\PaymentMethods;

use JsonException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\ValidatorException;
use Magento\Sales\Model\Order as MagentoOrder;
use Magento\Store\Model\ScopeInterface;
use ReflectionException;
use Resursbank\Core\Gateway\ValueHandler\Title;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Core\Model\Payment\Resursbank;
use Resursbank\Core\Model\PaymentMethod;
use Resursbank\Core\Model\PaymentMethodFactory;
use Resursbank\Ecom\Exception\ApiException;
use Resursbank\Ecom\Exception\AuthException;
use Resursbank\Ecom\Exception\CacheException;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\CurlException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Model\Interface\PaymentMethod as PaymentMethodInterface;
use Resursbank\Ecom\Lib\Model\Interface\PaymentMethodCollection as PaymentMethodCollectionInterface;
use Resursbank\Ecom\Lib\Model\PaymentMethodCollection;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod as RcoPaymentMethod;
use Resursbank\Ecom\Lib\Model\Rco\PaymentMethod\Type;
use Resursbank\Ecom\Lib\Validation\StringValidation;
use Resursbank\Ecom\Module\PaymentMethod\Repository;
use Resursbank\Rcoplus\Helper\Log;
use Throwable;

/**
 * Methods to fetch and convert payment methods using Ecom.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Ecom extends AbstractHelper
{
    /**
     * Storing methods resolved from API locally to avoid multiple API calls
     * on each page request to obtain the same resources (significant
     * performance improvement when cache is disabled).
     *
     * @var array
     */
    private array $methods = [];

    /**
     * @param Context $context
     * @param Log $log
     * @param PaymentMethodFactory $methodFactory
     * @param ResourceConnection $resourceConnection
     * @param StringValidation $stringValidation
     * @param PaymentMethods $paymentMethods
     * @param Config $config
     */
    public function __construct(
        Context $context,
        private readonly Log $log,
        private readonly PaymentMethodFactory $methodFactory,
        private readonly ResourceConnection $resourceConnection,
        private readonly StringValidation $stringValidation,
        private readonly PaymentMethods $paymentMethods,
        private readonly Config $config
    ) {
        parent::__construct(context: $context);
    }

    /**
     * Resolve list of  payment methods.
     *
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return array
     */
    public function getMethods(
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): array {
        // During a single checkout request this method will execute multiple
        // times. Storing collection locally improves performance.
        if (!empty($this->methods)) {
            return $this->methods;
        }

        $storeId = $this->config->getStore(
            scopeCode: $scopeCode,
            scopeType: $scopeType
        );

        $result = [];

        try {
            $methods = $this->getPaymentMethodsCollection(storeId: $storeId);

            foreach ($methods as $method) {
                $result["$storeId-" . $method->methodId] =
                    $this->convertMethod(method: $method);
            }
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        /* Block above might fail naturally if we've configured a different API
           flow. We still wish to list our methods though. */
        try {
            $this->appendLegacyMethods(methods: $result, storeId: $storeId);
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        $this->methods = $result;
        return $this->methods;
    }

    /**
     * Resolve payment method from API by id.
     *
     * @param string $id
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return PaymentMethod|null
     */
    public function getMethodById(
        string $id,
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): ?PaymentMethod {
        try {
            $storeId = $this->config->getStore(
                scopeCode: $scopeCode,
                scopeType: $scopeType
            );

            /* Attempt to use local storage first since this data is accessed
               multiple times on each page request in checkout. */
            $methods = $this->getMethods($scopeCode, $scopeType);

            if (isset($methods["$storeId-$id"])) {
                return $methods["$storeId-$id"];
            }

            $result = $this->convertMethod(
                method: $this->getMethod(storeId: $storeId, id: $id)
            );
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return $result ?? null;
    }

    /**
     * Strips prefix from code, resulting in a UUID.
     *
     * @param string $code
     * @return string
     */
    public function getUuidFromCode(string $code): string
    {
        $result = str_replace(Resursbank::ECOM_PREFIX, '', $code);
        $result = is_string($result) ? $result : '';

        try {
            $this->stringValidation->isUuid(value: $result);
        } catch (IllegalValueException) {
            $result = '';
        }

        return $result;
    }

    /**
     * Convert Ecom to Magento payment method model.
     *
     * @param RcoPaymentMethod $method
     * @return PaymentMethod
     * @throws JsonException
     * @throws ValidatorException
     */
    private function convertMethod(
        PaymentMethodInterface $method
    ): PaymentMethod {
        $result = $this->methodFactory->create();
        $result->setCode(
            code: $this->paymentMethods->getEcomCode(id: $method->methodId)
        );
        $result->setActive(state: true);
        $result->setSortOrder(order: $method->sortOrder);
        $result->setTitle(title: $method->name);
        $result->setMinOrderTotal(total: $method->getMinLimit());
        $result->setMaxOrderTotal(total: $method->getMaxLimit());
        $result->setOrderStatus(status: MagentoOrder::STATE_PENDING_PAYMENT);
        $result->setRaw(value: json_encode(value: [
            'type' => $this->getType(type: $method->type),
            'specificType' => $this->getSpecificType(type: $method->type),
            'customerType' => $this->getCustomerTypes(method: $method)
        ], flags: JSON_THROW_ON_ERROR));

        return $result;
    }

    /**
     * Convert data extracted from sales_order_payment to PaymentMethod.
     *
     * @param string $code
     * @param string $title
     * @return PaymentMethod
     * @throws ValidatorException
     */
    private function convertLegacyMethod(
        string $code,
        string $title
    ): PaymentMethod {
        $result = $this->methodFactory->create();
        $result->setCode(code: $code);
        $result->setActive(state: false);
        $result->setTitle(title: $title);

        return $result;
    }

    /**
     * Resolve array of available customer types for payment method.
     *
     * @param PaymentMethodInterface $method
     * @return array
     */
    private function getCustomerTypes(PaymentMethodInterface $method): array
    {
        $result = [];

        if ($method->enabledForB2b()) {
            $result[] = 'LEGAL';
        }

        if ($method->enabledForB2c()) {
            $result[] = 'NATURAL';
        }

        return $result;
    }

    /**
     * Convert  "type" to old "specificType". Essentially, drop the prefix
     * "RESURS_" if it exists, this will match the "specificType" property from
     * the deprecated APIs.
     *
     * @param Type $type
     * @return string
     */
    private function getSpecificType(Type $type): string
    {
        return (str_starts_with(haystack: $type->value, needle: 'RESURS_')) ?
            substr(string: $type->value, offset: 7) : $type->value;
    }

    /**
     * Resolve "PAYMENT_PROVIDER" as type for external payment methods.
     *
     * This method exists to mimic some behavior established by the deprecated
     * API integrations.
     *
     * @param Type $type
     * @return string
     */
    private function getType(Type $type): string
    {
        return str_starts_with(haystack: $type->value, needle: 'RESURS_') ?
            'INTERNAL' : 'PAYMENT_PROVIDER';
    }

    /**
     * Resolve all payment methods from Resurs Bank ever used to place an order.
     *
     * @param array $methods
     * @param string $storeId
     * @return void
     */
    private function appendLegacyMethods(
        array &$methods,
        string $storeId
    ): void {
        $connection = $this->resourceConnection->getConnection();
        $orderTable = $connection->getTableName(tableName: 'sales_order');
        $paymentTable = $connection->getTableName(tableName: 'sales_order_payment');

        /** @noinspection SqlNoDataSourceInspection */
        $data = $connection->fetchAll(
            sql: "select $paymentTable.method, $paymentTable.additional_information from $paymentTable " .
                "left join $orderTable on $paymentTable.parent_id = $orderTable.entity_id " .
                "where method like '" . Resursbank::ECOM_PREFIX . "%' " .
                "group by $paymentTable.method"
        );

        foreach ($data as $method) {
            if (!$this->validateDatabaseRecord(record: $method)) {
                continue;
            }

            $code = $method['method'];
            $uuid = $this->getUuidFromCode(code:$code);
            $title = $this->getTitleFromAdditionalInfo(
                info: $method['additional_information']
            );

            if (isset($methods["$storeId-$uuid"])) {
                continue;
            }

            try {
                $methods["$storeId-$uuid"] = $this->convertLegacyMethod(
                    code: $code,
                    title: $title
                );
            } catch (Throwable $error) {
                $this->log->exception(error: $error);
            }
        }
    }

    /**
     * Validate payment method database record.
     *
     * @param array $record
     * @return bool
     */
    private function validateDatabaseRecord(array $record): bool
    {
        return (
            isset($record['method']) &&
            is_string($record['method']) &&
            isset($record['additional_information']) &&
            is_string($record['additional_information'])
        );
    }

    /**
     * Resolve payment method title from additional_info JSON.
     *
     * @param string $info
     * @return string
     */
    private function getTitleFromAdditionalInfo(
        string $info
    ): string {
        $result = Title::DEFAULT_TITLE;

        try {
            $data = json_decode(json: $info, associative: true);

            if (is_array($data) &&
                isset($data['method_title']) &&
                is_string($data['method_title'])
            ) {
                /*
                 * This looks something like "Resurs Bank (something)". We will
                 * now strip this down to "something".
                 */
                $result = trim(str_replace(
                    search: Title::DEFAULT_TITLE,
                    replace: '',
                    subject: $data['method_title']
                ));

                // Remove first parentheses.
                $result = substr(string: $result, offset: 1);

                // Remove last parentheses.
                $result = substr(
                    string: $result,
                    offset: 0,
                    length: strlen($result)-1
                );
            }
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return $result;
    }

    /**
     * Resolve list of payment methods from Ecom. This method acts an anchor to
     * allow for different API flows to be implemented through plugins in other
     * modules.
     *
     * This method is not intended to be used outside this class, please use
     * getMethods() instead.
     *
     *  NOTE: scopeCode and scopeType are not used in this method, but are
     *  included in order for plugin methods to assert they should execute
     *  based on whether their API flow is enabled or not.
     *
     * @param string $storeId
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return PaymentMethodCollectionInterface
     * @throws IllegalTypeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPaymentMethodsCollection(
        string $storeId,
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): PaymentMethodCollectionInterface {
        try {
            return Repository::getPaymentMethods(storeId: $storeId);
        } catch (Throwable $e) {
            $this->log->exception(error: $e);
        }

        return new PaymentMethodCollection(data: []);
    }

    /**
     * Resolve payment method using UUID through Ecom. This method acts an
     * anchor to allow for different API flows to be implemented through plugins
     * in other modules.
     *
     * This method is not intended to be used outside this class, please use
     * getMethodById() instead.
     *
     * NOTE: scopeCode and scopeType are not used in this method, but are
     * included in order for plugin methods to assert they should execute
     * based on whether their API flow is enabled or not.
     *
     * @param string $storeId
     * @param string $id
     * @param string|null $scopeCode
     * @param string $scopeType
     * @return PaymentMethodInterface
     * @throws ApiException
     * @throws AuthException
     * @throws CacheException
     * @throws ConfigException
     * @throws CurlException
     * @throws EmptyValueException
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws Throwable
     * @throws ValidationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getMethod(
        string $storeId,
        string $id,
        ?string $scopeCode = null,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): PaymentMethodInterface {
        return Repository::getById(
            storeId: $storeId,
            paymentMethodId: $id
        );
    }
}
