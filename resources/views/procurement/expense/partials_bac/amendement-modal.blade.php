<div class="modal fade" id="amendementModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form class="ajax-input-form" method="POST" action="{{ route('document.amendement.expense') }}" data-redirect="{{ route('expense.index') }}" enctype='multipart/form-data'>
          @csrf
          <input type="hidden" name="action_type" id="amendement_type">
          <input type="hidden" name="id" value="{{$id ?? ''}}">
         <div class="modal-header">
            <div>
               <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Amendement</h4>
               <!-- <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | 29-07-2024</p> -->
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body pb-2">
            <div class="row mt-1">
               <div class="col-md-12">
                  <div class="mb-1">
                     <label class="form-label">Remarks <span class="text-danger">*</span></label>
                     <textarea name="remarks" class="form-control"></textarea>
                  </div>
                  <div class="mb-1">
                     <label class="form-label">Upload Document</label>
                     <input type="file" name="attachment[]" multiple class="form-control" />
                  </div>
               </div>
            </div>
         </div>
         <div class="modal-footer justify-content-center">  
            <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button> 
            <button type="submit" class="btn btn-primary" id="submit-button">Submit</button>
         </div>
       </form>
      </div>
   </div>
</div>