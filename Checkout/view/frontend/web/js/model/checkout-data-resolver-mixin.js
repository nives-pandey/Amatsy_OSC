define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/action/select-shipping-address',
        'Amasty_Checkout/js/model/payment/vault-payment-resolver',
        'uiRegistry',
        'underscore',
        'mage/utils/wrapper'
    ],
    function (
        $,
        quote,
        checkoutData,
        selectShippingMethodAction,
        addressConverter,
        addressList,
        paymentService,
        selectPaymentMethodAction,
        selectShippingAddress,
        vaultResolver,
        registry,
        _,
        wrapper
    ) {
        'use strict';

        var defaultShippingMethod = null;

        /**
         * Get default shipping method.
         * Store default shipping method to speedup check.
         *
         * @return {String|false}
         * @private
         */
        function _getDefaultShippingMethod () {
            var provider;

            if (defaultShippingMethod === null) {
                provider = registry.get('checkoutProvider');

                if (provider && provider.defaultShippingMethod) {
                    defaultShippingMethod = provider.defaultShippingMethod;
                } else {
                    defaultShippingMethod = false;
                }
            }

            return defaultShippingMethod;
        }

        return function (target) {
            var mixin = {
                /**
                 * @param {Function} original
                 * @param {Object} ratesData
                 */
                resolveShippingRates: function (original, ratesData) {
                    if (!ratesData || ratesData.length === 0) {
                        selectShippingMethodAction(null);

                        return;
                    }

                    if (ratesData.length === 1) {
                        //set shipping rate if we have only one available shipping rate
                        selectShippingMethodAction(ratesData[0]);

                        return;
                    }
                    var selectedShippingRate = checkoutData.getSelectedShippingRate(),
                        availableRate = false;

                    if (quote.shippingMethod()) {
                        availableRate = _.find(ratesData, function (rate) {
                            return rate['carrier_code'] == quote.shippingMethod()['carrier_code'] && //eslint-disable-line
                                rate['method_code'] == quote.shippingMethod()['method_code']; //eslint-disable-line eqeqeq
                        });
                    }

                    if (!availableRate && selectedShippingRate) {
                        availableRate = _.find(ratesData, function (rate) {
                            return rate['carrier_code'] + '_' + rate['method_code'] === selectedShippingRate;
                        });
                    }

                    if (!availableRate && window.checkoutConfig.selectedShippingMethod) {
                        availableRate = _.find(ratesData, function (rate) {
                            return rate['carrier_code'] + '_' + rate['method_code'] === window.checkoutConfig.selectedShippingMethod;
                        });
                    }

                    if (!availableRate && _getDefaultShippingMethod()) {
                        availableRate = _.find(ratesData, function (rate) {
                            return rate['carrier_code'] + '_' + rate['method_code'] === _getDefaultShippingMethod();
                        });
                        if (!availableRate) {
                            availableRate = _.find(ratesData, function (rate) {
                                return !!rate.available;
                            });
                        }
                    }

                    if (availableRate) {
                        selectShippingMethodAction(availableRate);
                    } else {
                        selectShippingMethodAction(null);
                    }
                },

                /**
                 * Resolve payment method. Used local storage
                 * @param {Function} original
                 */
                resolvePaymentMethod: function (original) {
                    original();
                    if (quote.paymentMethod()) {
                        return;
                    }
                    var paymentMethod = checkoutData.getSelectedPaymentMethod();
                    if (vaultResolver.isSavedVaultPayment(paymentMethod) && vaultResolver.resolve(paymentMethod)) {
                        return;
                    }
                    var provider = registry.get('checkoutProvider');
                    if (provider && provider.defaultPaymentMethod) {
                        var availablePaymentMethods = paymentService.getAvailablePaymentMethods();
                        availablePaymentMethods.some(function (payment) {
                            if (payment.method === provider.defaultPaymentMethod) {
                                selectPaymentMethodAction(payment);
                                return true;
                            }
                        });
                    }
                },

                /**
                 * Resolve estimation address. Used local storage
                 * @param {Function} original
                 */
                resolveEstimationAddress: function (original) {
                    original();
                    var shippingAddressData = checkoutData.getShippingAddressFromData(),
                        checkoutProvider = registry.get('checkoutProvider');

                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                },

                /**
                 * Apply resolved estimated address to quote
                 *
                 * @param {Function} original
                 * @param {Object} isEstimatedAddress
                 */
                applyShippingAddress: function (original, isEstimatedAddress) {
                    var addressData = addressList()[0];

                    original();

                    if (quote.shippingAddress()) {
                        return;
                    }

                    if (isEstimatedAddress) {
                        addressData = addressConverter.addressToEstimationAddress(addressData);
                    }

                    if (addressList().length > 1) {
                        selectShippingAddress(addressData);
                    }
                }
            };

            wrapper._extend(target, mixin);
            return target;
        };
    }
);