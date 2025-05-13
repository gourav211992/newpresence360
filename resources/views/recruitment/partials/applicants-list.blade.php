@forelse($applicants as $applicant)
    <div class="employee-task d-flex justify-content-between align-items-center">
        <div class="d-flex flex-row">
            <div
                style="background-color: #ddb6ff; color: #6b12b7; line-height: 30px; width: 30px; height: 30px; border-radius: 50%; position: relative; font-size: 1rem; text-align: center; margin-right: 5px; font-weight: 600;">
                {{ strtoupper(substr($applicant->name, 0, 1)) }}
            </div>
            <div class="my-auto text-dark">
                <h6 class="mb-0 fw-bolder text-dark">
                    {{ $applicant->name }}</h6>
                <small>Applied for
                    <strong>{{ $applicant->jobDetail->job_title_name }}</strong></small>
            </div>
        </div>
    </div>
@empty
    <div class="employee-task d-flex justify-content-between align-items-center">
        <h5>No data found</h5>
    </div>
@endforelse
