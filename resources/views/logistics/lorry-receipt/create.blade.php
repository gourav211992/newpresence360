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
									<ol class="breadcrumb">Home</a>
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
                                                           <select class="form-select select2" name="location" id="locationId">
                                                                <option value="">Select Location</option>
                                                                @foreach($locations as $location)
                                                                    <option value="{{ $location->id }}">{{ $location->store_name }}</option>
                                                                @endforeach
                                                            </select>

                                                        </div> 
                                                     </div>  

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Cost Center <span class="text-danger">*</span></label>  
                                                        </div>  

                                                        <div class="col-md-5"> 
                                                          <select name="cost_center_id" id="cost_center_id" class="form-select select2">
                                                            <option value="">Select Cost Center</option>
                                                        </select>

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
                                                            <input type="text"name="source_name"class="form-control mw-100 route-master-autocomplete"
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

                                                            <input type="hidden" name="customer_id" class="customer-id" data-type="consignor" />
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

                                                            <input type="hidden" name="consignee_id" class="customer-id" data-type="consignee" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="vehicle">Vehicle <span class="text-danger">*</span></label>
                                                             <input type="text"
                                                                        name="vehicle_type_name"
                                                                        class="form-control mw-100 vehicle-type-autocomplete"
                                                                        placeholder="Select Vehicle"  id="vehicle_type_name"/>
                                                                    <input type="hidden"
                                                                        name="vehicle_type_id" class="vehicle-type-id" />
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
                                                            <select class="form-select select2" id="gst_paid_by" name="gst_paid_by">
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
                                                            <select class="form-select select2" id="lr_type" name="lr_type">
                                                                <option value="">Select</option>
                                                                <option value="Inward">Inward</option>
                                                                <option value="Outward">Outward</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="billing_type">Billed or Pay <span class="text-danger">*</span></label>
                                                            <select class="form-select select2" id="billing_type" name="billing_type">
                                                                <option value="">Select</option>
                                                                <option value="To be Billed">To be Billed</option>
                                                                <option value="To Pay">To Pay</option>
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
                                                                                            <span class="badge rounded-pill badge-light-primary"><strong>Vehicle</strong>: <span id="routeVehicle">--</span></span>
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
                                                                                        <td id="FreightCharges" class="text-end">0.00</td>
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
                                                                                <input type="hidden" name="total_freight" id="totalFreightInput" value="0.00">

                                                                                <input type="hidden" id="fixedAmountGlobal" value="0">
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
    }
      // Select/Deselect All
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
    const freightCharge = parseFloat($('#freightCharges').val()) || 0;

    const total = subTotal + lr + freightCharge;

    $('#freightAmount').text(subTotal.toFixed(2));
    $('#subTotalAmount').text(subTotal.toFixed(2));
    $('#lrCharges').text(lr.toFixed(2));
    $('#FreightCharges').text(freightCharge.toFixed(2));
    $('#totalFreightAmount').text(total.toFixed(2));

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
    });

    const vehicleText = $('#vehicle_id option:selected').text() || 'Not selected';
    const vehicleCapacity = $('#vehicle_id option:selected').data('capacity') || '--';

    $('#routeSource').text(source);
    $('#routeDestination').text(destination);
    $('#routeWeight').text(totalWeight);
    $('#routeArticles').text(totalArticles);
    $('#routePoints').text(parseInt($('#activeFreePointGlobal').val() || 0)); 
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
        if (!currentVal || parseFloat(currentVal) === 0) {
            if (index < activeFreePoint) {
                freightInput.val(0);
            } else {
                if (fixedAmountGlobal) {
                    freightInput.val(fixedAmountGlobal);
                } else {
                    freightInput.val(freeAmountGlobal);
                }
            }
        }

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
            applyFreightToRows(selectedRows.length); 
            calculateTotals();
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

$(document).on('focus', '.route-master-autocomplete', function () {
    const $input = $(this);

    if (!$input.data('ui-autocomplete')) {
      $input.autocomplete({
    source: routeMasters,
    minLength: 0,
    select: function (event, ui) {
        $input.val(ui.item.label);

        const type = $input.data('type');
        if (type === 'source' || type === 'destination') {
            $(`#${type}IdInput`).val(ui.item.id);
        }

        if (type === 'location') {
            const nameAttr = $input.attr('name');
            const match = nameAttr.match(/locations\[(\d+)\]\[location_name\]/);
            if (match) {
                const index = match[1];
                const $hiddenInput = $(`input[name="locations[${index}][location_id]"]`);

                $hiddenInput.val(ui.item.id);
                calculateTotals(); // always fire
            }
        }

        return false;
    },
    change: function (event, ui) {
        const type = $input.data('type');

        if (ui.item) {
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
        } else {
            // If user cleared the field
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
    }).focus(function () {
        $(this).autocomplete('search', '');
    });

    }
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
            source: customerList,
            minLength: 0,
            select: function (event, ui) {
                const type = $input.data('type'); 
                $input.val(ui.item.label);
                $(`.customer-id[data-type="${type}"]`).val(ui.item.id);
                return false;
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
            source: driverList,
            minLength: 0,
            select: function (event, ui) {
                $input.val(ui.item.label);
                $input.closest('div').find('.driver-id').val(ui.item.id);
                return false;
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }
});

    
    //vehicle types
    const vehicleTypes = [
   @if($vehicleTypes->isNotEmpty())
    @foreach($vehicleTypes as $vt)
        {
            label: "{{ $vt->name }} ({{ $vt->capacity }} {{ $vt->unit->name ?? '' }})",
            value: "{{ $vt->name }} ({{ $vt->capacity }} {{ $vt->unit->name ?? '' }})",
            id: {{ $vt->id }}
        }@if(!$loop->last),@endif
    @endforeach
@else
    null
@endif
];

   $(document).on('focus', '.vehicle-type-autocomplete', function () {
    if (!$(this).data('ui-autocomplete')) {
        $(this).autocomplete({
            source: vehicleTypes,
            minLength: 0,
            select: function (event, ui) {
                $(this).val(ui.item.label);
                $(this).closest('tr').find('.vehicle-type-id').val(ui.item.id);
                return false;
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }
});
</script>
<script>
    // Make it globally accessible

    function fetchFreightCharge() {
        const sourceId = $('input[name="source_id"]').val();
        const destinationId = $('input[name="destination_id"]').val();

        if (!sourceId || !destinationId) return;

        $.ajax({
            url: '/freight-charge-details',
            method: 'GET',
            data: {
                source_id: sourceId,
                destination_id: destinationId
            },
            success: function (response) {
                $('#vehicle_type_name').val(response.vehicle_type_name).prop('disabled', true);
                $('.vehicle-type-id').val(response.vehicle_type_id);
                $('#distance').val(response.distance).prop('disabled', true);
                $('#freight_charges').val(response.freight_charges).prop('disabled', true);
                $('#distanceInput').val(response.distance);
                $('#freightCharges').val(response.freight_charges);

                // ✅ Set text content for display
                $('#routeVehicle').text(response.vehicle_type_name);
                $('#routeCapacity').text(response.vehicle_type_capacity + ' ' + response.vehicle_type_unit_name);
                $('#routeSource').text(response.source_name);
                $('#routeDestination').text(response.destination_name);
            },
            error: function () {
                $('#vehicle_type_name, #vehicle_type_id, #distance, #freight_charges').val('').prop('disabled', false);
            }
        });
    }

    $(document).ready(function () {
        $('input[name="source_name"], input[name="destination_name"]').on('blur', function () {
            const sourceId = $('input[name="source_id"]').val();
            const destId = $('input[name="destination_id"]').val();

            if (sourceId && destId) {
                fetchFreightCharge();
            }
        });
    });

    // ✅ This will now work globally:
    $(document).on('change', 'input[name*="[weight]"], input[name*="[no_of_articles]"]', function () {
        updateRouteDetailsUI(); 
        fetchFreightCharge();   
    });
</script>

<script>
$(document).ready(function () {
    $('#locationId').on('change', function () {
        var locationId = $(this).val();

        // Reset the cost center dropdown
        $('#cost_center_id').html('<option value="">Select Cost Center</option>');

        if (locationId) {
            $.ajax({
                url: '/get-cost-centers-by-location/' + locationId, 
                type: 'GET',
                success: function (response) {
                    if (response.success) {
                        $.each(response.data, function (key, center) {
                            $('#cost_center_id').append(
                                `<option value="${center.id}">${center.name}</option>`
                            );
                        });
                    }
                },
                error: function () {
                    alert('Unable to fetch cost centers.');
                }
            });
        }
    });
});


// location on focus
let activeFreePoint = 0;
let fixedAmount = null;
let sourceRouteId = null;
let freeAmount = null;
let globalSourceId = $('#sourceIdInput').val();

let pricingCache = {}

  function checkFreePoint(locationId = null, sourceId = null, $targetRow = null, isEditLoad = false) {
    if (!locationId || !sourceId) return;

    $.ajax({
        url: '/get-location-pricing',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            location_id: locationId,
            source_id: sourceId,
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

            // Set globals
            $('#activeFreePointGlobal').val(pricingCache[locationId].free_point || 0);
            $('#fixedAmountGlobal').val(pricingCache[locationId].amount || 0);
            $('#freeAmountGlobal').val(pricingCache[locationId].freeAmount || 0);

            applyFreightToRows(pricingCache[locationId]); 
        }
    });
}


function applyFreightToRows($specificRow = null, deletedRow = null) {
    const $rows = $('#item-table-body').find('tr');
    let zeroFreightCount = 0;

    $rows.each(function () {
        const freightAmount = parseFloat($(this).find('input[name*="[freight]"]').val()) || 0;
        if (freightAmount === 0) {
            zeroFreightCount++;
        }
    });
    const activeFreePoint = parseInt($('#activeFreePointGlobal').val() || 0);
    const sourceDefaultAmount = parseFloat($('#sourceDefaultAmountGlobal').val() || 0);

    const processRow = ($row, index) => {
        const locationId = $row.find('input[name*="[location_id]"]').val()?.trim();
        const $freightInput = $row.find('input[name*="[freight]"]');

        if (!locationId) return;
       const pricing = pricingCache[locationId];

    if (!pricing) {
        $freightInput.val(sourceDefaultAmount > 0 ? sourceDefaultAmount : '');
        return;
    }


        if (pricing) {
            if (pricing.type === 'both_exist') {
              if (index < activeFreePoint) {
                console.log(`Row index: ${index}, activeFreePoint: ${activeFreePoint}`);

                $freightInput.val(0);
            } else {
                if(pricing.amount){
                  $freightInput.val(pricing.amount && parseFloat(pricing.amount) > 0 ? parseFloat(pricing.amount) : 0);
                }else{
                    $freightInput.val(pricing.freeAmount && parseFloat(pricing.freeAmount) > 0 ? parseFloat(pricing.freeAmount) : 0);
                }
                
            }

            }else if (pricing.type === 'free_point') {
                if (index < activeFreePoint) {
                    $freightInput.val(0);
                } else {
                    $freightInput.val(parseFloat(pricing.freeAmount));
                }
            } else if (pricing.type === 'exists_in_fixed') {
                $freightInput.val(parseFloat(pricing.amount));
            } else {
                // fallback
                $freightInput.val(sourceDefaultAmount > 0 ? sourceDefaultAmount : '');
            }
        } else {
            // No pricing for this location
            $freightInput.val(sourceDefaultAmount > 0 ? sourceDefaultAmount : '');
        }
    };
    if (deletedRow !== null) {
        const $targetRow = $rows.eq(deletedRow);
        const locationId = $targetRow.find('input[name*="[location_id]"]').val()?.trim();
        const $freightInput = $targetRow.find('input[name*="[freight]"]');

        const pricing = pricingCache[locationId]; // assuming pricing was cached

        if (deletedRow <= activeFreePoint) {
            $freightInput.val(0);
        } else {
            $freightInput.val(pricing?.amount && parseFloat(pricing.amount) > 0 ? parseFloat(pricing.amount) : 0); 
        }
    }


    if ($specificRow && $specificRow.length) {
        processRow($specificRow, $specificRow.index());
    } else {
        $rows.each(function (index) {
            processRow($(this), index);
        });
    }

    calculateTotals();
}


function handleLocationUpdate($input) {
    const $row = $input.closest('tr');
    const locationId = $row.find('input[name*="[location_id]"]').val();
    const sourceId = $('#sourceIdInput').val();

    if (locationId && sourceId) {
        checkFreePoint(locationId, sourceId, $row); 
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
    function appendFilePreviews(fileUrl, previewElementId, index, fileId = null) {
    const previewContainer = document.getElementById(previewElementId);
    if (!previewContainer) return;

    const fileName = fileUrl.split('/').pop();

    const previewHtml = `
        <div class="col-4 file-preview-item" data-index="${index}" data-file-id="${fileId ?? ''}">
            <div class="card border">
                <div class="card-body p-2 d-flex justify-content-between align-items-center">
                    <div class="text-truncate small" title="${fileName}">
                        <i class="fa fa-paperclip me-1 text-secondary"></i> ${fileName}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFilePreview(this, '${previewElementId}', '${fileName}')">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    previewContainer.insertAdjacentHTML('beforeend', previewHtml);
}



        function addFiles(element, previewElementId) {
        const input = element;
        const allowedMaxFilesCount = Number(element.getAttribute('max_file_count') ? element.getAttribute('max_file_count') : 1);
        const files = Array.from(input.files); // Convert new FileList to array
        const dt = new DataTransfer();
        const inputId = input.name.replace('[]','');
        // Initialize storage for this input if not already initialized
        if (!fileInputData[inputId]) {
            fileInputData[inputId] = [];
            addedFilesCount = 0;
        } else {
            addedFilesCount = fileInputData[inputId].length;
        }

        if ((files.length + fileInputData[inputId].length) > allowedMaxFilesCount) 
        {
            Swal.fire({
                title: 'Error!',
                text: "Maximum " + allowedMaxFilesCount + " files are allowed",
                icon: 'error',
            });
            let prevAllFiles = fileInputData[inputId] ? fileInputData[inputId] : [];
            let tempDt = new DataTransfer();
            prevAllFiles.forEach((fileElement) => {
                tempDt.items.add(fileElement);
            });
            input.files = tempDt.files;
            return;
        }

        // Combine old and new files
        let allFiles = [...fileInputData[inputId], ...files];
        var invalidFile = {};

        // Validate files
        for (let i = 0; i < allFiles.length; i++) {
            const file = allFiles[i];
            const fileExtension = file.name.split('.').pop().toLowerCase();

            if (!ALLOWED_EXTENSIONS.includes(fileExtension) || !ALLOWED_MIME_TYPES.includes(file.type)) {
                invalidFile.message = 'Please select valid files';
                break;
            }
            const fileSize = (file.size / 1024).toFixed(2);
            if (fileSize > MAX_FILE_SIZE) {
                invalidFile.message = 'Please select files with size not more than 5MB';
                break;
            }
        }

        // Stop if there's an invalid file
        if (invalidFile && invalidFile.message) {
            Swal.fire({
                title: 'Error!',
                text: invalidFile.message,
                icon: 'error',
            });
            element.value = ''; // Reset file input
            return;
        } else {
            // Add all files to DataTransfer and rebuild the preview
            allFiles.forEach((file, i) => {
                dt.items.add(file);
                if (!fileInputData[inputId].some(f => f.name === file.name && f.size === file.size)) {
                    const fileUrl = URL.createObjectURL(file);
                    appendFilePreviews(fileUrl, previewElementId, i);
                }
            });

            // Update the global object for this input
            fileInputData[inputId] = allFiles.reduce((unique, file) => {
                if (!unique.some(f => f.name === file.name && f.size === file.size)) {
                    unique.push(file);
                }
                return unique;
            }, []);

            // Update the file input's FileList
            input.files = dt.files;

            // Reset and re-render SVG icons (if applicable)
            feather.replace({
                width: 20,
                height: 20,
            });
        }
    }
</script>


@endsection

