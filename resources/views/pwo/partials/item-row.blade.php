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
   <td class="poprod-decpt attributeBtn" id="itemAttribute_{{$rowCount}}" data-count="{{$rowCount}}" attribute-array=""> 
      <button type="button" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
   </td>
   <td>
      <select disabled class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
         
      </select>
   </td>
   <td>
      <input type="number" step="any" class="form-control mw-100 text-end"  name="components[{{$rowCount}}][qty]"/>
   </td>
   <td>
      {{-- <input type="hidden" name="components[{{$rowCount}}][customer_id]" />
      <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="components[{{$rowCount}}][customer_code]" /> --}}
   </td>
   <td></td>
   <td>
      <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" name="components[{{$rowCount}}][store_name]" value="{{$store?->store_name}}">
      <input type="hidden" name="components[{{$rowCount}}][store_id]" value="{{$store?->id}}">
   </td>
</tr>