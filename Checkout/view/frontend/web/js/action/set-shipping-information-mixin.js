define([
    'jquery',
    'mage/utils/wrapper',
    'Amasty_Checkout/js/model/events',
    'Magento_Checkout/js/model/totals'
], function ($, wrapper, events, totals) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (original) {
            events.trigger('before_shipping_save');
            totals.isLoading(true);
            return original().always(function(){
                events.trigger('after_shipping_save');
                totals.isLoading(false);
            });
        });
    };
});
