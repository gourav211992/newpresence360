@forelse($piItems as $piItem)
@php
$attributes = $piItem->bomAttributes;
$html = '';
foreach($attributes as $attribute) {
$attr = \App\Models\AttributeGroup::where('id', @$attribute->attribute_name)->first();
$attrValue = \App\Models\Attribute::where('id', @$attribute->attribute_value)->first();
    if ($attr && $attrValue) { 
        $html .= "<span class='badge rounded-pill badge-light-primary'><strong>{$attr->name}</strong>: {$attrValue->value}</span>";
    }
}   
@endphp
<tr>
    <td>
        <div class="form-check form-check-inline me-0">
            <input class="form-check-input pi_item_checkbox" type="checkbox" name="pi_item_check" value="{{$piItem->id}}">
        </div> 
    </td>   
    <td>{{$piItem->item_code ?? 'NA'}}</td>
    <td>{{$piItem?->item?->item_name}}</td>
    <td>{!! $html !!}</td>
    <td>{{$piItem?->uom?->name}}</td>
    @if($canView)
        <td>{{number_format($piItem?->total_value ?? 0 , 2)}}</td>
    @endif
    <td>{{$piItem?->customer?->company_name}}</td>
    <td>{{$piItem?->book?->book_code ?? 'NA'}}</td>
    <td>{{$piItem?->document_number ?? 'NA'}}</td>
    <td>{{$piItem?->document_date ?? 'NA'}}</td>
    {{-- <td>{{$piItem?->department?->name ?? 'NA'}}</td> --}}
</tr>
@empty
<tr>
    <td colspan="11" class="text-center">No record found!</td>
</tr>
@endforelse