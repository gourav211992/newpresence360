<div class="modal fade" id="status-modal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="status-modal-title"></h4>
                    <p class="mb-0 fw-bold voucehrinvocetxt mt-0" id="status-modal-description"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form role="post-data" method="POST" id="status-form" action="" redirect="{{route('recruitment.jobs.show',['id' => $job->id]) }}">
                <div class="modal-body pb-2">
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <input type="hidden" name="status" class="form-control" value="" id="status-input">
                                    <input type="hidden" name="candidate_id" class="form-control" value="" id="candidate-id-input">
                                    <input type="hidden" name="job_id" class="form-control" value="{{ $job->id }}" id="job-id-input">
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="log_message"></textarea>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        <button type="button" class="btn btn-primary" data-request="ajax-submit" data-target="[role=post-data]">Submit</button>
                    </div>
            </form>
        </div>
    </div>
</div>