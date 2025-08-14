<ul class="nav nav-tabs border-bottom" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ \Request::route()->getName() == 'recruitment.requests' ? 'active' : '' }}" href="{{ route('recruitment.requests') }}">Requested &nbsp;<span>({{ $requestCount }})</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ \Request::route()->getName() == 'recruitment.requests.for-approval' ? 'active' : '' }}" href="{{ route('recruitment.requests.for-approval') }}">For Approval &nbsp;<span>({{ $requestForApprovalCount }})</span></a>
    </li>  
    <li class="nav-item">
        <a class="nav-link {{ \Request::route()->getName() == 'recruitment.requests.assigned-candidate' ? 'active' : '' }}" href="{{ route('recruitment.requests.assigned-candidate') }}">Candidate Assigned &nbsp;<span>({{ $candidateAssignedRequestCount }})</span></a>
    </li> 
    <li class="nav-item">
        <a class="nav-link {{ \Request::route()->getName() == 'recruitment.requests.interview-scheduled' ? 'active' : '' }}" href="{{ route('recruitment.requests.interview-scheduled') }}">Interview Scheduled &nbsp;<span>({{ $interviewScheduledCount }})</span></a>
    </li> 
</ul> 
