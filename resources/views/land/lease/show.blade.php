@extends('layouts.app')
@php $source_id = request()->has('revisionNumber')&&request()->input('revision_number')!=""?$lease->source_id:$lease->id; @endphp

@section('title', 'Lease Details')
@section('styles')
    <style>
        .po-order-detail tr td .form-control {
            max-width: unset;
            /* Or max-width: none; */
        }
    </style>
@endsection
@section('content')
    <!-- BEGIN: Content-->

    <?php
    $mainbadgeClass = match ($lease->approvalStatus) {
        'approve' => 'success',
        'approval_not_required' => 'success',
        'draft' => 'warning',
        'submitted' => 'info',
        'partially_approved' => 'warning',
        'renew' => 'warning',
        default => 'danger',
    };
    ?>

    <div id="lease-form" class="lease-form">

        <div id="other_charges_hidden_fields"></div>
        <input type="hidden" name="status" id="status" value="{{ $lease->status }}">
        <input type="hidden" name="edit_id" value="{{ $lease->id }}">

        <input type="hidden" id="tax_percentage" value="">

        <div class="app-content content ">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">View Details</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('lease.index') }}">Lease</a>
                                            </li>
                                            <li class="breadcrumb-item active">View</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6  mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <button onClick="javascript: history.go(-1)"
                                    class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
                                    Back</button>
                                <a class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#reminder"
                                    data-bs-toggle="modal"><i data-feather="bell"></i> Action</a>



                                @if ($buttons['approve'])
                                    <a data-bs-toggle="modal" data-bs-target="#approved" class="btn btn-success btn-sm"><i
                                            data-feather="check-circle"></i> Approve</a>
                                    <a class="btn btn-danger btn-sm" data-bs-target="#reject" data-bs-toggle="modal"><i
                                            data-feather="x-circle"></i> Reject</a>
                                @endif


                                @if ($buttons['amend'])
                                    <a type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm"
                                        class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i>
                                        Amendment</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-body">
                    <section id="basic-datatable">
                        <div class="row">
                            <div class="col-12">

                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="card">
                                    <div class="card-body customernewsection-form">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div
                                                    class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                    <div>
                                                        <h4 class="card-title text-theme">Land/Plot Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                    <div class="header-right">
                                                        <span
                                                            class="badge rounded-pill badge-light-{{ $mainbadgeClass }}">{{ strtoupper($lease->approvalStatus) }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="book_id" id="series" disabled>
                                                            <option value="">Select</option>
                                                            @foreach ($book_type as $data)
                                                                <option value="{{ $data->id }}"
                                                                    {{ old('book_id', isset($lease) ? $lease->book_id : '') == $data->id ? 'selected' : '' }}>
                                                                    {{ $data->book_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('book_id')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="document_no"
                                                            id="document_no"
                                                            value="{{ old('document_no', isset($lease) ? $lease->document_no : '') }}"
                                                            readonly>
                                                        @error('document_no')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control" name="document_date"
                                                            id="document_date"
                                                            value="{{ old('document_date', isset($lease) ? $lease->document_date : '') }}"
                                                            readonly>
                                                        @error('document_date')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Reference No.</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="reference_no"
                                                            id="reference_no"
                                                            value="{{ old('reference_no', isset($lease) ? $lease->reference_no : '') }}"
                                                            readonly>
                                                        @error('reference_no')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                            </div>
                                            @if (isset($page) && $page == 'view_detail')
                                                <div class="col-md-4">
                                                    <div
                                                        class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                                        <h5
                                                            class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                                            <strong><i data-feather="arrow-right-circle"></i> Approval
                                                                History {{ $currNumber }}</strong>
                                                            <strong
                                                                class="badge rounded-pill badge-light-secondary amendmentselect">Rev.
                                                                No.
                                                                <select class="form-select revisionNumber">

                                                                    <option value=""
                                                                        @if ($currNumber == '') selected @endif>
                                                                        None</option>
                                                                    @foreach ($revisionNumbers as $revisionNumber)
                                                                        @if ($revisionNumber != 0)
                                                                            <option
                                                                                @if ($currNumber == $revisionNumber) selected @endif
                                                                                value="{{ $revisionNumber }}">
                                                                                {{ $revisionNumber }}</option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                            </strong>
                                                        </h5>
                                                        <ul class="timeline ms-50 newdashtimline ">
                                                            @foreach ($history as $his)
                                                                <?php
                                                                $badgeClass = match ($his->approval_type) {
                                                                    'approve' => 'success',
                                                                    'approval_not_required' => 'success',
                                                                    'draft' => 'warning',
                                                                    'Reminder' => 'info',
                                                                    'Renew' => 'success',
                                                                    'submitted' => 'info',
                                                                    'submit' => 'info',
                                                                    'partially_approved' => 'warning',
                                                                    default => 'danger',
                                                                };
                                                                ?>
                                                                <li class="timeline-item">
                                                                    <span
                                                                        class="timeline-point timeline-point-indicator timeline-point-{{ $badgeClass }}"></span>
                                                                    <div class="timeline-event">
                                                                        <div
                                                                            class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                            <h6>{{ucfirst($his->name ?? $his?->user?->name ?? 'NA')}}</h6>
                                                                            <span
                                                                                class="badge rounded-pill badge-light-{{ $badgeClass }}">{{ ucfirst($his->approval_type) }}</span>
                                                                        </div>
                                                                        <h5>({{ $his->approval_date }})</h5>
                                                                        <p>{{ $his->remarks }}</p>
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>


                                <div class="row outerclickfunc">
                                    <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">Customer Details</h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Customer <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select select2 customer"
                                                                name="customer_id" id="customer" readonly>
                                                                <option value="">Select</option>
                                                                @foreach ($customers as $customer)
                                                                    <option value="{{ $customer->id }}"
                                                                        {{ old('customer_id', isset($lease) ? $lease->customer_id : '') == $customer->id ? 'selected' : '' }}
                                                                        data-currency-id={{ $customer->currency_id }}>
                                                                        {{ $customer->company_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>


                                                            @error('customer_id')
                                                                <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <div class="mb-1">
                                                            <label class="form-label">Currency <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="currency_name"
                                                                readonly value="{{ $lease->currency->name ?? '' }}" />
                                                            <input type="hidden" name="currency_id" id="currency"
                                                                value="{{ $lease->currency->id ?? '' }}" />
                                                        </div>
                                                        @error('currency_id')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-2">
                                                        <div class="mb-1">
                                                            <label class="form-label">Exchange Rate <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="hidden" name="exchage_rate" id="exchange_rate"
                                                                value="{{ $lease->exchage_rate ?? '' }}" />
                                                            <input type="text" class="form-control" readonly
                                                                id="exchange_rate_amount"
                                                                value="{{ $lease->exchage_rate ?? '' }}" />

                                                        </div>
                                                        @error('exchage_rate')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class="mb-1">
                                                            <label class="form-label w-100">Billing Address <span
                                                                    class="text-danger">*</span> <a
                                                                    href="#edit_address_model" data-bs-toggle="modal"
                                                                    class="float-end font-small-2"><i
                                                                        data-feather='edit-3'></i> Edit</a></label>
                                                            <input type="text" class="form-control" readonly
                                                                name="billing_address" id="billing_address"
                                                                value="{{ $lease->billing_address }}" />
                                                        </div>
                                                        @error('reference_no')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row outerclickfunc">
                                    <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">Land Details</h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Select Land Parcel <span
                                                                    class="text-danger">*</span></label>
                                                                    <select class="form-select select2" name="land"
                                                                    id="land" disabled>
                                                                    <option value="">Select</option>
                                                                    @foreach ($lands as $land)
                                                                        <option value="{{ $land->id }}"
                                                                            {{ $lease->land_id == $land->id ? 'selected' : '' }}
                                                                            data-sizeland="{{ $land->plot_area }}"
                                                                            data-landaddress="{{ $land->address . ', ' . $land->district . ', ' . $land->state . ', ' . $land->pincode }}">
                                                                            {{ $land->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                              </div>
                                                    </div>

                                                    <div class="col-md-2 action-button mt-25">
                                                        <button data-bs-toggle="modal" type="button" disabled
                                                            data-bs-target="#land_details_model"
                                                            class="btn btn-outline-primary btn-sm mt-2 w-100">
                                                            <i data-feather="search"></i>
                                                            Find Land
                                                        </button>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Size of Land <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" readonly class="form-control"
                                                                id="land_size"
                                                                value="{{ $lease->plots[0]->land->plot_area ?? '' }}"
                                                                readonly>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-4">
                                                        <div class="mb-1">
                                                            <label class="form-label">Location of Land <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" readonly class="form-control"
                                                                id="land_location"
                                                                value="{{ $lease->plots[0]->land->address ?? '' }}"
                                                                readonly>
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
                                                        <h4 class="card-title text-theme">Property Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end" hidden>
                                                    <a href="#" class="btn btn-sm btn-outline-danger me-50"
                                                        id="delete_plot_rows">
                                                        <i data-feather="x-circle"></i> Delete</a>
                                                    <a href="#" class="btn btn-sm btn-outline-primary"
                                                        id="add_new_plot_row">
                                                        <i data-feather="plus"></i> Add New Plot</a>
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
                                                                <th width="200px">Plot</th>
                                                                <th>Khasara No.</th>
                                                                <th>Area of Plot</th>
                                                                <th>Dimension</th>
                                                                <th>Valuation</th>
                                                                <th width="200">Address</th>
                                                                <th width="150">Property Type</th>
                                                                <th width="130" class="text-end">Lease Amt</th>
                                                                <th width="130">Other Charges</th>
                                                                <th width="130" class="text-end">Total</th>
                                                            </tr>
                                                        </thead>
                                                        @php
                                                            $totalLeaseAmount = 0;
                                                            $totalOtherCharges = 0;
                                                            $totalTotalAmount = 0;
                                                        @endphp
                                                        <tbody class="mrntableselectexcel">
                                                            @foreach ($lease->plots as $plot)
                                                                @php
                                                                    // Accumulate the values
                                                                    $totalLeaseAmount += $plot->lease_amount ?? 0;
                                                                    $totalOtherCharges += $plot->other_charges ?? 0;
                                                                    $totalTotalAmount += $plot->total_amount ?? 0;
                                                                @endphp
                                                                @if (
                                                                    !empty($plot->plot->document_no) ||
                                                                        !empty($plot->plot->khasara_no) ||
                                                                        !empty($plot->plot->dimension) ||
                                                                        !empty($plot->plot->plot_valuation) ||
                                                                        !empty($plot->plot->address) ||
                                                                        !empty($plot->plot->property_type) ||
                                                                        !empty($plot->property_type))
                                                                    <tr>
                                                                        <td class="customernewsection-form">
                                                                            <div
                                                                                class="form-check form-check-primary custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    class="form-check-input"
                                                                                    id="${uniqueId}">
                                                                                <label class="form-check-label"
                                                                                    for="${uniqueId}"></label>
                                                                            </div>
                                                                        </td>
                                                                        <td class="poprod-decpt">
                                                                            <input type="hidden"
                                                                                name="plot_details[${rowCount}][land_parcel_id]"
                                                                                class="land-parcel-id"
                                                                                value="{{ $plot->land_parcel_id ?? '' }}">
                                                                            <input type="hidden"
                                                                                name="plot_details[${rowCount}][land_plot_id]"
                                                                                class="land-plot-id"
                                                                                value="{{ $plot->land_plot_id ?? '' }}">

                                                                            <input type="text"
                                                                                name="plot_details[${rowCount}][plot_document_no]"
                                                                                value="{{ $plot->plot->document_no ?? '' }}"
                                                                                class="form-control mw-100 ledgerselecct mb-25" />
                                                                        </td>
                                                                        <td><input type="text"
                                                                                name="plot_details[${rowCount}][khasara_no]"
                                                                                value="{{ $plot->plot->khasara_no ?? '' }}"
                                                                                class="form-control mw-100" /></td>
                                                                        <td><input type="text"
                                                                                name="plot_details[${rowCount}][plot_area]"
                                                                                value="{{ $plot->plot->plot_area ?? '' }}"
                                                                                class="form-control mw-100" /></td>
                                                                        <td><input type="text"
                                                                                name="plot_details[${rowCount}][dimension]"
                                                                                value="{{ $plot->plot->dimension ?? '' }}"
                                                                                class="form-control mw-100" /></td>
                                                                        <td><input type="text"
                                                                                name="plot_details[${rowCount}][plot_valuation]"
                                                                                value="{{ $plot->plot->plot_valuation ?? '' }}"
                                                                                class="form-control mw-100" /></td>
                                                                        <td><input type="text"
                                                                                name="plot_details[${rowCount}][address]"
                                                                                value="{{ $plot->plot->address ?? '' }}"
                                                                                class="form-control mw-100" />
                                                                        </td>
                                                                        <td>
                                                                            <input type="text"
                                                                                name="plot_details[${rowCount}][land_property_type]"
                                                                                value="{{ $plot->property_type ?? '' }}"
                                                                                class="form-control mw-100" />
                                                                        </td>
                                                                        </td>
                                                                        <td><input type="number"
                                                                                class="form-control mw-100 text-end"
                                                                                name="plot_details[${rowCount}][land_lease_amount]"
                                                                                id="add_lease_amount" placehonder="00"
                                                                                value="{{ $plot->lease_amount ?? '' }}" />
                                                                        </td>
                                                                        <td>
                                                                            <div
                                                                                class="position-relative d-flex align-items-center">
                                                                                <input type="number"
                                                                                    class="form-control mw-100 text-end"
                                                                                    name="plot_details[${rowCount}][land_other_charges]"
                                                                                    id="add_other_amount"
                                                                                    value="{{ $plot->other_charges ?? '' }}"
                                                                                    style="width: 70px" readonly />
                                                                                <input type="hidden"
                                                                                    class="form-control mw-100 text-end"
                                                                                    name="plot_details[${rowCount}][land_other_charges_json]"
                                                                                    value="{{ $plot->other_charges_json ?? '' }}"
                                                                                    id="add_other_amount_json"
                                                                                    placeholder="00" style="width: 70px"
                                                                                    readonly />
                                                                                <div class="ms-50">
                                                                                    <button type="button"
                                                                                        data-id="{{ $plot->land_plot_id }}"
                                                                                        data-otherdata="{{ $plot->other_charges_json }}"
                                                                                        data-otherdatatotal="{{ $plot->other_charges }}"
                                                                                        data-leaseamount="{{ $plot->lease_amount }}"
                                                                                        class="btn p-25 btn-sm btn-outline-secondary editmodel"
                                                                                        style="font-size: 10px">Add</button>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td><input type="number" placeholder="00"
                                                                                class="form-control mw-100 text-end"
                                                                                name="plot_details[${rowCount}][land_total_amount]"
                                                                                id="add_total_plot_amount" readonly
                                                                                value="{{ $plot->total_amount ?? '' }}" />
                                                                        </td>
                                                                    </tr>
                                                                @endif
                                                            @endforeach
                                                        </tbody>


                                                        <tfoot>

                                                            <tr class="totalsubheadpodetail">
                                                                <td colspan="7"></td>
                                                                <td class="text-end">&nbsp;</td>
                                                                <td class="text-end" id="total_lease_amount" readonly>
                                                                    {{ $totalLeaseAmount }}</td>
                                                                <td class="text-end" id="total_other_charges" readonly>
                                                                    {{ $totalOtherCharges }}</td>
                                                                <td class="text-end" id="total_plots_amount" readonly>
                                                                    {{ $totalTotalAmount }}</td>
                                                            </tr>
                                                            <tr valign="top">
                                                                <td colspan="8" rowspan="10">
                                                                    <table class="table border">
                                                                        <tr>
                                                                            <td class="p-0">
                                                                                <h6
                                                                                    class="text-dark mb-0 bg-light-primary py-1 px-50">
                                                                                    <strong>Agreement Details</strong>
                                                                                </h6>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class="agreemformlease">
                                                                                <div class="row">

                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label class="form-label">Agreement
                                                                                            No <span
                                                                                                class="text-danger">*</span></label>
                                                                                        <input type="text"
                                                                                            name="agreement_no"
                                                                                            class="form-control"
                                                                                            value="{{ $lease->agreement_no ?? '' }}"
                                                                                            required readonly>
                                                                                    </div>

                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label class="form-label">Lease
                                                                                            Time (Yrs) <span
                                                                                                class="text-danger">*</span></label>
                                                                                        <input type="number"
                                                                                            id="leaseTime"
                                                                                            name="lease_time"
                                                                                            class="form-control"
                                                                                            value="{{ $lease->lease_time ?? '' }}"
                                                                                            placeholder="Enter lease time in years"
                                                                                            required readonly>
                                                                                    </div>

                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label class="form-label">Lease
                                                                                            Start Date<span
                                                                                                class="text-danger">*</span></label>
                                                                                        <input type="date"
                                                                                            id="leaseStartDate"
                                                                                            name="lease_start_date"
                                                                                            class="form-control"
                                                                                            value="{{ $lease->lease_start_date ?? '' }}"
                                                                                            required
                                                                                            {{ old('lease_start_date') }}
                                                                                            readonly>
                                                                                    </div>

                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label class="form-label">Lease End
                                                                                            Date</label>
                                                                                        <input type="date"
                                                                                            id="leaseEndDate"
                                                                                            name="lease_end_date" readonly
                                                                                            class="form-control"
                                                                                            value="{{ $lease->lease_end_date ?? '' }}"
                                                                                            {{ old('lease_end_date') }}
                                                                                            readonly>
                                                                                    </div>

                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label class="form-label">Repayment
                                                                                            Period Type <span
                                                                                                class="text-danger">*</span></label>

                                                                                        <select class="form-select"
                                                                                            name="repayment_period_type"
                                                                                            id="repayment_period_type"
                                                                                            required readonly>
                                                                                            <option value="" disabled
                                                                                                {{ $lease->repayment_period_type == '' ? 'selected' : '' }}>
                                                                                                Select</option>
                                                                                            <option value="monthly"
                                                                                                {{ $lease->repayment_period_type == 'monthly' ? 'selected' : '' }}>
                                                                                                Monthly</option>
                                                                                            <option value="quarterly"
                                                                                                {{ $lease->repayment_period_type == 'quarterly' ? 'selected' : '' }}>
                                                                                                Quarterly</option>
                                                                                            <option value="yearly"
                                                                                                {{ $lease->repayment_period_type == 'yearly' ? 'selected' : '' }}>
                                                                                                Yearly</option>
                                                                                        </select>
                                                                                    </div>

                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label class="form-label">Repayment
                                                                                            Period <span
                                                                                                class="text-danger">*</span></label>

                                                                                        <input type="number" readonly
                                                                                            id="repaymentPeriod"
                                                                                            name="repayment_period"
                                                                                            class="form-control"
                                                                                            value="{{ $lease->repayment_period ?? '' }}"
                                                                                            readonly>
                                                                                    </div>

                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label class="form-label">Security
                                                                                            Deposit</label>
                                                                                        <input type="text"
                                                                                            class="form-control"
                                                                                            name="security_deposit"
                                                                                            value="{{ $lease->security_deposit ?? '' }}"
                                                                                            readonly>
                                                                                    </div>

                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label class="form-label">Deposit
                                                                                            Refundable</label>
                                                                                        <div class="demo-inline-spacing">
                                                                                            <div
                                                                                                class="form-check form-check-primary mt-25">
                                                                                                <input type="radio"
                                                                                                    id="customColorRadio3"
                                                                                                    name="deposit_refundable"
                                                                                                    class="form-check-input"
                                                                                                    {{ $lease->deposit_refundable == 1 ? 'checked' : '' }}
                                                                                                    value="1"
                                                                                                    readonly>
                                                                                                <label
                                                                                                    class="form-check-label fw-bolder"
                                                                                                    for="customColorRadio3">Yes</label>
                                                                                            </div>
                                                                                            <div
                                                                                                class="form-check form-check-primary mt-25">
                                                                                                <input type="radio"
                                                                                                    id="customColorRadio4"
                                                                                                    name="deposit_refundable"
                                                                                                    class="form-check-input"
                                                                                                    {{ $lease->deposit_refundable == 0 ? 'checked' : '' }}
                                                                                                    value="0"
                                                                                                    readonly>
                                                                                                <label
                                                                                                    class="form-check-label fw-bolder"
                                                                                                    for="customColorRadio4">No</label>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>


                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label
                                                                                            class="form-label">Processing
                                                                                            Fee</label>
                                                                                        <input type="text"
                                                                                            class="form-control"
                                                                                            name="processing_fee"
                                                                                            value="{{ $lease->processing_fee ?? '' }}"
                                                                                            readonly>
                                                                                    </div>

                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label class="form-label">Lease
                                                                                            Increment %</label>
                                                                                        <input type="text"
                                                                                            class="form-control"
                                                                                            name="lease_increment"
                                                                                            value="{{ $lease->lease_increment ?? '' }}"
                                                                                            readonly>
                                                                                    </div>

                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label class="form-label">Lease
                                                                                            Increment Duration (Yrs)
                                                                                        </label>
                                                                                        <input type="text"
                                                                                            class="form-control"
                                                                                            name="lease_increment_duration"
                                                                                            value="{{ $lease->lease_increment_duration ?? '' }}"
                                                                                            readonly>
                                                                                    </div>

                                                                                </div>

                                                                                <div class="row">
                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label class="form-label">Grace
                                                                                            Period (Days) </label>
                                                                                        <input type="text"
                                                                                            class="form-control"
                                                                                            name="grace_period"
                                                                                            value="{{ $lease->grace_period ?? '' }}"
                                                                                            readonly>
                                                                                    </div>

                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label class="form-label">Late Fee
                                                                                            %</label>
                                                                                        <input type="text"
                                                                                            class="form-control"
                                                                                            id="lateFeePercentage"
                                                                                            name="late_fee"
                                                                                            value="{{ $lease->late_fee ?? '' }}"
                                                                                            readonly>
                                                                                    </div>

                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label class="form-label">Late Fee
                                                                                            Value</label>
                                                                                        <input type="text"
                                                                                            class="form-control"
                                                                                            id="lateFeeValue"
                                                                                            name="late_fee_value"
                                                                                            value="{{ $lease->late_fee_value ?? '' }}"
                                                                                            readonly>
                                                                                    </div>

                                                                                    <div class="col-md-3 mb-1">
                                                                                        <label class="form-label">Late Fee
                                                                                            Duration (Days) </label>
                                                                                        <input type="text"
                                                                                            class="form-control"
                                                                                            name="late_fee_duration"
                                                                                            value="{{ $lease->late_fee_duration ?? '' }}"
                                                                                            readonly>
                                                                                        <input type="hidden"
                                                                                            class="form-control"
                                                                                            id="lease_sub_total"
                                                                                            name="sub_total_amount"
                                                                                            value="{{ $lease->sub_total_amount ?? '' }}">
                                                                                        <input type="hidden"
                                                                                            class="form-control"
                                                                                            id="lease_other_charges"
                                                                                            name="lease_other_charges"
                                                                                            value="{{ $lease->extra_charges ?? '' }}">
                                                                                        <input type="hidden"
                                                                                            class="form-control"
                                                                                            id="lease_total_installment"
                                                                                            name="total_amount"
                                                                                            value="{{ $lease->sub_total_amount + $lease->extra_charges + $lease->otherextra_charges ?? 0 }}">
                                                                                        <input type="hidden"
                                                                                            class="form-control"
                                                                                            id="lease_extra_charges"
                                                                                            name="lease_extra_charges"
                                                                                            value="{{ $lease->otherextra_charges }}">
                                                                                        <input type="hidden"
                                                                                            class="form-control"
                                                                                            id="tax_amount"
                                                                                            name="tax_amount"
                                                                                            value="{{ $lease->tax_amount }}">
                                                                                        <input type="hidden"
                                                                                            class="form-control"
                                                                                            id="lease_installment_cost"
                                                                                            name="lease_installment_cost"
                                                                                            value="{{ $lease->installment_amount }}">

                                                                                    </div>


                                                                                </div>

                                                                            </td>
                                                                        </tr>

                                                                    </table>
                                                                </td>
                                                                <td colspan="3">
                                                                    <table class="table border mrnsummarynewsty">
                                                                        <tr>
                                                                            <td colspan="2" class="p-0">
                                                                                <h6
                                                                                    class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                                                    <strong>Lease Summary</strong>
                                                                                    <div class="addmendisexpbtn">
                                                                                        <button type="button"
                                                                                            data-bs-toggle="modal"
                                                                                            data-bs-target="#discount"
                                                                                            class="btn p-25 btn-sm btn-outline-secondary"><i
                                                                                                data-feather="plus"></i>
                                                                                            Tax</button>
                                                                                        <button type="button"
                                                                                            data-bs-toggle="modal"
                                                                                            data-bs-target="#addother_charges_model"
                                                                                            class="btn p-25 btn-sm btn-outline-secondary"><i
                                                                                                data-feather="plus"></i>
                                                                                            Other Charges</button>
                                                                                    </div>
                                                                                </h6>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="totalsubheadpodetail">
                                                                            <td width="55%"><strong>Sub Total</strong>
                                                                            </td>
                                                                            <td class="text-end" id="subtotal">
                                                                                {{ $lease->sub_total_amount }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Other Charges</strong></td>
                                                                            <td class="text-end" id="othercharge">
                                                                                {{ $lease->extra_charges ?? 0 }}</td>
                                                                        </tr>
                                                                        <tr id="trbody" style="display:none"></tr>
                                                                        <tr class="totalsubheadpodetail">
                                                                            <td><strong>Taxable Value</strong></td>
                                                                            <td class="text-end" id="taxablevalue">
                                                                                {{ $lease->sub_total_amount + $lease->extra_charges + $lease->otherextra_charges ?? 0 }}
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Tax</strong></td>
                                                                            <td class="text-end" id="taxAmount">
                                                                                {{ $lease->tax_amount ?? 0 }}</td>
                                                                        </tr>


                                                                        <tr class="totalsubheadpodetail">
                                                                            <td><strong>Total After Tax</strong></td>
                                                                            <td class="text-end" id="totalvalue">
                                                                                {{ $lease->sub_total_amount + $lease->extra_charges + $lease->otherextra_charges ?? 0 }}
                                                                            </td>
                                                                        </tr>

                                                                        <tr class="voucher-tab-foot">
                                                                            <td class="text-primary"><strong
                                                                                    class="font-small-4"
                                                                                    id="installement_cost"> Installment
                                                                                    Cost</strong></td>
                                                                            <td>
                                                                                <div
                                                                                    class="quottotal-bg justify-content-end">
                                                                                    <h5 id="installment_cost">
                                                                                        {{ $lease->installment_amount ?? 0 }}
                                                                                    </h5>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    </table>


                                                                    <div class="mt-2 text-end">
                                                                        <button type="button"
                                                                        data-bs-toggle="modal" data-bs-target="#Disbursement"
                                                                        class="btn p-50 btn-sm btn-outline-secondary"><i
                                                                                data-feather="plus"></i>
                                                                            Repayment Schedule </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>

                                                <div class="row mt-2 outerclickfunc">

                                                    <div class="col-md-12">
                                                        <div class="border-bottom mb-2 mt-2 pb-25">
                                                            <div class="newheader ">
                                                                <h4 class="card-title text-theme">Upload Supporting
                                                                    Documents</h4>
                                                                <p class="card-text">Fill the details</p>
                                                            </div>
                                                        </div>

                                                        <div class="table-responsive-md">
                                                            <table
                                                                class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                <thead>
                                                                    <tr>
                                                                        <th>#</th>
                                                                        <th>Document Name</th>
                                                                        <th>Upload File</th>
                                                                        <th>Attachments</th>
                                                                        <th width="40px">Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="tableDoc">
                                                                    @php
                                                                        $documents = $lease->attachments
                                                                            ? json_decode($lease->attachments, true)
                                                                            : [];
                                                                        $i = 0;
                                                                    @endphp

                                                                    @foreach ($documents as $key => $file)
                                                                        @isset($file['files'])
                                                                            @php
                                                                                $documentName = $file['name'];
                                                                                $i++;
                                                                            @endphp
                                                                            <tr>
                                                                                <td>{{ $i }}</td>
                                                                                <td>
                                                                                    <select class="form-select mw-100"
                                                                                        name="documentname[{{ $i }}]"
                                                                                        readonly>
                                                                                        <option value="">Select</option>
                                                                                        @foreach ($doc_type as $doc)
                                                                                            <option
                                                                                                value="{{ $doc->name }}"
                                                                                                {{ $doc->name == $documentName ? 'selected' : '' }}>
                                                                                                {{ ucwords(str_replace('-', ' ', $doc->name)) }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </td>
                                                                                <td>
                                                                                    <input type="file" multiple
                                                                                        class="form-control mw-100"
                                                                                        name="attachments[{{ $i }}][]"
                                                                                        id="attachments-{{ $i }}"
                                                                                        readonly>
                                                                                </td>
                                                                                <td id="preview-{{ $i }}">
                                                                                    @isset($file['files'])
                                                                                        @foreach ($file['files'] as $key1 => $fileGroup)
                                                                                            @php
                                                                                                // Extract file extension
                                                                                                $extension = pathinfo(
                                                                                                    $fileGroup,
                                                                                                    PATHINFO_EXTENSION,
                                                                                                );
                                                                                                // Set default icon
                                                                                                $icon = 'file-text';
                                                                                                switch (
                                                                                                    strtolower(
                                                                                                        $extension,
                                                                                                    )
                                                                                                ) {
                                                                                                    case 'pdf':
                                                                                                        $icon = 'file';
                                                                                                        break;
                                                                                                    case 'doc':
                                                                                                    case 'docx':
                                                                                                        $icon = 'file';
                                                                                                        break;
                                                                                                    case 'xls':
                                                                                                    case 'xlsx':
                                                                                                        $icon = 'file';
                                                                                                        break;
                                                                                                    case 'png':
                                                                                                    case 'jpg':
                                                                                                    case 'jpeg':
                                                                                                    case 'gif':
                                                                                                        $icon = 'image';
                                                                                                        break;
                                                                                                    case 'zip':
                                                                                                    case 'rar':
                                                                                                        $icon =
                                                                                                            'archive';
                                                                                                        break;
                                                                                                    default:
                                                                                                        $icon = 'file';
                                                                                                        break;
                                                                                                }
                                                                                            @endphp
                                                                                            <div class="image-uplodasection expenseadd-sign"
                                                                                                data-file-index="{{ $key1 }}">
                                                                                                <i data-feather="{{ $icon }}"
                                                                                                    class="fileuploadicon"></i>
                                                                                                <input type="hidden"
                                                                                                    name="oldattachments[{{ $i }}][]"
                                                                                                    value="{{ $fileGroup }}"
                                                                                                    readonly>
                                                                                                <div class="delete-img oldimg text-danger"
                                                                                                    data-file-index="{{ $i }}"
                                                                                                    data-old-file="{{ $fileGroup }}">
                                                                                                    <i data-feather="x"></i>
                                                                                                </div>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    @endisset
                                                                                </td>
                                                                                <td>
                                                                                    <a href="#"
                                                                                        class="text-danger removeRow"><i
                                                                                            data-feather="minus-square"></i></a>
                                                                                </td>
                                                                            </tr>
                                                                        @endisset
                                                                    @endforeach



                                                                </tbody>


                                                            </table>
                                                        </div>


                                                    </div>


                                                    <div class="col-md-12 mt-2">
                                                        <div class="mb-1">
                                                            <label class="form-label">Final Remarks</label>
                                                            <textarea type="text" rows="4" name="remarks" class="form-control" placeholder="Enter Remarks here..."
                                                                readonly>{{ $lease->remarks ?? '' }}</textarea>

                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

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


        <div class="modal fade" id="add_other_charges_model" tabindex="-1" aria-labelledby="shareProjectTitle"
            aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
                <div class="modal-content">
                    <div class="modal-header p-0 bg-transparent">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-sm-2 mx-50 pb-2">
                        <h1 class="text-center mb-1" id="shareProjectTitle">Add Other Charges</h1>
                        <p class="text-center">Enter the details below.</p>

                        <div class="text-end">
                            <a href="#" class="text-primary add-contactpeontxt mt-50"><i data-feather='plus'></i>
                                Add Other Charges</a>
                        </div>

                        <div class="table-responsive-md customernewsection-form">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th width="150px">Other Charges Name</th>
                                        <th>Other Charges %</th>
                                        <th>Other Charges Value</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="other_charges_body">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td class="text-dark"><strong>Total</strong></td>
                                        <td class="text-dark" id="other_charges_total">0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-1"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="add_other_charges_button">Submit</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="addother_charges_model" tabindex="-1" aria-labelledby="shareProjectTitle"
            aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
                <div class="modal-content">
                    <div class="modal-header p-0 bg-transparent">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-sm-2 mx-50 pb-2">
                        <h1 class="text-center mb-1" id="shareProjectTitle">Add Other Charges</h1>
                        <p class="text-center">Enter the details below.</p>

                        <div class="text-end">
                            <a href="#" class="text-primary add-contactpeontxt mt-50"><i data-feather='plus'></i>
                                Add Other Charges</a>
                        </div>

                        <div class="table-responsive-md customernewsection-form">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th width="150px">Other Charges Name</th>
                                        <th>Other Charges %</th>
                                        <th>Other Charges Value</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody id="othercharges_body">
                                    <?php
                                        // Sample stdClass object
                                        $data = json_decode($lease->extra_othercharges_json,true);

                                        // Loop through the data and generate table rows
                                        if (isset($data[0]) && is_array($data[0])) {
                                            foreach ($data[0] as $index => $charge) {
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><input type="text" class="form-control mw-100 othercharges-name"
                                                value="<?php echo $charge['name']; ?>" readonly></td>
                                        <td><input type="number" class="form-control mw-100 othercharges-percentage"
                                                value="<?php echo $charge['percentage']; ?>" readonly></td>
                                        <td><input type="number" class="form-control mw-100 othercharges-value"
                                                value="<?php echo $charge['value']; ?>" readonly></td>
                                        <td><button class="btn btn-danger btn-sm delete-row"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-trash-2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                    </path>
                                                    <line x1="10" y1="11" x2="10" y2="17">
                                                    </line>
                                                    <line x1="14" y1="11" x2="14" y2="17">
                                                    </line>
                                                </svg></button></td>
                                    </tr>
                                    <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                                <tfoot>

                                    <tr>
                                        <td colspan="2"></td>
                                        <td class="text-dark"><strong>Total</strong></td>
                                        <td class="text-dark" id="othercharges_total">{{ $lease->otherextra_charges }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-1"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="addother_charges_button">Submit</button>
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
                        <h1 class="text-center mb-1" id="shareProjectTitle">Applicable Taxes</h1>
                        <p class="text-center">View the details below.</p>

                        <div class="table-responsive-md customernewsection-form">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th width="150px">Tax</th>
                                        <th>Taxable Amount</th>
                                        <th>Tax %</th>
                                        <th>Tax Value</th>
                                    </tr>
                                </thead>
                                <tbody id="po_tax_details">
                                    <tr>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="Disbursement" tabindex="-1" aria-labelledby="shareProjectTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 600px">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">
                                Repayment Schedule</h4>
                            <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Fill the details below</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pb-2">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    @if (!empty($lease->schedule) && $lease->schedule->isNotEmpty())
                                        <table
                                            class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th width="200">Repayment Amount</th>
                                                    <th>Date</th>
                                                    <th>Tax Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="repaymentTableBody">
                                                @foreach ($lease->schedule as $index => $schedule)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td class="text-dark fw-bolder">
                                                            {{ number_format($schedule->installment_cost, 2) }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($schedule->due_date)->format('d/m/Y') }}
                                                        </td>
                                                        <td>{{ number_format($schedule->tax_amount, 2) }}</td>
                                                        <td>
                                                            <span
                                                                class="badge rounded-pill badge-light-{{ $schedule->status === 'Paid' ? 'success' : 'warning' }} badgeborder-radius">
                                                                {{ ucfirst($schedule->status) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-center text-muted">No repayment schedules available.</p>
                                    @endif
                                </div>

                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="modal fade" id="edit_address_model" tabindex="-1" aria-labelledby="shareProjectTitle"
            aria-hidden="true">
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
                                <select class="select2 form-select" id="display_address_model" readonly>
                                </select>
                            </div>
                            <div class="col-md-6 mb-1">
                                <label class="form-label">Country <span class="text-danger">*</span></label>
                                <select class="select2 form-select" name="addresses[country_id]" id="country_id_model">
                                    <option value="">Select</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-md-6 mb-1">
                                <label class="form-label">State <span class="text-danger">*</span></label>
                                <select class="select2 form-select" name="addresses[state_id]" id="state_id_model">
                                    <option value="">Select</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-1">
                                <label class="form-label">City <span class="text-danger">*</span></label>
                                <select class="select2 form-select" name="addresses[city_id]" id="city_id_model">
                                    <option value="">Select</option>
                                </select>
                            </div>


                            <div class="col-md-6 mb-1">
                                <label class="form-label w-100">Pincode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" placeholder="Enter Pincode"
                                    name="addresses[pincode]" id="pincode_model" />
                            </div>

                            <div class="col-md-12 mb-1">
                                <label class="form-label">Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" placeholder="Enter Address" name="addresses[address]" id="address_model"></textarea>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="button" data-bs-dismiss="modal"
                            class="btn btn-outline-secondary me-1">Cancel</button>
                        <button type="button" class="btn btn-primary" id="edit_address_button">Submit</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="modal fade" id="reject" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">
                            Reject Lease Application
                        </h4>

                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('lease.appr_rej') }}" method="POST" id="reject_form"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body pb-2">
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            @if (isset($lease->id))
                                                <input type="hidden" name="appr_rej_status" value="reject">
                                                <input type="hidden" name="appr_rej_lease_id"
                                                    value="{{ $lease->id }}">
                                            @endif
                                        </div>
                                    </div>

                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    @if (isset($lease) && $lease->approvalStatus == 'reject')
                                        <textarea class="form-control" name="appr_rej_remarks">{{ $lease->appr_rej_recom_remark ?? '' }}</textarea>
                                    @else
                                        <textarea class="form-control" name="appr_rej_remarks"></textarea>
                                    @endif
                                </div>

                                <div class="mb-1">
                                    @if (isset($lease) && $lease->approvalStatus == 'reject')
                                        @if (isset($lease->id))
                                            <input type="hidden" name="stored_appr_rej_doc"
                                                value="{{ $lease->appr_rej_doc ?? '' }}">
                                        @endif
                                        <label class="form-label">Upload Document</label>
                                        <input type="file" name="appr_rej_doc" class="form-control" />
                                        @if (isset($data) && !empty($lease->appr_rej_doc))
                                            <div class="col-md-3 mt-1">
                                                <p><i data-feather='folder' class="me-50"></i><a
                                                        href="{{ asset('storage/' . $lease->appr_rej_doc) }}"
                                                        style="color:green; font-size:12px;" target="_blank"
                                                        download>Approved Doc</a></p>
                                            </div>
                                        @endif
                                    @else
                                        <label class="form-label">Upload Document</label>
                                        <input type="file" name="appr_rej_doc" class="form-control" />
                                    @endif
                                </div>

                                @php
                                    $selectedValues =
                                        isset($data) && $lease->appr_rej_behalf_of
                                            ? json_decode($lease->appr_rej_behalf_of, true)
                                            : [];
                                @endphp
                                <div class="mb-1">
                                    <label class="form-label">On Behalf of <span class="text-danger">*</span></label>
                                    @if (isset($lease) && $lease->approvalStatus == 'reject')
                                        <select class="form-select select2" multiple name="appr_rej_behalf_of[]">
                                            <option value="">Select</option>
                                            <option value="nishu"
                                                {{ in_array('nishu', $selectedValues) ? 'selected' : '' }}>Nishu Garg
                                            </option>
                                            <option value="mahesh"
                                                {{ in_array('mahesh', $selectedValues) ? 'selected' : '' }}>Mahesh
                                                Bhatt</option>
                                            <option value="inder"
                                                {{ in_array('inder', $selectedValues) ? 'selected' : '' }}>Inder Singh
                                            </option>
                                            <option value="shivangi"
                                                {{ in_array('shivangi', $selectedValues) ? 'selected' : '' }}>Shivangi
                                            </option>
                                        </select>
                                    @else
                                        <select class="form-select select2" multiple name="appr_rej_behalf_of[]">
                                            <option value="">Select</option>
                                            <option value="nishu">Nishu Garg</option>
                                            <option value="mahesh">Mahesh Bhatt</option>
                                            <option value="inder">Inder Singh</option>
                                            <option value="shivangi">Shivangi</option>
                                        </select>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-1 cancelButton">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="approved" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">

                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Approve
                            Lease Application</h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('lease.appr_rej') }}" method="POST" id="approve_form"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body pb-2">
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            @if (isset($lease->id))
                                                <input type="hidden" name="appr_rej_status" value="approve">
                                                <input type="hidden" name="appr_rej_lease_id"
                                                    value="{{ $lease->id }}">
                                            @endif

                                        </div>
                                    </div>


                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    @if (isset($lease) && $lease->approvalStatus == 'approve')
                                        <textarea class="form-control" name="appr_rej_remarks">{{ $lease->appr_rej_recom_remark ?? '' }}</textarea>
                                    @else
                                        <textarea class="form-control" name="appr_rej_remarks"></textarea>
                                    @endif
                                </div>

                                <div class="mb-1">
                                    @if (isset($lease) && $lease->approvalStatus == 'approve')
                                        @if (isset($lease->id))
                                            <input type="hidden" name="stored_appr_rej_doc"
                                                value="{{ $lease->appr_rej_doc ?? '' }}">
                                        @endif
                                        <label class="form-label">Upload Document</label>
                                        <input type="file" name="appr_rej_doc" class="form-control" />
                                        @if (isset($data) && !empty($lease->appr_rej_doc))
                                            <div class="col-md-3 mt-1">
                                                <p><i data-feather='folder' class="me-50"></i><a
                                                        href="{{ asset('storage/' . $lease->appr_rej_doc) }}"
                                                        style="color:green; font-size:12px;" target="_blank"
                                                        download>Approved Doc</a></p>
                                            </div>
                                        @endif
                                    @else
                                        <label class="form-label">Upload Document</label>
                                        <input type="file" name="appr_rej_doc" class="form-control" />
                                    @endif
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">On Behalf of <span class="text-danger">*</span></label>
                                    <select class="form-select select2" multiple name="appr_rej_behalf_of[]">
                                        <option value="">Select</option>
                                        @foreach ($approvers as $approver)
                                            <option value="{{ $approver->id }}">{{ $approver->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                 </div>

                            </div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-1 cancelButton"
                            data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- END: Content-->
    <div class="modal fade" id="reminder" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" style="max-width: 800px">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="land-lease-action" action="{{ route('lease.action') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="action" id="action">
                    <input type="hidden" name="source_id" value="{{ $lease->id }}">
                    <div class="modal-body px-sm-2 mx-50 pb-2 customernewsection-form">
                        <h1 class="text-center mb-1" id="shareProjectTitle">Action</h1>
                        <p class="text-center">Enter the details below.</p>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">Comment</label>
                                    <textarea class="form-control" name="comment" required placeholder="Enter Comment...."></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">Date</label>
                                    <input type="date" name="action_date" class="form-control"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">Attachment</label>
                                    <input type="file" class="form-control" name="attachments[]" multiple>

                                </div>
                                <div class="image-uplodasection expenseadd-sign" id="preview">

                                </div>
                            </div>
                        </div>

                        <div class=" mb-2">
                            <button form="land-lease-action" data-val="renew"
                                class="btn btn-outline-warning me-1 btn-sm perform_action">Renew</button>
                            <button form="land-lease-action" data-val="close"
                                class="btn btn-outline-danger me-1 btn-sm perform_action">Close</button>
                            <button form="land-lease-action" data-val="terminate"
                                class="btn btn-outline-primary me-1 btn-sm perform_action">Terminate</button>
                            <button form="land-lease-action" data-val="reminder"
                                class="btn btn-primary btn-sm perform_action">Reminder</button>
                        </div>

                        <div class="table-responsive mt-1">
                            <table class="table myrequesttablecbox table-striped ">
                                <thead>
                                    <tr>
                                        <th class="px-1">#</th>
                                        <th class="px-1">Name</th>
                                        <th class="px-1">Date</th>
                                        <th class="px-1">Status</th>
                                        <th class="px-1">Comment</th>
                                        <th class="px-1">Attachemnt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php use App\Models\Employee; @endphp
                                    @if ($lease->actions)
                                        @foreach ($lease->actions as $action)
                                            <tr valign="top">
                                                <td>1</td>
                                                <td class="px-1">{{ App\Models\Employee::find($action['user_id'])->name }}
                                                </td>
                                                <td class="px-1">{{ $action['action_date'] }}</td>
                                                <td class="px-1">{{ strtoupper($action['status']) }}</td>
                                                <td class="px-1">{{ $action['comment'] }}</td>
                                                <td class="px-1">
                                                    @php
                                                        // Decode the JSON-encoded data
                                                        $attachments = json_decode($action->attachments);
                                                    @endphp

                                                    @if ($attachments && is_array($attachments))
                                                        @foreach ($attachments as $file)
                                                            <a href="{{ asset('documents/' . $file) }}"
                                                                target="_blank"><i data-feather="file-text"
                                                                    class="fileuploadicon"></i> </a>
                                                            <br>
                                                        @endforeach
                                                    @else
                                                        <p>No attachments available.</p>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Amendment Modal --}}
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
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>BOM</strong>? After Amendment
                        this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade text-start" id="land_details_model" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Land
                            Details</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Land Parcel</label>
                                <select class="form-select" name="filter_land_id" id="filter_land_id">
                                    <option value="">Select</option>
                                    @if (isset($lands))
                                        @foreach ($lands as $key => $val)
                                            <option value="{{ $val->id }}">{{ $val->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Land Plot</label>
                                <select class="form-select select2" name="filter_plot_no" id="filter_plot_no">
                                    <option value="">Select</option>
                                    {{-- @if (isset($lands))
                                        @foreach ($lands as $land)
                                            @foreach ($land->plots as $plot)
                                                <option value="{{ $plot->id }}">{{ $plot->document_no }}</option>
                                            @endforeach
                                        @endforeach
                                    @endif --}}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">District</label>
                                <select class="form-select" name="filter_district" id="filter_district">
                                    <option value="">Select</option>
                                    {{-- @foreach ($lands as $land)
                                        <option value="{{ $land->id }}">{{ $land->district }}</option>
                                    @endforeach --}}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">State</label>
                                <select class="form-select select2" name="filter_state" id="filter_state">
                                    <option value="">Select</option>
                                    {{-- @foreach ($states as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach --}}
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Document No.</th>
                                            <th>Land Doc No.</th>
                                            <th>Plot Doc. No</th>
                                            <th>Khasara No.</th>
                                            <th>District</th>
                                            <th>State</th>
                                            <th>Country</th>
                                            <th>Pincode</th>
                                        </tr>
                                    </thead>
                                    <tbody id="find_land_table"></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                            data-feather="x-circle"></i> Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal" id="land_detail_process"><i
                            data-feather="check-circle"></i> Process</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <!-- BEGIN: Page JS-->
    <script>
        var taxCalculationUrl = "{{ url('/land-lease/tax-calculation') }}";
    </script>
    <script src="{{ asset('assets/js/custom/pages/lease/model-land-details.js') }}"></script>
    <script src="{{ asset('assets/js/custom/pages/lease/lease.js') }}"></script>

    <script>
        $(document).ready(function() {

                    $(".taxcaluculate").click(function() {
                        calcualtetax();
                        $("#discount").modal('show');
                    })


                    $(document).ready(function() {
                        var rowCount = 1; // Counter to keep track of the number of rows

                        // Event listener for adding a new row
                        $('#addother_charges_row').on('click', function(e) {
                            e.preventDefault(); // Prevent default anchor click behavior

                            // Create a new row with inputs
                            var newRow = `
            <tr>
                <td>${++rowCount}</td>
                <td><input type="text" class="form-control mw-100 othercharges-name" readonly value=""></td>
                <td><input type="number" class="form-control mw-100 othercharges-percentage" readonly value=""></td>
                <td><input type="number" class="form-control mw-100 othercharges-value" readonly value=""></td>
                <td><button class="btn btn-danger btn-sm delete-row">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                </button></td>
            </tr>
        `;

                            // Append the new row to the tbody
                            $('#othercharges_body').append(newRow);
                        });

                        // Event listener for deleting a row
                        $(document).on('click', '.delete-row', function() {
                            $(this).closest('tr').remove(); // Remove the row
                            calculateTotal(); // Recalculate total after removal
                        });

                        $(document).on('click', '.delete-row', function() {
                            $(this).closest('tr').remove(); // Remove the row
                            calculateTotal(); // Recalculate total after removal
                        });

                        // Event listener for changes in percentage and value inputs
                        $(document).on('input', '.othercharges-percentage, .othercharges-value', function() {
                            // Disable the other input in the same row
                            var row = $(this).closest('tr');
                            if ($(this).hasClass('othercharges-percentage')) {
                                if ($(this).val() !== '') {
                                    row.find('.othercharges-value').val('').prop('readonly',
                                        true); // Clear and disable value input
                                } else {
                                    row.find('.othercharges-value').prop('readonly',
                                    false); // Re-enable value input
                                }
                            } else if ($(this).hasClass('othercharges-value')) {
                                if ($(this).val() !== '') {
                                    row.find('.othercharges-percentage').val('').prop('readonly',
                                        true); // Clear and disable percentage input
                                } else {
                                    row.find('.othercharges-percentage').prop('readonly',
                                        false); // Re-enable percentage input
                                }
                            }
                            calculateTotal(); // Recalculate total whenever these inputs change
                        });

                        // Function to calculate the total other charges
                        function calculateTotal() {
                            let totalValue = parseFloat($("#totalvalue").html()) ||
                                0; // Fetching total value from the HTML element

                            let otherChargesTotal = 0; // To hold the total of other charges
                            $('#othercharges_body tr').each(function() {
                                let percentage = parseFloat($(this).find('.othercharges-percentage')
                                .val()) || 0;
                                let chargeValue = parseFloat($(this).find('.othercharges-value').val()) ||
                                0;

                                // Calculate charge based on percentage of the total value
                                if (percentage > 0) {
                                    let calculatedCharge = (totalValue * (percentage /
                                        100)); // Calculate based on percentage
                                    otherChargesTotal += calculatedCharge; // Sum up calculated charges

                                    // Automatically update the other-charges-value field with the calculated charge
                                    $(this).find('.othercharges-value').val(calculatedCharge.toFixed(2));
                                } else {
                                    otherChargesTotal +=
                                        chargeValue; // If percentage is not present, just add the value
                                }
                            });

                            // Update the total display in the footer
                            $('#othercharges_total').text(otherChargesTotal.toFixed(2));
                        }
                        $('#addother_charges_button').click(function() {
                            // Assuming you want to add a total row after submission
                            $('#trbody').css('display', 'table-row');
                            $('#trbody').html(`
                    <td><strong>Other Charges</strong></td>
                    <td class="text-end" id="chargeother">${$('#othercharges_total').text()}</td>
                `);
                            $("#lease_extra_charges").val(parseFloat($('#othercharges_total').text()));
                            $("#totalvalue").html(parseFloat($("#subtotal").html()) + parseFloat($(
                                    "#othercharge")
                                .html()) + parseFloat($('#othercharges_total').text()) + parseFloat(
                                $('#tax_amount')
                                .val()));
                            $('#addother_charges_model').modal('hide'); // Hide the modal after submission
                            calculateRepayment();
                        });
                    });
    </script>

    <script>
        $(document).ready(function() {
            // When the main land select changes
            $('#land').on('change', function() {
                var selectedLandId = $(this).val(); // Get selected land ID

                // Get the selected option
                var selectedOption = $(this).find('option:selected');

                // Get size and address data attributes
                var sizeLand = selectedOption.data('sizeland'); // Extract size of land
                var landAddress = selectedOption.data('landaddress'); // Extract address data

                // Set the values in the respective fields
                $('#land_size').val(sizeLand); // Fill the size of land field
                $('#land_location').val(landAddress); // Fill the location of land field

                // Set the selected land in the modal dropdown
                $('#filter_land_id').val(selectedLandId).trigger(
                    'change'); // Trigger change event for select2 if used

                // Show the modal
                $('#land_details_model').modal('show');
            });
        });
    </script>
    <script>
        $(".addRow").click(function() {
            var rowCount = $("#tableDoc").find('tr').length + 1; // Counter for row numbering, starting at 1

            var newRow = `
    <tr>
        <td>${rowCount}</td>
        <td>
        <select class="form-select mw-100" name="documentname[${rowCount-1}]">
        <option value="">Select</option>
         @foreach ($doc_type as $document)
                                                                                <option value="{{ $document->name }}">{{ ucwords(str_replace('-', ' ', $document->name)) }}</option>
                                                                            @endforeach  </select>
                                                                               </td>
        <td>
            <input type="file" multiple class="form-control mw-100" name="attachments[${rowCount-1}][]" id="attachments-${rowCount-1}">
        </td>
        <td id="preview-${rowCount-1}">
            <!-- File preview icons will be inserted here -->
        </td>
        <td><a href="#" class="text-danger trash"><i data-feather="trash-2"></i></a></td>
    </tr>`;

            $("#tableDoc").append(newRow);
            feather.replace();

        });



        // Use event delegation to handle dynamically added file inputs
        $(document).on('change', 'input[type="file"]', function(e) {
            handleFileUpload(e, `#preview`);
        });

        // Function to handle file upload preview with delete icon
        function handleFileUpload(event, previewElement) {
            var files = event.target.files;
            var previewContainer = $(previewElement); // The container where previews will appear
            previewContainer.empty(); // Clear previous previews

            if (files.length > 0) {
                // Loop through each selected file
                for (var i = 0; i < files.length; i++) {
                    // Get the file extension
                    var fileName = files[i].name;
                    var fileExtension = fileName.split('.').pop().toLowerCase(); // Get file extension

                    // Set default icon
                    var fileIconType = 'file-text'; // Default icon for unknown types

                    // Map file extension to specific Feather icons
                    switch (fileExtension) {
                        case 'pdf':
                            fileIconType = 'file'; // Icon for PDF files
                            break;
                        case 'doc':
                        case 'docx':
                            fileIconType = 'file'; // Icon for Word documents
                            break;
                        case 'xls':
                        case 'xlsx':
                            fileIconType = 'file'; // Icon for Excel files
                            break;
                        case 'png':
                        case 'jpg':
                        case 'jpeg':
                        case 'gif':
                            fileIconType = 'image'; // Icon for image files
                            break;
                        case 'zip':
                        case 'rar':
                            fileIconType = 'archive'; // Icon for compressed files
                            break;
                        default:
                            fileIconType = 'file'; // Default icon
                            break;
                    }

                    // Generate the file preview div dynamically
                    var fileIcon = `
            <div class="image-uplodasection expenseadd-sign" data-file-index="${i}">
                <i data-feather="${fileIconType}" class="fileuploadicon"></i>
                <div class="delete-img text-danger" data-file-index="${i}">
                    <i data-feather="x"></i>
                </div>
            </div>
        `;

                    // Append the generated fileIcon div to the preview container
                    previewContainer.append(fileIcon);
                }
                // Replace icons with Feather icons after appending the new elements
                feather.replace();
            }


            // Add event listener to delete the file preview when clicked
            previewContainer.find('.delete-img').click(function() {
                var fileIndex = $(this).parent().data('file-index'); // Get the correct index from parent
                removeFilePreview(fileIndex, previewContainer, event.target);
            });
        }

        // Function to remove a single file from the FileList
        function removeFilePreview(fileIndex, previewContainer, inputElement) {
            var dt = new DataTransfer(); // Create a new DataTransfer object to hold the remaining files
            var files = inputElement.files;

            // Loop through the files and add them to the DataTransfer object, except the one to delete
            for (var i = 0; i < files.length; i++) {
                if (i !== fileIndex) {
                    dt.items.add(files[i]); // Add file to DataTransfer if it's not the one being deleted
                }
            }

            // Update the input element with the new file list
            inputElement.files = dt.files;

            // Remove the preview of the deleted file
            previewContainer.children(`[data-file-index="${fileIndex}"]`).remove();

            // Now re-index the remaining file previews
            var remainingPreviews = previewContainer.children();
            remainingPreviews.each(function(index) {
                $(this).attr('data-file-index', index); // Update data-file-index correctly
                $(this).find('.delete-img').attr('data-file-index', index); // Also update delete button index
            });

            // Debugging logs
            console.log(`Remaining files after deletion: ${dt.files.length}`);
            console.log(`Remaining preview elements: ${remainingPreviews.length}`);

            // If no files are left after deleting, reset the file input
            if (dt.files.length === 0) { // Check the updated DataTransfer's files length
                inputElement.value = ""; // Clear the input value to reset it
            }
        }


        // Remove row functionality
        $("#tableBody").on("click", ".trash", function(event) {
            event.preventDefault(); // Prevent default action for <a> tag
            $(this).closest('tr').remove(); // Remove the closest <tr> element
        });
        $("#tableDoc").on("click", ".trash", function(event) {
            event.preventDefault(); // Prevent default action for <a> tag
            $(this).closest('tr').remove(); // Remove the closest <tr> element
        });
    </script>

    <script>
        document.getElementById('leaseTime').addEventListener('input', calculateLeaseEndDate);
        document.getElementById('leaseStartDate').addEventListener('change', calculateLeaseEndDate);


        function calculateLeaseEndDate() {
            const leaseTime = parseInt(document.getElementById('leaseTime').value);
            const leaseStartDate = document.getElementById('leaseStartDate').value;

            if (leaseTime && leaseStartDate) {
                const startDate = new Date(leaseStartDate);
                startDate.setFullYear(startDate.getFullYear() + leaseTime);

                const endDate = startDate.toISOString().split('T')[0]; // Get YYYY-MM-DD format
                document.getElementById('leaseEndDate').value = endDate;
            }

            calculateRepayment();
        }

        const lateFeePercentage = document.getElementById('lateFeePercentage');
        const lateFeeValue = document.getElementById('lateFeeValue');

        lateFeePercentage.addEventListener('input', function() {
            if (lateFeePercentage.value !== '') {
                var total = $("#lease_total_installment").val();

                lateFeeValue.value = (lateFeePercentage.value / 100 * total).toFixed(
                    2); // Format Late Fee Value to 2 decimal places
                lateFeeValue.readOnly = true; // Set the Late Fee Value field to readonly

            } else {
                lateFeeValue.readOnly = false; // Make the Late Fee Value editable again
            }
        });

        lateFeeValue.addEventListener('input', function() {
            if (lateFeeValue.value !== '') {
                lateFeePercentage.value = ''; // Clear the Late Fee % field
                lateFeePercentage.readOnly = true; // Set the Late Fee % field to readonly
            } else {
                lateFeePercentage.readOnly = false; // Make the Late Fee % editable again
            }
        });
    </script>

    <script>
        window.routes = {
            getLeaseDocumentNumber: @json(route('get.landrequests', ['book_id' => ':book_id'])),
            getStatesRoute: @json(route('vendor.get.states', ['country_id' => ':country_id'])),
            getCitiesRoute: @json(route('vendor.get.cities', ['state_id' => ':state_id'])),
            getExchangeRate: @json(route('getExchangeRate')),
            customerAddressStore: @json(route('lease.customer.address.store')),
            landDetailsFilter: @json(route('land.onleaseadd.filter-land')),
        };
        // url = window.routes.loanViewAllDetail.replace(
        //         ":id",
        //         report.home_loan.id
        //     );
        const DataOnLoad = {
            customers: @json($customers),
            lands: @json($lands),
        }
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        });

        @if (session('success'))
            showToast("warning", "{{ session('success') }}");
        @endif

        @if (session('error'))
            showToast("error", "{{ session('error') }}");
        @endif
        $('#customer').val('{{ $lease->customer_id }}').trigger('change');
        $('#customer').on('change', function() {
            // Get the selected option's data-currency-id attribute
            var currency_id = $(this).find(':selected').attr('data-currency-id');
            console.log(currency_id);

            if (currency_id) {
                var url = '{{ route('get.lease.exchange.rate', ':currency_id') }}';
                url = url.replace(':currency_id', currency_id);
                // Make AJAX call to get the exchange rate
                $.ajax({
                    url: url, // Your route for exchange rate

                    type: 'GET',
                    success: function(response) {
                        if (response.currency_id) {

                            // Populate the textboxes with the fetched data
                            $('#currency').val(response.currency_id);
                            $('#exchange_rate').val(response.exchange_rate_id);
                            $('#currency_name').val(response.currency_code);
                            $('#exchange_rate_amount').val(response.exchange_rate);
                        } else {
                            alert('Currency details not found');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                    }
                });
            } else {
                // Reset the fields if no currency is selected
                $('#currency').val('');
                $('#exchange_rate').val('');
                $('#currency_name').val('');
                $('#exchange_rate_amount').val('');
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            calcualtetax();
            // Make all input fields readonly
            $('.lease-form input').attr('readonly', true);

            // Make all select fields disabled (to simulate readonly)
            $('lease-form select').attr('disabled', true);

            // Make all textarea fields readonly
            $('lease-form textarea').attr('readonly', true);
        });
        $('.revisionNumber').attr('disabled', false);




        $(function() {


            $(".revisionNumber").change(function() {
                window.location.href = "{{ route('lease.show', $source_id) }}?revisionNumber=" + $(this)
                    .val();
            });
        });


        $(document).on('click', '#amendmentSubmit', (e) => {
            let actionUrl = "{{ route('lease.amendment', $lease->id) }}";
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                    location.reload();
                });
            });
        });

        document.querySelectorAll('.editmodel').forEach(button => {
            button.addEventListener('click', function() {
                const uniqueId = this.getAttribute('data-id');
                const otherDataJson = this.getAttribute('data-otherdata');
                const leaseamount = this.getAttribute('data-leaseamount');

                const divhtml = $(this).parent('div').parent('div');

                let otherChargesData = [];
                try {
                    const parsedData = JSON.parse(otherDataJson);
                    const dynamicKey = Object.keys(parsedData)[0];
                    otherChargesData = parsedData[dynamicKey] || [];
                } catch (e) {
                    console.error("Invalid JSON data:", e);
                    otherChargesData = [];
                }

                console.log(otherChargesData);

                // Populate the modal
                initializeOtherModal(
                    document.querySelector('#other_charges_body'),
                    uniqueId,
                    leaseamount,
                    otherChargesData,
                    divhtml
                );

                const addOtherChargesModal = new bootstrap.Modal(document.getElementById(
                    'add_other_charges_model'));
                addOtherChargesModal.show();
            });
        });

        function initializeOtherModal(chargesBody, uniqueId, leaseAmount, otherChargesData, divhtml) {
            chargesBody.innerHTML = "";
            const updatedataothercharges = divhtml; // Capture the updatedata reference

            console.log(updatedataothercharges); // This works
            otherChargesData.forEach(data => {
                addotherChargesRow(chargesBody, data, uniqueId, leaseAmount);
            });

            const addRowBtn = $("#add_other_charges_row");
            const rowId = '.other-charges-row';

            addRowBtn.off("click").on("click", function() {
                addotherChargesRow(chargesBody, {}, uniqueId, leaseAmount);
            });

            $("#other_charges_body")
                .off("click")
                .on("click", (e) => {
                    if (e.target.closest(".otherdelete-row")) {
                        e.target.closest("tr").remove();
                        calculateTotalCharges();
                        updateRowNumbers();
                        updateRowData(uniqueId);
                    }
                });

            $("#other_charges_body")
                .off("input")
                .on("input", (e) => {
                    if (
                        e.target.classList.contains("other-charges-percentage") ||
                        e.target.classList.contains("other-charges-value")
                    ) {
                        const row = e.target.closest("tr");
                        const rowId = row.id;

                        const percentageInput = row.querySelector(".other-charges-percentage");
                        const valueInput = row.querySelector(".other-charges-value");

                        if (e.target.value !== "") {
                            if (e.target === percentageInput) {
                                valueInput.value = "";
                                valueInput.disabled = true;
                            } else {
                                percentageInput.value = "";
                                percentageInput.disabled = true;
                            }
                        } else {
                            percentageInput.disabled = false;
                            valueInput.disabled = false;
                        }
                        console.log(updatedataothercharges); // This works
                        calculateTotalCharges(); // No need to pass updatedataothercharges, use the global one
                        updateRowData(rowId);
                    }
                });

            function calculateTotalCharges() {
                console.log(updatedataothercharges); // This will work because we're using the global variable
                const chargesBody = document.querySelector('#other_charges_body');
                let totalCharges = 0;

                chargesBody.querySelectorAll('tr').forEach(row => {
                    const percentageInput = row.querySelector('.other-charges-percentage');
                    const valueInput = row.querySelector('.other-charges-value');

                    const percentageValue = parseFloat(percentageInput.value) || 0;
                    const fixedValue = parseFloat(valueInput.value) || 0;

                    if (percentageValue > 0) {
                        const calculatedValue = (leaseAmount * percentageValue) / 100;
                        totalCharges += calculatedValue;
                        valueInput.value = calculatedValue.toFixed(2);
                    } else {
                        totalCharges += fixedValue;
                    }
                });

                console.log(updatedataothercharges); // This will now show the correct value
                document.getElementById('other_charges_total').textContent = totalCharges.toFixed(2);
                updatedataothercharges.find("#add_other_amount").val(totalCharges.toFixed(2));
                updateTotals();
            }

            function updateRowNumbers() {
                chargesBody.querySelectorAll(rowId).forEach((row, index) => {
                    row.querySelector("td:first-child").textContent = index + 1;
                });
            }

            function updateRowData(unqId) {
                var otherChData = {};
                otherChData[unqId] = [];

                chargesBody.querySelectorAll('tr').forEach(row => {
                    const landInput = row.querySelector(".other-charges-land-id");
                    const plotInput = row.querySelector(".other-charges-plot-id");
                    const nameInput = row.querySelector(".other-charges-name");
                    const percentageInput = row.querySelector(".other-charges-percentage");
                    const valueInput = row.querySelector(".other-charges-value");

                    if (!landInput || !plotInput || !nameInput || !percentageInput || !valueInput) return;

                    otherChData[unqId].push({
                        land: landInput.value,
                        plot: plotInput.value,
                        name: nameInput.value,
                        percentage: percentageInput.value,
                        value: valueInput.value,
                    });
                });
                console.log(updatedataothercharges); // This will now show the correct value
                updatedataothercharges.find("#add_other_amount_json").val(JSON.stringify(otherChData));
            }

            $("#add_other_charges_button")
                .off("click")
                .on("click", () => {
                    calculateTotalCharges();
                    updateRowData(uniqueId);
                    $("#add_other_charges_model").modal("hide");
                });

            function init() {
                calculateTotalCharges();
            }
            init();

            console.log('sdfsd');
            updateTotals();
            calculateTotalCharges();
        }




        // Helper function to add charges row
        function addotherChargesRow(chargesBody, data = {}, uniqueId, leaseAmount) {
            const rowCount = chargesBody.querySelectorAll(`tr`).length + 1;
            const uniquId = generateUniqueId();

            const newRow = `
        <tr id="${uniquId}">
            <td>${rowCount}</td>
            <input type="hidden" class="other-charges-land-id" readonly value="${data.land || ''}" />
            <input type="hidden" class="other-charges-plot-id" readonly value="${data.plot || ''}" />
            <td><input type="text" class="form-control mw-100 other-charges-name" readonly value="${data.name || ''}" /></td>
            <td><input type="number" class="form-control mw-100 other-charges-percentage" readonly value="${data.percentage || ''}" /></td>
            <td><input type="number" class="form-control mw-100 other-charges-value" readonly value="${data.value || ''}" /></td>
            <td><button class="btn btn-danger btn-sm otherdelete-row"><i data-feather="trash-2"></i></button></td>
        </tr>
    `;
            chargesBody.insertAdjacentHTML('beforeend', newRow);
            feather.replace();
        }

        // Function to calculate total charges
        function calculateotherTotalCharges(chargesBody, leaseAmount) {
            let totalCharges = 0;
            chargesBody.querySelectorAll('tr').forEach(row => {
                const percentageInput = row.querySelector('.other-charges-percentage');
                const valueInput = row.querySelector('.other-charges-value');

                const percentageValue = parseFloat(percentageInput.value) || 0;
                const fixedValue = parseFloat(valueInput.value) || 0;

                if (percentageValue > 0) {
                    const calculatedValue = (leaseAmount * percentageValue) / 100;
                    totalCharges += calculatedValue;
                    valueInput.value = calculatedValue.toFixed(2); // Set the calculated value
                } else {
                    totalCharges += fixedValue;
                }
            });

            // Update the total in the modal
            document.getElementById('other_charges_total').textContent = totalCharges.toFixed(2);
        }


        updateTotals();

        function generateUniqueId(length = 8) {
            const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
            const numbers = "0123456789";
            let result = chars.charAt(Math.floor(Math.random() * chars.length)); // Ensure first character is a letter
            const allChars = chars + numbers;
            for (let i = 1; i < length; i++) {
                const randomIndex = Math.floor(Math.random() * allChars.length);
                result += allChars[randomIndex];
            }
            return result;
        }

        calcualtetax();
        document.addEventListener('DOMContentLoaded', function() {
            $('.perform_action').click(function() {
                let data_val = $(this).attr('data-val');
                $("#action").val(data_val);
            });
        });

        $('#filter_land_id').on('change', function(e) {
            let land_id = $(this).val()
            e.preventDefault();

            let actionUrl = `{{ url('land/lease/get-land-parcel-data/${land_id}') }}`

            if (land_id != '') {

                $.ajax({
                    url: actionUrl,
                    success: function(response) {

                        let filter_plot_no = $('#filter_plot_no');
                        let filter_district = $('#filter_district');
                        let filter_state = $('#filter_state');

                        // Clear existing options before adding new ones
                        filter_plot_no.empty();
                        filter_district.empty();
                        filter_state.empty();

                        $.each(response.data, function(i, v) {

                            filter_plot_no.append(
                                `<option value="${v.plot[i].id}">${v.plot[i].document_no}</option>`
                            );

                            filter_district.append(
                                `<option value="${v.id}">${v.district}</option>`
                            );


                            filter_state.append(
                                `<option value="${v.id}">${v.state}</option>`
                            );
                            return false
                        });

                    }
                });

            }
        });
    </script>
@endsection
