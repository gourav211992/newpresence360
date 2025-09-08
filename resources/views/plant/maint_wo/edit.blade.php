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

              @if ($workOrder->document_status == 'draft' || ($buttons['amend'] && request('amendment') == 1))
                <button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" type="button" id="save-draft-btn">
                    <i data-feather="save"></i> Save as Draft
                </button>
				    
                <button type="submit" form="maint-wo-form" class="btn btn-primary btn-sm" id="submit-btn">
                    <i data-feather="check-circle"></i> Submit
                </button>
            @endif
		    
          </div>
        </div>
      </div>
    </div>

    {{-- Body --}}
    <div class="content-body">
      <form id="maint-wo-form" method="POST" action="{{ route('maint-wo.update', $workOrder->id) }}" enctype="multipart/form-data">
        @method('PUT')
        @csrf

        @php
          // Extract data from the work order for edit view
          $equipmentDetailsArr = $workOrder && $workOrder->equipment_details ? json_decode($workOrder->equipment_details) : (object)[];
          $refType = $equipmentDetailsArr->reference_type;
          $sparePartsData = $workOrder && $workOrder->spare_parts ? json_decode($workOrder->spare_parts, true) : [];
          $checklistData = $workOrder && $workOrder->checklist_data ? json_decode($workOrder->checklist_data, true) : [];
          
          // Debug spare parts data
          // dd('Spare Parts Data:', $sparePartsData, 'Work Order:', $workOrder->spare_parts);

          // Extract defect notification details if reference type is defect_notification
          $selectedDefectName = $equipmentDetailsArr->equipment_defect_type ?? '';
          $selectedPriority = $equipmentDetailsArr->equipment_priority ?? '';
          $reportedById = $equipmentDetailsArr->equipment_reported_by ?? null;
          $reportDateRaw = $equipmentDetailsArr->equipment_report_date ?? null;
          $reportDate = $reportDateRaw;

          // Amendment mode
          $isAmendmentMode = intval(request('amendment') ?? 0) === 1;

          // Disabled logic
          $commonFieldsDisabled = $isAmendmentMode;
          $editableFieldsDisabled = !$isAmendmentMode && ($workOrder->document_status !== 'draft');

          $editableFieldsDisabled = true;
        @endphp


        {{-- Hidden fields --}}
        <input type="hidden" name="book_code" id="book_code_input" value="{{ $workOrder->book_code ?? '' }}">
        <input type="hidden" name="doc_number_type" id="doc_number_type" value="{{ $workOrder->doc_number_type ?? '' }}">
        <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern" value="{{ $workOrder->doc_reset_pattern ?? '' }}">
        <input type="hidden" name="doc_prefix" id="doc_prefix" value="{{ $workOrder->doc_prefix ?? '' }}">
        <input type="hidden" name="doc_suffix" id="doc_suffix" value="{{ $workOrder->doc_suffix ?? '' }}">
        <input type="hidden" name="document_number" id="document_number" value="{{ $workOrder->document_number ?? '' }}">
        <input type="hidden" name="book_id" id="book_id" value="{{ $workOrder->book_id ?? '' }}">
        <input type="hidden" name="document_date" id="document_date" value="{{ $workOrder->document_date ?? '' }}">
        <input type="hidden" name="document_status" id="document_status" value="{{ $workOrder->document_status ?? '' }}">
        <input type="hidden" name="spare_parts" id="spare_parts" value="{{ $workOrder->spare_parts ?? '' }}">
        <input type="hidden" name="selected_equipment_id" id="selected_equipment_id" value="{{ $equipmentDetailsArr->equipment_id ?? '' }}">
        <input type="hidden" name="equipment_maintenance_type_name" id="equipment_maintenance_type_name" value="{{ $equipmentDetailsArr->equipment_maintenance_type_name ?? $equipmentDetailsArr->maintenance_type_name ?? '' }}">
       
        <input type="hidden" name="equipment_details" id="equipment_details" value="{{ $workOrder->equipment_details ?? '' }}">
		<input type="hidden" name="checklist_data" id="checklist_data">
        {{-- readonly/selection data populated from work order --}}
        <input type="hidden" name="defect_notification_id" id="defect_notification_id_hidden" value="{{ $workOrder->defect_notification_id ?? '' }}">
        <input type="hidden" name="equipment_category" id="equipment_category_hidden" value="{{ $equipmentDetailsArr->equipment_category ?? '' }}">
        <input type="hidden" name="equipment_name" id="equipment_name_hidden" value="{{ $equipmentDetailsArr->equipment_name ?? '' }}">
        <input type="hidden" name="defect_type" id="defect_type_hidden" value="{{ $selectedDefectName }}">
        {{-- Removed duplicate visible textarea here to avoid duplicate IDs/names. --}}
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
                      </div>
                    </div>

                    <div class="col-md-8">
                      <div class="row align-items-center mb-1">
                        <div class="col-md-3">
                          <label class="form-label">Series <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-5">
                          <select class="form-select" name="book_id" id="book_id" {{ $commonFieldsDisabled ? 'disabled' : ($editableFieldsDisabled ? 'disabled' : '') }} required>
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
                          <input type="text" class="form-control" name="document_number" id="document_number" value="{{ $workOrder->document_number ?? '' }}" {{ $commonFieldsDisabled ? 'disabled' : ($editableFieldsDisabled ? 'disabled' : '') }}>
                        </div>
                      </div>

                      <div class="row align-items-center mb-1">
                        <div class="col-md-3">
                          <label class="form-label">Doc Date <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-5">
                          <input type="date" value="{{ $workOrder->document_date }}" class="form-control" id="document_date" name="document_date" {{ $commonFieldsDisabled ? 'disabled' : ($editableFieldsDisabled ? 'disabled' : '') }} required>
                        </div>
                      </div>

                      <div class="row align-items-center mb-1">
                        <div class="col-md-3">
                          <label class="form-label">Location <span class="text-danger">*</span></label>
                        </div>
                        <div class="col-md-5">
                          <select class="form-select" name="location_id" id="location_id" {{ $commonFieldsDisabled ? 'disabled' : ($editableFieldsDisabled ? 'disabled' : '') }} required>
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
                          <button type="button" id="equipment_ref_btn" onclick="selectEquipmentReference()" data-bs-toggle="modal" data-bs-target="#reference" class="btn {{ $refType === 'equipment' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm mb-0 reference-btn" {{ $editableFieldsDisabled ? 'disabled' : '' }}>
                            <i data-feather="plus-square"></i> Equipment
                          </button>
                          <button type="button" id="defect_ref_btn" onclick="selectDefectNotificationReference()" data-bs-toggle="modal" data-bs-target="#defectlog" class="btn {{ $refType === 'defect_notification' ? 'btn-primary' : 'btn-outline-primary' }} btn-sm mb-0 reference-btn" {{ $editableFieldsDisabled ? 'disabled' : '' }}>
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
            <input type="text" placeholder="Select" value="{{ $equipmentDetailsArr->equipment_category ?? '' }}" class="form-control ledgerselecct" id="equipment_category" readonly />
          </div>
        </div>

        <div class="col-md-3 basic-equipment-field">
          <div class="mb-1">
            <label class="form-label">Equipment <span class="text-danger">*</span></label>
            <input type="hidden" name="equipment_id" id="equipment_id" value="{{ $equipmentDetailsArr->equipment_id ?? '' }}">
            <input type="text" placeholder="Select Equipment" value="{{ $equipmentDetailsArr->equipment_name ?? '' }}" class="form-control ledgerselecct" id="equipment_name" readonly required>
          </div>
        </div>

        <div class="col-md-3 basic-equipment-field">
          <div class="mb-1">
            <label class="form-label">Maintenance Type <span class="text-danger">*</span></label>
            <select class="form-select" name="equipment_maintenance_type_id" id="maintenance_type" disabled required>
              <option value="">Select Type</option>
              @php
                $allMaintenanceTypes = [];
                foreach($maintenanceTypesByEquipment ?? [] as $equipmentId => $types) {
                  foreach($types as $type) {
                    $allMaintenanceTypes[$type['id']] = $type['name'];
                  }
                }
              @endphp
              @foreach($allMaintenanceTypes as $id => $name)
                <option value="{{ $id }}" data-name="{{ $name }}" @if(($equipmentDetailsArr->equipment_maintenance_type_id ?? $equipmentDetailsArr->maintenance_type_id ?? '') == $id) selected @endif>{{ $name }}</option>
              @endforeach
            </select>
          </div>
        </div>

       

      

        @if($refType === 'defect_notification')
          <div class="col-md-3 equipment-detail-field">
            <div class="mb-1" id="defect_type_field">
              <label class="form-label">Defect Type</label>
              <select class="form-select" name="defect_type" id="defect_type_select" disabled>
                <option value="">Select</option>
                @foreach($defectTypes ?? [] as $defect)
                  <option value="{{ $defect->name }}" @if($defect->name == $selectedDefectName) selected @endif>{{ $defect->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="col-md-3 equipment-detail-field">
            <div class="mb-1" id="problem_field">
              <label class="form-label">Problem <span class="text-danger">*</span></label>
              <textarea class="form-control" name="problem" id="problem" rows="2" readonly>{{ $equipmentDetailsArr->equipment_problem ?? '' }}</textarea>
            </div>
          </div>

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

          <div class="col-md-3 equipment-detail-field">
            <div class="mb-1" id="report_date_field">
              <label class="form-label">Report Date & Time</label>
              <input type="datetime-local" name="report_date_time" id="report_date_time" value="{{ $reportDate }}" class="form-control" readonly />
            </div>
          </div>

          <div class="col-md-3 equipment-detail-field">
            <div class="mb-1" id="report_by_field">
              <label class="form-label">Reported by</label>
              <input type="text" value="{{ $equipmentDetailsArr->equipment_reported_by_name ?? '' }}" class="form-control" readonly />
            </div>
          </div>
        

        {{-- Keep these inside the same .row --}}
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
        @endif

      </div> <!-- /.row -->
    </div>   <!-- /.card-body -->
  </div>     <!-- /.card -->
</div>       <!-- /.col-12 -->


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
                      @if($refType === 'equipment')
                        <li class="nav-item" id="checklist-tab">
                          <a class="nav-link active" data-bs-toggle="tab" href="#payment">Checklist</a>
                        </li>
                        <li class="nav-item" id="spare-parts-tab">
                          <a class="nav-link" data-bs-toggle="tab" href="#attachment">Spare Parts</a>
                        </li>
                      @else
                        <li class="nav-item" id="spare-parts-tab">
                          <a class="nav-link active" data-bs-toggle="tab" href="#attachment">Spare Parts</a>
                        </li>
                      @endif
                    </ul>
                  </div>

                  <div class="tab-content pb-1">
                    {{-- Checklist tab - only show for equipment reference type --}}
					{{-- Checklist tab - only show for equipment reference type --}}
					{{-- Checklist tab - only show for equipment reference type --}}
@if($refType === 'equipment')
  <div class="tab-pane active" id="payment">
    <div class="row">
      <div class="col-md-12">
        <div class="table-responsive pomrnheadtffotsticky1">
          {{-- Hidden field to hold final JSON --}}
         

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
                    @foreach($mainCategory['checklist'] as $index => $item)
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
                             <select class="form-select mw-100 checklist-input"
                                     data-name="{{ $item['name'] }}"
                                     data-type="{{ $item['data_type'] }}"
                                     data-mandatory="{{ $item['mandatory'] ? 1 : 0 }}">
                               <option value="0" @if(!($item['value'] ?? false)) selected @endif>No</option>
                               <option value="1" @if($item['value'] ?? false) selected @endif>Yes</option>
                             </select>

                           @elseif(($item['data_type'] ?? 'text') === 'number')
                             <input type="number" class="form-control mw-100 checklist-input"
                                    data-name="{{ $item['name'] }}"
                                    data-type="{{ $item['data_type'] }}"
                                    data-mandatory="{{ $item['mandatory'] ? 1 : 0 }}"
                                    value="{{ $item['value'] ?? '' }}">

                           @elseif(($item['data_type'] ?? 'text') === 'list')
                             <select class="form-select mw-100 checklist-input"
                                     data-name="{{ $item['name'] }}"
                                     data-type="{{ $item['data_type'] }}"
                                     data-mandatory="{{ $item['mandatory'] ? 1 : 0 }}">
                               <option value="{{ $item['value'] ?? '' }}" selected>{{ $item['value'] ?? 'Select Option' }}</option>
                             </select>

                           @else
                             <input type="text" class="form-control mw-100 checklist-input"
                                    data-name="{{ $item['name'] }}"
                                    data-type="{{ $item['data_type'] }}"
                                    data-mandatory="{{ $item['mandatory'] ? 1 : 0 }}"
                                    value="{{ $item['value'] ?? '' }}">
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


                    {{-- Spare parts tab --}}
                    <div class="tab-pane {{ $refType === 'equipment' ? '' : 'active' }}" id="attachment">
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
                            <table id="itemTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                              <thead>
                                <tr>
                                  <th width="62" class="customernewsection-form">
                                    <div class="form-check form-check-primary custom-checkbox">
                                      <input type="checkbox" class="form-check-input" id="checkAll">
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
                                       <td class="customernewsection-form">
                                         <div class="form-check form-check-primary custom-checkbox">
                                           <input type="checkbox" class="form-check-input row-check" id="row_{{ $index }}">
                                           <label class="form-check-label" for="row_{{ $index }}"></label>
                                         </div>
                                       </td>
                                       <td class="poprod-decpt">
                                         <input type="hidden" name="item_id[]" class="item_id" value="{{ $part['item_id'] ?? ($part->item_id ?? '') }}">
                                         <input type="text" name="item[]" 
                                                value="{{ $part['item_code'] ?? ($part->item_code ?? '') }}"
                                                data-id="{{ $part['item_id'] ?? ($part->item_id ?? '') }}"
                                                data-code="{{ $part['item_code'] ?? ($part->item_code ?? '') }}"
                                                data-name="{{ $part['item_name'] ?? ($part->item_name ?? '') }}"
                                                data-attr="{{ $part['item_attributes'] ?? ($part->item_attributes ?? '[]') }}"
                                                class="item_code form-control mw-100 ledgerselecct mb-25" 
                                                placeholder="Select" />
                                       </td>
                                       <td class="poprod-decpt">
                                         <input type="text" 
                                                value="{{ $part['item_name'] ?? ($part->item_name ?? '') }}"
                                                class="item_name piitem form-control mw-100 ledgerselecct mb-25" 
                                                placeholder="Select"  />
                                       </td>
                                       <td class="poprod-decpt">
                                          <input type="hidden" class="attribute" value='{{ $part['attribute'] ?? ($part->attribute ?? "[]") }}'>
                                          <div class="d-flex flex-wrap gap-1" id="attribute-badges-{{ $index }}">
                                            @php
                                              $attributes = $part['attribute'] ?? ($part->attribute ?? '[]');
                                              $attributesArray = is_string($attributes) ? json_decode($attributes, true) : $attributes;
                                              $validAttributes = [];
                                              if (!empty($attributesArray) && is_array($attributesArray)) {
                                                $validAttributes = array_filter($attributesArray, function($attr) {
                                                  return (isset($attr['name']) && isset($attr['value'])) || 
                                                         (isset($attr['attribute_name']) && isset($attr['attribute_value']));
                                                });
                                              }
                                              $totalCount = count($validAttributes);
                                              $displayedCount = 0;
                                            @endphp
                                            
                                            @foreach($validAttributes as $attribute)
                                              @if($displayedCount < 2)
                                                @php $displayedCount++; @endphp
                                                <span class="badge rounded-pill badge-light-primary" style="font-size:10px; margin-right:5px;cursor:pointer">
                                                  <strong>{{ $attribute['name'] ?? $attribute['attribute_name'] }}</strong>: {{ $attribute['value'] ?? $attribute['attribute_value'] }}
                                                </span>
                                              @endif
                                            @endforeach
                                            
                                            @if($totalCount > 2)
                                              <span style="font-size:10px; color:black; margin-right:5px;cursor:pointer"><strong>...</strong></span>
                                            @endif
                                           </div>
                                         </td>
                                       <td>
                                         <select class="uom form-select mw-100" name="uom[]" required>
                                           <option value="{{ $part['uom_id'] ?? ($part->uom_id ?? '') }}">
                                             {{ $part['uom_name'] ?? ($part->uom ?? 'Select UOM') }}
                                           </option>
                                         </select>
                                       </td>
                                       <td>
                                          <input type="number" class="qty form-control mw-100" name="qty[]"
                                                 value="{{ $part['qty'] ?? ($part->qty ?? '') }}" required />
                                       </td>
                                       <td>
                                           <input type="number" class="available_stock form-control mw-100" name="available_stock[]"
                                                  value="{{ $part['available_stock'] ?? 100 }}" readonly />
                                       </td>
                                     </tr>
                                  @endforeach
                                @else
                                  <tr class="trselected">
                                    <td class="customernewsection-form">
                                      <div class="form-check form-check-primary custom-checkbox">
                                        <input type="checkbox" class="form-check-input row-check" id="row_first">
                                        <label class="form-check-label" for="row_first"></label>
                                      </div>
                                    </td>
                                    <td class="poprod-decpt">
                                      <input type="hidden" class="item_id">
                                      <input required type="text" placeholder="Select" name="item[]" class="item_code form-control mw-100 ledgerselecct mb-25" />
                                    </td>
                                    <td class="poprod-decpt">
                                      <input type="text" placeholder="Select" class="item_name form-control mw-100 ledgerselecct mb-25" />
                                    </td>
                                    <td class="poprod-decpt">
                                      <input type="hidden" class="attribute">
                                      <div class="d-flex flex-wrap gap-1" id="attribute-badges">
                                        <!-- Attribute badges will be displayed here -->
                                      </div>
                                    </td>
                                    <td>
                                      <select class="uom form-select mw-100" name="uom[]" required></select>
                                    </td>
                                    <td>
                                      <input type="number" class="qty form-control mw-100" name="qty[]" required />
                                    </td>
                                    <td>
                                      <input type="number" class="available_stock form-control mw-100" name="available_stock[]"  readonly />
                                    </td>
                                  </tr>
                                @endif
                              </tbody>
                              <tfoot>
                                 <tr valign="top">
                                   <td colspan="7" rowspan="10">
                                    <table class="table border">
                                      <tr>
                                        <td class="p-0">
                                          <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Part Details</strong></h6>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td class="poprod-decpt">
                                          <span class="poitemtxt mw-100">
                                            <strong>Name</strong>: 
                                            <span id="part_name">
                                              @if(!empty($sparePartsData) && count($sparePartsData) > 0)
                                                {{ $sparePartsData[0]['item_name'] ?? ($sparePartsData[0]->item_name ?? 'N/A') }}
                                              @endif
                                            </span>
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
                                              {{-- Debug: Show raw attribute data --}}
                                              <!-- Debug: {{ json_encode($attributesArray) }} -->
                                              @foreach($attributesArray as $attribute)
                                                {{-- Debug: Show individual attribute --}}
                                                <!-- Attribute: {{ json_encode($attribute) }} -->
                                                @if(isset($attribute['name']) && isset($attribute['value']))
                                                  <span class="badge rounded-pill badge-light-secondary me-1 mb-1">
                                                    <strong>{{ $attribute['name'] }}</strong>: {{ $attribute['value'] }}
                                                  </span>
                                                @else
                                                  {{-- Show what fields are available if name/value missing --}}
                                                  <!-- Missing name/value. Available keys: {{ implode(', ', array_keys($attribute)) }} -->
                                                @endif
                                              @endforeach
                                            @else
                                              <!-- No attributes found or not array. Data: {{ json_encode($attributesArray) }} -->
                                            @endif
                                          @endif
                                        </td>
                                      </tr>
                                      <tr>
                                        <td class="poprod-decpt">
                                          <span class="badge rounded-pill badge-light-primary">
                                            <strong>Inv. UOM</strong>: 
                                            <span id="uom">
                                              @if(!empty($sparePartsData) && count($sparePartsData) > 0)
                                                {{ $sparePartsData[0]['uom_name'] ?? ($sparePartsData[0]->uom ?? 'N/A') }}
                                              @endif
                                            </span>
                                          </span>
                                          <span class="badge rounded-pill badge-light-primary">
                                            <strong>Qty.</strong>: 
                                            <span id="qty">
                                              @if(!empty($sparePartsData) && count($sparePartsData) > 0)
                                                {{ $sparePartsData[0]['qty'] ?? ($sparePartsData[0]->qty ?? 'N/A') }}
                                              @endif
                                            </span>
                                          </span>
                                          <span class="badge rounded-pill badge-light-success">
                                            <strong>Available Stock</strong>: 
                                            <span id="available_stock">
                                              @if(!empty($sparePartsData) && count($sparePartsData) > 0)
                                                {{ $sparePartsData[0]['available_stock'] ?? 100 }}
                                              @else
                                                100
                                              @endif
                                            </span>
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
              <textarea rows="4" class="form-control" name="final_remark" placeholder="Enter Remarks here...">{{ $workOrder->final_remark ?? '' }}</textarea>
            </div>
          </div>
        </div>

        {{-- ===================== Modals ===================== --}}

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
                  <div class="col"><div class="mb-1"><label class="form-label">Equipment</label>
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
                      <label class="form-label">Maintenance Type</label>
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
                    </div>
                  </div>
                  <div class="col"><div class="mb-1"><label class="form-label">Maint. BOM</label>
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
                  <label class="form-label">Equipment</label>
                  <select class="form-control ledgerselecct" name="equipment_id">
                    <option value="">Select Equipment</option>
                    @foreach($equipments as $equipment)
                      <option value="{{ $equipment->id }}">{{ $equipment->name }}</option>
                    @endforeach
                  </select>
                  </div>
                  <div class="col">
                    <div class="mb-1">
                      <label class="form-label">Defect Type</label>
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
                <button type="button" class="btn btn-primary submitAttributeBtn">Select</button>
              </div>
            </div>
          </div>
        </div>

        {{-- Remarks Modal --}}
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

@endsection




@section('scripts')
<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
@include('plant.maint_wo.common-js-route',["wo" => isset($wo) ? $wo : null, "route_prefix" => "maint-wo"])
<script src="{{ asset('assets/js/modules/maint-wo/common-script.js') }}"></script>
<script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>

<script>
	const itemsData = @json($items);
	const sparePartsData = @json($sparePartsData);
  console.log("check the sparePartsData", sparePartsData);
  
	let rowCount = 1;

	// Populate attribute modal
	function populateAttributeModal(attributes, $row) {
		if (!attributes || attributes.length === 0) return;

		window.currentAttributeRow = $row;
		let existingAttributes = [];

		try {
			let existingAttrValue = $row.find('.attribute').val();
			if (existingAttrValue) {
				existingAttributes = JSON.parse(existingAttrValue);
			}
		} catch (e) {}

		let $attributesTable = $('#attribute_table');
		let innerHtml = '';

		attributes.forEach(function(element) {
			if (!element.values_data || element.values_data.length === 0) return;

			let optionsHtml = '<option value="">Select</option>';
			element.values_data.forEach(function(value) {
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
							${optionsHtml}
						</select>
					</td>
				</tr>
			`;
		});

		$attributesTable.html(innerHtml);
		$attributesTable.find('.select2').select2({ dropdownParent: $('#attribute') });

		$('.submitAttributeBtn').off('click').on('click', function(e) {
			e.preventDefault();
			e.stopPropagation();

			if (!window.currentAttributeRow) {
				$("#attribute").modal('hide');
				return;
			}

			let $currentRow = window.currentAttributeRow;
			let selectedAttributes = [];
			let badgesHtml = '';

			$('#attribute_table tr').each(function() {
				let $row = $(this);
				let attributeId = $row.find('input[name="id"]').val();
				let $select = $row.find('select');
				let selectedValueId = $select.val();
				let selectedValueText = $select.find('option:selected').text();
				let attributeName = $row.find('td:first').text().trim();

				if (attributeId && selectedValueId && selectedValueText !== 'Select') {
					selectedAttributes.push({
						item_attribute_id: attributeId,
						value_id: selectedValueId,
						name: attributeName,
						value: selectedValueText
					});

					badgesHtml += `<span class="badge rounded-pill badge-light-primary me-1 mb-1" style="font-size:10px;">
						<strong>${attributeName}</strong>: ${selectedValueText}
					</span>`;
				}
			});

			$currentRow.find('.attribute').val(JSON.stringify(selectedAttributes));
			let $badgeContainer = $currentRow.find('.d-flex.flex-wrap.gap-1');
			if ($badgeContainer.length) $badgeContainer.html(badgesHtml);

			$currentRow.find('.attribute').trigger('change');
			$("#attribute").modal('hide');
			window.currentAttributeRow = null;
		});
	}

	// Checklist data collection
	function collectChecklistData() {
		let formatted = [];
		$('.font-small-4').each(function() {
			let mainName = $(this).text().trim();
			if (mainName && mainName !== 'No checklist data available') {
				let entry = { main_name: mainName, checklist: [] };
				let tr = $(this).closest('tr');
				let nextTr = tr.next();

				while (nextTr.length && !nextTr.find('.font-small-4').length) {
					let input = nextTr.find('.checklist-input');
					if (input.length) {
						let inputEl = input[0];
						let checklistItem = {
							name: $(inputEl).data('name') || '',
							data_type: $(inputEl).data('type') || 'text',
							mandatory: ($(inputEl).data('mandatory') == 1),
							value: $(inputEl).val() || '',
							completed_at: new Date().toISOString().slice(0, 19).replace('T', ' '),
							completed_by: {{ auth()->id() ?? 1 }}
						};
						entry.checklist.push(checklistItem);
					}
					nextTr = nextTr.next();
				}
				if (entry.checklist.length > 0) formatted.push(entry);
			}
		});
		$('#checklist_data').val(JSON.stringify(formatted));
		return formatted;
	}

	// Collect all JSON data before submit
	function updateJsonData() {
		// Spare parts
		const allRows = [];
		$('.mrntableselectexcel tr').each(function() {
			const row = $(this);
			const itemId = row.find('.item_id').val();
			if (itemId) {
				const rowData = {
					item_id: itemId,
					item_code: row.find('.item_code').val() || '',
					item_name: row.find('.item_name').val() || '',
					attribute: row.find('.attribute').val() || '',
					qty: row.find('.qty').val() || 0,
					uom_id: row.find('.uom').val() || '',
					uom_name: row.find('.uom option:selected').text() || '',
					available_stock: row.find('.available_stock').val() || 0
				};
				allRows.push(rowData);
			}
		});
		$('#spare_parts').val(JSON.stringify(allRows));

		// Checklist
		collectChecklistData();

		// Equipment details
		const equipmentDetails = {
			reference_type: $('#reference_type').val() || '',
			equipment_category: $('#equipment_category_hidden').val() || $('#equipment_category').val() || '',
			equipment_name: $('#equipment_name_hidden').val() || $('#equipment_name').val() || '',
			equipment_id: $('#equipment_id').val() || '',
			equipment_maintenance_type_id: $('#maintenance_type').val() || '',
			equipment_maintenance_type_name: $('#equipment_maintenance_type_name').val() || $('#maintenance_type option:selected').text() || '',
			equipment_defect_type: $('#defect_type_hidden').val() || $('#defect_type_select').val() || '',
			equipment_problem: $('#problem_hidden').val() || $('#problem_field input').val() || '',
			equipment_priority: $('#priority_field select').val() || '',
			equipment_report_date: $('#report_date_time_hidden').val() || $('#report_date_field input').val() || '',
			equipment_reported_by: $('#reported_by_hidden').val() || $('#report_by_field input').val() || '',
			equipment_detailed_observations: $('#detailed_observations_field textarea').val() || '',
			equipment_supporting_documents: $('#supporting_documents_field input')[0]?.files[0]?.name || ''
		};
		$('#equipment_details').val(JSON.stringify(equipmentDetails));
	}

	// Save Draft
	const saveDraftBtn = document.getElementById('save-draft-btn');
	if (saveDraftBtn) {
		saveDraftBtn.addEventListener('click', function() {
			$('.preloader').show();
			document.getElementById('document_status').value = 'draft';
			updateJsonData();
			document.getElementById('maint-wo-form').submit();
		});
	}

	// Final Submit
	$('#maint-wo-form').on('submit', function(e) {
		e.preventDefault();
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

	// Toast function
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
			}
		});
		Toast.fire({ icon, title });
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
		showToast('error', "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach");
	@endif

	// Initialize autocomplete function
	function initAutoForItem(selector, type) {
		console.log("check the selector and type", selector, type);
		
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
				console.log("check the selectedItemIds", selectedItemIds);

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

				console.log("check the filtered", filtered);
				
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

				console.log("check the results", results);
				
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

				// Display attribute badges if item has attributes
				if (attr && attr.length > 0) {
					let badgesHtml = '';
					attr.forEach(function(attribute) {
						badgesHtml += `<span class="badge rounded-pill badge-light-primary" style="font-size:10px; margin-right:5px;">
							
						</span>`;
					});
					$input.closest('tr').find('.d-flex.flex-wrap.gap-1').html(badgesHtml);
					
					// Automatically open attribute modal if item has attributes
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
					$input.closest('tr').find('.d-flex.flex-wrap.gap-1').html('');
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

	// Initialize autocomplete for existing rows
	$(document).ready(function() {
		initAutoForItem('.item_code');
	});

	// Add New Item functionality
	$('#addNewRowBtn').on('click', function () {
		rowCount++;
		let newRow = `<tr>
			<td class="customernewsection-form">
				<div class="form-check form-check-primary custom-checkbox">
					<input type="checkbox" class="form-check-input row-check" id="row_${rowCount}">
					<label class="form-check-label" for="row_${rowCount}"></label>
				</div>
			</td>
			<td class="poprod-decpt">
				<input type="hidden" class="item_id">
				<input required type="text" placeholder="Select" name="item[]" class="item_code form-control mw-100 ledgerselecct mb-25" />
			</td>
			<td class="poprod-decpt">
				<input type="text" placeholder="Select" class="item_name form-control mw-100 ledgerselecct mb-25" />
			</td>
			<td class="poprod-decpt">
				<input type="hidden" class="attribute">
				<div class="d-flex flex-wrap gap-1" id="attribute-badges">
					<!-- Attribute badges will be displayed here -->
				</div>
			</td>
			<td>
				<select class="uom form-select mw-100" name="uom[]" required></select>
			</td>
			<td>
				<input type="number" class="qty form-control mw-100" name="qty[]" required />
			</td>
			<td>
				<input type="number" class="available_stock form-control mw-100" name="available_stock[]" 
        
        
        
        
         readonly />
			</td>
		</tr>`;
		$('.mrntableselectexcel').append(newRow);
		
		// Initialize autocomplete for the new row
		if (typeof initAutoForItem === 'function') {
			initAutoForItem('.item_code');
		}
	});

	// Delete selected rows functionality
	$('#delete').on('click', function () {
		let $rows = $('.mrntableselectexcel tr');
		let $checked = $rows.find('.row-check:checked');
		$checked.closest('tr').remove();
	});

	// Check all functionality
	$('#checkAll').on('change', function () {
		let isChecked = $(this).is(':checked');
		$('.mrntableselectexcel .row-check').prop('checked', isChecked);
	});

	// Add click handler for spare parts rows to update Part Details section
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
			let availableStock = $selected.find('.available_stock').val() || '0'; // Get available stock
			
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
			$('#available_stock').text(availableStock); // Update available stock in Part Details
			
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
						'<span class="badge rounded-pill badge-light-secondary" style="font-size:10px; color:black; margin-right:5px;cursor:pointer"><strong>...</strong></span>';
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

</script>
@endsection