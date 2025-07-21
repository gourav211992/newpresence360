<div class="modal fade text-start" id="prModal" tabindex="-1" aria-labelledby="prModal" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header d-flex justify-content-between align-items-start">
				<div>
					<h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="prModal">Select Purchase Indent</h4>
					<p class="mb-0">Select from the below list</p>
				</div>
				<div class="d-flex align-items-start gap-2">
					<button type="button" class="btn btn-primary btn-sm prProcess">
						<i data-feather="check-circle"></i> Process
					</button>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
			</div>			
			<div class="modal-body">
				<div class="row">
					<div class="col">
						<div class="mb-1">
							<label class="form-label">Vendor</label>
							<input type="text" id="vendor_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type="hidden" id="vendor_id_qt_val"></input>
						</div>
					</div>
					<div class="col">
						<div class="mb-1">
							<label class="form-label">Indent No.</label>
							<input type="text" id="document_no_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type="hidden" id="document_id_qt_val"></input>
						</div>
					</div>
					<div class="col">
						<div class="mb-1">
							<label class="form-label">Sales Order</label>
							<input type="text" id="pi_so_no_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type="hidden" id="pi_so_qt_val"></input>
						</div>
					</div>
					<div class="col">
						<div class="mb-1">
							<label class="form-label">Item</label>
							<input type="text" name="item_name_search" id="item_name_search" placeholder="Item Name/Code" class="form-control mw-100" autocomplete="off" value="">
						</div>
					</div>
					<div class="col mb-1">
						<label class="form-label">&nbsp;</label><br/>
						<button type="button" class="btn btn-warning btn-sm clearPiFilter"><i data-feather="x-circle"></i> Clear</button>
					</div>
					<div class="col-md-12">
						<div class="po-table-container">
							<table class="table table-striped table-bordered po-order-detail myrequesttablecbox nowrap w-100">
								<thead class="table-light header">
									<tr>
										<th class="d-none">ID</th>
										<th>
											<div class="form-check form-check-inline me-0">
												<input class="form-check-input" type="checkbox" name="podetail" id="inlineCheckbox1">
											</div>
										</th>
										<th>Series</th>
										<th>Indent No.</th>
										<th>Indent Date</th>
										<th>Item Code</th>
										<th>Item Name</th>
										<th>Attributes</th>
										<th>UOM</th>
										<th>Quantity</th>
										<th>Pending PO</th>
										<th>Avl Stock</th>
										<th style="min-width: 250px; max-width: 350px;">Vendor</th>
										<th>Sales Order</th>
										<th>Location</th>
										<th>Requester</th>
										<th>Remarks</th>
									</tr>
								</thead>
								<tbody id="prDataTable"></tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>