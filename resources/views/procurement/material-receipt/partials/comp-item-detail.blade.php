<tr>
    <td class="p-0">
        <h6 class="text-dark mb-0 bg-light-primary py-1 px-50">
            <strong>Item Details</strong>
        </h6>
    </td>
</tr>
<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary">
            <strong>Category</strong>:  {{$item?->category?->name ?? 'NA'}}
        </span>
        <span class="badge rounded-pill badge-light-primary">
            <strong>Sub Category</strong>: {{$item?->subCategory?->name ?? 'NA'}}
        </span>
        <span class="badge rounded-pill badge-light-primary">
            <strong>HSN</strong>: {{$item?->hsn?->code}}
        </span>
    </td>
</tr>
@if($specifications->count())
    <tr class="item_detail_row">
        <td class="poprod-decpt item_detail_attributes">
            <span class="mw-100" style="padding: 0%;">
                <strong>Specifications:</strong>
            </span>
            @foreach($specifications as $specification)
                <span class="badge rounded-pill badge-light-primary">
                    <strong data-group-id="">
                        {{$specification->specification_name ?? ''}}
                    </strong>: {{$specification->value ?? ''}}
                </span>
            @endforeach
        </td>
    </tr>
@endif
    @if($item->itemAttributes->count())
    <tr class="item_detail_row2">
        <td class="poprod-decpt item_detail_attributes">
            <span class="poitemtxt mw-100"><strong>Attributes:</strong></span>
            @foreach($item->itemAttributes as $index => $attribute)
                <span class="badge rounded-pill badge-light-primary"><strong data-group-id="{{$attribute->attributeGroup->id}}"> {{$attribute->attributeGroup->name}}</strong>: @foreach ($attribute->attributes() as $value)
                    @if(in_array($value->id, $selectedAttr))
                        {{ $value->value }}
                    @endif
                 @endforeach </span>
            @endforeach
        </td>
    </tr>
@endif
<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary"><strong>Inv. UOM</strong>:  {{$uomName}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>Qty.</strong>: {{$qty}}</span>
    </td>
</tr>
@if($totalStockData)
<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary">
            <strong>Confirmed Stock</strong>: {{ @$totalStockData['confirmedStockAltUom'] }}
        </span>

        <span class="badge rounded-pill badge-light-primary">
            <strong>Pending Stock</strong>:
            <form action="{{ url('/inventory-reports/get-stock-ledger-reports') }}" method="GET" target="_blank" style="display: inline;">
                <input type="hidden" name="item" value="{{ $itemId ?? '' }}"/>
                <input type="hidden" name="store_id" value="{{ $storeId ?? '' }}"/>
                <input type="hidden" name="sub_store_id" value="{{ $subStoreId ?? '' }}"/>
                <input type="hidden" name="type_of_stock_id" value="unconfirmed_stock"/>
                @foreach($attributes['attribute_name'] ?? [] as $attrName)
                    <input type="hidden" name="attribute_name[]" value="{{ $attrName }}"/>
                @endforeach

                @foreach($attributes['attribute_value'] ?? [] as $attrValue)
                    <input type="hidden" name="attribute_value[]" value="{{ $attrValue }}"/>
                @endforeach
                <button type="submit" style="border: none; background-color: transparent; color: #002bff; padding: 0; cursor: pointer;">
                    {{ @$totalStockData['pendingStockAltUom'] }}
                </button>
            </form>
        </span>
    </td>
</tr>
@endif
@if($purchaseOrder)
    <tr>
        <td class="poprod-decpt">
            <span class="mw-100" style="padding: 0%;">
                <strong style="font-size:11px; color : #6a6a6a;">
                    {{
                        ($type == 'po') ? 'Purchase' :
                        (($type == 'jo') ? 'Job' :
                        (($type == 'so') ? 'Sale' : ''))
                    }}
                    Order
                </strong>
            </span>
            <span class="badge rounded-pill badge-light-primary">
                {{$purchaseOrder->book_code}}-{{$purchaseOrder->document_number}}
            </span>
            <span class="badge rounded-pill badge-light-primary">
                <strong>Date</strong>:
                {{ $purchaseOrder?->getFormattedDate('document_date') }}
            </span>
            <span class="badge rounded-pill badge-light-primary">
                <strong>Order Qty.</strong>: {{number_format($poDetail->order_qty, 2)}}
            </span>
            @if($poDetail->grn_qty && ($poDetail->grn_qty > 0))
                <span class="badge rounded-pill badge-light-primary">
                    <strong>Received Qty.</strong>: {{number_format($poDetail->grn_qty, 2)}}
                </span>
            @endif
            @if($poDetail->grn_qty && ($poDetail->short_close_qty > 0))
                <span class="badge rounded-pill badge-light-primary">
                    <strong>Cloased Qty.</strong>: {{number_format($poDetail->short_close_qty, 2)}}
                </span>
            @endif
            <span class="badge rounded-pill badge-light-primary">
                <strong>Balance Qty.</strong>: {{number_format(((($poDetail->order_qty ?? 0.00) - ($poDetail->short_close_qty ?? 0.00)) - (($poDetail->grn_qty ?? 0.00))), 2)}}
            </span>
        </td>
    </tr>
    @if($gateEntry)
        <tr>
            <td class="poprod-decpt">
                <span class="mw-100" style="padding: 0%;">
                    <strong style="font-size:11px; color : #6a6a6a;">Gate Entry</strong>
                </span>
                <span class="badge rounded-pill badge-light-primary">
                    {{$gateEntry->book_code}}-{{$gateEntry->document_number}}
                </span>
                <span class="badge rounded-pill badge-light-primary">
                    <strong>Date</strong>:
                    {{ $gateEntry?->getFormattedDate('document_date') }}
                </span>
                <span class="badge rounded-pill badge-light-primary">
                    <strong>Qty.</strong>: {{number_format($poDetail->ge_qty, 2)}}
                </span>
            </td>
        </tr>
    @endif
@endif
@if($poDetail && $poDetail->so)
    <tr>
        <td class="poprod-decpt">
            <span class="mw-100" style="padding: 0%;">
                <strong style="font-size:11px; color : #6a6a6a;">Sales Order </strong>
            </span>
            <span class="badge rounded-pill badge-light-primary">
                {{$poDetail?->so?->book_code}} - {{$poDetail?->so?->document_number}}
            </span>
            <span class="badge rounded-pill badge-light-primary">
                <strong>Date</strong>: {{ $poDetail?->so?->getFormattedDate('document_date') }}
            </span>
        </td>
    </tr>
@endif
<!-- @if(isset($storagePoints['data']) && !empty($storagePoints['data']))
    <tr>
        <td class="poprod-decpt">
            <span class="mw-100" style="padding: 0%;">
                <strong style="font-size:11px; color : #6a6a6a;">Storage Points</strong>
            </span>
        </td>
    </tr>
    @foreach($storagePoints['data'] as $index => $value)
        <tr>
            <td class="poprod-decpt">
                <span class="badge rounded-pill badge-light-primary">
                    <strong>
                        Name
                    </strong>: {{ucFirst($value->name)}}
                </span>
                <span class="badge rounded-pill badge-light-primary">
                    <strong>
                        Available Weight
                    </strong>: {{(($value->max_weight ?? 0.00) - ($value->current_weight ?? 0.00))}}
                </span>
                <span class="badge rounded-pill badge-light-primary">
                    <strong>
                        Available Volume
                    </strong>: {{(($value->max_volume ?? 0.00) - ($value->current_volume ?? 0.00))}}
                </span>
            </td>
        </tr>
    @endforeach
@endif -->
@if(isset($remark) && $remark)
    <tr>
        <td class="poprod-decpt">
            <span class="badge rounded-pill badge-light-secondary text-wrap"><strong>Remarks</strong>:{{@$remark ?? ''}}</span>
        </td>
    </tr>
@endif
@if($mrn && ($mrn->reference_type == 'jo'))
    <tr>
        <td class="poprod-decpt">
            <span class="badge rounded-pill badge-light-primary text-wrap"><strong>Item Cost</strong>:{{number_format(($totalCost ?? 0.00), 2)}}</span>
        </td>
    </tr>
@endif

