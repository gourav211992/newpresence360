<tr class="item_detail_row">
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary"><strong>Category</strong>:  {{$item->category->name ?? 'NA'}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>Sub Category</strong>: {{$item->subCategory->name ?? 'NA'}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>: {{$item->hsn->code}}</span>
    </td>
</tr>
@if($specifications->count())
<tr class="item_detail_row">
    <td class="poprod-decpt item_detail_attributes">
        <span class="poitemtxt mw-100"><strong>Specifications:</strong></span>
        @foreach($specifications as $specification)
            <span class="badge rounded-pill badge-light-primary"><strong data-group-id="">{{$specification->specification_name}}</strong>: {{$specification->value}}</span>
        @endforeach
    </td>
</tr>
@endif
@if($item->itemAttributes->count())
<tr class="item_detail_row">
    <td class="poprod-decpt item_detail_attributes">
        <span class="poitemtxt mw-100"><strong>Attributes:</strong></span>
        @foreach($item->itemAttributes as $index => $attribute) 
            <span class="badge rounded-pill badge-light-primary"><strong data-group-id="{{$attribute->attributeGroup->id}}"> {{$attribute->attributeGroup->name}}</strong>: @foreach ($attribute->attributes() as $value) 
                @if(in_array($value->id, $selectedAttr))
                    {{ $value->value }}
                @endif
             @endforeach </span>
        @endforeach
    </td>
</tr>
@endif
@if(isset($remark) && $remark)
<tr class="item_detail_row">
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary text-wrap"><strong>Remarks</strong>: {{@$remark ?? ''}}</span>
    </td>
</tr>
@endif