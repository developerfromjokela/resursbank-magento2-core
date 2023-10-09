<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Order;

use Magento\Checkout\Controller\Onepage\Success;
use Magento\Checkout\Controller\Onepage\Failure;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\Page;
use Resursbank\Core\Helper\Order;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Scope;
use Resursbank\Ecom\Module\Payment\Repository;
use Magento\Sales\Api\OrderRepositoryInterface;
use Throwable;

/**
 * Handles rejected payments and attempts to update the order status if credit was denied.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetFailureReason
{
    /**
     * Default constructor.
     *
     * @param Log $log
     * @param Order $orderHelper
     * @param Config $configHelper
     * @param Scope $scope
     * @param OrderRepositoryInterface $orderRepo
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly Log $log,
        private readonly Order $orderHelper,
        private readonly Config $configHelper,
        private readonly Scope $scope,
        private readonly OrderRepositoryInterface $orderRepo,
    ) {
    }

    /**
     * Sets order status (if applicable) and updates payment history.
     *
     * @param Success|Failure $subject
     * @param ResultInterface|Redirect|Page $result
     * @return ResultInterface|Redirect|Page
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        $subject,
        $result
    ) {
        if (!$this->configHelper->isMapiActive(
            scopeCode: $this->scope->getId(),
            scopeType: $this->scope->getType()
        )) {
            return $result;
        }

        try {
            $order = $this->orderHelper->resolveOrderFromRequest();
            $payment = Repository::get(paymentId: $this->orderHelper->getPaymentId(order: $order));

            if (Repository::getTaskStatusDetails(paymentId: $payment->id)->completed) {
                $order->setStatus(status: Order::CREDIT_DENIED_CODE);
            }
            $this->orderRepo->save(entity: $order);
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
            return $result;
        }

        return $result;
    }
}
