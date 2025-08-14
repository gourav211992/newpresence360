@extends('layouts.app')
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
                                        <li class="breadcrumb-item"><a href="index.html">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">View</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            {{-- <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm"><i
                                    data-feather="arrow-left-circle"></i> Back</button>
                            <button class="btn btn-danger btn-sm" data-bs-target="#reject" data-bs-toggle="modal"><i
                                    data-feather="x-circle"></i> Reject</button>
                            <button data-bs-toggle="modal" data-bs-target="#approved" class="btn btn-success btn-sm"><i
                                    data-feather="check-circle"></i> Approve</button>
                            <button data-bs-toggle="modal" data-bs-target="#viewassesgive" class="btn btn-success btn-sm"><i
                                    data-feather="file-text"></i> Assessment</button> --}}

                                    <div class="form-group breadcrumb-right">
                                        @if (isset($vehicleLoan->approvalStatus) && $vehicleLoan->approvalStatus == 'Submitted')
                                            <button onclick="javascript: history.go(-1)"
                                                class="btn btn-secondary btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-arrow-left-circle">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <polyline points="12 8 8 12 12 16"></polyline>
                                                    <line x1="16" y1="12" x2="8" y2="12"></line>
                                                </svg> Back</button>
                                        @endif
                                        @if (isset($vehicleLoan->approvalStatus) && $vehicleLoan->approvalStatus == 'draft')
                                        <button onclick="javascript: history.go(-1)"
                                                class="btn btn-secondary btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-arrow-left-circle">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <polyline points="12 8 8 12 12 16"></polyline>
                                                    <line x1="16" y1="12" x2="8" y2="12"></line>
                                                </svg> Back</button>
                                            <button onclick="javascript: history.go(-1)"
                                                class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                    </path>
                                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                                </svg> Delete</button>
                                            <button onclick="javascript: history.go(-1)"
                                                class="btn btn-primary btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-3">
                                                    <path d="M12 20h9"></path>
                                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                                </svg> Edit</button>
                                            <button onclick="javascript: history.go(-1)"
                                                class="btn btn-primary btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-3">
                                                    <path d="M12 20h9"></path>
                                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                                </svg> Submit</button>
                                        @endif
                                    </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">



                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">

                            <div class="card">
                                <div class="card-body customernewsection-form">

                                    <div class="text-center new-applicayion-help-txt  mb-1 pb-25">
                                        <h4 class="purchase-head"><span>Application for Home Loan</span></h4>
                                        <h6 class="mt-2">(Nishu Garg | 20 Lkh | 29-07-2024)</h6>
                                        <h4 class="mt-1 fw-bolder text-dark">CIBIL Score = 700</h4>
                                    </div>

                                    <h5 class="mt-1 mb-2 text-dark border-bottom pb-1"><strong><i
                                                data-feather='arrow-right-circle'></i> Application History</strong></h5>

                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="step-custhomapp bg-light p-1 customerapptimelines mb-1">
                                                <ul class="timeline ms-50 newdashtimline ">
                                                    <li class="timeline-item">
                                                        <span class="timeline-point timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div
                                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Payment Collection</h6>
                                                                <span class="timeline-event-time me-1">2 min ago</span>
                                                            </div>
                                                            <h5>(Aniket Singh)</h5>
                                                            <p>Description will come here</p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span
                                                            class="timeline-point timeline-point-warning timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div
                                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Payment Release</h6>
                                                                <span class="timeline-event-time me-1">5 min ago</span>
                                                            </div>
                                                            <h5>(Deewan Singh)</h5>
                                                            <p>Description will come here</p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span
                                                            class="timeline-point timeline-point-info timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div
                                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Approved</h6>
                                                                <span class="timeline-event-time me-1">10 min ago</span>
                                                            </div>
                                                            <h5>(Aniket Singh)</h5>
                                                            <p>Description will come here</p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span
                                                            class="timeline-point timeline-point-danger timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div
                                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Approved</h6>
                                                                <span class="timeline-event-time me-1">10 min ago</span>
                                                            </div>
                                                            <h5>(Aniket Singh)</h5>
                                                            <p class="mb-0">Description will come here</p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span class="timeline-point timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div
                                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Recovery Schedule</h6>
                                                                <span class="timeline-event-time me-1">12 min ago</span>
                                                            </div>
                                                            <h5>(Aniket Singh)</h5>
                                                            <p>Description will come here</p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span
                                                            class="timeline-point timeline-point-warning timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div
                                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Application Under Approval </h6>
                                                                <span class="timeline-event-time me-1">45 min ago</span>
                                                            </div>
                                                            <h5>(Aniket Singh)</h5>
                                                            <p>Description will come here </p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span
                                                            class="timeline-point timeline-point-info timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div
                                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Application Submitted</h6>
                                                                <span class="timeline-event-time me-1">2 day ago</span>
                                                            </div>
                                                            <h5>(Aniket Singh)</h5>
                                                            <p><a href="#"><i data-feather="download"
                                                                        class="me-50"></i></a> Description will come here
                                                            </p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span
                                                            class="timeline-point timeline-point-danger timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div
                                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Application Pending</h6>
                                                                <span class="timeline-event-time me-1">5 day ago</span>
                                                            </div>
                                                            <h5>(Aniket Singh)</h5>
                                                            <p class="mb-0"><a href="#"><i data-feather="download"
                                                                        class="me-50"></i></a> Application Submitted</p>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>

                                        </div>
                                        <div class="col-md-7">
                                            <div class="">
                                                <ul class="nav nav-tabs border-bottom mt-25 loandetailhistory"
                                                    role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-bs-toggle="tab"
                                                            href="#Assessmentschdule">Assessment</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab"
                                                            href="#Disbursement">Disbursal Schedule</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab"
                                                            href="#Recovery">Recovery Schedule</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab"
                                                            href="#paymentsc">Payment Released</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab"
                                                            href="#Collections">Recovery</a>
                                                    </li>
                                                </ul>


                                                <div class="tab-content pb-1 px-1">

                                                    <div class="tab-pane active" id="Assessmentschdule">

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
                                                                                <td>Remarks</td>
                                                                                <td>Description will come here</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>2</td>
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

                                                    <div class="tab-pane" id="Disbursement">

                                                        <div class="row mt-2">
                                                            <div class="col-md-12">
                                                                <div class="table-responsive">
                                                                    <table
                                                                        class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>#</th>
                                                                                <th>Disbursal Date</th>
                                                                                <th>Loan Amt.</th>
                                                                                <th>Disbursal Amt. Type</th>
                                                                                <th>Disbursal Mil.</th>
                                                                                <th>Disbursal Amt.</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>1</td>
                                                                                <td>30-07-2024</td>
                                                                                <td>1000000</td>
                                                                                <td>Fixed Amt</td>
                                                                                <td>Description will come here</td>
                                                                                <td>200000</td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td>2</td>
                                                                                <td>30-07-2024</td>
                                                                                <td>800000</td>
                                                                                <td>Fixed Amt</td>
                                                                                <td>Description will come here</td>
                                                                                <td>200000</td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td>3</td>
                                                                                <td>30-07-2024</td>
                                                                                <td>600000</td>
                                                                                <td>Fixed Amt</td>
                                                                                <td>Description will come here</td>
                                                                                <td>200000</td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td>4</td>
                                                                                <td>30-07-2024</td>
                                                                                <td>400000</td>
                                                                                <td>Fixed Amt</td>
                                                                                <td>Description will come here</td>
                                                                                <td>400000</td>
                                                                            </tr>

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
                                                                                <th>Loan Amount</th>
                                                                                <th>Rep. Type</th>
                                                                                <th>Rep. Period</th>
                                                                                <th>Int. %</th>
                                                                                <th>Prin. Amt.</th>
                                                                                <th>Int. Amt.</th>
                                                                                <th>Total Amt.</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr>
                                                                                <td>1</td>
                                                                                <td>100000</td>
                                                                                <td>Yearly</td>
                                                                                <td>1st</td>
                                                                                <td>10%</td>
                                                                                <td>25000</td>
                                                                                <td>10000</td>
                                                                                <td>35000</td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td>2</td>
                                                                                <td>75000</td>
                                                                                <td>Yearly</td>
                                                                                <td>2nd</td>
                                                                                <td>10%</td>
                                                                                <td>25000</td>
                                                                                <td>7500</td>
                                                                                <td>32500</td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td>3</td>
                                                                                <td>50000</td>
                                                                                <td>Yearly</td>
                                                                                <td>3rd</td>
                                                                                <td>10%</td>
                                                                                <td>25000</td>
                                                                                <td>5000</td>
                                                                                <td>30000</td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td>4</td>
                                                                                <td>25000</td>
                                                                                <td>Yearly</td>
                                                                                <td>4th</td>
                                                                                <td>10%</td>
                                                                                <td>25000</td>
                                                                                <td>2500</td>
                                                                                <td>27500</td>
                                                                            </tr>

                                                                        </tbody>


                                                                    </table>
                                                                </div>
                                                            </div>

                                                        </div>

                                                    </div>

                                                    <div class="tab-pane" id="paymentsc">

                                                        <div class="row mt-2">
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
                                                                            <tr>
                                                                                <td>1</td>
                                                                                <td>Description will come here</td>
                                                                                <td>500000</td>
                                                                                <td>300000</td>
                                                                                <td>30-07-2021</td>
                                                                                <td>200000</td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td>2</td>
                                                                                <td>Description will come here</td>
                                                                                <td>400000</td>
                                                                                <td>400000</td>
                                                                                <td>-</td>
                                                                                <td>-</td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td>3</td>
                                                                                <td>Description will come here</td>
                                                                                <td>500000</td>
                                                                                <td>300000</td>
                                                                                <td>30-07-2021</td>
                                                                                <td>200000</td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td>4</td>
                                                                                <td>Description will come here</td>
                                                                                <td>500000</td>
                                                                                <td>-</td>
                                                                                <td>30-07-2021</td>
                                                                                <td>500000</td>
                                                                            </tr>

                                                                        </tbody>


                                                                    </table>
                                                                </div>
                                                            </div>

                                                        </div>

                                                    </div>

                                                    <div class="tab-pane" id="Collections">

                                                        <div class="row mt-2">
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

                                                </div>


                                            </div>

                                        </div>
                                    </div>


                                    <hr />



                                    <div class="row">


                                        <div class="col-md-9 order-2 order-sm-1">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Loan Amount <span
                                                            class="text-danger">*</span></label>
                                                </div>

                                                <div class="col-md-5">
                                                    <input type="number"
                                                        value="{{ !empty($home_loan->loan_amount) ? $home_loan->loan_amount : '' }}"
                                                        class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Scheme for <span
                                                            class="text-danger">*</span></label>
                                                </div>

                                                <div class="col-md-5">
                                                    <input type="text"
                                                        value="{{ !empty($home_loan->scheme_for) ? $home_loan->scheme_for : '' }}"
                                                        class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Name <span
                                                            class="text-danger">*</span></label>
                                                </div>

                                                <div class="col-md-3 pe-sm-0 mb-1 mb-sm-0">
                                                    <input type="text"
                                                        value="{{ !empty($home_loan->name) ? $home_loan->name : '' }}"
                                                        class="form-control" placeholder="First Name" />
                                                </div>
                                                <div class="col-md-3 pe-sm-0 mb-1 mb-sm-0">
                                                    <input type="text"
                                                        value="{{ !empty($home_loan->name) ? $home_loan->name : '' }}"
                                                        class="form-control" placeholder="Middle Name" />
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="text"
                                                        value="{{ !empty($home_loan->name) ? $home_loan->name : '' }}"
                                                        class="form-control" placeholder="Last Name" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Gender</label>
                                                </div>

                                                <div class="col-md-5">
                                                    <div class="demo-inline-spacing">
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="Male" name="Gender"
                                                                class="form-check-input" checked="">
                                                            <label class="form-check-label fw-bolder"
                                                                for="Male">Male</label>
                                                        </div>
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="Female" name="Gender"
                                                                class="form-check-input">
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
                                                            <input type="radio" id="SC" name="Cast"
                                                                class="form-check-input">
                                                            <label class="form-check-label fw-bolder"
                                                                for="SC">SC</label>
                                                        </div>
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="ST" name="Cast"
                                                                class="form-check-input">
                                                            <label class="form-check-label fw-bolder"
                                                                for="ST">ST</label>
                                                        </div>

                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="Others" name="Cast"
                                                                class="form-check-input" checked="">
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
                                                            <input type="radio" id="Single" name="Marital"
                                                                class="form-check-input">
                                                            <label class="form-check-label fw-bolder"
                                                                for="Single">Single</label>
                                                        </div>
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="Married" name="Marital"
                                                                class="form-check-input" checked="">
                                                            <label class="form-check-label fw-bolder"
                                                                for="Married">Married</label>
                                                        </div>
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="Divorced" name="Marital"
                                                                class="form-check-input">
                                                            <label class="form-check-label fw-bolder"
                                                                for="Divorced">Divorced</label>
                                                        </div>
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="Widowed" name="Marital"
                                                                class="form-check-input">
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
                                                    <input type="text"
                                                        value="{{ !empty($home_loan->father_mother_name) ? $home_loan->father_mother_name : '' }}"
                                                        class="form-control" />
                                                </div>
                                            </div>


                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">PAN/GIR No. <span
                                                            class="text-danger">*</span></label>
                                                </div>

                                                <div class="col-md-5">
                                                    <input type="text"
                                                        value="{{ !empty($home_loan->gir_no) ? $home_loan->gir_no : '' }}"
                                                        class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Date of Birth</label>
                                                </div>

                                                <div class="col-md-5 col-8">
                                                    <input type="date" class="form-control" />
                                                </div>
                                                <!-- <div class="col-md-2 col-4">
                                                                 <input type="text" class="form-control" placeholder="Age" disabled  />
                                                            </div> -->
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Email</label>
                                                </div>

                                                <div class="col-md-5">
                                                    <div class="input-group input-group-merge">
                                                        <span class="input-group-text" id="basic-addon5"><i
                                                                data-feather='mail'></i></span>
                                                        <input type="text"
                                                            value="{{ !empty($home_loan->email) ? $home_loan->email : '' }}"
                                                            class="form-control" placeholder="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Mobile</label>
                                                </div>

                                                <div class="col-md-5">
                                                    <div class="input-group input-group-merge">
                                                        <span class="input-group-text" id="basic-addon5"><i
                                                                data-feather='smartphone'></i></span>
                                                        <input type="text"
                                                            value="{{ !empty($home_loan->mobile) ? $home_loan->mobile : '' }}"
                                                            class="form-control" placeholder="Mobile">
                                                    </div>
                                                </div>
                                            </div>



                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Spouse Name</label>
                                                </div>

                                                <div class="col-md-5">
                                                    <input type="text"
                                                        value="{{ !empty($home_loan->spouse_name) ? $home_loan->spouse_name : '' }}"
                                                        class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">No. of Dependents (Excluding Parents)<span
                                                            class="text-danger">*</span></label>
                                                </div>

                                                <div class="col-md-5">
                                                    <select class="form-select">
                                                        <option>Select</option>
                                                        <option>1</option>
                                                        <option>2</option>
                                                        <option>3</option>
                                                        <option>4</option>
                                                        <option>5</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">No. of Children</label>
                                                </div>

                                                <div class="col-md-5">
                                                    <select class="form-select">
                                                        <option>Select</option>
                                                        <option>1</option>
                                                        <option>2</option>
                                                        <option>3</option>
                                                        <option>4</option>
                                                        <option>5</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Earning Member in Family <span
                                                            class="text-danger">*</span></label>
                                                </div>

                                                <div class="col-md-5">
                                                    <div class="demo-inline-spacing">
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="Single" name="Earning"
                                                                class="form-check-input">
                                                            <label class="form-check-label fw-bolder"
                                                                for="Single">Yes</label>
                                                        </div>
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="Married" name="Earning"
                                                                class="form-check-input" checked="">
                                                            <label class="form-check-label fw-bolder"
                                                                for="Married">No</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>




                                        </div>

                                        <!-- <div class="col-md-3 order-1 order-sm-2 border-start mb-2">
                                                        
                                                        <div class="appli-photobox">
                                                            <p>Photo Size<br/>25mm X 35mm</p>
                                                        </div>
                                                        
                                                        <div class="mt-2 text-center">
                                                            <div class="image-uploadhide">
                                                                <a href="attribute.html" class="btn btn-outline-primary btn-sm waves-effect"> <i data-feather="upload"></i> Upload Customer Image</a>
                                                                <input type="file" class="">
                                                            </div>

                                                        </div>
                                                        
                                                        
                                                        
                                                    </div> -->
                                    </div>





                                    <div class="mt-3">
                                        <div class="step-custhomapp bg-light">
                                            <ul class="nav nav-tabs my-25 custapploannav" role="tablist">

                                                <li class="nav-item">
                                                    <a class="nav-link active" data-bs-toggle="tab"
                                                        href="#Address">Address</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#Employer">Employer
                                                        Detail</a>
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
                                                    <a class="nav-link" data-bs-toggle="tab" href="#Proposed">Proposed
                                                        Loan
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab"
                                                        href="#Guarantorapp">Guarantor</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab"
                                                        href="#Guarantorapp">Co-applicant</a>
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
                                                    <div class="col-md-6">

                                                        <h5 class="mt-1 mb-2  text-dark"><strong>Guarantor Detail</strong>
                                                        </h5>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Guarantor <span
                                                                        class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="demo-inline-spacing">
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="Guarantor"
                                                                            name="Guarantor" class="form-check-input"
                                                                            checked="">
                                                                        <label class="form-check-label fw-bolder"
                                                                            for="Guarantor">Yes</label>
                                                                    </div>
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="applicant"
                                                                            name="Guarantor" class="form-check-input">
                                                                        <label class="form-check-label fw-bolder"
                                                                            for="applicant">No</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Name</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Date of Birth</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="date" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Father's/Mother Name</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Relationship with
                                                                    Applicant</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Address</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <textarea class="form-control" placeholder="Street 1"></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">City</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">State</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <select class="form-select">
                                                                    <option>Select</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Pin Code</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Occupation</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <select class="form-select">
                                                                    <option>Select</option>
                                                                    <option>Salaried</option>
                                                                    <option>Self Employed</option>
                                                                    <option>Business</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Phone/Fax</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Email</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">PAN/GIR No.</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Net Annual Income</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                    </div>

                                                    <div class="col-md-6">

                                                        <h5 class="mt-1 mb-2  text-dark"><strong>Co-applicant Detail (if
                                                                present)</strong></h5>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Co-applicant <span
                                                                        class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="demo-inline-spacing">
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="GuarantorYes"
                                                                            name="Guarantor" class="form-check-input"
                                                                            checked="">
                                                                        <label class="form-check-label fw-bolder"
                                                                            for="GuarantorYes">Yes</label>
                                                                    </div>
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="applicantNo"
                                                                            name="Guarantor" class="form-check-input">
                                                                        <label class="form-check-label fw-bolder"
                                                                            for="applicantNo">No</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Name</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Date of Birth</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="date" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Father's/Mother Name</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Relationship with
                                                                    Applicant</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Address</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <textarea class="form-control" placeholder="Street 1"></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">City</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">State</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <select class="form-select">
                                                                    <option>Select</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Pin Code</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Occupation</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <select class="form-select">
                                                                    <option>Select</option>
                                                                    <option>Salaried</option>
                                                                    <option>Self Employed</option>
                                                                    <option>Business</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Phone/Fax</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Email</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">PAN/GIR No.</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Net Annual Income</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                            <div class="tab-pane active" id="Address">
                                                <div class="row">
                                                    <div class="col-md-6">

                                                        <h5 class="mt-1 mb-4 text-dark"><strong>Current Address</strong>
                                                        </h5>

                                                        <div class="row mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Address</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <textarea class="form-control" placeholder="Street 1"></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                &nbsp;
                                                            </div>

                                                            <div class="col-md-6">
                                                                <textarea class="form-control" placeholder="Street 2"></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">City</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">State</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <select class="form-select">
                                                                    <option>Select</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Pin Code</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Years in Current Address</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <select class="form-select">
                                                                    <option>Select</option>
                                                                    <option>1</option>
                                                                    <option>3</option>
                                                                    <option>5</option>
                                                                    <option>7</option>
                                                                    <option>10</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Residence Phone</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Office Phone</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Fax Number</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>


                                                    </div>

                                                    <div class="col-md-6">

                                                        <div class="mt-1 mb-2 d-flex flex-column">
                                                            <h5 class="text-dark mb-0 me-1"><strong>Permanent
                                                                    Address</strong></h5>

                                                            <div
                                                                class="form-check form-check-primary mt-25 custom-checkbox">
                                                                <input type="checkbox" class="form-check-input"
                                                                    id="colorCheck2" checked="">
                                                                <label class="form-check-label" for="colorCheck2">Same As
                                                                    Current Address</label>
                                                            </div>
                                                        </div>


                                                        <div class="row mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Address</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <textarea class="form-control" placeholder="Street 1"></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                &nbsp;
                                                            </div>

                                                            <div class="col-md-6">
                                                                <textarea class="form-control" placeholder="Street 2"></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">City</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">State</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <select class="form-select">
                                                                    <option>Select</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Pin Code</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Residence Phone</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>


                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="Employer">
                                                <div class="row">
                                                    <div class="col-md-6">

                                                        <h5 class="mt-1 mb-2 text-dark"><strong>Basic Info</strong></h5>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Employer Name</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Department</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Address</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <textarea class="form-control" placeholder="Street 1"></textarea>
                                                            </div>
                                                        </div>


                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">City</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">State</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <select class="form-select">
                                                                    <option>Select</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Pin Code</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Phone No.</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>

                                                            <div class="col-md-2">
                                                                <input type="text" class="form-control"
                                                                    placeholder="Extn No." />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Fax Number</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>


                                                    </div>
                                                    <div class="col-md-6">
                                                        <h5 class="mt-1 mb-2 text-dark"><strong>Other Info</strong></h5>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Company Email</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Designation</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <select class="form-select">
                                                                    <option>Select</option>
                                                                    <option>Executive</option>
                                                                    <option>Managerial</option>
                                                                    <option>Clerk</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Years with Employers</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Contact Person</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Name of Previous Employer</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Retirement Age</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>



                                                    </div>

                                                </div>

                                                <p class="mt-2  text-dark customapplsmallhead"><strong>Other
                                                        Assets</strong></p>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div
                                                                    class="form-check form-check-primary mt-25 custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        id="Color">
                                                                    <label class="form-check-label" for="Color">Color
                                                                        TV</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div
                                                                    class="form-check form-check-primary mt-25 custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        id="Telephone">
                                                                    <label class="form-check-label"
                                                                        for="Telephone">Telephone</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div
                                                                    class="form-check form-check-primary mt-25 custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        id="Refrigerator">
                                                                    <label class="form-check-label"
                                                                        for="Refrigerator">Refrigerator</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div
                                                                    class="form-check form-check-primary mt-25 custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        id="Cellular">
                                                                    <label class="form-check-label"
                                                                        for="Cellular">Cellular Phone</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div
                                                                    class="form-check form-check-primary mt-25 custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        id="Car">
                                                                    <label class="form-check-label"
                                                                        for="Car">Car</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div
                                                                    class="form-check form-check-primary mt-25 custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        id="Personal">
                                                                    <label class="form-check-label"
                                                                        for="WhatsPersonalapp">Personal Computer</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div
                                                                    class="form-check form-check-primary mt-25 custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        id="Wheeler">
                                                                    <label class="form-check-label" for="Wheeler">Two
                                                                        Wheeler</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <div
                                                                    class="form-check form-check-primary mt-25 custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        id="Washing">
                                                                    <label class="form-check-label" for="Washing">Washing
                                                                        Machine</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="tab-pane" id="Bank">

                                                <p class="mt-2  text-dark customapplsmallhead"><strong>About Bank accounts
                                                        (including credit facilities, if any)</strong></p>

                                                <div class="table-responsive">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                        <tbody>
                                                            <tr>
                                                                <td>#</td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td>
                                                                    <select class="form-select mw-100">
                                                                        <option>Select</option>
                                                                    </select>
                                                                </td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="date"class="form-control mw-100"></td>
                                                                <td><a href="#" class="text-primary"><i
                                                                            data-feather="plus-square"
                                                                            class="me-50"></i></a></td>
                                                            </tr>

                                                            <tr>
                                                                <td>1</td>
                                                                <td>HDFC Bank</td>
                                                                <td>Noida</td>
                                                                <td>5</td>
                                                                <td>Saving</td>
                                                                <td>50109876543334</td>
                                                                <td>20,000.00</td>
                                                                <td>26-07-20214</td>
                                                                <td><a href="#" class="text-danger"><i
                                                                            data-feather="trash-2" class="me-50"></i></a>
                                                                </td>
                                                            </tr>


                                                        </tbody>


                                                    </table>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="Income">
                                                <p class="mt-2  text-dark customapplsmallhead"><strong>1) Outstanding Loan
                                                        Details in Individual Name</strong></p>

                                                <div class="table-responsive">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                        <tbody>
                                                            <tr>
                                                                <td>#</td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="date"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="date"class="form-control mw-100"></td>
                                                                <td><a href="#" class="text-primary"><i
                                                                            data-feather="plus-square"
                                                                            class="me-50"></i></a></td>
                                                            </tr>

                                                            <tr>
                                                                <td>1</td>
                                                                <td>HDFC Bank</td>
                                                                <td>Personal</td>
                                                                <td>20-07-2024</td>
                                                                <td>10,000</td>
                                                                <td>8,000</td>
                                                                <td>1,000</td>
                                                                <td>-</td>
                                                                <td>-</td>
                                                                <td><a href="#" class="text-danger"><i
                                                                            data-feather="trash-2" class="me-50"></i></a>
                                                                </td>
                                                            </tr>


                                                        </tbody>


                                                    </table>
                                                </div>

                                                <p class="mt-3  text-dark customapplsmallhead"><strong>2) Income
                                                        details</strong></p>
                                                <div class="row">
                                                    <div class="col-md-6">

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Gross Monthly Income</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Net Monthly Income</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>



                                                    </div>
                                                </div>

                                                <p class="mt-2  text-dark customapplsmallhead"><strong>3) Details of other
                                                        present immovable properties</strong> (Other than proposed for
                                                    housing loan)</p>
                                                <div class="row">
                                                    <div class="col-md-6">

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-12">
                                                                <label class="form-label">Nature of properties:
                                                                    <strong>Encumbered</strong><span
                                                                        class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="demo-inline-spacing">
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="Single"
                                                                            name="Encumbered" class="form-check-input">
                                                                        <label class="form-check-label fw-bolder"
                                                                            for="Single">Yes</label>
                                                                    </div>
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="Married"
                                                                            name="Encumbered" class="form-check-input"
                                                                            checked="">
                                                                        <label class="form-check-label fw-bolder"
                                                                            for="Married">No</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>



                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Plot of Land</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control"
                                                                    placeholder="Location" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Agricultural Land</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control"
                                                                    placeholder="Location" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">House/Godowns</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control"
                                                                    placeholder="Location" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Others</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control"
                                                                    placeholder="Location" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Total Present estimated value of
                                                                    the above</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>


                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="Guarantorapp">

                                                <div class="row">
                                                    <div class="col-md-9">
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Name</label>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Father's/Mother Name</label>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <h5 class="mt-2  mb-2 text-dark border-bottom pb-1"><strong>Details
                                                                of other present immovable properties</strong> (Other than
                                                            proposed as security for housing loan)</h5>



                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-12">
                                                                <label class="form-label">Nature of properties:
                                                                    <strong>Encumbered</strong><span
                                                                        class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="demo-inline-spacing">
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="Single"
                                                                            name="Encumbered" class="form-check-input">
                                                                        <label class="form-check-label fw-bolder"
                                                                            for="Single">Yes</label>
                                                                    </div>
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input type="radio" id="Married"
                                                                            name="Encumbered" class="form-check-input"
                                                                            checked="">
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
                                                                <input type="number" class="form-control"
                                                                    placeholder="Location" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Agricultural Land</label>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <input type="number" class="form-control"
                                                                    placeholder="Location" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">House/Godowns</label>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <input type="text" class="form-control"
                                                                    placeholder="Location" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Others</label>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <input type="text" class="form-control"
                                                                    placeholder="Location" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Total Present estimated value of
                                                                    the above</label>
                                                            </div>

                                                            <div class="col-md-4">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>



                                                    </div>

                                                    <div class="col-md-3 border-start">

                                                        <div class="appli-photobox">
                                                            <p>Photo Size<br />25mm X 35mm</p>
                                                            <!--<img src="img/user.png" />-->
                                                        </div>

                                                        <div class="mt-2 text-center">
                                                            <div class="image-uploadhide">
                                                                <a href="attribute.html"
                                                                    class="btn btn-outline-primary btn-sm waves-effect">
                                                                    <i data-feather="upload"></i> Upload Profile Image</a>
                                                                <input type="file" class="">
                                                            </div>

                                                        </div>



                                                    </div>

                                                </div>




                                                <h5 class="mt-1 mb-2 text-dark border-bottom pb-1"><strong>Details of
                                                        Movable Assets in my name</strong></h5>

                                                <p class="mt-2  text-dark customapplsmallhead"><strong>1) Life Insurance
                                                        Policies</strong></p>

                                                <div class="table-responsive">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                        <tbody>
                                                            <tr>
                                                                <td>#</td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="date"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><a href="#" class="text-primary"><i
                                                                            data-feather="plus-square"
                                                                            class="me-50"></i></a></td>
                                                            </tr>

                                                            <tr>
                                                                <td>1</td>
                                                                <td>987654</td>
                                                                <td>25-07-2024</td>
                                                                <td>500000</td>
                                                                <td>LIC, Cannught Place</td>
                                                                <td>25000</td>
                                                                <td>15000</td>
                                                                <td><a href="#" class="text-danger"><i
                                                                            data-feather="trash-2"
                                                                            class="me-50"></i></a></td>
                                                            </tr>


                                                        </tbody>


                                                    </table>
                                                </div>

                                                <p class="mt-2  text-dark customapplsmallhead"><strong>2) Investment
                                                        (Share/Debenture/Term deposits/Govt. Securities like, NSC
                                                        stc.)</strong></p>

                                                <div class="table-responsive">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                        <tbody>
                                                            <tr>
                                                                <td>#</td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><a href="#" class="text-primary"><i
                                                                            data-feather="plus-square"
                                                                            class="me-50"></i></a></td>
                                                            </tr>

                                                            <tr>
                                                                <td>1</td>
                                                                <td>Mutual Fund</td>
                                                                <td>200000</td>
                                                                <td>50</td>
                                                                <td>300000</td>
                                                                <td><a href="#" class="text-danger"><i
                                                                            data-feather="trash-2"
                                                                            class="me-50"></i></a></td>
                                                            </tr>


                                                        </tbody>


                                                    </table>
                                                </div>


                                                <p class="mt-2  text-dark customapplsmallhead"><strong>3) Other movable
                                                        Assets</strong></p>

                                                <div class="table-responsive">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                        <tbody>
                                                            <tr>
                                                                <td>#</td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="date"class="form-control mw-100"></td>
                                                                <td><a href="#" class="text-primary"><i
                                                                            data-feather="plus-square"
                                                                            class="me-50"></i></a></td>
                                                            </tr>

                                                            <tr>
                                                                <td>1</td>
                                                                <td>Plot in Noida</td>
                                                                <td>500000</td>
                                                                <td>200000</td>
                                                                <td>25-07-2029</td>
                                                                <td><a href="#" class="text-danger"><i
                                                                            data-feather="trash-2"
                                                                            class="me-50"></i></a></td>
                                                            </tr>


                                                        </tbody>


                                                    </table>
                                                </div>


                                                <p class="mt-2  text-dark customapplsmallhead"><strong>4) Details of
                                                        Liabilities</strong></p>

                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Details of Loan/Advance availed
                                                                    from Bank's/Institution & Other Liabilities</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Name of Bank/Institution</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Purpose</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Amount of Loan</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Overdue if any</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Details of Personal Gurantee
                                                                    given, if any:</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Name of the Person on whose
                                                                    behalf (Bank/Institution)</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Amount of Commitment</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Overdue if any</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>


                                                    </div>
                                                </div>

                                                <p class="mt-2  text-dark customapplsmallhead"><strong>Particulars of
                                                        Legal Heirs</strong></p>

                                                <div class="table-responsive">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                        <tbody>
                                                            <tr>
                                                                <td>#</td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><a href="#" class="text-primary"><i
                                                                            data-feather="plus-square"
                                                                            class="me-50"></i></a></td>
                                                            </tr>

                                                            <tr>
                                                                <td>1</td>
                                                                <td>Aniket Singh</td>
                                                                <td>Father</td>
                                                                <td>25</td>
                                                                <td>Noida</td>
                                                                <td><a href="#" class="text-danger"><i
                                                                            data-feather="trash-2"
                                                                            class="me-50"></i></a></td>
                                                            </tr>


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
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-2">
                                                                <label class="form-label">Loan Amount Requested</label>
                                                            </div>

                                                            <div class="col-md-3">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-2">
                                                                <label class="form-label">Rate of Interest %</label>
                                                            </div>

                                                            <div class="col-md-3">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-2">
                                                                <label class="form-label">Floating/Fixed</label>
                                                            </div>

                                                            <div class="col-md-3">
                                                                <select class="form-select">
                                                                    <option>Select</option>
                                                                    <option>Floating</option>
                                                                    <option>Fixed</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-2">
                                                                <label class="form-label">Margin %</label>
                                                            </div>

                                                            <div class="col-md-3">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>


                                                    </div>
                                                </div>

                                                <h5 class="mt-1 mb-2 text-dark border-bottom pb-1"><strong>Details of
                                                        Movable Assets in my name</strong></h5>

                                                <p class="mt-2  text-dark customapplsmallhead"><strong>1) Life Insurance
                                                        Policies</strong></p>

                                                <div class="table-responsive">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                        <tbody>
                                                            <tr>
                                                                <td>#</td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="date"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><a href="#" class="text-primary"><i
                                                                            data-feather="plus-square"
                                                                            class="me-50"></i></a></td>
                                                            </tr>

                                                            <tr>
                                                                <td>1</td>
                                                                <td>987654</td>
                                                                <td>25-07-2024</td>
                                                                <td>500000</td>
                                                                <td>LIC, Cannught Place</td>
                                                                <td>25000</td>
                                                                <td>15000</td>
                                                                <td><a href="#" class="text-danger"><i
                                                                            data-feather="trash-2"
                                                                            class="me-50"></i></a></td>
                                                            </tr>


                                                        </tbody>


                                                    </table>
                                                </div>

                                                <p class="mt-2  text-dark customapplsmallhead"><strong>2)
                                                        Share/Debenture/Term deposits/Govt. Securities (NSC stc.)</strong>
                                                </p>

                                                <div class="table-responsive">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                        <tbody>
                                                            <tr>
                                                                <td>#</td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="date"class="form-control mw-100"></td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="date"class="form-control mw-100"></td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><a href="#" class="text-primary"><i
                                                                            data-feather="plus-square"
                                                                            class="me-50"></i></a></td>
                                                            </tr>

                                                            <tr>
                                                                <td>1</td>
                                                                <td>HDFC Bank</td>
                                                                <td>25-07-2024</td>
                                                                <td>500000</td>
                                                                <td>200000</td>
                                                                <td>25-07-2029</td>
                                                                <td>No</td>
                                                                <td><a href="#" class="text-danger"><i
                                                                            data-feather="trash-2"
                                                                            class="me-50"></i></a></td>
                                                            </tr>


                                                        </tbody>


                                                    </table>
                                                </div>


                                                <p class="mt-2  text-dark customapplsmallhead"><strong>3) Other movable
                                                        Assets</strong></p>

                                                <div class="table-responsive">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                        <tbody>
                                                            <tr>
                                                                <td>#</td>
                                                                <td><input type="text"class="form-control mw-100"></td>
                                                                <td><input type="year"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="number"class="form-control mw-100"></td>
                                                                <td><input type="date"class="form-control mw-100"></td>
                                                                <td><a href="#" class="text-primary"><i
                                                                            data-feather="plus-square"
                                                                            class="me-50"></i></a></td>
                                                            </tr>

                                                            <tr>
                                                                <td>1</td>
                                                                <td>Plot in Noida</td>
                                                                <td>2024</td>
                                                                <td>500000</td>
                                                                <td>200000</td>
                                                                <td>25-07-2029</td>
                                                                <td><a href="#" class="text-danger"><i
                                                                            data-feather="trash-2"
                                                                            class="me-50"></i></a></td>
                                                            </tr>


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
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Purpose and amount of
                                                                    loan/credit facilities</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="text" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Security/Repayment
                                                                    schedule</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Presenting Outstanding</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Liabilities other than to Bank
                                                                    and Financial Institutions:</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="number" class="form-control" />
                                                            </div>
                                                        </div>


                                                    </div>
                                                </div>

                                                <div class="bg-light rounded p-1 mt-2">
                                                    <p class="text-dark customapplsmallhead">Details of Personal Guarantee
                                                        given for any person/firm. If yes, furnish details(i.e. Name of the
                                                        Bank/institutions, on whose behalf, amount of gurantee, present
                                                        status of a/c, etc)</p>

                                                    <p class="mt-2  text-dark customapplsmallhead">I enclose/Submit
                                                        documentary proof in support of the above submissions.</p>
                                                </div>


                                            </div>

                                            <div class="tab-pane" id="Documentsupload">
                                                <h5 class="mt-2 mb-2  text-dark"><strong>Upload documents provided by the
                                                        Customer</strong></h5>

                                                <div class="row">
                                                    <div class="col-md-6">



                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Aadhar Card</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="file" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">PAN/GIR No.</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="file" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Plot Document</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="file" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Land Document</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="file" class="form-control" />
                                                            </div>
                                                        </div>

                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Income Proof</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="file" class="form-control" />
                                                            </div>
                                                        </div>


                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Scan form Application</label>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <input type="file" class="form-control" />
                                                            </div>
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

                </section>


            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection
