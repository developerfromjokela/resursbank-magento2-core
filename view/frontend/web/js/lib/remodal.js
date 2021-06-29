/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

// phpcs:ignoreFile
define(
    [
        'jquery'
    ],
    /**
     * @param $
     * @returns {Readonly<RbC.Lib.Remodal>}
     */
    function (
        $
    ) {
        'use strict';

        /**
         * @constant
         * @namespace RbC.Lib.Remodal
         */
        var EXPORT = {
            /**
             * Retrieves the element that will be used to initialize the
             * Remodal instance from the DOM.
             *
             * @param {string} remodalId
             * @returns {jQuery}
             */
            getRemodalInstance: function (remodalId) {
                return $('[data-remodal-id="' + remodalId + '"]');
            },

            /**
             * Returns the jQuery object with the loader element.
             *
             * @param {string} loaderId
             * @returns {jQuery}
             */
            getLoader: function (loaderId) {
                return $('#' + loaderId);
            },

            /**
             * Returns the jQuery object with the content element.
             *
             * @param {string} contentId
             * @returns {jQuery}
             */
            getContent: function (contentId) {
                return $('#' + contentId);
            },

            /**
             * @param {string} id
             * @returns {string}
             */
            createId: function (id) {
                return 'resursbank-part-payment-remodal' +
                    (id === '' ? '' : '-' + id);
            }
        };

        return Object.freeze(EXPORT);
    }
);
