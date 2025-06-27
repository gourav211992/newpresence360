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
    <input type="hidden" name="components[{{$rowCount}}][hsn_id]"/> 
    <input type="hidden" name="components[{{$rowCount}}][hsn_code]"/> 
</td>
<td>
    <input type="text" name="components[{{$rowCount}}][item_name]" class="form-control mw-100 mb-25" readonly/>
</td>
<td class="poprod-decpt attributeBtn" id="itemAttribute_{{$rowCount}}" data-count="{{$rowCount}}" attribute-array=""> 
    <button type="button" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
</td>
<td>
    <select class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
    </select>
</td>
<td>
    <input @readonly(true) type="number" step="any" class="form-control mw-100 text-end"  name="components[{{$rowCount}}][qty]">
</td>
<td>
    <input type="number" step="any" class="form-control mw-100 text-end disabled-input"  name="components[{{$rowCount}}][avl_stock]">
</td>
<td>
    <input type="number" step="any" class="form-control mw-100 text-end disabled-input"  name="components[{{$rowCount}}][pending_po]">
</td>
<td>
    <input type="number" step="any" class="form-control mw-100 text-end"  name="components[{{$rowCount}}][adj_qty]">
</td>
<td>
    <input @readonly(true) type="number" step="any" class="form-control mw-100 text-end"  name="components[{{$rowCount}}][indent_qty]">
</td>
<td>
    <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="components[{{$rowCount}}][vendor_code]" />
    <input type="hidden" name="components[{{$rowCount}}][vendor_id]" />
</td>
@if(isset($soTrackingRequired) && $soTrackingRequired)
<td>
    <input readonly type="text" name="components[{{$rowCount}}][so_no]" class="form-control mw-100 mb-25" value="{{ isset($so) && $so ? $so->full_document_number : ''}}" />
</td>
@endif
<td>
    <input type="text" name="components[{{$rowCount}}][remark]" class="form-control mw-100 mb-25"/>
</td>
</td>
</tr>