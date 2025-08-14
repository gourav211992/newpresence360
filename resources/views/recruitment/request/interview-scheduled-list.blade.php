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
                            <h2 class="content-header-title float-start mb-0">My Requests</h2>
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
                        <a href="{{ route('recruitment.requests.create') }}"
                            class="btn btn-primary  btn-sm mb-50 mb-sm-0"><i data-feather="plus-square"></i> Add Request</a>
                    </div>
                </div>
            </div>
            <div class="content-body dasboardnewbody">

                <section id="chartjs-chart">
                    <div class="row">

                        @include('recruitment.request.summary', [
                            'requestCount' => $requestCount,
                            'requestForApprovalCount' => $requestForApprovalCount,
                            'rejectedRequestCount' => $rejectedRequestCount,
                            'openRequestCount' => $openRequestCount,
                            'interviewScheduledCount' => $interviewScheduledCount,
                        ])


                        <div class="col-md-12 col-12">
                            <div class="card  new-cardbox">
                                {{-- Card Header --}}
                                @include('recruitment.request.tab', [
                                    'requestCount' => $requestCount,
                                    'candidateAssignedRequestCount' => $candidateAssignedRequestCount,
                                    'requestForApprovalCount' => $requestForApprovalCount,
                                    'interviewScheduledCount' => $interviewScheduledCount,
                                ])

                                <div class="tab-content">
                                    <div class="tab-pane active">
                                        <div class="table-responsive candidates-tables">
                                            @include('recruitment.partials.card-header')
                                            <table class="table table-striped myrequesttablecbox loanapplicationlist">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Interview Date</th>
                                                        <th>Job Id</th>
                                                        <th>Job Type</th>
                                                        <th>Job Title</th>
                                                        <th>Request By</th>
                                                        <th>Candidate</th>
                                                        <th>Round</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($jobInterviews as $interview)
                                                        <tr>
                                                            <td>{{ $jobInterviews->firstItem() + $loop->index }}</td>
                                                            <td>
                                                                <span
                                                                    class="fw-bolder text-dark">{{ $interview->date_time ? App\Helpers\CommonHelper::dateFormat($interview->date_time) : '' }}</span>
                                                                ({{ $interview->date_time ? App\Helpers\CommonHelper::timeFormat($interview->date_time) : '' }})
                                                                </strong>
                                                                <br />
                                                                <a href="{{ $interview->meeting_link }}"
                                                                    class="d-flex align-items-center mt-25 font-small-2">
                                                                    <i data-feather='video' class="me-25"></i>
                                                                    <strong>Join Meeting</strong>
                                                                </a>
                                                            </td>
                                                            <td class="fw-bolder text-dark">
                                                                {{ isset($interview->job->job_id) ? $interview->job->job_id : '' }}
                                                            </td>
                                                            <td>
                                                                <a
                                                                    href="{{ route('recruitment.requests.job-view', ['id' => $interview->job_id]) }}">
                                                                    <span
                                                                        class="badge rounded-pill badge-light-primary badgeborder-radius">{{ ucfirst($interview->job->status) }}</span>
                                                                </a>
                                                            </td>
                                                            <td>{{ isset($interview->job->job_title_name) ? $interview->job->job_title_name : '' }}
                                                            </td>
                                                            <td>
                                                                <div class="d-flex flex-row">
                                                                    <div
                                                                        style="background-color: #ddb6ff; color: #6b12b7; line-height: 30px; width: 30px; height: 30px; border-radius: 50%; position: relative; font-size: 1rem; text-align: center; margin-right: 5px; font-weight: 600;">
                                                                        {{ strtoupper(substr($interview->creator_name, 0, 1)) }}
                                                                    </div>
                                                                    <div class="my-auto">
                                                                        <h6
                                                                            class="mb-0 fw-bolder text-dark hr-dashemplname">
                                                                            {{ $interview->creator_name }}</h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>{{ isset($interview->candidate->name) ? $interview->candidate->name : '' }}
                                                            </td>
                                                            <td>{{ $interview->round_name ? $interview->round_name : '' }}
                                                            </td>
                                                            <td>
                                                                @php
                                                                    if ($interview->status == 'scheduled') {
                                                                        $className = 'badge-light-primary';
                                                                    } elseif ($interview->status == 'rejected') {
                                                                        $className = 'badge-light-danger';
                                                                    } elseif ($interview->status == 'onhold') {
                                                                        $className = 'badge-light-warning';
                                                                    } else {
                                                                        $className = 'badge-light-success';
                                                                    }
                                                                @endphp
                                                                <span
                                                                    class="badge rounded-pill {{ $className }} badgeborder-radius">
                                                                    {{ $interview->status ? ucfirst($interview->status) : '' }}
                                                                </span>
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
                                                                            href="{{ route('recruitment.requests.job-view', ['id' => $interview->job_id]) }}">
                                                                            <i data-feather="eye" class="me-50"></i>
                                                                            <span>View Detail</span>
                                                                        </a>
                                                                        @if (!$interview->interviewFeedback)
                                                                            <a class="dropdown-item"
                                                                                href="#feedbackcandidate"
                                                                                onclick="setFeedbackModal('{{ $interview->id }}')">
                                                                                <i data-feather="message-circle"
                                                                                    class="me-50"></i>
                                                                                <span>Feedback</span>
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td class="text-danger text-center" colspan="12">No record(s)
                                                                found.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        {{-- Pagination --}}
                                        {{ $jobInterviews->appends(request()->input())->links('recruitment.partials.pagination') }}
                                        {{-- Pagination End --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- ChartJS section end -->

            </div>
        </div>
    </div>
    <!-- END: Content-->
    <!-- BEGIN: MODAL-->
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Date Range</label>
                        <input type="text" id="fp-range" class="form-control flatpickr-range"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" name="date_range" value="{{ request('date_range') }}" />
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Select Job Title</label>
                        <select class="form-select select2" name="job_title">
                            <option value="" {{ request('date_range') == '' ? 'selected' : '' }}>Select</option>
                            @forelse($jobTitles as $jobTitle)
                                <option value="{{ $jobTitle->id }}"
                                    {{ request('date_range') == $jobTitle->id ? 'selected' : '' }}>{{ $jobTitle->title }}
                                </option>
                            @empty
                            @endforelse
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Status</label>
                        <select class="form-select select2" name="status">
                            <option value="" {{ request('status') == '' ? 'selected' : '' }}>Select</option>
                            @forelse($status as $value)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                    {{ $value }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Feedback --}}
    @include('recruitment.partials.modal.feedback')

    <!-- END: MODAL-->
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>
    <script>
        function setFeedbackModal(interviewId) {
            document.getElementById('interview-id').value = interviewId;

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('feedback'));
            modal.show();
        }
    </script>
@endsection
