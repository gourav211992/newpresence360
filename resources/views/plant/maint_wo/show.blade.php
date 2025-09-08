@extends('layouts.app')
@section('content')
<style>
		.poitemtxt {
			white-space: normal;
		}
	</style>
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
							<a href="{{ route('maint-wo.index') }}">
								<button class="btn btn-secondary btn-sm mb-50 mb-sm-0">
									<i data-feather="arrow-left-circle"></i> Back
								</button>
							</a>
             
						@if($buttons['approve'])
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" 
                        data-bs-target="#approveModal" onclick="setApproval()">
                    <i data-feather="check-circle"></i> Approve
                </button>
                <button type="button" id="reject-button" class="btn btn-danger btn-sm mb-50 mb-sm-0" 
                        data-bs-toggle="modal" data-bs-target="#approveModal" onclick="setRejection()">
                    <i data-feather="x-circle"></i> Reject
                </button>
             
              
                    <button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm"
                        class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                @endif

                <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light" data-bs-toggle="modal" data-bs-target="#closeModal">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Close
                </button>
            
                            
						</div>
					</div>
      </div>
    </div>

    {{-- Body --}}
    <div class="content-body">
      <form id="maint-wo-form" method="POST" action="#" enctype="multipart/form-data">
        @csrf

        @php
          // Extract data from the work order for show view
          $workOrder = $data ?? null;
          $equipmentDetailsArr = $workOrder && $workOrder->equipment_details ? json_decode($workOrder->equipment_details) : (object)[];
          $refType = $equipmentDetailsArr->reference_type ?? '';
          $sparePartsData = $workOrder && $workOrder->spare_parts ? json_decode($workOrder->spare_parts, true) : [];
          $checklistData = $workOrder && $workOrder->checklist_data ? json_decode($workOrder->checklist_data, true) : [];
          
          // Extract defect notification details if reference type is defect_notification
          $selectedDefectName = $equipmentDetailsArr->equipment_defect_type ?? '';
          $selectedPriority = $equipmentDetailsArr->equipment_priority ?? '';
          $reportedById = $equipmentDetailsArr->equipment_reported_by ?? null;
          $reportDateRaw = $equipmentDetailsArr->equipment_report_date ?? null;
          $reportDate = $reportDateRaw ;
        @endphp


		

        {{-- Hidden fields for show view (populated with actual data) --}}
        <input type="hidden" name="book_code" id="book_code_input" value="{{ $workOrder->book_code ?? '' }}">
        <input type="hidden" name="doc_number_type" id="doc_number_type" value="{{ $workOrder->doc_number_type ?? '' }}">
        <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern" value="{{ $workOrder->doc_reset_pattern ?? '' }}">
        <input type="hidden" name="doc_prefix" id="doc_prefix" value="{{ $workOrder->doc_prefix ?? '' }}">
        <input type="hidden" name="doc_suffix" id="doc_suffix" value="{{ $workOrder->doc_suffix ?? '' }}">
        <input type="hidden" name="doc_no" id="doc_no" value="{{ $workOrder->doc_no ?? '' }}">
        <input type="hidden" name="document_status" id="document_status" value="{{ $workOrder->document_status ?? '' }}">

        <input type="hidden" name="spare_parts" id="spare_parts" value="{{ $workOrder->spare_parts ?? '' }}">
        <input type="hidden" name="checklist_data" id="checklist_data" value="{{ $workOrder->checklist_data ?? '' }}">
        <input type="hidden" name="equipment_details" id="equipment_details" value="{{ $workOrder->equipment_details ?? '' }}">

        {{-- readonly/selection data populated from work order --}}
        <input type="hidden" name="defect_notification_id" id="defect_notification_id_hidden" value="{{ $workOrder->defect_notification_id ?? '' }}">
        <input type="hidden" name="equipment_category" id="equipment_category_hidden" value="{{ $equipmentDetailsArr->equipment_category ?? '' }}">
        <input type="hidden" name="equipment_name" id="equipment_name_hidden" value="{{ $equipmentDetailsArr->equipment_name ?? '' }}">
        <input type="hidden" name="defect_type" id="defect_type_hidden" value="{{ $selectedDefectName }}">
        <input type="hidden" name="problem" id="problem_hidden" value="{{ $equipmentDetailsArr->equipment_problem ?? '' }}">
        <input type="hidden" name="report_date_time" id="report_date_time_hidden" value="{{ $reportDateRaw ?? '' }}">
        <input type="hidden" name="reported_by" id="reported_by_hidden" value="{{ $reportedById ?? '' }}">

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
                        <div class="header-right">
                          @php
                              use App\Helpers\Helper;
                          @endphp
                          <div class="col-md-6 text-sm-end">
                              <span
                                  class="badge rounded-pill {{App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$data->document_status] ?? ''}} forminnerstatus">
                                  <span class="text-dark">Status</span>
                                  : <span
                                      class="{{App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS['CLOSED'] ?? ''}}">
                                      @if ($data->document_status == App\Helpers\ConstantHelper::APPROVAL_NOT_REQUIRED)
                                          Approved
                                      @else
                                          {{ ucfirst($data->document_status) }}
                                      @endif
                                  </span>
                              </span>
                          </div>
                      </div>
                      </div>
                    </div>

                    <div class="col-md-8">
                      <div class="row align-items-center mb-1">
                        <div class="col-md-3">
                          <label class="form-label">Series <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-5">
                          <select class="form-select" id="book_id" name="book_id" disabled required>
                            @if(isset($series) && count($series) > 0)
                              @foreach($series as $index => $book)
                                <option value="{{ $book->id }}" @if($workOrder->book_id == $book->id) selected @endif>
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
                          <input type="text" class="form-control" id="document_number" name="document_number" value="{{ $workOrder->document_number ?? '' }}" disabled required>
                        </div>
                      </div>

                      <div class="row align-items-center mb-1">
                        <div class="col-md-3">
                          <label class="form-label">Doc Date <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-5">
                          <input type="date" value="{{ $workOrder->document_date }}" class="form-control" id="document_date" name="document_date" disabled required>
                        </div>
                      </div>

                      <div class="row align-items-center mb-1">
                        <div class="col-md-3">
                          <label class="form-label">Location <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-5">
                          <select class="form-select" name="location_id" id="location_id" disabled required>
                            <option value="">Select Location</option>
                            @foreach($locations ?? [] as $location)
                              <option value="{{ $location->id }}" @if($workOrder->location_id == $location->id) selected @endif>{{ $location->store_name }}</option>
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
                          <input type="hidden" name="reference_type" id="reference_type" value="{{ $refType }}">
                          <button type="button" class="btn btn-sm mb-0 reference-btn {{ $refType === 'equipment' ? 'btn-primary' : 'btn-outline-primary' }}" disabled>
                            <i data-feather="settings"></i> Equipment
                          </button>
                          <button type="button" class="btn btn-sm mb-0 reference-btn {{ $refType === 'defect_notification' ? 'btn-primary' : 'btn-outline-primary' }}" disabled>
						  <i data-feather="plus-square"></i> Defect Notification
                          </button>
                        </div>
                      </div>
                      
                    </div> {{-- /col-md-8 --}}
                    @include('partials.approval-history', [
                        'document_status' => $workOrder->document_status,
                        'revision_number' => $workOrder->revision_number,
                        'approvalHistory' => $approvalHistory ?? []
                    ])
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
                        <input type="text" placeholder="Select" value="{{ $equipmentDetailsArr->equipment_category ?? '' }}" class="form-control ledgerselecct" id="equipment_category" disabled />
                      </div>
                    </div>

                    <div class="col-md-3 basic-equipment-field">
                      <div class="mb-1">
                        <label class="form-label">Equipment <span class="text-danger">*</span></label>
                        <input type="hidden" name="equipment_id" id="equipment_id" value="{{ $equipmentDetailsArr->equipment_id ?? '' }}">
                        <input type="text" placeholder="Select Equipment" value="{{ $equipmentDetailsArr->equipment_name ?? '' }}" class="form-control ledgerselecct" id="equipment_name" disabled required>
                      </div>
                    </div>

                     <div class="col-md-3 basic-equipment-field">
                      <div class="mb-1">
                        <label class="form-label">Maintenance Type <span class="text-danger">*</span></label>
                        @php
                          // Get maintenance type from stored data
                          $maintenanceTypeId = $equipmentDetailsArr->equipment_maintenance_type_id ?? $equipmentDetailsArr->maintenance_type_id ?? '';
                          $maintenanceTypeName = $equipmentDetailsArr->equipment_maintenance_type_name ?? $equipmentDetailsArr->maintenance_type_name ?? '';
                        @endphp
                        <input type="text" class="form-control" value="{{ $maintenanceTypeName }}" disabled readonly>
                        <input type="hidden" name="equipment_maintenance_type_id" value="{{ $maintenanceTypeId }}">
                        <input type="hidden" name="equipment_maintenance_type_name" value="{{ $maintenanceTypeName }}">
                      </div>
                    </div>

                    @if($refType === 'defect_notification')
                      {{-- Defect Type --}}
                      <div class="col-md-3 equipment-detail-field">
                        <div class="mb-1" id="defect_type_field">
                          <label class="form-label">Defect Type</label>
                          <select class="form-select" name="defect_type" id="defect_type_select" disabled>
                            @foreach($defectTypes as $defect)
								<option value="{{$defect->name}}" @if($defect->name == $selectedDefectName) selected @endif>{{$defect->name}}</option>
							@endforeach
                          </select>
                        </div>
                      </div>

                      {{-- Problem --}}
                      <div class="col-md-3 equipment-detail-field">
                        <div class="mb-1" id="problem_field">
                          <label class="form-label">Problem <span class="text-danger">*</span></label>
                          <input type="text" value="{{ $equipmentDetailsArr->equipment_problem ?? '' }}" class="form-control" disabled />
                        </div>
                      </div>

                      {{-- Priority --}}
                      <div class="col-md-3 equipment-detail-field" id="priority_field">
                        <div class="mb-1">
                          <label class="form-label">Priority</label>
                          <select class="form-select" name="priority" disabled required>
                            <option value="">Select Priority</option>
                            <option value="Low" @if($selectedPriority == 'Low') selected @endif>Low</option>
                            <option value="Medium" @if($selectedPriority == 'Medium') selected @endif>Medium</option>
                            <option value="High" @if($selectedPriority == 'High') selected @endif>High</option>
                            <option value="Critical" @if($selectedPriority == 'Critical') selected @endif>Critical</option>
                          </select>
                        </div>
                      </div>

                      {{-- Report Date --}}
                      <div class="col-md-3 equipment-detail-field">
                        <div class="mb-1" id="report_date_field">
                          <label class="form-label">Report Date & Time</label>
                          <input type="text" value="{{ $reportDate }}" class="form-control" disabled />
                        </div>
                      </div>

                      {{-- Reported By --}}
                      <div class="col-md-3 equipment-detail-field">
                        <div class="mb-1" id="report_by_field">
                          <label class="form-label">Reported by</label>
                          <input type="text" value="{{ $equipmentDetailsArr->equipment_reported_by_name ?? '' }}" class="form-control" disabled />
                        </div>
                      </div>
                    

                  
                      {{-- Detailed Observations --}}
                      <div class="col-md-9 equipment-detail-field">
                        <div class="mb-1" id="detailed_observations_field">
                          <label class="form-label">Detailed observations</label>
                          <textarea name="detailed_observations" class="form-control" id="detailed_observations" rows="3" disabled>{{ $equipmentDetailsArr->equipment_detailed_observations ?? $workOrder->detailed_observations ?? '' }}</textarea>
                        </div>
                      </div>

                      {{-- Supporting Documents --}}
                      <div class="col-md-3 equipment-detail-field" id="supporting_documents_field">
                        <div class="mb-1">
                          <label class="form-label">Supporting Documents <span class="text-danger">*</span></label><br/>
                          <div class="mt-50">
                            @if(isset($equipmentDetailsArr->equipment_document) && $equipmentDetailsArr->equipment_document)
                              <a href="{{ asset('storage/' . $equipmentDetailsArr->equipment_document) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i data-feather="file"></i> View Document
                              </a>
                            @elseif($workOrder && $workOrder->document)
                              <a href="{{ asset('storage/' . $workOrder->document) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i data-feather="file"></i> View Document
                              </a>
                            @else
                              <input type="file" name="supporting_documents[]" class="form-control" disabled multiple>
                            @endif
                          </div>
                        </div>
                      </div>
                    @endif

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
                          @if($refType === 'equipment')
                            <h4 class="card-title text-theme">Checklist and Spare Parts Detail</h4>
                          @else
                            <h4 class="card-title text-theme">Spare Parts Detail</h4>
                          @endif
                          <p class="card-text">View the details</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="step-custhomapp bg-light">
                    <ul class="nav nav-tabs my-25 custapploannav" role="tablist" id="main-tabs">
                      @if($refType === 'equipment')
                        <li class="nav-item" id="checklist-tab">
                          <a class="nav-link active" data-bs-toggle="tab" href="#tab-checklist">Checklist</a>
                        </li>
                        <li class="nav-item" id="spare-parts-tab">
                          <a class="nav-link" data-bs-toggle="tab" href="#tab-spares">Spare Parts</a>
                        </li>
                      @else
                        <li class="nav-item" id="spare-parts-tab">
                          <a class="nav-link active" data-bs-toggle="tab" href="#tab-spares">Spare Parts</a>
                        </li>
                      @endif
                    </ul>
                  </div>

                  <div class="tab-content pb-1">
                    {{-- Checklist Tab - only show for equipment reference type --}}
                    @if($refType === 'equipment')
                      <div class="tab-pane active" id="tab-checklist">
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
                                  @if(!empty($checklistData))
                                    @php $counter = 1; @endphp
                                    @foreach($checklistData as $mainCategory)
                                      {{-- Main category header --}}
                                      <tr>
                                        <td>{{ $counter++ }}</td>
                                        <td colspan="2" class="poprod-decpt p-50">
                                          <strong class="font-small-4">{{ $mainCategory['main_name'] ?? 'Category' }}</strong>
                                        </td>
                                      </tr>
                                      
                                      {{-- Individual checklist items --}}
                                      @if(!empty($mainCategory['checklist']))
                                        @foreach($mainCategory['checklist'] as $item)
                                          <tr>
                                            <td></td>
                                            <td class="ps-1">
                                              {{ $item['name'] ?? 'N/A' }}
                                              @if($item['mandatory'] ?? false)
                                                <span class="text-danger">*</span>
                                              @endif
                                            </td>
                                            <td class="poprod-decpt">
                                              @if(($item['data_type'] ?? 'text') === 'boolean')
                                                <select class="form-select mw-100" disabled>
                                                  <option selected>{{ ($item['value'] ?? false) ? 'Yes' : 'No' }}</option>
                                                </select>
                                              @elseif(($item['data_type'] ?? 'text') === 'number')
                                                <input type="number" class="form-control mw-100" 
                                                       value="{{ $item['value'] ?? '' }}" disabled readonly>
                                              @elseif(($item['data_type'] ?? 'text') === 'list')
                                                <select class="form-select mw-100" disabled>
                                                  <option selected>{{ $item['value'] ?? 'N/A' }}</option>
                                                </select>
                                              @else
                                                <input type="text" class="form-control mw-100" 
                                                       value="{{ $item['value'] ?? '' }}" disabled readonly>
                                              @endif
                                            </td>
                                          </tr>
                                        @endforeach
                                      @endif
                                    @endforeach
                                  @else
                                    <tr>
                                      <td>1</td>
                                      <td colspan="2" class="poprod-decpt p-50 text-center text-muted">
                                        <strong class="font-small-4">No checklist data available</strong>
                                      </td>
                                    </tr>
                                  @endif
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>
                    @endif

                    {{-- Spare Parts Tab --}}
                    <div class="tab-pane {{ $refType === 'equipment' ? '' : 'active' }}" id="tab-spares">
                      <div class="border-bottom mb-2 pb-25">
                        <div class="row">
                          <div class="col-md-6">
                            <div class="newheader">
                              <h4 class="card-title text-theme">Spare Parts Detail</h4>
                              <p class="card-text">View the details</p>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-12">
                          <div class="table-responsive pomrnheadtffotsticky">
                            <table id="itemTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                              <thead>
                                <tr>
                                  <th width="62" class="customernewsection-form">
                                    <div class="form-check form-check-primary custom-checkbox">
                                      <input type="checkbox" class="form-check-input" id="checkAll" disabled>
                                      <label class="form-check-label" for="checkAll"></label>
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
                                @if(!empty($sparePartsData))
                                  @foreach($sparePartsData as $index => $part)
                                    <tr @if($index === 0) class="trselected" @endif>
                                      <td>
                                        <div class="form-check form-check-primary custom-checkbox">
                                          <input type="checkbox" class="form-check-input row-check" disabled>
                                        </div>
                                      </td>
                                      <td>
                                        <input type="hidden" name="item_id[]" class="item_id" value="{{ $part['item_id'] ?? ($part->item_id ?? '') }}">
                                        <input type="text" name="item[]" 
                                               value="{{ $part['item_code'] ?? ($part->item_code ?? '') }}"
                                               data-id="{{ $part['item_id'] ?? ($part->item_id ?? '') }}"
                                               data-code="{{ $part['item_code'] ?? ($part->item_code ?? '') }}"
                                               data-name="{{ $part['item_name'] ?? ($part->item_name ?? '') }}"
                                               data-attr='{{ json_encode($part['item_attributes'] ?? ($part->item_attributes ?? [])) }}'
                                               class="item_code form-control mw-100 ledgerselecct mb-25" 
                                               disabled readonly required />
                                      </td>
                                      <td>
                                        <input type="text" 
                                               value="{{ $part['item_name'] ?? ($part->item_name ?? '') }}"
                                               class="item_name form-control mw-100 ledgerselecct mb-25" 
                                               disabled readonly required />
                                      </td>
                                      <td>
                                        <input type="hidden" class="attribute" value='{{ $part['attribute'] ?? ($part->attribute ?? "[]") }}'>
                                        <div class="d-flex flex-wrap gap-1" id="attribute-badges-{{ $index }}">
                                           <!-- Attribute badges will be displayed here via JavaScript -->
                                        </div>
                                      </td>
                                      <td>
                                        <select class="uom form-select mw-100" name="uom[]" disabled required>
                                          <option value="{{ $part['uom_id'] ?? ($part->uom_id ?? '') }}">
                                            {{ $part['uom_name'] ?? ($part->uom ?? 'Select UOM') }}
                                          </option>
                                        </select>
                                      </td>
                                      <td>
                                        <input type="number" class="qty form-control mw-100" name="qty[]" 
                                               value="{{ $part['qty'] ?? ($part->qty ?? '') }}" 
                                               disabled readonly required />
                                      </td>
                                      <td>
                                        <input type="number" class="available_stock form-control mw-100" name="available_stock[]" 
                                               value="{{ $part['available_stock'] ?? 100 }}" 
                                               disabled readonly />
                                      </td>
                                    </tr>
                                  @endforeach
                                @else
                                  <tr>
                                    <td colspan="7" class="text-center text-muted">No spare parts data available</td>
                                  </tr>
                                @endif
                              </tbody>

                              <tfoot>
                                <tr valign="top">
                                  <td colspan="7" rowspan="10">
                                    <table class="table border">
                                      <tr>
                                        <td class="p-0">
                                          <h6 class="text-dark mb-0 bg-light-primary py-1 px-50">
                                            <strong>Part Details</strong>
                                          </h6>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td class="poprod-decpt">
                                          <span class="poitemtxt mw-100">
                                            <strong>Name</strong>: <span id="part_name">@if(!empty($sparePartsData) && count($sparePartsData) > 0){{ $sparePartsData[0]['item_name'] ?? ($sparePartsData[0]->item_name ?? 'N/A') }}@endif</span>
                                          </span>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td class="poprod-decpt" id="attributes_badges">
                                          @if(!empty($sparePartsData) && count($sparePartsData) > 0)
                                            @php
                                              $attributes = $sparePartsData[0]['attribute'] ?? ($sparePartsData[0]->attribute ?? '[]');
                                              $attributesArray = is_string($attributes) ? json_decode($attributes, true) : $attributes;
                                            @endphp
                                            @if(!empty($attributesArray) && is_array($attributesArray))
                                              @foreach($attributesArray as $attribute)
                                                @if(isset($attribute['name']) && isset($attribute['value']))
                                                  <span class="badge rounded-pill badge-light-secondary me-1 mb-1">
                                                    <strong>{{ $attribute['name'] }}</strong>: {{ $attribute['value'] }}
                                                  </span>
                                                @endif
                                              @endforeach
                                            @endif
                                          @endif
                                        </td>
                                      </tr>
                                      <tr>
                                        <td class="poprod-decpt">
                                          <span class="badge rounded-pill badge-light-primary">
                                            <strong>Inv. UOM</strong>: <span id="uom">@if(!empty($sparePartsData) && count($sparePartsData) > 0){{ $sparePartsData[0]['uom_name'] ?? ($sparePartsData[0]->uom ?? 'N/A') }}@endif</span>
                                          </span> 
                                          <span class="badge rounded-pill badge-light-primary">
                                            <strong>Qty.</strong>: <span id="qty">@if(!empty($sparePartsData) && count($sparePartsData) > 0){{ $sparePartsData[0]['qty'] ?? ($sparePartsData[0]->qty ?? 'N/A') }}@endif</span>
                                          </span>
                                          <span class="badge rounded-pill badge-light-success">
                                            <strong>Available Stock</strong>: <span id="available_stock">@if(!empty($sparePartsData) && count($sparePartsData) > 0){{ $sparePartsData[0]['available_stock'] ?? 100 }}@else 100 @endif</span>
                                          </span>
                                        </td>
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
              <textarea rows="4" class="form-control" name="final_remark" placeholder="Enter Remarks here..." disabled readonly>{{ $workOrder->final_remark ?? '' }}</textarea>
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
                  <div class="col"><div class="mb-1"><label class="form-label">Equipment</label><input type="text" placeholder="Select" class="form-control ledgerselecct" /></div></div>
                  <div class="col"><div class="mb-1"><label class="form-label">Maintenance Type</label><input type="text" placeholder="Select" class="form-control ledgerselecct" /></div></div>
                  <div class="col"><div class="mb-1"><label class="form-label">Maint. BOM</label><input type="text" placeholder="Select" class="form-control ledgerselecct" /></div></div>
                  <div class="col mb-1"><label class="form-label">&nbsp;</label><br/><button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button></div>

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
                      </select>
                    </div>
                  </div>
                  <div class="col">
                    <div class="mb-1">
                      <label class="form-label">Defect Type</label>
                      <select class="form-control ledgerselecct" name="defect_type_id">
                        <option value="">Select Defect Type</option>
                      </select>
                    </div>
                  </div>
                  <div class="col">
                    <div class="mb-1">
                      <label class="form-label">Priority</label>
                      <select class="form-select" name="priority">
                        <option value="">Select</option>
                      </select>
                    </div>
                  </div>
                  <div class="col">
                    <div class="mb-1">
                      <label class="form-label">Series</label>
                      <select class="form-select" id="series_filter" name="series">
                        <option value="">Select Series</option>
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
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Maint. Wo</strong></p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

	<!-- Amendment Submit Modal -->
	<div class="modal fade" id="amendmentSubmitModal" tabindex="-1" aria-labelledby="amendmentSubmitModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="amendmentSubmitModalLabel">Submit Amendment</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label for="amendment_remarks" class="form-label">Amendment Remarks <span class="text-danger">*</span></label>
						<textarea class="form-control" id="amendment_remarks" name="amendment_remarks" rows="4" placeholder="Please provide detailed remarks for this amendment..." required></textarea>
					</div>
					<div class="mb-3">
						<label for="amendment_attachment" class="form-label">Supporting Document (Optional)</label>
						<input type="file" class="form-control" id="amendment_attachment" name="amendment_attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
						<small class="text-muted">Accepted formats: PDF, DOC, DOCX, JPG, PNG (Max: 10MB)</small>
					</div>
				</div>
				
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="confirmAmendmentSubmit">
						<i data-feather="check-circle"></i> Submit Amendment
					</button>
				</div>
			</div>
		</div>
	</div>

  <!-- Approve/Reject Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="ajax-submit-2" method="POST" action="{{ route('maint-wo.approval') }}" 
                  data-redirect="{{ route('maint-wo.index') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="action_type" id="action_type">
                <input type="hidden" name="id" value="{{ $workOrder->id ?? '' }}">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="approve_reject_heading_label">Approve/Reject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-1">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control cannot_disable"></textarea>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Upload Document</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>
                    <span class="text-primary small">{{ __("message.attachment_caption") }}</span>
                </div>
                
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close Maintenance Modal -->
<div class="modal fade" id="closeModal" tabindex="-1" aria-labelledby="closeModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
          <input type="hidden" value="{{ $workOrder->id }}" name="workorder_id" id="workorder_id">
					<h5 class="modal-title" id="closeModalLabel">Close Maintenance</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label for="close_remarks" class="form-label">Close Remarks <span class="text-danger">*</span></label>
						<textarea class="form-control" id="close_remarks" name="close_remarks" rows="4" placeholder="Please provide detailed remarks for this close..." required></textarea>
					</div>
					<div class="mb-3">
						<label for="close_attachment" class="form-label">Supporting Document (Optional)</label>
						<input type="file" class="form-control" id="close_attachment" name="close_attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
						<small class="text-muted">Accepted formats: PDF, DOC, DOCX, JPG, PNG (Max: 10MB)</small>
					</div>
				</div>
				
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="confirmCloseSubmit">
						<i data-feather="check-circle"></i> Submit Close
					</button>
				</div>
			</div>
		</div>
	</div>
@endsection



@section('scripts')
	<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
    @include('plant.maint_wo.common-js-route',["wo" => isset($wo) ? $wo : null, "route_prefix" => "maint-wo"])
    <script src="{{ asset('assets/js/modules/maint-wo/common-script.js') }}"></script>
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
			$('html, body').scrollTop($('.trselected').offset().top - 200);
			updateFooterFromSelected();
		});
		$(document).on('click', 'tbody tr', function () {
			$(this).addClass('trselected').siblings().removeClass('trselected');
			$('html, body').scrollTop($(this).offset().top - 200);
			updateFooterFromSelected();
		});
		function updateFooterFromSelected() {
			let $selected = $('.trselected');
			if ($selected.length) {
				console.log("qty " + $selected.find('.qty').val());
				$('#part_name').text($selected.find('.item_name').val());
				$('#uom').text($selected.find('.uom option:selected').text());
				$('#qty').text($selected.find('.qty').val());
				let $selectElement = $selected.find('.item_code');
				let $badgesContainer = $('#attributes_badges'); // container for badges

				if ($selectElement.val() !== "") {
					let attributesJSON = JSON.parse($selectElement.attr('data-attr') || '[]');
					let $hiddenInput = $selected.find('.attribute');
					let existingAttributes = $hiddenInput.length && $hiddenInput.val()
						? JSON.parse($hiddenInput.val())
						: [];

					if (!attributesJSON.length) {
						$badgesContainer.html('<span>No attributes available</span>');
						return;
					}

					let badgesHtml = '';

					$.each(attributesJSON, function (index, element) {
						// Find selected value from existingAttributes
						let selectedValObj = existingAttributes.find(attr => attr.item_attribute_id === element.id);
						let selectedVal = selectedValObj ? selectedValObj.value_id : '';

						// Find text for selected value
						let selectedText = '';
						if (selectedVal) {
							let valObj = element.values_data.find(v => v.id === selectedVal);
							selectedText = valObj ? valObj.value : '';
						}

						badgesHtml += `
					<span class="badge rounded-pill badge-light-primary" style="margin-right:5px;">
						<strong>${element.group_name}</strong>: <span>${selectedText}</span>
					</span>
				`;
					});

					$badgesContainer.html(badgesHtml);

				} else {
					$badgesContainer.html('');
				}

			}
		}
		
		// Initialize autocomplete for existing spare parts row when document is ready
		$(document).ready(function() {
			console.log('Document ready - initializing autocomplete for existing rows');
			console.log('Found .item_code elements:', $('.item_code').length);
			initAutoForItem('.item_code');
		});

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
																<button data-bs-toggle="modal" data-bs-target="#attribute"
																	class="btn p-25 btn-sm btn-outline-secondary attributeBtn"
																	style="font-size: 10px">Attributes</button>
															</td>
															<td>
																<select class="uom form-select mw-100" name="uom[]" required>

																</select>
															</td>
															<td><input type="number" class="qty form-control mw-100"  name="qty[]"
																	required /></td>
														</tr>																  `;
			$('.mrntableselectexcel').append(newRow);
			// Initialize autocomplete for all item_code elements (including new row)
			initAutoForItem('.item_code');

		});
		$('#delete').on('click', function () {
			let $rows = $('.mrntableselectexcel tr');
			let $checked = $rows.find('.row-check:checked');

			// Prevent deletion if only one row exists
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
		initAutoForItem('.item_code');
		function updateJsonData() {
			// Collect Spare Parts Data
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

			// Collect Checklist Data
			const checklistData = [];
			let checklistIndex = 0;

			$('.mrntableselectexcel1 tr').each(function () {
				const row = $(this);
				const checklistName = row.find('td:nth-child(2)').text().trim();
				
				// Skip header rows and empty rows
				if (checklistName && !checklistName.includes('Checklist') && checklistName !== '#' && checklistName !== 'Checklist') {
					return;
				}
				
				if (checklistName && checklistName.includes('Checklist')) {
					const textInput = row.find('input[type="text"]');
					const checkboxInput = row.find('input[type="checkbox"]');
					
					let value = '';
					let type = '';
					
					if (textInput.length > 0) {
						value = textInput.val() || '';
						type = 'text';
					} else if (checkboxInput.length > 0) {
						value = checkboxInput.is(':checked');
						type = 'checkbox';
					}
					
					if (type) {
						checklistData.push({
							index: ++checklistIndex,
							name: checklistName,
							type: type,
							value: value
						});
					}
				}
			});

			$('#checklist_data').val(JSON.stringify(checklistData));

			// Collect Equipment Details Data
			const equipmentDetails = {
				reference_type: $('#reference_type').val() || '',
				equipment_category: $('#equipment_category_hidden').val() || $('#equipment_category').val() || '',
				equipment_name: $('#equipment_name_hidden').val() || $('#equipment_name').val() || '',
				equipment_id: $('#equipment_id').val() || '',
				equipment_maintenance_type_id: $('#maintenance_type').val() || '',
				equipment_maintenance_type_name: $('#maintenance_type option:selected').text() || '',
				equipment_defect_type: $('#defect_type_hidden').val() || $('#defect_type_select').val() || '',
				equipment_problem: $('#problem_hidden').val() || $('#problem_field input').val() || '',
				equipment_priority: $('#priority_field select').val() || '',
				equipment_report_date: $('#report_date_time_hidden').val() || $('#report_date_field input').val() || '',
				equipment_reported_by: $('#reported_by_hidden').val() || $('#report_by_field input').val() || '',
				equipment_detailed_observations: $('#detailed_observations_field textarea').val() || '',
				equipment_supporting_documents: $('#supporting_documents_field input')[0]?.files[0]?.name || ''
			};

			$('#equipment_details').val(JSON.stringify(equipmentDetails));
			
			console.log('Form data collected:', {
				spare_parts: allRows.length + ' items',
				checklist: checklistData.length + ' items', 
				equipment_details: equipmentDetails
			});
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
				Swal.fire({
					icon: 'error',
					title: 'Validation Error',
					text: 'Please select a reference type (Equipment or Defect Notification)',
					confirmButtonText: 'OK'
				});
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

		$(document).on('click', '.submitAttributeBtn', (e) => {
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

					setTimeout(() => {
						if (ui.item.is_attr) {
							$input.closest('tr').find('.attributeBtn').trigger('click');
						} else {
							$input.closest('tr').find('.attributeBtn').trigger('click');
							$input.closest('tr').find('.qty').val('').focus();
						}
					}, 100);

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

			// Find the attributes table and tbody
			const attributesTable = document.getElementById("attributes_table_modal");
			const tbody = attributesTable.querySelector("tbody");

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
			console.log(selectedAttributes);
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


			} else {
				$attributesTable.html(`
						<tr>
							<td colspan="2" class="text-center">No attributes available</td>
						</tr>
					`);
			}
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
			$('#maintenance_type').prop('disabled', true);
			
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
			$('#maintenance_type').prop('disabled', true); // Keep maintenance type enabled for user selection
			
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
			var selectedEquipment = $('input[name="equipmentRadio"]:checked');
			
			if (selectedEquipment.length === 0) {
				// Show toaster notification
				Swal.fire({
					icon: 'error',
					title: 'Validation Error',
					text: 'Please select at least one equipment',
					confirmButtonText: 'OK'
				});
				return false; // Don't close modal
			}
			
			// Get selected equipment data
			var equipmentRow = selectedEquipment.closest('tr');
			var equipmentName = equipmentRow.find('td').eq(0).find('strong').text().trim();
			if (!equipmentName) {
				equipmentName = equipmentRow.find('td').eq(0).text().trim();
			}
			var eqpt = selectedEquipment.data('eqpt');
			
			// Populate equipment fields
			$('#equipment_name').val(selectedEquipment.data('equipment-name'));
			$('#equipment_id').val(selectedEquipment.data('equipment-id'));
			$('#maintenance_type').val(selectedEquipment.data('maintenance-type'));
			
			// Keep only basic equipment fields visible and read-only for equipment selection
			$('.equipment-detail-field').hide();
			$('.basic-equipment-field').show();
			$('#equipment_category').prop('readonly', true);
			$('#equipment_name').prop('readonly', true);
			$('#maintenance_type').prop('disabled', true);
			
			console.log('Equipment selected:', equipmentName);
			console.log('Equipment detail fields shown');
			
			// Close modal manually
			$('#reference').modal('hide');
			
			return true;
		}

		function processDefectSelection() {
			let selectedDefect = $('input.defect-radio:checked').attr('id');
      let onlyNumber = selectedDefect.replace("defect_row_", "");
  
			if (onlyNumber === "") {
				Swal.fire({
					icon: 'error',
					title: 'Validation Error',
					text: 'Please select a defect notification',
					confirmButtonText: 'OK'
				});
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

						console.log("Fetched defect:", defect);

						// Equipment
						if (defect.equipment) {
							$('#equipment_id').val(defect.equipment.id);
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


		//Search function for the defect modal 

		$(document).ready(function() {
			$('#defect_search_btn').on('click', function(e) {
				e.preventDefault();

				var equipmentId = $('select[name="equipment_id"]').val();
				var defectTypeId = $('select[name="defect_type_id"]').val();
				var priority = $('select[name="priority"]').val();
				var series = $('select[name="series"]').val();

				$.ajax({
					url: "{{ route('defect-notification.filter') }}", 
					type: "GET",
					data: {
						equipment_id: equipmentId,
						defect_type_id: defectTypeId,
						priority: priority,
						series: series
					},
					beforeSend: function() {
						
					},
					success: function(response) {
						if(response.status && response.data.length > 0) {
							var tbody = '';
							response.data.forEach(function(defect) {
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
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: 'Failed to fetch filtered defects.'
						});
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
		});
	</script>

	<script>
function setApproval() {
    document.getElementById('action_type').value = "approve";
    document.getElementById('approve_reject_heading_label').textContent = "Approve Maintenance Work Order";
}


function setRejection() {
    document.getElementById('action_type').value = "reject";
    document.getElementById('approve_reject_heading_label').textContent = "Reject Maintenance Work Order";
}


$(document).on('submit', '.ajax-submit-2', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const formData = new FormData(this);
    const submitBtn = form.find('button[type="submit"]');
    const originalBtnText = submitBtn.html();
    
    // Show loading state
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Processing...');
    
    $.ajax({
        url: form.attr('action'),
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            toastr.success(response.message || 'Operation completed successfully');
            $('#approveModal').modal('hide');
            
            // Redirect if specified
            const redirectUrl = form.data('redirect');
            if (redirectUrl) {
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 1000);
            } else {
                // Reload the page to show updated status
                window.location.reload();
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            toastr.error(response?.message || 'An error occurred while processing your request');
        },
        complete: function() {
            submitBtn.prop('disabled', false).html(originalBtnText);
        }
    });
});

// Close form submit handler - simple form submission
$('#closeForm').on('submit', function(e) {
    $('.preloader').show();
});
</script>
<script>
$(document).ready(function () {
    $('#confirmCloseSubmit').on('click', function (e) {
        e.preventDefault();

        let remarks = $('#close_remarks').val().trim();
        let attachment = $('#close_attachment')[0].files[0];
        let workOrderId = $('#workorder_id').val(); // set dynamically when opening modal

        if (!remarks) {
            Swal.fire({
                icon: 'error',
                title: 'Remarks required',
                text: 'Please provide remarks before submitting.'
            });
            return;
        }

        let formData = new FormData();
        formData.append('close_remarks', remarks);
        if (attachment) {
            formData.append('close_attachment', attachment);
        }
        formData.append('workorder_id', workOrderId);
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: "{{ route('maint-wo.close-work-order') }}",
            type: "POST",
            data: formData,
            processData: false, // important for file upload
            contentType: false, // important for file upload
            beforeSend: function () {
                $('#confirmCloseSubmit').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
            },
            success: function (response) {
                $('#confirmCloseSubmit').prop('disabled', false).html('<i data-feather="check-circle"></i> Submit Close');
                $('#closeModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message || 'Work order closed successfully.'
                }).then(() => {
                  window.location.href = "{{ route('maint-wo.index') }}"; // reload page to reflect changes
                });
            },
            error: function (xhr) {
                $('#confirmCloseSubmit').prop('disabled', false).html('<i data-feather="check-circle"></i> Submit Close');
                let errorMsg = 'Something went wrong!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            }
        });
    });

    // Function to update attribute badges display (same as BOM)
    function updateAttributeBadges($row) {
        if (!$row) return;

        let $selectElement = $row.find('.item_code');
        let rowIndex = $row.index();
        let $badgesContainer = $row.find(`#attribute-badges-${rowIndex}`);

        if ($selectElement.val() !== "") {
            let $hiddenInput = $row.find('.attribute');
            let existingAttributes = $hiddenInput.length && $hiddenInput.val() ?
                JSON.parse($hiddenInput.val()) :
                [];

            let attr = JSON.parse($selectElement.attr('data-attr') || '[]');

            let badgesHtml = '';
            let selectedCount = 0;

            // Display badges directly from existingAttributes data
            if (existingAttributes && existingAttributes.length > 0) {
                existingAttributes.forEach(function(attr) {
                    if ((attr.name || attr.attribute_name) && (attr.value || attr.attribute_value)) {
                        selectedCount++;
                        if (selectedCount <= 2) {
                            let attributeName = attr.name || attr.attribute_name;
                            let attributeValue = attr.value || attr.attribute_value;
                            
                            badgesHtml +=
                                `<span class="badge rounded-pill badge-light-primary" style="font-size:10px; margin-right:5px;cursor:pointer">
                    <strong>${attributeName}</strong>: ${attributeValue}
                </span>`;
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

    // Add click event for the entire attribute cell (4th column)
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

            if (attributesJSON.length > 0) {
                // Open attribute modal
                $('#attributeModal').modal('show');
                
                // Populate attribute modal with data
                populateAttributeModal(attributesJSON, existingAttributes, $tr);
            } else {
                showToast('info', 'No attributes available for this item.');
            }
        } else {
            showToast('warning', 'Please select an item first.');
        }
    });

    // Function to populate attribute modal (placeholder - needs actual modal implementation)
    function populateAttributeModal(attributesJSON, existingAttributes, $row) {
        // This function would populate the attribute modal
        // For now, just show a message that modal functionality needs to be implemented
        console.log('Attribute modal functionality needs to be implemented');
        console.log('Attributes:', attributesJSON);
        console.log('Existing selections:', existingAttributes);
    }

    // Initialize attribute badges for existing rows on page load
    $(document).ready(function() {
        $('.mrntableselectexcel tr').each(function() {
            let $row = $(this);
            updateAttributeBadges($row);
        });
    });

});
</script>

@endsection