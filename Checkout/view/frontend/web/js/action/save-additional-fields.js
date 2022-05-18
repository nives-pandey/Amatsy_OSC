define(
    [
        'jquery',
        'Amasty_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'uiRegistry',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor'
    ],
    function ($, resourceUrlManager, quote, registry, storage, errorProcessor) {
        "use strict";
        var request;
        return function () {
            var serviceUrl, payload, data;

            serviceUrl = resourceUrlManager.getUrlForAdditionalFields(quote);

            data = registry.get('checkoutProvider').get('amcheckout.additional');

            payload = {
                cartId: quote.getQuoteId(),
                fields: data
            };
            if (request) {
                request.abort();
            }

            request = storage.post(
                serviceUrl, JSON.stringify(payload), false
            ).fail(
                function (response) {
                    if (response.responseText) {
                        errorProcessor.process(response);
                    }
                }
            );

            return request;
        }
    }
);
