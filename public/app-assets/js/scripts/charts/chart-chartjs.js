/*=========================================================================================
    File Name: chart-chartjs.js
    Description: Chartjs Examples
    ----------------------------------------------------------------------------------------
    Item Name: Vuexy  - Vuejs, HTML & Laravel Admin Dashboard Template
    Author: PIXINVENT
    Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/

$(window).on('load', function () {
  'use strict';

  var chartWrapper = $('.chartjs'),
    flatPicker = $('.flat-picker'),
    barChartEx = $('.bar-chart-ex'),
    orderbarChartEx = $('.orderbar-chart-ex'),
    leavebarChartEx = $('.leavebar-chart-ex'),
    assignedtaskChartEx = $('.assignedtask-chart-ex'),
    horizontalBarChartEx = $('.horizontal-bar-chart-ex'),
    lineChartEx = $('.line-chart-ex'),
    lineChartEx2 = $('.line-chart-ex2'),
    lineChartEx3 = $('.line-chart-ex3'),
    lineChartEx4 = $('.line-chart-ex4'),
    lineChartEx5 = $('.line-chart-ex5'),
    radarChartEx = $('.radar-chart-ex'),
    polarAreaChartEx = $('.polar-area-chart-ex'),
    bubbleChartEx = $('.bubble-chart-ex'),
    doughnutChartEx = $('.doughnut-chart-ex'),
    doughnutChartExpros = $('.doughnut-chart-ex-pros'),
    scatterChartEx = $('.scatter-chart-ex'),
    lineAreaChartEx = $('.line-area-chart-ex');

  // Color Variables
  var primaryColorShade = '#836AF9',
    yellowColor = '#ffe800',
    successColorShade = '#28dac6',
    warningColorShade = '#ffe802',
    warningLightColor = '#FDAC34',
    infoColorShade = '#299AFF',
    greyColor = '#4F5D70',
    blueColor = '#2c9aff',
    blueLightColor = '#84D0FF',
    greyLightColor = '#EDF1F4',
    tooltipShadow = 'rgba(0, 0, 0, 0.25)',
    lineChartPrimary = '#666ee8',
    lineChartDanger = '#ff4961',
    labelColor = '#6e6b7b',
    grid_line_color = 'rgba(200, 200, 200, 0.2)'; // RGBA color helps in dark layout

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

  // Init flatpicker
  if (flatPicker.length) {
    var date = new Date();
    flatPicker.each(function () {
      $(this).flatpickr({
        mode: 'range',
        defaultDate: ['2019-05-01', '2019-05-10']
      });
    });
  }


  // leave Chart
  // --------------------------------------------------------------------
  if (leavebarChartEx.length) {
    var leavebarChartExample = new Chart(leavebarChartEx, {
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
                stepSize: 5,
                min: 0,
                max: 20,
                fontColor: labelColor
              }
            }
          ]
        }
      },
      data: {
        labels: ['Applied', 'Approved', 'Rejected'],
        datasets: [
          {
            data: [12, 15, 5],
            barThickness: 25,
            backgroundColor: ['#7367F0', '#00d4bd', '#EA5455'],
            borderColor: 'transparent'
          }
        ]
      }
    });
  }



  if (assignedtaskChartEx.length) {
    var assignedChartExample = new Chart(assignedtaskChartEx, {
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
                display: true
              },
              ticks: {
                fontColor: labelColor
              }
            }
          ],
          yAxes: [
            {
              scaleLabel: {
                display: true,
                labelString: 'Hours'
              },
              display: true,
              gridLines: {
                color: grid_line_color,
                zeroLineColor: grid_line_color
              },
              ticks: {
                stepSize: 30,
                min: 0,
                fontColor: labelColor
              }
            }
          ]
        }
      },
      data: {
        labels: ['Actual', 'Approved',],
        datasets: [
          {
            data: [50, 40],
            barThickness: 30,
            backgroundColor: ['#F07C00', '#00d4bd'],
            borderColor: 'transparent',
          }
        ]
      }
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

  // Horizontal Bar Chart
  // --------------------------------------------------------------------
  if (horizontalBarChartEx.length) {
    new Chart(horizontalBarChartEx, {
      type: 'horizontalBar',
      options: {
        elements: {
          rectangle: {
            borderWidth: 2,
            borderSkipped: 'right'
          }
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
        responsive: true,
        maintainAspectRatio: false,
        responsiveAnimationDuration: 500,
        legend: {
          display: false
        },
        layout: {
          padding: {
            bottom: -30,
            left: -25
          }
        },
        scales: {
          xAxes: [
            {
              display: true,
              gridLines: {
                zeroLineColor: grid_line_color,
                borderColor: 'transparent',
                color: grid_line_color
              },
              scaleLabel: {
                display: true
              },
              ticks: {
                min: 0,
                stepSize: 1,
                fontColor: labelColor
              }
            }
          ],
          yAxes: [
            {
              display: true,
              gridLines: {
                display: false
              },
              scaleLabel: {
                display: true
              },
              ticks: {
                fontColor: labelColor
              },
            }
          ]
        }
      },
      data: {
        labels: ['Super Admin', 'Admin', 'Contractor'],
        datasets: [
          {
            data: [5, 4, 1],
            barThickness: 15,
            backgroundColor: window.colors.solid.info,
            borderColor: 'transparent'
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

  if (lineChartEx2.length) {
    var lineExample = new Chart(lineChartEx2, {
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
                stepSize: 200,
                min: 0,
                max: 800,
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
        labels: ['Airtel', 'Vodafone', 'Jio', 'BSNL', 'MTNL'],
        datasets: [
          {
            data: [280, 650, 480, 570, 310, 460],
            label: 'Billed',
            borderColor: lineChartDanger2,
            lineTension: 0.5,
            pointStyle: 'circle',
            backgroundColor: lineChartDanger2,
            fill: false,
            pointRadius: 1,
            pointHoverRadius: 5,
            pointHoverBorderWidth: 5,
            pointBorderColor: 'transparent',
            pointHoverBorderColor: window.colors.solid.white,
            pointHoverBackgroundColor: lineChartDanger2,
            pointShadowOffsetX: 1,
            pointShadowOffsetY: 1,
            pointShadowBlur: 5,
            pointShadowColor: tooltipShadow
          },
          {
            data: [380, 225, 505, 630, 215, 595],
            label: 'Savings',
            borderColor: lineChartPrimary2,
            lineTension: 0.5,
            pointStyle: 'circle',
            backgroundColor: lineChartPrimary2,
            fill: false,
            pointRadius: 1,
            pointHoverRadius: 5,
            pointHoverBorderWidth: 5,
            pointBorderColor: 'transparent',
            pointHoverBorderColor: window.colors.solid.white,
            pointHoverBackgroundColor: lineChartPrimary2,
            pointShadowOffsetX: 1,
            pointShadowOffsetY: 1,
            pointShadowBlur: 5,
            pointShadowColor: tooltipShadow
          },

        ]
      }
    });
  }


  if (lineChartEx3.length) {
    var lineExample = new Chart(lineChartEx3, {
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
            bottom: -65,
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
                stepSize: 200,
                min: 0,
                max: 800,
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
        labels: ['Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [
          {
            data: [280, 650, 480, 570, 310, 460],
            label: 'Applications',
            borderColor: lineChartDanger2,
            lineTension: 0.5,
            pointStyle: 'circle',
            backgroundColor: lineChartDanger2,
            fill: false,
            pointRadius: 1,
            pointHoverRadius: 5,
            pointHoverBorderWidth: 5,
            pointBorderColor: 'transparent',
            pointHoverBorderColor: window.colors.solid.white,
            pointHoverBackgroundColor: lineChartDanger2,
            pointShadowOffsetX: 1,
            pointShadowOffsetY: 1,
            pointShadowBlur: 5,
            pointShadowColor: tooltipShadow
          },
          {
            data: [380, 225, 505, 630, 215, 595],
            label: 'Qualified',
            borderColor: lineChartPrimary2,
            lineTension: 0.5,
            pointStyle: 'circle',
            backgroundColor: lineChartPrimary2,
            fill: false,
            pointRadius: 1,
            pointHoverRadius: 5,
            pointHoverBorderWidth: 5,
            pointBorderColor: 'transparent',
            pointHoverBorderColor: window.colors.solid.white,
            pointHoverBackgroundColor: lineChartPrimary2,
            pointShadowOffsetX: 1,
            pointShadowOffsetY: 1,
            pointShadowBlur: 5,
            pointShadowColor: tooltipShadow
          },

        ]
      }
    });
  }


  if (lineChartEx4.length) {
    var lineExample = new Chart(lineChartEx4, {
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
                stepSize: 200,
                min: 0,
                max: 800,
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
            padding: 20,
            boxWidth: 7
          }
        }
      },
      data: {
        labels: ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
        datasets: [
          {
            data: [280, 650, 480, 570, 310, 460],
            label: 'Sales',
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
            data: [380, 225, 505, 630, 215, 595],
            label: 'Budget',
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
                stepSize: 200,
                min: 0,
                max: 800,
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
            padding: 20,
            boxWidth: 7
          }
        }
      },
      data: {
        labels: ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
        datasets: [
          {
            data: [280, 650, 480, 570, 310, 460],
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

  // Radar Chart
  // --------------------------------------------------------------------
  if (radarChartEx.length) {
    var canvas = document.getElementById('canvas');

    // For radar gradient color
    var gradientBlue = canvas.getContext('2d').createLinearGradient(0, 0, 0, 150);
    gradientBlue.addColorStop(0, 'rgba(155,136,250, 0.9)');
    gradientBlue.addColorStop(1, 'rgba(155,136,250, 0.8)');

    var gradientRed = canvas.getContext('2d').createLinearGradient(0, 0, 0, 150);
    gradientRed.addColorStop(0, 'rgba(255,161,161, 0.9)');
    gradientRed.addColorStop(1, 'rgba(255,161,161, 0.8)');

    var radarExample = new Chart(radarChartEx, {
      type: 'radar',
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
      data: {
        labels: ['STA', 'STR', 'AGI', 'VIT', 'CHA', 'INT'],
        datasets: [
          {
            label: 'Donté Panlin',
            data: [25, 59, 90, 81, 60, 82],
            fill: true,
            backgroundColor: gradientRed,
            borderColor: 'transparent',
            pointBackgroundColor: 'transparent',
            pointBorderColor: 'transparent'
          },
          {
            label: 'Mireska Sunbreeze',
            data: [40, 100, 40, 90, 40, 90],
            fill: true,
            backgroundColor: gradientBlue,
            borderColor: 'transparent',
            pointBackgroundColor: 'transparent',
            pointBorderColor: 'transparent'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        responsiveAnimationDuration: 500,
        legend: {
          position: 'top',
          labels: {
            padding: 25,
            fontColor: labelColor
          }
        },
        layout: {
          padding: {
            top: -20
          }
        },
        tooltips: {
          enabled: false,
          custom: function (tooltip) {
            var tooltipEl = document.getElementById('tooltip');
            if (tooltip.body) {
              tooltipEl.style.display = 'block';
              if (tooltip.body[0].lines && tooltip.body[0].lines[0]) {
                tooltipEl.innerHTML = tooltip.body[0].lines[0];
              }
            } else {
              setTimeout(function () {
                tooltipEl.style.display = 'none';
              }, 500);
            }
          }
        },
        gridLines: {
          display: false
        },
        scale: {
          ticks: {
            maxTicksLimit: 1,
            display: false,
            fontColor: labelColor
          },
          gridLines: {
            color: grid_line_color
          },
          angleLines: { color: grid_line_color }
        }
      }
    });
  }

  // Polar Area Chart
  // --------------------------------------------------------------------
  if (polarAreaChartEx.length) {
    var polarExample = new Chart(polarAreaChartEx, {
      type: 'polarArea',
      options: {
        responsive: true,
        maintainAspectRatio: false,
        responsiveAnimationDuration: 500,
        legend: {
          position: 'bottom',
          labels: {
            usePointStyle: true,
            padding: 15,
            boxWidth: 9,
            fontColor: labelColor
          }
        },
        layout: {
          padding: {
            top: -5,
            bottom: -5
          }
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
        scale: {
          scaleShowLine: true,
          scaleLineWidth: 1,
          ticks: {
            display: false,
            fontColor: labelColor
          },
          reverse: false,
          gridLines: {
            display: false
          }
        },
        animation: {
          animateRotate: false
        }
      },
      data: {
        labels: ['Present', 'Absent', 'Leave'],
        datasets: [
          {
            label: 'Population (millions)',
            backgroundColor: [
              '#00D4BD', '#FDE802', '#6B12B7'
            ],
            data: [10, 17.5, 15],
            borderWidth: 0
          }
        ]
      }
    });
  }

  // Bubble Chart
  // --------------------------------------------------------------------
  if (bubbleChartEx.length) {
    var bubbleExample = new Chart(bubbleChartEx, {
      type: 'bubble',
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          xAxes: [
            {
              display: true,
              gridLines: {
                color: grid_line_color,
                zeroLineColor: grid_line_color
              },
              ticks: {
                stepSize: 10,
                min: 0,
                max: 140,
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
                stepSize: 100,
                min: 0,
                max: 400,
                fontColor: labelColor
              }
            }
          ]
        },
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
        }
      },
      data: {
        animation: {
          duration: 10000
        },
        datasets: [
          {
            backgroundColor: primaryColorShade,
            borderColor: primaryColorShade,
            data: [
              {
                x: 20,
                y: 74,
                r: 10
              }
            ]
          },
          {
            backgroundColor: warningColorShade,
            borderColor: warningColorShade,
            data: [
              {
                x: 30,
                y: 72,
                r: 5
              }
            ]
          },
          {
            backgroundColor: primaryColorShade,
            borderColor: primaryColorShade,
            data: [
              {
                x: 10,
                y: 110,
                r: 5
              }
            ]
          },
          {
            backgroundColor: warningColorShade,
            borderColor: warningColorShade,
            data: [
              {
                x: 40,
                y: 110,
                r: 7
              }
            ]
          },
          {
            backgroundColor: warningColorShade,
            borderColor: warningColorShade,
            data: [
              {
                x: 20,
                y: 135,
                r: 6
              }
            ]
          },
          {
            backgroundColor: warningColorShade,
            borderColor: warningColorShade,
            data: [
              {
                x: 10,
                y: 160,
                r: 12
              }
            ]
          },
          {
            backgroundColor: primaryColorShade,
            borderColor: primaryColorShade,
            data: [
              {
                x: 30,
                y: 165,
                r: 7
              }
            ]
          },
          {
            backgroundColor: primaryColorShade,
            borderColor: primaryColorShade,
            data: [
              {
                x: 40,
                y: 200,
                r: 20
              }
            ]
          },
          {
            backgroundColor: primaryColorShade,
            borderColor: primaryColorShade,
            data: [
              {
                x: 90,
                y: 185,
                r: 7
              }
            ]
          },
          {
            backgroundColor: primaryColorShade,
            borderColor: primaryColorShade,
            data: [
              {
                x: 50,
                y: 240,
                r: 7
              }
            ]
          },
          {
            backgroundColor: primaryColorShade,
            borderColor: primaryColorShade,
            data: [
              {
                x: 60,
                y: 275,
                r: 10
              }
            ]
          },
          {
            backgroundColor: primaryColorShade,
            borderColor: primaryColorShade,
            data: [
              {
                x: 70,
                y: 305,
                r: 5
              }
            ]
          },
          {
            backgroundColor: primaryColorShade,
            borderColor: primaryColorShade,
            data: [
              {
                x: 80,
                y: 325,
                r: 4
              }
            ]
          },
          {
            backgroundColor: warningColorShade,
            borderColor: warningColorShade,
            data: [
              {
                x: 50,
                y: 285,
                r: 5
              }
            ]
          },
          {
            backgroundColor: warningColorShade,
            borderColor: warningColorShade,
            data: [
              {
                x: 60,
                y: 235,
                r: 5
              }
            ]
          },
          {
            backgroundColor: warningColorShade,
            borderColor: warningColorShade,
            data: [
              {
                x: 70,
                y: 275,
                r: 7
              }
            ]
          },
          {
            backgroundColor: warningColorShade,
            borderColor: warningColorShade,
            data: [
              {
                x: 80,
                y: 290,
                r: 4
              }
            ]
          },
          {
            backgroundColor: warningColorShade,
            borderColor: warningColorShade,
            data: [
              {
                x: 90,
                y: 250,
                r: 10
              }
            ]
          },
          {
            backgroundColor: warningColorShade,
            borderColor: warningColorShade,
            data: [
              {
                x: 100,
                y: 220,
                r: 7
              }
            ]
          },
          {
            backgroundColor: warningColorShade,
            borderColor: warningColorShade,
            data: [
              {
                x: 120,
                y: 230,
                r: 4
              }
            ]
          },
          {
            backgroundColor: warningColorShade,
            borderColor: warningColorShade,
            data: [
              {
                x: 110,
                y: 320,
                r: 15
              }
            ]
          },
          {
            backgroundColor: warningColorShade,
            borderColor: warningColorShade,
            data: [
              {
                x: 130,
                y: 330,
                r: 7
              }
            ]
          },
          {
            backgroundColor: primaryColorShade,
            borderColor: primaryColorShade,
            data: [
              {
                x: 100,
                y: 310,
                r: 5
              }
            ]
          },
          {
            backgroundColor: primaryColorShade,
            borderColor: primaryColorShade,
            data: [
              {
                x: 110,
                y: 240,
                r: 5
              }
            ]
          },
          {
            backgroundColor: primaryColorShade,
            borderColor: primaryColorShade,
            data: [
              {
                x: 120,
                y: 270,
                r: 7
              }
            ]
          },
          {
            backgroundColor: primaryColorShade,
            borderColor: primaryColorShade,
            data: [
              {
                x: 130,
                y: 300,
                r: 6
              }
            ]
          }
        ]
      }
    });
  }

  // Doughnut Chart
  // --------------------------------------------------------------------
  if (doughnutChartEx.length) {
    var doughnutExample = new Chart(doughnutChartEx, {
      type: 'doughnut',
      options: {
        responsive: true,
        maintainAspectRatio: false,
        responsiveAnimationDuration: 500,
        cutoutPercentage: 60,
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
        }
      },
      data: {
        datasets: [
          {
            labels: ['Tablet', 'Mobile', 'Desktop'],
            data: [10, 10, 80],
            backgroundColor: [successColorShade, warningLightColor, window.colors.solid.primary],
            borderWidth: 0,
            pointStyle: 'rectRounded'
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

  // Scatter Chart
  // --------------------------------------------------------------------
  if (scatterChartEx.length) {
    var scatterExample = new Chart(scatterChartEx, {
      type: 'scatter',
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
        responsiveAnimationDuration: 800,
        title: {
          display: false,
          text: 'Chart.js Scatter Chart'
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
              gridLines: {
                color: grid_line_color,
                zeroLineColor: grid_line_color
              },
              ticks: {
                stepSize: 10,
                min: 0,
                max: 140,
                fontColor: labelColor
              }
            }
          ],
          yAxes: [
            {
              gridLines: {
                color: grid_line_color,
                zeroLineColor: grid_line_color
              },
              ticks: {
                stepSize: 100,
                min: 0,
                max: 400,
                fontColor: labelColor
              }
            }
          ]
        },
        legend: {
          position: 'top',
          align: 'start',
          labels: {
            usePointStyle: true,
            padding: 25,
            boxWidth: 9
          }
        },
        layout: {
          padding: {
            top: -20
          }
        }
      },
      data: {
        datasets: [
          {
            label: 'iPhone',
            data: [
              {
                x: 72,
                y: 225
              },
              {
                x: 81,
                y: 270
              },
              {
                x: 90,
                y: 230
              },
              {
                x: 103,
                y: 305
              },
              {
                x: 103,
                y: 245
              },
              {
                x: 108,
                y: 275
              },
              {
                x: 110,
                y: 290
              },
              {
                x: 111,
                y: 315
              },
              {
                x: 109,
                y: 350
              },
              {

                x: 116,
                y: 340
              },
              {
                x: 113,
                y: 260
              },
              {
                x: 117,
                y: 275
              },
              {
                x: 117,
                y: 295
              },
              {
                x: 126,
                y: 280
              },
              {
                x: 127,
                y: 340
              },
              {
                x: 133,
                y: 330
              }
            ],
            backgroundColor: window.colors.solid.primary,
            borderColor: 'transparent',
            pointBorderWidth: 2,
            pointHoverBorderWidth: 2,
            pointRadius: 5
          },
          {
            label: 'Samsung Note',
            data: [
              {
                x: 13,
                y: 95
              },
              {
                x: 22,
                y: 105
              },
              {
                x: 17,
                y: 115
              },
              {
                x: 19,
                y: 130
              },
              {
                x: 21,
                y: 125
              },
              {
                x: 35,
                y: 125
              },
              {
                x: 13,
                y: 155
              },
              {
                x: 21,
                y: 165
              },
              {
                x: 25,
                y: 155
              },
              {
                x: 18,
                y: 190
              },
              {
                x: 26,
                y: 180
              },
              {
                x: 43,
                y: 180
              },
              {
                x: 53,
                y: 202
              },
              {
                x: 61,
                y: 165
              },
              {
                x: 67,
                y: 225
              }
            ],
            backgroundColor: yellowColor,
            borderColor: 'transparent',
            pointRadius: 5
          },
          {
            label: 'OnePlus',
            data: [
              {
                x: 70,
                y: 195
              },
              {
                x: 72,
                y: 270
              },
              {
                x: 98,
                y: 255
              },
              {
                x: 100,
                y: 215
              },
              {
                x: 87,
                y: 240
              },
              {
                x: 94,
                y: 280
              },
              {
                x: 99,
                y: 300
              },
              {
                x: 102,
                y: 290
              },
              {
                x: 110,
                y: 275
              },
              {
                x: 111,
                y: 250
              },
              {
                x: 94,
                y: 280
              },
              {
                x: 92,
                y: 340
              },
              {
                x: 100,
                y: 335
              },
              {
                x: 108,
                y: 330
              }
            ],
            backgroundColor: successColorShade,
            borderColor: 'transparent',
            pointBorderWidth: 2,
            pointHoverBorderWidth: 2,
            pointRadius: 5
          }
        ]
      }
    });
  }

  // Orders Chart
  // ---------------------------------------------------------------------
  if (orderbarChartEx.length) {
    var maxDataValue = Math.max(...ordersData);
    var steps = 5; // Maximum steps

    function calculateStepSize(maxValue, steps) {
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
      stepSize = calculateStepSize(maxDataValue, steps);
    }

    if (maxDataValue === 0) {
      var totalSteps = steps;
      var maxYValue = stepSize * totalSteps;
    } else {
      var totalSteps = Math.ceil(maxDataValue / stepSize);
      var maxYValue = stepSize * steps;
    }

    var stepSize = calculateStepSize(maxDataValue, steps);

    var barChartExample = new Chart(orderbarChartEx, {
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
                stepSize: stepSize,
                min: 0,
                fontColor: labelColor,
                max: maxYValue
              }
            }
          ]
        }
      },
      data: {
        labels: ordersLabels,
        datasets: [
          {
            data: ordersData,
            barThickness: 35,
            backgroundColor: ['#3333331a', '#3333331a', '#3333331a', '#3333331a', '#3333331a', '#6B12B7'],
            borderColor: 'transparent'
          }
        ]
      }
    });
  }

  // Line AreaChart
  // --------------------------------------------------------------------
  if (lineAreaChartEx.length) {
    new Chart(lineAreaChartEx, {
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
        legend: {
          position: 'top',
          align: 'start',
          labels: {
            usePointStyle: true,
            padding: 25,
            boxWidth: 9
          }
        },
        layout: {
          padding: {
            top: -20,
            bottom: -20,
            left: -20
          }
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
                color: 'transparent',
                zeroLineColor: grid_line_color
              },
              scaleLabel: {
                display: true
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
                color: 'transparent',
                zeroLineColor: grid_line_color
              },
              ticks: {
                stepSize: 100,
                min: 0,
                max: 400,
                fontColor: labelColor
              },
              scaleLabel: {
                display: true
              }
            }
          ]
        }
      },
      data: {
        labels: [
          '7/12',
          '8/12',
          '9/12',
          '10/12',
          '11/12',
          '12/12',
          '13/12',
          '14/12',
          '15/12',
          '16/12',
          '17/12',
          '18/12',
          '19/12',
          '20/12',
          ''
        ],
        datasets: [
          {
            label: 'Africa',
            data: [40, 55, 45, 75, 65, 55, 70, 60, 100, 98, 90, 120, 125, 140, 155],
            lineTension: 0,
            backgroundColor: blueColor,
            pointStyle: 'circle',
            borderColor: 'transparent',
            pointRadius: 0.5,
            pointHoverRadius: 5,
            pointHoverBorderWidth: 5,
            pointBorderColor: 'transparent',
            pointHoverBackgroundColor: blueColor,
            pointHoverBorderColor: window.colors.solid.white
          },
          {
            label: 'Asia',
            data: [70, 85, 75, 150, 100, 140, 110, 105, 160, 150, 125, 190, 200, 240, 275],
            lineTension: 0,
            backgroundColor: blueLightColor,
            pointStyle: 'circle',
            borderColor: 'transparent',
            pointRadius: 0.5,
            pointHoverRadius: 5,
            pointHoverBorderWidth: 5,
            pointBorderColor: 'transparent',
            pointHoverBackgroundColor: blueLightColor,
            pointHoverBorderColor: window.colors.solid.white
          },
          {
            label: 'Europe',
            data: [240, 195, 160, 215, 185, 215, 185, 200, 250, 210, 195, 250, 235, 300, 315],
            lineTension: 0,
            backgroundColor: greyLightColor,
            pointStyle: 'circle',
            borderColor: 'transparent',
            pointRadius: 0.5,
            pointHoverRadius: 5,
            pointHoverBorderWidth: 5,
            pointBorderColor: 'transparent',
            pointHoverBackgroundColor: greyLightColor,
            pointHoverBorderColor: window.colors.solid.white
          }
        ]
      }
    });
  }
});
