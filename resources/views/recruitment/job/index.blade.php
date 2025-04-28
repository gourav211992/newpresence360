@extends('recruitment.layouts.app')

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
                        <h2 class="content-header-title float-start mb-0">Job Created</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('recruitment.dashboard') }}">Home</a>
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
                    <a href="{{ route('recruitment.jobs.create') }}" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i
                            data-feather="plus-square"></i> Create Job</a>
                </div>
            </div>
        </div>
        <div class="content-body dasboardnewbody">

            <section id="chartjs-chart">
                <div class="row">

                    <div class="col-xl-12 col-md-6 col-12">
                        <div class="card card-statistics">
                            <div class="card-header newheader pb-0">
                                <div class="header-left">
                                    <h4 class="card-title">Summary - <span class="font-small-3 fw-bold"
                                            style="font-color:#999">As on 27-09-2024</span></h4>
                                </div>
                            </div>
                            <div class="card-body statistics-body">
                                <div class="row">
                                    <div class="col  mb-2 mb-xl-0">
                                        <div class="d-flex flex-row">
                                            <div class="avatar bg-light-primary me-2">
                                                <div class="avatar-content">
                                                    <i data-feather="trending-up" class="avatar-icon"></i>
                                                </div>
                                            </div>
                                            <div class="my-auto">
                                                <h4 class="fw-bolder mb-0">{{ $jobCount }}</h4>
                                                <p class="card-text font-small-3 mb-0">Job Created</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col  mb-2 mb-sm-0">
                                        <div class="d-flex flex-row">
                                            <div class="avatar bg-light-success me-2">
                                                <div class="avatar-content">
                                                    <i data-feather="user-check" class="avatar-icon"></i>
                                                </div>
                                            </div>
                                            <div class="my-auto">
                                                <h4 class="fw-bolder mb-0">{{ $candidatesCount }}</h4>
                                                <p class="card-text font-small-3 mb-0">Candidate Assigned</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col  mb-2 mb-xl-0">
                                        <div class="d-flex flex-row">
                                            <div class="avatar bg-light-info me-2">
                                                <div class="avatar-content">
                                                    <i data-feather="calendar" class="avatar-icon"></i>
                                                </div>
                                            </div>
                                            <div class="my-auto">
                                                <h4 class="fw-bolder mb-0">0</h4>
                                                <p class="card-text font-small-3 mb-0">Scheduled</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col  ">
                                        <div class="d-flex flex-row">
                                            <div class="avatar bg-light-warning me-2">
                                                <div class="avatar-content">
                                                    <i data-feather="check-circle" class="avatar-icon"></i>
                                                </div>
                                            </div>
                                            <div class="my-auto">
                                                <h4 class="fw-bolder mb-0">0</h4>
                                                <p class="card-text font-small-3 mb-0">Select Candidates</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col  ">
                                        <div class="d-flex flex-row">
                                            <div class="avatar bg-light-danger me-2">
                                                <div class="avatar-content">
                                                    <i data-feather="x-circle" class="avatar-icon"></i>
                                                </div>
                                            </div>
                                            <div class="my-auto">
                                                <h4 class="fw-bolder mb-0">0</h4>
                                                <p class="card-text font-small-3 mb-0">Job Closed</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-12 col-12">
                        <div class="card  new-cardbox">
                            @include('recruitment.job.tab',[
                                'jobCount' => $jobCount,
                                'candidatesCount' => $candidatesCount,
                            ])
                            
                            <div class="tab-content">
                                @if(\Request::route()->getName() == 'recruitment.jobs.assigned-candidate')
                                    @include('recruitment.job.assgined-candidate',[
                                            'jobs' => $jobs
                                    ])
                                @else
                                    @include('recruitment.job.job-created',[
                                            'jobs' => $jobs
                                    ])
                                @endif
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
<div class="modal fade" id="skillModal" tabindex="-1" aria-labelledby="skillModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">All Skills</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="skillModalBody">
                <!-- Skills will be injected here -->
            </div>
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
            <div class="modal-body flex-grow-1">
                <div class="mb-1">
                    <label class="form-label" for="fp-range">Select Date Range</label>
                    <input type="text" id="fp-range" class="form-control flatpickr-range"
                        placeholder="YYYY-MM-DD to YYYY-MM-DD" name="date_range" value="{{ request('date_range') }}"/>
                </div>

                <div class="mb-1">
                    <label class="form-label">Select Job Title</label>
                    <select class="form-select select2" name="job_title">
                        <option value="" {{ request('date_range') == "" ? 'selected' : '' }}>Select</option>
                        @forelse($jobTitles as $jobTitle)
                            <option value="{{ $jobTitle->id }}" {{ request('date_range') == $jobTitle->id ? 'selected' : '' }}>{{ $jobTitle->title }}</option>
                        @empty
                        @endforelse
                    </select>
                </div>


                <div class="mb-1">
                    <label class="form-label">Skills</label>
                    <select class="form-select select2" name="skill">
                        <option value="" {{ request('skill') == "" ? 'selected' : '' }}>Select</option>
                        @forelse($skills as $skill)
                            <option value="{{ $skill->id }}" {{ request('skill') == $skill->id ? 'selected' : '' }}>{{ $skill->name }}</option>
                        @empty
                        @endforelse
                    </select>
                </div>

                <div class="mb-1">
                    <label class="form-label">Status</label>
                    <select class="form-select select2" name="status">
                        <option value="" {{ request('status') == "" ? 'selected' : '' }}>Select</option>
                        @forelse($status as $value)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $value }}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
            </div>
            <div class="modal-footer justify-content-start">
                <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                <a href="{{ route('recruitment.jobs') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<!-- END: MODAL-->
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const skillModal = document.getElementById('skillModal');
    skillModal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        const skills = JSON.parse(trigger.getAttribute('data-skills'));

        const body = skillModal.querySelector('#skillModalBody');
        body.innerHTML = ''; // Clear old content

        skills.forEach(skill => {
            const badge = `<span class="badge rounded-pill badge-light-secondary badgeborder-radius me-1 mb-1">${skill}</span>`;
            body.innerHTML += badge;
        });
    });
});
</script>
@endsection