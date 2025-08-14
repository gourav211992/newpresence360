@extends('layouts.app')
@section('styles')
@endsection
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <div class="content-header row">
                <div class="content-header-left col-md-6 col-4 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Dashboard Analytics</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">Loan Dashboard
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-end col-md-6 col-8">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-primary box-shadow-2" data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i data-feather="filter"></i> Filter</button>
                    </div>
                </div>
            </div>

            <div class="content-body dasboardnewbody">

                <!-- ChartJS section start -->
                <section id="chartjs-chart">
                    <div class="row">

                        <div class="col-md-6 col-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Loan Analytics</h4>
                                        <p class="card-text">Info Details</p>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <div id="donut-opentask"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="empluyeenewdashdetaiul">
                                                <div class="holiday-box">
                                                    <div><span>Home Loan</span></div>
                                                    <div>
                                                        <h3 class="la-home-loan"></h3>
                                                        {{-- <h5>Lac</h5> --}}
                                                    </div>
                                                </div>
                                                <div class="holiday-box">
                                                    <div><span>Vehicle Loan</span></div>
                                                    <div>
                                                        <h3 class="la-vehicle-loan"></h3>
                                                        {{-- <h5>Lac</h5> --}}
                                                    </div>
                                                </div>
                                                <div class="holiday-box">
                                                    <div><span>Term Loan</span></div>
                                                    <div>
                                                        <h3 class="la-term-loan"></h3>
                                                        {{-- <h5>Lac</h5> --}}
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
                                        <h4 class="card-title">KPI</h4>
                                        <p class="card-text">Info Details</p>
                                    </div>
                                    <div class="header-right d-flex align-items-center mb-25">
                                        <div class="btn-group new-btn-group">
                                            <input type="radio" class="btn-check" name="kpi-filter"
                                                id="ThisMonthKpiFilter" value="this-month" checked />
                                            <label class="btn btn-outline-primary" for="ThisMonthKpiFilter">This
                                                Month</label>

                                            <input type="radio" class="btn-check" name="kpi-filter"
                                                id="LastMonthKpiFilter" value="last-month" />
                                            <label class="btn btn-outline-primary" for="LastMonthKpiFilter">Last
                                                Month</label>

                                            <input type="radio" class="btn-check" name="kpi-filter" id="Months3KpiFilter"
                                                value="3-month" />
                                            <label class="btn btn-outline-primary" for="Months3KpiFilter">3 Months</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div id="goal-overview-radial-bar-chart" class="my-2"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row border-start text-center mx-0">
                                                <div class="col-12 border-bottom py-1">
                                                    <p class="card-text text-primary mb-0">Total</p>
                                                    <h3 class="fw-bolder text-primary  mb-0 kpi-total-loans">
                                                    </h3>
                                                </div>
                                                <div class="col-12 border-bottom py-1">
                                                    <p class="card-text text-muted mb-0">Recovery</p>
                                                    <h3 class="fw-bolder mb-0 kpi-recovery"></h3>
                                                </div>
                                                <div class="col-12 py-1">
                                                    <p class="card-text text-muted mb-0">Settlement</p>
                                                    <h3 class="fw-bolder mb-0 kpi-settlement"></h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>


                        <div class="col-md-12 col-12">
                            <div class="card">
                                <div class="card-header newheader d-flex justify-content-between align-items-start">
                                    <div class="header-left">
                                        <h4 class="card-title">Loan Summary</h4>
                                        <p class="card-text">Info Details</p>
                                    </div>
                                    <div class="header-right d-flex align-items-center mb-25">
                                        <div class="btn-group new-btn-group">
                                            <input type="radio" class="btn-check" name="loan-summary-filter"
                                                value="this-month" id="ThisMonthLoanSummary" checked />
                                            <label class="btn btn-outline-primary" for="ThisMonthLoanSummary">This
                                                Month</label>

                                            <input type="radio" class="btn-check" name="loan-summary-filter"
                                                value="last-month" id="LastMonthLoanSummary" />
                                            <label class="btn btn-outline-primary" for="LastMonthLoanSummary">Last
                                                Month</label>

                                            <input type="radio" class="btn-check" name="loan-summary-filter"
                                                value="3-months" id="Months3LoanSummary" />
                                            <label class="btn btn-outline-primary" for="Months3LoanSummary">3
                                                Months</label>

                                            <input type="radio" class="btn-check" name="loan-summary-filter"
                                                value="this-year" id="ThisYearLoanSummary" />
                                            <label class="btn btn-outline-primary" for="ThisYearLoanSummary">This
                                                Years</label>

                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <canvas class="leavebar-chart-ex chartjs" data-height="300"></canvas>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="row regulariandlatepunch">
                                                <div class="col-6 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="avatar bg-light-info mb-1">
                                                                <div class="avatar-content">
                                                                    <i data-feather="box"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="my-auto">
                                                                <h4 class="fw-bolder mb-0 ls-total-loans"></h4>
                                                                <p class="mb-0">Total Loan</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="avatar bg-light-danger mb-1">
                                                                <div class="avatar-content">
                                                                    <i data-feather="archive"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="my-auto">
                                                                <h4 class="fw-bolder mb-0 ls-disbursement"></h4>
                                                                <p class="mb-0">Disbursement</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-1">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="avatar bg-light-info mb-1">
                                                                <div class="avatar-content">
                                                                    <i data-feather="package"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="my-auto">
                                                                <h4 class="fw-bolder mb-0 ls-recovery"></h4>
                                                                <p class="mb-0">Recovery</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6 mb-1">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="avatar bg-light-danger mb-1">
                                                                <div class="avatar-content">
                                                                    <i data-feather="inbox"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="my-auto">
                                                                <h4 class="fw-bolder mb-0 ls-settlement"></h4>
                                                                <p class="mb-0">Settlement</p>
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

                    </div>
                </section>
                <!-- ChartJS section end -->

            </div>
        </div>
    </div>
    <!-- END: Content-->


    <div class="modal modal-slide-in fade filterpopuplabel" id="filterModal" tabindex="-1"
        aria-labelledby="filterModalLabel" aria-hidden="true">
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
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                    </div>

                    <div class="mb-1">
                        <label class="form-label" for="loan-type">Loan Type</label>
                        <select class="form-select" id="loan-type">
                            <option value="" selected>Select</option>
                            <option value="home-loan">Home Loan</option>
                            <option value="vehicle-loan">Vehicle Loan</option>
                            <option value="term-loan">Term Loan</option>
                        </select>
                    </div>

                </div>
                <div class="modal-footer justify-content-start">
                    <button type="button" class="btn btn-primary data-submit mr-1" id="apply-filter">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <!-- BEGIN: Page Vendor JS-->
    <script src="{{ asset('/app-assets/vendors/js/charts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('/app-assets/vendors/js/charts/chart.min.js') }}"></script>
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Page JS-->
    <script src="{{ asset('app-assets/js/scripts/cards/card-advance.js') }}"></script>
    <!-- END: Page JS-->

    <!-- BEGIN: Dashboard Custom Code JS-->
    <script src="{{ asset('assets/js/custom/pages/loan/loan-dashboard.js') }}"></script>
    <!-- END: Dashboard Custom Code JS-->

    <script>
        window.routes = {
            loanAnalytics: @json(route('loan.analytics')),
            loanKpi: @json(route('loan.kpi')),
            loanSummary: @json(route('loan.summary')),

        };
        // main.js
        window.addEventListener('DOMContentLoaded', (event) => {
            filterLoanAnalytics();
            filterKPI();
            filterLoanSummery();

            // Filter KPI
            document.querySelectorAll('input[name="kpi-filter"]').forEach(radio => {
                radio.addEventListener('change', (event) => {
                    console.log(`Radio button changed kpi: ${event.target.value}`);
                    filterKPI(event.target.value);
                });
            });

            // Filter Loan Summary
            document.querySelectorAll('input[name="loan-summary-filter"]').forEach(radio => {
                radio.addEventListener('change', (event) => {
                    console.log(`Radio button changed loan summary: ${event.target.value}`);
                    filterLoanSummery(event.target.value);
                });
            });

            document.getElementById('apply-filter').addEventListener('click', function(event) {
                event.preventDefault(); // Prevent the default form submission

                // Close the modal
                var filterModal = bootstrap.Modal.getInstance(document.getElementById('filterModal'));
                if (filterModal) {
                    filterModal.hide();
                }

                // Get the date range value
                let dateRange = document.getElementById('fp-range').value;

                // Get the loan type value
                let loanType = document.getElementById('loan-type').value;

                // Prepare the data for filtering
                let filterData = {};

                if (dateRange) {
                    // Split the date range into start and end dates
                    let dates = dateRange.split(' to ');
                    filterData.startDate = dates[0];
                    filterData.endDate = dates[1];
                }

                if (loanType) {
                    filterData.loanType = loanType;
                }
                // Send the filter data to the server or apply it to your queries
                applyFilters(filterData);
            });

            function applyFilters(filterData) {
                filterLoanAnalytics("", filterData.startDate, filterData.endDate, filterData.loanType);
                filterKPI("", filterData.startDate, filterData.endDate, filterData.loanType);
                filterLoanSummery("", filterData.startDate, filterData.endDate, filterData.loanType);
            }


        });
    </script>
@endsection
