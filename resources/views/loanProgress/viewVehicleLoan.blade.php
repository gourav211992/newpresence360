@extends('layouts.app')
@php
    use Illuminate\Support\Facades\Auth;

    // Get the logged-in user, or set to null if no one is logged in
    $loggedInUser = Auth::check() ? Auth::user()->id : null;
@endphp
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

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

        #uploadedImage {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
            /* Hide the image by default */
        }

        #hide-size {
            font-size: 14px;
            color: #555;
            margin: 0;
            position: absolute;
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

                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</button>

                            @if ($buttons['return'])
                                <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#return"
                                    data-bs-toggle="modal"><i data-feather="refresh-cw"></i> Return</button>
                            @endif

                            @if ($buttons['reject'])
                                <button class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-target="#reject"
                                    data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</button>
                            @endif

                            @if ($buttons['update_appraisal'])
                                {{-- @if (isset($module) && $module == 'appraisal') --}}
                                <a href="{{ route('loanAppraisal.create', $vehicleLoan->id) }}"
                                    class="btn btn-success btn-sm mb-50 mb-sm-0"><i data-feather="edit-3"></i> Update
                                    Appraisal</a>
                            @endif
                            @if ($buttons['approve'])
                            <button onclick="submitForm()"
                                class="btn btn-success btn-sm mb-50 mb-sm-0"><i data-feather="refresh-cw"></i>
                                Approved</button>
                        @endif

                            @if ($buttons['proceed'])
                                <button data-bs-toggle="modal" data-bs-target="#viewassesgive"
                                    class="btn btn-success btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i>
                                    Proceed</button>
                            @endif

                            @if ($buttons['accept'])
                                    <button data-bs-toggle="modal" data-bs-target="#accept" class="btn btn-success btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Accept</button>
                                @endif

                                @if ($buttons['fee_paid'])
                                <button data-bs-toggle="modal" data-bs-target="#update-fee"
                                class="btn btn-success btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Fee
                                Paid</button>
                                @endif
                                @if ($buttons['legal_doc'])
                                <button data-bs-toggle="modal" data-bs-target="#upload-letter"
                                class="btn btn-success btn-sm mb-50 mb-sm-0"><i data-feather="upload"></i> Upload Legal
                                Doc</button>
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
                            @else
                                @if (!isset($view_detail))
                                    <button class="btn btn-outline-primary btn-sm submission_val" data-val="draft"
                                        form="home-loan-createUpdate"><i data-feather="save"></i> Save as Draft</button>
                                    <button data-bs-toggle="modal" data-bs-target="#disclaimer" data-val="submitted"
                                        class="btn btn-primary btn-sm submission_val" form="home-loan-createUpdate"><i
                                            data-feather="check-circle"></i> Proceed</button>
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
                    <form id="vehicle-loan-createUpdate" method="POST"
                        action="{{ route('loan.vehicle.loan-createUpdate') }}" enctype="multipart/form-data">
                        @csrf
                        @if (isset($vehicleLoan->id))
                            <input type="hidden" name="edit_loanId" value="{{ $vehicleLoan->id }}">
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body customernewsection-form">

                                        <!-- Page Heading -->
                                        <div class="text-center new-applicayion-help-txt  mb-1 pb-25">
                                            <h4 class="purchase-head"><span>Application for Vehicle Loan</span></h4>
                                            <h6 class="mt-2">({{ $vehicleLoan->name ?? 'N/A' }} |
                                                {{ $overview->term_loan ?? $vehicleLoan->loan_amount }} |
                                                {{ $overview->update_at ?? $vehicleLoan->created_at ? $vehicleLoan->created_at->format('d-m-Y') : 'N/A' }})
                                            </h6>
                                        </div>

                                        @if (!empty($overview))
                                            <div class="bg-light-success rounded border p-1 mb-4">

                                                <div class="row">

                                                    <div class="col-md-7">

                                                        @if ($vehicleLoan->approvalStatus != 'draft')
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

                                                                $specialStatuses = [
                                                                    'approval_not_required' => ['appraisal', 'assessment','approved'],
                                                                    'partially_approved' => ['appraisal', 'assessment', 'approved'],
                                                                ];

                                                                if ($vehicleLoan->approvalStatus == 'submitted') {
            $activeStatuses = []; // All statuses inactive
        } elseif (array_key_exists($vehicleLoan->approvalStatus, $specialStatuses)) {
                                                                    $activeStatuses = $specialStatuses[$vehicleLoan->approvalStatus];
                                                                } else {
                                                                    $currentStatusIndex = $statuses[$vehicleLoan->approvalStatus] ?? count($statuses);
                                                                    $activeStatuses = array_keys(array_filter($statuses, fn($index) => $index <= $currentStatusIndex));
                                                                }
                                                            @endphp

                                                            <ul class="nav nav-tabs mb-0 mt-25 custapploannav customrapplicationstatus" role="tablist">
                                                                @foreach ($statuses as $status => $index)
                                                                    <li class="nav-item">
                                                                        <p class="{{ in_array($status, $activeStatuses) ? 'statusactive' : '' }}">
                                                                            <i data-feather="check"></i>
                                                                        </p>
                                                                        <a class="nav-link {{ $vehicleLoan->approvalStatus == $status ? 'active' : '' }}"
                                                                           href="#{{ ucfirst(str_replace(' ', '', $status)) }}">
                                                                            {{ ucwords(str_replace(['-', '_'], ' ', $status)) }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                        @endif

                                                        <div class="tab-content  bg-white">

                                                            <div class="tab-pane active" id="Appraisal">
                                                                <div>
                                                                    <ul class="nav nav-tabs border-bottom mt-25 loandetailhistory"
                                                                        role="tablist">
                                                                        <li class="nav-item">
                                                                            <a class="nav-link active"
                                                                                data-bs-toggle="tab" href="#Overview">
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
                                                                                                    <td>{{ $overview->updated_at ? $overview->updated_at->format('d-m-Y') : 'N/A' }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>2</td>
                                                                                                    <td>Name of Unit</td>
                                                                                                    <td>{{ $overview->unit_name ?? 'N/A' }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>3</td>
                                                                                                    <td>Name of Proprietor
                                                                                                    </td>
                                                                                                    <td>{{ $overview->proprietor_name ?? 'N/A' }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>4</td>
                                                                                                    <td>Address</td>
                                                                                                    <td>{{ $overview->address ?? 'N/A' }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>5</td>
                                                                                                    <td>CIBIL Score</td>
                                                                                                    <td>{{ $overview->cibil_score ?? 'N/A' }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>6</td>
                                                                                                    <td>Project Cost</td>
                                                                                                    <td>{{ App\Helpers\Helper::formatIndianNumber($overview->project_cost) ?? 'N/A' }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>7</td>
                                                                                                    <td>Term Loan</td>
                                                                                                    <td>{{ App\Helpers\Helper::formatIndianNumber($overview->term_loan) ?? 'N/A' }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>8</td>
                                                                                                    <td>Promotor's
                                                                                                        Contribution</td>
                                                                                                    <td>{{ App\Helpers\Helper::formatIndianNumber($overview->promotor_contribution) ?? 'N/A' }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>9</td>
                                                                                                    <td>Interest Rate (P.A)
                                                                                                    </td>
                                                                                                    <td>{{ $overview->interest_rate ?? 'N/A' }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>10</td>
                                                                                                    <td>Loan Period</td>
                                                                                                    <td>{{ $overview->loan_period ?? 'N/A' }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>11</td>
                                                                                                    <td>Repayment Type</td>
                                                                                                    <td>{{ [1 => 'Yearly', 2 => 'Half-Yearly', 3 => 'Quarterly', 4 => 'Monthly'][$overview->repayment_type] ?? 'N/A' }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>12</td>
                                                                                                    <td>No. of
                                                                                                        Installment(s)</td>
                                                                                                    <td>{{ $overview->no_of_installments ?? 'N/A' }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>13</td>
                                                                                                    <td>Repayment Start
                                                                                                        After</td>
                                                                                                    <td>{{ $overview->repayment_start_after ?? 'N/A' }}
                                                                                                    </td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>14</td>
                                                                                                    <td>Repayment Start
                                                                                                        Period</td>
                                                                                                    <td>{{ $overview->repayment_start_period ?? 'N/A' }}
                                                                                                    </td>
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
                                                                                                @if (!empty($overview->dpr))
                                                                                                    @php $i = 1; @endphp
                                                                                                    @foreach ($overview->dpr as $dpr)
                                                                                                        <tr>
                                                                                                            <td>{{ $i++ }}
                                                                                                            </td>
                                                                                                            <td>{{ $dpr->dpr->field_name }}
                                                                                                            </td>
                                                                                                            <td>{{ $dpr->dpr_value }}
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                    @endforeach
                                                                                                @else
                                                                                                    <tr>
                                                                                                        <td colspan="3"
                                                                                                            class="text-center">
                                                                                                            No Data Found
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                @endif
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
                                                                                                @if (!empty($overview->disbursal))
                                                                                                    @php
                                                                                                        $i = 1;
                                                                                                        $loanAmount =
                                                                                                            $vehicleLoan->loan_amount;
                                                                                                    @endphp
                                                                                                    @foreach ($overview->disbursal as $disbursal)
                                                                                                        <tr>
                                                                                                            <td>{{ $i++ }}
                                                                                                            </td>
                                                                                                            <td>{{ App\Helpers\Helper::formatIndianNumber($loanAmount) }}
                                                                                                            </td>
                                                                                                            <td>{{ App\Helpers\Helper::formatIndianNumber($disbursal->milestone) }}
                                                                                                            </td>

                                                                                                            <td>{{ App\Helpers\Helper::formatIndianNumber($disbursal->amount) }}
                                                                                                            </td>
                                                                                                            <td>{{ $disbursal->remarks }}
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                        @php
                                                                                                            $loanAmount -=
                                                                                                                App\Helpers\Helper::removeCommas($disbursal->amount);
                                                                                                        @endphp
                                                                                                    @endforeach
                                                                                                @else
                                                                                                    <tr>
                                                                                                        <td colspan="5"
                                                                                                            class="text-center">
                                                                                                            No Data Found
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
                                                                                                @if (!empty($overview->recovery))
                                                                                                    @php
                                                                                                        $i = 1;
                                                                                                        $totalInterest = 0;
                                                                                                        $totalRepayment = 0;
                                                                                                    @endphp
                                                                                                    @foreach ($overview->recovery as $recovery)
                                                                                                        <tr>
                                                                                                            <td>{{ $i++ }}
                                                                                                            </td>
                                                                                                            <td>{{ $recovery->year }}
                                                                                                            </td>
                                                                                                            <td>{{ number_format($recovery->start_amount, 2, '.', ',') }}
                                                                                                            </td>
                                                                                                            <td>{{ number_format($recovery->interest_amount, 2, '.', ',') }}
                                                                                                            </td>
                                                                                                            <td>{{ number_format($recovery->repayment_amount, 2, '.', ',') }}
                                                                                                            </td>
                                                                                                            <td>{{ number_format($recovery->end_amount, 2, '.', ',') }}
                                                                                                            </td>
                                                                                                        </tr>
                                                                                                        @php
                                                                                                            $totalInterest +=
                                                                                                                $recovery->interest_amount;
                                                                                                            $totalRepayment +=
                                                                                                                $recovery->repayment_amount;
                                                                                                        @endphp
                                                                                                    @endforeach
                                                                                                    <tr>
                                                                                                        <td>&nbsp;</td>
                                                                                                        <td>&nbsp;</td>
                                                                                                        <td
                                                                                                            class="fw-bolder text-dark">
                                                                                                            Total</td>
                                                                                                        <td
                                                                                                            class="fw-bolder text-dark">
                                                                                                            {{ $totalInterest }}
                                                                                                        </td>
                                                                                                        <td
                                                                                                            class="fw-bolder text-dark">
                                                                                                            {{ $totalRepayment }}
                                                                                                        </td>
                                                                                                        <td>&nbsp;</td>
                                                                                                    </tr>
                                                                                                @else
                                                                                                    <tr>
                                                                                                        <td colspan="6"
                                                                                                            class="text-center">
                                                                                                            No Data Found
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                @endif
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
                                                                <a class="nav-link" data-bs-toggle="tab"
                                                                    href="#Collections">
                                                                    Recovery
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" data-bs-toggle="tab" href="#logs">
                                                                    Logs
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" data-bs-toggle="tab"
                                                                    href="#documents">
                                                                    Documents
                                                                </a>
                                                            </li>
                                                        </ul>

                                                        <div class="tab-content mt-1">

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
                                                                                                        @if (json_decode($loan_disbursement_list->dis_milestone, true))
                                                                                                            @foreach (json_decode($loan_disbursement_list->dis_milestone, true) as $loan_disbursement_dis_milestone)
                                                                                                                {{ $loan_disbursement_dis_milestone['name'] }},
                                                                                                            @endforeach
                                                                                                        @else
                                                                                                            {{ $loan_disbursement_list->dis_milestone }},
                                                                                                        @endif
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
                                                                                        {!! $logs !!}
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
                                                                                    <tr>
                                                                                        <td>1</td>
                                                                                        <td>30-07-2021</td>
                                                                                        <td>1000000</td>
                                                                                        <td>400000</td>
                                                                                        <td>300000</td>
                                                                                        <td>100000</td>
                                                                                        <td>600000</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td>2</td>
                                                                                        <td>30-07-2022</td>
                                                                                        <td>1000000</td>
                                                                                        <td>400000</td>
                                                                                        <td>300000</td>
                                                                                        <td>100000</td>
                                                                                        <td>600000</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td>3</td>
                                                                                        <td>30-07-2023</td>
                                                                                        <td>1000000</td>
                                                                                        <td>400000</td>
                                                                                        <td>300000</td>
                                                                                        <td>100000</td>
                                                                                        <td>600000</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td>4</td>
                                                                                        <td>30-07-2024</td>
                                                                                        <td>1000000</td>
                                                                                        <td>400000</td>
                                                                                        <td>300000</td>
                                                                                        <td>100000</td>
                                                                                        <td>600000</td>
                                                                                    </tr>
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
                                                                                        <td class="text-nowrap">10-10-2024
                                                                                        </td>
                                                                                        <td>Appraisal</td>
                                                                                        <td>Description will come here</td>
                                                                                        <td>Aniket Singh</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td>2</td>
                                                                                        <td class="text-nowrap">10-10-2024
                                                                                        </td>
                                                                                        <td>Assessment</td>
                                                                                        <td>Description will come here</td>
                                                                                        <td>Deewan Singh</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td>3</td>
                                                                                        <td class="text-nowrap">10-10-2024
                                                                                        </td>
                                                                                        <td>Approved</td>
                                                                                        <td>Description will come here</td>
                                                                                        <td>Deewan Singh</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td>4</td>
                                                                                        <td class="text-nowrap">10-10-2024
                                                                                        </td>
                                                                                        <td>Sansactioned Letter</td>
                                                                                        <td><a href="#"><i
                                                                                                    data-feather="download"
                                                                                                    class="me-50"></i></a>
                                                                                            Description will come here</td>
                                                                                        <td>Deewan Singh</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td>5</td>
                                                                                        <td>10-10-2024</td>
                                                                                        <td>Processing Fee</td>
                                                                                        <td>Description will come here</td>
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
                                                                                        <td>Description will come here</td>
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
                                        @endif

                                        <!-- Vehicle Loan Data -->
                                        <div class="row">


                                            <div class="col-md-9 order-2 order-sm-1">

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label mb-sm-1">Loan Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="number" name="loan_amount"
                                                            value="{{ old('loan_amount', $vehicleLoan->loan_amount ?? '') }}"
                                                            class="form-control" />
                                                        <p class="voucehrinvocetxt m-0"><i></i></p>
                                                        @error('loan_amount')
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
                                                        $fullName = old('name', $vehicleLoan->name ?? '');
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

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Email<span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class="input-group input-group-merge">
                                                            <span class="input-group-text" id="basic-addon5"><i
                                                                    data-feather='mail'></i></span>
                                                            <input type="email" name="ve_email" id="email_no"
                                                                value="{{ old('ve_email', $vehicleLoan->email ?? '') }}"
                                                                class="form-control" placeholder="">
                                                            @error('ve_email')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Address</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <textarea type="text" name="address" class="form-control">{{ old('address', $vehicleLoan->address ?? '') }}</textarea>
                                                    </div>
                                                </div>


                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Telephone</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class="input-group input-group-merge">
                                                            <span class="input-group-text" id="basic-addon5"><i
                                                                    data-feather='smartphone'></i></span>
                                                            <input type="number" name="mobile"
                                                                value="{{ old('mobile', $vehicleLoan->mobile ?? '') }}"
                                                                class="form-control">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Telex</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="number" name="telex"
                                                            value="{{ old('telex', $vehicleLoan->telex ?? '') }}"
                                                            class="form-control" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Constitution<span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" name="constitution"
                                                            value="{{ old('constitution', $vehicleLoan->constitution ?? '') }}"
                                                            class="form-control" />
                                                        @error('constitution')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                @php
                                                    $scheduledTribe = old(
                                                        'scheduled_tribe',
                                                        $vehicleLoan->scheduled_tribe ?? '1',
                                                    );
                                                    $selectedPartner = old('partner', $vehicleLoan->partner ?? '1');
                                                    $selectedPartner_ship = old(
                                                        'partner_ship',
                                                        $vehicleLoan->partner_ship ?? '0',
                                                    );
                                                @endphp
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">State whether any of the Promoters belog
                                                            to Scheduled Caste/Scheduled Tribe</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Yes"
                                                                    name="scheduled_tribe" class="form-check-input"
                                                                    value="1"
                                                                    {{ $scheduledTribe === '1' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Yes">Yes</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="No"
                                                                    name="scheduled_tribe" class="form-check-input"
                                                                    value="0"
                                                                    {{ $scheduledTribe === '0' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="No">No</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Proprietor/Partner</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Proprietor" name="Proprietor"
                                                                    class="form-check-input" value="1"
                                                                    {{ $selectedPartner === '1' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Proprietor">Proprietor</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Partner" name="Proprietor"
                                                                    class="form-check-input" value="0"
                                                                    {{ $selectedPartner === '0' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Partner">Partner</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Proprietory/Partnership</label>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Proprietory" name="Partnership"
                                                                    class="form-check-input" value="1"
                                                                    {{ $selectedPartner_ship === '1' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Proprietory">Proprietory</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Partnership" name="Partnership"
                                                                    class="form-check-input" value="0"
                                                                    {{ $selectedPartner_ship === '0' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Partnership">Partnership</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>




                                            </div>

                                            <div class="col-md-3 order-1 order-sm-2 border-start mb-2">

                                                <div>
                                                    @if (isset($vehicleLoan) && !empty($vehicleLoan->image))
                                                        <div class="appli-photobox">

                                                            <img id="uploadedImage"
                                                                src="{{ asset('storage/' . $vehicleLoan->image) }}"
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
                                                            value="{{ old('stored_image', $vehicleLoan->image ?? '') }}">
                                                        <input type="file" name="image"
                                                            value="{{ old('image', $vehicleLoan->image ?? '') }}"
                                                            class="" onchange="previewImage(event)">
                                                    </div>

                                                </div>



                                            </div>


                                            <div class="col-md-12 order-3">
                                                <div class="table-responsive mt-1">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Name</th>
                                                                <th>Address</th>
                                                                <th>Father's Name</th>
                                                                <th>Age Academic Qualification</th>
                                                                <th>Capital Investment (Rs.)</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="table-body-vehicle-loan">
                                                            <tr>
                                                                <td id="row-number-vehicle-loan">1</td>
                                                                <td><input type="text" name="vehicleLoan[v_name][]"
                                                                        class="form-control mw-100"></td>
                                                                <td><input type="text" name="vehicleLoan[v_address][]"
                                                                        class="form-control mw-100"></td>
                                                                <td><input type="text"
                                                                        name="vehicleLoan[v_father_name][]"
                                                                        class="form-control mw-100"></td>
                                                                <td><input type="number" name="vehicleLoan[v_quali][]"
                                                                        class="form-control mw-100"></td>
                                                                <td><input type="number" name="vehicleLoan[v_inves][]"
                                                                        class="form-control mw-100"></td>
                                                                <td><a href="#"
                                                                        class="text-primary add-row-vehicle-loan"
                                                                        id="add-row-vehicle-loan"
                                                                        data-class="add-row-vehicle-loan"><i
                                                                            data-feather="plus-square"
                                                                            class="me-50"></i></a></td>
                                                            </tr>
                                                            @if (isset($vehicleLoan) && $vehicleLoan->dataVehicle && $vehicleLoan->dataVehicle->count() > 0)
                                                                @foreach ($vehicleLoan->dataVehicle as $key => $val)
                                                                    <tr>
                                                                        <td>{{ $key + 2 }}</td>
                                                                        <td><input type="text"
                                                                                name="vehicleLoan[v_name][]"
                                                                                value="{{ $val->name ?? '' }}"
                                                                                class="form-control mw-100"></td>
                                                                        <td><input type="text"
                                                                                name="vehicleLoan[v_address][]"
                                                                                value="{{ $val->address ?? '' }}"
                                                                                class="form-control mw-100"></td>
                                                                        <td><input type="text"
                                                                                name="vehicleLoan[v_father_name][]"
                                                                                value="{{ $val->father_name ?? '' }}"
                                                                                class="form-control mw-100"></td>
                                                                        <td><input type="number"
                                                                                name="vehicleLoan[v_quali][]"
                                                                                value="{{ $val->qualification ?? '' }}"
                                                                                class="form-control mw-100"></td>
                                                                        <td><input type="number"
                                                                                name="vehicleLoan[v_inves][]"
                                                                                value="{{ $val->investment ?? '' }}"
                                                                                class="form-control mw-100"></td>
                                                                        <td><a href="#"
                                                                                class="text-danger @if (isset($vehicleLoan) && isset($editData)) delete-item @endif"><i
                                                                                    data-feather="trash-2"
                                                                                    style="cursor: @if (isset($vehicleLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif


                                                        </tbody>


                                                    </table>
                                                </div>

                                            </div>



                                        </div>





                                        <div class="mt-3">
                                            <div class="step-custhomapp bg-light">
                                                <ul class="nav nav-tabs my-25 custapploannav" role="tablist">

                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-bs-toggle="tab"
                                                            href="#bank">Bank & Security</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#Vehicle">Vehicle
                                                            & Scheme Cost</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#Finance">Finance
                                                            & Loan Security</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab"
                                                            href="#Gauranter">Guarantors & Parties</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab"
                                                            href="#Documentsupload">Documents</a>
                                                    </li>

                                                </ul>

                                            </div>

                                            <div class="tab-content pb-1 px-1">
                                                <div class="tab-pane active" id="bank">

                                                    <div class="row">
                                                        <div class="col-md-6">

                                                            <h5 class="mt-1 mb-2  text-dark"><strong>Bankers
                                                                    Detail</strong></h5>



                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Date of Opening the
                                                                        Account</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="date"
                                                                        name="BankSecurity[common_data][opening_acc]"
                                                                        value="{{ old('BankSecurity.common_data.opening_acc', $vehicleLoan->bankSecurity->opening_acc ?? '') }}"
                                                                        class="form-control past-date" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Nominated Bank Name 1</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="BankSecurity[common_data][bank_name1]"
                                                                        value="{{ old('BankSecurity.common_data.bank_name1', $vehicleLoan->bankSecurity->bank_name1 ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Nominated Bank Address 1
                                                                    </label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea type="text" name="BankSecurity[common_data][bank_addr1]" class="form-control">{{ old('BankSecurity.common_data.bank_addr1', $vehicleLoan->bankSecurity->bank_addr1 ?? '') }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Nominated Bank Name 2</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="BankSecurity[common_data][bank_name2]"
                                                                        value="{{ old('BankSecurity.common_data.bank_name2', $vehicleLoan->bankSecurity->bank_name2 ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Nominated Bank Address 2
                                                                    </label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea type="text" name="BankSecurity[common_data][bank_addr2]" class="form-control">{{ old('BankSecurity.common_data.bank_addr2', $vehicleLoan->bankSecurity->bank_addr2 ?? '') }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Account Nature</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="BankSecurity[common_data][acc_nature]"
                                                                        value="{{ old('BankSecurity.common_data.acc_nature', $vehicleLoan->bankSecurity->acc_nature ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Details of Borrowings from
                                                                        Bank.othersources</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" name="BankSecurity[common_data][borrowing_detail]">{{ old('BankSecurity.common_data.borrowing_detail', $vehicleLoan->bankSecurity->borrowing_detail ?? '') }}</textarea>
                                                                </div>
                                                            </div>


                                                        </div>

                                                        <div class="col-md-6">

                                                            <h5 class="mt-1 mb-2  text-dark"><strong>Security
                                                                    Details</strong></h5>


                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Security Offered</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="BankSecurity[common_data][security_offerd]"
                                                                        value="{{ old('BankSecurity.common_data.security_offerd', $vehicleLoan->bankSecurity->security_offerd ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row  mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Security Description</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <textarea class="form-control" name="BankSecurity[common_data][security_desc]">{{ old('BankSecurity.common_data.security_desc', $vehicleLoan->bankSecurity->security_desc ?? '') }}</textarea>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Market value of
                                                                        Security</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="BankSecurity[common_data][security_market_val]"
                                                                        value="{{ old('BankSecurity.common_data.security_market_val', $vehicleLoan->bankSecurity->security_market_val ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>



                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="tab-pane" id="Vehicle">
                                                    <div class="row">
                                                        <div class="col-md-6">

                                                            <h5 class="mt-1 mb-2  text-dark"><strong>Vehicle
                                                                    Detail</strong></h5>



                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Model</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="VehicleScheme[common_data][model]"
                                                                        value="{{ old('VehicleScheme.common_data.model', $vehicleLoan->vehicleScheme->model ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Make</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="VehicleScheme[common_data][make]"
                                                                        value="{{ old('VehicleScheme.common_data.make', $vehicleLoan->vehicleScheme->make ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">H.P.</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="VehicleScheme[common_data][h_p]"
                                                                        value="{{ old('VehicleScheme.common_data.h_p', $vehicleLoan->vehicleScheme->h_p ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Carrying Capacity</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="text"
                                                                        name="VehicleScheme[common_data][carry_capacity]"
                                                                        value="{{ old('VehicleScheme.common_data.carry_capacity', $vehicleLoan->vehicleScheme->carry_capacity ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>


                                                        </div>

                                                        <div class="col-md-6">

                                                            <h5 class="mt-1 mb-2  text-dark"><strong>Cost of the Scheme
                                                                    (Rs)</strong></h5>


                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Chassis/Car/Vessel</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <select class="form-select"
                                                                        name="VehicleScheme[common_data][classic_vessel]">
                                                                        <option value="">Select</option>
                                                                        <option value="classic"
                                                                            {{ old('VehicleScheme.common_data.classic_vessel', isset($vehicleLoan->vehicleScheme->classic_vessel) ? $vehicleLoan->vehicleScheme->classic_vessel : '') == 'classic' ? 'selected' : '' }}>
                                                                            Chassis</option>
                                                                        <option value="car"
                                                                            {{ old('VehicleScheme.common_data.classic_vessel', isset($vehicleLoan->vehicleScheme->classic_vessel) ? $vehicleLoan->vehicleScheme->classic_vessel : '') == 'car' ? 'selected' : '' }}>
                                                                            Car</option>
                                                                        <option value="vessel"
                                                                            {{ old('VehicleScheme.common_data.classic_vessel', isset($vehicleLoan->vehicleScheme->classic_vessel) ? $vehicleLoan->vehicleScheme->classic_vessel : '') == 'vessel' ? 'selected' : '' }}>
                                                                            Vessel</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Body Building</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="body_building_scheme"
                                                                        name="VehicleScheme[common_data][body_building]"
                                                                        value="{{ old('VehicleScheme.common_data.body_building', $vehicleLoan->vehicleScheme->body_building ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Other Items</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="other_item_scheme"
                                                                        name="VehicleScheme[common_data][other_item]"
                                                                        value="{{ old('VehicleScheme.common_data.other_item', $vehicleLoan->vehicleScheme->other_item ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Spares/Tyres etc.</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="spares_tyres_scheme"
                                                                        name="VehicleScheme[common_data][spares_tyres]"
                                                                        value="{{ old('VehicleScheme.common_data.spares_tyres', $vehicleLoan->vehicleScheme->spares_tyres ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Insurance, Taxes and Other
                                                                        Charges</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="insurance_taxes_scheme"
                                                                        name="VehicleScheme[common_data][insurance_taxes]"
                                                                        value="{{ old('VehicleScheme.common_data.insurance_taxes', $vehicleLoan->vehicleScheme->insurance_taxes ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Preliminary and Pre-operative
                                                                        Expenses</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="pre_operative_exp_scheme"
                                                                        name="VehicleScheme[common_data][pre_operative_exp]"
                                                                        value="{{ old('VehicleScheme.common_data.pre_operative_exp', $vehicleLoan->vehicleScheme->pre_operative_exp ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Working Capital
                                                                        Margin</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="working_c_margin_scheme"
                                                                        name="VehicleScheme[common_data][working_c_margin]"
                                                                        value="{{ old('VehicleScheme.common_data.working_c_margin', $vehicleLoan->vehicleScheme->working_c_margin ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label
                                                                        class="form-label"><strong>Total</strong></label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="total_scheme"
                                                                        name="VehicleScheme[common_data][total]"
                                                                        value="{{ old('VehicleScheme.common_data.total', $vehicleLoan->vehicleScheme->total ?? '') }}"
                                                                        class="form-control" readonly />
                                                                </div>
                                                            </div>



                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="Finance">
                                                    <div class="row">
                                                        <div class="col-md-6">

                                                            <h5 class="mt-1 mb-2  text-dark"><strong>Means of Finance
                                                                    (Rs)</strong></h5>



                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Own Capital</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="own_capi"
                                                                        name="FinanceSecurity[common_data][own_capital]"
                                                                        value="{{ old('FinanceSecurity.common_data.own_capital', $vehicleLoan->financeSecurity->own_capital ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Term Loan from
                                                                        M.I.D.C</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="term_midc"
                                                                        name="FinanceSecurity[common_data][term_midc]"
                                                                        value="{{ old('FinanceSecurity.common_data.term_midc', $vehicleLoan->financeSecurity->term_midc ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Total</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="finance_total"
                                                                        name="FinanceSecurity[common_data][finance_total]"
                                                                        value="{{ old('FinanceSecurity.common_data.finance_total', $vehicleLoan->financeSecurity->finance_total ?? '') }}"
                                                                        class="form-control" readonly />
                                                                </div>
                                                            </div>



                                                        </div>

                                                        <div class="col-md-6">

                                                            <h5 class="mt-1 mb-2  text-dark"><strong>Security for the
                                                                    Loan</strong></h5>



                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Vehicle/s as per 3.0</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="vehicle_sc"
                                                                        name="FinanceSecurity[common_data][vehicle]"
                                                                        value="{{ old('FinanceSecurity.common_data.vehicle', $vehicleLoan->financeSecurity->vehicle ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Collateral Security</label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="collateral_security"
                                                                        name="FinanceSecurity[common_data][collateral_security]"
                                                                        value="{{ old('FinanceSecurity.common_data.collateral_security', $vehicleLoan->financeSecurity->collateral_security ?? '') }}"
                                                                        class="form-control" />
                                                                </div>
                                                            </div>



                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label
                                                                        class="form-label"><strong>Total</strong></label>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <input type="number" id="security_total"
                                                                        name="FinanceSecurity[common_data][security_total]"
                                                                        value="{{ old('FinanceSecurity.common_data.security_total', $vehicleLoan->financeSecurity->security_total ?? '') }}"
                                                                        class="form-control" readonly />
                                                                </div>
                                                            </div>



                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane" id="Gauranter">

                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>1) Guarantors
                                                            and their Net Worth</strong></p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th width="300px">Guarantor Name</th>
                                                                    <th>Address</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>

                                                            <tbody id="table-body-guarantor-party">
                                                                <tr>
                                                                    <td id="row-number-guarantor-party">1</td>
                                                                    <td><input type="text"
                                                                            name="GuarantorParty[guarantor_name][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="GuarantorParty[address][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-guarantor-party"
                                                                            id="add-row-guarantor-party"
                                                                            data-class="add-row-guarantor-party"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>

                                                                @if (isset($vehicleLoan) && $vehicleLoan->netWorth && $vehicleLoan->netWorth->count() > 0)
                                                                    @foreach ($vehicleLoan->netWorth as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorParty[guarantor_name][]"
                                                                                    value="{{ $val->guarantor_name ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorParty[address][]"
                                                                                    value="{{ $val->address ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($vehicleLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($vehicleLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif


                                                            </tbody>


                                                        </table>
                                                    </div>


                                                    <p class="mt-2  text-dark customapplsmallhead"><strong>2) Address of
                                                            the Parties who are expected to provide regular loads</strong>
                                                    </p>

                                                    <div class="table-responsive">
                                                        <table
                                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th width="300px">Party Name</th>
                                                                    <th>Address</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="table-body-guarantor-address">
                                                                <tr>
                                                                    <td id="row-number-guarantor-address">1</td>
                                                                    <td><input type="text"
                                                                            name="GuarantorPartyAddress[party_name][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><input type="text"
                                                                            name="GuarantorPartyAddress[address][]"
                                                                            class="form-control mw-100"></td>
                                                                    <td><a href="#"
                                                                            class="text-primary add-row-guarantor-address"
                                                                            id="add-row-guarantor-address"
                                                                            data-class="add-row-guarantor-address"><i
                                                                                data-feather="plus-square"
                                                                                class="me-50"></i></a></td>
                                                                </tr>

                                                                @if (isset($vehicleLoan) && $vehicleLoan->guarantorAddress && $vehicleLoan->guarantorAddress->count() > 0)
                                                                    @foreach ($vehicleLoan->guarantorAddress as $key => $val)
                                                                        <tr>
                                                                            <td>{{ $key + 2 }}</td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorPartyAddress[party_name][]"
                                                                                    value="{{ $val->party_name ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><input type="text"
                                                                                    name="GuarantorPartyAddress[address][]"
                                                                                    value="{{ $val->address ?? '' }}"
                                                                                    class="form-control mw-100"></td>
                                                                            <td><a href="#"
                                                                                    class="text-danger @if (isset($vehicleLoan) && isset($editData)) delete-item @endif"><i
                                                                                        data-feather="trash-2"
                                                                                        style="cursor: @if (isset($vehicleLoan) && isset($editData)) pointer @else not-allowed @endif;"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif


                                                            </tbody>


                                                        </table>
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
                                                                        value="{{ old('stored_adhar_card', $vehicleLoan->vehicleDocuments->adhar_card ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="LoanDocument[common_data][adhar_card][]"
                                                                        multiple class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" />
                                                                </div>

                                                                @if (isset($vehicleLoan) && $vehicleLoan->vehicleDocuments && $vehicleLoan->vehicleDocuments->adhar_card)
                                                                    @php
                                                                        $adhar_doc_json =
                                                                            $vehicleLoan->vehicleDocuments->adhar_card;
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
                                                                        name="LoanDocument[common_data][stored_pan_gir_no]"
                                                                        value="{{ old('stored_pan_gir_no', $vehicleLoan->vehicleDocuments->pan_gir_no ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="LoanDocument[common_data][pan_gir_no][]"
                                                                        multiple class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" />
                                                                </div>

                                                                @if (isset($vehicleLoan) && $vehicleLoan->vehicleDocuments && $vehicleLoan->vehicleDocuments->pan_gir_no)
                                                                    @php
                                                                        $pan_gir_no_json =
                                                                            $vehicleLoan->vehicleDocuments->pan_gir_no;
                                                                        $pan_gir_nos = json_decode(
                                                                            $pan_gir_no_json,
                                                                            true,
                                                                        );
                                                                    @endphp
                                                                    @if (!empty($pan_gir_nos) && is_array($pan_gir_nos))
                                                                        @foreach ($pan_gir_nos as $key => $doc)
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
                                                                                        Doc</a></p>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Vehicle Document</label>
                                                                </div>

                                                                <div class="col-md-5">
                                                                    <input type="hidden"
                                                                        name="LoanDocument[common_data][stored_vehicle_doc]"
                                                                        value="{{ old('stored_vehicle_doc', $vehicleLoan->vehicleDocuments->vehicle_doc ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="LoanDocument[common_data][vehicle_doc][]"
                                                                        multiple class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" />
                                                                </div>

                                                                @if (isset($vehicleLoan) && $vehicleLoan->vehicleDocuments && $vehicleLoan->vehicleDocuments->vehicle_doc)
                                                                    @php
                                                                        $vehicle_doc_json =
                                                                            $vehicleLoan->vehicleDocuments->vehicle_doc;
                                                                        $vehicle_docs = json_decode(
                                                                            $vehicle_doc_json,
                                                                            true,
                                                                        );
                                                                    @endphp
                                                                    @if (!empty($vehicle_docs) && is_array($vehicle_docs))
                                                                        @foreach ($vehicle_docs as $key => $doc)
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
                                                                                        target="_blank" download>Vehicle
                                                                                        Doc</a></p>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Security Document</label>
                                                                </div>

                                                                <div class="col-md-5">
                                                                    <input type="hidden"
                                                                        name="LoanDocument[common_data][stored_security_doc]"
                                                                        value="{{ old('stored_security_doc', $vehicleLoan->vehicleDocuments->security_doc ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="LoanDocument[common_data][security_doc][]"
                                                                        multiple class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" />
                                                                </div>

                                                                @if (isset($vehicleLoan) && $vehicleLoan->vehicleDocuments && $vehicleLoan->vehicleDocuments->security_doc)
                                                                    @php
                                                                        $security_doc_json =
                                                                            $vehicleLoan->vehicleDocuments
                                                                                ->security_doc;
                                                                        $security_docs = json_decode(
                                                                            $security_doc_json,
                                                                            true,
                                                                        );
                                                                    @endphp
                                                                    @if (!empty($security_docs) && is_array($security_docs))
                                                                        @foreach ($security_docs as $key => $doc)
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
                                                                                        target="_blank" download>Security
                                                                                        Doc</a></p>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Partnership Affidavit</label>
                                                                </div>

                                                                <div class="col-md-5">
                                                                    <input type="hidden"
                                                                        name="LoanDocument[common_data][stored_partnership_doc]"
                                                                        value="{{ old('stored_partnership_doc', $vehicleLoan->vehicleDocuments->partnership_doc ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="LoanDocument[common_data][partnership_doc][]"
                                                                        multiple class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" />
                                                                </div>

                                                                @if (isset($vehicleLoan) && $vehicleLoan->vehicleDocuments && $vehicleLoan->vehicleDocuments->partnership_doc)
                                                                    @php
                                                                        $partnership_doc_json =
                                                                            $vehicleLoan->vehicleDocuments
                                                                                ->partnership_doc;
                                                                        $partnership_docs = json_decode(
                                                                            $partnership_doc_json,
                                                                            true,
                                                                        );
                                                                    @endphp
                                                                    @if (!empty($partnership_docs) && is_array($partnership_docs))
                                                                        @foreach ($partnership_docs as $key => $doc)
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
                                                                                        target="_blank"
                                                                                        download>Partnership Doc</a></p>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Proprietorship
                                                                        Affidavit</label>
                                                                </div>

                                                                <div class="col-md-5">
                                                                    <input type="hidden"
                                                                        name="LoanDocument[common_data][stored_affidavit_doc]"
                                                                        value="{{ old('stored_affidavit_doc', $vehicleLoan->vehicleDocuments->affidavit_doc ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="LoanDocument[common_data][affidavit_doc][]"
                                                                        multiple class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" />
                                                                </div>

                                                                @if (isset($vehicleLoan) && $vehicleLoan->vehicleDocuments && $vehicleLoan->vehicleDocuments->affidavit_doc)
                                                                    @php
                                                                        $affidavit_doc_json =
                                                                            $vehicleLoan->vehicleDocuments
                                                                                ->affidavit_doc;
                                                                        $affidavit_docs = json_decode(
                                                                            $affidavit_doc_json,
                                                                            true,
                                                                        );
                                                                    @endphp
                                                                    @if (!empty($affidavit_docs) && is_array($affidavit_docs))
                                                                        @foreach ($affidavit_docs as $key => $doc)
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
                                                                                        target="_blank" download>Affidavit
                                                                                        Doc</a></p>
                                                                            </div>
                                                                        @endforeach
                                                                    @endif
                                                                @endif
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-4">
                                                                    <label class="form-label">Scan form Application</label>
                                                                </div>

                                                                <div class="col-md-5">
                                                                    <input type="hidden"
                                                                        name="LoanDocument[common_data][stored_scan_doc]"
                                                                        value="{{ old('stored_scan_doc', $vehicleLoan->vehicleDocuments->scan_doc ?? '') }}"
                                                                        class="form-control" />
                                                                    <input type="file"
                                                                        name="LoanDocument[common_data][scan_doc][]"
                                                                        multiple class="form-control"
                                                                        onchange="checkFileTypeandSize(event)" />
                                                                </div>

                                                                @if (isset($vehicleLoan) && $vehicleLoan->vehicleDocuments && $vehicleLoan->vehicleDocuments->scan_doc)
                                                                    @php
                                                                        $scan_doc_json =
                                                                            $vehicleLoan->vehicleDocuments->scan_doc;
                                                                        $scan_docs = json_decode($scan_doc_json, true);
                                                                    @endphp
                                                                    @if (!empty($scan_docs) && is_array($scan_docs))
                                                                        @foreach ($scan_docs as $key => $doc)
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
                                                                                        target="_blank" download>Scan
                                                                                        Doc</a></p>
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

    <div class="modal fade" id="upload-letter" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
            <div class="modal-content">
                <form action="{{ route('loanLegalDocumentation.loan-legal-document') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Upload Legal Doc</h1>
                    <p class="text-center">Enter the details below.</p>

                    <div class="row mt-3 customernewsection-form">

                        <div class="col-md-12 mb-1">
                            <div class="table-responsive-md">
                                <table
                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Document Name</th>
                                            <th>Upload File</th>
                                            <th>Attachments</th>
                                            <th width="40px">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableDoc">
                                        <tr>
                                            <td>1</td>
                                            <td>
                                                <select class="form-select mw-100 remove-disable" name="documentname[0]" requried>
                                                    <option value="">Select</option>
                                                    @if(isset($documents) && $documents->isNotEmpty())
                                                    @foreach ($documents as $document)
                                                    <option value="{{ $document->name }}">
                                                        {{ ucwords(str_replace('-', ' ', $document->name)) }}
                                                    </option>
                                                    @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td>
                                                <input type="file" multiple class="form-control mw-100 remove-disable loan-documents" name="attachments[]" id="attachments-0" requried>
                                            </td>
                                            <td id="preview-0">
                                            </td>
                                            <td><a href="#" class="text-primary addRow"><i data-feather="plus-square"></i></a></td>
                                        </tr>


                                    </tbody>


                                </table>
                            </div>
                        </div>
                        <input type="hidden" name="loan_type" value="vehicle" required>
                        <input type="hidden" name="loan_application_id" value="{{request()->route('id')}}" required>

                        <div class="col-md-12 mb-1">
                            <label class="form-label">Remarks <span class="text-danger">*</span></label>
                            <textarea class="form-control remove-disable" name="remarks" requried></textarea>
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

    <div class="modal fade" id="update-fee" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="loanProcessingFeeForm" action="{{ route('loanProcessingFee.loan-process') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-4 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Update Fee</h1>
                    <p class="text-center">Enter the details below.</p>

                    <div class="row mt-3 customernewsection-form">

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Processing Fee Amount <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control remove-disable" name="amount" required />
                        </div>
                        <div class="row align-items-center mb-1">
                            <div class="col-md-4">
                                <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                            </div>

                            <div class="col-md-8">
                                <div class="demo-inline-spacing">
                                    <div class="form-check form-check-primary mt-25">
                                        <input type="radio" id="Bank" value="Bank" name="payment_type"
                                            class="remove-disable form-check-input" checked>
                                        <label class="form-check-label fw-bolder" for="Bank">Bank</label>
                                    </div>
                                    <div class="form-check form-check-primary mt-25">
                                        <input type="radio" id="Cash" value="Cash" name="payment_type"
                                            class="form-check-input remove-disable">
                                        <label class="form-check-label fw-bolder" for="Cash">Cash</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-center mb-1">
                            <div class="col-md-4">
                                <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <input type="date" class="form-control remove-disable" name="payment_date"
                                    id="payment_date" required value="{{ date('Y-m-d') }}"
                                    max="{{ date('Y-m-d') }}" />
                            </div>
                        </div>
                        <div class="row align-items-center mb-1">
                            <div class="col-md-4">
                                <label class="form-label">Currency <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <select class="form-control select2 remove-disable" name="currency_id" required
                                                            id="currency_id">
                                                            <option>Select Currency</option>
                                                            @isset($currencies)
                                                            @foreach ($currencies as $currency)
                                                                <option value="{{ $currency->id }}">
                                                                    {{ $currency->name . ' (' . $currency->short_name . ')' }}
                                                                </option>
                                                            @endforeach
                                                            @endisset
                                                        </select>
                                                   
                            </div>
                        </div>
                       
                        <div class="row align-items-center mb-1">
                            <div class="col-md-4">
                                <label class="form-label">Loan customer receivable <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <select class="form-control select2 remove-disable" name="custledger_id"
                                    id="custledger_id">
                                    <option disabled selected value="">Select Ledger</option>
                                    @if(isset($allledgers))
                                    @foreach ($allledgers as $ledger)
                                        @php
                                            $groupIds = is_string($ledger->ledger_group_id) ? json_decode($ledger->ledger_group_id) : $ledger->ledger_group_id;

                                            $groupIdsString = implode(',', (array) $groupIds);
                                        @endphp

                                        <option value="{{ $ledger->id }}" data-group-ids="{{ $groupIdsString }}" >
                                            {{ $ledger->name }}
                                        </option>
                                    @endforeach

                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="row align-items-center mb-1">
                            <div class="col-md-4">

                            </div>
                            <div class="col-md-8">
                                <select class="form-control select2 remove-disable" name="groupledger_id"
                                    id="groupledger_id">
                                    <option disabled selected value="">Select Group Ledger</option>
                                </select>
                            </div>
                        </div>

                        <div class="row align-items-center mb-1 bankfield">
                            <div class="col-md-4">
                                <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                            </div>

                            <div class="col-md-8 mb-1 mb-sm-0">
                                <select class="form-control select2 bankInput remove-disable" name="bank_id"
                                    id="bank_id" onchange="getAccounts()" required>
                                    <option selected disabled value="">Select Bank</option>
                                    @foreach ($banks as $bank)
                                        <option value="{{ $bank->id }}">
                                            {{ $bank->bank_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row align-items-center mb-1 bankfield">

                            <div class="col-md-4">
                                <label class="form-label">A/c No. <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <select class="form-control select2 bankInput remove-disable" name="account_id"
                                    id="account_id" required>
                                    <option selected disabled value="">Select Bank Account
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row align-items-center mb-1 bankfield">
                            <div class="col-md-4">
                                <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            </div>

                            <div class="col-md-8 mb-1 mb-sm-0">
                                <select class="form-control select2 bankInput remove-disable" name="payment_mode"
                                    required>
                                    <option value="">Select</option>
                                    <option>IMPS/RTGS</option>
                                    <option>NEFT</option>
                                    <option>By Cheque</option>
                                </select>
                            </div>
                        </div>

                        <div class="row align-items-center mb-1 bankfield">

                            <div class="col-md-4">
                                <label class="form-label">Ref No. <span class="text-danger">*</span></label>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control remove-disable bankInput"
                                    name="reference_no" required />
                            </div>
                        </div>

                        <div class="row align-items-center mb-1 cashfield" style="display: none">
                            <div class="col-md-4">
                                <label class="form-label">Ledger <span class="text-danger">*</span></label>
                            </div>

                            <div class="col-md-8">
                                <select class="form-control select2 remove-disable" name="ledger_id"
                                    id="ledger_id">
                                    <option disabled selected value="">Select Ledger</option>
                                    @foreach ($ledgers as $ledger)
                                        <option value="{{ $ledger->id }}">{{ $ledger->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <input type="hidden" name="loan_type" value="home" required>
                        <input type="hidden" name="loan_application_id" value="{{ request()->route('id') }}"
                            required>
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Attachment <span class="text-danger">*</span></label>
                            <input type="file" class="form-control remove-disable" name="document" required />
                        </div>


                        <div class="col-md-12 mb-1">
                            <label class="form-label">Remarks <span class="text-danger">*</span></label>
                            <textarea class="form-control remove-disable" name="remarks" required></textarea>
                        </div>



                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    @isset($isPostingRequired)
                    @if($isPostingRequired)
                    <button type="button" id="submitBtn" class="btn btn-primary">Submit</button>
                    @else
                    <button type="submit" id="submitBtn" class="btn btn-primary">Submit</button>
                    @endif
                    @endisset
                </div>
            </form>
        </div>
        </div>
    </div>


    <div class="modal fade" id="accept" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
                <form action="{{ route('loanSanctionLetter.loan-accept') }}" method="POST" enctype="multipart/form-data">
                    @csrf
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<h1 class="text-center mb-1" id="shareProjectTitle">Upload Sansaction Letter</h1>
					<p class="text-center">Enter the details below.</p>

					<div class="row mt-3 customernewsection-form">


						   <div class="col-md-12 mb-2">
                                <label class="form-label">Upload Letter <span class="text-danger">*</span></label>
                                <input type="file" class="form-control upload-doc-home-loan" name="document" required/>
                            </div>
                            <input type="hidden" name="loan_type" value="vehicle" required>
                            <input type="hidden" name="loan_application_id" value="{{request()->route('id')}}" required>

                            <div class="col-md-12 mb-1">
                                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                <textarea class="form-control textarea-home-loan" name="remarks"></textarea>
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

    <div class="modal fade" id="approved" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
        </div>
        </div>
    </div>
    <form action="{{ route('loanApproval.loan-approve') }}" method="POST" enctype="multipart/form-data" id="approve-form">
        @csrf
        <input type="hidden" name="loan_type" value="vehicle" required>
        <input type="hidden" name="loan_application_id" value="{{request()->route('id')}}" required>
    </form>

    <div class="modal fade text-start show" id="postvoucher" tabindex="-1" aria-labelledby="postVoucherModal" aria-modal="true" role="dialog">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="postVoucherModal"> Voucher Details</h4>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					 <div class="row">

                         <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Series <span class="text-danger">*</span></label>
                                <input id = "voucher_book_code" class="form-control" disabled="" >
                                <input type="hidden" class="form-control" name="data" id="ldata" >
                                <input type="hidden" class="form-control" name="doc" id="doc" >
                                <input type="hidden" class="form-control" name="loan_data" id="loan_data" >
                                <input type="hidden" class="form-control" name="remakrs" id="remakrs" >

                            </div>
                        </div>

                         <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Voucher No <span class="text-danger">*</span></label>
                                <input id = "voucher_doc_no" class="form-control" disabled="" value="">
                            </div>
                        </div>
                         <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Voucher Date <span class="text-danger">*</span></label>
                                <input id = "voucher_date" class="form-control" disabled="" value="">
                            </div>
                        </div>
                         <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Currency <span class="text-danger">*</span></label>
                                <input id = "voucher_currency" class="form-control" disabled="" value="">
                            </div>
                        </div>

						 <div class="col-md-12">


							<div class="table-responsive">
								<table class="mt-1 table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
									<thead>
										 <tr>
											<th>Type</th>
											<th>Group</th>
											<th>Leadger Code</th>
											<th>Leadger Name</th>
                                            <th class="text-end">Debit</th>
                                            <th class="text-end">Credit</th>
										  </tr>
										</thead>
										<tbody id = "posting-table">


									   </tbody>


								</table>
							</div>
						</div>


					 </div>
				</div>
				<div class="modal-footer text-end">
					<button onclick = "postVoucher(this);" id = "posting_button" type = "button" class="btn btn-primary btn-sm waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Submit</button>
				</div>
			</div>
		</div>
	</div>

    <div class="modal fade" id="return" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route(explode('.',Route::currentRouteName())[0].'.loan-return') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Return
                                Home
                                Loan Application</h4>
                            <p class="mb-0 fw-bold voucehrinvocetxt mt-0">({{ $vehicleLoan->name ?? 'N/A' }} |
                                {{ $overview->term_loan ?? $vehicleLoan->loan_amount }} |
                                {{ $overview->update_at ?? $vehicleLoan->created_at ? $vehicleLoan->created_at->format('d-m-Y') : 'N/A' }})
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body pb-2" id="returnpolicy">

                        <div class="row mt-1">

                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" class="form-control upload-doc-home-loan" name="document"
                                        required />
                                </div>
                                <input type="hidden" name="loan_type" value="vehicle" required>
                                <input type="hidden" name="loan_application_id"
                                    value="{{ request()->route('id') }}" required>
                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    <textarea class="form-control textarea-home-loan" name="remarks" required></textarea>
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
                <form action="{{ route(explode('.',Route::currentRouteName())[0].'.loan-reject') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Reject
                                Term
                                Loan Application</h4>
                            <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{ @$overview->proprietor_name }} |
                                {{ @$overview->term_loan }} | {{ explode(' ', @$overview->created_at)[0] }}</p>
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

{{--    <div class="modal fade" id="viewassesgive" tabindex="-1" aria-labelledby="shareProjectTitle"--}}
{{--        aria-hidden="true">--}}
{{--        <div class="modal-dialog modal-dialog-centered">--}}
{{--            <div class="modal-content">--}}
{{--                <form action="{{ route('loanAssessment.assessment-proceed') }}" method="POST"--}}
{{--                    enctype="multipart/form-data">--}}
{{--                    @csrf--}}
{{--                    <div class="modal-header">--}}
{{--                        <div>--}}
{{--                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">--}}
{{--                                Assessment--}}
{{--                                by--}}
{{--                                Field Officer</h4>--}}
{{--                            <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{ @$overview->proprietor_name }} |--}}
{{--                                {{ @$overview->term_loan }} | {{ explode(' ', @$overview->created_at)[0] }}</p>--}}
{{--                        </div>--}}
{{--                        <button type="button" class="btn-close" data-bs-dismiss="modal"--}}
{{--                            aria-label="Close"></button>--}}
{{--                    </div>--}}
{{--                    <div class="modal-body pb-2">--}}

{{--                        <div class="row mt-1">--}}

{{--                            <div class="col-md-12">--}}

{{--                                <div class="row">--}}
{{--                                    <div class="col-md-12">--}}
{{--                                        <div class="mb-1">--}}
{{--                                            <label class="form-label">Upload Document <span--}}
{{--                                                    class="text-danger">*</span></label>--}}
{{--                                            <input type="file" class="form-control remove-disable" name="document"--}}
{{--                                                required />--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                                <input type="hidden" name="loan_type" value="vehicle" required>--}}
{{--                                <input type="hidden" name="loan_application_id"--}}
{{--                                    value="{{ request()->route('id') }}" required>--}}
{{--                                <div class="mb-1">--}}
{{--                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>--}}
{{--                                    <textarea class="form-control remove-disable" name="remarks" required></textarea>--}}
{{--                                </div>--}}



{{--                            </div>--}}

{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div class="modal-footer justify-content-center">--}}
{{--                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>--}}
{{--                        <button type="submit" class="btn btn-primary">Submit</button>--}}
{{--                    </div>--}}
{{--                </form>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
    <div class="modal fade" id="viewassesgive" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Assessment by Field Officer</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | 29-07-2024</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('loanAssessment.assessment-proceed') }}" method="POST">
                    @csrf
                <div class="modal-body pb-2">
                    Copy

                    @if ($buttons['proceed'])
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <!-- Basic Eligibility Section -->
                                <p class="mb-25 text-dark customapplsmallhead"><strong>Basic Eligibility</strong></p>
                                <p class="font-small-3 mb-1">30% (Evaluates borrower’s income, loan type requirements, and personal criteria)</p>

                                <div class="table-responsive">
                                    <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border" id="checkTable">
                                        <thead>
                                        <tr>
                                            <th class="20px">
                                                <div class="form-check form-check-inline me-0">
                                                    <input class="form-check-input" type="checkbox" name="podetail" id="inlineCheckbox1">
                                                </div>
                                            </th>
                                            <th width="300px">Parameter</th>
                                            <th>Weightage %</th>
                                            <th>Sub criteria</th>
                                            <th>Marks Allocation</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            // Initialize $basicEligibility as an empty array if no credit scoring exists
                                            if (!isset($credit_scoring) || empty($credit_scoring->basic_eligibility)) {
                                                $basicEligibility = [];
                                            } else {
                                                if (gettype($credit_scoring->basic_eligibility) == 'string') {
                                                    $basicEligibility = json_decode($credit_scoring->basic_eligibility, true);
                                                } else {
                                                    $basicEligibility = array_map(function($item) {
                                                        return $item;
                                                    }, $credit_scoring->basic_eligibility);
                                                }
                                            }

                                            $parameters = [
                                                ['parameter' => 'Age', 'weightage' => '-', 'sub_criteria' => 'Between 21-65 years for loan', 'marks' => '5 Marks'],
                                                ['parameter' => 'Income proof - choose one', 'weightage' => '-', 'sub_criteria' => 'Evaluates repayment capacity', 'marks' => '-'],
                                                ['parameter' => 'Salary Slip (Last 3 months)', 'weightage' => '-', 'sub_criteria' => 'For salaried applicants', 'marks' => '5 Marks'],
                                                ['parameter' => 'IRT last two years', 'weightage' => '-', 'sub_criteria' => 'For business/self employed applicants', 'marks' => '5 Marks'],
                                                ['parameter' => 'GST Returns', 'weightage' => '-', 'sub_criteria' => 'For business', 'marks' => '5 Marks'],
                                                ['parameter' => 'Debt to income Ratio', 'weightage' => '-', 'sub_criteria' => 'Ratio determines repayment ability', 'marks' => '-'],
                                                ['parameter' => 'Below 30%', 'weightage' => '-', 'sub_criteria' => 'Excellent repayment capacity', 'marks' => '10 Marks'],
                                                ['parameter' => '30-50%', 'weightage' => '-', 'sub_criteria' => 'Moderate repayment capacity', 'marks' => '5 Marks'],
                                                ['parameter' => 'Above 50%', 'weightage' => '-', 'sub_criteria' => 'Poor repayment capacity', 'marks' => '0 Marks'],
                                            ];
                                        @endphp

                                        @foreach($parameters as $param)
                                            @php
                                                // If no credit scoring exists, all checkboxes are unchecked by default
                                                $isChecked = isset($credit_scoring) ? in_array($param['parameter'], array_column($basicEligibility, 'parameter')) : false;
                                                $marks = (int)filter_var($param['marks'], FILTER_SANITIZE_NUMBER_INT); // Extract marks as integer
                                                $isNoMarks = trim($param['marks']) === '-';
                                                $radioClass = in_array($param['parameter'], ['Below 30%', '30-50%', 'Above 50%']) ? 'radio1' : '';
                                            @endphp
                                            <tr class="{{ $radioClass }}">
                                                <td>
                                                    @unless($isNoMarks)
                                                    <div class="form-check form-check-inline me-0">
                                                        <input class="form-check-input eligibility-checkbox" type="checkbox" name="basic_eligibility[]" value="{{ json_encode($param) }}" data-marks="{{ $marks }}" {{ $isChecked ? 'checked' : '' }}>
                                                    </div>
                                                    @endunless
                                                </td>
                                                @foreach(['parameter', 'sub_criteria', 'marks'] as $field)
                                                        <td>
                                                            @if($isNoMarks)
                                                                <strong>{{ $param[$field] }}</strong>
                                                            @else
                                                                {{ $param[$field] }}
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    <td class="weightage">
                                                        @if($isNoMarks)
                                                            <strong>{{ $isChecked ? $marks . '%' : '-' }}</strong>
                                                        @else
                                                            {{ $isChecked ? $marks . '%' : '-' }}
                                                        @endif
                                                    </td>
                                                </tr>
                                        @endforeach
                                        <tr>
                                            <td></td>
                                            <td colspan="3" class="text-end fw-bold text-dark">Total</td>
                                            <td class="fw-bold text-dark"><span id="totalMarks">0</span> Marks</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td colspan="3" class="text-end fw-bold text-dark">Total Weightage</td>
                                            <td class="fw-bold text-dark"><span id="totalWeightage">0</span>%</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    <textarea class="form-control textarea" name="remarks">{{ isset($credit_scoring) ? $credit_scoring->remarks : '' }}</textarea>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="loan_appraisal_id" value="{{ $loan_appraisal_id??"" }}">
                        <input type="hidden" name="loan_application_id" value="{{ request()->route('id') }}" required>
                    @endif
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal">Cancel</button>
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
        function showToast(icon, title) {
                Swal.fire({
                    title: 'Alert!',
                    text: title,
                    icon: icon
                });
        }
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.eligibility-checkbox');
            const totalMarksSpan = document.getElementById('totalMarks');
            const totalWeightageSpan = document.getElementById('totalWeightage');
            const table = document.getElementById('checkTable');

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const row = checkbox.closest('tr');

                    if (row && row.classList.contains('radio1') && checkbox.checked) {
                        const groupCheckboxes = table.querySelectorAll('tr.radio1 .form-check-input');
                        console.log(groupCheckboxes,'check')

                        groupCheckboxes.forEach(cb => {
                            if (cb !== checkbox && cb.checked) {
                                // Uncheck the previous checkbox
                                cb.checked = false;

                                // Reset weightage in the previous row
                                const otherRow = cb.closest('tr');
                                if (otherRow) {
                                    const weightageCell = otherRow.querySelector('.weightage');
                                    if (weightageCell) {
                                        weightageCell.textContent = '-';
                                    }
                                }

                                // Show toast
                                showToast('error', 'Only one ratio should be selected in this group.');
                            }
                        });
                    }

                    updateTotals();
                });
            });
            function updateTotals() {
                let totalMarks = 0;
                let totalWeightage = 0;

                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        const marks = parseInt(checkbox.getAttribute('data-marks'));
                        totalMarks += marks;
                        totalWeightage += marks; // Weightage is equal to marks in this case
                    }
                });

                totalMarksSpan.textContent = totalMarks;
                totalWeightageSpan.textContent = totalWeightage;

                // Update weightage percentage in the table
                checkboxes.forEach(checkbox => {
                    const weightageCell = checkbox.closest('tr').querySelector('.weightage');
                    if (weightageCell) {
                        if (checkbox.checked) {
                            const marks = parseInt(checkbox.getAttribute('data-marks'));
                            weightageCell.textContent = marks + '%';
                        } else {
                            weightageCell.textContent = '-';
                        }
                    }
                });
            }

            // Initialize totals on page load
            updateTotals();
        });
        {{--$(document).ready(function() {--}}
        {{--    $('#submitBasicEligibility').on('click', function(e) {--}}
        {{--        e.preventDefault();--}}

        {{--        let formData = new FormData($('#basicEligibilityForm')[0]);--}}

        {{--        $.ajax({--}}
        {{--            url: '{{ route("loanAppraisal.updateBasicEligibility") }}',--}}
        {{--            type: 'POST',--}}
        {{--            data: formData,--}}
        {{--            dataType: 'json',--}}
        {{--            contentType: false,--}}
        {{--            processData: false,--}}
        {{--            success: function(response) {--}}
        {{--                if (response.status === true) {--}}
        {{--                    alert(response.message);--}}
        {{--                    $('#viewassesgive').modal('hide');--}}
        {{--                } else {--}}
        {{--                    alert('Something went wrong, please try again.');--}}
        {{--                }--}}
        {{--            },--}}
        {{--            error: function(jqXHR, exception) {--}}
        {{--                console.log("An error occurred:", exception);--}}
        {{--                alert('Something went wrong, please try again.');--}}
        {{--            }--}}
        {{--        });--}}
        {{--    });--}}
        {{--});--}}
            function submitForm(){
                $('#approve-form').submit();
            }
        window.onload = function() {
            document.querySelectorAll('input.form-check-input').forEach(function(checkbox) {
                checkbox.removeAttribute('readonly');
                checkbox.removeAttribute('disabled');
            });
            let elements = document.getElementsByClassName('textarea'); // No dot here
            for (let i = 0; i < elements.length; i++) {
                elements[i].removeAttribute('disabled');
            }
        };
         $('#submitBtn').on('click', function (e) {
                e.preventDefault();

                // Create a FormData object to handle file uploads
                let formData = new FormData($('#loanProcessingFeeForm')[0]);

                // Send AJAX request
                $.ajax({
                    url: $('#loanProcessingFeeForm').attr('action'), // Get form action URL
                    type: 'POST',
                    data: formData,
                    processData: false, // Prevent jQuery from automatically transforming the data
                    contentType: false, // Set contentType to false for file uploads
                    beforeSend: function () {
                        // Optional: Add a loader or disable the button
                        $('#submitBtn').prop('disabled', true).text('Submitting...');
                    },
                    success: function (response) {
                        // Handle success
                        // alert('Form submitted successfully!');
                        $('#loanProcessingFeeForm')[0].reset(); // Reset the form
                        $('#submitBtn').prop('disabled', false).text('Submit');


                        $('#ldata').val(response.data);
                        $('#doc').val(response.doc);
                        $('#loan_data').val(response.loan_data);
                        $('#remakrs').val(response.remakrs);
                        // Optionally close the modal
                        onPostVoucherOpen();

                    },
                    error: function (xhr) {
                        // Handle error
                        let errorText = xhr.responseJSON?.message || 'Something went wrong. Please try again.';
                        alert(errorText);
                        $('#submitBtn').prop('disabled', false).text('Submit');
                    }
                });
            });


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
            @if (isset($vehicleLoan))
                let book_type_val = $("#book_type").val();

                if (book_type_val) {
                    fetchLoanSeries(book_type_val, 'series').done(function() {

                        let vehicleLoanSeries = '{{ $vehicleLoan->series }}';
                        $('#series option').each(function() {
                            if ($(this).val() == vehicleLoanSeries) {
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
                var $firstRow = $('#table-body-vehicle-loan tr:first').clone();
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
                    $('#table-body-vehicle-loan').append($newRow);
                });
                localStorage.removeItem('formData');
                feather.replace();

                var formData1 = JSON.parse(localStorage.getItem('formData1') || '[]');
                var $firstRow = $('#table-body-guarantor-party tr:first').clone();
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
                    $('#table-body-guarantor-party').append($newRow);
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
            @endif

            // table add delete values
            feather.replace();
            $('tbody').on('click', '#add-row-vehicle-loan, #add-row-guarantor-party, #add-row-guarantor-address',
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

                    // Update row number for the new row
                    var nextIndex = $('#' + tbodyId + ' tr').length + 1;
                    $newRow.find('#' + $firstTdClass).text(nextIndex);
                    $newRow.find('#' + clickedClass).removeClass(clickedClass).removeAttr('id').removeAttr(
                        'data-class').addClass('text-danger delete-item').html(
                        '<i data-feather="trash-2"></i>');

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
        });

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

        @if (!isset($editData))
            $('form').on('submit', function(e) {
                var formData = [];
                var formData1 = [];
                var formData2 = [];

                $('#table-body-vehicle-loan').find('tr').each(function(index, row) {
                    if (index === 0) return;

                    var rowData = {};

                    $(row).find('input, select').each(function() {
                        var name = $(this).attr('name');
                        rowData[name] = $(this).val();
                    });

                    formData.push(rowData);
                });

                $('#table-body-guarantor-party').find('tr').each(function(index, row) {
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

                localStorage.setItem('formData', JSON.stringify(formData));
                localStorage.setItem('formData1', JSON.stringify(formData1));
                localStorage.setItem('formData2', JSON.stringify(formData2));
            });
        @endif

        // document.addEventListener('DOMContentLoaded', function () {
        //     @if ($errors->any())
        //         @foreach ($errors->all() as $error)
        //             toastr.error('{{ $error }}', 'Error');
        //         @endforeach
        //     @endif
        // });

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
            var selectedValue = null;
            if(selectElement)
            selectedValue=  selectElement.value;
            get_series_details(selectedValue);
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Select all input fields of type number
            const numberInputs = document.querySelectorAll('input[type="number"]');

            // Loop through each input field
            numberInputs.forEach(function(input) {
                // Add an input event listener to each number input
                input.addEventListener('input', function() {
                    // If the value is negative, set it to its absolute value
                    if (this.value < 0) {
                        this.value = Math.abs(this.value);
                    }
                });

                // Add a blur event listener to ensure no negative values on losing focus
                input.addEventListener('blur', function() {
                    if (this.value < 0) {
                        this.value = Math.abs(this.value);
                    }
                });
            });
        });

        function updateSchemeTotal() {
            const body_building_scheme = parseFloat(document.getElementById('body_building_scheme').value) || 0;
            const other_item_scheme = parseFloat(document.getElementById('other_item_scheme').value) || 0;
            const spares_tyres_scheme = parseFloat(document.getElementById('spares_tyres_scheme').value) || 0;
            const insurance_taxes_scheme = parseFloat(document.getElementById('insurance_taxes_scheme').value) || 0;
            const pre_operative_exp_scheme = parseFloat(document.getElementById('pre_operative_exp_scheme').value) || 0;
            const working_c_margin_scheme = parseFloat(document.getElementById('working_c_margin_scheme').value) || 0;

            const total_scheme = body_building_scheme + other_item_scheme + spares_tyres_scheme + insurance_taxes_scheme +
                pre_operative_exp_scheme + working_c_margin_scheme;
            document.getElementById('total_scheme').value = total_scheme;
        }

        document.getElementById('body_building_scheme').addEventListener('input', updateSchemeTotal);
        document.getElementById('other_item_scheme').addEventListener('input', updateSchemeTotal);
        document.getElementById('spares_tyres_scheme').addEventListener('input', updateSchemeTotal);
        document.getElementById('insurance_taxes_scheme').addEventListener('input', updateSchemeTotal);
        document.getElementById('pre_operative_exp_scheme').addEventListener('input', updateSchemeTotal);
        document.getElementById('working_c_margin_scheme').addEventListener('input', updateSchemeTotal);

        // Means of Finance (Rs)

        function updateMeanFinance() {
            const own_capi = parseFloat(document.getElementById('own_capi').value) || 0;
            const term_midc = parseFloat(document.getElementById('term_midc').value) || 0;

            const finance_total = own_capi + term_midc;
            document.getElementById('finance_total').value = finance_total;
        }

        document.getElementById('own_capi').addEventListener('input', updateMeanFinance);
        document.getElementById('term_midc').addEventListener('input', updateMeanFinance);

        // Cost of the Scheme (Rs)

        function updateSchemeCost() {
            const vehicle_sc = parseFloat(document.getElementById('vehicle_sc').value) || 0;
            const collateral_security = parseFloat(document.getElementById('collateral_security').value) || 0;

            const security_total = vehicle_sc + collateral_security;
            document.getElementById('security_total').value = security_total;
        }

        document.getElementById('vehicle_sc').addEventListener('input', updateSchemeCost);
        document.getElementById('collateral_security').addEventListener('input', updateSchemeCost);


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

                document.querySelectorAll('#returnpolicy').forEach(function(modal) {
                    // Make all input fields read-only
                    modal.querySelectorAll('input').forEach(function(input) {
                        input.readOnly = false; // Makes the input field read-only
                    });

                    // Disable all select fields
                    modal.querySelectorAll('select').forEach(function(select) {
                        select.disabled = false; // Disables the select field
                    });

                    // Disable all textarea fields
                    modal.querySelectorAll('textarea').forEach(function(textarea) {
                        textarea.disabled = false; // Disables the textarea field
                    });
                });
            }
            $(document).ready(function() {
                $('.upload-doc-home-loan').removeAttr('disabled')
                $('.user-behalf-home-loan').removeAttr('disabled')
                $('.remove-disable').removeAttr('disabled'); $('.remove-disable').removeAttr('readonly');
                $('.remove-disable').removeAttr('readonly')
                $('.textarea-home-loan').removeAttr('disabled')
            })
        });

        $(document).ready(function() {
            $('#email-search').removeAttr('readonly')

            // Event listener for keyup on the search input field
            $('#email-search').on('keyup', function() {
                var searchValue = $(this).val().toLowerCase(); // Get the search input value in lowercase

                // Iterate over all table rows
                $('.loan-documents tr').each(function() {
                    var rowText = $(this).text()
                        .toLowerCase(); // Get the text content of the row (in lowercase)

                    // Check if the row text includes the search term
                    if (rowText.indexOf(searchValue) > -1) {
                        $(this).show(); // Show the row if it matches
                    } else {
                        $(this).hide(); // Hide the row if it doesn't match
                    }
                });
            });
        });

        $(".addRow").click(function() {
            var rowCount = $("#tableDoc").find('tr').length + 1; // Counter for row numbering, starting at 1

            var newRow = `
            <tr>
                <td>${rowCount}</td>
                <td>
                <select class="form-select mw-100" name="documentname[${rowCount-1}]">
                <option value="">Select</option>
                @if(isset($documents) && $documents->isNotEmpty())

                @foreach ($documents as $document)
                                                                                        <option value="{{ $document->name }}">{{ ucwords(str_replace('-', ' ', $document->name)) }}</option>
                                                                                    @endforeach @endisset </select>
                                                                                    </td>
                <td>
                    <input type="file" multiple class="form-control mw-100 loan-documents" name="attachments[]" id="attachments-${rowCount-1}">
                </td>
                <td id="preview-${rowCount-1}">
                    <!-- File preview icons will be inserted here -->
                </td>
                <td><a href="#" class="text-danger trash" style="padding: unset;"><i data-feather="trash-2"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg></i></a></td>
            </tr>`;

            $("#tableDoc").append(newRow);
            // feather.replace();

        });

        $("#tableDoc").on("click", ".trash", function(event) {
            event.preventDefault(); // Prevent default action for <a> tag
            $(this).closest('tr').remove(); // Remove the closest <tr> element
        });
        $(function() {
            $("input[name='payment_type']").click(function() {
                if ($("#Bank").is(":checked")) {
                    $(".bankfield").show();
                    $(".cashfield").hide();
                    $('.bankInput').attr('required', true);
                    $('#ledger_id').attr('required', false);
                } else {
                    $(".cashfield").show();
                    $(".bankfield").hide();
                    $('.bankInput').attr('required', false);
                    $('#ledger_id').attr('required', true);
                }
            });
        });
        var banks = {!! json_encode($banks) !!};

        function getAccounts() {
            var accounts = [];
            $('#account_id').empty();
            $('#account_id').prepend('<option disabled selected value="">Select Bank Account</option>');

            const bank_id = $('#bank_id').val();
            $.each(banks, function(key, value) {
                if (value['id'] == bank_id) {
                    accounts = value['bank_details'];
                }
            });

            $.each(accounts, function(key, value) {
                $("#account_id").append("<option value ='" + value['id'] + "'>" + value['account_number'] +
                    " </option>");
            });
        }

        function onPostVoucherOpen(type = "not_posted")
{
    resetPostVoucher();
    const apiURL = "{{route('loanProcessingFee.getPostingDetails')}}";
    const remakrs = $("#remakrs").val();
    const formData= $("#ldata").val();
    const loanData= $("#loan_data").val();

    $.ajax({
        url: apiURL + "?book_id=" + "{{$vehicleLoan->book_id}}" + "&document_id=" + "{{$vehicleLoan->id}}"+"&remakrs="+remakrs+"&data="+formData+"&loan_data="+loanData,
         type: "GET",
        dataType: "json",
        success: function(data) {
            if (!data.data.status) {
                Swal.fire({
                    title: 'Error!',
                    text: data.data.message,
                    icon: 'error',
                });
                return;
            }
            const voucherEntries = data.data.data;
            var voucherEntriesHTML = ``;
            Object.keys(voucherEntries.ledgers).forEach((voucher) => {
                voucherEntries.ledgers[voucher].forEach((voucherDetail, index) => {
                    voucherEntriesHTML += `
                    <tr>
                    <td>${voucher}</td>
                    <td class="fw-bolder text-dark">${voucherDetail.ledger_group_code ? voucherDetail.ledger_group_code : ''}</td>
                    <td>${voucherDetail.ledger_code ? voucherDetail.ledger_code : ''}</td>
                    <td>${voucherDetail.ledger_name ? voucherDetail.ledger_name : ''}</td>
                    <td class="text-end">${voucherDetail.debit_amount > 0 ? parseFloat(voucherDetail.debit_amount).toFixed(2) : ''}</td>
                    <td class="text-end">${voucherDetail.credit_amount > 0 ? parseFloat(voucherDetail.credit_amount).toFixed(2) : ''}</td>
					</tr>
                    `
                });
            });
            voucherEntriesHTML+= `
            <tr>
                <td colspan="4" class="fw-bolder text-dark text-end">Total</td>
                <td class="fw-bolder text-dark text-end">${voucherEntries.total_debit.toFixed(2)}</td>
                <td class="fw-bolder text-dark text-end">${voucherEntries.total_credit.toFixed(2)}</td>
			</tr>
            `;
            document.getElementById('posting-table').innerHTML = voucherEntriesHTML;
            document.getElementById('voucher_doc_no').value = voucherEntries.document_number;
            document.getElementById('voucher_date').value = moment(voucherEntries.document_date).format('D/M/Y');
            document.getElementById('voucher_book_code').value = voucherEntries.book_code;
            document.getElementById('voucher_currency').value = voucherEntries.currency_code;
            if (type === "posted") {
                document.getElementById('posting_button').style.display = 'none';
            } else {
                document.getElementById('posting_button').style.removeProperty('display');
            }
            $('#postvoucher').modal('show');
        }
    });

}

function postVoucher(element)
{
    const bookId = "{{isset($vehicleLoan) ? $vehicleLoan->book_id : ''}}";
    const documentId = "{{isset($vehicleLoan) ? $vehicleLoan->id : ''}}";
    const postingApiUrl = "{{route('loanProcessingFee.post')}}"
    const data = $("#ldata").val();
    const doc = $("#doc").val();
    const loan_data = $("#loan_data").val();
    const remakrs = $("#remakrs").val();

    if (bookId && documentId) {
        $.ajax({
            url: postingApiUrl,
            type: "POST",
            dataType: "json",
            contentType: "application/json", // Specifies the request payload type
            data: JSON.stringify({
                       book_id: bookId,
                document_id: documentId,
                data: data,
                doc: doc,
                loan_data: loan_data,
                remakrs: remakrs,

            }),
            success: function(data) {
                const response = data.data;
                if (response.status) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                    });
                    location.href = '/loan/progress/legal-documentation';;
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Some internal error occured',
                    icon: 'error',
                });
            }
        });

    }
}

function resetPostVoucher()
{
    document.getElementById('voucher_doc_no').value = '';
    document.getElementById('voucher_date').value = '';
    document.getElementById('voucher_book_code').value = '';
    document.getElementById('voucher_currency').value = '';
    document.getElementById('posting-table').innerHTML = '';
    document.getElementById('posting_button').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function () {
    const ledgerSelect = document.getElementById('custledger_id');
    const groupSelect = document.getElementById('groupledger_id');

    const allGroupOptions = @if(isset($groups)) JSON.parse('@json($groups)') @else[] @endif;

    // Initialize Select2 for ledger-select
    $('#custledger_id').select2();

    // Attach event listener after initializing Select2
    $('#custledger_id').on('change', function () {
        const selectedLedgerOptions = Array.from(ledgerSelect.selectedOptions);
        const allowedGroupIds = new Set();

        // Collect all allowed group IDs from selected ledgers
        selectedLedgerOptions.forEach(option => {
            const groupIds = option.getAttribute('data-group-ids');
            if (groupIds) {
                groupIds.split(',').forEach(id => allowedGroupIds.add(id));
            }
        });

        console.log(allowedGroupIds);

        // Filter the group select options
        groupSelect.innerHTML = ''; // Clear current options
        allGroupOptions.forEach(group => {
            if (allowedGroupIds.has(String(group.id))) { // Ensure you're comparing the correct data types
                const option = document.createElement('option');
                option.value = group.id;
                option.text = group.name;
                groupSelect.appendChild(option); // Add valid options
            }
        });

        // Refresh Select2 UI for groupSelect
        $('#group-select').select2();
    });


});


    </script>
@endsection
