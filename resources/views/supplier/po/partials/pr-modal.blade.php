<div class="modal fade text-start" id="prModal" tabindex="-1" aria-labelledby="prModal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
		<div class="modal-content">
			<div class="modal-header">
				<div>
					@if(request()->type == 'supplier-invoice')
					<h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="prModal">Select Purchase Order</h4>
					@else
					<h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="prModal">Select Purchase Indent</h4>
					@endif
					<p class="mb-0">Select from the below list</p>
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
							<label class="form-label">@if(request()->type == 'supplier-invoice') Doc @else Indent @endif No.</label>
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
					{{-- <div class="col">
						<div class="mb-1">
							@if(request()->route('type') == 'supplier-invoice')						
							<label class="form-label">Vendor</label>
							@else
							<label class="form-label">Preferred Vendor</label>
							@endif
							<input type="text" id="vendor_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type = "hidden" id = "vendor_id_qt_val"></input>
						</div>
					</div> --}}
					<div class="col  mb-1">
						<label class="form-label">&nbsp;</label><br/>
						<button type = "button" class="btn btn-warning btn-sm searchPiBtn"><i data-feather="search"></i> Search</button>
					</div>
					<div class="col-md-12">
						<div class="table-responsive" style="overflow-y: auto;max-height: 200px;">
							<table class="mt-1 table myrequesttablecbox table-striped po-order-detail"> 
								<thead>
									<tr>
										<th>
											<div class="form-check form-check-inline me-0">
												<input class="form-check-input" type="checkbox" name="podetail" id="inlineCheckbox1">
											</div> 
										</th>  
										<th>Series</th>
										<th>@if(request()->type == 'supplier-invoice') Doc @else Indent @endif No.</th>
										<th>@if(request()->type == 'supplier-invoice') Doc @else Indent @endif Date</th>
										<th>Item Code</th>
										<th>Item Name</th>
										<th>Quantity</th> 
										{{-- @if(request()->route('type') == 'supplier-invoice')	
										<th>Vendor</th>
										@else
										<th>Preferred Vendor</th>
										@endif --}}
									</tr>
								</thead>
								<tbody id="prDataTable">
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer text-end">
				<button type = "button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
				<button type = "button" class="btn btn-primary btn-sm prProcess"><i data-feather="check-circle"></i> Process</button>
			</div>
		</div>
	</div>
</div>