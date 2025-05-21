@forelse($mrnItems as $mrnItem)
    @php
        $mrn_qty = ($mrnItem->order_qty ?? 0.00);
    @endphp
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                <input class="form-check-input po_item_checkbox" type="checkbox" name="po_item_check" value="{{$mrnItem->id}}" data-current-po="{{ $mrnItem ? $mrnItem->mrn_header_id : 'null' }}" data-existing-mrn="{{ $mrnData ? $mrnData->mrn_header_id : 'null' }}"  @if ($mrnData && $mrnData->mrn_header_id !=  $mrnItem->mrn_header_id)  disabled="disabled" @endif>
            </div>
        </td>
        <td class="fw-bolder text-dark no-wrap">
            {{$mrnItem?->mrnHeader?->vendor_code ?? 'NA'}} {{$mrnItem?->mrnHeader?->type ?? 'NA'}}
        </td>
        <td class="fw-bolder text-dark no-wrap">
            {{$mrnItem?->mrnHeader?->vendor->company_name ?? 'NA'}}
        </td>
        <td class="no-wrap">
            {{$mrnItem->mrnHeader?->book?->book_code ?? 'NA'}}
        </td>
        <td class="no-wrap">
            {{$mrnItem->mrnHeader?->document_number ?? 'NA'}}
        </td>
        <td class="no-wrap">
            {{-- {{$mrnItem->mrnHeader?->document_date ?? 'NA'}} --}}
            {{ $mrnItem->mrnHeader->getFormattedDate('document_date') }}
        </td>
        <td class="no-wrap">
            {{$mrnItem->item_code ?? 'NA'}}
        </td>
        <td class="no-wrap">
            {{$mrnItem?->item?->item_name}}
        </td>
        <td class="text-end">
            {{number_format($mrn_qty, 2)}}
        </td>
        <td class="text-end">
            {{number_format(($mrnItem->inspection_qty ?? 0), 2)}}
        </td>
        <td class="text-end">
            {{ number_format(($mrn_qty ?? 0) - ($mrnItem->inspection_qty ?? 0), 2) }}
        </td>
        <td class="text-end">
            {{number_format($mrnItem->rate, 2)}}
        </td>
        <td class="text-end">
            {{ number_format(((($mrn_qty ?? 0) - ($mrnItem->inspection_qty ?? 0)) * $mrnItem->rate), 2) }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="13" class="text-center">No record found!</td>
    </tr>
@endforelse
