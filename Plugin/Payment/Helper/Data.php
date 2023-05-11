<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Payment\Helper;

use Exception;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Helper\Data as Subject;
use Magento\Payment\Model\Method\Factory as MethodFactory;
use Magento\Payment\Model\MethodInterface;
use Magento\Store\Model\StoreManager;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Core\Model\Payment\Resursbank as Method;
use Resursbank\Core\Model\PaymentMethodRepository as Repository;
use Throwable;

class Data
{
    /**
     * @var PaymentMethodInterface[]|null
     */
    private ?array $methodList = null;

    /**
     * @param PaymentMethods $paymentMethods
     * @param Log $log
     * @param MethodFactory $methodFactory
     * @param Repository $repository
     * @param StoreManager $storeManager
     * @param Config $config
     */
    public function __construct(
        private readonly PaymentMethods $paymentMethods,
        private readonly Log $log,
        private readonly MethodFactory $methodFactory,
        private readonly Repository $repository,
        private readonly StoreManager $storeManager,
        private readonly Config $config
    ) {
    }

    /**
     * Since we do not define our dynamic payment methods in the <payment>
     * section of our config.xml file we will need to manually append our
     * methods to the array collected by Magento.
     *
     * Without this our payment methods will not be recognized in checkout.
     *
     * @param Subject $subject
     * @param array<string, array> $result
     * @return array<string, array>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterGetPaymentMethods(
        Subject $subject,
        array $result
    ): array {
        try {
            foreach ($this->getMethodList() as $method) {
                $code = $method->getCode();

                // Append payment method to resulting list.
                if ($code !== null) {
                    $result[$code] = $result[Method::CODE];
                    $result[$code]['title'] = $method->getTitle();
                    $result[$code]['sort_order'] = $method->getSortOrder();
                }
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * This method appends our payment methods to the list compiled by the core
     * method. The core method will produce a one or two-dimensional array with
     * options ($code => $title).
     *
     * The native method will read the titles directly from the config, ignoring
     * the value handler specified on the method configuration.
     *
     * For this reason we must modify the output to include our methods,
     * otherwise they won't show up in configuration select boxes or in the
     * order grid.
     *
     * @param Subject $subject
     * @param array $result
     * @param bool $sorted
     * @param bool $asLabelValue
     * @param bool $withGroups
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterGetPaymentMethodList(
        Subject $subject,
        array $result,
        bool $sorted = true,
        bool $asLabelValue = false,
        bool $withGroups = false
    ): array {
        try {
            if (
                $asLabelValue &&
                $withGroups &&
                !isset($result['resursbank']['value'])
            ) {
                throw new InvalidArgumentException(
                    'Missing expected group section "resursbank" in payment ' .
                    'method list.'
                );
            }

            foreach ($this->getMethodList() as $method) {
                $code = $method->getCode();

                // Append payment method to resulting list.
                if ($code !== null) {
                    if ($asLabelValue && $withGroups) {
                        $result['resursbank']['value'][$code] = [
                            'value' => $code,
                            'label' => $method->getTitle('Resurs Bank')
                        ];
                    } elseif ($asLabelValue) {
                        $result[$code] = [
                            'value' => $code,
                            'label' => $method->getTitle('Resurs Bank')
                        ];
                    } elseif (!$withGroups) {
                        $result[$code] = $method->getTitle('Resurs Bank');
                    }
                }
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
     * Retrieve Resursbank model for payment method.
     *
     * @param string $code
     * @return PaymentMethodInterface|null
     */
    public function getResursModel(
        string $code
    ): ?PaymentMethodInterface {
        $result = null;

        try {
            if ($code !== Method::CODE) {
                $result = $this->repository->getByCode($code);
            }
        } catch (Exception $e) {
            $this->log->exception($e);
        }

        return $result;
    }

    /**
     * Generate instance of our payment method model and apply the code of the
     * requested payment method (i.e. "resursbank_invoice" or similar).
     *
     * @param string $code
     * @return MethodInterface
     * @throws LocalizedException
     */
    private function getMethod(
        string $code
    ): MethodInterface {
        /** @var Method $method */
        $method = $this->methodFactory->create(
            Method::class,
            ['code' => $code]
        );

        $model = $this->getResursModel($code);

        if ($model !== null && $model->getMethodId() !== null) {
            $method->setResursModel($model);
        }

        return $method;
    }

    /**
     * Store resolve method collection in a local variable to avoid expensive
     * database transactions during the same request cycle.
     *
     * @return PaymentMethodInterface[]
     */
    private function getMethodList(): array
    {
        if ($this->methodList !== null) {
            return $this->methodList;
        }

        try {
            $code = $this->storeManager->getStore()->getCode();

            if ($this->config->isMapiActive(scopeCode: $code)) {
                $this->methodList = $this->paymentMethods->getMapiMethods(
                    storeId: $this->config->getStore(scopeCode: $code)
                );
            } else {
                $this->methodList = $this->paymentMethods->getActiveMethods();
            }
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return $this->methodList;
    }
}
