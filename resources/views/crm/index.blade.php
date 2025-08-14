@extends('layouts.app')
@section('styles')
    {{-- Apex charts css --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/charts/apexcharts.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/css/plugins/charts/chart-apex.css') }}">
@endsection
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Dashboard Analytics</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('crm.home') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">Dashboard-{{ @$user->organization_id }}
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7">
                    <div class="form-group d-flex flex-wrap align-items-center justify-content-sm-end mb-sm-0 mb-1">
                        @if ($userType == 'employee')
                            <div class="dropdown me-1">
                                <div data-bs-toggle="dropdown" class="newcolortheme cursor-pointer">All Team <img
                                        src="{{ asset('/assets/css/down-arrow.png') }}"></div>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="heat-chart-dd">
                                    @if (count($salesTeam) > 0)
                                        @foreach ($salesTeam as $team)
                                            <a class="dropdown-item"
                                                href="{{ route('crm.home', ['sales_team_id' => $team->id]) }}">{{ $team->name }}</a>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="btn-group new-btn-group my-1 my-sm-0 me-1">
                            <input type="radio" class="btn-check" value="today" name="date_filter"
                                {{ Request::get('date_filter') == 'today' ? 'checked' : '' }} id="Today" />
                            <label class="btn btn-outline-primary bg-white" for="Today">Today</label>

                            <input type="radio" class="btn-check" value="week" name="date_filter"
                                {{ Request::get('date_filter') == 'week' ? 'checked' : '' }} id="Week" />
                            <label class="btn btn-outline-primary bg-white" for="Week">Week</label>

                            <input type="radio" class="btn-check" value="month" name="date_filter"
                                {{ Request::get('date_filter') == 'month' ? 'checked' : '' }} id="Months1Account" />
                            <label class="btn btn-outline-primary bg-white" for="Months1Account">This Month</label>

                            <input type="radio" class="btn-check" value="ytd" name="date_filter"
                                {{ Request::get('date_filter') == 'ytd' || Request::get('date_filter') == null ? 'checked' : '' }}
                                id="ytd" />
                            <label class="btn btn-outline-primary bg-white" for="ytd">YTD</label>
                        </div>
                        <a href="{{ route('crm.home') }}"><button class="btn btn-primary box-shadow-2 btn-sm me-1"><i
                                    data-feather="refresh-cw"></i> Clear</button></a>
                        <button class="btn btn-primary box-shadow-2 btn-sm" data-bs-toggle="modal"
                            data-bs-target="#filter"><i data-feather="filter"></i> Filter</button>
                    </div>
                </div>
            </div>

            <div class="content-body dasboardnewbody">
                <section id="chartjs-chart">
                    <div class="row match-height">

                        <div class="col-xl-12 col-md-6 col-12">
                            <div class="card card-statistics">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Sales Summary</h4>
                                        <p class="card-text">As on {{ date('d-m-Y') }}</p>
                                    </div>
                                    <div class="header-right d-flex align-items-center mb-25 mt-1 mt-sm-0">

                                    </div>
                                </div>
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
                                                    <h4 class="fw-bolder mb-0">{{ $salesSummary['totalSalesValue'] }}</h4>
                                                    <p class="card-text font-small-3 mb-0">Total Sales</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-sm-6 col-12 mb-2 mb-sm-0">
                                            <div class="d-flex flex-row">
                                                <div class="avatar bg-light-danger me-2">
                                                    <div class="avatar-content">
                                                        <i data-feather="target" class="avatar-icon"></i>
                                                    </div>
                                                </div>
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0">{{ $salesSummary['budgetProgress'] }}%</h4>
                                                    <p class="card-text font-small-3 mb-0">Budget Progress</p>
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
                                                    <h4 class="fw-bolder mb-0">{{ count($customers) }}</h4>
                                                    <p class="card-text font-small-3 mb-0">Active Customers</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-sm-6 col-12">
                                            <div class="d-flex flex-row">
                                                <div class="avatar bg-light-success me-2">
                                                    <div class="avatar-content">
                                                        <i data-feather="dollar-sign" class="avatar-icon"></i>
                                                    </div>
                                                </div>
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0">{{ $salesSummary['totalProspectsValue'] }}
                                                    </h4>
                                                    <p class="card-text font-small-3 mb-0">Prospect Value</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row match-height">
                        <div class="col-md-6 col-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">View All Closed Business - Prospects Won @if ($currencyMaster)
                                                (in {{ @$currencyMaster->conversion_type }})
                                            @endif
                                        </h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center mt-2">
                                        <div class="col-md-12">
                                            <canvas class="bar-chart-ex chartjs" data-height="265"></canvas>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card overtimechart">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Reminders & To-Do’s</h4>
                                    </div>
                                    <div class="dropdown d-flex align-items-center">
                                        <p class="mb-0 text-end font-small-2 me-1"><a class="mb-0 text-primary"
                                                href="{{ route('notes.index') }}"><i data-feather='eye'></i> View All</a>
                                        </p>
                                        <a href={{ route('notes.create') }}>
                                            <div class="newcolortheme cursor-pointer">
                                                <i data-feather='file-text' class="me-25"></i> Add Notes
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body bg-light">
                                    <div class="actual-databudgetinfo shadow-none mt-1 rounded-0"
                                        style="max-height: 270px; overflow-y: scroll;">
                                        @include('crm.partials.contact-log', [
                                            'diaries' => $meetingSummary['erpDiaries'],
                                        ])

                                    </div>


                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Top 5 Customers / All Others Split</h4>
                                    </div>

                                </div>
                                <div class="card-body mt-2">
                                    <div class="row align-items-center">
                                        @if (count($topCustomersData['topProspectsSplitByIndustry']) > 0)
                                            <div class="col-md-6 mb-1">
                                                <canvas class="doughnut-chart-ex-pros chartjs" data-height="275"></canvas>

                                            </div>
                                            <div class="col-md-6 devices-major-info">
                                                @forelse($topCustomersData['topProspectsSplitByIndustry'] as $data)
                                                    <div class="d-flex justify-content-between mt-1 mb-1">
                                                        <div class="d-flex align-items-center">
                                                            <span
                                                                class="fw-bold ms-75 me-25">{{ $data['industry'] }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="badge rounded-pill badge-light-primary"
                                                                style="color: {{ $data['color_code'] }} !important;">{{ $data['total_sale_value'] }}</span>
                                                        </div>
                                                    </div>
                                                @empty
                                                @endforelse
                                            </div>
                                        @else
                                            <span class="text-danger">No record(s) found</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Sales @if ($currencyMaster)
                                                (in {{ @$currencyMaster->conversion_type }})
                                            @endif
                                        </h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="configured-locasect">
                                        <div class="row mb-2">
                                            <div class="col-md-4 col-6">
                                                <div class="row">
                                                    <div class="col-md-4 col-4">
                                                        <div class="avatar bg-light-primary m-0">
                                                            <div class="avatar-content">
                                                                <i data-feather='trending-up'></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8 col-8">
                                                        <h2 class="fw-bolder text-primary">
                                                            {{ $salesSummary['totalTargetValue'] }}</h2>
                                                        <p class="card-text">Target</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-6">
                                                <div class="row">
                                                    <div class="col-md-4 col-4">
                                                        <div class="avatar bg-light-danger m-0">
                                                            <div class="avatar-content">
                                                                <i data-feather='dollar-sign'></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-8 col-8">
                                                        <h2 class="fw-bolder text-danger">
                                                            {{ $salesSummary['totalAchievementValue'] }}</h2>
                                                        <p class="card-text">Achievement</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <canvas class="line-chart-ex chartjs" data-height="195"></canvas>
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
                        <label class="form-label" for="fp-range">Select Date</label>
                        <input type="text" id="fp-range" name="date_range" value="{{ Request::get('date_range') }}"
                            class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Sales Team.</label>
                        <select class="form-select select2" name="sales_team_id">
                            <option value="" {{ Request::get('sales_team_id') == '' ? 'selected' : '' }}>Select
                            </option>
                            @foreach ($salesTeam as $team)
                                <option value="{{ $team->id }}"
                                    {{ Request::get('sales_team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Customer</label>
                        <select class="form-select select2" name="customer_code">
                            <option value="" {{ Request::get('customer_code') == '' ? 'selected' : '' }}>Select
                            </option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->customer_code }}"
                                    {{ Request::get('customer_code') == $customer->customer_code ? 'selected' : '' }}>
                                    {{ $customer->company_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type"
                            onchange="dropdown('{{ url('crm/get-countries-states') }}/'+this.value, 'type_id', '');">
                            <option value="" {{ Request::get('type') == '' ? 'selected' : '' }}>Select</option>
                            <option value="international" {{ Request::get('type') == 'international' ? 'selected' : '' }}>
                                International</option>
                            <option value="domestic" {{ Request::get('type') == 'domestic' ? 'selected' : '' }}>Domestic
                            </option>
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Country/State</label>
                        <select class="form-select select2" name="type_id[]" id="type_id" multiple>
                            <option value="">Select</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="{{ url('/crm/home') }}"class="btn btn-outline-secondary mr-1">Clear</a>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('/app-assets/vendors/js/charts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('/app-assets/vendors/js/charts/chart.min.js') }}"></script>
    {{-- <script src="{{ asset('/app-assets/js/scripts/charts/chart-chartjs.js') }}"></script> --}}
    {{-- <script src="{{ asset('/app-assets/js/scripts/charts/chart-apex.js') }}"></script> --}}
    <script src="{{ asset('/app-assets/js/scripts/charts/crm-home-chart.js') }}"></script>
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radios = document.querySelectorAll('input[name="date_filter"]');
            const currentUrl = new URL(window.location.href);

            // if (!currentUrl.searchParams.has('date_filter')) {
            //     currentUrl.searchParams.set('date_filter', 'ytd');
            //     window.location.href = currentUrl;
            // }

            radios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    const selectedValue = this.value;

                    currentUrl.searchParams.delete('date_range');
                    currentUrl.searchParams.delete('sales_team_id');
                    currentUrl.searchParams.delete('customer_code');
                    currentUrl.searchParams.set('date_filter', selectedValue);
                    window.location.href = currentUrl;

                });
            });
        });
    </script>
    <script>
        // Sales Graph
        var salesLabels = Object.values(@json($salesSummary['saleGraphData']['labels']));
        var salesOrderSummary = Object.values(@json($salesSummary['saleGraphData']['salesOrderSummary']));
        var salesCustomerTarget = Object.values(@json($salesSummary['saleGraphData']['customerTarget']));

        // View All Closed Business - Prospects Won
        var prospectsLabels = Object.values(@json($meetingSummary['prospectsGraphData']['labels']));
        var prospectsData = Object.values(@json($meetingSummary['prospectsGraphData']['data']));

        // Top 5 Customers / All Others split by industry
        var chartData = @json($topCustomersData['topProspectsSplitByIndustry']);
        var industryLabels = chartData.map(item => item.industry);
        var industryPercentage = chartData.map(item => item.sales_percentage);
        var industryColorCode = chartData.map(item => item.color_code);
    </script>
    <script>
        $(document).ready(function() {
            @if (Request::get('type'))
                dropdown('{{ url('crm/get-countries-states') }}/' + '{{ Request::get('type') }}', 'type_id',
                    @json(Request::get('type_id', [])));
            @endif
        });
    </script>
@endsection
