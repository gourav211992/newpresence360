const dropdown1 = $("#asset_id"); // First dropdown
const dropdown2 = $("#asset_code"); // Second dropdown
var baseUrl = window.location.origin;


function edit_page(){
    const dropdown1text = $("#asset_id option:selected").text(); // First dropdown
    dropdown2.val(dropdown1.val()).select2();
    fetchAndPopulateTable(dropdown1.val(), dropdown1text);
}




dropdown1.on("change", function () {
    $("#selectedSubAssets").val('');
    const dropdown1text = $("#asset_id option:selected").text(); // First dropdown
    $("#sub_asset_modal").modal('show');
    dropdown2.val(dropdown1.val()).select2();
    fetchAndPopulateTable(dropdown1.val(), dropdown1text);
});

dropdown2.on("change", function () {
    $("#selectedSubAssets").val('');
    const dropdown2text = $("#asset_code option:selected").text(); // Second dropdown
    dropdown1.val(dropdown2.val()).select2();
    fetchAndPopulateTable(dropdown2.val(), dropdown2text);
});

// Function to fetch and populate data
function fetchAndPopulateTable(assetId, code) {


    $.ajax({
        url: baseUrl + "/fixed-asset/sub_asset", // API Route
        type: "GET", // Or "POST" if needed
        data: {
            id: assetId
        }, // Send ID as a parameter
        dataType: "json",
        success: function (response) {
            let tableBody = $("#sub_asset");
            tableBody.empty(); // Clear existing rows
            let selectBox = $("#sub_asset_code");
            selectBox.empty(); // Clear existing options
            selectBox.append(new Option("Select", ""));

            // Loop through response data and append rows to table
            $.each(response, function (index, row) {
                let newRow = `
            <tr>
                <td><input type="checkbox" class="subAssetCheckbox" value="${row.id}" data-sub-asset-code="${row.sub_asset_code}"></td>
                <td>${code}</td>
                <td>${row.sub_asset_code}</td>
                <td>${row.current_value}</td>
            </tr>

        `;
                tableBody.append(newRow);
                selectBox.append(new Option(row.sub_asset_code, row.id));


               
            });
            let selectedSubAssets = JSON.parse($("#selectedSubAssets").val());
            let selectedSubCodes=[];
            selectedSubAssets.forEach(function (subAssetCode) {
                // Iterate through each checkbox in the table
                $('.subAssetCheckbox').each(function() {
                    let checkbox = $(this); // Get the current checkbox
                    if (checkbox.val() === subAssetCode) {
                        checkbox.prop('checked', true);
                        selectedSubCodes.push(checkbox.data("sub-asset-code"));
                    }

                });
                updateSubAssetBadges(selectedSubCodes);
                
            });
           

        },
        error: function (xhr, status, error) {

            let tableBody = $("#sub_asset");
            tableBody.empty(); // Clear existing rows
            let newRow = `
            <tr>
                <td colspan="4" class="text-center">No data Available</td>
            </tr>

        `;
            tableBody.append(newRow);
        }
    });
}
$(document).on("change", "#selectAll", function () {
    $(".subAssetCheckbox").prop("checked", $(this).prop("checked"));
});

function updateSelectedSubAssets() {
    let selectedSubIds = [];
    let selectedSubCodes = [];

    $(".subAssetCheckbox:checked").each(function () {
        selectedSubIds.push($(this).val());
        selectedSubCodes.push($(this).data("sub-asset-code"));
    });

    $("#selectedSubAssets").val(JSON.stringify(selectedSubIds)); // Store selected IDs in hidden input
    updateSubAssetBadges(selectedSubCodes);

    // Update "Select All" checkbox state
    if ($(".subAssetCheckbox:checked").length === $(".subAssetCheckbox").length) {
        $("#selectAll").prop("checked", true);
    } else {
        $("#selectAll").prop("checked", false);
    }
    if (selectedSubIds.length === 0) {
        showToast('error', "Please select at least one sub-asset.");
        return;
    } else {
        $('#sub_asset_modal').modal('hide')
    }
}

function updateSubAssetBadges(selectedSubCodes) {
    let badgeContainer = $("#subAssetBadgeContainer");
    let fullListContainer = $("#fullSubAssetList");

    badgeContainer.empty();
    fullListContainer.empty();

    if (selectedSubCodes.length === 0) return;

    let displayCount = 3; // Show only first 3 badges
    let moreCount = selectedSubCodes.length - displayCount;

    selectedSubCodes.slice(0, displayCount).forEach(code => {
        badgeContainer.append(
            `<span class="badge rounded-pill badge-light-secondary badgeborder-radius me-25 fw-bold font-small-2">${code}</span>`
        );
    });

    // Add "+X" if more than displayCount
    if (moreCount > 0) {
        badgeContainer.append(`<span data-bs-target="#pickuplocation" data-bs-toggle="modal">+${moreCount}</span>`);
    }

    // Populate full list in modal
    selectedSubCodes.forEach(code => {
        fullListContainer.append(`
<span class="badge rounded-pill badge-light-secondary badgeborder-radius me-25 fw-bold font-small-2">${code}</span>
`);
    });
}
$("#sub_asset_code").change(function () {
    let selectedCode = $(this).find("option:selected").text().trim();
    $("#sub_asset tr").each(function () {
        let rowSubAssetCode = $(this).find("td:eq(2)").text().trim(); // Get sub asset code from 2nd column

        if (selectedCode === "" || rowSubAssetCode === selectedCode || selectedCode === "Select") {
            $(this).show();  // Show matching rows
        } else {
            $(this).hide();  // Hide non-matching rows
        }
    });
});
function validateForm() {
    $('.preloader').show();
    // Get the JSON data from #selectedSubAssets
    let subAssetJson = $("#selectedSubAssets").val();


    // Check if the parsed JSON is an array or object and ensure it's not empty
    if (Array.isArray(subAssetJson) && subAssetJson.length === 0) {
        $('.preloader').hide();
        showToast('error', 'Please Select a Sub Asset.');
        return false;  // Prevent form submission
    }

    // If it's an object, check if sub_asset is available
    if (typeof subAssetJson === 'object' && subAssetJson !== null) {
        if (!subAssetJson.sub_asset || subAssetJson.sub_asset.trim() === "") {
            $('.preloader').hide();
            showToast('error', 'Please Select a Sub Asset.');
            return false;  // Prevent form submission
        }
    }

    // If the conditions pass, allow form submission
    return true;
}

