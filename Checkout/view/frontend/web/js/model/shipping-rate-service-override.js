/**
 * Rate request moved to separate method to be able to request methods without update shipping address.
 */
define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    'Magento_Checkout/js/model/shipping-rate-processor/customer-address',
    'Magento_Checkout/js/model/shipping-rate-registry'
], function (quote, defaultProcessor, customerAddressProcessor, rateRegistry) {
    'use strict';

    var RateService = {
        processors: {},

        /**
         * @private
         */
        _initialize: function () {
            this.processors.default = defaultProcessor;
            this.processors['customer-address'] = customerAddressProcessor;

            quote.shippingAddress.subscribe(this.updateRates, this);
        },

        /**
         * @param {object|null|undefined} address
         * @param {boolean|null|undefined} forceUpdate
         */
        updateRates: function (address, forceUpdate) {
            var shippingAddress = address || quote.shippingAddress(),
                type = shippingAddress.getType();

            if (forceUpdate) {
                rateRegistry.clearStorage();
            }

            if (this.processors[type]) {
                this.processors[type].getRates(shippingAddress);
            } else {
                this.processors.default.getRates(shippingAddress);
            }
        },

        /**
         * @param {String} type
         * @param {*} processor
         */
        registerProcessor: function (type, processor) {
            this.processors[type] = processor;
        }
    };

    RateService._initialize();

    return RateService;
});
