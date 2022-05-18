define([
    'Magento_Ui/js/form/element/date',
    'jquery',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Amasty_Checkout/js/model/payment-validators/dob-validator',
    'Amasty_Checkout/js/view/additional/register',
    'Amasty_Checkout/js/view/checkout/datepicker'
], function (Component, $, paymentValidatorRegistry, dobValidator) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Amasty_Checkout/form/date',
            listens: {
                '${ $.parentName }.register:checked': 'visible'
            },
            modules: {
                registerComponent: '${ $.parentName }.register'
            }
        },

        /**
         * initialize
         */
        initialize: function () {
            this._super();
            paymentValidatorRegistry.registerValidator(dobValidator);
            this.options.maxDate = '-1d';
            this.options.changeMonth = true;
            this.options.changeYear = true;
            this.options.yearRange = "-120y:c+nn";
            this.options.showButtonPanel = true;
            this.options.showOn = 'both';
            this.registerComponent(function (component) {
                this.visible(component.checked() && component.visible());
            }.bind(this))
        },
        onUpdate: function (newValue) {
            this._super();
            if (this.hasChanged()) {
                this.source.trigger('amcheckout.additional:save');
            }
        }
    });
});
