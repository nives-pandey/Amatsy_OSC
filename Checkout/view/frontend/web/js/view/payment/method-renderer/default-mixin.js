/**
 * By default Magento flow, when payment method selected billing address is updates.
 * And when billing address updates, isPlaceOrderActionAllowed also update.
 * But One Step Checkout optimize billing address KO update. @see onepage.replaceEqualityComparer
 * So we need update isPlaceOrderActionAllowed on payment method change, to emulate default flow.
 */
define([
    'underscore',
    'Magento_Checkout/js/model/quote'
], function (_, quote) {
    'use strict';

    return function (Component) {
        return Component.extend({
            initialize: function () {
                this._super();
                this.initPaymentSubscriber();

                return this;
            },
            initPaymentSubscriber: _.once(function () {
                quote.paymentMethod.subscribe(this.updateIsPlaceOrderActionAllowed, this);
            }),
            updateIsPlaceOrderActionAllowed: function () {
                this.isPlaceOrderActionAllowed(quote.billingAddress() != null);
            }
        });
    };
});
