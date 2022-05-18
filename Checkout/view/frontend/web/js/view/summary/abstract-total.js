define([], function () {
    'use strict';

    return function (Component) {
        return Component.extend({
            isFullMode: function () {
                return this.getTotals();
            }
        });
    }
});