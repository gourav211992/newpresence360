@forelse($mrnItems as $mrnItem)
    @php
        if($qtyTypeRequired && ($qtyTypeRequired == 'rejected')){
            $availableQty =  $mrnItem->available_qty;
        } else{
            $availableQty =  \App\Helpers\ItemHelper::convertToAltUom($mrnItem->item_id, $mrnItem->uom_id, $mrnItem->available_qty ?? 0);
        }
    @endphp
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                <input class="form-check-input po_item_checkbox" type="checkbox" name="po_item_check" value="{{$mrnItem->id}}">
            </div>
        </td>
        <td class="fw-bolder text-dark no-wrap">
            {{$mrnItem?->mrnHeader?->vendor_code ?? 'NA'}}
        </td>
        <td class="fw-bolder text-dark no-wrap">
            {{$mrnItem?->mrnHeader?->vendor->company_name ?? 'NA'}}
        </td>
        <td class="no-wrap">
            {{$mrnItem->mrnHeader?->book?->book_code ?? 'NA'}} - {{$mrnItem->mrnHeader?->document_number ?? 'NA'}}
        </td>
        <td class="no-wrap">
            {{-- {{$mrnItem->mrnHeader?->document_date ?? 'NA'}} --}}
            {{ $mrnItem->mrnHeader->getFormattedDate('document_date') }}
        </td>
        <td class="no-wrap">
            {{$mrnItem->mrnHeader?->lot_number ?? 'NA'}}
        </td>
        <td class="no-wrap">
            {{$mrnItem->item_code ?? 'NA'}}
        </td>
        <td class="no-wrap">
            {{$mrnItem?->item?->item_name}}
        </td>
        <td class="text-end">
            {{number_format($mrnItem->accepted_qty, 2)}}
        </td>
        <td class="text-end">
            {{number_format($mrnItem->rejected_qty, 2)}}
        </td>
        @if($qtyTypeRequired && ($qtyTypeRequired == 'rejected'))
            <td class="text-end">
                {{number_format($mrnItem->pr_rejected_qty, 2)}}
            </td>
            <td class="text-end">
                {{ number_format(($availableQty ?? 0) - ($mrnItem->pr_rejected_qty ?? 0), 2) }}
            </td>
        @else
            <td class="text-end">
                {{number_format($mrnItem->pr_qty, 2)}}
            </td>
            <td class="text-end">
                {{ number_format(($availableQty ?? 0), 2) }}
            </td>
        @endif
        <td class="text-end">
            {{number_format($mrnItem->rate, 2)}}
        </td>
        @if($qtyTypeRequired && ($qtyTypeRequired == 'rejected'))
            <td class="text-end">
                {{ number_format((($availableQty ?? 0) - ($mrnItem->pr_rejected_qty ?? 0))*($mrnItem->rate ?? 0), 2) }}
            </td>
        @else
            <td class="text-end">
                {{ number_format((($availableQty ?? 0))*($mrnItem->rate ?? 0), 2) }}
            </td>
        @endif
    </tr>
@empty
    <tr>
        <td colspan="15" class="text-center">No record found!</td>
    </tr>
@endforelse
