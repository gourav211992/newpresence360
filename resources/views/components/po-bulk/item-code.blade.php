<input type="text" name="component_item_name[{{$rowCount}}]"  class="form-control" value="{{$row?->item_code}}" readonly>
<input type="hidden" name="components[{{$rowCount}}][item_id]" value="{{$row?->item_id}}" />
<input type="hidden" name="components[{{$rowCount}}][item_code]" value="{{$row?->item_code}}" />
<input type="hidden" name="components[{{$rowCount}}][item_name]" value="{{$row?->item?->name ?? ''}}" />
<input type="hidden" name="components[{{$rowCount}}][hsn_id]" value="{{$row?->hsn_id}}" />
<input type="hidden" name="components[{{$rowCount}}][hsn_code]" value="{{$row?->hsn_code}}" />