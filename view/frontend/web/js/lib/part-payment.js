/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

// phpcs:ignoreFile
define(
    [
        'jquery',
        'mage/translate',
        'mage/url',
        'ko',
        'priceBox',
        'Magento_Catalog/js/price-options',
        'Resursbank_Core/js/model/part-payment'
    ],
    /**
     * @param $
     * @param $t
     * @param Url
     * @param ko
     * @param PriceBox
     * @param {Object} PriceOptions
     * @param {RbPp.Model} Model
     * @returns {Readonly<RbC.Lib.PartPayment>}
     */
    function (
        $,
        $t,
        Url,
        ko,
        PriceBox,
        PriceOptions,
        Model
    ) {
        'use strict';

        /**
         * @constant
         * @namespace RbC.Lib.PartPayment
         */
        var EXPORT = {
            /**
             * Creates and executes a call to the server that will fetch a table
             * of part payment information about a given price and payment method.
             * The table is returned as an HTML string.
             *
             * @param {number} price - Float.
             * @param {string} methodCode
             * @returns {Deferred}
             */
            getCostOfPurchase: function (price, methodCode) {
                var deferred = $.Deferred();
                var call = new $.ajax({
                    method: 'GET',
                    url: Url.build('resursbank_partpayment/frontend/html'),
                    data: {
                        form_key: Model.formKey(),
                        code: methodCode,
                        price: price
                    }
                });

                call.done(function (response) {
                    deferred.resolve(response);
                });

                call.fail(function () {
                    deferred.reject($t(
                        'No part payment information exists for this product.'
                    ));
                });

                call.always(function () {
                    Model.isFetchingData(false);
                });

                Model.isFetchingData(true);

                return deferred;
            }
        };

        return Object.freeze(EXPORT);
    }
);
