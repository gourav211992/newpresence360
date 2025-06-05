@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <form class="form" role="post-data" method="POST" action="{{ route('bank.reconcile.save-date') }}"
                redirect="{{ route('bank.reconcile.index', ['id' => $bank->id]) }}" autocomplete="off">
                @csrf
                <div class="content-header row">
                    <div class="content-header-left col-md-6 col-12 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Bank Reconciliation</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('bank.ledgers.index') }}">Finance</a>
                                        </li>
                                        <li class="breadcrumb-item active">Reconcile View</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <button type="button" class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                                data-bs-toggle="modal"><i data-feather="filter"></i> Select Date Range</button>
                            <a href="{{ route('bank.statements.not-match-entries', ['id' => $bank->id]) }}"
                                class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
                                Back</a>
                            <button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0" data-request="ajax-submit"
                                data-target="[role=post-data]"><i data-feather="check-circle"></i> Submit</button>
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
                                                <strong>{{ @$bank->ledger->name }}</strong>
                                            </h4>
                                            <p class="card-text">{{ $dateRange }}</p>
                                        </div>

                                    </div>

                                </div>
                                <div class="card-body px-0">
                                    <div
                                        class="earn-dedtable flex-column d-flex trail-balancefinance leadger-balancefinance trailbalnewdesfinance mt-0 ">
                                        <div class="table-responsive">
                                            <table class="table border">
                                                <thead>
                                                    <tr>
                                                        <th width="100px">Date</th>
                                                        <th>Particulars</th>
                                                        <th>Vch. Type</th>
                                                        <th>Transaction Type</th>
                                                        <th>Reference No.</th>
                                                        <th>Payment Date</th>
                                                        <th width="100px">Bank Date</th>
                                                        <th class="text-end">Debit</th>
                                                        <th class="text-end">Credit</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="">
                                                    <tr>
                                                        <td id="bank-date-error" colspan="9"></td>
                                                    </tr>
                                                    @forelse ($vouchers as $voucher)
                                                        <tr>
                                                            <td>{{ \Carbon\Carbon::parse($voucher->document_date)->format('d-m-Y') }}
                                                            </td>
                                                            <td>{{ $voucher->party_name }}</td>
                                                            <td><span
                                                                    class="badge badge-light-secondary">{{ ucfirst(strtolower($voucher->reference_service)) }}</span>
                                                            </td>
                                                            <td>{{ $voucher->payment_mode }}</td>
                                                            <td>{{ $voucher->reference_no }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($voucher->payment_date)->format('d-m-Y') }}
                                                            </td>
                                                            <td>
                                                                <input type="date" class="form-control font-small-2"
                                                                    name="bank_date[{{ $voucher->id }}]" />
                                                            </td>
                                                            <td class="text-end">
                                                                {{ $voucher->debit_amt_org ? number_format($voucher->debit_amt_org, 2) : 0 }}
                                                            </td>
                                                            <td class="text-end">
                                                                {{ $voucher->credit_amt_org ? number_format($voucher->credit_amt_org, 2) : 0 }}
                                                            </td>
                                                        </tr>
                                                    @empty
                                                    @endforelse
                                                </tbody>

                                                <tfoot>
                                                    <tr>
                                                        <td colspan="6" class="text-end">Bal. as per Company Books</td>
                                                        <td>&nbsp;</td>
                                                        <td colspan="2">{{ number_format($companyBookBalance, 2) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6" class="text-end">Amt. not reflected in Bank</td>
                                                        <td>&nbsp;</td>
                                                        <td>{{ number_format($unreflectedDr, 2) }}</td>
                                                        <td>{{ number_format($unreflectedCr, 2) }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="6" class="text-end">Balance as per Bank</td>
                                                        <td>&nbsp;</td>
                                                        <td colspan="2">{{ number_format($bankBalance, 2) }}</td>
                                                    </tr>
                                                </tfoot>


                                            </table>
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
    <!-- END: Content-->
    <!-- BEGIN: Modal-->
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Select Date Range</h5>
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
@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>
    <script>
        $(document).ready(function() {
            let dateParam = "{{ request('date') }}";

            if (!dateParam) {
                // Show the modal if there's no date in the query string
                $('#filter').modal({
                    backdrop: 'static',
                    keyboard: false
                }).modal('show');
            }
        });
    </script>
@endsection
