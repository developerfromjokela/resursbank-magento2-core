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
     * @param PaymentMethodRepository $repository
     * @param Log $log
     */
    public function __construct(
        PaymentMethodRepository $repository,
        Log $log
    ) {
        $this->repository = $repository;
        $this->log = $log;
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

        if (isset($subject['payment']) &&
            ($subject['payment'] instanceof PaymentDataObjectInterface)
        ) {
            try {
                /** @var PaymentMethod $method */
                $method = $this->repository->getByCode(
                    $subject['payment']->getPayment()->getMethod()
                );

                $result = $method->getTitle();
            } catch (Exception $e) {
                $this->log->exception($e);
            }
        }

        return $result;
    }
}
