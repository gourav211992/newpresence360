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
                                        <h3>Debtors</h3>
                                        <p class="my-25">As on <strong>{{ $date2 }}</strong></p>
                                    </div>
                                    <div
                                        class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                        <button data-bs-toggle="modal" data-bs-target="#addcoulmn"
                                            class="btn btn-primary btn-sm mb-0 waves-effect"><i data-feather="filter"></i>
                                            Advance Filter</button>
                                    </div>
                                </div>

                                <div class="customernewsection-form poreportlistview p-1">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Group</label>
                                                <select class="form-select select2" id="filter_group">
                                                    <option value="">Select</option>
                                                    @php
                                                        use App\Helpers\Helper;
                                                        $selectedGroupId = request()->query('group'); // Get group_id from URL params
                                                    @endphp

                                                    @isset($all_groups)
                                                        @foreach ($all_groups as $group)
                                                            <option value="{{ $group->id }}"
                                                                {{ $selectedGroupId == $group->id ? 'selected' : '' }}>
                                                                {{ $group->name }}
                                                            </option>
                                                        @endforeach
                                                    @endisset

                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Ledger</label>
                                                <select class="form-select select2" id="filter_ledger">
                                                    <option value="">Select</option>
                                                    @php
                                                        $selectedLedgerId = request()->query('ledger'); // Get group_id from URL params
                                                    @endphp
                                                    @isset($all_ledgers)
                                                        @foreach ($all_ledgers as $ledger)
                                                            <option value="{{ $ledger->id }}"
                                                                {{ $selectedLedgerId == $ledger->id ? 'selected' : '' }}>
                                                                {{ $ledger->name }}</option>
                                                        @endforeach
                                                    @endisset

                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="fp-range">Select Period</label>
                                            <input type="text" id="fp-range"
                                                class="form-control flatpickr-range bg-white flatpickr-input active"
                                                value="{{ $date }}" placeholder="YYYY-MM-DD to YYYY-MM-DD"
                                                readonly="readonly">
                                        </div>

                                        <div class="col-md-2">
                                            <div class="mt-2 mb-sm-0">
                                                <label class="mb-1">&nbsp;</label>
                                                <button
                                                    class="btn mt-25 btn-warning btn-sm waves-effect waves-float waves-light"
                                                    onClick="filter()">
                                                    <i data-feather="filter"></i> Run Report</button>
                                            </div>

                                        </div>





                                    </div>
                                    <br>
                                    <div class="col-md-3">
                                        @if (request()->hasAny(['ledger', 'age0', 'age1', 'age2', 'age3', 'age4']))
                                            <a type="button" href="{{ route('voucher.debit.report') }}"
                                                class="btn btn-danger">Clear</a>
                                        @endif
                                    </div>



                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="table-responsive trailbalnewdesfinance po-reportnewdesign gsttabreporttotal">
                                    <table
                                        class="datatables-basic table myrequesttablecbox tabledebreport tableistlastcolumnfixed">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Group</th>
                                                <th>Ledger</th>
                                                <th>Credit Days</th>
                                                <th class="text-end">Total O/S</th>
                                                <th class="text-end">OVERDUE</th>

                                                <!-- Define the variables for all age ranges -->
                                                @php
                                                    $age0 = request()->get('age0', 30);
                                                    $age1 = request()->get('age1', 60);
                                                    $age2 = request()->get('age2', 90);
                                                    $age3 = request()->get('age3', 120);
                                                    $age4 = request()->get('age4', 180);
                                                @endphp

                                                <!-- Display the age ranges dynamically -->
                                                <th class="text-end">0-{{ $age0 }} Days</th>
                                                <th class="text-end">{{ $age0 + 1 }}-{{ $age1 }} Days</th>
                                                <th class="text-end">{{ $age1 + 1 }}-{{ $age2 }} Days</th>
                                                <th class="text-end">{{ $age2 + 1 }}-{{ $age3 }} Days</th>
                                                <th class="text-end">{{ $age3 + 1 }}-{{ $age4 }} Days</th>
                                                <th class="text-end">Above {{ $age4 }} Days</th>

                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @foreach ($customers as $index => $customer)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td class="fw-bolder text-dark text-nowrap">
                                                        <div data-bs-placement="top"
                                                            title="{{ $customer?->ledger_parent_name ?? '-' }}">
                                                            {{ $customer?->ledger_parent_name ?? '-' }}
                                                        </div>
                                                    </td>

                                                    <td class="text-nowrap">
                                                        <div data-bs-placement="top"
                                                            title="{{ $customer?->ledger_name ?? '-' }}">
                                                            {{ $customer?->ledger_name ?? '-' }}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div data-bs-placement="top"
                                                            title="{{ $customer?->credit_days ?? 0 }}">
                                                            {{ $customer?->credit_days ?? 0 }}
                                                        </div>
                                                    </td>
                                                    <td class="text-end text-nowrap">
                                                        <span class="badge rounded-pill badge-light-success">
                                                            {{ number_format(abs($customer->total_outstanding), 2) }}
                                                            <span
                                                                class="{{ $customer->total_outstanding < 0 ? 'text-danger' : 'text-success' }}">
                                                                {{ $customer->total_outstanding < 0 ? 'Cr' : 'Dr' }}
                                                            </span>
                                                    </td>
                                                    <td class="text-end text-nowrap">
                                                        <div data-bs-placement="top"
                                                            title="{{ $customer?->overdue ?? 0 }}">
                                                            {{ Helper::formatIndianNumber($customer?->overdue ?? 0) }}
                                                        </div>
                                                    </td>
                                                    <td class="text-end text-nowrap">
                                                        {{ number_format(abs($customer->days_0_30), 2) }}
                                                        {{ $customer->days_0_30 < 0 ? 'Cr' : 'Dr' }}
                                                    </td>
                                                    <td class="text-end text-nowrap">
                                                        {{ number_format(abs($customer->days_30_60), 2) }}
                                                        {{ $customer->days_30_60 < 0 ? 'Cr' : 'Dr' }}
                                                    </td>
                                                    <td class="text-end text-nowrap">
                                                        {{ number_format(abs($customer->days_60_90), 2) }}
                                                        {{ $customer->days_60_90 < 0 ? 'Cr' : 'Dr' }}
                                                    </td>
                                                    <td class="text-end text-nowrap">
                                                        {{ number_format(abs($customer->days_90_120), 2) }}
                                                        {{ $customer->days_90_120 < 0 ? 'Cr' : 'Dr' }}
                                                    </td>
                                                    <td class="text-end text-nowrap">
                                                        {{ number_format(abs($customer->days_120_180), 2) }}
                                                        {{ $customer->days_120_180 < 0 ? 'Cr' : 'Dr' }}
                                                    </td>
                                                    <td class="text-end text-nowrap">
                                                        {{ number_format(abs($customer->days_above_180), 2) }}
                                                        {{ $customer->days_above_180 < 0 ? 'Cr' : 'Dr' }}
                                                    </td>
                                                    <td>
                                                       @if ($customer->ledger_id)
                                                        @php
                                                            $query = [];
                                                            if (request('date')) $query['date'] = request('date');
                                                            if (request('location_id')) $query['location_id'] = request('location_id');
                                                            if (request('organization_id')) $query['organization_id'] = request('organization_id');
                                                            if (request('cost_center_id')) $query['cost_center_id'] = request('cost_center_id');
                                                            if (request('cost_group_id')) $query['cost_group_id'] = request('cost_group_id');

                                                            $url = route('crdr.report.ledger.details', ['debit', $customer->ledger_id, $customer->ledger_parent_id]);
                                                            if ($query) {
                                                                $url .= '?' . http_build_query($query);
                                                            }
                                                        @endphp

                                                        <a href="{{ $url }}" target="_blank">
                                                            <i class="cursor-pointer" data-feather='eye'></i>
                                                        </a>
                                                    @endif

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
                <!-- ChartJS section end -->

            </div>
        </div>
    </div>
    <!-- END: Content-->
    <!-- Advance Filter Modal   -->

    <div class="modal fade text-start" id="invoice-view" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">
                            <span id="party"></span>
                        </h4>
                        <p class="mb-0">View the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">


                        <div class="col-md-12">


                            <div class="table-responsive">
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Series</th>
                                            <th>Doc No.</th>
                                            <th>Doc Date</th>
                                            <th>O/S Amount</th>
                                            <th class="text-end">0-30 Days</th>
                                            <th class="text-end">30-60 days</th>
                                            <th class="text-end">60-90 days</th>
                                            <th class="text-end">90-120 days</th>
                                            <th class="text-end">120-180 days</th>
                                            <th class="text-end">Above 180 days</th>
                                        </tr>
                                    </thead>
                                    <tbody id="inovice_tbody">

                                    </tbody>


                                </table>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                            data-feather="x-circle"></i> Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade text-start filterpopuplabel " id="addcoulmn" tabindex="-1" aria-labelledby="myModalLabel17"
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
                                <a class="nav-link active" data-bs-toggle="tab" href="#Ageing" role="tab"><i
                                        data-feather="calendar"></i> Ageing Filter</a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#Filter" role="tab"><i
                                        data-feather="bar-chart"></i> Other Filter</a>
                            </li>

                        </ul>
                    </div>


                    <!--
                    <div class="row">
                     <div class="col-md-7 mt-1">
                      <div class="form-check form-check-success mb-1">
                       <input type="checkbox" class="form-check-input" id="colorCheck1" data-column-index=""  checked="">
                       <label class="form-check-label fw-bolder text-dark" for="colorCheck1">All Columns</label>
                      </div>
                     </div>
                    </div>
            -->



                    <div class="tab-content tablecomponentreport">
                        <div class="tab-pane active" id="Ageing">



                            <div class="compoenentboxreport" style="margin-top:2%;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check form-check-primary">
                                            <input type="checkbox" class="form-check-input" checked
                                                id="selectAllInputAging">
                                            <label class="form-check-label" for="selectAllInputAging">Ageing</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row sortable">
                                    <div class="col-md-12">
                                    
                                        <div class="demo-inline-spacing">
                                            <div class="form-check form-check-primary mt-0">
                                                <input type="radio" id="customColorRadio1" name="d"
                                                    value="invoice" class="form-check-input" checked="">
                                                <label class="form-check-label fw-bolder" for="customColorRadio1">Invoice Date</label>
                                            </div>
                                            <div class="form-check form-check-primary mt-0">
                                                <input type="radio" id="service" name="d" value="due"
                                                    class="form-check-input">
                                                <label class="form-check-label fw-bolder" for="service">Due Date</label>
                                            </div>
                                        </div>
                                    
                                        </div>
                                    <!-- New input fields for days -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" id="age0" class="form-control aging-input"
                                                value="30" min="0" placeholder="30 Days">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" id="age1" class="form-control aging-input"
                                                value="60" min="0" placeholder="60 Days">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" id="age2" class="form-control aging-input"
                                                value="90" min="0" placeholder="90 Days">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" id="age3" class="form-control aging-input"
                                                value="120" min="0" placeholder="120 Days">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" id="age4" class="form-control aging-input"
                                                value="180" min="0" placeholder="180 Days">
                                        </div>
                                    </div>
                                </div>
                            </div>



                        </div>
                        <div class="tab-pane" id="Filter">
                            <div class="compoenentboxreport advanced-filterpopup customernewsection-form">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check ps-0">
                                            <label class="form-check-label">Add Filter</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Organizaion</label>
                                        <select id="organization_id" class="form-select select2" multiple>
                                            <option value="" disabled>Select</option>
                                            @foreach ($companies as $organization)
                                                <option value="{{ $organization->organization->id }}"
                                                    {{ $organization->organization->id == $organizationId ? 'selected' : '' }}>
                                                    {{ $organization->organization->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Location</label>
                                        <select id="location_id" class="form-select select2">
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Cost Group</label>
                                        <select id="cost_group_id" class="form-select select2" name="cost_group_id" required>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Cost Center</label>
                                        <select id="cost_center_id" class="form-select select2" name="cost_center_id"
                                            required>
                                        </select>
                                    </div>

                                </div>

                            </div>
                        </div>
                        <div class="tab-pane" id="Bank">
                            <div class="compoenentboxreport advanced-filterpopup customernewsection-form">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check ps-0">
                                            <label class="form-check-label">Add Filter</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Select Category</label>
                                        <select class="form-select select2">
                                            <option>Select</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Select Sub-Category</label>
                                        <select class="form-select select2">
                                            <option>Select</option>
                                        </select>
                                    </div>

                                </div>

                            </div>
                        </div>
                        <div class="tab-pane" id="Location">
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
                                                <select class="form-select select2" multiple>
                                                    <option>Select</option>
                                                    <option>Pawan Kuamr</option>
                                                    <option>Deepak Singh</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row camparboxnewcen">
                                            <div class="col-md-4">
                                                <label class="form-label">Type</label>
                                                <select class="form-select">
                                                    <option>Select</option>
                                                    <option>Daily</option>
                                                    <option>Weekly</option>
                                                    <option>Monthly</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label">Select Date</label>
                                                <input type="datetime-local" class="form-select" />
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label">Remarks</label>
                                                <textarea class="form-control" placeholder="Enter Remarks"></textarea>
                                            </div>




                                        </div>

                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>

                </div>

                <div class="modal-footer ">
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary data-submit mr-1" onclick="filter()">Apply</button>
                </div>
            </div>
        </div>
    </div>
    {{-- for customized excel --}}
    <form id="customExcelExportForm1" action="{{ route('credit.debit.report.export') }}" method="POST">
        @csrf
        <input type="hidden" name="customers" value="{{ json_encode($customers) }}">
        <!-- Serialize complex arrays to JSON -->
        <input type="hidden" name="date2" value="{{ $date2 }}">
        <input type="hidden" name="date" value="{{ $date }}">
        <input type="hidden" name="type" value="{{ 'debit' }}">
        <input type="hidden" name="age0" value="{{ $age0 }}">
        <input type="hidden" name="age1" value="{{ $age1 }}">
        <input type="hidden" name="age2" value="{{ $age2 }}">
        <input type="hidden" name="age3" value="{{ $age3 }}">
        <input type="hidden" name="age4" value="{{ $age4 }}">
        <input type="hidden" name="cost_group_id" value="{{ request('cost_group_id')??null }}">
        <input type="hidden" name="cost_center_id" value="{{ request('cost_center_id')??null }}">
        <input type="hidden" name="organization_id" value="{{ request('organization_id')??null }}">
        <input type="hidden" name="location_id" value="{{ request('location_id')??null }}">
        <input type="hidden" name="group_id" value="{{ $selectedGroupId }}">
    </form>
@endsection
@section('scripts')
    <!-- BEGIN: Dashboard Custom Code JS-->
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- END: Dashboard Custom Code JS-->

    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })

        $(function() {
            $(".sortable").sortable();
        });


        $(function() {
            $(".ledgerselecct").autocomplete({
                source: [
                    "Furniture (IT001)",
                    "Chair (IT002)",
                    "Table (IT003)",
                    "Laptop (IT004)",
                    "Bags (IT005)",
                ],
                minLength: 0
            }).focus(function() {
                if (this.value == "") {
                    $(this).autocomplete("search");
                }
            });
        });
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })


        var dt_basic_table = $('.datatables-basic');
        if (dt_basic_table.length) {
            var dt_basic = dt_basic_table.DataTable({
                order: [
                    [0, 'asc']
                ],
                scrollX: true,
                dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-3"l><"col-sm-12 col-md-6 withoutheadbuttin dt-action-buttons text-end pe-0"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                "drawCallback": function(settings) {
                    feather.replace(); // Re-initialize icons if needed
                },
                displayLength: 8,
                lengthMenu: [8, 10, 25, 50, 75, 100],
                buttons: [{
                    extend: 'excel',
                    text: feather.icons['file'].toSvg({
                        class: 'font-small-4 me-50'
                    }) + 'Excel',
                    className: 'btn btn-outline-secondary',
                    filename: 'Debtors Report',
                    action: function(e, dt, node, config) {
                        document.getElementById('customExcelExportForm1').submit();
                    },
                    init: function(api, node, config) {
                        $(node).removeClass('btn-secondary');
                        $(node).parent().removeClass('btn-group');
                        setTimeout(function() {
                            $(node).closest('.dt-buttons').removeClass('btn-group').addClass(
                                'd-inline-flex');
                        }, 50);
                    }
                }],
                // buttons: [{
                //     extend: 'collection',
                //     className: 'btn btn-outline-secondary dropdown-toggle',
                //     text: feather.icons['share'].toSvg({
                //         class: 'font-small-3 me-50'
                //     }) + 'Export',
                //     buttons: [
                //         {
                //             extend: 'excel',
                //             text: feather.icons['file'].toSvg({
                //                 class: 'font-small-4 me-50'
                //             }) + 'Excel',
                //             className: 'dropdown-item',
                //             filename: 'Debtors Report',
                //             // exportOptions: {
                //             //     columns: ':not(:last-child)' // Excludes the last column (Action)
                //             // }
                //             action: function (e, dt, node, config) {
                //                                 document.getElementById('customExcelExportForm1').submit();
                //             }
                //         },
                //         {
                //             extend: 'pdf',
                //             text: feather.icons['clipboard'].toSvg({
                //                 class: 'font-small-4 me-50'
                //             }) + 'Pdf',
                //             className: 'dropdown-item',
                //             filename: 'Debtors Report',
                //             exportOptions: {
                //                 columns: ':not(:last-child)' // Excludes the last column (Action)
                //             }
                //         },
                //         {
                //             extend: 'copy',
                //             text: feather.icons['mail'].toSvg({
                //                 class: 'font-small-4 me-50'
                //             }) + 'Mail',
                //             className: 'dropdown-item',
                //             filename: 'Debtors Report',
                //             exportOptions: {
                //                 columns: ':not(:last-child)' // Excludes the last column (Action)
                //             }
                //         }
                //     ],
                //     init: function(api, node, config) {
                //         $(node).removeClass('btn-secondary');
                //         $(node).parent().removeClass('btn-group');
                //         setTimeout(function() {
                //             $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                //         }, 50);
                //     }
                // }],
                language: {
                    search: '',
                    searchPlaceholder: "Search...",
                    paginate: {
                        previous: '&nbsp;',
                        next: '&nbsp;'
                    }
                }
            });

            $('div.head-label').html('<h6 class="mb-0">Event List</h6>');
        }

        // Flat Date picker (if needed)
        var dt_date_table = $('.dt-date');
        if (dt_date_table.length) {
            dt_date_table.flatpickr({
                monthSelectorType: 'static',
                dateFormat: 'm/d/Y'
            });
        }

        function getDetails(ledger, ledger_group, partyName) {
            $.ajax({
                url: "{{ route('voucher.credit_details.report') }}?ledger_id=" + ledger + "&ledger_group_id=" +
                    ledger_group + "&type=debit",
                method: 'GET', // Change to POST if necessary
                dataType: 'json',
                success: function(data) {
                    // Check if data is not empty
                    if (data.length > 0) {
                        var tbody = $('#inovice_tbody'); // Get tbody element
                        tbody.empty();
                        $('#party').text(partyName)

                        // Loop through the response data and append rows to the table
                        $.each(data, function(index, item) {
                            // Function to format amounts with Cr/Dr
                            function formatAmount(amount) {
                                return amount < 0 ? Math.abs(amount) + ' Cr' : amount + ' Dr';
                            }

                            // Create a new row for each item in the response
                            var row = '<tr>';
                            row += '<td>' + (index + 1) + '</td>'; // Row index
                            row += '<td>' + item.bookCode + '</td>'; // Series column
                            row += '<td>' + item.voucher_no + '</td>'; // Doc No. column
                            row += '<td>' + item.document_date + '</td>'; // Doc Date column
                            row += '<td class="text-end">' + formatAmount(item.total_outstanding) +
                                '</td>'; // O/S Amount column
                            row += '<td class="text-end">' + formatAmount(item.days_0_30) +
                                '</td>'; // 0-30 Days column
                            row += '<td class="text-end">' + formatAmount(item.days_30_60) +
                                '</td>'; // 30-60 Days column
                            row += '<td class="text-end">' + formatAmount(item.days_60_90) +
                                '</td>'; // 60-90 Days column
                            row += '<td class="text-end">' + formatAmount(item.days_90_120) +
                                '</td>'; // 90-120 Days column
                            row += '<td class="text-end">' + formatAmount(item.days_120_180) +
                                '</td>'; // 120-180 Days column
                            row += '<td class="text-end">' + formatAmount(item.days_above_180) +
                                '</td>'; // Above 180 Days column
                            row += '</tr>';

                            // Append the new row to the table body
                            tbody.append(row);
                            $('#invoice-view').modal('show');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching data: ', error);
                }
            });


        }

        $(document).ready(function() {
            let urlParams = new URLSearchParams(window.location.search);
            let selectedVoucher = urlParams.get('voucher');

            if (selectedVoucher) {
                $('#filter_voucher').val(selectedVoucher).select2(); // Set selected and trigger change for Select2
            }

            // Set the input fields' values based on the URL parameters, using defaults if the params are not set
            $('#age0').val(urlParams.get('age0') || 30); // Default to 30 if age0 is not present
            $('#age1').val(urlParams.get('age1') || 60); // Default to 60 if age1 is not present
            $('#age2').val(urlParams.get('age2') || 90); // Default to 90 if age2 is not present
            $('#age3').val(urlParams.get('age3') || 120); // Default to 120 if age3 is not present
            $('#age4').val(urlParams.get('age4') || 180); // Default to 180 if age4 is not present

            if (urlParams.get('organization_id') == "")
                $('#organization_id').val(urlParams.get('organization_id'));

            if (urlParams.get('cost_center_id') == "")
                $('#cost_center_id').val(urlParams.get('cost_center_id'));
            
            if (urlParams.get('cost_group_id') == "")
                $('#cost_group_id').val(urlParams.get('cost_group_id'));

            if (urlParams.get('location_id') == "")
                $('#location_id').val(urlParams.get('location_id'));


            function toggleColumns() {
                $(".column-toggle").each(function() {
                    let colIndex = $(this).data("column-index");
                    if ($(this).is(":checked")) {
                        $("table th:nth-child(" + colIndex + "), table td:nth-child(" + colIndex + ")")
                            .show();
                    } else {
                        $("table th:nth-child(" + colIndex + "), table td:nth-child(" + colIndex + ")")
                            .hide();
                    }
                });
            }

            // Select All Checkbox
            $("#selectAll").change(function() {
                $(".column-toggle").prop("checked", $(this).prop("checked"));
                toggleColumns();
            });

            // Individual Column Toggle
            $(".column-toggle").change(function() {
                toggleColumns();
            });

            // Initialize on page load
            toggleColumns();
        });

        function filter() {
            let ledger = $('#filter_ledger').val();
            let group = $('#filter_group').val();
            let location = $('#location_id').val();
            let cost_center = $('#cost_center_id').val();
            let cost_group = $('#cost_group_id').val();
            let organization = $('#organization_id').val();
            let dueDate = $('input[name="d"]:checked').val();
            let range = $('#fp-range').val();
            let ages = [];
            let isAgingChecked = $('#selectAllInputAging').prop('checked'); // Check if the aging checkbox is checked

            // If the aging checkbox is checked, capture the age values
            if (isAgingChecked) {
                $('.aging-input').each(function() {
                    ages.push($(this).val()); // Get value of each aging input field
                });
            }

            let currentUrl = new URL(window.location.href); // Get the current URL

            // Add or update the voucher parameter
            if (ledger !== "" && ledger !== null) {
                currentUrl.searchParams.set('ledger', ledger);
            } else {
                currentUrl.searchParams.delete('ledger');
            }
            if (group !== "" && group !== null) {
                currentUrl.searchParams.set('group', group);
            } else {
                currentUrl.searchParams.delete('group');
            }
            if (range !== "" && range !== null) {
                currentUrl.searchParams.set('date', range);
            } else {
                currentUrl.searchParams.delete('date');
            }
            if (location !== "" && location !== null)
                currentUrl.searchParams.set('location_id', location);
            else
                currentUrl.searchParams.delete('location_id');

            if (organization !== "" && organization !== null)
                currentUrl.searchParams.set('organization_id', organization);
            else
                currentUrl.searchParams.delete('organization_id');

            if (cost_center !== "" && cost_center !== null)
                currentUrl.searchParams.set('cost_center_id', cost_center);
            else
                currentUrl.searchParams.delete('cost_center_id');
            if (cost_group !== "" && cost_group !== null)
                currentUrl.searchParams.set('cost_group_id', cost_group);
            else
                currentUrl.searchParams.delete('cost_group_id');

            if (dueDate) currentUrl.searchParams.set('d', dueDate); else currentUrl.searchParams.delete('d');

            // Add age values to the URL only if aging checkbox is checked
            if (isAgingChecked) {
                for (let i = 0; i < ages.length; i++) {
                    currentUrl.searchParams.set('age' + i, ages[i]); // Add or update age0, age1, age2, etc.
                }
            } else {
                // If aging checkbox is not checked, remove any age parameters
                for (let i = 0; i < 5; i++) {
                    currentUrl.searchParams.delete('age' + i);
                }
            }

            const ages_v = ['age0', 'age1', 'age2', 'age3', 'age4'];
            let prevValue = 0; // Start comparison from 0
            let isValid = true;

            $.each(ages_v, function(index, id) {
                let value = parseInt($('#' + id).val(), 10);

                // Validation checks
                if (isNaN(value) || value <= prevValue) {
                    isValid = false;
                    return false; // Break out of loop on failure
                }

                prevValue = value; // Update previous value for next check
            });


            if (isValid)
                window.location.href = currentUrl.toString();
            else {
                Swal.fire({
                    title: 'Not Valid Ageing!',
                    text: "Each age must be a number greater than the previous one.",
                    icon: 'error'
                });
            }
        }

        $('#filter_group').on('change', function() {
            let groupId = $(this).val();
            if (!groupId) {
                $('#ledgerDropdown').html('<option value="">Select Ledger</option>');
                return;
            }

            $.ajax({
                url: "{{ route('crdr.report.ledger', ':groupId') }}".replace(':groupId', groupId),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 200) {
                        let options = '<option value="">Select Ledger</option>';
                        response.data.forEach(function(ledger) {
                            options += `<option value="${ledger.id}">${ledger.name}</option>`;
                        });
                        $('#filter_ledger').html(options);
                    } else {
                        alert('No ledgers found');
                    }
                },
                error: function() {
                    alert('Error fetching ledgers');
                }
            });
        });
    </script>
    <script>
        const locations = @json($locations);
        const costCenters = @json($cost_centers);
        const costGroups = @json($cost_groups);

        function updateLocationsDropdown(selectedOrgIds) {
            selectedOrgIds = $('#organization_id').val() || [];

            const requestedLocationId = @json(request('location_id')) || "";

            const filteredLocations = locations.filter(loc =>
                selectedOrgIds.includes(String(loc.organization_id))
            );

            const $locationDropdown = $('#location_id');
            $locationDropdown.empty().append('<option value="">Select</option>');


            filteredLocations.forEach(loc => {
                const isSelected = String(loc.id) === String(requestedLocationId) ? 'selected' : '';
                $locationDropdown.append(`<option value="${loc.id}" ${isSelected}>${loc.store_name}</option>`);
            });

            // Load cost centers if location was pre-selected
            if (requestedLocationId) {
                loadCostGroupsByLocation(requestedLocationId);
            }

            $locationDropdown.trigger('change');
        }

        function loadCostGroupsByLocation(locationId) {
        const costCenter = $('#cost_center_id');
        costCenter.val(@json(request('cost_center_id')) || "");
        const filteredCenters = costCenters.filter(center => {
            if (!center.location) return false;
            const locationArray = Array.isArray(center.location)
                ? center.location.flatMap(loc => loc.split(','))
                : [];
            return locationArray.includes(String(locationId));
        });

        const costGroupIds = [...new Set(filteredCenters.map(center => center.cost_group_id))];
        
        const filteredGroups = costGroups.filter(group => costGroupIds.includes(group.id));
        console.log(filteredCenters,costGroupIds,filteredGroups);

        const $groupDropdown = $('#cost_group_id');
        $groupDropdown.empty().append('<option value="">Select Cost Group</option>');

        filteredGroups.forEach(group => {
            $groupDropdown.append(`<option value="${group.id}">${group.name}</option>`);
        });

        $('#cost_group_id').trigger('change');
    }

    function loadCostCentersByGroup(locationId, groupId) {
        const costCenter = $('#cost_center_id');
        costCenter.empty();

        const filteredCenters = costCenters.filter(center => {
            if (!center.location || center.cost_group_id !== groupId) return false;

            const locationArray = Array.isArray(center.location)
                ? center.location.flatMap(loc => loc.split(','))
                : [];

            return locationArray.includes(String(locationId));
        });

        if (filteredCenters.length === 0) {
            costCenter.prop('required', false);
            $('#cost_center_id').hide();
        } else {
            costCenter.append('<option value="">Select Cost Center</option>');
            $('#cost_center_id').show();

            filteredCenters.forEach(center => {
                costCenter.append(`<option value="${center.id}">${center.name}</option>`);
            });
        }
        costCenter.val(@json(request('cost_center_id')) || "");
        costCenter.trigger('change');
    }



        
        $('#organization_id').trigger('change');
        // On change of organization
        $('#organization_id').on('change', function() {
            const selectedOrgIds = $(this).val() || [];
            updateLocationsDropdown(selectedOrgIds);

        });

        // On page load, check for preselected orgs
        const preselectedOrgIds = $('#organization_id').val() || [];
        if (preselectedOrgIds.length > 0) {
            updateLocationsDropdown(preselectedOrgIds);
        }
        // On location change, load cost centers
        $('#location_id').on('change', function() {
            const locationId = $(this).val();
            if (!locationId) {
                $('#cost_center_id').empty().append('<option value="">Select Cost Center</option>');
                // $('.cost_center').hide(); // Optional: hide the section if needed
                return;
            }
            loadCostGroupsByLocation(locationId);



        });
        $('#cost_group_id').on('change', function () {
                const locationId = $('#location_id').val();
                const groupId = parseInt($(this).val());

                if (!locationId || !groupId) {
                    $('#cost_center_id').empty().append('<option value="">Select Cost Center</option>');
                    return;
                }

                loadCostCentersByGroup(locationId, groupId);
            });
    </script>
@endsection
