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
                            <h2 class="content-header-title float-start mb-0">My Activity</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('recruitment.dashboard') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">History
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
                        <div class="col-md-12 col-12">
                            <div class="card  new-cardbox">
                                {{-- Card Header --}}
                                @include('recruitment.activity.tab', [
                                    'requestCount' => $requestCount,
                                    'referralCount' => $referralCount,
                                ])

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
                                                        <th>Exp</th>
                                                        <th>Request By</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($requestLog as $log)
                                                        <tr>
                                                            <td>{{ $requestLog->firstItem() + $loop->index }}</td>
                                                            <td class="text-nowrap">
                                                                {{ $log->created_at ? App\Helpers\CommonHelper::dateFormat($log->created_at) : '' }}
                                                            </td>
                                                            <td class="fw-bolder text-dark">{{ $log->request->job_id }}</td>
                                                            <td class="fw-bolder text-dark">{{ $log->request->request_id }}
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="badge rounded-pill badge-light-primary badgeborder-radius">
                                                                    {{ ucfirst($log->request->job_type) }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $log->request->job_title_name }}</td>
                                                            <td>{{ $log->request->education_name }}</td>
                                                            <td>
                                                                @php
                                                                    $selectedSkills = $log->request->recruitmentSkills;
                                                                @endphp
                                                                @foreach ($selectedSkills->take(2) as $skill)
                                                                    <span
                                                                        class="badge rounded-pill badge-light-secondary badgeborder-radius">
                                                                        {{ $skill->name }}
                                                                    </span>
                                                                @endforeach

                                                                @if ($selectedSkills->count() > 2)
                                                                    <a href="#" class="skilnum text-primary"
                                                                        data-bs-toggle="modal" data-bs-target="#skillModal"
                                                                        data-skills='@json($selectedSkills->pluck('name'))'>
                                                                        <span
                                                                            class="skilnum">+{{ $selectedSkills->count() - 2 }}</span>
                                                                    </a>
                                                                @endif
                                                            </td>
                                                            <td>{{ $log->request->work_experience_name }}</td>
                                                            <td class="">
                                                                <div class="d-flex flex-row">
                                                                    <div
                                                                        style="background-color: #ddb6ff; color: #6b12b7; line-height: 30px; width: 30px; height: 30px; border-radius: 50%; position: relative; font-size: 1rem; text-align: center; margin-right: 5px; font-weight: 600;">
                                                                        {{ strtoupper(substr($log->request->creator_name, 0, 1)) }}
                                                                    </div>
                                                                    <div class="my-auto">
                                                                        <h6
                                                                            class="mb-0 fw-bolder text-dark hr-dashemplname">
                                                                            {{ $log->request->creator_name }}</h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                @php
                                                                    if (
                                                                        in_array($log->status, [
                                                                            'approved-forward',
                                                                            'final-approved',
                                                                        ])
                                                                    ) {
                                                                        $className = 'badge-light-success';
                                                                    } elseif (
                                                                        in_array($log->status, [
                                                                            'pending',
                                                                            'onhold',
                                                                            'rejected',
                                                                            'revoke',
                                                                        ])
                                                                    ) {
                                                                        $className = 'badge-light-danger';
                                                                    } else {
                                                                        $className = 'badge-light-warning';
                                                                    }
                                                                @endphp
                                                                <span
                                                                    class="badge rounded-pill {{ $className }} badgeborder-radius">
                                                                    {{ ucwords(str_replace('-', ' ', $log->status)) }}
                                                                </span>
                                                            </td>
                                                            <td class="tableactionnew">
                                                                <a class="dropdown-item"
                                                                    href="{{ route('recruitment.requests.show', ['id' => $log->job_request_id]) }}">
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
                                        {{ $requestLog->appends(request()->input())->links('recruitment.partials.pagination') }}
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
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
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
