<tr id="row_{{$rowCount}}" data-index="{{$rowCount}}">
    <td class="customernewsection-form">
       <div class="form-check form-check-primary custom-checkbox">
          <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" value="{{$rowCount}}" data-id="">
          <label class="form-check-label" for="Email_{{$rowCount}}"></label>
       </div>
    </td>
    <td>
        <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="instruction_station" />
        <input type="hidden" name="instructions[{{$rowCount}}][station_id]">
        <input type="hidden" name="instructions[{{$rowCount}}][station_name]">
     </td>
     @if(isset($sectionRequired) && $sectionRequired)
    <td>
        <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="instruction_section" />
        <input type="hidden" name="instructions[{{$rowCount}}][section_id]">
           <input type="hidden" name="instructions[{{$rowCount}}][section_name]">
     </td>
     @endif
     @if(isset($subSectionRequired) && $subSectionRequired)
    <td>
        <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="instruction_sub_section" />
        <input type="hidden" name="instructions[{{$rowCount}}][sub_section_id]">
           <input type="hidden" name="instructions[{{$rowCount}}][sub_section_name]">
     </td>
     @endif
    <td>
        <textarea class="form-control mw-100" rows="1" name="instructions[{{$rowCount}}][instructions]"></textarea>
    </td>
   <td>
      <div class="d-flex align-items-center justify-content-center w-100">
         <i data-feather="upload" onclick="document.getElementById('file_input_{{$rowCount}}').click();" class="me-50"> </i>
         <input type="file" name="instructions[{{$rowCount}}][attachment][]"  class="d-none"  id="file_input_{{$rowCount}}"  onchange="addFiles(this, 'instruction_file_preview_{{$rowCount}}')" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"> 
         <div class="d-flex align-items-center file-preview-container"  id="instruction_file_preview_{{$rowCount}}">
         </div>
      </div>
   </td>
 </tr>