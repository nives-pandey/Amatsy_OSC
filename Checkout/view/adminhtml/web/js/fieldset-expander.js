/*global Fieldset*/
define([
    'jquery',
], function ($) {
    'use strict';

    return {
        /**
         * Expand Sections In Configurations by path
         *
         * @param section
         * @param expand
         */
        init: function (section, expand) {
            var pathForExpand = expand.split('/'),
                openContainer = '',
                collapsedState,
                targetSection,
                configStateElement;

            $.each(pathForExpand, function (i, openElement) {
                openContainer += '_' + openElement;

                configStateElement =  $('#' + section + openContainer + '-state');

                if (configStateElement.length !== 0) {
                    collapsedState = configStateElement.val() === '1' ? 0 : 1;
                } else {
                    collapsedState = $('#' + section + openContainer + '-head').attr('collapsed');
                }

                if (collapsedState === 1) {
                    Fieldset.toggleCollapse(section + openContainer);
                }
            });

            targetSection = $('#row_' + section + openContainer);

            if (targetSection.length === 0 ) {
                targetSection = $('#' + section + openContainer + '-head');
            }

            targetSection[0].scrollIntoView();
        }
    }
});
