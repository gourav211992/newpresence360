@extends('layouts.app')

@section('content')
<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <form class="ajax-input-form" method="POST" action="{{ route('warehouse-structure.store') }}" data-redirect="/warehouse-structures" enctype="multipart/form-data">
        @csrf
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Add Structure</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('/') }}">Home</a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('warehouse-structure.index') }}">
                                                Warehouse Structure
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active">
                                            Add New
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <input type="hidden" name="document_status" value="draft" id="document_status">
                            <a href="javascript: history.go(-1)" class="btn btn-secondary btn-sm">
                                <i data-feather="arrow-left-circle"></i> Back
                            </a>
                            <button type="button" class="btn btn-primary btn-sm submit-button" id="submit-button" name="action" value="submitted">
                                <i data-feather="check-circle"></i> Submit
                            </button>
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
                                            <div class="newheader  border-bottom mb-2 pb-25"> 
                                                <h4 class="card-title text-theme">Basic Information</h4>
                                                <p class="card-text">Fill the details</p> 
                                            </div>
                                        </div> 
                                        <div class="col-md-9"> 
                                            <div class="row align-items-center mb-1"> 
                                                <div class="col-md-3"> 
                                                    <label class="form-label">
                                                        Select Location  <span class="text-danger">*</span>
                                                    </label>  
                                                </div> 
                                                <div class="col-md-5">  
                                                    <div class="position-relative">
                                                        <select class="form-select select2" name="store_id" onchange="getSubStores(this.value)">
                                                            <option value="">Select Location</option>
                                                            @foreach($stores as $store)
                                                                <option value="{{ $store->id }}">
                                                                    {{$store->store_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1"> 
                                                <div class="col-md-3"> 
                                                    <label class="form-label">
                                                        Select Warehouse  <span class="text-danger">*</span>
                                                    </label>  
                                                </div> 
                                                <div class="col-md-5">  
                                                    <div class="position-relative">
                                                        <select class="form-select select2 sub_store" name="sub_store_id">
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="levelContainer" class="levelContainer">
                                                <div class="approvlevelflow row align-items-center mb-1" data-index="1">
                                                    <div class="col-md-3"> 
                                                        <label class="form-label">Level 1 <span class="text-danger">*</span></label>  
                                                    </div>
                                                    <div class="col-md-5">  
                                                        <input type="text" class="form-control mw-100" name="levels[1][name]">
                                                        <input type="hidden" class="form-control mw-100" name="levels[1][level]" value="1">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <a href="#" class="text-primary addLevel" data-index="1">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle">
                                                                <circle cx="12" cy="12" r="10"></circle>
                                                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                                                <line x1="8" y1="12" x2="16" y2="12"></line>
                                                            </svg>
                                                        </a>
                                                        <a href="#" class="text-danger deleteLevel" data-index="1" style="display: none;">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                                                                <circle cx="12" cy="12" r="10"></circle>
                                                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                                                <line x1="9" y1="9" x2="15" y2="15"></line>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 border-start">
                                            <div class="row align-items-center mb-2">
                                                <div class="col-md-12"> 
                                                    <label class="form-label text-primary">
                                                        <strong>Status</strong>
                                                    </label>   
                                                    <div class="demo-inline-spacing">
                                                        @foreach ($status as $statusOption)
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input
                                                                    type="radio"
                                                                    id="status_{{ $statusOption }}"
                                                                    name="status"
                                                                    value="{{ $statusOption }}"
                                                                    class="form-check-input"
                                                                    {{ $statusOption === 'active' ? 'checked' : '' }}
                                                                >
                                                                <label class="form-check-label fw-bolder" for="status_{{ $statusOption }}">
                                                                    {{ ucfirst($statusOption) }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div> 
                                                </div> 
                                            </div>  
                                        </div>
                                    </div>
                                </div>        
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </form>
</div>
@endsection
@section('scripts')
    <script type="text/javascript" src="{{asset('assets/js/modules/ws.js')}}"></script>
@endsection
