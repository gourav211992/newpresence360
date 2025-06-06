@foreach($bom->moProducts as $key => $moProduct)
@php
   $rowCount = $key + 1;
@endphp
<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}">
   <td class="customernewsection-form">
      <div class="form-check form-check-primary custom-checkbox">
         <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" value="{{$rowCount}}" data-id="{{$moProduct->id}}">
         <label class="form-check-label" for="Email_{{$rowCount}}"></label>
      </div>
   </td>
   <td>
      <input type="text" name="components[{{$rowCount}}][pwo_book_code]" value="{{$moProduct?->pwoMapping?->pwo?->book_code ?? ''}}" class="form-control mw-100 mb-25" readonly/>
   </td>
   <td>
      <input type="text" name="components[{{$rowCount}}][pwo_doc_no]" value="{{$moProduct?->pwoMapping?->pwo?->document_number ?? ''}}" class="form-control mw-100 mb-25" readonly/>
   </td>
   <td>
      <input type="text" name="components[{{$rowCount}}][pwo_doc_date]" value="{{$moProduct?->pwoMapping?->pwo?->getFormattedDate('document_date')  ?? ''}}" class="form-control mw-100 mb-25" readonly/>
   </td>
   <td>
      <input type="text" name="components[{{$rowCount}}][location_id]" value="{{$moProduct?->pwoMapping?->pwo?->location?->store_name  ?? ''}}" class="form-control mw-100 mb-25" readonly/>
   </td>
   <td class="poprod-decpt"> 
      <input type="text" readonly value="{{$moProduct->item_code}}" name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code " />
      <input type="hidden" name="components[{{$rowCount}}][item_id]" value="{{$moProduct->item_id}}"/>
      <input type="hidden" name="components[{{$rowCount}}][item_code]" value="{{$moProduct->item_code}}"/>

      @php
      $selectedAttr = $moProduct->attributes ? $moProduct->attributes()->pluck('attribute_value')->all() : []; 
      @endphp
      @foreach($moProduct->attributes as $attributeHidden)
         <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$attributeHidden->attribute_name}}][attr_id]" value="{{$attributeHidden->id}}">
      @endforeach
      @foreach($moProduct->item?->itemAttributes as $itemAttribute)
         @foreach ($itemAttribute->attributes() as $value)
            @if(in_array($value->id, $selectedAttr))
            <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="{{$value->id}}">
            @endif
         @endforeach
      @endforeach
  </td>
  <td>
      <input type="text" name="components[{{$rowCount}}][item_name]" value="{{$moProduct?->item?->item_name}}" class="form-control mw-100 mb-25" readonly/>
  </td>
   <td class="poprod-decpt" id="itemAttribute_{{$rowCount}}" data-count="{{$rowCount}}" attribute-array="{{$moProduct->item_attributes_array()}}">
       
   </td>
   <td>
      <select disabled class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
         <option value="{{$moProduct->uom_id}}">{{$moProduct?->uom?->name}}</option>
      </select>
   </td>
   <td>
      <input disabled type="text" value="{{number_format($moProduct->qty,4)}}" step="any" class="form-control mw-100 text-end"  name="components[{{$rowCount}}][qty]"/>
   </td>
   <td>
      <input type="hidden" name="components[{{$rowCount}}][customer_id]" value="{{$moProduct->customer_id}}" />
      <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" value="{{$moProduct?->customer?->company_name}}" name="components[{{$rowCount}}][customer_code]" />
   </td>
   {{-- <td></td> --}}
   <td>
      <input type="text" name="components[{{$rowCount}}][so_code]" value="{{$moProduct?->pwoMapping?->soItem?->header?->full_document_number ?? ''}}" class="form-control mw-100 mb-25" readonly/>
   </td>
   @if($moProduct?->mo?->machine_id)
   <td>
      <select class="form-select" name="components[{{$rowCount}}][machine_id]">
         <option value="">Select Machine</option>
         @foreach($machines as $machine)
         <option value="{{$machine->id}}" {{$moProduct->machine_id == $machine->id ? 'selected' : ''}}>{{$machine?->name}}</option>
         @endforeach
      </select>
   </td>
   <td>
      <input type="number" step="any" class="form-control mw-100 text-end" name="components[{{$rowCount}}][sheet]" value="{{$moProduct->number_of_sheet}}" readonly/>
   </td>
   @endif
   <td>
      <div class="d-flex align-items-center justify-content-center">
      <input type="hidden" name="components[{{$rowCount}}][remark]" value="{{$moProduct->remark}}"/>
         <div class="me-50 mx-1 cursor-pointer addRemarkBtn" data-row-count="{{$rowCount}}"><span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" data-bs-original-title="Remarks" aria-label="Remarks"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></span></div>
      </div>
   </td>
   <input type="hidden" name="components[{{$rowCount}}][pwo_mapping_id]" value="{{$moProduct->pwo_mapping_id}}">
   <input type="hidden" name="components[{{$rowCount}}][mo_product_id]" value="{{$moProduct->id}}">
</tr>
@endforeach