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
                            <h2 class="content-header-title float-start mb-0">Defect Notifications</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{route('/')}}">Home</a></li>  
                                    <li class="breadcrumb-item active">Defect Notification List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button> 
						<a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{route('defect-notification.create')}}"><i data-feather="plus-circle"></i> Add New</a> 
                    </div>
                </div>
            </div>
            <div class="content-body">
                 
                
				
				<section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
								
								   
                                <div class="table-responsive">
									<table id="defect-notifications-table" class="datatables-basic table myrequesttablecbox "> 
                                        <thead>
                                             <tr>
                                                <th height="18">#</th>
												<th>Date</th>
												<th>Equipment</th>
												<th>Category</th>
												<th>Location</th>
												<th>Defect Type</th>
												<th>Problem</th>
												<th>Priority</th>
												<th class="text-end">Status</th>
										  </tr>
											</thead>
											<tbody>
												<!-- Data will be loaded via Ajax -->
											</tbody>
									</table>
								</div>
								
								
								
								
                            </div>
                        </div>
                    </div>
                     
                </section>
                 

            </div>
        </div>
    </div>
    <!-- END: Content-->


    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

    <!-- BEGIN: Footer-->
    <!-- END: Footer-->
	
	 
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
<!--                        <input type="text" id="fp-default" class="form-control flatpickr-basic" placeholder="YYYY-MM-DD" />-->
						  <input type="text" id="fp-range" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
					</div>
					
					<div class="mb-1">
						<label class="form-label">Series</label>
						<select class="form-select">
							<option>Select</option>
						</select>
					</div> 
                    
                    <div class="mb-1">
						<label class="form-label">BOM Name</label>
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
   @section('scripts')
    <script type="text/javascript" src="{{ asset('assets/js/modules/finance-table.js') }}"></script>
    <script>
       $(function() {
        const dt = initializeBasicDataTable('.datatables-basic', 'Maintenance BOM');
        $('div.head-label').html('<h6 class="mb-0">Maintenance BOM</h6>');
    });



        function showToast(icon, title) {
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                },
            });
            Toast.fire({
                icon,
                title
            });
        }

        @if (session('success'))
            showToast("success", "{{ session('success') }}");
        @endif

        @if (session('error'))
            showToast("error", "{{ session('error') }}");
        @endif


        @if ($errors->any())
            showToast('error',
                "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
            );
        @endif

        $(document).ready(function() {
            // Check if DataTable is already initialized and destroy it
            if ($.fn.DataTable.isDataTable('#defect-notifications-table')) {
                $('#defect-notifications-table').DataTable().destroy();
            }

            // Initialize Flatpickr for date range filter
            $('#fp-range').flatpickr({
                mode: 'range',
                dateFormat: 'Y-m-d',
                onChange: function(selectedDates, dateStr, instance) {
                    if (typeof defectNotificationsTable !== 'undefined') {
                        defectNotificationsTable.ajax.reload();
                    }
                }
            });

            // Initialize DataTable
            var defectNotificationsTable = $('#defect-notifications-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route("defect-notification.ajax-data") }}',
                    data: function(d) {
                        // Add date range filter
                        var dateRange = $('#fp-range').val();
                        if (dateRange) {
                            var dates = dateRange.split(' to ');
                            if (dates.length === 2) {
                                d.start_date = dates[0];
                                d.end_date = dates[1];
                            }
                        }
                    }
                },
                columns: [
                    { data: 0, name: 'id', orderable: false, searchable: false }, // Row number
                    { data: 1, name: 'document_date' },
                    { data: 2, name: 'equipment.name', defaultContent: '-', orderable: false },
                    { data: 3, name: 'category', orderable: true },
                    { data: 4, name: 'location.name', defaultContent: '-', orderable: false },
                    { data: 5, name: 'defect_type', orderable: true },
                    { data: 6, name: 'problem', orderable: true },
                    { data: 7, name: 'priority', orderable: true },
                    { data: 8, name: 'document_status', orderable: false, searchable: false }
                ],
                order: [[1, 'desc']], // Order by date column
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
                    emptyTable: 'No defect notifications found',
                    zeroRecords: 'No matching defect notifications found'
                },
                drawCallback: function(settings) {
                    // Re-initialize Feather icons after table draw
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                }
            });

            // Apply filter when modal is closed with date range
            $('#filter').on('hidden.bs.modal', function() {
                var dateRange = $('#fp-range').val();
                if (dateRange) {
                    defectNotificationsTable.ajax.reload();
                }
            });

            // Clear filter functionality
            window.clearDateFilter = function() {
                $('#fp-range').val('');
                defectNotificationsTable.ajax.reload();
            };
        });
    </script>
 
@endsection