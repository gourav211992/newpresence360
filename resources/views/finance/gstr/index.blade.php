@extends('layouts.app')
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">


            <div class="content-body">

                <section id="basic-datatable">
                    <div class="card border  overflow-hidden">
                        <div class="row">
                            <div class="col-md-12 bg-light border-bottom mb-1 po-reportfileterBox">
                                <div class="row pofilterhead action-button align-items-center">
                                    <div class="col-md-8">
                                        <h3>GST 1</h3>
                                        <p>{{ App\Helpers\GeneralHelper::dateFormat2($startDate) }} to
                                            {{ App\Helpers\GeneralHelper::dateFormat2($endDate) }}</p>
                                    </div>
                                    <div
                                        class="col-md-4 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                        <div class="dropdown">
                                            <button class="btn btn-primary btn-sm mb-50 mb-sm-0 me-50 dropdown-toggle"
                                                type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i data-feather="share"></i> Export
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                <li><a class="dropdown-item"
                                                        href="{{ url('finance/gstr/json') }}{{ Request::getQueryString() ? '?' . Request::getQueryString() : '' }}">Export
                                                        to
                                                        json</a></li>
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                        data-bs-target="#exportJsonModal">Export to csv</a></li>
                                            </ul>
                                        </div>
                                        <button class="btn btn-primary btn-sm mb-50 mb-sm-0 me-50" data-bs-target="#filter"
                                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card">
                                    <div
                                        class="table-responsive trailbalnewdesfinance po-reportnewdesign trailbalnewdesfinancerightpad gsttabreporttotal">
                                        @include('finance.partials.table-header', [
                                            'searchPlaceholder' => 'Search...',
                                        ])
                                        <table class="datatables-basic table myrequesttablecbox">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Particulars</th>
                                                    <th>Vch Count</th>
                                                    <th class="text-end text-nowrap">Taxable Amount</th>
                                                    <th class="text-end">IGST</th>
                                                    <th class="text-end">CGST</th>
                                                    <th class="text-end">SGST/UTGST</th>
                                                    <th class="text-end">Cess</th>
                                                    <th class="text-end text-nowrap">Tax Amount</th>
                                                    <th class="text-end">Invoice Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $totalTaxableAmt = 0;
                                                    $totalIgst = 0;
                                                    $totalCgst = 0;
                                                    $totalSgst = 0;
                                                    $totalCess = 0;
                                                    $totalTaxAmt = 0;
                                                    $totalInvoiceAmt = 0;
                                                    $totalInvoiceCount = 0;
                                                @endphp

                                                @forelse ($gstrInvoiceTypes as $key => $item)
                                                    @php
                                                        $totalTaxableAmt += $item->taxable_amt ?? 0;
                                                        $totalIgst += $item->igst ?? 0;
                                                        $totalCgst += $item->cgst ?? 0;
                                                        $totalSgst += $item->sgst ?? 0;
                                                        $totalCess += $item->cess ?? 0;
                                                        $itemTotalTax =
                                                            ($item->igst ?? 0) +
                                                            ($item->cgst ?? 0) +
                                                            ($item->sgst ?? 0) +
                                                            ($item->cess ?? 0);
                                                        $totalTaxAmt += $itemTotalTax;
                                                        $totalInvoiceAmt += $item->invoice_amt ?? 0;
                                                        $totalInvoiceCount += $item->invoice_count ?? 0;
                                                    @endphp
                                                    <tr class="trail-bal-tabl-none">
                                                        <td>{{ $gstrInvoiceTypes->firstItem() + $key }}</td>
                                                        <td>
                                                            <div style="width: 200px">
                                                                @php
                                                                    $query = request()->except('search'); // Remove 'search' from query string
                                                                    $query = request()->except('page'); // Remove 'page' from query string
                                                                    $queryString = http_build_query($query);
                                                                @endphp
                                                                <a href="{{ url('/finance/gstr/details') . '/' . $item->id }}{{ $queryString ? '?' . $queryString : '' }}"
                                                                    class="fw-bolder text-dark">{{ strtoupper($item->type) }}</a>
                                                            </div>
                                                        </td>
                                                        <td><span
                                                                class="badge rounded-pill badge-light-secondary badgeborder-radius">{{ $item->invoice_count }}</span>
                                                        </td>
                                                        <td class="text-end">
                                                            {{ $item->taxable_amt ? number_format($item->taxable_amt, 2) : 0 }}
                                                        </td>
                                                        <td class="text-end">
                                                            {{ $item->igst ? number_format($item->igst, 2) : 0 }}</td>
                                                        <td class="text-end">
                                                            {{ $item->cgst ? number_format($item->cgst, 2) : 0 }}</td>
                                                        <td class="text-end">
                                                            {{ $item->sgst ? number_format($item->sgst, 2) : 0 }}</td>
                                                        <td class="text-end">
                                                            {{ $item->cess ? number_format($item->cess, 2) : 0 }}</td>
                                                        <td class="text-end">{{ number_format($itemTotalTax, 2) }}</td>
                                                        <td class="text-end">
                                                            {{ $item->invoice_amt ? number_format($item->invoice_amt, 2) : 0 }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center text-danger">No record(s)
                                                            found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-center">Total</td>
                                                    <td class="text-end">{{ number_format($totalTaxableAmt, 2) }}</td>
                                                    <td class="text-end">{{ number_format($totalIgst, 2) }}</td>
                                                    <td class="text-end">{{ number_format($totalCgst, 2) }}</td>
                                                    <td class="text-end">{{ number_format($totalSgst, 2) }}</td>
                                                    <td class="text-end">{{ number_format($totalCess, 2) }}</td>
                                                    <td class="text-end">{{ number_format($totalTaxAmt, 2) }}</td>
                                                    <td class="text-end">{{ number_format($totalInvoiceAmt, 2) }}</td>
                                                </tr>
                                            </tfoot>

                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end mx-1 mt-50">
                                        {{-- Pagination --}}
                                        {{ $gstrInvoiceTypes->appends(request()->input())->links('crm.partials.pagination') }}
                                        {{-- Pagination End --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
                <!-- ChartJS section end -->

            </div>
        </div>
    </div>
    <!-- END: Content-->

    <!-- BEGIN: Filter Modal -->
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0" id="filterForm">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Period</label>
                        <input type="text" id="fp-range" class="form-control flatpickr-range bg-white"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" name="date_range"
                            value="{{ request()->get('date_range') }}" />
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Group</label>
                        <select class="form-select" name="group_id">
                            <option value="" {{ request()->get('group_id') == '' ? 'selected' : '' }}>Select</option>
                            @forelse($groups as $group)
                                <option value="{{ $group->id }}"
                                    {{ request()->get('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}
                                </option>
                            @empty
                            @endforelse
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Company</label>
                        <select class="form-select" name="company_id">
                            <option value="" {{ request()->get('company_id') == '' ? 'selected' : '' }}>Select
                            </option>
                            @forelse($companies as $company)
                                <option value="{{ $company->id }}"
                                    {{ request()->get('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Organization</label>
                        <select class="form-select" name="organization_id">
                            <option value="" {{ request()->get('organization_id') == '' ? 'selected' : '' }}>Select
                            </option>
                            @forelse($organizationData as $organization)
                                <option value="{{ $organization->id }}"
                                    {{ request()->get('organization_id') == $organization->id ? 'selected' : '' }}>
                                    {{ $organization->name }}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>

                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="resetForm();">Reset</button>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Modal -->

    <!-- Export JSON Type Modal -->
    <div class="modal fade" id="exportJsonModal" tabindex="-1" aria-labelledby="exportJsonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportJsonModalLabel">Select Export Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="exportCsvForm" data-query="{{ request()->getQueryString() }}">
                        <div class="mb-3">
                            <label for="csvType" class="form-label">Choose Export Type</label>
                            <select class="form-select select2" name="type" id="csvType" required>
                                <option value="">-- Select Type --</option>
                                <option value="all">ALL</option>
                                @forelse($types as $type)
                                    <option value="{{ $type->id }}">{{ strtoupper($type->name) }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Export</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Modal -->
@endsection
@section('scripts')
    <script>
        function resetForm() {
            var form = document.getElementById('filterForm');

            // Reset the input field manually
            form.querySelector('input[name="date_range"]').value = ''; // Reset date_range field

            // Reset the select fields manually
            form.querySelector('select[name="group_id"]').value = ''; // Reset group_id field
            form.querySelector('select[name="company_id"]').value = ''; // Reset company_id field
            form.querySelector('select[name="organization_id"]').value = ''; // Reset organization_id field

            // Optionally trigger a change event for select elements (if needed)
            ['group_id', 'company_id', 'organization_id'].forEach(function(field) {
                var select = form.querySelector(`select[name="${field}"]`);
                var event = new Event('change');
                select.dispatchEvent(event);
            });
        }
    </script>
    <script>
        document.getElementById('exportCsvForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const type = document.getElementById('csvType').value;
            const query = this.dataset.query;

            if (type) {
                let url = `{{ url('finance/gstr/detail/csv') }}/${type}`;
                if (query) {
                    url += `?${query}`;
                }
                window.location.href = url;
            }
        });
    </script>
@endsection
