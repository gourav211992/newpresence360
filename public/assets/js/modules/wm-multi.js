$(document).ready(function () {
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

    /*Add New Row*/
    $(document).on("click", ".addNewItemBtn", (e) => {
        levelCounter++;

        let levelId = $("[name='level_id']").val();
        let moduleType = $("[name='module_type']").val();
        if (!checkBasicFilledDetail()) {
            Swal.fire({
                title: "Error!",
                text: "Please fill header detail first",
                icon: "error",
            });
            e.preventDefault();
            return false;
        }

        let parentDetails = [];
        let is_last_level = 0;
        let is_first_level = 0;

        $.ajax({
            type: "GET",
            url:
                "/warehouse-multiple-mappings/level-parents?level_id=" +
                levelId,
            async: false,
            success: function (data) {
                if (data.status == 200) {
                    is_last_level = data.is_last_level;
                    is_first_level = data.is_first_level;
                    parentDetails = Array.isArray(data.parentDetails)
                        ? data.parentDetails
                        : [];

                    localStorage.setItem(
                        "parentDetails",
                        JSON.stringify(parentDetails)
                    );

                    // Generate parent <option>
                    let parentOptions = parentDetails
                        .map(
                            (parent) =>
                                `<option value="${parent.id}">${parent.name}</option>`
                        )
                        .join("");

                    let currentWV = "";
                    if (moduleType == "edit") {
                        currentWV = `
                            <td>
                                <input type="hidden"
                                       class="form-control max_weight mw-100 mb-25"
                                       name="details[${levelCounter}][max_weight]" />
                            </td>
                            <td>
                                <input type="hidden"
                                       class="form-control max_volume mw-100 mb-25"
                                       name="details[${levelCounter}][max_volume]" />
                            </td>`;
                    }

                    // Build row HTML with correct names
                    let rowHtml = `
                    <tr>
                        <td class="customernewsection-form">
                            <div class="form-check form-check-primary custom-checkbox">
                                <input type="checkbox" class="form-check-input"/>
                                <label class="form-check-label"></label>
                            </div>
                        </td>
                        <td>
                            <input type="text"
                                   placeholder="Enter"
                                   class="form-control name mw-100 mb-25"
                                   name="details[${levelCounter}][name]" />
                        </td>
                        <td>
                            <div class="form-check form-check-primary custom-checkbox">
                                <input class="form-check-input" type="checkbox"
                                    name="details[${levelCounter}][storage_point]"
                                    ${is_last_level === 1 ? "checked" : ""} />
                                <label class="form-check-label"></label>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <input type="hidden" class="is_first_level"
                                       name="details[${levelCounter}][is_first_level]"
                                       value="${is_first_level}">
                                <input type="hidden" class="is_last_level"
                                       name="details[${levelCounter}][is_last_level]"
                                       value="${is_last_level}">
                                <select class="form-select mw-100 mb-25 parent-dropdown select2 parent_id"
                                        multiple
                                        name="details[${levelCounter}][parent_id][]"
                                        style="min-width:200px;">
                                    ${parentOptions}
                                </select>
                                <input type="checkbox" class="form-check-input select-all-parents"/>
                            </div>
                        </td>
                        <td>
                            <input type="text" class="form-control max_weight mw-100 mb-25"
                                   name="details[${levelCounter}][max_weight]"
                                   placeholder="Enter"
                                   ${is_last_level === 1 ? "" : "disabled"} />
                        </td>
                        <td>
                            <input type="text" class="form-control max_volume mw-100 mb-25"
                                   name="details[${levelCounter}][max_volume]"
                                   placeholder="Enter"
                                   ${is_last_level === 1 ? "" : "disabled"} />
                        </td>
                        ${currentWV}
                    </tr>`;

                    $(".mrntableselectexcel").append(rowHtml);
                    $(".select2").select2();
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: "Failed to fetch level parents",
                        icon: "error",
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: "Error!",
                    text: "An error occurred while fetching level parents",
                    icon: "error",
                });
            },
        });
    });

    $(".mrntableselectexcel").on("change", ".select-all-parents", function () {
        const $container = $(this).closest("div");
        const $select = $container.find("select.parent-dropdown");

        if ($(this).is(":checked")) {
            $select.find("option").prop("selected", true);
        } else {
            $select.find("option").prop("selected", false);
        }

        $select.trigger("change");

        // Fix min-width on select2 container instead of <select>
        const $select2Container = $select.next(".select2");
        if (
            $select2Container.length &&
            !$select2Container.data("fixed-width")
        ) {
            const currentWidth = $select2Container.outerWidth();
            $select2Container.css("min-width", currentWidth + "px");
            $select2Container.data("fixed-width", true);
        }
    });

    $(".mrntableselectexcel tr").each(function (levelCounter) {
        // Update 'name' for name input
        $(this)
            .find("input.name")
            .attr("name", `details[${levelCounter + 1}][name]`);

        // Update 'name' for the second checkbox and set the name for storage_point
        let $storagePointCheckbox = $(this)
            .find("input[type='checkbox']")
            .eq(1);
        $storagePointCheckbox.attr(
            "name",
            `details[${levelCounter + 1}][storage_point]`
        );

        // Check if the storage_point checkbox is checked, and enable/disable max_weight and max_volume
        if ($storagePointCheckbox.is(":checked")) {
            $(this).find("input.max_weight").prop("disabled", false);
            $(this).find("input.max_volume").prop("disabled", false);
        } else {
            $(this).find("input.max_weight").prop("disabled", true);
            $(this).find("input.max_volume").prop("disabled", true);
        }

        // Update 'name' for select elements based on 'level-name' data attribute
        $(this)
            .find("input.is_first_level")
            .attr("name", `details[${levelCounter + 1}][is_first_level]`);
        $(this)
            .find("input.is_last_level")
            .attr("name", `details[${levelCounter + 1}][is_last_level]`);
        $(this)
            .find("select.parent_id")
            .attr("name", `details[${levelCounter + 1}][parent_id][]`);
        // Update 'name' for max_weight and max_volume inputs
        $(this)
            .find("input.max_weight")
            .attr("name", `details[${levelCounter + 1}][max_weight]`);
        $(this)
            .find("input.max_volume")
            .attr("name", `details[${levelCounter + 1}][max_volume]`);
    });

    $(".mrntableselectexcel").on("input", "input[name*='[name]']", function () {
        let input = $(this);
        let name = input.val().trim();
        let currentRow = input.closest("tr");
        let currentParentDropdown = currentRow.find(".parent-dropdown");

        let allNames = $("input[name*='[name]']")
            .map(function () {
                return $(this).val().trim();
            })
            .get();

        if (allNames.filter((n) => n === name).length > 1) {
            if (!currentParentDropdown.data("original-options")) {
                currentParentDropdown.data(
                    "original-options",
                    currentParentDropdown.html()
                );
            }

            currentParentDropdown.html(
                currentParentDropdown.data("original-options")
            );

            let currentSelected = currentParentDropdown.val();
            let selectedInOtherRows = $(".parent-dropdown")
                .map(function () {
                    return this !== currentParentDropdown[0]
                        ? parseInt($(this).val())
                        : null;
                })
                .get()
                .filter(Boolean);

            currentParentDropdown.find("option").each(function () {
                let optionVal = parseInt($(this).val());
                if (
                    optionVal &&
                    selectedInOtherRows.includes(optionVal) &&
                    optionVal != currentSelected
                ) {
                    $(this).remove();
                }
            });

            currentParentDropdown.val(currentSelected).trigger("change");
        } else {
            if (currentParentDropdown.data("original-options")) {
                currentParentDropdown.html(
                    currentParentDropdown.data("original-options")
                );
                currentParentDropdown.trigger("change");
            }
        }
    });

    $(".mrntableselectexcel").on(
        "change",
        "input[type='checkbox']",
        function () {
            let $row = $(this).closest("tr");

            let $maxWeight = $row.find("input.max_weight");
            let $maxVolume = $row.find("input.max_volume");

            if ($(this).is(":checked")) {
                $maxWeight.prop("disabled", false);
                $maxVolume.prop("disabled", false);
            } else {
                $maxWeight.prop("disabled", true);
                $maxVolume.prop("disabled", true);
            }
        }
    );

    $(".select2").select2();

    // Delete selected rows
    // Enable level_id if all rows are deleted
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
                        url: "/warehouse-multiple-mappings/delete-details",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr(
                                "content"
                            ),
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
                    let checkbox = $(this).find(
                        "td:first-child .form-check-input"
                    );
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

    $(".select2").select2();
});

/*Sub Store drop down*/
function getLevels(id) {
    var store_id = id;
    var url = "/warehouse-multiple-mappings/levels?" + "store_id=" + store_id;
    $.ajax({
        type: "GET",
        url: url,
        data: {},
        success: function (data) {
            // console.log(data);
            if (data.status == 200) {
                console.log(data.data);
                let options = '<option value="">Select Level</option>';
                data.data.forEach((level) => {
                    options += `<option value="${level.id}">${level.name}</option>`;
                });
                $(".level_id").html(options);
                $(".level_id").prop("disabled", false);
            } else {
                $(".level_id").html(data);
                $(".level_id").prop("disabled", true);
            }
        },
    });
} // end of storeWiseLevels

/*Check filled all basic detail*/
function checkBasicFilledDetail() {
    let filled = false;
    let subStore = $("[name='sub_store_id']").val() || "";
    let subStoreLevel = $("[name='level_id']").val() || "";
    if (subStore && subStoreLevel) {
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
