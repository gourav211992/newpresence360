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
                            <h2 class="content-header-title float-start mb-0">Job Created</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('recruitment.hr-dashboard') }}">Home</a>
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
                        @if ($user->user_type !== App\Helpers\CommonHelper::IAM_VENDOR)
                            <a href="{{ route('recruitment.jobs.create') }}" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="plus-square"></i> Create Job</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="content-body dasboardnewbody">

                <section id="chartjs-chart">
                    <div class="row">

                        @include('recruitment.job.summary', [
                            'jobCount' => $jobCount,
                            'candidatesCount' => $candidatesCount,
                            'interviewCount' => $interviewCount,
                            'selectedCandidatesCount' => $selectedCandidatesCount,
                            'closedJobCount' => $closedJobCount,
                        ])

                        <div class="col-md-12 col-12">
                            <div class="card  new-cardbox">
                                @include('recruitment.job.tab', [
                                    'jobCount' => $jobCount,
                                    'candidatesCount' => $candidatesCount,
                                ])

                                <div class="tab-content">
                                    <div class="tab-pane active" id="Requested">
                                        <div class="table-responsive candidates-tables">
                                            @include('recruitment.partials.card-header')
                                            <table
                                                class="datatables-basic table table-striped myrequesttablecbox loanapplicationlist">
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
                                                            <td class="text-nowrap">
                                                                {{ $interview->date_time ? App\Helpers\CommonHelper::dateFormat($interview->date_time) : '' }}
                                                            </td>
                                                            <td class="fw-bolder text-dark">
                                                                {{ isset($interview->job->job_id) ? $interview->job->job_id : '' }}
                                                            </td>
                                                            <td>
                                                                <a
                                                                    href="{{ route('recruitment.jobs.show', ['id' => $interview->job_id]) }}">
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
                                                                            href="{{ route('recruitment.jobs.show', ['id' => $interview->job_id]) }}">
                                                                            <i data-feather="eye" class="me-50"></i>
                                                                            <span>View Detail</span>
                                                                        </a>
                                                                        @if (isset($interview->job) && $interview->job->status == 'open' && $user->id == $interview->job->created_by)
                                                                            <a class="dropdown-item" href="javascript:;"
                                                                                data-bs-target="#status-modal"
                                                                                data-bs-toggle="modal"
                                                                                data-status="{{ App\Helpers\CommonHelper::CLOSED }}"
                                                                                data-title="Closed Job"
                                                                                data-id="{{ $interview->job_id }}">
                                                                                <i data-feather="trash-2"
                                                                                    class="me-50"></i>
                                                                                <span>Closed</span>
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
    <!-- BEGIN: FILTER MODAL-->

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
                            <option value="" {{ request('job_title') == '' ? 'selected' : '' }}>Select</option>
                            @forelse($jobTitles as $jobTitle)
                                <option value="{{ $jobTitle->id }}"
                                    {{ request('job_title') == $jobTitle->id ? 'selected' : '' }}>{{ $jobTitle->title }}
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
                                    {{ ucwords($value) }}
                                </option>
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

    <div class="modal fade" id="status-modal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="status-modal-title"></h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form role="post-data" method="POST" id="status-form" action=""
                    redirect="{{ route('recruitment.jobs') }}">
                    <div class="modal-body pb-2">
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <input type="hidden" name="status" class="form-control" value=""
                                        id="status-input">
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="log_message"></textarea>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                        <button type="button" class="btn btn-primary" data-request="confirm-and-save"
                            data-target="[role=post-data]">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END: MODAL-->
@endsection
@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('[data-bs-toggle="modal"]');

            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    const status = this.getAttribute('data-status');
                    const title = this.getAttribute('data-title');
                    const jobId = this.getAttribute('data-id');

                    const statusInput = document.getElementById('status-input');
                    statusInput.value = status;

                    const modalTitle = document.querySelector('#status-modal-title');
                    modalTitle.textContent = title;

                    const form = document.getElementById('status-form');
                    form.setAttribute('data-message', `Do you want to ${title}?`);
                    form.action = `{{ url('/recruitment/jobs/update-status/${jobId}') }}`;
                });
            });
        });
    </script>
@endsection
