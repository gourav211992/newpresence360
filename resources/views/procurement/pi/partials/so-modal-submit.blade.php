<div class="modal fade text-start" id="soSubmitModal" tabindex="-1" aria-labelledby="soModal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
		<div class="modal-content">
			<div class="modal-header">
				<div>
					<h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="soModal">Select Item</h4>
					<p class="mb-0">Select from the below list</p>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col">
						<div class="mb-1">
							<label class="form-label">Item Name / Code</label>
							<input type="text" id="search_filter" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type = "hidden" id = "item_id_qt_val">
						</div>
					</div>
					<div class="col-md-12">
						<div class="table-responsive" style="overflow-y: auto;max-height: 350px;">
							<table class="mt-1 table myrequesttablecbox table-striped po-order-detail"> 
								<thead>
									<tr>
										<th>
											<div class="form-check form-check-inline me-0">
												<input class="form-check-input" type="checkbox" name="podetail" id="inlineCheckbox1">
											</div> 
										</th>  
										<th id="soTrackingNo" class="d-none">SO#</th>
										<th>Item Code</th>
										<th>Item Name</th>
										<th>Attributes</th>
										<th>UOM</th>
										<th>Quantity</th>
										<th>Confirmed Stock</th>
										<th>Unconfirmed Stock</th>
										<th>Vendor</th>
										{{-- <th>Pending PO</th>  --}}
									</tr>
								</thead>
								<tbody id="soSubmitDataTable">
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer text-end">
				<button type = "button" class="btn btn-outline-secondary btn-sm" id="backBtn"> Back</button>
				<button type = "button" class="btn btn-primary btn-sm soSubmitProcess"><i data-feather="check-circle"></i> Process</button>
			</div>
		</div>
	</div>
</div>