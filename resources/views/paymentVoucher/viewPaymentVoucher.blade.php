@php
    $type = $data->document_type ==='receipts'?'debit':'credit';
@endphp
@extends('layouts.app')

@section('styles')
<style>
    .settleInput {
        text-align: right;
    }
</style>
@endsection

@section('content')
<script>
    const locationCostCentersMap = @json(
        $locations->mapWithKeys(function ($location) {
            return [
                $location->id => $location->cost_centers->map(function ($cc) {
                    return ['id' => $cc->id, 'name' => $cc->name];
                }),
            ];
        }));
</script>
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

        <form id="voucherForm">
            @csrf

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
                                        <li class="breadcrumb-item active">View</li>
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
                                    @if ($buttons['draft'])
                                        <button type="button" onclick = "submitForm('draft');"
                                            class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" id="submit-button"
                                            name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                                    @endif
                                    @if($buttons['cancel'])
                                    <a id = "cancelButton" type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0"><i data-feather='x-circle'></i> Cancel</a>
                                    @endif

                                    @if ($buttons['submit'])
                                        <button type="button" onclick = "submitForm('submitted');"
                                            class="btn btn-primary btn-sm" id="submit-button" name="action"
                                            value="submitted"><i data-feather="check-circle"></i> Submit</button>
                                    @endif
                                    @if ($buttons['approve'])
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
                                            class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i data-feather="check-circle"></i> Post</button>
                                    @endif
                                    @if($data->document_status == "approved" || $data->document_status == "approval_not_required" || $data->document_status == "posted")
                                    <a data-bs-toggle="modal" data-bs-target="#addcoulmn" class="btn btn-primary btn-sm mb-0 waves-effect"><i data-feather="mail"></i> Send Mail</a>
                                    @endif
                                @endif
                                @if ($buttons['voucher'])
                                        <button type="button" onclick="onPostVoucherOpen('posted');"
                                            class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i data-feather="file-text"></i> Voucher</button>
                                    @endif



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
                                                    class="newheader d-flex justify-content-between  border-bottom mb-2 pb-25">
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
                                                       <div class="col-md-6 text-sm-end">
                                                        <span class="badge rounded-pill {{App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS_LIST[$data->document_status] ?? ''}} forminnerstatus">
                                                            <span class="text-dark">Status</span>
                                                             : <span class="{{App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS[$data->document_status] ?? ''}}">{{ucfirst($data->document_status)}}</span>
                                                        </span>
                                                </div>
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
                                                            value="{{ $data->date }}" max="{{ date('Y-m-d') }}" />
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
                                                            max="{{ date('Y-m-d') }}" />
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
                                                    <div class="col-md-2">
                                                        <label class="form-label">Ref No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="text" class="form-control bankInput"
                                                            name="reference_no" value="{{ $data->reference_no }}"
                                                            @if ($data->payment_type == 'Bank') required @endif />
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
                                                        <select id="locations" class="form-select select2" name="location">
                                                            <option disabled value="" selected>Select Locations</option>
                                                            @foreach ($locations as $location)
                                                            <option value="{{ $location->id }}"
                                                                {{ (isset($data->location) && $data->location == $location->id) ? 'selected' : '' }}>
                                                                {{ $location->store_name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                </div>
                                                {{-- @if(count($cost_centers) > 0 && $data->cost_center_id!=null)
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
                                                    $locationCostCenters = $selectedLocation->cost_centers ?? [];

                                                    // Check if the selected cost center exists in this location
                                                    $showCostCenter = count($locationCostCenters) > 0 || collect($locationCostCenters)->contains('id', $data->cost_center_id);
                                                @endphp

                                                <div class="row align-items-center mb-1" id="costCenterRow" style="{{ $showCostCenter ? '' : 'display:none;' }}">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cost Center <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5 mb-1 mb-sm-0">
                                                        <select class="costCenter form-control select2" name="cost_center_id" id="cost_center_id">
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
                                                               {{-- History Code --}}
                           @include('partials.approval-history', ['document_status' => $data->approvalStatus, 'revision_number' => $data->revision_number])
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
                                                                <th width="300px">Reference</th>
                                                                <th width="200px" class="text-end">Amount (<span
                                                                        id="selectedCurrencyName">{{ $data->currencyCode }}</span>)
                                                                </th>
                                                                <th width="200px" class="text-end">Amount (<span
                                                                        id="orgCurrencyName"></span>)</th>
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
                                                                            <option value="{{ $item?->ledger_group?->id ?? $item?->party?->ledger_group?->id }}">{{ $item?->ledger_group?->name ?? $item?->party?->ledger_group?->name }}</option>
                                                                        </select>
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
                                                                                <button type="button" class="btn p-25 btn-sm btn-outline-secondary invoice{{ $no }}" style="font-size: 10px" onclick="openInvoice({{ $no }},{{$data->id}},{{$item->id}})" @if($item->reference!="Invoice") disabled @endif>Invoice</button>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td><input type="number"
                                                                            class="form-control mw-100 text-end amount"
                                                                            name="amount[]"
                                                                            id="excAmount{{ $no }}"
                                                                            value="{{ $item->currentAmount }}" required />
                                                                    </td>
                                                                    <td><input type="number" readonly
                                                                            class="form-control mw-100 text-end amount_exc excAmount{{ $no }}"
                                                                            name="amount_exc[]"
                                                                            value="{{ $item->orgAmount }}" required />
                                                                    </td>
                                                                    <td>
                                                                        @if($data->document_status=="approved" || $data->document_status=="approval_not_required"||$data->document_status=="posted")
                                                                        <a href="javascript:void(0);" data-url="{{route('paymentVouchers.print',[$data->id,$item->ledger_id,$item->ledger_group_id])}}" class="text-primary print-btn"><i data-feather="printer"></i></a></td>
                                                                        @endif
                                                                    </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="totalsubheadpodetail">
                                                                <td colspan="5" class="text-end">Total</td>
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
                                <button type="submit" class="btn btn-primary" id="submit-button">Submit</button>
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
                                <input type="date" class="form-control" id="voucherDate" max="{{ date('Y-m-d') }}"/>
                            </div>
                        </div>

                         <div class="col-md-3 header_invoices">
                            <div class="mb-1">
                                <label class="form-label">Voucher Type <span class="text-danger">*</span></label>
                                <select class="form-select select2" id="book_code">
                                    <option value="">Select Type</option>
                                    @foreach ($books_t->unique('alias') as $book)
                                        <option>{{ $book->alias }}</option>
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
                                            <th class="text-end">Amount</th>
                                            <th class="text-end">Balance</th>
                                            <th class="text-end" width="150px">Settle Amt</th>
                                             <th class="text-center">
                                                 <div class="form-check form-check-inline me-0">
                                                    <input class="form-check-input" type="checkbox" name="podetail" disabled id="inlineCheckbox1">
                                                </div>
                                             </th>
                                          </tr>
                                        </thead>
                                        <tbody id="vouchersBody">
                                       </tbody>
                                       <tfoot>
                                            <tr>
                                                <td colspan="6" class="text-end">Total</td>
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
                    <button class="btn btn-outline-secondary btn-sm" hidden data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal" type="button" hidden onclick="setAmount()"><i data-feather="check-circle"></i> Process</button>
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

  <div class="modal fade text-start filterpopuplabel " id="addcoulmn" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Send Mail to {{$data->document_type=='payments'?'Vendor':'Customer'}}</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                         <div class=" customernewsection-form mb-1">

                            <div class="row mt-1">
                                <div class="col-md-8 mb-1">
                                    <label class="form-label">To <label class="text-danger">*</label></label>
                                    <select disabled class="form-select select2" name="to" id="to" required multiple>
                                        @foreach($to_users as $to)
                                        <option value="{{$to->id}}" data-type="{{$to->type}}" data-ledger_id="{{$to->ledger}}"  data-ledger_group_id="{{$to->group}}" selected>{{$to->email}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-8 mb-1">
                                    <label class="form-label">CC <label class="text-danger">*</label></label>
                                    @php
                                        $selectedCc = [App\Helpers\Helper::getAuthenticatedUser()->auth_user_id];
                                    @endphp

                                    <select class="form-select select2" name="cc" multiple>
                                        <option disabled>Select</option>
                                        @foreach($cc_users as $cc)
                                            <option value="{{ $cc->id }}" {{ in_array($cc->id, $selectedCc) ? 'selected' : '' }}>
                                                {{ $cc->email }}
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                             </div>
                             <input name="payment_voucher_id" type="hidden" value="{{$data->id}}">
                             <div class="col-md-12">
                                <label class="form-label">Remarks  <label class="text-danger">*</label></label>
                                <textarea
                                    class="form-control"
                                    placeholder="Enter Remarks"
                                    id="mail_remarks"
                                    name="mail_remarks"
                                    required
                                >Please find attached your current {{$data->document_type=='payments'?'payment':'receipt'}} advice.</textarea>
                            </div>
                        </div>
                     </div>
                 </div>
            </div>

            <div class="modal-footer">
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="applyBtn" class="btn btn-primary data-submit mr-1">Submit</button>
            </div>



        </div>
    </div>
</div>

@endsection

@section('scripts')

    <script>

document.addEventListener("click", function (e) {
    if (e.target.closest(".print-btn")) {
        e.preventDefault();
        $('.preloader').show();
        const btn = e.target.closest(".print-btn");
        const printUrl = btn.getAttribute("data-url");
// console.log(printUrl);
        $.ajax({
            url: printUrl,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function () {
                $('.preloader').hide();
                window.open(printUrl, '_blank');
            },
            error: function (xhr) {
                $('.preloader').hide();
                console.log(xhr.responseJSON)
                let errorMessage = 'An unexpected error occurred.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Print Error',
                    html: errorMessage,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    confirmButtonText: 'OK'
                });
            }
        });
    }
});


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
            $('#excAmount' + $('#currentRow').val()).val($('.settleTotal').text());
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
            if ($('#party_id'+id).val()!="") {
                $('.drop' + id).val('Invoice');
                const comingParty = $('#party_id' + id).val();
                if (comingParty != $('#currentParty').val()) {
                    $('#vouchersBody').empty();
                    $("#inlineCheckbox1").attr('checked', false);
                    calculateSettle();
                    $('#voucherDate').val('');
                }
                $('#currentParty').val(comingParty);
                $('#currentRow').val(id);
                getLedgers(paymentId,details,ref);
                $('#invoice').modal('toggle');
                if(paymentId!=null)
                $('.header_invoices').hide();
            } else {
                $('.drop' + id).val('');
                alert('Select party to select invoice!!');
            }
        }

        function getLedgers(paymentId=null,details=null,ref=null) {
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
                    date: $('#voucherDate').val(),
                    '_token': '{!! csrf_token() !!}',
                    partyCode: $('.partyCode'+$('#currentRow').val()).val(),
                    book_code: $('#book_code').val(),
                    partyID: $('#party_id' + $('#currentRow').val()).val(),
                    ledgerGroup: $('#groupSelect' + $('#currentRow').val()).val(),
                    document_no: $('#document_no').val(),
                    type: $('#document_type').val(),
                    payment_voucher_id:'{{$data->id}}',
                    page:'view',
                    details_id:details,
                },
                success: function(response) {
                    if (response.data.length > 0) {
                        var html = '';
                        $.each(response.data, function(index, val) {
                            if (!preSelected.includes(val['id'].toString())) {

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

                                if (val['balance'] < 1 && checked == "") {
                                    console.log('hii' + val['id']);
                                } else {
                                    if(val['settle']){
                                    html += `<tr id="${val['id']}" class="voucherRows">
                                            <td>${index+1}</td>
                                            <td>${val['date']}</td>
                                            <td class="fw-bolder text-dark">${val['series']['book_code'].toUpperCase()}</td>
                                            <td>${val['voucher_no']}</td>
                                            <td class="text-end">${formatIndianNumber(val['amount'])}</td>
                                            <td class="text-end">${formatIndianNumber(val['balance'])}</td>
                                            <td class="text-end">
                                                <input type="number" class="form-control mw-100 settleInput settleAmount${val['id']}" readonly data-id="${val['id']}" value="${val['settle']}"/>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-check-inline me-0">
                                                    <input class="form-check-input vouchers voucherCheck${val['id']}" data-id="${val['id']}" disabled type="checkbox" ${checked} checked name="vouchers" value="${val['id']}" data-amount="${dataAmount}">
                                                </div>
                                            </td>
                                        </tr>`;
                                    }
                                }
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
                settleSum = parseFloat(settleSum) + value;
            }).get();
            $('.settleTotal').text(parseFloat(settleSum).toFixed(2));
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
            let rowCount = document.querySelectorAll('.mrntableselectexcel tr').length;
            for (let index = 1; index <= rowCount; index++) {
                if (parseFloat($('#excAmount' + index).val()) == 0) {
                    alert('Can not save party with amount 0');
                    return false;
                }
            }

            if (parseFloat(removeCommas($('.currentCurrencySum').text())) == 0) {
                alert('Total amount should be greater than 0');
                return false;
            }
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

        $(document).on('change', '.invoiceDrop', function() {
            if ($(this).val() == "Invoice") {
                $('.invoice' + $(this).attr('data-id')).attr('disabled', false);
                $('#excAmount' + $(this).attr('data-id')).attr('readonly', true);
                openInvoice($(this).attr('data-id'));
            } else {
                $('.invoice' + $(this).attr('data-id')).attr('disabled', true);
                $('#excAmount' + $(this).attr('data-id')).attr('readonly', false);
                $('#party_vouchers' + $(this).attr('data-id')).val('[]');
            }
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
            calculateSettle();
        });

        function setApproval() {
            document.getElementById('action_type').value = "approve";
        }

        function setReject() {
            document.getElementById('action_type').value = "reject";
        }

        $(document).ready(function() {
            @if (!$buttons['draft'])
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
                    $('.bankInput').attr('required', true);
                    $('#ledger_id').attr('required', false);
                } else {
                    $(".cashfield").show();
                    $(".bankfield").hide();
                    $('.bankInput').attr('required', false);
                    $('#ledger_id').attr('required', true);
                }
            });
        });

        $(function() {
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
                        $(this).val(ui.item.code);

                        const id = $(this).attr("data-id");
                        $("#party_id" + id).val(ui.item.value);
                        $("#party_vouchers" + id).val("");
                        $("#excAmount" + id).val("0.00");
                        $(".drop" + id).val("");
                        $(".excAmount" + id).val("0.00");
                        $("#vouchersBody").empty();
                        $("#inlineCheckbox1").attr("checked", false);
                        calculateTotal();
                        calculateSettle();
                        $("#party_name" + id).val(ui.item.label);
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
                            <div class="position-relative d-flex align-items-center">
                                <select class="form-select mw-100 invoiceDrop drop${rowCount}" data-id="${rowCount}" name="reference[]">
                                    <option value="">Select</option>
                                    <option>Invoice</option>
                                    <option>Advance</option>
                                    <option>On Account</option>
                                </select>
                                <div class="ms-50 flex-shrink-0">
                                    <button type="button" class="btn p-25 btn-sm btn-outline-secondary invoice${rowCount}" style="font-size: 10px" onclick="openInvoice(${rowCount})">Invoice</button>
                                </div>
                            </div>
                        </td>

                        <td><input type="number" value="0" class="form-control mw-100 text-end amount" name="amount[]" id="excAmount${rowCount}" required/></td>

                        <td><input type="number" value="0" readonly class="form-control mw-100 text-end amount_exc excAmount${rowCount}" name="amount_exc[]" required/></td>
                        <td><a href="#" class="text-danger deleteRow"><i data-feather="trash-2"></i></a></td>
                    </tr>`;
                $('.mrntableselectexcel').append(newRow);
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
                    alert('Select currency first!!');
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
            $('#submitButton').click();
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
                            alert(response.message);
                        }
                    }
                });

            } else {
                alert('Organization currency is not set!!');
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
            $('.amount').each(function() {
                const value = parseFloat($(this).val()) || 0;
                currentCurrencySum = parseFloat(parseFloat(currentCurrencySum + value).toFixed(2));
            });
            $('.currentCurrencySum').text(formatIndianNumber(currentCurrencySum));

            let orgCurrencySum = 0;
            $('.amount_exc').each(function() {
                const value = parseFloat($(this).val()) || 0;
                orgCurrencySum = parseFloat(parseFloat(orgCurrencySum + value).toFixed(2));
            });
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

    $(document).on('click', '#revokeButton', (e) => {
    let actionUrl = '{{ route("paymentVouchers.revoke.document") }}'+ '?id='+'{{$data->id}}';
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
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
            // Proceed with AJAX request after confirmation
            let actionUrl = '{{ route("paymentVouchers.cancel.document") }}' + '?id=' + '{{$data->id}}';

            fetch(actionUrl)
                .then(response => response.json())
                .then(data => {
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
document.addEventListener('DOMContentLoaded', function () {
    const type = @json($type);

    document.getElementById('printButton').addEventListener('click', function (e) {
        e.preventDefault();
        const modal = new bootstrap.Modal(document.getElementById('partySelectModal'));
        modal.show();
    });

    document.getElementById('partySelectForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const selected = document.querySelector('input[name="party"]:checked');

        if (!selected) {
            alert("Please select a party to continue.");
            return;
        }

        const ledgerId = selected.getAttribute('data-ledgerid');
        const ledgerGroupId = selected.getAttribute('data-ledgergroupid');

        const url = `{{ route('crdr.report.ledger.print', ['type' => 'TYPE', 'ledger' => 'LEDGER', 'group' => 'GROUP']) }}`
            .replace('TYPE', type)
            .replace('LEDGER', ledgerId)
            .replace('GROUP', ledgerGroupId);

        window.open(url, '_blank');
        bootstrap.Modal.getInstance(document.getElementById('partySelectModal')).hide();
    });
});
function getSelectedData() {
    let selectedData = [];

    $('select[name="to"] option:selected').each(function() {
        selectedData.push({
            id: $(this).val(),
            type: $(this).data('type'),
            ledger_id:$(this).data('ledger_id'),
            ledger_group_id:$(this).data('ledger_group_id') // reads data-type="..." from the <option>
        });
    });

    return selectedData;
}
$('#applyBtn').on('click', function (e) {

            // Close the modal
            var filterModal = bootstrap.Modal.getInstance(document.getElementById('addcoulmn'));


            // Optionally handle the response here
            e.preventDefault();


            // Get the date value
            const dateValue = $('input[name="date"]').val();
            const today = new Date().toISOString().split('T')[0];

            var formData = {
                to: getSelectedData(),
                cc: $('select[name="cc"]').val(),
                remarks: $('textarea[name="mail_remarks"]').val(),
                payment_id: $('input[name="payment_voucher_id"]').val(),
                type:"{{$data->document_type}}",
            };
            let remarks= $('textarea[name="mail_remarks"]').val();
            let to = $('select[name="to"]').val();
            let cc = $('select[name="cc"]').val();

            var requiredFields = {
            "To": to,
            "CC": cc,
            "Remarks": remarks,
        };
            if (formData.to && formData.to.length > 0 || formData.type || formData.date) {


                // AJAX request
                let isValid=true;
                const fields = ['to','cc', 'mail_remarks'];


                fields.forEach(field => {
                    var inputField = $('[name="'+field+'"]');
                    var errorMessage = inputField.closest('.col-md-8, .col-md-4, .col-md-12').find('.invalid-feedback');

                    if (inputField.hasClass('select2-hidden-accessible')) {
                        // Select2 elements validation
                        if (!inputField.val() || inputField.val().length === 0) {
                            inputField.next('.select2-container').addClass('is-invalid');
                            errorMessage.show();
                            isValid=false;
                        } else {
                            inputField.next('.select2-container').removeClass('is-invalid');
                            errorMessage.hide();
                        }
                    } else {
                        // Standard input fields validation
                        if (!inputField.val().trim()) {
                            console.log(field);
                            inputField.addClass('is-invalid');
                            errorMessage.show();
                            isValid=false;
                        } else {
                            inputField.removeClass('is-invalid');
                            errorMessage.hide();
                        }
                    }
                });
                if(isValid){
                     $('.preloader').show();
                    $('#applyBtn').prop('disabled', true);
                    $.ajax({
                    url: "{{ route('paymentVouchers.email') }}",
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        // Show success message
                         $('.preloader').hide();
                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.onmouseenter = Swal.stopTimer;
                                toast.onmouseleave = Swal.resumeTimer;
                            }
                        });
                        Toast.fire({
                            icon: "success",
                            title: response.success
                        });
                        if (filterModal) {
                            filterModal.hide();
                            $('#applyBtn').prop('disabled',false);
                        }
                    },
                    error: function (xhr) {
                        $('.preloader').hide();
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;

                            // Handle and display validation errors
                            for (var field in errors) {
                                if (errors.hasOwnProperty(field)) {
                                    var errorMessages = errors[field];

                                    // Find the input field
                                    var inputField = $('[name="' + field + '"]');

                                    // If the field has the select2 class
                                    if (inputField.hasClass('select2')) {
                                        // Remove any previous error messages
                                        inputField.closest('.select2-wrapper').find(
                                            '.invalid-feedback').remove();

                                        // Append the error message after the select2 container
                                        inputField.closest('.select2-wrapper').append(
                                            '<div class="invalid-feedback d-block">' +
                                            errorMessages.join(', ') + '</div>');

                                        // Add is-invalid class to highlight the error
                                        inputField.next('.select2-container').addClass(
                                            'is-invalid');
                                    } else {
                                        // For normal inputs, remove previous error and append new one
                                        inputField.removeClass('is-invalid').addClass(
                                            'is-invalid');
                                        inputField.next('.invalid-feedback')
                                            .remove(); // Remove any previous error
                                        inputField.after(
                                            '<div class="invalid-feedback">' +
                                            errorMessages.join(', ') + '</div>');
                                    }
                                }
                            }
                        }


                    }
                });
            }
            } else {
                if (filterModal) {
                    filterModal.hide();
                    $('#applyBtn').prop('disabled',false);
                }
            }
        });

    </script>
@endsection
