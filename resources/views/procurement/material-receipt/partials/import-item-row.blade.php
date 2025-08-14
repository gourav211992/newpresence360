@foreach($uploadedItems as $key => $item)
    @php
        $rowCount = $key + 1;
    @endphp
    <tr data-group-item="{{json_encode($item)}}" id="row_{{$rowCount}}" data-index="{{$rowCount}}" @if($rowCount < 2 ) class="trselected" @endif>
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
            <input type="hidden" name="components[{{$rowCount}}][item_name]" value="{{@$item->item_name}}" />
            <input type="hidden" name="components[{{$rowCount}}][hsn_id]" value="{{@$item->hsn_id}}" />
            <input type="hidden" name="components[{{$rowCount}}][hsn_code]" value="{{$item?->item?->hsn?->code}}" />
            @php
                $selectedAttr = [];
                if (!empty($item->attributes)) {
                    $jsonAttributes = is_string($item->attributes) ? json_decode($item->attributes, true) : $item->attributes;
                    if (is_array($jsonAttributes)) {
                        foreach ($jsonAttributes as $jsonAttribute) {
                            if (isset($jsonAttribute['attribute_name_id'], $jsonAttribute['attribute_value_id'])) {
                                $selectedAttr[] = $jsonAttribute['attribute_value_id'];
                            }
                        }
                    }
                }
            @endphp
            @php
                $jsonAttributes = is_string($item->attributes) ? json_decode($item->attributes, true) : $item->attributes;
            @endphp
            @if(is_array($jsonAttributes))
                @foreach($jsonAttributes as $jsonAttribute)
                    <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$jsonAttribute['attribute_name_id']}}][attr_name]" value="{{$jsonAttribute['attribute_value_id']}}">
                @endforeach
            @endif
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
            <input type="text" name="components[{{$rowCount}}][item_name]" value="{{$item?->item_name}}" class="form-control mw-100 mb-25" readonly/>
        </td>
        <td class="poprod-decpt">
            <button type="button" class="btn p-25 btn-sm btn-outline-secondary attributeBtn" data-row-count="{{$rowCount}}" style="font-size: 10px" readonly>Attributes</button>
        </td>
        <td>
            <select class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
                <option value="{{$item->uom_id}}">{{ucfirst($item->uom->name)}}</option>
            </select>
        </td>
        <td>
            <select class="form-select item-store" name="components[{{$rowCount}}][store_id]">
                @foreach($locations as $erpStore)
                    <option value="{{$erpStore->id}}">
                        {{ucfirst($erpStore->store_name)}}
                    </option>
                @endforeach
            </select>
        </td>
        @if($item->store_id)
            <td>
                <select class="form-select" name="components[{{$rowCount}}][sub_store_id]">
                    <option value="{{ $item->store_id }}">
                        {{ucfirst($item?->erpSubStore?->name)}}
                    </option>
                </select>
            </td>
        @endif
        <td>
            <input type="number" class="form-control mw-100 order_qty text-end checkNegativeVal" name="components[{{$rowCount}}][order_qty]"
            value="{{$item->order_qty}}" step="any" />
        </td>
        <td>
            <input type="number" class="form-control mw-100 accepted_qty text-end checkNegativeVal" name="components[{{$rowCount}}][accepted_qty]"
            value="{{$item->order_qty}}" step="any" />
        </td>
        <td>
            <input type="number" class="form-control mw-100 text-end rejected_qty" name="components[{{$rowCount}}][rejected_qty]" readonly step="any"/>
        </td>
        <td><input type="number" name="components[{{$rowCount}}][rate]" value="{{$item->rate}}" readonly class="form-control mw-100 text-end rate" /></td>
        <td>
            <input type="number" name="components[{{$rowCount}}][basic_value]" value="{{$item->order_qty*$item->rate}}"  class="form-control text-end mw-100 basic_value checkNegativeVal" readonly step="any" />
        </td>
        <td>
            <div class="position-relative d-flex align-items-center">
                <input type="number" step="any" readonly name="components[{{$rowCount}}][discount_amount]" class="form-control mw-100 text-end" style="width: 70px" />
                <input type="hidden" name="components[{{$rowCount}}][discount_amount_header]"/>
                <input type="hidden" name="components[{{$rowCount}}][exp_amount_header]"/>
                <div class="ms-50">
                    <button type="button" data-row-count="{{$rowCount}}" class="btn p-25 btn-sm btn-outline-secondary addDiscountBtn" style="font-size: 10px">Add</button>
                </div>
            </div>
        </td>
        <td>
            <input type="number" step="any" name="components[{{$rowCount}}][item_total_cost]" readonly class="form-control mw-100 text-end" />
        </td>
        <td>
            <div class="d-flex">
                <input type="hidden" id="components_remark_{{ $rowCount }}" name="components[{{$rowCount}}][remark]" value=""/>
                <div class="me-50 cursor-pointer addRemarkBtn" data-row-count="{{$rowCount}}" {{-- data-bs-toggle="modal" data-bs-target="#Remarks" --}}>        <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" data-bs-original-title="Remarks" aria-label="Remarks"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></span></div>
            </div>
        </td>
    </tr>
@endforeach



