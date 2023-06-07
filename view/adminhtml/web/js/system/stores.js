/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

require([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'domReady!'
], function ($, alert, $t) {
    const fetcher = new Resursbank_FetchStores({
        getClientIdInputElement: function() {
            return document.getElementById(
                'payment_other_resursbank_section_api_client_id_' +
                fetcher.getSelectEnvironmentElement().value
            );
        },

        getClientSecretInputElement: function() {
            return document.getElementById(
                'payment_other_resursbank_section_api_client_secret_' +
                fetcher.getSelectEnvironmentElement().value
            );
        },

        errorHandler: function(message) {
            alert({
                title: 'Resurs Bank',
                content: message
            });

            if (fetcher.fetching) {
                fetcher.toggleFetch(false);
            }
        },

        onComplete: function() {
            alert({
                title: 'Resurs Bank',
                content: $t('rb-please-select-store')
            });
        },

        onToggle: function(state) {
            if (state) {
                $(document).trigger('ajaxSend', [undefined, { showLoader: true }]);
            } else {
                $(document).trigger('ajaxComplete', [undefined, { showLoader: true }]);
            }
        }
    });

    $('#resursbank_fetch_stores_btn').on('click', fetcher.fetchStores.bind(fetcher));
});
