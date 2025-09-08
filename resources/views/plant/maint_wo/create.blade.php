@extends('layouts.app')
@section('content')

<div class="app-content content">
  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>

  <div class="content-wrapper container-xxl p-0">
    {{-- Header --}}
    <div class="content-header pocreate-sticky">
      <div class="row">
        <div class="content-header-left col-md-6 mb-2">
          <div class="row breadcrumbs-top">
            <div class="col-12">
              <h2 class="content-header-title float-start mb-0">Maintenance Order</h2>
              <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                  <li class="breadcrumb-item active">Add New</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
          <div class="form-group breadcrumb-right">
            <button class="btn btn-secondary btn-sm mb-50 mb-sm-0">
              <i data-feather="arrow-left-circle"></i> Back
            </button>
            <button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" id="save-draft-btn">
              <i data-feather="save"></i> Save as Draft
            </button>
            <button type="submit" form="maint-wo-form" class="btn btn-primary btn-sm" id="submit-btn">
              <i data-feather="check-circle"></i> Submit
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- Body --}}
    <div class="content-body">
      <form id="maint-wo-form" method="POST" action="{{ route('maint-wo.store') }}" enctype="multipart/form-data">
        @csrf

        {{-- Hidden fields (single copy) --}}
        <input type="hidden" name="book_code" id="book_code_input">
        <input type="hidden" name="doc_number_type" id="doc_number_type">
        <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
        <input type="hidden" name="doc_prefix" id="doc_prefix">
        <input type="hidden" name="doc_suffix" id="doc_suffix">
        <input type="hidden" name="doc_no" id="doc_no">
        <input type="hidden" name="document_status" id="document_status">

        <input type="hidden" name="spare_parts" id="spare_parts">
        <input type="hidden" name="selected_equipment_id" id="selected_equipment_id">
        <input type="hidden" name="selected_bom_id" id="selected_bom_id">
        <input type="hidden" name="checklist_data" id="checklist_data">
        <input type="hidden" name="equipment_details" id="equipment_details">
        <input type="hidden" name="equipment_maintenance_type_name" id="equipment_maintenance_type_name">

        {{-- readonly/selection data --}}
        <input type="hidden" name="defect_notification_id" id="defect_notification_id_hidden" value="">
        <input type="hidden" name="equipment_category" id="equipment_category_hidden" value="Machinery">
        <input type="hidden" name="equipment_name" id="equipment_name_hidden" value="">
        <input type="hidden" name="defect_type" id="defect_type_hidden" value="">
        <input type="hidden" name="problem" id="problem_hidden" value="">
        <input type="hidden" name="report_date_time" id="report_date_time_hidden" value="">
        <input type="hidden" name="reported_by" id="reported_by_hidden" value="">

        <section id="basic-datatable">
          <div class="row">
            {{-- Basic Info --}}
            <div class="col-12">
              <div class="card">
                <div class="card-body customernewsection-form">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                        <div>
                          <h4 class="card-title text-theme">Basic Information</h4>
                          <p class="card-text">Fill the details</p>
                        </div>
                      </div>
                    </div>

                    <div class="col-md-8">
                      <div class="row align-items-center mb-1">
                        <div class="col-md-3">
                          <label class="form-label">Series <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-5">
                          <select class="form-select" id="book_id" name="book_id" required>
                            @if(isset($series) && count($series) > 0)
                              @foreach($series as $index => $book)
                                <option value="{{ $book->id }}" {{ $index === 0 ? 'selected' : '' }}>
                                  {{ $book->book_code }}
                                </option>
                              @endforeach
                            @else
                              <option value="">No series available</option>
                            @endif
                          </select>
                        </div>
                      </div>

                      <div class="row align-items-center mb-1">
                        <div class="col-md-3">
                          <label class="form-label">Doc No <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-5">
                          <input type="text" class="form-control" id="document_number" name="document_number" required>
                        </div>
                      </div>

                      <div class="row align-items-center mb-1">
                        <div class="col-md-3">
                          <label class="form-label">Doc Date <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-5">
                          <input type="date" value="{{ date('Y-m-d') }}" class="form-control" id="document_date" name="document_date" min="{{ date('Y-m-d') }}" required>
                        </div>
                      </div>

                      <div class="row align-items-center mb-1">
                        <div class="col-md-3">
                          <label class="form-label">Location <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-5">
                          <select class="form-select" name="location_id" id="location_id" required>
                            @foreach($locations ?? [] as $location)
                              <option value="{{ $location->id }}">{{ $location->store_name }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>

                      {{-- Reference From --}}
                      <div class="row align-items-center mb-1 selection_section">
                        <div class="col-md-3">
                          <label class="form-label">Reference From</label>
                        </div>
                        <div class="col-md-5 action-button">
                          <input type="hidden" name="reference_type" id="reference_type" value="">
                          <button type="button" id="equipment_ref_btn" onclick="selectEquipmentReference()" data-bs-toggle="modal" data-bs-target="#reference" class="btn btn-outline-primary btn-sm mb-0 reference-btn">
                            <i data-feather="plus-square"></i> Equipment
                          </button>
                          <button type="button" id="defect_ref_btn" onclick="selectDefectNotificationReference()" data-bs-toggle="modal" data-bs-target="#defectlog" class="btn btn-outline-primary btn-sm mb-0 reference-btn">
                            <i data-feather="plus-square"></i> Defect Notification
                          </button>
                          <div id="reference_type_error" class="text-danger mt-1" style="display:none;">
                            Please select at least one reference type (Equipment or Defect Notification)
                          </div>
                        </div>
                      </div>

                    </div> {{-- /col-md-8 --}}
                  </div> {{-- /row --}}
                </div>
              </div>
            </div>

            {{-- Equipment Details --}}
            <div class="col-12">
              <div class="card quation-card">
                <div class="card-header newheader">
                  <h4 class="card-title">Equipment Details</h4>
                </div>
                <div class="card-body">
                  <div class="row">

                    <div class="col-md-3 basic-equipment-field">
                      <div class="mb-1">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <input type="text" placeholder="Select" value="" class="form-control ledgerselecct" id="equipment_category" readonly />
                      </div>
                    </div>

                    <div class="col-md-3 basic-equipment-field">
                      <div class="mb-1">
                        <label class="form-label">Equipment <span class="text-danger">*</span></label>
                        <input type="hidden" name="equipment_id" id="equipment_id" value="">
                        <input type="text" placeholder="Select Equipment" class="form-control ledgerselecct" id="equipment_name" readonly required>
                        {{-- If you open a selector elsewhere, keep button here if needed
                        <button type="button" class="btn btn-sm btn-outline-primary mt-1" data-bs-toggle="modal" data-bs-target="#reference">
                          <i data-feather="search"></i> Select Equipment
                        </button>
                        --}}
                      </div>
                    </div>

                    <div class="col-md-3 basic-equipment-field">
                      <div class="mb-1">
                        <label class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="equipment_maintenance_type_id" id="maintenance_type" disabled required>
                          @php
                            $allMaintenanceTypes = [];
                            foreach(($maintenanceTypesByEquipment ?? []) as $equipmentId => $types) {
                              foreach($types as $type) {
                                $allMaintenanceTypes[$type['id']] = $type['name'];
                              }
                            }
                          @endphp
                          @foreach($allMaintenanceTypes as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>

                    <div class="col-md-3 equipment-detail-field">
                      <div class="mb-1" id="defect_type_field">
                        <label class="form-label">Defect Type</label>
                        <select class="form-select" name="defect_type" id="defect_type_select">
                          <option value="">Select</option>
                          <option value="General Defect">General Defect</option>
                          <option value="Breakdown">Breakdown</option>
                          <option value="Quality-based">Quality-based</option>
                          <option value="Preventive">Preventive</option>
                          <option value="Corrective">Corrective</option>
                          <option value="Emergency">Emergency</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-3 equipment-detail-field">
                      <div class="mb-1" id="problem_field">
                        <label class="form-label">Problem <span class="text-danger">*</span></label>
                        <input type="text" value="Please resolve ASAP" disabled class="form-control" />
                      </div>
                    </div>

                    <div class="col-md-3 equipment-detail-field" id="priority_field">
                      <div class="mb-1">
                        <label class="form-label">Priority</label>
                        <select class="form-select" name="priority" required>
                          <option value="">Select Priority</option>
                          <option value="Low">Low</option>
                          <option value="Medium" selected>Medium</option>
                          <option value="High">High</option>
                          <option value="Critical">Critical</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-3 equipment-detail-field">
                      <div class="mb-1" id="report_date_field">
                        <label class="form-label">Report Date & Time</label>
                        {{-- display-only, do not parse server-side --}}
                        <input type="text" value="22-07-2025 | 02:30 PM" disabled class="form-control" />
                      </div>
                    </div>

                    <div class="col-md-3 equipment-detail-field">
                      <div class="mb-1" id="report_by_field">
                        <label class="form-label">Reported by</label>
                        <input type="text" value="Aniket" disabled class="form-control" />
                      </div>
                    </div>

                    <div class="col-md-9 equipment-detail-field">
                      <div class="mb-1" id="detailed_observations_field">
                        <label class="form-label">Detailed observations</label>
                        <textarea name="detailed_observations" class="form-control" id="detailed_observations" rows="3" placeholder="Enter detailed observations"></textarea>
                      </div>
                    </div>

                    <div class="col-md-3 equipment-detail-field" id="supporting_documents_field">
                      <div class="mb-1">
                        <label class="form-label">Supporting Documents <span class="text-danger">*</span></label><br/>
                        <div class="mt-50">
                          <input type="file" name="supporting_documents[]" class="form-control" multiple>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>

            {{-- Checklist & Spare Parts Tabs --}}
            <div class="col-12">
              <div class="card">
                <div class="card-body customernewsection-form">
                  <div class="border-bottom mb-2 pb-25">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="newheader">
                          <h4 class="card-title text-theme">Checklist and Defect Detail</h4>
                          <p class="card-text">Fill the details</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="step-custhomapp bg-light">
                    <ul class="nav nav-tabs my-25 custapploannav" role="tablist" id="main-tabs">
                      <li class="nav-item" id="checklist-tab">
                        <a class="nav-link" data-bs-toggle="tab" href="#payment">Checklist</a>
                      </li>
                      <li class="nav-item" id="spare-parts-tab">
                        <a class="nav-link active" data-bs-toggle="tab" href="#attachment">Spare Parts</a>
                      </li>
                    </ul>
                  </div>

                  <div class="tab-content pb-1">
                    {{-- Checklist tab --}}
                    <div class="tab-pane" id="payment">
                      <div class="row">
                        <div class="col-md-12">
                          <div class="table-responsive pomrnheadtffotsticky1">
                            <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                              <thead>
                                <tr>
                                  <th style="width:30px">#</th>
                                  <th width="250">Checklist</th>
                                  <th>Maintenance</th>
                                </tr>
                              </thead>
                              <tbody class="mrntableselectexcel1">
                                {{-- dynamically populated by JS (populateChecklistTable) --}}
                                <tr>
                                  <td>1</td>
                                  <td colspan="2" class="poprod-decpt p-50"><strong class="font-small-4">Greasing and Oiling</strong></td>
                                </tr>
                                <tr>
                                  <td></td>
                                  <td class="ps-1">Checklist 1</td>
                                  <td class="poprod-decpt">
                                    <input type="text" placeholder="Enter Text" class="form-control mw-100" />
                                  </td>
                                </tr>
                                <tr>
                                  <td></td>
                                  <td class="ps-1">Checklist 2</td>
                                  <td class="poprod-decpt">
                                    <div class="form-check form-check-primary custom-checkbox ms-50">
                                      <input type="checkbox" class="mt-25 form-check-input" id="Email">
                                      <label class="mb-50 mt-25 form-check-label" for="Email">Yes/No</label>
                                    </div>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>

                    {{-- Spare parts tab --}}
                      <div class="tab-pane active" id="attachment">
                      <div class="border-bottom mb-2 pb-25">
                        <div class="row">
                          <div class="col-md-6">
                            <div class="newheader">
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

                      <div class="row">
                        <div class="col-md-12">
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
														<th>Available Stock</th>
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
														<td><input type="number" class="available_stock form-control mw-100"
																name="available_stock[]"  readonly /></td>
													</tr>
												</tbody>
												<tfoot>


													<tr valign="top">
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
																		<span
																			class="badge rounded-pill badge-light-primary"><strong>Available Stock</strong>:
																			<span id="available_stock"></span></span>
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
                        </div>
                      </div>

                    </div>{{-- /tab-pane --}}
                  </div>{{-- /tab-content --}}
                </div>
              </div>
            </div>

          </div>
        </section>

        {{-- Upload + Remarks --}}
        <div class="row mt-2">
          <div class="col-md-4">
            <div class="mb-1">
              <label class="form-label">Upload Document</label>
              <input type="file" name="upload_file" class="form-control">
            </div>
          </div>
          <div class="col-md-12">
            <div class="mb-1">
              <label class="form-label">Final Remarks</label>
              <textarea rows="4" class="form-control" name="final_remark" placeholder="Enter Remarks here..."></textarea>
            </div>
          </div>
        </div>

        {{-- ===================== Modals (single copies) ===================== --}}

        {{-- Filter Modal --}}
        <div class="modal modal-slide-in fade filterpopuplabel" id="filter" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0">
              <div class="modal-header mb-1">
                <h5 class="modal-title">Apply Filter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
              </div>
              <div class="modal-body flex-grow-1">
                <div class="mb-1">
                  <label class="form-label" for="fp-range">Select Date</label>
                  <input type="text" id="fp-range" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                </div>
                <div class="mb-1">
                  <label class="form-label">Series</label>
                  <select class="form-select"><option>Select</option></select>
                </div>
                <div class="mb-1">
                  <label class="form-label">BOM Name</label>
                  <select class="form-select select2"><option>Select</option></select>
                </div>
                <div class="mb-1">
                  <label class="form-label">Status</label>
                  <select class="form-select">
                    <option>Select</option>
                    <option>Active</option>
                    <option>Inactive</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer justify-content-start">
                <button type="button" class="btn btn-primary data-submit mr-1">Apply</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              </div>
            </form>
          </div>
        </div>

        {{-- Approved/Close Maintenance Modal --}}
        <div class="modal fade" id="approved" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <div>
                  <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal">Close the Maintenance</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body pb-2">
                <div class="row mt-1">
                  <div class="col-md-12">
                    <div class="mb-1">
                      <label class="form-label">Remarks <span class="text-danger">*</span></label>
                      <textarea class="form-control"></textarea>
                    </div>
                    <div class="mb-1">
                      <label class="form-label">Upload Document</label>
                      <input type="file" class="form-control" />
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer justify-content-center">
                <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                <button type="reset" class="btn btn-primary">Submit</button>
              </div>
            </div>
          </div>
        </div>

        {{-- Select Equipment (Reference) Modal --}}
        <div class="modal fade text-start" id="reference" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width:1000px">
            <div class="modal-content">
              <div class="modal-header">
                <div>
                  <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal">Select Equipment</h4>
                  <p class="mb-0">Select from the below list</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col">
					<div class="mb-1">
						<label class="form-label">Equipment</label>
						<select class="form-control ledgerselecct" name="equipment_id">
							<option value="">Select Equipment</option>
							@foreach($equipments as $equipment)
								<option value="{{ $equipment->id }}">{{ $equipment->name }}</option>
							@endforeach
						</select>
					</div>
				</div>
				
                  <div class="col"><div class="mb-1"><label class="form-label">Maintenance Type</label>
					<select class="form-control ledgerselecct" name="maintenance_type_id">
						<option value="">Select Maintenance Type</option>
						@php
                            $allMaintenanceTypes = [];
                            foreach(($maintenanceTypesByEquipment ?? []) as $equipmentId => $types) {
                              foreach($types as $type) {
                                $allMaintenanceTypes[$type['id']] = $type['name'];
                              }
                            }
                          @endphp
                          @foreach($allMaintenanceTypes as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                          @endforeach
					</select>
					</div></div>
                  <div class="col"><div class="mb-1">
					<label class="form-label">Maint. BOM</label>
					<select class="form-control ledgerselecct" name="maintenance_bom_id">
						<option value="">Select Maint. BOM</option>
						@foreach($maintenanceBoms as $bomData)
							<option value="{{ $bomData['id'] }}">{{ $bomData['display_name'] }}</option>
						@endforeach
					</select>
					</div></div>
                  <div class="col mb-1"><label class="form-label">&nbsp;</label><br/><button type="button" id="equipmentSearchBtn" class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button></div>

                  <div class="col-md-12">
                    <div class="table-responsive">
                      <table class="mt-1 table table-striped po-order-detail">
                        <thead>
                          <tr>
                            <th width="62" class="customernewsection-form">
                              <div class="form-check form-check-primary custom-checkbox">
                                <input type="checkbox" class="form-check-input sp-select">
                                <label class="form-check-label" for="Email"></label>
                              </div>
                            </th>
                            <th>Equipment</th>
                            <th>Maintenance Type</th>
                            <th>BOM</th>
                            <th>Series</th>
                            <th>Doc No</th>
							<th>Due Date</th>
                          </tr>
                        </thead>
                        <tbody id="eqptTable">
                          {{-- populate via JS --}}
                        </tbody>
                      </table>
                    </div>
                  </div>

                </div>
              </div>
              <div class="modal-footer text-end">
                <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
                <button id="equipment_process_btn" onclick="processEquipmentSelection()" class="btn btn-primary btn-sm"><i data-feather="check-circle"></i> Process</button>
              </div>
            </div>
          </div>
        </div>

        {{-- Defect Log Modal --}}
        <div class="modal fade text-start" id="defectlog" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width:1000px">
            <div class="modal-content">
              <div class="modal-header">
                <div>
                  <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal">Select Defect</h4>
                  <p class="mb-0">Select from the below list</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <!-- Filters -->
                  <div class="col">
                    <div class="mb-1">
                      <label class="form-label">Equipment</label>
                      <select class="form-control ledgerselecct" name="equipment_id">
                        <option value="">Select Equipment</option>
						@foreach($equipments as $equipment)
							<option value="{{ $equipment->id }}">{{ $equipment->name }}</option>
						@endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col">
                    <div class="mb-1">
                      <label class="form-label">Defect Type</label>
                      <select class="form-control ledgerselecct" name="defect_type_id">
                        <option value="">Select Defect Type</option>
						@foreach($defectTypes as $defectType)
							<option value="{{ $defectType->id }}">{{ $defectType->name }}</option>
						@endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col">
                    <div class="mb-1">
                      <label class="form-label">Priority</label>
                      <select class="form-select" name="priority">
                        <option value="">Select Priority</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                      </select>
                    </div>
                  </div>
                  <div class="col">
                    <div class="mb-1">
                      <label class="form-label">Series</label>
                      <select class="form-select" id="series_filter" name="series">
                        <option value="">Select Series</option>
						@foreach($series as $book)
							<option value="{{ $book->id }}">{{ $book->book_code }}</option>
						@endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col mb-1">
                    <label class="form-label">&nbsp;</label><br/>
                    <button class="btn btn-warning btn-sm" id="defect_search_btn">
                      <i data-feather="search"></i> Search
                    </button>
                  </div>

                  <!-- Table -->
                  <div class="col-md-12 mt-3">
                    <div class="table-responsive">
                      <table class="mt-1 table table-striped po-order-detail">
                        <thead>
                          <tr>
                            <th class="customernewsection-form">
                              <div class="form-check form-check-primary custom-radio">
                                <input type="radio" class="form-check-input defect-radio" name="defectRadio" id="defect_header" disabled>
                                <label class="form-check-label" for="defect_header"></label>
                              </div>
                            </th>
                            <th>Date</th>
                            <th>Series</th>
                            <th>Doc No</th>
                            <th>Equipment</th>
                            <th>Defect Type</th>
                            <th>Priority</th>
                            <th>Problem</th>
                            <th>Reported By</th>
                          </tr>
                        </thead>
                        <tbody id="defectTable">
                          <tr class="trail-bal-tabl-none">
                            <td colspan="9" class="text-center">No defect notifications found</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>

                </div>
              </div>
              <div class="modal-footer text-end">
                <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                  <i data-feather="x-circle"></i> Cancel
                </button>
                <button id="defect_process_btn" onclick="processDefectSelection()" class="btn btn-primary btn-sm">
                  <i data-feather="check-circle"></i> Process
                </button>
              </div>
            </div>
          </div>
        </div>


        {{-- Attribute Modal --}}
		<div class="modal fade" id="attribute" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Select Attribute</h1>
                <p class="text-center">Enter the details below.</p>

                <div class="table-responsive-md customernewsection-form">
                  <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id="attributes_table_modal" item-index="">
                    <thead>
                      <tr>
                        <th>Attribute Name</th>
                        <th>Attribute Value</th>
                      </tr>
                    </thead>
                    <tbody id="attribute_table"><!-- populated by JS --></tbody>
                  </table>
                </div>
              </div>
              <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('attribute');">Cancel</button>
                <button type="button" class="btn btn-primary submitAttributeBtn" onclick="closeModal('attribute');">Select</button>
              </div>
            </div>
          </div>
        </div>

        {{-- Remarks Modal (kept single) --}}
        <div class="modal fade" id="Remarks" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1">Add/Edit Remarks</h1>
                <p class="text-center">Enter the details below.</p>
                <div class="row mt-2">
                  <div class="col-md-12 mb-1">
                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                    <textarea class="form-control" placeholder="Enter Remarks"></textarea>
                  </div>
                </div>
              </div>
              <div class="modal-footer justify-content-center">
                <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                <button type="reset" class="btn btn-primary">Submit</button>
              </div>
            </div>
          </div>
        </div>



      </form>
    </div>

  </div>
</div>

@endsection

@section('styles')
<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    color: white;
    font-size: 16px;
}
</style>
@endsection

@section('scripts')
	<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
    @include('plant.maint_wo.common-js-route',["wo" => isset($wo) ? $wo : null, "route_prefix" => "maint-wo"])
    <script src="{{ asset('assets/js/modules/maint-wo/common-script.js')}}"></script>
  	
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

		$(".mrntableselectexcel tr").click(function () {
			$(this).addClass('trselected').siblings().removeClass('trselected');
			value = $(this).find('td:first').html();
		});

		$(document).on('keydown', function (e) {
			// Only handle arrow keys and only when not typing in input/textarea/select
			if ((e.which == 38 || e.which == 40) && 
				!$(e.target).is('input, textarea, select') && 
				$('.trselected').length > 0) {
				
				e.preventDefault(); // Prevent default arrow key behavior
				
				if (e.which == 38) {
					$('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
				} else if (e.which == 40) {
					$('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
				}
				$('html, body').scrollTop($('.trselected').offset().top - 200);
				updateFooterFromSelected();
			}
		});
		$(document).on('click', '.mrntableselectexcel tr', function () {
			$(this).addClass('trselected').siblings().removeClass('trselected');
			$('html, body').scrollTop($(this).offset().top - 200);
			updateFooterFromSelected();
			console.log('Spare part row clicked, updating footer...');
		});
		function updateFooterFromSelected() {
			let $selected = $('.trselected');
			console.log('updateFooterFromSelected called, selected rows:', $selected.length);
			
			if ($selected.length) {
				console.log("Selected row found, processing...");
				
				// Get basic part details
				let partName = $selected.find('.item_name').val() || 'N/A';
				let uomText = $selected.find('.uom option:selected').text() || $selected.find('.uom').val() || 'N/A';
				let qty = $selected.find('.qty').val() || '0';
				let availableStock = $selected.find('.available_stock').val() || '0';
				
				console.log('Part details extracted:', {
					partName: partName,
					uomText: uomText,
					qty: qty,
					availableStock: availableStock
				});
				
				// Update part details display
				$('#part_name').text(partName);
				$('#uom').text(uomText);
				$('#qty').text(qty);
				$('#available_stock').text(availableStock);
				
				console.log('Part details updated in DOM');
				
				let $selectElement = $selected.find('.item_code');
				let $badgesContainer = $('#attributes_badges'); // container for badges

				// Handle attributes - check for both static and AJAX loaded data
				let attributesData = [];
				
				// First try to get from AJAX loaded data (attribute-enriched hidden field)
				let $enrichedInput = $selected.find('.attribute-enriched');
				if ($enrichedInput.length && $enrichedInput.val()) {
					try {
						attributesData = JSON.parse($enrichedInput.val());
						console.log('Using AJAX loaded attributes:', attributesData);
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
								let valObj = element.values_data.find(v => v.id === selectedVal);
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
															<td><input type="number" class="available_stock form-control mw-100"
																	name="available_stock[]"  readonly /></td>
														</tr>																  `;
			$('.mrntableselectexcel').append(newRow);
			initAutoForItem('.item_code');

		});
		$('#delete').on('click', function () {
			let $rows = $('.mrntableselectexcel tr');
			let $checked = $rows.find('.row-check:checked');

			// // Prevent deletion if only one row exists
			// if ($rows.length <= 1) {
			// 	showToast('error', 'At least one row is required.');
			// 	return;
			// }

			// // Prevent deletion if checked rows would remove all
			// if ($rows.length - $checked.length < 1) {
			// 	showToast('error', 'You must keep at least one row.');
			// 	return;
			// }

			// Remove only the checked rows
			$checked.closest('tr').remove();

		});
		$('#checkAll').on('change', function () {
			let isChecked = $(this).is(':checked');
			$('.mrntableselectexcel .row-check').prop('checked', isChecked);
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
						available_stock: row.find('.available_stock').val() || 0,
					};
					allRows.push(rowData);
				}
			});

			$('#spare_parts').val(JSON.stringify(allRows));
		}


		document.getElementById('save-draft-btn').addEventListener('click', function () {
			// No validation required for draft - save as is
			$('.preloader').show();
			document.getElementById('document_status').value = 'draft';
			updateJsonData();
			document.getElementById('maint-wo-form').submit();

		});


		$('#maint-wo-form').on('submit', function (e) {
			e.preventDefault(); // Always prevent default first
			
			// Validate reference type selection
			let referenceType = $('#reference_type').val();
			if (!referenceType) {
				showToast('error', 'Please select a reference type (Equipment or Defect Notification)');
				$('#reference_type_error').show();
				return false;
			}
			
			$('.preloader').show();
			document.getElementById('document_status').value = 'submitted';
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


		
		function initAutoForItem(selector, type) {
			
			console.log("check the seelector and type",selector,type);
			
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
					console.log("check the selectedItemIds",selectedItemIds);

					// Filter itemsData by search term AND exclude already selected items
					let filtered = itemsData.filter(item => {
						// Get current input's item_id value:
						let currentItemId = $(selector).closest('tr').find('.item_id').val();
						
						// Check if this item is selected in other rows (excluding current row)
						let isSelectedElsewhere = false;
						$('.item_id').each(function() {
							let val = $(this).val();
							let $currentRow = $(selector).closest('tr');
							let $thisRow = $(this).closest('tr');
							
							// Only consider it selected elsewhere if it's in a different row
							if (val && val === item.id.toString() && !$thisRow.is($currentRow)) {
								isSelectedElsewhere = true;
							}
						});

						// Include item if:
						// - it matches the search term
						// - and (not selected elsewhere OR is the current selected item in this row)
						return (item.item_code.toLowerCase().includes(term) || item.item_name.toLowerCase().includes(term)) &&
							(!isSelectedElsewhere || item.id.toString() === currentItemId);
					});

					console.log("check the filtered",filtered);
					
					let results = filtered.map(item => ({
						id: item.id,
						label: `${item.item_code} - ${item.item_name}`,
						code: item.item_code,
						item_id: item.id,
						item_name: item.item_name,
						uom_name: item.uom_name,
						uom_id: item.uom_id,
						attr: item.item_attributes || [],
						available_stock: item.available_stock || 0
					}));

					console.log("check the results",results);
					
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
					let availableStock = ui.item.available_stock || 0;

					$input.attr('data-name', itemName);
					$input.attr('data-code', itemCode);
					$input.attr('data-attr', JSON.stringify(attr));
					$input.attr('data-id', itemId);
					$input.closest('tr').find('.item_id').val(itemId);
					$input.closest('tr').find('.item_name').val(itemName);
					$input.val(itemCode);

					let uomOption = `<option value="${uomId}">${uomName}</option>`;
					$input.closest('tr').find('.uom').empty().append(uomOption);
					$input.closest('tr').find('.available_stock').val(availableStock);

					// Update attribute badges using BOM-style function
					let $currentRow = $input.closest('tr');
					updateAttributeBadges($currentRow);
					
					// Automatically open attribute modal if item has attributes
					if (attr && attr.length > 0) {
						setTimeout(() => {
							// Trigger attribute modal by simulating click on attribute button logic
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
									$.each(element.values_data, function (i, value) {
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
						$(this).closest('tr').find('.available_stock').val(0);
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
					$(this).closest('tr').find(".available_stock").val(0);
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

			// Find the attributes table and tbody
			const attributesTable = document.getElementById("attribute_table");
			const tbody = attributesTable;

			let selectedAttributes = [];

			Array.from(tbody.rows).forEach(row => {
				const hiddenInputAttr = row.querySelector('input[type="hidden"][name="id"]');
				const selectElement = row.querySelector("select");

				if (hiddenInputAttr && selectElement) {
					const attributeId = parseInt(hiddenInputAttr.value, 10);
					const selectedVal = parseInt(selectElement.value, 10);
					
					// Get the attribute name from the row
					const attributeNameCell = row.querySelector('td:first-child');
					const attributeName = attributeNameCell ? attributeNameCell.textContent.trim() : '';
					
					// Get the selected value text
					const selectedOption = selectElement.options[selectElement.selectedIndex];
					const selectedValueText = selectedOption ? selectedOption.textContent.trim() : '';

					if (!isNaN(attributeId) && !isNaN(selectedVal) && selectedVal > 0) {
						selectedAttributes.push({
							item_attribute_id: attributeId,
							value_id: selectedVal,
							attribute_name: attributeName,
							attribute_value: selectedValueText
						});
					}
				}
			});

			// Update hidden input with JSON
			hiddenInput.val(JSON.stringify(selectedAttributes));
			console.log(selectedAttributes);
		}

		$(document).on('click', '.attributeBtn', function (e) {
			let $tr = $(this).closest('tr');
			let $selectElement = $tr.find('.item_code');
			let $attributesTable = $('#attribute_table'); // modal table
			$attributesTable.data('currentRow', $tr);
			
			// Handle both scenarios: equipment selection and new row addition
			let attributesJSON = [];
			let $hiddenInput = $tr.find('.attribute');
			let existingAttributes = [];
			
			// Scenario 1: Equipment selection (uses .attribute-enriched hidden field with all_values structure)
			let $enrichedInput = $tr.find('.attribute-enriched');
			if ($enrichedInput.length && $enrichedInput.val() && $enrichedInput.val() !== '[]') {
				try {
					let attributeData = JSON.parse($enrichedInput.val());
					// Convert from all_values structure to values_data structure
					attributesJSON = attributeData.map(function(attr) {
						return {
							id: attr.item_attribute_id,
							group_name: attr.group_name,
							values_data: attr.all_values || []
						};
					});
					existingAttributes = attributeData.map(function(attr) {
						return {
							item_attribute_id: attr.item_attribute_id,
							value_id: attr.selected_value_id
						};
					});
				} catch (e) {
					console.error('Error parsing enriched attribute data:', e);
				}
			}
			
			// Scenario 2: New row addition (uses data-attr attribute with values_data structure)
			if (!attributesJSON.length && $selectElement.val() !== "") {
				try {
					attributesJSON = JSON.parse($selectElement.attr('data-attr') || '[]');
					existingAttributes = $hiddenInput.length && $hiddenInput.val()
						? JSON.parse($hiddenInput.val())
						: [];
				} catch (e) {
					console.error('Error parsing data-attr:', e);
				}
			}

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

			$attributesTable.find('select').off('change').on('change', function () {
				changeAttributeVal($tr);
			});
			$attributesTable.find('select').select2();
		});
		function closeModal(id) {
			$('#' + id).modal('hide');
		}

		// Simple functions for equipment selection
		function selectEquipmentReference() {
			loadModal('eqpt');
			$('#reference_type').val('equipment');
			$('#reference_type_error').hide();
			$('#equipment_ref_btn').removeClass('btn-outline-primary').addClass('btn-primary');
			$('#defect_ref_btn').removeClass('btn-primary').addClass('btn-outline-primary');
			
			// Show only basic equipment fields, hide detail fields
			$('.basic-equipment-field').show();
			$('.equipment-detail-field').hide();
			
			// Make basic fields read-only immediately
			$('#equipment_category').prop('readonly', true);
			$('#equipment_name').prop('readonly', true);
			// $('#maintenance_type').prop('disabled', true);
			
			// Show checklist tab when equipment is selected
			$('#checklist-tab').show();
		}
		
		function selectDefectNotificationReference() {
			loadModal('defect');
			$('#reference_type').val('defect_notification');
			$('#reference_type_error').hide();
			$('#defect_ref_btn').removeClass('btn-outline-primary').addClass('btn-primary');
			$('#equipment_ref_btn').removeClass('btn-primary').addClass('btn-outline-primary');
			
			// Show all equipment detail fields but make them read-only
			$('.basic-equipment-field').show();
			$('.equipment-detail-field').show();
			
			// Make fields read-only for defect notification (they will be populated from selected defect)
			$('#equipment_category').prop('readonly', true);
			$('#equipment_name').prop('readonly', true);
			// $('#maintenance_type').prop('disabled', true); // Keep maintenance type enabled for user selection
			
			// Also disable other equipment detail fields
			$('#defect_type_select').prop('disabled', true);
			$('#priority_field select').prop('disabled', true);
			$('#problem_field input').prop('readonly', true);
			$('#detailed_observations_field textarea').prop('readonly', true);
			$('#report_by_field input').prop('readonly', true);
			$('#supporting_documents_field input').prop('disabled', true);
			
			// Hide checklist tab and show only spare parts tab
			$('#checklist-tab').hide();
			$('#spare-parts-tab a').tab('show'); // Activate spare parts tab
		}

		function processEquipmentSelection() {
			var selectedEquipment = $('input[name="equipment_radio"]:checked');
			
			if (selectedEquipment.length === 0) {
				// Show toaster notification
				showToast('error', 'Please select at least one equipment');
				return false; // Don't close modal
			}
			
			// Get selected equipment data
			var equipmentRow = selectedEquipment.closest('tr');
			var equipmentName = equipmentRow.find('td').eq(0).find('strong').text().trim();
			if (!equipmentName) {
				equipmentName = equipmentRow.find('td').eq(0).text().trim();
			}
			var eqpt = selectedEquipment.data('eqpt');
			
			// Get equipment and BOM IDs for AJAX call
			const equipmentId = selectedEquipment.val();
			const bomId = selectedEquipment.data('bom-id');
			
			// Populate equipment fields
			$('#equipment_name').val(selectedEquipment.data('equipment-name'));
			$('#equipment_id').val(selectedEquipment.data('equipment-id'));
			$('#selected_equipment_id').val(selectedEquipment.data('equipment-id')); // Store for maintenance type handler
			$('#maintenance_type').val(selectedEquipment.data('maintenance-type'));
			
			// Keep only basic equipment fields visible and read-only for equipment selection
			$('.equipment-detail-field').hide();
			$('.basic-equipment-field').show();
			$('#equipment_category').prop('readonly', true);
			$('#equipment_name').prop('readonly', true);
			$('#maintenance_type').prop('disabled', true);
			
			console.log('Equipment selected:', equipmentName);
			console.log('Equipment detail fields shown');
			
			// Fetch spare parts via AJAX
			const maintenanceTypeId = selectedEquipment.data('maintenance-type');
			if (equipmentId && maintenanceTypeId) {
				fetchEquipmentSpareParts(equipmentId, maintenanceTypeId);
			}
			
			// Close modal manually
			$('#reference').modal('hide');
			
			return true;
		}

		function processDefectSelection() {
			let selectedDefect = $('input.defect-radio:checked').attr('id');
      let onlyNumber = selectedDefect.replace("defect_row_", "");
  
			if (onlyNumber === "") {
				showToast('error', 'Please select a defect notification');
				return false;
			}

			var defectId = onlyNumber;

      

			$('#defect_process_btn')
				.prop('disabled', true)
				.html('<span class="spinner-border spinner-border-sm"></span> Loading...');

			$.ajax({
				url: "{{ route('defect-notification.get', 'PLACEHOLDER') }}".replace('PLACEHOLDER', defectId),
				type: 'GET',
				success: function(response) {
					if (response.status && response.data) {
						var defect = response.data;

						// Equipment
						if (defect.equipment) {
							$('#equipment_id').val(defect.equipment.id);
							$('#selected_equipment_id').val(defect.equipment.id); // Store for maintenance type handler
							$('#equipment_name').val(defect.equipment.document_number || defect.equipment.name || '');
						}

						// Defect Type
						if (defect.defect_type) {
							var defectTypeSelect = $('#defect_type_select');
							if (defectTypeSelect.find('option[value="' + defect.defect_type.id + '"]').length === 0) {
								defectTypeSelect.append('<option value="' + defect.defect_type.id + '">' + defect.defect_type.name + '</option>');
							}
							defectTypeSelect.val(defect.defect_type.id).prop('disabled', true);
						}

						// Category
						if (defect.category) {
							$('#equipment_category').val(defect.category.name);
						}

						// Book
						if (defect.book) {
							$('#book_code').val(defect.book.book_code);
						}

						// Location
						if (defect.location) {
							$('#location_name').val(defect.location.name);
						}

						// Priority
						if (defect.priority) {
							$('#priority_field select').val(defect.priority).prop('disabled', true);
						}

						// Problem
						if (defect.problem) {
							$('#problem_field input').val(defect.problem).prop('disabled', true);
						}

						// Detailed Observation
						if (defect.detailed_oberservation) {
							$('#detailed_observation').val(defect.detailed_oberservation).prop('disabled', true);
						}

						// Report Date
						var reportDate = defect.report_date_time ? defect.report_date_time.replace('T', ' ').split('.')[0] : '';
						$('#report_date_field input').val(reportDate).prop('disabled', true);

						if (defect.detailed_oberservation) {
							$('#detailed_observations').val(defect.detailed_oberservation);
						} else {
							$('#detailed_observations').val('');
						}

						$('#supporting_documents_field').empty();
						var supportingDiv = $('#supporting_documents_field');
						if (defect.attachment) {
							supportingDiv.show();
							var iconContainer = supportingDiv.find('.mt-50');
							iconContainer.empty();
							var icon = $('<i>', { 'data-feather': 'file-text', class: 'font-large-1 me-25' });
							iconContainer.append(icon);
							if (typeof feather !== 'undefined') {
								feather.replace();
							}
						} else {
							supportingDiv.remove();
						}

						// Populate Maintenance Type dropdown
						if (response.maintenance_types && response.maintenance_types.length > 0) {
							var maintenanceTypeSelect = $('#maintenance_type');
							maintenanceTypeSelect.empty();
							maintenanceTypeSelect.append('<option value="">Select Maintenance Type</option>');
							
							$.each(response.maintenance_types, function(index, type) {
								maintenanceTypeSelect.append('<option value="' + type.id + '" data-name="' + type.name + '">' + type.name + '</option>');
							});
							
							maintenanceTypeSelect.prop('disabled', false);
							console.log('Maintenance types populated:', response.maintenance_types.length);
						} else {
							$('#maintenance_type').empty().append('<option value="">No maintenance types available</option>').prop('disabled', true);
							console.log('No maintenance types available for this equipment');
						}

						// Hidden fields
						$('#defect_notification_id_hidden').val(defect.id);
						$('#equipment_name_hidden').val(defect.equipment ? defect.equipment.document_number : '');
						$('#defect_type_hidden').val(defect.defect_type ? defect.defect_type.name : '');
						$('#problem_hidden').val(defect.problem);
						$('#report_date_time_hidden').val(reportDate);
						$('#reported_by_hidden').val(defect.created_by || '');

						// Close modal
						$('#defectlog').modal('hide');

						showToast('success', 'Defect notification selected successfully');
					} else {
						showToast('error', 'Invalid defect data received');
					}
				},
				error: function(err) {
					console.error(err);
					showToast('error', 'Failed to load defect details');
				},
				complete: function() {
					$('#defect_process_btn').prop('disabled', false).html('<i data-feather="check-circle"></i> Process');
				}
			});

			return true;
		}

		function showEquipmentFields() {
			console.log('showEquipmentFields() called');
			
			// Hide all equipment detail fields first
			$('.basic-equipment-field').hide();
			$('.equipment-detail-field').hide();
			console.log('All fields hidden');
			
			// Show only basic equipment fields (Category, Equipment, Maintenance Type)
			$('.basic-equipment-field').show();
			console.log('Basic equipment fields shown, count:', $('.basic-equipment-field:visible').length);
			
			// Enable the fields for user interaction
			$('#equipment_category').prop('readonly', true); // Keep category readonly with default value
			$('#equipment_name').prop('readonly', true); // Keep equipment readonly until selected
			$('#maintenance_type').prop('disabled', false); // Enable maintenance type selection
			
			// Clear any previous values from hidden inputs for defect-related fields
			$('#defect_type_hidden').val('');
			$('#problem_hidden').val('');
			$('#report_date_time_hidden').val('');
			$('#reported_by_hidden').val('');
			
			console.log('Equipment fields setup complete');
		}

		// function showDefectNotificationFields() {
		// 	// Show all equipment detail fields
		// 	$('.equipment-detail-field').show();
			
		// 	// Set all fields as readonly with default values
		// 	$('#defect_type_select').prop('disabled', true).val('General Defect');
		// 	$('#defect_type_hidden').val('General Defect');
			
		// 	$('#problem_field input').prop('disabled', true).val('Please resolve ASAP');
		// 	$('#problem_hidden').val('Please resolve ASAP');
			
		// 	$('#priority_field select').prop('disabled', true).val('High');
			
		// 	$('#report_date_field input').prop('disabled', true).val('22-07-2025 | 02:30 PM');
		// 	$('#report_date_time_hidden').val('22-07-2025 | 02:30 PM');
			
		// 	$('#report_by_field input').prop('disabled', true).val('Aniket');
		// 	$('#reported_by_hidden').val('Aniket');
			
		// 	$('#detailed_observations_field textarea').prop('readonly', true).val('Defect notification requires immediate attention');
			
		// 	$('#supporting_documents_field input').prop('disabled', false); // Keep file upload enabled
		// }

		// function showDefectNotificationFields() {
		// 	// Show all equipment detail fields
		// 	$('.equipment-detail-field').show();
			
		// 	// Set all fields as readonly with default values
		// 	$('#defect_type_select').prop('disabled', true).val('General Defect');
		// 	$('#defect_type_hidden').val('General Defect');
			
		// 	$('#problem_field input').prop('disabled', true).val('Please resolve ASAP');
		// 	$('#problem_hidden').val('Please resolve ASAP');
			
		// 	$('#priority_field select').prop('disabled', true).val('High');
			
		// 	$('#report_date_field input').prop('disabled', true).val('22-07-2025 | 02:30 PM');
		// 	$('#report_date_time_hidden').val('22-07-2025 | 02:30 PM');
			
		// 	$('#report_by_field input').prop('disabled', true).val('Aniket');
		// 	$('#reported_by_hidden').val('Aniket');
			
		// 	$('#detailed_observations_field textarea').prop('readonly', true).val('Defect notification requires immediate attention');
			
		// 	$('#supporting_documents_field input').prop('disabled', false); // Keep file upload enabled
		// }


		// Maintenance Type change handler to update checklist
		$(document).on('change', '#maintenance_type', function() {
			var maintenanceTypeId = $(this).val();
			var maintenanceTypeName = $(this).find('option:selected').data('name') || $(this).find('option:selected').text();
			var equipmentId = $('#selected_equipment_id').val();
			
			// Store maintenance type name in hidden field
			$('#equipment_maintenance_type_name').val(maintenanceTypeName);
			
			if (maintenanceTypeId && equipmentId) {
				// Clear existing checklist
				$('#checklistTableBody').empty();
				
				// Show loading state
				$('#checklistTableBody').html('<tr><td colspan="3" class="text-center">Loading checklists...</td></tr>');
				
				$.ajax({
					url: "{{ route('defect-notification.get-checklists') }}",
					type: 'POST',
					data: {
						_token: $('meta[name="csrf-token"]').attr('content'),
						equipment_id: equipmentId,
						maintenance_type_id: maintenanceTypeId
					},
					success: function(response) {
						$('#checklistTableBody').empty();
						
						if (response.status && response.checklists && response.checklists.length > 0) {
							$.each(response.checklists, function(index, checklist) {
								var inputField = '';
								if (checklist.type === 'boolean') {
									inputField = '<input type="checkbox" class="form-check-input" name="checklist[' + checklist.id + ']" value="1">';
								} else {
									inputField = '<input type="text" class="form-control" name="checklist[' + checklist.id + ']" placeholder="Enter value">';
								}
								
								var row = '<tr>' +
									'<td>' + (index + 1) + '</td>' +
									'<td>' + checklist.name + '</td>' +
									'<td>' + inputField + '</td>' +
									'</tr>';
								
								$('#checklistTableBody').append(row);
							});
							console.log('Checklists loaded:', response.checklists.length);
						} else {
							$('#checklistTableBody').html('<tr><td colspan="3" class="text-center text-muted">No checklists available for this maintenance type</td></tr>');
						}
					},
					error: function(xhr, status, error) {
						console.error('Error loading checklists:', error);
						$('#checklistTableBody').html('<tr><td colspan="3" class="text-center text-danger">Error loading checklists</td></tr>');
					}
				});
			} else {
				$('#checklistTableBody').html('<tr><td colspan="3" class="text-center text-muted">Please select equipment and maintenance type</td></tr>');
			}
		});

		//Search function for the defect modal 

		$(document).ready(function() {
			$('#defect_search_btn').on('click', function(e) {
				e.preventDefault();

				var equipmentId = $('select[name="equipment_id"]').val();
				var defectTypeId = $('select[name="defect_type_id"]').val();
				var priority = $('select[name="priority"]').val();
				var series = $('select[name="series"]').val();

				$.ajax({
					url: '/plant/maint-wo/filter',
					method: 'POST',
					data: {
						type: 'defect',
						equipment_id: equipmentId,
						defect_type_id: defectTypeId,
						priority: priority,
						series_code: series,
						_token: $('meta[name="csrf-token"]').attr('content')
					},
					beforeSend: function() {
						
					},
					success: function(response) {
						console.log("Defect filter response:", response);
						
						if(response && response.length > 0) {
							var tbody = '';
							response.forEach(function(defect) {
								tbody += `<tr>
									<td class="customernewsection-form">
										<div class="form-check form-check-primary custom-radio">
											<input type="radio" class="form-check-input" name="defect_selection" id="defect_row_${defect.id}"
												value="${defect.id}"
												data-defect-id="${defect.id}"
												data-equipment-id="${defect.equipment?.id ?? ''}"
												data-equipment-name="${defect.equipment?.name ?? 'N/A'}"
												data-defect-type="${defect.defect_type?.name ?? 'N/A'}"
												data-priority="${defect.priority ?? ''}"
												data-problem="${defect.problem ?? ''}"
												data-reported-by="${defect.creator?.name ?? 'N/A'}">
											<label class="form-check-label" for="defect_row_${defect.id}"></label>
										</div>
									</td>
									<td><strong>${defect.document_date ? formatDate(defect.document_date) : 'N/A'}</strong></td>
									<td>${defect.book?.book_code ?? 'N/A'}</td>
									<td>${defect.document_number ?? 'N/A'}</td>
									<td>${defect.equipment?.name ?? 'N/A'}</td>
									<td>${defect.defect_type?.name ?? 'N/A'}</td>
									<td>${defect.priority ?? ''}</td>
									<td>${defect.problem ?? ''}</td>
									<td>${defect.creator?.name ?? 'N/A'}</td>
								</tr>`;
							});
							$('.po-order-detail tbody').html(tbody);
							feather.replace(); // re-render Feather icons
						} else {
							$('.po-order-detail tbody').html('<tr><td colspan="9" class="text-center">No defect notifications found</td></tr>');
						}
					},
					error: function(xhr) {
						console.error(xhr);
						showToast('error', 'Failed to fetch filtered defects.');
					},
					complete: function() {
					}
				});
			});

			function formatDate(dateStr) {
				var date = new Date(dateStr);
				var day = ("0" + date.getDate()).slice(-2);
				var month = ("0" + (date.getMonth() + 1)).slice(-2);
				var year = date.getFullYear();
				return `${day}-${month}-${year}`;
			}

		// Equipment Search Button Handler
		$('#equipmentSearchBtn').on('click', function() {
			const equipmentId = $('select[name="equipment_id"]').val();
			const maintenanceTypeId = $('select[name="maintenance_type_id"]').val();
			const bomId = $('select[name="maintenance_bom_id"]').val();

			if (!equipmentId) {
				Swal.fire({
					title: 'Missing Information',
					text: 'Please select Equipment before searching.',
					icon: 'warning'
				});
				return;
			}

			// Show loading state
			$(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

			// Call filter method for equipment
			$.ajax({
				url: '/plant/maint-wo/filter',
				method: 'POST',
				data: {
					type: 'equipment',
					equipment_id: equipmentId,
					maintenance_type_id: maintenanceTypeId,
					bom_id: bomId,
					_token: $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					console.log("Filter response:", response);
					
					// Response is now direct array data (like populateModal)
					if (response && response.length > 0) {
						// Use the first equipment result
						const equipmentData = response[0];
						
						// Show equipment modal with filtered results
						populateEquipmentModal(response);
						$('#equipment-modal').modal('show');

						Swal.fire({
							title: 'Success!',
							text: `Found ${response.length} equipment configuration(s).`,
							icon: 'success',
							timer: 2000,
							showConfirmButton: false
						});

					} else {
						// No data found - show empty modal
						$('#equipment-modal-table tbody').html('<tr><td colspan="5" class="text-center">No equipment found for the selected criteria.</td></tr>');
						$('#equipment-modal').modal('show');
						
						Swal.fire({
							title: 'No Results',
							text: 'No equipment found matching the selected criteria.',
							icon: 'info'
						});
					}
				},
				error: function(xhr, status, error) {
					console.error('Equipment search error:', error);
					Swal.fire({
						title: 'Error!',
						text: 'An error occurred while searching for equipment data.',
						icon: 'error'
					});
				},
				complete: function() {
					// Reset button state
					$('#equipmentSearchBtn').prop('disabled', false).html('<i data-feather="search"></i> Search');
					feather.replace(); // Re-initialize feather icons
				}
			});
		});

		// Function to update attribute badges display (same as BOM)
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
				let displayedCount = 0;

				if (attr && attr.length > 0) {
					// First, count total selected attributes
					attr.forEach(function(attribute) {
						let selectedAttr = existingAttributes.find(selected =>
							selected.item_attribute_id === attribute.id
						);
						if (selectedAttr) {
							selectedCount++;
						}
					});

					// Then display badges (max 2)
					attr.forEach(function(attribute) {
						// Check if this attribute has been selected
						let selectedAttr = existingAttributes.find(selected =>
							selected.item_attribute_id === attribute.id
						);

						// Only show selected attributes
						if (selectedAttr && displayedCount < 2) {
							displayedCount++;
							// Find the selected value from the attribute's values
							let valuesData = attribute.values_data || attribute.values || [];

							let selectedValue = valuesData.find(val => val.id === selectedAttr.value_id);

							if (selectedValue) {
								badgesHtml +=
									`<span class="badge rounded-pill badge-light-primary" style="font-size:10px; margin-right:5px;cursor:pointer">
						<strong>${attribute.group_name}</strong>: ${selectedValue.value}
					</span>`;
							} 
						}
					});

					// Only show "..." if there are more than 2 SELECTED attributes
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

		// Add click event for the entire attribute cell (4th column)
		$('.mrntableselectexcel').on('click', 'td:nth-child(4)', function() {
			var $this = $(this);
			var $tr = $this.closest('tr');
			var $selectElement = $tr.find('.item_code');
			var $attributesTable = $('#attributes_table_modal'); // correct modal table ID
			$attributesTable.data('currentRow', $tr);

			if ($selectElement.val() !== "") {
				let attributesJSON = JSON.parse($selectElement.attr('data-attr') || '[]');
				let $hiddenInput = $tr.find('.attribute');
				let existingAttributes = $hiddenInput.length && $hiddenInput.val()
					? JSON.parse($hiddenInput.val())
					: [];

				if (attributesJSON.length > 0) {
					// Open attribute modal using correct modal ID
					$('#attribute').modal('show');
					
					// Populate attribute modal with data
					populateAttributeModal(attributesJSON, existingAttributes, $tr);
				} else {
					showToast('info', 'No attributes available for this item.');
				}
			} else {
				showToast('warning', 'Please select an item first.');
			}
		});

		// Function to populate attribute modal with actual implementation
		function populateAttributeModal(attributesJSON, existingAttributes, $row) {
			let $modalTable = $('#attributes_table_modal tbody');
			$modalTable.empty();

			let innerHtml = '';
			$.each(attributesJSON, function (index, element) {
				let optionsHtml = '';
				$.each(element.values_data, function (i, value) {
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

			$modalTable.html(innerHtml);
			
			// Initialize select2 for the modal dropdowns
			$modalTable.find('.select2').select2({
				dropdownParent: $('#attribute')
			});
		}

		// Add click handler for the Submit Attribute button to save selections
		$(document).on('click', '.submitAttributeBtn', function() {
			let $currentRow = $('#attributes_table_modal').data('currentRow');
			if (!$currentRow) return;

			let selectedAttributes = [];
			
			// Collect selected attribute values from modal
			$('#attributes_table_modal tbody tr').each(function() {
				let $tr = $(this);
				let attributeId = $tr.find('input[name="id"]').val();
				let selectedValueId = $tr.find('select').val();
				
				if (selectedValueId && selectedValueId !== '') {
					selectedAttributes.push({
						item_attribute_id: parseInt(attributeId),
						value_id: parseInt(selectedValueId)
					});
				}
			});

			// Save selected attributes to hidden input
			$currentRow.find('.attribute').val(JSON.stringify(selectedAttributes));
			
			// Update attribute badges display
			updateAttributeBadges($currentRow);
			
			// Close modal
			$('#attribute').modal('hide');
		});

		});
	</script>
@endsection