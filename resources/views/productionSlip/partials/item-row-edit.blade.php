@use(App\Helpers\ConstantHelper)
@if (isset($slip))
    @foreach ($slip -> items as $slipItemIndex => $slipItem)
        @php
            $docType = $slip->document_type;
            // $hasInspection = $slipItem?->item?->is_inspection;
            $inspectionChecklistData = $slipItem->item->loadInspectionChecklists();

            $existingCheckList = array_map(function ($item) {
                return [
                    'insp_checklist_id' => $item['id'],
                    'checkList_id' => $item['checklist_id'],
                    'checkList_name' => $item['checklist_name'],
                    'detail_id' => $item['detail_id'],
                    'parameter_name' => $item['name'],
                    'parameter_value' => $item['value'],
                    'result' => $item['result'] ?? ''
                ];
            }, $slipItem->checklists->toArray());
            // dd($existingCheckList);
        @endphp

        <tr id = "item_row_{{$slipItemIndex}}" class = "item_header_rows" onclick = "onItemClick('{{$slipItemIndex}}');" data-detail-id = "{{$slipItem -> id}}" data-id = "{{$slipItem -> id}}">
        <input type = 'hidden' name = "pslip_item_id[]" value = "{{$slipItem->id}}">
            <td class="customernewsection-form">
            <div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_{{$slipItemIndex}}" del-index = "{{$slipItemIndex}}">
                <label class="form-check-label" for="item_checkbox_{{$slipItemIndex}}"></label>
            </div>
        </td>
        <td class="poprod-decpt">
            <input type="text" id="so_doc_{{$slipItemIndex}}" name="so_doc[{{$slipItemIndex}}]" class="form-control mw-100"  value="{{$slipItem?->so?->document_number}}" readonly>
        </td>
        <td>
            <input type="text" id = "customers_dropdown_{{$slipItemIndex}}" name="customer_code[{{$slipItemIndex}}]" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input {{$slipItem -> is_editable ? '' : 'restrict'}}" autocomplete="off"  value = "{{$slipItem -> customer ?-> company_name}}" {{$slipItem -> is_editable ? '' : 'readonly'}}>
            <input type = "hidden" name = "customer_id[{{$slipItemIndex}}]" id = "customers_dropdown_{{$slipItemIndex}}_value" value = "{{$slipItem -> customer_id}}"></input>
        </td>
        <td class="poprod-decpt">
            <input type="text" id = "items_dropdown_{{$slipItemIndex}}" name="item_code[{{$slipItemIndex}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input {{$slipItem -> is_editable ? '' : 'restrict'}}" autocomplete="off" data-name="{{$slipItem -> item ?-> item_name}}" data-code="{{$slipItem -> item ?-> item_code}}" data-id="{{$slipItem -> item ?-> id}}" hsn_code = "{{$slipItem -> item ?-> hsn ?-> code}}" item-name = "{{$slipItem -> item ?-> item_name}}" specs = "{{$slipItem -> item ?-> specifications}}" attribute-array = "{{$slipItem -> item_attributes_array()}}"  value = "{{$slipItem -> item ?-> item_code}}" {{$slipItem -> is_editable ? '' : 'readonly'}} item-location = "[]">
            <input type = "hidden" name = "item_id[]" id = "items_dropdown_{{$slipItemIndex}}_value" value = "{{$slipItem -> item_id}}"></input>
        </td>
        <td class="poprod-decpt">
            <input type="text" id = "items_name_{{$slipItemIndex}}" class="form-control mw-100"   value = "{{$slipItem -> item ?-> item_name}}" name = "item_name[{{$slipItemIndex}}]" readonly>
        </td>
        <td class="poprod-decpt" id="attribute_section_{{$slipItemIndex}}">
            <button id = "attribute_button_{{$slipItemIndex}}" {{count($slipItem -> item_attributes_array()) > 0 ? '' : 'disabled'}} type = "button" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
            <input type = "hidden" name = "attribute_value_{{$slipItemIndex}}" />
            </td>
        <td>
            <select class="form-select" name = "uom_id[]" id = "uom_dropdown_{{$slipItemIndex}}">
            </select>
        </td>
        <td>
            <input type="text" id = "item_so_qty_{{$slipItemIndex}}" value = "{{$slipItem ?->so_item?->order_qty}}" name = "item_so_qty[{{$slipItemIndex}}]" class="form-control mw-100 text-end"/>
        </td>
        <td>
            <input type="text" id = "item_qty_{{$slipItemIndex}}"  value = "{{$slipItem->qty}}" oninput = "changeItemQty(this, {{$slipItemIndex}});" onblur = "setFormattedNumericValue(this);" name = "item_qty[{{$slipItemIndex}}]" class="form-control mw-100 text-end" />
        </td>
        <td>
            <input type="text" id = "item_accepted_qty_{{$slipItemIndex}}"  value = "{{$slipItem->accepted_qty}}" oninput = "changeItemQty(this, {{$slipItemIndex}});" onblur = "setFormattedNumericValue(this);" name = "item_accepted_qty[{{$slipItemIndex}}]" class="form-control mw-100 text-end approver-can-edit" />
        </td>
        {{-- @if($slipItem?->mo_product?->mo?->is_last_station) --}}
        <td>
            <input type="text" id = "item_sub_prime_qty_{{$slipItemIndex}}"  value = "{{$slipItem->subprime_qty}}" oninput = "changeItemQty(this, {{$slipItemIndex}});" onblur = "setFormattedNumericValue(this);" name = "item_sub_prime_qty[{{$slipItemIndex}}]" class="form-control mw-100 text-end approver-can-edit" />
        </td>
        {{-- @endif --}}
        <td>
            <input type="text" id = "item_rejected_qty_{{$slipItemIndex}}"  value = "{{$slipItem->rejected_qty}}" name = "item_rejected_qty[{{$slipItemIndex}}]" class="form-control mw-100 text-end disabled-input" />
        </td>
        @if($isWipQty)
        <td><input type="text" id="item_wip_qty_{{$slipItemIndex}}" value = "{{$slipItem->wip_qty}}" name = "item_wip_qty[{{$slipItemIndex}}]" oninput = "changeItemQty(this, {{$slipItemIndex}});" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" /></td>
        <td><input type="text" id="item_total_qty_{{$slipItemIndex}}" value = "{{($slipItem->wip_qty + $slipItem->qty)}}" name = "item_total_qty[{{$slipItemIndex}}]" oninput = "changeItemQty(this, {{$slipItemIndex}});" class="form-control mw-100 text-end disabled-input" onblur = "setFormattedNumericValue(this);" /></td>
        @endif
        {{-- @if(in_array($slip->document_status ?? [], ConstantHelper::DOCUMENT_STATUS_APPROVED))
        <td><input type="text" id = "item_rate_{{$slipItemIndex}}" value = "{{number_format($slipItem -> rate,4)}}" name = "item_rate[{{$slipItemIndex}}]" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);"/></td>
        <td><input type="text" id = "item_item_value_{{$slipItemIndex}}" value = "{{number_format($slipItem -> item_value,2)}}" name = "item_item_value[{{$slipItemIndex}}]" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);"/></td>
        @endif --}}
        @if($machines->isNotEmpty())
        <td>
            <input type="hidden" name="machine_id[{{ $slipItemIndex }}]" value="">
            <select class="form-select select2"  multiple name="machine_id[{{$slipItemIndex}}][]" data-index="{{ $slipItemIndex }}" multiple>
                <option value="">Select Machine</option>
                @foreach ($machines as $machine)
                    <option value="{{$machine->id}}" {{ in_array($machine->id, $slipItem->machine_id ?? []) ? 'selected' : '' }}>{{$machine?->name}}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select class="form-select" name="cycle_count[{{$slipItemIndex}}]">
                <option value="">Select Cycle Count</option>
                @for ($i = 1; $i <= 10; $i++)
                    <option value="{{$i}}" {{$i == $slipItem->cycle_count ? 'selected' : ''}}>{{$i}}</option>
                @endfor
            </select>
        </td>
        @endif

        @if($stationLines->isNotEmpty())
        <td>
            <select class="form-select" name="station_line_id[{{$slipItemIndex}}]">
                @foreach ($stationLines as $stationLine)
                    <option value="{{$stationLine?->id}}" {{$stationLine?->id == $slipItem->station_line_id ? 'selected' : ''}} data-name="{{$stationLine?->supervisor_name}}">{{$stationLine?->name}}</option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" id="supervisor_name_{{$slipItemIndex}}" value="{{$slipItem?->supervisor_name}}" name="supervisor_name[{{$slipItemIndex}}]" class="form-control mw-100" />
        </td>
        @endif

        <td>
            <div class="d-flex">
                {{-- @if ($hasInspection ==1) --}}
                <input type="hidden" id="inspection_data_{{$slipItemIndex}}" name="inspection_data[{{$slipItemIndex}}]" value='@json($existingCheckList)'/>
                <div class="cursor-pointer me-50 text-success inspectionChecklistBtn"
                    data-row-count="{{ $slipItemIndex }}"
                    data-checklist='@json(["is_inspection" => 1, "checkLists" => @$inspectionChecklistData])'
                    data-bs-toggle="modal"
                    data-bs-target="#inspectionChecklistModal"
                    title="Inspection Checklist">
                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Inspection" class="text-success"><i data-feather="check-circle"></i></span>
                </div>
                {{-- @endif --}}
                <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_{{$slipItemIndex}}');">
                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
                <div class="me-50 cursor-pointer item_bundles" onclick = "renderBundleDetails({{$slipItemIndex}}, true)" id = "item_bundles_{{$slipItemIndex}}">
                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Bundle/Packets" class="text-warning"><i data-feather="package"></i></span>
                </div>
            <input type = "hidden" id = "item_remarks_{{$slipItemIndex}}" name = "item_remarks[{{$slipItemIndex}}]" />
        </td>
        <input type="hidden" id="mo_product_id_{{$slipItemIndex}}" name = "mo_product_id[{{$slipItemIndex}}]"  value="{{$slipItem?->mo_product_id}}">
        <input type="hidden" id="mo_id_{{$slipItemIndex}}" name="mo_id[{{$slipItemIndex}}]"  value="{{$slipItem?->mo?->id}}">
        <input type="hidden" id="so_id_{{$slipItemIndex}}" name="so_id[{{$slipItemIndex}}]"  value="{{$slipItem?->so_id}}">
        <input type="hidden" id="so_item_id_{{$slipItemIndex}}" name="so_item_id[{{$slipItemIndex}}]"  value="{{$slipItem?->so_item_id}}">
        <input type="hidden" id="station_id_{{$slipItemIndex}}" name = "station_id[{{$slipItemIndex}}]"  value = "{{$slipItem?->station_id}}">
        </tr>
    @endforeach
@endif
