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
                                <h2 class="content-header-title float-start mb-0">New Disbursal</h2>
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
                            <button class="btn btn-primary btn-sm mb-50 mb-sm-0" form="disbursement-add-update"><i
                                    data-feather="check-circle"></i> Submit</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">



                <section id="basic-datatable">
                    <form action="{{ route('loan.disbursement.add-update') }}" method="POST" enctype="multipart/form-data"
                        id="disbursement-add-update">
                        @csrf
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
                                            <div class="col-md-12">
                                                <!-- <div class="row align-items-center mb-1">
                                                                            <div class="col-md-2">
                                                                                <label class="form-label">Book Type <span class="text-danger">*</span></label>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <select class="form-select book_typeSelect" name="book_type" required onchange="fetchLoanSeries(this.value, 'disbursal_series');">
                                                                                    <option value="">Select</option>
                                                                                    @if (isset($book_type))
    @foreach ($book_type as $key => $val)
    <option value="{{ $val->id }}">{{ $val->name }}</option>
    @endforeach
    @endif
                                                                                </select>
                                                                            </div>
                                                                              
                                                                        </div>  -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <input type="hidden" name="status_val" value="submitted">
                                                    <div class="col-md-4">
                                                        <select class="form-select" name="disbursal_series"
                                                            id="disbursal_series" required>
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
                                                    <div class="col-md-2">
                                                        <label class="form-label">Disbursal No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="text" name="disbursal_no" id="disbursal_no"
                                                            value="{{ $disburs->disbursal_no ?? '' }}" class="form-control"
                                                            required>
                                                        <span id="disbursal_no_error_message" class="text-danger"></span>
                                                        <span id="disbursal_no_span"></span>
                                                        @error('disbursal_no')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Customer <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <select class="form-select select2" id="customers"
                                                            name="customer_id" required>
                                                            <option value="" selected>Select</option>
                                                            @if (isset($customers))
                                                                @foreach ($customers as $key => $val)
                                                                    <option value="{{ $val->id }}"
                                                                        {{ isset($disburs->home_loan_id) && $disburs->home_loan_id == $val->id ? 'selected' : '' }}>
                                                                        {{ $val->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="action-button mt-50">
                                                            <button data-bs-toggle="modal" type="button"
                                                                data-bs-target="#rescdule"
                                                                class="btn btn-outline-primary btn-sm"><i
                                                                    data-feather="plus-square"></i> Pending
                                                                Disbursal</button>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Application No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="text" disabled class="form-control"
                                                            value="" id="appli_no" required>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Loan Type <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="text" readonly value="" name="loan_type"
                                                            id="loan_type" class="form-control" required>
                                                    </div>

                                                </div>


                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Loan Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="text" value="" id="loan_amnt" disabled
                                                            class="form-control" required>
                                                    </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Select Disbursal Milestone <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="text" name="dis_milestone" value=""
                                                            id="milestone" readonly class="form-control" required>
                                                        @error('dis_milestone')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Disbursal Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="text" name="dis_amount" readonly value=""
                                                            id="dis_amnt" class="form-control" required>
                                                        @error('dis_amount')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Customer Contribution <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="number" name="customer_contri" id="customer_contri"
                                                            required
                                                            value="{{ old('customer_contri', $disburs->customer_contri ?? '') }}"
                                                            class="form-control">
                                                        @error('customer_contri')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Actual Disbursal Amt. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="number" required name="actual_dis" id="actual_dis"
                                                            value="{{ old('actual_dis', $disburs->actual_dis ?? '') }}"
                                                            class="form-control" readonly />
                                                        @error('actual_dis')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                </div>


                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Upload Document</label>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="file" class="form-control"
                                                            name="disbursement_docs[]" id="fileInput"
                                                            onchange="checkFileTypeandSize(event)" multiple />
                                                        <progress id="uploadProgress" value="0" max="100"
                                                            style="display:none;"></progress>
                                                        <div id="uploadStatus"></div>
                                                        <div id="fileList"></div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="disbursal_id_data" id="disbursal_id_data">

                                                <div class="row  mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Remarks</label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <textarea type="text" rows="4" class="form-control" placeholder="Enter Remarks here..."
                                                            name="dis_remarks">{{ $disburs->customer_contri ?? '' }}</textarea>
                                                    </div>

                                                </div>







                                            </div>


                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal to add new record -->
                    </form>
                </section>


            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select
                            Pending Disbursal</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Loan Type</label>
                                <select class="form-select" name="filter_loan_type" id="filter_loan_type">
                                    <option value="">Select</option>
                                    <option value="1">Home Loan</option>
                                    <option value="2">Vehicle Loan</option>
                                    <option value="3">Term Loan</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Customer Name</label>
                                <select class="form-select select2" name="filter_customer_name"
                                    id="filter_customer_name">
                                    <option value="">Select</option>
                                    @if (isset($customers))
                                        @foreach ($customers as $key => $val)
                                            <option value="{{ $val->name }}">{{ $val->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Application No.</label>
                                <select class="form-select select2" name="filter_appli_no" id="filter_appli_no">
                                    <option value="">Select</option>
                                    @if (isset($customers))
                                        @foreach ($customers as $key => $val)
                                            <option value="{{ $val->appli_no }}">{{ $val->appli_no }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>


                        <div class="col-md-3  mb-1">
                            <label class="form-label">&nbsp;</label><br />
                            <button type="button" id="search" class="btn btn-warning btn-sm"><i
                                    data-feather="search"></i> Search</button>
                        </div>

                        <div class="col-md-12">


                            <div class="table-responsive">
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Application No.</th>
                                            <th>Date</th>
                                            <th>Customer Name</th>
                                            <th>Loan Type</th>
                                            <th>Disbursal Milestone</th>
                                            <th>Disbursal Amt.</th>
                                            <th>Mobile No.</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pending_schedule"></tbody>
                                </table>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                            data-feather="x-circle"></i> Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal" id="process_disbursal"><i
                            data-feather="check-circle"></i> Process</button>
                </div>
            </div>
        </div>
    </div>

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
            $("#customer_contri").on('blur', function() {
                updateActualDis();
            });

            // $("#process_disbursal").click(function(){
            //     if ($('input[name="customColorRadio3"]:checked').length > 0) {
            //         var disbursal_id = $('input[name="customColorRadio3"]:checked').data('disbursal-id');
            //         $.ajax({
            //             url: '{{ route('loan.set_pending_status') }}',
            //             method: 'GET',
            //             data: {
            //                 disbursal_id: disbursal_id
            //             },
            //             success: function(response) {
            //                 if(response.success === 1){
            //                     alert(response.msg);
            //                     $("#process_disbursal").prop('disabled', true);
            //                 }else{
            //                     alert(response.msg);
            //                 }
            //             },
            //             error: function(xhr, status, error) {
            //                 console.error('Error:', error);
            //                 alert('An error occurred while fetching the data.');
            //             }
            //         });
            //     }else{
            //         alert('Please select at least one disbursal.');
            //     }
            // });

            $('#disbursal_series').on('change', function() {
                var book_id = $(this).val();
                var request = $('#disbursal_no');
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
            });

            $('#filter_loan_type').on('change', function() {
                //url => loan.get.disburs.customer
                let loanType = $(this).val();
                let filterData = {
                    'loanType': loanType,
                };
                disbursalPendingFilterLists(filterData)
            });

            $('#filter_customer_name').on('change', function() {
                //url => loan.get.disburs.customer
                let customerName = $(this).val();
                let filterData = {
                    'customerName': customerName,
                };
                disbursalPendingFilterLists(filterData)
            });

            function disbursalPendingFilterLists(filterData = null) {
                $.ajax({
                    url: '{{ route('loan.get.disburs.customer') }}',
                    method: 'GET',
                    data: {
                        loanType: filterData.loanType,
                        customerName: filterData.customerName,
                        //appliNo: filterData.appliNo
                    },
                    success: function(response) {
                        console.log('response', response);
                        if (response === '') {
                            let tbodyHTML =
                                '<tr><td colspan="10" class="text-center">No Records Found</td></tr>';
                            $('#pending_schedule').html(tbodyHTML);
                        } else {
                            if (filterData.customerName == null) {
                                let select = $('#filter_customer_name');
                                select.empty(); // Remove all existing options

                                // Append the default option
                                select.append('<option value="">Select</option>');

                                // Loop through the response and append new options
                                $.each(response.customers, function(index, customer) {
                                    select.append('<option value="' + customer.name + '">' +
                                        customer.name + '</option>');
                                });
                            }
                            if (filterData.customerName !== null) {
                                let select = $('#filter_appli_no');
                                select.empty(); // Remove all existing options

                                // Append the default option
                                select.append('<option value="">Select</option>');

                                // Loop through the response and append new options
                                $.each(response.customers, function(index, customer) {
                                    select.append('<option value="' + customer.appli_no + '">' +
                                        customer.appli_no + '</option>');
                                });
                            }

                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('An error occurred while fetching the data.');
                    }
                });
            };

            $('#search').click(function() {
                var loanType = $('select[name="filter_loan_type"]').val();
                var customerName = $('select[name="filter_customer_name"]').val();
                var appliNo = $('select[name="filter_appli_no"]').val();

                if (loanType || customerName || appliNo) {
                    $.ajax({
                        url: '{{ route('loan.get-pending-disbursal') }}',
                        method: 'GET',
                        data: {
                            filter_loan_type: loanType,
                            filter_customer_name: customerName,
                            filter_appli_no: appliNo
                        },
                        success: function(response) {
                            if (response === '') {
                                let tbodyHTML =
                                    '<tr><td colspan="10" class="text-center">No Records Found</td></tr>';
                                $('#pending_schedule').html(tbodyHTML);
                            } else {
                                $('#pending_schedule').html(response);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error:', error);
                            alert('An error occurred while fetching the data.');
                        }
                    });
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

            $(document).on('change', '#customers', function() {
                var customerID = $(this).val();

                $.ajax({
                    url: '{{ route('loan.get.customer') }}',
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
                        $("#appli_no").val(data.customer_record.appli_no);
                        $("#loan_type").val(loanData);
                        $("#loan_amnt").val(data.customer_record.ass_recom_amnt);
                        // $("#milestone").val(data.milestone);
                        // $("#dis_amnt").val(data.dis_amnt);

                        // Second AJAX call starts here
                        var loanType = data.customer_record.type;
                        var customerName = $('#customers option:selected').text();
                        var appliNo = $('#appli_no').val();

                        // Set the loan type
                        $('#filter_loan_type').val(loanType).trigger('change');

                        // Set the customer name
                        $('#filter_customer_name').val(customerName).trigger('change');

                        // Set the application number
                        $('#filter_appli_no').val(appliNo).trigger('change');

                        $.ajax({
                            url: '{{ route('loan.get-pending-disbursal') }}',
                            method: 'GET',
                            data: {
                                filter_loan_type: loanType,
                                filter_customer_name: customerName,
                                filter_appli_no: appliNo
                            },
                            success: function(response) {
                                if (response === '') {
                                    let tbodyHTML =
                                        '<tr><td colspan="10" class="text-center">No Records Found</td></tr>';
                                    $('#pending_schedule').html(tbodyHTML);
                                } else {
                                    $('#pending_schedule').html(response);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error:', error);
                                alert('An error occurred while fetching the data.');
                            }
                        });
                        // Second AJAX call ends here
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX Error:', status, error);
                    }
                });
            });

            $(document).on('input', '.principal-amnt', function() {
                var principalAmount = parseFloat($(this).val());
                var interestRate = parseFloat($('#recovery_interest').val());
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

                let dis_mile = parseFloat($currentRow.find('#dis_mile').val()) || 0;
                let dis_amnt = parseFloat($currentRow.find('#dis_amnt').val()) || 0;
                let dis_date = parseFloat($currentRow.find('#dis_date').val()) || 0;

                if (dis_amnt == 0 || dis_date == '') {
                    alert('Please Filled mandatory fields.');
                    return;
                } else {
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
                    if ($rowNumber.length && index > 0) {
                        alert('data');
                    }
                });
            });

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

        $("#process_disbursal").click(function() {
            if ($('input[name="customColorRadio3"]:checked').length > 0) {
                var disbursal_id = $('input[name="customColorRadio3"]:checked').data('disbursal-id');
                var disburseValue = $('input[name="customColorRadio3"]:checked').data('disburse');
                var milestoneValue = $('input[name="customColorRadio3"]:checked').data('milestone');

                $('#dis_amnt').val(disburseValue);
                $('#milestone').val(milestoneValue);
                $("#disbursal_id_data").val(disbursal_id);
                let filterData = {
                    home_loan_id: $("#home_loan_val").text(),
                    appli_no: $("#appli_no_val").text(),
                    dis_date: $("#dis_date_val").text(),
                    customer_name: $("#customer_name_val").text(),
                    type: $("#type_val").text(),
                    milestone_val: $("#milestone_val").text(),
                    disburse_val: $("#disburse_val").text(),
                };
                updateActualDis(filterData);
            }
        });
        // $(document).on('change', 'input[name="customColorRadio3"]', function() {
        //         if ($(this).is(':checked')) {
        //             var disburseValue = $(this).data('disburse');
        //             var milestoneValue = $(this).data('milestone');

        //             $('#dis_amnt').val(disburseValue);
        //             $('#milestone').val(milestoneValue);
        //         }
        //     });

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
                    alert('An error occurred while fetching the data.');
                }
            });
        }


        document.addEventListener('DOMContentLoaded', function() {
            const appliNoInput = document.getElementById('disbursal_no');
            const errorMessage = document.getElementById('disbursal_no_error_message');
            const appli_span = document.getElementById('disbursal_no_span')

            function validateAppliNo() {
                const value = appliNoInput.value.trim();

                // Check if the string starts with a negative sign
                if (value.startsWith('-')) {
                    appli_span.textContent = '';
                    errorMessage.textContent = 'The Disbursal number must not start with a negative sign.';
                    return false;
                }

                // Check if the string contains only allowed characters (letters, numbers, and dashes)
                const regex = /^[a-zA-Z0-9-_]+$/;
                if (!regex.test(value)) {
                    appli_span.textContent = '';
                    errorMessage.textContent =
                        'The Disbursal number can only contain letters, numbers, dashes, and underscores.';
                    return false;
                }

                // If all checks pass, clear the error message
                errorMessage.textContent = '';
                return true;
            }

            // Validate on blur
            appliNoInput.addEventListener('blur', validateAppliNo);
        });

        function updateActualDis(filterData = null) {
            let dis_amountVal = parseFloat($("#dis_amnt").val()) || 0;
            let customer_contriVal = parseFloat($("#customer_contri").val()) || 0;
            if (dis_amountVal == 0 || isNaN(dis_amountVal)) {
                $("#actual_dis").val(dis_amountVal);
            } else if (customer_contriVal > dis_amountVal) {
                alert('Customer Contribution Should be less than Disbursal Amount');
                $("#customer_contri").val('');
                return;
            } else {
                $("#actual_dis").val(dis_amountVal - customer_contriVal);
            }

            // Value update in Disbursal form
            $('#customers').val(filterData.home_loan_id).trigger('change');
            $('#appli_no').val(filterData.appli_no);
            $('#loan_type').val(filterData.type);
            $('#loan_amnt').val(filterData.appli_no);
            $('#milestone').val(filterData.milestone_val);
            $('#dis_amnt').val(filterData.disburse_val);

        }
    </script>

@endsection
