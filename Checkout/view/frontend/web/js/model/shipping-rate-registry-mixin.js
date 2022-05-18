define(['mage/utils/wrapper'], function (wrapper) {
    'use strict';

    /**
     * Add clear registry cache functionality.
     */
    return function (target) {
        return wrapper.extend(target, {
            usedKeys: [],
            set: function (origin, addressKey) {
                if (addressKey && this.usedKeys.indexOf(addressKey) === -1) {
                    this.usedKeys.push(addressKey);
                }

                return origin();
            },
            clearStorage: function () {
                this.usedKeys.forEach(function (key) {
                    this.set(key, null);
                }, this);

                this.usedKeys = [];
            }
        });
    };
});

