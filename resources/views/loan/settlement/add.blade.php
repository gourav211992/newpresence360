@extends('layouts.app')

@section('content')

    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">New Settlement</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Add New</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</button>
                            <button class="btn btn-primary btn-sm mb-50 mb-sm-0" form="settle-add-update"><i
                                    data-feather="check-circle"></i> Submit</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                <form action="{{ route('loan.settlement.save') }}" method="POST" enctype="multipart/form-data"
                    id="settle-add-update">
                    @csrf

                    <input type ="hidden" name="book_code" id ="book_code_input">
                    <input type="hidden" name="doc_number_type" id="doc_number_type">
                    <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
                    <input type="hidden" name="doc_prefix" id="doc_prefix">
                    <input type="hidden" name="doc_suffix" id="doc_suffix">
                    <input type="hidden" name="doc_no" id="doc_no">
                    @if ($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
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
                                    <div class="card-body customernewsection-form">


                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>


                                            </div>

                                        </div>


                                        <div class="row">

                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select" name="book_id" id="book_id" required>
                                                            <option value="">Select</option>
                                                            @if (isset($book_type))
                                                                @foreach ($book_type as $key => $val)
                                                                    <option value="{{ $val->id }}">
                                                                        {{ $val->book_name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="settle_document_no"
                                                            id="settle_document_no" required>
                                                        <span id="settle_document_no_error_message"
                                                            class="text-danger"></span>
                                                        <span id="settle_document_no_span"></span>
                                                        @error('settle_document_no')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Application No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select" name="settle_application_no"
                                                            id="settle_application_no" required>
                                                            <option value="">Select</option>
                                                            @if (isset($applicants))
                                                                @foreach ($applicants as $key => $val)
                                                                    <option value="{{ $val->id }}"
                                                                        {{ isset($recovery->home_loan_id) && $recovery->home_loan_id == $val->id ? 'selected' : '' }}>
                                                                        {{ $val->appli_no }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Customer <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" readonly id="settle_customer"
                                                            name="settle_customer" class="form-control" value="">
                                                        @error('settle_customer')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Loan Type <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" readonly id="settle_loan_type"
                                                            name="settle_loan_type" value="" class="form-control">
                                                        @error('settle_loan_type')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-4" hidden>

                                                <div
                                                    class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                                    <h5 class="mb-2 text-dark border-bottom pb-50">
                                                        <strong><i data-feather="arrow-right-circle"></i> Approval
                                                            History</strong>
                                                    </h5>
                                                    <ul class="timeline ms-50 newdashtimline ">
                                                        <li class="timeline-item">
                                                            <span class="timeline-point timeline-point-indicator"></span>
                                                            <div class="timeline-event">
                                                                <div
                                                                    class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                    <h6>Aniket Singh</h6>
                                                                    <span
                                                                        class="badge rounded-pill badge-light-danger">Rejected</span>
                                                                </div>
                                                                <h5>(2 min ago)</h5>
                                                                <p>Description will come here</p>
                                                            </div>
                                                        </li>
                                                        <li class="timeline-item">
                                                            <span
                                                                class="timeline-point timeline-point-warning timeline-point-indicator"></span>
                                                            <div class="timeline-event">
                                                                <div
                                                                    class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                    <h6>Deewan Singh</h6>
                                                                    <span
                                                                        class="badge rounded-pill badge-light-warning">Pending</span>
                                                                </div>
                                                                <h5>(5 min ago)</h5>
                                                                <p>Description will come here</p>
                                                            </div>
                                                        </li>
                                                        <li class="timeline-item">
                                                            <span
                                                                class="timeline-point timeline-point-info timeline-point-indicator"></span>
                                                            <div class="timeline-event">
                                                                <div
                                                                    class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                    <h6>Brijesh Kumar</h6>
                                                                    <span
                                                                        class="badge rounded-pill badge-light-success">Approved</span>
                                                                </div>
                                                                <h5>(10 min ago)</h5>
                                                                <p>Description will come here</p>
                                                            </div>
                                                        </li>
                                                        <li class="timeline-item">
                                                            <span
                                                                class="timeline-point timeline-point-danger timeline-point-indicator"></span>
                                                            <div class="timeline-event">
                                                                <div
                                                                    class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                    <h6>Deepender Singh</h6>
                                                                    <span
                                                                        class="badge rounded-pill badge-light-success">Approved</span>
                                                                </div>
                                                                <h5>(5 day ago)</h5>
                                                                <p><a href="#"><i data-feather="download"></i></a>
                                                                    Description will come here </p>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>

                                            </div>

                                        </div>

                                        <div class="row">

                                            <div class="col-md-12 revisedvalue mt-2">
                                                <div
                                                    class="newheader d-flex justify-content-between align-items-end mb-1 border-bottom pb-25">
                                                    <div class="header-left">
                                                        <h4 class="card-title text-theme">Loan Detail</h4>
                                                    </div>
                                                </div>

                                            </div>


                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Loan Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" value="" name="loan_amount"
                                                            id="loan_amount" disabled class="form-control">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Disburse Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" id="dis_amount" name="dis_amount"
                                                            value="" readonly class="form-control">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Recovered Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" id="rec_amnt" name="rec_amnt"
                                                            value="" readonly class="form-control">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Interest Recieved Till Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" value="" id="rec_intrst"
                                                            name="rec_intrst" readonly class="form-control">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Bal. Loan Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" id="settle_bal_loan_amnnt" readonly
                                                            name="settle_bal_loan_amnnt" value="" required
                                                            class="form-control">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3" hidden>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Bal. Interest Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input id="settle_intr_bal_amnnt" name="settle_intr_bal_amnnt"
                                                            type="text" readonly class="form-control">
                                                    </div>

                                                </div>

                                            </div>



                                            <div class="col-md-3 finalvalue">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Settlement Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" class="form-control" oninput="settle()"
                                                            id="settle_amnnt" name="settle_amnnt">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3 finalvalue">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Write off Amt. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" id="settle_wo_amnnt" name="settle_wo_amnnt"
                                                            readonly class="form-control">
                                                    </div>

                                                </div>

                                            </div>



                                        </div>




                                        <div class="row my-2">


                                            <div class="col-md-8 revisedvalue">
                                                <div
                                                    class="newheader d-flex justify-content-between align-items-end mb-1 border-bottom pb-25">
                                                    <div class="header-left">
                                                        <h4 class="card-title text-theme">Settlement Schedule</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>

                                                <div class="table-responsive-md mb-1">


                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Date</th>
                                                                <th>Amount</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="table-body-settle">
                                                            <tr>
                                                                <td id="row-number-settle">1</td>
                                                                <td><input required type="date"
                                                                        name="Settlement[schedule_date][]"
                                                                        id="schedule_date_0"
                                                                        class="form-control mw-100 past-date"></td>
                                                                <td><input required type="text" step="any"
                                                                        value="" name="Settlement[schedule_amnt][]"
                                                                        id="schedule_amnt_0"
                                                                        class="schedule_amnt form-control mw-100">
                                                                </td>
                                                                <td><a href="#" onClick="addRow()"
                                                                        class="add-bank-row-settle"
                                                                        id="add-bank-row-settle"
                                                                        data-class="add-bank-row-settle"><i
                                                                            data-feather="plus-square"></i></a></td>
                                                            </tr>


                                                        </tbody>


                                                    </table>



                                                </div>
                                            </div>

                                        </div>

                                        <div class="row">

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Upload Document</label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="file" class="form-control">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-6">
                                                <div class="row  mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Remarks</label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" name="remarks" class="form-control" />
                                                    </div>

                                                </div>


                                            </div>



                                        </div>





                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal to add new record -->

                    </section>
                </form>


            </div>
        </div>
    </div>>
    <!-- END: Content-->

    <script type="text/javascript">
        var getSeriesUrl = "{{ url('loan/get-series') }}";
        var getvoucherUrl = "{{ url('/get_voucher_no') }}".trim();
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/js/loan.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

    <script>
        let ballance_amnt = '';

        $('#book_id').on('change', function() {
            getDocNumberByBookId();
            /*var book_id = $(this).val();
            var request = $('#settle_document_no');
            request.val('');
            if (book_id) {
                $.ajax({
                    url: "{{ url('/loan/get-loan-request') }}/" + book_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        if (data.requestno == 1) {
                            request.prop('readonly', false);
                        } else {
                            request.prop('readonly', true);
                            request.val(data.requestno);
                        }
                    }
                });
            }*/
        });






        $('#fileInput').on('change', function() {
            var files = this.files;
            var $fileList = $('#fileList');

            // Loop through selected files
            $.each(files, function(index, file) {
                var fileSize = (file.size / 1024).toFixed(2) + ' KB'; // File size in KB
                var fileName = file.name;
                var fileExtension = fileName.split('.').pop()
                    .toUpperCase(); // Get file extension and make it uppercase

                // Create a new image-uplodasection div
                var $fileDiv = $('<div class="image-uplodasection mb-2"></div>');
                var $fileIcon = $('<i data-feather="file" class="fileuploadicon"></i>');
                var $fileName = $('<span class="file-name d-block"></span>').text(
                    fileExtension + ' file').css('font-size', '10px'); // Display extension
                var $fileInfo = $('<span class="file-info d-block"></span>').text(fileSize).css(
                    'font-size', '10px'); // Display file size on the next line
                var $deleteDiv = $(
                    '<div class="delete-img text-danger"><i data-feather="x"></i></div>');

                $fileDiv.append($fileIcon).append($fileName).append($fileInfo).append(
                    $deleteDiv);
                $fileList.append($fileDiv);
                feather.replace();
            });
        });

        // Optional: Handle delete button click to remove the fileDiv
        $(document).on('click', '.delete-img', function() {
            $(this).closest('.image-uplodasection').remove();
        });
        $(document).on('change', '#settle_application_no', function() {

            var customerID = $(this).val();
            //console.log(customerID);
            if (customerID != "") {

                $.ajax({
                    url: '{{ route('loan.settlement.customer') }}',
                    data: {
                        id: customerID
                    },
                    dataType: 'json',
                    success: function(data) {
                        let loanData = 'Loan';
                        if (data.customer_record.type == 1) {
                            loanData = 'Home ' + loanData;
                        } else if (data.customer_record.type == 2) {
                            loanData = 'Vehicle ' + loanData;
                        } else if (data.customer_record.type == 3) {
                            loanData = 'Term ' + loanData;
                        }

                        let dueDate = data.due_date;
                        let recoverySentioned = data.customer_record.recovery_sentioned;

                        var totalDisbursement = data.customer_record.loan_disbursements
                            .reduce(
                                function(sum, disbursement) {
                                    if (disbursement.approvalStatus === "Disbursed") {
                                        if (disbursement.actual_dis)
                                            return sum + parseFloat(removeCommas(disbursement.actual_dis));
                                        else
                                            return sum + parseFloat(removeCommas(disbursement.dis_amount));
                                    }
                                    return sum;
                                },
                                0
                            );

                        var totalInterest = data.customer_record.loan_appraisal.recovery
                            .reduce(
                                function(sum, recover) {

                                    return sum + parseFloat(removeCommas(recover.interest_amount)); // Convert to float and accumulate
                                }, 0);
                        var totalRecovery = 0;
                        var totalIntrestReceived = 0;
                        let intrest = parseFloat(removeCommas($('#settle_intr_bal_amnnt').val())) || 0;
                        totalRecovery = parseFloat(removeCommas(data.customer_record.recovery_loan_amount)) || 0;
                        console.log(data.customer_record.recovery_loan_amount);
                        var totalIntrestReceived = parseFloat(removeCommas(data.customer_record.recovery_ia)) || 0;

                        var totalSettlement = 0;

                        console.log(data.customer_record.loan_settlement.length)

                        if (data.customer_record.loan_settlement.length > 0) {
                            totalSettlement = data.customer_record.loan_settlement
                                .reduce(
                                    function(sum, settle) {
                                        return sum + parseFloat(removeCommas(settle.settle_amnnt)); // Convert to float and accumulate
                                    }, 0);
                        }

                        totalInterest = totalInterest - totalIntrestReceived;
                        let repayment_dur = data.customer_record.loan_appraisal
                            .repayment_start_period;
                        let repayment_type = data.customer_record.loan_appraisal
                            .repayment_type;
                        let rep_month = repayment_dur;


                        let settle_bal_loan_amnnt = removeCommas(data.customer_record.loan_appraisal
                            .term_loan) - totalRecovery;



                        $("#settle_customer").val(data.customer_record.name);
                        $("#settle_loan_type").val(loanData);
                        //console.log(data.customer_record);

                        let amount = removeCommas(data.customer_record.loan_appraisal.term_loan);

                        if (!isIndianFormatted(amount)) {
                            $("#loan_amount").val(formatIndianNumber(amount));
                        } else {
                            $("#loan_amount").val(amount); // Already formatted
                        }
                  
                        if (!isIndianFormatted(totalDisbursement))
                        {
                            $("#dis_amount").val(formatIndianNumber(totalDisbursement));
                        } else {
                            $("#dis_amount").val(totalDisbursement); // Already formatted
                        }
                        if (!isIndianFormatted(totalIntrestReceived))
                        {
                            $("#rec_intrst").val(formatIndianNumber(totalIntrestReceived));
                        } else {
                            $("#rec_intrst").val(totalIntrestReceived); // Already formatted
                        }
                  
                        if (!isIndianFormatted(settle_bal_loan_amnnt))
                        {
                            $("#settle_bal_loan_amnnt").val(formatIndianNumber(settle_bal_loan_amnnt||0));
                        } else {
                            $("#settle_bal_loan_amnnt").val(settle_bal_loan_amnnt||0); // Already formatted
                        }
                  
                        if (!isIndianFormatted(totalRecovery))
                        {
                            $("#rec_amnt").val(formatIndianNumber(totalRecovery));
                        } else {
                            $("#rec_amnt").val(totalRecovery); // Already formatted
                        }

                        $("#settle_intr_bal_amnnt").val(totalInterest.toFixed(2));



                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error:', status, error);
                    }
                });
            }
        });

        function isIndianFormatted(number) {
            const indianFormatRegex = /^\d{1,3}(,\d{2})*(\.\d{2})?$/;
            return indianFormatRegex.test(number.toString());
        }




        $(document).on('input', '.principal-amnt', function() {
            var principalAmount = parseFloat(removeCommas($(this).val()));
            var interestRate = parseFloat(removeCommas($('#recovery_interest').val()));
            var $row = $(this).closest('tr');

            if (!isNaN(principalAmount) && !isNaN(interestRate)) {
                var interestAmount = (principalAmount * interestRate) / 100;
                var totalAmount = principalAmount + interestAmount;

                $row.find('.interest-rate').val(interestAmount);
                $row.find('.total-amount').val(totalAmount);
            } else {
                $row.find('.interest-rate').val('');
                $row.find('.total-amount').val('');
            }
        });

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


        $('tbody').on('click', '.delete-item', function(e) {
            e.preventDefault();

            var $tableBody = $(this).closest('tbody');

            $(this).closest('tr').remove();
            //setSettlementAmount();

            var $firstTdId = $(this).closest('tr').find('td:first').attr('id');
            $tableBody.find('tr').each(function(index) {
                var $rowNumber = $(this).find('#' + $firstTdId);
                if ($rowNumber.length) {
                    $rowNumber.text(index + 1);
                }

            });
            var $firstRow = $tableBody.find('tr').first();

            // Remove any existing delete icon
            $firstRow.find('.add-bank-row-settle').remove();

            $firstRow.find('td:last').append(
                `
<a href="#" class="add-bank-row-settle" id="add-bank-row-settle" onClick="addRow()" data-class="add-bank-row-settle"><i data-feather="plus-square"></i></a>`
            );

            // Check if only one row is left
            if ($tableBody.find('tr').length === 1) {
                $firstRow.find('.delete-item').remove();
            }
            feather.replace();
            setreadonly();
        });

        function addRow() {

            var rowCount = $("#table-body-settle").find('tr').length + 1;
            var totalAmount = parseFloat(removeCommas($('#settle_amnnt').val()));
            var paidAmount = 0;

            $('#table-body-settle tr').each(function() {
                // Get the value of schedule_amnt in each row and add it to paidAmount
                let amount = parseFloat(removeCommas($(this).find('.schedule_amnt').val())) || 0;
                paidAmount += amount;
            });

            var balanceAmount = totalAmount - paidAmount;

            var newRow = `
<tr>
                                        <td id="row-number-settle">${rowCount}</td>
                                        <td><input required type="date"
                                                name="Settlement[schedule_date][]" step="any" id="schedule_date_${rowCount-1}"
                                                class="form-control mw-100 past-date"></td>
                                        <td><input required type="text"  value="${formatIndianNumber(balanceAmount)}"
                                                name="Settlement[schedule_amnt][]"  id="schedule_amnt_${rowCount-1}"
                                                class="form-control schedule_amnt mw-100"></td>

                                        <td><a href="#"
                                                                    class="text-danger delete-item"><i
                                                                        data-feather="trash-2"></i></a>
                                                                        </td>
                                    </tr>`;

            if (balanceAmount > 0) {
                if (!$("#table-body-settle tr:first .delete-item").length) {
                    $("#table-body-settle tr:first td:last").append(`
    <a href="#" class="text-danger delete-item"><i data-feather="trash-2"></i></a>
`);
                    feather.replace();
                }
                $("#table-body-settle").append(newRow);
                setreadonly();

                feather.replace();


            }
        }

        function setreadonly() {
            var rowCount = $("#table-body-settle").find('tr').length;
            $('#table-body-settle tr').each(function(index) {
                var $input = $(this).find('.schedule_amnt');
                if (index === rowCount - 1) {
                    $input.removeAttr("readonly");
                } else {
                    $input.attr("readonly", true);
                }
            });

        }

        $('#settle_amnnt').on('blur', function() {
           // Get the value of the input field
        var amount = $(this).val()||0;

        // Format the value with commas before saving or processing it
        var formattedAmount = formatIndianNumber(removeCommas(amount));

        // Set the formatted value back into the input field
        $(this).val(formattedAmount);
        });

        function settle() {
            // Get settlement and balance loan amounts from input fields
            let settleAmount = parseFloat(removeCommas($('#settle_amnnt').val())) ||0;
            let balanceLoanAmount = parseFloat(removeCommas($("#settle_bal_loan_amnnt").val()));

            // Check if balance loan amount is invalid or zero
            if (isNaN(balanceLoanAmount) || balanceLoanAmount === 0) {
                $('#settle_amnnt').val(''); // Clear the settlement amount field
                alert('Please select Bal. Loan Amount First');
                return false;
            }
            let writeOffAmount = (balanceLoanAmount - settleAmount).toFixed(2);
            // Ensure settlement amount is less than balance loan amount
            if (settleAmount > balanceLoanAmount) {
                $('#settle_amnnt').val(''); // Clear the settlement amount field
                writeOffAmount = 0;
                $("#settle_wo_amnnt").val(formatIndianNumber(writeOffAmount));
                alert('Settlement amount should be less than Bal. Loan Amount');
                return false;
            }

            // Calculate the write-off amount, round to 2 decimal places, and set it in the appropriate field

            $("#settle_wo_amnnt").val(formatIndianNumber(writeOffAmount));

            const rows = document.querySelectorAll('#table-body-settle tr');

            rows.forEach((row, index) => {
                const amountInput = row.querySelector('[name="Settlement[schedule_amnt][]"]');
                if (index === 0) {
                    // Set the value in the first row as typed
                    amountInput.value = formatIndianNumber(settleAmount);
                    //amountInput.removeAttr("readonly");
                } else {
                    row.remove();
                }
            });
            //setSettlementAmount();
        }

        $("#disbursement_amnt").on('change', function() {
            var selectedValue = $(this).val();
            if (selectedValue === "percent") {
                // Make the input field editable
                $("#dis_mile").removeAttr('readonly');
            } else {
                // Make the input field read-only
                $("#dis_mile").attr('readonly', true);
            }
        });

        var baseUrl = "{{ asset('storage/') }}";
        $(document).on('click', '#assess', function() {
            var loanId = $(this).data('loan-id');
            var loanAmnt = $(this).data('loan-amnt');
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
            var lloanCreatedAt = $(this).data('loan-created-at') || '-';
            var ccreateData = lloanCreatedAt.split(' ')[0];
            $("#dis_para").html(`${lloanName} | ${lloanAmnt} | ${ccreateData}`);

            $("#idd_loan").val(loanIdd);
            $("#lloan_amount").val(lloanAmnt);

            $.ajax({
                url: '{{ route('get.loan.disbursemnt') }}',
                data: {
                    id: loanIdd
                },
                dataType: 'json',
                success: function(data) {
                    try {
                        var disbursal_amnt = data.loan_amount.disbursal_amnt;
                        console.log(disbursal_amnt);
                        $('#disbursement_amnt option').each(function() {
                            if ($(this).val() == disbursal_amnt) {
                                $(this).prop('selected', true);
                            }
                        });
                        $("#table-body-dis").html(data.disburs);
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

        $(document).on('click', '#docc', function() {
            var loanIdDoc = $(this).data('loan-id');

            $.ajax({
                url: '{{ route('get.loan.docc') }}',
                data: {
                    id: loanIdDoc
                },
                dataType: 'json',
                success: function(data) {
                    $('#documents-tbody').html(data.doc);
                    feather.replace();
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
                                        saveAs(content, 'documents.zip');
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
                                d.keyword = keyword
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
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7]
                                    }
                                },
                                {
                                    extend: 'csv',
                                    text: feather.icons['file-text'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Csv',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7]
                                    }
                                },
                                {
                                    extend: 'excel',
                                    text: feather.icons['file'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Excel',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7]
                                    }
                                },
                                {
                                    extend: 'pdf',
                                    text: feather.icons['clipboard'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Pdf',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7]
                                    }
                                },
                                {
                                    extend: 'copy',
                                    text: feather.icons['copy'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Copy',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7]
                                    }
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
            })

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


        function fetchSeriesBased(series_id, id) {
            $.ajax({
                url: getvoucherUrl + '/' + series_id,
                method: 'GET',
                success: function(response) {
                    if (response.type == "Auto") {
                        $("#" + id).attr("readonly", true);
                        $("#" + id).val(response.voucher_no);
                    } else {
                        $("#" + id).attr("readonly", false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred while fetching the data.');
                }
            });
        }

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
            const appliNoInput = document.getElementById('settle_document_no');
            const errorMessage = document.getElementById('settle_document_no_error_message');
            const appli_span = document.getElementById('settle_document_no_span')

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

        @if (session('success'))
            showToast("success", "{{ session('suuess') }}");
        @endif

        @if (session('error'))
            showToast("error", "{{ session('error') }}");
        @endif

        function getDocNumberByBookId() {
            let currentDate = new Date().toISOString().split('T')[0];
            let bookId = $('#book_id').val();
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId + "&document_date=" +
                currentDate;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#book_code_input").val(data.data.book_code);
                        if (!data.data.doc.document_number) {
                            $("#settle_document_no").val('');
                            $("#settle_document_no").val('');
                            $('#doc_number_type').val('');
                            $('#doc_reset_pattern').val('');
                            $('#doc_prefix').val('');
                            $('#doc_suffix').val('');
                            $('#doc_no').val('');
                        } else {
                            $("#settle_document_no").val(data.data.doc.document_number);
                            $('#doc_number_type').val(data.data.doc.type);
                            $('#doc_reset_pattern').val(data.data.doc.reset_pattern);
                            $('#doc_prefix').val(data.data.doc.prefix);
                            $('#doc_suffix').val(data.data.doc.suffix);
                            $('#doc_no').val(data.data.doc.doc_no);
                        }
                        if (data.data.doc.type == 'Manually') {
                            $("#settle_document_no").attr('readonly', false);
                        } else {
                            $("#settle_document_no").attr('readonly', true);
                        }

                    }
                    if (data.status == 404) {
                        $("#book_code_input").val("");
                        alert(data.message);
                    }
                });
            });
        }
        function removeCommas(input) {
    if (typeof input === 'string' && input.includes(',')) {
        return input.replace(/,/g, ''); // Replace all commas
    }
    return input; // Return the same value if no commas are present
}
    </script>

@endsection
