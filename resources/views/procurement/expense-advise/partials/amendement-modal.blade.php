<div class="modal fade" id="amendmentModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
          <input type="hidden" name="action_type" value="amendment" id="action_type">
          <input type="hidden" name="id" value="{{$id ?? ''}}">
         <div class="modal-header">
            <div>
               <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="popupTitle">Amendment Application</h4>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body pb-2">
            <div class="row mt-1">
               <div class="col-md-12">
                  <div class="mb-2">
                     <label class="form-label">Remarks {{-- <span class="text-danger">*</span> --}}</label>
                     <textarea maxlength="250" name="amend_remarks" class="form-control"></textarea>
                     <span id="amendRemarkError"  class="ajax-validation-error-span form-label text-danger d-none" style="font-size:12px" role="alert">*Required</span>
                  </div>
                  <div class="mb-2">
                     <label class="form-label">Upload Document</label>
                     <input type="file" onchange="addFiles(this, 'expense_popup_file_preview')" name="amend_attachment[]" multiple class="form-control" />
                     <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                  </div>
                  <div class="mt-2">
                      <div class="row" id="expense_popup_file_preview">
                      </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="modal-footer justify-content-center">  
            <button type="reset" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button> 
            <button type="button" id="amendmentBtnSubmit" class="btn btn-primary">Submit</button>
         </div>
      </div>
   </div>
</div>