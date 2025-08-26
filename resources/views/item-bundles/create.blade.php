@extends('layouts.app')

@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('item-bundles.store') }}" data-redirect="{{ route('item-bundles.index') }}">
    @csrf
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Item Bundle</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('item-bundles.index') }}">Home</a></li>
                                        <li class="breadcrumb-item active">Add New</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">   
                            <a href="{{ route('item-bundles.index') }}" class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                                <i data-feather="arrow-left-circle"></i> Back
                            </a>
                            <input type="hidden" name="document_status" id="document_status">
                            <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" value="draft">
                                <i data-feather="save"></i> Save as Draft
                            </button>
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
                                                    Status : <span class="text-success">Approved</span>
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
														<input type="text" name="category_name" class="form-control category-autocomplete" placeholder="Type to search group">
														<input type="hidden" name="category_id" class="category-id">
														<input type="hidden" name="category_type" class="category-type" value="Product">
														<input type="hidden" name="cat_initials" class="cat_initials-id" value="">
													</div>
													<div class="col-md-3">
														<a href="{{route('categories.index')}}"  target="_blank" class="voucehrinvocetxt mt-0">Add Group</a>
													</div>
												</div>
												<div class="row align-items-center mb-1">
													<div class="col-md-3">
														<label class="form-label">SKU Name <span class="text-danger">*</span></label>
													</div>
													<div class="col-md-5">
														<input type="text" name="sku_name" class="form-control header-required sku-initial-input" />
													</div>
													<div class="col-md-3">
														<input type="text" name="sku_initial" class="form-control" placeholder="SKU Initial" />
													</div>
												</div>
												<div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">SKU Code <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="sku_code" class="form-control header-required" />
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Store Front SKU Code <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="front_sku_code" class="form-control header-required" />
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
                                                    <i data-feather="x-circle"></i> Delete</a>
                                                <a id="add_item_section" href="#" class="btn btn-sm btn-outline-primary">
                                                    <i data-feather="plus"></i> Add New Item</a>
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
                                                <!-- Initial Item Row -->
                                                <tr id="item_row_0" class="item_row">
                                                    <td class="customernewsection-form">
                                                        <div class="form-check form-check-primary custom-checkbox">
                                                            <input type="checkbox" class="form-check-input item_checkbox" id="item_checkbox_0">
                                                            <label class="form-check-label" for="item_checkbox_0"></label>
                                                        </div>
                                                    </td>
                                                    <!-- Item Code & ID -->
                                                    <td class="poprod-decpt">
                                                        <input type="text" name="bundle_item_details[0][item_code]" id="item_code_0" placeholder="Select Item Code" class="form-control mw-100 item_code_input ledgerselecct mb-25 item-required" />
                                                        <input type="hidden" name="bundle_item_details[0][item_id]" id="item_id_0" class="item_id" />
                                                        <input type="hidden" name="bundle_item_details[0][remarks]" id="item_remarks_0" />
                                                        <input type="hidden" name="bundle_item_details[0][hsn_id]" id="hsn_0" />
                                                        <input type="hidden" name="bundle_item_details[0][hsn_code]" id="item_hsn_0" />
                                                    </td>
                                                    <!-- Item Name -->
                                                    <td class="poprod-decpt">
                                                        <input type="text" name="bundle_item_details[0][item_name]" id="item_name_0" class="form-control mw-100 item_name_input ledgerselecct mb-25 item-required" readonly />
                                                    </td>
                                                    <!-- Attributes -->
                                                    <td class="poprod-decpt">
                                                        <button type="button" class="btn p-25 btn-sm btn-outline-secondary attribute_button" data-row-index="0" style="font-size: 10px;">Attributes</button>
                                                        <input type="hidden" name="bundle_item_details[0][attributes]" id="attribute_value_0" class="attribute_value" />
                                                        <div class="attribute_display"></div>
                                                    </td>
                                                    <!-- UOM -->
                                                    <td>
                                                        <select class="form-select mw-100 uom_select item-required" name="bundle_item_details[0][uom_id]" id="uom_id_0">
                                                            <option value="">Select UOM</option>
                                                        </select>
                                                    </td>
                                                    <!-- Quantity -->
                                                    <td>
                                                        <input type="text" name="bundle_item_details[0][qty]" id="item_qty_0" placeholder="Qty" class="form-control mw-100 item_qty mb-25 item-required" />
                                                    </td>
                                                </tr>
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
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <label class="form-label" for="final_remarks">Final Remarks</label>
                                                <textarea name="final_remarks" id="final_remarks" rows="4" class="form-control" placeholder="Enter Remarks here..."></textarea>
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
$(document).ready(function () {
	function applyCapsLock() {
		$('input[type="text"], input[type="number"]').each(function() {
			$(this).val($(this).val().toUpperCase());
		});
		$('input[type="text"], input[type="number"]').on('input', function() {
			var value = $(this).val().toUpperCase();  
			$(this).val(value); 
		});
	}
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
        lastRow.find('.item-required').each(function() {
            if (!$(this).val().trim()) {
                valid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        return valid;
    }

    initializeItemAutocomplete("#item_code_0");
    $(".item_row").each(function(){
        let rowIndex = $(this).attr("id").split("_")[2];
        renderAttributeBadges(rowIndex);
        updatePartDetails(rowIndex);
    });

    $(document).on("click", "#add_item_section", function (e) {
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
		applyCapsLock();
    });

    $(document).on("click", "#delete_item_section", function () {
        deleteItemRows();
    });

    function initializeItemAutocomplete(selector) {
        $(selector).autocomplete({
            source: function (request, response) {
                let selectedItemIds = $(".item_id").map(function () { return $(this).val(); }).get();
                $.ajax({
                    url: '/search',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        q: request.term,
                        type: 'item_bundle_module',
                        selectedAllItemIds: JSON.stringify(selectedItemIds)
                    },
                    success: function (data) {
                        response($.map(data, function (item) {
                            return {
                                label: `${item.item_name} (${item.item_code})`,
                                value: item.item_code,
                                id: item.id,
                                item_name: item.item_name,
                                uom_id: item.uom_id,
                                uom_name: item.uom?.name || '',
                                item_remark: item.item_remark|| '',
                                hsn_id: item.hsn?.id,
                                hsn: item.hsn?.code,
                                has_attributes: item.item_attributes_count > 0
                            };
                        }));
                    }
                });
            },
            minLength: 0,
            select: function (event, ui) {
                let $row = $(this).closest("tr");
                let rowIndex = $row.attr("id").split("_")[2];
                $("#item_id_" + rowIndex).val(ui.item.id);
                $("#item_code_" + rowIndex).val(ui.item.value);
                $("#item_name_" + rowIndex).val(ui.item.item_name);
                $("#uom_id_" + rowIndex).html(`<option value="${ui.item.uom_id}">${ui.item.uom_name}</option>`);
                $("#item_remarks_" + rowIndex).val(ui.item.item_remark); 
                $("#hsn_" + rowIndex).val(ui.item.hsn_id); 
                $("#item_hsn_" + rowIndex).val(ui.item.hsn); 
                $("#attribute_value_" + rowIndex).val(""); 
                $("#item_row_" + rowIndex + " .attribute_display").html("");
                renderAttributeBadges(rowIndex);
                updatePartDetails(rowIndex);
                if (ui.item.has_attributes) {
                    $("#item_row_" + rowIndex + " .attribute_button").show().trigger("click");
                }
                return false;
            }
        }).focus(function () {
            if (!this.value) $(this).autocomplete("search", "");
        });
    }

    function addItemRow() {
        let newIndex = $(".item_row").length;
        let newRow = `
            <tr id="item_row_${newIndex}" class="item_row">
                <td class="customernewsection-form">
                    <div class="form-check form-check-primary custom-checkbox">
                        <input type="checkbox" class="form-check-input item_checkbox" id="item_checkbox_${newIndex}">
                        <label class="form-check-label" for="item_checkbox_${newIndex}"></label>
                    </div>
                </td>
                <td class="poprod-decpt">
                    <input type="text" name="bundle_item_details[${newIndex}][item_code]" id="item_code_${newIndex}" placeholder="Select Item Code" class="form-control mw-100 item_code_input ledgerselecct mb-25 item-required" />
                    <input type="hidden" name="bundle_item_details[${newIndex}][item_id]" id="item_id_${newIndex}" class="item_id" />
                    <input type="hidden" name="bundle_item_details[${newIndex}][remarks]" id="item_remarks_${newIndex}" />
                    <input type="hidden" name="bundle_item_details[${newIndex}][hsn_id]" id="hsn_${newIndex}" />
                    <input type="hidden" name="bundle_item_details[${newIndex}][hsn_code]" id="item_hsn_${newIndex}" />
                </td>
                <td class="poprod-decpt">
                    <input type="text" name="bundle_item_details[${newIndex}][item_name]" id="item_name_${newIndex}" class="form-control mw-100 item_name_input ledgerselecct mb-25 item-required" readonly />
                </td>
                <td class="poprod-decpt">
                    <button type="button" class="btn p-25 btn-sm btn-outline-secondary attribute_button" data-row-index="${newIndex}" style="font-size: 10px;">Attributes</button>
                    <input type="hidden" name="bundle_item_details[${newIndex}][attributes]" id="attribute_value_${newIndex}" class="attribute_value" />
                    <div class="attribute_display"></div>
                </td>
                <td>
                    <select class="form-select mw-100 uom_select item-required" name="bundle_item_details[${newIndex}][uom_id]" id="uom_id_${newIndex}">
                        <option value="">Select UOM</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="bundle_item_details[${newIndex}][qty]" id="item_qty_${newIndex}" placeholder="Qty" class="form-control mw-100 item_qty mb-25 item-required" />
                </td>
            </tr>
        `;
        $("#item_detail_table_body").append(newRow);
        initializeItemAutocomplete(`#item_code_${newIndex}`);
        renderAttributeBadges(newIndex);
        updatePartDetails(newIndex);
    }

    function deleteItemRows() {
        $(".item_checkbox:checked").closest("tr").remove();
    }

    function renderAttributeBadges(rowIndex){
        let attrData = $("#attribute_value_" + rowIndex).val();
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

    $(document).on("click", ".attribute_button, .attribute_display", function(e){
        let rowIndex = $(this).closest("tr").attr("id").split("_")[2];
        let itemId = $("#item_id_" + rowIndex).val();
        if(!itemId){
            Swal.fire({icon: 'error', title: 'Error', text: 'Please select an item first.'});
            return;
        }
        let selectedAttr = $("#attribute_value_" + rowIndex).val();
        selectedAttr = selectedAttr ? JSON.parse(selectedAttr) : [];
        $("#attribute").data("row-index", rowIndex); 
        getItemAttribute(itemId, rowIndex, selectedAttr);
        e.stopPropagation();
    });

    function getItemAttribute(itemId, rowIndex, selectedAttr){
        $.ajax({
            url: '{{ route("item.attr") }}',
            data: { 
                item_id: itemId,
                rowCount: rowIndex
            },
            dataType: "json",
            success: function(data){
                if(data.status == 200){
                    $("#attribute tbody").html(data.data.html);
                    if(selectedAttr.length){
                        $("#attribute tbody tr").each(function(){
                            let groupId = $(this).data("group-id");
                            let found = selectedAttr.find(v => v.group_id == groupId);
                            if(found){
                                $(this).find("select").val(found.value_id);
                            }
                        });
                    }
                    $("#attribute").modal("show");
                }
            }
        });
    }

    $(document).on("click", ".submit_attribute", function(){
        let rowIndex = $("#attribute").data("row-index");
        let attrData = [];
        $("#attribute tbody tr").each(function(){
            let groupId = $(this).data("group-id");
            let groupName = $(this).find("td:first").text().trim();
            let select = $(this).find("select");
            let attributeId = select.data("attribute-id");
            let valueId = select.val();
            let valueName = select.find("option:selected").text().trim();
            if(valueId){
                attrData.push({
                    group_id: groupId,
                    group_name: groupName,
                    attribute_id: attributeId,
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

    function updatePartDetails(rowIndex){
        let itemName = $("#item_name_" + rowIndex).val() || "";
        let itemHSN  = $("#item_hsn_" + rowIndex).val() || "";
        let itemUOM = $("#uom_id_" + rowIndex + " option:selected").text() || "";
        let itemQty = $("#item_qty_" + rowIndex).val() || "";
        let itemRemarks = $("#item_remarks_" + rowIndex).val() || "";
        let attrData = $("#attribute_value_" + rowIndex).val();
        let attrHtml = "";
        if(attrData){
            let attrs = JSON.parse(attrData);
            attrs.forEach(a=>{
                attrHtml += `<span class="badge rounded-pill badge-light-primary"><strong>${a.group_name || ""}</strong>: ${a.value_name || ""}</span> `;
            });
        }
        $("#current_item_name").text(itemName);
        $("#current_item_hsn").text(itemHSN);
        $("#current_item_uom").text(itemUOM);
        $("#current_item_qty").text(itemQty);
        $("#current_item_remarks").text(itemRemarks);
        $("#attribute_badges_container").html(attrHtml);
    }

    $(document).on("change keyup", ".item_row input, .item_row select", function(){
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
	applyCapsLock()
});
</script>
@endsection