<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Model\Api;

use Magento\Framework\Exception\ValidatorException;
use Resursbank\RBEcomPHP\RESURS_ENVIRONMENTS;

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
     * @var string
     */
    private $country;

    /**
     * @param string $username
     * @return self
     * @throws ValidatorException
     */
    public function setUsername(
        string $username
    ): self {
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
    public function setPassword(
        string $password
    ): self {
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
    public function setEnvironment(
        int $environment
    ): self {
        if ($environment !== RESURS_ENVIRONMENTS::PRODUCTION &&
            $environment !== RESURS_ENVIRONMENTS::TEST
        ) {
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

    /**
     * @param string $country
     * @return self
     */
    public function setCountry(
        string $country
    ): self {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }
}
