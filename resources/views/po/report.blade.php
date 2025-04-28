@extends('layouts.app')

@section('content')
<!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            
             
            <div class="content-body"> 
                  
                <section id="basic-datatable">
                    <div class="card border  overflow-hidden"> 
                        <div class="row">
                            <div class="col-md-12 bg-light border-bottom mb-1 po-reportfileterBox">
                                <div class="row pofilterhead action-button align-items-center">
                                    <div class="col-md-4">
                                        <h3>PO Report</h3>
                                        <p>Apply the Basic Filter</p>
                                    </div>
                                    <div class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                        <div class="customernewsection-form">
                                            <div class="demo-inline-spacing">
                                                <div class="form-check form-check-primary mt-0">
                                                    <input type="radio" id="customColorRadio1" name="goodsservice" class="form-check-input" checked="">
                                                    <label class="form-check-label fw-bolder" for="customColorRadio1">Goods</label>
                                                </div> 
                                                <div class="form-check form-check-primary mt-0">
                                                    <input type="radio" id="service" name="goodsservice" class="form-check-input">
                                                    <label class="form-check-label fw-bolder" for="service">Service</label>
                                                </div> 
                                            </div>
                                        </div>
                                        <div class="btn-group new-btn-group my-1 my-sm-0 ps-0">
                                            <input type="radio" class="btn-check" name="Peroid" id="Current" checked />
                                            <label class="btn btn-outline-primary mb-0" for="Current">Current Month</label>

                                            <input type="radio" class="btn-check" name="Peroid" id="Last" />
                                            <label class="btn btn-outline-primary mb-0" for="Last">Last Month</label> 

                                            <input type="radio" class="btn-check" name="Peroid" id="Custom" />
                                            <label class="btn btn-outline-primary mb-0" for="Custom">Custom</label> 
                                        </div>
<!--                                        <button data-bs-toggle="modal" data-bs-target="#addcoulmn" class="btn btn-outline-primary btn-sm columnfilterbtn me-1"><i data-feather="plus-square"></i> Add Columns</button>-->
                                        <button data-bs-toggle="modal" data-bs-target="#addcoulmn" class="btn btn-primary btn-sm mb-0 waves-effect"><i data-feather="filter"></i> Advance Filter</button>
                                    </div>
                                </div>
                                
                                <div class="customernewsection-form poreportlistview p-1">
                                    <div class="row"> 
                                        <div class="col-md-2">
                                            <div class="mb-1 mb-sm-0"> 
                                                <label class="form-label">Category</label>
                                                <select class="form-select select2">
                                                    <option>Select</option> 
                                                </select> 
                                             </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-1 mb-sm-0"> 
                                                <label class="form-label">Sub-Type</label>
                                                <select class="form-select">
                                                    <option>Select</option>
                                                    <option>Raw Material</option>
                                                    <option>Semi Finished</option>
                                                    <option>Finished Goods</option>
                                                    <option>Traded Item</option>
                                                    <option>Asset</option>
                                                    <option>Expense</option>
                                                </select> 
                                             </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1 mb-sm-0"> 
                                                <label class="form-label">Item</label>
                                                <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  />
                                             </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-1 mb-sm-0"> 
                                                <label class="form-label">Status</label>
                                                <select class="form-select">
                                                    <option>Select</option>
                                                    <option selected>Outstanding</option>
                                                    <option>Completed</option>
                                                    <option>Short Closed</option>
                                                    <option>Amendment</option>
                                                </select> 
                                             </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1 mb-sm-0"> 
                                                <label class="form-label">Select Vendor</label>
                                                <select class="form-select select2"> 
                                                    <option>Indian Oil Corporation Ltd.</option>
                                                    <option>Airports Authority of India</option>
                                                    <option>Bharat Heavy Electricals Ltd.</option>
                                                    <option>Bharat Petroleum Corpn. Ltd.</option>
                                                    <option>NTPC Ltd.</option>
                                                    <option>Gail (India) Ltd.</option>
                                                    <option>Hindustan Petroleum Corpn. Ltd.</option>
                                                    <option>Steel Authority of India Ltd.</option>
                                                    <option>Indian Railway Stations Devpt. Corporation Ltd.</option>
                                                    <option>Oil & Natural Gas Corporation Ltd.</option>
                                                    <option>Oil & Natural Gas Corporation Ltd.</option>
                                                    <option>Hindustan Aeronautics Ltd.</option>
                                                </select>
                                             </div>
                                        </div>
                                    </div>
                                       
                                    
                                    
                                </div> 
                            </div>
                          <div class="col-md-12"> 
                                <div class="table-responsive trailbalnewdesfinance po-reportnewdesign">
									<table class="datatables-basic table myrequesttablecbox"> 
                                        <thead>
                                             <tr>
												<th>#</th>
												<th>PO No.</th>
												<th>PO Date</th>
												<th>VEndor</th>
												<th>Items</th>
												<th>PO Qty</th>
												<th>Rec Qty</th>
												<th>Bal Qty</th>
												<th>Rate</th>
												<th>Status</th>
												<th>Action</th>
											  </tr>
											</thead>
											<tbody>
												 <tr>
													<td>1</td>
													<td class="fw-bolder text-dark">PO001/2024/001</td>
													<td>30-07-2024</td>
													<td>
                                                        <div data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="L & T Infotech Pvt ltd">
                                                                L & T Infotech Pvt ltd
                                                        </div> 
                                                     </td>
													<td>Furniture (001)</td>
													<td>50</td>
													<td>30</td>
													<td>20</td>
                                                    <td>200000</td>
                                                    <td><span class="badge rounded-pill badge-light-success badgeborder-radius">Close</span></td>
                                                    <td><a href="#"><i class="cursor-pointer" data-feather='eye'></i></a></td>
												  </tr>
                                                
                                                  <tr>
													<td>2</td>
													<td class="fw-bolder text-dark">PO001/2024/001</td>
													<td>30-07-2024</td>
													<td>
                                                        <div data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="L & T Infotech Pvt ltd">
                                                                L & T Infotech Pvt ltd
                                                        </div> 
                                                     </td>
													<td>Furniture (001)</td>
													<td>50</td>
													<td>30</td>
													<td>20</td>
													<td>200000</td>
                                                    <td><span class="badge rounded-pill badge-light-success badgeborder-radius">Close</span></td>
                                                    <td><a href="#"><i class="cursor-pointer" data-feather='eye'></i></a></td>
												  </tr>
                                                
                                                  <tr>
													<td>3</td>
													<td class="fw-bolder text-dark">PO001/2024/001</td>
													<td>30-07-2024</td>
													<td>
                                                        <div data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="L & T Infotech Pvt ltd">
                                                                L & T Infotech Pvt ltd
                                                        </div> 
                                                     </td>
													<td>Furniture (001)</td>
													<td>50</td>
													<td>30</td>
													<td>20</td>
													<td>200000</td>
                                                    <td><span class="badge rounded-pill badge-light-success badgeborder-radius">Close</span></td>
                                                    <td><a href="#"><i class="cursor-pointer" data-feather='eye'></i></a></td>
												  </tr>
                                                
                                                  <tr>
													<td>4</td>
													<td class="fw-bolder text-dark">PO001/2024/001</td>
													<td>30-07-2024</td>
													<td>
                                                        <div data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="L & T Infotech Pvt ltd">
                                                                L & T Infotech Pvt ltd
                                                        </div> 
                                                     </td>
													<td>Furniture (001)</td>
													<td>50</td>
													<td>30</td>
													<td>20</td>
													<td>200000</td>
                                                    <td><span class="badge rounded-pill badge-light-success badgeborder-radius">Close</span></td>
                                                    <td><a href="#"><i class="cursor-pointer" data-feather='eye'></i></a></td>
												  </tr>
                                                
                                                  <tr>
													<td>5</td>
													<td class="fw-bolder text-dark">PO001/2024/001</td>
													<td>30-07-2024</td>
													<td>
                                                        <div data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="L & T Infotech Pvt ltd">
                                                                L & T Infotech Pvt ltd
                                                        </div> 
                                                     </td>
													<td>Furniture (001)</td>
													<td>50</td>
													<td>30</td>
													<td>20</td>
													<td>200000</td>
                                                    <td><span class="badge rounded-pill badge-light-success badgeborder-radius">Close</span></td>
                                                    <td><a href="#"><i class="cursor-pointer" data-feather='eye'></i></a></td>
												  </tr>
                                                
                                                  <tr>
													<td>6</td>
													<td class="fw-bolder text-dark">PO001/2024/001</td>
													<td>30-07-2024</td>
													<td>
                                                        <div data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="L & T Infotech Pvt ltd">
                                                                L & T Infotech Pvt ltd
                                                        </div> 
                                                     </td>
													<td>Furniture (001)</td>
													<td>50</td>
													<td>30</td>
													<td>20</td>
													<td>200000</td>
                                                    <td><span class="badge rounded-pill badge-light-success badgeborder-radius">Close</span></td>
                                                    <td><a href="#"><i class="cursor-pointer" data-feather='eye'></i></a></td>
												  </tr>
                                                <tr>
													<td>7</td>
													<td class="fw-bolder text-dark">PO001/2024/001</td>
													<td>30-07-2024</td>
													<td>
                                                        <div data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="L & T Infotech Pvt ltd">
                                                                L & T Infotech Pvt ltd
                                                        </div> 
                                                     </td>
													<td>Furniture (001)</td>
													<td>50</td>
													<td>30</td>
													<td>20</td>
													<td>200000</td>
                                                    <td><span class="badge rounded-pill badge-light-success badgeborder-radius">Close</span></td>
                                                    <td><a href="#"><i class="cursor-pointer" data-feather='eye'></i></a></td>
												  </tr>
                                                <tr>
													<td>8</td>
													<td class="fw-bolder text-dark">PO001/2024/001</td>
													<td>30-07-2024</td>
													<td>
                                                        <div data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="L & T Infotech Pvt ltd">
                                                                L & T Infotech Pvt ltd
                                                        </div> 
                                                     </td>
													<td>Furniture (001)</td>
													<td>50</td>
													<td>30</td>
													<td>20</td>
													<td>200000</td>
                                                    <td><span class="badge rounded-pill badge-light-success badgeborder-radius">Close</span></td>
                                                    <td><a href="#"><i class="cursor-pointer" data-feather='eye'></i></a></td>
												  </tr>
                                                <tr>
													<td>9</td>
													<td class="fw-bolder text-dark">PO001/2024/001</td>
													<td>30-07-2024</td>
													<td>
                                                        <div data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="L & T Infotech Pvt ltd">
                                                                L & T Infotech Pvt ltd
                                                        </div> 
                                                     </td>
													<td>Furniture (001)</td>
													<td>50</td>
													<td>30</td>
													<td>20</td>
													<td>200000</td>
                                                    <td><span class="badge rounded-pill badge-light-success badgeborder-radius">Close</span></td>
                                                    <td><a href="#"><i class="cursor-pointer" data-feather='eye'></i></a></td>
												  </tr>
                                                <tr>
													<td>10</td>
													<td class="fw-bolder text-dark">PO001/2024/001</td>
													<td>30-07-2024</td>
													<td>
                                                        <div data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="L & T Infotech Pvt ltd">
                                                                L & T Infotech Pvt ltd
                                                        </div> 
                                                     </td>
													<td>Furniture (001)</td>
													<td>50</td>
													<td>30</td>
													<td>20</td>
													<td>200000</td>
                                                    <td><span class="badge rounded-pill badge-light-success badgeborder-radius">Close</span></td>
                                                    <td><a href="#"><i class="cursor-pointer" data-feather='eye'></i></a></td>
												  </tr>
											   </tbody>


									</table>
						    </div> 
                            </div>
                        </div>
                    </div>
                     
                </section>
                <!-- ChartJS section end -->

            </div>
        </div>
    </div>
    <!-- END: Content-->






    <div class="modal fade text-start filterpopuplabel " id="addcoulmn" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<div>
							<h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Advance Filter</h4>
						</div>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						
<!--
								<div class="row"> 
									<div class="col-md-7 mt-1">
										<div class="form-check form-check-success mb-1">
											<input type="checkbox" class="form-check-input" id="colorCheck1" checked="">
											<label class="form-check-label fw-bolder text-dark" for="colorCheck1">All Columns</label>
										</div>
									</div>
								</div>
-->
                        
                                <div class="step-custhomapp bg-light">
                                    <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#Employee" role="tab" ><i data-feather="columns"></i> Columns</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#Bank" role="tab" ><i data-feather="bar-chart"></i> More Filter</a>
                                        </li>
 
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#Location" role="tab" ><i data-feather="calendar"></i> Scheduler</a>
                                        </li> 
 
                                    </ul>
                                </div>

                                <div class="tab-content tablecomponentreport">
                                    <div class="tab-pane active" id="Employee">
                                        <div class="compoenentboxreport">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-check form-check-primary">
                                                        <input type="checkbox" class="form-check-input" id="colorCheck1" checked="">
                                                        <label class="form-check-label" for="colorCheck1">Select All Columns</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row sortable">
                                                <div class="col-md-4">
                                                    <div class="form-check form-check-secondary">
                                                        <input type="checkbox" class="form-check-input" id="colorCheck1" checked="">
                                                        <label class="form-check-label" for="colorCheck1">PO NO</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-check-secondary">
                                                        <input type="checkbox" class="form-check-input" id="colorCheck1" checked="">
                                                        <label class="form-check-label" for="colorCheck1">PO Date</label>
                                                    </div>
                                                </div> 
                                                <div class="col-md-4">
                                                    <div class="form-check form-check-secondary">
                                                        <input type="checkbox" class="form-check-input" id="colorCheck1" checked="">
                                                        <label class="form-check-label" for="colorCheck1">Vendor</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-check-secondary">
                                                        <input type="checkbox" class="form-check-input" id="colorCheck1" checked="">
                                                        <label class="form-check-label" for="colorCheck1">Vendor Rating</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-check-secondary">
                                                        <input type="checkbox" class="form-check-input" id="colorCheck1" checked="">
                                                        <label class="form-check-label" for="colorCheck1">Category</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-check-secondary">
                                                        <input type="checkbox" class="form-check-input" id="colorCheck1" checked="">
                                                        <label class="form-check-label" for="colorCheck1">Sub Category</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-check-secondary">
                                                        <input type="checkbox" class="form-check-input" id="colorCheck1" checked="">
                                                        <label class="form-check-label" for="colorCheck1">Item Type</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-check-secondary">
                                                        <input type="checkbox" class="form-check-input" id="colorCheck1" checked="">
                                                        <label class="form-check-label" for="colorCheck1">Sub Type</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-check-secondary">
                                                        <input type="checkbox" class="form-check-input" id="Item" checked="">
                                                        <label class="form-check-label" for="Item">Item</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-check-secondary">
                                                        <input type="checkbox" class="form-check-input" id="Status" checked="">
                                                        <label class="form-check-label" for="Status">Status</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-check-secondary">
                                                        <input type="checkbox" class="form-check-input" id="Amount" checked="">
                                                        <label class="form-check-label" for="Amount">PO Amount</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check form-check-secondary">
                                                        <input type="checkbox" class="form-check-input" id="colorCheck1" checked="">
                                                        <label class="form-check-label" for="colorCheck1">Sub Type</label>
                                                    </div>
                                                </div>
                                            </div>

                                        </div> 
                                    </div>
                                    <div class="tab-pane" id="Bank">
                                         <div class="compoenentboxreport advanced-filterpopup customernewsection-form">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-check ps-0"> 
                                                        <label class="form-check-label">Add Filter</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4"> 
                                                    <label class="form-label">Select Category</label>
                                                    <select class="form-select select2"> 
                                                        <option>Select</option>
                                                    </select> 
                                                </div>
                                                <div class="col-md-4"> 
                                                    <label class="form-label">Select Sub-Category</label>
                                                    <select class="form-select select2"> 
                                                        <option>Select</option>
                                                    </select> 
                                                </div>
 

                                                <div class="col-md-4"> 
                                                    <label class="form-label">Select Attribute</label>
                                                    <select class="form-select select2"> 
                                                        <option>Select</option>
                                                    </select> 
                                                </div>
                                                <div class="col-md-4"> 
                                                    <label class="form-label">Select Attribute Value</label>
                                                    <select class="form-select select2"> 
                                                        <option>Select</option>
                                                    </select> 
                                                </div>


                                            </div>

                                        </div>
                                    </div>
                                    <div class="tab-pane" id="Location"> 
                                        <div class="row">
                                            <div class="col-md-12">
                                                 <div class="compoenentboxreport advanced-filterpopup customernewsection-form mb-1">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-check ps-0"> 
                                                                <label class="form-check-label">Add Scheduler</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row camparboxnewcen">
                                                        <div class="col-md-8"> 
                                                            <label class="form-label">To</label>
                                                            <select class="form-select select2" multiple> 
                                                                <option>Select</option>
                                                                <option>Pawan Kuamr</option>
                                                                <option>Deepak Singh</option>
                                                            </select> 
                                                        </div>
                                                     </div>
                                                     <div class="row camparboxnewcen">
                                                        <div class="col-md-4"> 
                                                            <label class="form-label">Type</label>
                                                            <select class="form-select"> 
                                                                <option>Select</option>
                                                                <option>Daily</option>
                                                                <option>Weekly</option>
                                                                <option>Monthly</option>
                                                            </select> 
                                                        </div>

                                                        <div class="col-md-4"> 
                                                            <label class="form-label">Select Date</label>
                                                            <input type="datetime-local" class="form-select" />
                                                        </div>

                                                        <div class="col-md-12"> 
                                                            <label class="form-label">Remarks</label>
                                                            <textarea class="form-control" placeholder="Enter Remarks"></textarea>
                                                        </div>




                                                    </div>

                                                </div>
                                             </div>


                                         </div>
                                          
                                    </div> 
                                </div>
									 
					</div> 
					
					<div class="modal-footer "> 
						<button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
						<button type="button" class="btn btn-primary data-submit mr-1">Apply</button>
					</div>
				</div>
			</div>
		</div>

    
@endsection
@section('scripts')

@endsection
