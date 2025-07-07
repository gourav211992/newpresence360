@extends('layouts.app')
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <div class="content-body">
                <section id="basic-datatable">
                    <div class="card border  overflow-hidden">
                        <div class="row">
                            <div class="col-md-12 bg-light border-bottom mb-1 po-reportfileterBox">
                                <div class="row pofilterhead action-button align-items-center">
                                    <div class="col-md-4">
                                        <h3>Cashflow Statement</h3>
                                        <p class="my-25">{{ $fy }}</p>
                                    </div>
                                    <div
                                        class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                        <a id="printButton"    data-url="{{ route('finance.cashflow', 'print') }}?range={{ $range }}&location_id={{ $location_id }}&cost_center_id={{ $cost_center_id }}&cost_group_id={{ $cost_group_id }}" class="btn btn-dark btn-sm mb-50 mb-sm-0 me-25"><i data-feather='printer'></i> Print</a>

                                        <button data-bs-toggle="modal" data-bs-target="#filter"
                                            class="btn btn-warning btn-sm mb-50 mb-sm-0 me-25"><i data-feather="filter"></i>
                                            Filter</button>
                                        <a data-bs-toggle="modal" data-bs-target="#addcoulmn"
                                            class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="mail"></i>
                                            Schedule Mail</a>
                                    </div>
                                    <div class="col-md-12">
                                        {{-- <p class="fw-normal font-small-3 badge bg-light-danger mt-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#errorenteries"> Error Entries: <strong>10</strong></p> --}}
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-12">
                                <div
                                    class="table-responsive trailbalnewdesfinance po-reportnewdesign gsttabreporttotal  trail-balancefinance">
                                    <table class="datatables-basic table myrequesttablecbox ">
                                        <thead>
                                            <tr>
                                                <th style="width: 100px">#</th>
                                                <th style="width: 700px">Particulars</th>
                                                <th style="width: 100px" class="text-end">Total Amount</th>
                                                <th class="text-end">&nbsp;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td></td>
                                                <td class="fw-bolder text-primary">
                                                    Opening Balance
                                                </td>
                                                <td class="text-end"><span class="font-small-4 text-primary fw-bolder">
                                                        @if ($opening < 0)
                                                            ({{ number_format(abs($opening), 2) }})
                                                        @else
                                                            {{ number_format($opening, 2) }}
                                                        @endif
                                                    </span></td>
                                                <td class="text-end">&nbsp;</td>
                                            </tr>

                                            <tr class="trail-bal-tabl-none">
                                                <td class="clickopentr">
                                                    <a href="#" class="open-job-sectab" style="display: none"><i
                                                            data-feather="plus-circle"></i></a>
                                                    <a href="#" class="close-job-sectab text-danger"><i
                                                            data-feather="minus-circle"></i></a>
                                                </td>
                                                <td class="fw-bolder text-dark">
                                                    Payment Made
                                                </td>
                                                <td class="text-end">({{ number_format($payment_made_t, 2) }})</td>
                                                <td class="text-end">&nbsp;</td>
                                            </tr>

                                            <tr class="shojpbdescrp">
                                                <td></td>
                                                <td colspan="1">
                                                    <div class="table-responsive" style="max-height: 300px">
                                                        <table class="table ledgersub-detailsnew cashflowsub-detailsnew">
                                                            <thead class="bg-white border-bottom">
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Vouch. No.</th>
                                                                    <th>Date</th>
                                                                    <th>Ledger Name</th>
                                                                    <th>Payment Mode</th>
                                                                    <th>Bank Name</th>
                                                                    <th>Amt.</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                            @foreach ($payment_made as $index => $paym)
                                                                <tr>
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <td>{{ $paym->voucher_no }}</td>
                                                                    <td>{{ \Carbon\Carbon::parse($paym->document_date)->format('d-m-Y') }}
                                                                    </td>
                                                                    <td>{{ $paym->ledger_name }}</td>
                                                                    <td>{{ $paym->payment_mode }}</td>
                                                                    <td>{{ $paym->bank_name ?? "-" }}</td>
                                                                    <td>{{ number_format($paym->amount, 2) }}</td>
                                                                    <td><a href="{{ route('vouchers.edit', [$paym->voucher_id]) }}"
                                                                            class="text-primary"><i
                                                                                data-feather="eye"></i></a></td>
                                                                </tr>
                                                            @endforeach
                                                        </table>
                                                    </div>
                                                </td>
                                                <td></td>
                                                <td></td>
                                            </tr>

                                            <tr class="trail-bal-tabl-none">
                                                <td class="clickopentr">
                                                    <a href="#" class="open-job-sectab" style="display: none"><i
                                                            data-feather="plus-circle"></i></a>
                                                    <a href="#" class="close-job-sectab text-danger"><i
                                                            data-feather="minus-circle"></i></a>
                                                </td>
                                                <td class="fw-bolder text-dark">
                                                    Payment Received
                                                </td>
                                                <td class="text-end">{{ number_format($payment_received_t, 2) }}</td>
                                                <td class="text-end">&nbsp;</td>
                                            </tr>

                                            <tr class="shojpbdescrp">
                                                <td></td>
                                                <td colspan="1">
                                                    <div class="table-responsive" style="max-height: 300px">
                                                        <table class="table ledgersub-detailsnew cashflowsub-detailsnew">
                                                            <thead class="bg-white border-bottom">
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Vouch. No.</th>
                                                                    <th>Date</th>
                                                                    <th>Ledger Name</th>
                                                                    <th>Payment Mode</th>
                                                                    <th>Bank Name</th>
                                                                    <th>Amt.</th>
                                                                    <th></th>
                                                                </tr>
                                                            </thead>
                                                            @foreach ($payment_received as $index => $pay)
                                                                <tr>
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <td>{{ $pay->voucher_no }}</td>
                                                                    <td>{{ \Carbon\Carbon::parse($pay->document_date)->format('d-m-Y') }}
                                                                    </td>
                                                                    <td>{{ $pay->ledger_name }}</td>
                                                                    <td>{{ $pay->payment_mode }}</td>
                                                                    <td>{{ $pay->bank_name }}</td>
                                                                    <td>{{ number_format($pay->amount, 2) }}</td>
                                                                    <td><a href="{{ route('vouchers.edit', [$pay->voucher_id]) }}"
                                                                            class="text-primary"><i
                                                                                data-feather="eye"></i></a></td>
                                                                </tr>
                                                            @endforeach
                                                        </table>
                                                    </div>
                                                </td>
                                                <td></td>
                                                <td></td>
                                            </tr>

                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                <td class="fw-bolder text-primary"> Closing Balance </td>
                                                <td class="text-end"><span class="font-small-4 text-primary fw-bolder">
                                                        @if ($closing < 0)
                                                            ({{ number_format(abs($closing), 2) }})
                                                        @else
                                                            {{ number_format($closing, 2) }}
                                                        @endif
                                                    </span></td>
                                                <td class="text-end">&nbsp;</td>
                                            </tr>
                                        </tfoot>


                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
                <!-- ChartJS section end -->
                <div class="modal fade text-start filterpopuplabel " id="addcoulmn" aria-labelledby="myModalLabel17" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Send
                                        Cashflow Statement</h4>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-12">
                                         <div class="compoenentboxreport advanced-filterpopup customernewsection-form mb-1">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-check ps-0">
                                                        <label class="form-check-label">Add Scheduler</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row camparboxnewcen mt-1">
                                                <div class="col-md-8 mb-1">
                                                    <label class="form-label">To <label class="text-danger">*</label></label>
                                                    <select class="form-select select2" id="to" name="to" multiple>
                                                        @foreach($users as $to)
                                                            <option value="{{ $to->id }}" @if($scheduler?->toable_id==$to->id) selected @endif>{{ $to->email }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-8 mb-1">
                                                    <label class="form-label">CC <label class="text-danger">*</label></label>
                                                    @php
                                                        $selectedCc = $scheduler && $scheduler?->cc
                                                            ? json_decode($scheduler?->cc, true)
                                                            : [App\Helpers\Helper::getAuthenticatedUser()->auth_user_id];
                                                    @endphp

                                                    <select class="form-select select2" name="cc" multiple>
                                                        <option disabled>Select</option>
                                                        @foreach($users as $cc)
                                                            <option value="{{ $cc->id }}" {{ in_array($cc->id, $selectedCc) ? 'selected' : '' }}>
                                                                {{ $cc->email }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                </div>
                                             </div>
                                             <div class="row camparboxnewcen">
                                                @php
                                                    $selectedType = old('type', $scheduler?->type ?? '');
                                                @endphp

                                                <div class="col-md-4">
                                                    <label class="form-label">Type <label class="text-danger">*</label></label>
                                                    <select class="form-select" name="type" id="type" required>
                                                        <option value="">Select</option>
                                                        <option value="daily" {{ $selectedType == 'daily' ? 'selected' : '' }}>Daily</option>
                                                        <option value="weekly" {{ $selectedType == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                        <option value="monthly" {{ $selectedType == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                    </select>
                                                </div>


                                                <div class="col-md-4">
                                                    <label class="form-label">Select Date <label class="text-danger">*</label></label>
                                                    <input
                                                        type="datetime-local"
                                                        class="form-select"
                                                        name="date"
                                                        min="{{ now()->format('Y-m-d\TH:i') }}"
                                                        value="{{ old('date', isset($scheduler) ? \Carbon\Carbon::parse($scheduler?->date)->format('Y-m-d\TH:i') : '') }}"
                                                        required
                                                    />
                                                </div>

                                                <div class="col-md-12">
                                                    <label class="form-label">Remarks  <label class="text-danger">*</label></label>
                                                    <textarea
                                                        class="form-control"
                                                        placeholder="Enter Remarks"
                                                        id="remarks"
                                                        name="remarks"
                                                        required
                                                    >{{ old('remarks', $scheduler?->remarks ?? '') }}</textarea>
                                                </div>




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
                <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
                    <div class="modal-dialog sidebar-sm">
                        <!-- Assuming the form is in the same Blade view -->
                        <form class="add-new-record modal-content pt-0" method="GET" action="{{ route('finance.cashflow') }}">
                            <div class="modal-header mb-1">
                                <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                            </div>
                            <div class="modal-body flex-grow-1">
                                <div class="mb-1">
                                    <label class="form-label" for="fp-range">Select Date Range</label>
                                    <input type="text" id="fp-range" name="date" class="form-control bg-whiteform-control flatpickr-range bg-white flatpickr-input" required
                                        placeholder="DD-MM-YYYY to DD-MM-YYYY" value="{{ $range }}" />
                                </div>
                                <div class="mb-1">
                                    <label class="form-label" for="organization">Organization</label>
                              <select id="organization_id" name="organization_id" class="form-select select2" required>
                                    <option value="" disabled>Select</option>

                                    @foreach ($mappings as $organization)
                                    <option value="{{ $organization->organization->id }}"
                                        {{ $organization->organization->id == $organization_id ? 'selected' : '' }}>
                                        {{ $organization->organization->name }}
                                    </option>
                                @endforeach
                                </select>
                            </div>
                            <div class="mb-1">
                                    <label class="form-label">Location</label>
                                    <select id="location_id" name="location_id" class="form-select select2">
                                    </select>
                                </div>
                                 <div class="mb-1">
                                    <label class="form-label">Cost Group</label>
                                    <select id="cost_group_id" class="form-select select2" name="cost_group_id">
                                    </select>
                                </div>
                                <div class="mb-1">
                                    <label class="form-label">Cost Center</label>
                                    <select id="cost_center_id" class="form-select select2"
                                        name="cost_center_id">
                                    </select>
                                </div>
                                <div class="modal-footer justify-content-start">
                                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                        </form>


                    </div>
                </div>
                <div class="modal fade text-start filterpopuplabel " id="errorenteries"
                    aria-labelledby="myModalLabel17" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Error in
                                        Voucher Enteries</h4>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive candidates-tables">
                                    <table class="table border table-striped myrequesttablecbox loanapplicationlist">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th>Vouch. No.</th>
                                                <th>Error</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td>09-04-2025</td>
                                                <td class="fw-bolder text-dark">PV001</td>
                                                <td class="text-danger">Multiple Entry</td>
                                                <td><a href="#" class="text-primary"><i data-feather="eye"></i></a></td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td>09-04-2025</td>
                                                <td class="fw-bolder text-dark">PV002</td>
                                                <td class="text-danger">Multiple Entry</td>
                                                <td><a href="#" class="text-primary"><i data-feather="eye"></i></a></td>
                                            </tr>
                                            <tr>
                                                <td>3</td>
                                                <td>09-04-2025</td>
                                                <td class="fw-bolder text-dark">PV003</td>
                                                <td class="text-danger">Multiple Entry</td>
                                                <td><a href="#" class="text-primary"><i data-feather="eye"></i></a></td>
                                            </tr>
                                            <tr>
                                                <td>4</td>
                                                <td>09-04-2025</td>
                                                <td class="fw-bolder text-dark">PV004</td>
                                                <td class="text-danger">Multiple Entry</td>
                                                <td><a href="#" class="text-primary"><i data-feather="eye"></i></a></td>
                                            </tr>
                                            <tr>
                                                <td>5</td>
                                                <td>09-04-2025</td>
                                                <td class="fw-bolder text-dark">PV005</td>
                                                <td class="text-danger">Multiple Entry</td>
                                                <td><a href="#" class="text-primary"><i data-feather="eye"></i></a></td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
          </div>
        </div>

    </div>
    {{-- for customized excel --}}
    <form id="customExcelExportForm"  action="{{ route('cashflow.export') }}" method="POST">
        @csrf
        <input type="hidden" name="opening" value="{{ $opening }}">
        <input type="hidden" name="closing" value="{{ $closing }}">
        <input type="hidden" name="fy" value="{{ $fy }}">
        <input type="hidden" name="organization_id" value="{{ $organization_id }}">

        <!-- Serialize complex arrays to JSON -->
        <input type="hidden" name="payment_made" value="{{ json_encode($payment_made) }}">
        <input type="hidden" name="payment_made_t" value="{{ $payment_made_t }}">
        <input type="hidden" name="payment_received" value="{{ json_encode($payment_received) }}">
        <input type="hidden" name="payment_received_t" value="{{ $payment_received_t }}">

        {{-- <button type="submit" class="btn btn-success">
            <i data-feather="file-text"></i> Export Excel
        </button> --}}
    </form>
</div>
    @endsection
@section('scripts')
<script>
    const locations = @json($locations);
    const costCenters = @json($cost_centers);
    const costGroups = @json($cost_groups);
</script>
    <!-- BEGIN: Dashboard Custom Code JS-->
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
        const printButton = document.getElementById("printButton");

            if (printButton) {
                printButton.addEventListener("click", function (e) {
                    e.preventDefault();
                    $('.preloader').show();

                    const url = printButton.getAttribute("data-url");

                    $.ajax({
                        url: url,
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function () {
                        $('.preloader').hide();
                            window.open(url, '_blank');
                        },
                        error: function (xhr) {
                            $('.preloader').hide();
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
                });
            }
        });
        $(window).on('load', function() {
            $('.preloader').css('display', 'flex');
            $('.preloader').fadeOut();
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })

        // Failsafe in case load never triggers (e.g. network failure or redirect)
        setTimeout(function () {
            $('.preloader').fadeOut();
        }, 15000);

        $(function() {
            $(".sortable").sortable();
        });


        $(function() {
            $(".ledgerselecct").autocomplete({
                source: [
                    "Furniture (IT001)",
                    "Chair (IT002)",
                    "Table (IT003)",
                    "Laptop (IT004)",
                    "Bags (IT005)",
                ],
                minLength: 0
            }).focus(function() {
                if (this.value == "") {
                    $(this).autocomplete("search");
                }
            });
        });

    $(function () {
        var dt_basic_table = $('.datatables-basic'),
            dt_date_table = $('.dt-date');

        // DataTable with buttons
        if (dt_basic_table.length) {
            var dt_basic = dt_basic_table.DataTable({
                order: [[0, 'asc']],
                ordering: false,
                dom:
                    '<"d-flex justify-content-between align-items-center mx-2 row"' +
                    '<"col-sm-12 col-md-3"l>' +
                    '<"col-sm-12 col-md-6 withoutheadbuttin dt-action-buttons text-end pe-0"B>' +
                    '<"col-sm-12 col-md-3"f>>' +
                    't' +
                    '<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                displayLength: 8,
                lengthMenu: [8, 10, 25, 50, 75, 100],
                buttons: [
                    {
                        extend: 'excel',
                                text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
                                className: 'btn btn-outline-secondary',
                                action: function (e, dt, node, config) {
                                        document.getElementById('customExcelExportForm').submit();
                                    }
                       ,
                        init: function (api, node, config) {
                            $(node).removeClass('btn-secondary');
                            $(node).parent().removeClass('btn-group');
                            setTimeout(function () {
                                $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                            }, 50);
                        }
                    }
                ],
                // buttons: [
                //     {
                //         extend: 'collection',
                //         className: 'btn btn-outline-secondary dropdown-toggle',
                //         text: feather.icons['share'].toSvg({ class: 'font-small-3 me-50' }) + 'Export',
                //         buttons: [
                //             {
                //                 extend: 'excel',
                //                 text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + 'Excel',
                //                 className: 'dropdown-item',
                //                 // exportOptions: {
                //                 //     columns: [0, 1, 2] // Adjusted to match your table: #, Particulars, Total Amount
                //                 // }
                //                 action: function (e, dt, node, config) {
                //                         document.getElementById('customExcelExportForm').submit();
                //                     }
                //             },
                //             // {
                //             //     extend: 'pdf',
                //             //     text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + 'PDF',
                //             //     className: 'dropdown-item',
                //             //     exportOptions: {
                //             //         columns: [0, 1, 2]
                //             //     }
                //             // },
                //             // {
                //             //     extend: 'copy',
                //             //     text: feather.icons['mail'].toSvg({ class: 'font-small-4 me-50' }) + 'Copy',
                //             //     className: 'dropdown-item',
                //             //     exportOptions: {
                //             //         columns: [0, 1, 2]
                //             //     }
                //             // }
                //         ],
                //         init: function (api, node, config) {
                //             $(node).removeClass('btn-secondary');
                //             $(node).parent().removeClass('btn-group');
                //             setTimeout(function () {
                //                 $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                //             }, 50);
                //         }
                //     }
                // ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search...',
                    paginate: {
                        previous: '&nbsp;',
                        next: '&nbsp;'
                    }
                }
            });

            $('div.head-label').html('<h6 class="mb-0">Event List</h6>');
        }

        // Flatpickr for date filtering if needed
        if (dt_date_table.length) {
            dt_date_table.flatpickr({
                monthSelectorType: 'static',
                dateFormat: 'm/d/Y'
            });
        }
    });
     function updateLocationsDropdown(selectedOrgId) {
        console.log(selectedOrgId,'selected')
        const filteredLocations = locations.filter(loc =>
            String(loc.organization_id) === String(selectedOrgId)
        );

        const $locationDropdown = $('#location_id');
        $locationDropdown.empty().append('<option value="">Select</option>');
        const selectedLocationId = "{{ $location_id }}";


        filteredLocations.forEach(loc => {
        const isSelected = String(loc.id) === String(selectedLocationId) ? 'selected' : '';
        $locationDropdown.append(`<option value="${loc.id}" ${isSelected}>${loc.store_name}</option>`);
        });

        $locationDropdown.trigger('change');
    }
    function loadCostGroupsByLocation(locationId) {
        const filteredCenters = costCenters.filter(center => {
            if (!center.location) return false;
            const locationArray = Array.isArray(center.location)
                ? center.location.flatMap(loc => loc.split(','))
                : [];
            return locationArray.includes(String(locationId));
        });

        const costGroupIds = [...new Set(filteredCenters.map(center => center.cost_group_id))];
        
        const filteredGroups = costGroups.filter(group => costGroupIds.includes(group.id));
        console.log(filteredCenters,costGroupIds,filteredGroups);

        const $groupDropdown = $('#cost_group_id');
        $groupDropdown.empty().append('<option value="">Select Cost Group</option>');

        filteredGroups.forEach(group => {
            $groupDropdown.append(`<option value="${group.id}">${group.name}</option>`);
        });

        $('#cost_group_id').trigger('change');
    }

    function loadCostCentersByGroup(locationId, groupId) {
        const costCenter = $('#cost_center_id');
        costCenter.empty();

        const filteredCenters = costCenters.filter(center => center.cost_group_id === groupId);

        if (filteredCenters.length === 0) {
            costCenter.prop('required', false);
            $('#cost_center_id').hide();
        } else {
            costCenter.append('<option value="">Select Cost Center</option>');
            $('#cost_center_id').show();

            filteredCenters.forEach(center => {
                costCenter.append(`<option value="${center.id}">${center.name}</option>`);
            });
        }
        costCenter.val(@json(request('cost_center_id')) || "");
        costCenter.trigger('change');
    }

    $(document).ready(function() {
       const preselectedOrgId = $('#organization_id').val();
    const preselectedLocationId = "{{ $location_id }}";
    const preselectedGroupId = "{{ $cost_group_id }}";
    const preselectedCenterId = @json($cost_center_id);

    if (preselectedOrgId) {
        updateLocationsDropdown(preselectedOrgId);
    }

    if (preselectedLocationId) {
        // Load Cost Groups and then continue
        const filteredCenters = costCenters.filter(center => {
            if (!center.location) return false;
            const locationArray = Array.isArray(center.location)
                ? center.location.flatMap(loc => loc.split(','))
                : [];
            return locationArray.includes(String(preselectedLocationId));
        });

        const costGroupIds = [...new Set(filteredCenters.map(center => center.cost_group_id))];
        const filteredGroups = costGroups.filter(group => costGroupIds.includes(group.id));

        const $groupDropdown = $('#cost_group_id');
        $groupDropdown.empty().append('<option value="">Select Cost Group</option>');

        filteredGroups.forEach(group => {
            const selected = String(group.id) === String(preselectedGroupId) ? 'selected' : '';
            $groupDropdown.append(`<option value="${group.id}" ${selected}>${group.name}</option>`);
        });

        // Manually trigger change to load cost centers
        $groupDropdown.trigger('change');

        // Now load cost centers if group is selected
        if (preselectedGroupId) {
            const costCenter = $('#cost_center_id');
            costCenter.empty();

            const filteredCentersByGroup = costCenters.filter(center => {
                if (!center.location || center.cost_group_id !== parseInt(preselectedGroupId)) return false;

                const locationArray = Array.isArray(center.location)
                    ? center.location.flatMap(loc => loc.split(','))
                    : [];

                return locationArray.includes(String(preselectedLocationId));
            });

            if (filteredCentersByGroup.length === 0) {
                costCenter.prop('required', false);
                costCenter.hide();
            } else {
                costCenter.append('<option value="">Select Cost Center</option>');
                costCenter.show();

                filteredCentersByGroup.forEach(center => {
                    const selected = String(center.id) === String(preselectedCenterId) ? 'selected' : '';
                    costCenter.append(`<option value="${center.id}" ${selected}>${center.name}</option>`);
                });
            }

            costCenter.trigger('change');
        }
    }

    // Also trigger cost group and center change if needed
    $('#location_id').on('change', function () {
        loadCostGroupsByLocation($(this).val());
    });

    $('#cost_group_id').on('change', function () {
        const locationId = $('#location_id').val();
        const groupId = parseInt($(this).val());
        if (locationId && groupId) {
            loadCostCentersByGroup(locationId, groupId);
        }
    });
        $(".open-job-sectab").click(function() {
            $(this).parent().parent().next('tr').show();
            $(this).parent().find('.close-job-sectab').show();
            $(this).parent().find('.open-job-sectab').hide();
        });
        //
         $('.add-new-record').on('submit', function () {
            $('.preloader').fadeIn(); // show preloader
        });
    });

    $('#cost_group_id').on('change', function () {
        const locationId = $('#location_id').val();
        const groupId = parseInt($(this).val());

        if (!locationId || !groupId) {
            $('#cost_center_id').empty().append('<option value="">Select Cost Center</option>');
            return;
        }

        loadCostCentersByGroup(locationId, groupId);
    });


    $(document).ready(function() {
        $(".close-job-sectab").click(function() {
            $(this).parent().parent().next('tr').hide();
            $(this).parent().find('.open-job-sectab').show();
            $(this).parent().find('.close-job-sectab').hide();
        });
    });
    $('#applyBtn').on('click', function (e) {

        // Close the modal
        var filterModal = bootstrap.Modal.getInstance(document.getElementById('addcoulmn'));

        // Optionally handle the response here
        e.preventDefault();

        // Get the date value
        const dateValue = $('input[name="date"]').val();
        const today = new Date().toISOString().split('T')[0];

        var formData = {
            to:  $('select[name="to"]').val(),
            type: $('select[name="type"]').val(),
            cc: $('select[name="cc"]').val(),
            date: $('input[name="date"]').val(),
            remarks: $('textarea[name="remarks"]').val(),
        };
        let type = $('select[name="type"]').val();
        let date = $('input[name="date"]').val();
        let remarks= $('textarea[name="remarks"]').val();
        let to = $('select[name="to"]').val();
        let cc = $('select[name="cc"]').val();

        var requiredFields = {
        "To": to,
        "CC": cc,
        "Type": type,
        "Date": date,
        "Remarks": remarks,
    };

        // Check for missing values
        // var missingFields = [];
        // $.each(requiredFields, function (key, value) {
        //     if (!value) {
        //         missingFields.push(key);
        //     }
        // });


        // // If missing fields exist, show an alert and stop execution
        // if (missingFields.length > 0) {
        //     alert("Please fill in the required fields: " + missingFields.join(", "));
        //     return;
        // }

            if (formData) {


                // AJAX request
                let isValid=true;
                const fields = ['to', 'type', 'date', 'cc', 'remarks'];


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
                    if (dateValue < today) {
                    var inputField = $('[name="date"]');

                    // For normal inputs, remove previous error and append new one
                    inputField.removeClass('is-invalid').addClass(
                        'is-invalid');
                    inputField.next('.invalid-feedback')
                        .remove(); // Remove any previous error
                    inputField.after(
                        '<div class="invalid-feedback">Please select a future date.</div>');
                    return; // Stop form submission
                }
                $('.preloader').show();
                $.ajax({
                    url: "{{ route('finance.cashflow.add.scheduler') }}",
                    method: 'POST',
                    data: formData,
                    success: function (response) {
                        $('.preloader').hide();
                        // Show success message
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

                        // Optionally reset the form
                        // $('select[name="type"]').val(null).trigger('change');
                        // $('select[name="cc"]').val(null).trigger('change');
                        // $('input[name="date"]').val('');
                        // $('textarea[name="remarks"]').val('');

                        if (filterModal) {
                            filterModal.hide();
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
                }
            }
        });

    </script>
@endsection
