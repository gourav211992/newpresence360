$(function () {
    'use strict';
    var chartColors = {
        column: {
            series1: '#826af9',
            series2: '#d2b0ff',
            bg: '#f8d3ff'
        },
        success: {
            shade_100: '#7eefc7',
            shade_200: '#06774f'
        },
        donut: {
            series1: '#00d4bd',
            series2: '#ffe700',
            series3: '#826bf8',
            series4: '#2b9bf4',
            series5: '#FFA1A1',
            series6: '#DC1B54',
            series7: '#2372B5',
            series8: '#F07C00',
            series9: '#C6178A',
            series10: '#ea5455',
            series11: '#28c76f',
            series12: '#06d0e9',
            series13: '#ff9f43',
            series14: '#6b12b7',

        },
        area: {
            series3: '#a4f8cd',
            series2: '#60f2ca',
            series1: '#2bdac7'
        }
    };

    $(window).on('load', function () {
        if (feather) {
            feather.replace({
                width: 14,
                height: 14
            });
        }
    })



    var donutChartCust = document.querySelector('#donut-chart-customer'),
        donutChartConfig = {
            chart: {
                height: 350,
                type: 'donut'
            },
            legend: {
                show: true,
                position: 'bottom'
            },
            labels: (typeof diariesStatusTitle !== 'undefined' && diariesStatusTitle !== null) ? diariesStatusTitle : [],
            series: (typeof diariesSalesPercentage !== 'undefined' && diariesSalesPercentage !== null) ? diariesSalesPercentage : [],
            colors: (typeof diariesColorCode !== 'undefined' && diariesColorCode !== null) ? diariesColorCode : [],
            dataLabels: {
                enabled: true,
                formatter: function (val, opt) {
                    return parseInt(val) + '%';
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            name: {
                                fontSize: '1rem',
                                fontFamily: 'Montserrat'
                            },
                            value: {
                                fontSize: '1rem',
                                fontFamily: 'Montserrat',
                                formatter: function (val) {
                                    return parseInt(val) + '%';
                                }
                            },
                            total: {
                                show: true,
                                fontSize: '1rem',
                                label: 'Total',

                            }
                        }
                    }
                }
            },
            responsive: [
                {
                    breakpoint: 992,
                    options: {
                        chart: {
                            height: 380
                        }
                    }
                },
                {
                    breakpoint: 576,
                    options: {
                        chart: {
                            height: 320
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    labels: {
                                        show: true,
                                        name: {
                                            fontSize: '1.5rem'
                                        },
                                        value: {
                                            fontSize: '1rem'
                                        },
                                        total: {
                                            fontSize: '1.5rem'
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            ]
        };

    if (typeof donutChartCust !== undefined && donutChartCust !== null) {
        var donutChart = new ApexCharts(donutChartCust, donutChartConfig);
        donutChart.render();
    }


    var prospectsPipeline = {
        series: [
            {
                name: "",
                data: (typeof prospectsStatusCount !== 'undefined' && prospectsStatusCount !== null) ? prospectsStatusCount : [],
            },
        ],
        chart: {
            type: 'bar',
            height: 250,
            dropShadow: {
                enabled: false,
            },
        },
        plotOptions: {
            bar: {
                borderRadius: 0,
                horizontal: true,
                distributed: true,
                barHeight: '80%',
                isFunnel: true,
            },
        },
        colors: (typeof prospectsColorCode !== 'undefined' && prospectsColorCode !== null) ? prospectsColorCode : [],
        dataLabels: {
            enabled: false,
            formatter: function (val, opt) {
                return opt.w.globals.labels[opt.dataPointIndex]
            },

            dropShadow: {
                enabled: false,
            },
        },
        xaxis: {
            categories: [],

        },
        legend: {
            show: false,
        },
    };

    if (typeof prospectsPipeline !== undefined && prospectsPipeline !== null) {
        var chart = new ApexCharts(document.querySelector("#chart"), prospectsPipeline);
        chart.render();
    }

    var donutChartProsp = document.querySelector('#donut-chart-prospects'),
        donutChartConfig = {
            chart: {
                height: 350,
                type: 'donut'
            },
            legend: {
                show: true,
                position: 'bottom'
            },
            labels: (typeof supplySplitTitle !== 'undefined' && supplySplitTitle !== null) ? supplySplitTitle : [],
            series: (typeof supplySplitPercentage !== 'undefined' && supplySplitPercentage !== null) ? supplySplitPercentage : [],
            colors: (typeof supplySplitColorCode !== 'undefined' && supplySplitColorCode !== null) ? supplySplitColorCode : [],
            dataLabels: {
                enabled: true,
                formatter: function (val, opt) {
                    return parseInt(val) + '%';
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        labels: {
                            show: true,
                            name: {
                                fontSize: '1rem',
                                fontFamily: 'Montserrat'
                            },
                            value: {
                                fontSize: '1rem',
                                fontFamily: 'Montserrat',
                                formatter: function (val) {
                                    return parseInt(val) + '%';
                                }
                            },
                            total: {
                                show: true,
                                fontSize: '1rem',
                                label: 'Total',

                            }
                        }
                    }
                }
            },
            responsive: [
                {
                    breakpoint: 992,
                    options: {
                        chart: {
                            height: 380
                        }
                    }
                },
                {
                    breakpoint: 576,
                    options: {
                        chart: {
                            height: 320
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    labels: {
                                        show: true,
                                        name: {
                                            fontSize: '1.5rem'
                                        },
                                        value: {
                                            fontSize: '1rem'
                                        },
                                        total: {
                                            fontSize: '1.5rem'
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            ]
        };

    if (typeof donutChartProsp !== undefined && donutChartProsp !== null) {
        var donutChart = new ApexCharts(donutChartProsp, donutChartConfig);
        donutChart.render();
    }


});
