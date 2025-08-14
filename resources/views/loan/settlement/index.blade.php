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
                        <h2 class="content-header-title float-start mb-0">Settlement Entry</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                
                                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                                <li class="breadcrumb-item active">Settlement List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
                <div class="form-group breadcrumb-right">
                    <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                    <!-- <button class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-target="#reject" data-bs-toggle="modal" onclick="fetchSTcApproveRecord(2);"><i data-feather="x-circle"></i> Reject</button>  -->
                    <!-- <button class="btn btn-success btn-sm mb-50 mb-sm-0" data-bs-target="#approved" data-bs-toggle="modal" onclick="fetchSTcApproveRecord(1);"><i data-feather="check-circle" ></i> Approve</button>   -->
                    <a class="btn btn-dark btn-sm mb-50 mb-sm-0" href="{{route('loan.settlement.add')}}"><i data-feather="file-text"></i> Add Settlement</a>
                </div>
            </div>
        </div>

        <div class="content-body">
            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

        @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif



            <section id="basic-datatable">
                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="table-responsive">
                                <table class="datatables-basic table myrequesttablecbox loanapplicationlist">
                                    <thead>
                                        <tr>
                                            <th class="pe-0"># </th>
                                            <th>Application No.</th>
                                            <th>Document No.</th>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Mobile No.</th>
                                            <th>Loan Type</th>
                                            <th>Loan Amt.</th>
                                            <th>Total Recovery.</th>
                                            <th>Bal. Amt.</th>
                                            <th>Settled Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal to add new record -->
                <div class="modal modal-slide-in fade" id="modals-slide-in">
                    <div class="modal-dialog sidebar-sm">
                        <form class="add-new-record modal-content pt-0">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                            <div class="modal-header mb-1">
                                <h5 class="modal-title" id="exampleModalLabel">New Record</h5>
                            </div>
                            <div class="modal-body flex-grow-1">
                                <div class="mb-1">
                                    <label class="form-label" for="basic-icon-default-fullname">Full Name</label>
                                    <input type="text" class="form-control dt-full-name" id="basic-icon-default-fullname" placeholder="John Doe" aria-label="John Doe" />
                                </div>
                                <div class="mb-1">
                                    <label class="form-label" for="basic-icon-default-post">Post</label>
                                    <input type="text" id="basic-icon-default-post" class="form-control dt-post" placeholder="Web Developer" aria-label="Web Developer" />
                                </div>
                                <div class="mb-1">
                                    <label class="form-label" for="basic-icon-default-email">Email</label>
                                    <input type="text" id="basic-icon-default-email" class="form-control dt-email" placeholder="john.doe@example.com" aria-label="john.doe@example.com" />
                                    <small class="form-text"> You can use letters, numbers & periods </small>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label" for="basic-icon-default-date">Joining Date</label>
                                    <input type="text" class="form-control dt-date" id="basic-icon-default-date" placeholder="MM/DD/YYYY" aria-label="MM/DD/YYYY" />
                                </div>
                                <div class="mb-4">
                                    <label class="form-label" for="basic-icon-default-salary">Salary</label>
                                    <input type="text" id="basic-icon-default-salary" class="form-control dt-salary" placeholder="$12000" aria-label="$12000" />
                                </div>
                                <button type="button" class="btn btn-primary data-submit me-1">Submit</button>
                                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
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
                                <input type="text" id="fp-range" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
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
                                    @if(isset($customer_names))
                                    @foreach($customer_names as $val)
                                    <option value="{{ $val->name }}">{{ $val->name }}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="mb-1">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="filter-status">
                                    <option value="">Select</option>
                                    <option value="0">Submitted</option>
                                    <option value="1">Approved</option>
                                    <option value="2">Rejected</option>
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

        </div>
    </div>
</div>
<!-- END: Content-->

<div class="modal fade" id="approved" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Approve Home Loan Application</h4>
                    <p class="mb-0 fw-bold voucehrinvocetxt mt-0" id="rc_detail"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('loan.settlement.appr_rej') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="st_appr_status" value="1">
                <input type="hidden" id="checkedData" name="checkedData">
                <div class="modal-body pb-2">

                    <div class="row mt-1">

                        <div class="col-md-12">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label">Loan Amount <span class="text-danger">*</span></label>
                                        <input type="number" disabled value="" id="getLoanAmountDATA" class="form-control" />
                                    </div>
                                </div>

                            </div>

                            <div class="mb-1">
                                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="st_appr_remark" id="st_appr_remark" required></textarea>
                            </div>

                            <div class="mb-1">
                                <label class="form-label">Upload Document</label>
                                <input type="file" class="form-control" name="st_appr_doc[]" id="fileInput" multiple onchange="handleFileInput(event)" />
                                <progress id="uploadProgress" value="0" max="100" style="display:none;"></progress>
                                <div id="uploadStatus"></div>
                                <span id="fileList" style="margin-top: 13px;display: flex;gap: 5px;"></span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="reject" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Reject Home Loan Application</h4>
                    <p class="mb-0 fw-bold voucehrinvocetxt mt-0" id="rc_detail_re"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('loan.settlement.appr_rej') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="st_appr_status" value="2">
                <input type="hidden" id="checkedDataVAL" name="checkedData">
                <div class="modal-body pb-2">

                    <div class="row mt-2">

                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label class="form-label">Loan Amount <span class="text-danger">*</span></label>
                                        <input type="number" disabled value="" id="getLoanAmountDATARE" class="form-control" />
                                    </div>
                                </div>



                            </div>

                            <div class="mb-1">
                                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="st_appr_remark" id="ste_appr_remark" required></textarea>
                            </div>

                            <div class="mb-1">
                                <label class="form-label">Upload Document</label>
                                <input type="file" class="form-control" name="st_appr_doc[]" id="fileInput" multiple onchange="handleFileInputRej(event)" />
                                <progress id="uploadProgress" value="0" max="100" style="display:none;"></progress>
                                <div id="uploadStatus"></div>
                                <span id="fileListRE" style="margin-top: 13px;display: flex;gap: 5px;"></span>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal modal-slide-in fade filterpopuplabel" id="filter">
    <div class="modal-dialog sidebar-sm">
        <form class="add-new-record modal-content pt-0">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="mb-1">
                    <label class="form-label" for="fp-range">Select Date Range</label>
                    <input type="text" id="fp-range" class="form-control flatpickr-range" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                </div>

                <div class="mb-1">
                    <label class="form-label">Loan Type</label>
                    <select class="form-select">
                        <option>Select</option>
                        <option>Home Loan</option>
                        <option>Vehicle Loan</option>
                        <option>Term Loan</option>
                    </select>
                </div>

                <div class="mb-1">
                    <label class="form-label">Customer Name</label>
                    <select class="form-select">
                        <option>Select</option>
                    </select>
                </div>

                <div class="mb-1">
                    <label class="form-label">Status</label>
                    <select class="form-select">
                        <option>Select</option>
                        <option>In Progress</option>
                        <option>Pending</option>
                        <option>Completed</option>
                    </select>
                </div>

            </div>
            <div class="modal-footer justify-content-start">
                <button type="button" class="btn btn-primary data-submit mr-1">Apply</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{asset('assets/js/loan.js')}}"></script>
<script>
    var getset = "{{ url('/loan/fetch-settle-approve') }}";
</script>
<script>
    $(document).ready(function() {
        function updateCheckedData() {
            var checkedValues = [];
            $('.row-checkbox:checked').each(function() {
                checkedValues.push($(this).closest('tr').find('#getID').val());
            });
            $('#checkedData').val(checkedValues.join(','));
            $('#checkedDataVAL').val(checkedValues.join(','));
        }

        function getAllCheckboxValues() {
            var allCheckboxValues = [];
            $('.row-checkbox').each(function() {
                $(this).prop('checked', $('#inlineCheckbox1').is(':checked'));
            });
            updateCheckedData();
        }

        $('#inlineCheckbox1').on('change', function() {
            getAllCheckboxValues();
        });

        $('#openModalBtn').on('click', function(e) {
            var isChecked = $('.row-checkbox:checked').length > 0;
            if (!isChecked) {
                e.preventDefault(); // Prevents opening the modal
                alert('Please select at least one checkbox.');
                return false; // Stop further propagation
            }
            // If checkboxes are selected, allow opening the modal
        });

        // Modal show event to ensure no other triggers are causing it
        $('#approved').on('show.bs.modal', function(e) {
            var isChecked = $('.row-checkbox:checked').length > 0;
            if (!isChecked) {
                $(this).modal('hide'); // Ensure the modal is hidden
                alert('Please select at least one checkbox.');
                e.preventDefault(); // Prevents the modal from being shown
            }
        });

        $('#reject').on('show.bs.modal', function(e) {
            var isChecked = $('.row-checkbox:checked').length > 0;
            if (!isChecked) {
                $(this).modal('hide'); // Ensure the modal is hidden
                alert('Please select at least one checkbox.');
                e.preventDefault(); // Prevents the modal from being shown
            }
        });

        // Optional: Initialize hidden field if needed
        updateCheckedData();
    });
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
                    url: "{{ route('loan.settlement') }}",
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
                            orderable: false,
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                    {
                        data: 'appli_no',
                        name: 'appli_no',
                        className:'fw-bolder text-dark'
                    },
                    {
                        data: 'settle_document_no',
                        name: 'settle_document_no',
                        className:'fw-bolder text-dark'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'name',
                        name: 'name'
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
                        data: 'recovery_amnt',
                        name: 'recovery_amnt'
                    },
                    {
                        data: 'bal_amnt',
                        name: 'bal_amnt'
                    },

                    {
                        data: 'settle_amnnt',
                        name: 'settle_amnnt'
                    },
                    {
                        data: 'status',
                        name: 'status'
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
                                title: 'Settlement Report Print',
                                exportOptions: {
                                    columns: ':not(:first-child):not(:last-child)'
                                },
                            },
                            {
                                extend: 'csv',
                                text: feather.icons['file-text'].toSvg({
                                    class: 'font-small-4 mr-50'
                                }) + 'Csv',
                                className: 'dropdown-item',
                                filename: 'Settlement_Report_CSV',
                                exportOptions: {
                                    columns: ':not(:first-child):not(:last-child)'
                                },
                                title: 'Settlement Report CSV'
                            },
                            {
                                extend: 'excel',
                                text: feather.icons['file'].toSvg({
                                    class: 'font-small-4 mr-50'
                                }) + 'Excel',
                                className: 'dropdown-item',
                                filename: 'Settlement_Report_Excel',
                                exportOptions: {
                                    columns: ':not(:first-child):not(:last-child)'
                                },
                                title: 'Settlement Report Excel'
                            },
                            {
                                extend: 'pdf',
                                text: feather.icons['clipboard'].toSvg({
                                    class: 'font-small-4 mr-50'
                                }) + 'Pdf',
                                className: 'dropdown-item',
                                filename: 'Settlement_Report_PDF',
                                exportOptions: {
                                    columns: ':not(:first-child):not(:last-child)'
                                },
                                customize: function(doc) {
                                    var colCount = doc.content[1].table.body[0].length;
                                    doc.content[1].table.widths = Array(colCount).fill('*');
                                },
                                orientation: 'landscape',
                                pageSize: 'A4',
                                title: 'Settlement Report PDF'
                            },
                            {
                                extend: 'copy',
                                text: feather.icons['copy'].toSvg({
                                    class: 'font-small-4 mr-50'
                                }) + 'Copy',
                                className: 'dropdown-item',
                                title: 'Settlement_Report_COPY'
                            }
                        ],
                        init: function(api, node, config) {
                            $(node).removeClass('btn-secondary');
                            $(node).parent().removeClass('btn-group');
                            setTimeout(function() {
                                $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
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

    function updateCheckedData() {
        var checkedValues = [];
        $('.row-checkbox:checked').each(function() {
            var id = $(this).closest('tr').find('#getID').val();
            checkedValues.push(id);
        });
        $('#checkedData').val(checkedValues.join(','));
        $("#checkedDataVAL").val(checkedValues.join(','));
    }

    $(document).on('change', '.row-checkbox', function() {
        var getLoanAmountDATA = $(this).closest('tr').find('#getLoanAmount').val();
        var getNameRecordField = $(this).closest('tr').find('#getNameRecord').val();
        var getDateRecordField = $(this).closest('tr').find('#getDateRecord').val();
        $("#rc_detail").html(`${getNameRecordField} | ${getLoanAmountDATA} | ${getDateRecordField}`)
        $("#getLoanAmountDATA").val(getLoanAmountDATA);

        var getLoanAmountDATARE = $(this).closest('tr').find('#getLoanAmount').val();
        var getNameRecordFieldRE = $(this).closest('tr').find('#getNameRecord').val();
        var getDateRecordFieldRE = $(this).closest('tr').find('#getDateRecord').val();
        $("#rc_detail_re").html(`${getNameRecordFieldRE} | ${getLoanAmountDATARE} | ${getDateRecordFieldRE}`)
        $("#getLoanAmountDATARE").val(getLoanAmountDATARE);
        ensureSingleCheckbox();
        updateCheckedData();
    });

    function ensureSingleCheckbox() {
        if ($('.row-checkbox:checked').length > 1) {
            alert('You can select only one record at a time.');
            // Uncheck the newly checked checkbox
            $('.row-checkbox:checked').last().prop('checked', false);
            updateCheckedData();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const textInputs = document.querySelectorAll('input[type="text"]');

        textInputs.forEach(function(input) {
            input.addEventListener('input', function() {
                if (this.value.length > 250) {
                    alert('You have exceeded the 250 character limit. Extra characters will be removed.');
                    this.value = this.value.substring(0, 250);
                }
            });

            input.addEventListener('blur', function() {
                if (this.value.length > 250) {
                    alert('You have exceeded the 250 character limit. Extra characters will be removed.');
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
                    alert('You have exceeded the 11 character limit. Extra characters will be removed.');
                    this.value = this.value.substring(0, 11);
                }
            });

            input.addEventListener('blur', function() {
                if (this.value.length > 11) {
                    alert('You have exceeded the 11 character limit. Extra characters will be removed.');
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
                    alert('You have exceeded the 500 character limit. Extra characters will be removed.');
                    this.value = this.value.substring(0, 500);
                }
            }

            textarea.addEventListener('input', enforceCharacterLimit);
            textarea.addEventListener('blur', enforceCharacterLimit);
        });
    });

    function handleFileInput(event) {
        checkFileTypeandSize(event);
        uploadMultipleDoc(event.target.files, 'fileList');
    }

    function handleFileInputRej(event) {
        checkFileTypeandSize(event);
        uploadMultipleDoc(event.target.files, 'fileListRE');
    }
</script>
@endsection
