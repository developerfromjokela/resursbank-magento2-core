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
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;

/**
 * Provides methods to simplify config handling.
 *
 * @package Resursbank\Core\Helper
 */
class Config extends AbstractHelper
{
    /**
     * Active section, defined by subclasses (see
     * \Resursbank\Core\Helper\Config\*).
     *
     * @var string
     */
    protected $section = '';

    /**
     * @var ScopeConfigInterface
     */
    private $reader;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var UrlInterface
     */
    private $backendUrl;

    /**
     * @var State
     */
    private $state;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $reader
     * @param WriterInterface $writer
     * @param UrlInterface $backendUrl
     * @param State $state
     * @param Context $context
     */
    public function __construct(
        ScopeConfigInterface $reader,
        WriterInterface $writer,
        UrlInterface $backendUrl,
        State $state,
        Context $context
    ) {
        $this->reader = $reader;
        $this->writer = $writer;
        $this->backendUrl = $backendUrl;
        $this->state = $state;

        parent::__construct($context);
    }

    /**
     * Retrieve config value.
     *
     * @param string $key
     * @param string $group
     * @param null|string $scopeCode
     * @return mixed
     * @throws Exception
     */
    public function getSetting(
        string $key,
        string $group,
        string $scopeCode = null
    ) {
        if ($this->section === '') {
            throw new Exception('Undefined section.');
        }

        return $this->reader->getValue(
            "{$this->section}/{$group}/{$key}",
            ScopeInterface::SCOPE_STORE,
            $this->getScope($scopeCode)
        );
    }

    /**
     * Update config value.
     *
     * @param string $key
     * @param string $group
     * @param string $value
     * @param int $scopeCode
     * @return void
     * @throws Exception
     */
    public function setSetting(
        string $key,
        string $group,
        string $value,
        int $scopeCode = 0
    ) {
        if ($this->section === '') {
            throw new Exception('Undefined section.');
        }

        $this->writer->save(
            "{$this->section}/{$group}/{$key}",
            $value,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeCode
        );
    }

    /**
     * Retrieve config flag.
     *
     * @param string $key
     * @param string $group
     * @param null|string $scopeCode
     * @return mixed
     * @throws Exception
     */
    public function getFlag(
        string $key,
        string $group,
        string $scopeCode = null
    ) {
        if ($this->section === '') {
            throw new Exception('Undefined section.');
        }

        return $this->reader->isSetFlag(
            "{$this->section}/{$group}/{$key}",
            ScopeInterface::SCOPE_STORE,
            $this->getScope($scopeCode)
        );
    }

    /**
     * Build a URL out of a configuration path. The URL will be formatted as a
     * backend URL.
     *
     * @param string $configPath
     * @return string
     */
    public function buildUrl(
        string $configPath
    ): string {
        return $this->backendUrl->getUrl(
            $configPath,
            [
                '_secure' => $this->_getRequest()->isSecure(),
                'store' => $this->_getRequest()->getParam('store'),
                'website' => $this->_getRequest()->getParam('website')
            ]
        );
    }

    /**
     * Resolve scope.
     *
     * NOTE: A value of "null" when reading configuration data with
     * ScopeInterface::SCOPE_STORE will result in an unexpected behaviour, data
     * from the first available store view will be returned instead (if any).
     * For this reason, we need to convert the scopeCode to "0" instead.
     *
     * @param null|string $scope
     * @return int|string
     */
    private function getScope($scope = null)
    {
        return $scope === null ? $this->getDefaultScope() : $scope;
    }

    /**
     * Default scope code in adminhtml should be "0", while it should be "null"
     * on frontend.
     *
     * When in adminhtml we will attempt to resolve the store from your request
     * and letting it fallback to 0, indicating default.
     *
     * @return int|null
     */
    private function getDefaultScope()
    {
        $result = null;

        try {
            if ($this->state->getAreaCode() === Area::AREA_ADMINHTML) {
                $result = (int) $this->_getRequest()->getParam('store');
            }
        } catch (Exception $e) {
            // Ignore potential errors.
        }

        return $result;
    }
}
