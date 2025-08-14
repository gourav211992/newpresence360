@foreach($poItems as $key => $item)
    @php
        $rowCount = $tableRowCount + $key + 1;
        $balanceQty = (($item->accepted_qty ?? 0) - ($item->mrn_qty ?? 0));
        if($type == 'jo')
        {
            $poQty = $item?->joItem?->order_qty;
        }
        else {
            $poQty = $item?->poItem?->order_qty;
        }
    @endphp
    <tr data-group-item="{{json_encode($item)}}" id="row_{{$rowCount}}" data-index="{{$rowCount}}" @if($rowCount < 2 ) class="trselected" @endif>
        <input type="hidden" name="components[{{$rowCount}}][ref_type]" value="{{$type}}">
        @if($type == 'jo')
        <input type="hidden" name="components[{{$rowCount}}][purchase_order_id]" value="{{$item->joItem->jo_id}}">
        <input type="hidden" name="components[{{$rowCount}}][po_detail_id]" value="{{$item->job_order_item_id}}">
        @else
        <input type="hidden" name="components[{{$rowCount}}][purchase_order_id]" value="{{$item->poItem->purchase_order_id}}">
        <input type="hidden" name="components[{{$rowCount}}][po_detail_id]" value="{{$item->purchase_order_item_id}}">
        @endif
        <input type="hidden" name="components[{{$rowCount}}][gate_entry_header_id]" value="{{$item->header_id}}">
        <input type="hidden" name="components[{{$rowCount}}][gate_entry_detail_id]" value="{{$item->id}}">
        <td class="customernewsection-form">
            <div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" data-id="{{$item->id}}" value="{{$rowCount}}">
                <label class="form-check-label" for="Email_{{$rowCount}}"></label>
            </div>
        </td>
        <td>
            <input type="text" name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code" value="{{$item->item_code}}" readonly />
            <input type="hidden" name="components[{{$rowCount}}][item_id]" value="{{@$item->item_id}}" />
            <input type="hidden" name="components[{{$rowCount}}][item_code]" value="{{@$item->item_code}}" />
            <input type="hidden" name="components[{{$rowCount}}][item_name]" value="{{@$item->item->item_name}}" />
            <input type="hidden" name="components[{{$rowCount}}][hsn_id]" value="{{@$item->hsn_id}}" />
            <input type="hidden" name="components[{{$rowCount}}][hsn_code]" value="{{$item?->item?->hsn?->code}}" />
            <input type="hidden" name="components[{{$rowCount}}][is_inspection]" value="{{$item?->item?->is_inspection}}" />
            <input type="hidden" name="components[{{$rowCount}}][so_id]" value="{{$item?->so_id}}">

            @php
                $selectedAttr = @$item->attributes ? @$item->attributes()->whereNotNull('attr_value')->pluck('attr_value')->all() : [];
            @endphp
            @foreach(@$item->attributes as $attributeHidden)
                <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$attributeHidden->attr_name}}][attr_id]" value="{{$attributeHidden->id}}">
            @endforeach
            @foreach(@$item->item->itemAttributes as $itemAttribute)
                @if(count($selectedAttr))
                    @foreach ($itemAttribute->attributes() as $value)
                        @if(in_array($value->id, $selectedAttr))
                            <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="{{$value->id}}">
                        @endif
                    @endforeach
                @else
                    <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="">
                @endif
            @endforeach
        </td>
        <td>
            <input type="text" name="components[{{$rowCount}}][item_name]" value="{{$item?->item?->item_name}}" class="form-control mw-100 mb-25" readonly/>
        </td>
        <td class="poprod-decpt attributeBtn" id="itemAttribute_{{$rowCount}}" data-count="{{$rowCount}}" attribute-array="{{$item->item_attributes_array()}}">
        </td>
        <td>
            <input type="hidden" name="components[{{$rowCount}}][inventory_uom_id]" value="{{$item->inventoty_uom_id}}">
            <select class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
                <option value="{{$item->uom->id}}">{{ucfirst($item->uom->name)}}</option>
                @if($item?->item?->alternateUOMs)
                    @foreach($item?->item?->alternateUOMs as $alternateUOM)
                        <option value="{{$alternateUOM?->uom?->id}}" {{$alternateUOM?->uom?->id == $item->inventory_uom_id ? 'selected' : '' }}>{{$alternateUOM?->uom?->name}}</option>
                    @endforeach
                @endif
            </select>
        </td>
        <td>
            <input type="number" class="form-control mw-100 po_qty text-end checkNegativeVal" value="{{$poQty}}" step="any" readonly />
        </td>
        <td>
            <input type="hidden" name="module-type" id="module-type" value="{{ $moduleType }}">
            <input type="number" class="form-control mw-100 order_qty text-end checkNegativeVal" name="components[{{$rowCount}}][order_qty]"
            value="{{$balanceQty}}" step="any" readonly />
        </td>
        <td>
            <input type="number" class="form-control mw-100 accepted_qty text-end checkNegativeVal" name="components[{{$rowCount}}][accepted_qty]"
            value="{{($item?->item?->is_inspection == 0) ? $balanceQty : 0.00}}" step="any"  {{ ($item?->item?->is_inspection == 1) ? 'readonly' : '' }} />
        </td>
        <td>
            <input type="number" class="form-control mw-100 text-end rejected_qty" name="components[{{$rowCount}}][rejected_qty]" step="any"
            {{ ($item?->item?->is_inspection == 1) ? 'readonly' : '' }} />
        </td>
        <td><input type="number" name="components[{{$rowCount}}][rate]" value="{{$item->rate}}" readonly class="form-control mw-100 text-end rate" /></td>
        <td>
            <input type="number" name="components[{{$rowCount}}][basic_value]" value="{{($item->order_qty - $item->grn_qty)*$item->rate}}"  class="form-control text-end mw-100 basic_value checkNegativeVal" readonly step="any" />
        </td>
        <td>
            <div class="position-relative d-flex align-items-center">
                @foreach($item->itemDiscount as $itemDis_key => $itemDiscount)
                    <input type="hidden" value="{{$itemDiscount->id}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][id]">
                    <input type="hidden" value="{{$itemDiscount->ted_id}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][ted_id]">
                    <input type="hidden" value="{{$itemDiscount->ted_name}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_name]">
                    <input type="hidden" value="{{$itemDiscount->ted_percentage}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_perc]">
                    @php
                        $tedPerc = $itemDiscount->ted_percentage;
                    @endphp
                    @if (!intval($itemDiscount->ted_percentage))
                        @php
                            $tedPerc = (floatval($itemDiscount->ted_amount) / floatval($itemDiscount->assesment_amount)) * 100;
                        @endphp
                    @endif
                    <input type="hidden" value="{{$tedPerc}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][hidden_dis_perc]">
                    <input type="hidden" value="{{$itemDiscount->ted_amount}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_amount]">
                @endforeach
                <input type="number" readonly name="components[{{$rowCount}}][discount_amount]" class="form-control mw-100 text-end" style="width: 70px" value="{{$item->item_discount_amount}}" step="any" />
                <input type="hidden" name="components[{{$rowCount}}][discount_amount_header]" value="{{$item->header_discount_amount}}"/>
                <input type="hidden" name="components[{{$rowCount}}][exp_amount_header]" value="{{$item->expense_amount}}" />
                <div class="ms-50">
                    <button type="button" data-row-count="{{$rowCount}}" class="btn p-25 btn-sm btn-outline-secondary addDiscountBtn" style="font-size: 10px">Add</button>
                </div>
            </div>
        </td>
        <td>
            <input type="text" id="item_total_cost_{{$rowCount}}" name="components[{{$rowCount}}][item_total_cost]" value="{{($item->order_qty*$item->rate) - $item->discount_amount}}" readonly class="form-control mw-100 text-end item_total_cost" step="any"/>
            @foreach($item->taxes as $tax_key => $item_tax)
                <input type="hidden" value="{{@$item_tax->id}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][id]">
                <input type="hidden" value="{{@$item_tax->ted_id}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][t_d_id]">
                <input type="hidden" value="{{@$item_tax->applicable_type}}" name="components[1][taxes][{{$tax_key + 1}}][applicability_type]">
                {{-- <input type="hidden" value="" name="components[1][taxes][1][t_code]"> --}}
                <input type="hidden" value="{{@$item_tax->ted_name}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][t_type]">
                <input type="hidden" value="{{@$item_tax->ted_perc}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][t_perc]">
                <input type="hidden" value="{{@$item_tax->ted_amount}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][t_value]">
            @endforeach
        </td>
        <td>
            <div class="d-flex">
                <input type="hidden" id="components_storage_packets_{{ $rowCount }}" name="components[{{$rowCount}}][storage_packets]" value=""/>
                <div class="me-50 cursor-pointer addStoragePointBtn" data-bs-toggle="modal" data-row-count="{{$rowCount}}" data-bs-target="#storage-point-modal">
                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary"
                        data-bs-original-title="Storage Point" aria-label="Storage Point">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-map-pin">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span>
                </div>
                <input type="hidden" id="components_remark_{{ $rowCount }}" name="components[{{$rowCount}}][remark]" value="{{$item->remarks}}"/>
                <div class="me-50 cursor-pointer addRemarkBtn" data-row-count="{{$rowCount}}" {{-- data-bs-toggle="modal" data-bs-target="#Remarks" --}}>        <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" data-bs-original-title="Remarks" aria-label="Remarks"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></span></div>
            </div>
        </td>
        <input type="hidden" name="components[{{$rowCount}}][po_item_hidden_ids]" value="{{$item->id}}">
        <input type="hidden" name="components[{{$rowCount}}][po_hidden_ids]" value="{{$item->gateEntryHeader->id}}">
        <input type="hidden" name="components[{{$rowCount}}][ge_qty]" value="{{$item->ge_qty}}">
        <input type="hidden" name="components[{{$rowCount}}][item_module_type]" value="{{$moduleType}}">
    </tr>
@endforeach



