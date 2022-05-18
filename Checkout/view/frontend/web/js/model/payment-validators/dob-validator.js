
define([
    'uiRegistry'
], function (registry) {
    'use strict';

    return {
        /**
         * Validate Date of Birth if available
         *
         * @returns {Boolean}
         */
        validate: function () {
            var amastyDob = registry.get('checkout.sidebar.additional.date_of_birth');

            if (amastyDob && amastyDob.visible()) {
                var validate = amastyDob.validate();
                if (validate == false) {
                    return false;
                }
                return validate.valid;
            }

            return true;
        }
    };
});
