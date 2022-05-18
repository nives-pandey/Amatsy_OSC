define(
    [
        'knockout',
        'mage/utils/wrapper'
    ],
    function (ko, wrapper) {
        'use strict';

        return function (target) {
            /**
             * Override isVisible to isActive function;
             * because in onestepcheckout all steps should be visible
             */
            target.registerStep = wrapper.wrapSuper(target.registerStep, function (code, alias, title, isVisible, navigate, sortOrder) {
                var isActive = ko.observable(isVisible());
                this._super(code, alias, title, isActive, navigate, sortOrder);
            });

            /**
             * Override for avoid hash in url because in our checkout all doing in one step
             */
            target.setHash = wrapper.wrapSuper(target.setHash, function (hash) {
                hash = '';
                this._super(hash);
            });

            return target;
        };
    }
);

