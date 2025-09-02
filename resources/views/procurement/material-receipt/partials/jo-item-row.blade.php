@foreach($poItems as $key => $item)
    @php
        $rowCount = $tableRowCount + $key + 1;
        $item_disc_key = '';
        $suppInvId = null;
        $suppInvItemId = null;
        $geId = null;
        $geItemId = null;
        $readOnly = '';
        $acceptedReadOnly = 'readonly';
        // $acceptedReadOnly = ($item?->item?->is_inspection == 1) ? 'readonly' : '';
        $joQty = $item->avail_order_qty ?? 0.00;
        $availableQty = $item->available_qty ?? 0.00;
        if($moduleType === 'gate-entry') {
            $geId = $item->ge_id;
            $geItemId = $item->ge_item_id;
            $suppInvId = $item->vendor_asn_id;
            $suppInvItemId = $item->vendor_asn_item_id;
        }
        if($moduleType === 'suppl-inv') {
            $suppInvId = $item->asn_id;
            $suppInvItemId = $item->asn_item_id;
        }

        if($moduleType === 'suppl-inv'){
            $readOnly = 'readonly';
        } elseif($moduleType === 'gate-entry'){
            $readOnly = ($item->jo?->partial_delivery == 'no') ? 'readonly' : '';
        } elseif($moduleType === 'j-order'){
            $readOnly = ($item->jo?->partial_delivery == 'no') ? 'readonly' : '';
        } else {
            $readOnly = '';
        }

        $grossItemValue = $availableQty * $item->rate;
        $itemDisc = $item->item_discount_amount;
        $headerDiscAmount = $item->header_discount_amount;
        $headerExpAmount = $item->expense_amount;
        $itemDiscPercentage = $grossItemValue > 0 ? ($itemDisc / $grossItemValue) * 100 : 0;
        $headerDiscPercentage = $grossItemValue > 0 ? ($headerDiscAmount / $grossItemValue) * 100 : 0;
        $headerExpPercentage = $grossItemValue > 0 ? ($headerExpAmount / $grossItemValue) * 100 : 0;

        $hasAssetDetail = (int) ($item?->item?->is_asset ?? 0);
        $asset = $item?->assetDetail;
        $assetPayload = [
            'asset_id'            => $asset->id ?? null,
            'asset_name'          => $asset->asset_name ?? ($item?->item?->item_name ?? ''),
            'asset_category_id'   => $item?->item?->asset_category_id,
            'asset_category_name' => $item?->item?->assetCategory?->name,
            'asset_code'          => null,
            'brand_name'          => $asset->brand_name ?? ($item?->item?->brand_name ?? ''),
            'model_no'            => $asset->model_no ?? ($item?->item?->model_no ?? ''),
            'estimated_life'      => $asset->estimated_life ?? ($item?->item?->expected_life ?? ''),
            'salvage_percentage'  => $item?->item?->getSalvagePercentage() ?? 0,
            'salvage_value'       => $asset->salvage_value ?? null,
            'procurement_type'    => $item?->jo?->procurement_type ?? null,
            'capitalization_date' => now()->toDateString(),
        ];
    @endphp
    <tr data-group-item="{{json_encode($item)}}" id="row_{{$rowCount}}" data-index="{{$rowCount}}" @if($rowCount < 2 ) class="trselected" @endif>
        <input type="hidden" name="components[{{$rowCount}}][ref_type]" value="{{$type}}">
        <input type="hidden" name="components[{{$rowCount}}][job_order_id]" value="{{$item->jo_id}}">
        <input type="hidden" name="components[{{$rowCount}}][jo_detail_id]" value="{{$item->id}}">
        <input type="hidden" name="components[{{$rowCount}}][vendor_asn_dtl_id]" value="{{$suppInvItemId}}">
        <input type="hidden" name="components[{{$rowCount}}][vendor_asn_id]" value="{{$suppInvId}}">
        <input type="hidden" name="components[{{$rowCount}}][gate_entry_detail_id]" value="{{$geItemId}}">
        <input type="hidden" name="components[{{$rowCount}}][gate_entry_header_id]" value="{{$geId}}">
        <td class="customernewsection-form">
            <div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" data-id="{{$item->id}}" value="{{$rowCount}}">
                <label class="form-check-label" for="Email_{{$rowCount}}"></label>
            </div>
        </td>
        <td>
            <input type="text" name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code" value="{{$item->item_code}}" {{$item?->job_order_item_id ? 'readonly' : ''}} />
            <input type="hidden" name="components[{{$rowCount}}][item_id]" value="{{@$item->item_id}}" />
            <input type="hidden" name="components[{{$rowCount}}][item_code]" value="{{@$item->item_code}}" />
            <input type="hidden" name="components[{{$rowCount}}][item_name]" value="{{@$item->item->name}}" />
            <input type="hidden" name="components[{{$rowCount}}][hsn_id]" value="{{@$item->hsn_id}}" />
            <input type="hidden" name="components[{{$rowCount}}][hsn_code]" value="{{$item?->item?->hsn?->code}}" />
            <input type="hidden" name="components[{{$rowCount}}][is_inspection]" value="{{$item?->item?->is_inspection}}" />
            @php
                $selectedAttr = @$item->attributes ? @$item->attributes()->whereNotNull('attribute_value')->pluck('attribute_value')->all() : [];
            @endphp
            @foreach(@$item->attributes as $attributeHidden)
                <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$attributeHidden->attribute_name}}][attr_id]" value="{{$attributeHidden->id}}">
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
        <td class="poprod-decpt" id="itemAttribute_{{$rowCount}}" data-count="{{$rowCount}}" attribute-array="{{$item->item_attributes_array()}}" {{$item?->job_order_item_id ? 'data-disabled="true"' : ''}}>
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
            <input type="number" class="form-control mw-100 po_qty text-end checkNegativeVal" value="{{$joQty}}" step="any" readonly />
        </td>
        <td>
            <input type="hidden" name="module-type" id="module-type" value="{{ $moduleType }}">
            <input type="number" class="form-control mw-100 order_qty text-end checkNegativeVal" name="components[{{$rowCount}}][order_qty]"
            value="{{$availableQty}}" step="any" {{ $readOnly }} />
        </td>
        <td>
            <input type="number" class="form-control mw-100 accepted_qty text-end checkNegativeVal" name="components[{{$rowCount}}][accepted_qty]"
            value="{{($item?->item?->is_inspection == 0) ? $availableQty : 0.00}}" step="any"  {{ $acceptedReadOnly }} />
        </td>
        <td>
            <input type="number" class="form-control mw-100 text-end rejected_qty" name="components[{{$rowCount}}][rejected_qty]" readonly step="any"
            {{ $acceptedReadOnly }} />
        </td>
        <td>
            <input type="number" name="components[{{$rowCount}}][rate]" value="{{$item->rate}}" readonly class="form-control mw-100 text-end rate" step="any" />
        </td>
        <td>
            <input type="number" name="components[{{$rowCount}}][basic_value]" value="{{($item->order_qty - $item->grn_qty)*$item->rate}}" class="form-control text-end mw-100 basic_value checkNegativeVal" readonly step="any" />
        </td>
        <td>
            <div class="position-relative d-flex align-items-center">
                @foreach($item->itemDiscount as $itemDis_key => $itemDiscount)
                    <input type="hidden" value="{{$itemDiscount->id}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][id]">
                    <input type="hidden" value="{{$itemDiscount->ted_id}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][ted_id]">
                    <input type="hidden" value="{{$itemDiscount->ted_name}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_name]">
                    <input type="hidden" value="{{$itemDiscount->ted_perc}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_perc]">
                    @php
                        $tedPerc = $itemDiscount->ted_perc;
                    @endphp
                    @if (!intval($itemDiscount->ted_perc))
                        @php
                            $tedPerc = (floatval($itemDiscount->ted_amount) / floatval($itemDiscount->assessment_amount)) * 100;
                        @endphp
                    @endif
                    <input type="hidden" value="{{$tedPerc}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][hidden_dis_perc]">
                    <input type="hidden" value="{{$itemDiscount->ted_amount}}" name="components[{{$rowCount}}][discounts][{{$itemDis_key+1}}][dis_amount]">
                @endforeach

                @if(!empty($item->jo->headerDiscount))
                    @php
                        $joId = $item->jo_id;
                        $poValue = \DB::table('erp_jo_products')
                            ->select(\DB::raw('SUM(order_qty * rate) as total'))
                            ->where('jo_id', $joId)
                            ->value('total');
                        $baseIndex = count($item->itemDiscount); // Offset for header discounts
                    @endphp
                    @foreach($item->jo->headerDiscount as $headDis_key => $headDiscount)
                        @php
                            $discPerc = ($poValue > 0) ? ($headDiscount->ted_amount / $poValue) * 100 : 0;
                            $discAmt = number_format(($grossItemValue * $discPerc / 100), 2);
                            $index = $baseIndex + $headDis_key + 1;
                        @endphp
                        <input type="hidden" value="" name="components[{{ $rowCount }}][discounts][{{ $index }}][id]">
                        <input type="hidden" value="{{ $headDiscount->ted_id }}" name="components[{{ $rowCount }}][discounts][{{ $index }}][ted_id]">
                        <input type="hidden" value="{{ $headDiscount->ted_name }}" name="components[{{ $rowCount }}][discounts][{{ $index }}][dis_name]">
                        <input type="hidden" value="{{ $discPerc }}" name="components[{{ $rowCount }}][discounts][{{ $index }}][dis_perc]">
                        <input type="hidden" value="{{ $discPerc }}" name="components[{{ $rowCount }}][discounts][{{ $index }}][hidden_dis_perc]">
                        <input type="hidden" value="{{ $discAmt }}" name="components[{{ $rowCount }}][discounts][{{ $index }}][dis_amount]">
                    @endforeach
                @endif
                <input type="number" readonly name="components[{{$rowCount}}][discount_amount]" class="form-control mw-100 text-end" style="width: 70px" value="{{$item->item_discount_amount + $item->header_discount_amount}}" step="any" />
                <input type="hidden" name="components[{{$rowCount}}][discount_amount_header]" value="{{$item->header_discount_amount}}"/>
                <input type="hidden" name="components[{{$rowCount}}][exp_amount_header]" value="{{$item->expense_amount}}" />
                <input type="hidden" name="components[{{$rowCount}}][item_disc_per]" value="{{$itemDiscPercentage}}" />
                <input type="hidden" name="components[{{$rowCount}}][header_disc_per]" value="{{$headerDiscPercentage}}"/>
                <input type="hidden" name="components[{{$rowCount}}][header_exp_per]" value="{{$headerExpPercentage}}" />
                <div class="ms-50">
                    <button type="button" data-row-count="{{$rowCount}}" class="btn p-25 btn-sm btn-outline-secondary addDiscountBtn" style="font-size: 10px">Add</button>
                </div>
            </div>
        </td>
        <td>
            <input type="text" id="item_total_cost_{{$rowCount}}" name="components[{{$rowCount}}][item_total_cost]" value="{{($item->order_qty*$item->rate) - $item->discount_amount}}" readonly class="form-control mw-100 text-end item_total_cost" step="any"/>
            @foreach($item->taxes as $tax_key => $item_tax)
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
            <div class="d-flex">
                @if($hasAssetDetail === 1)
                    <input type="hidden" name="components[{{$rowCount}}][assetDetailData]" />
                    <div class="cursor-pointer ms-50 text-success assetDetailBtn"
                        data-row-count="{{ $rowCount }}"
                        data-asset='@json($assetPayload)'
                        data-bs-toggle="modal"
                        data-bs-target="#assetDetailModal"
                        title="Asset Detail">
                        <span data-bs-toggle="tooltip" data-bs-placement="top" class="text-primary"
                            data-bs-original-title="Asset Detail" aria-label="Asset Detail">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-clipboard-check" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M10.854 6.146a.5.5 0 0 0-.708.708L11.293 8l-1.147 1.146a.5.5 0 0 0 .708.708L12 8.707l1.146 1.147a.5.5 0 0 0 .708-.708L12.707 8l1.147-1.146a.5.5 0 0 0-.708-.708L12 7.293 10.854 6.146z"/>
                                <path
                                    d="M10 1.5v1h1a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2h1v-1a1 1 0 1 1 2 0v1h2v-1a1 1 0 1 1 2 0zM5 4a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-9a1 1 0 0 0-1-1H5z"/>
                            </svg>
                        </span>
                    </div>
                @endif
                <input type="hidden" id="components_batches_{{ $rowCount }}" name="components[{{$rowCount}}][batch_details]" value=""/>
                <div class="me-50 cursor-pointer addBatchBtn"
                    data-bs-toggle="modal"
                    data-row-count="{{$rowCount}}"
                    data-is-batch-number="{{$item?->item?->is_batch_no}}"
                    data-is-expiry="{{$item?->item?->is_expiry}}"
                    data-bs-target="#item-batch-modal">
                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary"
                        data-bs-original-title="Item Batch" aria-label="Item Batch">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-map-pin">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span>
                </div>
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
        <input type="hidden" name="components[{{$rowCount}}][po_item_hidden_ids]" value="{{$item->id}}">
        <input type="hidden" name="components[{{$rowCount}}][po_hidden_ids]" value="{{$item->jo->id}}">
        <input type="hidden" name="components[{{$rowCount}}][ge_qty]" value="{{$item->ge_qty}}">
        <input type="hidden" name="components[{{$rowCount}}][item_module_type]" value="{{$moduleType}}">
        <input type="hidden" name="components[{{$rowCount}}][so_id]" value="{{$item->so_id}}">
    </tr>
@endforeach



