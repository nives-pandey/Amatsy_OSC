define([
    'ko',
    'underscore',
    'uiRegistry',
    'Magento_Checkout/js/model/quote'
], function (ko, _, registry, quote) {
    'use strict';

    /**
     * States of Billing Address and Shipping Address Forms which can change checkout behaviour.
     * You cant change state of form by changing this observables (one way links)
     */
    return {
        /**
         * is Billing address form in edit mode
         */
        isBillingFormVisible: ko.observable(false),

        /**
         * is Billing Same As Shipping
         */
        isBillingSameAsShipping: ko.observable(true),

        /**
         * is Shipping address form in edit mode
         */
        isShippingFormVisible: ko.observable(false),

        isFormRendered: ko.observable(false).extend({ rateLimit: { timeout: 100, method: 'notifyWhenChangesStop' } }),

        /**
         * Update states by billing form view
         */
        updateBillingFormStates: function () {
            var billingAddressFormComponent,
                billingFormCacheKey = 'billing-address-form',
                paymentMethod;

            if (window.checkoutConfig.displayBillingOnPaymentMethod) {
                paymentMethod = quote.paymentMethod();
                billingFormCacheKey = false;

                if (paymentMethod) {
                    billingFormCacheKey = paymentMethod.method + '-form';
                }
            }

            billingAddressFormComponent = this.getBillingForm(billingFormCacheKey);

            if (billingAddressFormComponent) {
                this.isBillingSameAsShipping(Boolean(billingAddressFormComponent.isAddressSameAsShipping()));
                this.isBillingFormVisible(!billingAddressFormComponent.isAddressDetailsVisible());
            } else {
                this.isBillingSameAsShipping(true);
                this.isBillingFormVisible(false);

                if (!window.checkoutConfig.displayBillingOnPaymentMethod && quote.isVirtual()) {
                    this.isBillingSameAsShipping(false);
                    this.isBillingFormVisible(true);
                }
            }
        },

        _indexedBillingForm: {},

        getBillingForm: function (index) {
            if (index && _.isUndefined(this._indexedBillingForm[index])) {
                this._indexedBillingForm[index] = registry.get({index: index});
            }

            return this._indexedBillingForm[index];
        }
    };
});
