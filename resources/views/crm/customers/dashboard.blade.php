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
                            <h2 class="content-header-title float-start mb-0">Customer Dashboard</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('crm.home') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">Dashboard
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7">
                    <div class="form-group d-flex flex-wrap align-items-center justify-content-sm-end mb-sm-0 mb-1">
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
                                {{ Request::get('date_filter') == 'ytd' ? 'checked' : '' }} id="ytd" />
                            <label class="btn btn-outline-primary bg-white" for="ytd">YTD</label>
                        </div>
                        <a href="{{ route('customers.dashboard') }}"><button
                                class="btn btn-warning box-shadow-2 btn-sm me-1"><i data-feather="refresh-cw"></i>
                                Clear</button></a>
                        <a href="{{ route('customers.index') }}" class="btn btn-primary box-shadow-2 btn-sm"><i
                                data-feather="users"></i>
                            View All</a>
                    </div>
                </div>
            </div>

            <div class="content-body dasboardnewbody">


                <section id="chartjs-chart">

                    <div class="row match-height">
                        <div class="col-xl-12 col-md-6 col-12">
                            <div class="row cutomerdardhcrminfo">

                                <div class="col-md-3">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0">{{ $salesSummary['totalSalesValue'] }}</h4>
                                                    <p class="card-text mb-0">Total Account Value</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-info">
                                                        <div class="avatar-content">
                                                            <i data-feather="dollar-sign" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- <div class="d-flex align-items-end justify-content-between">
                                            <h6>Since {{ $date }}</h6>
                                            <p class="text-danger mb-0"><i data-feather="trending-down"></i> 47%</p>
                                        </div> --}}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0">5</h4>
                                                    <p class="card-text mb-0">Total NCR’s</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-primary">
                                                        <div class="avatar-content">
                                                            <i data-feather="alert-triangle" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- <div class="d-flex align-items-end justify-content-between">
                                            <h6>Since {{ $date }}</h6>
                                            <p class="text-danger mb-0"><i data-feather="trending-down"></i> 77%</p>
                                        </div> --}}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0">{{ $customerCount }}</h4>
                                                    <p class="card-text mb-0">Active Customers</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-success">
                                                        <div class="avatar-content">
                                                            <i data-feather="users" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- <div class="d-flex align-items-end justify-content-between">
                                            <h6>Since {{ $date }}</h6>
                                            <p class="text-danger mb-0"><i data-feather="trending-down"></i> 37%</p>
                                        </div> --}}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card card-statistics">
                                        <div class="card-body statistics-body">
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0">{{ $salesSummary['budgetProgress'] }}%<h4>
                                                            <p class="card-text mb-0">Sales Target Progress</p>
                                                </div>
                                                <div>
                                                    <div class="avatar bg-light-danger">
                                                        <div class="avatar-content">
                                                            <i data-feather="target" class="avatar-icon"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- <div class="d-flex align-items-end justify-content-between">
                                            <h6>Since {{ $date }}</h6>
                                            <p class="text-success mb-0"><i data-feather="trending-up"></i> 87%</p>
                                        </div> --}}
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="row match-height">
                        <div class="col-md-5 col-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Sales vs Budget</h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="configured-locasect">
                                        <div class="row mb-3 mt-1">
                                            <div class="col-md-3 col-6">
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
                                                            {{ $salesSummary['totalAchievementValue'] }}</h2>
                                                        <p class="card-text">Sales</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
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
                                                            {{ $salesSummary['totalTargetValue'] }}</h2>
                                                        <p class="card-text">Budget</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <canvas class="line-chart-ex4" style="height: 250px"></canvas>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4 col-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Top 5 Customers / All Others Split</h4>
                                    </div>
                                </div>
                                <div class="card-body mt-2">
                                    <div class="row align-items-center">
                                        <div class="col-md-12 mb-1">
                                            <div id="top-customer-donut-chart"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>




                        <div class="col-md-3">
                            <div class="card overtimechart">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Latest NCRs</h4>
                                    </div>
                                    <div class="dropdown d-flex align-items-center">
                                        <p class="mb-0 text-end font-small-2 me-1"><a class="mb-0 text-primary"
                                                href="{{ route('notes.index') }}"><i data-feather='eye'></i> View All</a>
                                        </p>
                                    </div>
                                </div>
                                <div class="card-body bg-light">
                                    <div class="actual-databudgetinfo shadow-none mt-1 rounded-0"
                                        style="max-height: 330px; overflow-y: scroll;">
                                        <div class="card mb-75">

                                            <div class="card-body mail-message-wrapper p-1">
                                                <div class="mail-message">
                                                    <h5 class="card-text fw-bolder">7/7/24</h5>
                                                    <p class="card-text">Sealy NSW</p>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="card mb-75">

                                            <div class="card-body mail-message-wrapper p-1">
                                                <div class="mail-message">
                                                    <h5 class="card-text fw-bolder">2/7/24</h5>
                                                    <p class="card-text">Dry Seals</p>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="card mb-75">

                                            <div class="card-body mail-message-wrapper p-1">
                                                <div class="mail-message">
                                                    <h5 class="card-text fw-bolder">7/7/24</h5>
                                                    <p class="card-text">Sealy NSW</p>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="card mb-75">

                                            <div class="card-body mail-message-wrapper p-1">
                                                <div class="mail-message">
                                                    <h5 class="card-text fw-bolder">2/7/24</h5>
                                                    <p class="card-text">Dry Seals</p>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="card mb-75">

                                            <div class="card-body mail-message-wrapper p-1">
                                                <div class="mail-message">
                                                    <h5 class="card-text fw-bolder">7/7/24</h5>
                                                    <p class="card-text">Sealy NSW</p>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="card mb-75">

                                            <div class="card-body mail-message-wrapper p-1">
                                                <div class="mail-message">
                                                    <h5 class="card-text fw-bolder">2/7/24</h5>
                                                    <p class="card-text">Dry Seals</p>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="card mb-75">

                                            <div class="card-body mail-message-wrapper p-1">
                                                <div class="mail-message">
                                                    <h5 class="card-text fw-bolder">7/7/24</h5>
                                                    <p class="card-text">Sealy NSW</p>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="card mb-75">

                                            <div class="card-body mail-message-wrapper p-1">
                                                <div class="mail-message">
                                                    <h5 class="card-text fw-bolder">2/7/24</h5>
                                                    <p class="card-text">Dry Seals</p>
                                                </div>
                                            </div>

                                        </div>

                                    </div>


                                </div>
                            </div>
                        </div>



                        <div class="col-md-6">
                            <div class="card overtimechart">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Top Customers</h4>
                                    </div>

                                </div>
                                <div class="card-body">

                                    <div class="row align-items-center">
                                        <div class="col-md-12 px-0">
                                            <div class="configured-locasect ms-1">
                                                <div class="row mb-2">
                                                    <div class="col-4">
                                                        <div class="row mt-sm-2">
                                                            <div class="col-md-3 col-2">
                                                                <div class="avatar bg-light-primary m-0">
                                                                    <div class="avatar-content">
                                                                        <i data-feather="users"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-9 col-10">
                                                                <h2 class="fw-bolder text-primary">
                                                                    {{ $topCustomersData['limit'] }}</h2>
                                                                <p class="card-text">Top Customers</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class="row mt-2">
                                                            <div class="col-md-2 col-2">
                                                                <div class="avatar bg-light-success m-0">
                                                                    <div class="avatar-content">
                                                                        <i data-feather="dollar-sign"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-9 col-10">
                                                                <h2 class="fw-bolder text-success">
                                                                    {{ $topCustomersData['totalTopSales'] }}</h2>
                                                                <p class="card-text" style="line-height: 16px">Sales from
                                                                    Top Customers</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-3">
                                                        <div class="row mt-2 mb-sm-0 mb-1">
                                                            <div class="col-md-3 col-2">
                                                                <div class="avatar bg-light-danger m-0">
                                                                    <div class="avatar-content">
                                                                        <i data-feather="dollar-sign"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-9 col-10">
                                                                <h2 class="fw-bolder text-danger">
                                                                    {{ $topCustomersData['totalSalesValue'] }}</h2>
                                                                <p class="card-text">Total Sales</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <div class="col-md-12 ">
                                            <table class="table border payrollconfigured customerdataapp">
                                                <thead>
                                                    <tr>
                                                        <th>Unit</th>
                                                        <th>Target</th>
                                                        <th>Achieved</th>
                                                        <th>Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($topCustomersData['topSalesData'] as $topSalesData)
                                                        <tr>
                                                            <td class="text-dark fw-bolder">
                                                                <strong>{{ $topSalesData->customer->company_name }}</strong>
                                                            </td>
                                                            <td><span
                                                                    class="badge rounded-pill badge-light-secondary">{{ isset($topSalesData->customerTarget->target_value) ? App\Helpers\Helper::currencyFormat($topSalesData->customerTarget->target_value, 'display') : 0 }}</span>
                                                            </td>
                                                            <td><span
                                                                    class="badge rounded-pill badge-light-secondary">{{ $topSalesData->total_sale_value ? App\Helpers\Helper::currencyFormat($topSalesData->total_sale_value, 'display') : 0 }}</span>
                                                            </td>
                                                            <td><span class="badge rounded-pill badge-light-primary me-1">
                                                                    @php
                                                                        $balanceValue = abs(
                                                                            ($topSalesData->customerTarget
                                                                                ->target_value ??
                                                                                0) -
                                                                                ($topSalesData->total_sale_value ?? 0),
                                                                        );
                                                                    @endphp

                                                                    {{ App\Helpers\Helper::currencyFormat($balanceValue, 'display') }}

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
                        </div>


                        <div class="col-md-6">
                            <div class="card overtimechart">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Bottom Customers</h4>
                                    </div>

                                </div>
                                <div class="card-body">

                                    <div class="row align-items-center">
                                        <div class="col-md-12 px-0">
                                            <div class="configured-locasect ms-1">
                                                <div class="row mb-2">
                                                    <div class="col-4">
                                                        <div class="row mt-sm-2">
                                                            <div class="col-md-3 col-2">
                                                                <div class="avatar bg-light-primary m-0">
                                                                    <div class="avatar-content">
                                                                        <i data-feather="users"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-9 col-10">
                                                                <h2 class="fw-bolder text-primary">
                                                                    {{ $bottomCustomersData['bottomCustomerlimit'] }}</h2>
                                                                <p class="card-text">Bottom Customers</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class="row mt-2">
                                                            <div class="col-md-2 col-2">
                                                                <div class="avatar bg-light-success m-0">
                                                                    <div class="avatar-content">
                                                                        <i data-feather="dollar-sign"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-9 col-10">
                                                                <h2 class="fw-bolder text-success">
                                                                    {{ $bottomCustomersData['totalBottomSales'] }}</h2>
                                                                <p class="card-text" style="line-height: 16px">Sales from
                                                                    Bottom Customers</p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-3">
                                                        <div class="row mt-2 mb-sm-0 mb-1">
                                                            <div class="col-md-3 col-2">
                                                                <div class="avatar bg-light-danger m-0">
                                                                    <div class="avatar-content">
                                                                        <i data-feather="dollar-sign"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-9 col-10">
                                                                <h2 class="fw-bolder text-danger">
                                                                    {{ $bottomCustomersData['totalSalesValue'] }}</h2>
                                                                <p class="card-text">Total Sales</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <div class="col-md-12 ">
                                            <table class="table border payrollconfigured customerdataapp">
                                                <thead>
                                                    <tr>
                                                        <th>Unit</th>
                                                        <th>Target</th>
                                                        <th>Achieved</th>
                                                        <th>Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($bottomCustomersData['bottomSalesData'] as $bottomSalesData)
                                                        <tr>
                                                            <td class="text-dark fw-bolder">
                                                                <strong>{{ $bottomSalesData->customer->company_name }}</strong>
                                                            </td>
                                                            <td><span
                                                                    class="badge rounded-pill badge-light-secondary">{{ isset($bottomSalesData->customerTarget->target_value) ? App\Helpers\Helper::currencyFormat($bottomSalesData->customerTarget->target_value, 'display') : 0 }}</span>
                                                            </td>
                                                            <td><span
                                                                    class="badge rounded-pill badge-light-secondary">{{ $bottomSalesData->total_sale_value ? App\Helpers\Helper::currencyFormat($bottomSalesData->total_sale_value, 'display') : 0 }}</span>
                                                            </td>
                                                            <td><span class="badge rounded-pill badge-light-primary me-1">
                                                                    @php
                                                                        $balanceValue = abs(
                                                                            ($bottomSalesData->customerTarget
                                                                                ->target_value ??
                                                                                0) -
                                                                                ($bottomSalesData->total_sale_value ??
                                                                                    0),
                                                                        );
                                                                    @endphp

                                                                    {{ App\Helpers\Helper::currencyFormat($balanceValue, 'display') }}

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
                        </div>

                    </div>
                </section>
                <!-- ChartJS section end -->

            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection
@section('scripts')
    <script src="{{ asset('/app-assets/vendors/js/charts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('/app-assets/vendors/js/charts/chart.min.js') }}"></script>
    <script src="{{ asset('/app-assets/js/scripts/charts/crm-customer-charts.js') }}"></script>
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

        var salesLabels = Object.values(@json($salesSummary['saleGraphData']['labels']));
        var salesOrderSummary = Object.values(@json($salesSummary['saleGraphData']['salesOrderSummary']));
        var salesCustomerTarget = Object.values(@json($salesSummary['saleGraphData']['customerTarget']));

        var top5CustomersData = @json($top5CustomersData['topProspectsSplitByIndustry']);
        var top5CustomerSalesPrc = top5CustomersData.map(item => item.sales_percentage);
        var top5CustomerCategories = top5CustomersData.map(item => item.industry);
        var top5CustomerColorCode = top5CustomersData.map(item => item.color_code);
        // console.log(top5CustomerSalesPrc,top5CustomerCategories);
    </script>
@endsection
