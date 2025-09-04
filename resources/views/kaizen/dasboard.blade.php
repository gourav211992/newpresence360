@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <div class="content-header row">
                <div class="content-header-left col-md-4 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Dashboard</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">Dashboard
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-8">
                    <div class="form-group d-flex flex-wrap align-items-center justify-content-sm-end mb-sm-0 mb-1">

                        <div class="row">
                            <div class="col-12 d-flex flex-wrap align-items-center gap-2">

                                <button class="btn btn-dark btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                                    data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>

                                <!-- Download PDF -->
                                <a href="{{ route('improvement.pdf-download') }}" target="_blank" class="btn btn-danger box-shadow-2 btn-sm"><i
                                        data-feather="download"></i> Download Evaluation
                                </a>

                                <!-- Upload Kaizen -->
                                <a href="{{ route('kaizen.create') }}" class="btn btn-primary box-shadow-2 btn-sm"><i
                                        data-feather="upload"></i> Create
                                    Kaizen</a>
                            </div>
                        </div>


                    </div>
                </div>
            </div>

            <div class="content-body dasboardnewbody">


                <section id="chartjs-chart">

                    <div class="row match-height">
                        <div class="col-xl-12 col-md-6 col-12">
                            <div class="row cutomerdardhcrminfo">

                                <div class="col-md-2">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0" id="productivity">0</h4>
                                                    <p class="card-text mb-0">Productivity</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-info">
                                                        <div class="avatar-content">
                                                            <i data-feather="archive" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0" id="quality">0</h4>
                                                    <p class="card-text mb-0">Quality</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-primary">
                                                        <div class="avatar-content">
                                                            <i data-feather="activity" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0" id="cost">0</h4>
                                                    <p class="card-text mb-0">Cost</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-success">
                                                        <div class="avatar-content">
                                                            <i data-feather="check-circle" class="avatar-icon">₹</i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0" id="delivery">0<h4>
                                                            <p class="card-text mb-0">Delivery</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-danger">
                                                        <div class="avatar-content">
                                                            <i data-feather="truck" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0" id="safety">0<h4>
                                                            <p class="card-text mb-0">Safety</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-secondary">
                                                        <div class="avatar-content">
                                                            <i data-feather="alert-triangle" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0" id="moral">0<h4>
                                                            <p class="card-text mb-0">Moral</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-warning">
                                                        <div class="avatar-content">
                                                            <i data-feather="target" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>




                            </div>
                        </div>
                    </div>



                    <div class="row match-height">

                        <div class="col-md-8 col-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Kaizen</h4>
                                        <p class="card-text">No of Kaizen</p>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <canvas id="kaizenBarChart" class="chartjs" data-height="265"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Improvements Engagement</h4>
                                        {{-- <p class="card-text">Info Detail</p> --}}
                                    </div>
                                </div>
                                <div class="card-body">
                                    {{-- <div id="donut-opentask"></div> --}}
                                    <canvas id="donut-opentask" width="300" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card overtimechart">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Top 3 Identifier</h4>
                                    </div>
                                </div>
                                <div class="card-body">

                                    <div class="row align-items-center">


                                        <div class="col-md-12 ">
                                            <table class="table border payrollconfigured customerdataapp text-center"
                                                id="topIdentifiersTable">
                                                <thead>
                                                    <tr>
                                                        <th>S.NO</th>
                                                        <th>Employee</th>
                                                        <th>Kaizen Count</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>


                        <div class="col-md-12">
                            <div class="card overtimechart">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Evaluator - <span id="evaluatorMonth"></span></h4>
                                        <p class="card-text" id="evaluatorNames"></p>
                                    </div>
                                </div>
                                <div class="card-body">

                                    <div class="row align-items-center">


                                        <div class="col-md-12 ">
                                            <table class="table border payrollconfigured customerdataapp"
                                                id="EvaluatorTable">
                                                <thead>
                                                    <tr>
                                                        <th>S.NO</th>
                                                        <th>Description</th>
                                                        <th>Department</th>
                                                        <th>LLevel Merit</th>
                                                        <th>Cost</th>
                                                        <th>Delivery</th>
                                                        <th>Moral</th>
                                                        <th>Innovation</th>
                                                        <th>Quality</th>
                                                        <th>Safety</th>
                                                        <th>Productivity</th>
                                                        <th>Aggregated Score</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>

                    </div>
                </section>
                <!-- ChartJS section end -->

            </div>
        </div>
    </div>
    <!-- END: Content-->
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Date Range</label>
                        <input type="text" id="fp-range" class="form-control flatpickr-range"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" name="date_range"
                            value="{{ request('date_range') }}" />
                    </div>

                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                    <a href="" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            initializeAutocomplete();
            let dateRange = $("#fp-range").val();
            let from_date = null;
            let to_date = null;

            if (dateRange) {
                let dates = dateRange.split(" to ");
                from_date = dates[0];
                to_date = dates[1];
                initializeAutocomplete(from_date, to_date);
            }


            function initializeAutocomplete(from_date = null, to_date = null) {
                const url = "{{ route('kaizen.get-dashboard') }}";

                $.ajax({
                    url: url,
                    method: 'GET',
                    data: {
                        from_date: from_date,
                        to_date: to_date
                    },
                    success: function(data) {
                        console.log("Fetched data:", data);
                        $("#cost").html(data.counts.total_cost_saving_amt);
                        $("#delivery").html(data.counts.delivery_imp_id);
                        $("#moral").html(data.counts.moral_imp_id);
                        $("#productivity").html(data.counts.productivity_imp_id);
                        $("#quality").html(data.counts.quality_imp_id);
                        $("#safety").html(data.counts.safety_imp_id);
                        $("#evaluatorMonth").html(data.rangedate);
                        $("#evaluatorNames").html(data.evaluatorNames);



                        let rows = data.data;
                        $("#EvaluatorTable tbody").empty();

                        function renderCell(value) {
                            if (value && value !== '') {
                                return `<td class="text-success"><b>${value ?? ''}</b></td>`;
                            }
                            return `<td>-</td>`;
                        }

                        $.each(rows, function(i, row) {
                            let newRow = `
                            <tr>
                                <td>${i+1}</td>
                                <td class="text-dark fw-bolder">
                                    <h6 class="font-small-2">
                                        <span class="text-danger">Problem:</span> ${row.problem}
                                    </h6>
                                    <h6 class="font-small-2">
                                        <span class="text-success">Counter Measure:</span> ${row.countermeasure}
                                    </h6>
                                </td>
                                <td><span class="badge rounded-pill bg-primary">${row.department}</span></td>
                                <td>${row.designation}</td>
                                ${renderCell(row.cost)}
                                ${renderCell(row.delivery)}
                                ${renderCell(row.moral)}
                                ${renderCell(row.innovation)}
                                ${renderCell(row.quality)}
                                ${renderCell(row.safety)}
                                ${renderCell(row.productivity)}
                                <td>${row.score}</td>
                            </tr>
                            `;
                            $("#EvaluatorTable tbody").append(newRow);
                        });


                        // topIdentifiers

                        let topIdentifiers = data.topIdentifiers;
                        $("#topIdentifiersTable tbody").empty();
                        $.each(topIdentifiers, function(i, identifier) {
                            let newRow = `
                            <tr>
                                <td>${i+1}</td>
                                <td><span class="badge rounded-pill border border-primary text-primary">${identifier.name}</span></td>
                                <td><span class="badge rounded-pill text-white" style="background: linear-gradient(45deg, #6a11cb, #2575fc);">${identifier.count}</span></td>
                            </tr>
                            `;
                            $("#topIdentifiersTable tbody").append(newRow);
                        });

                        // Bar chart code js
                        const ctx = document.getElementById('kaizenBarChart').getContext('2d');
                        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                        gradient.addColorStop(0, "rgba(106, 17, 203, 0.9)");
                        gradient.addColorStop(1, "rgba(37, 117, 252, 0.9)");

                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: data.chart.labels,
                                datasets: [{
                                    label: 'No of Kaizen',
                                    data: data.chart.values,
                                    backgroundColor: gradient,
                                    borderRadius: 10
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        enabled: true
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                }
                            }
                        });

                        var labels = [];
                        var values = [];
                        var labelMap = {
                            'productivity_imp_id': 'Productivity',
                            'quality_imp_id': 'Quality',
                            'moral_imp_id': 'Moral',
                            'delivery_imp_id': 'Delivery',
                            'cost_imp_id': 'Cost',
                            'safety_imp_id': 'Safety',
                        };
                        $.each(data.counts, function(key, value) {
                           if(key!='total_cost_saving_amt'){
                               labels.push(labelMap[key] || key);
                               values.push(value);
                           }
                        });
                        donutChart(labels, values);

                    },
                    error: function(xhr) {
                        console.error('Error while fetching data:', xhr.responseText);
                        alert('An error occurred while fetching data.');
                    }
                });
            }

            function donutChart(labels, values, centerText = '') {
                const ctx = document.getElementById('donut-opentask').getContext('2d');

                const centerTextPlugin = {
                    id: 'centerTextPlugin',
                    beforeDraw(chart) {
                        if (!centerText) return;
                        const {
                            width,
                            height,
                            ctx
                        } = chart;
                        ctx.save();

                        const fontSize = (height / 114).toFixed(2);
                        ctx.font = fontSize + "em sans-serif";
                        ctx.textBaseline = "middle";
                        ctx.fillStyle = "#000"; // text color

                        const textX = width / 2;
                        const textY = height / 2;

                        ctx.textAlign = "center";
                        ctx.fillText(centerText, textX, textY);
                        ctx.restore();
                    }
                };

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: ['#4caf50', '#2196f3', '#ff9800', '#9c27b0', '#f44336',
                                '#555555'
                            ],
                            borderColor: '#333333',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                enabled: true
                            }
                        }
                    },
                    plugins: [centerTextPlugin] // Add custom plugin here
                });
            }
        });
      
    </script>


    <script src="{{ asset('app-assets/vendors/js/charts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('app-assets/vendors/js/charts/chart.min.js') }}"></script>
    <script src="{{ asset('app-assets/js/scripts/charts/chart-chartjs-expense.js') }}"></script>
    <script src="{{ asset('app-assets/js/scripts/charts/chart-kaizen.js') }}"></script>
@endsection
