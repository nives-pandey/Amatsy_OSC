define([
    'uiRegistry'
], function (registry) {
    'use strict';

    return {
        /**
         * Validate checkout shipping step
         *
         * @returns {Boolean}
         */
        validate: function (hideError) {
            var shipping = registry.get('checkout.steps.shipping-step.shippingAddress'),
                result;

            shipping.allowedDynamicalSave = false;
            window.silentShippingValidation = !!hideError;
            result = shipping.validateShippingInformation();
            delete window.silentShippingValidation;

            if (hideError && !result && !window.shippingErrorHideIsNotAllowed) {
                if (shipping.isFormInline && shipping.source.get('params.invalid')) {
                    //set current value as initialValue
                    shipping.source.trigger('data.overload');
                    //set initialValue to value and reset errors
                    shipping.source.trigger('data.reset');
                }

                shipping.errorValidationMessage(false);
            }

            shipping.allowedDynamicalSave = true;

            return result;
        }
    };
});
