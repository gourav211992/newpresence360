@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Complaint Management</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('complaint.index') }}">Home</a></li>
                                        <li class="breadcrumb-item active">Complaint Management List</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                                data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                        <a class="btn btn-primary btn-sm" href="{{ route('complaint.create') }}"><i data-feather="plus-circle"></i> Add New</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="table-responsive">
                                    <table class="datatables-basic table">
                                        <thead>
                                        <tr>
                                            <th>S.NO.</th>
                                            <th>Book Name</th>
                                            <th>Document Number</th>
                                            <th>Date</th>
                                            <th>User Name</th>
                                            <th>Note</th>
                                            <th>Issue Description</th>
                                            <th>Action</th>
                                        </tr>
                                        </thead>
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
                                        <input type="text" id="fp-range" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label">Customer Name</label>
                                        <select id="filter-ledger-name" class="form-select">
                                            <option value="">Select</option>
                                            @foreach ($stakeholders as $stakeholder)
                                                <option value="{{ $stakeholder->name }}">{{ $stakeholder->name }}</option>
                                            @endforeach
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
    </div>
    <!-- END: Content-->
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            var dt_basic_table = $('.datatables-basic');

            if (dt_basic_table.length) {
                var dt_basic = dt_basic_table.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('complaint.index') }}",  // Ajax route to fetch data
                    columns: [
                        { data: 'DT_RowIndex', orderable: false, searchable: false },  // Index
                        { data: 'book_name', name: 'book_name' },  // Document Date
                        { data: 'document_number', name: 'document_number' },  // Document Date
                        { data: 'document_date', name: 'document_date' },  // Document Date
                        { data: 'user_name', name: 'user_name' },  // User Name
                        { data: 'notes', name: 'notes' },  // Notes
                        { data: 'description', name: 'description' },  // Issue Description
                        { data: 'action', orderable: false, searchable: false }  // Action buttons
                    ],
                    dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    buttons: [
                        // Export buttons (optional)
                    ],
                    drawCallback: function() {
                        feather.replace();
                    },
                    language: {
                        paginate: {
                            previous: '&nbsp;',
                            next: '&nbsp;'
                        }
                    }
                });
            }
            $(".apply-filter").on("click", function () {
                var dateRange = $("#fp-range").val();
                var customerName = $("#filter-ledger-name").val();
                dt_basic.ajax.url("{{ route('complaint.index') }}?date_range=" + dateRange + "&customer_name=" + customerName).load();
                $(".modal").modal("hide");
            });
        });
    </script>
@endsection
