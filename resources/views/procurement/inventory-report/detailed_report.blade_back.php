@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-body">
                <div id="message-area">
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
                </div>
                <section id="basic-datatable">
                    <div class="card border  overflow-hidden">
                        <div class="row">
                            <div class="col-md-12 bg-light border-bottom mb-1 po-reportfileterBox">
                                <div class="row pofilterhead action-button align-items-center">
                                    <div class="col-md-4">
                                        <h3>Detailed Report</h3>
                                        <p>Apply the Filter</p>
                                    </div>
                                    <div
                                        class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                        <div class="btn-group new-btn-group my-1 my-sm-0 ps-0">
                                            <input type="radio"
                                                class="btn-check form-control flatpickr-range flatpickr-input"
                                                name="Period" id="Custom" />
                                            <label class="btn btn-outline-primary mb-0 ml-1" for="Custom">Custom</label>
                                        </div>
                                        <div class="btn-group new-btn-group my-1 my-sm-0 ps-0">
                                            <a href="/inventory-reports" type="button"
                                                class=" btn btn-warning ">Back</a>
                                        </div>
                                    </div>
                                </div>
                                <form action ='/inventory-reports/get-stock-ledger-reports' method="GET" >
                                    <div class="customernewsection-form poreportlistview p-1">
                                        <div class="row">
                                            <div class="col">
                                                <div class="mb-1 mb-sm-0">
                                                    <select class="form-select select2" id="item">
                                                        <option value="">Select Item</option>
                                                        @foreach ($items as $item)
                                                            <option value="{{ $item->id }}">{{ $item->item_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-1 mb-sm-0">
                                                    <button type="button" class="btn btn-primary btn-md mb-0 waves-effect attributeBtn" style="background:#fff !important;border:1px solid #6e6b7b59 !important;color:black  !important;" disabled>Attributes</button>
                                                </div>
                                            </div>
                                            <div class="col store_id">
                                                <div class="mb-1 mb-sm-0">
                                                    <select class="form-select mw-100 select2 store_code" name="store_id" id="store_id" disabled>
                                                        <option value="">Select Store</option>
                                                        @foreach($erpStores as $val)
                                                            <option value="{{$val->id}}">
                                                                {{$val->store_code}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col rack_id">
                                                <div class="mb-1 mb-sm-0">
                                                    <select class="form-select mw-100 select2 rack_code" name="rack_id" id="rack_id" disabled>
                                                        <option value="">Select Rack</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col shelf_id">
                                                <div class="mb-1 mb-sm-0">
                                                    <select class="form-select mw-100 select2 shelf_code" name="shelf_id" id="shelf_id" disabled>
                                                        <option value="">Select Shelf</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col bin_id">
                                                <div class="mb-1 mb-sm-0">
                                                    <select class="form-select mw-100 select2 bin_code" name="bin_id" id="bin_id" disabled>
                                                        <option value="">Select Bin</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col pe-0">
                                                <div class="mb-1 mb-sm-0">
                                                    <a href="/inventory-reports/get-stock-ledger-reports" type="button" class="btn btn-warning btn-md mb-0 waves-effect">Clear</a>
                                                </div>
                                            </div>
                                            <div class="col pe-0">
                                                <div class="mb-1 mb-sm-0">
                                                    <button type="submit" class="btn btn-primary btn-md mb-0 waves-effect">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-12" style="min-height: 300px">
                                <div class="table-responsive trailbalnewdesfinance po-reportnewdesign my-class">
                                    <table class="my-table datatables-basic table myrequesttablecbox">
                                        <thead>
                                            <th class="no-wrap">S.No</th>
                                            <th class="no-wrap">Doc. Date</th>
                                            {{-- <th class="no-wrap">Doc. Series</th> --}}
                                            <th class="no-wrap">Doc. No</th>
                                            <th class="no-wrap">Doc. Type</th>
                                            <th class="no-wrap">Item Code</th>
                                            <th class="no-wrap">Item Name</th>
                                            <th class="no-wrap">Attributes</th>
                                            <th class="no-wrap">Location</th>
                                            <th class="no-wrap">Rack</th>
                                            <th class="no-wrap">Shelf</th>
                                            <th class="no-wrap">Bin</th>
                                            <th class='no-wrap text-end'>Receipt Quantity</th>
                                            <th class='no-wrap text-end'>Issue Quantity</th>
                                            <th class='no-wrap text-end'>Receipt Value</th>
                                            <th class='no-wrap text-end'>Issue Value</th>
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
                    <button type="button" id="attribute-button" data-bs-dismiss="modal" class="btn btn-primary attribute-button">Select</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <!-- BEGIN: Dashboard Custom Code JS-->
    <script src="{{ asset('assets/js/custom/inventory-report.js') }}"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            feather.replace();
            let filterData = {};

            function updateFilterAndFetch() {
                if (Object.keys(filterData).length > 0) {
                    fetchPurchaseOrders(filterData);
                }
            }

            function handleFilterChange(selector, key) {
                $(selector).on('change', function () {
                    filterData[key] = $(this).val();
                    updateFilterAndFetch();
                });
            }

            // Attach change events to filters
            handleFilterChange('#item', 'item');
            handleFilterChange('#store_id', 'store_id');
            handleFilterChange('#rack_id', 'rack_id');
            handleFilterChange('#shelf_id', 'shelf_id');
            handleFilterChange('#bin_id', 'bin_id');

            $('#item').on('change', function () {
                checkAttribute($(this).val());
                getItemAttribute($(this).val());
            });

            $('#attribute-button').click(function () {
                filterData.attributes = $('.custnewpo-detail select, .custnewpo-detail input')
                    .map((_, item) => ({ groupId: $(item).data('attr-group-id'), val: $(item).val() }))
                    .get();
                updateFilterAndFetch();
            });

            async function fetchPurchaseOrders(filterData = {}) {
                try {
                    let columnOrderList = filterData.columnOrder || getColumnVisibilitySettings();
                    delete filterData.columnOrder;

                    const params = new URLSearchParams();
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
                    const response = await fetch(url);
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

                    const data = await response.json();
                    updateTable(data, columnOrderList);
                } catch (error) {
                    console.error("Error fetching purchase orders:", error);
                }
            }

            function checkAttribute(itemValue) {
                filterData.attributes_check = 0;
                $('.attributeBtn').show().prop('disabled', false);
            }

            async function getItemAttribute(itemId) {
                const actionUrl = `{{route("inventory-report.item.attr")}}?item_id=${itemId}`;
                const response = await fetch(actionUrl);
                const data = await response.json();

                if (data.status === 200) {
                    $("#attribute tbody").html(data.data.html);
                    const isItemSelected = $('#item').val() !== '';
                    $('#store_id, #rack_id, #shelf_id, #bin_id, .attributeBtn').prop('disabled', !isItemSelected);
                    $("#attribute").modal('show');
                }
            }
        });
    </script>

    <!-- <script>
        window.routes = {
            poReport: @json(route('inventory-report.detail.filter')),
        };
        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString("en-GB");
        }

        const tbody = document.getElementById('inventory-tbody');
        const records = @json($records);

        function calculateBalance(records, startDate, endDate) {
            let before_bal = {};
            let after_bal = {};
            let openingBalance = 0;
            let closingBalance = 0;

            const start = new Date(startDate);
            const end = new Date(endDate);
            const beforeStartDate = records.filter(record => new Date(record.document_date) < start);

            const beforeEndDate = records.filter(record => {
                const recordDate = new Date(record.document_date);
                return recordDate >= start && recordDate <= end;
            });

            beforeStartDate.forEach((brecord, index) => {
                if (!before_bal[index]) {
                    before_bal[index] = 0;
                }

                if (brecord.transaction_type === "receipt") {
                    before_bal[index] += parseFloat(brecord.receipt_qty);
                } else if (brecord.transaction_type === "issue") {
                    before_bal[index] -= parseFloat(brecord.issue_qty);
                }
                openingBalance += before_bal[index];
            });

            beforeEndDate.forEach((arecord, index) => {
                if (!after_bal[index]) {
                    after_bal[index] = openingBalance || 0;
                }

                if (arecord.transaction_type === "receipt") {
                    after_bal[index] += parseFloat(arecord.receipt_qty);
                } else if (arecord.transaction_type === "issue") {
                    after_bal[index] -= parseFloat(arecord.issue_qty);
                }
                closingBalance += after_bal[index];
            });

            return {
                openingBalance,
                closingBalance
            };
        }

        function updateTable(recordsToDisplay, startDate, endDate) {
            tbody.innerHTML = ''; // Clear the existing content

            // Get opening and closing balance
            const {
                openingBalance,
                closingBalance
            } = calculateBalance(recordsToDisplay, startDate, endDate);

            function getBalanceColor(balance) {
                return balance < 0 ? 'text-danger' : 'text-primary'; // Red if negative, blue if positive
            }

            // Add the opening balance row first
            const openingBalanceRow = document.createElement("tr");
            openingBalanceRow.innerHTML = `
                <td colspan="12"></td>
                <td class="fw-bolder no-wrap text-end ${getBalanceColor(openingBalance)}" style="width: 100%;">Opening Balance: ${openingBalance}</td>
                <td colspan="3"></td>
            `;
            tbody.appendChild(openingBalanceRow); // Append the opening balance row

            // Filter records to only show those with document_date after formattedStartDate
            const filteredRecords = recordsToDisplay.filter(report => {
                const documentDate = new Date(report.document_date);
                return documentDate >= new Date(startDate) && documentDate <= new Date(endDate);
            });
            let totalReceiptQty = 0.00;
            let totalIssueQty = 0.00;
            // Add each record to the table
            filteredRecords.forEach((report, index) => {
                const tr = document.createElement("tr");
                let attributesHTML = "N/A";
                try {
                    const itemAttributes = JSON.parse(report.item_attributes);
                    if (Array.isArray(itemAttributes) && itemAttributes.length > 0) {
                        attributesHTML = itemAttributes.map(attr => {
                            const attributeName = attr.attribute_name ?? "N/A";
                            const attributeValue = attr.attribute_value ?? "N/A";
                            return `<span class="badge rounded-pill badge-light-secondary badgeborder-radius">
                                ${attributeName}: ${attributeValue}
                            </span>`;
                        }).join("");
                    }
                } catch (error) {
                    console.log("Error parsing item_attributes:", error);
                }

                totalReceiptQty += parseFloat(report?.receipt_qty ?? 0.00);
                totalIssueQty += parseFloat(report?.issue_qty ?? 0.00);

                // Define the table row content for each record
                const cells = [
                    `<td>${index + 1}</td>`,
                    `<td class="no-wrap ">${formatDate(report.document_date)}</td>`,
                    // `<td class="no-wrap">${report.document_date}</td>`,
                    `<td class="no-wrap">${report.document_number}</td>`,
                    `<td class="no-wrap">${report?.book_type ?? "N/A"}</td>`,
                    `<td class="no-wrap">${report?.item_code ?? "N/A"}</td>`,
                    `<td class="no-wrap">${report?.item_name ?? "N/A"}</td>`,
                    `<td class="no-wrap">${attributesHTML}</td>`,
                    `<td class="no-wrap">${report?.store ?? "N/A"}</td>`,
                    `<td class="no-wrap">${report?.rack ?? "N/A"}</td>`,
                    `<td class="no-wrap">${report?.shelf ?? "N/A"}</td>`,
                    `<td class="no-wrap">${report?.bin ?? "N/A"}</td>`,
                    `<td class='no-wrap text-end'>${report?.receipt_qty ?? "N/A"}</td>`,
                    `<td class='no-wrap text-end'>${report?.issue_qty ?? "N/A"}</td>`,
                    `<td class='no-wrap text-end'>
                        ${report?.transaction_type === 'receipt' ? report?.org_currency_cost ?? 0.00 : 0.00}
                    </td>`,
                    `<td class='no-wrap text-end'>
                        ${report?.transaction_type === 'issue' ? report?.org_currency_cost ?? 0.00 : 0.00}
                    </td>`,
                    `<td class="no-wrap">${report?.document_status ?? "N/A"}</td>`,
                ];

                tr.innerHTML = cells.join("");
                tbody.appendChild(tr); // Append each report row
            });

            // Add the total receipt quantity  and issue qunatity total
            const totalQtyRow = document.createElement("tr");
            totalQtyRow.innerHTML = `
                <td colspan="11"></td>
                <td class="fw-bolder text-end ${getBalanceColor(totalReceiptQty)}" style="width: 100%;">Total: ${totalReceiptQty}</td>
                <td class="fw-bolder text-end ${getBalanceColor(totalIssueQty)}" style="width: 100%;">Total: ${totalIssueQty}</td>
                <td colspan="4"></td>
            `;
            tbody.appendChild(totalQtyRow);

            // Add the closing balance row below the total issue quantity row
            const closingBalanceRow = document.createElement("tr");
            closingBalanceRow.innerHTML = `
                <td colspan="12"></td>
                <td class="fw-bolder no-wrap text-end ${getBalanceColor(closingBalance)}" style="width: 100%;">Closing Balance: ${closingBalance}</td>
                <td colspan="3"></td>
            `;
            tbody.appendChild(closingBalanceRow);
        }

        document.querySelectorAll('input[name="Period"]').forEach((radio) => {
            radio.addEventListener('change', function(event) {
                let filteredRecords = records;
                if (this.id === 'Custom') {
                    let dateRange = document.getElementById('Custom').value;
                    if (dateRange) {
                        let dates = dateRange.split(' to ');
                        if (dates.length === 2) {
                            const startDate = dates[0].trim();
                            const endDate = dates[1].trim();
                            updateTable(filteredRecords, startDate, endDate);
                        }
                        updateTable(filteredRecords, dates[0].trim(), dates[0].trim())
                    }
                }
            });
        });

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

        updateTable(records, formattedStartDate, new Date().toISOString().split('T')[0]);

        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
            let filterData = {};
            // Helper function to call fetch if there are changes
            function updateFilterAndFetch() {
                if (Object.keys(filterData).length > 0) {
                    fetchPurchaseOrders(filterData);
                }
            }

            $('#item').on('change', function() {
                const itemValue = $(this).val();
                filterData.item = itemValue;
                checkAttribute(itemValue);
                getItemAttribute(itemValue);
            });

            $('#attribute_name').on('change', function() {
                const attributeName = $('#attribute_name').getAttribute('data-attr-group-id');
                const attributeValue = $(this).val();
                // Check the checkbox when a attributes is selected
                filterData.attribute_name = attributeName;
                filterData.attribute_value = attributeValue;
            });

            $('#attribute-button').click(function(){
                let arr = [];
                $('.custnewpo-detail select, .custnewpo-detail input').each((key, item) => {
                    let groupId = $(item).data('attr-group-id');
                    let val = $(item).val();
                    arr.push({groupId, val})
                })
                filterData.attributes = arr;
                updateFilterAndFetch();
            });

            $('#store_id').on('change', function() {
                const storeId = $(this).val();
                filterData.store_id = storeId;
                updateFilterAndFetch();
            });

            $('#rack_id').on('change', function() {
                const rackId = $(this).val();
                filterData.rack_id = rackId;
                updateFilterAndFetch();
            });

            $('#shelf_id').on('change', function() {
                const shelfId = $(this).val();
                filterData.shelf_id = shelfId;
                updateFilterAndFetch();
            });

            $('#bin_id').on('change', function() {
                const binId = $(this).val();
                filterData.bin_id = binId;
                updateFilterAndFetch();
            });

            // Check Uncheck Attributes
            $('#attributes').on('change', function() {
                let attributes_check = 0;
                if ($(this).is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    attributes_check = 1;
                    filterData.attributes_check = attributes_check;
                    // updateFilterAndFetch();
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    attributes_check = 0;
                    filterData.attributes_check = attributes_check;
                    updateFilterAndFetch();
                }
            });

            // Check Uncheck Store
            $('#store').on('change', function() {
                let store_check = 0;
                if ($(this).is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    store_check = 1;
                    filterData.store_check = store_check;
                    updateFilterAndFetch();
                    $('.store_id').css('display', 'block');
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    store_check = 0;
                    filterData.store_check = store_check;
                    updateFilterAndFetch();
                    $('.store_id').css('display', 'none');
                }
            });

            // Check Uncheck Rack
            $('#rack').on('change', function() {
                let rack_check = 0;
                if ($(this).is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    rack_check = 1;
                    filterData.rack_check = rack_check;
                    updateFilterAndFetch();
                    $('.rack_id').css('display', 'block');
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    rack_check = 0;
                    filterData.rack_check = rack_check;
                    updateFilterAndFetch();
                    $('.rack_id').css('display', 'none');
                }
            });

            // Check Uncheck Bin
            $('#bin').on('change', function() {
                let bin_check = 0;
                if ($(this).is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    bin_check = 1;
                    filterData.bin_check = bin_check;
                    updateFilterAndFetch();
                    $('.bin_id').css('display', 'block');
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    bin_check = 0;
                    filterData.bin_check = bin_check;
                    updateFilterAndFetch();
                    $('.bin_id').css('display', 'none');
                }
            });

            // Check Uncheck Shelf
            $('#shelf').on('change', function() {
                let shelf_check = 0;
                if ($(this).is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    shelf_check = 1;
                    filterData.shelf_check = shelf_check;
                    updateFilterAndFetch();
                    $('.shelf_id').css('display', 'block');
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    shelf_check = 0;
                    filterData.shelf_check = shelf_check;
                    updateFilterAndFetch();
                    $('.shelf_id').css('display', 'none');
                }
            });

            function checkAttribute(itemValue){
                let attributes_check = 0;
                    console.log('attribute unchecked', $('#attributes').is(':checked'));
                    // Handle the case where the checkbox is unchecked if needed
                    attributes_check = 0;
                    $('.attributeBtn').show();
                    $('#attributeBtn').prop('disabled', false);
                    filterData.attributes_check = attributes_check;
            }

            function getSelectedData() {
                let selectedData = [];
                $('select[name="to"] option:selected').each(function() {
                    selectedData.push({
                        id: $(this).val(),
                        type: $(this).data('type')
                    });
                });
                return selectedData;
            }

            $('.attributeBtn').on('click', function() {
                let attributes_check = 0;
                let itemValue = $("#item").val();
                    getItemAttribute(itemValue);
                    $('.attributeBtn').show();
                    updateFilterAndFetch();
            });

            /*For comp attr*/
            function getItemAttribute(itemId){
                let actionUrl = '{{route("inventory-report.item.attr")}}'+'?item_id='+itemId;
                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if (data.status == 200) {
                            $("#attribute tbody").empty();
                            $("#attribute table tbody").append(data.data.html);
                            const itemSelected = document.getElementById('item').value;
                            if (itemSelected !== '') {
                                document.querySelectorAll('#store_id, #rack_id, #shelf_id, #bin_id, .attributeBtn').forEach(function(element) {
                                    element.disabled = false;
                                });
                            } else {
                                // If no item is selected, keep all fields disabled
                                document.querySelectorAll('#store_id, #rack_id, #shelf_id, #bin_id, .attributeBtn').forEach(function(element) {
                                    element.disabled = true;
                                });
                            }
                            $("#attribute").modal('show');
                        }
                    });
                });
            }
        });
    </script> -->
@endsection
