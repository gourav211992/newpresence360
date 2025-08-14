let levelCounter = 1;
let levelCounter2 = 0;

// Loop through each row inside the .mrntableselectexcel table
$(".mrntableselectexcel tr").each(function () {
    const index = parseInt($(this).data("index"), 10);
    if (!isNaN(index) && index > levelCounter2) {
        levelCounter2 = index;
    }
});

levelCounter = levelCounter2 + 1; // Start from the next index

/*Check filled all basic detail*/
function checkBasicFilledDetail() {
    let filled = false;
    let store = $("[name='store_id']").val() || "";
    let subStore = $("[name='sub_store_id']").val() || "";
    if (store && subStore) {
        filled = true;
    }
    return filled;
}

/*Check filled component*/
function checkComponentRowExist() {
    let filled = false;
    let rowCount = $("#itemTable [id*='row_']").length;
    if (rowCount) {
        filled = true;
    }
    return filled;
}

// Get Store Wise Sub Stores
function getSubStores(storeLocationId) {
    console.log("storeLocationId", storeLocationId);

    const storeId = storeLocationId;
    $.ajax({
        url: "/sub-stores/store-wise",
        method: "GET",
        dataType: "json",
        data: {
            store_id: storeId,
        },
        success: function (data) {
            if (data.status == 200 && data.data.length) {
                let options = '<option value="">Select Warehouse</option>';
                data.data.forEach(function (location) {
                    options += `<option value="${location.id}">${location.name}</option>`;
                });
                $(".sub_store").empty();
                $(".sub_store").html(options);
            } else {
                $(".sub_store").empty();
                Swal.fire({
                    title: "Error!",
                    text: "Warehouse does not exist for location.",
                    icon: "error",
                });
            }
        },
        error: function (xhr) {
            Swal.fire({
                title: "Error!",
                text: xhr?.responseJSON?.message,
                icon: "error",
            });
        },
    });
}

// Function to capitalize the first letter of a string
function capitalizeFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function getDetails(subStoreId) {
    const storeId = $("[name='store_id']").val();

    if (!checkBasicFilledDetail()) {
        Swal.fire({
            title: "Error!",
            text: "Please fill header detail first",
            icon: "error",
        });
        return false;
    }

    $.ajax({
        type: "GET",
        url: `/warehouse-item-mappings/existing-details?store_id=${storeId}&sub_store_id=${subStoreId}`,
        success: function (data) {
            if (data.status === 200 && data.is_exist === 1) {
                const mappings = data.mappings || [];
                $(".mrntableselectexcel").html("");

                mappings.forEach((mapping, index) => {
                    const categories = Array.isArray(mapping.categories)
                        ? mapping.categories
                        : [];
                    // const subCategories = Array.isArray(mapping.sub_categories) ? mapping.sub_categories : [];
                    const items = Array.isArray(mapping.items)
                        ? mapping.items
                        : [];
                    const structures = Array.isArray(mapping.structures)
                        ? mapping.structures
                        : [];
                    const detail_id = mapping.detail_id ?? null; // null fallback

                    let categoryOptions = categories
                        .map(
                            (cat) =>
                                `<option value="${cat.id}" ${
                                    cat.selected ? "selected" : ""
                                }>${cat.name}</option>`
                        )
                        .join("");

                    // let subCategoryOptions = subCategories.map(sub =>
                    //     `<option value="${sub.id}" ${sub.selected ? 'selected' : ''}>${sub.name}</option>`
                    // ).join('');

                    let itemOptions = items
                        .map(
                            (item) =>
                                `<option value="${item.id}" ${
                                    item.selected ? "selected" : ""
                                }>${item.name}</option>`
                        )
                        .join("");

                    let structureHtml = "";
                    structures.forEach((structure, levelIndex) => {
                        const levelName = structure.name.toLowerCase(); // normalize
                        const levelOptions = (structure.options || [])
                            .map(
                                (opt) =>
                                    `<option value="${opt.id}" ${
                                        opt.selected ? "selected" : ""
                                    }>${opt.name}</option>`
                            )
                            .join("");

                        structureHtml += `
                            <div class="form-group me-2"  style="min-width: 220px; display: flex; align-items: center;">
                                <label class="form-label" style="font-weight: bold; padding-right:10px;  margin-bottom: 4px;">${
                                    structure.name
                                }</label>
                                <select class="form-select select2 child-dropdown"
                                        data-level-index="${levelIndex}"
                                        data-level-name="${levelName}"
                                        name="details[${
                                            index + 1
                                        }][${levelName}][]"
                                        multiple>
                                    ${levelOptions}
                                </select>
                            </div>`;
                    });

                    let rowHtml = `
                        <tr class="item-row">
                            <td class="customernewsection-form">
                                <div class="form-check form-check-primary custom-checkbox">
                                    <input type="checkbox" class="form-check-input" />
                                    <label class="form-check-label"></label>
                                </div>
                                <input type="hidden" name="details[${
                                    index + 1
                                }][detail_id]" value="${detail_id}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="form-group me-2"  style="min-width: 420px; display: flex; align-items: center;">
                                        <select class="form-select select2 category-select" name="details[${
                                            index + 1
                                        }][category_id][]" multiple>
                                            ${categoryOptions}
                                        </select>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="form-group me-2"  style="min-width: 220px; display: flex; align-items: center;">
                                        <select class="form-select select2 item-select" name="details[${
                                            index + 1
                                        }][item_id][]" multiple>
                                            ${itemOptions}
                                        </select>
                                    </div>
                                </div>
                            </td>
                            <td>
                              <div class="d-flex align-items-center">
                                 ${structureHtml}
                              </div>
                            </td>
                        </tr>
                    `;

                    $(".mrntableselectexcel").append(rowHtml);
                    updateUniqueSelectOptions();
                });

                $(".select2").select2({ width: "100%" });
            } else {
                $(".mrntableselectexcel").html("");
            }
        },
        error: function () {
            Swal.fire({
                title: "Error!",
                text: "An error occurred while fetching mapping data",
                icon: "error",
            });
            $(".mrntableselectexcel").html("");
        },
    });
}

/*Add New Row*/
$(document).on("click", ".addNewItemBtn", (e) => {
    levelCounter++;

    let storeId = $("[name='store_id']").val();
    let subStoreId = $("[name='sub_store_id']").val();
    let moduleType = $("[name='module_type']").val();
    if (!checkBasicFilledDetail()) {
        Swal.fire({
            title: "Error!",
            text: "Please fill header detail first",
            icon: "error",
        });
        return false;
    }

    let categories = [];
    let structures = [];

    var url =
        "/warehouse-item-mappings/details?" +
        "store_id=" +
        storeId +
        "&sub_store_id=" +
        subStoreId;
    $.ajax({
        type: "GET",
        url: url,
        async: false,
        success: function (data) {
            if (data.status == 200) {
                categories = Array.isArray(data.categories)
                    ? data.categories
                    : [];
                structures = Array.isArray(data.structures)
                    ? data.structures
                    : [];

                // Generate categories from the response data
                let categoryOptions = ``;
                categories.forEach((category) => {
                    categoryOptions += `<option value="${category.id}">${category.name}</option>`;
                });

                // Generate structures from the response data
                let structureOptions = ``;
                structures.forEach((structure) => {
                    structureOptions += `<option value="${structure.id}">${structure.name}</option>`;
                });

                // Now build rowHtml using the parentOptions
                let rowHtml = `
                    <tr class="item-row">
                        <td class="customernewsection-form">
                            <div class="form-check form-check-primary custom-checkbox">
                                <input type="checkbox" class="form-check-input" />
                                <label class="form-check-label"></label>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="form-group me-2"  style="min-width: 420px; display: flex; align-items: center;">
                                    <select class="form-select select2 category-select" multiple>
                                        ${categoryOptions}
                                    </select>
                                </div>
                            </div>
                        </td>
                        <!--  <td>
                             <div class="d-flex align-items-center">
                                 <div class="form-group me-2"  style="min-width: 220px; display: flex; align-items: center;">
                                     <select class="form-select select2 sub-category-select" multiple>
                                         <option value="">Select Sub Category</option>
                                     </select>
                                 </div>
                             </div>
                         </td> -->
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="form-group me-2"  style="min-width: 220px; display: flex; align-items: center;">
                                    <select class="form-select select2 item-select" multiple>
                                        <option value="">Select Item</option>
                                    </select>
                                </div>
                            </div>
                        </td>`;

                rowHtml += `<td><div class="d-flex align-items-center">`;

                structures.forEach((level, index) => {
                    let selectOptions = (level.storage_point_details || [])
                        .map(
                            (option) =>
                                `<option value="${option.id}">${option.name}</option>`
                        )
                        .join("");

                    let isFirst = index === 0;

                    rowHtml += `
                        <div class="form-group me-2"  style="min-width: 220px; display: flex; align-items: center;">
                            <label class="form-label" style="font-weight: bold; padding-right:10px;  margin-bottom: 4px;">
                                ${level.name}
                            </label>
                            <select class="form-select select2 child-dropdown"
                                    data-level-index="${index}"
                                    data-level-name="${level.name.toLowerCase()}"
                                    multiple>
                                <option value="">Select</option>
                                ${isFirst ? selectOptions : ""}
                            </select>
                        </div>`;
                });

                rowHtml += `</div></td></tr>`;

                $(".mrntableselectexcel").append(rowHtml);
                updateUniqueSelectOptions();

                // Re-initialize select2 for newly added elements
                $(".select2").select2({
                    width: "100%",
                });
            } else {
                Swal.fire({
                    title: "Error!",
                    text: "Failed to fetch records",
                    icon: "error",
                });
            }
        },
        error: function () {
            Swal.fire({
                title: "Error!",
                text: "An error occurred while fetching records",
                icon: "error",
            });
        },
    });

    $(".mrntableselectexcel tr").each(function (levelCounter) {
        // Update 'name' for name input
        $(this)
            .find("select.category-select")
            .attr("name", `details[${levelCounter + 1}][category_id][]`);
        // $(this).find("select.sub-category-select").attr("name", `details[${levelCounter + 1}][sub_category_id][]`);
        $(this)
            .find("select.item-select")
            .attr("name", `details[${levelCounter + 1}][item_id][]`);

        // For each row, find all child-dropdowns
        $(this)
            .find("select.child-dropdown")
            .each(function (levelIndex) {
                const levelName =
                    $(this).data("level-name") || `level_${levelIndex}`;
                $(this).attr(
                    "name",
                    `details[${levelCounter + 1}][${levelName}][]`
                );
            });
    });
});

$(document).on("change", ".category-select", function () {
    let selectedIds = $(this).val(); // from current select
    let row = $(this).closest(".item-row"); // adjust to your row wrapper class

    let subCatSelect = row.find(".sub-category-select");
    let itemSelect = row.find(".item-select");

    if (selectedIds && selectedIds.length > 0) {
        $.ajax({
            type: "GET",
            url: "/warehouse-item-mappings/get-sub-categories",
            data: { "parent_ids[]": selectedIds },
            traditional: true,
            success: function (response) {
                if (response.status == 200) {
                    let existingSubCatIds = new Set();
                    subCatSelect.find("option").each(function () {
                        existingSubCatIds.add($(this).val());
                    });

                    let existingItemIds = new Set();
                    itemSelect.find("option").each(function () {
                        existingItemIds.add($(this).val());
                    });

                    // Append new sub-categories
                    response.data.forEach(function (subCat) {
                        if (!existingSubCatIds.has(subCat.id.toString())) {
                            subCatSelect.append(
                                `<option value="${subCat.id}">${subCat.name}</option>`
                            );
                        }
                    });

                    // Append new items
                    response.items.forEach(function (item) {
                        if (!existingItemIds.has(item.id.toString())) {
                            itemSelect.append(
                                `<option value="${item.id}">${item.item_code}</option>`
                            );
                        }
                    });
                }
            },
        });
    } else {
        subCatSelect.empty();
        itemSelect.empty();
    }
});

// Get Items based on category + subcategory
$(document).on("change", ".sub-category-select", function () {
    let row = $(this).closest(".item-row");

    let selectedCatIds = row.find(".category-select").val();
    let selectedSubCatIds = $(this).val();
    let itemSelect = row.find(".item-select");

    itemSelect.empty();

    if (selectedCatIds && selectedCatIds.length > 0) {
        $.ajax({
            type: "GET",
            url: "/warehouse-item-mappings/get-items",
            data: {
                "category_ids[]": selectedCatIds,
                // 'sub_category_ids[]': selectedSubCatIds
            },
            traditional: true,
            success: function (response) {
                if (response.status == 200) {
                    response.items.forEach(function (item) {
                        itemSelect.append(
                            `<option value="${item.id}">${item.item_code}</option>`
                        );
                    });
                    itemSelect.trigger("change.select2");
                }
            },
        });
    }
});

// Child Dropdowns
$(document).on("change", ".child-dropdown", function () {
    let $currentDropdown = $(this);
    let selectedIds = $currentDropdown.val();
    let currentLevelIndex = parseInt($currentDropdown.data("level-index"));
    let $row = $currentDropdown.closest("tr");
    let $nextDropdown = $row.find(
        `select.child-dropdown[data-level-index="${currentLevelIndex + 1}"]`
    );

    // Clear dropdowns after next level
    $row.find(`select.child-dropdown`).each(function () {
        let index = parseInt($(this).data("level-index"));
        if (index > currentLevelIndex + 1) {
            $(this).empty().append('<option value="">Select</option>');
        }
    });

    if ($nextDropdown.length && selectedIds && selectedIds.length > 0) {
        $.ajax({
            type: "GET",
            url: "/warehouse-item-mappings/get-childs",
            data: { "parent_ids[]": selectedIds },
            traditional: true,
            success: function (response) {
                if (response.status === 200) {
                    let existingSelected = $nextDropdown.val() || [];
                    let newOptions = "";
                    let validValues = [];

                    response.data.forEach((item) => {
                        newOptions += `<option value="${item.id}">${item.name}</option>`;
                        validValues.push(String(item.id));
                    });

                    $nextDropdown.empty().append(newOptions);

                    // Restore previously selected values if still valid
                    let retained = existingSelected.filter((val) =>
                        validValues.includes(val)
                    );
                    if (retained.length > 0) {
                        $nextDropdown.val(retained).trigger("change");
                    }
                }
            },
        });
    }
});

// Delete selected rows
$(document).on("click", ".deleteBtn", (e) => {
    let itemIdsToDelete = [];
    $(".mrntableselectexcel tr").each(function () {
        let checkbox = $(this).find("td:first-child .form-check-input");
        if (checkbox.is(":checked")) {
            let itemId = $(this).find("input[name$='[detail_id]']").val();
            if (itemId) {
                itemIdsToDelete.push(itemId);
            }
        }
    });
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            // If there are IDs to delete in DB
            if (itemIdsToDelete.length > 0) {
                $.ajax({
                    url: "/warehouse-item-mappings/delete-details",
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr("content"),
                        ids: itemIdsToDelete,
                    },
                    success: function (response) {
                        if (response.status == "success") {
                        }

                        // DOM cleanup if not redirected
                        $(".mrntableselectexcel tr").each(function () {
                            let checkbox = $(this).find(
                                "td:first-child .form-check-input"
                            );
                            if (checkbox.is(":checked")) {
                                $(this).remove();
                            }
                        });

                        $(".mrntableselectexcel tr").each(function (index) {
                            $(this)
                                .find("input[name^='details']")
                                .each(function () {
                                    let nameAttr = $(this).attr("name");
                                    if (nameAttr) {
                                        let updatedName = nameAttr.replace(
                                            /\[\d+\]/,
                                            `[${index + 1}]`
                                        );
                                        $(this).attr("name", updatedName);
                                    }
                                });
                        });

                        if ($(".mrntableselectexcel tr").length === 0) {
                            $(".level_id").prop("disabled", false);
                            $(".sub_store_id").prop("disabled", false);
                        }

                        Swal.fire(
                            "Deleted!",
                            "Selected rows have been deleted.",
                            "success"
                        );
                    },
                    error: function () {
                        Swal.fire(
                            "Error",
                            "Failed to delete from database",
                            "error"
                        );
                    },
                });
            }

            // Remove rows from the DOM
            $(".mrntableselectexcel tr").each(function () {
                let checkbox = $(this).find("td:first-child .form-check-input");
                if (checkbox.is(":checked")) {
                    $(this).remove();
                }
            });

            // Re-index the remaining rows
            $(".mrntableselectexcel tr").each(function (index) {
                $(this)
                    .find("input[name^='details']")
                    .each(function () {
                        let nameAttr = $(this).attr("name");
                        if (nameAttr) {
                            let updatedName = nameAttr.replace(
                                /\[\d+\]/,
                                `[${index + 1}]`
                            );
                            $(this).attr("name", updatedName);
                        }
                    });
            });

            Swal.fire({
                title: "Deleted!",
                text: "Selected rows have been deleted.",
                icon: "success",
            });
        }
    });
});

$(document).on(
    "change focus blur",
    ".category-select, .sub-category-select, .item-select",
    function () {
        updateUniqueSelectOptions();
    }
);

function updateUniqueSelectOptions() {
    // Step 1: Collect selected values from all rows
    let usedCategories = new Map();
    let usedSubCategories = new Map();
    let usedItems = new Map();

    $(".mrntableselectexcel tr").each(function () {
        const $row = $(this);
        const rowEl = $row.get(0);

        ($row.find(".category-select").val() || []).forEach((val) => {
            val = String(val);
            if (!usedCategories.has(val)) usedCategories.set(val, []);
            usedCategories.get(val).push(rowEl);
        });

        ($row.find(".sub-category-select").val() || []).forEach((val) => {
            val = String(val);
            if (!usedSubCategories.has(val)) usedSubCategories.set(val, []);
            usedSubCategories.get(val).push(rowEl);
        });

        ($row.find(".item-select").val() || []).forEach((val) => {
            val = String(val);
            if (!usedItems.has(val)) usedItems.set(val, []);
            usedItems.get(val).push(rowEl);
        });
    });

    // Step 2: Disable reused options in other rows
    $(".mrntableselectexcel tr").each(function () {
        const $row = $(this);
        const rowEl = $row.get(0);

        // CATEGORY
        $row.find(".category-select option").each(function () {
            const val = String($(this).attr("value"));
            const owners = usedCategories.get(val) || [];
            const selectedHere = $row.find(".category-select").val() || [];

            if (
                owners.length > 0 &&
                !selectedHere.includes(val) &&
                owners.some((r) => r !== rowEl)
            ) {
                $(this).prop("disabled", true);
            } else {
                $(this).prop("disabled", false);
            }
        });

        // SUB CATEGORY
        // $row.find(".sub-category-select option").each(function () {
        //     const val = String($(this).attr("value"));
        //     const owners = usedSubCategories.get(val) || [];
        //     const selectedHere = $row.find(".sub-category-select").val() || [];

        //     if (owners.length > 0 && !selectedHere.includes(val) && owners.some(r => r !== rowEl)) {
        //         $(this).prop("disabled", true);
        //     } else {
        //         $(this).prop("disabled", false);
        //     }
        // });

        // ITEM
        $row.find(".item-select option").each(function () {
            const val = String($(this).attr("value"));
            const owners = usedItems.get(val) || [];
            const selectedHere = $row.find(".item-select").val() || [];

            if (
                owners.length > 0 &&
                !selectedHere.includes(val) &&
                owners.some((r) => r !== rowEl)
            ) {
                $(this).prop("disabled", true);
            } else {
                $(this).prop("disabled", false);
            }
        });
    });

    // Step 3: Refresh select2
    $(".category-select, .sub-category-select, .item-select").each(function () {
        $(this).trigger("change.select2");
    });
}
