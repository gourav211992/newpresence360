@foreach($pwoItems as $key => $pwoItem)
@php
   $rowCount = $rowCount + $key;
   $orderQty = $pwoItem->inventory_uom_qty - $pwoItem->pwo_qty;
   $prodQty = $orderQty; 
   if($pwoItem?->bom) {
      $safetyBufferperc = \App\Helpers\ItemHelper::getBomSafetyBufferPerc($pwoItem?->bom_id);
      $prodQty = $orderQty + ($orderQty * $safetyBufferperc / 100);
      $prodQty = ceil($prodQty);
   }
@endphp
<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}">
   <td class="customernewsection-form">
      <div class="form-check form-check-primary custom-checkbox">
         <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" value="{{$rowCount}}" data-id="">
         <label class="form-check-label" for="Email_{{$rowCount}}"></label>
      </div>
   </td>
   <td class="poprod-decpt"> 
      <input readonly type="text" name="component_item_name[{{$rowCount}}]" value="{{$pwoItem?->item?->item_code}}" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code " />
      <input type="hidden" name="components[{{$rowCount}}][item_id]" value="{{$pwoItem->item_id}}"/>
      <input type="hidden" name="components[{{$rowCount}}][item_code]" value="{{$pwoItem?->item?->item_code}}"/>
      @php
        $selectedAttrValues = $pwoItem?->attributes()->pluck('attr_value')->toArray() ?? [];
      @endphp
      @foreach($pwoItem->item?->itemAttributes as $itemAttribute)
         @foreach ($itemAttribute->attributes() as $value)
            @if(in_array($value->id, $selectedAttrValues))
            <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="{{$value->id}}">
            @endif
         @endforeach
      @endforeach
  </td>
  <td>
      <input type="text" name="components[{{$rowCount}}][item_name]" value="{{$pwoItem?->item?->item_name}}" class="form-control mw-100 mb-25" readonly/>
  </td>
  <td class="poprod-decpt attributeBtn" id="itemAttribute_{{$rowCount}}" data-count="{{$rowCount}}" attribute-array="{{$pwoItem->item_attributes_array()}}">
   </td>
   <td>
      <select disabled class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
         <option value="{{$pwoItem->inventory_uom_id}}">{{$pwoItem?->inventory_uom_code}}</option>
      </select>
   </td>
   <td>
      <input type="number" value="{{$prodQty}}" step="any" class="form-control mw-100 text-end"  name="components[{{$rowCount}}][qty]"/>
   </td>
   <td>
      <input type="hidden" name="components[{{$rowCount}}][customer_id]" value="{{$pwoItem?->header?->customer_id}}" />
      <input readonly type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" value="{{$pwoItem?->header?->customer?->company_name}}" name="components[{{$rowCount}}][customer_code]" />
   </td>
   <td>{{$pwoItem?->header?->document_number ?? ''}}</td>
   <input type="hidden" name="components[{{$rowCount}}][bom_id]" value="{{$pwoItem?->bom_id}}">
   <input type="hidden" name="components[{{$rowCount}}][so_item_id]" value="{{$pwoItem?->id}}">
   <input type="hidden" name="components[{{$rowCount}}][so_id]" value="{{$pwoItem?->header?->id}}">
</tr>
@endforeach