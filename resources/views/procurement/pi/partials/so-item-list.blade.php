@forelse($soItems as $soItem)
    @if($isAttribute)
        @php
            $attributes = $soItem->attributes;
            $html = '';
            foreach($attributes as $attribute) {
            $attr = \App\Models\AttributeGroup::where('id', @$attribute->attr_name)->first();
            $attrValue = \App\Models\Attribute::where('id', @$attribute->attr_value)->first();
                if ($attr && $attrValue) {
                    $html .= "<span class='badge rounded-pill badge-light-primary'><strong>{$attr->name}</strong>: {$attrValue->value}</span>";
                } else {
                    $html .= "<span class='badge rounded-pill badge-light-secondary'><strong></strong></span>";
                }
            }
        @endphp
    @endif
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                @if($isAttribute)
                    <input class="form-check-input pi_item_checkbox" type="checkbox" name="so_item_id" value="{{$soItem->id}}" data-item-id="{{$soItem->item_id}}" data-so-id="{{$soItem->sale_order_id}}">
                @else
                    <input class="form-check-input pi_item_checkbox" type="checkbox" name="so_item_id" value="{{$soItem->sale_order_id}}" data-item-id="{{$soItem->item_id}}" data-so-id="{{$soItem->sale_order_id}}">
                @endif
            </div>
        </td>
        <td>{{$soItem->header?->book_code ?? ''}}</td>
        <td>{{$soItem->header?->document_number ?? ''}}</td>
        <td>{{$soItem->header?->getFormattedDate('document_date')  ?? ''}}</td>
        <td>{{$soItem->item_code ?? ''}}</td>
        <td>{{$soItem->item_name ?? ''}}</td>
        @if($isAttribute)
            <td>{!! $html ? $html : '' !!}</td>
        @endif
        <td>{{$soItem->order_qty}}</td>
    <td class="fw-bolder text-dark">{{$soItem?->header?->customer_code ?? ''}}</td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center">No record found!</td>
    </tr>
@endforelse