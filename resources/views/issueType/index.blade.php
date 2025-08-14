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
                        <h2 class="content-header-title float-start mb-0">Legal Issue Type Master</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Legal Issue Type List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
                <div class="form-group breadcrumb-right">
                    <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                        data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                    <a class="btn btn-primary btn-sm" href="{{ route('issueType.create') }}"><i data-feather="plus-circle"></i> Add
                        New</a>
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
                                <table class="datatables-basic table myrequesttablecbox">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Issue Type</th>
                                            <th>Status</th>
                                            <th class="d-none">Created At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($issueTypes as $index => $issueType)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="fw-bolder text-dark">{{ $issueType->name }}</td>
                                                <td>
                                                    <span
                                                        class="badge rounded-pill badge-light-{{ $issueType->status === 'Active' ? 'success' : 'danger' }} badgeborder-radius">
                                                        {{ $issueType->status }}
                                                    </span>
                                                </td>
                                                <td class="d-none">{{ $issueType->created_at }}</td>
                                                <td class="tableactionnew">
                                                    <div class="dropdown">
                                                        <button type="button"
                                                            class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                            data-bs-toggle="dropdown">
                                                            <i data-feather="more-vertical"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <a class="dropdown-item"
                                                                href="{{ route('issueTypeEdit',$issueType->id)}}">
                                                                <i data-feather="edit-3" class="me-50"></i>
                                                                <span>Edit</span>
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

                <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
                    <div class="modal-dialog sidebar-sm">
                        <form class="add-new-record modal-content pt-0">
                            <div class="modal-header mb-1">
                                <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                    ×
                                </button>
                            </div>
                            <div class="modal-body flex-grow-1">
                                <div class="mb-1">
                                    <label class="form-label" for="fp-range">Select Date</label>
                                    <!--                        <input type="text" id="fp-default" class="form-control flatpickr-basic" placeholder="YYYY-MM-DD" />-->
                                    <input type="text" id="fp-range" class="form-control flatpickr-range bg-white"
                                        placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Issue Type</label>
                                    <select id="filter-book-type" class="form-select">
                                        <option value="">Select</option>
                                        @foreach($issueTypes as $issueType)
                                            <option value="{{ $issueType->id }}">{{ $issueType->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" id="filter-status">
                                        <option>Select</option>
                                        <option>Active</option>
                                        <option>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-start">
                                <button type="button" class="btn btn-primary apply-filter mr-1">
                                    Apply
                                </button>
                                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    Cancel
                                </button>
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
        $(".apply-filter").on("click", function () {

            // Capture filter values
            var dateRange = $("#fp-range").val(),
                bookType = $("#filter-book-type").val(),
                status = $("#filter-status").val();

            // Split date range into start and end dates
            var dates = dateRange.split(" to "),
                startDate = dates[0] ? dates[0] : '',
                endDate = dates[1] ? dates[1] : '';

            // Clear any existing filters
            dt_basic.search('').columns().search('');

            // Apply filters
            dt_basic.column(1).search(bookType ? bookType : '', true, false);
            dt_basic.column(3).search(status ? status : '', true, false);

            // Custom date range filter
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                var createdAt = data[4];
                if (startDate && endDate) {
                    if (createdAt >= startDate && createdAt <= endDate) {
                        return true;
                    }
                    return false;
                }
                return true;
            });

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