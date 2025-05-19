@forelse($piItems as $piItem)
@php
$attributes = $piItem->attributes;
$html = '';
foreach($attributes as $attribute) {
$attr = \App\Models\AttributeGroup::where('id', @$attribute->attribute_name)->first();
$attrValue = \App\Models\Attribute::where('id', @$attribute->attribute_value)->first();
    if ($attr && $attrValue) { 
        $html .= "<span class='badge rounded-pill badge-light-primary'><strong>{$attr->name}</strong>: {$attrValue->value}</span>";
    } else {
        $html .= "<span class='badge rounded-pill badge-light-secondary'><strong>Attribute not found</strong></span>";
    }
}   
@endphp
<tr>
    <td>
        <div class="form-check form-check-inline me-0">
            <input class="form-check-input pi_item_checkbox" type="checkbox" name="pi_item_check" value="{{$piItem->id}}">
        </div> 
    </td>   
    <td>{{$piItem->po?->book?->book_name ?? 'NA'}}</td>
    <td>{{$piItem->po?->document_number ?? 'NA'}}</td>
    <td>{{$piItem->po?->document_date ?? 'NA'}}</td>
    <td>{{$piItem->item_code ?? 'NA'}}</td>
    <td>{{$piItem?->item?->item_name}}</td>
    <td>{!! $html !!}</td>
    <td>{{$piItem?->uom?->name}}</td>
    <td>{{$piItem->order_qty - $piItem->invoice_quantity}}</td>
    <td class="fw-bolder text-dark">{{$piItem?->po?->organization->name ?? 'NA'}}</td>
    <td>{{$piItem->po?->department?->name ?? 'NA'}}</td>
</tr>
@empty
<tr>
    <td colspan="11" class="text-center">No record found!</td>
</tr>
@endforelse