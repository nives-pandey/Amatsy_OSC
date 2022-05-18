define([], function () {
    'use strict';

    return function (Component) {
        return Component.extend({
            /**
             * Force show payment methods block
             *
             * @returns {boolean}
             */
            hasShippingMethod: function () {
                return true;
            }
        });
    };
});
