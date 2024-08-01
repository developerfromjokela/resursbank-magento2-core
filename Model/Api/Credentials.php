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
    private string $username;

    /**
     * @var string
     */
    private string $password;

    /**
     * @var int
     */
    private int $environment;

    /**
     * @var string
     */
    private string $country;

    /**
     * Set username.
     *
     * @param string $username
     * @return self
     * @throws ValidatorException
     */
    public function setUsername(
        string $username
    ): self {
        if ($username === '') {
            throw new ValidatorException(
                __('rb-username-cannot-be-empty')
            );
        }

        $this->username = $username;

        return $this;
    }

    /**
     * Get username.
     *
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username ?? null;
    }

    /**
     * Set password.
     *
     * @param string $password
     * @return self
     * @throws ValidatorException
     */
    public function setPassword(
        string $password
    ): self {
        if ($password === '') {
            throw new ValidatorException(
                __('rb-password-cannot-be-empty')
            );
        }

        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password ?? null;
    }

    /**
     * Set environment.
     *
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
                    'rb-invalid-environment-value',
                    $environment
                )
            );
        }

        $this->environment = $environment;

        return $this;
    }

    /**
     * Get environment.
     *
     * @return int|null
     */
    public function getEnvironment(): ?int
    {
        return $this->environment ?? null;
    }

    /**
     * Set country.
     *
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
     * Get country.
     *
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country ?? null;
    }
}
