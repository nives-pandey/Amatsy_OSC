define([
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_Ui/js/modal/confirm'
], function ($, _, $t) {
    'use strict'

    $.widget('mage.alert', $.mage.confirm, {
        options: {
            modalClass: 'confirm',
            title: $.mage.__('Attention'),
            actions: {

                /**
                 * Callback always - called on all actions.
                 */
                always: function () {}
            },
            buttons: [{
                text: $.mage.__('OK'),
                class: 'action-primary action-accept',

                /**
                 * Click handler.
                 */
                click: function () {
                    this.closeModal(true);
                }
            }]
        },

        /**
         * Close modal window.
         */
        closeModal: function () {
            this.options.actions.always();
            this.element.bind('alertclosed', _.bind(this._remove, this));
            var textOrder = $.mage.__('Place Order');
            var placeOrderText = $('button.amasty').find('span');
            placeOrderText.attr('data-bind', "i18n: '" + textOrder + "'");
            placeOrderText.text($t(textOrder));


            return this._super();
        }
    });

    return function (config) {
        return $('<div></div>').html(config.content).alert(config);
    };
});
