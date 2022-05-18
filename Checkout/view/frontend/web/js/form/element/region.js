define(
    [
        'Magento_Ui/js/form/element/region',
        'uiRegistry'
    ],
    function (
        Component,
        registry
    ) {
        'use strict';

        return Component.extend({
            /**
             * Set default region value
             */
            initialize: function (config) {
                this._super();

                if (window.checkoutConfig.amdefault && window.checkoutConfig.amdefault.region) {
                    registry.get(this.parentName + '.' + 'region_id_input', function (region) {
                        if (!region.value() ) {
                            var country = registry.get(this.parentName + '.' + 'country_id');
                            if (country.value() == window.checkoutConfig.amdefault.country_id) {
                                region.value(window.checkoutConfig.amdefault.region);
                            }
                        }
                    }.bind(this));
                }

                return this;
            }
        });
    }
);
