<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<!-- BEGIN: Head-->

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0,minimal-ui">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name='robots' content='noindex, nofollow' />
    <meta name="google-site-verification" content="N5En2PPju8H1whZHL5CNKb4sG-LFoZkUgpp3H1AwNzY" />

    <title>@yield('title', 'Presence 360')</title>


    {{-- <link rel="apple-touch-icon" href="{{ url('/app-assets/images/ico/apple-icon-120.png') }}"> --}}
    <link rel="shortcut icon" type="image/x-icon" href="{{ url('/assets/css/favicon.png') }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600;700"
        rel="stylesheet">

    <!-- BEGIN: CSS-->
    @include('recruitment.layouts.css')
    @yield('style')
    <!-- END: CSS-->

</head>
<!-- END: Head-->

<!-- BEGIN: Body-->

<body class="vertical-layout vertical-menu-modern navbar-floating footer-static menu-collapsed" data-open="click"
    data-menu="vertical-menu-modern" data-col="">
    {{-- @include('recuritment.layouts.navbar') --}}
    @include('layouts.partials.header')

    @include('layouts.partials.v2.left-sidebar')
    {{-- @include('recruitment.partials.side-menu') --}}


    @yield('content')

    @include('layouts.partials.footer')

    @yield('modals')

    @include('recruitment.layouts.js')

    @yield('scripts')
</body>

</html>
