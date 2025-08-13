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
										<li class="breadcrumb-item"><a href="index.html">Home</a>
										</li>  
										<li class="breadcrumb-item active">Add New</li> 
									</ol>
								</div>
							</div>
						</div>
					</div>
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right">   
							<button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                            <button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"><i data-feather='save'></i> Save as Draft</button> 
                            <button data-bs-toggle="modal" data-bs-target="#amendmentconfirm" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                            <!--
                                                        <button class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-target="#reject" data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</button> 
                                                        <button class="btn btn-success btn-sm mb-50 mb-sm-0" data-bs-target="#approved" data-bs-toggle="modal"><i data-feather="check-circle" ></i> Approve</button>  
                            -->
							<button onClick="javascript: history.go(-1)" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Submit</button> 
							
							<button class="btn btn-success btn-sm mb-50 mb-sm-0" data-bs-target="#approved" data-bs-toggle="modal"><i data-feather="check-circle" ></i> Close</button>
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
                    <input type="hidden" name="document_status" id="document_status" value="">

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
                                                                @foreach($series as $book)
                                                                    <option value="{{ $book->id }}">{{ $book->book_code }}</option>
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
                                                        <input type="date" value="{{ date('Y-m-d') }}" class="form-control" id="document_date" name="scheduled_date" min="{{ date('Y-m-d') }}" required>
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
                                                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                         </div>
														
														 
												
													<div class="row align-items-center mb-1"> 
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Reference From</label>  
                                                        </div> 

                                                        <div class="col-md-5 action-button"> 
                                                            <button data-bs-toggle="modal" data-bs-target="#reference" class="btn btn-outline-primary btn-sm mb-0"><i data-feather="plus-square"></i> Equipment</button>
                                                            <button data-bs-toggle="modal" data-bs-target="#defectlog" class="btn btn-outline-primary btn-sm mb-0"><i data-feather="plus-square"></i> Defect Notification</button>
                                                        </div>
                                                    </div>
                                            </div> 
                                            
                                            <div class="col-md-4"> 

                                                    <div class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                                        <h5 class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                                            <strong><i data-feather="arrow-right-circle"></i> Approval History</strong>
                                                            <strong class="badge rounded-pill badge-light-secondary amendmentselect">Rev. No. 
                                                                <select class="form-select">
                                                                    <option>00</option>
                                                                    <option>01</option>
                                                                    <option>02</option>
                                                                    <option>03</option>
                                                                </select>
                                                            </strong>
                                                            
                                                        </h5>
                                                        <ul class="timeline ms-50 newdashtimline ">
                                                            <li class="timeline-item">
                                                                <span class="timeline-point timeline-point-indicator"></span>
                                                                <div class="timeline-event">
                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                        <h6>Deepak Kumar</h6> 
                                                                        <span class="badge rounded-pill badge-light-primary">Amendment</span>
                                                                    </div>
                                                                    <h5>(2 min ago)</h5>
                                                                    <p>Description will come here</p> 
                                                                </div>
                                                            </li>
                                                            <li class="timeline-item">
                                                                <span class="timeline-point timeline-point-indicator"></span>
                                                                <div class="timeline-event">
                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                        <h6>Aniket Singh</h6> 
                                                                        <span class="badge rounded-pill badge-light-danger">Rejected</span>
                                                                    </div>
                                                                    <h5>(2 min ago)</h5>
                                                                    <p>Description will come here</p> 
                                                                </div>
                                                            </li>
                                                            <li class="timeline-item">
                                                                <span class="timeline-point timeline-point-warning timeline-point-indicator"></span>
                                                                <div class="timeline-event">
                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                        <h6>Deewan Singh</h6>
                                                                        <span class="badge rounded-pill badge-light-warning">Pending</span>
                                                                    </div>
                                                                    <h5>(5 min ago)</h5>
                                                                    <p>Description will come here</p> 
                                                                </div>
                                                            </li>
                                                            <li class="timeline-item">
                                                                <span class="timeline-point timeline-point-info timeline-point-indicator"></span>
                                                                <div class="timeline-event">
                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                        <h6>Brijesh Kumar</h6>
                                                                        <span class="badge rounded-pill badge-light-success">Approved</span>
                                                                    </div>
                                                                    <h5>(10 min ago)</h5>
                                                                    <p>Description will come here</p> 
                                                                </div>
                                                            </li> 
                                                            <li class="timeline-item">
                                                                <span class="timeline-point timeline-point-danger timeline-point-indicator"></span>
                                                                <div class="timeline-event">
                                                                    <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                        <h6>Deepender Singh</h6>
                                                                       <span class="badge rounded-pill badge-light-success">Approved</span>
                                                                    </div>
                                                                    <h5>(5 day ago)</h5>
                                                                    <p><a href="#"><i data-feather="download"></i></a> Description will come here </p> 
                                                                </div>
                                                            </li>
                                                        </ul>
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

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Category <span class="text-danger">*</span></label> 
                                                            <input type="text" placeholder="Select" value="Machinery" class="form-control ledgerselecct" id="equipment_category" readonly />
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Equipment <span class="text-danger">*</span></label> 
                                                            <input type="hidden" name="equipment_id" id="equipment_id" value="">
                                                            <input type="text" placeholder="Select Equipment" class="form-control ledgerselecct" id="equipment_name" readonly required>
                                                            <button type="button" class="btn btn-sm btn-outline-primary mt-1" data-bs-toggle="modal" data-bs-target="#equipmentModal">
                                                                <i data-feather="search"></i> Select Equipment
                                                            </button>
                                                        </div>
                                                    </div> 

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                                                             <select class="form-select" name="maintenance_type" id="maintenance_type" required>
                                                                <option value="">Select Type</option>
                                                                <option value="Preventive">Preventive</option>
                                                                <option value="Corrective">Corrective</option>
                                                                <option value="Predictive">Predictive</option>
                                                                <option value="Breakdown">Breakdown</option>
                                                            </select> 
                                                        </div>
                                                    </div>

                                                    
                                                    <div class="col-md-3 equipment-detail-field">
                                                        <div class="mb-1"  id="defect_type_field" style="display:none!important;">
                                                            <label class="form-label">Defect Type</label>
                                                            <select class="form-select" disabled>
                                                                <option>Select</option> 
                                                                <option>General Defect</option> 
                                                                <option selected>Breakdown</option> 
                                                                <option>Quality-based</option> 
                                                            </select>  
                                                        </div>
                                                    </div>
													
													<div class="col-md-3 equipment-detail-field" >
                                                        <div class="mb-1" id="problem_field" style="display:none !important;">
                                                            <label class="form-label">Problem <span class="text-danger">*</span></label>
                                                            <input type="text" value="Please resolve ASAP" disabled class="form-control" /> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3 equipment-detail-field" id="priority_field">
                                                        <div class="mb-1" id="priority_field" style="display:none !important;">
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
                                                        <div class="mb-1" id="report_date_field" style="display:none !important;">
                                                            <label class="form-label">Report Date & Time</label>
                                                            <input type="text" value="22-07-2025 | 02:30 PM" disabled class="form-control" /> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3 equipment-detail-field">
                                                        <div class="mb-1"  id="report_by_field" style="display:none !important;">
                                                            <label class="form-label">Reported by</label>
                                                            <input type="text" value="Aniket" disabled class="form-control" />
                                                        </div>
                                                    </div>
													
													<div class="col-md-9 equipment-detail-field">
                                                        <div class="mb-1" id="detailed_observations_field" style="display:none !important;">
                                                            <label class="form-label">Detailed observations</label>
                                                            <textarea name="detailed_observations" class="form-control" rows="3" placeholder="Enter detailed observations"></textarea>
                                                        </div>
                                                    </div>
													
													<div class="col-md-3 equipment-detail-field" id="supporting_documents_field">
                                                        <div class="mb-1" id="supporting_documents_field" style="display:none !important;">
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
												<ul class="nav nav-tabs my-25 custapploannav" role="tablist">
													<li class="nav-item">
														<a class="nav-link active" data-bs-toggle="tab" href="#payment">Checklist</a>
													</li>
													<li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#attachment">Spare Parts</a>
													</li> 
												</ul>
											</div>
									 
									 		<div class="tab-content pb-1">
												<div class="tab-pane active" id="payment">
                                                	<div class="row">  
														 <div class="col-md-12"> 
															 <div class="table-responsive pomrnheadtffotsticky">
																 <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
																	<thead>
																		 <tr>
																			<th style="width: 30px">#</th>
																			<th width="250">Checklist</th>
																			<th>Maintenance</th>
																		  </tr>
																		</thead>
																		<tbody class="mrntableselectexcel">
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
												<div class="tab-pane" id="attachment">
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
																 <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
																	<thead>
																		 <tr>
																			<th width="62" class="customernewsection-form">
																				<div class="form-check form-check-primary custom-checkbox">
																					<input type="checkbox" class="form-check-input" id="checkAll">
																					<label class="form-check-label" for="checkAll"></label>
																				</div> 
																			</th>
																			<th width="150">Item Code</th>
																			<th >Item Name</th>
																			<th>Attribute</th>
																			<th>UOM</th>
																			<th>Qty</th>
																		  </tr>
																		</thead>
																		<tbody id="sparePartsTbody" class="mrntableselectexcel">
																			 <tr>
																				 <td class="customernewsection-form">
																					<div class="form-check form-check-primary custom-checkbox">
																						<input type="checkbox" class="form-check-input row-check">
																						<label class="form-check-label" for="Email"></label>
																					</div> 
																				 </td>
																				 <td class="poprod-decpt"><input type="text" placeholder="Select" value="SPA001" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																				 <td class="poprod-decpt"><input type="text" placeholder="Select" value="Spare Part Name" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																				 <td class="poprod-decpt"> 
																					<button data-bs-toggle="modal" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
																				 </td>
																				 <td><select class="form-select mw-100">
																				   <option>Select</option>
																				   <option selected>KG</option>
																				 </select></td>
																				 <td class="poprod-decpt"><input type="text" value="5" class="form-control mw-100 mb-25"  /></td>
																		      </tr>


																			 <tr>
																				 <td class="customernewsection-form">
																					<div class="form-check form-check-primary custom-checkbox">
																						<input type="checkbox" class="form-check-input row-check">
																						<label class="form-check-label" for="Email"></label>
																					</div> 
																				 </td>
																				 <td class="poprod-decpt"><input type="text" placeholder="Select" value="SPA001" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																				 <td class="poprod-decpt"><input type="text" placeholder="Select" value="Spare Part Name" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																				 <td class="poprod-decpt"> 
																					<button data-bs-toggle="modal" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
																				 </td>
																				 <td><select class="form-select mw-100">
																				   <option>Select</option>
																				   <option selected>KG</option>
																				 </select></td>
																				 <td class="poprod-decpt"><input type="text" value="5" class="form-control mw-100 mb-25"  /></td>
																		      </tr>

																			<tr>
																				 <td class="customernewsection-form">
																					<div class="form-check form-check-primary custom-checkbox">
																						<input type="checkbox" class="form-check-input row-check">
																						<label class="form-check-label" for="Email"></label>
																					</div> 
																				 </td>
																				 <td class="poprod-decpt"><input type="text" placeholder="Select" value="SPA001" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																				 <td class="poprod-decpt"><input type="text" placeholder="Select" value="Spare Part Name" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																				 <td class="poprod-decpt"> 
																					<button data-bs-toggle="modal" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
																				 </td>
																				 <td><select class="form-select mw-100">
																				   <option>Select</option>
																				   <option selected>KG</option>
																				 </select></td>
																				 <td class="poprod-decpt"><input type="text" value="5" class="form-control mw-100 mb-25"  /></td>
																		      </tr>

																			<tr>
																				 <td class="customernewsection-form">
																					<div class="form-check form-check-primary custom-checkbox">
																						<input type="checkbox" class="form-check-input row-check">
																						<label class="form-check-label" for="Email"></label>
																					</div> 
																				 </td>
																				 <td class="poprod-decpt"><input type="text" placeholder="Select" value="SPA001" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																				 <td class="poprod-decpt"><input type="text" placeholder="Select" value="Spare Part Name" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																				 <td class="poprod-decpt"> 
																					<button data-bs-toggle="modal" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
																				 </td>
																				 <td><select class="form-select mw-100">
																				   <option>Select</option>
																				   <option selected>KG</option>
																				 </select></td>
																				 <td class="poprod-decpt"><input type="text" value="5" class="form-control mw-100 mb-25"  /></td>
																		      </tr>

																	 </tbody>




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
																<input type="file" class="form-control">
															</div>
														</div> 
												 </div>



													<div class="col-md-12">
														<div class="mb-1">  
															<label class="form-label">Final Remarks</label> 
															<textarea type="text" rows="4" class="form-control" placeholder="Enter Remarks here..."></textarea> 

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
					<button id="equipment_process_btn" class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i> Process</button>
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
					<button id="defect_process_btn" class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i> Process</button>
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

    </form>
@endsection

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

<script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
		 
        
        $(function() {
            $( ".ledgerselecct" ).autocomplete({
                source: [ 
              "Furniture (IT001)",
            "Chair (IT002)",
            "Table (IT003)",
            "Laptop (IT004)",
            "Bags (IT005)",
            ],
                minLength: 0
            }).focus(function(){
                if (this.value == ""){
                    $(this).autocomplete("search");
                }
            });
        });
        
        $(".mrntableselectexcel tr").click(function() {
          $(this).addClass('trselected').siblings().removeClass('trselected');
          value = $(this).find('td:first').html();
        });
        
        $(document).on('keydown', function(e) {
          if (e.which == 38) {
            $('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
          } else if (e.which == 40) {
            $('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
          }
          $('html, body').scrollTop($('.trselected').offset().top - 200); 
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
    </script>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script>
$(function () {
    // SweetAlert toast helper
    function showToast(type, msg) {
        Swal.fire({
            toast: true,
            position: "top-end",
            icon: type,
            title: msg,
            showConfirmButton: false,
            timer: 3000
        });
    }

    // Tab toggle helper using Bootstrap's proper tab API
    function toggleTabs(showChecklist) {
        const chkTab = $('a[href="#payment"]'),
              spTab  = $('a[href="#attachment"]');

        if (showChecklist) {
            // Show both tabs and activate checklist
            chkTab.show();
            spTab.show();
            // Use Bootstrap's tab API to properly activate the checklist tab
            chkTab.tab('show');
        } else {
            // Hide checklist tab and show only spare parts
            chkTab.hide();
            spTab.show();
            // Use Bootstrap's tab API to properly activate the spare parts tab
            spTab.tab('show');
        }
    }

    // Equipment button → show both tabs
    $('button[data-bs-target="#reference"]').on("click", function () {
        toggleTabs(true);
    });

    // Defect Notification button → show only spare parts
    $('button[data-bs-target="#defectlog"]').on("click", function () {
        toggleTabs(false);
    });

    // Equipment Process
    $('#equipment_process_btn').on("click", function (e) {
        const selected = $('input[name="equipmentRadio"]:checked');
        if (!selected.length) {
            e.preventDefault();
            showToast('error', 'Please select an equipment record.');
            return;
        }
        toggleTabs(true);
    });

    // Defect Process
    $('#defect_process_btn').on("click", function (e) {
        const selected = $('input[name="defect_selection"]:checked');
        if (!selected.length) {
            e.preventDefault();
            showToast('error', 'Please select a defect record.');
            return;
        }
        const row = selected.closest('tr');
        $('#defect_type_field select')
            .val(row.find('td:eq(5)').text().trim())
            .prop('disabled', true);
        $('#priority_field select')
            .val(row.find('td:eq(6)').text().trim())
            .prop('disabled', true);
        $('#problem_field input')
            .val(row.find('td:eq(7)').text().trim())
            .prop('readonly', true);
        $('#report_date_field input')
            .val(row.find('td:eq(1)').text().trim())
            .prop('readonly', true);
        $('#report_by_field input')
            .val(row.find('td:eq(8)').text().trim())
            .prop('readonly', true);

        $('#defect_type_field, #priority_field, #problem_field, #report_date_field, #report_by_field, #detailed_observations_field, #supporting_documents_field')
            .show();

        toggleTabs(false);
    });

    // Spare Parts - Select All
    $('#checkAll').on("change", function () {
        $('.row-check').prop('checked', $(this).is(':checked'));
    });

    // Spare Parts - Add Row
    $('#addNewRowBtn').on("click", function () {
        let row = `<tr>
            <td><div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input row-check">
                <label class="form-check-label"></label>
            </div></td>
            <td><input type="text" class="form-control mw-100 mb-25" placeholder="Select"></td>
            <td><input type="text" class="form-control mw-100 mb-25" placeholder="Select"></td>
            <td><button class="btn p-25 btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#attribute" style="font-size:10px">Attributes</button></td>
            <td><select class="form-select mw-100"><option>Select</option><option>KG</option></select></td>
            <td><input type="text" class="form-control mw-100 mb-25"></td>
        </tr>`;
        $('#sparePartsTbody').append(row);
    });

    // Spare Parts - Delete Selected
    $('#delete').on("click", function () {
        const checked = $('.row-check:checked');
        if (!checked.length) {
            showToast('error', 'No rows selected for deletion.');
            return;
        }
        if ($('#sparePartsTbody tr').length === checked.length) {
            showToast('error', 'At least one row is required.');
            return;
        }
        checked.closest('tr').remove();
    });
});
</script>
 
@endsection