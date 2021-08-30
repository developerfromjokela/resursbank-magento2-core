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
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\PaymentMethods;
use Resursbank\Core\Model\Payment\Resursbank as Method;
use Resursbank\Core\Model\PaymentMethodRepository as Repository;
use function is_array;

class Data
{
    /**
     * @var PaymentMethods
     */
    private PaymentMethods $paymentMethods;

    /**
     * @var Log
     */
    private Log $log;

    /**
     * @var MethodFactory
     */
    private MethodFactory $methodFactory;

    /**
     * @var Repository
     */
    private Repository $repository;

    /**
     * @var PaymentMethodInterface[]|null
     */
    private ?array $methodList;

    /**
     * @param PaymentMethods $paymentMethods
     * @param Log $log
     * @param MethodFactory $methodFactory
     * @param Repository $repository
     */
    public function __construct(
        PaymentMethods $paymentMethods,
        Log $log,
        MethodFactory $methodFactory,
        Repository $repository
    ) {
        $this->paymentMethods = $paymentMethods;
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
     * @param array<mixed> $result
     * @return array<mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterGetPaymentMethodList(
        Subject $subject,
        array $result
    ): array {
        try {
            $isMultiDimensional = is_array(reset($result));

            foreach ($this->getMethodList() as $method) {
                $code = $method->getCode();

                // Append payment method to resulting list.
                if ($code !== null) {
                    if ($isMultiDimensional) {
                        if (!isset($result['resursbank']['value'])) {
                            throw new InvalidArgumentException(
                                'Missing expected groups section resursbank.'
                            );
                        }

                        $result['resursbank']['value'][$code] = [
                            'value' => $code,
                            'label' => $method->getTitle('Resurs Bank')
                        ];
                    } else {
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
     * @param string $code
     * @return PaymentMethodInterface|null
     */
    private function getResursModel(
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
     * Store resolve method collection in a local variable to avoid expensive
     * database transactions during the same request cycle.
     *
     * @return PaymentMethodInterface[]
     */
    private function getMethodList(): array
    {
        if ($this->methodList === null) {
            $this->methodList = $this->paymentMethods->getActiveMethods();
        }

        return $this->methodList;
    }
}
