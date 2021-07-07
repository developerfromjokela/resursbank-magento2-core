/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

// phpcs:ignoreFile
define(
    [
        'ko'
    ],
    /**
     * @param ko
     * @returns {Readonly<RbC.Model.ReadMore>}
     */
    function (
        ko
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
            /** @type {RbC.Ko.Boolean} */
            isFetchingData: ko.observable(false)
        };

        /**
         * @constant
         * @namespace RbC.Model.ReadMore
         */
        var EXPORT = {
            /**
             * @param {Object} [data]
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
            })
        };

        return Object.freeze(EXPORT);
    }
);
