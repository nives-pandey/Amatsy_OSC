/*jshint browser:true jquery:true*/
define([
    'jquery',
    'Magento_ConfigurableProduct/js/configurable',
    'Magento_Ui/js/modal/modal'
], function ($) {
    'use strict';

    $.widget('mage.amcheckoutConfigurable', $.mage.configurable, {
        /**
          * Initialize tax configuration, initial settings, and options values.
          * https://github.com/magento/magento2/issues/14211
          * @private
          */
        _initializeOptions: function () {
            var element = $(this.options.priceHolderSelector);

            if (!element.data('magePriceBox')) {
                element.priceBox();
            }

            return this._super();
        },

        /**
         * Initialize tax configuration, initial settings, and options values.
         * https://github.com/magento/magento2/issues/14211
         * @private
         */
        _calculatePrice: function (config) {
            var element = $(this.options.priceHolderSelector);

            if (!element.data('magePriceBox')) {
                element.priceBox();
            }

            return this._super(config);
        },

        configureElement: function (element) {
            return this._configureElement(element);
        },

        _getAttributeId: function (element) {
            return +$(element).data('attribute-id');
        },

        _fillState: function () {
            $.each(this.options.settings, $.proxy(function (index, element) {
                var attributeId = this._getAttributeId(element);

                if (attributeId && this.options.spConfig.attributes[attributeId]) {
                    element.config = this.options.spConfig.attributes[attributeId];
                    element.attributeId = attributeId;
                    this.options.state[attributeId] = false;
                }
            }, this));
        },

        _fillSelect: function (element) {
            var attributeId = this._getAttributeId(element),
                options = this._getAttributeOptions(attributeId),
                prevConfig,
                index = 1,
                allowedProducts,
                i,
                j;

            this._clearSelect(element);
            element.options[0] = new Option('', '');
            element.options[0].innerHTML = this.options.spConfig.chooseText;
            prevConfig = false;

            if (element.prevSetting) {
                prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
            }

            if (options) {
                for (i = 0; i < options.length; i++) {
                    allowedProducts = [];

                    if (prevConfig) {
                        for (j = 0; j < options[i].products.length; j++) {
                            // prevConfig.config can be undefined
                            if (prevConfig.config &&
                                prevConfig.config.allowedProducts &&
                                prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                allowedProducts.push(options[i].products[j]);
                            }
                        }
                    } else {
                        allowedProducts = options[i].products.slice(0);
                    }

                    if (allowedProducts.length > 0) {
                        options[i].allowedProducts = allowedProducts;
                        element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);

                        if (typeof options[i].price !== 'undefined') {
                            element.options[index].setAttribute('price', options[i].prices);
                        }

                        element.options[index].config = options[i];
                        index++;
                    }
                }
            }
        }
    });

    return $.mage.amcheckoutConfigurable;
});
