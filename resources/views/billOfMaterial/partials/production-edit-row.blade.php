@foreach($bom->bomProductions ?? [] as $key => $bomProduction)
@php
    $rowCount = $key + 1;
    $selectedAttr = array_column($bomProduction->attributes, 'attribute_id') ?? [];
@endphp
<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}">
    <td class="customernewsection-form">
       <div class="form-check form-check-primary custom-checkbox">
          <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" value="{{$rowCount}}" data-id="{{$bomProduction->id}}">
          <label class="form-check-label" for="Email_{{$rowCount}}"></label>
       </div>
    </td>
    <td>
        <input type="text" placeholder="Select" class="form-control mw-100  ledgerselecct" value="{{$bomProduction?->station?->name}}" name="product_station" />
        <input type="hidden" name="productions[{{$rowCount}}][station_id]" value="{{$bomProduction?->station_id}}">
        <input type="hidden" name="productions[{{$rowCount}}][station_name]" value="{{$bomProduction?->station?->name}}">
     </td>
    <td>
       <input type="text" name="prod_item_name[{{$rowCount}}]" value="{{$bomProduction->item_code}}" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code" />
       {{-- <input type="text" name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code" /> --}}
       <input type="hidden" name="productions[{{$rowCount}}][item_id]" value="{{$bomProduction->item_id}}"/>
       <input type="hidden" name="productions[{{$rowCount}}][item_code]" value="{{$bomProduction->item_code}}"/>
    </td>
    <td>
       <input type="text" value="{{$bomProduction?->item?->item_name}}" readonly name="product_name[{{$rowCount}}]" class="form-control mw-100 ledgerselecct" />
       @foreach($bomProduction->item?->itemAttributes as $itemAttribute)
         @foreach ($itemAttribute->attributes() as $value)
            @if(in_array($value->id, $selectedAttr))
            <input type="hidden" name="productions[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="{{$value->id}}">
            @endif
         @endforeach
      @endforeach
    </td>
    <td class="poprod-decpt"> 
       <button type="button" class="btn p-25 btn-sm btn-outline-secondary attributeBtn" data-row-count="{{$rowCount}}" style="font-size: 10px">Attributes</button>
    </td>
    <td>
       <select class="form-select mw-100 "  name="productions[{{$rowCount}}][uom_id]">
            <option value="{{$bomProduction->uom_id}}">{{$bomProduction?->uom?->name}}</option>
       </select>
    </td>
    <td>
        <input type="number" step="any" value="{{$bomProduction->qty ?? 1}}" class="form-control mw-100 text-end"  name="productions[{{$rowCount}}][qty]" value="1"/>
    </td>
    <input type="hidden" name="productions[{{$rowCount}}][id]" value="{{$bomProduction?->id}}">
 </tr>
 @endforeach