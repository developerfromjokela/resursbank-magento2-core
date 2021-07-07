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
        'Resursbank_Core/js/model/read-more'
    ],
    /**
     * @param $
     * @param $t
     * @param Url
     * @param ko
     * @param PriceBox
     * @param {Object} PriceOptions
     * @param {RbC.Model.ReadMore} Model
     * @returns {Readonly<RbC.Lib.ReadMore>}
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
         * @namespace RbC.Lib.ReadMore
         */
        var EXPORT = {
            /**
             * @param {number} price - Float.
             * @param {string} methodCode
             * @param {string} formKey
             * @returns {Deferred}
             */
            getCostOfPurchase: function (price, methodCode, formKey) {
                var deferred = $.Deferred();
                var call = new $.ajax({
                    method: 'GET',
                    url: Url.build('resursbank_core/frontend/readmore'),
                    data: {
                        form_key: formKey,
                        code: methodCode,
                        price: price
                    }
                });

                call.done(function (response) {
                    deferred.resolve(response);
                });

                call.fail(function () {
                    deferred.reject($t('No information could be found.'));
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
