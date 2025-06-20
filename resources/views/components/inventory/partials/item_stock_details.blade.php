@foreach($processedItems as $itemIndex => $item)
<tr>
    <td>
        {{ $item['item_code'] }}
        <input type = "hidden" id = "stock_item_id_{{$itemIndex}}" value = "{{$item['item_id']}}"></input>
    </td>
    <td>{{ $item['item_name'] }}</td>
    <td>
        {!! $item['attributes_ui'] !!}
        <input type = "hidden" id = "stock_attributes_{{$itemIndex}}" value = "{{json_encode($item['selected_attributes'])}}"></input>
    </td>
    <td>{{ $item['uom_name'] }}</td>
    <td>
    <input type="text" id="stock_org_name_input_{{$itemIndex}}" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input stock_org_name_input" index = "{{$itemIndex}}" autocomplete="off" value="{{ $item['organization_name'] }}">
    <input type = "hidden" class = "stock_org_name_id" id = "stock_org_name_input_id_{{$itemIndex}}" value = "{{ $item['organization_id'] }}"></input>
    </td>
    <td>
    <input type="text" id="stock_location_name_input_{{$itemIndex}}" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input stock_location_name_input" index = "{{$itemIndex}}" autocomplete="off" value="{{ $item['location_name'] }}">
    <input type = "hidden" class = "stock_location_name_id" id = "stock_location_name_input_id_{{$itemIndex}}" value = "{{ $item['location_id'] }}"></input>
    </td>
    <td>
    <input type="text" id="stock_sub_store_name_input_{{$itemIndex}}" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input stock_sub_store_name_input" index = "{{$itemIndex}}" autocomplete="off" value="{{ $item['sub_store_name'] }}">
    <input type = "hidden" class = "stock_sub_store_name_id" id = "stock_sub_store_name_input_id_{{$itemIndex}}" value = "{{ $item['sub_store_id'] }}"></input>
    </td>
    <td id = "stock_confirmed_qty_{{$itemIndex}}" style = "{{$item['confirmed_stocks'] > 0 ? '' : 'color:red;'}}">{{ $item['confirmed_stocks'] }}</td>
    <td id = "stock_unconfirmed_qty_{{$itemIndex}}" style = "{{$item['unconfirmed_stocks'] > 0 ? '' : 'color:red;'}}">{{ $item['unconfirmed_stocks'] }}</td>
</tr>
@endforeach
