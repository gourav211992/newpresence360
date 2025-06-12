@forelse($pwoItems as $piItem)
@php
$attributes = $piItem?->attributes;
$html = '';
foreach($attributes as $attribute) {
$attr = \App\Models\AttributeGroup::where('id', @$attribute['attribute_group_id'])->first();
$attrValue = \App\Models\Attribute::where('id', @$attribute['attribute_id'])->first();
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
            <input class="form-check-input pi_item_checkbox" type="checkbox" name="pi_item_check" value="{{$piItem->id}}" data-pwo-id="{{$piItem?->pwo_id}}">
        </div> 
    </td>   
    <td class="no-wrap">{{$piItem->pwo?->book?->book_name ?? ''}}</td>
    <td class="no-wrap">{{$piItem->pwo?->document_number ?? ''}}</td>
    <td class="no-wrap">{{$piItem->pwo?->getFormattedDate('document_date') ?? ''}}</td>
    <td class="no-wrap">{{$piItem->store?->store_name ?? ''}}</td>
    <td class="no-wrap">{{$piItem->item_code ?? ''}}</td>
    <td class="no-wrap">{{$piItem?->item?->item_name}}</td>
    <td class="no-wrap">{!! $html !!}</td>
    <td class="no-wrap">{{$piItem?->uom?->name}}</td>
    <td>{{$piItem->qty - $piItem->jo_qty}}</td>
    <td class="no-wrap">{{$piItem?->so ? strtoupper($piItem?->so?->book_code) .'-'. $piItem?->so?->document_number : '' }}</td>
    <td class="no-wrap">{{$piItem?->so?->customer?->company_name ?? ''}}</td>
</tr>
@empty
<tr>
    <td colspan="12" class="text-center">No record found!</td>
</tr>
@endforelse