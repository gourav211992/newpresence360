<tr>
    <td class="p-0">
        <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
    </td>
</tr>
<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary"><strong>Category</strong>:  {{$item?->category?->name ?? 'NA'}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>Sub Category</strong>: {{$item?->subCategory?->name ?? 'NA'}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>: {{$item?->hsn?->code}}</span>
        @if(isset($serviceItem))
            <span class="badge rounded-pill badge-light-primary"><strong>Service HSN</strong>: {{$serviceItem->hsn?->code}}</span>
        @endif
    </td>
</tr>
@if($specifications->count())
<tr class="item_detail_row">
    <td class="poprod-decpt item_detail_attributes">
        <span class="poitemtxt mw-100">
            <strong>Specifications:</strong>
        </span>
        @foreach($specifications as $specification)
            <span class="badge rounded-pill badge-light-primary"><strong data-group-id="">{{$specification->specification_name ?? ''}}</strong>: {{$specification->value ?? ''}}</span>
        @endforeach
    </td>
</tr>
@endif
@if(isset($item))
    @if($item?->itemAttributes->count() > 0)
    <tr> 
        <td class="poprod-decpt">
            <span class="poitemtxt mw-100"><strong>Attributes:</strong></span>
            @foreach($item->itemAttributes as $index => $attribute)
            <span class="badge rounded-pill badge-light-primary"><strong data-group-id="{{$attribute?->attributeGroup?->id}}">{{$attribute?->attributeGroup?->name}}</strong>: @foreach ($attribute?->attributes()  as $value) 
                    @if(in_array($value->id ?? 0, $selectedAttr))
                        {{ $value->value }}
                    @endif
                 @endforeach</span>
            @endforeach
        </td> 
    </tr> 
    @endif
@endif
<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary"><strong>Inv. UOM</strong>:  {{$uomName}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>Qty.</strong>: {{number_format($qty,2)}}</span>
    </td>
</tr>
@if(isset($delivery) && $delivery && count($delivery['delivery'] ?? 0))
<tr>
    <td class="poprod-decpt">
        <span class="poitemtxt mw-100"><strong>Delivery Schedule</strong>:</span>
        @foreach($delivery['delivery'] ?? [] as $d)
        <span class="badge rounded-pill badge-light-secondary"><strong>{{ \Carbon\Carbon::parse(@$d['dDate'])->format('d-m-y') }}</strong> : {{number_format(($d['dQty'] ?? 0), 2)}}</span>
        @endforeach
    </td>
</tr>
@endif
@if(isset($remark) && $remark)
<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-secondary text-wrap"><strong>Remarks</strong>:{{@$remark ?? ''}}</span>
    </td>
</tr>
@endif

@if(isset($piItems) && count($piItems))
<tr>
    <td class="poprod-decpt">
        <span class="poitemtxt mw-100"><strong>Purchase Indent:</strong></span>
        @foreach($piItems as $piItem)
        <span class="badge rounded-pill badge-light-primary text-wrap">{{$piItem?->pi?->book_code}} - {{$piItem?->pi?->document_number ?? ''}} : {{number_format($piItem->po_qty,2)}}</span>
        @endforeach
    </td>
</tr>
@endif
@if(isset($poItem->pi_item_mappings) && $poItem->pi_item_mappings)
<tr>
    @foreach($poItem->pi_item_mappings as $pi_item_mapping)
        @if($pi_item_mapping?->so)
        <td class="poprod-decpt">
            <span class="poitemtxt mw-100"><strong>Sales Order:</strong></span>
            <span class="badge rounded-pill badge-light-primary text-wrap">{{strtoupper($pi_item_mapping?->so?->book_code)}} - {{$pi_item_mapping?->so?->document_number ?? ''}}</span>
        </td>
        @endif
    @endforeach
</tr>
@endif
<tr>
    <td class="poprod-decpt">
        <span class="poitemtxt mw-100"><strong>PO Details:</strong></span>
        <span class="badge rounded-pill badge-light-primary text-wrap">Receipt Qty : </strong> {{number_format($poItem?->grn_qty,2)}}</span>
        <span class="badge rounded-pill badge-light-primary text-wrap">Short Closed Qty : </strong> {{number_format($poItem?->short_close_qty,2)}}</span>
        <span class="badge rounded-pill badge-light-primary text-wrap">Bal Qty : </strong> {{number_format($poItem?->short_bal_qty,2)}}</span>
        @if($poItem?->mrn_details?->count())
            <span class="poitemtxt mw-100"><strong>Receipt Details:</strong></span>
            @foreach($poItem?->mrn_details as $mrn_detail)
                <span class="badge rounded-pill badge-light-primary text-wrap">{{$mrn_detail?->header?->book?->book_code}} - {{$mrn_detail?->header?->document_number}} : {{number_format($mrn_detail?->accepted_qty,2)}}</span>
            @endforeach
        @endif
        
    </td>
</tr>