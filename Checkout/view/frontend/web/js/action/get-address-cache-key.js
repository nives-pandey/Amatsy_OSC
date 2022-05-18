define(
    [
        'underscore',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Magento_Checkout/js/model/address-converter'
    ],
    function (_, validationRules, addressConverter) {
        'use strict';

        /**
         * Return cache string by guest address.
         * Improve guest cache string key generation.
         * Collecting by shipping carriers required fields.
         *
         * @param {Object} address - quote model address
         * @return {String}
         * @since 3.0.0
         */
        function getAddressCacheKey (address) {
            var fields,
                formAddress,
                cacheKey = '',
                postcodeElementName = 'postcode';

            if (address.getType() !== 'new-address' && address.getType() !== 'new-customer-address') {
                return address.getCacheKey();
            }

            fields = validationRules.getObservableFields();

            if (!_.include(fields, postcodeElementName)){
                fields.push(postcodeElementName);
            }

            formAddress = addressConverter.quoteAddressToFormAddressData(address);
            _.each(fields, function (name) {
                if (formAddress.hasOwnProperty(name)) {
                    if (_.isObject(formAddress[name]) || _.isArray(formAddress[name])) {
                        _.each(formAddress[name], function (streetLine) {
                            cacheKey += '|' + streetLine;
                        });
                    } else if (_.isString(formAddress[name])){
                        cacheKey += '|' + formAddress[name];
                    }
                } else if(formAddress['custom_attributes']) {
                    cacheKey += '|' + formAddress['custom_attributes'][name];
                }
            });

            return cacheKey;
        }

        return getAddressCacheKey;
    }
);
