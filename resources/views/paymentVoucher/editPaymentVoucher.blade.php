@extends('layouts.app')
@php use App\Helpers\ConstantHelper; @endphp
@section('styles')
<style>
    .settleInput {
        text-align: right;
    }
</style>
@endsection

@section('content')
<script src="{{asset('assets/js/fileshandler.js')}}"></script>
<script>
        const locationCostCentersMap = @json($cost_centers);
</script>
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

        <form id="voucherForm" action="{{ route($editUrl,$data->id) }}" method="POST" enctype="multipart/form-data" onsubmit="return check_amount()">
            @csrf
            @method('PUT')

                <input type="hidden" name="status" id="status" value="{{ $data->document_status }}">
                <input type="hidden" name="totalAmount" id="totalAmount" value="{{ $data->amount }}">

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

                <input type="hidden" name="document_type" id="document_type" value="{{ $data->document_type }}">
                @include('fixed-asset.partials.amendement-submit-modal')

            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Edit {{ Str::ucfirst($data->document_type) }} Voucher</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                        <li class="breadcrumb-item"><a href="{{ $indexUrl }}" >{{ Str::ucfirst($data->document_type) }} Vouchers</a></li>
                                        <li class="breadcrumb-item active">Edit New</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <a href="{{ $indexUrl }}" class="btn btn-secondary btn-sm"><i
                                        data-feather="arrow-left-circle"></i> Back</a>
                                @if(isset($fyear) && $fyear['authorized'])
                                    @if ($buttons['draft'] || (request('amendment')==1 && $buttons['amend']))
                                        <a type="button" onclick = "submitForm('draft');"
                                            class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" id="draft"
                                            name="action" value="draft"><i data-feather='save'></i> Save as Draft</a>
                                    @endif
                                    @if($buttons['cancel'])
                                    <a id = "cancelButton" type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0"><i data-feather='x-circle'></i> Cancel</a>
                                    @endif

                                    @if ($buttons['submit'] || (request('amendment')==1 && $buttons['amend']))
                                        <a type="button" onclick = "submitForm('submitted');"
                                            class="btn btn-primary btn-sm" id="submitted" name="action"
                                            value="submitted"><i data-feather="check-circle"></i> Submit</a>
                                    @endif
                                    {{-- @if ($buttons['approve'])
                                        <button type="button" id="reject-button" data-bs-toggle="modal"
                                            data-bs-target="#approveModal" onclick = "setReject();"
                                            class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i data-feather="x-circle"></i>  Reject</button>
                                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#approveModal" onclick = "setApproval();"><i
                                                data-feather="check-circle"></i> Approve</button>
                                    @endif
                                    @if ($buttons['amend'])
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm"
                                            class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i>
                                            Amendment</button>
                                    @endif
                                    @if($buttons['revoke'])
                                    <a id = "revokeButton" type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='rotate-ccw'></i> Revoke</a>
                                    @endif
                                     @if ($buttons['post'])
                                        <button onclick = "onPostVoucherOpen();" type = "button"
                                            class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i data-feather="check-circle"></i>
                                             Post</button>
                                    @endif --}}
                                @endif
                                {{-- @if ($buttons['voucher'])
                                        <button type="button" onclick="onPostVoucherOpen('posted');"
                                            class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i data-feather="file-text"></i>
                                             Voucher</button>
                                    @endif --}}


                                <input id="submitButton" type="submit" value="Submit" class="hidden" />
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
                                                <div
                                                    class="newheader d-flex justify-content-between border-bottom mb-2 pb-25">
                                                    <div>
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                    <div class="header-right">
                                                        @php
                                                            use App\Helpers\Helper;
                                                            $mainBadgeClass = match ($data->document_status) {
                                                                'approved' => 'success',
                                                                'approval_not_required' => 'success',
                                                                'draft' => 'warning',
                                                                'submitted' => 'info',
                                                                'partially_approved' => 'warning',
                                                                default => 'danger',
                                                            };
                                                        @endphp
                                                          <span class="badge rounded-pill badge-light-secondary forminnerstatus">
                                                            Status : <span class="{{App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? ''}}">
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



                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select" id="book_id" name="book_id"
                                                            required onchange="get_voucher_details()" disabled>
                                                            <option disabled selected value="">Select</option>
                                                            @foreach ($books as $book)
                                                                <option value="{{ $book->id }}"
                                                                    @if ($data->book_id == $book->id) selected @endif>
                                                                    {{ $book->book_code }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" id="voucher_no"
                                                            name="voucher_no" required value="{{ $data->voucher_no }}"
                                                            readonly />
                                                        @error('voucher_no')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control" readonly
                                                            name="date" id="date" required
                                                            value="{{ $data->date }}" min="{{ $fyear['start_date'] }}"
                                                            max="{{ $fyear['end_date'] }}" />
                                                    </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Payment Type <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Bank" value="Bank"
                                                                    name="payment_type" class="form-check-input"
                                                                    @if ($data->payment_type == 'Bank') checked @endif>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Bank">Bank</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Cash" value="Cash"
                                                                    name="payment_type" class="form-check-input"
                                                                    @if ($data->payment_type == 'Cash') checked @endif>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Cash">Cash</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Payment Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control" name="payment_date"
                                                            id="payment_date" required value="{{ $data->payment_date }}"
                                                            min="{{ $fyear['start_date'] }}"
                                                        max="{{ $fyear['end_date'] }}"/>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1 bankfield"
                                                    @if ($data->payment_type == 'Cash') style="display: none" @endif>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Bank Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-3 mb-1 mb-sm-0">
                                                        <select class="form-control select2 bankInput" name="bank_id"
                                                            id="bank_id" onchange="getAccounts()"
                                                            @if ($data->payment_type == 'Bank') required @endif>
                                                            <option selected disabled value="">Select Bank</option>
                                                            @foreach ($banks as $bank)
                                                                <option value="{{ $bank->id }}"
                                                                    @if ($data->bank_id == $bank->id) selected @endif>
                                                                    {{ $bank->bank_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <label class="form-label">A/c No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <select class="form-control select2 bankInput" name="account_id"
                                                            id="account_id"
                                                            @if ($data->payment_type == 'Bank') required @endif>
                                                            <option selected disabled value="">Select Bank Account
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1 bankfield">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Payment Mode <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-3 mb-1 mb-sm-0">
                                                        <select class="form-control select2 bankInput" name="payment_mode"
                                                            @if ($data->payment_type == 'Bank') required @endif>
                                                            <option value="">Select</option>
                                                            <option @if ('IMPS/RTGS' == $data->payment_mode) selected @endif>
                                                                IMPS/RTGS</option>
                                                            <option @if ('NEFT' == $data->payment_mode) selected @endif>NEFT
                                                            </option>
                                                            <option @if ('By Cheque' == $data->payment_mode) selected @endif>By
                                                                Cheque</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1 cashfield"
                                                    @if ($data->payment_type == 'Bank') style="display: none" @endif>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Ledger <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-control select2" name="ledger_id"
                                                            id="ledger_id"
                                                            @if ($data->payment_type == 'Cash') required @endif>
                                                            <option disabled selected value="">Select Ledger</option>
                                                            @foreach ($ledgers as $ledger)
                                                                <option value="{{ $ledger->id }}"
                                                                    @if ($ledger->id == $data->ledger_id) selected @endif>
                                                                    {{ $ledger->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                     </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Currency <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5 mb-1 mb-sm-0">
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
                                                    <div class="col-md-3">
                                                        <label class="form-label mt-50">Exchange Rates</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control"
                                                         id="orgExchangeRate" oninput="resetCalculations()" value="{{ round($data->org_currency_exg_rate, 2) }}"  readonly />


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
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="locations" class="form-select" name="location">
                                                            <option value="" selected>Select Location</option>
                                                            @foreach ($locations as $location)
                                                            <option value="{{ $location->id }}"
                                                                {{ (isset($data->location) && $data->location == $location->id) ? 'selected' : '' }}>
                                                                {{ $location->store_name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                </div>
                                                {{-- @if(count($cost_centers) > 0)
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cost Center <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5 mb-1 mb-sm-0">
                                                    <select class="form-control select2" name="cost_center_id"
                                                            id="cost_center_id">
                                                            @foreach ($cost_centers as $cost)
                                                                <option value="{{ $cost['id'] }}" @if($cost['id']===$data->cost_center_id) selected @endif>
                                                                    {{ $cost['name'] }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                @endif --}}
                                                    @php
                                                    // Find the selected location object
                                                    $selectedLocation = $locations->firstWhere('id', $data->location);
                                                    // $locationCostCenters = $cost_centers ?? [];
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

                                                    // Check if the selected cost center exists in this location
                                                    $showCostCenter = !empty($locationCostCenters);
                                                @endphp

                                                <div class="row align-items-center mb-1" id="costCenterRow" style="{{ $showCostCenter ? '' : 'display:none;' }}">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cost Center <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5 mb-1 mb-sm-0">
                                                        <select class="costCenter form-control select2" name="cost_center_id" id="cost_center_id">
                                                        @if($data->cost_center_id=="")
                                                        <option value="">Select</option>
                                                        @endif
                                                            @foreach ($locationCostCenters as $value)
                                                            <option value="{{ $value['id'] }}"
                                                                @if($value['id'] == $data->cost_center_id) selected @endif>
                                                                {{ $value['name'] }}
                                                            </option>
                                                        @endforeach
                                                        </select>
                                                    </div>
                                                </div>


                                            </div>


                                        </div>
                                        <div class="row" @if($data->approvalStatus=="cancel") style="display:none;" @endif>
                                            <div class="col-md-12">
                                                <div class="border-top mt-2 pt-2 mb-1">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="newheader ">
                                                                <h4 class="card-title text-theme">Payment Detail</h4>
                                                                <p class="card-text">Fill the details</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 text-sm-end">
                                                            @if ($fyear['authorized'])

                                                            <a href="#"
                                                                class="btn btn-sm btn-outline-primary add-row">
                                                                <i data-feather="plus"></i> Add New</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="table-responsive pomrnheadtffotsticky">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                        <thead>
                                                            <tr>
                                                                <th width="50px">#</th>
                                                                <th width="300px">Ledger Code</th>
                                                                <th width="300px">Ledger Name</th>
                                                                <th width="300px">Ledger Group</th>
                                                                <th width="300px">Organization</th>
                                                                <th width="300px">Reference</th>
                                                                <th width="200px" class="text-end">Amount (<span
                                                                        id="selectedCurrencyName">{{ $data->currencyCode }}</span>)
                                                                </th>
                                                                <th width="200px" class="text-end">Amount (<span
                                                                        id="orgCurrencyName"></span>)</th>
                                                                <th width="200px" class="ref-no-header">Pay Ref. No</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            @foreach ($data->details as $index => $item)
                                                                @php
                                                                    $no = $index + 1;
                                                                @endphp
                                                                <tr class="approvlevelflow" id="{{ $no }}">
                                                                    <td>{{ $no }}</td>
                                                                    <td class="poprod-decpt">
                                                                        <input type="text" placeholder="Select"
                                                                            class="form-control mw-100 ledgerselect mb-25 partyCode{{$no}}" required data-id="{{ $no }}"
                                                                            required data-id="{{ $no }}"
                                                                            value="{{ $item?->ledger?->code ?? $item?->party?->ledger?->code }}" />
                                                                        <input type="hidden" name="party_id[]"
                                                                            type="hidden"
                                                                            id="party_id{{ $no }}"
                                                                            class="ledgers"
                                                                            value="{{ $item->ledger_id??$item->party_id }}" />
                                                                            <input type="hidden" name="party_vouchers[]" type="hidden" id="party_vouchers{{$no}}" class="party_vouchers" value="{{json_encode($item->invoice)}}"/>

                                                                    </td>
                                                                    <td class="poprod-decpt"><input type="text"
                                                                            disabled placeholder="Select"
                                                                            class="form-control mw-100 mb-25 partyName"
                                                                            id="party_name{{ $no }}"
                                                                            value="{{ $item?->ledger?->name ?? $item?->party?->ledger?->name }}" />
                                                                    </td>
                                                                    <td>
                                                                        <select required id="groupSelect{{$no}}"
                                                                            name="parent_ledger_id[]"
                                                                            class="ledgerGroup form-select mw-100">
                                                                            <option value="{{ $item?->ledger_group_id ?? $item?->party?->ledger_group?->id }}">{{ $item?->ledger_group?->name ?? $item?->party?->ledger_group?->name }}</option>
                                                                        </select>
                                                                    </td>
                                                                    
                                                                  
                                                                   
                                                                    <td>
                                                                        <input type="text" disabled
                                                                            placeholder="Select"
                                                                            class="form-control mw-100 mb-25 organization"
                                                                            id="organization{{$no}}"
                                                                            value="{{ $item?->organization?->name ?? $item?->party?->organization?->name ?? $item?->ledger?->organization?->name }}" />
                                                                    </td>
                                                                    <td>
                                                                        <div class="position-relative d-flex align-items-center">
                                                                            <select class="form-select mw-100 invoiceDrop drop{{ $no }}" data-id="{{ $no }}" name="reference[]">
                                                                                {{-- <option value="">Selecvoucht</option> --}}
                                                                                <option @if($item->reference=="Invoice") selected @endif>Invoice</option>
                                                                                <option @if($item->reference=="Advance") selected @endif>Advance</option>
                                                                                <option @if($item->reference=="On Account") selected @endif>On Account</option>
                                                                            </select>
                                                                            <div class="ms-50 flex-shrink-0">
                                                                                <button type="button" class="btn p-25 btn-sm btn-outline-secondary invoice{{ $no }}" style="font-size: 10px" onclick="openInvoice({{ $no }},{{$data->id}},{{$item->id}})" @if($item->reference!="Invoice" || !$fyear['authorized']) disabled @endif>Invoice</button>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td><input type="number"
                                                                            @if($item->reference=="Invoice") readonly @endif
                                                                            class="form-control mw-100 text-end amount"
                                                                            name="amount[]"
                                                                            id="excAmount{{ $no }}"
                                                                            value="{{ $item->currentAmount }}" required />
                                                                    </td>
                                                                    <td><input type="text" readonly
                                                                            class="form-control mw-100 text-end amount_exc excAmount{{ $no }}"
                                                                            name="amount_exc[]"
                                                                            value="{{ $item->orgAmount }}" required />
                                                                    </td>
                                                                     <td>
                                                                            <input type="number" class="form-control mw-100 bankInput reference_no" 
                                                                                name="reference_no[]" data-row="{{ $no }}" id="reference_no{{ $no }}" 
                                                                                @if($item->reference_no) value="{{ $item->reference_no }}" @endif />
                                                                            <span class="text-danger bankInput" id="reference_error{{ $no }}" style="font-size:12px"></span>
                                                                    </td>
                                                                    <td>
                                                                         @if ($fyear['authorized'])
                                                                         <a href="#" class="text-danger deleteRow"><i data-feather="trash-2"></i></a>
                                                                        @endif
                                                                        </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="totalsubheadpodetail">
                                                                <td colspan="{{ $data->payment_type == 'Bank' ? '6' : '5' }}"  class="text-end">Total</td>
                                                                <td class="text-end currentCurrencySum">0</td>
                                                                <td class="text-end orgCurrencySum">0</td>
                                                                <td></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>

                                                <div class="row mt-2">
                                                    <div class="col-md-4 mb-1">
                                                        <label class="form-label">Document</label>
                                                        <input type="file" class="form-control" name="document" />
                                                        @if ($data->document)
                                                            <a href="{{ asset('voucherPaymentDocuments') . '/' . $data->document }}"
                                                                target="_blank">View Uploaded Doc</a>
                                                        @endif
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
            <div class="modal fade text-start show" id="postvoucher" tabindex="-1" aria-labelledby="postVoucherModal"
                aria-modal="true" role="dialog">
                <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="postVoucherModal">
                                    Voucher
                                    Details</h4>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">

                                <div class="col-md-3">
                                    <div class="mb-1">
                                        <label class="form-label">Series <span class="text-danger">*</span></label>
                                        <input id = "voucher_book_code" class="form-control" disabled="">
                                        <input type="hidden" class="form-control" name="data" id="ldata">
                                        <input type="hidden" class="form-control" name="doc" id="doc">
                                        <input type="hidden" class="form-control" name="loan_data" id="loan_data">
                                        <input type="hidden" class="form-control" name="remakrs" id="remakrs">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-1">
                                        <label class="form-label">Voucher No <span class="text-danger">*</span></label>
                                        <input id = "voucher_doc_no" class="form-control" disabled="" value="">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-1">
                                        <label class="form-label">Voucher Date <span class="text-danger">*</span></label>
                                        <input id = "voucher_date" class="form-control" disabled="" value="">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-1">
                                        <label class="form-label">Currency <span class="text-danger">*</span></label>
                                        <input id = "voucher_currency" class="form-control" disabled=""
                                            value="">
                                    </div>
                                </div>

                                <div class="col-md-12">


                                    <div class="table-responsive">
                                        <table
                                            class="mt-1 table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Group</th>
                                                    <th>Leadger Code</th>
                                                    <th>Leadger Name</th>
                                                    <th class="text-end">Debit</th>
                                                    <th class="text-end">Credit</th>
                                                </tr>
                                            </thead>
                                            <tbody id = "posting-table">


                                            </tbody>


                                        </table>
                                    </div>
                                </div>


                            </div>
                        </div>
                        <div class="modal-footer text-end">
                            <button onclick = "postVoucher(this);" id = "posting_button" type = "button"
                                class="btn btn-primary btn-sm waves-effect waves-float waves-light"><i data-feather="check-circle"></i> Submit</button>
                        </div>
                    </div>
                </div>
            </div>


            <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form class="ajax-input-form" method="POST" action="{{ route('approvePaymentVoucher') }}"
                            data-redirect="{{ $indexUrl }}" enctype='multipart/form-data'>
                            @csrf
                            <input type="hidden" name="action_type" id="action_type">
                            <input type="hidden" name="id" value="{{ $data->id }}">
                            <div class="modal-header">
                                <div>
                                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">
                                        Approve Voucher</h4>
                                    <p class="mb-0 fw-bold voucehrinvocetxt mt-0">
                                        {{ Carbon\Carbon::now()->format('d-m-Y') }}</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body pb-2">
                                <div class="row mt-1">
                                    <div class="col-md-12">
                                        <div class="mb-1">
                                            <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                            <textarea name="remarks" class="form-control"></textarea>
                                        </div>
                                        <div class="mb-1">
                                            <label class="form-label">Upload Document</label>
                                            <input type="file" multiple class="form-control" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="submit-button"> Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- END: Content-->
    <div class="modal fade text-start" id="invoice" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select Pending Invoices</h4>
                        <p class="mb-0">Settled Amount from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                     <div class="row">
                        <div class="col-md-3 header_invoices">
                            <div class="mb-1">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="text" id="fp-range" name="date_range" value="{{ Request::get('date_range') }}" class="form-control flatpickr-range bg-white"
                                placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                            </div>
                        </div>

                         <div class="col-md-3 header_invoices">
                            <div class="mb-1">
                                <label class="form-label">Voucher Type <span class="text-danger">*</span></label>
                                <select class="form-select select2" id="book_code">
                                    <option value="">Select Type</option>
                                    @foreach ($books_t->unique('alias') as $book)
                                        <option class="{{ $book->alias }}">{{ strtoupper($book->name) }}</option>
                                    @endforeach
                                </select>
                                </div>
                        </div>

                         <div class="col-md-3 header_invoices">
                            <div class="mb-1">
                                <label class="form-label">Document No. <span class="text-danger">*</span></label>
                                <input type="text" id="document_no" class="form-control" />
                            </div>
                        </div>

                         <div class="col-md-3  mb-1 header_invoices">
                              <label class="form-label">&nbsp;</label><br/>
                             <button type="button" class="btn btn-warning btn-sm" onclick="getLedgers()"><i data-feather="search"></i> Search</button>
                         </div>
                         <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                    <thead>
                                         <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Series</th>
                                            <th>Document No.</th>
                                            <th>Location</th>
                                            <th>Cost Center</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-end">Balance</th>
                                            <th class="text-end" width="150px">Settle Amt</th>
                                             <th class="text-center">
                                                 <div class="form-check form-check-inline me-0">
                                                    <input class="form-check-input" type="checkbox" name="podetail" id="inlineCheckbox1">
                                                </div>
                                             </th>
                                          </tr>
                                        </thead>
                                        <tbody id="vouchersBody">
                                       </tbody>
                                       <tfoot>
                                            <tr>
                                                <td colspan="8" class="text-end">Total</td>
                                                <td class="fw-bolder text-dark text-end settleTotal">0</td>
                                                <td></td>
                                            </tr>
                                       </tfoot>
                                </table>
                            </div>
                        </div>
                     </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm header_invoices" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
                    <button id="process" class="btn btn-primary btn-sm header_invoices" type="button" onclick="setAmount()"><i data-feather="check-circle"></i> Process</button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="currentParty">
    <input type="hidden" id="currentRow">
    <input type="hidden" id="LedgerId">

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
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // $('#voucherForm').on('submit', function () {
        //     $('.preloader').show();
        // });
        function onPostVoucherOpen(type = "not_posted") {
            resetPostVoucher();

            const apiURL = "{{ route('paymentVouchers.getPostingDetails') }}";
            const remarks = $("#remarks").val();
            $.ajax({
                url: apiURL + "?book_id=" + "{{ $data->book_id }}" + "&document_id=" + "{{ $data->id }}" +
                    "&remarks=" + remarks + "&type={{ $data->document_type }}",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    if (!data.data.status) {
                        Swal.fire({
                            title: 'Error!',
                            text: data.data.message,
                            icon: 'error',
                        });
                        return;
                    }
                    const voucherEntries = data.data.data;
                    var voucherEntriesHTML = ``;
                    Object.keys(voucherEntries.ledgers).forEach((voucher) => {
                        voucherEntries.ledgers[voucher].forEach((voucherDetail, index) => {
                            voucherEntriesHTML += `
                    <tr>
                    <td>${voucher}</td>
                    <td class="fw-bolder text-dark">${voucherDetail.ledger_group_code ? voucherDetail.ledger_group_code : ''}</td>
                    <td>${voucherDetail.ledger_code ? voucherDetail.ledger_code : ''}</td>
                    <td>${voucherDetail.ledger_name ? voucherDetail.ledger_name : ''}</td>
                    <td class="text-end">${voucherDetail.debit_amount > 0 ? parseFloat(voucherDetail.debit_amount).toFixed(2) : ''}</td>
                    <td class="text-end">${voucherDetail.credit_amount > 0 ? parseFloat(voucherDetail.credit_amount).toFixed(2) : ''}</td>
					</tr>
                    `
                        });
                    });
                    voucherEntriesHTML += `
            <tr>
                <td colspan="4" class="fw-bolder text-dark text-end">Total</td>
                <td class="fw-bolder text-dark text-end">${voucherEntries.total_debit.toFixed(2)}</td>
                <td class="fw-bolder text-dark text-end">${voucherEntries.total_credit.toFixed(2)}</td>
			</tr>
            `;
                    document.getElementById('posting-table').innerHTML = voucherEntriesHTML;
                    document.getElementById('voucher_doc_no').value = voucherEntries.document_number;
                    document.getElementById('voucher_date').value = moment(voucherEntries.document_date).format(
                        'D/M/Y');
                    document.getElementById('voucher_book_code').value = voucherEntries.book_code;
                    document.getElementById('voucher_currency').value = voucherEntries.currency_code;
                    if (type === "posted") {
                        document.getElementById('posting_button').style.display = 'none';
                    } else {
                        document.getElementById('posting_button').style.removeProperty('display');
                    }
                    $('#postvoucher').modal('show');
                }
            });

        }

        function resetPostVoucher() {
            document.getElementById('voucher_doc_no').value = '';
            document.getElementById('voucher_date').value = '';
            document.getElementById('voucher_book_code').value = '';
            document.getElementById('voucher_currency').value = '';
            document.getElementById('posting-table').innerHTML = '';
            document.getElementById('posting_button').style.display = 'none';
        }

        function postVoucher(element) {
            const bookId = "{{ $data->book_id }}";
            const type = "{{ $data->document_type }}"
            const documentId = "{{ $data->id }}";
            const postingApiUrl = "{{ route('paymentVouchers.post') }}";
            const remarks = $("#remarks").val();
            console.log(bookId);
            console.log(documentId);
            if (bookId && documentId) {
                $.ajax({
                    url: postingApiUrl,
                    type: "POST",
                    dataType: "json",
                    contentType: "application/json", // Specifies the request payload type
                    data: JSON.stringify({
                        // Your JSON request data here
                        book_id: bookId,
                        document_id: documentId,
                        remarks: remarks,
                        type: type,

                    }),
                    success: function(data) {
                        const response = data.data;
                        if (response.status) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                            });
                            if ("{{$data->document_type}}" === 'Receipt' || "{{$data->document_type}}" === 'receipts' )
                            location.href = '/receipts';
                            else
                                location.href = '/payments';


                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                            });
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Some internal error occured',
                            icon: 'error',
                        });
                    }
                });

            }
        }
        var banks = {!! json_encode($banks) !!};
        var currencies = {!! json_encode($currencies) !!};
        var orgCurrency = {{ $orgCurrency }};
        var count = 2;
        var orgCurrencyName = '';


        function setAmount() {
            let isValid = true;

$('.settleInput').each(function () {
        let input = $(this);
        let row = input.closest('.voucherRows');
        let balanceText = row.find('.balanceInput').text().replace(/,/g, '');
        let balance = parseFloat(balanceText);
        let settleAmount = parseFloat(input.val());

        // Remove existing error message
        input.next('.invalid-feedback').remove();

        if (settleAmount > balance) {
            input.addClass('is-invalid');
            input.after('<span class="invalid-feedback d-block" style="font-size:12px">Settle amount cannot be greater than balance.</span>');
            isValid = false;
        } else {
            input.removeClass('is-invalid');
        }
    });

    if (!isValid) {
        // Prevent modal close or further processing
        return false;
    }

            $('#excAmount' + $('#currentRow').val()).val($('.settleTotal').text().replace(/,/g, ''));
            $('#excAmount' + $('#currentRow').val()).trigger('keyup');
            $('#invoice').modal('toggle');

            var selectedVouchers = [];
            const preSelected = $('.vouchers:checked').map(function() {
                selectedVouchers.push({
                    "party_id": $('#LedgerId').val(),
                    "voucher_id": this.value,
                    "amount": $('.settleAmount' + this.value).val()
                });
                return this.value;
            }).get();
            $('#party_vouchers' + $('#currentRow').val()).val(JSON.stringify(selectedVouchers));
            resetCalculations();
            $('#invoice').modal('hide');
        }



        $(document).on('input', '.settleInput', function(e) {
            let max = parseInt(e.target.max);
            let value = parseInt(e.target.value);

            if (value > 0) {
                $('.voucherCheck' + $(this).attr('data-id')).attr('checked', true);
            } else {
                $('.voucherCheck' + $(this).attr('data-id')).attr('checked', false);
            }

            if (value > max) {
                e.target.value = max;
            }
        });

        function openInvoice(id,paymentId=null,details=null,ref=null) {
            console.log(id);
             $('#excAmount' + id).attr('readonly', true);
            if ($('#party_id'+id).val()!="") {
                $('.drop' + id).val('Invoice');
                const comingParty = $('#party_id' + id).val();
                if (comingParty != $('#currentParty').val()) {
                    $('#vouchersBody').empty();
                    $("#inlineCheckbox1").attr('checked', false);
                    calculateSettle();
                    $('#fp-range').val('');
                }
                $('#currentParty').val(comingParty);
                $('#currentRow').val(id);
                getLedgers(paymentId,details,ref);
                $('#invoice').modal('show');

            } else {
                $('.drop' + id).val('');
                showToast('error','Select ledger to select invoice!!');
            }
        }

        function getLedgers(paymentId=null,details=null) {
            $('.vouchers:not(:checked)').map(function() {
                $('#' + this.value).remove();
            }).get();
            updateVoucherNumbers();

            const preSelected = $('.vouchers:checked').map(function() {
                return this.value;
            }).get();

            var preData = [];
            const partyData = $('#party_vouchers' + $('#currentRow').val()).val();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('getLedgerVouchers') }}',
                type: 'POST',
                dataType: 'json',
                data: {
                    date: $('#fp-range').val(),
                    '_token': '{!! csrf_token() !!}',
                    partyCode: $('.partyCode'+$('#currentRow').val()).val(),
                    book_code: $('#book_code').val(),
                    partyID: $('#party_id' + $('#currentRow').val()).val(),
                    ledgerGroup: $('#groupSelect' + $('#currentRow').val()).val(),
                    document_no: $('#document_no').val(),
                    type: $('#document_type').val(),
                    payment_voucher_id:'{{$data->id}}',
                    page:'edit',
                    details_id:details,
                },
                success: function(response) {
                    if (response.data.length > 0) {
                        var html = '';
                        $.each(response.data, function(index, val) {
                            if (!preSelected.includes(val['id'].toString())) {
                                $.each(val.items || [], function (i, item) {

                                var amount = 0.00;
                                var checked = "";
                                var dataAmount = parseFloat(val['balance']).toFixed(2);
                                if (partyData != "" && partyData != undefined) {
                                    $.each(JSON.parse(partyData), function(indexP, valP) {
                                        if (valP['voucher_id'].toString() == val['id']) {
                                            amount = (parseFloat(valP['amount'])).toFixed(2);
                                            checked = "checked";
                                            dataAmount = (parseFloat(valP['amount'])).toFixed(
                                            2);
                                        }
                                    });
                                }

                                if (parseFloat(val['balance']).toFixed(2) <=0 && checked == "") {
                                    console.log('hii' + val['id']);
                                } else {
                                    if(parseFloat(val['balance']).toFixed(2).toLocaleString('en-IN')=="0.00" && val['settle'] && details!=null){
                                    html += `<tr id="${val['id']}" class="voucherRows">
                                            <td>${index+1}</td>
                                            <td>${val['date']}</td>
                                            <td class="fw-bolder text-dark">${val['series']['book_code'].toUpperCase()}</td>
                                            <td>${val['voucher_no']}</td>
                                            <td class="">${val['erp_location']?.store_name ?? '-'}</td>
                                            <td>${item.cost_center?.name ?? '-'}</td>
                                            <td class="text-end">${formatIndianNumber(val['amount'])}</td>
                                            <td class="text-end balanceInput">${formatIndianNumber(val['balance'])}</td>
                                            <td class="text-end">
                                                <input type="number" class="form-control mw-100 settleInput settleAmount${val['id']}" data-id="${val['id']}" value="${val['settle']}"/>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-check-inline me-0">
                                                    <input class="form-check-input vouchers voucherCheck${val['id']}" data-id="${val['id']}" type="checkbox" ${checked} checked name="vouchers" value="${val['id']}" data-amount="${dataAmount}">
                                                </div>
                                            </td>
                                        </tr>`;
                                    }else{
                                        html += `<tr id="${val['id']}" class="voucherRows">
                                            <td>${index+1}</td>
                                            <td>${val['date']}</td>
                                            <td class="fw-bolder text-dark">${val['series']['book_code']}</td>
                                            <td>${val['voucher_no']}</td>
                                            <td class="">${val['erp_location']?.store_name ?? '-'}</td>
                                            <td>${item.cost_center?.name ?? '-'}</td>
                                            <td class="text-end">${formatIndianNumber(val['amount'])}</td>
                                            <td class="text-end balanceInput">${formatIndianNumber(val['balance'])}</td>
                                            <td class="text-end">
                                                <input type="number" class="form-control mw-100 settleInput settleAmount${val['id']}" data-id="${val['id']}" value="${amount}"/>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-check-inline me-0">
                                                    <input class="form-check-input vouchers voucherCheck${val['id']}" data-id="${val['id']}" type="checkbox" ${checked} name="vouchers" value="${val['id']}" data-amount="${dataAmount}">
                                                </div>
                                            </td>
                                        </tr>`;


                                    }
                                    
                                }
                            });
                            }
                        });
                        $('#LedgerId').val(response.ledgerId);
                        $('#vouchersBody').append(html);
                        calculateSettle();
                        updateVoucherNumbers();
                    }
                    calculateSettle();
                }
            });
        }
        function adjustInvoice(rows) {
            let enteredAmount = parseFloat($(rows).val()) || 0;
        let entersettle = $(rows);

        let row = $(rows).closest("tr");
        let balance = parseFloat(row.find(".balanceInput").text().replace(/,/g, "")) || 0;

        if (enteredAmount > balance) {
            let excessAmount = enteredAmount - balance;
            if (excessAmount > 0) {
                $(".voucherRows").each(function () {
                    let nextBalance = parseFloat($(this).find(".balanceInput").text().replace(/,/g, "")) || 0;
                    let nextSettleInput = $(this).find(".settleInput");
                    let checkBox = $(this).find(".vouchers");
                    let settle = parseFloat(nextSettleInput.val()) || 0;
                    let deduct = nextBalance-settle;
                    let nextSettle = settle + deduct;
                    if(excessAmount>=deduct && nextBalance > settle){
                        excessAmount-=deduct;
                        nextSettleInput.val(deduct+settle);
                        if(nextSettleInput.val()!=0)
                        checkBox.prop('checked', true);
                        console.log(enteredAmount-deduct);

                        entersettle.val(enteredAmount-deduct);
                    }
                });
                $(".voucherRows").each(function () {
                    let nextBalance = parseFloat($(this).find(".balanceInput").text().replace(/,/g, "")) || 0;
                    let nextSettleInput = $(this).find(".settleInput");
                    let checkBox = $(this).find(".vouchers");
                    let settle = parseFloat(nextSettleInput.val()) || 0;
                    let deduct = nextBalance-settle;
                    let nextSettle = settle + deduct;
                    if(excessAmount>=deduct && nextBalance > settle){
                        excessAmount-=deduct;
                        nextSettleInput.val(deduct+settle);
                        if(nextSettleInput.val()!=0)
                        checkBox.prop('checked', true);
                        console.log(enteredAmount-deduct);

                        entersettle.val(entersettle.val()-deduct);
                    }else if(excessAmount<deduct && nextBalance > settle){
                        nextSettleInput.val(excessAmount+settle);
                        if(nextSettleInput.val()!=0)
                        checkBox.prop('checked', true);
                        entersettle.val(entersettle.val()-excessAmount);
                        excessAmount=0;
                    }
                });
                $(".voucherRows").get().reverse().forEach(function () {
                    let checkBox = $(this).find(".vouchers");
                    let nextBalance = parseFloat($(this).find(".balanceInput").text().replace(/,/g, "")) || 0;
                    let nextSettleInput = $(this).find(".settleInput");
                    let settle = parseFloat(nextSettleInput.val()) || 0;
                    let deduct = nextBalance-settle;
                    let nextSettle = settle + deduct;
                    if(excessAmount>=deduct && nextBalance > settle){
                        excessAmount-=deduct;
                        nextSettleInput.val(deduct+settle);
                        if(nextSettleInput.val()!=0)
                        checkBox.prop('checked', true);
                        console.log(enteredAmount-deduct);

                        entersettle.val(entersettle.val()-deduct);
                    }else if(excessAmount<deduct && nextBalance > settle){
                        nextSettleInput.val(excessAmount+settle);
                        if(nextSettleInput.val()!=0)
                        checkBox.prop('checked', true);
                        entersettle.val(entersettle.val()-excessAmount);
                        excessAmount=0;
                    }
                });

            }
        }
        }




        function updateVoucherNumbers() {
            $('.voucherRows').each(function(index) {
                var level = index + 1;
                $(this).find('td:first-child').text(level);
            });
        }

        function calculateSettle() {
            let settleSum = 0;
            $('.vouchers:checked').map(function() {
                const value = parseFloat($('.settleAmount' + this.value).val()) || 0;
                settleSum += value;
            }).get();
            $('.settleTotal').text(formatIndianNumber(settleSum));
        }

        $(function() {
            $('#inlineCheckbox1').click(function() {
                $('.vouchers').prop('checked', this.checked);
                selectAllVouchers();
            });
            $(".revisionNumber").change(function() {
                window.location.href = "{{ route($editUrlString, $data->id) }}?revisionNumber=" +
                    $(this).val();
            });
        });

        $(document).on('click', '#amendmentSubmit', (e) => {
            let actionUrl = "{{ route('paymentVouchers.amendment', $data->id) }}";
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
function check_amount() {
            $('#draft').attr('disabled', true);
            $('#submitted').attr('disabled', true);
            $('.preloader').show();
            let rowCount = document.querySelectorAll('.mrntableselectexcel tr').length;
            for (let index = 1; index <= rowCount; index++) {
                if (parseFloat($('#excAmount' + index).val()) == 0) {
                    $('.preloader').hide();
                    showToast('error', 'Can not save ledger with amount 0');
                            $('#draft').attr('disabled', false);
            $('#submitted').attr('disabled', false);
                    return false;
                }
            }

            if (parseFloat(removeCommas($('.currentCurrencySum').text())) == 0) {
                $('.preloader').hide();
                showToast('error', 'Total amount should be greater than 0');
                        $('#draft').attr('disabled', false);
                $('#submitted').attr('disabled', false);
                return false;
            }
            //   if ($('#reference_no').hasClass('is-invalid') && $("#Bank").is(":checked")){
            //     $('.preloader').hide();
            //     showToast('error', 'Reference no. Already exist');
            //      $('#draft').attr('disabled', false);
            // $('#submitted').attr('disabled', false);
            //     return false;


            //   }
            if ($("#Bank").is(":checked")) {
                    let refError = false;
                    // First check for empty references
                    $('.reference_no').each(function() {
                        const refNo = $(this).val().trim();
                        const row = $(this).data('row');
                        
                    });
                    // Then check for duplicates
                    if (!validateReferenceNumbers()) {
                        refError = true;
                    }
                    
                    if (refError) {
                        $('.preloader').hide();
                        showToast('error', 'Please fix reference number errors');
                        $('#draft').attr('disabled', false);
                        $('#submitted').attr('disabled', false);
                        return false;
                    }
                }
                
                return true;
        }

        function selectAllVouchers() {
            $('.vouchers').each(function() {
                if (this.checked) {
                    $(".settleAmount" + this.value).val($(this).attr('data-amount'));
                } else {
                    $(".settleAmount" + this.value).val('0.00');
                }
            });
            calculateSettle();
        }

         // Now define this BELOW or inside DOM ready:
        function evaluateCostCenterVisibility() {
            const rows = $('.invoiceDrop');
            const rowCount = rows.length;
            let hasNonInvoiceSelected = false;
            let allInvoice = true;

            rows.each(function () {
                const value = $(this).val();
                if (value !== 'Invoice') {
                    hasNonInvoiceSelected = true;
                    allInvoice = false;
                    return false;
                }
            });

            const $costCenterRow = $('#costCenterRow');
            const $costCenterDropdown = $('#cost_center_id');

            if (rowCount === 1 && allInvoice) {
                $costCenterRow.hide();
                $costCenterDropdown.val('');
            } else if (hasNonInvoiceSelected) {
                const isHidden = $('#costCenterRow').is(':hidden');
                const isEmpty = $('#cost_center_id').val() == null;

                if (isHidden && isEmpty) {
                    let selectedLocationId = $('#locations').val();
                    renderCostCentersForLocation(selectedLocationId); // <- now this will be available
                }
            } else {
                $costCenterRow.hide();
                $costCenterDropdown.val('');
            }
        }
        $(document).on('change', '.invoiceDrop', function() {
            const rowId = $(this).attr('data-id');
            const selectedValue = $(this).val();
            
            if (selectedValue == "Invoice") {
                $('.invoice' + rowId).attr('disabled', false);
                $('#excAmount' + rowId).attr('readonly', true).val('0.00');
                $('.excAmount' + rowId).val('0.00'); // Set to 0.00 initially
                openInvoice(rowId);
            } else {
                $('.invoice' + rowId).attr('disabled', true);
                $('#excAmount' + rowId).attr('readonly', false).val('0.00');
                $('.excAmount' + rowId).val('0.00'); // Set to 0.00 initially
                $('#party_vouchers' + rowId).val('[]');
            }
            
            // Trigger amount calculation if amount is not 0
            if (parseFloat($('#excAmount' + rowId).val())) {
                $('#excAmount' + rowId).trigger('keyup');
            }
            
            calculateTotal();
        });

        $(document).on('click', '.vouchers', function() {
            if (this.checked) {
                $(".settleAmount" + this.value).val($(this).attr('data-amount'));
            } else {
                $(".settleAmount" + this.value).val('0.00');
            }
            calculateSettle();
        });

        $(document).on('keyup keydown', '.settleInput', function() {
            let value = parseInt($(this).val());
            if (value > 0) {
                $('.voucherCheck' + $(this).attr('data-id')).prop('checked', true);
            } else {
                $('.voucherCheck' + $(this).attr('data-id')).prop('checked', false);
            }

            //adjustInvoice(this);
            let input = $(this);
            let row = input.closest('.voucherRows');
            let balanceText = row.find('.balanceInput').text().replace(/,/g, '');
            let balance = parseFloat(balanceText);
            let settleAmount = parseFloat(input.val());

            // Remove existing error message span if it exists
            input.next('.invalid-feedback').remove();

            if (settleAmount > balance) {
                input.addClass('is-invalid');
                input.after('<span class="invalid-feedback d-block" style="font-size:12px">Settle amount cannot be greater than balance.</span>');
            } else {
                input.removeClass('is-invalid');
            }
            calculateSettle();
        });

        function setApproval() {
            document.getElementById('action_type').value = "approve";
        }

        function setReject() {
            document.getElementById('action_type').value = "reject";
        }
        function bind(){

                                                   $('.amount').on('click', function () {
                                                       if($(this).val()==="0" || $(this).val()==="0.00"){
                                                           $(this).val('');
                                                       }
                                                   });

                                                   $('.amount').on('focusout', function () {
                                                       if($(this).val()===""){
                                                           $(this).val('0.00');
                                                       }
                                                   });

                                                           }


        $(document).ready(function() {
            bind();
            // evaluateCostCenterVisibility();
            if ($("#Bank").is(":checked")) {
                $(".bankfield").show();
                $(".cashfield").hide();
                $('.bankInput').prop('required', true);
                $('.ref-no-header').show(); // Show the header
                $('.reference_no').prop('required', true).closest('td').show();
                $('#ledger_id').prop('required', false);
            } else {
                $(".cashfield").show();
                $(".bankfield").hide();
                $('.bankInput').prop('required', false);
                $('.reference_no').prop('required', false).closest('td').hide();
                $('.ref-no-header').hide(); // Hide the header
                $('#ledger_id').prop('required', true);
            }
            //$('#reference_no').trigger('input');
            @if (!($buttons['draft'] || ($buttons['amend'] && request('amendment')==1) || $fyear['authorized']))
                    $('#voucherForm').find('input, select, textarea').prop('disabled', true);
                    $('#revisionNumber').prop('disabled', false);
            @endif
            bind();

            if (orgCurrency != "") {
                $.each(currencies, function(key, value) {
                    if (value['id'] == orgCurrency) {
                        orgCurrencyName = value['short_name'];
                    }
                });
                $('#orgCurrencyName').text(orgCurrencyName);
            }
            if ($('#org_currency_id').val() == "")
                getExchangeRate();
            getAccounts();
            calculateTotal();
        });

        $(function() {
            $("input[name='payment_type']").click(function() {
                if ($("#Bank").is(":checked")) {
                    $(".bankfield").show();
                    $(".cashfield").hide();
                    $('.bankInput').prop('required', true);
                    $('.reference_no').prop('required', true).removeClass('is-invalid');
                    $('.ref-no-header').show(); // Show the header
                    $('.reference_no').closest('td').show(); // Show the Ref No. column
                    $('#ledger_id').attr('required', false);

                } else {
                    $(".cashfield").show();
                    $(".bankfield").hide();
                    $('.bankInput').prop('required', false);
                    $('.reference_no').prop('required', false).val('').removeClass('is-invalid');
                    $('.ref-no-header').hide(); // Hide the header
                    $('.reference_no').closest('td').hide(); // Hide the Ref No. column
                    $('.reference_no').next('.text-danger').text(''); // Clear error messages
                    $('#ledger_id').prop('required', true);

                }
            });
        });

        $(function() {
            function updateTotalColspan() {
                    const isBank = $("#Bank").is(":checked");
                    $(".totalsubheadpodetail td:first-child").attr("colspan", "6");
                }

                // Initial update
                updateTotalColspan();

                // Update when payment type changes
                $("input[name='payment_type']").change(function() {
                    updateTotalColspan();
                });

            function initializeAutocomplete() {
                $(".ledgerselect").autocomplete({
                    source: function(request, response) {
                        // Get all pre-selected ledgers
                        var preLedgers = [];
                        $(".ledgers").each(function() {
                            if ($(this).val() != "") {
                                preLedgers.push($(this).val());
                            }
                        });

                        $.ajax({
                            headers: {
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                            },
                            url: "{{ route('getParties') }}",
                            type: "POST",
                            dataType: "json",
                            data: {
                                keyword: request.term,
                                ids: preLedgers,
                                type: $("#document_type").val(),
                                _token: "{!! csrf_token() !!}",
                            },
                            success: function(data) {
                                response(data); // Pass the data to the response callback
                            },
                            error: function() {
                                response(
                            []); // Respond with an empty array in case of error
                            },
                        });
                    },
                    minLength: 0,
                    select: function(event, ui) {
                        const documentType = $("#document_type").val();
                        const isReceipts = (documentType === '{{ ConstantHelper::RECEIPTS_SERVICE_ALIAS }}');

                        let relation = null;
                        let relationLabel = '';

                        if (isReceipts) {
                            relation = ui.item.customer;
                            relationLabel = 'Customer';
                        } else {
                            relation = ui.item.vendor;
                            relationLabel = 'Vendor';
                        }

                        // Check if relation exists
                        if (!relation) {
                            Swal.fire({
                                icon: 'warning',
                                title: `${relationLabel} Missing`,
                                text: `${relationLabel} does not exist for this ledger.`
                            });
                            return false; // Block selection
                        }

                        // Check credit_days
                        if (!relation.credit_days || relation.credit_days == 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: `${relationLabel} Credit Days Missing`,
                                text: `This ${relationLabel.toLowerCase()} does not have credit days set.`
                            });
                            return false; // Block selection
                        }
                        $(this).val(ui.item.code);

                        const id = $(this).attr("data-id");
                        $("#party_id" + id).val(ui.item.value);
                        $("#party_vouchers" + id).val("");
                        $("#excAmount" + id).val("0.00");
                        $("#organization" + id).val(ui.item.organization.name);
                        $(".drop" + id).val("");
                        $(".excAmount" + id).val("0.00");
                        $("#vouchersBody").empty();
                        $("#inlineCheckbox1").attr("checked", false);
                        calculateTotal();
                        calculateSettle();
                        $("#party_name" + id).val(ui.item.label);
                        let groupDropdown = $(`#groupSelect${id}`);
                            $.ajax({
                            url: '{{ route('voucher.getLedgerGroups') }}',
                            method: 'GET',
                            data: {
                                ledger_id: ui.item.value,
                                _token: $('meta[name="csrf-token"]').attr(
                                    'content') // CSRF token
                            },
                            success: function(response) {
                                groupDropdown.empty(); // Clear previous options

                                response.forEach(item => {
                                    groupDropdown.append(
                                        `<option value="${item.id}" data-ledger="${ui.item.label}">${item.name}</option>`
                                    );
                                });
                                groupDropdown.data('ledger',ui.item.label);

                            },
                            error: function(xhr) {
                                let errorMessage =
                                'Error fetching group items.'; // Default message

                                if (xhr.responseJSON && xhr.responseJSON.error) {
                                    errorMessage = xhr.responseJSON
                                    .error; // Use API error message if available
                                }
                                showToast("error", errorMessage);


                            }
                        });

                        return false;
                    },
                    change: function(event, ui) {
                        if (!ui.item) {
                            $(this).val("");
                            const id = $(this).attr("data-id");
                            $("#party_id" + id).val("");
                        }
                    },
                    focus: function() {
                        return false; // Prevents default behavior
                    },
                }).focus(function() {
                    if (this.value == "") {
                        $(this).autocomplete("search");
                    }
                    return false; // Prevents default behavior
                });
            }
            initializeAutocomplete();
            // Monitor input field for empty state
            $(".ledgerselect").on('input', function() {
                var inputValue = $(this).val();
                if (inputValue.trim() === '') {
                    const id = $(this).attr("data-id");
                    $('#party_id' + id).val('');
                }
            });

            $('.mrntableselectexcel').on('click', '.deleteRow', function(e) {
                e.preventDefault();
                let row = $(this).closest('tr');
                row.remove();
                updateLevelNumbers();
                // evaluateCostCenterVisibility();
                calculateTotal();
            });

            $('.add-row').click(function(e) {
                e.preventDefault();
                let rowCount = document.querySelectorAll('.mrntableselectexcel tr').length + 1;
                let newRow = `
                    <tr class="approvlevelflow">
                        <td>${rowCount}</td>
                        <td class="poprod-decpt">
                            <input type="text" placeholder="Select" class="form-control mw-100 ledgerselect partyCode${rowCount} mb-25" required data-id="${rowCount}"/>
                            <input type="hidden" name="party_id[]" type="hidden" id="party_id${rowCount}" class="ledgers"/>
                            <input type="hidden" name="party_vouchers[]" type="hidden" id="party_vouchers${rowCount}" class="party_vouchers"/>
                        </td>
                        <td class="poprod-decpt"><input type="text" disabled placeholder="Select" class="form-control mw-100 mb-25 partyName" id="party_name${rowCount}"/></td>
                        <td>
                            <select required id="groupSelect${rowCount}"
                                name="parent_ledger_id[]"
                                class="ledgerGroup form-select mw-100">
                            </select>
                        </td>
                        <td>
                            <input type="text" disabled
                                placeholder="Select"
                                class="form-control mw-100 mb-25 organization"
                                id="organization${rowCount}"
                                    />
                        </td>
                        <td>
                            <div class="position-relative d-flex align-items-center">
                                <select
                                    class="form-select mw-100 invoiceDrop drop${rowCount}"
                                    data-id="${rowCount}" name="reference[]">
                                    <option value="">Select</option>
                                    <option>Invoice</option>
                                    <option>Advance</option>
                                    <option>On Account</option>
                                </select>
                                <div class="ms-50 flex-shrink-0">
                                    <button type="button"
                                        class="btn p-25 btn-sm btn-outline-secondary invoice${rowCount}" style="font-size: 10px" onclick="openInvoice(${rowCount})">Invoice</button>
                                </div>
                            </div>
                        </td>
                        <td><input type="number" value="0" class="form-control mw-100 text-end amount" name="amount[]" id="excAmount${rowCount}" required/></td>
                        <td><input type="text" value="0" readonly class="form-control mw-100 text-end amount_exc excAmount${rowCount}" name="amount_exc[]" required/></td>
                         <td>
                            <input type="number" class="form-control mw-100 bankInput reference_no" 
                                name="reference_no[]" data-row="${rowCount}" id="reference_no${rowCount}" />
                            <span class="text-danger bankInput" id="reference_error${rowCount}" style="font-size:12px"></span>
                        </td>
                        <td><a href="#" class="text-danger deleteRow"><i data-feather="trash-2"></i></a></td>
                    </tr>`;
                $('.mrntableselectexcel').append(newRow);
                                
                // Initialize reference tracking for the new row
                $(`#reference_no${rowCount}`).on('input', function() {
                    validateReferenceNumbers();
                });
                
                // Set visibility based on payment type
                if ($("#Bank").is(":checked")) {
                    $(`#reference_no${rowCount}`).prop('required', true).closest('td').show();
                } else {
                    $(`#reference_no${rowCount}`).prop('required', false).closest('td').hide();
                }
                
                bind();
                initializeAutocomplete();
                updateLevelNumbers();
                feather.replace({
                    width: 14,
                    height: 14
                });
                $('.select2').select2();
                count++;
            });

             $(document).on('keyup keydown', '.amount', function() {
                if ($('#orgExchangeRate').val() == "") {
                    showToast('error','Select currency first!!');
                    return false;
                }
                const inVal = parseFloat($(this).val()) || 0;
                if (inVal > 0) {
                    $("." + $(this).attr('id')).val($(this).val() * $('#orgExchangeRate').val());
                }else {
                    $("." + $(this).attr('id')).val("0.00");
                }
                calculateTotal();
            });

            $('#orgExchangeRate').change(function() {
                resetCalculations();
            });

            // $('#document_type').change(function() {
            //     $('.ledgerselect').val('');
            //     $('.ledgers').val('');
            //     $('.partyName').val('');
            // });
        });

        function updateLevelNumbers() {
            $('.approvlevelflow').each(function(index) {
                var level = index + 1;
                $(this).find('td:first-child').text(level);
            });
        }

        function updateLevelNumbers() {
            $('.approvlevelflow').each(function(index) {
                var level = index + 1;
                $(this).find('td:first-child').text(level);
            });
        }

        function submitForm(status) {

            $('#status').val(status);
            if ($('#reference_no').hasClass('is-invalid') && $("#Bank").is(":checked")){
                showToast('error', 'Reference no. Already exist');
                return false;
            }
            else
            {
            if ($('#action_type').val() === "amendment")
                $("#amendmentModal").modal('show');
            else
            $('#submitButton').click();
        }

        }



        function getAccounts() {
            var accounts = [];
            $('#account_id').empty();
            $('#account_id').prepend('<option disabled selected value="">Select Bank Account</option>');

            const bank_id = $('#bank_id').val();
            $.each(banks, function(key, value) {
                if (value['id'] == bank_id) {
                    accounts = value['bank_details'];
                }
            });

            const preSelected = "{{ $data->account_id }}";
            $.each(accounts, function(key, value) {
                if (value['id'] == parseInt(preSelected)) {
                    $("#account_id").append("<option value ='" + value['id'] + "' selected>" + value[
                        'account_number'] + " </option>");
                } else {
                    $("#account_id").append("<option value ='" + value['id'] + "'>" + value['account_number'] +
                        " </option>");
                }
            });
        }

        function getExchangeRate() {
            if ($('#currency_id').val() != "") {
                $.each(currencies, function(key, value) {
                    if (value['id'] == $('#currency_id').val()) {
                        $('#selectedCurrencyName').text(value['short_name']);
                    }
                });
            }

            if (orgCurrency != "") {
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
                        currency: $('#currency_id').val()
                    },
                    success: function(response) {
                        if (response.status) {

                            $('#orgExchangeRate').val(response.data.org_currency_exg_rate).trigger('change');

                            $('#org_currency_id').val(response.data.org_currency_id);
                            $('#org_currency_code').val(response.data.org_currency_code);
                            $('#org_currency_exg_rate').val(response.data.org_currency_exg_rate);

                            $('#comp_currency_id').val(response.data.comp_currency_id);
                            $('#comp_currency_code').val(response.data.comp_currency_code);
                            $('#comp_currency_exg_rate').val(response.data.comp_currency_exg_rate);

                            $('#group_currency_id').val(response.data.group_currency_id);
                            $('#group_currency_code').val(response.data.group_currency_code);
                            $('#group_currency_exg_rate').val(response.data.group_currency_exg_rate);

                            $('#base_currency_code').val(response.data.org_currency_code);
                            $('#company_currency_code').val(response.data.comp_currency_code);
                            $('#company_exchange_rate').val(response.data
                                .comp_currency_exg_rate);
                            $('#grp_currency_code').val(response.data.group_currency_code);
                            $('#grp_exchange_rate').val(response.data
                                .group_currency_exg_rate);

                        } else {
                            resetCurrencies();
                            $('#orgExchangeRate').val('');
                            showToast('error',response.message);
                        }
                    }
                });

            } else {
                showToast('error','Organization currency is not set!!');
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
        }
        function resetCalculations() {
            $('#org_currency_exg_rate').val($('#orgExchangeRate').val());
            $('.amount').each(function() {
                if ($(this).val() != "") {
                    const inVal = parseFloat($(this).val()) || 0;
                    if (inVal > 0) {
                        $("." + $(this).attr('id')).val($(this).val() * $('#orgExchangeRate').val());
                    }
                }
            });
            calculateTotal();
        }

        function calculateTotal() {
            let currentCurrencySum = 0;
            let orgCurrencySum = 0;
            
            $('.amount').each(function() {
                const value = parseFloat($(this).val()) || 0;
                currentCurrencySum = parseFloat(parseFloat(currentCurrencySum + value).toFixed(2));
                
                // Calculate amount_exc for each row
                const rowId = $(this).attr('id').replace('excAmount', '');
                const excValue = value * parseFloat($('#orgExchangeRate').val() || 1);
                $('.excAmount' + rowId).val(excValue.toFixed(2));
                orgCurrencySum = parseFloat(parseFloat(orgCurrencySum + excValue).toFixed(2));
            });
            
            $('.currentCurrencySum').text(formatIndianNumber(currentCurrencySum));
            $('.orgCurrencySum').text(formatIndianNumber(orgCurrencySum));
            $('#totalAmount').val(orgCurrencySum);
        }

        function get_voucher_details() {
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



    </script>
    <script>

        function onPostVoucherOpen(type = "not_posted") {
            resetPostVoucher();

            const apiURL = "{{ route('paymentVouchers.getPostingDetails') }}";
            const remarks = $("#remarks").val();
            $.ajax({
                url: apiURL + "?book_id=" + "{{ $data->book_id }}" + "&document_id=" + "{{ $data->id }}" +
                    "&remarks=" + remarks + "&type={{ $data->document_type }}",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    if (!data.data.status) {
                        Swal.fire({
                            title: 'Error!',
                            text: data.data.message,
                            icon: 'error',
                        });
                        return;
                    }
                    const voucherEntries = data.data.data;
                    var voucherEntriesHTML = ``;
                    Object.keys(voucherEntries.ledgers).forEach((voucher) => {
                        voucherEntries.ledgers[voucher].forEach((voucherDetail, index) => {
                            voucherEntriesHTML += `
                    <tr>
                    <td>${voucher}</td>
                    <td class="fw-bolder text-dark">${voucherDetail.ledger_group_code ? voucherDetail.ledger_group_code : ''}</td>
                    <td>${voucherDetail.ledger_code ? voucherDetail.ledger_code : ''}</td>
                    <td>${voucherDetail.ledger_name ? voucherDetail.ledger_name : ''}</td>
                    <td class="text-end">${voucherDetail.debit_amount > 0 ? parseFloat(voucherDetail.debit_amount).toFixed(2) : ''}</td>
                    <td class="text-end">${voucherDetail.credit_amount > 0 ? parseFloat(voucherDetail.credit_amount).toFixed(2) : ''}</td>
					</tr>
                    `
                        });
                    });
                    voucherEntriesHTML += `
            <tr>
                <td colspan="4" class="fw-bolder text-dark text-end">Total</td>
                <td class="fw-bolder text-dark text-end">${voucherEntries.total_debit.toFixed(2)}</td>
                <td class="fw-bolder text-dark text-end">${voucherEntries.total_credit.toFixed(2)}</td>
			</tr>
            `;
                    document.getElementById('posting-table').innerHTML = voucherEntriesHTML;
                    document.getElementById('voucher_doc_no').value = voucherEntries.document_number;
                    document.getElementById('voucher_date').value = moment(voucherEntries.document_date).format(
                        'D/M/Y');
                    document.getElementById('voucher_book_code').value = voucherEntries.book_code;
                    document.getElementById('voucher_currency').value = voucherEntries.currency_code;
                    if (type === "posted") {
                        document.getElementById('posting_button').style.display = 'none';
                    } else {
                        document.getElementById('posting_button').style.removeProperty('display');
                    }
                    $('#postvoucher').modal('show');
                }
            });

        }

        function resetPostVoucher() {
            document.getElementById('voucher_doc_no').value = '';
            document.getElementById('voucher_date').value = '';
            document.getElementById('voucher_book_code').value = '';
            document.getElementById('voucher_currency').value = '';
            document.getElementById('posting-table').innerHTML = '';
            document.getElementById('posting_button').style.display = 'none';
        }

        function postVoucher(element) {
             Swal.fire({
            title: 'Are you sure?',
            text: "Note: Once Submit the Voucher you are not able to redo the entry",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, post it!',
            cancelButtonText: 'Cancel'
            }).then((result) => {
            if (result.isConfirmed) {
            $('.preloader').show();
            const bookId = "{{ $data->book_id }}";
            const type = "{{ $data->document_type }}"
            const documentId = "{{ $data->id }}";
            const postingApiUrl = "{{ route('paymentVouchers.post') }}";
            const remarks = $("#remarks").val();
            console.log(bookId);
            console.log(documentId);
            if (bookId && documentId) {
                $.ajax({
                    url: postingApiUrl,
                    type: "POST",
                    dataType: "json",
                    contentType: "application/json", // Specifies the request payload type
                    data: JSON.stringify({
                        // Your JSON request data here
                        book_id: bookId,
                        document_id: documentId,
                        remarks: remarks,
                        type: type,

                    }),
                    success: function(data) {
                        const response = data.data;
                        if (response.status) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                            });
                            if ("{{$data->document_type}}" === 'Receipt' || "{{$data->document_type}}" === 'receipts' )
                                location.href = '/receipts';
                            else
                                location.href = '/payments';


                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                            });
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Some internal error occured',
                            icon: 'error',
                        });
                    }
                });

            }
            }
            else{
                $('#postvoucher').modal('hide');
            }
        });   
        }

    $(document).on('click', '#revokeButton', (e) => {
    let actionUrl = '{{ route("paymentVouchers.revoke.document") }}'+ '?id='+'{{$data->id}}';
    $('.preloader').show();
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            $('.preloader').hide();
            if(data.status == 'error') {
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

function changerate(){
        $('#org_currency_exg_rate').val($('#orgExchangeRate').val());
        calculateTotal();
        }
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
            let actionUrl = '{{ route("paymentVouchers.cancel.document") }}' + '?id=' + '{{$data->id}}';

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
function showToast(icon, title) {
            Swal.fire({
                        title:'Alert!',
                        text: title,
                        icon: icon
                    });
}
       function on_account_required(data){
            let onAccountRequired = false;
            $('.invoiceDrop').each(function() {
                    $(this).find('option').filter(function() {
                        return $(this).text().trim() === 'On Account';
                    }).hide();
                });


                if (data != null) {
                console.log(data.parameters.on_account_required);
                if (Array.isArray(data?.parameters?.on_account_required)) {
                    for (let i = 0; i < data.parameters.on_account_required.length; i++) {
                        if (data.parameters.on_account_required[i].trim().toLowerCase() === "yes") {
                            $('.invoiceDrop').each(function() {
                                $(this).find('option').filter(function() {
                                    return $(this).text().trim() === 'On Account';
                                }).show();
                    });

                            break; // Exit the loop once we find "yes"
                        }
                    }



                }
            }
        }

        function getDocNumberByBookId() {
            on_account_required(null);
            let currentDate = new Date().toISOString().split('T')[0];
            let bookId = $('#book_id').val();
            let document_date = $('#date').val();
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId + "&document_date=" +
                document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                    on_account_required(data.data);
                    }
                });
            });
        }
        getDocNumberByBookId();

        @if (session('success'))
            showToast("success", "{{ session('success') }}");
        @endif

        @if (session('error'))
            showToast("error", "{{ session('error') }}");
        @endif

        @if ($errors->any())
            showToast('error',
                "@foreach ($errors->all() as $error){{ $error }}@endforeach"
            );
        @endif
            $('#locations').on('change', function() {
                let selectedLocationIds = $(this).val();
                renderCostCentersForLocation(selectedLocationIds);
                // evaluateCostCenterVisibility(); // <- Add this line
            });

        function renderCostCentersForLocation(selectedLocationId) {
            const costCenterSet = locationCostCentersMap.filter(center => {
                if (!center.location) return false;
                const locationArray = Array.isArray(center.location)
                    ? center.location.flatMap(loc => loc.split(','))
                    : [];
                return locationArray.includes(String(selectedLocationId));
            });

            const $costCenterRow = $('#costCenterRow');
            const $dropdown = $('.costCenter');
            if (costCenterSet.length > 0) {
                $costCenterRow.show();
                $dropdown.empty();
                // $dropdown.append('<option value="">Select Cost Center</option>');
                costCenterSet.forEach(center => {
                    $dropdown.append(`<option value="${center.id}">${center.name}</option>`);
                });
            } else {
                $costCenterRow.hide();
                $dropdown.empty();
            }
        }
        let timer;
        // Global variable to track reference numbers
        let referenceNumbers = {};
        let serverValidationErrors = {}; // Track server-side validation errors

        // Function to validate reference numbers
        function validateReferenceNumbers() {
            let duplicatesFound = false;
            let hasEmptyFields = false;
            
            // Reset local duplicate tracking but keep server errors
            referenceNumbers = {}; 

            $('.reference_no').each(function() {
                const $input = $(this);
                const refNo = $input.val().trim();
                const row = $input.data('row');
                const $errorSpan = $('#reference_error' + row);
                
                // Clear previous local errors but preserve server errors
                if (!serverValidationErrors[row]) {
                    $input.removeClass('is-invalid');
                    $errorSpan.text('');
                }
                
                
                // Skip empty references for duplicate check
                if (refNo === '') {
                    return;
                }
                
                // Track reference numbers and their rows
                if (!referenceNumbers[refNo]) {
                    referenceNumbers[refNo] = [row];
                } else {
                    referenceNumbers[refNo].push(row);
                }
            });
            
            // Highlight duplicates
            Object.entries(referenceNumbers).forEach(([refNo, rows]) => {
                if (rows.length > 1) {
                    duplicatesFound = true;
                    rows.forEach(row => {
                        // Only override if no server error exists
                        if (!serverValidationErrors[row]) {
                            $(`#reference_no${row}`).addClass('is-invalid');
                            $(`#reference_error${row}`).text('This reference number already exists.');
                        }
                    });
                }
            });
            
            return !duplicatesFound && !hasEmptyFields;
        }



        // Update the input event handler
       $(document).on('input', '.reference_no', function() {
       clearTimeout(timer);
    
            const $input = $(this);
            const refNo = $input.val().trim();
            const row = $input.data('row');
            const $errorSpan = $('#reference_error' + row);
            
            // Clear server error if it exists for this field
            if (serverValidationErrors[row]) {
                delete serverValidationErrors[row];
            }
            
            // Clear previous validation
            $input.removeClass('is-invalid');
            $errorSpan.text('');
            
            // First validate locally
            validateReferenceNumbers();
            
            // Only check with server if reference is not empty and no local duplicates
            if (refNo.length > 0) {
                timer = setTimeout(function() {
                    $.ajax({
                        url: '{{ route('voucher.checkReference') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            reference_no: refNo,
                            // Include all other reference numbers in the form
                            otherRefs: Object.keys(referenceNumbers).filter(r => r !== refNo),
                        },
                        success: function(response) {
                            if (response.exists) {
                                // Track this as a server validation error
                                serverValidationErrors[row] = true;
                                $errorSpan.text(response.message || 'This reference number already exists.');
                                $input.addClass('is-invalid');
                            }
                        },
                        error: function(xhr) {
                            // Track this as a server validation error
                            serverValidationErrors[row] = true;
                            const errorMessage = xhr.responseJSON?.message || 'Error validating reference number.';
                            $errorSpan.text(errorMessage);
                            $input.addClass('is-invalid');
                        }
                    });
                }, 500);
            }
        });
        $(document).on('click', '#amendmentBtnSubmit', (e) => {
            let remark = $("#amendmentModal").find('[name="amend_remarks"]').val();
            if(!remark) {
                e.preventDefault();
                $("#amendRemarkError").removeClass("d-none");
                return false;
            } else {
                $("#amendmentModal").modal('hide');
                $("#amendRemarkError").addClass("d-none");
                e.preventDefault();
               $('#submitButton').click();
            }
        });
    </script>
@endsection
