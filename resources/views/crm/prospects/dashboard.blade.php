@extends('layouts.app')

@section('styles')
    {{-- Owl Crowsel --}}
    <link rel="stylesheet" href="{{ asset('app-assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('app-assets/css/owl.theme.default.min.css') }}">
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
                            <h2 class="content-header-title float-start mb-0">Prospect Dashboard</h2>
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
                                {{ Request::get('date_filter') == 'ytd' ? 'checked' : '' }} id="clear" />
                            <label class="btn btn-outline-primary bg-white" for="clear">YTD</label>
                        </div>
                        <a href="{{ route('prospects.dashboard') }}"><button
                                class="btn btn-warning box-shadow-2 btn-sm me-1 mb-sm-0 mb-sm-50"><i
                                    data-feather="refresh-cw"></i> Clear</button></a>
                        <a href="{{ route('prospects.index') }}"
                            class="btn btn-dark box-shadow-2 btn-sm me-1 mb-sm-0 mb-sm-50"><i data-feather="file-text"></i>
                            View All</a>
                        <a href="{{ route('prospects.index', ['status' => App\Helpers\ConstantHelper::WON]) }}"
                            class="btn btn-primary box-shadow-2 btn-sm mb-sm-0 mb-sm-50"><i data-feather="users"></i> Won
                            Customers</a>
                    </div>
                </div>
            </div>

            <div class="content-body dasboardnewbody">

                <section id="chartjs-chart">
                    <div class="owl-carousel owl-theme cutomerdardhcrminfo">
                        @forelse($prospectsData['statusData'] as $key => $value)
                            <div class="item">
                                <div class="card card-statistics">
                                    <div class="card-body statistics-body">
                                        <div class="d-flex flex-row justify-content-between">
                                            <div class="my-auto">
                                                <h4 class="fw-bolder mb-0">{{ $value->prospects_count }}</h4>
                                                <p class="card-text mb-0">{{ $value->title }}</p>
                                            </div>
                                            <div>
                                                <div class="avatar bg-light-info">
                                                    <div class="avatar-content">
                                                        <i data-feather="{{ $value->icon ? $value->icon : 'dollar-sign' }}"
                                                            class="avatar-icon"
                                                            style="color: {{ $value->color_code }};"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @empty
                        @endforelse
                    </div>
                    <div class="row match-height">
                        <div class="col-md-5">
                            <div class="card overtimechart">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Top Prospects</h4>
                                    </div>
                                    <a href={{ route('notes.create') }}>
                                        <div class="newcolortheme cursor-pointer">
                                            <i data-feather='file-text' class="me-25"></i> Add Notes
                                        </div>
                                    </a>

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
                                                                    {{ $prospectsData['limit'] }}</h2>
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
                                                                    {{ $prospectsData['salesFigureSum'] ? App\Helpers\Helper::currencyFormat($prospectsData['salesFigureSum'], 'display') : 0 }}
                                                                </h2>
                                                                <p class="card-text" style="line-height: 16px">Value from
                                                                    Top Prospects</p>
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
                                                        <th>Customer Name</th>
                                                        <th>Industry Name</th>
                                                        <th>Sales Figure</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($prospectsData['topProspects'] as $value)
                                                        <tr>
                                                            <td class="text-dark fw-bolder">
                                                                <strong>{{ isset($value->company_name) ? $value->company_name : '-' }}</strong>
                                                            </td>
                                                            <td><span
                                                                    class="badge rounded-pill badge-light-secondary">{{ isset($value->industry->name) ? $value->industry->name : '-' }}</span>
                                                            </td>
                                                            <td><span
                                                                    class="badge rounded-pill badge-light-secondary">{{ $value->sales_figure }}</span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>



                        <div class="col-md-4 col-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Prospect Split by Status</h4>
                                    </div>
                                </div>
                                <div class="card-body mt-2">
                                    <div class="row align-items-center">
                                        <div class="col-md-12 mb-1">
                                            <div id="donut-chart-customer"></div>
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
                                                href="diary.html"><i data-feather='eye'></i> View All</a></p>
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
                                        <h4 class="card-title">Prospects by Pipeline</h4>
                                    </div>

                                </div>
                                <div class="card-body">

                                    <div class="row align-items-center">
                                        <div class="col-md-7">
                                            <div id="chart"></div>
                                        </div>
                                        <div class="col-md-5 devices-major-info">
                                            @forelse($prospectsData['statusData'] as $key => $value)
                                                @if ($value->prospects_count > 0)
                                                    <div class="d-flex justify-content-between mt-2 mb-1">
                                                        <div class="d-flex align-items-center">
                                                            <i data-feather="check-circle" class="font-medium-2"
                                                                style="color:{{ $value->color_code }}"></i>
                                                            <span class="fw-bold ms-75 me-25">{{ $value->title }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="badge rounded-pill"
                                                                style="color:{{ $value->color_code }};background-color:rgba(115, 103, 240, 0.12);">{{ $value->prospects_count }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            @empty
                                            @endforelse

                                        </div>

                                    </div>


                                </div>
                            </div>
                        </div>



                        <div class="col-md-6">
                            <div class="card overtimechart">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Top Lost Prospects</h4>
                                    </div>
                                    <div class="dropdown d-flex align-items-center">
                                        <p class="mb-0 text-end font-small-2 me-1"><a class="mb-0 text-primary"
                                                href="{{ route('prospects.index', ['status' => App\Helpers\ConstantHelper::LOST]) }}"><i
                                                    data-feather='eye'></i> View All</a></p>
                                    </div>
                                </div>
                                <div class="card-body">

                                    <div class="row align-items-center">


                                        <div class="col-md-12 ">
                                            <table class="table border payrollconfigured customerdataapp">
                                                <thead>
                                                    <tr>
                                                        <th>Customer Name</th>
                                                        <th>Value</th>
                                                        <th>Loss Reason</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($prospectsData['lostProspects'] as $value)
                                                        <tr>
                                                            <td class="text-dark fw-bolder">
                                                                <strong>{{ isset($value->company_name) ? $value->company_name : '-' }}</strong>
                                                            </td>
                                                            <td><span
                                                                    class="badge rounded-pill badge-light-secondary">{{ $value->sales_figure }}</span>
                                                            </td>
                                                            <td>{{ ucfirst($value->lead_status) }}</td>
                                                        </tr>
                                                    @empty
                                                    @endforelse

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
    <script src="{{ asset('/app-assets/js/scripts/owl.carousel.js') }}"></script>
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>
    <script src="{{ asset('/app-assets/vendors/js/charts/chart.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="{{ asset('/app-assets/js/scripts/charts/crm-prospects.js') }}"></script>
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
        $(document).ready(function() {
            var owl = $('.owl-carousel');
            owl.owlCarousel({
                margin: 15,
                nav: false,
                loop: false,
                responsive: {
                    0: {
                        items: 1
                    },
                    600: {
                        items: 3
                    },
                    1000: {
                        items: 5
                    }
                }
            })
        })

        // Prospects by Pipeline
        var chartData = @json($prospectsData['statusData']);
        var diariesStatusTitle = chartData.map(item => item.title);
        var diariesSalesPercentage = chartData.map(item => item.sales_percentage);
        var diariesColorCode = chartData.map(item => item.color_code);

        // Prospect Split by Status
        var prospectsStatusCount = chartData
            .filter(item => item.prospects_count > 0)
            .map(item => item.prospects_count);

        var prospectsColorCode = chartData
            .filter(item => item.prospects_count > 0)
            .map(item => item.color_code);

        var totalSalesFigure = "{{ $prospectsData['totalSalesFigure'] }}";
    </script>
@endsection
