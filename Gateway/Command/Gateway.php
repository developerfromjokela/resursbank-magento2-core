<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Gateway\Command;

use Exception;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\RBEcomPHP\ResursBank;
use Resursbank\Core\Helper\Api;

/**
 * Generic gateway command methods.
 *
 * @package Resursbank\Core\Gateway\Command
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Gateway
{
    /**
     * @var Api
     */
    private $api;

    public function __construct(
        Api $api
    ) {
        $this->api = $api;
    }

    /**
     * Conditions for our gateway commands to be available.
     *
     * @param PaymentDataObjectInterface $payment
     * @return bool
     */
    public function isEnabled(
        PaymentDataObjectInterface $payment
    ): bool {
        return $payment->getOrder()->getGrandTotalAmount() > 0;
    }

    /**
     * Retrieve connection adapter to Resurs Bank API.
     *
     * @param Credentials $credentials
     * @return ResursBank
     * @throws Exception
     */
    public function getAdapter(
        Credentials $credentials
    ): ResursBank {
        return $this->api->getConnection($credentials);
    }
}
