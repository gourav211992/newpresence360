@extends('layouts.app')

@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <div class="d-flex align-items-center justify-content-center" style="min-height: 80vh;">
                <div class="text-center">
                    <h1 class="display-4 text-danger">Access Denied</h1>
                    <p class="lead">
                        You don’t have access to the requested service:
                        <strong>{{ $parentUrl ?? 'Unknown' }}</strong>.
                    </p>
                    <a href="{{ url()->previous() }}" class="btn btn-primary mt-3">Go Back</a>
                    <a href="{{ route('/') }}" class="btn btn-outline-secondary mt-3">Go to Dashboard</a>
                </div>
            </div>

        </div>
    </div>
@endsection
