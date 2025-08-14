<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}">
   <td class="customernewsection-form">
      <div class="form-check form-check-primary custom-checkbox">
         <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" value="{{$rowCount}}" data-id="">
         <label class="form-check-label" for="Email_{{$rowCount}}"></label>
      </div>
   </td>
   <td class="poprod-decpt"> 
      <input type="text" name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code " />
      <input type="hidden" name="components[{{$rowCount}}][item_id]"/>
      <input type="hidden" name="components[{{$rowCount}}][item_code]"/>
  </td>
  <td>
      <input type="text" name="components[{{$rowCount}}][item_name]" class="form-control mw-100 mb-25" readonly/>
  </td>
   <td class="poprod-decpt"> 
      <button type="button" class="btn p-25 btn-sm btn-outline-secondary attributeBtn" data-row-count="{{$rowCount}}" style="font-size: 10px">Attributes</button>
   </td>
   <td>
      <select disabled class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
         
      </select>
   </td>
   <td>
      <input type="number" step="any" class="form-control mw-100 text-end"  name="components[{{$rowCount}}][qty]"/>
   </td>
   <td>
      <input type="hidden" name="components[{{$rowCount}}][customer_id]" />
      <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="components[{{$rowCount}}][customer_code]" />
   </td>
   {{-- <td></td> --}}
   <td></td>
   <td>
      <div class="d-flex align-items-center justify-content-center">
      <input type="hidden" name="components[{{$rowCount}}][remark]" />
         <div class="me-50 mx-1 cursor-pointer addRemarkBtn" data-row-count="{{$rowCount}}"><span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" data-bs-original-title="Remarks" aria-label="Remarks"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></span></div>
      </div>
   </td>
</tr>