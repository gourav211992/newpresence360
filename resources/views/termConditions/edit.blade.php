@extends('layouts.app')

@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('terms.update', $term->id) }}" data-redirect="{{ url('/terms-conditions') }}">
    @csrf
    @method('PUT')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Edit Terms and Conditions</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('terms.index') }}">Home</a></li>
                                        <li class="breadcrumb-item active">Edit</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                    <a href="{{ route('terms.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                        <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                data-url="{{ route('terms.destroy', $term->id) }}" 
                                data-redirect="{{ route('terms.index') }}"
                                data-message="Are you sure you want to delete this term?">
                            <i data-feather="trash-2" class="me-50"></i> Delete
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Update</button>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <!-- Input for Term Name -->
                                    <div class="row align-items-center mb-1">
                                        <div class="col-md-3">
                                            <label class="form-label">Term Name<span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" name="term_name" class="form-control" value="{{ isset($term->term_name) ? $term->term_name : '' }}" placeholder="Enter Term Name"  />
                                            @error('term_name')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Input for Term Detail -->
                                    <div class="row align-items-center mb-1">
                                        <div class="col-md-12">
                                            <label class="form-label">Term Detail<span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-md-12">
                                            <textarea name="term_detail" id="summernote" class="form-control" placeholder="Enter Term Detail" >{{ isset($term->term_detail) ? $term->term_detail : '' }}</textarea>
                                            @error('term_detail')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Input for Status -->
                                    <div class="row align-items-center mb-1">
                                        <div class="col-md-3">
                                            <label class="form-label">Status</label>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="demo-inline-spacing">
                                                @foreach ($statuses as $statusOption)
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input
                                                            type="radio"
                                                            id="status_{{ $statusOption }}"
                                                            name="status"
                                                            value="{{ $statusOption }}"
                                                            class="form-check-input"
                                                            {{ $statusOption == $term->status ? 'checked' : '' }}
                                                        >
                                                        <label class="form-check-label fw-bolder" for="status_{{ $statusOption }}">
                                                            {{ ucfirst($statusOption) }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('status')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</form>
<!-- END: Content-->
@endsection
