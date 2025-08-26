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
                        <h2 class="content-header-title float-start mb-0">Return Goods Receipt</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active">RGR List</li>
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
                    <a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{ route('rgr.create') }}">
                        <i data-feather="plus-circle"></i> Add New
                    </a>
                </div>
            </div>
        </div>
        <div class="content-body">
            <section id="basic-datatable">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="table-responsive">
                                <table class="table myrequesttablecbox" id="rgrTable">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Date</th>
                                            <th>Series</th>
                                            <th>Doc No.</th>
                                            <th>Location</th>
                                            <th>Items</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
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
            {{-- Status --}}
            <div class="mb-1">
				<label class="form-label">Status</label>
				<select id="filter-status" name="status" class="form-select">
					<option value="">Select Status</option>
					<option value="draft">Draft</option>
					<option value="submitted">Submitted</option>
					<option value="rejected">Rejected</option>
				</select>
			</div>

            {{-- Series / Book --}}
            <div class="mb-1">
				<label class="form-label">Series (Book)</label>
				<select id="filter-book" name="book_id" class="form-select">
					<option value="">Select Series</option>
					@foreach($books as $book)
						<option value="{{ $book->id }}">{{ $book->book_code }}</option>
					@endforeach
				</select>
			</div>
            {{-- Location --}}
            <div class="mb-1">
				<label class="form-label">Location</label>
				<select id="filter-location" name="location_id" class="form-select">
					<option value="">Select Location</option>
					@foreach($locations as $loc)
						<option value="{{ $loc->id }}">{{ $loc->store_name }}</option>
					@endforeach
				</select>
			</div>

         </div>
         <div class="modal-footer justify-content-start">
            <button type="button" class="btn btn-primary apply-filter mr-1">Apply</button>
            <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
         </div>
      </form>
   </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript" src="{{ asset('assets/js/modules/common-datatable.js') }}"></script>
<script>
 $(window).on("load", function () {
     if (feather) feather.replace({ width: 14, height: 14 });
 });

$(document).ready(function() {
    function renderData(data) { return data ? data : ''; }

    var columns = [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'document_date', name: 'document_date', render: renderData },
        { data: 'book_name', name: 'book_name', render: renderData, createdCell: td => $(td).addClass('no-wrap') },
        { data: 'document_number', name: 'document_number', render: renderData, createdCell: td => $(td).addClass('no-wrap') },
        { data: 'location', name: 'location', render: renderData },
        { data: 'items', name: 'items', render: renderData },
        { data: 'action', name: 'action', orderable: false, searchable: false }
    ];

   var filters = { 
		status: '#filter-status',
		date_range: '#fp-range',
		book_id: '#filter-book',
		location_id: '#filter-location'
	};
    var exportColumns = [0,1,2,3,4,5,6];

    initializeDataTable('#rgrTable', 
        "{{ route('rgr.index') }}",
        columns,
        filters,
        'RGR',
        exportColumns,
        [[1, "desc"]] // document_date column
    );

    $('.apply-filter').on('click', function() {
        $('#rgrTable').DataTable().ajax.reload();
        $('#filter').modal('hide');
    });
});
</script>
@endsection
