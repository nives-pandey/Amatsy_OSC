/**
 * Billing address view mixin for store flag is billing form in edit mode (visible)
 */
define([
    'Magento_Checkout/js/model/quote',
    'Amasty_Checkout/js/model/address-form-state'
], function (quote, formService) {
    'use strict';

    return function (billingAddress) {
        return billingAddress.extend({
            initialize: function () {
                this._super();

                /**
                 * Vendor Module Fix
                 * Magento EE 2.4.2
                 * Module: Vertex_AddressValidation
                 * File: view/frontend/web/js/billing-validation-mixin.js; method addressDetailsVisibilityChanged
                 * Issue: observable isAddressDetailsVisible could be changed by other components
                 * before registry.get() is executed => this.addressValidator is null =>
                 * => this.addressValidator.message is browser console error
                 */
                if (!this.addressValidator) {
                    this.addressValidator = {
                        message: {
                            hasMessage: function () {
                                return false;
                            }
                        }
                    }
                }

                return this;
            },

            initObservable: function () {
                this._super();

                this.isAddressSameAsShipping.subscribe(formService.updateBillingFormStates, formService);
                this.isAddressDetailsVisible.subscribe(formService.updateBillingFormStates, formService);

                if (window.checkoutConfig.displayBillingOnPaymentMethod) {
                    quote.paymentMethod.subscribe(formService.updateBillingFormStates, formService);
                }

                formService.updateBillingFormStates();
                formService.isFormRendered(true);

                return this;
            }
        });
    };
});
