@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Recovery</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">View Details</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    @php
                        if ($loan->type == 1) {
                            $view_route = 'loan.view_all_detail';
                            $edit_route = 'loan.home-loan-edit';
                            $delete_route = 'loan.home-loan-delete';
                        } elseif ($loan->type == 2) {
                            $view_route = 'loan.view_vehicle_detail';
                            $edit_route = 'loan.edit_vehicle_detail';
                            $delete_route = 'loan.delete_vehicle_detail';
                        } else {
                            $view_route = 'loan.view_term_detail';
                            $edit_route = 'loan.term-loan-edit';
                            $delete_route = 'loan.term-loan-delete';
                        }
                    @endphp
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</button>
                            @if ($buttons['reject'])
                                <button class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-target="#reject"
                                    data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</button>
                            @endif
                            @if ($buttons['approve'])
                                <button class="btn btn-success btn-sm mb-50 mb-sm-0" data-bs-target="#approved"
                                    data-bs-toggle="modal"><i data-feather="check-circle"></i> Approve</button>
                            @endif
                            @if($buttons['voucher'])
                                    <button type="button" onclick="onPostVoucherOpen('posted');" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg> Voucher</button>
                            @endif

                            @if ($buttons['post'])
                                <button onclick = "onPostVoucherOpen();" type = "button"
                                    class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg
                                        xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="feather feather-check-circle">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg> Post</button>
                            @endif
                            <a class="btn btn-primary btn-sm mb-50 mb-sm-0"
                                href="{{ route($view_route, ['id' => $loan->id]) }}"><i data-feather="check-circle"></i>
                                View
                                Application</a>
                        </div>
                    </div>

                </div>
            </div>
            <div class="content-body">

                <div id="recovery-add-update">
                    <input type="hidden" name="recovery_remain" id="recovery_remain" value="">
                    <input type="hidden" name="current_settled" id="current_settled" value="">

                    <section id="basic-datatable">
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body customernewsection-form">


                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
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
                                                        <select class="form-select" name="book_id" id="book_id" required>
                                                            <option value="">Select</option>
                                                            @if (isset($book_type))
                                                                @foreach ($book_type as $key => $val)
                                                                    <option value="{{ $val->id }}"
                                                                        @if (isset($data->book_id) && $val->id == $data->book_id) selected @endif>
                                                                        {{ $val->book_name }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" readonly class="form-control" id="document_no"
                                                            name="document_no" value="{{ $data->document_no }}" required>
                                                        <span id="document_no_error_message" class="text-danger"></span>
                                                        <span id="document_no_span"></span>
                                                        @error('document_no')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Application No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select select2" id="applicants"
                                                            name="application_no" required>
                                                            <option value="">Select</option>
                                                            @if (isset($applicants))
                                                                @foreach ($applicants as $key => $val)
                                                                    <option value="{{ $val->id }}"
                                                                        {{ isset($data->application_no) && $data->application_no == $val->id ? 'selected' : '' }}>
                                                                        {{ $val->appli_no }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Customer <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" id="cus_tomer" name="cus_tomer" readonly
                                                            class="form-control" value="" required>
                                                        @error('cus_tomer')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Loan Type <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" id="loan_type" name="loan_type" readonly
                                                            value="" class="form-control" required>
                                                        @error('loan_type')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                </div>

                                            </div>



                                        </div>



                                        <div class="row">

                                            <div class="col-md-12 revisedvalue mt-2">
                                                <div
                                                    class="newheader d-flex justify-content-between align-items-end mb-1 border-bottom pb-25">
                                                    <div class="header-left">
                                                        <h4 class="card-title text-theme">Loan Detail</h4>
                                                    </div>
                                                </div>

                                            </div>


                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Loan Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" id="loan_amount" name="loan_amount"
                                                            readonly class="form-control" required>
                                                        @error('loan_amount')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Disburse Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text"
                                                            value="{{ \App\Helpers\Helper::formatIndianNumber($data->dis_amount) }}"
                                                            id="dis_amount" name="dis_amount" readonly
                                                            class="form-control">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Recovered Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text"
                                                            value="{{ \App\Helpers\Helper::formatIndianNumber($data->rec_principal_amnt) }}"
                                                            id="rec_amnt" readonly class="form-control">
                                                        <input name="rec_amnt" type="hidden" id="rec_amnt_in">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Interest Recieved Till Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="hidden" id="rec_intrst_in" name="rec_intrst">
                                                        <input type="text"
                                                            value="{{ \App\Helpers\Helper::formatIndianNumber(round($data->rec_interest_amnt,2)) }}"
                                                            id="rec_intrst" readonly class="form-control">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Bal. Loan Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="hidden" id="balance_amount_in"
                                                            name="balance_amount" value="">
                                                        <input type="text" id="balance_amount"
                                                            value="{{ \App\Helpers\Helper::formatIndianNumber(round($data->balance_amount, 2)) }}"
                                                            readonly class="form-control" name="blnc_amnt" required>
                                                        @error('balance_amount')
                                                            <span class="text-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                </div>


                                            </div>



                                            <div class="col-md-3" hidden>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Bal. Interest Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" value="" id="bal_intrst_amnt"
                                                            readonly class="form-control">
                                                        <input type="hidden" name="bal_intrst_amnt"
                                                            id="bal_intrst_amnt_in">
                                                    </div>

                                                </div>

                                            </div>


                                            <div class="col-md-3" hidden>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Recovery Amount <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="number" class="form-control"
                                                            oninput="adjustAmounts()" name="recovery_amnnt"
                                                            id="recovery_amnnt" required>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Date of Payment <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="date" class="form-control"
                                                            value="{{ $data->payment_date }}" name="payment_date"
                                                            onchange="calculate_interest()" id="payment_date" required>
                                                    </div>

                                                </div>

                                            </div>


                                        </div>
                                        <div class="row my-2 settlement_detail">

                                            <div class="col-md-12 revisedvalue mt-2">
                                                <div
                                                    class="newheader d-flex justify-content-between align-items-end mb-1 border-bottom pb-25">
                                                    <div class="header-left">
                                                        <h4 class="card-title text-theme">Settlement Detail</h4>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-12">
                                                                <label class="form-label">Settled Amount <span
                                                                        class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <input type="text"
                                                                    value="{{ \App\Helpers\Helper::formatIndianNumber($data->settled_amnt) }}"
                                                                    name="settled_amnt" id="settled_amnt" readonly
                                                                    class="form-control">

                                                            </div>

                                                        </div>

                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-12">
                                                                <label class="form-label">Settled Recovery Amount <span
                                                                        class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <input type="text"
                                                                    value="{{ \App\Helpers\Helper::formatIndianNumber($data->settled_rec_amnt) }}"
                                                                    id="settled_rec_amnt" name="settled_rec_amnt" readonly
                                                                    class="form-control">

                                                            </div>

                                                        </div>

                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-12">
                                                                <label class="form-label">Settled Balance Amount <span
                                                                        class="text-danger">*</span></label>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <input type="text" value=""
                                                                    id="settled_blnc_amnt" name="settled_blnc_amnt"
                                                                    value="{{ \App\Helpers\Helper::formatIndianNumber($data->settled_blnc_amnt) }}"
                                                                    readonly class="form-control">

                                                            </div>

                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                            <br>
                                        </div>


                                        <div class="row my-2 settlement">


                                            <div class="col-md-12 revisedvalue">
                                                <div
                                                    class="newheader d-flex justify-content-between align-items-end mb-1 border-bottom pb-25">
                                                    <div class="header-left">
                                                        <h4 class="card-title text-theme">Settlement Info</h4>
                                                    </div>
                                                </div>

                                                <div class="table-responsive-md mb-1">



                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Date</th>
                                                                <th class="text-end">Disbursed</th>
                                                                <th class="text-end">Recovered</th>
                                                                <th class="text-end">Balance</th>
                                                                <th class="text-end">Interest</th>
                                                                <th class="text-end text-warning">Settled Int.</th>
                                                                <th class="text-end text-success">Settled Principal</th>
                                                                <th class="text-end">Remaining</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="recovery_history_2">
                                                            @php
                                                                // Decode the JSON data in dis_detail
                                                                $disDetailData =
                                                                    json_decode($data->dis_detail, true) ?? null;

                                                                // Initialize totals for each column
                                                                $totalActualDis = 0;
                                                                $totalRecovered = 0;
                                                                $totalBalance = 0;
                                                                $totalInterest = 0;
                                                                $totalSettledInterest = 0;
                                                                $totalSettledPrincipal = 0;
                                                                $totalRemaining = 0;
                                                            @endphp


                                                            @isset($data->dis_detail)
                                                                @foreach ($disDetailData as $key => $dis)
                                                                    @php
                                                                        // Accumulate totals
                                                                        $totalActualDis += $dis['disbursed'] ?? 0;
                                                                        $totalRecovered += $dis['recovered'] ?? 0;
                                                                        $totalBalance += $dis['balance'] ?? 0;
                                                                        $totalInterest += $dis['interest'] ?? 0;
                                                                        $totalSettledInterest +=
                                                                            $dis['settled_interest'] ?? 0;
                                                                        $totalSettledPrincipal +=
                                                                            $dis['settled_principal'] ?? 0;
                                                                        $totalRemaining += $dis['remaining'] ?? 0;
                                                                    @endphp


                                                                    <tr>
                                                                        <td>{{ $key + 1 }}</td>
                                                                        <td>{{ \Carbon\Carbon::parse($dis['recovery_date'])->format('d/m/y') }}
                                                                        </td>
                                                                        <td class="fw-bolder numeric-input text-dark text-end">
                                                                            {{ \App\Helpers\Helper::formatIndianNumber($dis['disbursed'] ?? 0, 2) }}
                                                                        </td>
                                                                        <td class="text-end numeric-input">
                                                                            {{ \App\Helpers\Helper::formatIndianNumber($dis['recovered'] ?? 0, 2) }}
                                                                        </td>
                                                                        <td class="text-end numeric-input">
                                                                            {{ \App\Helpers\Helper::formatIndianNumber($dis['balance'] ?? 0, 2) }}
                                                                        </td>
                                                                        <td class="text-end numeric-input">
                                                                            {{ \App\Helpers\Helper::formatIndianNumber($dis['interest'] ?? 0, 2) }}
                                                                        </td>
                                                                        <td class="text-end text-warning numeric-input">
                                                                            {{ \App\Helpers\Helper::formatIndianNumber($dis['settled_interest'] ?? 0, 2) }}
                                                                        </td>
                                                                        <td class="text-end text-success numeric-input">
                                                                            {{ \App\Helpers\Helper::formatIndianNumber($dis['settled_principal'] ?? 0, 2) }}
                                                                        </td>
                                                                        <td class="text-end numeric-input">
                                                                            {{ \App\Helpers\Helper::formatIndianNumber($dis['remaining'] ?? 0, 2) }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endisset
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="bg-light-success">
                                                                <td></td>
                                                                <td class="fw-bolder text-dark text-end">Total</td>
                                                                <td class="fw-bolder text-dark text-end">
                                                                    {{ \App\Helpers\Helper::formatIndianNumber($totalActualDis, 2) }}
                                                                </td>
                                                                <td class="fw-bolder text-dark text-end">
                                                                    {{ \App\Helpers\Helper::formatIndianNumber($totalRecovered, 2) }}
                                                                </td>
                                                                <td class="fw-bolder text-dark text-end">
                                                                    {{ \App\Helpers\Helper::formatIndianNumber($totalBalance, 2) }}
                                                                </td>
                                                                <td class="fw-bolder text-dark text-end">
                                                                    {{ \App\Helpers\Helper::formatIndianNumber($totalInterest, 2) }}
                                                                </td>
                                                                <td class="fw-bolder text-dark text-end">
                                                                    {{ \App\Helpers\Helper::formatIndianNumber($totalSettledInterest, 2) }}
                                                                </td>
                                                                <td class="fw-bolder text-dark text-end">
                                                                    {{ \App\Helpers\Helper::formatIndianNumber($totalSettledPrincipal, 2) }}
                                                                </td>
                                                                <td class="fw-bolder text-dark text-end">
                                                                    {{ \App\Helpers\Helper::formatIndianNumber($totalRemaining, 2) }}
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>

                                                </div>
                                            </div>

                                        </div>

                                        <div class="row">


                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Payment Mode <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <select class="form-select" name="payment_mode" required>
                                                            <option value="">Select</option>
                                                            <option value="by_cheque"
                                                                {{ isset($data->payment_mode) && $data->payment_mode == 'by_cheque' ? 'selected' : '' }}>
                                                                By Cheque</option>
                                                            <option value="neft"
                                                                {{ isset($data->payment_mode) && $data->payment_mode == 'neft' ? 'selected' : '' }}>
                                                                NEFT/IMPS/RTGS</option>
                                                            <option value="other"
                                                                {{ isset($data->payment_mode) && $data->payment_mode == 'other' ? 'selected' : '' }}>
                                                                Other</option>
                                                        </select>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1 bankdetail">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Reference No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="number" class="form-control" name="ref_no"
                                                            value="{{ $data->ref_no }}" required>
                                                    </div>

                                                </div>

                                            </div>


                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1 bankdetail">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Bank Name</label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <select class="form-control select2" name="bank_name"
                                                            id="bank" required onchange="get_account()">
                                                            <option value="">Select</option>
                                                            @foreach ($banks as $bank)
                                                                <option value="{{ $bank->id }}"
                                                                    {{ isset($data->bank_name) && $data->bank_name == $bank->id ? 'selected' : '' }}>
                                                                    {{ $bank->bank_name }} ({{ $bank->bank_code }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1 bankdetail">
                                                    <div class="col-md-12">
                                                        <label class="form-label">A/c No.</label>
                                                    </div>

                                                    <select class="form-control select2" name="account_number" required
                                                        id="account">
                                                        <option value="">Select Account
                                                            Number</option>

                                                    </select>
                                                </div>

                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Upload Document</label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="file" class="form-control" id="recovery_doc"
                                                            name="recovery">
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="col-md-6">
                                                <div class="row  mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Remarks</label>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="text" class="form-control"
                                                            value="{{ $data->remarks }}" name="remarks" id="remarks" />
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
    </div>
    <!-- END: Content-->

    <!-- END: Content-->

    <div class="modal fade text-start show" id="postvoucher" tabindex="-1" aria-labelledby="postVoucherModal"
        aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="postVoucherModal"> Voucher
                            Details</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                <input id = "voucher_currency" class="form-control" disabled="" value="">
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
                        class="btn btn-primary btn-sm waves-effect waves-float waves-light"><svg
                            xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="feather feather-check-circle">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg> Submit</button>
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
                            Reject Recovery
                        </h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0" id="rc_detail_re"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('loan.recovery_appr_rej') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="rc_appr_status" value="reject">
                    <input type="hidden" id="checkedDataVAL" name="checkedData" value="{{ $data->id }}">
                    <div class="modal-body pb-2">

                        <div class="row mt-2">

                            <div class="col-md-12">

                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" class="form-control" name="rc_appr_doc[]" id="fileInput" />
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="rc_appr_remark" id="re_appr_remark" required></textarea>
                                </div>


                            </div>

                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal"
                            aria-label="Close">Cancel</button>
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
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">
                            Approved Recovery
                        </h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0" id="rc_detail_re"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('loan.recovery_appr_rej') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="rc_appr_status" value="approve">
                    <input type="hidden" id="checkedDataVAL" name="checkedData" value="{{ $data->id }}">
                    <div class="modal-body pb-2">

                        <div class="row mt-2">

                            <div class="col-md-12">

                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" class="form-control" name="rc_appr_doc[]" id="fileInput" />



                                    <div class="mb-1">
                                        <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="rc_appr_remark" id="re_appr_remark" required></textarea>
                                    </div>


                                </div>

                            </div>
                        </div>

                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal"
                                aria-label="Close">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                </form>
            </div>
        </div>
    </div>



    <script type="text/javascript">
        var getSeriesUrl = "{{ url('loan/get-series') }}";
        var getvoucherUrl = "{{ url('/get_voucher_no') }}".trim();
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/js/loan.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

    <script>
        $(document).ready(function() {
            if ("{{ $data->settle_status ?? 0 }}" == "1") {
                $('.settlement').hide();
                $('.settlement_detail').show();
            } else {
                $('.settlement').show();
                $('.settlement_detail').hide();
            }

            let recevoryDate = null;

            $('#book_id').on('change', function() {
                var book_id = $(this).val();
                var request = $('#document_no');
                request.val('');
                if (book_id) {
                    $.ajax({
                        url: "{{ url('/loan/get-loan-request') }}/" + book_id,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            if (data.requestno == 1) {
                                request.prop('readonly', false);
                            } else {
                                request.prop('readonly', true);
                                request.val(data.requestno);
                            }
                        }
                    });
                }
            });
            $(document).on('blur', '#recovery_amnnt', function() {
                if (!applicants) {
                    $(this).val('');
                    alert('Select Application No First.');
                    return;
                }
            });

            $('#fileInput').on('change', function() {
                var files = this.files;
                var $fileList = $('#fileList');
                $fileList.empty();

                // Loop through selected files
                $.each(files, function(index, file) {
                    var fileSize = (file.size / 1024).toFixed(2) + ' KB'; // File size in KB
                    var fileName = file.name;
                    var fileExtension = fileName.split('.').pop()
                        .toUpperCase(); // Get file extension and make it uppercase

                    // Create a new image-uplodasection div
                    var $fileDiv = $('<div class="image-uplodasection mb-2"></div>');
                    var $fileIcon = $('<i data-feather="file" class="fileuploadicon"></i>');
                    var $fileName = $('<span class="file-name d-block"></span>').text(
                        fileExtension + ' file').css('font-size', '10px'); // Display extension
                    var $fileInfo = $('<span class="file-info d-block"></span>').text(fileSize).css(
                        'font-size', '10px'); // Display file size on the next line
                    var $deleteDiv = $(
                        '<div class="delete-img text-danger"><i data-feather="x"></i></div>');

                    $fileDiv.append($fileIcon).append($fileName).append($fileInfo).append(
                        $deleteDiv);
                    $fileList.append($fileDiv);
                    feather.replace();
                });
            });

            // Optional: Handle delete button click to remove the fileDiv
            $(document).on('click', '.delete-img', function() {
                $(this).closest('.image-uplodasection').remove();
            });

            $(document).on('change', '#applicants', function() {

                var customerID = $(this).val();
                //console.log(customerID);
                if (customerID != "") {
                    applicants = parseFloat($("#applicants").val()) || 0;



                    $.ajax({
                        url: '{{ route('loan.get.recovery.customer') }}',
                        data: {
                            id: customerID
                        },
                        dataType: 'json',
                        success: function(data) {

                            let loanData = 'Loan';
                            if (data.customer_record.type == 1) {
                                loanData = 'Home ' + loanData;
                            } else if (data.customer_record.type == 2) {
                                loanData = 'Vehicle ' + loanData;
                            } else if (data.customer_record.type == 3) {
                                loanData = 'Term ' + loanData;
                            }

                            var totalDisbursement = data.customer_record.loan_disbursements
                                .reduce(
                                    function(sum, disbursement) {
                                        if (disbursement.approvalStatus === "Disbursed") {
                                            if (disbursement.actual_dis)
                                                return sum + parseFloat(removeCommas(disbursement
                                                    .actual_dis));
                                            else
                                                return sum + parseFloat(removeCommas(disbursement
                                                    .dis_amount));
                                        }
                                        return sum;
                                    },
                                    0
                                );

                            $("#cus_tomer").val(data.customer_record.name);
                            $("#loan_type").val(loanData);
                            $("#loan_amount").val((data.customer_record.loan_appraisal
                                .term_loan));


                            calculate_interest();

                        },
                        error: function(xhr, status, error) {
                            console.log('AJAX Error:', status, error);
                        }
                    });
                }
            });
            $('#applicants').trigger('change');




        });

        function fetchLoanSeries(book_type_id, id) {
            return $.ajax({
                url: getSeriesUrl,
                method: 'GET',
                data: {
                    book_type_id: book_type_id
                },
                success: function(response) {
                    if (response.success === 1) {
                        $("#" + id).html(response.html);
                    } else {
                        alert(response.msg);
                        $("#" + id).html(response.html);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred while fetching the data.');
                }
            });
        }

        function fetchSeriesBased(series_id, id) {
            $.ajax({
                url: getvoucherUrl + '/' + series_id,
                method: 'GET',
                success: function(response) {
                    if (response.type == "Auto") {
                        $("#" + id).attr("readonly", true);
                        $("#" + id).val(response.voucher_no);
                    } else {
                        $("#" + id).attr("readonly", false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred while fetching the data.');
                }
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            const appliNoInput = document.getElementById('document_no');
            const errorMessage = document.getElementById('document_no_error_message');
            const appli_span = document.getElementById('document_no_span')

            function validateAppliNo() {
                const value = appliNoInput.value.trim();

                // Check if the string starts with a negative sign
                if (value.startsWith('-')) {
                    appli_span.textContent = '';
                    errorMessage.textContent = 'The Document number must not start with a negative sign.';
                    return false;
                }

                // Check if the string contains only allowed characters (letters, numbers, and dashes)
                const regex = /^[a-zA-Z0-9-_]+$/;
                if (!regex.test(value)) {
                    appli_span.textContent = '';
                    errorMessage.textContent =
                        'The Document number can only contain letters, numbers, dashes and underscores.';
                    return false;
                }

                // If all checks pass, clear the error message
                errorMessage.textContent = '';
                return true;
            }

            // Validate on blur
            appliNoInput.addEventListener('blur', validateAppliNo);
        });

        document.addEventListener('DOMContentLoaded', function() {
            const textInputs = document.querySelectorAll('input[type="text"]');

            textInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    if (this.value.length > 250) {
                        alert(
                            'You have exceeded the 250 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 250);
                    }
                });

                input.addEventListener('blur', function() {
                    if (this.value.length > 250) {
                        alert(
                            'You have exceeded the 250 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 250);
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const textInputs = document.querySelectorAll('input[type="number"]');

            textInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    if (this.value.length > 11) {
                        alert(
                            'You have exceeded the 11 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 11);
                    }
                });

                input.addEventListener('blur', function() {
                    if (this.value.length > 11) {
                        alert(
                            'You have exceeded the 11 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 11);
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const textareas = document.querySelectorAll('textarea');

            textareas.forEach(function(textarea) {
                function enforceCharacterLimit() {
                    if (this.value.length > 500) {
                        alert(
                            'You have exceeded the 500 character limit. Extra characters will be removed.'
                        );
                        this.value = this.value.substring(0, 500);
                    }
                }

                textarea.addEventListener('input', enforceCharacterLimit);
                textarea.addEventListener('blur', enforceCharacterLimit);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const numberInputs = document.querySelectorAll('input[type="number"]');

            function sanitizeInput(value) {
                let sanitized = value.replace(/-+/g, '');
                return sanitized.replace(/[^0-9]/g, '');
            }

            function updateValue(input) {
                let sanitizedValue = sanitizeInput(input.value);
                input.value = sanitizedValue;
            }

            numberInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    updateValue(this);
                });

                input.addEventListener('blur', function() {
                    updateValue(this);
                });
            });
        });


        function adjustAmounts() {
            revert_change();
            var amount = parseFloat(removeCommas($('#recovery_amnnt').val())) || 0;
            var pri = parseFloat(removeCommas($('#rec_amnt_in').val())) || 0;
            var inter = parseFloat(removeCommas($('#rec_intrst_in').val())) || 0;
            //  amount = amount + pri + inter;


            // Select all rows in the recovery history table

            const rows = document.querySelectorAll('#recovery_history tr');
            let recovery_remaining = amount;


            if (amount > 0) {
                rows.forEach(row => {
                    const statusCell = row.querySelector('[id^="recovery_status_"]');
                    //console.log(statusCell.value);
                    const old_set_i = parseFloat(removeCommas(row.querySelector('[id^="int_set_"]').value)) || 0;

                    if (statusCell.value != "fully_recover") {
                        if (recovery_remaining > 0) {

                            // Access cells for interest, settled interest, principal, settled principal, and remaining
                            const settledPrincipalCell = row.querySelector('[id^="rec_set_princ_"]');
                            const remainingCell = row.querySelector('[id^="rec_remaining_"]');
                            const settledInterestCell = row.querySelector('[id^="rec_set_intrst_"]');
                            const principalCell = row.querySelector('[id^="rec_dis_amnt_"]');
                            const interestCell = row.querySelector('[id^="rec_intrest_amnt_"]');
                            const balanceCell = row.querySelector('[id^="rec_blnc_amnt_"]');
                            const rowIndex = row.rowIndex;

                            // Select the corresponding row in #recovery_history_2
                            const otherRow = $('#recovery_history_2 tr');


                            // Find the interest cell with the specific ID in #recovery_history_2
                            const settledPrincipalCell2 = otherRow.find(`#rec_set_princ2_${rowIndex}`);
                            const settledInterestCell2 = otherRow.find(`#rec_set_intrst2_${rowIndex}`);


                            // Parse values, ensuring each is a valid number or defaulting to 0
                            let interest = parseFloat(removeCommas(interestCell.textContent)) || 0;
                            let settledInterest = parseFloat(removeCommas(settledInterestCell.textContent)) || 0;
                            let principal = parseFloat(removeCommas(principalCell.textContent)) || 0;
                            let settledPrincipal = parseFloat(removeCommas(settledPrincipalCell.textContent)) || 0;

                            // Ensure parsed values are numbers; if NaN, set them to 0
                            if (isNaN(interest)) interest = 0;
                            if (isNaN(settledInterest)) settledInterest = 0;
                            if (isNaN(principal)) principal = 0;
                            if (isNaN(settledPrincipal)) settledPrincipal = 0;

                            var recovery_interest = "";
                            //recovery_interest = recovery_remaining+inter;

                            // 1. Adjust interest first
                            if (interest > 0) {

                                if (recovery_remaining >= interest) {
                                    recovery_remaining = recovery_remaining - interest;

                                    settledInterestCell.textContent = interest.toFixed(2);
                                    settledInterestCell2.text(interest.toFixed(2));
                                } else {
                                    settledInterestCell.textContent = (recovery_remaining);
                                    settledInterestCell2.text(recovery_remaining);
                                    recovery_remaining = 0;
                                }

                            }
                            adjust_remaining();
                        }
                    }
                });

                rows.forEach(row => {

                    if (recovery_remaining > 0) {
                        // Access cells for interest, settled interest, principal, settled principal, and remaining
                        const settledPrincipalCell = row.querySelector('[id^="rec_set_princ_"]');
                        const remainingCell = row.querySelector('[id^="rec_remaining_"]');
                        const settledInterestCell = row.querySelector('[id^="rec_set_intrst_"]');
                        const principalCell = row.querySelector('[id^="balance_dis_"]');
                        const interestCell = row.querySelector('[id^="rec_intrest_amnt_"]');
                        const balanceCell = row.querySelector('[id^="rec_blnc_amnt_"]');
                        const old_set_p = parseFloat(removeCommas(row.querySelector('[id^="pri_set_"]').value)) || 0;
                        const otherRow = $('#recovery_history_2 tr');
                        const rowIndex = row.rowIndex;


                        // Find the interest cell with the specific ID in #recovery_history_2
                        const settledPrincipalCell2 = otherRow.find(`#rec_set_princ2_${rowIndex}`);
                        const settledInterestCell2 = otherRow.find(`#rec_set_intrst2_${rowIndex}`);






                        // Parse values, ensuring each is a valid number or defaulting to 0
                        let interest = parseFloat(removeCommas(interestCell.textContent)) || 0;
                        let settledInterest = parseFloat(removeCommas(settledInterestCell.textContent)) || 0;
                        let principal = parseFloat(removeCommas(principalCell.value)) || 0;
                        let settledPrincipal = parseFloat(removeCommas(settledPrincipalCell.textContent)) || 0;

                        // Ensure parsed values are numbers; if NaN, set them to 0
                        if (isNaN(interest)) interest = 0;
                        if (isNaN(settledInterest)) settledInterest = 0;
                        if (isNaN(principal)) principal = 0;
                        if (isNaN(settledPrincipal)) settledPrincipal = 0;



                        // 1. Adjust interest first
                        console.log(principal);
                        if (principal > 0) {
                            if (recovery_remaining >= principal) {
                                recovery_remaining = recovery_remaining - principal;
                                settledPrincipalCell.textContent = (principal).toFixed(2);
                                settledPrincipalCell2.text((principal).toFixed(2));


                            } else {
                                settledPrincipalCell.textContent = (recovery_remaining).toFixed(2);
                                settledPrincipalCell2.text((recovery_remaining).toFixed(2));
                                recovery_remaining = 0;
                            }

                        }

                        balanceCell.textContent = parseFloat(removeCommas(principalCell.textContent)).toFixed(2) - parseFloat(removeCommas(
                            settledPrincipalCell.textContent)).toFixed(2) || 0;
                        // console.log(balance.textContent);
                        adjust_remaining();
                    }
                });
                adjust_remaining();



            } else {
                rows.forEach(row => {
                    row.querySelector('[id^="rec_set_princ_"]').textContent = 0;
                    row.querySelector('[id^="rec_set_intrst_"]').textContent = 0;

  

                });
                adjust_remaining();
            }

        }

        function get_total2() {
            const rows = document.querySelectorAll('#recovery_history_2 tr');
            let totalDisbursed = 0;
            let totalRecovered = 0;
            let totalBalance = 0;
            let totalInterest = 0;
            let totalSettledInterest = 0;
            let totalSettledPrincipal = 0;
            let totalRemaining = 0;
            document.querySelectorAll('#recovery_history_2 tr').forEach(row => {
                //console.log(row.rowIndex);
                totalDisbursed += parseFloat(removeCommas(row.querySelector('#rec_dis_amnt2_' + row.rowIndex)
                    ?.innerText) || 0);
                totalRecovered += parseFloat(removeCommas(row.querySelector('#rec_rec_amnt2_' + row.rowIndex)
                    ?.innerText) || 0);
                totalBalance += parseFloat(removeCommas(row.querySelector('#rec_blnc_amnt2_' + row.rowIndex)?.innerText) ||
                    0);
                totalInterest += parseFloat(removeCommas(row.querySelector('#rec_intrest_amnt2_' + row.rowIndex)
                    ?.innerText) || 0);
                totalSettledInterest += parseFloat(removeCommas(row.querySelector('#rec_set_intrst2_' + row.rowIndex)
                    ?.innerText) ||
                    0);
                totalSettledPrincipal += parseFloat(removeCommas(row.querySelector('#rec_set_princ2_' + row.rowIndex)
                    ?.innerText) || 
                    0);
                totalRemaining += parseFloat(removeCommas(row.querySelector('#rec_remaining2_' + row.rowIndex)
                    ?.innerText) || 0);
            });

            // Display totals in the footer
            document.getElementById('rec_dis_amnt2').innerText = totalDisbursed.toFixed(2);
            document.getElementById('rec_rec_amnt2').innerText = totalRecovered.toFixed(2);
            document.getElementById('rec_blnc_amnt2').innerText = totalBalance.toFixed(2);
            document.getElementById('rec_intrest_amnt2').innerText = totalInterest.toFixed(2);
            document.getElementById('rec_set_intrst2').innerText = totalSettledInterest.toFixed(2);
            document.getElementById('rec_set_princ2').innerText = totalSettledPrincipal.toFixed(2);
            document.getElementById('rec_remaining2').innerText = totalRemaining.toFixed(2);

        }

        function get_total() {
            get_total2();
            const rows = document.querySelectorAll('#recovery_history tr');
            rows.forEach(row => {

                // Access cells for interest, settled interest, principal, settled principal, and remaining
                const settledPrincipalCell = row.querySelector('[id^="rec_set_princ_"]');
                const remainingCell = row.querySelector('[id^="rec_remaining_"]');
                const settledInterestCell = row.querySelector('[id^="rec_set_intrst_"]');
                const principalCell = row.querySelector('[id^="balance_dis_"]');
                const interestCell = row.querySelector('[id^="rec_intrest_amnt_"]');
                const recoverCell = row.querySelector('[id^="rec_rec_amnt_"]');
                const totalrecover = parseFloat(removeCommas(row.querySelector('[id^="recover_set_"]').value)) || 0;

                const balanceValue = row.querySelector('[id^="balance_dis_"]');

                const balanceCell = row.querySelector('[id^="rec_blnc_amnt_"]');



                // Parse values, ensuring each is a valid number or defaulting to 0
                let interest = parseFloat(removeCommas(interestCell.textContent)) || 0;
                let settledInterest = parseFloat(removeCommas(settledInterestCell.textContent)) || 0;
                let principal = parseFloat(removeCommas(principalCell.value)) || 0;
                let settledPrincipal = parseFloat(removeCommas(settledPrincipalCell.textContent)) || 0;


                // Ensure parsed values are numbers; if NaN, set them to 0
                if (isNaN(interest)) interest = 0;
                if (isNaN(settledInterest)) settledInterest = 0;
                if (isNaN(principal)) principal = 0;
                if (isNaN(settledPrincipal)) settledPrincipal = 0;


                let remain = (principal - settledPrincipal) + (interest - settledInterest);
                recoverCell.textContent = parseFloat(settledPrincipal + totalrecover).toFixed(2);
                remainingCell.textContent = remain.toFixed(2);

                let balance = (principal - settledPrincipal);
                balanceCell.textContent = balance.toFixed(2);


            });
            let totalDisbursed = 0;
            let totalRecovered = 0;
            let totalBalance = 0;
            let totalInterest = 0;
            let totalSettledInterest = 0;
            let totalSettledPrincipal = 0;
            let totalRemaining = 0;
            document.querySelectorAll('#recovery_history tr').forEach(row => {
                //console.log(row.rowIndex);
                totalDisbursed += parseFloat(removeCommas(row.querySelector('#rec_dis_amnt_' + row.rowIndex)
                    ?.innerText) || 0);
                totalRecovered += parseFloat(removeCommas(row.querySelector('#rec_rec_amnt_' + row.rowIndex)
                    ?.innerText) || 0);
                totalBalance += parseFloat(removeCommas(row.querySelector('#rec_blnc_amnt_' + row.rowIndex)?.innerText) ||
                    0);
                totalInterest += parseFloat(removeCommas(row.querySelector('#rec_intrest_amnt_' + row.rowIndex)
                    ?.innerText) || 0);
                totalSettledInterest += parseFloat(removeCommas(row.querySelector('#rec_set_intrst_' + row.rowIndex)
                    ?.innerText) ||
                    0);
                totalSettledPrincipal += parseFloat(removeCommas(row.querySelector('#rec_set_princ_' + row.rowIndex)
                    ?.innerText) ||
                    0);
                totalRemaining += parseFloat(removeCommas(row.querySelector('#rec_remaining_' + row.rowIndex)
                    ?.innerText) || 0);
            });

            // Display totals in the footer
            document.getElementById('rec_dis_amnt').innerText = totalDisbursed.toFixed(2);
            document.getElementById('rec_rec_amnt').innerText = totalRecovered.toFixed(2);
            document.getElementById('rec_blnc_amnt').innerText = totalBalance.toFixed(2);
            document.getElementById('rec_intrest_amnt').innerText = totalInterest.toFixed(2);
            document.getElementById('rec_set_intrst').innerText = totalSettledInterest.toFixed(2);
            document.getElementById('rec_set_princ').innerText = totalSettledPrincipal.toFixed(2);
            document.getElementById('rec_remaining').innerText = totalRemaining.toFixed(2);

        }

        function adjust_remaining() {
            get_total();
            let totalSettledInterest = parseFloat(removeCommas(document.getElementById('rec_set_intrst').textContent)) || 0;
            let totalSettledPrincipal = parseFloat(removeCommas(document.getElementById('rec_set_princ').textContent)) || 0;
            let bl = parseFloat(removeCommas(document.getElementById('balance_amount').value)) || 0;
            let int = parseFloat(removeCommas(document.getElementById('bal_intrst_amnt').value)) || 0;
            let rec = parseFloat(removeCommas(document.getElementById('rec_amnt').value)) || 0;
            let rec_int = parseFloat(removeCommas(document.getElementById('rec_intrst').value)) || 0;
            //console.log(bl-totalSettledPrincipal);
            if (bl > totalSettledPrincipal)
                document.getElementById('balance_amount_in').value = bl - totalSettledPrincipal;
            else
                document.getElementById('balance_amount_in').value = 0;
            if (int > totalSettledInterest)
                document.getElementById('bal_intrst_amnt_in').value = int - totalSettledInterest;
            else
                document.getElementById('bal_intrst_amnt_in').value = 0;

            document.getElementById('rec_amnt_in').value = rec + totalSettledPrincipal;
            document.getElementById('rec_intrst_in').value = rec_int + totalSettledInterest;
            update_table_inputs();


        }


        function revert_change() {
            const rows = document.querySelectorAll('#recovery_history tr');
            rows.forEach(row => {
                const otherRow = $('#recovery_history_2 tr');

                const rowIndex = row.rowIndex;
                // Find the interest cell with the specific ID in #recovery_history_2
                const settledPrincipalCell2 = otherRow.find(`#rec_set_princ2_${rowIndex}`);
                const settledInterestCell2 = otherRow.find(`#rec_set_intrst2_${rowIndex}`);

                const settledPrincipalCell = row.querySelector('[id^="rec_set_princ_"]');
                const settledInterestCell = row.querySelector('[id^="rec_set_intrst_"]');

                settledPrincipalCell.textContent = '0.00';
                settledInterestCell.textContent = '0.00';
                settledInterestCell2.text('0.00');
                settledPrincipalCell2.text('0.00');


            });
            adjust_remaining();
        }



        function get_account() {
            let selectedAccountNumber = "{{ $data->account_number }}";

            let bankId = $('#bank').val();
            if (bankId != "") {
                $('#account').empty().append('<option value="">Select Account Number</option>');

                if (bankId) {
                    $.ajax({
                        url: "{{ route('get.bank.details') }}", // Your route to get bank details
                        type: 'GET',
                        data: {
                            bank_id: bankId
                        },
                        success: function(response) {
                            if (response) {
                                // Loop through the account numbers and add them to the dropdown
                                $.each(response, function(index, account) {
                                    let isSelected = account.id == selectedAccountNumber ? 'selected' :
                                        '';
                                    $('#account').append('<option value="' + account.id + '" ' +
                                        isSelected + '>' + account.account_number + '</option>');
                                });
                            } else {
                                $('#account').append('<option value="">No accounts available</option>');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error fetching account numbers:', xhr);
                        }
                    });
                }

            }

        }


        function calculate_interest() {
            let payment_date = moment($('#payment_date').val()).format('D-M-YYYY');
            let applicants = parseFloat($("#applicants").val()) || 0;
            if (!applicants) {
                alert('Select Application No First.');
                return;
            }
            payment_date = moment(payment_date, 'D-M-YYYY');

            const rows = document.querySelectorAll('#recovery_history tr');
            rows.forEach(row => {

                let created_date = row.querySelector('[id^="rec_date_"]').textContent;
                created_date = moment(created_date, 'D-M-YYYY');

                const rowIndex = row.rowIndex;

                // Select the corresponding row in #recovery_history_2
                const otherRow = $('#recovery_history_2 tr');


                // Find the interest cell with the specific ID in #recovery_history_2
                const interestCell2 = otherRow.find(`#rec_intrest_amnt2_${rowIndex}`);




                const remainingCell = row.querySelector('[id^="rec_remaining_"]');
                const settledInterestCell = row.querySelector('[id^="rec_set_intrst_"]');
                const principalCell = row.querySelector('[id^="rec_dis_amnt_"]');
                const interestCell = row.querySelector('[id^="rec_intrest_amnt_"]');
                const balanceCell = row.querySelector('[id^="rec_blnc_amnt_"]');
                const statusCell = row.querySelector('[id^="recovery_status_"]');
                let interestRates = "";
                let applicants = parseFloat($("#applicants").val()) || 0;
                let exceed_days = payment_date.diff(created_date, "days");



                let tot_amn = "";
                //console.log(payment_date, created_date);
                if (statusCell.value == "partial_recover") {
                    tot_amn = parseFloat(removeCommas(balanceCell.textContent)) || 0;
                    $.ajax({
                        url: '{{ route('loan.get.RecoveryInterest') }}',
                        data: {
                            applicants: applicants,
                            exceed_days: exceed_days,
                            dis_amount: tot_amn
                        },
                        dataType: 'json',
                        success: function(response) {
                            let interestRates = response.amount;
                            interestCell.textContent = response.amount;
                            interestCell2.text(response.amount);

                            get_total();


                        },
                        error: function(xhr, status, error) {
                            console.log('AJAX Error:', status,
                                error);
                        }
                    });
                } else if (statusCell.value == "null") {
                    tot_amn = parseFloat(removeCommas(principalCell.textContent)) || 0;
                    $.ajax({
                        url: '{{ route('loan.get.RecoveryInterest') }}',
                        data: {
                            applicants: applicants,
                            exceed_days: exceed_days,
                            dis_amount: tot_amn
                        },
                        dataType: 'json',
                        success: function(response) {
                            let interestRates = response.amount;
                            interestCell.textContent = response.amount;
                            interestCell2.text(response.amount);
                            get_total();


                        },
                        error: function(xhr, status, error) {
                            console.log('AJAX Error:', status,
                                error);
                        }
                    });

                } else {
                    get_total();
                }

            });
        }


        function update_table_inputs() {

            // Clear previous inputs
            $('#recovery-add-update').find('input[name="disbursementData[]"]').remove();

            let disbursementData = [];

            $('#recovery_history tr').each(function(index) {
                let dis_id = $(`#dis_id_${index+1}`).val();
                let balance_amount = $(`#rec_blnc_amnt_${index+1}`).text();
                let recovered_amount = $(`#rec_rec_amnt_${index+1}`).text();
                let interest_amount = $(`#rec_intrest_amnt_${index+1}`).text();
                let settled_interest = $(`#rec_set_intrst_${index+1}`).text();
                let settled_principal = $(`#rec_set_princ_${index+1}`).text();
                let remaining = $(`#rec_remaining_${index+1}`).text();

                disbursementData.push({
                    dis_id,
                    balance_amount,
                    recovered_amount,
                    interest_amount,
                    settled_interest,
                    settled_principal,
                    remaining
                });

            });

            // Append disbursement data as hidden inputs
            disbursementData.forEach((data, index) => {
                Object.keys(data).forEach(key => {
                    $('#recovery-add-update').append(
                        `<input type="hidden" name="disbursementData[${index}][${key}]" value="${data[key]}">`
                    );
                });
            });
            var recover_remain = $('#rec_remaining').text();
            var set_princ = $('#rec_set_princ2').text();

            $('#recovery_remain').val(recover_remain);
            $('#current_settled').val(set_princ);

        }

        @if (session('success'))
            showToast("error", "{{ session('suuess') }}");
        @endif

        @if (session('error'))
            showToast("error", "{{ session('error') }}");
        @endif
        get_account();
        $('#recovery-add-update input, #recovery-add-update textarea').prop('readonly', true);

        // Disable all select elements
        $('#recovery-add-update select').prop('disabled', true);

        function onPostVoucherOpen(type = "not_posted") {
            resetPostVoucher();

            const apiURL = "{{ route('loan.recovery.getPostingDetails') }}";
            const remarks = $("#remarks").val();
            $.ajax({
                url: apiURL +"?book_id="+"{{ $data->book_id }}" +"&document_id="+"{{ $data->id }}"+"&remarks=" + remarks,
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
                    <td class="text-end">${voucherDetail.debit_amount > 0 ? parseFloat((voucherDetail.debit_amount)).toFixed(2) : ''}</td>
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
                    document.getElementById('voucher_date').value = moment(voucherEntries.document_date).format('D/M/Y');
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
            const documentId = "{{ $data->id }}";
            const postingApiUrl = "{{ route('loan.recovery.post') }}";
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
                    }),
                    success: function(data) {
                        const response = data.data;
                        if (response.status) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                            });
                            location.href = '/loan/recovery';
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
    </script>
@endsection
