<div class="modal fade text-start" id="analyzeModal" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select Quotation</h4>
                    <p class="mb-0">Select from the below list</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                 <div class="row">
                     <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Quotation No. <span class="text-danger">*</span></label>
                           <select class="form-select select2">
                                <option>Select</option> 
                            </select>
                        </div>
                    </div>
                     <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Series <span class="text-danger">*</span></label>
                            <select class="form-select select2">
                                <option>Select</option> 
                            </select>
                        </div>
                    </div>
                      <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <select class="form-select select2">
                                <option>Select</option> 
                            </select>
                        </div>
                    </div>
                     <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Item Name <span class="text-danger">*</span></label>
                            <select class="form-select select2">
                                <option>Select</option> 
                            </select>
                        </div>
                    </div>
                     <div class="col  mb-1">
                          <label class="form-label">&nbsp;</label><br/>
                         <button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                     </div>
                     <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail"> 
                                <thead>
                                     <tr>
                                        <th width="15px">
											<div class="form-check form-check-inline me-0">
												<input class="form-check-input" type="checkbox" name="analyzeDetail" id="analyzeDetail">
											</div> 
										</th> 
                                        <th>Doc Number</th>  
                                        <th>Doc Date</th>  
                                        <th width="180px">Product Name</th>  
                                        <th width="100px">Product Code</th>  
                                        <th>UOM</th>  
                                        <th width="50px">Attribute</th>
                                        <th width="100px">Location</th>
                                        <th width="90px">Total Qty</th>
                                        <th width="90px">Avl Stock</th>
                                      </tr>
                                    </thead>
                                    <tbody id="analyzeDataTable">

                                    </tbody>
                            </table>
                        </div>
                    </div>
                 </div>
            </div>
            <div class="modal-footer text-end">
                <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
                <button class="btn btn-primary btn-sm analyzeProcessBtn"><i data-feather="check-circle"></i> Process</button>
            </div>
        </div>
    </div>
</div>