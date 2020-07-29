<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Handler;

use Exception;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Model\PaymentMethod;
use Resursbank\Core\Model\PaymentMethodRepository;
use Resursbank\Core\Gateway\Command\Gateway;

/**
 * @package Resursbank\Core\Gateway\Handler
 */
class Title implements ValueHandlerInterface
{
    /**
     * @var string
     */
    public const DEFAULT_TITLE = 'Resurs Bank';

    /**
     * @var PaymentMethodRepository
     */
    private $repository;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * @param PaymentMethodRepository $repository
     * @param Log $log
     * @param Gateway $gateway
     */
    public function __construct(
        PaymentMethodRepository $repository,
        Log $log,
        Gateway $gateway
    ) {
        $this->repository = $repository;
        $this->log = $log;
        $this->gateway = $gateway;
    }

    /**
     * Resolve correct method title.
     *
     * @param array $subject
     * @param null|int|string $storeId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function handle(
        array $subject,
        $storeId = null
    ): string {
        $result = self::DEFAULT_TITLE;

        try {
            /** @var PaymentDataObjectInterface $payment */
            $payment = $this->gateway->getPayment($subject);

            /** @var PaymentMethod $method */
            $method = $this->repository->getByCode(
                $payment->getPayment()->getMethod()
            );

            if ($method->getTitle() !== null) {
                $result = $method->getTitle();
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }
}
