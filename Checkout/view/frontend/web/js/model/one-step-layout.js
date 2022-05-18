// Checkout layout options model
define([
    'ko',
    'uiRegistry',
    'Amasty_Checkout/js/view/utils',
    'uiLayout'
], function (ko, registry, viewUtils, layout) {
    'use strict';

    const MAPPING_BLOCK_NAME = {
            shipping_address: 'checkout.steps.shipping-step.shippingAddress',
            shipping_method: 'checkout.steps.shipping-step.shippingAddress',
            delivery: 'checkout.steps.shipping-step.amcheckout-delivery-date',
            payment_method: 'checkout.steps.billing-step',
            summary: 'checkout.sidebar',
            additional_checkboxes: 'checkout.sidebar.additional.checkboxes'
        },
        CLASS_NAMES = {
            classic: {
                main: 'opc-wrapper am-opc-wrapper',
                column: 'checkout-column opc',
                block: 'checkout-block'
            },
            modern: {
                main: 'amcheckout-main-container',
                column: 'amcheckout-column',
                block: 'amcheckout-block amcheckout-step-container'
            }
        },
        BLOCK_ATTRS = {
            classic: {},
            modern: {
                main: {
                    'data-amcheckout-js': 'main-container'
                }
            }
        };

    return {
        containerClassNames: ko.observable(''),
        selectedLayout: [],
        checkoutDesign: '',
        checkoutLayout: '',
        checkoutBlocks: {},
        mainAdditionalClasses: '',
        gdprConsents: window.checkoutConfig.amastyOscGdprConsent,
        gdprComponents: {},

        /**
         * Getting checkout block by name
         * @param {String} blockName
         * @returns {observable}
         */
        getCheckoutBlock: function (blockName) {
            var requestComponent = this.checkoutBlocks[blockName]
                || this.requestComponent(MAPPING_BLOCK_NAME[blockName]);

            switch (blockName) {
                case 'shipping_address':
                    if (requestComponent()) {
                        requestComponent().template = 'Amasty_Checkout/onepage/shipping/address';
                    }

                    break;

                case 'shipping_method':
                    if (requestComponent()) {
                        requestComponent().template = 'Amasty_Checkout/onepage/shipping/methods';
                    }

                    break;

                default: break;
            }

            return requestComponent;
        },

        /**
         * Getting component by name from registry
         * @param {String} name
         * @returns {observable}
         */
        requestComponent: function (name) {
            var observable = ko.observable();

            registry.get(name, function (summary) {
                observable(summary);
            });

            this.checkoutBlocks[name] = observable;

            return observable;
        },

        /**
         * Emulate 2columns layout for virtual quote checkout
         * @returns {Array}
         */
        getVirtualLayout: function () {
            return [
                [viewUtils.getBlockLayoutConfig('payment_method')],
                [viewUtils.getBlockLayoutConfig('summary')]
            ];
        },

        /**
         * Getting checkout container css classes by selected design
         * @returns {String}
         */
        setContainerClassNames: function () {
            var classNames = this.containerClassNames() + ' ' + CLASS_NAMES[this.checkoutDesign].main;

            if (this.checkoutDesign === 'modern') {
                classNames += ' -' + this.checkoutDesign + ' -layout-' + this.checkoutLayout;
            } else {
                classNames += ' layout-' + this.checkoutLayout;
            }

            classNames += ' ' + this.mainAdditionalClasses;

            this.containerClassNames(classNames);
        },

        /**
         * Getting checkout container attributes by selected design
         * @returns {Object}
         */
        getContainerAttrs: function () {
            return BLOCK_ATTRS[this.checkoutDesign].main;
        },

        /**
         * Getting checkout column css classes by design
         * @param {Number} columnIndex
         * @returns {String}
         */
        getColumnClassNames: function (columnIndex) {
            var classNames = '',
                defaultClassNames = CLASS_NAMES;

            classNames = defaultClassNames[this.checkoutDesign].column
                + this.getAdditionalColumnClassNames(columnIndex);

            return classNames;
        },

        /**
         * Getting checkout column additional css classes by design, layout and column type
         * @param {Number} columnIndex
         * @returns {String}
         */
        getAdditionalColumnClassNames: function (columnIndex) {
            var additionalColumnsClasses = '';

            if (this.checkoutDesign === 'modern' && this.checkoutLayout === '2columns') {
                if (columnIndex === 0) {
                    additionalColumnsClasses += ' -main';
                } else if (columnIndex === 1) {
                    additionalColumnsClasses += ' -sidebar';
                }
            }

            return additionalColumnsClasses;
        },

        /**
         * Getting checkout column tag attributes by design, layout and column type
         * @param {Number} columnIndex
         * @returns {Object}
         */
        getColumnAttrs: function (columnIndex) {
            var columnAttrs = {},
                checkoutJsPrefix = 'data-amcheckout-js',
                defaultAttrs = BLOCK_ATTRS[this.checkoutDesign].column;

            columnAttrs = defaultAttrs || {};

            if (this.checkoutDesign === 'modern' && this.checkoutLayout === '2columns') {
                if (columnIndex === 0) {
                    columnAttrs[checkoutJsPrefix] = 'main-column';
                } else if (columnIndex === 1) {
                    columnAttrs[checkoutJsPrefix] = 'sidebar-column';
                }
            }

            return columnAttrs;
        },

        /**
         * Getting checkout block css classes
         * @param {String} blockName
         * @returns {*}
         */
        getBlockClassNames: function (blockName) {
            var defaultClassNames = CLASS_NAMES,
                classNames = defaultClassNames[this.checkoutDesign].block;

            if (blockName === 'summary') {
                classNames += ' -summary';
            }

            return classNames;
        },

        /**
         * Getting checkout block tag attributes by design and layout
         * @returns {Object}
         */
        getBlockAttrs: function () {
            var blockAttrs = {},
                checkoutJsPrefix = 'data-amcheckout-js',
                defaultAttrs = BLOCK_ATTRS[this.checkoutDesign].block;

            blockAttrs = defaultAttrs || {};

            if (this.checkoutDesign === 'modern' && this.checkoutLayout !== '3columns') {
                blockAttrs[checkoutJsPrefix] = 'step-container';
            }

            return blockAttrs;
        },

        /**
         * Check modern 2 columns design
         * @returns {boolean}
         */
        isModernTwoColumns: function () {
            return this.checkoutDesign === 'modern' && this.checkoutLayout === '2columns';
        },

        /**
         * @param {String} blockName
         * @param {Object|null} componentConfig
         * @return {observable}
         */
        getGdprComponent: function (blockName, componentConfig) {
            var name = componentConfig ? componentConfig.name + '-' + blockName : '',
                component = this.gdprComponents[name],
                config = {};

            if (this.gdprConsents[blockName] && name && !component) {
                component = ko.observable();
                config = Object.assign(
                    config,
                    componentConfig,
                    {
                        name: name,
                        items: this.gdprConsents[blockName]['consents'],
                        meta: this.gdprConsents[blockName]['meta']
                    }
                );

                // move checkbox before place order button
                if (blockName === 'summary') {
                    config.parent = MAPPING_BLOCK_NAME.additional_checkboxes;
                }

                layout([config]);
                registry.get(name, function (target) {
                    component(target);
                });

                this.gdprComponents[name] = component;
            }

            return component;
        }
    };
});
