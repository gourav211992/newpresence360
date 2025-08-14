<div class="card">
    <div class="card-body customernewsection-form">
        <div class="border-bottom mb-2 pb-25">
            <div class="row">
                <div class="col-md-6">
                    <div class="newheader">
                        <h4 class="card-title text-theme">Item Detail</h4>
                        <p class="card-text">Fill the details</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive pomrnheadtffotsticky">
                    <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                        <thead>
                            <tr>
                                <th class="customernewsection-form">
                                    <div class="form-check form-check-primary custom-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select_all_items_checkbox" oninput="checkOrRecheckAllItems(this);">
                                        <label class="form-check-label" for="select_all_items_checkbox"></label>
                                    </div>
                                </th>
                                <th width="150px">Item Code</th>
                                <th width="240px">Item Name</th>
                                <th>Attributes</th>
                                <th>UOM</th>
                                <th width="130px">Order No</th>
                                <th class="numeric-alignment">Order Qty</th>
                                <th class="numeric-alignment">Picked Qty</th>
                                <th class="numeric-alignment">Delivery Date</th>
                                <th class="numeric-alignment">Rate</th>
                                <th class="numeric-alignment">Value</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="mrntableselectexcel" id="item_header">
                            @if (isset($order))
                                @php
                                    $docType = $order->document_type;
                                @endphp
                                @foreach ($order->items as $orderItemIndex => $orderItem)
                                    <tr id="item_row_{{$orderItemIndex}}" class="item_header_rows" onclick="onItemClick('{{$orderItemIndex}}');" data-detail-id="{{$orderItem->id}}" data-id="{{$orderItem->id}}">
                                        <input type="hidden" name="pl_item_id[]" value="{{$orderItem->id}}" {{$orderItem->is_editable ? '' : 'readonly'}}>
                                        <td class="customernewsection-form">
                                            <div class="form-check form-check-primary custom-checkbox">
                                                <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_{{$orderItemIndex}}" del-index="{{$orderItemIndex}}">
                                                <label class="form-check-label" for="item_checkbox_{{$orderItemIndex}}"></label>
                                            </div>
                                        </td>
                                        <td class="poprod-decpt">
                                            <input type="text" id="items_dropdown_{{$orderItemIndex}}" name="item_code[{{$orderItemIndex}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input {{$orderItem->is_editable ? '' : 'restrict'}}" autocomplete="off" data-name="{{$orderItem->item?->item_name}}" data-code="{{$orderItem->item?->item_code}}" data-id="{{$orderItem->item?->id}}" hsn_code="{{$orderItem->item?->hsn?->code}}" item-name="{{$orderItem->item?->item_name}}" specs="{{$orderItem->item?->specifications}}" attribute-array="{{$orderItem->item_attributes_array()}}" value="{{$orderItem->item?->item_code}}" {{$orderItem->is_editable ? '' : 'readonly'}} item-location="[]">
                                            <input type="hidden" name="item_id[]" id="items_dropdown_{{$orderItemIndex}}_value" value="{{$orderItem->item_id}}">
                                        </td>
                                        <td class="poprod-decpt">
                                            <input type="text" id="items_name_{{$orderItemIndex}}" class="form-control mw-100" value="{{$orderItem->item?->item_name}}" name="item_name[{{$orderItemIndex}}]" readonly>
                                        </td>
                                        <td class="poprod-decpt" id="attribute_section_{{$orderItemIndex}}">
                                            <button id="attribute_button_{{$orderItemIndex}}" {{count($orderItem->item_attributes_array()) > 0 ? '' : 'disabled'}} type="button" data-bs-toggle="modal" onclick="setItemAttributes('items_dropdown_{{$orderItemIndex}}', '{{$orderItemIndex}}', {{ json_encode(!$orderItem->is_editable) }});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                                            <input type="hidden" name="attribute_value_{{$orderItemIndex}}">
                                        </td>
                                        <td>
                                            <select class="form-select" name="uom_id[]" id="uom_dropdown_{{$orderItemIndex}}">
                                            </select>
                                        </td>
                                        <td class="numeric-alignment">
                                            <input type="text" id="item_order_no_{{$orderItemIndex}}" value="{{ $order->document_status !== 'approved' && $order->document_status !== 'approval_not_required' ? 0.00 : $orderItem->so->book_code."-".$orderItem->so->document_number }}" name="item_order_no[{{$orderItemIndex}}]" class="form-control mw-100 text-end" readonly>
                                        </td>
                                        <td class="numeric-alignment">
                                            <input type="text" id="item_order_qty_{{$orderItemIndex}}" value="{{ $order->document_status !== 'approved' && $order->document_status !== 'approval_not_required' ? 0.00 : $orderItem->order_qty }}" name="item_order_qty[{{$orderItemIndex}}]" class="form-control mw-100 text-end" readonly>
                                        </td>
                                        <td class="numeric-alignment">
                                            <input type="text" id="item_picked_qty_{{$orderItemIndex}}" value="{{ $order->document_status !== 'approved' && $order->document_status !== 'approval_not_required' ? 0.00 : $orderItem->picked_qty }}" name="item_picked_qty[{{$orderItemIndex}}]" class="form-control mw-100 text-end" readonly>
                                        </td>
                                        <td class="numeric-alignment">
                                            <input type="text" id="item_delivery_date_{{$orderItemIndex}}" value="{{ $order->document_status !== 'approved' && $order->document_status !== 'approval_not_required' ? 0.00 : $orderItem->delivery_date }}" name="item_delivery_date[{{$orderItemIndex}}]" class="form-control mw-100 text-end" readonly>
                                        </td>
                                        <td class="numeric-alignment">
                                            <input type="text" id="item_rate_{{$orderItemIndex}}" value="{{ $orderItem->rate }}" name="item_rate[{{$orderItemIndex}}]" disabled class="form-control mw-100 text-end" oninput="setValue({{$orderItemIndex}});">
                                        </td>
                                        <td class="numeric-alignment">
                                            <input type="text" id="item_value_{{$orderItemIndex}}" value="{{ $orderItem->total_amount }}" name="item_value[{{$orderItemIndex}}]" class="form-control mw-100 text-end" readonly>
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick="setItemRemarks('item_remarks_{{$orderItemIndex}}');">
                                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span>
                                                </div>
                                            </div>
                                            <input type="hidden" id="item_remarks_{{$orderItemIndex}}" name="item_remarks[{{$orderItemIndex}}]">
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="totalsubheadpodetail"> 
                                <td colspan="9"></td>
                            </tr>
                            <tr valign="top">
                                <td id = "item_details_td" colspan="12" rowspan="10">
                                    <table class="table border">
                                        <tr>
                                            <td class="p-0">
                                                <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                                            </td>
                                        </tr>   
                                        <tr> 
                                            <td class="poprod-decpt">
                                                <div id ="current_item_cat_hsn">

                                                </div>
                                            </td> 
                                        </tr>
                                        <tr id = "current_item_specs_row"> 
                                            <td class="poprod-decpt">
                                                <div id ="current_item_specs">

                                                </div>
                                            </td> 
                                        </tr> 
                                        <tr id = "current_item_attribute_row"> 
                                            <td class="poprod-decpt">
                                                <div id ="current_item_attributes">

                                                </div>
                                            </td> 
                                        </tr> 
                                        <tr id = "current_item_stocks_row"> 
                                            <td class="poprod-decpt">
                                                <div id ="current_item_stocks">

                                                </div>
                                            </td> 
                                        </tr> 
                                        
                                        <tr id = "current_item_inventory"> 
                                            <td class="poprod-decpt">
                                                <div id ="current_item_inventory_details">

                                                </div>
                                            </td> 
                                        </tr> 
                                        
                                        <tr id = "current_item_lot_no_row">
                                            <td class="poprod-decpt">
                                                <div id ="current_item_lot_no">

                                                </div>
                                                </td>
                                        </tr>
                                        <tr id = "current_item_so_no_row">
                                            <td class="poprod-decpt">
                                                <div id ="current_item_so_no">

                                                </div>
                                                </td>
                                        </tr>
                                        

                                        <tr id = "current_item_qt_no_row"> 
                                            <td class="poprod-decpt">
                                                <div id ="current_item_qt_no">

                                                </div>
                                            </td> 
                                        </tr>

                                        <tr id = "current_item_store_location_row"> 
                                            <td class="poprod-decpt">
                                                <div id ="current_item_store_location">

                                                </div>
                                            </td> 
                                        </tr>

                                        <tr id = "current_item_description_row">
                                            <td class="poprod-decpt">
                                                <span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>: <span style = "text-wrap:auto;" id = "current_item_description"></span></span>
                                                </td>
                                        </tr>

                                        <tr id = "current_item_land_lease_agreement_row">
                                            <td class="poprod-decpt">
                                                <div id ="current_item_land_lease_agreement">
                                                </div>
                                            </td>
                                        </tr>
                                    </table> 
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
