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
                <th>Item Code</th>
                <th>Item Name</th>
                <th>Attributes</th>
                <th>UOM</th>
                <th class="text-end">Qty</th>
                <th>UID</th>
                <th>Delivery<br>Cancelled?</th>
                <th>Customer</th>
                <th>Customer Name</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody class="mrntableselectexcel" id="item_header">
            @if (isset($items) && count($items) > 0)
                @php
                    $docType = $order->document_type;
                @endphp
                @foreach ($items as $orderItemIndex => $orderItem)
                    <tr id="item_row_{{$orderItemIndex}}" class="item_header_rows" onclick="onItemClick('{{$orderItemIndex}}');" data-detail-id="{{$orderItem->id}}" data-id="{{$orderItem->id}}">
                        <td class="customernewsection-form">
                            <div class="form-check form-check-primary custom-checkbox">
                                <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_{{$orderItemIndex}}" del-index="{{$orderItemIndex}}">
                                <label class="form-check-label" for="item_checkbox_{{$orderItemIndex}}"></label>
                            </div>
                        </td>
                        <td class="d-none">
                            <select id="item_type_{{$orderItemIndex}}" name="item_type[]" class="form-select mw-100">
                                <option value="Pickup" {{ ($orderItem->type ?? '') == 'Pickup' ? 'selected' : '' }}>Pickup</option>
                                <!-- <option value="Dropoff" {{ ($orderItem->type ?? '') == 'Dropoff' ? 'selected' : '' }}>Dropoff</option> -->
                            </select>
                        </td>
                        <td class="poprod-decpt">
                            <input type="text" id="items_dropdown_{{$orderItemIndex}}" name="item_code[]" placeholder="Select" class="form-control mw-100 except_draft ledgerselecct comp_item_code ui-autocomplete-input {{$orderItem->is_editable ? '' : 'restrict'}}" autocomplete="off" data-name="{{$orderItem->item_name}}" data-code="{{$orderItem->item_code}}" data-id="{{$orderItem->item_id}}" hsn_code="{{$orderItem->hsn_code ?? ''}}" item-name="{{$orderItem->item_name}}" specs="{{$orderItem->specifications ?? '[]'}}" attribute-array="{{$orderItem->item_attributes_array()}}" value="{{$orderItem->item_code}}" {{$orderItem->is_editable ? '' : 'readonly'}} item-location="[]">
                            <input type="hidden" name="item_id[]" id="items_dropdown_{{$orderItemIndex}}_value" value="{{$orderItem->item_id}}">
                            <input type="hidden" name="pickup_item_id[]" id="items_dropdown_{{$orderItemIndex}}_value" value="{{$orderItem->id}}">
                        </td>
                        <td class="poprod-decpt">
                            <input type="text" id="items_name_{{$orderItemIndex}}" class="form-control mw-100" value="{{$orderItem->item_name}}" name="item_name[]" readonly>
                        </td>
                        <td class="poprod-decpt" id="attribute_section_{{$orderItemIndex}}">
                            <button id="attribute_button_{{$orderItemIndex}}" {{count($orderItem->item_attributes_array()) > 0 ? '' : 'disabled'}} type="button" data-bs-toggle="modal" onclick="setItemAttributes('items_dropdown_{{$orderItemIndex}}', '{{$orderItemIndex}}', {{ json_encode(!$orderItem->is_editable) }});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                            <input type="hidden" name="attribute_value_{{$orderItemIndex}}">
                        </td>
                        <td>
                            <select class="form-select" name="uom_id[]" id="uom_dropdown_{{$orderItemIndex}}">
                                @if(isset($orderItem->uom_id) && isset($orderItem->uom_code))
                                    <option value="{{$orderItem->uom_id}}" selected>{{$orderItem->uom_code}}</option>
                                @endif
                            </select>
                        </td>
                        <td>
                            <input type="text" id="item_qty_{{$orderItemIndex}}" data-index="{{$orderItemIndex}}" value="{{ $orderItem->qty ?? 0 }}" name="item_req_qty[{{$orderItemIndex}}]" oninput="changeItemQty(this, {{$orderItemIndex}});" onchange="itemQtyChange(this, {{$orderItemIndex}})" class="form-control mw-100 text-end item_qty_input" onblur="setFormattedNumericValue(this);" />
                        <td>
                            <input type="text" id="item_uid_{{$orderItemIndex}}" name="item_uid[]" class="form-select mw-100" value="{{ $orderItem->uid ?? '' }}" readonly>
                        </td>
                        <td>
                            <input type="checkbox" class="form-check-input" name="item_delivery_cancelled[]" {{$orderItem->delivery_cancelled == 'Yes' ? "checked" : ""}} id="item_delivery_cancelled_{{$orderItemIndex}}" del-index="{{$orderItemIndex}}">
                            <label class="form-check-label" for="item_delivery_cancelled_{{$orderItemIndex}}"></label>
                        </td>
                        <td>
                            <input type="text" id="item_customer_{{$orderItemIndex}}" class="form-control mw-100" value="{{ $orderItem-> customer -> customer_code ?? '' }}" name="item_customer[]" readonly>
                            <input type="hidden" id="item_customer_id_{{$orderItemIndex}}" class="form-control mw-100" value="{{ $orderItem-> customer -> id ?? '' }}" name="item_customer_id[]" readonly>
                        </td>
                        <td>
                            <input type="text" id="item_customer_name_{{$orderItemIndex}}" class="form-control mw-100" value="{{ $orderItem -> customer_name ?? '' }}" name="item_customer_name[]" readonly>
                        </td>
                        <td>
                            <input type="text" id="item_mobile_{{$orderItemIndex}}" class="form-control mw-100" value="{{ $orderItem->customer_phone ?? '' }}" name="item_mobile[]" readonly>
                        </td>
                        <td>
                            <input type="text" id="item_email_{{$orderItemIndex}}" class="form-control mw-100" value="{{ $orderItem->customer_email ?? '' }}" name="item_email[]" readonly>
                        </td>
                        <td>
                            <div class="d-flex">
                                <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_{{$orderItemIndex}}');">
                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i>
                                    </span>
                                </div>
                            </div>
                            <input type = "hidden" id = "item_remarks_{{$orderItemIndex}}" name = "item_remarks[{{$orderItemIndex}}]" />
                        </td>
                        
                    </tr>
                @endforeach
            @endif
        </tbody>
        
        <tfoot>
            <tr class="totalsubheadpodetail"> 
                <td colspan="13"></td>
                <td class="{{isset($order->rfq->pqs) ? '' : 'd-none'}}" colspan="{{ isset($order->rfq->pqs) ? count($order->rfq->pqs) : 0 }}" id="vendor_bottom"> </td>
            </tr>
            
            <tr valign="top">
                <td colspan="13" rowspan="10">
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
