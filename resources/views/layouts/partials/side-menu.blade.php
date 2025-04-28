<!-- BEGIN: Main Menu-->
<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow erpnewsidemenu" data-scroll-to-active="true">

    <div class="shadow-bottom"></div>
    <div class="main-menu-content newmodulleftmenu">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
            <li class="nav-item">
                <a class="d-flex align-items-center dashboard-icon" href="https://login.thepresence360.com"><i
                        data-feather="home"></i><span class="menu-title text-truncate"
                        data-i18n="Dashboards">HRMS</span></a>

            </li>
            @if ($organization_id == 6 || $organization_id == 4 || $organization_id == 9 || $organization_id == 86)
                <li @if (Route::currentRouteName() == 'crm.home') class="active nav-item" @else class="nav-item" @endif
                    url={{ Route::currentRouteName() }}><a class="d-flex align-items-center"
                        href="{{ route('crm.home') }}"><i data-feather="alert-circle"></i><span
                            class="menu-title text-truncate">CRM</span></a>
                </li>
                <li @if (Route::currentRouteName() == 'customers.dashboard') class="active nav-item" @else class="nav-item" @endif
                    url={{ Route::currentRouteName() }}><a class="d-flex align-items-center"
                        href="{{ route('customers.dashboard') }}"><i data-feather="users"></i><span
                            class="menu-title text-truncate">Customer</span></a>
                </li>
                <li @if (Route::currentRouteName() == 'prospects.dashboard') class="active nav-item" @else class="nav-item" @endif
                    url={{ Route::currentRouteName() }}><a class="d-flex align-items-center"
                        href="{{ route('prospects.dashboard') }}"><i data-feather="box"></i><span
                            class="menu-title text-truncate">Prospects</span></a>
                </li>
            @else
                <li
                    class="nav-item  {{ request()->routeIs('finance*') || request()->routeIs('cost*') || request()->routeIs('ledger-groups*') || request()->routeIs('ledgers*') || request()->routeIs('vouchers*') || request()->routeIs('trial_balance') || request()->routeIs('getLedgerReport') || request()->routeIs('finance.profitLoss') || request()->routeIs('finance.balanceSheet') ? 'active' : '' }}">
                    <a class="d-flex align-items-center" href="#"><i data-feather="file-text"></i><span
                            class="menu-title text-truncate" data-i18n="Dashboards">Finance</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Chart of Accounts</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li class="{{ request()->routeIs('ledger-groups*') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center"
                                        href="{{ route('ledger-groups.index') }}"><spanclass="menu-item
                                            text-truncate">Ledger
                                            Master</span></a>
                                </li>

                                <li class="{{ request()->routeIs('ledgers*') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center" href="{{ route('ledgers.index') }}"><span
                                            class="menu-item text-truncate">Ledgers</span></a>
                                </li>
                            </ul>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Cost Center</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li class="{{ request()->routeIs('cost-group*') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center" href="{{ route('cost-group.index') }}"><span
                                            class="menu-item text-truncate">Cost Group</span></a>
                                </li>
                                <li class="{{ request()->routeIs('cost-center*') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center" href="{{ route('cost-center.index') }}"><span
                                            class="menu-item text-truncate">Cost Center</span></a>
                                </li>

                            </ul>
                        </li>
                        <li class="{{ request()->routeIs('vouchers*') ? 'active' : '' }}"><a
                                class="d-flex align-items-center" href="{{ route('vouchers.index') }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Voucher</span></a>
                        </li>
                        <li class="{{ request()->routeIs('payment-vouchers*') ? 'active' : '' }}"><a
                                class="d-flex align-items-center" href="{{ route('payments.index') }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Payment
                                    Voucher</span></a>
                        </li>
                        <li class="{{ request()->routeIs('paymentVoucher.receipt') ? 'active' : '' }}"><a
                                class="d-flex align-items-center"
                                href="{{ route('receipts.index')}}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Receipt
                                    Voucher</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="{{ route('budget.index') }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Budget</span></a>
                        </li>

                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Reports</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li class="{{ request()->routeIs('trial_balance') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center" href="{{ route('trial_balance') }}"><span
                                            class="menu-item text-truncate">Trial Balance</span></a>
                                </li>
                                <li class="{{ request()->routeIs('getLedgerReport') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center" href="{{ route('getLedgerReport') }}"><span
                                            class="menu-item text-truncate">Ledger</span></a>
                                </li>

                                <li class="{{ request()->routeIs('finance.profitLoss') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center" href="{{ route('finance.profitLoss') }}"><span
                                            class="menu-item text-truncate">Profit & Loss</span></a>
                                </li>

                                <li class="{{ request()->routeIs('finance.balanceSheet') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center"
                                        href="{{ route('finance.balanceSheet') }}"><span
                                            class="menu-item text-truncate">Balance Sheet</span></a>
                                </li>
                                <li class="{{ request()->routeIs('voucher.credit.report') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center"
                                        href="{{ route('voucher.credit.report') }}"><span
                                            class="menu-item text-truncate">Creditors</span></a>
                                </li>

                                <li class="{{ request()->routeIs('voucher.debit.report') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center"
                                        href="{{ route('voucher.debit.report') }}"><span
                                            class="menu-item text-truncate">Debtors</span></a>
                                </li>



                            </ul>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                            class="menu-item text-truncate">Fixed Asset</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li class="{{ request()->routeIs('finance.fixed-asset.setup*') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center" href="{{route('finance.fixed-asset.setup.index')}}"><span class="menu-item text-truncate">Setup</span></a></li>

                                <li class="{{ request()->routeIs('finance.fixed-asset.registration*') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center" href="{{route('finance.fixed-asset.registration.index')}}"><span class="menu-item text-truncate">Registration</span></a></li>
                                <li>
                                    <a class="d-flex align-items-center" href="#"><span class="menu-item text-truncate">Depreciation</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="#"><span class="menu-item text-truncate">Split/Merger</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="#"><span class="menu-item text-truncate">Revaluation</span></a>
                                </li>
                                <li class="{{ request()->routeIs('finance.fixed-asset.issue-transfer*') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center" href="{{route('finance.fixed-asset.issue-transfer.index')}}"><span class="menu-item text-truncate">Issue/Transfer</span></a></li>
                                <li>
                                    <li class="{{ request()->routeIs('finance.fixed-asset.insurance*') ? 'active' : '' }}">
                                        <a class="d-flex align-items-center" href="{{route('finance.fixed-asset.insurance.index')}}"><span class="menu-item text-truncate">Insurance</span></a></li>
                                    <li>

                                    <li class="{{ request()->routeIs('finance.fixed-asset.maintenance*') ? 'active' : '' }}">
                                        <a class="d-flex align-items-center" href="{{route('finance.fixed-asset.maintenance.index')}}"><span class="menu-item text-truncate">Maint. &amp; Condition</span></a></li>
                                    <li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="nav-item {{ request()->routeIs('loan*') ? 'active' : '' }}">
                    <a class="d-flex align-items-center" href="#">
                        <i data-feather="dollar-sign"></i>
                        <span class="menu-title text-truncate">Loan</span>
                    </a>
                    <ul class="menu-content">
                        <li class="{{ request()->routeIs('loan.dashboard') ? 'active' : '' }}">
                            <a class="d-flex align-items-center" href="{{ route('loan.dashboard') }}">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate">Dashboard</span>
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('loan.index') ? 'active' : '' }}">
                            <a class="d-flex align-items-center" href="{{ route('loan.index') }}">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate">My Application</span>
                            </a>
                        </li>
                        <li>
                            <a class="d-flex align-items-center" href="#">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate">New Application</span>
                            </a>
                            <ul class="menu-content loanappsub-menu">
                                <li class="{{ request()->routeIs('loan.home-loan') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center" href="{{ route('loan.home-loan') }}">
                                        <span class="menu-item text-truncate"><i data-feather="home"></i> Home
                                            Loan</span>
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('loan.vehicle-loan') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center" href="{{ route('loan.vehicle-loan') }}">
                                        <span class="menu-item text-truncate"><i data-feather="truck"></i> Vehicle
                                            Loan</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('loan.term-loan') }}"><span
                                            class="menu-item text-truncate"><i data-feather="file-text"></i> Term
                                            Loan</span></a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a class="d-flex align-items-center" href="#"><span
                                    class="menu-item text-truncate"><i data-feather="circle"></i> In
                                    Progress</span></a>
                            <ul class="menu-content">
                                <li class="{{ request()->routeIs('loanAppraisal.index') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loanAppraisal.index') }}"><span
                                            class="menu-item text-truncate">Appraisal</span></a>
                                </li>
                                <li class="{{ request()->routeIs('loanAssessment.index') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loanAssessment.index') }}"><span
                                            class="menu-item text-truncate">Assessment</span></a>
                                </li>
                                <li class="{{ request()->routeIs('loanApproval.index') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loanApproval.index') }}"><span
                                            class="menu-item text-truncate">Approval</span></a>
                                </li>
                                <li class="{{ request()->routeIs('loanSanctionLetter.index') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loanSanctionLetter.index') }}"><span
                                            class="menu-item text-truncate">Sansaction Letter</span></a>
                                </li>
                                <li class="{{ request()->routeIs('loanProcessingFee.index') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loanProcessingFee.index') }}"><span
                                            class="menu-item text-truncate">Processing Fee</span></a>
                                </li>
                                <li class="{{ request()->routeIs('loanLegalDocumentation.index') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loanLegalDocumentation.index') }}"><span
                                            class="menu-item text-truncate">Legal Documentation</span></a>
                                </li>
                            </ul>
                        </li>
                        <li><a class="d-flex align-items-center" href="#">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate">Disbursement</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li class="{{ request()->routeIs('loan.disbursement') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loan.disbursement') }}"><span
                                            class="menu-item text-truncate">Request</span></a>
                                </li>
                                <li class="{{ request()->routeIs('loan.disbursement.assesment') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loan.disbursement.assesment') }}"><span
                                            class="menu-item text-truncate">Assessment</span></a>
                                </li>
                                <li class="{{ request()->routeIs('loan.disbursement.approval') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loan.disbursement.approval') }}"><span
                                            class="menu-item text-truncate">Approval</span></a>
                                </li>
                                <li class="{{ request()->routeIs('loan.disbursement.submission') ? 'active' : '' }}">
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loan.disbursement.submission') }}"><span
                                            class="menu-item text-truncate">Disbursement</span></a>
                                </li>
                            </ul>
                        </li>
                        <li class="{{ request()->routeIs('loan.recovery') ? 'active' : '' }}"><a
                                class="d-flex align-items-center" href="{{ route('loan.recovery') }}"><i
                                    data-feather="circle"></i><span
                                    class="menu-item text-truncate">Recovery</span></a>
                        </li>
                        <li class="{{ request()->routeIs('loan.settlement') ? 'active' : '' }}"><a
                                class="d-flex align-items-center" href="{{ route('loan.settlement') }}"><i
                                    data-feather="circle"></i><span
                                    class="menu-item text-truncate">Settlement</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Reports</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loan.report') }}"><span
                                            class="menu-item text-truncate">Detail report</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loandisbursement.report') }}"><span
                                            class="menu-item text-truncate">Disbursement Report</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loanrepayment.report') }}"><span
                                            class="menu-item text-truncate">Repayment Report</span></a>
                                </li>
                            </ul>
                        </li>

                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Masters</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loan.interest-rate') }}"><span
                                            class="menu-item text-truncate">Interest Rate</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('loan.financial-setup') }}"><span
                                            class="menu-item text-truncate">Financial setup</span></a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li
                    class="nav-item {{ request()->routeIs('land*') || request()->routeIs('lease*') ? 'active' : '' }}">
                    <a class="d-flex align-items-center" href="{{ route('land') }}"><i data-feather="map"></i><span
                            class="menu-title text-truncate">Land</span></a>
                    <ul class="menu-content">
                        <li class="{{ request()->routeIs('land.dashboard') ? 'active' : '' }}">
                            <a class="d-flex align-items-center" href="{{ route('land.dashboard') }}"><i
                                    data-feather="circle"></i><span
                                    class="menu-item text-truncate">Dashboard</span></a>
                        </li>
                        <li class="{{ request()->routeIs('land-parcel') ? 'active' : '' }}">
                            <a class="d-flex align-items-center" href="{{ route('land-parcel.index') }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Land
                                    Parcel</span></a>
                        </li>
                        <li class="{{ request()->routeIs('land-plot') ? 'active' : '' }}">
                            <a class="d-flex align-items-center" href="{{ route('land-plot.index') }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Land
                                    Plot</span></a>
                        </li>
                        <li class="{{ request()->routeIs('lease.index') ? 'active' : '' }}">
                            <a class="d-flex align-items-center" href="{{ route('lease.index') }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Lease</span></a>
                        </li>
                        <li class="{{ request()->routeIs('sale.invoice.index') ? 'active' : '' }}">
                            <a class="d-flex align-items-center"
                                href="{{ route('sale.invoice.index', ['type' => 'lease-invoice']) }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate">Lease
                                    Invoice</span></a>
                        </li>
                        <li class="{{ request()->routeIs('land.recovery') ? 'active' : '' }}">
                            <a class="d-flex align-items-center" href="{{ route('land.recovery') }}"><i
                                    data-feather="circle"></i><span
                                    class="menu-item text-truncate">Recovery</span></a>
                        </li>
                        <li>
                        <a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                            class="menu-item text-truncate">Reports</span></a>
                    <ul class="menu-content loanappsub-menu">
                        <li class="{{ request()->routeIs('land.report') ? 'active' : '' }}">
                            <a class="d-flex align-items-center" href="{{ route('land.report') }}"><i
                                    ></i><span class="menu-item text-truncate">Land Report</span></a>
                        </li>
                        <li class="{{ request()->routeIs('lease.report') ? 'active' : '' }}">
                            <a class="d-flex align-items-center" href="{{ route('lease.report') }}"><i
                                    ></i><span class="menu-item text-truncate">Lease Report</span></a>
                        </li>
                    </ul>
                </li>
                    </ul>
                </li>

                <li @if (Route::currentRouteName() == 'legal') class="active nav-item" @else class="nav-item" @endif
                    url={{ Route::currentRouteName() }}><a class="d-flex align-items-center"
                        href="{{ route('legal') }}"><i data-feather="alert-triangle"></i><span
                            class="menu-title text-truncate">Legal</span></a>
                </li>

                <li @if (Route::currentRouteName() == 'file-tracking.index') class="active nav-item" @else class="nav-item" @endif
                    url={{ Route::currentRouteName() }}><a class="d-flex align-items-center"
                        href="{{ route('file-tracking.index') }}"><i data-feather="file-text"></i><span
                            class="menu-title text-truncate">File Tracking</span></a>
                </li>
                <li class="nav-item {{ request()->routeIs('document-drive*') ? 'active' : '' }}">
                    <a class="d-flex align-items-center" href="{{ route('document-drive.index') }}">
                        <i data-feather="folder"></i>
                        <span class="menu-title text-truncate">Document Drive</span>
                    </a>
                    <ul class="menu-content">
                        <li class="@if (Route::currentRouteName() == 'document-drive.index') active @else '' @endif">
                            <a class="d-flex align-items-center" href="{{ route('document-drive.index') }}">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate">My Drive</span>
                            </a>
                        </li>
                        <li class="@if (Route::currentRouteName() == 'document-drive.shared-with-me') active @else '' @endif">
                            <a class="d-flex align-items-center" href="{{ route('document-drive.shared-with-me') }}">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate">Shared with me</span>
                            </a>
                        </li>
                        <li class="@if (Route::currentRouteName() == 'document-drive.shared-drive') active @else '' @endif">
                            <a class="d-flex align-items-center" href="{{ route('document-drive.shared-drive') }}">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate">Shared Drive</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="d-flex align-items-center dashboard-icon" href="#"><i
                            data-feather="map-pin"></i><span class="menu-title text-truncate"
                            data-i18n="Dashboards">Projects</span></a>

                </li>

                <li
                    class="nav-item {{ request()->routeIs('categories*') || request()->routeIs('po*') || request()->routeIs('supplier-invoice*') || request()->routeIs('mrn*') || request()->routeIs('item*') || request()->routeIs('vendor*') || request()->routeIs('customer*') || request()->routeIs('hsn*') || request()->routeIs('payment-terms*') || request()->routeIs('attributes*') || request()->routeIs('units*') || request()->routeIs('tax*') ? 'active' : '' }}">

                    <a class="d-flex align-items-center dashboard-icon" href="#"><i
                            data-feather="inbox"></i><span class="menu-title text-truncate"
                            data-i18n="Dashboards">Procurement</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="{{ route('pi.index') }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate"
                                    data-i18n="eCommerce">Purchase Indent</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="{{ url('purchase-order') }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate"
                                    data-i18n="eCommerce">Purchase Order</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="{{ url('supplier-invoice') }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate"
                                    data-i18n="eCommerce">Supplier Invoice</span></a>
                        </li>
                        <li>
                            <a class="d-flex align-items-center" href="{{ route('material-receipt.index') }}">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate" data-i18n="eCommerce">
                                    Material Receipt
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="d-flex align-items-center" href="{{ route('purchase-bill.index') }}">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate" data-i18n="eCommerce">
                                    Purchase Bill
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="d-flex align-items-center" href="{{ route('expense-adv.index') }}">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate" data-i18n="eCommerce">
                                    Expense Advice
                                </span>
                            </a>
                        </li>
                    </ul>

                </li>
                <li class="nav-item ">
                    <a class="d-flex align-items-center dashboard-icon" href="#">
                        <i data-feather="inbox"></i>
                        <span class="menu-title text-truncate" data-i18n="Dashboards">Inventory</span>
                    </a>
                    <ul class="menu-content">
                        <li>
                            <a class="d-flex align-items-center" href="#">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate" data-i18n="eCommerce">Material Request
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="d-flex align-items-center" href="#">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate" data-i18n="eCommerce">Material Issue
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="d-flex align-items-center" href="#">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate" data-i18n="eCommerce">Material Receipt
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="d-flex align-items-center" href="#">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate" data-i18n="eCommerce">Location Transfer Out
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="d-flex align-items-center" href="#">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate" data-i18n="eCommerce">Location Transfer In
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="d-flex align-items-center" href="#">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate" data-i18n="eCommerce">Stock Adjustment
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="d-flex align-items-center" href="#">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate" data-i18n="eCommerce">Physical Stock Take
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="d-flex align-items-center" href="{{ route('inventory-report.index') }}">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate" data-i18n="eCommerce">Inventory Report
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="d-flex align-items-center" href="#">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate">Masters</span>
                            </a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="#">
                                        <span class="menu-item text-truncate">Stores</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>

                <li class="active nav-item"><a class="d-flex align-items-center" href="#"><i data-feather="inbox"></i><span class="menu-title text-truncate" data-i18n="Dashboards">Recuritment</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="{{ route('recruitment.dashboard') }}"><i data-feather="circle"></i><span class="menu-item text-truncate">Dashboard</span></a></li>
                        <li><a class="d-flex align-items-center" href="{{ route('recruitment.requests') }}"><i data-feather="circle"></i><span class="menu-item text-truncate">My Request</span></a></li>
                        <li><a class="d-flex align-items-center" href="ijp.html"><i data-feather="circle"></i><span class="menu-item text-truncate">Internal Job Posting</span></a></li>
                        <li><a class="d-flex align-items-center" href="referal.html"><i data-feather="circle"></i><span class="menu-item text-truncate">My Referral</span></a></li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span class="menu-item text-truncate">Assessment</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="assessment.html"><span class="menu-item text-truncate">Create Task</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="assess-result.html"><span class="menu-item text-truncate">Results</span></a>
                                </li>
                            </ul>
                        </li><li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span class="menu-item text-truncate">HR</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="dashboard-hr.html"><span class="menu-item text-truncate">Dashboard</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="request-hr.html"><span class="menu-item text-truncate">Requests</span></a></li>
                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('recruitment.jobs') }}"><span class="menu-item text-truncate">Jobs Created</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('recruitment.job-candidates') }}"><span class="menu-item text-truncate">Candidates</span></a>
                                </li>
                            </ul>
                        </li>
                        
                    </ul>
                </li> 

                <li class="nav-item ">
                    <a class="d-flex align-items-center dashboard-icon" href="#"><i
                            data-feather="archive"></i><span class="menu-title text-truncate"
                            data-i18n="Dashboards">Sales</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center"
                                href="{{ route('sale.order.index', ['type' => 'sq']) }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate"
                                    data-i18n="eCommerce">Quotation</span></a>
                        </li>
                        <li><a class="d-flex align-items-center"
                                href="{{ route('sale.order.index', ['type' => 'so']) }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate"
                                    data-i18n="eCommerce">Order</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="{{ route('sale.invoice.index') }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate"
                                    data-i18n="eCommerce">Invoice</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="{{ route('sale.return.index') }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate"
                                    data-i18n="eCommerce">Return</span></a>
                        </li>

                    </ul>
                </li>

                <li class="nav-item ">
                    <a class="d-flex align-items-center dashboard-icon" href="#"><i
                            data-feather="external-link"></i><span class="menu-title text-truncate"
                            data-i18n="Dashboards">Manufacturing</span></a>
                    <ul class="menu-content">
                        <li><a class="d-flex align-items-center" href="{{ route('bill.of.material.index') }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate"
                                    data-i18n="eCommerce">Bill of Material</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Masters</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('stations.index') }}"><span
                                            class="menu-item text-truncate">Stations</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('product-sections.index') }}"><span
                                            class="menu-item text-truncate">Sections</span></a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>


                <li class="nav-item {{ request()->routeIs('book*') ? 'active' : '' }}">
                    <a class="d-flex align-items-center dashboard-icon" href="#"><i
                            data-feather="grid"></i><span class="menu-title text-truncate"
                            data-i18n="Dashboards">Master
                            Management</span></a>
                    <ul class="menu-content">

                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Series Master</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li
                                    class="{{ Route::currentRouteName() == 'book-type.index' || Route::currentRouteName() == 'bookType.create' || Route::currentRouteName() == 'bookTypeEdit' ? 'active' : '' }}">
                                    <a class="d-flex align-items-center" href="{{ route('book-type.index') }}"><span
                                            class="menu-item text-truncate">Book Type</span></a>
                                </li>
                                <li
                                    class="{{ Route::currentRouteName() == 'book' || Route::currentRouteName() == 'book_create' || Route::currentRouteName() == 'bookEdit' ? 'active' : '' }}">
                                    <a class="d-flex align-items-center" href="{{ route('book') }}"><span
                                            class="menu-item text-truncate">Series</span></a>
                                </li>
                            </ul>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Legal</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="{{ url('/issue-type') }}"><span
                                            class="menu-item text-truncate">Issue Type</span></a>
                                </li>
                            </ul>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Item Master</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('attributes.index') }}"><span
                                            class="menu-item text-truncate">Attributes</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('product-specifications.index') }}"><span
                                            class="menu-item text-truncate">Specifications</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('units.index') }}"><span
                                            class="menu-item text-truncate">Unit of Measurement</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('item.index') }}"><span
                                            class="menu-item text-truncate">Items</span></a>
                                </li>
                            </ul>
                        </li>
                        <li><a class="d-flex align-items-center" href="{{ route('customer.index') }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate"
                                    data-i18n="eCommerce">Customer Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="{{ route('vendor.index') }}"><i
                                    data-feather="circle"></i><span class="menu-item text-truncate"
                                    data-i18n="eCommerce">Vendor
                                    Master</span></a>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Compliances</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('hsn.index') }}"><span
                                            class="menu-item text-truncate">HSN/SAC</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('tax.index') }}"><span
                                            class="menu-item text-truncate">Tax</span></a>
                                </li>

                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('terms.index') }}"><span
                                            class="menu-item text-truncate">Term And Condition</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('discount-masters.index') }}">
                                        <span class="menu-item text-truncate">Discount Master</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('expense-masters.index') }}">
                                        <span class="menu-item text-truncate">Expense Master</span>
                                    </a>
                                </li>

                            </ul>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">Common</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li @if (Route::currentRouteName() == 'user-signature.index') class="active" @endif
                                    url={{ Route::currentRouteName() }}><a class="d-flex align-items-center"
                                        href="{{ route('user-signature.index') }}"><span class="menu-title text-truncate">Signature</span></a>
                                </li>
 <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('asset-category.index') }}"><span
                                            class="menu-item text-truncate">Asset Category</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('categories.index') }}"><span
                                            class="menu-item text-truncate">Category</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('payment-terms.index') }}"><span
                                            class="menu-item text-truncate">Payment Terms</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('exchange-rates.index') }}"><span
                                            class="menu-item text-truncate">Exchange Rate</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('store.index') }}"><span
                                            class="menu-item text-truncate">Stores</span></a>
                                </li>

                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('stations.index') }}"><span
                                            class="menu-item text-truncate">Stations</span></a>
                                </li>


                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('bank.index') }}"><span
                                            class="menu-item text-truncate">Banks</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('documents.index') }}"><span
                                            class="menu-item text-truncate">Documents</span></a>
                                </li>

                            </ul>
                        </li>
                        <li>
                            <a class="d-flex align-items-center" href="#">
                                <i data-feather="circle"></i>
                                <span class="menu-item text-truncate">Account SetUp</span>
                            </a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('stock-accounts.index') }}">
                                        <span class="menu-item text-truncate">Stock Account</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('cogs-accounts.index') }}">
                                        <span class="menu-item text-truncate">Cogs Account</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('gr-accounts.index') }}">
                                        <span class="menu-item text-truncate">Gr Account</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center" href="{{ route('sales-accounts.index') }}">
                                        <span class="menu-item text-truncate">Sales Account</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                    class="menu-item text-truncate">DPR Templates</span></a>
                            <ul class="menu-content loanappsub-menu">
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('dpr-template.index') }}"><span
                                            class="menu-item text-truncate">Templates</span></a>
                                </li>
                                <li>
                                    <a class="d-flex align-items-center"
                                        href="{{ route('dpr-master.index') }}"><span
                                            class="menu-item text-truncate">Template Fields</span></a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li>
                    <a class="d-flex align-items-center" href="{{ route('bank.index') }}">
                        <i data-feather="grid"></i>
                        <span class="menu-item text-truncate">Banks</span></a>
                </li>
                <li>
                    <a class="d-flex align-items-center" href="{{ route('documents.index') }}">
                        <i data-feather="grid"></i>
                        <span class="menu-item text-truncate">Documents</span></a>
                </li>
            @endif
        </ul>
    </div>

</div>
<!-- END: Main Menu-->
