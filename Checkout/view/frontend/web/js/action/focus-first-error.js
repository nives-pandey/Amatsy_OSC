define(
    ['jquery'],
    function ($) {
        'use strict';

        function toggleErrorCollapsible(errors) {
            $.each(errors, function (index, error) {
                if ($(error).css('display') != 'none'
                    && $(error).parents('[data-amcheckout-js="step-content"]').length) {
                    var stepContainer = $(error).parents('[data-amcheckout-js="step-content"]').parent(),
                        stepContainerIsActive = stepContainer.data('mageCollapsible')
                            ? stepContainer.data('mageCollapsible').options.active
                            : true;

                    if (!stepContainerIsActive) {
                        var animate = stepContainer.collapsible("option", "animate");

                        stepContainer
                            .collapsible('option', 'animate', false)
                            .collapsible('activate')
                            .collapsible('option', 'animate', animate);
                    }
                }
            })
        }

        return function () {
            var checkoutWindow = window;

            if (checkoutWindow.checkoutDesign == 'modern'
                && (checkoutWindow.checkoutLayout == '1column' || checkoutWindow.checkoutLayout == '2columns')) {
                var allErrors = $('.mage-error, .field-error');

                if (allErrors.length) {
                    toggleErrorCollapsible(allErrors);
                }
            }

            var errorField = $('.mage-error:visible, .field-error:visible').first();

            if (!errorField.length) {
                return;
            }

            if (errorField.is('div.mage-error')) {
                if (errorField.prop('for')) {
                    errorField = $('#' + errorField.prop('for')).length
                        ? $('#' + errorField.prop('for'))
                        : $('[name="' + errorField.prop('for') + '"]');
                } else {
                    var input = errorField.prevAll(':input');
                    if (input.length) {
                        errorField = input;
                    }
                }
            } else if (errorField.is('div.field-error')) {
                errorField = $('#' + errorField.prop('id').replace('error-', ''))
            }

            if (errorField.is('input:not(:visible)')) {
                errorField = $('label[for="' + errorField.prop('id') + '"]')
            }

            var offset = errorField.offset().top - (checkoutWindow.innerHeight / 2);

            if (offset < 0) {
                offset = 0;
            }

            $(checkoutWindow).scrollTop(offset);

            errorField.focus();
        };
    }
);

