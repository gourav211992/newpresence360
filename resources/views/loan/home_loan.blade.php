@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
             <div class="content-header row">
                <div class="content-header-left col-md-6 col-4 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Loan Application</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="javascript::void(0);">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">All Request
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div> 
				 <div class="content-header-right text-sm-end col-md-6">
                    <div class="form-group breadcrumb-right"> 
							{{-- <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button> 
							<button class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-target="#reject" data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</button> 
							<button class="btn btn-success btn-sm mb-50 mb-sm-0" data-bs-target="#approved" data-bs-toggle="modal"><i data-feather="check-circle" ></i> Approve</button> 
                            <a class="btn btn-primary btn-sm mb-50 mb-sm-0"  href="{{route('loan.home-loan-add')}}"><i data-feather="check-circle" ></i> Add New</a> 
							<button class="btn btn-success btn-sm mb-50 mb-sm-0"><i data-feather="check-circle" ></i> Submit</button>   --}}

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
            <div class="content-body dasboardnewbody">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
                <!-- ChartJS section start -->
                <section id="chartjs-chart">
                    <div class="row">
						
						  
						
						<div class="col-md-12 col-12">
                            <div class="card  new-cardbox">
								
								
								<ul class="nav nav-tabs border-bottom" role="tablist">
									<li class="nav-item">
										<a class="nav-link active" data-bs-toggle="tab" href="#Pending">My Applications &nbsp;<span>(10)</span></a>
									</li><!--
									<li class="nav-item">
										<a class="nav-link" data-bs-toggle="tab" href="#Approved">Approved by MD &nbsp;<span>(5)</span></a>
									</li>
									<li class="nav-item">
										<a class="nav-link" data-bs-toggle="tab" href="#BOD">Approved by BOD &nbsp;<span>(8)</span></a>
									</li>
                                    <li class="nav-item">
										<a class="nav-link" data-bs-toggle="tab" href="#Rejected">Rejected &nbsp;<span>(9)</span></a>
									</li>
                                    <li class="nav-item">
										<a class="nav-link" data-bs-toggle="tab" href="#Loan">Loan Sanctioned &nbsp;<span>(5)</span></a>
									</li>-->
								</ul>
								
								 <div class="tab-content">
                                        <div class="tab-pane active" id="Pending">
                                            <div class="table-responsive">
												<table class="datatables-basic table myrequesttablecbox loanapplicationlist">
													<thead>
														<tr>
															<th class="pe-0">
																<div class="form-check form-check-inline">
																	<input class="form-check-input" type="checkbox" id="inlineCheckbox1">
																</div>
															</th>
															<th>Application No.</th>
															<th>Reference No.</th>
															<th>Date</th>
															<th>Name</th>
															<th>Email-ID</th>
															<th>Mobile No.</th>
															<th>Loan Type</th>
															<th>Loan Amt.</th>
															<th>Aging</th> 
															<th>Status</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
                                                    @foreach($home_loan as $key => $value)
														<tr>
															<td class="pe-0">
																<div class="form-check form-check-inline">
																	<input class="form-check-input" type="checkbox" id="inlineCheckbox1">
																</div>
															</td>
															<td class="fw-bolder text-dark">-</td>
															<td class="fw-bolder text-dark">-</td>
															<td>-</td>
															<td>{{$value->name}}</td>
															<td>{{$value->email}}</td>
															<td>{{$value->mobile}}</td>
															<td>-</td> 
															<td>{{$value->loan_amount}}</td>
                                                            <td>30 Days</td> 
															<td><span class="badge rounded-pill badge-light-warning badgeborder-radius">Pending</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
                                                                        <a class="dropdown-item" data-bs-toggle="modal" href="#viewassesgive">
																			<i data-feather='file-text' class="me-50"></i>
																			<span>Assessment</span>
																		</a>
                                                                        <a class="dropdown-item" data-bs-toggle="modal" href="#Disbursement">
																			<i data-feather='calendar' class="me-50"></i>
																			<span>Disbursal Schedule</span>
																		</a>
                                                                        <a class="dropdown-item" data-bs-toggle="modal" href="#Recovery">
																			<i data-feather='clipboard' class="me-50"></i>
																			<span>Recovery Schedule</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
                                                        @endforeach
													</tbody>
												</table>
											</div>
                                        </div>
                                         <div class="tab-pane" id="Approved">
                                            <div class="table-responsive">
												<table class="datatables-basic table myrequesttablecbox loanapplicationlist">
													<thead>
														<tr>
															<th>#</th>
															<th>Date</th>
															<th>Name</th>
															<th>Email-ID</th>
															<th>Mobile No.</th>
															<th>Loan Type</th>
															<th>Loan Amt.</th>
															<th>Aging</th>
															<th>Status</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td>1</td>
															<td>20-07-2024</td>
															<td class="fw-bolder text-dark">Nishu Garg</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Home </td> 
															<td>20 Lkh</td>
                                                            <td>30 Days</td>
															<td><span class="badge rounded-pill badge-light-success badgeborder-radius">Approved by MD</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>2</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Kundan Kumar</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Term</td> 
															<td>20 Lkh</td>
															<td>30 Days</td> 
															<td><span class="badge rounded-pill badge-light-success badgeborder-radius">Approved by MD</span></td>  
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>3</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Rahul Upadhyay</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Vehicle</td> 
															<td>20 Lkh</td>
															<td>30 Days</td> 
															<td><span class="badge rounded-pill badge-light-success badgeborder-radius">Approved by MD</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>4</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Ashish Kumar</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Home</td> 
															<td>20 Lkh</td>
															<td>30 Days</td> 
															<td><span class="badge rounded-pill badge-light-success badgeborder-radius">Approved by MD</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>5</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Inder Singh</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Term</td> 
															<td>20 Lkh</td>
															<td>30 Days</td>
															<td><span class="badge rounded-pill badge-light-success badgeborder-radius">Approved by MD</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>

													</tbody>
												</table>
											</div>
                                        </div> 
										<div class="tab-pane" id="BOD">
                                            <div class="table-responsive">
												<table class="datatables-basic table myrequesttablecbox loanapplicationlist">
													<thead>
														<tr>
															<th>#</th>
															<th>Date</th>
															<th>Name</th>
															<th>Email-ID</th>
															<th>Mobile No.</th>
															<th>Loan Type</th>
															<th>Loan Amt.</th>
															<th>Aging</th> 
															<th>Status</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td>1</td>
															<td>20-07-2024</td>
															<td class="fw-bolder text-dark">Nishu Garg</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Home </td> 
															<td>20 Lkh</td>
                                                            <td>30 Days</td> 
															<td><span class="badge rounded-pill badge-light-success badgeborder-radius">Approved by BOD</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>2</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Kundan Kumar</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Term</td> 
															<td>20 Lkh</td>
															<td>30 Days</td> 
															<td><span class="badge rounded-pill badge-light-success badgeborder-radius">Approved by BOD</span></td>  
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>3</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Rahul Upadhyay</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Vehicle</td> 
															<td>20 Lkh</td>
															<td>30 Days</td> 
															<td><span class="badge rounded-pill badge-light-success badgeborder-radius">Approved by BOD</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>4</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Ashish Kumar</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Home</td> 
															<td>20 Lkh</td>
															<td>30 Days</td> 
															<td><span class="badge rounded-pill badge-light-success badgeborder-radius">Approved by BOD</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>5</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Inder Singh</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Term</td> 
															<td>20 Lkh</td>
															<td>30 Days</td>
															<td><span class="badge rounded-pill badge-light-success badgeborder-radius">Approved by BOD</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>

													</tbody>
												</table>
											</div>
                                        </div>
                                         <div class="tab-pane" id="Rejected">
                                            <div class="table-responsive">
												<table class="datatables-basic table myrequesttablecbox loanapplicationlist">
													<thead>
														<tr>
															<th>#</th>
															<th>Date</th>
															<th>Name</th>
															<th>Email-ID</th>
															<th>Mobile No.</th>
															<th>Loan Type</th>
															<th>Loan Amt.</th>
															<th>Aging</th> 
															<th>Status</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td>1</td>
															<td>20-07-2024</td>
															<td class="fw-bolder text-dark">Nishu Garg</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Home </td> 
															<td>20 Lkh</td>
                                                            <td>30 Days</td> 
															<td><span class="badge rounded-pill badge-light-danger badgeborder-radius">Rejected</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>2</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Kundan Kumar</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Term</td> 
															<td>20 Lkh</td>
															<td>30 Days</td> 
															<td><span class="badge rounded-pill badge-light-danger badgeborder-radius">Rejected</span></td>  
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>3</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Rahul Upadhyay</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Vehicle</td> 
															<td>20 Lkh</td>
															<td>30 Days</td> 
															<td><span class="badge rounded-pill badge-light-danger badgeborder-radius">Rejected</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>4</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Ashish Kumar</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Home</td> 
															<td>20 Lkh</td>
															<td>30 Days</td> 
															<td><span class="badge rounded-pill badge-light-danger badgeborder-radius">Rejected</span></td>  
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>5</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Inder Singh</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Term</td> 
															<td>20 Lkh</td>
															<td>30 Days</td>
															<td><span class="badge rounded-pill badge-light-danger badgeborder-radius">Rejected</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>

													</tbody>
												</table>
											</div>
                                        </div>
                                         <div class="tab-pane" id="Loan">
                                            <div class="table-responsive">
												<table class="datatables-basic table myrequesttablecbox loanapplicationlist">
													<thead>
														<tr>
															<th>#</th>
															<th>Date</th>
															<th>Name</th>
															<th>Email-ID</th>
															<th>Mobile No.</th>
															<th>Loan Type</th>
															<th>Loan Amt.</th>
															<th>Repayment Type</th>
                                                            <th>No. of Repayment</th>
                                                            <th>Loan Bal.</th>
                                                            <th>No. of Overdue</th>
															<th>Status</th>
															<th>Action</th>
														</tr>
													</thead>
													<tbody>
														<tr>
															<td>1</td>
															<td>20-07-2024</td>
															<td class="fw-bolder text-dark">Nishu Garg</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Home </td> 
															<td>20 Lkh</td>
                                                            <td>Yearly</td>
                                                            <td>6/0</td>
                                                            <td>-</td>
                                                            <td>-</td>
															<td><span class="badge rounded-pill badge-light-success badgeborder-radius">Sanctioned</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
                                                                        <a class="dropdown-item" data-bs-toggle="modal" href="#Payment">
																			<i data-feather='dollar-sign' class="me-50"></i>
																			<span>Release Payment</span>
																		</a>
                                                                        <a class="dropdown-item" data-bs-toggle="modal" href="#Collection">
																			<i data-feather='calendar' class="me-50"></i>
																			<span>Collection</span>
																		</a>
                                                                        <a class="dropdown-item" data-bs-toggle="modal" href="#Settlement">
																			<i data-feather='tool' class="me-50"></i>
																			<span>Settlement</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>2</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Kundan Kumar</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Term</td> 
															<td>20 Lkh</td>
															<td>Half-Yearly</td>
                                                            <td>12/12</td>
                                                            <td>-</td>
                                                            <td>-</td>
                                                            <td><span class="badge rounded-pill badge-light-success badgeborder-radius">Settled</span></td>  
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>3</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Rahul Upadhyay</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Vehicle</td> 
															<td>20 Lkh</td>
															<td>Monthly</td>
                                                            <td>24/1</td>
                                                            <td>800000</td>
                                                            <td>2</td>
															<td><span class="badge rounded-pill badge-light-warning badgeborder-radius">In Progress</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
                                                                        <a class="dropdown-item" data-bs-toggle="modal" href="#Payment">
																			<i data-feather='dollar-sign' class="me-50"></i>
																			<span>Release Payment</span>
																		</a>
                                                                        <a class="dropdown-item" data-bs-toggle="modal" href="#Collection">
																			<i data-feather='calendar' class="me-50"></i>
																			<span>Collection</span>
																		</a>
                                                                        <a class="dropdown-item" data-bs-toggle="modal" href="#Settlement">
																			<i data-feather='tool' class="me-50"></i>
																			<span>Settlement</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>4</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Ashish Kumar</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Home</td> 
															<td>20 Lkh</td>
															<td>Quarterly</td>
                                                            <td>12/5</td>
                                                            <td>1000000</td>
                                                            <td>1</td>
															<td><span class="badge rounded-pill badge-light-warning badgeborder-radius">In Progress</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
                                                                        <a class="dropdown-item" data-bs-toggle="modal" href="#Payment">
																			<i data-feather='dollar-sign' class="me-50"></i>
																			<span>Release Payment</span>
																		</a> 
                                                                        <a class="dropdown-item" data-bs-toggle="modal" href="#Collection">
																			<i data-feather='calendar' class="me-50"></i>
																			<span>Collection</span>
																		</a> 
                                                                        <a class="dropdown-item" data-bs-toggle="modal" href="#Settlement">
																			<i data-feather='tool' class="me-50"></i>
																			<span>Settlement</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
															</td>
														</tr>
														<tr>
															<td>5</td>
															<td>29-07-2024</td>
															<td class="fw-bolder text-dark">Inder Singh</td>
															<td>nishu@gmail.com</td>
															<td>9876787656</td>
															<td>Term</td> 
															<td>20 Lkh</td>
															<td>Yearly</td>
                                                            <td>5/5</td>
                                                            <td>-</td>
                                                            <td>-</td>
															<td><span class="badge rounded-pill badge-light-success badgeborder-radius">Settled</span></td> 
															<td class="tableactionnew">
																<div class="dropdown">
																	<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																		<i data-feather="more-vertical"></i>
																	</button>
																	<div class="dropdown-menu dropdown-menu-end">
																		<a class="dropdown-item" href="view-loan.html">
																			<i data-feather="check-circle" class="me-50"></i>
																			<span>View Detail</span>
																		</a>
																		<a class="dropdown-item" data-bs-toggle="modal" href="#viewdocs">
																			<i data-feather='folder' class="me-50"></i>
																			<span>Documents</span>
																		</a>
																	</div>
																</div>
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
					
					 
                     
                </section>
                <!-- ChartJS section end -->

            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="modal fade" id="viewdocs" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Download Documents</h1>
					<p class="text-center text-dark fw-bold">Nishu Garg | 20 Lkh | 29-07-2024</p>

					<div class="row mt-2"> 
						
						   <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail loanapplicationlist"> 
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
				
				<div class="modal-footer justify-content-center">  
						<button type="reset" class="btn btn-outline-secondary me-1">Cancel</button> 
					<button type="reset" class="btn btn-primary">Download All</button>
				</div>
			</div>
		</div>
	</div>
    
    <div class="modal fade" id="viewasses" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">View Assessment</h1>
					<p class="text-center text-dark fw-bold">Nishu Garg | 20 Lkh | 29-07-2024</p>

					<div class="row mt-2"> 
						
						   <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail loanapplicationlist"> 
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
                                                    <td><a href="#"><i data-feather='download'></i></a></td>
                                                </tr>
                                                
                                                <tr>
                                                     <td>3</td>   
                                                    <td>Disbursement first flour completed</td>
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
				
				<div class="modal-footer justify-content-center">  
						<button type="reset" class="btn btn-outline-secondary me-1">Cancel</button> 
					<button type="reset" class="btn btn-primary">Download All</button>
				</div>
			</div>
		</div>
	</div>
    
    
    <div class="modal fade" id="new-application" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">New Application</h1>
					<p class="text-center voucehrinvocetxt mt-0">Select the new application type.</p>

					<div class="row my-3"> 
						
						   <div class="col-md-12"> 
                                    <div class="row custom-options-checkable g-1">
                                        <div class="col-md-4">
                                            <input class="custom-option-item-check" type="radio" name="loantypeselect" id="homeloan" onclick="document.location.href='home-loan.html'" />
                                            <label class="custom-option-item text-center p-1" for="homeloan">
                                                <i data-feather="home" class="font-large-1 mb-75"></i>
                                                <span class="custom-option-item-title h4 d-block">Home Loan</span>
                                                <small>(For Govt. Employees<br/>only)</small>
                                            </label>
                                        </div>

                                        <div class="col-md-4">
                                            <input class="custom-option-item-check" type="radio" name="loantypeselect" id="vehicleloan" value="" onclick="document.location.href='vehicle-loan.html'" />
                                            <label class="custom-option-item text-center text-center p-1" for="vehicleloan">
                                                <i data-feather="truck" class="font-large-1 mb-75"></i> 
                                                <span class="custom-option-item-title h4 d-block">Vehicle Loan</span>
                                                <small>(For the acquisition of transport vehicle only)</small>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <input class="custom-option-item-check" type="radio" name="loantypeselect" id="termloan" value="" onclick="document.location.href='term-loan.html'" />
                                            <label class="custom-option-item text-center p-1" for="termloan">
                                                <i data-feather="file-text" class="font-large-1 mb-75"></i>
                                                <span class="custom-option-item-title h4 d-block">Term Loan</span>
                                                <small>(For Govt. Employees<br/>only)</small>
                                            </label>
                                        </div>
                                    </div>
                     
                            </div>
						  
					</div>
				</div>
				
				<div class="modal-footer justify-content-center">  
						<button type="reset" class="btn btn-outline-secondary me-1">Cancel</button> 
					<button type="reset" class="btn btn-primary">Proceed</button>
				</div>
			</div>
		</div>
	</div>
    
    
    <div class="modal fade" id="viewassesgive" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Assessment by Field Officer</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | 29-07-2024</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body pb-2"> 

					<div class="row mt-1"> 
						
						   <div class="col-md-12"> 
                               
                                     <div class="row"> 
                                         <div class="col-md-6">
                                             <div class="mb-1">
                                               <label class="form-label">Loan Amount <span class="text-danger">*</span></label>
                                               <input type="number" disabled value="100000" class="form-control" />
                                             </div> 
                                         </div>
                                         
                                         <div class="col-md-6">
                                             <div class="mb-1">
                                               <label class="form-label">Recommended Loan Amt. <span class="text-danger">*</span></label>
                                               <input type="number" class="form-control" />
                                             </div> 
                                         </div>
                                         
                                         <div class="col-md-6">
                                             <div class="mb-1">
                                               <label class="form-label">CIBIL Score <span class="text-danger">*</span></label>
                                               <input type="number" class="form-control" />
                                             </div> 
                                         </div>
                                         
                                         <div class="col-md-6">
                                             <div class="mb-1">
                                               <label class="form-label">Upload Document</label>
                                               <input type="file" class="form-control" />
                                             </div> 
                                         </div>
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
    
    <div class="modal fade" id="Disbursement" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" style="max-width: 600px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Disbursal Schedule by Field Officer</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | 29-07-2024</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body pb-2"> 

					<div class="row mt-1"> 
						
						   <div class="col-md-12"> 
                               
                               <div class="row">
                                   <div class="col-md-6">
                                       <div class="mb-1">
                                           <label class="form-label">Loan Amount</label>
                                           <input type="input" class="form-control" disabled value="100000" />
                                       </div>
                                   </div>
                                   <div class="col-md-6">
                                       <div class="mb-1">
                                           <label class="form-label">Disbursal Amount Type <span class="text-danger">*</span></label>
                                           <select class="form-select mw-100">
                                                           <option>Select</option> 
                                                           <option>%age</option> 
                                                           <option>Fixed Amount</option> 
                                                       </select>
                                       </div>
                                   </div>
                               </div>
                              <div class="table-responsive"> 
                                    <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border"> 
                                        <thead>
                                             <tr>
                                                <th>#</th>
                                                <th>Disbursal Milestone <span class="text-danger">*</span></th> 
                                                <th>Disbursal Amount <span class="text-danger">*</span></th>
                                                <th>Date <span class="text-danger">*</span></th>
                                                <th>Action</th>
                                              </tr>
                                            </thead>
                                            <tbody>
                                                 <tr>
                                                    <td>#</td>
                                                    <td><input type="text" class="form-control mw-100"></td>
                                                    <td><input type="number" class="form-control mw-100"></td> 
                                                    <td><input type="date" class="form-control mw-100"></td> 
                                                    <td><a href="#" class="text-primary"><i data-feather="plus-square" class="me-50"></i></a></td>
                                                  </tr>

                                                <tr>
                                                    <td>1</td>
                                                    <td>First Construction</td> 
                                                    <td>500000</td>
                                                    <td>30-07-2024</td>
                                                    <td><a href="#" class="text-danger"><i data-feather="trash-2" class="me-50"></i></a></td>
                                                  </tr>


                                           </tbody>


                                    </table>
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
    
    <div class="modal fade" id="Recovery" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" style="max-width: 600px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Recovery Schedule by Field Officer</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | 29-07-2024</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body pb-2"> 

					<div class="row mt-1"> 
						
						   <div class="col-md-12"> 
                               
                               <div class="row">
                                   <div class="col-md-4">
                                       <div class="mb-1">
                                           <label class="form-label">Loan Amount</label>
                                           <input type="input" class="form-control" disabled value="100000" />
                                       </div>
                                   </div> 
                                   <div class="col-md-4">
                                       <div class="mb-1">
                                           <label class="form-label">Interest Rate <span class="text-danger">*</span></label>
                                           <select class="form-select mw-100" disabled>
                                               <option>Select</option> 
                                               <option>8%</option>
                                               <option>9%</option>
                                               <option selected>10%</option>
                                               <option>11%</option>
                                               <option>12%</option>
                                           </select>
                                       </div>
                                   </div>
                                   <div class="col-md-4">
                                       <div class="mb-1">
                                           <label class="form-label">Santioned Int. Rate <span class="text-danger">*</span></label>
                                           <input type="input" class="form-control" value="11" />
                                       </div>
                                   </div>
                                   <div class="col-md-4">
                                       <div class="mb-1">
                                           <label class="form-label">Repayment Type <span class="text-danger">*</span></label>
                                           <select class="form-select mw-100">
                                               <option>Select</option> 
                                               <option selected>Yearly</option> 
                                               <option>Half-Yearly</option> 
                                               <option>Monthly</option> 
                                               <option>Quarterly</option> 
                                           </select>
                                       </div>
                                   </div>
                                   <div class="col-md-4">
                                       <div class="mb-1">
                                           <label class="form-label">Repayment Period<span class="text-danger">*</span></label>
                                           <input type="number" value="4" class="form-control mw-100" />
                                       </div>
                                   </div>
                               </div>
                              <div class="table-responsive"> 
                                    <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border"> 
                                        <thead>
                                             <tr>
                                                <th>#</th>
                                                <th width="100px">Period</th>
                                                <th>Principal Amt.</th>
                                                <th>Interest Amt.</th>
                                                <th>Total <span class="text-danger">*</span></th>
                                              </tr>
                                            </thead>
                                            <tbody>
                                                 <tr>
                                                    <td>1</td>
                                                    <td>1st</td>
                                                    <td><input type="number" value="25000" class="form-control mw-100"></td> 
                                                    <td><input type="number" value="10000" disabled class="form-control mw-100"></td>
                                                    <td><input type="number" value="35000" disabled class="form-control mw-100"></td>
                                                  </tr>
                                                
                                                <tr>
                                                    <td>2</td>
                                                    <td>2nd</td>
                                                    <td><input type="number" value="25000" class="form-control mw-100"></td> 
                                                    <td><input type="number" value="7500" disabled class="form-control mw-100"></td>
                                                    <td><input type="number" value="32500" disabled class="form-control mw-100"></td>
                                                  </tr>
                                                
                                                <tr>
                                                    <td>3</td>
                                                    <td>3rd</td>
                                                    <td><input type="number" value="25000" class="form-control mw-100"></td> 
                                                    <td><input type="number" value="5000" disabled class="form-control mw-100"></td>
                                                    <td><input type="number" value="30000" disabled class="form-control mw-100"></td>
                                                  </tr>
                                                
                                                <tr>
                                                    <td>4</td>
                                                    <td>4th</td>
                                                    <td><input type="number" value="25000" class="form-control mw-100"></td> 
                                                    <td><input type="number" value="2500" disabled class="form-control mw-100"></td>
                                                    <td><input type="number" value="27500" disabled class="form-control mw-100"></td>
                                                  </tr> 
                                           </tbody>


                                    </table>
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
    
    
    <div class="modal fade" id="Collection" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" style="max-width: 600px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Collection by Customer</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | 29-07-2024</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body pb-2"> 

					<div class="row mt-1"> 
						
						   <div class="col-md-12"> 
                               
                               <div class="row">
                                   <div class="col-md-6">
                                       <div class="mb-1">
                                           <label class="form-label">Loan Amount</label>
                                           <input type="input" class="form-control" disabled value="100000" />
                                       </div>
                                   </div>
                                   <div class="col-md-6">
                                       <div class="mb-1">
                                           <label class="form-label">Repayment Type <span class="text-danger">*</span></label>
                                           <select class="form-select mw-100" disabled>
                                               <option>Select</option> 
                                               <option selected>Yearly - 4</option>
                                           </select>
                                       </div>
                                   </div>
                                   <div class="col-md-6">
                                       <div class="mb-1">
                                           <label class="form-label">No. of Repayment <span class="text-danger">*</span></label>
                                           <select class="form-select mw-100">
                                               <option>Select</option> 
                                               <option selected>1</option>
                                               <option>2</option>
                                               <option>3</option>
                                               <option>4</option>
                                           </select>
                                       </div>
                                   </div>
                                   
                                   <div class="col-md-6">
                                       <div class="mb-1">
                                           <label class="form-label">Repayment Amount <span class="text-danger">*</span></label>
                                           <input type="number" class="form-control mw-100" />
                                       </div>
                                   </div>
                                   
                                   <div class="col-md-12">
                                       <div class="mb-1">
                                           <label class="form-label">Remarks</label>
                                           <textarea class="form-control"></textarea>
                                         </div>
                                   </div>
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
    
      
    
    
    <div class="modal fade" id="approved" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Approve Home Loan Application</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | 29-07-2024</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body pb-2"> 

					<div class="row mt-1"> 
						
						   <div class="col-md-12">
                                    
                                    <div class="row">
                                       <div class="col-md-6">
                                         <div class="mb-1">
                                           <label class="form-label">Loan Amount <span class="text-danger">*</span></label>
                                           <input type="number" disabled value="100000" class="form-control" />
                                         </div> 
                                     </div>

                                     <div class="col-md-6">
                                         <div class="mb-1">
                                           <label class="form-label">Recommended Loan Amt. <span class="text-danger">*</span></label>
                                           <input type="number" class="form-control" value="900000" />
                                         </div> 
                                     </div>
                               
                                       </div>
                               
                                     <div class="mb-1">
                                       <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                       <textarea class="form-control"></textarea>
                                     </div>
                               
                                     <div class="mb-1">
                                       <label class="form-label">Upload Document</label>
                                       <input type="file" class="form-control" />
                                     </div>
                               
                                     <div class="mb-1">
                                       <label class="form-label">On Behalf of <span class="text-danger">*</span></label>
                                       <select class="form-select select2" multiple>
                                           <option>Select</option>  
                                           <option>Nishu Garg</option>  
                                           <option>Mahesh Bhatt</option>  
                                           <option>Inder Singh</option>  
                                           <option>Shivangi</option>  
                                       </select>
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
    
    <div class="modal fade" id="reject" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Reject Home Loan Application</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | 29-07-2024</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body pb-2"> 

					<div class="row mt-2"> 
						
						   <div class="col-md-12"> 
                                     <div class="row">
                                       <div class="col-md-6">
                                         <div class="mb-1">
                                           <label class="form-label">Loan Amount <span class="text-danger">*</span></label>
                                           <input type="number" disabled value="100000" class="form-control" />
                                         </div> 
                                     </div>

                                     <div class="col-md-6">
                                         <div class="mb-1">
                                           <label class="form-label">Recommended Loan Amt. <span class="text-danger">*</span></label>
                                           <input type="number" class="form-control" value="900000" />
                                         </div> 
                                     </div>
                               
                                       </div>
                               
                                     <div class="mb-1">
                                       <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                       <textarea class="form-control"></textarea>
                                     </div>
                               
                                     <div class="mb-1">
                                       <label class="form-label">Upload Document</label>
                                       <input type="file" class="form-control" />
                                     </div>
                               
                                     <div class="mb-1">
                                       <label class="form-label">On Behalf of <span class="text-danger">*</span></label>
                                       <select class="form-select select2" multiple>
                                           <option>Select</option>  
                                           <option>Nishu Garg</option>  
                                           <option>Mahesh Bhatt</option>  
                                           <option>Inder Singh</option>  
                                           <option>Shivangi</option>  
                                       </select>
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
						  <input type="text" id="fp-range" class="form-control flatpickr-range" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
					</div>
					
					<div class="mb-1">
						<label class="form-label">Loan Type</label>
						<select class="form-select">
							<option>Select</option>
                            <option>Home Loan</option>
							<option>Vehicle Loan</option>
							<option>Term Loan</option>
						</select>
					</div> 
                    
                    <div class="mb-1">
						<label class="form-label">Customer Name</label>
						<select class="form-select">
							<option>Select</option> 
						</select>
					</div> 
                    
                    <div class="mb-1">
						<label class="form-label">Status</label>
						<select class="form-select">
							<option>Select</option>
							<option>In Progress</option>
							<option>Save as Draft</option>
							<option>Pending on MD</option>
							<option>Pending on BOD</option>
							<option>Loan Sanctioned</option>
						</select>
					</div> 
					 
				</div>
				<div class="modal-footer justify-content-start">
					<button type="button" class="btn btn-primary data-submit mr-1">Apply</button>
					<button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
				</div>
			</form>
		</div>
	</div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(window).on("load", function () {
    if (feather) {
        feather.replace({
            width: 14,
            height: 14,
        });
    }
});
$(function () {
    var dt_basic_table = $(".datatables-basic"),
        dt_date_table = $(".dt-date"),
        dt_complex_header_table = $(".dt-complex-header"),
        dt_row_grouping_table = $(".dt-row-grouping"),
        dt_multilingual_table = $(".dt-multilingual"),
        assetPath = "/app-assets/";

    if ($("body").attr("data-framework") === "laravel") {
        assetPath = $("body").attr("data-asset-path");
    }

    // DataTable with buttons
    // --------------------------------------------------------------------

    if (dt_basic_table.length) {
        var dt_basic = dt_basic_table.DataTable({
            order: [[0, "asc"]],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 7,
            lengthMenu: [7, 10, 25, 50, 75, 100],
            buttons: [
                {
                    extend: "collection",
                    className: "btn btn-outline-secondary dropdown-toggle",
                    text:
                        feather.icons["share"].toSvg({
                            class: "font-small-4 mr-50",
                        }) + "Export",
                    buttons: [
                        {
                            extend: "print",
                            text:
                                feather.icons["printer"].toSvg({
                                    class: "font-small-4 mr-50",
                                }) + "Print",
                            className: "dropdown-item",
                            exportOptions: { columns: [3, 4, 5, 6, 7] },
                        },
                        {
                            extend: "csv",
                            text:
                                feather.icons["file-text"].toSvg({
                                    class: "font-small-4 mr-50",
                                }) + "Csv",
                            className: "dropdown-item",
                            exportOptions: { columns: [3, 4, 5, 6, 7] },
                        },
                        {
                            extend: "excel",
                            text:
                                feather.icons["file"].toSvg({
                                    class: "font-small-4 mr-50",
                                }) + "Excel",
                            className: "dropdown-item",
                            exportOptions: { columns: [3, 4, 5, 6, 7] },
                        },
                        {
                            extend: "pdf",
                            text:
                                feather.icons["clipboard"].toSvg({
                                    class: "font-small-4 mr-50",
                                }) + "Pdf",
                            className: "dropdown-item",
                            exportOptions: { columns: [3, 4, 5, 6, 7] },
                        },
                        {
                            extend: "copy",
                            text:
                                feather.icons["copy"].toSvg({
                                    class: "font-small-4 mr-50",
                                }) + "Copy",
                            className: "dropdown-item",
                            exportOptions: { columns: [3, 4, 5, 6, 7] },
                        },
                    ],
                    init: function (api, node, config) {
                        $(node).removeClass("btn-secondary");
                        $(node).parent().removeClass("btn-group");
                        setTimeout(function () {
                            $(node)
                                .closest(".dt-buttons")
                                .removeClass("btn-group")
                                .addClass("d-inline-flex");
                        }, 50);
                    },
                },
            ],

            language: {
                paginate: {
                    // remove previous & next text from pagination
                    previous: "&nbsp;",
                    next: "&nbsp;",
                },
            },
        });
        $("div.head-label").html('<h6 class="mb-0">Event List</h6>');
    }

    // Flat Date picker
    if (dt_date_table.length) {
        dt_date_table.flatpickr({
            monthSelectorType: "static",
            dateFormat: "m/d/Y",
        });
    }

    // Add New record
    // ? Remove/Update this code as per your requirements ?
    var count = 101;

    $(".apply-filter").on("click", function () {
        console.log("clicked to data submit");

        // Capture filter values
        var dateRange = $("#fp-range").val(),
            bookType = $("#filter-book-type").val(),
            bookName = $("#filter-book-name").val(),
            status = $("#filter-status").val();

        // Split date range into start and end dates
        var dates = dateRange.split(" to "),
            startDate = dates[0] ? dates[0] : '',
            endDate = dates[1] ? dates[1] : '';

        // Clear any existing filters
        dt_basic.search('').columns().search('');

        // Apply filters
        dt_basic.column(1).search(bookType ? bookType : '', true, false); // Adjust index if needed
        dt_basic.column(3).search(bookName ? bookName : '', true, false); // Adjust index if needed
        dt_basic.column(6).search(status ? status : '', true, false); // Adjust index if needed

        // Custom date range filter
        // $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        //     var createdAt = data[7]; // Assuming the `created_at` date is in the 8th column
        //     if (startDate && endDate) {
        //         if (createdAt >= startDate && createdAt <= endDate) {
        //             return true;
        //         }
        //         return false;
        //     }
        //     return true;
        // });

        // Redraw the table
        dt_basic.draw();

        // Remove the custom filter function to avoid stacking filters
        $.fn.dataTable.ext.search.pop();

        // Hide the modal
        $(".modal").modal("hide");
    })
    // Delete Record
    $(".datatables-basic tbody").on("click", ".delete-record", function () {
        dt_basic.row($(this).parents("tr")).remove().draw();
    });
});
</script>
   

    @endsection