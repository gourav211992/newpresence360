@use(App\Helpers\InventoryHelper)
@forelse($soProcessItems as $soProcessItem)
@php
$attributes = json_decode($soProcessItem->attributes, true);
$html = '';
$selectedAttr = [];
foreach($attributes as $attribute) {
$attr = \App\Models\ItemAttribute::where('id', @$attribute['attribute_id'])->first();
$attrValue = \App\Models\Attribute::where('id', @$attribute['attribute_value'])->first();
    if ($attr && $attrValue) { 
        $html .= "<span class='badge rounded-pill badge-light-primary'><strong>{$attr?->attributeGroup?->name}</strong>: {$attrValue->value}</span>";
    } else {
        $html .= "<span class='badge rounded-pill badge-light-secondary'><strong>Attribute not found</strong></span>";
    }
    if($attrValue) {
        $selectedAttr[] = $attrValue?->id; 
    }
}
$inventoryStock = InventoryHelper::totalInventoryAndStock($soProcessItem->item_id, $selectedAttr, $soProcessItem->uom_id, null);
$soProcessItem->attributes = $soProcessItem->item_attributes_array();
if ($soProcessItem->attributes->isNotEmpty()) {
    $soProcessItem->attributes = $soProcessItem->attributes->map(function ($attrGroup) {
        $attrGroup['values_data'] = collect($attrGroup['values_data'])
            ->filter(fn($attr) => $attr->selected)
            ->values();
        return $attrGroup;
    });
}
@endphp
<tr>
    <td>
        <div class="form-check form-check-inline me-0">
            <input class="form-check-input pi_item_checkbox" type="checkbox" name="pi_item_check" value="{{$soProcessItem->item_id}}" data-item="{{json_encode($soProcessItem)}}">

        </div> 
    </td>  
    @if($soTracking == 'yes')
        <td>{{ (strtoupper($soProcessItem?->so?->book_code) .'-'. $soProcessItem?->so?->document_number)}}</td>
    @endif
    <td>{{ $soProcessItem?->item?->item_code ?? ''}}</td>
    <td>{{ $soProcessItem?->item?->item_name ?? ''}}</td>
    <td>{!!  $html ?? '' !!}</td>
    <td>{{  $soProcessItem?->item?->uom?->name ?? ''}}</td>
    <td class="text-end">{{number_format($soProcessItem->total_qty,2)}}</td>
    <td class="text-end">{{number_format($inventoryStock['confirmedStocks'], 2)}}</td>
    <td class="text-end">{{number_format($inventoryStock['pendingStocks'], 2)}}</td>
    <td>{{$soProcessItem?->vendor?->company_name ?? ''}}</td>
    {{-- <td class="text-end">{{number_format(0, 2)}}</td> --}}
</tr>
@empty
<tr>
    <td colspan="10" class="text-center">No record found!</td>
</tr>
@endforelse