/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

define(
    [
        'jquery',
        'ko',
        'mage/translate',
        'uiLayout',
        'uiComponent',
        'Magento_Catalog/js/price-utils',
        'Resursbank_Core/js/lib/remodal',
        'Resursbank_Core/js/lib/checkout',
        'Resursbank_Core/js/lib/product',
        'Resursbank_Core/js/model/part-payment',
        'Resursbank_Core/js/remodal'
    ],
    /**
     * @param $
     * @param ko
     * @param $t
     * @param Layout
     * @param Component
     * @param PriceUtils
     * @param {RbC.Lib.Remodal} RemodalLib
     * @param {RbC.Lib.Checkout} CheckoutLib
     * @param {RbC.Lib.Product} ProductLib
     * @param {RbPp.Model} Model
     * @param Remodal
     * @returns {*}
     */
    function (
        $,
        ko,
        $t,
        Layout,
        Component,
        PriceUtils,
        RemodalLib,
        CheckoutLib,
        ProductLib,
        Model,
        Remodal
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Resursbank_Core/part-payment',
                linkTitle: '',
                modalTitle: '',
                methodCode: '',
                label: ko.observable(null),
                info: ko.observable(null),
                modalComponent: '',
                requestFn: null
            },

            initialize: function () {
                var me = this;

                me._super();

                /**
                 * @type {string}
                 */
                me.linkTitle = me.linkTitle === '' ?
                    $t('Read More') :
                    me.linkTitle;

                /**
                 * @type {string}
                 */
                me.modalTitle = me.modalTitle === '' ?
                    'Resurs Bank - ' + $t('Part Payment') :
                    me.modalTitle;

                /**
                 * @type {string}
                 */
                me.remodalId = me.remodalId = RemodalLib.createId(
                    me.methodCode
                );

                /**
                 * @type {RbC.Ko.Boolean}
                 */
                me.hasLabel = ko.computed(function () {
                    return typeof me.label() === 'string';
                });

                /**
                 * @type {RbC.Ko.Boolean}
                 */
                me.hasInfo = ko.computed(function () {
                    return typeof me.info() === 'string';
                });

                /**
                 * Whether or not the modal should open. It is passed down to
                 * the modal component, where a subscription will check if it's
                 * updated, and will then open the modal window.
                 */
                me.openModal = ko.observable(false);

                /**
                 * Called when "Read more" links are clicked to initiate the
                 * Remodal modal windows, and later to open the modal windows.
                 *
                 * NOTE: Can't initiate Remodal windows by checking if the
                 * elements has rendered as the Knockout bindings hasn't been
                 * initiated at that point, so the attributes that Remodal is
                 * looking for aren't there yet.
                 *
                 * @param {object} comp - The view model.
                 * @param {Event} event
                 */
                me.bindRemodal = function (comp, event) {
                    // Prevent link from adding a hashtag to the URL as it will
                    // cause Magento to redirect from checkout.
                    event.preventDefault();
                    me.openModal(true);
                };

                Layout([{
                    parent: me.name,
                    name: me.name + '.remodal',
                    displayArea: 'remodal',
                    component: me.modalComponent,
                    config: {
                        modalTitle: me.modalTitle,
                        id: me.remodalId,
                        open: me.openModal,
                        onClose: function () {
                            me.openModal(false);
                        },
                        requestFn: me.requestFn
                    }
                }]);
            }
        });
    }
);
