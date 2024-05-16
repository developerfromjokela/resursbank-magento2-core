<?php
/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\Core\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Resursbank\Core\Helper\Url;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Module\Store\Widget\GetStores as GetStoresWidget;
use Resursbank\Core\Helper\Log;
use Throwable;

/**
 * Render widget utilised to fetch stores in admin config.
 */
class GetStores extends Template
{
    /**
     * @param Context $context
     * @param Log $log
     * @param Url $url
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly Log $log,
        public readonly Url $url,
        array $data = []
    ) {
        parent::__construct(
            context: $context,
            data: $data
        );
    }

    /**
     * Resolve widget content.
     *
     * @retrun string
     */
    public function getWidget(): string
    {
        try {
            Config::validateInstance();

            $widget = new GetStoresWidget(
                fetchUrlCallback: 'getResursBankFetchStoresUrl',
                automatic: false,
                storeSelectId: 'payment_other_resursbank_section_api_store',
                environmentSelectId: 'payment_other_resursbank_section_api_environment',
                flowSelectId: 'payment_other_resursbank_section_api_flow'
            );

            return $widget->content;
        } catch (Throwable $error) {
            $this->log->exception(error: $error);
        }

        return '';
    }

    /**
     * Placeholder getUrls method.
     *
     * @return array
     */
    public function getUrls(): array
    {
        return [];
    }
}
