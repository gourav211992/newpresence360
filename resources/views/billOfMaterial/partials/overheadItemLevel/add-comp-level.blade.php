@if(isset($headerOverheads) && count($headerOverheads))
@foreach($headerOverheads as $index => $headerOverhead)
    <tr class="item_display_overhead_row">
        <td>{{$rowCount}}</td>
        <td>
            <input type="text" id="item_overhead_input_{{$rowCount}}_{{$rowCount}}" placeholder="Select" name="components[{{$rowCount}}][overhead][{{$rowCount}}][description]" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="{{$headerOverhead->overhead_description}}">
            <input type="hidden" id="item_overhead_id_{{$rowCount}}_{{$rowCount}}" name="components[{{$rowCount}}][overhead][{{$rowCount}}][overhead_id]" value="{{$headerOverhead->overhead_id}}">
        </td>
        <td>
            <input type="number" id="item_overhead_input_perc_{{$rowCount}}_{{$rowCount}}" class="form-control mw-100 percentage_input" name="components[{{$rowCount}}][overhead][{{$rowCount}}][perc]" step="any" value="{{ isset($headerOverhead->overhead_perc) && $headerOverhead->overhead_perc != 0 ? $headerOverhead->overhead_perc : '' }}">
        </td>
        <td>
            <input type="number" class="form-control mw-100 {{$headerOverhead->overhead_perc ? 'disabled-input' : ''}}" name="components[{{$rowCount}}][overhead][{{$rowCount}}][amnt]" step="any" value="{{$headerOverhead->overhead_amount}}">
        </td>
        <td>
            <input type="text" readonly class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="{{$headerOverhead?->ledger_name}}" name="components[{{$rowCount}}][overhead][{{$rowCount}}][ledger_name]">
            <input type="hidden" name="components[{{$rowCount}}][overhead][{{$rowCount}}][ledger_id]">
        </td>
        <td>
            <input type="text" readonly class="form-control mw-100" name="components[{{$rowCount}}][overhead][{{$rowCount}}][ledger_group_name]">
            <input type="hidden" name="components[{{$rowCount}}][overhead][{{$rowCount}}][ledger_group_id]">
        </td>
        <td class="text-center">
            <a href="javascript:;" class="text-danger deleteOverheadRow" data-id="{{$headerOverhead->id}}">
                <i data-feather="trash-2"></i>
            </a>
            <a href="javascript:;" class="text-primary addOverheadItemRow">
                <i data-feather="plus-square"></i>
            </a>
            <input type="hidden" id="item_overhead_id_{{$rowCount}}_{{$rowCount}}" name="components[{{$rowCount}}][overhead][{{$rowCount}}][id]" value="{{$headerOverhead->id}}">
        </td>
    </tr>
    @endforeach
    <tr class="item_sub_total_row" id="item_sub_total_row_{{$rowCount}}">
        <td colspan="2"></td>
        <td class="text-dark"><strong>Sub Total</strong></td>
        <td class="text-dark text-end"><strong id="total">{{ number_format(0, 2) }}</strong></td>
        <td colspan="2"></td>
        <td class="text-center">
        </td>
    </tr>

@else
    @php
        $rowCount = 1;
        $rowCount = 1;
    @endphp
    <tr class="item_display_overhead_row" data-index="{{$rowCount}}">
        <td>{{$rowCount}}</td>
        <td>
            <input type="text" id="item_overhead_input_{{$rowCount}}_{{$rowCount}}" placeholder="Select" name="components[{{$rowCount}}][overhead][{{$rowCount}}][description]" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
            <input type="hidden" id="item_overhead_id_{{$rowCount}}_{{$rowCount}}" name="components[{{$rowCount}}][overhead][{{$rowCount}}][overhead_id]">
        </td>
        <td><input type="number" id="item_overhead_input_perc_{{$rowCount}}_{{$rowCount}}" class="form-control mw-100 percentage_input" name="components[{{$rowCount}}][overhead][{{$rowCount}}][perc]" step="any"></td>
        <td><input type="number" class="form-control mw-100" name="components[{{$rowCount}}][overhead][{{$rowCount}}][amnt]" step="any"></td>
        <td>
            <input type="text" readonly class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="" name="components[{{$rowCount}}][overhead][{{$rowCount}}][ledger_name]">
            <input type="hidden" name="components[{{$rowCount}}][overhead][{{$rowCount}}][ledger_id]">
        </td>
        <td>
            <input type="text" readonly class="form-control mw-100" name="components[{{$rowCount}}][overhead][{{$rowCount}}][ledger_group_name]">
            <input type="hidden" name="components[{{$rowCount}}][overhead][{{$rowCount}}][ledger_group_id]">
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
    <tr class="item_sub_total_row" id="item_sub_total_row_{{$rowCount}}">
        <td colspan="2"></td>
        <td class="text-dark"><strong>Sub Total</strong></td>
        <td class="text-dark text-end"><strong id="total">{{number_format(0,2)}}</strong></td>
        <td colspan="2"></td>
        <td class="text-center"></td>
    </tr>
@endif