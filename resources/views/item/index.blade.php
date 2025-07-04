@extends('layouts.app')

@section('content')
<style>
    .itemactive { position: absolute; left: 6px; font-size: 11px; top: 6px; color: #fff } 
    .iteminactive {  left: 24px; color: #999 } 
    .customernewsection-form .statusactiinactive .form-check-input { width: 80px; cursor: pointer}
    .customernewsection-form .statusactiinactive .form-check-input:checked + .itemactive { display: inline-block}
    .customernewsection-form .statusactiinactive .form-check-input:checked ~ .iteminactive { display: none }

    .customernewsection-form .statusactiinactive .form-check-input:not(:checked) + .itemactive { display: none}
    .customernewsection-form .statusactiinactive .form-check-input:not(:checked) ~ .iteminactive { display: inline-block }
</style>
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
								<h2 class="content-header-title float-start mb-0">New Item</h2>
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
                                                
                                                
                                                <div class="col-md-9"> 
                                                    <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Type</label>  
                                                        </div> 

                                                        <div class="col-md-5"> 
                                                            <div class="demo-inline-spacing">
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="customColorRadio1" name="goodsservice" class="form-check-input" checked="">
                                                                    <label class="form-check-label fw-bolder" for="customColorRadio1">Goods</label>
                                                                </div> 
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="service" name="goodsservice" class="form-check-input">
                                                                    <label class="form-check-label fw-bolder" for="service">Service</label>
                                                                </div> 
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Unit</label>  
                                                        </div> 

                                                        <div class="col-md-5">  
                                                            <select class="form-select select2">
                                                                <option>Select</option>
                                                                <option>BOX - box</option>
                                                                <option>CMS - cm</option>
                                                                <option>DOZ - dg</option>
                                                                <option>FTS - ft</option> 
                                                                <option>INC - in</option> 
                                                            </select>  
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="hsn">  

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3"> 
                                                                <label class="form-label">Item Code</label>  
                                                            </div>  

                                                            <div class="col-md-5">  
                                                                <input type="text" class="form-control"  /> 
                                                            </div>
                                                         </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3"> 
                                                                <label class="form-label">Item Name</label>  
                                                            </div>  

                                                            <div class="col-md-5"> 
                                                                <input type="text" class="form-control"  />
                                                            </div> 
                                                         </div> 

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3"> 
                                                                <label class="form-label">HSN</label>  
                                                            </div>  

                                                             <div class="col-md-5"> 
                                                                <select class="form-select select2">
                                                                    <option>Select</option>
                                                                    <option>0802</option>
                                                                    <option>0813</option>
                                                                    <option>08</option>
                                                                    <option>0804</option>
                                                                    <option>0806</option>
                                                                    <option>0906</option>
                                                                    <option>1517</option>
                                                                    <option>7309</option>
                                                                    <option>7310</option>
                                                                    <option>7321</option>
                                                                    <option>7412</option>
                                                                    <option>7416</option>
                                                                </select> 
                                                            </div> 
                                                         </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3"> 
                                                                <label class="form-label">Preferred Vendor</label>  
                                                            </div>  

                                                             <div class="col-md-5"> 
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
                                                            <div class="col-md-3"> 
                                                                <label class="form-label">Category Mapping</label>  
                                                            </div>  

                                                            <div class="col-md-3 pe-sm-0"> 
                                                                <select class="form-select">
                                                                    <option>Catgeory</option> 
                                                                </select> 
                                                            </div>
                                                            <div class="col-md-3"> 
                                                                <select class="form-select">
                                                                    <option>Sub-Category</option> 
                                                                </select>
                                                            </div>   
                                                         </div> 



                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3"> 
                                                                <label class="form-label">Conversion</label>  
                                                            </div>  

                                                            <div class="col-md-3 pe-sm-0">
                                                                <select class="form-select">
                                                                    <option>UOM</option> 
                                                                </select> 
                                                            </div>  
                                                            <div class="col-md-3 pe-sm-0">
                                                                <select class="form-select">
                                                                    <option>Alternate UOM</option> 
                                                                </select> 
                                                            </div>
                                                            <div class="col-md-3">
                                                                <select class="form-select">
                                                                    <option>Conversion Fector</option> 
                                                                </select> 
                                                            </div>
                                                         </div>

                                                       </div>
                                                    
                                                    
                                                    <div class="sac" style="display: none">  

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3"> 
                                                                <label class="form-label">Service Code</label>  
                                                            </div>  

                                                            <div class="col-md-5">  
                                                                <input type="text" class="form-control"  /> 
                                                            </div>
                                                         </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3"> 
                                                                <label class="form-label">Service Name</label>  
                                                            </div>  

                                                            <div class="col-md-5"> 
                                                                <input type="text" class="form-control"  />
                                                            </div> 
                                                         </div> 

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3"> 
                                                                <label class="form-label">SAC</label>  
                                                            </div>  

                                                             <div class="col-md-5"> 
                                                                <select class="form-select select2">
                                                                    <option>Select</option>
                                                                    <option>0802</option>
                                                                    <option>0813</option>
                                                                    <option>08</option>
                                                                    <option>0804</option>
                                                                    <option>0806</option>
                                                                    <option>0906</option>
                                                                    <option>1517</option>
                                                                    <option>7309</option>
                                                                    <option>7310</option>
                                                                    <option>7321</option>
                                                                    <option>7412</option>
                                                                    <option>7416</option>
                                                                </select> 
                                                            </div> 
                                                         </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3"> 
                                                                <label class="form-label">Preferred Vendor</label>  
                                                            </div>  

                                                             <div class="col-md-5"> 
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
                                                            <div class="col-md-3"> 
                                                                <label class="form-label">Category Mapping</label>  
                                                            </div>  

                                                            <div class="col-md-3 pe-sm-0"> 
                                                                <select class="form-select">
                                                                    <option>Catgeory</option> 
                                                                </select> 
                                                            </div>
                                                            <div class="col-md-3"> 
                                                                <select class="form-select">
                                                                    <option>Sub-Category</option> 
                                                                </select>
                                                            </div>   
                                                         </div> 
 

                                                       </div>
                                                     

                                                </div>

                                                <div class="col-md-3 border-start">
                                                    <div class="row align-items-center mb-2">
                                                        <div class="col-md-12"> 
                                                            <label class="form-label text-primary"><strong>Status</strong></label>   
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
                                                        
                                                    <div class="row align-items-center mb-2">
                                                        
                                                        <div class="col-md-12"> 
                                                            <label class="form-label text-primary"><strong>Open for Order</strong></label>   
                                                             <div class="demo-inline-spacing">
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Order1" name="Order" class="form-check-input" checked="">
                                                                    <label class="form-check-label fw-bolder" for="Order1">Yes</label>
                                                                </div> 
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Order2" name="Order" class="form-check-input">
                                                                    <label class="form-check-label fw-bolder" for="Order2">No</label>
                                                                </div> 
                                                            </div> 
                                                        </div> 
                                                        
                                                    </div>
                                                        
                                                        
                                                    <div class="row align-items-center mb-2">
                                                        
                                                        <div class="col-md-12"> 
                                                            <label class="form-label text-primary"><strong>Open for Sale</strong></label>   
                                                             <div class="demo-inline-spacing">
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Yes" name="Sale" class="form-check-input" checked="">
                                                                    <label class="form-check-label fw-bolder" for="Yes">Yes</label>
                                                                </div> 
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="No" name="Sale" class="form-check-input">
                                                                    <label class="form-check-label fw-bolder" for="No">No</label>
                                                                </div> 
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
