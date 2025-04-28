// /*=========================================================================================
//     File Name: loan-report.js
//     Description: Loan report page content with filter
//     ----------------------------------------------------------------------------------------
//     Item Name: Vuexy  - Vuejs, HTML & Laravel Admin Dashboard Template
// ==========================================================================================*/

// //------------ Filter data --------------------
// //---------------------------------------------

async function fetchLoanReports(filterData = {}) {
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

        var reportFilterUrl = window.routes.loanReport;

        const url = `${reportFilterUrl}?${params.toString()}`;
        console.log("url", url);

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
function updateTable(data = [], columnVisibility = []) {
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
        .map((column) => getColumnIndexById(column.id))
        .filter((index) => index !== -1); // Ensure valid indices

    // Ensure the Index (0) and Action (12) columns are always visible
    if (!visibleColumnIndices.includes(0)) visibleColumnIndices.unshift(0); // Index column
    if (!visibleColumnIndices.includes(12)) visibleColumnIndices.push(12); // Action column

    // Define table headers corresponding to the columns
    const tableHeaders = [
        `<th>#</th>`, // Index
        `<th>Loan Id</th>`, // Loan Id
        `<th>loan Type</th>`, // Loan Type
        `<th>Customer</th>`, // Customer
        `<th>Loan Amount</th>`, // Loan Amount
        `<th>Rec Amount</th>`, // Recemmended Amount
        `<th>Interest Rate</th>`, // Interest Rate
        `<th>Total Dis. AMT</th>`,
        `<th>Total Settle AMT</th>`, // Interest Rate
        `<th>Tenure (Months)</th>`, // Tenure
        `<th>Out Standing Balance</th>`, // Out Standing Balance
        `<th>Status</th>`,
        `<th>Action</th>` // Status
         // Action
    ];

    // Generate table heading HTML based on visible columns
    const headingRow = document.createElement("tr");
    const headingHTML = visibleColumnIndices
        .map((index) => tableHeaders[index])
        .join("");

    headingRow.innerHTML = headingHTML;
    thead.appendChild(headingRow); // Append the heading row to thead
    // Loop through the purchase order data and update the table
    data.loan_reports.forEach((report, index) => {
        const tr = document.createElement("tr");

        const types = {
            1: "Home",
            2: "Vehicle",
            3: "Term",
        };

        const statuses = {
            2: "Approved",
            3: "close",
            4: "Disbursed",
            5: "Disbursement",
            6: "Recovery",
        };

        // Assuming `loan.approvalStatus` is available
const approvalStatus = report.home_loan.approvalStatus || "N/A";

// Determine the mainBadgeClass
const mainBadgeClass = (() => {
    switch (approvalStatus) {
        case 'Disbursed':
            return 'info';
        case 'completed':
            return 'success';
        default:
            return 'warning';
    }
})();

// Determine the loanStatus badge HTML
let loanStatus;
if (mainBadgeClass === 'warning') {
    loanStatus = `<span class='badge rounded-pill badge-light-warning badgeborder-radius'>${approvalStatus}</span>`;
} else if (mainBadgeClass === 'success') {
    loanStatus = `<span class='badge rounded-pill badge-light-success badgeborder-radius'>${approvalStatus}</span>`;
} else if (mainBadgeClass === 'danger') {
    loanStatus = `<span class='badge rounded-pill badge-light-danger badgeborder-radius'>${approvalStatus}</span>`;
} else if (mainBadgeClass === 'info') {
    loanStatus = `<span class='badge rounded-pill badge-light-info badgeborder-radius'>${approvalStatus}</span>`;
} else {
    loanStatus = '-';
}



        let loanType = types[report.home_loan.type] || "N/A";
        let url = "#";
        if(loanType == "Home"){
            url = window.routes.loanViewAllDetail.replace(
                ":id",
                report.home_loan.id
            );
        } else if(loanType == "Vehicle"){
            url = window.routes.loanViewVehicleDetail.replace(
                ":id",
                report.home_loan.id
            );
        } else if(loanType == "Term"){
            url = window.routes.loanViewTermDetail.replace(
                ":id",
                report.home_loan.id
            );
        } else {
            url = "#";
        }
     // Create table data cells based on column visibility
    let totalDisburesement = report.home_loan.loan_disbursements
    .filter(item => item.document_status === 'Disbursed')
    .reduce((sum, item) => sum + parseFloat(removeCommas(item.actual_dis)||(item.dis_amount)), 0);
    let totalRecoveryPrincipal =0;
    if(report.home_loan.recovery_loan){
    totalRecoveryPrincipal = report.home_loan.recovery_loan
    .filter(item =>
        item.approvalStatus === 'Approved' || item.approvalStatus === 'approval_not_required' || item.approvalStatus === 'posted'
    ) // Filter items where approvalStatus matches either condition
    .reduce((sum, item) => sum + parseFloat(removeCommas(item.settled_principal) || 0), 0);
}
if(totalRecoveryPrincipal==0){
    totalRecoveryPrincipal = report.home_loan.recovery_pa;
}

let totalRecoveryInterest=0;
    if(report.home_loan.recovery_loan){
        totalRecoveryInterest = report.home_loan.recovery_loan
        .filter(item =>
            item.approvalStatus === 'Approved' || item.approvalStatus === 'approval_not_required' || item.approvalStatus === 'posted'
        ) // Filter items where approvalStatus matches either condition
        .reduce((sum, item) => sum + parseFloat(removeCommas(item.settled_interest) || 0), 0);
    }

    if(totalRecoveryPrincipal==0){
        totalRecoveryPrincipal = report.home_loan.recovery_ia||0;
    }


        let total = report.home_loan.loan_disbursements
        .filter(item => item.approvalStatus === 'Disbursed')
        .reduce((sum, item) => sum + parseFloat(item.actual_dis), 0);

        let totalSettled =0;
        if(report.home_loan.settlement){
            totalSettled= report.home_loan.settlement
        .filter(item =>
            item.approvalStatus === 'Approved' || item.approvalStatus === 'approval_not_required' || item.approvalStatus === 'posted'
        ) // Filter items where approvalStatus matches either condition
        .reduce((sum, item) => sum + parseFloat(removeCommas(item.settle_amnnt) || 0), 0);}

        balance = totalDisburesement - totalRecoveryPrincipal;


        const cells = [
            `<td>${index + 1}</td>`, // Index
            `<td class="fw-bolder text-dark">${report.home_loan.appli_no}</td>`,
            `<td>${loanType}</td>`,
            `<td>${report.home_loan.name ?? "N/A"}</div></td>`,
            `<td>${report.home_loan.loan_appraisal.term_loan ?? "0"}</td>`,
            `<td>${totalRecoveryPrincipal ?? "0"}</td>`,
            `<td>${totalRecoveryInterest ?? "0"}</td>`,
           `<td>${parseFloat(totalDisburesement).toFixed(2) ?? "0"}</td>`,
           `<td>${parseFloat(totalSettled).toFixed(2) ?? "0"}</td>`,
            `<td>${
                convertToMonths(
                    report.home_loan.loan_appraisal.repayment_type,report.home_loan.loan_appraisal.no_of_installments
                ) ?? "N/A"
            }</td>`,
            `<td>${balance.toFixed(2) ?? "N/A"}</td>`,
            `<td>${loanStatus ?? "N/A"}</td>`, // outstanding
            `<td><a href="${url}"><i class="cursor-pointer" data-feather='eye'></i></a></td>`, // Action
        ];

        // Construct row based on column visibility settings
        const rowHTML = visibleColumnIndices
            .map((index) => cells[index])
            .join("");

        tr.innerHTML = rowHTML;
        tbody.appendChild(tr);
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
                                    "loan_report.home_loan_" +
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

function convertToMonths(type, period) {
    switch (type) {
        case "1":
            return period * 12; // 1 year = 12 months
        case "2":
            return period * 6; // half year = 6 months
        case "4":
            return period; // 1 month = 1 month
        case "3":
            return period * 3; // 1 quarter = 3 months
        case "yearly":
            return period * 12; // 1 year = 12 months
        case "half_yearly":
            return period * 6; // half year = 6 months
        case "monthly":
            return period; // 1 month = 1 month
        case "quarterly":
            return period * 3; // 1 quarter = 3 months
        default:
            return 0; // Invalid type
    }
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
        "loan-id": 1,
        "loan-type": 2,
        "customer": 3,
        "loan-amount": 4,
        "recommended-amount": 5,
        "interest-rate": 6,
        "total-dis": 7,
        "total-settle": 8,
        "tenure": 9,
        "out-standing-balance": 10,
        "loan-status": 11,
        "action": 12,
    };

    return columnMapping[columnId] || -1;
}

// Call fetchPurchaseOrders when the page loads
document.addEventListener("DOMContentLoaded", function () {
    fetchLoanReports();

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
