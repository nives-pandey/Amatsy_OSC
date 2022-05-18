define(
    [
        'Amasty_Checkout/js/model/resource-url-manager',
        'Amasty_Checkout/js/model/delivery',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor'
    ],
    function (resourceUrlManager, deliveryService, quote, storage, errorProcessor) {
        "use strict";
        return function (payload) {
            if (deliveryService.isLoading()) {
                return;
            }

            // deliveryService.isLoading(true);
            var serviceUrl = resourceUrlManager.getUrlForDelivery(quote);

            storage.post(
                serviceUrl, JSON.stringify(payload), false
            ).done(
                function (result) {

                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            ).always(
                function () {
                    deliveryService.isLoading(false);
                }
            );
        }
    }
);
