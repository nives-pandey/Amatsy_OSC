/**
 * Checkout cart items view.
 */
define([
    'ko',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/view/summary/cart-items'
], function (ko, totals, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_Checkout/checkout/summary/cart-items'
        },

        itemsQty: ko.observable(),

        initialize: function () {
            this._super();

            this.itemsQty(this.getItemsQty());
            totals.totals.subscribe(function () {
                this.itemsQty(this.getItemsQty());
            }, this);

            return this;
        },

        getItemsQty: function () {
            return parseFloat(totals.totals().items_qty);
        }
    });
});
