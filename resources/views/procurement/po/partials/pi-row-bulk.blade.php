@forelse($piItems ?? $pi_items as $key => $pi_item)
@php
$rowCount = $key + 1;
$attributes = $pi_item->attributes;
$html = '';
foreach($attributes as $attribute) {
$attr = \App\Models\AttributeGroup::where('id', @$attribute->attribute_name)->first();
$attrValue = \App\Models\Attribute::where('id', @$attribute->attribute_value)->first();
    if ($attr && $attrValue) { 
        $html .= "<span class='badge rounded-pill badge-light-primary'><strong>{$attr->name}</strong>: {$attrValue->value}</span>";
    }
}   
$itemCost = 0;
$attributes = null;
$itemId = $pi_item->item_id;
$uomId = $pi_item->uom_id;
$transactionDate = $documentDate ?? date('Y-m-d');
if($pi_item?->item?->approvedVendors->count()) {
    $itemVendor = $pi_item?->item?->approvedVendors[0] ?? null; 
    $currencyId = $itemVendor?->vendor?->currency_id ?? null;
    $vendorId = $itemVendor?->vendor_id;
    $itemCost = \App\Helpers\ItemHelper::getItemCostPrice($itemId, $attributes, $uomId, $currencyId, $transactionDate, $vendorId);
} else {
    $vendorId = null;
    $currencyId = $orgCurrencyId;
    $itemCost = \App\Helpers\ItemHelper::getItemCostPrice($itemId, $attributes, $uomId, $currencyId, $transactionDate, $vendorId);
}
@endphp
<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}">
  <td class="customernewsection-form">
      <div class="form-check form-check-primary custom-checkbox">
         <input name="components[{{$rowCount}}][pi_item_id]" type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" value="{{$pi_item->id}}">
         <label class="form-check-label" for="Email_{{$rowCount}}"></label>
     </div>
 </td>
<td>
    <input type="text" name="components[{{$rowCount}}][doc_number]" value="{{$pi_item?->pi?->book_code}} - {{$pi_item?->pi?->document_number}}" class="form-control mw-100 mb-25" readonly/>
</td>
<td>
    <input type="text" name="components[{{$rowCount}}][doc_date]" value="{{$pi_item?->pi?->getFormattedDate('document_date')}}" class="form-control mw-100 mb-25" readonly/>
</td>
 <td class="poprod-decpt"> 
    <input type="text" readonly  name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code " value="{{$pi_item->item_code}}" />
    <input type="hidden" name="components[{{$rowCount}}][item_id]" value="{{$pi_item->item_id}}" />
    <input type="hidden" name="components[{{$rowCount}}][item_code]" value="{{$pi_item->item_code}}" /> 
    <input type="hidden" name="components[{{$rowCount}}][item_name]" value="{{$pi_item?->item?->name}}" />
    <input type="hidden" name="components[{{$rowCount}}][hsn_id]" value="{{$pi_item->hsn_id}}" /> 
    <input type="hidden" name="components[{{$rowCount}}][hsn_code]" value="{{$pi_item->hsn_code}}" />
    @php
      $selectedAttr = $pi_item->attributes ? $pi_item->attributes()->whereNotNull('attribute_value')->pluck('attribute_value')->all() : []; 
      @endphp
      @foreach($pi_item?->item?->itemAttributes as $itemAttribute)
            @if(count($selectedAttr))
                @foreach ($itemAttribute->attributes() as $value)
                @if(in_array($value->id, $selectedAttr))
                <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][item_attr_id]" value="{{$itemAttribute->id}}">
                <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="{{$value->id}}">
                @endif
                @endforeach
            @else
                <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="">
            @endif
      @endforeach
</td>
<td>
    <input type="text" name="components[{{$rowCount}}][item_name]" value="{{$pi_item?->item?->item_name}}" class="form-control mw-100 mb-25" readonly/>
</td>
<td class="poprod-decpt"> 
    {!! $html !!}
</td>
<td>
    <input type="hidden" name="components[{{$rowCount}}][inventory_uom_code]" value="{{$pi_item->inventory_uom_code}}">
    <input type="hidden" name="components[{{$rowCount}}][inventory_uom_id]" value="{{$pi_item->inventory_uom_id}}">
    <input type="hidden" name="components[{{$rowCount}}][inventory_uom_qty]" value="{{$pi_item->inventory_uom_qty}}">
    <input type="hidden" name="components[{{$rowCount}}][uom_code]" value="{{$pi_item->uom_code}}">
    <input type="hidden" name="components[{{$rowCount}}][uom_id]" value="{{$pi_item?->uom?->id}}">
    <input readonly class="form-control" type="text" name="components[{{$rowCount}}][uom_name]" value="{{ucfirst($pi_item?->uom?->name)}}">
</td>
<td>
    <select class="form-select mw-100 select2" name="components[{{$rowCount}}][vendor_id]">
        @if($pi_item?->item?->approvedVendors->count())
            @foreach($pi_item?->item?->approvedVendors as $approvedVendor)
                <option value="{{$approvedVendor?->vendor_id}}" {{$pi_item->vendor_id == $approvedVendor?->vendor_id ? 'selected' : ''}}>{{$approvedVendor?->vendor?->company_name}}</option>
            @endforeach
        @else
            <option value=""></option>
            @foreach($vendors as $vendor)
                <option value="{{$vendor?->id}}" {{$vendor?->id == $pi_item->vendor_id ? 'selected' : '' }}>{{$vendor?->company_name}}</option>
            @endforeach
        @endif
      </select>
</td>
<td><input type="number" class="form-control mw-100 text-end" value="{{floatval($pi_item->indent_qty)}}" name="components[{{$rowCount}}][qty]" step="any"></td>
<td>
    <input type="number" name="components[{{$rowCount}}][rate]" value="{{ floatval($itemCost) }}" class="form-control mw-100 text-end" />
</td>
<td>
    <input type="date" value="{{ date('Y-m-d') }}" name="components[{{ $rowCount }}][delivery_date]" class="form-control mw-100" />
</td>
<td>
    <input readonly type="text" name="components[{{$rowCount}}][sale_order]" value="{{$pi_item?->so ? strtoupper($pi_item?->so?->book_code) .'-'. $pi_item?->so?->document_number : '' }}" class="form-control mw-100 text-end" />
</td>
<td>
    <input type="hidden" name="components[{{$rowCount}}][store_id]" value="{{$pi_item?->pi->store_id}}">
    <input readonly class="form-control mw-100" type="text" name="components[{{$rowCount}}][store_name]" value="{{ucfirst($pi_item?->pi?->store?->store_name)}}">
</td>
<td>
    <input type="hidden" name="components[{{$rowCount}}][department_id]" value="{{$pi_item?->pi->department_id}}">
    <input type="hidden" name="components[{{$rowCount}}][sub_store_id]" value="{{$pi_item?->pi->sub_store_id}}">
    <input readonly class="form-control mw-100" type="text" name="components[{{$rowCount}}][department_name]" value="{{ucfirst($pi_item?->pi?->sub_store?->name)}}">
</td>
<td>
    <input type="hidden" name="components[{{$rowCount}}][user_id]" value="{{$pi_item?->pi?->user_id}}">
    <input readonly class="form-control mw-100" type="text" name="components[{{$rowCount}}][requester_name]" value="{{ucfirst($pi_item?->pi?->requester?->name)}}">
</td>
<td><input disabled type="text" class="form-control mw-100 text-end" value="{{$pi_item->remarks}}" name="components[{{$rowCount}}][remark]"></td>
<input type="hidden" name="components[{{$rowCount}}][so_id]" value="{{$pi_item?->so_id}}">
</tr>
@empty
<tr class="no-data-row">
    <td colspan="12" class="text-center text-muted">No records found</td>
</tr>
@endforelse