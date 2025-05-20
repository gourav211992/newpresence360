@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .appli-photobox {
            border: #c3c3c3 thin solid;
            padding: 3px;
            width: 150px;
            height: 180px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin-top: 10px;
            position: relative;
        }

        #uploadedImage,
        #uploadedGuaranImage,
        #uploadedImageco {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
            /* Hide the image by default */
        }

        #hide-size,
        #hide-size_guar,
        #hide-size_co {
            font-size: 14px;
            color: #555;
            margin: 0;
            position: absolute;
            /* Ensure text and image occupy the same space */
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            margin-left: -11px;
            margin-top: -2px;
            position: absolute;
            top: 70%;
            width: 0;
            padding-right: 1px;
        }
    </style>
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
                                    <button class="btn btn-outline-primary btn-sm submission_val" data-val="draft" form="home-loan-createUpdate"><i data-feather="save"></i>
                                        Save as
                                        Draft</button>
                                        <button class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-toggle="modal" data-bs-target="#deleteModal"><i data-feather="trash-2"></i> Delete</button>
                                @endif
                                @if ($buttons['submit'])
                                    <button data-bs-toggle="modal" data-bs-target="#disclaimer" data-val="submitted" class="btn btn-primary btn-sm submission_val" form="home-loan-createUpdate"><i
                                            data-feather="check-circle"></i> Proceed</button>
                                @endif
                                @if ($buttons['approve'])
                                    <button class="btn btn-danger btn-sm" data-bs-target="#reject" data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</button>
                                    <button data-bs-toggle="modal" data-bs-target="#approved" class="btn btn-success btn-sm"><i data-feather="check-circle"></i> Approve</button>
                                @endif
                                @endif
                            </div>
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
                    <form id="home-loan-createUpdate" method="POST" action="{{ route('loan.home-loan-createUpdate') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @if (isset($homeLoan->id))
                            <input type="hidden" name="edit_loanId" value="{{ $homeLoan->id }}">
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body customernewsection-form">

                                        <!-- Page Heading -->

                                        <div class="text-center new-applicayion-help-txt  mb-1 pb-25">
                                            <h4 class="purchase-head"><span>Application for Home Loan</span></h4>
                                            <h6 class="mt-2">({{ $homeLoan->name ?? 'N/A' }} |
                                                {{ $overview->term_loan ?? 'N/A' }} |
                                                {{ $overview->created_at->format('d-m-Y') ?? $homeLoan->created_at->format('d-m-Y') }})
                                            </h6>
                                        </div>

                                        <div class="bg-light-success rounded border p-1 mb-4">

                                            <div class="row">

                                                <div class="col-md-7">
                                                    @if ($homeLoan->approvalStatus != 'draft')
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
                                                                            $statuses[$homeLoan->approvalStatus] ??
                                                                            count($statuses);
                                                                    @endphp

                                                                    <ul class="nav nav-tabs mb-0 mt-25 custapploannav customrapplicationstatus"
                                                                        role="tablist">
                                                                        <li class="nav-item">
                                                                            <p
                                                                                class="{{ $currentStatusIndex >= 0 ? 'statusactive' : '' }}">
                                                                                <i data-feather="check"></i>
                                                                            </p>
                                                                            <a class="nav-link {{ $homeLoan->approvalStatus == 'appraisal' ? 'active' : '' }}"
                                                                                data-bs-toggle="tab" href="#Appraisal">
                                                                                Appraisal
                                                                            </a>
                                                                        </li>
                                                                        <li class="nav-item">
                                                                            <p
                                                                                class="{{ $currentStatusIndex >= 1 ? 'statusactive' : '' }}">
                                                                                <i data-feather="check"></i>
                                                                            </p>
                                                                            <a class="nav-link {{ $homeLoan->approvalStatus == 'assessment' ? 'active' : '' }}"
                                                                                href="#Assessmentschdule">
                                                                                Assessment
                                                                            </a>
                                                                        </li>
                                                                        <li class="nav-item">
                                                                            <p
                                                                                class="{{ $currentStatusIndex >= 2 ? 'statusactive' : '' }}">
                                                                                <i data-feather="check"></i>
                                                                            </p>
                                                                            <a class="nav-link {{ $homeLoan->approvalStatus == 'approved' ? 'active' : '' }}"
                                                                                href="#approval">
                                                                                Approval
                                                                            </a>
                                                                        </li>
                                                                        <li class="nav-item">
                                                                            <p
                                                                                class="{{ $currentStatusIndex >= 3 ? 'statusactive' : '' }}">
                                                                                <i data-feather="check"></i>
                                                                            </p>
                                                                            <a class="nav-link {{ $homeLoan->approvalStatus == 'sanctioned' ? 'active' : '' }}"
                                                                                href="#Sansactioned">
                                                                                Sant. Letter
                                                                            </a>
                                                                        </li>
                                                                        <li class="nav-item">
                                                                            <p
                                                                                class="{{ $currentStatusIndex >= 4 ? 'statusactive' : '' }}">
                                                                                <i data-feather="check"></i>
                                                                            </p>
                                                                            <a class="nav-link {{ $homeLoan->approvalStatus == 'processingfee' ? 'active' : '' }}"
                                                                                href="#Processing">
                                                                                Proc. Fee
                                                                            </a>
                                                                        </li>
                                                                        <li class="nav-item">
                                                                            <p
                                                                                class="{{ $currentStatusIndex >= 5 ? 'statusactive' : '' }}">
                                                                                <i data-feather="check"></i>
                                                                            </p>
                                                                            <a class="nav-link {{ $homeLoan->approvalStatus == 'legal docs' ? 'active' : '' }}"
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
                                                                                                <td>{{explode(' ',$overview->created_at)[0]}}</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>2</td>
                                                                                                <td>Name of Unit</td>
                                                                                                <td>{{$overview->unit_name}}</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>3</td>
                                                                                                <td>Name of Proprietor</td>
                                                                                                <td>{{$overview->proprietor_name}}</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>4</td>
                                                                                                <td>Address</td>
                                                                                                <td>{{$overview->address}}</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>5</td>
                                                                                                <td>CIBIL Score</td>
                                                                                                <td>{{$overview->cibil_score}}</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>6</td>
                                                                                                <td>Project Cost</td>
                                                                                                <td>{{$overview->project_cost}}</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>7</td>
                                                                                                <td>Term Loan</td>
                                                                                                <td>RS. {{$overview->term_loan}} LAKHS</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>8</td>
                                                                                                <td>Promotor's Contribution</td>
                                                                                                <td>RS. {{$overview->promotor_contribution}} LAKHS</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>9</td>
                                                                                                <td>Interest Rate (P.A)</td>
                                                                                                <td>{{$overview->interest_rate}} %</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>10</td>
                                                                                                <td>Loan Period</td>
                                                                                                <td>{{$overview->loan_period}}</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>11</td>
                                                                                                <td>Repayment Type</td>
                                                                                                <td>{{$overview->repayment_type}}</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>12</td>
                                                                                                <td>No. of Installment(s)</td>
                                                                                                <td>{{$overview->no_of_installments}}</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>13</td>
                                                                                                <td>Repayment Start After</td>
                                                                                                <td>{{$overview->repayment_start_after}}</td>
                                                                                            </tr>
                                                                                            <tr>
                                                                                                <td>14</td>
                                                                                                <td>Repayment Start Period</td>
                                                                                                <td>{{$overview->repayment_start_period}} Year</td>
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
                                                                                            @foreach ($overview->dpr as $key=> $dpr_list)
                                                                                            <tr>
                                                                                                <td>{{$key+1}}</td>
                                                                                                <td>{{$dpr_list->dpr->field_name}}</td>
                                                                                                <td>{{$dpr_list->dpr_value}}</td>
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
                                                                                            @foreach ($overview->disbursal as $key=> $disbursal_list)

                                                                                            <tr>
                                                                                                <td>{{$key+1}}</td>
                                                                                                <td>{{$overview->loan->loan_amount}}</td>
                                                                                                <td>{{$disbursal_list->milestone}}</td>
                                                                                                <td>{{$disbursal_list->amount}}</td>
                                                                                                <td>{{$disbursal_list->remarks}}</td>
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
                                                                                                $total_rep_amount=0;
                                                                                                $total_int_amount=0;
                                                                                            @endphp
                                                                                            @foreach ($overview->recovery as $key=> $recovery_list)
                                                                                            @php
                                                                                                $total_rep_amount+=$recovery_list->repayment_amount;
                                                                                                $total_int_amount+=$recovery_list->interest_amount;
                                                                                            @endphp

                                                                                            <tr>
                                                                                                <td>{{$key+1}}</td>
                                                                                                <td>{{$recovery_list->year}}</td>
                                                                                                <td>{{$recovery_list->start_amount}}</td>
                                                                                                <td>{{$recovery_list->interest_amount}}</td>
                                                                                                <td>{{$recovery_list->repayment_amount}}</td>
                                                                                                <td>{{$recovery_list->end_amount}}</td>
                                                                                            </tr>
                                                                                            @endforeach

                                                                                            <tr>
                                                                                                <td>&nbsp;</td>
                                                                                                <td>&nbsp;</td>
                                                                                                <td class="fw-bolder text-dark">
                                                                                                    Total</td>
                                                                                                <td class="fw-bolder text-dark">
                                                                                                    {{$total_int_amount}}</td>
                                                                                                <td class="fw-bolder text-dark">
                                                                                                    {{$total_rep_amount}}</td>
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
                                                                        <span
                                                                            class="input-group-text border-0 ps-0">
                                                                            <i data-feather="search"></i>
                                                                        </span>
                                                                        <input type="text"
                                                                            class="form-control border-0"
                                                                            id="email-search"
                                                                            placeholder="Search Doc"
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

                                        <!-- Home Loan Data -->
                                        <div class="row">

                                            <div class="col-md-9 order-2 order-sm-1">

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Loan Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="number" name="loan_amount"
                                                            value="{{ old('loan_amount', $homeLoan->loan_amount ?? '') }}"
                                                            class="form-control" min="0" />
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
                                                            value="{{ old('scheme_for', $homeLoan->scheme_for ?? '') }}"
                                                            class="form-control" />
                                                        @error('scheme_for')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    @php
                                                        $fullName = old('name', $homeLoan->name ?? '');
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
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Gender</label>
                                                    </div>
                                                    @php
                                                        $selectedGender = old('gender', $homeLoan->gender ?? 'male');
                                                        $selectedCast = old('cast', $homeLoan->cast ?? 'others');
                                                        $selectedMaritalStatus = old(
                                                            'marital_status',
                                                            $homeLoan->marital_status ?? 'married',
                                                        );
                                                    @endphp
                                                    <div class="col-md-5">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Male" name="gender"
                                                                    value="male" class="form-check-input"
                                                                    {{ $selectedGender === 'male' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Male">Male</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Female" name="gender"
                                                                    value="female" class="form-check-input"
                                                                    {{ $selectedGender === 'female' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Female">Female</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cast</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="SC" name="cast"
                                                                    value="sc" class="form-check-input"
                                                                    {{ $selectedCast === 'sc' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="SC">SC</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="ST" name="cast"
                                                                    value="st" class="form-check-input"
                                                                    {{ $selectedCast === 'st' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="ST">ST</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Others" name="cast"
                                                                    value="others" class="form-check-input"
                                                                    {{ $selectedCast === 'others' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Others">Others</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Marital Status</label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Single"
                                                                    name="marital_status" value="single"
                                                                    class="form-check-input"
                                                                    {{ $selectedMaritalStatus === 'single' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Single">Single</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Married"
                                                                    name="marital_status" value="married"
                                                                    class="form-check-input"
                                                                    {{ $selectedMaritalStatus === 'married' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Married">Married</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Divorced"
                                                                    name="marital_status" value="divorced"
                                                                    class="form-check-input"
                                                                    {{ $selectedMaritalStatus === 'divorced' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Divorced">Divorced</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Widowed"
                                                                    name="marital_status" value="widowed"
                                                                    class="form-check-input"
                                                                    {{ $selectedMaritalStatus === 'widowed' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Widowed">Widowed</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Father's/Mother Name</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="father_mother_name"
                                                            value="{{ old('father_mother_name', $homeLoan->father_mother_name ?? '') }}"
                                                            class="form-control" />
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">PAN/GIR No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="gir_no"
                                                            value="{{ old('gir_no', $homeLoan->gir_no ?? '') }}"
                                                            id="gir_no"
                                                            onblur='validatePanGir("#gir_no", "#gir_no_js", 10);'
                                                            class="form-control" min="0" />
                                                        <span id="gir_no_js" class="text-danger"></span>
                                                        @error('gir_no')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Date of Birth</label>
                                                    </div>
                                                    <div class="col-md-5 col-8">
                                                        <input type="date" name="dob" id="dob"
                                                            value="{{ old('dob', $homeLoan->dob ?? '') }}"
                                                            onblur="validateAge()" class="form-control past-date" />
                                                        <span id="age-message" class="text-danger"></span>
                                                        <span id="validate_age">
                                                            @error('dob')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </span>
                                                    </div>
                                                    <div class="col-md-2 col-4">
                                                        <input type="text" name="age"
                                                            value="{{ old('age', $homeLoan->age ?? '') }}" id="age"
                                                            class="form-control" placeholder="Age" readonly />
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
                                                            <input type="email" name="email" id="email_no"
                                                                value="{{ old('email', $homeLoan->email ?? '') }}"
                                                                class="form-control" placeholder="">
                                                            <span id="email_js" class="text-danger"></span>
                                                            @error('email')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Mobile <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="input-group input-group-merge">
                                                            <span class="input-group-text" id="basic-addon5"><i
                                                                    data-feather='smartphone'></i></span>
                                                            <input type="number" name="mobile" id="mobile_no"
                                                                value="{{ old('mobile', $homeLoan->mobile ?? '') }}"
                                                                onblur='validateLength("#mobile_no", "#mobile_js", 10);'
                                                                class="form-control" placeholder="Mobile" min="0">
                                                            @error('mobile')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                        <span id="mobile_js" class="text-danger"></span>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1 additional_data">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Spouse Name</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="spouse_name"
                                                            value="{{ old('spouse_name', $homeLoan->spouse_name ?? '') }}"
                                                            class="form-control" />
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">No. of Dependents (Excluding
                                                            Parents)<span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="no_of_depends">
                                                            <option value="">Select</option>
                                                            <option value="1"
                                                                {{ old('no_of_depends', isset($homeLoan->no_of_depends) ? $homeLoan->no_of_depends : '') == 1 ? 'selected' : '' }}>
                                                                1</option>
                                                            <option value="2"
                                                                {{ old('no_of_depends', isset($homeLoan->no_of_depends) ? $homeLoan->no_of_depends : '') == 2 ? 'selected' : '' }}>
                                                                2</option>
                                                            <option value="3"
                                                                {{ old('no_of_depends', isset($homeLoan->no_of_depends) ? $homeLoan->no_of_depends : '') == 3 ? 'selected' : '' }}>
                                                                3</option>
                                                        </select>
                                                        @error('no_of_depends')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1 additional_data">
                                                    <div class="col-md-3">
                                                        <label class="form-label">No. of Children</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="no_of_children">
                                                            <option value="0">Select</option>
                                                            <option value="1"
                                                                {{ old('no_of_children', isset($homeLoan->no_of_children) ? $homeLoan->no_of_children : '') == 1 ? 'selected' : '' }}>
                                                                1</option>
                                                            <option value="2"
                                                                {{ old('no_of_children', isset($homeLoan->no_of_children) ? $homeLoan->no_of_children : '') == 2 ? 'selected' : '' }}>
                                                                2</option>
                                                            <option value="3"
                                                                {{ old('no_of_children', isset($homeLoan->no_of_children) ? $homeLoan->no_of_children : '') == 3 ? 'selected' : '' }}>
                                                                3</option>
                                                            <option value="4"
                                                                {{ old('no_of_children', isset($homeLoan->no_of_children) ? $homeLoan->no_of_children : '') == 4 ? 'selected' : '' }}>
                                                                4</option>
                                                            <option value="5"
                                                                {{ old('no_of_children', isset($homeLoan->no_of_children) ? $homeLoan->no_of_children : '') == 5 ? 'selected' : '' }}>
                                                                5</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                @php
                                                    $selectedEarningMember = old(
                                                        'earning_member',
                                                        $homeLoan->earning_member ?? '0',
                                                    );
                                                @endphp
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Earning Member in Family <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Single"
                                                                    name="earning_member" value="1"
                                                                    class="form-check-input"
                                                                    {{ $selectedEarningMember === '1' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Single">Yes</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Married"
                                                                    name="earning_member" value="0"
                                                                    class="form-check-input"
                                                                    {{ $selectedEarningMember === '0' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Married">No</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3 order-1 order-sm-2 border-start mb-2">
                                                <div>
                                                    @if (isset($homeLoan) && !empty($homeLoan->image))
                                                        <div class="appli-photobox">
                                                            <img id="uploadedImage"
                                                                src="{{ asset('storage/' . $homeLoan->image) }}"
                                                                alt="Uploaded Image" style="display: block;" />
                                                        </div>
                                                    @else
                                                        <div class="appli-photobox">
                                                            <p id="hide-size">Photo Size<br />25mm X 35mm</p>
                                                            <img id="uploadedImage" />
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="mt-2 text-center">
                                                    <div class="image-uploadhide">
                                                        <a href="attribute.html"
                                                            class="btn btn-outline-primary btn-sm waves-effect"> <i
                                                                data-feather="upload"></i> Upload Customer Image</a>
                                                        <input type="hidden" name="stored_image"
                                                            value="{{ old('image', $homeLoan->image ?? '') }}">
                                                        <input type="file" name="image"
                                                            value="{{ old('image', $homeLoan->image ?? '') }}"
                                                            class="" onchange="previewImage(event)">
                                                    </div>

                                                </div>

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
                                                            href="#Employer">Employer Detail</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#Bank">Bank
                                                            Account</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#Income">Loan and
                                                            Income</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#Details">Other
                                                            Details</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab"
                                                            href="#Proposed">Proposed Loan
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" id="guarantor_tab"
                                                            href="#GuarantApp">Guarantor</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link hide" data-bs-toggle="tab" id="co_appli_tab"
                                                            href="#GuarantorCoOther">Co-applicant</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab"
                                                            href="#Documentsupload">Documents</a>
                                                    </li>

                                                </ul>

                                            </div>

                                            <div class="tab-content pb-1 px-1">
                                                <div class="tab-pane" id="Details">


                                                    <div class="row">
                                                        @php
                                                            $selectedGuarantorCo = old(
                                                                'OtherDetail.common_data.type',
                                                                $homeLoan->otherDetails->type ?? '1',
                                                            );
                                                            $selectedGuarantor = old(
                                                                'OtherDetail.common_data.type',
                                                                $homeLoan->loanOtherGuarantors->type ?? '1',
                                                            );
                                                        @endphp
                                                        <div class="col-md-6" id="guara">

                                                            <h5 class="mt-1 mb-2  text-dark"><strong>Guarantor
                                                                    Detail</strong></h5>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Guarantor <span
                                                                            class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <div class="demo-inline-spacing">
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="radio" id="Guarantor"
                                                                                name="OtherDetail[common_data][guar_type]"
                                                                                value="1" class="form-check-input"
                                                                                {{ $selectedGuarantor == '1' ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-bolder"
                                                                                for="Guarantor">Yes</label>
                                                                        </div>
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="radio" id="applicant"
                                                                                name="OtherDetail[common_data][guar_type]"
                                                                                value="0" class="form-check-input"
                                                                                {{ $selectedGuarantor == '0' ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-bolder"
                                                                                for="applicant">No</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hid_g_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Name <span
                                                                            class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="OtherDetail[common_data][guar_name]"
                                                                        value="{{ old('OtherDetail.common_data.guar_name', $homeLoan->loanOtherGuarantors->name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                                @error('OtherDetail.common_data.guar_name')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </div>

                                                            <div class="row align-items-center mb-1 hid_g_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Date of Birth <span
                                                                            class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="date"
                                                                        name="OtherDetail[common_data][guar_dob]"
                                                                        id="dob1"
                                                                        value="{{ old('OtherDetail.common_data.guar_dob', $homeLoan->loanOtherGuarantors->dob ?? '') }}"
                                                                        onblur="validateAgeData(this)"
                                                                        class="form-control past-date" />
                                                                </div>
                                                                @error('OtherDetail.common_data.guar_dob')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                                <span class="age-message" id="message-dob1"
                                                                    class="text-danger"></span>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hid_g_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Father's/Mother Name</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="OtherDetail[common_data][guar_fm_name]"
                                                                        value="{{ old('OtherDetail.common_data.guar_fm_name', $homeLoan->loanOtherGuarantors->fm_name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hid_g_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Relationship with
                                                                        Applicant</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="OtherDetail[common_data][guar_applicant_relation]"
                                                                        value="{{ old('OtherDetail.common_data.guar_applicant_relation', $homeLoan->loanOtherGuarantors->applicant_relation ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row mb-1 hid_g_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Address <span
                                                                            class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" name="OtherDetail[common_data][guar_address]" placeholder="Street 1">{{ old('OtherDetail.common_data.guar_address', $homeLoan->loanOtherGuarantors->address ?? '') }}</textarea>
                                                                </div>
                                                                @error('OtherDetail.common_data.guar_address')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </div>

                                                            <div class="row align-items-center mb-1 hid_g_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">City</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <!-- <input type="text" name="OtherDetail[common_data][city]" value="{{ old('OtherDetail.common_data.city', $homeLoan->otherDetails->city ?? '') }}" class="form-control"  /> -->
                                                                    <select class="form-select"
                                                                        name="OtherDetail[common_data][guar_city]"
                                                                        id="guar-city-select"></select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hid_g_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">State</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <select class="form-select"
                                                                        name="OtherDetail[common_data][guar_state]"
                                                                        id="guar-state-select"></select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hid_g_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Pin Code</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="OtherDetail[common_data][guar_pin_code]"
                                                                        value="{{ old('OtherDetail.common_data.guar_pin_code', $homeLoan->loanOtherGuarantors->pin_code ?? '') }}"
                                                                        class="form-control" id="guar_pin_code"
                                                                        placeholder="Enter PIN code (6 digits)"
                                                                        onblur="validatePinCode(this)" />
                                                                </div>
                                                                @error('OtherDetail.common_data.guar_pin_code')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </div>

                                                            <div class="row align-items-center mb-1 hid_g_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Occupation</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <select class="form-select"
                                                                        name="OtherDetail[common_data][guar_occupation]">
                                                                        <option value="">Select</option>
                                                                        @if (isset($occupation))
                                                                            @foreach ($occupation as $key => $val)
                                                                                <option value="{{ $val->id }}"
                                                                                    {{ old('OtherDetail.common_data.guar_occupation', $homeLoan->loanOtherGuarantors->occupation ?? '') == $val->id ? 'selected' : '' }}>
                                                                                    {{ $val->name }}</option>
                                                                            @endforeach
                                                                        @endif

                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hid_g_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Phone/Fax <span
                                                                            class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="OtherDetail[common_data][guar_phn_fax]"
                                                                        value="{{ old('OtherDetail.common_data.guar_phn_fax', $homeLoan->loanOtherGuarantors->phn_fax ?? '') }}"
                                                                        class="form-control" id="guar_phn_fax_oth"
                                                                        onblur='validateLength("#guar_phn_fax_oth", "#guar_phn_fax_oth_js", 10);' />
                                                                </div>
                                                                <span id="guar_phn_fax_oth_js" class="text-danger"></span>
                                                                @error('OtherDetail.common_data.guar_phn_fax')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </div>

                                                            <div class="row align-items-center mb-1 hid_g_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Email</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="email"
                                                                        name="OtherDetail[common_data][guar_email]"
                                                                        value="{{ old('OtherDetail.common_data.guar_email', $homeLoan->loanOtherGuarantors->email ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                                @error('OtherDetail.common_data.guar_email')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </div>

                                                            <div class="row align-items-center mb-1 hid_g_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">PAN/GIR No. <span
                                                                            class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="OtherDetail[common_data][guar_pan_gir_no]"
                                                                        id="guar_pan_gir_no"
                                                                        onblur='validatePanGir("#guar_pan_gir_no", "#guar_pan_gir_no_js", 10);'
                                                                        value="{{ old('OtherDetail.common_data.guar_pan_gir_no', $homeLoan->loanOtherGuarantors->pan_gir_no ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                                <span id="guar_pan_gir_no_js" class="text-danger"></span>
                                                                @error('OtherDetail.common_data.guar_pan_gir_no')
                                                                    <span class="text-danger">{{ $message }}</span>
                                                                @enderror
                                                            </div>

                                                            <div class="row align-items-center mb-1 hid_g_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Net Annual Income</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="OtherDetail[common_data][guar_net_annu_income]"
                                                                        value="{{ old('OtherDetail.common_data.guar_net_annu_income', $homeLoan->loanOtherGuarantors->net_annu_income ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                        </div>

                                                        <div class="col-md-6" id="coo-present">

                                                            <h5 class="mt-1 mb-2  text-dark"><strong>Co-applicant Detail
                                                                    (if present)</strong></h5>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Co-applicant <span
                                                                            class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <div class="demo-inline-spacing">
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="radio" id="GuarantorYes"
                                                                                name="OtherDetail[common_data][co_type]"
                                                                                value="1" class="form-check-input"
                                                                                {{ $selectedGuarantorCo == '1' ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-bolder"
                                                                                for="GuarantorYes">Yes</label>
                                                                        </div>
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="radio" id="applicantNo"
                                                                                name="OtherDetail[common_data][co_type]"
                                                                                value="0" class="form-check-input"
                                                                                {{ $selectedGuarantorCo == '0' ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-bolder"
                                                                                for="applicantNo">No</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <div class="row align-items-center mb-1 hide_co_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Name</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="OtherDetail[common_data][co_name]"
                                                                        value="{{ old('OtherDetail.common_data.co_name', $homeLoan->otherDetails->name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hide_co_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Date of Birth</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="date"
                                                                        name="OtherDetail[common_data][co_dob]"
                                                                        id="dob2"
                                                                        value="{{ old('OtherDetail.common_data.co_dob', $homeLoan->otherDetails->dob ?? '') }}"
                                                                        onblur="validateAgeData(this)"
                                                                        class="form-control past-date" />
                                                                    @error('OtherDetail.common_data.co_dob')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                    <span class="age-message" id="message-dob2"
                                                                        class="text-danger"></span>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hide_co_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Father's/Mother Name</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="OtherDetail[common_data][co_fm_name]"
                                                                        value="{{ old('OtherDetail.common_data.co_fm_name', $homeLoan->otherDetails->fm_name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hide_co_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Relationship with
                                                                        Applicant</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="OtherDetail[common_data][co_applicant_relation]"
                                                                        value="{{ old('OtherDetail.common_data.co_applicant_relation', $homeLoan->otherDetails->applicant_relation ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row mb-1 hide_co_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Address</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" name="OtherDetail[common_data][co_address]" placeholder="Street 1">{{ old('OtherDetail.common_data.co_address', $homeLoan->otherDetails->address ?? '') }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hide_co_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">City</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <!-- <input type="text" name="OtherDetail[common_data][co_city]" value="{{ old('OtherDetail.common_data.co_city', $homeLoan->otherDetails->city ?? '') }}" class="form-control"  /> -->
                                                                    <select class="form-select"
                                                                        name="OtherDetail[common_data][co_city]"
                                                                        id="other-city-select"></select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hide_co_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">State</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <!-- <select class="form-select" name="OtherDetail[common_data][co_state]">
                                                                                                                                    <option value="">Select</option>
                                                                                                                                    <option value="1" {{ old('OtherDetail.common_data.co_state', isset($homeLoan->otherDetails->state) ? $homeLoan->otherDetails->state : '') == 1 ? 'selected' : '' }}>1</option>
                                                                                                                                    <option value="2" {{ old('OtherDetail.common_data.co_state', isset($homeLoan->otherDetails->state) ? $homeLoan->otherDetails->state : '') == 2 ? 'selected' : '' }}>2</option>
                                                                                                                                </select>  -->
                                                                    <select class="form-select"
                                                                        name="OtherDetail[common_data][co_state]"
                                                                        id="other-state-select"></select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hide_co_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Pin Code</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="OtherDetail[common_data][co_pin_code]"
                                                                        value="{{ old('OtherDetail.common_data.co_pin_code', $homeLoan->otherDetails->pin_code ?? '') }}"
                                                                        class="form-control" id="co_pin_code"
                                                                        placeholder="Enter PIN code (6 digits)"
                                                                        onblur="validatePinCode(this)" />
                                                                    @error('OtherDetail.common_data.co_pin_code')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hide_co_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Occupation</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <select class="form-select"
                                                                        name="OtherDetail[common_data][co_occupation]">
                                                                        <option value="">Select</option>
                                                                        @if (isset($occupation))
                                                                            @foreach ($occupation as $key => $val)
                                                                                <option value="{{ $val->id }}"
                                                                                    {{ old('OtherDetail.common_data.co_occupation', $homeLoan->otherDetails->occupation ?? '') == $val->id ? 'selected' : '' }}>
                                                                                    {{ $val->name }}</option>
                                                                            @endforeach
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hide_co_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Phone/Fax</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="OtherDetail[common_data][co_phn_fax]"
                                                                        value="{{ old('OtherDetail.common_data.co_phn_fax', $homeLoan->otherDetails->phn_fax ?? '') }}"
                                                                        class="form-control" id="co_phn_fax_guar"
                                                                        onblur='validateLength("#co_phn_fax_guar", "#co_phn_fax_guar_js", 10);' />
                                                                    <span id="co_phn_fax_guar_js"
                                                                        class="text-danger"></span>
                                                                    @error('OtherDetail.common_data.co_phn_fax')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hide_co_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Email</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="email"
                                                                        name="OtherDetail[common_data][co_email]"
                                                                        value="{{ old('OtherDetail.common_data.co_email', $homeLoan->otherDetails->email ?? '') }}"
                                                                        class="form-control" />
                                                                    @error('OtherDetail.common_data.co_email')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 hide_co_field">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">PAN/GIR No.</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="OtherDetail[common_data][co_pan_gir_no]"
                                                                        id="co_pan_gir_no"
                                                                        onblur='validatePanGir("#co_pan_gir_no", "#co_pan_gir_no_js", 10);'
                                                                        value="{{ old('OtherDetail.common_data.co_pan_gir_no', $homeLoan->otherDetails->pan_gir_no ?? '') }}"
                                                                        class="form-control" />
                                                                    <span id="co_pan_gir_no_js"
                                                                        class="text-danger"></span>
                                                                    @error('OtherDetail.common_data.co_pan_gir_no')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="tab-pane active" id="Address">
                                                    <div class="row">
                                                        <div class="col-md-6">

                                                            <h5 class="mt-1 mb-4 text-dark"><strong>Current
                                                                    Address</strong></h5>

                                                            <div class="row mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Address</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" name="Address[address1]" id="currentAddress1" placeholder="Street 1">{{ old('Address.address1', $homeLoan->addresses->address1 ?? '') }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    &nbsp;
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" name="Address[address2]" id="currentAddress2" placeholder="Street 2">{{ old('Address.address2', $homeLoan->addresses->address2 ?? '') }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">City</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <!-- <input type="text" name="Address[city]" value="{{ old('Address.city', $homeLoan->addresses->city ?? '') }}" id="currentCity" class="form-control"  /> -->
                                                                    <select class="form-select" name="Address[city]"
                                                                        id="city-select"></select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">State</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <select class="form-select" name="Address[state]"
                                                                        id="state-select">
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Pin Code</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" name="Address[pin_code]"
                                                                        value="{{ old('Address.pin_code', $homeLoan->addresses->pin_code ?? '') }}"
                                                                        id="currentPinCode" class="form-control"
                                                                        placeholder="Enter PIN code (6 digits)"
                                                                        onblur="validatePinCode(this)" />
                                                                    @error('Address.pin_code')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Years in Current
                                                                        Address</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <select class="form-select"
                                                                        name="Address[years_current_addr]">
                                                                        <option value="">Select</option>
                                                                        <option value="1"
                                                                            {{ old('Address.years_current_addr', isset($homeLoan->addresses->years_current_addr) ? $homeLoan->addresses->years_current_addr : '') == 1 ? 'selected' : '' }}>
                                                                            1</option>
                                                                        <option value="3"
                                                                            {{ old('Address.years_current_addr', isset($homeLoan->addresses->years_current_addr) ? $homeLoan->addresses->years_current_addr : '') == 3 ? 'selected' : '' }}>
                                                                            3</option>
                                                                        <option value="5"
                                                                            {{ old('Address.years_current_addr', isset($homeLoan->addresses->years_current_addr) ? $homeLoan->addresses->years_current_addr : '') == 5 ? 'selected' : '' }}>
                                                                            5</option>
                                                                        <option value="7"
                                                                            {{ old('Address.years_current_addr', isset($homeLoan->addresses->years_current_addr) ? $homeLoan->addresses->years_current_addr : '') == 7 ? 'selected' : '' }}>
                                                                            7</option>
                                                                        <option value="10"
                                                                            {{ old('Address.years_current_addr', isset($homeLoan->addresses->years_current_addr) ? $homeLoan->addresses->years_current_addr : '') == 10 ? 'selected' : '' }}>
                                                                            10</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Residence Phone</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" name="Address[residence_phn]"
                                                                        value="{{ old('Address.residence_phn', $homeLoan->addresses->residence_phn ?? '') }}"
                                                                        id="currentResidencePhn" class="form-control"
                                                                        onblur='validateLength("#currentResidencePhn", "#resi_js", 10);' />
                                                                    <span id="resi_js" class="text-danger"></span>
                                                                    @error('Address.residence_phn')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Office Phone</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" name="Address[office_phn]"
                                                                        value="{{ old('Address.office_phn', $homeLoan->addresses->office_phn ?? '') }}"
                                                                        id="add_phn" class="form-control"
                                                                        onblur='validateLength("#add_phn", "#add_phn_js", 10);' />
                                                                    <span id="add_phn_js" class="text-danger"></span>
                                                                    @error('Address.office_phn')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Fax Number</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" name="Address[fax_num]"
                                                                        value="{{ old('Address.fax_num', $homeLoan->addresses->fax_num ?? '') }}"
                                                                        id="addd_fax_num" class="form-control"
                                                                        onblur='validateLength("#addd_fax_num", "#addd_fax_num_js", 10);' />
                                                                    <span id="addd_fax_num_js" class="text-danger"></span>
                                                                    @error('Address.fax_num')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>


                                                        </div>

                                                        <div class="col-md-6">

                                                            <div class="mt-1 mb-2 d-flex flex-column">
                                                                <h5 class="text-dark mb-0 me-1"><strong>Permanent
                                                                        Address</strong></h5>
                                                                @php
                                                                    $p_addr1 = '';
                                                                    $p_addr2 = '';
                                                                    $p_city = '';
                                                                    $p_state = '';
                                                                    $p_pin_code = '';
                                                                    $p_resid_phn = '';
                                                                    $checked = 'checked';
                                                                    if (
                                                                        isset($homeLoan->addresses->same_as) &&
                                                                        $homeLoan->addresses->same_as == 1
                                                                    ) {
                                                                        $p_addr1 = $homeLoan->addresses->address1;
                                                                        $p_addr2 = $homeLoan->addresses->address2;
                                                                        $p_city = $homeLoan->addresses->city;
                                                                        $p_state = $homeLoan->addresses->state;
                                                                        $p_pin_code = $homeLoan->addresses->pin_code;
                                                                        $p_resid_phn =
                                                                            $homeLoan->addresses->residence_phn;
                                                                    } else {
                                                                        $checked = '';
                                                                    }
                                                                @endphp
                                                                <div
                                                                    class="form-check form-check-primary mt-25 custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        id="colorCheck2" name="Address[same_as]"
                                                                        {{ $checked }}>

                                                                    <label class="form-check-label"
                                                                        for="colorCheck2">Same
                                                                        As Current Address</label>
                                                                </div>
                                                            </div>


                                                            <div class="row mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Address</label>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    @if (isset($homeLoan->addresses) && !empty($homeLoan->addresses->p_address1))
                                                                        <textarea class="form-control" name="Address[p_address1]" id="permanentAddress1" placeholder="Street 1">{{ $homeLoan->addresses->p_address1 ?? '' }}</textarea>
                                                                    @else
                                                                        <textarea class="form-control" name="Address[p_address1]" id="permanentAddress1" placeholder="Street 1">{{ $p_addr1 ?? '' }}</textarea>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    &nbsp;
                                                                </div>

                                                                <div class="col-md-6">
                                                                    @if (isset($homeLoan->addresses) && !empty($homeLoan->addresses->p_address2))
                                                                        <textarea class="form-control" name="Address[p_address2]" id="permanentAddress2" placeholder="Street 1">{{ $homeLoan->addresses->p_address2 ?? '' }}</textarea>
                                                                    @else
                                                                        <textarea class="form-control" name="Address[p_address2]" id="permanentAddress2" placeholder="Street 2">{{ $p_addr2 ?? '' }}</textarea>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">City</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <!-- <input type="text" id="permanentCity" value="{{ $p_city ?? '' }}" class="form-control"  /> -->
                                                                    <select class="form-select" name="Address[p_city]"
                                                                        id="p-city-select"></select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">State</label>
                                                                </div>

                                                                <div class="col-md-6">

                                                                    <!-- <input type="text" id="permanentState" value="{{ $p_state ?? '' }}" class="form-control"  /> -->
                                                                    <select class="form-select" name="Address[p_state]"
                                                                        id="p-state-select"></select>

                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Pin Code</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    @if (isset($homeLoan->addresses) && !empty($homeLoan->addresses->p_pin))
                                                                        <input type="text" name="Address[p_pin]"
                                                                            id="permanentPinCode"
                                                                            value="{{ $homeLoan->addresses->p_pin ?? '' }}"
                                                                            class="form-control"
                                                                            placeholder="Enter PIN code (6 digits)"
                                                                            onblur="validatePinCode(this)" />
                                                                    @else
                                                                        <input type="text" name="Address[p_pin]"
                                                                            id="permanentPinCode"
                                                                            value="{{ $p_pin_code ?? '' }}"
                                                                            class="form-control"
                                                                            placeholder="Enter PIN code (6 digits)"
                                                                            onblur="validatePinCode(this)" />
                                                                    @endif
                                                                    @error('Address.p_pin')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Residence Phone</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    @if (isset($homeLoan->addresses) && !empty($homeLoan->addresses->p_resi_code))
                                                                        <input type="number"
                                                                            name="Address[p_resi_code]"
                                                                            id="permanentResidencePhn"
                                                                            value="{{ $homeLoan->addresses->p_resi_code ?? '' }}"
                                                                            class="form-control" />
                                                                    @else
                                                                        <input type="number"
                                                                            name="Address[p_resi_code]"
                                                                            value="{{ $p_resid_phn ?? '' }}"
                                                                            id="permanentResidencePhn"
                                                                            class="form-control"
                                                                            onblur='validateLength("#permanentResidencePhn", "#permanentResidencePhn_js", 10);' />
                                                                    @endif
                                                                    <span id="permanentResidencePhn_js"
                                                                        class="text-danger"></span>
                                                                    @error('Address.p_resi_code')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="Employer">
                                                    <div class="row">
                                                        <div class="col-md-6">

                                                            <h5 class="mt-1 mb-2 text-dark"><strong>Basic Info</strong>
                                                            </h5>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Employer Name</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="EmployerDetail[employer_name]"
                                                                        value="{{ old('EmployerDetail.employer_name', $homeLoan->employerDetails->employer_name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Department</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="EmployerDetail[department]"
                                                                        value="{{ old('EmployerDetail.department', $homeLoan->employerDetails->department ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Address</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" name="EmployerDetail[address]" placeholder="Street 1">{{ old('EmployerDetail.address', $homeLoan->employerDetails->address ?? '') }}</textarea>
                                                                </div>
                                                            </div>


                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">City</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <!-- <input type="text" name="EmployerDetail[city]" value="{{ old('EmployerDetail.city', $homeLoan->employerDetails->city ?? '') }}" class="form-control"  /> -->
                                                                    <select class="form-select"
                                                                        name="EmployerDetail[city]"
                                                                        id="employer-city-select"></select>

                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">State</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <select class="form-select"
                                                                        name="EmployerDetail[state]"
                                                                        id="employer-state-select"></select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Pin Code</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="EmployerDetail[pin_code]"
                                                                        value="{{ old('EmployerDetail.pin_code', $homeLoan->employerDetails->pin_code ?? '') }}"
                                                                        id="emp_pin_code" class="form-control"
                                                                        placeholder="Enter PIN code (6 digits)"
                                                                        onblur="validatePinCode(this)" />
                                                                    @error('EmployerDetail.pin_code')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Phone No.</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" name="EmployerDetail[phn_no]"
                                                                        value="{{ old('EmployerDetail.phn_no', $homeLoan->employerDetails->phn_no ?? '') }}"
                                                                        id="EmployerDetail_id" class="form-control"
                                                                        onblur='validateLength("#EmployerDetail_id", "#EmployerDetail_id_js", 10);' />
                                                                    <span id="EmployerDetail_id_js"
                                                                        class="text-danger"></span>
                                                                    @error('EmployerDetail.phn_no')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>

                                                                <div class="col-md-2">
                                                                    <input type="number" name="EmployerDetail[ext_no]"
                                                                        value="{{ old('EmployerDetail.ext_no', $homeLoan->employerDetails->ext_no ?? '') }}"
                                                                        class="form-control" placeholder="Extn No." />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Fax Number</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" name="EmployerDetail[fax_num]"
                                                                        value="{{ old('EmployerDetail.fax_num', $homeLoan->employerDetails->fax_num ?? '') }}"
                                                                        class="form-control" id="EmployerDetail_fax_num"
                                                                        onblur='validateLength("#EmployerDetail_fax_num", "#EmployerDetail_fax_num_js", 10);' />
                                                                    <span id="EmployerDetail_fax_num_js"
                                                                        class="text-danger"></span>
                                                                    @error('EmployerDetail.fax_num')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>


                                                        </div>
                                                        <div class="col-md-6">
                                                            <h5 class="mt-1 mb-2 text-dark"><strong>Other Info</strong>
                                                            </h5>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Company Email</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="email"
                                                                        name="EmployerDetail[company_email]"
                                                                        value="{{ old('EmployerDetail.company_email', $homeLoan->employerDetails->company_email ?? '') }}"
                                                                        class="form-control" />
                                                                    @error('EmployerDetail.company_email')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Designation</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <select class="form-select"
                                                                        name="EmployerDetail[designation]">
                                                                        <option value="">Select</option>
                                                                        <option value="executive"
                                                                            {{ old('EmployerDetail.designation', isset($homeLoan->employerDetails->designation) ? $homeLoan->employerDetails->designation : '') == 'executive' ? 'selected' : '' }}>
                                                                            Executive</option>
                                                                        <option value="managerial"
                                                                            {{ old('EmployerDetail.designation', isset($homeLoan->employerDetails->designation) ? $homeLoan->employerDetails->designation : '') == 'managerial' ? 'selected' : '' }}>
                                                                            Managerial</option>
                                                                        <option value="clerk"
                                                                            {{ old('EmployerDetail.designation', isset($homeLoan->employerDetails->designation) ? $homeLoan->employerDetails->designation : '') == 'clerk' ? 'selected' : '' }}>
                                                                            Clerk</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Years with Employers</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="EmployerDetail[years_with_employers]"
                                                                        value="{{ old('EmployerDetail.years_with_employers', $homeLoan->employerDetails->years_with_employers ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Contact Person</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="EmployerDetail[contact_person]"
                                                                        value="{{ old('EmployerDetail.contact_person', $homeLoan->employerDetails->contact_person ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Name of Previous
                                                                        Employer</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="EmployerDetail[previous_employer]"
                                                                        value="{{ old('EmployerDetail.previous_employer', $homeLoan->employerDetails->previous_employer ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Retirement Age</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="EmployerDetail[retirement_age]"
                                                                        value="{{ old('EmployerDetail.retirement_age', $homeLoan->employerDetails->retirement_age ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>



                                                        </div>

                                                    </div>

                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>Other
                                                            Assets</strong></p>
                                                    @php
                                                        $oldAssets = [];
                                                        if (isset($homeLoan) && isset($homeLoan->employerDetails)) {
                                                            $otherAssets = json_decode(
                                                                $homeLoan->employerDetails->other_assets,
                                                                true,
                                                            );
                                                            $oldAssets = old(
                                                                'EmployerDetail.other_assets',
                                                                $otherAssets ?? [],
                                                            );
                                                        } else {
                                                            $oldAssets = old('EmployerDetail.other_assets', []);
                                                        }
                                                    @endphp
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div
                                                                        class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="checkbox"
                                                                            name="EmployerDetail[other_assets][]"
                                                                            value="color_tv" class="form-check-input"
                                                                            id="Color"
                                                                            {{ in_array('color_tv', $oldAssets) ? 'checked' : '' }}>
                                                                        <label class="form-check-label"
                                                                            for="Color">Color TV</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div
                                                                        class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="checkbox"
                                                                            name="EmployerDetail[other_assets][]"
                                                                            value="telephone" class="form-check-input"
                                                                            id="Telephone"
                                                                            {{ in_array('telephone', $oldAssets) ? 'checked' : '' }}>
                                                                        <label class="form-check-label"
                                                                            for="Telephone">Telephone</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div
                                                                        class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="checkbox"
                                                                            name="EmployerDetail[other_assets][]"
                                                                            value="refrigerator"
                                                                            class="form-check-input" id="Refrigerator"
                                                                            {{ in_array('refrigerator', $oldAssets) ? 'checked' : '' }}>
                                                                        <label class="form-check-label"
                                                                            for="Refrigerator">Refrigerator</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div
                                                                        class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="checkbox"
                                                                            name="EmployerDetail[other_assets][]"
                                                                            value="cellular_phone"
                                                                            class="form-check-input" id="Cellular"
                                                                            {{ in_array('cellular_phone', $oldAssets) ? 'checked' : '' }}>
                                                                        <label class="form-check-label"
                                                                            for="Cellular">Cellular Phone</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div
                                                                        class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="checkbox"
                                                                            name="EmployerDetail[other_assets][]"
                                                                            value="car" class="form-check-input"
                                                                            id="Car"
                                                                            {{ in_array('car', $oldAssets) ? 'checked' : '' }}>
                                                                        <label class="form-check-label"
                                                                            for="Car">Car</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div
                                                                        class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="checkbox"
                                                                            name="EmployerDetail[other_assets][]"
                                                                            value="personal_computer"
                                                                            class="form-check-input" id="Personal"
                                                                            {{ in_array('personal_computer', $oldAssets) ? 'checked' : '' }}>
                                                                        <label class="form-check-label"
                                                                            for="WhatsPersonalapp">Personal
                                                                            Computer</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div
                                                                        class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="checkbox"
                                                                            name="EmployerDetail[other_assets][]"
                                                                            value="two_wheeler" class="form-check-input"
                                                                            id="Wheeler"
                                                                            {{ in_array('two_wheeler', $oldAssets) ? 'checked' : '' }}>
                                                                        <label class="form-check-label"
                                                                            for="Wheeler">Two Wheeler</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div
                                                                        class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="checkbox"
                                                                            name="EmployerDetail[other_assets][]"
                                                                            value="washing_machine"
                                                                            class="form-check-input" id="Washing"
                                                                            {{ in_array('washing_machine', $oldAssets) ? 'checked' : '' }}>
                                                                        <label class="form-check-label"
                                                                            for="Washing">Washing Machine</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="tab-pane" id="Bank">

                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>About Bank
                                                            accounts (including credit facilities, if any)</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border data-table"
                                                            id="table1">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Name of Bank</th>
                                                                    <th>Branch</th>
                                                                    <th>No. of Years A/C held</th>
                                                                    <th>A/C Type</th>
                                                                    <th>A/C No.</th>
                                                                    <th>A/C Bal.</th>
                                                                    <th>As on Date</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body">
                                                                <tr>
                                                                    <td id="row-number">1</td>
                                                                    <td>
                                                                        <input type="text"
                                                                            name="BankAcc[bank_name][]"
                                                                            class="form-control mw-100">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" name="BankAcc[branch][]"
                                                                            class="form-control mw-100">
                                                                    </td>
                                                                    <td>
                                                                        <select class="form-select mw-100"
                                                                            name="BankAcc[ac_held][]">
                                                                            <option value="">Select</option>
                                                                            <option value="1">1</option>
                                                                            <option value="2">2</option>
                                                                            <option value="3">3</option>
                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" name="BankAcc[ac_type][]"
                                                                            class="form-control mw-100">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" name="BankAcc[ac_no][]"
                                                                            class="form-control mw-100">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number" min="0"
                                                                            name="BankAcc[ac_balance][]"
                                                                            class="form-control mw-100">
                                                                    </td>
                                                                    <td>
                                                                        <input type="date" name="BankAcc[date][]"
                                                                            class="form-control mw-100 past-date">
                                                                    </td>
                                                                    <td><a href="#"
                                                                            class="text-success add-bank-row"
                                                                            id="add-bank-row"
                                                                            data-class="add-bank-row"><i
                                                                                data-feather="plus-square"></i></a></td>
                                                                </tr>
                                                                @if (isset($homeLoan) && $homeLoan->bankAccounts && $homeLoan->bankAccounts->count() > 0)
                                                                    @foreach ($homeLoan->bankAccounts as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="BankAcc[bank_name][]"
                                                                                    value="{{ $val->bank_name ?? '' }}"
                                                                                    class="form-control mw-100">
                                                                            </td>
                                                                            <td><input type="text"
                                                                                    name="BankAcc[branch][]"
                                                                                    value="{{ $val->branch ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td>
                                                                                <select class="form-select mw-100"
                                                                                    name="BankAcc[ac_held][]">
                                                                                    <option value="">Select</option>
                                                                                    <option value="1"
                                                                                        {{ (isset($val->ac_held) ? $val->ac_held : '') == 1 ? 'selected' : '' }}>
                                                                                        1</option>
                                                                                    <option value="2"
                                                                                        {{ (isset($val->ac_held) ? $val->ac_held : '') == 2 ? 'selected' : '' }}>
                                                                                        2</option>
                                                                                    <option value="3"
                                                                                        {{ (isset($val->ac_held) ? $val->ac_held : '') == 3 ? 'selected' : '' }}>
                                                                                        3</option>
                                                                                </select>
                                                                            </td>
                                                                            <td><input type="text"
                                                                                    name="BankAcc[ac_type][]"
                                                                                    value="{{ $val->ac_type ?? '' }}"
                                                                                    class="form-control mw-100">
                                                                            </td>
                                                                            <td><input type="number"
                                                                                    name="BankAcc[ac_no][]"
                                                                                    value="{{ $val->ac_no ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number" min="0"
                                                                                    name="BankAcc[ac_balance][]"
                                                                                    value="{{ $val->ac_balance ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="date"
                                                                                    name="BankAcc[date][]"
                                                                                    value="{{ $val->date ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($homeLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($homeLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif

                                                            </tbody>


                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="Income">
                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>1) Outstanding
                                                            Loan Details in Individual Name</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border data-table"
                                                            id="table2">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Source</th>
                                                                    <th>Purpose</th>
                                                                    <th>Date of Sanction</th>
                                                                    <th>Loan Amt.</th>
                                                                    <th>Outstanding</th>
                                                                    <th>EMI</th>
                                                                    <th>Overdue Amt., if any</th>
                                                                    <th>Overdue Since</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-loan-indiv">
                                                                <tr>
                                                                    <td id="row-number-loan-indiv">1</td>
                                                                    <td>
                                                                        <input type="text"
                                                                            name="LoanIncIdividual[source][]"
                                                                            class="form-control mw-100">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text"
                                                                            name="LoanIncIdividual[purpose][]"
                                                                            class="form-control mw-100">
                                                                    </td>
                                                                    <td>
                                                                        <input type="date"
                                                                            name="LoanIncIdividual[sanction_date][]"
                                                                            class="form-control mw-100 past-date">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number"
                                                                            name="LoanIncIdividual[amount][]"
                                                                            id="indiv_amnt" class="form-control mw-100">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number"
                                                                            name="LoanIncIdividual[outstanding][]"
                                                                            id="indiv_out" class="form-control mw-100">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number"
                                                                            name="LoanIncIdividual[emi][]"
                                                                            id="indiv_emi" class="form-control mw-100">
                                                                    </td>
                                                                    <td>
                                                                        <input type="number"
                                                                            name="LoanIncIdividual[overdue_amount][]"
                                                                            class="form-control mw-100">
                                                                    </td>
                                                                    <td>
                                                                        <input type="date"
                                                                            name="LoanIncIdividual[overdue_since][]"
                                                                            class="form-control mw-100 future-date">
                                                                    </td>
                                                                    <td><a href="#"
                                                                            class="text-success add-bank-row-loan-indiv"
                                                                            id="add-bank-row-loan-indiv"
                                                                            data-class="add-bank-row-loan-indiv"><i
                                                                                data-feather="plus-square"></i></a></td>
                                                                </tr>
                                                                @if (isset($homeLoan) && $homeLoan->loanIncomes && $homeLoan->loanIncomes->count() > 0)
                                                                    @foreach ($homeLoan->loanIncomes->loanIncomeIndividualDetails as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="LoanIncIdividual[source][]"
                                                                                    value="{{ $val->source ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="LoanIncIdividual[purpose][]"
                                                                                    value="{{ $val->purpose ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="date"
                                                                                    name="LoanIncIdividual[sanction_date][]"
                                                                                    value="{{ $val->sanction_date ?? '' }}"
                                                                                    class="form-control mw-100 past-date">
                                                                            </td>
                                                                            <td><input type="number"
                                                                                    name="LoanIncIdividual[amount][]"
                                                                                    value="{{ $val->amount ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="LoanIncIdividual[outstanding][]"
                                                                                    value="{{ $val->outstanding ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="LoanIncIdividual[emi][]"
                                                                                    value="{{ $val->emi ?? '' }}"
                                                                                    class="form-control mw-100">
                                                                            </td>
                                                                            <td><input type="number"
                                                                                    name="LoanIncIdividual[overdue_amount][]"
                                                                                    value="{{ $val->overdue_amount ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="date"
                                                                                    name="LoanIncIdividual[overdue_since][]"
                                                                                    value="{{ $val->overdue_since ?? '' }}"
                                                                                    class="form-control mw-100 future-date">
                                                                            </td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($homeLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($homeLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif

                                                            </tbody>


                                                        </table>
                                                    </div>

                                                    <p class="mt-3  text-dark customapplsmallhead"><strong>2) Income
                                                            details</strong></p>
                                                    <div class="row">
                                                        <div class="col-md-6">

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Gross Monthly Income <span
                                                                            class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="LoanIncIdividual[common_data][gross_monthly_income]"
                                                                        value="{{ old('LoanIncIdividual.common_data.gross_monthly_income', $homeLoan->loanIncomes->gross_monthly_income ?? '') }}"
                                                                        class="form-control" />
                                                                    @error('LoanIncIdividual.common_data.gross_monthly_income')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Net Monthly Income <span
                                                                            class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number"
                                                                        name="LoanIncIdividual[common_data][net_monthly_income]"
                                                                        value="{{ old('LoanIncIdividual.common_data.net_monthly_income', $homeLoan->loanIncomes->net_monthly_income ?? '') }}"
                                                                        class="form-control" />
                                                                    @error('LoanIncIdividual.common_data.net_monthly_income')
                                                                        <span class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>



                                                        </div>
                                                    </div>

                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>3) Details of
                                                            other present immovable properties</strong> (Other than proposed
                                                        for housing loan)</p>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            @php
                                                                $selectedEncum = old(
                                                                    'LoanIncIdividual.common_data.encumbered',
                                                                    $homeLoan->loanIncomes->encumbered ?? '0',
                                                                );
                                                            @endphp
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-12">
                                                                    <label class="form-label">Nature of properties:
                                                                        <strong>Encumbered</strong><span
                                                                            class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <div class="demo-inline-spacing">
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="radio" id="yes_enc"
                                                                                name="LoanIncIdividual[common_data][encumbered]"
                                                                                value="1" class="form-check-input"
                                                                                {{ $selectedEncum === '1' ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-bolder"
                                                                                for="yes_enc">Yes</label>
                                                                        </div>
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="radio" id="no_enc"
                                                                                name="LoanIncIdividual[common_data][encumbered]"
                                                                                value="0" class="form-check-input"
                                                                                {{ $selectedEncum === '0' ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-bolder"
                                                                                for="no_enc">No</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>



                                                            <div class="row align-items-center mb-1 immv_dtil">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Plot of Land</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" min="0"
                                                                        id="l_in_plot"
                                                                        name="LoanIncIdividual[common_data][plot_land]"
                                                                        value="{{ old('LoanIncIdividual.common_data.plot_land', $homeLoan->loanIncomes->plot_land ?? '') }}"
                                                                        class="form-control" placeholder="Location" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 immv_dtil">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Agricultural Land</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="l_in_agri"
                                                                        min="0"
                                                                        name="LoanIncIdividual[common_data][agriculture_land]"
                                                                        value="{{ old('LoanIncIdividual.common_data.agriculture_land', $homeLoan->loanIncomes->agriculture_land ?? '') }}"
                                                                        class="form-control" placeholder="Location" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 immv_dtil">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">House/Godowns</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" min="0"
                                                                        id="l_in_house"
                                                                        name="LoanIncIdividual[common_data][house_godowns]"
                                                                        value="{{ old('LoanIncIdividual.common_data.house_godowns', $homeLoan->loanIncomes->house_godowns ?? '') }}"
                                                                        class="form-control" placeholder="Location" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 immv_dtil">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Others</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" min="0"
                                                                        id="l_in_others"
                                                                        name="LoanIncIdividual[common_data][others]"
                                                                        value="{{ old('LoanIncIdividual.common_data.others', $homeLoan->loanIncomes->others ?? '') }}"
                                                                        class="form-control" placeholder="Location" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1 immv_dtil">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Total Present estimated
                                                                        value of the above</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="l_in_total"
                                                                        name="LoanIncIdividual[common_data][estimated_value]"
                                                                        value="{{ old('LoanIncIdividual.common_data.estimated_value', $homeLoan->loanIncomes->estimated_value ?? '') }}"
                                                                        class="form-control" readonly />
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="GuarantApp">

                                                    <div class="row">
                                                        <div class="col-md-9">
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Name</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <input type="text"
                                                                        name="GuarantorData[common_data][guarntr_name]"
                                                                        value="{{ old('GuarantorCo.common_data.guarntr_name', $homeLoan->loanGuarApplicant->name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Father's/Mother Name</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <input type="text"
                                                                        name="GuarantorData[common_data][guarntr_fm_name]"
                                                                        value="{{ old('GuarantorData.common_data.guarntr_fm_name', $homeLoan->loanGuarApplicant->fm_name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <h5 class="mt-2  mb-2 text-dark border-bottom pb-1">
                                                                <strong>Details of other present immovable
                                                                    properties</strong> (Other than proposed as security for
                                                                housing loan)
                                                            </h5>



                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-12">
                                                                    <label class="form-label">Nature of properties:
                                                                        <strong>Encumbered</strong><span
                                                                            class="text-danger">*</span></label>
                                                                </div>

                                                                @php
                                                                    $selectedCOG = old(
                                                                        'GuarantorData.common_data.encumbered',
                                                                        $homeLoan->loanGuarApplicant->encumbered ?? '0',
                                                                    );
                                                                @endphp
                                                                <div class="col-md-6">
                                                                    <div class="demo-inline-spacing">
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="radio" id="Single"
                                                                                name="GuarantorData[common_data][guarntr_encumbered]"
                                                                                value="1" class="form-check-input"
                                                                                {{ $selectedCOG === '1' ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-bolder"
                                                                                for="Single">Yes</label>
                                                                        </div>
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="radio" id="Married"
                                                                                name="GuarantorData[common_data][guarntr_encumbered]"
                                                                                value="0" class="form-check-input"
                                                                                {{ $selectedCOG === '0' ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-bolder"
                                                                                for="Married">No</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>



                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Plot of Land</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <input type="number" min="0"
                                                                        id="g_in_plot"
                                                                        name="GuarantorData[common_data][guarntr_land_plot]"
                                                                        value="{{ old('GuarantorData.common_data.guarntr_land_plot', $homeLoan->loanGuarApplicant->land_plot ?? '') }}"
                                                                        class="form-control" placeholder="Location" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Agricultural Land</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <input type="number" min="0"
                                                                        id="g_in_agri"
                                                                        name="GuarantorData[common_data][guarntr_agriculture_land]"
                                                                        value="{{ old('GuarantorData.common_data.guarntr_agriculture_land', $homeLoan->loanGuarApplicant->agriculture_land ?? '') }}"
                                                                        class="form-control" placeholder="Location" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">House/Godowns</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <input type="number" min="0"
                                                                        id="g_in_house"
                                                                        name="GuarantorData[common_data][guarntr_h_godowns]"
                                                                        value="{{ old('GuarantorData.common_data.guarntr_h_godowns', $homeLoan->loanGuarApplicant->h_godowns ?? '') }}"
                                                                        class="form-control" placeholder="Location" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Others</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <input type="number" min="0"
                                                                        id="g_in_others"
                                                                        name="GuarantorData[common_data][guarntr_other]"
                                                                        value="{{ old('GuarantorData.common_data.guarntr_other', $homeLoan->loanGuarApplicant->other ?? '') }}"
                                                                        class="form-control" placeholder="Location" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Total Present estimated
                                                                        value of the above</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <input type="number" min="0"
                                                                        id="g_in_total" readonly
                                                                        name="GuarantorData[common_data][guarntr_est_val]"
                                                                        value="{{ old('GuarantorData.common_data.guarntr_est_val', $homeLoan->loanGuarApplicant->est_val ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>



                                                        </div>

                                                        <div class="col-md-3 order-1 order-sm-2 border-start mb-2">

                                                            <div>
                                                                @if (isset($homeLoan) && !empty($homeLoan->loanGuarApplicant->guarntr_image))
                                                                    <div class="appli-photobox">

                                                                        <img id="uploadedGuaranImage"
                                                                            src="{{ asset('storage/' . $homeLoan->loanGuarApplicant->guarntr_image) }}"
                                                                            alt="Uploaded Image"
                                                                            style="display: block;" />
                                                                    </div>
                                                                @else
                                                                    <div class="appli-photobox">
                                                                        <p id="hide-size_guar">Photo Size<br />25mm X 35mm
                                                                        </p>
                                                                        <img id="uploadedGuaranImage" />
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="mt-2 text-center">
                                                                <div class="image-uploadhide">
                                                                    <a href="attribute.html"
                                                                        class="btn btn-outline-primary btn-sm waves-effect">
                                                                        <i data-feather="upload"></i> Upload Customer
                                                                        Image</a>
                                                                    <input type="hidden" name="stored_guarntr_image"
                                                                        value="{{ old('image', $homeLoan->loanGuarApplicant->guarntr_image ?? '') }}">
                                                                    <input type="file" name="guarntr_image"
                                                                        value="{{ old('image', $homeLoan->loanGuarApplicant->guarntr_image ?? '') }}"
                                                                        class=""
                                                                        onchange="previewGuaraImage(event)">
                                                                </div>

                                                            </div>



                                                        </div>
                                                    </div>
                                                    <h5 class="mt-1 mb-2 text-dark border-bottom pb-1"><strong>Details of
                                                            Movable Assets in my name</strong></h5>

                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>1) Life
                                                            Insurance Policies</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border data-table"
                                                            id="table3">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Policy No.</th>
                                                                    <th>Maturity Date</th>
                                                                    <th>Sum Insured</th>
                                                                    <th>Co. & Branch name</th>
                                                                    <th>Last Premium paid upto</th>
                                                                    <th>Total Premium paid or surrender value</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-guarntr-insu-poli">
                                                                <tr>
                                                                    <td id="row-number-guarntr-insu-poli">1</td>
                                                                    <td><input type="text"
                                                                            name="GuarantorData[guarntr_lip_policy_no][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="date"
                                                                            name="GuarantorData[guarntr_lip_maturity_date][]"
                                                                            class="form-control mw-100 future-date"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorData[guarntr_lip_sum_insured][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="GuarantorData[guarntr_lip_co_branch][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorData[guarntr_lip_last_premium][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorData[guarntr_lip_surrender_value][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-guarntr-insu-poli"
                                                                            id="add-row-guarntr-insu-poli"
                                                                            data-class="add-row-guarntr-insu-poli"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>
                                                                @if (isset($homeLoan) && $homeLoan->loanGuarApplicant && $homeLoan->loanGuarApplicant->count() > 0)
                                                                    @foreach ($homeLoan->loanGuarApplicant->loanGuarApplicantInsurancePolicies as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorData[guarntr_lip_policy_no][]"
                                                                                    value="{{ $val->policy_no ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="date"
                                                                                    name="GuarantorData[guarntr_lip_maturity_date][]"
                                                                                    value="{{ $val->maturity_date ?? '' }}"
                                                                                    class="form-control mw-100 future-date">
                                                                            </td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorData[guarntr_lip_sum_insured][]"
                                                                                    value="{{ $val->sum_insured ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorData[guarntr_lip_co_branch][]"
                                                                                    value="{{ $val->co_branch ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorData[guarntr_lip_last_premium][]"
                                                                                    value="{{ $val->last_premium ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorData[guarntr_lip_surrender_value][]"
                                                                                    value="{{ $val->surrender_value ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($homeLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($homeLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>


                                                        </table>
                                                    </div>

                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>2) Investment
                                                            (Share/Debenture/Term deposits/Govt. Securities like, NSC
                                                            stc.)</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border data-table"
                                                            id="table4">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Description</th>
                                                                    <th>Face Value</th>
                                                                    <th>No. of Units</th>
                                                                    <th>Present Market Value</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-guarntr-deposit-term">
                                                                <tr>
                                                                    <td id="row-number-guarntr-deposit-term">1</td>
                                                                    <td><input type="text"
                                                                            name="GuarantorData[guarntr_description][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorData[guarntr_face_value][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorData[guarntr_units][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorData[guarntr_market_val][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-guarntr-deposit-term"
                                                                            id="add-row-guarntr-deposit-term"
                                                                            data-class="add-row-guarntr-deposit-term"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>
                                                                @if (isset($homeLoan) && $homeLoan->loanGuarApplicant && $homeLoan->loanGuarApplicant->count() > 0)
                                                                    @foreach ($homeLoan->loanGuarApplicant->loanGuarApplicantTermDeposits as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorData[guarntr_description][]"
                                                                                    value="{{ $val->description ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorData[guarntr_face_value][]"
                                                                                    value="{{ $val->face_value ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorData[guarntr_units][]"
                                                                                    value="{{ $val->units ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorData[guarntr_market_val][]"
                                                                                    value="{{ $val->market_val ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($homeLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($homeLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>


                                                        </table>
                                                    </div>


                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>3) Other
                                                            movable Assets</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border data-table"
                                                            id="table5">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Description</th>
                                                                    <th>Purchase Price</th>
                                                                    <th>Market Value</th>
                                                                    <th>Valuation Date</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-guarntr-asset">
                                                                <tr>
                                                                    <td id="row-number-guarntr-asset">1</td>
                                                                    <td><input type="text"
                                                                            name="GuarantorData[guarntr_description_moveable][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorData[guarntr_purchase_price][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorData[guarntr_market_val_moveable][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="date"
                                                                            name="GuarantorData[guarntr_valuation_date][]"
                                                                            class="form-control mw-100 past-date"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-guarntr-asset"
                                                                            id="add-row-guarntr-asset"
                                                                            data-class="add-row-guarntr-asset"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>
                                                                @if (isset($homeLoan) && $homeLoan->loanGuarApplicant && $homeLoan->loanGuarApplicant->count() > 0)
                                                                    @foreach ($homeLoan->loanGuarApplicant->loanGuarApplicantMoveableAssets as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorData[guarntr_description_moveable][]"
                                                                                    value="{{ $val->description ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorData[guarntr_purchase_price][]"
                                                                                    value="{{ $val->purchase_price ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorData[guarntr_market_val_moveable][]"
                                                                                    value="{{ $val->market_val ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="date"
                                                                                    name="GuarantorData[guarntr_valuation_date][]"
                                                                                    value="{{ $val->valuation_date ?? '' }}"
                                                                                    class="form-control mw-100 past-date">
                                                                            </td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($homeLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($homeLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>


                                                        </table>
                                                    </div>


                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>4) Details of
                                                            Liabilities</strong></p>

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Details of Loan/Advance
                                                                        availed from Bank's/Institution & Other
                                                                        Liabilities</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="GuarantorData[common_data][guarntr_oth_liability]"
                                                                        value="{{ old('GuarantorData.common_data.guarntr_oth_liability', $homeLoan->loanGuarApplicant->oth_liability ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Name of
                                                                        Bank/Institution</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="GuarantorData[common_data][guarntr_bank_name]"
                                                                        value="{{ old('GuarantorData.common_data.guarntr_bank_name', $homeLoan->loanGuarApplicant->bank_name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Purpose</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="GuarantorData[common_data][guarntr_purpose]"
                                                                        value="{{ old('GuarantorData.common_data.guarntr_purpose', $homeLoan->loanGuarApplicant->purpose ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Amount of Loan</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" min="0"
                                                                        name="GuarantorData[common_data][guarntr_loan_amount]"
                                                                        value="{{ old('GuarantorData.common_data.guarntr_loan_amount', $homeLoan->loanGuarApplicant->loan_amount ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Overdue if any</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="GuarantorData[common_data][guarntr_overdue]"
                                                                        value="{{ old('GuarantorData.common_data.guarntr_overdue', $homeLoan->loanGuarApplicant->overdue ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Details of Personal Gurantee
                                                                        given, if any:</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="GuarantorData[common_data][guarntr_personal_guarantee]"
                                                                        value="{{ old('GuarantorData.common_data.guarntr_personal_guarantee', $homeLoan->loanGuarApplicant->personal_guarantee ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Name of the Person on whose
                                                                        behalf (Bank/Institution)</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="GuarantorData[common_data][guarntr_person_behalf]"
                                                                        value="{{ old('GuarantorData.common_data.guarntr_person_behalf', $homeLoan->loanGuarApplicant->person_behalf ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Amount of Commitment</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="GuarantorData[common_data][guarntr_commitment_amnt]"
                                                                        value="{{ old('GuarantorData.common_data.guarntr_commitment_amnt', $homeLoan->loanGuarApplicant->commitment_amnt ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>

                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>Particulars of
                                                            Legal Heirs</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border data-table"
                                                            id="table6">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Name</th>
                                                                    <th>Relationship</th>
                                                                    <th>Age</th>
                                                                    <th>Present Address</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-guarntr-heirs">
                                                                <tr>
                                                                    <td id="row-number-guarntr-heirs">1</td>
                                                                    <td><input type="text"
                                                                            name="GuarantorData[guarntr_name][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="GuarantorData[guarntr_relation][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorData[guarntr_age][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="GuarantorData[guarntr_present_addr][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-guarntr-heirs"
                                                                            id="add-row-guarntr-heirs"
                                                                            data-class="add-row-guarntr-heirs"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>
                                                                @if (isset($homeLoan) && $homeLoan->loanGuarApplicant && $homeLoan->loanGuarApplicant->count() > 0)
                                                                    @foreach ($homeLoan->loanGuarApplicant->loanGuarApplicantLegalHeirs as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorData[guarntr_name][]"
                                                                                    value="{{ $val->name ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorData[guarntr_relation][]"
                                                                                    value="{{ $val->relation ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorData[guarntr_age][]"
                                                                                    value="{{ $val->age ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorData[guarntr_present_addr][]"
                                                                                    value="{{ $val->present_addr ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($homeLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($homeLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>


                                                        </table>
                                                    </div>

                                                </div>
                                                <div class="tab-pane" id="GuarantorCoOther">

                                                    <div class="row">
                                                        <div class="col-md-9">
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Name</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <input type="text"
                                                                        name="GuarantorCo[common_data][name]"
                                                                        value="{{ old('GuarantorCo.common_data.name', $homeLoan->guarantorCoApplicants->name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Father's/Mother Name</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <input type="text"
                                                                        name="GuarantorCo[common_data][fm_name]"
                                                                        value="{{ old('GuarantorCo.common_data.fm_name', $homeLoan->guarantorCoApplicants->fm_name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <h5 class="mt-2  mb-2 text-dark border-bottom pb-1">
                                                                <strong>Details of other present immovable
                                                                    properties</strong> (Other than proposed as security for
                                                                housing loan)
                                                            </h5>



                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-12">
                                                                    <label class="form-label">Nature of properties:
                                                                        <strong>Encumbered</strong><span
                                                                            class="text-danger">*</span></label>
                                                                </div>

                                                                @php
                                                                    $selectedCOG = old(
                                                                        'GuarantorCo.common_data.encumbered',
                                                                        $homeLoan->guarantorCoApplicants->encumbered ??
                                                                            '0',
                                                                    );
                                                                @endphp
                                                                <div class="col-md-6">
                                                                    <div class="demo-inline-spacing">
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="radio" id="Single"
                                                                                name="GuarantorCo[common_data][encumbered]"
                                                                                value="1" class="form-check-input"
                                                                                {{ $selectedCOG === '1' ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-bolder"
                                                                                for="Single">Yes</label>
                                                                        </div>
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="radio" id="Married"
                                                                                name="GuarantorCo[common_data][encumbered]"
                                                                                value="0" class="form-check-input"
                                                                                {{ $selectedCOG === '0' ? 'checked' : '' }}>
                                                                            <label class="form-check-label fw-bolder"
                                                                                for="Married">No</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>



                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Plot of Land</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <input type="number" min="0"
                                                                        id="o_in_plot"
                                                                        name="GuarantorCo[common_data][land_plot]"
                                                                        value="{{ old('GuarantorCo.common_data.land_plot', $homeLoan->guarantorCoApplicants->land_plot ?? '') }}"
                                                                        class="form-control" placeholder="Location" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Agricultural Land</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <input type="number" min="0"
                                                                        id="o_in_agri"
                                                                        name="GuarantorCo[common_data][agriculture_land]"
                                                                        value="{{ old('GuarantorCo.common_data.agriculture_land', $homeLoan->guarantorCoApplicants->agriculture_land ?? '') }}"
                                                                        class="form-control" placeholder="Location" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">House/Godowns</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <input type="number" min="0"
                                                                        id="o_in_house"
                                                                        name="GuarantorCo[common_data][h_godowns]"
                                                                        value="{{ old('GuarantorCo.common_data.h_godowns', $homeLoan->guarantorCoApplicants->h_godowns ?? '') }}"
                                                                        class="form-control" placeholder="Location" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Others</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <input type="number" min="0"
                                                                        id="o_in_others"
                                                                        name="GuarantorCo[common_data][other]"
                                                                        value="{{ old('GuarantorCo.common_data.other', $homeLoan->guarantorCoApplicants->other ?? '') }}"
                                                                        class="form-control" placeholder="Location" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Total Present estimated
                                                                        value of the above</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <input type="number" min="0"
                                                                        id="o_in_total" readonly
                                                                        name="GuarantorCo[common_data][est_val]"
                                                                        value="{{ old('GuarantorCo.common_data.est_val', $homeLoan->guarantorCoApplicants->est_val ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>



                                                        </div>
                                                        <div class="col-md-3 order-1 order-sm-2 border-start mb-2">

                                                            <div>
                                                                @if (isset($homeLoan) && !empty($homeLoan->guarantorCoApplicants->image_co))
                                                                    <div class="appli-photobox">

                                                                        <img id="uploadedImageco"
                                                                            src="{{ asset('storage/' . $homeLoan->guarantorCoApplicants->image_co) }}"
                                                                            alt="Uploaded Image"
                                                                            style="display: block;" />
                                                                    </div>
                                                                @else
                                                                    <div class="appli-photobox">
                                                                        <p id="hide-size_co">Photo Size<br />25mm X 35mm
                                                                        </p>
                                                                        <img id="uploadedImageco" />
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="mt-2 text-center">
                                                                <div class="image-uploadhide">
                                                                    <a href="attribute.html"
                                                                        class="btn btn-outline-primary btn-sm waves-effect">
                                                                        <i data-feather="upload"></i> Upload Customer
                                                                        Image</a>
                                                                    <input type="hidden" name="stored_image_co"
                                                                        value="{{ old('image', $homeLoan->guarantorCoApplicants->image_co ?? '') }}">
                                                                    <input type="file" name="image_co"
                                                                        value="{{ old('image', $homeLoan->guarantorCoApplicants->image_co ?? '') }}"
                                                                        class="" onchange="previewImageCo(event)">
                                                                </div>

                                                            </div>



                                                        </div>


                                                    </div>
                                                    <h5 class="mt-1 mb-2 text-dark border-bottom pb-1"><strong>Details of
                                                            Movable Assets in my name</strong></h5>

                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>1) Life
                                                            Insurance Policies</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border data-table"
                                                            id="table3">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Policy No.</th>
                                                                    <th>Maturity Date</th>
                                                                    <th>Sum Insured</th>
                                                                    <th>Co. & Branch name</th>
                                                                    <th>Last Premium paid upto</th>
                                                                    <th>Total Premium paid or surrender value</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-co-applicant-insu-poli">
                                                                <tr>
                                                                    <td id="row-number-co-applicant-insu-poli">1</td>
                                                                    <td><input type="text"
                                                                            name="GuarantorCo[lip_policy_no][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="date"
                                                                            name="GuarantorCo[lip_maturity_date][]"
                                                                            class="form-control mw-100 future-date"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorCo[lip_sum_insured][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="GuarantorCo[lip_co_branch][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorCo[lip_last_premium][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorCo[lip_surrender_value][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-co-applicant-insu-poli"
                                                                            id="add-row-co-applicant-insu-poli"
                                                                            data-class="add-row-co-applicant-insu-poli"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>
                                                                @if (isset($homeLoan) && $homeLoan->guarantorCoApplicants && $homeLoan->guarantorCoApplicants->count() > 0)
                                                                    @foreach ($homeLoan->guarantorCoApplicants->loanGuarantorCoApplicantInsurancePolicy as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorCo[lip_policy_no][]"
                                                                                    value="{{ $val->policy_no ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="date"
                                                                                    name="GuarantorCo[lip_maturity_date][]"
                                                                                    value="{{ $val->maturity_date ?? '' }}"
                                                                                    class="form-control mw-100 future-date">
                                                                            </td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorCo[lip_sum_insured][]"
                                                                                    value="{{ $val->sum_insured ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorCo[lip_co_branch][]"
                                                                                    value="{{ $val->co_branch ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorCo[lip_last_premium][]"
                                                                                    value="{{ $val->last_premium ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorCo[lip_surrender_value][]"
                                                                                    value="{{ $val->surrender_value ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($homeLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($homeLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>


                                                        </table>
                                                    </div>

                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>2) Investment
                                                            (Share/Debenture/Term deposits/Govt. Securities like, NSC
                                                            stc.)</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border data-table"
                                                            id="table4">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Description</th>
                                                                    <th>Face Value</th>
                                                                    <th>No. of Units</th>
                                                                    <th>Present Market Value</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-co-applicant-deposit-term">
                                                                <tr>
                                                                    <td id="row-number-co-applicant-deposit-term">1</td>
                                                                    <td><input type="text"
                                                                            name="GuarantorCo[description][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorCo[face_value][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorCo[units][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorCo[market_val][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-co-applicant-deposit-term"
                                                                            id="add-row-co-applicant-deposit-term"
                                                                            data-class="add-row-co-applicant-deposit-term"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>
                                                                @if (isset($homeLoan) && $homeLoan->guarantorCoApplicants && $homeLoan->guarantorCoApplicants->count() > 0)
                                                                    @foreach ($homeLoan->guarantorCoApplicants->loanGuarantorCoApplicantTermDeposit as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorCo[description][]"
                                                                                    value="{{ $val->description ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorCo[face_value][]"
                                                                                    value="{{ $val->face_value ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorCo[units][]"
                                                                                    value="{{ $val->units ?? '' }}"
                                                                                    class="form-control mw-100">
                                                                            </td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorCo[market_val][]"
                                                                                    value="{{ $val->market_val ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($homeLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($homeLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>


                                                        </table>
                                                    </div>


                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>3) Other
                                                            movable Assets</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border data-table"
                                                            id="table5">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Description</th>
                                                                    <th>Purchase Price</th>
                                                                    <th>Market Value</th>
                                                                    <th>Valuation Date</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-co-applicant-asset">
                                                                <tr>
                                                                    <td id="row-number-co-applicant-asset">1</td>
                                                                    <td><input type="text"
                                                                            name="GuarantorCo[description_moveable][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorCo[purchase_price][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="GuarantorCo[market_val_moveable][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="date"
                                                                            name="GuarantorCo[valuation_date][]"
                                                                            class="form-control mw-100 past-date"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-co-applicant-asset"
                                                                            id="add-row-co-applicant-asset"
                                                                            data-class="add-row-co-applicant-asset"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>
                                                                @if (isset($homeLoan) && $homeLoan->guarantorCoApplicants && $homeLoan->guarantorCoApplicants->count() > 0)
                                                                    @foreach ($homeLoan->guarantorCoApplicants->loanGuarantorCoApplicantMoveableAsset as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorCo[description_moveable][]"
                                                                                    value="{{ $val->description ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorCo[purchase_price][]"
                                                                                    value="{{ $val->purchase_price ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="GuarantorCo[market_val_moveable][]"
                                                                                    value="{{ $val->market_val ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="date"
                                                                                    name="GuarantorCo[valuation_date][]"
                                                                                    value="{{ $val->valuation_date ?? '' }}"
                                                                                    class="form-control mw-100 past-date">
                                                                            </td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($homeLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($homeLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>


                                                        </table>
                                                    </div>


                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>4) Details of
                                                            Liabilities</strong></p>

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Details of Loan/Advance
                                                                        availed from Bank's/Institution & Other
                                                                        Liabilities</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="GuarantorCo[common_data][oth_liability_co]"
                                                                        value="{{ old('GuarantorCo.common_data.oth_liability_co', $homeLoan->guarantorCoApplicants->oth_liability ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Name of
                                                                        Bank/Institution</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="GuarantorCo[common_data][bank_name_co]"
                                                                        value="{{ old('GuarantorCo.common_data.bank_name_co', $homeLoan->guarantorCoApplicants->bank_name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Purpose</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="GuarantorCo[common_data][purpose_co]"
                                                                        value="{{ old('GuarantorCo.common_data.purpose_co', $homeLoan->guarantorCoApplicants->purpose ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Amount of Loan</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" min="0"
                                                                        name="GuarantorCo[common_data][loan_amount_co]"
                                                                        value="{{ old('GuarantorCo.common_data.loan_amount_co', $homeLoan->guarantorCoApplicants->loan_amount ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Overdue if any</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="GuarantorCo[common_data][overdue_co]"
                                                                        value="{{ old('GuarantorCo.common_data.overdue_co', $homeLoan->guarantorCoApplicants->overdue ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Details of Personal Gurantee
                                                                        given, if any:</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="GuarantorCo[common_data][personal_guarantee_co]"
                                                                        value="{{ old('GuarantorCo.common_data.personal_guarantee_co', $homeLoan->guarantorCoApplicants->personal_guarantee ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Name of the Person on whose
                                                                        behalf (Bank/Institution)</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="GuarantorCo[common_data][person_behalf_co]"
                                                                        value="{{ old('GuarantorCo.common_data.person_behalf_co', $homeLoan->guarantorCoApplicants->person_behalf ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Amount of Commitment</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="GuarantorCo[common_data][commitment_amnt_co]"
                                                                        value="{{ old('GuarantorCo.common_data.commitment_amnt_co', $homeLoan->guarantorCoApplicants->commitment_amnt ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>

                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>Particulars of
                                                            Legal Heirs</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border data-table"
                                                            id="table6">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Name</th>
                                                                    <th>Relationship</th>
                                                                    <th>Age</th>
                                                                    <th>Present Address</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-legal-heirs">
                                                                <tr>
                                                                    <td id="row-number-legal-heirs">1</td>
                                                                    <td><input type="text" name="GuarantorCo[name][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="GuarantorCo[relation][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text" name="GuarantorCo[age][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="GuarantorCo[present_addr][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-legal-heirs"
                                                                            id="add-row-legal-heirs"
                                                                            data-class="add-row-legal-heirs"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>
                                                                @if (isset($homeLoan) && $homeLoan->guarantorCoApplicants && $homeLoan->guarantorCoApplicants->count() > 0)
                                                                    @foreach ($homeLoan->guarantorCoApplicants->loanGuarantorCoApplicantLegalHeir as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorCo[name][]"
                                                                                    value="{{ $val->name ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorCo[relation][]"
                                                                                    value="{{ $val->relation ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorCo[age][]"
                                                                                    value="{{ $val->age ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorCo[present_addr][]"
                                                                                    value="{{ $val->present_addr ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($homeLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($homeLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>


                                                        </table>
                                                    </div>

                                                </div>
                                                <div class="tab-pane" id="Proposed">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Outside Borrowing</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number" min="0"
                                                                        name="ProposedLoan[common_data][outside_borrowing]"
                                                                        value="{{ old('ProposedLoan.common_data.outside_borrowing', $homeLoan->proposedLoans->outside_borrowing ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Loan Amount
                                                                        Requested</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number" min="0"
                                                                        name="ProposedLoan[common_data][loan_amount_request]"
                                                                        value="{{ old('ProposedLoan.common_data.loan_amount_request', $homeLoan->proposedLoans->loan_amount_request ?? '') }}"
                                                                        id="requested_amount" class="form-control" />

                                                                    <span class="text-danger"
                                                                        id="requested_amountjs"></span>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Rate of Interest %</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number" min="0"
                                                                        id="interest_rate_float"
                                                                        name="ProposedLoan[common_data][interest_rate]"
                                                                        value="{{ old('ProposedLoan.common_data.interest_rate', $homeLoan->proposedLoans->interest_rate ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Floating/Fixed</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <select class="form-select"
                                                                        name="ProposedLoan[common_data][floating_fixed]">
                                                                        <option value="">Select</option>
                                                                        <option value="floating"
                                                                            {{ old('ProposedLoan.common_data.floating_fixed', isset($homeLoan->proposedLoans->floating_fixed) ? $homeLoan->proposedLoans->floating_fixed : '') == 'floating' ? 'selected' : '' }}>
                                                                            Floating</option>
                                                                        <option value="fixed"
                                                                            {{ old('ProposedLoan.common_data.floating_fixed', isset($homeLoan->proposedLoans->floating_fixed) ? $homeLoan->proposedLoans->floating_fixed : '') == 'fixed' ? 'selected' : '' }}>
                                                                            Fixed</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Margin %</label>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <input type="number" min="0"
                                                                        id="margin_float"
                                                                        name="ProposedLoan[common_data][margin]"
                                                                        value="{{ old('ProposedLoan.common_data.margin', $homeLoan->proposedLoans->margin ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>

                                                    <h5 class="mt-1 mb-2 text-dark border-bottom pb-1"><strong>Details of
                                                            Movable Assets in my name</strong></h5>

                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>1) Life
                                                            Insurance Policies</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border data-table"
                                                            id="table7">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Policy No.</th>
                                                                    <th>Date of Issuance</th>
                                                                    <th>Sum Insured</th>
                                                                    <th>Co. & Branch name</th>
                                                                    <th>Annual Premium</th>
                                                                    <th>Premium paid for surrender value</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-proposed-loan">
                                                                <tr>
                                                                    <td id="row-number-proposed-loan">1</td>
                                                                    <td><input type="text"
                                                                            name="ProposedLoan[policy_no][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="date"
                                                                            name="ProposedLoan[issuance_date][]"
                                                                            class="form-control mw-100 past-date"></td>
                                                                    <td><input type="number"
                                                                            name="ProposedLoan[sum_insured][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="ProposedLoan[co_branch][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="ProposedLoan[annual_premium][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="ProposedLoan[premium_paid][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-proposed-loan"
                                                                            id="add-row-proposed-loan"
                                                                            data-class="add-row-proposed-loan"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>
                                                                @if (isset($homeLoan) && $homeLoan->proposedLoans && $homeLoan->proposedLoans->loanProposedInsurancePolicy->count() > 0)
                                                                    @foreach ($homeLoan->proposedLoans->loanProposedInsurancePolicy as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="ProposedLoan[policy_no][]"
                                                                                    value="{{ $val->policy_no ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="date"
                                                                                    name="ProposedLoan[issuance_date][]"
                                                                                    value="{{ $val->issuance_date ?? '' }}"
                                                                                    class="form-control mw-100 past-date">
                                                                            </td>
                                                                            <td><input type="number"
                                                                                    name="ProposedLoan[sum_insured][]"
                                                                                    value="{{ $val->sum_insured ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="ProposedLoan[co_branch][]"
                                                                                    value="{{ $val->co_branch ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="ProposedLoan[annual_premium][]"
                                                                                    value="{{ $val->annual_premium ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="ProposedLoan[premium_paid][]"
                                                                                    value="{{ $val->premium_paid ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($homeLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($homeLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>


                                                        </table>
                                                    </div>

                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>2)
                                                            Share/Debenture/Term deposits/Govt. Securities (NSC
                                                            stc.)</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border data-table"
                                                            id="table8">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Co./Bank/Post Office</th>
                                                                    <th>Date of Instrument</th>
                                                                    <th>Face Value</th>
                                                                    <th>Resent Value</th>
                                                                    <th>Due Date</th>
                                                                    <th>Whether Encumbered</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-term-deposit">
                                                                <tr>
                                                                    <td id="row-number-term-deposit">1</td>
                                                                    <td><input type="text"
                                                                            name="ProposedLoan[post_office][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="date"
                                                                            name="ProposedLoan[instrument_date][]"
                                                                            class="form-control mw-100 past-date"></td>
                                                                    <td><input type="number"
                                                                            name="ProposedLoan[face_value][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="ProposedLoan[resent_value][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="date"
                                                                            name="ProposedLoan[due_date][]"
                                                                            class="form-control mw-100 future-date"></td>
                                                                    <td><input type="text"
                                                                            name="ProposedLoan[whether_encumbered][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-term-deposit"
                                                                            id="add-row-term-deposit"
                                                                            data-class="add-row-term-deposit"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>
                                                                @if (isset($homeLoan) && $homeLoan->proposedLoans && $homeLoan->proposedLoans->count() > 0)
                                                                    @foreach ($homeLoan->proposedLoans->loanProposedTermDeposit as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="ProposedLoan[post_office][]"
                                                                                    value="{{ $val->post_office ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="date"
                                                                                    name="ProposedLoan[instrument_date][]"
                                                                                    value="{{ $val->instrument_date ?? '' }}"
                                                                                    class="form-control mw-100 past-date">
                                                                            </td>
                                                                            <td><input type="number"
                                                                                    name="ProposedLoan[face_value][]"
                                                                                    value="{{ $val->face_value ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="ProposedLoan[resent_value][]"
                                                                                    value="{{ $val->resent_value ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="date"
                                                                                    name="ProposedLoan[due_date][]"
                                                                                    value="{{ $val->due_date ?? '' }}"
                                                                                    class="form-control mw-100 future-date">
                                                                            </td>
                                                                            <td><input type="text"
                                                                                    name="ProposedLoan[whether_encumbered][]"
                                                                                    value="{{ $val->whether_encumbered ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($homeLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($homeLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>


                                                        </table>
                                                    </div>


                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>3) Other
                                                            movable Assets</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border data-table"
                                                            id="table9">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Description</th>
                                                                    <th>Year of Acquiring</th>
                                                                    <th>Purchase Price</th>
                                                                    <th>Present Market Value</th>
                                                                    <th>Valuation Date</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-moveable-assets">
                                                                <tr>
                                                                    <td id="row-number-moveable-assets">1</td>
                                                                    <td><input type="text"
                                                                            name="ProposedLoan[description][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="ProposedLoan[acquiring_year][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="ProposedLoan[purchase_price][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="number"
                                                                            name="ProposedLoan[present_market_val][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="date"
                                                                            name="ProposedLoan[valuation_date][]"
                                                                            class="form-control mw-100 past-date"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-moveable-assets"
                                                                            id="add-row-moveable-assets"
                                                                            data-class="add-row-moveable-assets"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>
                                                                @if (isset($homeLoan) && $homeLoan->proposedLoans && $homeLoan->proposedLoans->count() > 0)
                                                                    @foreach ($homeLoan->proposedLoans->loanProposedMoveableAsset as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="ProposedLoan[description][]"
                                                                                    value="{{ $val->description ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="ProposedLoan[acquiring_year][]"
                                                                                    value="{{ $val->acquiring_year ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="ProposedLoan[purchase_price][]"
                                                                                    value="{{ $val->purchase_price ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="number"
                                                                                    name="ProposedLoan[present_market_val][]"
                                                                                    value="{{ $val->present_market_val ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="date"
                                                                                    name="ProposedLoan[valuation_date][]"
                                                                                    value="{{ $val->valuation_date ?? '' }}"
                                                                                    class="form-control mw-100 past-date">
                                                                            </td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($homeLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($homeLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>


                                                        </table>
                                                    </div>


                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>4) Details of
                                                            Liabilities</strong></p>

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Name of Bank/Institution and
                                                                        it's Branch</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="ProposedLoan[common_data][bank_name]"
                                                                        value="{{ old('ProposedLoan.common_data.bank_name', $homeLoan->proposedLoans->bank_name ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Purpose and amount of
                                                                        loan/credit facilities</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="ProposedLoan[common_data][loan_credit]"
                                                                        value="{{ old('ProposedLoan.common_data.loan_credit', $homeLoan->proposedLoans->loan_credit ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Security/Repayment
                                                                        schedule</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="ProposedLoan[common_data][security_schedule]"
                                                                        value="{{ old('ProposedLoan.common_data.security_schedule', $homeLoan->proposedLoans->security_schedule ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Presenting
                                                                        Outstanding</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="ProposedLoan[common_data][present_outstanding]"
                                                                        value="{{ old('ProposedLoan.common_data.present_outstanding', $homeLoan->proposedLoans->present_outstanding ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Liabilities other than to
                                                                        Bank and Financial Institutions:</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="ProposedLoan[common_data][liabilities]"
                                                                        value="{{ old('ProposedLoan.common_data.liabilities', $homeLoan->proposedLoans->liabilities ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>


                                                        </div>
                                                    </div>

                                                    <div class="bg-light rounded p-1 mt-2">
                                                        <p class="text-dark customapplsmallhead">Details of Personal
                                                            Guarantee given for any person/firm. If yes, furnish
                                                            details(i.e. Name of the Bank/institutions, on whose behalf,
                                                            amount of gurantee, present status of a/c, etc)</p>

                                                        <p class="mt-2  text-dark customapplsmallhead">I enclose/Submit
                                                            documentary proof in support of the above submissions.</p>
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
                                                                        name="LoanDocument[common_data][stored_adhar_card]"
                                                                        value="{{ old('stored_adhar_card', $homeLoan->documents->adhar_card ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="LoanDocument[common_data][adhar_card][]"
                                                                        class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" multiple />
                                                                </div>
                                                                @if (isset($homeLoan) && $homeLoan->documents && $homeLoan->documents->adhar_card)
                                                                    @php
                                                                        $adhar_doc_json =
                                                                            $homeLoan->documents->adhar_card;
                                                                        $adhar_docs = json_decode(
                                                                            $adhar_doc_json,
                                                                            true,
                                                                        );
                                                                    @endphp
                                                                    @if (!empty($adhar_docs) && is_array($adhar_docs))
                                                                        @foreach ($adhar_docs as $key => $doc)
                                                                            @php
                                                                                $extension = pathinfo(
                                                                                    $doc,
                                                                                    PATHINFO_EXTENSION,
                                                                                );
                                                                                $extension = ucfirst($extension);
                                                                            @endphp
                                                                            <div class="col-md-3 mt-1">
                                                                                <div class="row d-flex">
                                                                                    <p><i data-feather='folder'
                                                                                            class="me-50"></i><a
                                                                                            href="{{ asset('storage/' . $doc) }}"
                                                                                            style="color:green; font-size:12px;"
                                                                                            target="_blank"
                                                                                            download>Aadhar Card</a></p>
                                                                                </div>
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
                                                                        name="LoanDocument[common_data][stored_gir_no]"
                                                                        value="{{ old('stored_gir_no', $homeLoan->documents->gir_no ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="LoanDocument[common_data][gir_no][]"
                                                                        class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" multiple />
                                                                </div>

                                                                @if (isset($homeLoan) && $homeLoan->documents && $homeLoan->documents->gir_no)
                                                                    @php
                                                                        $gir_no_json = $homeLoan->documents->gir_no;
                                                                        $gir_nos = json_decode($gir_no_json, true);
                                                                    @endphp
                                                                    @if (!empty($gir_nos) && is_array($gir_nos))
                                                                        @foreach ($gir_nos as $key => $doc)
                                                                            @php
                                                                                $extension = pathinfo(
                                                                                    $doc,
                                                                                    PATHINFO_EXTENSION,
                                                                                );
                                                                                $extension = ucfirst($extension);
                                                                            @endphp
                                                                            <div class="col-md-3 mt-1">
                                                                                <p><i data-feather='folder'
                                                                                        class="me-50"></i><a
                                                                                        href="{{ asset('storage/' . $doc) }}"
                                                                                        style="color:green; font-size:12px;"
                                                                                        target="_blank" download>PAN/GIR
                                                                                        No</a></p>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Plot Document</label>
                                                                </div>

                                                                <div class="col-md-5">
                                                                    <input type="hidden"
                                                                        name="LoanDocument[common_data][stored_plot_doc]"
                                                                        value="{{ old('stored_plot_doc', $homeLoan->documents->plot_doc ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="LoanDocument[common_data][plot_doc][]"
                                                                        class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" multiple />
                                                                </div>

                                                                @if (isset($homeLoan) && $homeLoan->documents && $homeLoan->documents->plot_doc)
                                                                    @php
                                                                        $plot_doc_json = $homeLoan->documents->plot_doc;
                                                                        $plot_docs = json_decode($plot_doc_json, true);
                                                                    @endphp
                                                                    @if (!empty($plot_docs) && is_array($plot_docs))
                                                                        @foreach ($plot_docs as $key => $doc)
                                                                            @php
                                                                                $extension = pathinfo(
                                                                                    $doc,
                                                                                    PATHINFO_EXTENSION,
                                                                                );
                                                                                $extension = ucfirst($extension);
                                                                            @endphp
                                                                            <div class="col-md-3 mt-1">
                                                                                <p><i data-feather='folder'
                                                                                        class="me-50"></i><a
                                                                                        href="{{ asset('storage/' . $doc) }}"
                                                                                        style="color:green; font-size:12px;"
                                                                                        target="_blank" download>Plot
                                                                                        Document</a></p>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Land Document</label>
                                                                </div>

                                                                <div class="col-md-5">
                                                                    <input type="hidden"
                                                                        name="LoanDocument[common_data][stored_land_doc]"
                                                                        value="{{ old('stored_land_doc', $homeLoan->documents->land_doc ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="LoanDocument[common_data][land_doc][]"
                                                                        class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" multiple />
                                                                </div>

                                                                @if (isset($homeLoan) && $homeLoan->documents && $homeLoan->documents->land_doc)
                                                                    @php
                                                                        $land_doc_json = $homeLoan->documents->land_doc;
                                                                        $land_docs = json_decode($land_doc_json, true);
                                                                    @endphp
                                                                    @if (!empty($land_docs) && is_array($land_docs))
                                                                        @foreach ($land_docs as $key => $doc)
                                                                            @php
                                                                                $extension = pathinfo(
                                                                                    $doc,
                                                                                    PATHINFO_EXTENSION,
                                                                                );
                                                                                $extension = ucfirst($extension);
                                                                            @endphp
                                                                            <div class="col-md-3 mt-1">
                                                                                <p><i data-feather='folder'
                                                                                        class="me-50"></i><a
                                                                                        href="{{ asset('storage/' . $doc) }}"
                                                                                        style="color:green; font-size:12px;"
                                                                                        target="_blank" download>Land
                                                                                        Document</a></p>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </div>

                                                            <div class="row mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Income Proof</label>
                                                                </div>

                                                                <div class="col-md-5">
                                                                    <input type="hidden"
                                                                        name="LoanDocument[common_data][stored_income_proof]"
                                                                        value="{{ old('stored_income_proof', $homeLoan->documents->income_proof ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="LoanDocument[common_data][income_proof][]"
                                                                        class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" multiple />
                                                                </div>

                                                                @if (isset($homeLoan) && $homeLoan->documents && $homeLoan->documents->income_proof)
                                                                    @php
                                                                        $income_proof_json =
                                                                            $homeLoan->documents->income_proof;
                                                                        $income_proofs = json_decode(
                                                                            $income_proof_json,
                                                                            true,
                                                                        );
                                                                    @endphp
                                                                    @if (!empty($income_proofs) && is_array($income_proofs))
                                                                        @foreach ($income_proofs as $key => $doc)
                                                                            @php
                                                                                $extension = pathinfo(
                                                                                    $doc,
                                                                                    PATHINFO_EXTENSION,
                                                                                );
                                                                                $extension = ucfirst($extension);
                                                                            @endphp
                                                                            <div class="col-md-3 mt-1">
                                                                                <p><i data-feather='folder'
                                                                                        class="me-50"></i><a
                                                                                        href="{{ asset('storage/' . $doc) }}"
                                                                                        style="color:green; font-size:12px;"
                                                                                        target="_blank" download>Income
                                                                                        Proof</a></p>
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
                    </form>
                    <!-- Modal to add new record -->

                </section>


            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="modal fade" id="viewassesgive" tabindex="-1" aria-labelledby="shareProjectTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <form action="{{ route('loanAssessment.assessment-proceed') }}" method="POST" enctype="multipart/form-data">
            @csrf
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Assessment
                            by
                            Field Officer</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{$overview->proprietor_name}} | {{$overview->term_loan}} | {{explode(' ',$overview->created_at)[0]}}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">

                    <div class="row mt-1">

                        <div class="col-md-12">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label class="form-label">Upload Document <span
                                                class="text-danger">*</span></label>
                                        <input type="file" class="form-control remove-disable" name="document" required/>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="loan_type" value="home" required>
                            <input type="hidden" name="loan_application_id" value="{{request()->route('id')}}" required>
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
                <form action="{{ route('loanAssessment.loan-reject') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Reject Home
                            Loan Application</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{$overview->proprietor_name}} | {{$overview->term_loan}} | {{explode(' ',$overview->created_at)[0]}}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">

                    <div class="row mt-1">

                        <div class="col-md-12">

                            <div class="mb-1">
                                <label class="form-label">Upload Document</label>
                                <input type="file" class="form-control remove-disable" name="document" required/>
                            </div>
                            <input type="hidden" name="loan_type" value="home" required>
                            <input type="hidden" name="loan_application_id" value="{{request()->route('id')}}" required>
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
                <form action="{{ route('loanAssessment.loan-return') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Return
                            Home
                            Loan Application</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{$overview->proprietor_name}} | {{$overview->term_loan}} | {{explode(' ',$overview->created_at)[0]}}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">

                    <div class="row mt-1">

                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label">Upload Document</label>
                                <input type="file" class="form-control remove-disable" name="document" required/>
                            </div>
                            <input type="hidden" name="loan_type" value="home" required>
                            <input type="hidden" name="loan_application_id" value="{{request()->route('id')}}" required>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/js/loan.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script type="text/javascript">
        var getSeriesUrl = "{{ url('loan/get-series') }}".trim();
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
            @if (isset($homeLoan))
                let book_type_val = $("#book_type").val();

                if (book_type_val) {
                    fetchLoanSeries(book_type_val, 'series').done(function() {

                        let homeLoanSeries = '{{ $homeLoan->series }}';
                        $('#series option').each(function() {
                            if ($(this).val() == homeLoanSeries) {
                                $(this).prop('selected', true);
                            }
                        });
                    });
                } else {
                    console.log("Book type value is empty");
                }
            @endif
            function toggleImmvDtil() {
                if ($('#no_enc').is(':checked')) {
                    $('.immv_dtil').hide();
                } else {
                    $('.immv_dtil').show();
                }
            }

            // Initial check on page load
            toggleImmvDtil();

            // Check on radio button change
            $('input[name="LoanIncIdividual[common_data][encumbered]"]').change(function() {
                toggleImmvDtil();
            });
            $('.cancelButton').on('click', function() {
                $('#approve').modal('hide');
                $('#reject').modal('hide');
            });
            let permaCity = '';
            let permaState = '';
            let selectedCity = "{{ $homeLoan->addresses->city ?? '' }}";
            let selectedState = "{{ $homeLoan->addresses->state ?? '' }}";

            let selectedEmployerCity = "{{ $homeLoan->employerDetails->city ?? '' }}";
            let selectedOtherCity = "{{ $homeLoan->otherDetails->city ?? '' }}";
            let selectedGuarCity = "{{ $homeLoan->loanOtherGuarantors->city ?? '' }}";
            let selectedPCity = "{{ $homeLoan->addresses->p_city ?? '' }}";

            let selectedEmployerState = "{{ $homeLoan->employerDetails->state ?? '' }}";
            let selectedOtherState = "{{ $homeLoan->otherDetails->state ?? '' }}";
            let selectedGuarState = "{{ $homeLoan->loanOtherGuarantors->state ?? '' }}";
            let selectedPState = "{{ $homeLoan->addresses->p_state ?? '' }}";
            @if (!isset($editData))
                var formData = JSON.parse(localStorage.getItem('formData') || '[]');
                var $firstRow = $('#table-body tr:first').clone();
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
                    $('#table-body').append($newRow);
                });
                localStorage.removeItem('formData');
                feather.replace();

                var formData1 = JSON.parse(localStorage.getItem('formData1') || '[]');
                var $firstRow = $('#table-body-loan-indiv tr:first').clone();
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
                    $('#table-body-loan-indiv').append($newRow);
                });
                localStorage.removeItem('formData1');
                feather.replace();

                var formData2 = JSON.parse(localStorage.getItem('formData2') || '[]');
                var $firstRow = $('#table-body-co-applicant-insu-poli tr:first').clone();
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
                    $('#table-body-co-applicant-insu-poli').append($newRow);
                });
                localStorage.removeItem('formData2');
                feather.replace();

                var formData3 = JSON.parse(localStorage.getItem('formData3') || '[]');
                var $firstRow = $('#table-body-co-applicant-deposit-term tr:first').clone();
                formData3.forEach(function(rowData, index) {
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
                    $('#table-body-co-applicant-deposit-term').append($newRow);
                });
                localStorage.removeItem('formData3');
                feather.replace();

                var formData4 = JSON.parse(localStorage.getItem('formData4') || '[]');
                var $firstRow = $('#table-body-co-applicant-asset tr:first').clone();
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
                    $('#table-body-co-applicant-asset').append($newRow);
                });
                localStorage.removeItem('formData4');
                feather.replace();

                var formData5 = JSON.parse(localStorage.getItem('formData5') || '[]');
                var $firstRow = $('#table-body-legal-heirs tr:first').clone();
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
                    $('#table-body-legal-heirs').append($newRow);
                });
                localStorage.removeItem('formData5');
                feather.replace();

                var formData6 = JSON.parse(localStorage.getItem('formData6') || '[]');
                var $firstRow = $('#table-body-proposed-loan tr:first').clone();
                formData6.forEach(function(rowData, index) {
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
                    $('#table-body-proposed-loan').append($newRow);
                });
                localStorage.removeItem('formData6');
                feather.replace();

                var formData7 = JSON.parse(localStorage.getItem('formData7') || '[]');
                var $firstRow = $('#table-body-term-deposit tr:first').clone();
                formData7.forEach(function(rowData, index) {
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
                    $('#table-body-term-deposit').append($newRow);
                });
                localStorage.removeItem('formData7');
                feather.replace();

                var formData8 = JSON.parse(localStorage.getItem('formData8') || '[]');
                var $firstRow = $('#table-body-moveable-assets tr:first').clone();
                formData8.forEach(function(rowData, index) {
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
                    $('#table-body-moveable-assets').append($newRow);
                });
                localStorage.removeItem('formData8');
                feather.replace();

                // ---------------------------------------------------------------------------------------
                var formData9 = JSON.parse(localStorage.getItem('formData9') || '[]');
                var $firstRow = $('#table-body-guarntr-insu-poli tr:first').clone();
                formData9.forEach(function(rowData, index) {
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
                    $('#table-body-guarntr-insu-poli').append($newRow);
                });
                localStorage.removeItem('formData9');
                feather.replace();

                var formData10 = JSON.parse(localStorage.getItem('formData10') || '[]');
                var $firstRow = $('#table-body-guarntr-deposit-term tr:first').clone();
                formData10.forEach(function(rowData, index) {
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
                    $('#table-body-guarntr-deposit-term').append($newRow);
                });
                localStorage.removeItem('formData10');
                feather.replace();

                var formData11 = JSON.parse(localStorage.getItem('formData11') || '[]');
                var $firstRow = $('#table-body-guarntr-asset tr:first').clone();
                formData11.forEach(function(rowData, index) {
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
                    $('#table-body-guarntr-asset').append($newRow);
                });
                localStorage.removeItem('formData11');
                feather.replace();

                var formData12 = JSON.parse(localStorage.getItem('formData12') || '[]');
                var $firstRow = $('#table-body-guarntr-heirs tr:first').clone();
                formData12.forEach(function(rowData, index) {
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
                    $('#table-body-guarntr-heirs').append($newRow);
                });
                localStorage.removeItem('formData12');
                feather.replace();
            @endif

            var selectedValueData = $('input[name="OtherDetail[common_data][co_type]"]:checked').val();
            var selectedValueGuar = $('input[name="OtherDetail[common_data][guar_type]"]:checked').val();
            if (selectedValueData == 1) {
                $("#co_appli_tab").show();
                $('.hide_co_field').show();
            } else {
                $("#co_appli_tab").hide();
                $('.hide_co_field').hide();
            }
            if (selectedValueGuar == 1) {
                $("#guarantor_tab").show();
                $('.hid_g_field').show();
            } else {
                $("#guarantor_tab").hide();
                $('.hid_g_field').hide();
            }

            // table add delete values
            feather.replace();
            $('tbody').on('click',
                '#add-bank-row, #add-bank-row-loan-indiv, #add-row-proposed-loan, #add-row-term-deposit, #add-row-moveable-assets, #add-row-legal-heirs, #add-row-co-applicant-asset, #add-row-co-applicant-deposit-term, #add-row-co-applicant-insu-poli, #add-row-guarntr-insu-poli, #add-row-guarntr-deposit-term, #add-row-guarntr-asset, #add-row-guarntr-heirs',
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

                    let indiv_amnt = parseFloat($currentRow.find('#indiv_amnt').val()) || 0;
                    let indiv_out = parseFloat($currentRow.find('#indiv_out').val()) || 0;
                    let indiv_emi = parseFloat($currentRow.find('#indiv_emi').val()) || 0;

                    if (indiv_out !== 0 && indiv_amnt === 0) {
                        alert('Please enter Loan Amount');
                        return;
                    }

                    if (indiv_out === 0 && indiv_amnt !== 0) {
                        alert('Please enter Outstanding Amount');
                        return;
                    }

                    if (indiv_out > indiv_amnt) {
                        alert('Please enter Outstanding Amount Less than to Loan Amount');
                        return;
                    }

                    if ((indiv_out === 0 || indiv_amnt === 0) && indiv_emi !== 0) {
                        alert('Please enter Outstanding & Loan Amount first');
                        return;
                    }

                    if (indiv_out !== 0 && indiv_amnt !== 0 && indiv_emi !== 0) {
                        if (indiv_emi > indiv_amnt) {
                            alert('Please enter EMI Amount less than to Loan Amount');
                            return;
                        } else if (indiv_emi > indiv_out) {
                            alert('Please enter EMI Amount less than to Outstanding Amount');
                            return;
                        }
                    }

                    $currentRow.find('input').val('');
                    var acHeldValue = $currentRow.find('select[name="BankAcc[ac_held][]"]').val();

                    // Update row number for the new row
                    var nextIndex = $('#' + tbodyId + ' tr').length + 1;
                    $newRow.find('#' + $firstTdClass).text(nextIndex);
                    $newRow.find('#' + clickedClass).removeClass(clickedClass).removeAttr('id').removeAttr(
                        'data-class').addClass('text-danger delete-item').html(
                        '<i data-feather="trash-2"></i>');
                    if (acHeldValue) {
                        $newRow.find('select[name="BankAcc[ac_held][]"]').val(acHeldValue);
                        $currentRow.find('select[name="BankAcc[ac_held][]"]').val('');
                    }

                    $('#' + tbodyId).append($newRow);
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

            // update permanent address
            function updatePermanentAddress() {
                if ($('#colorCheck2').is(':checked')) {
                    $('#permanentAddress1').val($('#currentAddress1').val());
                    $('#permanentAddress2').val($('#currentAddress2').val());
                    var selectedCityId = $("#city-select").val(); // Get the selected city ID
                    var selectedCityText = $("#city-select").find('option:selected')
                        .text(); // Get the selected city name
                    var newCityOption = new Option(selectedCityText, selectedCityId, true, true);
                    $('#p-city-select').empty().append(newCityOption).trigger(
                        'change'); // Empty existing options and add the new one

                    // When the #state-select value changes
                    $('#state-select').on('change', function() {
                        var selectedStateId = $("#state-select").val(); // Get the selected state ID
                        var selectedStateText = $("#state-select").find('option:selected')
                            .text(); // Get the selected state name

                        // Replace the option in #p-state-select with the selected state
                        var newStateOption = new Option(selectedStateText, selectedStateId, true, true);
                        $('#p-state-select').empty().append(newStateOption).trigger(
                            'change'); // Empty existing options and add the new one
                    });
                    $('#permanentPinCode').val($('#currentPinCode').val());
                    $('#permanentResidencePhn').val($('#currentResidencePhn').val());
                } else {
                    @if (isset($homeLoan->addresses->same_as) && $homeLoan->addresses->same_as == 0)
                    @else
                        $('#permanentAddress1').val('');
                        $('#permanentAddress2').val('');
                        $('#p-city-select').empty().trigger('change');
                        $('#p-state-select').empty().trigger('change');
                        $('#permanentPinCode').val('');
                        $('#permanentResidencePhn').val('');
                    @endif
                }
            }
            $('#colorCheck2').on('change', updatePermanentAddress);

            $('#currentAddress1, #currentAddress2, #currentCity, #currentState, #currentPinCode, #currentResidencePhn')
                .on('change focusout', updatePermanentAddress);

            $('#colorCheck2').trigger('change');

            // tab show hide on the basis of guarantor/co_applicant
            $('input[name="OtherDetail[common_data][guar_type]"]').on('change', function() {
                var selectedValue = $(this).val();
                if (selectedValue === '1') {
                    $("#guarantor_tab").show();
                    $("#coo-present").css('display', 'block');
                    $("#guara").css('display', 'block');
                    $('.hid_g_field').show();
                } else {
                    $("#guarantor_tab").hide();
                    $("#coo-present").css('display', 'block');
                    $("#guara").css('display', 'block');
                    $('.hid_g_field').hide();
                }
            });

            $('input[name="OtherDetail[common_data][co_type]"]').on('change', function() {
                var selectedValue = $(this).val();
                if (selectedValue === '1') {
                    $("#co_appli_tab").show();
                    $("#coo-present").css('display', 'block');
                    $("#guara").css('display', 'block');
                    $('.hide_co_field').show();
                } else {
                    $("#co_appli_tab").hide();
                    $("#coo-present").css('display', 'block');
                    $("#guara").css('display', 'block');
                    $('.hide_co_field').hide();
                }
            });

            // fetch cities
            $('#city-select').select2({
                placeholder: 'Select a city',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('loan.getCities') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
            if (selectedCity) {
                $.ajax({
                    url: '{{ route('loan.getCityByID') }}',
                    data: {
                        id: selectedCity
                    },
                    dataType: 'json',
                    success: function(data) {
                        var option = new Option(data.name, data.id, true, true);
                        $('#city-select').append(option).trigger('change');
                        permaCity = data.name;
                        updatePermanentAddress();
                    }
                });
            }
            $('#city-select').on('select2:select', function(e) {
                var data = e.params.data;
                permaCity = data.text;
                updatePermanentAddress();
            });

            // fetch states
            $('#state-select').select2({
                placeholder: 'Select a state',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('loan.getStates') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
            if (selectedState) {
                $.ajax({
                    url: '{{ route('loan.getStateByID') }}',
                    data: {
                        id: selectedState
                    },
                    dataType: 'json',
                    success: function(data) {
                        var option = new Option(data.name, data.id, true, true);
                        $('#state-select').append(option).trigger('change');
                        permaState = data.name;
                        updatePermanentAddress();
                    }
                });
            }
            $('#state-select').on('select2:select', function(e) {
                var data = e.params.data;
                permaState = data.text;
                updatePermanentAddress();
            });

            // employer city/state
            $('#employer-city-select').select2({
                placeholder: 'Select a city',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('loan.getCities') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            if (selectedEmployerCity) {
                $.ajax({
                    url: '{{ route('loan.getCityByID') }}',
                    data: {
                        id: selectedEmployerCity
                    },
                    dataType: 'json',
                    success: function(data) {
                        var option = new Option(data.name, data.id, true, true);
                        $('#employer-city-select').append(option).trigger('change');
                    }
                });
            }
            $('#employer-state-select').select2({
                placeholder: 'Select a state',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('loan.getStates') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
            if (selectedEmployerState) {
                $.ajax({
                    url: '{{ route('loan.getStateByID') }}',
                    data: {
                        id: selectedEmployerState
                    },
                    dataType: 'json',
                    success: function(data) {
                        var option = new Option(data.name, data.id, true, true);
                        $('#employer-state-select').append(option).trigger('change');
                    }
                });
            }

            // other city/state
            $('#other-city-select').select2({
                placeholder: 'Select a city',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('loan.getCities') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
            if (selectedOtherCity) {
                $.ajax({
                    url: '{{ route('loan.getCityByID') }}',
                    data: {
                        id: selectedOtherCity
                    },
                    dataType: 'json',
                    success: function(data) {
                        var option = new Option(data.name, data.id, true, true);
                        $('#other-city-select').append(option).trigger('change');
                    }
                });
            }
            $('#other-state-select').select2({
                placeholder: 'Select a state',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('loan.getStates') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
            if (selectedOtherState) {
                $.ajax({
                    url: '{{ route('loan.getStateByID') }}',
                    data: {
                        id: selectedOtherState
                    },
                    dataType: 'json',
                    success: function(data) {
                        var option = new Option(data.name, data.id, true, true);
                        $('#other-state-select').append(option).trigger('change');
                    }
                });
            }



            $('#guar-city-select').select2({
                placeholder: 'Select a city',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('loan.getCities') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
            if (selectedGuarCity) {
                $.ajax({
                    url: '{{ route('loan.getCityByID') }}',
                    data: {
                        id: selectedGuarCity
                    },
                    dataType: 'json',
                    success: function(data) {
                        var option = new Option(data.name, data.id, true, true);
                        $('#guar-city-select').append(option).trigger('change');
                    }
                });
            }
            $('#guar-state-select').select2({
                placeholder: 'Select a state',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('loan.getStates') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
            if (selectedGuarState) {
                $.ajax({
                    url: '{{ route('loan.getStateByID') }}',
                    data: {
                        id: selectedGuarState
                    },
                    dataType: 'json',
                    success: function(data) {
                        var option = new Option(data.name, data.id, true, true);
                        $('#guar-state-select').append(option).trigger('change');
                    }
                });
            }

            // permanent address
            $('#p-city-select').select2({
                placeholder: 'Select a city',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('loan.getCities') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            if (selectedPCity) {
                $.ajax({
                    url: '{{ route('loan.getCityByID') }}',
                    data: {
                        id: selectedPCity
                    },
                    dataType: 'json',
                    success: function(data) {
                        var option = new Option(data.name, data.id, true, true);
                        $('#p-city-select').append(option).trigger('change');
                    }
                });
            }
            $('#p-state-select').select2({
                placeholder: 'Select a state',
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route('loan.getStates') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.id,
                                    text: item.name
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
            if (selectedPState) {
                $.ajax({
                    url: '{{ route('loan.getStateByID') }}',
                    data: {
                        id: selectedPState
                    },
                    dataType: 'json',
                    success: function(data) {
                        var option = new Option(data.name, data.id, true, true);
                        $('#p-state-select').append(option).trigger('change');
                    }
                });
            }

            if ($('#colorCheck2').is(':checked')) {
                $('#city-select').on('change', function() {
                    var selectedCityId = $(this).val(); // Get the selected city ID
                    var selectedCityText = $(this).find('option:selected')
                        .text(); // Get the selected city name

                    // Set the value and text of #p-city-select using Select2
                    var newOption = new Option(selectedCityText, selectedCityId, true, true);
                    $('#p-city-select').append(newOption).trigger(
                        'change'); // Append and trigger change to update Select2
                });

                $('#state-select').on('change', function() {
                    var selectedStateId = $(this).val(); // Get the selected city ID
                    var selectedStateText = $(this).find('option:selected')
                        .text(); // Get the selected city name

                    // Set the value and text of #p-state-select using Select2
                    var newOptionState = new Option(selectedStateText, selectedStateId, true, true);
                    $('#p-state-select').append(newOptionState).trigger(
                        'change'); // Append and trigger change to update Select2
                });
            }

        });

        // validate age should greater than 18
        function validateAge() {
            var dobInput = document.getElementById('dob');
            var dobValue = dobInput.value;
            var messageElement = document.getElementById('age-message');
            var validateAge = document.getElementById('validate_age');
            var ageInput = document.getElementById('age');

            if (!dobValue) {
                messageElement.textContent = '';
                if (ageInput) {
                    ageInput.value = '';
                }
                return;
            }

            var dob = new Date(dobValue);
            var today = new Date();

            if (isNaN(dob.getTime())) {
                messageElement.textContent = 'Please enter a valid date.';
                if (ageInput) {
                    ageInput.value = '';
                }
                return;
            }

            var age = today.getFullYear() - dob.getFullYear();
            var monthDiff = today.getMonth() - dob.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }

            if (age < 18) {
                validateAge.textContent = '';
                messageElement.textContent = 'You must be at least 18 years old.';
                if (ageInput) {
                    ageInput.value = age;
                }
            } else {
                messageElement.textContent = '';
                if (ageInput) {
                    ageInput.value = age;
                }
            }
        }

        // validate length of a field
        function validateLength(inputSelector, errorSelector, maxLength) {
            var inputElement = $(inputSelector);
            var inputValue = inputElement.val();

            if (inputValue.length > maxLength) {
                $(errorSelector).text(`The value must be less than ${maxLength + 1} characters.`);
            } else {
                $(errorSelector).text('');
            }
        }

        var widthInPixels = (25 / 25.4) * 96;
        var heightInPixels = (35 / 25.4) * 96;

        function previewImage(event) {
            var fileInput = event.target;
            var file = fileInput.files[0];

            var validImageTypes = ['image/jpeg', 'image/png'];
            if (!file || !validImageTypes.includes(file.type)) {
                alert("Please upload a valid image file (jpg, jpeg, png).");
                fileInput.value = ""; // Clear the file input
                return;
            }

            if (file.size > 1048576) {
                alert("The image size exceeds 1MB. Please upload an image with a smaller size.");
                fileInput.value = ""; // Clear the file input
                return;
            }

            var img = new Image();
            img.onload = function() {
                // if (img.width !== Math.round(widthInPixels) || img.height !== Math.round(heightInPixels)) {
                //     alert("The image dimensions must be exactly 25mm x 35mm.");
                //     fileInput.value = "";
                //     return;
                // }

                var output = document.getElementById('uploadedImage');
                var placeholderText = document.getElementById('hide-size');

                output.src = img.src;
                output.style.display = 'block';
                placeholderText.style.display = 'none';
            };

            var reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }


        function previewGuaraImage(event) {
            var fileInput = event.target;
            var file = event.target.files[0];

            var validImageTypes = ['image/jpeg', 'image/png'];
            if (!file || !validImageTypes.includes(file.type)) {
                alert("Please upload a valid image file (jpg, jpeg, png).");
                fileInput.value = "";
                return;
            }

            if (file.size > 1048576) {
                alert("The image size exceeds 1MB. Please upload an image with a smaller size.");
                fileInput.value = "";
                return;
            }

            var img = new Image();
            img.onload = function() {
                // if (img.width !== Math.round(widthInPixels) || img.height !== Math.round(heightInPixels)) {
                //     alert("The image dimensions must be exactly 25mm x 35mm.");
                //     fileInput.value = "";
                //     return;
                // }

                var output = document.getElementById('uploadedGuaranImage');
                var placeholderText = document.getElementById('hide-size_guar');

                output.src = img.src;
                output.style.display = 'block';
                placeholderText.style.display = 'none';
            };

            var reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function previewImageCo(event) {
            var fileInput = event.target;
            var file = event.target.files[0];

            var validImageTypes = ['image/jpeg', 'image/png'];
            if (!file || !validImageTypes.includes(file.type)) {
                alert("Please upload a valid image file (jpg, jpeg, png).");
                fileInput.value = "";
                return;
            }

            if (file.size > 1048576) {
                alert("The image size exceeds 1MB. Please upload an image with a smaller size.");
                fileInput.value = "";
                return;
            }

            var img = new Image();
            img.onload = function() {
                // if (img.width !== Math.round(widthInPixels) || img.height !== Math.round(heightInPixels)) {
                //     alert("The image dimensions must be exactly 25mm x 35mm.");
                //     fileInput.value = "";
                //     return;
                // }

                var output = document.getElementById('uploadedImageco');
                var placeholderText = document.getElementById('hide-size_co');

                output.src = img.src;
                output.style.display = 'block';
                placeholderText.style.display = 'none';
            };

            var reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        @if (!isset($editData))
            $('form').on('submit', function(e) {
                var formData = [];
                var formData1 = [];
                var formData2 = [];
                var formData3 = [];
                var formData4 = [];
                var formData5 = [];
                var formData6 = [];
                var formData7 = [];
                var formData8 = [];
                var formData9 = [];
                var formData10 = [];
                var formData11 = [];
                var formData12 = [];

                $('#table-body').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData[name] = $(this).val();
                    });

                    formData.push(rowData);
                });

                $('#table-body-loan-indiv').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData1 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData1[name] = $(this).val();
                    });

                    formData1.push(rowData1);
                });

                $('#table-body-co-applicant-insu-poli').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData2 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData2[name] = $(this).val();
                    });

                    formData2.push(rowData2);
                });

                $('#table-body-co-applicant-deposit-term').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData3 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData3[name] = $(this).val();
                    });

                    formData3.push(rowData3);
                });

                $('#table-body-co-applicant-asset').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData4 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData4[name] = $(this).val();
                    });

                    formData4.push(rowData4);
                });

                $('#table-body-legal-heirs').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData5 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData5[name] = $(this).val();
                    });

                    formData5.push(rowData5);
                });

                $('#table-body-proposed-loan').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData6 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData6[name] = $(this).val();
                    });

                    formData6.push(rowData6);
                });

                $('#table-body-term-deposit').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData7 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData7[name] = $(this).val();
                    });

                    formData7.push(rowData7);
                });

                $('#table-body-moveable-assets').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData8 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData8[name] = $(this).val();
                    });

                    formData8.push(rowData8);
                });

                $('#table-body-guarntr-insu-poli').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData9 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData9[name] = $(this).val();
                    });

                    formData9.push(rowData9);
                });

                $('#table-body-guarntr-deposit-term').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData10 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData10[name] = $(this).val();
                    });

                    formData10.push(rowData10);
                });

                $('#table-body-guarntr-asset').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData11 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData11[name] = $(this).val();
                    });

                    formData11.push(rowData11);
                });

                $('#table-body-guarntr-heirs').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData12 = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData12[name] = $(this).val();
                    });

                    formData12.push(rowData12);
                });

                localStorage.setItem('formData', JSON.stringify(formData));
                localStorage.setItem('formData1', JSON.stringify(formData1));
                localStorage.setItem('formData2', JSON.stringify(formData2));
                localStorage.setItem('formData3', JSON.stringify(formData3));
                localStorage.setItem('formData4', JSON.stringify(formData4));
                localStorage.setItem('formData5', JSON.stringify(formData5));
                localStorage.setItem('formData6', JSON.stringify(formData6));
                localStorage.setItem('formData7', JSON.stringify(formData7));
                localStorage.setItem('formData8', JSON.stringify(formData8));
                localStorage.setItem('formData9', JSON.stringify(formData9));
                localStorage.setItem('formData10', JSON.stringify(formData10));
                localStorage.setItem('formData11', JSON.stringify(formData11));
                localStorage.setItem('formData12', JSON.stringify(formData12));
            });
        @endif

        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('.text-danger') && document.querySelector('#Proposed')) {
                var tabTriggerEl = document.querySelector('#Proposed-tab');
                var tab = new bootstrap.Tab(tabTriggerEl);
                tab.show();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->any())
                 @foreach ($errors->all() as $error)
            toastr.error('{{$error}}', 'Error');
@endforeach


            @endif
        });

        function get_series_details(selectedValue = 0) {
            // if(selectedValue > 0){
            //     var selectedSeries = selectedValue;
            // }else{
            //     var selectedSeries = document.getElementById("series").value;
            // }
            // $.ajax({
            //     url: '{{ url('get_voucher_no') }}/'+selectedSeries,
            //     type: 'GET',
            //     success: function(data) {
            //         if (data.type=="Auto") {
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

        document.addEventListener('DOMContentLoaded', function() {
            const appliNoInput = document.getElementById('appli_no');
            const errorMessage = document.getElementById('appli_no_error_message');
            const appli_span = document.getElementById('appli_span')

            function validateAppliNo() {
                const value = appliNoInput.value.trim();

                // Check if the string starts with a negative sign
                if (value.startsWith('-')) {
                    appli_span.textContent = '';
                    errorMessage.textContent = 'The Applicant number must not start with a negative sign.';
                    return false;
                }

                // Check if the string contains only allowed characters (letters, numbers, and dashes)
                const regex = /^[a-zA-Z0-9-_]+$/;
                if (!regex.test(value)) {
                    appli_span.textContent = '';
                    errorMessage.textContent =
                        'The Applicant number can only contain letters, numbers, dashes and underscores.';
                    return false;
                }

                // If all checks pass, clear the error message
                errorMessage.textContent = '';
                return true;
            }

            // Validate on blur
            appliNoInput.addEventListener('blur', validateAppliNo);
        });


        document.addEventListener('DOMContentLoaded', function() {
            // Function to toggle visibility of fields
            function toggleFields() {
                var selectedStatus = document.querySelector('input[name="marital_status"]:checked').value;
                var elements = document.querySelectorAll('.additional_data');

                elements.forEach(element => {
                    if (selectedStatus === 'single') {
                        element.style.opacity = '0'; // Make the element fully transparent
                        element.style.visibility = 'hidden'; // Hide the element but keep its space
                        element.style.position = 'absolute'; // Remove from normal flow
                        element.style.transform = 'translateY(-9999px)'; // Move out of view
                    } else {
                        element.style.opacity = '1'; // Make the element fully visible
                        element.style.visibility = 'visible'; // Show the element
                        element.style.position = ''; // Reset position
                        element.style.transform = ''; // Reset transform
                    }
                });
            }

            // Initialize visibility based on the selected status
            toggleFields();

            // Add event listener to the radio buttons
            var maritalStatusRadios = document.querySelectorAll('input[name="marital_status"]');
            maritalStatusRadios.forEach(function(radio) {
                radio.addEventListener('change', toggleFields);
            });
        });

        function validatePinCode(input) {
            var pinCode = input.value;
            var regex = /^[1-9][0-9]{5}$/;
            var errorMessageId = input.id + '-error';
            var errorMessage = document.getElementById(errorMessageId);

            if (!errorMessage) {
                // Create a new error message span if it doesn't exist
                errorMessage = document.createElement('span');
                errorMessage.id = errorMessageId;
                errorMessage.className = 'text-danger';
                input.parentNode.appendChild(errorMessage);
            }

            if (pinCode === '' || regex.test(pinCode)) {
                errorMessage.textContent = '';
                errorMessage.style.display = 'none';
            } else {
                errorMessage.textContent = 'The PIN code must be a 6-digit number starting with a non-zero digit.';
                errorMessage.style.display = 'block';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Function to update the total value
            function updateTotal() {
                const plotLand = parseFloat(document.getElementById('l_in_plot').value) || 0;
                const agriLand = parseFloat(document.getElementById('l_in_agri').value) || 0;
                const houseGodowns = parseFloat(document.getElementById('l_in_house').value) || 0;
                const others = parseFloat(document.getElementById('l_in_others').value) || 0;

                const total = plotLand + agriLand + houseGodowns + others;
                document.getElementById('l_in_total').value = total;
            }

            function updateGTotal() {
                const plotLand = parseFloat(document.getElementById('g_in_plot').value) || 0;
                const agriLand = parseFloat(document.getElementById('g_in_agri').value) || 0;
                const houseGodowns = parseFloat(document.getElementById('g_in_house').value) || 0;
                const others = parseFloat(document.getElementById('g_in_others').value) || 0;

                const total = plotLand + agriLand + houseGodowns + others;
                document.getElementById('g_in_total').value = total;
            }

            function updateOTotal() {
                const plotLand = parseFloat(document.getElementById('o_in_plot').value) || 0;
                const agriLand = parseFloat(document.getElementById('o_in_agri').value) || 0;
                const houseGodowns = parseFloat(document.getElementById('o_in_house').value) || 0;
                const others = parseFloat(document.getElementById('o_in_others').value) || 0;

                const total = plotLand + agriLand + houseGodowns + others;
                document.getElementById('o_in_total').value = total;
            }

            // Add event listeners to input fields
            document.getElementById('l_in_plot').addEventListener('input', updateTotal);
            document.getElementById('l_in_agri').addEventListener('input', updateTotal);
            document.getElementById('l_in_house').addEventListener('input', updateTotal);
            document.getElementById('l_in_others').addEventListener('input', updateTotal);

            document.getElementById('g_in_plot').addEventListener('input', updateGTotal);
            document.getElementById('g_in_agri').addEventListener('input', updateGTotal);
            document.getElementById('g_in_house').addEventListener('input', updateGTotal);
            document.getElementById('g_in_others').addEventListener('input', updateGTotal);

            document.getElementById('o_in_plot').addEventListener('input', updateOTotal);
            document.getElementById('o_in_agri').addEventListener('input', updateOTotal);
            document.getElementById('o_in_house').addEventListener('input', updateOTotal);
            document.getElementById('o_in_others').addEventListener('input', updateOTotal);
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

        function checkDecimalValue(input) {
            if (input.value.includes('.')) {
                alert("Decimal values are not allowed.");
                input.value = "";
            }
        }

        document.getElementById('interest_rate_float').addEventListener('input', function() {
            checkDecimalValue(this);
        });

        document.getElementById('margin_float').addEventListener('input', function() {
            checkDecimalValue(this);
        });


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

            if (page != null && page == 'view_detail') {
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

            $(document).ready(function(){
                $('.remove-disable').removeAttr('disabled'); $('.remove-disable').removeAttr('readonly');
                $('.remove-disable').removeAttr('readonly')
            })
        });
    </script>
@endsection
