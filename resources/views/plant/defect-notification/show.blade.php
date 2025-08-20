@extends('layouts.app')
@section('content')

{{-- If status is DRAFT and not viewing revision history, redirect to edit view --}}
@if($defectNotification->document_status === \App\Helpers\ConstantHelper::DRAFT && !request()->has('revisionNumber'))
    <script>
        window.location.replace("{{ route('defect-notification.edit', $defectNotification->id) }}");
    </script>
@endif

    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
					<div class="content-header-left col-md-6 mb-2">
						<div class="row breadcrumbs-top">
							<div class="col-12">
								<h2 class="content-header-title float-start mb-0">Defect Notification</h2>
								<div class="breadcrumb-wrapper">
									<ol class="breadcrumb">
										<li class="breadcrumb-item"><a href="{{route('/')}}">Home</a>
										</li>  
										<li class="breadcrumb-item"><a href="{{ route('defect-notification.index') }}">Defect Notifications</a></li>
										<li class="breadcrumb-item active">View</li>

									</ol>
								</div>
							</div>
						</div>
					</div>
					
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right">
							<a href="{{ route('defect-notification.index') }}"> <button class="btn btn-secondary btn-sm"><i
										data-feather="arrow-left-circle"></i> Back</button>
							</a>
							@if($buttons['amend'] && intval(request('amendment') ?? 0))
                                <button type="button" class="btn btn-primary btn-sm" id="amendmentBtn"><i data-feather="check-circle"></i> Submit</button>
                            @else
                                @if($buttons['approve'])
                                    <button type="button" class="btn btn-primary btn-sm" id="approved-button" name="action"
                                        value="approved"><i data-feather="check-circle"></i> Approve</button>
                                    <button type="button" id="reject-button"
                                        class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" class="feather feather-x-circle">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg> Reject</button>
                                @endif
                                @if($buttons['amend'])
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm"
                                        class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                                @endif
                            @endif
                            
						</div>
					</div>
				</div>
			</div>
            <div class="content-body">
                <form id="defect-notification-form" method="POST" action="{{ route('defect-notification.update', $defectNotification->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="book_code" id="book_code_input" value="{{ $defectNotification->book_code }}">
                    <input type="hidden" name="doc_number_type" id="doc_number_type" value="{{ $defectNotification->doc_number_type }}">
                    <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern" value="{{ $defectNotification->doc_reset_pattern }}">
                    <input type="hidden" name="doc_prefix" id="doc_prefix" value="{{ $defectNotification->doc_prefix }}">
                    <input type="hidden" name="doc_suffix" id="doc_suffix" value="{{ $defectNotification->doc_suffix }}">
                    <input type="hidden" name="doc_no" id="doc_no" value="{{ $defectNotification->doc_no }}">
                    <input type="hidden" name="document_status" id="document_status" value="{{ $defectNotification->document_status }}">
                    
                   
				<section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            
                            
                            <div class="card">
								 <div class="card-body customernewsection-form">  
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between"> 
                                                    <div>
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Update the details</p>
                                                    </div> 
                                                </div> 
                                            </div> 

                                           

                                            <div class="col-md-8"> 
												
													<div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Series <span class="text-danger">*</span></label>  
                                                        </div>  
														<div class="col-md-5">  
															<select class="form-select" id="book_id" name="book_id" required>
																@if(isset($series) && count($series) > 0)
																	@foreach($series as $book)
																		<option value="{{ $book->id }}" {{ $book->id == $defectNotification->book_id ? 'selected' : '' }}>{{ $book->book_code }}</option>
																	@endforeach
																@else
																	<option value="">No series available</option>
																@endif
															</select>
														</div>
                                                     </div>

													 <input type="hidden" name="book_id" value="{{ $defectNotification->book_id }}">
													 <input type="hidden" name="doc_no" value="{{ $defectNotification->doc_no }}">

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Doc No <span class="text-danger">*</span></label>  
                                                        </div>  

                                                        <div class="col-md-5"> 
														<input type="text" class="form-control" id="document_number" name="document_number" value="{{ $defectNotification->document_number }}" required>
                                                        </div> 
                                                     </div>  

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Doc Date <span class="text-danger">*</span></label>  
                                                        </div>  

                                                        <div class="col-md-5"> 
															<input type="date" value="{{ $defectNotification->document_date ? \Carbon\Carbon::parse($defectNotification->document_date)->format('Y-m-d') : date('Y-m-d') }}" class="form-control" id="document_date" name="document_date" min="{{ date('Y-m-d') }}" required>
														</div>
                                                     </div>
												
													<div class="row align-items-center mb-1">
														<div class="col-md-3"> 
															<label class="form-label">Location <span class="text-danger">*</span></label>  
														</div>  

														<div class="col-md-5">  
															<select class="form-select" name="location_id" id="location_id" required>
																<option value="">Select Location</option>
																@foreach($locations ?? [] as $location)
																	<option value="{{ $location->id }}" {{ $location->id == $defectNotification->location_id ? 'selected' : '' }}>{{ $location->store_name }}</option>
																@endforeach
															</select>
														</div>
													 </div>
   
                                            </div> 

                                            @include('partials.approval-history', ['document_status' => $defectNotification->document_status, 'revision_number' => $defectNotification->revision_number])
                                            
                                        </div> 
                                        
                                </div>
                            </div>
                            
                             <div class="row">
                                <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">Equipment Details</h4> 
                                                </div>
                                            </div>
                                            <div class="card-body"> 
                                                <div class="row">

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Category <span class="text-danger">*</span></label> 
                                                            <select class="form-select" name="category_id" id="category_id" required>
                                                                <option value="">Select Category</option>
                                                                @foreach($categories ?? [] as $category)
                                                                    <option value="{{ $category->id }}" {{ $category->id == $defectNotification->category_id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Equipment <span class="text-danger">*</span></label> 
                                                            <select class="form-select" name="equipment_id" id="equipment_id" required>
                                                                <option value="">Select Equipment</option>
                                                                @foreach($equipments ?? [] as $equipment)
                                                                    <option value="{{ $equipment->id }}" {{ $equipment->id == $defectNotification->equipment_id ? 'selected' : '' }}>{{ $equipment->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div> 
 

                                                    
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Defect Type <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="defect_type_id" id="defect_type_id" required>
                                                                <option value="">Select Defect Type</option>
                                                                @foreach($defectTypes ?? [] as $defectType)
                                                                    <option value="{{ $defectType->id }}" {{ $defectType->id == $defectNotification->defect_type_id ? 'selected' : '' }}>{{ $defectType->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Problem <span class="text-danger">*</span></label>
                                                            <input type="text" name="problem" value="{{ $defectNotification->problem ?? '' }}" class="form-control" /> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="priority">
                                                                <option value="">Select</option> 
                                                                <option value="High" {{ $defectNotification->priority == 'High' ? 'selected' : '' }}>High</option> 
                                                                <option value="Medium" {{ $defectNotification->priority == 'Medium' ? 'selected' : '' }}>Medium</option> 
                                                                <option value="Low" {{ $defectNotification->priority == 'Low' ? 'selected' : '' }}>Low</option> 
                                                                <option value="Critical" {{ $defectNotification->priority == 'Critical' ? 'selected' : '' }}>Critical</option> 
                                                            </select>  
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Report Date & Time <span class="text-danger">*</span></label>
                                                            <input type="datetime-local" name="report_date_time" value="{{ $defectNotification->report_date_time ? \Carbon\Carbon::parse($defectNotification->report_date_time)->format('Y-m-d\TH:i') : '' }}" class="form-control" /> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Attachment</label>
                                                            <input type="file" name="upload_document" class="form-control" /> 
                                                        </div>
                                                    </div>
													 
													<div class="col-md-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Detailed observations</label>
                                                            <input type="text" name="detailed_oberservation" class="form-control" value="{{ $defectNotification->detailed_oberservation ?? '' }}" />
                                                        </div>
                                                    </div>
                                                 </div>
                                                 
                                            </div>
                                        </div>
                                    </div>    
                            </div>
                        </div>
                    </div>
                    <!-- Modal to add new record -->
                </section>
                </form>

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

    <div class="modal fade" id="approved" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Close the Maintenance</h4> 
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body pb-2"> 

					<div class="row mt-1"> 
						
						   <div class="col-md-12">  
                               
                                    <div class="mb-1">
                                       <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                       <textarea class="form-control"></textarea>
                                     </div>
							   
							   		  <div class="mb-1">
                                       <label class="form-label">Upload Document</label>
                                       <input type="file" class="form-control" />
                                     </div>
                     
                            </div>
						  
					</div>
				</div>
				
				<div class="modal-footer justify-content-center">  
						<button type="reset" class="btn btn-outline-secondary me-1">Cancel</button> 
					<button type="reset" class="btn btn-primary">Submit</button>
				</div>
			</div>
		</div>
	</div>
     
    
    <div class="modal fade text-start" id="reference" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select Equipment</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					 <div class="row">
                         
                         <div class="col">
                            <div class="mb-1">
                               <label class="form-label">Equipment</label>
                               <input type="text" placeholder="Select" class="form-control ledgerselecct" />
                            </div>
                        </div>
                         
                         <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Maintenance Type</label>
                                <input type="text" placeholder="Select" class="form-control ledgerselecct" />
                            </div>
                        </div>
                          
                         
                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Maint. BOM</label>
                                <input type="text" placeholder="Select" class="form-control ledgerselecct" />
                            </div>
                        </div>
						  
                         
                         <div class="col  mb-1">
                              <label class="form-label">&nbsp;</label><br/>
                             <button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                         </div>

						 <div class="col-md-12">
 

							<div class="table-responsive">
								<table class="mt-1 table table-striped po-order-detail"> 
									<thead>
													 <tr>
													 	<th width="62" class="customernewsection-form">
															<div class="form-check form-check-primary custom-checkbox">
																<input type="checkbox" class="form-check-input sp-select">
																<label class="form-check-label" for="Email"></label>
															</div> 
													 	</th>
											<th>Equipment</th>  
											<th>Maintenance Type</th>
											<th>BOM</th> 
											<th>Series</th> 
											<th>Doc No</th>
										  </tr>
										</thead>
										<tbody>
											<tr class="trail-bal-tabl-none">
											    <th class="customernewsection-form">
													<div class="form-check form-check-primary custom-radio">
														<input type="radio" class="form-check-input equipment-radio" name="equipmentRadio" id="equipment_1" data-equipment-id="1">
														<label class="form-check-label" for="equipment_1"></label>
													</div> 
												</th> 
												<td><strong>Procesor</strong></td> 
												<td>Running</td>
												<td>Plant</td>
												<td>BOM</td>
												<td>01</td>
											</tr>
											<tr class="trail-bal-tabl-none">
											    <th class="customernewsection-form">
													<div class="form-check form-check-primary custom-radio">
														<input type="radio" class="form-check-input equipment-radio" name="equipmentRadio" id="equipment_2" data-equipment-id="2">
														<label class="form-check-label" for="equipment_2"></label>
													</div> 
												</th>
												<td><strong>Procesor</strong></td> 
												<td>Running</td>
												<td>Plant</td>
												<td>BOM</td>
												<td>01</td>
											</tr>
											<tr class="trail-bal-tabl-none">
											    <th class="customernewsection-form">
													<div class="form-check form-check-primary custom-radio">
														<input type="radio" class="form-check-input equipment-radio" name="equipmentRadio" id="equipment_3" data-equipment-id="3">
														<label class="form-check-label" for="equipment_3"></label>
													</div> 
												</th>
												<td><strong>Procesor</strong></td> 
												<td>Running</td>
												<td>Plant</td>
												<td>BOM</td>
												<td>01</td>
												
											</tr>
											<tr class="trail-bal-tabl-none">
											    <th class="customernewsection-form">
													<div class="form-check form-check-primary custom-radio">
														<input type="radio" class="form-check-input equipment-radio" name="equipmentRadio" id="equipment_4" data-equipment-id="4">
														<label class="form-check-label" for="equipment_4"></label>
													</div> 
												</th>
												<td><strong>Procesor</strong></td> 
												<td>Running</td>
												<td>Plant</td>
												<td>BOM</td>
												<td>01</td>
											</tr>
											<tr class="trail-bal-tabl-none">
											    <th class="customernewsection-form">
													<div class="form-check form-check-primary custom-radio">
														<input type="radio" class="form-check-input equipment-radio" name="equipmentRadio" id="equipment_5" data-equipment-id="5">
														<label class="form-check-label" for="equipment_5"></label>
													</div> 
												</th>
												<td><strong>Procesor</strong></td> 
												<td>Running</td>
												<td>Plant</td>
												<td>BOM</td>
												<td>01</td>
											</tr>
											 
											  
										</tbody>


								</table>
							</div>
						</div>


					 </div>
				</div>
				<div class="modal-footer text-end">
					<button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
					<button id="equipment_process_btn" onclick="processEquipmentSelection()" class="btn btn-primary btn-sm"><i data-feather="check-circle"></i> Process</button>
				</div>
			</div>
		</div>
	</div> 
	
	<div class="modal fade text-start" id="defectlog" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select Defect</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					 <div class="row">
                         
                         <div class="col">
                            <div class="mb-1">
                               <label class="form-label">Equipment</label>
                               <input type="text" placeholder="Select" class="form-control ledgerselecct" />
                            </div>
                        </div>
                         
                         <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Defect Type</label>
                                <input type="text" placeholder="Select" class="form-control ledgerselecct" />
                            </div>
                        </div>
						 
						 <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Priority</label>
                                <select class="form-select">
									<option>Select</option>
									<option>High</option>
									<option>Medium</option>
									<option>Low</option>
								</select>
                            </div>
                        </div>
                          
                         
                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Series</label>
                                <input type="text" placeholder="Select" class="form-control ledgerselecct" />
                            </div>
                        </div>
						 
						  
                         
                         <div class="col  mb-1">
                              <label class="form-label">&nbsp;</label><br/>
                             <button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                         </div>

						 <div class="col-md-12">
 

							<div class="table-responsive">
								<table class="mt-1 table table-striped po-order-detail"> 
									<thead>
										 <tr>
										    <th class="customernewsection-form">
												<div class="form-check form-check-primary custom-radio">
													<input type="radio" class="form-check-input defect-radio" name="defectRadio" id="defect_header" disabled>
													<label class="form-check-label" for="defect_header"></label>
												</div> 
											</th>
											<th>Date</th> 
											<th>Series</th> 
											<th>Doc No</th>
											<th>Equipment</th>  
											<th>Defect Type</th>
											<th>Priority</th> 
											<th>Problem</th>  
											<th>Reported By</th>  
										  </tr>
										</thead>
										<tbody>
											<tr class="trail-bal-tabl-none">
											    <td class="customernewsection-form">
													<div class="form-check form-check-primary custom-radio">
														<input type="radio" class="form-check-input" name="defect_selection" id="defect_row_1">
														<label class="form-check-label" for="defect_row_1"></label>
													</div> 
												</td>
												<td><strong>23-07-2025</strong></td> 
												<td>DEF</td>
												<td>001</td>
												<td>Plant</td>
												<td>Breakdown</td>
												<td>High</td>
												<td>Please resolve ASAP</td>
												<td>Aniket Singh</td>
											</tr>
											<tr class="trail-bal-tabl-none">
											    <td class="customernewsection-form">
													<div class="form-check form-check-primary custom-radio">
														<input type="radio" class="form-check-input" name="defect_selection" id="defect_row_2">
														<label class="form-check-label" for="defect_row_2"></label>
													</div> 
												</td>
												<td><strong>23-07-2025</strong></td> 
												<td>DEF</td>
												<td>001</td>
												<td>Plant</td>
												<td>Breakdown</td>
												<td>High</td>
												<td>Please resolve ASAP</td>
												<td>Aniket Singh</td>
											</tr>
											
											<tr class="trail-bal-tabl-none">
											    <td class="customernewsection-form">
													<div class="form-check form-check-primary custom-radio">
														<input type="radio" class="form-check-input" name="defect_selection" id="defect_row">
														<label class="form-check-label" for="defect_row"></label>
													</div> 
												</td>
												<td><strong>23-07-2025</strong></td> 
												<td>DEF</td>
												<td>001</td>
												<td>Plant</td>
												<td>Breakdown</td>
												<td>High</td>
												<td>Please resolve ASAP</td>
												<td>Aniket Singh</td>
											</tr>
											
											
											<tr class="trail-bal-tabl-none">
											    <td class="customernewsection-form">
													<div class="form-check form-check-primary custom-radio">
														<input type="radio" class="form-check-input" name="defect_selection" id="defect_row">
														<label class="form-check-label" for="defect_row"></label>
													</div> 
												</td>
												<td><strong>23-07-2025</strong></td> 
												<td>DEF</td>
												<td>001</td>
												<td>Plant</td>
												<td>Breakdown</td>
												<td>High</td>
												<td>Please resolve ASAP</td>
												<td>Aniket Singh</td>
											</tr>
											
											<tr class="trail-bal-tabl-none">
											    <td class="customernewsection-form">
													<div class="form-check form-check-primary custom-radio">
														<input type="radio" class="form-check-input" name="defect_selection" id="defect_row">
														<label class="form-check-label" for="defect_row"></label>
													</div> 
												</td>
												<td><strong>23-07-2025</strong></td> 
												<td>DEF</td>
												<td>001</td>
												<td>Plant</td>
												<td>Breakdown</td>
												<td>High</td>
												<td>Please resolve ASAP</td>
												<td>Aniket Singh</td>
											</tr>
											 
											  
										</tbody>


								</table>
							</div>
						</div>


					 </div>
				</div>
				<div class="modal-footer text-end">
					<button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
					<button id="defect_process_btn" onclick="processDefectSelection()" class="btn btn-primary btn-sm"><i data-feather="check-circle"></i> Process</button>
				</div>
			</div>
		</div>
	</div>
    
    <div class="modal fade" id="Remarks" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered" >
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Add/Edit Remarks</h1>
					<p class="text-center">Enter the details below.</p>
                    
                    
                     <div class="row mt-2">
                         
						
						<div class="col-md-12 mb-1">
							<label class="form-label">Remarks <span class="text-danger">*</span></label>
							<textarea class="form-control" placeholder="Enter Remarks"></textarea>
						</div> 
                    
                    </div>

					 
                    
				</div>
				
				<div class="modal-footer justify-content-center">  
						<button type="reset" class="btn btn-outline-secondary me-1">Cancel</button> 
					<button type="reset" class="btn btn-primary">Submit</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Attribute Modal -->
	<div class="modal fade" id="attribute" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Select Attribute</h1>
					<p class="text-center">Enter the details below.</p>

					<div class="table-responsive-md customernewsection-form">
						<table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"
							id="attributes_table_modal" item-index="">
							<thead>
								<tr>
									<th>Attribute Name</th>
									<th>Attribute Value</th>
								</tr>
							</thead>
							<tbody id="attribute_table">

							</tbody>

						</table>
					</div>
				</div>

				<div class="modal-footer justify-content-center">
					<button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('attribute');">Cancel</button>
					<button type="button" class="btn btn-primary submitAttributeBtn" onclick="closeModal('attribute');">Select</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Amendment Confirmation Modal -->
	<div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body alertmsg text-center warning">
					<i data-feather='alert-circle'></i>
					<h2>Are you sure?</h2>
					<p>Are you sure you want to <strong>Amend</strong> this <strong>Defect Notification</strong></p>
					<button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
					<button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
				</div> 
			</div>
		</div>
	</div>

	<!-- Amendment Submit Modal -->
	<div class="modal fade" id="amendmentSubmitModal" tabindex="-1" aria-labelledby="amendmentSubmitModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="amendmentSubmitModalLabel">Submit Amendment</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label for="amendment_remarks" class="form-label">Amendment Remarks <span class="text-danger">*</span></label>
						<textarea class="form-control" id="amendment_remarks" name="amendment_remarks" rows="4" placeholder="Please provide detailed remarks for this amendment..." required></textarea>
					</div>
					<div class="mb-3">
						<label for="amendment_attachment" class="form-label">Supporting Document (Optional)</label>
						<input type="file" class="form-control" id="amendment_attachment" name="amendment_attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
						<small class="text-muted">Accepted formats: PDF, DOC, DOCX, JPG, PNG (Max: 10MB)</small>
					</div>
				</div>
				
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="confirmAmendmentSubmit">
						<i data-feather="check-circle"></i> Submit Amendment
					</button>
				</div>
			</div>
		</div>
	</div>

    </form>
@endsection

@section('scripts')
	<script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>

	<script>
		const itemsData = @json($items);
		let rowCount = 1;
		$(window).on('load', function () {
			if (feather) {
				feather.replace({
					width: 14,
					height: 14
				});
			}
		})

		// Revision Number dropdown functionality
		$('#revisionNumber').prop('disabled', false).prop('readonly', false);
		
		$(document).on('change', '#revisionNumber', (e) => {
			let actionUrl = location.pathname + '?revisionNumber=' + e.target.value;
			let revision_number = Number("{{ $defectNotification->revision_number }}");
			let revisionNumber = Number(e.target.value);
			if (revision_number == revisionNumber) {
				location.href = actionUrl;
			} else {
				window.open(actionUrl, '_blank');
			}
		});

		// Form submission functionality
		$('#defect-notification-form').on('submit', function(e) {
			e.preventDefault();
			$('#document_status').val('submitted');
			collectEquipmentDetailsAndSubmit();
		});

		$('#save-draft-btn').on('click', function() {
			$('#document_status').val('draft');
			collectEquipmentDetailsAndSubmit();
		});

		// Function to collect Equipment Details fields and submit form
		function collectEquipmentDetailsAndSubmit() {
			// Get the main form
			let form = $('#defect-notification-form');
			
			// Remove any existing hidden fields for equipment details to avoid duplicates
			form.find('input[name="category_id_hidden"]').remove();
			form.find('input[name="equipment_id_hidden"]').remove();
			form.find('input[name="defect_type_id_hidden"]').remove();
			form.find('input[name="problem_hidden"]').remove();
			form.find('input[name="priority_hidden"]').remove();
			form.find('input[name="report_date_time_hidden"]').remove();
			form.find('input[name="detailed_oberservation_hidden"]').remove();
			
			// Collect values from Equipment Details section
			let categoryId = $('#category_id').val();
			let equipmentId = $('#equipment_id').val();
			let defectTypeId = $('#defect_type_id').val();
			let problem = $('input[name="problem"]').val();
			let priority = $('select[name="priority"]').val();
			let reportDateTime = $('input[name="report_date_time"]').val();
			let detailedObservation = $('input[name="detailed_oberservation"]').val();
			
			// Add hidden fields to main form with collected values
			if (categoryId) {
				form.append('<input type="hidden" name="category_id" value="' + categoryId + '">');
			}
			if (equipmentId) {
				form.append('<input type="hidden" name="equipment_id" value="' + equipmentId + '">');
			}
			if (defectTypeId) {
				form.append('<input type="hidden" name="defect_type_id" value="' + defectTypeId + '">');
			}
			if (problem) {
				form.append('<input type="hidden" name="problem" value="' + problem + '">');
			}
			if (priority) {
				form.append('<input type="hidden" name="priority" value="' + priority + '">');
			}
			if (reportDateTime) {
				form.append('<input type="hidden" name="report_date_time" value="' + reportDateTime + '">');
			}
			if (detailedObservation) {
				form.append('<input type="hidden" name="detailed_oberservation" value="' + detailedObservation + '">');
			}
			
			// Now submit the form with validation
			submitForm();
		}

		function submitForm() {
			// Validate required fields
			let isValid = true;
			let errorMessage = '';
			let documentStatus = $('#document_status').val();

			// Always required fields
			if (!$('#book_id').val()) {
				isValid = false;
				errorMessage += 'Series is required.\n';
			}
			if (!$('#document_number').val()) {
				isValid = false;
				errorMessage += 'Document Number is required.\n';
			}
			if (!$('#document_date').val()) {
				isValid = false;
				errorMessage += 'Document Date is required.\n';
			}
			if (!$('#location_id').val()) {
				isValid = false;
				errorMessage += 'Location is required.\n';
			}

			// Only validate additional fields if not saving as draft
			if (documentStatus !== 'draft') {
				if (!$('#category_id').val()) {
					isValid = false;
					errorMessage += 'Category is required.\n';
				}
				if (!$('#equipment_id').val()) {
					isValid = false;
					errorMessage += 'Equipment is required.\n';
				}
				if (!$('#defect_type_id').val()) {
					isValid = false;
					errorMessage += 'Defect Type is required.\n';
				}
				if (!$('input[name="problem"]').val()) {
					isValid = false;
					errorMessage += 'Problem description is required.\n';
				}
				if (!$('select[name="priority"]').val()) {
					isValid = false;
					errorMessage += 'Priority is required.\n';
				}
				if (!$('input[name="report_date_time"]').val()) {
					isValid = false;
					errorMessage += 'Report Date & Time is required.\n';
				}
			}

			if (!isValid) {
				showToast('error', errorMessage);
				return;
			}

			// Show loading
			$('.preloader').show();

			// Submit the form
			$('#defect-notification-form')[0].submit();
		}

		function showToast(icon, title) {
			const Toast = Swal.mixin({
				toast: true,
				position: 'top-end',
				showConfirmButton: false,
				timer: 3000,
				timerProgressBar: true,
				didOpen: (toast) => {
					toast.addEventListener('mouseenter', Swal.stopTimer)
					toast.addEventListener('mouseleave', Swal.resumeTimer)
				}
			});
			Toast.fire({
				icon,
				title
			});
		}

		// Disable all form fields except the revision number dropdown
		$('#defect-notification-form').find('input, select, textarea').not('#revisionNumber').prop('readonly', true).prop('disabled', true);

		// Handle amendment mode - enable fields when amendment=1 parameter is present
		@if(intval(request('amendment') ?? 0))
			// Enable all form fields for amendment
			$('#defect-notification-form').find('input, select, textarea').prop('readonly', false).prop('disabled', false);
			
			// Keep only series (book_id) and document number fields disabled
			$('#book_id').prop('disabled', true);
			$('#document_number').prop('readonly', true);
			$('#doc_no').prop('disabled', true);
			$('#book_code_input').prop('disabled', true);
			$('#doc_number_type').prop('disabled', true);
			$('#doc_reset_pattern').prop('disabled', true);
			$('#doc_prefix').prop('disabled', true);
			$('#doc_suffix').prop('disabled', true);
			
			// Update document status to indicate amendment mode
			$('#document_status').val('amendment');
		@endif

		// Amendment confirmation functionality following standard pattern
		$(document).on('click', '#amendmentSubmit', (e) => {
			e.preventDefault();
			let url = new URL(window.location.href);
			url.search = '';
			url.searchParams.set('amendment', 1);
			let amendmentUrl = url.toString();
			window.location.replace(amendmentUrl);
		});

		// Amendment submit functionality - show modal for remarks and document
		$(document).on('click', '#amendmentBtn', (e) => {
			e.preventDefault();
			$('#amendmentSubmitModal').modal('show');
		});

		// Handle amendment form submission
		$(document).on('click', '#confirmAmendmentSubmit', (e) => {
			e.preventDefault();
			
			const remarks = $('#amendment_remarks').val().trim();
			if (!remarks) {
				Swal.fire({
					title: 'Error!',
					text: 'Amendment remarks are required.',
					icon: 'error'
				});
				return;
			}

			// Add amendment action type and remarks to the main form
			$('<input>').attr({
				type: 'hidden',
				name: 'action_type',
				value: 'amendment'
			}).appendTo('#defect-notification-form');
			
			$('<input>').attr({
				type: 'hidden',
				name: 'amend_remarks',
				value: remarks
			}).appendTo('#defect-notification-form');

			// Handle file attachment if provided
			const fileInput = $('#amendment_attachment')[0];
			if (fileInput.files.length > 0) {
				// Create a new FormData and append the file
				const formData = new FormData($('#defect-notification-form')[0]);
				formData.append('amend_attachment', fileInput.files[0]);
				
				// Submit via AJAX with file
				$.ajax({
					url: $('#defect-notification-form').attr('action'),
					method: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function(response) {
						Swal.fire({
							title: 'Success!',
							text: 'Amendment submitted successfully.',
							icon: 'success'
						}).then(() => {
							window.location.href = "{{ route('defect-notification.index') }}";
						});
					},
					error: function(xhr) {
						Swal.fire({
							title: 'Error!',
							text: 'An error occurred while submitting the amendment.',
							icon: 'error'
						});
					}
				});
			} else {
				// Submit normally without file
				$('#defect-notification-form').submit();
			}
		});

		@if (session('success'))
			$('.preloader').hide();
			showToast("success", "{{ session('success') }}");
		@endif

		@if (session('error'))
			$('.preloader').hide();
			showToast("error", "{{ session('error') }}");
		@endif

		@if ($errors->any())
			$('.preloader').hide();
			showToast('error',
				"@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
			);
		@endif
	</script>

@endsection