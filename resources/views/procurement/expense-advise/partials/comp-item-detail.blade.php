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
        <td class="poprod-decpt">
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
@if(isset($item))
    @if($item?->itemAttributes->count() > 0)
    <tr>
        <td class="poprod-decpt">
            <span class="mw-100" style="padding: 0%;">
                <strong style="font-size:11px; color : #6a6a6a;">Attributes</strong>
            </span>
            @foreach($item->itemAttributes as $index => $attribute)
                <span class="badge rounded-pill badge-light-primary">
                    <strong data-group-id="{{$attribute?->attributeGroup?->id}}">
                        {{$attribute?->attributeGroup?->name}}
                    </strong>: @foreach ($attribute?->attributes()  as $value)
                                    @if(in_array($value->id ?? 0, $selectedAttr))
                                        {{ $value->value }}
                                    @endif
                                @endforeach
                </span>
            @endforeach
        </td>
    </tr>
    @endif
@endif
<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary">
            <strong>Inv. UOM</strong>:  {{$uomName}}
        </span>
        <span class="badge rounded-pill badge-light-primary">
            <strong>Qty.</strong>: {{$qty}}
        </span>
    </td>
</tr>
@if($purchaseOrder)
    <tr>
        <td class="poprod-decpt">
            <span class="mw-100" style="padding: 0%;">
                <strong style="font-size:11px; color : #6a6a6a;">Purchase Order</strong>
            </span>
            <span class="badge rounded-pill badge-light-primary">
                {{$purchaseOrder->book_code}}-{{$purchaseOrder->document_number}}
            </span>
            <span class="badge rounded-pill badge-light-primary">
                <strong>Date</strong>: {{ $purchaseOrder->getFormattedDate('document_date') }}
            </span>
        </td>
    </tr>
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
@if(isset($remark) && $remark)
    <tr>
        <td class="poprod-decpt">
            <span class="badge rounded-pill badge-light-secondary text-wrap">
                <strong>Remarks</strong>:{{@$remark ?? ''}}
            </span>
        </td>
    </tr>
@endif
