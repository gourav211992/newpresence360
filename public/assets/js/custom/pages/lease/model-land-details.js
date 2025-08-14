let filterData = {};
// Wait for the DOM to be fully loaded
const tbody = document.querySelector(".mrntableselectexcel");
const deleteBtn = document.querySelector("#delete_plot_rows");
const addNewPlotBtn = document.querySelector("#add_new_plot_row");
let currentRowLeaseAmount = 0;
let totalLeaseAmount = 0;
let totalOtherCharges = 0;
let totalPlotsAmount = 0;
let otherChargesData = {};
let rowCounter = 0;

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('repayment_period_type').addEventListener('change', calculateRepayment);
});
function calculateRepayment() {
    calculateRepaymentPeriod();
}
function calextracharge() {
    $('#trbody').css('display', 'table-row');
    $('#trbody').html(`
                    <td><strong>Extra Other Charges</strong></td>
                    <td class="text-end" id="chargeother">${parseFloat($('#othercharges_total').text().replace(/,/g, '')).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        }</td>
                `);

    $("#lease_extra_charges").val(parseFloat($('#othercharges_total').text()));
    updateData();
    calculateRepayment();
    calcualtetax();
}


function updateData() {
    // Initialize an empty object and array
    var otherData = {};
    otherData[0] = [];

    // Loop through each row in the table
    $('#othercharges_body tr').each(function () {
        // Get the input values using jQuery's val() method
        const nameInput = $(this).find(".othercharges-name").val();
        const percentageInput = $(this).find(".othercharges-percentage").val();
        const valueInput = $(this).find(".othercharges-value").val();

        console.log($(this).find(".othercharges-percentage"));
        console.log($(this).find(".othercharges-percentage").val());
        // Skip if any input is missing
        if (!percentageInput || !valueInput)
            return;

        // Push the values into the array inside otherData[0]
        otherData[0].push({
            name: nameInput,          // No need for .value, .val() already fetches the value
            percentage: percentageInput,
            value: valueInput,
        });
    });

    // Set the JSON stringified version of otherData into the hidden input
    $("#lease_extra_charges_json").val(JSON.stringify(otherData));
}
function calcualtetax() {
    // Get any required data for tax calculation, for example, amount or discount
    var subtotal = parseFloat($("#lease_sub_total").val()) || 0.00; // Convert to float and default to 0.00 if empty or invalid
    var othercharge = parseFloat($("#lease_other_charges").val()) || 0.00; // Same for other charges
    var extracharge = parseFloat($("#lease_extra_charges").val()) || 0.00; // Assuming you have an input field with id="amount"


    var total = parseFloat(subtotal) + parseFloat(othercharge) + parseFloat(extracharge);


    var landid = $("#land").val();

    // Perform the AJAX request to calculate the tax
    if (total > 0 && landid) {
        $.ajax({
            url: taxCalculationUrl, // Your route for tax calculation
            method: 'POST', // Use the appropriate method (POST/GET)
            data: {
                price: total,
                landid: landid,
                date: $('#document_date').val(),
                _token: '{{ csrf_token() }}' // Include CSRF token for security in Laravel
            },
            success: function (response) {
                // Assuming the response contains the calculated tax
                var totalTaxAmount = 0;
                // Loop through the response and calculate tax
                $.each(response, function (index, tax) {
                    $('#po_tax_details').empty();
                    var taxPercentage = parseFloat(tax.tax_percentage) || 0.00;
                    var taxAmount = (taxPercentage / 100) * total; // Calculate tax based on percentage
                    totalTaxAmount += taxAmount; // Accumulate the tax amount
                    console.log('taxamount- ' +taxAmount)
                $('#tax_percentage').val(taxPercentage)
                    // Append each tax calculation as a new row in the table
                    $('#po_tax_details').append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${tax.tax_type}</td>
                                    <td>${total}</td>
                                    <td>${taxPercentage}</td>
                                    <td>${taxAmount.toFixed(2)}</td>
                                </tr>
                            `);
                });

                // Show the total tax calculated in the modal or any other element
                // Set the value with commas for display and plain value for input
                $("#taxAmount").text(totalTaxAmount.toLocaleString('en-IN', { maximumFractionDigits: 2 }));
                $("#tax_amount").val(totalTaxAmount.toFixed(2));  // Keep this as plain number for the input

                calculateRepayment();
            },
            error: function (xhr, status, error) {
                // Handle any errors
                console.error('Error calculating tax:', error);
                // alert('Failed to calculate tax.');
            }
        });
    }


}

function calculateRepaymentPeriod() {
    const leaseTime = parseInt(document.getElementById('leaseTime').value);
    const periodType = document.getElementById('repayment_period_type').value;
    const repaymentPeriodInput = document.getElementById('repaymentPeriod');

    var subtotal = 0.00;
    var lease_other_charges = 0.00;
    var lease_extra_charges = 0.00;
    var tax_amount = 0.00;

    if ($("#lease_sub_total").val() != '') {
        subtotal = parseFloat($("#lease_sub_total").val());
    }
    if ($("#lease_other_charges").val() != '') {
        lease_other_charges = parseFloat($("#lease_other_charges").val());
    }
    if ($("#lease_extra_charges").val() != '') {
        lease_extra_charges = parseFloat($("#lease_extra_charges").val());
    }
    if ($("#tax_amount").val() != '') {
        tax_amount = parseFloat($("#tax_amount").val());
    }

    var totalValue = subtotal + lease_other_charges + lease_extra_charges + tax_amount;
    var taxableValue = subtotal + lease_other_charges + lease_extra_charges;

    // Display values with commas and two decimal places
    $("#totalvalue").html(totalValue.toLocaleString('en-IN', { maximumFractionDigits: 2 }));
    $("#lease_total_installment").val(totalValue.toFixed(2));
    $("#taxablevalue").html(taxableValue.toLocaleString('en-IN', { maximumFractionDigits: 2 }));

    if (leaseTime && periodType) {
        let repaymentPeriod = 0;

        switch (periodType) {
            case 'monthly':
                repaymentPeriod = leaseTime * 12; // Convert years to months
                break;
            case 'quarterly':
                repaymentPeriod = leaseTime * 4; // Convert years to quarters
                break;
            case 'yearly':
                repaymentPeriod = leaseTime; // 1 year = 1 repayment period
                break;
            default:
                repaymentPeriod = 0;
        }

        repaymentPeriodInput.value = repaymentPeriod;
        $("#installement_cost").html('Installment Cost (' + repaymentPeriod + ')');

        $("#lease_installment_cost").val((parseFloat($("#taxablevalue").html().replace(/,/g, '')) / repaymentPeriod).toFixed(2));

        $("#installment_cost").html((parseFloat($("#totalvalue").html().replace(/,/g, '')) / repaymentPeriod).toLocaleString('en-IN', { maximumFractionDigits: 2 }));

    } else {
        repaymentPeriodInput.value = ''; // Clear if inputs are not set
    }
}

// Pending Disbursal
function setupFilters() {
    const filters = [
        {
            selector: $("#filter_land_id"),
            key: "landId",
            type: "select",
        }, // select field
        {
            selector: $("#filter_plot_no"),
            key: "plotId",
            type: "select",
        }, // select field
        {
            selector: $("#filter_district"),
            key: "districtId",
            type: "select",
        }, // select field
        {
            selector: $("#filter_state"),
            key: "stateId",
            type: "select",
        }, // select field
    ];

    filters.forEach(({ selector, key, type }) => {
        // Attach 'change' event for select elements and 'input' event for input elements
        const eventType = type === "select" ? "change" : "input";

        $(selector).on("change", () => {
            if ($(selector).val()) {
                filterData[key] = $(selector).val();
                updateFilterAndFetch();
            } else {
                delete filterData[key];
                updateFilterAndFetch();
            }
        });
    });
}

function updateFilterAndFetch() {
    if (Object.keys(filterData).length > 0) {
        fetchLandDetails(filterData);
    }
}

async function fetchLandDetails(filterData = {}) {
    try {
        const ROUTES = window.routes.landDetailsFilter;
        const params = new URLSearchParams(filterData);
        const url = `${ROUTES}?${params}`;
        const response = await fetch(url);

        if (!response.ok) {
            showToast("error", `HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();

        const tbody = document.querySelector("#find_land_table"); // Get the table body
        tbody.innerHTML = "";

        updateTable(tbody, data.land_filter_list);
    } catch (error) {
        showToast("error", `Error fetching Land Details: ${error}`);
    }
}

function updateTable(tbody, lands) {
    if (!Array.isArray(lands)) {
        showToast("error", "Land Data is not an array");
        return;
    }

    lands.forEach((land, index) => {
        land.plots.forEach((plot, index) => {
            if (typeof land === "object" && land !== null) {
                const row = createTableRow(land, plot, index);
                tbody.appendChild(row);
            } else {
                showToast("error", `Invalid land at index ${index}: ${land}`);
            }
        });
    });
    setReadonly();

}
function setReadonly() {
    $('.mrntableselectexcel input[type="text"]').attr('readonly', true);
}


function createTableRow(land, plot, index) {
    const row = document.createElement("tr");

    // Create checkbox cell
    const checkboxCell = document.createElement("td");
    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.dataset.rowId = index; // Store the index as data attribute
    checkbox.addEventListener("change", updateProcessButton);
    checkboxCell.appendChild(checkbox);
    checkboxCell.appendChild(checkbox);

    const hiddenInputs = document.createElement("div");
    hiddenInputs.style.display = "none";

    // Add hidden inputs for any additional data you need to store
    const hiddenData = {
        // Land data
        land_id: land.id ?? "",
        land_area: land.plot_area ?? "",
        land_location: `${land.address}`,
        // Plot data
        plot_id: plot.id ?? "",
        plot_area: plot.plot_area ?? "",
        dimension: plot.dimension ?? "",
        plot_valuation: plot.plot_valuation ?? "",
        address: plot.address ?? "",
        property_type: plot.type_of_usage ?? "",
    };

    // Create hidden input elements
    Object.entries(hiddenData).forEach(([key, value]) => {
        const hiddenInput = document.createElement("input");
        hiddenInput.type = "hidden";
        hiddenInput.name = `hidden_${key}`;
        hiddenInput.value = value;
        hiddenInputs.appendChild(hiddenInput);
    });

    checkboxCell.appendChild(hiddenInputs);

    const cellsData = [
        land.document_no ?? "N/A",
        land.name ?? "N/A",
        plot.document_no ?? "N/A",
        plot.khasara_no ?? "N/A",
        // land.district ?? "N/A",
        // land.state_relation?.name ?? "N/A",
        land.country ?? "N/A",
        land.pincode ?? "N/A",
    ];

    // Add checkbox cell first
    row.appendChild(checkboxCell);

    // Add other cells
    cellsData.forEach((cellValue) => {
        const cell = document.createElement("td");
        cell.textContent = cellValue;
        row.appendChild(cell);
    });

    return row;
}

// Function to handle land_detail_process button click
function handleProcessDisbursal() {
    const checkedRows = document.querySelectorAll(
        'input[type="checkbox"]:checked'
    );
    const selectedData = [];

    checkedRows.forEach((checkbox) => {
        const row = checkbox.closest("tr");
        const hiddenInputs = row
            .querySelector("div")
            .querySelectorAll('input[type="hidden"]');

        // Collect visible data
        const visibleData = {
            plot_document_no: row.cells[3].textContent,
            khasara_no: row.cells[4].textContent,
        };

        // Collect hidden data
        const hiddenData = {};
        hiddenInputs.forEach((input) => {
            const key = input.name.replace("hidden_", "");
            hiddenData[key] = input.value;
        });

        // Combine visible and hidden data
        selectedData.push({
            ...visibleData,
            ...hiddenData,
        });
    });

    return selectedData; // You can process this data further as needed
}

function updateProcessButton() {
    const processButton = document.getElementById("land_detail_process");
    const checkedBoxes = document.querySelectorAll(
        'input[type="checkbox"]:checked'
    );

    // Enable button if at least one checkbox is checked, disable otherwise
    processButton.disabled = checkedBoxes.length === 0;
}

function deleteSelectedRows() {
    const checkboxes = tbody.querySelectorAll('input[type="checkbox"]:checked');
    checkboxes.forEach((checkbox) => {
        const row = checkbox.closest("tr");
        row.remove();
    });
    updateTotals();
}

function addNewRow() {
    const lastRow = tbody.querySelector("tr:last-child");
    if (lastRow) {
        const newRow = lastRow.cloneNode(true);

        // Uncheck the checkbox in the new row
        newRow.querySelector('input[type="checkbox"]').checked = false;

        // Generate a unique ID for the checkbox
        const uniqueId = "unique_" + Date.now();
        newRow.querySelector('input[type="checkbox"]').id = uniqueId;
        newRow.querySelector("label").setAttribute("for", uniqueId);

        // Clear the 'disabled' attribute from inputs that were disabled
        newRow.querySelectorAll("input[disabled]").forEach((input) => {
            input.removeAttribute("disabled");
        });

        tbody.appendChild(newRow);
        addInputListeners(newRow);
    }
}
function calculateChargesWithoutModal(leaseAmount, otherAmountInput, totalPlotAmountInput, otherAmountJson, uniqueId) {
    let totalCharges = 0;

    // Assuming `otherChargesData` is available
    if (otherChargesData[uniqueId]) {
        otherChargesData[uniqueId].forEach((data) => {
            const percentageValue = parseFloat(data.percentage) || 0;
            const fixedValue = parseFloat(data.value) || 0;

            if (percentageValue > 0) {
                const calculatedValue = (leaseAmount * percentageValue) / 100;
                totalCharges += calculatedValue;
            } else {
                totalCharges += fixedValue;
            }
        });
    }

    if (totalCharges > 0) {
        otherAmountInput.value = totalCharges.toFixed(2);
        totalPlotAmountInput.value = (leaseAmount + totalCharges).toFixed(2);
    } else {
        otherAmountInput.value = "0.00";
        totalPlotAmountInput.value = leaseAmount.toFixed(2);
    }

    // Update the JSON with calculated data
    otherAmountJson.value = JSON.stringify(otherChargesData[uniqueId]);
}



function addInputListeners(row) {
    console.log(row);
    const leaseAmount = row.querySelector("#add_lease_amount");
    const otherAmount = row.querySelector("#add_other_amount");
    const otherAmountJson = row.querySelector("#add_other_amount_json");
    const totalPlotAmount = row.querySelector("#add_total_plot_amount");
    const addOtherChargesBtn = row.querySelector(
        "[data-bs-target='#add_other_charges_model']"
    );
    const uniqueId = row.querySelector("[data-id]").getAttribute("data-id");


    [leaseAmount, otherAmount].forEach((input) => {
        input.addEventListener("input", () => {
            const currentLeaseAmount = parseFloat(leaseAmount.value) || 0;
            calculateChargesWithoutModal(currentLeaseAmount, otherAmount, totalPlotAmount, otherAmountJson, uniqueId);
            updateTotals();
        });
    });

    addOtherChargesBtn.addEventListener("click", (e) => {
        e.preventDefault();
        // Extract the data-id from the button
        const uniqueId = e.target.attributes["data-id"].value;

        currentRowLeaseAmount = parseFloat(leaseAmount.value) || 0;
        initializeOtherChargesModal(
            row,
            uniqueId,
            currentRowLeaseAmount,
            otherAmount,
            totalPlotAmount,
            otherAmountJson
        );
    });
}

function initializeOtherChargesModal(
    row,
    uniqueId,
    leaseAmount,
    otherAmountInput,
    totalPlotAmountInput,
    otherAmountJson
) {

    rowCounter++;
    const addRowBtn = $("#add_other_charges_row");
    const chargesBody = document.getElementById("other_charges_body");
    // Unique identifier for each row
    //let newId = `${uniqueId}-${rowCounter}`;
    const rowId = `#${uniqueId}`;
    const rowdata = $(row).closest("tr");
    const landParcelId = rowdata.find(".land-parcel-id").val();
    const landPlotId = rowdata.find(".land-plot-id").val();

    if (!otherChargesData[uniqueId]) {
        otherChargesData[uniqueId] = [];
    }

    function addChargesRow(data = {}) {

        const rowCount = chargesBody.querySelectorAll(rowId).length + 1;

        const newRow = `
                <tr id="${uniqueId}">
                    <td>${rowCount}</td>
                    <input type="hidden" class="other-charges-land-id" value="${landParcelId || ""
            }" /><input type="hidden" class="other-charges-plot-id" value="${landPlotId || ""
            }" />
                    <td><input type="text" class="form-control mw-100 other-charges-name" value="${data.name || ""
            }" /></td>
                    <td><input type="number" class="form-control mw-100 other-charges-percentage" value="${data.percentage || ""
            }" ${data.value ? "readonly" : ""} /></td>
                    <td><input type="number" class="form-control mw-100 other-charges-value" value="${data.value || ""
            }" ${data.percentage ? "readonly" : ""} /></td>
                    <td><button class="btn btn-danger btn-sm otherdelete-row"><i data-feather="trash-2"></i></button></td>
                </tr>
            `;
        chargesBody.insertAdjacentHTML("beforeend", newRow);
        feather.replace();
        updateRowData(uniqueId);
    }

    addRowBtn.off("click").on("click", function () {

        addChargesRow();
        //updateRowData();
    });

    $("#other_charges_body")
        .off("click")
        .on("click", (e) => {
            if (e.target.closest(".otherdelete-row")) {
                e.target.closest("tr").remove();
                calculateTotalCharges();
                updateRowNumbers();
                updateRowData(uniqueId);
            }
        });

    $("#other_charges_body")
        .off("input")
        .on("input", (e) => {
            if (
                e.target.classList.contains("other-charges-percentage") ||
                e.target.classList.contains("other-charges-value")
            ) {
                const row = e.target.closest("tr");
                const percentageInput = row.querySelector(
                    ".other-charges-percentage"
                );
                const valueInput = row.querySelector(".other-charges-value");

                if (e.target.value !== "") {
                    if (e.target === percentageInput) {
                        valueInput.value = "";
                        valueInput.disabled = true;
                    }
                    else {
                        percentageInput.value = "";
                        percentageInput.disabled = true;
                    }
                } else {
                    percentageInput.disabled = false;
                    valueInput.disabled = false;
                }

                calculateTotalCharges();
                updateRowData(uniqueId);
            }
        });

    function calculateTotalCharges() {
        let totalCharges = 0;
        chargesBody.querySelectorAll(rowId).forEach((row) => {
            const nameInput = row.querySelector(".other-charges-name");
            const percentageInput = row.querySelector(".other-charges-percentage");
            const valueInput = row.querySelector(".other-charges-value");

            if (!nameInput || !percentageInput || !valueInput) return;

            const percentageValue = parseFloat(percentageInput.value) || 0;
            const fixedValue = parseFloat(valueInput.value) || 0;

            if (percentageValue > 0) {
                const calculatedValue = (leaseAmount * percentageValue) / 100;
                totalCharges += calculatedValue;

                // Automatically set the calculated value in the "other-charges-value" input field
                valueInput.value = calculatedValue.toFixed(2);
            } else {
                totalCharges += fixedValue;
            }
        });


        if (totalCharges > 0) {
            $("#other_charges_total").text(totalCharges.toFixed(2) || 0);
            otherAmountInput.value = totalCharges.toFixed(2);
            totalPlotAmountInput.value = (leaseAmount + totalCharges).toFixed(
                2
            );
            updateTotals();
        }
        else {

            $("#other_charges_total").text(0);
            otherAmountInput.value = 0.00;
            updateTotals();
        }
    }

    function updateRowNumbers() {
        chargesBody.querySelectorAll(rowId).forEach((row, index) => {
            row.querySelector("td:first-child").textContent = index + 1;
        });
    }

    function updateRowData(unqId) {
        otherChargesData[unqId] = []; // Reset the specific array each time

        chargesBody.querySelectorAll(rowId).forEach((row) => {
            const landInput = row.querySelector(".other-charges-land-id");
            const plotInput = row.querySelector(".other-charges-plot-id");
            const nameInput = row.querySelector(".other-charges-name");
            const percentageInput = row.querySelector(".other-charges-percentage");
            const valueInput = row.querySelector(".other-charges-value");

            if (landInput && plotInput && nameInput && percentageInput && valueInput) {
                otherChargesData[unqId].push({
                    land: landInput.value,
                    plot: plotInput.value,
                    name: nameInput.value,
                    percentage: percentageInput.value,
                    value: valueInput.value,
                });
            }
        });

        otherAmountJson.value = JSON.stringify(otherChargesData);
    }


    // Save button click
    $("#add_other_charges_button")
        .off("click")
        .on("click", () => {
            $("#add_other_charges_model").modal("hide");
            calculateTotalCharges();
            updateRowData(uniqueId);

        });


    function init() {
        chargesBody.innerHTML = "";

        if (otherChargesData[uniqueId].length === 0) {
            addChargesRow();
        } else {
            otherChargesData[uniqueId].forEach((data) => addChargesRow(data));
        }
        $("#other_charges_total").text(0);

        calculateTotalCharges();
    }
    init();
}
function calculateTotal() {
    let totalValue = parseFloat($("#lease_sub_total").val()) || 0;
    totalValue += parseFloat($("#lease_other_charges").val()) || 0;

    let otherChargesTotal = 0; // To hold the total of other charges
    $('#othercharges_body tr').each(function () {
        let percentage = parseFloat($(this).find('.othercharges-percentage').val()) || 0;
        let chargeValue = parseFloat($(this).find('.othercharges-value').val()) || 0;

        // Calculate charge based on percentage of the total value
        if (percentage > 0) {
            let calculatedCharge = (totalValue * (percentage / 100)); // Calculate based on percentage
            otherChargesTotal += calculatedCharge; // Sum up calculated charges

            // Automatically update the other-charges-value field with the calculated charge
            $(this).find('.othercharges-value').val(calculatedCharge.toFixed(2));

        } else {
            otherChargesTotal += chargeValue; // If percentage is not present, just add the value
        }
    });

    $('#othercharges_total').text(otherChargesTotal.toFixed(2));
    updateData();
    calextracharge();

    // Update the total display in the footer

}

function calculateRowTotal(row) {
    const leaseAmount =
        parseFloat(row.querySelector("#add_lease_amount").value) || 0;
    const otherAmount =
        parseFloat(row.querySelector("#add_other_amount").value) || 0;
    const totalPlotAmount = row.querySelector("#add_total_plot_amount");

    totalPlotAmount.value = (leaseAmount + otherAmount).toFixed(2);
}

function updateTotals() {
    totalLeaseAmount = 0;
    totalOtherCharges = 0;
    totalPlotsAmount = 0;

    tbody.querySelectorAll("tr").forEach((row) => {
        totalLeaseAmount +=
            parseFloat(row.querySelector("#add_lease_amount").value) || 0;
        totalOtherCharges +=
            parseFloat(row.querySelector("#add_other_amount").value) || 0;
        totalPlotsAmount +=
            parseFloat(row.querySelector("#add_total_plot_amount").value) || 0;
    });

    document.getElementById("total_lease_amount").textContent =
        totalLeaseAmount.toLocaleString('en-IN', { maximumFractionDigits: 2 });
    document.getElementById("subtotal").textContent =
        totalLeaseAmount.toLocaleString('en-IN', { maximumFractionDigits: 2 });
    document.getElementById("lease_sub_total").value =
        totalLeaseAmount.toFixed(2);  // Keep this as toFixed if you're storing a value without commas

    document.getElementById("total_other_charges").textContent =
        totalOtherCharges.toLocaleString('en-IN', { maximumFractionDigits: 2 });
    document.getElementById("othercharge").textContent =
        totalOtherCharges.toLocaleString('en-IN', { maximumFractionDigits: 2 });
    document.getElementById("lease_other_charges").value =
        totalOtherCharges.toFixed(2);  // Keep this as toFixed if you're storing a value without commas

    document.getElementById("total_plots_amount").textContent =
        totalPlotsAmount.toLocaleString('en-IN', { maximumFractionDigits: 2 });

    calculateRepaymentPeriod();
    calcualtetax();

}

deleteBtn.addEventListener("click", (e) => {
    e.preventDefault();
    deleteSelectedRows();
});
/*
addNewPlotBtn.addEventListener("click", (e) => {
    e.preventDefault();
    addNewRow();
});
*/


function populateTable(selectedRowsData) {
    console.log(selectedRowsData);
    tbody.innerHTML = ""; // Clear existing content

    selectedRowsData.forEach((rowData) => {
        const rowHtml = generateRowHtml(rowData);
        tbody.insertAdjacentHTML("beforeend", rowHtml);
    });
    tbody.querySelectorAll("tr").forEach((row) => {
        addInputListeners(row);
    });
    updateTotals();
}

function generateRowHtml(rowData) {
    const table = document.querySelector("table"); // Select the table element
    const rowCount = table.querySelectorAll("tr").length + 1;
    const uniqueId = generateUniqueId();

    return `
        <tr>
            <td class="customernewsection-form">
                <div class="form-check form-check-primary custom-checkbox">
                    <input type="checkbox" class="form-check-input" id="${uniqueId}">
                    <label class="form-check-label" for="${uniqueId}"></label>
                </div>
            </td>
            <input type="hidden" name="plot_details[${rowCount}][land_parcel_id]" class="land-parcel-id" value="${rowData.land_id || ""
        }">
            <input type="hidden" name="plot_details[${rowCount}][land_plot_id]" class="land-plot-id" value="${rowData.plot_id || ""
        }">
            <td class="poprod-decpt">
                <input type="text" name="plot_details[${rowCount}][plot_document_no]" value="${rowData.plot_document_no || ""
        }" class="form-control mw-100 ledgerselecct mb-25" />
            </td>
            <td><input type="text" name="plot_details[${rowCount}][khasara_no]" value="${rowData.khasara_no || ""
        }" class="form-control mw-100" /></td>
            <td><input type="text" name="plot_details[${rowCount}][plot_area]" value="${rowData.plot_area || ""
        }" class="form-control mw-100" /></td>
            <td><input type="text" name="plot_details[${rowCount}][dimension]" value="${rowData.dimension || ""
        }" class="form-control mw-100" /></td>
            <td><input type="text" name="plot_details[${rowCount}][plot_valuation]" value="${rowData.plot_valuation || ""
        }" class="form-control mw-100" /></td>
            <td><input type="text" name="plot_details[${rowCount}][address]" value="${rowData.address || ""
        }" class="form-control mw-100" /></td>
            <td>
                <input type="text" name="plot_details[${rowCount}][land_property_type]" value="${rowData.property_type || ""
        }" class="form-control mw-100" /></td>
            </td>
            <td><input type="number" class="form-control mw-100 text-end" name="plot_details[${rowCount}][land_lease_amount]" id="add_lease_amount" placehonder="00" /></td>
            <td>
                <div class="position-relative d-flex align-items-center">
                    <input type="number" class="form-control mw-100 text-end" name="plot_details[${rowCount}][land_other_charges]" id="add_other_amount" placeholder="00" style="width: 70px" readonly/>
                    <input type="hidden" class="form-control mw-100 text-end" name="plot_details[${rowCount}][land_other_charges_json]" id="add_other_amount_json" placeholder="00" style="width: 70px" readonly/>
                    <div class="ms-50">
                        <button data-bs-toggle="modal" data-bs-target="#add_other_charges_model" data-id="${uniqueId}" class="btn p-25 btn-sm btn-outline-secondary"
                            style="font-size: 10px">Add</button>
                    </div>
                </div>
            </td>
            <td><input type="number" placeholder="00" class="form-control mw-100 text-end" name="plot_details[${rowCount}][land_total_amount]" id="add_total_plot_amount" readonly/></td>
        </tr>
        `;
}

function generateUniqueId(length = 8) {
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    const numbers = "0123456789";
    let result = chars.charAt(Math.floor(Math.random() * chars.length)); // Ensure first character is a letter
    const allChars = chars + numbers;
    for (let i = 1; i < length; i++) {
        const randomIndex = Math.floor(Math.random() * allChars.length);
        result += allChars[randomIndex];
    }
    return result;
}

// Initialize all event listeners and functions on page load
document.addEventListener("DOMContentLoaded", function () {
    setupFilters();
    document
        .getElementById("land_detail_process")
        .addEventListener("click", () => {
            const selectedRowsData = handleProcessDisbursal();
            let filter_land_id = $("#filter_land_id").val();
            if (filter_land_id == selectedRowsData[0].land_id) {
                $("#land").val(selectedRowsData[0].land_id).trigger("change");
                $("#land_size")
                    .val(selectedRowsData[0].land_area)
                    .trigger("change");
                $("#land_location")
                    .val(selectedRowsData[0].land_location)
                    .trigger("change");
            }
            console.log('select', selectedRowsData);
            populateTable(selectedRowsData);
        });

    const processButton = document.getElementById("land_detail_process");

    // Initially disable the button
    processButton.disabled = true;

    processButton.addEventListener("click", () => {
        const selectedRowsData = handleProcessDisbursal();
    });
});
