@forelse($soItems as $soItem)
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                <input class="form-check-input po_item_checkbox" type="checkbox" name="po_item_check" value="{{$soItem->id}}">
            </div>
        </td>
        <td class="fw-bolder text-dark">
            {{$soItem->so?->customer_code ?? 'NA'}} {{$soItem->so?->type ?? 'NA'}}
        </td>
        <td class="fw-bolder text-dark">
            {{$soItem->so?->customer->display_name ?? 'NA'}}
        </td>

        <td>
            {{$soItem->so?->book?->book_name ?? 'NA'}}
        </td>
        <td>
            {{$soItem->so?->document_number ?? 'NA'}}
        </td>
        <td>
            {{$soItem->so?->document_date ?? 'NA'}}
        </td>
        <td>
            {{$soItem->item_code ?? 'NA'}}
        </td>
        <td>
            {{$soItem?->item?->item_name}}
        </td>
        <td>
            {{$soItem->order_qty}}
        </td>
        <td>
            {{ number_format(($soItem->order_qty ?? 0) - ($soItem->grn_qty ?? 0), 2) }}
        </td>
        <td>
            {{$soItem->rate}}
        </td>
        <td>
            {{ number_format(($soItem->order_qty * $soItem->rate), 2) }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="12" class="text-center">No record found!</td>
    </tr>
@endforelse
