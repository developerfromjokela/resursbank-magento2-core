<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * @package Resursbank\Checkout\Model\Ui
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * Payment method code prefix.
     *
     * @var string
     */
    public const CODE_PREFIX = 'resursbank_';

    /**
     * Default payment method code.
     *
     * @var string
     */
    public const CODE = self::CODE_PREFIX . 'default';

    /**
     * @inheritdoc
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::CODE => [
                    'enabled' => true
                ]
            ]
        ];
    }
}
