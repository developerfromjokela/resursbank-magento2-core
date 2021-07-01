/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

define(
    [
        'jquery',
        'ko',
        'mage/translate',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/quote',
        'Resursbank_Core/js/view/remodal'
    ],
    /**
     * @param $
     * @param ko
     * @param $t
     * @param PriceUtils
     * @param Quote
     * @param Component
     * @returns {*}
     */
    function (
        $,
        ko,
        $t,
        PriceUtils,
        Quote,
        Component
    ) {
        'use strict';

        return Component.extend({
            initialize: function () {
                var me = this;

                me._super();

                Quote.totals.subscribe(function () {
                    me.totalsHasChanged = true;
                });
            }
        });
    }
);
