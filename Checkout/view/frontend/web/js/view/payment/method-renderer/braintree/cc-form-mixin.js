// upgrade Braintree flow for One Step Checkout
define([
], function () {
    'use strict';

    return function (Component) {
        return Component.extend({
            /**
             * Brantree harcoded disallow order place on initialization.
             * By default Magento flow, payment method cannot be preselected.
             * Dnd when payment method selected billing address is updates.
             * And when billing address updates, isPlaceOrderActionAllowed also update.
             * But One Step Checkout optimize billing address KO update.
             */
            initFormValidationEvents: function () {
                this._super();
                this.updateIsPlaceOrderActionAllowed();
            }
        });
    };
});
