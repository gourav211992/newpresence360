<tr>
    <td class="p-0">
        <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
    </td>
</tr>
{{-- <tr>
    <td class="poprod-decpt">
        <span class="poitemtxt mw-100"><strong>Name</strong>: {{$item?->item_name ?? 'NA'}}</span>
    </td>
</tr> --}}
<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary"><strong>Category</strong>:  {{$item?->category?->name ?? 'NA'}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>Sub Category</strong>: {{$item?->subCategory?->name ?? 'NA'}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>: {{$item?->hsn?->code}}</span>
    </td>
</tr>
@if(is_array($specifications))
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
@if(isset($item->itemAttributes))
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
        @if(@$poItem?->mrn_details?->count())
            <span class="poitemtxt mw-100"><strong>Receipt Details:</strong></span>
            @foreach($poItem?->mrn_details as $mrn_detail)
                <span class="badge rounded-pill badge-light-primary text-wrap">{{$mrn_detail?->header?->book?->book_code}} - {{$mrn_detail?->header?->document_number}} : {{$mrn_detail?->accepted_qty}}</span>
            @endforeach
        @endif
        {{-- <span class="badge rounded-pill badge-light-primary text-wrap">Close Qty : </strong> {{$poItem?->short_close_qty}}</span>
        <span class="badge rounded-pill badge-light-primary text-wrap">Bal Qty : </strong> {{$poItem?->short_bal_qty}}</span> --}}
    </td>
</tr>
