@extends('layouts.app')
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <form class="form" role="post-data" method="POST" action="{{ url('recruitment/requests/' . $jobRequest->id) }}"
            redirect="{{ route('recruitment.requests') }}">
            @csrf
            @method('PUT')
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">
                                        Job Request
                                    </h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a href="{{ route('recruitment.dashboard') }}">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">
                                                Edit
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <a href="{{ route('recruitment.requests') }}" class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                                <i data-feather="arrow-left-circle"></i> Back
                            </a>
                            <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-request="ajax-submit"
                                data-target="[role=post-data]">
                                <i data-feather="check-circle"></i> Update
                            </button>
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
                                                <div class="newheader border-bottom mb-2 pb-25">
                                                    <h4 class="card-title text-theme">
                                                        Basic Information
                                                    </h4>
                                                    <p class="card-text">
                                                        Fill the details
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="col-md-9">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Job Type
                                                            <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5 customernewsection-form">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="New" name="job_type"
                                                                    value="new" class="form-check-input job_type"
                                                                    {{ $jobRequest->job_type == 'new' ? 'checked' : '' }} />
                                                                <label class="form-check-label fw-bolder"
                                                                    for="New">New</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Replacement" name="job_type"
                                                                    value="replacement" class="form-check-input job_type"
                                                                    {{ $jobRequest->job_type == 'replacement' ? 'checked' : '' }} />
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Replacement">Replacement</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1 replacementTypeField">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Employee
                                                            <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select select2" id="emp_id" name="emp_id">
                                                            <option value="">Select Employee</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Job Title
                                                            <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="job_title_id">
                                                            <option value=""
                                                                {{ $jobRequest->job_title_id == '' ? 'selected' : '' }}>
                                                                Select</option>
                                                            @forelse ($jobTitles as $title)
                                                                <option value="{{ $title->id }}"
                                                                    {{ $jobRequest->job_title_id == $title->id ? 'selected' : '' }}>
                                                                    {{ $title->title }}</option>
                                                            @empty
                                                                <option disabled>No job titles available</option>
                                                            @endforelse
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">No. of Position<span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control numberonly-v2"
                                                            name="no_of_position"
                                                            value="{{ $jobRequest->no_of_position }}" />
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Educations<span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="education_id">
                                                            <option value=""
                                                                {{ $jobRequest->education_id == '' ? 'selected' : '' }}>
                                                                Select</option>
                                                            @forelse ($eduactions as $eduaction)
                                                                <option value="{{ $eduaction->id }}"
                                                                    {{ $jobRequest->education_id == $eduaction->id ? 'selected' : '' }}>
                                                                    {{ $eduaction->name }}</option>
                                                            @empty
                                                            @endforelse
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Certification</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="certification_id[]"
                                                            id="certification_id" multiple>
                                                            <option value="">Select</option>
                                                            @forelse ($certifications as $certification)
                                                                <option value="{{ $certification->name }}"
                                                                    {{ in_array($certification->id, $requestCertifications) ? 'selected' : '' }}>
                                                                    {{ ucfirst($certification->name) }}</option>
                                                            @empty
                                                            @endforelse
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Req. Skills<span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select select2" id="skill" name="skill[]"
                                                            multiple>
                                                            <option value="">Select Skill</option>
                                                            @foreach ($skills as $skill)
                                                                <option value="{{ $skill->name }}"
                                                                    {{ in_array($skill->id, $requestSkills) ? 'selected' : '' }}>
                                                                    {{ ucfirst($skill->name) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Work Exp.<span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-control select2" id="work_exp_id"
                                                            name="work_exp_id">
                                                            <option value=""
                                                                {{ $jobRequest->work_exp_id == '' ? 'selected' : '' }}>
                                                                Select Experience</option>
                                                            @foreach ($workExperiences as $workExperience)
                                                                <option value="{{ $workExperience->id }}"
                                                                    {{ $jobRequest->work_exp_id == $workExperience->id ? 'selected' : '' }}>
                                                                    {{ ucfirst($workExperience->name) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Expected D.O.J<span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control" name="expected_doj"
                                                            value="{{ $jobRequest->expected_doj }}" />
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Priority
                                                            <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="priority">
                                                            <option value=""
                                                                {{ $jobRequest->priority == '' ? 'selected' : '' }}>Select
                                                            </option>
                                                            @forelse($priorities as $priority)
                                                                <option value="{{ $priority }}"
                                                                    {{ $jobRequest->priority == $priority ? 'selected' : '' }}>
                                                                    {{ $priority }}</option>
                                                            @empty
                                                            @endforelse
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Select Company
                                                            <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-control select2" id="company_id"
                                                            name="company_id"
                                                            onchange="dropdown('{{ url('recruitment/get-locations/') }}/' + this.value, 'location_id', '')">
                                                            <option value=""
                                                                {{ $jobRequest->company_id == '' ? 'selected' : '' }}>
                                                                Select Company</option>
                                                            @foreach ($companies as $company)
                                                                <option value="{{ $company->id }}"
                                                                    {{ $jobRequest->company_id == $company->id ? 'selected' : '' }}>
                                                                    {{ ucfirst($company->name) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Placed Location
                                                            <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-control select2" id="location_id"
                                                            name="location_id">
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Job Description
                                                            <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <textarea rows="1" type="text" class="form-control" name="job_description">{{ $jobRequest->job_description }}</textarea>
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Reason
                                                            <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <textarea rows="1" type="text" class="form-control" name="reason">{{ $jobRequest->reason }}</textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- <div class="col-md-3 border-start">
                                        <div class="row align-items-center mb-2">
                                            <div class="col-md-12">
                                                <label class="form-label">Status</label>
                                            </div>

                                            <div class="col-md-12 customernewsection-form">
                                                <div class="demo-inline-spacing">
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" id="customColorRadio3"
                                                            class="form-check-input"
                                                            {{ $jobRequest->status == 'active' ? 'checked' : '' }} name="status" value="active"/>
                                                        <label class="form-check-label fw-bolder"
                                                            for="customColorRadio3">Active</label>
                                                    </div>
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" id="customColorRadio4"
                                                            class="form-check-input" name="status" value="inactive" {{ $jobRequest->status == 'inactive' ? 'checked' : '' }}/>
                                                        <label class="form-check-label fw-bolder"
                                                            for="customColorRadio4">Inactive</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-12">
                                                <label class="form-label">Assessment Required</label>
                                            </div>

                                            <div class="col-md-12 customernewsection-form">
                                                <div class="demo-inline-spacing">
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" id="Yes" name="assessment_required" value="yes"
                                                            class="form-check-input" {{ $jobRequest->assessment_required == 'yes' ? 'checked' : '' }}/>
                                                        <label class="form-check-label fw-bolder" for="Yes">Yes</label>
                                                    </div>
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" id="No" name="assessment_required" value="no"
                                                            class="form-check-input" {{ $jobRequest->assessment_required == 'no' ? 'checked' : '' }} />
                                                        <label class="form-check-label fw-bolder" for="No">No</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal to add new record -->
                    </section>
                </div>
            </div>
        </form>
    </div>
    <!-- END: Content-->
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#skill").select2({
                tags: true
            });

            $("#certification_id").select2({
                tags: true
            });

            $('.job_type').on("change", function() {
                toggleReplacementField($(this).val());
            });

            toggleReplacementField($('.job_type:checked').val());

            dropdown("{{ url('recruitment/get-locations') }}/" + '{{ $jobRequest->company_id }}', 'location_id',
                '{{ $jobRequest->location_id }}');

        });

        function toggleReplacementField(type) {
            const positionField = $('input[name="no_of_position"]');

            if (type === 'replacement') {
                $('.replacementTypeField').show();

                // Set to 1 only if not already filled or filled with other than 1
                if (positionField.val() === '' || positionField.val() != 1) {
                    positionField.val(1);
                }

                positionField.prop('readonly', true);
            } else {
                $('.replacementTypeField').hide();

                // Remove readonly but don't clear value if one exists
                positionField.prop('readonly', false);
                positionField.val('{{ $jobRequest->no_of_position }}');
            }
        }


        $(document).ready(function() {
            const selectedEmpId = "{{ $jobRequest->emp_id }}";

            $('#emp_id').select2({
                placeholder: "Select Employee...",
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

            if (selectedEmpId) {
                $.ajax({
                    url: "{{ route('recruitment.fetch-team') }}",
                    type: 'GET',
                    data: {
                        id: selectedEmpId
                    },
                    success: function(data) {
                        if (data && data.data && data.data.length > 0) {
                            const emp = data.data[0];
                            const option = new Option(emp.name, emp.id, true, true);
                            $('#emp_id').append(option).trigger('change');
                        }
                    }
                });
            }
        });
    </script>
@endsection
