define(
    [
        'jquery',
        'Amasty_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/error-processor',
        'mage/storage'
    ],
    function (
        $,
        resourceUrlManager,
        quote,
        errorProcessor,
        storage
    ) {
        "use strict";

        return function () {
            var serviceUrl = resourceUrlManager.getUrlForSavePassword(quote),
                password = $('form[data-role=email-with-possible-login]').find('#customer-password'),
                payload = {
                    password: password ? password.val() : ''
            };

            return storage.post(
                serviceUrl,
                JSON.stringify(payload),
                false
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            );
        };
    }
);
