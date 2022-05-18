/**
 * Gdpr component inheritance to avoid incorrect checkbox displaying by country
 */
define(
    [
        'Amasty_Gdpr/js/view/gdpr-consent',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, quote) {
        'use strict';

        return Component.extend({

            initialize: function () {
                this._super();

                if (quote.billingAddress()) {
                    quote.billingAddress.valueHasMutated();
                }
            }
        });
    }
);
