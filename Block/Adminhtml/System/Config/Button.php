<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button as MagentoButton;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Resursbank\Core\Helper\Url;

/**
 * Centralised code for buttons in admin config.
 */
class Button extends Field
{
    /**
     * @var Url
     */
    private Url $url;

    /**
     * @param Url $url
     * @param Context $context
     */
    public function __construct(
        Url $url,
        Context $context
    ) {
        $this->url = $url;

        parent::__construct($context);
    }

    /**
     * Unset some non-related element parameters.
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Render and return button HTML.
     *
     * @param AbstractElement $element
     * @param string $label
     * @param string $path
     * @return string
     * @throws LocalizedException
     */
    public function create(
        AbstractElement $element,
        string $label,
        string $path
    ): string {
        /** @noinspection PhpUndefinedMethodInspection */
        /** @phpstan-ignore-next-line */
        $this->setElement($element);

        /** @phpstan-ignore-next-line */
        return $this->getLayout()
            ->createBlock(MagentoButton::class)
            ->setType('button')
            ->setClass('scalable')
            ->setLabel(__($label))
            ->setOnClick(
                "setLocation('{$this->url->getAdminUrl($path)}')"
            )
            ->toHtml();
    }
}
