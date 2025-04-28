@use(App\Helpers\ConstantHelper)
@foreach($bom->moItems as $key => $moItem)
@php
   $rowCount = $key + 1;
@endphp
<tr>
   @if(strtolower($bom->so_tracking_required) == 'yes')
   <td>
      <input type="text" name="component[{{$rowCount}}][doc_no_2]" value="{{strtoupper($moItem?->so?->book_code)}} - {{$moItem?->so?->document_number}}" class="form-control mw-100 mb-25" readonly/>
  </td>
  @endif
   <td class="poprod-decpt"> 
      <input type="text" readonly value="{{$moItem->item_code}}" name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct" />
      <input type="hidden" name="component[{{$rowCount}}][item_id_2]" value="{{$moItem->item_id}}"/>
      <input type="hidden" name="component[{{$rowCount}}][item_code_2]" value="{{$moItem->item_code}}"/>
  </td>
  <td>
      <input type="text" name="component[{{$rowCount}}][item_name_2]" value="{{$moItem?->item?->item_name}}" class="form-control mw-100 mb-25" readonly/>
  </td>
   <td class="poprod-decpt">
      {{-- <button type="button" class="btn p-25 btn-sm btn-outline-secondary attributeBtn" data-row-count="{{$rowCount}}" style="font-size: 10px">Attributes</button> --}}
    @php
    $selectedAttr = $moItem->attributes ? $moItem->attributes()->pluck('attribute_value')->all() : []; 
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
      <input type="text" readonly value="{{number_format($moItem->qty,4)}}" step="any" class="form-control mw-100 text-end"  name="component[{{$rowCount}}][qty_2]"/>
   </td>
   <td>
      <input type="text" readonly value="{{number_format($moItem->consumed_qty,4)}}" step="any" class="form-control mw-100 text-end"  name="component[{{$rowCount}}][consumed_qty_2]"/>
   </td>
   @if(in_array($bom->document_status,[ConstantHelper::CLOSED, ConstantHelper::POSTED]))
   <td>
      <input type="text" readonly value="{{number_format($moItem->rate,4)}}" step="any" class="form-control mw-100 text-end"  name="component[{{$rowCount}}][qty_2]"/>
   </td>
   <td>
      <input type="text" readonly value="{{number_format($moItem->value,4)}}" step="any" class="form-control mw-100 text-end"  name="component[{{$rowCount}}][qty_2]"/>
   </td>
   @endif
   <input type="hidden" value="{{$moItem->id}}" name="component[{{$rowCount}}][mo_item_id_2]">
</tr>
@endforeach