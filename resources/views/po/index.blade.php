@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
					<div class="content-header-left col-md-6 col-6 mb-2">
						<div class="row breadcrumbs-top">
							<div class="col-12">
								<h2 class="content-header-title float-start mb-0">Purchase Order</h2>
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
					<div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
						<div class="form-group breadcrumb-right">   
							<button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</button>  
							<button class="btn btn-outline-primary btn-sm"><i data-feather='save'></i> Save as Draft</button>  
							<button  class="btn btn-primary btn-sm"><i data-feather="check-circle"></i> Create</button> 
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
                                                    <div class="newheader  border-bottom mb-2 pb-25"> 
														<h4 class="card-title text-theme">Basic Information</h4>
														<p class="card-text">Fill the details</p> 
													</div>
                                                </div> 
                                                
                                                 
											</div>
											  
  
											
											<div class="row">
                                                 <div class="col-md-12">
                                                     
                                                     <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Purchase Order <span class="text-danger">*</span></label>  
                                                        </div> 
                                                        
                                                        <div class="col-md-4">  
                                                            <input type="text" class="form-control" placeholder="PO001" />  
                                                        </div>
                                                    </div>
                                                     
                                                     <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">PO Date</label>  
                                                        </div> 
                                                        
                                                        <div class="col-md-4">  
                                                            <input type="date" class="form-control" />  
                                                        </div> 
                                                    </div>
                                                     
                                                     <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Series</label>  
                                                        </div> 
                                                        
                                                        <div class="col-md-4">  
                                                            <input type="text" class="form-control" />  
                                                        </div> 
                                                    </div>
                                                     
                                                   <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Vendor <span class="text-danger">*</span></label>  
                                                        </div> 

                                                        <div class="col-md-4">  
                                                            <select class="form-select select2">
                                                                <option>Select</option>
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
                                                     
                                                     <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Billing to</label>  
                                                        </div> 
                                                        
                                                        <div class="col-md-4">  
                                                            <select class="form-select">
                                                                <option>Select</option>  
                                                            </select>  
                                                        </div>
                                                         
                                                         <div class="col-md-2"> 
                                                            <label class="form-label">Ship to</label>  
                                                        </div> 
                                                        
                                                        <div class="col-md-4">  
                                                            <select class="form-select">
                                                                <option>Select</option>  
                                                            </select>  
                                                        </div>
                                                    </div>
                                                     
                                                     
                                                     
                                                     <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Reference#</label>  
                                                        </div> 
                                                        
                                                        <div class="col-md-4">  
                                                            <input type="text" class="form-control" />  
                                                        </div>
                                                    </div>
                                                      
                                                    
                                                     <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Payment Terms</label>  
                                                        </div> 
                                                        
                                                        <div class="col-md-4">  
                                                            <select class="form-select">
                                                                <option>Select</option> 
                                                                <option>Due on Receipt</option> 
                                                            </select>  
                                                        </div>
                                                    </div>
                                                     
                                                     <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Currency Type</label>  
                                                        </div> 
                                                        
                                                        <div class="col-md-4">  
                                                            <select class="form-select">
                                                                <option>Select</option> 
                                                                <option selected>INR</option> 
                                                            </select>  
                                                        </div>
                                                    </div>
                                                     
                                                      
                                                     
                                                      
                                                     
                                                     <div class="newheader d-flex justify-content-between align-items-end mt-2 border-top pt-2">
                                                        <div class="header-left">
                                                            <h4 class="card-title text-theme">PO Item Wise Detail</h4>
                                                            <p class="card-text">Fill the details</p>
                                                        </div>
                                                        <div> 
                                                            <a href="#" class="text-primary add-contactpeontxt"><i data-feather='plus'></i> Add New Item</a> 
                                                        </div>
                                                    </div> 
											 
                                                     
                                                     
                                                     
                                                     
                                                     
                                                     
                                                     <div class="table-responsive">
                                                         <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border"> 
                                                            <thead>
                                                                 <tr>
                                                                    <th>#</th>
                                                                    <th width="300px">Item</th>
                                                                    <th width="120px">HSN Code</th>
                                                                    <th>Exp. Delivery Date</th>
                                                                    <th>UOM</th>
                                                                    <th>Qty</th>
                                                                    <th>Rate</th>
                                                                    <th>Basic Value</th>
                                                                    <th>Dis. %</th>
                                                                    <th>Discount Amt</th>
                                                                    <th width="20px">SGST%</th>
                                                                    <th width="20px">CGST%</th>
                                                                    <th width="20px">IGST%</th>
                                                                    <th>Action</th>
                                                                  </tr>
                                                                </thead>
                                                                <tbody>
                                                                     <tr>
                                                                        <td>
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="radio" name="podetail" id="inlineCheckbox1">
                                                                            </div>
                                                                         </td>
                                                                        <td>
                                                                           <select class="form-select mw-100">
                                                                               <option>Select</option> 
                                                                           </select> 
                                                                        </td>
                                                                        <td><input type="text" disabled value="HSN001" class="form-control mw-100" /></td>
                                                                        <td><input type="date" class="form-control mw-100" /></td>
                                                                        <td><input type="text" value="KG" disabled class="form-control mw-100" /></td>
                                                                        <td><input type="text" class="form-control mw-100" /></td>
                                                                         <td><input type="text" class="form-control mw-100" /></td>
                                                                         <td><input type="text" disabled class="form-control mw-100" /></td>
                                                                         <td><input type="text" class="form-control mw-100" /></td>
                                                                         <td><input type="text" disabled class="form-control mw-100" /></td>
                                                                         <td><input type="text" disabled value="6" class="form-control mw-100 min-widthauto" /></td>
                                                                         <td><input type="text" disabled value="6" class="form-control mw-100 min-widthauto" /></td>
                                                                         <td><input type="text" disabled value="-" class="form-control mw-100 min-widthauto" /></td>
                                                                         <td><a href="#" class="text-danger"><i data-feather="trash-2" class="me-50"></i></a></td>
                                                                      </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="radio" name="podetail" id="inlineCheckbox1">
                                                                            </div>
                                                                         </td>
                                                                        <td>
                                                                           <select class="form-select mw-100">
                                                                               <option>Select</option> 
                                                                           </select> 
                                                                        </td>
                                                                        <td><input type="text" disabled value="HSN001" class="form-control mw-100" /></td>
                                                                        <td><input type="date" class="form-control mw-100" /></td>
                                                                        <td><input type="text" value="KG" disabled class="form-control mw-100" /></td>
                                                                        <td><input type="text" class="form-control mw-100" /></td>
                                                                         <td><input type="text" class="form-control mw-100" /></td>
                                                                         <td><input type="text" disabled class="form-control mw-100" /></td>
                                                                         <td><input type="text" class="form-control mw-100" /></td>
                                                                         <td><input type="text" disabled class="form-control mw-100" /></td>
                                                                          <td><input type="text" disabled value="3" class="form-control mw-100 min-widthauto" /></td>
                                                                         <td><input type="text" disabled value="3" class="form-control mw-100 min-widthauto" /></td>
                                                                         <td><input type="text" disabled value="-" class="form-control mw-100 min-widthauto" /></td>
                                                                         <td><a href="#" class="text-danger"><i data-feather="trash-2" class="me-50"></i></a></td>
                                                                      </tr>
                                                                    
                                                                    <tr>
                                                                        <td colspan="7">
                                                                            <label class="form-label">Item Remarks</label>
                                                                            <input type="text" class="form-control mw-100" />
                                                                        </td> 
                                                                        <td class="text-end totalcustomer-sub-head"><strong>Sub Total</strong></td> 
                                                                        <td class="text-end"></td> 
                                                                         
                                                                        <td>0.00</td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td> 
                                                                        <td></td> 
                                                                    </tr>
                                                                    
                                                                    <tr> 
                                                                        <td colspan="8" class="text-end totalcustomer-sub-head"><strong>SGST Amt</strong></td> 
                                                                        <td class="text-end"></td>  
                                                                        <td>0.00</td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td> 
                                                                        <td></td> 
                                                                    </tr>
                                                                     <tr> 
                                                                        <td colspan="8" class="text-end totalcustomer-sub-head"><strong>CGST amt</strong></td> 
                                                                        <td class="text-end"></td>  
                                                                        <td>0.00</td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td> 
                                                                        <td></td> 
                                                                    </tr>
                                                                     <tr> 
                                                                        <td colspan="8" class="text-end totalcustomer-sub-head"><strong>IGST Amt</strong></td> 
                                                                        <td class="text-end"></td>  
                                                                        <td>0.00</td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td> 
                                                                        <td></td> 
                                                                    </tr>
                                                                      
                                                                    <tr> 
                                                                        <td colspan="8" class="text-end totalcustomer-sub-head"><strong>Discount</strong></td> 
                                                                        <td>
                                                                            <select class="form-select">
                                                                               <option>Select</option> 
                                                                           </select>
                                                                        </td> 
                                                                         
                                                                        <td>0.00</td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td> 
                                                                        <td></td> 
                                                                    </tr>
                                                                    
                                                                    <tr> 
                                                                        <td colspan="8" class="text-end totalcustomer-sub-head"><strong>Other Expenses</strong></td> 
                                                                        <td>
                                                                            <input type="text" class="form-control mw-100" />
                                                                        </td> 
                                                                         
                                                                        <td>0.00</td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td> 
                                                                        <td></td> 
                                                                    </tr>
                                                                    
                                                                    <tr> 
                                                                        <td colspan="8" class="text-end totalcustomer-sub-head"><strong>Other Expenses</strong></td> 
                                                                        <td>
                                                                            <input type="text" class="form-control mw-100" />
                                                                        </td> 
                                                                         
                                                                        <td>0.00</td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td> 
                                                                        <td></td> 
                                                                    </tr>
                                                                     
                                                                    <tr class="voucher-tab-foot">
                                                                        <td colspan="8" class="text-end text-primary"><strong>Total Amt.</strong></td> 
                                                                        <td class="text-end"></td>  
                                                                        <td>
                                                                            <div class="quottotal-bg"> 
                                                                                <h5>0.00</h5>
                                                                            </div>
                                                                        </td>
                                                                        <td></td>
                                                                        <td></td>
                                                                        <td></td> 
                                                                        <td></td>  
                                                                    </tr>


                                                               </tbody>


                                                        </table>
                                                    </div>
                                                     
                                                     
                                                     <div class="row mt-2">
                                                         
                                                         
                                                        <div class="col-md-12">
                                                             <div class="col-md-4">
                                                                <div class="mb-1">
                                                                    <label class="form-label">Upload Document <span class="text-danger">*</span></label>
                                                                    <input type="file" class="form-control">
                                                                </div>
                                                            </div> 
                                                     </div>
                                                         


                                                        <div class="col-md-12">
                                                            <div class="mb-1">  
                                                                <label class="form-label">Final Remarks <span class="text-danger">*</span></label> 
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
@endsection
