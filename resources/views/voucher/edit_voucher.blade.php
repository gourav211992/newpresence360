@php
    use App\Helpers\ConstantHelper;
    // Find the selected location object
    $selectedLocation = $locations->firstWhere('id', $data->location);
    $locationId = (string) $data->location;


    // Initialize as empty array if no location found
    $locationCostCenters = [];
    if(!is_null($locationId)){
        $locationCostCenters = array_filter($cost_centers, function ($center) use ($locationId) {
            if (empty($center['location'])) return false;

            // Always ensure we have an array of individual strings
            $locations = is_array($center['location'])
                ? explode(',', implode(',', $center['location']))
                : explode(',', $center['location']);

            $locations = array_map('trim', $locations); // remove spaces

            return in_array($locationId, $locations);
        });
    }
@endphp

@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" type="text/css" href="{{ url('/app-assets/css/core/menu/menu-types/vertical-menu.css') }}">
    <link rel="stylesheet" href="{{ url('/app-assets/js/jquery-ui.css') }}">
    <style>
        .badge-light-primary span {
            font-weight: bold;
            /* Makes the INR text bold */
            color: #6B12B7;
            /* Sets the text color to blue (you can change this to any color) */
        }
    </style>
@endsection

@section('content')
    <script>
        const locationCostCentersMap = @json($cost_centers);
    </script>
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <form id="voucherForm" action="{{ route('vouchers.update', $data->id) }}" method="POST"
                enctype="multipart/form-data" onsubmit="return check_amount()">
                @csrf
                @method('PUT')
                <input type="hidden" name="actionType" id="actionType" value="submit"/>
                <input type="hidden" name="doc_number_type" id="doc_number_type">
                <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
                <input type="hidden" name="doc_prefix" id="doc_prefix">
                <input type="hidden" name="doc_suffix" id="doc_suffix">
                <input type="hidden" name="doc_no" id="doc_no">

                <input type="hidden" name="org_currency_id" id="org_currency_id" value="{{ $data->org_currency_id }}">
                <input type="hidden" name="org_currency_code" id="org_currency_code"
                    value="{{ $data->org_currency_code }}">
                <input type="hidden" name="org_currency_exg_rate" id="org_currency_exg_rate"
                    value="{{ $data->org_currency_exg_rate }}">

                <input type="hidden" name="comp_currency_id" id="comp_currency_id" value="{{ $data->comp_currency_id }}">
                <input type="hidden" name="comp_currency_code" id="comp_currency_code"
                    value="{{ $data->comp_currency_code }}">
                <input type="hidden" name="comp_currency_exg_rate" id="comp_currency_exg_rate"
                    value="{{ $data->comp_currency_exg_rate }}">

                <input type="hidden" name="group_currency_id" id="group_currency_id"
                    value="{{ $data->group_currency_id }}">
                <input type="hidden" name="group_currency_code" id="group_currency_code"
                    value="{{ $data->group_currency_code }}">
                <input type="hidden" name="group_currency_exg_rate" id="group_currency_exg_rate"
                    value="{{ $data->group_currency_exg_rate }}">

                <input type="hidden" name="currency_code" id="currency_code" value="{{ $data->currency_code }}">



                <input type="hidden" name="status" id="status">
                  <div class="modal fade" id="amendConfirmPopup" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <div>
                <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Amend Voucher
                </h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <input type="hidden" name="action_type" id="action_type_main">
            </div>
            <div class="modal-body pb-2">
                <div class="row mt-1">
                <div class="col-md-12">
                    <div class="mb-1">
                        <label class="form-label">Remarks</label>
                        <textarea name="amend_remarks" class="form-control cannot_disable"></textarea>
                    </div>
                    <div class = "row">
                        <div class = "col-md-8">
                            <div class="mb-1">
                                <label class="form-label">Upload Document</label>
                                <input name = "amend_attachments[]" onchange = "addFiles(this, 'amend_files_preview')" type="file" class="form-control cannot_disable" max_file_count = "2" multiple/>
                            </div>
                        </div>
                        <div class = "col-md-4" style = "margin-top:19px;">
                            <div class="row" id = "amend_files_preview">
                            </div>
                        </div>
                    </div>
                    <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">  
                <button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('amendConfirmPopup')">Cancel</button> 
                <button type="button" class="btn btn-primary" onclick = "submitAmend('voucherForm');">Submit</button>
            </div>
        </div>
    </div>
    </div>

                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 col-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Edit Voucher</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                            <li class="breadcrumb-item"><a href="{{ route('vouchers.index') }}">Vouchers
                                                    List</a></li>
                                            <li class="breadcrumb-item active">Edit Voucher</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <a type="button" href="{{ route('vouchers.index') }}" class="btn btn-secondary btn-sm"><i
                                        data-feather="arrow-left-circle"></i> Back</a>
                               

                                @if (isset($fyear) && $fyear['authorized'])
                                <a type="button" onclick = "openAmendConfirmModal('draft');"
                                            class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 d-none" id="btnDraft"
                                            name="action" value="draft"><i data-feather='save'></i> Save as Draft</a>
                                <a type="button" onclick = "openAmendConfirmModal('submitted');"
                                            class="btn btn-primary btn-sm d-none" id="btnSubmit" name="action"
                                            value="submitted"><i data-feather="check-circle"></i> Submit</a>
                               
                                @if ($buttons['draft'])
                                        <a type="button" onclick = "submitForm('draft');"
                                            class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 d-none" id="draft"
                                            name="action" value="draft"><i data-feather='save'></i> Save as Draft</a>
                                    @endif
                                    @if ($buttons['submit'])
                                        <a type="button" onclick = "submitForm('submitted');"
                                            class="btn btn-primary btn-sm" id="submitted" name="action"
                                            value="submitted"><i data-feather="check-circle"></i> Submit</a>
                                    @endif
                                    @if ($buttons['approve'])
                                        <a type="button" id="reject-button" data-bs-toggle="modal"
                                            data-bs-target="#approveModal" onclick = "setReject();"
                                            class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i
                                                data-feather="x-circle"></i> Reject</a>
                                        <a type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#approveModal" onclick = "setApproval();"><i
                                                data-feather="check-circle"></i> Approve</a>
                                    @endif
                                    {{-- @if ($buttons['amend'])
                                        <a type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm"
                                            class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i>
                                            Amendment</a>
                                    @endif --}}
                                    {{-- @if ($buttons['revoke'])
                                    <a id = "revokeButton" type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='rotate-ccw'></i> Revoke</a>
                                    @endif --}}
                                    {{-- @if ($buttons['cancel'])
                                    <a id = "cancelButton" type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0"><i data-feather="x-circle"></i> Cancel</a>
                                    @endif --}}
                                    {{-- @if ($buttons['reference'])
                                    <a type="button" href="{{$ref_view_route}}"
                                        class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i data-feather="file-text"></i>  View REF</a>
                                    @endif --}}

                                    @if ($buttons['amend'])
                                        <a type="button" data-bs-toggle="modal" id="btnAmend" data-bs-target="#amendmentconfirm"
                                            class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i>
                                            Amendment</a>
                                    @endif
                                    @if ($buttons['revoke'])
                                        <a id = "revokeButton" type="button"
                                            class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='rotate-ccw'></i>
                                            Revoke</a>
                                    @endif
                                    @if ($buttons['cancel'])
                                        <a id = "cancelButton" type="button"
                                            class="btn btn-danger btn-sm mb-50 mb-sm-0"><i data-feather="x-circle"></i>
                                            Cancel</a>
                                    @endif
                                @endif
                                @if ($buttons['reference'])
                                    <a type="button" href="{{ $ref_view_route }}"
                                        class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i
                                            data-feather="file-text"></i> View REF</a>
                                @endif



                                <input id="submitButton" type="submit" value="Submit" class="hidden" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <section id="basic-datatable">
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body customernewsection-form">

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div
                                                    class="newheader  d-flex justify-content-between border-bottom mb-2 pb-25">
                                                    <div>
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>

                                                    <div class="header-right">
                                                        @php
                                                            use App\Helpers\Helper;
                                                            $mainBadgeClass = match ($data->approvalStatus) {
                                                                'approved' => 'success',
                                                                'approval_not_required' => 'success',
                                                                'draft' => 'warning',
                                                                'submitted' => 'info',
                                                                'partially_approved' => 'warning',
                                                                default => 'danger',
                                                            };
                                                        @endphp
                                                        <div class="col-md-6 text-sm-end">
                                                            <span
                                                                class="badge rounded-pill {{ App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$data->document_status] ?? '' }} forminnerstatus">
                                                                <span class="text-dark">Status</span>
                                                                : <span
                                                                    class="{{ App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? '' }}">
                                                                    @if ($data->document_status == App\Helpers\ConstantHelper::APPROVAL_NOT_REQUIRED)
                                                                        Approved
                                                                    @else
                                                                        {{ ucfirst($data->document_status) }}
                                                                    @endif
                                                                </span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>





                                            <div class="col-md-8">

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Voucher Type<span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <select class="form-select select2 disable" name="book_type_id"
                                                            id="book_type_id" required onchange="getBooks()" disabled>
                                                            <option value="{{ $data?->series?->org_service_id }}"
                                                                selected>
                                                                {{ $data?->series?->org_service?->name }}</option>

                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <select class="form-select  disable" id="book_id" name="book_id"
                                                            required onchange="get_voucher_details()" disabled>
                                                            <option value="{{ $data?->book_id }}" selected>
                                                                {{ $data?->series?->book_code }}</option>



                                                        </select>
                                                    </div>
                                                </div>

                                                <div hidden class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Voucher Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control  disable" name="voucher_name"
                                                            id="voucher_name" required value="{{ $data->voucher_name }}"
                                                            readonly />
                                                        @error('voucher_name')
                                                            <span class="text-danger"
                                                                style="font-size:12px">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Voucher No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" name="voucher_no"
                                                            id="voucher_no" required value="{{ $data->voucher_no }}"
                                                            readonly />
                                                        @error('voucher_no')
                                                            <span class="text-danger"
                                                                style="font-size:12px">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="date" class="form-control" name="date"
                                                            id="date" required value="{{ $data->document_date }}"
                                                            min="{{ $fyear['start_date'] }}"
                                                            max="{{ $fyear['end_date'] }}" />
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Location <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <select id="locations" class="form-select" name="location">
                                                            <option disabled value="" selected>Select Location
                                                            </option>
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}"
                                                                    {{ isset($data->location) && $data->location == $location->id ? 'selected' : '' }}>
                                                                    {{ $location->store_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Currency <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <select class="form-control select2" name="currency_id"
                                                            id="currency_id" onchange="getExchangeRate()">
                                                            <option>Select Currency</option>
                                                            @foreach ($currencies as $currency)
                                                                <option value="{{ $currency->id }}"
                                                                    @if ($data->currency_id == $currency->id) selected @endif>
                                                                    {{ $currency->name . ' (' . $currency->short_name . ')' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                </div>
                                                <div class="row align-items-center mb-1">

                                                    <div class="col-md-2">
                                                        <label class="form-label mt-50">Exchange Rate</label>


                                                    </div>
                                                    <div class="col-md-6">
                                                        <input type="text" class="form-control" id="orgExchangeRate"
                                                            value="{{ round($data->org_currency_exg_rate, 2) }}"
                                                            onclick="rate_change()" oninput="calculate_cr_dr()" />
                                                    </div>
                                                    <div class="col-md-7" hidden>
                                                        <div class="d-flex align-items-center">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="d-flex">
                                                                        <input type="text" class="form-control"
                                                                            readonly id="base_currency_code"
                                                                            value="{{ $data->org_currency_code }}"
                                                                            style="text-transform:uppercase;width: 80px; border-right: none; border-radius: 7px 0 0 7px" />


                                                                    </div>
                                                                    <label class="form-label">Base</label>
                                                                </div>

                                                                <div hidden class="col-md-4">
                                                                    <div class="d-flex">
                                                                        <input type="text" class="form-control"
                                                                            readonly id="company_currency_code"
                                                                            value="{{ $data->comp_currency_code }}"
                                                                            style="text-transform:uppercase;width: 80px; border-right: none; border-radius: 7px 0 0 7px" />

                                                                        <input type="text" class="form-control"
                                                                            readonly id="company_exchange_rate"
                                                                            value="{{ round($data->comp_currency_exg_rate, 2) }}"
                                                                            style="width: 80px;  border-radius:0 7px 7px 0" />


                                                                    </div>
                                                                    <label class="form-label">Company</label>
                                                                </div>

                                                                <div hidden class="col-md-4">
                                                                    <div class="d-flex">
                                                                        <input type="text" class="form-control"
                                                                            readonly id="grp_currency_code"
                                                                            value="{{ $data->group_currency_code }}"
                                                                            style="text-transform:uppercase;width: 80px; border-right: none; border-radius: 7px 0 0 7px" />

                                                                        <input type="text" class="form-control"
                                                                            readonly id="grp_exchange_rate"
                                                                            value="{{ round($data->group_currency_exg_rate, 2) }}"
                                                                            style="width: 80px;  border-radius:0 7px 7px 0" />


                                                                    </div>
                                                                    <label class="form-label">Group</label>
                                                                </div>
                                                            </div>



                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            {{-- History Code --}}
                                            @include('partials.approval-history', [
                                                'document_status' => $data->approvalStatus,
                                                'revision_number' => $data->revision_number,
                                            ])


                                            <div @if ($data->approvalStatus === 'cancel') style="display:none;" @endif>
                                            </div>
                                            <div
                                                class="newheader d-flex justify-content-between align-items-end mt-2 border-top pt-2">
                                                <div class="header-left"
                                                    @if ($data->approvalStatus === 'cancel') style="display:none;" @endif>
                                                    <h4 class="card-title text-theme">Item Wise Detail</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                                {{-- {{ dd($buttons['draft'], $fyear['authorized'] , $buttons['draft'] && !$fyear['authorized']) }} --}}
                                                <div class="header-right">
                                                    @if ($buttons['draft'] && $fyear['authorized'])
                                                        <a href="{{ route('ledgers.create') }}"
                                                            class="btn btn-outline-primary btn-sm" target="_blank"><i
                                                                data-feather="plus"></i> Add Ledger</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>


                                        <div class="table-responsive pomrnheadtffotsticky mt-1"
                                            @if ($data->approvalStatus === 'cancel') style="display:none;" @endif>
                                            <table
                                                class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th width="200px">Ledger Code</th>
                                                        <th>Group</th>
                                                        <th width="150px" class="text-end">Debit Amt</th>
                                                        <th width="150px" class="text-end">Credit Amt</th>
                                                        @if(in_array($data->reference_service,App\Helpers\ConstantHelper::DUE_DATE_ALIAS))
                                                        <th>Due Date</th>
                                                        @endif
                                                        <th width="200px">Cost Center</th>
                                                        <th>Remarks</th>
                                                        <th width="60px">Action</th>
                                                    </tr>
                                                </thead>

                                                <tbody class="mrntableselectexcel" id="item-details-body">
                                                    @forelse ($data->items as $index => $item)
                                                        {{-- <input type="hidden" name="item_id[]" value="{{ $item->id }}"> --}}
                                                        @php
                                                            $no = $index + 1;
                                                        @endphp
                                                        <tr id="{{ $no }}">
                                                            <td class="number">{{ $no }}</td>
                                                            <td class="poprod-decpt">
                                                                <input type="text"
                                                                    class="form-control mw-100 ledgerselect"
                                                                    placeholder="Select Ledger"
                                                                    name="ledger_name{{ $no }}" required
                                                                    id="ledger_name{{ $no }}" required
                                                                    data-id="{{ $no }}"
                                                                    value="{{ $item->ledger->name ?? '' }}" />
                                                                <input type="hidden" name="ledger_id[]" type="hidden"
                                                                    id="ledger_id{{ $no }}"
                                                                    value="{{ $item->ledger_id }}" class="ledgers" />
                                                                <!--<input placeholder="Line Notes" type="text"
                                                                                                                                                                            class="form-control mw-100 mt-50"
                                                                                                                                                                            name="notes1" />-->
                                                            </td>
                                                            <td>
                                                                <select id="groupSelect{{ $no }}"
                                                                    name="parent_ledger_id[]"
                                                                    class="ledgerGroup form-select mw-100"
                                                                    data-ledger="{{ $item->ledger_id }}" required>
                                                                    @if (
                                                                        $item->ledger &&
                                                                            method_exists($item->ledger, 'groups') &&
                                                                            $item->ledger->groups() instanceof \Illuminate\Database\Eloquent\Relations\Relation)
                                                                        @if (is_array($item->ledger->groups))
                                                                            @foreach ($item->ledger->groups as $group)
                                                                                <option value="{{ $group->id }}"
                                                                                    @if ($group->id == $item->ledger_parent_id) selected @endif>
                                                                                    {{ $group->name }}</option>
                                                                            @endforeach
                                                                        @else
                                                                            <option
                                                                                value="{{ $item->ledger->groups->id }}"
                                                                                @if ($item->ledger->groups->id == $item->ledger_parent_id) selected @endif>
                                                                                {{ $item->ledger->groups->name }}</option>
                                                                        @endif
                                                                    @else
                                                                        @foreach ($groups as $group)
                                                                            @if ($group->id == $item->ledger_parent_id)
                                                                                <option value="{{ $group->id }}"
                                                                                    selected> {{ $group->name }}</option>
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </td>
                                                            <input type="hidden" name="group_debit_amt[]"
                                                                id="group_debit_amt_{{ $no }}"
                                                                value="{{ $item->debit_amt_group }}">
                                                            <input type="hidden" name="comp_debit_amt[]"
                                                                id="comp_debit_amt_{{ $no }}"
                                                                value="{{ $item->debit_amt_comp }}">
                                                            <input type="hidden" name="group_credit_amt[]"
                                                                id="group_credit_amt_{{ $no }}"
                                                                value="{{ $item->credit_amt_group }}">
                                                            <input type="hidden" name="comp_credit_amt[]"
                                                                id="comp_credit_amt_{{ $no }}"
                                                                value="{{ $item->credit_amt_comp }}">

                                                            <input type="hidden"
                                                                class="dbt_amt_inr debt_inr_{{ $no }}"
                                                                name="org_debit_amt[]" id="dept_inr_{{ $no }}"
                                                                value="{{ $item->debit_amt_org }}" />

                                                            <input type="hidden"
                                                                class="crd_amt_inr crd_inr_{{ $no }}"
                                                                name="org_credit_amt[]" id="crd_inr_{{ $no }}"
                                                                value="{{ $item->credit_amt_org }}" />


                                                            <td><input @if(!$buttons['draft']) type="text" @else type="number" @endif  
                                                                    class="form-control mw-100 dbt_amt debt_{{ $no }} text-end @if(!$buttons['draft']) indian-number @endif"
                                                                    name="debit_amt[]" id="dept_{{ $no }}"
                                                                    onfocus="focusInput(this)" min="0"
                                                                    step="0.01" value="{{ $item->debit_amt }}" /></td>

                                                            <td><input @if(!$buttons['draft']) type="text" @else type="number" @endif  
                                                                    class="form-control mw-100 crd_amt crd_{{ $no }} text-end @if(!$buttons['draft']) indian-number @endif"
                                                                    name="credit_amt[]" id="crd_{{ $no }}"
                                                                    onfocus="focusInput(this)" min="0"
                                                                    step="0.01" value="{{ $item->credit_amt }}" />
                                                            </td>
                                                             @if(in_array($data->reference_service,App\Helpers\ConstantHelper::DUE_DATE_ALIAS))
                                                             <td><input type="date" value="{{$item->due_date ??""}}" class="form-control mw-100"/></td>  
                                                           
                                                            @endif
                                                            <td>
                                                                <select class="costCenter form-select mw-100"
                                                                    name="cost_center_id[]"
                                                                    id="cost_center_id{{ $no }}">
                                                                    
                                                                    @if($item->cost_center_id==null && count($locationCostCenters)>0)
                                                                    <option value="" selected>Select</option>
                                                                    @endif
                                                                    
                                                                    @foreach ($locationCostCenters as $value)
                                                                        <option value="{{ $value['id'] }}"
                                                                            @if ($value['id'] == $item->cost_center_id) selected @endif>
                                                                            {{ $value['name'] }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>


                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control mw-100 remarks_"
                                                                    placeholder="Enter Remarks"
                                                                    id="hiddenRemarks_{{ $no }}"
                                                                    value="{{ $item->remarks }}" name="item_remarks[]">
                                                                <div class="d-flex">
                                                                    <div hidden class="me-50 cursor-pointer remark-btn"
                                                                        data-row-id="{{ $no }}"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#remarksModal"><span
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-placement="top" title="Remarks"
                                                                            class="text-primary"><i
                                                                                data-feather="file-text"></i></span>
                                                                    </div>
                                                            </td>
                                                            <td>
                                                                @if ($buttons['draft'] && $fyear['authorized'])
                                                                    <div class="me-50 cursor-pointer"><span
                                                                            data-bs-placement="top"
                                                                            class="text-danger remove-item"><i
                                                                                data-feather="trash-2"></i></span>
                                                                    </div>
                                                                @endif
                                        </div>

                                        </td>
                                        </tr>
                                    @empty
                                        <div>No Item data..</div>
                                        @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr class="totalsubheadpodetail voucher-tab-foot">
                                                <td colspan="3"></td>
                                                <td class="text-end">
                                                    <h5 id="dbt_total">0.00</h5>
                                                    <input type="hidden" name="amount" id="amount">
                                                </td>
                                                <td hidden class="text-end">
                                                    <h5 id="dbt_total_inr">0.00</h5>
                                                </td>
                                                <td class="text-end">
                                                    <h5 id="crd_total">0.00</h5>
                                                </td>
                                                <td hidden class="text-end">
                                                    <h5 id="crd_total_inr">0.00</h5>
                                                </td>
                                                 
                                                <td colspan="{{in_array($data->reference_service,App\Helpers\ConstantHelper::DUE_DATE_ALIAS)?4:3}}" class="text-end">
                                                    @if ($buttons['draft'] && $fyear['authorized'])
                                                        <a href="#"
                                                            class="text-primary add-contactpeontxt mt-0 add-item-row"
                                                            id="addnew"><i data-feather='plus'></i> Add New
                                                            Item</a>
                                                    @endif
                                                </td>

                                            </tr>
                                            <tr valign="top" class="voucher_details" id="voucher-details-row">
                                                <td colspan="9" rowspan="10">
                                                    <table class="table border">
                                                        <tr>
                                                            <td class="p-0">
                                                                <h6 class="text-dark mb-0 bg-light-primary py-1 px-50">
                                                                    <strong>Voucher Details</strong>
                                                                </h6>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="poprod-decpt">
                                                                <span class="poitemtxt mw-100"><strong>Ledger Name:</strong><span id="ledger_name_details">-</span></span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                        <tr>
                                                            <td class="poprod-decpt">
                                                                <span
                                                                    class="badge rounded-pill badge-light-primary"><strong>Base
                                                                        Currency:</strong> <span id="base-currency"
                                                                        class="text-uppercase">-</span></span>
                                                                <span
                                                                    class="badge rounded-pill badge-light-primary"><strong>Debit
                                                                        Amt:</strong> <span id="base-debit">-</span></span>
                                                                <span
                                                                    class="badge rounded-pill badge-light-primary"><strong>Credit
                                                                        Amt:</strong> <span
                                                                        id="base-credit">-</span></span>
                                                            </td>
                                                        </tr>

                                                        <td class="poprod-decpt">
                                                            <span class="badge rounded-pill badge-light-primary"><strong>Company
                                                                    Currency:</strong> <span id="company-currency"
                                                                    class="text-uppercase">-</span></span>
                                                            <span class="badge rounded-pill badge-light-primary"><strong>Debit
                                                                    Amt:</strong> <span id="company-debit">-</span></span>
                                                            <span class="badge rounded-pill badge-light-primary"><strong>Credit
                                                                    Amt:</strong> <span id="company-credit">-</span></span>
                                                        </td>
                                            </tr>
                                            <tr>
                                                <td class="poprod-decpt">
                                                    <span class="badge rounded-pill badge-light-primary"><strong>Group
                                                            Currency:</strong> <span id="group-currency"
                                                            class="text-uppercase">-</span></span>
                                                    <span class="badge rounded-pill badge-light-primary"><strong>Debit
                                                            Amt:</strong> <span id="group-debit">-</span></span>
                                                    <span class="badge rounded-pill badge-light-primary"><strong>Credit
                                                            Amt:</strong> <span id="group-credit">-</span></span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="poprod-decpt">
                                                    <span
                                                        class="badge rounded-pill badge-light-secondary"><strong>Remarks:</strong>
                                                        <span id="remarks">Description will
                                                            come here for items...</span></span>
                                                </td>
                                            </tr>
                                            </table>
                                            </td>
                                            </tr>
                                        </tfoot>
                                        </table>
                                    </div>



                                    <div class="row mt-2" @if ($data->approvalStatus === 'cancel') style="display:none;" @endif>

                                        <div class="col-md-4 mb-1">
                                            <label class="form-label">Document</label>
                                            <input type="file" onchange="checkFileTypeandSize(event)"
                                                class="form-control" multiple name="document[]" id="document" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label"></label>
                                            <div id="preview">
                                                @php
                                                    $documents = $data->document
                                                        ? json_decode($data->document, true)
                                                        : [];
                                                    if (!is_array($documents) && $data->document) {
                                                        $documents[] = $data->document;
                                                    }

                                                @endphp

                                                @isset($documents)
                                                    @foreach ($documents as $key1 => $fileGroup)
                                                        @php
                                                            // Extract file extension
                                                            $extension = pathinfo($fileGroup, PATHINFO_EXTENSION);
                                                            // Set default icon
                                                            $icon = 'file-text';
                                                            switch (strtolower($extension)) {
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
                                                                    $icon = 'archive';
                                                                    break;
                                                                default:
                                                                    $icon = 'file';
                                                                    break;
                                                            }
                                                        @endphp
                                                        <div class="image-uplodasection expenseadd-sign"
                                                            data-file-index="{{ $key1 }}">
                                                            <a href="{{ url('') }}/voucherDocuments/{{ $fileGroup }}"
                                                                target="_blank">
                                                                <i data-feather="{{ $icon }}"
                                                                    class="fileuploadicon"></i>
                                                        </div>
                                                        </a>
                                                    @endforeach
                                                @endisset
                                                </td>
                                            </div>

                                        </div>


                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <label class="form-label">Final Remarks</label>
                                                <textarea type="text" rows="4" class="form-control" placeholder="Enter Remarks here..." name="remarks">{{ $data->remarks }}</textarea>

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
    </form>

    </div>
    </div>
    <!-- END: Content-->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form class="ajax-input-form" method="POST" action="{{ route('approveVoucher') }}"
                    data-redirect="{{ route('vouchers.index') }}" enctype='multipart/form-data'>
                    @csrf
                    <input type="hidden" name="action_type" id="action_type">
                    <input type="hidden" name="id" value="{{ $data->id }}">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="approve_reject_heading_label"></h4>
                            <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{ Carbon\Carbon::now()->format('d-m-Y') }}
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                   <div class="modal-body pb-2">
            <div class="row mt-1">
               <div class="col-md-12">
                  <div class="mb-1">
                     <label class="form-label">Remarks</label>
                     <textarea name="remarks" class="form-control cannot_disable"></textarea>
                  </div>
                  <div class="row">
                    <div class = "col-md-8">
                        <div class="mb-1">
                            <label class="form-label">Upload Document</label>
                            <input type="file" name = "attachments[]" multiple class="form-control cannot_disable" onchange = "addFiles(this, 'approval_files_preview');" max_file_count = "2"/>
                        </div>
                    </div>
                    <div class = "col-md-4" style = "margin-top:19px;">
                        <div class = "row" id = "approval_files_preview">

                        </div>
                    </div>
                  </div>
                  <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                  
               </div>
            </div>
         </div>
                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submit-button">Submit</button>
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
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Voucher</strong>? After Amendment
                        this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" onclick="submitamend('voucherForm')" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div> 
    <div class="modal fade" id="remarksModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
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
                            <textarea class="form-control" id="remarksInput" placeholder="Enter Remarks"></textarea>
                        </div>
                    </div>
                    <!-- Hidden field to store the current row ID -->
                    <input type="hidden" id="currentRowId">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitRemarks">Submit</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ url('/app-assets/js/jquery-ui.js') }}"></script>
    <script src="{{asset('assets/js/fileshandler.js')}}"></script>
    <script>
        // $('#voucherForm').on('submit', function () {
        //     $('.preloader').show();
        // });
        $('.voucher_details').hide();

        var currencies = {!! json_encode($currencies) !!};
        var orgCurrency = {{ $orgCurrency }};
        var orgCurrencyName = '';


        $(document).ready(function() {
            $('#book_type_id').select2();
            let selectedOption = $('#book_type_id').find('option:selected');
            let cv = @json(ConstantHelper::CONTRA_VOUCHER);
            let allowedNames = @json($allowedCVGroups);
            let jv = @json(ConstantHelper::JOURNAL_VOUCHER);
            let excludeNames = @json($exlucdeJVGroups);

            // Check if selected option's data-alias is equal to contra_alias (e.g., 'cv')
            if (selectedOption.data('alias') === cv) {
                $('.ledgerGroup').each(function() {
                    let text = $(this).text().trim();
                    console.log("allowed " + allowedNames, text);
                    // get the visible text of each ledger group
                    if (!allowedNames.includes(text) && (text != "")) {
                        let id = $(this).closest('tr').attr('id');
                        $('#ledger_name' + id).val('');
                        $('#ledger_id' + id).val('');
                        $('#groupSelect' + id).val('');
                    }
                });
            } else if (selectedOption.data('alias') === jv) {
                $('.ledgerGroup').each(function() {
                    let text = $(this).text().trim();
                    console.log("exclude " + excludeNames, text);
                    if (excludeNames.includes(text) && (text != "")) {
                        let id = $(this).closest('tr').attr('id');
                        console.log(excludeNames, text, id);

                        $('#ledger_name' + id).val('');
                        $('#ledger_id' + id).val('');
                        $('#groupSelect' + id).val('');

                    }
                });
            }

            if (orgCurrency != "") {
                $.each(currencies, function(key, value) {
                    if (value['id'] == orgCurrency) {
                        orgCurrencyName = value['short_name'];
                    }
                });
            }

            if ($('#currency_code').val() == "") {
                console.log('default');
                getExchangeRate();
            } else {
                calculate_cr_dr();
            }



            // Unified event handler for row and input/select clicks
            $('#item-details-body').on('click', 'tr, input, select', function(event) {
                const row = $(this).closest('tr');
                const rowId = row.attr('id'); // Get the row ID
                $('#item-details-body tr').removeClass('trselected');
                row.addClass('trselected');
                handleRowClick(row);
            });


            $('.remark-btn').on('click', function() {
                const rowId = $(this).data('row-id'); // Get the row ID
                const currentRemarks = $(`#hiddenRemarks_${rowId}`)
                    .val(); // Fetch the current remarks from the hidden input

                // Populate the modal
                $('#currentRowId').val(rowId);
                $('#remarksInput').val(currentRemarks.trim());
            })
            // Handle modal submission
            $('#submitRemarks').on('click', function() {
                const rowId = $('#currentRowId').val();
                const newRemarks = $('#remarksInput').val();

                // Update the hidden input
                $(`#hiddenRemarks_${rowId}`).val(newRemarks);
                handleRowClick(rowId);


                $('#remarksModal').modal('hide'); // Close the modal

            });


        });

        function getExchangeRate() {
            $('#item-details-body tr').removeClass('trselected');
            $('.voucher_details').hide();
            $('.selectedCurrencyName').text('');

            if (orgCurrency != "") {
                let currency = parseFloat($('#currency_id').val()) || 0;
                if (currency != 0) {
                    console.log(currency);
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ route('getExchangeRate') }}',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            date: $('#date').val(),
                            '_token': '{!! csrf_token() !!}',
                            currency: currency
                        },
                        success: function(response) {
                            if (response.status) {


                                $('#orgExchangeRate').val(response.data.org_currency_exg_rate)
                                    .trigger(
                                        'change');

                                $('#currency_code').val(response.data.party_currency_code);


                                $('#org_currency_id').val(response.data.org_currency_id);
                                $('#org_currency_code').val(response.data.org_currency_code);
                                $('#base_currency_code').val(response.data.org_currency_code);

                                $('.selectedCurrencyName').text("(" + $('#org_currency_code').val() + ")");

                                $('#org_currency_exg_rate').val(response.data
                                    .org_currency_exg_rate);

                                $('#comp_currency_id').val(response.data.comp_currency_id);
                                $('#comp_currency_code').val(response.data.comp_currency_code);
                                $('#comp_currency_exg_rate').val(response.data
                                    .comp_currency_exg_rate);

                                $('#company_currency_code').val(response.data.comp_currency_code);
                                $('#company_exchange_rate').val(response.data
                                    .comp_currency_exg_rate);

                                $('#group_currency_id').val(response.data.group_currency_id);
                                $('#group_currency_code').val(response.data.group_currency_code);
                                $('#group_currency_exg_rate').val(response.data
                                    .group_currency_exg_rate);
                                $('#grp_currency_code').val(response.data.group_currency_code);
                                $('#grp_exchange_rate').val(response.data
                                    .group_currency_exg_rate);
                                calculate_cr_dr();

                            } else {
                                resetCurrencies();
                                $('#orgExchangeRate').val('');
                                showToast("error", response.message);
                            }
                        }
                    });

                } else {
                    resetCurrencies();
                }
            } else {
                showToast("error", 'Organization currency is not set!!');
            }
        }

        function resetCurrencies() {
            $('#org_currency_id').val('');
            $('#org_currency_code').val('');
            $('#org_currency_exg_rate').val('');

            $('#comp_currency_id').val('');
            $('#comp_currency_code').val('');
            $('#comp_currency_exg_rate').val('');

            $('#group_currency_id').val('');
            $('#group_currency_code').val('');
            $('#group_currency_exg_rate').val('');
            $('#orgExchangeRate').val('');
        }


        function submitForm(status) {
            $('#status').val(status);
            $('#submitButton').click();
        }


        var costcenters = {!! json_encode($cost_centers) !!};
        var bookTypes = {!! json_encode($bookTypes) !!};

        $(document).bind('ctrl+n', function() {
            document.getElementById('addnew').click();
        });

        function check_amount() {
            $('#draft').attr('disabled', true);
            $('#submitted').attr('disabled', true);
            //$('.preloader').show();

            let seen = new Set(); // Create a Set to track unique combinations
            let duplicateFound = false; // Flag to track duplicates

            $('.ledgerGroup').each(function(index) {
                let ledgerGroup = $(this).val(); // Get the selected value
                let ledger_id = $(this).data('ledger'); // Get ledger ID from data attribute

                let key = ledger_id + '-' + ledgerGroup; // Create a unique key for comparison

                if (seen.has(key)) {
                    duplicateFound = true; // Set flag if duplicate found
                    return false; // Break out of .each loop early
                }

                seen.add(key); // Add key to Set
            });

            if (duplicateFound) {
              //  $('.preloader').hide();
                showToast("error", "Duplicate ledger groups found. Please correct and try again.");
                return false;
            }
            let stop = false;


            $('#item-details-body tr').each(function() {
                let debAmount = parseFloat(removeCommas($(this).find('.dbt_amt').val())) || 0;
                let crdAmount = parseFloat(removeCommas($(this).find('.crd_amt').val())) || 0;

                // Check if both the credit and debit amounts are 0
                if (debAmount == 0 && crdAmount == 0) {
                //    $('.preloader').hide();
                    showToast('error', 'Can not save ledgers with Credit and Debit amount both being 0');
                    $('#draft').attr('disabled', false);
                    $('#submitted').attr('disabled', false);
                    stop = true;
                    return false; // Stop the loop and return false
                }
            });
            if (stop)
                return false;

            if (parseFloat(removeCommas($('#crd_total').text())) == 0 || parseFloat(removeCommas($('#dbt_total').text())) ==
                0) {
                //$('.preloader').hide();
                showToast("error", 'Debit and credit amount should be greater than 0');
                $('#draft').attr('disabled', false);
                $('#submitted').attr('disabled', false);
                return false;
            }
            if (parseFloat(removeCommas($('#crd_total').text())) == parseFloat(removeCommas($('#dbt_total').text()))) {
                $('.preloader').show();
                return true;
            } else {
                //$('.preloader').hide();
                showToast("error", 'Debit and credit amount total should be same!!');
                $('#draft').attr('disabled', false);
                $('#submitted').attr('disabled', false);
                return false;
            }
        }

        $(document).on('input', '.dbt_amt, .crd_amt, .dbt_amt_inr, .crd_amt_inr,.remarks_', function() {
            const inVal = parseFloat(removeCommas($(this).val())) || 0;
            const rowId = $(this).closest('tr'); // Get the row ID
            const $row = $(this).closest('tr'); // Find the row of the current input

            if ($(this).hasClass('dbt_amt')) {
                $row.find('.crd_amt').val(0);
            } else if ($(this).hasClass('crd_amt')) {
                $row.find('.dbt_amt').val(0);
            }
            handleRowClick(rowId);
            calculate_cr_dr();
        });

        // Moving between input fields on pressing ENTER
        $(document).on('keydown', function(event) {
            if (event.keyCode === 13) {
                var activeElement = document.activeElement;
                if (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA') {
                    // Check if the input is not hidden
                    if (activeElement.type !== 'hidden') {
                        event.preventDefault(); // Prevent default enter key behavior

                        // Get the next sibling in the current row
                        var nextField = activeElement.nextElementSibling;
                        while (nextField && nextField.type === 'hidden') {
                            nextField = nextField.nextElementSibling;
                        }

                        // If there's a next field in the row, focus on it
                        if (nextField) {
                            nextField.focus();
                            return; // Stop further navigation within the row
                        }

                        // Otherwise, find the first input in the next column
                        var nextColumn = activeElement.closest('td').nextElementSibling;
                        if (nextColumn) {
                            nextField = nextColumn.querySelector('input, textarea');
                            if (nextField) {
                                nextField.focus();
                                return; // Stop further navigation within the row
                            }
                        }

                        // Otherwise, find the first input in the next row
                        var nextRow = activeElement.closest('tr').nextElementSibling;
                        if (nextRow) {
                            nextField = nextRow.querySelector('input, textarea');
                            if (nextField) {
                                nextField.focus();
                            }
                        }
                    }
                }
            }
        });

        $(document).on('click', '.remove-item', function() {
            const $row = $(this).closest('tr');
            const totalRows = $('#item-details-body tr').length;

            if (totalRows <= 1) {
                showToast("warning", "At least one row must remain."); // Optional toast/alert
                return; // Don't remove
            }

            $row.remove(); // Remove the row
            updateRowNumbers();
            calculate_cr_dr(); // Your custom function
        });

        function rate_change() {
            $('.voucher_details').hide();

        }

        function populateCostCenterDropdowns() {
            let selectedLocationIds = $('#locations').val();

            const costCenterSet = locationCostCentersMap.filter(center => {
                if (!center.location) return false;
                const locationArray = Array.isArray(center.location) ?
                    center.location.flatMap(loc => loc.split(',')) : [];
                return locationArray.includes(String(selectedLocationIds));
            });

            // Update all .costCenter selects
            $('.costCenter').each(function() {
                let $dropdown = $(this);
                $dropdown.empty();
                costCenterSet.forEach((center) => {
                    $dropdown.append(`<option value="${center.id}">${center.name}</option>`);
                });
            });
        }

        function populateSingleCostCenterDropdown($dropdown,val) {
            let selectedLocationIds = $('#locations').val();

            const costCenterSet = locationCostCentersMap.filter(center => {
                if (!center.location) return false;
                const locationArray = Array.isArray(center.location) ?
                    center.location.flatMap(loc => loc.split(',')) : [];
                return locationArray.includes(String(selectedLocationIds));
            });


            $dropdown.empty();
           costCenterSet.forEach((center) => {
                const isSelected = String(center.id) === String(val) ? 'selected' : '';
                $dropdown.append(`<option value="${center.id}" ${isSelected}>${center.name}</option>`);
            });
        }

        function calculate_cr_dr() {
            $('#org_currency_exg_rate').val($('#orgExchangeRate').val());
            const exchangeRate = parseFloat($('#orgExchangeRate').val()) ||
                1; // Assume an input for exchange rate with id 'exchange_rate'

            const exchangeRateComp = parseFloat($('#comp_currency_exg_rate').val()) ||
                1; // Assume an input for exchange rate with id 'exchange_rate'

            const exchangeRateGroup = parseFloat($('#group_currency_exg_rate').val()) ||
                1; // Assume an input for exchange rate with id 'exchange_rate'

            $('#item-details-body tr').each(function() {
                const rowId = $(this).attr('id'); // Get the row ID

                // Get the debit and credit values for the current row
                const debitAmt = parseFloat(removeCommas($(`#dept_${rowId}`).val())) || 0;
                const creditAmt = parseFloat(removeCommas($(`#crd_${rowId}`).val())) || 0;

                // Organization Rate
                $(`#dept_inr_${rowId}`).val((debitAmt * exchangeRateComp).toFixed(2));
                $(`#crd_inr_${rowId}`).val((creditAmt * exchangeRateComp).toFixed(2));

                //Company Rate
                $(`#comp_debit_amt_${rowId}`).val((debitAmt * exchangeRateComp).toFixed(2));
                $(`#comp_credit_amt_${rowId}`).val((creditAmt * exchangeRateComp).toFixed(2));


                //Group Rate
                $(`#group_debit_amt_${rowId}`).val((debitAmt * exchangeRateGroup).toFixed(2));
                $(`#group_credit_amt_${rowId}`).val((creditAmt * exchangeRateGroup).toFixed(2));
            });

            let cr_sum = 0;
            let cr_sum_inr = 0;
            let dr_sum = 0;
            let dr_sum_inr = 0;
            $('.crd_amt').each(function() {
                const value = parseFloat(removeCommas($(this).val())) || 0;
                cr_sum += value;
            });

            // Iterate over credit INR amount fields
            $('.crd_amt_inr').each(function() {
                const value = parseFloat(removeCommas($(this).val())) || 0;
                cr_sum_inr += value;
            });

            // Iterate over debit amount fields
            $('.dbt_amt').each(function() {
                const value = parseFloat(removeCommas($(this).val())) || 0;
                dr_sum += value;
            });

            // Iterate over debit INR amount fields
            $('.dbt_amt_inr').each(function() {
                const value = parseFloat(removeCommas($(this).val())) || 0;
                dr_sum_inr += value;
            });
            $('#crd_total_inr').text(formatIndianNumber(cr_sum_inr.toFixed(2)));
            $('#crd_total').text(formatIndianNumber(cr_sum.toFixed(2)));
            $('#dbt_total').text(formatIndianNumber(dr_sum.toFixed(2)));
            $('#dbt_total_inr').text(formatIndianNumber(dr_sum_inr.toFixed(2)));

            $('#amount').val(dr_sum);

        }

        var books = [];
        document.addEventListener('DOMContentLoaded', function() {
            // Add new item row
            document.querySelector('.add-item-row').addEventListener('click', function(e) {
                e.preventDefault();


                var cr_amount = 0;
                var dr_amount = 0;

                $('.dbt_amt').each(function() {
                    const value = parseFloat(removeCommas($(this).val())) || 0;
                    $(this).val(value.toFixed(2));

                });
                $('.crd_amt').each(function() {
                    const value = parseFloat(removeCommas($(this).val())) || 0;
                    $(this).val(value.toFixed(2));

                });

                if (parseFloat(removeCommas($('#crd_total').text())) == parseFloat(removeCommas($(
                            '#dbt_total')
                        .text()))) {} else if (
                    parseFloat(removeCommas($('#crd_total').text())) > parseFloat(removeCommas($(
                        '#dbt_total').text()))) {
                    dr_amount = parseFloat(removeCommas($('#crd_total').text())) - parseFloat(removeCommas(
                        $('#dbt_total')
                        .text()));
                } else {
                    cr_amount = parseFloat(removeCommas($('#dbt_total').text())) - parseFloat(removeCommas(
                        $('#crd_total')
                        .text()));
                }

                let rowCount = document.querySelectorAll('#item-details-body tr').length;
                rowCount = Number($('#item-details-body tr:last').attr('id'));

                let totalDebit = parseFloat(removeCommas($('#dbt_total').text()));
                let totalCredit = parseFloat(removeCommas($('#crd_total').text()));
                let balanceDebit = totalDebit - totalCredit; // Calculate the balance for debit
                let balanceCredit = totalCredit - totalDebit; // Calculate the balance for credit

                balanceDebit = balanceDebit.toFixed(2);
                balanceCredit = balanceCredit.toFixed(2);

                let newRow = `
                <tr id="${rowCount + 1}">
                    <td class="number">${rowCount + 1}</td>
                    <td class="poprod-decpt">
                        <input type="text"
                            class="form-control mw-100 ledgerselect"
                            placeholder="Select Ledger" name="ledger_name${rowCount + 1}"
                            required id="ledger_name${rowCount + 1}"
                            data-id="${rowCount + 1}" />
                        <input type="hidden" name="ledger_id[]" type="hidden" id="ledger_id${rowCount + 1}" class="ledgers" />
                    </td>
                    <td>
                        <select required id="groupSelect${rowCount + 1}" name="parent_ledger_id[]" class="ledgerGroup form-select mw-100">
                        </select>
                    </td>
                    <input type="hidden" name="group_debit_amt[]" id="group_debit_amt_${rowCount + 1}" value="0">
                    <input type="hidden" name="comp_debit_amt[]" id="comp_debit_amt_${rowCount + 1}" value="0">
                    <input type="hidden" name="group_credit_amt[]" id="group_credit_amt_${rowCount + 1}" value="0">
                    <input type="hidden" name="comp_credit_amt[]" id="comp_credit_amt_${rowCount + 1}" value="0">
                    <input type="hidden" class="dbt_amt_inr debt_inr_${rowCount + 1}" name="org_debit_amt[]" id="dept_inr_${rowCount + 1}" />
                    <input type="hidden" class="crd_amt_inr crd_inr_${rowCount + 1}" name="org_credit_amt[]" id="crd_inr_${rowCount + 1}" />

                    <td>
                        <input type="number" class="form-control mw-100 dbt_amt debt_${rowCount + 1} text-end" onfocus="focusInput(this)"
                            name="debit_amt[]" id="dept_${rowCount + 1}" min="0" step="0.01"
                            value="${balanceCredit > 0 ? balanceCredit : 0}"/>
                    </td>
                    <td>
                        <input type="number" class="form-control mw-100 crd_amt crd_${rowCount + 1} text-end" onfocus="focusInput(this)"
                            name="credit_amt[]" id="crd_${rowCount + 1}" min="0" step="0.01"
                            value="${balanceDebit > 0 ? balanceDebit : 0}"/>
                    </td>
                   <td>
                        <select class="costCenter form-select mw-100" name="cost_center_id[]" id="cost_center_id${rowCount + 1}">
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control mw-100 remarks_" placeholder="Enter Remarks"
                            id="hiddenRemarks_${rowCount + 1}" name="item_remarks[]" value="">
                    </td>
                    <td>
                                                                    @if ($buttons['draft'] && $fyear['authorized'])

                                                                    <div class="me-50 cursor-pointer"><span

                                                                            data-bs-placement="top"
                                                                            class="text-danger remove-item"><i
                                                                                data-feather="trash-2"></i></span>
                                                                    </div>
                                                                    @endif
                                                                </div>

                                                            </td>
                </tr>
                `;
                feather.replace();

                document.querySelector('#item-details-body').insertAdjacentHTML('beforeend',
                    newRow);
                feather.replace();
                initializeLedgerAutocomplete($("#item-details-body"));
                calculate_cr_dr();
                // Populate cost centers for the new row's dropdown
                let selected = $(`#cost_center_id${rowCount}`).val();
                populateSingleCostCenterDropdown($(`#cost_center_id${rowCount + 1}`),selected);
               
                updateRowNumbers();





            });

        });

        function getBooks() {
            $('#book_id').empty();
            $('#book_id').prepend('<option disabled selected value="">Select Series</option>');

            const book_type_id = $('#book_type_id').val();
            $.each(bookTypes, function(key, value) {
                if (value['id'] == book_type_id) {
                    books = value['books'];
                }
            });

            $.each(books, function(key, value) {
                $("#book_id").append("<option value ='" + value['id'] + " '>" + value['book_code'] + " </option>");
            });

        }

        function get_voucher_details() {
            $.each(books, function(key, value) {
                if (value['id'] == $('#book_id').val()) {
                    $('#voucher_name').val(value['book_name']);
                }
            });

            $.ajax({
                url: '{{ url('get_voucher_no') }}/' + $('#book_id').val(),
                type: 'GET',
                success: function(data) {
                    if (data.type == "Auto") {
                        $("#voucher_no").attr("readonly", true);
                        $('#voucher_no').val(data.voucher_no);
                    } else {
                        $("#voucher_no").attr("readonly", false);
                    }
                }
            });
        }

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

            const dateInput = document.getElementById("date");

            // Determine the max and min values for the date input
            const today = moment().format("YYYY-MM-DD");
            const fyearStartDate = "{{ $fyear['start_date'] }}";
            const fyearEndDate = "{{ $fyear['end_date'] }}";
            // console.log('here',1,fyearStartDate, fyearEndDate);

            if (backDateAllowed && futureDateAllowed) {
                // dateInput.removeAttribute("min");
                // dateInput.removeAttribute("max");
                // console.log('here',1,fyearStartDate, fyearEndDate);
                dateInput.setAttribute("min", fyearStartDate);
                dateInput.setAttribute("max", fyearEndDate);
            } else if (backDateAllowed) {
                dateInput.setAttribute("max", today);
                dateInput.setAttribute("min", fyearStartDate);
                // console.log('here',2);
            } else if (futureDateAllowed) {
                dateInput.setAttribute("min", today);
                dateInput.setAttribute("max", fyearEndDate);
                // console.log('here',3);
            } else {
                dateInput.setAttribute("min", today);
                dateInput.setAttribute("max", today);
                // console.log('here',4);
            }
        }

        function getDocNumberByBookId() {
            resetParametersDependentElements(null);
            let currentDate = new Date().toISOString().split('T')[0];
            let bookId = $('#book_id').val();
            let document_date = $('#date').val();
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId +
                "&document_date=" +
                document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        resetParametersDependentElements(data.data);
                        $("#book_code_input").val(data.data.book_code);
                        $("#voucher_name").val($("#book_id option:selected").text());
                        if (!data.data.doc.document_number) {
                            $("#voucher_no").val('');
                            $('#doc_number_type').val('');
                            $('#doc_reset_pattern').val('');
                            $('#doc_prefix').val('');
                            $('#doc_suffix').val('');
                            $('#doc_no').val('');
                        } else {
                            $("#voucher_no").val(data.data.doc.document_number);
                            $('#doc_number_type').val(data.data.doc.type);
                            $('#doc_reset_pattern').val(data.data.doc.reset_pattern);
                            $('#doc_prefix').val(data.data.doc.prefix);
                            $('#doc_suffix').val(data.data.doc.suffix);
                            $('#doc_no').val(data.data.doc.doc_no);
                        }
                        if (data.data.doc.type == 'Manually') {
                            $("#voucher_no").attr('readonly', false);
                        } else {
                            $("#voucher_no").attr('readonly', true);
                        }

                    }
                    if (data.status == 404) {
                        $("#voucher_no").val('');
                        $('#doc_number_type').val('');
                        $('#doc_reset_pattern').val('');
                        $('#doc_prefix').val('');
                        $('#doc_suffix').val('');
                        $('#doc_no').val('');
                        showToast("error", data.message);
                    }
                });
            });
        }

        function handleRowClick(rowElement) {
            const $row = $(rowElement); // Accept DOM/jQuery row directly

            $('.voucher_details').show();

            const rowId = $row.data('row-id') || $row.attr('id') || ''; // fallback if needed
            const ledgerName = $row.find('td').eq(1).find('input[name^="ledger_name"]').val();
            const debitAmount = parseFloat(removeCommas($row.find('td').eq(3).find('input').val() || '0')) || 0;
            const creditAmount = parseFloat(removeCommas($row.find('td').eq(4).find('input').val() || '0')) || 0;

            const compCurrency = $('#comp_currency_code').val() || '';
            const groupCurrency = $('#group_currency_code').val() || '';
            const baseCurrency = $('#org_currency_code').val() || '';

            const compRate = parseFloat($('#comp_currency_exg_rate').val()) || 1;
            const groupRate = parseFloat($('#group_currency_exg_rate').val()) || 1;
            const baseRate = parseFloat($('#org_currency_exg_rate').val()) || 1;

            const companyDebit = debitAmount * compRate;
            const companyCredit = creditAmount * compRate;
            const groupDebit = debitAmount * groupRate;
            const groupCredit = creditAmount * groupRate;
            const baseDebit = debitAmount * baseRate;
            const baseCredit = creditAmount * baseRate;

            const remark = $(`#hiddenRemarks_${rowId}`).val() || 'No remarks available';

            $('#ledger_name_details').text(ledgerName || '-');
            $('#company-currency').text(compCurrency);
            $('#company-debit').text(formatIndianNumber(companyDebit.toFixed(2)));
            $('#company-credit').text(formatIndianNumber(companyCredit.toFixed(2)));
            $('#group-currency').text(groupCurrency);
            $('#base-currency').text(baseCurrency);
            $('#group-debit').text(formatIndianNumber(groupDebit.toFixed(2)));
            $('#group-credit').text(formatIndianNumber(groupCredit.toFixed(2)));
            $('#base-debit').text(formatIndianNumber(baseDebit.toFixed(2)));
            $('#base-credit').text(formatIndianNumber(baseCredit.toFixed(2)));
            $('#remarks').text(remark);
            $('#voucher-details-row').data('row-id', rowId);
        }
        $(document).on('click', '#amendmentSubmit', (e) => {
            let actionUrl = "{{ route('vouchers.amendment', $data->id) }}";
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

        function setApproval() {
            document.getElementById('action_type').value = "approve";
           document.getElementById('approve_reject_heading_label').textContent = "Approve Voucher";

        }

        function setReject() {
            document.getElementById('action_type').value = "reject";
            document.getElementById('approve_reject_heading_label').textContent = "Reject Voucher";

        }



        $(function() {
            $("#revisionNumber").change(function() {
                const fullUrl = "{{ route('vouchers.edit', ['voucher' => $data->id]) }}?revisionNumber=" +
                    $(this)
                    .val();
                window.open(fullUrl, "_blank");
            });
        });

        function checkFileTypeandSize(event) {
            const file = event.target.files[0];

            if (file) {
                const maxSizeMB = 5;
                const fileSizeMB = file.size / (1024 * 1024);

                const videoExtensions = /(\.mp4|\.avi|\.mov|\.wmv|\.mkv)$/i;
                if (videoExtensions.exec(file.name)) {
                    showToast("error", "Video files are not allowed.");
                    event.target.value = "";
                    return;
                }

                if (fileSizeMB > maxSizeMB) {
                    showToast("error", "File size should not exceed 5MB.");
                    event.target.value = "";
                    return;
                }
                handleFileUpload(event, `#preview`);
            }
        }

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

        function updateRowNumbers() {
            $('#item-details-body tr').each(function(index) {
                // Update the number column (index starts at 0, so add 1)
                $(this).find('.number').text(index + 1);
            });
        }

        @if (!$buttons['draft'] || !$fyear['authorized'])
            $('#voucherForm').find('input, select, textarea').prop('disabled', true);
            $('#revisionNumber').prop('disabled', false);
        @endif
        function focusInput(inputElement) {
            // Check if the input value is "0"
            if (inputElement.value === "0" || inputElement.value === "0.00") {
                // Clear the input field
                inputElement.value = "";
            }
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

        $(document).on('click', '#revokeButton', (e) => {
            $('.preloader').show();
            let actionUrl = '{{ route('voucher.revoke.document') }}' + '?id=' + '{{ $data->id }}';
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    $('.preloader').hide();
                    if (data.status == 'error') {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                        });
                    } else {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                        });
                    }
                    location.reload();
                });
            });
        });
        $(document).on('click', '#cancelButton', (e) => {
            e.preventDefault(); // Prevent default behavior
            // Show confirmation dialog
            Swal.fire({
                title: 'Are you sure to cancel?',
                text: "Your all ledger entries will be deleted, also same voucher no. can't be used and this action cannot be undo.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, cancel it!',
                cancelButtonText: 'No, keep it',
            }).then((result) => {

                if (result.isConfirmed) {
                    $('.preloader').show();
                    // Proceed with AJAX request after confirmation
                    let actionUrl = '{{ route('voucher.cancel.document') }}' + '?id=' +
                        '{{ $data->id }}';

                    fetch(actionUrl)
                        .then(response => response.json())
                        .then(data => {
                            $('.preloader').hide();
                            if (data.status === 'error') {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message,
                                    icon: 'error',
                                });
                            } else {
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message,
                                    icon: 'success',
                                }).then(() => {
                                    location.reload(); // Reload after confirmation
                                });
                            }
                        })
                        .catch(error => {
                            $('.preloader').hide();
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Something went wrong. Please try again.',
                                icon: 'error',
                            });
                        });
                }
            });
        });

        $(document).on('change', '.costCenter', function() {
            var selectedValue = $(this).val(); // Get the selected cost center value
            $('.costCenter').val(selectedValue); // Set the same value for all dropdowns
        });
        $('#locations').on('change', function() {
            populateCostCenterDropdowns();
        });


        $(document).on('input', '.ledgerselect', function() {
            const currentRow = $(this).closest('tr');
            let groupDropdown = currentRow.find('.ledgerGroup'); // group dropdown select
            groupDropdown.empty();

            const inputValue = $(this).val();
            if (inputValue.trim() === '') {
                currentRow.find('.ledger_id').val(''); // hidden input for selected ledger ID
            }
        });

        function initializeLedgerAutocomplete(context) {
            context.find(".ledgerselect").each(function() {
                const $input = $(this);

                // Avoid reinitializing if already done
                if ($input.hasClass('ui-autocomplete-input')) {
                    $input.autocomplete("destroy");
                }

                $input.autocomplete({
                    source: function(request, response) {
                        let preLedgers = [];
                        $('.ledgerselect').each(function() {
                            if ($(this).val() !== "") {
                                preLedgers.push($(this).val());
                            }
                        });

                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            url: '{{ route('ledgers.search') }}',
                            type: "POST",
                            dataType: "json",
                            data: {
                                keyword: request.term,
                                series: $('#book_type_id').val(),
                                ids: preLedgers
                            },
                            success: function(data) {
                                response(data);
                            },
                            error: function() {
                                response([]);
                            }
                        });
                    },
                    minLength: 0,
                    select: function(event, ui) {
                        $input.val(ui.item.label);
                        const currentRow = $input.closest('tr');
                        const ledgerId = ui.item.value;
                        const groupDropdown = currentRow.find('.ledgerGroup');
                        const ledgerIdInput = currentRow.find('.ledgers');

                        let preGroups = [];
                        $('.ledgerGroup').each(function() {
                            let ledgerGroup = $(this).val();
                            let ledger_id = $(this).data('ledger');
                            if (ledgerGroup !== "") {
                                preGroups.push({
                                    ledger_id,
                                    ledgerGroup
                                });
                            }
                        });

                        if (ledgerId) {
                            $.ajax({
                                url: '{{ route('voucher.getLedgerGroups') }}',
                                method: 'GET',
                                data: {
                                    ids: preGroups,
                                    ledger_id: ledgerId
                                },
                                success: function(response) {

                                    groupDropdown.empty();
                                    response.forEach(item => {
                                        groupDropdown.append(
                                            `<option value="${item.id}">${item.name}</option>`
                                        );
                                    });
                                    groupDropdown.removeAttr('style');
                                    groupDropdown.data('ledger', ledgerId);
                                    ledgerIdInput.val(ledgerId);

                                    handleRowClick(currentRow);
                                },
                                error: function(xhr) {
                                    let errorMessage = xhr.responseJSON?.error ||
                                        'Error fetching group items.';
                                    showToast("error", errorMessage);
                                }
                            });
                        }

                        return false;
                    },
                    change: function(event, ui) {
                        if (!ui.item) {
                            $input.val('');
                            const currentRow = $input.closest('tr');
                            currentRow.find('.ledger_id').val('');
                            currentRow.find('.ledgerGroup').empty();
                        }
                    },
                    focus: function() {
                        return false;
                    }
                }).focus(function() {
                    if (this.value === "") {
                        $(this).autocomplete("search");
                    }
                });
            });
        }
        $(function() {
            initializeLedgerAutocomplete($("#item-details-body"));
        });
        function submitAmend()
    {
        $('#submitButton').click();
    }
    </script>
@endsection
