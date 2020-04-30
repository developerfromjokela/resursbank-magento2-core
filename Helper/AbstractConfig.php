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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Provides methods to simplify config handling.
 *
 * @package Resursbank\Core\Helper
 */
abstract class AbstractConfig extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    private $reader;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $reader
     * @param WriterInterface $writer
     * @param Context $context
     */
    public function __construct(
        ScopeConfigInterface $reader,
        WriterInterface $writer,
        Context $context
    ) {
        $this->reader = $reader;
        $this->writer = $writer;

        parent::__construct($context);
    }

    /**
     * NOTE: While scopeId can be provided instead of scopeCode the code is more
     * accurate to use here since Magento will anyways resolve the code from the
     * provided id. This is because the value is read from the compiled cached
     * array, not directly from the database table.
     *
     * @param string $key
     * @param string $group
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function get(
        string $key,
        string $group,
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ) {
        return $this->reader->getValue(
            $this->getPath($group, $key),
            $scopeType,
            $this->getScope($scopeCode)
        );
    }

    /**
     * @param string $key
     * @param string $group
     * @param string $value
     * @param string $scopeType
     * @param int $scopeId
     * @return void
     */
    public function set(
        string $key,
        string $group,
        string $value,
        string $scopeType = ScopeInterface::SCOPE_STORE,
        int $scopeId = 0
    ): void {
        $this->writer->save(
            $this->getPath($group, $key),
            $value,
            $scopeType,
            $scopeId
        );
    }

    /**
     * @param string $key
     * @param string $group
     * @return string
     */
    private function getPath(
        string $key,
        string $group
    ): string {
        return "resursbank/{$group}/{$key}";
    }
}
