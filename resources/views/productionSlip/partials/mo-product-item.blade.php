@forelse ($orders as $index => $order)
    @php
        $qtIndex = $loop->index;
        $attributesHTML = '';
        foreach ($order->attributes as $key => $moProductAttribute) {
            $attributeName = $moProductAttribute->dis_attribute_name ? $moProductAttribute->dis_attribute_name : '';
            $attributeValue = $moProductAttribute->dis_attribute_value ? $moProductAttribute->dis_attribute_value : '';
            $attributesHTML.="<span class='badge rounded-pill badge-light-primary' > $attributeName : $attributeValue </span>";
        }
    @endphp
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                <input class="form-check-input po_checkbox" type="checkbox" name="po_check" id="po_checkbox_{{$index}}" document-id="{{$order->id}}" document-mo-id="{{$order->mo_id}}" value="{{$index}}" >
            </div> 
        </td>   
        <td class="no-wrap">{{$order->customer_code ?? ''}}</td>
        <td class="no-wrap">{{$order?->mo?->book_code}} - {{ $order?->mo?->document_number}}</td>
        <td class="no-wrap">{{$order?->mo?->getFormattedDate('document_date')}}</td>
        <td class="no-wrap">{{$order?->mo?->station?->name}}</td>
        <td class="no-wrap">{{$order?->so?->book_code}} - {{ $order?->so?->document_number}}</td>
        <td class="no-wrap">{{$order?->so?->getFormattedDate('document_date')}}</td>
        <td class="no-wrap">{{$order?->item_code}}</td>
        <td class="no-wrap">{{$order?->item_name}}</td>
        <td class="no-wrap">{!! $attributesHTML !!}</td>
        <td class="no-wrap">{{$order?->uom?->name}}</td>
        <td class="no-wrap text-end">{{$order?->qty - $order?->short_closed_qty}}</td>
        {{-- @dd($order?->pslip_bal_qty); --}}
        <td class="no-wrap text-end">{{$order?->pslip_bal_qty}}</td>
    </tr>
@empty
    <tr>
        <td colspan="12" class="text-center">No data found</td>
    </tr>
@endforelse