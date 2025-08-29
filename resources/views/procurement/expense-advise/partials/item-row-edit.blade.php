@foreach($mrn->items as $key => $item)
   @php
      $rowCount = $key + 1;
   @endphp
   <tr id="row_{{$rowCount}}" data-index="{{$rowCount}}" @if($rowCount < 2 ) class="trselected" @endif>
      <input type="hidden" name="components[{{$rowCount}}][header_id]" value="{{$item->expense_header_id}}">
      <input type="hidden" name="components[{{$rowCount}}][detail_id]" value="{{$item->id}}">
      <input type="hidden" name="components[{{$rowCount}}][purchase_order_id]" value="{{$item->expenseHeader->purchase_order_id}}">
      <input type="hidden" name="components[{{$rowCount}}][po_detail_id]" value="{{@$item->purchase_order_item_id}}">
      <input type="hidden" name="components[{{$rowCount}}][job_order_id]" value="{{$item->expenseHeader->job_order_id}}">
      <input type="hidden" name="components[{{$rowCount}}][jo_detail_id]" value="{{$item->job_order_item_id}}">
      <input type="hidden" name="components[{{$rowCount}}][jo_service_item_id]" value="{{$item->jo_service_item_id}}">
      <td class="customernewsection-form">
         <div class="form-check form-check-primary custom-checkbox">
            <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" data-id="{{$item->id}}" value="{{$rowCount}}">
            <label class="form-check-label" for="Email_{{$rowCount}}"></label>
         </div>
      </td>
      <td>
         <input type="text" name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code" value="{{$item->item_code}}" readonly/>
         <input type="hidden" name="components[{{$rowCount}}][item_id]" value="{{@$item->item_id}}" />
         <input type="hidden" name="components[{{$rowCount}}][item_code]" value="{{@$item->item_code}}" />
         <input type="hidden" name="components[{{$rowCount}}][item_name]" value="{{@$item->item->item_name}}" />
         <input type="hidden" name="components[{{$rowCount}}][hsn_id]" value="{{@$item->hsn_id}}" />
         <input type="hidden" name="components[{{$rowCount}}][hsn_code]" value="{{@$item->hsn_code}}" />
         @php
            $selectedAttr = $item->attributes ? $item->attributes()->whereNotNull('attr_value')->pluck('attr_value')->all() : [];
         @endphp
         @foreach($item->attributes as $attributeHidden)
            <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$attributeHidden->attribute_name}}][attr_id]" value="{{$attributeHidden->id}}">
         @endforeach
         @if(isset($item->item->itemAttributes) && ($item->item->itemAttributes))
         @foreach($item->item->itemAttributes as $itemAttribute)
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
         @endif
      </td>
      <td>
         <input type="text" name="components[{{$rowCount}}][item_name]" value="{{$item?->item?->item_name}}" class="form-control mw-100 mb-25" readonly/>
      </td>
      <td class="poprod-decpt attributeBtn" id="itemAttribute_{{$rowCount}}" data-count="{{$rowCount}}" attribute-array="{{$item->item_attributes_array()}}">
      </td>
      <td>
         <select class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
            <option value="{{@$item->uom->id}}">{{ucfirst(@$item->uom->name)}}</option>
         </select>
      </td>
      <td>
         <input type="number" class="form-control mw-100 text-end accepted_qty" name="components[{{$rowCount}}][accepted_qty]" value="{{$item->accepted_qty}}" step="any" />
      </td>
      <td><input type="number" name="components[{{$rowCount}}][rate]" value="{{$item->rate}}" class="form-control mw-100 text-end rate checkNegativeVal" readonly step="any"/></td>
      <td>
         <input type="number" name="components[{{$rowCount}}][basic_value]" value="{{($item->accepted_qty*$item->rate)}}" class="form-control text-end mw-100 basic_value" step="any" readonly />
      </td>
      <td>
         <div class="position-relative d-flex align-items-center">
            @foreach($item->itemDiscount as $itemDis_key => $itemDiscount)
               <input type="hidden" value="{{$itemDiscount->id}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][id]">
               <input type="hidden" value="{{$itemDiscount->ted_name}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_name]">
               <input type="hidden" value="{{$itemDiscount->ted_percentage}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_perc]">
               <input type="hidden" value="{{$itemDiscount->ted_amount}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_amount]">
            @endforeach
            <input type="number" readonly name="components[{{$rowCount}}][discount_amount]" class="form-control mw-100 text-end" style="width: 70px" value="{{$item->discount_amount}}" step="any"/>
            <input type="hidden" name="components[{{$rowCount}}][discount_amount_header]" value="{{$item->header_discount_amount}}"/>
            <input type="hidden" name="components[{{$rowCount}}][exp_amount_header]" value="{{$item->header_exp_amount}}" />
            <div class="ms-50">
               <button type="button" data-row-count="{{$rowCount}}" class="btn p-25 btn-sm btn-outline-secondary addDiscountBtn" style="font-size: 10px">Add</button>
            </div>
         </div>
      </td>
      <td>
         <input type="number" name="components[{{$rowCount}}][item_total_cost]" value="{{$item->net_value}}" readonly class="form-control mw-100 text-end" step="any"/>
         @foreach($item->taxes as $tax_key => $po_item_tax)
            <input type="hidden" value="{{$po_item_tax->id}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][id]">
            <input type="hidden" value="{{$po_item_tax->ted_id}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][t_d_id]">
            <input type="hidden" value="{{$po_item_tax->applicability_type}}" name="components[1][taxes][{{$tax_key + 1}}][applicability_type]">
            {{-- <input type="hidden" value="" name="components[1][taxes][1][t_code]"> --}}
            <input type="hidden" value="{{$po_item_tax->ted_name}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][t_type]">
            <input type="hidden" value="{{$po_item_tax->ted_percentage}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][t_perc]">
            <input type="hidden" value="{{$po_item_tax->ted_amount}}" name="components[{{$rowCount}}][taxes][{{$tax_key + 1}}][t_value]">
         @endforeach
      </td>
      <td>
         <div class="d-flex">
            <!-- <input type="hidden" id="components_stores_data_{{ $rowCount }}" name="components[{{$rowCount}}][store_data]" value=""/>
            <div class="me-50 cursor-pointer addDeliveryScheduleBtn" data-bs-toggle="modal" data-row-count="{{$rowCount}}" data-bs-target="#store-modal">
               <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary"
                  data-bs-original-title="Store Location" aria-label="Store Location">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  class="feather feather-map-pin">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
               </span>
            </div> -->
            <div class="me-50 cursor-pointer addRemarkBtn" data-row-count="{{$rowCount}}" {{-- data-bs-toggle="modal" data-bs-target="#Remarks" --}}>
               <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" data-bs-original-title="Remarks" aria-label="Remarks">
               <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text">
               <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></span></div>
               <input type="hidden" value="{{ $item->remark}}" name="components[{{$rowCount}}][remark]">
            </div>
         </div>
      </td>
   </tr>
@endforeach
