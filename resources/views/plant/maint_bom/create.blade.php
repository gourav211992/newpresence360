@extends('layouts.app')
@section('content')
	<!-- BEGIN: Content-->
	<div class="app-content content ">
		<div class="content-overlay"></div>
		<div class="header-navbar-shadow"></div>
		<div class="content-wrapper container-xxl p-0">
			<div class="content-header pocreate-sticky">
				<div class="row">
					<div class="content-header-left col-md-6 mb-2">
						<div class="row breadcrumbs-top">
							<div class="col-12">
								<h2 class="content-header-title float-start mb-0">Maintenance BOM</h2>
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
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right">
							<a href="{{ route('maint-bom.index') }}"> <button class="btn btn-secondary btn-sm"><i
										data-feather="arrow-left-circle"></i> Back</button>
							</a>
							<button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" type="button" id="save-draft-btn">
								<i data-feather="save"></i> Save as Draft
							</button>

							<button type="submit" form="maint-bom-form" class="btn btn-primary btn-sm" id="submit-btn">
								<i data-feather="check-circle"></i> Submit
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="content-body">
				<form id="maint-bom-form" method="POST" action="{{ route('maint-bom.store') }}"
					enctype="multipart/form-data">

					@csrf

					<section id="basic-datatable">
						<div class="row">
							<div class="col-12">

								<div class="card">
									<div class="card-body customernewsection-form">

										<div class="border-bottom mb-2 pb-25">
											<div class="row">
												<div class="col-md-6">
													<div class="newheader ">
														<h4 class="card-title text-theme">Basic Information</h4>
														<p class="card-text">Fill the details</p>
													</div>
												</div>

											</div>
										</div>




										<div class="row">
											<input type="hidden" name="book_code" id="book_code_input">
											<input type="hidden" name="spare_parts" id="spare_parts">
											<input type="hidden" name="doc_number_type" id="doc_number_type">
											<input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
											<input type="hidden" name="doc_prefix" id="doc_prefix">
											<input type="hidden" name="doc_suffix" id="doc_suffix">
											<input type="hidden" name="doc_no" id="doc_no">
											<input type="hidden" name="document_status" id="document_status" value="">


											<div class="col-md-8">



												<div class="">

													<div class="row align-items-center mb-1">
														<div class="col-md-3">
															<label class="form-label">Series <span
																	class="text-danger">*</span></label>
														</div>

														<div class="col-md-5">
															<select class="form-select" id="book_id" name="book_id"
																required>
																@foreach ($series as $book)
																	<option value="{{ $book->id }}">{{ $book->book_code }}
																	</option>
																@endforeach
															</select>
														</div>
													</div>

													<div class="row align-items-center mb-1">
														<div class="col-md-3">
															<label class="form-label">Doc No <span
																	class="text-danger">*</span></label>
														</div>

														<div class="col-md-5">
															<input type="text" class="form-control" id="document_number"
																name="document_number" required>
														</div>
													</div>

													<div class="row align-items-center mb-1">
														<div class="col-md-3">
															<label class="form-label">Doc Date <span
																	class="text-danger">*</span></label>
														</div>

														<div class="col-md-5">
															<input type="date" class="form-control" id="document_date"
																name="document_date" value="{{ date('Y-m-d') }}" required>
														</div>
													</div>


													<div class="row align-items-center mb-1">
														<div class="col-md-3">
															<label class="form-label">BOM Name <span
																	class="text-danger">*</span></label>
														</div>

														<div class="col-md-5">
															<input type="text" name="bom_name" id="bom_name"
																class="form-control" required />
														</div>
													</div>


												</div>

											</div>


										</div>


									</div>
								</div>
								<div class="card">
									<div class="card-body customernewsection-form">
										<div class="border-bottom mb-2 pb-25">
											<div class="row">
												<div class="col-md-6">
													<div class="newheader ">
														<h4 class="card-title text-theme">Spare Parts Detail</h4>
														<p class="card-text">Fill the details</p>
													</div>
												</div>
												<div class="col-md-6 text-sm-end">
													<a href="#" class="btn btn-sm btn-outline-danger me-50" id="delete">
														<i data-feather="x-circle"></i> Delete</a>
													<a href="#" class="btn btn-sm btn-outline-primary" id="addNewRowBtn">
														<i data-feather="plus"></i> Add New Item</a>
												</div>
											</div>
										</div>
										<div class="table-responsive pomrnheadtffotsticky">
											<table id="itemTable"
												class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
												<thead>
													<tr>
														<th width="62" class="customernewsection-form">
															<div class="form-check form-check-primary custom-checkbox">
																<input type="checkbox" class="form-check-input"
																	id="checkAll">
																<label class="form-check-label" for="Email"></label>
															</div>
														</th>
														<th width="285">Item Code</th>
														<th width="208">Item Name</th>
														<th>Attributes</th>
														<th>UOM</th>
														<th>Qty</th>
													</tr>
												</thead>
												<tbody class="mrntableselectexcel">
													<tr class="trselected">
														<td class="customernewsection-form">
															<div class="form-check form-check-primary custom-checkbox">
																<input type="checkbox" class="form-check-input row-check"
																	id="Email">
																<label class="form-check-label" for="Email"></label>
															</div>
														</td>
														<td class="poprod-decpt">
															<input type="hidden" class="item_id">
															<input required type="text" placeholder="Select" name="item[]"
																class="item_code form-control mw-100 ledgerselecct mb-25" />
														</td>
														<td required class="poprod-decpt">
															<input type="text" placeholder="Select"
																class="item_name form-control mw-100 ledgerselecct mb-25" />
														</td>

														<td class="poprod-decpt">
															<input type="hidden" class="attribute">
															<div class="d-flex flex-wrap gap-1" id="attribute-badges">
																<!-- Attribute badges will be displayed here -->
															</div>
														</td>
														<td>
															<select class="uom form-select mw-100" name="uom[]" required>

															</select>
														</td>
														<td><input type="number" class="qty form-control mw-100"
																name="qty[]" required /></td>
													</tr>
												</tbody>
												<tfoot>


													<tr valign="top" class="part-details-section">
														<td colspan="7" rowspan="10">
															<table class="table border">
																<tr>
																	<td class="p-0">
																		<h6
																			class="text-dark mb-0 bg-light-primary py-1 px-50">
																			<strong>Part Details</strong>
																		</h6>
																	</td>
																</tr>
																<tr>
																	<td class="poprod-decpt">
																		<span
																			class="poitemtxt mw-100"><strong>Name</strong>:<span
																				id="part_name"></span></span>
																	</td>
																</tr>
																<tr>
																	<td class="poprod-decpt" id="attributes_badges">
																		
																	</td>
																</tr>
																<tr>
																	<td class="poprod-decpt">
																		<span
																			class="badge rounded-pill badge-light-primary"><strong>Inv.
																				UOM</strong>: <span id="uom"></span></span>
																		<span
																			class="badge rounded-pill badge-light-primary"><strong>Qty.</strong>:
																			<span id="qty"></span></span>
																	</td>
																</tr>
																<tr>
																	{{-- <td class="poprod-decpt">
																		<span
																			class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>:
																			<span id="remarks"></span></span>
																	</td> --}}
																</tr>
															</table>
														</td>

													</tr>

												</tfoot>
											</table>
										</div>


										<div class="row mt-2">
											<div class="col-md-4">
												<div class="mb-1">
													<label class="form-label">Upload Document</label>
													<input type="file" name="document" class="form-control">
												</div>
											</div>

											<div class="col-md-12">
												<div class="mb-1">
													<label class="form-label">Final Remarks</label>
													<textarea type="text" name="remarks" rows="4" class="form-control"
														placeholder="Enter Remarks here..."></textarea>

												</div>
											</div>

										</div>


									</div>
								</div>
							</div>
						</div>
						<!-- Modal to add new record -->

					</section>
				</form>

				</section>
			</form>

			</div>
		</div>
	</div>
	<!-- END: Content-->


	<div class="sidenav-overlay"></div>
	<div class="drag-target"></div>

			</div>
		</div>
	</div>
	<!-- END: Content-->


	<div class="sidenav-overlay"></div>
	<div class="drag-target"></div>


	<div class="modal fade text-start" id="overhead" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
						<h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Enter Overhead
						</h4>
						<p class="mb-0">Enter the below list</p>
					</div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row">


						<div class="col-md-12">


							<div class="table-responsive-md">
								<table
									class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
									<thead>
										<tr>
											<th>#</th>
											<th>Description</th>
											<th>Amount</th>
											<th width="400px">Leadger</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>1</td>
											<td><input type="text" class="form-control mw-100"></td>
											<td><input type="text" class="form-control mw-100"></td>
											<td>
												<select class="form-select select2">
													<option>Select</option>
												</select>
											</td>
										</tr>

										<tr>
											<td>2</td>
											<td><input type="text" class="form-control mw-100"></td>
											<td><input type="text" class="form-control mw-100"></td>
											<td>
												<select class="form-select select2">
													<option>Select</option>
												</select>
											</td>
										</tr>

										<tr>
											<td>2</td>
											<td><input type="text" class="form-control mw-100"></td>
											<td><input type="text" class="form-control mw-100"></td>
											<td>
												<select class="form-select select2">
													<option>Select</option>
												</select>
											</td>
										</tr>


									</tbody>


								</table>
							</div>
						</div>


					</div>
				</div>
				<div class="modal-footer text-end">
					<button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i>
						Cancel</button>
					<button class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i>
						Submit</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="wastage" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Wastage Details</h1>
					<p class="text-center">Enter the details below.</p>

					<div class="row">
						<div class="col-md-12 mb-1">
							<label class="form-label">Wastage Type <span class="text-danger">*</span></label>
							<select class="form-control">
								<option>Select</option>
								<option selected>Fixed</option>
								<option>%age</option>
							</select>
						</div>

						<div class="col-md-12 mb-1">
							<label class="form-label">Wastage Value <span class="text-danger">*</span></label>
							<input type="text" class="form-control" placeholder="Enter Value">
						</div>
					</div>
				</div>

				<div class="modal-footer justify-content-center">
					<button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
					<button type="reset" class="btn btn-primary">Select</button>
				</div>
			</div>
		</div>
	</div>


	<div class="modal fade" id="attribute" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Select Attribute</h1>
					<p class="text-center">Enter the details below.</p>

					<div class="table-responsive-md customernewsection-form">
						<table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"
							id="attributes_table_modal" item-index="">
							<thead>
								<tr>
									<th>Attribute Name</th>
									<th>Attribute Value</th>
								</tr>
							</thead>
							<tbody id="attribute_table">

							</tbody>

						</table>
					</div>
				</div>

				<div class="modal-footer justify-content-center">
					<button type="button" class="btn btn-outline-secondary me-1"
						onclick="closeModal('attribute');">Cancel</button>
					<button type="button" class="btn btn-primary submitAttributeBtn">Select</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1"
		aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body alertmsg text-center warning">
					<i data-feather='alert-circle'></i>
					<h2>Are you sure?</h2>
					<p>Are you sure you want to <strong>Amendment</strong> this <strong>BOM</strong>? After Amendment this
						action cannot be undone.</p>
					<button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
				</div>
			</div>
		</div>
	</div>
@endsection




@section('scripts')
	<script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>

	<script>
		const itemsData = @json($items);
		let rowCount = 1;
		$(window).on('load', function () {
			if (feather) {
				feather.replace({
					width: 14,
					height: 14
				});
			}
		})



		// $(function () {
		// 	$(".ledgerselecct").autocomplete({
		// 		source: [
		// 			"Indian Oil Corporation Ltd.",
		// 			"Airports Authority of India",
		// 			"Bharat Heavy Electricals Ltd.",
		// 			"Bharat Petroleum Corpn. Ltd.",
		// 			"NTPC Ltd.",
		// 			"Gail (India) Ltd.",
		// 			"Hindustan Petroleum Corpn. Ltd.",
		// 			"Steel Authority of India Ltd.",
		// 			"Indian Railway Stations Devpt. Corporation Ltd.",
		// 			"Oil & Natural Gas Corporation Ltd.",
		// 			"Oil & Natural Gas Corporation Ltd.",
		// 			"Hindustan Aeronautics Ltd.",
		// 		],
		// 		minLength: 0
		// 	}).focus(function () {
		// 		if (this.value == "") {
		// 			$(this).autocomplete("search");
		// 		}
		// 	});
		// });

		$(".mrntableselectexcel tr").click(function () {
			$(this).addClass('trselected').siblings().removeClass('trselected');
			value = $(this).find('td:first').html();
		});

		$(document).on('keydown', function (e) {
			if (e.which == 38) {
				$('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
			} else if (e.which == 40) {
				$('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
			}
			// Remove automatic scrolling - only scroll for attribute-related actions
			updateFooterFromSelected();
		});
		
		$(document).on('click', 'tbody tr', function () {
			$(this).addClass('trselected').siblings().removeClass('trselected');
			// Remove automatic scrolling - only scroll for attribute-related actions
			updateFooterFromSelected();
		});
		function updateFooterFromSelected() {
			let $selected = $('.trselected');
			
			if ($selected.length) {	
				
				// Get basic part details
				let partName = $selected.find('.item_name').val() || 'N/A';
				let uomText = $selected.find('.uom option:selected').text() || $selected.find('.uom').val() || 'N/A';
				let qty = $selected.find('.qty').val() || '0';
				
				
				// Update part details display
				$('#part_name').text(partName);
				$('#uom').text(uomText);
				$('#qty').text(qty);
				
				let $selectElement = $selected.find('.item_code');
				let $badgesContainer = $('#attributes_badges'); // container for badges

				// Handle attributes - check for both static and AJAX loaded data
				let attributesData = [];
				
				// First try to get from AJAX loaded data (attribute-enriched hidden field)
				let $enrichedInput = $selected.find('.attribute-enriched');
				if ($enrichedInput.length && $enrichedInput.val()) {
					try {
						attributesData = JSON.parse($enrichedInput.val());
					} catch (e) {
						console.log('Error parsing enriched attributes:', e);
					}
				}
				
				// If no AJAX data, try static data approach
				if (!attributesData.length && $selectElement.val() !== "") {
					let attributesJSON = JSON.parse($selectElement.attr('data-attr') || '[]');
					let $hiddenInput = $selected.find('.attribute');
					let existingAttributes = $hiddenInput.length && $hiddenInput.val()
						? JSON.parse($hiddenInput.val())
						: [];

					if (attributesJSON.length) {
						attributesData = attributesJSON.map(function(element) {
							// Find selected value from existingAttributes
							let selectedValObj = existingAttributes.find(attr => attr.item_attribute_id === element.id);
							let selectedVal = selectedValObj ? selectedValObj.value_id : '';

							// Find text for selected value
							let selectedText = '';
							if (selectedVal) {
								let valuesData = element.values_data || element.values || [];
								let valObj = valuesData.find(v => v.id === selectedVal);
								selectedText = valObj ? valObj.value : '';
							}
							
							return {
								group_name: element.group_name,
								selected_value_name: selectedText,
								value: selectedText
							};
						}).filter(attr => attr.selected_value_name || attr.value);
					}
				}

				// Display attributes
				if (attributesData.length) {
					let badgesHtml = '';
					attributesData.forEach(function(attr) {
						let displayValue = attr.selected_value_name || attr.value || 'N/A';
						let groupName = attr.group_name || attr.group_short_name || 'Attribute';
						
						badgesHtml += `
							<span class="badge rounded-pill badge-light-primary" style="margin-right:5px;">
								<strong>${groupName}</strong>: <span>${displayValue}</span>
							</span>
						`;
					});
					$badgesContainer.html(badgesHtml);
				} else {
					$badgesContainer.html('<span class="text-muted">No attributes selected</span>');
				}
			}
		}
		$('#addNewRowBtn').on('click', function () {
			rowCount++;
			let newRow = `<tr>
															<td class="customernewsection-form">
																<div class="form-check form-check-primary custom-checkbox">
																	<input type="checkbox" class="form-check-input row-check"
																		id="Email">
																	<label class="form-check-label" for="Email"></label>
																</div>
															</td>
															<td class="poprod-decpt">
																<input type="hidden" class="item_id">
																<input required type="text" placeholder="Select" name="item[]"
																	class="item_code form-control mw-100 ledgerselecct mb-25" />
															</td>
															<td required class="poprod-decpt">
																<input type="text" placeholder="Select"
																	class="item_name form-control mw-100 ledgerselecct mb-25" />
															</td>

															<td class="poprod-decpt">
																<input type="hidden" class="attribute">
																<div class="d-flex flex-wrap gap-1" id="attribute-badges">
																	<!-- Attribute badges will be displayed here -->
																</div>
															</td>
															<td>
																<select class="uom form-select mw-100" name="uom[]" required>

																</select>
															</td>
															<td><input type="number" class="qty form-control mw-100"  name="qty[]"
																	required /></td>
														</tr>																  `;
			$('.mrntableselectexcel').append(newRow);
			initAutoForItem('.item_code');

		});
		$('#delete').on('click', function () {
			let $rows = $('.mrntableselectexcel tr');
			let $checked = $rows.find('.row-check:checked');

			// Prevent deletion if only one row exists
			if ($rows.length <= 1) {
				showToast('error', 'At least one row is required.');
				return;
			}

			// Prevent deletion if checked rows would remove all
			if ($rows.length - $checked.length < 1) {
				showToast('error', 'You must keep at least one row.');
				return;
			}

			// Remove only the checked rows
			$checked.closest('tr').remove();

		});
		$('#checkAll').on('change', function () {
			let isChecked = $(this).is(':checked');
			$('.mrntableselectexcel .row-check').prop('checked', isChecked);
		});
		function resetParametersDependentElements(data) {
			let backDateAllowed = false;
			let futureDateAllowed = false;

			if (data != null) {
				if (Array.isArray(data?.parameters?.back_date_allowed)) {
					for (let i = 0; i < data.parameters.back_date_allowed.length; i++) {
						if (data.parameters.back_date_allowed[i].trim().toLowerCase() === "yes") {
							backDateAllowed = true;
							break; // Exit the loop once we find "yes"
						}
					}
				}
				if (Array.isArray(data?.parameters?.future_date_allowed)) {
					for (let i = 0; i < data.parameters.future_date_allowed.length; i++) {
						if (data.parameters.future_date_allowed[i].trim().toLowerCase() === "yes") {
							futureDateAllowed = true;
							break; // Exit the loop once we find "yes"
						}
					}
				}
				//console.log(backDateAllowed, futureDateAllowed);

			}

			const dateInput = document.getElementById("document_date");

			// Determine the max and min values for the date input
			const today = moment().format("YYYY-MM-DD");

			if (backDateAllowed && futureDateAllowed) {
				dateInput.removeAttribute("min");
				dateInput.removeAttribute("max");
			} else if (backDateAllowed) {
				dateInput.setAttribute("max", today);
				dateInput.removeAttribute("min");
			} else if (futureDateAllowed) {
				dateInput.setAttribute("min", today);
				dateInput.removeAttribute("max");
			} else {
				dateInput.setAttribute("min", today);
				dateInput.setAttribute("max", today);

			}
		}

		$('#book_id').on('change', function () {
			resetParametersDependentElements(null);
			let currentDate = new Date().toISOString().split('T')[0];
			let document_date = $('#document_date').val();
			let bookId = $('#book_id').val();
			let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId +
				"&document_date=" + document_date;
			fetch(actionUrl).then(response => {
				return response.json().then(data => {
					if (data.status == 200) {
						resetParametersDependentElements(data.data);
						$("#book_code_input").val(data.data.book_code);
						if (!data.data.doc.document_number) {
							$("#document_number").val('');
							$('#doc_number_type').val('');
							$('#doc_reset_pattern').val('');
							$('#doc_prefix').val('');
							$('#doc_suffix').val('');
							$('#doc_no').val('');
						} else {
							$("#document_number").val(data.data.doc.document_number);
							$('#doc_number_type').val(data.data.doc.type);
							$('#doc_reset_pattern').val(data.data.doc.reset_pattern);
							$('#doc_prefix').val(data.data.doc.prefix);
							$('#doc_suffix').val(data.data.doc.suffix);
							$('#doc_no').val(data.data.doc.doc_no);
						}
						if (data.data.doc.type == 'Manually') {
							$("#document_number").attr('readonly', false);
						} else {
							$("#document_number").attr('readonly', true);
						}

					}
					if (data.status == 404) {
						$("#document_number").val('');
						$('#doc_number_type').val('');
						$('#doc_reset_pattern').val('');
						$('#doc_prefix').val('');
						$('#doc_suffix').val('');
						$('#doc_no').val('');
						showToast('error', data.message);
					}
				});
			});
		});

		$('#book_id').trigger('change');
		initAutoForItem('.item_code');
		function updateJsonData() {
			const allRows = [];

			$('.mrntableselectexcel tr').each(function () {
				const row = $(this);
				const itemId = row.find('.item_id').val();

				if (itemId) { // skip empty rows
					const rowData = {
						item_id: itemId,
						item_code: row.find('.item_code').val() || '',
						item_name: row.find('.item_name').val() || '',
						attribute: row.find('.attribute').val() || '',
						qty: row.find('.qty').val() || 0,
						uom_id: row.find('.uom').val() || '',
						uom_name: row.find('.uom option:selected').text() || '',
					};
					allRows.push(rowData);
				}
			});
			$('#spare_parts').val(JSON.stringify(allRows));
		}


		document.getElementById('save-draft-btn').addEventListener('click', function () {
			$('.preloader').show();
			document.getElementById('document_status').value = 'draft';
			updateJsonData();
			document.getElementById('maint-bom-form').submit();

		});


		$('#maint-bom-form').on('submit', function (e) {
			$('.preloader').show();
			document.getElementById('document_status').value = 'submitted';
			e.preventDefault(); // Always prevent default first
			updateJsonData();
			this.submit();

		});

		function showToast(icon, title) {
			const Toast = Swal.mixin({
				toast: true,
				position: "top-end",
				showConfirmButton: false,
				timer: 3000,
				timerProgressBar: true,
				didOpen: (toast) => {
					toast.onmouseenter = Swal.stopTimer;
					toast.onmouseleave = Swal.resumeTimer;
				},
			});
			Toast.fire({
				icon,
				title
			});
		}

		@if (session('success'))
			$('.preloader').hide();
			showToast("success", "{{ session('success') }}");
		@endif

		@if (session('error'))
			$('.preloader').hide();
			showToast("error", "{{ session('error') }}");
		@endif

		@if ($errors->any())
			$('.preloader').hide();
			showToast('error',
				"@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
			);
		@endif
		$(document).on('input change', '.qty, .uom, .item_name, .item_code, .attribute', function () {
			updateFooterFromSelected();
		});

		$(document).on('click', '.submitAttributeBtn', (e) => {
			let $currentRow = $('#attribute_table').data('currentRow');
			if ($currentRow) {
				changeAttributeVal($currentRow);
				updateAttributeBadges($currentRow);
				updateFooterFromSelected();
			}
			$("#attribute").modal('hide');
		});
		function initAutoForItem(selector, type) {
			$(selector).autocomplete({
				minLength: 0,
				source: function (request, response) {
					let term = request.term.toLowerCase();

					// Gather all already selected item IDs from other rows
					let selectedItemIds = [];
					$('.item_id').each(function () {
						let val = $(this).val();
						if (val) selectedItemIds.push(val);
					});

					// Filter itemsData by search term AND exclude already selected items
					let filtered = itemsData.filter(item => {
						let isSelectedElsewhere = selectedItemIds.includes(item.id.toString());

						// Allow the current input's item (so it doesn't exclude itself)
						// Get current input's item_id value:
						let currentItemId = $(selector).closest('tr').find('.item_id').val();

						// Include item if:
						// - it matches the search term
						// - and (not selected elsewhere OR is the current selected item in this row)
						return (item.item_code.toLowerCase().includes(term) || item.item_name.toLowerCase().includes(term)) &&
							(!isSelectedElsewhere || item.id.toString() === currentItemId);
					});
					

					let results = filtered.map(item => ({
						id: item.id,
						label: `${item.item_code} - ${item.item_name}`,
						code: item.item_code,
						item_id: item.id,
						item_name: item.item_name,
						uom_name: item.uom_name,
						uom_id: item.uom_id,
						attr: item.item_attributes,
					}));

					response(results);
				},
				select: function (event, ui) {
					let $input = $(this);
					let itemCode = ui.item.code;
					let attr = ui.item.attr;
					let itemName = ui.item.item_name;
					let itemId = ui.item.item_id;
					let uomId = ui.item.uom_id;
					let uomName = ui.item.uom_name;
					

					$input.attr('data-name', itemName);
					$input.attr('data-code', itemCode);
					$input.attr('data-attr', JSON.stringify(attr));
					$input.attr('data-id', itemId);
					$input.closest('tr').find('.item_id').val(itemId);
					$input.closest('tr').find('.item_name').val(itemName);
				
					$input.val(itemCode);

					let uomOption = `<option value="${uomId}">${uomName}</option>`;
					$input.closest('tr').find('.uom').empty().append(uomOption);
					
					// Update part details section
					$('#part_name').text(itemName);
					$('#uom').text(uomName);
					$('#qty').text('0'); // Default quantity
					

					// Display attribute badges if item has attributes
					if (attr && attr.length > 0) {
						let badgesHtml = '';
						attr.forEach(function(attribute) {
							badgesHtml += `<span class="badge rounded-pill badge-light-primary" style="font-size:10px; margin-right:5px;">
								<strong>${attribute.group_name}</strong>
							</span>`;
						});
						$input.closest('tr').find('#attribute-badges').html(badgesHtml);
						
						// Automatically open attribute modal if item has attributes
						setTimeout(() => {
							let $tr = $input.closest('tr');
							let $attributesTable = $('#attribute_table');
							$attributesTable.data('currentRow', $tr);
							
							// Populate modal with attributes
							let attributesJSON = attr;
							let $hiddenInput = $tr.find('.attribute');
							let existingAttributes = $hiddenInput.length && $hiddenInput.val()
								? JSON.parse($hiddenInput.val())
								: [];

							if (attributesJSON.length > 0) {
								let innerHtml = ``;
								$.each(attributesJSON, function (index, element) {
									let optionsHtml = ``;
									
									// Check if element has values_data or use different structure
									let valuesData = element.values_data || element.values || [];
									
									$.each(valuesData, function (i, value) {
										let isSelected = existingAttributes.some(attr =>
											attr.item_attribute_id === element.id && attr.value_id === value.id
										);
										optionsHtml += `<option value='${value.id}' ${isSelected ? 'selected' : ''}>${value.value}</option>`;
									});

									innerHtml += `
										<tr>
											<td>
												${element.group_name}
												<input type="hidden" name="id" value="${element.id}">
											</td>
											<td>
												<select class="form-select select2" style="max-width:100% !important;">
													<option value="">Select</option>
													${optionsHtml}
												</select>
											</td>
										</tr>
									`;
								});
								$attributesTable.html(innerHtml);
								$attributesTable.find('select').off('change').on('change', function () {
									changeAttributeVal($tr);
								});
								$attributesTable.find('select').select2();
								
								// Open the modal
								$('#attribute').modal('show');
							} else {
								
							}
						}, 100);
					} else {
						
						$input.closest('tr').find('#attribute-badges').html('');
						setTimeout(() => {
							$input.closest('tr').find('.qty').val('').focus();
						}, 100);
					}

					return false;
				},
				change: function (event, ui) {
					if (!ui.item) {
						$(this).val("");
						$(this).attr('data-name', '');
						$(this).attr('data-code', '');
						$(this).attr('data-attr', '');
						$(this).closest('tr').find('.item_id').val('');
						$(this).closest('tr').find('.item_name').val('');
						$(this).closest('tr').find('.uom').empty();
						
					}
				}
			}).focus(function () {
				if (!this.value.trim()) {
					$(this).autocomplete("search", "");
				}
			}).on("input", function () {
				if ($(this).val().trim() === "") {
					$(this).removeData("selected");
					$(this).closest('tr').find(".item_name").val('');
					$(this).closest('tr').find(".attribute").val('');
					$(this).closest('tr').find(".item_id").val('');
					$(this).closest('tr').find(".item_code").val('');
				}
			});

			$(selector).autocomplete("instance")._renderItem = function (ul, item) {
				return $("<li>")
					.append(`<div><strong>${item.code}</strong> - ${item.item_name}</div>`)
					.appendTo(ul);
			};
		}

		function changeAttributeVal($row) {
			let hiddenInput = $row.find('.attribute');
			if (!hiddenInput) return;

			// Find the attributes table - it's the tbody with id="attribute_table"
			const tbody = document.getElementById("attribute_table");

			let selectedAttributes = [];

			Array.from(tbody.rows).forEach(row => {
				const hiddenInputAttr = row.querySelector('input[type="hidden"][name="id"]');
				const selectElement = row.querySelector("select");
				
				if (hiddenInputAttr && selectElement) {
					const attributeId = parseInt(hiddenInputAttr.value, 10);
					const selectedVal = parseInt(selectElement.value, 10);

					if (!isNaN(attributeId) && !isNaN(selectedVal) && selectedVal > 0) {
						selectedAttributes.push({
							item_attribute_id: attributeId,
							value_id: selectedVal
						});
					}
				}
			});

			// Update hidden input with JSON
			hiddenInput.val(JSON.stringify(selectedAttributes));
		}


		$(document).on('click', '.attributeBtn', function (e) {
			let $tr = $(this).closest('tr');
			let $selectElement = $tr.find('.item_code');
			let $attributesTable = $('#attribute_table'); // modal table
			$attributesTable.data('currentRow', $tr);

			if ($selectElement.val() !== "") {
				let attributesJSON = JSON.parse($selectElement.attr('data-attr') || '[]');
				let $hiddenInput = $tr.find('.attribute');
				let existingAttributes = $hiddenInput.length && $hiddenInput.val()
					? JSON.parse($hiddenInput.val())
					: [];

				if (!attributesJSON.length) {
					$attributesTable.html(`
							<tr>
								<td colspan="2" class="text-center">No attributes available</td>
							</tr>
						`);
					return;
				}

				let innerHtml = ``;

				$.each(attributesJSON, function (index, element) {
					let optionsHtml = ``;

					$.each(element.values_data, function (i, value) {
						let isSelected = existingAttributes.some(attr =>
							attr.item_attribute_id === element.id && attr.value_id === value.id
						);

						optionsHtml += `
								<option value='${value.id}' ${isSelected ? 'selected' : ''}>${value.value}</option>
							`;
					});

					innerHtml += `
							<tr>
								<td>
									${element.group_name}
									<input type="hidden" name="id" value="${element.id}">
								</td>
								<td>
									<select class="form-select select2" style="max-width:100% !important;">
										<option value="">Select</option>
										${optionsHtml}
									</select>
								</td>
							</tr>
						`;
				});

				$attributesTable.html(innerHtml);

				// Initialize select2

				//Bind change event
				$attributesTable.find('select').off('change').on('change', function () {
					changeAttributeVal($tr);
				});
				$attributesTable.find('select').select2();

				// Open the attribute modal
				$('#attribute').modal('show');

			} else {
				$attributesTable.html(`
						<tr>
							<td colspan="2" class="text-center">Please select an item first</td>
						</tr>
					`);
			}
		});
		function closeModal(id) {
			$('#' + id).modal('hide');
		}

		            // Function to update attribute badges display
            function updateAttributeBadges($row) {
                if (!$row) return;

                let $selectElement = $row.find('.item_code');
                let $badgesContainer = $row.find('#attribute-badges');

                if ($selectElement.val() !== "") {
                    let $hiddenInput = $row.find('.attribute');
                    let existingAttributes = $hiddenInput.length && $hiddenInput.val() ?
                        JSON.parse($hiddenInput.val()) :
                        [];

                    let attr = JSON.parse($selectElement.attr('data-attr') || '[]');

                    let badgesHtml = '';
                    let selectedCount = 0;

                    if (attr && attr.length > 0) {
                        attr.forEach(function(attribute) {

                            // Check if this attribute has been selected
                            let selectedAttr = existingAttributes.find(selected =>
                                selected.item_attribute_id === attribute.id
                            );

                            // Only show selected attributes
                            if (selectedAttr) {
                                selectedCount++;
                                if (selectedCount <= 2) {
                                    // Find the selected value from the attribute's values
                                    let valuesData = attribute.values_data || attribute.values || [];

                                    let selectedValue = valuesData.find(val => val.id === selectedAttr
                                        .value_id);

                                    if (selectedValue) {
                                        badgesHtml +=
                                            `<span class="badge rounded-pill badge-light-primary" style="font-size:10px; margin-right:5px;cursor:pointer">
								<strong>${attribute.group_name}</strong>: ${selectedValue.value}
							</span>`;

                                    } else {
                                        // Handle case where selected value isn't found (optional)
                                        badgesHtml +=
                                            `<span class="badge rounded-pill badge-light-warning" style="font-size:10px; margin-right:5px;cursor:pointer">
								<strong>${attribute.group_name}</strong>: N/A
							</span>`;
                                    }
                                }
                            }
                        });

                        if (selectedCount > 2) {
                            badgesHtml +=
                                '<span style="font-size:10px; color:black; margin-right:5px;cursor:pointer"><strong>...</strong></span>';
                        }

                        $badgesContainer.html(badgesHtml);

                    } else {
                        $badgesContainer.html('');
                    }
                } else {
                    $badgesContainer.html('');
                }
            }

            // Add click event for the entire attribute cell
            $('.mrntableselectexcel').on('click', 'td:nth-child(4)', function() {
                var $this = $(this);
                var $tr = $this.closest('tr');
                var $selectElement = $tr.find('.item_code');
                var $attributesTable = $('#attribute_table'); // modal table
                $attributesTable.data('currentRow', $tr);

                if ($selectElement.val() !== "") {
                    let attributesJSON = JSON.parse($selectElement.attr('data-attr') || '[]');
                    let $hiddenInput = $tr.find('.attribute');
                    let existingAttributes = $hiddenInput.length && $hiddenInput.val()
                        ? JSON.parse($hiddenInput.val())
                        : [];

                    if (!attributesJSON.length) {
                        $attributesTable.html(`
                            <tr>
                                <td colspan="2" class="text-center">No attributes available</td>
                            </tr>
                        `);
                        return;
                    }

                    let innerHtml = ``;

                    $.each(attributesJSON, function (index, element) {
                        let optionsHtml = ``;

                        $.each(element.values_data, function (i, value) {
                            let isSelected = existingAttributes.some(attr =>
                                attr.item_attribute_id === element.id && attr.value_id === value.id
                            );

                            optionsHtml += `
                                <option value='${value.id}' ${isSelected ? 'selected' : ''}>${value.value}</option>
                            `;
                        });

                        innerHtml += `
                            <tr>
                                <td>
                                    ${element.group_name}
                                    <input type="hidden" name="id" value="${element.id}">
                                </td>
                                <td>
                                    <select class="form-select select2" style="max-width:100% !important;">
                                        <option value="">Select</option>
                                        ${optionsHtml}
                                    </select>
                                </td>
                            </tr>
                        `;
                    });

                    $attributesTable.html(innerHtml);

                    // Bind change event
                    $attributesTable.find('select').off('change').on('change', function () {
                        changeAttributeVal($tr);
                    });
                    $attributesTable.find('select').select2();

                    // Open the attribute modal
                    $('#attribute').modal('show');

                } else {
                    $attributesTable.html(`
                        <tr>
                            <td colspan="2" class="text-center">Please select an item first</td>
                        </tr>
                    `);
                }
            });

        $('#submitAttributeBtn').on('click', function() {
            var selectedAttrs = [];
            $('#attributeModal .attribute-value:checked').each(function() {
                selectedAttrs.push({
                    attribute_id: $(this).data('attribute-id'),
                    attribute_name: $(this).data('attribute-name'),
                    attribute_value: $(this).val(),
                });
            });

            var currentRow = $('#attributeModal').data('currentRow');
            currentRow.data('selectedAttributes', selectedAttrs);
            updateAttributeBadges(currentRow);

            $('#attributeModal').modal('hide');
        });

	
		document.addEventListener("DOMContentLoaded", function () {
			const partDetails = document.querySelector('td[colspan="7"][rowspan="10"]');
			if (partDetails) {
				partDetails.onselectstart = function () {
					return false;
				};
				partDetails.addEventListener("mousedown", function (e) {
					e.preventDefault();
				});
			}
		});

				
		document.addEventListener("DOMContentLoaded", function () {

			const els = document.querySelectorAll('.part-details-section');

			els.forEach(el => {
				el.addEventListener("click", function (e) {
					e.stopPropagation(); 
					e.preventDefault(); 
				}, true); 
			});
		});
	</script>
@endsection