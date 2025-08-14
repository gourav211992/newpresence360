@foreach($soItems as $key => $so_item)
@php
   $rowCount = $key + 1;
   $so = \App\Models\ErpSaleOrder::find(@$so_item['so_id']);
   $vendor = \App\Models\Vendor::find(@$so_item['vendor_id']);
   $item = \App\Models\Item::find(@$so_item['item_id']);
   $attributes = is_array($so_item['attributes']) ? $so_item['attributes'] : json_decode($so_item['attributes'], true);
   $selectedAttr = collect($attributes ?? [])
   ->pluck('attribute_value')
   ->all();
    $stocks = \App\Helpers\InventoryHelper::totalInventoryAndStock($item?->id, $selectedAttr, $item?->uom_id, $storeId);
@endphp
<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}">
  <td class="customernewsection-form">
      <div class="form-check form-check-primary custom-checkbox">
         <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" value="{{$rowCount}}" data-id="{{@$so_item['item_id']}}">
         <label class="form-check-label" for="Email_{{$rowCount}}"></label>
     </div>
 </td>
 <td class="poprod-decpt"> 
    <input @readonly(true) type="text" name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code " value="{{$item?->item_code}}" />
    <input type="hidden" name="components[{{$rowCount}}][item_id]" value="{{$item?->id}}" />
    <input type="hidden" name="components[{{$rowCount}}][item_code]" value="{{$item?->item_code}}" /> 
    <input type="hidden" name="components[{{$rowCount}}][item_name]" value="{{$item?->name}}" />
    <input type="hidden" name="components[{{$rowCount}}][hsn_id]" value="{{$item?->hsn?->id}}" /> 
    <input type="hidden" name="components[{{$rowCount}}][hsn_code]" value="{{$item?->hsn?->hsn_code}}" />
      @foreach($item?->itemAttributes ?? [] as $itemAttribute)
            @if(count($selectedAttr))
                @foreach ($itemAttribute->attributes() as $value)
                @if(in_array($value->id, $selectedAttr))
                <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute?->attribute_group_id}}][attr_name]" value="{{$value?->id}}">
                @endif
                @endforeach
            @else
                <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute?->attribute_group_id}}][attr_name]" value="">
            @endif
      @endforeach
    
</td>
<td>
    <input type="text" name="components[{{$rowCount}}][item_name]" class="form-control mw-100 mb-25" readonly value="{{$item?->item_name}}" />
</td>
<td class="poprod-decpt attributeBtn" id="itemAttribute_{{$rowCount}}" data-count="{{$rowCount}}" attribute-array="{{json_encode($so_item->item_attributes_array()) ?? []}}">
</td>
<td>
    <input type="hidden" name="components[{{$rowCount}}][inventoty_uom_id]" value="" @readonly(true)>
    <select class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
        <option value="{{$item?->uom?->id}}">{{ucfirst($item?->uom?->name)}}</option>
    </select>
</td>
<td>
    <input type="number" step="any" class="form-control mw-100 text-end"  name="components[{{$rowCount}}][qty]" value="{{@$so_item['total_qty']}}">
</td>
<td>
    <input type="number" step="any" class="form-control mw-100 text-end disabled-input"  name="components[{{$rowCount}}][avl_stock]" value="{{$stocks['confirmedStocks'] ?? 0}}">
</td>
<td>
    <input type="number" step="any" class="form-control mw-100 text-end disabled-input"  name="components[{{$rowCount}}][pending_po]" value="">
</td>
<td>
    <input type="number" step="any" class="form-control mw-100 text-end"  name="components[{{$rowCount}}][adj_qty]">
</td>
<td>
    <input type="number" step="any" class="form-control mw-100 text-end disabled-input"  name="components[{{$rowCount}}][indent_qty]" value="{{@$so_item['total_qty']}}">
</td>
<td>
    <input type="text" name="components[{{$rowCount}}][vendor_code]" class="form-control mw-100 mb-25" value="{{@$vendor?->company_name}}" />
    <input type="hidden" name="components[{{$rowCount}}][vendor_id]" value="{{@$vendor?->id}}">
</td>
@if(isset($soTrackingRequired) && $soTrackingRequired)
<td>
    <input readonly type="text" name="components[{{$rowCount}}][so_no]" class="form-control mw-100 mb-25" value="{{isset($so) && $so ? $so->full_document_number : '' }}" />
</td>
@endif
<td>
    <input type="text" name="components[{{$rowCount}}][remark]" class="form-control mw-100 mb-25"/>
</td>
<input type="hidden" name="components[{{$rowCount}}][so_id]" value="{{@$so_item['so_id']}}">
<input type="hidden" name="components[{{$rowCount}}][so_pi_mapping_item_id]" value="{{@$so_item['item_id']}}">
</tr>
@endforeach