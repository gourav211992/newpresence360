@extends('layouts.app')

@section('content')
    <style>
        .sidebar-filter {
        -webkit-overflow-scrolling: touch; /* for smooth scroll on iOS */
    }
    </style>
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-body">
                {{-- <div id="message-area">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
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
                </div> --}}
                <section id="basic-datatable">
                    <div class="card border  overflow-hidden">
                        <div class="row">
                            <div class="col-md-12 bg-light border-bottom mb-1 po-reportfileterBox">
                                <div class="d-flex justify-content-between align-items-center p-2">
                                    <div>
                                        <h3 class="mb-0">Stock Ledger Report</h3>
                                        <p class="mb-0">Apply the Filter</p>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-secondary" onclick="toggleFilterSidebar()">Filters</button>
                                        <a href="/inventory-reports/get-stock-ledger-reports" class="btn btn-danger">Clear</a>
                                        <button type="button" onclick="sendMailTo();" class="btn btn-primary">
                                            <i data-feather="mail"></i> E-Mail
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div id="filterSidebar" class="sidebar-filter bg-white shadow p-3" style="width: 300px; position: fixed; top: 0; right: -300px; height: 100vh; overflow-y: auto; transition: all 0.3s; z-index: 1050;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5>Filters</h5>
                                    <button class="btn-close" onclick="toggleFilterSidebar()"></button>
                                </div>
                                <form action="/inventory-reports/get-stock-ledger-summary-filter" method="GET">
                                    <div class="mb-2">
                                        <label>Period</label>
                                        <input type="text" name="Period" id="Custom" class="form-control flatpickr-input" readonly />
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Doc No.</label>
                                        <input type="text" name="doc_no" id="doc_no" placeholder="Document No" class="form-control mw-100" autocomplete="off" value="">
                                    </div>
                                    <div class="mb-2">
                                        <label>Item</label>
                                        <input type="text" placeholder="Select" class="form-control ledgerselecct inventory_items" id="item" name="item" />
                                    </div>
                                    <div class="mb-2">
                                        <label>Attributes</label>
                                        <button type="button" class="btn btn-outline-secondary w-100 attributeBtn">Attributes</button>
                                    </div>
                                    <div class="mb-2">
                                        <label>Location</label>
                                        <select class="form-select select2 store_code" name="store_id" id="store_id">
                                            <option value="">Location</option>
                                            @foreach ($erpStores as $val)
                                                <option value="{{ $val->id }}">{{ $val->store_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label>Store</label>
                                        <select class="form-select select2 sub_store_code" name="sub_store_id" id="sub_store_id">
                                            <option value="">Store</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label>Stock Type</label>
                                        <select class="form-select select2 stock_types" name="stock_type" id="stock_type">
                                            <option value="R">Regular</option>
                                            <option value="W">WIP</option>
                                            <option value="S">Sub Standard</option>
                                            <option value="J">Rejected</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label>Doc Type</label>
                                        <select class="form-select select2 book_type_code" name="book_type_id" id="book_type_id">
                                            <option value="">Doc Type</option>
                                            @foreach ($bookTypes as $val)
                                                <option value="{{ $val }}">{{ $val }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label>Status</label>
                                        <select class="form-select select2 type_of_stock" name="type_of_stock_id" id="type_of_stock_id">
                                            <option value="">Status</option>
                                            <option value="confirmed_stock">Confirmed</option>
                                            <option value="unconfirmed_stock">Unconfirmed</option>
                                        </select>
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <button type="button" class="btn btn-secondary" onclick="toggleFilterSidebar()">Cancel</button>
                                        <button type="button" class="btn btn-primary" id="applyFiltersBtn">Apply</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-12" style="min-height: 300px">
                                <div class="table-responsive trailbalnewdesfinance po-reportnewdesign my-class">
                                    <table class="my-table datatables-basic table myrequesttablecbox">
                                        <thead>
                                            <th class="no-wrap">S.No</th>
                                            <th class="no-wrap">Doc. Date</th>
                                            <th class="no-wrap">Doc. Series</th>
                                            <th class="no-wrap">Doc. No</th>
                                            <th class="no-wrap">Doc. Type</th>
                                            <th class="no-wrap">Item Code</th>
                                            <th class="no-wrap">Item Name</th>
                                            <th class="no-wrap">Attributes</th>
                                            <th class="no-wrap">Location</th>
                                            <th class="no-wrap">Store</th>
                                            <th class="no-wrap">Station</th>
                                            <th class="no-wrap">UOM</th>
                                            <th class="no-wrap">Stock Type</th>
                                            <th class="no-wrap">SO No</th>
                                            <th class="no-wrap">LOT No</th>
                                            <th class='no-wrap text-end'>Rate</th>
                                            <th class='no-wrap text-end'>Receipt Quantity</th>
                                            <th class='no-wrap text-end'>Issue Quantity</th>
                                            <th class='no-wrap text-end'>Receipt Value</th>
                                            <th class='no-wrap text-end'>Issue Value</th>
                                            <th class='no-wrap text-end'>Reserved Stock</th>
                                            <th class='no-wrap text-end'>Hold Stock</th>
                                            <th class="no-wrap">Status</th>
                                        </thead>
                                        <tbody id="inventory-tbody">
                                            <!-- Table rows will be populated here dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <div class="modal fade" id="attribute" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Select Attribute</h1>
                    <p class="text-center">Enter the details below.</p>
                    <div class="table-responsive-md customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th>Attribute Name</th>
                                    <th>Attribute Value</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="button" id="attribute-button" data-bs-dismiss="modal"
                        class="btn btn-primary attribute-button">Select</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="sendMail" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="send-mail">
                    @csrf
                    <input type="hidden" name="action_type" id="action_type">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal"
                                id="send_mail_heading_label">
                            </h4>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pb-2">
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">Email To</label>
                                    <select name="email_to[]" class="select2-email form-control mail_modal cannot_disable"
                                        multiple data-placeholder="Select or enter email(s)">
                                        @foreach ($users as $user)
                                            <option value="{{ $user->email }}">{{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">CC To</label>
                                    <select name="email_cc[]" class="select2-cc form-control mail_modal cannot_disable"
                                        multiple data-placeholder="Select or enter email(s)">
                                        @foreach ($users as $user)
                                            <option value="{{ $user->email }}">{{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" id="mail_remarks" class="form-control mail_modal cannot_disable"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="reset" class="btn btn-outline-secondary me-1"
                                onclick = "closeModal('sendMail');">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <!-- BEGIN: Dashboard Custom Code JS-->
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        var selectedItem = @json($selectedItem);
        const documentStatusCssList = @json($statusCss);
        var selectedItemId = '';
        var subStoreId = '';
        var storeId = '';
        var subStoreLocType = @json($subStoreLocType);
        window.routes = {
            poReport: @json(route('inventory-report.detail.filter')),
            detailedReports: '/get-stock-ledger-reports',
            addScheduler: @json(route('inventory-report.add.scheduler')),
        };
        document.addEventListener('DOMContentLoaded', function() {
            let filterData = {};
            const currentDate = new Date();
            const currentMonth = currentDate.getMonth();
            const currentYear = currentDate.getFullYear();
            let startOfFinancialYear;
            if (currentMonth < 3) {
                startOfFinancialYear = new Date(currentYear - 1, 3, 1);
            } else {
                startOfFinancialYear = new Date(currentYear, 3, 1);
            }
            const formatStartDate = startOfFinancialYear.toISOString().split('T')[0];
            let startDate = new Date(formatStartDate);
            startDate.setDate(startDate.getDate() + 1);
            const formattedStartDate = startDate.toISOString().split('T')[0];
            startDate = formattedStartDate;
            endDate = new Date().toISOString().split('T')[0];
            let displayStartDate = flatpickr.formatDate(new Date(startDate), "d-m-Y");
            let displayEndDate = flatpickr.formatDate(new Date(endDate), "d-m-Y");
            mailstartDate = startDate;
            mailendDate = endDate;
            const urlParams = getURLParams();
            setDropdownValues(urlParams);

            document.getElementById("applyFiltersBtn").addEventListener("click", function () {
                const queryParams = new URLSearchParams();

                const fields = [
                    "Period", "item", "store_id", "sub_store_id",
                    "stock_type", "book_type_id", "type_of_stock_id", "doc_no"
                ];

                fields.forEach((fieldId) => {
                    const el = document.getElementById(fieldId);
                    if (el) {
                        // Special case for item autocomplete
                        if (fieldId === "item") {
                            const itemId = el.getAttribute("data-id");
                            if (itemId) {
                                queryParams.set("item", itemId);
                            }
                        } else if (el.value) {
                            queryParams.set(fieldId, el.value);
                        }
                    }
                });


                if (filterData.attributes && Array.isArray(filterData.attributes)) {
                    filterData.attributes.forEach(attr => {
                        if (attr.groupId && attr.val) {
                            queryParams.append('attribute_name[]', attr.groupId);
                            queryParams.append('attribute_value[]', attr.val);
                        }
                    });
                }

                const newUrl = `${window.location.pathname}?${queryParams.toString()}`;
                window.location.href = newUrl;
            });

            function getURLParams() {
                const params = new URLSearchParams(window.location.search);
                let paramObj = {};
                params.forEach((value, key) => {
                    paramObj[key] = value;
                });
                return paramObj;
            }

            function setDropdownValues(params) {
                Object.keys(params).forEach(key => {
                    let element = document.getElementById(key);
                    if (element) {
                        let paramValue = params[key];

                        if (key === "item") {
                            $(element).attr("data-id", paramValue);
                            $.ajax({
                                type: 'GET',
                                data: { item_id: paramValue },
                                url: '/inventory-reports/single-item',
                                success: function (data) {
                                    if (data && data.name) {
                                        element.value = data.name;
                                    }
                                }
                            });
                            return;
                        }

                        if (key === "store_id") {
                            storeId = paramValue;
                            $('#store_id').val(paramValue).trigger('change');
                            $.ajax({
                                type: 'GET',
                                data: { store_id: paramValue, types: subStoreLocType },
                                url: '/sub-stores/store-wise',
                                success: function (data) {
                                    $('#sub_store_id').empty().append('<option value="">Select</option>');
                                    $.each(data.data, function (index, item) {
                                        $('#sub_store_id').append(`<option value="${item.id}">${item.name}</option>`);
                                    });
                                    if (params['sub_store_id']) {
                                        $('#sub_store_id').val(params['sub_store_id']).trigger('change');
                                    }
                                }
                            });
                            return;
                        }

                        if (element.tagName.toLowerCase() === "select") {
                            let option = element.querySelector(`option[value="${paramValue}"]`);
                            if (option) option.selected = true;
                        } else {
                            element.value = paramValue;
                        }

                        if ($(element).hasClass("select2")) {
                            $(element).val(paramValue).trigger('change');
                        }
                    }
                });
            }

            function formatDate(dateStr) {
                const date = new Date(dateStr);
                return date.toLocaleDateString("en-GB");
            }
            const tbody = document.getElementById('inventory-tbody');
            const records = @json($records);

            function calculateBalance(records, startDate, endDate) {
                let before_bal = {};
                let after_bal = {};
                let before_value = {};
                let after_value = {};
                let openingBalance = 0;
                let closingBalance = 0;
                let openingValue = 0;
                let closingValue = 0;
                const start = new Date(startDate);
                const end = new Date(endDate);

                const allowedStatuses = ['approved', 'approval_not_required', 'posted'];
                const beforeStartDate = records.filter(record =>
                    new Date(record.document_date) < start
                    // &&
                    // allowedStatuses.includes(record.document_status)
                );
                const beforeEndDate = records.filter(record => {
                    const recordDate = new Date(record.document_date);
                    return (
                        recordDate >= start &&
                        recordDate <= end
                        // &&
                        // allowedStatuses.includes(record.document_status)
                    );
                });
                beforeStartDate.forEach((brecord, index) => {
                    if (!before_bal[index]) {
                        before_bal[index] = 0;
                    }
                    if (!before_value[index]) {
                        before_value[index] = 0;
                    }

                    if (brecord.transaction_type === "receipt") {
                        before_bal[index] += parseFloat(brecord.receipt_qty);
                        before_value[index] += parseFloat(brecord.receipt_org_currency_cost);
                    } else if (brecord.transaction_type === "issue") {
                        before_bal[index] -= parseFloat(brecord.issue_qty);
                        before_value[index] -= parseFloat(brecord.issue_org_currency_cost);
                    }
                    openingBalance += before_bal[index];
                    openingValue += before_value[index];
                });
                beforeEndDate.forEach((arecord, index) => {
                    if (!after_bal[index]) {
                        after_bal[index] = openingBalance || 0;
                    }
                    if (!after_value[index]) {
                        after_value[index] = openingValue || 0;
                    }

                    if (arecord.transaction_type === "receipt") {
                        after_bal[index] += parseFloat(arecord.receipt_qty);
                        after_value[index] += parseFloat(arecord.receipt_org_currency_cost);
                    } else if (arecord.transaction_type === "issue") {
                        after_bal[index] -= parseFloat(arecord.issue_qty);
                        after_value[index] -= parseFloat(arecord.issue_org_currency_cost);
                    }
                    closingBalance += after_bal[index];
                    closingValue += after_value[index];
                });
                return {
                    openingBalance,
                    closingBalance,
                    openingValue,
                    closingValue
                };
            }

            function updateTable(recordsToDisplay, startDate, endDate) {
                tbody.innerHTML = '';
                const {
                    openingBalance,
                    closingBalance,
                    openingValue,
                    closingValue
                } = calculateBalance(recordsToDisplay, startDate, endDate);

                function getBalanceColor(balance) {
                    return balance < 0 ? 'text-danger' : 'text-primary'; // Red if negative, blue if positive
                }

                // Add the opening balance row first
                const openingBalanceRow = document.createElement("tr");
                openingBalanceRow.innerHTML = `
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="fw-bolder no-wrap text-end ${getBalanceColor(openingBalance)}" style="width: 100%;">Opening Balance</td>
                <td></td>
                <td></td>
                <td class="fw-bolder no-wrap text-end ${getBalanceColor(openingBalance)}" style="width: 100%;">Quantity: ${openingBalance}</td>
                <td></td>
                <td class="fw-bolder no-wrap text-end ${getBalanceColor(openingValue)}" style="width: 100%;">Value: ${openingValue}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                `;
                tbody.appendChild(openingBalanceRow); // Append the opening balance row

                // Filter records to only show those with document_date after formattedStartDate
                const filteredRecords = recordsToDisplay.filter(report => {
                    const documentDate = new Date(report.document_date);
                    return documentDate >= new Date(startDate) && documentDate <= new Date(endDate);
                });
                let totalReceiptQty = 0.00;
                let totalIssueQty = 0.00;
                let totalReceiptValue = 0.00;
                let totalIssueValue = 0.00;
                // Add each record to the table
                filteredRecords.forEach((report, index) => {
                    const tr = document.createElement("tr");
                    let attributesHTML = "";
                    try {
                        const itemAttributes = JSON.parse(report.item_attributes);
                        if (Array.isArray(itemAttributes) && itemAttributes.length > 0) {
                            attributesHTML = itemAttributes.map(attr => {
                                const attributeName = attr.attribute_name ?? "";
                                const attributeValue = attr.attribute_value ?? "";
                                return `<span class="badge rounded-pill badge-light-secondary badgeborder-radius">
                                ${attributeName}: ${attributeValue}
                            </span>`;
                            }).join("");
                        }
                    } catch (error) {
                        console.log("Error parsing item_attributes:", error);
                    }

                    totalReceiptQty += parseFloat(report?.receipt_qty ?? "0") || 0;
                    totalIssueQty += parseFloat(report?.issue_qty ?? "0") || 0;
                    totalReceiptValue += parseFloat(report?.transaction_type === 'receipt' ? report
                        ?.receipt_org_currency_cost ?? "0" : "0") || 0;
                    totalIssueValue += parseFloat(report?.transaction_type === 'issue' ? report
                        ?.issue_org_currency_cost ?? "0" : "0") || 0;

                    // Define the table row content for each record
                    const cells = [
                        `<td>${index + 1}</td>`,
                        `<td class="no-wrap ">${formatDate(report.document_date)}</td>`,
                        `<td class="no-wrap">${report.book.book_code}</td>`,
                        `<td class="no-wrap">${report.document_number}</td>`,
                        `<td class="no-wrap">${report?.book_type ?? ""}</td>`,
                        `<td class="no-wrap">${report?.item?.item_code ?? ""}</td>`,
                        `<td class="no-wrap">
                            ${
                                report?.stock_type === "W" && report?.wip_station_id
                                    ? (report?.item?.item_name ?? "") + " - " + (report?.wip_station?.name ?? "")
                                    : (report?.item?.item_name ?? "")
                            }
                        </td>`,
                        `<td class='no-wrap'>
                            <div style="white-space: normal;">
                                ${attributesHTML}
                            </div>
                        </td>`,
                        `<td class="no-wrap">${report?.location?.store_name ?? ""}</td>`,
                        `<td class="no-wrap">${report?.store?.name ?? ""}</td>`,
                        `<td class="no-wrap">${report?.station?.name ?? ""}</td>`,
                        `<td class="no-wrap">${report?.inventory_uom?.name ?? ""}</td>`,
                        `<td class="no-wrap">${report?.stock_type === "R" ? "Regular" : report?.stock_type === "W" ? "WIP" : report?.stock_type === "S" ? "Sub Standard": report?.stock_type === "J" ? "Rejected": ""}</td>`,
                        `<td class="no-wrap">${report?.so?.book_code ?? ""}-${report?.so?.document_number ?? ""}</td>`,
                        `<td class="no-wrap">${report?.lot_number ?? ""}</td>`,
                        `<td class='no-wrap text-end'>${parseFloat(report?.org_currency_cost_per_unit) ?? 0.00}</td>`,
                        `<td class='no-wrap text-end'>${report?.receipt_qty ?? 0.00}</td>`,
                        `<td class='no-wrap text-end'>${report?.issue_qty ?? 0.00}</td>`,
                        `<td class='no-wrap text-end'>
                        ${report?.transaction_type === 'receipt' ? report?.receipt_org_currency_cost ?? 0.00 : 0.00}
                        </td>`,
                        `<td class='no-wrap text-end'>
                        ${report?.transaction_type === 'issue' ? report?.issue_org_currency_cost ?? 0.00 : 0.00}
                        </td>`,
                        `<td class='no-wrap text-end'>
                        ${report?.reserved_qty ?? 0.00}
                        </td>`,
                        `<td class='no-wrap text-end'>
                        ${report?.putaway_pending_qty ?? 0.00}
                        </td>`,
                        `<td class="no-wrap">
                            ${report?.document_status ? (
                                documentStatusCssList[report.document_status]
                                    ? `<span class='badge ${documentStatusCssList[report.document_status]}'>
                                            ${report.document_status.charAt(0).toUpperCase() + report.document_status.slice(1).toLowerCase()}
                                    </span>`
                                    : `<span class='badge default-status-class'>
                                            ${report.document_status.charAt(0).toUpperCase() + report.document_status.slice(1).toLowerCase()}
                                    </span>`
                            ) : (
                                `<span class='badge default-status-class'>N/A</span>`
                            )}
                        </td>`,
                    ];

                    tr.innerHTML = cells.join("");
                    tbody.appendChild(tr); // Append each report row
                });
                // Add the total receipt quantity  and issue qunatity total
                const totalQtyRow = document.createElement("tr");
                totalQtyRow.innerHTML = `
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="fw-bolder text-end ${getBalanceColor(totalReceiptQty)}" style="width: 100%;">Total: ${totalReceiptQty}</td>
                <td class="fw-bolder text-end ${getBalanceColor(totalIssueQty)}" style="width: 100%;">Total: ${totalIssueQty}</td>
                <td class="fw-bolder text-end ${getBalanceColor(totalReceiptValue)}" style="width: 100%;">Total: ${totalReceiptValue}</td>
                <td class="fw-bolder text-end ${getBalanceColor(totalIssueValue)}" style="width: 100%;">Total: ${totalIssueValue}</td>
                <td></td>
                <td></td>
                <td></td>
                `;
                tbody.appendChild(totalQtyRow);
                // Add the closing balance row below the total issue quantity row
                const closingBalanceRow = document.createElement("tr");
                closingBalanceRow.innerHTML = `
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="fw-bolder no-wrap text-end ${getBalanceColor(closingValue)}" style="width: 100%;">Closing Balance</td>
                <td></td>
                <td></td>
                <td class="fw-bolder no-wrap text-end ${getBalanceColor(closingBalance)}" style="width: 100%;">Quantity: ${closingBalance}</td>
                <td></td>
                <td class="fw-bolder no-wrap text-end ${getBalanceColor(closingValue)}" style="width: 100%;">Value: ${closingValue}</td>
                <td></td>
                <td></td>
                <td></td>
                `;
                tbody.appendChild(closingBalanceRow);
            }

            let customInput = document.getElementById("Custom");
            if (!customInput) return;

            customInput.style.width = "100%";
            customInput.style.padding = "0.375rem 0.75rem";
            customInput.style.border = "1px solid #ced4da";
            customInput.style.borderRadius = "0.25rem";
            customInput.value = displayStartDate + " to " + displayEndDate;

            // Initialize Flatpickr on the input
            let datepicker = flatpickr(customInput, {
                mode: "range",
                dateFormat: "d-m-Y",
                defaultDate: [displayStartDate, displayEndDate],
                onClose: function(selectedDates) {
                    if (selectedDates.length > 0) {
                        startDate = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                        endDate = selectedDates.length > 1 ? flatpickr.formatDate(selectedDates[1],
                            "Y-m-d") : startDate;
                        displayStartDate = flatpickr.formatDate(selectedDates[0], "d-m-Y");
                        displayEndDate = selectedDates.length > 1 ? flatpickr.formatDate(selectedDates[
                            1], "d-m-Y") : displayStartDate;
                        customInput.value = displayStartDate + (displayStartDate !== displayEndDate ?
                            " to " + displayEndDate : "");
                        mailstartDate = startDate;
                        mailendDate = endDate;
                        updateTable(records, startDate, endDate);
                    }
                }
            });

            let customLabel = document.querySelector('label[for="Custom"]');
            if (customLabel) {
                customLabel.addEventListener("click", function() {
                    datepicker.open();
                });
            }
            customInput.addEventListener("click", function() {
                datepicker.open();
            });
            document.querySelectorAll('input[name="Period"]').forEach((radio) => {
                radio.addEventListener("change", function(event) {
                    if (this.id === "Custom") {
                        datepicker.open();
                    }
                });
            });

            updateTable(records, startDate, endDate);
            feather.replace();

            function updateFilterAndFetch() {
                if (Object.keys(filterData).length > 0) {
                    const params = new URLSearchParams();
                    if (Object.keys(filterData).length > 0) {
                        Object.entries(filterData).forEach(([key, value]) => {
                            if (Array.isArray(value)) {
                                value.forEach(attr => {
                                    params.append('attribute_name[]', attr.groupId);
                                    params.append('attribute_value[]', attr.val);
                                });
                            } else if (value) {
                                params.append(key, value);
                            }
                        });
                    }
                }
            }

            function handleFilterChange(selector, key) {
                $(selector).on('change', function() {
                    if (key == 'item') {
                        filterData[key] = $(this).attr('data-id');
                    } else {
                        filterData[key] = $(this).val();
                    }
                    updateFilterAndFetch();
                });
            }
            // Attach change events to filters
            handleFilterChange('#item', 'item');
            handleFilterChange('#store_id', 'store_id');
            handleFilterChange('#sub_store_id', 'sub_store_id');
            handleFilterChange('#type_of_stock_id', 'type_of_stock_id');
            handleFilterChange('#stock_type', 'stock_type');
            handleFilterChange('#doc_no', 'doc_no');

            $('#attribute-button').click(function() {
                filterData.attributes = $('.custnewpo-detail select, .custnewpo-detail input')
                    .map((_, item) => ({
                        groupId: $(item).data('attr-group-id'),
                        val: $(item).val()
                    }))
                    .get();
                updateFilterAndFetch();
            });
            async function fetchPurchaseOrders(filterData = {}, startDate, endDate) {
                try {
                    let columnOrderList = filterData.columnOrder || getColumnVisibilitySettings();
                    delete filterData.columnOrder;
                    const params = new URLSearchParams();
                    let itemValue = $("#item").attr('data-id');
                    let storeIdValue = $("#store_id").val();
                    let subStoreIdValue = $("#sub_store_id").val();
                    let stockTypeValue = $("#stock_type").val();
                    let typeOfStockIdIdValue = $("#type_of_stock_id").val();
                    let docNo = $("#doc_no").val();

                    if (itemValue && !filterData.hasOwnProperty('item')) {
                        params.append('item', itemValue);
                    }
                    if (storeIdValue && !filterData.hasOwnProperty('store_id')) {
                        params.append('store_id', storeIdValue);
                    }
                    if (subStoreIdValue && !filterData.hasOwnProperty('sub_store_id')) {
                        params.append('sub_store_id', subStoreIdValue);
                    }
                    if (stockTypeValue && !filterData.hasOwnProperty('stock_type')) {
                        params.append('stock_type', stockTypeValue);
                    }
                    if (typeOfStockIdIdValue && !filterData.hasOwnProperty('type_of_stock_id')) {
                        params.append('type_of_stock_id', typeOfStockIdIdValue);
                    }
                    if (docNo && !filterData.hasOwnProperty('doc_no')) {
                        params.append('doc_no', docNo);
                    }

                    Object.entries(filterData).forEach(([key, value]) => {
                        if (Array.isArray(value)) {
                            value.forEach(attr => {
                                params.append('attribute_name[]', attr.groupId);
                                params.append('attribute_value[]', attr.val);
                            });
                        } else if (value) {
                            params.append(key, value);
                        }
                    });
                    const url = `${window.routes.poReport}?${params.toString()}`;

                    window.history.pushState({}, '', url);
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    const data = await response.json();
                    if (Array.isArray(data)) {
                        updateTable(data, startDate, endDate);
                    } else {
                        console.error("Unexpected response format:", data);
                    }
                } catch (error) {
                    console.error("Error fetching purchase orders:", error);
                }
            }
            $('.attributeBtn').on('click', function() {
                let attributes_check = 0;
                let itemValue = $("#item").attr('data-id');
                getItemAttribute(itemValue);
                $('.attributeBtn').show();
                updateFilterAndFetch();
            });

            function checkAttribute(itemValue) {
                filterData.attributes_check = 0;
                $('.attributeBtn').show().prop('disabled', false);
            }
            async function getItemAttribute(itemId) {
                const actionUrl = `{{ route('inventory-report.item.attr') }}?item_id=${itemId}`;
                const response = await fetch(actionUrl);
                const data = await response.json();
                if (data.status === 200 && data.data.html) {$('#item').val()
                    $("#attribute tbody").html(data.data.html);
                    const isItemSelected = $('#item').val() !== '';
                    $('#store_id, #sub_store_id').prop(
                        'disabled', !
                        isItemSelected);
                    $("#attribute").modal('show');
                }
            }

            function getColumnVisibilitySettings() {
                const columnVisibility = [];
                $(".sortable .form-check-input").each(function() {
                    columnVisibility.push({
                        id: $(this).attr("id"),
                        visible: $(this).is(":checked"),
                    });
                });
                $(".sortable .aging-visibility").each(function() {
                    let visibleId = $(this).attr("id");
                    let visibleValue = ($(this).val() == 1) ? 1 : 0;
                    columnVisibility.push({
                        id: visibleId,
                        visible: visibleValue ? true : false,
                    });
                });
                return columnVisibility;
            }
            $(document).on('change', '.store_code', function() {
                var store_code_id = $(this).val();
                if (store_code_id) {
                    setTimeout(() => {
                        $('#store_id').val(store_code_id).trigger('change.select2');
                    }, 10);
                    var data = {
                        store_id: store_code_id,
                        types: subStoreLocType,
                    };
                    $.ajax({
                        type: 'GET',
                        data: data,
                        url: '/sub-stores/store-wise',
                        success: function(data) {
                            $('#sub_store_id').empty();
                            $('#sub_store_id').append('<option value="">Select</option>');
                            $.each(data.data, function(index, item) {
                                $('#sub_store_id').append('<option value="' + item.id +
                                    '">' + item.name + '</option>');
                            });
                            $('#sub_store_id').trigger('change');
                        }
                    });
                } else {
                    $('#sub_store_id').empty();
                    $('#sub_store_id').append('<option value="">Select</option>');
                    $('#sub_store_id').trigger('change');
                }
            });

            function initializeAutocomplete(selector, type) {
                $(selector).autocomplete({
                    minLength: 0,
                    source: function(request, response) {
                        $.ajax({
                            url: '/search',
                            method: 'GET',
                            dataType: 'json',
                            data: {
                                q: request.term,
                                type: type
                            },
                            success: function(data) {
                                response($.map(data, function(item) {
                                    return {
                                        id: item.id,
                                        label: item.item_name,
                                        code: item.item_code
                                    };
                                }));
                            },
                            error: function(xhr) {
                                console.error('Error fetching item data:', xhr
                                    .responseText);
                            }
                        });
                    },
                    select: function(event, ui) {
                        var $input = $(this);
                        var itemName = ui.item.label;
                        var itemId = ui.item.id;
                        var itemCode = ui.item.code;

                        $input.val(itemName);
                        $input.attr('data-name', itemName);
                        $input.attr('data-code', itemCode);
                        $input.attr('data-id', itemId);
                        $input.attr('value', itemId);
                        checkAttribute(itemId);
                        getItemAttribute(itemId);
                    },
                    change: function(event, ui) {
                        if (!ui.item) {
                            $(this).val('');
                            $(this).attr('data-name', '');
                            $(this).attr('data-id', '');
                        }
                    }
                }).focus(function() {
                    if (this.value === "") {
                        $(this).autocomplete("search", "");
                    }
                });
            }

            initializeAutocomplete(".inventory_items", "inventory_items");

            $('.select2-email').select2({
                tags: true,
                tokenSeparators: [',', ' '],
                placeholder: "Select or enter email(s)",
                width: '100%',
                createTag: function(params) {
                    var term = $.trim(params.term);
                    // Basic email format validation
                    if (term.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                        return {
                            id: term,
                            text: term,
                            newTag: true
                        };
                    }
                    return null;
                }
            });

            $('.select2-cc').select2({
                tags: true,
                tokenSeparators: [',', ' '],
                placeholder: "Select or enter email(s)",
                width: '100%',
                createTag: function(params) {
                    var term = $.trim(params.term);
                    // Basic email format validation
                    if (term.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                        return {
                            id: term,
                            text: term,
                            newTag: true
                        };
                    }
                    return null;
                }
            });

            $(document).on('submit', '#send-mail', function(e) {

                e.preventDefault();

                const table = $(".datatables-basic").DataTable();

                const displayedDataArray = [];

                table.rows({ search: 'applied' }).nodes().each(function(row) {

                    const rowValues = [];
                    $(row).find('td').each(function() {
                        rowValues.push($(this).text().trim());
                    });
                    displayedDataArray.push(rowValues);
                });

                const displayedHeaders = [];
                $(table.columns().header()).each(function() {
                    displayedHeaders.push($(this).text().trim());
                });
                const formData = {
                    email_to: $('select[name="email_to[]"]').val(),
                    email_cc: $('select[name="email_cc[]"]').val(),
                    remarks: $('#mail_remarks').val(),
                    displayedData: displayedDataArray,
                    displayedHeaders: displayedHeaders,
                    filter_json: getURLParams(),
                    start_date: mailstartDate,
                    end_date: mailendDate,
                    report_type: 'detailed',
                    _token: $('input[name="_token"]').val()
                };

                $.ajax({
                    url: window.routes.addScheduler,
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(formData),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: response.success || 'Email sent successfully!',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        $('select[name="email_to[]"]').val([]).trigger('change');
                        $('select[name="email_cc[]"]').val(null).trigger('change');
                        $('textarea[name="remarks"]').val('');
                        $('#sendMail').modal('hide');
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error sending email',
                            text: xhr.responseJSON?.message || 'Something went wrong.'
                        });
                    }
                });
            });

        });

        function sendMailTo() {
            $('.ajax-validation-error-span').remove();
            $('.reset_mail').removeClass('is-invalid');
            $('.mail_modal').prop('readonly', false);
            $('.mail_modal').prop('disabled', false);
            $('[name="cc_to[]"]').prop('disabled', false);
            $('[name="cc_to[]"]').prop('readonly', false);

            const emailInput = document.getElementById('cust_mail');
            const header = document.getElementById('send_mail_heading_label');
            if (header) {
                header.innerHTML = "Send Mail";
            }
            $("#mail_remarks").val("Your Mail has been sent successfully.");
            $('#sendMail').modal('show');
        }
        function toggleFilterSidebar() {
            const sidebar = document.getElementById('filterSidebar');
            if (sidebar.style.right === '0px') {
                sidebar.style.right = '-300px';
            } else {
                sidebar.style.right = '0px';
            }
        }
    </script>
@endsection
