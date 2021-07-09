/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

// phpcs:ignoreFile
define(
    [],
    /**
     * @returns {Readonly<RbC.Lib.Checkout>}
     */
    function () {
        'use strict';

        /**
         * KnockoutJS.
         * 
         * This namespace is required if we want to have better type info about
         * our KnockoutJS variables.
         * 
         * @namespace RbC.Ko
         */

        /**
         * @callback RbC.Ko.String
         * @param {string} [value]
         * @return {string}
         */

        /**
         * @callback RbC.Ko.Boolean
         * @param {boolean} [value]
         * @return {boolean}
         */

        /**
         * @callback RbC.Ko.Number
         * @param {number} [value]
         * @return {number}
         */

        /**
         * @constant
         * @namespace RbC.Lib.Checkout
         */
        var EXPORT = {
            /**
             * Returns an object with price format rules for the checkout page.
             *
             * @returns {Object|null} Object with rules, or null if such an object
             * can't be found.
             */
            getPriceFormat: function() {
                return window.hasOwnProperty('checkoutConfig') &&
                    window.checkoutConfig.hasOwnProperty('priceFormat') ?
                        window.checkoutConfig.priceFormat :
                        null;
            }
        };

        return Object.freeze(EXPORT);
    }
);
