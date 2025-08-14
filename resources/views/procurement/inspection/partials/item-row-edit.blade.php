@foreach($mrn->items as $key => $item)
   @php
      $rowCount = $key + 1;
      $hasInspection = $item->is_inspection;
      $inspectionChecklistData = $hasInspection === 'yes' ? $item->item->loadInspectionChecklists() : [];
   @endphp
   <tr id="row_{{$rowCount}}" data-index="{{$rowCount}}" @if($rowCount < 2 ) class="trselected" @endif>
      <input type="hidden" name="components[{{$rowCount}}][mrn_header_id]" value="{{$item->header->mrn_header_id}}">
      <input type="hidden" name="components[{{$rowCount}}][mrn_detail_id]" value="{{$item->mrn_detail_id}}">
      <input type="hidden" name="components[{{$rowCount}}][inspection_dtl_id]" value="{{$item->id}}">
      <input type="hidden" name="components[{{$rowCount}}][inspection_header_id]" value="{{$item->header_id}}">
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
         <input type="hidden" name="components[{{$rowCount}}][item_name]" value="{{@$item->item->item_name}}" />
         <input type="hidden" name="components[{{$rowCount}}][hsn_id]" value="{{@$item->hsn_id}}" />
         <input type="hidden" name="components[{{$rowCount}}][hsn_code]" value="{{@$item->hsn_code}}" />
         @php
                $selectedAttr = $item->attributes
                    ? $item->attributes()->whereNotNull('attr_value')->pluck('attr_value')->all()
                    : [];
            @endphp
            @foreach ($item->attributes as $attributeHidden)
                <input type="hidden"
                    name="components[{{ $rowCount }}][attr_group_id][{{ $attributeHidden->attr_name }}][attr_id]"
                    value="{{ $attributeHidden->id }}">
            @endforeach
            @if (isset($item->item->itemAttributes) && $item->item->itemAttributes)
                @foreach ($item->item->itemAttributes as $itemAttribute)
                    @if (count($selectedAttr))
                        @foreach ($itemAttribute->attributes() as $value)
                            @if (in_array($value->id, $selectedAttr))
                                <input type="hidden"
                                    name="components[{{ $rowCount }}][attr_group_id][{{ $itemAttribute->attribute_group_id }}][attr_name]"
                                    value="{{ $value->id }}">
                            @endif
                        @endforeach
                    @else
                        <input type="hidden"
                            name="components[{{ $rowCount }}][attr_group_id][{{ $itemAttribute->attribute_group_id }}][attr_name]"
                            value="">
                    @endif
                @endforeach
            @endif
      </td>
      <td>
         <input type="text" name="components[{{$rowCount}}][item_name]" value="{{$item?->item?->item_name}}" class="form-control mw-100 mb-25" readonly/>
      </td>
      <td class="poprod-decpt attributeBtn" id="itemAttribute_{{ $rowCount }}" data-count="{{ $rowCount }}"
            attribute-array="{{ $item->item_attributes_array() }}"
            {{ $item?->job_order_item_id ? 'data-disabled="true"' : '' }}
            {{ $item?->purchase_order_item_id ? 'data-disabled="true"' : '' }}>
        </td>
      <td>
         <select class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
            <option value="{{@$item->uom->id}}">{{ucfirst(@$item->uom->name)}}</option>
         </select>
      </td>
      <td>
         <input type="number" class="form-control mw-100 mrn_qty text-end checkNegativeVal" name="components[{{$rowCount}}][mrn_qty]" value="{{$item?->mrnDetail?->order_qty}}" readonly step="any"/>
      </td>
      <td>
         <input type="number" class="form-control mw-100 text-end order_qty" name="components[{{$rowCount}}][order_qty]" value="{{$item->order_qty}}" step="any" />
      </td>
      <td>
         <input type="number" class="form-control mw-100 text-end accepted_qty checkNegativeVal" name="components[{{$rowCount}}][accepted_qty]" value="{{$item->accepted_qty}}" step="any" />
      </td>
      <td>
         <input type="number" class="form-control mw-100 text-end rejected_qty" readonly name="components[{{$rowCount}}][rejected_qty]" value="{{$item->rejected_qty}}" step="any" @readonly(true)/>
      </td>
      <td>
         <div class="d-flex">
            @if($hasInspection === 1 && !empty($inspectionChecklistData))
               <input type="hidden" name="components[{{$rowCount}}][inspectionData]" />
               <div class="cursor-pointer ms-50 text-success inspectionChecklistBtn"
                  data-row-count="{{ $rowCount }}"
                  data-checklist='@json(["is_inspection" => 1, "checkLists" => $inspectionChecklistData])'
                  data-existing-checklist='@json(["existingCheckLists" => $item->checklists])'
                  data-bs-toggle="modal"
                  data-bs-target="#inspectionChecklistModal"
                  title="Inspection Checklist">
                  <span data-bs-toggle="tooltip" data-bs-placement="top" title="Inspection" class="text-success"><i data-feather="check-circle"></i></span>
                 
               </div>
            @endif
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
