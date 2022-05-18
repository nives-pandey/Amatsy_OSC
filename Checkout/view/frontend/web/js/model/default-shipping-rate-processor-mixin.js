define(
    [
        'mage/utils/wrapper',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/shipping-service',
        'Amasty_Checkout/js/action/get-address-cache-key'
    ],
    function (wrapper, rateRegistry, shippingService, getAddressCacheKey) {
        'use strict';

        /**
         * Modify shippingRegistry guest cache.
         * Reduce quantity of requests to server.
         * @since 3.0.0
         * @since 3.0.5 fixed
         */
        return function (target) {
            target.getRates = wrapper.wrapSuper(target.getRates, function (address) {
                var cacheKey, cache;

                if (address.getType() !== 'new-address' && address.getType() !== 'new-customer-address') {
                    return this._super();
                }

                cacheKey = getAddressCacheKey(address);
                cache = rateRegistry.get(cacheKey);
                if (cache) {
                    rateRegistry.set(address.getCacheKey(), cache);
                } else if (!rateRegistry.get(address.getCacheKey())) {
                    shippingService.getShippingRates().subscribe(function (rates) {
                        rateRegistry.set(cacheKey, rates);
                        this.dispose();
                    });
                }

                return this._super();
            });

            return target;
        };
    }
);

