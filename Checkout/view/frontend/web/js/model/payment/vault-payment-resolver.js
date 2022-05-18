define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Magento_Checkout/js/model/payment/method-list'
],function (_, utils, registry, availableMethods) {
    'use strict';

    return {
        /**
         * Is pyment method are saved valt payment
         *
         * @param {String} method
         * @returns {boolean}
         */
        isSavedVaultPayment: function (method) {
            try {
                if (method
                    && utils.nested(window, 'checkoutConfig.payment.vault.' + method)
                ) {
                    return true;
                }
            } catch (e) {

            }

            return false;
        },

        /**
         * Is pyment method are saved valt payment and available
         *
         * @param {String} method
         * @param {Array} methodlist optional
         * @returns {boolean}
         */
        isVaultMethodAvailable: function (vaultMethod, methodlist) {
            if (!vaultMethod) {
                return false;
            }

            var vaultCode = utils.nested(window, 'checkoutConfig.payment.vault.' + vaultMethod + '.config.code');
            if (!vaultCode) {
                return false;
            }

            if (_.isUndefined(methodlist)) {
                methodlist = availableMethods;
            }
            return !! _.some(methodlist, function (method) {
                return method.method === vaultCode;
            });
        },

        /**
         * Fix Magento bug with preselect saved vault payment
         * @param {String} method
         * @returns {boolean}
         */
        resolve: function (method) {
            if (this.isVaultMethodAvailable(method)) {
                registry.get({index: method}, function (vaultView) {
                    if (_.isFunction(vaultView.selectPaymentMethod)) {
                        vaultView.selectPaymentMethod();
                    }
                });
                return true
            }

            return false;
        }
    };
});
