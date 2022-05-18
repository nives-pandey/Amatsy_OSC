// Checkout views utility methods
define(['underscore'], function (_) {
    'use strict';

    return {
        /**
         * Find checkout block layout config in window.checkoutConfig
         * @param {string} blockNames
         * @returns {null|{title:string, name:string}}
         */
        getBlockLayoutConfig: function (blockNames) {
            var resultBlock = null;

            _.find(window.checkoutConfig.checkoutBlocksConfig, function (column) {
                return _.find(column, function (block) {
                    if (blockNames === block.name) {
                        resultBlock = block;

                        return true;
                    }
                });
            });

            return resultBlock;
        },

        /**
         * Get checkout block title from window.checkoutConfig
         * @param {String} blockName
         * @returns {String}
         */
        getBlockTitle: function (blockName) {
            var blockConfig = this.getBlockLayoutConfig(blockName);

            return blockConfig ? blockConfig.title : '';
        }
    };
});
