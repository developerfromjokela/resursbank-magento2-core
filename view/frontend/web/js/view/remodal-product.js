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
        'Resursbank_Core/js/view/remodal',
        'Resursbank_Core/js/model/part-payment'
    ],
    /**
     * @param $
     * @param ko
     * @param $t
     * @param PriceUtils
     * @param Component
     * @param {RbC.Model.PartPayment} PartPaymentModel
     * @returns {*}
     */
    function (
        $,
        ko,
        $t,
        PriceUtils,
        Component,
        PartPaymentModel
    ) {
        'use strict';

        return Component.extend({
            initialize: function () {
                var me = this;

                me._super();

                var ogUpdateRemodalWindow = me.updateRemodalWindow;

                /**
                 * Fetches updated part payment information from the server and
                 * updates the remodal content with it.
                 */
                me.updateRemodalWindow = function () {
                    ogUpdateRemodalWindow(PartPaymentModel.finalPrice());
                }

                PartPaymentModel.finalPrice.subscribe(function () {
                    me.totalsHasChanged = true;
                });
            }
        });
    }
);
