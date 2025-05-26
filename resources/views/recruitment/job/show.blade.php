@extends('layouts.app')
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Job View</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a
                                                href="{{ route('recruitment.hr-dashboard') }}">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">View</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('recruitment.jobs') }}" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</a>

                            @if ($job->status == App\Helpers\CommonHelper::OPEN && $user->id == $job->created_by)
                                <a href="{{ route('recruitment.jobs.candidates', ['id' => $job->id]) }}"
                                    class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="users"></i> Assign
                                    Candidate</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">

                                <div class="col-md-8">

                                    <div class="card">
                                        <div class="card-body customernewsection-form">

                                            <div class="row">
                                                <div class="col-md-7">
                                                    <div class="new-applicayion-help-txt pb-25">
                                                        <h4 class="purchase-head text-start">{{ $job->job_title_name }}</h4>
                                                        <h6 class="mt-1 font-small-3"><span style="color: #999">Submitted
                                                                on: <span
                                                                    class="badge rounded-pill badge-light-secondary rounded me-1">{{ $job->created_at ? App\Helpers\CommonHelper::dateFormat($job->created_at) : '' }}</span>
                                                        </h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-5 text-end d-sm-flex flex-sm-column align-items-sm-end">
                                                    <span
                                                        class="badge rounded-pill fw-bold px-2 py-50 badge-light-success rounded"><i
                                                            data-feather="check-circle"></i>
                                                        {{ ucfirst($job->status) }}</span>
                                                </div>
                                                <div class="col-md-12">
                                                    <h6 class="job-descroption-detail">
                                                        @if (Str::length(strip_tags($job->job_description)) > 100)
                                                            {!! Str::limit(strip_tags($job->job_description), 100, '') !!}
                                                            <a href="#" data-bs-toggle="modal"
                                                                data-bs-target="#jobDescModal">...Read More</a>
                                                        @else
                                                            {!! $job->job_description !!}
                                                        @endif
                                                    </h6>
                                                </div>
                                            </div>

                                            <ul class="nav nav-tabs border-bottom loandetailhistory mb-0">
                                                <li class="nav-item">
                                                    <a class="nav-link active candidate-tab" href="#assigned" id="assigned"
                                                        onclick="fetchCandidateList('{{ App\Helpers\CommonHelper::ASSIGNED }}')">
                                                        New Candidates ({{ $job->newCanidatesCount }})
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link candidate-tab" id="qualified" href="#qualified"
                                                        onclick="fetchCandidateList('{{ App\Helpers\CommonHelper::QUALIFIED }}')">
                                                        Qualified ({{ $job->qualifiedCanidatesCount }})
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link candidate-tab" id="not-qualified"
                                                        href="#not-qualified"
                                                        onclick="fetchCandidateList('{{ App\Helpers\CommonHelper::NOT_QUALIFIED }}')">
                                                        Not Qualified({{ $job->notqualifiedCanidatesCount }})
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link candidate-tab" id="onhold" href="#onhold"
                                                        onclick="fetchCandidateList('{{ App\Helpers\CommonHelper::ONHOLD }}')">
                                                        On Hold({{ $job->onholdCanidatesCount }})
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link candidate-tab" id="scheduled" href="#scheduled"
                                                        onclick="fetchCandidateList('{{ App\Helpers\CommonHelper::SCHEDULED }}')">
                                                        Scheduled({{ $job->scheduledInterviewCount }})
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link candidate-tab" id="selected" href="#selected"
                                                        onclick="fetchCandidateList('{{ App\Helpers\CommonHelper::SELECTED }}')">
                                                        Selected({{ $job->selectedCandidateCount }})
                                                    </a>
                                                </li>
                                            </ul>
                                            <div class="tab-content mt-1">
                                                <div class="tab-pane active" id="newcand"></div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body customernewsection-form">
                                            <div class="exp-heade-top"><i data-feather="users"></i> <strong>Job
                                                    Details</strong> <i data-feather="arrow-down" class="float-end"></i>
                                            </div>
                                            <div class="px-1 mt-2 expempinfodetail">
                                                <div class="row">
                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">Job Type </label>
                                                        <h6 class="fw-bolder text-dark"><span
                                                                class="badge rounded-pill badge-light-success badgeborder-radius font-small-2">{{ ucfirst($job->publish_for) }}</span>
                                                        </h6>
                                                    </div>
                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">Employeement Type </label>
                                                        <h6 class="fw-bolder text-dark"><span
                                                                class="badge rounded-pill badge-light-primary badgeborder-radius font-small-2">{{ ucfirst($job->employement_type) }}</span>
                                                        </h6>
                                                    </div>
                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">Industry</label>
                                                        <h6 class="fw-bolder text-dark">{{ $job->industry_name }}</span>
                                                        </h6>
                                                    </div>
                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">Education</label>
                                                        <h6 class="fw-bolder text-dark">{{ $job->education_name }}
                                                        </h6>
                                                    </div>

                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">Work Experience</label>
                                                        <h6 class="fw-bolder text-dark">{{ $job->work_exp_min }} -
                                                            {{ $job->work_exp_max }} year</h6>
                                                    </div>
                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">Notice Period</label>
                                                        <h6 class="fw-bolder text-dark">{{ $job->notice_peroid_id }}</h6>
                                                    </div>

                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">Job Location </label>
                                                        <h6 class="fw-bolder text-dark">{{ $job->location_name }}</h6>
                                                    </div>

                                                    <div class="col-md-12 mb-50">
                                                        <label class="form-label">Required Skills</label>
                                                        <h6 class="fw-bolder text-dark">
                                                            @forelse($jobSkills as $skill)
                                                                <span
                                                                    class="badge rounded-pill badge-light-secondary badgeborder-radius font-small-2">{{ $skill }}</span>
                                                            @empty
                                                            @endforelse
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-body customernewsection-form">

                                            <div class="exp-heade-top"><i data-feather="arrow-right-circle"></i>
                                                <strong>Job Activity</strong> <i data-feather="arrow-down"
                                                    class="float-end"></i>
                                            </div>
                                            <div
                                                class="mt-2 bg-light step-custhomapp p-1 customerapptimelines customerapptimelinesapprovalpo expviewdetailsher">
                                                <ul class="timeline ms-50 newdashtimline ">
                                                    @forelse ($jobLogs as $log)
                                                        @php
                                                            if (
                                                                in_array($log->status, [
                                                                    App\Helpers\CommonHelper::ONHOLD,
                                                                    App\Helpers\CommonHelper::NOT_QUALIFIED,
                                                                ])
                                                            ) {
                                                                $logClassName = 'badge-light-danger';
                                                                $timeLineClass = 'timeline-point-danger';
                                                            } elseif (
                                                                $log->status == App\Helpers\CommonHelper::QUALIFIED
                                                            ) {
                                                                $logClassName = 'badge-light-success';
                                                                $timeLineClass = 'timeline-point-success';
                                                            } else {
                                                                $logClassName = 'badge-light-success';
                                                                $timeLineClass = 'timeline-point-success';
                                                            }
                                                        @endphp
                                                        <li class="timeline-item">
                                                            <span
                                                                class="timeline-point {{ $timeLineClass }} timeline-point-indicator"></span>
                                                            <div class="timeline-event">
                                                                <div
                                                                    class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                    @if ($log->log_type == App\Helpers\CommonHelper::CANDIDATE || $log->log_type == App\Helpers\CommonHelper::INTERVIEW)
                                                                        <h6>{{ $log->candidate_name }}</h6>
                                                                    @else
                                                                        <h6>{{ $log->action_by_name }}</h6>
                                                                    @endif
                                                                    <span
                                                                        class="badge rounded-pill {{ $logClassName }}">{{ ucwords(str_replace('-', ' ', $log->status)) }}</span>
                                                                </div>
                                                                <h5>({{ $log->created_at->diffForHumans() }})</h5>
                                                                <p>{{ $log->log_message }}</p>
                                                            </div>
                                                        </li>
                                                    @empty
                                                    @endforelse
                                                </ul>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->

    <!-- Job Description Modal -->
    <div class="modal fade" id="jobDescModal" tabindex="-1" aria-labelledby="jobDescModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="jobDescModalLabel">Job
                        Description
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">
                    {!! $job->job_description !!}
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Modal --}}
    @include('recruitment.partials.modal.status-modal', [
        'job' => $job,
    ])

    {{-- Schedule Interview --}}
    @include('recruitment.partials.modal.schedule-interview', [
        'job' => $job,
    ])

    {{-- Feedback --}}
    {{-- @include('recruitment.partials.modal.feedback',[
    'job' => $job
]) --}}

    {{-- Feedback --}}
    @include('recruitment.partials.modal.hr-feedback', [
        'job' => $job,
    ])
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetchCandidateList();
        });

        function fetchCandidateList(status = 'assigned') {
            $('.candidate-tab').removeClass('active');
            $('#' + status).addClass('active');
            const jobId = "{{ $job->id }}";
            const url = `{{ route('recruitment.jobs.fetch-candidates', [':jobId', ':status']) }}`
                .replace(':jobId', jobId)
                .replace(':status', status);

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    page: 'job-view-hr'
                },
                success: function(response) {
                    $('#newcand').html(response); // You can adjust this to target the right tab
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }

                    const firstCandidateId = $('#newcand .employee-boxnew').first().attr('id').replace(
                        'candSec', '');
                    if (firstCandidateId) {
                        if (status == 'scheduled' || status == 'selected') {
                            fetchCandidateInterviewDetail(firstCandidateId, jobId,
                                status); // Show the details of the first candidate
                        } else {
                            fetchCandidateDetail(firstCandidateId,
                                jobId); // Show the details of the first candidate
                        }
                        $('#candSec' + firstCandidateId).addClass('active'); // Highlight the first candidate
                    }

                },
                error: function(xhr) {
                    console.error('Failed to load candidates for status:', status);
                }
            });
        }

        function fetchCandidateInterviewDetail(id, jobId, status) {
            // Remove active from all
            $('.employee-boxnew').removeClass('active');

            // Add active to the selected candidate block
            $('#candSec' + id).addClass('active');

            if (id !== '') {
                const url = '{{ route('recruitment.jobs.candidate-interview-detail', [':id', ':jobId']) }}'
                    .replace(':id', id)
                    .replace(':jobId', jobId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        page: 'job-view-hr',
                        status: status
                    },
                    success: function(response) {
                        $('#emp-detail-div').html(response);
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
                $('#emp-detail-div').html('');
            }
        }

        function fetchCandidateDetail(id, jobId) {
            // Remove active from all
            $('.employee-boxnew').removeClass('active');

            // Add active to the selected candidate block
            $('#candSec' + id).addClass('active');

            if (id !== '') {
                const url = '{{ route('recruitment.jobs.candidate-detail', [':id', ':jobId']) }}'
                    .replace(':id', id)
                    .replace(':jobId', jobId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        page: 'job-view-hr'
                    },
                    success: function(response) {
                        $('#emp-detail-div').html(response);
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
                $('#emp-detail-div').html('');
            }
        }
    </script>


    <script>
        function setStatusModal(status, candidateId) {
            // Set the status input value
            document.getElementById('status-input').value = status;
            document.getElementById('candidate-id-input').value = candidateId;

            // Change the modal title based on the status
            const modalTitle = document.getElementById('status-modal-title');
            const modalDescription = document.getElementById('status-modal-description');

            switch (status) {
                case 'Qualified':
                    modalTitle.textContent = 'Mark as Qualified';
                    modalDescription.textContent = 'Please provide remarks for this candidate being marked as Qualified.';
                    break;
                case 'Not Qualified':
                    modalTitle.textContent = 'Mark as Not Qualified';
                    modalDescription.textContent =
                        'Please provide remarks for this candidate being marked as Not Qualified.';
                    break;
                case 'On Hold':
                    modalTitle.textContent = 'Mark as On Hold';
                    modalDescription.textContent = 'Please provide remarks for this candidate being put On Hold.';
                    break;
            }

            const form = document.getElementById('status-form');
            form.action = `{{ url('/recruitment/jobs/update-candidate-status') }}`;

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('status-modal'));
            modal.show();
        }

        function setScheduleInterviewModal(status, candidateId) {
            document.getElementById('interview-candidate-id').value = candidateId;

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('schedule-interview-modal'));
            modal.show();
        }

        // function setFeedbackModal(interviewId) {
        //     document.getElementById('interview-id').value = interviewId;

        //     // Show the modal
        //     const modal = new bootstrap.Modal(document.getElementById('feedback'));
        //     modal.show();
        // }

        function setHrFeedbackModal(interviewId) {
            document.getElementById('interview-id').value = interviewId;

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('feedback'));
            modal.show();
        }
    </script>
@endsection
