
@extends('layouts.app')
@section('content')

<form class="ajax-input-form" method="POST" action="{{ route('subStore.update', $subStore->id) }}" data-redirect="{{ url('/sub-stores') }}">
<input type="hidden" name="sub_store_id" value="{{ $subStore->id }}">
   @csrf
    @method('PUT')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Edit Sub Location</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                                        <li class="breadcrumb-item active">Edit Sub Location</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('subStore.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                            <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                data-url="{{ route('subStore.destroy', $subStore->id) }}" 
                                data-redirect="{{ route('subStore.index') }}"
                                data-message="Are you sure you want to delete this record?">
                                <i data-feather="trash-2" class="me-50"></i> Delete
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0">
                                <i data-feather="check-circle"></i> Update
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
                                            <div class="newheader border-bottom mb-2 pb-25">
                                                <h4 class="card-title text-theme">Basic Information</h4>
                                                <p class="card-text">Fill the details</p>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="name" id="sub_store_name"  class="form-control" value="{{ $subStore->name }}" {{ $isSubStoreReferenced ? 'readonly' : '' }} />
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Alias<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="code" id="sub_store_code" class="form-control" value="{{ $subStore->code }}" {{ $isSubStoreReferenced ? 'readonly' : '' }}/>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Type<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select name="store_location_type" class="form-select select2" id="store-location-type" oninput = "typeChange(this);">
                                                        @foreach ($storeLocationType as $option)
                                                            <option value="{{ $option }}" 
                                                                {{$subStore->type == $option ? 'selected' : '' }}>
                                                                {{ ucfirst($option) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('store_location_type')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div> 
                                            <div class="row align-items-center mb-1 d-none" id = "stock-store-header">
                                                <div class="col-md-3">
                                                    <label class="form-label">Sub Type(s)<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select name="stock_store_types" class="form-select select2" id = "stock-store-type" oninput = "subTypeChange(this);">
                                                        @foreach ($stockStoreTypes as $stockStoreTypeVal => $stockStoreTypeLabel)
                                                            <option value="{{ $stockStoreTypeVal }}" {{$stockStoreTypeVal == $selectedStockStoreType ? 'selected' : ''}}>
                                                                {{ ($stockStoreTypeLabel) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1 d-none" id = "station_wise_consumption_header">
                                                <div class="col-md-3">
                                                    <label class="form-label"> Station Wise Consumption <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="checkbox" {{$subStore -> station_wise_consumption === 'yes' ? 'checked' : ''}} name="station_wise_consumption" id="station_wise_consumption_input" />
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1 d-none" id = "is_warehouse_required_header">
                                                <div class="col-md-3">
                                                    <label class="form-label"> Is Warehouse <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="checkbox" {{$subStore -> is_warehouse_required ? 'checked' : ''}} name="is_warehouse_required" id="is_warehouse_required_input" />
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1" id = "uic_scan_for_issue_header">
                                                <div class="col-md-3">
                                                    <label class="form-label">Enforce UIC Scan while Issuing<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="checkbox" {{$subStore -> uic_scan_for_issue == 'yes' ? 'checked' : ''}} name="uic_scan_for_issue" id="uic_scan_for_issue_input" />
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Parent Location(s)<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select name="store_id[]" id="store_id" class="form-select select2" multiple>
                                                        <option value="">Select</option>
                                                        @foreach($stores as $store)
                                                        <option value="{{ $store->id }}" {{in_array($store->id, $selectedStoreIds) ? 'selected' : ''}}>
                                                                {{ $store->store_name }}
                                                            </option>                                                            
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>   
                                        </div>

                                        <div class="col-md-3 border-start">
                                            <div class="row align-items-center mb-2">
                                                <div class="col-md-12"> 
                                                    <label class="form-label text-primary"><strong>Status</strong></label>   
                                                    <div class="demo-inline-spacing">
                                                        @foreach ($status as $option)
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input
                                                                    type="radio"
                                                                    id="status_{{ strtolower($option) }}"
                                                                    name="status"
                                                                    value="{{ $option }}"
                                                                    class="form-check-input"
                                                                    {{ $subStore->status == $option ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder" for="status_{{ strtolower($option) }}">
                                                                    {{ ucfirst($option) }}
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
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</form>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();  
                $(this).val(value); 
            });
        }
        applyCapsLock();
    });
    function typeChange(element)
    {
        let stationWiseFieldElement = document.getElementById('station_wise_consumption_header');
        let stationWiseFieldInput = document.getElementById('station_wise_consumption_input');
        let warehouseFieldElement = document.getElementById('is_warehouse_required_header');
        let warehouseFieldInput = document.getElementById('is_warehouse_required_input');
        let uicScanForIssueFieldElement = document.getElementById('uic_scan_for_issue_header');
        let uicScanForIssueFieldInput = document.getElementById('uic_scan_for_issue_input');

        let stockStoreTypeElement = document.getElementById('stock-store-header');
        if (element.value === "{{App\Helpers\ConstantHelper::SHOP_FLOOR}}") {
            stationWiseFieldElement.classList.remove('d-none');
        } else {
            stationWiseFieldInput.checked = false;
            stationWiseFieldElement.classList.add('d-none');
        }
        if (element.value === "{{App\Helpers\ConstantHelper::STOCKK}}") {
            warehouseFieldElement.classList.remove('d-none');
            uicScanForIssueFieldElement.classList.remove('d-none');
            stockStoreTypeElement.classList.remove('d-none');
        } else {
            warehouseFieldInput.checked = false;
            uicScanForIssueFieldInput.checked = false;
            uicScanForIssueFieldElement.classList.add('d-none');
            warehouseFieldElement.classList.add('d-none');
            stockStoreTypeElement.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        typeChange(document.getElementById('store-location-type'));
        subTypeChange(document.getElementById('stock-store-type'));
    });

    function subTypeChange(element)
    {
        let warehouseFieldElement = document.getElementById('is_warehouse_required_header');
        let warehouseFieldInput = document.getElementById('is_warehouse_required_input');

        if (element.value === "{{App\Helpers\SubStore\Constants::MAIN_STORE_VALUE}}") {
            warehouseFieldElement.classList.remove('d-none');
        } else {
            warehouseFieldElement.classList.add('d-none');
            warehouseFieldInput.checked = false;
        }
    }
 </script>
@endsection
