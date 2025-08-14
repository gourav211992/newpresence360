@forelse($soItems as $soItem)
    @php
        $attributes = json_decode($soItem->attributes, TRUE);
        $html = '';
        $orderQty = $soItem->inventory_uom_qty - $soItem->pwo_qty;
        foreach($attributes as $attribute) {
            $attN =  $attribute['attribute_name'] ?? '';
            $attV =  $attribute['attribute_value'] ?? '';
            if ($attN && $attV) { 
                $html .= "<span class='badge rounded-pill badge-light-primary'><strong>{$attN}</strong>: {$attV}</span>";
            } else {
                $html .= "<span class='badge rounded-pill badge-light-secondary'><strong></strong></span>";
            }
        }
    @endphp
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                <input class="form-check-input pi_item_checkbox" type="checkbox" name="so_item_id" value="{{$soItem?->id ?? $soItem?->sale_order_id}}" data-item-id="{{$soItem->item_id}}">
            </div> 
        </td>   
        <td>{{$soItem->header->book_code ?? ''}}</td>
        <td>{{$soItem->header->document_number ?? ''}}</td>
        <td>{{$soItem->header->getFormattedDate('document_date')}}</td>
        <td class="fw-bolder text-dark">{{$soItem?->header?->customer?->company_name ?? ''}}</td>
        <td>{{$soItem?->item?->item_code ?? ''}}</td>
        <td>{{$soItem?->item?->item_name ?? ''}}</td>
        @if($isAttribute)
            <td>{!! $html ? $html : '' !!}</td>
        @endif
        <td>{{$soItem?->inventory_uom_code ?? ''}}</td>
        <td class="text-end">{{number_format($orderQty, 2)}}</td>
        <td class="text-end">{{$soItem?->header?->store?->store_name ?? ''}}</td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center">No record found!</td>
    </tr>
@endforelse