@extends('layouts.app')
@section('content')

    <!-- BEGIN: Header-->
    <
     
    <!-- END: Header-->


    <!-- BEGIN: Main Menu-->
    <div class="main-menu menu-fixed menu-light menu-accordion menu-shadow erpnewsidemenu" data-scroll-to-active="true">
        
        <div class="shadow-bottom"></div>
        <div class="main-menu-content newmodulleftmenu">
            <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
                <li class="active nav-item"><a class="d-flex align-items-center" href="index.html"><i data-feather="file-text"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Finance</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="voucher.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Voucher</span></a>
                        </li>
                        
                        <li><a class="d-flex align-items-center" href="group.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Group Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="cost-center.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Cost Center Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="account.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Account Master</span></a>
                        </li> 
						<li><a class="d-flex align-items-center" href="close-fy.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Close F.Y</span></a>
                        </li> 
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span class="menu-item text-truncate">Reports</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="trail-balance.html"><span class="menu-item text-truncate">Trial Balance</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="ledger.html"><span class="menu-item text-truncate">Ledger</span></a></li>
                                <li>
                                    <a class="d-flex align-items-center" href="profit-loss.html"><span class="menu-item text-truncate">Profit & Loss Account</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="balance-sheet.html"><span class="menu-item text-truncate">Balance Sheet</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="creditors.html"><span class="menu-item text-truncate">Creditors</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="creditors.html"><span class="menu-item text-truncate">Debtors</span></a>
                                </li>
								<li>
                                    <a class="d-flex align-items-center" href="tds-reprot.html"><span class="menu-item text-truncate">TDS Report</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="tcs-report.html"><span class="menu-item text-truncate">TCS Report</span></a>
                                </li>
								<li>
                                    <a class="d-flex align-items-center" href="cashflow-statement.html"><span class="menu-item text-truncate">Cashflow Statement</span></a>
                                </li>
                                
                            </ul>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span class="menu-item text-truncate">GST Reports</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="gst.html"><span class="menu-item text-truncate">GST 1</span></a>
                                </li> 
                                  
                            </ul>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span class="menu-item text-truncate">Fixed Assets</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="setup.html"><span class="menu-item text-truncate">Setup</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="registration.html"><span class="menu-item text-truncate">Registration</span></a></li>
                                <li>
                                    <a class="d-flex align-items-center" href="depreciation.html"><span class="menu-item text-truncate">Depreciation</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="split-merger.html"><span class="menu-item text-truncate">Split/Merger</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="revaluation.html"><span class="menu-item text-truncate">Revaluation</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="issue-asset.html"><span class="menu-item text-truncate">Issue/Transfer</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="insurance.html"><span class="menu-item text-truncate">Insurance</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="maint-cond.html"><span class="menu-item text-truncate">Maint. & Condition</span></a>
                                </li>
                            </ul>
                        </li> 
                    </ul>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="budget.html"><i data-feather="sliders"></i><span class="menu-title text-truncate">Budget</span></a>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="po.html"><i data-feather="box"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Procurement</span></a>
                </li>
				<li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i data-feather="truck"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Replenishment</span></a>
                    <ul class="menu-content"> 
                        <li><a class="d-flex align-items-center" href="dynamic-replenishment.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Replenishment</span></a></li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span class="menu-item text-truncate">Master</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="config.html"><span class="menu-item text-truncate">Config</span></a>
                                </li>
								<li>
                                    <a class="d-flex align-items-center" href="rule-master.html"><span class="menu-item text-truncate">Rule Master</span></a>
                                </li>
								<li>
                                    <a class="d-flex align-items-center" href="mappping.html"><span class="menu-item text-truncate">Source Supply</span></a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li><li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i data-feather="truck"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Transport</span></a>
                    <ul class="menu-content"> 
                        <li><a class="d-flex align-items-center" href="transport.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Transporter Master</span></a></li>
                        <li><a class="d-flex align-items-center" href="trip-request.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Trip Request</span></a></li>
                        <li><a class="d-flex align-items-center" href="transporter.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Transporter</span></a></li>
                    </ul>
                </li>  
                <li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i data-feather="inbox"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Inventory</span></a>
                    <ul class="menu-content"> 
                        <li><a class="d-flex align-items-center" href="mrn.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Material Receipt</span></a>
                        </li>
                        </ul>
                </li> 
                <li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i data-feather="archive"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Sales</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="sales.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Sales Order</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="invoice.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Invoice</span></a>
                        </li>
                    </ul>
                </li> 
                <li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i data-feather="dollar-sign"></i><span class="menu-title text-truncate">Loan</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="../loan/dashboard.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Dashboard</span></a>
                        </li> 
                        <li><a class="d-flex align-items-center" href="../loan/index.html"><i data-feather="circle"></i><span class="menu-item text-truncate">My Application</span></a>
                        </li> 
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span class="menu-item text-truncate">New Application</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="../loan/home-loan.html"><span class="menu-item text-truncate"><i data-feather="home"></i> Home Loan</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="../loan/vehicle-loan.html"><span class="menu-item text-truncate"><i data-feather="truck"></i> Vehicle Loan</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="../loan/term-loan.html"><span class="menu-item text-truncate"><i data-feather="file-text"></i> Term Loan</span></a>
                                </li>
                            </ul>
                        </li> 
						<li><a class="d-flex align-items-center" href="../loan/disbursement.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Disbursement</span></a>
                        </li> 
                        <li><a class="d-flex align-items-center" href="../loan/recovery.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Recovery</span></a>
                        </li> 
                        <li><a class="d-flex align-items-center" href="../loan/settlement.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Settlement</span></a>
                        </li> 
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span class="menu-item text-truncate">Masters</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="../loan/interest-rate.html"><span class="menu-item text-truncate">Interest Rate</span></a>
                                </li>
                            </ul>
                        </li> 
                    </ul>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i data-feather="map"></i><span class="menu-title text-truncate">Land</span></a>
                    <ul class="menu-content">
						<li><a class="d-flex align-items-center" href="../land/my-land.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Land Parcel</span></a>
                        </li> 
                        <li><a class="d-flex align-items-center" href="../land/my-land-plot.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Land Plot</span></a>
                        </li> 
                        <li><a class="d-flex align-items-center" href="../land/on-lease.html"><i data-feather="circle"></i><span class="menu-item text-truncate">On Lease</span></a>
                        </li> 
                        <li><a class="d-flex align-items-center" href="../land/recovery.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Recovery</span></a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="../legal/legal.html"><i data-feather="alert-triangle"></i><span class="menu-title text-truncate">Legal</span></a>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="document-manage.html"><i data-feather="folder"></i><span class="menu-title text-truncate">Document Drive</span></a>
                    <ul class="menu-content">
						<li><a class="d-flex align-items-center" href="document-manage.html"><i data-feather="circle"></i><span class="menu-item text-truncate">My Drive</span></a>
                        </li> 
                        <li><a class="d-flex align-items-center" href="document-manage.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Shared with me</span></a>
                        </li> 
                        <li><a class="d-flex align-items-center" href="document-manage.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Shared Drive</span></a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="../survey/survey.html"><i data-feather="bar-chart"></i><span class="menu-title text-truncate">Survey</span></a>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="../crm/dashboard.html"><i data-feather="database"></i><span class="menu-title text-truncate">CRM</span></a>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i data-feather="bar-chart-2"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Reports</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="../reports/po-report.html"><i data-feather="circle"></i><span class="menu-item text-truncate">PO</span></a>
                        </li>
						<li><a class="d-flex align-items-center" href="../reports/sales-report.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Sales</span></a>
                        </li>
                    </ul>
                </li> 
                <li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i data-feather="grid"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Master Management</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="store.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Store</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span class="menu-item text-truncate">Account Setup</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="stock-account-setup.html"><span class="menu-item text-truncate">Stock Account</span></a>
                                </li>
                            </ul>
                        </li>
                        
                        <li><a class="d-flex align-items-center" href="exchange-rate.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Exchange Rate</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="book-type.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Book Type</span></a>
                        </li>
						<li><a class="d-flex align-items-center" href="book.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Series</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="station.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Station</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="attribute.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Attribute</span></a>
                        </li>
						<li><a class="d-flex align-items-center" href="item-master.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Item Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="price.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Price Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="bom.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Bill of Material</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="customer.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Customer Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="vendor.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Vendor Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="tax.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Tax Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="hsn.html"><i data-feather="circle"></i><span class="menu-item text-truncate">HSN/SAC Master</span></a>
                        </li></ul>
                </li> 
            </ul>
        </div>
		
    </div>
    <!-- END: Main Menu-->

    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Split Asset</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>  
                                    <li class="breadcrumb-item active">Asset List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button> 
						<a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{ route('splite.add') }}"><i data-feather="plus-circle"></i> Add New</a> 
                    </div>
                </div>
            </div>
            <div class="content-body">
                 
                
				
				<section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
								
								   
                                <div class="table-responsive">
									<table class="datatables-basic table myrequesttablecbox "> 
                                            <thead>
                                             <tr>
												<th>#</th>
												<th>Series</th>
												<th>Doc No.</th>
												<th>Asset Name</th>
												<th>Asset Code</th>
												<th>Ledger Name</th>
												<th>Split Date</th> 
												<th>Qty</th>
												<th>Cap. Date</th>
												<th>Status</th>
												<th>Action</th>
											  </tr>
											</thead>
											<tbody>
												 <tr>
													<td>1</td>
													<td class="fw-bolder text-dark">Laptop</td>
													<td>L001</td>
													<td>Account</td>
													<td>9-11-2024</td>
													<td>2100</td>
													<td>20</td>
													<td>20</td>
													<td>19-11-2024</td>
													<td><span class="badge rounded-pill badge-light-success badgeborder-radius">Active</span></td>
													<td class="tableactionnew">
														<div class="dropdown">
															<button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
																<i data-feather="more-vertical"></i>
															</button>
															<div class="dropdown-menu dropdown-menu-end">
																<a class="dropdown-item" href="#">
																	<i data-feather="edit" class="me-50"></i>
																	<span>View Detail</span>
																</a>
																<a class="dropdown-item" href="#">
																	<i data-feather="edit-3" class="me-50"></i>
																	<span>Edit</span>
																</a>
																<a class="dropdown-item" href="#">
																	<i data-feather="trash-2" class="me-50"></i>
																	<span>Delete</span>
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
                    <!-- Modal to add new record -->
                    <div class="modal modal-slide-in fade" id="modals-slide-in">
                        <div class="modal-dialog sidebar-sm">
                            <form class="add-new-record modal-content pt-0">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                                <div class="modal-header mb-1">
                                    <h5 class="modal-title" id="exampleModalLabel">New Record</h5>
                                </div>
                                <div class="modal-body flex-grow-1">
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-fullname">Full Name</label>
                                        <input type="text" class="form-control dt-full-name" id="basic-icon-default-fullname" placeholder="John Doe" aria-label="John Doe" />
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-post">Post</label>
                                        <input type="text" id="basic-icon-default-post" class="form-control dt-post" placeholder="Web Developer" aria-label="Web Developer" />
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-email">Email</label>
                                        <input type="text" id="basic-icon-default-email" class="form-control dt-email" placeholder="john.doe@example.com" aria-label="john.doe@example.com" />
                                        <small class="form-text"> You can use letters, numbers & periods </small>
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-date">Joining Date</label>
                                        <input type="text" class="form-control dt-date" id="basic-icon-default-date" placeholder="MM/DD/YYYY" aria-label="MM/DD/YYYY" />
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label" for="basic-icon-default-salary">Salary</label>
                                        <input type="text" id="basic-icon-default-salary" class="form-control dt-salary" placeholder="$12000" aria-label="$12000" />
                                    </div>
                                    <button type="button" class="btn btn-primary data-submit me-1">Submit</button>
                                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
                 

            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

    <!-- BEGIN: Footer-->
    <!-- END: Footer-->
	
	 
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
		<div class="modal-dialog sidebar-sm">
			<form class="add-new-record modal-content pt-0"> 
				<div class="modal-header mb-1">
					<h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
				</div>
				<div class="modal-body flex-grow-1">
					<div class="mb-1">
						  <label class="form-label" for="fp-range">Select Date</label>
<!--                        <input type="text" id="fp-default" class="form-control flatpickr-basic" placeholder="YYYY-MM-DD" />-->
						  <input type="text" id="fp-range" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
					</div>
					
					<div class="mb-1">
						<label class="form-label">Asset Code</label>
						<select class="form-select">
							<option>Select</option>
						</select>
					</div> 
                    
                    <div class="mb-1">
						<label class="form-label">Ledger Name</label>
						<select class="form-select">
							<option>Select</option> 
						</select>
					</div> 
                    
                    <div class="mb-1">
						<label class="form-label">Status</label>
						<select class="form-select">
							<option>Select</option>
							<option>Active</option>
							<option>Inactive</option>
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
@endsection
@section('scripts')
    <!-- BEGIN: Vendor JS-->
    
    <!-- BEGIN: Vendor JS-->
     <script src="../../../app-assets/vendors/js/vendors.min.js"></script>
    <!-- BEGIN Vendor JS-->

    <!-- BEGIN: Page Vendor JS-->
    <script src="../../../app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
    <script src="../../../app-assets/vendors/js/tables/datatable/dataTables.bootstrap5.min.js"></script>
    <script src="../../../app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
    <script src="../../../app-assets/vendors/js/tables/datatable/responsive.bootstrap5.min.js"></script>
    <script src="../../../app-assets/vendors/js/tables/datatable/datatables.checkboxes.min.js"></script>
    <script src="../../../app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
    <script src="../../../app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
    <script src="../../../app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
    <script src="../../../app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
    <script src="../../../app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
    <script src="../../../app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>
    <script src="../../../app-assets/vendors/js/tables/datatable/dataTables.rowGroup.min.js"></script>
    <script src="../../../app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
	<script src="../../../app-assets/vendors/js/forms/select/select2.full.min.js"></script>
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src="../../../app-assets/js/core/app-menu.js"></script>
    <script src="../../../app-assets/js/core/app.js"></script>
    <!-- END: Theme JS-->

    <!-- BEGIN: Page JS--> 
    <script src="../../../app-assets/js/scripts/forms/pickers/form-pickers.js"></script>
	<script src="../../../app-assets/js/scripts/forms/form-select2.js"></script>
    <!-- END: Page JS-->

    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })
		$(function () { 

  var dt_basic_table = $('.datatables-basic'),
    dt_date_table = $('.dt-date'),
    dt_complex_header_table = $('.dt-complex-header'),
    dt_row_grouping_table = $('.dt-row-grouping'),
    dt_multilingual_table = $('.dt-multilingual'),
    assetPath = '../../../app-assets/';

  if ($('body').attr('data-framework') === 'laravel') {
    assetPath = $('body').attr('data-asset-path');
  }

  // DataTable with buttons
  // --------------------------------------------------------------------

  if (dt_basic_table.length) {
    var dt_basic = dt_basic_table.DataTable({
      
      order: [[0, 'asc']],
      dom: 
        '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 7,
      lengthMenu: [7, 10, 25, 50, 75, 100],
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-outline-secondary dropdown-toggle',
          text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + 'Export',
          buttons: [
            {
              extend: 'print',
              text: feather.icons['printer'].toSvg({ class: 'font-small-4 mr-50' }) + 'Print',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'csv',
              text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'excel',
              text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'pdf',
              text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + 'Pdf',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            },
            {
              extend: 'copy',
              text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + 'Copy',
              className: 'dropdown-item',
              exportOptions: { columns: [3, 4, 5, 6, 7] }
            }
          ],
          init: function (api, node, config) {
            $(node).removeClass('btn-secondary');
            $(node).parent().removeClass('btn-group');
            setTimeout(function () {
              $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
            }, 50);
          }
        },
         
      ],
      
      language: {
        paginate: {
          // remove previous & next text from pagination
          previous: '&nbsp;',
          next: '&nbsp;'
        }
      }
    });
    $('div.head-label').html('<h6 class="mb-0">Event List</h6>');
  }

  // Flat Date picker
  if (dt_date_table.length) {
    dt_date_table.flatpickr({
      monthSelectorType: 'static',
      dateFormat: 'm/d/Y'
    });
  }

  // Add New record
  // ? Remove/Update this code as per your requirements ?
  var count = 101;
  $('.data-submit').on('click', function () {
    var $new_name = $('.add-new-record .dt-full-name').val(),
      $new_post = $('.add-new-record .dt-post').val(),
      $new_email = $('.add-new-record .dt-email').val(),
      $new_date = $('.add-new-record .dt-date').val(),
      $new_salary = $('.add-new-record .dt-salary').val();

    if ($new_name != '') {
      dt_basic.row
        .add({
          responsive_id: null,
          id: count,
          full_name: $new_name,
          post: $new_post,
          email: $new_email,
          start_date: $new_date,
          salary: '$' + $new_salary,
          status: 5
        })
        .draw();
      count++;
      $('.modal').modal('hide');
    }
  });

  // Delete Record
  $('.datatables-basic tbody').on('click', '.delete-record', function () {
    dt_basic.row($(this).parents('tr')).remove().draw();
  });
	
	 
 
});	
</script>
@endsection