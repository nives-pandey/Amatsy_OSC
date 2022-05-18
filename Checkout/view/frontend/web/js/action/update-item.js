define(
    [
        'Amasty_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Amasty_Checkout/js/action/recollect-shipping-rates',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Customer/js/customer-data',
        'Amasty_Checkout/js/action/update-items-content',
    ],
    function (
        resourceUrlManager,
        totals,
        quote,
        storage,
        errorProcessor,
        recollectShippingRates,
        paymentService,
        methodConverter,
        customerData,
        updateItemsContent
    ) {
        "use strict";


        return function (itemId, formData) {
            if (totals.isLoading()) {
                return;
            }

            totals.isLoading(true);

            storage.post(
                resourceUrlManager.getUrlForUpdateItem(quote),
                JSON.stringify({
                    itemId: itemId,
                    formData: formData
                }), false
            ).done(
                function (result) {
                    if (!result) {
                        window.location.reload();
                    }

                    recollectShippingRates();

                    paymentService.setPaymentMethods(methodConverter(result.payment));
                    customerData.reload(['cart']);
                    totals.isLoading(false);
                    updateItemsContent(result.totals);
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    totals.isLoading(false);
                }
            );
        };
    }
);
