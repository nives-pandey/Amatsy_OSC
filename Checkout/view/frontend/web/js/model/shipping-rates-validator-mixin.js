/*global define*/
define(
    [
        'jquery',
        'mage/utils/wrapper'
    ],
    function (
        $,
        wrapper
    ) {
        'use strict';
        return function (target) {
            var mixin = {
                /**
                 * @return {*}
                 */
                postcodeValidation: function (original) {
                    original();

                    return true;
                },

                /**
                 * Fix validation for billing address
                 *
                 * @param {Function} original
                 * @param {Object} element
                 * @param {Number} delay
                 */
                bindHandler: function (original, element, delay) {
                    if (element.component.indexOf('/group') !== -1
                        || (element.name.indexOf('billing') === -1 && element.dataScope.indexOf('billing') === -1)
                    ) {
                        return original(element, delay);
                    }

                    if (element.index === 'postcode') {
                        var self = this;

                        delay = typeof delay === 'undefined' ? 1000 : delay;

                        element.on('value', function () {
                            clearTimeout(self.validateZipCodeTimeout);
                            self.validateZipCodeTimeout = setTimeout(function () {
                                self.postcodeValidation(element);
                            }, delay);
                        });
                    }
                }
            };

            wrapper._extend(target, mixin);
            return target;
        };
    }
);
