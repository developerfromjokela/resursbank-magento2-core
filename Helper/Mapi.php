<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Sales\Model\Order;
use Resursbank\Core\Model\Payment\Resursbank;
use Resursbank\Core\Model\PaymentMethod;
use Resursbank\Ecom\Lib\Model\PaymentMethod as EcomPaymentMethod;
use Resursbank\Ecom\Lib\Order\PaymentMethod\Type;
use Resursbank\Ecom\Module\PaymentMethod\Repository as EcomRepository;
use Throwable;
use JsonException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\ValidatorException;
use Resursbank\Core\Model\PaymentMethodFactory;

use function str_starts_with;

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
     * @param int|string $id
     * @param string $storeId
     * @return PaymentMethod|null
     */
    public function getMapiMethodById(
        int|string $id,
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
            'specificType' => $this->getMapiSpecificType(type: $method->type),
            'customerType' => $this->getCustomerTypes(method: $method)
        ], flags: JSON_THROW_ON_ERROR));

        return $result;
    }

    /**
     * Resolve array of available customer types for MAPI method.
     *
     * @param EcomPaymentMethod $method
     * @return array
     */
    private function getCustomerTypes(EcomPaymentMethod $method): array
    {
        $result = [];

        if ($method->enabledForLegalCustomer) {
            $result[] = 'LEGAL';
        }

        if ($method->enabledForNaturalCustomer) {
            $result[] = 'NATURAL';
        }

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
            substr(string: $type->value, offset: 7) : $type->value;
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
