@foreach($item->itemAttributes as $index => $attribute)
<tr>
   <td>{{$attribute->attributeGroup->name}}</td>
   <td>
      <select class="form-select select2" id="attribute_name_{{$attribute->attributeGroup->id}}" name="attribute_name_{{$attribute->attributeGroup->id}}" data-attr-name="{{$attribute->attributeGroup->name}}" data-attr-group-id="{{$attribute->attributeGroup->id}}">
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