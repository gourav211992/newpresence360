@extends('layouts.app')
@section('styles')
    <style type="text/css">
        #map {
            width: 100%;
            height: 550px;
            border: 10px solid #fff;
            box-shadow: 0 0px 20px rgba(0, 0, 0, 0.1);
        }

        .gm-ui-hover-effect {
            display: none !important;
        }
    </style>
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDcAUGu5A8te4ZMlIhVtBduoCNWLrQfObY&libraries=places"></script>
        
        
@endsection
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <div class="content-header row">
                <div class="content-header-left col-md-6 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Land Overview</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ url('/land') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">Land Overview
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body dasboardnewbody">
                <div class="row match-height">
                    <div class="col-md-12">
                        <div class="card card-statistics new-cardbox">
                            <div class="card-header newheader align-items-start">
                                <div>
                                    <h4 class="card-title">Plots</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">

                                            <div id="map"></div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-header row">
                    <div class="content-header-left col-md-6 mb-2">
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-2">
                        <form id="dateRangeForm" action="{{ route('land.dashboard') }}" method="GET">
                            <div class="form-group breadcrumb-right">
                                <div id="reportrange" class="col-md-5"
                                    style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display: flex; align-items: center;">
                                    <i class="glyphicon glyphicon-calendar fa fa-calendar" style="margin-right: 10px;"></i>
                                    <span id="date-range"></span>
                                </div>
                            </div>
                            <input type="hidden" name="start_date" id="start_date">
                            <input type="hidden" name="end_date" id="end_date">
                        </form>
                    </div>
                </div>

                <!-- ChartJS section start -->
                <section id="chartjs-chart">
                    <div class="row match-height">
                        <div class="col-md-12">
                            <div class="card card-statistics new-cardbox">
                                <div class="card-body statistics-body">
                                    <div class="row">
                                        <div class="col-xl-3 col-sm-6 col-12 mb-2 mb-xl-0">
                                            <div class="d-flex flex-row">
                                                <div class="avatar bg-light-primary me-2">
                                                    <div class="avatar-content">
                                                        <i data-feather="trending-up" class="avatar-icon"></i>
                                                    </div>
                                                </div>
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0">{{ $active_leases }}</h4>
                                                    <p class="card-text font-small-3 mb-0">Active Leases</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-sm-6 col-12 mb-2 mb-sm-0">
                                            <div class="d-flex flex-row">
                                                <div class="avatar bg-light-danger me-2">
                                                    <div class="avatar-content">
                                                        <i data-feather="box" class="avatar-icon"></i>
                                                    </div>
                                                </div>
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0">{{ $expiring_leases }}</h4>
                                                    <p class="card-text font-small-3 mb-0">Expiring Leases</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-sm-6 col-12 mb-2 mb-xl-0">
                                            <div class="d-flex flex-row">
                                                <div class="avatar bg-light-info me-2">
                                                    <div class="avatar-content">
                                                        <i data-feather="users" class="avatar-icon"></i>
                                                    </div>
                                                </div>
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0">{{ $expired_leases }}</h4>
                                                    <p class="card-text font-small-3 mb-0">Expired Leases</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-sm-6 col-12">
                                            <div class="d-flex flex-row">
                                                <div class="avatar bg-light-success me-2">
                                                    <div class="avatar-content">
                                                        <i class="fa fa-inr avatar-icon fa-2x"></i>

                                                </div>
                                                </div>
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0">
                                                        {{ App\Helpers\Helper::formatIndianNumber($lease_revenue_summary) }}
                                                    </h4>
                                                    <p class="card-text font-small-3 mb-0">Leases Revenue Summery</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row match-height">
                        <div class="col-md-8">
                            <div class="card card-statistics new-cardbox">
                                <div class="card-header newheader align-items-start">
                                    <div>
                                        <h4 class="card-title">Lease Expiry Report</h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <section id="basic-datatable">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card">

                                                    <div class="table-responsive">
                                                        <table class="datatables-basic table loanapplicationlist">
                                                            <thead>
                                                                <tr>
                                                                    <th>Lease Id</th>
                                                                    <th>Tenant Name</th>
                                                                    <th>Start Date</th>
                                                                    <th>End Date</th>
                                                                    <th>Expiry Day</th>
                                                                    <th hidden>Renewal Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($leases as $lease)
                                                                    <tr>
                                                                        <td class="fw-bolder text-dark">
                                                                            {{ $lease->document_no }}
                                                                        </td>
                                                                        <td class="fw-bolder text-dark">
                                                                            {{ $lease->customer->company_name }}
                                                                        </td>
                                                                        <td>{{ \Carbon\Carbon::parse($lease->lease_start_date)->format('d-m-Y') }}</td>
                                                                        <td>{{ \Carbon\Carbon::parse($lease->lease_end_date)->format('d-m-Y') }}</td>
                                                                        </td>
                                                                        <td>
                                                                            @if (!empty($lease->lease_end_date))
                                                                                @php
                                                                                    $expiryDate = \Carbon\Carbon::parse($lease->lease_end_date);
                                                                                    $daysToExpiry = now()->diffInDays($expiryDate, false);
                                                                                @endphp
                                                                                @if ($daysToExpiry > 0)
                                                                                    <span class="text-success">{{ $daysToExpiry }} days remaining</span>
                                                                                @elseif ($daysToExpiry === 0)
                                                                                    <span class="text-warning">Expires today</span>
                                                                                @else
                                                                                    <span class="text-danger">Expired {{ abs($daysToExpiry) }} days ago</span>
                                                                                @endif
                                                                            @else
                                                                                <span class="text-muted">N/A</span>
                                                                            @endif
                                                                        </td>
                                                                        <td hidden>
                                                                            <span
                                                                                class="badge rounded-pill badge-light-{{ $lease->customer->status === 'active' ? 'warning' : ($lease->customer->status === 'inactive' ? 'danger' : 'success') }} badgeborder-radius">
                                                                                {{ ucfirst($lease->customer->status) }}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </section>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Revenue Report</h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div id="donut-opentask"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row match-height">
                        <div class="col-md-12">
                            <div class="card card-statistics new-cardbox">
                                <div class="card-header newheader align-items-start">
                                    <div>
                                        <h4 class="card-title">Payment and Recovery</h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <section id="basic-datatable">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="card">

                                                    <div class="table-responsive">
                                                        <table
                                                            class="datatables-basic table myrequesttablecbox loanapplicationlist">
                                                            <thead>
                                                                <tr>
                                                                    <th>Tenant Name</th>
                                                                    <th>Amount Due</th>
                                                                    <th>Due Date</th>
                                                                    <th>Status</th>
                                                                    <th hidden>Renewal Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($recoveries as $recovery)
                                                                    <tr>
                                                                        <td>{{ $recovery->lease->customer->company_name }}</td>
                                                                        <td>{{ $recovery->due_amount }}</td>
                                                                        <td>{{ \Carbon\Carbon::parse($recovery->due_date)->format('d-m-Y') }}</td>
                                                                        <td>
                                                                            <span
                                                                            class="badge rounded-pill badge-light-{{ $recovery->status === 'paid' ? 'success' : 'warning' }} badgeborder-radius">
                                                                            {{ ucfirst($recovery->status) }}
                                                                        </span>
                                                                        </td>
                                                                        <td hidden>
                                                                            <span
                                                                                class="badge rounded-pill badge-light-{{ $recovery->status === 1 ? 'warning' : ($recovery->status === 0 ? 'danger' : 'success') }} badgeborder-radius">
                                                                                {{ ucfirst($recovery->status === 1 ?"active":"inactive") }}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>

                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <!-- ChartJS section end -->

        </div>
    </div>
    <!-- END: Content-->
@endsection
@section('scripts')
    <!-- BEGIN: Page Vendor JS-->
    <script src="{{ asset('/app-assets/vendors/js/charts/apexcharts.min.js') }}"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    

    <!-- END: Page Vendor JS-->
    <script>
        window.routes = {
            landDashboard: @json(route('land.dashboard')),
            landGetRevenue: @json(route('land.getDashboardRevenueReport'))
        };

        const chart = function() {
            var chartColors = {
                donut: {
                    series6: '#ea5455', // Define your own colors
                    series7: '#826BF8',
                    series8: '#FFD700'
                }
            };

            document.addEventListener('DOMContentLoaded', function() {
                var donutChartElroletask = document.querySelector('#donut-opentask');

                if (donutChartElroletask) {
                    // Fetch chart data from Laravel backend
                    fetch(window.routes.landGetRevenue)
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.json();
                        })
                        .then(data => {
                            // Update the series data with the fetched data
                            var donutChartConfigroletask = {
                                chart: {
                                    height: 300,
                                    type: 'donut'
                                },
                                legend: {
                                    show: true,
                                    position: 'bottom'
                                },
                                labels: ['Revenue Collected', 'Overdue Payments', 'Pending Payments'],
                                series: [data.revenue, data.overdue, data.pending], // Use the data from the backend
                                colors: [
                                    chartColors.donut.series6,
                                    chartColors.donut.series7,
                                    chartColors.donut.series8
                                ],
                                dataLabels: {
                                    enabled: true,
                                    // formatter: function(val) {
                                    //     return parseInt(val);
                                    // }
                                    formatter: function(val, opts) {
                                        // Return the raw value from the series instead of converting to percentage
                                        return opts.w.globals.series[opts.seriesIndex] || 0;
                                    }
                                },
                                plotOptions: {
                                    pie: {
                                        donut: {
                                            labels: {
                                                show: true,
                                                name: {
                                                    fontSize: '2rem',
                                                    fontFamily: 'Montserrat'
                                                },
                                                value: {
                                                    fontSize: '1rem',
                                                    fontFamily: 'Montserrat',
                                                    // formatter: function(val) {
                                                    //     return parseInt(val);
                                                    // }
                                                    formatter: function(val, opts) {
                                                        // Return the actual value from the series
                                                        return opts.w.globals.series[opts
                                                            .seriesIndex] || 0;
                                                    }
                                                },
                                                total: {
                                                    show: true,
                                                    fontSize: '1.5rem',
                                                    label: 'Total Revenue',
                                                    formatter: function(w) {
                                                        // Calculate and return the total sum of the series
                                                        return w.config.series[0] || 0;;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                responsive: [{
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

                            // Initialize the chart with the fetched data
                            if (typeof donutChartElroletask !== undefined && donutChartElroletask !==
                                null) {
                                var donutChartroletask = new ApexCharts(donutChartElroletask,
                                    donutChartConfigroletask);
                                donutChartroletask.render();
                            }
                        })
                        .catch(error => console.error('Error fetching chart data:', error));
                };
            });
        };

        const daterange = function() {
            $('#reportrange').daterangepicker({
                startDate: moment(), // Start date is today
                endDate: moment().add(29, 'days'),
            }, function(start, end) {
                // Update the date range display with the selected dates
                $('#date-range').text(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
            });

            // Set the initial date range display
            const initialStartDate = moment().format('MM/DD/YYYY');
            const initialEndDate = moment().add(29, 'days').format('MM/DD/YYYY');
            $('#date-range').text(initialStartDate + ' - ' +
                initialEndDate); // Ensure the initial dates are set correctly

            // Event listener for the Apply button
            $('.applyBtn').click(function() {
                // Get the selected date range
                var startDate = $('#reportrange').data('daterangepicker').startDate.format('MM/DD/YYYY');
                var endDate = $('#reportrange').data('daterangepicker').endDate.format('MM/DD/YYYY');

                $('#date-range').text(startDate + ' - ' + endDate);
                // Log the dates to the console (for debugging)
                console.log('Start Date:', startDate);
                console.log('End Date:', endDate);
                $('#start_date').val(startDate); // Start date
                $('#end_date').val(endDate);

                $('#dateRangeForm').submit();

            });
        };

        const init = function() {
            chart();
            daterange();
        };

        init();

        // Google Map Integration
        window.addEventListener('load', function() {


            initMap(@json($locations));
            // initMap(locations);
        });

        function initMap(locations) {
            var plotPolygons = [];
            var latitudes = [];
            var longitudes = [];
            var markers = [];

            // Default to Shillong, India if no coordinates are provided
            var shillongLat = 25.5788;
            var shillongLng = 91.8933;

            // Use Shillong's coordinates as initial center on load
            var initialLat = shillongLat;
            var initialLng = shillongLng;
            var initialZoom = 10;

            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: initialZoom,
                center: {
                    lat: initialLat,
                    lng: initialLng
                }
            });

            // Legend element to show Occupied/Vacant status
            const legend = document.createElement('div');
            legend.style.display = 'none'; // Initially hide the legend
            legend.style.position = 'absolute';
            legend.style.top = '4px';
            legend.style.right = '8px';
            legend.style.backgroundColor = '#2e4733';
            legend.style.color = '#fff';
            legend.style.padding = '10px';
            legend.style.borderRadius = '5px';
            legend.style.zIndex = '1';
            legend.innerHTML = `
            <div style="display: flex; align-items: center; margin-right: 5px;">
                <div style="width: 20px; height: 20px; background-color: #00ff00; border: 1px solid #fff; margin-right: 5px;"></div>
                Occupied
            </div>
            <div style="display: flex; align-items: center;">
                <div style="width: 20px; height: 20px; border: 1px solid #fff; margin-right: 5px;"></div>
                Vacant
            </div>`;
            map.controls[google.maps.ControlPosition.TOP_RIGHT].push(legend);

            var tooltip = new google.maps.InfoWindow();
            var plotInfoWindow = new google.maps.InfoWindow();

            locations.forEach(function(location) {
                if (location.latitude && !isNaN(location.latitude) &&
                    location.longitude && !isNaN(location.longitude)) {

                    var marker = new google.maps.Marker({
                        position: {
                            lat: parseFloat(location.latitude),
                            lng: parseFloat(location.longitude)
                        },
                        map: map,
                        title: location.name,
                    });

                    markers.push(marker);

                    // Geofencing Logic
                    if (location.locations && Array.isArray(location.locations)) {
                        var geofenceCoords = location.locations.map(function(coord) {
                            return {
                                lat: parseFloat(coord.latitude),
                                lng: parseFloat(coord.longitude)
                            };
                        });

                        var geofencePolygon = new google.maps.Polygon({
                            paths: geofenceCoords,
                            strokeColor: '#FF0000',
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: '',
                            fillOpacity: 0.35
                        });

                        geofencePolygon.setMap(map);

                        // Define the bounds for the geofence area
                        var bounds = new google.maps.LatLngBounds();
                        geofenceCoords.forEach(function(coord) {
                            bounds.extend(coord);
                        });
                    }

                    location.plots.forEach(function(plot) {
                        if (plot.locations && Array.isArray(plot.locations) && plot.locations.length > 2) {
                            var plotPaths = plot.locations.map(coord => {
                                const lat = parseFloat(coord.latitude);
                                const lng = parseFloat(coord.longitude);
                                return (!isNaN(lat) && !isNaN(lng)) ? {
                                    lat,
                                    lng
                                } : null;
                            }).filter(coord => coord !== null);

                            if (plotPaths.length > 2) {
                                var fillColor = plot.filled ? "#00ff00" : "none";
                                var strokeColor = "#417657";

                                var plotPolygon = new google.maps.Polygon({
                                    path: plotPaths,
                                    strokeColor: strokeColor,
                                    strokeOpacity: 1.0,
                                    strokeWeight: 5,
                                    fillColor: plot.filled ? fillColor : "none",
                                    fillOpacity: plot.filled ? 0.5 : 0.0,
                                    map: map
                                });

                                google.maps.event.addListener(plotPolygon, "click", function(event) {
                                    plotInfoWindow.setContent(
                                        `<div>
                                    <strong>Plot name:</strong> ${plot.plot_name || "N/A"}<br>
                                    ${plot.approvalStatus !=='approval_not_required' ?'<strong>Status: '+plot.approvalStatus: ''}</strong><br>
                                    <strong>Area:</strong> ${plot.plot_area || "N/A"}<br>
                                    <strong>Dimensions:</strong> ${plot.dimension || "N/A"}<br>
                                    <strong>Type:</strong> ${plot.type_of_usage || "N/A"}
                                </div>`
                                    );
                                    plotInfoWindow.setPosition(event.latLng);
                                    plotInfoWindow.open(map);
                                });
                            }
                        }
                    });

                    google.maps.event.addListener(marker, "mouseover", function() {
                        tooltip.setContent(
                            `<div>
                        <strong style="display: block; text-align: center;">${location.name}</strong><br>
                        ${location.approvalStatus !=='approval_not_required' ?'<strong>Status: '+location.approvalStatus: ''}</strong><br>
                        Dimension: ${location.dimension || "No dimension available"}<br>
                        Area/Unit: ${location.plot_area || "No survey available"} ${location.area_unit}<br>
                        Handover Date: ${formatDateToDDMMYYYY(location.handoverdate) || "No handover available"}<br>
                        Land Valuation: ${location.land_valuation || "No land valuation available"}<br>
                    </div>`
                        );
                        tooltip.open(map, marker);
                    });

                    google.maps.event.addListener(marker, "mouseout", function() {
                        tooltip.close();
                    });

                    // Zoom into geofencing area with specific zoom level and keep other areas visible
                    google.maps.event.addListener(marker, "click", function() {
                        legend.style.display = 'flex'; // Show legend on single marker click
                        map.setCenter(bounds.getCenter()); // Center on the geofence area
                        map.setZoom(
                            15); // Adjust zoom to show the area while keeping other elements visible
                    });

                    // Hide legend and reset zoom on double-click
                    google.maps.event.addListener(marker, "dblclick", function() {
                        map.setCenter({
                            lat: 25.5788,
                            lng: 91.8933
                        }); // Reset to Shillong
                        map.setZoom(10);
                        legend.style.display = 'none';
                    });

                    // // Show legend on single-click and center map on marker
                    // marker.addListener("click", function() {
                    //     legend.style.display = 'flex'; // Show the legend on single-click
                    //     map.setCenter(marker.getPosition());
                    //     map.setZoom(8);
                    // });

                    // // Hide legend on double-click without hiding the marker
                    // marker.addListener("dblclick", function() {
                    //     map.setCenter({
                    //         lat: shillongLat,
                    //         lng: shillongLng
                    //     });
                    //     map.setZoom(initialZoom);
                    //     legend.style.display = 'none'; // Hide the legend on double-click
                    // });
                }
            });
        }
        $(function () {
    var dt_table = $('.myrequesttablecbox.loanapplicationlist');

    if (dt_table.length) {
        var dt_instance = dt_table.DataTable({
            order: [], // Disable default sorting
            columnDefs: [
                { orderable: false, targets: [-1] } // Disable sorting on the last column
            ],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 5,
            lengthMenu: [5, 10, 20, 25],
            buttons: [
                {
                    extend: 'collection',
                    className: 'btn btn-outline-secondary dropdown-toggle',
                    text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + 'Export',
                    buttons: [
                        {
                            extend: 'csv',
                            text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'CSV',
                            className: 'dropdown-item',
                            filename: 'Tenant_Due_Report',
                            exportOptions: {
                                columns: function (idx) {
                                    return idx !== 4; // Exclude hidden 'Renewal Status' column
                                },
                                format: {
                                    header: function (data, columnIdx) {
                                        switch (columnIdx) {
                                            case 0: return 'Tenant Name';
                                            case 1: return 'Amount Due';
                                            case 2: return 'Due Date';
                                            case 3: return 'Status';
                                            default: return data;
                                        }
                                    }
                                }
                            }
                        },
                        {
                            extend: 'excel',
                            text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
                            className: 'dropdown-item',
                            filename: 'Tenant_Due_Report',
                            exportOptions: {
                                columns: function (idx) {
                                    return idx !== 4; // Exclude hidden 'Renewal Status' column
                                },
                                format: {
                                    header: function (data, columnIdx) {
                                        switch (columnIdx) {
                                            case 0: return 'Tenant Name';
                                            case 1: return 'Amount Due';
                                            case 2: return 'Due Date';
                                            case 3: return 'Status';
                                            default: return data;
                                        }
                                    }
                                }
                            }
                        }
                    ],
                    init: function (api, node) {
                        $(node).removeClass('btn-secondary');
                        setTimeout(function () {
                            $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                        }, 50);
                    }
                }
            ],
            language: {
                paginate: {
                    previous: '&nbsp;',
                    next: '&nbsp;'
                }
            }
        });

        // Update the label for the table
        $('div.head-label').html('<h6 class="mb-0">Tenant Due Report</h6>');
    }
});

</script>
@endsection
