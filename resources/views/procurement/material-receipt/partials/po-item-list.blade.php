@forelse($poItems as $poDetail)
    @php

        $orderQty = 0.00;
        $invOrderQty = 0.00;
        $geQty = 0.00;
        $grnQty = 0.00;
        $balanceQty = 0.00;
        $moduleType = 'p-order';
        $ref_no = ($poDetail->po?->book?->book_code ?? 'NA') . '-' . ($poDetail->po?->document_number ?? 'NA');
        if($poDetail->gateEntryHeader){
            $orderQty = (($poDetail->gateEntryHeader ? $poDetail?->poItem?->order_qty : 0.00) - ($poDetail->gateEntryHeader ? $poDetail?->poItem?->short_close_qty : 0.00));
            $invOrderQty = 0.00;
            $geQty = (($poDetail->gateEntryHeader ? $poDetail->accepted_qty : 0.00));
            $grnQty = (($poDetail->gateEntryHeader ? $poDetail->mrn_qty : 0.00));
            $balanceQty = ($geQty - $grnQty);
            $moduleType = 'gate-entry';
        } elseif($poDetail->po->supp_invoice_required == 'yes' && isset($poDetail->po->supplierInvoice)){
            $moduleType = 'suppl-inv';
            $orderQty = (($poDetail->order_qty ?? 0.00) - ($poDetail->short_close_qty ?? 0.00));
            $invOrderQty = (($poDetail->po_item?->order_qty ?? 0.00) - ($poDetail->short_close_qty ?? 0.00));
            $geQty = 0.00;
            $grnQty = $poDetail->po_item?->grn_qty ?? 0.00;
            $balanceQty = ($invOrderQty - $grnQty);
        } else{
            $moduleType = 'p-order';
            $orderQty = (($poDetail->order_qty ?? 0.00) - ($poDetail->short_close_qty ?? 0.00));
            $invOrderQty = 0.00;
            $geQty = 0.00;
            $grnQty = $poDetail->grn_qty ?? 0.00;
            $balanceQty = ($orderQty - $grnQty);
        }
    @endphp
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                @if($moduleType == 'gate-entry')
                    <input class="form-check-input po_item_checkbox" type="checkbox" name="po_item_check" value="{{$poDetail->id}}" data-module="{{$moduleType}}"
                    data-current-po="{{ $poDetail ? $poDetail->header_id : 'null' }}" data-existing-po="{{ $poData ? $poData->purchase_order_id : 'null' }}"  @if ($poData && $poData->purchase_order_id !=  $poDetail->purchase_order_id)  disabled="disabled" @endif >
                    <input type="hidden" name="reference_no" id="reference_no" value={{ $ref_no }}>
                @else
                    <input class="form-check-input po_item_checkbox" type="checkbox" name="po_item_check" value="{{$poDetail->id}}" data-module="{{$moduleType}}"
                    data-current-po="{{ $poDetail ? $poDetail->purchase_order_id : 'null' }}" data-existing-po="{{ $poData ? $poData->purchase_order_id : 'null' }}"  @if ($poData && $poData->purchase_order_id !=  $poDetail->purchase_order_id)  disabled="disabled" @endif >
                    <input type="hidden" name="reference_no" id="reference_no" value={{ $ref_no }}>
                @endif
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
        @if($poDetail->po->supp_invoice_required == 'yes' && isset($poDetail->po->supplierInvoice))
            <td class="no-wrap">
                {{ $poDetail->po->supplierInvoice->book->book_code ?? 'NA' }} - {{ $poDetail->po->supplierInvoice->document_number ?? 'NA' }}
            </td>
            <td class="no-wrap">
                {{ $poDetail->po->supplierInvoice?->getFormattedDate('document_date') }}
            </td>
        @else
            <td>-</td>
            <td>-</td>
        @endif

        {{-- Gate Entry Details --}}
        @if($poDetail->gateEntryHeader)
            <td class="no-wrap">
                {{ $poDetail->gateEntryHeader?->book?->book_code ?? 'NA' }} - {{ $poDetail->gateEntryHeader->document_number ?? 'NA' }}
            </td>
            <td class="no-wrap">
                {{ $poDetail->gateEntryHeader?->getFormattedDate('document_date') }}
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
            {{  number_format($grnQty, 2) }}
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
        <td colspan="20" class="text-center">No record found!</td>
    </tr>
@endforelse
