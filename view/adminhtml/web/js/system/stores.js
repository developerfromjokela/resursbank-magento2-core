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
    if (typeof Resursbank_FetchStores !== 'function') {
        return;
    }

    const fetcher = new Resursbank_FetchStores({
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
                content: (typeof resursbankPleaseSelectStoreMsg === 'string') ?
                    resursbankPleaseSelectStoreMsg :
                    'Please select store.'
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
