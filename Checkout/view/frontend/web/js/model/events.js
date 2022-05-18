define([
    'underscore',
    'uiEvents'
], function (_, uiEvents) {
    'use strict';

    // events to use
    const eventBeforeShippingSave = 'before_shipping_save',
        eventAfterShippingSave = 'after_shipping_save';

    /**
     * object undependable event manager
     */
    return _.extend(uiEvents, {
        onBeforeShippingSave: function (callback) {
            this.on(eventBeforeShippingSave, callback);
        },
        onAfterShippingSave: function (callback) {
            this.on(eventAfterShippingSave, callback);
        }
    });
});
