@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h4 class="content-header-title float-start mb-0 border-0">
                                {{ @$bank->bankInfo->bank_name }}:
                                <strong>{{ @$bank->ledger->name }}({{ $bank->account_number }})</strong>
                            </h4>

                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">

                    <div class="form-group breadcrumb-right">
                        <a href="{{ route('bank.statements.upload', ['id' => $bank->id]) }}{{ request()->has('date') ? '?date=' . request()->get('date') : '' }}"
                            class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</a>

                        <a class="btn btn-primary btn-sm mb-50 mb-sm-0 me-50 dropdown-toggle"
                            href="{{ route('bank.statements.export', ['id' => $bank->id, 'type' => request()->type, 'batch_uid' => request()->batch_uid]) }}">
                            <i data-feather="share"></i> Export
                        </a>
                        <a class="btn btn-success btn-sm mb-50 mb-sm-0"
                            href="{{ route('bank.statements.match-entries', ['id' => $bank->id]) }}{{ request()->has('date') ? '?date=' . request()->get('date') : '' }}"><i
                                data-feather="check-circle"></i> Match
                            Statement</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row justify-content-center">
                        <div class="col-md-12 mt-3 col-12">
                            <div class="card  new-cardbox">
                                <ul class="nav nav-tabs border-bottom" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request('type') == 'success-statement' || !request('type') ? 'active' : '' }}"
                                            href="{{ route('bank.statements.upload', ['id' => $bank->id, 'type' => 'success-statement', 'batch_uid' => request()->batch_uid]) }}">Records
                                            Succeded &nbsp;<span>({{ $successCount }})</span></a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request('type') == 'failed-statement' ? 'active' : '' }}"
                                            href="{{ route('bank.statements.upload', ['id' => $bank->id, 'type' => 'failed-statement', 'batch_uid' => request()->batch_uid]) }}">Records
                                            Failed
                                            &nbsp;<span>({{ $failureCount }})</span></a>
                                    </li>
                                </ul>
                                <div class="tab-content">
                                    <div class="tab-pane active" id="Succeded">
                                        <div class="table-responsive candidates-tables">
                                            <table
                                                class="datatables-basic table table-striped myrequesttablecbox loanapplicationlist">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Date</th>
                                                        <th>Narration</th>
                                                        <th>Chq/Ref No</th>
                                                        <th>Debit Amount</th>
                                                        <th>Credit Amount</th>
                                                        <th>Balance</th>
                                                        <th>Remarks</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($statements as $statement)
                                                        <tr>
                                                            <td>{{ $statements->firstItem() + $loop->index }}</td>
                                                            <td class="fw-bolder text-dark">
                                                                {{ $statement->date ? App\Helpers\CommonHelper::dateFormat($statement->date) : '' }}
                                                            </td>
                                                            <td>{{ $statement->narration }}</td>
                                                            <td>{{ $statement->ref_no }}</td>
                                                            <td>{{ $statement->debit_amt }}</td>
                                                            <td>{{ $statement->credit_amt }}</td>
                                                            <td><span
                                                                    class="badge rounded-pill badge-light-secondary badgeborder-radius">{{ $statement->balance }}</span>
                                                            </td>
                                                            @if (request('type') == 'failed-statement')
                                                                <td class="text-danger">
                                                                    {{ $statement->errors }}
                                                                </td>
                                                            @else
                                                                <td class="text-success">
                                                                    Success
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @empty
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        {{ $statements->appends(request()->input())->links('recruitment.partials.pagination') }}

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>
@endsection
