@forelse ($grn_details as $grn)
    @isset($grn->header->vendor->company_name)
    <tr class="@if((isset($selected_grn_id) && $selected_grn_id == $grn->id) ) table-active @endif">
        <td>
            <div class="form-check form-check-inline me-0">
           
                <input 
                    class="form-check-input" 
                    type="radio" 
                    name="grn_id" 
                    id="grn_{{ $loop->index }}" 
                    value="{{ $grn->id }}" 
                    data-grn="{{ json_encode($grn) }}"
                    @if((isset($selected_grn_id) && $selected_grn_id == $grn->id) ) checked @endif
                >
            </div>
        </td>
        <td>{{ $grn->header->document_number }}</td>
        <td>{{ $grn->header->created_at->format('d-m-Y') }}</td>
        <td class="fw-bolder text-dark">{{ $grn->header->vendor_code }}</td>
        <td>{{ $grn->header->vendor->company_name }}</td>
        <td>{{ $grn->item->item_name }}</td>
        <td>{{ $grn->accepted_qty }}</td>
    </tr>
    @endisset
@empty
<tr>
    <td colspan="7" class="text-center">No data available</td>
</tr>
@endforelse
