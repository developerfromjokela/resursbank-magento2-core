<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
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
     * @param string $group
     * @param string $key
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return string
     */
    public function get(
        string $group,
        string $key,
        string $scopeType = ScopeInterface::SCOPE_STORE,
        ?string $scopeCode = null
    ) {
        return $this->reader->getValue(
            $this->getPath($group, $key),
            $scopeType,
            $scopeCode
        );
    }

    /**
     * @param string $group
     * @param string $key
     * @param string $value
     * @param string $scopeType
     * @param int $scopeId
     * @return void
     */
    public function set(
        string $group,
        string $key,
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
     * @param string $group
     * @param string $key
     * @return string
     */
    protected function getPath(
        string $group,
        string $key
    ): string {
        return "resursbank/{$group}/{$key}";
    }
}
