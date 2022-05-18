define([
    'jquery',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer'
], function ($, storage, quote, urlBuilder, customer) {
    'use strict';

    return {
        canSave: true,
        paymentMethod: null,
        quoteId: quote.getQuoteId(),
        isLoggedIn:customer.isLoggedIn(),
        saveUrl: '',

        initialize: function () {
            quote.paymentMethod.subscribe(function (method) {
                this.paymentMethod = null;
                if (method) {
                    this.paymentMethod = method.method;
                }
            }, this);

            if (this.isLoggedIn) {
                this.saveUrl = urlBuilder.createUrl('/checkout/saveInsertedInfo', {});
            } else {
                this.saveUrl = urlBuilder.createUrl('/checkout/:cartId/saveInsertedInfo', {cartId: this.quoteId});
            }

            $(window).on('beforeunload', this.saveStatistic.bind(this));

            return this;
        },

        saveStatistic: function () {
            var cashStorage = JSON.parse(window.localStorage.getItem('mage-cache-storage')),
                checkoutData,
                shippingAddress = quote.shippingAddress(),
                request = {quote_id: this.quoteId},
                isQuoteActive;

            if (!this.canSave || !cashStorage || !cashStorage.hasOwnProperty('checkout-data') || !this.quoteId) {
                return;
            }

            checkoutData = cashStorage['checkout-data'];
            isQuoteActive = Object.keys(checkoutData).some(function (value) {
                return checkoutData[value] != null;
            });

            if (!isQuoteActive) {
                return;
            }

            if (shippingAddress && shippingAddress.getType() === 'new-customer-address') {
                request.shippingAddressFromData = {
                    'street': shippingAddress.street,
                    'city': shippingAddress.city,
                    'region_id': shippingAddress.regionId,
                    'region': shippingAddress.region,
                    'country_id': shippingAddress.countryId,
                    'postcode': shippingAddress.postcode,
                    'email': shippingAddress.email,
                    'customer_id': shippingAddress.customerId,
                    'firstname': shippingAddress.firstname,
                    'lastname': shippingAddress.lastname,
                    'middlename': shippingAddress.middlename,
                    'prefix': shippingAddress.prefix,
                    'suffix': shippingAddress.suffix,
                    'vat_id': shippingAddress.vatId,
                    'company': shippingAddress.company,
                    'telephone': shippingAddress.telephone,
                    'fax': shippingAddress.fax,
                    'custom_attributes': shippingAddress.customAttributes,
                    'save_in_address_book': shippingAddress.saveInAddressBook
                };
            }

            if (checkoutData.newCustomerBillingAddress) {
                request.newCustomerBillingAddress = checkoutData.newCustomerBillingAddress;
            }

            if (checkoutData.selectedPaymentMethod) {
                if (checkoutData.selectedPaymentMethod.includes('braintree_cc_vault_')) {
                    checkoutData.selectedPaymentMethod = 'braintree_cc_vault';
                }
                request.selectedPaymentMethod = checkoutData.selectedPaymentMethod;
            }

            if (checkoutData.selectedShippingRate) {
                request.selectedShippingRate = checkoutData.selectedShippingRate;
            }

            if (checkoutData.validatedEmailValue) {
                request.validatedEmailValue = checkoutData.validatedEmailValue;
            }

            if (!request.selectedPaymentMethod && this.paymentMethod) {
                request.selectedPaymentMethod = this.paymentMethod;
            }

            storage.post(
                this.saveUrl,
                JSON.stringify(request),
                false
            );
        }
    };
});
