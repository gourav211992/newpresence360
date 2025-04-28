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
                            <h2 class="content-header-title float-start mb-0">Loan Application</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                    <li class="breadcrumb-item active">All Request</li>
                                </ol>
                            </div>
                        </div>

                    </div>

                </div>
                <div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                    </div>
                </div>
            </div>
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <section id="basic-datatable">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="table-responsive">
                                <table class="datatables-basic table myrequesttablecbox">
                                    <thead>
                                        <tr>
                                            <th class="pe-0">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="inlineCheckbox1">
                                                </div>
                                            </th>
                                            <th>Application No.</th>
                                            <th>Reference No.</th>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Email-ID</th>
                                            <th>Mobile No.</th>
                                            <th>Loan Type</th>
                                            <th>Loan Amt.</th>
                                            <th>Rec. Loan Amt.</th>
                                            <th>Santioned. Int</th>
                                            <th>Status</th>
                                            <th>Loan Apply Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
                    <div class="modal-dialog sidebar-sm">
                        <form class="add-new-record modal-content pt-0">
                            <div class="modal-header mb-1">
                                <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close">×</button>
                            </div>
                            <div class="modal-body flex-grow-1">
                                <div class="mb-1">
                                    <label class="form-label" for="fp-range">Select Date</label>
                                    <input type="text" id="fp-range" class="form-control flatpickr-range bg-white"
                                        placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Loan Type</label>
                                    <select id="filter-ledger-type" class="form-select">
                                        <option value="">Select</option>
                                        <option value="1">Home Loan</option>
                                        <option value="2">Vehicle Loan</option>
                                        <option value="3">Term Loan</option>
                                    </select>
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Customer Name</label>
                                    <select id="filter-ledger-name" class="form-select">
                                        <option value="">Select</option>
                                        @foreach ($loans as $loan)
                                            <option value="{{ $loan->name }}">{{ $loan->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="filter-status">
                                        <option value="">Select</option>
                                        <option value="0">Save as Draft</option>
                                        <option value="1">Proceed</option>
                                        <option value="2">Approved</option>
                                        <option value="3">Rejected</option>
                                        <option value="4">Assessment</option>
                                        <option value="5">Disbursement</option>
                                        <option value="6">Recovery</option>
                                    </select>
                                </div>

                            </div>
                            <div class="modal-footer justify-content-start">
                                <button type="button" class="btn btn-primary apply-filter mr-1">Apply</button>
                                <button type="reset" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="modal fade" id="viewassesgive" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Assessment by
                            Field Officer</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0" id="ass_para"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('loan.assess') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="loan_id" id="id_loan" value="">
                    <div class="modal-body pb-2">

                        <div class="row mt-1">

                            <div class="col-md-12">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label">Loan Amount <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" disabled id="amnt_loan" value=""
                                                class="form-control" />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label">Recommended Loan Amt. <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="ass_recom_amnt" id="ass_recom_amnt"
                                                value="" min="0" class="form-control" required />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label">CIBIL Score <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="ass_cibil" id="ass_cibil" value=""
                                                min="0" class="form-control" required />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label">Upload Document</label>
                                            <input type="file" name="ass_doc" class="form-control"
                                                onchange="checkFileTypeandSize(event)" />
                                            <div id="hidden_inputs"></div>
                                            <div id="doc_link" style="margin-top: 10px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="ass_remarks" id="ass_remarks" required></textarea>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="Disbursement" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px">
            <form action="{{ route('loan.disbursemnt') }}" method="POST" enctype="multipart/form-data"
                id="disbursement-form">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">
                                Disbursal Schedule by Field Officer</h4>
                            <p class="mb-0 fw-bold voucehrinvocetxt mt-0" id="dis_para"></p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pb-2">

                        <div class="row mt-1">
                            
                            <input type="hidden" name="loan_idd" id="idd_loan" value="">
                            <div class="col-md-12">

                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <div class="mb-1">
                                            <label class="form-label">Loan Amount</label>
                                            <input type="input" id="lloan_amount" class="form-control" disabled
                                                value="" />
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-1">
                                            <label class="form-label">Recommended Loan Amt</label>
                                            <input type="input" id="lloan_rec_amount" class="form-control" disabled
                                                value="" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-1">
                                            <label class="form-label">Intrest rate</label>
                                            <input type="input" id="lloan_intrest_rate" class="form-control" disabled
                                                value="" />
                                        </div>
                                    </div>
                                </div>
                                <div id="input-status-message" class="alert"></div>
                                <div class="table-responsive">
                                    <table
                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Disbursal Milestone <span class="text-danger">*</span></th>
                                                <th>Disbursal Amount <span class="text-danger">*</span></th>
                                                <th>Date <span class="text-danger">*</span></th>
                                                <th id="disbursal-action">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table-body-dis"></tbody>
                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                <td>Total:</td>
                                                <td id="total-disbursal-amount">0</td> <!-- Total Disbursal Amount -->
                                                <td></td> <!-- Empty cell for date -->
                                                <td></td> <!-- Empty cell for action -->
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <div id="noteDisburs"></div>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="disburs_da" disabled>Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="viewdocs" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Download Documents</h1>
                    <p class="text-center text-dark fw-bold" id="ass_parad"></p>

                    <div class="row mt-2">

                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="documentsTable"
                                    class="mt-1 table myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Document Name</th>
                                            <th>Download</th>
                                        </tr>
                                    </thead>
                                    <tbody id="documents-tbody"></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal"
                        aria-label="Close">Cancel</button>
                    <button type="button" class="btn btn-primary download_all" id="download-all"
                        onclick="downloadDocumentsZip();">Download All</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="Recovery" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px">
            <form action="{{ route('loan.recovery-schedule') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Recovery
                                Schedule by Field Officer</h4>
                            <p class="mb-0 fw-bold voucehrinvocetxt mt-0" id="ass_parar"></p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pb-2">

                        <div class="row mt-1">

                            <div class="col-md-12">

                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="hidden" name="rid_loan" id="rid_loan" value="">
                                        <div class="mb-1">
                                            <label class="form-label">Loan Amount</label>
                                            <input type="input" id="ramnt_loan" class="form-control" disabled
                                                value="" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-1">
                                            <label class="form-label">Interest Rate <span
                                                    class="text-danger">*</span></label>
                                            <input type="input" name="recovery_interest" id="recovery_interest"
                                                class="form-control" readonly required />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-1">
                                            <label class="form-label">Santioned Int. Rate <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" min="0" name="recovery_sentioned"
                                                id="recovery_sentioned" required class="form-control" value="11" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-1">
                                            <label class="form-label">Repayment Type <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select mw-100" name="recovery_repayment_type"
                                                id="recovery_repayment_type" required>
                                                <option value="" disabled>Select</option>
                                                <option selected value="yearly">Yearly</option>
                                                <option value="half_yearly">Half-Yearly</option>
                                                <option value="monthly">Monthly</option>
                                                <option value="quarterly">Quarterly</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-1">
                                            <label class="form-label">Repayment Period<span
                                                    class="text-danger">*</span></label>
                                            <input type="number" min="0" name="recovery_repayment_period"
                                                id="recovery_repayment_period" required value=""
                                                class="form-control mw-100" />
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-1">
                                            <label class="form-label">Due Date<span class="text-danger">*</span></label>
                                            <input type="date" name="recovery_due_date" id="recovery_due_date"
                                                class="form-control mw-100 future-date" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table
                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th width="100px">Period</th>
                                                <th>Principal Amt.</th>
                                                <th>Interest Amt.</th>
                                                <th>Recovery Date</th>
                                                <th>Total <span class="text-danger">*</span></th>
                                            </tr>
                                        </thead>
                                        <tbody id="repayment-schedule"></tbody>
                                    </table>
                                    <div id="note"></div>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="rec_submit">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Content-->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/js/loan.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

    <script>
        let loanAmntDataCheck = 0;
        let lloanAmntdT = 0;
        let globalDisbursalDates = [];
        $(document).ready(function() {
            $("#dis_percentage").on('input', function() {
                var dis_per = parseFloat($(this).val());

                // Validate the percentage
                if (!/^\d+(\.\d+)?$/.test(dis_per) || dis_per <= 0 || dis_per > 100) {
                    $("#disburs_da").prop('disabled', true);
                    if (dis_per !== '') {
                        alert("Please enter a valid percentage between 1 and 100.");
                    }
                    return;
                }

                var loan_amount = parseFloat($("#lloan_amount").val());

                if (!loan_amount || loan_amount <= 0) {
                    alert("Please enter a valid loan amount.");
                    return;
                }

                // Calculate the number of milestones
                var milestones_count = Math.ceil(100 /
                    dis_per); // Always round up to ensure at least enough rows
                var first_milestone_amount = Math.round(loan_amount * (dis_per /
                    100)); // First milestone amount based on percentage

                // Reset the table body
                var $tableBody = $('#table-body-dis');
                $tableBody.empty();

                // Function to get ordinal suffix
                function getOrdinalSuffix(i) {
                    var j = i % 10,
                        k = i % 100;
                    if (j == 1 && k != 11) {
                        return "st";
                    }
                    if (j == 2 && k != 12) {
                        return "nd";
                    }
                    if (j == 3 && k != 13) {
                        return "rd";
                    }
                    return "th";
                }

                // Create rows for milestones
                var total_amount_distributed = 0;
                var remaining_amount = loan_amount; // Track remaining amount

                for (var i = 1; i <= milestones_count; i++) {
                    var suffix = getOrdinalSuffix(i); // Get the correct ordinal suffix

                    // Calculate the amount for the current milestone
                    var amount_for_milestone = (i === 1) ?
                        first_milestone_amount // First row based on the percentage
                        :
                        Math.floor((loan_amount - first_milestone_amount) / (milestones_count -
                            1)); // Remaining rows evenly distributed

                    // On the last row, adjust for any remaining amount difference
                    if (i === milestones_count) {
                        amount_for_milestone = remaining_amount;
                    }

                    total_amount_distributed += amount_for_milestone; // Track total amount distributed
                    remaining_amount -=
                        amount_for_milestone; // Subtract current milestone from remaining amount

                    // Append row to the table
                    $tableBody.append(
                        '<tr>' +
                        '<td>' + i + '</td>' +
                        '<td><input type="text" name="Disbursal[milestone][]" id="dis_mile_' + i +
                        '" class="form-control mw-100 dis_mile" value="' + i + suffix +
                        ' Milestone"></td>' +
                        '<td><input type="number" value="' + Math.round(amount_for_milestone) +
                        '" name="Disbursal[dis_amount][]" class="form-control mw-100 dis_amnt" id="dis_amnt_' +
                        i + '"></td>' +
                        '<td><input type="date" name="Disbursal[dis_date][]" class="form-control mw-100 dis_date" id="dis_date_' +
                        i + '"></td>' +
                        '</tr>'
                    );
                }

                // Enable the disbursal button
                $("#disburs_da").prop('disabled', false);

                // Function to set global disbursal dates (if required)
                getDate();
            });






            $("#recovery_sentioned").blur(function() {
                var rec_sentioned = parseFloat($(this).val());
                var rec_interest = parseFloat($("#recovery_interest").val());
                if (!isNaN(rec_sentioned) && !isNaN(rec_interest)) {
                    if (rec_sentioned <= rec_interest) {
                        alert('Sanctioned Int. Rate should be greater than Interest Rate.');
                        $(this).val('');
                        return;
                    }
                } else {
                    alert('Please enter valid numbers.');
                    $(this).val('');
                    return;
                }
            });

            $("#disbursal_due_date, #disbursal_date_type").on('change', function() {
                getDate();
            });

            $("#recovery_due_date, #recovery_repayment_type").on('change', function() {
                getRecoveryDate();
            });

            function getRecoveryDate() {
                var $tableBodyDataRec = $('#repayment-schedule');
                var disbursal_date_type_rec = $("#recovery_repayment_type").val();
                var baseDateValueRec = $("#recovery_due_date").val();

                if (!baseDateValueRec) {
                    console.log("Recovery due date is not set");
                    return;
                }

                var baseDateRec = new Date(baseDateValueRec);

                // Clear the globalRecoveryDates array to store new dates
                globalRecoveryDates = [];

                $tableBodyDataRec.find('.recovery_date').each(function(index) {
                    let newDateRecovery = new Date(baseDateRec);

                    switch (disbursal_date_type_rec) {
                        case 'yearly':
                            newDateRecovery.setFullYear(newDateRecovery.getFullYear() + index);
                            break;
                        case 'half_yearly':
                            newDateRecovery.setMonth(newDateRecovery.getMonth() + (6 * index));
                            break;
                        case 'monthly':
                            newDateRecovery.setMonth(newDateRecovery.getMonth() + index);
                            break;
                        case 'quarterly':
                            newDateRecovery.setMonth(newDateRecovery.getMonth() + (3 * index));
                            break;
                    }

                    // Format the date to YYYY-MM-DD
                    const formattedDateRecovery = newDateRecovery.toISOString().split('T')[0];
                    $(this).val(formattedDateRecovery);

                    // Save the formatted date to the global array
                    globalRecoveryDates.push(formattedDateRecovery);
                });
            }

            function getDate() {
                var $tableBodyData = $('#table-body-dis');
                var disbursal_date_type = $("#disbursal_date_type").val();
                var baseDateValue = $("#disbursal_due_date").val();

                var baseDate = new Date(baseDateValue);

                // Clear the globalDisbursalDates array to store new dates
                globalDisbursalDates = [];

                $tableBodyData.find('.dis_date').each(function(index) {
                    let newDate = new Date(baseDate);

                    switch (disbursal_date_type) {
                        case 'yearly':
                            newDate.setFullYear(newDate.getFullYear() + (index + 1));
                            break;
                        case 'half_yearly':
                            newDate.setMonth(newDate.getMonth() + (6 * (index + 1)));
                            break;
                        case 'monthly':
                            newDate.setMonth(newDate.getMonth() + (1 * (index + 1)));
                            break;
                        case 'quarterly':
                            newDate.setMonth(newDate.getMonth() + (3 * (index + 1)));
                            break;
                    }

                    // Format the date to YYYY-MM-DD
                    const formattedDate = newDate.toISOString().split('T')[0];
                    $(this).val(formattedDate);

                    // Save the formatted date to the global array
                    globalDisbursalDates.push(formattedDate);
                });
            }

            $("#dis_valuE").on('blur', function() {
                var dis_valuE = $(this).val();
                var lloan_amountVal = $("#lloan_amount").val();

                // Validate input
                if (!/^\d+(\.\d+)?$/.test(dis_valuE) || dis_valuE <= 0) {
                    $("#disburs_da").prop('disabled', true);
                    alert("Please enter a valid positive value.");
                    return;
                }

                // Validate loan amount
                if (!lloan_amountVal || lloan_amountVal <= 0) {
                    alert("Invalid loan amount.");
                    return;
                }

                var $tableBody = $('#table-body-dis');
                $tableBody.empty(); // Clear previous rows

                // Calculate the number of full milestones
                var numFullMilestones = Math.floor(lloan_amountVal /
                    dis_valuE); // Number of full milestones
                var remainingAmount = lloan_amountVal %
                    dis_valuE; // Remaining amount for the last milestone

                // Set a reasonable limit for the maximum number of rows (if needed)
                var maxRows = 100;
                if (numFullMilestones + (remainingAmount > 0 ? 1 : 0) > maxRows) {
                    alert("Too many milestones. Please adjust the disbursal value.");
                    return;
                }

                // Generate rows for full milestones
                for (var i = 1; i <= numFullMilestones; i++) {
                    var suffix = getOrdinalSuffix(i); // Assuming this function is defined
                    var dateValue = globalDisbursalDates[i - 1] || ''; // Handle date values

                    $tableBody.append(
                        '<tr>' +
                        '<td>' + i + '</td>' +
                        '<td><input type="text" name="Disbursal[milestone][]" class="form-control mw-100 dis_mile" value="' +
                        i + suffix + ' Milestone"></td>' +
                        '<td><input type="number" value="' + dis_valuE +
                        '" name="Disbursal[dis_amount][]" class="form-control mw-100 dis_amnt"></td>' +
                        '<td><input type="date" value="' + dateValue +
                        '" name="Disbursal[dis_date][]" class="form-control mw-100 dis_date"></td>' +
                        '</tr>'
                    );
                }

                // Generate the row for the remaining amount, if any
                if (remainingAmount > 0) {
                    var suffix = getOrdinalSuffix(numFullMilestones + 1);
                    var dateValue = globalDisbursalDates[numFullMilestones] || ''; // Handle date values

                    $tableBody.append(
                        '<tr>' +
                        '<td>' + (numFullMilestones + 1) + '</td>' +
                        '<td><input type="text" name="Disbursal[milestone][]" class="form-control mw-100 dis_mile" value="' +
                        (numFullMilestones + 1) + suffix + ' Milestone"></td>' +
                        '<td><input type="number" value="' + remainingAmount +
                        '" name="Disbursal[dis_amount][]" class="form-control mw-100 dis_amnt"></td>' +
                        '<td><input type="date" value="' + dateValue +
                        '" name="Disbursal[dis_date][]" class="form-control mw-100 dis_date"></td>' +
                        '</tr>'
                    );
                }

                // Enable the submit button after the input
                $("#disburs_da").prop('disabled', false);
                getDate(); // Assuming this function handles dates correctly
            });

            $("#ass_cibil").blur(function() {
                let cibi_score = $(this).val();
                $.ajax({
                    url: '{{ route('get.loan.cibil') }}',
                    method: 'GET',
                    data: {
                        cibi_score: cibi_score
                    },
                    success: function(response) {
                        if (response.success === 0) {
                            alert(response.msg);
                            $("#ass_cibil").val('');
                        } else {
                            $("#ass_cibil").val(response.value);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('An error occurred while fetching the data.');
                    }
                });
            });

            // $("#disburs_da").click(function(){
            // 	let total_dis = 0;
            // 	$(".dis_amnt").each(function() {
            // 		let value = parseFloat($(this).val()) || 0;
            // 		total_dis += value;
            // 	});

            // 	if(lloanAmntdT < total_dis){
            // 		event.preventDefault();
            // 		alert('Sum of Disbursal Amount should be less than Loan Amount.');
            // 		$(".dis_amnt").each(function() {
            // 			$(this).val('');
            // 		});
            // 	}
            // });

            $("#ass_recom_amnt").blur(function() {
                let ass_recom_amnt = $(this).val();
                let amnt_loan_data = $("#amnt_loan").val();
                if (ass_recom_amnt > loanAmntDataCheck) {
                    alert('Recommended loan amount should be less than Loan Amount.');
                    $(this).val('');
                }
            });
            $('#recovery_repayment_period').on('input', function() {
                $("#rec_submit").prop('disabled', true);
                var period = parseInt($(this).val());
                var ramnt_loann = parseInt($("#ramnt_loan").val());
                var recovery_sentionedData = parseFloat($("#recovery_sentioned").val());
                var interestRate = recovery_sentionedData / 100;

                if (isNaN(recovery_sentionedData) || recovery_sentionedData == 0) {
                    alert('Please Enter Sanctioned Int. Rate First.');
                    $("#recovery_repayment_period").val('');
                    return;
                }

                if (!isNaN(period) && period > 0 && !isNaN(ramnt_loann)) {
                    var total_Recovery = Math.round(ramnt_loann / period);
                }

                var $tableBody = $('#repayment-schedule');
                var currentRows = $tableBody.find('tr').length;
                $tableBody.empty();
                if (period > 0) {
                    var remainingPrincipal = ramnt_loann;

                    for (var i = 1; i <= period; i++) {
                        var suffix = getOrdinalSuffix(i);
                        var principalAmount = Math.round(total_Recovery);
                        var interestAmount = Math.round(remainingPrincipal * interestRate);
                        var totalAmount = Math.round(principalAmount + interestAmount);

                        $tableBody.append(
                            '<tr>' +
                            '<td>' + i + '</td>' +
                            '<td><input type="text" name="RecoverySchedule[period][]" class="form-control mw-100" value="' +
                            i + suffix + '" readonly></td>' +
                            '<td><input type="number" value="' + principalAmount +
                            '" name="RecoverySchedule[principal_amnt][]" class="form-control mw-100 principal-amnt principal-amntt" id="principal-amntt" readonly></td>' +
                            '<td><input type="number" value="' + interestAmount +
                            '" name="RecoverySchedule[interest_rate][]" class="form-control mw-100 interest-rate" readonly></td>' +
                            '<td><input type="date" name="RecoverySchedule[recovery_date][]" class="form-control mw-100 recovery_date" readonly></td>' +
                            '<td><input type="number" value="' + totalAmount +
                            '" name="RecoverySchedule[total][]" class="form-control mw-100 total-amount" readonly></td>' +
                            '</tr>'
                        );

                        // Update remaining principal for the next period
                        remainingPrincipal -=
                            principalAmount; // Subtract the principal recovered in the current period
                    }
                    $("#rec_submit").prop('disabled', false);

                    getRecoveryDate();
                }
            });

            // $(document).on('input', '.principal-amnt', function() {
            //     var principalAmount = parseFloat($(this).val());
            //     var interestRate = parseFloat($('#recovery_interest').val());
            // 	var recoverySentioned = parseFloat($('#recovery_sentioned').val());
            //     var $row = $(this).closest('tr');

            //     if (!isNaN(principalAmount) && !isNaN(recoverySentioned)) {
            //         var interestAmount = (principalAmount * recoverySentioned) / 100;
            //         var totalAmount = principalAmount + interestAmount;

            //         $row.find('.interest-rate').val(interestAmount);
            //         $row.find('.total-amount').val(totalAmount);
            //     } else {
            //         $row.find('.interest-rate').val('');
            //         $row.find('.total-amount').val('');
            //     }

            // 	var totalPrincipal = 0;
            // 	$('.principal-amnt').each(function() {
            // 		totalPrincipal += parseFloat($(this).val()) || 0;
            // 	});

            // 	var ramntLoan = parseFloat($('#ramnt_loan').val()) || 0;

            // 	if (totalPrincipal == ramntLoan) {
            // 		$("#rec_submit").prop('disabled', false);
            // 	} else {
            // 		$("#rec_submit").prop('disabled', true);
            // 	}
            // });

            function getOrdinalSuffix(i) {
                var j = i % 10,
                    k = i % 100;
                if (j == 1 && k != 11) {
                    return "st";
                }
                if (j == 2 && k != 12) {
                    return "nd";
                }
                if (j == 3 && k != 13) {
                    return "rd";
                }
                return "th";
            }
            $('tbody').on('click', '#add-bank-row-dis', function(e) {
                e.preventDefault();
                $("#disburs_da").attr('disabled', true);
                var $tbody = $(this).closest('tbody');
                var tbodyId = $tbody.attr('id');
                var clickedClass = $(this).attr('id');
                var $firstTdClass = $(this).closest('tr').find('td:first').attr('id');

                var $currentRow = $(this).closest('tr');
                var $newRow = $currentRow.clone(true, true);

                var isValid = $currentRow.find('input').filter(function() {
                    return $(this).val().trim() !== '';
                }).length > 0;

                if (!isValid) {
                    alert('At least one field must be filled before adding a new row.');
                    return;
                }

                let dis_mile = $currentRow.find('#dis_mile').val();
                let dis_amnt = $currentRow.find('#dis_amnt').val();
                let dis_date = $currentRow.find('#dis_date').val();
                let disbursement_amnt = $("#disbursement_amnt").val();
                let disbursal_percent = $("#dis_percentage").val();
                let lloan_amount = parseFloat($("#lloan_amount").val());
                if (dis_mile == '' || dis_mile == 0 || dis_amnt == 0 || dis_date == '') {
                    alert('Please Filled mandatory fields.');
                    return;
                } else {
                    $("#disburs_da").removeAttr('disabled');
                }

                if (dis_amnt.length > 11) {
                    alert('You have exceeded the 11 character limit at Disbursal Amount.');
                    $currentRow.find('#dis_amnt').val('');
                    $("#disburs_da").prop('disabled', true);
                    return;
                } else {
                    $("#disburs_da").removeAttr('disabled');
                }

                if (disbursement_amnt == 'percent' && disbursal_percent == '') {
                    alert('Please add Disbursal %age First.');
                    $("#disburs_da").prop('disabled', true);
                    return;
                } else {
                    $("#disburs_da").removeAttr('disabled');
                }

                let maxDisbursalAmount = (lloan_amount * disbursal_percent) / 100;
                let totalDisbursalAmount = 0;

                $('#table-body-dis').find('tr').each(function() {
                    let amount = parseFloat($(this).find('.dis_amnt').val());
                    if (!isNaN(amount)) {
                        totalDisbursalAmount += amount;
                    }
                });

                if (totalDisbursalAmount > maxDisbursalAmount) {
                    alert('The total disbursal amount exceeds the allowed limit of ' + maxDisbursalAmount);
                    $("#disburs_da").prop('disabled', true);
                    return;
                } else {
                    $("#disburs_da").removeAttr('disabled');
                }

                if (totalDisbursalAmount < maxDisbursalAmount) {
                    $("#disburs_da").prop('disabled', true);
                }

                if (totalDisbursalAmount == maxDisbursalAmount) {
                    $("#disburs_da").removeAttr('disabled');
                }

                $currentRow.find('input').val('');

                // Update row number for the new row
                var nextIndex = $('#' + tbodyId + ' tr').length + 1;
                $newRow.find('#' + $firstTdClass).text(nextIndex);
                $newRow.find('#' + clickedClass).removeClass(clickedClass).removeAttr('id').removeAttr(
                    'data-class').addClass('text-danger delete-item').html(
                    '<i data-feather="trash-2"></i>');

                $('#' + tbodyId).append($newRow);
                feather.replace();
            });

            $('tbody').on('click', '.delete-item', function(e) {
                e.preventDefault();

                var $tableBody = $(this).closest('tbody');

                $(this).closest('tr').remove();

                var $firstTdId = $(this).closest('tr').find('td:first').attr('id');
                $tableBody.find('tr').each(function(index) {
                    var $rowNumber = $(this).find('#' + $firstTdId);
                    if ($rowNumber.length) {
                        $rowNumber.text(index + 1);
                    }

                    let totalDisbursalAmountDel = 0;
                    let lloan_amountDel = parseFloat($("#lloan_amount").val());
                    let disbursal_percentDel = parseFloat($("#dis_percentage").val());
                    let maxDisbursalAmountDel = (lloan_amountDel * disbursal_percentDel) / 100;

                    $('#table-body-dis').find('tr').each(function() {
                        let amount = parseFloat($(this).find('.dis_amnt').val());
                        if (!isNaN(amount)) {
                            totalDisbursalAmountDel += amount;
                        }
                    });



                    // if ($rowNumber.length && index > 0) {
                    // 	$("#disburs_da").removeAttr('disabled', false);
                    // }else{
                    // 	$("#disburs_da").prop('disabled', true);
                    // }
                    if ($tableBody.find('tr').length > 1) {
                        if (totalDisbursalAmountDel > maxDisbursalAmountDel) {
                            $("#disburs_da").prop('disabled', true);
                        } else if (totalDisbursalAmountDel < maxDisbursalAmountDel) {
                            $("#disburs_da").prop('disabled', true);
                        } else if (totalDisbursalAmountDel == maxDisbursalAmountDel) {
                            $("#disburs_da").removeAttr('disabled');
                        }
                    } else {
                        $("#disburs_da").prop('disabled', true);
                    }
                });
            });

            $("#disbursement_amnt").on('change', function() {
                $("#disburs_da").prop('disabled', 'true');
                var selectedValue = $(this).val();
                if (selectedValue === "percent") {
                    $("#disPer").show();
                    $("#disVal").hide();
                } else {
                    $("#disPer").hide();
                    $("#disVal").show();
                    $("#disVal").removeClass('d-none');
                }

            });
        });
        var baseUrl = "{{ asset('storage/') }}";
        $(document).on('click', '#assess', function() {
            var loanId = $(this).data('loan-id');
            var loanAmnt = $(this).data('loan-amnt');
            loanAmntDataCheck = $(this).data('loan-amnt');
            var loanName = $(this).data('loan-name') || '-';
            var loanCreatedAt = $(this).data('loan-created-at') || '-';
            var createData = loanCreatedAt.split(' ')[0];
            $("#ass_para").html(`${loanName} | ${loanAmnt} | ${createData}`);

            // Set the loan ID and amount in the form
            $("#id_loan").val(loanId);
            $("#amnt_loan").val(loanAmnt);

            $.ajax({
                url: '{{ route('get.loan.assess') }}',
                data: {
                    id: loanId
                },
                dataType: 'json',
                success: function(data) {
                    if (data.assess) {
                        $("#ass_recom_amnt").val(data.assess.ass_recom_amnt || '');
                        $("#ass_cibil").val(data.assess.ass_cibil || '');
                        $("#ass_remarks").val(data.assess.ass_remarks || '');
                        if (data.assess.ass_doc) {
                            var hiddenInputHtml = '<input type="hidden" name="stored_ass_doc" value="' +
                                data.assess.ass_doc + '" class="form-control" />';
                            $("#hidden_inputs").html(hiddenInputHtml);
                            var docUrl = "{{ asset('storage') }}" + '/' + data.assess.ass_doc;
                            var linkHtml = '<a href="' + docUrl +
                                '" target="_blank">Assessment Doc</a>';
                            $("#doc_link").html(linkHtml);
                        }
                    } else {
                        console.log('No assessment data found.');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', status, error);
                }
            });
        });

        $(document).on('click', '#disburs', function() {
            var loanIdd = $(this).data('loan-id');
            var lloanAmnt = $(this).data('loan-amnt');
            var lloanName = $(this).data('loan-name') || '-';
            var intrestRate = $(this).data('intrest-rate') || '-';
            var lloanCreatedAt = $(this).data('loan-created-at') || '-';
            var ccreateData = lloanCreatedAt.split(' ')[0];
            lloanAmntdT = lloanAmnt;
            $("#dis_para").html(`${lloanName} | ${lloanAmnt} | ${ccreateData}`);

            $("#idd_loan").val(loanIdd);


            $.ajax({
                url: '{{ route('get.loan.disbursemnt') }}',
                data: {
                    id: loanIdd
                },
                dataType: 'json',
                success: function(data) {
                    try {
                        var loan_amnt = data.loan_amount.loan_amount;
                        var loan_rec_amnt = data.loan_amount.ass_recom_amnt;
                        var loan_intrest = data.intrest_rate;
                        var total_disbursal = data.total_disbursal;
                        var disbursal_show = data.disbursal_show;

                        $("#lloan_amount").val(loan_amnt);
                        $("#lloan_rec_amount").val(loan_rec_amnt);
                        $("#lloan_intrest_rate").val(loan_intrest);
                        $("#total-disbursal-amount").text(total_disbursal);
                        if (disbursal_show == 0) {
                            $("#disbursal-action").hide();

                        }

                        // $('#disbursement_amnt option').each(function() {
                        //     if ($(this).val() == disbursal_amnt) {
                        //         $(this).prop('selected', true);
                        //     }
                        // });
                        // if (data.loan_amount.disbursal_amnt == 'percent') {
                        //     $("#disPer").show();
                        //     $("#disVal").hide();
                        //     $("#dis_percentage").val(dis_percentage);
                        //     $("#dis_percentage").prop('readonly', true);
                        // } else if (data.loan_amount.disbursal_amnt == 'fixed') {
                        //     $("#disVal").removeClass('d-none');
                        //     $("#disVal").show();
                        //     $("#disPer").hide();
                        //     $("#dis_valuE").val(dis_valuEa);
                        //     $("#dis_valuE").prop('readonly', true);
                        // } else {}
                        // if (dis_Date) {
                        //     $("#disbursal_due_date").val(dis_Date);
                        //     $("#disbursal_due_date").prop('readonly', true);
                        // }
                        $("#table-body-dis").html(data.disburs);
                        // if (data.dis_count > 0) {
                        //     $("#disburs_da").prop('disabled', true);
                        //     $("#disbursement_amnt").prop('disabled', true);
                        //     $("#dis_percentage").prop('disabled', true);
                        //     $("#disbursal_date_type").prop('disabled', true);
                        //     $("#disburs_da").prop('disabled', true);
                        //     var messageDisburs =
                        //         'You have already entered the Disbursal schedule. You are unable to edit it again.';
                        //     $('#noteDisburs').html(
                        //         `<p class="text-center" style="color:green;font-size:12px;"><b>Note:</b> ${messageDisburs}</p>`
                        //     );
                        // } else {
                        //     $("#disburs_da").prop('disabled', false);
                        //     $("#disbursement_amnt").prop('disabled', false);
                        //     $("#dis_percentage").prop('disabled', false);
                        //     $("#disbursal_date_type").prop('disabled', false);
                        //     $("#disburs_da").prop('disabled', false);
                        //     $('#noteDisburs').empty()
                        // }
                    } catch (e) {
                        console.error('Error inserting HTML:', e);
                    }
                    feather.replace();
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', status, error);
                }
            });
        });

        /*========================================*/
        /*========================================*/
        /*=======  Disbursal Schedule  ===========*/
        /*========================================*/
        /*========================================*/
        let nextIndex = $('#table-body-dis tr').length + 1;
        let recAmount = parseInt($("#lloan_rec_amount").val());
        let remainingAmount = recAmount;

        function addNewRow() {
            if (!checkPreviousRowFields()) {
                $('#input-status-message').text("Please fill out all fields in the previous row.")
                    .removeClass('alert-success').addClass('alert-danger');
                return;
            }
            let calculateRow = calculateTotalRowAmount();
            let disAmountValue = calculateRow.disAmountValue;
            if (calculateRow.status == false) {
                return;
            }
            // Disable all previous row inputs
            $('#table-body-dis tr').find('input').prop('readonly', true);

            // Replace the last <td> in the previous row with text or a disabled element
            $('#table-body-dis tr').find('td:last').each(function() {
                $(this).html('<a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>');
            });

            // Create a new row with the necessary inputs
            let $newRow = `<tr>
                <td id="row-number-dis">${nextIndex}</td>
                <td><input type="text" name="Disbursal[milestone][]" id="dis_mile" class="form-control mw-100 dis_mile"></td>
                <td><input type="number" name="Disbursal[dis_amount][]" id="dis_amnt" value="${disAmountValue}" class="form-control mw-100 dis_amnt"></td> 
                <td><input type="date" name="Disbursal[dis_date][]" id="dis_date" class="form-control mw-100 dis_date"></td> 
                <td><a href="#" class="text-primary add-row"><i data-feather="plus-square"></i></a></td>
            </tr>`;

            // Prepend the new row to the table body
            $('#table-body-dis').prepend($newRow);
            feather.replace();
            nextIndex++;

            // Update the remaining amount after adding the new row
            remainingAmount -= disAmountValue;

            recalculateRowNumbers();
            calculateTotalAmount();
        }

        function deleteRow($row) {
            let disAmntValue = parseInt($row.find('.dis_amnt').val()) || 0;
            remainingAmount += disAmntValue;

            $row.remove();
            nextIndex--;
            recalculateRowNumbers();
            calculateTotalAmount();

            // Enable inputs of the new first row
            let firstRow = $('#table-body-dis tr:first');
            let newValue = parseInt(firstRow.find('.dis_amnt').val()) + remainingAmount;
            firstRow.find('.dis_amnt').val(newValue);
            firstRow.find('input').prop('readonly', false);
            $('#disburs_da').prop('disabled', true);
        }

        function recalculateRowNumbers() {
            $('#table-body-dis tr').each(function(index, row) {
                $(row).find('#row-number-dis').text($('#table-body-dis tr').length - index);
            });
        }

        function calculateTotalAmount() {
            let totalCalculated = 0;
            let totalDisabledAmount = 0;

            $('.dis_amnt').each(function() {
                let amount = parseInt($(this).val()) || 0;
                totalCalculated += amount;
            });

            // Select all disabled input fields with class dis_amnt within the table
            $('#table-body-dis input.dis_amnt[readonly]').each(function() {
                let amount = parseFloat($(this).val()) || 0; // Handle empty or invalid inputs
                totalDisabledAmount += amount;
            });
            $('#total-disbursal-amount').text(totalDisabledAmount);

            remainingAmount = recAmount - totalCalculated;

            // if (Math.abs(totalCalculated - recAmount) < 0.01) {
            //     $('#input-status-message').text("Total amount matches the required value.")
            //         .removeClass('alert-danger').addClass('alert-success');
            // } else {
            //     $('#input-status-message').text("Total amount does not match the required value.")
            //         .removeClass('alert-success').addClass('alert-danger');
            // }
            return remainingAmount;
        }

        function calculateTotalRowAmount() {
            // Check and log the value of recAmount
            recAmount = parseInt($("#lloan_rec_amount").val()) || 0;

            let totalPreviousAmount = calculateTotalAmount(); // Total amount from all rows so far

            // Calculate dis_amnt value for the new row
            let disAmountValue = Math.min(remainingAmount, recAmount - totalPreviousAmount);

            // If disAmountValue is still NaN or invalid, set it to 0
            if (isNaN(disAmountValue) || disAmountValue < 0) {
                disAmountValue = 0;
            }

            if (remainingAmount == 0 && disAmountValue == 0) {
                let message = $('#input-status-message').text("No remaining amount to distribute.")
                    .removeClass('alert-success').addClass('alert-danger');
                $('#disburs_da').prop('disabled', false);
                $('#table-body-dis tr').find('input').prop('readonly', true);
                remainingAmount -= disAmountValue;
                return {
                    disAmountValue: remainingAmount,
                    status: true,
                    message: message,
                };
            } else if (disAmountValue == 0) {
                let message = $('#input-status-message').text("Disbursal amount is eqal to Rec amount.")
                    .removeClass('alert-success').addClass('alert-danger');
                return {
                    disAmountValue: remainingAmount,
                    status: false,
                    message: message,
                };
            } else if (remainingAmount == 0) {
                let message = $('#input-status-message').text("No remaining amount to distribute.")
                    .removeClass('alert-success').addClass('alert-danger');
                return {
                    disAmountValue: remainingAmount,
                    status: true,
                    message: message,
                };
            } else {
                return {
                    disAmountValue: remainingAmount,
                    status: true,
                };
            }

        }

        function checkPreviousRowFields() {
            let allFilled = true;
            $('#table-body-dis tr:first input').each(function() {
                if ($(this).val() === '') {
                    allFilled = false;
                }
            });
            return allFilled;
        }

        $(document).on('click', '.add-row', function(e) {
            e.preventDefault();
            addNewRow();
        });

        $(document).on('click', '.delete-row', function(e) {
            e.preventDefault();
            let $row = $(this).closest('tr');
            deleteRow($row);
        });

        $(document).on('input', '.dis_amnt', function() {
            calculateTotalAmount();
        });

        $(document).on('click', '#disburs_da', function(e) {
            e.preventDefault();

            let totalDisabledAmount = 0;
            let totalEnabledAmount = 0;

            $('#table-body-dis input.dis_amnt[readonly]').each(function() {
                let amount = parseFloat($(this).val()) || 0; // Handle empty or invalid inputs
                totalDisabledAmount += amount;
            });

            // Calculate the total of enabled dis_amnt inputs
            $('#table-body-dis input.dis_amnt:not([readonly])').each(function() {
                let amount = parseFloat($(this).val()) || 0; // Handle empty or invalid inputs
                totalEnabledAmount += amount;
            });

            if (totalDisabledAmount == recAmount && totalEnabledAmount == 0) {
                $('#table-body-dis tr').each(function() {
                    // Find the dis_amount input in the current row
                    let amountInput = $(this).find('input[name="Disbursal[dis_amount][]"]');

                    // Check if amountInput exists
                    if (amountInput.length) {
                        let amountValue = parseInt(amountInput.val()); // Use parseFloat for decimal values

                        // Check if the value is 0 or NaN (which is the case if the value is null or empty)
                        if (amountValue === 0 || isNaN(amountValue)) {
                            $(this).remove(); // Remove the entire row if the condition is met
                        }
                    }
                });

                $('#disbursement-form').submit();
            } else {
                $('#input-status-message').text(
                        `The Disbursal Amount of ${totalEnabledAmount} cannot be greater than the Rec Amount of ${recAmount}.`
                    )
                    .removeClass('alert-success').addClass('alert-danger');
            }

        });

        $(document).ready(function() {
            let firstRow = $('#table-body-dis tr:first').find('.dis_amnt');
            if (firstRow.length && !firstRow.val()) {
                firstRow.val(recAmount.toFixed(2));
                remainingAmount = 0;
            }
            calculateTotalAmount();
        });

        /*========================================*/
        /*========================================*/
        /*=======  End Disbursal Schedule  =======*/
        /*========================================*/
        /*========================================*/

        $(document).on('click', '#docc', function() {
            var loanIdDoc = $(this).data('loan-id');
            var dloanAmnt = $(this).data('loan-amnt');
            var dloanName = $(this).data('loan-name') || '-';
            var dloanCreatedAt = $(this).data('loan-created-at') || '-';
            var dcreateData = dloanCreatedAt.split(' ')[0];
            $("#ass_parad").html(`${dloanName} | ${dloanAmnt} | ${dcreateData}`);
            $.ajax({
                url: '{{ route('get.loan.docc') }}',
                data: {
                    id: loanIdDoc
                },
                dataType: 'json',
                success: function(data) {
                    $('#documents-tbody').html(data.doc);
                    feather.replace();
                    var linksData = document.querySelectorAll('#documents-tbody tr td a');
                    if (linksData.length === 0) {
                        $("#download-all").prop('disabled', true);
                    } else {
                        $("#download-all").removeAttr('disabled');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', status, error);
                }
            });
        });

        $(document).on('click', '#r_schedule', function() {
            var rloanId = $(this).data('loan-id');
            var rloanAmnt = $(this).data('loan-amnt');
            var rloanName = $(this).data('loan-name') || '-';
            var rloanCreatedAt = $(this).data('loan-created-at') || '-';
            var rcreateData = rloanCreatedAt.split(' ')[0];
            $("#ass_parar").html(`${rloanName} | ${rloanAmnt} | ${rcreateData}`);

            $("#rid_loan").val(rloanId);
            $("#ramnt_loan").val(rloanAmnt);

            $.ajax({
                url: '{{ route('get.loan.recovery.schedule') }}',
                data: {
                    id: rloanId
                },
                dataType: 'json',
                success: function(data) {
                    $("#repayment-schedule").html('');
                    $("#repayment-schedule").html(data.recovery_data);
                    $("#recovery_sentioned").val(data.loan_data.recovery_sentioned);
                    $("#recovery_repayment_period").val(data.loan_data.recovery_repayment_period);
                    $("#recovery_interest").val(data.interest_rate);
                    $("#recovery_due_date").val(data.loan_data.recovery_due_date);
                    if (data.rec_count > 0) {
                        $("#recovery_sentioned").prop('readonly', true);
                        $("#recovery_repayment_type").prop('disabled', true);
                        $("#recovery_repayment_period").prop('readonly', true);
                        $("#recovery_due_date").prop('readonly', true);
                        $("#rec_submit").prop('disabled', true);
                        var message =
                            'You have already entered the recovery schedule. You are unable to edit it again.';
                        $('#note').html(
                            `<p class="text-center" style="color:green;font-size:12px;"><b>Note:</b> ${message}</p>`
                        );
                    } else {
                        $("#recovery_sentioned").prop('readonly', false);
                        $("#recovery_repayment_type").prop('disabled', false);
                        $("#recovery_repayment_period").prop('readonly', false);
                        $("#recovery_due_date").prop('readonly', false);
                        $("#rec_submit").prop('disabled', false);
                        $('#note').empty()
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', status, error);
                }
            });
        });

        function downloadDocumentsZip() {
            var zip = new JSZip();
            var hasDocuments = false;

            // Select all <a> tags inside <tr> > <td> inside #documents-tbody
            var links = document.querySelectorAll('#documents-tbody tr td a');

            if (links.length === 0) {
                alert('No documents available to download.');
                return;
            } else {
                $("#download-all").removeAttr('disabled');
            }

            var linksProcessed = 0;

            links.forEach(function(link, index) {
                if (link.href.length > 0) {
                    hasDocuments = true;
                    // Fetch the document content
                    fetch(link.href)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.blob();
                        })
                        .then(blob => {
                            // Add to zip file
                            var fileName = `document_${index + 1}.${link.href.split('.').pop()}`;
                            zip.file(fileName, blob);

                            linksProcessed++;
                            // Check if all files are added
                            if (linksProcessed === links.length) {
                                zip.generateAsync({
                                        type: 'blob'
                                    })
                                    .then(function(content) {
                                        // Trigger download
                                        saveAs(content, 'application_documents.zip');
                                    });
                            }
                        })
                        .catch(error => console.error('Error downloading file:', error));
                }
            });

            if (!hasDocuments) {
                alert('No valid documents to download.');
            }
        }



        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
        $(function() {
            var dt_basic_table = $('.datatables-basic'),
                dt_date_table = $('.dt-date'),
                dt_complex_header_table = $('.dt-complex-header'),
                dt_row_grouping_table = $('.dt-row-grouping'),
                dt_multilingual_table = $('.dt-multilingual'),
                assetPath = '../../../app-assets/';
            if ($('body').attr('data-framework') === 'laravel') {
                assetPath = $('body').attr('data-asset-path');
            }

            // DataTable with buttons
            // --------------------------------------------------------------------

            var keyword = '';
            if (dt_basic_table.length) {
                var dt_basic = dt_basic_table.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('loan.index') }}",
                        data: function(d) {
                            d.date = $("#fp-range").val(),
                                d.ledger = $("#filter-ledger-name").val(),
                                d.status = $("#filter-status").val(),
                                d.type = $("#filter-ledger-type").val(),
                                d.keyword = $('#DataTables_Table_0_filter input').val()
                        }
                    },
                    columns: [{
                            data: null,
                            className: 'dt-center',
                            defaultContent: '<div class="form-check form-check-inline"><input class="form-check-input row-checkbox" type="checkbox"></div>',
                            orderable: false
                        },
                        {
                            data: 'appli_no',
                            name: 'appli_no'
                        },
                        {
                            data: 'ref_no',
                            name: 'ref_no'
                        },
                        {
                            data: 'proceed_date',
                            name: 'proceed_date'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'email',
                            name: 'email'
                        },
                        {
                            data: 'mobile',
                            name: 'mobile'
                        },
                        {
                            data: 'type',
                            name: 'type'
                        },
                        {
                            data: 'loan_amount',
                            name: 'loan_amount'
                        },
                        {
                            data: 'ass_recom_amnt',
                            name: 'ass_recom_amnt'
                        },
                        {
                            data: 'age',
                            name: 'age'
                        },
                        {
                            data: 'status',
                            name: 'status'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    drawCallback: function() {
                        feather.replace();
                    },
                    dom: 'Bfrtip',
                    order: [
                        [0, 'desc']
                    ],
                    dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 7,
                    lengthMenu: [7, 10, 25, 50, 75, 100],
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
                                    title: 'Loan Applications Report Print'
                                },
                                {
                                    extend: 'csv',
                                    text: feather.icons['file-text'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Csv',
                                    className: 'dropdown-item',
                                    filename: 'Loan_Applications_Report_CSV',
                                    exportOptions: {
                                        columns: ':not(:first-child):not(:last-child)'
                                    },
                                    title: 'Loan Application Report'
                                },
                                {
                                    extend: 'excel',
                                    text: feather.icons['file'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Excel',
                                    className: 'dropdown-item',
                                    filename: 'Loan_Applications_Report_Excel',
                                    exportOptions: {
                                        columns: ':not(:first-child):not(:last-child)'
                                    },
                                    title: 'Loan Application Report'
                                },
                                {
                                    extend: 'pdf',
                                    text: feather.icons['clipboard'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Pdf',
                                    className: 'dropdown-item',
                                    filename: 'Loan_Applications_Report_PDF',
                                    exportOptions: {
                                        columns: ':not(:first-child):not(:last-child)'
                                    },
                                    customize: function(doc) {
                                        var colCount = doc.content[1].table.body[0].length;
                                        doc.content[1].table.widths = Array(colCount).fill('*');
                                    },
                                    orientation: 'landscape',
                                    pageSize: 'A4',
                                    title: 'Loan Application Report'
                                },
                                {
                                    extend: 'copy',
                                    text: feather.icons['copy'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Copy',
                                    className: 'dropdown-item',
                                    title: 'Loan_Applications_Report_COPY'
                                }
                            ],
                            init: function(api, node, config) {
                                $(node).removeClass('btn-secondary');
                                $(node).parent().removeClass('btn-group');
                                setTimeout(function() {
                                    $(node).closest('.dt-buttons').removeClass('btn-group')
                                        .addClass('d-inline-flex');
                                }, 50);
                            }
                        },

                    ],
                    language: {
                        paginate: {
                            // remove previous & next text from pagination
                            previous: '&nbsp;',
                            next: '&nbsp;'
                        }
                    }
                });
                $('div.head-label').html('<h6 class="mb-0">Event List</h6>');
            }

            // Flat Date picker
            if (dt_date_table.length) {
                dt_date_table.flatpickr({
                    monthSelectorType: 'static',
                    dateFormat: 'm/d/Y'
                });
            }

            // Filter record
            $(".apply-filter").on("click", function() {
                // Redraw the table
                dt_basic.draw();

                // Remove the custom filter function to avoid stacking filters
                // $.fn.dataTable.ext.search.pop();

                // Hide the modal
                $(".modal").modal("hide");
            });

            $('#DataTables_Table_0_filter input').on('keyup change', function() {
                dt_basic.draw(); // Redraw DataTable with new search value
            });

            // Delete Record
            $('.datatables-basic tbody').on('click', '.delete-record', function() {
                dt_basic.row($(this).parents('tr')).remove().draw();
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Select all input fields of type number
            const numberInputs = document.querySelectorAll('input[type="number"]');

            // Loop through each input field
            numberInputs.forEach(function(input) {
                // Add an input event listener to each number input
                input.addEventListener('input', function() {
                    // If the value is negative, set it to its absolute value
                    if (this.value < 0) {
                        this.value = Math.abs(this.value);
                    }
                });

                // Add a blur event listener to ensure no negative values on losing focus
                input.addEventListener('blur', function() {
                    if (this.value < 0) {
                        this.value = Math.abs(this.value);
                    }
                });
            });
        });

        function checkFileTypeandSize(event) {
            const file = event.target.files[0];

            if (file) {
                const maxSizeMB = 5;
                const fileSizeMB = file.size / (1024 * 1024);

                const videoExtensions = /(\.mp4|\.avi|\.mov|\.wmv|\.mkv)$/i;
                if (videoExtensions.exec(file.name)) {
                    alert("Video files are not allowed.");
                    event.target.value = "";
                    return;
                }

                if (fileSizeMB > maxSizeMB) {
                    alert("File size should not exceed 5MB.");
                    event.target.value = "";
                    return;
                }
            }
        }


        document.addEventListener('DOMContentLoaded', function() {
            const textareas = document.querySelectorAll('textarea');

            textareas.forEach(function(textarea) {
                function enforceCharacterLimit() {
                    if (this.value.length > 500) {
                        alert(
                            'You have exceeded the 500 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 500);
                    }
                }

                textarea.addEventListener('input', enforceCharacterLimit);
                textarea.addEventListener('blur', enforceCharacterLimit);
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const textInputs = document.querySelectorAll('input[type="number"]');

            textInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    if (this.value.length > 11) {
                        alert(
                            'You have exceeded the 11 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 11);
                    }
                });

                input.addEventListener('blur', function() {
                    if (this.value.length > 11) {
                        alert(
                            'You have exceeded the 11 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 11);
                    }
                });
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            const textInputs = document.querySelectorAll('input[type="text"]');

            textInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    if (this.value.length > 250) {
                        alert(
                            'You have exceeded the 250 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 250);
                    }
                });

                input.addEventListener('blur', function() {
                    if (this.value.length > 250) {
                        alert(
                            'You have exceeded the 250 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 250);
                    }
                });
            });
        });

        function checkDecimalValue(input) {
            if (input.value.includes('.')) {
                alert("Decimal values are not allowed.");
                input.value = "";
            }
        }

        document.getElementById('dis_percentage').addEventListener('input', function() {
            checkDecimalValue(this);
        });


        function checkTableBody() {
            var rowCount = $('#table-body-dis tr').length;
            console.log(rowCount);
            if (rowCount < 0) {
                $('#disburs_da').attr('disabled', true);
            } else {
                $('#disburs_da').removeAttr('disabled');
            }
        }

        checkTableBody();

        $('#table-body-dis').on('DOMNodeInserted DOMNodeRemoved', function() {
            checkTableBody();
        });


        document.addEventListener('DOMContentLoaded', function() {
            const pastDateInputs = document.querySelectorAll('.past-date');
            const futureDateInputs = document.querySelectorAll('.future-date');

            function readonlyates() {
                const today = new Date().toISOString().split('T')[0];

                pastDateInputs.forEach(input => {
                    input.setAttribute('max', today);
                });

                futureDateInputs.forEach(input => {
                    input.setAttribute('min', today);
                });
            }
            readonlyates();
        });

        document.addEventListener('DOMContentLoaded', function() {
            const appliNoInput = document.getElementById('document_no');
            const errorMessage = document.getElementById('document_no_error_message');
            const appli_span = document.getElementById('document_no_span')

            function validateAppliNo() {
                const value = appliNoInput.value.trim();

                // Check if the string starts with a negative sign
                if (value.startsWith('-')) {
                    appli_span.textContent = '';
                    errorMessage.textContent = 'The Document number must not start with a negative sign.';
                    return false;
                }

                // Check if the string contains only allowed characters (letters, numbers, and dashes)
                const regex = /^[a-zA-Z0-9-_]+$/;
                if (!regex.test(value)) {
                    appli_span.textContent = '';
                    errorMessage.textContent =
                        'The Document number can only contain letters, numbers, dashes and underscores.';
                    return false;
                }

                // If all checks pass, clear the error message
                errorMessage.textContent = '';
                return true;
            }

            // Validate on blur
            appliNoInput.addEventListener('blur', validateAppliNo);
        });
    </script>
@endsection
