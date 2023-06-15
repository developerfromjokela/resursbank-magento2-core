<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Block\Adminhtml\System\Config\GetStores;

use Magento\Backend\Block\Widget\Button as MagentoButton;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Resursbank\Core\Block\Adminhtml\System\Config\Button as BaseButton;

/**
 * Render button to fetch stores.
 */
class Button extends BaseButton
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
        $this->setElement($element);

        $block = $this->getLayout()
            ->createBlock(type: MagentoButton::class);

        /**
         * @noinspection PhpPossiblePolymorphicInvocationInspection
         * @phpstan-ignore-next-line
         */
        return $block->setType(type: 'button')
            ->setClass('scalable')
            ->setLabel(__('rb-fetch-stores'))
            ->setId('resursbank_fetch_stores_btn')
            ->toHtml();
    }
}
