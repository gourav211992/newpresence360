@foreach($item->itemAttributes as $index => $attribute)
<tr>
   <input type="hidden" name="row_count[{{$rowCount}}]" value="{{$rowCount}}">
   <td>{{$attribute->attributeGroup->name}}</td>
   <td>
      <select class="form-select select2" name="comp_attribute[{{$rowCount}}][item_name]" data-attr-name="{{$attribute->attributeGroup->name}}" data-attr-group-id="{{$attribute->attributeGroup->id}}">
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