@if(isset($specifications) && $specifications->count())
   @foreach($specifications as $specification)
   <div class="col-md-3 heaer_item"> 
      <div class="mb-1">
         <label class="form-label">{{$specification->specification_name ?? 'NA'}} <span class="text-danger">*</span></label>
         <input type="text" value="{{$specification->value ?? ''}}" class="form-control" readonly="">
   </div> 
   </div>
   @endforeach
@endif
@if(isset($item) && $item?->itemAttributes->count())
@foreach($item?->itemAttributes as $index => $attribute)
   <div class="col-md-3 heaer_item header_attr"> 
      <div class="mb-1">
      <label class="form-label">{{$attribute->attributeGroup->name}} <span class="text-danger">*</span></label>  
      <input type="hidden" name="attributes[{{ $index + 1 }}][attr_group_id][{{$attribute->attribute_group_id}}][attr_group_id]" value="{{$attribute->attributeGroup->id}}">
      <select class="form-select" name="attributes[{{ $index + 1 }}][attr_group_id][{{$attribute->attribute_group_id}}][attr_name]">
         <option value="">Select</option>
         @php
            $selectedAttributes = $attribute->attribute_id;
         @endphp
         @foreach ($attribute->attributeGroup->attributes as $value)
         @if(in_array($value->id, $selectedAttributes))
            <option value="{{ $value->id }}">
                {{ $value->value }}
            </option>
         @endif
         @endforeach
      </select>
   </div>
</div>
@endforeach
@endif