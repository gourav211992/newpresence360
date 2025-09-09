@foreach($bom->bomItems as $key => $bomDetail)
@php
   $rowCount = $key + 1;
   $itemCost = $bomDetail->item_cost;
   $cost = \App\Helpers\ItemHelper::getChildBomItemCost($bomDetail->item_id,[]);
@endphp
<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}" @if($rowCount < 2 ) class="trselected" @endif>
   <td class="customernewsection-form">
      <div class="form-check form-check-primary custom-checkbox">
         @if(empty($isCopy) || !$isCopy)
            <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" data-id="{{$bomDetail->id}}" value="{{$rowCount}}">
         @else
            <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" data-id="" value="{{$rowCount}}">
         @endif
         <label class="form-check-label" for="Email_{{$rowCount}}"></label>
      </div>
   </td>
   @if(isset($sectionRequired) && $sectionRequired)
   <td>
      <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="product_section" value="{{$bomDetail?->section_name}}" />
      <input type="hidden" name="components[{{$rowCount}}][section_id]" value="{{$bomDetail?->subSection?->section_id}}">
      <input type="hidden" name="components[{{$rowCount}}][section_name]" value="{{$bomDetail?->section_name}}">
   </td>
   @if(isset($subSectionRequired) && $subSectionRequired)
   <td>
      <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="product_sub_section" value="{{$bomDetail?->sub_section_name}}" />
      <input type="hidden" name="components[{{$rowCount}}][sub_section_id]" value="{{$bomDetail?->sub_section_id}}">
      <input type="hidden" name="components[{{$rowCount}}][sub_section_name]" value="{{$bomDetail?->sub_section_name}}">
   </td>
   @endif
   @endif
   <td>
      <input type="text" name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code" value="{{$bomDetail->item?->item_code}}" />
      <input type="hidden" name="components[{{$rowCount}}][item_id]" value="{{$bomDetail->item?->id}}" />
      <input type="hidden" name="components[{{$rowCount}}][item_code]" value="{{$bomDetail->item?->item_code}}" />
      @php
      $selectedAttr = $bomDetail->attributes ? $bomDetail->attributes()->pluck('attribute_value')->all() : [];
      @endphp
      @if(empty($isCopy) || !$isCopy)
         @foreach($bomDetail->attributes as $attributeHidden)
            <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$attributeHidden->attribute_name}}][attr_id]" value="{{$attributeHidden->id}}">
         @endforeach
      @endif
      @foreach($bomDetail->item?->itemAttributes as $itemAttribute)
         @foreach ($itemAttribute->attributes() as $value)
            @if(in_array($value->id, $selectedAttr))
            <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="{{$value->id}}">
            @endif
         @endforeach
      @endforeach
   </td>
   <td>
      <input type="text" name="components[{{$rowCount}}][item_name]" class="form-control mw-100 mb-25" readonly value="{{$bomDetail?->item?->item_name}}" />
  </td>
   <td class="poprod-decpt">
      <button type="button" {{-- data-bs-toggle="modal" data-bs-target="#attribute" --}} class="btn p-25 btn-sm btn-outline-secondary attributeBtn" @if(empty($isEdit) || !$isEdit) disabled @endif data-row-count="{{$rowCount}}" style="font-size: 10px">Attributes</button>
   </td>
   <td>
      <select class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
         <option value="{{$bomDetail->uom?->id}}">{{ucfirst($bomDetail->uom?->name)}}</option>
      </select>
   </td>
   <td>
      <div class="position-relative d-flex align-items-center">
      <input @readonly(true) type="number" class="form-control mw-100 text-end" value="{{$bomDetail->qty}}"  name="components[{{$rowCount}}][qty]" step="any"/>
      @if($bomDetail?->norm)
         <input type="hidden" name="components[{{$rowCount}}][qty_per_unit]" value="{{$bomDetail?->norm?->qty_per_unit}}">
         <input type="hidden" name="components[{{$rowCount}}][total_qty]" value="{{$bomDetail?->norm?->total_qty}}">
         <input type="hidden" name="components[{{$rowCount}}][std_qty]" value="{{$bomDetail?->norm?->std_qty}}">
      @endif
      @if(isset($consumption_method) && $consumption_method)
      <div class="ms-50 consumption_btn">
         <button type="button" data-row-count="{{$rowCount}}" class="btn p-25 btn-sm btn-outline-secondary addConsumptionBtn" style="font-size: 10px">F</button>
      </div>
      @endif
   </div>
   </td>
   <td class="{{$canView ? '' : 'd-none'}}">
      @if($canView)
         <input type="number" value="{{isset($itemCost) ? $itemCost : '' }}" name="components[{{$rowCount}}][item_cost]" class="form-control mw-100 text-end" step="any" />
         @else
         <input type="hidden" value="{{isset($itemCost) ? $itemCost : '' }}" name="components[{{$rowCount}}][item_cost]" class="form-control mw-100 text-end" step="any" />
         <input disabled type="number" value="" name="components[{{$rowCount}}][item_cos_dummy]" class="form-control mw-100 text-end" step="any" />
      @endif
   </td>
   @if(isset($supercedeCostRequired) && $supercedeCostRequired)
   <td class="{{$canView ? '' : 'd-none'}}">
      <input type="number" value="{{$bomDetail->superceeded_cost}}" name="components[{{$rowCount}}][superceeded_cost]" class="form-control mw-100 text-end" step="any"/>
   </td>
   @endif
   <td class="{{$canView ? '' : 'd-none'}}">
      @if($canView)
         <input type="number" value="{{$bomDetail->item_value}}" name="components[{{$rowCount}}][item_value]" class="form-control mw-100 text-end" readonly step="any" />
         @else
         <input type="hidden" value="{{$bomDetail->item_value}}" name="components[{{$rowCount}}][item_value]" class="form-control mw-100 text-end" readonly step="any" />
         <input disabled type="number" value="0.00" name="components[{{$rowCount}}][item_val_dummy]" class="form-control mw-100 text-end" step="any" />
      @endif
   </td>
   @if(isset($componentWasteRequired) && $componentWasteRequired)
   <td class="{{$canView ? '' : 'd-none'}}">
      <input type="number" value="{{$bomDetail->waste_perc}}" name="components[{{$rowCount}}][waste_perc]" class="form-control mw-100 text-end" step="any" />
   </td>
   <td class="{{$canView ? '' : 'd-none'}}">
      <input type="number" value="{{$bomDetail->waste_amount ?? ''}}" name="components[{{$rowCount}}][waste_amount]" class="form-control mw-100 text-end" step="any" />
   </td>
   @endif
   @if(isset($componentOverheadRequired) && $componentOverheadRequired)
   <td class="{{$canView ? '' : 'd-none'}}">
      <div class="position-relative d-flex align-items-center">
         @if($canView)
            <input type="number" value="{{$bomDetail->overhead_amount ?? ''}}" name="components[{{$rowCount}}][overhead_amount]" readonly class="form-control mw-100 text-end" style="width: 70px" step="any" />
            @foreach($bomDetail->overheads()->where('type','D')->get() as $over_key => $overhead)
            @if(empty($isCopy) || !$isCopy)
               <input type="hidden" name="components[{{$rowCount}}][overhead][{{$over_key+1}}][id]" value="{{$overhead->id}}">
            @endif
            <input type="hidden" id="item_overhead_id_{{$rowCount}}_{{$over_key+1}}" name="components[{{$rowCount}}][overhead][{{$over_key+1}}][overhead_id]" value="{{$overhead->overhead_id}}">
            <input type="hidden" id="item_overhead_input_{{$rowCount}}_{{$over_key+1}}" name="components[{{$rowCount}}][overhead][{{$over_key+1}}][description]" value="{{$overhead?->overhead_description}}">
            <input type="hidden" name="components[{{$rowCount}}][overhead][{{$over_key+1}}][perc]" value="{{isset($overhead->overhead_perc) && $overhead->overhead_perc != 0 ? $overhead->overhead_perc : ''}}">
            <input type="hidden" name="components[{{$rowCount}}][overhead][{{$over_key+1}}][amnt]" value="{{$overhead->overhead_amount}}" {{isset($overhead->overhead_perc) && $overhead->overhead_perc != 0 ? 'disabled-input' : ''}}>
            <input type="hidden" name="components[{{$rowCount}}][overhead][{{$over_key+1}}][ledger_name]" value="{{$overhead?->ledger_name}}">
            <input type="hidden" name="components[{{$rowCount}}][overhead][{{$over_key+1}}][ledger_id]" value="{{$overhead?->ledger_id}}">
            <input type="hidden" name="components[{{$rowCount}}][overhead][{{$over_key+1}}][ledger_group_name]" value="{{$overhead?->ledger_group_name}}">
            <input type="hidden" name="components[{{$rowCount}}][overhead][{{$over_key+1}}][ledger_group_id]" value="{{$overhead?->ledger_group_id}}">
            @endforeach
            <div class="ms-50">
               <button type="button" class="btn p-10 btn-sm btn-outline-secondary addOverHeadItemBtn" style="font-size: 8px" data-row-count="{{$rowCount}}"  @if(empty($isEdit) || !$isEdit) disabled @endif ><i data-feather="plus"></i></button>
            </div>
         @else
         <input type="hidden" value="{{$bomDetail->overhead_amount ?? ''}}" name="components[{{$rowCount}}][overhead_amount]" readonly class="form-control mw-100 text-end" style="width: 70px" step="any" />
         <input type="number" disabled value="0.00" name="components[{{$rowCount}}][overhead_amnt_dummy]" class="form-control mw-100 text-end" style="width: 70px" step="any" />
         @endif
      </div>
   </td>
   @endif
   <td class="{{$canView ? '' : 'd-none'}}">
      @if($canView)
         <input type="text" value="{{$bomDetail->total_amount}}" name="components[{{$rowCount}}][item_total_cost]" readonly class="form-control mw-100 text-end" />
      @else
         <input type="hidden" value="{{$bomDetail->total_amount}}" name="components[{{$rowCount}}][item_total_cost]" readonly class="form-control mw-100 text-end" />
         <input disabled type="text" value="0.00" name="components[{{$rowCount}}][item_total_cos_dummy]" class="form-control mw-100 text-end" />
      @endif
   </td>
   @if(isset($stationRequired) && $stationRequired)
   <td>
      <div class="d-flex align-items-center justify-content-center">
         <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="product_station" value="{{$bomDetail?->station_name}}" />
         <input type="hidden" name="components[{{$rowCount}}][station_id]" value="{{$bomDetail?->station_id}}">
         <input type="hidden" name="components[{{$rowCount}}][station_name]" value="{{$bomDetail?->station_name}}">
     </div>
   </td>
   @endif
   <td>
      <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" value="{{$bomDetail?->vendor?->company_name}}" name="product_vendor" />
      <input type="hidden" name="components[{{$rowCount}}][vendor_id]" value="{{$bomDetail?->vendor_id}}">
   </td>

   @if(isset($bacthInheritRequird) && $bacthInheritRequird)
      <td id="td_bacth_inherit_requird">
         <div class="form-check form-check-primary custom-checkbox">
            @if (isset($buttons))
               <input type="checkbox" class="form-check-input is_inherit_batch_item" id="is_inherit_batch_item" name="components[{{$rowCount}}][is_inherit_batch_item]" {{ $bomDetail->is_inherit_batch_item ? 'checked' : '' }} {{ $buttons['approve'] || request('amendment') || $buttons['draft'] ? '' :'disabled' }}>
            @else
               <input type="checkbox" class="form-check-input is_inherit_batch_item" id="is_inherit_batch_item" name="components[{{$rowCount}}][is_inherit_batch_item]" {{ @$bomDetail->is_inherit_batch_item ? 'checked' : '' }}>
            @endif
            <label class="form-check-label" for="is_inherit_batch_item"></label>
         </div>
      </td>
   @endif

   <td>
      <input type="hidden" name="components[{{$rowCount}}][remark]" value="{{$bomDetail->remark}}" />
      <div class="d-flex align-items-center justify-content-center">
         <div class="cursor-pointer addRemarkBtn" style="margin-right: 0.250rem;" data-row-count="{{$rowCount}}">
            <i data-feather="file-text"></i>
         </div>
         <div class="cursor-pointer linkAppend {{isset($cost['cost']) && isset($cost['route']) ? '' : 'd-none'}}">
            <a href="{{$cost['route'] ?? ''}}" target="_blank" class="">
               <i data-feather="link"></i>
           </a>
        </div>
     </div>
   </td>
   @if(empty($isCopy) || !$isCopy)
      <input type="hidden" name="components[{{$rowCount}}][bom_detail_id]" value="{{$bomDetail->id}}">
   @endif
</tr>
@endforeach