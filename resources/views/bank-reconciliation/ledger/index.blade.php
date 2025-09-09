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
                            <h2 class="content-header-title float-start mb-0">Bank Reconciliation</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                    <li class="breadcrumb-item active">Bank List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-12">
                                            <div class="newheader">
                                                <div>
                                                    <h4 class="card-title text-theme text-dark" id="company_name"></h4>
                                                    <p class="card-text">{{ $dateRange }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    @include('recruitment.partials.card-header')
                                    <table class="datatables-basic table myrequesttablecbox ">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Ledger NAme</th>
                                                <th>Bank NAme</th>
                                                <th>A/C No.</th>
                                                <th class="text-end">Opening Balance</th>
                                                <th class="text-end">Debit</th>
                                                <th class="text-end">Credit</th>
                                                <th class="text-end">Closing Balance</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @forelse($data as $ledger)
                                                <tr>
                                                    <td>{{ $data->firstItem() + $loop->index }}</td>
                                                    <td class="fw-bolder text-dark">{{ $ledger->name }}</td>
                                                    <td class="fw-bolder text-dark">{{ $ledger->bank_name }}</td>
                                                    <td>{{ $ledger->account_number }}</td>
                                                    <td class="text-end">
                                                        {{ number_format(abs($ledger->opening), 2) }}
                                                        {{ $ledger->opening >= 0 ? 'Dr' : 'Cr' }}
                                                    </td>
                                                    <td class="text-end">{{ number_format($ledger->debit_amount, 2) }}
                                                    </td>
                                                    <td class="text-end">{{ number_format($ledger->credit_amount, 2) }}
                                                    </td>
                                                    <td class="text-end">
                                                        {{ number_format(abs($ledger->closing), 2) }}
                                                        {{ $ledger->closing >= 0 ? 'Dr' : 'Cr' }}
                                                    </td>
                                                    <td><a href="{{ route('bank.statements.upload', ['id' => $ledger->account_id]) }}{{ request()->has('date') ? '?date=' . request()->get('date') : '' }}"
                                                            class="btn btn-outline-primary btn-sm font-small-2"><i
                                                                data-feather="upload"></i> Upload Bank Statement</a></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-danger text-center fw-bold" colspan="6">No record(s)
                                                        found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                    </table>
                                </div>
                                {{-- Pagination --}}
                                {{ $data->appends(request()->input())->links('recruitment.partials.pagination') }}
                                {{-- Pagination End --}}
                            </div>
                        </div>
                    </div>
                </section>


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
                    <div class="mb-1">
                        <label class="form-label">Organization</label>
                        <select class="form-select select2" name="organization_id" id="organization_id">
                            <option value="">Select</option>
                            @forelse ($mappedOrganizations as $organization)
                                <option value="{{ $organization->id }}"
                                    {{ $organization->id == (request('organization_id') ? request('organization_id') : $authUser->organization_id) ? 'selected' : '' }}>
                                    {{ $organization->name }}
                                </option>

                            @empty
                            @endforelse
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Ledger Name</label>
                        <select class="form-select" name="ledger_id">
                            <option value="">Select</option>
                            @forelse($ledgers as $ledger)
                                <option value="{{ $ledger->id }}"
                                    {{ request('ledger_id') == $ledger->id ? 'selected' : '' }}>
                                    {{ $ledger->name }}</option>
                            @empty
                            @endforelse
                        </select>
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
{{-- @section('scripts')
    <script>
        $(document).ready(function() {
            $('#company_name').text($('#organization_id option:selected').text());
        });
    </script>
@endsection --}}
