define(
    [
        'Amasty_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (resourceUrlManager, quote, storage, totalsService, errorProcessor) {
        "use strict";
        return function (checked) {
            var serviceUrl, payload;

            totalsService.isLoading(true);
            serviceUrl = resourceUrlManager.getUrlForGiftWrap(quote);
            payload = {checked: checked};

            storage.post(
                serviceUrl, JSON.stringify(payload), false
            ).done(
                function (result) {
                    quote.setTotals(result);
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            ).always(
                function () {
                    totalsService.isLoading(false);
                }
            );
        }
    }
);
