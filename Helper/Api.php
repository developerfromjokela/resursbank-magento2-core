<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Exception;
use InvalidArgumentException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Resursbank\Core\Helper\Api\Credentials as CredentialsHelper;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\RBEcomPHP\ResursBank;

/**
 * API adapter utilising the EComPHP library.
 */
class Api extends AbstractHelper
{
    /**
     * @var CredentialsHelper
     */
    private $credentialsHelper;

    /**
     * @param Context $context
     * @param CredentialsHelper $credentialsHelper
     */
    public function __construct(
        Context $context,
        CredentialsHelper $credentialsHelper
    ) {
        $this->credentialsHelper = $credentialsHelper;

        parent::__construct($context);
    }

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
        $user = $credentials->getUsername();
        $pass = $credentials->getPassword();
        $env = $credentials->getEnvironment();

        // Validate API credentials & settings.
        if ($user === null || $pass === null || $env === null) {
            throw new InvalidArgumentException(
                'Failed to establish API connection, incomplete Credentials.'
            );
        }

        // Establish API connection.
        $connection = new ResursBank($user, $pass, $env);

        // Enable WSDL cache to suppress redundant API calls.
        $connection->setWsdlCache(true);

        // Enable usage of PSP methods.
        $connection->setSimplifiedPsp(true);

        // Supply API call with debug information.
        $connection->setUserAgent($this->getUserAgent($userAgent));

        // Apply auto debitable types.
        $connection->setAutoDebitableTypes(false);

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
