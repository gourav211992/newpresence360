$(function () {
    'use strict';

    var chartColors = {
        donut: {
            series1: '#00d4bd',
            series2: '#ffe700',
            series3: '#2b9bf4',

        }
    };

    var radialBarChartEl = document.querySelector('#radialbar-chart'),
        radialBarChartConfig = {
            chart: {
                height: 350,
                type: 'radialBar'
            },
            colors: [chartColors.donut.series1, chartColors.donut.series2, chartColors.donut.series3],
            plotOptions: {
                radialBar: {
                    size: 185,
                    hollow: {
                        size: '25%'
                    },
                    track: {
                        margin: 15
                    },
                    dataLabels: {
                        name: {
                            fontSize: '2rem',
                            fontFamily: 'Montserrat'
                        },
                        value: {
                            fontSize: '1rem',
                            fontFamily: 'Montserrat'
                        },
                        total: {
                            show: true,
                            fontSize: '1rem',
                            label: 'Comments',
                            formatter: function (w) {
                                return '80%';
                            }
                        }
                    }
                }
            },
            grid: {
                padding: {
                    top: -35,
                    bottom: -30
                }
            },
            legend: {
                show: true,
                position: 'bottom'
            },
            stroke: {
                lineCap: 'round'
            },
            series: [80, 50, 35],
            labels: ['Comments', 'Replies', 'Shares']
        };

    if (typeof radialBarChartEl !== undefined && radialBarChartEl !== null) {
        var radialChart = new ApexCharts(radialBarChartEl, radialBarChartConfig);
        radialChart.render();
    }
});