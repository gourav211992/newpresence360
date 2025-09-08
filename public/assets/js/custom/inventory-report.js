// /*=========================================================================================
//     File Name: po-report.js
//     Description: Purchase report page content with filter
//     ----------------------------------------------------------------------------------------
//     Item Name: Vuexy  - Vuejs, HTML & Laravel Admin Dashboard Template
// ==========================================================================================*/

// //------------ Filter data --------------------
// //---------------------------------------------

function isObject(value) {
    return typeof value === "object" && value !== null;
}

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
            if (!isObject(value) && value) {
                params.append(key, value);
            } else if (isObject(value)) {
                value.forEach((attribute, index) => {
                    params.append(`attribute_name[]`, attribute.groupId);
                    params.append(`attribute_value[]`, attribute.val);
                });
            }
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
function updateTable(inventory_reports = [], columnVisibility = []) {
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
    // console.log('columnVisibility', columnVisibility);
    // Create a mapping of column indices from visibility settings
    const visibleColumnIndices = columnVisibility
        .filter((column) => column.visible)
        .map((column) => getColumnIndexById(column.id))
        .filter((index) => index !== -1); // Ensure valid indices
    // Ensure the Index (0), Action (10) and Action (11) columns are always visible
    if (!visibleColumnIndices.includes(0)) visibleColumnIndices.unshift(0); // Index column
    if (!visibleColumnIndices.includes(11)) visibleColumnIndices.push(11); // Confirmed Stock columns
    if (!visibleColumnIndices.includes(12)) visibleColumnIndices.push(12);
    if (!visibleColumnIndices.includes(13)) visibleColumnIndices.push(13);
    if (!visibleColumnIndices.includes(14)) visibleColumnIndices.push(14); // Unconfirmed Stock columns
    if (!visibleColumnIndices.includes(15)) visibleColumnIndices.push(15);
    if (!visibleColumnIndices.includes(16)) visibleColumnIndices.push(16);
    if (!visibleColumnIndices.includes(17)) visibleColumnIndices.push(17);
    if (!visibleColumnIndices.includes(18)) visibleColumnIndices.push(18);

    // Define table headers corresponding to the columns
    const day_1 = $("#day1").val();
    const day_2 = $("#day2").val();
    const day_3 = $("#day3").val();
    const day_4 = $("#day4").val();
    const day_5 = $("#day5").val();
    const tableHeaders = [
        `<th>#</th>`, // Index
        `<th>Document No</th>`, // Doc No
        `<th>Document Date</th>`, // Doc Date
        `<th>Item</th>`, // Item
        `<th>Item Code</th>`, // Item Code
        `<th>Attributes</th>`, // Attributes
        `<th>Location</th>`, // Store
        `<th>Store</th>`,
        `<th>Station</th>`, // Shelf
        `<th>UOM</th>`, // UOM
        `<th>Stock Type</th>`, // Bin
        `<th class='text-end'>Confirmed<br> Stock</th> `, // Confirmed Stock Quantity
        `<th class='text-end'>Cost</th>`, // Confirmed Stock Cost
        `<th class='text-end'>Value</th>`, // Confirmed Stock Value
        `<th class='text-end'>Unconfirmed<br> Stock</th>`, // Unconfirmed Stock Quantity
        `<th class='text-end'>Cost</th>`, // Unconfirmed Stock Cost
        `<th class='text-end'>Value</th>`, // Unconfirmed Stock Value
        `<th class='text-end'>Reserved<br> Stock</th>`, // Reserved Quantity
        `<th class='text-end'>Hold<br> Stock</th>`, // Hold Quantity
        `<th>0-${day_1} Days</th>`,
        `<th>${day_1}-${day_2} Days</th>`,
        `<th>${day_2}-${day_3} Days</th>`,
        `<th>${day_3}-${day_4} Days</th>`,
        `<th>${day_4}-${day_5} Days</th>`,
        `<th>Above ${day_5} Days</th>`,
    ];

    // Generate table heading HTML based on visible columns
    const headingRow = document.createElement("tr");
    const headingHTML = visibleColumnIndices
        .map((index) => tableHeaders[index] || `<th>N/A</th>`)
        .join("");

    headingRow.innerHTML = headingHTML;
    thead.appendChild(headingRow); // Append the heading row to thead

    // Loop through the purchase order data and update the table
    inventory_reports.forEach((report, index) => {
        const tr = document.createElement("tr");
        const confirmedStockValue = Number(report?.confirmed_stock_value);
        const confirmedStock = Number(report?.confirmed_stock);
        const unconfirmedStockValue = Number(report?.unconfirmed_stock_value);
        const unconfirmedStock = Number(report?.unconfirmed_stock);

        // Check if both values are valid numbers and avoid NaN or Infinity
        const confirmedStockCost =
            confirmedStock && !isNaN(confirmedStockValue) && confirmedStock > 0
                ? (confirmedStockValue / confirmedStock).toFixed(2)
                : "0.00";
        const unconfirmedStockCost =
            unconfirmedStock &&
            !isNaN(unconfirmedStockValue) &&
            unconfirmedStock > 0
                ? (unconfirmedStockValue / unconfirmedStock).toFixed(2)
                : "0.00";

        // Parse the item_attributes JSON string
        let attributesHTML = ""; // Default value if attributes are not present or invalid
        let storeId = "";
        let subLocationId = "";
        let stationId = "";
        let stockType = "";
        let shelfId = "";
        let binId = "";
        let typeOfStockId = "";
        let attrId = { attribute_name: [], attribute_value: [] };
        try {
            const itemAttributes = JSON.parse(report.item_attributes);
            if (Array.isArray(itemAttributes) && itemAttributes.length > 0) {
                attributesHTML = itemAttributes
                    .map((attr) => {
                        const attributeName = attr.attribute_name ?? "";
                        const attributeValue = attr.attribute_value ?? "";
                        return `<span class="badge rounded-pill badge-light-secondary badgeborder-radius">
                        ${attributeName}: ${attributeValue}
                    </span>`;
                    })
                    .join(""); // Join the HTML for all attributes
            }
            if ($("#attributes").is(":checked")) {
                if (
                    Array.isArray(itemAttributes) &&
                    itemAttributes.length > 0
                ) {
                    itemAttributes.forEach((attr) => {
                        if (attr.attr_name && attr.attr_value) {
                            attrId.attribute_name.push(attr.attr_name);
                            attrId.attribute_value.push(attr.attr_value);
                        }
                    });
                }
            }
            storeId = $("#store").is(":checked") ? report?.store_id : "";
            subLocationId = $("#sub_location").is(":checked")
                ? report?.sub_store_id
                : "";
            stationId = $("#station").is(":checked") ? report?.station_id : "";
            stockType = $("#stock_types").is(":checked")
                ? report?.stock_type
                : "";
            shelfId = $("#shelf").is(":checked") ? report?.shelf_id : "";
            binId = $("#bin").is(":checked") ? report?.bin_id : "";
            typeOfStockId = "";
        } catch (error) {
            console.error("Error parsing item_attributes:", error);
        }
        const hiddenInputs = attrId.attribute_name
            .map((name, index) => {
                const value = attrId.attribute_value[index] || "";
                return `
                <input type="hidden" name="attribute_name[]" value="${name}" />
                <input type="hidden" name="attribute_value[]" value="${value}" />
            `;
            })
            .join("");
        // Create table data cells based on column visibility
        const cells = [
            `<td>${index + 1}</td>`, // Index
            `<td class="no-wrap fw-bolder text-dark">${report.document_number}</td>`, // PO No
            `<tdclass='no-wrap'>${formatDate(report.document_date)}</td>`, // PO Date
            `<td class="no-wrap clickable-item" item-id="${report.item_id}">
            <form action ='/inventory-reports/get-stock-ledger-reports' method="GET" target="_blank" >
            <input type="hidden" name="item" value='${
                report.item_id ? report.item_id : ""
            }'/>
            <input type="hidden" name="store_id" value='${
                storeId ? storeId : ""
            }'/>
            <input type="hidden" name="sub_store_id" value='${
                subLocationId ? subLocationId : ""
            }'/>
            <input type="hidden" name="station_id" value='${
                stationId ? stationId : ""
            }'/>
            <input type="hidden" name="shelf_id" value='${
                shelfId ? shelfId : ""
            }'/>
            <input type="hidden" name="bin_id" value='${binId ? binId : ""}'/>
            ${hiddenInputs}
            <input type="hidden" name="type_of_stock_id" value='${
                typeOfStockId ? typeOfStockId : ""
            }'/>
            <button type="submit" style="border: none; background-color: #fff; color: #002bff;">
                ${
                    report?.stock_type === "W" && report?.wip_station_id
                        ? `${report?.item?.item_name ?? ""} - ${
                              report?.wip_station?.name ?? ""
                          }`
                        : `${report?.item?.item_name ?? ""}`
                }
            </button>
            </form>
            </td>`, // Item Name
            `<td class='no-wrap'>${report?.item?.item_code ?? ""}</td>`, // Item Code
            `<td class='no-wrap'>
                <div style="white-space: normal;">
                    ${attributesHTML}
                </div>
            </td>`, // Attributes
            `<td class='no-wrap'>${report?.location?.store_name ?? ""}</td>`, // Store
            `<td class='no-wrap'>${report?.store?.name ?? ""}</td>`, // Rack
            `<td class='no-wrap'>${report?.station?.name ?? ""}</td>`, // Station
            `<td class='no-wrap'>${report?.inventory_uom?.name ?? ""}</td>`, // UOM
            `<td class="no-wrap">${report?.stock_type === "R" ? "Regular" : report?.stock_type === "W" ? "WIP" : report?.stock_type === "S" ? "Sub Standard": report?.stock_type === "J" ? "Rejected": ""}</td>`,
            `<td class='text-end'>${report?.confirmed_stock ?? 0.0}</td>`, // Confirmed Stock Quantity
            `<td class='text-end'>${confirmedStockCost ?? "0.00"}</td>`, // Confirmed Stock Cost
            `<td class='text-end'>${report?.confirmed_stock_value ?? 0.0}</td>`, // Confirmed Stock Value
            `<td class='text-end'>${report?.unconfirmed_stock ?? 0.0}</td>`, // Unconfirmed Stock Quantity
            `<td class='text-end'>${unconfirmedStockCost ?? "0.00"}</td>`, // Unconfirmed Stock Cost
            `<td class='text-end'>${
                report?.unconfirmed_stock_value ?? 0.0
            }</td>`, // Unconfirmed Stock Value
            `<td class='text-end'>${report?.reserved_qty ?? 0.0}</td>`, // Reserved Qty
            `<td class='text-end'>${report?.putaway_pending_qty ?? 0.0}</td>`, // Hold Qty
            `<td class='text-end'>${
                report?.confirmed_stock_day1_days ?? 0.0
            }</td>`, // 10 Days Ago
            `<td class='text-end'>${
                report?.confirmed_stock_day2_days ?? 0.0
            }</td>`, // 15 Days Ago
            `<td class='text-end'>${
                report?.confirmed_stock_day3_days ?? 0.0
            }</td>`, // 20 Days Ago
            `<td class='text-end'>${
                report?.confirmed_stock_day4_days ?? 0.0
            }</td>`, // 10 Days Ago
            `<td class='text-end'>${
                report?.confirmed_stock_day5_days ?? 0.0
            }</td>`, // 15 Days Ago
            `<td class='text-end'>${
                report?.confirmed_stock__more_than_day5_days ?? "0.00"
            }</td>`, // 20 Days Ago
        ];

        // Construct row based on column visibility settings
        const rowHTML = visibleColumnIndices
            .map((index) => cells[index] || "<td></td>")
            .join("");

        tr.innerHTML = rowHTML;
        tbody.appendChild(tr);

        tr.querySelector(".clickable-item")?.addEventListener("click", (e) => {
            const itemId = e.target.getAttribute("item-id");
            if (itemId) {
                window.location.href = `/inventory-reports/get-stock-ledger-reports?item_id=${itemId}`;
            }
        });

        feather.replace();
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
                                    "Material_Receipt_" +
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
                                // console.log('dataToSend---->>', dataToSend);

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

        $(dataTableSelector).on("init.dt", function () {
            // Use `closest()` to always find the correct wrapper
            $(this)
                .closest(".dataTables_wrapper")
                .attr(
                    "style",
                    `
                width: max-content !important;
                min-width: 100% !important;
            `
                );
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

    $(".sortable .aging-visibility").each(function () {
        let visibleId = $(this).attr("id");
        let visibleValue = $(this).val() == 1 ? 1 : 0;
        columnVisibility.push({
            id: visibleId,
            visible: visibleValue ? true : false,
        });
    });

    return columnVisibility;
}

// Example: Map column IDs to indices
function getColumnIndexById(columnId) {
    // Map column IDs to their index positions
    const columnMapping = {
        "document-no": 1,
        "document-date": 2,
        item: 3,
        "item-code": 4,
        attributes: 5,
        store: 6,
        sub_location: 7,
        station: 8,
        uom: 9,
        stock_types: 10,
        "confirmed-stock-qty": 11,
        "confirmed-stock-cost": 12,
        "confirmed-stock-value": 13,
        "unconfirmed-stock-qty": 14,
        "unconfirmed-stock-cost": 15,
        "unconfirmed-stock-value": 16,
        reserved_qty: 17,
        hold_qty: 18,
        day1_visibility: 19,
        day2_visibility: 20,
        day3_visibility: 21,
        day4_visibility: 22,
        day5_visibility: 23,
        day6_visibility: 24,
    };
    // console.clear();

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

$(document).on("change", ".store_code", function () {
    var store_code_id = $(this).val();
    $("#store_id").val(store_code_id).select2();

    var data = {
        store_code_id: store_code_id,
    };

    $.ajax({
        type: "POST",
        data: data,
        url: "/material-receipts/get-store-racks",
        success: function (data) {
            $("#rack_id").empty();
            $("#rack_id").append('<option value="">Select</option>');
            $.each(data.storeRacks, function (key, value) {
                $("#rack_id").append(
                    '<option value="' + key + '">' + value + "</option>"
                );
            });
            $("#rack_id").trigger("change");

            $("#bin_id").empty();
            $("#bin_id").append('<option value="">Select</option>');
            $.each(data.storeBins, function (key, value) {
                $("#bin_id").append(
                    '<option value="' + key + '">' + value + "</option>"
                );
            });
        },
    });
});

$(document).on("change", ".rack_code", function () {
    var rack_code_id = $(this).val();
    $("#rack_id").val(rack_code_id).select2();

    var data = {
        rack_code_id: rack_code_id,
    };

    $.ajax({
        type: "POST",
        data: data,
        url: "/material-receipts/get-rack-shelfs",
        success: function (data) {
            $("#shelf_id").empty();
            $("#shelf_id").append('<option value="">Select</option>');
            $.each(data.storeShelfs, function (key, value) {
                $("#shelf_id").append(
                    '<option value="' + key + '">' + value + "</option>"
                );
            });

            $("#shelf_id").trigger("change");
        },
    });
});

// Get Attribute Values
$(document).on("change", ".attribute_name", function () {
    var attribute_name = $(this).val();
    $("#attribute_name").val(attribute_name).select2();

    var data = {
        attribute_name: attribute_name,
    };

    $.ajax({
        type: "POST",
        data: data,
        url: "/inventory-reports/get-attribute-values",
        success: function (data) {
            $("#attribute_value").empty();
            $.each(data.attributeValues, function (key, value) {
                $("#attribute_value").append(
                    '<option value="' + key + '">' + value + "</option>"
                );
            });
        },
    });
});
