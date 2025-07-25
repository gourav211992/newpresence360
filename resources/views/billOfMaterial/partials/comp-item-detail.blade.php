<tr class="item_detail_row">
   <td class="poprod-decpt">
      <span class="poitemtxt mw-100"><strong>Name</strong>: {{$item->item_name ?? 'NA'}}</span>
   </td>
</tr>
<tr class="item_detail_row">
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary"><strong>Category</strong>:  {{$item->category->name ?? 'NA'}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>Sub Category</strong>: {{$item->subCategory->name ?? 'NA'}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>: {{$item->hsn->code}}</span>
    </td>
</tr>
@if($item->itemAttributes->count())
<tr class="item_detail_row">
    <td class="poprod-decpt item_detail_attributes">
        <span class="poitemtxt mw-100"><strong>Attributes:</strong></span>
        @foreach($item->itemAttributes as $attribute)
            @php
                $groupName = $attribute?->attributeGroup?->name ?? 'Unknown';
                $groupId = $attribute?->attributeGroup?->id ?? '';
            @endphp

            <span class="badge rounded-pill badge-light-primary">
                <strong data-group-id="{{ $groupId }}">{{ $groupName }}</strong>:

                @foreach ($attribute->attributes() as $value)
                    @if($selectedAttr->contains($value->id))
                        {{ $value->value }}
                        @break
                    @endif
                @endforeach

                @if(isset($oldAttributes[$attribute->id]))
                    <span class="text-danger">{{ $oldAttributes[$attribute->id] }}</span>
                @endif
            </span>
        @endforeach
    </td>
</tr>
@endif
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
@if((isset($qty_per_unit) && $qty_per_unit) || (isset($total_qty) && $total_qty) || (isset($std_qty) && $std_qty))
<tr class="item_detail_row">
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary"><strong>Consumption per unit</strong>:  {{$qty_per_unit}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>Pieces</strong>: {{$total_qty}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>Std Qty</strong>: {{$std_qty}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>Norms</strong>: {{$output}}</span>
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