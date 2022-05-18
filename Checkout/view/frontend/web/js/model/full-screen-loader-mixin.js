define(
    [
        'mage/utils/wrapper',
        'Amasty_Checkout/js/model/payment/payment-loading'
    ],
    function (wrapper, paymentLoader) {
        'use strict';

        return function (target) {
            /**
             * Override for avoid full screen on shipping dynamic save
             */
            target.startLoader =  wrapper.wrapSuper(target.startLoader, function () {
                if (window.loaderIsNotAllowed) {
                    paymentLoader(true);
                } else {
                    this._super();
                }
            });

            return target;
        };
    }
);

