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
                            <h2 class="content-header-title float-start mb-0">Requests</h2>
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
                                                style="font-color:#999">{{ request('date_range') ? request('date_range') : 'As on ' . date('d-m-Y') }}</span>
                                        </h4>
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
                                                    <h4 class="fw-bolder mb-0">{{ $requestCount }}</h4>
                                                    <p class="card-text font-small-3 mb-0">Total Request</p>
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
                                                    <h4 class="fw-bolder mb-0">{{ $approvedRequestCount }}</h4>
                                                    <p class="card-text font-small-3 mb-0">Approved Request</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col  mb-2 mb-xl-0">
                                            <div class="d-flex flex-row">
                                                <div class="avatar bg-light-info me-2">
                                                    <div class="avatar-content">
                                                        <i data-feather="check-circle" class="avatar-icon"></i>
                                                    </div>
                                                </div>
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0">{{ $pendingRequestCount }}</h4>
                                                    <p class="card-text font-small-3 mb-0">Pending Request</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col  ">
                                            <div class="d-flex flex-row">
                                                <div class="avatar bg-light-warning me-2">
                                                    <div class="avatar-content">
                                                        <i data-feather="calendar" class="avatar-icon"></i>
                                                    </div>
                                                </div>
                                                <div class="my-auto">
                                                    <h4 class="fw-bolder mb-0">{{ $jobcreated }}</h4>
                                                    <p class="card-text font-small-3 mb-0">Job Created</p>
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
                                                    <h4 class="fw-bolder mb-0">{{ $rejectedRequestCount }}</h4>
                                                    <p class="card-text font-small-3 mb-0">Rejected Request</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-12 col-12">
                            <div class="card  new-cardbox">
                                {{-- Card Header --}}
                                <ul class="nav nav-tabs border-bottom" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link {{ \Request::route()->getName() == 'recruitment.requests' ? 'active' : '' }}"
                                            href="{{ route('recruitment.requests') }}">Requested
                                            &nbsp;<span>({{ $requestCount }})</span></a>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    <div class="tab-pane active">
                                        <div class="table-responsive candidates-tables">
                                            @include('recruitment.partials.card-header')
                                            <table class="table table-striped myrequesttablecbox loanapplicationlist">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Date</th>
                                                        <th>Job Id</th>
                                                        <th>Request Id</th>
                                                        <th>Job Type</th>
                                                        <th>Job Title</th>
                                                        <th>Education</th>
                                                        <th>Skills</th>
                                                        <th>Exp.</th>
                                                        <th>Request By</th>
                                                        <th>Expected D.O.J</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($requests as $request)
                                                        <tr>
                                                            <td>{{ $requests->firstItem() + $loop->index }}</td>
                                                            <td class="text-nowrap">
                                                                {{ $request->created_at ? App\Helpers\CommonHelper::dateFormat($request->created_at) : '' }}
                                                            </td>
                                                            <td class="fw-bolder text-dark">{{ $request->job_id }}</td>
                                                            <td class="fw-bolder text-dark">{{ $request->request_id }}</td>
                                                            <td>
                                                                <span
                                                                    class="badge rounded-pill badge-light-primary badgeborder-radius">
                                                                    {{ ucfirst($request->job_type) }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $request->job_title_name }}</td>
                                                            <td>{{ $request->education_name }}</td>
                                                            <td>
                                                                @php
                                                                    $skills = $request->recruitmentSkills;
                                                                @endphp
                                                                @foreach ($skills->take(2) as $skill)
                                                                    <span
                                                                        class="badge rounded-pill badge-light-secondary badgeborder-radius">
                                                                        {{ $skill->name }}
                                                                    </span>
                                                                @endforeach

                                                                @if ($skills->count() > 2)
                                                                    <a href="#" class="skilnum text-primary"
                                                                        data-bs-toggle="modal" data-bs-target="#skillModal"
                                                                        data-skills='@json($skills->pluck('name'))'>
                                                                        <span
                                                                            class="skilnum">+{{ $skills->count() - 2 }}</span>
                                                                    </a>
                                                                @endif
                                                            </td>
                                                            <td>{{ $request->work_experience_name }}</td>
                                                            <td class="">
                                                                <div class="d-flex flex-row">
                                                                    <div
                                                                        style="background-color: #ddb6ff; color: #6b12b7; line-height: 30px; width: 30px; height: 30px; border-radius: 50%; position: relative; font-size: 1rem; text-align: center; margin-right: 5px; font-weight: 600;">
                                                                        {{ strtoupper(substr($request->creator_name, 0, 1)) }}
                                                                    </div>
                                                                    <div class="my-auto">
                                                                        <h6
                                                                            class="mb-0 fw-bolder text-dark hr-dashemplname">
                                                                            {{ $request->creator_name }}</h6>
                                                                    </div>
                                                                </div>
                                                                @if ($request->approvar_name)
                                                                    <span class="text-danger">
                                                                        Pending at {{ $request->approvar_name }}
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $request->expected_doj ? App\Helpers\CommonHelper::dateFormat($request->expected_doj) : '' }}
                                                            </td>
                                                            <td>
                                                                @php
                                                                    if (
                                                                        in_array($request->status, [
                                                                            'approved-forward',
                                                                            'final-approved',
                                                                            'active',
                                                                        ])
                                                                    ) {
                                                                        $className = 'badge-light-success';
                                                                    } elseif (
                                                                        in_array($request->status, [
                                                                            'pending',
                                                                            'onhold',
                                                                            'rejected',
                                                                            'cancelled',
                                                                            'revoke',
                                                                            'inactive',
                                                                        ])
                                                                    ) {
                                                                        $className = 'badge-light-danger';
                                                                    } else {
                                                                        $className = 'badge-light-warning';
                                                                    }
                                                                @endphp
                                                                <span
                                                                    class="badge rounded-pill {{ $className }} badgeborder-radius">
                                                                    {{ ucwords(str_replace('-', ' ', $request->status)) }}
                                                                    @if ($request->action_by_name)
                                                                        by {{ $request->action_by_name }}
                                                                    @endif
                                                                </span>
                                                            </td>
                                                            <td class="tableactionnew">
                                                                <a class="dropdown-item"
                                                                    href="{{ route('recruitment.request-hr.show', ['id' => $request->id]) }}">
                                                                    <i data-feather="eye" class="me-50"></i>
                                                                </a>
                                                            </td>

                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td class="text-danger text-center" colspan="13">No
                                                                record(s) found.</td>
                                                        </tr>
                                                    @endforelse

                                                </tbody>
                                            </table>
                                        </div>

                                        {{-- Pagination --}}
                                        {{ $requests->appends(request()->input())->links('recruitment.partials.pagination') }}
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
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" name="date_range"
                            value="{{ request('date_range') }}" />
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
                        <label class="form-label">Skills</label>
                        <select class="form-select select2" name="skill">
                            <option value="" {{ request('skill') == '' ? 'selected' : '' }}>Select</option>
                            @forelse($skills as $skill)
                                <option value="{{ $skill->id }}"
                                    {{ request('skill') == $skill->id ? 'selected' : '' }}>{{ $skill->name }}</option>
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
                                    {{ ucwords(str_replace('-', ' ', $value)) }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                    <a href="{{ route('recruitment.request-hr') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <!-- END: MODAL-->
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const skillModal = document.getElementById('skillModal');
            skillModal.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                const skills = JSON.parse(trigger.getAttribute('data-skills'));

                const body = skillModal.querySelector('#skillModalBody');
                body.innerHTML = ''; // Clear old content

                skills.forEach(skill => {
                    const badge =
                        `<span class="badge rounded-pill badge-light-secondary badgeborder-radius me-1 mb-1">${skill}</span>`;
                    body.innerHTML += badge;
                });
            });
        });
    </script>
@endsection
