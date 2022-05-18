define([
    'jquery',
    'stickyWidget',
    'checkoutCollapsibleSteps'
], function ($) {
    'use strict';

    $.widget('amasty.summaryWidget', {
        options: {
            desktopBreakpoint: 1024,
        },

        _create: function () {
            if ($(this.element).parents('[data-amcheckout-js="main-container"]').length) {
                this.summaryByLayout();
            }
        },

        summaryByLayout: function () {
            switch (window.checkoutLayout) {
                case '1column':
                    $(this.element).checkoutCollapsibleSteps();
                    break;

                case '2columns':
                    this.manageCollapsible();
                    if (!$(this.element).data('amastyStickyWidget')) {
                        $(this.element).stickyWidget({
                            parentContainer: '[data-amcheckout-js="main-container"]',
                            element: '[data-amcheckout-js="sidebar-column"]',
                            scrollContentContainer: '[data-amcheckout-js="main-column"]',
                            stickyOffset: {
                                top: 0,
                                left: 60
                            },
                            stickyPosition: 'right'
                        });
                    }
                    break;

                default:
                    break;
            }
        },

        manageCollapsible: function () {
            if ($(window).width() < this.options.desktopBreakpoint) {
                $(this.element).checkoutCollapsibleSteps();
            } else {
                $('[data-amcheckout-js="step-container"]').on("dimensionsChanged", function () {
                    $(window).trigger('scroll');
                });
            }

            $(window).on('resize', function () {
                if ($(window).width() < this.options.desktopBreakpoint) {
                    $(this.element).checkoutCollapsibleSteps().checkoutCollapsibleSteps('createCollapsible');
                } else {
                    if ($(this.element).data('amastyCheckoutCollapsibleSteps')) {
                        $(this.element).checkoutCollapsibleSteps('destroyCollapsible');
                    }
                }
            }.bind(this));
        }
    });

    return $.amasty.summaryWidget;
});
