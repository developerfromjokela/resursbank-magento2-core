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
