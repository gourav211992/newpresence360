<!-- BEGIN: Main Menu-->
<div class="main-menu menu-fixed menu-light menu-accordion menu-shadow erpnewsidemenu" data-scroll-to-active="true">

    <div class="shadow-bottom"></div>
    <div class="main-menu-content newmodulleftmenu">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
            <li class="active nav-item"><a class="d-flex align-items-center" href="#"><i
                        data-feather="inbox"></i><span class="menu-title text-truncate"
                        data-i18n="Dashboards">Recuritment</span></a>
                <ul class="menu-content">
                    <li><a class="d-flex align-items-center" href="{{ route('recruitment.dashboard') }}"><i
                                data-feather="circle"></i><span class="menu-item text-truncate">Dashboard</span></a>
                    </li>
                    <li><a class="d-flex align-items-center" href="{{ route('recruitment.requests') }}"><i
                                data-feather="circle"></i><span class="menu-item text-truncate">My
                                Request</span></a></li>
                    <li><a class="d-flex align-items-center" href="{{ route('recruitment.internal-jobs') }}"><i
                                data-feather="circle"></i><span class="menu-item text-truncate">Internal Job
                                Posting</span></a></li>
                    <li><a class="d-flex align-items-center" href="{{ route('recruitment.my-activities') }}"><i
                                data-feather="circle"></i><span class="menu-item text-truncate">My Activity</span></a>
                    </li>
                    <li><a class="d-flex align-items-center" href="{{ route('recruitment.my-referal') }}"><i
                                data-feather="circle"></i><span class="menu-item text-truncate">My Referral</span></a>
                    </li>
                    {{-- <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                class="menu-item text-truncate">Assessment</span></a>
                        <ul class="menu-content loanappsub-menu">
                            <li>
                                <a class="d-flex align-items-center" href="assessment.html"><span
                                        class="menu-item text-truncate">Create Task</span></a>
                            </li>
                            <li>
                                <a class="d-flex align-items-center" href="assess-result.html"><span
                                        class="menu-item text-truncate">Results</span></a>
                            </li>
                        </ul>
                    </li> --}}
                    <li><a class="d-flex align-items-center" href="#"><i data-feather="circle"></i><span
                                class="menu-item text-truncate">HR</span></a>
                        <ul class="menu-content loanappsub-menu">
                            <li>
                                <a class="d-flex align-items-center"
                                    href="{{ route('recruitment.hr-dashboard') }}"><span
                                        class="menu-item text-truncate">Dashboard</span></a>
                            </li>
                            <li>
                                <a class="d-flex align-items-center" href="{{ route('recruitment.request-hr') }}"><span
                                        class="menu-item text-truncate">Requests</span></a>
                            </li>
                            <li>
                                <a class="d-flex align-items-center" href="{{ route('recruitment.jobs') }}"><span
                                        class="menu-item text-truncate">Jobs Created</span></a>
                            </li>
                            <li>
                                <a class="d-flex align-items-center"
                                    href="{{ route('recruitment.job-candidates') }}"><span
                                        class="menu-item text-truncate">Candidates</span></a>
                            </li>
                        </ul>
                    </li>

                </ul>
            </li>
        </ul>
    </div>

</div>
<!-- END: Main Menu-->
