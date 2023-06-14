<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Block\Adminhtml\System\Config\Methods;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Resursbank\Core\Block\Adminhtml\System\Config\Button;

/**
 * Render button to sync payment methods.
 */
class Sync extends Button
{
    /**
     * @inheritDoc
     *
     * @param AbstractElement $element
     * @return string
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function _getElementHtml(
        AbstractElement $element
    ): string {
        return $this->create(
            $element,
            'Sync data',
            'resursbank_core/data/sync'
        );
    }
}
