<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Resursbank\Core\Model\Api\Credentials;

/**
 * @package Resursbank\Core\Helper
 */
class PaymentMethods extends AbstractHelper
{
    /**
     * @var Api
     */
    private $api;

    public function __construct(
        Context $context,
        Api $api
    ) {
        $this->api = $api;

        parent::__construct($context);
    }

    /**
     * @param Credentials $credentials
     * @throws Exception
     */
    public function sync(Credentials $credentials)
    {
        $methods = $this->api->getConnection($credentials)->getPaymentMethods();

        die(var_dump($methods));
    }
}
