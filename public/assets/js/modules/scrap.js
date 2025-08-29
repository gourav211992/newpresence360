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
$(document).on(
    "change",
    "#scavengingItemsTable > thead .form-check-input",
    (e) => {
        const isChecked = e.target.checked;
        $("#scavengingItemsTable > tbody .form-check-input").each(function () {
            if (!$(this).is(":disabled")) {
                // Only check if the checkbox is not disabled
                $(this).prop("checked", isChecked);
            }
        });
    }
);

$(document).on(
    "change",
    "#scavengingItemsTable > tbody .form-check-input",
    (e) => {
        const allChecked =
            $("#scavengingItemsTable > tbody .form-check-input:not(:disabled)")
                .length ===
            $(
                "#scavengingItemsTable > tbody .form-check-input:checked:not(:disabled)"
            ).length;

        $("#scavengingItemsTable > thead .form-check-input").prop(
            "checked",
            allChecked
        );
    }
);

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
if ($("#scavengingItemsTable .mrntableselectexcel tr").length) {
    setTimeout(() => {
        $("[name*='component_item_name[1]']").trigger("focus");
        $("[name*='component_item_name[1]']").trigger("blur");
    }, 100);
}

/*Open item remark modal*/
$(document).on("click", ".addRemarkBtn", (e) => {
    let rowCount = e.target.closest("div").getAttribute("data-row-count");
    $("#itemRemarkModal #row_count").val(rowCount);
    let remarkValue = $(
        "#scavengingItemsTable #scavengingItemsTr_" + rowCount
    ).find("[name*='remark']");

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
    let remarkValue = $(
        "#scavengingItemsTable #scavengingItemsTr_" + rowCount
    ).find("[name*='remark']");
    let textValue = $("#itemRemarkModal").find("textarea").val();
    if (!remarkValue.length) {
        rowHidden = `<input type="hidden" value="${textValue}" name="scavenging[${rowCount}][remark]" />`;
        $("#scavengingItemsTable #scavengingItemsTr_" + rowCount)
            .find(".addRemarkBtn")
            .after(rowHidden);
    } else {
        $("#scavengingItemsTable #scavengingItemsTr_" + rowCount)
            .find("[name*='remark']")
            .val(textValue);
    }
    $("#itemRemarkModal").modal("hide");
});

$("#attribute").on("hidden.bs.modal", function () {
    let rowCount = $("[id*=scavengingItemsTr_].trselected").attr("data-index");
    if ($(`[name="scavenging[${rowCount}][qty]"]`).is("[readonly]")) {
        $(`[name="scavenging[${rowCount}][qty]"]`).trigger("focus");
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
    $("tr[id*='scavengingItemsTr_']").each(function (index, item) {
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
    $("tr[id*='scavengingItemsTr_']").each(function (index, item) {
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
function isEmptyValue(val) {
    return (
        val === undefined ||
        val === null ||
        val === "" ||
        val === "0" ||
        val === 0
    );
}

function isEmptyValue(val) {
    return (
        val === undefined ||
        val === null ||
        val === "" ||
        val === "0" ||
        val === 0
    );
}

function validateStore(event = null) {
    if (isEmptyValue($("#store_id").val())) {
        Swal.fire({
            title: "Error!",
            text: "Please select store first!",
            icon: "error",
        });

        if (event) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
        return false; // stop execution
    }
    return true;
}

function validateSubStore(event = null) {
    if (isEmptyValue($("#sub_store_id").val())) {
        Swal.fire({
            title: "Error!",
            text: "Please select sub store first!",
            icon: "error",
        });

        if (event) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
        return false; // stop execution
    }
    return true;
}

/* ================================
   Add New Item Row
================================ */
$(document).on("click", "#addNewItemBtn", (e) => {
    if (!validateStore(e) || !validateSubStore(e)) return false;

    let rowsLength = $("#scavengingItemsTable > tbody > tr").length;
    let lastRow = $("#scavengingItemsTable .mrntableselectexcel tr:last");
    // Last row validation
    let lastTrObj = {
        item_id: "0",
        attr_require: false,
        scavengingItemsTr_length: lastRow.length,
    };

    if (lastRow.length > 0) {
        let item_id = lastRow.find("[name*='[item_id]']").val();
        let attr_require = true;

        if (lastRow.find("[name*='attr_name']").length) {
            let emptyAttr = lastRow
                .find("[name*='attr_name']")
                .filter(function () {
                    return $(this).val().trim() === "";
                });
            attr_require = emptyAttr.length > 0;
        }

        if (
            $("tr[id*='scavengingItemsTr_']:last").find(
                "[name*='[attr_group_id]']"
            ).length == 0 &&
            item_id
        ) {
            attr_require = false;
        }

        lastTrObj = {
            item_id,
            attr_require,
            scavengingItemsTr_length: lastRow.length,
        };
    }

    // Fetch new row HTML
    let actionUrl =
        scrapItemRowRoute +
        "?count=" +
        rowsLength +
        "&component_item=" +
        JSON.stringify(lastTrObj);

    fetch(actionUrl)
        .then((res) => res.json())
        .then((data) => {
            if (data.status === 200) {
                if (rowsLength) {
                    $("#scavengingItemsTable > tbody > tr:last").after(
                        data.data.html
                    );
                } else {
                    $("#scavengingItemsTable > tbody").html(data.data.html);
                }

                initializeAutocomplete2(".comp_item_code");
                initializeAutocompleteQt(
                    ".comp_item_code_cost_centers",
                    "cost_center_id",
                    "cost_center",
                    "name",
                    "code"
                );
            } else if (data.status === 422) {
                Swal.fire({
                    title: "Error!",
                    text: data.message || "Validation error occurred.",
                    icon: "error",
                });
            } else {
                console.error("Unexpected error while adding row.");
            }
        });
});

/* ================================
   Delete Selected Rows
================================ */
$(document).on("click", "#deleteBtn", (e) => {
    if (!validateStore(e) || !validateSubStore(e)) return false;

    let itemIds = [];
    $("#scavengingItemsTable > tbody .form-check-input:checked").each(
        function () {
            itemIds.push($(this).val());
        }
    );

    if (itemIds.length) {
        itemIds.forEach((item) => {
            $(`#scavengingItemsTr_${item}`).remove();
        });
    } else {
        alert("Please add & select a row to delete.");
    }

    // Reset if no rows left
    if (!$("tr[id*='scavengingItemsTr_']").length) {
        $("#scavengingItemsTable > thead .form-check-input").prop(
            "checked",
            false
        );
    }
});

/* ================================
   Open Attribute Modal
================================ */
$(document).on("click", ".attributeBtn", (e) => {
    if (!validateStore(e) || !validateSubStore(e)) return false;

    let tr = e.target.closest("tr");
    let item_code = tr.querySelector("[name*=item_code]").value;
    let item_id = tr.querySelector("[name*='[item_id]']").value;

    if (!item_code || !item_id) {
        alert("Please select an item first.");
        return;
    }

    let selectedAttr = [];
    let attrElements = tr.querySelectorAll("[name*=attr_name]");
    if (attrElements.length > 0) {
        selectedAttr = Array.from(attrElements)
            .map((el) => el.value)
            .filter((v) => v);
    }

    let rowCount = tr.getAttribute("data-index");
    getItemAttribute(item_id, rowCount, JSON.stringify(selectedAttr), tr);
});

/* ================================
   Fetch & Display Item Details
================================ */
$(document).on(
    "input change focus",
    "#scavengingItemsTable tr input, #scavengingItemsTable tr select",
    (e) => {
        let currentTr = e.target.closest("tr");
        let rowCount = $(currentTr).attr("data-index");
        let itemId = $(currentTr).find("[name*='[item_id]']").val();

        if (!itemId) return;

        let remark = $(currentTr).find("[name*='remark']").val() || "";
        let selectedAttr = $(currentTr)
            .find("[name*='attr_name']")
            .map(function () {
                return $(this).val();
            })
            .get()
            .filter((v) => v);

        let uomId = $(currentTr).find("[name*='[uom_id]']").val() || "";
        let qty = $(currentTr).find("[name*='[qty]']").val() || "";
        let store_id = $("#store_id").val() || "";
        let sub_store_id = $("#sub_store_id").val() || "";

        let actionUrl =
            scrapItemDetailsRoute +
            "?item_id=" +
            itemId +
            "&selectedAttr=" +
            JSON.stringify(selectedAttr) +
            "&remark=" +
            remark +
            "&uom_id=" +
            uomId +
            "&qty=" +
            qty +
            "&store_id=" +
            store_id +
            "&sub_store_id=" +
            sub_store_id;

        fetch(actionUrl)
            .then((res) => res.json())
            .then((data) => {
                if (data.status === 200) {
                    $("#itemDetailDisplay").html(data.data.html);

                    let avlStock =
                        data.data?.inventoryStock.confirmedStocks || 0;
                    $(`input[name="scavenging[${rowCount}][avl_stock]"]`).val(
                        Number(avlStock).toFixed(2)
                    );

                    let pendingPo = data.data?.pendingPo || 0;
                    $(`input[name="scavenging[${rowCount}][pending_po]"]`).val(
                        Number(pendingPo).toFixed(2)
                    );
                }
            });
    }
);

/* ================================
   Submit Attribute Selection
================================ */
$(document).on("click", ".submitAttributeBtn", (e) => {
    let rowCount = $("[id*=scavengingItemsTr_].trselected").attr("data-index");
    $(`[name="scavenging[${rowCount}][qty]"]`).focus();
    $("#attribute").modal("hide");
});

function getSubStores(locationId) {
    const storeId = locationId;
    const $subStoreRow = $(".sub-store-row");
    const $subStoreSelect = $(".sub_store");

    if (!storeId) {
        $subStoreRow.addClass("d-none");
        $subStoreSelect.empty();
        return;
    }

    $.ajax({
        url: "/sub-stores/store-wise",
        method: "GET",
        dataType: "json",
        data: {
            store_id: storeId,
        },
        success: function (data) {
            if (data.status == 200 && data.data.length) {
                let options = '<option value="">Select Sub Store</option>';
                data.data.forEach(function (location) {
                    options += `<option value="${location.id}">${location.name}</option>`;
                });

                $subStoreSelect.empty().html(options).val(null);
                $subStoreRow.removeClass("d-none");
            } else {
                $subStoreSelect.empty().val(null);
                $subStoreRow.addClass("d-none");

                Swal.fire({
                    title: "Error!",
                    text: "No sub store exists for this location.",
                    icon: "error",
                });
            }
        },
        error: function (xhr) {
            Swal.fire({
                title: "Error!",
                text: xhr?.responseJSON?.message || "Something went wrong.",
                icon: "error",
            });
        },
    });
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

/*For comp attr*/
function getItemAttribute(itemId, rowCount, selectedAttr, tr) {
    let isSo = $(tr).find('[name*="so_item_id"]').length ? 1 : 0;
    if (!isSo) {
        isSo = $(tr).find('[name*="so_pi_mapping_item_id"]').length ? 1 : 0;
    }
    if (!isSo) {
        if ($(tr).find('td[id*="itemAttribute_"]').data("disabled")) {
            isSo = 1;
        }
    }

    let actionUrl =
        scrapItemAttrRoute +
        "?item_id=" +
        itemId +
        `&rowCount=${rowCount}&selectedAttr=${selectedAttr}&isSo=${isSo}`;
    fetch(actionUrl).then((response) => {
        return response.json().then((data) => {
            if (data.status == 200) {
                $("#attribute tbody").empty();
                $("#attribute table tbody").append(data.data.html);
                $(tr)
                    .find("td:nth-child(2)")
                    .find("[name*='[attr_name]']")
                    .remove();
                $(tr).find("td:nth-child(2)").append(data.data.hiddenHtml);
                $(tr)
                    .find("td[id*='itemAttribute_']")
                    .attr(
                        "attribute-array",
                        JSON.stringify(data.data.itemAttributeArray)
                    );
                if (data.data.attr) {
                    $("#attribute").modal("show");
                    $(".select2").select2();
                }
                qtyEnabledDisabled();
            }
        });
    });
}

function getDocNumberByBookId(bookId) {
    let document_date = $("[name='document_date']").val();
    let actionUrl =
        getDocNumberByBookIdUrl +
        "?book_id=" +
        bookId +
        "&document_date=" +
        document_date;
    fetch(actionUrl).then((response) => {
        return response.json().then((data) => {
            if (data.status == 200) {
                $("#book_code").val(data.data.book_code);
                if (!data.data.doc.document_number) {
                    $("#document_number").val("");
                }
                $("#document_number").val(data.data.doc.document_number);
                if (data.data.doc.type == "Manually") {
                    $("#document_number").attr("readonly", false);
                } else {
                    $("#document_number").attr("readonly", true);
                }
                const parameters = data.data.parameters;
                setServiceParameters(parameters);
            }
            if (data.status == 404) {
                $("#book_code").val("");
                $("#document_number").val("");
                const docDateInput = $("[name='document_date']");
                docDateInput.attr(
                    "min",
                    "{{ $current_financial_year['start_date'] }}"
                );
                docDateInput.attr(
                    "max",
                    "{{ $current_financial_year['end_date'] }}"
                );
                docDateInput.val(new Date().toISOString().split("T")[0]);
                alert(data.message);
            }
        });
    });
}

/*Set Service Parameter*/
function setServiceParameters(parameters) {
    /*Date Validation*/
    const docDateInput = $("[name='document_date']");
    let isFeature = false;
    let isPast = false;
    if (
        parameters.future_date_allowed &&
        parameters.future_date_allowed.includes("yes")
    ) {
        let futureDate = new Date();
        futureDate.setDate(
            futureDate.getDate() /*+ (parameters.future_date_days || 1)*/
        );
        docDateInput.val(futureDate.toISOString().split("T")[0]);
        docDateInput.attr("min", new Date().toISOString().split("T")[0]);
        isFeature = true;
    } else {
        isFeature = false;
        docDateInput.attr("max", new Date().toISOString().split("T")[0]);
    }
    if (
        parameters.back_date_allowed &&
        parameters.back_date_allowed.includes("yes")
    ) {
        let backDate = new Date();
        backDate.setDate(
            backDate.getDate() /*- (parameters.back_date_days || 1)*/
        );
        docDateInput.val(backDate.toISOString().split("T")[0]);
        // docDateInput.attr("max", "");
        isPast = true;
    } else {
        isPast = false;
        docDateInput.attr("min", new Date().toISOString().split("T")[0]);
    }
    /*Date Validation*/
    if (isFeature && isPast) {
        docDateInput.removeAttr("min");
        docDateInput.removeAttr("max");
    }
    /*Reference from*/
    let reference_type_service = parameters.reference_type_service;

    if (reference_type_service.length) {
        let scrap = "{{ AppHelpersConstantHelper::SCRAP_SERVICE_ALIAS }}";
        if (reference_type_service.includes(scrap)) {
            $("#reference_type").removeClass("d-none");
        } else {
            $("#reference_type").addClass("d-none");
        }
        if (reference_type_service.includes("d")) {
            $("#addNewItemBtn").removeClass("d-none");
        } else {
            $("#addNewItemBtn").addClass("d-none");
        }
    } else {
        Swal.fire({
            title: "Error!",
            text: "Please update first reference from service param.",
            icon: "error",
        });
        setTimeout(() => {
            location.href = scrapIndexRoute;
        }, 1500);
    }
}

// for component item code
function initializeAutocomplete2(selector, type) {
    $(selector)
        .autocomplete({
            minLength: 0,
            source: function (request, response) {
                let selectedAllItemIds = [];
                $(
                    "#scavengingItemsTable tbody [id*='scavengingItemsTr_']"
                ).each(function (index, item) {
                    if (Number($(item).find('[name*="[item_id]"]').val())) {
                        selectedAllItemIds.push(
                            Number($(item).find('[name*="[item_id]"]').val())
                        );
                    }
                });
                $.ajax({
                    url: "/search",
                    method: "GET",
                    dataType: "json",
                    data: {
                        q: request.term,
                        type: "scrap_comp_item",
                        selectedAllItemIds: JSON.stringify(selectedAllItemIds),
                    },
                    success: function (data) {
                        response(
                            $.map(data, function (item) {
                                return {
                                    id: item.id,
                                    label: `${item.item_name} (${item.item_code})`,
                                    code: item.item_code || "",
                                    item_id: item.id,
                                    item_name: item.item_name,
                                    uom_name: item.uom?.name,
                                    uom_id: item.uom_id,
                                    alternate_u_o_ms: item.alternate_u_o_ms,
                                    is_attr: item.item_attributes_count,
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
                let itemCode = ui.item.code;
                let itemName = ui.item.value;
                let itemN = ui.item.item_name;
                let itemId = ui.item.item_id;
                let uomId = ui.item.uom_id;
                let uomName = ui.item.uom_name;
                $input.attr("data-name", itemName);
                $input.attr("data-code", itemCode);
                $input.attr("data-id", itemId);
                $input.closest("tr").find('[name*="[item_id]"]').val(itemId);
                $input.closest("tr").find("[name*=item_code]").val(itemCode);
                $input.closest("tr").find("[name*=item_name]").val(itemN);
                $input.val(itemCode);
                let uomOption = `<option value=${uomId}>${uomName}</option>`;
                if (ui.item?.alternate_u_o_ms) {
                    for (let alterItem of ui.item.alternate_u_o_ms) {
                        uomOption += `<option value="${alterItem.uom_id}" ${
                            alterItem.is_purchasing ? "selected" : ""
                        }>${alterItem.uom?.name}</option>`;
                    }
                }
                $input
                    .closest("tr")
                    .find("[name*=uom_id]")
                    .empty()
                    .append(uomOption);
                $input.closest("tr").find("[name*=attr_group_id]").remove();

                setTimeout(() => {
                    if (ui.item.is_attr) {
                        $input
                            .closest("tr")
                            .find(".attributeBtn")
                            .trigger("click");
                    } else {
                        $input
                            .closest("tr")
                            .find(".attributeBtn")
                            .trigger("click");
                        $input
                            .closest("tr")
                            .find('[name*="[qty]"]')
                            .val("")
                            .focus();
                    }
                }, 100);
                validateItems($input, true);
                return false;
            },
            change: function (event, ui) {
                if (!ui.item) {
                    $(this).val("");
                    $(this).attr("data-name", "");
                    $(this).attr("data-code", "");
                }
            },
        })
        .focus(function () {
            if (this.value === "") {
                $(this).autocomplete("search", "");
            }
        })
        .on("input", function () {
            if ($(this).val().trim() === "") {
                $(this).removeData("selected");
                $(this)
                    .closest("tr")
                    .find("input[name*='component_item_name']")
                    .val("");
                $(this).closest("tr").find("input[name*='item_name']").val("");
                $(this)
                    .closest("tr")
                    .find("td[id*='itemAttribute_']")
                    .html(defautAttrBtn);
                $(this).closest("tr").find("input[name*='item_id']").val("");
                $(this).closest("tr").find("input[name*='item_code']").val("");
                $(this).closest("tr").find("input[name*='attr_name']").remove();
            }
        });
}

function renderData(data) {
    return data ? data : "";
}

function getDynamicParams() {
    return {
        document_date: $("[name='document_date']").val() || "",
        header_book_id: $("#book_id").val() || "",
        series_id: $("#book_id_qt_val").val() || "",
        document_number: $("#document_number").val() || "",
        item_id: $("#item_id_qt_val").val() || "",
        store_id: $("#store_id").val() || "",
        sub_store_id: $("#sub_store_id_po").val() || "",
        item_search: $("#item_name_search").val() || "",
        selected_ps_item_ids: $("[name='ps_item_ids']").val() || "[]",
    };
}

function getProductionSlips() {
    console.log(type);

    const ajaxUrl = getPsRoute.replace(":type", type);
    var columns = [
        {
            data: "id",
            visible: false,
            orderable: true,
            searchable: false,
        },
        {
            data: "select_checkbox",
            name: "select_checkbox",
            orderable: false,
            searchable: false,
        },
        {
            data: "book_name",
            name: "book_name",
            render: renderData,
            orderable: false,
            searchable: false,
        },
        {
            data: "doc_no",
            name: "doc_no",
            render: renderData,
            orderable: false,
            searchable: false,
        },
        {
            data: "doc_date",
            name: "doc_date",
            render: renderData,
            orderable: false,
            searchable: false,
        },
        {
            data: "item_code",
            name: "item_code",
            render: renderData,
            orderable: false,
            searchable: false,
        },
        {
            data: "item_name",
            name: "item_name",
            render: renderData,
            orderable: false,
            searchable: false,
        },
        {
            data: "attributes",
            name: "attributes",
            render: renderData,
            orderable: false,
            searchable: false,
        },
        {
            data: "uom",
            name: "uom",
            render: renderData,
            orderable: false,
            searchable: false,
        },
        {
            data: "qty",
            name: "qty",
            render: renderData,
            orderable: false,
            searchable: false,
        },
        {
            data: "remarks",
            name: "remarks",
            render: renderData,
            orderable: false,
            searchable: false,
        },
    ];
    initializeDataTableCustom("#psModal .ps-order-detail", ajaxUrl, columns);
}
function getSelectedItemIDs() {
    return $("#psModal .ps_item_checkbox:checked")
        .map(function () {
            return Number($(this).val());
        })
        .get();
}

function setHiddenInput(name, value) {
    const safeValue = Array.isArray(value)
        ? JSON.stringify(value)
        : value || "[]";
    $(`[name='${name}']`).val(safeValue);
}

function initializeAutocompleteQt(
    selector,
    requestInputElement,
    typeVal,
    labelKey1,
    labelKey2 = ""
) {
    $(selector).each(function () {
        const $input = $(this);
        $input
            .autocomplete({
                minLength: 0,
                source: function (request, response) {
                    let selectedAllItemIds = [];

                    $(
                        "#scavengingItemsTable tbody [id*='scavengingItemsTr_']"
                    ).each(function () {
                        let val = Number(
                            $(this)
                                .find(`[name*="[${requestInputElement}]"]`)
                                .val()
                        );
                        if (val) selectedAllItemIds.push(val);
                    });

                    $.ajax({
                        url: "/search",
                        method: "GET",
                        dataType: "json",
                        data: {
                            type: typeVal,
                            q: request.term,
                            header_book_id: $("#book_id").val(),
                            store_id: $("#store_id").val() || "",
                            sub_store_id: $("#sub_store_id").val() || "",
                            selectedAllItemIds:
                                JSON.stringify(selectedAllItemIds),
                        },
                        success: function (data) {
                            response(
                                $.map(data, function (item) {
                                    return {
                                        id: item.id,
                                        label: `${item[labelKey1]} ${
                                            labelKey2 && item[labelKey2]
                                                ? "(" + item[labelKey2] + ")"
                                                : ""
                                        }`,
                                        code: item[labelKey1] || "",
                                    };
                                })
                            );
                        },
                        error: function (xhr) {
                            console.error(
                                "Error fetching autocomplete data:",
                                xhr.responseText
                            );
                        },
                    });
                },
                select: function (event, ui) {
                    let $row = $input.closest("tr");
                    $input.val(ui.item.label);
                    $row.find(`input[name*="[${requestInputElement}]"]`).val(
                        ui.item.id
                    );
                    return false;
                },
                change: function (event, ui) {
                    let $row = $input.closest("tr");
                    if (!ui.item) {
                        $input.val("");
                        $row.find(
                            `input[name*="[${requestInputElement}]"]`
                        ).val("");
                    }
                },
            })
            .focus(function () {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
    });
}

initializeAutocomplete2(".comp_item_code");
initializeAutocompleteQt(
    ".comp_item_code_cost_centers",
    "cost_center_id",
    "cost_center",
    "name",
    "code"
);
