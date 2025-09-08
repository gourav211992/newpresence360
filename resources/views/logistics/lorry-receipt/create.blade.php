@extends('layouts.app')

@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('logistics.lorry-receipt.store') }}"   data-redirect="{{ route('logistics.lorry-receipt.index') }}">
    @csrf

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
									<ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a>
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
                           <!-- Save as Draft Button -->
                            <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" onclick="setStatusAndSubmit('draft')">
                                <i data-feather='save'></i> Save as Draft
                            </button>
<!--
                            <button class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-target="#reject" data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</button> 
							<button class="btn btn-success btn-sm mb-50 mb-sm-0" data-bs-target="#approved" data-bs-toggle="modal"><i data-feather="check-circle" ></i> Approve</button>  
-->
							<!-- Submit Button -->
                            <button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0" onclick="setStatusAndSubmit('submitted')">
                                <i data-feather="check-circle"></i> Submit
                            </button>
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
                                                            <input type="hidden" name="status" id="statusInput" value="draft"> 
                                                           <select class="form-select disable_on_edit" onchange="getDocNumberByBookId(this);" name="book_id" id="series_id_input">
                                                                @foreach ($series as $currentSeries)
                                                                    <option value="{{ $currentSeries->id }}" 
                                                                        {{ isset($order) && $order->book_id == $currentSeries->id ? 'selected' : '' }}>
                                                                        {{ $currentSeries->book_code }}
                                                                    </option> 
                                                                @endforeach
                                                            </select>

                                                        </div>
                                                        <input type = "hidden" name = "book_code" id = "book_code_input" value = ""></input>
                                                     </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Doc No <span class="text-danger">*</span></label>  
                                                        </div>  

                                                        <div class="col-md-5"> 
                                                            <input type="text" class="form-control" id="document_number" name="document_number">
                                                        </div> 
                                                     </div>  

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Doc Date <span class="text-danger">*</span></label>  
                                                        </div>  

                                                        <div class="col-md-5"> 
                                                            <input type="date" class="form-control" id="document_date" name="document_date" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                                                        </div>
 
                                                     </div>
												
													<div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Location <span class="text-danger">*</span></label>  
                                                        </div>  

                                                        <div class="col-md-5"> 
                                                           <select class="form-select" name="location" id="locationId">
                                                                @foreach($locations as $location)
                                                                    <option value="{{ $location->id }}">{{ $location->store_name }}</option>
                                                                @endforeach
                                                            </select>

                                                        </div> 
                                                     </div>  

                                                     <div class="row align-items-center mb-1" id="cost_center_wrapper">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Cost Center <span class="text-danger">*</span></label>  
                                                        </div>  

                                                        <div class="col-md-5"> 
                                                          <select name="cost_center_id" id="cost_center_id" class="form-select">
                                                            <option value="">Select Cost Center</option>
                                                        </select>
                                                            <span id="cost_center_error" style="color: red; display: none;">Please select a cost center.</span>

                                                        </div> 
                                                     </div>
												 
                                            </div> 
                                            
                                            <!-- <div class="col-md-4"> 

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

                                                </div> -->

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
                                                            <label class="form-label" for="source">Source <span class="text-danger">*</span></label>
                                                            <input type="text" name="source_name" class="form-control mw-100 route-master-autocomplete"
                                                                        placeholder="Start typing  locations..."
                                                                        data-type="source" />
                                                          <input type="hidden" name="source_id" class="route-master-id" id="sourceIdInput" data-type="source" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="destination">Destination <span class="text-danger">*</span></label>
                                                            <input type="text" name="destination_name" class="form-control mw-100 route-master-autocomplete"
                                                                        placeholder="Start typing  locations."
                                                                        data-type="destination" />
                                                        <input type="hidden" name="destination_id" class="route-master-id" data-type="destination" id="destinationIdInput"/>
                                                        </div>
                                                    </div>

                                                    <!-- Consignor -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="consignor">Consignor <span class="text-danger">*</span></label>
                                                            <input type="text"
                                                                name="customer_name"
                                                                class="form-control mw-100 customer-autocomplete"
                                                                data-type="consignor"
                                                                placeholder="Start typing customer..." />

                                                            <input type="hidden" name="customer_id" class="customer-id" data-type="consignor" id="customer_id" data-id=""/>
                                                        </div>
                                                    </div>

                                                    <!-- Consignee -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="consignee">Consignee <span class="text-danger">*</span></label>
                                                            <input type="text"
                                                                name="consignee_name"
                                                                class="form-control mw-100 customer-autocomplete"
                                                                data-type="consignee"
                                                                placeholder="Start typing consignee..." />

                                                            <input type="hidden" name="consignee_id" class="customer-id" data-type="consignee" id="consignee_id"/>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="vehicle">Vehicle No<span class="text-danger">*</span></label>
                                                             <input type="text"
                                                                        name="vehicle_number"
                                                                        class="form-control mw-100 vehicle-number-autocomplete"
                                                                        placeholder="Select Vehicle"  id="vehicle_number"/>
                                                                    <input type="hidden"
                                                                        name="vehicle_number_id" class="vehicle-number-id" id="vehicle_number_id"/>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="distance">Distance (Km) <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="distance" name="distances"  placeholder="Enter Distance (Km)" />
                                                             <input type="hidden" class="form-control" id="distanceInput" name="distance"  placeholder="Enter Distance (Km)" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="freight_charges">Freight Charges (Rs) <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" id="freight_charges" name="freight_charge"  placeholder="Enter Freight Charges (Rs)" />
                                                             <input type="hidden" class="form-control" id="freightCharges" name="freight_charges"  placeholder="Enter Freight Charges (Rs)" />
                                                        </div>
                                                    </div>

                                                   <!-- Blade: HTML -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="driver">Driver <span class="text-danger">*</span></label>
                                                            <input type="text" name="driver_name" class="form-control mw-100 driver-autocomplete" placeholder="Select Driver" data-type="driver" />
                                                            <input type="hidden" name="driver_id" class="driver-id" data-type="driver"/>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="driver_cash">Driver Cash (Rs)</label>
                                                            <input type="number" class="form-control" id="driver_cash" name="driver_cash" placeholder="Enter Driver Cash (Rs)" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="fuel_price">Fuel Price (Rs)</label>
                                                            <input type="number" class="form-control" id="fuel_price" name="fuel_price" placeholder="Enter Fuel Price (Rs)" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="invoice_no">Invoice No.</label>
                                                            <input type="text" class="form-control" id="invoice_no" name="invoice_no" placeholder="Enter Invoice No." />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="invoice_value">Invoice Value</label>
                                                            <input type="text" class="form-control" id="invoice_value" name="invoice_value" placeholder="Enter Invoice Value" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="no_of_bundles">No of Article/Bundles <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" id="no_of_bundles" name="no_of_bundles" placeholder="Enter No of Article/Bundles" />
                                                            <input type="hidden" class="form-control" id="per_bundles"  placeholder="Enter No of Article/Bundles" />
                                                            <input type="hidden" class="form-control" id="no_bundles"  placeholder="Enter No of Article/Bundles" />
                                                            <input type="hidden" class="form-control" id="no_bundles_amount"  placeholder="Enter No of Article/Bundles" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="weight">Weight (kg) <span class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" id="weight" name="weight" placeholder="Enter Weight (kg)" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="ewaybill_no">E-Waybill No. <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="ewaybill_no" name="ewaybill_no" placeholder="Enter E-Waybill No." />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="gst_paid_by">GST Paid By <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="gst_paid_by" name="gst_paid_by">
                                                                <option value="">Select</option>
                                                                <option value="Consignor">Consignor</option>
                                                                <option value="Consignee">Consignee</option>
                                                                <option value="Transporter">Transporter</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="lr_type">LR Type <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="lr_type" name="lr_type">
                                                                <option value="">Select</option>
                                                                <option value="Inward">Inward</option>
                                                                <option value="Outward">Outward</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="billing_type">Billed or Pay <span class="text-danger">*</span></label>
                                                            <select class="form-select" id="billing_type" name="billing_type">
                                                                <option value="">Select</option>
                                                                <option value="To be Billed">To be Billed</option>
                                                                <option value="To Pay">To Pay </option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="load_type">Load Type</label>
                                                            <select class="form-select" id="load_type" name="load_type">
                                                                <option value="">Select</option>
                                                                <option value="FTL">FTL</option>
                                                                <option value="Bulk">Bulk</option>
                                                                <option value="CEP">CEP</option>
                                                                <option value="FCL">FCL</option>
                                                                <option value="LCP">LCP</option>
                                                                <option value="LTL">LTL</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="lr_charges">LR Charges</label>
                                                           <select class="form-select" id="lr_charges" name="lr_charges">
                                                                <option value="">Select</option>
                                                                @foreach($lorryCharges as $value)
                                                                    <option value="{{ $value }}">{{ $value }}</option>
                                                                @endforeach
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
                                                            <a href="#" class="btn btn-sm btn-outline-danger me-50" id="deleteSelected">
                                                                <i data-feather="x-circle"></i> Delete
                                                            </a>

                                                            <a href="#" id="addRowBtn" class="btn btn-sm btn-outline-primary">
                                                               <i data-feather="plus"></i> Add New Item
                                                            </a>

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
                                                                            <input type="checkbox" class="form-check-input row-checkbox" id="checkAll">
                                                                            <label class="form-check-label" for="Email"></label>
                                                                        </div> 
                                                                    </th>
                                                                    <th width="271">Location <span class="text-danger">*</span></th>
                                                                    <th width="185">Type <span class="text-danger">*</span></th>
                                                                    <th width="197">No of Articles <span class="text-danger">*</span></th>
                                                                    <th width="197">Weight <span class="text-danger">*</span></th>
                                                                    <th width="246" class=" text-end">Freight(Rs) <span class="text-danger">*</span></th>
                                                                  </tr>
                                                                </thead>
                                                                <tbody class="mrntableselectexcel" id="item-table-body">
                                                                    <tr>
                                                                        <td class="customernewsection-form">
                                                                            <div class="form-check form-check-primary custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input rowCheckbox" name="locations[0][selected]" id="row_0">
                                                                                <label class="form-check-label" for="row_0"></label>
                                                                            </div> 
                                                                        </td>
                                                                        <td class="poprod-decpt">
                                                                            <input type="text" name="locations[0][location_name]" placeholder="Select" class="form-control mw-100 location-update  route-master-autocomplete" placeholder="Start typing  locations..." data-type="location"  />
                                                                        <input type="hidden" name="locations[0][location_id]" class="route-master-id" data-type="location" />
                                                                        </td>
                                                                        <td>
                                                                            <select class="form-select mw-100" name="locations[0][type]">
                                                                                <option value="">Select</option>
                                                                                <option value="Pick Up">Pick Up</option>
                                                                                <option value="Drop Off" selected>Drop Off</option>
                                                                            </select> 
                                                                            
                                                                        </td>
                                                                        <td><input type="text" name="locations[0][no_of_articles]"  class="form-control mw-100" /></td>
                                                                        <td><input type="text" name="locations[0][weight]"  class="form-control mw-100" /></td>
                                                                        <td><input type="text" name="locations[0][freight]"  class="form-control mw-100 text-end" /></td>
                                                                    </tr>
                                                                </tbody>
                                                                    <tfoot>
                                                                        <!-- Freight Total Row -->
                                                                        <tr class="totalsubheadpodetail">
                                                                            <td colspan="5"></td>
                                                                            <td class="text-end" id="freightAmount"></td>
                                                                        </tr>

                                                                        <!-- Route Details and LR Summary Side-by-Side -->
                                                                        <tr valign="top">
                                                                            <!-- Route Details Column -->
                                                                            <td colspan="4" rowspan="10">
                                                                                <table class="table border" id="routeDetailsBox">
                                                                                    <tr>
                                                                                        <td class="p-0">
                                                                                            <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Route Details</strong></h6>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td class="poprod-decpt">
                                                                                            <span class="poitemtxt mw-100"><strong>Source</strong>: <span id="routeSource">--</span></span>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td class="poprod-decpt">
                                                                                            <span class="poitemtxt mw-100"><strong>Destination</strong>: <span id="routeDestination">--</span></span>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td class="poprod-decpt">
                                                                                            <span class="badge rounded-pill badge-light-primary"><strong>Weight</strong>: <span id="routeWeight">--</span></span>
                                                                                            <span class="badge rounded-pill badge-light-primary"><strong>No of Article</strong>: <span id="routeArticles">--</span></span>
                                                                                            <span class="badge rounded-pill badge-light-primary"><strong>Points</strong>: <span id="routePoints">--</span></span>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td class="poprod-decpt">
                                                                                            <span class="badge rounded-pill badge-light-primary"><strong>Vehicle Type</strong>: <span id="routeVehicle">--</span></span>
                                                                                            <span class="badge rounded-pill badge-light-primary"><strong>Capacity</strong>: <span id="routeCapacity">--</span></span>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>
                                                                            </td>

                                                                            <!-- LR Summary Column -->
                                                                            <td colspan="3">
                                                                                <table class="table border mrnsummarynewsty">
                                                                                    <tr>
                                                                                        <td colspan="2" class="p-0">
                                                                                            <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                                                                <strong>LR Summary</strong>
                                                                                            </h6>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr class="totalsubheadpodetail">
                                                                                        <td width="55%"><strong>Sub Total</strong></td>
                                                                                        <td class="text-end" id="subTotalAmount">0.00</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td><strong>LR Charges</strong></td>
                                                                                        <td class="text-end" id="lrCharges">0.00</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td><strong>Freight Charges</strong></td>
                                                                                        <td id="FreightChargeshtml" class="text-end">0.00</td>
                                                                                    </tr>
                                                                                    <tr class="voucher-tab-foot">
                                                                                        <td class="text-primary"><strong>Total Freight Charges</strong></td>
                                                                                        <td>
                                                                                            <div class="quottotal-bg justify-content-end">
                                                                                                <h5 id="totalFreightAmount">0.00</h5>
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>
                                                                                </table>

                                                                                <!-- Hidden Inputs -->
                                                                                <input type="hidden" name="sub_total" id="subTotalInput" value="0.00">
                                                                                <input type="hidden" id="totalFreightInput" class="totalFreightInput" name="total_freight" value="0.00">

                                                                                <input type="hidden" id="fixedAmountGlobal" value="0">
                                                                                 <input type="hidden" id="sourceDefaultAmountGlobal" value="0">
                                                                                <input type="hidden" id="activeFreePointGlobal" value="0">
                                                                                <input type="hidden" id="freeAmountGlobal" value="0">
                                                                            </td>
                                                                        </tr>
                                                                    </tfoot>



                                                        </table>
                                                    </div>
                                                      
                                                     
                                                     
                                                     
                                                     
                                                     <div class="row mt-2">
														  
                                                       <div class="col-md-12">
                                                             <div class = "row">
                                                                    <div class="col-md-4">
                                                                       <div class="mb-1">
                                                                           <label class="form-label">Upload Document</label>
                                                                           <input type="file" class="form-control" name = "attachments[]" onchange = "addFiles(this,'main_order_file_preview')" max_file_count = "{{isset($maxFileCount) ? $maxFileCount : 10}}" multiple >
                                                                           <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                                                       </div>
                                                                   </div> 
                                                                   <div class = "col-md-6" style = "margin-top:19px;">
                                                                       <div class = "row" id = "main_order_file_preview">
                                                                       </div>
                                                                   </div>
                                                                   </div>
                                                            </div>

                                                        <div class="col-md-12">
                                                            <div class="mb-1">  
                                                                <label class="form-label">Final Remarks</label> 
                                                                <textarea type="text" rows="4" class="form-control" placeholder="Enter Remarks here..." name="remarks"></textarea> 

                                                            </div>
                                                        </div>

                                                     </div> 
                                                    
												</div> 
                                                 
                                             </div> 
                                            </div>
                             </div>
                            
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->
</form>

@endsection
@section('scripts')

<script>
    $(document).on('change', '#lr_charges', function () {
    calculateTotals();
   });
   function setStatusAndSubmit(status) {
    document.getElementById('statusInput').value = status;

    if ($('#cost_center_wrapper').is(':visible')) {
        const costCenterVal = $('#cost_center_id').val();

        if (!costCenterVal) {
            $('#cost_center_error').show();
            $('#cost_center_id').addClass('is-invalid');
            return; 
        } else {
            $('#cost_center_error').hide();
            $('#cost_center_id').removeClass('is-invalid');
        }
    }
}
    document.getElementById('checkAll').addEventListener('change', function () {
        document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = this.checked);
    });

</script>

<script>
    const activeFreePointId = parseInt($('#activeFreePointGlobal').val() || 0);
    const fixedAmountGlobalId = parseInt($('#fixedAmountGlobal').val() || 0);
    const freeAmountGlobalId = parseInt($('#freeAmountGlobal').val() || 0);
    function calculateTotals() {
    let subTotal = 0;

    $('input[name*="[freight]"]').each(function () {
        const val = parseFloat($(this).val()) || 0;
        subTotal += val;
    });

    const lr = parseFloat($('#lr_charges').val()) || 0;
    const freightCharge = parseFloat($('#freight_charges').val()) || 0;

    const total = subTotal + lr + freightCharge;

    $('#subTotalAmount').text(subTotal.toFixed(2));
    $('#lrCharges').text(lr.toFixed(2));
    $('#FreightChargeshtml').text(freightCharge.toFixed(2));
    $('#totalFreightAmount').text(total.toFixed(2));
console.log($('#totalFreightAmount').html());

    // Update hidden fields
    $('#subTotalInput').val(subTotal.toFixed(2));
    $('#totalFreightInput').val(total.toFixed(2));
}

// show location details here
function updateRouteDetailsUI() {
    const $rows = $('#item-table-body').find('tr');

    const source = $('#sourceText').text() || 'Not selected';
    const destination = $('#destinationText').text() || 'Not selected';
    let totalWeight = 0;
    let totalArticles = 0;
    let totalPoints = 0;

    $rows.each(function () {
        const weight = parseFloat($(this).find('input[name*="[weight]"]').val()) || 0;
        const articles = parseInt($(this).find('input[name*="[no_of_articles]"]').val()) || 0;
        totalWeight += weight;
        totalArticles += articles;
        totalPoints += 1;
    });

    const $vehicle = $('#vehicle_id option:selected');
    const vehicleText = $vehicle.length ? $vehicle.text() : $('#routeVehicle').text() || 'Not selected';
    const vehicleCapacity = $vehicle.length ? $vehicle.data('capacity') : $('#routeCapacity').text() || '--';

    $('#routeSource').text(source);
    $('#routeDestination').text(destination);
    $('#routeWeight').text(totalWeight);
    $('#routeArticles').text(totalArticles);
    $('#routePoints').text(totalPoints);
    $('#routeVehicle').text(vehicleText);
    $('#routeCapacity').text(vehicleCapacity);
}


 let rowIndex = 1;

$('#addRowBtn').on('click', function () {
    const tbody = $('.mrntableselectexcel');
    let incomplete = false;

    // Check required fields in all existing rows
    tbody.find('tr').each(function () {
        const requiredFields = [
            $(this).find('.route-master-autocomplete[data-type="location"]'),
            $(this).find('select[name*="[type]"]'),
            $(this).find('input[name*="[no_of_articles]"]'),
            $(this).find('input[name*="[weight]"]'),
            $(this).find('input[name*="[freight]"]')
        ];

        for (const field of requiredFields) {
            if (field.length && field.val().trim() === '') {
                incomplete = true;
                return false;
            }
        }
    });

    if (incomplete) {
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Row',
            text: 'Please fill all required fields before adding a new row.',
            confirmButtonText: 'OK'
        });
        return;
    }

    const rowId = 'row_' + rowIndex;

    const newRow = $(`
        <tr>
            <td>
                <div class="form-check form-check-primary custom-checkbox">
                    <input type="checkbox" class="form-check-input rowCheckbox" name="locations[${rowIndex}][selected]" id="${rowId}" value="${rowIndex}">
                    <label class="form-check-label" for="${rowId}"></label>
                </div>
            </td>
            <td>
                <input type="text" name="locations[${rowIndex}][location_name]" class="form-control mw-100 route-master-autocomplete location-update" placeholder="Start typing locations..." data-type="location">
                <input type="hidden" name="locations[${rowIndex}][location_id]" class="route-master-id" data-type="location">
            </td>
            <td>
                <select class="form-select mw-100" name="locations[${rowIndex}][type]">
                    <option value="">Select</option>
                    <option value="Pick Up">Pick Up</option>
                    <option value="Drop Off">Drop Off</option>
                </select>
            </td>
            <td><input type="text" name="locations[${rowIndex}][no_of_articles]" class="form-control mw-100" /></td>
            <td><input type="text" name="locations[${rowIndex}][weight]" class="form-control mw-100" /></td>
            <td><input type="text" name="locations[${rowIndex}][freight]" class="form-control mw-100 text-end freight-input" /></td>
        </tr>
    `);

    tbody.append(newRow);
  

    const activeFreePoint = parseInt($('#activeFreePointGlobal').val() || 0);
    const fixedAmountGlobal = parseInt($('#fixedAmountGlobal').val() || 0);
    const freeAmountGlobal = parseInt($('#freeAmountGlobal').val() || 0);

    setTimeout(() => {
    const $rows = $('#item-table-body').find('tr');

    $rows.each(function(index) {
        const $row = $(this);
        const freightInput = $row.find('input[name*="[freight]"]');
        const currentVal = freightInput.val();

        // Only set freight if it's empty or 0
        // if (!currentVal || parseFloat(currentVal) === 0) {
        //     if (index < activeFreePoint) {
        //         freightInput.val(0);
        //     } else {
        //         if (fixedAmountGlobal) {
        //             freightInput.val(fixedAmountGlobal);
        //         } else {
        //             freightInput.val(freeAmountGlobal);
        //         }
        //     }
        // }

        // Re-bind on input
        freightInput.off('input').on('input', calculateTotals);
    });

    calculateTotals();
}, 300);

    rowIndex++;
});


 $(document).ready(function () {
        calculateTotals();
        $('#lrCharges, #freightCharges').on('input', calculateTotals);
        $(document).on('input', 'input[name*="[freight]"]', calculateTotals);
  });

// delete row script
$(document).on('click', '#deleteSelected', function (e) {
    e.preventDefault(); 
    e.stopImmediatePropagation(); 

    const selectedRows = $('.rowCheckbox:checked').closest('tr');

    if (selectedRows.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Rows Selected',
            text: 'Please select at least one row to delete.',
            confirmButtonText: 'OK'
        });
        return;
    }   

    Swal.fire({
        icon: 'question',
        title: 'Are you sure?',
        text: 'Do you want to delete the selected row(s)?',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            selectedRows.remove();
           applyFreightToRows(null, null, selectedRows)
            calculateTotals();
            updateRouteDetailsUI();
        }
    });
});

$(document).ready(function () {
    $('#item-table-body tr').each(function () {
        const $row = $(this);
        const locationId = $row.find('input[name*="[location_id]"]').val();
        if (locationId && globalSourceId) {
            checkFreePoint(locationId, globalSourceId, $row, true); 
        }
    });
});

</script>

<script>
    function getDocNumberByBookId(element = null, reset = true) {
    // Fallback to dropdown value if element is not passed (e.g., on page load)
    let bookId = element ? element.value : $('#series_id_input').val();

    // Exit early if no bookId
    if (!bookId) return;

    let documentDate = $("#document_date").val() || '';
    let actionUrl = '{{ route("book.get.doc_no_and_parameters") }}?book_id=' + bookId + '&document_date=' + documentDate;

    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status === 200) {
                $("#book_code_input").val(data.data.book_code);

                if (reset) {
                    $("#document_number").val(data.data.doc.document_number || '');
                }

                $("#document_number").attr('readonly', data.data.doc.type !== 'Manually');
            }

            if (data.status === 404 && reset) {
                $("#book_code_input").val("");
                alert(data.message);
            }

            if (data.status === 500 && reset) {
                $("#book_code_input").val("");
                $("#series_id_input").val("");
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                });
            }
        });
    });
}

$(document).ready(function () {
    getDocNumberByBookId();
});
</script>
    <script>
        const routeMasters = [
  @if($routeMasters->isNotEmpty())
    @foreach($routeMasters as $rm)
        {
            label: "{{ $rm->name }}",
            value: "{{ $rm->name }}",
            id: {{ $rm->id }}
        }@if(!$loop->last),@endif
    @endforeach
@else
    null
@endif

];

$(document).on('focus click', '.route-master-autocomplete', function () {
    const $input = $(this);

    // Agar pehle init nahi hua hai to init karo
    if (!$input.data('ui-autocomplete')) {
        $input.autocomplete({
            minLength: 0,
            source: function (request, response) {
                const type = $input.data('type');
                const term = $.trim(request.term).toLowerCase();

                let filtered = routeMasters;

                // Exclude selected source/destination from location field
                if (type === 'location') {
                    const sourceName = $('input[data-type="source"]').val()?.toLowerCase() || '';
                    const destinationName = $('input[data-type="destination"]').val()?.toLowerCase() || '';

                    filtered = routeMasters.filter(item =>
                        item.label.toLowerCase() !== sourceName &&
                        item.label.toLowerCase() !== destinationName
                    );
                }

                const matches = filtered.filter(item =>
                    item.label.toLowerCase().includes(term)
                );

                if (!matches.length) {
                    response([{ label: 'No results found', value: '', id: null, disabled: true }]);
                } else {
                    response(matches);
                }
            },

            select: function (event, ui) {
                if (ui.item.disabled) {
                    event.preventDefault();
                    return false;
                }

                $input.val(ui.item.label);
                const type = $input.data('type');

                if (type === 'source' || type === 'destination') {
                    $(`#${type}IdInput`).val(ui.item.id);
                    $(this).trigger('sorrceDestination', ui);
                }

                if (type === 'location') {
                    const nameAttr = $input.attr('name');
                    const match = nameAttr.match(/locations\[(\d+)\]\[location_name\]/);
                    if (match) {
                        const index = match[1];
                        const $hiddenInput = $(`input[name="locations[${index}][location_id]"]`);

                        $hiddenInput.val(ui.item.id);
                        calculateTotals();
                    }
                }

                return false;
            },

            change: function (event, ui) {
                const type = $input.data('type');

                if (!ui.item || ui.item.disabled) {
                    $input.val('');
                    if (type === 'location') {
                        const nameAttr = $input.attr('name');
                        const match = nameAttr.match(/locations\[(\d+)\]\[location_name\]/);
                        if (match) {
                            const index = match[1];
                            $(`input[name="locations[${index}][location_id]"]`).val('');
                            calculateTotals();
                        }
                    }
                }
            }
        });
    }

    // ✅ Force open suggestions on every focus/click
    $input.autocomplete('search', '');
});




//customer autocomplete
const customerList = [
@if($customers->isNotEmpty())
    @foreach($customers as $customer)
        { 
            label: "{{ addslashes($customer->company_name) }}",
            value: "{{ addslashes($customer->company_name) }}",
            id: {{ $customer->id }}
        },
    @endforeach
    @else
    null
    @endif
];

$(document).on('focus', '.customer-autocomplete', function () {
    const $input = $(this);

    if (!$input.data('ui-autocomplete')) {
        $input.autocomplete({
            minLength: 0,
            source: function (request, response) {
                const results = $.ui.autocomplete.filter(customerList, request.term);

                if (!results.length) {
                    results.push({
                        label: 'No results found',
                        value: '',
                        id: ''
                    });
                }

                response(results);
            },
            select: function (event, ui) {
                if (ui.item.value === '') {
                    event.preventDefault(); 
                    return false;
                }

               event.preventDefault(); 
                const type = $input.data('type');
                $input.val('');
                $input.val(ui.item.label);
                $(`.customer-id[data-type="${type}"]`).val(ui.item.id);
                
                return false;
            },
            close: function () {
                $(this).trigger('change'); 
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
        
    }
});

    //drivers autocomplete
 const driverList = [
    @if($drivers->isNotEmpty())
    @foreach($drivers as $driver)
        {
            label: "{{ addslashes($driver->name) }}",
            value: "{{ addslashes($driver->name) }}",
            id: {{ $driver->id }}
        },
    @endforeach
    @else
    null
@endif
];

$(document).on('focus', '.driver-autocomplete', function () {
    const $input = $(this);

    if (!$input.data('ui-autocomplete')) {
        $input.autocomplete({
            minLength: 0,
            source: function (request, response) {
                const term = $.ui.autocomplete.escapeRegex(request.term);
                const matcher = new RegExp(term, "i");

                const matches = $.grep(driverList, function (item) {
                    return matcher.test(item.label);
                });

                if (matches.length) {
                    response(matches);
                } else {
                    response([{ label: "No results found", value: "", id: "" }]);
                }
            },
            select: function (event, ui) {
                if (ui.item.label === "No results found") {
                    event.preventDefault(); 
                    return false;
                }

                $input.val(ui.item.label);
                $input.closest('div').find('.driver-id').val(ui.item.id);
                return false;
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }
});


 </script>   

@if($vehicleNumbers->isNotEmpty())
<script>
const vehicleNumbers = [
    @foreach($vehicleNumbers as $vn)
        {
            label: "{{ $vn->lorry_no }} ({{ $vn->vehicleType->name ?? '' }})",
            value: "{{ $vn->lorry_no }} ({{ $vn->vehicleType->name ?? '' }})",
            id: {{ $vn->id }}
        }{{ !$loop->last ? ',' : '' }}
    @endforeach
];
</script>
@endif

<script>

    $(document).on('input', '#no_of_bundles', function () {
    var perbundle = parseFloat($("#per_bundles").val()) || 0;
    var nobundle = parseFloat($("#no_bundles").val()) || 0;
    var changenobundle = parseFloat($(this).val()) || 0;
    var nobundleamount = parseFloat($("#no_bundles_amount").val()) || 0;

    console.log("Per bundle:", perbundle);
    console.log("No of bundles:", nobundle);
    console.log("change bundles:", changenobundle);
    console.log("No bundle amount:", nobundleamount);

    if (nobundle > changenobundle) {
        console.log("👉 IF chal gaya");
        $('#freight_charges').val(nobundleamount);
    } else {
        console.log("👉 ELSE chal gaya");
        var valuecal = changenobundle - nobundle;
        console.log(valuecal);
        var bundleamount = (perbundle * valuecal) + nobundleamount;
         console.log(bundleamount);
        $('#freight_charges').val(bundleamount);
    }
    calculateTotals();
});




$('.vehicle-number-autocomplete').each(function () {
    $(this).autocomplete({
        source: function (request, response) {
            const results = $.ui.autocomplete.filter(vehicleNumbers, request.term);
            if (!results.length) {
                results.push({
                    label: 'No results found',
                    value: '',
                    id: ''
                });
            }
            response(results);
        },
        minLength: 0,
        select: function (event, ui) {
            if (ui.item.value === '') {
                return false;
            }
            $(this).val(ui.item.label);
            $(this).closest('div').find('.vehicle-number-id').val(ui.item.id);
            $(this).trigger('vehicleNumberSelected', ui);
            return false;
        }
    }).focus(function () {
        $(this).autocomplete('search', '');
    });
});



</script>
<script>
    // Make it globally accessible
let lastFreightChargeMessage = ''; // Track last message
    function fetchFreightCharge(sourceId,destId,vehicleId,custId) {
        sourceId    = sourceId   || $('input[name="source_id"]').val();
        const destinationId      = destId     || $('input[name="destination_id"]').val();
        vehicleId = vehicleId  || $('input[name="vehicle_number_id"]').val();
        const customerId      = custId     || $('input[name="customer_id"]').val();

        console.log("Final values =>", sourceId, destId, vehicleId, custId);

        if (!sourceId || !destinationId) return;

        $.ajax({
            url: '/freight-charge-details',
            method: 'GET',
            data: {
                source_id: sourceId,
                destination_id: destinationId,
                vehicle_id:vehicleId,
                customer_id:customerId
            },
            success: function (response) {
                if(response.message == 'Get freight charge data')
                {
                $('#distance').val(response.distance).prop('disabled', true);
                $('#freight_charges').val(response.freight_charges).prop('disabled', true);
                $('#distanceInput').val(response.distance);
                $('#freightCharges').val(response.freight_charges);
                $('#FreightChargeshtml').text(
                    parseFloat(response.freight_charges ?? 0)
                );
                $('#no_of_bundles').val(response.no_bundle);
                $('#per_bundles').val(response.per_bundle);
                $('#no_bundles').val(response.no_bundle);
                $('#no_bundles_amount').val(response.freight_charges);

                // ✅ Set text content for display
                $('#routeVehicle').text(response.vehicle_type_name);
                $('#routeCapacity').text(response.vehicle_type_capacity + ' ' + response.vehicle_type_unit_name);
                $('#routeSource').text(response.source_name);
                $('#routeDestination').text(response.destination_name);
                calculateTotals();
                }else if(response.message && response.message.includes('No freight charge found'))
                {
                    if (lastFreightChargeMessage !== response.message) 
                    {
                        console.log('Resetting fields because of new "No freight charge found" message');
                        console.log('else wala part');
                        $('#distance').val('').prop('disabled', false);
                        $('#freight_charges').val('').prop('disabled', false);
                        $('#distanceInput').val('');
                        $('#freightCharges').val('');
                        $('#FreightChargeshtml').text('0.00');
                    

                        // ✅ Set text content for display
                        $('#routePoints').html('');
                        $('#routeWeight').html('');
                        $('#routeArticles').html('');
                        $('#routeVehicle').html('');
                        $('#routeCapacity').html('');
                        $('#routeSource').html('');
                        $('#routeDestination').html('');
                    }
                    lastFreightChargeMessage = response.message;  // Update last message
                    calculateTotals();
                }
                

            },
            error: function (response) {
                let message = response.responseJSON?.message ?? '';

                if (message.includes('No freight charge found')) 
                {
                    if (lastFreightChargeMessage !== message) 
                    {
                        console.log('Resetting fields due to new "No freight charge found" message.');

                        $('#distance').val('').prop('disabled', false);
                        $('#freight_charges').val('').prop('disabled', false);
                        $('#distanceInput').val('');
                        $('#freightCharges').val('');
                        $('#FreightChargeshtml').text('0.00');
                
                        // ✅ Set text content for display
                        $('#routePoints').html('');
                        $('#routeWeight').html('');
                        $('#routeArticles').html('');
                        $('#routeVehicle').html('');
                        $('#routeCapacity').html('');
                        $('#routeSource').html('');
                        $('#routeDestination').html('');
                    }
                }
                lastFreightChargeMessage = message;
                calculateTotals();
            }
        });
    }
let oldSource = $('input[name="source_name"]').val();
let oldDestination = $('input[name="destination_name"]').val();

$('input[name="source_name"], input[name="destination_name"], input[name="vehicle_number"], input[name="customer_name"]')
    .on('autocompleteselect', function (event, ui) {
        const type = $(this).attr("name");

        let sourceId = $('input[name="source_id"]').val();
        let destId   = $('input[name="destination_id"]').val();
        let vehicleId= $('input[name="vehicle_number_id"]').val();
        let custId   = $('input[name="customer_id"]').val();
        console.log(type, ui);
        // Agar current field source/dest/vehicle/customer hai to ui.item.id ka use karo
        if (type === "source_name") sourceId = ui.item.id;
        if (type === "destination_name") destId = ui.item.id;
        if (type === "vehicle_number") vehicleId = ui.item.id;
        if (type === "customer_name") custId = ui.item.id;

        console.log('sourceId:', sourceId);
        console.log('destId:', destId);
        console.log('vehicleId:', vehicleId);
        console.log('custId:', custId);

         if (oldSource !== sourceId || oldDestination !== destId) {
            
            let defaultRow = `
                <tr>
                    <td class="customernewsection-form">
                        <div class="form-check form-check-primary custom-checkbox">
                            <input type="checkbox" class="form-check-input rowCheckbox" name="locations[0][selected]" id="row_0">
                            <label class="form-check-label" for="row_0"></label>
                        </div> 
                    </td>
                    <td class="poprod-decpt">
                        <input type="text" name="locations[0][location_name]" placeholder="Select" 
                               class="form-control mw-100 location-update route-master-autocomplete" 
                               data-type="location" />
                        <input type="hidden" name="locations[0][location_id]" class="route-master-id" data-type="location" />
                    </td>
                    <td>
                        <select class="form-select mw-100" name="locations[0][type]">
                            <option value="">Select</option>
                            <option value="Pick Up">Pick Up</option>
                            <option value="Drop Off" selected>Drop Off</option>
                        </select> 
                    </td>
                    <td><input type="text" name="locations[0][no_of_articles]"  class="form-control mw-100" /></td>
                    <td><input type="text" name="locations[0][weight]"  class="form-control mw-100" /></td>
                    <td><input type="text" name="locations[0][freight]"  class="form-control mw-100 text-end" /></td>
                </tr>
            `;

            $("#item-table-body").html(defaultRow);

            // पुरानी values update कर दो
            oldSource = sourceId;
            oldDestination = destId;
        }

        if (sourceId && destId && vehicleId) {
            fetchFreightCharge(sourceId,destId,vehicleId,custId);

            // Clear related fields
            $('#activeFreePointGlobal').val('');
            $('#fixedAmountGlobal').val('');
            $('#freeAmountGlobal').val('');
            $('.totalFreightInput').val('');

            calculateTotals();
        }
    });



    $(document).on('change', 'input[name*="[weight]"], input[name*="[no_of_articles]"]', function () {
        updateRouteDetailsUI(); 
        fetchFreightCharge();   
    });
</script>

<script>
$(document).ready(function () {
    function loadCostCenters(locationId) {
        // Clear previous options and hide wrapper
        $('#cost_center_id').empty();
        $('#cost_center_wrapper').hide();
        $('#cost_center_id').prop('required', false);

        if (locationId) {
            $.ajax({
                url: '/get-cost-centers-by-location/' + locationId,
                type: 'GET',
                success: function (response) {
                    if (response.success && response.data.length > 0) {
                        $.each(response.data, function (key, center) {
                            $('#cost_center_id').append(
                                `<option value="${center.id}">${center.name}</option>`
                            );
                        });

                        // Automatically select first cost center
                        $('#cost_center_id').val(response.data[0].id);

                        $('#cost_center_wrapper').show();
                        $('#cost_center_id').prop('required', true);
                        $('#cost_center_error').hide();
                    }
                },
                error: function () {
                    alert('Unable to fetch cost centers.');
                }
            });
        }
    }

    $('#locationId').on('change', function () {
        const locationId = $(this).val();
        loadCostCenters(locationId);
    });

    const initialLocationId = $('#locationId').val();
    if (initialLocationId) {
        loadCostCenters(initialLocationId);
    } else {
        $('#cost_center_wrapper').hide();
        $('#cost_center_id').prop('required', false);
        $('#cost_center_error').hide();
    }
});




// location on focus
let activeFreePoint = 0;
let fixedAmount = null;
let sourceRouteId = null;
let freeAmount = null;
let globalSourceId = $('#sourceIdInput').val();

let pricingCache = {}
// checkFreePoint(locationId, sourceId, vehicleId, customerId, $row);
  function checkFreePoint(locationId = null, sourceId = null, vehicleId = null, customerId = null, $targetRow = null, isEditLoad = false) {
    if (!locationId || !sourceId) return;

    $.ajax({
        url: '/get-location-pricing',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            location_id: locationId,
            source_id: sourceId,
            vehicle_id: vehicleId,
            customer_id: customerId,
        },
        success: function (res) {
            $('#fixedAmountDisplay').empty();

            const sourceAmount = parseInt(res.source_amount || 0);
            $('#sourceDefaultAmountGlobal').val(sourceAmount);

            // Store response to cache
            pricingCache[locationId] = {
                type: res.status,
                free_point: parseInt(res.free_point || 0),
                amount: parseInt(res.amount || 0),
                freeAmount: parseInt(res.free_amount || 0),
            };

            $('#activeFreePointGlobal').val(pricingCache[locationId].free_point || 0);
            $('#fixedAmountGlobal').val(pricingCache[locationId].amount || 0);
            $('#freeAmountGlobal').val(pricingCache[locationId].freeAmount || 0);

            applyFreightToRows(pricingCache[locationId], $targetRow); 
        }
    });
}

function applyFreightToRows($specificRow = null,$row, deletedRow = null) {

    const activeFreePoint = parseInt($('#activeFreePointGlobal').val() || 0);
    const sourceDefaultAmount = parseFloat($('#sourceDefaultAmountGlobal').val() || 0);
    if (deletedRow !== null) {
        const $rows = $('#item-table-body').find('tr');
        //$rows.eq(0).find('input[name*="[freight]"]').val(0);

        if (deletedRow.length <= activeFreePoint) {
            for (let i = 0; i < activeFreePoint && i < $rows.length; i++) {
                $rows.eq(i).find('input[name*="[freight]"]').val(0);
            }
        }

        return;
    }
   
    const freightAmount = parseFloat($row.find('input[name*="[freight]"]').val()) || 0;

    const locationId = $row.find('input[name*="[location_id]"]').val()?.trim();
    const $freightInput = $row.find('input[name*="[freight]"]');

    if (!locationId) return;
       const pricing = $specificRow;

    if (!pricing) {
        $freightInput.val(sourceDefaultAmount > 0 ? sourceDefaultAmount : '');
        return;
    }
   
    $index = $row.index();

     if (pricing) {
            if (pricing.type === 'both_exist') {
              if ($index < activeFreePoint) {

                $freightInput.val(0);
            } else {
                if(pricing.amount){
                  $freightInput.val(pricing.amount && parseFloat(pricing.amount) > 0 ? parseFloat(pricing.amount) : 0);
                }else{
                    $freightInput.val(pricing.freeAmount && parseFloat(pricing.freeAmount) > 0 ? parseFloat(pricing.freeAmount) : 0);
                }
                
            }

            }else if (pricing.type === 'free_point') {
                if ($index < activeFreePoint) {
                    $freightInput.val(0);
                } else {
                    $freightInput.val(parseFloat(pricing.freeAmount));
                }
            } else if (pricing.type === 'exists_in_fixed') {
                if(pricing.amount === 0){
                   $freightInput.val('');
                }else{
                    $freightInput.val(parseFloat(pricing.amount));
                }
                
            } else {
                $freightInput.val(sourceDefaultAmount > 0 ? sourceDefaultAmount : '');
            }
        } else {
            $freightInput.val(sourceDefaultAmount > 0 ? sourceDefaultAmount : '');
        }


        if (deletedRow !== null) {
        const $targetRow = $rows.eq(deletedRow);
        const locationId = $targetRow.find('input[name*="[location_id]"]').val()?.trim();
        const $freightInput = $targetRow.find('input[name*="[freight]"]');

        const pricing = pricingCache[locationId]; 

        if (deletedRow <= activeFreePoint) {
            $freightInput.val(0);
        } else {
            $freightInput.val(pricing?.amount && parseFloat(pricing.amount) > 0 ? parseFloat(pricing.amount) : 0); 
        }
    }

    
    calculateTotals();
}



function handleLocationUpdate($input) {
    const $row = $input.closest('tr');
    const locationId = $row.find('input[name*="[location_id]"]').val();
    const sourceId = $('#sourceIdInput').val();
    const vehicleId = $('#vehicle_number_id').val();
    const customerId = $('#customer_id').val();



    if (locationId && sourceId && vehicleId && customerId) {
        checkFreePoint(locationId, sourceId, vehicleId, customerId, $row); 
    }
}



$(document).on('autocompleteselect autocompletechange', '.location-update', function () {
    handleLocationUpdate($(this));
    calculateTotals(); 
});

$(document).on('change', 'input[name*="[location_id]"]', function () {
    const $row = $(this).closest('tr');
    const $input = $row.find('.location-update');
    handleLocationUpdate($input);
});

//File upload preview js code
    let fileInputData = {};
      const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    const MAX_FILE_SIZE = 5120; // in KB (5MB)
       function appendFilePreviews(fileUrl, previewElementId, index, fileId = null, inputName = '') {
    const previewContainer = document.getElementById(previewElementId);
    if (!previewContainer) return;

    const fileName = fileUrl.split('/').pop();

    const previewHtml = `
        <div class="col-1 file-preview-item image-uplodasection expenseadd-sign" data-index="${index}" data-file-id="${fileId ?? ''}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="feather feather-file-text me-2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>
             <div class="delete-img text-danger" data-edit-flag="true" data-index="${index}" data-input-id="${previewElementId}" data-input-name="${inputName}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    `;

     previewContainer.insertAdjacentHTML('beforeend', previewHtml);
    feather.replace({ width: 20, height: 20 });
}


function addFiles(element, previewElementId) {
    const input = element;
    const allowedMaxFilesCount = Number(input.getAttribute('max_file_count') || 1);
    const inputId = input.name.replace('[]', '');
    const files = Array.from(input.files);

    if (!fileInputData[inputId]) {
        fileInputData[inputId] = [];
    }

    const currentCount = fileInputData[inputId].length;

    if ((files.length + currentCount) > allowedMaxFilesCount) {
        Swal.fire({
            title: 'Error!',
            text: `Maximum ${allowedMaxFilesCount} files are allowed.`,
            icon: 'error'
        });
        resetInput(inputId, input);
        return;
    }

    let allFiles = [...fileInputData[inputId], ...files];
    let invalidMessage = '';

    for (const file of files) {
        const ext = file.name.split('.').pop().toLowerCase();
        const sizeKB = file.size / 1024;

        if (!ALLOWED_EXTENSIONS.includes(ext) || !ALLOWED_MIME_TYPES.includes(file.type)) {
            invalidMessage = 'Please select valid file types.';
            break;
        }

        if (sizeKB > MAX_FILE_SIZE) {
            invalidMessage = 'Each file must be less than 5MB.';
            break;
        }
    }

    if (invalidMessage) {
        Swal.fire({
            title: 'Error!',
            text: invalidMessage,
            icon: 'error'
        });
        resetInput(inputId, input);
        return;
    }

    fileInputData[inputId] = allFiles.reduce((unique, file) => {
        if (!unique.some(f => f.name === file.name && f.size === file.size)) {
            unique.push(file);
        }
        return unique;
    }, []);

    const newDt = new DataTransfer();
    fileInputData[inputId].forEach(file => newDt.items.add(file));
    input.files = newDt.files;
    refreshPreviews(previewElementId, inputId);
    feather.replace({ width: 20, height: 20 });
}

function refreshPreviews(previewElementId, inputId) {
    const previewWrapper = document.getElementById(previewElementId);
    if (!previewWrapper) return;
    const jsPreviews = previewWrapper.querySelectorAll('.file-preview-item');
    jsPreviews.forEach(el => el.remove());

    fileInputData[inputId].forEach((file, index) => {
        const fileUrl = URL.createObjectURL(file);
        appendFilePreviews(fileUrl, previewElementId, index, null, inputId);
    });
}


function resetInput(inputId, input) {
    const tempDt = new DataTransfer();
    fileInputData[inputId].forEach(file => tempDt.items.add(file));
    input.files = tempDt.files;
}

document.addEventListener('click', function (e) {
    const deleteIcon = e.target.closest('.delete-img');
    if (!deleteIcon) return;

    const isEditMode = deleteIcon.getAttribute('data-edit-flag') === 'true';

    if (isEditMode && deleteIcon.hasAttribute('data-id')) {
        const mediaId = deleteIcon.getAttribute('data-id');
        const removeInput = document.querySelector('input[name="removed_media_ids"]');

        if (removeInput) {
            let existing = removeInput.value ? removeInput.value.split(',') : [];
            if (!existing.includes(mediaId)) {
                existing.push(mediaId);
                removeInput.value = existing.join(',');
            }
        } else {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'removed_media_ids';
            input.value = mediaId;
            document.querySelector('form').appendChild(input);
        }
        deleteIcon.closest('.image-uplodasection').remove();
        return;
    }

    // ✅ New file: remove from fileInputData and input.files
    const index = parseInt(deleteIcon.getAttribute('data-index'));
    const previewElementId = deleteIcon.getAttribute('data-input-id');
    const inputId = deleteIcon.getAttribute('data-input-name');
    const input = document.querySelector(`input[name="${inputId}[]"]`);

    if (!fileInputData[inputId]) return;
    fileInputData[inputId].splice(index, 1);

    const dt = new DataTransfer();
    fileInputData[inputId].forEach(file => dt.items.add(file));
    input.files = dt.files;
    deleteIcon.closest('.image-uplodasection').remove();

    feather.replace({ width: 20, height: 20 });
});
</script>

<script>
    $(document).ready(function () {

        function clearFieldAndId(fieldNameSelector) {
            const $input = $(fieldNameSelector);
            const nameAttr = $input.attr('name');

            const idName = nameAttr.replace('[location_name]', '[location_id]');
            const $idInput = $(`input[name="${idName}"]`);

            $input.val('').trigger('change');
            $idInput.val('');
        }

        function checkForSameName(field1, field2, label1, label2) {
            const val1 = $(field1).val().trim().toLowerCase();
            const val2 = $(field2).val().trim().toLowerCase();

            if (val1 && val2 && val1 === val2) {
                Swal.fire({
                    icon: 'error',
                    title: 'Something went wrong !',
                    text: `${label1} and ${label2} cannot be the same.`,
                });

                clearFieldAndId(field2);
                return true;
            }
            return false;
        }

        function checkLocationAgainstSourceDest($locationInput) {
            const locationVal = $locationInput.val().trim().toLowerCase();
            const sourceVal = $('input[name="source_name"]').val().trim().toLowerCase();
            const destVal = $('input[name="destination_name"]').val().trim().toLowerCase();

            if (locationVal && (locationVal === sourceVal || locationVal === destVal)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Duplicate Location Not Allowed',
                    text: 'Location cannot be same as Source or Destination.',
                });

                clearFieldAndId($locationInput);
                return true;
            }
            return false;
        }

        $('input[name="consignee_name"], input[name="destination_name"]').on('change blur', function () {
            checkForSameName('input[name="customer_name"]', 'input[name="consignee_name"]', 'Consignor', 'Consignee');
            checkForSameName('input[name="source_name"]', 'input[name="destination_name"]', 'Source', 'Destination');
        });

     
        $(document).on('autocompleteselect', 'input[name="customer_name"], input[name="consignee_name"], input[name="source_name"], input[name="destination_name"]', function (e, ui) {
            const selectedName = ui.item.label.trim().toLowerCase();
            const fieldName = $(this).attr('name');

            let matchSelector = '', label1 = '', label2 = '';

            if (fieldName === 'customer_name') {
                matchSelector = 'input[name="consignee_name"]';
                label1 = 'Consignor';
                label2 = 'Consignee';
            } else if (fieldName === 'consignee_name') {
                matchSelector = 'input[name="customer_name"]';
                label1 = 'Consignor';
                label2 = 'Consignee';
            } else if (fieldName === 'source_name') {
                matchSelector = 'input[name="destination_name"]';
                label1 = 'Source';
                label2 = 'Destination';
            } else if (fieldName === 'destination_name') {
                matchSelector = 'input[name="source_name"]';
                label1 = 'Source';
                label2 = 'Destination';
            }

            const matchVal = $(matchSelector).val().trim().toLowerCase();
            if (selectedName && matchVal && selectedName === matchVal) {
                Swal.fire({
                    icon: 'error',
                    title: 'Something went wrong!',
                    text: `${label1} and ${label2} cannot be the same.`,
                });

                clearFieldAndId(this);
                e.preventDefault();
                return false;
            }
        });

        $(document).on('change blur autocompleteselect', 'input[name^="locations"][name$="[location_name]"]', function () {
            checkLocationAgainstSourceDest($(this));
        });

    });
</script>



<script>
    // multipoint filed disable js code
    $(document).ready(function () {
        function toggleLocationFields() {
            const sourceId = $('#sourceIdInput').val();
            const destinationId = $('#destinationIdInput').val();
            const vehicleId = $('#vehicle_number_id').val();
            const customerId = $('#customer_id').val();
            

            const shouldEnable = sourceId && destinationId && vehicleId && customerId;

            $('#item-table-body :input').prop('disabled', !shouldEnable);
        }

        toggleLocationFields();

        $(document).on('autocomplete.select', '.route-master-autocomplete', function (e, ui) {
            const type = $(this).data('type');
            const hiddenInput = $(this).siblings('.route-master-id');

            if (hiddenInput.length && ui.item?.id) {
                hiddenInput.val(ui.item.id);
                $(this).val(ui.item.label);
                toggleLocationFields();
            }
        });
        setInterval(() => {
            toggleLocationFields();
        }, 500);
    });


// Check if table has any data
function tableHasData() {
    let hasData = false;

    $('#item-table-body tr').each(function () {
        $(this).find('input[type="text"], input[type="number"], select').each(function () {
            const val = $(this).val();
            if (val !== null && val.toString().trim() !== '') {
                hasData = true;
                return false; // break inner loop
            }
        });
        if (hasData) {
            return false; // break outer loop
        }
    });

    return hasData;
}

// Check if fields are empty and table has data
function shouldClearTable() {
    const sourceName = $('input[name="source_name"]').val();
    const destinationName = $('input[name="destination_name"]').val();
    const vehicleNumber = $('input[name="vehicle_number"]').val();
    const consignorName = $('input[name="consignor_name"]').val();

    return (sourceName === '' || vehicleNumber === '' || destinationName === '' || consignorName === '') && tableHasData();
}

// Clear table data and reset fields
function clearTableAndFields() {
    $('#item-table-body tr').each(function () {
        $(this).find('input, textarea').val('');
        $(this).find('select').prop('selectedIndex', 0);
    });

    $('#activeFreePointGlobal').val('');
    $('#fixedAmountGlobal').val('');
    $('#freeAmountGlobal').val('');
    $('#distanceInput').val('');
    $('#freightCharges').val('');
    $('#FreightChargeshtml').text('0.00');

    calculateTotals();
    fetchFreightCharge();

    $('#totalFreightAmount').html('0.0');
    $('#totalFreightInput').val('');
    $('#freightCharges').val('');
    
    $('#FreightCharges').html('0.0');
    $('#FreightChargeshtml').text('0.00');
    $('#routePoints').html('');
    $('#routeWeight').html('');
    $('#routeArticles').html('');
    $('#routeVehicle').html('');
    $('#routeCapacity').html('');
    $('#routeSource').html('');
    $('#routeDestination').html('');

    console.log("Cleared table and global fields because source or vehicle was changed or cleared.");
}

$(document).on('sourceDestination', 'input.route-master-autocomplete', function (event, ui) {
    $('#sourceIdInput').val(ui.item.id); 
    $('#totalFreightAmount').html('0.0');
    $('#totalFreightInput').val('');
    $('#freightCharges').val('');
    
    $('#FreightChargeshtml').text('0.00');
    if (tableHasData()) {
        clearTableAndFields();
    }
});

$(document).on('vehicleNumberSelected', '.vehicle-number-autocomplete', function (e, ui) {
    $('#vehicle_number_id').val(ui.item.id); 
     fetchFreightCharge();
    $('#totalFreightAmount').html('0.0');
    $('#totalFreightInput').val('');
    $('#freightCharges').val('');
    $('#FreightChargeshtml').text('0.0');
    if (tableHasData()) {
        clearTableAndFields();
    }
});

$(document).on('input', 'input[name="source_name"], input[name="vehicle_number"]', function () {
    const $this = $(this);

    if ($this.attr('name') === 'source_name' && $this.val().trim() === '') {
        $('#sourceIdInput').val('');
    }

    if ($this.attr('name') === 'vehicle_number' && $this.val().trim() === '') {
        $('#vehicle_number_id').val('');
    }

    if (shouldClearTable()) {
        clearTableAndFields();
    }
});





</script>

@endsection

