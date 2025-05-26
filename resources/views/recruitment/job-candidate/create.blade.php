@extends('layouts.app')
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <form class="form" role="post-data" method="POST" action="{{ route('recruitment.job-candidates.store') }}"
                redirect="{{ route('recruitment.job-candidates') }}" autocomplete="off">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6  mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">New Candidate</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a
                                                    href="{{ route('recruitment.hr-dashboard') }}">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">Add New</li>


                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <a href="{{ route('recruitment.job-candidates') }}"
                                class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
                                Back</a>
                            <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-request="ajax-submit"
                                data-target="[role=post-data]"><i data-feather="check-circle"></i> Create</button>
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
                                                {{-- <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Job</label>
                                                </div>

                                                <div class="col-md-5">
                                                    <select class="form-select select2" name="job_id[]" multiple>
                                                        <option value="">Select</option>
                                                        @forelse ($jobs as $job)
                                                        <option value="{{ $job->id }}">{{ $job->job_title_name }}</option>
                                                        @empty
                                                        @endforelse
                                                    </select>
                                                </div>
                                            </div> --}}

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Full Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="name" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Email-ID <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="email" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Mobile No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="mobile_no" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Education <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="education_id">
                                                            <option value="">Select</option>
                                                            @forelse ($educations as $education)
                                                                <option value="{{ $education->id }}">{{ $education->name }}
                                                                </option>
                                                            @empty
                                                            @endforelse
                                                        </select>
                                                    </div>
                                                </div>


                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Skills <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select select2" id="skill" name="skill[]"
                                                            multiple>
                                                            <option value="">Select Skill</option>
                                                            @foreach ($skills as $skill)
                                                                <option value="{{ $skill->name }}">
                                                                    {{ ucfirst($skill->name) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>


                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Work Exp. (In Year) <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control numberonly-v2"
                                                            name="work_exp" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Current Organization <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control"
                                                            name="current_organization" />
                                                    </div>
                                                </div>


                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Expected Salary (In Lacs) <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="exp_salary" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-control select2" id="location_id"
                                                            name="location_id">
                                                            <option value="">Select Experience</option>
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}">
                                                                    {{ ucfirst($location->store_name) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Upload Resume <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="file" class="form-control" name="resume" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Refered By</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select select2" id="refered_by"
                                                            name="refered_by">
                                                            <option value="">Select</option>
                                                        </select>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="col-md-3 border-start">
                                                <div class="row align-items-center mb-2">
                                                    <div class="col-md-12">
                                                        <label
                                                            class="form-label text-primary"><strong>Status</strong></label>
                                                    </div>

                                                    <div class="col-md-12  customernewsection-form">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio3"
                                                                    name="status"
                                                                    value="{{ App\Helpers\CommonHelper::ACTIVE }}"
                                                                    class="form-check-input" checked="">
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio3">Active</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio4"
                                                                    name="status"
                                                                    value="{{ App\Helpers\CommonHelper::INACTIVE }}"
                                                                    class="form-check-input">
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio4">Inactive</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label text-primary"><strong>Potential
                                                                Type</strong></label>
                                                    </div>

                                                    <div class="col-md-12  customernewsection-form">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Yes"
                                                                    name="potential_type" class="form-check-input"
                                                                    value="high">
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Yes">High</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="No"
                                                                    name="potential_type" class="form-check-input"
                                                                    checked="" value="medium">
                                                                <label class="form-check-label fw-bolder"
                                                                    for="No">Medium</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Low"
                                                                    name="potential_type" class="form-check-input"
                                                                    value="low">
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Low">Low</label>
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
                    </section>
                </div>
            </form>
        </div>
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
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#refered_by').select2({
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
        });
    </script>
@endsection
