@extends('layouts.app')
@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Job Order</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                    <li class="breadcrumb-item active">JO List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                        @if(count($servicesBooks['services']))
                            <a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{ route('jo.create') }}"><i data-feather="plus-circle"></i> Create JO</a>
                            <a class="btn btn-dark btn-sm mb-50 mb-sm-0" href="{{ route('transactions.report', ['serviceAlias' => 'job-order']) }}"><i data-feather="bar-chart-2"></i>Report</a>
                        @endif
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
                                                <th>Type</th>
                                                <th>Location</th>
                                                <th>Vendor</th>
                                                <th>Items</th>
                                                <th>Curr</th>
                                                <th>Item Value</th>
                                                <th>Discount</th>
                                                <th>Tax</th>
                                                <th>Expenses</th>
                                                <th>Total Amt</th>
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
        { data: 'job_order_type', name: 'job_order_type', render: renderData,createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'store_location', name: 'store_location', render: renderData,createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'vendor_name', name: 'vendor_name', render: renderData},
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
         { data: 'document_status', name: 'document_status', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
    ];
    var filters = {
        status: '#filter-status',
        category: '#filter-category',
        item_code: '#filter-item-code'
    };

    let title = '';
    title = 'Job Order';
    var exportColumns = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14];
    initializeDataTable('.datatables-basic', 
        '{{route("jo.index")}}',
        columns,
        filters,
        title,
        exportColumns,
        // [[1, "desc"]] // default order

    );
    // Apply filter on button click
    // applyFilter('.apply-filter');
});
</script>
@endsection
