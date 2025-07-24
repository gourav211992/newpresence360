@foreach($soItems as $key => $item)
    @php
        $rowCount = $tableRowCount + $key + 1;
        $orderQty = 0.00;
        $moduleType = 'w-order';
        if($item->po && ($item->jo->gate_entry_required == 'yes')){
            $moduleType = 'gate-entry';
            $orderQty = ($item->ge_qty - $item->grn_qty);
        } else{
            $moduleType = 'w-order';
            $orderQty = ($item->qty  - $item->grn_qty);
        }
    @endphp
    <tr data-group-item="{{json_encode($item)}}" id="row_{{$rowCount}}" data-index="{{$rowCount}}" @if($rowCount < 2 ) class="trselected" @endif>
        <input type="hidden" name="components[{{$rowCount}}][ref_type]" value="{{$type}}">
        <input type="hidden" name="components[{{$rowCount}}][purchase_order_id]" value="{{$item->sale_order_id}}">
        <input type="hidden" name="components[{{$rowCount}}][po_detail_id]" value="{{$item->id}}">
        <input type="hidden" name="components[{{$rowCount}}][supplier_inv_detail_id]" value="">
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
            <input type="hidden" name="components[{{$rowCount}}][item_name]" value="{{@$item->item->name}}" />
            <input type="hidden" name="components[{{$rowCount}}][hsn_id]" value="{{@$item->hsn_id}}" />
            <input type="hidden" name="components[{{$rowCount}}][hsn_code]" value="{{$item?->item?->hsn?->code}}" />
            <input type="hidden" name="components[{{$rowCount}}][is_inspection]" value="{{$item?->item?->is_inspection}}" />
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
        <td class="poprod-decpt" id="itemAttribute_{{$rowCount}}" data-count="{{$rowCount}}" attribute-array="{{$item->item_attributes_array()}}">
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
            <input type="number" class="form-control mw-100 po_qty text-end checkNegativeVal" value="{{$item->qty}}" step="any" readonly />
        </td>
        <td>
            <input type="hidden" name="module-type" id="module-type" value="{{ $moduleType }}">
            <input type="number" class="form-control mw-100 order_qty text-end checkNegativeVal" name="components[{{$rowCount}}][order_qty]"
            value="{{$orderQty}}" step="any" {{ ($item?->header?->partial_delivery == 'no') ? 'readonly' : '' }} {{ ($item?->item?->is_inspection == 1) ? 'readonly' : '' }} />
        </td>
        <td>
            <input type="number" class="form-control mw-100 accepted_qty text-end checkNegativeVal" name="components[{{$rowCount}}][accepted_qty]"
            value="{{($item?->item?->is_inspection == 0) ? $orderQty : 0.00}}" step="any" {{ ($item?->item?->is_inspection == 1) ? 'readonly' : '' }} />
        </td>
        <td>
            <input type="number" class="form-control mw-100 text-end rejected_qty" name="components[{{$rowCount}}][rejected_qty]" readonly step="any"
            {{ ($item?->item?->is_inspection == 1) ? 'readonly' : '' }} />
        </td>
        <td><input type="number" name="components[{{$rowCount}}][rate]" value="{{$item->rate}}" readonly class="form-control mw-100 text-end rate" /></td>
        <td>
            <input type="number" name="components[{{$rowCount}}][basic_value]" value="{{($item->qty - $item->grn_qty)*$item->rate}}"  class="form-control text-end mw-100 basic_value checkNegativeVal" readonly step="any" />
        </td>
        <td>
        </td>
        <td>
            <input type="text" id="item_total_cost_{{$rowCount}}" name="components[{{$rowCount}}][item_total_cost]" value="{{$item->qty*$item->rate}}" readonly class="form-control mw-100 text-end item_total_cost" step="any"/>
        </td>
        <td>
            <div class="d-flex">
                <!-- <input type="hidden" id="components_storage_packets_{{ $rowCount }}" name="components[{{$rowCount}}][storage_packets]" value=""/>
                <div class="me-50 cursor-pointer addStoragePointBtn" data-bs-toggle="modal" data-row-count="{{$rowCount}}" data-bs-target="#storage-point-modal">
                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary"
                        data-bs-original-title="Storage Point" aria-label="Storage Point">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-map-pin">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span>
                </div> -->
                <input type="hidden" id="components_remark_{{ $rowCount }}" name="components[{{$rowCount}}][remark]" value="{{$item->remarks}}"/>
                <div class="me-50 cursor-pointer addRemarkBtn" data-row-count="{{$rowCount}}" {{-- data-bs-toggle="modal" data-bs-target="#Remarks" --}}>        <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" data-bs-original-title="Remarks" aria-label="Remarks"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></span></div>
            </div>
        </td>
        <input type="hidden" name="components[{{$rowCount}}][so_item_hidden_ids]" value="{{$item->id}}">
        <input type="hidden" name="components[{{$rowCount}}][so_hidden_ids]" value="{{$item->header->id}}">
        <input type="hidden" name="components[{{$rowCount}}][ge_qty]" value="{{$item->ge_qty}}">
        <input type="hidden" name="components[{{$rowCount}}][item_module_type]" value="{{$moduleType}}">
    </tr>
@endforeach



