@extends('layouts.app')
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <form class="form" role="post-data" method="POST"
                action="{{ route('recruitment.jobs.assign-candidate', ['id' => $job->id]) }}"
                redirect="{{ route('recruitment.jobs') }}" autocomplete="off">
                <div class="content-header row">
                    <div class="content-header-left col-md-6 mb-1 mb-sm-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Candidate List</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a
                                                href="{{ route('recruitment.hr-dashboard') }}">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">{{ $job->job_title_name }} - {{ $job->job_id }}
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 ">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('recruitment.jobs') }}" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</a>
                            <button class="btn btn-warning btn-sm mb-sm-0 mb-50" data-bs-target="#filter"
                                data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                            <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-request="ajax-submit"
                                data-target="[role=post-data]"><i data-feather="check-circle"></i> Assign</button>
                        </div>
                    </div>
                </div>
                <div class="content-body dasboardnewbody">

                    <!-- ChartJS section start -->
                    <section id="chartjs-chart">
                        <div class="row">
                            <div class="col-md-12 col-12">
                                <div class="card  new-cardbox">

                                    <div class="table-responsive candidates-tables border-0">
                                        @include('recruitment.partials.card-header')
                                        <table class="datatables-basic table table-striped">
                                            <thead>
                                                <tr>
                                                    <th class="pe-0">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" id="check-all">
                                                        </div>
                                                    </th>
                                                    <th width="350px">Candidate</th>
                                                    <th>Resume</th>
                                                    <th>Skills</th>
                                                    <th>Exp.</th>
                                                    <th width="170px">Current Employer</th>
                                                    <th>Location</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($candidates as $candidate)
                                                    <tr>
                                                        <td class="pe-0">
                                                            <div class="form-check form-check-inline">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="candidate_ids[]" value="{{ $candidate->id }}"
                                                                    {{ in_array($candidate->id, $assignedCandidateIds) ? 'checked' : '' }}>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="cand-inforecsec">
                                                                <div class="">
                                                                    <h3>{{ $candidate->name }}</h3>
                                                                    <h4>{{ $candidate->current_organization }}</h4>
                                                                    <h5>{{ $candidate->mobile_no }}
                                                                        <a>{{ $candidate->email }}</a>
                                                                    </h5>
                                                                </div>

                                                            </div>

                                                        </td>
                                                        <td><a href="{{ url('/') . '/' . $candidate->resume_path }}"
                                                                target="_blank"><img
                                                                    src="{{ asset('img/resume.png') }}" /></a></td>
                                                        <td>
                                                            @php
                                                                $skills = $candidate->candidateSkills;
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
                                                        <td>{{ $candidate->work_exp }}</td>
                                                        <td>{{ $candidate->current_organization }}</td>
                                                        <td>{{ $candidate->location_name }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-danger text-center" colspan="7">No record(s)
                                                            found.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>



                        </div>



                    </section>
                    <!-- ChartJS section end -->

                </div>
            </form>
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
                        <label class="form-label">Select Location</label>
                        <select class="form-select select2" name="location_id">
                            <option value="" {{ request('location_id') == '' ? 'selected' : '' }}>Select</option>
                            @forelse($locations as $location)
                                <option value="{{ $location->id }}"
                                    {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                    {{ $location->store_name }}</option>
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
                                    {{ request('skill') == $skill->id ? 'selected' : '' }}>
                                    {{ $skill->name }}</option>
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
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Get the "check all" checkbox in the header
            const checkAllCheckbox = document.getElementById('check-all');

            // Get all the checkboxes in the table
            const checkboxes = document.querySelectorAll('.datatables-basic tbody .form-check-input');

            // Event listener for "check all" checkbox
            checkAllCheckbox.addEventListener('change', function() {
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = checkAllCheckbox.checked;
                });
            });

            // Optional: Update the header checkbox status when individual checkboxes are clicked
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
                    checkAllCheckbox.checked = allChecked;
                    checkAllCheckbox.indeterminate = !allChecked && Array.from(checkboxes).some(
                        checkbox => checkbox.checked);
                });
            });
        });
    </script>
@endsection
