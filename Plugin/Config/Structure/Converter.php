<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin\Config\Structure;

use Exception;
use Magento\Config\Model\Config\Structure\Converter as Original;

/**
 * Create custom configuration sections for all dynamic payment methods.
 *
 * We need to create the sections this way since we do not know what payment
 * methods will be available until the client fetches them from the API.
 *
 * @package Resursbank\Core\Plugin\Config\Structure
 */
class Converter
{
    /**
     * Build and append configuration sections.
     *
     * @param Original $subject
     * @param array $result
     * @return array
     * @throws Exception
     */
    public function afterConvert(Original $subject, array $result): array
    {
        try {
            if ($this->hasConfigElement($result)) {
                // @todo Get the correct data with repository.
                $collection = $this->getFakeMethods();

                // Amend array structure for our payment methods.
                $methods = &$result['config']['system']['sections']['payment']['children']
                ['resursbank_section']['children']['resursbank']['children']['methods']
                ['children']['collection'];

                if (!isset($methods['children']) ||
                    !is_array($methods['children'])
                ) {
                    $methods['children'] = [];
                }

                // @todo The method that is returned should be a model
                foreach ($collection as $method) {
                    $this->addBasicConfig($methods, $method);
                }

                // echo "<pre>";
                // var_dump($result['config']['system']['sections']['payment']['children']
                // ['resursbank_section']['children']['resursbank']['children']['methods']); die;
            }
        } catch (Exception $e) {
            // Log

            throw $e;
        }

        return $result;
    }

    /**
     * Appends basic configuration structure to system config.
     *
     * NOTE: This creates the required array keys for all conditional config
     * options (such as "Payment Fee"). Be sure to always execute this before
     * any subsequent method to further expand the config structure.
     *
     * @param array $config
     * @param array $method // NEEDS TO CHANGE
     */
    private function addBasicConfig(
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
                $result['config']['system']['sections']['payment']['children']
                ['resursbank_section']['children']['resursbank']['children']['methods']
            ) &&
            is_array(
                $result['config']['system']['sections']['payment']['children']
                ['resursbank_section']['children']['resursbank']['children']['methods']
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
