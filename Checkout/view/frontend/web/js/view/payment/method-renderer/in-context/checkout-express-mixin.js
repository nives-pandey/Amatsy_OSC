define([
    'ko',
    'underscore',
    'uiRegistry',
    'rjsResolver',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/method-list'
], function (ko, _, registry, onJsLoad, quote, paymentMethods) {
    'use strict';

    return function (Component) {
        return Component.extend({
            validationTimeout: 0,

            /**
             * fix paypal behavior for one step checkout
             * reactivate button on shipping save
             */
            initListeners: function () {
                this._super();
                onJsLoad(function () {
                    var fields = registry.filter(function (module) {
                        return ko.isObservable(module.required)
                            && module.required.peek() === true
                            && ko.isObservable(module.value)
                            && ko.isObservable(module.visible)
                            && ko.isObservable(module.disabled);
                    });

                    paymentMethods.subscribe(this.amDeferredValidation, this);
                    _.each(fields, function (element) {
                        element.value.subscribe(function (value) {
                            if ((value || value === 0 || value === false)
                                && element.required()
                                && element.visible()
                                && !element.disabled()
                            ) {
                                this.amDeferredValidation();
                            }
                        }.bind(this));
                    }.bind(this));

                }.bind(this));
            },

            validate: function () {
                this._super();
                clearTimeout(this.validationTimeout);
            },

            amDeferredValidation: function () {
                clearTimeout(this.validationTimeout);

                if (quote.paymentMethod() && quote.paymentMethod().method === this.item.method) {
                    this.validationTimeout = setTimeout(function () {
                        window.shippingErrorHideIsNotAllowed = true;
                        this.validate();
                        delete window.shippingErrorHideIsNotAllowed;
                    }.bind(this), 200);
                }
            }
        });
    };
});
