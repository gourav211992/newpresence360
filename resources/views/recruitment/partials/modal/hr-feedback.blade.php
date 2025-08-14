<div class="modal fade" id="feedback" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form role="feedback" method="POST" id="status-form" action="{{ route('recruitment.jobs-interviews.hr-feedback') }}" reload="true">
                <div class="modal-body px-sm-4 mx-50 pb-2">
                    <h1 class="text-center" id="shareProjectTitle">Feedback for Candidate</h1>
                    <p class="text-center">Enter the details below.</p>

                    <div class="row mt-2"> 
                        <input type="hidden" id="interview-id" name="job_interview_id"/>
                        
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Feedback <span class="text-danger">*</span></label>
                            <div class="demo-inline-spacing">
                                <div class="form-check form-check-primary mt-25">
                                    <input type="radio" id="Next" class="form-check-input" checked="" name="status" value="{{ App\Helpers\CommonHelper::SELECTED }}">
                                    <label class="form-check-label fw-bolder" for="Next">Selected</label>
                                </div> 
                                <div class="form-check form-check-primary mt-25">
                                    <input type="radio" id="Reject" class="form-check-input" name="status" value="{{ App\Helpers\CommonHelper::REJECTED }}">
                                    <label class="form-check-label fw-bolder" for="Reject">Reject</label>
                                </div> 
                                
                                <div class="form-check form-check-primary mt-25">
                                    <input type="radio" id="Holdnew" class="form-check-input" name="status" value="{{ App\Helpers\CommonHelper::ONHOLD }}">
                                    <label class="form-check-label fw-bolder" for="Holdnew">On Hold</label>
                                </div> 
                            </div> 
                        </div>

                        <div class="col-md-12 mb-1">
                            <label class="form-label">Attachment </label>
                            <input type="file" class="form-control" placeholder="Enter Name" name="attachment"/> 
                        </div>
                        
                        <div class="col-md-12 mb-1">
                            <label class="form-label">Remarks <span class="text-danger">*</span></label>
                            <textarea class="form-control" placeholder="Enter Feedback for Candidate" name="remarks"></textarea>
                        </div>
                            
                    </div>
                </div>
                
                <div class="modal-footer justify-content-center">  
                    <button type="reset" class="btn btn-outline-primary me-1">Cancel</button> 
                    <button type="button" class="btn btn-primary" data-request="ajax-submit" data-target="[role=feedback]">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>