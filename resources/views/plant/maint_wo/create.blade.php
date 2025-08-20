@extends('layouts.app')
@section('content')

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
								<h2 class="content-header-title float-start mb-0">Maintenance Order</h2>
								<div class="breadcrumb-wrapper">
									<ol class="breadcrumb">
										<li class="breadcrumb-item"><a href="{{route('/')}}">Home</a>
										</li>  
										<li class="breadcrumb-item active">Add New</li> 
									</ol>
								</div>
							</div>
						</div>
					</div>
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right">   
							<button  class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                            <button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" id="save-draft-btn"><i data-feather='save'></i> Save as Draft</button> 
                           <button type="submit" form="maint-wo-form" class="btn btn-primary btn-sm" id="submit-btn">
								<i data-feather="check-circle"></i> Submit
							</button>
						</div>
					</div>
				</div>
			</div>
            <div class="content-body">
                <form id="maint-wo-form" method="POST" action="{{ route('maint-wo.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="book_code" id="book_code_input">
                    <input type="hidden" name="doc_number_type" id="doc_number_type">
                    <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
                    <input type="hidden" name="doc_prefix" id="doc_prefix">
                    <input type="hidden" name="doc_suffix" id="doc_suffix">
                    <input type="hidden" name="doc_no" id="doc_no">
                    <input type="hidden" name="document_status" id="document_status">
                    <input type="hidden" name="spare_parts" id="spare_parts">
                    <input type="hidden" name="checklist_data" id="checklist_data">
                    <input type="hidden" name="equipment_details" id="equipment_details">
                    
                    <!-- Hidden inputs for readonly data -->
                    <input type="hidden" name="equipment_category" id="equipment_category_hidden" value="Machinery">
                    <input type="hidden" name="equipment_name" id="equipment_name_hidden" value="">
                    <input type="hidden" name="defect_type" id="defect_type_hidden" value="">
                    <input type="hidden" name="problem" id="problem_hidden" value="">
                    <input type="hidden" name="report_date_time" id="report_date_time_hidden" value="">
                    <input type="hidden" name="reported_by" id="reported_by_hidden" value="">

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
                                                        <p class="card-text">Fill the details</p>
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
                                                                @foreach($series as $index => $book)
                                                                    <option value="{{ $book->id }}" {{ $index === 0 ? 'selected' : '' }}>{{ $book->book_code }}</option>
                                                                @endforeach
                                                            @else
                                                                <option value="">No series available</option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3"> 
                                                        <label class="form-label">Doc No <span class="text-danger">*</span></label>  
                                                    </div>  
                                                    <div class="col-md-5"> 
                                                        <input type="text" class="form-control" id="document_number" name="document_number" required>
                                                    </div> 
                                                </div>  

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3"> 
                                                        <label class="form-label">Doc Date <span class="text-danger">*</span></label>  
                                                    </div>  
                                                    <div class="col-md-5"> 
                                                        <input type="date" value="{{ date('Y-m-d') }}" class="form-control" id="document_date" name="document_date" min="{{ date('Y-m-d') }}" required>
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
																<option value="{{ $location->id }}">{{ $location->store_name }}</option>
															@endforeach
														</select>
													</div>
													</div>
														
														 
												
													<div class="row align-items-center mb-1 selection_section"> 
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Reference From</label>  
                                                        </div> 

                                                        <div class="col-md-5 action-button"> 
                                                            <input type="hidden" name="reference_type" id="reference_type" value="">
                                                            <button type="button" id="equipment_ref_btn" onclick="selectEquipmentReference()" data-bs-toggle="modal" data-bs-target="#reference" class="btn btn-outline-primary btn-sm mb-0 reference-btn"><i data-feather="plus-square"></i> Equipment</button>
                                                            <button type="button" id="defect_ref_btn" onclick="selectDefectNotificationReference()" data-bs-toggle="modal" data-bs-target="#defectlog" class="btn btn-outline-primary btn-sm mb-0 reference-btn"><i data-feather="plus-square"></i> Defect Notification</button>
                                                            <div id="reference_type_error" class="text-danger mt-1" style="display: none;">Please select at least one reference type (Equipment or Defect Notification)</div>
                                                        </div>
                                                    </div>
                                            </div> 
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

                                                    <div class="col-md-3 basic-equipment-field">
                                                        <div class="mb-1">
                                                            <label class="form-label">Category <span class="text-danger">*</span></label> 
                                                            <input type="text" placeholder="Select" value="Machinery" class="form-control ledgerselecct" id="equipment_category" readonly />
                                                        </div>
                                                    </div>
													
													<div class="col-md-3 basic-equipment-field">
                                                        <div class="mb-1">
                                                            <label class="form-label">Equipment <span class="text-danger">*</span></label> 
                                                            <input type="hidden" name="equipment_id" id="equipment_id" value="">
                                                            <input type="text" placeholder="Select Equipment" class="form-control ledgerselecct" id="equipment_name" readonly required>
                                                            <button type="button" class="btn btn-sm btn-outline-primary mt-1" data-bs-toggle="modal" data-bs-target="#equipmentModal">
                                                                <i data-feather="search"></i> Select Equipment
                                                            </button>
                                                        </div>
                                                    </div> 

                                                    <div class="col-md-3 basic-equipment-field">
                                                        <div class="mb-1">
                                                            <label class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                                                             <select class="form-select" name="maintenance_type" id="maintenance_type" required disabled>
                                                                <option value="">Select Type</option>
                                                                <option value="Preventive">Preventive</option>
                                                                <option value="Corrective" selected>Corrective</option>
                                                                <option value="Predictive">Predictive</option>
                                                                <option value="Breakdown">Breakdown</option>
                                                            </select> 
                                                        </div>
                                                    </div>

                                                    
                                                    <div class="col-md-3 equipment-detail-field">
                                                        <div class="mb-1"  id="defect_type_field">
                                                            <label class="form-label">Defect Type</label>
                                                            <select class="form-select" name="defect_type" id="defect_type_select">
                                                                <option value="">Select</option> 
                                                                <option value="General Defect">General Defect</option> 
                                                                <option value="Breakdown">Breakdown</option> 
                                                                <option value="Quality-based">Quality-based</option> 
                                                                <option value="Preventive">Preventive</option>
                                                                <option value="Corrective">Corrective</option>
                                                                <option value="Emergency">Emergency</option>
                                                            </select>  
                                                        </div>
                                                    </div>
													
													<div class="col-md-3 equipment-detail-field" >
                                                        <div class="mb-1" id="problem_field">
                                                            <label class="form-label">Problem <span class="text-danger">*</span></label>
                                                            <input type="text" value="Please resolve ASAP" disabled class="form-control" /> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3 equipment-detail-field" id="priority_field">
                                                        <div class="mb-1" id="priority_field">
                                                            <label class="form-label">Priority</label>
                                                            <select class="form-select" name="priority" required>
                                                                <option value="">Select Priority</option>
                                                                <option value="Low">Low</option>
                                                                <option value="Medium" selected>Medium</option>
                                                                <option value="High">High</option>
                                                                <option value="Critical">Critical</option>
                                                            </select>  
                                                        </div>
                                                    </div>
													
													<div class="col-md-3 equipment-detail-field">
                                                        <div class="mb-1" id="report_date_field">
                                                            <label class="form-label">Report Date & Time</label>
                                                            <input type="text" value="22-07-2025 | 02:30 PM" disabled class="form-control" /> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3 equipment-detail-field">
                                                        <div class="mb-1"  id="report_by_field">
                                                            <label class="form-label">Reported by</label>
                                                            <input type="text" value="Aniket" disabled class="form-control" />
                                                        </div>
                                                    </div>
													
													<div class="col-md-9 equipment-detail-field">
                                                        <div class="mb-1" id="detailed_observations_field">
                                                            <label class="form-label">Detailed observations</label>
                                                            <textarea name="detailed_observations" class="form-control" rows="3" placeholder="Enter detailed observations"></textarea>
                                                        </div>
                                                    </div>
													
													<div class="col-md-3 equipment-detail-field" id="supporting_documents_field">
                                                        <div class="mb-1" id="supporting_documents_field">
                                                            <label class="form-label">Supporting Documents <span class="text-danger">*</span></label><br/>
                                                            <div class="mt-50">
                                                                <input type="file" name="supporting_documents[]" class="form-control" multiple>
                                                            </div>
                                                        </div>
                                                    </div>
                                                 </div>
                                                 
 
                                                 
                        
                                            </div>
                                        </div>
                                    
                                    
                                    </div>    
                                
                            </div>
                            
							
                            <div class="card">
								 <div class="card-body customernewsection-form"> 
                                     
                                     
                                            <div class="border-bottom mb-2 pb-25">
                                                     <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="newheader "> 
                                                                <h4 class="card-title text-theme">Checklist and Defect Detail</h4>
                                                                <p class="card-text">Fill the details</p>
                                                            </div>
                                                        </div>
                                                    </div> 
                                             </div>
											 
											 
											  
									 		<div class="step-custhomapp bg-light">
												<ul class="nav nav-tabs my-25 custapploannav" role="tablist" id="main-tabs">
													<li class="nav-item" id="checklist-tab">
														<a class="nav-link" data-bs-toggle="tab" href="#payment">Checklist</a>
													</li>
													<li class="nav-item" id="spare-parts-tab">
														<a class="nav-link active" data-bs-toggle="tab" href="#attachment">Spare Parts</a>
													</li> 
												</ul>
											</div>
									 
									 		<div class="tab-content pb-1">
												<div class="tab-pane" id="payment">
                                                	<div class="row">  
														 <div class="col-md-12"> 
															 <div class="table-responsive pomrnheadtffotsticky1">
																 <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
																	<thead>
																		 <tr>
																			<th style="width: 30px">#</th>
																			<th width="250">Checklist</th>
																			<th>Maintenance</th>
																		  </tr>
																		</thead>
																		<tbody class="mrntableselectexcel1">
																			  <tr>
																				  <td>1</td>
																				  <td colspan="2" class="poprod-decpt p-50"><strong class="font-small-4">Greecing and Oiling</strong></td> 
																			  </tr>
																			  <tr>
																				 <td></td>
																				 <td class="ps-1">Checklist 1</td>
																			     <td class="poprod-decpt">
																					<input type="text" placeholder="Enter Text" class="form-control mw-100"  />
																				 </td>
																			  </tr>

																			  <tr>
																				 <td></td>
																				 <td class="ps-1">Checklist 2</td>
																			     <td class="poprod-decpt">
																					<div class="form-check form-check-primary custom-checkbox ms-50">
																						<input type="checkbox" class="mt-25 form-check-input" id="Email">
																						<label class="mb-50 mt-25 form-check-label" for="Email">Yes/No</label>
																					</div> 
																				 </td>
																			  </tr>
																			
																			  <tr>
																				 <td></td>
																				 <td class="ps-1">Checklist 3</td>
																			     <td class="poprod-decpt">
																					<input type="text" placeholder="Enter Text" class="form-control mw-100"  />
																				 </td>
																			  </tr>
																			  
																			  <tr>
																				 <td></td>
																				 <td class="ps-1">Checklist 4</td>
																			     <td class="poprod-decpt">
																					<div class="form-check form-check-primary custom-checkbox ms-50">
																						<input type="checkbox" class="mt-25 form-check-input" id="Email">
																						<label class="mb-50 mt-25 form-check-label" for="Email">Yes/No</label>
																					</div> 
																				 </td>
																			  </tr>
																			
																			 <tr>
																				  <td>2</td>
																				  <td colspan="2" class="poprod-decpt p-50"><strong class="font-small-4">Greecing and Oiling</strong></td> 
																			  </tr>
 

																			  <tr>
																				 <td></td>
																				 <td class="ps-1">Checklist 1</td>
																			     <td class="poprod-decpt">
																					<input type="text" placeholder="Enter Text" class="form-control mw-100"  />
																				 </td>
																			  </tr>

																			  <tr>
																				 <td></td>
																				 <td class="ps-1">Checklist 2</td>
																			     <td class="poprod-decpt">
																					<div class="form-check form-check-primary custom-checkbox ms-50">
																						<input type="checkbox" class="mt-25 form-check-input" id="Email">
																						<label class="mb-50 mt-25 form-check-label" for="Email">Yes/No</label>
																					</div> 
																				 </td>
																			  </tr>
																			 
																	 </tbody>
																</table>
															</div> 
														</div>  
													 </div>
												</div>
												<div class="tab-pane active" id="attachment">
												<div class="border-bottom mb-2 pb-25">
												<div class="row">
													<div class="col-md-6">
														<div class="newheader">
															<h4 class="card-title text-theme">Spare Parts Detail</h4>
															<p class="card-text">Fill the details</p>
														</div>
													</div>
													<div class="col-md-6 text-sm-end">
														<a href="#" class="btn btn-sm btn-outline-danger me-50" id="delete">
															<i data-feather="x-circle"></i> Delete</a>
														<a href="#" class="btn btn-sm btn-outline-primary" id="addNewRowBtn">
															<i data-feather="plus"></i> Add New Item</a>
													</div>
												</div>
											</div>
                                                	<div class="row">  
														 <div class="col-md-12"> 
														 <div class="table-responsive pomrnheadtffotsticky">
											<table id="itemTable"
												class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
												<thead>
													<tr>
														<th width="62" class="customernewsection-form">
															<div class="form-check form-check-primary custom-checkbox">
																<input type="checkbox" class="form-check-input"
																	id="checkAll">
																<label class="form-check-label" for="Email"></label>
															</div>
														</th>
														<th width="285">Item Code</th>
														<th width="208">Item Name</th>
														<th>Attributes</th>
														<th>UOM</th>
														<th>Qty</th>
													</tr>
												</thead>
												<tbody class="mrntableselectexcel">
													<tr class="trselected">
														<td class="customernewsection-form">
															<div class="form-check form-check-primary custom-checkbox">
																<input type="checkbox" class="form-check-input row-check"
																	id="Email">
																<label class="form-check-label" for="Email"></label>
															</div>
														</td>
														<td class="poprod-decpt">
															<input type="hidden" class="item_id">
															<input required type="text" placeholder="Select" name="item[]"
																class="item_code form-control mw-100 ledgerselecct mb-25" />
														</td>
														<td required class="poprod-decpt">
															<input type="text" placeholder="Select"
																class="item_name form-control mw-100 ledgerselecct mb-25" />
														</td>

														<td class="poprod-decpt">
															<input type="hidden" class="attribute">
															<button data-bs-toggle="modal" data-bs-target="#attribute"
																class="btn p-25 btn-sm btn-outline-secondary attributeBtn"
																style="font-size: 10px">Attributes</button>
														</td>
														<td>
															<select class="uom form-select mw-100" name="uom[]" required>

															</select>
														</td>
														<td><input type="number" class="qty form-control mw-100"
																name="qty[]" required /></td>
													</tr>
												</tbody>
												<tfoot>


													<tr valign="top">
														<td colspan="6" rowspan="10">
															<table class="table border">
																<tr>
																	<td class="p-0">
																		<h6
																			class="text-dark mb-0 bg-light-primary py-1 px-50">
																			<strong>Part Details</strong>
																		</h6>
																	</td>
																</tr>
																<tr>
																	<td class="poprod-decpt">
																		<span
																			class="poitemtxt mw-100"><strong>Name</strong>:<span
																				id="part_name"></span></span>
																	</td>
																</tr>
																<tr>
																	<td class="poprod-decpt" id="attributes_badges">
																		
																	</td>
																</tr>
																<tr>
																	<td class="poprod-decpt">
																		<span
																			class="badge rounded-pill badge-light-primary"><strong>Inv.
																				UOM</strong>: <span id="uom"></span></span>
																		<span
																			class="badge rounded-pill badge-light-primary"><strong>Qty.</strong>:
																			<span id="qty"></span></span>
																	</td>
																</tr>
																<tr>
																	{{-- <td class="poprod-decpt">
																		<span
																			class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>:
																			<span id="remarks"></span></span>
																	</td> --}}
																</tr>
															</table>
														</td>

													</tr>

												</tfoot>
											</table>
										</div>
 
														</div> 

													 </div>
												</div>
									 		</div>
									 
									 		<div class="row mt-2"> 
													<div class="col-md-12">
														 <div class="col-md-4">
															<div class="mb-1">
																<label class="form-label">Upload Document</label>
																<input type="file" name="upload_file" class="form-control">
															</div>
														</div> 
												 </div>



													<div class="col-md-12">
														<div class="mb-1">  
															<label class="form-label">Final Remarks</label> 
															<textarea type="text" rows="4" class="form-control" name="final_remark" placeholder="Enter Remarks here..."></textarea> 

														</div>
													</div>

												 </div>
  
											
											 
								</div>
                            </div>
                            
                            
                            
                             
                            
                            
                        </div>
                    </div>
                    <!-- Modal to add new record -->
                     
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
											@if(isset($defectNotifications) && $defectNotifications->count() > 0)
												@foreach($defectNotifications as $index => $defect)
													<tr class="trail-bal-tabl-none">
														<td class="customernewsection-form">
															<div class="form-check form-check-primary custom-radio">
																<input type="radio" class="form-check-input" name="defect_selection" id="defect_row_{{ $defect->id }}" 
																	data-defect-id="{{ $defect->id }}"
																	data-equipment="{{ $defect->equipment?->name ?? 'N/A' }}"
																	data-defect-type="{{ $defect->defectType?->name ?? 'N/A' }}"
																	data-priority="{{ $defect->priority ?? '' }}"
																	data-problem="{{ $defect->problem ?? '' }}"
																	data-reported-by="{{ $defect->creator?->name ?? 'N/A' }}">
																<label class="form-check-label" for="defect_row_{{ $defect->id }}"></label>
															</div> 
														</td>
														<td><strong>{{ $defect->document_date ? \Carbon\Carbon::parse($defect->document_date)->format('d-m-Y') : 'N/A' }}</strong></td> 
														<td>{{ $defect->book?->book_code ?? 'N/A' }}</td>
														<td>{{ $defect->document_number ?? 'N/A' }}</td>
														<td>{{ $defect->equipment?->name ?? 'N/A' }}</td>
														<td>{{ $defect->defectType?->name ?? 'N/A' }}</td>
														<td>{{ $defect->priority ?? '' }}</td>
														<td>{{ $defect->problem ?? '' }}</td>
														<td>{{ $defect->creator?->name ?? 'N/A' }}</td>
													</tr>
												@endforeach
											@else
												<tr class="trail-bal-tabl-none">
													<td colspan="9" class="text-center">No defect notifications found</td>
												</tr>
											@endif
											 
											  
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

		$(".mrntableselectexcel tr").click(function () {
			$(this).addClass('trselected').siblings().removeClass('trselected');
			value = $(this).find('td:first').html();
		});

		$(document).on('keydown', function (e) {
			if (e.which == 38) {
				$('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
			} else if (e.which == 40) {
				$('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
			}
			$('html, body').scrollTop($('.trselected').offset().top - 200);
			updateFooterFromSelected();
		});
		$(document).on('click', 'tbody tr', function () {
			$(this).addClass('trselected').siblings().removeClass('trselected');
			$('html, body').scrollTop($(this).offset().top - 200);
			updateFooterFromSelected();
		});
		function updateFooterFromSelected() {
			let $selected = $('.trselected');
			if ($selected.length) {
				console.log("qty " + $selected.find('.qty').val());
				$('#part_name').text($selected.find('.item_name').val());
				$('#uom').text($selected.find('.uom option:selected').text());
				$('#qty').text($selected.find('.qty').val());
				let $selectElement = $selected.find('.item_code');
				let $badgesContainer = $('#attributes_badges'); // container for badges

				if ($selectElement.val() !== "") {
					let attributesJSON = JSON.parse($selectElement.attr('data-attr') || '[]');
					let $hiddenInput = $selected.find('.attribute');
					let existingAttributes = $hiddenInput.length && $hiddenInput.val()
						? JSON.parse($hiddenInput.val())
						: [];

					if (!attributesJSON.length) {
						$badgesContainer.html('<span>No attributes available</span>');
						return;
					}

					let badgesHtml = '';

					$.each(attributesJSON, function (index, element) {
						// Find selected value from existingAttributes
						let selectedValObj = existingAttributes.find(attr => attr.item_attribute_id === element.id);
						let selectedVal = selectedValObj ? selectedValObj.value_id : '';

						// Find text for selected value
						let selectedText = '';
						if (selectedVal) {
							let valObj = element.values_data.find(v => v.id === selectedVal);
							selectedText = valObj ? valObj.value : '';
						}

						badgesHtml += `
					<span class="badge rounded-pill badge-light-primary" style="margin-right:5px;">
						<strong>${element.group_name}</strong>: <span>${selectedText}</span>
					</span>
				`;
					});

					$badgesContainer.html(badgesHtml);

				} else {
					$badgesContainer.html('');
				}

			}
		}
		
		// Initialize autocomplete for existing spare parts row when document is ready
		$(document).ready(function() {
			console.log('Document ready - initializing autocomplete for existing rows');
			console.log('Found .item_code elements:', $('.item_code').length);
			initAutoForItem('.item_code');
		});

		$('#addNewRowBtn').on('click', function () {
			rowCount++;
			let newRow = `<tr>
															<td class="customernewsection-form">
																<div class="form-check form-check-primary custom-checkbox">
																	<input type="checkbox" class="form-check-input row-check"
																		id="Email">
																	<label class="form-check-label" for="Email"></label>
																</div>
															</td>
															<td class="poprod-decpt">
																<input type="hidden" class="item_id">
																<input required type="text" placeholder="Select" name="item[]"
																	class="item_code form-control mw-100 ledgerselecct mb-25" />
															</td>
															<td required class="poprod-decpt">
																<input type="text" placeholder="Select"
																	class="item_name form-control mw-100 ledgerselecct mb-25" />
															</td>

															<td class="poprod-decpt">
																<input type="hidden" class="attribute">
																<button data-bs-toggle="modal" data-bs-target="#attribute"
																	class="btn p-25 btn-sm btn-outline-secondary attributeBtn"
																	style="font-size: 10px">Attributes</button>
															</td>
															<td>
																<select class="uom form-select mw-100" name="uom[]" required>

																</select>
															</td>
															<td><input type="number" class="qty form-control mw-100"  name="qty[]"
																	required /></td>
														</tr>																  `;
			$('.mrntableselectexcel').append(newRow);
			// Initialize autocomplete for all item_code elements (including new row)
			initAutoForItem('.item_code');

		});
		$('#delete').on('click', function () {
			let $rows = $('.mrntableselectexcel tr');
			let $checked = $rows.find('.row-check:checked');

			// Prevent deletion if only one row exists
			if ($rows.length <= 1) {
				showToast('error', 'At least one row is required.');
				return;
			}

			// Prevent deletion if checked rows would remove all
			if ($rows.length - $checked.length < 1) {
				showToast('error', 'You must keep at least one row.');
				return;
			}

			// Remove only the checked rows
			$checked.closest('tr').remove();

		});
		$('#checkAll').on('change', function () {
			let isChecked = $(this).is(':checked');
			$('.mrntableselectexcel .row-check').prop('checked', isChecked);
		});
		function resetParametersDependentElements(data) {
			let backDateAllowed = false;
			let futureDateAllowed = false;

			if (data != null) {
				console.log(data.parameters.back_date_allowed);
				if (Array.isArray(data?.parameters?.back_date_allowed)) {
					for (let i = 0; i < data.parameters.back_date_allowed.length; i++) {
						if (data.parameters.back_date_allowed[i].trim().toLowerCase() === "yes") {
							backDateAllowed = true;
							break; // Exit the loop once we find "yes"
						}
					}
				}
				if (Array.isArray(data?.parameters?.future_date_allowed)) {
					for (let i = 0; i < data.parameters.future_date_allowed.length; i++) {
						if (data.parameters.future_date_allowed[i].trim().toLowerCase() === "yes") {
							futureDateAllowed = true;
							break; // Exit the loop once we find "yes"
						}
					}
				}
				//console.log(backDateAllowed, futureDateAllowed);

			}

			const dateInput = document.getElementById("document_date");

			// Determine the max and min values for the date input
			const today = moment().format("YYYY-MM-DD");

			if (backDateAllowed && futureDateAllowed) {
				dateInput.removeAttribute("min");
				dateInput.removeAttribute("max");
			} else if (backDateAllowed) {
				dateInput.setAttribute("max", today);
				dateInput.removeAttribute("min");
			} else if (futureDateAllowed) {
				dateInput.setAttribute("min", today);
				dateInput.removeAttribute("max");
			} else {
				dateInput.setAttribute("min", today);
				dateInput.setAttribute("max", today);

			}
		}

		$('#book_id').on('change', function () {
			resetParametersDependentElements(null);
			let currentDate = new Date().toISOString().split('T')[0];
			let document_date = $('#document_date').val();
			let bookId = $('#book_id').val();
			let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId +
				"&document_date=" + document_date;
			fetch(actionUrl).then(response => {
				return response.json().then(data => {
					if (data.status == 200) {
						resetParametersDependentElements(data.data);
						$("#book_code_input").val(data.data.book_code);
						if (!data.data.doc.document_number) {
							$("#document_number").val('');
							$('#doc_number_type').val('');
							$('#doc_reset_pattern').val('');
							$('#doc_prefix').val('');
							$('#doc_suffix').val('');
							$('#doc_no').val('');
						} else {
							$("#document_number").val(data.data.doc.document_number);
							$('#doc_number_type').val(data.data.doc.type);
							$('#doc_reset_pattern').val(data.data.doc.reset_pattern);
							$('#doc_prefix').val(data.data.doc.prefix);
							$('#doc_suffix').val(data.data.doc.suffix);
							$('#doc_no').val(data.data.doc.doc_no);
						}
						if (data.data.doc.type == 'Manually') {
							$("#document_number").attr('readonly', false);
						} else {
							$("#document_number").attr('readonly', true);
						}

					}
					if (data.status == 404) {
						$("#document_number").val('');
						$('#doc_number_type').val('');
						$('#doc_reset_pattern').val('');
						$('#doc_prefix').val('');
						$('#doc_suffix').val('');
						$('#doc_no').val('');
						showToast('error', data.message);
					}
				});
			});
		});

		$('#book_id').trigger('change');
		initAutoForItem('.item_code');
		function updateJsonData() {
			// Collect Spare Parts Data
			const allRows = [];

			$('.mrntableselectexcel tr').each(function () {
				const row = $(this);
				const itemId = row.find('.item_id').val();

				if (itemId) { // skip empty rows
					const rowData = {
						item_id: itemId,
						item_code: row.find('.item_code').val() || '',
						item_name: row.find('.item_name').val() || '',
						attribute: row.find('.attribute').val() || '',
						qty: row.find('.qty').val() || 0,
						uom_id: row.find('.uom').val() || '',
						uom_name: row.find('.uom option:selected').text() || '',
					};
					allRows.push(rowData);
				}
			});

			$('#spare_parts').val(JSON.stringify(allRows));

			// Collect Checklist Data
			const checklistData = [];
			let checklistIndex = 0;

			$('.mrntableselectexcel1 tr').each(function () {
				const row = $(this);
				const checklistName = row.find('td:nth-child(2)').text().trim();
				
				// Skip header rows and empty rows
				if (checklistName && !checklistName.includes('Checklist') && checklistName !== '#' && checklistName !== 'Checklist') {
					return;
				}
				
				if (checklistName && checklistName.includes('Checklist')) {
					const textInput = row.find('input[type="text"]');
					const checkboxInput = row.find('input[type="checkbox"]');
					
					let value = '';
					let type = '';
					
					if (textInput.length > 0) {
						value = textInput.val() || '';
						type = 'text';
					} else if (checkboxInput.length > 0) {
						value = checkboxInput.is(':checked');
						type = 'checkbox';
					}
					
					if (type) {
						checklistData.push({
							index: ++checklistIndex,
							name: checklistName,
							type: type,
							value: value
						});
					}
				}
			});

			$('#checklist_data').val(JSON.stringify(checklistData));

			// Collect Equipment Details Data
			const equipmentDetails = {
				equipment_reference_type: $('#reference_type').val() || '',
				equipment_category: $('#equipment_category_hidden').val() || $('#equipment_category').val() || '',
				equipment_name: $('#equipment_name_hidden').val() || $('#equipment_name').val() || '',
				equipment_id: $('#equipment_id').val() || '',
				equipment_maintenance_type: $('#maintenance_type').val() || '',
				equipment_defect_type: $('#defect_type_hidden').val() || $('#defect_type_select').val() || '',
				equipment_problem: $('#problem_hidden').val() || $('#problem_field input').val() || '',
				equipment_priority: $('#priority_field select').val() || '',
				equipment_report_date: $('#report_date_time_hidden').val() || $('#report_date_field input').val() || '',
				equipment_reported_by: $('#reported_by_hidden').val() || $('#report_by_field input').val() || '',
				equipment_detailed_observations: $('#detailed_observations_field textarea').val() || '',
				equipment_supporting_documents: $('#supporting_documents_field input')[0]?.files[0]?.name || ''
			};

			$('#equipment_details').val(JSON.stringify(equipmentDetails));
			
			console.log('Form data collected:', {
				spare_parts: allRows.length + ' items',
				checklist: checklistData.length + ' items', 
				equipment_details: equipmentDetails
			});
		}


		document.getElementById('save-draft-btn').addEventListener('click', function () {
			// No validation required for draft - save as is
			$('.preloader').show();
			document.getElementById('document_status').value = 'draft';
			updateJsonData();
			document.getElementById('maint-wo-form').submit();

		});


		$('#maint-wo-form').on('submit', function (e) {
			e.preventDefault(); // Always prevent default first
			
			// Validate reference type selection
			let referenceType = $('#reference_type').val();
			if (!referenceType) {
				showToast('error', 'Please select a reference type (Equipment or Defect Notification)');
				$('#reference_type_error').show();
				return false;
			}
			
			$('.preloader').show();
			document.getElementById('document_status').value = 'submitted';
			updateJsonData();
			this.submit();

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
		$(document).on('input change', '.qty, .uom, .item_name, .item_code, .attribute', function () {
			updateFooterFromSelected();
		});

		$(document).on('click', '.submitAttributeBtn', (e) => {
			$("#attribute").modal('hide');
		});
		
		function initAutoForItem(selector, type) {
			$(selector).autocomplete({
				minLength: 0,
				source: function (request, response) {
					let term = request.term.toLowerCase();

					// Gather all already selected item IDs from other rows
					let selectedItemIds = [];
					$('.item_id').each(function () {
						let val = $(this).val();
						if (val) selectedItemIds.push(val);
					});

					// Filter itemsData by search term AND exclude already selected items
					let filtered = itemsData.filter(item => {
						let isSelectedElsewhere = selectedItemIds.includes(item.id.toString());

						// Allow the current input's item (so it doesn't exclude itself)
						// Get current input's item_id value:
						let currentItemId = $(selector).closest('tr').find('.item_id').val();

						// Include item if:
						// - it matches the search term
						// - and (not selected elsewhere OR is the current selected item in this row)
						return (item.item_code.toLowerCase().includes(term) || item.item_name.toLowerCase().includes(term)) &&
							(!isSelectedElsewhere || item.id.toString() === currentItemId);
					});

					let results = filtered.map(item => ({
						id: item.id,
						label: `${item.item_code} - ${item.item_name}`,
						code: item.item_code,
						item_id: item.id,
						item_name: item.item_name,
						uom_name: item.uom_name,
						uom_id: item.uom_id,
						attr: item.item_attributes,
					}));

					response(results);
				},
				select: function (event, ui) {
					let $input = $(this);
					let itemCode = ui.item.code;
					let attr = ui.item.attr;
					let itemName = ui.item.item_name;
					let itemId = ui.item.item_id;
					let uomId = ui.item.uom_id;
					let uomName = ui.item.uom_name;

					$input.attr('data-name', itemName);
					$input.attr('data-code', itemCode);
					$input.attr('data-attr', JSON.stringify(attr));
					$input.attr('data-id', itemId);
					$input.closest('tr').find('.item_id').val(itemId);
					$input.closest('tr').find('.item_name').val(itemName);
					$input.val(itemCode);

					let uomOption = `<option value="${uomId}">${uomName}</option>`;
					$input.closest('tr').find('.uom').empty().append(uomOption);

					setTimeout(() => {
						if (ui.item.is_attr) {
							$input.closest('tr').find('.attributeBtn').trigger('click');
						} else {
							$input.closest('tr').find('.attributeBtn').trigger('click');
							$input.closest('tr').find('.qty').val('').focus();
						}
					}, 100);

					return false;
				},
				change: function (event, ui) {
					if (!ui.item) {
						$(this).val("");
						$(this).attr('data-name', '');
						$(this).attr('data-code', '');
						$(this).attr('data-attr', '');
						$(this).closest('tr').find('.item_id').val('');
						$(this).closest('tr').find('.item_name').val('');
						$(this).closest('tr').find('.uom').empty();
					}
				}
			}).focus(function () {
				if (!this.value.trim()) {
					$(this).autocomplete("search", "");
				}
			}).on("input", function () {
				if ($(this).val().trim() === "") {
					$(this).removeData("selected");
					$(this).closest('tr').find(".item_name").val('');
					$(this).closest('tr').find(".attribute").val('');
					$(this).closest('tr').find(".item_id").val('');
					$(this).closest('tr').find(".item_code").val('');
				}
			});

			$(selector).autocomplete("instance")._renderItem = function (ul, item) {
				return $("<li>")
					.append(`<div><strong>${item.code}</strong> - ${item.item_name}</div>`)
					.appendTo(ul);
			};
		}

		function changeAttributeVal($row) {
			let hiddenInput = $row.find('.attribute');


			if (!hiddenInput) return;

			// Find the attributes table and tbody
			const attributesTable = document.getElementById("attributes_table_modal");
			const tbody = attributesTable.querySelector("tbody");

			let selectedAttributes = [];

			Array.from(tbody.rows).forEach(row => {
				const hiddenInputAttr = row.querySelector('input[type="hidden"][name="id"]');
				const selectElement = row.querySelector("select");

				if (hiddenInputAttr && selectElement) {
					const attributeId = parseInt(hiddenInputAttr.value, 10);
					const selectedVal = parseInt(selectElement.value, 10);

					if (!isNaN(attributeId) && !isNaN(selectedVal) && selectedVal > 0) {
						selectedAttributes.push({
							item_attribute_id: attributeId,
							value_id: selectedVal
						});
					}
				}
			});

			// Update hidden input with JSON
			hiddenInput.val(JSON.stringify(selectedAttributes));
			console.log(selectedAttributes);
		}

		$(document).on('click', '.attributeBtn', function (e) {
			let $tr = $(this).closest('tr');
			let $selectElement = $tr.find('.item_code');
			let $attributesTable = $('#attribute_table'); // modal table
			$attributesTable.data('currentRow', $tr);

			if ($selectElement.val() !== "") {
				let attributesJSON = JSON.parse($selectElement.attr('data-attr') || '[]');
				let $hiddenInput = $tr.find('.attribute');
				let existingAttributes = $hiddenInput.length && $hiddenInput.val()
					? JSON.parse($hiddenInput.val())
					: [];

				if (!attributesJSON.length) {
					$attributesTable.html(`
							<tr>
								<td colspan="2" class="text-center">No attributes available</td>
							</tr>
						`);
					return;
				}

				let innerHtml = ``;

				$.each(attributesJSON, function (index, element) {
					let optionsHtml = ``;

					$.each(element.values_data, function (i, value) {
						let isSelected = existingAttributes.some(attr =>
							attr.item_attribute_id === element.id && attr.value_id === value.id
						);

						optionsHtml += `
								<option value='${value.id}' ${isSelected ? 'selected' : ''}>${value.value}</option>
							`;
					});

					innerHtml += `
							<tr>
								<td>
									${element.group_name}
									<input type="hidden" name="id" value="${element.id}">
								</td>
								<td>
									<select class="form-select select2" style="max-width:100% !important;">
										<option value="">Select</option>
										${optionsHtml}
									</select>
								</td>
							</tr>
						`;
				});

				$attributesTable.html(innerHtml);

				// Initialize select2

				//Bind change event
				$attributesTable.find('select').off('change').on('change', function () {
					changeAttributeVal($tr);
				});
				$attributesTable.find('select').select2();


			} else {
				$attributesTable.html(`
						<tr>
							<td colspan="2" class="text-center">No attributes available</td>
						</tr>
					`);
			}
		});
		function closeModal(id) {
			$('#' + id).modal('hide');
		}

		// Simple functions for equipment selection
		function selectEquipmentReference() {
			console.log('Equipment button clicked');
			$('#reference_type').val('equipment');
			$('#reference_type_error').hide();
			$('#equipment_ref_btn').removeClass('btn-outline-primary').addClass('btn-primary');
			$('#defect_ref_btn').removeClass('btn-primary').addClass('btn-outline-primary');
			
			// Show basic equipment fields immediately
			$('.basic-equipment-field').show();
			$('.equipment-detail-field').hide();
			console.log('Equipment fields shown');
			
			// Show checklist tab when equipment is selected
			$('#checklist-tab').show();
			console.log('Checklist tab shown for equipment selection');
		}
		
		function selectDefectNotificationReference() {
			console.log('Defect notification button clicked');
			$('#reference_type').val('defect_notification');
			$('#reference_type_error').hide();
			$('#defect_ref_btn').removeClass('btn-outline-primary').addClass('btn-primary');
			$('#equipment_ref_btn').removeClass('btn-primary').addClass('btn-outline-primary');
			
			// Show all equipment detail fields
			$('.basic-equipment-field').show();
			$('.equipment-detail-field').show();
			console.log('All fields shown for defect notification');
			
			// Hide checklist tab and show only spare parts tab
			$('#checklist-tab').hide();
			$('#spare-parts-tab a').tab('show'); // Activate spare parts tab
			console.log('Checklist tab hidden, spare parts tab activated');
		}

		function processEquipmentSelection() {
			console.log('Process equipment selection called');
			var selectedEquipment = $('input[name="equipmentRadio"]:checked');
			
			if (selectedEquipment.length === 0) {
				// Show toaster notification
				showToast('error', 'Please select at least one equipment');
				return false; // Don't close modal
			}
			
			// Get selected equipment data
			var equipmentRow = selectedEquipment.closest('tr');
			var equipmentName = equipmentRow.find('td').eq(0).find('strong').text().trim();
			if (!equipmentName) {
				equipmentName = equipmentRow.find('td').eq(0).text().trim();
			}
			
			// Populate equipment fields
			$('#equipment_name').val(equipmentName);
			$('#equipment_id').val(selectedEquipment.data('equipment-id'));
			
			// Show equipment detail fields including defect type
			$('.equipment-detail-field').show();
			
			// Enable defect type field for user selection
			$('#defect_type_select').prop('disabled', false);
			
			console.log('Equipment selected:', equipmentName);
			console.log('Equipment detail fields shown');
			
			// Close modal manually
			$('#reference').modal('hide');
			
			return true;
		}

		function processDefectSelection() {
			console.log('Process defect selection called');
			var selectedDefect = $('input[name="defect_selection"]:checked');
			
			if (selectedDefect.length === 0) {
				// Show toaster notification
				showToast('error', 'Please select at least one defect notification');
				return false; // Don't close modal
			}
			
			// Get selected defect data from table row
			var defectRow = selectedDefect.closest('tr');
			var date = defectRow.find('td').eq(1).text().trim();
			var equipment = defectRow.find('td').eq(4).text().trim();
			var defectType = defectRow.find('td').eq(5).text().trim();
			var priority = defectRow.find('td').eq(6).text().trim();
			var problem = defectRow.find('td').eq(7).text().trim();
			var reportedBy = defectRow.find('td').eq(8).text().trim();
			
			console.log('Defect data:', {date, equipment, defectType, priority, problem, reportedBy});
			
			// Populate equipment detail fields with defect notification data
			$('#equipment_name').val(equipment);
			
			// Handle defect type - add option if it doesn't exist
			var defectTypeSelect = $('#defect_type_select');
			if (defectType && defectTypeSelect.find('option[value="' + defectType + '"]').length === 0) {
				// Add the defect type as a new option if it doesn't exist
				defectTypeSelect.append('<option value="' + defectType + '">' + defectType + '</option>');
				console.log('Added new defect type option:', defectType);
			}
			defectTypeSelect.val(defectType).prop('disabled', true);
			
			$('#problem_field input').val(problem).prop('disabled', true);
			$('#priority_field select').val(priority).prop('disabled', true);
			$('#report_date_field input').val(date).prop('disabled', true);
			$('#report_by_field input').val(reportedBy).prop('disabled', true);
			
			// Also populate hidden inputs for form submission
			$('#equipment_name_hidden').val(equipment);
			$('#defect_type_hidden').val(defectType);
			$('#problem_hidden').val(problem);
			$('#report_date_time_hidden').val(date);
			$('#reported_by_hidden').val(reportedBy);
			
			console.log('Defect notification fields populated');
			
			// Close modal manually
			$('#defectlog').modal('hide');
			
			return true;
		}

		function showEquipmentFields() {
			console.log('showEquipmentFields() called');
			
			// Hide all equipment detail fields first
			$('.basic-equipment-field').hide();
			$('.equipment-detail-field').hide();
			console.log('All fields hidden');
			
			// Show only basic equipment fields (Category, Equipment, Maintenance Type)
			$('.basic-equipment-field').show();
			console.log('Basic equipment fields shown, count:', $('.basic-equipment-field:visible').length);
			
			// Enable the fields for user interaction
			$('#equipment_category').prop('readonly', true); // Keep category readonly with default value
			$('#equipment_name').prop('readonly', true); // Keep equipment readonly until selected
			$('#maintenance_type').prop('disabled', false); // Enable maintenance type selection
			
			// Clear any previous values from hidden inputs for defect-related fields
			$('#defect_type_hidden').val('');
			$('#problem_hidden').val('');
			$('#report_date_time_hidden').val('');
			$('#reported_by_hidden').val('');
			
			console.log('Equipment fields setup complete');
		}

		function showDefectNotificationFields() {
			// Show all equipment detail fields
			$('.equipment-detail-field').show();
			
			// Set all fields as readonly with default values
			$('#defect_type_select').prop('disabled', true).val('General Defect');
			$('#defect_type_hidden').val('General Defect');
			
			$('#problem_field input').prop('disabled', true).val('Please resolve ASAP');
			$('#problem_hidden').val('Please resolve ASAP');
			
			$('#priority_field select').prop('disabled', true).val('High');
			
			$('#report_date_field input').prop('disabled', true).val('22-07-2025 | 02:30 PM');
			$('#report_date_time_hidden').val('22-07-2025 | 02:30 PM');
			
			$('#report_by_field input').prop('disabled', true).val('Aniket');
			$('#reported_by_hidden').val('Aniket');
			
			$('#detailed_observations_field textarea').prop('readonly', true).val('Defect notification requires immediate attention');
			
			$('#supporting_documents_field input').prop('disabled', false); // Keep file upload enabled
		}

	

	</script>
@endsection