@foreach($mrnItems as $key => $item)
    @php
        $rowCount = $key + 1;
        $qty = ($item->order_qty ?? 0.00) - ($item->inspection_qty ?? 0.00);
        $hasInspection = $item->is_inspection;
        $inspectionChecklistData = $hasInspection == 1 ? $item->item->loadInspectionChecklists() : [];
        $batchDetails = $item->batches ?? [];
        $isBatchEditable = ($item?->item?->is_batch_no == 1) ? 1 : 0;
        $isBatchEnable = ($item?->item?->is_batch_no == 1) ? 'Yes' : 'No';
        $mrnBatches = collect($batchDetails ?? [])->map(function ($b) {
            return [
                'id'                 => null,
                'mrn_batch_detail_id'=> (int) $b->id,
                'batch_number'       => (string) $b->batch_number,
                // "" instead of null
                'manufacturing_year' => $b->manufacturing_year ? (string) (int) $b->manufacturing_year : '',
                // keep your date or default if you want a fixed placeholder
                'expiry_date'        => $b->expiry_date?->toDateString() ?: '-0001-11-30',
                // 🔄 rename quantity -> mrn_qty
                'mrn_qty'            => (float) $b->quantity,
                'inspection_qty'     => (float) $b->quantity,
                'accepted_qty'       => (float) $b->quantity,
                'rejected_qty'       => (float) $b->rejected_qty,
            ];
        })->values();
    @endphp
    <tr id="row_{{$rowCount}}" data-index="{{$rowCount}}" @if($rowCount < 2 ) class="trselected" @endif>
        <input type="hidden" name="components[{{$rowCount}}][mrn_header_id]" value="{{$item->mrn_header_id}}">
        <input type="hidden" name="components[{{$rowCount}}][mrn_detail_id]" value="{{$item->id}}">
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
            <input type="hidden" name="components[{{$rowCount}}][hsn_code]" value="{{$item?->item?->hsn?->code}}" />
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
        <td class="poprod-decpt" id="itemAttribute_{{$rowCount}}" data-count="{{$rowCount}}" attribute-array="{{$item->item_attributes_array()}}" {{$item?->purchase_order_item_id ? 'data-disabled="true"' : ''}} >
        </td>
        <td>
            <input type="hidden" name="components[{{$rowCount}}][inventoty_uom_id]" value="{{$item->inventoty_uom_id}}">
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
            <input type="hidden" name="components[{{$rowCount}}][is_batch_no]" value="{{$isBatchEnable}}">
            <span class="badge bg-light-{{ $isBatchEnable == 'Yes' ? 'success' : 'danger' }}">{{ $isBatchEnable }}</span>
        </td>
        <td>
            <input type="number" class="form-control mw-100 mrn_qty text-end checkNegativeVal" name="components[{{$rowCount}}][mrn_qty]" value="{{$item->order_qty}}" readonly step="any"/>
        </td>
        <td>
            <input type="number" class="form-control mw-100 order_qty text-end checkNegativeVal" name="components[{{$rowCount}}][order_qty]" value="{{$qty}}" step="any" {{ $isBatchEditable ? 'readonly' : '' }} />
        </td>
        <td>
            <input type="number" class="form-control mw-100 accepted_qty text-end checkNegativeVal" name="components[{{$rowCount}}][accepted_qty]" value="{{$qty}}" step="any" {{ $isBatchEditable ? 'readonly' : '' }} />
        </td>
        <td>
            <input type="number" class="form-control mw-100 rejected_qty text-end checkNegativeVal" name="components[{{$rowCount}}][rejected_qty]" value="0.00" readonly step="any"/>
        </td>
        <td>
            <div class="d-flex">
                @if($hasInspection == 1 && !empty($inspectionChecklistData))
                    <input type="hidden" name="components[{{$rowCount}}][inspectionData]" />
                    <div class="cursor-pointer ms-50 text-success inspectionChecklistBtn"
                        data-row-count="{{ $rowCount }}"
                        data-checklist='@json(["is_inspection" => 1, "checkLists" => $inspectionChecklistData])'
                        data-bs-toggle="modal"
                        data-bs-target="#inspectionChecklistModal"
                        title="Inspection Checklist">
                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Inspection" class="text-success">
                            <i data-feather="check-circle"></i>
                        </span>
                    </div>
                @endif
                <input type="hidden"
                    id="components_batches_{{ $rowCount }}"
                    name="components[{{ $rowCount }}][batch_details]"
                    value='@json($mrnBatches, JSON_UNESCAPED_SLASHES)' />

                <div class="addBatchBtn"
                    data-row-count="{{ $rowCount }}"
                    data-batch-count="{{ count($batchDetails ?? []) }}"
                    data-mrn-batches='@json($mrnBatches, JSON_UNESCAPED_SLASHES)'
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
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13">
                            </line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </span>
                </div>
            </div>
        </td>
        <input type="hidden" name="components[{{$rowCount}}][mrn_item_hidden_ids]" value="{{$item->id}}">
        <input type="hidden" name="components[{{$rowCount}}][mrn_hidden_ids]" value="{{$item->mrnHeader->id}}">
    </tr>
@endforeach



