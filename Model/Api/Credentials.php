<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

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
 * @package Resursbank\Core\Model\Api
 */
class Credentials
{
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
     * @param string $username
     * @return self
     * @throws ValidatorException
     */
    public function setUsername(string $username): self
    {
        if ($username === '') {
            throw new ValidatorException(
                __('Username cannot be empty.')
            );
        }

        $this->username = $username;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $password
     * @return self
     * @throws ValidatorException
     */
    public function setPassword(string $password): self
    {
        if ($password === '') {
            throw new ValidatorException(
                __('Password cannot be empty.')
            );
        }

        $this->password = $password;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
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

        if ($environment !== 0 && $environment !== 1) {
            throw new ValidatorException(
                __(
                    'Invalid environment value %1. 0 = prod, 1 = test.',
                    $environment
                )
            );
        }

        $this->environment = $environment;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getEnvironment(): ?int
    {
        return $this->environment;
    }
}
