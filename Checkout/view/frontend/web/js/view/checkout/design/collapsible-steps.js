define([
    'jquery',
    'mage/collapsible',
    'Magento_Ui/js/modal/modal'
], function ($) {
    'use strict';

    $.widget('amasty.checkoutCollapsibleSteps', {
        _create: function () {
            this.createCollapsible();
        },

        initCollapsible: function () {
            $(this.element).parent().collapsible({
                header: '[data-amcheckout-js="step-title"]',
                content: '[data-amcheckout-js="step-content"]',
                active: true,
                openedState: '-collapsible -opened',
                closedState: '-collapsible -closed',
                icons: {
                    header: 'amcheckout-icon -plus',
                    activeHeader: 'amcheckout-icon -minus'
                },
                animate: 300
            });
        },

        createCollapsible: function () {
            var checkoutWindow = window;

            if (!$(this.element).parent().data('mageCollapsible')
                && checkoutWindow.checkoutDesign == 'modern'
                && (checkoutWindow.checkoutLayout == '1column' || checkoutWindow.checkoutLayout == '2columns')) {
                this.initCollapsible();
            }
        },

        destroyCollapsible: function () {
            var collapsibleElement = $(this.element).parent();

            if (collapsibleElement.data('mageCollapsible')) {
                collapsibleElement.collapsible("forceActivate").collapsible("destroy");
            }
        }
    });

    return $.amasty.checkoutCollapsibleSteps;
});
