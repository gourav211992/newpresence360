@extends('layouts.app')



@section('styles')

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
										<li class="breadcrumb-item"><a href="index.html">Home</a>
										</li>
										<li class="breadcrumb-item active">View</li>


									</ol>
								</div>
							</div>
						</div>
					</div>
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right">
							<button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
							<button data-bs-toggle="modal" data-bs-target="#return" class="btn btn-warning btn-sm mb-50 mb-sm-0"><i data-feather="refresh-cw"></i> Return</button>
							<button  class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-target="#reject" data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</button>
                            <button data-bs-toggle="modal" data-bs-target="#upload-letter" class="btn btn-success btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Accept</button>

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
                                                   <h6 class="mt-2">(Nishu Garg | {{$overview->term_loan}} | {{date('d-m-Y')}})</h6>
                                           </div>

                                           <div class="bg-light-success rounded border p-1 mb-4">

                                            <div class="row">

                                                <div class="col-md-7">
                                                    <div class="step-custhomapp bg-light mb-0">
                                                        <ul class="nav nav-tabs mb-0 mt-25 custapploannav customrapplicationstatus" role="tablist">
                                                            <li class="nav-item">
                                                                <p class="statusactive"><i data-feather="check"></i></p>
                                                                <a class="nav-link active" data-bs-toggle="tab" href="#Appraisal">
                                                                    Appraisal
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <p class="statusactive"><i data-feather="check"></i></p>
                                                                <a class="nav-link" data-bs-toggle="tab" href="#Assessmentschdule">
                                                                    Assessment
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <p class="statusactive"><i data-feather="check"></i></p>
                                                                <a class="nav-link" data-bs-toggle="tab" href="#approval">
                                                                        Approval
                                                                    </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <p><i data-feather="check"></i></p>
                                                                <a class="nav-link" href="#Sansactioned">
                                                                    Sant. Letter
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <p><i data-feather="check"></i></p>
                                                                <a class="nav-link" href="#Processing">
                                                                    Proc. Fee
                                                                </a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <p><i data-feather="check"></i></p>
                                                                <a class="nav-link" href="#Legal">
                                                                    Legal Doc
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>

                                                    <div class="tab-content  bg-white">

                                                        <div class="tab-pane active" id="Appraisal">
                                                            <div class="">
                                                                <ul class="nav nav-tabs border-bottom mt-25 loandetailhistory" role="tablist">
                                                                    <li class="nav-item">
                                                                        <a class="nav-link active" data-bs-toggle="tab" href="#Overview">
                                                                            Overview
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item">
                                                                        <a class="nav-link" data-bs-toggle="tab" href="#Project">
                                                                            Project Report
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item">
                                                                        <a class="nav-link" data-bs-toggle="tab" href="#Disbursement">

                                                                            Disbursal Schedule
                                                                        </a>
                                                                    </li>
                                                                    <li class="nav-item">
                                                                        <a class="nav-link" data-bs-toggle="tab" href="#Recovery">
                                                                            Recovery Schedule
                                                                        </a>
                                                                    </li>
                                                                </ul>

                                                                <div class="tab-content">

                                                                    <div class="tab-pane active" id="Overview">
                                                                        <div class="row mt-2">
                                                                            <div class="col-md-12">
                                                                                <div class="table-responsive">
                                                                                    <table class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
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
                                                                                                    <td>Name of Unit</td>
                                                                                                    <td>M/s NB HOTEL</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>3</td>
                                                                                                    <td>Name of Proprietor</td>
                                                                                                    <td>Mrs. Binolis Nongsiej</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>4</td>
                                                                                                    <td>Address</td>
                                                                                                    <td>MAWBYRSHEM, NEW NONGSTOIN WEST KHASI HILLS DISTRICT.</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>5</td>
                                                                                                    <td>CIBIL Score</td>
                                                                                                    <td>300</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>6</td>
                                                                                                    <td>Project Cost</td>
                                                                                                    <td>RS. 127.29 Lakhs</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>7</td>
                                                                                                    <td>Term Loan</td>
                                                                                                    <td>RS. 85.00 LAKHS</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>8</td>
                                                                                                    <td>Promotor's Contribution</td>
                                                                                                    <td>RS. 42.29 LAKHS</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>9</td>
                                                                                                    <td>Interest Rate (P.A)</td>
                                                                                                    <td>10 %</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>10</td>
                                                                                                    <td>Loan Period</td>
                                                                                                    <td>5</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>11</td>
                                                                                                    <td>Repayment Type</td>
                                                                                                    <td>Yeraly</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>12</td>
                                                                                                    <td>No. of Installment(s)</td>
                                                                                                    <td>5</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>13</td>
                                                                                                    <td>Repayment Start After</td>
                                                                                                    <td>1st Disbursement</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>14</td>
                                                                                                    <td>Repayment Start Period</td>
                                                                                                    <td>1 Year</td>
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
                                                                                    <table class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
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
                                                                                                    <td>Constitution</td>
                                                                                                    <td>Proprietorship</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>2</td>
                                                                                                    <td>Proposed Project</td>
                                                                                                    <td>SETTING UP OF HOTEL CUM SHOPPING COMPLEX</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>3</td>
                                                                                                    <td>Project Site</td>
                                                                                                    <td>MAWBYRSHEM, NEW NONGSTOIN WEST KHASI HILLS DISTRICT.</td>
                                                                                                </tr>

                                                                                                <tr>
                                                                                                    <td>4</td>
                                                                                                    <td>Debt Equity Ratio</td>
                                                                                                    <td>2.01: 1</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>5</td>
                                                                                                    <td>Capacity </td>
                                                                                                    <td>23 DOUBLE BEDDED ROOMS &amp; 10 NOS. OF SHOPS.</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>6</td>
                                                                                                    <td>1 ST YEAR : 60%</td>

                                                                                                    <td>Description will come here</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>7</td>
                                                                                                    <td>Average D.S.C.R.</td>
                                                                                                    <td>1.98: 1</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>8</td>
                                                                                                    <td>Break Even Point</td>
                                                                                                    <td>52.74%</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>9</td>
                                                                                                    <td>Internal Rate of Return</td>
                                                                                                    <td>21.98%</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                    <td>10</td>
                                                                                                    <td>Collateral Type</td>
                                                                                                    <td>Land</td>
                                                                                                </tr>

                                                                                                <tr>
                                                                                                    <td>10</td>
                                                                                                    <td>Collateral Value</td>
                                                                                                    <td>700000</td>
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
                                                                                    <table class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
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
                                                                                                <tr>
                                                                                                    <td>1</td>
                                                                                                    <td>1000000</td>
                                                                                                    <td>Description will come here</td>
                                                                                                    <td>200000</td>
                                                                                                    <td>Repayment after 10 Months</td>
                                                                                                </tr>

                                                                                                <tr>
                                                                                                    <td>2</td>
                                                                                                    <td>800000</td>
                                                                                                    <td>Description will come here</td>
                                                                                                    <td>200000</td>
                                                                                                    <td>Repayment after 10 Months</td>
                                                                                                </tr>

                                                                                                <tr>
                                                                                                    <td>3</td>
                                                                                                    <td>600000</td>
                                                                                                    <td>Description will come here</td>
                                                                                                    <td>200000</td>
                                                                                                    <td>Repayment after 10 Months</td>
                                                                                                </tr>

                                                                                                <tr>
                                                                                                    <td>4</td>
                                                                                                    <td>400000</td>
                                                                                                    <td>Description will come here</td>
                                                                                                    <td>400000</td>
                                                                                                    <td>Repayment after 10 Months</td>
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
                                                                                    <table class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
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
                                                                                                <tr>
                                                                                                    <td>1</td>
                                                                                                    <td>1st</td>
                                                                                                    <td>500000</td>
                                                                                                    <td>50000</td>
                                                                                                    <td>25000</td>
                                                                                                    <td>500000</td>
                                                                                                </tr>

                                                                                                <tr>
                                                                                                    <td>2</td>
                                                                                                    <td>2nd</td>
                                                                                                    <td>500000</td>
                                                                                                    <td>50000</td>
                                                                                                    <td>125000</td>
                                                                                                    <td>375000</td>
                                                                                                </tr>

                                                                                                <tr>
                                                                                                    <td>3</td>
                                                                                                    <td>3rd</td>
                                                                                                    <td>375000</td>
                                                                                                    <td>37500</td>
                                                                                                    <td>125000</td>
                                                                                                    <td>250000</td>
                                                                                                </tr>

                                                                                                <tr>
                                                                                                    <td>4</td>
                                                                                                    <td>4th</td>
                                                                                                    <td>250000</td>
                                                                                                    <td>25000</td>
                                                                                                    <td>125000</td>
                                                                                                    <td>125000</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                  <td>5</td>
                                                                                                  <td>5th</td>
                                                                                                  <td>125000</td>
                                                                                                  <td>12500</td>
                                                                                                  <td>125000</td>
                                                                                                  <td>0</td>
                                                                                                </tr>
                                                                                                <tr>
                                                                                                  <td>&nbsp;</td>
                                                                                                  <td>&nbsp;</td>
                                                                                                  <td class="fw-bolder text-dark">Total</td>
                                                                                                  <td class="fw-bolder text-dark">175000</td>
                                                                                                  <td class="fw-bolder text-dark">500000</td>
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
                                                                        <table class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
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
                                                                                        <td><a href="#"><i data-feather='download'></i></a></td>
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
                                                                        <table class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
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
                                                                                        <td><a href="#"><i data-feather='download'></i></a></td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td>2</td>
                                                                                        <td>10-10-2024</td>
                                                                                        <td>Aniket Singh</td>
                                                                                        <td>Approve</td>
                                                                                        <td>Description will come here</td>
                                                                                        <td><a href="#"><i data-feather='download'></i></a></td>
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
                                                                        <table class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
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
                                                                                        <td><a href="#"><i data-feather='download'></i></a></td>
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
                                                                        <table class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
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
                                                                                        <td><a href="#"><i data-feather='download'></i></a></td>
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
                                                                        <table class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
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
                                                                                        <td><a href="#"><i data-feather='download'></i></a></td>
                                                                                    </tr>

                                                                                    <tr>
                                                                                        <td>6</td>
                                                                                        <td>Court Order</td>
                                                                                        <td><a href="#"><i data-feather='download'></i></a></td>
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
                                                        <ul class="nav nav-tabs border-bottom mt-25 loandetailhistory mb-0" role="tablist">
                                                            <li class="nav-item">
                                                                <a class="nav-link active" data-bs-toggle="tab" href="#paymentsc">
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
                                                                            <table class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
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

                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="table-responsive">
                                                                            <table class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
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
                                                                            <table class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
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
                                                                                            <td class="text-nowrap">10-10-2024</td>
                                                                                            <td>Appraisal</td>
                                                                                            <td>Description will come here</td>
                                                                                            <td>Aniket Singh</td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                            <td>2</td>
                                                                                            <td class="text-nowrap">10-10-2024</td>
                                                                                            <td>Assessment</td>
                                                                                            <td>Description will come here</td>
                                                                                            <td>Deewan Singh</td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                            <td>3</td>
                                                                                            <td class="text-nowrap">10-10-2024</td>
                                                                                            <td>Approved</td>
                                                                                            <td>Description will come here</td>
                                                                                            <td>Deewan Singh</td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                            <td>4</td>
                                                                                            <td class="text-nowrap">10-10-2024</td>
                                                                                            <td>Sansactioned Letter</td>
                                                                                            <td><a href="#"><i data-feather="download" class="me-50"></i></a> Description will come here</td>
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
                                                                                            <td><a href="#"><i data-feather="download" class="me-50"></i></a> Legal Doc</td>
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
                                                                        <div class="input-group input-group-merge docreplchatsearch border-bottom mb-25">
                                                                            <span class="input-group-text border-0 ps-0">
                                                                                <i data-feather="search"></i>
                                                                            </span>
                                                                            <input type="text" class="form-control border-0" id="email-search" placeholder="Search Doc" aria-label="Search...">
                                                                        </div>
                                                                        <div class="table-responsive">
                                                                            <table class="table border myrequesttablecbox table-striped po-order-detail loanapplicationlist">
                                                                                <thead>
                                                                                     <tr>
                                                                                        <th>#</th>
                                                                                        <th>Document Name</th>
                                                                                        <th>Download</th>
                                                                                      </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                         <tr>
                                                                                             <td>1</td>
                                                                                            <td>Aadhar Card</td>
                                                                                            <td><a href="#"><i data-feather='download'></i></a></td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                             <td>2</td>
                                                                                            <td>PAN/GIR No.</td>
                                                                                            <td><a href="#"><i data-feather='download'></i></a></td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                             <td>3</td>
                                                                                            <td>Plot Document</td>
                                                                                            <td><a href="#"><i data-feather='download'></i></a> <a href="#"><i data-feather='download'></i></a></td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                             <td>4</td>
                                                                                            <td>Land Document</td>
                                                                                            <td><a href="#"><i data-feather='download'></i></a></td>
                                                                                        </tr>

                                                                                        <tr>
                                                                                             <td>5</td>
                                                                                            <td>Scan form Application</td>
                                                                                            <td><a href="#"><i data-feather='download'></i></a></td>
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



											<div class="row">


                                                <div class="col-md-9 order-2 order-sm-1">
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Loan Amount <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="number" class="form-control"  />
                                                        </div>
                                                     </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Scheme for <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="text" class="form-control"/>
                                                        </div>
                                                     </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Name <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-3 pe-sm-0 mb-1 mb-sm-0">
                                                            <input type="text" class="form-control" placeholder="First Name" />
                                                        </div>
                                                        <div class="col-md-3 pe-sm-0 mb-1 mb-sm-0">
                                                            <input type="text" class="form-control" placeholder="Middle Name" />
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="text" class="form-control" placeholder="Last Name" />
                                                        </div>
                                                     </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Gender</label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <div class="demo-inline-spacing">
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Male" name="Gender" class="form-check-input" checked="">
                                                                    <label class="form-check-label fw-bolder" for="Male">Male</label>
                                                                </div>
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Female" name="Gender" class="form-check-input">
                                                                    <label class="form-check-label fw-bolder" for="Female">Female</label>
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
                                                                    <input type="radio" id="SC" name="Cast" class="form-check-input" >
                                                                    <label class="form-check-label fw-bolder" for="SC">SC</label>
                                                                </div>
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="ST" name="Cast" class="form-check-input">
                                                                    <label class="form-check-label fw-bolder" for="ST">ST</label>
                                                                </div>

                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Others" name="Cast" class="form-check-input" checked="">
                                                                    <label class="form-check-label fw-bolder" for="Others" >Others</label>
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
                                                                    <input type="radio" id="Single" name="Marital" class="form-check-input">
                                                                    <label class="form-check-label fw-bolder" for="Single">Single</label>
                                                                </div>
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Married" name="Marital" class="form-check-input" checked="">
                                                                    <label class="form-check-label fw-bolder" for="Married">Married</label>
                                                                </div>
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Divorced" name="Marital" class="form-check-input">
                                                                    <label class="form-check-label fw-bolder" for="Divorced">Divorced</label>
                                                                </div>
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Widowed" name="Marital" class="form-check-input">
                                                                    <label class="form-check-label fw-bolder" for="Widowed">Widowed</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Father's/Mother Name</label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="text" class="form-control"  />
                                                        </div>
                                                     </div>


                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">PAN/GIR No. <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="text" class="form-control"  />
                                                        </div>
                                                     </div>

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Date of Birth</label>
                                                        </div>

                                                        <div class="col-md-5 col-8">
                                                            <input type="date" class="form-control"  />
                                                        </div>
                                                        <div class="col-md-2 col-4">
                                                             <input type="text" class="form-control" placeholder="Age" disabled  />
                                                        </div>
                                                     </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Email</label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <div class="input-group input-group-merge">
                                                                <span class="input-group-text" id="basic-addon5"><i data-feather='mail'></i></span>
                                                                <input type="text" class="form-control" placeholder="">
                                                            </div>
                                                        </div>
                                                     </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Mobile</label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <div class="input-group input-group-merge">
                                                                <span class="input-group-text" id="basic-addon5"><i data-feather='smartphone'></i></span>
                                                                <input type="text" class="form-control" placeholder="Mobile">
                                                            </div>
                                                        </div>
                                                     </div>



                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Spouse Name</label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="text" class="form-control"  />
                                                        </div>
                                                     </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">No. of Dependents (Excluding Parents)<span class="text-danger">*</span></label>
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
                                                            <label class="form-label">Earning Member in Family <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <div class="demo-inline-spacing">
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Single" name="Earning" class="form-check-input">
                                                                    <label class="form-check-label fw-bolder" for="Single">Yes</label>
                                                                </div>
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Married" name="Earning" class="form-check-input" checked="">
                                                                    <label class="form-check-label fw-bolder" for="Married">No</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                     </div>




												</div>

                                                <div class="col-md-3 order-1 order-sm-2 border-start mb-2">

                                                    <div class="appli-photobox">
                                                        <p>Photo Size<br/>25mm X 35mm</p>
                                                        <!--<img src="img/user.png" />-->
                                                    </div>

                                                    <div class="mt-2 text-center">
                                                        <div class="image-uploadhide">
                                                            <a href="attribute.html" class="btn btn-outline-primary btn-sm waves-effect"> <i data-feather="upload"></i> Upload Customer Image</a>
                                                            <input type="file" class="">
                                                        </div>

                                                    </div>



                                                </div>
											</div>





											<div class="mt-3">
                                                <div class="step-custhomapp bg-light">
                                                    <ul class="nav nav-tabs my-25 custapploannav" role="tablist">

                                                        <li class="nav-item">
                                                            <a class="nav-link active" data-bs-toggle="tab" href="#Address">Address</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Employer">Employer Detail</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Bank">Bank Account</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Income">Loan and Income</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Details">Other Details</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Proposed">Proposed Loan
                                                            </a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Guarantorapp">Guarantor</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Guarantorapp">Co-applicant</a>
                                                        </li>
                                                        <li class="nav-item">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#Documentsupload">Documents</a>
                                                        </li>

                                                    </ul>

                                                </div>

												 <div class="tab-content pb-1 px-1">
														<div class="tab-pane" id="Details">


                                                            <div class="row">
                                                                <div class="col-md-6">

                                                                    <h5 class="mt-1 mb-2  text-dark"><strong>Guarantor Detail</strong></h5>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Guarantor <span class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <div class="demo-inline-spacing">
                                                                                <div class="form-check form-check-primary mt-25">
                                                                                    <input type="radio" id="Guarantor" name="Guarantor" class="form-check-input" checked="">
                                                                                    <label class="form-check-label fw-bolder" for="Guarantor">Yes</label>
                                                                                </div>
                                                                                <div class="form-check form-check-primary mt-25">
                                                                                    <input type="radio" id="applicant" name="Guarantor" class="form-check-input">
                                                                                    <label class="form-check-label fw-bolder" for="applicant">No</label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Name</label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Date of Birth</label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="date" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Father's/Mother Name</label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Relationship with Applicant</label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control"  />
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
                                                                            <input type="text" class="form-control"  />
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
                                                                            <input type="text" class="form-control"  />
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
                                                                            <input type="text" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Email</label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                     <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">PAN/GIR No.</label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Net Annual Income</label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                </div>

                                                                <div class="col-md-6">

                                                                    <h5 class="mt-1 mb-2  text-dark"><strong>Co-applicant Detail (if present)</strong></h5>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Co-applicant <span class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <div class="demo-inline-spacing">
                                                                                <div class="form-check form-check-primary mt-25">
                                                                                    <input type="radio" id="GuarantorYes" name="Guarantor" class="form-check-input" checked="">
                                                                                    <label class="form-check-label fw-bolder" for="GuarantorYes">Yes</label>
                                                                                </div>
                                                                                <div class="form-check form-check-primary mt-25">
                                                                                    <input type="radio" id="applicantNo" name="Guarantor" class="form-check-input">
                                                                                    <label class="form-check-label fw-bolder" for="applicantNo">No</label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Name</label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Date of Birth</label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="date" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Father's/Mother Name</label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Relationship with Applicant</label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control"  />
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
                                                                            <input type="text" class="form-control"  />
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
                                                                            <input type="text" class="form-control"  />
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
                                                                            <input type="text" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Email</label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                     <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">PAN/GIR No.</label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Net Annual Income</label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <input type="text" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                </div>
                                                            </div>

														</div>
														 <div class="tab-pane active" id="Address">
                                                            <div class="row">
                                                                     <div class="col-md-6">

                                                                         <h5 class="mt-1 mb-4 text-dark"><strong>Current Address</strong></h5>

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
                                                                                <input type="text" class="form-control"  />
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
                                                                                <input type="text" class="form-control"  />
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
                                                                                <input type="text" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Office Phone</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Fax Number</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control"  />
                                                                            </div>
                                                                         </div>


                                                                     </div>

                                                                    <div class="col-md-6">

                                                                        <div class="mt-1 mb-2 d-flex flex-column">
                                                                            <h5 class="text-dark mb-0 me-1"><strong>Permanent Address</strong></h5>

                                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input" id="colorCheck2" checked="">
                                                                                <label class="form-check-label" for="colorCheck2">Same As Current Address</label>
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
                                                                                <input type="text" class="form-control"  />
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
                                                                                <input type="text" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Residence Phone</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control"  />
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
                                                                                <input type="text" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Department</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control"  />
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
                                                                                <input type="text" class="form-control"  />
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
                                                                                <input type="text" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Phone No.</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control"  />
                                                                            </div>

                                                                            <div class="col-md-2">
                                                                                <input type="text" class="form-control" placeholder="Extn No."  />
                                                                            </div>
                                                                         </div>

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Fax Number</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control"  />
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
                                                                                <input type="text" class="form-control"  />
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
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Contact Person</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Name of Previous Employer</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Retirement Age</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control"  />
                                                                            </div>
                                                                         </div>



                                                                     </div>

                                                                 </div>

                                                            <p class="mt-2  text-dark customapplsmallhead"><strong>Other Assets</strong></p>

                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="row">
                                                                        <div class="col-md-4">
                                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input" id="Color">
                                                                                <label class="form-check-label" for="Color">Color TV</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input" id="Telephone">
                                                                                <label class="form-check-label" for="Telephone">Telephone</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input" id="Refrigerator">
                                                                                <label class="form-check-label" for="Refrigerator">Refrigerator</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input" id="Cellular">
                                                                                <label class="form-check-label" for="Cellular">Cellular Phone</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input" id="Car">
                                                                                <label class="form-check-label" for="Car">Car</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input" id="Personal">
                                                                                <label class="form-check-label" for="WhatsPersonalapp">Personal Computer</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input" id="Wheeler">
                                                                                <label class="form-check-label" for="Wheeler">Two Wheeler</label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input" id="Washing">
                                                                                <label class="form-check-label" for="Washing">Washing Machine</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

														</div>
                                                        <div class="tab-pane" id="Bank">

                                                            <p class="mt-2  text-dark customapplsmallhead"><strong>About Bank accounts (including credit facilities, if any)</strong></p>

                                                            <div class="table-responsive">
                                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                                                 <td><a href="#" class="text-primary"><i data-feather="plus-square" class="me-50"></i></a></td>
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
                                                                                <td><a href="#" class="text-danger"><i data-feather="trash-2" class="me-50"></i></a></td>
                                                                              </tr>


                                                                       </tbody>


                                                                </table>
                                                            </div>
														</div>
                                                        <div class="tab-pane" id="Income">
                                                            <p class="mt-2  text-dark customapplsmallhead"><strong>1) Outstanding Loan Details in Individual Name</strong></p>

                                                            <div class="table-responsive">
                                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                                                 <td><a href="#" class="text-primary"><i data-feather="plus-square" class="me-50"></i></a></td>
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
                                                                                <td><a href="#" class="text-danger"><i data-feather="trash-2" class="me-50"></i></a></td>
                                                                              </tr>


                                                                       </tbody>


                                                                </table>
                                                            </div>

                                                            <p class="mt-3  text-dark customapplsmallhead"><strong>2) Income details</strong></p>
                                                            <div class="row">
                                                                <div class="col-md-6">

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Gross Monthly Income</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Net Monthly Income</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>



                                                                     </div>
                                                            </div>

                                                            <p class="mt-2  text-dark customapplsmallhead"><strong>3) Details of other present immovable properties</strong> (Other than proposed for housing loan)</p>
                                                            <div class="row">
                                                                <div class="col-md-6">

                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-12">
                                                                                <label class="form-label">Nature of properties: <strong>Encumbered</strong><span class="text-danger">*</span></label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <div class="demo-inline-spacing">
                                                                                    <div class="form-check form-check-primary mt-25">
                                                                                        <input type="radio" id="Single" name="Encumbered" class="form-check-input">
                                                                                        <label class="form-check-label fw-bolder" for="Single">Yes</label>
                                                                                    </div>
                                                                                    <div class="form-check form-check-primary mt-25">
                                                                                        <input type="radio" id="Married" name="Encumbered" class="form-check-input" checked="">
                                                                                        <label class="form-check-label fw-bolder" for="Married">No</label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                         </div>



                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Plot of Land</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control" placeholder="Location"  />
                                                                            </div>
                                                                         </div>

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Agricultural Land</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control" placeholder="Location"  />
                                                                            </div>
                                                                         </div>

                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">House/Godowns</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control" placeholder="Location"  />
                                                                            </div>
                                                                         </div>

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Others</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control" placeholder="Location"  />
                                                                            </div>
                                                                         </div>

                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-4">
                                                                                <label class="form-label">Total Present estimated value of the above</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
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
                                                                            <input type="text" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                     <div class="row align-items-center mb-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Father's/Mother Name</label>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <input type="text" class="form-control"  />
                                                                        </div>
                                                                     </div>

                                                                    <h5 class="mt-2  mb-2 text-dark border-bottom pb-1"><strong>Details of other present immovable properties</strong> (Other than proposed as security for housing loan)</h5>



                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-12">
                                                                            <label class="form-label">Nature of properties: <strong>Encumbered</strong><span class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <div class="demo-inline-spacing">
                                                                                <div class="form-check form-check-primary mt-25">
                                                                                    <input type="radio" id="Single" name="Encumbered" class="form-check-input">
                                                                                    <label class="form-check-label fw-bolder" for="Single">Yes</label>
                                                                                </div>
                                                                                <div class="form-check form-check-primary mt-25">
                                                                                    <input type="radio" id="Married" name="Encumbered" class="form-check-input" checked="">
                                                                                    <label class="form-check-label fw-bolder" for="Married">No</label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                     </div>



                                                                     <div class="row align-items-center mb-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Plot of Land</label>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <input type="number" class="form-control" placeholder="Location"  />
                                                                        </div>
                                                                     </div>

                                                                     <div class="row align-items-center mb-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Agricultural Land</label>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <input type="number" class="form-control" placeholder="Location"  />
                                                                        </div>
                                                                     </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">House/Godowns</label>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <input type="text" class="form-control" placeholder="Location"  />
                                                                        </div>
                                                                     </div>

                                                                     <div class="row align-items-center mb-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Others</label>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <input type="text" class="form-control" placeholder="Location"  />
                                                                        </div>
                                                                     </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Total Present estimated value of the above</label>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <input type="number" class="form-control"  />
                                                                        </div>
                                                                     </div>



                                                                </div>

                                                                <div class="col-md-3 border-start">

                                                                        <div class="appli-photobox">
                                                                            <p>Photo Size<br/>25mm X 35mm</p>
                                                                            <!--<img src="img/user.png" />-->
                                                                        </div>

                                                                        <div class="mt-2 text-center">
                                                                            <div class="image-uploadhide">
                                                                                <a href="attribute.html" class="btn btn-outline-primary btn-sm waves-effect"> <i data-feather="upload"></i> Upload Profile Image</a>
                                                                                <input type="file" class="">
                                                                            </div>

                                                                        </div>



                                                                    </div>

                                                            </div>




                                                            <h5 class="mt-1 mb-2 text-dark border-bottom pb-1"><strong>Details of Movable Assets in my name</strong></h5>

                                                            <p class="mt-2  text-dark customapplsmallhead"><strong>1) Life Insurance Policies</strong></p>

                                                            <div class="table-responsive">
                                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                                                 <td><a href="#" class="text-primary"><i data-feather="plus-square" class="me-50"></i></a></td>
                                                                              </tr>

                                                                            <tr>
                                                                                <td>1</td>
                                                                                <td>987654</td>
                                                                                <td>25-07-2024</td>
                                                                                <td>500000</td>
                                                                                <td>LIC, Cannught Place</td>
                                                                                <td>25000</td>
                                                                                <td>15000</td>
                                                                                <td><a href="#" class="text-danger"><i data-feather="trash-2" class="me-50"></i></a></td>
                                                                              </tr>


                                                                       </tbody>


                                                                </table>
                                                            </div>

                                                            <p class="mt-2  text-dark customapplsmallhead"><strong>2) Investment (Share/Debenture/Term deposits/Govt. Securities like, NSC stc.)</strong></p>

                                                            <div class="table-responsive">
                                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                                                 <td><a href="#" class="text-primary"><i data-feather="plus-square" class="me-50"></i></a></td>
                                                                              </tr>

                                                                            <tr>
                                                                                <td>1</td>
                                                                                <td>Mutual Fund</td>
                                                                                <td>200000</td>
                                                                                <td>50</td>
                                                                                <td>300000</td>
                                                                                <td><a href="#" class="text-danger"><i data-feather="trash-2" class="me-50"></i></a></td>
                                                                              </tr>


                                                                       </tbody>


                                                                </table>
                                                            </div>


                                                            <p class="mt-2  text-dark customapplsmallhead"><strong>3) Other movable Assets</strong></p>

                                                            <div class="table-responsive">
                                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                                                 <td><a href="#" class="text-primary"><i data-feather="plus-square" class="me-50"></i></a></td>
                                                                              </tr>

                                                                            <tr>
                                                                                <td>1</td>
                                                                                <td>Plot in Noida</td>
                                                                                <td>500000</td>
                                                                                <td>200000</td>
                                                                                <td>25-07-2029</td>
                                                                                <td><a href="#" class="text-danger"><i data-feather="trash-2" class="me-50"></i></a></td>
                                                                              </tr>


                                                                       </tbody>


                                                                </table>
                                                            </div>


                                                            <p class="mt-2  text-dark customapplsmallhead"><strong>4) Details of Liabilities</strong></p>

                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-3">
                                                                                <label class="form-label">Details of Loan/Advance availed from Bank's/Institution & Other Liabilities</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>
                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-3">
                                                                                <label class="form-label">Name of Bank/Institution</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-3">
                                                                                <label class="form-label">Purpose</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-3">
                                                                                <label class="form-label">Amount of Loan</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-3">
                                                                                <label class="form-label">Overdue if any</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-3">
                                                                                <label class="form-label">Details of Personal Gurantee given, if any:</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-3">
                                                                                <label class="form-label">Name of the Person on whose behalf (Bank/Institution)</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-3">
                                                                                <label class="form-label">Amount of Commitment</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-3">
                                                                                <label class="form-label">Overdue if any</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>


                                                                     </div>
                                                            </div>

                                                            <p class="mt-2  text-dark customapplsmallhead"><strong>Particulars of Legal Heirs</strong></p>

                                                            <div class="table-responsive">
                                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                                                <td><a href="#" class="text-primary"><i data-feather="plus-square" class="me-50"></i></a></td>
                                                                              </tr>

                                                                            <tr>
                                                                                <td>1</td>
                                                                                <td>Aniket Singh</td>
                                                                                <td>Father</td>
                                                                                <td>25</td>
                                                                                <td>Noida</td>
                                                                                <td><a href="#" class="text-danger"><i data-feather="trash-2" class="me-50"></i></a></td>
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
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-2">
                                                                                <label class="form-label">Loan Amount Requested</label>
                                                                            </div>

                                                                            <div class="col-md-3">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-2">
                                                                                <label class="form-label">Rate of Interest %</label>
                                                                            </div>

                                                                            <div class="col-md-3">
                                                                                <input type="number" class="form-control"  />
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
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>


                                                                     </div>
                                                            </div>

                                                            <h5 class="mt-1 mb-2 text-dark border-bottom pb-1"><strong>Details of Movable Assets in my name</strong></h5>

                                                            <p class="mt-2  text-dark customapplsmallhead"><strong>1) Life Insurance Policies</strong></p>

                                                            <div class="table-responsive">
                                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                                                 <td><a href="#" class="text-primary"><i data-feather="plus-square" class="me-50"></i></a></td>
                                                                              </tr>

                                                                            <tr>
                                                                                <td>1</td>
                                                                                <td>987654</td>
                                                                                <td>25-07-2024</td>
                                                                                <td>500000</td>
                                                                                <td>LIC, Cannught Place</td>
                                                                                <td>25000</td>
                                                                                <td>15000</td>
                                                                                <td><a href="#" class="text-danger"><i data-feather="trash-2" class="me-50"></i></a></td>
                                                                              </tr>


                                                                       </tbody>


                                                                </table>
                                                            </div>

                                                            <p class="mt-2  text-dark customapplsmallhead"><strong>2) Share/Debenture/Term deposits/Govt. Securities (NSC stc.)</strong></p>

                                                            <div class="table-responsive">
                                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                                                 <td><a href="#" class="text-primary"><i data-feather="plus-square" class="me-50"></i></a></td>
                                                                              </tr>

                                                                            <tr>
                                                                                <td>1</td>
                                                                                <td>HDFC Bank</td>
                                                                                <td>25-07-2024</td>
                                                                                <td>500000</td>
                                                                                <td>200000</td>
                                                                                <td>25-07-2029</td>
                                                                                <td>No</td>
                                                                                <td><a href="#" class="text-danger"><i data-feather="trash-2" class="me-50"></i></a></td>
                                                                              </tr>


                                                                       </tbody>


                                                                </table>
                                                            </div>


                                                            <p class="mt-2  text-dark customapplsmallhead"><strong>3) Other movable Assets</strong></p>

                                                            <div class="table-responsive">
                                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
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
                                                                                 <td><a href="#" class="text-primary"><i data-feather="plus-square" class="me-50"></i></a></td>
                                                                              </tr>

                                                                            <tr>
                                                                                <td>1</td>
                                                                                <td>Plot in Noida</td>
                                                                                <td>2024</td>
                                                                                <td>500000</td>
                                                                                <td>200000</td>
                                                                                <td>25-07-2029</td>
                                                                                <td><a href="#" class="text-danger"><i data-feather="trash-2" class="me-50"></i></a></td>
                                                                              </tr>


                                                                       </tbody>


                                                                </table>
                                                            </div>


                                                            <p class="mt-2  text-dark customapplsmallhead"><strong>4) Details of Liabilities</strong></p>

                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-3">
                                                                                <label class="form-label">Name of Bank/Institution and it's Branch</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-3">
                                                                                <label class="form-label">Purpose and amount of loan/credit facilities</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-3">
                                                                                <label class="form-label">Security/Repayment schedule</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                         <div class="row align-items-center mb-1">
                                                                            <div class="col-md-3">
                                                                                <label class="form-label">Presenting Outstanding</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>

                                                                        <div class="row align-items-center mb-1">
                                                                            <div class="col-md-3">
                                                                                <label class="form-label">Liabilities other than to Bank and Financial Institutions:</label>
                                                                            </div>

                                                                            <div class="col-md-6">
                                                                                <input type="number" class="form-control"  />
                                                                            </div>
                                                                         </div>


                                                                     </div>
                                                            </div>

                                                            <div class="bg-light rounded p-1 mt-2">
                                                                <p class="text-dark customapplsmallhead">Details of Personal Guarantee given for any person/firm. If yes, furnish details(i.e. Name of the Bank/institutions, on whose behalf, amount of gurantee, present status of a/c, etc)</p>

                                                                <p class="mt-2  text-dark customapplsmallhead">I enclose/Submit documentary proof in support of the above submissions.</p>
                                                            </div>


														</div>

                                                        <div class="tab-pane" id="Documentsupload">
                                                            <div class="table-responsive-md">
                                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                   <thead>
                                                                        <tr>
                                                                           <th>#</th>
                                                                           <th>Document Name</th>
                                                                           <th>Upload File</th>
                                                                           <th>Attachments</th>
                                                                           <th width="40px">Action</th>
                                                                         </tr>
                                                                       </thead>
                                                                       <tbody>
                                                                            <tr>
                                                                               <td>1</td>
                                                                               <td>
                                                                                 <select class="form-select mw-100">
                                                                                       <option>Select</option>
                                                                                   </select>
                                                                               </td>
                                                                               <td>
                                                                                   <input type="file" multiple class="form-control mw-100">
                                                                                </td>
                                                                                 <td>
                                                                                    <div class="image-uplodasection expenseadd-sign">
                                                                                        <i data-feather="file-text" class="fileuploadicon"></i>
                                                                                       <div class="delete-img text-danger">
                                                                                           <i data-feather="x"></i>
                                                                                       </div>
                                                                                    </div>
                                                                                    <div class="image-uplodasection expenseadd-sign">
                                                                                        <i data-feather="file-text" class="fileuploadicon"></i>
                                                                                       <div class="delete-img text-danger">
                                                                                           <i data-feather="x"></i>
                                                                                       </div>
                                                                                    </div>
                                                                                </td>
                                                                                <td><a href="#" class="text-primary"><i data-feather="plus-square"></i></a></td>
                                                                             </tr>
                                                                             <tr>
                                                                               <td>2</td>
                                                                               <td>
                                                                                 <select class="form-select mw-100">
                                                                                       <option>Select</option>
                                                                                   </select>
                                                                               </td>
                                                                               <td>
                                                                                   <input type="file" multiple class="form-control mw-100">
                                                                                </td>
                                                                                 <td>
                                                                                    <div class="image-uplodasection expenseadd-sign">
                                                                                        <i data-feather="file-text" class="fileuploadicon"></i>
                                                                                       <div class="delete-img text-danger">
                                                                                           <i data-feather="x"></i>
                                                                                       </div>
                                                                                    </div>
                                                                                    <div class="image-uplodasection expenseadd-sign">
                                                                                        <i data-feather="file-text" class="fileuploadicon"></i>
                                                                                       <div class="delete-img text-danger">
                                                                                           <i data-feather="x"></i>
                                                                                       </div>
                                                                                    </div>
                                                                                </td>
                                                                                <td><a href="#" class="text-danger"><i data-feather="trash-2"></i></a></td>
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
                    <!-- Modal to add new record -->

                </section>


            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="modal fade" id="upload-letter" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-4 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Upload Sansaction Letter</h1>
					<p class="text-center">Enter the details below.</p>

					<div class="row mt-3 customernewsection-form">


						   <div class="col-md-12 mb-2">
                                <label class="form-label">Upload Letter <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" />
                            </div>


                            <div class="col-md-12 mb-1">
                                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                <textarea class="form-control" placeholder="Enter Remarks"></textarea>
                            </div>



				    </div>
                </div>

				<div class="modal-footer justify-content-center">
						<button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
					<button type="reset" class="btn btn-primary">Submit</button>
				</div>
			</div>
		</div>
	</div>


    <div class="modal fade" id="reject" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Reject Home Loan Application</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | {{$overview->term_loan}} | {{date('d-m-Y')}}</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body pb-2">

					<div class="row mt-1">

						   <div class="col-md-12">

                                     <div class="mb-1">
                                       <label class="form-label">Upload Document</label>
                                       <input type="file" class="form-control" />
                                     </div>

                                     <div class="mb-1">
                                       <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                       <textarea class="form-control"></textarea>
                                     </div>

                            </div>

					</div>
				</div>

				<div class="modal-footer justify-content-center">
						<button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
					<button type="reset" class="btn btn-primary">Submit</button>
				</div>
			</div>
		</div>
	</div>

    <div class="modal fade" id="return" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Return Home Loan Application</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | {{$overview->term_loan}} | {{date('d-m-Y')}}</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body pb-2">

					<div class="row mt-1">

						   <div class="col-md-12">

                                     <div class="mb-1">
                                       <label class="form-label">Upload Document</label>
                                       <input type="file" class="form-control" />
                                     </div>


                                     <div class="mb-1">
                                       <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                       <textarea class="form-control"></textarea>
                                     </div>

                            </div>

					</div>
				</div>

				<div class="modal-footer justify-content-center">
						<button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
					<button type="reset" class="btn btn-primary">Submit</button>
				</div>
			</div>
		</div>
	</div>

@endsection



@section('scripts')

<script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })


        $(function() {
           $("input[name='loanassesment']").click(function() {
             if ($("#Disbursement1").is(":checked")) {
               $(".selectdisbusement").show();
               $(".cibil-score").hide();
             } else {
               $(".selectdisbusement").hide();
               $(".cibil-score").show();
             }
           });
         });


    </script>

@endsection
