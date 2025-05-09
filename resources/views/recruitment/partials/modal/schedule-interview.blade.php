<div class="modal fade" id="schedule-interview-modal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form role="scheduled-interview" method="POST" id="status-form" action="{{ route('recruitment.jobs-interviews.scheduled',['jobId' => $job->id]) }}" redirect="{{route('recruitment.jobs.show',['id' => $job->id]) }}">
            <div class="modal-body px-sm-4 mx-50 pb-2">
                <h1 class="text-center" id="shareProjectTitle">Scheduled Interview for Candidate</h1>
                <p class="text-center">Enter the details below.</p>
                <input type="hidden" name="candidate_id" id="interview-candidate-id"/>
                <div class="row mt-2"> 
                    <div class="col-md-12 mb-1">
                        <label class="form-label" for="select2-basic">Round <span class="text-danger">*</span></label>
                        <select class="form-select" name="round_id">
                            <option value="">Select</option>
                            @forelse ($rounds as $round)
                                <option value="{{ $round->id }}">{{ $round->name }}</option>
                            @empty
                            @endforelse
                        </select> 
                    </div> 
                    
                    <div class="col-md-12 mb-1">
                        <label class="form-label">Interview Date & Time</label>
                        <input type="datetime-local" class="form-control" placeholder="Enter Name" name="date_time" /> 
                    </div>
                    
                    <div class="col-md-12 mb-1">
                        <label class="form-label">Meeting Link <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="Enter Meeting Link" name="meeting_link"/>
                    </div>
                    
                    <div class="col-md-12 mb-1">
                        <label class="form-label">Remarks <span class="text-danger">*</span></label>
                        <textarea class="form-control" placeholder="Enter Remarks" name="remarks"></textarea>
                    </div>
                        
                </div>
            </div>
            
            <div class="modal-footer justify-content-center">  
                <button type="reset" class="btn btn-outline-primary me-1">Cancel</button> 
                <button type="button" class="btn btn-primary" data-request="ajax-submit" data-target="[role=scheduled-interview]">Submit</button>
            </div>
            </form>
        </div>
    </div>
</div>