@extends('layouts.app')

@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('item-bundles.update', $bundle->id) }}" enctype="multipart/form-data" data-redirect="{{ route('item-bundles.index') }}">
    @csrf
    @method('PUT')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Edit Item Bundle</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('item-bundles.index') }}">Home</a></li>
                                        <li class="breadcrumb-item active">Edit</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                   <div class="content-header-right text-sm-end col-md-6 mb-2 mb-sm-0">
					<div class="form-group breadcrumb-right">
						<a href="{{ route('item-bundles.index') }}" class="btn btn-secondary btn-sm mb-50 mb-sm-0">
							<i data-feather="arrow-left-circle"></i> Back
						</a>
						<input type="hidden" name="document_status" id="document_status">

						@if($bundle->status == 'draft')
							<button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" value="draft">
								<i data-feather="save"></i> Save as Draft
							</button>
						@endif

						<button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0 submit-button" value="submitted">
							<i data-feather="check-circle"></i> Submit
						</button>
					</div>
				</div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body customernewsection-form">
                                    <div class="border-bottom mb-2 pb-25">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="newheader">
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-sm-end">
                                                <span class="badge rounded-pill badge-light-secondary forminnerstatus">
                                                    Status : <span class="text-success">{{ ucfirst($bundle->document_status) }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-8">
											<div>
												<div class="row align-items-center mb-1">
													<div class="col-md-3">
														<label class="form-label">Group Mapping <span class="text-danger">*</span></label>
													</div>
													<div class="col-md-3 pe-sm-0 mb-1 mb-sm-0">
														<input
															type="text"
															name="category_name"
															class="form-control category-autocomplete"
															placeholder="Type to search group"
															value="{{ $bundle->category->name ?? '' }}""
														>
														<input
															type="hidden"
															name="category_id"
															class="category-id"
															value="{{ old('category_id', $bundle->category->id ?? '') }}"
														>
														<input
															type="hidden"
															name="category_type"
															class="category-type"
															value="Product"
														>
														<input
															type="hidden"
															name="cat_initials"
															class="cat_initials-id"
															value="{{ $bundle->category->cat_initials ?? ($bundle->category->sub_cat_initials ?? '') }}">
													</div>
												</div>

												<div class="row align-items-center mb-1">
													<div class="col-md-3">
														<label class="form-label">SKU Name <span class="text-danger">*</span></label>
													</div>
													<div class="col-md-5">
														<input
															type="text"
															name="sku_name"
															class="form-control header-required sku-initial-input"
															value="{{ old('sku_name', $bundle->sku_name ?? '') }}"
														/>
													</div>
													<div class="col-md-3">
														<input
															type="text"
															name="sku_initial"
															class="form-control"
															placeholder="SKU Initial"
															maxlength="1"
															value="{{ old('sku_initial', $bundle->sku_initial ?? '') }}"
														/>
													</div>
												</div>

												<div class="row align-items-center mb-1">
													<div class="col-md-3">
														<label class="form-label">SKU Code <span class="text-danger">*</span></label>
													</div>
													<div class="col-md-5">
														<input
															type="text"
															name="sku_code"
															class="form-control header-required"
															value="{{ old('sku_code', $bundle->sku_code ?? '') }}"
														/>
													</div>
												</div>

												<div class="row align-items-center mb-1">
													<div class="col-md-3">
														<label class="form-label">Store Front SKU Code <span class="text-danger">*</span></label>
													</div>
													<div class="col-md-5">
														<input
															type="text"
															name="front_sku_code"
															class="form-control header-required"
															value="{{ old('front_sku_code', $bundle->front_sku_code ?? '') }}"
														/>
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
                                                <a href="#" id="delete_item_section" class="btn btn-sm btn-outline-danger me-50">
                                                    <i data-feather="x-circle"></i> Delete
                                                </a>
                                                <a id="add_item_section" href="#" class="btn btn-sm btn-outline-primary">
                                                    <i data-feather="plus"></i> Add New Item
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive pomrnheadtffotsticky">
                                        <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                            <thead>
                                                <tr>
                                                    <th width="62" class="customernewsection-form">
                                                        <div class="form-check form-check-primary custom-checkbox">
                                                            <input type="checkbox" class="form-check-input" id="select_all_items">
                                                            <label class="form-check-label" for="select_all_items"></label>
                                                        </div>
                                                    </th>
                                                    <th width="285">Item Code</th>
                                                    <th width="208">Item Name</th>
                                                    <th>Attributes</th>
                                                    <th>UOM</th>
                                                    <th class="numeric-alignment">Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody class="mrntableselectexcel" id="item_detail_table_body">
                                                @foreach ($bundle->bundleItems as $key => $item)
                                                @php
                                                    $attributeJson = '';
                                                    if (!empty($item->attributes)) {
                                                        $mappedAttributes = collect($item->attributes)->map(function ($attr) {
                                                            return [
                                                                'id'            => $attr['id'] ?? null,
                                                                'attribute_id'  => $attr['item_attribute_id'] ?? $attr['attribute_id'] ?? null,
                                                                'group_id'      => $attr['attr_name'] ?? $attr['group_id'] ?? null,
                                                                'group_name'    => $attr['attribute_name'] ?? $attr['group_name'] ?? '',
                                                                'value_id'      => $attr['attr_value'] ?? $attr['value_id'] ?? null,
                                                                'value_name'    => $attr['attribute_value'] ?? $attr['value_name'] ?? '',
                                                            ];
                                                        })->toArray();
                                                        $attributeJson = e(json_encode($mappedAttributes));
                                                    }
                                                @endphp
                                                <tr id="item_row_{{ $key }}" class="item_row">
                                                    <td class="customernewsection-form">
                                                        <div class="form-check form-check-primary custom-checkbox">
                                                            <input type="checkbox" class="form-check-input item_checkbox" id="item_checkbox_{{ $key }}">
                                                            <label class="form-check-label" for="item_checkbox_{{ $key }}"></label>
                                                        </div>
                                                    </td>
                                                    <td class="poprod-decpt">
                                                        <input type="text" 
                                                            name="bundle_item_details[{{ $key }}][item_code]" 
                                                            id="item_code_{{ $key }}" 
                                                            placeholder="Select Item Code" 
                                                            class="form-control mw-100 item_code_input mb-25" 
                                                            value="{{ $item->item_code }}" />
                                                        <input type="hidden" 
                                                            name="bundle_item_details[{{ $key }}][item_id]" 
                                                            id="item_id_{{ $key }}" 
                                                            class="item_id" 
                                                            value="{{ $item->item_id }}" />
                                                        <input type="hidden" 
                                                            name="bundle_item_details[{{ $key }}][id]" 
                                                            id="bundle_item_detail_id_{{ $key }}" 
                                                            value="{{ $item->id }}">
                                                        <input type="hidden" 
                                                            name="bundle_item_details[{{ $key }}][remarks]" 
                                                            id="item_remarks_{{ $key }}" 
                                                            value="{{ $item->remarks }}" />
                                                        <input type="hidden" 
                                                            name="bundle_item_details[{{ $key }}][hsn_id]" 
                                                            id="hsn_{{ $key }}" 
                                                            value="{{ $item->hsn_id }}" />
                                                        <input type="hidden" 
                                                            name="bundle_item_details[{{ $key }}][hsn_code]" 
                                                            id="item_hsn_{{ $key }}"  
                                                            value="{{ $item->hsn?->code }}" />
                                                    </td>
                                                    <td class="poprod-decpt">
                                                        <input type="text" 
                                                            name="bundle_item_details[{{ $key }}][item_name]" 
                                                            id="item_name_{{ $key }}" 
                                                            class="form-control mw-100 item_name_input mb-25" 
                                                            value="{{ $item->item->item_name ?? '' }}" 
                                                            readonly />
                                                    </td>
                                                    <td class="poprod-decpt">
                                                        @php
                                                            $hasAttrs = !empty($item->attributes);
                                                        @endphp
                                                        <button type="button"
                                                            class="btn p-25 btn-sm btn-outline-secondary attribute_button"
                                                            data-row-index="{{ $key }}"
                                                            style="font-size: 10px; {{ $hasAttrs ? 'display:none;' : '' }};">
                                                            Attributes
                                                        </button>
                                                        <input type="hidden"
                                                            name="bundle_item_details[{{ $key }}][attributes]"
                                                            id="attribute_value_{{ $key }}"
                                                            class="attribute_value"
                                                            value="{{ $attributeJson }}" />
                                                        <div class="attribute_display" id="attribute_display_{{ $key }}">
                                                            @if($hasAttrs)
                                                                @foreach ($item->attributes as $attr)
                                                                    <span class="badge rounded-pill badge-light-primary">
                                                                        {{ $attr['attribute_name'] ?? $attr['group_name'] }}:
                                                                        {{ $attr['attribute_value'] ?? $attr['value_name'] }}
                                                                    </span>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <select class="form-select mw-100 uom_select" 
                                                                name="bundle_item_details[{{ $key }}][uom_id]" 
                                                                id="uom_id_{{ $key }}" 
                                                                required>
                                                            <option value="">Select UOM</option>
                                                            @foreach($uoms as $uom)
                                                                <option value="{{ $uom->id }}" {{ $item->uom_id == $uom->id ? 'selected' : '' }}>
                                                                    {{ $uom->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                            name="bundle_item_details[{{ $key }}][qty]" 
                                                            id="item_qty_{{ $key }}" 
                                                            placeholder="Qty" 
                                                            class="form-control mw-100 item_qty mb-25" 
                                                            value="{{ $item->qty }}" 
                                                            required min="1" step="any" />
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr valign="top">
                                                    <td colspan="6" rowspan="10">
                                                        <table class="table border">
                                                            <tr>
                                                                <td class="p-0">
                                                                    <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Part Details</strong></h6>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="poprod-decpt">
                                                                    <span class="item_detail_name"><strong>Name</strong>: <span id="current_item_name"></span></span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="poprod-decpt">
                                                                    <span class="badge rounded-pill badge-light-primary hsn_badge"><strong>HSN</strong>: <span id="current_item_hsn"></span></span>
                                                                    <span class="badge rounded-pill badge-light-primary attribute_badge"><strong>Attribute</strong>: <span id="attribute_badges_container"></span></span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="poprod-decpt">
                                                                    <span class="badge rounded-pill badge-light-primary uom_badge"><strong>Inv. UOM</strong>: <span id="current_item_uom"></span></span>
                                                                    <span class="badge rounded-pill badge-light-primary qty_badge"><strong>Qty.</strong>: <span id="current_item_qty"></span></span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="poprod-decpt">
                                                                    <span class="badge rounded-pill badge-light-secondary remarks_badge"><strong>Remarks</strong>: <span id="current_item_remarks"></span></span>
                                                                </td>
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
												<label class="form-label" for="upload_document">Upload Document</label>
												<input type="file" name="upload_document" id="upload_document" class="form-control" />
												@if(!empty($bundle->upload_document))
													<div class="mt-0">
														<a href="{{ Storage::url($bundle->upload_document) }}" target="_blank" download class="d-block file-link">
															<i class="fas fa-file file-icon"></i>
														</a>
													</div>
												@endif
											</div>
										</div>
                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <label class="form-label" for="final_remarks">Final Remarks</label>
                                                <textarea name="final_remarks" id="final_remarks" rows="4" class="form-control" placeholder="Enter Remarks here...">{{ old('final_remarks', $bundle->final_remarks) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    {{-- Attribute popup --}}
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
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th>Attribute Name</th>
                                    <th>Attribute Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dynamically filled by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="button" class="btn btn-primary submit_attribute">Select</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
	function applyCapsLock() {
		$('input[type="text"], input[type="number"]').each(function() {
			$(this).val($(this).val().toUpperCase());
		});
		$('input[type="text"], input[type="number"]').on('input', function() {
			$(this).val($(this).val().toUpperCase());
		});
	}
    let itemCounter = $(".item_row").length;
    $(".item_row").each(function() {
        let rowIndex = $(this).attr("id").split("_")[2];
        renderAttributeBadges(rowIndex);
        updatePartDetails(rowIndex);
        initializeItemAutocomplete(`#item_code_${rowIndex}`);
    });

    $("#add_item_section").click(function(e) {
        e.preventDefault();
        if (!isHeaderFilled()) {
            Swal.fire({icon: 'error', title: 'Error', text: 'Please fill all header details first.'});
            return false;
        }
        if (!isLastRowFilled()) {
            Swal.fire({icon: 'error', title: 'Error', text: 'Please fill previous item row completely before adding another row.'});
            return false;
        }
        addItemRow();
    });

    $("#delete_item_section").click(function(e) {
        e.preventDefault();
        $(".item_checkbox:checked").closest("tr").remove();
    });

    function isHeaderFilled() {
        let filled = true;
        $('.header-required').each(function() {
            if (!$(this).val().trim()) {
                filled = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        return filled;
    }

    function isLastRowFilled() {
        let lastRow = $('.item_row').last();
        if (!lastRow.length) return true;
        let valid = true;
        lastRow.find('.item_code_input, .item_name_input, .uom_select, .item_qty').each(function() {
            if (!$(this).val().trim()) {
                valid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        return valid;
    }

    function initializeItemAutocomplete(selector) {
        $(selector).autocomplete({
            source: function(request, response) {
                let selectedItemIds = $(".item_id").map(function() { 
                    return $(this).val(); 
                }).get();
                $.ajax({
                    url: '/search',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        q: request.term,
                        type: 'item_bundle_module',
                        selectedAllItemIds: JSON.stringify(selectedItemIds)
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: `${item.item_name} (${item.item_code})`,
                                value: item.item_code,
                                id: item.id,
                                item_name: item.item_name,
                                uom_id: item.uom_id,
                                uom_name: item.uom?.name || '',
                                item_remark: item.item_remark || '',
                                hsn_id: item.hsn?.id,
                                hsn: item.hsn?.code,
                                has_attributes: item.item_attributes_count > 0
                            };
                        }));
                    }
                });
            },
            minLength: 0,
            select: function(event, ui) {
                let $row = $(this).closest("tr");
                let rowIndex = $row.attr("id").split("_")[2];
                $("#item_id_" + rowIndex).val(ui.item.id);
                $("#item_code_" + rowIndex).val(ui.item.value);
                $("#item_name_" + rowIndex).val(ui.item.item_name);
                $("#uom_id_" + rowIndex).html(`<option value="${ui.item.uom_id}">${ui.item.uom_name}</option>`);
                $("#uom_id_" + rowIndex).val(ui.item.uom_id).trigger("change");
                $("#item_remarks_" + rowIndex).val(ui.item.item_remark || "");
                $("#hsn_" + rowIndex).val(ui.item.hsn_id || "");
                $("#item_hsn_" + rowIndex).val(ui.item.hsn || "");
                $("#attribute_value_" + rowIndex).val("");
                $("#attribute_display_" + rowIndex).html("");
                renderAttributeBadges(rowIndex);
                updatePartDetails(rowIndex);
                if (ui.item.has_attributes) {
                    $("#item_row_" + rowIndex + " .attribute_button").show().trigger("click");
                }
                return false;
            }
        }).focus(function() {
            if (!this.value) $(this).autocomplete("search", "");
        });
    }

    function addItemRow() {
        let newIndex = itemCounter;
        let newRow = `
            <tr id="item_row_${newIndex}" class="item_row">
                <td class="customernewsection-form">
                    <div class="form-check form-check-primary custom-checkbox">
                        <input type="checkbox" class="form-check-input item_checkbox" id="item_checkbox_${newIndex}">
                        <label class="form-check-label" for="item_checkbox_${newIndex}"></label>
                    </div>
                </td>
                <td class="poprod-decpt">
                    <input type="text"
                        name="bundle_item_details[${newIndex}][item_code]"
                        id="item_code_${newIndex}"
                        placeholder="Select Item Code"
                        class="form-control mw-100 item_code_input ledgerselecct mb-25" />
                    <input type="hidden"
                        name="bundle_item_details[${newIndex}][item_id]"
                        id="item_id_${newIndex}"
                        class="item_id" />
                    <input type="hidden"
                        name="bundle_item_details[${newIndex}][id]"
                        value="" />
                    <input type="hidden"
                        name="bundle_item_details[${newIndex}][remarks]"
                        id="item_remarks_${newIndex}" />
                    <input type="hidden"
                        name="bundle_item_details[${newIndex}][hsn_id]"
                        id="hsn_${newIndex}" />
                    <input type="hidden"
                        name="bundle_item_details[${newIndex}][hsn_code]"
                        id="item_hsn_${newIndex}" />
                </td>
                <td class="poprod-decpt">
                    <input type="text"
                        name="bundle_item_details[${newIndex}][item_name]"
                        id="item_name_${newIndex}"
                        class="form-control mw-100 item_name_input ledgerselecct mb-25"
                        readonly />
                </td>
                <td class="poprod-decpt">
                    <button type="button"
                            class="btn p-25 btn-sm btn-outline-secondary attribute_button"
                            data-row-index="${newIndex}"
                            style="font-size: 10px;">Attributes</button>
                    <input type="hidden"
                        name="bundle_item_details[${newIndex}][attributes]"
                        id="attribute_value_${newIndex}"
                        class="attribute_value" />
                    <div class="attribute_display" id="attribute_display_${newIndex}"></div>
                </td>
                <td>
                    <select class="form-select mw-100 uom_select"
                            name="bundle_item_details[${newIndex}][uom_id]"
                            id="uom_id_${newIndex}" required>
                        <option value="">Select UOM</option>
                        <!-- Populate with server-side UOM options if needed -->
                    </select>
                </td>
                <td>
                    <input type="number"
                        name="bundle_item_details[${newIndex}][qty]"
                        id="item_qty_${newIndex}"
                        placeholder="Qty"
                        class="form-control mw-100 item_qty mb-25"
                        required min="1" step="any" />
                </td>
            </tr>
        `;
        $("#item_detail_table_body").append(newRow);
        initializeItemAutocomplete(`#item_code_${newIndex}`);
        renderAttributeBadges(newIndex);
        itemCounter++;
    }

    function renderAttributeBadges(rowIndex) {
        let attrData = $("#attribute_value_" + rowIndex).val();
        if (attrData && attrData.indexOf('&quot;') !== -1) {
            attrData = $('<textarea/>').html(attrData).text();
        }
        let btn = $(`#item_row_${rowIndex} .attribute_button`);
        let html = "";
        if(attrData){
            try {
                let attrs = JSON.parse(attrData);
                if(Array.isArray(attrs) && attrs.length > 0){
                    html = attrs.map(a =>
                        `<span class="badge rounded-pill badge-light-primary"><strong>${a.group_name}</strong>: ${a.value_name}</span>`
                    ).join(" ");
                    btn.hide();
                }else{
                    btn.show();
                }
            }catch{
                btn.show();
            }
        }else{
            btn.show();
        }
        $(`#item_row_${rowIndex} .attribute_display`).html(html);
    }

    $(document).on("click", ".attribute_button, .attribute_display", function(e) {
        let rowIndex = $(this).closest("tr").attr("id").split("_")[2];
        let itemId = $("#item_id_" + rowIndex).val();
        if (!itemId) {
            Swal.fire({icon: 'error', title: 'Error', text: 'Please select an item first.'});
            return;
        }
        let attrVal = $("#attribute_value_" + rowIndex).val().trim();
        if (attrVal && attrVal.indexOf('&quot;') !== -1) {
            attrVal = $('<textarea/>').html(attrVal).text();
        }
        let selectedAttr = [];
        if (attrVal) {
            try {
                selectedAttr = JSON.parse(attrVal);
            } catch (e) {
                selectedAttr = [];
            }
        }
        $("#attribute").data("row-index", rowIndex);
        getItemAttribute(itemId, rowIndex, selectedAttr);
        e.stopPropagation();
    });

    function getItemAttribute(itemId, rowIndex, selectedAttr) {
        let bundleItemDetailId = $(`#bundle_item_detail_id_${rowIndex}`).val() || null;
        $.ajax({
            url: '{{ route("item.attr") }}',
            data: { 
                item_id: itemId, 
                rowCount: rowIndex,
                bundle_item_detail_id: bundleItemDetailId 
            },
            dataType: "json",
            success: function(data) {
                if (data.status == 200) {
                    $("#attribute tbody").html(data.data.html);
                    if (selectedAttr.length) {
                        $("#attribute tbody tr").each(function() {
                            let groupId = $(this).data("group-id");
                            let found = selectedAttr.find(v => v.group_id == groupId);
                            if (found) {
                                $(this).find("select").val(found.value_id);
                            }
                        });
                    }
                    $("#attribute").modal("show");
                }
            }
        });
    }

    $(document).on("click", ".submit_attribute", function() {
        let rowIndex = $("#attribute").data("row-index");
        let attrData = [];
        $("#attribute tbody tr").each(function() {
            let groupId = $(this).data("group-id");
            let groupName = $(this).find("td:first").text().trim();
            let select = $(this).find("select");
            let attributeId = select.data("attribute-id");
            let valueId = select.val();
            let valueName = select.find("option:selected").text().trim();
            if (valueId) {
                attrData.push({
                    attribute_id: attributeId,
                    group_id: groupId,
                    group_name: groupName,
                    value_id: valueId,
                    value_name: valueName
                });
            }
        });
        $("#attribute_value_" + rowIndex).val(JSON.stringify(attrData));
        renderAttributeBadges(rowIndex);
        updatePartDetails(rowIndex);
        $("#attribute").modal("hide");
    });

    function updatePartDetails(rowIndex) {
        let itemName = $("#item_name_" + rowIndex).val() || "";
        let itemHSN = $("#item_hsn_" + rowIndex).val() || "";
        let itemUOM = $("#uom_id_" + rowIndex + " option:selected").text() || "";
        let itemQty = $("#item_qty_" + rowIndex).val() || "";
        let itemRemarks = $("#item_remarks_" + rowIndex).val() || "";
        let attrHtml = "";
        let attrData = $("#attribute_value_" + rowIndex).val();
        if (attrData && attrData.indexOf('&quot;') !== -1) {
            attrData = $('<textarea/>').html(attrData).text();
        }
        if (attrData) {
            try {
                let attrs = JSON.parse(attrData);
                if (Array.isArray(attrs)) {
                    attrs.forEach(a => {
                        attrHtml += `<span class="badge rounded-pill badge-light-primary"><strong>${a.group_name || ""}</strong>: ${a.value_name || ""}</span> `;
                    });
                }
            } catch (e) {}
        }
        $("#current_item_name").text(itemName);
        $("#current_item_hsn").text(itemHSN);
        $("#current_item_uom").text(itemUOM);
        $("#current_item_qty").text(itemQty);
        $("#current_item_remarks").text(itemRemarks);
        $("#attribute_badges_container").html(attrHtml);
    }

    $(document).on("change keyup", ".item_row input, .item_row select", function() {
        let rowIndex = $(this).closest("tr").attr("id").split("_")[2];
        updatePartDetails(rowIndex);
        renderAttributeBadges(rowIndex);
    });

    $("#select_all_items").on('click', function () {
        $(".item_checkbox").prop('checked', $(this).prop('checked'));
    });
	function generateSkuInitials(skuName) {
			if (!skuName) return "";

			const words = skuName.trim().split(/\s+/);

			if (words.length === 1) {
				return words[0].substring(0, 3).toUpperCase();
			} else if (words.length === 2) {
				return (words[0].substring(0, 2) + words[1].substring(0, 1)).toUpperCase();
			} else {
				return (words[0].substring(0, 1) + words[1].substring(0, 1) + words[2].substring(0, 1)).toUpperCase();
			}
		}

		$(document).on('input', '.sku-initial-input', function () {
			const skuName = $(this).val();
			const skuInitials = generateSkuInitials(skuName);
			$('input[name="sku_initial"]').val(skuInitials); 
		});
	 applyCapsLock();
});
</script>
@endsection