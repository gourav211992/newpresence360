@forelse($poItems as $poItem)
        @php
            if (isset($poItem->po->supp_invoice_required) && ($poItem->po->supp_invoice_required == 'yes')) {
                $ref_no = ($poItem->po_item?->po?->book?->book_code ?? 'NA') . '-' . ($poItem->po_item?->po?->document_number ?? 'NA');
            } else {
                $ref_no = ($poItem->po?->book?->book_code ?? 'NA') . '-' . ($poItem->po?->document_number ?? 'NA');
            }
        @endphp
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                <input class="form-check-input po_item_checkbox" type="checkbox" name="po_item_check" value="{{$poItem->id}}" data-current-po="{{ $poItem ? $poItem->purchase_order_id : 'null' }}" data-existing-po="{{ $poData ? $poData->purchase_order_id : 'null' }}"  @if ($poData && $poData->purchase_order_id !=  $poItem->purchase_order_id)  disabled="disabled" @endif>
                <input type="hidden" name="reference_no" id="reference_no" value={{ $ref_no }}>
            </div>
        </td>
        <!-- <td class="fw-bolder text-dark">
            {{$poItem?->po?->vendor_code ?? 'NA'}} {{$poItem?->po?->type ?? 'NA'}}
        </td> -->
        <td class="fw-bolder text-dark no-wrap">
            {{$poItem?->po?->vendor->company_name ?? 'NA'}}
        </td>
        @if(isset($poItem->po->supp_invoice_required) && ($poItem->po->supp_invoice_required == 'yes'))
            <td class="no-wrap">
                {{$poItem->po?->book?->book_code ?? 'NA'}} - {{$poItem->po?->document_number ?? 'NA'}}
            </td>
            <td class="no-wrap">
                {{ $poItem->po?->getFormattedDate('document_date') }}
            </td>
            <td class="no-wrap">
                {{$poItem->vendorAsn?->book_code ?? 'NA'}} - {{$poItem->vendorAsn?->document_number ?? 'NA'}}
            </td>
            <td class="no-wrap">
                {{ $poItem->vendorAsn?->getFormattedDate('document_date') }}
            </td>
            <td class="no-wrap">
                {{$poItem?->item?->item_name}}[{{$poItem->item_code ?? 'NA'}}]
            </td>
            <td class="no-wrap">
                @foreach($poItem?->attributes as $index => $attribute)
                    <span class="badge rounded-pill badge-light-primary">
                        <strong data-group-id="{{$attribute->headerAttribute->id}}">
                            {{$attribute->headerAttribute->name}}
                        </strong>:
                        {{ $attribute->headerAttributeValue->value }}
                    </span>
                @endforeach
            </td>
            <td class="text-end">
                {{number_format($poItem->order_qty, 2)}}
            </td>
            <td class="text-end">
                {{number_format($poItem->balance_qty, 2)}}
            </td>
            <td class="text-end">
                {{number_format($poItem->ge_qty, 2)}}
            </td>
            <td class="text-end">
                {{ number_format(($poItem->balance_qty ?? 0) - ($poItem->ge_qty ?? 0), 2) }}
            </td>
            <td class="text-end">
            {{number_format($poItem->rate, 2)}}
            </td>
            <td class="text-end">
                {{ number_format((($poItem->balance_qty - $poItem->ge_qty)* $poItem->rate), 2) }}
            </td>
        @else
            <td class="no-wrap">
                {{$poItem->po?->book?->book_code ?? 'NA'}} - {{$poItem->po?->document_number ?? 'NA'}}
            </td>
            <td class="no-wrap">
                {{ $poItem->po?->getFormattedDate('document_date') }}
            </td>
            <td></td>
            <td></td>
            <td class="no-wrap">
                {{$poItem?->item?->item_name}}[{{$poItem->item_code ?? 'NA'}}]
            </td>
            <td class="no-wrap">
                @foreach($poItem?->attributes as $index => $attribute)
                    <span class="badge rounded-pill badge-light-primary">
                        <strong data-group-id="{{$attribute?->headerAttribute?->id}}">
                            {{$attribute?->headerAttribute?->name}}
                        </strong>:
                        {{ $attribute?->headerAttributeValue?->value }}
                    </span>
                @endforeach
            </td>
            <td class="text-end">
                {{number_format($poItem->order_qty, 2)}}
            </td>
            <td></td>
            <td class="text-end">
                {{number_format($poItem->ge_qty, 2)}}
            </td>
            <td class="text-end">
                {{ number_format(($poItem->order_qty ?? 0) - ($poItem->ge_qty ?? 0), 2) }}
            </td>
            <td class="text-end">
                {{number_format($poItem->rate, 2)}}
            </td>
            <td class="text-end">
                {{ number_format((($poItem->order_qty - $poItem->ge_qty)* $poItem->rate), 2) }}
            </td>
        @endif
    </tr>
@empty
    <tr>
        <td colspan="16" class="text-center">No record found!</td>
    </tr>
@endforelse
