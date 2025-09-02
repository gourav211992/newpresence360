<div class="modal fade" id="cancelEInvoiceModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content">
          <form class="ajax-input-form" method="POST" action="{{ route('purchase-return.cancel.e-invoice') }}" enctype='multipart/form-data'>
             @csrf
             <input type="hidden" name="action_type" id="action_type">
             <input type="hidden" name="id" value="{{$id ?? ''}}">
             <input type="hidden" name="irn_id" value="{{$mrnData->id ?? ''}}">
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
                         <label class="form-label">Irn Number <span class="text-success">{{ $irnData->irn_number }}</span></label>
                      </div>
                      <div class="mb-2">
                         <label class="form-label">Remarks {{-- <span class="text-danger">*</span> --}}</label>
                         <textarea maxlength="250" name="remarks" class="form-control"></textarea>
                      </div>
                      <div class="mt-2">
                         <div class="row" id="pr_popup_file_preview">
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
