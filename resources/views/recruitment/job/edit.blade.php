@extends('layouts.app')
@section('style')
    <style>
        .note-editor.note-frame.card {
            border-radius: 0px;
        }
    </style>
@endsection
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <form class="form" role="post-data" method="POST" action="{{ url('recruitment/jobs/' . $job->id) }}"
                redirect="{{ route('recruitment.jobs') }}">
                @csrf
                @method('PUT')
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Create Job</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a
                                                    href="{{ route('recruitment.hr-dashboard') }}">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">Edit</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <a href="{{ route('recruitment.jobs') }}" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                        data-feather="arrow-left-circle"></i> Back</a>
                                <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-request="ajax-submit"
                                    data-target="[role=post-data]"><i data-feather="check-circle"></i> Create</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-body">
                    <section id="basic-datatable">
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body travelexp-form">

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="newheader  border-bottom mb-2 pb-25">
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>

                                            <div class="col-md-9">

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Select Job Title <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="job_title_id"
                                                            value="{{ $job->job_title_name }}" class="form-control"
                                                            readonly />
                                                        {{-- <select class="form-select select2" name="job_title_id" onchange="fetchJobRequests(this.value)">
                                                        <option value="" {{ $job->job_title_id == "" ? 'selected' : ''}}>Select</option>
                                                        @forelse($jobTitles as $title)
                                                            <option value="{{ $title->id }}" {{ $job->job_title_id == $title->id ? 'selected' : ''}}>{{ $title->title }}</option>
                                                        @empty
                                                        @endforelse
                                                    </select> --}}
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Status <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class="demo-inline-spacing  customernewsection-form">
                                                            <div class="form-check form-check-primary mt-25 mb-75">
                                                                <input type="radio" id="customColorRadio3" name="status"
                                                                    class="form-check-input"
                                                                    {{ $job->status == 'open' ? 'checked' : '' }}
                                                                    value="{{ App\Helpers\CommonHelper::OPEN }}">
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio3">Open</label>
                                                            </div>

                                                            <div class="form-check form-check-primary mt-25 mb-75">
                                                                <input type="radio" id="customColorRadio4" name="status"
                                                                    class="form-check-input"
                                                                    {{ $job->status == 'closed' ? 'checked' : '' }}
                                                                    value="{{ App\Helpers\CommonHelper::CLOSED }}">
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio4">Closed</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Third Party Assessment <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class="demo-inline-spacing  customernewsection-form">
                                                            <div class="form-check form-check-primary mt-25 mb-75">
                                                                <input type="radio" id="customColorRadio91"
                                                                    name="third_party_assessment" class="form-check-input"
                                                                    value="yes"
                                                                    {{ $job->third_party_assessment == 'yes' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio91">Yes</label>
                                                            </div>

                                                            <div class="form-check form-check-primary mt-25 mb-75">
                                                                <input type="radio" id="customColorRadio9"
                                                                    name="third_party_assessment" class="form-check-input"
                                                                    value="no"
                                                                    {{ $job->third_party_assessment == 'no' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio9">No</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1" style="display: none"
                                                    id="assessmentUrl">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Assesment Link <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" name="assessment_url"
                                                            value="{{ $job->assessment_url }}" class="form-control" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Last Apply Date</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="date" name="last_apply_date" class="form-control"
                                                            value="{{ $job->last_apply_date }}" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Publish For <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class=" demo-inline-spacing customernewsection-form">
                                                            <div class="form-check form-check-primary mt-25 mb-75">
                                                                <input type="radio" id="Internal" name="publish_for"
                                                                    class="form-check-input" checked=""
                                                                    value="internal"
                                                                    {{ $job->publish_for == 'internal' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Internal">Internal</label>
                                                            </div>

                                                            <div class="form-check form-check-primary mt-25 mb-75">
                                                                <input type="radio" id="External" name="publish_for"
                                                                    class="form-check-input" value="external"
                                                                    {{ $job->publish_for == 'external' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="External">External</label>
                                                            </div>

                                                            <div class="form-check form-check-primary mt-25 mb-75">
                                                                <input type="radio" id="Both" name="publish_for"
                                                                    class="form-check-input" value="both"
                                                                    {{ $job->publish_for == 'both' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Both">For
                                                                    Both</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 customernewsection-form">
                                                <div class="mt-2">
                                                    <div class="tab-content ">
                                                        <div class="tab-pane active" id="Request">
                                                            <h5 class="mt-1 mb-2 text-dark border-bottom pb-75">
                                                                <strong>Request Info</strong>
                                                            </h5>
                                                            <div class="table-responsive-md">
                                                                <table
                                                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="pe-0">
                                                                                <div
                                                                                    class="form-check form-check-inline me-0">
                                                                                    <input class="form-check-input"
                                                                                        type="checkbox" id="checkAll">
                                                                                </div>
                                                                            </th>
                                                                            <th>Date</th>
                                                                            <th>Job Request Id</th>
                                                                            <th>Job Type</th>
                                                                            <th>Education</th>
                                                                            <th>Skills</th>
                                                                            <th>Exp.</th>
                                                                            <th>Request By</th>
                                                                            <th>Expected D.O.J</th>
                                                                            <th>No. of Position</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="jobRequestTableBody">
                                                                        <tr>
                                                                            <td colspan="12" class="text-danger">
                                                                                <strong>Note: Please select job
                                                                                    title.</strong>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                                <div id="request-error-placeholder" class="mt-1"></div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane active mt-3" id="Pattern">
                                                            <h5 class="mt-1 mb-2 text-dark border-bottom pb-75">
                                                                <strong>Additional Information</strong>
                                                            </h5>

                                                            <div class="row">
                                                                <div class="col-md-6">

                                                                    <h5 class="mt-1 mb-2  text-dark"><strong>Job
                                                                            Information</strong></h5>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Employement Type
                                                                                <span class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <select class="form-select select2"
                                                                                name="employement_type">
                                                                                <option value=""
                                                                                    {{ $job->employement_type == '' ? 'selected' : '' }}>
                                                                                    Select</option>
                                                                                @forelse(App\Helpers\CommonHelper::EMPLOYEMENT_TYPE as $type)
                                                                                    <option value="{{ $type }}"
                                                                                        {{ $job->employement_type == $type ? 'selected' : '' }}>
                                                                                        {{ $type }}</option>
                                                                                @empty
                                                                                @endforelse
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">No of position
                                                                                <span class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="text" name="no_of_position"
                                                                                value="{{ $job->no_of_position }}"
                                                                                class="form-control numberonly-v2" />
                                                                        </div>
                                                                    </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Job Industry <span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <select class="form-select select2"
                                                                                name="industry_id">
                                                                                <option value=""
                                                                                    {{ $job->industry_id == '' ? 'selected' : '' }}>
                                                                                    Select</option>
                                                                                @forelse ($industries as $industry)
                                                                                    <option value="{{ $industry->id }}"
                                                                                        {{ $job->industry_id == $industry->id ? 'selected' : '' }}>
                                                                                        {{ $industry->name }}</option>
                                                                                @empty
                                                                                @endforelse
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Work Mode <span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <select class="form-select select2"
                                                                                name="work_mode">
                                                                                <option value=""
                                                                                    {{ $job->work_mode == '' ? 'selected' : '' }}>
                                                                                    Select</option>
                                                                                @forelse(App\Helpers\CommonHelper::WORK_MODE as $workMode)
                                                                                    <option value="{{ $workMode }}"
                                                                                        {{ $job->work_mode == $workMode ? 'selected' : '' }}>
                                                                                        {{ $workMode }}</option>
                                                                                @empty
                                                                                @endforelse
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Key Skills <span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <select class="form-select select2"
                                                                                id="skill" name="skill[]" multiple>
                                                                                <option value="">Select Skill
                                                                                </option>
                                                                                @foreach ($skills as $skill)
                                                                                    <option value="{{ $skill->name }}"
                                                                                        {{ in_array($skill->id, $jobSkills) ? 'selected' : '' }}>
                                                                                        {{ ucfirst($skill->name) }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Select Company
                                                                                <span class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <select class="form-control select2"
                                                                                id="company_id" name="company_id"
                                                                                onchange="dropdown('{{ url('recruitment/get-locations/') }}/' + this.value, 'location_id', '')">
                                                                                <option value=""
                                                                                    {{ $job->company_id == '' ? 'selected' : '' }}>
                                                                                    Select Company</option>
                                                                                @foreach ($companies as $company)
                                                                                    <option value="{{ $company->id }}"
                                                                                        {{ $job->company_id == $company->id ? 'selected' : '' }}>
                                                                                        {{ ucfirst($company->name) }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Job Location <span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <select class="form-control select2"
                                                                                id="location_id" name="location_id">
                                                                            </select>
                                                                        </div>
                                                                    </div>


                                                                    <div class="row mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Company Detail <span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <textarea class="form-control" name="company_detail">{{ $job->company_detail }}</textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-6">

                                                                    <h5 class="mt-1 mb-2  text-dark"><strong>Information
                                                                            for
                                                                            Candidate</strong></h5>

                                                                    <div class="row mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Qualifications <span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <select class="form-select select2"
                                                                                name="education_id"
                                                                                value="{{ old('education_id') }}">
                                                                                <option value=""
                                                                                    {{ $job->education_id == '' ? 'selected' : '' }}>
                                                                                    Select</option>
                                                                                @forelse ($educations as $education)
                                                                                    <option value="{{ $education->id }}"
                                                                                        {{ $job->education_id == $education->id ? 'selected' : '' }}>
                                                                                        {{ $education->name }}</option>
                                                                                @empty
                                                                                @endforelse
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Work Exp. (In year)
                                                                                <span class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-3 mb-sm-0 mb-1">
                                                                            <input type="number" class="form-control"
                                                                                placeholder="Min" name="work_exp_min numberonly-v2"
                                                                                value="{{ $job->work_exp_min }}" />
                                                                        </div>

                                                                        <div class="col-md-3 mb-sm-0 mb-1">
                                                                            <input type="number" class="form-control"
                                                                                placeholder="Max" name="work_exp_max numberonly-v2"
                                                                                value="{{ $job->work_exp_max }}" />
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Working Hour <span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <select class="form-select select2"
                                                                                name="working_hour_id">
                                                                                <option value=""
                                                                                    {{ $job->working_hour_id == '' ? 'selected' : '' }}>
                                                                                    Select</option>
                                                                                @forelse ($workingHours as $workingHour)
                                                                                    <option value="{{ $workingHour->id }}"
                                                                                        {{ $job->working_hour_id == $workingHour->id ? 'selected' : '' }}>
                                                                                        {{ $workingHour->name }}</option>
                                                                                @empty
                                                                                @endforelse
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Annual Salary (In
                                                                                lacs)<span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-3 mb-sm-0 mb-1">
                                                                            <input type="number" class="form-control"
                                                                                placeholder="Min" name="annual_salary_min numberonly-v2"
                                                                                value="{{ $job->annual_salary_min }}" />
                                                                            <div
                                                                                class="form-check form-check-primary mt-25 custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    class="form-check-input"
                                                                                    id="colorCheck1" value="1"
                                                                                    name="hide_from_candidate"
                                                                                    {{ $job->hide_from_candidate ? 'checked' : '' }}>
                                                                                <label class="form-check-label"
                                                                                    for="colorCheck1">Hide for
                                                                                    Condidate</label>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-3 mb-sm-0 mb-1">
                                                                            <input type="number" class="form-control"
                                                                                placeholder="Max" name="annual_salary_max numberonly-v2"
                                                                                value="{{ $job->annual_salary_max }}" />
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Notice Peroid <span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <select class="form-select select2"
                                                                                name="notice_peroid_id"
                                                                                value="{{ old('notice_peroid_id') }}">
                                                                                <option value=""
                                                                                    {{ $job->notice_peroid_id == '' ? 'selected' : '' }}>
                                                                                    Select</option>
                                                                                @forelse ($noticePeriods as $noticePeriod)
                                                                                    <option
                                                                                        value="{{ $noticePeriod->id }}"
                                                                                        {{ $job->notice_peroid_id == $noticePeriod->id ? 'selected' : '' }}>
                                                                                        {{ $noticePeriod->name }}</option>
                                                                                @empty
                                                                                @endforelse
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    {{-- <div class="row mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Notification E-Mail <span
                                                                                class="text-danger">*</span></label>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <select class="form-select select2" id="notification_email" name="notification_email[]" multiple></select>
                                                                    </div>
                                                                </div> --}}
                                                                </div>

                                                                <div class="col-md-12">
                                                                    <div class="row mb-1">
                                                                        <div class="col-md-2">
                                                                            <label class="form-label">Job Description <span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-9">
                                                                            <textarea class="form-control summernote" name="description">{{ $job->job_description }}</textarea>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane active mt-1" id="Approval">

                                                            <h5 class="mt-1 mb-2 text-dark border-bottom pb-75">
                                                                <strong>Interview Allocate
                                                                    Panel</strong>
                                                            </h5>

                                                            <div class="table-responsive-md">
                                                                <table
                                                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                    <thead>
                                                                        <tr>
                                                                            <th width="50px">#</th>
                                                                            <th width="250px">Level<span
                                                                                    class="text-danger">*</span></th>
                                                                            <th width="500px">Panelist Name<span
                                                                                    class="text-danger">*</span></th>
                                                                            <th width="400px">External Panelist Email-ID
                                                                            </th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="panelist-table-body">
                                                                        @foreach ($panelAllocations as $roundId => $items)
                                                                            @php
                                                                                $internalPanelsName = $items
                                                                                    ->whereNotNull('panel_id')
                                                                                    ->pluck('panel.name')
                                                                                    ->filter()
                                                                                    ->toArray();
                                                                                $externalEmail = $items
                                                                                    ->whereNotNull('external_email')
                                                                                    ->pluck('external_email')
                                                                                    ->first();
                                                                                $roundName =
                                                                                    optional($items->first()->round)
                                                                                        ->name ?? 'N/A';
                                                                            @endphp

                                                                            <tr>
                                                                                <td>{{ $loop->iteration }}</td>

                                                                                <td><span
                                                                                        class="badge rounded-pill badge-light-primary badgeborder-radius me-1 mb-1">{{ $roundName }}</span>
                                                                                </td>

                                                                                <td>
                                                                                    @foreach ($internalPanelsName as $panelName)
                                                                                        <span
                                                                                            class="badge rounded-pill badge-light-primary badgeborder-radius">{{ $panelName }}</span>
                                                                                    @endforeach
                                                                                </td>

                                                                                <td>
                                                                                    <span
                                                                                        class="badge rounded-pill badge-light-primary badgeborder-radius">{{ $externalEmail }}</span>
                                                                                </td>

                                                                                <td>
                                                                                    <a class="text-danger"
                                                                                        href="javascript:;"
                                                                                        data-url="{{ route('recruitment.jobs.remove-panel', ['id' => $job->id, 'roundId' => $roundId]) }}"
                                                                                        data-request="remove">
                                                                                        <i data-feather="trash-2"></i>
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                        <tr class="panelist-row">
                                                                            <td class="row-index">
                                                                                {{ count($panelAllocations) + 1 }}</td>
                                                                            <td>
                                                                                <select
                                                                                    class="form-select mw-100 select2 round"
                                                                                    name="data[0][round]" id="round_0">
                                                                                    <option value="">Select</option>
                                                                                    @forelse($rounds as $round)
                                                                                        <option
                                                                                            value="{{ $round->id }}">
                                                                                            {{ $round->name }}</option>
                                                                                    @empty
                                                                                    @endforelse
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <select
                                                                                    class="form-select select2 panel_id"
                                                                                    multiple name="data[0][panel_ids][]"
                                                                                    id="panel_id_0"></select>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text"
                                                                                    class="form-control mw-100 external_email"
                                                                                    placeholder="Enter Email-ID"
                                                                                    name="data[0][external_email]"
                                                                                    id="external_email_0" />
                                                                            </td>
                                                                            <td class="action-cell">
                                                                                <a href="#"
                                                                                    class="text-primary add-row change-to-remove"
                                                                                    id="add-more">
                                                                                    <i data-feather="plus-square"></i>
                                                                                </a>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal to add new record -->
                    </section>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Content-->

    <!-- BEGIN: MODAL-->
    <div class="modal fade" id="skillModal" tabindex="-1" aria-labelledby="skillModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">All Skills</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="skillModalBody">
                    <!-- Skills will be injected here -->
                </div>
            </div>
        </div>
    </div>
    <!-- END: MODAL-->
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>

    <script>
        $("#skill").select2({
            tags: true
        });

        document.addEventListener('DOMContentLoaded', function() {
            const skillModal = document.getElementById('skillModal');
            skillModal.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                const skills = JSON.parse(trigger.getAttribute('data-skills'));

                const body = skillModal.querySelector('#skillModalBody');
                body.innerHTML = ''; // Clear old content

                skills.forEach(skill => {
                    const badge =
                        `<span class="badge rounded-pill badge-light-secondary badgeborder-radius me-1 mb-1">${skill}</span>`;
                    body.innerHTML += badge;
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            fetchJobRequests('{{ $job->job_title_id }}');
            dropdown("{{ url('recruitment/get-locations') }}/" + '{{ $job->company_id }}', 'location_id',
                '{{ $job->location_id }}');
        });

        function fetchJobRequests(val) {
            if (val !== '') {
                $.ajax({
                    url: '{{ route('recruitment.jobs.get-job-requests') }}',
                    type: 'GET',
                    data: {
                        job_title_id: val,
                        job_id: '{{ $job->job_id }}'
                    },
                    success: function(response) {
                        $('#jobRequestTableBody').html(response);
                        // Re-render Feather icons after DOM update
                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching job requests.');
                    }
                });
            } else {
                $('#jobRequestTableBody').html('');
            }
        }

        $(document).on('click', '.clickopentr .open-job-sectab', function(e) {
            e.preventDefault();
            let $row = $(this).closest('tr');
            $row.next('.shojpbdescrp').slideDown();
            $(this).hide();
            $row.find('.close-job-sectab').show();
        });

        $(document).on('click', '.clickopentr .close-job-sectab', function(e) {
            e.preventDefault();
            let $row = $(this).closest('tr');
            $row.next('.shojpbdescrp').slideUp();
            $(this).hide();
            $row.find('.open-job-sectab').show();
        });

        $(document).on('change', '#checkAll', function() {
            $('.row-checkbox').prop('checked', $(this).prop('checked'));
        });

        $(document).ready(function() {
            $('#notification_email').select2({
                placeholder: "Select Email Id...",
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('recruitment.fetch-emails') }}",
                    dataType: 'json',
                    data: function(params) {
                        return {
                            search: $.trim(params.term),
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.data.map(function(employee) {
                                return {
                                    id: employee.id,
                                    text: employee.email
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            initPanelIdSelect2($('.panel_id'));
        });

        function initPanelIdSelect2($element) {
            $element.select2({
                placeholder: "Select Panel Name...",
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('recruitment.fetch-employees') }}",
                    dataType: 'json',
                    data: function(params) {
                        return {
                            search: $.trim(params.term),
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.data.map(function(employee) {
                                return {
                                    id: employee.id,
                                    text: employee.name
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                }
            });
        }
    </script>
    <script>
        var row_count = $(".panelist-row").length;
        var maximum_fields = 30;

        $("#add-more").click(function() {
            if (row_count < maximum_fields) {
                // Create HTML template with dynamic values
                var html = `
                <tr class="panelist-row">
                    <td class="row-index">${row_count + 1}</td>
                    <td>
                        <select class="form-select mw-100 select2 round" name="data[${row_count}][round]" id="round_${row_count}">
                            <option value="">Select</option>
                            @forelse($rounds as $round)
                                <option value="{{ $round->id }}">{{ $round->name }}</option>
                            @empty
                            @endforelse
                        </select>
                    </td>
                    <td>
                        <select class="form-select select2 panel_id" multiple name="data[${row_count}][panel_ids][]" id="panel_id_${row_count}"></select>
                    </td>
                    <td>
                        <input type="text" class="form-control mw-100 external_email" placeholder="Enter Email-ID" name="data[${row_count}][external_email]" id="external_email_${row_count}"/>
                    </td>
                    <td class="action-cell">
                        <a href="#" class="text-danger remove-row" id="remove_${row_count}" onclick="removeRows(event, ${row_count})">
                            <i data-feather="trash-2"></i>
                        </a>
                    </td>
                </tr>
            `;

                // Append the generated HTML
                // $("#panelist-table-body").append(html);
                document.getElementById('panelist-table-body').insertAdjacentHTML('beforeend', html);


                // Re-initialize select2 for the new select elements
                $("#round_" + row_count).select2();
                initPanelIdSelect2($("#panel_id_" + row_count));
                if (feather) feather.replace();

                // Increment row count for the next row
                row_count++;
                updateRowIndices();
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Alert!',
                    text: 'You can add only ' + maximum_fields + ' rows'
                });
            }
        });

        // Function to handle row removal
        function removeRows(event, row_count) {
            event.preventDefault();
            var row = document.getElementById(`remove_${row_count}`).closest('tr');
            row.remove();
            updateRowIndices();
        }

        // Update row indices after deletion
        function updateRowIndices() {
            $(".panelist-row").each(function(index) {
                $(this).find(".row-index").text(index + 1);
            });
        }
    </script>
    <script>
        $(document).ready(function() {
            // Initial check on page load
            toggleAssessmentUrl();

            // Listen for changes on the radio buttons
            $('input[name="third_party_assessment"]').on('change', function() {
                toggleAssessmentUrl();
            });

            function toggleAssessmentUrl() {
                const value = $('input[name="third_party_assessment"]:checked').val();
                console.log(value);
                if (value === 'yes') {
                    $('#assessmentUrl').show();
                } else {
                    $('#assessmentUrl').hide();
                }
            }
        });
    </script>
    <script>
        function updateTotalPositions() {
            let total = 0;

            $('.row-checkbox:checked').each(function() {
                const row = $(this).closest('tr');
                const noOfPosition = parseInt(row.find('.no-of-position').val()) || 0;
                total += noOfPosition;
            });

            $('input[name="no_of_position"]').val(total);
        }

        // Trigger sum calculation on checkbox changes
        $(document).on('change', '.row-checkbox', function() {
            updateTotalPositions();
        });

        // Also trigger on "Check All" change
        $(document).on('change', '#checkAll', function() {
            $('.row-checkbox').prop('checked', $(this).prop('checked'));
            updateTotalPositions();
        });
    </script>
@endsection
