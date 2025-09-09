/*Approve modal*/
$(document).on("click", "#approved-button", (e) => {
    let actionType = "approve";
    $("#approveModal").find("#action_type").val(actionType);
    $("#approveModal #popupTitle").text("Approve Application");
    $("#approveModal").modal("show");
});
$(document).on("click", "#reject-button", (e) => {
    let actionType = "reject";
    $("#approveModal").find("#action_type").val(actionType);
    $("#approveModal #popupTitle").text("Reject Application");
    $("#approveModal").modal("show");
});

/*Tbl row highlight*/
$(document).on("click", ".mrntableselectexcel tr", (e) => {
    $(e.target.closest("tr"))
        .addClass("trselected")
        .siblings()
        .removeClass("trselected");
});
$(document).on("keydown", function (e) {
    if (e.which == 38) {
        /*bottom to top*/
        $(".trselected")
            .prev("tr")
            .addClass("trselected")
            .siblings()
            .removeClass("trselected");
    } else if (e.which == 40) {
        /*top to bottom*/
        $(".trselected")
            .next("tr")
            .addClass("trselected")
            .siblings()
            .removeClass("trselected");
    }
});

/*Check box check and uncheck*/
$(document).on("change", "#itemTable > thead .form-check-input", (e) => {
    const isChecked = e.target.checked;
    $("#itemTable > tbody .form-check-input").each(function () {
        if (!$(this).is(":disabled")) {
            // Only check if the checkbox is not disabled
            $(this).prop("checked", isChecked);
        }
    });
});

$(document).on("change", "#itemTable > tbody .form-check-input", (e) => {
    const allChecked =
        $("#itemTable > tbody .form-check-input:not(:disabled)").length ===
        $("#itemTable > tbody .form-check-input:checked:not(:disabled)").length;

    $("#itemTable > thead .form-check-input").prop("checked", allChecked);
});

/*Attribute on change*/
$(document).on("change", '[name*="comp_attribute"]', (e) => {
    let rowCount = e.target
        .closest("tr")
        .querySelector('[name*="row_count"]').value;
    let attrGroupId = e.target.getAttribute("data-attr-group-id");
    $(
        `[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`
    ).val(e.target.value);
    qtyEnabledDisabled();
    setSelectedAttribute(rowCount);
});

/*Edit mode table calculation filled*/
if ($("#itemTable .mrntableselectexcel tr").length) {
    setTimeout(() => {
        $("[name*='component_item_name[1]']").trigger("focus");
        $("[name*='component_item_name[1]']").trigger("blur");
    }, 100);
}

/*Open item remark modal*/
$(document).on("click", ".addRemarkBtn", (e) => {
    let rowCount = e.target.closest("div").getAttribute("data-row-count");
    $("#itemRemarkModal #row_count").val(rowCount);
    let remarkValue = $("#itemTable #row_" + rowCount).find("[name*='remark']");

    if (!remarkValue.length) {
        $("#itemRemarkModal textarea").val("");
    } else {
        $("#itemRemarkModal textarea").val(remarkValue.val());
    }
    $("#itemRemarkModal").modal("show");
});

/*Submit item remark modal*/
$(document).on("click", ".itemRemarkSubmit", (e) => {
    let rowCount = $("#itemRemarkModal #row_count").val();
    let remarkValue = $("#itemTable #row_" + rowCount).find("[name*='remark']");
    let textValue = $("#itemRemarkModal").find("textarea").val();
    if (!remarkValue.length) {
        rowHidden = `<input type="hidden" value="${textValue}" name="components[${rowCount}][remark]" />`;
        $("#itemTable #row_" + rowCount)
            .find(".addRemarkBtn")
            .after(rowHidden);
    } else {
        $("#itemTable #row_" + rowCount)
            .find("[name*='remark']")
            .val(textValue);
    }
    $("#itemRemarkModal").modal("hide");
});

$("#attribute").on("hidden.bs.modal", function () {
    let rowCount = $("[id*=row_].trselected").attr("data-index");
    if ($(`[name="components[${rowCount}][qty]"]`).is("[readonly]")) {
        $(`[name="components[${rowCount}][vendor_code]"]`).trigger("focus");
    } else {
        $(`[name="components[${rowCount}][qty]"]`).trigger("focus");
    }
});

/*Vendor change*/
$(document).on("blur", '[name*="[vendor_code]"]', (e) => {
    if (!e.target.value) {
        $(e.target).closest("tr").find('[name*="[vendor_name]"').val("");
    }
});

//Disable form submit on enter button
document.querySelector("form").addEventListener("keydown", function (event) {
    if (event.key === "Enter") {
        event.preventDefault(); // Prevent form submission
    }
});
$("input[type='text']").on("keydown", function (event) {
    if (event.key === "Enter") {
        event.preventDefault(); // Prevent form submission
    }
});
$("input[type='number']").on("keydown", function (event) {
    if (event.key === "Enter") {
        event.preventDefault(); // Prevent form submission
    }
});

/*Qty enabled and disabled*/
function qtyEnabledDisabled() {
    $("tr[id*='row_']").each(function (index, item) {
        let qtyDisabled = false;
        if ($(item).find("[name*='[attr_name]']").length) {
            $(item)
                .find("[name*='[attr_name]']")
                .each(function () {
                    if ($(this).val().trim() === "") {
                        qtyDisabled = true;
                    }
                });
            $(item)
                .find("[name*='[qty]']")
                .attr("readonly", Boolean(qtyDisabled));
            if (qtyDisabled) {
                $(item).find("[name*='[qty]']").val("");
            }
        } else {
            $(item).find("[name*='[qty]']").attr("readonly", false);
        }
    });
}
qtyEnabledDisabled();

$(document).on("blur", '[name*="component_item_name"]', (e) => {
    if (!e.target.value) {
        $(e.target).closest("tr").find('[name*="[item_name]"]').val("");
        $(e.target).closest("tr").find('[name*="[item_id]"]').val("");
    }
});

$(document).on("keyup", "input[name*='[qty]']", function (e) {
    validateItems(e.target, false);
});

function validateItems(inputEle, itemChange = false) {
    let items = [];
    $("tr[id*='row_']").each(function (index, item) {
        let itemId = $(item).find("input[name*='[item_id]']").val();
        let uomId = $(item).find("select[name*='[uom_id]']").val();
        let soId = $(item).find("input[name*='[so_id]']").val();
        if (itemId && uomId) {
            let attr = [];
            $(item)
                .find("input[name*='[attr_name]']")
                .each(function (ind, it) {
                    const matches = it.name.match(
                        /components\[\d+\]\[attr_group_id\]\[(\d+)\]\[attr_name\]/
                    );
                    if (matches) {
                        const attr_id = parseInt(matches[1], 10);
                        const attr_value = parseInt(it.value, 10);
                        if (attr_id && attr_value) {
                            attr.push({ attr_id, attr_value });
                        }
                    }
                });
            items.push({
                item_id: itemId,
                uom_id: uomId,
                attributes: attr,
                so_id: soId,
            });
        }
    });

    if (items.length && hasDuplicateObjects(items)) {
        Swal.fire({
            title: "Error!",
            text: "Duplicate item!",
            icon: "error",
        });
        $(inputEle).val("");
        if (itemChange) {
            $(inputEle)
                .closest("tr")
                .find("input[name*='[item_name]']")
                .val("");
            $(inputEle).closest("tr").find("[name*='[uom_id]']").empty();
        }
    }
}

function hasDuplicateObjects(arr) {
    let seen = new Set();
    return arr.some((obj) => {
        let key = JSON.stringify(obj);
        if (seen.has(key)) {
            return true;
        }
        seen.add(key);
        return false;
    });
}

function initAutocompVendor(selector, type) {
    $(selector)
        .autocomplete({
            minLength: 0,
            source: function (request, response) {
                let item_id = $(this.element)
                    .closest("tr")
                    .find("[name*='[item_id]']")
                    .val();
                $.ajax({
                    url: "/search",
                    method: "GET",
                    dataType: "json",
                    data: {
                        q: request.term,
                        type: "vendor_list",
                        item_id: item_id,
                    },
                    success: function (data) {
                        response(
                            $.map(data, function (item) {
                                return {
                                    id: item.id,
                                    label: item.company_name,
                                    code: item.vendor_code,
                                    addresses: item.addresses,
                                };
                            })
                        );
                    },
                    error: function (xhr) {
                        console.error(
                            "Error fetching customer data:",
                            xhr.responseText
                        );
                    },
                });
            },
            select: function (event, ui) {
                let $input = $(this);
                let itemName = ui.item.value;
                let itemId = ui.item.id;
                let itemCode = ui.item.code;
                $input.attr("data-name", itemName);
                $input.val(itemCode);
                $input
                    .closest("tr")
                    .find("[name*='[vendor_name]']")
                    .val(itemName);
                $input.closest("tr").find("[name*='[vendor_id]']").val(itemId);
            },
            change: function (event, ui) {
                if (!ui.item) {
                    $(this).val("");
                    $(this).attr("data-name", "");
                    $(this)
                        .closest("tr")
                        .find("[name*='[vendor_name]']")
                        .val("");
                    $(this).closest("tr").find("[name*='[vendor_id]']").val("");
                }
            },
        })
        .focus(function () {
            if (this.value === "") {
                $(this).autocomplete("search", "");
                $(this).closest("tr").find("[name*='[vendor_name]']").val("");
                $(this).closest("tr").find("[name*='[vendor_id]']").val("");
            }
        })
        .on("input", function () {
            if ($(this).val().trim() === "") {
                $(this).removeData("selected");
                $(this).closest("tr").find("[name*='[vendor_name]']").val("");
                $(this).closest("tr").find("[name*='[vendor_id]']").val("");
            }
        });
}
if ($("[name*='[vendor_code]']").length) {
    initAutocompVendor("[name*='[vendor_code]']");
}

function updateIndentQty($row) {
    var reqQty = parseFloat($row.find('input[name$="[qty]"]').val()) || 0;
    var avlStock =
        parseFloat($row.find('input[name$="[avl_stock]"]').val()) || 0;
    var adjQtyInput = $row.find('input[name$="[adj_qty]"]');
    var adjQty = parseFloat(adjQtyInput.val()) || 0;
    if (adjQty > Math.min(reqQty, avlStock)) {
        adjQty = Math.min(reqQty, avlStock);
        adjQtyInput.val(adjQty);
    }

    var indentQty = reqQty - adjQty;
    $row.find('input[name$="[indent_qty]"]').val(indentQty.toFixed(2));
}

// When adj_qty changes
$(document).on(
    "keyup change",
    'input[name^="components"][name$="[adj_qty]"]',
    function () {
        var $row = $(this).closest("tr");
        updateIndentQty($row);
    }
);

// When req_qty changes
$(document).on(
    "keyup change",
    'input[name^="components"][name$="[qty]"]',
    function () {
        var $row = $(this).closest("tr");
        updateIndentQty($row);
    }
);

document.querySelectorAll("#orderTypeSelect").forEach((radio) => {
    radio.addEventListener("change", function () {
        document.getElementById("procurement_type").value = this.value;
    });
});

$(document).on("change", "#procurement_type", function () {
    let selectedValue = this.value;
    $("#procurement_type").val(selectedValue);
});

function copyItemRow() {
    let $rows = $("#itemTable > tbody tr");
    let $checked = $rows.find(".form-check-input:checked");

    if ($rows.length === 0) {
        Swal.fire({
            title: "Error!",
            text: "Please add at least one item to copy.",
            icon: "error",
        });
        return;
    }

    if ($checked.length === 0) {
        Swal.fire({
            title: "Error!",
            text: "Please select at least one item to copy.",
            icon: "error",
        });
        return;
    }

    let currentRowCount = $rows.length;
    if ($checked.length !== 1) {
        Swal.fire({
            title: "Error!",
            text: "Multiple items can not be cloned, Please check single item.",
            icon: "error",
        });
        return;
    }

    try {
        let $row = $checked.closest("tr");
        let $clone = cloneRow($row, ++currentRowCount);
        console.log($clone.html());

        $("#itemTable .mrntableselectexcel tr[id*='row_']:last").after($clone);
        $clone.closest("tr").find("input[name*='attr_group_id']").remove();
        $clone.closest("tr").find("input[name*='pi_item_id']").val("");

        initAutocompVendor("[name*='[vendor_code]']");
        initializeAutocomplete2(".comp_item_code");
        console.log($clone.html());

        setTimeout(() => {
            $clone.closest("tr").find(".attributeBtn").trigger("click");
        }, 100);

        $(".form-check-input").each(function () {
            $(this).prop("checked", false);
        });
    } catch (error) {
        console.error("Error copying item row:", error);
        Swal.fire({
            title: "Error!",
            text: "An unexpected error occurred while copying the item row.",
            icon: "error",
        });
    }
}

function cloneRow($row, newIndex) {
    let $clone = $row.clone();

    $clone.attr({
        id: "row_" + newIndex,
        "data-index": newIndex,
        "data-count": newIndex,
        "data-row-count": newIndex,
    });

    $clone.closest("tr").attr("id", "row_" + newIndex);
    $clone.find("[data-index]").attr("data-index", newIndex);
    $clone.find("[data-row-count]").attr("data-row-count", newIndex);
    $clone.find("[data-row-count]").attr("data-row-count", newIndex);

    $clone.find("[name]").each(function () {
        let name = $(this).attr("name");
        if (name) {
            name = name.replace(/\[\d+\]/g, "[" + newIndex + "]");
            $(this).attr("name", name);
        }
    });

    $clone.find("[id]").each(function () {
        let id = $(this).attr("id");
        if (id) {
            id = id.replace(/_\d+$/, "_" + newIndex);
            $(this).attr("id", id);
        }
    });

    $clone
        .find('input[type="checkbox"]')
        .prop("checked", false)
        .attr("data-id", "")
        .val(newIndex);
    return $clone;
}

$(document).on("click", ".toggle-expand", function (e) {
    e.preventDefault();
    var targetKey = $(this).data("target");
    var parentLevel = parseInt($(this).closest("tr").data("level"), 10);
    $('tr[data-row-key^="' + targetKey + '-"]').each(function () {
        var rowLevel = parseInt($(this).data("level"), 10);
        if (rowLevel === parentLevel + 1) {
            $(this).removeClass("d-none");
        }
    });
    $(this).addClass("d-none");
    $(this).siblings(".toggle-collapse").removeClass("d-none");
});

$(document).on("click", ".toggle-collapse", function (e) {
    e.preventDefault();
    var targetKey = $(this).data("target");
    $('tr[data-row-key^="' + targetKey + '-"]').addClass("d-none");
    $('tr[data-row-key^="' + targetKey + '-"] .toggle-collapse').addClass(
        "d-none"
    );
    $('tr[data-row-key^="' + targetKey + '-"] .toggle-expand').removeClass(
        "d-none"
    );
    $(this).addClass("d-none");
    $(this).siblings(".toggle-expand").removeClass("d-none");
});

// Analyze modal js code
function getSelectedPiIDS() {
    let ids = [];
    $(".pi_item_checkbox:checked").each(function () {
        ids.push($(this).val());
    });
    return ids;
}

function getSelectedPiIDS2() {
    let ids = [];
    $(".analyze_row:checked").each(function () {
        ids.push($(this).val());
    });
    return ids;
}

$(document).on("click", ".analyzeButton", (e) => {
    let ids = getSelectedPiIDS();
    if (!ids.length) {
        $("#soModal").modal("hide");
        Swal.fire({
            title: "Error!",
            text: "Please select at least one line item",
            icon: "error",
        });
        return false;
    }

    ids = JSON.stringify(ids);
    let d_date = $("input[name='document_date']").val() || "";
    let book_id = $("#book_id").val() || "";
    let rowCount = $("#itemTable tbody tr[id*='row_']").length;
    let isAttribute = 0;
    if ($("#attributeCheck").is(":checked")) {
        isAttribute = 1;
    } else {
        isAttribute = 0;
    }
    let selectedItems = [];
    if (!isAttribute) {
        $("#soModal .pi_item_checkbox:checked").each(function () {
            selectedItems.push({
                sale_order_id: Number($(this).val()),
                item_id: Number($(this).data("item-id")),
            });
        });
    }
    let selectedItemsParam = encodeURIComponent(JSON.stringify(selectedItems));
    let actionUrl =
        analyzeSoItemUrl +
        "?ids=" +
        ids +
        "&d_date=" +
        d_date +
        "&book_id=" +
        book_id +
        "&rowCount=" +
        rowCount +
        `&is_attribute=${isAttribute}&selected_items=${selectedItemsParam}`;
    fetch(actionUrl).then((response) => {
        return response.json().then((data) => {
            if (data.status == 200) {
                $("#analyzeDataTable").empty().append(data.data.pos);
                feather.replace();
                $("#soModal").modal("hide");
                $("#analyzeModal").modal("show");
            }
            if (data.status == 422) {
                Swal.fire({
                    title: "Error!",
                    text: data.message,
                    icon: "error",
                });
                return false;
            }
        });
    });
});

$(document).on("click", ".analyzeProcessBtn", (e) => {
    let ids = getSelectedPiIDS2();
    if (!ids.length) {
        // $("#soModal").modal('show');
        Swal.fire({
            title: "Error!",
            text: "Please select at least one line item",
            icon: "error",
        });
        return false;
    }

    ids = JSON.stringify(ids);
    console.log(ids);

    let d_date = $("input[name='document_date']").val() || "";
    let book_id = $("#book_id").val() || "";
    let rowCount = $("#itemTable tbody tr[id*='row_']").length;
    let isAttribute = 0;
    if ($("#attributeCheck").is(":checked")) {
        isAttribute = 1;
    } else {
        isAttribute = 0;
    }

    // if(!isAttribute) {
    //     $("#soModal .pi_item_checkbox:checked").each(function () {
    //         selectedItems.push({
    //             "sale_order_id": Number($(this).val()),
    //             "item_id": Number($(this).data("item-id"))
    //         });
    //     });
    // }
    let selectedItems = [];
    $(".analyze_row:checked").each(function () {
        let tr = $(this).closest("tr");
        if (!tr.hasClass("d-none")) {
            let $checkbox = $(this);
            let soItemIdsRaw = $checkbox.data("so-item-ids") || "";
            let soItemIds = soItemIdsRaw
                .toString()
                .split(",")
                .map((id) => id.trim().replace(/^['"]|['"]$/g, ""))
                .map((id) => Number(id))
                .filter((id) => id > 0);
            selectedItems.push({
                bom_id: Number($checkbox.val()),
                so_id: Number($checkbox.data("so-id")),
                so_item_id: Number($checkbox.data("so-item-id")),
                so_item_ids: soItemIds,
                level: Number($checkbox.data("level")),
                parent_bom_id: Number($checkbox.data("parent-bom-id")),
                item_id: Number($checkbox.data("item-id")),
                item_name: $checkbox.data("item-name"),
                item_code: $checkbox.data("item-code"),
                uom_id: Number($checkbox.data("uom-id")),
                uom_name: $checkbox.data("uom-name"),
                attribute: $checkbox.data("attribute"),
                total_qty: parseFloat($checkbox.data("total-qty")),
                store_name: $checkbox.data("store-name"),
                store_id: Number($checkbox.data("store-id")),
                doc_no: $checkbox.data("doc-no"),
                doc_date: $checkbox.data("doc-date"),
                main_so_item: Number($checkbox.data("main-so-item")),
            });
        }
    });

    let postData = {
        ids: ids,
        d_date: d_date,
        book_id: book_id,
        rowCount: rowCount,
        is_attribute: isAttribute,
        selected_items: selectedItems,
    };

    fetch(processSoActionUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        body: JSON.stringify(postData),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.status == 200) {
                if ($("#itemTable > tbody tr").length) {
                    $("#itemTable > tbody > tr:last").after(data.data.pos);
                } else {
                    $("#itemTable > tbody").empty().append(data.data.pos);
                }
                updateRowIndex(true);
                $("#soModal").modal("hide");
                initializeAutocomplete2(".comp_item_code");
                initializeAutocompleteCustomer("[name*='[customer_code]']");
                let newIds = [];
                $('input[name^="components"][name$="[so_item_id]"]').each(
                    function () {
                        let val = $(this).val();
                        if (val && val !== "0" && !newIds.includes(val)) {
                            newIds.push(val);
                        }
                    }
                );
                let existingIds = localStorage.getItem("selectedSoItemIds");
                if (existingIds) {
                    existingIds = JSON.parse(existingIds);
                    const mergedIds = Array.from(
                        new Set([...existingIds, ...newIds])
                    );
                    localStorage.setItem(
                        "selectedSoItemIds",
                        JSON.stringify(mergedIds)
                    );
                } else {
                    localStorage.setItem(
                        "selectedSoItemIds",
                        JSON.stringify(newIds)
                    );
                }
                $("#analyzeModal").modal("hide");
            }

            if (data.status == 422) {
                Swal.fire({
                    title: "Error!",
                    text: data.message,
                    icon: "error",
                });
                return false;
            }
        });
});

/*Checkbox for pi item list*/
$(document).on("change", ".po-order-detail > thead .form-check-input", (e) => {
    if (e.target.checked) {
        $(".po-order-detail > tbody .form-check-input").each(function () {
            $(this).prop("checked", true);
        });
    } else {
        $(".po-order-detail > tbody .form-check-input").each(function () {
            $(this).prop("checked", false);
        });
    }
});
$(document).on("change", ".po-order-detail > tbody .form-check-input", (e) => {
    if (!$(".po-order-detail > tbody .form-check-input:not(:checked)").length) {
        $(".po-order-detail > thead .form-check-input").prop("checked", true);
    } else {
        $(".po-order-detail > thead .form-check-input").prop("checked", false);
    }
});

$(document).on("input", 'input[id^="analyse_required_qty_"]', function () {
    let $required = $(this);
    let rowIndex = $required.attr("id").replace("analyse_required_qty_", "");

    let $total = $("#analyse_total_qty_" + rowIndex);
    let $remaining = $("#analyse_remaining_qty_" + rowIndex);

    let total = parseFloat($total.val()) || 0;
    let required = parseFloat($required.val()) || 0;

    if (required > total) {
        required = total;
        $required.val(required);
    }

    $remaining.val(total - required);


    let parentKey = $required.data("row-key");
    console.log("Parent Key:", parentKey);

    cascadeToChildren(parentKey, required);
});

function cascadeToChildren(parentKey, parentRequired) {
    $("#analyzeDataTable")
        .find('tr[data-parent-key="' + parentKey + '"]')
        .each(function () {
            let $childRow = $(this);
            let childKey = $childRow.data("row-key");

            let $childTotal = $childRow.find('input[id^="analyse_total_qty_"]');
            let $childRequired = $childRow.find(
                'input[id^="analyse_required_qty_"]'
            );
            let $childRemaining = $childRow.find(
                'input[id^="analyse_remaining_qty_"]'
            );

            let childTotal = parseFloat($childTotal.val()) || 0;
            let childRequired = Math.min(parentRequired, childTotal);

            $childRequired.val(childRequired);
            $childRemaining.val(childTotal - childRequired);

            console.log("Cascade → Parent:", parentKey, "Child:", childKey);

            cascadeToChildren(childKey, childRequired);
        });
}
