$(function () {
    'use strict';
    var lineChartEx4 = $('.line-chart-ex4'),
        lineChartEx5 = $('.line-chart-ex5');

    // Color Variables
    var tooltipShadow = 'rgba(0, 0, 0, 0.25)',
        lineChartPrimary = '#666ee8',
        lineChartDanger = '#ff4961',
        labelColor = '#6e6b7b',
        grid_line_color = 'rgba(200, 200, 200, 0.2)'; // RGBA color helps in dark layout


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



    if (typeof donutChartCust !== undefined && donutChartCust !== null) {
        var donutChart = new ApexCharts(donutChartCust, donutChartConfig);
        donutChart.render();
    }

    var topCustomerDonutChart = document.querySelector('#top-customer-donut-chart'),
        topCustomerDonut = {
            chart: {
                height: 350,
                type: 'donut'
            },
            legend: {
                show: true,
                position: 'bottom'
            },
            labels: (typeof top5CustomerCategories !== 'undefined' && top5CustomerCategories !== null) ? top5CustomerCategories : [],
            series: (typeof top5CustomerSalesPrc !== 'undefined' && top5CustomerSalesPrc !== null) ? top5CustomerSalesPrc : [],
            colors: (typeof top5CustomerColorCode !== 'undefined' && top5CustomerColorCode !== null) ? top5CustomerColorCode : [],
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



    if (typeof topCustomerDonutChart !== undefined && topCustomerDonutChart !== null) {
        var topCustomerDonutChart = new ApexCharts(topCustomerDonutChart, topCustomerDonut);
        topCustomerDonutChart.render();
    }

    if (lineChartEx4.length) {
        var maxCustomerTargetValue = Math.max(...salesCustomerTarget);
        var maxSalesOrderValue = Math.max(...salesOrderSummary);

        var maxDataValue = Math.max(maxCustomerTargetValue, maxSalesOrderValue);
        // var maxDataValue = 250;
        var steps = 5; // Maximum steps
        var stepSize, maxYValue;

        function calculateSalesChartStepSize(maxValue, steps) {
            let initialStepSize = maxValue / steps;
            if (maxValue >= 10000000) {
                return Math.ceil(initialStepSize / 1000) * 1000;
            } else if (maxValue >= 1000000) {
                return Math.ceil(initialStepSize / 100) * 100;
            } else if (maxValue >= 100000) {
                return Math.ceil(initialStepSize / 100) * 100;
            } else if (maxValue >= 10000) {
                return Math.ceil(initialStepSize / 100) * 100;
            } else if (maxValue >= 1000) {
                return Math.ceil(initialStepSize / 10) * 10;
            } else if (maxValue >= 100) {
                return Math.ceil(initialStepSize / 10) * 10;
            } else {
                return Math.ceil(initialStepSize);
            }
        }

        if (maxDataValue === 0) {
            stepSize = 5;
        } else {
            stepSize = calculateSalesChartStepSize(maxDataValue, steps);
        }

        if (maxDataValue === 0) {
            var totalSteps = steps;
            maxYValue = stepSize * totalSteps;
        } else {
            var totalSteps = Math.ceil(maxDataValue / stepSize);
            maxYValue = stepSize * steps;
        }

        var lineExample = new Chart(lineChartEx4, {
            type: 'line',
            plugins: [
                // to add spacing between legends and chart
                {
                    beforeInit: function (chart) {
                        chart.legend.afterFit = function () {
                            this.height += 20;
                        };
                    }
                }
            ],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                backgroundColor: false,
                hover: {
                    mode: 'label'
                },
                tooltips: {
                    // Updated default tooltip UI
                    shadowOffsetX: 1,
                    shadowOffsetY: 1,
                    shadowBlur: 8,
                    shadowColor: tooltipShadow,
                    backgroundColor: window.colors.solid.white,
                    titleFontColor: window.colors.solid.black,
                    bodyFontColor: window.colors.solid.black
                },
                layout: {
                    padding: {
                        top: -15,
                        bottom: -25,
                        left: -15
                    }
                },
                scales: {
                    xAxes: [
                        {
                            display: true,
                            scaleLabel: {
                                display: true
                            },
                            gridLines: {
                                display: true,
                                color: grid_line_color,
                                zeroLineColor: grid_line_color
                            },
                            ticks: {
                                fontColor: labelColor
                            }
                        }
                    ],
                    yAxes: [
                        {
                            display: true,
                            scaleLabel: {
                                display: true
                            },
                            ticks: {
                                stepSize: stepSize,
                                min: 0,
                                max: maxYValue,
                                fontColor: labelColor
                            },

                            gridLines: {
                                display: true,
                                color: grid_line_color,
                                zeroLineColor: grid_line_color
                            }
                        }
                    ]
                },
                legend: {
                    position: 'bottom',
                    align: 'center',
                    labels: {
                        usePointStyle: true,
                        padding: 10,
                        boxWidth: 7
                    }
                }
            },
            data: {
                labels: salesLabels,
                datasets: [
                    {
                        data: salesCustomerTarget,
                        label: 'Budget',
                        borderColor: lineChartDanger,
                        lineTension: 0.5,
                        pointStyle: 'circle',
                        backgroundColor: lineChartDanger,
                        fill: false,
                        pointRadius: 1,
                        pointHoverRadius: 5,
                        pointHoverBorderWidth: 5,
                        pointBorderColor: 'transparent',
                        pointHoverBorderColor: window.colors.solid.white,
                        pointHoverBackgroundColor: lineChartDanger,
                        pointShadowOffsetX: 1,
                        pointShadowOffsetY: 1,
                        pointShadowBlur: 5,
                        pointShadowColor: tooltipShadow
                    },
                    {
                        data: salesOrderSummary,
                        label: 'Sale',
                        borderColor: lineChartPrimary,
                        lineTension: 0.5,
                        pointStyle: 'circle',
                        backgroundColor: lineChartPrimary,
                        fill: false,
                        pointRadius: 1,
                        pointHoverRadius: 5,
                        pointHoverBorderWidth: 5,
                        pointBorderColor: 'transparent',
                        pointHoverBorderColor: window.colors.solid.white,
                        pointHoverBackgroundColor: lineChartPrimary,
                        pointShadowOffsetX: 1,
                        pointShadowOffsetY: 1,
                        pointShadowBlur: 5,
                        pointShadowColor: tooltipShadow
                    },

                ]
            }
        });
    }

    if (lineChartEx5.length) {
        var lineExample = new Chart(lineChartEx5, {
            type: 'line',
            plugins: [
                // to add spacing between legends and chart
                {
                    beforeInit: function (chart) {
                        chart.legend.afterFit = function () {
                            this.height += 0;
                        };
                    }
                }
            ],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                backgroundColor: false,
                hover: {
                    mode: 'label'
                },
                tooltips: {
                    // Updated default tooltip UI
                    shadowOffsetX: 1,
                    shadowOffsetY: 1,
                    shadowBlur: 8,
                    shadowColor: tooltipShadow,
                    backgroundColor: window.colors.solid.white,
                    titleFontColor: window.colors.solid.black,
                    bodyFontColor: window.colors.solid.black
                },
                layout: {
                    padding: {
                        top: 5,
                        bottom: 5,
                        left: -15
                    }
                },
                scales: {
                    xAxes: [
                        {
                            display: true,
                            scaleLabel: {
                                display: true
                            },
                            gridLines: {
                                display: true,
                                color: grid_line_color,
                                zeroLineColor: grid_line_color
                            },
                            ticks: {
                                fontColor: labelColor
                            }
                        }
                    ],
                    yAxes: [
                        {
                            display: true,
                            scaleLabel: {
                                display: true
                            },
                            ticks: {
                                // stepSize: 200,
                                // min: 0,
                                // max: 800,
                                fontColor: labelColor
                            },

                            // gridLines: {
                            //     display: true,
                            //     color: grid_line_color,
                            //     zeroLineColor: grid_line_color
                            // }
                        }
                    ]
                },
                legend: {
                    position: 'bottom',
                    align: 'center',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        boxWidth: 7
                    }
                }
            },
            data: {
                labels: prospectsLabels,
                datasets: [
                    {
                        data: prospectsData,
                        label: 'Purchase',
                        borderColor: lineChartDanger,
                        lineTension: 0.5,
                        pointStyle: 'circle',
                        backgroundColor: lineChartDanger,
                        fill: false,
                        pointRadius: 1,
                        pointHoverRadius: 5,
                        pointHoverBorderWidth: 5,
                        pointBorderColor: 'transparent',
                        pointHoverBorderColor: window.colors.solid.white,
                        pointHoverBackgroundColor: lineChartDanger,
                        pointShadowOffsetX: 1,
                        pointShadowOffsetY: 1,
                        pointShadowBlur: 5,
                        pointShadowColor: tooltipShadow
                    }

                ]
            }
        });
    }
});