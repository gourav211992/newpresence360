@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endsection

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
                                <h2 class="content-header-title float-start mb-0">View Application</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                                        <li class="breadcrumb-item active">View</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">

                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</button>


                            @if ($buttons['return'])
                                <button data-bs-toggle="modal" data-bs-target="#return"
                                    class="btn btn-warning btn-sm mb-50 mb-sm-0"><i data-feather="refresh-cw"></i>
                                    Return</button>
                            @endif

                            @if ($buttons['reject'])
                                <button class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-target="#reject"
                                    data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</button>
                            @endif

                            @if ($buttons['proceed'])
                                <button data-bs-toggle="modal" data-bs-target="#viewassesgive"
                                    class="btn btn-success btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i>
                                    Proceed</button>
                            @endif

                            @if (isset($page) && $page == 'edit')
                                @if ($buttons['draft'])
                                    <button class="btn btn-outline-primary btn-sm submission_val" data-val="draft"
                                        form="home-loan-createUpdate"><i data-feather="save"></i>
                                        Save as
                                        Draft</button>
                                    <button class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"><i data-feather="trash-2"></i> Delete</button>
                                @endif
                                @if ($buttons['submit'])
                                    <button data-bs-toggle="modal" data-bs-target="#disclaimer" data-val="submitted"
                                        class="btn btn-primary btn-sm submission_val" form="home-loan-createUpdate"><i
                                            data-feather="check-circle"></i> Proceed</button>
                                @endif
                                @if ($buttons['approve'])
                                    <button class="btn btn-danger btn-sm" data-bs-target="#reject" data-bs-toggle="modal"><i
                                            data-feather="x-circle"></i> Reject</button>
                                    <button data-bs-toggle="modal" data-bs-target="#approved"
                                        class="btn btn-success btn-sm"><i data-feather="check-circle"></i> Approve</button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="content-body">

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <section id="basic-datatable">
                    <form id="term-loan-createUpdate" method="POST" action="{{ route('loan.term-loan-createUpdate') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @if (isset($termLoan->id))
                            <input type="hidden" name="edit_loanId" value="{{ $termLoan->id }}">
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body customernewsection-form">

                                        <!-- Page Heading -->
                                        <div class="text-center new-applicayion-help-txt  mb-1 pb-25">
                                            <h4 class="purchase-head"><span>Application for Term Loan</span></h4>
                                            <h6 class="mt-2">({{ $termLoan->name ?? 'N/A' }} |
                                                {{ $overview->term_loan ?? 'N/A' }} |
                                                {{ $termLoan->created_at ? $termLoan->created_at->format('d-m-Y') : 'N/A' }})
                                            </h6>


                                        </div>

                                        <div class="bg-light-success rounded border p-1 mb-4">

                                            <div class="row">

                                                <div class="col-md-7">
                                                    @if ($termLoan->approvalStatus != 'draft')
                                                        <div class="step-custhomapp bg-light mb-0">
                                                            @php
                                                                $statuses = [
                                                                    'appraisal' => 0,
                                                                    'assessment' => 1,
                                                                    'approved' => 2,
                                                                    'sanctioned' => 3,
                                                                    'processingfee' => 4,
                                                                    'legal docs' => 5,
                                                                ];

                                                                // Get the index of the current approval status, or set a high index if it's not found
                                                                $currentStatusIndex =
                                                                    $statuses[$termLoan->approvalStatus] ??
                                                                    count($statuses);
                                                            @endphp

                                                            <ul class="nav nav-tabs mb-0 mt-25 custapploannav customrapplicationstatus"
                                                                role="tablist">
                                                                <li class="nav-item">
                                                                    <p
                                                                        class="{{ $currentStatusIndex >= 0 ? 'statusactive' : '' }}">
                                                                        <i data-feather="check"></i>
                                                                    </p>
                                                                    <a class="nav-link {{ $termLoan->approvalStatus == 'appraisal' ? 'active' : '' }}"
                                                                        data-bs-toggle="tab" href="#Appraisal">
                                                                        Appraisal
                                                                    </a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <p
                                                                        class="{{ $currentStatusIndex >= 1 ? 'statusactive' : '' }}">
                                                                        <i data-feather="check"></i>
                                                                    </p>
                                                                    <a class="nav-link {{ $termLoan->approvalStatus == 'assessment' ? 'active' : '' }}"
                                                                        href="#Assessmentschdule">
                                                                        Assessment
                                                                    </a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <p
                                                                        class="{{ $currentStatusIndex >= 2 ? 'statusactive' : '' }}">
                                                                        <i data-feather="check"></i>
                                                                    </p>
                                                                    <a class="nav-link {{ $termLoan->approvalStatus == 'approved' ? 'active' : '' }}"
                                                                        href="#approval">
                                                                        Approval
                                                                    </a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <p
                                                                        class="{{ $currentStatusIndex >= 3 ? 'statusactive' : '' }}">
                                                                        <i data-feather="check"></i>
                                                                    </p>
                                                                    <a class="nav-link {{ $termLoan->approvalStatus == 'sanctioned' ? 'active' : '' }}"
                                                                        href="#Sansactioned">
                                                                        Sant. Letter
                                                                    </a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <p
                                                                        class="{{ $currentStatusIndex >= 4 ? 'statusactive' : '' }}">
                                                                        <i data-feather="check"></i>
                                                                    </p>
                                                                    <a class="nav-link {{ $termLoan->approvalStatus == 'processingfee' ? 'active' : '' }}"
                                                                        href="#Processing">
                                                                        Proc. Fee
                                                                    </a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <p
                                                                        class="{{ $currentStatusIndex >= 5 ? 'statusactive' : '' }}">
                                                                        <i data-feather="check"></i>
                                                                    </p>
                                                                    <a class="nav-link {{ $termLoan->approvalStatus == 'legal docs' ? 'active' : '' }}"
                                                                        href="#Legal">
                                                                        Legal Doc
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    @endif

                                                    <div class="tab-content  bg-white">

                                                        <div class="tab-pane active" id="Appraisal">
                                                            <div class="">
                                                                <ul class="nav nav-tabs border-bottom mt-25 loandetailhistory"
                                                                    role="tablist">
                                                                    <li class="nav-item">
                                                                        <a class="nav-link active" data-bs-toggle="tab"
                                                                            href="#Overview">
                                                                            Overview
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item">
                                                                        <a class="nav-link" data-bs-toggle="tab"
                                                                            href="#Project">
                                                                            Project Report
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item">
                                                                        <a class="nav-link" data-bs-toggle="tab"
                                                                            href="#Disbursement">

                                                                            Disbursal Schedule
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item">
                                                                        <a class="nav-link" data-bs-toggle="tab"
                                                                            href="#Recovery">
                                                                            Recovery Schedule
                                                                        </a>
                                                                    </li>
                                                                </ul>

                                                                <div class="tab-content">

                                                                    <div class="tab-pane active" id="Overview">
                                                                        <div class="row mt-2">
                                                                            <div class="col-md-12">
                                                                                <div class="table-responsive">
                                                                                    <table
                                                                                        class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th>#</th>
                                                                                                <th>Particulars</th>
                                                                                                <th>Remarks</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            <tr>
                                                                                                <td>1</td>
                                                                                                <td>Date</td>
                                                                                                <td>{{ explode(' ', $overview->created_at)[0] }}
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>2</td>
                                                                                                <td>Name of Unit</td>
                                                                                                <td>{{ $overview->unit_name }}
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>3</td>
                                                                                                <td>Name of Proprietor</td>
                                                                                                <td>{{ $overview->proprietor_name }}
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>4</td>
                                                                                                <td>Address</td>
                                                                                                <td>{{ $overview->address }}
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>5</td>
                                                                                                <td>CIBIL Score</td>
                                                                                                <td>{{ $overview->cibil_score }}
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>6</td>
                                                                                                <td>Project Cost</td>
                                                                                                <td>{{ $overview->project_cost }}
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>7</td>
                                                                                                <td>Term Loan</td>
                                                                                                <td>RS.
                                                                                                    {{ $overview->term_loan }}
                                                                                                    LAKHS</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>8</td>
                                                                                                <td>Promotor's Contribution
                                                                                                </td>
                                                                                                <td>RS.
                                                                                                    {{ $overview->promotor_contribution }}
                                                                                                    LAKHS</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>9</td>
                                                                                                <td>Interest Rate (P.A)</td>
                                                                                                <td>{{ $overview->interest_rate }}
                                                                                                    %</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>10</td>
                                                                                                <td>Loan Period</td>
                                                                                                <td>{{ $overview->loan_period }}
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>11</td>
                                                                                                <td>Repayment Type</td>
                                                                                                <td>{{ $overview->repayment_type }}
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>12</td>
                                                                                                <td>No. of Installment(s)
                                                                                                </td>
                                                                                                <td>{{ $overview->no_of_installments }}
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>13</td>
                                                                                                <td>Repayment Start After
                                                                                                </td>
                                                                                                <td>{{ $overview->repayment_start_after }}
                                                                                                </td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>14</td>
                                                                                                <td>Repayment Start Period
                                                                                                </td>
                                                                                                <td>{{ $overview->repayment_start_period }}
                                                                                                    Year</td>
                                                                                            </tr>

                                                                                        </tbody>


                                                                                    </table>
                                                                                </div>
                                                                            </div>

                                                                        </div>

                                                                    </div>

                                                                    <div class="tab-pane" id="Project">
                                                                        <div class="row mt-2">
                                                                            <div class="col-md-12">
                                                                                <div class="table-responsive">
                                                                                    <table
                                                                                        class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th>#</th>
                                                                                                <th>Particulars</th>
                                                                                                <th>Remarks</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            @foreach ($overview->dpr as $key => $dpr_list)
                                                                                                <tr>
                                                                                                    <td>{{ $key + 1 }}
                                                                                                    </td>
                                                                                                    <td>{{ $dpr_list->dpr->field_name }}
                                                                                                    </td>
                                                                                                    <td>{{ $dpr_list->dpr_value }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                            @endforeach
                                                                                        </tbody>


                                                                                    </table>
                                                                                </div>
                                                                            </div>

                                                                        </div>

                                                                    </div>


                                                                    <div class="tab-pane" id="Disbursement">

                                                                        <div class="row mt-2">
                                                                            <div class="col-md-12">
                                                                                <div class="table-responsive">
                                                                                    <table
                                                                                        class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th>#</th>
                                                                                                <th>Loan Amt.</th>
                                                                                                <th>Disbursal Mil.</th>
                                                                                                <th>Disbursal Amt.</th>
                                                                                                <th>Remarks</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            @foreach ($overview->disbursal as $key => $disbursal_list)
                                                                                                <tr>
                                                                                                    <td>{{ $key + 1 }}
                                                                                                    </td>
                                                                                                    <td>{{ $overview->loan->loan_amount }}
                                                                                                    </td>
                                                                                                    <td>{{ $disbursal_list->milestone }}
                                                                                                    </td>
                                                                                                    <td>{{ $disbursal_list->amount }}
                                                                                                    </td>
                                                                                                    <td>{{ $disbursal_list->remarks }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                            @endforeach

                                                                                        </tbody>

                                                                                    </table>
                                                                                </div>
                                                                            </div>

                                                                        </div>

                                                                    </div>

                                                                    <div class="tab-pane" id="Recovery">

                                                                        <div class="row mt-2">
                                                                            <div class="col-md-12">
                                                                                <div class="table-responsive">
                                                                                    <table
                                                                                        class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th>#</th>
                                                                                                <th>Year</th>
                                                                                                <th>Amt. at Start</th>
                                                                                                <th>Interest Amt.</th>
                                                                                                <th>Repayemnt Amt.</th>
                                                                                                <th>Amount at End</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            @php
                                                                                                $total_rep_amount = 0;
                                                                                                $total_int_amount = 0;
                                                                                            @endphp
                                                                                            @foreach ($overview->recovery as $key => $recovery_list)
                                                                                                @php
                                                                                                    $total_rep_amount +=
                                                                                                        $recovery_list->repayment_amount;
                                                                                                    $total_int_amount +=
                                                                                                        $recovery_list->interest_amount;
                                                                                                @endphp

                                                                                                <tr>
                                                                                                    <td>{{ $key + 1 }}
                                                                                                    </td>
                                                                                                    <td>{{ $recovery_list->year }}
                                                                                                    </td>
                                                                                                    <td>{{ $recovery_list->start_amount }}
                                                                                                    </td>
                                                                                                    <td>{{ $recovery_list->interest_amount }}
                                                                                                    </td>
                                                                                                    <td>{{ $recovery_list->repayment_amount }}
                                                                                                    </td>
                                                                                                    <td>{{ $recovery_list->end_amount }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                            @endforeach

                                                                                            <tr>
                                                                                                <td>&nbsp;</td>
                                                                                                <td>&nbsp;</td>
                                                                                                <td
                                                                                                    class="fw-bolder text-dark">
                                                                                                    Total</td>
                                                                                                <td
                                                                                                    class="fw-bolder text-dark">
                                                                                                    {{ $total_int_amount }}
                                                                                                </td>
                                                                                                <td
                                                                                                    class="fw-bolder text-dark">
                                                                                                    {{ $total_rep_amount }}
                                                                                                </td>
                                                                                                <td>&nbsp;</td>
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


                                                        <div class="tab-pane" id="Assessmentschdule">

                                                            <div class="row mt-2">
                                                                <div class="col-md-12">
                                                                    <div class="table-responsive">
                                                                        <table
                                                                            class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>#</th>
                                                                                    <th>Particulars</th>
                                                                                    <th>Remarks</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td>1</td>
                                                                                    <td>Date</td>
                                                                                    <td>14-10-2024</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>2</td>
                                                                                    <td>Assessed By</td>
                                                                                    <td>Deewan Singh</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>3</td>
                                                                                    <td>Remarks</td>
                                                                                    <td>Description will come here</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>4</td>
                                                                                    <td>Download Doc</td>
                                                                                    <td><a href="#"><i
                                                                                                data-feather='download'></i></a>
                                                                                    </td>
                                                                                </tr>

                                                                            </tbody>


                                                                        </table>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                        </div>

                                                        <div class="tab-pane" id="approval">

                                                            <div class="row mt-2">
                                                                <div class="col-md-12">
                                                                    <div class="table-responsive">
                                                                        <table
                                                                            class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>#</th>
                                                                                    <th>Date</th>
                                                                                    <th>Name</th>
                                                                                    <th>Status</th>
                                                                                    <th>Remarks</th>
                                                                                    <th>Docs</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td>1</td>
                                                                                    <td>14-10-2024</td>
                                                                                    <td>Deepak Singh</td>
                                                                                    <td>Approve</td>
                                                                                    <td>Description will come here</td>
                                                                                    <td><a href="#"><i
                                                                                                data-feather='download'></i></a>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>2</td>
                                                                                    <td>10-10-2024</td>
                                                                                    <td>Aniket Singh</td>
                                                                                    <td>Approve</td>
                                                                                    <td>Description will come here</td>
                                                                                    <td><a href="#"><i
                                                                                                data-feather='download'></i></a>
                                                                                    </td>
                                                                                </tr>

                                                                            </tbody>


                                                                        </table>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                        </div>


                                                        <div class="tab-pane" id="Sansactioned">

                                                            <div class="row mt-2">
                                                                <div class="col-md-12">
                                                                    <div class="table-responsive">
                                                                        <table
                                                                            class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>#</th>
                                                                                    <th>Particulars</th>
                                                                                    <th>Remarks</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td>1</td>
                                                                                    <td>Date</td>
                                                                                    <td>14-10-2024</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>1</td>
                                                                                    <td>Updated by</td>
                                                                                    <td>Deepak Kumar</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>2</td>
                                                                                    <td>Status</td>
                                                                                    <td>Accepted</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>3</td>
                                                                                    <td>Remarks</td>
                                                                                    <td>Description will come here</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>4</td>
                                                                                    <td>Download Letter</td>
                                                                                    <td><a href="#"><i
                                                                                                data-feather='download'></i></a>
                                                                                    </td>
                                                                                </tr>

                                                                            </tbody>


                                                                        </table>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                        </div>

                                                        <div class="tab-pane" id="Processing">

                                                            <div class="row mt-2">
                                                                <div class="col-md-12">
                                                                    <div class="table-responsive">
                                                                        <table
                                                                            class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>#</th>
                                                                                    <th>Particulars</th>
                                                                                    <th>Remarks</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td>1</td>
                                                                                    <td>Date</td>
                                                                                    <td>14-10-2024</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>2</td>
                                                                                    <td>Updated by</td>
                                                                                    <td>Deepak Kumar</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>3</td>
                                                                                    <td>Fee Paid</td>
                                                                                    <td>Yes</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>4</td>
                                                                                    <td>Processing Fee</td>
                                                                                    <td>30,000.00</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>5</td>
                                                                                    <td>Remarks</td>
                                                                                    <td>Description will come here</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>6</td>
                                                                                    <td>Download Doc</td>
                                                                                    <td><a href="#"><i
                                                                                                data-feather='download'></i></a>
                                                                                    </td>
                                                                                </tr>

                                                                            </tbody>


                                                                        </table>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                        </div>

                                                        <div class="tab-pane" id="Legal">

                                                            <div class="row mt-2">
                                                                <div class="col-md-12">
                                                                    <div class="table-responsive">
                                                                        <table
                                                                            class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>#</th>
                                                                                    <th>Particulars</th>
                                                                                    <th>Remarks</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td>1</td>
                                                                                    <td>Date</td>
                                                                                    <td>14-10-2024</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>2</td>
                                                                                    <td>Updated by</td>
                                                                                    <td>Deepak Kumar</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>3</td>
                                                                                    <td>Legal Letter</td>
                                                                                    <td>Accepted</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>4</td>
                                                                                    <td>Remarks</td>
                                                                                    <td>Description will come here</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td>5</td>
                                                                                    <td>Agreement</td>
                                                                                    <td><a href="#"><i
                                                                                                data-feather='download'></i></a>
                                                                                    </td>
                                                                                </tr>

                                                                                <tr>
                                                                                    <td>6</td>
                                                                                    <td>Court Order</td>
                                                                                    <td><a href="#"><i
                                                                                                data-feather='download'></i></a>
                                                                                    </td>
                                                                                </tr>

                                                                            </tbody>


                                                                        </table>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                        </div>


                                                    </div>




                                                </div>
                                                <div class="col-md-5 bg-white rounded border">
                                                    <ul class="nav nav-tabs border-bottom mt-25 loandetailhistory mb-0"
                                                        role="tablist">
                                                        <li class="nav-item">
                                                            <a class="nav-link active" data-bs-toggle="tab"
                                                                href="#paymentsc">
                                                                Payment Release
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Collections">
                                                                Recovery
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#logs">
                                                                Logs
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#documents">
                                                                Documents
                                                            </a>
                                                        </li>
                                                    </ul>

                                                    <div class="tab-content mt-1">
                                                        <div class="tab-pane active" id="paymentsc">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="table-responsive">
                                                                        <table
                                                                            class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>#</th>
                                                                                    <th>Disbursal Mil.</th>
                                                                                    <th>Disbursal Amt.</th>
                                                                                    <th>Cust. Contribution</th>
                                                                                    <th>Disbursal Date</th>
                                                                                    <th>Act. Disbursal Amt.</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>

                                                                                @if (isset($loan_disbursement))
                                                                                    @foreach ($loan_disbursement as $key => $loan_disbursement_list)
                                                                                        <tr>
                                                                                            <td>{{ $key + 1 }}
                                                                                            </td>
                                                                                            <td>
                                                                                                @foreach (json_decode($loan_disbursement_list->dis_milestone, true) as $loan_disbursement_dis_milestone)
                                                                                                    {{ $loan_disbursement_dis_milestone['name'] }},
                                                                                                @endforeach
                                                                                            </td>
                                                                                            <td>{{ $loan_disbursement_list->dis_amount }}
                                                                                            </td>
                                                                                            <td>{{ $loan_disbursement_list->customer_contri }}
                                                                                            </td>
                                                                                            <td>{{ explode(' ', $loan_disbursement_list->created_at)[0] }}
                                                                                            </td>
                                                                                            <td>{{ $loan_disbursement_list->actual_dis }}
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                @endif

                                                                            </tbody>


                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="tab-pane" id="Collections">

                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="table-responsive">
                                                                        <table
                                                                            class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>#</th>
                                                                                    <th>Collected on</th>
                                                                                    <th>Loan Amount</th>
                                                                                    <th>Repay. Amt.</th>
                                                                                    <th>Rec. Pri. Amt.</th>
                                                                                    <th>Rec. Int. Amt.</th>
                                                                                    <th>Bal. Loan Amt.</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @if (isset($recovery_loan))
                                                                                    @foreach ($recovery_loan as $key => $recovery_loan_list)
                                                                                        <tr>
                                                                                            <td>{{ $key + 1 }}
                                                                                            </td>
                                                                                            <td>{{ explode(' ', $recovery_loan_list->created_at)[0] }}
                                                                                            </td>
                                                                                            <td>{{ $recovery_loan_list->homeloan->loan_amount }}
                                                                                            </td>
                                                                                            <td>{{ $recovery_loan_list->recovery_amnnt }}
                                                                                            </td>
                                                                                            <td>{{ $recovery_loan_list->rec_principal_amnt }}
                                                                                            </td>
                                                                                            <td>{{ $recovery_loan_list->rec_interest_amnt }}
                                                                                            </td>
                                                                                            <td>{{ $recovery_loan_list->balance_amount }}
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                @endif

                                                                            </tbody>


                                                                        </table>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                        </div>

                                                        <div class="tab-pane" id="logs">

                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    <div class="table-responsive">
                                                                        <table
                                                                            class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>#</th>
                                                                                    <th>Date</th>
                                                                                    <th>Particular</th>
                                                                                    <th>Remarks</th>
                                                                                    <th>Updated By</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td>1</td>
                                                                                    <td class="text-nowrap">
                                                                                        10-10-2024
                                                                                    </td>
                                                                                    <td>Appraisal</td>
                                                                                    <td>Description will come here
                                                                                    </td>
                                                                                    <td>Aniket Singh</td>
                                                                                </tr>

                                                                                <tr>
                                                                                    <td>2</td>
                                                                                    <td class="text-nowrap">
                                                                                        10-10-2024
                                                                                    </td>
                                                                                    <td>Assessment</td>
                                                                                    <td>Description will come here
                                                                                    </td>
                                                                                    <td>Deewan Singh</td>
                                                                                </tr>

                                                                                <tr>
                                                                                    <td>3</td>
                                                                                    <td class="text-nowrap">
                                                                                        10-10-2024
                                                                                    </td>
                                                                                    <td>Approved</td>
                                                                                    <td>Description will come here
                                                                                    </td>
                                                                                    <td>Deewan Singh</td>
                                                                                </tr>

                                                                                <tr>
                                                                                    <td>4</td>
                                                                                    <td class="text-nowrap">
                                                                                        10-10-2024
                                                                                    </td>
                                                                                    <td>Sansactioned Letter</td>
                                                                                    <td><a href="#"><i
                                                                                                data-feather="download"
                                                                                                class="me-50"></i></a>
                                                                                        Description will come here
                                                                                    </td>
                                                                                    <td>Deewan Singh</td>
                                                                                </tr>


                                                                                <tr>
                                                                                    <td>5</td>
                                                                                    <td>10-10-2024</td>
                                                                                    <td>Processing Fee</td>
                                                                                    <td>Description will come here
                                                                                    </td>
                                                                                    <td>Deewan Singh</td>
                                                                                </tr>

                                                                                <tr>
                                                                                    <td>6</td>
                                                                                    <td>10-10-2024</td>
                                                                                    <td><a href="#"><i
                                                                                                data-feather="download"
                                                                                                class="me-50"></i></a>
                                                                                        Legal Doc
                                                                                    </td>
                                                                                    <td>Description will come here
                                                                                    </td>
                                                                                    <td>Deewan Singh</td>
                                                                                </tr>






                                                                            </tbody>


                                                                        </table>
                                                                    </div>
                                                                </div>

                                                            </div>

                                                        </div>

                                                        <div class="tab-pane" id="documents">

                                                            <div class="row mt-2">
                                                                <div class="col-md-12">
                                                                    <div
                                                                        class="input-group input-group-merge docreplchatsearch border-bottom mb-25">
                                                                        <span class="input-group-text border-0 ps-0">
                                                                            <i data-feather="search"></i>
                                                                        </span>
                                                                        <input type="text"
                                                                            class="form-control border-0"
                                                                            id="email-search" placeholder="Search Doc"
                                                                            aria-label="Search...">
                                                                    </div>
                                                                    <div class="table-responsive">
                                                                        <table
                                                                            class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>#</th>
                                                                                    <th>Document Name</th>
                                                                                    <th>Download</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody class="loan-documents">
                                                                                {!! $document_listing !!}
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

                                        <!-- Term Loan Data -->
                                        <div class="row">
                                            <div class="col-md-9">

                                                @php
                                                    $fullName = old('name', $termLoan->name ?? '');
                                                    $nameParts = explode(' ', $fullName);
                                                    $f_name = '';
                                                    $m_name = '';
                                                    $l_name = '';
                                                    if (count($nameParts) == 3) {
                                                        $f_name = $nameParts[0];
                                                        $m_name = $nameParts[1];
                                                        $l_name = $nameParts[2];
                                                    } elseif (count($nameParts) == 2) {
                                                        $f_name = $nameParts[0];
                                                        $l_name = $nameParts[1];
                                                    } elseif (count($nameParts) == 1) {
                                                        $f_name = $nameParts[0];
                                                    }
                                                @endphp
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Name of the Concern <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-3 pe-sm-0 mb-1 mb-sm-0">
                                                        <input type="text" name="f_name"
                                                            value="{{ old('f_name', $f_name) }}" class="form-control"
                                                            placeholder="First Name" />
                                                        @error('f_name')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-3 pe-sm-0 mb-1 mb-sm-0">
                                                        <input type="text" name="m_name"
                                                            value="{{ old('m_name', $m_name) }}" class="form-control"
                                                            placeholder="Middle Name" />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" name="l_name"
                                                            value="{{ old('l_name', $l_name) }}" class="form-control"
                                                            placeholder="Last Name" />
                                                        @error('l_name')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                @php
                                                    $fullNamePro = old('name', $termLoan->promoter_name ?? '');
                                                    $namePartsPro = explode(' ', $fullNamePro);
                                                    $f_name_pro = '';
                                                    $m_name_pro = '';
                                                    $l_name_pro = '';
                                                    if (count($namePartsPro) == 3) {
                                                        $f_name_pro = $namePartsPro[0];
                                                        $m_name_pro = $namePartsPro[1];
                                                        $l_name_pro = $namePartsPro[2];
                                                    } elseif (count($namePartsPro) == 2) {
                                                        $f_name_pro = $namePartsPro[0];
                                                        $l_name_pro = $namePartsPro[1];
                                                    } elseif (count($namePartsPro) == 1) {
                                                        $f_name_pro = $namePartsPro[0];
                                                    }
                                                @endphp
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Name of the Promoter(s)<span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-3 pe-sm-0 mb-1 mb-sm-0">
                                                        <input type="text" name="f_name_pro"
                                                            value="{{ old('f_name_pro', $f_name_pro) }}"
                                                            class="form-control" placeholder="First Name" />
                                                        @error('f_name_pro')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-3 pe-sm-0 mb-1 mb-sm-0">
                                                        <input type="text" name="m_name_pro"
                                                            value="{{ old('m_name_pro', $m_name_pro) }}"
                                                            class="form-control" placeholder="Middle Name" />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" name="l_name_pro"
                                                            value="{{ old('l_name_pro', $l_name_pro) }}"
                                                            class="form-control" placeholder="Last Name" />
                                                        @error('l_name_pro')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Email <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class="input-group input-group-merge">
                                                            <span class="input-group-text" id="basic-addon5"><i
                                                                    data-feather='mail'></i></span>
                                                            <input type="email" name="tr_email" id="email_no"
                                                                value="{{ old('tr_email', $termLoan->email ?? '') }}"
                                                                class="form-control" placeholder="">
                                                            @error('tr_email')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Loan Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="number" name="loan_amount"
                                                            value="{{ old('loan_amount', $termLoan->loan_amount ?? '') }}"
                                                            class="form-control" />
                                                        @error('loan_amount')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Scheme for <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" name="scheme_for"
                                                            value="{{ old('scheme_for', $termLoan->scheme_for ?? '') }}"
                                                            class="form-control" />
                                                        @error('scheme_for')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>



                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Line of Activity</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" name="activity_line"
                                                            value="{{ old('activity_line', $termLoan->activity_line ?? '') }}"
                                                            class="form-control" />
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="col-md-3 mb-2">


                                            </div>
                                        </div>





                                        <div class="mt-3">
                                            <div class="step-custhomapp bg-light">
                                                <ul class="nav nav-tabs my-25 custapploannav" role="tablist">

                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-bs-toggle="tab"
                                                            href="#Address">Address</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab"
                                                            href="#Promoters">Promoters</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab"
                                                            href="#Constitution">Constitution</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#Finance">Means of
                                                            Finance</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#worth">Bio-Data
                                                            and Net worth</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab"
                                                            href="#Documentsupload">Documents</a>
                                                    </li>

                                                </ul>

                                            </div>

                                            <div class="tab-content pb-1 px-1">
                                                <div class="tab-pane active" id="Address">
                                                    <div class="row">
                                                        <div class="col-md-6">

                                                            <h5 class="mt-1 mb-2 text-dark"><strong>Registered
                                                                    Office</strong></h5>


                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">C/o.</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text" name="Address[co_term]"
                                                                        value="{{ old('Address.co_term', $termLoan->termLoanAddress->co_term ?? '') }}"
                                                                        class="form-control" />

                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Street/Road</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text" name="Address[street_road]"
                                                                        value="{{ old('Address.street_road', $termLoan->termLoanAddress->street_road ?? '') }}"
                                                                        class="form-control" />

                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">House No./Land Mark</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text" name="Address[house_land_mark]"
                                                                        value="{{ old('Address.house_land_mark', $termLoan->termLoanAddress->house_land_mark ?? '') }}"
                                                                        class="form-control" />

                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">City/Town/Village</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="Address[city_town_village]"
                                                                        value="{{ old('Address.city_town_village', $termLoan->termLoanAddress->city_town_village ?? '') }}"
                                                                        class="form-control" />

                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Pin Code</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" name="Address[pin_code]"
                                                                        value="{{ old('Address.pin_code', $termLoan->termLoanAddress->pin_code ?? '') }}"
                                                                        class="form-control" />

                                                                    @error('Address.pin_code')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Telephone No.</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="Address[registered_offc_tele]"
                                                                        value="{{ old('Address.registered_offc_tele', $termLoan->termLoanAddress->registered_offc_tele ?? '') }}"
                                                                        class="form-control" />
                                                                    @error('Address.registered_offc_tele')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Mobile Phone</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="Address[registered_offc_mobile]"
                                                                        value="{{ old('Address.registered_offc_mobile', $termLoan->termLoanAddress->registered_offc_mobile ?? '') }}"
                                                                        class="form-control" />
                                                                    @error('Address.registered_offc_mobile')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror

                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Email ID</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="Address[registered_offc_email_id]"
                                                                        value="{{ old('Address.registered_offc_email_id', $termLoan->termLoanAddress->registered_offc_email_id ?? '') }}"
                                                                        class="form-control" />
                                                                    @error('Address.registered_offc_email_id')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror

                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Fax Number</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="Address[registered_offc_fax_num]"
                                                                        value="{{ old('Address.registered_offc_fax_num', $termLoan->termLoanAddress->registered_offc_fax_num ?? '') }}"
                                                                        class="form-control" />
                                                                    @error('Address.registered_offc_fax_num')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror

                                                                </div>
                                                            </div>


                                                        </div>

                                                        <div class="col-md-6">

                                                            <div class="mt-1 mb-2 d-flex flex-column">
                                                                <h5 class="text-dark mb-0 me-1">
                                                                    <strong>Factory/Location</strong>
                                                                </h5>
                                                            </div>


                                                            <div class="row mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Address</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea type="text" name="Address[addr1]" class="form-control">{{ old('Address.addr1', $termLoan->termLoanAddress->addr1 ?? '') }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    &nbsp;
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea type="text" name="Address[addr2]" class="form-control">{{ old('Address.addr2', $termLoan->termLoanAddress->addr2 ?? '') }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Telephone No.</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" name="Address[factory_tele]"
                                                                        value="{{ old('Address.factory_tele', $termLoan->termLoanAddress->factory_tele ?? '') }}"
                                                                        class="form-control" />
                                                                    @error('Address.factory_tele')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Mobile Phone</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" name="Address[factory_mobile]"
                                                                        value="{{ old('Address.factory_mobile', $termLoan->termLoanAddress->factory_mobile ?? '') }}"
                                                                        class="form-control" />
                                                                    @error('Address.factory_mobile')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Email ID</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text" name="Address[factory_email_id]"
                                                                        value="{{ old('Address.factory_email_id', $termLoan->termLoanAddress->factory_email_id ?? '') }}"
                                                                        class="form-control" />
                                                                    @error('Address.factory_email_id')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Fax Number</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" name="Address[factory_fax_num]"
                                                                        value="{{ old('Address.factory_fax_num', $termLoan->termLoanAddress->factory_fax_num ?? '') }}"
                                                                        class="form-control" />
                                                                    @error('Address.factory_fax_num')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="Promoters">

                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>Photo self
                                                            attested, including the name of the promoters</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Promoters Name</th>
                                                                    <th>Domicile</th>
                                                                    <th>Upload Photo</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-term-loan">
                                                                <tr>
                                                                    <td id="row-number-term-loan">1</td>
                                                                    <td><input type="text"
                                                                            name="TermPromotor[promoter_name][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="TermPromotor[domicile][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="file"
                                                                            name="TermPromotor[domicile_photo][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-term-loan"
                                                                            id="add-row-term-loan"
                                                                            data-class="add-row-term-loan"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>

                                                                @if (isset($termLoan) && $termLoan->termLoanPromoter && $termLoan->termLoanPromoter->count() > 0)
                                                                    @foreach ($termLoan->termLoanPromoter as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="TermPromotor[promoter_name][]"
                                                                                    value="{{ old('TermPromotor.promoter_name' . $key, $val->promoter_name ?? '') }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="TermPromotor[domicile][]"
                                                                                    value="{{ old('TermPromotor.domicile' . $key, $val->domicile ?? '') }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td>
                                                                                @if ($key == 0)
                                                                                    <input type="hidden"
                                                                                        name="TermPromotor[stored_domicile_photo][]"
                                                                                        value="">
                                                                                @endif
                                                                                <input type="hidden"
                                                                                    name="TermPromotor[stored_domicile_photo][]"
                                                                                    value="{{ $val->domicile_photo ?? '' }}">
                                                                                <div class="d-flex align-items-center"
                                                                                    style="gap:5px;">
                                                                                    <input type="file"
                                                                                        name="TermPromotor[domicile_photo][]"
                                                                                        class="form-control mw-100">
                                                                                    <span>
                                                                                        @if (isset($val->domicile_photo))
                                                                                            <a href="{{ asset('storage/' . $val->domicile_photo) }}"
                                                                                                target="_blank"
                                                                                                download>{{ $val->promoter_name ?? 'Promoter' }}
                                                                                                Photo</a>
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </td>

                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($termLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($termLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif


                                                            </tbody>


                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="Constitution">

                                                    <div class="row">
                                                        <div class="col-md-12">

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Business Type</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <select class="form-select"
                                                                        name="Constitution[common_data][cons_business_type]"
                                                                        id="cons_business_type">
                                                                        <option value="">Select</option>
                                                                        <option value="huf"
                                                                            {{ old('Constitution.common_data.cons_business_type', isset($termLoan->constitutions->business_type) ? $termLoan->constitutions->business_type : '') == 'huf' ? 'selected' : '' }}>
                                                                            Proprietary/Partnership (HUF)</option>
                                                                        <option value="plc"
                                                                            {{ old('Constitution.common_data.cons_business_type', isset($termLoan->constitutions->business_type) ? $termLoan->constitutions->business_type : '') == 'plc' ? 'selected' : '' }}>
                                                                            Private Limited Company</option>
                                                                        <option value="plcc"
                                                                            {{ old('Constitution.common_data.cons_business_type', isset($termLoan->constitutions->business_type) ? $termLoan->constitutions->business_type : '') == 'plcc' ? 'selected' : '' }}>
                                                                            Public Limited Company</option>
                                                                        <option value="cos"
                                                                            {{ old('Constitution.common_data.cons_business_type', isset($termLoan->constitutions->business_type) ? $termLoan->constitutions->business_type : '') == 'cos' ? 'selected' : '' }}>
                                                                            Co-operative Society</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Date of PRC/PMT registration
                                                                        /commencement of Business, Reg No., if any</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="date"
                                                                        name="Constitution[common_data][prc]"
                                                                        value="{{ old('Constitution.common_data.prc', $termLoan->constitutions->prc ?? '') }}"
                                                                        class="form-control past-date" />
                                                                </div>
                                                            </div>



                                                        </div>
                                                    </div>

                                                    <p class="mt-1 text-dark customapplsmallhead"><strong>Name of
                                                            associate/Subsidiary concerns in which the Promoters are
                                                            interested as Proprietor/Partner/Directors. (Three years audited
                                                            financial statement to be enclosed and also names and addresses
                                                            of Banks/Financial Institutions with whom enquiries may be made
                                                            regarding the associate concern and the promoters.)</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Name of the Associate/Sister Concern(s)</th>
                                                                    <th>Name of the Banker/FI with</th>
                                                                    <th>Nature of facility address</th>
                                                                    <th>Outstanding</th>
                                                                    <th>Defaults, if any</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-promoters">
                                                                <tr>
                                                                    <td id="row-number-promoters">1</td>
                                                                    <td><input type="text"
                                                                            name="Constitution[sister_concern][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="Constitution[banker_name][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="Constitution[nature_facility_address][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="Constitution[outstanding][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="Constitution[any_default][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-promoters"
                                                                            id="add-row-promoters"
                                                                            data-class="add-row-promoters"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>
                                                                @if (isset($termLoan) && $termLoan->constitutions && $termLoan->constitutions->Promoters->count() > 0)
                                                                    @foreach ($termLoan->constitutions->Promoters as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="Constitution[sister_concern][]"
                                                                                    value="{{ old('Constitution.sister_concern' . $key, $val->sister_concern ?? '') }}"
                                                                                    class="form-control mw-100">
                                                                            </td>
                                                                            <td><input type="date"
                                                                                    name="Constitution[banker_name][]"
                                                                                    value="{{ old('Constitution.banker_name' . $key, $val->banker_name ?? '') }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="Constitution[nature_facility_address][]"
                                                                                    value="{{ old('Constitution.nature_facility_address' . $key, $val->nature_facility_address ?? '') }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="Constitution[outstanding][]"
                                                                                    value="{{ old('Constitution.outstanding' . $key, $val->outstanding ?? '') }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="Constitution[any_default][]"
                                                                                    value="{{ old('Constitution.any_default' . $key, $val->any_default ?? '') }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($termLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($termLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif

                                                            </tbody>


                                                        </table>
                                                    </div>

                                                    <div class="row mt-1">
                                                        <div class="col-md-12">

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Is the loan sought for
                                                                        establishing a new unit or for
                                                                        Exansion/Modenisation/Diversification/Escalation
                                                                        etc. of the existing loan</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="date"
                                                                        name="Constitution[common_data][esclation]"
                                                                        value="{{ old('Constitution.common_data.esclation', $termLoan->constitutions->esclation ?? '') }}"
                                                                        class="form-control past-date" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">SSI/SIA No.</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="text"
                                                                        name="Constitution[common_data][sia_no]"
                                                                        value="{{ old('Constitution.common_data.sia_no', $termLoan->constitutions->sia_no ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">SSI/SIA Registration
                                                                        Date</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="date"
                                                                        name="Constitution[common_data][sia_date]"
                                                                        value="{{ old('Constitution.common_data.sia_date', $termLoan->constitutions->sia_date ?? '') }}"
                                                                        class="form-control past-date" />
                                                                </div>
                                                            </div>



                                                        </div>
                                                    </div>

                                                    <p class="mt-2 text-dark customapplsmallhead"><strong>Details of
                                                            Proprietor/Partner/Directors</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Name</th>
                                                                    <th>Age</th>
                                                                    <th>Position in the company</th>
                                                                    <th>Shareholding</th>
                                                                    <th>%age</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-partner">
                                                                <tr>
                                                                    <td id="row-number-partner">1</td>
                                                                    <td><input type="text"
                                                                            name="Constitution[par_name][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="Constitution[par_age][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="Constitution[par_position][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="Constitution[par_shareholding][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="Constitution[par_percentage][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-partner"
                                                                            id="add-row-partner"
                                                                            data-class="add-row-partner"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>

                                                                @if (isset($termLoan) &&
                                                                        $termLoan->constitutions &&
                                                                        $termLoan->constitutions->Partner &&
                                                                        $termLoan->constitutions->Partner->count() > 0)
                                                                    @foreach ($termLoan->constitutions->Partner as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="Constitution[par_name][]"
                                                                                    value="{{ old('Constitution.par_name' . $key, $val->name ?? '') }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="date"
                                                                                    name="Constitution[par_age][]"
                                                                                    value="{{ old('Constitution.par_age' . $key, $val->age ?? '') }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="Constitution[par_position][]"
                                                                                    value="{{ old('Constitution.par_position' . $key, $val->position ?? '') }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="Constitution[par_shareholding][]"
                                                                                    value="{{ old('Constitution.par_shareholding' . $key, $val->shareholding ?? '') }}"
                                                                                    class="form-control mw-100">
                                                                            </td>
                                                                            <td><input type="text"
                                                                                    name="Constitution[par_percentage][]"
                                                                                    value="{{ old('Constitution.par_percentage' . $key, $val->percentage ?? '') }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($termLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($termLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif


                                                            </tbody>


                                                        </table>
                                                    </div>

                                                    <p class="mt-1 text-dark customapplsmallhead">(If there is more than
                                                        one Promotor/Director, separete sheet may be attached)</p>
                                                    <p class="text-dark customapplsmallhead"><strong>Note: Give Bio-Data,
                                                            net worth of the promoters as per Performa-A</strong> </p>


                                                    <div class="row mt-3">
                                                        <div class="col-md-12">

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Name of the Managing
                                                                        Partner/Managing Direcctor/Chief Executive</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="text"
                                                                        name="Constitution[common_data][director_name]"
                                                                        value="{{ old('Constitution.common_data.director_name', $termLoan->constitutions->director_name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Indicate the Name of the Bank
                                                                        from whom you propose to avail working capital
                                                                        facilities</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="text"
                                                                        name="Constitution[common_data][working_capital]"
                                                                        value="{{ old('Constitution.common_data.working_capital', $termLoan->constitutions->working_capital ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Address of the Bank from whom
                                                                        you propose to avail working capital
                                                                        facilities</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="date"
                                                                        name="Constitution[common_data][capital_facilities]"
                                                                        value="{{ old('Constitution.common_data.capital_facilities', $termLoan->constitutions->capital_facilities ?? '') }}"
                                                                        class="form-control past-date" />
                                                                </div>
                                                            </div>



                                                        </div>


                                                        <div class="col-md-12">

                                                            <h5 class="mt-2  text-dark"><strong>Cost of the Project
                                                                    (Propsed Only) Rs in Lakhs </strong></h5>
                                                            <p class="text-dark mb-2 customapplsmallhead">Furnish estimate
                                                                of the project under the following heads</p>


                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Land & Site
                                                                        Development</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="Constitution[common_data][site_dev]"
                                                                        value="{{ old('Constitution.common_data.site_dev', $termLoan->constitutions->site_dev ?? '') }}"
                                                                        class="form-control project-calc-total">
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Building and other civil
                                                                        works</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="Constitution[common_data][civil_works]"
                                                                        value="{{ old('Constitution.common_data.civil_works', $termLoan->constitutions->civil_works ?? '') }}"
                                                                        class="form-control project-calc-total">
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Plant & Machinery including
                                                                        Electrification & Installation</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="Constitution[common_data][plant_install]"
                                                                        value="{{ old('Constitution.common_data.plant_install', $termLoan->constitutions->plant_install ?? '') }}"
                                                                        class="form-control project-calc-total">
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Technical Know-how
                                                                        fees</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="Constitution[common_data][technical_fee]"
                                                                        value="{{ old('Constitution.common_data.technical_fee', $termLoan->constitutions->technical_fee ?? '') }}"
                                                                        class="form-control project-calc-total">
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Miscellaneous fixed
                                                                        assets</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="Constitution[common_data][fixed_asset]"
                                                                        value="{{ old('Constitution.common_data.fixed_asset', $termLoan->constitutions->fixed_asset ?? '') }}"
                                                                        class="form-control project-calc-total">
                                                                </div>
                                                            </div>


                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Preliminary and Pre-operative
                                                                        expenses including interest during
                                                                        escalation</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="Constitution[common_data][pre_operative]"
                                                                        value="{{ old('Constitution.common_data.pre_operative', $termLoan->constitutions->pre_operative ?? '') }}"
                                                                        class="form-control project-calc-total">
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Provision for contingencies
                                                                        including escalation</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="Constitution[common_data][provision]"
                                                                        value="{{ old('Constitution.common_data.provision', $termLoan->constitutions->provision ?? '') }}"
                                                                        class="form-control project-calc-total">
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Start-up expenses</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="Constitution[common_data][startup_expense]"
                                                                        value="{{ old('Constitution.common_data.startup_expense', $termLoan->constitutions->startup_expense ?? '') }}"
                                                                        class="form-control project-calc-total">
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Working Capital required
                                                                        including Margin Money</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="Constitution[common_data][margin_money]"
                                                                        value="{{ old('Constitution.common_data.margin_money', $termLoan->constitutions->margin_money ?? '') }}"
                                                                        class="form-control project-calc-total">
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Total</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="Constitution[common_data][cons_total]"
                                                                        value="{{ old('LoanIncIdividual.common_data.total', $termLoan->constitutions->total ?? '') }}"
                                                                        id="project_total" class="form-control" readonly>
                                                                </div>
                                                            </div>




                                                        </div>


                                                    </div>

                                                </div>
                                                <div class="tab-pane" id="Finance">

                                                    <div class="row">
                                                        <div class="col-md-12">

                                                            <h5 class="mt-1 text-dark border-bottom pb-1"><strong>Means of
                                                                    Finance</strong> (Proposed Only)</h5>

                                                            <p class="mt-2 text-dark customapplsmallhead"><strong>A)
                                                                    Equity</strong></p>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Promoters
                                                                        Contribution</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="MeanFinance[promoters_cont]"
                                                                        id="promoters_contribution"
                                                                        value="{{ old('MeanFinance.promoters_cont', $termLoan->meanFinance->promoters_cont ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Total</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="text" name="MeanFinance[equity_total]"
                                                                        id="promoters_total"
                                                                        value="{{ old('MeanFinance.equity_total', $termLoan->meanFinance->equity_total ?? '') }}"
                                                                        readonly class="form-control" />
                                                                </div>
                                                            </div>


                                                            <p class="mt-2 text-dark customapplsmallhead"><strong>B)
                                                                    Debt</strong></p>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">MIDC Ltd</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number" name="MeanFinance[midc_ltd]"
                                                                        id="debt_midc"
                                                                        value="{{ old('MeanFinance.midc_ltd', $termLoan->meanFinance->midc_ltd ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Others</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number" name="MeanFinance[others]"
                                                                        id="debt_other"
                                                                        value="{{ old('MeanFinance.others', $termLoan->meanFinance->others ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Total</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number" name="MeanFinance[debt_total]"
                                                                        id="debt_total"
                                                                        value="{{ old('MeanFinance.debt_total', $termLoan->meanFinance->debt_total ?? '') }}"
                                                                        readonly class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label"
                                                                        style="font-weight: 700 !important; color: #000">GRAND
                                                                        TOTAL (A+B)</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number" name="MeanFinance[grand_total]"
                                                                        id="both_grand"
                                                                        value="{{ old('MeanFinance.grand_total', $termLoan->meanFinance->grand_total ?? '') }}"
                                                                        readonly class="form-control" />
                                                                </div>
                                                            </div>



                                                        </div>
                                                    </div>

                                                    <p class="mt-1 text-dark customapplsmallhead"><strong>Details of
                                                            security to be offered For term loan</strong></p>
                                                    @php
                                                        $primary_total =
                                                            ($termLoan->meanFinance->primary_land ?? 0) +
                                                            ($termLoan->meanFinance->primary_building ?? 0) +
                                                            ($termLoan->meanFinance->primary_machinery ?? 0) +
                                                            ($termLoan->meanFinance->primary_other ?? 0);
                                                        $collateral_total =
                                                            ($termLoan->meanFinance->collateral_land ?? 0) +
                                                            ($termLoan->meanFinance->collateral_building ?? 0) +
                                                            ($termLoan->meanFinance->collateral_machinery ?? 0) +
                                                            ($termLoan->meanFinance->collateral_other ?? 0);
                                                    @endphp
                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Item</th>
                                                                    <th>Primary</th>
                                                                    <th>Collateral</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>1</td>
                                                                    <td>Land</td>
                                                                    <td><input type="number"
                                                                            name="MeanFinance[primary_land]"
                                                                            value="{{ old('MeanFinance.primary_land', $termLoan->meanFinance->primary_land ?? '') }}"
                                                                            class="form-control mw-100 primary_focus"></td>
                                                                    <td><input type="number"
                                                                            name="MeanFinance[collateral_land]"
                                                                            value="{{ old('MeanFinance.collateral_land', $termLoan->meanFinance->collateral_land ?? '') }}"
                                                                            class="form-control mw-100 collateral_focus">
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>2</td>
                                                                    <td>Building</td>
                                                                    <td><input type="number"
                                                                            name="MeanFinance[primary_building]"
                                                                            value="{{ old('MeanFinance.primary_building', $termLoan->meanFinance->primary_building ?? '') }}"
                                                                            class="form-control mw-100 primary_focus"></td>
                                                                    <td><input type="number"
                                                                            name="MeanFinance[collateral_building]"
                                                                            value="{{ old('MeanFinance.collateral_building', $termLoan->meanFinance->collateral_building ?? '') }}"
                                                                            class="form-control mw-100 collateral_focus">
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>3</td>
                                                                    <td>Plant and Machinery</td>
                                                                    <td><input type="number"
                                                                            name="MeanFinance[primary_machinery]"
                                                                            value="{{ old('MeanFinance.primary_machinery', $termLoan->meanFinance->primary_machinery ?? '') }}"
                                                                            class="form-control mw-100 primary_focus"></td>
                                                                    <td><input type="number"
                                                                            name="MeanFinance[collateral_machinery]"
                                                                            value="{{ old('MeanFinance.collateral_machinery', $termLoan->meanFinance->collateral_machinery ?? '') }}"
                                                                            class="form-control mw-100 collateral_focus">
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>4</td>
                                                                    <td>Others</td>
                                                                    <td><input type="number"
                                                                            name="MeanFinance[primary_other]"
                                                                            value="{{ old('MeanFinance.primary_other', $termLoan->meanFinance->primary_other ?? '') }}"
                                                                            class="form-control mw-100 primary_focus"></td>
                                                                    <td><input type="number"
                                                                            name="MeanFinance[collateral_other]"
                                                                            value="{{ old('MeanFinance.collateral_other', $termLoan->meanFinance->collateral_other ?? '') }}"
                                                                            class="form-control mw-100 collateral_focus">
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td></td>
                                                                    <td><strong>Total</strong></td>
                                                                    <td><strong
                                                                            id="primary_focus_total">{{ !empty($primary_total) ? $primary_total : 0 }}</strong>
                                                                    </td>
                                                                    <td><strong
                                                                            id="collateral_focus_total">{{ !empty($collateral_total) ? $collateral_total : 0 }}</strong>
                                                                    </td>
                                                                </tr>



                                                            </tbody>


                                                        </table>
                                                    </div>


                                                    <div class="row mt-2">
                                                        <div class="col-md-12">


                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Details of guarantees, if
                                                                        any, details of the guarantor(s) may be given
                                                                        (Bio-Data and net worth of the guarantor(s) may be
                                                                        given as per perfoma (A))</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="text"
                                                                        name="MeanFinance[guarantee_detail]"
                                                                        value="{{ old('MeanFinance.guarantee_detail', $termLoan->meanFinance->guarantee_detail ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">State the period within which
                                                                        the term loan will be repaid including
                                                                        moratorium</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="text" name="MeanFinance[period_state]"
                                                                        value="{{ old('MeanFinance.period_state', $termLoan->meanFinance->period_state ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="tab-pane" id="worth">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Name</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="text"
                                                                        name="TermNetWorth[common_data][nw_name]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_name', $termLoan->termLoanNetWorth->name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Father's/Husband's
                                                                        Name</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="text"
                                                                        name="TermNetWorth[common_data][nw_father_name]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_father_name', $termLoan->termLoanNetWorth->father_name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Date of Birth</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="date"
                                                                        name="TermNetWorth[common_data][nw_dob]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_dob', $termLoan->termLoanNetWorth->dob ?? '') }}"
                                                                        class="form-control past-date" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <h5 class="mt-1 mb-2 text-dark border-bottom pb-1"><strong>Unit's
                                                            Address & Phone No.</strong></h5>

                                                    <div class="row">
                                                        <div class="col-md-6">

                                                            <p class="mb-4 text-dark customapplsmallhead">
                                                                <strong>Present</strong>
                                                            </p>

                                                            <div class="row mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Address</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" name="TermNetWorth[common_data][nw_unit_address1]" id="presentUnitAddress1">{{ old('TermNetWorth.common_data.nw_unit_address1', $termLoan->termLoanNetWorth->unit_address1 ?? '') }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    &nbsp;
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" name="TermNetWorth[common_data][nw_unit_address2]" id="presentUnitAddress2">{{ old('TermNetWorth.common_data.nw_unit_address2', $termLoan->termLoanNetWorth->unit_address2 ?? '') }}</textarea>
                                                                </div>
                                                            </div>


                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Phone No.</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="TermNetWorth[common_data][nw_unit_phone]"
                                                                        id="presentUnitPhone"
                                                                        value="{{ old('TermNetWorth.common_data.nw_unit_phone', $termLoan->termLoanNetWorth->unit_phone ?? '') }}"
                                                                        class="form-control">
                                                                    @error('TermNetWorth.common_data.nw_unit_phone')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>



                                                        </div>

                                                        <div class="col-md-6">

                                                            <div class="mb-2 d-flex flex-column">
                                                                <p class="text-dark mb-0 me-1 customapplsmallhead">
                                                                    <strong>Permanent</strong>
                                                                </p>

                                                                <div
                                                                    class="form-check form-check-primary mt-25 custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        id="colorCheck2" checked="">
                                                                    <label class="form-check-label"
                                                                        for="colorCheck2">Same
                                                                        As Present Address</label>
                                                                </div>
                                                            </div>


                                                            <div class="row mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Address</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" id="permanentUnitAddress1">{{ $termLoan->termLoanNetWorth->unit_address1 ?? '' }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    &nbsp;
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" id="permanentUnitAddress2">{{ $termLoan->termLoanNetWorth->unit_address2 ?? '' }}</textarea>
                                                                </div>
                                                            </div>


                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Phone No.</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="permanentUnitPhone"
                                                                        value="{{ $termLoan->termLoanNetWorth->unit_phone ?? '' }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>


                                                    <h5 class="mt-2 mb-2 text-dark border-bottom pb-1"><strong>Residential
                                                            Details</strong></h5>

                                                    <div class="row">
                                                        <div class="col-md-6">

                                                            <p class="mb-4 text-dark customapplsmallhead">
                                                                <strong>Present</strong>
                                                            </p>

                                                            <div class="row mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Address</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" name="TermNetWorth[common_data][nw_resi_address1]" id="presentResiAddress1">{{ old('TermNetWorth.common_data.nw_resi_address1', $termLoan->termLoanNetWorth->resi_address1 ?? '') }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    &nbsp;
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" name="TermNetWorth[common_data][nw_resi_address2]" id="presentResiAddress2">{{ old('TermNetWorth.common_data.nw_resi_address2', $termLoan->termLoanNetWorth->resi_address2 ?? '') }}</textarea>
                                                                </div>
                                                            </div>


                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Mobile No.</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="TermNetWorth[common_data][nw_resi_mobile]"
                                                                        id="presentResiMobile"
                                                                        value="{{ old('TermNetWorth.common_data.nw_resi_mobile', $termLoan->termLoanNetWorth->resi_mobile ?? '') }}"
                                                                        class="form-control">
                                                                    @error('TermNetWorth.common_data.nw_resi_mobile')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Phone No.</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="TermNetWorth[common_data][nw_resi_phone]"
                                                                        id="presentResiPhone"
                                                                        value="{{ old('TermNetWorth.common_data.nw_resi_phone', $termLoan->termLoanNetWorth->resi_phone ?? '') }}"
                                                                        class="form-control">
                                                                    @error('TermNetWorth.common_data.nw_resi_phone')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>



                                                        </div>

                                                        <div class="col-md-6">

                                                            <div class="mb-2 d-flex flex-column">
                                                                <p class="text-dark mb-0 me-1 customapplsmallhead">
                                                                    <strong>Permanent</strong>
                                                                </p>

                                                                <div
                                                                    class="form-check form-check-primary mt-25 custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        id="res" checked="">
                                                                    <label class="form-check-label" for="res">Same
                                                                        As Present Address</label>
                                                                </div>
                                                            </div>


                                                            <div class="row mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Address</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" id="permanentResiAddress1">{{ $termLoan->termLoanNetWorth->resi_address1 ?? '' }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    &nbsp;
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" id="permanentResiAddress2">{{ $termLoan->termLoanNetWorth->resi_address2 ?? '' }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Mobile No.</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="permanentResiMobile"
                                                                        value="{{ $termLoan->termLoanNetWorth->resi_mobile ?? '' }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>


                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Phone No.</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="permanentResiPhone"
                                                                        value="{{ $termLoan->termLoanNetWorth->resi_phone ?? '' }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>

                                                    <hr />

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2">
                                                            <label class="form-label">Qualification</label>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <input type="text"
                                                                name="TermNetWorth[common_data][nw_qualification]"
                                                                value="{{ old('TermNetWorth.common_data.nw_qualification', $termLoan->termLoanNetWorth->qualification ?? '') }}"
                                                                class="form-control">
                                                        </div>
                                                    </div>

                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>Experience
                                                        </strong>(Enclose Certificates Wherever available)</p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>No. of years</th>
                                                                    <th>Employer</th>
                                                                    <th>Designation</th>
                                                                    <th>Last Salary Drawn</th>
                                                                    <th>Document</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-term-experience">
                                                                <tr>
                                                                    <td id="row-number-term-experience">1</td>
                                                                    <td><input type="number"
                                                                            name="TermNetWorth[nw_no_of_years][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="TermNetWorth[nw_employer][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="TermNetWorth[nw_designation][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="TermNetWorth[nw_last_salary_drawn][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="file"
                                                                            name="TermNetWorth[nw_doc][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-term-experience"
                                                                            id="add-row-term-experience"
                                                                            data-class="add-row-term-experience"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>

                                                                @if (isset($termLoan) &&
                                                                        $termLoan->termLoanNetWorth &&
                                                                        $termLoan->termLoanNetWorth->termLoanNetWorthExperience &&
                                                                        $termLoan->termLoanNetWorth->termLoanNetWorthExperience->count() > 0)
                                                                    @foreach ($termLoan->termLoanNetWorth->termLoanNetWorthExperience as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="TermNetWorth[nw_no_of_years][]"
                                                                                    value="{{ $val->no_of_years ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="TermNetWorth[nw_employer][]"
                                                                                    value="{{ $val->employer ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="TermNetWorth[nw_designation][]"
                                                                                    value="{{ $val->designation ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="TermNetWorth[nw_last_salary_drawn][]"
                                                                                    value="{{ $val->last_salary_drawn ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td>
                                                                                @if ($key == 0)
                                                                                    <input type="hidden"
                                                                                        name="TermNetWorth[stored_nw_doc][]"
                                                                                        value="">
                                                                                @endif
                                                                                <input type="hidden"
                                                                                    name="TermNetWorth[stored_nw_doc][]"
                                                                                    value="{{ $val->doc ?? '' }}">
                                                                                <div class="d-flex align-items-center"
                                                                                    style="gap:5px;">
                                                                                    <input type="file"
                                                                                        name="TermNetWorth[nw_doc][]"
                                                                                        class="form-control mw-100">
                                                                                    <span>
                                                                                        @if (isset($val->doc))
                                                                                            <a href="{{ asset('storage/' . $val->doc) }}"
                                                                                                target="_blank"
                                                                                                download>{{ $val->employer ?? 'Employee' }}
                                                                                                Doc</a>
                                                                                        @endif
                                                                                    </span>
                                                                                </div>
                                                                            </td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($termLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($termLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif


                                                            </tbody>


                                                        </table>
                                                    </div>


                                                    <div class="row mt-2">
                                                        <div class="col-md-12">
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Present Profession</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="text"
                                                                        name="TermNetWorth[common_data][nw_present_profession]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_present_profession', $termLoan->termLoanNetWorth->present_profession ?? '') }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>

                                                            <hr />
                                                            @php
                                                                $selectedPassportHolder = old(
                                                                    'TermNetWorth.common_data.nw_passport_holder',
                                                                    $termLoan->termLoanNetWorth->passport_holder ?? '0',
                                                                );
                                                                $selectedClubMember = old(
                                                                    'TermNetWorth.common_data.nw_club_member',
                                                                    $termLoan->termLoanNetWorth->club_member ?? '0',
                                                                );
                                                            @endphp
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-12">
                                                                    <label class="form-label">Are you a valid passport
                                                                        holder? <span class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <div class="demo-inline-spacing">
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="radio" id="Guarantor"
                                                                                name="TermNetWorth[common_data][nw_passport_holder]"
                                                                                value="1" class="form-check-input"
                                                                                {{ $selectedPassportHolder == '1' ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-bolder"
                                                                                for="Guarantor">Yes</label>
                                                                        </div>
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="radio" id="applicant"
                                                                                name="TermNetWorth[common_data][nw_passport_holder]"
                                                                                value="0" class="form-check-input"
                                                                                {{ $selectedPassportHolder == '0' ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-bolder"
                                                                                for="applicant">No</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12 pass_holder">
                                                                    <label class="form-label mt-1">(If yes, copy of I.T.
                                                                        clearance to be enclosed)</label>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 pass_holder">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Permanent Account
                                                                        No.</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="text"
                                                                        name="TermNetWorth[common_data][nw_permanent_acc]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_permanent_acc', $termLoan->termLoanNetWorth->permanent_acc ?? '') }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 pass_holder">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Income declared last
                                                                        year</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="text"
                                                                        name="TermNetWorth[common_data][nw_income_declare]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_income_declare', $termLoan->termLoanNetWorth->income_declare ?? '') }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>

                                                            <hr class="pass_holder" />

                                                            <div class="row mb-1 pass_holder">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Address of Bank A/C</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <textarea class="form-control" name="TermNetWorth[common_data][nw_bank_address]">{{ old('TermNetWorth.common_data.nw_bank_address', $termLoan->termLoanNetWorth->bank_address ?? '') }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 pass_holder">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Date of Opening of Bank
                                                                        A/C</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="date"
                                                                        name="TermNetWorth[common_data][nw_opening_bank_date]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_opening_bank_date', $termLoan->termLoanNetWorth->opening_bank_date ?? '') }}"
                                                                        class="form-control past-date">
                                                                </div>
                                                            </div>

                                                            <hr />

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Are you a member of any
                                                                        club? <span class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <div class="demo-inline-spacing">
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="radio" id="Guarantor"
                                                                                name="TermNetWorth[common_data][nw_club_member]"
                                                                                value="1" class="form-check-input"
                                                                                {{ $selectedClubMember == '1' ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-bolder"
                                                                                for="Guarantor">Yes</label>
                                                                        </div>
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="radio" id="applicant"
                                                                                name="TermNetWorth[common_data][nw_club_member]"
                                                                                value="0" class="form-check-input"
                                                                                {{ $selectedClubMember == '0' ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-bolder"
                                                                                for="applicant">No</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 club_mem">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Name of Club</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="text"
                                                                        name="TermNetWorth[common_data][nw_club_name]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_club_name', $termLoan->termLoanNetWorth->club_name ?? '') }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>


                                                            <div class="row mb-1 club_mem">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Address of Club</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <textarea class="form-control" name="TermNetWorth[common_data][nw_club_address]">{{ old('TermNetWorth.common_data.nw_club_address', $termLoan->termLoanNetWorth->club_address ?? '') }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 club_mem">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Membership fee paid</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="text"
                                                                        name="TermNetWorth[common_data][nw_paid_membership_fee]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_paid_membership_fee', $termLoan->termLoanNetWorth->paid_membership_fee ?? '') }}"
                                                                        class="form-control">
                                                                </div>
                                                            </div>


                                                            <hr />



                                                        </div>

                                                    </div>


                                                    <p class="mb-25 text-dark customapplsmallhead"><strong>A) Immovable
                                                            Properties</strong></p>
                                                    <p class="mt-0  text-dark customapplsmallhead">(Properties owned or
                                                        jointly owned with family members with details of eligible
                                                        percentage of share)</p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Description (Mention specific details such as Sy.
                                                                        No/Site No./Door No./Street/Town etc. and also
                                                                        Dimension</th>
                                                                    <th>Value(in lakhs)</th>
                                                                    <th>How acquired</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-term-property">
                                                                <tr>
                                                                    <td id="row-number-term-property">1</td>
                                                                    <td><input type="text"
                                                                            name="TermNetWorth[nw_property_desc][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="TermNetWorth[nw_property_value][]"
                                                                            class="form-control mw-100 class_property_value">
                                                                    </td>
                                                                    <td>
                                                                        <select class="form-select"
                                                                            name="TermNetWorth[nw_acquired][]">
                                                                            <option value="">Select</option>
                                                                            <option value="self">Self</option>
                                                                            <option value="ancestral">Ancestral</option>
                                                                            <option value="gifted">Gifted</option>
                                                                        </select>
                                                                    </td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-term-property"
                                                                            id="add-row-term-property"
                                                                            data-class="add-row-term-property"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>

                                                                @if (isset($termLoan) &&
                                                                        $termLoan->termLoanNetWorth &&
                                                                        $termLoan->termLoanNetWorth->termLoanNetWorthProperty &&
                                                                        $termLoan->termLoanNetWorth->termLoanNetWorthProperty->count() > 0)
                                                                    @foreach ($termLoan->termLoanNetWorth->termLoanNetWorthProperty as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="TermNetWorth[nw_property_desc][]"
                                                                                    value="{{ $val->property_desc ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="TermNetWorth[nw_property_value][]"
                                                                                    value="{{ $val->property_value ?? '' }}"
                                                                                    class="form-control mw-100 class_property_value">
                                                                            </td>
                                                                            <td>
                                                                                <select class="form-select"
                                                                                    name="TermNetWorth[nw_acquired][]">
                                                                                    <option value="">Select</option>
                                                                                    <option value="self"
                                                                                        {{ (isset($val->acquired) ? $val->acquired : '') == 'self' ? 'selected' : '' }}>
                                                                                        Self</option>
                                                                                    <option value="ancestral"
                                                                                        {{ (isset($val->acquired) ? $val->acquired : '') == 'ancestral' ? 'selected' : '' }}>
                                                                                        Ancestral</option>
                                                                                    <option value="gifted"
                                                                                        {{ (isset($val->acquired) ? $val->acquired : '') == 'gifted' ? 'selected' : '' }}>
                                                                                        Gifted</option>
                                                                                </select>
                                                                            </td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($termLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($termLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif


                                                            </tbody>


                                                        </table>
                                                    </div>


                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>B) Movable
                                                            Assets</strong></p>

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Cash on Hand</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="TermNetWorth[common_data][nw_cash_on_hold]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_cash_on_hold', $termLoan->termLoanNetWorth->cash_on_hold ?? '') }}"
                                                                        class="form-control moveable_flow" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Cash at Bank</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="TermNetWorth[common_data][nw_bank_cash]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_bank_cash', $termLoan->termLoanNetWorth->bank_cash ?? '') }}"
                                                                        class="form-control moveable_flow" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Investments</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="TermNetWorth[common_data][nw_bank_investment]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_bank_investment', $termLoan->termLoanNetWorth->bank_investment ?? '') }}"
                                                                        class="form-control moveable_flow" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Deposits</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="TermNetWorth[common_data][nw_bank_deposit]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_bank_deposit', $termLoan->termLoanNetWorth->bank_deposit ?? '') }}"
                                                                        class="form-control moveable_flow" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label
                                                                        class="form-label">Shares/Debentures/Others</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="TermNetWorth[common_data][nw_bank_shares]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_bank_shares', $termLoan->termLoanNetWorth->bank_shares ?? '') }}"
                                                                        class="form-control moveable_flow" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Jewellery</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="TermNetWorth[common_data][nw_jewelery]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_jewelery', $termLoan->termLoanNetWorth->jewelery ?? '') }}"
                                                                        class="form-control moveable_flow" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Any Movable assets, vehicle
                                                                        etc.</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="TermNetWorth[common_data][nw_moveable_asset]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_moveable_asset', $termLoan->termLoanNetWorth->moveable_asset ?? '') }}"
                                                                        class="form-control moveable_flow" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label"><strong>Sub total
                                                                            (B)</strong></label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number"
                                                                        name="TermNetWorth[common_data][nw_moveable_sub_total]"
                                                                        id="moveable_total"
                                                                        value="{{ old('TermNetWorth.common_data.nw_moveable_sub_total', $termLoan->termLoanNetWorth->moveable_sub_total ?? '') }}"
                                                                        readonly class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label"
                                                                        style="font-weight: 700 !important; color: #000">TOTAL
                                                                        Assets (A+B)</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number" id="total_asset_AB"
                                                                        name="TermNetWorth[common_data][nw_moveable_total]"
                                                                        value="{{ old('TermNetWorth.common_data.nw_moveable_total', $termLoan->termLoanNetWorth->moveable_total ?? '') }}"
                                                                        readonly class="form-control"
                                                                        placeholder="Total Value of Assets" />
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>


                                                    <p class="mt-2 text-dark customapplsmallhead"><strong>C)
                                                            Liabilities</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Description</th>
                                                                    <th>Value(in lakhs)</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-term-lia">
                                                                <tr>
                                                                    <td id="row-number-term-lia">1</td>
                                                                    <td><input type="text"
                                                                            name="TermNetWorth[nw_net_worth_desc][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="TermNetWorth[nw_net_worth_value][]"
                                                                            class="form-control mw-100 nw_nt_wrth"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-term-lia"
                                                                            id="add-row-term-lia"
                                                                            data-class="add-row-term-lia"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>
                                                                @if (isset($termLoan) &&
                                                                        $termLoan->termLoanNetWorth &&
                                                                        $termLoan->termLoanNetWorth->termLoanNetWorthLiability &&
                                                                        $termLoan->termLoanNetWorth->termLoanNetWorthLiability->count() > 0)
                                                                    @foreach ($termLoan->termLoanNetWorth->termLoanNetWorthLiability as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="TermNetWorth[nw_net_worth_desc][]"
                                                                                    value="{{ $val->net_worth_desc ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="TermNetWorth[nw_net_worth_value][]"
                                                                                    value="{{ $val->net_worth_value ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($termLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($termLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif


                                                            </tbody>


                                                        </table>
                                                    </div>

                                                    <div class="row mt-2 align-items-center mb-1">
                                                        <div class="col-md-2">
                                                            <label class="form-label"
                                                                style="font-weight: 700 !important; color: #000">Total Net
                                                                Worth<br />(Assets-Liabilities)</label>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <input type="number"
                                                                name="TermNetWorth[common_data][nw_total_net_worth]"
                                                                value="{{ old('TermNetWorth.common_data.nw_total_net_worth', $termLoan->termLoanNetWorth->total_net_worth ?? '') }}"
                                                                readonly class="form-control" id="nw_total_net_worth" />
                                                        </div>
                                                    </div>


                                                    <div class="bg-light p-1 rounded">
                                                        <p class="text-dark customapplsmallhead"><strong>Note:</strong>
                                                            Enclose copies of property Documents, Pass Books, shares,
                                                            Debentures etc in proof of your net worth</p>

                                                        <div class="row align-items-center">
                                                            <div class="col-md-2">
                                                                <label class="form-label">Assets Proof</label>
                                                            </div>

                                                            <div class="col-md-3">
                                                                <input type="hidden"
                                                                    name="TermNetWorth[common_data][stored_asset_proof2]"
                                                                    value="{{ old('TermNetWorth.common_data.stored_asset_proof2', $termLoan->termLoanNetWorth->asset_proof ?? '') }}"
                                                                    class="form-control" />
                                                                <input type="file"
                                                                    name="TermNetWorth[common_data][asset_proof]"
                                                                    value="{{ old('TermNetWorth.common_data.asset_proof') }}"
                                                                    class="form-control" />
                                                            </div>
                                                            @if (isset($termLoan) && $termLoan->termLoanNetWorth && $termLoan->termLoanNetWorth->asset_proof)
                                                                <div class="col-md-3 mt-1">
                                                                    <p><i data-feather='folder' class="me-50"></i><a
                                                                            href="{{ asset('storage/' . $termLoan->termLoanNetWorth->asset_proof) }}"
                                                                            style="color:green; font-size:12px;"
                                                                            target="_blank" download>Asset Proof</a></p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>


                                                </div>

                                                <div class="tab-pane" id="Documentsupload">
                                                    <h5 class="mt-2 mb-2  text-dark"><strong>Upload documents provided by
                                                            the Customer</strong></h5>

                                                    <div class="row">
                                                        <div class="col-md-6">



                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Aadhar Card</label>
                                                                </div>

                                                                <div class="col-md-5">
                                                                    <input type="hidden"
                                                                        name="TermLoanDocument[common_data][stored_adhar_card]"
                                                                        value="{{ old('TermLoanDocument.common_data.stored_adhar_card', $termLoan->termLoanDocument->adhar_card ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="TermLoanDocument[common_data][adhar_card][]"
                                                                        value="{{ old('TermLoanDocument.common_data.adhar_card') }}"
                                                                        class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" multiple />
                                                                </div>

                                                                @if (isset($termLoan) && $termLoan->termLoanDocument && $termLoan->termLoanDocument->adhar_card)
                                                                    @php
                                                                        $adhar_doc_json =
                                                                            $termLoan->termLoanDocument->adhar_card;
                                                                        $adhar_docs = json_decode(
                                                                            $adhar_doc_json,
                                                                            true,
                                                                        );
                                                                    @endphp
                                                                    @if (!empty($adhar_docs) && is_array($adhar_docs))
                                                                        @foreach ($adhar_docs as $key => $doc)
                                                                            <div class="col-md-3 mt-1">
                                                                                <p><i data-feather='folder'
                                                                                        class="me-50"></i><a
                                                                                        href="{{ asset('storage/' . $doc) }}"
                                                                                        style="color:green; font-size:12px;"
                                                                                        target="_blank" download>Aadhar
                                                                                        Doc</a></p>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">PAN/GIR No.</label>
                                                                </div>

                                                                <div class="col-md-5">
                                                                    <input type="hidden"
                                                                        name="TermLoanDocument[common_data][stored_gir_no]"
                                                                        value="{{ old('TermLoanDocument.common_data.stored_gir_no', $termLoan->termLoanDocument->gir_no ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="TermLoanDocument[common_data][gir_no][]"
                                                                        value="{{ old('TermLoanDocument.common_data.gir_no') }}"
                                                                        class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" multiple />
                                                                </div>
                                                                @if (isset($termLoan) && $termLoan->termLoanDocument && $termLoan->termLoanDocument->gir_no)

                                                                    @php
                                                                        $term_doc_json =
                                                                            $termLoan->termLoanDocument->gir_no;
                                                                        $term_docs = json_decode($term_doc_json, true);
                                                                    @endphp
                                                                    @if (!empty($term_docs) && is_array($term_docs))
                                                                        @foreach ($term_docs as $key => $doc)
                                                                            <div class="col-md-3 mt-1">
                                                                                <p><i data-feather='folder'
                                                                                        class="me-50"></i><a
                                                                                        href="{{ asset('storage/' . $doc) }}"
                                                                                        style="color:green; font-size:12px;"
                                                                                        target="_blank" download>GIR
                                                                                        Doc</a></p>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </div>


                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Assets Proof</label>
                                                                </div>

                                                                <div class="col-md-5">
                                                                    <input type="hidden"
                                                                        name="TermLoanDocument[common_data][stored_asset_proof]"
                                                                        value="{{ $termLoan->termLoanDocument->asset_proof ?? '' }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="TermLoanDocument[common_data][asset_proof][]"
                                                                        value="{{ old('TermLoanDocument.common_data.asset_proof') }}"
                                                                        class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" multiple />
                                                                </div>
                                                                @if (isset($termLoan) && $termLoan->termLoanDocument && $termLoan->termLoanDocument->asset_proof)

                                                                    @php
                                                                        $asset_doc_json =
                                                                            $termLoan->termLoanDocument->asset_proof;
                                                                        $asset_docs = json_decode(
                                                                            $asset_doc_json,
                                                                            true,
                                                                        );
                                                                    @endphp
                                                                    @if (!empty($asset_docs) && is_array($asset_docs))
                                                                        @foreach ($asset_docs as $key => $doc)
                                                                            <div class="col-md-3 mt-1">
                                                                                <p><i data-feather='folder'
                                                                                        class="me-50"></i><a
                                                                                        href="{{ asset('storage/' . $doc) }}"
                                                                                        style="color:green; font-size:12px;"
                                                                                        target="_blank" download>Asset
                                                                                        Proof</a></p>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Scan form
                                                                        Application</label>
                                                                </div>

                                                                <div class="col-md-5">
                                                                    <input type="hidden"
                                                                        name="TermLoanDocument[common_data][stored_application]"
                                                                        value="{{ old('TermLoanDocument.common_data.stored_application', $termLoan->termLoanDocument->application ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="TermLoanDocument[common_data][application][]"
                                                                        value="{{ old('TermLoanDocument.common_data.application') }}"
                                                                        class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" multiple />
                                                                </div>
                                                                @if (isset($termLoan) && $termLoan->termLoanDocument && $termLoan->termLoanDocument->application)

                                                                    @php
                                                                        $application_doc_json =
                                                                            $termLoan->termLoanDocument->application;
                                                                        $application_docs = json_decode(
                                                                            $application_doc_json,
                                                                            true,
                                                                        );
                                                                    @endphp
                                                                    @if (!empty($application_docs) && is_array($application_docs))
                                                                        @foreach ($application_docs as $key => $doc)
                                                                            <div class="col-md-3 mt-1">
                                                                                <p><i data-feather='folder'
                                                                                        class="me-50"></i><a
                                                                                        href="{{ asset('storage/' . $doc) }}"
                                                                                        style="color:green; font-size:12px;"
                                                                                        target="_blank"
                                                                                        download>Application</a></p>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
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
                    </form>
                </section>


            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="modal fade" id="viewassesgive" tabindex="-1" aria-labelledby="shareProjectTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('loanAssessment.assessment-proceed') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">
                                Assessment
                                by
                                Field Officer</h4>
                            <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{ $overview->proprietor_name }} |
                                {{ $overview->term_loan }} | {{ explode(' ', $overview->created_at)[0] }}</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body pb-2">

                        <div class="row mt-1">

                            <div class="col-md-12">

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-1">
                                            <label class="form-label">Upload Document <span
                                                    class="text-danger">*</span></label>
                                            <input type="file" class="form-control remove-disable" name="document"
                                                required />
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="loan_type" value="vehicle" required>
                                <input type="hidden" name="loan_application_id"
                                    value="{{ request()->route('id') }}" required>
                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    <textarea class="form-control remove-disable" name="remarks" required></textarea>
                                </div>



                            </div>

                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reject" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('loanAssessment.loan-reject') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Reject
                                Term
                                Loan Application</h4>
                            <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{ $overview->proprietor_name }} |
                                {{ $overview->term_loan }} | {{ explode(' ', $overview->created_at)[0] }}</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body pb-2">

                        <div class="row mt-1">

                            <div class="col-md-12">

                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" class="form-control remove-disable" name="document"
                                        required />
                                </div>
                                <input type="hidden" name="loan_type" value="term" required>
                                <input type="hidden" name="loan_application_id"
                                    value="{{ request()->route('id') }}" required>
                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    <textarea class="form-control remove-disable" name="remarks" required></textarea>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="return" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('loanAssessment.loan-return') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Return
                                Home
                                Loan Application</h4>
                            <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{ $overview->proprietor_name }} |
                                {{ $overview->term_loan }} | {{ explode(' ', $overview->created_at)[0] }}</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body pb-2">

                        <div class="row mt-1">

                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" class="form-control remove-disable" name="document"
                                        required />
                                </div>
                                <input type="hidden" name="loan_type" value="term" required>
                                <input type="hidden" name="loan_application_id"
                                    value="{{ request()->route('id') }}" required>
                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    <textarea class="form-control remove-disable" name="remarks" required></textarea>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var getVoucherBaseUrl = "{{ url('get_voucher_no') }}";
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/js/loan.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script type="text/javascript">
        var getSeriesUrl = "{{ url('loan/get-series') }}";
        var getvoucherUrl = "{{ url('/get_voucher_no') }}".trim();
    </script>
    <script>
        $(document).ready(function() {
            $('#series').on('change', function() {
                var book_id = $(this).val();
                var request = $('#appli_no');
                request.val('');
                if (book_id) {
                    $.ajax({
                        url: "{{ url('/loan/get-loan-request') }}/" + book_id,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            if (data.requestno == 1) {
                                request.prop('readonly', false);
                            } else {
                                request.prop('readonly', true);
                                request.val(data.requestno);
                            }
                        }
                    });
                }
            });
            toggleClubMemberFields();
            $('input[name="TermNetWorth[common_data][nw_club_member]"]').change(function() {
                toggleClubMemberFields();
            });

            function toggleClubMemberFields() {
                var selectedValue = $('input[name="TermNetWorth[common_data][nw_club_member]"]:checked').val();
                if (selectedValue == '0') {
                    $('.club_mem').hide();
                } else {
                    $('.club_mem').show();
                }
            }

            togglePassUserFields();
            $('input[name="TermNetWorth[common_data][nw_passport_holder]"]').change(function() {
                togglePassUserFields();
            });

            function togglePassUserFields() {
                var selectedValue = $('input[name="TermNetWorth[common_data][nw_passport_holder]"]:checked').val();
                if (selectedValue == '0') {
                    $('.pass_holder').hide();
                } else {
                    $('.pass_holder').show();
                }
            }

            @if (isset($termLoan))
                let book_type_val = $("#book_type").val();

                if (book_type_val) {
                    fetchLoanSeries(book_type_val, 'series').done(function() {

                        let termLoanSeries = '{{ $termLoan->series }}';
                        $('#series option').each(function() {
                            if ($(this).val() == termLoanSeries) {
                                $(this).prop('selected', true);
                            }
                        });
                    });
                } else {
                    console.log("Book type value is empty");
                }
            @endif
            $('.cancelButton').on('click', function() {
                $('#approve').modal('hide');
                $('#reject').modal('hide');
            });
            @if (!isset($editData))
                var formData = JSON.parse(localStorage.getItem('formData') || '[]');
                var $firstRow = $('#table-body-partner tr:first').clone();
                formData.forEach(function(rowData, index) {
                    var $newRow = $firstRow.clone();

                    $newRow.find('td:first').text(index + 2);

                    $newRow.find('input, select').each(function() {
                        var nameAttr = $(this).attr('name');
                        if (nameAttr && rowData[nameAttr] !== undefined) {
                            $(this).val(rowData[nameAttr]);
                        }
                    });

                    $newRow.find('td:last').html(
                        '<a href="#" class="text-danger delete-item"><i data-feather="trash-2"></i></a>'
                    );
                    $('#table-body-partner').append($newRow);
                });
                localStorage.removeItem('formData');
                feather.replace();

                var formData1 = JSON.parse(localStorage.getItem('formData1') || '[]');
                var $firstRow = $('#table-body-promoters tr:first').clone();
                formData1.forEach(function(rowData, index) {
                    var $newRow = $firstRow.clone();

                    $newRow.find('td:first').text(index + 2);

                    $newRow.find('input, select').each(function() {
                        var nameAttr = $(this).attr('name');
                        if (nameAttr && rowData[nameAttr] !== undefined) {
                            $(this).val(rowData[nameAttr]);
                        }
                    });

                    $newRow.find('td:last').html(
                        '<a href="#" class="text-danger delete-item"><i data-feather="trash-2"></i></a>'
                    );
                    $('#table-body-promoters').append($newRow);
                });
                localStorage.removeItem('formData1');
                feather.replace();

                var formData2 = JSON.parse(localStorage.getItem('formData2') || '[]');
                var $firstRow = $('#table-body-guarantor-address tr:first').clone();
                formData2.forEach(function(rowData, index) {
                    var $newRow = $firstRow.clone();

                    $newRow.find('td:first').text(index + 2);

                    $newRow.find('input, select').each(function() {
                        var nameAttr = $(this).attr('name');
                        if (nameAttr && rowData[nameAttr] !== undefined) {
                            $(this).val(rowData[nameAttr]);
                        }
                    });

                    $newRow.find('td:last').html(
                        '<a href="#" class="text-danger delete-item"><i data-feather="trash-2"></i></a>'
                    );
                    $('#table-body-guarantor-address').append($newRow);
                });
                localStorage.removeItem('formData2');
                feather.replace();

                var formData3 = JSON.parse(localStorage.getItem('formData3') || '[]');
                var $firstRow = $('#table-body-term-experience tr:first').clone();
                formData3.forEach(function(rowData, index) {
                    var $newRow = $firstRow.clone();

                    $newRow.find('td:first').text(index + 2);

                    $newRow.find('input, select').each(function() {
                        var nameAttr = $(this).attr('name');
                        var type = $(this).attr('type');
                        if (type !== 'file' && nameAttr && rowData[nameAttr] !== undefined) {
                            $(this).val(rowData[nameAttr]);
                        }
                    });

                    $newRow.find('td:last').html(
                        '<a href="#" class="text-danger delete-item"><i data-feather="trash-2"></i></a>'
                    );
                    $('#table-body-term-experience').append($newRow);
                });
                localStorage.removeItem('formData3');
                feather.replace();

                var formData4 = JSON.parse(localStorage.getItem('formData4') || '[]');
                var $firstRow = $('#table-body-term-property tr:first').clone();
                formData4.forEach(function(rowData, index) {
                    var $newRow = $firstRow.clone();

                    $newRow.find('td:first').text(index + 2);

                    $newRow.find('input, select').each(function() {
                        var nameAttr = $(this).attr('name');
                        if (nameAttr && rowData[nameAttr] !== undefined) {
                            $(this).val(rowData[nameAttr]);
                        }
                    });

                    $newRow.find('td:last').html(
                        '<a href="#" class="text-danger delete-item"><i data-feather="trash-2"></i></a>'
                    );
                    $('#table-body-term-property').append($newRow);
                });
                localStorage.removeItem('formData4');
                feather.replace();

                var formData5 = JSON.parse(localStorage.getItem('formData5') || '[]');
                var $firstRow = $('#table-body-term-lia tr:first').clone();
                formData5.forEach(function(rowData, index) {
                    var $newRow = $firstRow.clone();

                    $newRow.find('td:first').text(index + 2);

                    $newRow.find('input, select').each(function() {
                        var nameAttr = $(this).attr('name');
                        if (nameAttr && rowData[nameAttr] !== undefined) {
                            $(this).val(rowData[nameAttr]);
                        }
                    });

                    $newRow.find('td:last').html(
                        '<a href="#" class="text-danger delete-item"><i data-feather="trash-2"></i></a>'
                    );
                    $('#table-body-term-lia').append($newRow);
                });
                localStorage.removeItem('formData5');
                feather.replace();

                var formData6 = JSON.parse(localStorage.getItem('formData6') || '[]');
                var $firstRow = $('#table-body-term-loan tr:first').clone();
                formData6.forEach(function(rowData, index) {
                    var $newRow = $firstRow.clone();

                    $newRow.find('td:first').text(index + 2);

                    $newRow.find('input, select').each(function() {
                        var nameAttr = $(this).attr('name');
                        var type = $(this).attr('type');

                        if (type !== 'file' && nameAttr && rowData[nameAttr] !== undefined) {
                            $(this).val(rowData[nameAttr]);
                        }
                    });

                    $newRow.find('td:last').html(
                        '<a href="#" class="text-danger delete-item"><i data-feather="trash-2"></i></a>'
                    );
                    $('#table-body-term-loan').append($newRow);
                });
                localStorage.removeItem('formData6');
                feather.replace();
            @endif

            // table add delete values
            feather.replace();
            $('tbody').on('click',
                '#add-row-term-loan, #add-row-partner, #add-row-promoters, #add-row-term-experience, #add-row-term-property, #add-row-term-lia',
                function(e) {
                    e.preventDefault();
                    var $tbody = $(this).closest('tbody');
                    var tbodyId = $tbody.attr('id');
                    var clickedClass = $(this).attr('id');
                    var $firstTdClass = $(this).closest('tr').find('td:first').attr('id');

                    var $currentRow = $(this).closest('tr');
                    var $newRow = $currentRow.clone(true, true);
                    // var $newRow = $currentRow.clone();

                    var isValid = $currentRow.find('input').filter(function() {
                        return $(this).val().trim() !== '';
                    }).length > 0;

                    if (!isValid) {
                        alert('At least one field must be filled before adding a new row.');
                        return;
                    }

                    $currentRow.find('input').val('');
                    var nwAcquiredValue = $currentRow.find('select[name="TermNetWorth[nw_acquired][]"]').val();

                    // Update row number for the new row
                    var nextIndex = $('#' + tbodyId + ' tr').length + 1;
                    $newRow.find('#' + $firstTdClass).text(nextIndex);
                    $newRow.find('#' + clickedClass).removeClass(clickedClass).removeAttr('id').removeAttr(
                        'data-class').addClass('text-danger delete-item').html(
                        '<i data-feather="trash-2"></i>');

                    if (nwAcquiredValue) {
                        $newRow.find('select[name="TermNetWorth[nw_acquired][]"]').val(nwAcquiredValue);
                        $currentRow.find('select[name="TermNetWorth[nw_acquired][]"]').val('');
                    }

                    $('#' + tbodyId).append($newRow);
                    updateTotals();
                    feather.replace();
                });

            $('tbody').on('click', '.delete-item', function(e) {
                e.preventDefault();

                var $tableBody = $(this).closest('tbody');

                $(this).closest('tr').remove();

                var $firstTdId = $(this).closest('tr').find('td:first').attr('id');
                $tableBody.find('tr').each(function(index) {
                    var $rowNumber = $(this).find('#' + $firstTdId);
                    if ($rowNumber.length) {
                        $rowNumber.text(index + 1);
                    }
                });
                updateTotals();
            });

            // hidden value set on click of draft and proceed
            $('.submission_val').click(function() {
                let data_val = $(this).attr('data-val');
                if (data_val == 'draft') {
                    $("#status_val").val(data_val);
                } else {
                    $("#status_val").val(data_val);
                }
            });
        });

        @if (!isset($editData))
            $('form').on('submit', function(e) {
                var formData = [];
                var formData1 = [];
                var formData2 = [];
                var formData3 = [];
                var formData4 = [];
                var formData5 = [];
                var formData6 = [];

                $('#table-body-partner').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData[name] = $(this).val();
                    });

                    formData.push(rowData);
                });

                $('#table-body-promoters').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData1 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData1[name] = $(this).val();
                    });

                    formData1.push(rowData1);
                });

                $('#table-body-guarantor-address').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData2 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData2[name] = $(this).val();
                    });

                    formData2.push(rowData2);
                });

                $('#table-body-term-experience').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData3 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        var type = $(this).attr('type');

                        if (type !== 'file') {
                            rowData3[name] = $(this).val();
                        }
                    });

                    formData3.push(rowData3);
                });

                $('#table-body-term-property').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData4 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData4[name] = $(this).val();
                    });

                    formData4.push(rowData4);
                });

                $('#table-body-term-lia').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData5 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData5[name] = $(this).val();
                    });

                    formData5.push(rowData5);
                });

                $('#table-body-term-loan').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData6 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        var type = $(this).attr('type');
                        if (type !== 'file') {
                            rowData6[name] = $(this).val();
                        }
                    });

                    formData6.push(rowData6);
                });

                localStorage.setItem('formData', JSON.stringify(formData));
                localStorage.setItem('formData1', JSON.stringify(formData1));
                localStorage.setItem('formData2', JSON.stringify(formData2));
                localStorage.setItem('formData3', JSON.stringify(formData3));
                localStorage.setItem('formData4', JSON.stringify(formData4));
                localStorage.setItem('formData5', JSON.stringify(formData5));
                localStorage.setItem('formData6', JSON.stringify(formData6));
            });
        @endif

        // document.addEventListener('DOMContentLoaded', function () {
        //     @if ($errors->any())
        //         @foreach ($errors->all() as $error)
        //             toastr.error('{{ $error }}', 'Error');
        //         @endforeach
        //     @endif
        // });

        document.querySelectorAll('.project-calc-total').forEach(function(input) {
            input.addEventListener('blur', function() {
                let project_total = 0;
                document.querySelectorAll('.project-calc-total').forEach(function(input) {
                    project_total += parseFloat(input.value) || 0;
                });
                document.getElementById('project_total').value = project_total;
            });
        });

        // document.querySelectorAll('.moveable_flow').forEach(function(input) {
        //     input.addEventListener('blur', function() {
        //         let moveable_total = 0;
        //         document.querySelectorAll('.moveable_flow').forEach(function(input) {
        //             moveable_total += parseFloat(input.value) || 0;
        //         });
        //         document.getElementById('moveable_total').value = moveable_total;
        //     });
        // });


        document.querySelectorAll('.collateral_focus').forEach(function(input) {
            input.addEventListener('input', function() {
                let collateral_focus_total = 0;
                document.querySelectorAll('.collateral_focus').forEach(function(input) {
                    collateral_focus_total += parseFloat(input.value) || 0;
                });
                document.getElementById('collateral_focus_total').textContent = collateral_focus_total;
            });
        });

        document.querySelectorAll('.primary_focus').forEach(function(input) {
            input.addEventListener('input', function() {
                let primary_focus_total = 0;
                document.querySelectorAll('.primary_focus').forEach(function(input) {
                    primary_focus_total += parseFloat(input.value) || 0;
                });
                document.getElementById('primary_focus_total').textContent = primary_focus_total;
            });
        });

        function calculateTotals() {
            var promotersContribution = parseFloat(document.getElementById('promoters_contribution').value) || 0;
            var debtMidc = parseFloat(document.getElementById('debt_midc').value) || 0;
            var debtOther = parseFloat(document.getElementById('debt_other').value) || 0;

            var promotersTotal = promotersContribution;
            var debtTotal = debtMidc + debtOther;
            var grandTotal = promotersTotal + debtTotal;

            document.getElementById('promoters_total').value = promotersTotal.toFixed(2);
            document.getElementById('debt_total').value = debtTotal.toFixed(2);
            document.getElementById('both_grand').value = grandTotal.toFixed(2);
        }

        window.onload = function() {
            document.getElementById('promoters_contribution').addEventListener('input', calculateTotals);
            document.getElementById('debt_midc').addEventListener('input', calculateTotals);
            document.getElementById('debt_other').addEventListener('input', calculateTotals);
        };

        function updatePermanentUnitAddress() {
            if ($('#colorCheck2').is(':checked')) {
                $('#permanentUnitAddress1').val($('#presentUnitAddress1').val());
                $('#permanentUnitAddress2').val($('#presentUnitAddress2').val());
                $('#permanentUnitPhone').val($('#presentUnitPhone').val());
            } else {
                $('#permanentUnitAddress1').val('');
                $('#permanentUnitAddress2').val('');
                $('#permanentUnitPhone').val('');
            }
        }
        $('#colorCheck2').on('change', updatePermanentUnitAddress);

        $('#presentUnitAddress1, #presentUnitAddress2, #presentUnitPhone')
            .on('change focusout', updatePermanentUnitAddress);

        $('#colorCheck2').trigger('change');


        function updatePermanentResiAddress() {
            if ($('#res').is(':checked')) {
                $('#permanentResiAddress1').val($('#presentResiAddress1').val());
                $('#permanentResiAddress2').val($('#presentResiAddress2').val());
                $('#permanentResiMobile').val($('#presentResiMobile').val());
                $('#permanentResiPhone').val($('#presentResiPhone').val());
            } else {
                $('#permanentResiAddress1').val('');
                $('#permanentResiAddress2').val('');
                $('#permanentResiMobile').val('');
                $('#permanentResiPhone').val('');
            }
        }
        $('#res').on('change', updatePermanentResiAddress);

        $('#presentResiAddress1, #presentResiAddress2, #presentResiMobile, #presentResiPhone')
            .on('change focusout', updatePermanentResiAddress);

        $('#res').trigger('change');



        function updateTotals() {
            let moveable_total = 0;
            let property_total = 0;
            let nw_total_net_worth = 0;

            document.querySelectorAll('.moveable_flow').forEach(function(input) {
                moveable_total += parseFloat(input.value) || 0;
            });

            const propertyInputs = document.querySelectorAll('#table-body-term-property .class_property_value');

            propertyInputs.forEach(function(input, index) {
                if (index !== 0) {
                    property_total += parseFloat(input.value) || 0;
                }
            });

            document.querySelectorAll('.nw_nt_wrth').forEach(function(input) {
                nw_total_net_worth += parseFloat(input.value) || 0;
            });

            document.getElementById('moveable_total').value = moveable_total;
            const total_asset_AB = moveable_total + property_total;
            document.getElementById('total_asset_AB').value = total_asset_AB;
            document.getElementById('nw_total_net_worth').value = nw_total_net_worth + total_asset_AB;
        }

        document.querySelectorAll('.moveable_flow, .class_property_value, .nw_nt_wrth').forEach(function(element) {
            element.addEventListener('blur', function() {
                updateTotals();
            });
        });

        function get_series_details(selectedValue = 0) {
            // if (selectedValue > 0) {
            //     var selectedSeries = selectedValue;
            // } else {
            //     var selectedSeries = document.getElementById("series").value;
            // }
            // $.ajax({
            //     url: '{{ url('get_voucher_no') }}/' + selectedSeries,
            //     type: 'GET',
            //     success: function(data) {
            //         if (data.type == "Auto") {
            //             $("#appli_no").attr("readonly", true);
            //             $('#appli_no').val(data.voucher_no);
            //         } else {
            //             $("#appli_no").attr("readonly", false);
            //         }
            //     }
            // });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var selectElement = document.getElementById('series');
            var selectedValue = selectElement.value;
            get_series_details(selectedValue);
        });

        // document.addEventListener('DOMContentLoaded', function() {
        //     const numberInputs = document.querySelectorAll('input[type="number"]');
        //     numberInputs.forEach(function(input) {
        //         input.addEventListener('input', function() {
        //             if (this.value < 0) {
        //                 this.value = Math.abs(this.value);
        //             }
        //         });

        //         input.addEventListener('blur', function() {
        //             if (this.value < 0) {
        //                 this.value = Math.abs(this.value);
        //             }
        //         });
        //     });
        // });

        document.addEventListener('DOMContentLoaded', function() {
            const numberInputs = document.querySelectorAll('input[type="number"]');

            function sanitizeInput(value) {
                let sanitized = value.replace(/-+/g, '');
                return sanitized.replace(/[^0-9]/g, '');
            }

            function updateValue(input) {
                let sanitizedValue = sanitizeInput(input.value);
                input.value = sanitizedValue;
            }

            numberInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    updateValue(this);
                });

                input.addEventListener('blur', function() {
                    updateValue(this);
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const textInputs = document.querySelectorAll('input[type="text"]');

            textInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    if (this.value.length > 250) {
                        alert(
                            'You have exceeded the 250 character limit. Extra characters will be removed.'
                            );
                        this.value = this.value.substring(0, 250);
                    }
                });

                input.addEventListener('blur', function() {
                    if (this.value.length > 250) {
                        alert(
                            'You have exceeded the 250 character limit. Extra characters will be removed.'
                            );
                        this.value = this.value.substring(0, 250);
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const textInputs = document.querySelectorAll('input[type="number"]');

            textInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    if (this.value.length > 11) {
                        alert(
                            'You have exceeded the 11 character limit. Extra characters will be removed.'
                            );
                        this.value = this.value.substring(0, 11);
                    }
                });

                input.addEventListener('blur', function() {
                    if (this.value.length > 11) {
                        alert(
                            'You have exceeded the 11 character limit. Extra characters will be removed.'
                            );
                        this.value = this.value.substring(0, 11);
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const textareas = document.querySelectorAll('textarea');

            textareas.forEach(function(textarea) {
                function enforceCharacterLimit() {
                    if (this.value.length > 500) {
                        alert(
                            'You have exceeded the 500 character limit. Extra characters will be removed.'
                            );
                        this.value = this.value.substring(0, 500);
                    }
                }

                textarea.addEventListener('input', enforceCharacterLimit);
                textarea.addEventListener('blur', enforceCharacterLimit);
            });
        });

        function fetchSeriesBased(series_id, id) {
            $.ajax({
                url: getvoucherUrl + '/' + series_id,
                method: 'GET',
                success: function(response) {
                    if (response.type == "Auto") {
                        $("#" + id).attr("readonly", true);
                        $("#" + id).val(response.voucher_no);
                    } else {
                        $("#" + id).attr("readonly", false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred while fetching the data.');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const pastDateInputs = document.querySelectorAll('.past-date');
            const futureDateInputs = document.querySelectorAll('.future-date');

            function disableDates() {
                const today = new Date().toISOString().split('T')[0];

                pastDateInputs.forEach(input => {
                    input.setAttribute('max', today);
                });

                futureDateInputs.forEach(input => {
                    input.setAttribute('min', today);
                });
            }
            disableDates();

            @isset($page)
                var page = @json($page);
            @else
                var page = null;
            @endisset

            if (page !== null && page == 'view_detail') {
                // Disabled / Readonly all fields
                // Make all input fields read-only
                document.querySelectorAll('input').forEach(function(input) {
                    input.readOnly = true; // Makes the input field read-only
                });

                // Disable all select fields
                document.querySelectorAll('select').forEach(function(select) {
                    select.disabled = true; // Disables the select field
                });

                // Disable all textarea fields
                document.querySelectorAll('textarea').forEach(function(select) {
                    select.disabled = true; // Disables the select field
                });

                // Disable all radio fields
                document.querySelectorAll('input[type="radio"]').forEach(function(select) {
                    select.disabled = true; // Disables the select field
                });

                // Disable all checkbox fields
                document.querySelectorAll('input[type="checkbox"]').forEach(function(select) {
                    select.disabled = true; // Disables the select field
                });

                // Disable all file fields
                document.querySelectorAll('input[type="file"]').forEach(function(select) {
                    select.disabled = true; // Disables the select field
                });
            }

            $(document).ready(function() {
                $('.remove-disable').removeAttr('disabled'); $('.remove-disable').removeAttr('readonly');
                $('.remove-disable').removeAttr('readonly')
            })
        });
    </script>
@endsection
