@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <form class="form" role="post-data" method="POST"
                action="{{ url('recruitment/assessments/' . $assessment->id) }}"
                redirect="{{ route('recruitment.assessments') }}">
                @csrf
                @method('PUT')
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Edit Task</h2>

                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="index.html">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">Edit Task</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <a href="{{ route('recruitment.assessments') }}"
                                    class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
                                    Back</a>
                                <button type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0"
                                    onclick="addQuestionBox()"><i data-feather="plus-square"></i>
                                    Add
                                    Question</button>
                                <button type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0"
                                    data-request="ajax-submit" data-target="[role=post-data]"><i
                                        data-feather="check-circle"></i>
                                    Submit</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-body">

                    <section id="basic-datatable">
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body customernewsection-form">

                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="row">

                                                    <div class="col-md-12">
                                                        <div class="row align-items-center mb-2">
                                                            <div class="col-md-12">
                                                                <label class="form-label"><strong>Task Type</strong></label>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <div class="demo-inline-spacing">
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="assessment"
                                                                            name="task_type" class="form-check-input"
                                                                            value="assessment"
                                                                            {{ $assessment->task_type == 'assessment' ? 'checked' : '' }}>
                                                                        <label class="form-check-label fw-bolder"
                                                                            for="assessment">Assessment</label>
                                                                    </div>
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="questionaries"
                                                                            name="task_type" class="form-check-input"
                                                                            value="questionaries"
                                                                            {{ $assessment->task_type == 'questionaries' ? 'checked' : '' }}>
                                                                        <label class="form-check-label fw-bolder"
                                                                            for="questionaries">Questionaries</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- <div class="col-md-12 col-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Select Template </label>
                                                            <select class="form-select"
                                                                onchange="getTemplateData(this.value)" name="template_id">
                                                                <option value="">Select</option>
                                                                @forelse($templates as $template)
                                                                    <option value="{{ $template->id }}">
                                                                        {{ $template->template_name }}
                                                                    </option>
                                                                @empty
                                                                @endforelse
                                                            </select>
                                                        </div>
                                                    </div> --}}

                                                    <div class="col-md-12 col-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Select Job Title <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" name="job_title_id">
                                                                <option value=""
                                                                    {{ $assessment->job_title_id == '' ? 'selected' : '' }}>
                                                                    Select</option>
                                                                @forelse($jobTitles as $title)
                                                                    <option value="{{ $title->id }}"
                                                                        {{ $assessment->job_title_id == $title->id ? 'selected' : '' }}>
                                                                        {{ $title->title }}
                                                                    </option>
                                                                @empty
                                                                @endforelse
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 col-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Task Title <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control"
                                                                placeholder="Enter Assessment Title" name="task_title"
                                                                value="{{ $assessment->task_title }}">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 col-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Passing % <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" class="form-control"
                                                                placeholder="Enter per%" name="passing_percentage"
                                                                value="{{ $assessment->passing_percentage }}" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 col-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Description <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea class="form-control" placeholder="Enter Description" name="description">{{ $assessment->description }}</textarea>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 col-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Department <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" name="department_id">
                                                                <option value=""
                                                                    {{ $assessment->department_id == '' ? 'selected' : '' }}>
                                                                    Select</option>
                                                                @forelse($departments as $department)
                                                                    <option value="{{ $department->id }}"
                                                                        {{ $assessment->department_id == $department->id ? 'selected' : '' }}>
                                                                        {{ $department->name }}
                                                                    </option>
                                                                @empty
                                                                @endforelse
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Designation <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" name="designation_id">
                                                                <option value=""
                                                                    {{ $assessment->designation_id == '' ? 'selected' : '' }}>
                                                                    Select</option>
                                                                @forelse($designations as $designation)
                                                                    <option value="{{ $designation->id }}"
                                                                        {{ $assessment->designation_id == $designation->id ? 'selected' : '' }}>
                                                                        {{ $designation->name }}
                                                                    </option>
                                                                @empty
                                                                @endforelse
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 col-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Experience <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <input type="number" class="form-control"
                                                                        placeholder="Enter Min Exp." name="min_exp"
                                                                        value="{{ $assessment->min_exp }}">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <input type="number" class="form-control"
                                                                        placeholder="Enter Max Exp" name="max_exp"
                                                                        value="{{ $assessment->max_exp }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-check smallcheckboxsurvey mb-25">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="inlineCheckbox1" name="save_as_template"
                                                                value="1"
                                                                {{ $assessment->save_as_template == '1' ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="inlineCheckbox1">Save as
                                                                Template</label>
                                                        </div>
                                                        <input type="text" class="form-control"
                                                            placeholder="Enter Template Name" name="template_name"
                                                            value="{{ $assessment->template_name }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-8" id="question-section">
                                                @include('recruitment.partials.template-view', [
                                                    'questions' => $questions,
                                                ])

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
    <script src="{{ asset('app-assets/js/assessment.js') }}"></script>
    <script>
        let questionCounter = $('#question-section .question-box').length + 1;

        function getTemplateData(val) {
            if (val !== '') {
                $.ajax({
                    url: '{{ route('recruitment.assessments.get-template-data') }}',
                    type: 'GET',
                    data: {
                        template_id: val
                    },
                    success: function(response) {
                        let data = response.template;
                        setfieldsData(data);

                        $('#question-section').html('');
                        $('#question-section').html(response.html);
                        // Re-render Feather icons after DOM update

                        questionCounter = $('#question-section .question-box').length + 1;

                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching template data.');
                    }
                });
            }
        }

        function setfieldsData(data) {
            if (!data) return;

            document.getElementById('assessment').checked = (data.task_type === 'assessment');
            document.getElementById('questionaries').checked = (data.task_type === 'questionaries');
            $('select[name="job_title_id"]').val(data.job_title_id).trigger('change');
            $('input[name="task_title"]').val(data.task_title);
            $('input[name="passing_percentage"]').val(data.passing_percentage);
            $('textarea[name="description"]').val(data.description);
            $('select[name="department_id"]').val(data.department_id).trigger('change');
            $('select[name="designation_id"]').val(data.designation_id).trigger('change');
            $('input[name="min_exp"]').val(data.min_exp);
            $('input[name="max_exp"]').val(data.max_exp);
            $('input[name="save_as_template"]').prop('checked', data.save_as_template == 1);
            $('input[name="template_name"]').val(data.template_name);
        }
    </script>
@endsection
