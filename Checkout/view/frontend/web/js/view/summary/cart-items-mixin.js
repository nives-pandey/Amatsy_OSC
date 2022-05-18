define([], function () {
    'use strict';

    return function (Component) {
        return Component.extend({
            /**
             * After Magento 2.2.4 items is not expanded by default.
             * return true for expanded by default
             */
            isItemsBlockExpanded: function () {
                return true;
            }
        });
    }
});