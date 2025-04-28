x   @extends('layouts.app')

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
                            <h2 class="content-header-title float-start mb-0">Interest Rate</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript::void(0);">Home</a></li>
                                    <li class="breadcrumb-item active">Interest List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
                    <div class="form-group breadcrumb-right">
						<a class="btn btn-primary btn-sm" href="{{route('loan.interest-add')}}"><i data-feather="plus-circle"></i> Add New</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
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
                                                  <th>#</th>
                                                    <th>Valid from</th>
                                                    <th>Valid upto</th>
                                                    <th>Base Rate</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($interest_rate as $index => $rate)
                                                <tr>
                                                  <td class="fw-bolder text-dark">{{ $index + 1 }}</td>
                                                    <td class="fw-bolder text-dark">{{ $rate->effective_from }}</td>
                                                    <td>{{ !empty($rate->effective_to) ? $rate->effective_to : '-' }}</td>
                                                    <td>{{ $rate->base_rate }}</td>
                                                    <td class="tableactionnew">
														<div class="dropdown">
															<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																<i data-feather="more-vertical"></i>
															</button>
															<div class="dropdown-menu dropdown-menu-end">
																<a class="dropdown-item" href="interest-edit/{{$rate->id}}">
																	<i data-feather="edit-3" class="me-50"></i>
																	<span>Edit</span>
																</a>
                                                                @if(empty($rate->effective_to))
																<a class="dropdown-item"
                                                                href="interest-delete/{{$rate->id}}">
                                                                <i data-feather="trash-2" class="me-50"></i>
                                                                <span>Delete</span>
                                                                @endif
                                                            </a>
															</div>
														</div>
													</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
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


            </div>
        </div>
    </div>
    <!-- END: Content-->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    $(window).on("load", function () {
        if (feather) {
            feather.replace({
                width: 14,
                height: 14,
            });
        }
    });
    $(function () {
        var dt_basic_table = $(".datatables-basic"),
            dt_date_table = $(".dt-date"),
            dt_complex_header_table = $(".dt-complex-header"),
            dt_row_grouping_table = $(".dt-row-grouping"),
            dt_multilingual_table = $(".dt-multilingual"),
            assetPath = "/app-assets/";

        if ($("body").attr("data-framework") === "laravel") {
            assetPath = $("body").attr("data-asset-path");
        }

        // DataTable with buttons
        // --------------------------------------------------------------------

        if (dt_basic_table.length) {
            var dt_basic = dt_basic_table.DataTable({
                order: [[0, "asc"]],
                dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                displayLength: 7,
                lengthMenu: [7, 10, 25, 50, 75, 100],
                buttons: [
                    {
                        extend: "collection",
                        className: "btn btn-outline-secondary dropdown-toggle",
                        text:
                            feather.icons["share"].toSvg({
                                class: "font-small-4 mr-50",
                            }) + "Export",
                        buttons: [
                            {
                                extend: "print",
                                text:
                                    feather.icons["printer"].toSvg({
                                        class: "font-small-4 mr-50",
                                    }) + "Print",
                                className: "dropdown-item",
                                exportOptions: { columns: [3, 4, 5, 6, 7] },
                            },
                            {
                                extend: "csv",
                                text:
                                    feather.icons["file-text"].toSvg({
                                        class: "font-small-4 mr-50",
                                    }) + "Csv",
                                className: "dropdown-item",
                                exportOptions: { columns: [3, 4, 5, 6, 7] },
                            },
                            {
                                extend: "excel",
                                text:
                                    feather.icons["file"].toSvg({
                                        class: "font-small-4 mr-50",
                                    }) + "Excel",
                                className: "dropdown-item",
                                exportOptions: { columns: [3, 4, 5, 6, 7] },
                            },
                            {
                                extend: "pdf",
                                text:
                                    feather.icons["clipboard"].toSvg({
                                        class: "font-small-4 mr-50",
                                    }) + "Pdf",
                                className: "dropdown-item",
                                exportOptions: { columns: [3, 4, 5, 6, 7] },
                            },
                            {
                                extend: "copy",
                                text:
                                    feather.icons["copy"].toSvg({
                                        class: "font-small-4 mr-50",
                                    }) + "Copy",
                                className: "dropdown-item",
                                exportOptions: { columns: [3, 4, 5, 6, 7] },
                            },
                        ],
                        init: function (api, node, config) {
                            $(node).removeClass("btn-secondary");
                            $(node).parent().removeClass("btn-group");
                            setTimeout(function () {
                                $(node)
                                    .closest(".dt-buttons")
                                    .removeClass("btn-group")
                                    .addClass("d-inline-flex");
                            }, 50);
                        },
                    },
                ],

                language: {
                    paginate: {
                        // remove previous & next text from pagination
                        previous: "&nbsp;",
                        next: "&nbsp;",
                    },
                },
            });
            $("div.head-label").html('<h6 class="mb-0">Event List</h6>');
        }

        // Flat Date picker
        if (dt_date_table.length) {
            dt_date_table.flatpickr({
                monthSelectorType: "static",
                dateFormat: "m/d/Y",
            });
        }

        // Add New record
        // ? Remove/Update this code as per your requirements ?
        var count = 101;

        $(".apply-filter").on("click", function () {
            console.log("clicked to data submit");

            // Capture filter values
            var dateRange = $("#fp-range").val(),
                bookType = $("#filter-book-type").val(),
                bookName = $("#filter-book-name").val(),
                status = $("#filter-status").val();

            // Split date range into start and end dates
            var dates = dateRange.split(" to "),
                startDate = dates[0] ? dates[0] : '',
                endDate = dates[1] ? dates[1] : '';

            // Clear any existing filters
            dt_basic.search('').columns().search('');

            // Apply filters
            dt_basic.column(1).search(bookType ? bookType : '', true, false); // Adjust index if needed
            dt_basic.column(3).search(bookName ? bookName : '', true, false); // Adjust index if needed
            dt_basic.column(6).search(status ? status : '', true, false); // Adjust index if needed

            // Custom date range filter
            // $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            //     var createdAt = data[7]; // Assuming the `created_at` date is in the 8th column
            //     if (startDate && endDate) {
            //         if (createdAt >= startDate && createdAt <= endDate) {
            //             return true;
            //         }
            //         return false;
            //     }
            //     return true;
            // });

            // Redraw the table
            dt_basic.draw();

            // Remove the custom filter function to avoid stacking filters
            $.fn.dataTable.ext.search.pop();

            // Hide the modal
            $(".modal").modal("hide");
        })
        // Delete Record
        $(".datatables-basic tbody").on("click", ".delete-record", function () {
            dt_basic.row($(this).parents("tr")).remove().draw();
        });
    });
</script>
    @endsection
