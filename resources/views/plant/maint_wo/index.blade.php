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
                            <h2 class="content-header-title float-start mb-0">Maintenance WO</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>  
                                    <li class="breadcrumb-item active">Maintenance List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button> 
						<a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{route('maint-wo.create')}}"><i data-feather="plus-circle"></i> Add New</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
				<section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
								
								   
                                <div class="table-responsive">
									<table class="datatables-basic table myrequesttablecbox tableistlastcolumnfixed newerptabledesignlisthome"> 
                                        <thead>
                                             <tr>
												<th height="18">#</th>
												<th>Date</th>
												<th>Series</th>
												<th>Doc NO</th>
												<th>Location</th>
												<th>Type</th>
												<th>Equipment</th>
												<th>CAtegory</th>
												<th>Due Date</th>
												<th class="text-end">Status</th>
											  </tr>
											</thead>
											<tbody>
												 <tr>
													<td>1</td>
													<td class="fw-bolder text-dark">08-07-2025</td>
													<td>PL</td>
													<td>1</td>
													<td>Noida Sector 135</td>
													<td><span class='badge rounded-pill badge-light-secondary badgeborder-radius'>Defect</span></td>
													<td>Plant</td>
													<td>Machinery</td>
													<td>15-08-2025</td>
                                                    <td class="tableactionnew">
                                                        <div class="d-flex align-items-center justify-content-end">
                                                             <span class='badge rounded-pill badge-light-success badgeborder-radius'>Approved</span>
                                                        	<div class="dropdown">
																<button type="button"
																	class="btn btn-sm dropdown-toggle hide-arrow p-0"
																	data-bs-toggle="dropdown">
																	<i data-feather="more-vertical"></i>
																</button>
																<div class="dropdown-menu dropdown-menu-end">
																	<a class="dropdown-item"
																		href="#">
																		<i data-feather="edit-3" class="me-50"></i>
																		<span>View</span>
																	</a>
																</div>
															</div>
                                                    	</div>
                                                    </td>
												  </tr>
												  <tr>
													<td>2</td>
													<td class="fw-bolder text-dark">08-07-2025</td>
													<td>PL</td>
													<td>2</td>
													<td>Noida Sector 135</td>
													<td><span class='badge rounded-pill badge-light-secondary badgeborder-radius'>Maint.</span></td>
													<td>Plant</td>
													<td>Machinery</td>
													<td>15-08-2025</td>
													<td class="tableactionnew">
                                                        <div class="d-flex align-items-center justify-content-end">
                                                             <span class='badge rounded-pill badge-light-success badgeborder-radius'>Approved</span>
                                                        	<div class="dropdown">
																<button type="button"
																	class="btn btn-sm dropdown-toggle hide-arrow p-0"
																	data-bs-toggle="dropdown">
																	<i data-feather="more-vertical"></i>
																</button>
																<div class="dropdown-menu dropdown-menu-end">
																	<a class="dropdown-item"
																		href="#">
																		<i data-feather="edit-3" class="me-50"></i>
																		<span>View</span>
																	</a>
																</div>
															</div>
                                                    	</div>
                                                    </td>
												  </tr>
												  <tr>
													<td>3</td>
													<td class="fw-bolder text-dark">08-07-2025</td>
													<td>PL</td>
													<td>3</td>
													<td>Noida Sector 135</td>
													<td><span class='badge rounded-pill badge-light-secondary badgeborder-radius'>Maint.</span></td>
													<td>Plant</td>
													<td>Machinery</td>
													<td>15-08-2025</td>
													<td class="tableactionnew">
                                                        <div class="d-flex align-items-center justify-content-end">
                                                             <span class='badge rounded-pill badge-light-danger badgeborder-radius'>Rejected</span>
                                                        	<div class="dropdown">
																<button type="button"
																	class="btn btn-sm dropdown-toggle hide-arrow p-0"
																	data-bs-toggle="dropdown">
																	<i data-feather="more-vertical"></i>
																</button>
																<div class="dropdown-menu dropdown-menu-end">
																	<a class="dropdown-item"
																		href="#">
																		<i data-feather="edit-3" class="me-50"></i>
																		<span>View</span>
																	</a>
																</div>
															</div>
                                                    	</div>
                                                    </td>
												  </tr>
												  <tr>
													<td>4</td>
													<td class="fw-bolder text-dark">08-07-2025</td>
													<td>PL</td>
													<td>4</td>
													<td>Noida Sector 135</td>
													<td><span class='badge rounded-pill badge-light-secondary badgeborder-radius'>Defect</span></td>
													<td>Plant</td>
													<td>Machinery</td>
													<td>15-08-2025</td>
													<td class="tableactionnew">
                                                        <div class="d-flex align-items-center justify-content-end">
                                                             <span class='badge rounded-pill badge-light-primary badgeborder-radius'>Submitted</span>
                                                        	<div class="dropdown">
																<button type="button"
																	class="btn btn-sm dropdown-toggle hide-arrow p-0"
																	data-bs-toggle="dropdown">
																	<i data-feather="more-vertical"></i>
																</button>
																<div class="dropdown-menu dropdown-menu-end">
																	<a class="dropdown-item"
																		href="#">
																		<i data-feather="edit-3" class="me-50"></i>
																		<span>View</span>
																	</a>
																</div>
															</div>
                                                    	</div>
                                                    </td>
												  </tr>
												  <tr>
													<td>5</td>
													<td class="fw-bolder text-dark">08-07-2025</td>
													<td>PL</td>
													<td>5</td>
													<td>Noida Sector 135</td>
													<td><span class='badge rounded-pill badge-light-secondary badgeborder-radius'>Defect</span></td>
													<td>Plant</td>
													<td>Machinery</td>
													<td>15-08-2025</td>
													<td class="tableactionnew">
                                                        <div class="d-flex align-items-center justify-content-end">
                                                             <span class='badge rounded-pill badge-light-warning badgeborder-radius'>Draft</span>
                                                        	<div class="dropdown">
																<button type="button"
																	class="btn btn-sm dropdown-toggle hide-arrow p-0"
																	data-bs-toggle="dropdown">
																	<i data-feather="more-vertical"></i>
																</button>
																<div class="dropdown-menu dropdown-menu-end">
																	<a class="dropdown-item"
																		href="#">
																		<i data-feather="edit-3" class="me-50"></i>
																		<span>View</span>
																	</a>
																</div>
															</div>
                                                    	</div>
                                                    </td>
												  </tr>
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