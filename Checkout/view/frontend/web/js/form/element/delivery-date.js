define([
        'ko',
        'jquery',
        'Magento_Ui/js/form/element/date',
        'mage/translate'
    ], function (ko, $, AbstractField, $t) {
        'use strict';
        return AbstractField.extend({
            defaults: {
                amcheckout_days: [],
                amcheckout_firstDay: 0
            },

            initConfig: function () {
                this._super();
                this.options.closeText = $t('Done');
                this.options.currentText = $t('Today');
                this.options.dayNames = [
                    $t('Sunday'),
                    $t('Monday'),
                    $t('Tuesday'),
                    $t('Wednesday'),
                    $t('Thursday'),
                    $t('Friday'),
                    $t('Saturday')
                ];
                this.options.dayNamesMin = [
                    $t('Su'),
                    $t('Mo'),
                    $t('Tu'),
                    $t('We'),
                    $t('Th'),
                    $t('Fr'),
                    $t('Sa')
                ];
                this.options.dayNamesShort = [
                    $t('Sun'),
                    $t('Mon'),
                    $t('Tue'),
                    $t('Wed'),
                    $t('Thu'),
                    $t('Fri'),
                    $t('Sat')
                ];
                this.options.monthNames = [
                    $t('January'),
                    $t('February'),
                    $t('March'),
                    $t('April'),
                    $t('May'),
                    $t('June'),
                    $t('July'),
                    $t('August'),
                    $t('September'),
                    $t('October'),
                    $t('November'),
                    $t('December')
                ];
                this.options.monthNamesShort = [
                    $t('Jan'),
                    $t('Feb'),
                    $t('Mar'),
                    $t('Apr'),
                    $t('May'),
                    $t('Jun'),
                    $t('Jul'),
                    $t('Aug'),
                    $t('Sep'),
                    $t('Oct'),
                    $t('Nov'),
                    $t('Dec')
                ];
                this.options.nextText = $t('Next');
                this.options.prevText = $t('Prev');
                this.options.weekHeader = $t('Wk');
                this.options.minDate = new Date();
                this.options.showOn = 'both';
                this.options.firstDay = this.amcheckout_firstDay;

                if (this.amcheckout_days.length > 0) {
                    this.options.beforeShowDay = this.restrictDates.bind(this);
                }

                this.prepareDateTimeFormats();

                return this;
            },

            restrictDates: function (d) {
                return [$.inArray(d.getDay(), this.amcheckout_days) != -1, ''];
            }
        });
    }
);
