<tr>
    <td style="padding-left: {{ $level * 20 }}px;">
        {{ $rowNumber }}
    </td>
    <td>{{ $bomItem->station_name }}</td>
    <td>{{ $bomItem->section_name }}</td>
    <td>
        <b>{{ $bomItem->item->item_name }}</b><br>
        {{ $bomItem->item_code }}
    </td>
    <td>{{ $bomItem->item->uom->name ?? '' }}</td>
    <td style="text-align:right;">{{ number_format($bomItem->qty, 4) }}</td>
    <td style="text-align:right;">{{ number_format($bomItem->item_cost, 4) }}</td>
    <td style="text-align:right;">{{ number_format($bomItem->qty * $bomItem->item_cost, 2) }}</td>
</tr>
