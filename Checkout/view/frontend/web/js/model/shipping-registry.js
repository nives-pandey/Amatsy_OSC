define([
    'ko',
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Customer/js/model/address-list',
    'Amasty_Checkout/js/model/address-form-state',
    'Amasty_Checkout/js/action/get-address-cache-key',
    'Amasty_Checkout/js/model/payment/payment-loading',
    'Magento_Ui/js/lib/validation/validator'
], function (
    ko,
    _,
    quote,
    validationRules,
    rateRegistry,
    formPopUpState,
    addressConverter,
    addressList,
    addressFormState,
    getAddressCacheKey,
    paymentLoader,
    validator
) {
    'use strict';

    return {
        savedAddress: '',
        shippingMethod: '',
        shippingCarrier: '',

        /**
         * list of shipping address
         * it also can contains third party extension fields
         */
        addressComponents: [],

        /**
         * filtered addressComponents
         *  modules that are not used in shipping estimation (not in observable)
         */
        observedComponents: [],

        /**
         * Saved values of observable address.
         * Update on shipping address save
         */
        additionalAddressValues: {},

        /**
         * @api
         * Excluded components by name (index)
         * Additional excludes should be added before initObservers executed
         */
        excludedFieldsNames: [],

        /**
         * @api
         * Excluded collection components by name (index)
         * Additional excludes should be added before initObservers executed
         */
        excludedCollectionNames: ['billing-address-form', 'customer-email'],
        isEstimationHaveError: ko.observable(false),
        isAddressChanged: ko.observable(false).extend({ notify: 'always', rateLimit: 20 }),
        validationTimeout: 0,
        checkDelay: 1000,

        /**
         * Register additional shipping fields observers
         * @param {Function} elems - observable array of shipping elements
         * @returns {void}
         */
        initObservers: function (elems) {
            if (_.isEmpty(this.addressComponents)) {
                this.excludedFieldsNames = _.union(this.excludedFieldsNames, validationRules.getObservableFields());
                this.filterElements(elems());

                _.each(this.addressComponents, function (element) {
                    if (this.excludedFieldsNames.indexOf(element.index) === -1) {
                        this.observedComponents.push(element);
                        this.additionalAddressValues[element.index] = this.getInitialValue(element.index);
                    }

                    element.on('value', this.triggerValidation.bind(this, element));
                }, this);
            }
        },

        /**
         * Set saved values on page load (saved on server)
         * @returns {void}
         */
        setInitialValues: function () {
            var savedAddress = window.checkoutConfig.shippingAddressFromData,
                savedMethod = window.checkoutConfig.selectedShippingMethod,
                subscriber,
                cacheKey;

            if (savedAddress) {
                this.savedAddress = addressConverter.formAddressDataToQuoteAddress(savedAddress);
                cacheKey = getAddressCacheKey(this.savedAddress);
                rateRegistry.set(cacheKey, window.checkoutConfig.quoteData.initRates);
            } else if (window.checkoutConfig.selectedShippingAddressId) {
                if (addressList.getLength()) {
                    this._setCustomerAddress(addressList());
                } else {
                    subscriber = addressList.subscribe(function (addreses) {
                        this._setCustomerAddress(addreses);
                        subscriber.dispose();
                    }, this);
                }
            }

            if (savedMethod) {
                if (_.isObject(savedMethod)) {
                    this.shippingMethod = savedMethod.method_code;
                    this.shippingCarrier = savedMethod.carrier_code;
                } else if (_.isString(savedMethod)) {
                    savedMethod = savedMethod.split('_');
                    this.shippingMethod = savedMethod[0];
                    this.shippingCarrier = savedMethod[1];
                }
            }
        },

        /**
         * Set shipping rates and saved address by customer address
         * @param {Array} addresses
         * @private
         * @returns {void}
         */
        _setCustomerAddress: function (addresses) {
            var selectedAddress;

            selectedAddress = _.find(addresses, function (address) {
                return address.customerAddressId * 1 === window.checkoutConfig.selectedShippingAddressId * 1;
            });

            if (selectedAddress) {
                this.savedAddress = selectedAddress;
                rateRegistry.set(selectedAddress.getKey(), window.checkoutConfig.quoteData.initRates);
            }
        },

        /**
         * Get initial value for additional shipping fields.
         * @param {String} index
         * @return {null|*}
         */
        getInitialValue: function (index) {
            var savedAddress = window.checkoutConfig.shippingAddressFromData;

            if (savedAddress) {
                if (savedAddress.custom_attributes
                    && savedAddress.custom_attributes[index]
                ) {
                    return savedAddress.custom_attributes[index];
                }

                if (savedAddress[index]) {
                    return savedAddress[index];
                }
            }

            return null;
        },

        /**
         * Extract all fields wich can be observable from fields
         * @param {Array} elems
         * @returns {void}
         */
        filterElements: function (elems) {
            if (!elems || !elems.length) {
                return;
            }

            _.each(elems, function (element) {
                if (this._isCollection(element)) {
                    try {
                        if (this._isCollectionValid(element)) {
                            this.filterElements(element.elems());
                        }
                    } catch (e) {
                        // continue
                    }

                    return;// continue
                }

                if (this._isModuleValid(element)) {
                    this.addressComponents.push(element);
                }
            }.bind(this));
        },

        /**
         * Is component are collection
         *
         * @param {Object} element
         * @returns {Boolean}
         * @private
         */
        _isCollection: function (element) {
            return typeof element.initChildCount === 'number';
        },

        /**
         * Is component collection is valid
         *
         * @param {Object} element
         * @returns {Boolean}
         * @private
         */
        _isCollectionValid: function (element) {
            return this.excludedCollectionNames.indexOf(element.index) === -1;
        },

        /**
         * Is component can be observable
         *
         * @param {Object} module
         * @returns {Boolean}
         * @private
         */
        _isModuleValid: function (module) {
            return ko.isObservable(module.error)
                && ko.isObservable(module.value);
        },

        /**
         * Debounce validation
         * @returns {void}
         */
        triggerValidation: function () {
            clearTimeout(this.validationTimeout);

            if (!formPopUpState.isVisible() && !addressFormState.isShippingFormVisible()) {
                paymentLoader(true);

                this.validationTimeout = setTimeout(this.validation.bind(this), this.checkDelay);
            }
        },

        validation: function () {
            var isError = false,
                valueChanged = false,
                result;

            _.find(this.observedComponents, function (element) {
                if (element.visible && !element.visible() || element.disabled && element.disabled()) {
                    return false;// continue
                }

                if (element.error()) {
                    isError = true;

                    return true;// break
                }

                if (_.isObject(element.validation)) {
                    result = validator(element.validation, element.value(), element.validationParams);

                    if (!result.passed) {
                        isError = true;

                        return true;// break
                    }
                }

                if (this.additionalAddressValues[element.index] !== element.value()) {
                    valueChanged = true;
                }

                return false;
            }, this);

            this.isEstimationHaveError(isError);

            if (!isError) {
                this.isAddressChanged(valueChanged);
            } else {
                paymentLoader(false);
            }
        },

        /**
         * Store saved values for tracking changes
         * @returns {void}
         */
        registerAdditionAddressValues: function () {
            clearTimeout(this.validationTimeout);
            _.each(this.observedComponents, function (element) {
                this.additionalAddressValues[element.index] = element.value();
            }.bind(this));
        },

        /**
         * Set saved data
         * @param {object} address
         * @returns {void}
         */
        register: function (address) {
            if (!address) {
                address = quote.shippingAddress();
            }

            this.savedAddress = address;
            this.shippingMethod = quote.shippingMethod().method_code;
            this.shippingCarrier = quote.shippingMethod().carrier_code;
            this.registerAdditionAddressValues();
        },

        /**
         * Compare current Shipping Data with saved and determines is need to save it
         *
         * @returns {boolean}
         */
        isHaveUnsavedShipping: function () {
            var methodData = quote.shippingMethod();

            if (!methodData) {
                return false;
            }

            if (!this.savedAddress) {
                return true;
            }

            return this.isAddressChanged()
                || !this._compareObjectsData(quote.shippingAddress(), this.savedAddress)
                || this.shippingMethod !== methodData.method_code
                || this.shippingCarrier !== methodData.carrier_code;
        },

        /**
         * Is address objects are equal
         *
         * @param {*} quoteAddress
         * @param {*} savedAddress
         * @returns {boolean}
         * @private
         * @since 2.0.0
         * @since 3.0.0 - empty values removed from comparable objects, customer address compare optimization
         */
        _compareObjectsData: function (quoteAddress, savedAddress) {
            if (quoteAddress.getType && quoteAddress.getType() === 'customer-address') {
                // customer address is not editable, compare only ids.
                return quoteAddress.customerAddressId === savedAddress.customerAddressId;
            }

            quoteAddress = _.pick(quoteAddress, this._cleanupAddressFunction);
            savedAddress = _.pick(savedAddress, this._cleanupAddressFunction);

            if (quoteAddress.regionId) {
                quoteAddress.regionId *= 1;
            }

            if (savedAddress.regionId) {
                savedAddress.regionId *= 1;
            }

            // objects for isEqual should not contain functions. Same for objects of their objects.
            return _.isEqual(quoteAddress, savedAddress);
        },

        /**
         * Iterate function.
         * Remove functions and empty values.
         * Leave only acceptable array cells for comparing.
         *
         * @param {*} value
         * @param {string} key
         * @return {boolean}
         * @private
         */
        _cleanupAddressFunction: function (value, key) {
            return !_.isFunction(value)
                && key !== 'save_in_address_book'
                && value
                && (!_.isEmpty(value) || _.isNumber(value));// isEmpty - returns true for any number type (int/float)
        }
    };
});
