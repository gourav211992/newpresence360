@extends('layouts.app')
@section('content')
 <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
					<div class="content-header-left col-md-6  mb-2">
						<div class="row breadcrumbs-top">
							<div class="col-12">
								<h2 class="content-header-title float-start mb-0">New Fixed Charges</h2>
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
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>  
                            <button onClick="javascript: history.go(-1)" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Create</button>  
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
                                                    <div class="newheader  border-bottom mb-2 pb-25"> 
														<h4 class="card-title text-theme">Basic Information</h4>
														<p class="card-text">Fill the details</p> 
													</div>
                                                </div> 
                                                
                                                <div class="col-md-9"> 
                                                     
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3 mb-sm-0 mb-1"> 
                                                            <label class="form-label">Source <span class="text-danger">*</span></label>  
                                                        </div>  
  
                                                        <div class="col-md-3 mb-sm-0 mb-1"> 
                                                            <select name="source_state_id" class="form-control source-state state-select select2" data-type="source">
                                                            <option value="">Select State</option>
                                                            @foreach($states as $state)
                                                                <option value="{{ $state->id }}" >{{ $state->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        </div>
														<div class="col-md-3"> 
                                                            <select name="source_city_id" 
                                                                class="form-control source-city city-select select2" 
                                                                data-selected="">
                                                            <option value="">Select City</option>
                                                        </select>
                                                        </div>
                                                     </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3 mb-sm-0 mb-1"> 
                                                            <label class="form-label">Destination <span class="text-danger">*</span></label>  
                                                        </div>  
  
                                                        <div class="col-md-3 mb-sm-0 mb-1"> 
                                                            <select name="destination_state_id" 
                                                                class="form-control destination-state state-select select2" 
                                                                data-type="destination">
                                                            <option value="">Select State</option>
                                                            @foreach($states as $state)
                                                                <option value="{{ $state->id }}" >{{ $state->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        </div>
														<div class="col-md-3"> 
                                                             <select name="destination_city_id" 
                                                                        class="form-control destination-city city-select select2" 
                                                                        data-selected="" >
                                                                    <option value="">Select City</option>
                                                            </select>
                                                        </div>
                                                     </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Vehicle Type  <span class="text-danger">*</span></label>  
                                                        </div>  
  
                                                        <div class="col-md-5"> 
                                                             <select name="vehicle_type_id[]" class="form-control select2 " multiple>
                                                                <option value="">Select Vehicle Type</option>
                                                                @foreach($vehicleTypes as $vehicleType)
                                                                    <option value="{{ $vehicleType->id }}" >{{ $vehicleType->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div> 
                                                     </div>
													
													<div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Customer </label>  
                                                        </div>  
  
                                                        <div class="col-md-5"> 
                                                             <select name="customer_id" class="form-control mw-100 select2">
                                                                <option value="">Select Customer</option>
                                                                @foreach($customers as $customer)
                                                                    <option value="{{ $customer->id }}">
                                                                        {{ $customer->company_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div> 
                                                     </div>
                                                      
												</div>
                                                
                                                
                                                <div class="col-md-3 border-start">
                                                    <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-12"> 
                                                            <label class="form-label">Status</label>  
                                                        </div> 
                                                        
                                                        <div class="col-md-12"> 
                                                            <div class="demo-inline-spacing">
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="customColorRadio3" name="customColorRadio3" class="form-check-input" checked="">
                                                                    <label class="form-check-label fw-bolder" for="customColorRadio3">Active</label>
                                                                </div> 
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="customColorRadio4" name="customColorRadio3" class="form-check-input">
                                                                    <label class="form-check-label fw-bolder" for="customColorRadio4">Inactive</label>
                                                                </div> 
                                                            </div> 
                                                        </div>
                                                    </div>
                                                
                                                </div>
                                                
                                                <div class="col-md-12">
                                                    <div class="newheader d-flex justify-content-between align-items-end mt-2 border-top pt-2">
                                                        <div class="header-left">
                                                            <h4 class="card-title text-theme">Add Location</h4>
                                                            <p class="card-text">Fill the details</p>
                                                        </div> 
                                                    </div>
                                                    
                                                </div>
                                                
                                                <div class="col-md-8">
                                                    
                                                    <div class="table-responsive-md">
                                                         <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable"> 
                                                            <thead>
                                                                 <tr>
                                                                    <th>#</th>
                                                                    <th width="200">State<span class="text-danger">*</span></th>
                                                                    <th width="200">City <span class="text-danger">*</span></th>
                                                                    <th width="200">Rate <span class="text-danger">*</span></th>
                                                                    <th>Action</th> 
                                                                  </tr>
                                                                </thead>
                                                                <tbody> 
                                                                     <tr>
                                                                        <td>1</td>
                                                                        <td>
                                                                        <select name="state_id" class="form-control source-state state-select select2" data-type="source">
                                                                            <option value="">Select State</option>
                                                                            @foreach($states as $state)
                                                                                <option value="{{ $state->id }}" >{{ $state->name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                      </td>
                                                                        <td>
                                                                    <select name="city_id" class="form-control destination-city city-select select2" data-selected="" >
                                                                            <option value="">Select City</option>
                                                                    </select>
                                                                      </td>
                                                                         <td><input type="text"  name="amount" value="0.00" class="form-control mw-100"></td>
                                                                         <td><a href="#" class="text-primary"><i data-feather="plus-square"></i></a></td> 
                                                                      </tr>
                                                                      
                                                               </tbody>
                                                         </table>
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
@endsection