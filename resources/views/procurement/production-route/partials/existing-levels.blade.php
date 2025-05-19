@if(!empty($pRoute->levels))
    @foreach($pRoute->levels as $key => $val)
        @php
            $rowCount = $key + 1;
        @endphp
        <tr class="approvlevelflow" data-index="{{ $rowCount }}" data-level-id="{{ $val->id }}">
            <td>
                {{ $rowCount }}
                {{-- <input type="hidden" name="levels[{{ $rowCount }}][level_id]" value="{{ $val->id }}"> --}}
            </td>
            <td colspan="2">
                <h6 class="mb-0 fw-bolder text-dark">Level {{ $val->level }}</h6>
                <input type="hidden" name="levels[{{ $rowCount }}][level]" value="{{ $val->level }}">
                <input type="hidden" name="levels[{{ $rowCount }}][name]" value="{{ $val->name }}">
                <input type="hidden" name="levels[{{ $rowCount }}][level_id]" value="{{ $val->id }}">
            </td>
            <td>
            </td>
            <td>
            </td>
            <td>
                <a data-row-count="{{ $rowCount }}" data-index="{{ $rowCount }}" class="text-primary addLevel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-square">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                </a>
                <a data-row-count="{{ $rowCount }}" data-index="{{ $rowCount }}" class="deleteLevel text-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                </a>
            </td>
        </tr>

        @if(!empty($val->details))
            @foreach($val->details as $key1 => $detail)
                @php
                    $detailRowCount = $key1 + 1;
                @endphp
                <tr class="child-row" data-index="{{ $rowCount }}" data-detail-id="{{ $detailRowCount }}" data-level-id="{{ $val->id }}" data-child-id="{{ $detail->id }}">
                    <td>
                        <input type="hidden" name="levels[{{ $rowCount }}][details][{{ $detailRowCount }}][child_id]" value="{{ $detail->id }}">
                    </td>
                    <td>
                        <select class="form-select mw-100 select2 station" name="levels[{{ $rowCount }}][details][{{ $detailRowCount }}][station_id]" onchange="updateStationDropdown()">
                            <option value="">Select</option>
                            @foreach($stations as $val)
                                <option value="{{ $val->id }}" {{ old('levels.'.$rowCount.'.details.'.$detailRowCount.'.station_id', $detail->station_id) == $val->id ? 'selected' : '' }}>
                                    {{ $val->name }}
                                </option>
                            @endforeach
                            <input type="hidden" name="levels[{{ $rowCount }}][details][{{ $detailRowCount }}][hidden_station_id]" id="hidden_station_{{ $rowCount }}_{{ $detailRowCount }}" value="{{ $detail->station_id }}">
                        </select>
                    </td>
                    <td>
                        <select class="form-select mw-100 select2 parent" name="levels[{{ $rowCount }}][details][{{ $detailRowCount }}][parent_id]" onchange="updateStationDropdown()">
                            <option value="">Select</option>
                            @foreach($stations as $val)
                                <option value="{{ $val->id }}" {{ old('levels.'.$rowCount.'.details.'.$detailRowCount.'.parent_id', $detail->pr_parent_id) == $val->id ? 'selected' : '' }}>
                                    {{ $val->name }}
                                </option>
                            @endforeach
                            <input type="hidden" name="levels[{{ $rowCount }}][details][{{ $detailRowCount }}][hidden_parent_id]" id="hidden_parent_{{ $rowCount }}_{{ $detailRowCount }}" value="{{ $detail->pr_parent_id }}">
                        </select>
                    </td>
                    <td>
                        <input type="checkbox" class="form-check-input consumption-checkbox" data-level-id="{{ $rowCount }}" data-detail-id="{{ $detailRowCount }}" {{ old('levels.'.$rowCount.'.details.'.$detailRowCount.'.consumption', $detail->consumption ?? 'no') == 'yes' ? 'checked' : '' }}>
                        <input type="hidden" name="levels[{{ $rowCount }}][details][{{ $detailRowCount }}][consumption]" value="{{ old('levels.'.$rowCount.'.details.'.$detailRowCount.'.consumption', $detail->consumption ?? 'no') }}">
                    </td>
                    <td>
                        <input type="checkbox" class="form-check-input qa-checkbox" data-level-id="{{ $rowCount }}" data-detail-id="{{ $detailRowCount }}" {{ old('levels.'.$rowCount.'.details.'.$detailRowCount.'.qa', $detail->qa ?? 'no') == 'yes' ? 'checked' : '' }}>
                        <input type="hidden" name="levels[{{ $rowCount }}][details][{{ $detailRowCount }}][qa]" value="{{ old('levels.'.$rowCount.'.details.'.$detailRowCount.'.qa', $detail->qa ?? 'no') }}">
                    </td>
                    {{-- <td>
                        <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct pr_items" name="levels[{{ $rowCount }}][details][{{ $detailRowCount }}][semi_finished_item_id]" value="{{@$detail->items->item_name}}" />
                        <input type="hidden" id="semi_finished_item_id_{{ $rowCount }}_{{ $detailRowCount }}" name="levels[{{ $rowCount }}][details][{{ $detailRowCount }}][semi_finished_item_id]" value="{{@$detail->items->id}}" />
                        <input type="hidden" id="semi_finished_item_code_{{ $rowCount }}_{{ $detailRowCount }}" name="levels[{{ $rowCount }}][details][{{ $detailRowCount }}][item_code]" value="{{@$detail->items->item_code}}" />
                    </td>
                    <td class="attributes-container" style="white-space: nowrap; overflow: hidden; max-width: 200px; position: relative;">
                        @if(isset($detail->item_attributes) && $detail->item_attributes )
                        <div class="attribute-slider" style="display: flex; overflow-x: auto; max-width: 100%; padding: 5px; gap: 10px; border: 1px solid #ddd; border-radius: 5px; white-space: nowrap; min-width: 100%; overflow-x: auto;">
                            @php
                                // Assuming the `item_attributes` data from the detail array is already decoded into a PHP array
                                $selectedAttributes = json_decode($detail->item_attributes, true);
                                $selectedAttributes = $selectedAttributes ?? [];
                            @endphp
                                @foreach(json_decode($detail->item_attributes) as $key=> $itemAttribute)
                                    @php
                                        $attributeGroupId = $itemAttribute->attr_name;
                                        $groupName = $itemAttribute->attribute_name;
                                    @endphp
                                    <div class="attribute-group" style="display: flex; align-items: center; gap: 10px; min-width: 200px;">
                                        <label style="margin-right: 5px; white-space: nowrap;">{{ $groupName }}:</label>
                                        <select class="form-select select2" name='levels[{{$rowCount}}][details][{{$detailRowCount}}][attribute_data][{{$key}}][{{$attributeGroupId}}]' data-attr-name="{{$groupName}}" data-attr-group-id="{{$attributeGroupId}}">
                                            <option value="">Select {{ $groupName }}</option>
                                            <option value="{{ $itemAttribute->attr_value }}" {{ ($itemAttribute->attr_value) ? 'selected' : '' }}>{{ $itemAttribute->attribute_value }}</option>
                                        </select>
                                    </div>
                                @endforeach
                        </div>
                    </td> --}}


                    <td>
                        <a class="text-primary btn-add-child" data-index="{{ $rowCount }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-square">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                        </a>
                        <a class="delete-child text-danger" data-index="{{ $rowCount }}" data-detail-id="{{ $detailRowCount }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </a>
                    </td>
                </tr>
            @endforeach
        @endif
    @endforeach
@endif
