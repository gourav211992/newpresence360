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
                            <h2 class="content-header-title float-start mb-0">Maintenance BOM</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>  
                                    <li class="breadcrumb-item active">BOM List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button> 
						<a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{route('maint-bom.create')}}"><i data-feather="plus-circle"></i> Add New</a> 
                    </div>
                </div>
            </div>
            <div class="content-body">
                 
                
				
				<section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
								
								   
                                <div class="table-responsive">
									<table class="datatables-basic table myrequesttablecbox "> 
                                        <thead>
                                             <tr>
												<th>#</th>
												<th>Date</th>
												<th>Series</th>
												<th>Doc No.</th>
												<th>BOM Name</th>
												<th class="text-end">Status</th>
										  </tr>
											</thead>
											<tbody>
												@isset($data)
                                                @foreach($data as $d)
                                                    <tr>
                                                        <td class="text-nowrap">{{ $loop->iteration }}</td>
                                                        <td class="fw-bolder text-dark text-nowrap">
                                                            {{ \Carbon\Carbon::parse($d->document_date)->format('d-m-Y') ?? '-' }}
                                                        </td>
                                                        <td class="text-nowrap">{{ $d?->book?->book_code ?? '-' }}</td>
                                                        <td class="text-nowrap">{{ $d->document_number ?? '-' }}</td>
                                                        <td class="text-nowrap">
                                                            {{ $d->bom_name ?? '-' }}</td>
                                                        <td class="tableactionnew">
                                                            <div class="d-flex align-items-center justify-content-end">
                                                                @php $statusClasss = App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$d->document_status??"draft"];  @endphp
                                                                <span
                                                                    class='badge rounded-pill {{ $statusClasss }} badgeborder-radius'>
                                                                    @if ($d->document_status == App\Helpers\ConstantHelper::APPROVAL_NOT_REQUIRED)
                                                                        Approved
                                                                    @else
                                                                        {{ ucfirst($d->document_status) }}
                                                                    @endif
                                                                </span>


                                                                <div class="dropdown">
                                                                    <button type="button"
                                                                        class="btn btn-sm dropdown-toggle hide-arrow p-0"
                                                                        data-bs-toggle="dropdown">
                                                                        <i data-feather="more-vertical"></i>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-end">
                                                                        @if ($d->document_status == 'draft')
                                                                            <a class="dropdown-item"
                                                                                href="{{ route('maint-bom.edit', $d->id) }}">
                                                                                <i data-feather="edit" class="me-50"></i>
                                                                                <span>View</span>
                                                                            </a>
                                                                        @else
                                                                            <a class="dropdown-item"
                                                                                href="{{ route('maint-bom.show', $d->id) }}">
                                                                                <i data-feather="edit" class="me-50"></i>
                                                                                <span>View</span>
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
											  </tr>
                                              @endforeach
                                              @endisset
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
        handleRowSelection('.datatables-basic');
    </script>
 
@endsection