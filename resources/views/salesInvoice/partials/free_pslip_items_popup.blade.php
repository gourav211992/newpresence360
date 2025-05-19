<div class="modal fade text-start" id="freePslipItems" tabindex="-1" aria-labelledby="header_pull_label" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1250px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="header_pull_label">Select Items</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					 <div class="row">
						 <div class="col-md-12">
							<div class="table-responsive">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail"> 
									<thead>
										 <tr>
											<th>
												<div class="form-check form-check-inline me-0">
													<input class="form-check-input" type="checkbox" id="checkAllPslipItems(this);">
												</div> 
											</th>  
											<th>Item Name</th>
											<th>Item Code</th>
											<th>Attributes</th>
                                            <th>UOM</th>
                                            <th>Location</th>
											<th>Qty</th>
											<th>Rate</th>
											<th>Value</th>
											<th>Balance Qty</th> 
											<th id = "avl_stock_header">Avl Stock</th> 
											<th>Rate</th> 
											<th id = "packing_slip_nos_header">Packing Slips No</th> 
										  </tr>
										</thead>
										<tbody id = "qts_data_table">
                                            
									   </tbody>


								</table>
							</div>
						</div>


					 </div>
				</div>
				<div class="modal-footer text-end">
					<button type = "button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
					<button type = "button" class="btn btn-primary btn-sm" onclick = "processOrder();" data-bs-dismiss="modal"><i data-feather="check-circle"></i> Process</button>
				</div>
			</div>
		</div>
	</div>