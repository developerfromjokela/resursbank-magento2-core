<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Payment\Helper;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Helper\Data as Subject;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Core\Model\PaymentMethod;
use Resursbank\Core\Model\Payment\Resursbank as Method;
use Resursbank\Core\Model\PaymentMethodRepository as Repository;
use Magento\Payment\Model\Method\Factory as MethodFactory;
use Magento\Payment\Model\MethodInterface;

/**
 * @package Resursbank\Core\Plugin\Payment\Helper
 */
class Data
{
    /**
     * @var PaymentMethods
     */
    private $paymentMethods;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var MethodFactory
     */
    private $methodFactory;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param PaymentMethods $paymentMethods
     * @param Config $config
     * @param Log $log
     * @param MethodFactory $methodFactory
     * @param Repository $repository
     */
    public function __construct(
        PaymentMethods $paymentMethods,
        Config $config,
        Log $log,
        MethodFactory $methodFactory,
        Repository $repository
    ) {
        $this->paymentMethods = $paymentMethods;
        $this->config = $config;
        $this->log = $log;
        $this->methodFactory = $methodFactory;
        $this->repository = $repository;
    }

    /**
     * Since we do not define our dynamic payment methods in the <payment>
     * section of our config.xml file we will need to manually append our
     * methods to the array collected by Magento.
     *
     * Without this our payment methods will not be recognized in checkout.
     *
     * @param Subject $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterGetPaymentMethods(
        Subject $subject,
        array $result
    ): array {
        try {
            /** @var PaymentMethod $method */
            foreach ($this->paymentMethods->getActiveMethods() as $method) {
                $code = $method->getCode();

                // Append payment method to resulting list.
                $result[$code] = $result[Method::CODE];
                $result[$code]['title'] = $method->getTitle();
                $result[$code]['sort_order'] = $this->getSortOrder($code);
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * Since we do not define our dynamic payment methods in the <payment>
     * section of our config.xml we will need to manually create an instance of
     * our payment method model.
     *
     * @param Subject $subject
     * @param callable $proceed
     * @param string $code
     * @return MethodInterface
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function aroundGetMethodInstance(
        Subject $subject,
        callable $proceed,
        string $code
    ): MethodInterface {
        return $this->paymentMethods->isResursBankMethod($code) ?
            $this->getMethod($code) :
            $proceed($code);
    }

    /**
     * Generate instance of our payment method model and apply the code of the
     * requested payment method (ie. "resursbank_invoice" or similar).
     *
     * @param string $code
     * @return MethodInterface
     * @throws LocalizedException
     */
    private function getMethod(
        string $code
    ): MethodInterface {
        /** @var Method $metod */
        $method = $this->methodFactory->create(
            Method::class,
            ['code' => $code]
        );

        $method->setTitle($this->getTitle($code));

        return $method;
    }

    /**
     * Retrieve payment method sort order from our configuration.
     *
     * @param string $code
     * @return int
     */
    private function getSortOrder(
        string $code
    ): int {
        return $this->config->getMethodSortOrder($code);
    }

    /**
     * @param string $code
     * @return string
     */
    private function getTitle(
        string $code
    ): string {
        $result = Method::TITLE;

        try {
            if ($code !== Method::CODE) {
                $method = $this->repository->getByCode($code);
                $result = $method->getTitle($result);
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }
}
