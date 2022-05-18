define(
    [
        'mage/utils/wrapper',
        'Amasty_Checkout/js/action/focus-first-error',
        'Amasty_Checkout/js/model/payment-validators/login-form-validator'
    ],
    function (wrapper, focusFirstError, loginFormValidator) {
        'use strict';

        return function (target) {
            /**
             * Focus first error after validation
             */
            target.validate = wrapper.wrapSuper(target.validate, function (hideError) {
                var result;

                if (!loginFormValidator.validate()) {
                    if (!hideError) {
                        focusFirstError();
                    }

                    return false;
                }

                result = this._super();

                if (!result && !hideError) {
                    focusFirstError();
                }

                return result;
            });

            return target;
        };
    }
);
