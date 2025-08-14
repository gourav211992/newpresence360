@extends('layouts.app')

@section('content')

<div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">
                                {{$typeName}}
                            </h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>  
                                    <li class="breadcrumb-item active">List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" onclick ='openFiltersModal();' ><i data-feather="filter"></i> Filter</button> 
                        @if ($create_button)
                        <a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{$create_route}}"><i data-feather="plus-circle"></i>
                            {{'Create ' . $typeName}}
                        </a> 
                        @endif
                        <a class="btn btn-dark btn-sm mb-50 mb-sm-0" href="{{ route('transactions.report', ['serviceAlias' => 'si']) }}"><i data-feather="bar-chart-2"></i>Report</a>

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
                                                <th>Department</th>
                                                <th>Rev No</th>
                                                <th>Ref No</th>
                                                <th>Customer</th>
                                                <th>Items</th>
                                                <th>Curr</th>
                                                <th class = "numeric-alignment">Item Value</th>
                                                <th class = "numeric-alignment">Discount</th>
                                                <th class = "numeric-alignment">Tax</th>
                                                <th class = "numeric-alignment">Expenses</th>
                                                <th class = "numeric-alignment">Total Amt</th>
                                                <th>Invoice Type</th>
                                                <th>E Invoice</th>
                                                <th>E-Way Bill</th>
                                                <th>POD</th>
                                                <th style = 'text-align:center'>Status</th>
											  </tr>
											</thead>
											<tbody>
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

@endsection
@section('scripts')
<script type="text/javascript" src="{{asset('assets/js/modules/common-datatable.js')}}"></script>
<script>
    let reportDataTableInstance = null;
    $(document).ready(function() {
    function renderData(data) {
        return data ? data : 'N/A'; 
    }
    var columns = [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'document_date', name: 'document_date', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'book_name', name: 'book_name', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'document_number', name: 'document_number', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'store_code', name: 'store_code', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'department_code', name: 'department_code', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'revision_number', name: 'revision_number', render: renderData, orderable: true, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'reference_number', name: 'reference_number', render: renderData},
        { data: 'customer_name', name: 'customer_name', render: renderData },
        { data: 'items_count', name: 'items_count', render: renderData },
        { data: 'curr_name', name: 'curr_name', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
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
         { data: 'total_amount', name: 'total_amount', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('text-end');
            } 
         },
        { data: 'gst_invoice_type', name: 'gst_invoice_type', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'e_invoice_status', name: 'e_invoice_status', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'is_ewb_generated', name: 'is_ewb_generated', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'delivery_status', name: 'delivery_status', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
        { data: 'document_status', name: 'document_status', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
    ];
    // Define your dynamic filters
    let filtersComponents = @json($filterArray);
    // Define your dynamic filters
    let filters = {
        'date_range' : '#document_date_filter'
    };
    filtersComponents.forEach(filter => {
        filters[filter.requestName] = "#" + (filter.id + "_input");
    });
    var exportColumns = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]; // Columns to export
    reportDataTableInstance = initializeDataTable('.datatables-basic', 
        "{{ $redirect_url }}", 
        columns,
        filters,  // Apply filters
        "{{$typeName}}",  // Export title
        exportColumns,  // Export columns
        [],// default order
        'landscape'

    );
    // Apply filter on button click
    // applyFilter('.apply-filter');
});
</script>
@include('partials.index-filter',$filterArray)
@endsection