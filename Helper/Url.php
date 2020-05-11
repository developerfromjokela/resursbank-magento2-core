<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Backend\Model\UrlInterface;

/**
 * @package Resursbank\Core\Helper
 */
class Url extends AbstractHelper
{
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param Context $context
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        UrlInterface $url
    ) {
        $this->url = $url;

        parent::__construct($context);
    }

    /**
     * Return URL to admin page.
     *
     * @param string $path
     * @return string
     */
    public function getAdminUrl(
        string $path
    ): string {
        return $this->url->getUrl(
            $path,
            [
                '_secure' => $this->_getRequest()->isSecure(),
                'store' => $this->_getRequest()->getParam('store'),
                'website' => $this->_getRequest()->getParam('website')
            ]
        );
    }
}
