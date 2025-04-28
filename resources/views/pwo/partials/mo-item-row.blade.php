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
  </td>
  <td>
      <input type="text" name="component[{{$rowCount}}][item_name_2]" value="{{$moItem?->item?->item_name}}" class="form-control mw-100 mb-25" readonly/>
  </td>
   <td class="poprod-decpt">
    @php
    $selectedAttr = $moItem->attributes ? $moItem->attributes()->pluck('attribute_id')->all() : []; 
    @endphp
    @foreach($moItem?->item?->itemAttributes as $index => $attribute) 
        <span class="badge rounded-pill badge-light-primary"><strong data-group-id="{{$attribute->attributeGroup->id}}"> {{$attribute->attributeGroup->name}}</strong>: @foreach ($attribute->attributes() as $value) 
            @if(in_array($value->id, $selectedAttr))
                {{ $value->value }}
            @endif
        @endforeach </span>
    @endforeach
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
   @if(strtolower($bom->so_tracking_required) == 'yes')
   <td>
      <input type="text" readonly value="{{strtoupper($moItem?->so?->book_code)}} - {{$moItem?->so?->document_number}}" step="any" class="form-control mw-100"  name="component[{{$rowCount}}][doc_no_2]"/>
   </td>
   @endif
   <input type="hidden" value="{{$moItem->id}}" name="component[{{$rowCount}}][mo_item_id_2]">
</tr>
@endforeach