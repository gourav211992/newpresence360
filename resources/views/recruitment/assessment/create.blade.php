@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <form class="form" role="post-data" method="POST" action="{{ route('recruitment.assessments.store') }}"
                redirect="{{ route('recruitment.assessments') }}" autocomplete="off" enctype="multipart/form-data">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Add Task</h2>

                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="index.html">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">Add New Task</li>
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
                                    data-request="ajax-submit" data-target="[role=post-data]" id="saveBtn"><i
                                        data-feather="check-circle"></i>
                                    Submit</button>

                                <button type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0"
                                    data-request="ajax-submit" data-target="[role=post-data]" id="saveAsAnotherBtn"><i
                                        data-feather="check-circle"></i>
                                    Save as another</button>
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
                                                                            checked="" value="assessment">
                                                                        <label class="form-check-label fw-bolder"
                                                                            for="assessment">Assessment</label>
                                                                    </div>
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="questionaries"
                                                                            name="task_type" class="form-check-input"
                                                                            value="questionaries">
                                                                        <label class="form-check-label fw-bolder"
                                                                            for="questionaries">Questionaries</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 col-12">
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
                                                    </div>

                                                    <div class="col-md-12 col-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Select Job Title <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" name="job_title_id">
                                                                <option value="">Select</option>
                                                                @forelse($jobTitles as $title)
                                                                    <option value="{{ $title->id }}">{{ $title->title }}
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
                                                                placeholder="Enter Assessment Title" name="task_title">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 col-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Passing % <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" class="form-control"
                                                                placeholder="Enter per%" name="passing_percentage" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 col-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Description <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea class="form-control" placeholder="Enter Description" name="description"></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 col-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Department <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" name="department_id">
                                                                <option value="">Select</option>
                                                                @forelse($departments as $department)
                                                                    <option value="{{ $department->id }}">
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
                                                                <option value="">Select</option>
                                                                @forelse($designations as $designation)
                                                                    <option value="{{ $designation->id }}">
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
                                                                        placeholder="Enter Min Exp." name="min_exp">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <input type="number" class="form-control"
                                                                        placeholder="Enter Max Exp" name="max_exp">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="form-check smallcheckboxsurvey mb-25">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="inlineCheckbox1" name="save_as_template"
                                                                value="1">
                                                            <label class="form-check-label" for="inlineCheckbox1">Save as
                                                                Template</label>
                                                        </div>
                                                        <input type="text" class="form-control"
                                                            placeholder="Enter Template Name" name="template_name">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-8" id="question-section">
                                                <div class="question-box" data-question-index="0">
                                                    <h4>Question 1
                                                        <a href="#" onclick="addQuestionBox()">
                                                            <span data-bs-toggle="tooltip" data-popup="tooltip-custom"
                                                                data-bs-placement="top" title="Duplicate"
                                                                class="float-end text-dark ms-1">
                                                                <i data-feather='copy'></i>
                                                            </span>
                                                        </a>
                                                        <span data-bs-toggle="tooltip" data-popup="tooltip-custom"
                                                            data-bs-placement="top" title="Add Image"
                                                            class="upload-btn-wrapper float-end">
                                                            <button type="button" class="uploadBtnnew">
                                                                <i data-feather='image'></i>
                                                            </button>
                                                            <input type="file" name="questions[0][attachment]">
                                                        </span>
                                                    </h4>
                                                    <div class="row">
                                                        <div class="col-md-8 mb-sm-0 mb-1">
                                                            <input type="text" class="form-control"
                                                                placeholder="Title" name="questions[0][title]">
                                                        </div>
                                                        <div class="col-md-4 question-select mb-1">
                                                            <select data-placeholder="Select Question Type"
                                                                class="select2-icons form-select" id="select2-icons0"
                                                                name="questions[0][type]"
                                                                onchange="handleQuestionTypeChange(this)">
                                                                <option value="single choice" data-icon="circle" selected>
                                                                    Single
                                                                    Choice</option>
                                                                <option value="multiple choice" data-icon="stop-circle">
                                                                    Multiple Choice</option>
                                                                <option value="dropdown" data-icon="chevron-down">
                                                                    Dropdown
                                                                </option>
                                                                <option value="file upload" data-icon="upload">File
                                                                    Upload
                                                                </option>
                                                                <option value="image" data-icon="image">Image Upload
                                                                </option>
                                                                <option value="short answer" data-icon="align-left">
                                                                    Short
                                                                    Answer</option>
                                                                <option value="rating" data-icon="star">Rating
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="innergroupanser option-preview-section"
                                                        data-question-index="0">
                                                    </div>
                                                    <div class="option-section" data-question-index="0">
                                                        <div class="row innergroupanser mb-2 mt-1 addoption-box">
                                                            <div class="col-md-10">
                                                                <input type="text" class="form-control"
                                                                    placeholder="Enter Value"
                                                                    name="questions[0][new_option_label]">
                                                            </div>
                                                            <div class="col-md-2 mt-1  text-sm-end">
                                                                <span class="text-theme cursor-pointer add-removetxt"
                                                                    onclick="addOptions(this)">
                                                                    <i data-feather="plus-square"></i> Add Option
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="d-flex align-items-center">
                                                            <div class="form-check form-check-primary form-switch">
                                                                <input type="checkbox" checked class="form-check-input"
                                                                    id="required0" name="questions[0][is_required]"
                                                                    value="1" />
                                                            </div>
                                                            <label class="form-check-label"
                                                                for="required0">Required</label>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center mt-1 show-dropdown-toggle"
                                                        style="display:none;">
                                                        <div class="form-check form-check-primary form-switch">
                                                            <input type="checkbox" class="form-check-input"
                                                                id="dropdown0" name="questions[0][is_dropdown]"
                                                                value="1" />
                                                        </div>
                                                        <label class="form-check-label" for="dropdown0">Show as
                                                            dropdown</label>
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

                        $('#question-section').html(response.html);
                        questionCounter = $('#question-section .question-box').length + 1;

                        $('#question-section .question-box').each(function() {
                            let $box = $(this);
                            let qIndex = $box.data('question-index');

                            initSelect2WithIcons(`#select2-icons${qIndex}`);
                            feather.replace();

                            // Manually trigger the type change handler to re-render options section
                            let select = this.querySelector(`#select2-icons${qIndex}`);
                            if (select) handleQuestionTypeChange(select);
                        });

                        if (typeof feather !== 'undefined') {
                            feather.replace();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching template data.');
                    }
                });
            } else {
                resetfieldsData();
                resetQuestBox();
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

        function resetfieldsData() {
            document.getElementById('assessment').checked = false;
            document.getElementById('questionaries').checked = false;
            $('select[name="job_title_id"]').val('').trigger('change');
            $('select[name="department_id"]').val('').trigger('change');
            $('select[name="designation_id"]').val('').trigger('change');
            $('input[name="task_title"]').val('');
            $('input[name="passing_percentage"]').val('');
            $('textarea[name="description"]').val('');
            $('input[name="min_exp"]').val('');
            $('input[name="max_exp"]').val('');
            $('input[name="template_name"]').val('');
            $('input[name="save_as_template"]').prop('checked', false);
        }

        function resetQuestBox() {
            $('#question-section').html(`
                <div class="question-box" data-question-index="0">
                    <h4>Question 1
                        <a href="#" onclick="addQuestionBox()">
                            <span data-bs-toggle="tooltip" data-popup="tooltip-custom"
                                data-bs-placement="top" title="Duplicate"
                                class="float-end text-dark ms-1">
                                <i data-feather='copy'></i>
                            </span>
                        </a>
                        <span data-bs-toggle="tooltip" data-popup="tooltip-custom"
                            data-bs-placement="top" title="Add Image"
                            class="upload-btn-wrapper float-end">
                            <button type="button" class="uploadBtnnew">
                                <i data-feather='image'></i>
                            </button>
                            <input type="file" name="questions[0][attachment]">
                        </span>
                    </h4>
                    <div class="row">
                        <div class="col-md-8 mb-sm-0 mb-1">
                            <input type="text" class="form-control"
                                placeholder="Title" name="questions[0][title]">
                        </div>
                        <div class="col-md-4 question-select mb-1">
                            <select data-placeholder="Select Question Type"
                                class="select2-icons form-select" id="select2-icons0"
                                name="questions[0][type]"
                                onchange="handleQuestionTypeChange(this)">
                                <option value="single choice" data-icon="circle" selected>Single Choice</option>
                                <option value="multiple choice" data-icon="stop-circle">Multiple Choice</option>
                                <option value="dropdown" data-icon="chevron-down">Dropdown</option>
                                <option value="file upload" data-icon="upload">File Upload</option>
                                <option value="image" data-icon="image">Image Upload</option>
                                <option value="short answer" data-icon="align-left">Short Answer</option>
                                <option value="rating" data-icon="star">Rating</option>
                            </select>
                        </div>
                    </div>

                    <div class="innergroupanser option-preview-section" data-question-index="0"></div>

                    <div class="option-section" data-question-index="0">
                        <div class="row innergroupanser mb-2 mt-1 addoption-box">
                            <div class="col-md-10">
                                <input type="text" class="form-control"
                                    placeholder="Enter Value" name="questions[0][new_option_label]">
                            </div>
                            <div class="col-md-2 mt-1 text-sm-end">
                                <span class="text-theme cursor-pointer add-removetxt" onclick="addOptions(this)">
                                    <i data-feather="plus-square"></i> Add Option
                                </span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex align-items-center">
                            <div class="form-check form-check-primary form-switch">
                                <input type="checkbox" checked class="form-check-input"
                                    id="required0" name="questions[0][is_required]" value="1" />
                            </div>
                            <label class="form-check-label" for="required0">Required</label>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mt-1 show-dropdown-toggle" style="display:none;">
                        <div class="form-check form-check-primary form-switch">
                            <input type="checkbox" class="form-check-input"
                                id="dropdown0" name="questions[0][is_dropdown]" value="1" />
                        </div>
                        <label class="form-check-label" for="dropdown0">Show as dropdown</label>
                    </div>
                </div>
            `);

            questionCounter = $('#question-section .question-box').length + 1;

            // ✅ Correct initialization
            initSelect2WithIcons('#select2-icons0');
            feather.replace();
        }
    </script>
    <script src="{{ asset('app-assets/js/assessment.js') }}"></script>

    <script>
        document.getElementById('saveAsAnotherBtn').addEventListener('click', function() {
            const form = document.querySelector('form[role="post-data"]');
            form.setAttribute('redirect', '{{ route('recruitment.assessments.create') }}');
        });

        document.getElementById('saveBtn').addEventListener('click', function() {
            const form = document.querySelector('form[role="post-data"]');
            form.setAttribute('redirect', '{{ route('recruitment.assessments') }}');
        });
    </script>
@endsection
