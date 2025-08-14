@forelse($pwoItems as $pwoItem)
    @php
    $stationUsedQty = 0; 
    if($station_id) {
        $selectedStation = $pwoItem->stations()->where('station_id',$station_id)->first();
        $stationUsedQty = $selectedStation?->mo_product_qty ?? 0;
    }
        $attributes = $pwoItem->attributes;
        $html = '';
        foreach($attributes as $attribute) {
            $attN =  $attribute['attribute_group_name'] ?? '';
            $attV =  $attribute['attribute_name'] ?? '';
            if ($attN && $attV) { 
                $html .= "<span class='badge rounded-pill badge-light-primary'><strong>{$attN}</strong>: {$attV}</span>";
            } else {
                $html .= "<span class='badge rounded-pill badge-light-secondary'><strong>Attribute not found</strong></span>";
            }
        }
    @endphp
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                <input class="form-check-input pi_item_checkbox" type="checkbox" name="pwo_mapping_id" value="{{$pwoItem->id}}">
            </div> 
        </td>   
        <td>{{$pwoItem->pwo?->book_code ?? ''}}</td>
        <td>{{$pwoItem->pwo?->document_number ?? ''}}</td>
        <td>{{$pwoItem->pwo?->getFormattedDate('document_date')  ?? ''}}</td>
        <td>{{$pwoItem?->item?->item_code ?? ''}}</td>
        <td>{{$pwoItem?->item?->item_name ?? ''}}</td>
        <td>{!! $html ? $html : '' !!}</td>
        <td class="text-end">{{number_format(($pwoItem->inventory_uom_qty - $stationUsedQty),2)}}</td>
        <td class="fw-bolder text-dark">{{$pwoItem?->so?->customer?->company_name ?? ''}}</td>
        <td>{{$pwoItem?->so?->book_code ?? ''}}</td>
        <td>{{$pwoItem?->so?->document_number ?? ''}}</td>
        <td>{{$pwoItem?->so?->getFormattedDate('document_date')}}</td>
    </tr>
@empty
    <tr>
        <td colspan="11" class="text-center">No record found!</td>
    </tr>
@endforelse