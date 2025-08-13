@extends('layouts.app')

@section('content')
<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <form class="ajax-input-form" method="POST" action="{{ route('warehouse-item-mapping.store') }}" data-redirect="/warehouse-item-mappings" enctype="multipart/form-data">
        @csrf
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">
                                    Structure Item Mapping
                                </h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('/') }}">Home</a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('warehouse-item-mapping.index') }}">
                                                Structure Item Mapping
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
                                                        Location  <span class="text-danger">*</span>
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
                                                        Warehouse  <span class="text-danger">*</span>
                                                    </label>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="position-relative">
                                                        <select class="form-select select2 sub_store_id sub_store" name="sub_store_id" onchange="getDetails(this.value)">
                                                            <option value="">Select Warehouse</option>
                                                        </select>
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
                            <div class="card">
								<div class="card-body customernewsection-form">
                                    <div class="border-bottom mb-2 pb-25">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="newheader ">
                                                    <h4 class="card-title text-theme">Structure Item Mapping Detail</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-sm-end">
                                                <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50 deleteBtn">
                                                    <i data-feather="x-circle"></i> Delete
                                                </a>
                                                <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary addNewItemBtn">
                                                    <i data-feather="plus"></i> Add New
                                                </a>
                                                <input type="hidden" name="module_type" value="create">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="table-responsive pomrnheadtffotsticky">
                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                    <thead>
                                                        <tr>
                                                            <th width="50px" class="customernewsection-form">
                                                                <div class="form-check form-check-primary custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input" id="Email">
                                                                    <label class="form-check-label" for="Email"></label>
                                                                </div>
                                                            </th>
                                                            <th class="text-center">
                                                                <b>Category</b>
                                                            </th>
                                                            {{-- <th class="text-center">
                                                                <b>Sub Category</b>
                                                            </th> --}}
                                                            <th class="text-center">
                                                                <b>Item</b>
                                                            </th>
                                                            <th class="text-center">
                                                                <b>Structure Hierarchy</b>
                                                            </th>
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
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </form>
</div>
@endsection
@section('scripts')
    <script type="text/javascript" src="{{asset('assets/js/modules/witems.js')}}"></script>
    <script>
        /*Change Level*/
        function checkItems(id) {
            $(".mrntableselectexcel").empty();
        }
    </script>
@endsection
