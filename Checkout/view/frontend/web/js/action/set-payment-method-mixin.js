define([
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'mage/utils/wrapper',
    'Magento_CheckoutAgreements/js/model/agreements-assigner'
], function (registry, quote, wrapper, agreementsAssigner) {
    'use strict';

    return function (setPaymentMethodAction) {
        return wrapper.wrap(setPaymentMethodAction, function (originalAction, messageContainer) {
            agreementsAssigner(quote.paymentMethod());

            return originalAction(messageContainer);
        });
    };
});
