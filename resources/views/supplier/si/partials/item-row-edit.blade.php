@foreach($po->po_items as $key => $po_item)
@php
   $rowCount = $key + 1;
@endphp
<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}" @if($rowCount < 2 ) class="trselected" @endif> 
 <input type="hidden" name="components[{{$rowCount}}][pi_item_id]" value="{{$po_item->pi_item_id}}">
 <input type="hidden" name="components[{{$rowCount}}][grn_qty]" value="{{$po_item->grn_qty}}">
  <td class="customernewsection-form">
      <div class="form-check form-check-primary custom-checkbox">
         <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" value="{{$rowCount}}" data-id="{{$po_item->id}}">
         <label class="form-check-label" for="Email_{{$rowCount}}"></label>
     </div>
 </td>
 <td class="poprod-decpt"> 
    <input type="text" {{$po_item->po_item_id ? 'readonly' : ''}} @if($po_item->pi_item_id) readonly @endif name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code " value="{{$po_item->item_code}}" />
    <input type="hidden" name="components[{{$rowCount}}][item_id]" value="{{$po_item->item_id}}" />
    <input type="hidden" name="components[{{$rowCount}}][item_code]" value="{{$po_item->item_code}}" /> 
    <input type="hidden" name="components[{{$rowCount}}][item_name]" value="{{$po_item?->item?->name}}" />
    <input type="hidden" name="components[{{$rowCount}}][hsn_id]" value="{{$po_item->hsn_id}}" /> 
    <input type="hidden" name="components[{{$rowCount}}][hsn_code]" value="{{$po_item->hsn_code}}" />
    @php
      $selectedAttr = $po_item->attributes ? $po_item->attributes()->whereNotNull('attribute_value')->pluck('attribute_value')->all() : []; 
      @endphp
      @foreach($po_item->attributes as $attributeHidden)
         <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$attributeHidden->attribute_name}}][attr_id]" value="{{$attributeHidden->id}}">
      @endforeach
      @foreach($po_item?->item?->itemAttributes as $itemAttribute)
            @if(count($selectedAttr))
                @foreach ($itemAttribute->attributes() as $value)
                @if(in_array($value->id, $selectedAttr))
                <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="{{$value->id}}">
                @endif
                @endforeach
            @else
                <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="">
            @endif
      @endforeach
</td>
<td>
    <input type="text" name="components[{{$rowCount}}][item_name]" value="{{$po_item?->item?->item_name}}" class="form-control mw-100 mb-25" readonly/>
</td>
<td class="poprod-decpt"> 
    <button {{$po_item->po_item_id ? 'disabled' : ''}} type="button" {{-- data-bs-toggle="modal" data-bs-target="#attribute" --}} class="btn p-25 btn-sm btn-outline-secondary attributeBtn" data-row-count="{{$rowCount}}" style="font-size: 10px">Attributes</button>
</td>
<td>
    <input type="hidden" name="components[{{$rowCount}}][inventoty_uom_id]" value="{{$po_item->inventoty_uom_id}}">
    <select {{$po_item->po_item_id ? 'disabled' : ''}} class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
         <option value="{{$po_item?->uom?->id}}">{{ucfirst($po_item?->uom?->name)}}</option>
         @foreach($po_item?->item?->alternateUOMs as $alternateUOM)
         <option value="{{$alternateUOM?->uom?->id}}" {{$alternateUOM?->uom?->id == $po_item->inventory_uom_id ? 'selected' : '' }}>{{$alternateUOM?->uom?->name}}</option>
         @endforeach
      </select>
</td>
<td><input type="number" class="form-control mw-100 text-end" value="{{$po_item->order_qty}}" name="components[{{$rowCount}}][qty]" @if($po_item->pi_item_id) maxAmount="{{$po_item?->pi_item?->balence_qty}}" @endif step="any" @readonly(true)></td>
<td><input type="number" name="components[{{$rowCount}}][rate]" value="{{$po_item->rate}}" class="form-control mw-100 text-end" /></td> 
<td><input type="number" readonly value="{{$po_item->order_qty*$po_item->rate}}" name="components[{{$rowCount}}][item_value]" class="form-control mw-100 text-end" step="any" /></td>
<td>
    <div class="position-relative d-flex align-items-center">
        @foreach($po_item->itemDiscount as $itemDis_key => $itemDiscount)
        <input type="hidden" value="{{$itemDiscount->id}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][id]">
        <input type="hidden" value="{{$itemDiscount->ted_id}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][ted_id]">
        <input type="hidden" value="{{$itemDiscount->ted_name}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_name]">
        <input type="hidden" value="{{$itemDiscount->ted_perc}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_perc]">
        <input type="hidden" value="{{$itemDiscount->ted_amount}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_amount]">
        @endforeach
        <input type="number" readonly name="components[{{$rowCount}}][discount_amount]" class="form-control mw-100 text-end" style="width: 70px" value="{{$po_item->item_discount_amount}}" step="any" />
        <input type="hidden" name="components[{{$rowCount}}][discount_amount_header]" value="{{$po_item->header_discount_amount}}"/>
        <input type="hidden" name="components[{{$rowCount}}][exp_amount_header]" value="{{$po_item->expense_amount}}" />
        <div class="ms-50">
            <button type="button" data-row-count="{{$rowCount}}" class="btn p-25 btn-sm btn-outline-secondary addDiscountBtn" style="font-size: 10px">Add</button>
        </div>
    </div>
</td>
<td><input type="number" value="{{($po_item->order_qty*$po_item->rate) - $po_item->item_discount_amount}}" name="components[{{$rowCount}}][item_total_cost]" readonly class="form-control mw-100 text-end" step="any" />
    @foreach($po_item->taxes as $tax_key => $po_item_tax)
    <input type="hidden" value="{{$po_item_tax->id}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][id]">
    <input type="hidden" value="{{$po_item_tax->ted_id}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][t_d_id]">
    <input type="hidden" value="{{$po_item_tax->applicable_type}}" name="components[1][taxes][{{$tax_key + 1}}][applicability_type]">
    {{-- <input type="hidden" value="" name="components[1][taxes][1][t_code]"> --}}
    <input type="hidden" value="{{$po_item_tax->ted_name}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][t_type]">
    <input type="hidden" value="{{$po_item_tax->ted_perc}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][t_perc]">
    <input type="hidden" value="{{$po_item_tax->ted_amount}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][t_value]">
    @endforeach
</td>
<td>
   <div class="d-flex">
    @foreach($po_item->itemDelivery as $itemDeli_key => $itemDelivery)
        <input type="hidden" value="{{$itemDelivery->id}}" name="components[{{$rowCount}}][delivery][{{$itemDeli_key + 1}}][id]">
        <input type="hidden" value="{{$itemDelivery->qty}}" name="components[{{$rowCount}}][delivery][{{$itemDeli_key + 1}}][d_qty]">
        <input type="hidden" value="{{$itemDelivery->delivery_date}}" name="components[{{$rowCount}}][delivery][{{$itemDeli_key + 1}}][d_date]">
    @endforeach
    <div class="me-50 cursor-pointer addDeliveryScheduleBtn" data-row-count="{{$rowCount}}"{{--  data-bs-toggle="modal" data-bs-target="#delivery" --}}>    <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" data-bs-original-title="Delivery Schedule" aria-label="Delivery Schedule"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
    </div>
    <div class="me-50 cursor-pointer addRemarkBtn" data-row-count="{{$rowCount}}" {{-- data-bs-toggle="modal" data-bs-target="#Remarks" --}}>        <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" data-bs-original-title="Remarks" aria-label="Remarks"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></span></div>
    <input type="hidden" value="{{$po_item->remarks}}" name="components[{{$rowCount}}][remark]">
</div>
</td>
<input type="hidden" name="components[{{$rowCount}}][po_item_id]" value="{{$po_item->id}}">
<input type="hidden" name="components[{{$rowCount}}][si_po_item_id]" value="{{$po_item->po_item_id}}">
</tr>
@endforeach