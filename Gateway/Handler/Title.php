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
use Resursbank\Core\Gateway\SubjectReader;
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
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Log
     */
    private $log;

    /**
     * @param PaymentMethodRepository $repository
     * @param SubjectReader $subjectReader
     * @param Log $log
     */
    public function __construct(
        PaymentMethodRepository $repository,
        SubjectReader $subjectReader,
        Log $log
    ) {
        $this->repository = $repository;
        $this->subjectReader = $subjectReader;
        $this->log = $log;
    }

    /**
     * Resolve method title using PaymentDataObjectInterface in anonymous array.
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
            $code = $this->subjectReader->readPaymentMethodCode($subject);

            if ($code !== '') {
                /** @var PaymentMethod $method */
                $method = $this->repository->getByCode($code);

                if ($method->getTitle() !== null) {
                    $result = $method->getTitle();
                }
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }
}
