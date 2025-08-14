@forelse($poItems as $poDetail)
    @php

        $orderQty = 0.00;
        $invOrderQty = 0.00;
        $geQty = 0.00;
        $balanceQty = 0.00;
        $moduleType = 'p-order';
        if($poDetail->po->supp_invoice_required == 'yes'){
            $moduleType = 'suppl-inv';
            $ref_no = ($poItem->vendorAsn?->book_code ?? 'NA') . '-' . ($poItem->vendorAsn?->document_number ?? 'NA');
            $invOrderQty = (($poDetail->balance_qty ?? 0.00) - ($poDetail->short_close_qty ?? 0.00));
            $orderQty = (($poDetail->po_item?->order_qty ?? 0.00) - ($poDetail->short_close_qty ?? 0.00));
            $geQty = ($poDetail->ge_qty ?? 0.00);
            $balanceQty = ($invOrderQty - $geQty);
        } else{
            $moduleType = 'p-order';
            $ref_no = ($poDetail->po?->book?->book_code ?? 'NA') . '-' . ($poDetail->po?->document_number ?? 'NA');
            $orderQty = (($poDetail->order_qty ?? 0.00) - ($poDetail->short_close_qty ?? 0.00));
            $invOrderQty = 0.00;
            $geQty = ($poDetail->ge_qty ?? 0.00);
            $balanceQty = ($orderQty - $geQty);
        }
    @endphp
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                @if($moduleType == 'suppl-inv')
                    <input class="form-check-input po_item_checkbox" type="checkbox" name="po_item_check" value="{{$poDetail->id}}" data-module="{{$moduleType}}"
                    data-current-po="{{ $poDetail ? $poDetail->vendor_asn_id : 'null' }}" data-existing-po="{{ $poData ? $poData->purchase_order_id : 'null' }}"  @if ($poData && $poData->purchase_order_id !=  $poDetail->purchase_order_id)  disabled="disabled" @endif >
                @else
                    <input class="form-check-input po_item_checkbox" type="checkbox" name="po_item_check" value="{{$poDetail->id}}" data-module="{{$moduleType}}"
                    data-current-po="{{ $poDetail ? $poDetail->purchase_order_id : 'null' }}" data-existing-po="{{ $poData ? $poData->purchase_order_id : 'null' }}"  @if ($poData && $poData->purchase_order_id !=  $poDetail->purchase_order_id)  disabled="disabled" @endif >
                @endif
                <input type="hidden" name="reference_no" id="reference_no" value={{ $ref_no }}>
            </div>
        </td>
        <td class="fw-bolder text-dark no-wrap">
            {{ $poDetail->po?->vendor->company_name ?? 'NA' }}
            <input type="hidden" name="module-type" id="module-type" value="{{ $moduleType }}">
        </td>
        <td class="no-wrap">
            {{ $poDetail->po?->book?->book_code ?? 'NA' }} - {{ $poDetail->po?->document_number ?? 'NA' }}
        </td>
        <td class="no-wrap">
            {{ $poDetail->po?->getFormattedDate('document_date') }}
        </td>

        {{-- Supplier Invoice Details --}}
        @if($poDetail->po->supp_invoice_required == 'yes')
            <td class="no-wrap">
                {{ $poDetail->vendorAsn->book_code ?? 'NA' }} - {{ $poDetail->vendorAsn->document_number ?? 'NA' }}
            </td>
            <td class="no-wrap">
                {{ $poDetail->vendorAsn?->getFormattedDate('document_date') }}
            </td>
        @else
            <td>-</td>
            <td>-</td>
        @endif

        <td class="no-wrap">
            {{ $poDetail?->item?->item_name }} [{{ $poDetail->item_code ?? 'NA' }}]
        </td>
        <td class="no-wrap">
            @foreach($poDetail?->attributes as $attribute)
                <span class="badge rounded-pill badge-light-primary">
                    <strong>{{ $attribute->headerAttribute->name }}</strong>: {{ $attribute->headerAttributeValue->value }}
                </span>
            @endforeach
        </td>
        <td class="text-end">
            {{ number_format($orderQty, 2) }}
        </td>
        <td class="text-end">
            {{ number_format($invOrderQty, 2) }}
        </td>
        <td class="text-end">
            {{ number_format($geQty, 2) }}
        </td>
        <td class="text-end">
            {{ number_format($balanceQty, 2) }}
        </td>
        <td class="text-end">
            {{ number_format($poDetail->rate, 2) }}
        </td>
        <td class="text-end">
            {{ number_format(($balanceQty * $poDetail->rate), 2) }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="14" class="text-center">No record found!</td>
    </tr>
@endforelse
