@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Reconcile Report</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('bank.ledgers.index') }}">Finance</a></li>
                                    <li class="breadcrumb-item active">Reconcile View</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <a href="{{ route('bank.statements.upload', ['id' => $bank->id]) }}{{ request()->has('date') ? '?date=' . request()->get('date') : '' }}"
                            class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</a>
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                        <a href="{{ route('bank.reconcile.index', ['id' => $bank->id]) }}"
                            class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Reconcile
                            Now</a>
                    </div>
                </div>


            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-md-12">

                        <div class="card overflow-hidden">

                            <div class="row">
                                <div class="col-md-12 bg-light border-bottom po-reportfileterBox">
                                    <div class="pofilterhead newheader">
                                        <h4 class="card-title text-theme text-dark">{{ @$bank->bankInfo->bank_name }}:
                                            <strong>{{ @$bank->ledger->name }}({{ $bank->account_number }})</strong>
                                        </h4>
                                        <p class="card-text">{{ $dateRange }}</p>
                                    </div>

                                </div>

                            </div>

                            <div class="card-body">
                                <div class=" ">
                                    <div class="step-custhomapp bg-light">
                                        <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link {{ \Request::route()->getName() == 'bank.statements.match-entries' ? 'active' : '' }}"
                                                    href="{{ route('bank.statements.match-entries', ['id' => $bank->id]) }}{{ request()->has('date') ? '?date=' . request()->get('date') : '' }}">Match
                                                    Enteries</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link {{ \Request::route()->getName() == 'bank.statements.not-match-entries' ? 'active' : '' }}"
                                                    href="{{ route('bank.statements.not-match-entries', ['id' => $bank->id]) }}{{ request()->has('date') ? '?date=' . request()->get('date') : '' }}">Not
                                                    Match
                                                    Enteries</a>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="tab-content ">
                                        <div class="tab-pane active" id="Match">
                                            <div
                                                class="earn-dedtable flex-column d-flex trail-balancefinance leadger-balancefinance trailbalnewdesfinance mt-0">
                                                @include('recruitment.partials.card-header')

                                                <div class="table-responsive">
                                                    <table class="table border">
                                                        <thead>
                                                            <tr>
                                                                <th>Date</th>
                                                                <th>Particulars</th>
                                                                <th>Vch. Type</th>
                                                                <th>Vch. No.</th>
                                                                <th class="text-end">Debit</th>
                                                                <th class="text-end">Credit</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse ($vouchers as $voucher)
                                                                <tr>
                                                                    <td>
                                                                        {{ $voucher->document_date ? App\Helpers\CommonHelper::dateFormat($voucher->document_date) : '' }}
                                                                    </td>
                                                                    <td>{{ $voucher->party_name ? $voucher->party_name : '-' }}
                                                                    </td>
                                                                    <td>{{ $voucher->book_code }}</td>
                                                                    <td>{{ $voucher->voucher_no }}</td>
                                                                    <td class="text-end">
                                                                        {{ number_format($voucher->debit_amt_org, 2) }}
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ number_format($voucher->credit_amt_org, 2) }}
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td class="text-danger text-center fw-bold"
                                                                        colspan="6">No record(s)
                                                                        found.
                                                                    </td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                                {{-- Pagination --}}
                                                {{ $vouchers->appends(request()->input())->links('recruitment.partials.pagination') }}
                                                {{-- Pagination End --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- END: Content-->
    <!-- BEGIN: Modal-->
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Period</label>
                        <input type="text" id="fp-range" class="form-control flatpickr-range bg-white"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD"
                            value="{{ request('date') ? request('date') : $dateRange }}" name="date" />
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Modal-->
@endsection
