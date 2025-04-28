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
   <td><input type="number" name="components[{{$rowCount}}][item_cost]" class="form-control mw-100 text-end" step="any" /></td>
   @if(isset($supercedeCostRequired) && $supercedeCostRequired)
   <td>
      <input type="number" name="components[{{$rowCount}}][superceeded_cost]" class="form-control mw-100 text-end" step="any"/>
   </td>
   @endif
   <td>
      <input type="number" name="components[{{$rowCount}}][item_value]" class="form-control mw-100 text-end" readonly step="any" />
   </td>
   @if(isset($componentWasteRequired) && $componentWasteRequired)
   <td>
      <input type="number" name="components[{{$rowCount}}][waste_perc]" class="form-control mw-100 text-end" step="any" />
      {{-- <select class="form-select mw-100" name="components[{{$rowCount}}][waste_type]">
         @foreach($wasteTypes as $wasteType)
         <option value="{{$wasteType}}">{{$wasteType}}</option>
         @endforeach
      </select> --}}
   </td>
   <td>
      <input type="number" name="components[{{$rowCount}}][waste_amount]" class="form-control mw-100 text-end" step="any" />
   </td>
   @endif

   @if(isset($componentOverheadRequired) && $componentOverheadRequired)
   <td>
      <div class="position-relative d-flex align-items-center">
         <input type="number" name="components[{{$rowCount}}][overhead_amount]" readonly class="form-control mw-100 text-end" style="width: 70px" step="any" />
         <div class="ms-50">
            <button type="button" class="btn p-25 btn-sm btn-outline-secondary addOverHeadItemBtn" style="font-size: 10px" data-row-count="{{$rowCount}}">Add</button>
         </div>
      </div>
   </td>
   @endif
   <td>
      <input type="text" name="components[{{$rowCount}}][item_total_cost]" readonly class="form-control mw-100 text-end" />
   </td>
   @if(isset($stationRequired) && $stationRequired)
   <td>
      <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="product_station" />
      <input type="hidden" name="components[{{$rowCount}}][station_id]">
         <input type="hidden" name="components[{{$rowCount}}][station_name]">
   </td>
   @endif
   <td>
      <div class="d-flex align-items-center justify-content-center">
      <input type="hidden" name="components[{{$rowCount}}][remark]" />
         <div class="me-50 mx-1 cursor-pointer addRemarkBtn" data-row-count="{{$rowCount}}" {{-- data-bs-toggle="modal" data-bs-target="#Remarks" --}}>        <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" data-bs-original-title="Remarks" aria-label="Remarks"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></span></div>
         <div class="me-50 cursor-pointer linkAppend d-none">
            <a href="" target="_blank" class="">
            <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" data-bs-original-title="Link" aria-label="Link">
               <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-link">
                     <path d="M10 13a5 5 0 0 1 7 7l-1.5 1.5a5 5 0 0 1-7-7"></path>
                     <path d="M14 11a5 5 0 0 0-7-7l-1.5 1.5a5 5 0 0 0 7 7"></path>
               </svg>
            </span>
            </a>
         </div>
      </div>
   </td>
</tr>