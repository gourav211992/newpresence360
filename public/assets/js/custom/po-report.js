// /*=========================================================================================
//     File Name: po-report.js
//     Description: Purchase report page content with filter
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
        console.log(reportFilterUrl);
        const url = `${reportFilterUrl}?${params.toString()}`;
        console.log("url", url);

        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        console.log(data);
        // Call the function to update the table
        updateTable(data, columnOrderList || []);
    } catch (error) {
        console.error("Error fetching purchase orders:", error);
    }
}

// Function to update the table dynamically
function updateTable(po_reports = [], columnVisibility = []) {
    const tbody = document.querySelector("tbody");
    const thead = document.querySelector("thead");
    console.log(columnVisibility);
    const dataTableSelector = ".datatables-basic";

    // Destroy existing DataTable instance if it exists
    if ($.fn.DataTable.isDataTable(dataTableSelector)) {
        const table = $(dataTableSelector).DataTable();
        table.destroy();
    }

    tbody.innerHTML = ""; // Clear existing table rows
    thead.innerHTML = ""; // Clear existing table headings

    console.log(columnVisibility, "VISI");

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
        `<th>PO No</th>`, // PO No
        `<th>PO Date</th>`, // PO Date
        `<th>Vendor</th>`, // Vendor
        `<th>Vendor Rating</th>`, // Vendor rating
        `<th>Category</th>`, // Category
        `<th>Sub Category</th>`, // Sub category
        `<th>Item type</th>`, // Item type
        `<th>Sub type</th>`, // Sub type
        `<th>Item</th>`, // Item Name
        `<th>Item Code</th>`, // Item Code
        `<th>Po Qty</th>`, // Order Quantity
        `<th>Rec Qty</th>`, // Example value
        `<th>Bal Qty</th>`, // Example value
        `<th>Rate</th>`, // Rate
        `<th>Item Discount</th>`, // Item Discount
        `<th>Header Discount</th>`, // Item Header Discount
        `<th>PO Amount</th>`, // Po Amount
        `<th>Tax</th>`, // Tax
        `<th>Status</th>`, // Status
    ];

    console.log(visibleColumnIndices, "HTML")


    // Generate table heading HTML based on visible columns
    const headingRow = document.createElement("tr");
    const headingHTML = visibleColumnIndices
        .map((index) => { return tableHeaders[index] || `<th>N/A</th>` })
        .join("");


    headingRow.innerHTML = headingHTML;
    thead.appendChild(headingRow); // Append the heading row to thead
    var counter = 1;
    // Loop through the purchase order data and update the table
    po_reports.forEach((report) => {
        report.po_items.forEach((po_item) => {
            const tr = document.createElement("tr");
            let balQty = po_item?.order_qty - po_item?.grn_qty;
            console.log(po_item);
            // Create table data cells based on column visibility
            const cells = [
                `<td>${counter++}</td>`, // Index
                `<td class="fw-bolder text-dark">${report.book_code}</td>`, // Series
                `<td class="fw-bolder text-dark">${report.document_number}</td>`, // PO No
                `<td>${formatDate(report.document_date)}</td>`, // PO Date
                `<td>${report.vendor?.display_name ?? "N/A"}</td>`, // Vendor
                `<td>${"vendor rating" ?? "N/A"}</td>`, // Vendor rating
                `<td>${po_item?.item?.category?.name ?? "N/A"}</td>`, // category
                `<td>${po_item?.item?.sub_category?.name ?? "N/A"}</td>`, // Sub category
                `<td>${po_item?.item?.type ?? "N/A"}</td>`, // item type
                `<td>${"sub type" ?? "N/A"}</td>`, // sub type
                `<td>${po_item?.item?.item_name ?? "N/A"}</td>`, // Item Name
                `<td>${po_item?.item?.item_code ?? "N/A"}</td>`, // Item Code
                `<td>${po_item?.order_qty ?? "N/A"}</td>`, // Order Quantity
                `<td>${po_item?.grn_qty ?? "N/A"}</td>`, // res qty
                `<td>${balQty ?? "N/A"}</td>`, // bal qty
                `<td>${po_item?.rate ?? "N/A"}</td>`, // Rate
                `<td>${po_item?.item_discount_amount ?? "N/A"}</td>`, // Item Discount
                `<td>${po_item?.header_discount_amount ?? "N/A"}</td>`, // Item Header Discount
                `<td>${report.total_item_value ?? "N/A"}</td>`, // Po amount
                `<td>${po_item?.tax_amount ?? "N/A"}</td>`, // Taxes
                `<td>
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <span class="badge rounded-pill badge-light-success badgeborder-radius">
                            ${report.document_status.charAt(0).toUpperCase() + report.document_status.slice(1).toLowerCase()}
                        </span>
                        <a href="/purchase-order/edit/${po_item?.purchase_order_id}">
                            <i class="cursor-pointer" data-feather='eye'></i>
                        </a>
                    </div>
                </td>`,// Status
            ];

            // Construct row based on column visibility settings
            const rowHTML = visibleColumnIndices
                .map((index) => cells[index] || "<td>N/A</td>")
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
                                    "PO_Report_" +
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
        "po-no": 2,
        "po-date": 3,
        "vendor": 4,
        "vendor-rating": 5,
        "category": 6,
        "sub-category": 7,
        "item-type": 8,
        "sub-type": 9,
        "item": 10,
        "item-code": 11,
        "po-qty": 12,
        "rec-qty": 13,
        "bal-qty": 14,
        "rate": 15,
        "item-discount": 16,
        "header-discount": 17,
        "taxs": 18,
        "po-amount": 19,
        "status": 20
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
