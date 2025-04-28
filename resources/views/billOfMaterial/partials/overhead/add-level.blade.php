<tr class="display_overhead_row" data-level="{{$levelCount}}" data-index="{{$rowCount}}">
    <td>{{$rowCount}}</td>
    <td>
        <input type="text" id="overhead_input_{{$levelCount}}_{{$rowCount}}" placeholder="Select" name="header[{{$levelCount}}][overhead][{{$rowCount}}][description]" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
        <input type="hidden" id="overhead_id_{{$levelCount}}_{{$rowCount}}" name="header[{{$levelCount}}][overhead][{{$rowCount}}][overhead_id]">
    </td>
    <td><input type="number" id="overhead_input_perc_{{$levelCount}}_{{$rowCount}}" class="form-control mw-100 percentage_input" name="header[{{$levelCount}}][overhead][{{$rowCount}}][perc]" step="any"></td>
    <td><input type="number" class="form-control mw-100" name="header[{{$levelCount}}][overhead][{{$rowCount}}][amnt]" step="any"></td>
    <td>
        <input type="text" readonly class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="" name="header[{{$levelCount}}][overhead][{{$rowCount}}][ledger_name]">
        <input type="hidden" name="header[{{$levelCount}}][overhead][{{$rowCount}}][ledger_id]">
    </td>
    <td>
        <input type="text" readonly class="form-control mw-100" name="header[{{$levelCount}}][overhead][{{$rowCount}}][ledger_group_name]">
        <input type="hidden" name="header[{{$levelCount}}][overhead][{{$rowCount}}][ledger_group_id]">
    </td>
    <td class="text-center">
        <a href="javascript:;" class="text-danger deleteOverheadRow" data-id="">
            <i data-feather="trash-2"></i>
          </a>
        <a href="javascript:;" class="text-primary addOverheadRow">
            <i data-feather="plus-square"></i>
        </a>
    </td>
</tr>
<tr class="sub_total_row" id="sub_total_row_{{$levelCount}}">
    <td colspan="2"></td>
    <td class="text-dark"><strong>Sub Total</strong></td>
    <td class="text-dark text-end"><strong id="total">{{number_format(0,2)}}</strong></td>
    <td colspan="2"></td>
    <td class="text-center">
        <a href="javascript:;" class="text-primary addOverheadLevel"><i data-feather="plus-square"></i></a>
    </td>
</tr>