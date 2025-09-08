setTimeout(() => {
    $("#book_id").trigger("change");
}, 0);

$(document).on("load", function () {
    if (feather) {
        feather.replace({
            width: 14,
            height: 14,
        });
    }
});

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
$(document).on("change", ".comp_attribute", (e) => {
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
        rowHidden = `<input type="hidden" value="${textValue}" name="components[${rowCount}][remark]" />`;
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
    setAttributesUIHelper(rowCount, "#scavengingItemsTable tbody");
    if ($(`[name="components[${rowCount}][qty]"]`).is("[readonly]")) {
        $(`[name="components[${rowCount}][qty]"]`).trigger("focus");
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

function validateItems(inputEle, itemChange = false) {
    let items = [];
    $("tr[id*='scavengingItemsTr_']").each(function (index, item) {
        let itemId = $(item).find("input[name*='[item_id]']").val();
        let uomId = $(item).find("select[name*='[uom_id]']").val();
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

function enableDisableFormOnValidation(type, e = null) {
    if (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
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
   Reusable Delete Handler
================================ */
function handleDelete(
    tableSelector,
    rowPrefix,
    deletedInputId,
    psInputId = "#ps_item_ids",
    psPullInputId = "#ps_pull_item_ids"
) {
    let itemIds = [];
    let editItemIds = [];

    $(`${tableSelector} > tbody .form-check-input:checked`).each(function () {
        const $this = $(this);
        const dataId = $this.attr("data-id");
        if (dataId) {
            editItemIds.push(dataId);
        }
        itemIds.push($this.val());
    });

    if (!itemIds.length) {
        alert("Please add & select a row to delete.");
        return;
    }

    if (editItemIds.length) {
        $("#deleteComponentModal")
            .find("#deleteConfirm")
            .attr("data-ids", JSON.stringify(editItemIds))
            .attr("data-item-ids", JSON.stringify(itemIds))
            .attr("data-table", tableSelector)
            .attr("data-row-prefix", rowPrefix)
            .attr("data-deleted-input", deletedInputId)
            .attr("data-ps-pull-input", psPullInputId)
            .attr("data-ps-input", psInputId);

        $("#deleteComponentModal").modal("show");
    } else {
        // only pulled items → delete immediately
        itemIds.forEach((item) => {
            $(`${rowPrefix}${item}`).remove();
        });

        // cleanup check-all
        if (!$(`${tableSelector} tbody tr`).length) {
            $(`${tableSelector} > thead .form-check-input`).prop(
                "checked",
                false
            );
            $("#reference_from").removeClass("d-none");
        }
    }
}

/* ================================
   Attach Delete Events
================================ */
const deleteConfig = {
    "#deleteBtn": [
        "#scavengingItemsTable",
        "#scavengingItemsTr_",
        "#deleted_scrap_item_ids",
    ],
    "#deleteBtnPullItem": [
        "#productionSlipsTable",
        "#row_",
        "#deleted_ps_item_ids",
    ],
};

Object.entries(deleteConfig).forEach(([btn, args]) => {
    $(document).on("click", btn, (e) => {
        if (!validateStore(e) || !validateSubStore(e)) return false;
        handleDelete(...args);
    });
});

/* ================================
   Confirm Delete
================================ */
$(document).on("click", "#deleteConfirm", (e) => {
    let ids = JSON.parse(e.target.getAttribute("data-ids") || "[]");
    let allItemIds = JSON.parse(e.target.getAttribute("data-item-ids") || "[]");
    let tableSelector = e.target.getAttribute("data-table");
    let rowPrefix = e.target.getAttribute("data-row-prefix");
    let deletedInputId = e.target.getAttribute("data-deleted-input");
    let psInputId = e.target.getAttribute("data-ps-input");
    let psPullInputId = e.target.getAttribute("data-ps-pull-input");

    logger();

    if (deletedInputId && ids.length) {
        let existing = $(`${deletedInputId}`).val();
        let parsed = existing ? JSON.parse(existing) : [];
        parsed = parsed.concat(ids);
        $(deletedInputId).val(JSON.stringify([...new Set(parsed)]));
    }

    if (psInputId && ids.length) {
        let psExisting = $(`${psInputId}`).val();
        let psParsed = psExisting ? JSON.parse(psExisting) : [];
        psParsed = psParsed.filter((x) => !ids.includes(String(x)));
        $(`${psInputId}`).val(JSON.stringify(psParsed));
        if (psPullInputId) {
            $(`${psPullInputId}`).val(JSON.stringify(psParsed));
        }
    }

    allItemIds.forEach((id) => {
        $(`${rowPrefix}${id}`).remove();
        $(`${tableSelector} tbody .form-check-input[data-id='${id}']`)
            .closest("tr")
            .remove();
    });

    logger();
    // reset modal attrs
    $("#deleteComponentModal")
        .find("#deleteConfirm")
        .attr("data-ids", "")
        .attr("data-item-ids", "")
        .attr("data-table", "")
        .attr("data-row-prefix", "")
        .attr("data-deleted-input", "")
        .attr("data-ps-input", "");

    $("#deleteComponentModal").modal("hide");

    // cleanup check-all
    if (!$(`${tableSelector} tbody tr`).length) {
        $(`${tableSelector} thead .form-check-input`).prop("checked", false);
        $("#reference_from").removeClass("d-none");
    }
});

/* ================================
   Open Attribute Modal
================================ */
$(document).on("click", ".attributeBtn", (e) => {
    if (!validateStore(e) || !validateSubStore(e)) return false;

    const tr = e.target.closest("tr");
    const item_code = tr?.querySelector("[name*=item_code]")?.value || "";
    const item_id = tr?.querySelector("[name*='[item_id]']")?.value || "";

    if (!item_code || !item_id) {
        Swal.fire({
            title: "Error!",
            text: "Please select an item first.",
            icon: "error",
        });
        return;
    }

    const attrElements = tr.querySelectorAll("[name*='attr_name']");
    const selectedAttr = Array.from(attrElements)
        .map((el) => el.value)
        .filter((v) => v);

    const rowCount = tr.getAttribute("data-index");
    const mode = tr.getAttribute("data-mode") || "edit";
    const requestHeader =
        tr.getAttribute("data-request-header") || "components";

    getItemAttribute(
        item_id,
        rowCount,
        JSON.stringify(selectedAttr),
        tr,
        mode,
        requestHeader
    );
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
                    $(`input[name="components[${rowCount}][avl_stock]"]`).val(
                        Number(avlStock).toFixed(2)
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
    validateItems(e.target, false);
    qtyEnabledDisabled();
    setSelectedAttribute(rowCount);
    $(`[name="components[${rowCount}][qty]"]`).focus();
    $("#attribute").modal("hide");
});

function getSubStores(storeId, subStoreId = "") {
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
            sub_type: "Scrap",
        },
        success: function (data) {
            if (
                data.status === 200 &&
                Array.isArray(data.data) &&
                data.data.length
            ) {
                enableDisableFormOnValidation("enable");
                let options = '<option value="">Select Sub Store</option>';
                data.data.forEach(function (subStore) {
                    options += `<option value="${subStore.id}">${subStore.name}</option>`;
                });

                $subStoreSelect
                    .empty()
                    .html(options)
                    .val(subStoreId || "")
                    .trigger("change");

                $subStoreRow.removeClass("d-none");
            } else {
                $subStoreSelect.empty().val(null);
                $subStoreRow.addClass("d-none");

                Swal.fire({
                    title: "Error!",
                    text: "No sub store exists for this location.",
                    icon: "error",
                });
                enableDisableFormOnValidation("disable");
                return;
                // setTimeout(() => {
                //     window.location.href(scrapIndexRoute);
                // }, 500);
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

function recalcRate(rowIndex) {
    const $qtyInput = $(`[name="components[${rowIndex}][qty]"]`);
    const $costInput = $(`[name="components[${rowIndex}][total_cost]"]`);
    const $rateInput = $(`[name="components[${rowIndex}][rate]"]`);
    const attr_name = $(`[name="components[${rowIndex}][attr_name]"]`);

    const qty = +$qtyInput.val() || 0;
    const cost = +$costInput.val() || 0;

    if (cost < 0) {
        Swal.fire("Error!", "Cost cannot be negative.", "error");
        $costInput.val("").addClass("is-invalid");
        if (!$costInput.next(".text-danger").length) {
            $costInput.after(
                '<span class="text-danger">Cost cannot be negative.</span>'
            );
        }
        $rateInput.val("");
        return;
    }

    $costInput.removeClass("is-invalid").next(".text-danger").remove();

    if (qty > 0 && cost > 0) {
        const rate = cost / qty;
        $rateInput.val(rate.toFixed(6));
    } else {
        $rateInput.val("");
    }
}

$(document).on(
    "input change focus",
    "#scavengingItemsTable [name^='components'][name$='[qty]']",
    function (e) {
        const rowIndex = $(this).closest("tr").data("index");
        recalcRate(rowIndex);
        validateItems(e.target, false);
    }
);

$(document).on(
    "input change focus",
    "#scavengingItemsTable [name^='components'][name$='[total_cost]']",
    function (e) {
        const rowIndex = $(this).closest("tr").data("index");
        recalcRate(rowIndex);
        validateItems(e.target, false);
    }
);

/*For comp attr*/
function getItemAttribute(
    itemId,
    rowCount,
    selectedAttr,
    tr,
    mode = "edit",
    requestHeader = "components"
) {
    let actionUrl =
        scrapItemAttrRoute +
        "?item_id=" +
        itemId +
        `&rowCount=${rowCount}` +
        `&selectedAttr=${selectedAttr}` +
        `&requestHeader=${requestHeader}` +
        `&mode=${mode}`;

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
                    // if readonly mode
                    if (mode === "view") {
                        $("#attribute .cancelAttributeBtn").hide();
                        $("#attribute .submitAttributeBtn").hide();
                    } else {
                        $("#attribute .cancelAttributeBtn").show();
                        $("#attribute .submitAttributeBtn").show();
                    }

                    $("#attribute").modal("show");
                    $(".select2").select2();
                }
                qtyEnabledDisabled();
            }
        });
    });
}

function getDocNumberByBookId(bookId, docNumber) {
    let document_date = $("[name='document_date']").val();
    let actionUrl =
        getDocNumberByBookIdUrl +
        "?book_id=" +
        bookId +
        "&document_date=" +
        document_date +
        "&document_number=" +
        docNumber;
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
    let reference_from_service = parameters.reference_from_service;
    if ($("#sub_store_id").val() || "") {
        if (reference_from_service.length) {
            if (
                reference_from_service.includes(PRODUCTION_SLIP_SERVICE_ALIAS)
            ) {
                $("#reference_type_div").removeClass("d-none");
            } else {
                $("#reference_type_div").addClass("d-none");
            }

            if (reference_from_service.includes("d")) {
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
                $(this).closest("tr").find("td[id*='itemAttribute_']")
                    .html(` <button id="attribute_button_1" type="button"
                        class="btn p-25 btn-sm btn-outline-secondary"
                        style="font-size: 10px">Attributes</button>
                    <input type="hidden" name="attribute_value_1" />`);
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

function getPslipItems() {
    const ajaxUrl = getPsRoute.replace(":type", type);

    const columns = [
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
            orderable: true,
            searchable: false,
            title: "Book Name",
        },
        {
            data: "doc_no",
            name: "doc_no",
            render: renderData,
            orderable: true,
            searchable: false,
            title: "Doc No",
        },
        {
            data: "doc_date",
            name: "doc_date",
            render: renderData,
            orderable: true,
            searchable: false,
            title: "Doc Date",
        },
        {
            data: "item_code",
            name: "item_code",
            render: renderData,
            orderable: true,
            searchable: false,
            title: "Item Code",
        },
        {
            data: "item_name",
            name: "item_name",
            render: renderData,
            orderable: true,
            searchable: false,
            title: "Item Name",
        },
        {
            data: "attributes",
            name: "attributes",
            render: renderData,
            orderable: true,
            searchable: false,
            title: "Attributes",
        },
        {
            data: "uom",
            name: "uom",
            render: renderData,
            orderable: true,
            searchable: false,
            title: "UOM",
        },
        {
            data: "qty",
            name: "qty",
            render: renderData,
            orderable: true,
            searchable: false,
            title: "Qty",
        },
        {
            data: "remarks",
            name: "remarks",
            render: renderData,
            orderable: true,
            searchable: false,
            title: "Remarks",
        },
    ];

    const selector = "#psModal .ps-order-detail";
    $(selector).css("width", "100%");

    psTable = initializeDataTableCustom(selector, ajaxUrl, columns);
}

$(document).on("keyup", "#item_name_search", function () {
    if (psTable) psTable.ajax.reload(null, false);
});

function initializeDataTableCustom(
    selector,
    ajaxUrl,
    columns,
    ajaxRequestType = "GET"
) {
    const $table = $(selector);
    if (!$table.length) return null;

    if ($table.find("thead").length === 0) {
        const header = `<thead><tr>${columns
            .map((c) => `<th>${c.title ?? ""}</th>`)
            .join("")}</tr></thead>`;
        $table.prepend(header);
        if ($table.find("tbody").length === 0) $table.append("<tbody></tbody>");
    }

    const dt = $table.DataTable({
        // crucial for modal reuse
        destroy: true,
        retrieve: true,

        processing: true,
        serverSide: true,
        deferRender: true,

        scrollY: "300px",
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        responsive: false, // avoid conflicts with scrollX

        // keep orderable only on first hidden index
        columnDefs: [
            { targets: 0, width: "40px", orderable: true },
            { targets: 1, width: "50px" },
            { targets: 2, width: "90px" },
            { targets: 3, width: "120px" },
            { targets: "_all", orderable: false },
        ],

        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"],
        ],
        order: [[0, "desc"]],
        searching: false,

        ajax: {
            url: ajaxUrl,
            type: ajaxRequestType,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: function (d) {
                const dynamicParams =
                    typeof getDynamicParams === "function"
                        ? getDynamicParams()
                        : {};
                Object.assign(d, dynamicParams);
            },
        },

        columns,

        dom:
            "<'row'<'col-sm-12'tr>>" +
            "<'row align-items-center'" +
            "<'col-md-4 text-start'l>" +
            "<'col-md-4 text-center'i>" +
            "<'col-md-4 text-end'p>" +
            ">",

        initComplete: function () {
            // when DataTable finishes, adjust once
            this.api().columns.adjust();
        },

        drawCallback: function () {
            if (window.feather && typeof feather.replace === "function") {
                feather.replace();
            }
        },

        rowCallback: function (row, data) {
            if (data && typeof data.DT_RowIndex !== "undefined") {
                $(row).attr("id", "row_" + data.DT_RowIndex);
                $(row).attr("data-index", data.DT_RowIndex);
            }
        },

        language: {
            paginate: { previous: " ", next: " " },
        },

        search: { caseInsensitive: true },
    });

    return dt;
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

function updateHiddenInput(id, values, unique = false) {
    let current = [];
    try {
        current = JSON.parse($(`#${id}`).val() || "[]");
    } catch {}
    if (!Array.isArray(values)) values = [values];

    let merged = current.concat(values);
    if (unique) merged = [...new Set(merged)];
    $(`#${id}`).val(JSON.stringify(merged));
}

function clearPsInputs() {
    updateHiddenInput("ps_item_ids", []);
    updateHiddenInput("item_ids", []);
    updateHiddenInput("reference_type", "");
}

function reInitAttributes(container) {
    $(container)
        .find("tr")
        .each((index, tr) => {
            setAttributesUIHelper(index, container);
        });
}
