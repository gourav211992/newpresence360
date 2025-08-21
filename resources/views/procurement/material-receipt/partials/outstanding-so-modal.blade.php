<div class="modal fade text-start" id="soModal" tabindex="-1" aria-labelledby="soModal" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header d-flex justify-content-between align-items-start">
				<div>
					<h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="soModal">
						Select Work Order
					</h4>
					<p class="mb-0">
						Select from the below list
					</p>
				</div>
				<div class="d-flex align-items-start gap-2">
					<button type="button" class="btn btn-primary btn-sm soProcess">
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
							<input type="text" id="so_vendor_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type="hidden" id="so_vendor_id_qt_val"></input>
						</div>
					</div>
					<div class="col">
						<div class="mb-1">
							<label class="form-label">Doc No.</label>
							<input type="text" id="so_document_no_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type = "hidden" id = "so_document_id_qt_val"></input>
						</div>
					</div>
					<div class="col">
						<div class="mb-1">
							<label class="form-label">Item</label>
							<input type="text" name="so_item_name_search" id="so_item_name_search" placeholder="Item Name/Code" class="form-control mw-100" autocomplete="off" value="">
						</div>
					</div>
					<div class="col mb-1">
						<label class="form-label">&nbsp;</label><br/>
						<button type="button" class="btn btn-warning btn-sm clearSoFilter"><i data-feather="x-circle"></i> Clear</button>
					</div>
					<div class="col-md-12">
						<div class="so-table-container">
							<table class="table table-striped table-bordered so-order-detail myrequesttablecbox nowrap w-100">
								<thead class="table-light header">
									<tr>
										<th class="d-none">ID</th>
										<th>
											<div class="form-check form-check-inline me-0">
												<input class="form-check-input" type="checkbox" name="sodetail" id="inlineSoCheckbox1">
											</div>
										</th>
										<th>SUPPLIER NAME</th>
                                        <th>SO NO</th>
                                        <th>SO DATE</th>
										<th>GE NO</th>
										<th>GE DATE</th>
                                        <th>ITEM CODE</th>
                                        <th>ITEM NAME</th>
                                        <th>ATTRIBUTES</th>
                                        <th class="text-end">SO QTY</th>
                                        <th class="text-end">INV QTY</th>
										<th class="text-end">GE QTY</th>
										<th class="text-end">GRN QTY</th>
                                        <th class="text-end">BALANCE QTY</th>
                                        <th class="text-end">RATE</th>
                                        <th class="text-end">VALUE</th>
                                        <th class="text-end">Payment Term</th>
                                        <th class="text-end">Credit Days</th>
									</tr>
								</thead>
								<tbody id="soDataTable">
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

