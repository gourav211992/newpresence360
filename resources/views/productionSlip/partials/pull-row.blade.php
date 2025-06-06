@foreach($orders as $index => $order)
@php
// $currentOrderIndexVal = $index + 1;
$currentOrderIndexVal = $index;
@endphp
<tr id="item_row_{{$currentOrderIndexVal}}" class="item_header_rows" onclick = "onItemClick('{{$currentOrderIndexVal}}');">
<td class="customernewsection-form">
    <div class="form-check form-check-primary custom-checkbox">
        <input type="checkbox" class="form-check-input item_row_checks" id="item_row_check_{{$currentOrderIndexVal}}" del-index="{{$currentOrderIndexVal}}">
        <label class="form-check-label" for="Email"></label>
    </div> 
</td>
<td class="poprod-decpt">
    <input type="text" id="so_doc_{{$currentOrderIndexVal}}" name="so_doc[{{$currentOrderIndexVal}}]" class="form-control mw-100"  value="{{strtoupper($order?->pwoMapping?->so?->book_code)}} - {{$order?->pwoMapping?->so?->document_number}}" readonly>
</td>
<td class="poprod-decpt">
    <input type="text" id="customer_{{$currentOrderIndexVal}}" name="customer[{{$currentOrderIndexVal}}]" class="form-control mw-100"  value="{{$order?->customer?->company_name}}" readonly>
    <input type="hidden" id="customer_id_{{$currentOrderIndexVal}}" name = "customer_id[{{$currentOrderIndexVal}}]"  value = "{{$order?->customer?->id}}">
</td>
<td class="poprod-decpt"> 
    <input type="text" id = "items_dropdown_{{$currentOrderIndexVal}}" name="item_code[{{$currentOrderIndexVal}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input restrict" autocomplete="off" data-name="{{$order -> item ?-> item_name}}" data-code="{{$order -> item ?-> item_code}}" data-id="{{$order -> item ?-> id}}" hsn_code = "{{$order -> item ?-> hsn ?-> code}}" item-name = "{{$order -> item ?-> item_name}}" specs = "{{$order -> item ?-> specifications}}" attribute-array = "{{$order -> item_attributes_array()}}"  value = "{{$order -> item ?-> item_code}}" readonly>
    <input type = "hidden" name = "item_id[]" id = "items_dropdown_{{$currentOrderIndexVal}}_value" value = "{{$order -> item_id}}"></input>
</td>
<td class="poprod-decpt">
    <input type="text" id="items_name_{{$currentOrderIndexVal}}" name="item_name[{{$currentOrderIndexVal}}]" class="form-control mw-100"  value="{{$order?->item?->item_name}}" readonly>
</td>
<td class="poprod-decpt" id="attribute_section_{{$currentOrderIndexVal}}"> 
    <button id = "attribute_button_{{$currentOrderIndexVal}}" type = "button" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
    <input type = "hidden" name = "attribute_value_{{$currentOrderIndexVal}}" />
 </td>
<td>
    <select class="form-select" name="uom_id[]" id="uom_dropdown_{{$currentOrderIndexVal}}">
        <option value="{{$order?->item?->uom?->id}}">{{$order?->item?->uom?->name}}</option>
    </select> 
</td>
<td><input type="text" id="item_so_qty_{{$currentOrderIndexVal}}" name = "item_so_qty[{{$currentOrderIndexVal}}]" class="form-control mw-100 text-end" value = "{{$order?->soItem?->order_qty}}" readonly/></td>
<td><input type="text" id="item_qty_{{$currentOrderIndexVal}}" name = "item_qty[{{$currentOrderIndexVal}}]" oninput = "changeItemQty(this, {{$currentOrderIndexVal}});" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" value = "{{$order->pslip_bal_qty}}"/></td>
<td>
    <div class="d-flex">
            <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_{{$currentOrderIndexVal}}');">        
            <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
            <div class="me-50 cursor-pointer item_bundles" onclick = "assignDefaultBundleInfoArray({{$currentOrderIndexVal}}, true)" id = "item_bundles_{{$currentOrderIndexVal}}">    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Details" class="text-primary"><i data-feather="package"></i></span>
        </div>
    <input type="hidden" id="item_remarks_{{$currentOrderIndexVal}}" name = "item_remarks[{{$currentOrderIndexVal}}]" />
</td>
<input type="hidden" id="mo_product_id_{{$currentOrderIndexVal}}" name = "mo_product_id[{{$currentOrderIndexVal}}]"  value="{{$order?->id}}">
<input type="hidden" id="mo_id_{{$currentOrderIndexVal}}" name="mo_id[{{$currentOrderIndexVal}}]"  value="{{$order?->mo?->id}}">
<input type="hidden" id="so_id_{{$currentOrderIndexVal}}" name="so_id[{{$currentOrderIndexVal}}]"  value="{{$order?->so_id}}">
<input type="hidden" id="so_item_id_{{$currentOrderIndexVal}}" name="so_item_id[{{$currentOrderIndexVal}}]"  value="{{$order?->so_item_id}}">
<input type="hidden" id="station_id_{{$currentOrderIndexVal}}" name = "station_id[{{$currentOrderIndexVal}}]"  value = "{{$order?->mo?->station?->id}}">
</tr>
@endforeach