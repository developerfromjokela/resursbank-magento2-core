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

namespace Resursbank\Core\Model;

use Magento\Framework\Model\AbstractModel;
use Resursbank\Core\Api\Data\AccountInterface;
use Resursbank\Core\Model\ResourceModel\Account as Resource;

/**
 * @package Resursbank\Core\Model
 */
class Account extends AbstractModel implements AccountInterface
{
    /**
     * Initialize model.
     */
    protected function _construct()
    {
        $this->_init(Resource::class);
    }

    /**
     * @inheritDoc
     */
    public function getAccountId(?int $default = null): ?int
    {
        $result = $this->getData(self::ACCOUNT_ID);

        return $result === null ? $default : (int)$result;
    }

    /**
     * @inheritDoc
     */
    public function setAccountId(int $id): AccountInterface
    {
        $this->setData(self::ACCOUNT_ID, $id);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUsername(?string $default = null): ?string
    {
        $result = $this->getData(self::USERNAME);

        return $result === null ? $default : (string)$result;
    }

    /**
     * @inheritDoc
     */
    public function setUsername(string $username): AccountInterface
    {
        $this->setData(self::USERNAME, $username);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEnvironment(?string $default = null): ?string
    {
        $result = $this->getData(self::ENVIRONMENT);

        return $result === null ? $default : (string)$result;
    }

    /**
     * @inheritDoc
     */
    public function setEnvironment(string $environment): AccountInterface
    {
        $this->setData(self::ENVIRONMENT, $environment);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSalt(?string $default = null): ?string
    {
        $result = $this->getData(self::SALT);

        return $result === null ? $default : (string)$result;
    }

    /**
     * @inheritDoc
     */
    public function setSalt(string $salt): AccountInterface
    {
        $this->setData(self::SALT, $salt);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(?string $default = null): ?string
    {
        $result = $this->getData(self::CREATED_AT);

        return $result === null ? $default : (string)$result;
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $timestamp): AccountInterface
    {
        $this->setData(self::CREATED_AT, $timestamp);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(?string $default = null): ?string
    {
        $result = $this->getData(self::UPDATED_AT);

        return $result === null ? $default : (string)$result;
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(string $timestamp): AccountInterface
    {
        $this->setData(self::UPDATED_AT, $timestamp);

        return $this;
    }
}
