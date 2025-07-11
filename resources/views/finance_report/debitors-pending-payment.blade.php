@extends('layouts.app')
{{-- <style>

.tableistlastcolumnfixed tr td:nth-last-child(3), .tableistlastcolumnfixed tr th:nth-last-child(3) {
    position: sticky;
    right: 120px;
    z-index: 10;
    background: #fff;
}
.tableistlastcolumnfixed tr td:nth-last-child(2), .tableistlastcolumnfixed tr th:nth-last-child(2) {
    position: sticky;
    right: 39px;
    z-index: 10;
    background: #fff;
}
</style> --}}

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
                                        <h3>Pending Payments to Debitors</h3>
                                        <p class="my-25">As on <strong>{{ $date2 }}</strong></p>
                                    </div>
                                    <div
                                        class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                        <a href="{{ route('pending.payment.show.import','receipts') }}" class="btn btn-secondary  me-50 btn-sm mb-0">
                                            <i data-feather="upload"></i> Import
                                        </a> 
                                        <button data-bs-toggle="modal" data-bs-target="#filter"
                                            class="btn btn-warning me-50 btn-sm mb-0"><i data-feather="filter"></i>
                                            Filter</button>
                                        <button class="btn btn-primary btn-sm mb-0 waves-effect" id="proceed"><i
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


                                        <div class="col-md-2">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Voucher Type</label>
                                                <select class="form-select select2" id="book_code">
                                                    <option value="">Select Type</option>
                                                    @foreach ($books_t->unique('alias') as $book)
                                                        <option value="{{ $book->alias }}">{{ strtoupper($book->name) }}
                                                        </option>
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
                                        <div class="col-md-2 mb-2">
													<div class="mb-1 mb-sm-0">
														<label class="form-label">Ledger Group</label>
														<select class="form-select select2" id="filter_group" required>
														</select>
													</div>
                                        </div>

                                        {{-- <div class="col-md-2">
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
                                        </div> --}}



                                        <div class="col-md-2 mb-1 mb-sm-0">
                                            <label class="form-label" for="fp-range">Document No.</label>
                                            <input type="text" id="document_no" class="form-control" />
                                        </div>

                                        <div class="col-md-2">
                                            <div class="mt-2 mb-sm-0">
                                                <label class="mb-1">&nbsp;</label>
                                                <button class="btn mt-25 btn-dark btn-sm" id="findFilters" type="submit"><i
                                                        data-feather="search"></i> Find</button>
                                                 <button class="btn mt-25 btn-danger btn-sm" id="clearAll" type="button"><i
                                                        data-feather="refresh-cw"></i> Clear All</button>
                                            </div>
                                            </div>
                                        </div>

                                    </div>



                                </div>
                            </div>
                            <div class="col-md-12">
                                <div
                                    class="table-responsive trailbalnewdesfinance po-reportnewdesign leadger-balancefinance trailbalnewdesfinancerightpad gsttabreporttotal">
                                    <table
                                        class="mt-1 datatables-basic table myrequesttablecbox tablecomponentreport po-order-detail tableistlastcolumnfixed">
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
                                                <td colspan="9" class="text-end">Grand Total</td>
                                                <td class="fw-bolder text-dark text-end totalAmount">0</td>
                                                <!-- Amount Total -->
                                                <td class="fw-bolder text-dark text-end totalBalance">0</td>
                                                <!-- Balance Total -->
                                                <td class="fw-bolder text-dark text-end totalSettle">0</td>
                                                <!-- Settle Amt Total -->
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
                        <label class="form-label">Cost Group</label>
                        <select id="cost_group_id" class="form-select select2" name="cost_group_id" required>
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
        const costGroups = @json($cost_groups);
    </script>
    <!-- BEGIN: Dashboard Custom Code JS-->
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- END: Dashboard Custom Code JS-->
    <script>
        const voucherMap = {};
        const selectedVoucherIds = new Set();

        function updateLocationsDropdown(selectedOrgIds) {
            const filteredLocations = locations.filter(loc =>
                selectedOrgIds.includes(String(loc.organization_id))
            );
            const $locationDropdown = $('#location_id').empty().append('<option value="">Select</option>');
            filteredLocations.forEach(loc => {
                $locationDropdown.append(`<option value="${loc.id}">${loc.store_name}</option>`);
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

        $(function() {
            $(".sortable").sortable();

            const dt_basic_table = $('.datatables-basic');
            if (dt_basic_table.length) {
                dt_basic_table.DataTable({
                    order: [
                        [0, 'asc']
                    ],
                    dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-md-3"l><"col-md-6 dt-action-buttons text-end pe-0"B><"col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-md-6"i><"col-md-6"p>>',
                    displayLength: 8,
                    lengthMenu: [8, 10, 25, 50, 75, 100],
                    buttons: [{
                        extend: 'collection',
                        className: 'btn btn-outline-secondary dropdown-toggle',
                        text: feather.icons['share'].toSvg({
                            class: 'font-small-3 me-50'
                        }) + 'Export',
                        buttons: [{
                            extend: 'excel',
                            text: feather.icons['file'].toSvg({
                                class: 'font-small-4 me-50'
                            }) + 'Excel',
                            className: 'dropdown-item',
                            exportOptions: {
                                columns: [3, 4, 5, 6, 7]
                            }
                        }],
                        init: function(api, node) {
                            $(node).removeClass('btn-secondary').closest('.dt-buttons')
                                .removeClass('btn-group').addClass('d-inline-flex');
                        }
                    }],
                    language: {
                        search: '',
                        searchPlaceholder: "Search...",
                        paginate: {
                            previous: '&nbsp;',
                            next: '&nbsp;'
                        }
                    }
                });
            }

            if (feather) feather.replace({
                width: 14,
                height: 14
            });
        });

        function buildParams(extra = {}) {
            const map = {
                date: $('#fp-range').val(),
                ledgerGroup: $('#filter_group').val(),
                book_code: $('#book_code').val(),
                filter_ledger: $('#filter_ledger').val(),
                filter_group: $('#filter_group').val(),
                document_no: $('#document_no').val(),
                cost_center_id: $('#cost_center_id').val(),
                cost_group_id: $('#cost_group_id').val(),
                location_id: $('#location_id').val(),
                organization_id: ($('#filter-organization').val() || []).filter(v => v?.trim() !== '')
            };
            const params = Object.fromEntries(Object.entries(map).filter(([k, v]) =>
                Array.isArray(v) ? v.length : v !== null && v !== ''
            ));
            return {
                ...params,
                ...extra
            };
        }

        function getLedgers(params = {}, details = null) {
            $('.preloader').show();
            $('.vouchers:not(:checked)').each(function() {
                $('#' + this.value).remove();
            });
            updateVoucherNumbers();

            const preSelected = $('.vouchers:checked').map(function() {
                return this.value;
            }).get();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('getInvocies') }}',
                type: 'POST',
                dataType: 'json',
                data: {
                    ...params,
                    type: 'receipts',
                    details_id: details
                },
                success: response => {
                   console.log("Voucher Response:", response);
                    // $('.preloader').hide();
                    const table = $('.datatables-basic').DataTable();
                    table.clear().draw();
                    Object.keys(voucherMap).forEach(key => delete voucherMap[key]);
                    selectedVoucherIds.clear(); $('.preloader').hide();
                    if (response.data.length > 0) {
                        let html = '';
                        let rowIndex = 1; 
                        $.each(response.data, function(index, voucher) {
                            // if (!preSelected.includes(voucher.id.toString())) {
                            if (!preSelected.includes(voucher.id.toString()) && voucher.balance >= 1) {
                                const items = voucher.items || [];

                                items.forEach(function(item, i) {
                                    const uniqueKey = `${voucher.id}_${i}`;
                                    voucherMap[uniqueKey] = {
                                        ...voucher,
                                        item: item // Also attach the specific item
                                    };
                                    const amount = parseFloat(item.amount ?? voucher.amount ??
                                        0).toFixed(2);
                                    const balance = parseFloat(item.balance ?? voucher
                                        .balance ?? 0).toFixed(2);
                                    const dataAmount = balance;
                                    const existingSettleAmt = voucherMap[uniqueKey]?.settle_amt ?? 0;
                                    const isChecked = existingSettleAmt > 0;
                                    html += `<tr id="${uniqueKey}" class="voucherRows">
                                        <td>${rowIndex}</td>
                                        <td class="text-nowrap">${voucher.date ?? '-'}</td>
                                        <td class="text-nowrap" data-ledger-id="${item.ledger ?? ''}">${item.ledger?.name ?? '-'}</td>
                                        <td class="text-nowrap">${item.ledger_group?.name ?? item.ledger?.ledger_group?.name ?? '-'}</td>
                                        <td class="text-nowrap">${voucher.organization?.name ?? '-'}</td>
                                        <td class="text-nowrap">${voucher.erp_location?.store_name ?? '-'}</td>
                                        <td class="text-nowrap">${item.cost_center?.name ?? '-'}</td>
                                        <td class="text-nowrap fw-bolder text-dark">${voucher.series?.book_code?.toUpperCase() || '-'}</td>
                                        <td class="text-nowrap">${voucher.voucher_no ?? '-'}</td>
                                        <td class="text-nowrap text-end">${formatIndianNumber(amount)}</td>
                                        <td class="text-nowrap balanceInput text-end">${formatIndianNumber(balance)}</td>
                                        <td class="text-end">
                                            <input type="number" style="width:105px" class="form-control text-end mw-100 settleInput settleAmount${uniqueKey}" data-id="${uniqueKey}" value="${existingSettleAmt}"/>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check form-check-inline me-2">
                                                <input class="form-check-input vouchers voucherCheck${uniqueKey}" data-id="${uniqueKey}" type="checkbox" name="vouchers" value="${uniqueKey}" data-amount="${dataAmount}"  ${isChecked ? 'checked' : ''}/>
                                            </div>
                                        </td>
                                    </tr>`;
                                    
                                    rowIndex++;
                                });
                            }
                        });

                        const rows = $.parseHTML(html).filter(el => el.nodeName === "TR");
                        rows.forEach(row => table.row.add(row));
                        table.draw();

                    }
                    $('#inlineCheckbox1').prop('checked', false);
                    updateVoucherNumbers();
                    calculateSettle();
                    calculateAmountAndBalanceTotals();
                    $('.preloader').hide();
                },
                error: () => $('.preloader').hide()
            });
        }
       $(document).on('keyup keydown', '.settleInput', function() {
            const input = $(this);
            const id = input.data('id');
            const value = parseFloat(input.val()) || 0;

            // Store in voucherMap for cross-page persistence
            if (voucherMap[id]) {
                voucherMap[id].settle_amt = value;
            }

            // Set checkbox status
            if (value > 0) {
                $('.voucherCheck' + id).prop('checked', true);
                selectedVoucherIds.add(id);
            } else {
                $('.voucherCheck' + id).prop('checked', false);
                selectedVoucherIds.delete(id);
            }
            let row = input.closest('.voucherRows');
            let balanceText = row.find('.balanceInput').text().replace(/,/g, '');
            let balance = parseFloat(balanceText);
            let settleAmount = parseFloat(input.val());

            // Remove existing error message span if it exists
            input.next('.invalid-feedback').remove();

            if (settleAmount > balance) {
                input.addClass('is-invalid');
                input.after(
                    '<span class="invalid-feedback d-block" style="font-size:12px">Settle amount cannot be greater than balance.</span>'
                    );
            } else {
                input.removeClass('is-invalid');
            }
            calculateSettle();
        });

        $(document).on('input', '.settleInput', function() {
            const key = $(this).data('id');
            const val = parseFloat($(this).val()) || 0;

            // Save to memory
            if (voucherMap[key]) {
                voucherMap[key].settle_amt = val;
            }

            // Update checkbox state
            if (val > 0) {
                $('.voucherCheck' + key).prop('checked', true);
                selectedVoucherIds.add(key);
            } else {
                $('.voucherCheck' + key).prop('checked', false);
                selectedVoucherIds.delete(key);
            }

            calculateSettle();
        });


        function updateVoucherNumbers() {
            $('.voucherRows').each(function(index) {
                $(this).find('td:first-child').text(index + 1);
            });
        }

        function calculateSettle() {
            let settleSum = 0;

            // Loop through all entries in voucherMap (not just visible ones)
            Object.entries(voucherMap).forEach(([key, voucher]) => {
                const val = parseFloat(voucher?.settle_amt) || 0;
                if (val > 0) {
                    settleSum += val;
                }
            });

            $('.totalSettle').text(formatIndianNumber(settleSum.toFixed(2)));
        }

        $('.datatables-basic').on('draw.dt', function() {
            $('.vouchers').each(function() {
                const key = $(this).val();
                const isChecked = selectedVoucherIds.has(key);
                $(this).prop('checked', isChecked);

                const $row = $(this).closest('tr');
                const settleVal = parseFloat(voucherMap[key]?.settle_amt || 0);
                $row.find('.settleInput').val(isChecked ? settleVal.toFixed(2) : '0.00');
            });

            calculateSettle();
        });


        function calculateAmountAndBalanceTotals() {
            let totalAmount = 0,
                totalBalance = 0;
            $('.voucherRows').each(function() {
                totalAmount += parseFloat($(this).find('td').eq(9).text().replace(/,/g, '')) || 0;
                totalBalance += parseFloat($(this).find('td').eq(10).text().replace(/,/g, '')) || 0;
            });
            $('.totalAmount').text(formatIndianNumber(totalAmount.toFixed(2)));
            $('.totalBalance').text(formatIndianNumber(totalBalance.toFixed(2)));
        }

        function selectAllVouchers() {
            $('.vouchers').each(function() {
                const isChecked = $(this).is(':checked');
                const $row = $(this).closest('tr');
                const balanceText = $row.find('.balanceInput').text().replace(/,/g, '');
                const balanceVal = parseFloat(balanceText) || 0;

                $(`.settleAmount${this.value}`).val(isChecked ? balanceVal.toFixed(2) : '0.00');
            });
            calculateSettle();
        }

        function getSelectedVoucherData() {
           const selectedData = [];
            const validationErrors = [];
            const reportedLedgers = new Set(); // Track already reported ledger names

            selectedVoucherIds.forEach(key => {
                const voucher = voucherMap[key];
                if (!voucher || !voucher.item?.id) return;

                const ledgerName = voucher.item?.ledger?.name ?? 'Unknown Ledger';

                if (!voucher.item?.ledger?.customer) {
                    if (!reportedLedgers.has(ledgerName)) {
                        validationErrors.push(`${ledgerName}'s customer is missing`);
                        reportedLedgers.add(ledgerName);
                    }
                    return;
                }

                const creditDays = voucher.item.ledger.customer.credit_days;
                if (creditDays === null || creditDays === undefined || creditDays === '') {
                    if (!reportedLedgers.has(ledgerName)) {
                        validationErrors.push(`${ledgerName}'s customer has no credit days set`);
                        reportedLedgers.add(ledgerName);
                    }
                    return;
                }

                selectedData.push({
                    ...voucher,
                    settle_amt: parseFloat(voucher.settle_amt || 0).toFixed(2),
                    item_id: voucher.item.id
                });
            });

            if (validationErrors.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    html: validationErrors.map(e => `<div>${e}</div>`).join(''),
                });
                return false;
            }
            return selectedData;
        }

        $('#inlineCheckbox1').on('click', function() {
            const isChecked = this.checked;
            const table = $('.datatables-basic').DataTable();

            // Loop through all rows in DataTable's full dataset (not just DOM)
            table.rows().every(function() {
                const data = this.data(); // `data` is the actual row data object/HTML
                const rowNode = this.node();
                const $row = $(rowNode);
                const $checkbox = $row.find('.vouchers');
                const key = $checkbox.val();

                // Handle checkbox and settle input on visible page only
                if ($row.is(':visible')) {
                    $checkbox.prop('checked', isChecked);
                    const balance = parseFloat($row.find('.balanceInput').text().replace(/,/g, '')) || 0;
                    $row.find('.settleInput').val(isChecked ? balance.toFixed(2) : '0.00');
                }

                // Ensure voucherMap and selectedVoucherIds are updated
                if (voucherMap[key]) {
                    const balance = parseFloat(voucherMap[key]?.item?.balance || voucherMap[key]?.balance ||
                        0);
                    voucherMap[key].settle_amt = isChecked ? balance : 0;

                    if (isChecked) {
                        selectedVoucherIds.add(key);
                    } else {
                        selectedVoucherIds.delete(key);
                    }
                }
            });

            calculateSettle();
        });


        $('#filterForm').on('submit', e => {
            e.preventDefault();
            $('#inlineCheckbox1').prop('checked', false);
            $('#filter').modal('hide');
            handleFilterChange();
            // getLedgers(buildParams());
        });

        $('#findFilters').on('click', e => {
            e.preventDefault();
            $('#inlineCheckbox1').prop('checked', false);
            handleFilterChange();
            // getLedgers(buildParams());
        });

        $(document).on('click', '#proceed', function() {
            isValid = true;
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
                        '<span class="invalid-feedback d-block"  style="font-size:12px">Settle amount cannot be greater than balance.</span>'
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
            $('.preloader').show();
            const selectedRows = getSelectedVoucherData();
            if (selectedRows === false){
                $('.preloader').hide();
                return;
            }
              

            console.log(selectedRows);
            if (selectedRows.length === 0) {
                $('.preloader').hide();
                Swal.fire({
                    icon: 'warning',
                    title: 'No Valid Rows',
                    text: 'Please select at least one valid row with a settle amount.',
                });
                return;
            }

            $.ajax({
                url: '/report/store-cr-dr-row', // your POST endpoint
                type: 'POST',
                data: JSON.stringify({
                        rows: selectedRows,
                        type: 'receipts'
                    }),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Laravel CSRF
                },
                success: function(response) {
                    // $('.preloader').hide();
                    window.location.href = response.redirect;
                },
                error: function(xhr) {
                    $('.preloader').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to submit vouchers. Please try again.'
                    });
                }
            });
        });
        function hasFilters() {
            // Check main filters
            const mainFilters = [
                $('#fp-range').val(),
                $('#book_code').val(),
                $('#filter_ledger').val(),
                $('#filter_group').val(),
                $('#document_no').val()
            ].some(value => value && value !== '');

           
            return mainFilters;
        }

        function handleFilterChange() {
            toggleClearButton();
            getLedgers(buildParams());
        }

        function toggleClearButton() {
        const hasActiveFilters = hasFilters();
        if (hasActiveFilters) {
            $('#clearAll').removeClass('d-none');
        } else {
            $('#clearAll').addClass('d-none');
        }
}

        $(document).ready(() => {
            toggleClearButton(); 
            $(document).on('change', '.vouchers', function() {
                const key = $(this).val();
                const isChecked = $(this).is(':checked');
                const $row = $(this).closest('tr');
                const balance = parseFloat($row.find('.balanceInput').text().replace(/,/g, '')) || 0;

                const $input = $row.find('.settleInput');
                $input.val(isChecked ? balance.toFixed(2) : '0.00');

                if (voucherMap[key]) {
                    voucherMap[key].settle_amt = isChecked ? balance : 0;
                }

                if (isChecked) {
                    selectedVoucherIds.add(key);
                } else {
                    selectedVoucherIds.delete(key);
                }

                calculateSettle();
            });

            getLedgers(buildParams());
            // $('#fp-range, #book_code, #filter_ledger, #filter_group, #document_no').on('change', function() {
            //     handleFilterChange();
            //     toggleClearButton();
            // });
            $('#filter-organization').on('change', e => updateLocationsDropdown($(e.target).val() || []));
            $('#location_id').on('change', e => {
                const loc = $(e.target).val();
                // if (!loc) return $('#cost_center_id').html('<option value="">Select Cost Center</option>');
                loadCostGroupsByLocation(loc);
            });

            updateLocationsDropdown($('#filter-organization').val() || []);
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
        $(document).on('change', '#filter_ledger', function () {
            groupDropdown = $("#filter_group");

            let ledgerId = $(this).val(); // Get the selected organization ID
            $.ajax({
                url: '{{ route('voucher.getLedgerGroups') }}',
                method: 'GET',
                data: {
                    ledger_id: ledgerId,
                    _token: $('meta[name="csrf-token"]').attr(
                        'content') // CSRF token
                },
                success: function(response) {
                    // $('.preloader').hide();
                    groupDropdown.empty(); // Clear previous options

                    response.forEach(item => {
                        groupDropdown.append(
                            `<option value="${item.id}" data-ledger="${ledgerId}">${item.name}</option>`
                        );
                    });
                    groupDropdown.data('ledger', ledgerId);
                    //handleRowClick(rowId);

                },
                error: function(xhr) {
                    // $('.preloader').hide();
                    let errorMessage =
                    'Error fetching group items.'; // Default message

                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON
                        .error; // Use API error message if available
                    }
                    showToast("error", errorMessage);

                    
                }
            });
        });
        $(document).on('click', '#clearAll', function() {
            // Reset all filter inputs
            $('#fp-range').val('');
            $('#book_code').val('').trigger('change');
            $('#filter_ledger').val('').trigger('change');
            $('#filter_group').val('').trigger('change');
            $('#document_no').val('');
            
            
            
            // Uncheck all checkboxes
            $('#inlineCheckbox1').prop('checked', false);
            $('.vouchers').prop('checked', false);
            
            // Clear voucher selections
            selectedVoucherIds.clear();
            Object.keys(voucherMap).forEach(key => {
                if (voucherMap[key]) {
                    voucherMap[key].settle_amt = 0;
                }
            });
            
            // Reset settle inputs
            $('.settleInput').val('0.00');
            
            // Reload data with empty filters
            handleFilterChange();
            
            // Reset totals
            $('.totalAmount, .totalBalance, .totalSettle').text('0.00');
            
            // Hide the clear button after clearing
            toggleClearButton();
        });

    </script>
@endsection
