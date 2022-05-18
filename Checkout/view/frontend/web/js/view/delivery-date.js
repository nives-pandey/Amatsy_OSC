define(
    [
        'jquery',
        'underscore',
        'uiComponent',
        'Amasty_Checkout/js/view/utils',
        'Amasty_Checkout/js/action/update-delivery',
        'Amasty_Checkout/js/model/delivery',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Amasty_Checkout/js/view/checkout/datepicker'
    ],
    function (
        $,
        _,
        Component,
        viewUtils,
        updateAction,
        deliveryService,
        paymentValidatorRegistry
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Amasty_Checkout/checkout/delivery_date',
                listens: {
                    'update': 'update'
                }
            },
            isLoading: deliveryService.isLoading,
            _requiredFieldSelector: '.amcheckout-delivery-date .field._required :input:not(:button)',

            /**
             * initialize
             */
            initialize: function () {
                this._super();
                var self = this,
                    validator = {
                        validate: self.validate.bind(self)
                    };
                paymentValidatorRegistry.registerValidator(validator);
            },

            update: function () {
                if (this.validate()) {
                    var data = this.source.get('amcheckoutDelivery');

                    updateAction(data);
                }
            },

            validate: function () {
                this.source.set('params.invalid', false);
                this.source.trigger('amcheckoutDelivery.data.validate');

                if (this.source.get('params.invalid')) {
                    return false;
                }

                var validationResult = true;
                this.elems().forEach(function (item) {
                    if (item.validate().valid == false) {
                        validationResult = false;
                        return false;
                    }
                });
                return validationResult;
            },

            getDeliveryDateName: function () {
                return viewUtils.getBlockTitle('delivery');
            }
        });
    }
);
