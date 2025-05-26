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
                                                        <th onclick="sort('created_at')">
                                                            Date<i data-feather='arrow-up-circle' class="float-end"
                                                                id="sort-icon-created_at"></i>
                                                        </th>
                                                        <th onclick="sort('job_id')">
                                                            Job Id<i data-feather='arrow-up-circle' class="float-end"
                                                                id="sort-icon-job_id"></i>
                                                        </th>
                                                        <th onclick="sort('request_id')">
                                                            Request Id<i data-feather='arrow-up-circle' class="float-end"
                                                                id="sort-icon-request_id"></i>
                                                        </th>
                                                        <th onclick="sort('job_type')">
                                                            Job Type<i data-feather='arrow-up-circle' class="float-end"
                                                                id="sort-icon-job_type"></i>
                                                        </th>
                                                        <th>Job Title</th>
                                                        <th>Education</th>
                                                        <th>Skills</th>
                                                        <th>Exp</th>
                                                        <th>Request By</th>
                                                        <th onclick="sort('expected_doj')">
                                                            Expected D.O.J<i data-feather='arrow-up-circle'
                                                                class="float-end" id="sort-icon-expected_doj"></i>
                                                        </th>
                                                        <th onclick="sort('status')">
                                                            Status<i data-feather='arrow-up-circle' class="float-end"
                                                                id="sort-icon-status"></i>
                                                        </th>
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
                                                                    $selectedSkills = $request->recruitmentSkills;
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
                                                                @if (in_array($request->status, [App\Helpers\CommonHelper::PENDING, App\Helpers\CommonHelper::APPROVED_FORWARD]) &&
                                                                        $request->approvar_name)
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
                                                                <div class="dropdown">
                                                                    <button type="button"
                                                                        class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                                        data-bs-toggle="dropdown">
                                                                        <i data-feather="more-vertical"></i>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-end">
                                                                        <a class="dropdown-item"
                                                                            href="{{ route('recruitment.requests.show', ['id' => $request->id]) }}">
                                                                            <i data-feather="eye" class="me-50"></i>
                                                                            <span>View Detail</span>
                                                                        </a>

                                                                        @if (!in_array($request->status, ['final-approved', 'revoked', 'rejected']) && $user->id == $request->created_by)
                                                                            <a class="dropdown-item"
                                                                                href="{{ route('recruitment.requests.edit', ['id' => $request->id]) }}">
                                                                                <i data-feather="edit-3" class="me-50"></i>
                                                                                <span>Edit</span>
                                                                            </a>
                                                                        @endif

                                                                        @if ($request->status == App\Helpers\CommonHelper::PENDING && $user->id == $request->created_by)
                                                                            <a class="dropdown-item" href="javascript:;"
                                                                                data-bs-target="#status-modal"
                                                                                data-bs-toggle="modal"
                                                                                data-status="{{ App\Helpers\CommonHelper::REVOKED }}"
                                                                                data-title="Revoked Request"
                                                                                data-id="{{ $request->id }}">
                                                                                <i data-feather="trash-2"
                                                                                    class="me-50"></i>
                                                                                <span>Revoke</span>
                                                                            </a>
                                                                        @endif

                                                                        @if (in_array($request->status, [App\Helpers\CommonHelper::PENDING, App\Helpers\CommonHelper::APPROVED_FORWARD]) &&
                                                                                $request->approval_authority == $user->id)
                                                                            @if ($user->manager_id)
                                                                                <a class="dropdown-item" href="javascript:;"
                                                                                    data-bs-target="#status-modal"
                                                                                    data-bs-toggle="modal"
                                                                                    data-status="{{ App\Helpers\CommonHelper::APPROVED_FORWARD }}"
                                                                                    data-title="Approved Request"
                                                                                    data-id="{{ $request->id }}">
                                                                                    <i data-feather="check-circle"
                                                                                        class="me-50"></i>
                                                                                    <span>Approve & Forward</span>
                                                                                </a>
                                                                            @endif

                                                                            <a class="dropdown-item" href="javascript:;"
                                                                                data-bs-target="#status-modal"
                                                                                data-bs-toggle="modal"
                                                                                data-status="{{ App\Helpers\CommonHelper::FINAL_APPROVED }}"
                                                                                data-title="Approved Request"
                                                                                data-id="{{ $request->id }}">
                                                                                <i data-feather="check-circle"
                                                                                    class="me-50"></i>
                                                                                <span>Final Approve</span>
                                                                            </a>
                                                                            <a class="dropdown-item" href="javascript:;"
                                                                                data-bs-target="#status-modal"
                                                                                data-bs-toggle="modal"
                                                                                data-status="{{ App\Helpers\CommonHelper::REJECTED }}"
                                                                                data-title="Reject Request"
                                                                                data-id="{{ $request->id }}">
                                                                                <i data-feather="x-circle"
                                                                                    class="me-50"></i> Reject
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                </div>
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
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
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
                    redirect="{{ route('recruitment.requests') }}">
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('[data-bs-toggle="modal"]');

            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    const status = this.getAttribute('data-status');
                    const title = this.getAttribute('data-title');
                    const requestId = this.getAttribute('data-id');

                    const statusInput = document.getElementById('status-input');
                    statusInput.value = status;

                    const modalTitle = document.querySelector('#status-modal-title');
                    modalTitle.textContent = title;

                    const form = document.getElementById('status-form');
                    form.setAttribute('data-message', `Do you want to ${title}?`);
                    form.action = `{{ url('/recruitment/requests/update-status/${requestId}') }}`;
                });
            });
        });
    </script>

    {{-- Sorting --}}
    <script>
        // Handle sorting logic
        function sort(column) {
            const params = new URLSearchParams(window.location.search);
            const currentColumn = params.get('column');
            const currentDirection = params.get('sort');

            const newSortDirection = (currentColumn === column && currentDirection === 'asc') ? 'desc' : 'asc';

            params.set('column', column);
            params.set('sort', newSortDirection);

            // Build new query string and reload
            const newQueryString = '?' + params.toString();
            window.location.href = window.location.pathname + newQueryString;
        }

        window.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const column = params.get('column');
            const sortDirection = params.get('sort');

            if (column && sortDirection) {
                // Build the dynamic element ID based on the column
                const icon = document.getElementById(`sort-icon-${column}`);
                if (icon) {
                    // Determine the correct icon based on sort direction
                    const iconType = sortDirection === 'asc' ? 'arrow-up-circle' : 'arrow-down-circle';
                    icon.setAttribute('data-feather', iconType);
                    feather.replace(); // Re-render Feather icons
                }
            }
        });
    </script>
@endsection
