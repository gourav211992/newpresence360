@extends('layouts.app')

@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('documents.update', $document->id) }}"  data-redirect="{{ url('/erp-document') }}">
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
                                <h2 class="content-header-title float-start mb-0">Edit Document</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Home</a></li>
                                        <li class="breadcrumb-item active">Edit</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
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
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Service<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <select name="service" class="form-control">
                                                    <option value="">Select Service</option>
                                                    <option value="finance" {{ (isset($document->service) && $document->service == 'finance') ? 'selected' : '' }}>Finance</option>
                                                    <option value="land" {{ (isset($document->service) && $document->service == 'land') ? 'selected' : '' }}>Land</option>
                                                    <option value="loan" {{ (isset($document->service) && $document->service == 'loan') ? 'selected' : '' }}>Loan</option>
                                                </select>

                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Name<span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" name="name" class="form-control" value="{{$document->name ??''}}"placeholder="Enter Document Name" />
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Status</label>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="demo-inline-spacing">
                                                    @foreach ($status as $statusOption)
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input
                                                                type="radio"
                                                                id="status_{{ $statusOption }}"
                                                                name="status"
                                                                value="{{ $statusOption }}"
                                                                class="form-check-input"
                                                                {{ $statusOption == $document->status ? 'checked' : '' }}
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
