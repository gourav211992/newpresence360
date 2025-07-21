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
    <td class="no-wrap">{{$piItem->pi?->book?->book_name ?? ''}}</td>
    <td class="no-wrap">{{$piItem->pi?->document_number ?? ''}}</td>
    <td class="no-wrap">{{$piItem->pi?->getFormattedDate('document_date') ?? ''}}</td>
    <td class="no-wrap">{{$piItem->item_code ?? ''}}</td>
    <td class="no-wrap">{{$piItem?->item?->item_name}}</td>
    <td class="no-wrap">{!! $html !!}</td>
    <td class="no-wrap">{{$piItem?->uom?->name}}</td>
    <td>{{$piItem->indent_qty - $piItem->order_qty}}</td>
    <td class="fw-bolder text-dark no-wrap">
        @php
        $approvedVendorIds = \App\Helpers\ItemHelper::getItemApprovedVendors($piItem->item_id, $documentDate) ?? [];
        @endphp
        <select class="form-select select2" name="vend_name">
            @if(count($approvedVendorIds))
                @php
                $approvedVendors = \App\Models\Vendor::whereIn('id',$approvedVendorIds)->get();

                if($piItem->vendor_id) {
                    $firstVendorId = $piItem->vendor_id;
                } else {
                    $firstVendorId = $approvedVendors?->first()?->id;
                }
                @endphp
                @foreach($approvedVendors as $vendor)
                    <option value="{{$vendor?->id}}" {{ $vendor->id == $firstVendorId ? 'selected' : '' }}>{{$vendor?->company_name}}</option>
                @endforeach
            @else
                <option value=""></option>
                @foreach($vendors as $vendor)
                <option value="{{$vendor?->id}}" {{$vendor?->id == $piItem?->vendor_id  ? 'selected' : '' }}>{{$vendor?->company_name}}</option>
                @endforeach
            @endif
        </select>
    </td>
    <td class="no-wrap">{{$piItem?->so ? strtoupper($piItem?->so?->book_code) .'-'. $piItem?->so?->document_number : '' }}</td>
    <td class="no-wrap">{{$piItem->pi?->store?->store_name ?? ''}}</td>
    <td class="no-wrap">{{$piItem->pi?->sub_store_id ? $piItem->pi?->sub_store?->name : $piItem->pi?->requester?->name }}</td>
    <td class="no-wrap">{{$piItem->remarks ?? ''}}</td>
</tr>
@empty
<tr>
    <td colspan="12" class="text-center">No record found!</td>
</tr>
@endforelse