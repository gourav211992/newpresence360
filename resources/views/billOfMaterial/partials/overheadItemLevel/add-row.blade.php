<tr class="item_display_overhead_row">
    <td>{{$indexCount}}</td>
    <td>
        <input type="text" id="item_overhead_input_{{$rowCount}}_{{$indexCount}}" placeholder="Select" name="components[{{$rowCount}}][overhead][{{$indexCount}}][description]" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
        <input type="hidden" id="item_overhead_id_{{$rowCount}}_{{$indexCount}}" name="components[{{$rowCount}}][overhead][{{$indexCount}}][overhead_id]">
    </td>
    <td><input type="number" id="item_overhead_input_perc_{{$rowCount}}_{{$indexCount}}" class="form-control mw-100 percentage_input" name="components[{{$rowCount}}][overhead][{{$indexCount}}][perc]" step="any"></td>
    <td><input type="number" class="form-control mw-100" name="components[{{$rowCount}}][overhead][{{$indexCount}}][amnt]" step="any"></td>
    <td>
        <input type="text" readonly class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="" name="components[{{$rowCount}}][overhead][{{$indexCount}}][ledger_name]">
        <input type="hidden" name="components[{{$rowCount}}][overhead][{{$indexCount}}][ledger_id]">
    </td>
    <td>
        <input type="text" readonly class="form-control mw-100" name="components[{{$rowCount}}][overhead][{{$indexCount}}][ledger_group_name]">
        <input type="hidden" name="components[{{$rowCount}}][overhead][{{$indexCount}}][ledger_group_id]">
    </td>
    <td class="text-center">
        <a href="javascript:;" class="text-danger deleteOverheadItemRow" data-id="">
            <i data-feather="trash-2"></i>
        </a>
        <a href="javascript:;" class="text-primary addOverheadItemRow">
            <i data-feather="plus-square"></i>
        </a>
    </td>
</tr>
@if($indexCount == 1)
<tr class="item_sub_total_row" id="item_sub_total_row_{{$rowCount}}">
    <td colspan="2"></td>
    <td class="text-dark"><strong>Sub Total</strong></td>
    <td class="text-dark text-end"><strong id="total">{{number_format(0,2)}}</strong></td>
    <td colspan="2"></td>
    <td class="text-center"></td>
</tr>
@endif