@extends('layouts.app')
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <form class="form" role="post-data" method="POST" action="{{ route('recruitment.requests.store') }}"
            redirect="{{ route('recruitment.requests') }}" autocomplete="off">
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
                                                Add New
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
                                <i data-feather="check-circle"></i> Create
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
                                                                    checked />
                                                                <label class="form-check-label fw-bolder"
                                                                    for="New">New</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Replacement" name="job_type"
                                                                    value="replacement" class="form-check-input job_type" />
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
                                                            <option value="">Select</option>
                                                            @forelse ($jobTitles as $title)
                                                                <option value="{{ $title->id }}">{{ $title->title }}
                                                                </option>
                                                            @empty
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
                                                            name="no_of_position" value="{{ old('no_of_position') }}" />
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Educations<span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="education_id"
                                                            value="{{ old('education_id') }}">
                                                            <option value="">Select</option>
                                                            @forelse ($eduactions as $eduaction)
                                                                <option value="{{ $eduaction->id }}">{{ $eduaction->name }}
                                                                </option>
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
                                                        <select class="form-select select2" id="certification_id"
                                                            name="certification_id[]" multiple>
                                                            <option value="">Select</option>
                                                            @foreach ($certifications as $certification)
                                                                <option value="{{ $certification->name }}">
                                                                    {{ ucfirst($certification->name) }}</option>
                                                            @endforeach
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
                                                                <option value="{{ $skill->name }}">
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
                                                            <option value="">Select Experience</option>
                                                            @foreach ($workExperiences as $workExperience)
                                                                <option value="{{ $workExperience->id }}">
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
                                                        <input type="date" class="form-control" name="expected_doj" />
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Priority
                                                            <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="priority">
                                                            <option value="">Select</option>
                                                            @forelse($priorities as $priority)
                                                                <option value="{{ $priority }}">
                                                                    {{ ucfirst($priority) }}</option>
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
                                                            <option value="">Select Company</option>
                                                            @foreach ($companies as $company)
                                                                <option value="{{ $company->id }}">
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
                                                            <option value="">Select Location</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Job Description
                                                            <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <textarea rows="1" type="text" class="form-control" name="job_description"></textarea>
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Reason
                                                            <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <textarea rows="1" type="text" class="form-control" name="reason"></textarea>
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
                                                            checked="" name="status" value="active"/>
                                                        <label class="form-check-label fw-bolder"
                                                            for="customColorRadio3">Active</label>
                                                    </div>
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" id="customColorRadio4"
                                                            class="form-check-input" name="status" value="inactive"/>
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
                                                            class="form-check-input" />
                                                        <label class="form-check-label fw-bolder" for="Yes">Yes</label>
                                                    </div>
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" id="No" name="assessment_required" value="no"
                                                            class="form-check-input" checked="" />
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
        });

        function toggleReplacementField(type) {
            if (type === 'replacement') {
                $('.replacementTypeField').show();
                $('input[name="no_of_position"]').val(1);
                $('input[name="no_of_position"]').prop('readonly', true);
            } else {
                $('.replacementTypeField').hide();
                $('input[name="no_of_position"]').prop('readonly', false);
                $('input[name="no_of_position"]').val('');

            }
        }

        $(document).ready(function() {
            $('#emp_id').select2({
                placeholder: "Select Employee...",
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('recruitment.fetch-team') }}",
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
        });
    </script>
@endsection
