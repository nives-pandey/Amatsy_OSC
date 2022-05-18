/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/textarea'
], function (Component) {
    'use strict';

    return Component.extend({
        onUpdate: function (newValue) {
            this._super();
            if (this.isDifferedFromDefault()) {
                this.source.trigger('amcheckout.additional:save');
            }
        }
    });
});
