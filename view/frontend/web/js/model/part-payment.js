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
        'priceBox'
    ],
    /**
     * @param $
     * @param $t
     * @param Url
     * @param ko
     * @param PriceBox
     * @returns {Readonly<RbC.Model.PartPayment>}
     */
    function (
        $,
        $t,
        Url,
        ko,
        PriceBox
    ) {
        'use strict';

        /**
         * Whether the model has been initialized.
         *
         * @type {boolean}
         */
        var initialized = false;

        /**
         * @constant
         * @type {object}
         */
        var PRIVATE = {
            /** @type {RbC.Ko.Number} */
            duration: ko.observable(0),

            /** @type {RbC.Ko.String} */
            formKey: ko.observable(''),

            /** @type {RbC.Ko.Number} */
            suggestedPrice: ko.observable(0),

            /** @type {RbC.Ko.String} */
            productType: ko.observable(''),

            /** @type {RbC.Ko.Boolean} */
            isFetchingData: ko.observable(false),

            /** @type {RbC.Ko.Number} */
            finalPrice: ko.observable(0)
        };

        /**
         * @constant
         * @namespace RbC.Model.PartPayment
         */
        var EXPORT = {
            /**
             * @param {Object} [data]
             * @param {number} [data.duration]
             * @param {number} [data.suggestedPrice]
             * @param {number} [data.finalPrice]
             * @param {string} [data.formKey]
             * @param {string} [data.productType]
             * @param {boolean} [data.isFetchingData]
             */
            init: function (data) {
                if (!initialized) {
                    Object.keys(data).forEach(function (key) {
                        if (PRIVATE.hasOwnProperty(key)) {
                            PRIVATE[key](data[key]);
                        }
                    });

                    initialized = true;
                }
            },

            /**
             * How many months the customer is expected to make regular payments
             * for the purchased product.
             *
             * @type {RbC.Ko.Number}
             */
            duration: ko.computed({
                read: function () {
                    return PRIVATE.duration();
                },

                write: function (value) {
                    if (typeof value === 'number') {
                        PRIVATE.duration(value);
                    }
                }
            }),

            /**
             * Form key supplied in AJAX calls.
             *
             * @type {RbC.Ko.String}
             */
            formKey: ko.computed(function () {
                return PRIVATE.formKey();
            }),

            /**
             * The calculated price the customer is expected to pay each month
             * for the entirety of the duration.
             *
             * @type {RbC.Ko.Number}
             */
            suggestedPrice: ko.computed({
                read: function () {
                    return PRIVATE.suggestedPrice();
                },

                write: function (value) {
                    if (typeof value === 'number') {
                        PRIVATE.suggestedPrice(value);
                    }
                }
            }),

            /**
             * The type (simple, bundle etc.) of the product that the customer
             * is currently viewing.
             *
             * @type {RbC.Ko.String}
             */
            productType: ko.computed({
                read: function () {
                    return PRIVATE.productType();
                }
            }),

            /**
             * Whether the model is currently fetching data.
             *
             * @type {RbC.Ko.Boolean}
             */
            isFetchingData: ko.computed({
                read: function () {
                    return PRIVATE.isFetchingData();
                },

                write: function (value) {
                    if (typeof value === 'boolean') {
                        PRIVATE.isFetchingData(value);
                    }
                }
            }),

            /**
             * Observes the final price. Returns a number when read, accepts
             * only numbers when written to.
             *
             * @type {RbC.Ko.Number}
             */
            finalPrice: ko.computed({
                read: function () {
                    return PRIVATE.finalPrice();
                },

                write: function (value) {
                    if (typeof value === 'number') {
                        PRIVATE.finalPrice(value);
                    }
                }
            })
        };

        return Object.freeze(EXPORT);
    }
);
