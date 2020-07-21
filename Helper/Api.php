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
     * @param string $userAgent
     * @return ResursBank
     * @throws Exception
     */
    public function getConnection(
        Credentials $credentials,
        string $userAgent = ''
    ): ResursBank {
        $connection = new ResursBank(
            $credentials->getUsername(),
            $credentials->getPassword(),
            $credentials->getEnvironment()
        );

        $connection->setWsdlCache(true);
        $connection->setUserAgent($this->getUserAgent($userAgent));

        return $connection;
    }

    /**
     * @param string $custom
     * @return string
     */
    private function getUserAgent(
        string $custom = ''
    ): string {
        return $custom === '' ? 'Mage 2' : "Mage 2 + ${custom}";
    }
}
