@forelse($piItems as $piItem)
<tr>
    <td>
        <div class="form-check form-check-inline me-0">
            <input class="form-check-input pi_item_checkbox" type="checkbox" name="pi_item_check" value="{{$piItem->id}}">
        </div> 
    </td>   
    @if($piItem->pi)
        <td>{{$piItem->pi?->book?->book_name ?? 'NA'}}</td>
        <td>{{$piItem->pi?->document_number ?? 'NA'}}</td>
        <td>{{$piItem->pi?->document_date ?? 'NA'}}</td>
        <td>{{$piItem->item_code ?? 'NA'}}</td>
        <td>{{$piItem?->item?->item_name}}</td>
        <td>{{$piItem->indent_qty - $piItem->order_qty}}</td>
        <td class="fw-bolder text-dark">{{$piItem->vendor_name ?? 'NA'}}</td>
    @else
        <td>{{$piItem->po?->book?->book_name ?? 'NA'}}</td>
        <td>{{$piItem->po?->document_number ?? 'NA'}}</td>
        <td>{{$piItem->po?->document_date ?? 'NA'}}</td>
        <td>{{$piItem->item_code ?? 'NA'}}</td>
        <td>{{$piItem?->item?->item_name}}</td>
        <td>{{$piItem->order_qty}}</td>
        <td class="fw-bolder text-dark">{{$piItem?->po?->vendor->company_name ?? 'NA'}}</td>
    @endif
</tr>
@empty
<tr>
    <td colspan="8" class="text-center">No record found!</td>
</tr>
@endforelse