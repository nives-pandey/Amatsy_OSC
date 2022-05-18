/* jshint browser:true jquery:true */
var amasty_mixin_enabled = !window.amasty_checkout_disabled,
    config;

config = {
    'map': { '*': {} },
    config: {
        mixins: {
            'Magento_Checkout/js/model/new-customer-address': {
                'Amasty_Checkout/js/model/new-customer-address-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/view/payment/list': {
                'Amasty_Checkout/js/view/payment/list': amasty_mixin_enabled
            },
            'Magento_Checkout/js/view/summary/abstract-total': {
                'Amasty_Checkout/js/view/summary/abstract-total': amasty_mixin_enabled
            },
            'Magento_Checkout/js/model/step-navigator': {
                'Amasty_Checkout/js/model/step-navigator-mixin': amasty_mixin_enabled
            },
            'Magento_Paypal/js/action/set-payment-method': {
                'Amasty_Checkout/js/action/set-payment-method-mixin': amasty_mixin_enabled
            },
            'Magento_CheckoutAgreements/js/model/agreements-assigner': {
                'Amasty_Checkout/js/model/agreements-assigner-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/view/summary': {
                'Amasty_Checkout/js/view/summary-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/view/shipping': {
                'Amasty_Checkout/js/view/shipping-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/view/summary/cart-items': {
                'Amasty_Checkout/js/view/summary/cart-items-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/model/payment/additional-validators': {
                'Amasty_Checkout/js/model/payment-validators/additional-validators-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Amasty_Checkout/js/model/checkout-data-resolver-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/model/shipping-rates-validator': {
                'Amasty_Checkout/js/model/shipping-rates-validator-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Amasty_Checkout/js/action/set-shipping-information-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/model/full-screen-loader': {
                'Amasty_Checkout/js/model/full-screen-loader-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/model/shipping-rate-processor/new-address': {
                'Amasty_Checkout/js/model/default-shipping-rate-processor-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/view/payment': {
                'Amasty_Checkout/js/view/payment-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/model/payment-service': {
                'Amasty_Checkout/js/model/payment-service-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/model/address-converter': {
                'Amasty_Checkout/js/model/address-converter-mixin': amasty_mixin_enabled
            },
            'Magento_Paypal/js/view/payment/method-renderer/in-context/checkout-express': {
                'Amasty_Checkout/js/view/payment/method-renderer/in-context/checkout-express-mixin':
                    amasty_mixin_enabled
            },

            // in Magento 2.4 module Magento_Braintree renamed to Paypal_Braintree
            'Magento_Braintree/js/view/payment/method-renderer/paypal': {
                'Amasty_Checkout/js/view/payment/method-renderer/braintree/paypal-mixin':
                    amasty_mixin_enabled
            },
            'PayPal_Braintree/js/view/payment/method-renderer/paypal': {
                'Amasty_Checkout/js/view/payment/method-renderer/braintree/paypal-mixin':
                    amasty_mixin_enabled
            },
            'Magento_Braintree/js/view/payment/method-renderer/cc-form': {
                'Amasty_Checkout/js/view/payment/method-renderer/braintree/cc-form-mixin': amasty_mixin_enabled
            },
            'PayPal_Braintree/js/view/payment/method-renderer/cc-form': {
                'Amasty_Checkout/js/view/payment/method-renderer/braintree/cc-form-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/view/billing-address': {
                'Amasty_Checkout/js/view/billing-address-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/view/payment/default': {
                'Amasty_Checkout/js/view/payment/method-renderer/default-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/model/shipping-rate-registry': {
                'Amasty_Checkout/js/model/shipping-rate-registry-mixin': amasty_mixin_enabled
            },
            'Magento_Checkout/js/view/shipping-address/address-renderer/default': {
                'Amasty_Checkout/js/view/shipping-address/address-renderer/default-mixin': amasty_mixin_enabled
            },
            'Amasty_Gdpr/js/model/consents-assigner': {
                'Amasty_Checkout/js/model/consents-assigner-mixin': amasty_mixin_enabled
            }
        }
    }
};

if (amasty_mixin_enabled) {
    config.map['*'] = {
        checkoutCollapsibleSteps: 'Amasty_Checkout/js/view/checkout/design/collapsible-steps',
        summaryWidget: 'Amasty_Checkout/js/view/summary/summary-widget',
        stickyWidget: 'Amasty_Checkout/js/view/summary/sticky-widget',
        'Magento_Checkout/template/payment-methods/list.html': 'Amasty_Checkout/template/payment-methods/list.html',
        'Magento_Checkout/template/billing-address/details.html':
            'Amasty_Checkout/template/onepage/billing-address/details.html',
        'Magento_Checkout/js/action/get-totals': 'Amasty_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/shipping-rate-service': 'Amasty_Checkout/js/model/shipping-rate-service-override',
        'Magento_Checkout/js/action/recollect-shipping-rates': 'Amasty_Checkout/js/action/recollect-shipping-rates'
    };
}
