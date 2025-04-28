// Filter management
let filterData = {};

// Purchase Order Report Module
document.addEventListener("DOMContentLoaded", function () {
    // Initialize the module
    initializeFilters();
});

const DOM_SELECTORS = {
    dataTable: ".datatables-basic",
    tableBody: "tbody",
    tableHead: "thead",
    periodInputs: 'input[name="Period"]',
    series: "#series",
    customer: "#customer",
    landId: "#land_id",
    area: "#area",
    landCost: "#land_cost",
    khasaraNumber: "#khasara_number",
    paymentLastReceived: "#payment_last_received",
    totalLeaseAmount: "#total_lease_amount",
    leaseDuration: "#lease_duration",
    monthlyInstallment: "#monthly_installment",
    applyButton: "#applyBtn",
    schedulerReportButton: ".schedulerReportBtn",
    schedulerReporttBody: "#recovery-schedule",
    columnVisibilityContainer: "#Columns",
    selectAllCheckbox: "#selectAll",
    columnCheckboxes: ".form-check-input:not(#selectAll)",
    toUserInput: 'select[name="to"] option:selected',
    typeInput: "#type",
    dateInput: "#dateInput",
    remarksInput: "#remarks",
};

const COLUMN_IDS = [
    "#",
    "sl-no",
    "customer-name",
    "land-number",
    "area-column",
    "land-cost",
    "total-lease-amount",
    "lease-duration",
    "lease-type",
    "installment-amount",
    "action",
];

const TABLE_HEADERS = [
    "#",
    "SL NO",
    "Customer Name",
    "Land Number",
    "Area",
    "Land Cost",
    "Total Lease Amount",
    "Lease Duration",
    "Lease Type",
    "Installment Amount",
    "Action",
];

// Data fetching
async function fetchPurchaseOrders(filterData = {}) {
    try {
        const ROUTES = window.routes.landReportFilter;
        const params = new URLSearchParams(filterData);
        const url = `${ROUTES}?${params}`;
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        lastFetchedData = data;
        updateTable(data);
    } catch (error) {
        console.error("Error fetching purchase orders:", error);
    }
}

// Table management
function updateTable(lease_reports = []) {
    const tbody = document.querySelector(DOM_SELECTORS.tableBody);
    const thead = document.querySelector(DOM_SELECTORS.tableHead);

    destroyExistingDataTable();
    clearTableContents(tbody, thead);
    populateTableHeaders(thead);
    populateTableRows(tbody, lease_reports);

    initializeFeatherIcons();
    initializeDataTable();
}

function destroyExistingDataTable() {
    if ($.fn.DataTable.isDataTable(DOM_SELECTORS.dataTable)) {
        $(DOM_SELECTORS.dataTable).DataTable().destroy();
    }
}

function clearTableContents(tbody, thead) {
    tbody.innerHTML = "";
    thead.innerHTML = "";
}

function populateTableHeaders(thead) {
    const visibleColumnIndices = getVisibleColumnIndices();
    const headerRow = document.createElement("tr");
    headerRow.innerHTML += "<th>#</th>";
    headerRow.innerHTML = visibleColumnIndices
        .map((index) => `<th>${TABLE_HEADERS[index]}</th>`)
        .join("");

    thead.appendChild(headerRow);
}

function populateTableRows(tbody, lease_reports) {
    if (!Array.isArray(lease_reports)) {
        console.error("lease_reports is not an array");
        return;
    }
    const visibleColumnIndices = getVisibleColumnIndices();

    lease_reports.forEach((report, index) => {
        if (typeof report === "object" && report !== null) {
            const row = createTableRow(report, index, visibleColumnIndices);
            tbody.appendChild(row);
        } else {
            console.error(`Invalid report data at index ${index}:`, report);
        }
    });
}

function calculateDaysDifference(date1, date2) {
    // Get the time difference in milliseconds
    const timeDifference = date2.getTime() - date1.getTime();

    // Calculate the difference in days
    const daysDifference = timeDifference / (1000 * 3600 * 24); // Convert milliseconds to days

    return Math.round(daysDifference); // Return rounded result
}

// DataTable initialization
function initializeDataTable() {
    try {
        $(DOM_SELECTORS.dataTable).DataTable({
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
                            filename: () =>
                                `PO_Report_${new Date()
                                    .toISOString()
                                    .slice(0, 10)}`,
                            exportOptions: { columns: () => true },
                        },
                        {
                            extend: "copy",
                            text:
                                feather.icons["mail"].toSvg({
                                    class: "font-small-4 me-50",
                                }) + "Mail",
                            className: "dropdown-item",
                            action: handleMailExport,
                        },
                    ],
                    init: (api, node, config) => {
                        $(node).removeClass("btn-secondary");
                        $(node).parent().removeClass("btn-group");
                        setTimeout(() => {
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
                paginate: { previous: "&nbsp;", next: "&nbsp;" },
            },
        });
    } catch (error) {
        console.error("Error initializing DataTable:", error);
    }

    $('[data-bs-toggle="tooltip"]').tooltip();
}

function handleMailExport(e, dt, button, config) {
    const exportedData = dt.buttons.exportData();
    const dataToSend = JSON.stringify(exportedData);

    $.ajax({
        url: window.routes.reportSendMail, // Replace with actual mail route
        type: "GET",
        success: (response) => showToast("success", response.success),
        error: (error) => showToast("error", error.responseJSON.message),
    });
}

function initializeFilters() {
    fetchPurchaseOrders();
    setupEventListeners();
    setupColumnVisibilityListeners();
}

function setupEventListeners() {
    document.querySelectorAll(DOM_SELECTORS.periodInputs).forEach((radio) => {
        radio.addEventListener("change", handlePeriodChange);
    });
    setupFilters();
    document
        .querySelector(DOM_SELECTORS.applyButton)
        .addEventListener("click", handleFormSubmission);

    // Use event delegation for scheduler report buttons
    document.addEventListener("click", function (e) {
        // Check if the clicked element or its parent is a scheduler report button
        const schedulerBtn = e.target.closest(".schedulerReportBtn");
        if (schedulerBtn) {
            e.preventDefault(); // Prevent the default anchor behavior
            const landNo = schedulerBtn.getAttribute("data-land-no");
            sendSchedulerAjaxRequest(landNo);
        }
    });
}

function setupFilters() {
    const filters = [
        { selector: DOM_SELECTORS.series, key: "series", type: "select" }, // select field
        {
            selector: DOM_SELECTORS.documentRef,
            key: "documentRef",
            type: "input",
        }, // input field
        { selector: DOM_SELECTORS.customer, key: "customer", type: "select" }, // select field
        { selector: DOM_SELECTORS.landId, key: "landId", type: "select" }, // select field
        { selector: DOM_SELECTORS.area, key: "area", type: "input" }, // input field
    ];

    filters.forEach(({ selector, key, type }) => {
        // Attach 'change' event for select elements and 'input' event for input elements
        const eventType = type === "select" ? "change" : "input";

        $(selector).on(eventType, () => {
            filterData[key] = $(selector).val();
            updateFilterAndFetch();
        });
    });
}

function handlePeriodChange(event) {
    if (event.target.id === "Custom") {
        handleCustomDateSelection();
    } else {
        handlePresetPeriodSelection(event.target.value);
    }
}

function handleCustomDateSelection() {
    const dateRange = document.getElementById("Custom").value;
    if (dateRange) {
        const [startDate, endDate] = dateRange.split(" to ");
        filterData.startDate = startDate;
        filterData.endDate = endDate;
    }
    delete filterData.period;
    updateFilterAndFetch();
}

function handlePresetPeriodSelection(period) {
    filterData.period = period;
    delete filterData.startDate;
    delete filterData.endDate;
    updateFilterAndFetch();
}

function updateFilterAndFetch() {
    if (Object.keys(filterData).length > 0) {
        fetchPurchaseOrders(filterData);
    }
}

// Form submission
function handleFormSubmission(e) {
    e.preventDefault();
    const formData = gatherFormData();
    const filterModal = bootstrap.Modal.getInstance(
        document.getElementById("addcoulmn")
    );

    filterData = formData;
    //if (formData.landCost || formData.khasaraNumber || formData.paymentLastReceived || formData.totalLeaseAmount || formData.leaseDuration || formData.monthlyInstallment) {
    updateFilterAndFetch();
    // if (filterModal) {
    //     filterModal.hide();
    // }
    //}
    if (formData.to.length > 0 || formData.type || formData.date) {
        sendAjaxRequest(formData);
    }
}

function gatherFormData() {
    return {
        landCost: $(DOM_SELECTORS.landCost).val(),
        khasaraNumber: $(DOM_SELECTORS.khasaraNumber).val(),
        paymentLastReceived: $(DOM_SELECTORS.paymentLastReceived).val(),
        totalLeaseAmount: $(DOM_SELECTORS.totalLeaseAmount).val(),
        leaseDuration: $(DOM_SELECTORS.leaseDuration).val(),
        monthlyInstallment: $(DOM_SELECTORS.monthlyInstallment).val(),

        //scheduler fields
        to: getSelectedData(),
        type: $(DOM_SELECTORS.typeInput).val(),
        date: $(DOM_SELECTORS.dateInput).val(),
        remarks: $(DOM_SELECTORS.remarksInput).val(),
    };
}

function getSelectedData() {
    let selectedData = [];

    $(DOM_SELECTORS.toUserInput).each(function () {
        selectedData.push({
            id: $(this).val(),
            type: $(this).data("type"),
        });
    });

    return selectedData;
}

function sendAjaxRequest(formData) {
    $.ajax({
        url: window.routes.reportScheduler,
        method: "POST",
        data: formData,
        success: handleSuccessResponse,
        error: handleAjaxError,
    });
}

function sendSchedulerAjaxRequest(landNo) {
    $.ajax({
        url: window.routes.recoverySchedulerReport,
        method: "GET",
        data: {
            landNo: landNo,
        },
        success: function (response) {
            updateRecoveryScheduleTable(response.recovried);
        },
        error: handleAjaxError,
    });
}

function updateRecoveryScheduleTable(data) {
    const tbody = document.getElementById("recovery-schedule");
    tbody.innerHTML = ""; // Clear existing content

    if (!Array.isArray(data) || data.length === 0) {
        const noDataRow = document.createElement("tr");
        noDataRow.innerHTML =
            '<td colspan="6" class="text-center">No data available</td>';
        tbody.appendChild(noDataRow);
        return;
    }

    let totalReceived = 0;
    data.forEach((item, index) => {
        
        const row = document.createElement("tr");
        let dueDate = "N/A";
        let daysOverdue = 0;
        let totalReceived = 0;
        if(item.schedules.length > 0){
            console.log(item.schedules);
        item.schedules.forEach((sch)=>{
            if(sch.status!='paid'){
                dueDate = sch.due_date;
                return;
            }
        });
        item.schedules.forEach((sch)=>{
            if(sch.status=='paid'){
                totalReceived += sch.installment_cost;
            }
        });
        let today = new Date();
        item.schedules.forEach((sch)=>{
            if (sch.status!="paid" && new Date(sch.due_date) < today) {
                let dueDate = new Date(sch.due_date);
                overdueDays = Math.ceil((today - dueDate) / (1000 * 60 * 60 * 24)); 
                return;
            }
        });

    }
        // if (item.date_of_payment) {
        //     const recoveryDate = new Date(item.date_of_payment);
        //     const currentDate = new Date();
        //     if (currentDate > recoveryDate) {
        //         const diffTime = Math.abs(currentDate - recoveryDate);
        //         daysOverdue = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        //     } else {
        //         daysOverdue = 0;
        //     }
        // }

        // if (
        //     item.received_amount !== null &&
        //     item.received_amount !== undefined &&
        //     !isNaN(item.received_amount)
        // ) {
        //     totalReceived += Number(item.received_amount); // Ensure the value is a number
        // }

        // // Format date
        // const formattedDate = item.date_of_payment
        //     ? (() => {
        //           const date = new Date(item.date_of_payment);
        //           const day = String(date.getDate()).padStart(2, "0");
        //           const month = String(date.getMonth() + 1).padStart(2, "0"); // Month is zero-based
        //           const year = date.getFullYear();
        //           return `${day}-${month}-${year}`;
        //       })()
        //     : "N/A";

        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${item.total_amount || "N/A"}</td>
            <td>${item.total_amount- totalReceived || 0}</td>
            <td>${totalReceived}</td>
            <td>${daysOverdue}</td>
        `;

        tbody.appendChild(row);
    });
}

function handleSuccessResponse(response) {
    showToast("success", response.success);
    resetForm();
}

function handleAjaxError(xhr) {
    if (xhr.status === 422) {
        handleValidationError(xhr.responseJSON.errors);
    }
}

function resetForm() {
    $(DOM_SELECTORS.toSelect).val([]).trigger("change");
    $(DOM_SELECTORS.typeSelect).val(null).trigger("change");
    $(DOM_SELECTORS.dateInput).val("");
    $(DOM_SELECTORS.remarksTextarea).val("");

    const filterModal = bootstrap.Modal.getInstance(
        document.getElementById("addcoulmn")
    );
    if (filterModal) {
        filterModal.hide();
    }
}

// Utility functions
function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString("en-GB");
}

function initializeFeatherIcons() {
    feather.replace();
}

function showToast(icon, title) {
    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        },
    });
    Toast.fire({ icon, title });
}

function handleValidationError(errors) {
    for (let field in errors) {
        if (errors.hasOwnProperty(field)) {
            let inputField = $('[name="' + field + '"]');
            displayFieldError(inputField, errors[field]);
        }
    }
}

function displayFieldError(inputField, errorMessages) {
    if (inputField.hasClass("select2")) {
        inputField
            .closest(".select2-wrapper")
            .find(".invalid-feedback")
            .remove();
        inputField
            .closest(".select2-wrapper")
            .append(
                '<div class="invalid-feedback d-block">' +
                    errorMessages.join(", ") +
                    "</div>"
            );
        inputField.next(".select2-container").addClass("is-invalid");
    } else {
        inputField.removeClass("is-invalid").addClass("is-invalid");
        inputField.next(".invalid-feedback").remove();
        inputField.after(
            '<div class="invalid-feedback">' +
                errorMessages.join(", ") +
                "</div>"
        );
    }
}

function createTableRow(report, index, visibleColumnIndices) {
    const row = document.createElement("tr");

    let paymentDate = null;
    let daysDifference = null;
    if (paymentDate !== null) {
        const paymentDateObj = new Date(paymentDate);
        const currentDateObj = new Date();
        daysDifference = calculateDaysDifference(
            paymentDateObj,
            currentDateObj
        );
    }
    console.log(report);
    let urls = window.routes.lease.replace("__ID__", report.id);


    const cellsData = [
        index + 1,
        report.document_no,
        report.customer?.display_name ?? "N/A",
        report.land?.document_no ?? "N/A",
        report.land?.area_unit+"("+report.land?.plot_area+")" ?? "N/A",
        report.land?.land_valuation ?? "N/A",
        report.total_amount ?? "N/A",
        report.repayment_period ?? "N/A",
        report.repayment_period_type ?? "N/A",
        report.installment_amount ?? "N/A",
        `<a href="${urls}"><i class="cursor-pointer" data-feather='eye'></i></a>
        <a href="#" class="schedulerReportBtn" data-bs-toggle="modal" data-bs-target="#landReportModal" data-land-no="${report.land_id}"><i data-feather="filter"></i></i></a>`,
    ];

    row.innerHTML = visibleColumnIndices
        .map((index) => `<td>${cellsData[index]}</td>`)
        .join("");

    return row;
}

function setupColumnVisibilityListeners() {
    const selectAllCheckbox = document.querySelector(
        DOM_SELECTORS.selectAllCheckbox
    );
    const columnCheckboxes = document.querySelectorAll(
        DOM_SELECTORS.columnCheckboxes
    );

    selectAllCheckbox.addEventListener("change", handleSelectAllChange);
    columnCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener("change", handleColumnCheckboxChange);
    });
}

function handleSelectAllChange(event) {
    const columnCheckboxes = document.querySelectorAll(
        DOM_SELECTORS.columnCheckboxes
    );
    columnCheckboxes.forEach((checkbox) => {
        checkbox.checked = event.target.checked;
    });
    updateTableColumns();
}

function handleColumnCheckboxChange() {
    updateTableColumns();
    updateSelectAllCheckbox();
}

function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.querySelector(
        DOM_SELECTORS.selectAllCheckbox
    );
    const columnCheckboxes = document.querySelectorAll(
        DOM_SELECTORS.columnCheckboxes
    );
    selectAllCheckbox.checked = Array.from(columnCheckboxes).every(
        (checkbox) => checkbox.checked
    );
}

function updateTableColumns() {
    if (lastFetchedData.length > 0) {
        updateTable(lastFetchedData);
    }
}

function getVisibleColumnIndices() {
    const visibleIndices = COLUMN_IDS.slice(1, -1)
        .filter((id) => document.getElementById(id).checked)
        .map((id) => COLUMN_IDS.indexOf(id));
    return [0, ...visibleIndices, COLUMN_IDS.length - 1];
}
