<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Api\Data;

/**
 * @package Resursbank\Core\Api\Data
 */
interface AccountInterface
{
    /**
     * @var string
     */
    public const ACCOUNT_ID = 'account_id';

    /**
     * @var string
     */
    public const USERNAME = 'username';

    /**
     * @var string
     */
    public const ENVIRONMENT = 'environment';

    /**
     * @var string
     */
    public const SALT = 'salt';

    /**
     * @var string
     */
    public const CREATED_AT = 'created_at';

    /**
     * @var string
     */
    public const UPDATED_AT = 'updated_at';

    /**
     * Get ID.
     *
     * @param int|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return int|null
     */
    public function getAccountId(?int $default = null): ?int;

    /**
     * Set ID.
     *
     * @param int $accountId
     * @return self
     */
    public function setAccountId(int $accountId): self;

    /**
     * Get username.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getUsername(?string $default = null): ?string;

    /**
     * Set username.
     *
     * @param string $username
     * @return self
     */
    public function setUsername(string $username): self;

    /**
     * Get environment.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getEnvironment(?string $default = null): ?string;

    /**
     * Set environment.
     *
     * @param string $environment
     * @return self
     */
    public function setEnvironment(string $environment): self;

    /**
     * Get callback salt.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getSalt(?string $default = null): ?string;

    /**
     * Set callback salt.
     *
     * @param string $salt
     * @return self
     */
    public function setSalt(string $salt): self;

    /**
     * Get entry creation time.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getCreatedAt(?string $default = null): ?string;

    /**
     * Set entry creation time.
     *
     * @param string $timestamp - Must be a valid MySQL timestamp.
     * @return self
     */
    public function setCreatedAt(string $timestamp): self;

    /**
     * Get entry update time.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getUpdatedAt(?string $default = null): ?string;

    /**
     * Set entry update time.
     *
     * @param string $timestamp - Must be a MySQL valid timestamp.
     * @return self
     */
    public function setUpdatedAt(string $timestamp): self;
}
