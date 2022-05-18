define([
    'moment',
    'ko',
    'underscore',
    'jquery',
    'mage/translate',
    'mage/calendar'
], function (moment, ko, _, $, $t) {
    'use strict';

    var defaults = {
        showsTime: false,
        timeFormat: null,
        buttonImage: null,
        buttonImageOnly: null,
        buttonText: $t('Select Date')
    };

    ko.bindingHandlers.amastydatepicker = {

        init: function (el, valueAccessor) {

            var config = valueAccessor(),
                observable,
                options = {};

            _.extend(options, defaults);

            if (typeof config === 'object') {
                observable = config.storage;

                _.extend(options, config.options);
            } else {
                observable = config;
            }

            var date = moment(observable(), config.elem.pickerDateTimeFormat);

            $(el).calendar(options);
            observable() && $(el).datepicker('setDate', date.format('YYYY-MM-DD'));
            $(el).blur();

            ko.utils.registerEventHandler(el, 'change', function () {
                observable(this.value);
            });
        }
    };
});
