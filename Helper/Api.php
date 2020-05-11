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

namespace Resursbank\Core\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Resursbank\Core\Model\Api\Credentials;
use Resursbank\RBEcomPHP\ResursBank;

/**
 * Provides business logic to handle API calls through the EComPHP library.
 *
 * @package Resursbank\Core\Helper
 */
class Api extends AbstractHelper
{
    /**
     * @param Credentials $credentials
     * @return ResursBank
     * @throws Exception
     */
    public function getConnection(
        Credentials $credentials
    ) {
        return new ResursBank(
            $credentials->getUsername(),
            $credentials->getPassword(),
            $credentials->getEnvironment()
        );
    }
}
