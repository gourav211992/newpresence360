$(window).on('load', function () {
    'use strict';

    var chartWrapper = $('.chartjs'),
        doughnutChartExpros = $('.doughnut-chart-ex-pros'),
        lineChartEx = $('.line-chart-ex'),
        barChartEx = $('.bar-chart-ex');

    // Color Variables
    var tooltipShadow = 'rgba(0, 0, 0, 0.25)',
        lineChartPrimary = '#666ee8',
        lineChartDanger = '#ff4961',
        labelColor = '#6e6b7b',
        grid_line_color = 'rgba(200, 200, 200, 0.2)';

    // Detect Dark Layout
    if ($('html').hasClass('dark-layout')) {
        labelColor = '#b4b7bd';
    }

    // Wrap charts with div of height according to their data-height
    if (chartWrapper.length) {
        chartWrapper.each(function () {
            $(this).wrap($('<div style="height:' + this.getAttribute('data-height') + 'px"></div>'));
        });
    }

    // Bar Chart
    // --------------------------------------------------------------------
    if (barChartEx.length) {
        var numOfProspects = prospectsLabels.length; // Get the number of labels
        var prospectColors = []; // Initialize an empty array to hold colors

        // Generate an array of random colors based on the number of prospects
        for (let i = 0; i < numOfProspects; i++) {
            prospectColors.push('#6B12B7'); // You can use a function to generate random colors
        }

        var barChartExample = new Chart(barChartEx, {
            type: 'bar',

            options: {
                elements: {
                    rectangle: {
                        borderWidth: 2,
                        borderSkipped: 'bottom'
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                responsiveAnimationDuration: 500,
                legend: {
                    display: false
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
                scales: {
                    xAxes: [
                        {
                            display: true,
                            gridLines: {
                                display: true,
                                color: grid_line_color,
                                zeroLineColor: grid_line_color
                            },
                            scaleLabel: {
                                display: false
                            },
                            ticks: {
                                fontColor: labelColor
                            }
                        }
                    ],
                    yAxes: [
                        {
                            display: true,
                            gridLines: {
                                color: grid_line_color,
                                zeroLineColor: grid_line_color
                            },
                            ticks: {
                                stepSize: 40,
                                min: 0,
                                fontColor: labelColor
                            }
                        }
                    ]
                }
            },
            data: {
                labels: prospectsLabels,
                datasets: [
                    {
                        data: prospectsData,
                        barThickness: 35,
                        backgroundColor: prospectColors,
                        borderColor: 'transparent'
                    }
                ]
            }
        });
    }

    if (doughnutChartExpros.length) {
        var doughnutExample = new Chart(doughnutChartExpros, {
            type: 'doughnut',
            options: {
                responsive: true,
                maintainAspectRatio: false,
                responsiveAnimationDuration: 500,
                cutoutPercentage: 0,
                legend: { display: false },
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
                            var label = data.datasets[0].labels[tooltipItem.index] || '',
                                value = data.datasets[0].data[tooltipItem.index];
                            var output = ' ' + label + ' : ' + value + ' %';
                            return output;
                        }
                    },
                    // Updated default tooltip UI
                    shadowOffsetX: 1,
                    shadowOffsetY: 1,
                    shadowBlur: 8,
                    shadowColor: tooltipShadow,
                    backgroundColor: window.colors.solid.white,
                    titleFontColor: window.colors.solid.black,
                    bodyFontColor: window.colors.solid.black
                },
                dataLabels: {
                    enabled: true,

                }
            },
            data: {
                datasets: [
                    {
                        labels: (typeof industryLabels !== 'undefined' && industryLabels !== null) ? industryLabels : [],
                        data: (typeof industryPercentage !== 'undefined' && industryPercentage !== null) ? industryPercentage : [],
                        backgroundColor: (typeof industryColorCode !== 'undefined' && industryColorCode !== null) ? industryColorCode : [],
                        borderWidth: 0,
                        pointStyle: 'rectRounded'
                    }
                ]
            }
        });
    }

    // Line Chart
    // --------------------------------------------------------------------
    if (lineChartEx.length) {
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

        var lineExample = new Chart(lineChartEx, {
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
                        data: salesOrderSummary,
                        label: 'Achieved',
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
                        data: salesCustomerTarget,
                        label: 'Target',
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

    //Draw rectangle Bar charts with rounded border
    Chart.elements.Rectangle.prototype.draw = function () {
        var ctx = this._chart.ctx;
        var viewVar = this._view;
        var left, right, top, bottom, signX, signY, borderSkipped, radius;
        var borderWidth = viewVar.borderWidth;
        var cornerRadius = 20;
        if (!viewVar.horizontal) {
            left = viewVar.x - viewVar.width / 2;
            right = viewVar.x + viewVar.width / 2;
            top = viewVar.y;
            bottom = viewVar.base;
            signX = 1;
            signY = top > bottom ? 1 : -1;
            borderSkipped = viewVar.borderSkipped || 'bottom';
        } else {
            left = viewVar.base;
            right = viewVar.x;
            top = viewVar.y - viewVar.height / 2;
            bottom = viewVar.y + viewVar.height / 2;
            signX = right > left ? 1 : -1;
            signY = 1;
            borderSkipped = viewVar.borderSkipped || 'left';
        }

        if (borderWidth) {
            var barSize = Math.min(Math.abs(left - right), Math.abs(top - bottom));
            borderWidth = borderWidth > barSize ? barSize : borderWidth;
            var halfStroke = borderWidth / 2;
            var borderLeft = left + (borderSkipped !== 'left' ? halfStroke * signX : 0);
            var borderRight = right + (borderSkipped !== 'right' ? -halfStroke * signX : 0);
            var borderTop = top + (borderSkipped !== 'top' ? halfStroke * signY : 0);
            var borderBottom = bottom + (borderSkipped !== 'bottom' ? -halfStroke * signY : 0);
            if (borderLeft !== borderRight) {
                top = borderTop;
                bottom = borderBottom;
            }
            if (borderTop !== borderBottom) {
                left = borderLeft;
                right = borderRight;
            }
        }

        ctx.beginPath();
        ctx.fillStyle = viewVar.backgroundColor;
        ctx.strokeStyle = viewVar.borderColor;
        ctx.lineWidth = borderWidth;
        var corners = [
            [left, bottom],
            [left, top],
            [right, top],
            [right, bottom]
        ];

        var borders = ['bottom', 'left', 'top', 'right'];
        var startCorner = borders.indexOf(borderSkipped, 0);
        if (startCorner === -1) {
            startCorner = 0;
        }

        function cornerAt(index) {
            return corners[(startCorner + index) % 4];
        }

        var corner = cornerAt(0);
        ctx.moveTo(corner[0], corner[1]);

        for (var i = 1; i < 4; i++) {
            corner = cornerAt(i);
            var nextCornerId = i + 1;
            if (nextCornerId == 4) {
                nextCornerId = 0;
            }

            var nextCorner = cornerAt(nextCornerId);

            var width = corners[2][0] - corners[1][0],
                height = corners[0][1] - corners[1][1],
                x = corners[1][0],
                y = corners[1][1];

            var radius = cornerRadius;

            if (radius > height / 2) {
                radius = height / 2;
            }
            if (radius > width / 2) {
                radius = width / 2;
            }

            if (!viewVar.horizontal) {
                ctx.moveTo(x + radius, y);
                ctx.lineTo(x + width - radius, y);
                ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
                ctx.lineTo(x + width, y + height - radius);
                ctx.quadraticCurveTo(x + width, y + height, x + width, y + height);
                ctx.lineTo(x + radius, y + height);
                ctx.quadraticCurveTo(x, y + height, x, y + height);
                ctx.lineTo(x, y + radius);
                ctx.quadraticCurveTo(x, y, x + radius, y);
            } else {
                ctx.moveTo(x + radius, y);
                ctx.lineTo(x + width - radius, y);
                ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
                ctx.lineTo(x + width, y + height - radius);
                ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
                ctx.lineTo(x + radius, y + height);
                ctx.quadraticCurveTo(x, y + height, x, y + height);
                ctx.lineTo(x, y + radius);
                ctx.quadraticCurveTo(x, y, x, y);
            }
        }

        ctx.fill();
        if (borderWidth) {
            ctx.stroke();
        }
    };
});