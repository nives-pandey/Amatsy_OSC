define([
    'Magento_Ui/js/lib/view/utils/async',
    'uiRegistry',
    'ko'
], function ($, registry, ko) {
    'use strict';

    return {
        isReady: ko.observable(false),

        geolocate: function (autocomplete) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var geolocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    var circle = new google.maps.Circle({
                        center: geolocation,
                        radius: position.coords.accuracy
                    });
                    autocomplete.setBounds(circle.getBounds());
                });
            }
        },

        registerField: function (component) {
            var self = this;
            if (this.isReady()) {
                return this.init(component);
            } else {
                this.isReady.subscribe(function (isReady) {
                    if (isReady) {
                        return self.init(component);
                    }
                });
            }
        },

        init: function (component) {
            var self = this;

            registry.get(component, function (rootComponent) {
                registry.get(component + '.street.0', function (inputComponent) {
                    $.async({
                        selector: '#' + inputComponent.uid
                    }, function (input) {
                        var autocomplete = new google.maps.places.Autocomplete(
                            input,
                            {types: ['geocode']}
                        );

                        autocomplete.setFields(['address_components', 'name']);

                        autocomplete.addListener('place_changed', function () {
                            self.fillInAddress(autocomplete, rootComponent);
                        });

                        self.geolocate(autocomplete);
                    });
                });
				
				registry.get(component + '.postcode', function (inputComponent) {
                    $.async({
                        selector: '#' + inputComponent.uid
                    }, function (input) {
                        var autocomplete = new google.maps.places.Autocomplete(
                            input,
                            {types: ['geocode']}
                        );

                        autocomplete.setFields(['address_components', 'name']);

                        autocomplete.addListener('place_changed', function () {
                            self.fillInAddress(autocomplete, rootComponent);
                        });

                        self.geolocate(autocomplete);
                    });
                });
            });
        },

        fillInAddress: function (autocomplete, rootComponent) {
            var place = autocomplete.getPlace();

            if (!place.address_components) {
                return;
            }

            var streetComponent = rootComponent.getChild('street').getChild(0);
            var street = place.name.replace(',', '');

            if (street && (streetComponent.value() === street)) {
                streetComponent.value.valueHasMutated();
            } else {
                streetComponent.value(street);
            }
            if (rootComponent.hasChild('postcode')) {
                rootComponent.getChild('postcode').value('');
            }
            if (rootComponent.hasChild('region_id_input')) {
                rootComponent.getChild('region_id_input').value('');
            }
            if (rootComponent.hasChild('city')) {
                rootComponent.getChild('city').value('');
            }

            var isRegionApplied = false, postcode = false, postcode_suffix = false, stateSelect;

            for (var i = place.address_components.length - 1; i >= 0; i--) {
                var addressComponent = place.address_components[i];
                var addressType = addressComponent.types[0];
				
                switch (addressType) {
                    case 'country':
                        if (rootComponent.hasChild('country_id')) {
                            rootComponent.getChild('country_id').value(addressComponent.short_name);
                        }
                        break;
                    case 'locality':
                    case 'postal_town':
                        if (rootComponent.hasChild('city')) {
                            rootComponent.getChild('city').value(addressComponent.long_name);
                        }
                        break;
					case 'sublocality_level_1':
					case 'sublocality_level_3':
                        if (rootComponent.getChild('street').getChild(1)) {
                            rootComponent.getChild('street').getChild(1).value(addressComponent.long_name);
                        }
                        break;
					case 'sublocality_level_2':
					    if (rootComponent.getChild('street').getChild(2)) {
                            rootComponent.getChild('street').getChild(2).value(addressComponent.long_name);
                        }
                        break;
                    case 'postal_code':
                        if (rootComponent.hasChild('postcode')) {
                            postcode = addressComponent.long_name;
                            if (postcode_suffix) {
                                postcode = postcode + '-' + postcode_suffix;
                            }
                            rootComponent.getChild('postcode').value(postcode);
                        }
                        break;
                    case 'postal_code_suffix':
                        postcode_suffix = addressComponent.long_name;
                        break;
                    case 'administrative_area_level_1':
                        if (isRegionApplied) {
                            break;
                        }

                        stateSelect = rootComponent.getChild('region_id');
                        if (stateSelect && stateSelect.visible()) {
                            var value = addressComponent.short_name;

                            var country = checkoutConfig.defaultCountryId;
                            if (rootComponent.hasChild('country_id')) {
                                country = rootComponent.getChild('country_id').value();
                            }
                            if (country in window.amasty_checkout_regions && value in window.amasty_checkout_regions[country]) {
                                stateSelect.value(window.amasty_checkout_regions[country][value]);
                            }
                        } else if (rootComponent.hasChild('region_id_input')) {
                            rootComponent.getChild('region_id_input').value(addressComponent.long_name);
                        }

                        isRegionApplied = true;
                        break;
                    case 'administrative_area_level_2':
                        if (isRegionApplied) {
                            var stateInput = rootComponent.getChild('region_id_input');
                            if (stateInput && stateInput.visible() && stateInput.value()) {
                                stateInput.value(stateInput.value() + ', ' + addressComponent.long_name);
                            }
                        } else {
                            stateSelect = rootComponent.getChild('region_id');
                            if (stateSelect && stateSelect.visible()) {
                                var value = addressComponent.short_name;

                                var country = checkoutConfig.defaultCountryId;
                                if (rootComponent.hasChild('country_id')) {
                                    country = rootComponent.getChild('country_id').value();
                                }
                                if (country in window.amasty_checkout_regions && value in window.amasty_checkout_regions[country]) {
                                    stateSelect.value(window.amasty_checkout_regions[country][value]);
                                }
                            }
                        }
                        break;
                }
            }
        }
    };
});
