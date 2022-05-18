define(
    [
        'Magento_Ui/js/form/element/single-checkbox'
    ],
    function (
        Component
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                templates: {
                    checkbox: 'Amasty_Checkout/form/gift_messages/checkbox'
                }
            },
            showPopupHandler: function (component, event) {
                event.stopPropagation();

                this.trigger('edit_link_click');
            }
        });
    }
);
