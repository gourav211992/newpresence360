@use(App\Helpers\ConstantHelper)
@foreach($bom->moProductions as $key => $moProduction)
@php
   $rowCount = $key + 1;
   $stockData = $moProduction->getInventoryAndStock();
@endphp
<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}">
   {{-- <td class="customernewsection-form">
      <div class="form-check form-check-primary custom-checkbox">
         <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" value="{{$rowCount}}" data-id="{{$moProduction->id}}">
         <label class="form-check-label" for="Email_{{$rowCount}}"></label>
      </div>
   </td> --}}
   <td class="poprod-decpt"> 
      <input type="text" readonly value="{{$moProduction->item_code ?? $moProduction?->item?->item_code}}" name="production_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code " />
      <input type="hidden" name="productions[{{$rowCount}}][item_id]" value="{{$moProduction->item_id}}"/>
      <input type="hidden" name="productions[{{$rowCount}}][item_code]" value="{{$moProduction->item_code ?? $moProduction?->item?->item_code}}"/>

      @php
      $selectedAttr = $moProduction->productionAttributes ? $moProduction->productionAttributes()->pluck('attribute_id')->all() : []; 
      @endphp
      @foreach($moProduction->productionAttributes as $attributeHidden)
         <input type="hidden" name="productions[{{$rowCount}}][attr_group_id][{{$attributeHidden->attribute_name}}][attr_id]" value="{{$attributeHidden->id}}">
      @endforeach
      @foreach($moProduction->item?->itemAttributes as $itemAttribute)
         @foreach ($itemAttribute->attributes() as $value)
            @if(in_array($value->id, $selectedAttr))
            <input type="hidden" name="productions[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="{{$value->id}}">
            @endif
         @endforeach
      @endforeach
  </td>
  <td>
      <input type="text" name="productions[{{$rowCount}}][item_name]" value="{{$moProduction?->item?->item_name}}" class="form-control mw-100 mb-25" readonly/>
  </td>
   <td class="poprod-decpt"> 
      @foreach($moProduction?->item?->itemAttributes as $index => $attribute) 
         <span class="badge rounded-pill badge-light-primary"><strong data-group-id="{{$attribute->attributeGroup->id}}"> {{$attribute->attributeGroup->name}}</strong>: @foreach ($attribute->attributes() as $value) 
            @if(in_array($value->id, $selectedAttr))
                  {{ $value->value }}
            @endif
         @endforeach </span>
      @endforeach
      {{-- <button type="button" class="btn p-25 btn-sm btn-outline-secondary attributeBtn" data-row-count="{{$rowCount}}" style="font-size: 10px">Attributes</button> --}}
   </td>
   <td>
      <select disabled class="form-select mw-100 " name="productions[{{$rowCount}}][uom_id]">
         <option value="{{$moProduction->uom_id}}">{{$moProduction?->uom?->name}}</option>
      </select>
   </td>
   <td>
      <input disabled type="text" value="{{number_format($moProduction->required_qty,4)}}" step="any" class="form-control mw-100 text-end"  name="productions[{{$rowCount}}][required_qty]"/>
   </td>
   @if(!in_array($bom->document_status,[ConstantHelper::CLOSED, ConstantHelper::POSTED]))
   <td>
    <input disabled type="text" step="any" class="form-control mw-100 text-end" value="{{number_format(@$stockData['confirmedStocks'], 4)}}"  name="productions[{{$rowCount}}][available_stock]"/>
   </td>
   @endif
 {{-- @if(!in_array($bom->document_status,[ConstantHelper::CLOSED, ConstantHelper::POSTED]))
 <td>
    <input disabled type="number" step="any" value="{{$moProduction->required_qty - floatval($stockData['confirmedStocks'] ?? 0)}}" class="form-control mw-100 text-end"  name="productions[{{$rowCount}}][to_be_produced]"/>
 </td>
 @endif --}}   
 <td>
    @if(!in_array($moProduction?->mo?->document_status,[ConstantHelper::CLOSED, ConstantHelper::POSTED]))
    <input type="number" step="any" value="{{number_format($moProduction->required_qty,4)}}" class="form-control mw-100 text-end"  name="productions[{{$rowCount}}][produced_qty]"/>
    {{-- <input type="number" step="any" value="{{number_format(($moProduction->required_qty - floatval($stockData['confirmedStocks'] ?? 0)),4)}}" class="form-control mw-100 text-end"  name="productions[{{$rowCount}}][produced_qty]"/> --}}
    @endif
    @if(in_array($moProduction?->mo?->document_status,[ConstantHelper::CLOSED, ConstantHelper::POSTED]))
    <input disabled type="text" step="any" value="{{number_format(($moProduction->produced_qty ?? 0),4)}}" class="form-control mw-100 text-end"  name="productions[{{$rowCount}}][produced_qty]"/>
    @endif
 </td>

   @if(in_array($moProduction?->mo?->document_status,[ConstantHelper::CLOSED, ConstantHelper::POSTED]))
      <td>
         <input disabled type="text" step="any" value="{{number_format(($moProduction->rate ?? 0),4)}}" class="form-control mw-100 text-end"  name="productions[{{$rowCount}}][rate]"/>
      </td>
      <td>
         <input disabled type="text" step="any" value="{{ number_format($moProduction->value, 4) }}" class="form-control mw-100 text-end"  name="productions[{{$rowCount}}][value]"/>
      </td>
   @endif

   <input type="hidden" name="productions[{{$rowCount}}][mo_production_item_id]" value="{{$moProduction->id}}">
</tr>
@endforeach