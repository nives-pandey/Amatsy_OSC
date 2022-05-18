define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Amasty_Checkout/js/model/payment-validators/agreement-validator'
    ],
    function (Component, additionalValidators, agreementValidator) {
        'use strict';
        additionalValidators.registerValidator(agreementValidator);
        return Component.extend({});
    }
);
