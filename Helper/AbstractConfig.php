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
 * Provides basic methods to simplify configuration read/write.
 */
abstract class AbstractConfig extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $reader;

    /**
     * @var WriterInterface
     */
    protected WriterInterface $writer;

    /**
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
     * provided id. This is because the value is read from the compiled, cached,
     * array, not directly from the database table.
     *
     * @param string $group
     * @param string $key
     * @param null|string $scopeCode
     * @param string $scopeType
     * @return mixed
     */
    public function get(
        string $group,
        string $key,
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ) {
        return $this->reader->getValue(
            $this->getPath($group, $key),
            $scopeType,
            $scopeCode
        );
    }

    /**
     * NOTE: For an explanation of $scopeCode argument please see the get method
     * above.
     *
     * @param string $group
     * @param string $key
     * @param null|string $scopeCode
     * @param string $scopeType
     * @return bool
     */
    public function isEnabled(
        string $group,
        string $key,
        ?string $scopeCode,
        string $scopeType = ScopeInterface::SCOPE_STORES
    ): bool {
        return $this->reader->isSetFlag(
            $this->getPath($group, $key),
            $scopeType,
            $scopeCode
        );
    }

    /**
     * NOTE: Unlike the reader (see the get() method above), the writer expects
     * you to provide an id of the intended resource (website/store/view) rather
     * than a code.
     *
     * @param string $group
     * @param string $key
     * @param string $value
     * @param int $scopeId
     * @param string $scopeType
     * @return void
     */
    public function set(
        string $group,
        string $key,
        string $value,
        int $scopeId = 0,
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    ): void {
        $this->writer->save(
            $this->getPath($group, $key),
            $value,
            $scopeType,
            $scopeId
        );
    }

    /**
     * NOTE: Unlike the reader (see the get() method above), the writer expects
     * you to provide an id of the intended resource (website/store/view) rather
     * than a code.
     *
     * @param string $group
     * @param string $key
     * @param int $scopeId
     * @param string $scopeType
     * @return void
     */
    public function delete(
        string $group,
        string $key,
        int $scopeId = 0,
        string $scopeType = ScopeInterface::SCOPE_STORE
    ): void {
        $this->writer->delete(
            $this->getPath($group, $key),
            $scopeType,
            $scopeId
        );
    }

    /**
     * Retrieved path to one of our settings based on provided group / key.
     *
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
