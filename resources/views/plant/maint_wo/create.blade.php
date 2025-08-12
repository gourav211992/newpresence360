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
                                                            <select class="form-select">
                                                                <option>Select</option> 
                                                            </select>
                                                        </div>
                                                     </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Doc No <span class="text-danger">*</span></label>  
                                                        </div>  

                                                        <div class="col-md-5"> 
                                                            <input type="text" class="form-control">
                                                        </div> 
                                                     </div>  

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Doc Date <span class="text-danger">*</span></label>  
                                                        </div>  

                                                        <div class="col-md-5"> 
                                                            <input type="date" class="form-control">
                                                        </div> 
                                                     </div>
												
													 <div class="row align-items-center mb-1">
                                                            <div class="col-md-3"> 
                                                                <label class="form-label">Location <span class="text-danger">*</span></label>  
                                                            </div>  

                                                            <div class="col-md-5">  
                                                                <select class="form-select">
                                                                    <option>Select</option> 
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
                                                            <input type="text" placeholder="Select" value="Machinery" class="form-control ledgerselecct" />
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Equipment <span class="text-danger">*</span></label> 
                                                            <input type="text" placeholder="Select" value="Handloom Machine" class="form-control ledgerselecct" />
                                                        </div>
                                                    </div> 

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                                                             <select class="form-select">
                                                                <option>Select</option> 
                                                                <option selected>Running</option> 
                                                            </select> 
                                                        </div>
                                                    </div>

                                                    
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Defect Type</label>
                                                            <select class="form-select" disabled>
                                                                <option>Select</option> 
                                                                <option>General Defect</option> 
                                                                <option selected>Breakdown</option> 
                                                                <option>Quality-based</option> 
                                                            </select>  
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Problem <span class="text-danger">*</span></label>
                                                            <input type="text" value="Please resolve ASAP" disabled class="form-control" /> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Priority</label>
                                                            <select class="form-select" disabled>
                                                                <option>Select</option> 
                                                                <option>High</option> 
                                                                <option selected>Medium</option> 
                                                                <option>Low</option> 
                                                            </select>  
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Report Date & Time</label>
                                                            <input type="text" value="22-07-2025 | 02:30 PM" disabled class="form-control" /> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Reported by</label>
                                                            <input type="text" value="Aniket" disabled class="form-control" />
                                                        </div>
                                                    </div>
													
													<div class="col-md-9">
                                                        <div class="mb-1">
                                                            <label class="form-label">Detailed observations</label>
                                                            <input type="text" disabled class="form-control" value="Oil leak observed near shaft seal other descrioption will come here" />
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Supporting Documents <span class="text-danger">*</span></label><br/>
                                                            <div class="mt-50">
																<a href="#" class="me-25" ><i data-feather='file-text' class="font-large-1"></i></a>
                                                            	<a href="#" class="me-25" ><i data-feather='file-text' class="font-large-1"></i></a>
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
													<div class="text-end mb-50">
														<button class="btn btn-outline-danger btn-sm mb-50 mb-sm-0"><i data-feather="x-circle"></i> Delete</button>
														<button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"><i data-feather="plus-square"></i> Add New</button>
													</div>
                                                	<div class="row">  
														 <div class="col-md-12"> 
															 <div class="table-responsive pomrnheadtffotsticky">
																 <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
																	<thead>
																		 <tr>
																			<th width="62" class="customernewsection-form">
																				<div class="form-check form-check-primary custom-checkbox">
																					<input type="checkbox" class="form-check-input" id="Email">
																					<label class="form-check-label" for="Email"></label>
																				</div> 
																			</th>
																			<th width="150">Item Code</th>
																			<th >Item Name</th>
																			<th>Attribute</th>
																			<th>UOM</th>
																			<th>Qty</th>
																		  </tr>
																		</thead>
																		<tbody class="mrntableselectexcel">
																			 <tr>
																				 <td class="customernewsection-form">
																					<div class="form-check form-check-primary custom-checkbox">
																						<input type="checkbox" class="form-check-input" id="Email">
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
																						<input type="checkbox" class="form-check-input" id="Email">
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
																						<input type="checkbox" class="form-check-input" id="Email">
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
																						<input type="checkbox" class="form-check-input" id="Email">
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