<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0,minimal-ui">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Presence 360')</title>
    {{-- <link rel="apple-touch-icon" href="{{url('/app-assets/images/ico/apple-icon-120.png')}}"> --}}
    <link rel="shortcut icon" type="image/x-icon" href="{{url('/assets/css/favicon.png')}}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600;700"
        rel="stylesheet">
    <!-- <script src = "{{asset('app-assets/js/common-script.js')}}"> </script> -->
    @include('layouts.partials.css')
    @yield('styles')
</head>
@php
$sessionVendorId = request()->cookie('vendor_id');
@endphp
<!-- END: Head-->
<!-- BEGIN: Body-->
<body class="vertical-layout vertical-menu-modern  navbar-floating footer-static  menu-collapsed" data-open="click"
    data-menu="vertical-menu-modern" data-col="">
    {{-- @include('layouts.partials.header') --}}
    <!-- BEGIN: Header-->
<nav class="header-navbar navbar navbar-expand-lg align-items-center floating-nav d-block container-xxl erpnewheader">
    <div class="d-flex justify-content-between align-items-center">
        <div class="w-100">
            <div class="header-navbar navbar-light navbar-shadow new-navbarfloating">
                <div class="navbar-container d-flex content">
                    <div class="bookmark-wrapper d-flex align-items-center">
                        <ul class="nav navbar-nav headerlogo">
                            @if (@$orgLogo)
                                <li> <img src="{{$orgLogo}}" /></li>
                            @endif
                        </ul>
                        <ul class="nav navbar-nav left-baricontop">
                            <li class="nav-item">
                                <a class="nav-link menu-toggle" href="#">
                                    <i></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <ul class="nav navbar-nav align-items-center ms-auto">
                        @if(auth()->user()?->auth_user?->vendor_portals->count())
                        <li class="nav-item d-none d-lg-block select-organization-menu">
                            <select class="form-select" name="vendor_id" id="vendor_id">
                                {{-- <option value="">-- Select Vendor --</option> --}}
                                @foreach (auth()->user()?->auth_user?->vendor_portals as $vendorPortal)
                                    <option value="{{ $vendorPortal?->vendor_id }}"
                                        {{ $vendorPortal?->vendor_id == $sessionVendorId ? 'selected' : '' }}>
                                        {{ $vendorPortal?->vendor?->company_name ?? $vendorPortal?->vendor?->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </li>
                        @endif
                        <li class="nav-item d-none d-lg-block">
                            <div class="theme-switchbox">
                                <div class="themeswitchstyle">
                                    <span class="dark-lightmode"><i data-feather="moon"></i></span>
                                    <span class="day-lightmode"><i data-feather="sun"></i></span>
                                </div>
                            </div>
                        </li>
                        @include('layouts.notification.notification')
                        <li class="nav-item dropdown dropdown-user">
                            <a class="nav-link dropdown-toggle dropdown-user-link" id="dropdown-user" href="#"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="avatar">
                                    {{@$logedinUser?->getInitials() ?? "NA"}}
                                </span>
                            </a>
                            <div class="dropdown-menu drop-newmenu dropdown-menu-end" aria-labelledby="dropdown-user">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                        class="me-50" data-feather="power"></i> Logout</a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
<!-- END: Header-->
    @include('layouts.partials.side-menu-supplier')
    @yield('content')
    @include('layouts.partials.footer')
    @yield('modals')
    @include('layouts.partials.js')
    @yield('scripts')
    <script>
        // Vendor on change
        $(document).on('change', 'select[id="vendor_id"]', (e) => {
            e.preventDefault();
            if(e.target.value) {
                vendorOnChange(e.target.value);
            }
        });

        function vendorOnChange(vendorId)
        {
            let actionUrl = '{{route("supplier.change.vendor")}}'+"?vendor_id="+vendorId;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if(data.status == 200) {
                        if(vendorId) {
                            location.href='{{route("supplier.dashboard")}}';
                        }
                        // alert("Vendor Changed!");
                    }
                });
            });
        }
        let sessionVendorId = @json($sessionVendorId);
        if(!sessionVendorId) {
            vendorOnChange('');
        }

    </script>
</body>
<!-- END: Body-->
</html>