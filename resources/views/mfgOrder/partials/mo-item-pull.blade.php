@foreach($pwoItems as $key => $pwoItem)
@php
   // $rowCount = $rowCount + $key;
   $rowCount = $key + 1;
@endphp
<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}">
   <td class="customernewsection-form">
      <div class="form-check form-check-primary custom-checkbox">
         <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" name="components[{{$rowCount}}][selected]" value="{{$rowCount}}">
         <label class="form-check-label" for="Email_{{$rowCount}}"></label>
      </div>
   </td>
   <td>
      <input type="text" name="components[{{$rowCount}}][pwo_book_code]" value="{{$pwoItem->pwo?->book_code ?? ''}}" class="form-control mw-100 mb-25" readonly/>
   </td>
   <td>
      <input type="text" name="components[{{$rowCount}}][pwo_doc_no]" value="{{$pwoItem->pwo?->document_number ?? ''}}" class="form-control mw-100 mb-25" readonly/>
   </td>
   <td>
      <input type="text" name="components[{{$rowCount}}][pwo_doc_date]" value="{{$pwoItem->pwo?->getFormattedDate('document_date')  ?? ''}}" class="form-control mw-100 mb-25" readonly/>
   </td>
   <td>
      <input type="text" name="components[{{$rowCount}}][location_id]" value="{{$pwoItem->pwo?->location?->store_name  ?? ''}}" class="form-control mw-100 mb-25" readonly/>
   </td>
   <td class="poprod-decpt">
      <input readonly type="text" name="component_item_name[{{$rowCount}}]" value="{{$pwoItem?->item?->item_code}}" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code " />
      <input type="hidden" name="components[{{$rowCount}}][item_id]" value="{{$pwoItem->item_id}}"/>
      <input type="hidden" name="components[{{$rowCount}}][item_code]" value="{{$pwoItem?->item?->item_code}}"/>
      @php
        $selectedAttrValues = array_column($pwoItem->attributes, 'attribute_id');
      @endphp
      @foreach($pwoItem->item?->itemAttributes as $itemAttribute)
         @foreach ($itemAttribute->attributes() as $value)
            @if(in_array($value->id, $selectedAttrValues))
            <input type="hidden" name="components[{{$rowCount}}][attr_group_id][{{$itemAttribute->attribute_group_id}}][attr_name]" value="{{$value->id}}">
            @endif
         @endforeach
      @endforeach
   </td>
   <td>
      <input type="text" name="components[{{$rowCount}}][item_name]" value="{{$pwoItem?->item?->item_name}}" class="form-control mw-100 mb-25" readonly/>
   </td>
   <td class="poprod-decpt" id="itemAttribute_{{$rowCount}}" data-count="{{$rowCount}}" attribute-array="{{$pwoItem->item_attributes_array()}}">
   </td>
   <td>
      <select disabled class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
         <option value="{{$pwoItem->uom_id}}">{{$pwoItem?->uom?->name}}</option>
      </select>
   </td>
   <td>
      @php
         $allowedValue = $pwoItem->qty - $pwoItem->mo_product_qty;
      @endphp

      <input type="number" value="{{ $allowedValue }}" step="any" class="form-control mw-100 text-end" onkeyup="validateQty(this)" onblur="validateQty(this)" id="components_bom_qty_{{ $rowCount }}" name="components[{{ $rowCount }}][qty]" min="1" max="{{ $allowedValue}}"/>
   </td>
   <td>
      <input type="hidden" name="components[{{$rowCount}}][customer_id]" value="{{$pwoItem?->so?->customer_id}}" />
      <input readonly type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" value="{{$pwoItem?->so?->customer?->company_name}}" name="components[{{$rowCount}}][customer_code]" />
   </td>
   <td>
      <input type="text" name="components[{{$rowCount}}][so_code]" value="{{ $pwoItem?->so ? strtoupper($pwoItem?->so?->book_code).' - '.$pwoItem?->so?->document_number : ''}}" class="form-control mw-100 mb-25" readonly/>
   </td>
   @if($machines->count() > 0)
   <td>
      <select class="form-select" name="components[{{$rowCount}}][machine_id]">
         <option value="">Select Machine</option>
         @foreach($machines as $machine)
         <option value="{{$machine->id}}">{{$machine?->name}}</option>
         @endforeach
      </select>
   </td>
   <td>
      <input type="number" step="any" class="form-control mw-100 text-end" name="components[{{$rowCount}}][sheet]" value="" readonly/>
   </td>
   @endif
   <td>
      <div class="d-flex align-items-center justify-content-center">
      <input type="hidden" name="components[{{$rowCount}}][remark]" value="{{$pwoItem->remark}}"/>
         <div class="me-50 mx-1 cursor-pointer addRemarkBtn" data-row-count="{{$rowCount}}"><span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" data-bs-original-title="Remarks" aria-label="Remarks"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></span></div>
      </div>
   </td>
   <input type="hidden" name="components[{{$rowCount}}][pwo_mapping_id]" id="pwo_mapping_id_{{$rowCount}}" value="{{$pwoItem->id}}">
   <input type="hidden" name="components[{{$rowCount}}][so_id]" value="{{$pwoItem->so_id}}">
   <input type="hidden" name="components[{{$rowCount}}][so_item_id]" value="{{$pwoItem->so_item_id}}">
</tr>
@endforeach
