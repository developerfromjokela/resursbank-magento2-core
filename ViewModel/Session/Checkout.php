<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\ViewModel\Session;

use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Used to describe methods that are considered "undefined" for a session
 * interface.
 *
 * Specified as a view model to avoid errors/warnings about the session not
 * being part of the presentation layer.
 *
 * @method setData(string $key, mixed $value)
 * @method unsetData(string|string[] $key)
 * @method setLastQuoteId(int $id)
 * @method setResursFailureRedirectUrl(string $url)
 * @method getResursFailureRedirectUrl()
 * @method setResursBankPaymentFailed(bool $value)
 * @method getResursBankPaymentFailed()
 */
class Checkout extends Session implements ArgumentInterface
{

}
