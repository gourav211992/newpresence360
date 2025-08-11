<div class="table-responsive pomrnheadtffotsticky">
    <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad" id="quote_data"> 
        <thead>
            <tr>
                <th width="30px" class="customernewsection-form">
                    <div class="form-check form-check-primary custom-checkbox">
                        <input type="checkbox" class="form-check-input" id="select_all_items_checkbox" oninput="checkOrRecheckAllItems(this);">
                        <label class="form-check-label" for="select_all_items_checkbox"></label>
                    </div>
                </th>
                <th width="150px">Item Code</th>
                <th width="240px">Item Name</th>
                <th width="70px">Attributes</th>
                <th width="50px">UOM</th>
                <th width="100px" class="numeric-alignment">Requested Qty</th>
                 @if(isset($order->selected_vendor) && $order->document_status != App\Helpers\ConstantHelper::DRAFT)
                    <th><input type='hidden' id = 'vendor_radio_{{ $order->selected_vendor }}' name="vendor_radio" class="vendor_radio" value = '{{ $order -> selected_vendor }}' >{{ $order->vendor->company_name}}</th>
                @elseif(isset($order))
               @foreach ($order->rfq->pqs as $pq)
                <th class="dynamic-vendor-th" style="width: 100px; word-break: break-word; white-space: normal; line-height: 1; text-align: center; vertical-align: middle;">
                    <div class="form-check form-check-primary custom-radio" style="display: flex; flex-direction: column; align-items: center;">
                        <input type="checkbox" class="form-check-input vendor_radio item_row_checks" name="vendor_radio" value="{{ $pq->vendor_id }}" {{ $pq->vendor_id == $order->selected_vendor ? 'checked' : ''}} id="vendor_radio_{{ $pq->vendor_id }}">
                        <input type="hidden" class="form-check-input" name="pq_id" value="{{ $pq->id }}" id="pq_id_{{ $pq->vendor_id }}">

                        <label class="form-check-label" for="vendor_radio_{{ $pq->vendor_id }}">
                            <span style="display: block; font-size: 11px; margin-top: 2px;">
                                {{ ucwords(strtolower($pq->vendor_name)) }}
                            </span>
                        </label>
                    </div>
                </th>
            @endforeach

                @endif
            </tr>
        </thead>
        <tbody class="mrntableselectexcel" id="item_header">
            @if (isset($items) && count($items) > 0)
                @php
                    $docType = $order->document_type;
                @endphp
                @foreach ($items as $orderItemIndex => $orderItem)
                    <tr id="item_row_{{$orderItemIndex}}" class="item_header_rows" onclick="onItemClick('{{$orderItemIndex}}');" data-detail-id="{{$orderItem->id}}" data-id="{{$orderItem->id}}">
                        <input type="hidden" id="pq_item_id_{{$orderItemIndex}}" name="pq_item_id[]" value="{{$orderItem->id}}" {{$orderItem->is_editable ? '' : 'readonly'}}>
                        <td class="customernewsection-form">
                            <div class="form-check form-check-primary custom-checkbox">
                                <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_{{$orderItemIndex}}" del-index="{{$orderItemIndex}}">
                                <label class="form-check-label" for="item_checkbox_{{$orderItemIndex}}"></label>
                            </div>
                        </td>
                        <td class="poprod-decpt">
                            <input type="text" id="items_dropdown_{{$orderItemIndex}}" name="item_code[]" placeholder="Select" class="form-control mw-100 except_draft ledgerselecct comp_item_code ui-autocomplete-input {{$orderItem->is_editable ? '' : 'restrict'}}" autocomplete="off" data-name="{{$orderItem->item?->item_name}}" data-code="{{$orderItem->item?->item_code}}" data-id="{{$orderItem->item?->id}}" hsn_code="{{$orderItem->item?->hsn?->code}}" item-name="{{$orderItem->item?->item_name}}" specs="{{$orderItem->item?->specifications}}" attribute-array="{{$orderItem->item_attributes_array()}}" value="{{$orderItem->item?->item_code}}" {{$orderItem->is_editable ? '' : 'readonly'}} item-location="[]">
                            <input type="hidden" name="item_id[]" id="items_dropdown_{{$orderItemIndex}}_value" value="{{$orderItem->item_id}}">
                            @if ($orderItem->rfq_item_id)
                                <input type="hidden" name="rfq_item_ids" id="rfq_id_{{$orderItemIndex}}" value="{{$orderItem->rfq_item_ids}}">
                            @endif
                            @if ($orderItem->pwo_item_id)
                                <input type="hidden" name="pwo_item_id" id="pwo_id_{{$orderItemIndex}}" value="{{$orderItem->pwo_item_id}}">
                            @endif
                        </td>
                        <td class="poprod-decpt">
                            <input type="text" id="items_name_{{$orderItemIndex}}" class="form-control mw-100" value="{{$orderItem->item?->item_name}}" name="item_name[]" readonly>
                        </td>
                        <td class="poprod-decpt" id="attribute_section_{{$orderItemIndex}}">
                            <button id="attribute_button_{{$orderItemIndex}}" {{count($orderItem->item_attributes_array()) > 0 ? '' : 'disabled'}} type="button" data-bs-toggle="modal" onclick="setItemAttributes('items_dropdown_{{$orderItemIndex}}', '{{$orderItemIndex}}', {{ json_encode(!$orderItem->is_editable) }});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                            <input type="hidden" name="attribute_value_{{$orderItemIndex}}">
                        </td>
                        <td>
                            <select class="form-select" name="uom_id[]" id="uom_dropdown_{{$orderItemIndex}}">
                            </select>
                        </td>
                        <td><input type="text" id = "item_req_qty_{{$orderItemIndex}}" data-index = '{{$orderItemIndex}}' value='{{ $orderItem->request_qty ?? 0 }}' name = "item_req_qty[{{$orderItemIndex}}]" oninput = "changeItemQty(this, {{$orderItemIndex}});" onchange = "itemQtyChange(this, {{$orderItemIndex}})" class="form-control mw-100 text-end item_qty_input" onblur = "setFormattedNumericValue(this);"/></td>
                        @if(isset($order->selected_vendor) && $order->document_status != App\Helpers\ConstantHelper::DRAFT)
                            @foreach($order->selectedPq->items->where('rfq_item_id',$orderItem->id) as $pq)
                                <td><input type='hidden' id = 'vendor_rate_{{ $orderItemIndex }}_{{ $pq->id }}' name="item_rate_{{ $orderItemIndex }}" value = '{{ $order -> selectedPQ -> suppliers -> id }}' >{{ $pq->rate}}</td>
                            @endforeach
                        @elseif(isset($orderItem) )
                        @foreach ($orderItem->pqItems->where('rfq_item_id',$orderItem->id) as $pq)
                            <td><input type='hidden' value = '{{ $$orderItem->pqItems -> vendor_id }}' >{{ $$orderItem->pqItems->rate }}</td>
                        @endforeach
                        @endif
                    </tr>
                @endforeach
            @endif
            
        </tbody>
        
        <tfoot>
            <tr class="totalsubheadpodetail"> 
                <td colspan="6"></td>
                <td class="{{isset($order->rfq->pqs) ? '' : 'd-none'}}" colspan="{{ isset($order->rfq->pqs) ? count($order->rfq->pqs) : 0 }}" id="vendor_bottom"> </td>
            </tr>
            
            <tr class="d-none" valign="top">
                <td colspan="{{2 + count($suppliers)}}" rowspan="10">
                    <table class="table border">
                        <tr>
                            <td class="p-0">
                                <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                            </td>
                        </tr>
                        <tr> 
                            <td class="poprod-decpt">
                                <div id ="current_item_cat_hsn"></div>
                            </td> 
                        </tr>
                        <tr id = "current_item_specs_row"> 
                            <td class="poprod-decpt">
                                <div id ="current_item_specs"></div>
                            </td> 
                        </tr> 
                        <tr id = "current_item_attribute_row"> 
                            <td class="poprod-decpt">
                                <div id ="current_item_attributes"></div>
                            </td> 
                        </tr> 
                        <tr id = "current_item_stocks_row"> 
                            <td class="poprod-decpt">
                                <div id ="current_item_stocks"></div>
                            </td> 
                        </tr> 
                        <tr> 
                            <td class="poprod-decpt">
                                <div id ="current_item_inventory_details"></div>
                            </td> 
                        </tr> 

                        <tr id = "current_item_delivery_schedule_row"> 
                            <td class="poprod-decpt">
                                <div id ="current_item_delivery_schedule"></div>
                            </td> 
                        </tr> 

                        <tr id = "current_item_qt_no_row"> 
                            <td class="poprod-decpt">
                                <div id ="current_item_qt_no"></div>
                            </td> 
                        </tr>

                        <tr id = "current_item_description_row">
                            <td class="poprod-decpt">
                                <span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>: <span style = "text-wrap:auto;" id = "current_item_description"></span></span>
                            </td>
                        </tr>
                    </table> 
                </td>
            </tr> 

        </tfoot>

</table>
</div>
