@forelse($joItems as $joDetail)
    @php

        $orderQty = 0.00;
        $invOrderQty = 0.00;
        $geQty = 0.00;
        $grnQty = 0.00;
        $balanceQty = 0.00;
        $moduleType = 'j-order';
        $ref_no = ($joDetail->jo?->book?->book_code ?? 'NA') . '-' . ($joDetail->jo?->document_number ?? 'NA');
        if($joDetail->gateEntryHeader){
            $orderQty = (($joDetail->gateEntryHeader ? $joDetail?->joItem?->order_qty : 0.00) - ($joDetail->gateEntryHeader ? $joDetail?->joItem?->short_close_qty : 0.00));
            $invOrderQty = 0.00;
            $geQty = (($joDetail->gateEntryHeader ? $joDetail->accepted_qty : 0.00));
            $grnQty = (($joDetail->gateEntryHeader ? $joDetail->mrn_qty : 0.00));
            $balanceQty = ($geQty - $grnQty);
            $moduleType = 'gate-entry';
        } elseif($joDetail->jo->supp_invoice_required == 'yes' && isset($joDetail->jo->supplierInvoice)){
            $moduleType = 'suppl-inv';
            $orderQty = (($joDetail->order_qty ?? 0.00) - ($joDetail->short_close_qty ?? 0.00));
            $invOrderQty = (($joDetail->jo_item?->order_qty ?? 0.00) - ($joDetail->short_close_qty ?? 0.00));
            $geQty = 0.00;
            $grnQty = $joDetail->jo_item?->grn_qty ?? 0.00;
            $balanceQty = ($invOrderQty - $grnQty);
        } else{
            $moduleType = 'j-order';
            $orderQty = (($joDetail->order_qty ?? 0.00) - ($joDetail->short_close_qty ?? 0.00));
            $invOrderQty = 0.00;
            $geQty = 0.00;
            $grnQty = $joDetail->grn_qty ?? 0.00;
            $balanceQty = ($orderQty - $grnQty);
        }
    @endphp
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                @if($moduleType == 'gate-entry')
                    <input class="form-check-input po_item_checkbox" type="checkbox" name="po_item_check" value="{{$joDetail->id}}" data-module="{{$moduleType}}"
                    data-current-po="{{ $joDetail ? $joDetail->header_id : 'null' }}" data-existing-jo="{{ $joData ? $joData->purchase_order_id : 'null' }}"  @if ($joData && $joData->purchase_order_id !=  $joDetail->purchase_order_id)  disabled="disabled" @endif >
                    <input type="hidden" name="reference_no" id="reference_no" value={{ $ref_no }}>
                @else
                    <input class="form-check-input po_item_checkbox" type="checkbox" name="po_item_check" value="{{$joDetail->id}}" data-module="{{$moduleType}}"
                    data-current-po="{{ $joDetail ? $joDetail->purchase_order_id : 'null' }}" data-existing-jo="{{ $joData ? $joData->purchase_order_id : 'null' }}"  @if ($joData && $joData->purchase_order_id !=  $joDetail->purchase_order_id)  disabled="disabled" @endif >
                    <input type="hidden" name="reference_no" id="reference_no" value={{ $ref_no }}>
                @endif
            </div>
        </td>
        <td class="fw-bolder text-dark no-wrap">
            {{ $joDetail->jo?->vendor->company_name ?? 'NA' }}
            <input type="hidden" name="module-type" id="module-type" value="{{ $moduleType }}">
        </td>
        <td class="no-wrap">
            {{ $joDetail->jo?->book?->book_code ?? 'NA' }} - {{ $joDetail->jo?->document_number ?? 'NA' }}
        </td>
        <td class="no-wrap">
            {{ $joDetail->jo?->getFormattedDate('document_date') }}
        </td>

        {{-- Supplier Invoice Details --}}
        @if($joDetail->jo->supp_invoice_required == 'yes' && isset($joDetail->jo->supplierInvoice))
            <td class="no-wrap">
                {{ $joDetail->jo->supplierInvoice->book->book_code ?? 'NA' }} - {{ $joDetail->jo->supplierInvoice->document_number ?? 'NA' }}
            </td>
            <td class="no-wrap">
                {{ $joDetail->jo->supplierInvoice?->getFormattedDate('document_date') }}
            </td>
        @else
            <td>-</td>
            <td>-</td>
        @endif

        {{-- Gate Entry Details --}}
        @if($joDetail->gateEntryHeader)
            <td class="no-wrap">
                {{ $joDetail->gateEntryHeader?->book?->book_code ?? 'NA' }} - {{ $joDetail->gateEntryHeader->document_number ?? 'NA' }}
            </td>
            <td class="no-wrap">
                {{ $joDetail->gateEntryHeader?->getFormattedDate('document_date') }}
            </td>
        @else
            <td>-</td>
            <td>-</td>
        @endif

        <td class="no-wrap">
            {{ $joDetail?->item?->item_name }} [{{ $joDetail->item_code ?? 'NA' }}]
        </td>
        <td class="no-wrap">
            @foreach($joDetail?->attributes as $attribute)
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
            {{ number_format($joDetail->rate, 2) }}
        </td>
        <td class="text-end">
            {{ number_format(($balanceQty * $joDetail->rate), 2) }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="20" class="text-center">No record found!</td>
    </tr>
@endforelse
