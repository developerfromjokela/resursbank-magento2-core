<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Config;

use Exception;
use Magento\Paypal\Model\Config\Structure\PaymentSectionModifier as Original;

/**
 * Create custom configuration sections for all dynamic payment methods.
 *
 * We need to create the sections this way since we do not know what payment
 * methods will be available until the client fetches them from the API.
 *
 * @package Resursbank\Core\Plugin\Config\Structure
 */
class Structure
{
    public function afterModify(
        Original $subject,
        array $result
    ): array {
        try {
            if ($this->hasConfigElement($result)) {
                $collection = $this->getFakeMethods();

                // Amend array structure for our payment methods.
                $methods = &$result['other_payment_methods']['children']['resursbank_section']
                    ['children']['resursbank']['children']['methods']['children']['collection'];

                if (!isset($methods['children']) ||
                    !is_array($methods['children'])
                ) {
                    $methods['children'] = [];
                }

                foreach ($collection as $method) {
                    $this->addPaymentMethod($methods, $method);
                }
            }
        } catch (Exception $e) {
            // Log error

            throw $e;
        }

        return $result;
    }

    /**
     * Appends a dynamic payment method to config.
     *
     * @param array $config
     * @param array $method // NEEDS TO CHANGE
     */
    private function addPaymentMethod(
        array &$config,
        array $method
    ) {
        $config['children'][$method['code']] = [
            'id' => $method['code'],
            'translate' => 'label',
            'sortOrder' => 0,
            'showInDefault' => 1,
            'showInWebsite' => 1,
            'showInStore' => 1,
            'label' => $method['label'],
            '_elementType' => 'group',
            'path' => 'payment/resursbank_section/resursbank/methods/collection',
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
                    'path' => "payment/resursbank_section/resursbank/methods/collection/{$method['code']}"
                ]
            ]
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
                $result['other_payment_methods']['children']['resursbank_section']
                ['children']['resursbank']['children']['methods']['children']['collection']
            ) &&
            is_array(
                $result['other_payment_methods']['children']['resursbank_section']
                ['children']['resursbank']['children']['methods']['children']['collection']
            )
        );
    }

    private function getFakeMethods(): array
    {
        return [
            ['code' => 'test_method_one', 'label' => 'Test method one'],
            ['code' => 'test_method_two', 'label' => 'Test method two']
        ]; 
    }
}
