/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

define(
    [
        'jquery',
        'ko',
        'mage/translate',
        'uiComponent',
        'Magento_Catalog/js/price-utils',
        'Resursbank_Core/js/lib/remodal',
        'Resursbank_Core/js/model/read-more',
        'Resursbank_Core/js/remodal'
    ],
    /**
     * @param $
     * @param ko
     * @param $t
     * @param Component
     * @param PriceUtils
     * @param {RbC.Lib.Remodal} RemodalLib
     * @param {RbC.Model.ReadMore} Model
     * @param Remodal
     * @returns {*}
     */
    function (
        $,
        ko,
        $t,
        Component,
        PriceUtils,
        RemodalLib,
        Model,
        Remodal
    ) {
        'use strict';

        /**
         * @namespace RbC.View
         */

        /**
         * @namespace RbC.View.Remodal
         * @memberOf RbC.View
         */

        /**
         * @typedef {object} RbC.View.Remodal.Props
         * @memberOf RbC.View.Remodal
         * @property {string} title
         * @property {string} id - ID of the remodal window.
         * @property {RbC.Ko.Boolean} open - Whether the modal can be opened.
         * @property {RbC.Ko.Boolean} update - Whether the modal is allowed to
         * update. If no data has been previously fetched, it will fetch
         * regardless of the flag's state. If data exists, it will only update
         * if this flag set to "true".
         * @property {function} requestFn
         * @property {function} onClose
         * @property {function} onOpen
         */

        /**
         * @typedef {object} RbC.View.Remodal.I
         * @memberOf RbC.View.Remodal
         * @property {RbC.Ko.String} content
         */

        /**
         * @typedef {
         * RbC.View.Remodal.Props & RbC.View.Remodal.I
         * } RbC.View.Remodal.Interface
         * @memberOf RbC.View.Remodal
         */

        return Component.extend({
            defaults: {
                template: 'Resursbank_Core/remodal',
                title: '',
                id: '',
                open: null,
                requestFn: null,
                onClose: null
            },

            initialize: function () {
                /**
                 * @type {RbC.View.Remodal.Interface}
                 */
                var me = this;

                /**
                 * An instance of Remodal, the modal JQuery plugin.
                 *
                 * @type {null|object}
                 */
                var remodalInstance = null;

                /**
                 * The AJAX spinner used when fetching data for the "Read more"
                 * links.
                 *
                 * @type {jQuery}
                 */
                var loaderEl;

                /**
                 * The content for the "Read more" links.
                 *
                 * @type {jQuery}
                 */
                var contentEl;

                me._super();

                /**
                 * @type {RbC.Ko.String}
                 */
                me.content = ko.observable('');

                /**
                 * @type {string}
                 */
                me.loaderId = me.id + '-loader';

                /**
                 * @type {string}
                 */
                me.contentId = me.id + '-content';

                /**
                 * @type {RbC.Ko.Boolean}
                 */
                me.update = ko.observable(false);

                /**
                 * @type {RbC.Ko.Boolean}
                 */
                me.hasTitle = ko.observable(me.title !== '');

                /**
                 * Initialize the Remodal window.
                 *
                 * NOTE: The modal window will open when initialized.
                 */
                function initRemodal()
                {
                    loaderEl = RemodalLib.getLoader(me.loaderId);
                    contentEl = RemodalLib.getContent(me.contentId);
                    remodalInstance = RemodalLib.getRemodalInstance(me.id)
                        .remodal({
                            hashTracking: false
                        });

                    $(document).on('closed', '.remodal', function () {
                        me.onClose(me.content);
                    });
                }

                /**
                 * Opens the Remodal window and fetches new data when needed.
                 */
                function openRemodalWindow()
                {
                    remodalInstance.open();

                    if (me.content() === '' || me.update()) {
                        me.updateRemodalWindow();
                    }
                }

                /**
                 * Fires the supplied "requestFn" request function to fetch
                 * information from the server and updates the remodal content
                 * with it.
                 */
                me.updateRemodalWindow = function () {
                    var request;

                    if (typeof me.requestFn !== 'function') {
                        throw Error('Request function is not supplied.');
                    }

                    $(contentEl).hide();
                    $(loaderEl).show();
                    Model.isFetchingData(true);

                    request = me.requestFn();

                    if (request !== undefined) {
                        request.done(function (response) {
                            $(loaderEl).fadeOut(1000, function () {
                                if (response.hasOwnProperty('html')) {
                                    me.content(response.html);
                                } else if (response.hasOwnProperty('error')) {
                                    me.content(status.error);
                                } else {
                                    me.content(
                                        $t('An unknown error occurred.')
                                    );
                                }

                                me.update(false);

                                $(contentEl).fadeIn(1000);
                            });
                        }).fail(function (error) {
                            $(loaderEl).fadeOut(1000, function () {
                                me.content(error);

                                me.update(false);

                                $(contentEl).fadeIn(1000);
                            });
                        }).always(function () {
                            Model.isFetchingData(false);
                        });
                    } else {
                        $(loaderEl).fadeOut(1000, function () {
                            $(contentEl).fadeIn(1000);
                        });
                    }
                }

                me.open.subscribe(function (value) {
                    if (value === true) {
                        if (remodalInstance === null) {
                            initRemodal();
                        }

                        openRemodalWindow();
                    }
                });
            }
        });
    }
);
