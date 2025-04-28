<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}">
    <td class="customernewsection-form">
       <div class="form-check form-check-primary custom-checkbox">
          <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" value="{{$rowCount}}" data-id="">
          <label class="form-check-label" for="Email_{{$rowCount}}"></label>
       </div>
    </td>
    <td>
        <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="product_station" />
        <input type="hidden" name="productions[{{$rowCount}}][station_id]">
           <input type="hidden" name="productions[{{$rowCount}}][station_name]">
     </td>
    <td>
       <input type="text" name="prod_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code" />
       {{-- <input type="text" name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code" /> --}}
       <input type="hidden" name="productions[{{$rowCount}}][item_id]"/>
       <input type="hidden" name="productions[{{$rowCount}}][item_code]"/>
    </td>
    <td>
       <input type="text" readonly name="product_name[{{$rowCount}}]" class="form-control mw-100 ledgerselecct" />
    </td>
    <td class="poprod-decpt"> 
       <button type="button" class="btn p-25 btn-sm btn-outline-secondary attributeBtn" data-row-count="{{$rowCount}}" style="font-size: 10px">Attributes</button>
    </td>
    <td>
       <select class="form-select mw-100 " name="productions[{{$rowCount}}][uom_id]">
       </select>
    </td>
    <td>
        <input type="number" step="any" class="form-control mw-100 text-end"  name="productions[{{$rowCount}}][qty]" value="1"/>
    </td>
 </tr>