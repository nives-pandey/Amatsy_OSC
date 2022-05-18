/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Amasty_Checkout/js/action/save-additional-fields',
    'Magento_Checkout/js/checkout-data'
], function (Component, saveAction) {
    'use strict';

    return Component.extend({
        saveAllowed: false,

        initialize: function () {
            this._super();
            this.source.on('amcheckout.additional:save', this.saveForm.bind(this));
        },

        saveForm: function () {
            saveAction();
        }
    });
});
