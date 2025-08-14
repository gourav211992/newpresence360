@extends('layouts.app')
@section('content')
<div class="app-content content ">
   <div class="content-overlay"></div>
   <div class="header-navbar-shadow"></div>
   <div class="content-wrapper container-xxl p-0">
      <div class="content-header row">
         @include('layouts.partials.breadcrumb-list', [
         'title' => 'Production Work Orders',
         'menu' => 'Home', 
         'menu_url' => url('home'),
         'sub_menu' => 'PWO List'
         ])
         <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
            <div class="form-group breadcrumb-right">
               <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button> 
               @if(count($servicesBooks['services']))
               <a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{route('pwo.create')}}"><i data-feather="plus-circle"></i> Add New</a> 
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
                                 <th style="display:none;"></th> 
                                 <th>Date</th>
                                 <th>Series</th>
                                 <th>Doc No.</th>
                                 <th>Location</th>
                                 <th>Items</th>
                                 <th>SO No.</th>
                                 <th style="text-align: right">Status</th> 
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
        return data ? data : ''; 
    }
    var columns = [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'id', name: 'id', visible: false, searchable: false },
        { data: 'document_date', name: 'document_date', render: renderData },
        { data: 'book_name', name: 'book_name', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
         },
        { data: 'document_number', name: 'document_number', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            } 
         }, 
        { data: 'location', name: 'location', render: renderData },
        { data: 'items', name: 'items', render: renderData },
        { data: 'so_no', name: 'so_no', render: renderData },
         { data: 'document_status', name: 'document_status', render: renderData, createdCell: function(td, cellData, rowData, row, col) {
               $(td).addClass('no-wrap');
            }
        },
    ];
    // Define your dynamic filters
    var filters = {
        status: '#filter-status',         // Status filter (dropdown)
        category: '#filter-category',     // Category filter (dropdown)
        item_code: '#filter-item-code'    // Item code filter (input text field)
    };
    var exportColumns = [0, 1, 2, 3, 4, 5, 6]; // Columns to export
    initializeDataTable('.datatables-basic', 
        "{{ route('pwo.index') }}", 
        columns,
        filters,  // Apply filters
        'PWO',  // Export title
        exportColumns,  // Export columns
        [[1, "desc"]] // default order

    );
    // Apply filter on button click
    // applyFilter('.apply-filter');
});

</script>
@endsection