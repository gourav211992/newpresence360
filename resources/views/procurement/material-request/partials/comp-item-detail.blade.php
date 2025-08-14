<tr>
    <td class="p-0">
        <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
    </td>
</tr>
{{-- <tr>
    <td class="poprod-decpt">
        <span class="poitemtxt mw-100"><strong>Name</strong>: {{$item->item_name ?? 'NA'}}</span>
    </td> 
</tr> --}}
<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary"><strong>Category</strong>:  {{$item->category->name ?? 'NA'}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>Sub Category</strong>: {{$item->subCategory->name ?? 'NA'}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>: {{$item->hsn->code}}</span>
        {{-- <span class="badge rounded-pill badge-light-primary"><strong>Exp. Date</strong>:  </span>  --}}
    </td>
</tr>
@if($specifications->count())
<tr class="item_detail_row">
    <td class="poprod-decpt item_detail_attributes">
        <span class="poitemtxt mw-100"><strong>Specifications:</strong></span>
        @foreach($specifications as $specification)
            <span class="badge rounded-pill badge-light-primary"><strong data-group-id="">{{$specification->specification_name}}</strong>: {{$specification->value}}</span>
        @endforeach
    </td>
</tr>
@endif

@if($item->itemAttributes->count() > 0)
<tr> 
    <td class="poprod-decpt">
        <span class="poitemtxt mw-100"><strong>Attributes:</strong></span>
        @foreach($item->itemAttributes as $index => $attribute)
        <span class="badge rounded-pill badge-light-primary"><strong data-group-id="{{$attribute->attributeGroup->id}}">{{$attribute->attributeGroup->name}}</strong>: @foreach ($attribute->attributes() as $value) 
                @if(in_array($value->id, $selectedAttr))
                    {{ $value->value }}
                @endif
             @endforeach</span>
        @endforeach
    </td> 
</tr> 
@endif

<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary"><strong>Inv. UOM</strong>:  {{$uomName}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>Qty.</strong>: {{$qty}}</span>
        {{-- <span class="badge rounded-pill badge-light-primary"><strong>Exp. Date</strong>:  </span>  --}}
    </td>
</tr>
@if(isset($delivery) && $delivery)
<tr>
    <td class="poprod-decpt">
        <span class="poitemtxt mw-100"><strong>Delivery Schedule</strong>:</span>
        @foreach($delivery as $d)
        <span class="badge rounded-pill badge-light-secondary"><strong>{{ \Carbon\Carbon::parse(@$d['dDate'])->format('d-m-y') }}</strong> : {{@$d['dQty']}}</span>
        @endforeach
    </td>
</tr>
@endif
@if(isset($remark) && $remark)
<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>:{{@$remark ?? ''}}</span>
    </td>
</tr>
@endif