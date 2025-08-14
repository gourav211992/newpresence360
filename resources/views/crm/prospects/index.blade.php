@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-8 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper">
                                <h2 class="content-header-title float-start mb-0">Propects</h2>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('crm.home') }}">Home</a></li>
                                    <li class="breadcrumb-item active">Propect List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-4 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                data-feather="arrow-left-circle"></i> Back</button>
                        <a href="{{ route('prospects.csv') }}{{ Request::getQueryString() ? '?' . Request::getQueryString() : '' }}"
                            class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="file-text"></i> Export to CSV</a>
                        <a href="{{ route('notes.create') }}"
                            class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i
                                data-feather="plus-square"></i> Add New</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                @include('crm.partials.table-header', [
                                    'searchPlaceholder' => 'Search by order number.',
                                ])
                                <div class="table-responsive">
                                    <table class="table myrequesttablecbox loanapplicationlist">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Customer Name</th>
                                                <th>Lead Status</th>
                                                <th>Customer Value</th>
                                                <th>Industry</th>
                                                <th>Current Supplier Split</th>
                                                <th>Last Contact Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($customers as $key => $customer)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td class="fw-bolder text-dark">
                                                        <a
                                                            href="{{ route('prospects.view', ['customerCode' => $customer->customer_code]) }}">
                                                            {{ $customer->company_name }}
                                                        </a>
                                                    </td>
                                                    <td>{{ isset($customer->meetingStatus->title) ? $customer->meetingStatus->title : '-' }}
                                                    </td>
                                                    <td>{{ $customer->sales_figure }}</td>
                                                    <td>{{ isset($customer->industry->name) ? $customer->industry->name : '-' }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $supplierSplits = $customer->supplierSplit;
                                                            $visibleSplits = $supplierSplits->take(2); // Get the first two supplier splits
                                                        @endphp
                                                        @foreach ($visibleSplits as $supplierSplit)
                                                            <span
                                                                class="badge rounded-pill badge-light-secondary badgeborder-radius">
                                                                {{ $supplierSplit->partner_name . ':' . $supplierSplit->supply_percentage }}
                                                            </span>
                                                        @endforeach
                                                        @if ($supplierSplits->isNotEmpty() && count($supplierSplits) > 2)
                                                            <a href="#" class="read-more" data-bs-toggle="modal"
                                                                data-bs-target="#supplierSplitsModal"
                                                                data-splits="{{ json_encode($supplierSplits) }}">
                                                                Read more
                                                            </a>
                                                        @endif
                                                    </td>
                                                    <td>{{ isset($customer->latestDiary->created_at) ? App\Helpers\GeneralHelper::dateFormat($customer->latestDiary->created_at) : '-' }}
                                                    </td>
                                                    <td>
                                                        <a
                                                            href="{{ route('prospects.view', ['customerCode' => $customer->customer_code]) }}">
                                                            <i data-feather="eye" class="me-50"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-danger text-center" colspan="12">No record(s) found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end mx-1 mt-50">
                                    {{-- Pagination --}}
                                    {{ $customers->appends(request()->input())->links('crm.partials.pagination') }}
                                    {{-- Pagination End --}}
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

@section('modals')
    <!-- Modal -->
    <div class="modal fade" id="supplierSplitsModal" tabindex="-1" aria-labelledby="supplierSplitsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="supplierSplitsModalLabel">Supplier Splits</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Partner Name</th>
                                <th>Supply Percentage</th>
                            </tr>
                        </thead>
                        <tbody id="supplierSplitsList">
                            <!-- Dynamic rows will be inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var readMoreLinks = document.querySelectorAll('.read-more');

            readMoreLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Get the supplier splits data from the data-splits attribute
                    var splits = JSON.parse(link.getAttribute('data-splits'));

                    // Get the modal body element where we will insert the splits
                    var modalBody = document.querySelector('#supplierSplitsList');

                    // Clear any previous content in the modal body
                    modalBody.innerHTML = '';
                    console.log(splits);

                    // Loop through the remaining splits and construct the HTML to append
                    splits.forEach(function(split) {
                        var htm = `
                    <tr>
                        <td>${split.partner_name}</td>
                        <td>${split.supply_percentage}</td>
                    </tr>
                `;

                        // Append the HTML to the modal body (assumes your modal contains a <table> with tbody)
                        modalBody.insertAdjacentHTML('beforeend', htm);
                    });
                });
            });
        });
    </script>
@endsection
