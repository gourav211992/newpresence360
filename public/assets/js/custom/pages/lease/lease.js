let billingAddressFinalData = {};

// public/js/utils.js
const Utils = {
    // Ajax utility function
    ajaxRequest: function (url, method = "GET", value = null) {
        return $.ajax({
            url: url,
            type: method,
            data: value,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });
    },
};
function generateRepaymentSchedule() {
    // Get input values
    const leaseStartDate = new Date($('#leaseStartDate').val());
    const repaymentPeriodType = $('#repayment_period_type').val();
    let installmentAmount = parseFloat($('#lease_installment_cost').val()) || 0.00;
    const period = parseInt($('#repaymentPeriod').val());

    // Get the lease increment percentage and duration in years
    const leaseIncrementPercent = parseFloat($('input[name="lease_increment"]').val()) || 0.00;
    const leaseIncrementDuration = parseInt($('input[name="lease_increment_duration"]')
        .val()) || 0.00;

    const repaymentTableBody = $('#repaymentTableBody');
    repaymentTableBody.empty(); // Clear previous entries

    // Initialize next increment date to the day after the increment duration completes
    let nextIncrementDate = new Date(leaseStartDate);
    nextIncrementDate.setFullYear(nextIncrementDate.getFullYear() + leaseIncrementDuration);
    nextIncrementDate.setDate(nextIncrementDate.getDate() +
        1); // Move to the next day after the full year

    console.log(nextIncrementDate);

    // Generate repayment schedule
    for (let i = 1; i <= period; i++) {
        const dueDate = new Date(leaseStartDate);

        // Adjust the due date based on the selected repayment period type
        switch (repaymentPeriodType) {
            case 'monthly':
                dueDate.setMonth(leaseStartDate.getMonth() + i); // Increment month
                break;
            case 'quarterly':
                dueDate.setMonth(leaseStartDate.getMonth() + (i * 3)); // Increment quarter
                break;
            case 'yearly':
                dueDate.setFullYear(leaseStartDate.getFullYear() + i); // Increment year
                break;
        }

        // Apply lease increment only after the full increment period completes
        if (dueDate >= nextIncrementDate) {
            installmentAmount += installmentAmount * (leaseIncrementPercent /
                100); // Apply the increment
            nextIncrementDate.setFullYear(nextIncrementDate.getFullYear() +
                leaseIncrementDuration); // Move next increment to next period end
            nextIncrementDate.setDate(nextIncrementDate.getDate() +
                1); // Start from the day after the full increment period
        }
        let tax_percentage = parseFloat($('#tax_percentage').val())||null;

        // console.log(tax_percentage, parseFloat(installmentAmount.toFixed(2)), tax_percentage + parseFloat(installmentAmount.toFixed(2)))

        const status = "Pending"; // Status for each installment
        const tax_value= (parseFloat(tax_percentage) / 100) * parseFloat(installmentAmount.toFixed(2))||0;
        console.log(tax_value);
        const row = `
            <tr>
                <input type="hidden" name="sc[${i-1}][installment_cost]" value="${installmentAmount.toFixed(2)}">
                <input type="hidden" name="sc[${i-1}][due_date]" value="${dueDate.toLocaleDateString()}">
                <input type="hidden" name="sc[${i-1}][tax_amount]" value="${tax_value.toFixed(2)}">
                <input type="hidden" name="sc[${i-1}][status]" value="${status}">

                <td>${i}</td>
                <td class="text-dark fw-bolder">${installmentAmount.toFixed(2)}</td>
                <td>${tax_value.toFixed(2)}</td>
                <td>${dueDate.toLocaleDateString()}</td>
                <td><span class="badge rounded-pill badge-light-${status === "Paid" ? 'success' : 'warning'} badgeborder-radius">${status}</span></td>
            </tr>`;
                        repaymentTableBody.append(row);
    }

    //$("#Disbursement").modal('show');
}
function genrateLeaseNumber() {
    const element = document.querySelector("#series");

    element.addEventListener("change", function (event) {
        let bookId = $("#series").val();
        if (bookId) {
            let url = window.routes.getLeaseDocumentNumber.replace(
                ":book_id",
                bookId
            );
            Utils.ajaxRequest(url, "GET")
                .done(function (response) {
                    if (response.requestno) {
                        $("#document_no").val(response.requestno);
                    }
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    showToast(
                        "error",
                        "Error occurred while fetching lease document number. Please try again." +
                            errorThrown
                    );
                });
        } else {
            $("#document_no").val("");
        }
    });
}

function getCustomer() {
    $("#customer").on("select2:select", function (event) {
        let customerId = $(this).val();
        if (customerId)
        {
            let customers = DataOnLoad.customers;
            // Function to check for duplicate IDs
            function findCustomerId(data, idToCheck)
            {
                return data.filter(
                    (item) => Number(item.id) === Number(idToCheck)
                );
            }

            // For example, checking for the ID 2
            let sameIdData = findCustomerId(customers, customerId);
            console.log(sameIdData[0].display_name);
            if (sameIdData.length > 0) {
                $("#customer").val(sameIdData[0].id).trigger("change");
                //$("#currency").val(1);

                let billingAddress = findBillingAddress(sameIdData[0]);

                if (billingAddress) {
                    $("#billing_address").val(billingAddress.display_address);
                    $("#display_address_model").append(
                        `<option value="${billingAddress.id}">${billingAddress.display_address}</option>`
                    );
                    $("#display_address_model")
                        .val(billingAddress.id)
                        .trigger("change");

                    updateLocationDropdowns(billingAddress);
                } else {
                    updateLocationDropdowns(null);
                    $("#billing_address").val("");
                }
            }
        } else {
            $("#customer").val("");
        }
    });
}

// custoser find address
function findBillingAddress(customer) {
    if (!customer.addresses || !Array.isArray(customer.addresses)) {
        return null; // Return null if addresses is not an array or doesn't exist
    }

    return customer.addresses.find((address) => address.is_billing === 1);
}

function updateLocationDropdowns(initialBillingAddress) {
    console.log("billing", initialBillingAddress);

    let billingAddress = initialBillingAddress || {};

    // Function to clear all fields
    function clearAllFields() {
        $("#country_id_model").val("").trigger("change");
        $("#state_id_model")
            .empty()
            .append('<option value="">Select State</option>');
        $("#city_id_model")
            .empty()
            .append('<option value="">Select City</option>');
        $("#pincode_model").val("");
        $("#address_model").val("");
    }

    // Update fields if billingAddress exists
    if (billingAddress) {
        $("#country_id_model")
            .val(billingAddress.country_id || "")
            .trigger("change.select2");
        $("#pincode_model").val(billingAddress.pincode || "");
        $("#address_model").val(billingAddress.address || "");

        billingAddressFinalData.country_id = billingAddress.country_id || "";
        billingAddressFinalData.pincode = billingAddress.pincode || "";
        billingAddressFinalData.address = billingAddress.address || "";
    } else {
        clearAllFields();
    }

    // Function to update state dropdown
    function updateStates(countryId, callback) {
        if (!countryId) {
            $("#state_id_model")
                .empty()
                .append('<option value="">Select State</option>');
            $("#city_id_model")
                .empty()
                .append('<option value="">Select City</option>');
            return;
        }

        let url = window.routes.getStatesRoute.replace(
            ":country_id",
            countryId
        );
        $.get(url, function (states) {
            const $stateSelect = $("#state_id_model");
            populateSelect($stateSelect, states, "Select State");
            if (billingAddress.state_id) {
                $stateSelect
                    .val(billingAddress.state_id)
                    .trigger("change.select2");
                billingAddressFinalData.state_id = billingAddress.state_id;
            }
            if (callback) callback();
        });
    }

    // Function to update city dropdown
    function updateCities(stateId) {
        if (!stateId) {
            $("#city_id_model")
                .empty()
                .append('<option value="">Select City</option>');
            return;
        }

        let url = window.routes.getCitiesRoute.replace(":state_id", stateId);
        $.get(url, function (cities) {
            const $citySelect = $("#city_id_model");
            populateSelect($citySelect, cities, "Select City");
            if (billingAddress.city_id) {
                $citySelect
                    .val(billingAddress.city_id)
                    .trigger("change.select2");
                billingAddressFinalData.city_id = billingAddress.city_id;
            }
        });
    }

    // When country changes
    $("#country_id_model").on("select2:select", function () {
        const countryId = $(this).val();
        billingAddress.country_id = countryId;
        billingAddressFinalData.country_id = countryId;
        updateStates(countryId, function () {
            const stateId = $("#state_id_model").val();
            if (stateId) {
                updateCities(stateId);
            }
        });
    });

    // When state changes
    $("#state_id_model").on("select2:select", function () {
        const stateId = $(this).val();
        billingAddress.state_id = stateId;
        billingAddressFinalData.state_id = stateId;
        updateCities(stateId);
    });

    // When city changes
    $("#city_id_model").on("select2:select", function () {
        const cityId = $(this).val();
        billingAddress.city_id = cityId;
        billingAddressFinalData.city_id = cityId;
        $("#city_id_model").val(cityId).trigger("change");
    });

    // When pincode changes
    $("#pincode_model").on("input", function () {
        const pincode = $(this).val();
        $("#pincode_model").val(pincode);
        billingAddressFinalData.pincode = pincode;
    });

    // When address changes
    $("#address_model").on("input", function () {
        const address = $(this).val();
        $("#address_model").val(address);
        billingAddressFinalData.address = address;
    });

    // Initial update
    if (billingAddress.country_id) {
        updateStates(billingAddress.country_id, function () {
            if (billingAddress.state_id) {
                updateCities(billingAddress.state_id);
            }
        });
    }

    function populateSelect(element, data, placeholder) {
        let options = `<option value="">${placeholder}</option>`;
        data.forEach((item) => {
            options += `<option value="${item.id || item}">${
                item.name || item
            }</option>`;
        });
        element.html(options);
    }
}

// New function to submit address data
function submitAddressData() {
    // Ensure all required fields are filled
    if (
        !billingAddressFinalData.country_id ||
        !billingAddressFinalData.state_id ||
        !billingAddressFinalData.city_id ||
        !billingAddressFinalData.pincode ||
        !billingAddressFinalData.address
    ) {
        showToast("error", "Please fill all required fields");
        return;
    }
}

function submintAddress() {
    $("#edit_address_button").on("click", function (e) {
        e.preventDefault();

        console.log("address submit ", billingAddressFinalData);
        submitAddressData(); // Submit data when pincode or address changes
    });
}

function getExchangeRate() {
    const element = document.querySelector("#currency");

    element.addEventListener("change", function (event) {
        let currencyId = element.value;
        if (currencyId) {
            const today = new Date();

            // Extract the year, month, and day
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, "0"); // Months are zero-indexed
            const day = String(today.getDate()).padStart(2, "0");

            // Format the date as YYYY-MM-DD
            const formattedDate = `${year}-${month}-${day}`;

            let url = window.routes.getExchangeRate;
            let data = {
                currency: currencyId,
                date: formattedDate,
            };
            Utils.ajaxRequest(url, "Post", data)
                .done(function (response) {
                    if (response.exchangeRate.status == "true") {
                        $("#exchange_rate").val(response.exchangeRate);
                    } else {
                        showToast("error", response.exchangeRate.message);
                    }
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    showToast(
                        "error",
                        "Error occurred while fetching lease document number. Please try again." +
                            errorThrown
                    );
                });
        } else {
            element.value = "";
        }
    });
}

function selectLand() {
    const element = document.querySelector("#land");

    element.addEventListener("change", function (event) {
        let landId = element.value;
    });
}

function showToast(type, message) {
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

    // Trigger the toast based on type (success, error, warning, etc.)
    Toast.fire({
        icon: type,
        title: message,
    });
}

// Initialize all event listeners and functions on page load
document.addEventListener("DOMContentLoaded", function () {
   // genrateLeaseNumber();
    getExchangeRate();
    getCustomer();
    submintAddress();

    $(".submit-form").on("click", function (e) {
        e.preventDefault(); // Prevent default form submission
        let index = 0;
        generateRepaymentSchedule();

        $.each(otherChargesData, function (uniqueId, otherCharge) {
            $.each(otherCharge, function (key, val) {
                index++;
                var inputFields = `
            <input type="hidden" class="dynamic-input" name="other_charges[${uniqueId}][${index}][land_parcel_id]" value="${val.land}" />
            <input type="hidden" class="dynamic-input" name="other_charges[${uniqueId}][${index}][land_plot_id]" value="${val.plot}" />
            <input type="hidden" class="dynamic-input" name="other_charges[${uniqueId}][${index}][name]" value="${val.name}" />
            <input type="hidden" class="dynamic-input" name="other_charges[${uniqueId}][${index}][percentage]" value="${val.percentage}" />
            <input type="hidden" class="dynamic-input" name="other_charges[${uniqueId}][${index}][value]" value="${val.value}" />
        `;

                // Append the fields to the #other_charges_hidden_fields container
                $("#other_charges_hidden_fields").append(inputFields);
            });
        });
        //$("#lease-form").find("input:hidden, select:hidden").remove();

        let status = $(this).data("val");
        $("#status").val(status);

        const formId = $(this).attr("form"); // Get the form ID
        //var innerHtml = $("#" + formId).html();
        //console.log(innerHtml);
        $("#" + formId).submit();
    });
});
