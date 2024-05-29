<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway;

use Exception;
use JsonException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\PaymentException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\OrderRepository;
use ReflectionException;
use Resursbank\Core\Api\LogInterface;
use Resursbank\Core\Exception\PaymentDataException;
use Resursbank\Core\Helper\AbstractLog;
use Resursbank\Core\Model\Api\Payment\Converter\Item\ItemInterface;
use Resursbank\Core\Model\Api\Payment\Item;
use Resursbank\Ecom\Exception\AttributeCombinationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Exception\Validation\IllegalValueException;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLine;
use Resursbank\Ecom\Lib\Model\Payment\Order\ActionLog\OrderLineCollection;
use Resursbank\Ecom\Lib\Order\OrderLineType;
use Resursbank\RBEcomPHP\ResursBank;
use Throwable;
use function get_class;

/**
 * Common functionality for gateway commands.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Command
{
    /**
     * Use the addOrderLine method in ECom to add payload data while avoiding methods that override supplied data.
     *
     * @param ResursBank $connection
     * @param array<Item> $data
     * @throws Exception
     */
    public function addOrderLines(
        ResursBank $connection,
        array $data
    ): void {
        foreach ($data as $item) {
            // Ecom wrongly specifies some arguments as int when they should
            // be floats.
            $connection->addOrderLine(
                articleNumberOrId: $item->getArtNo(),
                description: $item->getDescription(),
                unitAmountWithoutVat: $item->getUnitAmountWithoutVat(),
                vatPct: $item->getVatPct(),
                unitMeasure: $item->getUnitMeasure(),
                articleType: $item->getType(),
                quantity: $item->getQuantity()
            );
        }
    }

    /**
     * Resolve Payment from subject data.
     *
     * @param array $commandSubject
     * @param LogInterface $log
     * @return Payment
     * @throws PaymentException
     */
    public function getPayment(
        array $commandSubject,
        LogInterface $log
    ): Payment {
        try {
            $data = SubjectReader::readPayment(subject: $commandSubject);
            $payment = $data->getPayment();

            if (!$payment instanceof Payment) {
                throw new PaymentException(phrase: __(
                    'Payment object is not an instance of %1',
                    Payment::class
                ));
            }

            return $payment;
        } catch (Throwable $error) {
            $log->exception(error: $error);

            throw new PaymentException(phrase: __(
                'Something went wrong when trying to place the order. ' .
                'Please try again, or select another payment method. You ' .
                'could also try refreshing the page.'
            ));
        }
    }

    /**
     * Get the order.
     *
     * @param array $commandSubject
     * @param OrderRepository $orderRepo
     * @return OrderInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getOrder(
        array $commandSubject,
        OrderRepository $orderRepo
    ): OrderInterface {
        $data = SubjectReader::readPayment(subject: $commandSubject);
        return $orderRepo->get(id: $data->getOrder()->getId());
    }

    /**
     * OrderLine renderer for capture and refund.
     *
     * @param array $items
     * @return OrderLineCollection
     * @throws IllegalTypeException
     * @throws IllegalValueException
     * @throws JsonException
     * @throws ReflectionException
     * @throws AttributeCombinationException
     */
    public function getOrderLines(array $items): OrderLineCollection
    {
        $data = [];

        /** @var ItemInterface $item */
        foreach ($items as $item) {
            $data[] = new OrderLine(
                quantity: $item->getQuantity(),
                quantityUnit: (string)__('rb-default-quantity-unit'),
                vatRate: $item->getVatPct(),
                totalAmountIncludingVat: $item->getTotalAmountInclVat(),
                description: $item->getDescription(),
                reference: $item->getArtNo(),
                type: match ($item->getType()) {
                    Item::TYPE_DISCOUNT => OrderLineType::DISCOUNT,
                    Item::TYPE_SHIPPING => OrderLineType::SHIPPING,
                    default => OrderLineType::NORMAL
                }
            );
        }

        return new OrderLineCollection(data: $data);
    }
}
