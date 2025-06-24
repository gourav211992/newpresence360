@forelse($joItems as $joItem)
    @php
        $orderQty = (($joItem->order_qty ?? 0) - ($joItem->short_close_qty ?? 0));
        $invOrderQty = (($joItem->jo_item?->order_qty ?? 0) - ($joItem->short_close_qty ?? 0));
        if (isset($joItem->jo->type) && ($joItem->jo->type == 'supplier-invoice')) {
            $ref_no = ($joItem->jo_item?->jo?->book?->book_code ?? 'NA') . '-' . ($joItem->jo_item?->jo?->document_number ?? 'NA');
        } else {
            $ref_no = ($joItem->jo?->book?->book_code ?? 'NA') . '-' . ($joItem->jo?->document_number ?? 'NA');
        }
    @endphp
    <tr>
        <td>
            <div class="form-check form-check-inline me-0">
                <input class="form-check-input jo_item_checkbox" type="checkbox" name="jo_item_check" value="{{$joItem->id}}" data-current-jo="{{ $joItem ? $joItem->purchase_order_id : 'null' }}" data-existing-jo="{{ $joData ? $joData->purchase_order_id : 'null' }}"  @if ($joData && $joData->purchase_order_id !=  $joItem->purchase_order_id)  disabled="disabled" @endif>
                <input type="hidden" name="reference_no" id="reference_no" value={{ $ref_no }}>
            </div>
        </td>
        <!-- <td class="fw-bolder text-dark">
            {{$joItem?->jo?->vendor_code ?? 'NA'}} {{$joItem?->jo?->type ?? 'NA'}}
        </td> -->
        <td class="fw-bolder text-dark no-wrap">
            {{$joItem?->jo?->vendor->company_name ?? 'NA'}}
        </td>
        @if(isset($joItem->jo->type) && ($joItem->jo->type == 'supplier-invoice'))
            <td class="no-wrap">
                {{$joItem->jo_item?->jo?->book?->book_code ?? 'NA'}} - {{$joItem->jo_item?->jo?->document_number ?? 'NA'}}
            </td>
            <td class="no-wrap">
                {{ $joItem->jo_item->jo?->getFormattedDate('document_date') }}
            </td>
            <td class="no-wrap">
                {{$joItem->jo?->book?->book_code ?? 'NA'}} - {{$joItem->jo?->document_number ?? 'NA'}}
            </td>
            <td class="no-wrap">
                {{ $joItem->jo?->getFormattedDate('document_date') }}
            </td>
            <td class="no-wrap">
                {{$joItem?->item?->item_name}}[{{$joItem->item_code ?? 'NA'}}]
            </td>
            <td class="no-wrap">
                @foreach($joItem?->attributes as $index => $attribute)
                    <span class="badge rounded-pill badge-light-primary">
                        <strong data-group-id="{{$attribute->headerAttribute->id}}">
                            {{$attribute->headerAttribute->name}}
                        </strong>:
                        {{ $attribute->headerAttributeValue->value }}
                    </span>
                @endforeach
            </td>
            <td class="text-end">
                {{number_format($invOrderQty, 2)}}
            </td>
            <td class="text-end">
                {{number_format($joItem->order_qty, 2)}}
            </td>
            <td class="text-end">
                {{number_format($joItem->ge_qty, 2)}}
            </td>
            <td class="text-end">
                {{ number_format(($invOrderQty ?? 0) - ($joItem->ge_qty ?? 0), 2) }}
            </td>
            <td class="text-end">
            {{number_format($joItem->rate, 2)}}
            </td>
            <td class="text-end">
                {{ number_format((($invOrderQty - $joItem->ge_qty)* $joItem->rate), 2) }}
            </td>
        @else
            <td class="no-wrap">
                {{$joItem->jo?->book?->book_code ?? 'NA'}} - {{$joItem->jo?->document_number ?? 'NA'}}
            </td>
            <td class="no-wrap">
                {{ $joItem->jo?->getFormattedDate('document_date') }}
            </td>
            <td></td>
            <td></td>
            <td class="no-wrap">
                {{$joItem?->item?->item_name}}[{{$joItem->item_code ?? 'NA'}}]
            </td>
            <td class="no-wrap">
                @foreach($joItem?->attributes as $index => $attribute)
                    <span class="badge rounded-pill badge-light-primary">
                        <strong data-group-id="{{$attribute?->headerAttribute?->id}}">
                            {{$attribute?->headerAttribute?->name}}
                        </strong>:
                        {{ $attribute?->headerAttributeValue?->value }}
                    </span>
                @endforeach
            </td>
            <td class="text-end">
                {{number_format($orderQty, 2)}}
            </td>
            <td></td>
            <td class="text-end">
                {{number_format($joItem->ge_qty, 2)}}
            </td>
            <td class="text-end">
                {{ number_format(($orderQty ?? 0) - ($joItem->ge_qty ?? 0), 2) }}
            </td>
            <td class="text-end">
                {{number_format($joItem->rate, 2)}}
            </td>
            <td class="text-end">
                {{ number_format((($orderQty - $joItem->ge_qty)* $joItem->rate), 2) }}
            </td>
        @endif
    </tr>
@empty
    <tr>
        <td colspan="16" class="text-center">No record found!</td>
    </tr>
@endforelse
