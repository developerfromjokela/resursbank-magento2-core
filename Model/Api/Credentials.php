<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

namespace Resursbank\Core\Model\Api;

use Magento\Framework\Exception\ValidatorException;

// See to-do: 2020-04-27
// use Resursbank\RBEcomPHP\RESURS_ENVIRONMENTS;

/**
 * @todo 2020-04-27 - In the middle of module separation. Can't use
 *       Ecom constants until a later date because Ecom would become a
 *       dependency that we cannot have during this stage in
 *       development. Will use static values instead.
 */

/**
 * @package Resursbank\Core\Api
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
     * @param int $environment
     * @return self
     * @throws ValidatorException
     */
    public function setEnvironment(int $environment): self
    {
        // See to-do: 2020-04-27
        // RESURS_ENVIRONMENTS::ENVIRONMENT_TEST :
        // RESURS_ENVIRONMENTS::ENVIRONMENT_PRODUCTION;

        if ($environment < 0 || $environment > 2) {
            throw new ValidatorException(
                __(
                    'Invalid environment value %1. ' .
                    '0 = prod, 1 = test, 2 = unspecified.',
                    $environment
                )
            );
        }

        $this->environment = $environment;

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
        return $this->getEnvironment() === 1 ?
            self::ENVIRONMENT_CODE_TEST :
            self::ENVIRONMENT_CODE_PROD;

        // See to-do: 2020-04-27
//        return $this->getEnvironment() === RESURS_ENVIRONMENTS::ENVIRONMENT_TEST ?
//            self::ENVIRONMENT_CODE_TEST :
//            self::ENVIRONMENT_CODE_PROD;
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
