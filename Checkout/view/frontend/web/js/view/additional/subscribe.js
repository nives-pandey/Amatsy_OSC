define([
    'jquery',
    'Magento_Ui/js/form/element/single-checkbox'
], function ($,  SingleCheckbox) {
    'use strict';

    return SingleCheckbox.extend({
        defaults: {
            valueMap: {
                'true': true,
                'false': false
            }
        },
        onUpdate: function (newValue) {
            this._super();
            if (this.initialChecked !== newValue) {
                this.source.trigger('amcheckout.additional:save');
                this.initialChecked = newValue;
            }
        }
    });
});