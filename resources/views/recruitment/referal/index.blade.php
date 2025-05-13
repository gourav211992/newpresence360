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
                            <h2 class="content-header-title float-start mb-0">Referral</h2>
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
                                <div class="table-responsive candidates-tables">
                                    @include('recruitment.partials.card-header')
                                    <table
                                        class="datatables-basic table table-striped myrequesttablecbox loanapplicationlist">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th>Job Id</th>
                                                <th>Job Title</th>
                                                <th>Candidate</th>
                                                <th>Education</th>
                                                <th>Skills</th>
                                                <th>Exp.</th>
                                                <th>Applied For.</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($referrals as $referral)
                                                <tr>
                                                    <td>{{ $referrals->firstItem() + $loop->index }}</td>
                                                    <td class="text-nowrap">
                                                        {{ $referral->created_at ? App\Helpers\CommonHelper::dateFormat($referral->created_at) : '' }}
                                                    </td>
                                                    <td class="fw-bolder text-dark">{{ @$referral->job->job_id }}</td>
                                                    <td>{{ @$referral->job->job_title_name }}</td>
                                                    <td>
                                                        <a
                                                            href="{{ route('recruitment.my-referal.show', ['id' => $referral->id, 'jobId' => $referral->job_id]) }}">
                                                            <span
                                                                class="badge rounded-pill badge-light-primary badgeborder-radius">{{ ucfirst(@$referral->candidate->name) }}</span>
                                                        </a>
                                                    </td>
                                                    <td>{{ @$referral->job->education_name }}</td>
                                                    <td>
                                                        @php
                                                            $skills = @$referral->job->jobSkills;
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
                                                                <span class="skilnum">+{{ $skills->count() - 2 }}</span>
                                                            </a>
                                                        @endif
                                                    </td>
                                                    <td>{{ @$referral->job->work_exp_min }} -
                                                        {{ @$referral->job->work_exp_max }} year</td>
                                                    <td>{{ ucfirst($referral->applied_for) }}</td>
                                                    <td>{{ isset($referral->job->status) ? ucfirst($referral->job->status) : '' }}
                                                    </td>
                                                    <td class="tableactionnew">
                                                        <a class="dropdown-item"
                                                            href="{{ route('recruitment.my-referal.show', ['id' => $referral->id, 'jobId' => $referral->job_id]) }}">
                                                            <i data-feather="eye" class="me-50"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-danger text-center" colspan="12">No record(s) found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                {{-- Pagination --}}
                                {{ $referrals->appends(request()->input())->links('recruitment.partials.pagination') }}
                                {{-- Pagination End --}}
                            </div>
                        </div>
                    </div>
                </section>
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
                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                    <a href="{{ route('recruitment.my-referal') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
