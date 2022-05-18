/* jshint browser:true jquery:true */
/* global alert */
var config = {
    map: {
        '*': {
            amastySectionsRate: 'Amasty_Checkout/js/reports/sections-rate',
            amCharts: 'Amasty_Checkout/vendor/amcharts/amcharts',
            amChartsSerial: 'Amasty_Checkout/vendor/amcharts/serial',
            amLayoutBuilder: 'Amasty_Checkout/js/layout-builder/build/layout-builder.bundle',
            amUseDefaultStateManager: 'Amasty_Checkout/js/model/default-state-manager'
        }
    },
    shim: {
        'Amasty_Checkout/vendor/amcharts/serial': [ 'Amasty_Checkout/vendor/amcharts/amcharts' ]
    }
};
