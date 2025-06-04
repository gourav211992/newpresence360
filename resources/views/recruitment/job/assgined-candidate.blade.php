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
                                                        <th>Date</th>
                                                        <th>Job Id</th>
                                                        <th>Job Type</th>
                                                        <th>Job Title</th>
                                                        <th>Education</th>
                                                        <th>Skills</th>
                                                        <th>Exp.</th>
                                                        <th>Request By</th>
                                                        <th>Candidates</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($jobs as $job)
                                                        <tr>
                                                            <td>{{ $jobs->firstItem() + $loop->index }}</td>
                                                            <td class="text-nowrap">
                                                                {{ $job->created_at ? App\Helpers\CommonHelper::dateFormat($job->created_at) : '' }}
                                                            </td>
                                                            <td class="fw-bolder text-dark">{{ $job->job_id }}</td>
                                                            <td>
                                                                <a
                                                                    href="{{ route('recruitment.jobs.show', ['id' => $job->id]) }}">
                                                                    <span
                                                                        class="badge rounded-pill badge-light-primary badgeborder-radius">
                                                                        {{ ucfirst($job->status) }}
                                                                    </span>
                                                                </a>
                                                            </td>
                                                            <td>{{ $job->job_title_name }}</td>
                                                            <td>{{ $job->education_name }}</td>
                                                            <td>
                                                                @php
                                                                    $skills = $job->jobSkills;
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
                                                            <td>{{ $job->work_exp_min }} - {{ $job->work_exp_max }} year
                                                            </td>
                                                            <td>
                                                                <div class="d-flex flex-row">
                                                                    <div
                                                                        style="background-color: #ddb6ff; color: #6b12b7; line-height: 30px; width: 30px; height: 30px; border-radius: 50%; position: relative; font-size: 1rem; text-align: center; margin-right: 5px; font-weight: 600;">
                                                                        {{ strtoupper(substr($job->creator_name, 0, 1)) }}
                                                                    </div>
                                                                    <div class="my-auto">
                                                                        <h6
                                                                            class="mb-0 fw-bolder text-dark hr-dashemplname">
                                                                            {{ $job->creator_name }}</h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>{{ $job->assigned_candidates_count }}</td>
                                                            <td class="tableactionnew">
                                                                @php
                                                                    $isCreator = $user->id == $job->created_by;
                                                                    $isVendor =
                                                                        $user->user_type ==
                                                                        App\Helpers\CommonHelper::IAM_VENDOR;
                                                                @endphp

                                                                <div class="dropdown">
                                                                    <button type="button"
                                                                        class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                                        data-bs-toggle="dropdown">
                                                                        <i data-feather="more-vertical"></i>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-end">
                                                                        @if ($job->status == 'open' && ($isCreator || $isVendor))
                                                                            <a class="dropdown-item"
                                                                                href="{{ route('recruitment.jobs.candidates', ['id' => $job->id]) }}">
                                                                                <i data-feather="users" class="me-50"></i>
                                                                                <span>Assign Candidates</span>
                                                                            </a>
                                                                        @endif
                                                                        @if ($job->status == 'open' && $isCreator)
                                                                            <a class="dropdown-item" href="javascript:;"
                                                                                data-bs-target="#assign-vendor-modal"
                                                                                data-bs-toggle="modal"
                                                                                data-id="{{ $job->id }}">
                                                                                <i data-feather="users" class="me-50"></i>
                                                                                <span>Assign Vendors</span>
                                                                            </a>
                                                                            <a class="dropdown-item"
                                                                                href="{{ route('recruitment.jobs.edit', ['id' => $job->id]) }}">
                                                                                <i data-feather="edit-3" class="me-50"></i>
                                                                                <span>Edit</span>
                                                                            </a>

                                                                            <a class="dropdown-item" href="#">
                                                                                <i data-feather="trash-2"
                                                                                    class="me-50"></i>
                                                                                <span>Closed</span>
                                                                            </a>
                                                                        @endif

                                                                        <a class="dropdown-item"
                                                                            href="{{ route('recruitment.jobs.show', ['id' => $job->id]) }}">
                                                                            <i data-feather="eye" class="me-50"></i>
                                                                            <span>View Detail</span>
                                                                        </a>
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
                                        {{ $jobs->appends(request()->input())->links('recruitment.partials.pagination') }}
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

    <div class="modal fade" id="assign-vendor-modal" tabindex="-1" aria-labelledby="vendorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal"> Assign Vendor</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form role="post-data" method="POST" id="vendor-form">
                    <div class="modal-body pb-2">
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">Vendor <span class="text-danger">*</span></label>
                                    <select name="vendor_ids[]" class="form-select select2" multiple id="select-vendor">
                                        <option value="">Select</option>
                                        @forelse($vendors as $vendor)
                                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Remarks</label>
                                    <textarea class="form-control" name="log_message"></textarea>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-primary" data-request="ajax-submit"
                            data-target="[role=post-data]">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- BEGIN: FILTER MODAL-->
    @include('recruitment.job.filter', [
        'skillsData' => $skills,
        'jobTitles' => $jobTitles,
        'status' => $status,
    ])
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
                    const jobId = this.getAttribute('data-id');

                    const form = document.getElementById('vendor-form');
                    form.action = `{{ url('/recruitment/jobs/assign-vendor/${jobId}') }}`;

                    $('#select-vendor').val(null).trigger('change');
                    fetch(`{{ url('/recruitment/jobs/get-assigned-vendors/${jobId}') }}`)
                        .then(response => response.json())
                        .then(response => {
                            console.log(response.data);
                            if (response.data) {
                                $('#select-vendor').val(response.data).trigger('change');
                            } else {
                                $('#select-vendor').val([]).trigger('change');
                            }
                        });
                });
            });

        });

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('assign-vendor-modal');

            modal.addEventListener('hidden.bs.modal', function() {
                location.reload(); // 👈 reload on modal close
            });
        });
    </script>
@endsection
