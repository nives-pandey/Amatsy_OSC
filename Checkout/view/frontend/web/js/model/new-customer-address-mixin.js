/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'underscore',
    'mage/utils/wrapper'
], function (_, wrapper) {
    'use strict';

    return function (newAddressFunction) {
        return wrapper.wrap(newAddressFunction, function (origin, addressData) {
            if (window.checkoutConfig.amdefault) {
                _.each(window.checkoutConfig.amdefault, function (defaultValue, name) {
                    if (!addressData[name] && name !== 'region_id' && name !== 'region') {
                        addressData[name] = defaultValue;
                    }
                });
                if (addressData.country_id === window.checkoutConfig.amdefault.country_id) {
                    if (!addressData.region) {
                        addressData.region = {};
                    }
                    if (!addressData.region.region_id) {
                        addressData.region.region_id = window.checkoutConfig.amdefault.region_id;
                    }
                    if (!addressData.region.region) {
                        addressData.region.region = window.checkoutConfig.amdefault.region;
                    }
                }
            }

            return origin(addressData);
        });
    }
});
