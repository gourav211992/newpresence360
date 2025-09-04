/*Tax Detail Display Start*/
$(document).on("click", ".summaryTaxBtn", (e) => {
    getTaxSummary();
});

/*Approve modal*/
$(document).on("click", "#approved-button", (e) => {
    let actionType = "approve";
    $("#approveModal").find("#action_type").val(actionType);
    $("#approveModal #popupTitle").text("Approve Application");
    $("#approveModal").modal("show");
});

/*Reject modal*/
$(document).on("click", "#reject-button", (e) => {
    let actionType = "reject";
    $("#approveModal #popupTitle").text("Reject Application");
    $("#approveModal").find("#action_type").val(actionType);
    $("#approveModal").modal("show");
});

function getTaxSummary() {
    let taxSummary = {};
    $("#itemTable [id*='row_']").each(function (index, row) {
        row = $(row);
        let qty = Number(row.find('[name*="[accepted_qty]"]').val());
        let rate = Number(row.find('[name*="[rate]"]').val());
        let itemDisc = Number(row.find('[name*="[discount_amount]"]').val());
        let itemHeaderDisc = Number(
            row.find('[name*="[discount_amount_header]"]').val()
        );
        let totalItemDisc = itemDisc + itemHeaderDisc;
        let totalItemValue = qty * rate;
        let totalItemValueAfterDisc = totalItemValue - totalItemDisc;
        let processedTaxTypes = {};
        if (totalItemValueAfterDisc) {
            row.find('[name*="[t_type]"]').each(function (taxIndex, TaxRow) {
                // Get tax type, percentage, and value for each tax row
                let tType = $(TaxRow)
                    .closest("td")
                    .find(
                        `[name*="components[${index + 1}][taxes][${
                            taxIndex + 1
                        }][t_type]"]`
                    )
                    .val();
                let tPerc = Number(
                    $(TaxRow)
                        .closest("td")
                        .find(
                            `[name*="components[${index + 1}][taxes][${
                                taxIndex + 1
                            }][t_perc]"]`
                        )
                        .val()
                );
                let tValue = Number(
                    $(TaxRow)
                        .closest("td")
                        .find(
                            `[name*="components[${index + 1}][taxes][${
                                taxIndex + 1
                            }][t_value]"]`
                        )
                        .val()
                );
                let dynamicKey = `${tType}_${tPerc}`;
                if (taxSummary[dynamicKey]) {
                    taxSummary[dynamicKey].totalTaxValue += tValue;
                } else {
                    taxSummary[dynamicKey] = {
                        taxType: tType,
                        taxPerc: tPerc,
                        totalTaxValue: tValue,
                        totalTaxableAmount: 0,
                    };
                }
                processedTaxTypes[dynamicKey] = true;
            });
            for (let key in processedTaxTypes) {
                taxSummary[key].totalTaxableAmount += totalItemValueAfterDisc;
            }
        }
    });
    let taxSummaryHtml = "";
    let rowCount = 1;
    for (let key in taxSummary) {
        let summary = taxSummary[key];
        taxSummaryHtml += `<tr>
        <td>${rowCount}</td>
        <td>${summary.taxType}</td>
        <td>${summary.totalTaxableAmount.toFixed(2)}</td>
        <td>${summary.taxPerc}%</td>
        <td>${summary.totalTaxValue.toFixed(2)}</td>
        </tr>`;
        rowCount++;
    }
    $("#mrn_tax_details").html(taxSummaryHtml);
    $("#mrnTaxDetailModal").modal("show");
}

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
    // if($('.trselected').length) {
    //   $('html, body').scrollTop($('.trselected').offset().top - 200);
    // }
});

/*Check box check and uncheck*/
$(document).on("change", "#itemTable > thead .form-check-input", (e) => {
    if (e.target.checked) {
        $("#itemTable > tbody .form-check-input").each(function () {
            $(this).prop("checked", true);
        });
    } else {
        $("#itemTable > tbody .form-check-input").each(function () {
            $(this).prop("checked", false);
        });
    }
});

$(document).on("change", "#itemTable > tbody .form-check-input", (e) => {
    if (!$("#itemTable > tbody .form-check-input:not(:checked)").length) {
        $("#itemTable > thead .form-check-input").prop("checked", true);
    } else {
        $("#itemTable > thead .form-check-input").prop("checked", false);
    }
});

/*Attribute on change*/
$(document).on("change", '[name*="comp_attribute"]', (e) => {
    let closestTr = e.target.closest("tr");
    let rowCount = e.target
        .closest("tr")
        .querySelector('[name*="row_count"]').value;
    let attrGroupId = e.target.getAttribute("data-attr-group-id");
    $(
        `[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`
    ).val(e.target.value);
    // closestTr = $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).closest('tr');
    // getItemDetail(closestTr);
    qtyEnabledDisabled();
});

// Check Negative Values
let oldValue;
$(document).on("focus", ".checkNegativeVal", function (e) {
    oldValue = e.target.value; // Store the old value when the field gains focus
});

/*Order qty on change*/
$(document).on("change", "[name*='order_qty']", async function (e) {
    const $tr = $(e.target).closest("tr");
    const $qtyInput = $(e.target);
    const orderQty = parseFloat($qtyInput.val()) || 0;

    const $poQtyInput = $tr.find(".mrn_qty");
    const poQty = parseFloat($poQtyInput.val()) || 0;

    const $acceptedQtyInput = $tr.find("[name*='accepted_qty']");
    const $rejectedQtyInput = $tr.find("[name*='rejected_qty']");
    const dataIndex = $tr.attr("data-index");
    const itemId = $tr.find("[name*='item_id']").val();

    $qtyInput.val(orderQty.toFixed(6));
    checkDuplicateObjects($qtyInput);

    if (orderQty <= 0) {
        Swal.fire({
            title: "Error!",
            text: "Inspection Qty. cannot be zero.",
            icon: "error",
        });
        $qtyInput.val(poQty.toFixed(6));
        return;
    }

    const getVal = (selector) => {
        const el = $tr.find(selector);
        return el.length ? el.val() : "";
    };

    const data = {};
    const safeSet = (key, val) => {
        if (val) data[key] = val;
    };

    safeSet("item_id", itemId);
    safeSet("mrn_header_id", getVal("[name*='[mrn_header_id]']"));
    safeSet("mrn_detail_id", getVal("[name*='[mrn_detail_id]']"));
    safeSet("inspection_dtl_id", getVal("[name*='[inspection_dtl_id]']"));
    safeSet("qty", orderQty.toFixed(6));
    safeSet("type", currentProcessType);

    const response = await fetch(
        qtyChangeUrl + "?" + new URLSearchParams(data).toString()
    );
    const result = await response.json();

    const resultQty = parseFloat(result.order_qty) || 0;
    const finalQty = resultQty.toFixed(6);
    $qtyInput.val(finalQty);

    let acceptedQty = resultQty;
    let rejectedQty = resultQty - acceptedQty;

    $acceptedQtyInput.val(acceptedQty.toFixed(6));
    $acceptedQtyInput.trigger("change");
    $rejectedQtyInput.val(rejectedQty.toFixed(6));

    // // Keep single-batch hidden JSON aligned if user edits row Accepted/Rejected
    // const batchCount =
    //     Number($tr.find(".addBatchBtn").data("batch-count")) || 0;
    // if (batchCount <= 1) seedRowFromMrn(dataIndex);

    if (result.status !== 200 && result.message) {
        Swal.fire({ title: "Error!", text: result.message, icon: "error" });
        return false;
    }
});

/*accepted qty on change*/
$(document).on("change", "[name*='accepted_qty']", function (e) {
    edit = true; // Set edit to true when order_qty changes
    const $tr = $(e.target).closest("tr");
    const $acceptedQtyInput = $tr.find("[name*='accepted_qty']");
    const $orderQtyInput = $tr.find("[name*='order_qty']");
    const $rejectedQtyInput = $tr.find("[name*='rejected_qty']");
    const dataIndex = $tr.attr("data-index");
    const itemId = $tr.find("[name*='item_id']").val();

    let acceptedQty = parseFloat($acceptedQtyInput.val()) || 0;
    const orderQty = parseFloat($orderQtyInput.val()) || 0;

    if (acceptedQty > orderQty) {
        Swal.fire({
            title: "Error!",
            text: "Accepted Qty. cannot be greater than Inspection Qty.",
            icon: "error",
        });
        acceptedQty = orderQty;
    }

    let rejectedQty = orderQty - acceptedQty;

    $acceptedQtyInput.val(acceptedQty.toFixed(6));
    $rejectedQtyInput.val(rejectedQty.toFixed(6));
});

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

    // Validate if remark length exceeds 250 characters
    if (textValue.length > 250) {
        Swal.fire({
            title: "Error!",
            text: "Remark cannot be longer than 250 characters.",
            icon: "error",
        });
        return false; // Stop further execution if validation fails
    }

    if (!remarkValue.length) {
        let rowHidden = `<input type="hidden" value="${textValue}" name="components[${rowCount}][remark]" />`;
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

/*Edit mode table calculation filled*/
if ($("#itemTable .mrntableselectexcel tr").length) {
    setTimeout(() => {
        $("[name*='component_item_name[1]']").trigger("focus");
        $("[name*='component_item_name[1]']").trigger("blur");
    }, 100);
}

/*Check filled all basic detail*/
function checkBasicFilledDetail() {
    let filled = false;
    let bookId = $("#book_id").val() || "";
    let documentNumber = $("#document_number").val() || "";
    let documentDate = $("[name='document_date']").val() || "";
    let storeId = $("[name='header_store_id']").val() || "";
    let subStoreId = $("[name='sub_store_id']").val() || "";
    if (bookId && documentNumber && documentDate && storeId && subStoreId) {
        filled = true;
    }
    return filled;
}

/*Check filled vendor detail*/
function checkVendorFilledDetail() {
    let filled = false;
    let vName = $("#vendor_name").val();
    let vCurrency = $("[name='currency_id']").val();
    let vPaymentTerm = $("[name='payment_term_id']").val();
    let shippingId = $("#shipping_id").val();
    let billingId = $("#billing_id").val();
    if (vName && vCurrency && vPaymentTerm && shippingId && billingId) {
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

$("#attribute").on("hidden.bs.modal", function () {
    let rowCount = $("[id*=row_].trselected").attr("data-index");
    // $(`[id*=row_${rowCount}]`).find('.addSectionItemBtn').trigger('click');
    $(`[name="components[${rowCount}][qty]"]`).trigger("focus");
});

/*Vendor change update field*/
$(document).on("blur", "#vendor_name", (e) => {
    if (!e.target.value) {
        $("#vendor_id").val("");
        $("#vendor_code").val("");
        $("#shipping_id").val("");
        $("#billing_id").val("");
        $("[name='currency_id']").val("").trigger("change");
        $("[name='payment_term_id']").val("").trigger("change");
        $(".shipping_detail").text("-");
        $(".billing_detail").text("-");
    }
});

$(document).on("input", ".qty-input", function () {
    const maxAmount = Number($(this).attr("maxAmount")) || 0;
    if (Number(this.value) > maxAmount) {
        Swal.fire({
            title: "Error!",
            text: "Purchase indent quantity is not available.",
            icon: "error",
        });
        this.value = maxAmount;
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
                .find("[name*='[order_qty]']")
                .attr("readonly", Boolean(qtyDisabled));
            if (qtyDisabled) {
                $(item).find("[name*='[order_qty]']").val("");
            }
        } else {
            $(item).find("[name*='[order_qty]']").attr("readonly", false);
        }
    });
}

function checkDuplicateObjects(inputEle) {
    let items = [];
    $("tr[id*='row_']").each(function (index, item) {
        let itemId = $(item).find("input[name*='[item_id]']").val();
        let attrName = $(item).find("input[name*='[attr_name]']").val();
        let uomId = $(item).find("select[name*='[uom_id]']").val();
        if (itemId && attrName && uomId) {
            let attr = [];
            // Collect attributes
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
            // Add item details to the items array
            items.push({
                item_id: itemId,
                uom_id: uomId,
                attributes: attr,
            });
        }
    });
    if (items.length) {
        if (hasDuplicateObjects(items)) {
            Swal.fire({
                title: "Error!",
                text: "Duplicate item!",
                icon: "error",
            });
            $(inputEle).val("");
        }
    }
}

function hasDuplicateObjects(array, inputEle) {
    const seen = new Set();
    for (const obj of array) {
        const objString = JSON.stringify(obj);
        if (seen.has(objString)) {
            return true;
        }
        seen.add(objString);
    }
    return false;
}

// UOM on change bind rate
$(document).on("change", 'select[name*="[uom_id]"]', (e) => {
    let tr = $(e.target).closest("tr");
    getItemDetail(tr);
});

// 1. Attach change event
$(document).on("change", ".header_store_id", function () {
    const selectedStoreId = $(this).val();
    if (selectedStoreId) {
        getSubStores(selectedStoreId);
        getRejectedSubStores(selectedStoreId);
    }
});

// 2. On page load: trigger if already selected
const selectedStoreId = $(".header_store_id").val();
if (selectedStoreId) {
    getSubStores(selectedStoreId);
    getRejectedSubStores(selectedStoreId);
}

// Get SUb Stores
function getSubStores(storeLocationId) {
    const storeId = storeLocationId;
    $.ajax({
        url: "/sub-stores/store-wise",
        method: "GET",
        dataType: "json",
        data: {
            store_id: storeId,
            sub_type: "main",
        },
        success: function (data) {
            console.log("data", data);

            if (data.status == 200 && data.data.length) {
                let options = "";
                data.data.forEach(function (location) {
                    options += `<option value="${location.id}">${location.name}</option>`;
                });
                $(".sub_store").html(options);
            } else {
                // No data found, hide subStore header and cell
                $(".sub_store").empty();
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

// Get Rejected Sub Stores
function getRejectedSubStores(storeLocationId) {
    const storeId = storeLocationId;
    $.ajax({
        url: "/sub-stores/store-wise",
        method: "GET",
        dataType: "json",
        data: {
            store_id: storeId,
            sub_type: "rejected",
        },
        success: function (data) {
            console.log("data", data);

            if (data.status == 200 && data.data.length) {
                let options = '<option value="">select</option>';
                data.data.forEach(function (location) {
                    options += `<option value="${location.id}">${location.name}</option>`;
                });
                $(".rejected_sub_store").html(options);
            } else {
                // No data found, hide Rejected Sub Store header and cell
                $(".rejected_sub_store").empty();
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

// Auto scroll when row added
function focusAndScrollToLastRowInput(
    inputSelector = ".comp_item_code",
    tableSelector = "#itemTable"
) {
    let $lastRow = $(`${tableSelector} > tbody > tr`).last();
    let $input = $lastRow.find(inputSelector);

    // if ($input.length) {
    //     $input.focus().autocomplete('search', '');
    //     $input[0].scrollIntoView({
    //         behavior: 'smooth',
    //         block: 'center',
    //         inline: 'nearest'
    //     });
    // }
}
