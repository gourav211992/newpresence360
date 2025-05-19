@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content -->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Lease Reports</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                    <li class="breadcrumb-item active">Lease Reports</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-md-12 bg-light border-bottom mb-1 po-reportfileterBox">
                            <div class="row pofilterhead action-button align-items-center">
                                <div class="col-md-4">
                                    <h3>Lease Report</h3>
                                    <p>Apply the Basic Filter</p>
                                </div>
                                <div
                                    class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                    <div class="btn-group new-btn-group my-1 my-sm-0 ps-0">
                                        <input type="radio" class="btn-check form-control" name="Period"
                                            id="active" />
                                        <label class="btn btn-outline-primary mb-0" for="active">Active Lease
                                            Agreements</label>

                                        <input type="radio" class="btn-check form-control" name="Period"
                                            id="upcoming" />
                                        <label class="btn btn-outline-primary mb-0" for="upcoming">Upcoming Lease
                                            Expiration</label>

                                        <input type="radio" class="btn-check form-control" name="Period"
                                            id="payment-status" />
                                        <label class="btn btn-outline-primary mb-0" for="payment-status">Payment Status
                                            Report</label>

                                        <input type="radio" class="btn-check form-control" name="Period"
                                            id="expired" />
                                        <label class="btn btn-outline-primary mb-0" for="expired">Expired Lease
                                            Agreements</label>

                                        <input type="radio" class="btn-check form-control" name="Period"
                                            id="revenue-report" />
                                        <label class="btn btn-outline-primary mb-0" for="revenue-report">Revenue Report
                                            (Monthly)</label>

                                    </div>

                                </div>
                            </div>

                            <div class="customernewsection-form poreportlistview p-1">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="mb-1 mb-sm-0">
                                            <label class="form-label">Series Number</label>
                                            <select class="form-select select2 apply-filter" id="series">
                                                <option value="">Select</option>
                                                @foreach ($leases as $lease)
                                                    <option value="{{ $lease->document_no }}">{{ $lease->document_no }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-1 mb-sm-0">
                                            <label class="form-label">Customer Name</label>
                                            <select class="form-select select2 apply-filter" id="customer">
                                                <option value="">Select</option>
                                                @foreach ($leases as $lease)
                                                @if($lease->customer)
                                                    <option value="{{ $lease->customer->id }}">{{ $lease->customer->display_name }}
                                                    </option>
                                                @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-1 mb-sm-0">
                                            <label class="form-label">Land ID/Number</label>
                                            <select class="form-select select2 apply-filter" id="land_id">
                                                <option value="" selected>Select</option>
                                                @foreach ($leases as $lease)
                                                @if($lease->land)
                                                    <option value="{{ $lease->land->id }}">{{ $lease->land->document_no }}
                                                    </option>
                                                @endif
                                                @endforeach 
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-1 mb-sm-0">
                                            <label class="form-label">Area </label>
                                            <select class="form-select select2 apply-filter" id="area">
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

                        <div class="col-md-12">
                            <div class="card">
                                <div class="table-responsive trailbalnewdesfinance po-reportnewdesign my-class">
                                    <table class="my-table datatables-basic table myrequesttablecbox">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Lease ID</th>
                                                <th>Land ID</th>
                                                <th>Tenant Name</th>
                                                <th>Property Name</th>
                                                <th>Khasara No.</th>
                                                <th>Land Area.</th>
                                                <th>Lease Start Date</th>
                                                <th>Lease End Date</th>
                                                <th>Monthly Rent</th>
                                                <th>Payment Status</th>
                                                <th>Overdue Months</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
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
    <!-- END: Content -->
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
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="total-received"
                                                checked="">
                                            <label class="form-check-label" for="total-received">Total Received</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="amount-due"
                                                checked="">
                                            <label class="form-check-label" for="amount-due">Amount Due</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="overdue"
                                                checked="">
                                            <label class="form-check-label" for="overdue">Overdue (Days)</label>
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
                                        <label class="form-label">Payment Last Received</label>
                                        <input type="date" class="form-select" name="payment_last_received"
                                            id="payment_last_received" />
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
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            var dt_basic_table = $('.myrequesttablecbox');

            if (dt_basic_table.length) {
                var dataTable = dt_basic_table.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
					url: "{{ route('lease.report') }}",
					data: function (d) {
						d.date = $("#fp-range").val(),
						d.land = $("#land_id").val(),
						d.lease = $("#series").val(),
						d.customer = $("#customer").val(),
						d.area = $("#area").val();
					}
				},
                columns: [{
                            data: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'lease_id' // Lease ID column
                        },
                        {
                            data: 'land_no' // New Land No column
                        },
                        {
                            data: 'tenant_name'
                        },
                        {
                            data: 'property_name'
                        },
                        {
                            data: 'khasara_no'
                        },
                        {
                            data: 'land_area'
                        },
                        {
                            data: 'lease_start_date'
                        },
                        {
                            data: 'lease_end_date'
                        },
                        {
                            data: 'monthly_rent'
                        },
                        {
                            data: 'payment_status'
                        },
                        {
                            data: 'overdue_months'
                        },
                        {
                            data: 'status',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    order: [
                        [1, 'desc'] // Default sorting by Lease ID
                    ],
                    dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    buttons: [{
                        extend: 'collection',
                        className: 'btn btn-outline-secondary dropdown-toggle',
                        text: feather.icons['share'].toSvg({
                            class: 'font-small-4 mr-50'
                        }) + 'Export',
                        buttons: [{
                                extend: 'print',
                                text: feather.icons['printer'].toSvg({
                                    class: 'font-small-4 mr-50'
                                }) + 'Print',
                                className: 'dropdown-item',
                                exportOptions: {
                                    columns: ':visible'
                                }
                            },
                            {
                                extend: 'csv',
                                text: feather.icons['file-text'].toSvg({
                                    class: 'font-small-4 mr-50'
                                }) + 'CSV',
                                className: 'dropdown-item',
                                exportOptions: {
                                    columns: ':visible'
                                }
                            },
                            {
                                extend: 'excel',
                                text: feather.icons['file'].toSvg({
                                    class: 'font-small-4 mr-50'
                                }) + 'Excel',
                                className: 'dropdown-item',
                                exportOptions: {
                                    columns: ':visible'
                                }
                            }
                        ],
                        init: function(api, node, config) {
                            $(node).removeClass('btn-secondary');
                            setTimeout(function() {
                                $(node).closest('.dt-buttons').removeClass('btn-group')
                                    .addClass('d-inline-flex');
                            }, 50);
                        }
                    }],
                    drawCallback: function() {
                        feather.replace();
                    },
                    language: {
                        paginate: {
                            previous: '&nbsp;',
                            next: '&nbsp;'
                        }
                    },
                    columnDefs: [{
                        targets: [10, 11], // Adjust column indices for styling
                        className: 'text-center'
                    }]
                });
                dataTable.column(11).visible(false);
                // Handle the Expired button click
                $('#active').on('click', function() {
                    var today = moment().format('DD-MM-YYYY'); // Get today's date in 'DD-MM-YYYY' format
                    dataTable.columns().every(function() {
                        this.visible(true);
                    });
                    dataTable.rows().every(function() {
                        $(this.node()).show();
                    });


                    // Iterate through the rows and hide those with expired leases
                    dataTable.rows().every(function() {
                        var rowData = this.data(); // Get the row data
                        var leaseEndDate = moment(rowData.lease_end_date).format(
                            'DD-MM-YYYY'); // Parse lease_end_date

                        // Check if the lease end date is before today's date
                        if (leaseEndDate < today) {
                            $(this.node()).hide(); // Hide expired row
                        } else {
                            $(this.node()).show(); // Show non-expired row
                        }
                    });

                    // Optionally hide specific columns when expired filter is applied
                    dataTable.column(7).visible(false); // Hide "Payment Status"
                    dataTable.column(10).visible(false); // Hide "Payment Status"
                    dataTable.column(11).visible(false); // Hide "Payment Status"
                    dataTable.column(12).visible(false); // Hide "Overdue Months"

                    // Redraw table after filtering
                    //dataTable.draw();
                });
                $('#upcoming').on('click', function() {
                    var today = moment(); // Get today's date
                    var next12Months = moment().add(12, 'months'); // Get the date 12 months from today
                    dataTable.columns().every(function() {
                        this.visible(true);
                    });
                    dataTable.rows().every(function() {
                        $(this.node()).show();
                    });


                    dataTable.rows().every(function() {
                        var rowData = this.data(); // Get the row data
                        var leaseEndDate = moment(rowData.lease_end_date,
                            'DD-MM-YYYY'); // Parse lease_end_date

                        // Check if the lease end date is within the next 12 months from today
                        if (leaseEndDate.isBetween(today, next12Months, 'days', '[]')) {
                            $(this.node()).show(); // Show row if it's within the next 12 months
                        } else {
                            $(this.node()).hide(); // Hide row if it's outside the 12 months
                        }
                    });
                    // Optionally hide specific columns if needed (e.g., payment columns)
                    dataTable.column(7).visible(false); // Hide "Payment Status" column
                    dataTable.column(10).visible(false); // Hide "Payment Status" column
                    dataTable.column(11).visible(false); // Hide "Overdue Months" column

                    // Redraw the table after filtering
                    // dataTable.draw();
                });
                $('#payment-status').on('click', function() {
                    dataTable.columns().every(function() {
                        this.visible(true);
                    });
                    dataTable.rows().every(function() {
                        $(this.node()).show();
                    });
                    // Optionally hide specific columns if needed (e.g., payment columns)
                    dataTable.column(1).visible(false); // Hide "Payment Status" column
                    dataTable.column(7).visible(false); // Hide "Payment Status" column
                    dataTable.column(8).visible(false); // Hide "Overdue Months" column
                    dataTable.column(12).visible(false); // Hide "Overdue Months" column

                    // Redraw the table after filtering
                    // dataTable.draw();
                });
                $('#expired').on('click', function() {
                    var today = moment().format('DD-MM-YYYY'); // Get today's date in 'DD-MM-YYYY' format

                    // Iterate through the rows and hide those with expired leases
                    dataTable.rows().every(function() {
                        var rowData = this.data(); // Get the row data
                        var leaseEndDate = moment(rowData.lease_end_date).format(
                            'DD-MM-YYYY'); // Parse lease_end_date

                        dataTable.columns().every(function() {
                            this.visible(true);
                        });
                        // Check if the lease end date is before today's date
                        if (leaseEndDate > today) {
                            $(this.node()).hide(); // Hide expired row
                        } else {
                            $(this.node()).show(); // Show non-expired row
                        }
                    });

                    // Optionally hide specific columns when expired filter is applied
                    dataTable.column(7).visible(false); // Hide "Payment Status"
                    dataTable.column(9).visible(false); // Hide "Payment Status"
                    dataTable.column(10).visible(false); // Hide "Payment Status"
                    dataTable.column(11).visible(false); // Hide "Payment Status"
                    dataTable.column(12).visible(false); // Hide "Overdue Months"

                    // Redraw table after filtering
                    //dataTable.draw();
                });
                $('#revenue-report').on('click', function() {
                    var today = moment().format('DD-MM-YYYY'); // Get today's date in 'DD-MM-YYYY' format

                    // Iterate through the rows and hide those with expired leases
                    dataTable.columns().every(function() {
                        this.visible(true);
                    });

                    dataTable.rows().every(function() {
                        $(this.node()).show();
                    });



                    // Optionally hide specific columns when expired filter is applied
                    dataTable.column(4).visible(false); // Hide "Payment Status"
                    dataTable.column(7).visible(false); // Hide "Payment Status"
                    dataTable.column(8).visible(false); // Hide "Payment Status"
                    dataTable.column(11).visible(false); // Hide "Payment Status"
                    dataTable.column(12).visible(false); // Hide "Overdue Months"

                    // Redraw table after filtering
                    //dataTable.draw();
                });
                $(".apply-filter").on("change", function () {
			        dataTable.draw();
                })



            }
        });

        
    </script>
@endsection
