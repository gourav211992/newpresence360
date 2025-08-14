@if($specifications->count())
   @foreach($specifications as $specification)
   <div class="col-md-3 heaer_item">
      <div class="mb-1">
         <label class="form-label">Customer Name <span class="text-danger">*</span></label>
         <input type="text" id="customer_name" value="{{$specification->value ?? ''}}" class="form-control mw-100 ledgerselecct" name="customer_name" readonly />  
      </div>
   </div>
   @endforeach
@endif
@if($item?->itemAttributes->count())
@php
   $itemAttIds = $bom->moAttributes()->pluck('item_attribute_id')->toArray();
   $itemAttributes = $item?->itemAttributes()->whereIn('id',$itemAttIds)->get();
@endphp
@foreach($itemAttributes as $index => $attribute)
@php
$headerAttribute = $bom->moAttributes()->where('attribute_name',$attribute->attribute_group_id)->first(); 
@endphp
@if(isset($headerAttribute)) 
<input type="hidden" name="attributes[{{$index + 1 }}][attr_group_id][{{$headerAttribute->attribute_name}}][attr_id]" value="{{$headerAttribute->id}}">
@endif
   <div class="col-md-3 heaer_item header_attr">
      <div class="mb-1">
      <label class="form-label">{{$attribute->attributeGroup->name}} <span class="text-danger">*</span></label>  
      <input type="hidden" name="attributes[{{ $index + 1 }}][attr_group_id][{{$attribute->attribute_group_id}}][attr_group_id]" value="{{$attribute->attributeGroup->id}}">
      <select class="form-select" name="attributes[{{ $index + 1 }}][attr_group_id][{{$attribute->attribute_group_id}}][attr_name]">
         <option value="">Select</option>
         @foreach ($attribute->attributeGroup->attributes as $value)
         @if(in_array($value->id, $selectedAttributes))
            <option value="{{ $value->id }}" selected>
                {{ $value->value }}
            </option>
         @else
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