@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Expense</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="{{ url('/') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">Expense List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal">
                            <i data-feather="filter"></i> Filter
                        </button>
                        <a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{ route('expense.create') }}">
                            <i data-feather="plus-circle"></i> Create</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="table-responsive">
                                    <table class="datatables-basic table myrequesttablecbox">
                                        <thead>
                                            <tr>
                                                <th>S No.</th>
                                                <th>Series</th>
                                                <th>Expense No.</th>
                                                <th>Expense Date</th>
                                                <th>Rev. No.</th>
                                                <th>Vendor</th>
                                                <th>Item</th>
                                                <th>Item Value</th>
                                                <th>Discount</th>
                                                <th>Taxable</th>
                                                <th>Tax</th>
                                                <th>Expenses</th>
                                                <th>Total Amt</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    {{-- END: Content --}}
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                    <label class="form-label" for="fp-range">Select Date</label>
                    {{-- <input type="text" id="fp-default" class="form-control flatpickr-basic" placeholder="YYYY-MM-DD" /> --}}
                    <input type="text" id="fp-range" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                    </div>
                    <div class="mb-1">
                    <label class="form-label">Item Code</label>
                    <select class="form-select">
                        <option>Select</option>
                    </select>
                    </div>
                    <div class="mb-1">
                    <label class="form-label">Item Name</label>
                    <select class="form-select select2">
                        <option>Select</option>
                    </select>
                    </div>
                    <div class="mb-1">
                    <label class="form-label">Category</label>
                    <select class="form-select select2">
                        <option>Select</option>
                    </select>
                    </div>
                    <div class="mb-1">
                    <label class="form-label">Sub-Category</label>
                    <select class="form-select select2">
                        <option>Select</option>
                    </select>
                    </div>
                    <div class="mb-1">
                    <label class="form-label">Status</label>
                    <select class="form-select">
                        <option>Select</option>
                        <option>Active</option>
                        <option>Inactive</option>
                    </select>
                    </div>
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
    <script type="text/javascript" src="{{asset('assets/js/modules/common-datatable.js')}}"></script>
    <script>
        $(window).on("load", function () {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14,
                });
            }
        });
        $(document).ready(function() {
            function renderData(data) {
                return data ? data : 'N/A'; 
            }
            var columns = [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'book_name', name: 'book_name', render: renderData },
                { data: 'document_number', name: 'document_number', render: renderData },
                { data: 'document_date', name: 'document_date', render: renderData },
                { data: 'revision_number', name: 'revision_number', render: renderData },
                { data: 'vendor_name', name: 'vendor_name', render: renderData },
                { data: 'total_items', name: 'total_items', render: renderData },
                { data: 'total_item_amount', name: 'total_item_amount', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
                    $(td).addClass('text-end');
                    }
                },
                { data: 'total_discount', name: 'total_discount', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
                    $(td).addClass('text-end');
                    }
                },
                { data: 'taxable_amount', name: 'taxable_amount', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
                    $(td).addClass('text-end');
                    } 
                },
                { data: 'total_taxes', name: 'total_taxes', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
                    $(td).addClass('text-end');
                    }
                },
                { data: 'expense_amount', name: 'expense_amount', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
                    $(td).addClass('text-end');
                    }
                },
                { data: 'total_amount', name: 'total_amount', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
                    $(td).addClass('text-end');
                    } 
                },
                { data: 'document_status', name: 'document_status', render: renderData },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ];
            // Define your dynamic filters
            var filters = {
                status: '#filter-status',         // Status filter (dropdown)
                category: '#filter-category',     // Category filter (dropdown)
                item_code: '#filter-item-code'    // Item code filter (input text field)
            };
            var exportColumns = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]; // Columns to export
            initializeDataTable('.datatables-basic', 
                "{{ route('expense.index') }}", 
                columns,
                filters,  // Apply filters
                'Expense Advise',  // Export title
                exportColumns  // Export columns
            );
            // Apply filter on button click
            // applyFilter('.apply-filter');
        });
    </script>
@endsection
