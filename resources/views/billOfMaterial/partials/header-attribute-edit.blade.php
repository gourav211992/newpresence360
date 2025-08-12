@if($specifications->count())
   @foreach($specifications as $specification)
   <div class="col-md-3 heaer_item">
      <div class="mb-1">
         <label class="form-label">{{$specification->specification_name ?? ''}} <span class="text-danger">*</span></label>
         <input type="text" id="customer_name" value="{{$specification->value ?? ''}}" class="form-control mw-100 ledgerselecct" name="customer_name" readonly />  
      </div>
   </div>
   @endforeach
@endif
@if($item?->itemAttributes->count())
@php
   $itemAttIds = $bom->bomAttributes()->pluck('item_attribute_id')->toArray();
   $itemAttributes = $item?->itemAttributes()->whereIn('id',$itemAttIds)->get();
@endphp
@foreach($itemAttributes as $index => $attribute)
@php
$headerAttribute = $bom->bomAttributes()->where('attribute_name',$attribute->attribute_group_id)->first(); 
@endphp
@if(empty($isCopy) || !$isCopy)
   @if(isset($headerAttribute)) 
   <input type="hidden" name="attributes[{{$index + 1 }}][attr_group_id][{{$headerAttribute->attribute_name}}][attr_id]" value="{{$headerAttribute->id}}">
   @endif
@endif
   <div class="col-md-3 heaer_item header_attr">
      <div class="mb-1">
      <label class="form-label">{{$attribute->attributeGroup->name}} <span class="text-danger">*</span></label>  
      <input type="hidden" name="attributes[{{ $index + 1 }}][attr_group_id][{{$attribute->attribute_group_id}}][attr_group_id]" value="{{$attribute->attributeGroup->id}}">
      <select class="form-select" name="attributes[{{ $index + 1 }}][attr_group_id][{{$attribute->attribute_group_id}}][attr_name]" disabled>
         <option value="">Select</option>
         @if(isset($oldAttributes[$attribute->id]))
            <option value="{{ $oldAttributes[$attribute->id]['value_id'] }}" selected>
                  {{ $oldAttributes[$attribute->id]['value_label'] }}
            </option>
         @endif
         @foreach ($attribute->attributes() as $value)
         @if(in_array($value->id, $selectedAttributes))
            <option value="{{ $value->id }}" selected>
                {{ $value->value }}
            </option>
            @break
         @endif
         @endforeach
      </select>
   </div>
</div>
@endforeach
@endif