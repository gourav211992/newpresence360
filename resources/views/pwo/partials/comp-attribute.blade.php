@foreach($itemAttributes as $index => $attribute)
<tr>
   <input type="hidden" name="row_count[{{$rowCount}}]" value="{{$rowCount}}">
   <td>{{$attribute?->attributeGroup?->name}}</td>
   <td>
      <input type="hidden" name="comp_attribute[{{$rowCount}}][item_id]" value="{{$item->id}}">
      <input type="hidden" name="comp_attribute[{{$rowCount}}][attribute_id]" value="{{$attribute?->attribute_group_id}}">
      <select class="form-select select2" {{isset($pwo_so_mapping_id) && $pwo_so_mapping_id ? 'disabled' : '' }}  name="comp_attribute[{{$rowCount}}][attribute_value]" data-attr-name="{{$attribute?->attributeGroup?->name}}" data-attr-group-id="{{$attribute?->attributeGroup?->id}}">
         <option value="">Select</option>
         @foreach ($attribute->attributes() as $value)
            <option value="{{ $value->id }}" {{in_array($value->id, $selectedAttr) ? 'selected' : ''}} >
                {{ $value->value }}
            </option>
         @endforeach
      </select>
   </td>
</tr>
@endforeach