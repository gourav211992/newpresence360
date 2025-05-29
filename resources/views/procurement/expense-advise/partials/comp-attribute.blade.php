@foreach($item->itemAttributes as $index => $attribute)
@php
   $rowKey = $rowCount . '_' . $index;
   $attrGroupId = $attribute?->attribute_group_id;
   $attrGroupName = $attribute?->attributeGroup?->name ?? 'Attribute';
   $selectedValue = "";
   foreach ($attribute->attributes() as $key => $value) {
      if(in_array($value->id, $selectedAttr)) {
         $selectedValue = $value?->value;
      }
   }
@endphp
<tr>
   <input type="hidden" name="row_count[{{$rowCount}}]" value="{{$rowCount}}">
   <td>{{$attribute->attributeGroup->name}}</td>
   <td>
    <input type="hidden" name="comp_attribute[{{$rowCount}}][item_id]" value="{{$item->id}}">
    <input value="{{$selectedValue}}" name="comp_attribute[{{$rowCount}}][item_name]" type="text" class="form-control mw-100 ledgerselecct attr-autocomplete" data-row="{{$rowCount}}" data-attr-group-id="{{ $attrGroupId }}" placeholder="Type to search {{ $attrGroupName }}..." id="autocomplete_input_{{$rowKey}}" autocomplete="off">
   </td>
</tr>
@endforeach
