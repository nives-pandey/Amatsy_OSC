define(
    [
        'jquery',
        'underscore'
    ],
    function ($, _) {
        'use strict';

        var startPlaceOrder = function (selector) {
            var toolBar;

            if (selector) {
                $(selector).click();
            } else {
                toolBar = $('.payment-method._active .actions-toolbar:has(.action.primary)');

                if (toolBar.length > 1) {
                    _.find(toolBar, function (element) {
                        if (element.style.display !== 'none') {
                            toolBar = $(element);

                            return true; // break
                        }

                        return false;
                    });
                }

                toolBar.find('.action.primary').click();
            }
        };

        return function (selector) {
            startPlaceOrder(selector);
        };
    }
);
