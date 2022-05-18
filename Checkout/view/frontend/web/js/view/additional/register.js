define([
    'jquery',
    'Magento_Ui/js/form/element/single-checkbox'
], function ($, SingleCheckbox) {
    'use strict';

    return SingleCheckbox.extend({
        defaults: {
            templates: {
                checkbox: 'Amasty_Checkout/form/components/single/checkbox'
            },
            modules: {
                email: 'checkout.steps.shipping-step.shippingAddress.customer-email'
            },
            listens: {
                'visible': 'onVisibilityChange'
            },
            valueMap: {
                'true': true,
                'false': false
            }
        },

        initialize: function () {
            this._super();
            this.email(this.emailObserver.bind(this));
        },
        onUpdate: function (newValue) {
            this._super();
            this.source.trigger('amcheckout.additional:save');
        },
        onVisibilityChange: function (visibility) {
            if (!visibility) {
                this.checked(false);
                this.checked.valueHasMutated();
            } else {
                this.checked(this.initialChecked);
            }
        },
        emailObserver: function (component) {
            this.visible(!component.isPasswordVisible());

            component.isLoading.subscribe(function (isLoading) {
                if (isLoading === true) {
                    $.when(component.isEmailCheckComplete).done(function () {
                        this.visible(true);
                    }.bind(this)).fail(function () {
                        this.visible(false);
                    }.bind(this));
                }
            }.bind(this));
        }
    });
});