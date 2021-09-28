<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\OrderRepository;
use Resursbank\Core\Exception\InvalidDataException;
use function is_string;

class Order extends AbstractHelper
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
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchBuilder;

    /**
     * @var OrderRepository
     */
    private OrderRepository $orderRepository;

    /**
     * @param Context $context
     * @param SearchCriteriaBuilder $searchBuilder
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        Context $context,
        SearchCriteriaBuilder $searchBuilder,
        OrderRepository $orderRepository
    ) {
        $this->searchBuilder = $searchBuilder;
        $this->orderRepository = $orderRepository;

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
        $orderList = $this->orderRepository->getList(
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
     * @throws AlreadyExistsException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function setCreditDeniedStatus(
        OrderInterface $order
    ): void {
        $this->orderRepository->save(
            $order->setStatus(self::CREDIT_DENIED_CODE)
        );
    }
}
