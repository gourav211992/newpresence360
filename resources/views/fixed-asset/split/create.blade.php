@extends('layouts.app')
<style>
    .code_error {
        font-size: 12px;
    }
</style>

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
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Split</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Add New</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('finance.fixed-asset.split.index') }}"> <button
                                    class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</button>
                            </a>
                            <button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" type="button" id="save-draft-btn">
                                <i data-feather="save"></i> Save as Draft
                            </button>
                            <button type="submit" form="fixed-asset-split-form" class="btn btn-primary btn-sm"
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
                        <form id="fixed-asset-split-form" method="POST"
                            action="{{ route('finance.fixed-asset.split.store') }}" enctype="multipart/form-data">

                            @csrf
                            <input type="hidden" name="sub_assets" id="sub_assets">
                            <input type="hidden" name="doc_number_type" id="doc_number_type">
                            <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
                            <input type="hidden" name="doc_prefix" id="doc_prefix">
                            <input type="hidden" name="doc_suffix" id="doc_suffix">
                            <input type="hidden" name="doc_no" id="doc_no">
                            <input type="hidden" name="document_status" id="document_status" value="">
                            <input type="hidden" name="dep_type" id="depreciation_type" value="{{ $dep_type }}">

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


                                                        <div class="col-md-6 text-sm-end" hidden>
                                                            <span
                                                                class="badge rounded-pill badge-light-secondary forminnerstatus">
                                                                Status : <span class="text-success">Approved</span>
                                                            </span>
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
                                                        <select class="form-select" id="book_id" name="book_id" required>
                                                            @foreach ($series as $book)
                                                                <option value="{{ $book->id }}">{{ $book->book_code }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="document_number">Doc No <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" readonly
                                                            id="document_number" name="document_number" required>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="document_date">Doc Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control" id="document_date"
                                                            name="document_date" value="{{ date('Y-m-d') }}" required>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Category <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="old_category_id"
                                                            id="old_category" required>
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}"
                                                                    {{ old('category') == $category->id ? 'selected' : '' }}>
                                                                    {{ $category->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
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

                                                        </select>
                                                    </div>

                                                </div>
                                                <div class="row align-items-center mb-1 cost_center">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cost Center <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="cost_center" class="form-select"
                                                            name="cost_center_id" required>
                                                        </select>
                                                    </div>

                                                </div>

                                            </div>


                                            <div class="col-md-4">

                                                {{-- History Code --}}

                                            </div>

                                        </div>
                                    </div>
                                </div>


                                <div class="row customernewsection-form">
                                    <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">Old Asset Details</h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <!-- Asset Code & Name -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="asset_id">Asset Code &
                                                                Name <span class="text-danger">*</span></label>
                                                            <input type="text" id="asset_search_input"
                                                                class="form-control">
                                                            <input type="hidden" id="asset_id" name="asset_id">
                                                        </div>
                                                    </div>

                                                    <!-- Sub-Asset Code -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="sub_asset_id">Sub-Asset Code
                                                                <span class="text-danger">*</span></label>
                                                            <input type="text" id="subasset_search_input"
                                                                class="form-control">
                                                            <input type="hidden" id="sub_asset_id" name="sub_asset_id">
                                                        </div>
                                                    </div>

                                                    <!-- Last Date of Dep. -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="last_dep_date">Last Date of
                                                                Posted<span class="text-danger">*</span></label>
                                                            <input type="date" id="last_dep_date" name="last_dep_date"
                                                                class="form-control" readonly />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="expiry_date">Last Date of
                                                                Dep.<span class="text-danger">*</span></label>
                                                            <input type="date" id="expiry_date" name="expiry_date"
                                                                class="form-control" readonly />
                                                        </div>
                                                    </div>
                                                    <input type="hidden" id="capitalize_date_old"
                                                        name="capitalize_date" />

                                                    <!-- Current Value -->
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="current_value_asset">Current
                                                                Value
                                                                <span class="text-danger">*</span></label>
                                                            <input type="text" id="current_value_asset"
                                                                name="current_value_asset" class="form-control" disabled
                                                                required />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label" for="salvage_value">Salvage
                                                                Value
                                                                <span class="text-danger">*</span></label>
                                                            <input type="text" id="salvage_value_asset"
                                                                name="salvage_value_asset" class="form-control" disabled
                                                                required />
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
                                                        <h4 class="card-title text-theme">New Asset Detail</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end">
                                                    <a href="#" id="delete_new_sub_asset"
                                                        class="btn btn-sm btn-outline-danger me-50">
                                                        <i data-feather="x-circle"></i> Delete</a>
                                                    <a href="#" id= "add_new_sub_asset"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i data-feather="plus"></i> Add New</a>
                                                </div>
                                            </div>
                                        </div>





                                        <div class="row">

                                            <div class="col-md-12">


                                                <div class="table-responsive pomrnheadtffotsticky">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                        <thead>
                                                            <tr>
                                                                <th class="customernewsection-form">
                                                                    <div
                                                                        class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input"
                                                                            id="Email">
                                                                        <label class="form-check-label"
                                                                            for="Email"></label>
                                                                    </div>
                                                                </th>
                                                                <th width="200">Asset Code</th>
                                                                <th width="200">Asset Name</th>
                                                                <th width="200">Sub Asset Code</th>
                                                                <th width="200">Category</th>
                                                                <th width="200">Ledger</th>
                                                                <th width="50">Est. Life</th>
                                                                <th width="200">Capitalize Date</th>
                                                                <th width="50">Quantity</th>
                                                                <th class="text-end">Current Value</th>
                                                                <th class="text-end">Dep %</th>
                                                                <th class="text-end">Salvage Value</th>

                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            <tr>
                                                                <td class="customernewsection-form">
                                                                    <div
                                                                        class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input row-check">
                                                                        <label class="form-check-label"></label>
                                                                    </div>
                                                                </td>
                                                                <td class="poprod-decpt">
                                                                    <input type="text" required placeholder="Enter"
                                                                        class="form-control mw-100 mb-25 asset-code-input"
                                                                        oninput="this.value = this.value.toUpperCase();" />
                                                                    <span class="text-danger code_error"
                                                                        style="font-size:12px"></span>
                                                                </td>
                                                                <td class="poprod-decpt">
                                                                    <input type="text" required placeholder="Enter"
                                                                        class="form-control mw-100 mb-25 asset-name-input"
                                                                        oninput="syncInputAcrossSameAssets(this)" />
                                                                </td>
                                                                <td class="poprod-decpt">
                                                                    <input type="text" required placeholder="Enter"
                                                                        disabled
                                                                        class="form-control mw-100 mb-25 sub-asset-code-input" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" required placeholder="Enter"
                                                                        class="form-control mw-100 mb-25 category-input" />
                                                                    <input type="hidden" class="category" />
                                                                    <input type="hidden" class="salvage_per" />

                                                                </td>
                                                                <td>
                                                                    <select class="form-select mw-100 mb-25 ledger"
                                                                        required>
                                                                        <option value=""
                                                                            {{ old('ledger') ? '' : 'selected' }}>Select
                                                                        </option>
                                                                    </select>
                                                                    <select
                                                                        class="d-none ledger-group form-select mw-100 mb-25"
                                                                        required>
                                                                    </select>


                                                                </td>
                                                                <td>
                                                                    <input type="number" required
                                                                        class="form-control mw-100 mb-25 life" disabled
                                                                        oninput="syncInputAcrossSameAssets(this)">
                                                                </td>
                                                                <td>
                                                                    <input type="date" required
                                                                        class="form-control mw-100 mb-25 capitalize_date"
                                                                        oninput="syncInputAcrossSameAssets(this)" />
                                                                </td>

                                                                <td>
                                                                    <input type="text" required disabled value="1"
                                                                        class="form-control mw-100 quantity-input" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" required
                                                                        class="form-control mw-100 text-end current-value-input"
                                                                        oninput="calculateTotals()" min="1" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" required
                                                                        class="form-control mw-100 text-end dep_per"
                                                                        readonly />
                                                                </td>
                                                                <td>
                                                                    <input type="text" required
                                                                        class="form-control mw-100 text-end salvage-value-input"
                                                                        min="1" readonly />
                                                                </td>




                                                            </tr>


                                                        </tbody>


                                                    </table>
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
                                                    <div class="col-md-3 d-none">
                                                        <div class="mb-1">
                                                            <label class="form-label">Category <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" id="category">
                                                                <option value=""
                                                                    {{ old('category') ? '' : 'selected' }}>
                                                                    Select</option>
                                                                @foreach ($new_categories as $category)
                                                                    <option value="{{ $category->id }}"
                                                                        {{ old('category') == $category->id ? 'selected' : '' }}>
                                                                        {{ $category->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Quantity <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="quantity"
                                                                id="quantity" value="{{ old('quantity') }}" readonly />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 d-none">
                                                        <div class="mb-1">
                                                            <label class="form-label">Ledger <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" id="ledger">
                                                                <option value=""
                                                                    {{ old('ledger') ? '' : 'selected' }}>Select</option>
                                                                @foreach ($ledgers as $ledger)
                                                                    <option value="{{ $ledger->id }}"
                                                                        {{ old('ledger') == $ledger->id ? 'selected' : '' }}>
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
                                                            <select class="form-select" id="ledger_group"
                                                                name="ledger_group_id">
                                                                @foreach ($groups as $group)
                                                                    <option value="{{ $group->id }}">
                                                                        {{ $group?->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 d-none">
                                                        <div class="mb-1">
                                                            <label class="form-label">Capitalize Date <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="date" class="form-control"
                                                                id="capitalize_date"
                                                                value="{{ old('capitalize_date') }}" />
                                                        </div>
                                                    </div>


                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Maint. Schedule <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select" name="maintenance_schedule"
                                                                id="maintenance_schedule" required>
                                                                <option value=""
                                                                    {{ old('maintenance_schedule') == '' ? 'selected' : '' }}>
                                                                    Select</option>
                                                                <option value="weekly"
                                                                    {{ old('maintenance_schedule') == 'Weekly' ? 'selected' : '' }}>
                                                                    Weekly</option>
                                                                <option value="monthly"
                                                                    {{ old('maintenance_schedule') == 'Monthly' ? 'selected' : '' }}>
                                                                    Monthly</option>
                                                                <option value="quarterly"
                                                                    {{ old('maintenance_schedule') == 'Quarterly' ? 'selected' : '' }}>
                                                                    Quarterly</option>
                                                                <option value="semi-annually"
                                                                    {{ old('maintenance_schedule') == 'Semi-Annually' ? 'selected' : '' }}>
                                                                    Semi-Annually</option>
                                                                <option value="annually"
                                                                    {{ old('maintenance_schedule') == 'Annually' ? 'selected' : '' }}>
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


                                                    <div class="col-md-3 d-none">
                                                        <div class="mb-1">
                                                            <label class="form-label">Est. Useful Life (yrs) <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="useful_life"
                                                                value="{{ old('useful_life') }}" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Salvage Value <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control"
                                                                name="salvage_value" id="salvage_value" readonly
                                                                value="{{ old('salvage_value') }}" required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 d-none">
                                                        <div class="mb-1">
                                                            <label class="form-label">Dep % <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" class="form-control"
                                                                id="depreciation_rate" name="depreciation_percentage"
                                                                readonly />
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
                                                                name="total_depreciation" class="form-control"
                                                                value="0" readonly />
                                                        </div>
                                                    </div>




                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Current Value <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" required
                                                                name="current_value" id="current_value"
                                                                value="{{ old('current_value') }}" readonly />
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
    </div>
    <!-- END: Content-->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>



    <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1"
        aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>MRN</strong>? After Amendment
                        this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
                </div>
            </div>
        </div>
    </div>
@endsection




@section('scripts')
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
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })




        // Delegated click event for dynamically added rows
        $(document).on('click', '.mrntableselectexcel tr', function() {
            $(this).addClass('trselected').siblings().removeClass('trselected');
        });

        // Keyboard navigation for up/down arrow keys
        $(document).on('keydown', function(e) {
            var $selected = $('.trselected');

            if (e.which === 38) { // Up arrow
                $selected.prev('tr').addClass('trselected').siblings().removeClass('trselected');
            } else if (e.which === 40) { // Down arrow
                $selected.next('tr').addClass('trselected').siblings().removeClass('trselected');
            }

            // Scroll to the selected row inside scrollable container
            var $container = $('.mrntableselectexcel');
            var $newSelected = $('.trselected');

            if ($newSelected.length && $container.length && $newSelected.offset()) {
                var containerOffset = $container.offset().top;
                var selectedOffset = $newSelected.offset().top;
                $container.scrollTop($container.scrollTop() + (selectedOffset - containerOffset - 40));
            }
        });


        $('#add_new_sub_asset').on('click', function() {
            let allInputsFilled = true;

            $('.mrntableselectexcel').find('input, select').each(function() {
                if ($(this).val() === null || $(this).val().toString().trim() === '') {
                    allInputsFilled = false;
                    $(this).addClass('is-invalid'); // highlight empty input/select
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!allInputsFilled) {
                showToast('warning',
                    'Please complete all input fields in the existing row(s) before adding a new one.');
                return;
            }

            const subAssetCode = $('#sub_asset_id').val();
            genereateSubAssetRow(subAssetCode);
            applyFixedPrefixToInputs();
        });

        function genereateSubAssetRow(code) {
            let Current = $('#current_value_asset').val();
            let subAssetId = $('#sub_asset_id').val();
            let assetId = $('#asset_id').val();
            let formattedDate = "";
            if ($('#last_dep_date').val() != "") {
                let lastDepDate = new Date($('#last_dep_date').val());
                lastDepDate.setDate(lastDepDate.getDate() + 1);
                formattedDate = lastDepDate.toISOString().split('T')[0];
            }
            let newRow = '';
            newRow = ` <tr >
                 <td class="customernewsection-form">
                <div class="form-check form-check-primary custom-checkbox">
                  <input type="checkbox" class="form-check-input row-check">
                  <label class="form-check-label"></label>
                </div>
              </td>
                <td class="poprod-decpt">
                    <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 asset-code-input" oninput="this.value = this.value.toUpperCase();" />
                    <span class="text-danger code_error" style="font-size:12px"></span>
                </td>
                <td class="poprod-decpt">
                    <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 asset-name-input" oninput="syncInputAcrossSameAssets(this)" />
                </td>
                <td class="poprod-decpt">
                    <input type="text" required placeholder="Enter" disabled class="form-control mw-100 mb-25 sub-asset-code-input" />
                </td>
                 <td>
               <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 category-input" />
                 <input type="hidden" class="category"/> 
                 <input type="hidden" class="salvage_per"/> 
               
              </td>
              <td>
               <select class="form-select mw-100 mb-25 ledger" required>
                                                                <option value=""
                                                                    {{ old('ledger') ? '' : 'selected' }}>Select</option>
                                                               
                </select>
                                                                   <select class="d-none ledger-group form-select mw-100 mb-25" required>
                </select>
                                                            </td>
              <td>
                <input type="number" required class="form-control mw-100 mb-25 life" disabled oninput="syncInputAcrossSameAssets(this)"> 
                </td>
              <td>
                <input type="date" required class="form-control mw-100 mb-25 capitalize_date" value="${formattedDate}" oninput="syncInputAcrossSameAssets(this)"/>
              </td>
                <td>
                    <input type="text" required disabled value="1" class="form-control mw-100 quantity-input" />
                </td>
                <td>
                    <input type="text" required class="form-control mw-100 text-end current-value-input"  oninput="calculateTotals()" max="${Current}" min="1" />
                </td>
                <td>
                <input type="text" required class="form-control mw-100 text-end dep_per" readonly />
              </td>
                <td>
                    <input type="text" required class="form-control mw-100 text-end salvage-value-input" min="1" readonly />
                </td>
                 
                </tr> `;
            $(".mrntableselectexcel tr").removeClass('trselected');
            $('.mrntableselectexcel').append(newRow);
            initializeCategoryAutocomplete('.category-input');
            depCapitalizeDate();
        }
        $('#Email').on('change', function() {
            let isChecked = $(this).is(':checked');
            $('.form-check-input').not(this).prop('checked', isChecked);
        });



        $('#delete_new_sub_asset').on('click', function() {
            let totalRows = $('.mrntableselectexcel tr').length;
            let checkedRows = $('.mrntableselectexcel tr .row-check:checked').length;
            console.log(totalRows, checkedRows);

            if ((totalRows - checkedRows) < 1) {
                showToast('warning', 'At least one row must remain.');
                return;
            }

            $('.mrntableselectexcel .row-check:checked').closest('tr').remove();
            updateSubAssetCodes();
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

        $('#book_id').on('change', function() {
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
        document.getElementById('save-draft-btn').addEventListener('click', function() {
            $('.preloader').show();
            document.getElementById('document_status').value = 'draft';

            collectSubAssetDataToJson();

            let currentValueAsset = parseFloat($('#current_value_asset').val()) || 0;
            let totalCurrentValue = parseFloat($('#current_value').val()) || 0;

            if (totalCurrentValue != currentValueAsset) {
                $('.preloader').hide();
                showToast('error', 'Total Current Value must equal to Asset Current Value.');
                return false;
            } else if (totalCurrentValue <= 0) {
                $('.preloader').hide();
                showToast('error', 'Total Current Value must be greater than 0.');
                return false;
            }
            let isValid = true;
            $('.asset-code-input').each(function(index) {
                if ($(this).hasClass('is-invalid')) {
                    // $('.preloader').hide();
                    isValid = false;
                }
            });
            if (isValid == false) {
                $('.preloader').hide();
                showToast('error', 'Code Already Exist.');
                return false;
            }
            if (!validateAssetCodes()) {
                $('.preloader').hide();
                showToast('error', 'Invalid Asset Code.');
                return false;
            }


            document.getElementById('fixed-asset-split-form').submit();
        });

        $('#fixed-asset-split-form').on('submit', function(e) {
            $('.preloader').show();
            e.preventDefault(); // Always prevent default first

            collectSubAssetDataToJson();
            let isValid = true;




            document.getElementById('document_status').value = 'submitted';

            let currentValueAsset = parseFloat($('#current_value_asset').val()) || 0;
            let totalCurrentValue = parseFloat($('#current_value').val()) || 0;

            if (totalCurrentValue != currentValueAsset) {
                $('.preloader').hide();
                showToast('error', 'Total Current Value must equal to Asset Current Value.');
                return false;
            } else if (totalCurrentValue <= 0) {
                $('.preloader').hide();
                showToast('error', 'Total Current Value must be greater than 0.');
                return false;
            }
            $('.asset-code-input').each(function(index) {
                if ($(this).hasClass('is-invalid')) {
                    isValid = false;
                }
            });
            if (isValid == false) {
                $('.preloader').hide();
                showToast('error', 'Code Already Exist.');
                return false;
            }
             if (!validateAssetCodes()) {
                $('.preloader').hide();
                showToast('error', 'Invalid Asset Code.');
                return false;
            }

            // Submit formadd_bln manually if validation passes
            this.submit();
        });


        $(document).ready(function() {
            $(document).on('change', '.ledger', function() {
                const $row = $(this).closest('tr');
                const ledgerId = $(this).val();
                console.log(ledgerId);
                const $ledgerGroupSelect = $('.ledger-group');

                if (ledgerId) {
                    $.ajax({
                        url: '{{ route('finance.fixed-asset.getLedgerGroups') }}',
                        method: 'GET',
                        data: {
                            ledger_id: ledgerId,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $ledgerGroupSelect.empty(); // Clear previous options
                            response.forEach(item => {
                                $ledgerGroupSelect.append(
                                    `<option value="${item.id}">${item.name}</option>`
                                );
                            });

                        },
                        error: function() {
                            showToast('error', 'Error fetching group items.');
                        }
                    });
                } else {
                    $ledgerGroupSelect.empty();
                }
                syncInputAcrossSameAssets(this);
            });
            $('#category').val($('#old_category').val()).trigger('change');

            $("#asset_search_input").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ route('finance.fixed-asset.asset-search') }}',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            location: $('#location').val(),
                            cost_center: $('#cost_center').val(),
                            category: $('#old_category').val(),
                        },
                        success: function(data) {
                            response(data.map(function(item) {
                                return {
                                    label: item.asset_code + ' (' + item
                                        .asset_name + ')',
                                    value: item.id,
                                };
                            }));
                        },
                        error: function() {
                            response([]);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    const asset = ui.item.asset;
                    $(this).val(ui.item.label);
                    $('#asset_id').val(ui.item.value);
                    clearSubAsset();
                    return false; // Prevent default behavior
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        clearAsset();
                    }
                },
                focus: function(event, ui) {
                    if (!ui.item) {
                        clearAsset();
                    }
                }
            }).focus(function() {
                if (this.value === '') {
                    $(this).autocomplete('search');
                }
            });
            $("#subasset_search_input").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ route('finance.fixed-asset.sub_asset_search') }}',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id: $('#asset_id').val(),
                            q: request.term
                        },
                        success: function(data) {
                            response(data.map(function(item) {
                                return {
                                    label: item.sub_asset_code,
                                    value: item.id,
                                    asset: item.asset,
                                    sub_asset: item
                                };
                            }));
                        },
                        error: function() {
                            response([]);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    const asset = ui.item.asset;
                    const sub_asset = ui.item.sub_asset

                    // Set the input box and hidden ID field
                    $(this).val(ui.item.label);
                    $('#sub_asset_id').val(ui.item.value);
                    console.log(asset);

                    // Fill other fields directly
                    //$('#category').val(asset.category_id).trigger('change');
                    $('#ledger').val(asset.ledger_id).trigger('change');
                    $('#ledger_group').val(asset.ledger_group_id).trigger('change');
                    $('#capitalize_date_old').val(sub_asset.capitalize_date);


                    $('#last_dep_date')
                        .val('')
                        .removeAttr('min')
                        .removeAttr('max')
                        .prop('readonly', true).prop('required', false);
                    $('#expiry_date').val('');
                    $('#salvage_value_asset').val('');
                    let expiryDate = new Date(sub_asset.expiry_date).toISOString().split('T')[0];

                    // Handle depreciation date
                    if (sub_asset.last_dep_date !== sub_asset.capitalize_date) {
                        let lastDepDate = new Date(sub_asset.last_dep_date);
                        lastDepDate.setDate(lastDepDate.getDate() - 1);
                        let formattedDate = lastDepDate.toISOString().split('T')[0];
                        let today = new Date().toISOString().split('T')[0];

                        $('#last_dep_date')
                            .val(formattedDate)
                            .attr('min', formattedDate)
                            .attr('max', expiryDate)
                            .prop('readonly', false).prop('required', true);
                    }

                    $('#expiry_date').val(expiryDate);
                    $('#salvage_value_asset').val(sub_asset.salvage_value);
                    $('.capitalize_date').val(sub_asset.last_dep_date);
                    depCapitalizeDate();
                    $('#depreciation_rate').val(asset.depreciation_percentage);
                    $('#depreciation_rate_year').val(asset.depreciation_percentage_year);
                    $('#useful_life').val(asset.useful_life);
                    $('#maintenance_schedule').val(asset.maintenance_schedule);
                    $('#current_value_asset').val(sub_asset.current_value_after_dep);


                    $('#total_depreciation').val(sub_asset.total_depreciation);
                    add_blank();

                    return false; // Prevent default behavior
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        clearSubAsset();
                    }

                },
                focus: function(event, ui) {
                    if (!ui.item) {
                        clearSubAsset();
                    }
                }
            }).focus(function() {
                if (this.value === '') {
                    $(this).autocomplete('search');
                }
            });


        });

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
            $('.preloader').hide();
            showToast("success", "{{ session('success') }}");
        @endif

        @if (session('error'))
            $('.preloader').hide();
            showToast("error", "{{ session('error') }}");
        @endif

        @if ($errors->any())
            $('.preloader').hide();
            showToast('error',
                "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
            );
        @endif

        // Function to update sub-asset codes based on current asset codes in all rows
        function updateSubAssetCodes() {
            const assetCodeCounts = {};
            const assetCodeToName = {}; // Store the first encountered name for each asset code
            const assetCodeToCategoryId = {}; // Store the first encountered category for each asset code
            const assetCodeToLedger = {}; // Store the first encountered ledger for each asset code
            const assetCodeToLedgerGroup = {}; // Store the first encountered ledger group for each asset code
            const asstCodeToLife = {};
            const assetCodeToCategoryText = {};
            const assetCodeToSalvage = {};
            const capitalizeDate = {};



            $('.mrntableselectexcel tr').each(function() {
                const $row = $(this);

                const assetCode = $row.find('.asset-code-input').val().trim();
                $.ajax({
                    url: '{{ route('finance.fixed-asset.check-code') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        code: assetCode
                    },
                    success: function(response) {
                        const $input = $row.find('.asset-code-input');
                        const $errorEl = $row.find('.code_error'); // Use class instead of ID

                        if (response.exists) {
                            $errorEl.text('Code already exists.');
                            $input.addClass('is-invalid');
                        } else {
                            $errorEl.text('');
                            $input.removeClass('is-invalid');
                        }
                    }
                });


                const $assetNameInput = $row.find('.asset-name-input');
                const $subAssetInput = $row.find('.sub-asset-code-input');




                if (assetCode !== '') {
                    // Count sub-assets per asset code
                    assetCodeCounts[assetCode] = (assetCodeCounts[assetCode] || 0) + 1;
                    const subAssetCode = `${assetCode}-${String(assetCodeCounts[assetCode]).padStart(2, '0')}`;
                    $subAssetInput.val(subAssetCode);

                    // Handle asset name consistency
                    const currentAssetName = $assetNameInput.val().trim();

                    if (!assetCodeToName[assetCode] && currentAssetName !== '' && !assetCodeToCategoryId[
                            assetCode] && !assetCodeToCategoryText[assetCode] && !assetCodeToLedger[assetCode] && !
                        assetCodeToLedgerGroup[assetCode] && !asstCodeToLife[assetCode] && !assetCodeToSalvage[
                            assetCode] && !capitalizeDate[assetCode]) {
                        // First time seeing this asset code — store its name
                        assetCodeToName[assetCode] = currentAssetName;
                        assetCodeToCategoryId[assetCode] = $row.find('.category-input').val().trim();
                        assetCodeToCategoryText[assetCode] = $row.find('.category').val().trim();
                        assetCodeToLedger[assetCode] = $row.find('.ledger').val();
                        assetCodeToLedgerGroup[assetCode] = $row.find('.ledger-group').val();
                        asstCodeToLife[assetCode] = $row.find('.life').val().trim();
                        assetCodeToSalvage[assetCode] = $row.find('.salvage_per').val().trim();
                        capitalizeDate[assetCode] = $row.find('.capitalize_date').val().trim();

                    } else if (assetCodeToName[assetCode]) {
                        $assetNameInput.val(assetCodeToName[assetCode]);
                        renderLedgerSelects();
                        $row.find('.category-input').val(assetCodeToCategoryId[assetCode]).trigger('change');
                        $row.find('.category').val(assetCodeToCategoryText[assetCode]).trigger('change');
                        $row.find('.ledger').val(assetCodeToLedger[assetCode] || "").trigger('change');
                        $row.find('.ledger-group').val(assetCodeToLedgerGroup[assetCode] || "").trigger('change');
                        $row.find('.life').val(asstCodeToLife[assetCode] || "");
                        $row.find('.salvage_per').val(assetCodeToSalvage[assetCode]);
                        $row.find('.capitalize_date').val(capitalizeDate[assetCode]);

                    }
                } else {
                    $subAssetInput.val('');

                }

            });
            calculateTotals();

        }

        $('#ledger').change(function() {
            if ($(this).val() == "") {
                return;
            }
            //syncInputAcrossSameAssets('ledger');

            let groupDropdown = $('#ledger_group');
            $.ajax({
                url: '{{ route('finance.fixed-asset.getLedgerGroups') }}',
                method: 'GET',
                data: {
                    ledger_id: $(this).val(),
                    _token: $('meta[name="csrf-token"]').attr(
                        'content') // CSRF token
                },
                success: function(response) {
                    groupDropdown.empty(); // Clear previous options

                    response.forEach(item => {
                        groupDropdown.append(
                            `<option value="${item.id}">${item.name}</option>`
                        );
                    });

                },
                error: function() {
                    showToast('error', 'Error fetching group items.');
                }
            });

        });

        $('#old_category').on('change', function() {
            $('.mrntableselectexcel').empty();
            add_blank();
            $('#asset_search_input').val('');
            $('#asset_id').val('');
            $('#subasset_search_input').val('');
            $('#sub_asset_id').val('');
            $('#last_dep_date')
                .val('')
                .removeAttr('min')
                .removeAttr('max')
                .prop('readonly', true).prop('required', false);
            $('#expiry_date').val('');
            $('#salvage_value_asset').val('');
            $('#current_value_asset').val('');
            $('#category').val($(this).val()).trigger('change');
            loadLocation();
            depCapitalizeDate();


        });

        function collectSubAssetDataToJson() {
            const subAssetData = [];

            $('.mrntableselectexcel tr').each(function() {
                const $row = $(this);

                const assetCode = $row.find('.asset-code-input').val()?.trim() || '';
                const assetName = $row.find('td:eq(2) input').val()?.trim() || '';
                const subAssetCode = $row.find('.sub-asset-code-input').val()?.trim() || '';
                const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                const currentValue = parseFloat($row.find('.current-value-input').val()) || 0;
                const salvageValue = parseFloat($row.find('.salvage-value-input').val()) || 0;
                const category = $row.find('.category').val()?.trim() || '';
                const categoryInput = $row.find('.category-input').val()?.trim() || '';
                const ledger = $row.find('.ledger').val() || '';
                const ledgerGroup = $('#ledger_group').val() || '';
                const life = $row.find('.life').val()?.trim() || '';
                const salvagePer = $row.find('.salvage_per').val()?.trim() || '';
                const depPer = $row.find('.dep_per').val()?.trim() || '';
                const capitalizeDate = $row.find('.capitalize_date').val()?.trim() || '';


                if (assetCode !== '') {
                    subAssetData.push({
                        asset_code: assetCode,
                        asset_name: assetName,
                        sub_asset_id: subAssetCode,
                        quantity: quantity,
                        current_value: currentValue,
                        salvage_value: salvageValue,
                        category: category,
                        category_input: categoryInput,
                        ledger: ledger,
                        ledger_group: ledgerGroup,
                        life: life,
                        salvage_per: salvagePer,
                        dep_per: depPer,
                        capitalize_date: capitalizeDate,
                    });
                }
            });

            $('#sub_assets').val(JSON.stringify(subAssetData));
        }

        $(document).on('input change', '.asset-code-input', updateSubAssetCodes);
        $('#location').on('change', function() {
            var locationId = $(this).val();
            var selectedCostCenterId = '{{ $data->cost_center_id ?? '' }}';

            if (locationId) {
                // Build the route manually
                var url = '{{ route('finance.fixed-asset.get-cost-centers') }}';

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        location_id: locationId,
                        category_id: $('#old_category').val(),
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.length == 0) {
                            $('#cost_center').empty();
                            $('#cost_center').prop('required', false);
                            $('.cost_center').hide();
                            // loadCategories();
                        } else {
                            $('.cost_center').show();
                            $('#cost_center').prop('required', true);
                            $('#cost_center').empty(); // Clear previous options
                            $.each(data, function(key, value) {
                                let selected = (value.id == selectedCostCenterId) ? 'selected' :
                                    '';
                                $('#cost_center').append('<option value="' + value.id + '" ' +
                                    selected + '>' + value.name + '</option>');
                            });
                            $('#cost_center').trigger('change');
                        }
                    },
                    error: function() {
                        $('#cost_center').empty();
                    }
                });
            } else {
                $('#cost_center').empty();
            }
        });

        function add_blank() {
            $('.mrntableselectexcel').empty();
            let blank_row = ` <tr >
              <td class="customernewsection-form">
                <div class="form-check form-check-primary custom-checkbox">
                  <input type="checkbox" class="form-check-input row-check">
                  <label class="form-check-label"></label>
                </div>
              </td>
              <td class="poprod-decpt">
                <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 asset-code-input" oninput="this.value = this.value.toUpperCase();" />
                <span class="text-danger code_error" style="font-size:12px"></span>
              </td>
              <td class="poprod-decpt">
                <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 asset-name-input" oninput="syncInputAcrossSameAssets(this)"/>
              </td>
              <td class="poprod-decpt">
                <input type="text" required placeholder="Enter" disabled class="form-control mw-100 mb-25 sub-asset-code-input" />
              </td>
              <td>
               <input type="text" required placeholder="Enter" class="form-control mw-100 mb-25 category-input" />
                 <input type="hidden" class="category"/> 
                 <input type="hidden" class="salvage_per"/> 
               
              </td>
              <td>
              <select class="form-select mw-100 mb-25 ledger" required>
                                                                <option value=""
                                                                    {{ old('ledger') ? '' : 'selected' }}>Select</option>
                                                         
                                                            </select>
                                                               <select class="d-none ledger-group form-select mw-100 mb-25" required>
                </select>
             
                                                             </td>
              <td>
                <input type="number" required class="form-control mw-100 mb-25 life" disabled oninput="syncInputAcrossSameAssets(this)"> 
                </td>
              <td>
                <input type="date" required class="form-control mw-100 mb-25 capitalize_date" oninput="syncInputAcrossSameAssets(this)"/>
              </td>
              <td>
                <input type="text" required disabled value="1" class="form-control mw-100 quantity-input" />
              </td>
              <td>
                <input type="text" required class="form-control mw-100 text-end current-value-input"  oninput="calculateTotals()" min="1" />
              </td>
              <td>
                <input type="text" required class="form-control mw-100 text-end dep_per" readonly />
              </td>
              <td>
                <input type="text" required class="form-control mw-100 text-end salvage-value-input" min="1" readonly />
              </td>
              
            </tr>`;
            $('.mrntableselectexcel').append(blank_row);
            initializeCategoryAutocomplete('.category-input');
            depCapitalizeDate();
            applyFixedPrefixToInputs();
        }

        function loadLocation(selectlocation = null) {
            $('#cost_center').empty();
            $('#cost_center').prop('required', false);
            $('.cost_center').hide();
            if (!$('#old_category').val()) {
                return;
            }
            const url = '{{ route('finance.fixed-asset.get-locations') }}';

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    category_id: $('#old_category').val(),
                },
                dataType: 'json',
                success: function(data) {
                    const $category = $('#location');
                    $category.empty();

                    $.each(data, function(key, value) {
                        const isSelected = selectlocation == value.id ? ' selected' : '';
                        $category.append('<option value="' + value.id + '"' + isSelected + '>' + value
                            .name + '</option>');
                    });
                    $('#location').trigger('change');
                },
                error: function() {
                    $('#location').empty();
                }
            });
        }
        loadLocation();
        $('#last_dep_date').on('change', function() {
            let selectedDate = new Date($(this).val());
            if (!isNaN(selectedDate)) {
                selectedDate.setDate(selectedDate.getDate() + 1);
                let nextDate = selectedDate.toISOString().split('T')[0];
                $('.capitalize_date').val(nextDate);
                $('#capitalize_date_old').val(nextDate);
                depCapitalizeDate();
            }
        });

        function calculateTotals() {
            let totalQuantity = 0;
            let totalCurrentValue = 0;
            let totalSalvageValue = 0;
            let depreciationType = document.getElementById("depreciation_type").value;
            let method = document.getElementById("depreciation_method").value;



            $('.mrntableselectexcel tr').each(function() {
                const $row = $(this);
                const $salvageValueInput = $row.find('.salvage-value-input');
                const old_asset_salvage = parseFloat($('#salvage_value_asset').val()) || 0;
                const rdv = parseFloat($('#current_value_asset').val()) || 0;
                const $depRateInput = $row.find('.dep_per');
                const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                const currentValue = parseFloat($row.find('.current-value-input').val()) || 0;
                const depreciationPercentage = parseFloat($row.find('.salvage_per').val()) || 0;
                const usefulLife = parseFloat($row.find('.life').val()) || 0;
                const dep_percentage = $('#depreciation_rate').val() || 0;
                //const salvageValue = (currentValue * (depreciationPercentage / 100)).toFixed(2);
                const salvageValue = currentValue * old_asset_salvage / rdv;
                $salvageValueInput.val(parseFloat(salvageValue).toFixed(2));
                // Ensure all required values are provided
                if (!depreciationType || !currentValue || !depreciationPercentage || !usefulLife || !method) {
                    return;
                }

                // let depreciationRate = 0;
                // if (method === "SLM") {
                //     depreciationRate = ((((currentValue - salvageValue) / usefulLife) / currentValue) * 100)
                //         .toFixed(2);
                // } else if (method === "WDV") {
                //     depreciationRate = ((1 - Math.pow(salvageValue / currentValue, 1 / usefulLife)) * 100).toFixed(
                //         2);
                // }
                // //console.log(depreciationRate);

                // $depRateInput.val(depreciationRate);

                $depRateInput.val(dep_percentage);


                // Accumulate totals
                totalSalvageValue += parseFloat(salvageValue);
                totalQuantity += quantity;
                totalCurrentValue += currentValue;
            });

            $('#quantity').val(totalQuantity);


            let currentValueAsset = parseFloat($('#current_value_asset').val()) || 0;
            if (totalCurrentValue > currentValueAsset) {
                showToast('error', 'Total Current Value cannot be greater than Asset Current Value.');
            }

            $('#current_value').val(totalCurrentValue.toFixed(2));
            $('#salvage_value').val(totalSalvageValue.toFixed(2));


        }



        function initializeCategoryAutocomplete(selector) {

            let salvage_rate = '{{ $dep_percentage }}';
            $(selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ route('finance.fixed-asset.category-search') }}',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            q: request.term,
                        },
                        success: function(data) {
                            response(data.map(function(item) {
                                return {
                                    label: item.name,
                                    value: item.id,
                                    ledger: item.setup.ledger_id,
                                    life: item.setup.expected_life_years,
                                    salvage: item.setup.salvage_percentage ??
                                        salvage_rate,
                                };
                            }));
                        },
                        error: function() {
                            response([]);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    renderLedgerSelects();
                    const row = $(this).closest('tr');
                    row.find('.category').val(ui.item.value);
                    $(this).val(ui.item.label);
                    let excludedId = parseInt($('#ledger').val());
                    if (ui.item.ledger !== excludedId) {
                        row.find('.ledger').val(ui.item.ledger).trigger('change');
                    } else {
                        // Option is excluded, so clear the selection or do something else
                        row.find('.ledger').val('').trigger('change');
                    }
                    //row.find('.life').val(ui.item.life);
                    row.find('.salvage_per').val(ui.item.salvage);
                    syncInputAcrossSameAssets(this);
                    syncInputAcrossSameAssets($('.life'));
                    syncInputAcrossSameAssets($('.ledger'));
                    syncInputAcrossSameAssets($('.salvage_per'));
                    calculateTotals();



                    return false;
                },
                change: function(event, ui) {
                    const row = $(this).closest('tr');
                    if (!ui.item) {
                        const row = $(this).closest('tr');
                        row.find('.category').val('');
                        $(this).val('');
                        row.find('.ledger').empty().append(
                            `<option value="">Select</option>`
                        );
                        row.find('.ledger').val('').trigger('change');
                        row.find('.life').val('');
                        row.find('.salvage_per').val('');
                        syncInputAcrossSameAssets(this);
                        syncInputAcrossSameAssets($('.life'));
                        syncInputAcrossSameAssets($('.ledger'));
                        syncInputAcrossSameAssets($('.salvage_per'));
                        calculateTotals();
                    }
                    return false;
                }

            }).focus(function() {
                if (this.value === '') {
                    $(this).autocomplete('search');
                }
            });

        }

        function syncInputAcrossSameAssets(element) {
            const $this = $(element);
            const row = $this.closest('tr');
            $this.removeClass('is-invalid')

            const value = $this.val();
            const assetName = row.find('.asset-code-input').val().trim();

            // Get the first class that identifies the field (excluding utility classes)
            const fieldClass = $this.attr('class').split(' ').find(cls => ['life', 'ledger', 'ledger-group',
                'category-input', 'salvage_per', 'asset-name-input', 'category', 'capitalize_date'
            ].includes(cls));

            if (!fieldClass) return;

            $('.mrntableselectexcel tr').each(function() {
                const $otherRow = $(this);
                const otherAssetName = $otherRow.find('.asset-code-input').val().trim();

                if (otherAssetName === assetName && $otherRow[0] !== row[0]) {
                    const $target = $otherRow.find(`.${fieldClass}`);
                    if ($target.length) {
                        $target.val(value);
                        if (fieldClass === 'category-input')
                            $target.trigger('change');
                    }
                }
            });
            calculateTotals();
        }


        initializeCategoryAutocomplete('.category-input');
        const allLedgers = @json($ledgers);



        function renderLedgerSelects() {

            let excludedId = parseInt($('#ledger').val());

            console.log('Excluded ID:', excludedId);

            $('.ledger').each(function() {
                const $select = $(this);
                // Get current selected value from this select, convert to int
                let currentSelected = parseInt($select.val());

                $select.empty().append('<option value="">Select</option>');

                allLedgers.forEach(ledger => {
                    if (ledger.id !== excludedId) {
                        // Select only if ledger.id equals currentSelected
                        const isSelected = (ledger.id === currentSelected) ? 'selected' : '';
                        $select.append(
                            `<option value="${ledger.id}" ${isSelected}>${ledger.name}</option>`
                        );
                    }
                });
            });
        }


        function depCapitalizeDate() {
            let today = new Date().toISOString().split('T')[0];
            let capitalize_date = $('#capitalize_date_old').val();
            let expiry = $('#expiry_date').val();
            if (expiry == "") {
                return;
            }
            if (capitalize_date == "") {
                capitalize_date = '{{ $financialStartDate }}';
            }
            if ($('#last_dep_date').val() == "") {
                $('.capitalize_date').attr('min', capitalize_date).attr('max', expiry).prop('readonly', false).prop(
                    'required', true);

            } else {
                let lastDepDate = new Date($('#last_dep_date').val());
                lastDepDate.setDate(lastDepDate.getDate() + 1);
                let formattedDate = lastDepDate.toISOString().split('T')[0];
                $('.capitalize_date').removeAttr('min').removeAttr('max').prop('readonly', true).prop('required', false);
                $('.capitalize_date').val(formattedDate);

            }
            updateLife();
        }

        function clearSubAsset() {
            $('#subasset_search_input').val('');
            $('#current_value_asset').val("");
            $('#last_dep_date').val("")
                .removeAttr('min')
                .removeAttr('max')
                .prop('readonly', true).prop('required', false);
            $('#expiry_date').val("");
            $('#salvage_value_asset').val("");
            $('#sub_asset_id').val("");
            $('#ledger').val("");
            $('#ledger_group').val("");
            $('.capitalize_date').val("");
            $('#capitalize_date_old').val("");
            $('#depreciation_rate').val("");
            $('#depreciation_rate_year').val("");
            $('#useful_life').val("");
            $('#maintenance_schedule').val("");
            $('#current_value_asset').val("");
            $('#total_depreciation').val("");
            depCapitalizeDate();
            add_blank();

        }

        function clearAsset() {
            $('#asset_search_input').val('');
            $('#asset_id').val('');
            clearSubAsset();
        }


        $(document).on('change', '.capitalize_date, #expiry_date', function() {
            var $row = $(this).closest('tr');
            calculateUsefulLife($row);
        });

        function calculateUsefulLife($row) {
            var startDate = new Date($row.find('.capitalize_date').val());
            var endDate = new Date($('#expiry_date').val());

            if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                var years = endDate.getFullYear() - startDate.getFullYear();
                var months = endDate.getMonth() - startDate.getMonth();
                var days = endDate.getDate() - startDate.getDate();

                if (days < 0) {
                    months -= 1;
                    days += new Date(startDate.getFullYear(), startDate.getMonth() + 1, 0).getDate();
                }

                if (months < 0) {
                    years -= 1;
                    months += 12;
                }

                var totalYears = years + (months / 12) + (days / 365);
                $row.find('.life').val(totalYears.toFixed(2));
                if (startDate == endDate) {
                    $row.find('.life').val(0);
                }
            } else {
                $row.find('.life').val('');
            }
        }

        function updateLife() {
            $('.capitalize_date').each(function() {
                var $row = $(this).closest('tr');
                calculateUsefulLife($row);
            });
        }

        function applyFixedPrefixToInputs() {
            const selector = '.asset-code-input';
            let prefix = $('#subasset_search_input').val();

            if (!prefix) {
                return; // Exit if prefix is not set
            }

            prefix = prefix.trim().split(/\s+/)[0] + "#S";
            const inputs = document.querySelectorAll(selector);

            inputs.forEach(input => {
                // Set default value if needed
                if (!input.value.startsWith(prefix)) {
                    input.value = prefix+"01";
                }

                // Enforce prefix and allow only numbers after it
                input.addEventListener("input", function() {
                    if (!this.value.startsWith(prefix)) {
                        this.value = prefix;
                    }

                    // Extract the numeric part after prefix
                    let numericPart = this.value.slice(prefix.length).replace(/\D/g, '');
                    this.value = prefix + numericPart;
                });

                // Prevent deleting or navigating into the prefix
                input.addEventListener("keydown", function(e) {
                    if (
                        this.selectionStart <= prefix.length &&
                        (e.key === "Backspace" || e.key === "Delete" || e.key === "ArrowLeft")
                    ) {
                        e.preventDefault();
                    }
                });

                // Keep cursor after prefix on click
                input.addEventListener("click", function() {
                    if (this.selectionStart < prefix.length) {
                        this.setSelectionRange(prefix.length, prefix.length);
                    }
                });

                // Optional: force cursor after prefix on focus
                input.addEventListener("focus", function() {
                    if (this.selectionStart < prefix.length) {
                        this.setSelectionRange(prefix.length, prefix.length);
                    }
                });
                $(selector).trigger('change');
            });
        }

        function validateAssetCodes() {
            const inputs = document.querySelectorAll('.asset-code-input');
            let prefix = $('#subasset_search_input').val();

            if (!prefix) return true; // If no prefix, nothing to validate

            // Trim and extract first word + '#S'
            prefix = prefix.trim().split(/\s+/)[0] + "#S";

            let allValid = true;

            inputs.forEach(input => {
                const value = input.value.trim();
                if (value === prefix) {
                    allValid = false;

                    // Optional: visually mark invalid input
                    input.style.border = "1px solid red";
                } else {
                    input.style.border = ""; // Reset border if valid
                }
            });

            return allValid;
        }
    </script>
    <!-- END: Content-->
@endsection
