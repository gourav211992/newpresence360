@extends('layouts.app')

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
                            <h2 class="content-header-title float-start mb-0">
                                {{ $customer->company_name ? $customer->company_name : '' }}</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('crm.home') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">Prospects
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7">
                    <div class="form-group d-flex flex-wrap align-items-center justify-content-sm-end mb-sm-0 mb-1">

                        <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0 me-1"><i
                                data-feather="arrow-left-circle"></i> Back</button>

                    </div>
                </div>
            </div>

            <div class="content-body dasboardnewbody">


                <section id="chartjs-chart">

                    <div class="row match-height">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Customer Infomation</h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center pt-sm-2 pt-1">
                                        <div class="col-md-3 col-6 mb-sm-0 mb-1">
                                            <div class="mail-message">
                                                <div class="customer-infoicon">
                                                    <i data-feather='dollar-sign'></i>
                                                </div>
                                                <h5 class="card-text fw-bolder mb-0">Customer Value</h5>
                                                <p class="card-text">
                                                    {{ App\Helpers\Helper::currencyFormat($customer->sales_figure, 'display') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-sm-0 mb-1">
                                            <div class="mail-message">

                                                <div class="customer-infoicon">
                                                    <i data-feather='phone'></i>
                                                </div>
                                                <h5 class="card-text fw-bolder mb-0">Key Contact</h5>
                                                <p class="card-text">{{ $customer->contact_person }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-sm-0 mb-1">
                                            <div class="mail-message">


                                                <div class="customer-infoicon">
                                                    <i data-feather='map-pin'></i>
                                                </div>
                                                <h5 class="card-text fw-bolder mb-0">Address</h5>
                                                <p class="card-text">{{ $customer->customer_address }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 ps-sm-1 ps-0">
                                            <div class="mail-message">
                                                <div class="customer-infoicon">
                                                    <i data-feather='search'></i>
                                                </div>
                                                <h5 class="card-text fw-bolder mb-0">Lead Status</h5>
                                                <select class="discussions-select" name="lead_status"
                                                    onchange="updateLeadStatus(this.value)"
                                                    style="border: none;outline: none;box-shadow: none;color: #6e6b7b;width: 125px;font-size: 13px;">
                                                    <option value=""
                                                        {{ $customer->lead_status == '' ? 'selected' : '' }}>Select</option>
                                                    @forelse($meetingStatuses as $status)
                                                        <option value="{{ $status->alias }}"
                                                            {{ $customer->lead_status == $status->alias ? 'selected' : '' }}>
                                                            {{ $status->title }}</option>
                                                    @empty
                                                    @endforelse
                                                </select>

                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row match-height">
                        {{-- Supply Split Graph --}}
                        <div class="col-md-4 col-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Supply Split</h4>
                                    </div>
                                    <div class="dropdown d-flex align-items-center">
                                        <div class="newcolortheme cursor-pointer" data-bs-toggle="modal"
                                            data-bs-target="#rescdule">
                                            <i data-feather='file-text' class="me-25"></i> Add New
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body mt-2">
                                    <div class="row align-items-center">
                                        <div class="col-md-12 mb-1">
                                            <div id="donut-chart-prospects"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card overtimechart">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Contact Log</h4>
                                    </div>
                                    <div class="dropdown d-flex align-items-center">
                                        <p class="mb-0 text-end font-small-2 me-1"><a class="mb-0 text-primary"
                                                href="{{ route('notes.index', ['customer_id' => $customer->id]) }}"><i
                                                    data-feather='eye'></i> View All</a></p>
                                        <a href="{{ route('notes.create') }}">
                                            <div class="newcolortheme cursor-pointer">
                                                <i data-feather='file-text' class="me-25"></i> Add Notes
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body bg-light">
                                    <div class="actual-databudgetinfo shadow-none mt-1 rounded-0"
                                        style="max-height: 370px; overflow-y: scroll;">
                                        @include('crm.partials.contact-log', [
                                            'diaries' => $diariesData['latestDiaries'],
                                        ])
                                    </div>


                                </div>
                            </div>
                        </div>

                        {{-- Need to know section --}}
                        <div class="col-md-4">
                            @include('crm.partials.need-to-know', [
                                'feedbacks' => $feedbacks,
                            ])
                        </div>
                    </div>
                </section>
                <!-- ChartJS section end -->
            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection

@section('modals')
    @include('crm.modals.supply-split', [
        'customer' => $customer,
        'splitData' => $splitData,
    ])

    @include('crm.modals.need-to-know-modal', [
        'questions' => $questions,
        'customer' => $customer,
    ])
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>
    <script src="{{ asset('/app-assets/vendors/js/charts/chart.min.js') }}"></script>
    <script src="{{ asset('/app-assets/js/scripts/charts/crm-prospects.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
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

        var chartData = @json($supplySplitData['supplyPartners']);
        var supplySplitTitle = chartData.map(item => item.name);
        var supplySplitPercentage = chartData.map(item => item.percentage);
        var supplySplitColorCode = chartData.map(item => item.color_code);

        function updateLeadStatus(value) {
            $.ajax({
                headers: {
                    'X-CSRF-Token': $('meta[name=_token]').attr('content')
                },
                url: "{{ url('crm/prospects/update-status/' . $customer->id) }}",
                type: 'POST',
                cache: false,
                data: {
                    'status': value
                },
                datatype: 'html',
                success: function($response) {
                    Swal.fire("Success!", $response.message, "success");
                },
                error: function(xhr, status, error) {
                    Swal.fire(
                        "Info!",
                        $error,
                        "warning"
                    );
                }
            });
        }
    </script>
@endsection
