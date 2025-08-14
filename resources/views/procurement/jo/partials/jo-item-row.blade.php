@foreach($po->joItems as $key => $po_item)
@php
   $rowCount = $key + 1;
@endphp
<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}" @if($rowCount < 2 ) class="trselected" @endif>
 <td>
    <input type="text" name="component[{{$rowCount}}][so_id]" value="{{$po_item?->so?->full_document_number}}" class="form-control mw-100 mb-25" readonly/>
</td>
 <td class="poprod-decpt"> 
    <input type="text" name="component_item_name2[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code" readonly value="{{$po_item->item_code}}" />
    <input type="hidden" name="component[{{$rowCount}}][item_id]" value="{{$po_item->item_id}}" />
    @php
      $selectedAttr = $po_item->attributes ? $po_item->attributes()->whereNotNull('attribute_value')->pluck('attribute_value')->all() : []; 
      @endphp
      @foreach($po_item?->item?->itemAttributes as $itemAttribute)
            @if(count($selectedAttr))
                @foreach ($itemAttribute->attributes() as $value)
                @if(in_array($value->id, $selectedAttr))
                <input type="hidden" name="component[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][item_attr_id]" value="{{$itemAttribute->id}}">
                <input type="hidden" name="component[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="{{$value->id}}">
                @endif
                @endforeach
            @else
                <input type="hidden" name="component[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="">
            @endif
      @endforeach
</td>
<td>
    <input type="text" readonly name="component[{{$rowCount}}][item_name]" value="{{$po_item?->item?->item_name}}" class="form-control mw-100 mb-25"/>
</td>
<td>
    <input type="text" readonly name="component[{{$rowCount}}][item_type]" value="{{$po_item?->rm_type == 'rm' ? 'RM' : 'FG'}}" class="form-control mw-100 mb-25"/>
</td>
<td class="poprod-decpt attributeBtn" data-disabled="true" id="itemAttribute_{{$rowCount}}" data-count="{{$rowCount}}" attribute-array="{{$po_item->item_attributes_array()}}"> 
</td>
<td>
    <input type="hidden" name="component[{{$rowCount}}][inventoty_uom_id]" value="{{$po_item->inventoty_uom_id}}">
    <select  class="form-select mw-100 disabled-input" name="component[{{$rowCount}}][uom_id]">
         <option value="{{$po_item?->uom?->id}}">{{ucfirst($po_item?->uom?->name)}}</option>
         @foreach($po_item?->item?->alternateUOMs as $alternateUOM)
         @if($alternateUOM?->uom?->id == $po_item->inventory_uom_id)
         <option value="{{$alternateUOM?->uom?->id}}" {{$alternateUOM?->uom?->id == $po_item->inventory_uom_id ? 'selected' : '' }}>{{$alternateUOM?->uom?->name}}</option>
         @endif
         @endforeach
      </select>
</td>
<td><input readonly type="number" class="form-control mw-100 text-end" value="{{$po_item->qty}}" name="component[{{$rowCount}}][qty]" step="any"></td>
<td><input readonly type="number" value="{{$po_item?->consumed_qty}}" name="component[{{$rowCount}}][cons_qty]" class="form-control mw-100 text-end" step="any" /></td>
<input type="hidden" name="component[{{$rowCount}}][jo_item_id]" value="{{$po_item?->id}}">
{{-- <input type="hidden" name="component[{{$rowCount}}][so_id]" value="{{$po_item?->so_id}}">
<input type="hidden" name="component[{{$rowCount}}][jo_product_id]" value="{{$po_item?->id}}">
<input type="hidden" name="component[{{$rowCount}}][pwo_id]" value="{{$po_item?->pwoSoMapping?->pwo_id}}">
<input type="hidden" name="component[{{$rowCount}}][pwo_so_mapping_id]" value="{{$po_item?->pwoSoMapping?->id}}"> --}}
</tr>
@endforeach