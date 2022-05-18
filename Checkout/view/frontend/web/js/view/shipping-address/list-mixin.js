/**
 * Change shipping address list view.
 */
define([
    'ko',
    'underscore',
    'Amasty_Checkout/js/model/address-form-state',
    'Amasty_Checkout/js/model/shipping-registry',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/checkout-data',
    'mage/translate',
    'jquery'
], function (
    ko,
    _,
    addressFormState,
    shippingRegistry,
    addressList,
    quote,
    formPopUpState,
    selectShippingAddress,
    checkoutData,
    $t
) {
    'use strict';

    var newAddressOption = {
        /**
         * Get new address label
         * @returns {String}
         */
        getAddressInline: function () {
            return $t('New Address');
        },
        customerAddressId: null
    };

    function isAddressNew(address) {
        return address && (address === newAddressOption || address.getType() === 'new-customer-address');
    }

    return function (Component) {
        return Component.extend({
            defaults: {
                dropdownTemplate: 'Amasty_Checkout/shipping-address/shipping-address',
                modules: {
                    shippingInformationComponent: 'index = ship-to',
                    shippingAddressComponent: '${ $.parentName }'
                }
            },

            initialize: function () {
                _.bindAll(
                    this,
                    'addressOptionsText',
                    'changeAddress',
                    'onAddressChange',
                    'updateAddress',
                    'cancelAddressEdit'
                );

                this._super();

                if (this.getVisible()) {
                    window.isShippingAddressFormOverridden(true);
                    this.shippingAddressComponent(this.overrideNewShippingAddress);
                    shippingRegistry.excludedCollectionNames.push('shipping-address-fieldset');
                }

                return this;
            },

            initObservable: function () {
                this._super()
                    .observe({
                        /**
                         * Current dropdown value
                         */
                        selectedAddress: quote.shippingAddress(),

                        /**
                         * On true shows current shipping address information as plain text
                         */
                        isAddressDetailsVisible: quote.shippingAddress() != null,

                        /**
                         * On true shows new address form if isAddressListVisible is also true
                         */
                        isAddressFormVisible: false,

                        /**
                         * On true shows dropdown and new address form
                         */
                        isAddressListVisible: !quote.shippingAddress(),
                    });

                this.initSubscribers();

                return this;
            },

            /**
             * Set subscribers to observables
             */
            initSubscribers: function () {
                if (this.getVisible()) {
                    quote.shippingAddress.subscribe(this.onShippingAddressUpdate, this);
                    this.selectedAddress.subscribe(this.onAddressChange, this);
                    this.isAddressFormVisible.subscribe(this.updatePopupState, this);
                    this.isAddressListVisible.subscribe(this.isShippingFormVisibleUpdate, this);
                }
            },

            /**
             * Modify new shipping address for registered customer functionality.
             *  Old behavior - popup; new - inline form
             *
             * @param {object} shippingComponent
             */
            overrideNewShippingAddress: function (shippingComponent) {
                //override popup functionality
                shippingComponent.getPopUp = shippingComponent.getPopUpOverride;
            },

            /**
             * Override for better compatibility.
             * Magento_NegotiableQuote compatibility.
             *
             * @return {string}
             */
            getTemplate: function () {
                this._super();

                return this.dropdownTemplate;
            },

            /**
             * Override to prevent not used functionality render (performance save)
             */
            initChildren: function () {
                return this;
            },

            /**
             * Override to prevent not used functionality render (performance save).
             * This list uses ship-to component instead origin
             */
            createRendererComponent: function () {},

            /**
             * Visible by default is usual variable,
             * but it can be changed to observable or computed by vendors (amazon)
             *
             * @return {boolean}
             */
            getVisible: function () {
                return ko.utils.unwrapObservable(this.visible);
            },

            /**
             * Subscriber
             * updates new shipping address core state
             * required to suppress address update before save
             */
            updatePopupState: function () {
                if (this.getVisible()) {
                    formPopUpState.isVisible(this.isAddressListVisible() && this.isAddressFormVisible());
                }
            },

            /**
             * Shipping address subscriber
             * Close form on shipping address change
             *
             * @param {object} newAddress
             */
            onShippingAddressUpdate: function (newAddress) {
                if (newAddress != null) {
                    this.isAddressDetailsVisible(true);
                    this.isAddressListVisible(false);
                }
            },

            /**
             * New Address form visibility subscriber
             */
            isShippingFormVisibleUpdate: function () {
                this.updatePopupState();

                if (this.getVisible()) {
                    addressFormState.isShippingFormVisible(
                        this.isAddressListVisible()
                    );
                }
            },

            /*
             * html Select binding methods
             */

            /**
             * Options array
             */
            addressOptions: ko.pureComputed(function () {
                var addressOptions = _.clone(addressList()),
                    newAddressAdded;

                _.find(addressOptions, function (address) {
                    if (isAddressNew(address)) {
                        newAddressAdded = true;

                        return true;//break
                    }
                });

                if (!newAddressAdded) {
                    addressOptions.push(newAddressOption);
                }

                return addressOptions;
            }),

            /**
             * For html option text binding
             *
             * @param {Object} address
             * @return {string}
             */
            addressOptionsText: function (address) {
                if (address.getAddressInline) {
                    return address.getAddressInline();
                }

                if (isAddressNew(address)) {
                    return $t('New Address');
                }

                return this.getCaptionByAddressType(address);
            },

            /**
             * @param {Object} address
             * @returns {*}
             */
            getCaptionByAddressType: function (address) {
                switch (address.getType()) {
                    case 'gift-registry':
                        return $t('Recipient Address');
                    default:
                        return address.getKey();
                }
            },

            /**
             * Update address action
             */
            updateAddress: function () {
                if (this.selectedAddress() != null) {
                    if (this.isAddressFormVisible()) {
                        this.shippingAddressComponent().saveNewAddress();
                    } else {
                        selectShippingAddress(this.selectedAddress());
                        checkoutData.setSelectedShippingAddress(this.selectedAddress().getKey());
                    }
                }

                this.cancelAddressEdit();
            },

            /**
             * Update dropdown selected value. Should same as current shipping address.
             */
            updateDropdownAddress: function () {
                var shippingAddress = quote.shippingAddress(),
                    list = addressList(),
                    addressIndex,
                    selectedAddress;

                selectedAddress = _.find(list, function (address) {
                    return address === shippingAddress;
                });

                if (!selectedAddress && isAddressNew(shippingAddress)) {
                    //Resolve when shipping address and selectedAddress different objects with same value.
                    addressIndex = _.findIndex(list, function (address) {
                        return isAddressNew(address);
                    });

                    if (addressIndex !== -1) {
                        list[addressIndex] = shippingAddress;
                        addressList(list);
                    }
                }

                this.selectedAddress(shippingAddress);
            },

            /**
             * Show address dropdown
             */
            changeAddress: function () {
                this.updateDropdownAddress();
                this.isAddressListVisible(true);
                this.isAddressDetailsVisible(false);
            },

            /**
             * Cancel address edit action.
             */
            cancelAddressEdit: function () {
                this.updateDropdownAddress();
                this.isAddressDetailsVisible(true);
                this.isAddressListVisible(false);
            },

            /**
             * On dropdown value change
             *
             * @param {Object} address
             */
            onAddressChange: function (address) {
                this.isAddressFormVisible(isAddressNew(address));
            }
        });
    };
});
