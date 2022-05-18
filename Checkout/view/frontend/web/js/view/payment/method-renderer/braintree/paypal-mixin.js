define([
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Amasty_Checkout/js/model/events'
], function (_, quote, events) {
    'use strict';

    return function (Component) {
        return Component.extend({
            /**
             * fix braintree paypal behavior for one step checkout
             */
            initObservable: function () {
                var self = this;

                this._super();

                events.onAfterShippingSave(_.debounce(function () {
                    var method = quote.paymentMethod();

                    if (method && method.method === self.code) {
                        self.reInitPayPal();
                    }
                }, 50));

                return this;
            }
        });
    };
});
