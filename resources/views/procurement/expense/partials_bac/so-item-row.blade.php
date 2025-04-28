@foreach($items as $key => $item)
    @php
        $rowCount = $key + 1;
    @endphp
    <tr id="row_{{$rowCount}}" data-index="{{$rowCount}}" @if($rowCount < 2 ) class="trselected" @endif>
        <input type="hidden" name="components[{{$rowCount}}][sale_order_id]" value="{{$item->sale_order_id}}">
        <input type="hidden" name="components[{{$rowCount}}][so_detail_id]" value="{{$item->id}}">
        <td class="customernewsection-form">
            <div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" data-id="{{$item->id}}" value="{{$rowCount}}">
                <label class="form-check-label" for="Email_{{$rowCount}}"></label>
            </div>
        </td>
        <td>
            <input type="text" name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code" value="{{$item->item_code}}" />
            <input type="hidden" name="components[{{$rowCount}}][item_id]" value="{{@$item->item_id}}" />
            <input type="hidden" name="components[{{$rowCount}}][item_code]" value="{{@$item->item_code}}" /> 
            <input type="hidden" name="components[{{$rowCount}}][item_name]" value="{{@$item->item->name}}" />
            <input type="hidden" name="components[{{$rowCount}}][hsn_id]" value="{{@$item->hsn_id}}" /> 
            <input type="hidden" name="components[{{$rowCount}}][hsn_code]" value="{{@$item->hsn_code}}" />
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
        <td class="poprod-decpt"> 
            <button type="button" class="btn p-25 btn-sm btn-outline-secondary attributeBtn" data-row-count="{{$rowCount}}" style="font-size: 10px">Attributes</button>
        </td>
        <td>
            <select class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
                <option value="{{@$item->uom->id}}">{{ucfirst(@$item->uom->name)}}</option>
            </select>
        </td>
        <td>
            <input type="text" class="form-control mw-100 accepted_qty" name="components[{{$rowCount}}][accepted_qty]" value="{{($item->order_qty - $item->invoice_qty)}}" />
        </td>
        <td>
            <input type="text" name="components[{{$rowCount}}][rate]" value="{{$item->rate}}" readonly class="form-control mw-100 text-end rate" />
        </td>
        <td>
            <input type="text" name="components[{{$rowCount}}][basic_value]" value="{{($item->order_qty - $item->invoice_qty)*$item->rate}}"  class="form-control text-end mw-100 basic_value" readonly />
        </td>
        <td>
            <div class="position-relative d-flex align-items-center">
                @foreach($item->discount_ted as $itemDis_key => $itemDiscount)
                    <input type="hidden" value="{{$itemDiscount->id}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][id]">
                    <input type="hidden" value="{{$itemDiscount->ted_name}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_name]">
                    <input type="hidden" value="{{$itemDiscount->ted_percentage}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_perc]">
                    <input type="hidden" value="{{$itemDiscount->ted_amount}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_amount]">
                @endforeach
                <input type="number" readonly name="components[{{$rowCount}}][discount_amount]" class="form-control mw-100 text-end" style="width: 70px" value="{{$item->item_discount_amount}}" />
                <input type="hidden" name="components[{{$rowCount}}][discount_amount_header]" value="{{$item->header_discount_amount}}"/>
                <input type="hidden" name="components[{{$rowCount}}][exp_amount_header]" value="{{$item->item_expense_amount}}" />
                <div class="ms-50">
                    <button type="button" data-row-count="{{$rowCount}}" class="btn p-25 btn-sm btn-outline-secondary addDiscountBtn" style="font-size: 10px">Discount</button>
                </div>
            </div>
        </td>
        <td>
            <input type="text" id="item_total_cost_{{$rowCount}}" name="components[{{$rowCount}}][item_total_cost]" value="{{($item->order_qty*$item->rate) - $item->discount_amount}}" readonly class="form-control mw-100 text-end item_total_cost" />
            @foreach($item->tax_ted as $tax_key => $item_tax)
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
            <select class="form-select mw-100 " name="components[{{$rowCount}}][cost_center_id]">
                <option value="">Select</option>
                @foreach($costCenters as $costCenter)
                    <option value="{{$costCenter->id}}">
                        {{$costCenter->name}}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <div class="d-flex">
                <div class="me-50 cursor-pointer addRemarkBtn" data-row-count="{{$rowCount}}" {{-- data-bs-toggle="modal" data-bs-target="#Remarks" --}}>
                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" data-bs-original-title="Remarks" aria-label="Remarks">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13">
                            </line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </span>
                </div>
            </div>
        </td>
    </tr>
@endforeach



