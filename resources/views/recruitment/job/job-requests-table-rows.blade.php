@forelse($jobRequests as $jobReq)
    <tr>
        <td class="pe-0">
            <div class="form-check form-check-inline me-0">
                @if ($jobId)
                    <input class="form-check-input row-checkbox" type="checkbox" checked>
                @else
                    <input class="form-check-input row-checkbox" type="checkbox" name="job_request[{{ $jobReq->id }}]"
                        value="{{ $jobReq->id }}">
                @endif
            </div>
        </td>
        <td class="text-nowrap">
            {{ $jobReq->created_at ? App\Helpers\CommonHelper::dateFormat($jobReq->created_at) : '' }}</td>
        <td class="fw-bolder text-dark">{{ $jobReq->request_id }}</td>
        <td>{{ $jobReq->job_type }}</td>
        <td>{{ $jobReq->education_name }}</td>
        <td>
            @php
                $skills = $jobReq->recruitmentSkills;
            @endphp
            @foreach ($skills->take(2) as $skill)
                <span class="badge rounded-pill badge-light-secondary badgeborder-radius">
                    {{ $skill->name }}
                </span>
            @endforeach

            @if ($skills->count() > 2)
                <a href="#" class="skilnum text-primary" data-bs-toggle="modal" data-bs-target="#skillModal"
                    data-skills='@json($skills->pluck('name'))'>
                    <span class="skilnum">+{{ $skills->count() - 2 }}</span>
                </a>
            @endif
        </td>
        <td>{{ $jobReq->work_experience_name }}</td>
        <td>
            <div class="d-flex flex-row">
                <div class="avatar me-75">
                    <img src="{{ asset('app-assets/images/portrait/small/avatar-s-9.jpg') }}" width="25"
                        height="25" alt="Avatar">
                </div>
                <div class="my-auto">
                    <h6 class="mb-0 fw-bolder text-dark hr-dashemplname">{{ $jobReq->creator_name }}</h6>
                </div>
            </div>
        </td>
        <td>{{ $jobReq->expected_doj ? App\Helpers\CommonHelper::dateFormat($jobReq->expected_doj) : '' }}</td>
        <td>
            <input type="hidden" class="no-of-position" value="{{ $jobReq->no_of_position }}">
            {{ $jobReq->no_of_position }}
        </td>
        <td class="clickopentr">
            <a href="#" class="open-job-sectab"><i data-feather='arrow-down-circle'></i></a>
            <a href="#" class="close-job-sectab text-danger" style="display: none"><i
                    data-feather='arrow-up-circle'></i></a>
        </td>
    </tr>

    <tr class="shojpbdescrp" style="display: none">
        <td class="pe-0"></td>
        <td colspan="11">
            <p><strong>Job Description: </strong> {{ $jobReq->job_description }}</p>
            <p><strong>Reason: </strong> {{ $jobReq->reason }}</p>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" class="text-danger text-center">No request found!</td>
    </tr>
@endforelse
