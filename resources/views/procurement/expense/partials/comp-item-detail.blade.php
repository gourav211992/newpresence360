<tr>
    <td class="p-0">
        <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
    </td>
</tr>
<tr>
    <td class="poprod-decpt">
        <span class="poitemtxt mw-100"><strong>Name</strong>: {{$item->item_name ?? 'NA'}}</span>
    </td> 
</tr>
@if($purchaseOrder)
    <tr>
        <td class="poprod-decpt">
            <span class="badge rounded-pill badge-light-primary">
                <strong>PO No.</strong>: {{$purchaseOrder->document_number}}
            </span>
            <span class="badge rounded-pill badge-light-primary">
                <strong>PO Date.</strong>: {{date('Y-m-d', strtotime($purchaseOrder->document_date))}}
            </span>
        </td>
    </tr>
@endif
@if($saleOrder)
    <tr>
        <td class="poprod-decpt">
            <span class="badge rounded-pill badge-light-primary">
                <strong>SO No.</strong>: {{$saleOrder->document_number}}
            </span>
            <span class="badge rounded-pill badge-light-primary">
                <strong>SO Date.</strong>: {{date('Y-m-d', strtotime($saleOrder->document_date))}}
            </span>
        </td>
    </tr>
@endif
<tr> 
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary">
            <strong>HSN</strong>: {{$item->hsn->code}}
        </span>
        @foreach($item->itemAttributes as $index => $attribute)
            <span class="badge rounded-pill badge-light-primary">
                <strong data-group-id="{{$attribute->attributeGroup->id}}">
                    {{$attribute->attributeGroup->name}}
                </strong>: 
                @foreach ($attribute->attributes() as $value) 
                    @if(in_array($value->id, $selectedAttr))
                        {{ $value->value }}
                    @endif
                @endforeach
            </span>
        @endforeach
    </td> 
</tr> 
<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary"><strong>Inv. UOM</strong>: {{$uomName}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>Qty.</strong>: {{$qty}}</span>
        <!-- <span class="badge rounded-pill badge-light-primary"><strong>Exp. Date</strong>:  </span>  -->
    </td>
</tr>
<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>:{{$item->remark ?: $remark}}</span>
    </td>
</tr>