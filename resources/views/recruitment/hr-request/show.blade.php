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
                                <h2 class="content-header-title float-start mb-0">Request</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a
                                                href="{{ route('recruitment.hr-dashboard') }}">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">View Detail</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a class="btn btn-secondary btn-sm mb-50 mb-sm-0"
                                href="{{ route('recruitment.request-hr') }}"><i data-feather="arrow-left-circle"></i>
                                Back</a>

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
                                                        <h4 class="purchase-head text-start">
                                                            {{ $jobRequest->job_title_name }}</h4>
                                                        <h6 class="mt-1 font-small-3"><span style="color: #999">Submitted
                                                                on: <span
                                                                    class="badge rounded-pill badge-light-secondary rounded me-1">{{ $jobRequest->created_at ? App\Helpers\CommonHelper::dateFormat($jobRequest->created_at) : '' }}</span>
                                                        </h6>
                                                    </div>
                                                </div>
                                                <div class="col-md-5 text-end d-sm-flex flex-sm-column align-items-sm-end">
                                                    @php
                                                        if (
                                                            in_array($jobRequest->status, [
                                                                App\Helpers\CommonHelper::PENDING,
                                                                App\Helpers\CommonHelper::REJECTED,
                                                            ])
                                                        ) {
                                                            $className = 'badge-light-danger';
                                                        } elseif (
                                                            $jobRequest->status ==
                                                            App\Helpers\CommonHelper::FINAL_APPROVED
                                                        ) {
                                                            $className = 'badge-light-success';
                                                        } elseif (
                                                            $jobRequest->status ==
                                                            App\Helpers\CommonHelper::APPROVED_FORWARD
                                                        ) {
                                                            $className = 'badge-light-primary';
                                                        } else {
                                                            $className = 'badge-light-success';
                                                        }
                                                    @endphp
                                                    <span
                                                        class="badge rounded-pill fw-bold px-2 py-50 {{ $className }} rounded">
                                                        <i data-feather="check-circle"></i>
                                                        {{ ucwords(str_replace('-', ' ', $jobRequest->status)) }}

                                                        @if ($jobRequest->status == App\Helpers\CommonHelper::PENDING)
                                                            at {{ $jobRequest->approvar_name }}
                                                        @endif

                                                        @if ($jobRequest->action_by_name)
                                                            by {{ $jobRequest->action_by_name }}
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="col-md-12">
                                                    <h6 class="job-descroption-detail">
                                                        @if (strlen($jobRequest->job_description) > 100)
                                                            {{ Str::limit($jobRequest->job_description, 100, '') }}
                                                            <a href="#" data-bs-toggle="modal"
                                                                data-bs-target="#jobDescModal">...Read More</a>
                                                        @else
                                                            {{ $jobRequest->job_description }}
                                                        @endif
                                                    </h6>
                                                </div>
                                            </div>
                                            @if ($job)
                                                <ul class="nav nav-tabs border-bottom loandetailhistory mb-0">
                                                    <li class="nav-item">
                                                        <a class="nav-link active candidate-tab" href="#assigned"
                                                            id="assigned" onclick="fetchCandidateList('assigned')">
                                                            New Candidates ({{ $job->newCanidatesCount }})
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link candidate-tab" id="qualified" href="#qualified"
                                                            onclick="fetchCandidateList('qualified')">
                                                            Qualified ({{ $job->qualifiedCanidatesCount }})
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link candidate-tab" id="not-qualified"
                                                            href="#not-qualified"
                                                            onclick="fetchCandidateList('not-qualified')">
                                                            Not Qualified({{ $job->notqualifiedCanidatesCount }})
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link candidate-tab" id="onhold" href="#onhold"
                                                            onclick="fetchCandidateList('onhold')">
                                                            On Hold({{ $job->onholdCanidatesCount }})
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link candidate-tab" id="scheduled" href="#scheduled"
                                                            onclick="fetchCandidateList('scheduled')">
                                                            Scheduled({{ $job->scheduledInterviewCount }})
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link candidate-tab" id="selected" href="#selected"
                                                            onclick="fetchCandidateList('selected')">
                                                            Selected({{ $job->selectedCandidateCount }})
                                                        </a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content mt-1">
                                                    <div class="tab-pane active" id="newcand"></div>
                                                </div>
                                            @else
                                                <div class="tab-content mt-1">
                                                    <div class="tab-pane active" id="newcand">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="employee-boxnew p-1">
                                                                    <div class="card">
                                                                        <div class="card-body emplodetainfocard pb-0">
                                                                            <div
                                                                                class="employee-box text-center border-0 my-4">
                                                                                @if (in_array($jobRequest->status, [App\Helpers\CommonHelper::PENDING, App\Helpers\CommonHelper::APPROVED_FORWARD]))
                                                                                    <span>Approval Pending at
                                                                                        {{ $jobRequest->approvar_name }}</span>
                                                                                @elseif (!$jobRequest->job_id && $jobRequest->status == App\Helpers\CommonHelper::FINAL_APPROVED)
                                                                                    <h2>No Job Created</h2>
                                                                                @endif
                                                                            </div>
                                                                            <div class="empl-detail-info mb-5">
                                                                                <div class="row">
                                                                                    <div class="col-md-12 mt-2 text-center">
                                                                                        @if (!$jobRequest->job_id && $jobRequest->status == App\Helpers\CommonHelper::FINAL_APPROVED)
                                                                                            <a href="{{ route('recruitment.jobs.create') }}"
                                                                                                class="btn btn-primary btn-primary-new me-1  mb-50 mb-sm-0"><i
                                                                                                    data-feather="check-circle"></i>
                                                                                                Create Job Now </a>
                                                                                        @endif
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
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body customernewsection-form">

                                            <div class="exp-heade-top">
                                                <i data-feather="users"></i>
                                                <strong>Request Details</strong>
                                                <i data-feather="arrow-down" class="float-end"></i>
                                            </div>
                                            <div class="px-1 mt-2 expempinfodetail">
                                                <div class="row">
                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">Request ID </label>
                                                        <h6 class="fw-bolder text-dark"><span
                                                                class="badge rounded-pill badge-light-success badgeborder-radius font-small-2">{{ $jobRequest->request_id }}</span>
                                                        </h6>
                                                    </div>
                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">Request Type </label>
                                                        <h6 class="fw-bolder text-dark"><span
                                                                class="badge rounded-pill badge-light-success badgeborder-radius font-small-2">{{ ucfirst($jobRequest->job_type) }}</span>
                                                        </h6>
                                                    </div>
                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">Priority </label>
                                                        @php
                                                            if (
                                                                $jobRequest->priority == App\Helpers\CommonHelper::HIGH
                                                            ) {
                                                                $className = 'badge-light-danger';
                                                            } elseif (
                                                                $jobRequest->priority ==
                                                                App\Helpers\CommonHelper::MEDIUM
                                                            ) {
                                                                $className = 'badge-light-warning';
                                                            } else {
                                                                $className = 'badge-light-primary';
                                                            }
                                                        @endphp
                                                        <h6 class="fw-bolder text-dark"><span
                                                                class="badge rounded-pill {{ $className }} badgeborder-radius font-small-2">{{ ucfirst($jobRequest->priority) }}</span>
                                                        </h6>
                                                    </div>
                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">No. of Position</label>
                                                        <h6 class="fw-bolder text-dark">
                                                            {{ $jobRequest->no_of_position }}</span></h6>
                                                    </div>
                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">Education</label>
                                                        <h6 class="fw-bolder text-dark">{{ $jobRequest->education_name }}
                                                        </h6>
                                                    </div>

                                                    @if (count($requestCertifications) > 0)
                                                        <div class="col-md-6 mb-50">
                                                            <label class="form-label">Certification </label>
                                                            <h6 class="fw-bolder text-dark">
                                                                {{ $jobRequest->certification_name }}</h6>
                                                            @forelse($requestCertifications as $certification)
                                                                <span
                                                                    class="badge rounded-pill badge-light-secondary badgeborder-radius font-small-2">{{ $certification }}</span>
                                                            @empty
                                                            @endforelse
                                                        </div>
                                                    @endif

                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">Work Experience</label>
                                                        <h6 class="fw-bolder text-dark">
                                                            {{ $jobRequest->work_experience_name }}</h6>
                                                    </div>

                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">Expected D.O.J</label>
                                                        <h6 class="fw-bolder text-dark">
                                                            {{ $jobRequest->expected_doj ? App\Helpers\CommonHelper::dateFormat($jobRequest->expected_doj) : '' }}
                                                        </h6>
                                                    </div>

                                                    <div class="col-md-6 mb-50">
                                                        <label class="form-label">Job Location </label>
                                                        <h6 class="fw-bolder text-dark">
                                                            {{ $jobRequest->placed_location_name }}</h6>
                                                    </div>

                                                    <div class="col-md-12 mb-50">
                                                        <label class="form-label">Required Skills</label>
                                                        <h6 class="fw-bolder text-dark">
                                                            @forelse($requestSkills as $skill)
                                                                <span
                                                                    class="badge rounded-pill badge-light-secondary badgeborder-radius font-small-2">{{ $skill }}</span>
                                                            @empty
                                                            @endforelse
                                                        </h6>
                                                    </div>

                                                    <div class="col-md-12 mb-50">
                                                        <label class="form-label">Reason</label>
                                                        <h6 class="fw-bolder text-dark">
                                                            {{ $jobRequest->reason }}
                                                        </h6>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-body customernewsection-form">

                                            <div class="exp-heade-top"><i data-feather="arrow-right-circle"></i>
                                                <strong>Request History</strong> <i data-feather="arrow-down"
                                                    class="float-end"></i>
                                            </div>
                                            <div
                                                class="mt-2 bg-light step-custhomapp p-1 customerapptimelines customerapptimelinesapprovalpo expviewdetailsher">
                                                <ul class="timeline ms-50 newdashtimline ">
                                                    @forelse ($jobRequestLogs as $log)
                                                        @php
                                                            if (
                                                                in_array($log->status, [
                                                                    App\Helpers\CommonHelper::PENDING,
                                                                    App\Helpers\CommonHelper::REJECTED,
                                                                ])
                                                            ) {
                                                                $logClassName = 'badge-light-danger';
                                                                $timeLineClass = 'timeline-point-danger';
                                                            } elseif (
                                                                $log->status == App\Helpers\CommonHelper::FINAL_APPROVED
                                                            ) {
                                                                $logClassName = 'badge-light-success';
                                                                $timeLineClass = 'timeline-point-success';
                                                            } elseif (
                                                                $log->status ==
                                                                App\Helpers\CommonHelper::APPROVED_FORWARD
                                                            ) {
                                                                $logClassName = 'badge-light-primary';
                                                                $timeLineClass = 'timeline-point-primary';
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
                                                                    <h6>{{ $log->action_by_name }}</h6>
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
                    {{ $jobRequest->job_description }}
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>

    @if ($job)
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
                                fetchCandidateInterviewDetail(firstCandidateId, jobId, status);
                            } else {
                                fetchCandidateDetail(firstCandidateId, jobId);
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
    @endif
@endsection
