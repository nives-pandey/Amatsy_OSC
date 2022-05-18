define(
    [
        'Amasty_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Customer/js/customer-data',
        'mage/url'
    ],
    function (
        resourceUrlManager,
        totals,
        quote,
        storage,
        errorProcessor,
        shippingService,
        rateRegistry,
        paymentService,
        methodConverter,
        customerData,
        urlBuilder
    ) {
        "use strict";
        return function (itemId) {
            if (totals.isLoading())
                return;

            totals.isLoading(true);
            shippingService.isLoading(true);
            var serviceUrl = resourceUrlManager.getUrlForRemoveItem(quote);
            var shipppingAddress = quote.shippingAddress();

            //Fix for magento 2.2.2
            if (shipppingAddress.extensionAttributes
                && shipppingAddress.extensionAttributes.checkoutFields
                && Object.keys(shipppingAddress.extensionAttributes.checkoutFields).length === 0
            ) {
                shipppingAddress.extensionAttributes.checkoutFields = [];
            }

            storage.post(
                serviceUrl, JSON.stringify({
                    itemId: itemId,
                    address: shipppingAddress
                }), false
            ).done(
                function (result) {
                    if (!result) {
                        return window.location.reload();
                    }

                    var itemIds = result.totals.items.map(function (value, index) {
                        return value.item_id;
                    });
                    customerData.reload(['cart']);

                    if  (!itemIds.length) {
                        window.location.href = urlBuilder.build("checkout/cart/index");
                        return this;
                    }
                    window.checkoutConfig.quoteItemData = window.checkoutConfig.quoteItemData.filter(function (item) {
                        return itemIds.indexOf(+item.item_id) !== -1;
                    });

                    shippingService.setShippingRates(result.shipping);
                    rateRegistry.set(quote.shippingAddress().getKey(), result.shipping);
                    quote.setTotals(result.totals);

                    paymentService.setPaymentMethods(methodConverter(result.payment));
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                }
            ).always(
                function () {
                    shippingService.isLoading(false);
                    totals.isLoading(false);
                }
            );
        }
    }
);
