/**
 * Processor for collect consent checkboxes data by each checkout block
 */
define([
    'mage/utils/wrapper',
    'jquery',
    'underscore'
], function (wrapper, $, _) {
    'use strict';

    return {

        /**
         * @return {Object}
         */
        getConsentsData: function () {
            var consents = window.checkoutConfig.amastyOscGdprConsent || [],
                consentsData = {},
                consentData,
                consentElements,
                checked;

            _.each(consents, function (consentByBlock, blockCode) {
                _.each(consentByBlock.consents, function (consent) {
                    consentElements = $('input[data-gdpr-checkbox-code="' + consent.checkbox_code + '"]:visible');

                    _.each(consentElements, function (consentElement) {
                        consentData = consentsData[consent.checkbox_code];
                        checked = Boolean($(consentElement).prop('checked'));

                        if (!consentData || !consentData.checked) {
                            consentsData[consent.checkbox_code] = {
                                checked: checked,
                                from: blockCode
                            };
                        }
                    });
                });
            });

            return consentsData;
        }
    };
});
