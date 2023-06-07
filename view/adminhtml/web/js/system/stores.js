require([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'domReady!'
], function ($, alert, $t) {
    const fetcher = new Resursbank_FetchStores();

    fetcher.getClientIdInputElement = function() {
        return document.getElementById(
            'payment_other_resursbank_section_api_client_id_' +
            fetcher.getSelectEnvironmentElement().value
        );
    };

    fetcher.getClientSecretInputElement = function() {
        return document.getElementById(
            'payment_other_resursbank_section_api_client_secret_' +
            fetcher.getSelectEnvironmentElement().value
        );
    };

    fetcher.handleError = function(message) {
        alert({
            title: 'Resurs Bank',
            content: message
        });

        if (fetcher.fetching) {
            fetcher.toggleFetch(false);
        }
    };

    fetcher.onComplete = function() {
        alert({
            title: 'Resurs Bank',
            content: $t('rb-please-select-store')
        });
    };

    fetcher.onToggle = function(state) {
        // console.log(loader.loaderAjax);
        // console.log(loader.loader);
        if (state) {
            $(document).trigger('ajaxSend', [undefined, { showLoader: true }]);
        } else {
            $(document).trigger('ajaxComplete', [undefined, { showLoader: true }]);
        }
    };

    $('#resursbank_fetch_stores_btn').on('click', fetcher.fetchStores.bind(fetcher));
});
