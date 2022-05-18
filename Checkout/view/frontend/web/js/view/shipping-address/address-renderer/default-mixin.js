define([
    'underscore',
    'uiRegistry'
], function (_) {
    'use strict';

    return function (targetModule) {
        return targetModule.extend({
            getCustomAttributeLabel: function (attribute) {
                if (_.isObject(attribute)
                    && _.isString(attribute.attribute_code)
                    && attribute.attribute_code.indexOf('custom_field_') !== -1
                ) {
                    return this._super(attribute.value);
                }

                return this._super();
            }
        });
    };
});
