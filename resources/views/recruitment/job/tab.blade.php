<ul class="nav nav-tabs border-bottom" role="tablist">
    <li class="nav-item">
        <a class="nav-link {{ \Request::route()->getName() == 'recruitment.jobs' ? 'active' : '' }}" href="{{ route('recruitment.jobs') }}">Created Job
            &nbsp;<span>({{ $jobCount }})</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ \Request::route()->getName() == 'recruitment.jobs.assigned-candidate' ? 'active' : '' }}" href="{{ route('recruitment.jobs.assigned-candidate') }}">Candidate Assigned
            &nbsp;<span>({{ $candidatesCount }})</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ \Request::route()->getName() == 'recruitment.jobs.interview-scheduled' ? 'active' : '' }}" href="{{ route('recruitment.jobs.interview-scheduled') }}">Interview Scheduled
            &nbsp;<span>({{ $interviewCount }})</span></a>
    </li>
</ul>