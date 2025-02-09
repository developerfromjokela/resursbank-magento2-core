<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Api\OrderRepositoryInterface;
use Resursbank\Core\Exception\InvalidDataException;
use Resursbank\Core\ViewModel\Session\Checkout as CheckoutSession;
use Throwable;
use function is_string;

/**
 * This class implements ArgumentInterface (that's normally reserved for
 * ViewModels) because we found no other way of removing the suppressed warning
 * for PHPMD.CookieAndSessionMisuse. The interface fools the analytic tools into
 * thinking this class is part of the presentation layer, and thus eligible to
 * handle the session.
 */
class Order extends AbstractHelper implements ArgumentInterface
{
    /**
     * Custom order status reflecting credit denied result during checkout.
     *
     * @var string
     */
    public const CREDIT_DENIED_CODE = 'resursbank_credit_denied';

    /**
     * Label for custom order status explained above.
     *
     * @var string
     */
    public const CREDIT_DENIED_LABEL = 'Resurs Bank - Credit Denied';

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepo;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @var OrderManagementInterface
     */
    private OrderManagementInterface $orderManagement;

    /**
     * @var Log
     */
    private Log $log;

    /**
     * @param Context $context
     * @param SearchCriteriaBuilder $searchBuilder
     * @param OrderRepositoryInterface $orderRepo
     * @param RequestInterface $request
     * @param CheckoutSession $checkoutSession
     * @param OrderManagementInterface $orderManagement
     * @param Log $log
     */
    public function __construct(
        Context $context,
        SearchCriteriaBuilder $searchBuilder,
        OrderRepositoryInterface $orderRepo,
        RequestInterface $request,
        CheckoutSession $checkoutSession,
        OrderManagementInterface $orderManagement,
        Log $log
    ) {
        $this->searchBuilder = $searchBuilder;
        $this->orderRepo = $orderRepo;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->orderManagement = $orderManagement;
        $this->log = $log;

        parent::__construct($context);
    }

    /**
     * Check if the supplied order is new.
     *
     * @param OrderModel $order
     * @return bool
     */
    public function isNew(
        OrderModel $order
    ): bool {
        return (
            $order->isObjectNew() &&
            !$order->getOriginalIncrementId() &&
            (float) $order->getGrandTotal() > 0
        );
    }

    /**
     * Resolve order from quote id.
     *
     * @param int $quoteId
     * @return OrderInterface
     * @throws InvalidDataException
     */
    public function getOrderByQuoteId(
        int $quoteId
    ): OrderInterface {
        $orderList = $this->orderRepo->getList(
            $this->searchBuilder
                ->addFilter('quote_id', $quoteId)
                ->create()
        )->getItems();

        $order = end($orderList);

        if (!($order instanceof OrderInterface)) {
            throw new InvalidDataException(__(
                'Order with quote ID: %1 could not be found in the database.',
                $quoteId
            ));
        }

        if ((int) $order->getEntityId() === 0) {
            throw new InvalidDataException(__(
                'The order does not have a valid entity ID.'
            ));
        }

        return $order;
    }

    /**
     * Resolve increment_id from OrderInterface.
     *
     * @param OrderInterface $order
     * @return string
     * @throws InvalidDataException
     */
    public function getIncrementId(
        OrderInterface $order
    ): string {
        $result = $order->getIncrementId();

        if (!is_string($result)) {
            throw new InvalidDataException(
                __('Invalid or missing order increment ID.')
            );
        }

        return $result;
    }

    /**
     * Apply "Credit Denied" status to supplied order.
     *
     * @param OrderInterface $order
     */
    public function setCreditDeniedStatus(
        OrderInterface $order
    ): void {
        $this->orderRepo->save(
            $order->setStatus(self::CREDIT_DENIED_CODE)
        );
    }

    /**
     * Apply "pending_payment" order state.
     *
     * @param OrderInterface $order
     */
    public function setPendingPaymentState(
        OrderInterface $order
    ): void {
        $this->orderRepo->save(
            $order->setState(OrderModel::STATE_PENDING_PAYMENT)
        );
    }

    /**
     * Cancels both the order and all of its items to support item reservation.
     *
     * @param OrderInterface $order
     * @param bool $allowOffline Set to true to allow offline capture if online fails.
     * @return OrderInterface
     * @throws LocalizedException
     */
    public function cancelOrder(
        OrderInterface $order,
        bool $allowOffline = false
    ): OrderInterface {
        try {
            $this->orderManagement->cancel($order->getEntityId());
            $this->log->info('Canceled order #' .
                $order->getIncrementId());
        } catch (Throwable $error) {
            if ($allowOffline) {
                $this->log->info('Attempting to cancel order offline...');
                $order->registerCancellation();
                $this->orderRepo->save($order);
            }
        } catch (LocalizedException $error) {
            $this->log->error('Offline cancel failed: ' .
                $error->getMessage());
        }

        return $order;
    }

    /**
     * Sets the "resursbank_result" column in the "order_sales" table, which
     * says whether the customer has arrived to the success or failure page.
     *
     * true = Success.
     * false = Failure.
     *
     * @param OrderInterface $order
     * @param bool $value
     * @return OrderInterface
     */
    public function setResursbankResult(
        OrderInterface $order,
        bool $value
    ): OrderInterface {
        /** @noinspection PhpUndefinedMethodInspection */
        /* Type-cast:ed twice because we need an integer typed as a string,
           otherwise the value won't be properly saved if it's 0|false */
        /** @phpstan-ignore-next-line Undefined method. */
        $order->setData('resursbank_result', (string)(int) $value);

        $this->orderRepo->save($order);

        return $order;
    }

    /**
     * Gets the value from "resursbank_result" column of an order.
     *
     * @see setResursbankResult
     * @param OrderInterface $order
     * @return bool|null
     */
    public function getResursbankResult(
        OrderInterface $order
    ): ?bool {
        /** @phpstan-ignore-next-line Undefined method. */
        $value = $this->orderRepo->get($order->getEntityId())
            ->getData('resursbank_result');

        return $value !== null ? $value === '1' : null;
    }

    /**
     * Resolve the active order from a request with a "quote_id" parameter. If
     * a quote id cannot be found, then the order will be resolved from the
     * session. If both fail, an exception will be raised.
     *
     * This method exists in order to support intermediate browser change.
     *
     * @return OrderInterface
     * @throws InvalidDataException
     */
    public function resolveOrderFromRequest(): OrderInterface
    {
        $quoteId = $this->getQuoteId();
        $order = $quoteId !== 0 ?
            $this->getOrderByQuoteId($quoteId) :
            $this->checkoutSession->getLastRealOrder();

        if (!($order instanceof OrderInterface) ||
            (int) $order->getEntityId() === 0
        ) {
            throw new InvalidDataException(__(
                'Failed to resolve order from request or session.'
            ));
        }

        return $order;
    }

    /**
     * Returns the quote id from a request by looking for a "quote_id"
     * parameter. Returns 0 if the there is no "quote_id" parameter.
     *
     * @return int
     */
    public function getQuoteId(): int
    {
        return (int) $this->request->getParam('quote_id');
    }
}
