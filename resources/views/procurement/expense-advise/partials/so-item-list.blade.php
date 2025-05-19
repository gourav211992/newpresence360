@forelse($soItems as $soItem)
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                <input class="form-check-input so_item_checkbox" type="checkbox" name="so_item_check" value="{{$soItem->id}}">
            </div>
        </td>
        <td class="fw-bolder text-dark">
            {{$soItem->so?->customer_code ?? 'NA'}}
        </td>
        <td class="fw-bolder text-dark">
            {{$soItem->so?->customer->display_name ?? 'NA'}}
        </td>
        <td>
            {{$soItem->so?->book?->book_code ?? 'NA'}} - {{$soItem->so?->document_number ?? 'NA'}}
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
        <td class="text-end">
            {{number_format($soItem->order_qty, 2)}}
        </td>
        <td class="text-end">
            {{number_format($soItem->expense_advise_qty, 2)}}
        </td>
        <td class="text-end">
            {{ number_format(($soItem->order_qty ?? 0) - ($soItem->expense_advise_qty ?? 0), 2) }}
        </td>
        <td class="text-end">
            {{number_format($soItem->rate, 2)}}
        </td>
        <td class="text-end">
        {{ number_format(($soItem->order_qty * $soItem->rate), 2) }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="13" class="text-center">No record found!</td>
    </tr>
@endforelse
