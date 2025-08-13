<div class="modal fade text-start" id="joModal" tabindex="-1" aria-labelledby="joModal" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header d-flex justify-content-between align-items-start">
				<div>
					<h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="joModal">
						Select Job Order
					</h4>
					<p class="mb-0">
						Select from the below list
					</p>
				</div>
				<div class="d-flex align-items-start gap-2">
					<button type="button" class="btn btn-primary btn-sm joProcess">
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
							<input type="text" id="jo_vendor_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type="hidden" id="jo_vendor_id_qt_val"></input>
						</div>
					</div>
                    <div class="col">
						<div class="mb-1">
							<label class="form-label">JO No.</label>
							<input type="text" id="jo_document_no_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type = "hidden" id = "jo_document_id_qt_val"></input>
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
						<button type="button" class="btn btn-warning btn-sm clearJoFilter"><i data-feather="x-circle"></i> Clear</button>
					</div>
					<div class="col-md-12">
						<div class="jo-table-container">
							<table class="table table-striped table-bordered jo-order-detail myrequesttablecbox nowrap w-100">
								<thead class="table-light header">
									<tr>
										<th class="d-none">ID</th>
										<th>
											<div class="form-check form-check-inline me-0">
												<input class="form-check-input" type="checkbox" name="jodetail" id="inlineCheckbox1">
											</div>
										</th>
										<th>SUPPLIER NAME</th>
                                        <th>JO NO</th>
                                        <th>JO DATE</th>
                                        <th>ITEM CODE</th>
                                        <th>ITEM NAME</th>
                                        <th>ATTRIBUTES</th>
                                        <th class="text-end">JO QTY</th>
                                        <th class="text-end">INV QTY</th>
										<th class="text-end">Exp QTY</th>
                                        <th class="text-end">BALANCE QTY</th>
                                        <th class="text-end">RATE</th>
                                        <th class="text-end">VALUE</th>
									</tr>
								</thead>
								<tbody id="joDataTable">
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<!-- <div class="modal-footer text-end">
				<button type = "button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
					<i data-feather="x-circle"></i> Cancel
				</button>
				<button type = "button" class="btn btn-primary btn-sm joProcess">
					<i data-feather="check-circle"></i> Process
				</button>
			</div> -->
		</div>
	</div>
</div>
