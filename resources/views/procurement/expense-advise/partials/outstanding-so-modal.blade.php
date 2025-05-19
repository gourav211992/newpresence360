<div class="modal fade text-start" id="soModal" tabindex="-1" aria-labelledby="soModal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1500px">
		<div class="modal-content">
			<div class="modal-header">
				<div>
					<h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="soModal">
						Select SO
					</h4>
					<p class="mb-0">
						Select from the below list
					</p>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col">
						<div class="mb-1">
							<label class="form-label">Series</label>
							<input type="text" id="book_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type = "hidden" id = "book_id_qt_val"></input>
						</div>
					</div>
					<div class="col">
						<div class="mb-1">
							<label class="form-label">Doc No.</label>
							<input type="text" id="document_no_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type = "hidden" id = "document_id_qt_val"></input>
						</div>
					</div>
					<div class="col">
						<div class="mb-1">
							<label class="form-label">Item</label>
							<input type="text" id="item_name_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type = "hidden" id = "item_id_qt_val"></input>
						</div>
					</div>
					<div class="col">
						<div class="mb-1">
							<label class="form-label">Customer</label>
							<input type="text" id="customer_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type = "hidden" id = "customer_id_qt_val"></input>
						</div>
					</div>
					<div class="col  mb-1">
						<label class="form-label">&nbsp;</label><br/>
						<button type = "button" class="btn btn-warning btn-sm searchSoBtn">
							<i data-feather="search"></i> Search
						</button>
					</div>
					<div class="col-md-12">
						<div class="table-responsive" style="overflow-y: auto;max-height: 200px;">
							<table class="mt-1 table myrequesttablecbox table-striped so-order-detail">
								<thead>
									<tr>
										<th>
											<div class="form-check form-check-inline me-0">
												<input class="form-check-input" type="checkbox" name="sodetail" id="inlineCheckbox1">
											</div>
										</th>
                                        <th>CUSTOMER CODE</th>
                                        <th>CUSTOMER NAME</th>
                                        <th>SO NO.</th>
                                        <th>SO Date</th>
                                        <th>ITEM CODE</th>
                                        <th>Item Name</th>
                                        <th class="text-end">SO Quantity</th>
                                        <th class="text-end">EXPENSE Quantity</th>
                                        <th class="text-end">BALANCE QTY</th>
                                        <th class="text-end">RATE</th>
                                        <th class="text-end">VALUE</th>
									</tr>
								</thead>
								<tbody id="soDataTable">
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer text-end">
				<button type = "button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
					<i data-feather="x-circle"></i> Cancel
				</button>
				<button type = "button" class="btn btn-primary btn-sm soProcess">
					<i data-feather="check-circle"></i> Process
				</button>
			</div>
		</div>
	</div>
</div>
