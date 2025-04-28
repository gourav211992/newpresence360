@extends('layouts.crm')
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
                                <li class="breadcrumb-item active">Dashboard
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-sm-end col-md-7">
                <div class="form-group d-flex flex-wrap align-items-center justify-content-sm-end mb-sm-0 mb-1">
                    @if($userType == 'employee')
                    <div class="dropdown me-1">
                        <div data-bs-toggle="dropdown" class="newcolortheme cursor-pointer">All Team <img
                                src="{{ asset('/assets/css/down-arrow.png')}}"></div>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="heat-chart-dd">
                            @if(count($salesTeam) > 0)
                                @foreach ($salesTeam as $team)
                                    <a class="dropdown-item" href="{{ route('crm.home',['sales_team_id' => $team->id ]) }}" >{{ $team->name }}</a>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endif
                    <div class="btn-group new-btn-group my-1 my-sm-0 me-1">
                        <input type="radio" class="btn-check" value="today" name="date_filter" {{ Request::get('date_filter') == 'today' ? 'checked' : '' }} id="Today" />
                        <label class="btn btn-outline-primary bg-white" for="Today">Today</label>

                        <input type="radio" class="btn-check" value="week" name="date_filter" {{ Request::get('date_filter') == 'week' ? 'checked' : '' }} id="Week" />
                        <label class="btn btn-outline-primary bg-white" for="Week">Week</label>

                        <input type="radio" class="btn-check" value="month" name="date_filter" {{ Request::get('date_filter') == 'month' ? 'checked' : '' }} id="Months1Account" />
                        <label class="btn btn-outline-primary bg-white" for="Months1Account">This Month</label>

                        <input type="radio" class="btn-check" value="clear" name="date_filter" {{ Request::get('date_filter') == 'clear' ? 'checked' : '' }} id="clear" />
                        <label class="btn btn-outline-primary bg-white" for="clear">Clear</label>
                    </div>
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
                                                    <i data-feather="box" class="avatar-icon"></i>
                                                </div>
                                            </div>
                                            <div class="my-auto">
                                                <h4 class="fw-bolder mb-0"><a href="{{ route('customers.index') }}{{ Request::getQueryString() ? '?' . Request::getQueryString() : '' }}">{{ $salesSummary['totalOrderValue'] }}</a></h4>
                                                <p class="card-text font-small-3 mb-0">Order Value</p>
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
                                                <h4 class="fw-bolder mb-0">{{ $salesSummary['customerCount'] }}</h4>
                                                <p class="card-text font-small-3 mb-0">Customer Count</p>
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
                                                <h4 class="fw-bolder mb-0">{{ $salesSummary['newLeadCount'] }}</h4>
                                                <p class="card-text font-small-3 mb-0">New Lead Count</p>
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
                                    <h4 class="card-title">Orders @if($currencyMaster)(in {{ @$currencyMaster->conversion_type }})@endif</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-12">
                                        <canvas class="orderbar-chart-ex chartjs" data-height="265"></canvas>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="card">
                            <div class="card-header newheader d-flex justify-content-between align-items-start">
                                <div class="header-left">
                                    <h4 class="card-title">Sales @if($currencyMaster)(in {{ @$currencyMaster->conversion_type }})@endif</h4>
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
                                                    <h2 class="fw-bolder text-primary">{{ $salesSummary['totalTargetValue'] }}</h2>
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
                                                    <h2 class="fw-bolder text-danger">{{ $salesSummary['totalAchievementValue'] }}</h2>
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

                    <div class="col-md-6 col-12">
                        <div class="card">
                            <div class="card-header newheader d-flex justify-content-between align-items-start">
                                <div class="header-left">
                                    <h4 class="card-title">Meeting Summary</h4>
                                </div>
                                <div class="header-right d-flex align-items-center mb-25">

                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div id="donut-opentask"></div>
                                    </div>
                                    <div class="col-md-6 mt-1 mt-sm-0">
                                        <div class="empluyeenewdashdetaiul crmnewdashcolor">
                                            <div class="holiday-box" style="visibility: hidden"></div>
                                            <div class="holiday-box" style="visibility: hidden"></div>
                                            <div class="holiday-box">
                                                <div><span>Existing</span></div>
                                                <div>
                                                    <h3>{{ $meetingSummary['totalExistingDiaries'] }}</h3>
                                                </div>
                                            </div>
                                            <div class="holiday-box">
                                                <div><span>New/ Prospect</span></div>
                                                <div>
                                                    <h3>{{ $meetingSummary['totalNewDiaries'] }}</h3>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12">
                        <div class="card">
                            <div class="card-header newheader d-flex justify-content-between align-items-start">
                                <div class="header-left">
                                    <h4 class="card-title">Leads</h4>
                                </div>

                            </div>
                            <div class="card-body mt-2">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <div id="donut-apptask"></div>

                                    </div>
                                    <div class="col-md-6 devices-major-info">
                                        @forelse($meetingSummary['meetingStatus'] as $key => $value)
                                            <div class="d-flex justify-content-between mt-2 mb-1">
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="circle" class="font-medium-2" style="color:{{ $value->color_code }}"></i>
                                                    <span class="fw-bold ms-75 me-25">{{ $value->title }}</span>
                                                </div>
                                                <div>
                                                    <span class="badge rounded-pill badge-light-primary">{{ $meetingSummary['chartData']['count'][$value->id] }}</span>
                                                </div>
                                            </div>
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
                                                            <h2 class="fw-bolder text-primary">{{ $topCustomersData['limit'] }}</h2>
                                                            <p class="card-text">Top Customers</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-5 p-0">
                                                    <div class="row mt-2">
                                                        <div class="col-md-2 col-2">
                                                            <div class="avatar bg-light-success m-0">
                                                                <div class="avatar-content">
                                                                    <i data-feather="dollar-sign"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-9 col-10">
                                                            <h2 class="fw-bolder text-success">{{ $topCustomersData['totalTopSales'] }}</h2>
                                                            <p class="card-text" style="line-height: 16px">Sales
                                                                from Top Customers</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-3 p-0">
                                                    <div class="row mt-2 mb-sm-0 mb-1">
                                                        <div class="col-md-3 col-2">
                                                            <div class="avatar bg-light-danger m-0">
                                                                <div class="avatar-content">
                                                                    <i data-feather="dollar-sign"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-9 col-10">
                                                            <h2 class="fw-bolder text-danger">{{ $salesSummary['totalSalesValue'] }}</h2>
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
                                                    <td class="text-dark fw-bolder"><strong>{{ $topSalesData->customer->company_name }}</strong></td>
                                                    <td><span
                                                            class="badge rounded-pill badge-light-secondary">{{ isset($topSalesData->customerTarget->target_value) ? App\Helpers\Helper::currencyFormat($topSalesData->customerTarget->target_value,'display') : 0 }}</span>
                                                    </td>
                                                    <td><span
                                                            class="badge rounded-pill badge-light-secondary">{{ $topSalesData->total_sale_value ? App\Helpers\Helper::currencyFormat($topSalesData->total_sale_value,'display') : 0 }}</span>
                                                    </td>
                                                    <td><span class="badge rounded-pill badge-light-primary me-1">
                                                        @php
                                                            $balanceValue = abs(($topSalesData->customerTarget->target_value ?? 0) - ($topSalesData->total_sale_value ?? 0));
                                                        @endphp
                                                        
                                                            {{ App\Helpers\Helper::currencyFormat($balanceValue,'display') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @empty
                                                    <tr><td class="text-danger text-center" colspan="4"><strong>No record(s) found.</strong></td></tr>
                                                @endforelse

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
                                    <h4 class="card-title">My Notes & Meetings</h4>
                                </div>
                                <div class="dropdown">
                                    <a href={{ route('notes.create') }}>
                                        <div class="newcolortheme cursor-pointer">
                                            <i data-feather='file-text' class="me-25"></i> Add Notes
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body bg-light">
                                <div class="actual-databudgetinfo shadow-none mt-1">
                                    @forelse($meetingSummary['erpDiaries'] as $erpDiary)
                                        <div class="card">
                                            <div class="card-header border-bottom  p-1">
                                                <div class="user-details d-flex justify-content-between align-items-center flex-wrap">
                                                    @php
                                                        $randomColor = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                                                    @endphp
                                                    <div class="me-75" style="width: 38px; height: 38px; border-radius: 50%; background-color: {{ $randomColor }}; color: white; text-align: center; line-height: 38px;">
                                                        {{ strtoupper(substr($erpDiary->customer_name, 0, 1)) }}
                                                    </div>
                                                    <div class="mail-items">
                                                        <h5 class="mb-0 mt-50">{{ $erpDiary->customer_name }}</h5>
                                                        <p class="mb-0">{{ $erpDiary->contact_person }} | {{ $erpDiary->email }} | {{ isset($erpDiary->customer->phone) ? $erpDiary->customer->phone : '-' }}</p>
                                                    </div>
                                                </div>
                                                <div class="mail-meta-item d-flex align-items-center">
                                                    <span class="badge font-small-2 fw-bold rounded-pill {{ $erpDiary->customer_type == 'New' ? 'badge-light-success' : 'badge-light-warning' }} badgeborder-radius">{{ $erpDiary->customer_type }}</span>
                                                </div>
                                            </div>
                                            <div class="card-body mail-message-wrapper p-1">
                                                <div class="mail-message">
                                                    <h5 class="card-text fw-bold">{{ $erpDiary->subject }}</h5>
                                                    <p class="card-text">{!! $erpDiary->description !!}</p>
                                                    <div class="row">
                                                        <div class="col-md-7">
                                                            @if(count($erpDiary->attachments) > 0)
                                                                @foreach($erpDiary->attachments as $attachment)
                                                                    @if($attachment->document_path)
                                                                        @php
                                                                            $extension = App\Helpers\GeneralHelper::checkFileExtension($attachment->document_path);
                                                                        @endphp
                                                                        <a href="{{ url('/').'/'.$attachment->document_path }}" target="_blank">
                                                                            @if($extension == 'image')
                                                                                <img src="{{ url('/').'/'.$attachment->document_path }}" class="me-25" alt="image" height="18">
                                                                            @endif
                                                                            <small class="text-muted fw-bolder">{{ basename($attachment->document_path) }}</small>
                                                                        </a>
                                                                        <br>
                                                                    @endif
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                        <div class="col-md-5">
                                                            <div class="row">
                                                            <div class="col-md-12">
                                                                <div>  
                                                                    <label class="form-label">Created on: </label>
                                                                    <span>{{ $erpDiary->created_at ? App\Helpers\GeneralHelper::dateFormat($erpDiary->created_at) : ''}}</span>
                                                                </div>
                                                            </div>
                                                            <div>  
                                                                <label class="form-label">Created by: </label>
                                                                @if($erpDiary->created_by_type == 'employee')
                                                                    <span>{{ isset($erpDiary->createdByEmployee->name) ? $erpDiary->createdByEmployee->name : '' }}</span>
                                                                @elseif($erpDiary->created_by_type == 'user')
                                                                    <span>{{ isset($erpDiary->createdByUser->name) ? $erpDiary->createdByUser->name : '' }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="card">
                                            <div class="card-body mail-message-wrapper p-1">
                                                <div class="mail-message">
                                                    <p class="card-text text-danger text-center"> No record(s) found. </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>

                                <p class="mb-0 text-end font-small-2"><a class="mb-0 text-primary" href="{{ route('notes.index') }}"><i
                                            data-feather='eye'></i> View All</a></p>
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
                    <input type="text" id="fp-range" name="date_range" value="{{ Request::get('date_range') }}" class="form-control flatpickr-range bg-white"
                        placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                </div>

                <div class="mb-1">
                    <label class="form-label">Sales Team.</label>
                    <select class="form-select select2" name="sales_team_id">
                        <option value="" {{ Request::get('sales_team_id') == "" ? 'selected' : '' }}>Select</option>
                        @foreach($salesTeam as $team)
                            <option value="{{ $team->id }}" {{ Request::get('sales_team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-1">
                    <label class="form-label">Customer</label>
                    <select class="form-select select2" name="customer_code">
                        <option value="" {{ Request::get('customer_code') == "" ? 'selected' : '' }}>Select</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->customer_code}}" {{ Request::get('customer_code') == $customer->customer_code ? 'selected' : '' }}>{{ $customer->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="type" onchange="dropdown('{{ url('crm/get-countries-states') }}/'+this.value, 'type_id', '');">
                        <option value="" {{ Request::get('type') == "" ? 'selected' : '' }}>Select</option>
                        <option value="international" {{ Request::get('type') == "international" ? 'selected' : '' }}>International</option>
                        <option value="domestic" {{ Request::get('type') == "domestic" ? 'selected' : '' }}>Domestic</option>
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
<script src="{{ asset('/app-assets/js/scripts/charts/chart-chartjs.js') }}"></script>
<script src="{{ asset('/app-assets/js/scripts/charts/chart-apex.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const radios = document.querySelectorAll('input[name="date_filter"]');
        
        radios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                const selectedValue = this.value;
                const currentUrl = new URL(window.location.href);

                if (selectedValue == 'clear') {
                    window.location.href = '{{ route('crm.home') }}';
                    // currentUrl.searchParams.delete('date_filter');
                    // currentUrl.searchParams.delete('date_range');
                    // currentUrl.searchParams.delete('sales_team_id');
                    // currentUrl.searchParams.delete('customer_code');
                    // currentUrl.searchParams.delete('type');
                } else {
                    currentUrl.searchParams.delete('date_range');
                    currentUrl.searchParams.delete('sales_team_id');
                    currentUrl.searchParams.delete('customer_code');
                    currentUrl.searchParams.set('date_filter', selectedValue);
                    window.location.href = currentUrl;
                }
                
            });
        });
    });
</script>
<script>
    // Meeting graph
    var totalExistingDiaries = @json($meetingSummary['totalExistingDiaries']);
    var totalNewDiaries = @json($meetingSummary['totalNewDiaries']);
    var totalDiaries = totalExistingDiaries+totalNewDiaries;

    // Sales Graph
    var salesLabels = Object.values(@json($salesSummary['saleGraphData']['labels']));
    var salesOrderSummary = Object.values(@json($salesSummary['saleGraphData']['salesOrderSummary']));
    var salesCustomerTarget = Object.values(@json($salesSummary['saleGraphData']['customerTarget']));
    
    // Order Graph
    var ordersLabels = Object.values(@json($orderSummary['ordersGraphData']['labels']));
    var ordersData = Object.values(@json($orderSummary['ordersGraphData']['data']));

    // Meeting Summary Chart
    var diariesStatusTitle = Object.values(@json($meetingSummary['chartData']['status']));
    var diariesStatusCount = Object.values(@json($meetingSummary['chartData']['count']));
    var diariesColorCode = Object.values(@json($meetingSummary['chartData']['colors']));
    var totalDiariesStatusCount = diariesStatusCount.reduce(function (acc, count) {
        return acc + count;
    }, 0);

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