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
        'Resursbank_Core/js/lib/part-payment',
        'Resursbank_Core/js/model/part-payment',
        'Resursbank_Core/js/remodal'
    ],
    /**
     * @param $
     * @param ko
     * @param $t
     * @param Component
     * @param PriceUtils
     * @param {RbC.Lib.Remodal} RemodalLib
     * @param {RbC.Lib.PartPayment} PartPaymentLib
     * @param {RbC.Model.PartPayment} Model
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
        PartPaymentLib,
        Model,
        Remodal
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Resursbank_Core/remodal',
                methodCode: '',
                modalTitle: '',
                remodalId: '',
                openModal: null
            },

            initialize: function () {
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
                var remodalLoader;

                /**
                 * The content for the "Read more" links.
                 *
                 * @type {jQuery}
                 */
                var remodalContent;

                me._super();

                /**
                 * A flag that states whether the cart's totals has changed. If
                 * they do we need to fetch new data when clicking "Read more".
                 *
                 * @type {boolean}
                 */
                me.totalsHasChanged = false;

                /**
                 * @type {string}
                 */
                me.modalTitle = 'Resurs Bank - ' + $t('Part Payment');

                /**
                 * @type {Simplified.Observable.String}
                 */
                me.modalContent = ko.observable('');

                /**
                 * @type {string}
                 */
                me.remodalLoaderId = me.remodalId + '-loader';

                /**
                 * @type {string}
                 */
                me.remodalContentId = me.remodalId + '-content';

                /**
                 * HTML string with data to be displayed in the "Read more"
                 * modal window.
                 *
                 * @type {Function} ko.observable() - string.
                 */
                me.remodalData = ko.observable(null);

                /**
                 * Initialize the Remodal window.
                 *
                 * NOTE: The modal window will open when initialized.
                 */
                function initRemodal()
                {
                    remodalLoader = RemodalLib.getLoader(me.remodalLoaderId);
                    remodalContent = RemodalLib.getContent(me.remodalContentId);
                    remodalInstance = RemodalLib.getRemodalInstance(me.remodalId)
                        .remodal({
                            hashTracking: false
                        });
                }

                /**
                 * Opens the Remodal window and fetches new data when needed.
                 */
                function openRemodalWindow()
                {
                    remodalInstance.open();

                    if (me.remodalData() === null || me.totalsHasChanged) {
                        me.updateRemodalWindow();
                    }
                }

                /**
                 * Fetches updated part payment information from the server and
                 * updates the remodal content with it.
                 *
                 * @param {number} [price]
                 */
                me.updateRemodalWindow = function (price) {
                    var request;

                    $(remodalContent).hide();
                    $(remodalLoader).show();
                    Model.isFetchingData(true);

                    request = PartPaymentLib.getCostOfPurchase(
                        typeof price === 'number' ? price : Model.finalPrice(),
                        me.methodCode
                    );

                    request.done(function (response) {
                        $(remodalLoader).fadeOut(1000, function () {
                            if (response.hasOwnProperty('html')) {
                                me.remodalData(response.html);
                            } else if (response.hasOwnProperty('error')) {
                                me.remodalData(status.error);
                            } else {
                                me.remodalData($t('An unknown error occurred.'));
                            }

                            me.totalsHasChanged = false;

                            $(remodalContent).fadeIn(1000);
                        });
                    }).fail(function (error) {
                        $(remodalLoader).fadeOut(1000, function () {
                            me.remodalData(error);

                            me.totalsHasChanged = false;

                            $(remodalContent).fadeIn(1000);
                        });
                    }).always(function () {
                        Model.isFetchingData(false);
                    });
                }

                me.openModal.subscribe(function () {
                    if (remodalInstance === null) {
                        initRemodal();
                    }

                    openRemodalWindow();
                });
            }
        });
    }
);
