@forelse($poItems as $poItem)
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                <input class="form-check-input po_item_checkbox" type="checkbox" name="po_item_check" value="{{$poItem->id}}">
            </div>
        </td>
        <!-- <td class="fw-bolder text-dark">
            {{$poItem?->po?->vendor_code ?? 'NA'}} {{$poItem?->po?->type ?? 'NA'}}
        </td> -->
        <td class="fw-bolder text-dark">
            {{$poItem?->po?->vendor->company_name ?? 'NA'}}
        </td>
        @if(isset($poItem->po->type) && ($poItem->po->type == 'supplier-invoice'))
            <td>
                {{$poItem->po_item?->po?->book?->book_code ?? 'NA'}} - {{$poItem->po_item?->po?->document_number ?? 'NA'}}
            </td>
            <td>
                {{$poItem->po_item?->po?->document_date ?? 'NA'}} - {{$poItem->po?->book?->book_code ?? 'NA'}}
            </td>
            <td>
                {{$poItem->po?->document_number ?? 'NA'}}
            </td>
            <td>
                {{$poItem->po?->document_date ?? 'NA'}}
            </td>
            <td>
                {{$poItem?->item?->item_name}}[{{$poItem->item_code ?? 'NA'}}]
            </td>
            <td>
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
                {{$poItem->po_item?->order_qty}}
            </td>
            <td class="text-end">
                {{$poItem->order_qty}}
            </td>
            <td class="text-end">
                {{$poItem->expense_advise_qty}}
            </td>
            <td class="text-end">
                {{ number_format(($poItem->order_qty ?? 0) - ($poItem->expense_advise_qty ?? 0), 2) }}
            </td>
            <td class="text-end">
                {{$poItem->rate}}
            </td>
            <td class="text-end">
                {{ number_format((($poItem->order_qty - $poItem->expense_advise_qty)* $poItem->rate), 2) }}
            </td>
        @else
            <td>
                {{$poItem->po?->book?->book_code ?? 'NA'}} - {{$poItem->po?->document_number ?? 'NA'}}
            </td>
            <td>
                {{$poItem->po?->document_date ?? 'NA'}}
            </td>
            <td></td>
            <td></td>
            <td>
                {{$poItem?->item?->item_name}}[{{$poItem->item_code ?? 'NA'}}]
            </td>
            <td>
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
                {{$poItem->order_qty}}
            </td>
            <td></td>
            <td class="text-end">
                {{$poItem->grn_qty}}
            </td>
            <td class="text-end">
                {{ number_format(($poItem->order_qty ?? 0) - ($poItem->expense_advise_qty ?? 0), 2) }}
            </td>
            <td class="text-end">
                {{$poItem->rate}}
            </td>
            <td class="text-end">
                {{ number_format((($poItem->order_qty - $poItem->expense_advise_qty)* $poItem->rate), 2) }}
            </td>
        @endif
    </tr>
@empty
    <tr>
        <td colspan="16" class="text-center">No record found!</td>
    </tr>
@endforelse
