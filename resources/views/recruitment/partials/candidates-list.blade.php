<div class="row">
    <div class="col-md-5">
        @forelse($candidates as $key => $candidate)
            <a 
                @if(in_array($status, ['scheduled','selected']))
                    onclick="fetchCandidateInterviewDetail({{ $candidate->id }}, {{ $jobId }}, '{{ $status }}')"
                @else
                    onclick="fetchCandidateDetail({{ $candidate->id }}, {{ $jobId }})"
                @endif
            >
            <div class="employee-boxnew package-section" id="candSec{{ $candidate->id }}">
                <div class="row align-items-center">
                    <div class="col-3">
                        <div class="position-relative" style="background-color: #ddb6ff; color: #6b12b7; text-align: center; line-height: 65px; width: 65px; height: 65px; border-radius: 50%; font-size: large; font-weight: bold;">
                            {{ strtoupper(substr($candidate->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="col-9 pl-0">
                        <h5>{{ $candidate->name }}</h5>
                        <h6><i data-feather='phone'></i> {{ $candidate->mobile_no }}</h6>
                        <h6><i data-feather='mail'></i> {{ $candidate->email }}</h6>
                        @if(isset($candidate->scheduledInterview->round->name))
                            <span class="badge rounded-pill badge-light-warning font-small-2 fw-bolder">{{ $candidate->scheduledInterview->round->name }}</span>
                        @endif
                    </div> 
                </div> 
            </div>
        </a>
        @empty
        
        @endforelse
    </div>
    <div class="col-md-7" id="emp-detail-div"></div>                                                
</div>

@if(count($candidates) < 1)
<div class="row">
    <div class="col-md-12">
        <div class="employee-boxnew p-1" id="candSec">
            <div class="card">
                <div class="card-body emplodetainfocard pb-0">
                    <div class="employee-box text-center border-0 my-4">
                        @if($status == 'assigned')
                            <h2>No Candidate Assign</h2>
                        @else
                            <h2>No Candidate(s) Found</h2>
                        @endif
                    </div>
                    @if($page == 'job-view-hr')
                        <div class="empl-detail-info mb-5">
                            <div class="row">
                                <div class="col-md-12 mt-2 text-center">
                                    @if($status == 'assigned')
                                        <a href="{{ route('recruitment.jobs.candidates',['id' => $jobId]) }}"
                                            class="btn btn-primary btn-primary-new me-1  mb-50 mb-sm-0"><i
                                                data-feather="check-circle"></i>
                                            Assign Candidate </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif