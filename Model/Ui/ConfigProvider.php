<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Resursbank\Core\Model\Payment\Resursbank as Method;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * Get config.
     *
     * @return array<mixed>
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                Method::CODE => [
                    'enabled' => true
                ]
            ]
        ];
    }
}
