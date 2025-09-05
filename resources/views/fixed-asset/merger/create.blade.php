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
                                <h2 class="content-header-title float-start mb-0">Merger</h2>
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
                            <a href="{{ route('finance.fixed-asset.merger.index') }}"> <button
                                    class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</button>
                            </a>
                            <button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" type="button" id="save-draft-btn">
                                <i data-feather="save"></i> Save as Draft
                            </button>

                            <button type="submit" form="fixed-asset-merger-form" class="btn btn-primary btn-sm"
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
                        <form id="fixed-asset-merger-form" method="POST"
                            action="{{ route('finance.fixed-asset.merger.store') }}" enctype="multipart/form-data">

                            @csrf
                            <input type="hidden" name="sub_assets" id="sub_assets">
                            <input type="hidden" name="asset_details" id="asset_details">
                            <input type="hidden" name="doc_number_type" id="doc_number_type">
                            <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
                            <input type="hidden" name="doc_prefix" id="doc_prefix">
                            <input type="hidden" name="doc_suffix" id="doc_suffix">
                            <input type="hidden" name="doc_no" id="doc_no">
                            <input type="hidden" name="document_status" id="document_status" value="">
                            <input type="hidden" name="dep_type" id="depreciation_type" value="{{ $dep_type }}">
                            <input type="hidden" id="old_ledger">

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
                                                        <input type="text" class="form-control" id="document_number"
                                                            name="document_number" required>
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
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}">
                                                                    {{ $location->store_name }}</option>
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




                                <div class="card">
                                    <div class="card-body customernewsection-form">


                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Select Assets</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end">
                                                    <a href="#" class="btn btn-sm btn-outline-danger me-50"
                                                        id="delete">
                                                        <i data-feather="x-circle"></i> Delete</a>
                                                    <a id="addNewRowBtn" class="btn btn-sm btn-outline-primary">
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
                                                                            id="checkAll">
                                                                        <label class="form-check-label"
                                                                            for="Email"></label>
                                                                    </div>
                                                                </th>
                                                                <th width="200px">Asset Name & Code</th>
                                                                <th width="500px">Sub Assets & Code</th>
                                                                <th width="100px">Quantity</th>
                                                                <th class="text-end">Current Value</th>
                                                                <th class="text-end">Salvage Value</th>
                                                                <th width="200px">Last Dep. Date</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            <tr>
                                                                <td class="customernewsection-form">
                                                                    <input type="hidden" class="ledger">
                                                                    <div
                                                                        class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox"
                                                                            class="form-check-input row-check"
                                                                            id="Email">
                                                                        <label class="form-check-label"
                                                                            for="Email"></label>
                                                                    </div>
                                                                </td>
                                                                <td class="poprod-decpt">
                                                                    <input type="text" required
                                                                        class="form-control asset-search-input mw-100" />
                                                                    <input type="hidden" name="asset_id[]"
                                                                        class="asset_id" data-id="1"
                                                                        id="asset_id_1" />

                                                                </td>

                                                                <td class="poprod-decpt">
                                                                    <select id="sub_asset_id_1" name="sub_asset_id[]"
                                                                        data-id="1"
                                                                        class="form-select mw-100 select2 sub_asset_id"
                                                                        multiple required>
                                                                        <option disabled value="">Select</option>
                                                                        <!-- Will be filled via AJAX -->
                                                                    </select>
                                                                </td>
                                                                <td><input type="number" name="quantity[]"
                                                                        id="quantity_1" readonly data-id="1"
                                                                        class="form-control mw-100 quantity" /></td>
                                                                <td class="text-end"><input type="text"
                                                                        name="currentvalue[]" id="currentvalue_1"
                                                                        data-id="1"
                                                                        class="form-control mw-100 text-end currentvalue"
                                                                        readonly />
                                                                </td>
                                                                <td class="text-end"><input type="text"
                                                                        name="salvagevalue[]" id="salvagevalue_1"
                                                                        data-id="1"
                                                                        class="form-control mw-100 text-end salvagevalue"
                                                                        readonly />
                                                                </td>
                                                                <td><input type="date" name="last_dep_date[]"
                                                                        id="last_dep_date_1" data-id="1"
                                                                        class="form-control mw-100 last_dep_date"
                                                                        readonly />
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
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Category <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" name="category_id"
                                                                id="category" required>
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
                                                                <label class="form-label">IT Act Category <span
                                                                        class="text-danger"></span></label>
                                                                <select class="form-select select2" name="it_category_id"
                                                                    id="it_category">
                                                                    <option value=""
                                                                        {{ old('it_category') ? '' : 'selected' }}>
                                                                        Select</option>
                                                                        @foreach ($it_categories as $it_category)
                                                                            <option value="{{ $it_category->id }}" 
                                                                                {{ old('it_category') == $it_category->id ? 'selected' : '' }}>
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
                                                                id="asset_name" value="{{ old('asset_name') }}"
                                                                required />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Asset Code <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="asset_code"
                                                                oninput="this.value = this.value.toUpperCase();"
                                                                id="asset_code" value="{{ old('asset_code') }}"
                                                                required />
                                                            <span class="text-danger code_error"
                                                                style="font-size:12px"></span>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Quantity <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="quantity"
                                                                id="quantity" value="1" readonly />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Ledger <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2" name="ledger_id"
                                                                id="ledger" required>
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
                                                            <select class="form-select" name="ledger_group_id"
                                                                id="ledger_group" required>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Capitalize Date <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="date" class="form-control"
                                                                name="capitalize_date" id="capitalize_date"
                                                                value="{{ old('capitalize_date') }}"
                                                                min="{{ $financialStartDate }}"
                                                                max="{{ $financialEndDate }}" required />
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


                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Est. Useful Life (yrs) <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="number" class="form-control" name="useful_life"
                                                                id="useful_life" value="{{ old('useful_life') }}"
                                                                 required />
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

                                                    <div class="col-md-3">
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

                        </form>


                    </div>
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
        $(document).ready(function() {
            $('#category').val($('#old_category').val()).trigger('change');
        });
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })



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
            const subAssetCode = $('#sub_asset_id').val();
            genereateSubAssetRow(subAssetCode);
            applyFixedPrefixToInputs();
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
        document.getElementById('save-draft-btn').addEventListener('click', function(e) {
            $('.preloader').show();
            e.preventDefault(); // Prevent default form submission
            document.getElementById('document_status').value = 'draft';
            const allRows = [];

            $('.mrntableselectexcel tr').each(function() {
                const row = $(this);
                const rowId = row.find('.asset_id').attr('data-id');
                let sub_asset_codes = [];
                row.find(`#sub_asset_id_${rowId} option:selected`).each(function() {
                    sub_asset_codes.push($(this).text());
                });

                const rowData = {
                    asset_id: row.find(`#asset_id_${rowId}`).val(),
                    sub_asset_id: row.find(`#sub_asset_id_${rowId}`).val(), // array from select2
                    quantity: row.find(`#quantity_${rowId}`).val(),
                    sub_asset_code: sub_asset_codes,
                    currentvalue: row.find(`#currentvalue_${rowId}`).val(),
                    salvagevalue: row.find(`#salvagevalue_${rowId}`).val(),
                    last_dep_date: row.find(`#last_dep_date_${rowId}`).val(),
                };

                allRows.push(rowData);
            });


            $('#asset_details').val(JSON.stringify(allRows));
            if ($('#asset_code').hasClass('is-invalid')) {
                $('.preloader').hide();
                showToast('error', 'Code already exist.');
                return false;

            }
            if (!validateAssetCodes()) {
                $('.preloader').hide();
                showToast('error', 'Invalid Asset Code.');
                return false;
            }

            document.getElementById('fixed-asset-merger-form').submit();
        });


        $('#fixed-asset-merger-form').on('submit', function(e) {
            $('.preloader').show();
            e.preventDefault(); // Always prevent default first

            document.getElementById('document_status').value = 'submitted';
            const allRows = [];

            $('.mrntableselectexcel tr').each(function() {
                const row = $(this);
                const rowId = row.find('.asset_id').attr('data-id');
                let sub_asset_codes = [];
                row.find(`#sub_asset_id_${rowId} option:selected`).each(function() {
                    sub_asset_codes.push($(this).text());
                });

                const rowData = {
                    asset_id: row.find(`#asset_id_${rowId}`).val(),
                    sub_asset_id: row.find(`#sub_asset_id_${rowId}`).val(), // array from select2
                    quantity: row.find(`#quantity_${rowId}`).val(),
                    sub_asset_code: sub_asset_codes,
                    currentvalue: row.find(`#currentvalue_${rowId}`).val(),
                    salvagevalue: row.find(`#salvagevalue_${rowId}`).val(),
                    last_dep_date: row.find(`#last_dep_date_${rowId}`).val(),
                };

                allRows.push(rowData);
            });

            $('#asset_details').val(JSON.stringify(allRows));
            if ($('#asset_code').hasClass('is-invalid')) {
                $('.preloader').hide();
                showToast('error', 'Code already exist.');
                return false;

            }
            if (!validateAssetCodes()) {
                $('.preloader').hide();
                showToast('error', 'Invalid Asset Code.');
                return false;
            }


            this.submit();
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
        $('#old_category').on('change', function() {
            $('.mrntableselectexcel').empty();
            $('#addNewRowBtn').trigger('click');
            $('#category').val($(this).val()).trigger('change');

            loadLocation();

        });
        $('#category').on('change', function() {
            $('#ledger').val("").trigger('change');
            $('#ledger').trigger('change');
            $('#ledger_group').val("").trigger('change');
            $('#maintenance_schedule').val("");
            $('#useful_life').val("");
            updateDepreciationValues();

            var category_id = $(this).val();
            if (category_id) {
                $.ajax({
                    type: "GET",
                    url: "{{ route('finance.fixed-asset.setup.category') }}?category_id=" + category_id,
                    success: function(res) {
                        if (res) {
                            $('#ledger').val(res.ledger_id).trigger('change');
                            $('#ledger').trigger('change');
                            $('#ledger_group').val(res.ledger_group_id).trigger('change');
                            $('#maintenance_schedule').val(res.maintenance_schedule);
                            $('#useful_life').val(res.expected_life_years);
                            if (res.salvage_percentage)
                                $('#depreciation_percentage').val(res.salvage_percentage);
                            else
                                $('#depreciation_percentage').val('{{ $dep_percentage }}');
                            updateSum();
                            updateDepreciationValues();

                        }
                    }
                });
            }
        });
        $('#ledger').change(function() {
            if ($(this).val() == "") {
                return;
            }
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

        function updateDepreciationValues() {
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


            let salvageValue = (parseFloat($('#salvage_value').val())).toFixed(2);

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

        function initializeAssetAutocomplete(selector) {
            $(selector).on('keydown', function (e) {
                if (e.key === 'Backspace' || e.key === 'Delete') {
                    const row = $(this).closest('tr');
                    if (row.find('.asset_id').val() !== '') {
                          const row = $(this).closest('tr');
                    let subAssetSelect = row.find('.sub_asset_id');
                        subAssetSelect.empty();
                        row.find('.sub_asset_id').empty();
                        row.find('.ledger').val('');
                        renderLedgerSelects();

                        row.find('.asset_id').val('');
                        row.find('.quantity').val('');
                        row.find('.currentvalue').val('');
                        row.find('.salvagevalue').val('');
                        row.find('.last_dep_date').val('');
                        refreshAssetSelects();
                        updateSum();
                        //applyFixedPrefixToInputs();
                        
                    }
                }
            });
            $(selector).autocomplete({
              
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
                            ids: getAllAssetIds(),
                            category: $('#old_category').val(),
                            location: $('#location').val(),
                            cost_center: $('#cost_center').val(),
                        },
                        success: function(data) {
                            response(data.map(function(item) {
                                return {
                                    label: item.asset_code + ' (' + item.asset_name +
                                        ')',
                                    value: item.id,
                                    asset: item
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
                    const row = $(this).closest('tr');
                    const rowId = row.data('id'); // assuming you set `data-id` on the <tr>

                    // Set visible label and hidden ID
                    $(this).val(ui.item.label);
                    row.find('.asset_id').val(ui.item.value);

                    let subAssetSelect = row.find('.sub_asset_id');
                    let last_dep = row.find('.last_dep_date');
                    let ledger = row.find('.ledger');
                    subAssetSelect.html('<option value="">Loading...</option>');

                    $.ajax({
                        url: '{{ route('finance.fixed-asset.sub_asset') }}',
                        type: 'GET',
                        data: {
                            id: ui.item.value
                        },
                        success: function(response) {
                            subAssetSelect.empty();
                            subAssetSelect.html('<option disabled value="">Select</option>');
                            $.each(response, function(key, subAsset) {
                                subAssetSelect.append(
                                    '<option value="' + subAsset.id + '">' + subAsset
                                    .sub_asset_code + '</option>'
                                );
                            });

                            if (response[0].asset) {
                                last_dep.val('')
                                    .removeAttr('min')
                                    .removeAttr('max')
                                    .prop('readonly', true).prop('required', false);
                                ledger.val(response[0].asset.ledger_id);
                                renderLedgerSelects();
                                if (response[0].asset.last_dep_date != response[0].asset
                                    .capitalize_date) {
                                    let lastDepDate = new Date(response[0].asset.last_dep_date);
                                    lastDepDate.setDate(lastDepDate.getDate() - 1);
                                    let formatted = lastDepDate.toISOString().split('T')[0];
                                    let today = new Date().toISOString().split('T')[0];
                                    last_dep.val(formatted)
                                        .attr('min', formatted)
                                        .attr('max', today)
                                        .prop('readonly', false).prop('required', true);
                                } else {
                                    last_dep.val('')
                                        .removeAttr('min')
                                        .removeAttr('max')
                                        .prop('readonly', true).prop('required', false);
                                }
                            }
                            row.find('.quantity').val('');
                            row.find('.currentvalue').val('');
                            row.find('.salvagevalue').val('');
                            refreshAssetSelects();
                            updateSum();
                        },
                        error: function() {
                            showToast('error', 'Failed to load sub-assets.');
                        }
                    });
                    //applyFixedPrefixToInputs();

                    return false;
                },
                change: function(event, ui) {
                    const row = $(this).closest('tr');
                    let subAssetSelect = row.find('.sub_asset_id');
                    if (!ui.item) {
                        $(this).val('');
                        subAssetSelect.empty();
                        row.find('.sub_asset_id').empty();
                        row.find('.ledger').val('');
                        renderLedgerSelects();

                        row.find('.asset_id').val('');
                        row.find('.quantity').val('');
                        row.find('.currentvalue').val('');
                        row.find('.salvagevalue').val('');
                        row.find('.last_dep_date').val('');
                        refreshAssetSelects();
                        updateSum();
                    }
                    //applyFixedPrefixToInputs();
                }
            }).focus(function() {
                if (this.value === '') {
                    $(this).autocomplete('search');
                }
                return false;
            });
        }


        initializeAssetAutocomplete('.asset-search-input');

        // On Sub-Asset change, get value and last dep date
        $(document).on('change', '.sub_asset_id', function() {
            let subAssetIds = $(this).val();
            let row = $(this).data('id');
            let assetId = $('#asset_id_' + row).val();
            let totalCurrentValue = 0; // To accumulate values
            let responsesReceived = 0; // Counter to check when all requests are done
            if (subAssetIds && subAssetIds.length > 0) {
                subAssetIds.forEach(function(subAssetId) {
                    $.ajax({
                        url: '{{ route('finance.fixed-asset.sub_asset_details') }}',
                        type: 'GET',
                        data: {
                            id: assetId,
                            sub_asset_id: subAssetId,
                        },
                        success: function(response) {
                            let currentValue = parseFloat(response.current_value_after_dep) ||
                                0;
                            totalCurrentValue += currentValue;
                            responsesReceived++;
                            if (responsesReceived === subAssetIds.length) {
                                $('#currentvalue_' + row).val(totalCurrentValue.toFixed(2));
                                $('#quantity_' + row).val(responsesReceived);
                                updateSum();
                            }
                        },
                        error: function() {
                            showToast('error', 'Failed to load sub-asset details.');
                            responsesReceived++;

                            // Still check if all requests have completed (including failures)
                            if (responsesReceived === subAssetIds.length) {
                                $('#currentvalue_' + row).val(0);
                                $('#quantity_' + row).val(0);
                                updateSum();
                            }
                        }
                    });
                });
            } else {
                $('#currentvalue_' + row).val(0);
                $('#quantity_' + row).val(0);
                updateSum();
            }
            let firstNonEmptyDate = null;
            applyFixedPrefixToInputs();


        });
        $('.select2').select2();


        function updateSum() {
            let depreciationPercentage = parseFloat(document.getElementById("depreciation_percentage").value) || 0;

            let totalValue = 0;
            let totalQuantity = 0;
            let salvageValue = 0;

            $('.currentvalue').each(function() {
                let value = parseFloat($(this).val()) || 0;
                let salvageValue = (value * (depreciationPercentage / 100)).toFixed(2);
                $(this).closest('tr').find('.salvagevalue').val(salvageValue);
                totalValue += value;
            });
            $('.salvagevalue').each(function() {
                let value = parseFloat($(this).val()) || 0;
                salvageValue += value;
            });

            $('.quantity').each(function() {
                let qty = parseFloat($(this).val()) || 0;
                totalQuantity += qty;
            });

            // Example: Update totals in specific HTML elements
            $('#current_value').val(totalValue.toFixed(2));
            $('#salvage_value').val(salvageValue.toFixed(2));
            let allReadonly = true;
            let lastDepDate = '';

            $('.last_dep_date').each(function() {
                if (!$(this).prop('readonly') || $(this).val() != '') {
                    lastDepDate = $(this).val();
                    allReadonly = false;
                    return false; // Exit loop early
                }
            });

            if (allReadonly) {
                $('#capitalize_date').val('').attr('min', '{{ $financialStartDate }}').attr('max',
                    '{{ $financialEndDate }}').prop('readonly', false).prop('required', true);
            } else {
                let nextDate = new Date(lastDepDate);
                nextDate.setDate(nextDate.getDate() + 1); // Add 1 day

                // Format as yyyy-mm-dd
                let yyyy = nextDate.getFullYear();
                let mm = String(nextDate.getMonth() + 1).padStart(2, '0');
                let dd = String(nextDate.getDate()).padStart(2, '0');
                let formattedDate = `${yyyy}-${mm}-${dd}`;

                $('#capitalize_date').val(formattedDate).removeAttr('min').removeAttr('max').prop('readonly', true).prop(
                    'required', false);
            }
            updateDepreciationValues();
        }

        let rowCount = 1;

        $('#addNewRowBtn').on('click', function() {
            let allInputsFilled = true;

            $('.mrntableselectexcel').find('.asset-search-input, .sub_asset_id, .last_dep_date').each(function() {
                const input = this;

                // Only validate if NOT readonly
                if (!$(input).prop('readonly')) {
                    if (!input.checkValidity()) {
                        allInputsFilled = false;
                        input.reportValidity(); // Show default error message
                        return false; // Exit loop after first invalid input
                    } else {
                        $(input).removeClass('is-invalid');
                    }
                } else {
                    $(input).removeClass('is-invalid');
                }
            });

            if (!allInputsFilled) {
                // showToast('warning',
                //     'Please complete all input fields in the existing row(s) before adding a new one.');
                return;
            }


            rowCount++;
            let newRow = `
    <tr >
        <input type="hidden" class="ledger">
        <td class="customernewsection-form">
            <div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input row-check" id="Email_${rowCount}">
                <label class="form-check-label" for="Email_${rowCount}"></label>
            </div>
        </td>
        <td class="poprod-decpt">   
            <input type="text" class="form-control asset-search-input mw-100" required />
            <input type="hidden" name="asset_id[]" class="asset_id" data-id="${rowCount}" id="asset_id_${rowCount}"/> 
         </td>
        <td class="poprod-decpt">
            <select id="sub_asset_id_${rowCount}" name="sub_asset_id[]" data-id="${rowCount}"
                class="form-select mw-100 select2 sub_asset_id" multiple required>
                <option value="" disabled>Select</option>
                <!-- Will be filled via AJAX -->
            </select>
        </td>
        <td><input type="number" name="quantity[]" id="quantity_${rowCount}" readonly data-id="${rowCount}"
            class="form-control mw-100 quantity" /></td>
        <td><input type="text" name="currentvalue[]" id="currentvalue_${rowCount}" data-id="${rowCount}"
            class="form-control mw-100 text-end currentvalue" readonly /></td>
              <td class="text-end"><input type="text" name="salvagevalue[]" id="salvagevalue_${rowCount}" data-id="${rowCount}"
                                                                    class="form-control mw-100 text-end salvagevalue" readonly/>
                                                            </td>
        <td><input type="date" name="last_dep_date[]" id="last_dep_date_${rowCount}" data-id="${rowCount}"
            class="form-control mw-100 last_dep_date" readonly /></td>
    </tr>
    `;

            $('.mrntableselectexcel').append(newRow);
            $(".select2").select2();
            refreshAssetSelects();
            initializeAssetAutocomplete('.asset-search-input');
        });

        function refreshAssetSelects() {
            let selectedAssets = [];

            // Collect all selected asset values
            $('.asset_id').each(function() {
                let val = $(this).val();
                if (val) {
                    selectedAssets.push(val);
                }
            });

            // Disable already selected options in other selects
            $('.asset_id').each(function() {
                let currentSelect = $(this);
                let currentVal = currentSelect.val();

                currentSelect.find('option').each(function() {
                    let optionVal = $(this).val();

                    if (optionVal === "") return; // skip placeholder

                    if (selectedAssets.includes(optionVal) && optionVal !== currentVal) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                });
            });

        }



        $('#delete').on('click', function() {
            let $rows = $('.mrntableselectexcel tr');
            let $checked = $rows.find('.row-check:checked');

            // Prevent deletion if only one row exists
            if ($rows.length <= 1) {
                showToast('error', 'At least one row is required.');
                return;
            }

            // Prevent deletion if checked rows would remove all
            if ($rows.length - $checked.length < 1) {
                showToast('error', 'You must keep at least one row.');
                return;
            }

            // Remove only the checked rows
            $checked.closest('tr').remove();
            updateSum();
        });
        $('#checkAll').on('change', function() {
            let isChecked = $(this).is(':checked');
            $('.mrntableselectexcel .row-check').prop('checked', isChecked);
        });

        function getAllRowsAsJson() {
            const allRows = [];

            $('.mrntableselectexcel tr').each(function() {
                const row = $(this);
                const rowId = row.find('.asset_id').attr('data-id');

                const rowData = {
                    asset_id: row.find(`#asset_id_${rowId}`).val(),
                    sub_asset_id: row.find(`#sub_asset_id_${rowId}`).val(),
                    sub_asset_code: row.find(`#sub_asset_id_${rowId} option:selected`).text(),
                    quantity: row.find(`#quantity_${rowId}`).val(),
                    currentvalue: row.find(`#currentvalue_${rowId}`).val(),
                    last_dep_date: row.find(`#last_dep_date_${rowId}`).val(),
                };

                allRows.push(rowData);
            });

            return allRows;
        }
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







        function getAllAssetIds() {
            let assetIds = [];

            $('.asset_id').each(function() {
                let val = $(this).val();
                if (val) {
                    assetIds.push(parseFloat(val));
                }
            });

            return assetIds;
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
        $('#asset_code').on('input', function() {
            const assetCode = $('#asset_code').val();
            $.ajax({
                url: '{{ route('finance.fixed-asset.check-code') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    code: assetCode
                },
                success: function(response) {
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
        $(document).ready(function() {
            $(document).on('change', '.last_dep_date', function() {
                const changedValue = $(this).val();

                // Validate the date
                if (!changedValue || isNaN(new Date(changedValue).getTime())) return;

                // Update all non-readonly `.last_dep_date` inputs
                $('.last_dep_date').each(function() {
                    if (!$(this).prop('readonly')) {
                        $(this).val(changedValue);
                    }
                });

                // Compute and set #capitalize_date to next day
                const nextDate = new Date(changedValue);
                nextDate.setDate(nextDate.getDate() + 1);

                const yyyy = nextDate.getFullYear();
                const mm = String(nextDate.getMonth() + 1).padStart(2, '0');
                const dd = String(nextDate.getDate()).padStart(2, '0');
                const formattedDate = `${yyyy}-${mm}-${dd}`;

                $('#capitalize_date')
                    .val(formattedDate)
                    .removeAttr('min')
                    .removeAttr('max')
                    .prop('readonly', true).prop('required', false);
            });
        });

        const allLedgers = @json($ledgers);


        function appendAllLedgers($select) {
            $select.empty().append('<option value="">Select</option>');
            allLedgers.forEach(ledger => {
                $select.append(`<option value="${ledger.id}">${ledger.name}</option>`);
            });
        }


        function renderLedgerSelects() {
            // Collect all selected ledger IDs from all .ledger dropdowns
            const selectedLedgerIds = [];
            $('.ledger').each(function() {
                const val = $(this).val();
                if (val) {
                    selectedLedgerIds.push(val.toString());
                }
            });

            const $thisSelect = $('#ledger');
            const currentVal = $thisSelect.val(); // not used now since we will exclude everything in selectedLedgerIds

            $thisSelect.empty().append('<option value="">Select</option>');

            allLedgers.forEach(ledger => {
                const ledgerIdStr = ledger.id.toString();

                // Exclude if this ledger ID is already selected anywhere
                if (!selectedLedgerIds.includes(ledgerIdStr)) {
                    $thisSelect.append(`<option value="${ledger.id}">${ledger.name}</option>`);
                }
            });
        }
         $('#useful_life').on('input', function() {
            updateSum();
            updateDepreciationValues();
           });
        
        $('.code_error').css('font-size', '12px');
        
        
        function applyFixedPrefixToInputs() {
            const selector = '#asset_code';
            let prefix = $('.sub_asset_id').first().find('option:selected').first().text();
            //console.log("prefix"+prefix);

            if (!prefix) {
                return; // Exit if prefix is not set
            }

            prefix = prefix.trim().split(/\s+/)[0] + "#M";
            const input = document.getElementById('asset_code');

            // Set default value if needed
                if (!input.value.startsWith(prefix)) {
                    input.value = prefix+"01";
                }

                input.addEventListener("input", function (e) {
                    const cursorPosition = this.selectionStart;

                    // If prefix is missing or changed, reset it
                    if (!this.value.startsWith(prefix)) {
                        this.value = prefix;
                    }

                    // Only allow numbers after the prefix
                    let valueAfterPrefix = this.value.slice(prefix.length);
                    valueAfterPrefix = valueAfterPrefix.replace(/\D/g, '');

                    this.value = prefix + valueAfterPrefix;

                    // Restore cursor position
                    this.setSelectionRange(cursorPosition, cursorPosition);
                });


                // Enforce prefix and allow only numbers after it
                /*input.addEventListener("input", function() {
                    if (!this.value.startsWith(prefix)) {
                        this.value = prefix;
                    }

                    // Extract the numeric part after prefix
                    let numericPart = this.value.slice(prefix.length).replace(/\D/g, '');
                    this.value = prefix + numericPart;
                });*/

                // Prevent deleting or navigating into the prefix
                input.addEventListener("keydown", function(e) {
                    if (this.selectionStart <= prefix.length &&
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
        }
        function validateAssetCodes() {
            let prefix = $('.sub_asset_id').first().find('option:selected').first().text();
            const inputs = document.getElementById('asset_code');
            
            if (!prefix) return true; // If no prefix, nothing to validate

            // Trim and extract first word + '#S'
            prefix = prefix.trim().split(/\s+/)[0] + "#M";

            let allValid = true;

                const value = inputs.value;
                if (value === prefix) {
                    allValid = false;
                    inputs.style.border = "1px solid red";
                } else {
                    inputs.style.border = ""; // Reset border if valid
                }
            

            return allValid;
        }


    </script>
    <!-- END: Content-->
@endsection
