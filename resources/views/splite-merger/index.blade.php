@extends('layouts.app')

@section('content')


    <!-- BEGIN: Main Menu-->
    <!-- <div class="main-menu menu-fixed menu-light menu-accordion menu-shadow erpnewsidemenu" data-scroll-to-active="true">

        <div class="shadow-bottom"></div>
        <div class="main-menu-content newmodulleftmenu">
            <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
                <li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i
                            data-feather="file-text"></i><span class="menu-title text-truncate"
                            data-i18n="Dashboards">Finance</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="voucher.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Voucher</span></a>
                        </li>

                        <li><a class="d-flex align-items-center" href="group.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Group Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="cost-center.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Cost Center
                                    Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="account.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Account Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="close-fy.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Close F.Y</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Reports</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="trail-balance.html"><span
                                            class="menu-item text-truncate">Trial Balance</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="ledger.html"><span
                                            class="menu-item text-truncate">Ledger</span></a></li>
                                <li>
                                    <a class="d-flex align-items-center" href="profit-loss.html"><span
                                            class="menu-item text-truncate">Profit & Loss Account</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="balance-sheet.html"><span
                                            class="menu-item text-truncate">Balance Sheet</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="creditors.html"><span
                                            class="menu-item text-truncate">Creditors</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="creditors.html"><span
                                            class="menu-item text-truncate">Debtors</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="tds-reprot.html"><span
                                            class="menu-item text-truncate">TDS Report</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="tcs-report.html"><span
                                            class="menu-item text-truncate">TCS Report</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="cashflow-statement.html"><span
                                            class="menu-item text-truncate">Cashflow Statement</span></a>
                                </li>

                            </ul>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">GST Reports</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="gst.html"><span
                                            class="menu-item text-truncate">GST 1</span></a>
                                </li>

                            </ul>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Fixed Assets</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="setup.html"><span
                                            class="menu-item text-truncate">Setup</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="registration.html"><span
                                            class="menu-item text-truncate">Registration</span></a></li>
                                <li>
                                    <a class="d-flex align-items-center" href="depreciation.html"><span
                                            class="menu-item text-truncate">Depreciation</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="split-merger.html"><span
                                            class="menu-item text-truncate">Split/Merger</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="revaluation.html"><span
                                            class="menu-item text-truncate">Revaluation</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="issue-asset.html"><span
                                            class="menu-item text-truncate">Issue/Transfer</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="insurance.html"><span
                                            class="menu-item text-truncate">Insurance</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="maint-cond.html"><span
                                            class="menu-item text-truncate">Maint. & Condition</span></a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="../new-sp-screens/budget.html"><i
                            data-feather="sliders"></i><span class="menu-title text-truncate">Budget</span></a>
                </li>
                <li class="active nav-item"><a class="d-flex align-items-center" href="../new-sp-screens/po.html"><i
                            data-feather="box"></i><span class="menu-title text-truncate"
                            data-i18n="Dashboards">Procurement</span></a>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i
                            data-feather="inbox"></i><span class="menu-title text-truncate"
                            data-i18n="Dashboards">Inventory</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="../new-sp-screens/mrn.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Material
                                    Receipt</span></a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i
                            data-feather="archive"></i><span class="menu-title text-truncate"
                            data-i18n="Dashboards">Sales</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="sales.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Sales Order</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="invoice.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Invoice</span></a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i
                            data-feather="dollar-sign"></i><span class="menu-title text-truncate">Loan</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="../loan/dashboard.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Dashboard</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="../loan/index.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">My
                                    Application</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">New Application</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="../loan/home-loan.html"><span
                                            class="menu-item text-truncate"><i data-feather="home"></i> Home
                                            Loan</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="../loan/vehicle-loan.html"><span
                                            class="menu-item text-truncate"><i data-feather="truck"></i> Vehicle
                                            Loan</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="../loan/term-loan.html"><span
                                            class="menu-item text-truncate"><i data-feather="file-text"></i> Term
                                            Loan</span></a>
                                </li>
                            </ul>
                        </li>
                        <li><a class="d-flex align-items-center" href="../loan/disbursement.html"><i
                                    data-feather="circle"></i><span
                                    class="menu-item text-truncate">Disbursement</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="../loan/recovery.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Recovery</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="../loan/settlement.html"><i
                                    data-feather="circle"></i><span
                                    class="menu-item text-truncate">Settlement</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Masters</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="../loan/interest-rate.html"><span
                                            class="menu-item text-truncate">Interest Rate</span></a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i
                            data-feather="map"></i><span class="menu-title text-truncate">Land</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="../land/my-land.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Land
                                    Parcel</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="../land/my-land-plot.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Land Plot</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="../land/on-lease.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">On Lease</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="../land/recovery.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Recovery</span></a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="../legal/legal.html"><i
                            data-feather="alert-triangle"></i><span class="menu-title text-truncate">Legal</span></a>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="document-manage.html"><i
                            data-feather="folder"></i><span class="menu-title text-truncate">Document Drive</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="document-manage.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">My Drive</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="document-manage.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Shared with
                                    me</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="document-manage.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Shared
                                    Drive</span></a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="../survey/survey.html"><i
                            data-feather="bar-chart"></i><span class="menu-title text-truncate">Survey</span></a>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="../crm/dashboard.html"><i
                            data-feather="database"></i><span class="menu-title text-truncate">CRM</span></a>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i
                            data-feather="bar-chart-2"></i><span class="menu-title text-truncate"
                            data-i18n="Dashboards">Reports</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="../reports/po-report.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">PO</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="../reports/sales-report.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Sales</span></a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item"><a class="d-flex align-items-center" href="index.html"><i
                            data-feather="grid"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Master
                            Management</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="store.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Store</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Account Setup</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="stock-account-setup.html"><span
                                            class="menu-item text-truncate">Stock Account</span></a>
                                </li>
                            </ul>
                        </li>

                        <li><a class="d-flex align-items-center" href="exchange-rate.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Exchange
                                    Rate</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="book-type.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Book Type</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="book.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Series</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="station.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Station</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="attribute.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Attribute</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="item-master.html"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Item
                                    Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="price.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Price Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="bom.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Bill of Material</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="customer.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Customer Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="vendor.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Vendor Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="tax.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Tax Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="hsn.html"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">HSN/SAC Master</span></a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

    </div> -->
    <!-- END: Main Menu-->

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
                                <h2 class="content-header-title float-start mb-0">Split</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Add New</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <button onClick="javascript: history.go(-1)"
                                class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
                                Back</button>
                            <button onClick="javascript: history.go(-1)" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="check-circle"></i> Submit</button>
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
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="newheader border-bottom mb-2 pb-25  ">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>


                                                    <div class="col-md-6 text-sm-end">
                                                        <span
                                                            class="badge rounded-pill badge-light-secondary forminnerstatus">
                                                            Status : <span class="text-success">Approved</span>
                                                        </span>
                                                    </div>

                                                </div>
                                            </div>

                                        </div>




                                        <div class="col-md-8">

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Series <span
                                                            class="text-danger">*</span></label>
                                                </div>

                                                <div class="col-md-5">
                                                    <select class="form-select">
                                                        <option>Select</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Doc No <span
                                                            class="text-danger">*</span></label>
                                                </div>

                                                <div class="col-md-5">
                                                    <input type="text" class="form-control">
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Doc Date <span
                                                            class="text-danger">*</span></label>
                                                </div>

                                                <div class="col-md-5">
                                                    <input type="date" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">

                                            <div
                                                class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                                <h5
                                                    class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                                    <strong><i data-feather="arrow-right-circle"></i> Approval
                                                        History</strong>
                                                    <strong
                                                        class="badge rounded-pill badge-light-secondary amendmentselect">Rev.
                                                        No.
                                                        <select class="form-select">
                                                            <option>00</option>
                                                            <option>01</option>
                                                            <option>02</option>
                                                            <option>03</option>
                                                        </select>
                                                    </strong>

                                                </h5>
                                                <ul class="timeline ms-50 newdashtimline ">
                                                    <li class="timeline-item">
                                                        <span class="timeline-point timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div
                                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Deepak Kumar</h6>
                                                                <span
                                                                    class="badge rounded-pill badge-light-primary">Amendment</span>
                                                            </div>
                                                            <h5>(2 min ago)</h5>
                                                            <p>Description will come here</p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span class="timeline-point timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div
                                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Aniket Singh</h6>
                                                                <span
                                                                    class="badge rounded-pill badge-light-danger">Rejected</span>
                                                            </div>
                                                            <h5>(2 min ago)</h5>
                                                            <p>Description will come here</p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span
                                                            class="timeline-point timeline-point-warning timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div
                                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Deewan Singh</h6>
                                                                <span
                                                                    class="badge rounded-pill badge-light-warning">Pending</span>
                                                            </div>
                                                            <h5>(5 min ago)</h5>
                                                            <p>Description will come here</p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span
                                                            class="timeline-point timeline-point-info timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div
                                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Brijesh Kumar</h6>
                                                                <span
                                                                    class="badge rounded-pill badge-light-success">Approved</span>
                                                            </div>
                                                            <h5>(10 min ago)</h5>
                                                            <p>Description will come here</p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span
                                                            class="timeline-point timeline-point-danger timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div
                                                                class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Deepender Singh</h6>
                                                                <span
                                                                    class="badge rounded-pill badge-light-success">Approved</span>
                                                            </div>
                                                            <h5>(5 day ago)</h5>
                                                            <p><a href="#"><i data-feather="download"></i></a>
                                                                Description will come here </p>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                            </div>


                            <div class="row customernewsection-form">
                                <div class="col-md-12">
                                    <div class="card quation-card">
                                        <div class="card-header newheader">
                                            <div>
                                                <h4 class="card-title">Old Asset Details</h4>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">


                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Asset Code & Name <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" placeholder="Select"
                                                            class="form-control mw-100 p_ledgerselecct"  />
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Sub-Asset Code <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" placeholder="Select"
                                                            class="form-control mw-100 c_ledgerselecct" />
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Last Date of Dep. <span
                                                                class="text-danger">*</span></label>
                                                        <input type="date" value="2025-03-31" class="form-control">
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Current Value <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" value="5,30,000.00" disabled
                                                            class="form-control">
                                                    </div>
                                                </div>

                                            </div>


                                        </div>
                                    </div>

                                </div>
                            </div>



                            <div class="card">
                                <div class="card-body customernewsection-form">


                                    <div class="border-bottom mb-2 pb-25">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="newheader ">
                                                    <h4 class="card-title text-theme">New Asset Detail</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-sm-end">
                                                <a href="#" id="delete_new_sub_asset" class="btn btn-sm btn-outline-danger me-50">
                                                    <i data-feather="x-circle"></i> Delete</a>
                                                <a href="#" id= "add_new_sub_asset" class="btn btn-sm btn-outline-primary">
                                                    <i data-feather="plus"></i> Add New</a>
                                            </div>
                                        </div>
                                    </div>





                                    <div class="row">

                                        <div class="col-md-12">


                                            <div class="table-responsive pomrnheadtffotsticky">
                                                <table
                                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                    <thead>
                                                        <tr>
                                                            <th class="customernewsection-form">
                                                                <div
                                                                    class="form-check form-check-primary custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        id="Email">
                                                                    <label class="form-check-label" for="Email"></label>
                                                                </div>
                                                            </th>
                                                            <th width="200">Asset Code</th>
                                                            <th>Asset Name</th>
                                                            <th width="200">Sub Asset Code</th>
                                                            <th width="100">Quantity</th>
                                                            <th>Current Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="mrntableselectexcel">

                                                    </tbody>


                                                </table>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>



                            <div class="row customernewsection-form">
                                <div class="col-md-12">
                                    <div class="card quation-card">
                                        <div class="card-header newheader">
                                            <div>
                                                <h4 class="card-title">Asset Details</h4>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">


                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Category <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-select select2">
                                                            <option>Select</option>
                                                            <option selected>IT Asset</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Quantity <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" value="20" disabled />
                                                    </div>
                                                </div>


                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Ledger <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-select select2">
                                                            <option>Select</option>
                                                            <option selected>Laptop</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Ledger Group <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-select select2">
                                                            <option>Select</option>
                                                            <option selected>Laptop</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Capitalize Date <span
                                                                class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" disabled
                                                            value="2025-04-01" />
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Maint. Schedule <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-select">
                                                            <option>Select</option>
                                                            <option>Yearly</option>
                                                            <option>Monthly</option>
                                                            <option>Weekly</option>
                                                        </select>
                                                    </div>
                                                </div>



                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Dep. Method <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" value="SLM" disabled />
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Est. Useful life (yrs) <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" value="10" />
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Salvage Value <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" disabled class="form-control" />
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Dep % <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" value="10" disabled />
                                                    </div>
                                                </div>


                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Total Dep. <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" value="1000" disabled />
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Current Value <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" value="10000"
                                                            disabled />
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

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>



    <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1"
        aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>MRN</strong>? After Amendment
                        this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select
                            Item</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">GRN No. <span class="text-danger">*</span></label>
                                <select class="form-select">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Code <span class="text-danger">*</span></label>
                                <select class="form-select">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                                <select class="form-select">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <select class="form-select">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="col  mb-1">
                            <label class="form-label">&nbsp;</label><br />
                            <button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                        </div>

                        <div class="col-md-12">


                            <div class="table-responsive">
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                    <thead>
                                        <tr>
                                            <th>
                                                <div class="form-check form-check-inline me-0">
                                                    <input class="form-check-input" type="checkbox" name="podetail"
                                                        id="inlineCheckbox1">
                                                </div>
                                            </th>
                                            <th>GRN No.</th>
                                            <th>GRN Date</th>
                                            <th>Vendor Code</th>
                                            <th>Vendor Name</th>
                                            <th>Item</th>
                                            <th>Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="form-check form-check-inline me-0">
                                                    <input class="form-check-input" type="checkbox" name="podetail"
                                                        id="inlineCheckbox1">
                                                </div>
                                            </td>
                                            <td>2901</td>
                                            <td>10-04-2023</td>
                                            <td class="fw-bolder text-dark">8765</td>
                                            <td>DOW CHECMICAL (AUSTRALIA)</td>
                                            <td>SPRINGTEK Coir Bond 5 Mattress</td>
                                            <td>200</td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <div class="form-check form-check-inline me-0">
                                                    <input class="form-check-input" type="checkbox" name="podetail"
                                                        id="inlineCheckbox1">
                                                </div>
                                            </td>
                                            <td>3312</td>
                                            <td>10-04-2023</td>
                                            <td class="fw-bolder text-dark">4576</td>
                                            <td>DOW CHECMICAL (AUSTRALIA)</td>
                                            <td>2inch Double Coir Sofa</td>
                                            <td>20</td>
                                        </tr>





                                    </tbody>


                                </table>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                            data-feather="x-circle"></i> Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i>
                        Process</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade text-start" id="postvoucher" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Post
                            Voucher</h4>
                        <p class="mb-0">View Details</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Series <span class="text-danger">*</span></label>
                                <input class="form-control" disabled value="VOUCH/2024" />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Voucher No <span class="text-danger">*</span></label>
                                <input class="form-control" disabled value="098" />
                            </div>
                        </div>

                        <div class="col-md-12">


                            <div class="table-responsive">
                                <table
                                    class="mt-1 table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Leadger Code</th>
                                            <th>Leadger Name</th>
                                            <th class="text-end">Debit</th>
                                            <th class="text-end">Credit</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td class="fw-bolder text-dark">2901</td>
                                            <td>Finance</td>
                                            <td class="text-end">10000</td>
                                            <td class="text-end">0</td>
                                            <td>Remarks come here...</td>
                                        </tr>

                                        <tr>
                                            <td>2</td>
                                            <td class="fw-bolder text-dark">2901</td>
                                            <td>Finance</td>
                                            <td class="text-end">0</td>
                                            <td class="text-end">10000</td>
                                            <td>Remarks come here...</td>
                                        </tr>

                                        <tr>
                                            <td colspan="3" class="fw-bolder text-dark text-end">Total</td>
                                            <td class="fw-bolder text-dark text-end">10000</td>
                                            <td class="fw-bolder text-dark text-end">10000</td>
                                            <td></td>
                                        </tr>





                                    </tbody>


                                </table>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                            data-feather="x-circle"></i> Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i>
                        Submit</button>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="discount" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Add Discount</h1>
                    <p class="text-center">Enter the details below.</p>


                    <div class="text-end"><a href="#" class="text-primary add-contactpeontxt mt-50"><i
                                data-feather='plus'></i> Add Discount</a></div>

                    <div class="table-responsive-md customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th width="150px">Discount Name</th>
                                    <th>Discount Type</th>
                                    <th>Discount %</th>
                                    <th>Discount Value</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#</td>
                                    <td>
                                        <select class="form-select mw-100">
                                            <option>Select</option>
                                            <option>Discount 1</option>
                                            <option>Discount 2</option>
                                            <option>Discount 3</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100">
                                            <option>Select</option>
                                            <option>Fixed</option>
                                            <option>Percentage</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control mw-100" /></td>
                                    <td><input type="text" class="form-control mw-100" /></td>
                                    <td>
                                        <a href="#" class="text-danger"><i data-feather="trash-2"></i></a>
                                    </td>
                                </tr>


                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-dark"><strong>Total</strong></td>
                                    <td class="text-dark"><strong>1000</strong></td>
                                    <td></td>
                                </tr>


                            </tbody>


                        </table>
                    </div>

                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit-address" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Edit Address</h1>
                    <p class="text-center">Enter the details below.</p>


                    <div class="row mt-2">
                        <div class="col-md-12 mb-1">
                            <label class="form-label">Select Address <span class="text-danger">*</span></label>
                            <select class="select2 form-select">
                                <option value="AK" selected>56, Sector 44 Rd Gurugram, Haryana, Pin Code - 122022, India
                                </option>
                                <option value="HI">Noida, U.P</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label">Country <span class="text-danger">*</span></label>
                            <select class="select2 form-select">
                                <option>Select</option>
                                <option>India</option>
                            </select>
                        </div>


                        <div class="col-md-6 mb-1">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <select class="select2 form-select">
                                <option>Select</option>
                                <option>Gautam Budh Nagar</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-1">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <select class="select2 form-select">
                                <option>Select</option>
                                <option>Noida</option>
                            </select>
                        </div>


                        <div class="col-md-6 mb-1">
                            <label class="form-label w-100">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" value="201301" placeholder="Enter Pincode" />
                        </div>

                        <div class="col-md-12 mb-1">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control"
                                placeholder="Enter Address">56, Sector 44 Rd, Kanhai Colony, Sector 52</textarea>
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

    <div class="modal fade" id="Remarks" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Add/Edit Remarks</h1>
                    <p class="text-center">Enter the details below.</p>


                    <div class="row mt-2">


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

    <div class="modal fade" id="expenses" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Add Expenses</h1>
                    <p class="text-center">Enter the details below.</p>

                    <div class="text-end"> <a href="#" class="text-primary add-contactpeontxt mt-50"><i
                                data-feather='plus'></i> Add Expenses</a></div>

                    <div class="table-responsive-md customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th width="150px">Expense Name</th>
                                    <th>Expense Type</th>
                                    <th>Expense %</th>
                                    <th>Expense Value</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#</td>
                                    <td>
                                        <select class="form-select mw-100">
                                            <option>Select</option>
                                            <option>Expense 1</option>
                                            <option>Expense 2</option>
                                            <option>Expense 3</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100">
                                            <option>Select</option>
                                            <option>Fixed</option>
                                            <option>Percentage</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control mw-100" /></td>
                                    <td><input type="text" class="form-control mw-100" /></td>
                                    <td>
                                        <a href="#" class="text-danger"><i data-feather="trash-2"></i></a>
                                    </td>
                                </tr>


                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-dark"><strong>Total</strong></td>
                                    <td class="text-dark"><strong>1000</strong></td>
                                    <td></td>
                                </tr>


                            </tbody>


                        </table>
                    </div>

                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="delivery" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="max-width: 900px">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Store Location</h1>
                    <p class="text-center">Enter the details below.</p>


                    <div class="text-end"><a href="#" class="text-primary add-contactpeontxt mt-50"><i
                                data-feather='plus'></i> Add Quantity</a></div>

                    <div class="table-responsive-md customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th width="80px">#</th>
                                    <th>Store</th>
                                    <th>Rack</th>
                                    <th>Shelf</th>
                                    <th>Bin</th>
                                    <th width="50px">Qty</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#</td>
                                    <td>
                                        <select class="form-select mw-100 select2">
                                            <option>Select</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2">
                                            <option>Select</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2">
                                            <option>Select</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2">
                                            <option>Select</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control mw-100" /></td>
                                    <td>
                                        <a href="#" class="text-danger"><i data-feather="trash-2"></i></a>
                                    </td>
                                </tr>


                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-dark"><strong>Total Qty</strong></td>
                                    <td class="text-dark"><strong>20</strong></td>
                                    <td></td>
                                </tr>


                            </tbody>


                        </table>
                    </div>

                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="taxdetail" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Taxes</h1>
                    <div class="table-responsive-md customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"
                            id="order_tax_main_table">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th width="150px">Tax</th>
                                    <th>Taxable Amount</th>
                                    <th>Tax %</th>
                                    <th>Tax Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>IGST</td>
                                    <td>111110.00</td>
                                    <td>18%</td>
                                    <td>19999.80</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>CGST</td>
                                    <td>111110.00</td>
                                    <td>9%</td>
                                    <td>19999.80</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>SGST</td>
                                    <td>111110.00</td>
                                    <td>9%</td>
                                    <td>19999.80</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="attribute" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Select Attribute</h1>
                    <p class="text-center">Enter the details below.</p>

                    <div class="table-responsive-md customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th>Attribute Name</th>
                                    <th>Attribute Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Color</td>
                                    <td>
                                        <select class="form-select select2">
                                            <option>Select</option>
                                            <option>Black</option>
                                            <option>White</option>
                                            <option>Red</option>
                                            <option>Golden</option>
                                            <option>Silver</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Size</td>
                                    <td>
                                        <select class="form-select select2">
                                            <option>Select</option>
                                            <option>5.11"</option>
                                            <option>5.10"</option>
                                            <option>5.09"</option>
                                            <option>5.00"</option>
                                            <option>6.20"</option>
                                        </select>
                                    </td>
                                </tr>





                            </tbody>


                        </table>
                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Select</button>
                </div>
            </div>
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
    <script src="../../../app-assets/vendors/js/editors/quill/katex.min.js"></script>
    <script src="../../../app-assets/vendors/js/editors/quill/highlight.min.js"></script>
    <script src="../../../app-assets/vendors/js/editors/quill/quill.min.js"></script>
    <script src="../../../app-assets/vendors/js/forms/select/select2.full.min.js"></script>
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src="../../../app-assets/js/core/app-menu.js"></script>
    <script src="../../../app-assets/js/core/app.js"></script>
    <!-- END: Theme JS-->

    <!-- BEGIN: Page JS-->
    <script src="../../../app-assets/js/scripts/forms/form-quill-editor.js"></script>
    <script src="../../../app-assets/js/scripts/forms/form-select2.js"></script>
    <link rel="stylesheet" href="../../../app-assets/js/jquery-ui.css">
    <script src="../../../app-assets/js/jquery-ui.js"></script>
    <!-- END: Page JS-->

    <script>
        $(window).on('load', function () {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })


        $(function () {
            $(".p_ledgerselecct").autocomplete({
                source: [
                    "Furniture (IT001)",
                    "Chair (IT002)",
                    "Table (IT003)",
                    "Laptop (IT004)",
                    "Bags (IT005)",
                ],
                minLength: 0
            }).focus(function () {
                if (this.value == "") {
                    $(this).autocomplete("search");
                }
            });
        });
        $(function () {
            $(".c_ledgerselecct").autocomplete({
                source: [
                    "Furniture (IT001)",
                    "Leg (IT002)",
                    "Table (IT003)",
                    "Laptop (IT004)",
                    "Bags (IT005)",
                ],
                minLength: 0
            }).focus(function () {
                if (this.value == "") {
                    $(this).autocomplete("search");
                }
            });
        });
        
        $(function () {
    $(".p_ledgerselecct").autocomplete({
        source: [
            "Furniture (IT001)",
            "Leg (IT002)",
            "Table (IT003)",
            "Laptop (IT004)",
            "Bags (IT005)",
        ],
        minLength: 0,
        select: function (event, ui) {
            const fullText = ui.item.value;
            const match = fullText.match(/\(([^)]+)\)/); // Extract text inside parentheses
            const code = match ? match[1] : '';
            console.log("Extracted Code:", code);
            // Optionally, return false to prevent default behavior
        }
    }).focus(function () {
        if (this.value == "") {
            $(this).autocomplete("search");
        }
    });
});


        $(".mrntableselectexcel tr").click(function () {
            $(this).addClass('trselected').siblings().removeClass('trselected');
            value = $(this).find('td:first').html();
        });

        $(document).on('keydown', function (e) {
            if (e.which == 38) {
                $('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
            } else if (e.which == 40) {
                $('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
            }
            $('.mrntableselectexcel').scrollTop($('.trselected').offset().top - 40);
        });

        $('#add_new_sub_asset').on('click', function() {
    const uniqueId = `chk_${Date.now()}`; // ensures unique checkbox ID
    const rowHtml = genereateSubAssetRow();
    $('.mrntableselectexcel').append(rowHtml);
});

function genereateSubAssetRow(code, pflag){
    console.log("kaka mana "); 
    const uniqueId = `chk_${Date.now()}`; 
    const newRow = `
        <tr>
            <td class="customernewsection-form">
                <div class="form-check form-check-primary custom-checkbox">
                    <input type="checkbox" class="form-check-input row-check" id="${uniqueId}">
                    <label class="form-check-label" for="${uniqueId}"></label>
                </div>
            </td>
            <td class="poprod-decpt">
                <input type="text" value="ASS001" placeholder="Enter" class="form-control mw-100 mb-25" />
            </td>
            <td class="poprod-decpt">
                <input type="text" placeholder="Enter" class="form-control mw-100 mb-25" />
            </td>
            <td class="poprod-decpt">
                <input type="text" value="ASS001-01" placeholder="Enter" disabled class="form-control mw-100 mb-25" />
            </td>
            <td>
                <input type="text" disabled value="1" class="form-control mw-100" />
            </td>
            <td>
                <input type="text" value="2000.00" class="form-control mw-100 text-end" />
            </td>
        </tr>
    `;
    return newRow;
}


$('#delete_new_sub_asset').on('click', function() {
    $('.mrntableselectexcel .row-check:checked').closest('tr').remove();
});


    </script>
<!-- END: Content-->
@endsection
