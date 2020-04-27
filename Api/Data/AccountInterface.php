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
     * @param int $id
     * @return self
     */
    public function setAccountId(int $id): self;

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
     * Get the salt.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getSalt(?string $default = null): ?string;

    /**
     * Set the salt.
     *
     * @param string $salt
     * @return self
     */
    public function setSalt(string $salt): self;

    /**
     * Get the time when the event was created.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getCreatedAt(?string $default = null): ?string;

    /**
     * Set the time when the event entry was created.
     *
     * @param string $timestamp - Must be a MySQL valid timestamp.
     * @return self
     */
    public function setCreatedAt(string $timestamp): self;

    /**
     * Get the time when the event was updated.
     *
     * @param string|null $default - Value to be returned in the event that
     * a value couldn't be retrieved from the database.
     * @return string|null
     */
    public function getUpdatedAt(?string $default = null): ?string;

    /**
     * Set the time when the event entry was updated.
     *
     * @param string $timestamp - Must be a MySQL valid timestamp.
     * @return self
     */
    public function setUpdatedAt(string $timestamp): self;
}
