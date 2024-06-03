<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Data\Order;

/**
 * This class primarily exists as a "pure" alternative to the Braintree
 * type keeps popping up unless we actively set a preference for another type.
 */
class OrderAdapter extends \Magento\Payment\Gateway\Data\Order\OrderAdapter
{
}
