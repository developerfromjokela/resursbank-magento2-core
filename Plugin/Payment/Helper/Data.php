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
use Resursbank\Core\Model\Ui\ConfigProvider;
use Resursbank\Core\Model\Payment\Resursbank as Method;
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
     * @param PaymentMethods $paymentMethods
     * @param Config $config
     * @param Log $log
     * @param MethodFactory $methodFactory
     */
    public function __construct(
        PaymentMethods $paymentMethods,
        Config $config,
        Log $log,
        MethodFactory $methodFactory
    ) {
        $this->paymentMethods = $paymentMethods;
        $this->config = $config;
        $this->log = $log;
        $this->methodFactory = $methodFactory;
    }

    /**
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

                // Append payment method to result.
                $result[$code] = $result[ConfigProvider::CODE];
                $result[$code]['title'] = $method->getTitle();
                $result[$code]['sort_order'] = $this->getSortOrder($code);
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
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
     * @param string $code
     * @return MethodInterface
     * @throws LocalizedException
     */
    private function getMethod(
        string $code
    ): MethodInterface {
        return $this->methodFactory->create(Method::class, ['code' => $code]);
    }

    /**
     * @param string $code
     * @return int
     */
    private function getSortOrder(
        string $code
    ): int {
        return $this->config->getMethodSortOrder($code);
    }
}
