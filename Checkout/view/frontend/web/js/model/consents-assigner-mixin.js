/**
 * Mixin for Gdpr assigner to collect checkbox values by position
 */
define([
    'underscore',
    'mage/utils/wrapper',
    './gdpr/consent-processor'
], function (_, wrapper, processor) {
    'use strict';

    return function (agreementsAssignerAction) {
        return wrapper.wrap(agreementsAssignerAction, function (originalAction, paymentData) {
            var consents = window.checkoutConfig.amastyOscGdprConsent || [];

            if (_.isEmpty(consents)) {
                originalAction(paymentData);

                return;
            }

            if (!paymentData['additional_data']) {
                paymentData['additional_data'] = {};
            }

            paymentData['additional_data']['amgdpr_agreement'] = JSON.stringify(processor.getConsentsData());

            return paymentData;
        });
    };
});
