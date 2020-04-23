<?php
/**
 * Copyright 2016 Resurs Bank AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Resursbank\Checkout\Model\Api;

use Resursbank\RBEcomPHP\RESURS_ENVIRONMENTS;

/**
 * Class Credentials
 * @package Resursbank\Checkout\Api
 */
class Credentials
{
    /**
     * @var string
     */
    public const ENVIRONMENT_CODE_TEST = 'test';

    /**
     * @var string
     */
    public const ENVIRONMENT_CODE_PROD = 'prod';

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var int
     */
    private $environment;

    /**
     * @return bool
     */
    public function hasCredentials(): bool
    {
        return (
            $this->getUsername() !== '' &&
            $this->getPassword() !== ''
        );
    }

    /**
     * @param string $username
     * @return self
     */
    public function setUsername($username): self
    {
        $this->username = (string) $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @param string $password
     * @return self
     */
    public function setPassword($password): self
    {
        $this->password = (string) $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * @param string|int $environment (test|production, 1|0)
     * @return self
     */
    public function setEnvironment($environment): self
    {
        if (is_string($environment)) {
            $this->environment = $environment === 'test' ?
                RESURS_ENVIRONMENTS::ENVIRONMENT_TEST :
                RESURS_ENVIRONMENTS::ENVIRONMENT_PRODUCTION;
        } else {
            $this->environment = (int) $environment;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getEnvironment(): int
    {
        return (int) $this->environment;
    }

    /**
     * Set multiple values at the same time.
     *
     * @param array $data
     */
    public function setData(array $data): void
    {
        if (isset($data['username'])) {
            $this->setUsername($data['username']);
        }

        if (isset($data['environment'])) {
            $this->setEnvironment($data['environment']);
        }

        if (isset($data['password'])) {
            $this->setPassword($data['password']);
        }
    }

    /**
     * Retrieve hash value based on credentials.
     *
     * @return string
     */
    public function getHash(): string
    {
        return sha1(
            $this->getUsername() .
            $this->getEnvironment()
        );
    }

    /**
     * Retrieve readable environment code.
     *
     * @return string
     */
    public function getEnvironmentCode(): string
    {
        return $this->getEnvironment() === RESURS_ENVIRONMENTS::ENVIRONMENT_TEST ?
            self::ENVIRONMENT_CODE_TEST :
            self::ENVIRONMENT_CODE_PROD;
    }

    /**
     * Retrieve readable unique method code suffix.
     *
     * @return string - Returns a lowercased string.
     */
    public function getMethodSuffix(): string
    {
        return strtolower(
            $this->getUsername() . '_' . $this->getEnvironmentCode()
        );
    }
}
