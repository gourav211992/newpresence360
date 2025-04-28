@extends('layouts.app')

@section('styles')
@endsection

@section('content')

    <!-- BEGIN: Content-->
    <div class="app-content content ">

        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>

        <div class="content-wrapper container-xxl p-0">

            <!-- Header -->
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Update Appraisal</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                                        <li class="breadcrumb-item active">Add New</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</button>
                            <button form="appraisal-form"
                                    class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submission_val" data-val="draft"><i
                                    data-feather="save"></i> Save as draft</button>
                            <button form="appraisal-form" class="btn btn-success btn-sm mb-50 mb-sm-0 submission_val"
                                    data-val="submitted"><i data-feather="refresh-cw"></i> Approve</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Body -->
            <div class="content-body">

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div id="success-message" style="display: none;" class="alert alert-success"></div>

                <div id="error-message" style="display: none;" class="alert alert-danger">
                    <ul id="error-list"></ul>
                </div>

                <section id="basic-datatable">
                    <form id="appraisal-form" method="POST" action="{{ route('loanAppraisal.save') }}"
                          enctype='multipart/form-data'>
                        @csrf
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body customernewsection-form">

                                        <!-- Main Form -->
                                        <div class="row">

                                            <div class="col-md-12">
                                                <div
                                                    class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                    <div>
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <input type="hidden" name="loan_id" id="loan_id"
                                                       value="{{ $loanAppraisal->loan_id ?? ($loan->id ?? '') }}">
                                                <input type="hidden" name="loan_amount" id="loan_amount"
                                                       value="{{ $loan->loan_amount ?? '' }}">
                                                <input type="hidden" name="status" id="status"
                                                       value="{{ $loanAppraisal->status ?? '' }}">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Application No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" name="application_no"
                                                               id="application_no"
                                                               value="{{ $loanAppraisal->application_no ?? ($loan->appli_no ?? '') }}"
                                                               readonly />
                                                        <p class="error text-danger"></p>
                                                    </div>
                                                </div>
{{--                                                <div class="row align-items-center mb-1">--}}
{{--                                                    <div class="col-md-4">--}}
{{--                                                        <label class="form-label">Name of Unit <span--}}
{{--                                                                class="text-danger">*</span></label>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="col-md-6">--}}
{{--                                                        <input type="text" class="form-control" name="unit_name"--}}
{{--                                                               id="unit_name" value="{{ $loanAppraisal->unit_name ?? '' }}" />--}}
{{--                                                        <p class="error text-danger"></p>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Name of Proprietor <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" name="proprietor_name"
                                                               id="proprietor_name"
                                                               value="{{ $loanAppraisal->proprietor_name ?? ($loan->name ?? '') }}"
                                                               readonly>
                                                        <p class="error text-danger"></p>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Project Cost<span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" name="project_cost"
                                                               id="project_cost" oninput="calculate_contribution()"
                                                               value="{{ $loanAppraisal->project_cost ?? '' }}">
                                                        <p class="error text-danger"></p>
                                                    </div>
                                                </div>
{{--                                                <div class="row align-items-center mb-1">--}}
{{--                                                    <div class="col-md-4">--}}
{{--                                                        <label class="form-label">Address <span--}}
{{--                                                                class="text-danger">*</span></label>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="col-md-6">--}}
{{--                                                        <input type="text" class="form-control" name="address"--}}
{{--                                                               id="address"--}}
{{--                                                               value="{{ $loanAppraisal->address ?? ($loan->address ?? '') }}">--}}
{{--                                                        <p class="error text-danger"></p>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                                <div class="row align-items-center mb-1">--}}
{{--                                                    <div class="col-md-4">--}}
{{--                                                        <label class="form-label">Project Cost<span--}}
{{--                                                                class="text-danger">*</span></label>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="col-md-6">--}}
{{--                                                        <input type="text" class="form-control" name="project_cost"--}}
{{--                                                               id="project_cost" oninput="calculate_contribution()"--}}
{{--                                                               value="{{ $loanAppraisal->project_cost ?? '' }}">--}}
{{--                                                        <p class="error text-danger"></p>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
                                            </div>

                                            <div class="col-md-6">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Interest Rate (P.A) <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" name="interest_rate"
                                                               id="interest_rate"
                                                               value="{{ $loanAppraisal->interest_rate ?? '' }}" />
                                                        <p class="error text-danger"></p>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Promotor's Contribution <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control"
                                                               name="promotor_contribution" id="promotor_contribution"
                                                               readonly
                                                               value="{{ $loanAppraisal->promotor_contribution ?? '' }}">
                                                        <p class="error text-danger"></p>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Term Loan<span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" name="term_loan"
                                                               id="term_loan" oninput="calculate_contribution()"
                                                               value="{{ $loan->loan_amount ?? '' }}" required>
                                                        <p class="error text-danger contri_error"></p>
                                                        @error('term_loan')
                                                        <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tab Form -->
                                        <div class="col-md-12">
                                            <div class="mt-2">

                                                <div class="step-custhomapp bg-light">
                                                    <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Credit">Credit Scoroing</a>
                                                        </li>
{{--                                                        <li class="nav-item">--}}
{{--                                                            <a class="nav-link active" data-bs-toggle="tab"--}}
{{--                                                               href="#Report">Detail Project Report</a>--}}
{{--                                                        </li>--}}
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab"
                                                               href="#Disbursal">Disbursal Schedule</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab"
                                                               href="#Recovery">Recovery Schedule</a>
                                                        </li>
{{--                                                        <li class="nav-item">--}}
{{--                                                            <a class="nav-link" data-bs-toggle="tab"--}}
{{--                                                               href="#Documentsupload">KYC Documents</a>--}}
{{--                                                        </li>--}}
                                                    </ul>
                                                </div>

                                                <div class="tab-content ">

                                                    <div class="tab-pane" id="Disbursal">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="table-responsive">
                                                                    <table
                                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                        <thead>
                                                                        <tr>
                                                                            <th>#</th>
                                                                            <th>Disbursal Milestone <span
                                                                                    class="text-danger">*</span></th>
                                                                            <th>Disbursal Amount <span
                                                                                    class="text-danger">*</span></th>
                                                                            <th>Remarks <span
                                                                                    class="text-danger">*</span>
                                                                            </th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                        </thead>
                                                                        <tbody id="disbursalTable">
                                                                        @if (!empty($loanAppraisal->disbursal))
                                                                            @php
                                                                                $i = 0;
                                                                            @endphp
                                                                            @foreach ($loanAppraisal->disbursal as $disbursal)
                                                                                <tr>
                                                                                    <td>{{ $i + 1 }}</td>
                                                                                    <td>
                                                                                        <input type="text"
                                                                                               name="disbursal_milestone[{{ $i }}]"
                                                                                               id="disbursal_milestone_{{ $i }}"
                                                                                               required
                                                                                               value="{{ old('disbursal_milestone.' . $i, $disbursal->milestone) }}"
                                                                                               class="form-control mw-100">
                                                                                        @error('disbursal_milestone.' .
                                                                                            $i)
                                                                                        <p class="error text-danger">
                                                                                            {{ $message }}</p>
                                                                                        @enderror
                                                                                    </td>
                                                                                    <td>
                                                                                        <input type="number"
                                                                                               name="disbursal_amount[{{ $i }}]"
                                                                                               id="disbursal_amount_{{ $i }}"
                                                                                               required
                                                                                               value="{{ old('disbursal_amount.' . $i, $disbursal->amount) }}"
                                                                                               class="form-control mw-100">
                                                                                        @error('disbursal_amount.' . $i)
                                                                                        <p class="error text-danger">
                                                                                            {{ $message }}</p>
                                                                                        @enderror
                                                                                    </td>
                                                                                    <td>
                                                                                        <input type="text"
                                                                                               name="disbursal_remarks[{{ $i }}]"
                                                                                               id="disbursal_remarks_{{ $i }}"
                                                                                               value="{{ old('disbursal_remarks.' . $i, $disbursal->remarks) }}"
                                                                                               class="form-control mw-100">
                                                                                        @error('disbursal_remarks.' .
                                                                                            $i)
                                                                                        <p class="error text-danger">
                                                                                            {{ $message }}</p>
                                                                                        @enderror
                                                                                    </td>
                                                                                    <td>
                                                                                        <a href="#"
                                                                                           class="text-primary addDisbursal">
                                                                                            <i data-feather="plus-square"
                                                                                               class="me-50"></i>
                                                                                        </a>
                                                                                        <a href="#"
                                                                                           class="text-primary deleteDisbursal">
                                                                                            <i
                                                                                                data-feather="trash-2"></i>
                                                                                        </a>
                                                                                    </td>
                                                                                </tr>
                                                                                @php
                                                                                    $i++;
                                                                                @endphp
                                                                            @endforeach
                                                                        @else
                                                                            <tr>
                                                                                <td>1</td>
                                                                                <td>
                                                                                    <input type="text"
                                                                                           name="disbursal_milestone[0]"
                                                                                           id="disbursal_milestone_0"
                                                                                           value="{{ old('disbursal_milestone.0') }}"
                                                                                           class="form-control mw-100">
                                                                                    @error('disbursal_milestone.0')
                                                                                    <p class="error text-danger">
                                                                                        {{ $message }}</p>
                                                                                    @enderror
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text"
                                                                                           name="disbursal_amount[0]"
                                                                                           id="disbursal_amount_0"
                                                                                           value="{{ old('disbursal_amount.0', App\Helpers\Helper::formatIndianNumber($loan->loan_amount)) }}"
                                                                                           required
                                                                                           class="form-control mw-100 disbursal_amount">
                                                                                    @error('disbursal_amount.0')
                                                                                    <p class="error text-danger">
                                                                                        {{ $message }}</p>
                                                                                    @enderror
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text"
                                                                                           name="disbursal_remarks[0]"
                                                                                           id="disbursal_remarks_0"
                                                                                           value="{{ old('disbursal_remarks.0') }}"
                                                                                           class="form-control mw-100">
                                                                                    @error('disbursal_remarks.0')
                                                                                    <p class="error text-danger">
                                                                                        {{ $message }}</p>
                                                                                    @enderror
                                                                                </td>
                                                                                <td>
                                                                                    <a href="#"
                                                                                       class="text-primary addDisbursal">
                                                                                        <i data-feather="plus-square"
                                                                                           class="me-50"></i>
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                        </tbody>

                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="tab-pane" id="Recovery">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="table-responsive">
                                                                    <table id="repaymentTable"
                                                                           class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                        <thead>
                                                                        <tr>
                                                                            <th>#</th>
                                                                            <th width="100px">Year</th>
                                                                            <th class="text-end">Amt. at Start</th>
                                                                            <th class="text-end">Interest Amt.</th>
                                                                            <th class="text-end">Repayemnt Amt.</th>
                                                                            <th class="text-end">Amount at End <span
                                                                                    class="text-danger">*</span></th>
                                                                        </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                        @if (!empty($loanAppraisal->recovery))
                                                                            @php
                                                                                $i = 0;
                                                                                $totalInterest = 0;
                                                                                $totalRepayment = 0;
                                                                            @endphp
                                                                            @foreach ($loanAppraisal->recovery as $recovery)
                                                                                <tr>
                                                                                    <td>{{ $i + 1 }}</td>
                                                                                    <td><input type="text"
                                                                                               class="form-control mw-100 text-end"
                                                                                               name="year[{{ $i }}]"
                                                                                               id="year_{{ $i }}"
                                                                                               value="{{ $recovery->year }}"
                                                                                               readonly></td>
                                                                                    <td><input type="text"
                                                                                               class="form-control mw-100 text-end"
                                                                                               name="start_amount[{{ $i }}]"
                                                                                               id="start_amount_{{ $i }}"
                                                                                               value="{{ App\Helpers\Helper::removeCommas($recovery->start_amount) }}"
                                                                                               readonly></td>
                                                                                    <td><input type="text"
                                                                                               class="form-control mw-100 text-end"
                                                                                               name="interest_amount[{{ $i }}]"
                                                                                               id="interest_amount_{{ $i }}"
                                                                                               value="{{ App\Helpers\Helper::removeCommas($recovery->interest_amount) }}"
                                                                                               readonly></td>
                                                                                    <td><input type="text"
                                                                                               class="form-control mw-100 text-end"
                                                                                               name="repayment_amount[{{ $i }}]"
                                                                                               id="repayment_amount_{{ $i }}"
                                                                                               value="{{ App\Helpers\Helper::removeCommas($recovery->repayment_amount) }}"
                                                                                               readonly></td>
                                                                                    <td><input type="text"
                                                                                               class="form-control mw-100 text-end"
                                                                                               name="end_amount[{{ $i }}]"
                                                                                               id="end_amount_{{ $i }}"
                                                                                               value="{{ App\Helpers\Helper::removeCommas($recovery->end_amount) }}"
                                                                                               readonly></td>
                                                                                </tr>
                                                                                @php
                                                                                    $i++;
                                                                                    $totalInterest +=
                                                                                        $recovery->interest_amount;
                                                                                    $totalRepayment +=
                                                                                        $recovery->repayment_amount;
                                                                                @endphp
                                                                            @endforeach
                                                                            <tr>
                                                                                <td colspan="3"
                                                                                    class="fw-bolder text-dark">Total
                                                                                </td>
                                                                                <td class="fw-bolder text-dark">
                                                                                    {{ App\Helpers\Helper::formatIndianNumber($totalInterest) }}</td>
                                                                                <td class="fw-bolder text-dark">
                                                                                    {{ App\Helpers\Helper::formatIndianNumber($totalRepayment) }}</td>
                                                                                <td>&nbsp;</td>
                                                                            </tr>
                                                                        @endif
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                            </div>

                                                        </div>
                                                    </div>

                                                    <div class="tab-pane active"  id="Credit">


{{--                                                        <p class="mt-2 mb-25 text-dark customapplsmallhead"><strong>2) Detailed Financial  Analysis</strong></p>--}}
{{--                                                        <p class="font-small-3 mb-1">40% (The committee review and refines the applicant’s score based on:</p>--}}

{{--                                                        <div class="table-responsive">--}}
{{--                                                            <table id="table2" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">--}}
{{--                                                                <thead>--}}
{{--                                                                <tr>--}}
{{--                                                                    <th class="20px">--}}
{{--                                                                        <div class="form-check form-check-inline me-0">--}}
{{--                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Parameter">--}}
{{--                                                                        </div>--}}
{{--                                                                    </th>--}}
{{--                                                                    <th width="300px">Parameter</th>--}}
{{--                                                                    <th>Weightage %</th>--}}
{{--                                                                    <th>Sub criteria</th>--}}
{{--                                                                    <th>Marks Allocation</th>--}}
{{--                                                                </tr>--}}
{{--                                                                </thead>--}}
{{--                                                                <tbody>--}}
{{--                                                                <tr>--}}
{{--                                                                    <td></td>--}}
{{--                                                                    <td colspan="4" style="color: #000">--}}
{{--                                                                        <strong>For Education loans</strong> Co-applicant’s income and credit history--}}
{{--                                                                    </td>--}}
{{--                                                                </tr>--}}
{{--                                                                <tr>--}}
{{--                                                                    <td>--}}
{{--                                                                        <div class="form-check form-check-inline me-0">--}}
{{--                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Loan" data-marks="20">--}}
{{--                                                                        </div>--}}
{{--                                                                    </td>--}}
{{--                                                                    <td>Excellent Income and Credit History</td>--}}
{{--                                                                    <td>-</td>--}}
{{--                                                                    <td>Highly Reliable Co-applicant</td>--}}
{{--                                                                    <td>20 Marks</td>--}}
{{--                                                                </tr>--}}


{{--                                                                <tr>--}}
{{--                                                                    <td>--}}
{{--                                                                        <div class="form-check form-check-inline me-0">--}}
{{--                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Identity" data-marks="15">--}}
{{--                                                                        </div>--}}
{{--                                                                    </td>--}}
{{--                                                                    <td>Good Income and Credit History</td>--}}
{{--                                                                    <td>-</td>--}}
{{--                                                                    <td>Reliable Co-applicant with Minor Risks</td>--}}
{{--                                                                    <td>15 Marks</td>--}}
{{--                                                                </tr>--}}

{{--                                                                <tr>--}}
{{--                                                                    <td>--}}
{{--                                                                        <div class="form-check form-check-inline me-0">--}}
{{--                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Credit" data-marks="10">--}}
{{--                                                                        </div>--}}
{{--                                                                    </td>--}}
{{--                                                                    <td>Average Income and Credit History</td>--}}
{{--                                                                    <td>-</td>--}}
{{--                                                                    <td>Moderate Risk Co-applicant</td>--}}
{{--                                                                    <td>10 Marks</td>--}}
{{--                                                                </tr>--}}

{{--                                                                <tr>--}}
{{--                                                                    <td>--}}
{{--                                                                        <div class="form-check form-check-inline me-0">--}}
{{--                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Bank" data-marks="5">--}}
{{--                                                                        </div>--}}
{{--                                                                    </td>--}}
{{--                                                                    <td>Poor Income and Credit History</td>--}}
{{--                                                                    <td>-</td>--}}
{{--                                                                    <td>High Risk Co-applicant</td>--}}
{{--                                                                    <td>5 Marks</td>--}}
{{--                                                                </tr>--}}

{{--                                                                <tr>--}}
{{--                                                                    <td></td>--}}
{{--                                                                    <td colspan="4" style="color: #000">--}}
{{--                                                                        <strong>Prospective Earnings of the Borrower Post Completion</strong>--}}
{{--                                                                    </td>--}}
{{--                                                                </tr>--}}


{{--                                                                <tr>--}}
{{--                                                                    <td>--}}
{{--                                                                        <div class="form-check form-check-inline me-0">--}}
{{--                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Loan" data-marks="20">--}}
{{--                                                                        </div>--}}
{{--                                                                    </td>--}}
{{--                                                                    <td>Excellent Prospective Earnings</td>--}}
{{--                                                                    <td>-</td>--}}
{{--                                                                    <td>Exceptional Earning Potential</td>--}}
{{--                                                                    <td>20 Marks</td>--}}
{{--                                                                </tr>--}}


{{--                                                                <tr>--}}
{{--                                                                    <td>--}}
{{--                                                                        <div class="form-check form-check-inline me-0">--}}
{{--                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Identity" data-marks="15">--}}
{{--                                                                        </div>--}}
{{--                                                                    </td>--}}
{{--                                                                    <td>Good Prospective Earnings</td>--}}
{{--                                                                    <td>-</td>--}}
{{--                                                                    <td>Strong Earning Potential</td>--}}
{{--                                                                    <td>15 Marks</td>--}}
{{--                                                                </tr>--}}

{{--                                                                <tr>--}}
{{--                                                                    <td>--}}
{{--                                                                        <div class="form-check form-check-inline me-0">--}}
{{--                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Credit" data-marks="10">--}}
{{--                                                                        </div>--}}
{{--                                                                    </td>--}}
{{--                                                                    <td>Moderate Prospective Earnings</td>--}}
{{--                                                                    <td>-</td>--}}
{{--                                                                    <td>Moderate Earning Potential</td>--}}
{{--                                                                    <td>10 Marks</td>--}}
{{--                                                                </tr>--}}

{{--                                                                <tr>--}}
{{--                                                                    <td>--}}
{{--                                                                        <div class="form-check form-check-inline me-0">--}}
{{--                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Bank" data-marks="5">--}}
{{--                                                                        </div>--}}
{{--                                                                    </td>--}}
{{--                                                                    <td>Low Prospective Earnings</td>--}}
{{--                                                                    <td>-</td>--}}
{{--                                                                    <td>Limited Earning Potential</td>--}}
{{--                                                                    <td>5 Marks</td>--}}
{{--                                                                </tr>--}}

{{--                                                                <tr>--}}
{{--                                                                    <td></td>--}}
{{--                                                                    <td colspan="3" class="text-end fw-bold text-dark">Total</td>--}}
{{--                                                                    <td class="fw-bold text-dark"><span id="totalMarksTable2">0</span> Marks</td>--}}
{{--                                                                </tr>--}}
{{--                                                                <tr>--}}
{{--                                                                    <td></td>--}}
{{--                                                                    <td colspan="3" class="text-end fw-bold text-dark">Total Percentage</td>--}}
{{--                                                                    <td class="fw-bold text-dark"><span id="totalPercentageTable2">0</span>%</td>--}}
{{--                                                                </tr>--}}



{{--                                                                </tbody>--}}


{{--                                                            </table>--}}
{{--                                                        </div>--}}

{{--                                                        <p class="mt-2 mb-25 text-dark customapplsmallhead"><strong>3) Detailed Financial  Analysis</strong></p>--}}
{{--                                                        <p class="font-small-3 mb-1">40% (The committee review and refines the applicant’s score based on:</p>--}}

{{--                                                        <div class="table-responsive">--}}
{{--                                                            <table id="table3" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">--}}
{{--                                                                <thead>--}}
{{--                                                                <tr>--}}
{{--                                                                    <th class="20px">--}}
{{--                                                                        <div class="form-check form-check-inline me-0">--}}
{{--                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Parameter">--}}
{{--                                                                        </div>--}}
{{--                                                                    </th>--}}
{{--                                                                    <th width="300px">Parameter</th>--}}
{{--                                                                    <th>Weightage %</th>--}}
{{--                                                                    <th>Sub criteria</th>--}}
{{--                                                                    <th>Marks Allocation</th>--}}
{{--                                                                </tr>--}}
{{--                                                                </thead>--}}
{{--                                                                <tbody>--}}
{{--                                                                <tr>--}}
{{--                                                                    <td></td>--}}
{{--                                                                    <td colspan="4" style="color: #000">--}}
{{--                                                                        <strong>For Microfinance</strong> Stability of borrower’s income source or business--}}
{{--                                                                    </td>--}}
{{--                                                                </tr>--}}
{{--                                                                <tr>--}}
{{--                                                                    <td>--}}
{{--                                                                        <div class="form-check form-check-inline me-0">--}}
{{--                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Loan" data-marks="40">--}}
{{--                                                                        </div>--}}
{{--                                                                    </td>--}}
{{--                                                                    <td>Highly Stable and Reliable Income Source</td>--}}
{{--                                                                    <td>-</td>--}}
{{--                                                                    <td>Highly Stable Income Source</td>--}}
{{--                                                                    <td>40 Marks</td>--}}
{{--                                                                </tr>--}}


{{--                                                                <tr>--}}
{{--                                                                    <td>--}}
{{--                                                                        <div class="form-check form-check-inline me-0">--}}
{{--                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Identity" data-marks="30">--}}
{{--                                                                        </div>--}}
{{--                                                                    </td>--}}
{{--                                                                    <td>Stable Income Source with Minor Risks</td>--}}
{{--                                                                    <td>-</td>--}}
{{--                                                                    <td>Stable Income Source with Minor Variability</td>--}}
{{--                                                                    <td>30 Marks</td>--}}
{{--                                                                </tr>--}}

{{--                                                                <tr>--}}
{{--                                                                    <td>--}}
{{--                                                                        <div class="form-check form-check-inline me-0">--}}
{{--                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Credit" data-marks="20">--}}
{{--                                                                        </div>--}}
{{--                                                                    </td>--}}
{{--                                                                    <td>Moderately Stable Income Source with Some Uncertainty</td>--}}
{{--                                                                    <td>-</td>--}}
{{--                                                                    <td>Moderately Stable with Income Risks</td>--}}
{{--                                                                    <td>20 Marks</td>--}}
{{--                                                                </tr>--}}

{{--                                                                <tr>--}}
{{--                                                                    <td>--}}
{{--                                                                        <div class="form-check form-check-inline me-0">--}}
{{--                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Bank" data-marks="10">--}}
{{--                                                                        </div>--}}
{{--                                                                    </td>--}}
{{--                                                                    <td>Unstable Income Source or Business</td>--}}
{{--                                                                    <td>-</td>--}}
{{--                                                                    <td>High-Risk Income Source</td>--}}
{{--                                                                    <td>10 Marks</td>--}}
{{--                                                                </tr>--}}
{{--                                                                <tr>--}}
{{--                                                                    <td></td>--}}
{{--                                                                    <td colspan="3" class="text-end fw-bold text-dark">Total</td>--}}
{{--                                                                    <td class="fw-bold text-dark"><span id="totalMarksTable3">0</span> Marks</td>--}}
{{--                                                                </tr>--}}
{{--                                                                <tr>--}}
{{--                                                                    <td></td>--}}
{{--                                                                    <td colspan="3" class="text-end fw-bold text-dark">Total Percentage</td>--}}
{{--                                                                    <td class="fw-bold text-dark"><span id="totalPercentageTable3">0</span>%</td>--}}
{{--                                                                </tr>--}}



{{--                                                                </tbody>--}}


{{--                                                            </table>--}}
{{--                                                        </div>--}}
                                                        @if($loan->type == 3)
                                                            <p class="mt-2 mb-25 text-dark customapplsmallhead"><strong>1) Detailed Financial  Analysis</strong></p>
                                                            <p class="font-small-3 mb-1">40% (The committee review and refines the applicant’s score based on</p>

                                                            <div class="table-responsive">
                                                                <table id="table1" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                    <thead>
                                                                    <tr>
                                                                        <th class="20px">
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="checkbox" name="podetail" id="Parameter">
                                                                            </div>
                                                                        </th>
                                                                        <th width="300px">Parameter</th>
                                                                        <th>Weightage %</th>
                                                                        <th>Sub criteria</th>
                                                                        <th>Marks Allocation</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <tr>
                                                                        <td></td>
                                                                        <td colspan="4" style="color: #000">
                                                                            <strong>For Terms loans</strong> (Feasibility of the project and profitability)
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="checkbox" name="podetail" id="Loan" data-marks="20">
                                                                            </div>
                                                                        </td>
                                                                        <td>Excellent Feasibility and Profitability</td>
                                                                        <td>-</td>
                                                                        <td>Highly Viable and Profitable</td>
                                                                        <td>20 Marks</td>
                                                                    </tr>


                                                                    <tr>
                                                                        <td>
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="checkbox" name="podetail" id="Identity" data-marks="15">
                                                                            </div>
                                                                        </td>
                                                                        <td>Good Feasibility and Profitability</td>
                                                                        <td>-</td>
                                                                        <td>Viable with Strong Profit Potential</td>
                                                                        <td>15 Marks</td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="checkbox" name="podetail" id="Credit" data-marks="10">
                                                                            </div>
                                                                        </td>
                                                                        <td>Moderate Feasibility and Profitability</td>
                                                                        <td>-</td>
                                                                        <td>Moderately Feasible with Limited Profitability</td>
                                                                        <td>10 Marks</td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="checkbox" name="podetail" id="Bank" data-marks="5">
                                                                            </div>
                                                                        </td>
                                                                        <td>Poor Feasibility and Profitability</td>
                                                                        <td>-</td>
                                                                        <td>High Risk with Limited Profit Potential</td>
                                                                        <td>5 Marks</td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td></td>
                                                                        <td colspan="4" style="color: #000">
                                                                            <strong>Cash flow projection</strong>
                                                                        </td>
                                                                    </tr>


                                                                    <tr>
                                                                        <td>
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="checkbox" name="podetail" id="Loan" data-marks="20">
                                                                            </div>
                                                                        </td>
                                                                        <td>Excellent Cash Flow Stability</td>
                                                                        <td>-</td>
                                                                        <td>Highly Stable and Predictable Cash Flow</td>
                                                                        <td>20 Marks</td>
                                                                    </tr>


                                                                    <tr>
                                                                        <td>
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="checkbox" name="podetail" id="Identity" data-marks="15">
                                                                            </div>
                                                                        </td>
                                                                        <td>Good Cash Flow with Some Variability</td>
                                                                        <td>-</td>
                                                                        <td>Stable Cash Flow with Manageable Variability</td>
                                                                        <td>15 Marks</td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="checkbox" name="podetail" id="Credit" data-marks="10">
                                                                            </div>
                                                                        </td>
                                                                        <td>Moderate Cash Flow Stability</td>
                                                                        <td>-</td>
                                                                        <td>Moderately Stable Cash Flow with Some Concerns</td>
                                                                        <td>10 Marks</td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="checkbox" name="podetail" id="Bank" data-marks="5">
                                                                            </div>
                                                                        </td>
                                                                        <td>Weak or Unstable Cash Flow</td>
                                                                        <td>-</td>
                                                                        <td>Unstable Cash Flow with High Risk</td>
                                                                        <td>5 Marks</td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td></td>
                                                                        <td colspan="3" class="text-end fw-bold text-dark">Total</td>
                                                                        <td class="fw-bold text-dark"><span id="totalMarksTable1">0</span> Marks</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td></td>
                                                                        <td colspan="3" class="text-end fw-bold text-dark">Total Percentage</td>
                                                                        <td class="fw-bold text-dark"><span id="totalPercentageTable1">0</span>%</td>
                                                                    </tr>
                                                                    </tbody>


                                                                </table>
                                                            </div>
                                                        @else
                                                            <p class="mt-2 mb-25 text-dark customapplsmallhead"><strong>4) Detailed Financial  Analysis</strong></p>
                                                            <p class="font-small-3 mb-1">40% (The committee review and refines the applicant’s score based on:</p>


                                                            <div class="table-responsive">
                                                                <table id="table4" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                    <thead>
                                                                    <tr>
                                                                        <th class="20px">
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="checkbox" name="podetail" id="Parameter">
                                                                            </div>
                                                                        </th>
                                                                        <th width="300px">Parameter</th>
                                                                        <th>Weightage %</th>
                                                                        <th>Sub criteria</th>
                                                                        <th>Marks Allocation</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <tr>
                                                                        <td></td>
                                                                        <td colspan="4" style="color: #000">
                                                                            <strong>For Housing loan</strong> Borrowser’s income stablity, debt-to-income ratio, credit history, and the property;s appraised value to ensure the borrower’s capablity to repay the loan and the property’s adequacy as collaterral
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="checkbox" name="podetail" id="Loan" data-marks="40">
                                                                            </div>
                                                                        </td>
                                                                        <td>Highly Capable Borrower</td>
                                                                        <td>-</td>
                                                                        <td>Stable, high income with low debt, excellent credit, and a highly appraised property.</td>
                                                                        <td>40 Marks</td>
                                                                    </tr>


                                                                    <tr>
                                                                        <td>
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="checkbox" name="podetail" id="Identity" data-marks="30">
                                                                            </div>
                                                                        </td>
                                                                        <td>Reliable Borrower</td>
                                                                        <td>-</td>
                                                                        <td>Good income stability, moderate debt, solid credit, and an appraised property of sufficient value.</td>
                                                                        <td>30 Marks</td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="checkbox" name="podetail" id="Credit" data-marks="20">
                                                                            </div>
                                                                        </td>
                                                                        <td>Moderately Reliable Borrower</td>
                                                                        <td>-</td>
                                                                        <td>Some financial instability, higher debt ratio, fair credit, and an adequately valued property.</td>
                                                                        <td>20 Marks</td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>
                                                                            <div class="form-check form-check-inline me-0">
                                                                                <input class="form-check-input" type="checkbox" name="podetail" id="Bank" data-marks="10">
                                                                            </div>
                                                                        </td>
                                                                        <td>High-Risk Borrower</td>
                                                                        <td>-</td>
                                                                        <td>Unstable income, high debt, poor credit, and low property value, posing a high risk to repayment.</td>
                                                                        <td>10 Marks</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td></td>
                                                                        <td colspan="3" class="text-end fw-bold text-dark">Total</td>
                                                                        <td class="fw-bold text-dark"><span id="totalMarksTable4">0</span> Marks</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td></td>
                                                                        <td colspan="3" class="text-end fw-bold text-dark">Total Percentage</td>
                                                                        <td class="fw-bold text-dark"><span id="totalPercentageTable4">0</span>%</td>
                                                                    </tr>



                                                                    </tbody>


                                                                </table>
                                                            </div>
                                                        @endif


                                                        <p class="mt-2 mb-25 text-dark customapplsmallhead"><strong>5) Collateral Adequacy</strong></p>
                                                        <p class="font-small-3 mb-1">30% (The committee review and refines the applicant’s score based on:</p>


                                                        <div class="table-responsive">
                                                            <table id="table5" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                <thead>
                                                                <tr>
                                                                    <th class="20px">
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Parameter">
                                                                        </div>
                                                                    </th>
                                                                    <th width="300px">Parameter</th>
                                                                    <th>Weightage %</th>
                                                                    <th>Sub criteria</th>
                                                                    <th>Marks Allocation</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <tr>
                                                                    <td></td>
                                                                    <td colspan="4" style="color: #000">
                                                                        <strong>Review confessional valuations and adequacy of collateral</strong>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Loan" data-marks="30">
                                                                        </div>
                                                                    </td>
                                                                    <td>Excellent Collateral Adequacy</td>
                                                                    <td>-</td>
                                                                    <td>Highly Secure Collateral</td>
                                                                    <td>30 Marks</td>
                                                                </tr>


                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Identity" data-marks="20">
                                                                        </div>
                                                                    </td>
                                                                    <td>Good Collateral Adequacy</td>
                                                                    <td>-</td>
                                                                    <td>Secure Collateral with Minor Concerns</td>
                                                                    <td>20 Marks</td>
                                                                </tr>

                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Credit" data-marks="10">
                                                                        </div>
                                                                    </td>
                                                                    <td>Moderate Collateral Adequacy</td>
                                                                    <td>-</td>
                                                                    <td>Moderately Secure Collateral</td>
                                                                    <td>10 Marks</td>
                                                                </tr>

                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Bank" data-marks="5">
                                                                        </div>
                                                                    </td>
                                                                    <td>Inadequate Collateral</td>
                                                                    <td>-</td>
                                                                    <td>High-Risk Collateral</td>
                                                                    <td>5 Marks</td>
                                                                </tr>
                                                                <tr>
                                                                    <td></td>
                                                                    <td colspan="3" class="text-end fw-bold text-dark">Total</td>
                                                                    <td class="fw-bold text-dark"><span id="totalMarksTable5">0</span> Marks</td>
                                                                </tr>
                                                                <tr>
                                                                    <td></td>
                                                                    <td colspan="3" class="text-end fw-bold text-dark">Total Percentage</td>
                                                                    <td class="fw-bold text-dark"><span id="totalPercentageTable5">0</span>%</td>
                                                                </tr>



                                                                </tbody>


                                                            </table>
                                                        </div>


                                                        <p class="mt-2 mb-25 text-dark customapplsmallhead"><strong>6) Collateral Adequacy</strong></p>
                                                        <p class="font-small-3 mb-1">30% (The committee review and refines the applicant’s score based on:</p>


                                                        <div class="table-responsive">
                                                            <table id="table6" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                <thead>
                                                                <tr>
                                                                    <th class="20px">
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Parameter">
                                                                        </div>
                                                                    </th>
                                                                    <th width="300px">Parameter</th>
                                                                    <th>Weightage %</th>
                                                                    <th>Sub criteria</th>
                                                                    <th>Marks Allocation</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <tr>
                                                                    <td></td>
                                                                    <td colspan="4" style="color: #000">
                                                                        <strong>Review confessional valuations and adequacy of collateral</strong>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Loan" data-marks="30">
                                                                        </div>
                                                                    </td>
                                                                    <td>Excellent - Highly Secure Collateral</td>
                                                                    <td>-</td>
                                                                    <td>Collateral is highly valuable, well-documented, and fully covers the loan amount with no risks.</td>
                                                                    <td>30 Marks</td>
                                                                </tr>


                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Identity" data-marks="20">
                                                                        </div>
                                                                    </td>
                                                                    <td>Good- Secure Collateral with Minor Concerns</td>
                                                                    <td>-</td>
                                                                    <td>Collateral is generally adequate, with minor concerns but still sufficient to secure the loan.</td>
                                                                    <td>20 Marks</td>
                                                                </tr>

                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Credit" data-marks="10">
                                                                        </div>
                                                                    </td>
                                                                    <td>Moderate- Moderately Secure Collateral</td>
                                                                    <td>-</td>
                                                                    <td>Collateral is somewhat inadequate, with risks in value or liquidity, requiring careful monitoring.</td>
                                                                    <td>10 Marks</td>
                                                                </tr>

                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Bank" data-marks="5">
                                                                        </div>
                                                                    </td>
                                                                    <td>Poor- High-Risk Collateral</td>
                                                                    <td>-</td>
                                                                    <td>Collateral is inadequate and poses a significant risk to the loan’s security in the event of default.</td>
                                                                    <td>5 Marks</td>
                                                                </tr>
                                                                <tr>
                                                                    <td></td>
                                                                    <td colspan="3" class="text-end fw-bold text-dark">Total</td>
                                                                    <td class="fw-bold text-dark"><span id="totalMarksTable6">0</span> Marks</td>
                                                                </tr>
                                                                <tr>
                                                                    <td></td>
                                                                    <td colspan="3" class="text-end fw-bold text-dark">Total Percentage</td>
                                                                    <td class="fw-bold text-dark"><span id="totalPercentageTable6">0</span>%</td>
                                                                </tr>



                                                                </tbody>


                                                            </table>
                                                        </div>


                                                        <p class="mt-2 mb-25 text-dark customapplsmallhead"><strong>7) Compliance and Risks</strong></p>
                                                        <p class="font-small-3 mb-1">20% (The committee review and refines the applicant’s score based on:</p>


                                                        <div class="table-responsive">
                                                            <table id="table7" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                <thead>
                                                                <tr>
                                                                    <th class="20px">
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Parameter">
                                                                        </div>
                                                                    </th>
                                                                    <th width="300px">Parameter</th>
                                                                    <th>Weightage %</th>
                                                                    <th>Sub criteria</th>
                                                                    <th>Marks Allocation</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <tr>
                                                                    <td></td>
                                                                    <td colspan="4" style="color: #000">
                                                                        <strong>Check revelatory adherence</strong>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Loan" data-marks="20">
                                                                        </div>
                                                                    </td>
                                                                    <td>Excellent - Fully Compliant and Risk-Free</td>
                                                                    <td>-</td>
                                                                    <td>Full adherence to regulations, excellent risk management, no legal or regulatory risks.</td>
                                                                    <td>20 Marks</td>
                                                                </tr>


                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Identity"  data-marks="15">
                                                                        </div>
                                                                    </td>
                                                                    <td>Good- Compliant with Minor Risks</td>
                                                                    <td>-</td>
                                                                    <td>Good compliance with minor risks, with few concerns or small gaps in documentation or practice.</td>
                                                                    <td>15 Marks</td>
                                                                </tr>

                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Credit"  data-marks="10">
                                                                        </div>
                                                                    </td>
                                                                    <td>Moderate- Partially Compliant with Moderate Risks</td>
                                                                    <td>-</td>
                                                                    <td>Moderate compliance with notable gaps, some regulatory or legal risks that need attention.</td>
                                                                    <td>10 Marks</td>
                                                                </tr>

                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Bank"  data-marks="5">
                                                                        </div>
                                                                    </td>
                                                                    <td>Poor- Non-Compliant and High-Risk</td>
                                                                    <td>-</td>
                                                                    <td>Non-compliant borrower with high risk due to poor adherence to regulatory standards and risks.</td>
                                                                    <td>5 Marks</td>
                                                                </tr>
                                                                <tr>
                                                                    <td></td>
                                                                    <td colspan="3" class="text-end fw-bold text-dark">Total</td>
                                                                    <td class="fw-bold text-dark"><span id="totalMarksTable7">0</span> Marks</td>
                                                                </tr>
                                                                <tr>
                                                                    <td></td>
                                                                    <td colspan="3" class="text-end fw-bold text-dark">Total Percentage</td>
                                                                    <td class="fw-bold text-dark"><span id="totalPercentageTable7">0</span>%</td>
                                                                </tr>



                                                                </tbody>


                                                            </table>
                                                        </div>


                                                        <p class="mt-2 mb-25 text-dark customapplsmallhead"><strong>8) Community and Socail Impact</strong></p>
                                                        <p class="font-small-3 mb-1">10% (The committee review and refines the applicant’s score based on:</p>


                                                        <div class="table-responsive">
                                                            <table id="table8" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                <thead>
                                                                <tr>
                                                                    <th class="20px">
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Parameter">
                                                                        </div>
                                                                    </th>
                                                                    <th width="300px">Parameter</th>
                                                                    <th>Weightage %</th>
                                                                    <th>Sub criteria</th>
                                                                    <th>Marks Allocation</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <tr>
                                                                    <td></td>
                                                                    <td colspan="4" style="color: #000">
                                                                        <strong>For Microfinance</strong>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Loan" data-marks="10">
                                                                        </div>
                                                                    </td>
                                                                    <td>Excellent - Positive Community Champion</td>
                                                                    <td>-</td>
                                                                    <td>Full adherence to regulations, excellent risk management, no legal or regulatory risks.</td>
                                                                    <td>10 Marks</td>
                                                                </tr>


                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Identity" data-marks="5">
                                                                        </div>
                                                                    </td>
                                                                    <td>Good- Moderate Community Impact</td>
                                                                    <td>-</td>
                                                                    <td>Some positive effects, but limited in scope or duration, with moderate benefits to the community.</td>
                                                                    <td>5 Marks</td>
                                                                </tr>

                                                                <tr>
                                                                    <td>
                                                                        <div class="form-check form-check-inline me-0">
                                                                            <input class="form-check-input" type="checkbox" name="podetail" id="Credit" data-marks="0">
                                                                        </div>
                                                                    </td>
                                                                    <td>Poor- No/very less Community Impact</td>
                                                                    <td>-</td>
                                                                    <td>Minimal to no positive impact, or potentially negative effects on the community or local economy.</td>
                                                                    <td>0 Marks</td>
                                                                </tr>

                                                                <tr>
                                                                    <td></td>
                                                                    <td colspan="3" class="text-end fw-bold text-dark">Total</td>
                                                                    <td class="fw-bold text-dark"><span id="totalMarksTable8">0</span> Marks</td>
                                                                </tr>
                                                                <tr>
                                                                    <td></td>
                                                                    <td colspan="3" class="text-end fw-bold text-dark">Total Percentage</td>
                                                                    <td class="fw-bold text-dark"><span id="totalPercentageTable8">0</span>%</td>
                                                                </tr>



                                                                </tbody>


                                                            </table>
                                                        </div>








                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- Modal to add new record -->
                </section>

            </div>
        </div>
    </div>
    <!-- END: Content-->
    <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Repayment Start Period</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body max_value">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="myModalLabel17"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select
                            Pending
                            Disbursal</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Loan Type</label>
                                <select class="form-select">
                                    <option>Select</option>
                                    <option>Home Loan</option>
                                    <option>Vehicle Loan</option>
                                    <option>Term Loan</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Customer Name</label>
                                <select class="form-select select2">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Application No.</label>
                                <select class="form-select select2">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-3  mb-1">
                            <label class="form-label">&nbsp;</label><br />
                            <button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                        </div>

                        <div class="col-md-12">


                            <div class="table-responsive">
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Application No.</th>
                                        <th>Date</th>
                                        <th>Customer Name</th>
                                        <th>Loan Type</th>
                                        <th>Disbursal Milestone</th>
                                        <th>Disbursal Amt.</th>
                                        <th>Mobile No.</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>
                                            <div class="form-check form-check-primary">
                                                <input type="radio" id="customColorRadio3" name="customColorRadio3"
                                                       class="form-check-input" checked="">
                                            </div>
                                        </td>
                                        <td>HL/2024/001</td>
                                        <td>20-07-2024</td>
                                        <td class="fw-bolder text-dark">Kundan Kumar</td>
                                        <td>Term</td>
                                        <td>1st floor completed</td>
                                        <td>200000</td>
                                        <td>9876787656</td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <div class="form-check form-check-primary">
                                                <input type="radio" id="customColorRadio3" name="customColorRadio3"
                                                       class="form-check-input" checked="">
                                            </div>
                                        </td>
                                        <td>HL/2024/001</td>
                                        <td>20-07-2024</td>
                                        <td class="fw-bolder text-dark">Kundan Kumar</td>
                                        <td>Term</td>
                                        <td>2nd floor completed</td>
                                        <td>200000</td>
                                        <td>nishu@gmail.com</td>
                                    </tr>





                                    </tbody>


                                </table>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                            data-feather="x-circle"></i>
                        Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i>
                        Process</button>
                </div>
            </div>
        </div>
    </div>

@endsection



@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function calculateTotalMarksAndPercentage(tableId, totalMarksId, totalPercentageId) {
                const table = document.getElementById(tableId);
                if (!table) {
                    console.error(`Table with ID ${tableId} not found.`);
                    return;
                }

                const checkboxes = table.querySelectorAll('input[type="checkbox"]');
                const totalMarksSpan = document.getElementById(totalMarksId);
                const totalPercentageSpan = document.getElementById(totalPercentageId);

                if (!totalMarksSpan || !totalPercentageSpan) {
                    console.error(`Total marks or percentage span not found for table ${tableId}.`);
                    return;
                }

                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', () => {
                        let totalMarks = 0;
                        let totalWeightage = 0;

                        checkboxes.forEach(cb => {
                            const row = cb.closest('tr');
                            if (!row) {
                                console.error('Row not found for checkbox:', cb);
                                return;
                            }

                            const weightageCell = row.querySelector('td:nth-child(3)'); // Select the "Weightage %" column
                            if (!weightageCell) {
                                console.error('Weightage cell not found for checkbox:', cb);
                                return;
                            }

                            const marks = parseInt(cb.getAttribute('data-marks') || 0);

                            if (cb.checked) {
                                totalMarks += marks;
                                totalWeightage += marks;
                                weightageCell.textContent = marks + '%'; // Update the weightage percentage
                            } else {
                                weightageCell.textContent = '-'; // Reset to "-" if unchecked
                            }
                        });

                        totalMarksSpan.textContent = totalMarks;
                        totalPercentageSpan.textContent = totalWeightage ;
                    });
                });
            }

            calculateTotalMarksAndPercentage('table1', 'totalMarksTable1', 'totalPercentageTable1');
            calculateTotalMarksAndPercentage('table2', 'totalMarksTable2', 'totalPercentageTable2');
            calculateTotalMarksAndPercentage('table3', 'totalMarksTable3', 'totalPercentageTable3');
            calculateTotalMarksAndPercentage('table4', 'totalMarksTable4', 'totalPercentageTable4');
            calculateTotalMarksAndPercentage('table5', 'totalMarksTable5', 'totalPercentageTable5');
            calculateTotalMarksAndPercentage('table6', 'totalMarksTable6', 'totalPercentageTable6');
            calculateTotalMarksAndPercentage('table7', 'totalMarksTable7', 'totalPercentageTable7');
            calculateTotalMarksAndPercentage('table8', 'totalMarksTable8', 'totalPercentageTable8');
        });

        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })


        $(function() {
            $(".ledgerselecct").autocomplete({
                source: [
                    "Furniture (IT001)",
                    "Chair (IT002)",
                    "Table (IT003)",
                    "Laptop (IT004)",
                    "Bags (IT005)",
                ],
                minLength: 0
            }).focus(function() {
                if (this.value == "") {
                    $(this).autocomplete("search");
                }
            });
        });


        // -------------------- Form Handling Javascript (Starts) -------------------- //
        $(document).on('submit', '#appraisal-form', function(event) {
            event.preventDefault();

            // Reset messages and error highlights
            $('#success-message').hide().text('');
            $('#error-message').hide();
            $('#error-list').empty();
            $("button[type=submit]").prop('disabled', true);

            // Calculate the grand total marks
            // let totalMarksTable1 = parseInt(document.getElementById('totalMarksTable1').textContent) || 0;
            // let totalMarksTable2 = parseInt(document.getElementById('totalMarksTable2').textContent) || 0;
            // let totalMarksTable3 = parseInt(document.getElementById('totalMarksTable3').textContent) || 0;
            var totalMarksTable4 = parseInt(document.getElementById('{{ $loan->type == 3 ? 'totalMarksTable1' : 'totalMarksTable4' }}').textContent) || 0;
            let totalMarksTable5 = parseInt(document.getElementById('totalMarksTable5').textContent) || 0;
            let totalMarksTable6 = parseInt(document.getElementById('totalMarksTable6').textContent) || 0;
            let totalMarksTable7 = parseInt(document.getElementById('totalMarksTable7').textContent) || 0;
            let totalMarksTable8 = parseInt(document.getElementById('totalMarksTable8').textContent) || 0;

            let grandTotalMarks = totalMarksTable4 + totalMarksTable5 + totalMarksTable6 + totalMarksTable7 + totalMarksTable8;
            if (grandTotalMarks < 100) {
                $('#error-message').text('The total marks must be equal to or greater than 100.').show();
                $("button[type=submit]").prop('disabled', false);
                return;
            }

            // Gather checkbox data
            let financialAnalysis = [];
            let Collateral = [];
            let Collateral1 = [];
            let complianceAndRisk = [];
            let Community = [];

            function gatherData(tableId, array) {
                document.querySelectorAll(`#${tableId} tbody tr`).forEach(row => {
                    let checkbox = row.querySelector('input[type="checkbox"]');
                    if (checkbox && checkbox.checked) {
                        let parameter = row.cells[1].innerText;
                        let weightage = row.cells[2].innerText;
                        let subCriteria = row.cells[3].innerText;
                        let marks = row.cells[4].innerText;

                        array.push({
                            parameter: parameter,
                            weightage: weightage,
                            sub_criteria: subCriteria,
                            marks: marks
                        });
                    }
                });
            }
            gatherData('{{ $loan->type == 3 ? 'table1' : 'table4' }}', financialAnalysis);
            gatherData('table5', Collateral);
            gatherData('table6', Collateral1);
            gatherData('table7', complianceAndRisk);
            gatherData('table8', Community);

            let checkboxData = {
                financial_analysis: financialAnalysis,
                collateral_1: Collateral,
                collateral_2: Collateral1,
                compliance_and_risk: complianceAndRisk,
                community: Community
            };

            var formData = new FormData(this);
            formData.append('checkbox_data', JSON.stringify(checkboxData));
            // formData.append('status', "approved");
            // console.log(formData);
            $.ajax({
                url: '{{ route('loanApproval.update-approval') }}',
                type: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function(response) {
                    $("button[type=submit]").prop('disabled', false);
                    if (response.status === true) {
                        $("input[type='text'],input[type='number'],select").removeClass('is-invalid');
                        $('#appraisal-form')[0].reset();
                        $('#success-message').text(response.message).show();
                        window.location.href = '{{ route('loanAppraisal.index') }}';
                    } else {
                        var errors = response.errors;
                        $("input[type='text'],input[type='number'],select").removeClass('is-invalid');
                        $.each(errors, function(key, value) {
                            $(`#${key}`).addClass('is-invalid');
                            $('#error-list').append('<li>' + value[0] + '</li>');
                        });
                        $('#error-message').show();
                    }
                },
                error: function(jqXHR, exception) {
                    $("button[type=submit]").prop('disabled', false); // Enable submit button on error
                    console.log("An error occurred:", exception);
                    $('#error-message').text('Something went wrong, please try again.').show();
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            $('.submission_val').click(function() {
                let data_val = $(this).attr('data-val');
                $("#status").val(data_val);
            });
        });

        $(document).on('keyup', '#term_loan', function() {
            $('#disbursal_amount_0').val(formatIndianNumber($(this).val()))
        });

        // Fetch Interest Rate
        document.addEventListener('DOMContentLoaded', function() {
            $('#cibil_score').on('input', function() {
                let cibil_score = $(this).val();
                $.ajax({
                    url: '{{ route('loanAppraisal.getInterestRate') }}',
                    method: 'POST',
                    data: {
                        cibil_score: cibil_score
                    },
                    success: function(response) {
                        let interestRate = response.base_rate;
                        $('#interest_rate').val(interestRate);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching helper value:', error);
                        $('#interest_rate').val('');
                    }
                });
            });
        });

        function calculateLoanRepayment(loanAmount, interestRate, numYears, gracePeriod) {
            let repaymentSchedule = [];
            let remainingBalance = loanAmount;
            let interestRateDecimal = interestRate / 100;

            // let Repayment = $("#repayment_start_after").val();
            // if (Repayment == '1st Disbursement') {
            //     gracePeriod = 1;
            // }
            // if (Repayment == '2nd Disbursement') {
            //     gracePeriod = 2;
            // }
            let Repayment = removeCommas($("#repayment_start_period").val());
            gracePeriod = Repayment;

            console.log(gracePeriod);

            let principalPerYear = loanAmount / (numYears - gracePeriod);


            console.log(principalPerYear);

            // Year 1 - Grace period, no repayment
            let interestFirstYear = parseFloat((remainingBalance * interestRateDecimal).toFixed(2));
            // repaymentSchedule.push({
            //     year: 1,
            //     amount_at_start: remainingBalance.toFixed(2),
            //     interest_amount: interestFirstYear,
            //     repayment_amount: Repayment === '1st Disbursement' || Repayment === '2nd Disbursement' ? 0 : principalPerYear.toFixed(2),
            //     amount_at_end: Repayment === '1st Disbursement' || Repayment === '2nd Disbursement' ? remainingBalance.toFixed(2) : remainingBalance.toFixed(2) - principalPerYear.toFixed(2)
            // });

            // Years 2 to numYears - Repayment period
            for (let year = 1; year <= numYears; year++) {
                let interest = parseFloat((remainingBalance * interestRateDecimal).toFixed(2));
                let totalRepayment = (interest + parseFloat(principalPerYear)).toFixed(2);

                // remainingBalance -= principalPerYear;

                repaymentSchedule.push({
                    year: year,
                    amount_at_start: parseFloat(remainingBalance.toFixed(2)),
                    interest_amount: parseFloat(interest).toFixed(2),
                    repayment_amount: ((Repayment && Repayment >= year)) ? 0 : parseFloat(principalPerYear).toFixed(
                        2),
                    amount_at_end: ((Repayment && Repayment >= year)) ?
                        parseFloat(remainingBalance).toFixed(2) : (parseFloat(remainingBalance) - parseFloat(
                            principalPerYear)).toFixed(2)
                });

                if (Repayment && Repayment >= year) {

                } else {
                    remainingBalance -= principalPerYear;
                }

            }


            return repaymentSchedule;
        }

        function displaySchedule() {
            // Assuming you have input fields to get loan amount, interest rate, etc.
            let loanAmount = parseFloat(removeCommas($('#term_loan').val()));
            let interestRate = parseFloat(removeCommas($('#interest_rate').val()));
            let numYears = parseInt($('#no_of_installments').val());
            let gracePeriod = 0;

            // Calculate the repayment schedule
            let repaymentSchedule = calculateLoanRepayment(loanAmount, interestRate, numYears, gracePeriod);

            // Append rows to the table
            let tableBody = $('#repaymentTable tbody');
            tableBody.empty(); // Clear any existing rows

            let totalInterest = 0;
            let totalRepayment = 0;

            repaymentSchedule.forEach((data, index) => {
                let rowHtml = `
                    <tr>
                        <td>${index + 1}</td>
                        <td><input type="number" class="form-control mw-100 text-end" name="year[${index}]" id="year_${index}" value="${data.year}" readonly></td>
                        <td><input type="text" class="form-control mw-100 text-end" name="start_amount[${index}]" id="start_amount_${index}" value="${formatIndianNumber(data.amount_at_start)}" readonly></td>
                        <td><input type="text" class="form-control mw-100 text-end" name="interest_amount[${index}]" id="interest_amount_${index}" value="${formatIndianNumber(data.interest_amount)}" readonly></td>
                        <td><input type="text" class="form-control mw-100 text-end" name="repayment_amount[${index}]" id="repayment_amount_${index}" value="${formatIndianNumber(data.repayment_amount)}" readonly></td>
                        <td><input type="text" class="form-control mw-100 text-end" name="end_amount[${index}]" id="end_amount_${index}" value="${formatIndianNumber(data.amount_at_end)}" readonly></td>
                    </tr>
                `;
                tableBody.append(rowHtml);

                totalInterest += parseFloat(data.interest_amount);
                totalRepayment += parseFloat(data.repayment_amount);
            });
            rowHtml = `<tr>
                            <td colspan="3" class="text-end fw-bolder text-dark">Total</td>
                            <td class="fw-bolder text-dark text-end">${formatIndianNumber(totalInterest)}</td>
                            <td class="fw-bolder text-dark text-end">${formatIndianNumber(totalRepayment)}</td>
                        </tr>`;
            tableBody.append(rowHtml);
        }

        // Calculate Number Of Installments
        document.addEventListener('DOMContentLoaded', function() {
            $('#loan_period, #repayment_type').on('input', function() {
                let loan_period = parseFloat($('#loan_period').val()) || 0;
                let repayment_type = parseFloat($('#repayment_type').val()) || 0;
                let no_of_installments = 0;

                if (repayment_type == 1) {
                    no_of_installments = loan_period;
                } else if (repayment_type == 2) {
                    no_of_installments = loan_period * 2;
                } else if (repayment_type == 3) {
                    no_of_installments = loan_period * 4;
                } else if (repayment_type == 4) {
                    no_of_installments = loan_period * 12;
                } else {
                    no_of_installments = 0;
                }

                $('#no_of_installments').val(no_of_installments);
                $('#repayment_start_period').attr('max', no_of_installments);
                displaySchedule();
            });
        });

        // Fetch DPR Fields
        document.addEventListener('DOMContentLoaded', function() {
            $('#dpr_template').on('input', function() {
                let template_id = $(this).val();
                $.ajax({
                    url: '{{ route('loanAppraisal.getDprFields') }}',
                    method: 'POST',
                    data: {
                        template_id: template_id
                    },
                    success: function(response) {
                        let dprFields = response.dprFields;

                        // Clear the previous fields before appending new ones
                        $('#dprFieldsContainer').empty();

                        // Loop through each dprField and dynamically generate the HTML
                        dprFields.forEach(function(field) {
                            let html = `
                            <div class="col-md-6">
                                <div class="row align-items-center mb-1">
                                    <div class="col-md-4">
                                        <label class="form-label">${field.field_name}<span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="dpr[${field.id}]" placeholder="Enter ${field.field_name}">
                                        <p class="error text-danger"></p>
                                    </div>
                                </div>
                            </div>
                        `;

                            // Append the generated HTML to the container
                            $('#dprFieldsContainer').append(html);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching fields:', error);
                    }
                });
            });
        });

        // Disbursal Schedule - Append New Row
        $(".addDisbursal").click(function() {

            var rowCount = $("#disbursalTable").find('tr').length + 1;
            var totalAmount = parseFloat(removeCommas($('#term_loan').val()));
            var paidAmount = 0;
            var dis_amnt_nmber;
            let dis_amnt
            for (let i = 0; i < rowCount; i++) {
                dis_amnt_nmber = parseFloat(($(`#disbursal_amount_${i}`).val() || '0').replace(/,/g, '')) || 0;

                paidAmount += dis_amnt_nmber
            }
            var balanceAmount = totalAmount - paidAmount;

            $(`#disbursal_milestone_${rowCount-2}`).attr('readonly', true);
            $(`#disbursal_amount_${rowCount-2}`).attr('readonly', true);
            $(`#disbursal_remarks_${rowCount-2}`).attr('readonly', true);

            var newRow = `
    <tr>
        <td>${rowCount}</td>
        <td>
            <input type="text" name="disbursal_milestone[${rowCount-1}]" required id="disbursal_milestone_${rowCount-1}"
                class="form-control mw-100">
            <p class="error text-danger disbursal_milestone_${rowCount-1}-error"></p> <!-- Error placeholder -->
        </td>
        <td>
            <input type="text" name="disbursal_amount[${rowCount-1}]" required  id="disbursal_amount_${rowCount-1}"
                value="${(formatIndianNumber(balanceAmount))}" class="form-control mw-100">
            <p class="error text-danger disbursal_amount_${rowCount-1}-error"></p> <!-- Error placeholder -->
        </td>
        <td>
            <input type="text" name="disbursal_remarks[${rowCount-1}]"  id="disbursal_remarks_${rowCount-1}"
                class="form-control mw-100">
            <p class="error text-danger disbursal_remarks_${rowCount-1}-error"></p> <!-- Error placeholder -->
        </td>
        <td>
            <a href="#" class="text-primary deleteDisbursal">
                <i data-feather="trash-2"></i>
            </a>
        </td>
    </tr>`;

            $("#disbursalTable").append(newRow);
            feather.replace();
        });

        // Disbursal Schedule - Delete Row
        $("#disbursalTable").on("click", ".deleteDisbursal", function(event) {
            event.preventDefault();
            // Get the previous <tr> of the clicked button's closest <tr>
            let prevTr = $(this).closest('tr').prev();

            // Remove the readonly attribute from all input fields in the previous <tr>
            prevTr.find('input[readonly]').removeAttr('readonly');

            $(this).closest('tr').remove();
        });


        $('#interest_rate').on('change', function() {
            displaySchedule();
        });

        $('#repayment_start_period').on('change', function() {
            displaySchedule();
        });

        // Document Delete
        $(document).on('click', '.deleteDocument', function(e) {
            e.preventDefault();

            var documentId = $(this).data('id'); // Get the document ID
            var $row = $(this).closest('tr');

            if (confirm("Are you sure you want to delete this document?")) {
                var documentIdsArray;

                if (documentId.includes('#')) {
                    documentIdsArray = documentId.split('#'); // Split by `#` if it exists
                } else {
                    documentIdsArray = [documentId]; // Otherwise, wrap `documentId` in an array
                }

                // Loop through each ID
                documentIdsArray.forEach(function(id) {
                    console.log(id);
                    $.ajax({
                        url: '{{ route('loanAppraisal.deleteDocument') }}', // Route to delete document
                        type: 'POST', // Use POST method with '_method' => 'DELETE'
                        data: {
                            "_token": "{{ csrf_token() }}", // Include CSRF token
                            "_method": "DELETE", // Specify the method as DELETE
                            "document_id": id
                        },
                        success: function(response) {
                            if (response.status) { // Check for 'status' in response
                                alert(response.message);
                                $row.remove();
                            } else {
                                alert(response.message || "Something went wrong. Try again.");
                            }
                        },
                        error: function() {
                            alert("Failed to delete the document. Please try again later.");
                        }
                    });
                });
            }
        });





        // Document Upload - Append New Row
        $(".addRow").click(function() {

            var rowCount = $("#tableDoc").find('tr').length + 1; // Counter for row numbering, starting at 1

            var newRow = `
                    <tr>
                        <td>${rowCount}</td>
                        <td>
                            <select class="form-select mw-100" name="documentname[${rowCount-1}]">
                                <option value="">Select</option>
                                @foreach ($doc_type as $document)
            <option value="{{ $document->name }}">{{ ucwords(str_replace('-', ' ', $document->name)) }}</option>
                                @endforeach
            </select>
        </td>
        <td>
            <input type="file" multiple class="form-control mw-100" name="attachments[${rowCount-1}][]" id="attachments-${rowCount-1}">
                        </td>
                        <td id="preview-${rowCount-1}"></td>
                        <td><a href="#" class="text-danger trash"><i data-feather="trash-2"></i></a></td>
                    </tr>`;

            $("#tableDoc").append(newRow);
            feather.replace();
        });


        $(document).on('change', 'input[type="file"]', function(e) {
            var rowIndex = $(this).attr('id').split('-')[1]; // Extract row index from the file input's id
            handleFileUpload(e, `#preview-${rowIndex}`);
        });

        // Function to handle file upload preview with delete icon
        function handleFileUpload(event, previewElement) {

            var files = event.target.files;
            var previewContainer = $(previewElement); // The container where previews will appear
            previewContainer.empty(); // Clear previous previews

            if (files.length > 0) {
                // Loop through each selected file
                for (var i = 0; i < files.length; i++) {
                    var fileName = files[i].name;
                    var fileExtension = fileName.split('.').pop().toLowerCase(); // Get file extension

                    // Set default icon
                    var fileIconType = 'file-text'; // Default icon for unknown types

                    // Map file extension to specific Feather icons
                    switch (fileExtension) {
                        case 'pdf':
                            fileIconType = 'file'; // Icon for PDF files
                            break;
                        case 'doc':
                        case 'docx':
                            fileIconType = 'file'; // Icon for Word documents
                            break;
                        case 'xls':
                        case 'xlsx':
                            fileIconType = 'file'; // Icon for Excel files
                            break;
                        case 'png':
                        case 'jpg':
                        case 'jpeg':
                        case 'gif':
                            fileIconType = 'image'; // Icon for image files
                            break;
                        case 'zip':
                        case 'rar':
                            fileIconType = 'archive'; // Icon for compressed files
                            break;
                        default:
                            fileIconType = 'file'; // Default icon
                            break;
                    }

                    // Generate the file preview div dynamically
                    var fileIcon = `
                        <div class="image-uplodasection expenseadd-sign" data-file-index="${i}">
                            <i data-feather="${fileIconType}" class="fileuploadicon"></i>
                            <div class="delete-img text-danger" data-file-index="${i}">
                                <i data-feather="x"></i>
                            </div>
                        </div>
                    `;

                    // Append the generated fileIcon div to the preview container
                    previewContainer.append(fileIcon);
                }
                // Replace icons with Feather icons after appending the new elements
                feather.replace();
            }

            // Add event listener to delete the file preview when clicked
            previewContainer.find('.delete-img').click(function() {
                var fileIndex = $(this).parent().data('file-index'); // Get the correct index from parent
                removeFilePreview(fileIndex, previewContainer, event.target);
            });

        }

        // Function to remove a single file from the FileList
        function removeFilePreview(fileIndex, previewContainer, inputElement) {

            var dt = new DataTransfer(); // Create a new DataTransfer object to hold the remaining files
            var files = inputElement.files;

            // Loop through the files and add them to the DataTransfer object, except the one to delete
            for (var i = 0; i < files.length; i++) {
                if (i !== fileIndex) {
                    dt.items.add(files[i]); // Add file to DataTransfer if it's not the one being deleted
                }
            }

            // Update the input element with the new file list
            inputElement.files = dt.files;

            // Remove the preview of the deleted file
            previewContainer.children(`[data-file-index="${fileIndex}"]`).remove();

            // Now re-index the remaining file previews
            var remainingPreviews = previewContainer.children();
            remainingPreviews.each(function(index) {
                $(this).attr('data-file-index', index); // Update data-file-index correctly
                $(this).find('.delete-img').attr('data-file-index', index); // Also update delete button index
            });

            // Debugging logs
            console.log(`Remaining files after deletion: ${dt.files.length}`);
            console.log(`Remaining preview elements: ${remainingPreviews.length}`);

            // If no files are left after deleting, reset the file input
            if (dt.files.length === 0) { // Check the updated DataTransfer's files length
                inputElement.value = ""; // Clear the input value to reset it
            }
        }

        $("#tableDoc").on("click", ".trash", function(event) {
            event.preventDefault(); // Prevent default action for <a> tag
            $(this).closest('tr').remove(); // Remove the closest <tr> element
        });

        function cleanInput(input) {
            // Remove negative numbers and special characters
            input.value = input.value.replace(/[^a-zA-Z0-9 ]/g, '');
        }

        function calculate_contribution() {
            let cost = parseFloat(removeCommas($('#project_cost').val())) || 0;
            let loan = parseFloat(removeCommas($('#term_loan').val())) || 0;

            if (isNaN(cost) || isNaN(loan) || cost < 0 || loan < 0) {
                $('.contri_error').text('Please enter valid non-negative numbers for project cost and term loan.');
                $('#promotor_contribution').val('');
                return;
            }

            if (cost > loan) {
                let contribution = cost - loan;
                $('#promotor_contribution').val(formatIndianNumber(contribution));
                $('.contri_error').empty();
            } else {
                $('#term_loan').val(0);
                $('#promotor_contribution').val(formatIndianNumber(
                    cost)); // Entire cost as contribution if loan exceeds or equals cost
                $('.contri_error').text('Term Loan should be less than project cost!');
            }

        }

        function year() {
            let year = $('#repayment_type').val();
            console.log(year);
            switch (year) {
                case '1':
                    $('.year').html("Year");
                    break;
                case '2':
                    $('.year').html("Half Year");
                    break;
                case '3':
                    $('.year').html("Quarter");
                    break;
                case '4':
                    $('.year').html("Month");
                    break;
            }
        }
        $(document).ready(function() {
            $('#repayment_start_period').on('input', function() {
                let max = parseInt($(this).attr('max'), 10);
                let value = parseInt($(this).val(), 10);

                // Show modal if value exceeds max
                if (value > max) {
                    //$('#repayment_start_period').val(max); // Reset input to max value
                    $('.max_value').html('Value Should be less than ' + max);
                    $('#errorModal').modal('show'); // Show Bootstrap modal
                }
            });
            // calculate_contribution();
            year();
        });

        $('#project_cost, #term_loan, #interest_rate, .disbursal_amount').on('blur', function() {
            // Get the value of the input field
            var amount = $(this).val();
            amount = amount ? formatIndianNumber(removeCommas(amount)) : 0

            // Format the value with commas before saving or processing it
            var formattedAmount = amount;

            // Set the formatted value back into the input field
            $(this).val(formattedAmount);
        });
    </script>
@endsection
