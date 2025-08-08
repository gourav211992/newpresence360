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
								<h2 class="content-header-title float-start mb-0">Maintenance BOM</h2>
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
            	<button onClick="javascript: history.go(-1)" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Submit</button> 
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
											 
                                              <div class="border-bottom mb-2 pb-25">
                                                     <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="newheader "> 
                                                                <h4 class="card-title text-theme">Basic Information</h4>
                                                                <p class="card-text">Fill the details</p> 
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 text-sm-end">
                                                            <span class="badge rounded-pill badge-light-secondary forminnerstatus">
                                                                Status : <span class="text-success">Approved</span>
                                                            </span>
                                                        </div>
                                                    </div> 
                                             </div>
											
											  
  
											
											<div class="row">
                                                
                                                
                                                 <div class="col-md-8"> 
                                                     
                                                     
                                                    
                                                    <div class="">
                                                        
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
                                                                <input type="date" placeholder="Select" class="form-control" />
                                                            </div> 
                                                         </div> 
														 

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3"> 
                                                                <label class="form-label">BOM Name <span class="text-danger">*</span></label>  
                                                            </div>  

                                                            <div class="col-md-5">  
                                                                <input type="text" placeholder="Select" class="form-control" />
                                                            </div>
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
							
							
							<div class="card">
								<div class="card-body customernewsection-form"> 
                                                  
										  <div class="border-bottom mb-2 pb-25">
												 <div class="row">
													<div class="col-md-6">
														<div class="newheader "> 
															<h4 class="card-title text-theme">Spare Parts Detail</h4>
													<p class="card-text">Fill the details</p>
														</div>
													</div>
													<div class="col-md-6 text-sm-end">
														<a href="#" class="btn btn-sm btn-outline-danger me-50">
															<i data-feather="x-circle"></i> Delete</a>
														<a href="#" class="btn btn-sm btn-outline-primary">
															<i data-feather="plus"></i> Add New Item</a>
												   </div>
												</div> 
										 </div>


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
																		<input type="checkbox" class="form-check-input" id="Email">
																		<label class="form-check-label" for="Email"></label>
																	</div> 
																</td>
																 <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																 <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																<td class="poprod-decpt"> 
																	<button data-bs-toggle="modal" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
																 </td>
																<td><select class="form-select mw-100">
																  <option>Select</option>
																  <option selected>KG</option>
																  </select></td>
																<td><input type="text" value="10" class="form-control mw-100" /></td>
															  </tr>


															 <tr>
																 <td class="customernewsection-form">
																	<div class="form-check form-check-primary custom-checkbox">
																		<input type="checkbox" class="form-check-input" id="Email">
																		<label class="form-check-label" for="Email"></label>
																	</div> 
																</td>
																 <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																 <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																<td class="poprod-decpt"> 
																	<button data-bs-toggle="modal" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
																 </td>
																<td><select class="form-select mw-100">
																  <option>Select</option>
																  <option selected>KG</option>
																  </select></td>
																<td><input type="text" value="10" class="form-control mw-100" /></td>
															  </tr>

															<tr>
																 <td class="customernewsection-form">
																	<div class="form-check form-check-primary custom-checkbox">
																		<input type="checkbox" class="form-check-input" id="Email">
																		<label class="form-check-label" for="Email"></label>
																	</div> 
																</td>
																 <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																 <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																<td class="poprod-decpt"> 
																	<button data-bs-toggle="modal" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
																 </td>
																<td><select class="form-select mw-100">
																  <option>Select</option>
																  <option selected>KG</option>
																  </select></td>
																<td><input type="text" value="10" class="form-control mw-100" /></td>
															  </tr>

															<tr>
																 <td class="customernewsection-form">
																	<div class="form-check form-check-primary custom-checkbox">
																		<input type="checkbox" class="form-check-input" id="Email">
																		<label class="form-check-label" for="Email"></label>
																	</div> 
																</td>
																 <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																 <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																<td class="poprod-decpt"> 
																	<button data-bs-toggle="modal" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
																 </td>
																<td><select class="form-select mw-100">
																  <option>Select</option>
																  <option selected>KG</option>
																  </select></td>
																<td><input type="text" value="10" class="form-control mw-100" /></td>
															  </tr>

															<tr>
																 <td class="customernewsection-form">
																	<div class="form-check form-check-primary custom-checkbox">
																		<input type="checkbox" class="form-check-input" id="Email">
																		<label class="form-check-label" for="Email"></label>
																	</div> 
																</td>
																 <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																 <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  /></td>
																<td class="poprod-decpt"> 
																	<button data-bs-toggle="modal" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
																 </td>
																<td><select class="form-select mw-100">
																  <option>Select</option>
																  <option selected>KG</option>
																  </select></td>
																<td><input type="text" value="10" class="form-control mw-100" /></td>
															  </tr>


													 </tbody>
												   <tfoot>

													 
													 <tr valign="top">
														<td colspan="6" rowspan="10">
															<table class="table border">
																<tr>
																	<td class="p-0">
																		<h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Part Details</strong></h6>
																	</td>
																</tr>
																<tr>
																	<td class="poprod-decpt">
																		<span class="poitemtxt mw-100"><strong>Name</strong>: Furniture for Reception in ground flour...</span>
																	 </td> 
																</tr>
																<tr> 
																	<td class="poprod-decpt">
																		<span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>: 8755</span>
																		<span class="badge rounded-pill badge-light-primary"><strong>Color</strong>: Black</span>
																		<span class="badge rounded-pill badge-light-primary"><strong>Size</strong>: 5.11 Inch</span>
																	</td> 
																</tr> 
																<tr>
																	<td class="poprod-decpt">
																		<span class="badge rounded-pill badge-light-primary"><strong>Inv. UOM</strong>: KG</span>
																		<span class="badge rounded-pill badge-light-primary"><strong>Qty.</strong>: 100</span> 
																	</td>
																</tr> 
																<tr>
																	<td class="poprod-decpt">
																		<span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>: Description will come here for items...</span>
																	 </td>
																</tr>
															</table>  
														</td>
														 
													 </tr> 

												</tfoot>
											</table>
									 </div>


										<div class="row mt-2"> 
												<div class="col-md-4">
													<div class="mb-1">
														<label class="form-label">Upload Document</label>
														<input type="file" class="form-control">
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



       <div class="modal fade text-start" id="overhead" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Enter Overhead</h4>
                        <p class="mb-0">Enter the below list</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					 <div class="row">
                          

						 <div class="col-md-12">
 

							 <div class="table-responsive-md"> 
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border"> 
                                    <thead>
                                         <tr>
                                            <th>#</th> 
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th width="400px">Leadger</th> 
                                          </tr>
                                        </thead>
                                        <tbody>
                                             <tr>
                                                <td>1</td> 
                                                <td><input type="text"class="form-control mw-100"></td>
                                                <td><input type="text"class="form-control mw-100"></td>
                                                <td>
                                                   <select class="form-select select2">
                                                        <option>Select</option>
                                                    </select> 
                                                </td> 
                                              </tr>

                                            <tr>
                                                <td>2</td> 
                                                <td><input type="text"class="form-control mw-100"></td>
                                                <td><input type="text"class="form-control mw-100"></td>
                                                <td>
                                                   <select class="form-select select2">
                                                        <option>Select</option>
                                                    </select> 
                                                </td> 
                                              </tr>
                                            
                                            <tr>
                                                <td>2</td> 
                                                <td><input type="text"class="form-control mw-100"></td>
                                                <td><input type="text"class="form-control mw-100"></td>
                                                <td>
                                                   <select class="form-select select2">
                                                        <option>Select</option>
                                                    </select> 
                                                </td> 
                                              </tr>


                                       </tbody>


                                </table>
                            </div>
						</div>


					 </div>
				</div>
				<div class="modal-footer text-end">
					<button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
					<button class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i> Submit</button>
				</div>
			</div>
		</div>
	</div>
    
    <div class="modal fade" id="wastage" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Wastage Details</h1>
					<p class="text-center">Enter the details below.</p>

					<div class="row">
                        <div class="col-md-12 mb-1">
							<label class="form-label">Wastage Type <span class="text-danger">*</span></label>
							<select class="form-control">
                                <option>Select</option>
                                <option selected>Fixed</option>
                                <option>%age</option>
                            </select>
						</div>
                        
                        <div class="col-md-12 mb-1">
							<label class="form-label">Wastage Value <span class="text-danger">*</span></label>
							<input type="text" class="form-control" placeholder="Enter Value">
						</div>
                    </div>
				</div>
				
				<div class="modal-footer justify-content-center">  
						<button type="reset" class="btn btn-outline-secondary me-1">Cancel</button> 
					<button type="reset" class="btn btn-primary">Select</button>
				</div>
			</div>
		</div>
	</div>

	
	 <div class="modal fade" id="attribute" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Select Attribute</h1>
					<p class="text-center">Enter the details below.</p>

					<div class="table-responsive-md customernewsection-form">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"> 
									<thead>
										 <tr>  
											<th>Attribute Name</th>
											<th>Attribute Value</th>
										  </tr>
										</thead>
										<tbody>
											 <tr> 
												<td>Color</td>
												<td>
                                                    <select class="form-select select2">
                                                        <option>Select</option>
                                                        <option>Black</option>
                                                        <option>White</option>
                                                        <option>Red</option>
                                                        <option>Golden</option>
                                                        <option>Silver</option>
                                                    </select> 
                                                </td>
											</tr>
											
											<tr>   
												<td>Size</td>
												<td>
                                                    <select class="form-select select2">
                                                        <option>Select</option>
                                                        <option>5.11"</option>
                                                        <option>5.10"</option>
                                                        <option>5.09"</option>
                                                        <option>5.00"</option>
                                                        <option>6.20"</option>
                                                    </select> 
                                                </td>
											</tr>
											
											
											 
											 

									   </tbody>


								</table>
							</div>
				</div>
				
				<div class="modal-footer justify-content-center">  
						<button type="reset" class="btn btn-outline-secondary me-1">Cancel</button> 
					<button type="reset" class="btn btn-primary">Select</button>
				</div>
			</div>
		</div>
	</div>
    
    
     <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>BOM</strong>? After Amendment this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
                </div> 
            </div>
        </div>
    </div>
@endsection




@section('scripts')
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
      "Indian Oil Corporation Ltd.",
    "Airports Authority of India",
    "Bharat Heavy Electricals Ltd.",
    "Bharat Petroleum Corpn. Ltd.",
    "NTPC Ltd.",
    "Gail (India) Ltd.",
    "Hindustan Petroleum Corpn. Ltd.",
    "Steel Authority of India Ltd.",
    "Indian Railway Stations Devpt. Corporation Ltd.",
    "Oil & Natural Gas Corporation Ltd.",
    "Oil & Natural Gas Corporation Ltd.",
    "Hindustan Aeronautics Ltd.",
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
        
        
  </script>
  @endsection
