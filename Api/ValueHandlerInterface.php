<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Api;

use Magento\Payment\Gateway\Config\ValueHandlerInterface as MagentoValueHandlerInterface;

/**
 * This interface exists only to give type annotations for methods that haven't
 * been properly described by Magento.
 */
interface ValueHandlerInterface extends MagentoValueHandlerInterface
{
    /**
     * @inheritdoc
     *
     * @param array<mixed> $subject
     * @param int|null $storeId
     * @return mixed
     */
    public function handle(array $subject, $storeId = null);
}
