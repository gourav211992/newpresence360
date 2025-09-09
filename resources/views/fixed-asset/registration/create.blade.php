@extends('layouts.app')
@section('css')
    <style type="text/css">
        .image-uplodasection {
            position: relative;
            margin-bottom: 10px;
        }

        .fileuploadicon {
            font-size: 24px;
        }



        .delete-img {
            position: absolute;
            top: 5px;
            right: 5px;
            cursor: pointer;
        }

        .preview-image {
            max-width: 100px;
            max-height: 100px;
            display: block;
            margin-top: 10px;
        }

        .disabled-select {
            pointer-events: none;
            background-color: #e9ecef;
            color: #6c757d;
            border: 1px solid #ced4da;
        }

        .code_error {
            font-size: 12px;
        }
    </style>
@endsection

@section('content')
    @php
        $unauthorizedMonths = [];
        foreach ($fy_months as $month) {
            if (!$month['authorized']) {
                $unauthorizedMonths[] = $month['fy_month'];
            }
        }
    @endphp
    <script>
        const unauthorizedMonths = @json($unauthorizedMonths);
    </script>
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">New Asset</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Add New</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('finance.fixed-asset.registration.index') }}"> <button
                                    class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</button>
                            </a>
                            <button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" type="button" id="save-draft-btn">
                                <i data-feather="save"></i> Save as Draft
                            </button>
                            <button type="submit" form="fixed-asset-registration-form" class="btn btn-primary btn-sm"
                                id="submit-btn">
                                <i data-feather="check-circle"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">



                <section id="basic-datatable">
                    <div class="row">
                        <form id="fixed-asset-registration-form" method="POST"
                            action="{{ route('finance.fixed-asset.registration.store') }}" enctype="multipart/form-data">
                            @csrf


                            <input type="hidden" name="book_code" id="book_code_input">
                            <input type="hidden" name="doc_number_type" id="doc_number_type">
                            <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
                            <input type="hidden" name="doc_prefix" id="doc_prefix">
                            <input type="hidden" name="doc_suffix" id="doc_suffix">
                            <input type="hidden" name="doc_no" id="doc_no">
                            <input type="hidden" name="document_status" id="document_status" value="">
                            <input type="hidden" name="mrn_detail_id" id="mrn_detail_id" value="">
                            <input type="hidden" name="mrn_header_id" id="mrn_header_id" value="">
                            <input type="hidden" name="dep_type" id="depreciation_type" value="{{ $dep_type }}">
                            <input type="hidden" name="days" id="days" value="0">
                            <input type="hidden" name="prefix" id="prefix">



                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body customernewsection-form">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="newheader border-bottom mb-2 pb-25  ">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h4 class="card-title text-theme">Basic Information</h4>
                                                            <p class="card-text">Fill the details</p>
                                                        </div>




                                                    </div>
                                                </div>

                                            </div>




                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="book_id">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="book_id" id="book_id" required>
                                                            @if ($series)
                                                                @foreach ($series as $index => $ser)
                                                                    <option value="{{ $ser->id }}" {{ old('book_id') == $ser->id ? 'selected' : '' }}>
                                                                        {{ $ser->book_code }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="document_number">Doc No <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="document_number"
                                                            id="document_number" value="{{ old('document_number') }}"
                                                            readonly required>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="document_date">Doc Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">

                                                        <input type="date" class="form-control" name="document_date"
                                                            id="document_date"
                                                            value="{{ old('document_date') ?? date('Y-m-d') }}" required>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="location" class="form-select" name="location_id"
                                                            required>
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}">
                                                                    {{ $location->store_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                </div>
                                                <div class="row align-items-center mb-1 cost_center">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cost Center <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="cost_center" class="form-select" name="cost_center_id"
                                                            required>
                                                        </select>
                                                    </div>

                                                </div>


                                                <!-- <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="reference_no">Reference No.</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="reference_no"
                                                            id="reference_no" value="{{ old('reference_no') }}">
                                                    </div>
                                                </div> -->

                                                <!-- <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="reference_from">Reference From
                                                            <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-3 action-button">
                                                        <a data-bs-toggle="modal" data-bs-target="#rescdule"
                                                            class="btn btn-outline-primary btn-sm mb-0 w-100"><i
                                                                data-feather="plus-square"></i> GRN</a>
                                                    </div>
                                                </div> -->

                                                <div class="row align-items-center mb-2">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="status">Status</label>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio3" name="status"
                                                                    class="form-check-input" value="active" {{ old('status', 'active') == 'active' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio3">Active</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio4" name="status"
                                                                    class="form-check-input" value="inactive" {{ old('status') == 'inactive' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio4">Inactive</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>




                                        </div>
                                    </div>
                                </div>




                                <div class="row customernewsection-form">
                                    <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">Asset Details</h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Category <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" name="category_id"
                                                                id="category" required>
                                                                <option value="" {{ old('category') ? '' : 'selected' }}>
                                                                    Select</option>
                                                                @foreach ($categories as $category)
                                                                    <option value="{{ $category->id }}" {{ old('category') == $category->id ? 'selected' : '' }}>
                                                                        {{ $category->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">IT Act Category <span
                                                                    class="text-danger"></span></label>
                                                            <select class="form-select select2" name="it_category_id"
                                                                id="it_category">
                                                                <option value="" {{ old('it_category') ? '' : 'selected' }}>
                                                                    Select</option>
                                                                @foreach ($it_categories as $it_category)
                                                                    <option value="{{ $it_category->id }}" {{ old('it_category') == $it_category->id ? 'selected' : '' }}>
                                                                        {{ $it_category->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Asset Name <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="asset_name"
                                                                id="asset_name" value="{{ old('asset_name') }}" required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Asset Code <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="asset_code"
                                                                id="asset_code" value="{{ old('asset_code') }}"
                                                                oninput="this.value = this.value.toUpperCase();" required />
                                                            <span class="text-danger code_error" style="font-size:12px"
                                                                style="font-size:12px"></span>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Current Value <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" required
                                                                name="current_value" id="current_value"
                                                                value="{{ old('current_value') }}" oninput="updateDepreciationValues()" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Quantity <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="quantity"
                                                                id="quantity" value="{{ old('quantity') }}" oninput="updateDepreciationValues()" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Ledger <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" name="ledger_id" id="ledger"
                                                                required>
                                                                <option value="" {{ old('ledger') ? '' : 'selected' }}>
                                                                    Select</option>
                                                                @foreach ($ledgers as $ledger)
                                                                    <option value="{{ $ledger->id }}" {{ old('ledger') == $ledger->id ? 'selected' : '' }}>
                                                                        {{ $ledger->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>

                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Ledger Group <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select" name="ledger_group_id"
                                                                id="ledger_group" required>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Capitalize Date </label>
                                                            <input type="date" class="form-control" name="capitalize_date"
                                                                id="capitalize_date" value="{{ old('capitalize_date') }}"
                                                                min="{{ $financialStartDate }}"
                                                                max="{{ $financialEndDate }}" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Maint. Schedule <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select" name="maintenance_schedule"
                                                                id="maintenance_schedule" required>
                                                                <option value="" {{ old('maintenance_schedule') == '' ? 'selected' : '' }}>
                                                                    Select</option>
                                                                <option value="weekly" {{ old('maintenance_schedule') == 'Weekly' ? 'selected' : '' }}>
                                                                    Weekly</option>
                                                                <option value="monthly" {{ old('maintenance_schedule') == 'Monthly' ? 'selected' : '' }}>
                                                                    Monthly</option>
                                                                <option value="quarterly" {{ old('maintenance_schedule') == 'Quarterly' ? 'selected' : '' }}>
                                                                    Quarterly</option>
                                                                <option value="semi-annually" {{ old('maintenance_schedule') == 'Semi-Annually' ? 'selected' : '' }}>
                                                                    Semi-Annually</option>
                                                                <option value="annually" {{ old('maintenance_schedule') == 'Annually' ? 'selected' : '' }}>
                                                                    Annually</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Dep. Method <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" name="depreciation_method"
                                                                id="depreciation_method" class="form-control"
                                                                value="{{ $dep_method }}" readonly />
                                                        </div>
                                                    </div>


                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Est. Useful Life (yrs) <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" name="useful_life"
                                                                id="useful_life" value="{{ old('useful_life') }}"
                                                                oninput="updateDepreciationValues()" required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Salvage Value <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="salvage_value"
                                                                id="salvage_value" readonly
                                                                value="{{ old('salvage_value') }}" required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Dep % <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" id="depreciation_rate"
                                                                name="depreciation_percentage" readonly />
                                                            <input type="hidden" value="{{ $dep_percentage }}"
                                                                id="depreciation_percentage" />
                                                            <input type="hidden" id="depreciation_rate_year"
                                                                name="depreciation_percentage_year" />

                                                        </div>
                                                    </div>




                                                    
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Total Dep. <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" id="total_depreciation"
                                                                name="total_depreciation" class="form-control" value="0"
                                                                readonly />
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row customernewsection-form">
                                    <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader d-flex justify-content-between align-items-center">
                                                <h4 class="card-title mb-0">Item Details</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                     <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Brand Name </label>
                                                            <input type="text" class="form-control indian-number" name="brand_name"
                                                                id="brand_name"
                                                                required />
                                                        </div>
                                                    </div>
                                                      

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Model No </label>
                                                            <input type="text" class="form-control indian-number" name="model_no"
                                                                id="model_no"
                                                             required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Batch Number </label>
                                                            <input type="text" class="form-control indian-number" name="batch_number"
                                                                id="batch_number"
                                                                required />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Manufactering Year </label>
                                                            <input type="text" class="form-control" name="manufactering_year"
                                                                id="manufactering_year"
                                                                required />
                                                        </div>
                                                    </div>


                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                  </div>

                                <div class="row customernewsection-form">
                                    <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">Vendor Details</h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Vendor </label>
                                                            <select class="form-select" id="vendor" name="vendor_id" required>
                                                                <option value="">Select</option>
                                                                @foreach ($vendors as $vendor)
                                                                    <option value="{{ $vendor->id }}" data-country="{{ $vendor->currency_id }}" {{ old('vendor') == $vendor->id ? 'selected' : '' }}>
                                                                        {{ $vendor->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Currency </label>
                                                            <select class="form-select" id="currency" disabled required>
                                                                <option value="">Select</option>
                                                                @foreach ($currencies as $currency)
                                                                    <option value="{{ $currency->id }}" {{ old('currency') == $currency->id ? 'selected' : '' }}>
                                                                        {{ $currency->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <input type="hidden" name="currency_id" id="currency_id"
                                                                value="{{ old('currency') }}">

                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 d-none">
                                                        <div class="mb-1">
                                                            <label class="form-label">Supplier Invoice No. </label>
                                                            <input type="text" class="form-control"
                                                                name="supplier_invoice_no" id="supplier_invoice_no" value=""
                                                                 />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3 d-none">
                                                        <div class="mb-1">
                                                            <label class="form-label">Supplier Invoice Date </label>
                                                            <input type="date" class="form-control"
                                                                name="supplier_invoice_date" id="supplier_invoice_date"
                                                                 />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Sub Total </label>
                                                            <input type="text" class="form-control" name="sub_total"
                                                                id="sub_total" value="" required />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label w-100">Tax 
                                                                <input type="text" class="form-control" name="tax_amount"
                                                                id="tax_amount" value="" required />
                                                            </label>
                                                            
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Purchase Amt </label>
                                                            <input type="text" class="form-control" name="purchase_amount"
                                                                id="purchase_amount" value="" required readonly />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Book Date </label>
                                                            <input type="date" class="form-control" name="book_date"
                                                                id="book_date" required />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                            </div>
                        </form>
                    </div>
                    <!-- Modal to add new record -->

                </section>


            </div>
        </div>
    </div> <!-- END: Content-->

    <div class="modal fade text-start alertbackdropreadonly" id="amendmentconfirm" tabindex="-1"
        aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>MRN</strong>? After Amendment this
                        action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select Item
                        </h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">GRN No. </label>
                                <select class="form-select filter" name="grn_no" id="grn_no">
                                    <option value="">Select</option>
                                    @foreach ($grns->unique('document_number') as $grn)
                                        <option value="{{ $grn->document_number }}">{{ $grn->document_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Code </label>
                                <select class="form-select filter" name="vendor_code" id="vendor_code">
                                    <option value="">Select</option>
                                    @foreach ($grns->unique('vendor_code') as $grn)
                                        <option value="{{ $grn->vendor_code }}">{{ $grn->vendor_code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Vendor Name </label>
                                <select class="form-select filter" id="vendor_name" name="vendor_name">
                                    <option value="">Select</option>
                                    @foreach ($grns->unique('vendor_id') as $grn)
                                        <option value="{{ $grn->vendor->company_name }}">{{ $grn->vendor->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Item Name </label>
                                <select class="form-select filter" id="item_name" name="item_name">
                                    <option value="">Select</option>
                                    @foreach ($grn_details->unique('item_id') as $item)
                                        <option value="{{ $item->item->id }}">{{ $item->item->item_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col  mb-1">
                            <label class="form-label">&nbsp;</label><br />
                            <button class="btn btn-warning btn-sm" id="searchButton"><i data-feather="search"></i>
                                Search</button>
                        </div>

                        <div class="col-md-12">


                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table id="grn_table"
                                    class="mt-1 table myrequesttablecbox table-striped po-order-detail table-hover">
                                    <thead class="sticky-top bg-white">
                                        <tr>
                                            <th>
                                            </th>
                                            <th>GRN No.</th>
                                            <th>GRN Date</th>
                                            <th>Vendor Code</th>
                                            <th>Vendor Name</th>
                                            <th>Item</th>
                                            <th>Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>


                                </table>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i>
                        Cancel</button>
                    <button id="submit_grns" class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i
                            data-feather="check-circle"></i>
                        Process</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade text-start" id="postvoucher" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Post Voucher
                        </h4>
                        <p class="mb-0">View Details</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Series <span class="text-danger">*</span></label>
                                <input class="form-control" readonly value="VOUCH/2024" />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Voucher No <span class="text-danger">*</span></label>
                                <input class="form-control" readonly value="098" />
                            </div>
                        </div>

                        <div class="col-md-12">


                            <div class="table-responsive">
                                <table
                                    class="mt-1 table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Leadger Code</th>
                                            <th>Leadger Name</th>
                                            <th class="text-end">Debit</th>
                                            <th class="text-end">Credit</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td class="fw-bolder text-dark">2901</td>
                                            <td>Finance</td>
                                            <td class="text-end">10000</td>
                                            <td class="text-end">0</td>
                                            <td>Remarks come here...</td>
                                        </tr>

                                        <tr>
                                            <td>2</td>
                                            <td class="fw-bolder text-dark">2901</td>
                                            <td>Finance</td>
                                            <td class="text-end">0</td>
                                            <td class="text-end">10000</td>
                                            <td>Remarks come here...</td>
                                        </tr>

                                        <tr>
                                            <td colspan="3" class="fw-bolder text-dark text-end">Total</td>
                                            <td class="fw-bolder text-dark text-end">10000</td>
                                            <td class="fw-bolder text-dark text-end">10000</td>
                                            <td></td>
                                        </tr>





                                    </tbody>


                                </table>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i>
                        Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i>
                        Submit</button>
                </div>
            </div>
        </div>
    </div>



    <div class="modal fade" id="discount" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Add Discount</h1>
                    <p class="text-center">Enter the details below.</p>


                    <sdiv class="text-end"><a href="#" class="text-primary add-contactpeontxt mt-50"><i
                                data-feather='plus'></i> Add Discount</a></sdiv>

                    <div class="table-responsive-md customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th width="150px">Discount Name</th>
                                    <th>Discount Type</th>
                                    <th>Discount %</th>
                                    <th>Discount Value</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#</td>
                                    <td>
                                        <select class="form-select mw-100">
                                            <option>Select</option>
                                            <option>Discount 1</option>
                                            <option>Discount 2</option>
                                            <option>Discount 3</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100">
                                            <option>Select</option>
                                            <option>Fixed</option>
                                            <option>Percentage</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control mw-100" /></td>
                                    <td><input type="text" class="form-control mw-100" /></td>
                                    <td>
                                        <a href="#" class="text-danger"><i data-feather="trash-2"></i></a>
                                    </td>
                                </tr>


                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-dark"><strong>Total</strong></td>
                                    <td class="text-dark"><strong>1000</strong></td>
                                    <td></td>
                                </tr>


                            </tbody>


                        </table>
                    </div>

                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit-address" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Edit Address</h1>
                    <p class="text-center">Enter the details below.</p>


                    <div class="row mt-2">
                        <div class="col-md-12 mb-1">
                            <label class="form-label">Select Address <span class="text-danger">*</span></label>
                            <select class="select2 form-select">
                                <option value="AK" selected>56, Sector 44 Rd Gurugram, Haryana, Pin Code - 122022,
                                    India</option>
                                <option value="HI">Noida, U.P</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-1">
                            <label class="form-label">Country <span class="text-danger">*</span></label>
                            <select class="select2 form-select">
                                <option>Select</option>
                                <option>India</option>
                            </select>
                        </div>


                        <div class="col-md-6 mb-1">
                            <label class="form-label">State <span class="text-danger">*</span></label>
                            <select class="select2 form-select">
                                <option>Select</option>
                                <option>Gautam Budh Nagar</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-1">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <select class="select2 form-select">
                                <option>Select</option>
                                <option>Noida</option>
                            </select>
                        </div>


                        <div class="col-md-6 mb-1">
                            <label class="form-label w-100">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" value="201301" placeholder="Enter Pincode" />
                        </div>

                        <div class="col-md-12 mb-1">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control"
                                placeholder="Enter Address">56, Sector 44 Rd, Kanhai Colony, Sector 52</textarea>
                        </div>

                    </div>



                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="Remarks" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Add/Edit Remarks</h1>
                    <p class="text-center">Enter the details below.</p>


                    <div class="row mt-2">


                        <div class="col-md-12 mb-1">
                            <label class="form-label">Remarks <span class="text-danger">*</span></label>
                            <textarea class="form-control" placeholder="Enter Remarks"></textarea>
                        </div>

                    </div>



                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="expenses" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Add Expenses</h1>
                    <p class="text-center">Enter the details below.</p>

                    <div class="text-end"> <a href="#" class="text-primary add-contactpeontxt mt-50"><i
                                data-feather='plus'></i> Add Expenses</a></div>

                    <div class="table-responsive-md customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th width="150px">Expense Name</th>
                                    <th>Expense Type</th>
                                    <th>Expense %</th>
                                    <th>Expense Value</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#</td>
                                    <td>
                                        <select class="form-select mw-100">
                                            <option>Select</option>
                                            <option>Expense 1</option>
                                            <option>Expense 2</option>
                                            <option>Expense 3</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100">
                                            <option>Select</option>
                                            <option>Fixed</option>
                                            <option>Percentage</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control mw-100" /></td>
                                    <td><input type="text" class="form-control mw-100" /></td>
                                    <td>
                                        <a href="#" class="text-danger"><i data-feather="trash-2"></i></a>
                                    </td>
                                </tr>


                                <tr>
                                    <td colspan="3"></td>
                                    <td class="text-dark"><strong>Total</strong></td>
                                    <td class="text-dark"><strong>1000</strong></td>
                                    <td></td>
                                </tr>


                            </tbody>


                        </table>
                    </div>

                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="delivery" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="max-width: 900px">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Store Location</h1>
                    <p class="text-center">Enter the details below.</p>


                    <div class="text-end"><a href="#" class="text-primary add-contactpeontxt mt-50"><i
                                data-feather='plus'></i> Add Quantity</a></div>

                    <div class="table-responsive-md customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th width="80px">#</th>
                                    <th>Store</th>
                                    <th>Rack</th>
                                    <th>Shelf</th>
                                    <th>Bin</th>
                                    <th width="50px">Qty</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#</td>
                                    <td>
                                        <select class="form-select mw-100 select2">
                                            <option>Select</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2">
                                            <option>Select</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2">
                                            <option>Select</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2">
                                            <option>Select</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control mw-100" /></td>
                                    <td>
                                        <a href="#" class="text-danger"><i data-feather="trash-2"></i></a>
                                    </td>
                                </tr>


                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-dark"><strong>Total Qty</strong></td>
                                    <td class="text-dark"><strong>20</strong></td>
                                    <td></td>
                                </tr>


                            </tbody>


                        </table>
                    </div>

                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="taxdetail" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Taxes</h1>
                    <div class="table-responsive-md customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"
                            id="order_tax_main_table">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th width="150px">Tax</th>
                                    <th>Taxable Amount</th>
                                    <th>Tax %</th>
                                    <th>Tax Value</th>
                                </tr>
                            </thead>
                            <tbody id="extraAmountsTable">
                                <tr>
                                    <td>1</td>
                                    <td>IGST</td>
                                    <td class="sub_total"></td>
                                    <td id="igst_per"></td>
                                    <td id="igst_tax"></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>CGST</td>
                                    <td class="sub_total"></td>
                                    <td id="cgst_per"></td>
                                    <td id="cgst_tax"></td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>SGST</td>
                                    <td class="sub_total"></td>
                                    <td id="sgst_per"></td>
                                    <td id="sgst_tax"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="attribute" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Select Attribute</h1>
                    <p class="text-center">Enter the details below.</p>

                    <div class="table-responsive-md customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th>Attribute Name</th>
                                    <th>Attribute Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Color</td>
                                    <td>
                                        <select class="form-select select2">
                                            <option>Select</option>
                                            <option>Black</option>
                                            <option>White</option>
                                            <option>Red</option>
                                            <option>Golden</option>
                                            <option>Silver</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Size</td>
                                    <td>
                                        <select class="form-select select2">
                                            <option>Select</option>
                                            <option>5.11"</option>
                                            <option>5.10"</option>
                                            <option>5.09"</option>
                                            <option>5.00"</option>
                                            <option>6.20"</option>
                                        </select>
                                    </td>
                                </tr>





                            </tbody>


                        </table>
                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Select</button>
                </div>
            </div>
        </div>
    </div>


    @section('scripts')
        <script src="{{ url('/app-assets/js/jquery-ui.js') }}"></script>
    <script>
            document.getElementById('document_date').addEventListener('input', function() {
                if (!isDateAuthorized(this.value)) {
                    this.value = '';
                    this.focus();
                }
            });

            function getMonthName(ym) {
                // ym = '2024-07'
                const [year, month] = ym.split('-');
                const d = new Date(year, parseInt(month) - 1);
                return d.toLocaleString('default', {
                    month: 'long',
                    year: 'numeric'
                });
            }

            function isDateAuthorized(dateValue) {
                if (!dateValue) return true; // allow empty, you can tweak this logic if needed
                var selectedMonth = dateValue.substring(0, 7);
                if (unauthorizedMonths.includes(selectedMonth)) {
                    var monthLabel = getMonthName(selectedMonth);

                    Swal.fire({
                        icon: 'error',
                        title: 'Unauthorized Month',
                        text: 'You are not authorized to select dates from ' + monthLabel +
                            '. Please select another month.',
                        confirmButtonText: 'OK'
                    });

                    return false;
                }
                return true;
            }
    </script>
        <script>


            $.ajax({
                url: '{{ route('finance.fixed-asset.fetch.grn.data') }}',
                type: 'GET',
                success: function (response) {
                    $('#grn_table tbody').html(response.html);
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                }
            });
            $('#rescdule').on('show.bs.modal', function (e) {

            });


            function resetParametersDependentElements(data) {
                let backDateAllowed = false;
                let futureDateAllowed = false;

                if (data != null) {
                    console.log(data.parameters.back_date_allowed);
                    if (Array.isArray(data?.parameters?.back_date_allowed)) {
                        for (let i = 0; i < data.parameters.back_date_allowed.length; i++) {
                            if (data.parameters.back_date_allowed[i].trim().toLowerCase() === "yes") {
                                backDateAllowed = true;
                                break; // Exit the loop once we find "yes"
                            }
                        }
                    }
                    if (Array.isArray(data?.parameters?.future_date_allowed)) {
                        for (let i = 0; i < data.parameters.future_date_allowed.length; i++) {
                            if (data.parameters.future_date_allowed[i].trim().toLowerCase() === "yes") {
                                futureDateAllowed = true;
                                break; // Exit the loop once we find "yes"
                            }
                        }
                    }
                    //console.log(backDateAllowed, futureDateAllowed);

                }

                const dateInput = document.getElementById("document_date");

                // Determine the max and min values for the date input
                const today = moment().format("YYYY-MM-DD");

                if (backDateAllowed && futureDateAllowed) {
                    dateInput.setAttribute("min", "{{ $financialStartDate }}");
                    dateInput.setAttribute("max", "{{ $financialEndDate }}");
                } else if (backDateAllowed) {
                    dateInput.setAttribute("max", today);
                    dateInput.setAttribute("min", "{{ $financialStartDate }}");
                } else if (futureDateAllowed) {
                    dateInput.setAttribute("min", today);
                    dateInput.setAttribute("max", "{{ $financialEndDate }}");
                } else {
                    dateInput.setAttribute("min", today);
                    dateInput.setAttribute("max", today);

                }
            }

            $('#book_id').on('change', function () {
                resetParametersDependentElements(null);
                let currentDate = new Date().toISOString().split('T')[0];
                let document_date = $('#document_date').val();
                let bookId = $('#book_id').val();
                let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId +
                    "&document_date=" + document_date;
                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if (data.status == 200) {
                            resetParametersDependentElements(data.data);
                            $("#book_code_input").val(data.data.book_code);
                            if (!data.data.doc.document_number) {
                                $("#document_number").val('');
                                $('#doc_number_type').val('');
                                $('#doc_reset_pattern').val('');
                                $('#doc_prefix').val('');
                                $('#doc_suffix').val('');
                                $('#doc_no').val('');
                            } else {
                                $("#document_number").val(data.data.doc.document_number);
                                $('#doc_number_type').val(data.data.doc.type);
                                $('#doc_reset_pattern').val(data.data.doc.reset_pattern);
                                $('#doc_prefix').val(data.data.doc.prefix);
                                $('#doc_suffix').val(data.data.doc.suffix);
                                $('#doc_no').val(data.data.doc.doc_no);
                            }
                            if (data.data.doc.type == 'Manually') {
                                $("#document_number").attr('readonly', false);
                            } else {
                                $("#document_number").attr('readonly', true);
                            }

                        }
                        if (data.status == 404) {
                            $("#document_number").val('');
                            $('#doc_number_type').val('');
                            $('#doc_reset_pattern').val('');
                            $('#doc_prefix').val('');
                            $('#doc_suffix').val('');
                            $('#doc_no').val('');
                            showToast('error', data.message);
                        }
                    });
                });
            });
            $('#book_id').trigger('change');


            document.getElementById('save-draft-btn').addEventListener('click', function () {
                $('.preloader').show();
                document.getElementById('document_status').value = 'draft';
                if (!($('#asset_code').hasClass('is-invalid'))) {
                    document.getElementById('fixed-asset-registration-form').submit();
                } else {
                    $('.preloader').hide();
                    showToast('error', 'Please correct the errors before submitting.');
                }
            });
            $('#fixed-asset-registration-form').on('submit', function (e) {
                $('.preloader').show();
                if ($(this).find('.is-invalid').length > 0) {
                    e.preventDefault(); // Prevent form submission
                    $('.preloader').hide();
                    showToast('error', 'Please correct the errors before submitting.');
                }
            });

            document.getElementById('submit-btn').addEventListener('click', function () {
                document.getElementById('document_status').value = 'submitted';
            });



            $(".mrntableselectexcel tr").click(function () {
                $(this).addClass('trselected').siblings().removeClass('trselected');
                value = $(this).find('td:first').html();
            });

            $('#ledger').change(function () {
                let groupDropdown = $('#ledger_group');
                $.ajax({
                    url: '{{ route('finance.fixed-asset.getLedgerGroups') }}',
                    method: 'GET',
                    data: {
                        ledger_id: $(this).val(),
                        _token: $('meta[name="csrf-token"]').attr(
                            'content') // CSRF token
                    },
                    success: function (response) {
                        groupDropdown.empty(); // Clear previous options

                        response.forEach(item => {
                            groupDropdown.append(
                                `<option value="${item.id}">${item.name}</option>`
                            );
                        });

                    },
                    error: function () {
                        showToast('error', 'Error fetching group items.');
                    }
                });

            });


            $('#searchButton').on('click', function (e) {
                e.preventDefault();

                let grn_no = $('#grn_no').val();
                let vendor_code = $('#vendor_code').val();
                let vendor_name = $('#vendor_name').val();
                let item_name = $('#item_name').val();

                $.ajax({
                    url: "{{ route('finance.fixed-asset.fetch.grn.data') }}",
                    method: "GET",
                    data: {
                        grn_no: grn_no,
                        vendor_code: vendor_code,
                        vendor_name: vendor_name,
                        item_name: item_name
                    },
                    success: function (res) {
                        $('#grn_table tbody').html(res.html);
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                    }
                });
            });
            $('#infoBtn').hide();
            document.addEventListener('DOMContentLoaded', function () {
                const processButton = document.querySelector('#submit_grns');
                const radioButtons = document.querySelectorAll('input[name="grn_id"]');

                processButton.addEventListener('click', function (event) {
                    // Check if any radio button is selected
                    const selectedRadio = document.querySelector('input[name="grn_id"]:checked');

                    //console.log(selectedRadio);

                    if (!selectedRadio) {
                        event.preventDefault(); // Prevent further processing
                        showToast('error', 'Please select a GRN before proceeding.');
                    } else {
                        // Retrieve and log the data-grn attribute of the selected radio button
                        const grnData = selectedRadio.dataset.grn;
                        const nearestTr = selectedRadio.closest('tr'); // Find the nearest <tr>
                        if (nearestTr) {
                            const tds = nearestTr.querySelectorAll('td'); // Get all <td> elements in the row
                            if (tds.length > 1) { // Ensure there are at least two columns
                                $('#asset_name').val(tds[tds.length - 2]
                                    .textContent).trigger('keyup'); // Get the second last column
                            }
                        } // Access the data-grn attribute

                        // Make sure grnData is available
                        if (grnData) {
                            $('#mrn_detail_id').val(selectedRadio.value);
                            const parsedGrnData = JSON.parse(grnData); // Parse the JSON data
                            $('#mrn_header_id').val(parsedGrnData?.header?.id || '');
                            $('#supplier_invoice_no').val(parsedGrnData?.header?.supplier_invoice_no || '');
                            $('#quantity').val(parsedGrnData?.accepted_qty || 0); // Log the parsed data
                            $('#vendor').val(parsedGrnData?.header?.vendor?.id || '');
                            $('#currency').val(parsedGrnData?.header?.vendor?.currency_id || '');
                            $('#vendor_id').val(parsedGrnData?.header?.vendor?.id || '');
                            $('#currency_id').val(parsedGrnData?.header?.vendor?.currency_id || '');
                            $('#sub_total').val(parsedGrnData?.basic_value || 0);
                            //$('.sub_total').html(parsedGrnData?.basic_value || 0);
                            $('#tax').val(parsedGrnData?.tax_value || 0);
                            $('#purchase_amount').val(
                                (parseFloat(parsedGrnData?.tax_value || 0) + parseFloat(parsedGrnData
                                    ?.basic_value || 0)).toFixed(2)
                            );
                            $('#current_value').val(parsedGrnData?.basic_value || 0);
                            const invoiceDate = parsedGrnData?.header?.supplier_invoice_date || '';
                            const formattedInvoiceDate = invoiceDate && invoiceDate !== '0000-00-00' ?
                                invoiceDate.split('T')[0] : '';
                            $('#supplier_invoice_date').val(formattedInvoiceDate);
                            const createdAt = parsedGrnData?.created_at || '';
                            const formattedCreatedAt = createdAt && createdAt !== '0000-00-00' ? createdAt
                                .split('T')[0] : '';
                            $('#book_date').val(formattedCreatedAt);
                            // let igstData = parsedGrnData?.igst_value;
                            // let cgstData = parsedGrnData?.cgst_value;
                            // let sgstData = parsedGrnData?.sgst_value;
                            // $('#igst_per').html(parseFloat((igstData['value']/parsedGrnData?.basic_value)*100).toFixed(2) || 0);
                            // $('#cgst_per').html(parseFloat((cgstData['value']/parsedGrnData?.basic_value)*100).toFixed(2) || 0);
                            // $('#sgst_per').html(parseFloat((sgstData['value']/parsedGrnData?.basic_value)*100).toFixed(2) || 0);
                            // $('#sgst_tax').html(sgstData['value']||0);
                            // $('#cgst_tax').html(cgstData['value']||0);
                            // $('#igst_tax').html(igstData['value']||0);
                            $('#extraAmountsTable').empty();

                            // Check if taxes exist and are not empty
                            if (parsedGrnData?.taxes?.length > 0) {
                                let snno = 1;
                                parsedGrnData.taxes.forEach(item => {
                                    $('#extraAmountsTable').append(`
                                    <tr>
                                        <td>${snno}</td>
                                        <td>${item.ted_name}</td>
                                        <td class="indian-number">${parsedGrnData?.basic_value}</td>
                                        <td>${item.ted_percentage}%</td>
                                        <td class="indian-number">${item.ted_amount}</td>
                                    </tr>
                                `);
                                    snno++;
                                });

                                // Show info button
                                $('#infoBtn').show();
                            } else {
                                // Hide info button if no taxes
                                $('#infoBtn').hide();
                            }
                            updateDepreciationValues();




                        } else {
                            console.error('data-grn attribute not found on the selected radio button');
                        }
                    }
                });
            });

            function updateDepreciationValues() {
                let purchaseDate = document.getElementById("supplier_invoice_date").value;
                let depreciationType = document.getElementById("depreciation_type").value;
                let currentValue = parseFloat(document.getElementById("current_value").value) || 0;
                let depreciationPercentage = parseFloat(document.getElementById("depreciation_percentage").value) || 0;
                let usefulLife = parseFloat(document.getElementById("useful_life").value) || 0;
                let method = document.getElementById("depreciation_method").value;

                // Ensure all required values are provided
                if (!depreciationType || !currentValue || !depreciationPercentage || !usefulLife || !method) {
                    return;
                }


                // Determine financial date based on depreciation type
                let financialDate;
                let financialEnd = new Date("{{ $financialEndDate }}");


                // Extract the financial year-end month and day
                let financialEndMonth = financialEnd.getMonth();
                let financialEndDay = financialEnd.getDate();
                let devidend = 1;

                switch (depreciationType) {
                    case 'half_yearly':
                        devidend = 2; // Adjust dividend for half-yearly
                        break;

                    case 'quarterly':
                        devidend = 4; // Adjust dividend for quarterly
                        break;

                    case 'monthly':
                        devidend = 12; // Adjust dividend for monthly
                        break;

                }

                let salvageValue = (currentValue * (depreciationPercentage / 100)).toFixed(2);
                let depreciationRate = 0;
                if (method === "SLM") {
                    depreciationRate = ((((currentValue - salvageValue) / usefulLife) / currentValue) * 100).toFixed(2);
                } else if (method === "WDV") {
                    depreciationRate = ((1 - Math.pow(salvageValue / currentValue, 1 / usefulLife)) * 100).toFixed(2);
                }

                let totalDepreciation = 0;
                document.getElementById("salvage_value").value = salvageValue;
                console.log("dep_rate" + depreciationRate + "devidend" + devidend);
                document.getElementById("depreciation_rate").value = depreciationRate;
                document.getElementById("depreciation_rate_year").value = depreciationRate;
                document.getElementById("total_depreciation").value = totalDepreciation;
            }

            function showToast(icon, title) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.onmouseenter = Swal.stopTimer;
                        toast.onmouseleave = Swal.resumeTimer;
                    },
                });
                Toast.fire({
                    icon,
                    title
                });
            }

            @if (session('success'))
                showToast("success", "{{ session('success') }}");
            @endif

            @if (session('error'))
                showToast("error", "{{ session('error') }}");
            @endif

            @if ($errors->any())
                showToast('error',
                    "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
                );
            @endif

            $('#category').on('change', function () {

                var category_id = $(this).val();
                if (category_id) {
                    $.ajax({
                        type: "GET",
                        url: "{{ route('finance.fixed-asset.setup.category') }}?category_id=" + category_id,
                        success: function (res) {
                            if (res) {
                                $('#ledger').val(res.ledger_id).select2();
                                $('#ledger').trigger('change');
                                $('#ledger_group').val(res.ledger_group_id);
                                $('#maintenance_schedule').val(res.maintenance_schedule);
                                $('#useful_life').val(res.expected_life_years);
                                if (res.salvage_percentage)
                                    $('#depreciation_percentage').val(res.salvage_percentage);
                                else
                                    $('#depreciation_percentage').val('{{ $dep_percentage }}');



                                updateDepreciationValues();

                            }
                        }
                    });
                    $.ajax({
                        url: "{{route('finance.fixed-asset.asset-code')}}", // Replace with your actual route
                        method: 'POST',
                        data: { category: category_id },
                        success: function (response) {
                            $('#asset_code').val(response.code); 
                             $('#prefix').val(response.prefix); 
                        },
                        error: function () {
                            $('#asset_code').val('');
                            $('#prefix').val('');
                        }
                    });


                }
            });
            $('#location').on('change', function () {
                var locationId = $(this).val();

                if (locationId) {
                    // Build the route manually
                    var url = '{{ route('cost-center.get-cost-center', ':id') }}'.replace(':id', locationId);

                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            if (data.length == 0) {
                                $('#cost_center').empty();
                                $('#cost_center').prop('required', false);
                                $('.cost_center').hide();
                            } else {
                                $('.cost_center').show();
                                $('#cost_center').prop('required', true);
                                $('#cost_center').empty(); // Clear previous options
                                $.each(data, function (key, value) {
                                    $('#cost_center').append('<option value="' + value.id + '">' +
                                        value.name + '</option>');
                                });
                            }
                        },
                        error: function () {
                            $('#cost_center').empty();
                        }
                    });
                } else {
                    $('#cost_center').empty();
                }
            });

            $('#location').trigger('change');
            $('#asset_code').on('input', function () {
                $.ajax({
                    url: '{{ route('finance.fixed-asset.check-code') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        code: $('#asset_code').val(),
                    },
                    success: function (response) {
                        const $input = $('#asset_code');
                        const $errorEl = $('.code_error'); // Use class instead of ID

                        if (response.exists) {
                            $errorEl.text('Code already exists.');
                            $input.addClass('is-invalid');
                        } else {
                            $errorEl.text('');
                            $input.removeClass('is-invalid');
                        }
                    }
                });

            });
        </script>
        <script>
document.addEventListener('DOMContentLoaded', function () {
    const vendorSelect = document.getElementById('vendor');
    const currencySelect = document.getElementById('currency');
    const currencyIdInput = document.getElementById('currency_id');

    function updateCurrency() {
        const selectedOption = vendorSelect.options[vendorSelect.selectedIndex];
        const currencyId = selectedOption.getAttribute('data-country');

        if (currencyId) {
            currencySelect.value = currencyId;
            currencyIdInput.value = currencyId;
        } else {
            currencySelect.value = '';
            currencyIdInput.value = '';
        }

        currencySelect.disabled = !currencyId;
    }

    // Initial update in case old() data exists
    updateCurrency();

    // Update currency when vendor changes
    vendorSelect.addEventListener('change', updateCurrency);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const subTotalInput = document.getElementById('sub_total');
    const taxAmountInput = document.getElementById('tax_amount');
    const purchaseAmountInput = document.getElementById('purchase_amount');

    function calculatePurchaseAmount() {
        const subTotal = parseFloat(subTotalInput.value) || 0;
        const taxAmount = parseFloat(taxAmountInput.value) || 0;
        const purchaseAmount = subTotal + taxAmount;

        purchaseAmountInput.value = purchaseAmount.toFixed(2);
    }

    // Listen to changes in sub total and tax amount
    subTotalInput.addEventListener('input', calculatePurchaseAmount);
    taxAmountInput.addEventListener('input', calculatePurchaseAmount);

    // Initial calculation in case old values are pre-filled
    calculatePurchaseAmount();
});
</script>



    @endsection
@endsection