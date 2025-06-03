@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-6 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Task</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">All Request
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-dark btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i
                                data-feather="filter"></i> Filter</button>
                        <a href="{{ route('recruitment.assessments.create') }}"
                            class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="plus-square"></i> Add Task</a>
                    </div>
                </div>
            </div>
            <div class="content-body dasboardnewbody">
                <!-- ChartJS section start -->
                <section id="chartjs-chart">
                    <div class="row">
                        <div class="col-md-12 col-12">
                            <div class="card  new-cardbox">

                                <div class="table-responsive candidates-tables">
                                    @include('recruitment.partials.card-header')
                                    <table
                                        class="datatables-basic table table-striped myrequesttablecbox loanapplicationlist tasklist">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Template Name</th>
                                                <th>Job Title</th>
                                                <th>Department</th>
                                                <th>Designation</th>
                                                <th>Work Exp.</th>
                                                <th>Question</th>
                                                <th>Passing Score</th>
                                                <th>Active</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($assessments as $assessment)
                                                <tr>
                                                    <td>{{ $assessments->firstItem() + $loop->index }}</td>
                                                    <td class="text-nowrap">
                                                        {{ $assessment->created_at ? App\Helpers\CommonHelper::dateFormat($assessment->created_at) : '' }}
                                                    </td>
                                                    <td class="text-dark fw-bolder">{{ $assessment->task_type }}</td>
                                                    <td>{{ $assessment->template_name }}</td>
                                                    <td>{{ isset($assessment->jobTitle->title) ? $assessment->jobTitle->title : '' }}
                                                    </td>
                                                    <td>{{ isset($assessment->department->name) ? $assessment->department->name : '' }}
                                                    </td>
                                                    <td>{{ isset($assessment->designation->name) ? $assessment->designation->name : '' }}
                                                    </td>
                                                    <td>{{ $assessment->min_exp . '-' . $assessment->max_exp }} Yrs</td>
                                                    <td>{{ $assessment->questions_count }}</td>
                                                    <td>{{ $assessment->passing_percentage }}%</td>
                                                    <td>
                                                        <div class="form-check form-check-primary form-switch">
                                                            <input type="checkbox" class="form-check-input status-toggle"
                                                                data-id="{{ $assessment->id }}"
                                                                {{ $assessment->status == 'active' ? 'checked' : '' }} />
                                                        </div>
                                                    </td>
                                                    <td class="tableactionnew">
                                                        <div class="dropdown">
                                                            <button type="button"
                                                                class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                                data-bs-toggle="dropdown">
                                                                <i data-feather="more-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item"
                                                                    href="{{ route('recruitment.assessments.preview', ['id' => $assessment->id]) }}">
                                                                    <i data-feather="eye" class="me-50"></i>
                                                                    <span>Preview</span>
                                                                </a>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('recruitment.assessments.edit', ['id' => $assessment->id]) }}">
                                                                    <i data-feather="edit-3" class="me-50"></i>
                                                                    <span>Edit</span>
                                                                </a>
                                                                <a class="dropdown-item" href="javascript:;"
                                                                    data-url="{{ url('recruitment/assessments/remove-assessment') . '/' . $assessment->id }}"
                                                                    data-request="remove">
                                                                    <i data-feather="check-circle" class="me-50"></i>
                                                                    <span>Delete</span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-danger text-center" colspan="12">No record(s) found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                {{-- Pagination --}}
                                {{ $assessments->appends(request()->input())->links('recruitment.partials.pagination') }}
                                {{-- Pagination End --}}
                            </div>
                        </div>
                    </div>
                </section>
                <!-- ChartJS section end -->
            </div>
        </div>
    </div>

    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <form>
                    <div class="modal-body flex-grow-1">
                        <div class="mb-1">
                            <label class="form-label" for="fp-range">Select Date Range</label>
                            <input type="text" id="fp-range" class="form-control flatpickr-range"
                                placeholder="YYYY-MM-DD to YYYY-MM-DD" name="date_range"
                                value="{{ request('date_range') }}" />
                        </div>

                        <div class="mb-1">
                            <label class="form-label">Select Type</label>
                            <select class="form-select select2" name="task_type">
                                <option value="" {{ request('task_type') == '' ? 'selected' : '' }}>Select
                                </option>
                                <option value="assessment" {{ request('task_type') == 'assessment' ? 'selected' : '' }}>
                                    Assessment</option>
                                <option value="questionaries"
                                    {{ request('task_type') == 'questionaries' ? 'selected' : '' }}>Questionaries</option>
                            </select>
                        </div>

                        <div class="mb-1">
                            <label class="form-label">Select Job Title</label>
                            <select class="form-select select2" name="job_title">
                                <option value="" {{ request('job_title') == '' ? 'selected' : '' }}>Select</option>
                                @forelse($jobTitles as $title)
                                    <option value="{{ $title->id }}"
                                        {{ request('job_title') == $title->id ? 'selected' : '' }}>{{ $title->title }}
                                    </option>
                                @empty
                                @endforelse
                            </select>
                        </div>


                    </div>
                    <div class="modal-footer justify-content-start">
                        <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                        <a href="{{ route('recruitment.assessments') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>

    <script>
        $(document).on('change', '.status-toggle', function() {
            const id = $(this).data('id');
            const status = $(this).is(':checked') ? 'active' : 'inactive';
            console.log(status);
            console.log(id);
            $.ajax({
                url: '{{ route('recruitment.assessments.update-status') }}',
                type: 'POST',
                data: {
                    id: id,
                    status: status,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 200) {
                        Swal.fire("Success!", response.message, "success");
                    }
                },
                error: function(response) {
                    Swal.fire("Error!", response.responseJSON?.message || "Something went wrong",
                        "error");
                }
            });
        });
    </script>
@endsection
