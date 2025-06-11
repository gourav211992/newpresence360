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
                                    <div class="col-md-4">
                                        <h3>Pending Paymnets to Creditors</h3>
                                        <p class="my-25">As on <strong>{{ $date2 }}</strong></p>
                                    </div>
                                    <div
                                        class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                        <button data-bs-toggle="modal" data-bs-target="#filter"
                                            class="btn btn-warning me-50 btn-sm mb-0"><i data-feather="filter"></i>
                                            Filter</button>
                                        <button class="btn btn-primary btn-sm mb-0 waves-effect"><i
                                                data-feather="check-circle"></i> Proceed</button>
                                    </div>
                                </div>

                                <div class="customernewsection-form poreportlistview p-1">
                                    <div class="row">

                                        <div class="col-md-2 mb-1 mb-sm-0">
                                            <label class="form-label" for="fp-range">Date</label>
                                            <input type="text" id="fp-range" name="date_range"
                                                value="{{ Request::get('date_range') }}"
                                                class="form-control flatpickr-range bg-white"
                                                placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                                        </div>
{{-- {{ dd($books_t->unique('alias')) }} --}}

                                        <div class="col-md-2">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Voucher Type</label>
                                                <select class="form-select select2" id="book_code">
                                                    <option value="">Select Type</option>
                                                    @foreach ($books_t->unique('alias') as $book)
                                                        <option value="{{ $book->alias }}">{{ strtoupper($book->name) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Ledger</label>
                                                <select class="form-select select2" id="filter_ledger">
                                                    <option value="">Select</option>
                                                    @php
                                                        $selectedLedgerId = request()->query('ledger'); // Get group_id from URL params
                                                    @endphp
                                                    @isset($all_ledgers)
                                                        @foreach ($all_ledgers as $ledger)
                                                            <option value="{{ $ledger->id }}"
                                                                {{ $selectedLedgerId == $ledger->id ? 'selected' : '' }}>
                                                                {{ $ledger->name }}</option>
                                                        @endforeach
                                                    @endisset

                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Ledger Group</label>
                                                <select class="form-select select2" id="filter_group">
                                                    <option value="">Select</option>
                                                    @php
                                                        use App\Helpers\Helper;
                                                        $selectedGroupId = request()->query('group'); // Get group_id from URL params
                                                    @endphp

                                                    @isset($all_groups)
                                                        @foreach ($all_groups as $group)
                                                            <option value="{{ $group->id }}"
                                                                {{ $selectedGroupId == $group->id ? 'selected' : '' }}>
                                                                {{ $group->name }}
                                                            </option>
                                                        @endforeach
                                                    @endisset

                                                </select>
                                            </div>
                                        </div>



                                        <div class="col-md-2 mb-1 mb-sm-0">
                                            <label class="form-label" for="fp-range">Document No.</label>
                                            <input type="text" id="document_no" class="form-control" />
                                        </div>

                                        <div class="col-md-2">
                                            <div class="mt-2 mb-sm-0">
                                                <label class="mb-1">&nbsp;</label>
                                                <button class="btn mt-25 btn-dark btn-sm" id="findFilters" type="submit"><i
                                                        data-feather="search"></i> Find</button>
                                            </div>
                                        </div>

                                    </div>



                                </div>
                            </div>
                            <div class="col-md-12">
                                <div
                                    class="table-responsive trailbalnewdesfinance po-reportnewdesign leadger-balancefinance trailbalnewdesfinancerightpad gsttabreporttotal">
                                    <table
                                        class="datatables-basic table myrequesttablecbox tablecomponentreport po-order-detail">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Date</th>
                                                <th>Ledger Name</th>
                                                <th>Ledger Group</th>
                                                <th>Organization</th>
                                                <th>Location</th>
                                                <th>Cost Center</th>
                                                <th>Series</th>
                                                <th>Document No.</th>
                                                <th class="text-end text-nowrap">Amount</th>
                                                <th class="text-end">Balance</th>
                                                <th width="150px" class="text-end">Settle Amt</th>
                                                <th class="ps-75">
                                                    <div class="form-check form-check-inline me-0">
                                                        <input class="form-check-input" type="checkbox" name="podetail"
                                                            id="inlineCheckbox1">
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="table">

                                            {{-- <td>
                                                    <div class="form-check form-check-inline me-0">
                                                        <input class="form-check-input" type="checkbox" name="podetail"
                                                            id="inlineCheckbox1">
                                                    </div>
                                                </td> --}}
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="9" class="text-center">Grand Total</td>
                                                <td class="text-end"></td>
                                                <td class="text-end"></td>
                                                <td class="text-end"></td>
                                                <td class="text-end"></td>
                                            </tr>
                                        </tfoot>

                                    </table>
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
    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0" id="filterForm">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">


                    <div class="mb-1">
                        <label class="form-label">Organization</label>
                        <select id="filter-organization" class="form-select select2" multiple name="filter_organization">
                            <option value="" disabled>Select</option>
                            @foreach ($mappings as $organization)
                                <option value="{{ $organization->organization->id }}"
                                    {{ $organization->organization->id == $organizationId ? 'selected' : '' }}>
                                    {{ $organization->organization->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Location</label>
                        <select name="location_id" id="location_id" class="form-select select2">
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Cost Center</label>
                        <select id="cost_center_id" class="form-select select2" name="cost_center_id">
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

@endsection
@section('scripts')
    <script>
        const locations = @json($locations);
        const costCenters = @json($cost_centers);
    </script>
    <!-- BEGIN: Dashboard Custom Code JS-->
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- END: Dashboard Custom Code JS-->
    <script>
        function updateLocationsDropdown(selectedOrgIds) {
            const filteredLocations = locations.filter(loc =>
                selectedOrgIds.includes(String(loc.organization_id))
            );

            const $locationDropdown = $('#location_id');
            $locationDropdown.empty().append('<option value="">Select</option>');

            filteredLocations.forEach(loc => {
                $locationDropdown.append(`<option value="${loc.id}">${loc.store_name}</option>`);
            });

            $locationDropdown.trigger('change');
        }

        function loadCostCenters(locationId) {
            if (locationId) {
                const filteredCenters = costCenters.filter(center => {
                    if (!center.location) return false;

                    const locationArray = Array.isArray(center.location) ?
                        center.location.flatMap(loc => loc.split(',')) : [];

                    return locationArray.includes(String(locationId));
                });
                console.log(filteredCenters, costCenters, locationId);

                const $costCenter = $('#cost_center_id');
                $costCenter.empty();

                if (filteredCenters.length === 0) {
                    $costCenter.append('<option value="">Select Cost Center</option>');
                } else {
                    $costCenter.append('<option value="">Select Cost Center</option>');
                    $('.cost_center').show();

                    filteredCenters.forEach(center => {
                        $costCenter.append(`<option value="${center.id}">${center.name}</option>`);
                    });
                }

                $costCenter.trigger('change');
            }
        }
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })

        $(function() {
            $(".sortable").sortable();
        });

        $(function() {

            var dt_basic_table = $('.datatables-basic'),
                dt_date_table = $('.dt-date'),
                dt_complex_header_table = $('.dt-complex-header'),
                dt_row_grouping_table = $('.dt-row-grouping'),
                dt_multilingual_table = $('.dt-multilingual'),
                assetPath = '../../../app-assets/';

            if ($('body').attr('data-framework') === 'laravel') {
                assetPath = $('body').attr('data-asset-path');
            }

            // DataTable with buttons
            // --------------------------------------------------------------------

            if (dt_basic_table.length) {
                var dt_basic = dt_basic_table.DataTable({

                    order: [
                        [0, 'asc']
                    ],
                    dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-3"l><"col-sm-12 col-md-6 withoutheadbuttin dt-action-buttons text-end pe-0"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 8,
                    lengthMenu: [8, 10, 25, 50, 75, 100],
                    buttons: [{
                            extend: 'collection',
                            className: 'btn btn-outline-secondary dropdown-toggle',
                            text: feather.icons['share'].toSvg({
                                class: 'font-small-3 me-50'
                            }) + 'Export',
                            buttons: [

                                {
                                    extend: 'excel',
                                    text: feather.icons['file'].toSvg({
                                        class: 'font-small-4 me-50'
                                    }) + 'Excel',
                                    className: 'dropdown-item',
                                    exportOptions: {
                                        columns: [3, 4, 5, 6, 7]
                                    }
                                },

                            ],
                            init: function(api, node, config) {
                                $(node).removeClass('btn-secondary');
                                $(node).parent().removeClass('btn-group');
                                setTimeout(function() {
                                    $(node).closest('.dt-buttons').removeClass('btn-group')
                                        .addClass('d-inline-flex');
                                }, 50);
                            }
                        },
                        //        {
                        //          extend: 'collection',
                        //          className: 'btn btn-outline-secondary',
                        //          text: feather.icons['share'].toSvg({ class: 'font-small-4 me-50' }) + 'fd',
                        //        },

                    ],


                    language: {
                        search: '',
                        searchPlaceholder: "Search...",
                        paginate: {
                            // remove previous & next text from pagination
                            previous: '&nbsp;',
                            next: '&nbsp;'
                        }
                    }
                });
                $('div.head-label').html('<h6 class="mb-0">Event List</h6>');
            }

            // Flat Date picker
            if (dt_date_table.length) {
                dt_date_table.flatpickr({
                    monthSelectorType: 'static',
                    dateFormat: 'm/d/Y'
                });
            }

        });
        /**
         * Collect all filter fields, keeping only keys that have a value
         */
        function buildParams(extra = {}) {
            console.log($('#book_code').val());
            const map = {
                date: $('#fp-range').val(),
                ledgerGroup: $('#filter_group').val(),
                book_code: $('#book_code').val(),
                filter_ledger: $('#filter_ledger').val(),
                // filter_group: $('#filter_group').val(),
                document_no: $('#document_no').val(),
                cost_center_id: $('#cost_center_id').val(),
                location_id: $('#location_id').val(),
                organization_id: ($('#filter-organization').val() || [])
                    .filter(v => v && v.trim() !== '')
            };

            // keep only entries that are truthy or non-empty arrays
            const params = Object.fromEntries(
                Object.entries(map).filter(([k, v]) =>
                    Array.isArray(v) ? v.length : v !== null && v !== ''
                )
            );

            return {
                ...params,
                ...extra
            }; // add/override anything passed in
        }

        /**
         * Ajax loader
         */
        function getLedgers(params = {}, details = null) {
            $('.preloader').show();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('getInvocies') }}',
                type: 'POST',
                dataType: 'json',
                data: {
                    ...params,
                    type: 'credit',
                    details_id: details
                },
                success: res => {
                    console.log(res)
                    $('.preloader').hide();

                    // locate the vouchers array ─ adjust if your key path differs
                    const vouchersRaw = Array.isArray(res.data) ? res.data : res;
                    const vouchers = vouchersRaw.filter(v => parseFloat(v.balance) !== 0);

                    // running counters
                    let idx = 1;
                    let grandAmt = 0;
                    let grandBal = 0;

                    // turn every (voucher → item) pair into one table row
                    const builtRows = vouchers.flatMap(voucher => {
                        return (voucher.items || []).map(item => {
                            const amt = Number(item.amount ?? voucher.amount ??
                            0); // or item.credit_amt_org …
                            const bal = Number(item.balance ?? voucher.balance ?? 0);

                            grandAmt += amt;
                            grandBal += bal;

                            return [
                                idx++, // #
                                voucher.date ?? '-', // column: Date   (from voucher)
                                item.ledger?.name ?? '-', // Ledger Name    (item → ledger)
                                item.ledger?.ledger_group
                                ?.name // Ledger Group   (item → ledger → group)
                                ??
                                item.ledger_group?.name ?? '-',
                                voucher.organization?.name ??
                                '-', // Organization   (voucher relation)
                                voucher.ErpLocation
                                ?.store_name // Location       (item relation)
                                ??
                                voucher.erp_location?.store_name ?? '-',
                                item.cost_center?.name ??
                                '-', // Cost Center    (item relation)
                                voucher.series?.book_code ??
                                '-', // Series         (voucher relation)
                                voucher.voucher_no ?? '-', // Document No.   (voucher)
                                formatIndianNumber(amt), // Amount         (helper fn)
                                formatIndianNumber(bal), // Balance
                                `<input type="number"
                        class="form-control form-control-sm text-end settle-amt"
                        name="settle[${item.id}]"
                        min="0"  max="${amt}" step="0.01" style="min-width: 150px;">`,
                                `<div class="form-check form-check-inline me-0">
                     <input class="form-check-input row-select"
                            type="checkbox" data-id="${item.id}">
                 </div>`
                            ];
                        });
                    });

                    /* ---------- render ---------- */
                    const $table = $('.datatables-basic');

                    if ($.fn.DataTable.isDataTable($table)) {
                        const dt = $table.DataTable();
                        dt.clear().rows.add(builtRows).draw();
                    } else {
                        const html = builtRows
                            .map(cells => `<tr><td class="text-end text-nowrap">${cells.join('</td><td>')}</td></tr>`)
                            .join('');
                        $table.find('tbody').html(html);
                    }

                    /* ---------- grand totals ---------- */
                    const $tfoot = $table.find('tfoot tr td');
                    $tfoot.eq(9).text(formatIndianNumber(grandAmt)); // Amount total
                    $tfoot.eq(10).text(formatIndianNumber(grandBal)); // Balance total
                    $tfoot.eq(11).text('0.00'); // Settle-total (if you track it)

                    /* ---------- optional: attach listeners to .settle-amt or .row-select here ---------- */
                },
                error: () => $('.preloader').hide()
            });
        }

        /* ---------- events ---------- */

        // click inside modal body (find button)
        $('#findFilters').on('click', e => {
            e.preventDefault();
            getLedgers(buildParams());
        });

        // full-form submit (Apply button)
        $('#filterForm').on('submit', e => {
            e.preventDefault();
            $('#filter').modal('hide');
            getLedgers(buildParams());
        });

        // initial load
        $(document).ready(() => {
            getLedgers(buildParams());

            // keep location / cost-center dropdowns in sync
            $('#filter-organization').on('change', e =>
                updateLocationsDropdown($(e.target).val() || [])
            );
            $('#location_id').on('change', e => {
                const loc = $(e.target).val();
                if (!loc) return $('#cost_center_id').html('<option value="">Select Cost Center</option>');
                loadCostCenters(loc);
            });

            // auto-populate if org already pre-selected
            updateLocationsDropdown($('#filter-organization').val() || []);
        });
    </script>
@endsection
