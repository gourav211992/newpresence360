@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="card border  overflow-hidden">
                        <div class="row">
                            <div class="col-md-12 bg-light border-bottom mb-1 po-reportfileterBox">
                                <div class="row pofilterhead action-button align-items-center">
                                    <div class="col-md-4">
                                        <h3>Land Report</h3>
                                        <p>Apply the Basic Filter</p>
                                    </div>
                                    <div
                                        class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                        <div class="btn-group new-btn-group my-1 my-sm-0 ps-0">
                                            <input type="radio" class="btn-check" name="Period" id="CurrentMonth"
                                                value="this-month" />
                                            <label class="btn btn-outline-primary mb-0" for="CurrentMonth">Current
                                                Month</label>

                                            <input type="radio" class="btn-check" name="Period" id="LastMonth"
                                                value="last-month" />
                                            <label class="btn btn-outline-primary mb-0" for="LastMonth">Last Month</label>

                                            <input type="radio"
                                                class="btn-check form-control flatpickr-range flatpickr-input"
                                                name="Period" id="Custom" />
                                            <label class="btn btn-outline-primary mb-0" for="Custom">Custom</label>
                                        </div>
                                        <button data-bs-toggle="modal" data-bs-target="#advancedFilter"
                                            class="btn btn-primary btn-sm mb-0 waves-effect"><i data-feather="filter"></i>
                                            Advance Filter</button>
                                    </div>
                                </div>

                                <div class="customernewsection-form poreportlistview p-1">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Series Number</label>
                                                <select class="form-select select2" id="series">
                                                    <option value="">Select</option>
                                                    @foreach ($leases as $lease)
                                                        <option value="{{ $lease->id }}">{{ $lease->document_no }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Customer Name</label>
                                                <select class="form-select select2" id="customer">
                                                    <option value="">Select</option>
                                                    @foreach ($leases as $lease)
                                                        <option value="{{ $lease->customer->id }}">{{ $lease->customer->display_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Land ID/Number</label>
                                                <select class="form-select" id="land_id">
                                                    <option value="" selected>Select</option>
                                                    @foreach ($leases as $lease)
                                                        <option value="{{ $lease->land_id }}">{{ $lease->land->document_no }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Area </label>
                                                
                                                <select class="form-select select2 apply-filter" name="area" id="area">
                                                    <option value="" selected>Select</option>
                                                    @foreach ($leases as $lease)
                                                    @if($lease->land)
                                                        <option value='{{$lease->land->plot_area."(".$lease->land->area_unit.")"}}'>{{$lease->land->plot_area."(".$lease->land->area_unit.")"}}
                                                        </option>
                                                    @endif
                                                    @endforeach 
                                                </select>
                                           </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12" style="min-height: 300px">
                                <div class="table-responsive trailbalnewdesfinance po-reportnewdesign my-class">
                                    <table class="my-table datatables-basic table myrequesttablecbox">
                                        <thead></thead>
                                        <tbody></tbody>
                                    </table>
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

    <div class="modal fade text-start filterpopuplabel " id="advancedFilter" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Advance
                            Filter</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="step-custhomapp bg-light">
                        <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#Columns" role="tab"><i
                                        data-feather="columns"></i> Columns</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#filter-data" role="tab"><i
                                        data-feather="bar-chart"></i> More Filter</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#Scheduler" role="tab"><i
                                        data-feather="calendar"></i> Scheduler</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content tablecomponentreport">
                        <div class="tab-pane active" id="Columns">
                            <div class="compoenentboxreport">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check form-check-primary">
                                            <input type="checkbox" class="form-check-input" id="selectAll"
                                                checked="">
                                            <label class="form-check-label" for="selectAll">Select All Columns</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row sortable">
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="sl-no"
                                                checked="">
                                            <label class="form-check-label" for="sl-no">SL NO</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="customer-name"
                                                checked="">
                                            <label class="form-check-label" for="customer-name">Customer Name</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="land-number"
                                                checked="">
                                            <label class="form-check-label" for="land-number">Land Number</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="area-column"
                                                checked="">
                                            <label class="form-check-label" for="area-column">Area (in SQ FT)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="land-cost"
                                                checked="">
                                            <label class="form-check-label" for="land-cost">Land Cost</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="total-lease-amount"
                                                checked="">
                                            <label class="form-check-label" for="total-lease-amount">Total Lease
                                                Amount</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="lease-duration"
                                                checked="">
                                            <label class="form-check-label" for="lease-duration">Lease Duration</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="lease-type"
                                                checked="">
                                            <label class="form-check-label" for="lease-type">Lease Type</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="installment-amount"
                                                checked="">
                                            <label class="form-check-label" for="installment-amount">Installment
                                                Amount</label>
                                        </div>
                                    </div>
                                     </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="filter-data">
                            <div class="compoenentboxreport advanced-filterpopup customernewsection-form">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check ps-0">
                                            <label class="form-check-label">Add Filter</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-1">
                                    <div class="col-md-3">
                                        <label class="form-label">Land Cost</label>
                                        <input type="text" class="form-control" name="land_cost" id="land_cost">

                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Khasara Number</label>
                                        <input type="text" class="form-control" name="khasara_number"
                                            id="khasara_number">

                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Total Lease Amount</label>
                                        <input type="number" class="form-control" name="total_lease_amount"
                                            id="total_lease_amount">

                                    </div>
                                </div>
                                <div class="row mt-1 mb-1">
                                    <div class="col-md-3">
                                        <label class="form-label">Lease Duration</label>
                                        <input type="number" class="form-control" name="lease_duration"
                                            id="lease_duration">

                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Monthly Installment</label>
                                        <input type="number" class="form-control" name="monthly_installment"
                                            id="monthly_installment">

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="Scheduler">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="compoenentboxreport advanced-filterpopup customernewsection-form mb-1">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-check ps-0">
                                                    <label class="form-check-label">Add Scheduler</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row camparboxnewcen">
                                            <div class="col-md-8">
                                                <label class="form-label">To</label>
                                                <div class="select2-wrapper" name="to-wrapper">
                                                    <select class="form-select select2" name="to" multiple
                                                        id="to_user">
                                                        <option value="" disabled>Select</option>
                                                        @if (auth()->check() && auth()->user()->employee_type == 'employee')
                                                            <option value="{{ auth()->user()->id }}"
                                                                data-type="App\\Models\\Employee">
                                                                {{ auth()->user()->name }}
                                                            </option>
                                                        @else
                                                            @foreach ($users as $user)
                                                                <option value="{{ $user->id }}"
                                                                    data-type="App\Models\User">
                                                                    {{ $user->name }}
                                                                </option>
                                                            @endforeach
                                                            @foreach ($employees as $employee)
                                                                <option value="{{ $employee->id }}"
                                                                    data-type="App\Models\Employee">
                                                                    {{ $employee->name }}
                                                                </option>
                                                            @endforeach
                                                        @endif

                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row camparboxnewcen">
                                            <div class="col-md-4">
                                                <label class="form-label">Type</label>
                                                <select class="form-select" name="type" id="type">
                                                    <option value="">Select</option>
                                                    <option value="daily">Daily</option>
                                                    <option value="weekly">Weekly</option>
                                                    <option value="monthly">Monthly</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label">Select Date</label>
                                                <input type="datetime-local" class="form-select" name="date"
                                                    id="dateInput" />
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label">Remarks</label>
                                                <textarea class="form-control" placeholder="Enter Remarks" name="remarks" id="remarks"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer ">
                    <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary data-submit mr-1" id="applyBtn">Apply</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Scheduler report -->
    <!-- Modal Structure -->
    {{-- <div class="modal fade" id="landReportModal" tabindex="-1" aria-labelledby="landReportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="landReportModalLabel">Land Report Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table" id="modalDataTable">
                        <thead>
                            <tr>
                                <th>Field Name</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody id="modalTableBody">
                            <!-- Data will be appended here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="modal fade" id="landReportModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 800px">
            <div class="modal-content">
                <div class="modal-body pb-2">
                    <div class="row mt-1">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table
                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Lease Amount</th>
                                            <th>Amount Due</th>
                                            <th>Total Received </th>
                                            <th>Overdue (Days) </th>
                                        </tr>
                                    </thead>
                                    <tbody id="recovery-schedule"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1 waves-effect">Cancel</button>
                        <button type="submit" class="btn btn-primary waves-effect waves-float waves-light" id="rec_submit" disabled="">Submit</button>
                    </div> --}}
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <!-- BEGIN: Dashboard Custom Code JS-->
    <script src="{{ asset('assets/js/custom/pages/land/land-report.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- END: Dashboard Custom Code JS-->

    <script>
        window.routes = {
            landReportFilter: @json(route('land.getReportFilter')),
            reportScheduler: @json(route('land.add.scheduler')),
            reportSendMail: @json(route('land.send.report')),
            recoverySchedulerReport: @json(route('land.recovery.scheduler')),
            lease: @json(route('lease.show', ['id' => '__ID__'])),
        };

        $(function() {
            $(".sortable").sortable();
        });
    </script>
@endsection
