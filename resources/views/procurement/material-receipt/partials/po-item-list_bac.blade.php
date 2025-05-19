@forelse($poItems as $poDetail)
        @php
            $orderQty = (($poDetail->order_qty ?? 0) - ($poDetail->short_close_qty ?? 0));
            $invOrderQty = (($poDetail->po_item?->order_qty ?? 0) - ($poDetail->short_close_qty ?? 0));
        @endphp
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                <input class="form-check-input po_item_checkbox" type="checkbox" name="po_item_check" value="{{$poDetail->id}}">
            </div>
        </td>
        <td class="fw-bolder text-dark">
            {{$poDetail?->po?->vendor->company_name ?? 'NA'}}
        </td>
        @if($poDetail?->poItem?->po?->gate_entry_required == '')
            <td>
                {{$poDetail->po_item?->po?->book?->book_name ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po_item?->po?->document_number ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po_item?->po?->document_date ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->book?->book_name ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->document_number ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->document_date ?? 'NA'}}
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                {{$poDetail?->item?->item_name}}[{{$poDetail->item_code ?? 'NA'}}]
            </td>
            <td>
                @foreach($poDetail?->attributes as $index => $attribute)
                    <span class="badge rounded-pill badge-light-primary">
                        <strong data-group-id="{{$attribute->headerAttribute->id}}">
                            {{$attribute->headerAttribute->name}}
                        </strong>:
                        {{ $attribute->headerAttributeValue->value }}
                    </span>
                @endforeach
            </td>
            <td class="text-end">
                {{$invOrderQty}}
            </td>
            <td class="text-end">
                {{$poDetail->order_qty}}
            </td>
            <td class="text-end">
                -
            </td>
            <td class="text-end">
                {{$poDetail->grn_qty}}
            </td>
            <td class="text-end">
                {{ number_format(($invOrderQty ?? 0) - ($poDetail->grn_qty ?? 0), 2) }}
            </td>
            <td class="text-end">
                {{$poDetail->rate}}
            </td>
            <td class="text-end">
                {{ number_format((($invOrderQty - $poDetail->grn_qty)* $poDetail->rate), 2) }}
            </td>
        @elseif(isset($poDetail->po->type) && ($poDetail->po->type == 'supplier-invoice'))
            <td>
                {{$poDetail->po_item?->po?->book?->book_name ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po_item?->po?->document_number ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po_item?->po?->document_date ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->book?->book_name ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->document_number ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->document_date ?? 'NA'}}
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                {{$poDetail?->item?->item_name}}[{{$poDetail->item_code ?? 'NA'}}]
            </td>
            <td>
                @foreach($poDetail?->attributes as $index => $attribute)
                    <span class="badge rounded-pill badge-light-primary">
                        <strong data-group-id="{{$attribute->headerAttribute->id}}">
                            {{$attribute->headerAttribute->name}}
                        </strong>:
                        {{ $attribute->headerAttributeValue->value }}
                    </span>
                @endforeach
            </td>
            <td class="text-end">
                {{$invOrderQty}}
            </td>
            <td class="text-end">
                {{$poDetail->order_qty}}
            </td>
            <td class="text-end">
                -
            </td>
            <td class="text-end">
                {{$poDetail->grn_qty}}
            </td>
            <td class="text-end">
                {{ number_format(($invOrderQty ?? 0) - ($poDetail->grn_qty ?? 0), 2) }}
            </td>
            <td class="text-end">
                {{$poDetail->rate}}
            </td>
            <td class="text-end">
                {{ number_format((($invOrderQty - $poDetail->grn_qty)* $poDetail->rate), 2) }}
            </td>
        @else
            <td>
                {{$poDetail->po?->book?->book_name ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->document_number ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->document_date ?? 'NA'}}
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                {{$poDetail?->item?->item_name}}[{{$poDetail->item_code ?? 'NA'}}]
            </td>
            <td>
                @foreach($poDetail?->attributes as $index => $attribute)
                    <span class="badge rounded-pill badge-light-primary">
                        <strong data-group-id="{{$attribute->headerAttribute->id}}">
                            {{$attribute->headerAttribute->name}}
                        </strong>:
                        {{ $attribute->headerAttributeValue->value }}
                    </span>
                @endforeach
            </td>
            <td class="text-end">
                {{$orderQty}}
            </td>
            <td></td>
            <td></td>
            <td class="text-end">
                {{$poDetail->grn_qty}}
            </td>
            <td class="text-end">
                {{ number_format(($orderQty ?? 0) - ($poDetail->grn_qty ?? 0), 2) }}
            </td>
            <td class="text-end">
                {{$poDetail->rate}}
            </td>
            <td class="text-end">
                {{ number_format((($orderQty - $poDetail->grn_qty)* $poDetail->rate), 2) }}
            </td>
        @endif
    </tr>
@empty
    <tr>
        <td colspan="20" class="text-center">No record found!</td>
    </tr>
@endforelse







@forelse($poItems as $poDetail)
        @php
            $orderQty = (($poDetail->order_qty ?? 0) - ($poDetail->short_close_qty ?? 0));
            $invOrderQty = (($poDetail->po_item?->order_qty ?? 0) - ($poDetail->short_close_qty ?? 0));
        @endphp
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                <input class="form-check-input po_item_checkbox" type="checkbox" name="po_item_check" value="{{$poDetail->id}}">
            </div>
        </td>
        <td class="fw-bolder text-dark">
            {{$poDetail?->po?->vendor->company_name ?? 'NA'}}
        </td>
        @if($poDetail?->poItem?->po?->gate_entry_required == '')
            <td>
                {{$poDetail->po_item?->po?->book?->book_name ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po_item?->po?->document_number ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po_item?->po?->document_date ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->book?->book_name ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->document_number ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->document_date ?? 'NA'}}
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                {{$poDetail?->item?->item_name}}[{{$poDetail->item_code ?? 'NA'}}]
            </td>
            <td>
                @foreach($poDetail?->attributes as $index => $attribute)
                    <span class="badge rounded-pill badge-light-primary">
                        <strong data-group-id="{{$attribute->headerAttribute->id}}">
                            {{$attribute->headerAttribute->name}}
                        </strong>:
                        {{ $attribute->headerAttributeValue->value }}
                    </span>
                @endforeach
            </td>
            <td class="text-end">
                {{$invOrderQty}}
            </td>
            <td class="text-end">
                {{$poDetail->order_qty}}
            </td>
            <td class="text-end">
                -
            </td>
            <td class="text-end">
                {{$poDetail->grn_qty}}
            </td>
            <td class="text-end">
                {{ number_format(($invOrderQty ?? 0) - ($poDetail->grn_qty ?? 0), 2) }}
            </td>
            <td class="text-end">
                {{$poDetail->rate}}
            </td>
            <td class="text-end">
                {{ number_format((($invOrderQty - $poDetail->grn_qty)* $poDetail->rate), 2) }}
            </td>
        @elseif(isset($poDetail->po->type) && ($poDetail->po->type == 'supplier-invoice'))
            <td>
                {{$poDetail->po_item?->po?->book?->book_name ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po_item?->po?->document_number ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po_item?->po?->document_date ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->book?->book_name ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->document_number ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->document_date ?? 'NA'}}
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                {{$poDetail?->item?->item_name}}[{{$poDetail->item_code ?? 'NA'}}]
            </td>
            <td>
                @foreach($poDetail?->attributes as $index => $attribute)
                    <span class="badge rounded-pill badge-light-primary">
                        <strong data-group-id="{{$attribute->headerAttribute->id}}">
                            {{$attribute->headerAttribute->name}}
                        </strong>:
                        {{ $attribute->headerAttributeValue->value }}
                    </span>
                @endforeach
            </td>
            <td class="text-end">
                {{$invOrderQty}}
            </td>
            <td class="text-end">
                {{$poDetail->order_qty}}
            </td>
            <td class="text-end">
                -
            </td>
            <td class="text-end">
                {{$poDetail->grn_qty}}
            </td>
            <td class="text-end">
                {{ number_format(($invOrderQty ?? 0) - ($poDetail->grn_qty ?? 0), 2) }}
            </td>
            <td class="text-end">
                {{$poDetail->rate}}
            </td>
            <td class="text-end">
                {{ number_format((($invOrderQty - $poDetail->grn_qty)* $poDetail->rate), 2) }}
            </td>
        @else
            <td>
                {{$poDetail->po?->book?->book_name ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->document_number ?? 'NA'}}
            </td>
            <td>
                {{$poDetail->po?->document_date ?? 'NA'}}
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                {{$poDetail?->item?->item_name}}[{{$poDetail->item_code ?? 'NA'}}]
            </td>
            <td>
                @foreach($poDetail?->attributes as $index => $attribute)
                    <span class="badge rounded-pill badge-light-primary">
                        <strong data-group-id="{{$attribute->headerAttribute->id}}">
                            {{$attribute->headerAttribute->name}}
                        </strong>:
                        {{ $attribute->headerAttributeValue->value }}
                    </span>
                @endforeach
            </td>
            <td class="text-end">
                {{$orderQty}}
            </td>
            <td></td>
            <td></td>
            <td class="text-end">
                {{$poDetail->grn_qty}}
            </td>
            <td class="text-end">
                {{ number_format(($orderQty ?? 0) - ($poDetail->grn_qty ?? 0), 2) }}
            </td>
            <td class="text-end">
                {{$poDetail->rate}}
            </td>
            <td class="text-end">
                {{ number_format((($orderQty - $poDetail->grn_qty)* $poDetail->rate), 2) }}
            </td>
        @endif
    </tr>
@empty
    <tr>
        <td colspan="20" class="text-center">No record found!</td>
    </tr>
@endforelse
