@foreach($processedItems as $itemIndex => $item)
<tr>
    <td>
        {{ $item['item_code'] }}
        <input class = "stock_items" type = "hidden" id = "stock_item_id_{{$itemIndex}}" value = "{{$item['item_id']}}"></input>
    </td>
    <td>{{ $item['item_name'] }}</td>
    <td>
        {!! $item['attributes_ui'] !!}
        <input class = "stock_attributes" type = "hidden" id = "stock_attributes_{{$itemIndex}}" value = "{{json_encode($item['selected_attributes'])}}"></input>
    </td>
    <td>
        {{ $item['uom_name'] }}
    </td>
    <td>
    {{ $item['organization_name'] }}
    </td>
    <td>
    {{ $item['location_name'] }}
    </td>
    <td>
    {{ $item['sub_store_name'] }}
    </td>
    <td class = "numeric-alignment" id = "stock_confirmed_qty_{{$itemIndex}}" style = "{{$item['confirmed_stocks'] > 0 ? '' : 'color:red;'}}">{{ $item['confirmed_stocks'] }}</td>
    <td class = "numeric-alignment" id = "stock_unconfirmed_qty_{{$itemIndex}}" style = "{{$item['unconfirmed_stocks'] > 0 ? '' : 'color:red;'}}">{{ $item['unconfirmed_stocks'] }}</td>
</tr>
@endforeach
