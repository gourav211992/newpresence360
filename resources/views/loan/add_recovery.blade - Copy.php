@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">New Recovery</h2>
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
                            <button class="btn btn-success btn-sm mb-50 mb-sm-0" form="recovery-add-update"><i
                                    data-feather="check-circle"></i> Submit</button>

                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                <form action="{{ route('loan.recovery.add-update') }}" method="POST" enctype="multipart/form-data"
                    id="recovery-add-update">
                    @csrf
                    <input type ="hidden" name="book_code" id ="book_code_input">
                    <input type="hidden" name="doc_number_type" id="doc_number_type">
                    <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
                    <input type="hidden" name="doc_prefix" id="doc_prefix">
                    <input type="hidden" name="doc_suffix" id="doc_suffix">
                    <input type="hidden" name="doc_no" id="doc_no">

                    <input type="hidden" name="recovery_remain" id="recovery_remain" value="">
                    <input type="hidden" name="current_settled" id="current_settled" value="">

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
                                                        <select class="form-select" name="book_id" id="book_id"
                                                            onchange="getDocNumberByBookId()" required>
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
                                                        <input type="text" readonly class="form-control" id="document_no"
                                                            name="document_no" required>
                                                        <span id="document_no_error_message" class="text-danger"></span>
                                                        <span id="document_no_span"></span>
                                                        @error('document_no')
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
                                                        <select class="form-select select2" id="applicants"
                                                            name="application_no" required>
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
                                                        <input type="text" id="cus_tomer" name="cus_tomer" readonly
                                                            class="form-control" value="" required>
                                                        @error('cus_tomer')
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
                                                        <input type="text" id="loan_type" name="loan_type" readonly
                                                            value="" class="form-control" required>
                                                        @error('loan_type')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>

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
                                                        <input type="text" id="loan_amount" name="loan_amount"
                                                            readonly class="form-control" required>
                                                        @error('loan_amount')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
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
                                                        <input type="text" value="" id="dis_amount"
                                                            name="dis_amount" readonly class="form-control">
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
                                                        <input type="text" value="" id="rec_amnt" readonly
                                                            class="form-control">
                                                        <input name="rec_amnt" type="hidden" id="rec_amnt_in">
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
                                                        <input type="hidden" id="rec_intrst_in" name="rec_intrst">
                                                        <input type="text" value="" id="rec_intrst" readonly
                                                            class="form-control">
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
                                                        <input type="hidden" id="balance_amount_in"
                                                            name="balance_amount" value="">
                                                        <input type="text" id="balance_amount" value="" readonly
                                                            class="form-control" name="blnc_amnt" required>
                                                        @error('balance_amount')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
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
                                                        <input type="text" value="" id="bal_intrst_amnt"
                                                            readonly class="form-control">
                                                        <input type="hidden" name="bal_intrst_amnt"
                                                            id="bal_intrst_amnt_in">
                                                    </div>

                                                </div>

                                            </div>


                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Recovery Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" class="form-control"
                                                            oninput="adjustAmounts()" name="recovery_amnnt"
                                                            id="recovery_amnnt" required>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Date of Payment <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="date" class="form-control"
                                                            value="{{ date('Y-m-d') }}" name="payment_date"
                                                            onchange="calculate_interest()" id="payment_date" required>
                                                    </div>

                                                </div>

                                            </div>


                                        </div>
                                        <div class="row my-2 settlement_detail">

                                            <div class="col-md-12 revisedvalue mt-2">
                                                <div
                                                    class="newheader d-flex justify-content-between align-items-end mb-1 border-bottom pb-25">
                                                    <div class="header-left">
                                                        <h4 class="card-title text-theme">Settlement Detail</h4>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-12">
                                                                <label class="form-label">Settled Amount <span
                                                                        class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <input type="text" value="" name="settled_amnt"
                                                                    id="settled_amnt" readonly class="form-control">

                                                            </div>

                                                        </div>

                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-12">
                                                                <label class="form-label">Settled Recovery Amount <span
                                                                        class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <input type="text" value=""
                                                                    id="settled_rec_amnt" readonly class="form-control">

                                                            </div>

                                                        </div>

                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-12">
                                                                <label class="form-label">Settled Balance Amount <span
                                                                        class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <input type="text" value=""
                                                                    id="settled_blnc_amnt" readonly class="form-control">

                                                            </div>

                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                        </div>


                                        <div class="row my-2 settlement">


                                            <div class="col-md-12 revisedvalue">
                                                <div
                                                    class="newheader d-flex justify-content-between align-items-end mb-1 border-bottom pb-25">
                                                    <div class="header-left">
                                                        <h4 class="card-title text-theme">Settlement Info</h4>
                                                    </div>
                                                </div>

                                                <div class="table-responsive-md mb-1">



                                                    <table hidden
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Date</th>
                                                                <th class="text-end">Disbursed</th>
                                                                <th class="text-end">Recovered</th>
                                                                <th class="text-end">Balance</th>
                                                                <th class="text-end">Interest</th>
                                                                <th class="text-end text-warning">Settled Int.</th>
                                                                <th class="text-end text-success">Settled Principal</th>
                                                                <th class="text-end">Remaining</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="recovery_history">


                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="bg-light-success">
                                                                <td></td>
                                                                <td id="rec_date" class="fw-bolder text-dark text-end">
                                                                    Total</td>
                                                                <td id="rec_dis_amnt"
                                                                    class="fw-bolder text-dark text-end"></td>
                                                                <td id="rec_rec_amnt"
                                                                    class="fw-bolder text-dark text-end"></td>
                                                                <td id="rec_blnc_amnt"
                                                                    class="fw-bolder text-dark text-end"></td>
                                                                <td id="rec_intrest_amnt"
                                                                    class="fw-bolder text-dark text-end"></td>
                                                                <td id="rec_set_intrst"
                                                                    class="fw-bolder text-dark text-end"></td>
                                                                <td id="rec_set_princ"
                                                                    class="fw-bolder text-dark text-end"></td>
                                                                <td id="rec_remaining"
                                                                    class="fw-bolder text-dark text-end"></td>
                                                            </tr>

                                                        </tfoot>

                                                    </table>
                                                    <br>
                                                    <br>
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Date</th>
                                                                <th class="text-end">Disbursed</th>
                                                                <th class="text-end">Recovered</th>
                                                                <th class="text-end">Balance</th>
                                                                <th class="text-end">Interest</th>
                                                                <th class="text-end text-warning">Settled Int.</th>
                                                                <th class="text-end text-success">Settled Principal</th>
                                                                <th class="text-end">Remaining</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="recovery_history_2">


                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="bg-light-success">
                                                                <td></td>
                                                                <td id="rec_date" class="fw-bolder text-dark text-end">
                                                                    Total</td>
                                                                <td id="rec_dis_amnt2"
                                                                    class="fw-bolder text-dark text-end"></td>
                                                                <td id="rec_rec_amnt2"
                                                                    class="fw-bolder text-dark text-end"></td>
                                                                <td id="rec_blnc_amnt2"
                                                                    class="fw-bolder text-dark text-end"></td>
                                                                <td id="rec_intrest_amnt2"
                                                                    class="fw-bolder text-dark text-end"></td>
                                                                <td id="rec_set_intrst2"
                                                                    class="fw-bolder text-dark text-end"></td>
                                                                <td id="rec_set_princ2"
                                                                    class="fw-bolder text-dark text-end"></td>
                                                                <td id="rec_remaining2"
                                                                    class="fw-bolder text-dark text-end"></td>
                                                            </tr>

                                                        </tfoot>

                                                    </table>

                                                </div>
                                            </div>

                                        </div>

                                        <div class="row">


                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Payment Mode <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <select class="form-select" name="payment_mode" required>
                                                            <option value="">Select</option>
                                                            <option value="by_cheque">By Cheque</option>
                                                            <option selected value="neft">NEFT/IMPS/RTGS</option>
                                                            <option value="other">Other</option>
                                                        </select>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1 bankdetail">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Reference No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="number" class="form-control" name="ref_no"
                                                            required>
                                                    </div>

                                                </div>

                                            </div>


                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1 bankdetail">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Bank Name</label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <select class="form-control select2" name="bank_name"
                                                            id="bank" required onchange="get_account()">
                                                            <option value="">Select</option>
                                                            @foreach ($banks as $bank)
                                                                <option value="{{ $bank->id }}">
                                                                    {{ $bank->bank_name }}({{ $bank->bank_code }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1 bankdetail">
                                                    <div class="col-md-12">
                                                        <label class="form-label">A/c No.</label>
                                                    </div>

                                                    <select class="form-control select2" name="account_number" required
                                                        id="account">
                                                        <option value="">Select Account
                                                            Number</option>

                                                    </select>
                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Upload Document</label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="file" class="form-control" id="recovery_doc"
                                                            name="recovery">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-6">
                                                <div class="row  mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Remarks</label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" class="form-control" name="remarks" />
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
    </div>
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
        $(document).ready(function() {
            let recevoryDate = null;

            $('.settlement').hide();
            $('.settlement_detail').hide();

            /*
                        $('#book_id').on('change', function() {
                            var book_id = $(this).val();
                            var request = $('#document_no');
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
                            }
                        });*/
            $(document).on('blur', '#recovery_amnnt', function() {
                if (!applicants) {
                    $(this).val('');
                    alert('Select Application No First.');
                    return;
                }
            });

            $('#fileInput').on('change', function() {
                var files = this.files;
                var $fileList = $('#fileList');
                $fileList.empty();

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

            $(document).on('change', '#applicants', function() {
                $('.settlement').hide();
                $('.settlement_detail').hide();
                document.querySelectorAll('input[type="text"]').forEach(input => input.value = '');



                var customerID = $(this).val();
                //console.log(customerID);
                if (customerID != "") {
                    applicants = parseFloat($("#applicants").val()) || 0;



                    $.ajax({
                        url: '{{ route('loan.get.recovery.customer') }}',
                        data: {
                            id: customerID
                        },
                        dataType: 'json',
                        success: function(data) {
                            $('.settlement').show();
                            $('.settlement_detail').hide();

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
                                                return sum + parseFloat(disbursement
                                                    .actual_dis);
                                            else
                                                return sum + parseFloat(disbursement
                                                    .dis_amount);
                                        }
                                        return sum;
                                    },
                                    0
                                );

                            var totalInterest = data.customer_record.loan_appraisal.recovery
                                .reduce(
                                    function(sum, recover) {
                                        return sum + parseFloat(recover
                                            .interest_amount
                                        ); // Convert to float and accumulate
                                    }, 0);
                            if (data.customer_record.bal_intrst_amnt != 0) {
                                $("#bal_intrst_amnt").val(data.customer_record.bal_intrst_amnt);
                                $("#bal_intrst_amnt_in").val(data.customer_record
                                    .bal_intrst_amnt);
                            } else {

                                $("#bal_intrst_amnt").val(totalInterest);
                                $("#bal_intrst_amnt_in").val(totalInterest);

                            }
                            var totalRecovery = 0;
                            var totalIntrestReceived = 0;
                            let intrest = parseFloat($('#bal_intrst_amnt').val()) || 0;
                            totalRecovery = parseFloat(data.customer_record.recovery_pa) || 0;
                            totalIntrestReceived = parseFloat(data.customer_record
                                .recovery_ia) || 0;
                            totalInterest = totalInterest - totalIntrestReceived;
                            let repayment_dur = data.customer_record.loan_appraisal
                                .repayment_start_period;
                            let repayment_type = data.customer_record.loan_appraisal
                                .repayment_type;
                            let rep_month = repayment_dur;
                            var totalSettled = 0;

                            console.log(data.customer_record.loan_settlement.length)

                            if (data.customer_record.loan_settlement.length > 0) {
                                $('.settlement').hide();
                                $('.settlement_detail').show();

                                totalSettled = data.customer_record.loan_settlement
                                    .reduce(
                                        function(sum, settle) {
                                            return sum + parseFloat(settle
                                                .settle_amnnt
                                            ); // Convert to float and accumulate
                                        }, 0);
                            }
                            $('#settled_amnt').val(formatIndianNumber(totalSettled));

                            var total = totalRecovery + totalSettled;
                            $('#settled_rec_amnt').val(formatIndianNumber(total));
                            let loan_to = parseFloat(data.customer_record.loan_appraisal
                                .term_loan).toFixed(2) || 0;
                            if (loan_to > total)
                                $('#settled_blnc_amnt').val(formatIndianNumber(loan_to -
                                    total));
                            else
                                $('#settled_blnc_amnt').val(0);

                            let balance_amount = 0;

                            if (totalDisbursement > total)
                                balance_amount = totalDisbursement - total;





                            $("#cus_tomer").val(data.customer_record.name);
                            $("#loan_type").val(loanData);
                            //console.log(data.customer_record);

                            $("#loan_amount").val(formatIndianNumber(data.customer_record
                                .loan_appraisal
                                .term_loan));
                            $("#rec_intrst").val(formatIndianNumber(totalIntrestReceived));
                            $("#rec_intrst_in").val(totalIntrestReceived);
                            $("#dis_amount").val(formatIndianNumber(totalDisbursement));
                            $("#rec_amnt").val(formatIndianNumber(
                                totalRecovery)); // Set recovery amount
                            $("#rec_amnt_in").val(totalRecovery); // Set recovery amount
                            $("#dis_amount").val(formatIndianNumber(
                                totalDisbursement)); // Set disbursement amount
                            $("#balance_amount").val(formatIndianNumber(
                                balance_amount)); // Set balance amount

                            $("#balance_amount_in").val(balance_amount); // Set balance amount

                            $("#bal_principal").val(data.customer_record.recovery_pa);
                            $("#bal_interest").val(data.customer_record.recovery_ia);
                            $("#bal_principal_hide").val(data.customer_record.recovery_pa);
                            $("#bal_interest_hide").val(data.customer_record.recovery_ia);
                            $("#due_date").val(dueDate);
                            $("#recovery_sentioned").val(recoverySentioned);



                            $('#recovery_history').empty();
                            $('#recovery_history_2').empty();

                            $.each(data.customer_record.loan_disbursements, function(key, dis) {
                                let created_date = moment(dis.created_at).format(
                                    'D-M-YYYY');

                                $('#recovery_history').append(
                                    `<tr>
                                <td>  <input type="hidden" name="dis_id[]" value="${dis.id}" id="dis_id_${key+1}">
                                    <input type="hidden" id="pri_set_${key+1}" value="${(parseFloat(dis.settled_principal) || 0).toFixed(2)}">
                                    <input type="hidden" id="int_set_${key+1}" value="${(parseFloat(dis.settled_interest) || 0).toFixed(2)}">
                                    <input type="hidden" id="recover_set_${key+1}" value="${(parseFloat(dis.recovered) || 0).toFixed(2)}">
                                    <input type="hidden" id="remain_set_${key+1}" value="${(parseFloat(dis.remainig) || 0).toFixed(2)}">
                                    <input type="hidden" id="recovery_status_${key+1}" name="recovery_status[]" value="${dis.recovery_status}">
                                     <input type="hidden" id="balance_dis_${key+1}" value="${dis.remaining !== null ? dis.remaining : (dis.actual_dis !== null ? dis.actual_dis : dis.dis_amount)}">
                                    ${key + 1}
                                </td>

                                <td id="rec_date_${key+1}">${ created_date|| ""}</td>
                                <td  id="rec_dis_amnt_${key+1}" class="fw-bolder text-dark text-end">${(parseFloat(dis.actual_dis ?? dis.dis_amount ) || 0).toFixed(2)}</td>
                                <td  id="rec_rec_amnt_${key+1}" class="text-end">${(parseFloat(dis.recovered) || 0).toFixed(2)}</td>
                                <td  id="rec_blnc_amnt_${key+1}" class="text-end">${dis.remaining !== null ? dis.remaining : (dis.actual_dis !== null ? dis.actual_dis : dis.dis_amount)}</td>
                                <td  id="rec_intrest_amnt_${key+1}" class="text-end"></td>
                                <td  id="rec_set_intrst_${key+1}" class="text-end text-warning">0.00</td>
                                <td  id="rec_set_princ_${key+1}" class="text-end text-success">0.00</td>
                                <td  id="rec_remaining_${key+1}" class="text-end"></td>
                            </tr>`
                                );

                                let value = dis.remaining ?? dis.actual_dis ?? dis
                                    .dis_amount;
                                value ? formatIndianNumber(value) : 0

                                let dis_remaining = dis.remaining ? formatIndianNumber(
                                    dis.remaining) : 0
                                $('#recovery_history_2').append(
                                    `<tr>
                                <td> ${key + 1}</td>
                                <td id="rec_date2_${key+1}">${ created_date|| ""}</td>
                                <td  id="rec_dis_amnt2_${key+1}" class="fw-bolder text-dark text-end">${(formatIndianNumber(parseFloat(dis.actual_dis ?? dis.dis_amount )) || 0)}</td>
                                <td  id="rec_rec_amnt2_${key+1}" class="text-end">${formatIndianNumber(parseFloat(dis.recovered) || 0)}</td>
                                <td  id="rec_blnc_amnt2_${key+1}" class="text-end">${formatIndianNumber(parseFloat(value))}</td>
                                <td  id="rec_intrest_amnt2_${key+1}" class="text-end"></td>
                                <td  id="rec_set_intrst2_${key+1}" class="text-end text-warning">0.00</td>
                                <td  id="rec_set_princ2_${key+1}" class="text-end text-success">0.00</td>
                                <td  id="rec_remaining2_${key+1}" class="text-end">${dis_remaining}</td>
                            </tr>`
                                );



                            });

                            calculate_interest();

                        },
                        error: function(xhr, status, error) {
                            console.log('AJAX Error:', status, error);
                        }
                    });
                }
            });




        });

        function fetchLoanSeries(book_type_id, id) {
            return $.ajax({
                url: getSeriesUrl,
                method: 'GET',
                data: {
                    book_type_id: book_type_id
                },
                success: function(response) {
                    if (response.success === 1) {
                        $("#" + id).html(response.html);
                    } else {
                        alert(response.msg);
                        $("#" + id).html(response.html);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred while fetching the data.');
                }
            });
        }

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

        document.addEventListener('DOMContentLoaded', function() {
            const numberInputs = document.querySelectorAll('input[type="number"]');

            function sanitizeInput(value) {
                let sanitized = value.replace(/-+/g, '');
                return sanitized.replace(/[^0-9]/g, '');
            }

            function updateValue(input) {
                let sanitizedValue = sanitizeInput(input.value);
                input.value = sanitizedValue;
            }

            numberInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    updateValue(this);
                });

                input.addEventListener('blur', function() {
                    updateValue(this);
                });
            });
        });


        function adjustAmounts() {
            revert_change();
            var amount = parseFloat($('#recovery_amnnt').val()) || 0;
            var pri = parseFloat($('#rec_amnt_in').val()) || 0;
            var inter = parseFloat($('#rec_intrst_in').val()) || 0;
            //  amount = amount + pri + inter;


            // Select all rows in the recovery history table

            const rows = document.querySelectorAll('#recovery_history tr');
            let recovery_remaining = amount;


            if (amount > 0) {
                rows.forEach(row => {
                    const statusCell = row.querySelector('[id^="recovery_status_"]');
                    //console.log(statusCell.value);
                    const old_set_i = parseFloat(row.querySelector('[id^="int_set_"]').value) || 0;

                    if (statusCell.value != "fully_recover") {
                        if (recovery_remaining > 0) {

                            // Access cells for interest, settled interest, principal, settled principal, and remaining
                            const settledPrincipalCell = row.querySelector('[id^="rec_set_princ_"]');
                            const remainingCell = row.querySelector('[id^="rec_remaining_"]');
                            const remainingCell2 = row.querySelector('[id^="rec_remaining2_"]');

                            const settledInterestCell = row.querySelector('[id^="rec_set_intrst_"]');
                            const principalCell = row.querySelector('[id^="rec_dis_amnt_"]');
                            const interestCell = row.querySelector('[id^="rec_intrest_amnt_"]');
                            const balanceCell = row.querySelector('[id^="rec_blnc_amnt_"]');
                            const rowIndex = row.rowIndex;

                            // Select the corresponding row in #recovery_history_2
                            const otherRow = $('#recovery_history_2 tr');


                            // Find the interest cell with the specific ID in #recovery_history_2
                            const settledPrincipalCell2 = otherRow.find(`#rec_set_princ2_${rowIndex}`);
                            const settledInterestCell2 = otherRow.find(`#rec_set_intrst2_${rowIndex}`);


                            // Parse values, ensuring each is a valid number or defaulting to 0
                            let interest = parseFloat(interestCell.textContent) || 0;
                            let settledInterest = parseFloat(settledInterestCell.textContent) || 0;
                            let principal = parseFloat(principalCell.textContent) || 0;
                            let settledPrincipal = parseFloat(settledPrincipalCell.textContent) || 0;

                            // Ensure parsed values are numbers; if NaN, set them to 0
                            if (isNaN(interest)) interest = 0;
                            if (isNaN(settledInterest)) settledInterest = 0;
                            if (isNaN(principal)) principal = 0;
                            if (isNaN(settledPrincipal)) settledPrincipal = 0;

                            var recovery_interest = "";
                            //recovery_interest = recovery_remaining+inter;

                            // 1. Adjust interest first
                            if (interest > 0) {
                                if (recovery_remaining >= interest) {
                                    recovery_remaining = recovery_remaining - interest;

                                    settledInterestCell.textContent = formatIndianNumber(interest);
                                    settledInterestCell2.text(formatIndianNumber(interest));
                                } else {
                                    settledInterestCell.textContent = formatIndianNumber(recovery_remaining);
                                    settledInterestCell2.text(formatIndianNumber(recovery_remaining));
                                    recovery_remaining = 0;
                                }

                            }
                            adjust_remaining();
                        }
                    }
                });

                rows.forEach(row => {

                    if (recovery_remaining > 0) {
                        // Access cells for interest, settled interest, principal, settled principal, and remaining
                        const settledPrincipalCell = row.querySelector('[id^="rec_set_princ_"]');
                        const remainingCell = row.querySelector('[id^="rec_remaining_"]');
                        const remainingCell2 = row.querySelector('[id^="rec_remaining2_"]');

                        const settledInterestCell = row.querySelector('[id^="rec_set_intrst_"]');
                        const principalCell = row.querySelector('[id^="balance_dis_"]');
                        const interestCell = row.querySelector('[id^="rec_intrest_amnt_"]');
                        const balanceCell = row.querySelector('[id^="rec_blnc_amnt_"]');
                        const old_set_p = parseFloat(row.querySelector('[id^="pri_set_"]').value) || 0;
                        const otherRow = $('#recovery_history_2 tr');
                        const rowIndex = row.rowIndex;

                        // Find the interest cell with the specific ID in #recovery_history_2
                        const settledPrincipalCell2 = otherRow.find(`#rec_set_princ2_${rowIndex}`);
                        const settledInterestCell2 = otherRow.find(`#rec_set_intrst2_${rowIndex}`);

                        // Parse values, ensuring each is a valid number or defaulting to 0
                        let interest = parseFloat(interestCell.textContent) || 0;
                        let settledInterest = parseFloat(settledInterestCell.textContent) || 0;
                        let principal = parseFloat(principalCell.value) || 0;
                        let settledPrincipal = parseFloat(settledPrincipalCell.textContent) || 0;

                        // Ensure parsed values are numbers; if NaN, set them to 0
                        if (isNaN(interest)) interest = 0;
                        if (isNaN(settledInterest)) settledInterest = 0;
                        if (isNaN(principal)) principal = 0;
                        if (isNaN(settledPrincipal)) settledPrincipal = 0;



                        // 1. Adjust interest first
                        console.log(principal);
                        if (principal > 0) {
                            if (recovery_remaining >= principal) {
                                recovery_remaining = recovery_remaining - principal;
                                settledPrincipalCell.textContent = formatIndianNumber(principal);
                                settledPrincipalCell2.text(formatIndianNumber(principal));


                            } else {
                                settledPrincipalCell.textContent = formatIndianNumber(recovery_remaining);
                                settledPrincipalCell2.text((recovery_remaining).toFixed(2));
                                recovery_remaining = 0;
                            }

                        }

                        balanceCell.textContent = parseFloat(principalCell.textContent).toFixed(2) - parseFloat(
                            settledPrincipalCell.textContent).toFixed(2) || 0;
                        // console.log(balance.textContent);
                        adjust_remaining();
                    }
                });
                adjust_remaining();



            } else {
                rows.forEach(row => {
                    row.querySelector('[id^="rec_set_princ_"]').textContent = 0;
                    row.querySelector('[id^="rec_set_intrst_"]').textContent = 0;



                });
                adjust_remaining();
            }
        }

        $('#recovery_amnnt').on('blur', function() {
           // Get the value of the input field
        var amount = $(this).val();

        // Format the value with commas before saving or processing it
        var formattedAmount = formatIndianNumber(removeCommas(amount));

        // Set the formatted value back into the input field
        $(this).val(formattedAmount);
        });

        function get_total2() {
            const rows = document.querySelectorAll('#recovery_history_2 tr');
            let totalDisbursed = 0;
            let totalRecovered = 0;
            let totalBalance = 0;
            let totalInterest = 0;
            let totalSettledInterest = 0;
            let totalSettledPrincipal = 0;
            let totalRemaining = 0;
            document.querySelectorAll('#recovery_history_2 tr').forEach(row => {

                totalDisbursed += parseFloat(removeCommas($('#rec_dis_amnt2_' + row.rowIndex).text()) || 0);
                totalRecovered += parseFloat(removeCommas($('#rec_rec_amnt2_' + row.rowIndex).text()) || 0);
                totalBalance += parseFloat(removeCommas($('#rec_blnc_amnt2_' + row.rowIndex).text()) || 0);
                totalInterest += parseFloat(removeCommas($('#rec_intrest_amnt2_' + row.rowIndex).text()) || 0);
                totalSettledInterest += parseFloat(removeCommas($('#rec_set_intrst2_' + row.rowIndex).text()) || 0);
                totalSettledPrincipal += parseFloat(removeCommas($('#rec_set_princ2_' + row.rowIndex).text()) || 0);
                totalRemaining += parseFloat(removeCommas($('#rec_remaining2_' + row.rowIndex).text()) || 0);

            });

            // Display totals in the footer
            document.getElementById('rec_dis_amnt2').innerText = formatIndianNumber(totalDisbursed);
            document.getElementById('rec_rec_amnt2').innerText = formatIndianNumber(totalRecovered);
            document.getElementById('rec_blnc_amnt2').innerText = formatIndianNumber(totalBalance);
            document.getElementById('rec_intrest_amnt2').innerText = formatIndianNumber(totalInterest);
            document.getElementById('rec_set_intrst2').innerText = formatIndianNumber(totalSettledInterest);
            document.getElementById('rec_set_princ2').innerText = formatIndianNumber(totalSettledPrincipal);
            document.getElementById('rec_remaining2').innerText = formatIndianNumber(totalRemaining);

        }

        function get_total() {
            get_total2();
            const rowsTable2 = document.querySelectorAll('#recovery_history_2 tr');
            const rows = document.querySelectorAll('#recovery_history tr');

            rows.forEach((row, index) => {

                const correspondingRow = rowsTable2[index];
                // Access cells for interest, settled interest, principal, settled principal, and remaining
                const settledPrincipalCell = row.querySelector('[id^="rec_set_princ_"]');
                const remainingCell = row.querySelector('[id^="rec_remaining_"]');
                const remainingCell2 = correspondingRow.querySelector('[id^="rec_remaining2_"]');



                const settledInterestCell = row.querySelector('[id^="rec_set_intrst_"]');
                const principalCell = row.querySelector('[id^="balance_dis_"]');
                const interestCell = row.querySelector('[id^="rec_intrest_amnt_"]');
                const recoverCell = row.querySelector('[id^="rec_rec_amnt_"]');
                const totalrecover = parseFloat(row.querySelector('[id^="recover_set_"]').value) || 0;

                const balanceValue = row.querySelector('[id^="balance_dis_"]');

                const balanceCell = row.querySelector('[id^="rec_blnc_amnt_"]');



                // Parse values, ensuring each is a valid number or defaulting to 0
                let interest = parseFloat(interestCell.textContent) || 0;
                let settledInterest = parseFloat(settledInterestCell.textContent) || 0;
                let principal = parseFloat(principalCell.value) || 0;
                let settledPrincipal = parseFloat(settledPrincipalCell.textContent) || 0;


                // Ensure parsed values are numbers; if NaN, set them to 0
                if (isNaN(interest)) interest = 0;
                if (isNaN(settledInterest)) settledInterest = 0;
                if (isNaN(principal)) principal = 0;
                if (isNaN(settledPrincipal)) settledPrincipal = 0;

                console.log(principal,settledPrincipal,interest,settledInterest);
                let remain = (principal - settledPrincipal) + (interest - settledInterest);
                recoverCell.textContent = parseFloat(settledPrincipal + totalrecover).toFixed(2);
                remainingCell.textContent = remain.toFixed(2);
                remainingCell2.textContent = remain.toFixed(2);

                let balance = (principal - settledPrincipal);
                balanceCell.textContent = balance.toFixed(2);


            });
            let totalDisbursed = 0;
            let totalRecovered = 0;
            let totalBalance = 0;
            let totalInterest = 0;
            let totalSettledInterest = 0;
            let totalSettledPrincipal = 0;
            let totalRemaining = 0;
            document.querySelectorAll('#recovery_history tr').forEach(row => {
                //console.log(row.rowIndex);
                totalDisbursed += parseFloat(row.querySelector('#rec_dis_amnt_' + row.rowIndex)
                    ?.innerText || 0);
                totalRecovered += parseFloat(row.querySelector('#rec_rec_amnt_' + row.rowIndex)
                    ?.innerText || 0);
                totalBalance += parseFloat(row.querySelector('#rec_blnc_amnt_' + row.rowIndex)?.innerText ||
                    0);
                totalInterest += parseFloat(row.querySelector('#rec_intrest_amnt_' + row.rowIndex)
                    ?.innerText || 0);
                totalSettledInterest += parseFloat(row.querySelector('#rec_set_intrst_' + row.rowIndex)
                    ?.innerText ||
                    0);
                totalSettledPrincipal += parseFloat(row.querySelector('#rec_set_princ_' + row.rowIndex)
                    ?.innerText ||
                    0);
                totalRemaining += parseFloat(row.querySelector('#rec_remaining_' + row.rowIndex)
                    ?.innerText || 0);
            });

            // Display totals in the footer
            document.getElementById('rec_dis_amnt').innerText = totalDisbursed.toFixed(2);
            document.getElementById('rec_rec_amnt').innerText = totalRecovered.toFixed(2);
            document.getElementById('rec_blnc_amnt').innerText = totalBalance.toFixed(2);
            document.getElementById('rec_intrest_amnt').innerText = totalInterest.toFixed(2);
            document.getElementById('rec_set_intrst').innerText = totalSettledInterest.toFixed(2);
            document.getElementById('rec_set_princ').innerText = totalSettledPrincipal.toFixed(2);
            document.getElementById('rec_remaining').innerText = totalRemaining.toFixed(2);

        }

        function adjust_remaining() {
            get_total();
            let totalSettledInterest = parseFloat(document.getElementById('rec_set_intrst').textContent) || 0;
            let totalSettledPrincipal = parseFloat(document.getElementById('rec_set_princ').textContent) || 0;
            let bl = parseFloat(document.getElementById('balance_amount').value) || 0;
            let int = parseFloat(document.getElementById('bal_intrst_amnt').value) || 0;
            let rec = parseFloat(document.getElementById('rec_amnt').value) || 0;
            let rec_int = parseFloat(document.getElementById('rec_intrst').value) || 0;
            //console.log(bl-totalSettledPrincipal);
            if (bl > totalSettledPrincipal)
                document.getElementById('balance_amount_in').value = bl - totalSettledPrincipal;
            else
                document.getElementById('balance_amount_in').value = 0;
            if (int > totalSettledInterest)
                document.getElementById('bal_intrst_amnt_in').value = int - totalSettledInterest;
            else
                document.getElementById('bal_intrst_amnt_in').value = 0;

            document.getElementById('rec_amnt_in').value = rec + totalSettledPrincipal;
            document.getElementById('rec_intrst_in').value = rec_int + totalSettledInterest;
            update_table_inputs();


        }


        function revert_change() {
            const rows = document.querySelectorAll('#recovery_history tr');
            rows.forEach(row => {
                const otherRow = $('#recovery_history_2 tr');

                const rowIndex = row.rowIndex;
                // Find the interest cell with the specific ID in #recovery_history_2
                const settledPrincipalCell2 = otherRow.find(`#rec_set_princ2_${rowIndex}`);
                const settledInterestCell2 = otherRow.find(`#rec_set_intrst2_${rowIndex}`);

                const settledPrincipalCell = row.querySelector('[id^="rec_set_princ_"]');
                const settledInterestCell = row.querySelector('[id^="rec_set_intrst_"]');

                settledPrincipalCell.textContent = '0.00';
                settledInterestCell.textContent = '0.00';
                settledInterestCell2.text('0.00');
                settledPrincipalCell2.text('0.00');


            });
            adjust_remaining();
        }



        function get_account() {
            let bankId = $('#bank').val();
            if (bankId != "") {
                $('#account').empty().append('<option value="">Select Account Number</option>');

                if (bankId) {
                    $.ajax({
                        url: "{{ route('get.bank.details') }}", // Your route to get bank details
                        type: 'GET',
                        data: {
                            bank_id: bankId
                        },
                        success: function(response) {
                            if (response) {
                                // Loop through the account numbers and add them to the dropdown
                                $.each(response, function(index, account) {
                                    $('#account').append('<option value="' + account.id + '">' +
                                        account
                                        .account_number + '</option>');
                                });
                            } else {
                                $('#account').append('<option value="">No accounts available</option>');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error fetching account numbers:', xhr);
                        }
                    });
                }

            }

        }

        function calculate_interest() {
            let payment_date = moment($('#payment_date').val()).format('D-M-YYYY');
            let applicants = parseFloat($("#applicants").val()) || 0;
            if (!applicants) {
                alert('Select Application No First.');
                return;
            }
            payment_date = moment(payment_date, 'D-M-YYYY');

            const rows = document.querySelectorAll('#recovery_history tr');
            rows.forEach(row => {

                let created_date = row.querySelector('[id^="rec_date_"]').textContent;
                created_date = moment(created_date, 'D-M-YYYY');

                const rowIndex = row.rowIndex;

                // Select the corresponding row in #recovery_history_2
                const otherRow = $('#recovery_history_2 tr');


                // Find the interest cell with the specific ID in #recovery_history_2
                const interestCell2 = otherRow.find(`#rec_intrest_amnt2_${rowIndex}`);
                const remainingCell = row.querySelector('[id^="rec_remaining_"]');
                const remainingCell2 = row.querySelector('[id^="rec_remaining2_"]');

                const settledInterestCell = row.querySelector('[id^="rec_set_intrst_"]');
                const principalCell = row.querySelector('[id^="rec_dis_amnt_"]');
                const interestCell = row.querySelector('[id^="rec_intrest_amnt_"]');
                const balanceCell = row.querySelector('[id^="rec_blnc_amnt_"]');
                const statusCell = row.querySelector('[id^="recovery_status_"]');
                let interestRates = "";
                let applicants = parseFloat($("#applicants").val()) || 0;
                let exceed_days = payment_date.diff(created_date, "days");



                let tot_amn = "";
                //console.log(payment_date, created_date);
                if (statusCell.value == "partial_recover") {
                    tot_amn = parseFloat(balanceCell.textContent) || 0;
                    $.ajax({
                        url: '{{ route('loan.get.RecoveryInterest') }}',
                        data: {
                            applicants: applicants,
                            exceed_days: exceed_days,
                            dis_amount: tot_amn
                        },
                        dataType: 'json',
                        success: function(response) {
                            let interestRates = response.amount;
                            interestCell.textContent = response.amount;
                            interestCell2.text(formatIndianNumber(response.amount));

                            get_total();

                        },
                        error: function(xhr, status, error) {
                            console.log('AJAX Error:', status,
                                error);
                        }
                    });
                } else if (statusCell.value == "null") {
                    tot_amn = parseFloat(principalCell.textContent) || 0;
                    $.ajax({
                        url: '{{ route('loan.get.RecoveryInterest') }}',
                        data: {
                            applicants: applicants,
                            exceed_days: exceed_days,
                            dis_amount: tot_amn
                        },
                        dataType: 'json',
                        success: function(response) {
                            let interestRates = response.amount;
                            interestCell.textContent = response.amount;
                            interestCell2.text(formatIndianNumber(response.amount));
                            get_total();


                        },
                        error: function(xhr, status, error) {
                            console.log('AJAX Error:', status,
                                error);
                        }
                    });

                } else {
                    get_total();
                }

            });
        }


        function update_table_inputs() {

            // Clear previous inputs
            $('#recovery-add-update').find('input[name="disbursementData[]"]').remove();

            let disbursementData = [];

            $('#recovery_history tr').each(function(index) {
                let dis_id = $(`#dis_id_${index+1}`).val();
                let balance_amount = $(`#rec_blnc_amnt_${index+1}`).text();
                let recovered_amount = $(`#rec_rec_amnt_${index+1}`).text();
                let interest_amount = $(`#rec_intrest_amnt_${index+1}`).text();
                let settled_interest = $(`#rec_set_intrst_${index+1}`).text();
                let settled_principal = $(`#rec_set_princ_${index+1}`).text();
                let remaining = $(`#rec_remaining_${index+1}`).text();
                let disbursed = $(`#rec_dis_amnt_${index+1}`).text();

                disbursementData.push({
                    dis_id,
                    disbursed,
                    balance_amount,
                    recovered_amount,
                    interest_amount,
                    settled_interest,
                    settled_principal,
                    remaining
                });

            });

            // Append disbursement data as hidden inputs
            disbursementData.forEach((data, index) => {
                Object.keys(data).forEach(key => {
                    $('#recovery-add-update').append(
                        `<input type="hidden" name="disbursementData[${index}][${key}]" value="${data[key]}">`
                    );
                });
            });
            var recover_remain = $('#rec_remaining').text();
            var set_princ = $('#rec_set_princ2').text();

            $('#recovery_remain').val(recover_remain);
            $('#current_settled').val(set_princ);

        }

        @if (session('success'))
            showToast("error", "{{ session('suuess') }}");
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
                            $("#document_no").val('');
                            $("#document_no").val('');
                            $('#doc_number_type').val('');
                            $('#doc_reset_pattern').val('');
                            $('#doc_prefix').val('');
                            $('#doc_suffix').val('');
                            $('#doc_no').val('');
                        } else {
                            $("#document_no").val(data.data.doc.document_number);
                            $('#doc_number_type').val(data.data.doc.type);
                            $('#doc_reset_pattern').val(data.data.doc.reset_pattern);
                            $('#doc_prefix').val(data.data.doc.prefix);
                            $('#doc_suffix').val(data.data.doc.suffix);
                            $('#doc_no').val(data.data.doc.doc_no);
                        }
                        if (data.data.doc.type == 'Manually') {
                            $("#document_no").attr('readonly', false);
                        } else {
                            $("#document_no").attr('readonly', true);
                        }

                    }
                    if (data.status == 404) {
                        $("#book_code_input").val("");
                        alert(data.message);
                    }
                });
            });
        }
    </script>
@endsection
