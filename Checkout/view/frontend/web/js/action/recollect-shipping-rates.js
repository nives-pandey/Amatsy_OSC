define([
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Amasty_Checkout/js/model/shipping-rate-service-override'
], function (_, quote,  rateService) {
    'use strict';

    /**
     * Clear shipping rates cache and request new rates for current shipping address
     */
    return _.throttle(function () {
        if (!quote.isVirtual()) {
            rateService.updateRates(quote.shippingAddress(), true);
        }
    }, 500);
});
