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
     * @returns {Readonly<RbC.Lib.Product>}
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
         * @namespace RbC.Lib.Product
         */
        var EXPORT = {
            /**
             * Get the final price of the current product.
             *
             * @param {string} productType
             * @returns {number} Returns -1 if a final price could not be found.
             */
            getFinalPrice: function (productType) {
                var returnPrice = -1;
                var instance = EXPORT.getPriceBoxInstance(productType);
                var displayPrices = instance.cache.displayPrices;

                if (displayPrices.finalPrice) {
                    returnPrice = displayPrices.finalPrice.amount;
                } else if (displayPrices.basePrice) {
                    returnPrice = displayPrices.basePrice.amount;
                }

                return returnPrice;
            },

            /**
             * Returns an object with price format rules for the current
             * product.
             *
             * @param {string} productType
             * @returns {Object|null} Object with rules, or null if such an
             * object can't be found.
             */
            getPriceFormat: function (productType) {
                var result = null;
                var priceBoxInstance;

                try {
                    priceBoxInstance = EXPORT.getPriceBoxInstance(productType);
                    result = priceBoxInstance
                        .options
                        .priceConfig
                        .priceFormat;
                } catch (error) {
                    console.error('Price format could not be determined.');
                }

                return result;
            },

            /**
             * HTMLElement of the price box on the product page.
             *
             * @param {string} productType
             * @returns {jQuery}
             */
            getPriceBoxEl: function (productType) {
                return productType === 'bundle' ?
                    $('.price-box.price-configured_price') :
                    $('.price-box');
            },

            /**
             * jQuery instance of the price box element on a product page that
             * will allow us to get the product's prices.
             *
             * @returns {Object|null}
             */
            getPriceBoxInstance: function (productType) {
                var result = null;

                try {
                    result = EXPORT.getPriceBoxEl(productType)
                        .data('magePriceBox');
                } catch (error) {
                    console.error('Could not find the price box instance.');
                }

                return result;
            }
        };

        return Object.freeze(EXPORT);
    }
);
