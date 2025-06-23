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
								<h2 class="content-header-title float-start mb-0">Multi Point Pricing</h2>
								<div class="breadcrumb-wrapper">
									<ol class="breadcrumb">
										<li class="breadcrumb-item"><a href="index.html">Home</a>
										</li>  
										<li class="breadcrumb-item active">Master</li>


									</ol>
								</div>
							</div>
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
									 
									 		<div>
                                                <div class="step-custhomapp bg-light"> 
                                                    <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                                        <li class="nav-item">
                                                            <a class="nav-link active" data-bs-toggle="tab" href="#Fixed">Fixed</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Point">Point</a>
                                                        </li>
                                                    </ul>
                                                </div>
												 <div class="tab-content pb-1">
														<div class="tab-pane active" id="Fixed">
															<div class="text-end mb-50">
																<a href="add-fixed-point.html" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="plus-circle"></i> Add New</a>
															</div>
															<div class="row">
																 <div class="col-md-12"> 

																	 <div class="table-responsive-md">
																		 <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
																			<thead>
																				 <tr>
																					<th>#</th>
																					<th>Source</th>
																					<th>Destination</th>
																					<th>Customer</th>  
																					<th>Locations</th>  
																					<th>Action</th>  
																				  </tr>
																				</thead>
																				<tbody>
																					 <tr>
																						 <td>1</td>
																						 <td>Greator Noida (Uttar Pradesh)</td>
																						 <td>Haridwar (Uttrakhand)</td>
																						 <td>-</td>
																						 <td class="poprod-decpt">
																							<span class="badge rounded-pill badge-light-primary">Roorkee: 1000.00</span>
																							<span class="badge rounded-pill badge-light-primary">Meerut: 750.00</span>
																							 <span class="badge rounded-pill badge-light-primary">+3</span>
																						 </td>
																						 <td><a href="#" class="text-dark"><i data-feather="edit"></i></a></td>
																					  </tr>
																					   
																					<tr>
																						 <td>2</td>
																						 <td>Greator Noida (Uttar Pradesh)</td>
																						 <td>Haridwar (Uttrakhand)</td>
																						 <td>Sheelafoam Ltd</td>
																						 <td class="poprod-decpt">
																							<span class="badge rounded-pill badge-light-primary">Roorkee: 1000.00</span>
																							<span class="badge rounded-pill badge-light-primary">Meerut: 750.00</span>
																							 <span class="badge rounded-pill badge-light-primary">+3</span>
																						 </td>
																						 <td><a href="#" class="text-dark"><i data-feather="edit"></i></a></td>
																					  </tr>
																					
																					<tr>
																						 <td>3</td>
																						 <td>Greator Noida (Uttar Pradesh)</td>
																						 <td>Haridwar (Uttrakhand)</td>
																						 <td>Staqo World Pvt Ltd</td>
																						 <td class="poprod-decpt">
																							<span class="badge rounded-pill badge-light-primary">Roorkee: 1000.00</span>
																							<span class="badge rounded-pill badge-light-primary">Meerut: 750.00</span>
																							 <span class="badge rounded-pill badge-light-primary">+3</span>
																						 </td>
																						 <td><a href="#" class="text-dark"><i data-feather="edit"></i></a></td>
																					  </tr>
																					
																					<tr>
																						 <td>4</td>
																						 <td>Greator Noida (Uttar Pradesh)</td>
																						 <td>Haridwar (Uttrakhand)</td>
																						 <td>-</td>
																						 <td class="poprod-decpt">
																							<span class="badge rounded-pill badge-light-primary">Roorkee: 1000.00</span>
																							<span class="badge rounded-pill badge-light-primary">Meerut: 750.00</span>
																							 <span class="badge rounded-pill badge-light-primary">+3</span>
																						 </td>
																						 <td><a href="#" class="text-dark"><i data-feather="edit"></i></a></td>
																					  </tr>

																			 </tbody>




																		</table>
																	</div>  

																</div>


															 </div>
													 	</div>
													 	<div class="tab-pane" id="Point">
															<div class="text-end mb-50">
																<button class="btn btn-outline-danger btn-sm mb-50 mb-sm-0"><i data-feather="x-circle"></i> Delete</button>
																<button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"><i data-feather="plus-square"></i> Add New</button>
																<button class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Save</button>
															</div>
															<div class="row">
																 <div class="col-md-12"> 

																	 <div class="table-responsive-md">
																		 <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
																			<thead>
																				 <tr>
																					<th width="50px" class="customernewsection-form">
																						<div class="form-check form-check-primary custom-checkbox">
																							<input type="checkbox" class="form-check-input" id="Email">
																							<label class="form-check-label" for="Email"></label>
																						</div> 
																					</th>
																					<th colspan="2">Source <span class="text-danger">*</span></th>
																					<th width="100px">Free Point <span class="text-danger">*</span></th>
																					<th width="150px">Rate <span class="text-danger">*</span></th>
																					<th width="250px">Customer</th>  
																				  </tr>
																				</thead>
																				<tbody class="mrntableselectexcel">
																					 <tr>
																						 <td>
																							<div class="form-check form-check-primary custom-checkbox">
																								<input type="checkbox" class="form-check-input" id="Email">
																								<label class="form-check-label" for="Email"></label>
																							</div> 
																						 </td>
																						 <td width="150px">
																							 <input type="text" placeholder="Select State" class="form-control mw-100 ledgerselecct"  />  
																						 </td>
																						 <td width="150px">
																							 <input type="text" placeholder="Select City" class="form-control mw-100 ledgerselecct"  />  
																						 </td>
																						 <td><input type="text" placeholder="Enter Free Points" class="form-control mw-100"  /></td>
																						 <td><input type="text" placeholder="Enter Rate" class="form-control mw-100"  /></td>
																					    <td>
																						    <input type="text" placeholder="Select Customer" class="form-control mw-100 ledgerselecct"  />
																						 </td>
																					  </tr>
																					<tr>
																						 <td>
																							<div class="form-check form-check-primary custom-checkbox">
																								<input type="checkbox" class="form-check-input" id="Email">
																								<label class="form-check-label" for="Email"></label>
																							</div> 
																						 </td>
																						 <td width="150px">
																							 <input type="text" placeholder="Select State" class="form-control mw-100 ledgerselecct"  />  
																						 </td>
																						 <td width="150px">
																							 <input type="text" placeholder="Select City" class="form-control mw-100 ledgerselecct"  />  
																						 </td>
																						 <td><input type="text" placeholder="Enter Free Points" class="form-control mw-100"  /></td>
																						 <td><input type="text" placeholder="Enter Rate" class="form-control mw-100"  /></td>
																					    <td>
																						    <input type="text" placeholder="Select Customer" class="form-control mw-100 ledgerselecct"  />
																						 </td>
																					  </tr>
																					<tr>
																						 <td>
																							<div class="form-check form-check-primary custom-checkbox">
																								<input type="checkbox" class="form-check-input" id="Email">
																								<label class="form-check-label" for="Email"></label>
																							</div> 
																						 </td>
																						 <td width="150px">
																							 <input type="text" placeholder="Select State" class="form-control mw-100 ledgerselecct"  />  
																						 </td>
																						 <td width="150px">
																							 <input type="text" placeholder="Select City" class="form-control mw-100 ledgerselecct"  />  
																						 </td>
																						 <td><input type="text" placeholder="Enter Free Points" class="form-control mw-100"  /></td>
																						 <td><input type="text" placeholder="Enter Rate" class="form-control mw-100"  /></td>
																					    <td>
																						    <input type="text" placeholder="Select Customer" class="form-control mw-100 ledgerselecct"  />
																						 </td>
																					  </tr>
																					<tr>
																						 <td>
																							<div class="form-check form-check-primary custom-checkbox">
																								<input type="checkbox" class="form-check-input" id="Email">
																								<label class="form-check-label" for="Email"></label>
																							</div> 
																						 </td>
																						 <td width="150px">
																							 <input type="text" placeholder="Select State" class="form-control mw-100 ledgerselecct"  />  
																						 </td>
																						 <td width="150px">
																							 <input type="text" placeholder="Select City" class="form-control mw-100 ledgerselecct"  />  
																						 </td>
																						 <td><input type="text" placeholder="Enter Free Points" class="form-control mw-100"  /></td>
																						 <td><input type="text" placeholder="Enter Rate" class="form-control mw-100"  /></td>
																					    <td>
																						    <input type="text" placeholder="Select Customer" class="form-control mw-100 ledgerselecct"  />
																						 </td>
																					  </tr>
																					<tr>
																						 <td>
																							<div class="form-check form-check-primary custom-checkbox">
																								<input type="checkbox" class="form-check-input" id="Email">
																								<label class="form-check-label" for="Email"></label>
																							</div> 
																						 </td>
																						 <td width="150px">
																							 <input type="text" placeholder="Select State" class="form-control mw-100 ledgerselecct"  />  
																						 </td>
																						 <td width="150px">
																							 <input type="text" placeholder="Select City" class="form-control mw-100 ledgerselecct"  />  
																						 </td>
																						 <td><input type="text" placeholder="Enter Free Points" class="form-control mw-100"  /></td>
																						 <td><input type="text" placeholder="Enter Rate" class="form-control mw-100"  /></td>
																					    <td>
																						    <input type="text" placeholder="Select Customer" class="form-control mw-100 ledgerselecct"  />
																						 </td>
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
                        </div>
                    </div>
                    <!-- Modal to add new record -->
                     
                </section>
                 

            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection