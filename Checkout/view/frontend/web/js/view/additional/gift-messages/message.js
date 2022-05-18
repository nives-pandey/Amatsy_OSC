define(
    [
        'uiElement',
        'Magento_Ui/js/lib/view/utils/async',
        'Magento_Ui/js/form/form'
    ],
    function (
        uiElement,
        $,
        Component
    ) {
        'use strict';

        var provider = uiElement();

        return Component.extend({
            defaults: {
                template: 'Amasty_Checkout/form/gift_messages/message'
            },

            observables: {},

            initialize: function () {
                this._super();

                this.getObservable('recipient')(this.recipient);
                this.getObservable('sender')(this.sender);
                this.getObservable('message')(this.message);
            },

            getObservable: function (key) {
                this._initObservable(String(this.item_id), key);
                return provider[this.getUniqueKey(String(this.item_id), key)];
            },

            _initObservable: function (node, key) {
                if (node && !this.observables.hasOwnProperty(node)) {
                    this.observables[node] = [];
                }
                if (key && this.observables[node].indexOf(key) == -1) {
                    this.observables[node].push(key);
                    provider.observe(this.getUniqueKey(node, key));
                }
            },

            getUniqueKey: function (node, key) {
                return node + '-' + key;
            },

            getElementId: function (area) {
                var prefix;
                if (this.item_id) {
                    prefix = 'gift-message-item-' + String(this.item_id)
                } else {
                    prefix = 'gift-message-whole'
                }

                return prefix + area;
            },

            collectData: function () {
                return {
                    item_id: this.item_id,
                    recipient: this.getObservable('recipient')(),
                    sender: this.getObservable('sender')(),
                    message: this.getObservable('message')()
                }
            }
        });
    }
);
