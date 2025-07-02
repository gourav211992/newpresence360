@extends('layouts.app')
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
								<h2 class="content-header-title float-start mb-0">Lorry Receipt</h2>
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
                            <a href="pdf/po.html" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0"><i data-feather='printer'></i> Print</a>
                            <button data-bs-toggle="modal" data-bs-target="#amendmentconfirm" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
<!--
                            <button class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-target="#reject" data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</button> 
							<button class="btn btn-success btn-sm mb-50 mb-sm-0" data-bs-target="#approved" data-bs-toggle="modal"><i data-feather="check-circle" ></i> Approve</button>  
-->
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
                                                            <select class="form-control">
																<option>Mother Diary</option>
															</select>
                                                        </div> 
                                                     </div>  

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Cost Center <span class="text-danger">*</span></label>  
                                                        </div>  

                                                        <div class="col-md-5"> 
                                                            <select class="form-control">
																<option>Mother Diary</option>
															</select>
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
                                                    <h4 class="card-title">General Information</h4> 
                                                </div>
                                            </div>
                                            <div class="card-body"> 
                                                <div class="row">
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Source <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" placeholder="Select Source" /> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Destination <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" placeholder="Select Destination" />  
                                                        </div>
                                                    </div>
 
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Consignor <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" placeholder="Select Consignor" />
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Consignee <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" placeholder="Select Consignee" /> 
                                                        </div>
                                                    </div>
													 
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Vehicle <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" placeholder="Select Vehicle" /> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Distance (Km) <span class="text-danger">*</span></label>
                                                            <input type="text" disabled class="form-control" placeholder="Enter Distance (Km)" />
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Freight Charges (Rs) <span class="text-danger">*</span></label>
                                                            <input type="number" disabled class="form-control" placeholder="Enter Freight Charges (Rs)" />
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Driver <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" placeholder="Select Driver" />  
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Driver Cash (Rs)</label>
                                                            <input type="number" class="form-control" placeholder="Enter Driver Cash (Rs)" /> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Fuel Price (Rs)</label>
                                                            <input type="number" class="form-control" placeholder="Enter Fuel Price (Rs)" /> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Invoice No.</label>
                                                            <input type="text" class="form-control" placeholder="Enter Invoice No." />
                                                        </div>
                                                    </div>  

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Invoice Value</label>
                                                            <input type="text" class="form-control" placeholder="Enter Invoice Value" /> 
                                                        </div>
                                                    </div>
													 
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">No of Article/Bundles <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" placeholder="Enter No of Article/Bundles" /> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Weight (kg) <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" placeholder="Enter Weight (kg)" />
                                                        </div>
                                                    </div>
													 
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">E-Waybill No. <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" placeholder="Enter E-Waybill No." />
                                                        </div>
                                                    </div> 
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">GST Paid By <span class="text-danger">*</span></label>
                                                             <select class="form-select">
                                                                <option>Select</option>
                                                                <option>Consignor</option> 
                                                                <option>Consignee</option> 
                                                                <option>Transporter</option> 
                                                            </select> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">LR Type <span class="text-danger">*</span></label>
                                                             <select class="form-select">
                                                                <option>Select</option> 
                                                                <option>Inward</option> 
                                                                <option>Outward</option> 
                                                            </select> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Billed or Pay <span class="text-danger">*</span></label>
                                                             <select class="form-select">
                                                                <option>Select</option> 
                                                                <option>To be Billed</option> 
                                                                <option>To Pay</option> 
                                                            </select> 
                                                        </div>
                                                    </div> 
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Load Type</label>
                                                             <select class="form-select">
                                                                <option>Select</option> 
                                                                <option>FTL</option> 
                                                                <option>Bulk</option> 
                                                                <option>CEP</option> 
                                                                <option>FCL</option> 
                                                                <option>LCP</option> 
                                                                <option>LTL</option>
                                                            </select> 
                                                        </div>
                                                    </div>
													
													<div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">LR Charges</label>
                                                             <select class="form-select">
                                                                <option>Select</option>
                                                                <option>5</option> 
                                                                <option>10</option> 
                                                            </select> 
                                                        </div>
                                                    </div> 
													
													<div class="col-md-3 mb-1">
														
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
                                                                <h4 class="card-title text-theme">Multi Point Detail</h4>
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
											 
											 
											  
  
											
											<div class="row"> 
                                                
                                                 <div class="col-md-12">
                                                     
                                                     
                                                     <div class="table-responsive pomrnheadtffotsticky">
                                                         <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
                                                            <thead>
                                                                 <tr>
                                                                    <th width="53" class="customernewsection-form">
                                                                        <div class="form-check form-check-primary custom-checkbox">
                                                                            <input type="checkbox" class="form-check-input" id="Email">
                                                                            <label class="form-check-label" for="Email"></label>
                                                                        </div> 
                                                                    </th>
                                                                    <th width="271">Location</th>
                                                                    <th width="185">Type</th>
                                                                    <th width="197">No of Articles</th>
                                                                    <th width="197">Weight</th>
                                                                    <th width="246" class=" text-end">Freight(Rs)</th>
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
                                                                         <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  /></td>
                                                                        <td>
                                                                            <select class="form-select mw-100">
                                                                                <option>Select</option>
                                                                                <option>Pick Up</option>
                                                                                <option selected>Drop Off</option>
                                                                            </select> 
                                                                        </td>
                                                                       <td><input type="text" value="5" class="form-control mw-100" /></td>
                                                                       <td><input type="text" value="20" class="form-control mw-100" /></td>
                                                                       <td><input type="text" value="100.00" class="form-control mw-100 text-end" /></td>
                                                                      </tr>
                                                                      
                                                                    
                                                                     <tr>
                                                                         <td class="customernewsection-form">
                                                                            <div class="form-check form-check-primary custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input" id="Email">
                                                                                <label class="form-check-label" for="Email"></label>
                                                                            </div> 
                                                                        </td>
                                                                         <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  /></td>
                                                                        <td>
                                                                            <select class="form-select mw-100">
                                                                                <option>Select</option>
                                                                                <option selected>Pick Up</option>
                                                                                <option>Drop Off</option>
                                                                            </select> 
                                                                        </td>
                                                                       <td><input type="text" value="5" class="form-control mw-100" /></td>
                                                                       <td><input type="text" value="20" class="form-control mw-100" /></td>
                                                                       <td><input type="text" value="100.00" class="form-control mw-100 text-end" /></td>
                                                                      </tr>
                                                                    
                                                                    <tr>
                                                                         <td class="customernewsection-form">
                                                                            <div class="form-check form-check-primary custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input" id="Email">
                                                                                <label class="form-check-label" for="Email"></label>
                                                                            </div> 
                                                                        </td>
                                                                         <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  /></td>
                                                                        <td>
                                                                            <select class="form-select mw-100">
                                                                                <option>Select</option>
                                                                                <option>Pick Up</option>
                                                                                <option selected>Drop Off</option>
                                                                            </select> 
                                                                        </td>
                                                                       <td><input type="text" value="5" class="form-control mw-100" /></td>
                                                                       <td><input type="text" value="20" class="form-control mw-100" /></td>
                                                                       <td><input type="text" value="100.00" class="form-control mw-100 text-end" /></td>
                                                                      </tr>
                                                                    
                                                                    <tr>
                                                                         <td class="customernewsection-form">
                                                                            <div class="form-check form-check-primary custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input" id="Email">
                                                                                <label class="form-check-label" for="Email"></label>
                                                                            </div> 
                                                                        </td>
                                                                         <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  /></td>
                                                                        <td>
                                                                            <select class="form-select mw-100">
                                                                                <option>Select</option>
                                                                                <option selected>Pick Up</option>
                                                                                <option>Drop Off</option>
                                                                            </select> 
                                                                        </td>
                                                                       <td><input type="text" value="5" class="form-control mw-100" /></td>
                                                                       <td><input type="text" value="20" class="form-control mw-100" /></td>
                                                                       <td><input type="text" value="100.00" class="form-control mw-100 text-end" /></td>
                                                                      </tr> 
                                                                    
                                                                    
                                                             </tbody>
                                                             
                                                             <tfoot>
                                                                 
                                                                 <tr class="totalsubheadpodetail"> 
                                                                    <td colspan="5"></td>
                                                                    <td class="text-end">20,000.00</td> 
                                                                </tr>
                                                                 
																 
																 <tr valign="top">
                                                                    <td colspan="4" rowspan="10">
                                                                        <table class="table border">
                                                                            <tr>
                                                                                <td class="p-0">
                                                                                    <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Route Details</strong></h6>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="poprod-decpt">
                                                                                    <span class="poitemtxt mw-100"><strong>Source</strong>: Plot No 14, Sector 135, Noida, Uttar Pradesh, India</span>
                                                                                 </td> 
                                                                            </tr>
																			<tr>
                                                                                <td class="poprod-decpt">
                                                                                    <span class="poitemtxt mw-100"><strong>Destination</strong>: Plot No 14, Sector 135, Noida, Uttar Pradesh, India</span>
                                                                                 </td> 
                                                                            </tr>
                                                                            <tr> 
                                                                                <td class="poprod-decpt">
                                                                                    <span class="badge rounded-pill badge-light-primary"><strong>Weight</strong>: 80</span>
                                                                                    <span class="badge rounded-pill badge-light-primary"><strong>No of Article</strong>: 20</span>
                                                                                    <span class="badge rounded-pill badge-light-primary"><strong>Points</strong>: 4</span>
                                                                                </td> 
                                                                            </tr> 
                                                                            <tr>
                                                                                <td class="poprod-decpt">
                                                                                    <span class="badge rounded-pill badge-light-primary"><strong>Vehicle</strong>: Truck</span>
                                                                                    <span class="badge rounded-pill badge-light-primary"><strong>Capacity</strong>: 1000.00 KG</span> 
                                                                                </td>
                                                                            </tr>
                                                                             
                                                                                  
                                                                        </table> 
                                                                    </td>
                                                                    <td colspan="3">
                                                                        <table class="table border mrnsummarynewsty">
                                                                            <tr>
                                                                                <td colspan="2" class="p-0">
                                                                                    <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between"><strong>LR Summary</strong>
                                                                                    </h6>
                                                                                </td>
                                                                            </tr>
                                                                            <tr class="totalsubheadpodetail"> 
                                                                                <td width="55%"><strong>Sub Total</strong></td>  
                                                                                <td class="text-end">1,200.00</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><strong>LR Charges</strong></td>
                                                                                <td class="text-end">10.00</td>
                                                                            </tr> 
                                                                            <tr> 
                                                                                <td><strong>Freight Charges	</strong></td>  
                                                                                <td class="text-end">12,280.00</td>
                                                                            </tr>
                                                                              
                                                                            <tr class="voucher-tab-foot">
                                                                                <td class="text-primary"><strong>Total Freight Charges</strong></td>  
                                                                                <td>
                                                                                    <div class="quottotal-bg justify-content-end"> 
                                                                                        <h5>13,490.00</h5>
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                 </tr> 
 

                                                            </tfoot>


                                                        </table>
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
                            
                            
                            
                             
                            
                            
                        </div>
                    </div>
                    <!-- Modal to add new record -->
                     
                </section>
                 

            </div>
        </div>
    </div>
    <!-- END: Content-->
@section('content')

@endsection