@foreach($bom->items as $key => $moItem)
@php
   $rowCount = $key + 1;
   $selectedAttr = $moItem->attributes->map(fn($attribute) => intval($attribute->attribute_id))->toArray();
   $inventoryStock = App\Helpers\InventoryHelper::totalInventoryAndStock($moItem->item_id, $selectedAttr, $moItem->uom_id, $moItem->header->location_id);
@endphp
<tr>
   <td class="poprod-decpt"> 
      <input type="text" readonly value="{{$moItem->item_code}}" name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct" />
      <input type="hidden" name="component[{{$rowCount}}][item_id_2]" value="{{$moItem->item_id}}"/>
      <input type="hidden" name="component[{{$rowCount}}][item_code_2]" value="{{$moItem->item_code}}"/>
      @foreach($moItem?->item?->itemAttributes as $itemAttribute)
         @foreach ($itemAttribute->attributes() as $value)
            @if(in_array($value->id, $selectedAttr))
            <input type="hidden" name="component[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="{{$value->id}}">
            @endif
         @endforeach
      @endforeach
  </td>
  <td>
      <input type="text" name="component[{{$rowCount}}][item_name_2]" value="{{$moItem?->item?->item_name}}" class="form-control mw-100 mb-25" readonly/>
  </td>
   <td class="poprod-decpt" id="itemAttribute_{{$rowCount}}" data-count="{{$rowCount}}" attribute-array="{{$moItem->item_attributes_array()}}">
   </td>
   <td>
      <select readonly class="form-select mw-100 " name="component[{{$rowCount}}][uom_id_2]">
         <option value="{{$moItem->uom_id}}">{{$moItem?->uom?->name}}</option>
      </select>
   </td>
   <td>
      <input type="text" readonly value="{{number_format($moItem->order_qty,4)}}" step="any" class="form-control mw-100 text-end"  name="component[{{$rowCount}}][qty_2]"/>
   </td>
   <td>
      <input type="text" readonly value="{{number_format($inventoryStock['confirmedStocks'] ?? 0, 4)}}" step="any" class="form-control mw-100 text-end"  name="component[{{$rowCount}}][conf_2]"/>
   </td>
   <td>
      <input type="text" readonly value="{{number_format($inventoryStock['pendingStocks'] ?? 0, 4)}}" step="any" class="form-control mw-100 text-end"  name="component[{{$rowCount}}][unconf_2]"/>
   </td>
   <td>
      <input type="text" readonly value="{{strtoupper($moItem?->so?->book_code)}} - {{$moItem?->so?->document_number}}" step="any" class="form-control mw-100"  name="component[{{$rowCount}}][doc_no_2]"/>
   </td>
   <input type="hidden" value="{{$moItem->id}}" name="component[{{$rowCount}}][mo_item_id_2]">
</tr>
@endforeach