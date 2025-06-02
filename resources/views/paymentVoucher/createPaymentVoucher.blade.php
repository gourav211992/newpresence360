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


            <form id="voucherForm" action="{{ $storeUrl }}" method="POST" enctype="multipart/form-data"
                onsubmit="return check_amount()">
                @csrf
                <input type="hidden" name="status" id="status">
                <input type="hidden" name="doc_number_type" id="doc_number_type">
                <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
                <input type="hidden" name="doc_prefix" id="doc_prefix">
                <input type="hidden" name="doc_suffix" id="doc_suffix">
                <input type="hidden" name="doc_no" id="doc_no">

                <input type="hidden" name="org_currency_id" id="org_currency_id">
                <input type="hidden" name="org_currency_code" id="org_currency_code">
                <input type="hidden" name="org_currency_exg_rate" id="org_currency_exg_rate">

                <input type="hidden" name="comp_currency_id" id="comp_currency_id">
                <input type="hidden" name="comp_currency_code" id="comp_currency_code">
                <input type="hidden" name="comp_currency_exg_rate" id="comp_currency_exg_rate">

                <input type="hidden" name="group_currency_id" id="group_currency_id">
                <input type="hidden" name="group_currency_code" id="group_currency_code">
                <input type="hidden" name="group_currency_exg_rate" id="group_currency_exg_rate">

                <input type="hidden" name="document_type" id="document_type" value="{{ $type }}">

                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">New {{ Str::ucfirst($type) }} Voucher
                                    </h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                            <li class="breadcrumb-item"><a
                                                    href="{{ $redirectUrl }}">{{ Str::ucfirst($type) }}
                                                    Vouchers</a></li>
                                            <li class="breadcrumb-item active">Add New</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <button onClick="javascript: history.go(-1)"
                                    class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
                                    Back</button>
                                <button type="button" onclick="submitForm('draft');" id="draft"
                                    class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"><i data-feather='save'></i> Save as
                                    Draft</button>
                                <button type="button" onclick="submitForm('submitted');"
                                    class="btn btn-primary btn-sm mb-50 mb-sm-0" id="submitted"><i
                                        data-feather="check-circle"></i>
                                    Submit</button>
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
                                                <div class="newheader  border-bottom mb-2 pb-25">
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-10">

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        {{-- {{ dd($books) }} --}}
                                                        <label class="form-label">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select" id="book_id" name="book_id"
                                                            required onchange="getDocNumberByBookId()">
                                                            <option disabled selected value="">Select</option>
                                                            @foreach ($books as $book)
                                                                <option value="{{ $book->id }}" {{ old('book_id') == $book->id ? 'selected' : '' }}>
                                                                    {{ strtoupper($book->book_code) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Document No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" id="voucher_no"
                                                            name="voucher_no" required value="{{ old('voucher_no') }}"
                                                            readonly />
                                                        @error('voucher_no')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control" name="date"
                                                            id="date" required value="{{ old('document_date') ?? date('Y-m-d') }}"
                                                            min="{{ $fyear['start_date'] }}"
                                                        max="{{ $fyear['end_date'] }}" />
                                                    </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Payment Type <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                    <div class="demo-inline-spacing">
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="Bank" value="Bank"
                                                                name="payment_type" class="form-check-input"
                                                                {{ old('payment_type', 'Bank') == 'Bank' ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bolder" for="Bank">Bank</label>
                                                        </div>
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="Cash" value="Cash"
                                                                name="payment_type" class="form-check-input"
                                                                {{ old('payment_type') == 'Cash' ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bolder" for="Cash">Cash</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Payment Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control" name="payment_date"
                                                            id="payment_date" required value="{{ old('payment_date') ??date('Y-m-d') }}"
                                                            min="{{ $fyear['start_date'] }}"
                                                        max="{{ $fyear['end_date'] }}" />
                                                    </div>
                                                </div>


                                                <div class="row align-items-center mb-1 bankfield">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Bank Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-3 mb-1 mb-sm-0">
                                                        <select class="form-control select2 bankInput" name="bank_id"
                                                            id="bank_id" onchange="getAccounts()" required>
                                                            <option selected disabled value="">Select Bank</option>
                                                            @foreach ($banks as $bank)
                                                                <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>
                                                                    {{ $bank->bank_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-md-1">
                                                        <label class="form-label">A/c No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <select class="form-control select2 bankInput" name="account_id"
                                                            id="account_id" required>
                                                            <option selected disabled value="">Select Bank Account
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1 bankfield">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Payment Mode <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-3 mb-1 mb-sm-0">
                                                        <select class="form-control select2 bankInput" name="payment_mode"
                                                            required>
                                                            <option value="">Select</option>
                                                            <option>IMPS/RTGS</option>
                                                            <option>NEFT</option>
                                                            <option>By Cheque</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <label class="form-label">Ref No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-3">
                                                    <input type="text" class="form-control bankInput" name="reference_no" id="reference_no" required />
                                                    <span class="text-danger bankInput" id="reference_error"></span>
                                                </div>

                                                </div>

                                                <div class="row align-items-center mb-1 cashfield" style="display: none">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Ledger <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-control select2" name="ledger_id"
                                                            id="ledger_id">
                                                            <option disabled selected value="">Select Ledger</option>
                                                            @foreach ($ledgers as $ledger)
                                                                <option value="{{ $ledger->id }}">{{ $ledger->name }}
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

                                                    <div class="col-md-5 mb-1 mb-sm-0">
                                                        <select class="form-control select2" name="currency_id"
                                                            id="currency_id" onchange="getExchangeRate()">
                                                            <option>Select Currency</option>
                                                            @foreach ($currencies as $currency)
                                                                <option value="{{ $currency->id }}"
                                                                    @if (old('currency_id') ?? $orgCurrency == $currency->id) selected @endif>
                                                                    {{ $currency->name . ' (' . $currency->short_name . ')' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                </div>




                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label mt-50">Exchange Rates</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" id="orgExchangeRate"
                                                            value="" oninput="resetCalculations()" />


                                                    </div>
                                                    <div hidden class="col-md-5 mb-1 mb-sm-0">
                                                        <div class="d-flex align-items-center">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="d-flex">
                                                                        <input type="text" class="form-control"
                                                                            readonly id="base_currency_code"
                                                                            value=""
                                                                            style="text-transform:uppercase;width: 80px; border-right: none; border-radius: 7px 0 0 7px" />
                                                                    </div>
                                                                    <label class="form-label">Base</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <div class="d-flex">
                                                                        <input type="text" class="form-control"
                                                                            readonly id="company_currency_code"
                                                                            value=""
                                                                            style="text-transform:uppercase;width: 80px; border-right: none; border-radius: 7px 0 0 7px" />
                                                                        <input type="text" class="form-control"
                                                                            readonly id="company_exchange_rate"
                                                                            value=""
                                                                            style="width: 80px; border-radius:0 7px 7px 0" />
                                                                    </div>
                                                                    <label class="form-label">Company</label>
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <div class="d-flex">
                                                                        <input type="text" class="form-control"
                                                                            readonly id="grp_currency_code" value=""
                                                                            style="text-transform:uppercase;width: 80px; border-right: none; border-radius: 7px 0 0 7px" />
                                                                        <input type="text" class="form-control"
                                                                            readonly id="grp_exchange_rate" value=""
                                                                            style="width: 80px; border-radius:0 7px 7px 0" />
                                                                    </div>
                                                                    <label class="form-label">Group</label>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Location <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="locations" class="form-select"
                                                            name="location" required>
                                                            <option disabled value="" selected>Select Location</option>
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}">
                                                                    {{ $location->store_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                </div>
                                                {{-- @if (count($cost_centers) > 0) --}}

                                                <div class="row align-items-center mb-1" id="costCenterRow" style="display: none;">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Cost Center <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="costCenter form-control select2" name="cost_center_id" id="cost_center_id">
                                                            {{-- options will be appended dynamically --}}
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- @endif --}}

                                            </div>



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
                                                            <a href="#"
                                                                class="btn btn-sm btn-outline-primary add-row">
                                                                <i data-feather="plus"></i> Add New</a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="totalAmount" id="totalAmount">
                                                <input type="hidden" name="exchangeRateData" id="exchangeRateData">

                                                <div class="table-responsive pomrnheadtffotsticky">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                        <thead>
                                                            <tr>
                                                                <th width="50px">#</th>
                                                                <th width="300px">Ledger Code</th>
                                                                <th width="300px">Ledger Name</th>
                                                                <th width="300px">Ledger Group</th>
                                                                <th width="200px">Reference</th>
                                                                <th width="200px" class="text-end">Amount (<span
                                                                        id="selectedCurrencyName"></span>)</th>
                                                                <th width="200px" class="text-end">Amount (<span
                                                                        id="orgCurrencyName"></span>)</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            <tr class="approvlevelflow">
                                                                <td>1</td>
                                                                <td class="poprod-decpt">
                                                                    <input type="text" placeholder="Select"
                                                                        class="form-control mw-100 ledgerselect partyCode1 mb-25"
                                                                        required data-id="1" />
                                                                    <input type="hidden" name="party_id[]"
                                                                        type="hidden" id="party_id1" class="ledgers" />
                                                                    <input type="hidden" name="party_vouchers[]"
                                                                        type="hidden" id="party_vouchers1"
                                                                        class="party_vouchers" />

                                                                </td>
                                                                <td class="poprod-decpt"><input type="text" disabled
                                                                        placeholder="Select"
                                                                        class="form-control mw-100 mb-25 partyName"
                                                                        id="party_name1" />
                                                                </td>
                                                                <td>
                                                                    <select required id="groupSelect1"
                                                                        name="parent_ledger_id[]"
                                                                        class="ledgerGroup form-select mw-100">
                                                                    </select>
                                                                </td>
                                                                <td>

                                                                    <div
                                                                        class="position-relative d-flex align-items-center">
                                                                        <select
                                                                            class="form-select mw-100 invoiceDrop drop1"
                                                                            data-id="1" name="reference[]">
                                                                            <option value="">Select</option>
                                                                            <option>Invoice</option>
                                                                            <option>Advance</option>
                                                                            <option>On Account</option>
                                                                        </select>
                                                                        <div class="ms-50 flex-shrink-0">
                                                                            <button type="button"
                                                                                class="btn p-25 btn-sm btn-outline-secondary invoice1"
                                                                                style="font-size: 10px"
                                                                                onclick="openInvoice(1)">Invoice</button>
                                                                        </div>
                                                                    </div>
                                                                </td>

                                                                <td><input type="number" value="0"
                                                                        class="form-control mw-100 text-end amount"
                                                                        name="amount[]" id="excAmount1" required /></td>
                                                                <td><input type="number" value="0" readonly
                                                                        class="form-control mw-100 text-end amount_exc excAmount1"
                                                                        name="amount_exc[]" required /></td>
                                                                <td></td>
                                                            </tr>
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
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Final Remarks</label>
                                                            <textarea type="text" rows="4" class="form-control" placeholder="Enter Remarks here..." name="remarks"></textarea>
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

    <div class="modal fade text-start" id="invoice" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select
                            Pending Invoices</h4>
                        <p class="mb-0">Settled Amount from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="text" id="fp-range" name="date_range"
                                    value="{{ Request::get('date_range') }}"
                                    class="form-control flatpickr-range bg-white"
                                    placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Voucher Type <span class="text-danger">*</span></label>
                                <select class="form-select select2" id="book_code">
                                    <option value="">Select Type</option>
                                    @foreach ($books_t->unique('alias') as $book)
                                        <option>{{ strtoupper($book->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Document No. <span class="text-danger">*</span></label>
                                <input type="text" id="document_no" class="form-control" />
                            </div>
                        </div>

                        <div class="col-md-3  mb-1">
                            <label class="form-label">&nbsp;</label><br />
                            <button type="button" class="btn btn-warning btn-sm" onclick="getLedgers()"><i
                                    data-feather="search"></i> Search</button>
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
                                                    <input class="form-check-input" type="checkbox" name="podetail"
                                                        id="inlineCheckbox1">
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
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                            data-feather="x-circle"></i> Cancel</button>
                    <button class="btn btn-primary btn-sm" type="button" onclick="setAmount()"><i
                            data-feather="check-circle"></i> Process</button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="currentParty">
    <input type="hidden" id="currentRow">
    <input type="hidden" id="LedgerId">
@endsection

@section('scripts')
    <script>
        var banks = {!! json_encode($banks) !!};
        var currencies = {!! json_encode($currencies) !!};
        var orgCurrency = {{ $orgCurrency }};
        var count = 2;
        var orgCurrencyName = '';

        // $('#voucherForm').on('submit', function () {
        //     $('.preloader').show();
        // });
        function setAmount() {
            let isValid = true;

            $('.settleInput').each(function() {
                let input = $(this);
                let row = input.closest('.voucherRows');
                let balanceText = row.find('.balanceInput').text().replace(/,/g, '');
                let balance = parseFloat(balanceText);
                let settleAmount = parseFloat(input.val());

                // Remove existing error message
                input.next('.invalid-feedback').remove();

                if (settleAmount > balance) {
                    input.addClass('is-invalid');
                    input.after(
                        '<span class="invalid-feedback d-block">Settle amount cannot be greater than balance.</span>'
                        );
                    isValid = false;
                } else {
                    input.removeClass('is-invalid');
                }
            });

            if (!isValid) {
                // Prevent modal close or further processing
                return false;
            }
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

        function openInvoice(id) {
            if ($('#party_id' + id).val() != "") {
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
                getLedgers();
                $('#invoice').modal('toggle');
            } else {
                $('.drop' + id).val('');
                showToast('error', 'Select ledger to select invoice!!');
            }
        }

        function getLedgers() {
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
                    partyCode: $('.partyCode' + $('#currentRow').val()).val(),
                    partyID: $('#party_id' + $('#currentRow').val()).val(),
                    ledgerGroup: $('#groupSelect' + $('#currentRow').val()).val(),
                    book_code: $('#book_code').val(),
                    document_no: $('#document_no').val(),
                    type: $('#document_type').val()
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
                                    html += `<tr id="${val['id']}" class="voucherRows">
                                            <td>${index+1}</td>
                                            <td>${val['date']}</td>
                                            <td class="fw-bolder text-dark">${val['series']['book_code'].toUpperCase()}</td>
                                            <td>${val['voucher_no']}</td>
                                            <td class="text-end">${formatIndianNumber(val['amount'])}</td>
                                            <td class="balanceInput text-end">${formatIndianNumber(val['balance'])}</td>
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
                        $('#LedgerId').val(response.ledgerId);
                        $('#vouchersBody').append(html);
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

        function adjustInvoice(rows) {
            let enteredAmount = parseFloat($(rows).val()) || 0;
            let entersettle = $(rows);

            let row = $(rows).closest("tr");
            let balance = parseFloat(row.find(".balanceInput").text().replace(/,/g, "")) || 0;

            if (enteredAmount > balance) {
                let excessAmount = enteredAmount - balance;
                if (excessAmount > 0) {
                    $(".voucherRows").each(function() {
                        let nextBalance = parseFloat($(this).find(".balanceInput").text().replace(/,/g, "")) || 0;
                        let nextSettleInput = $(this).find(".settleInput");
                        let checkBox = $(this).find(".vouchers");
                        let settle = parseFloat(nextSettleInput.val()) || 0;
                        let deduct = nextBalance - settle;
                        let nextSettle = settle + deduct;
                        if (excessAmount >= deduct && nextBalance > settle) {
                            excessAmount -= deduct;
                            nextSettleInput.val(deduct + settle);
                            if (nextSettleInput.val() != 0)
                                checkBox.prop('checked', true);
                            console.log(enteredAmount - deduct);

                            entersettle.val(enteredAmount - deduct);
                        }
                    });
                    $(".voucherRows").each(function() {
                        let nextBalance = parseFloat($(this).find(".balanceInput").text().replace(/,/g, "")) || 0;
                        let nextSettleInput = $(this).find(".settleInput");
                        let checkBox = $(this).find(".vouchers");
                        let settle = parseFloat(nextSettleInput.val()) || 0;
                        let deduct = nextBalance - settle;
                        let nextSettle = settle + deduct;
                        if (excessAmount >= deduct && nextBalance > settle) {
                            excessAmount -= deduct;
                            nextSettleInput.val(deduct + settle);
                            if (nextSettleInput.val() != 0)
                                checkBox.prop('checked', true);
                            console.log(enteredAmount - deduct);

                            entersettle.val(entersettle.val() - deduct);
                        } else if (excessAmount < deduct && nextBalance > settle) {
                            nextSettleInput.val(excessAmount + settle);
                            if (nextSettleInput.val() != 0)
                                checkBox.prop('checked', true);
                            entersettle.val(entersettle.val() - excessAmount);
                            excessAmount = 0;
                        }
                    });
                    $(".voucherRows").get().reverse().forEach(function() {
                        let checkBox = $(this).find(".vouchers");
                        let nextBalance = parseFloat($(this).find(".balanceInput").text().replace(/,/g, "")) || 0;
                        let nextSettleInput = $(this).find(".settleInput");
                        let settle = parseFloat(nextSettleInput.val()) || 0;
                        let deduct = nextBalance - settle;
                        let nextSettle = settle + deduct;
                        if (excessAmount >= deduct && nextBalance > settle) {
                            excessAmount -= deduct;
                            nextSettleInput.val(deduct + settle);
                            if (nextSettleInput.val() != 0)
                                checkBox.prop('checked', true);
                            console.log(enteredAmount - deduct);

                            entersettle.val(entersettle.val() - deduct);
                        } else if (excessAmount < deduct && nextBalance > settle) {
                            nextSettleInput.val(excessAmount + settle);
                            if (nextSettleInput.val() != 0)
                                checkBox.prop('checked', true);
                            entersettle.val(entersettle.val() - excessAmount);
                            excessAmount = 0;
                        }
                    });

                }
            }
        }



        function calculateSettle() {
            let settleSum = 0;
            $('.vouchers:checked').map(function() {
                const value = parseFloat($('.settleAmount' + this.value).val()) || 0;
                settleSum = parseFloat(settleSum) + value;
            }).get();
            $('.settleTotal').text(parseFloat(settleSum).toFixed(2));
        }


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
              if ($('#reference_no').hasClass('is-invalid') && $("#Bank").is(":checked")){
                     $('.preloader').hide();
                showToast('error', 'Reference no. Already exist');
                 $('#draft').attr('disabled', false);
            $('#submitted').attr('disabled', false);
                return false;


              }
        }



        $(function() {
            $('#inlineCheckbox1').click(function() {
                $('.vouchers').prop('checked', this.checked);
                selectAllVouchers();
            });
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
                input.after(
                    '<span class="invalid-feedback d-block">Settle amount cannot be greater than balance.</span>'
                    );
            } else {
                input.removeClass('is-invalid');
            }
            calculateSettle();
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
                                groupDropdown.data('ledger', ui.item.label);

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

            // Monitor input field for empty state
            $(".ledgerselect").on('input', function() {
                var inputValue = $(this).val();
                if (inputValue.trim() === '') {
                    const id = $(this).attr("data-id");
                    $('#party_id' + id).val('');
                }
            });
            initializeAutocomplete();

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
                                                                        <select required id="groupSelect${rowCount}"
                                                                            name="parent_ledger_id[]"
                                                                            class="ledgerGroup form-select mw-100">
                                                                        </select>
                                                                    </td>
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

            $(document).on('input', '.amount', function() {
                if ($('#orgExchangeRate').val() == "") {
                    showToast('error', 'Select currency first!!');
                    return false;
                }
                const inVal = parseFloat($(this).val()) || 0;
                if (inVal > 0) {
                    $("." + $(this).attr('id')).val($(this).val() * $('#orgExchangeRate').val());
                } else {
                    $("." + $(this).attr('id')).val("0.00");
                }
                calculateTotal();
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
    var oldSelected = "{{ old('account_id') }}"; // Inject the old value from Laravel
    $('#account_id').empty();
    $('#account_id').prepend('<option disabled value="">Select Bank Account</option>');

    const bank_id = $('#bank_id').val();
    $.each(banks, function(key, value) {
        if (value['id'] == bank_id) {
            accounts = value['bank_details'];
        }
    });

    $.each(accounts, function(key, value) {
        const isSelected = (value['id'] == oldSelected) ? 'selected' : '';
        $("#account_id").append("<option value='" + value['id'] + "' " + isSelected + ">" + value['account_number'] + "</option>");
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
                            $('#base_currency_code').val(response.data.org_currency_code);
                            $('#company_currency_code').val(response.data.comp_currency_code);
                            $('#company_exchange_rate').val(response.data
                                .comp_currency_exg_rate);
                            $('#grp_currency_code').val(response.data.group_currency_code);
                            $('#grp_exchange_rate').val(response.data
                                .group_currency_exg_rate);


                            $('#orgExchangeRate').val(response.data.org_currency_exg_rate).trigger(
                                'change');

                            $('#org_currency_id').val(response.data.org_currency_id);
                            $('#org_currency_code').val(response.data.org_currency_code);
                            $('#org_currency_exg_rate').val(response.data.org_currency_exg_rate);

                            $('#comp_currency_id').val(response.data.comp_currency_id);
                            $('#comp_currency_code').val(response.data.comp_currency_code);
                            $('#comp_currency_exg_rate').val(response.data.comp_currency_exg_rate);

                            $('#group_currency_id').val(response.data.group_currency_id);
                            $('#group_currency_code').val(response.data.group_currency_code);
                            $('#group_currency_exg_rate').val(response.data.group_currency_exg_rate);
                        } else {
                            resetCurrencies();
                            $('#orgExchangeRate').val('');
                            showToast('error', response.message);
                        }
                    }
                });

            } else {
                showToast('error', 'Organization currency is not set!!');
            }
        }
        $(document).ready(function() {
            bind();
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
            if($('#book_id').val())
            $('#book_id').trigger('change');
                if($('#bank_id').val())
                getAccounts();
            if($('#currency_id').val())
            getExchangeRate();
         if (orgCurrency != "") {
                $.each(currencies, function(key, value) {
                    if (value['id'] == orgCurrency) {
                        orgCurrencyName = value['short_name'];
                    }
                });
                $('#orgCurrencyName').text(orgCurrencyName);
            }
            getExchangeRate();
        });

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
                currentCurrencySum = parseFloat(parseFloat(removeCommas(currentCurrencySum) + value).toFixed(2));
            });
            $('.currentCurrencySum').text(formatIndianNumber(currentCurrencySum));

            let orgCurrencySum = 0;
            $('.amount_exc').each(function() {
                const value = parseFloat($(this).val()) || 0;
                orgCurrencySum = parseFloat(parseFloat(removeCommas(orgCurrencySum + value)).toFixed(2));
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

        function on_account_required(data) {
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
            on_account_required(null);
            let currentDate = new Date().toISOString().split('T')[0];
            let bookId = $('#book_id').val();
            let document_date = $('#date').val();
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId + "&document_date=" +
                document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        resetParametersDependentElements(data.data);
                        on_account_required(data.data);
                        $("#book_code_input").val(data.data.book_code);
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
                        showToast('error', data.message);
                    }
                });
            });
        }

        function bind() {

            $('.amount').on('click', function() {
                if ($(this).val() === "0" || $(this).val() === "0.00") {
                    $(this).val('');
                }
            });

            $('.amount').on('focusout', function() {
                if ($(this).val() === "") {
                    $(this).val('0.00');
                }
            });

        }

        function changerate() {
            $('#org_currency_exg_rate').val($('#orgExchangeRate').val());
            calculateTotal();
        }
        bind();

        function showToast(icon, title) {
            Swal.fire({
                title: 'Alert!',
                text: title,
                icon: icon
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
                "@foreach ($errors->all() as $error){{ $error }}@endforeach"
            );
        @endif

        //
        $('#locations').on('change', function () {
    let selectedLocationIds = $(this).val();

    // Ensure selectedLocationIds is always an array
    if (!Array.isArray(selectedLocationIds)) {
        selectedLocationIds = selectedLocationIds ? [selectedLocationIds] : [];
    }

    let costCenterSet = new Map();

    selectedLocationIds.forEach(locId => {
        let centersObj = locationCostCentersMap[locId] || {};
            let centers = Object.values(centersObj);
            console.log(centers);
            centers.forEach(center => {
                costCenterSet.set(center.id, center.name);
            });
    });

    // Get the div
    let $costCenterRow = $('#costCenterRow');
    let $dropdown = $('.costCenter');
    console.log(costCenterSet)

    // Show or hide the row based on availability
    if (costCenterSet.size > 0) {
        $costCenterRow.show();
        $dropdown.empty();
        costCenterSet.forEach((name, id) => {
            $dropdown.append(`<option value="${id}">${name}</option>`);
        });
    } else {
        $costCenterRow.hide();
        $dropdown.empty();
    }
});
        let timer;

        $('#reference_no').on('input', function () {
            clearTimeout(timer);
            const refNo = $(this).val();

            if (refNo.length > 0) {
                timer = setTimeout(function () {
                    $.ajax({
                        url: '{{ route("voucher.checkReference") }}', // route defined below
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            reference_no: refNo
                        },
                        success: function (response) {
                            if (response.exists) {
                                $('#reference_error').text('This reference number already exists.');
                                $('#reference_no').addClass('is-invalid');
                            } else {
                                $('#reference_error').text('');
                                $('#reference_no').removeClass('is-invalid');
                            }
                        }
                    });
                }, 500); // debounce
            } else {
                $('#reference_error').text('');
                $('#reference_no').removeClass('is-invalid');
            }
        });

    </script>
@endsection
