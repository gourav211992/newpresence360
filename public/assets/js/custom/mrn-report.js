// /*=========================================================================================
//     File Name: mrn-report.js
//     Description: Material Reciept report page content with filter
//     ----------------------------------------------------------------------------------------
//     Item Name: Vuexy  - Vuejs, HTML & Laravel Admin Dashboard Template
// ==========================================================================================*/

// //------------ Filter data --------------------
// //---------------------------------------------

async function fetchPurchaseOrders(filterData = {}) {
    try {
        if (!filterData.columnOrder) {
            const columnOrder = getColumnVisibilitySettings();
            var columnOrderList = columnOrder;
        } else {
            var columnOrderList = filterData.columnOrder;
        }

        delete filterData.columnOrder;

        const params = new URLSearchParams();

        Object.entries(filterData).forEach(([key, value]) => {
            if (value) params.append(key, value);
        });

        var reportFilterUrl = window.routes.poReport;
        const url = `${reportFilterUrl}?${params.toString()}`;
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        // Call the function to update the table
        updateTable(data, columnOrderList || []);
    } catch (error) {
        console.error("Error fetching purchase orders:", error);
    }
}

// Function to update the table dynamically
function updateTable(mrn_reports = [], columnVisibility = []) {
    const tbody = document.querySelector("tbody");
    const thead = document.querySelector("thead");
    const dataTableSelector = ".datatables-basic";

    // Destroy existing DataTable instance if it exists
    if ($.fn.DataTable.isDataTable(dataTableSelector)) {
        const table = $(dataTableSelector).DataTable();
        table.destroy();
    }

    tbody.innerHTML = ""; // Clear existing table rows
    thead.innerHTML = ""; // Clear existing table headings

    // Create a mapping of column indices from visibility settings
    const visibleColumnIndices = columnVisibility
        .filter((column) => column.visible)
        .map((column) => { return getColumnIndexById(column.id) > 0 ? getColumnIndexById(column.id) : 0 })
        .filter((index) => index !== -1); // Ensure valid indices

    // // Ensure the Index (0) and Action (11) columns are always visible
    // if (!visibleColumnIndices.includes(0)) visibleColumnIndices.unshift(0); // Index column
    // if (!visibleColumnIndices.includes(17)) visibleColumnIndices.push(17); // Action column

    // Define table headers corresponding to the columns
    const tableHeaders = [
        `<th>S No</th>`, // Index
        `<th>Series</th>`, // Series
        `<th>MRN No</th>`, // MRN No
        `<th>MRN Date</th>`, // MRN Date
        `<th>PO No</th>`, // PO No
        `<th>Gate Entry No</th>`, // Gate Entry No
        `<th>SO No</th>`, // SO No
        `<th>LOT No</th>`, // LOT No
        `<th>Vendor</th>`, // Vendor
        `<th>Vendor Rating</th>`, // Vendor rating
        `<th>Category</th>`, // Category
        `<th>Sub Category</th>`, // Sub category
        `<th>Item type</th>`, // Item type
        `<th>Sub type</th>`, // Sub type
        `<th>Item</th>`, // Item Name
        `<th>Item Code</th>`, // Item Code
        `<th>Receipt Qty</th>`, // Order Quantity
        `<th>Accepted Qty</th>`, // Accepted Quantity
        `<th>Rejected Qty</th>`, // Rejected Quantity
        `<th>Purchase Return Qty</th>`, // Purchase Return Quantity
        `<th>Purchase Return Rejected Qty</th>`, // Purchase Return Rejected Quantity
        `<th>Purchase Bill Qty</th>`, // Purchase Bill Quantity
        `<th>Location</th>`, // Store
        `<th>Store</th>`, // Sub Store
        `<th>Rate</th>`, // Rate
        `<th>Basic Value</th>`, // Basic Value
        `<th>Item Discount</th>`, // Item Discount
        `<th>Header Discount</th>`, // Item Header Discount
        `<th>Item Amount</th>`, // MRN Amount
        `<th>Status</th>`, // Status
    ];

    // Generate table heading HTML based on visible columns
    const headingRow = document.createElement("tr");
    const headingHTML = visibleColumnIndices
        .map((index) => { return tableHeaders[index] || `<th></th>` })
        .join("");

    headingRow.innerHTML = headingHTML;
    thead.appendChild(headingRow); // Append the heading row to thead
    var counter = 1;
    // Loop through the purchase order data and update the table
    mrn_reports.forEach((report) => {
        report.items.forEach((mrn_item) => {
            const tr = document.createElement("tr");
            // Create table data cells based on column visibility

            total_item_value = ((mrn_item?.rate ?? 0.00) * (mrn_item?.accepted_qty ?? 0.00)) - (mrn_item?.discount_amount ?? 0.00);
            let po_no = report?.po?.book_code ? report.po.document_number : '';
            let ge_no = report?.gate_entry_no ?? '';
            let so_no = mrn_item?.so?.book_code ? mrn_item.so.document_number : '';
            let lot_no = report?.lot_number ?? '';


            const cells = [
                `<td>${counter++}</td>`, // Index
                `<td class="fw-bolder text-dark">${report.book_code}</td>`, // Series
                `<td class="fw-bolder text-dark">${report.document_number}</td>`, // MRN No
                `<td>${formatDate(report.document_date)}</td>`, // MRN Date
                `<td class="fw-bolder text-dark">${po_no ?? ""}</td>`, // PO No
                `<td class="fw-bolder text-dark">${ge_no}</td>`, // Gate Entry No
                `<td class="fw-bolder text-dark">${so_no || ""}</td>`, // SO No
                `<td class="fw-bolder text-dark">${lot_no}</td>`, // LOT No
                `<td>${report.vendor?.display_name ?? ""}</td>`, // Vendor
                `<td>${"vendor rating" ?? ""}</td>`, // Vendor rating
                `<td>${mrn_item?.item?.category?.name ?? ""}</td>`, // category
                `<td>${mrn_item?.item?.sub_category?.name ?? ""}</td>`, // Sub category
                `<td>${mrn_item?.item?.type ?? ""}</td>`, // item type
                `<td>${"sub type" ?? ""}</td>`, // sub type
                `<td>${mrn_item?.item?.item_name ?? ""}</td>`, // Item Name
                `<td>${mrn_item?.item?.item_code ?? ""}</td>`, // Item Code
                `<td>${Number(mrn_item?.order_qty ?? 0).toFixed(2)}</td>`, // Order Quantity
                `<td>${Number(mrn_item?.accepted_qty ?? 0).toFixed(2)}</td>`, // Accepted qty
                `<td>${Number(mrn_item?.rejected_qty ?? 0).toFixed(2)}</td>`, // Rejected qty
                `<td>${Number(mrn_item?.pr_qty ?? 0).toFixed(2)}</td>`, // Purchase Return qty
                `<td>${Number(mrn_item?.pr_rejected_qty ?? 0).toFixed(2)}</td>`, // Purchase Return Rejected qty
                `<td>${Number(mrn_item?.purchase_bill_qty ?? 0).toFixed(2)}</td>`, // Purchase Bill qty
                `<td>${mrn_item?.erp_store?.store_name ?? ''}</td>`, // Store
                `<td>${mrn_item?.sub_store?.name ?? ''}</td>`, // Sub Store
                `<td>${Number(mrn_item?.rate ?? 0).toFixed(2)}</td>`, // Rate
                `<td>${Number(mrn_item?.basic_value ?? 0).toFixed(2)}</td>`,// Basic Value
                `<td>${Number(mrn_item?.discount_amount ?? 0).toFixed(2)}</td>`, // Item Discount
                `<td>${Number(mrn_item?.header_discount_amount ?? 0).toFixed(2)}</td>`, // Item Header Discount
                `<td>${Number(total_item_value).toFixed(2)}</td>`, // Po amount
                `<td class="no-wrap">
                    ${documentStatusCssList[report?.document_status ?? ""] ?
                        `<span class='badge ${documentStatusCssList[report?.document_status ?? ""]}'>
                            ${report.document_status.charAt(0).toUpperCase() + report.document_status.slice(1).toLowerCase()}
                        </span>` :
                        `<span class='badge default-status-class'>${report.document_status.charAt(0).toUpperCase() + report.document_status.slice(1).toLowerCase()}</span>`
                    }
                    <a href="/material-receipts/edit/${report?.id}">
                        <i class="cursor-pointer" data-feather='eye'></i>
                    </a>
                </td>`,// Status
            ];

            // Construct row based on column visibility settings
            const rowHTML = visibleColumnIndices
                .map((index) => cells[index] || "<td></td>")
                .join("");

            tr.innerHTML = rowHTML;
            tbody.appendChild(tr);
            feather.replace();
        });
    });

    // Reinitialize DataTable after updating the table rows
    try {
        // Reinitialize DataTable after updating the table rows
        $(dataTableSelector).DataTable({
            order: [[0, "asc"]],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-3"l><"col-sm-12 col-md-6 withoutheadbuttin dt-action-buttons text-end pe-0"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 8,
            lengthMenu: [8, 10, 25, 50, 75, 100],
            buttons: [
                {
                    extend: "collection",
                    className: "btn btn-outline-secondary dropdown-toggle",
                    text:
                        feather.icons["share"].toSvg({
                            class: "font-small-3 me-50",
                        }) + "Export",
                    buttons: [
                        {
                            extend: "excel",
                            text:
                                feather.icons["file"].toSvg({
                                    class: "font-small-4 me-50",
                                }) + "Excel",
                            className: "dropdown-item",
                            filename: function () {
                                return (
                                    "MRN_Report_" +
                                    new Date().toISOString().slice(0, 10)
                                ); // Custom filename logic
                            },
                            exportOptions: {
                                columns: function (idx, data, node) {
                                    return true; // Include all columns
                                },
                            },
                        },
                        {
                            extend: "copy",
                            text:
                                feather.icons["mail"].toSvg({
                                    class: "font-small-4 me-50",
                                }) + "Mail",
                            className: "dropdown-item",
                            action: function (e, dt, button, config) {
                                var exportedData = dt.buttons.exportData(); // Get the export data
                                var dataToSend = JSON.stringify(exportedData); // Convert data to JSON

                                $.ajax({
                                    url: window.routes.reportSendMail, // Laravel route to send email
                                    type: "GET",
                                    success: function (response) {
                                        // Show success message
                                        const Toast = Swal.mixin({
                                            toast: true,
                                            position: "top-end",
                                            showConfirmButton: false,
                                            timer: 3000,
                                            timerProgressBar: true,
                                            didOpen: (toast) => {
                                                toast.onmouseenter =
                                                    Swal.stopTimer;
                                                toast.onmouseleave =
                                                    Swal.resumeTimer;
                                            },
                                        });
                                        Toast.fire({
                                            icon: "success",
                                            title: response.success,
                                        });
                                    },
                                    error: function (error) {
                                        const Toast = Swal.mixin({
                                            toast: true,
                                            position: "top-end",
                                            showConfirmButton: false,
                                            timer: 3000,
                                            timerProgressBar: true,
                                            didOpen: (toast) => {
                                                toast.onmouseenter =
                                                    Swal.stopTimer;
                                                toast.onmouseleave =
                                                    Swal.resumeTimer;
                                            },
                                        });
                                        Toast.fire({
                                            icon: "error",
                                            title: error.responseJSON.message,
                                        });
                                    },
                                });
                            },
                        },
                    ],
                    init: function (api, node, config) {
                        $(node).removeClass("btn-secondary");
                        $(node).parent().removeClass("btn-group");
                        setTimeout(function () {
                            $(node)
                                .closest(".dt-buttons")
                                .removeClass("btn-group")
                                .addClass("d-inline-flex");
                        }, 50);
                    },
                },
            ],
            language: {
                search: "",
                searchPlaceholder: "Search...",
                paginate: {
                    // remove previous & next text from pagination
                    previous: "&nbsp;",
                    next: "&nbsp;",
                },
            },
        });
    } catch (error) {
        console.error("Error initializing DataTable:", error);
    }

    $('[data-bs-toggle="tooltip"]').tooltip();
}

// Helper function to format date
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString("en-GB"); // Format as dd-mm-yyyy
}

function resetfilterdata(){

}

/**
 * Get the current order of columns based on their checkboxes.
 * @returns {Array} columnOrder - Array of column IDs in their current order
 */
function getColumnVisibilitySettings() {
    const columnVisibility = [];
    $(".sortable .form-check-input").each(function () {
        columnVisibility.push({
            id: $(this).attr("id"),
            visible: $(this).is(":checked"),
        });
    });

    return columnVisibility;
}

// Example: Map column IDs to indices
function getColumnIndexById(columnId) {
    // Map column IDs to their index positions
    const columnMapping = {
        "s-no": 0,
        "series": 1,
        "mrn-no": 2,
        "mrn-date": 3,
        "po-no": 4,
        "gate-entry-no": 5,
        "so-no": 6,
        "lot-no": 7,
        "vendor": 8,
        "vendor-rating": 9,
        "category": 10,
        "sub-category": 11,
        "item-type": 12,
        "sub-type": 13,
        "item": 14,
        "item-code": 15,
        "mrn-qty": 16,
        "accept-qty": 17,
        "rejected-qty": 18,
        "pr-qty": 19,
        "pr-rejected-qty": 20,
        "purchase-bill-qty": 21,
        "store": 22,
        "sub-store": 23,
        "rate": 24,
        "basic-value": 25,
        "item-discount": 26,
        "header-discount": 27,
        "mrn-amount": 28,
        "status": 29
    };

    return columnMapping[columnId] || -1;
}


// Call fetchPurchaseOrders when the page loads
document.addEventListener("DOMContentLoaded", function () {
    fetchPurchaseOrders();

    const selectAllCheckbox = document.getElementById("selectAll");
    const checkboxes = document.querySelectorAll(".sortable .form-check-input");

    // Update the "Select All" checkbox state based on individual checkboxes
    const updateSelectAllState = () => {
        const allChecked = Array.from(checkboxes).every(
            (checkbox) => checkbox.checked
        );
        const someChecked = Array.from(checkboxes).some(
            (checkbox) => checkbox.checked
        );

        selectAllCheckbox.checked = allChecked;
        selectAllCheckbox.indeterminate = !allChecked && someChecked;
    };

    // Handle "Select All" checkbox change
    const handleSelectAllChange = () => {
        checkboxes.forEach(
            (checkbox) => (checkbox.checked = selectAllCheckbox.checked)
        );
        updateSelectAllState();
    };

    // Initialize event listeners
    selectAllCheckbox.addEventListener("change", handleSelectAllChange);
    checkboxes.forEach((checkbox) =>
        checkbox.addEventListener("change", updateSelectAllState)
    );

    // Initial update of "Select All" checkbox state
    updateSelectAllState();
});
