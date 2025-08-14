<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form class="ajax-input-form" method="POST" action="{{ route('document.approval.inspection') }}" data-redirect="{{ route('inspection.index') }}" enctype='multipart/form-data'>
          @csrf
          <input type="hidden" name="action_type" id="action_type">
          <input type="hidden" name="id" value="{{$id ?? ''}}">
         <div class="modal-header">
            <div>
               <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="popupTitle">Approve Application</h4>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body pb-2">
            <div class="row mt-1">
               <div class="col-md-12">
                  <div class="mb-2">
                     <label class="form-label">Remarks {{-- <span class="text-danger">*</span> --}}</label>
                     <textarea maxlength="250" name="remarks" class="form-control"></textarea>
                  </div>
                  <div class="mb-2">
                     <label class="form-label">Upload Document</label>
                     <input id="attachments" type="file" name="attachment[]" class="form-control" onchange="addFiles(this, 'mrn_popup_file_preview')" multiple>
                     <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                  </div>
                  <div class="mt-2">
                      <div class="row" id="mrn_popup_file_preview">
                      </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="modal-footer justify-content-center">
            <button type="reset" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button>
            <button type="submit" class="btn btn-primary">Submit</button>
         </div>
       </form>
      </div>
   </div>
</div>
