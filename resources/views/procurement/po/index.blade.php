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
                            <h2 class="content-header-title float-start mb-0">Purchase Orders</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                    <li class="breadcrumb-item active">PO List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <a href="{{ route('purchaseOrder.import.index') }}" class="btn btn-primary btn-sm " ><i data-feather='plus'></i> Import</a>
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                        @if(count($servicesBooks['services']))
                            <a class="btn btn-info btn-sm mb-50 mb-sm-0" href="{{ url(request()->type) }}/bulk-create"><i data-feather="plus-circle"></i> Create Bulk PO</a>
                                <a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{ url(request()->type) }}/create"><i data-feather="plus-circle"></i> Create PO</a>
                        @endif
                        <a class="btn btn-dark btn-sm mb-50 mb-sm-0" href="{{ route('transactions.report', ['serviceAlias' => 'po']) }}"><i data-feather="bar-chart-2"></i>Report</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="table-responsive">
                                    <table class="datatables-basic table myrequesttablecbox tableistlastcolumnfixed">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Date</th>
                                                <th>Series</th>
                                                <th>Doc No.</th>
                                                <th>Location</th>
                                                <th>Vendor</th>
                                                <th>Items</th>
                                                <th>Curr</th>
                                                <th>Item Value</th>
                                                <th>Discount</th>
                                                <th>Tax</th>
                                                <th>Expenses</th>
                                                <th>Total Amt</th>
                                                <th>SO No.</th>
                                                <th>Status</th>
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
     {{-- filter start------}}
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
                    <input type="text" id="fp-range" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Series</label>
                        <select class="form-select select2" id="filter-book" name="book_id[]" multiple>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}">{{ $book->book_code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                    <label class="form-label">Location</label>
                    <select class="form-select select2" id="filter-location" name="location_id[]" multiple>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->store_name }}</option>
                            @endforeach
                    </select>
                    </div>
                    <div class="mb-1">
                    <label class="form-label">Vendor</label>
                    <select class="form-select select2" id="filter-vendor" name="vendor_id[]" multiple>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->company_name }}-({{ $vendor->vendor_code }})</option>
                            @endforeach
                    </select>
                    </div>
                    <div class="mb-1">
                    <label class="form-label">Organization</label>
                    <select class="form-select select2" id="filter-organization" name="organization_id[]" multiple>
                            @foreach($applicableOrganizations as $applicableOrganization)
                                <option value="{{ $applicableOrganization->id }}">{{ $applicableOrganization->name }}</option>
                            @endforeach
                    </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button type="button" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary reset-filter" data-bs-dismiss="modal">Clear</button>
                </div>
            </form>
        </div>
    </div>
    {{-- filter end --}}

@endsection
@section('scripts')
<script type="text/javascript" src="{{asset('assets/js/modules/common-datatable.js')}}"></script>
<script>
$(document).ready(function() {
   function renderData(data) {
        return data ? data : '';
    }
    var columns = [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'document_date', name: 'document_date', render: renderData},
        { data: 'book_name', name: 'book_name', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'document_number', name: 'document_number', render: renderData,createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'store_location', name: 'store_location', render: renderData,createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'vendor_name', name: 'vendor_name', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'components', name: 'components', render: renderData },
        { data: 'curr_name', name: 'curr_name', render: renderData ,createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'total_item_value', name: 'total_item_value', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('text-end');
            }
         },
        { data: 'total_discount_value', name: 'total_discount_value', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('text-end');
            }
         },
        { data: 'total_tax_value', name: 'total_tax_value', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('text-end');
            }
         },
        { data: 'total_expense_value', name: 'total_expense_value', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('text-end');
            }
         },
         { data: 'grand_total_amount', name: 'grand_total_amount', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('text-end');
            }
         },
         { data: 'sales_order', name: 'sales_order', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
         { data: 'document_status', name: 'document_status', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
    ];
    var filters = {
        book_id: '#filter-book',
        vendor_id: '#filter-vendor',
        location_id: '#filter-location',
        date_range: '#fp-range',
        organization_id: '#filter-organization',
    };
    let title = 'Purchase Order';
    var exportColumns = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14];
    var table = initializeDataTable('.datatables-basic',
        '{{url(request()->route('type'))}}',
        columns,
        filters,
        title,
        exportColumns,
        // [[1, "desc"]] // default order

    );
    $(".data-submit").on("click", function () {
        table.ajax.reload();
        $("#filter").modal('hide');
    });

    $(".reset-filter").on("click", function () {
        $("#filter-book").val(null).trigger("change");
        $("#filter-vendor").val(null).trigger("change");
        $("#filter-location").val(null).trigger("change");
        $("#filter-organization").val(null).trigger("change");
        $("#fp-range").val("");
        table.ajax.reload();
    });
});
</script>
@endsection
