@foreach($mrn->items as $key => $item)
   @php
      $rowCount = $key + 1;
      $inspectionChecklistData = $item->item->loadInspectionChecklists() ?? [];
      $batchDetails = $item->batches ?? [];
      $isBatchEditable = ($item?->item?->is_batch_no == 1) ? 1 : 0;
      $isBatchEnable = ($item?->item?->is_batch_no == 1) ? 'Yes' : 'No';
      $mrnBatches = collect($batchDetails ?? [])->map(function ($b) {
         return [
               'id' => (int) $b->id,
               'mrn_batch_detail_id' => (int) $b->batch_detail_id ?? $b->id,
               'batch_number'        => (string) $b->batch_number,
               'manufacturing_year'  => $b->manufacturing_year ? (int) $b->manufacturing_year : null,
               'expiry_date'         => $b->expiry_date?->toDateString(), // Y-m-d
               'quantity'            => (float) $b->quantity,
               'inspection_qty'      => (float) $b->inspection_qty,
               'accepted_qty'        => (float) $b->accepted_qty,
               'rejected_qty'        => (float) $b->rejected_qty,
         ];
      })->values();

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
      }, $item->checklists->toArray());
      
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
         <input type="hidden" name="components[{{$rowCount}}][is_batch_no]" value="{{$isBatchEnable}}">
         <span class="badge bg-light-{{ $isBatchEnable == 'Yes' ? 'success' : 'danger' }}">{{ $isBatchEnable }}</span>
      </td>
      <td>
         <input type="number" class="form-control mw-100 mrn_qty text-end checkNegativeVal" name="components[{{$rowCount}}][mrn_qty]" value="{{$item?->mrnDetail?->order_qty}}" readonly step="any"/>
      </td>
      <td>
         <input type="number" class="form-control mw-100 text-end order_qty" name="components[{{$rowCount}}][order_qty]" value="{{$item->order_qty}}" step="any" {{ $isBatchEditable ? 'readonly' : '' }} />
      </td>
      <td>
         <input type="number" class="form-control mw-100 text-end accepted_qty checkNegativeVal" name="components[{{$rowCount}}][accepted_qty]" value="{{$item->accepted_qty}}" step="any" {{ $isBatchEditable ? 'readonly' : '' }} />
      </td>
      <td>
         <input type="number" class="form-control mw-100 text-end rejected_qty" readonly name="components[{{$rowCount}}][rejected_qty]" value="{{$item->rejected_qty}}" step="any" readonly />
      </td>
      <td>
         <div class="d-flex">
            <input type="hidden" id="inspection_data_{{$rowCount}}" name="components[{{$rowCount}}][inspectionData]" value='@json($existingCheckList)' />
            <div class="cursor-pointer ms-50 text-success inspectionChecklistBtn"
               data-row-count="{{ $rowCount }}"
               data-checklist='@json(["is_inspection" => 1, "checkLists" => $inspectionChecklistData])'
               data-existing-checklist='@json(["existingCheckLists" => $item->checklists])'
               data-bs-toggle="modal"
               data-bs-target="#inspectionChecklistModal"
               title="Inspection Checklist">
               <span data-bs-toggle="tooltip" data-bs-placement="top" title="Inspection" class="text-success"><i data-feather="check-circle"></i></span>
            </div>

            <input type="hidden" id="components_batches_{{ $rowCount }}" name="components[{{$rowCount}}][batch_details]" value=""/>
            <div
               class="addBatchBtn"
               data-row-count="{{ $rowCount }}"
               data-batch-count="{{ count($batchDetails ?? []) }}"
               data-mrn-batches='@json($mrnBatches)'   {{-- ← single quotes here --}}
               data-bs-toggle="modal"
               data-bs-target="#item-batch-modal"
               style="display: {{ $isBatchEditable ? 'block' : 'none' }};">
               <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary"
                  data-bs-original-title="Item Batch" aria-label="Item Batch">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  class="feather feather-map-pin">
               <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span>
            </div>

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

