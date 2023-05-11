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
 * Mapi related business logic.
 */
class Mapi extends AbstractHelper
{
    /**
     * @param Context $context
     * @param PaymentMethodFactory $methodFactory
     * @param Log $log
     */
    public function __construct(
        Context $context,
        private readonly PaymentMethodFactory $methodFactory,
        private readonly Log $log
    ) {
        parent::__construct(context: $context);
    }

    /**
     * Resolve MAPI payment method converted to PaymentMethod
     *
     * @param string $id
     * @param string $storeId
     * @return PaymentMethod|null
     */
    public function getMapiMethodById(
        string $id,
        string $storeId
    ): ?PaymentMethod {
        try {
            return $this->convertMapiMethod(
                method: EcomRepository::getById(
                    storeId: $storeId,
                    paymentMethodId: $id
                )
            );
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return null;
    }

    /**
     * Resolve list of MAPI payment methods.
     *
     * @param string $storeId
     * @return array
     */
    public function getMapiMethods(string $storeId): array
    {
        $result = [];

        try {
            $methods = EcomRepository::getPaymentMethods(storeId: $storeId);

            foreach ($methods as $method) {
                $result[] = $this->convertMapiMethod(method: $method);
            }
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return $result;
    }

    /**
     * Convert EcomPaymentMethod to PaymentMethod.
     *
     * @param EcomPaymentMethod $method
     * @return PaymentMethod
     * @throws JsonException
     * @throws ValidatorException
     */
    private function convertMapiMethod(
        EcomPaymentMethod $method
    ): PaymentMethod {
        $result = $this->methodFactory->create();
        $result->setCode(code: Resursbank::CODE_PREFIX . $method->id);
        $result->setActive(state: true);
        $result->setSortOrder(order: $method->sortOrder);
        $result->setTitle(title: $method->name);
        $result->setMinOrderTotal(total: $method->minPurchaseLimit);
        $result->setMaxOrderTotal(total: $method->maxPurchaseLimit);
        $result->setOrderStatus(status: Order::STATE_PENDING_PAYMENT);
        $result->setRaw(value: json_encode(value: [
            'type' => $this->getMapiType(type: $method->type),
            'specificType' => $this->getMapiSpecificType(type: $method->type)
        ], flags: JSON_THROW_ON_ERROR));

        return $result;
    }

    /**
     * Convert MAPI "type" to old "specificType". Essentially, drop the prefix
     * "RESURS_" if it exists, this will match the "specificType" property from
     * the deprecated APIs.
     *
     * @param Type $type
     * @return string
     */
    private function getMapiSpecificType(Type $type): string
    {
        return (str_starts_with(haystack: $type->value, needle: 'RESURS_')) ?
            substr(string: $type->value, offset: 8) : $type->value;
    }

    /**
     * Resolve "PAYMENT_PROVIDER" as type for external payment methods to mimic
     * some behavior established by the deprecated API integrations.
     *
     * @param Type $type
     * @return string
     */
    private function getMapiType(Type $type): string
    {
        return str_starts_with(haystack: $type->value, needle: 'RESURS_') ?
            'INTERNAL' : 'PAYMENT_PROVIDER';
    }
}
