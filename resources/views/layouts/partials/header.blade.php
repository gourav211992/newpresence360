<!-- BEGIN: Header-->
<nav class="header-navbar navbar navbar-expand-lg align-items-center floating-nav d-block container-xxl erpnewheader">
    <div class="d-flex justify-content-between align-items-center">
        <div class="w-100">
            <div class="header-navbar navbar-light navbar-shadow new-navbarfloating">
                <div class="navbar-container d-flex content">
                    <div class="bookmark-wrapper d-flex align-items-center">
                        <ul class="nav navbar-nav headerlogo">
                            @if (isset($orgLogo))
                                <li>
                                    <img src="{{ $orgLogo ? $orgLogo : url('/img/thepresence360_logo.svg')}}" alt="Logo"/>
                                </li>
                            @endif
                        </ul>
                        <ul class="nav navbar-nav left-baricontop">
                            <li class="nav-item">
                                <a class="nav-link menu-toggle" href="#">
                                    <i></i>
                                </a>
                            </li>
                        </ul>
                        {{-- <ul class="nav navbar-nav bookmark-icons">
                            <li class="nav-item nav-search">
                                <a class="nav-link nav-link-search"><i class="ficon" data-feather="search"></i></a>
                                <div class="search-input">
                                    <div class="search-input-icon"><i data-feather="search"></i></div>
                                    <input class="form-control input" type="text" placeholder="Explore Vuexy..."
                                        tabindex="-1" data-search="search">
                                    <div class="search-input-close"><i data-feather="x"></i></div>
                                    <ul class="search-list search-list-main"></ul>
                                </div>
                            </li>
                        </ul> --}}
                    </div>

                    <ul class="nav navbar-nav align-items-center ms-auto">

                        @if(isset($iamOrganizations) && count($iamOrganizations))                        <li class="nav-item d-none d-lg-block select-organization-menu">
                            <form action="{{ route('update-organization') }}" method="POST">
                                @csrf

                                <select class="form-select" name="organization_id" id="organization" onchange="this.form.submit()">
                                    <option value="">-- Select Organization --</option>
                                    @foreach ($iamOrganizations as $org)
                                        <option value="{{ $org->id }}"
                                            {{ $org->id == $organization_id ? 'selected' : '' }}>
                                            {{ $org->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </li>

                        <li class="nav-item d-none d-lg-block select-organization-menu">
                            <form action="{{ route('update-organization') }}" method="POST">
                                @csrf
                                <select class="form-select" name="financial_year" id="financial_year" >
                                    <option value="">-- Select F.Y --</option>
                                    @if(isset($fyears) && is_iterable($fyears))
                                    @foreach ($fyears as $year)
                                    <option value="{{ $year['id'] }}"
                                        data-start="{{ $year['start_date'] }}"
                                        data-end="{{ $year['end_date'] }}"
                                        {{ isset($c_fyear) && $c_fyear == $year['range'] ? 'selected' : '' }}>
                                        FY {{ $year['range'] }}
                                    </option>
                                    @endforeach
                                    @endif
                                </select>
                            </form>
                        </li>
                        @endif


                        {{-- <li class="nav-item d-none d-lg-block">
                            <div class="theme-switchbox">
                                <div class="themeswitchstyle">
                                    <span class="dark-lightmode"><i data-feather="moon"></i></span>
                                    <span class="day-lightmode"><i data-feather="sun"></i></span>
                                </div>
                            </div>
                        </li> --}}

                        {{-- @include('layouts.notification.notification') --}}
                        {{-- <li class="nav-item dropdown dropdown-notification me-25"><a class="nav-link" href="#"
                                data-bs-toggle="dropdown"><i class="ficon" data-feather="bell"></i><span
                                    class="badge rounded-pill bg-danger badge-up">5</span></a>
                            <ul class="dropdown-menu dropdown-menu-media dropdown-menu-end">
                                <li class="dropdown-menu-header">
                                    <div class="dropdown-header d-flex">
                                        <h4 class="notification-title mb-0 me-auto">Notifications</h4>
                                        <div class="badge rounded-pill badge-light-primary">6 New</div>
                                    </div>
                                </li>
                                <li class="scrollable-container media-list"><a class="d-flex" href="#">
                                        <div class="list-item d-flex align-items-start">
                                            <div class="me-1">
                                                <div class="avatar"><img
                                                        src="{{url('app-assets/images/portrait/small/avatar-s-15.jpg')}}"
                                                        alt="avatar" width="32" height="32"></div>
                                            </div>
                                            <div class="list-item-body flex-grow-1">
                                                <p class="media-heading"><span class="fw-bolder">Congratulation Sam
                                                        🎉</span>winner!</p><small class="notification-text"> Won the
                                                    monthly best seller badge.</small>
                                            </div>
                                        </div>
                                    </a><a class="d-flex" href="#">
                                        <div class="list-item d-flex align-items-start">
                                            <div class="me-1">
                                                <div class="avatar"><img
                                                        src="{{url('app-assets/images/portrait/small/avatar-s-3.jpg')}}"
                                                        alt="avatar" width="32" height="32"></div>
                                            </div>
                                            <div class="list-item-body flex-grow-1">
                                                <p class="media-heading"><span class="fw-bolder">New
                                                        message</span>&nbsp;received</p><small
                                                    class="notification-text"> You have 10 unread messages</small>
                                            </div>
                                        </div>
                                    </a><a class="d-flex" href="#">
                                        <div class="list-item d-flex align-items-start">
                                            <div class="me-1">
                                                <div class="avatar bg-light-danger">
                                                    <div class="avatar-content">MD</div>
                                                </div>
                                            </div>

                                            <div class="list-item-body flex-grow-1">
                                                <p class="media-heading"><span class="fw-bolder">Revised Order
                                                        👋</span>&nbsp;checkout</p><small class="notification-text"> MD
                                                    Inc. order updated</small>
                                            </div>
                                        </div>
                                    </a>
                                    <div class="list-item d-flex align-items-center">
                                        <h6 class="fw-bolder me-auto mb-0">System Notifications</h6>
                                        <div class="form-check form-check-primary form-switch">
                                            <input class="form-check-input" id="systemNotification" type="checkbox"
                                                checked="">
                                            <label class="form-check-label" for="systemNotification"></label>
                                        </div>
                                    </div><a class="d-flex" href="#">
                                        <div class="list-item d-flex align-items-start">
                                            <div class="me-1">
                                                <div class="avatar bg-light-danger">
                                                    <div class="avatar-content"><i class="avatar-icon"
                                                            data-feather="x"></i></div>
                                                </div>
                                            </div>
                                            <div class="list-item-body flex-grow-1">
                                                <p class="media-heading"><span class="fw-bolder">Server
                                                        down</span>&nbsp;registered</p><small class="notification-text">
                                                    USA Server is down due to high CPU usage</small>
                                            </div>
                                        </div>
                                    </a><a class="d-flex" href="#">
                                        <div class="list-item d-flex align-items-start">
                                            <div class="me-1">
                                                <div class="avatar bg-light-success">
                                                    <div class="avatar-content"><i class="avatar-icon"
                                                            data-feather="check"></i></div>
                                                </div>
                                            </div>
                                            <div class="list-item-body flex-grow-1">
                                                <p class="media-heading"><span class="fw-bolder">Sales
                                                        report</span>&nbsp;generated</p><small
                                                    class="notification-text"> Last month sales report generated</small>
                                            </div>
                                        </div>
                                    </a><a class="d-flex" href="#">
                                        <div class="list-item d-flex align-items-start">
                                            <div class="me-1">
                                                <div class="avatar bg-light-warning">
                                                    <div class="avatar-content"><i class="avatar-icon"
                                                            data-feather="alert-triangle"></i></div>
                                                </div>
                                            </div>
                                            <div class="list-item-body flex-grow-1">
                                                <p class="media-heading"><span class="fw-bolder">High
                                                        memory</span>&nbsp;usage</p><small class="notification-text">
                                                    BLR Server using high memory</small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li class="dropdown-menu-footer"><a class="btn btn-primary w-100" href="#">Read all
                                        notifications</a></li>
                            </ul>
                        </li> --}}
                        <li class="nav-item dropdown dropdown-user">
                            <a class="nav-link dropdown-toggle dropdown-user-link" id="dropdown-user" href="#"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <span class="avatar">
                                            {{$logedinUser->getInitials()}}
                                            {{-- <img class="round"
                                        src="{{ url('app-assets/images/portrait/small/avatar-s-11.jpg') }}"
                                        alt="avatar" height="32" width="32"> --}}
                                    </span>
                                </a>
                            <div class="dropdown-menu drop-newmenu dropdown-menu-end" aria-labelledby="dropdown-user">
                                {{-- <a class="dropdown-item" href="#"><i class="me-50" data-feather="user"></i>
                                    Profile</a>
                                <a class="dropdown-item" href="#"><i class="me-50"
                                        data-feather="credit-card"></i>
                                    Visiting Card</a>
                                <a class="dropdown-item" href="#"><i class="me-50" data-feather="log-in"></i>
                                    Request</a>
                                <a class="dropdown-item" href="#"><i class="me-50"
                                        data-feather="check-circle"></i>
                                    Approval</a>
                                <a class="dropdown-item" href="#"><i class="me-50" data-feather="tool"></i>
                                    Setting</a> --}}
                                <a class="dropdown-item" href="{{ env("AUTH_URL", "") }}logout" ><i
                                        class="me-50" data-feather="power"></i> Logout</a>


                            </div>
                        </li>
                        {{-- <li class="nav-item dropdown dropdown-notification">
                            <a class="nav-link d-inline-block drivebtnsect" href="#" data-bs-toggle="dropdown">
                                <img src="{{ url('/img/menuiconlist.png') }}" />
                            </a>
                            <ul class="dropdown-menu dropdown-menu-media dropdown-menu-end worksdrivebox">
                                <li class="dropdown-menu-header">
                                    <div class="dropdown-header text-center">
                                        <h4 class="notification-title mb-0 me-auto">Quick Links</h4>
                                    </div>
                                </li>
                                <li class="scrollable-container media-list">
                                    <div class="row">
                                        <div class="col-md-4 col-6">
                                            <a href="#">
                                                <div class="drivework">
                                                    <img src="{{ url('/img/d4.png') }}" />
                                                    <p>Gmail</p>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-4 col-6">
                                            <a href="#">
                                                <div class="drivework">
                                                    <img src="{{ url('/img/d3.png') }}" />
                                                    <p>Outlook</p>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-4 col-6">
                                            <a href="#">
                                                <div class="drivework">
                                                    <img src="{{ url('/img/d2.png') }}" />
                                                    <p>Google Drive</p>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-4 col-6">
                                            <a href="#">
                                                <div class="drivework">
                                                    <img src="{{ url('/img/d1.png') }}" />
                                                    <p>Whatsapp</p>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-4 col-6">
                                            <a href="#">
                                                <div class="drivework">
                                                    <img src="{{ url('/img/d4.png') }}" />
                                                    <p>Gmail</p>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-4 col-6">
                                            <a href="#">
                                                <div class="drivework">
                                                    <img src="{{ url('/img/d3.png') }}" />
                                                    <p>Outlook</p>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-4 col-6">
                                            <a href="#">
                                                <div class="drivework">
                                                    <img src="{{ url('/img/d2.png') }}" />
                                                    <p>Google Drive</p>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-4 col-6">
                                            <a href="#">
                                                <div class="drivework">
                                                    <img src="{{ url('/img/d1.png') }}" />
                                                    <p>Whatsapp</p>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-4 col-6">
                                            <a href="#">
                                                <div class="drivework">
                                                    <img src="{{ url('/img/d4.png') }}" />
                                                    <p>Gmail</p>
                                                </div>
                                            </a>
                                        </div>

                                    </div>
                                </li>
                            </ul>
                        </li> --}}
                    </ul>
                </div>
            </div>
        </div>

    </div>
</nav>
<ul class="main-search-list-defaultlist d-none">
    <li class="d-flex align-items-center"><a href="#">
            <h6 class="section-label mt-75 mb-0">Files</h6>
        </a></li>
    <li class="auto-suggestion"><a class="d-flex align-items-center justify-content-between w-100"
            href="app-file-manager.html">
            <div class="d-flex">
                <div class="me-75"><img src="{{ url('/app-assets/images/icons/xls.png') }}" alt="png"
                        height="32">
                </div>
                <div class="search-data">
                    <p class="search-data-title mb-0">Two new item submitted</p><small class="text-muted">Marketing
                        Manager</small>
                </div>
            </div><small class="search-data-size me-50 text-muted">&apos;17kb</small>
        </a></li>
    <li class="auto-suggestion"><a class="d-flex align-items-center justify-content-between w-100"
            href="app-file-manager.html">
            <div class="d-flex">
                <div class="me-75"><img src="{{ url('/app-assets/images/icons/jpg.png') }}" alt="png"
                        height="32">
                </div>
                <div class="search-data">
                    <p class="search-data-title mb-0">52 JPG file Generated</p><small class="text-muted">FontEnd
                        Developer</small>
                </div>
            </div><small class="search-data-size me-50 text-muted">&apos;11kb</small>
        </a></li>
    <li class="auto-suggestion"><a class="d-flex align-items-center justify-content-between w-100"
            href="app-file-manager.html">
            <div class="d-flex">
                <div class="me-75"><img src="{{ url('/app-assets/images/icons/pdf.png') }}" alt="png"
                        height="32">
                </div>
                <div class="search-data">
                    <p class="search-data-title mb-0">25 PDF File Uploaded</p><small class="text-muted">Digital
                        Marketing Manager</small>
                </div>
            </div><small class="search-data-size me-50 text-muted">&apos;150kb</small>
        </a></li>
    <li class="auto-suggestion"><a class="d-flex align-items-center justify-content-between w-100"
            href="app-file-manager.html">
            <div class="d-flex">
                <div class="me-75"><img src="{{ url('/app-assets/images/icons/doc.png') }}" alt="png"
                        height="32">
                </div>
                <div class="search-data">
                    <p class="search-data-title mb-0">Anna_Strong.doc</p><small class="text-muted">Web
                        Designer</small>
                </div>
            </div><small class="search-data-size me-50 text-muted">&apos;256kb</small>
        </a></li>
    <li class="d-flex align-items-center"><a href="#">
            <h6 class="section-label mt-75 mb-0">Members</h6>
        </a></li>
    <li class="auto-suggestion"><a class="d-flex align-items-center justify-content-between py-50 w-100"
            href="app-user-view-account.html">
            <div class="d-flex align-items-center">
                <div class="avatar me-75"><img src="{{ url('/app-assets/images/portrait/small/avatar-s-8.jpg') }}"
                        alt="png" height="32"></div>
                <div class="search-data">
                    <p class="search-data-title mb-0">John Doe</p><small class="text-muted">UI designer</small>
                </div>
            </div>
        </a></li>
    <li class="auto-suggestion"><a class="d-flex align-items-center justify-content-between py-50 w-100"
            href="app-user-view-account.html">
            <div class="d-flex align-items-center">
                <div class="avatar me-75"><img src="{{ url('/app-assets/images/portrait/small/avatar-s-1.jpg') }}"
                        alt="png" height="32"></div>
                <div class="search-data">
                    <p class="search-data-title mb-0">Michal Clark</p><small class="text-muted">FontEnd
                        Developer</small>
                </div>
            </div>
        </a></li>
    <li class="auto-suggestion"><a class="d-flex align-items-center justify-content-between py-50 w-100"
            href="app-user-view-account.html">
            <div class="d-flex align-items-center">
                <div class="avatar me-75"><img src="{{ url('/app-assets/images/portrait/small/avatar-s-14.jpg') }}"
                        alt="png" height="32"></div>
                <div class="search-data">
                    <p class="search-data-title mb-0">Milena Gibson</p><small class="text-muted">Digital Marketing
                        Manager</small>
                </div>
            </div>
        </a></li>
    <li class="auto-suggestion"><a class="d-flex align-items-center justify-content-between py-50 w-100"
            href="app-user-view-account.html">
            <div class="d-flex align-items-center">
                <div class="avatar me-75"><img src="{{ url('/app-assets/images/portrait/small/avatar-s-6.jpg') }}"
                        alt="png" height="32"></div>
                <div class="search-data">
                    <p class="search-data-title mb-0">Anna Strong</p><small class="text-muted">Web Designer</small>
                </div>
            </div>
        </a></li>
</ul>
<ul class="main-search-list-defaultlist-other-list d-none">
    <li class="auto-suggestion justify-content-between"><a
            class="d-flex align-items-center justify-content-between w-100 py-50">
            <div class="d-flex justify-content-start"><span class="me-75"
                    data-feather="alert-circle"></span><span>No
                    results found.</span></div>
        </a></li>
</ul>
<!-- END: Header-->
<script>
    function sendFySession(startDate, endDate, id) {
    fetch("{{ route('store.fy.session') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            start_date: startDate,
            end_date: endDate,
            fyearId: id,
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Session updated:', data.message);
        location.reload();
    })
    .catch(error => {
        console.error('Error setting session:', error);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('financial_year');
    let previousIndex = select.selectedIndex;

    select.addEventListener('change', function (event) {
        const newIndex = this.selectedIndex;
        const selected = this.options[newIndex];
        const previousFY = this.options[previousIndex].textContent.trim().replace(/^FY\s*/, '');
        const newFY = selected?.textContent.trim().replace(/^FY\s*/, '');

        const id = selected.value;
        const start = selected.getAttribute('data-start');
        const end = selected.getAttribute('data-end');

        // Immediately revert selection before async dialog
        this.selectedIndex = previousIndex;

        if (id.trim() !== "") {
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to switch F.Y. from "${previousFY}" to "${newFY}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, switch it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If confirmed, update index and trigger form submission or action
                    previousIndex = newIndex;
                    select.selectedIndex = newIndex;

                    if (start && end && id !== "") {
                        sendFySession(start, end, id);
                    }
                }
            });
        }
    });
});


</script>


