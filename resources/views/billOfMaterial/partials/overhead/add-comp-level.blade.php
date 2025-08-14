@if(isset($headerOverheads) && count($headerOverheads))
    @php
    $previousLevel = null;
    $rowCounters = [];
    $totalItems = count($headerOverheads);
@endphp

@foreach($headerOverheads as $index => $headerOverhead)
    @php
        $levelCount = $headerOverhead->level;
        $rowCounters[$levelCount] = ($rowCounters[$levelCount] ?? 0) + 1;
        $rowCount = $rowCounters[$levelCount];
    @endphp

    <tr class="display_overhead_row" data-level="{{$levelCount}}" data-index="{{$rowCount}}">
        <td>{{$rowCount}}</td>
        <td>
            <input type="text" id="overhead_input_{{$levelCount}}_{{$rowCount}}" placeholder="Select" name="header[{{$levelCount}}][overhead][{{$rowCount}}][description]" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="{{$headerOverhead->overhead_description}}">
            <input type="hidden" id="overhead_id_{{$levelCount}}_{{$rowCount}}" name="header[{{$levelCount}}][overhead][{{$rowCount}}][overhead_id]" value="{{$headerOverhead->overhead_id}}">
        </td>
        <td>
            <input type="number" id="overhead_input_perc_{{$levelCount}}_{{$rowCount}}" class="form-control mw-100 percentage_input" name="header[{{$levelCount}}][overhead][{{$rowCount}}][perc]" step="any" value="{{ isset($headerOverhead->overhead_perc) && $headerOverhead->overhead_perc != 0 ? $headerOverhead->overhead_perc : '' }}">
        </td>
        <td>
            <input type="number" class="form-control mw-100 {{$headerOverhead->overhead_perc ? 'disabled-input' : ''}}" name="header[{{$levelCount}}][overhead][{{$rowCount}}][amnt]" step="any" value="{{$headerOverhead->overhead_amount}}">
        </td>
        <td>
            <input type="text" readonly class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="{{$headerOverhead?->ledger_name}}" name="header[{{$levelCount}}][overhead][{{$rowCount}}][ledger_name]">
            <input type="hidden" name="header[{{$levelCount}}][overhead][{{$rowCount}}][ledger_id]">
        </td>
        <td>
            <input type="text" readonly class="form-control mw-100" name="header[{{$levelCount}}][overhead][{{$rowCount}}][ledger_group_name]">
            <input type="hidden" name="header[{{$levelCount}}][overhead][{{$rowCount}}][ledger_group_id]">
        </td>
        <td class="text-center">
            <a href="javascript:;" class="text-danger deleteOverheadRow" data-id="{{$headerOverhead->id}}">
                <i data-feather="trash-2"></i>
            </a>
            @if(($headerOverheads[$index + 1]->level ?? null) !== $levelCount)
            <a href="javascript:;" class="text-primary addOverheadRow">
                <i data-feather="plus-square"></i>
            </a>
            @endif
            <input type="hidden" id="overhead_id_{{$levelCount}}_{{$rowCount}}" name="header[{{$levelCount}}][overhead][{{$rowCount}}][id]" value="{{$headerOverhead->id}}">
        </td>
    </tr>

    @php
        $nextLevel = $headerOverheads[$index + 1]->level ?? null;
    @endphp

    @if($nextLevel !== $levelCount)
        <tr class="sub_total_row" id="sub_total_row_{{$levelCount}}">
            <td colspan="2"></td>
            <td class="text-dark"><strong>Sub Total</strong></td>
            <td class="text-dark text-end"><strong id="total">{{ number_format(0, 2) }}</strong></td>
            <td colspan="2"></td>
            <td class="text-center">
                <a href="javascript:;" class="text-primary addOverheadLevel @if (!$loop->last) d-none @endif">
                    <i data-feather="plus-square"></i>
                </a>
            </td>
        </tr>
    @endif
@endforeach

@else
    @php
        $rowCount = 1;
        $levelCount = 1;
    @endphp
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
        <td class="text-center"><a href="#" class="text-primary addOverheadLevel"><i data-feather="plus-square"></i></a></td>
    </tr>
@endif