<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\RBEcomPHP\ResursBank;

/**
 * API adapter utilising the EComPHP library.
 *
 * @package Resursbank\Core\Helper
 */
class Api extends AbstractHelper
{
    /**
     * @param Credentials $credentials
     * @return ResursBank
     * @throws Exception
     */
    public function getConnection(
        Credentials $credentials
    ): ResursBank {
        return new ResursBank(
            $credentials->getUsername(),
            $credentials->getPassword(),
            $credentials->getEnvironment()
        );
    }
}
