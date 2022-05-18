define([
    'jquery',
    'Amasty_Checkout/vendor/amcharts/amcharts',
    'Amasty_Checkout/vendor/amcharts/serial',
    'mage/translate',
    'Magento_Ui/js/modal/modal'
], function ($) {
    'use strict';

    $.widget('amasty.sectionsRate', {
        options: {
            graphicInfo: null,
            sectionsRateGraphId: 'sections-rate-graph'
        },

        /**
         * Initialize widget
         */
        _create: function() {
            this.renderRateGraph(this.options.sectionsRateGraphId);
        },

        renderRateGraph: function (containerId) {
            var self = this;

            AmCharts.addInitHandler(function(chart) {
                var index,
                    graph,
                    dataItemIndex,
                    dataProvider;

                for (index = 0; index < chart.graphs.length; index++) {
                    graph = chart.graphs[index];
                    if (graph.fillColorsFunction !== undefined) {
                        graph.fillColorsField = graph.valueField + "FillColor";
                        for (dataItemIndex = 0; dataItemIndex < chart.dataProvider.length; dataItemIndex++) {
                            dataProvider = chart.dataProvider[dataItemIndex];
                            dataProvider[graph.fillColorsField] = graph.fillColorsFunction.call(this, dataProvider[graph.valueField]);
                        }
                    }
                }

            }, ["serial"]);

            AmCharts.makeChart(containerId,
                {
                    "type": "serial",
                    "categoryField": "label",
                    "fontFamily": "Open Sans",
                    "fontSize": 15,
                    "color": "#303030",
                    "startDuration": 1,
                    "categoryAxis": {
                        "gridPosition": "start"
                    },
                    "trendLines": [],
                    "graphs": [
                        {
                            "balloonText": $.mage.__("Form Completion Rate of [[category]]: [[value]]%"),
                            "fillAlphas": 1,
                            "fillColorsFunction": self.changeColumnColour,
                            "lineAlpha": 0,
                            "id": "AmGraph-1",
                            "labelText": "[[value]]",
                            "title": $.mage.__("Form Completion Rate, %"),
                            "type": "column",
                            "valueField": "size"
                        }
                    ],
                    "guides": [],
                    "valueAxes": [
                        {
                            "id": "ValueAxis-1",
                            "title": $.mage.__("Form Completion Rate, %")
                        }
                    ],
                    "allLabels": [],
                    "balloon": {},
                    "legend": {
                        "enabled": false,
                        "useGraphSettings": true
                    },
                    "titles": [
                        {
                            "id": "Title-1",
                            "text": $.mage.__("Checkout Sections")
                        }
                    ],
                    "dataProvider": self.options.graphicInfo
                }
            );
        },

        changeColumnColour: function (value) {
            if (0 <= value && value <= 30) {
                return "#f46161";
            } else if (31 <= value && value <= 60) {
                return "#f89e5d";
            } else if (value > 60) {
                return "#76c769";
            }
        }
    });

    return $.amasty.sectionsRate;
});
