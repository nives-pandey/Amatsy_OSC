define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Amasty_Checkout/checkout/summary/gift_wrap'
            },

            totals: quote.getTotals(),

            /**
             * Get formatted price
             * @returns {*|String}
             */
            getValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('amasty_checkout').value;
                }
                return this.getFormattedPrice(price);
            },

            isDisplayed: function () {
                return !!(this.totals() && totals.getSegment('amasty_checkout').value !== null);
            }
        });
    }
);
