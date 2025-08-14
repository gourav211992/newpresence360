@extends('recruitment.layouts.app')

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
                                                href="{{ route('recruitment.hr-dashboard') }}">Job</a>
                                        </li>
                                        <li class="breadcrumb-item active">View</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('recruitment.my-referal') }}"
                                class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
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

                                <div class="col-md-12">

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
                                            <div class="tab-content mt-1">
                                                <div class="tab-pane active" id="newcand">
                                                    <div class="row match-height">
                                                        @if ($referral)
                                                            <div class="col-md-4">
                                                                <div class="employee-boxnew p-1">
                                                                    <div class="card">
                                                                        <div class="card-body emplodetainfocard pb-0">
                                                                            <div class="employee-box text-center">
                                                                                <div
                                                                                    style="background-color: #ddb6ff;color: #6b12b7;line-height: 63px;width: 70px;height: 70px;border-radius: 5px;position: relative;font-size: xx-large;text-align: center;margin-left: 190px;margin-bottom: 15px;font-weight: bold;">
                                                                                    {{ $referral->name ? strtoupper(substr($referral->name, 0, 1)) : '' }}
                                                                                </div>
                                                                                <h2>{{ $referral->name ? $referral->name : '' }}
                                                                                </h2>
                                                                                <span>{{ $job->job_title_name }}</span>

                                                                                <div class="row justify-content-center">
                                                                                    <div class="col-md-6 col-6 pe-0">
                                                                                        <div class="empl-info">
                                                                                            <div class="iconcode">
                                                                                                <i data-feather="calendar"
                                                                                                    class="text-primary-new"></i>
                                                                                            </div>
                                                                                            <div class="emp-infpdet">
                                                                                                <h3>
                                                                                                    {{ $referral->created_at ? App\Helpers\CommonHelper::dateFormat($referral->created_at) : '' }}
                                                                                                </h3>
                                                                                                <h4>Applied Date</h4>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-md-4 col-6">
                                                                                        <div class="empl-info">
                                                                                            <div class="iconcode">
                                                                                                <i data-feather="briefcase"
                                                                                                    class="text-primary-new"></i>
                                                                                            </div>
                                                                                            <div class="emp-infpdet">
                                                                                                <h3>{{ $referral->work_exp ? $referral->work_exp : '' }}
                                                                                                </h3>
                                                                                                <h4>Exp.</h4>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                </div>
                                                                            </div>

                                                                            <div class="empl-detail-info">
                                                                                <h3>DETAILS</h3>
                                                                                @php
                                                                                    $resumePath = isset(
                                                                                        $referral->resume_path,
                                                                                    )
                                                                                        ? $referral->resume_path
                                                                                        : '';
                                                                                @endphp
                                                                                <a href="{{ url('/') . '/' . $resumePath }}"
                                                                                    class="text-danger download-resume"><img
                                                                                        src="{{ asset('img/resume.png') }}" /></a>
                                                                                <div class="row">
                                                                                    <div class="col-6">
                                                                                        <p>Email</p>
                                                                                        <h5>{{ $referral->email ? $referral->email : '' }}
                                                                                        </h5>
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <p>Phone No</p>
                                                                                        <h5>{{ $referral->mobile_no ? $referral->mobile_no : '' }}
                                                                                        </h5>
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <p>Location</p>
                                                                                        <h5>{{ $referral->location_name ? $referral->location_name : 'N/A' }}
                                                                                        </h5>
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <p>Skill Set</p>
                                                                                        @php
                                                                                            $skills = isset(
                                                                                                $referral->candidateSkills,
                                                                                            )
                                                                                                ? $referral->candidateSkills
                                                                                                : [];
                                                                                        @endphp
                                                                                        @forelse($skills as $skill)
                                                                                            <h5><span
                                                                                                    class="badge rounded-pill badge-light-secondary badgeborder-radius font-small-2">{{ $skill->name }}</span>
                                                                                            </h5>
                                                                                        @empty
                                                                                            <h5>N/A</h5>
                                                                                        @endforelse

                                                                                    </div>

                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                </div>


                                                            </div>
                                                        @else
                                                            <div class="col-md-4">
                                                                <div class="employee-boxnew p-1">
                                                                    <div class="card">
                                                                        <div class="card-body emplodetainfocard pb-0">
                                                                            <div
                                                                                class="employee-box text-center border-0 my-4">
                                                                                <h2>No Candidate found</h2>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <div class="col-md-4">
                                                            <div class="employee-boxnew h-100">
                                                                <div class="card">
                                                                    <div class="card-body customernewsection-form">

                                                                        <div class="exp-heade-top"><i
                                                                                data-feather="users"></i>
                                                                            <strong>Job
                                                                                Details</strong> <i
                                                                                data-feather="arrow-down"
                                                                                class="float-end"></i>
                                                                        </div>
                                                                        <div class="px-1 mt-2 expempinfodetail">
                                                                            <div class="row">
                                                                                <div class="col-md-6 mb-50">
                                                                                    <label class="form-label">Job Type
                                                                                    </label>
                                                                                    <h6 class="fw-bolder text-dark"><span
                                                                                            class="badge rounded-pill badge-light-success badgeborder-radius font-small-2">{{ ucfirst($job->publish_for) }}</span>
                                                                                    </h6>
                                                                                </div>
                                                                                <div class="col-md-6 mb-50">
                                                                                    <label
                                                                                        class="form-label">Education</label>
                                                                                    <h6 class="fw-bolder text-dark">
                                                                                        {{ $job->education_name }}
                                                                                    </h6>
                                                                                </div>
                                                                                <div class="col-md-6 mb-50">
                                                                                    <label class="form-label">Work
                                                                                        Experience</label>
                                                                                    <h6 class="fw-bolder text-dark">
                                                                                        {{ $job->work_exp_min }} -
                                                                                        {{ $job->work_exp_max }} yrs
                                                                                    </h6>
                                                                                </div>
                                                                                <div class="col-md-6 mb-50">
                                                                                    <label class="form-label">Job Location
                                                                                    </label>
                                                                                    <h6 class="fw-bolder text-dark">
                                                                                        {{ $job->location_name }}
                                                                                    </h6>
                                                                                </div>

                                                                                <div class="col-md-12 mb-50">
                                                                                    <label class="form-label">Required
                                                                                        Skills</label>
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
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4">
                                                            <div class="employee-boxnew h-100">
                                                                <div class="card">
                                                                    <div class="card-body customernewsection-form">

                                                                        <div class="exp-heade-top"><i
                                                                                data-feather="arrow-right-circle"></i>
                                                                            <strong>Candidate Journey</strong> <i
                                                                                data-feather="arrow-down"
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
                                                                                            $logClassName =
                                                                                                'badge-light-danger';
                                                                                            $timeLineClass =
                                                                                                'timeline-point-danger';
                                                                                        } elseif (
                                                                                            $log->status ==
                                                                                            App\Helpers\CommonHelper::QUALIFIED
                                                                                        ) {
                                                                                            $logClassName =
                                                                                                'badge-light-success';
                                                                                            $timeLineClass =
                                                                                                'timeline-point-success';
                                                                                        } else {
                                                                                            $logClassName =
                                                                                                'badge-light-success';
                                                                                            $timeLineClass =
                                                                                                'timeline-point-success';
                                                                                        }
                                                                                    @endphp
                                                                                    <li class="timeline-item">
                                                                                        <span
                                                                                            class="timeline-point {{ $timeLineClass }} timeline-point-indicator"></span>
                                                                                        <div class="timeline-event">
                                                                                            <div
                                                                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                                                @if ($log->log_type == App\Helpers\CommonHelper::CANDIDATE || $log->log_type == App\Helpers\CommonHelper::INTERVIEW)
                                                                                                    <h6>{{ $log->candidate_name }}
                                                                                                    </h6>
                                                                                                @else
                                                                                                    <h6>{{ $log->action_by_name }}
                                                                                                    </h6>
                                                                                                @endif
                                                                                                <span
                                                                                                    class="badge rounded-pill {{ $logClassName }}">{{ ucwords(str_replace('-', ' ', $log->status)) }}</span>
                                                                                            </div>
                                                                                            <h5>({{ $log->created_at->diffForHumans() }})
                                                                                            </h5>
                                                                                            <p>{{ $log->log_message }}</p>
                                                                                        </div>
                                                                                    </li>
                                                                                @empty
                                                                                    <li class="timeline-item">
                                                                                        <h5 class="text-danger">No History
                                                                                            found</h5>
                                                                                    </li>
                                                                                @endforelse
                                                                            </ul>
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

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal to add new record -->
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
@endsection
