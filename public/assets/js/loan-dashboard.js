/*=========================================================================================
    File Name: dashboard-ecommerce.js
    Description: dashboard ecommerce page content with Apexchart Examples
    ----------------------------------------------------------------------------------------
    Item Name: Vuexy  - Vuejs, HTML & Laravel Admin Dashboard Template
    Author: PIXINVENT
    Author URL: http://www.themeforest.net/user/pixinvent
==========================================================================================*/

//------------ Loan Analytics Chart ------------
//---------------------------------------------
function filterLoanAnalytics(
    time = "",
    startDate = "",
    endDate = "",
    loanType = ""
) {
    let donutChartroletask = null;
    var donutChartElroletask = document.querySelector("#donut-opentask");

    var chartColors = {
        donut: {
            series6: "#ea5455", // Define your own colors
            series7: "#826BF8",
            series8: "#28c76f",
        },
    };

    var homeLoansElement = document.querySelector(".la-home-loan"),
        vehicleLoansElement = document.querySelector(".la-vehicle-loan"),
        termLoansElement = document.querySelector(".la-term-loan");

    const params = new URLSearchParams();

    if (time) params.append("time", time);
    if (startDate) params.append("startDate", startDate);
    if (endDate) params.append("endDate", endDate);
    if (loanType) params.append("loanType", loanType);

    var windowUrl = window.routes.loanAnalytics;

    const url = `${windowUrl}?${params.toString()}`;

    if (donutChartElroletask) {
        fetch(url)
            .then((response) => response.json())
            .then((data) => {
                // update data
                homeLoansElement.textContent = data.home_loans;
                vehicleLoansElement.textContent = data.vehicle_loans;
                termLoansElement.textContent = data.term_loans;

                if (donutChartroletask) {
                    donutChartroletask.destroy(); // Destroy the previous chart instance if it exists
                }
                donutChartElroletask.innerHTML = "";

                // Update the series data with the fetched data
                var donutChartConfigroletask = {
                    chart: {
                        height: 300,
                        type: "donut",
                    },
                    legend: {
                        show: true,
                        position: "bottom",
                    },
                    labels: ["Home", "Vehicle", "Term"],
                    series: [
                        data.home_loans,
                        data.vehicle_loans,
                        data.term_loans,
                    ], // Use the data from the backend
                    colors: [
                        chartColors.donut.series6,
                        chartColors.donut.series7,
                        chartColors.donut.series8,
                    ],
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) {
                            return parseInt(val) + "%";
                        },
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                labels: {
                                    show: true,
                                    name: {
                                        fontSize: "2rem",
                                        fontFamily: "Montserrat",
                                    },
                                    value: {
                                        fontSize: "1rem",
                                        fontFamily: "Montserrat",
                                        formatter: function (val) {
                                            return parseInt(val);
                                        },
                                    },
                                    total: {
                                        show: true,
                                        fontSize: "1.5rem",
                                        label: "Total",
                                        formatter: function (w) {
                                            // Calculate and return the total sum of the series
                                            var total = w.config.series.reduce(
                                                function (a, b) {
                                                    return a + b;
                                                },
                                                0
                                            );
                                            return total;
                                        },
                                    },
                                },
                            },
                        },
                    },
                    responsive: [
                        {
                            breakpoint: 992,
                            options: {
                                chart: {
                                    height: 380,
                                },
                            },
                        },
                        {
                            breakpoint: 576,
                            options: {
                                chart: {
                                    height: 320,
                                },
                                plotOptions: {
                                    pie: {
                                        donut: {
                                            labels: {
                                                show: true,
                                                name: {
                                                    fontSize: "1.5rem",
                                                },
                                                value: {
                                                    fontSize: "1rem",
                                                },
                                                total: {
                                                    fontSize: "1.5rem",
                                                },
                                            },
                                        },
                                    },
                                },
                            },
                        },
                    ],
                };

                donutChartroletask = new ApexCharts(
                    donutChartElroletask,
                    donutChartConfigroletask
                );
                donutChartroletask.render();
            })
            .catch((error) =>
                console.error("Error fetching chart data:", error)
            );
    }
}

//------------ KPI Chart ------------
//---------------------------------------------
function filterKPI(time = "", startDate = "", endDate = "", loanType = "") {
    var $goalStrokeColor2 = "#51e5a8";
    var $strokeColor = "#ebe9f1";
    var $textHeadingColor = "#5e5873";

    var goalOverviewChartOptions;
    var goalOverviewChart;
    var goalOverviewChartData;

    goalOverviewChart = document.querySelector(
        "#goal-overview-radial-bar-chart"
    );

    var totalLoansElement = document.querySelector(".kpi-total-loans"),
        recoveryElement = document.querySelector(".kpi-recovery"),
        settlementElement = document.querySelector(".kpi-settlement");

    const params = new URLSearchParams();

    if (time) params.append("time", time);
    if (startDate) params.append("startDate", startDate);
    if (endDate) params.append("endDate", endDate);
    if (loanType) params.append("loanType", loanType);

    var windowUrl = window.routes.loanKpi;
    const url = `${windowUrl}?${params.toString()}`;

    if (goalOverviewChart) {
        fetch(url)
            .then((response) => response.json())
            .then((data) => {
                // update data
                totalLoansElement.textContent = data.total_loans;
                recoveryElement.textContent = data.recovery_percentage;
                settlementElement.textContent = data.settlement_percentage;

                if (goalOverviewChartData) {
                    goalOverviewChartData.destroy(); // Destroy the previous chart instance if it exists
                }
                goalOverviewChart.innerHTML = "";

                // Update the series data with the fetched data
                goalOverviewChartOptions = {
                    chart: {
                        height: 245,
                        type: "radialBar",
                        sparkline: {
                            enabled: true,
                        },
                        dropShadow: {
                            enabled: true,
                            blur: 3,
                            left: 1,
                            top: 1,
                            opacity: 0.1,
                        },
                    },
                    colors: [$goalStrokeColor2],
                    plotOptions: {
                        radialBar: {
                            offsetY: -10,
                            startAngle: -150,
                            endAngle: 150,
                            hollow: {
                                size: "77%",
                            },
                            track: {
                                background: $strokeColor,
                                strokeWidth: "50%",
                            },
                            dataLabels: {
                                name: {
                                    show: false,
                                },
                                value: {
                                    color: $textHeadingColor,
                                    fontSize: "2.86rem",
                                    fontWeight: "600",
                                },
                            },
                        },
                    },
                    fill: {
                        type: "gradient",
                        gradient: {
                            shade: "dark",
                            type: "horizontal",
                            shadeIntensity: 0.5,
                            gradientToColors: [window.colors.solid.success],
                            inverseColors: true,
                            opacityFrom: 1,
                            opacityTo: 1,
                            stops: [0, 100],
                        },
                    },
                    series: [data.recovery_percentage],
                    stroke: {
                        lineCap: "round",
                    },
                    grid: {
                        padding: {
                            bottom: 30,
                        },
                    },
                };
                goalOverviewChartData = new ApexCharts(
                    goalOverviewChart,
                    goalOverviewChartOptions
                );
                goalOverviewChartData.render();
            })
            .catch((error) =>
                console.error("Error fetching chart data:", error)
            );
    }
}

//------------ Loan Summary Chart ------------
//---------------------------------------------
function filterLoanSummery(
    time = "",
    startDate = "",
    endDate = "",
    loanType = ""
) {
    var chartWrapper = $(".chartjs"),
        leavebarChartEx = $(".leavebar-chart-ex"),
        totalLoansElement = document.querySelector(".ls-total-loans"),
        totalDisbursementElement = document.querySelector(".ls-disbursement"),
        totalRecoveryElement = document.querySelector(".ls-recovery"),
        totalSettlementElement = document.querySelector(".ls-settlement");

    // Color Variables
    var tooltipShadow = "rgba(0, 0, 0, 0.25)",
        labelColor = "#6e6b7b",
        grid_line_color = "rgba(200, 200, 200, 0.2)"; // RGBA color helps in dark layout

    const params = new URLSearchParams();

    if (time) params.append("time", time);
    if (startDate) params.append("startDate", startDate);
    if (endDate) params.append("endDate", endDate);
    if (loanType) params.append("loanType", loanType);

    var windowUrl = window.routes.loanSummary;
    const url = `${windowUrl}?${params.toString()}`;

    // Use fetch with a GET request, passing filterValue as a query parameter
    fetch(url)
        .then((response) => response.json())
        .then((data) => {
            var fetchedData = [
                data.home_loans,
                data.vehicle_loans,
                data.term_loans,
            ]; // Example fetched data
            var maxY = Math.max(...fetchedData);

            // Update the content of the h4 element
            totalLoansElement.textContent = data.total_loans;
            totalDisbursementElement.textContent = data.disbursement;
            totalRecoveryElement.textContent = data.recovery;
            totalSettlementElement.textContent = data.settlement;

            var leavebarChartExample = new Chart(leavebarChartEx, {
                type: "bar",
                options: {
                    elements: {
                        rectangle: {
                            borderWidth: 2,
                            borderSkipped: "bottom",
                        },
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    responsiveAnimationDuration: 500,
                    legend: {
                        display: false,
                    },
                    tooltips: {
                        // Updated default tooltip UI
                        shadowOffsetX: 1,
                        shadowOffsetY: 1,
                        shadowBlur: 8,
                        shadowColor: tooltipShadow,
                        backgroundColor: window.colors.solid.white,
                        titleFontColor: window.colors.solid.black,
                        bodyFontColor: window.colors.solid.black,
                    },
                    scales: {
                        xAxes: [
                            {
                                display: true,
                                gridLines: {
                                    display: true,
                                    color: grid_line_color,
                                    zeroLineColor: grid_line_color,
                                },
                                scaleLabel: {
                                    display: false,
                                },
                                ticks: {
                                    fontColor: labelColor,
                                },
                            },
                        ],
                        yAxes: [
                            {
                                display: true,
                                gridLines: {
                                    color: grid_line_color,
                                    zeroLineColor: grid_line_color,
                                },
                                ticks: {
                                    stepSize: 1000000,
                                    min: 0,
                                    max: maxY,
                                    fontColor: labelColor,
                                },
                            },
                        ],
                    },
                },
                data: {
                    labels: ["HL", "VL", "TL"],
                    datasets: [
                        {
                            data: [
                                data.home_loans,
                                data.vehicle_loans,
                                data.term_loans,
                            ],
                            barThickness: 25,
                            backgroundColor: ["#7367F0", "#FF9F43", "#EA5455"],
                            borderColor: "transparent",
                        },
                    ],
                },
            });
        })
        .catch((error) => console.error("Error fetching data:", error));

    if (chartWrapper.length) {
        chartWrapper.each(function () {
            $(this).wrap(
                $(
                    '<div style="height:' +
                        this.getAttribute("data-height") +
                        'px"></div>'
                )
            );
        });
    }
}
