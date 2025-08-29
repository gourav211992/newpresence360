<tr>
    <td class="p-0">
        <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
    </td>
</tr>
<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary"><strong>Category</strong>:  {{$item->category->name ?? 'NA'}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>Sub Category</strong>: {{$item->subCategory->name ?? 'NA'}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>: {{$item->hsn->code}}</span>
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
    </td>
</tr>
@if(isset($inventoryStock) && count($inventoryStock))
<tr>
    <td class="poprod-decpt">
        <span class="badge rounded-pill badge-light-primary"><strong>Confirmed Stock</strong>:  {{$inventoryStock['confirmedStocks'] ? $inventoryStock['confirmedStocks'] : 0}}</span>
        <span class="badge rounded-pill badge-light-primary"><strong>Unconfirmed Stock</strong>
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
                    :  {{$inventoryStock['pendingStocks'] ? $inventoryStock['pendingStocks'] : 0}}
                </button>
            </form>
        </span>
    </td>
</tr>
@endif