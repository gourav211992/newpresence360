@foreach($pwoItems as $key => $pwoItem)
@php
   $rowCount = $current_row_count + $key + 1;
@endphp
<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}" @if($rowCount < 2 ) class="trselected" @endif>
  <td class="customernewsection-form">
      <div class="form-check form-check-primary custom-checkbox">
         <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" value="{{$rowCount}}" data-id="">
         {{-- <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" value="{{$rowCount}}" data-id="{{$pwoItem->id}}"> --}}
         <label class="form-check-label" for="Email_{{$rowCount}}"></label>
     </div>
 </td>
 <td class="poprod-decpt"> 
    <input type="text" readonly name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code " value="{{$pwoItem?->item_code}}" />
    <input type="hidden" name="components[{{$rowCount}}][item_id]" value="{{$pwoItem?->item_id}}" />
    <input type="hidden" name="components[{{$rowCount}}][item_code]" value="{{$pwoItem?->item_code}}" /> 
    <input type="hidden" name="components[{{$rowCount}}][item_name]" value="{{$pwoItem?->item?->name}}" />
    @php
        $selectedAttr = collect($pwoItem?->attributes)->pluck('attribute_id')->all();
    @endphp
      @foreach($pwoItem?->item?->itemAttributes as $itemAttribute)
            @if(count($selectedAttr))
                @foreach ($itemAttribute->attributes() as $value)
                @if(in_array($value->id, $selectedAttr))
                <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][item_attr_id]" value="{{$itemAttribute->id}}">
                <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="{{$value->id}}">
                @endif
                @endforeach
            @else
                <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="">
            @endif
      @endforeach
</td>
<td>
    <input type="text" name="components[{{$rowCount}}][item_name]" value="{{$pwoItem?->item?->item_name}}" class="form-control mw-100 mb-25" readonly/>
</td>
<td class="poprod-decpt attributeBtn" id="itemAttribute_{{$rowCount}}" data-disabled="true" data-count="{{$rowCount}}" attribute-array="{{$pwoItem->item_attributes_array()}}"> 
</td>
<td>
    <input type="hidden" name="components[{{$rowCount}}][inventoty_uom_id]" value="{{$pwoItem->inventoty_uom_id}}">
    <select  class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
         <option value="{{$pwoItem?->uom?->id}}">{{ucfirst($pwoItem?->uom?->name)}}</option>
         @foreach($pwoItem?->item?->alternateUOMs as $alternateUOM)
         <option value="{{$alternateUOM?->uom?->id}}" {{$alternateUOM?->uom?->id == $pwoItem->inventory_uom_id ? 'selected' : '' }}>{{$alternateUOM?->uom?->name}}</option>
         @endforeach
      </select>
</td>
<td><input type="number" class="form-control mw-100 text-end" value="{{$pwoItem?->qty}}" name="components[{{$rowCount}}][qty]" step="any"></td>
<td><input type="number" name="components[{{$rowCount}}][rate]" value="" class="form-control mw-100 text-end" /></td> 
<td><input type="number" readonly value="" name="components[{{$rowCount}}][item_value]" class="form-control mw-100 text-end" step="any" /></td>
<td>
    <div class="position-relative d-flex align-items-center">
        <input type="number" readonly name="components[{{$rowCount}}][discount_amount]" class="form-control mw-100 text-end" style="width: 70px" value="" step="any" />
        <input type="hidden" name="components[{{$rowCount}}][discount_amount_header]" value=""/>
        <input type="hidden" name="components[{{$rowCount}}][exp_amount_header]" value="" />
        <div class="ms-50">
            <button type="button" data-row-count="{{$rowCount}}" class="btn p-25 btn-sm btn-outline-secondary addDiscountBtn" style="font-size: 10px">Add</button>
        </div>
    </div>
</td>
<td>
    <input type="number" value="" name="components[{{$rowCount}}][item_total_cost]" readonly class="form-control mw-100 text-end" step="any" />
</td>
<td>
    <input type="date" value="{{ date('Y-m-d') }}" name="components[{{ $rowCount }}][delivery_date]" class="form-control mw-100" />
</td>
<td>
   <div class="d-flex">
    <div class="me-50 cursor-pointer addDeliveryScheduleBtn" data-row-count="{{$rowCount}}">
        <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" data-bs-original-title="Delivery Schedule" aria-label="Delivery Schedule"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg></span>
    </div>
    <div class="me-50 cursor-pointer addRemarkBtn" data-row-count="{{$rowCount}}">
        <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" data-bs-original-title="Remarks" aria-label="Remarks"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></span></div>
    <input type="hidden" value="{{$pwoItem->remarks}}" name="components[{{$rowCount}}][remark]">
</div>
</td>
<input type="hidden" name="components[{{$rowCount}}][so_id]" value="{{$pwoItem?->so_id}}">
<input type="hidden" name="components[{{$rowCount}}][pwo_so_mapping_id]" value="{{$pwoItem?->id}}">
<input type="hidden" name="components[{{$rowCount}}][pwo_id]" value="{{$pwoItem?->pwo_id}}">
</tr>
@endforeach