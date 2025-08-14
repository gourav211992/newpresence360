<div class="modal fade text-start profilenew-modal" id="needtoknow" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px !important">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Add New</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="location.reload();"></button>
            </div>
            <div class="modal-body" style="max-height: 450px; overflow-y: scroll;">
                <form class="form" role="post-data" method="POST" action="{{ route('notes.store-answer') }}" autocomplete="off">
                @csrf
                <div class="row">
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}"/>
                    <input type="hidden" name="customer_code" value="{{ $customer->customer_code }}"/>
                    <input type="hidden" name="organization_id" value="{{ $customer->organization_id }}"/>
                    @forelse ($questions as $question)
                    <div class="col-12">
                        <div class="mb-1">
                            <label class="form-label">{{ $question->question }}</label>
                            <textarea class="form-control" name="feedback[{{ $question->id }}]">{{ isset($question->feedback->feedback) ? $question->feedback->feedback : '' }}</textarea>
                        </div>
                    </div>
                    @empty
                        
                    @endforelse
                    <div class="col-12 mb-1 mt-1"> 
                        <button type="button" class="btn btn-primary data-submit" data-request="ajax-submit" data-target="[role=post-data]">Submit</button>
                    </div>                        
                </div>
                </form>
            </div>
            
        </div>
    </div>
</div>