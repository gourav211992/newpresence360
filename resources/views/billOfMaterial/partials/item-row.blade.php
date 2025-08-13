<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}">
   <td class="customernewsection-form">
      <div class="form-check form-check-primary custom-checkbox">
         <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" value="{{$rowCount}}" data-id="">
         <label class="form-check-label" for="Email_{{$rowCount}}"></label>
      </div>
   </td>
   @if(isset($sectionRequired) && $sectionRequired)
   <td>
      <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="product_section"/>
      <input type="hidden" name="components[{{$rowCount}}][section_id]">
      <input type="hidden" name="components[{{$rowCount}}][section_name]">
   </td>
   @if(isset($subSectionRequired) && $subSectionRequired)
   <td>
      <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="product_sub_section"/>
      <input type="hidden" name="components[{{$rowCount}}][sub_section_id]">
      <input type="hidden" name="components[{{$rowCount}}][sub_section_name]">
   </td>
   @endif
   @endif
   <td>
      <input type="text" name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code" />
      <input type="hidden" name="components[{{$rowCount}}][item_id]"/>
      <input type="hidden" name="components[{{$rowCount}}][item_code]"/>
   </td>
   <td>
      <input type="text" name="components[{{$rowCount}}][item_name]" class="form-control mw-100 mb-25" readonly/>
  </td>
   <td class="poprod-decpt"> 
      <button type="button" {{-- data-bs-toggle="modal" data-bs-target="#attribute" --}} class="btn p-25 btn-sm btn-outline-secondary attributeBtn" data-row-count="{{$rowCount}}" style="font-size: 10px">Attributes</button>
   </td>
   <td>
      <select class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
         
      </select>
   </td>
   <td>
      <div class="position-relative d-flex align-items-center">
         <input @readonly(true) type="number" step="any" class="form-control mw-100 text-end"  name="components[{{$rowCount}}][qty]"/>
         <div class="ms-50 consumption_btn">
            <button type="button" data-row-count="{{$rowCount}}" class="btn p-25 btn-sm btn-outline-secondary addConsumptionBtn" style="font-size: 10px">F</button>
         </div>
      </div>
   </td>
   <td class="{{$canView ? '' : 'd-none'}}">
      @if($canView)
      <input type="number" name="components[{{$rowCount}}][item_cost]" class="form-control mw-100 text-end" step="any" />
      @else
      <input type="hidden" name="components[{{$rowCount}}][item_cost]" class="form-control mw-100 text-end" step="any" />
      <input type="number" value="" name="components[{{$rowCount}}][item_cos_dummy]" value="" class="form-control mw-100 text-end" disabled step="any" />
      @endif
   </td>
   <td class="{{$canView ? '' : 'd-none'}}">
      @if($canView)
      <input type="number" name="components[{{$rowCount}}][item_value]" class="form-control mw-100 text-end" readonly step="any" />
      @else
      <input type="hidden" name="components[{{$rowCount}}][item_value]" class="form-control mw-100 text-end" readonly step="any" />
      <input type="number" value="0.00" name="components[{{$rowCount}}][item_val_dummy]" class="form-control mw-100 text-end" disabled step="any" />
      @endif

   </td>
   @if(isset($componentWasteRequired) && $componentWasteRequired)
   <td class="{{$canView ? '' : 'd-none'}}">
      <input type="number" name="components[{{$rowCount}}][waste_perc]" class="form-control mw-100 text-end" step="any" />
      {{-- <select class="form-select mw-100" name="components[{{$rowCount}}][waste_type]">
         @foreach($wasteTypes as $wasteType)
         <option value="{{$wasteType}}">{{$wasteType}}</option>
         @endforeach
      </select> --}}
   </td>
   <td class="{{$canView ? '' : 'd-none'}}">
      <input type="number" name="components[{{$rowCount}}][waste_amount]" class="form-control mw-100 text-end" step="any" />
   </td>
   @endif

   @if(isset($componentOverheadRequired) && $componentOverheadRequired)
   <td class="{{$canView ? '' : 'd-none'}}">
      <div class="position-relative d-flex align-items-center">
         @if($canView)
            <input type="number" name="components[{{$rowCount}}][overhead_amount]" readonly class="form-control mw-100 text-end" style="width: 70px" step="any" />
            <div class="ms-50">
               <button type="button" class="btn p-10 btn-sm btn-outline-secondary addOverHeadItemBtn" style="font-size: 8px" data-row-count="{{$rowCount}}"><i data-feather="plus"></i></button>
            </div>
         @else
         <input type="text"  name="components[{{$rowCount}}][overhead_amnt_dummy]" value="0.00" disabled class="form-control mw-100 text-end" style="width: 70px" step="any" />
         <input type="hidden" name="components[{{$rowCount}}][overhead_amount]" readonly class="form-control mw-100 text-end" style="width: 70px" step="any" />
         @endif
      </div>
   </td>
   @endif
   <td class="{{$canView ? '' : 'd-none'}}">
      @if($canView)
         <input type="text" name="components[{{$rowCount}}][item_total_cost]" readonly class="form-control mw-100 text-end" />
      @else
         <input type="hidden" name="components[{{$rowCount}}][item_total_cost]" readonly class="form-control mw-100 text-end" />
         <input type="text" name="components[{{$rowCount}}][total_cos_dummy]" value="0.00" disabled class="form-control mw-100 text-end" step="any" />
      @endif
   </td>
   @if(isset($stationRequired) && $stationRequired)
   <td>
      <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="product_station" />
      <input type="hidden" name="components[{{$rowCount}}][station_id]">
         <input type="hidden" name="components[{{$rowCount}}][station_name]">
   </td>
   @endif
   <td>
      <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="product_vendor" />
      <input type="hidden" name="components[{{$rowCount}}][vendor_id]">
   </td>

   @if(isset($bacthInheritRequird) && $bacthInheritRequird)
      <td id="td_bacth_inherit_requird">
         <div class="form-check form-check-primary custom-checkbox">
            <input type="checkbox" class="form-check-input is_inherit_batch_item" id="is_inherit_batch_item" name="components[{{$rowCount}}][is_inherit_batch_item]">
            <label class="form-check-label" for="is_inherit_batch_item"></label>
         </div>
      </td>
   @endif

   <td>
      <div class="d-flex align-items-center justify-content-center">
      <input type="hidden" name="components[{{$rowCount}}][remark]" />
         <div class="cursor-pointer addRemarkBtn" style="margin-right: 0.250rem;" data-row-count="{{$rowCount}}" {{-- data-bs-toggle="modal" data-bs-target="#Remarks" --}}>       
            <i data-feather="file-text"></i>
            </div>
         <div class="cursor-pointer linkAppend d-none">
            <a href="" target="_blank" class="">
               <i data-feather="link"></i>
            </a>
         </div>
      </div>
   </td>
</tr>