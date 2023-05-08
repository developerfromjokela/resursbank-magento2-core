<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Plugin;

use Resursbank\Core\Helper\Config;
use Resursbank\Core\Helper\Log;
use Resursbank\Core\Helper\Scope;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\Ecom\Config as EcomConfig;
use Resursbank\Ecom\Lib\Api\Environment as EnvironmentEnum;
use Resursbank\Ecom\Lib\Api\GrantType;
use Resursbank\Ecom\Lib\Api\Scope as EcomScope;
use Resursbank\Ecom\Lib\Model\Network\Auth\Jwt;
use Throwable;

/**
 * Handles initial init of Ecom+.
 */
class Init
{
    /**
     * @var Config
     */
    private Config $config;
    /**
     * @var Scope
     */
    private Scope $scope;
    /**
     * @var Credentials
     */
    private Credentials $credentials;
    /**
     * @var Log
     */
    private Log $log;

    /**
     * @param Config $config
     * @param Scope $scope
     * @param Credentials $credentials
     * @param Log $log
     */
    public function __construct(
        Config $config,
        Scope $scope,
        Credentials $credentials,
        Log $log,
    ) {
        $this->config = $config;
        $this->scope = $scope;
        $this->credentials = $credentials;
        $this->log = $log;
    }

    /**
     * Perform initial setup of Ecom+
     * @return void
     */
    public function beforeLaunch(): void
    {
        $environment = $this->credentials->getEnvironment();
        $jwtScope = $environment === EnvironmentEnum::PROD
            ? EcomScope::MERCHANT_API
            : EcomScope::MOCK_MERCHANT_API;

        try {
            EcomConfig::setup(
                jwtAuth: new Jwt(
                    clientId: $this->config->getClientId(
                        scopeCode: $this->scope->getId()
                    ),
                    clientSecret: $this->config->getClientSecret(
                        scopeCode: $this->scope->getId()
                    ),
                    scope: $jwtScope,
                    grantType: GrantType::CREDENTIALS,
                ),
                isProduction: $environment === EnvironmentEnum::PROD
            );
        } catch (Throwable $e) {
            $this->log->exception(error: $e);
        }
    }
}
