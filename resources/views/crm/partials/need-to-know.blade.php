<div class="card overtimechart">
    <div class="card-header newheader d-flex justify-content-between align-items-start">
        <div class="header-left">
            <h4 class="card-title">Need to Know’s</h4>
        </div>
        <div class="dropdown d-flex align-items-center">
            <div class="newcolortheme cursor-pointer"  data-bs-toggle="modal" data-bs-target="#needtoknow" > 
                <i data-feather='file-text' class="me-25"></i> Add New
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="accordion accordion-margin needtoknowquest" id="accordionMargin" style="max-height: 270px; overflow-y: scroll;">
            @forelse ($feedbacks as $feedback)
                <div class="accordion-item border">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed text-dark font-small-4"
                            type="button" data-bs-toggle="collapse" data-bs-target="#modulename{{ $feedback->id }}">
                            {{ isset($feedback->question->question) ? $feedback->question->question : '' }}
                        </button>
                    </h2>
                    <div id="modulename{{ $feedback->id }}" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <p>{{$feedback->feedback}}</p>
                        </div>
                    </div>
                </div>
                
            @empty
                <span class="text-danger"> No record(s) found.</span>
            @endforelse
            
        </div>


    </div>
</div>