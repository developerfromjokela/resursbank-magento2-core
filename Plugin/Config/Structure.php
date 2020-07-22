<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Config;

use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\ValidatorException;
use Magento\Paypal\Model\Config\Structure\PaymentSectionModifier as Original;
use Resursbank\Core\Api\Data\PaymentMethodInterface;
use Resursbank\Core\Api\PaymentMethodRepositoryInterface;
use Resursbank\Core\Helper\Api\Credentials;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Model\PaymentMethod;
use function is_array;

/**
 * Create custom configuration sections for all dynamic payment methods.
 *
 * We need to create the sections this way since we do not know what payment
 * methods will be available until the client fetches them from the API.
 *
 * @package Resursbank\Core\Plugin\Config
 */
class Structure
{
    /**
     * @var Credentials
     */
    private $credentials;

    /**
     * @var Log
     */
    private $log;

    /**
     * @var PaymentMethodRepositoryInterface
     */
    private $paymentMethodRepo;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @param Credentials $credentials
     * @param Log $log
     * @param PaymentMethodRepositoryInterface $paymentMethodRepo
     * @param SearchCriteriaBuilder $searchBuilder
     */
    public function __construct(
        Credentials $credentials,
        Log $log,
        PaymentMethodRepositoryInterface $paymentMethodRepo,
        SearchCriteriaBuilder $searchBuilder
    ) {
        $this->credentials = $credentials;
        $this->log = $log;
        $this->paymentMethodRepo = $paymentMethodRepo;
        $this->searchBuilder = $searchBuilder;
    }

    /**
     * Build and append configuration sections.
     *
     * @param Original $subject
     * @param array $result
     * @return array
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterModify(
        Original $subject,
        array $result
    ): array {
        try {
            if ($this->hasConfigElement($result)) {
                $collection = $this->getPaymentMethods();

                // Amend array structure for our payment methods.
                $methods = &$result['other_payment_methods']['children']
                    ['resursbank_section']['children']['resursbank']['children']
                    ['methods'];

                if (!isset($methods['children']) ||
                    !is_array($methods['children'])
                ) {
                    $methods['children'] = [];
                }

                /** @var PaymentMethod $method */
                foreach ($collection as $method) {
                    $this->addPaymentMethod($methods, $method);
                }
            }
        } catch (Exception $e) {
            $this->log->exception($e);

            throw $e;
        }

        return $result;
    }

    /**
     * Get the payment methods for the current user.
     *
     * @return array
     * @throws ValidatorException
     */
    private function getPaymentMethods(): array
    {
        $credentials = $this->credentials->resolveFromConfig();

        $searchCriteria = $this->searchBuilder->addFilter(
            PaymentMethodInterface::CODE,
            "%{$this->credentials->getMethodSuffix($credentials)}",
            'like'
        )->create();

        return $this->paymentMethodRepo->getList($searchCriteria)->getItems();
    }

    /**
     * Appends a dynamic payment method to config.
     *
     * @param array $config
     * @param PaymentMethod $method
     */
    private function addPaymentMethod(
        array &$config,
        PaymentMethod $method
    ): void {
        $config['children'][$method->getCode()] = [
            'id' => $method->getCode(),
            'translate' => 'label',
            'sortOrder' => 0,
            'showInDefault' => 1,
            'showInWebsite' => 1,
            'showInStore' => 1,
            'label' => $method->getTitle(),
            '_elementType' => 'group',
            'path' => 'payment/resursbank_section/resursbank/methods',
            'children' => [
                'sort_order' => [
                    'id' => 'sort_order',
                    'translate' => 'label',
                    'type' => 'text',
                    'sortOrder' => 1,
                    'showInDefault' => 1,
                    'showInWebsite' => 1,
                    'showInStore' => 1,
                    'label' => 'Sort Order',
                    '_elementType' => 'field',
                    'path' => "payment/resursbank_section/resursbank/methods/{$method->getCode()}",
                    'config_path' => "resursbank/methods/{$method->getCode()}/sort",
                ],
            ],
        ];
    }

    /**
     * Check if config array includes element for payment methods.
     *
     * @param array $result
     * @return bool
     */
    private function hasConfigElement(array $result): bool
    {
        return (
            isset(
                $result['other_payment_methods']['children']
                ['resursbank_section']['children']['resursbank']['children']
                ['methods']
            ) &&
            is_array(
                $result['other_payment_methods']['children']
                ['resursbank_section']['children']['resursbank']['children']
                ['methods']
            )
        );
    }
}
