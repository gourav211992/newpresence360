
{{-- Remarks Modal --}}

<div class="modal fade" id="Remarks" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" >
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Add/Edit Remarks</h1>
                <p class="text-center">Enter the details below.</p>
                    <div class="row mt-2">
                    <div class="col-md-12 mb-1">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" current-item = "item_remarks_0" onchange = "changeItemRemarks(this);" id ="current_item_remarks_input" placeholder="Enter Remarks"></textarea>
                    </div> 
                </div>
            </div>
            <div class="modal-footer justify-content-center">  
                    <button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('Remarks');">Cancel</button> 
                <button type="button" class="btn btn-primary" onclick="closeModal('Remarks');">Submit</button>
            </div>
        </div>
    </div>
</div>

{{-- Amend Modal --}}


<div class="modal fade" id="amendConfirmPopup" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Amend Pick List
                    </h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <input type="hidden" name="action_type" id="action_type_main">
            </div>
            <div class="modal-body pb-2">
                <div class="row mt-1">
                    <div class="col-md-12">
                        <div class="mb-1">
                            <label class="form-label">Remarks</label>
                            <textarea name="amend_remarks" class="form-control cannot_disable"></textarea>
                        </div>
                        <div class = "row">
                            <div class = "col-md-8">
                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input name = "amend_attachments[]" onchange = "addFiles(this, 'amend_files_preview')" type="file" class="form-control cannot_disable" max_file_count = "2" multiple/>
                                </div>
                            </div>
                            <div class = "col-md-4" style = "margin-top:19px;">
                                <div class="row" id = "amend_files_preview">
                                </div>
                            </div>
                        </div>
                        <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">  
                <button type="button" class="btn btn-outline-secondary me-1" onclick = "closeModal('amendConfirmPopup');">Cancel</button> 
                <button type="button" class="btn btn-primary" onclick = "submitAmend();">Submit</button>
            </div>
        </div>
    </div>
</div>

{{-- Approval Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="ajax-submit-2" method="POST" action="{{ route('document.approval.pickList') }}" data-redirect="{{ $redirect_url }}" enctype='multipart/form-data'>
                @csrf
                <input type="hidden" name="action_type" id="action_type">
                <input type="hidden" name="id" value="{{isset($order) ? $order -> id : ''}}">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="approve_reject_heading_label">
                        </h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">
                    <div class="row mt-1">
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control cannot_disable"></textarea>
                            </div>
                            <div class="row">
                                <div class = "col-md-8">
                                    <div class="mb-1">
                                        <label class="form-label">Upload Document</label>
                                        <input type="file" name = "attachments[]" multiple class="form-control cannot_disable" onchange = "addFiles(this, 'approval_files_preview');" max_file_count = "2"/>
                                    </div>
                                </div>
                                <div class = "col-md-4" style = "margin-top:19px;">
                                    <div class = "row" id = "approval_files_preview">
                                    </div>
                                </div>
                            </div>
                            <span class = "text-primary small">{{__("message.attachment_caption")}}</span>        
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">  
                    <button type="reset" class="btn btn-outline-secondary me-1" onclick = "closeModal('approveModal');">Cancel</button> 
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Amend Confirm Modal --}}
<div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body alertmsg text-center warning">
                <i data-feather='alert-circle'></i>
                <h2>Are you sure?</h2>
                <p>Are you sure you want to <strong>Amend</strong> this <strong>Pick List</strong>?</p>
                <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                <button type="button" data-bs-dismiss="modal" onclick = "amendConfirm();" class="btn btn-primary">Confirm</button>
            </div> 
        </div>
    </div>
</div>
