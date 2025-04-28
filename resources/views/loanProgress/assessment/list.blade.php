@extends('layouts.app')



@section('styles')
    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/vendors/css/calendars/fullcalendar.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('app-assets/css/pages/app-calendar.css') }}">
    <!-- END: Page CSS-->
@endsection



@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-6 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Application for Assessment</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">All Request
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                    </div>
                </div>
            </div>
            <div class="content-body dasboardnewbody">

                <!-- ChartJS section start -->
                <section id="chartjs-chart">
                    <div class="row">



                        <div class="col-md-12 col-12">
                            <div class="card  new-cardbox">
                                <div class="table-responsive">
                                    <table class="datatables-assessment table myrequesttablecbox loanapplicationlist">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Application No.</th>
                                                <th>Reference No.</th>
                                                <th>Date</th>
                                                <th>Name</th>
                                                <th>Email-ID</th>
                                                <th>Mobile No.</th>
                                                <th>Loan Type</th>
                                                <th>Loan Amt.</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td class="fw-bolder text-dark">HL/2024/001</td>
                                                <td class="fw-bolder text-dark">APP001</td>
                                                <td>20-07-2024</td>
                                                <td>Nishu Garg</td>
                                                <td>nishu@gmail.com</td>
                                                <td>9876787656</td>
                                                <td>Home </td>
                                                <td>20 Lkh</td>
                                                <td>
                                                    <a href="view-loan-ass.html">
                                                        <i data-feather="eye" class="me-50"></i>
                                                    </a>
                                                </td>
                                            </tr>
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

    <div class="modal fade" id="viewassesgive" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Assessment by
                            Field Officer</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | {{date('d-m-Y')}}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">

                    <div class="row mt-1">

                        <div class="col-md-12">

                            <div class="row">


                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label class="form-label">Upload Document <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control" />
                                    </div>
                                </div>
                            </div>

                            <div class="mb-1">
                                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                <textarea class="form-control"></textarea>
                            </div>



                        </div>

                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Submit</button>
                </div>
            </div>
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
                    <p class="text-center text-dark fw-bold">Nishu Garg | 20 Lkh | {{date('d-m-Y')}}</p>

                    <div class="row mt-2">

                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table
                                    class="mt-1 table myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Document Name</th>
                                            <th>Download</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Aadhar Card</td>
                                            <td><a href="#"><i data-feather='download'></i></a></td>
                                        </tr>

                                        <tr>
                                            <td>2</td>
                                            <td>PAN/GIR No.</td>
                                            <td><a href="#"><i data-feather='download'></i></a></td>
                                        </tr>

                                        <tr>
                                            <td>3</td>
                                            <td>Plot Document</td>
                                            <td><a href="#"><i data-feather='download'></i></a> <a href="#"><i
                                                        data-feather='download'></i></a></td>
                                        </tr>

                                        <tr>
                                            <td>4</td>
                                            <td>Land Document</td>
                                            <td><a href="#"><i data-feather='download'></i></a></td>
                                        </tr>

                                        <tr>
                                            <td>5</td>
                                            <td>Scan form Application</td>
                                            <td><a href="#"><i data-feather='download'></i></a></td>
                                        </tr>





                                    </tbody>


                                </table>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Download All</button>
                </div>
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
                        <input type="text" id="fp-range" class="form-control flatpickr-range"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" />
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
                        <select id="filter-ledger-name" class="form-select">
                            <option value="">Select</option>
                            @foreach ($loans as $loan)
                                <option value="{{ $loan->name }}">{{ $loan->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- <div class="mb-1">
                        <label class="form-label">Status</label>
                        <select class="form-select">
                            <option>Select</option>
                            <option>Save as Draft</option>
                            <option>Send Back</option>
                        </select>
                    </div> --}}

                </div>
                <div class="modal-footer justify-content-start">
                    <button type="button" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection



@section('scripts')
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

            var dt_basic_table = $('.datatables-assessment'),
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

            if (dt_basic_table.length) {
                var dt_basic = dt_basic_table.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('loanAssessment.index') }}",
                        data: function(d) {
                            d.date = $("#fp-range").val(),
                                d.ledger = $("#filter-ledger-name").val(),
                                d.status = $("#filter-status").val(),
                                d.type = $("#filter-ledger-type").val(),
                                d.keyword = $('#DataTables_Table_0_filter input').val()
                        }
                    },
                    columns: [{
                            data: 'sr_no',
                            name: 'sr_no',
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
                                    title: 'Loan Assessment Report'
                                },
                                {
                                    extend: 'csv',
                                    text: feather.icons['file-text'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Csv',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [1, 3, 4, 5, 6, 7, 8]
                                    },
                                    filename: 'Loan Assessment Report',
                                    title: 'Loan Assessment Report'
                                },
                                {
                                    extend: 'excel',
                                    text: feather.icons['file'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Excel',
                                    className: 'dropdown-item',
                                    filename: 'Loan Assessment Report',
                                    exportOptions: {
                                        columns: [1, 3, 4, 5, 6, 7, 8]
                                    },
                                    title: 'Loan Assessment Report'
                                },
                                {
                                    extend: 'pdf',
                                    text: feather.icons['clipboard'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Pdf',
                                    className: 'dropdown-item',
                                    filename: 'Loan Assessment Report',
                                    exportOptions: {
                                        columns: [1, 3, 4, 5, 6, 7, 8]
                                    },
                                    title: 'Loan Assessment Report',
                                    customize: function(doc) {
                                        // Center the entire content
                                        doc.pageMargins = [40, 60, 40, 60];

                                        // Center align all table cells
                                        doc.content[1].table.body.forEach(function(row) {
                                            row.forEach(function(cell) {
                                                cell.alignment = 'center';
                                            });
                                        });

                                        // Center the table itself
                                        doc.content[1].alignment = 'center';

                                        // Ensure table is centered on page
                                        doc.content[1].table.widths = Array(doc.content[1].table
                                            .body[0].length).fill('*');

                                    }
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
            $(".data-submit").on("click", function() {
                // Redraw the table
                dt_basic.draw();

                // Remove the custom filter function to avoid stacking filters
                // $.fn.dataTable.ext.search.pop();

                // Hide the modal
                $(".modal").modal("hide");
            });

            // Delete Record
            $('.datatables-basic tbody').on('click', '.delete-record', function() {
                dt_basic.row($(this).parents('tr')).remove().draw();
            });



        });

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'dayGridWeek,listWeek'
                },
                initialView: 'dayGridWeek',
                editable: true,
                dayMaxEvents: true, // allow "more" link when too many events
                eventClick: function(event, jsEvent, view) {
                    alert();
                },
                //dateClick: function(info) {
                //alert();
                //},
                eventContent: function(info) {
                    return {
                        html: info.event.title
                    };
                },
                events: [{
                        title: '<div class="team-leavecalen-week"><span class="badge badge-light-secondary">Sakshi Maan<br/>(SL)</span><span class="badge badge-light-primary">Ashish Kumar<br/>(AL)</span><span class="badge badge-light-success">Kundan Tiwari<br/>(OL)</span></div>',
                        start: '2023-01-10'
                    },
                    {
                        title: '<div class="team-leavecalen-week"><span class="badge badge-light-primary">Pankaj Tripathi<br />(AL)</span><span class="badge badge-light-secondary">Deepak Singh<br/>(SL)</span><span class="badge badge-light-warning">Ashish Kumar<br/>(EL)</span><span class="badge badge-light-info">Nishu Garg<br />(CL)</span><span class="badge badge-light-success">Rahul Upadhyay<br />(OL)</span></div> ',
                        start: '2023-01-11'
                    },

                ]
            });

            calendar.render();
        });


        $(function() {
            $("input[name='loanassesment']").click(function() {
                if ($("#Disbursement1").is(":checked")) {
                    $(".selectdisbusement").show();
                    $(".cibil-score").hide();
                } else {
                    $(".selectdisbusement").hide();
                    $(".cibil-score").show();
                }
            });
        });

        $(function() {
            $("input[name='LoanSettlement']").click(function() {
                if ($("#Dispute1").is(":checked")) {
                    $("#dispute-settle").show();
                    $("#normal-settle").hide();
                } else {
                    $("#dispute-settle").hide();
                    $("#normal-settle").show();
                }
            });
        });
    </script>
@endsection
