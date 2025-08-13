// Determine if current URL is an edit page
const isEditPage = window.location.pathname.includes("/edit");

/*Tax Detail Display Start*/
$(document).on("click", ".summaryTaxBtn", (e) => {
    getTaxSummary();
});
let selectedCostCenterId = null;
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

/*Deviation modal*/
$(document).on("click", "#deviation-button", (e) => {
    let actionType = "deviation-closed";
    $("#deviateModal").find("#action_type").val(actionType);
    $("#deviateModal #popupTitle").text("Putaway Deviation");
    $("#deviateModal").modal("show");
});

function getTaxSummary() {
    let taxSummary = {};
    $("#itemTable [id*='row_']").each(function (index, row) {
        row = $(row);
        let qty = Number(row.find('[name*="[order_qty]"]').val());
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
    setSelectedAttribute(rowCount);
});

// Check Negative Values
let oldValue;
$(document).on("focus", ".checkNegativeVal", function (e) {
    oldValue = e.target.value; // Store the old value when the field gains focus
});

$(document).on("change", "[name*='order_qty']", async function (e) {
    const $tr = $(e.target).closest("tr");
    const $qtyInput = $(e.target);
    const orderQty = parseFloat($qtyInput.val()) || 0;

    const $poQtyInput = $tr.find(".po_qty");
    const poQty = parseFloat($poQtyInput.val()) || 0;

    const $acceptedQtyInput = $tr.find("[name*='accepted_qty']");
    const $rejectedQtyInput = $tr.find("[name*='rejected_qty']");
    const $itemCost = $tr.find("[name*='rate']");
    const $itemValue = $tr.find("[name*='basic_value']");
    const inspectionRequired = $(".inspection_required").val() === "yes";
    const dataIndex = $tr.attr("data-index");
    const itemId = $tr.find("[name*='item_id']").val();

    $qtyInput.val(orderQty.toFixed(2));
    checkDuplicateObjects($qtyInput);

    if (orderQty <= 0) {
        Swal.fire({
            title: "Error!",
            text: "Receipt Qty. cannot be zero.",
            icon: "error",
        });
        $qtyInput.val(poQty.toFixed(2));
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
    safeSet("purchase_order_id", getVal("[name*='[purchase_order_id]']"));
    safeSet("po_detail_id", getVal("[name*='[po_detail_id]']"));
    safeSet("job_order_id", getVal("[name*='[job_order_id]']"));
    safeSet("jo_detail_id", getVal("[name*='[jo_detail_id]']"));
    safeSet("sale_order_id", getVal("[name*='[sale_order_id]']"));
    safeSet("so_detail_id", getVal("[name*='[so_detail_id]']"));
    safeSet("ge_detail_id", getVal("[name*='[gate_entry_detail_id]']"));
    safeSet("asn_detail_id", getVal("[name*='[vendor_asn_dtl_id]']"));
    safeSet("mrn_detail_id", getVal("[name*='[mrn_detail_id]']"));
    safeSet("qty", orderQty.toFixed(2));
    safeSet("type", currentProcessType);

    try {
        const response = await fetch(
            qtyChangeUrl + "?" + new URLSearchParams(data).toString()
        );
        const result = await response.json();

        const resultQty = parseFloat(result.order_qty) || 0;
        const finalQty = resultQty.toFixed(2);
        $qtyInput.val(finalQty);

        let acceptedQty = inspectionRequired ? 0.0 : resultQty;
        let rejectedQty = inspectionRequired ? 0.0 : resultQty - acceptedQty;

        $acceptedQtyInput.val(acceptedQty.toFixed(2));
        $acceptedQtyInput.trigger("change");
        $rejectedQtyInput.val(rejectedQty.toFixed(2));

        if (Number($itemCost.val())) {
            let totalValue =
                parseFloat(acceptedQty) * parseFloat($itemCost.val());
            $itemValue.val(totalValue.toFixed(2));
        } else {
            $itemValue.val("");
        }

        // if (acceptedQty > 0) {
        //     generatePackets(dataIndex, itemId, acceptedQty.toFixed(2));
        // }

        if (result.status !== 200 && result.message) {
            Swal.fire({ title: "Error!", text: result.message, icon: "error" });
            return false;
        }
    } catch (err) {
        console.error(err);
        Swal.fire({
            title: "Error!",
            text: "Quantity validation failed.",
            icon: "error",
        });
    }
});

/*qty on change*/
$(document).on("change", "[name*='accepted_qty']", function (e) {
    edit = true; // Set edit to true when order_qty changes
    const $tr = $(e.target).closest("tr");
    const $acceptedQtyInput = $tr.find("[name*='accepted_qty']");
    const $orderQtyInput = $tr.find("[name*='order_qty']");
    const $rejectedQtyInput = $tr.find("[name*='rejected_qty']");
    const $itemCost = $tr.find("[name*='rate']");
    const $itemValue = $tr.find("[name*='basic_value']");
    const inspectionRequired = $(".inspection_required").val() === "yes";
    const dataIndex = $tr.attr("data-index");
    const itemId = $tr.find("[name*='item_id']").val();

    let acceptedQty = parseFloat($acceptedQtyInput.val()) || 0;
    const orderQty = parseFloat($orderQtyInput.val()) || 0;

    if (acceptedQty > orderQty) {
        Swal.fire({
            title: "Error!",
            text: "Accepted Qty. cannot be greater than Receipt Qty.",
            icon: "error",
        });
        acceptedQty = orderQty;
    }

    let rejectedQty = inspectionRequired ? 0 : orderQty - acceptedQty;

    $acceptedQtyInput.val(acceptedQty.toFixed(2));
    $rejectedQtyInput.val(rejectedQty.toFixed(2));

    if (Number($itemCost.val())) {
        const value = orderQty * parseFloat($itemCost.val());
        $itemValue.val(value.toFixed(2));
    } else {
        $itemValue.val("");
    }

    // if (acceptedQty > 0) {
    //     generatePackets(dataIndex, itemId, acceptedQty.toFixed(2));
    // }
});

/*rate on change*/
$(document).on("change", "[name*='rate']", (e) => {
    let tr = e.target.closest("tr");
    let rate = e.target;
    let dataIndex = $(e.target).closest("tr").attr("data-index");
    let itemId = $(e.target).closest("tr").find("[name*=item_id]").val();
    let orderQuantity = $(e.target).closest("tr").find("[name*='order_qty']");
    let acceptedQuantity = $(e.target)
        .closest("tr")
        .find("[name*='accepted_qty']");
    let orderRate = $(e.target).closest("tr").find("[name*='rate']");
    let itemValue = $(e.target).closest("tr").find("[name*='basic_value']");
    if (Number(orderQuantity.val())) {
        let itemRate = parseFloat(rate.value);
        // if(itemRate < 1) {
        //     itemRate = oldValue;
        //     orderRate.val(oldValue);
        //     Swal.fire({
        //         title: 'Error!',
        //         text: 'Item Rate must be positive integer.',
        //         icon: 'error',
        //     });
        //     return false;
        // } else{
        // }
        let totalItemValue = itemRate * parseFloat(orderQuantity.val());
        totalItemValue = parseFloat(totalItemValue);
        orderRate.val(itemRate.toFixed(2));
        itemValue.val(totalItemValue.toFixed(2));
    } else {
        itemValue.val("");
    }
});

/*Calculate total amount of discount rows for the item*/
function totalItemDiscountAmount() {
    let total = 0;
    $("#eachRowDiscountTable .display_discount_row").each(function (
        index,
        item
    ) {
        total =
            total + Number($(item).find('[name="itemDiscountAmount"]').val());
    });
    $("#disItemFooter #total").text(total.toFixed(2));
}

/*Each row addDiscountBtn*/
$(document).on("click", ".addDiscountBtn", (e) => {
    let rowCount = e.target.closest("button").getAttribute("data-row-count");
    let tr = "";
    let totalAmnt = 0;
    $(`[id="row_${rowCount}"]`)
        .find("[name*='[dis_amount]']")
        .each(function (index, item) {
            let key = index + 1;
            let id = $(item).closest("tr").find(`[name*='[${key}][id]']`).val();
            let name = $(item)
                .closest("tr")
                .find(`[name*='[${key}][dis_name]']`)
                .val();
            let perc = $(item)
                .closest("tr")
                .find(`[name*='[${key}][dis_perc]']`)
                .val();
            let amnt = Number($(item).val()).toFixed(2);
            totalAmnt += Number(amnt);
            let tbl_row_count = index + 1;
            tr += `
        <tr class="display_discount_row">
            <td>${tbl_row_count}</td>
            <td>${name}
                <input type="hidden" value="${id}" name="disc_item[${tbl_row_count}][item_d_id]">
                <input type="hidden" value="${name}" name="disc_item[${tbl_row_count}][item_d_name]" />
            </td>
            <td class="text-end">${perc}
                <input type="hidden" value="${perc}" name="disc_item[${tbl_row_count}][item_d_perc]" />
            </td>
            <td class="text-end">${amnt}
            <input type="hidden" value="${amnt}" name="disc_item[${tbl_row_count}][item_d_amnt]" />
            </td>
            <td>
                <a data-row-count="${rowCount}" data-id="${id}" href="javascript:;" class="text-danger deleteItemDiscountRow">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                </a>
            </td>
        </tr>`;
        });
    $(".display_discount_row").remove();
    $("#eachRowDiscountTable #disItemFooter").before(tr);
    $("#disItemFooter #total").text(totalAmnt);
    $("#disItemFooter #row_count").val(rowCount);
    $("#itemRowDiscountModal").modal("show");
    initializeAutocompleteTED(
        "new_item_dis_name_select",
        "new_item_discount_id",
        "new_item_dis_name",
        "sales_module_discount",
        "new_item_dis_perc"
    );
});

// Set Each Row Item Calculation
function setTableCalculation(edit = null) {
    if (edit === null) {
        edit = window.location.pathname.includes("/edit");
    }

    const reference_type = $(".reference_type").val();
    let totalItemValue = 0;
    let totalItemDiscount = 0;
    let totalItemCost = 0;
    let totalHeaderDiscount = 0;
    let totalAfterBothDisc = 0;
    let totalTax = 0;
    let totalAfterTax = 0;
    let totalHeaderExp = 0;
    let grandTotal = 0;
    let poItemIds = [];
    let poIds = [];
    let itemQtys = {}; // <-- make this an object

    $("#itemTable [id*='row_']").each(function (index, item) {
        let rowCount = Number($(item).attr("data-index"));
        if (reference_type == "po") {
            poItemId = $(item).find("[name*='[po_detail_id]']").val();
            poId = $(item).find("[name*='[purchase_order_id]']").val();
        } else if (reference_type == "jo") {
            poItemId = $(item).find("[name*='[jo_detail_id]']").val();
            poId = $(item).find("[name*='[job_order_id]']").val();
        } else {
            poItemId = "";
            poId = "";
        }
        let qty = $(item).find("[name*='[order_qty]']").val() || 0;
        if (poItemId) {
            poItemIds.push(poItemId);
            itemQtys[poItemId] = qty; // assign qty keyed by poItemId
        }

        if (poId) {
            poIds.push(poId);
        }
        let rate = $(item).find("[name*='[rate]']").val() || 0;
        let itemValue = Number(qty) * Number(rate) || 0;
        totalItemValue += itemValue;
        $(item).find("[name*='[basic_value]']").val(itemValue.toFixed(2));

        /*Bind Item Discount*/
        let itemDiscount = 0;
        if ($(item).find("[name*='[dis_perc]']").length && itemValue) {
            $(item)
                .find("[name*='[dis_perc]']")
                .each(function (index, eachItem) {
                    let hiddenPerc =
                        Number(
                            $(
                                `[name="components[${rowCount}][discounts][${
                                    index + 1
                                }][hidden_dis_perc]"]`
                            ).val()
                        ) || 0;
                    let discPerc = hiddenPerc || Number($(eachItem).val());
                    let eachDiscAmount = 0;
                    if (discPerc) {
                        eachDiscAmount = (itemValue * discPerc) / 100;
                    } else {
                        eachDiscAmount =
                            Number(
                                $(
                                    `[name="components[${rowCount}][discounts][${
                                        index + 1
                                    }][dis_amount]"]`
                                ).val()
                            ) || 0;
                    }
                    itemDiscount += eachDiscAmount;
                    $(
                        `[name="components[${rowCount}][discounts][${
                            index + 1
                        }][dis_amount]"]`
                    ).val(eachDiscAmount.toFixed(2));
                });
            $(item)
                .find("[name*='[discount_amount]']")
                .val(itemDiscount.toFixed(2));
        } else if (!itemValue) {
            $(item).find("[name*='[discount_amount]']").val("0.00");
        }
        totalItemDiscount += itemDiscount;

        let itemCost = itemValue - itemDiscount;
        totalItemCost += itemCost;
        $(item).find("[name*='[item_total_cost]']").val(itemCost.toFixed(2));
        /*Bind Item Discount*/
    });

    /*Bind table footer*/
    $("#totalItemValue")
        .attr("amount", totalItemValue)
        .text(totalItemValue.toFixed(2));
    $("#totalItemDiscount")
        .attr("amount", totalItemDiscount)
        .text(totalItemDiscount.toFixed(2));
    $("#TotalEachRowAmount")
        .attr("amount", totalItemCost)
        .text(totalItemCost.toFixed(2));
    /*Bind table footer*/

    $("#f_sub_total")
        .attr("amount", totalItemValue.toFixed(2))
        .text(totalItemValue.toFixed(2))
        .attr("style", totalItemValue < 0 ? "color: red !important;" : "");
    $("#f_total_discount")
        .attr("amount", totalItemDiscount.toFixed(2))
        .text(totalItemDiscount.toFixed(2))
        .attr("style", totalItemDiscount < 0 ? "color: red !important;" : "");

    /*Bind summary header Discount*/
    let totalAmountAfterItemDis = totalItemCost;
    let disHeaderAmnt = 0;
    if (
        $(".display_summary_discount_row").find("[name*='[d_perc]']").length &&
        totalAmountAfterItemDis
    ) {
        $(".display_summary_discount_row")
            .find("[name*='[d_perc]']")
            .each(function (index, eachItem) {
                let eachDiscTypePrice = 0;
                let hiddenPerc =
                    Number(
                        $(
                            `[name="disc_summary[${index + 1}][hidden_d_perc]"]`
                        ).val()
                    ) || 0;
                let itemDiscPerc = hiddenPerc || Number($(eachItem).val());
                if (itemDiscPerc) {
                    eachDiscTypePrice =
                        (totalAmountAfterItemDis * itemDiscPerc) / 100;
                } else {
                    eachDiscTypePrice =
                        Number(
                            $(
                                `[name="disc_summary[${index + 1}][d_amnt]"]`
                            ).val()
                        ) || 0;
                }
                $(`[name="disc_summary[${index + 1}][d_amnt]"]`).closest(
                    "td"
                ).html(`${eachDiscTypePrice.toFixed(2)}
                <input type="hidden" value="${eachDiscTypePrice.toFixed(
                    2
                )}" name="disc_summary[${index + 1}][d_amnt]">
            `);
                // $(`[name="disc_summary[${index + 1}][d_amnt]"]`).val(eachDiscTypePrice.toFixed(2));
                disHeaderAmnt += eachDiscTypePrice;
            });
    } else {
        let eachDiscTypePrice = 0;
        $(".display_summary_discount_row")
            .find("[name*='[d_perc]']")
            .each(function (index) {
                $(`[name="disc_summary[${index + 1}][d_amnt]"]`).closest(
                    "td"
                ).html(`${eachDiscTypePrice.toFixed(2)}
                <input type="hidden" value="${eachDiscTypePrice.toFixed(
                    2
                )}" name="disc_summary[${index + 1}][d_amnt]">
            `);
                // $(`[name="disc_summary[${index + 1}][d_amnt]"]`).val(eachDiscTypePrice.toFixed(2));
            });
        disHeaderAmnt += eachDiscTypePrice;
    }
    $("#disSummaryFooter #total")
        .attr("amount", disHeaderAmnt.toFixed(2))
        .text(disHeaderAmnt.toFixed(2))
        .attr("style", disHeaderAmnt < 0 ? "color: red !important;" : "");
    $("#f_header_discount")
        .attr("amount", disHeaderAmnt.toFixed(2))
        .text(disHeaderAmnt.toFixed(2))
        .attr("style", disHeaderAmnt < 0 ? "color: red !important;" : "");
    /*Bind summary header Discount*/

    /*Bind header discount item level*/
    $("#itemTable [id*='row_']").each(function (index, item2) {
        let rowCount2 = Number($(item2).attr("data-index"));
        let qty2 = $(item2).find("[name*='[order_qty]']").val() || 0;
        let rate2 = $(item2).find("[name*='[rate]']").val() || 0;
        let itemValue2 = Number(qty2) * Number(rate2) || 0;
        let itemDisc2 =
            Number($(item2).find("[name*='[discount_amount]']").val()) || 0;
        let itemHeaderDisc =
            ((itemValue2 - itemDisc2) / (totalItemValue - totalItemDiscount)) *
            disHeaderAmnt;
        if (itemHeaderDisc) {
            $(item2)
                .find("[name*='[discount_amount_header]']")
                .val(itemHeaderDisc.toFixed(2));
        } else {
            $(item2).find("[name*='[discount_amount_header]']").val("0.00");
        }
        totalHeaderDiscount += itemHeaderDisc;
    });
    /*Bind header discount item level*/

    /*Bind Tax*/
    const taxPromises = [];
    let isTax = $("#tax_required").val().trim().toLowerCase() === "yes";
    $("#itemTable [id*='row_']").each(function (index, item3) {
        let rowCount3 = Number($(item3).attr("data-index"));
        let qty3 = $(item3).find("[name*='[order_qty]']").val() || 0;
        let rate3 = $(item3).find("[name*='[rate]']").val() || 0;
        let itemValue3 = Number(qty3) * Number(rate3) || 0;
        let itemDisc3 =
            Number($(item3).find("[name*='[discount_amount]']").val()) || 0;
        let itemHeaderDisc =
            Number($(item3).find("[name*='[discount_amount_header]']").val()) ||
            0;
        let itemId = $(item3).find('[name*="[item_id]"]').val();

        let price = itemValue3 - itemDisc3 - itemHeaderDisc;
        if (price > 0 && itemId) {
            if (isTax) {
                let transactionType = "collection";
                let partyCountryId = $("#hidden_country_id").val();
                let partyStateId = $("#hidden_state_id").val();
                let locationId = $("[name='header_store_id']").val();
                let document_date = $("[name='document_date']").val();
                // Construct the query parameters
                let queryParams = new URLSearchParams({
                    price: price,
                    item_id: itemId,
                    transaction_type: transactionType,
                    party_country_id: partyCountryId,
                    party_state_id: partyStateId,
                    rowCount: rowCount3,
                    location_id: locationId,
                    document_date: document_date,
                }).toString();
                let urlWithParams = `${actionUrlTax}?${queryParams}`;
                let promise = fetch(urlWithParams)
                    .then((response) => response.json())
                    .then((data) => {
                        $(item3).find("[name*='t_d_id']").remove();
                        $(item3).find("[name*='t_code']").remove();
                        $(item3).find("[name*='applicability_type']").remove();
                        $(item3).find("[name*='t_type']").remove();
                        $(item3).find("[name*='t_perc']").remove();
                        $(item3).find("[name*='t_value']").remove();
                        if (data.status === 200) {
                            $(item3)
                                .find("[name*='item_total_cost']")
                                .after(data?.data?.html);
                        } else {
                            console.warn(
                                "Data status not 200 or HTML not found in response"
                            );
                        }
                    })
                    .catch((error) => {
                        console.error("Fetch error:", error);
                    });
                taxPromises.push(promise);
            }
        } else {
            $(item3).find("[name*='t_d_id']").remove();
            $(item3).find("[name*='t_code']").remove();
            $(item3).find("[name*='applicability_type']").remove();
            $(item3).find("[name*='t_type']").remove();
            $(item3).find("[name*='t_perc']").remove();
            $(item3).find("[name*='t_value']").remove();
        }
    });
    /*Bind Tax*/

    Promise.all(taxPromises).then(() => {
        $("#itemTable [id*='row_']").each(function (index, item4) {
            let rowCount4 = Number($(item4).attr("data-index"));
            let qty4 = $(item4).find("[name*='[order_qty]']").val() || 0;
            let rate4 = $(item4).find("[name*='[rate]']").val() || 0;
            let itemValue4 = Number(qty4) * Number(rate4) || 0;
            let itemDisc4 =
                Number($(item4).find("[name*='[discount_amount]']").val()) || 0;
            let itemHeaderDisc =
                Number(
                    $(item4).find("[name*='[discount_amount_header]']").val()
                ) || 0;

            let totalAmountAfterItemDis = itemValue4 - itemDisc4;
            if (isTax) {
                if (
                    $(item4).find("[name*='[t_perc]']").length &&
                    totalAmountAfterItemDis
                ) {
                    let taxAmountRow = 0.0;
                    $(item4)
                        .find("[name*='[t_perc]']")
                        .each(function (index, eachItem) {
                            let eachTaxTypePrice = 0;
                            let taxPercTax = Number($(eachItem).val());
                            if (taxPercTax) {
                                eachTaxTypePrice =
                                    ((totalAmountAfterItemDis -
                                        Number(itemHeaderDisc)) *
                                        taxPercTax) /
                                    100;
                                $(item4)
                                    .find(
                                        `[name="components[${rowCount4}][taxes][${
                                            index + 1
                                        }][t_value]"]`
                                    )
                                    .val(eachTaxTypePrice.toFixed(2));
                            } else {
                                $(item4)
                                    .find(
                                        `[name="components[${rowCount4}][taxes][${
                                            index + 1
                                        }][t_value]"]`
                                    )
                                    .val(eachTaxTypePrice.toFixed(2));
                            }
                            taxAmountRow += eachTaxTypePrice;
                        });
                    totalTax += taxAmountRow;
                }
            }
        });
        totalAfterBothDisc =
            Number(totalItemValue || 0) -
            Number(totalItemDiscount || 0) -
            Number(totalHeaderDiscount || 0);
        totalAfterTax =
            Number(totalItemValue || 0) -
            Number(totalItemDiscount || 0) -
            Number(totalHeaderDiscount || 0) +
            Number(totalTax || 0);

        $("#f_taxable_value")
            .attr("amount", totalAfterBothDisc.toFixed(2))
            .text(totalAfterBothDisc.toFixed(2))
            .attr(
                "style",
                totalAfterBothDisc < 0 ? "color: red !important;" : ""
            );
        $("#f_tax")
            .attr("amount", totalTax.toFixed(2))
            .text(totalTax.toFixed(2))
            .attr("style", totalTax < 0 ? "color: red !important;" : "");
        $("#f_total_after_tax")
            .attr("amount", totalAfterTax.toFixed(2))
            .text(totalAfterTax.toFixed(2))
            .attr("style", totalAfterTax < 0 ? "color: red !important;" : "");

        let rows = $(".display_summary_exp_row").find("[name*='[e_perc]']");

        let fetchPromises = [];
        let expAmounts = [];
        let totalRows = rows.length;

        if (totalRows && totalAfterTax) {
            rows.each(function (index, eachItem) {
                let hiddenPerc =
                    Number(
                        $(
                            `[name="exp_summary[${index + 1}][hidden_e_perc]"]`
                        ).val()
                    ) || 0;
                let expDiscPerc = hiddenPerc || Number($(eachItem).val());
                let tedId =
                    Number(
                        $(`[name="exp_summary[${index + 1}][e_id]"]`).val()
                    ) || 0;

                const computeAndUpdate = (baseAmount, idx) => {
                    let eachExpTypePrice = (baseAmount * expDiscPerc) / 100;
                    expAmounts[idx] = eachExpTypePrice; // store individually for later accumulation

                    $(`[name="exp_summary[${idx + 1}][e_amnt]"]`).closest("td")
                        .html(`
                        ${eachExpTypePrice.toFixed(2)}
                        <input type="hidden" value="${eachExpTypePrice.toFixed(
                            2
                        )}" name="exp_summary[${idx + 1}][e_amnt]">
                        `);
                };

                if (
                    tedId &&
                    poItemIds.length &&
                    poIds.length &&
                    (reference_type == "po" || reference_type == "jo")
                ) {
                    const p = fetch(
                        "/material-receipts/get-selected-item-amount",
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": $(
                                    'meta[name="csrf-token"]'
                                ).attr("content"),
                            },
                            body: JSON.stringify({
                                po_item_ids: poItemIds,
                                po_ids: poIds,
                                ted_id: tedId,
                                edit: edit,
                                itemQtys: itemQtys,
                                reference_type: reference_type,
                            }),
                        }
                    )
                        .then((response) => response.json())
                        .then((data) => {
                            let baseAmount =
                                data.status === 200
                                    ? data.data.poItemValue
                                    : totalAfterTax;

                            computeAndUpdate(baseAmount, index);
                        })
                        .catch(() => {
                            computeAndUpdate(totalAfterTax, index);
                        });

                    fetchPromises.push(p);
                } else {
                    computeAndUpdate(totalAfterTax, index);
                }
            });

            Promise.all(fetchPromises).then(() => {
                let totalHeaderExp = expAmounts.reduce(
                    (sum, val) => sum + (val || 0),
                    0
                );

                $("#expSummaryFooter #total")
                    .attr("amount", totalHeaderExp.toFixed(2))
                    .text(totalHeaderExp.toFixed(2))
                    .attr(
                        "style",
                        totalHeaderExp < 0 ? "color: red !important;" : ""
                    );
                $("#f_exp")
                    .text(totalHeaderExp.toFixed(2))
                    .css("color", totalHeaderExp < 0 ? "red" : "");

                grandTotal = totalAfterTax + totalHeaderExp;

                $("#f_total_after_exp")
                    .attr("amount", grandTotal.toFixed(2))
                    .text(grandTotal.toFixed(2))
                    .attr(
                        "style",
                        grandTotal < 0 ? "color: red !important;" : ""
                    );

                /*Bind header exp item level*/
                let total_net_total = 0;
                $("#itemTable [id*='row_']").each(function (index, item5) {
                    let rowCount5 = Number($(item5).attr("data-index"));
                    let qty5 =
                        $(item5).find("[name*='[order_qty]']").val() || 0;
                    let rate5 = $(item5).find("[name*='[rate]']").val() || 0;
                    let itemValue5 = Number(qty5) * Number(rate5) || 0;
                    let itemDisc5 =
                        Number(
                            $(item5).find("[name*='[discount_amount]']").val()
                        ) || 0;
                    let itemHeaderDisc5 =
                        Number(
                            $(item5)
                                .find("[name*='[discount_amount_header]']")
                                .val()
                        ) || 0;
                    let itemTax5 = 0;
                    if ($(item5).find("[name*='[t_value]']").length) {
                        $(item5)
                            .find("[name*='[t_value]']")
                            .each(function (indexing, iteming) {
                                itemTax5 += Number($(iteming).val()) || 0;
                            });
                    }
                    total_net_total +=
                        itemValue5 - itemDisc5 - itemHeaderDisc5 + itemTax5;
                });

                $("#itemTable [id*='row_']").each(function (index, item6) {
                    let each_net_value = 0;
                    let exp_header_amnt_item = 0;
                    let rowCount6 = Number($(item6).attr("data-index"));
                    let qty6 =
                        $(item6).find("[name*='[order_qty]']").val() || 0;
                    let rate6 = $(item6).find("[name*='[rate]']").val() || 0;
                    let itemValue6 = Number(qty6) * Number(rate6) || 0;
                    let itemDisc6 =
                        Number(
                            $(item6).find("[name*='[discount_amount]']").val()
                        ) || 0;
                    let itemHeaderDisc6 =
                        Number(
                            $(item6)
                                .find("[name*='[discount_amount_header]']")
                                .val()
                        ) || 0;
                    let itemTax6 = 0;
                    if ($(item6).find("[name*='[t_value]']").length) {
                        $(item6)
                            .find("[name*='[t_value]']")
                            .each(function (indexing, iteming) {
                                itemTax6 += Number($(iteming).val()) || 0;
                            });
                    }
                    if (totalHeaderExp) {
                        each_net_value =
                            itemValue6 - itemDisc6 - itemHeaderDisc6 + itemTax6;
                        exp_header_amnt_item =
                            (each_net_value / total_net_total) * totalHeaderExp;
                        $(item6)
                            .find("[name*='[exp_amount_header]']")
                            .val(exp_header_amnt_item.toFixed(2));
                    } else {
                        $(item6)
                            .find("[name*='[exp_amount_header]']")
                            .val(exp_header_amnt_item.toFixed(2));
                    }
                });
            });
        } else {
            let totalHeaderExp = 0;

            rows.each(function (index, eachItem) {
                let eachExpTypePrice = 0;
                $(`[name="exp_summary[${index + 1}][e_amnt]"]`).closest(
                    "td"
                ).html(`
                    ${eachExpTypePrice.toFixed(2)}
                    <input type="hidden" value="${eachExpTypePrice.toFixed(
                        2
                    )}" name="exp_summary[${index + 1}][e_amnt]">
                `);
                totalHeaderExp += eachExpTypePrice;
            });

            $("#expSummaryFooter #total")
                .attr("amount", totalHeaderExp.toFixed(2))
                .text(totalHeaderExp.toFixed(2))
                .attr(
                    "style",
                    totalHeaderExp < 0 ? "color: red !important;" : ""
                );
            $("#f_exp")
                .text(totalHeaderExp.toFixed(2))
                .css("color", totalHeaderExp < 0 ? "red" : "");

            grandTotal = totalAfterTax + totalHeaderExp;

            $("#f_total_after_exp")
                .attr("amount", grandTotal.toFixed(2))
                .text(grandTotal.toFixed(2))
                .attr("style", grandTotal < 0 ? "color: red !important;" : "");

            /*Bind header exp item level*/
            let total_net_total = 0;
            $("#itemTable [id*='row_']").each(function (index, item5) {
                let rowCount5 = Number($(item5).attr("data-index"));
                let qty5 = $(item5).find("[name*='[order_qty]']").val() || 0;
                let rate5 = $(item5).find("[name*='[rate]']").val() || 0;
                let itemValue5 = Number(qty5) * Number(rate5) || 0;
                let itemDisc5 =
                    Number(
                        $(item5).find("[name*='[discount_amount]']").val()
                    ) || 0;
                let itemHeaderDisc5 =
                    Number(
                        $(item5)
                            .find("[name*='[discount_amount_header]']")
                            .val()
                    ) || 0;
                let itemTax5 = 0;
                if ($(item5).find("[name*='[t_value]']").length) {
                    $(item5)
                        .find("[name*='[t_value]']")
                        .each(function (indexing, iteming) {
                            itemTax5 += Number($(iteming).val()) || 0;
                        });
                }
                total_net_total +=
                    itemValue5 - itemDisc5 - itemHeaderDisc5 + itemTax5;
            });

            $("#itemTable [id*='row_']").each(function (index, item6) {
                let each_net_value = 0;
                let exp_header_amnt_item = 0;
                let rowCount6 = Number($(item6).attr("data-index"));
                let qty6 = $(item6).find("[name*='[order_qty]']").val() || 0;
                let rate6 = $(item6).find("[name*='[rate]']").val() || 0;
                let itemValue6 = Number(qty6) * Number(rate6) || 0;
                let itemDisc6 =
                    Number(
                        $(item6).find("[name*='[discount_amount]']").val()
                    ) || 0;
                let itemHeaderDisc6 =
                    Number(
                        $(item6)
                            .find("[name*='[discount_amount_header]']")
                            .val()
                    ) || 0;
                let itemTax6 = 0;
                if ($(item6).find("[name*='[t_value]']").length) {
                    $(item6)
                        .find("[name*='[t_value]']")
                        .each(function (indexing, iteming) {
                            itemTax6 += Number($(iteming).val()) || 0;
                        });
                }
                if (totalHeaderExp) {
                    each_net_value =
                        itemValue6 - itemDisc6 - itemHeaderDisc6 + itemTax6;
                    exp_header_amnt_item =
                        (each_net_value / total_net_total) * totalHeaderExp;
                    $(item6)
                        .find("[name*='[exp_amount_header]']")
                        .val(exp_header_amnt_item.toFixed(2));
                } else {
                    $(item6)
                        .find("[name*='[exp_amount_header]']")
                        .val(exp_header_amnt_item.toFixed(2));
                }
            });
        }
    });
}

/*itemDiscountSubmit*/
$(document).on("click", ".itemDiscountSubmit", (e) => {
    $("#itemRowDiscountModal").modal("hide");
});

/*Delete deleteItemDiscountRow*/
$(document).on("click", ".deleteItemDiscountRow", (e) => {
    let rowCount = e.target.closest("a").getAttribute("data-row-count") || 0;
    let index = e.target.closest("a").getAttribute("data-index") || 0;
    let id = e.target.closest("a").getAttribute("data-id") || 0;
    let total = 0.0;
    if (!id) {
        e.target.closest("tr").remove();
        $("#eachRowDiscountTable .display_discount_row").each(function (
            index,
            item
        ) {
            let disAmount = $(item).find("[name='itemDiscountAmount']").val();
            total += Number(disAmount);
        });
        $("#disItemFooter #total").text(total.toFixed(2));
    }
});

// Total Store Quantity
function storeQtyTotal() {
    let storeQty = 0;
    $(".add-more-store-locations [name*='erp_store_qty']").each(function (
        index,
        item
    ) {
        storeQty = storeQty + Number($(item).val());
    });
    $("#storeLocationFooter #store-qty").attr("qty", storeQty);
    $("#storeLocationFooter #store-qty").text(Number(storeQty));
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
        setTableCalculation();
    }, 100);
}

function summaryDisTotal() {
    let total = 0.0;
    $(".display_summary_discount_row [name*='[d_amnt]']").each(function (
        index,
        item
    ) {
        total += Number($(item).val()) || 0;
    });
    $("#disSummaryFooter #total")
        .attr("amount", total.toFixed(2))
        .text(total.toFixed(2));
}

/*Open summary discount modal*/
$(document).on("click", ".summaryDisBtn", (e) => {
    e.stopPropagation();
    if (!Number($(`[name*="[basic_value]"]`).val())) {
        Swal.fire({
            title: "Error!",
            text: "Please first enter qty & rate in table.",
            icon: "error",
        });
        return false;
    }
    $("#summaryDiscountModal").modal("show");
    initializeAutocompleteTED(
        "new_dis_name_select",
        "new_discount_id",
        "new_dis_name",
        "sales_module_discount",
        "new_dis_perc"
    );
});

/*summaryDiscountSubmit*/
$(document).on("click", ".summaryDiscountSubmit", (e) => {
    $("#summaryDiscountModal").modal("hide");
    return false;
});

/*delete summary discount row*/
$(document).on("click", ".deleteSummaryDiscountRow", (e) => {
    let trId = $(e.target).closest("tr").find('[name*="[d_id]"]').val();
    if (!trId) {
        $(e.target).closest("tr").remove();
        summaryDisTotal();
        setTableCalculation();
        if (!Number($("#disSummaryFooter #total").attr("amount"))) {
            $("#f_header_discount_hidden").addClass("d-none");
        } else {
            $("#f_header_discount_hidden").removeClass("d-none");
        }
    }
});

/*Open summary expen modal*/
$(document).on("click", ".summaryExpBtn", (e) => {
    e.stopPropagation();
    if (!Number($(`[name*="[basic_value]"]`).val())) {
        Swal.fire({
            title: "Error!",
            text: "Please first enter qty & rate in table.",
            icon: "error",
        });
        return false;
    }
    $("#summaryExpenModal").modal("show");
    initializeAutocompleteTED(
        "new_exp_name_select",
        "new_exp_id",
        "new_exp_name",
        "sales_module_expense",
        "new_exp_perc"
    );
});

/*delete summary exp row*/
$(document).on("click", ".deleteExpRow", (e) => {
    let trId = $(e.target).closest("tr").find('[name*="[e_id]"]').val();
    if (!trId) {
        $(e.target).closest("tr").remove();
        summaryExpTotal();
    }
});

// summaryExpSubmit
$(document).on("click", ".summaryExpSubmit", (e) => {
    $("#summaryExpenModal").modal("hide");
    return false;
    // setTableCalculation();
});

function summaryExpTotal() {
    let total = 0.0;
    $(".display_summary_exp_row [name*='e_amnt']").each(function (index, item) {
        total = total + Number($(item).val());
    });
    $("#expSummaryFooter #total").attr("amount", total);
    $("#expSummaryFooter #total").text(total.toFixed(2));
}

$(document).on("input change", "#itemTable input", (e) => {
    setTableCalculation();
});

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
    let billingId = $("#billing_id").val();
    if (vName && vCurrency && vPaymentTerm && billingId) {
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
    let qty = $(`[name="components[${rowCount}][qty]"]`).val() || "";
    $(`[name="components[${rowCount}][qty]"]`).val(qty).focus();
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

/*Add New Summary Discount*/
$(document).on("click", "#add_new_item_dis", (e) => {
    e.preventDefault();
    let rowCount = $("#disItemFooter #row_count").val();
    const new_item_dis_name = $("#new_item_dis_name").val() || "";
    const new_item_dis_id = $("#new_item_discount_id").val() || "";
    const new_item_dis_perc = (
        Number($("#new_item_dis_perc").val()) || 0
    ).toFixed(2);
    const new_item_dis_value = (
        Number($("#new_item_dis_value").val()) || 0
    ).toFixed(2);
    let item_dis = 0;
    $(`.display_discount_row`)
        .find('[name*="[item_d_amnt]"]')
        .each(function (index, item) {
            item_dis += parseFloat($(item).val() || 0);
        });
    let _total_head_dis_all = item_dis + parseFloat(new_item_dis_value);
    let totalCost =
        parseFloat($(`[name*='components[${rowCount}][basic_value]']`).val()) ||
        0;
    if (_total_head_dis_all > totalCost) {
        Swal.fire({
            title: "Error!",
            text: "You can not give total discount more then total cost.",
            icon: "error",
        });
        return false;
    }

    if (!new_item_dis_name || (!new_item_dis_perc && !new_item_dis_value))
        return;
    const tbl_row_count =
        $("#eachRowDiscountTable .display_discount_row").length + 1;
    const tr = `
    <tr class="display_discount_row">
        <td>${tbl_row_count}</td>
        <td>${new_item_dis_name}
            <input type="hidden" value="${new_item_dis_id}" name="disc_item[${tbl_row_count}][ted_d_id]">
            <input type="hidden" value="" name="disc_item[${tbl_row_count}][item_d_id]">
            <input type="hidden" value="${new_item_dis_name}" name="disc_item[${tbl_row_count}][item_d_name]" />
        </td>
        <td class="text-end">${new_item_dis_perc}
            <input type="hidden" value="${new_item_dis_perc}" name="disc_item[${tbl_row_count}][item_d_perc]" />
        </td>
        <td class="text-end">${new_item_dis_value}
        <input type="hidden" value="${new_item_dis_value}" name="disc_item[${tbl_row_count}][item_d_amnt]" />
        </td>
        <td>
            <a data-row-count="${rowCount}" data-id="" href="javascript:;" class="text-danger deleteItemDiscountRow">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            </a>
        </td>
    </tr>`;
    if (!$(".display_discount_row").length) {
        $("#eachRowDiscountTable #disItemFooter").before(tr);
    } else {
        $(".display_discount_row:last").after(tr);
    }
    $("#new_item_dis_name").val("");
    $("#new_item_discount_id").val("");
    $("#new_item_dis_perc").val("").prop("readonly", false);
    $("#new_item_dis_value").val("").prop("readonly", false);
    let total_head_dis = 0;
    $("[name*='[item_d_amnt]']").each(function (index, item) {
        total_head_dis += Number($(item).val());
    });
    $("#disItemFooter #total").text(total_head_dis.toFixed(2));
    $(`[id*='row_${rowCount}']`).find("[name*='[discounts]'").remove();

    let hiddenDis = "";
    let totalAmnt = 0;
    $(".display_discount_row").each(function (index, item) {
        let id = $(item).find('[name*="[item_d_id]"]').val();
        let ted_id = $(item).find('[name*="[ted_d_id]"]').val();
        let name = $(item).find('[name*="[item_d_name]"]').val();
        let perc = $(item).find('[name*="[item_d_perc]"]').val();
        let amnt = $(item).find('[name*="[item_d_amnt]"]').val();
        totalAmnt += Number(amnt);
        hiddenDis += `<input type="hidden" value="${id}" name="components[${rowCount}][discounts][${
            index + 1
        }][id]">
        <input type="hidden" value="${ted_id}" name="components[${rowCount}][discounts][${
            index + 1
        }][ted_id]">
        <input type="hidden" value="${name}" name="components[${rowCount}][discounts][${
            index + 1
        }][dis_name]">
        <input type="hidden" value="${perc}" name="components[${rowCount}][discounts][${
            index + 1
        }][dis_perc]">
        <input type="hidden" value="${amnt}" name="components[${rowCount}][discounts][${
            index + 1
        }][dis_amount]">`;
    });
    $(`[name*="components[${rowCount}][discount_amount]"]`).val(totalAmnt);
    $(`[name*="components[${rowCount}][discount_amount]"]`).after(hiddenDis);
    setTableCalculation();
});

/*Header discount perc change*/
$(document).on("keyup", "#new_item_dis_perc", (e) => {
    e.preventDefault();
    let rowCount = $("#disItemFooter #row_count").val();
    let input = $(e.target);
    input.prop("readonly", false);
    let value = parseFloat(input.val());
    let percAmount = 0;
    let totalCost = 0;
    if (isNaN(value)) {
        input.val("");
        value = 0;
    }
    if (value < 0) {
        value = 0;
        input.val(value);
    } else if (value > 100) {
        let _total_perc = 0;
        $(`.display_discount_row`)
            .find('[name*="[item_d_perc]"]')
            .each(function (index, item) {
                _total_perc += parseFloat($(item).val() || 0);
            });
        value = 100 - _total_perc;
        input.val(value);
        setTimeout(() => {
            Swal.fire({
                title: "Error!",
                text: "You cannot add more than 100%.",
                icon: "error",
            });
        }, 0);
    }

    totalCost =
        parseFloat($(`[name*='components[${rowCount}][basic_value]']`).val()) ||
        0;

    percAmount = parseFloat((totalCost * value) / 100);
    $("#new_item_dis_value")
        .prop("readonly", Boolean(percAmount))
        .val(percAmount ? percAmount.toFixed(2) : "");
    return false;
});

/*Header discount value change*/
$(document).on("keyup", "#new_item_dis_value", (e) => {
    e.preventDefault();
    let input = $(e.target);
    input.prop("readonly", false);
    let value = parseFloat(input.val());
    $("#new_item_dis_perc").prop("readonly", Boolean(value)).val("");
    return false;
});

/*Add New Summary Discount*/
$(document).on("click", "#add_new_head_dis", (e) => {
    e.preventDefault();
    const new_dis_name = $("#new_dis_name").val() || "";
    const new_dis_id = $("#new_discount_id").val() || "";
    const new_dis_perc = (Number($("#new_dis_perc").val()) || 0).toFixed(2);
    const new_dis_value = (Number($("#new_dis_value").val()) || 0).toFixed(2);

    let _total_head_dis = 0;
    $("[name*='[d_amnt]']").each(function (index, item) {
        _total_head_dis += Number($(item).val());
    });

    let totalCost = parseFloat($("#TotalEachRowAmount").attr("amount")) || 0;
    let _total_head_dis_all = _total_head_dis + Number(new_dis_value);
    if (_total_head_dis_all > totalCost) {
        Swal.fire({
            title: "Error!",
            text: "You can not give total discount more then total cost.",
            icon: "error",
        });
        return false;
    }

    if (!new_dis_name || (!new_dis_perc && !new_dis_value)) return;
    const tbl_row_count =
        $("#summaryDiscountTable .display_summary_discount_row").length + 1;
    const tr = `
    <tr class="display_summary_discount_row">
        <td>${tbl_row_count}</td>
        <td>${new_dis_name}
            <input type="hidden" value="" name="disc_summary[${tbl_row_count}][d_id]">
            <input type="hidden" value="${new_dis_id}" name="disc_summary[${tbl_row_count}][ted_d_id]">
            <input type="hidden" value="${new_dis_name}" name="disc_summary[${tbl_row_count}][d_name]" />
        </td>
        <td class="text-end">${new_dis_perc}
            <input type="hidden" value="${new_dis_perc}" name="disc_summary[${tbl_row_count}][d_perc]" />
        </td>
        <td class="text-end">${new_dis_value}
        <input type="hidden" value="${new_dis_value}" name="disc_summary[${tbl_row_count}][d_amnt]" />
        </td>
        <td>
            <a href="javascript:;" class="text-danger deleteSummaryDiscountRow">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            </a>
        </td>
    </tr>`;
    if (!$(".display_summary_discount_row").length) {
        $("#summaryDiscountTable #disSummaryFooter").before(tr);
    } else {
        $(".display_summary_discount_row:last").after(tr);
    }
    $("#new_dis_name").val("");
    $("#new_discount_id").val("");
    $("#new_dis_perc").val("").prop("readonly", false);
    $("#new_dis_value").val("").prop("readonly", false);
    let total_head_dis = 0;
    $("[name*='[d_amnt]']").each(function (index, item) {
        total_head_dis += Number($(item).val());
    });
    if (total_head_dis) {
        $("#f_header_discount_hidden").removeClass("d-none");
    } else {
        $("#f_header_discount_hidden").addClass("d-none");
    }
    $("#disSummaryFooter #total").text(total_head_dis.toFixed(2));
    setTableCalculation();
});

/*Header discount perc change*/
$(document).on("keyup", "#new_dis_perc", (e) => {
    e.preventDefault();
    let input = $(e.target);
    input.prop("readonly", false);
    let value = parseFloat(input.val());
    let percAmount = 0;
    let totalCost = 0;
    if (isNaN(value)) {
        input.val("");
        value = 0;
    }
    if (value < 0) {
        value = 0;
        input.val(value);
    } else if (value > 100) {
        let _total_perc = 0;
        $("[name*='[d_perc]']").each(function (index, item) {
            _total_perc += Number($(item).val());
        });
        value = 100 - _total_perc;
        input.val(value);
        Swal.fire({
            title: "Error!",
            text: "You cannot add more than 100%.",
            icon: "error",
        });
    }
    totalCost = parseFloat($("#TotalEachRowAmount").attr("amount")) || 0;
    percAmount = parseFloat((totalCost * value) / 100);
    $("#new_dis_value")
        .prop("readonly", Boolean(percAmount))
        .val(percAmount ? percAmount.toFixed(2) : "");
    return false;
});

/*Header discount value change*/
$(document).on("keyup", "#new_dis_value", (e) => {
    e.preventDefault();
    let input = $(e.target);
    input.prop("readonly", false);
    let value = parseFloat(input.val());
    $("#new_dis_perc").prop("readonly", Boolean(value)).val("");
    return false;
});

/*Add New Summary Discount*/
$(document).on("click", "#add_new_head_exp", (e) => {
    e.preventDefault();
    console.log("dfsdfdfsfsfdfsdfsdfsfds");
    const new_exp_name = $("#new_exp_name").val() || "";
    const new_exp_id = $("#new_exp_id").val() || "";
    const new_exp_perc = (Number($("#new_exp_perc").val()) || 0).toFixed(2);
    const new_exp_value = (Number($("#new_exp_value").val()) || 0).toFixed(2);

    let _total_head_exp = 0;
    $("[name*='[e_amnt]']").each(function (index, item) {
        _total_head_exp += Number($(item).val());
    });

    let totalCost = parseFloat($("#f_total_after_tax").attr("amount")) || 0;
    console.log("Total Cost: ", totalCost);
    let _total_head_exp_all = _total_head_exp + Number(new_exp_value);
    if (_total_head_exp_all > totalCost) {
        Swal.fire({
            title: "Error!",
            text: "You can not give total exp more then after tax value.",
            icon: "error",
        });
        return false;
    }

    if (!new_exp_name || (!new_exp_perc && !new_exp_value)) return;
    const tbl_row_count =
        $("#summaryExpTable .display_summary_exp_row").length + 1;
    const tr = `
    <tr class="display_summary_exp_row">
        <td>${tbl_row_count}</td>
        <td>${new_exp_name}
            <input type="hidden" value="" name="exp_summary[${tbl_row_count}][e_id]">
            <input type="hidden" value="${new_exp_id}" name="exp_summary[${tbl_row_count}][ted_e_id]">
            <input type="hidden" value="${new_exp_name}" name="exp_summary[${tbl_row_count}][e_name]" />
        </td>
        <td class="text-end">${new_exp_perc}
            <input type="hidden" value="${new_exp_perc}" name="exp_summary[${tbl_row_count}][e_perc]" />
        </td>
        <td class="text-end">${new_exp_value}
            <input type="hidden" value="${new_exp_value}" name="exp_summary[${tbl_row_count}][e_amnt]" />
        </td>
        <td>
            <a href="javascript:;" class="text-danger deleteExpRow">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            </a>
        </td>
    </tr>`;
    if (!$(".display_summary_exp_row").length) {
        $("#summaryExpTable #expSummaryFooter").before(tr);
    } else {
        $(".display_summary_exp_row:last").after(tr);
    }
    $("#new_exp_name").val("");
    $("#new_exp_id").val("");
    $("#new_exp_perc").val("").prop("readonly", false);
    $("#new_exp_value").val("").prop("readonly", false);
    let total_head_exp = 0;
    $("[name*='[e_amnt]']").each(function (index, item) {
        total_head_exp += Number($(item).val());
    });
    $("#expSummaryFooter #total").text(total_head_exp.toFixed(2));
    setTableCalculation();
});

/*Header discount perc change*/
$(document).on("keyup", "#new_exp_perc", (e) => {
    e.preventDefault();
    let input = $(e.target);
    input.prop("readonly", false);
    let value = parseFloat(input.val());
    let percAmount = 0;
    let totalCost = 0;
    if (isNaN(value)) {
        input.val("");
        value = 0;
    }
    if (value < 0) {
        value = 0;
        input.val(value);
    } else if (value > 100) {
        let _total_perc = 0;
        $("[name*='[e_perc]']").each(function (index, item) {
            _total_perc += Number($(item).val());
        });
        value = 100 - _total_perc;
        input.val(value);
        Swal.fire({
            title: "Error!",
            text: "You cannot add more than 100%.",
            icon: "error",
        });
    }
    // totalCost = parseFloat($("#TotalEachRowAmount").attr('amount')) || 0;
    totalCost = parseFloat($("#f_total_after_tax").attr("amount")) || 0;
    percAmount = parseFloat((totalCost * value) / 100);
    $("#new_exp_value")
        .prop("readonly", Boolean(percAmount))
        .val(percAmount ? percAmount.toFixed(2) : "");
    return false;
});

/*Header discount value change*/
$(document).on("keyup", "#new_exp_value", (e) => {
    e.preventDefault();
    let input = $(e.target);
    input.prop("readonly", false);
    let value = parseFloat(input.val());
    $("#new_exp_perc").prop("readonly", Boolean(value)).val("");
    return false;
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
qtyEnabledDisabled();

setTimeout(() => {
    if ($("tr[id*='row_']").length) {
        setTableCalculation();
    }
}, 0);

/*Get Stock Detail*/
function ledgerStock(currentTr, itemId, selectedAttr, itemStoreData) {
    if (itemId) {
        let actionUrl =
            "/material-receipts/get-stock-detail?item_id=" +
            itemId +
            "&selectedAttr=" +
            JSON.stringify(selectedAttr) +
            "&itemStoreData=" +
            JSON.stringify(itemStoreData);
        fetch(actionUrl).then((response) => {
            return response.json().then((data) => {
                if (data.status == 200) {
                    $("#itemDetailDisplay").html(data.data.html);
                }
            });
        });
    }
}

function checkDuplicateObjects(inputEle) {
    let items = [];
    $("tr[id*='row_']").each(function (index, item) {
        let itemId = $(item).find("input[name*='[item_id]']").val();
        let poId = $(item).find("input[name*='[purchase_order_id]']").val();
        let soId = $(item).find("input[name*='[so_id]']").val();
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
                poId: poId,
                soId: soId,
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
    getItemCostPrice(tr);
    setTableCalculation();
});

// Get Cost Centers
function getCostCenters(storeLocationId) {
    $("#cost_center_div").hide(); // Hide by default

    $.ajax({
        url: "/get-cost-centers",
        method: "GET",
        dataType: "json",
        data: {
            locationId: storeLocationId,
        },
        success: function (data) {
            if (Array.isArray(data) && data.length > 0) {
                let options = "";

                data.forEach(function (costcenter) {
                    const selected =
                        costcenter.id == selectedCostCenterId ? "selected" : "";
                    options += `<option value="${costcenter.id}" ${selected}>${costcenter.name}</option>`;
                });

                $(".cost_center").html(options);
                $("#cost_center_div").show();
            } else {
                $(".cost_center").empty();
                $("#cost_center_div").hide();
            }
        },
        error: function (xhr) {
            Swal.fire({
                title: "Error!",
                text:
                    xhr?.responseJSON?.message ||
                    "Failed to load cost centers.",
                icon: "error",
            });
        },
    });
}

// 1. Handle location change
$(document).on("change", ".header_store_id", function () {
    const selectedStoreId = $(this).val();
    if (selectedStoreId) {
        getSubStores(selectedStoreId, true); // pass true to check on substore load
        getCostCenters(selectedStoreId);
    }
});

// 2. Handle sub-store change
$(document).on("change", ".sub_store", function () {
    const selectedStoreId = $(".header_store_id").val();
    const selectedSubStoreId = $(this).val();
    const isRequired = $(this).find(":selected").data("warehouse-required");

    if (selectedStoreId && selectedSubStoreId && isRequired) {
        checkWarehouseSetup(selectedStoreId, selectedSubStoreId);
    }
});

// 3. On page load trigger if values already present
const selectedStoreId = $(".header_store_id").val();
const $selectedSubStore = $(".sub_store").find("option:selected");
const selectedSubStoreId = $(".sub_store").val();
const isSubStoreRequired =
    Number($selectedSubStore.data("warehouse-required")) === 1;

if (selectedStoreId) {
    const path = window.location.pathname;
    const match = path.match(/\/material-receipts\/edit\/(\d+)/);
    const id = match ? match[1] : null;
    if (id == null) {
        getSubStores(selectedStoreId, selectedSubStoreId);
    }
    // avoid double-check
    getCostCenters(selectedStoreId);
}

if (selectedStoreId && selectedSubStoreId && isSubStoreRequired) {
    checkWarehouseSetup(selectedStoreId, selectedSubStoreId);
}
// 4. Get Sub Stores
function getSubStores(storeLocationId, selectedSubStoreId = null) {
    const storeId = storeLocationId;

    $.ajax({
        url: "/sub-stores/store-wise",
        method: "GET",
        dataType: "json",
        data: {
            store_id: storeId,
            sub_type: "main",
        },
        success: function (response) {
            if (
                response.status === 200 &&
                Array.isArray(response.data) &&
                response.data.length
            ) {
                let options = "";

                response.data.forEach(function (location) {
                    const isSelected =
                        selectedSubStoreId && location.id == selectedSubStoreId
                            ? "selected"
                            : "";
                    options += `<option value="${location.id}" data-warehouse-required="${location.is_warehouse_required}" ${isSelected}>${location.name}</option>`;
                });

                $(".sub_store").html(options);

                if (selectedSubStoreId) {
                    $(".sub_store").val(selectedSubStoreId).trigger("change");
                }

                // ✅ Trigger change manually after population to check warehouse setup
                const $selectedOption = $(".sub_store option:selected");
                const subStoreId = $selectedOption.val();
                const isWarehouseRequired = Number(
                    $selectedOption.data("warehouse-required")
                );

                if (subStoreId && isWarehouseRequired) {
                    checkWarehouseSetup(storeId, subStoreId);
                }
            } else {
                $(".sub_store").empty();
            }
        },
        error: function (xhr) {
            Swal.fire({
                title: "Error!",
                text: xhr?.responseJSON?.message || "Something went wrong!",
                icon: "error",
            });
        },
    });
}

// 5. Warehouse Setup Validation
function checkWarehouseSetup(storeId, subStoreId) {
    $.ajax({
        url: "/material-receipts/warehouse/check-setup",
        method: "GET",
        dataType: "json",
        data: { store_id: storeId, sub_store_id: subStoreId },
        success: function (data) {
            if (data.status === 204 && !data.is_setup) {
                Swal.fire({
                    title: "Warehouse Setup Missing",
                    text: data.message,
                    icon: "error",
                });
                disableWarehouseActions();
            } else if (data.status === 200 && data.is_setup) {
                enableWarehouseActions();
            } else {
                Swal.fire({
                    title: "Error!",
                    text: "Something went wrong while checking warehouse setup.",
                    icon: "error",
                });
                disableWarehouseActions();
            }
        },
        error: function (xhr) {
            Swal.fire({
                title: "Error!",
                text: xhr?.responseJSON?.message || "Warehouse check failed:",
                icon: "error",
            });
            disableWarehouseActions();
        },
    });
}

// Utility: Enable or Disable UI elements
function enableWarehouseActions() {
    $(".vendor_name").prop("disabled", false).trigger("change.select2");
    $(".addNewItemBtn")
        .css({ "pointer-events": "auto", opacity: "1" })
        .removeClass("disabled");
    $(".addStoragePointBtn")
        .css({ "pointer-events": "auto", opacity: "1" })
        .removeClass("disabled");
    $(".is_warehouse_required").val(1);
}

function disableWarehouseActions() {
    $(".vendor_name").prop("disabled", true).trigger("change.select2");
    $(".addNewItemBtn")
        .css({ "pointer-events": "none", opacity: "0.6" })
        .addClass("disabled");
    $(".addStoragePointBtn")
        .css({ "pointer-events": "none", opacity: "0.6" })
        .addClass("disabled");
    $(".is_warehouse_required").val(0);
}

// GLOBALS
let itemStorageMap = {}; // Key = item ID or code, Value = array of storage points
let allStoragePointsList = [];
let activeRowIndex = null;
let expectedInvQty = 0;

// Generate Packets
function generatePackets(activeRowIndex, itemId, qty) {
    let uomId = Number(
        $("#itemTable #row_" + activeRowIndex)
            .find("select[name*='[uom_id]']")
            .val()
    );
    // Call backend API to get item conversion & storage details
    $.ajax({
        url: `/material-receipts/warehouse/item-uom-info`,
        method: "GET",
        dataType: "json",
        data: {
            item_id: itemId,
            uom_id: uomId,
            qty: qty,
        },
        success: function (response) {
            if (response.status === 200 && response.data) {
                const invQty = response.data.inventory_qty;
                const storageUom = response.data.item.storage_uom_id;
                let totalPackets = Math.ceil(invQty);
                let perPktQty = totalPackets;
                if (storageUom) {
                    perPktQty = response.data.item.storage_uom_conversion;
                }
                totalPackets = Math.ceil(invQty / perPktQty);
                const packetUom = response.data.storage_uom_name || "PKT";
                expectedInvQty = invQty;

                let rows = "";
                const packets = [];
                for (let i = 0; i < totalPackets; i++) {
                    let defaultQty =
                        i < totalPackets - 1
                            ? perPktQty
                            : invQty - perPktQty * (totalPackets - 1);
                    const id = $(this).find(".storage-packet-id").val() || null;
                    const qty =
                        $(this).find(".storage-packet-qty").val() || defaultQty;
                    const packet_number =
                        $(this).find(".storage-packet-number").val() || null;
                    packets.push({
                        id: id,
                        quantity: qty,
                        packet_number: packet_number,
                    });
                }
                $(
                    `#itemTable #row_${activeRowIndex} input[name*='[storage_packets]']`
                ).val(JSON.stringify(packets));
            } else {
                Swal.fire(
                    "Error",
                    "Could not fetch item storage conversion info.",
                    "error"
                );
            }
        },
        error: function (xhr) {
            Swal.fire(
                "Error",
                xhr?.responseJSON?.message || "API call failed",
                "error"
            );
        },
    });

    const packets = [];
    $("#storagePacketTable tbody tr").each(function () {
        const id = $(this).find(".storage-packet-id").val() || null;
        const qty = parseFloat($(this).find(".storage-packet-qty").val()) || 0;
        const packet_number =
            $(this).find(".storage-packet-number").val() || null;
        packets.push({ id: id, quantity: qty, packet_number: packet_number });
    });

    $(`#itemTable #row_${activeRowIndex} input[name*='[storage_packets]']`).val(
        JSON.stringify(packets)
    );
}

// Open modal on icon/button click
$(document).on("click", ".addStoragePointBtn", function () {
    activeRowIndex = $(this).data("row-count");
    let qty = Number(
        $("#itemTable #row_" + activeRowIndex)
            .find("[name*='[order_qty]']")
            .val()
    );

    if (!qty) {
        Swal.fire({
            title: "Error!",
            text: "Please enter quantity then you can add store location.",
            icon: "error",
        });
        return false;
    }

    let itemId = $("#itemTable #row_" + activeRowIndex)
        .find("[name*='[item_id]']")
        .val();
    // Only extract packets if no storage_packets exists yet
    const existingValue = $(
        `#itemTable #row_${activeRowIndex} input[name*='[storage_packets]']`
    ).val();

    let parsed = null;
    if (existingValue && existingValue.length) {
        parsed = JSON.parse(existingValue);
    } else {
        // If no existing value, initialize with empty array
        getOldPackets(activeRowIndex);
        const $storageInput = $(
            `#itemTable #row_${activeRowIndex} input[name*='[storage_packets]']`
        ).val();
        if ($storageInput) {
            parsed = JSON.parse($storageInput); // re-parse after rebuild
        }
    }

    if (Array.isArray(parsed) && parsed.length > 0) {
        // console.log('123');
        populatePacketTable(parsed);
        updateAddButtonState();
        $("#storagePointsRowIndex").val(activeRowIndex);
        $("#storagePointsModal").modal("show");
        return;
    }
});

// Utility: Extract and save old packets into hidden input
function getOldPackets(rowCount) {
    const packets = [];

    $(`[id="row_${rowCount}"]`)
        .find("[name*='[packet_number]']")
        .each(function (index, item) {
            let key = index + 1;
            const id = $(item)
                .closest("tr")
                .find(`[name*='[${key}][packet_id]']`)
                .val();
            const packet_number = $(item)
                .closest("tr")
                .find(`[name*='[${key}][packet_number]']`)
                .val();
            const quantity = $(item)
                .closest("tr")
                .find(`[name*='[${key}][quantity]']`)
                .val();
            if (quantity || packet_number) {
                packets.push({
                    id: id,
                    quantity: quantity,
                    packet_number: packet_number,
                });
                expectedInvQty += Number(quantity);
            }
        });

    if (packets.length) {
        $(`#itemTable #row_${rowCount} input[name*='[storage_packets]']`).val(
            JSON.stringify(packets)
        );
    }
}

// Populate the packet table with data
function populatePacketTable(data) {
    const activeRowIndex = $("#storagePointsRowIndex").val();
    let rows = "";
    data.forEach((row, i) => {
        rows += `
            <tr data-index="${i}">
                <td><input type="checkbox" class="form-check-input packet-row-check" data-index="${i}" /></td>
                <td>
                    <input type="number" step="any" value="${row.quantity}" class="form-control storage-packet-qty mw-100" name="components[${activeRowIndex}][packets][${i}][quantity]" data-index="${i}" />
                    <input type="hidden" step="any" value="${row.id}" class="form-control storage-packet-id mw-100" name="components[${activeRowIndex}][packets][${i}][packet_id]" data-index="${i}" />
                </td>
                <td>
                    <input type="text" step="any" value="${row.packet_number}" class="form-control storage-packet-number mw-100" name="components[${activeRowIndex}][packets][${i}][packet_number]" data-index="${i}" />
                </td>
                <td>
                    <a href="#" class="text-primary add-storage-row-header" data-index="${i}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                    </a>
                    <a href="#" class="text-danger remove-storage-row" data-index="${i}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    </a>
                </td>
            </tr>`;
    });

    $("#storagePacketTable tbody").html(rows);

    $("#storagePacketTable tfoot").remove();
    $("#storagePacketTable").append(`
        <tfoot>
            <tr>
                <td class="text-end fw-bold">Total</td>
                <td><span id="storagePacketTotal">0</span></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    `);
    updatePacketTotal();
    updateAddButtonState();
}

// Recalculate total
$(document).on("input", ".storage-packet-qty", function () {
    updatePacketTotal();
    updateAddButtonState();
});

// Delete row
$(document).on("click", ".remove-storage-row", function () {
    $(this).closest("tr").remove();
    updatePacketTotal();
    updateAddButtonState();
});

// Add new row
$(document).on("click", ".add-storage-row-header", function () {
    const activeRowIndex = $("#storagePointsRowIndex").val();
    let total = 0;
    $(".storage-packet-qty").each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    if (total >= expectedInvQty) return;

    const remaining = expectedInvQty - total;
    const index = $("#storagePacketTable tbody tr").length;
    const baseUom =
        $("#storagePacketTable tbody tr:first-child td:nth-child(4)").text() ||
        "PCS";

    const row = `
        <tr data-index="${index}">
            <td><input type="checkbox" class="form-check-input packet-row-check" data-index="${index}" /></td>
            <td>
                <input type="number" step="any" value="${remaining}" class="form-control storage-packet-qty mw-100" name="components[${activeRowIndex}][packets][${index}][quantity]" data-index="${index}" />
            </td>
            <td>
                <input type="text" step="any" class="form-control storage-packet-number mw-100" name="components[${activeRowIndex}][packets][${index}][packet_number]" data-index="${index}" />
            </td>
            <td>
                <a href="#" class="text-primary add-storage-row-header" data-index="${index}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                </a>
                <a href="#" class="text-danger remove-storage-row" data-index="${index}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </a>
            </td>
        </tr>`;

    $("#storagePacketTable tbody").append(row);
    updatePacketTotal();
    updateAddButtonState();
});

// Multi-delete selected
$(document).on("click", ".delete-storage-row-header", function () {
    $(".packet-row-check:checked").each(function () {
        $(this).closest("tr").remove();
    });
    updatePacketTotal();
    updateAddButtonState();
});

function updatePacketTotal() {
    let total = 0;
    $(".storage-packet-qty").each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    $("#storagePacketTotal").text(total);
}

function updateAddButtonState() {
    const total = Number($("#storagePacketTotal").text());

    $(".add-storage-row-header").prop("disabled", total >= expectedInvQty);
}

// Save button validation
$(document).on("click", "#saveStoragePointsBtn", function () {
    const totalQty = Number($("#storagePacketTotal").text());

    if (totalQty !== expectedInvQty) {
        Swal.fire(
            "Error",
            `Total quantity (${totalQty}) must equal inventory quantity (${expectedInvQty}).`,
            "error"
        );
        return;
    }

    const packets = [];
    $("#storagePacketTable tbody tr").each(function () {
        const id = $(this).find(".storage-packet-id").val() || null;
        const qty = parseFloat($(this).find(".storage-packet-qty").val()) || 0;
        const packet_number =
            $(this).find(".storage-packet-number").val() || null;
        packets.push({ id: id, quantity: qty, packet_number: packet_number });
    });
    $(
        `#itemTable #row_${activeRowIndex} input[name*='[storage_packets]']`
    ).empty(); // Clear existing value
    $(`#itemTable #row_${activeRowIndex} input[name*='[storage_packets]']`).val(
        JSON.stringify(packets)
    );
    $("#storagePointsModal").modal("hide");
});

function initAttributeAutocomplete(context = document) {
    $(context)
        .find(".attr-autocomplete")
        .each(function () {
            let $input = $(this);
            $input.autocomplete({
                minLength: 0,
                source: function (request, response) {
                    let itemId =
                        $input
                            .closest("tr")
                            .find("input[name*='item_id']")
                            .val() || "";
                    let attrGroupId = $input.data("attr-group-id");
                    $.ajax({
                        url: "/search",
                        method: "GET",
                        dataType: "json",
                        data: {
                            q: request.term,
                            type: "item_attr_value",
                            item_id: itemId,
                            attr_group_id: attrGroupId,
                        },
                        success: function (data) {
                            response(
                                $.map(data, function (item) {
                                    return {
                                        id: item.id,
                                        label: item.value,
                                        value: item.value,
                                    };
                                })
                            );
                        },
                        error: function (xhr) {
                            console.error(
                                "Error fetching attribute values:",
                                xhr.responseText
                            );
                        },
                    });
                },
                select: function (event, ui) {
                    const row = $input.closest("tr");
                    const rowCount = row.find('[name*="row_count"]').val();
                    const attrGroupId = $input.data("attr-group-id");
                    $input.val(ui.item.label);
                    $(
                        `[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`
                    ).val(ui.item.id);
                    qtyEnabledDisabled();
                    setSelectedAttribute(rowCount);
                    const itemId = $("#attribute tbody tr")
                        .find('[name*="[item_id]"]')
                        .val();
                    const itemAttributes = [];
                    $("#attribute tbody tr").each(function () {
                        const attr_id = $(this)
                            .find('[name*="[attribute_id]"]')
                            .val();
                        const attr_value = $(this)
                            .find('[name*="[attribute_value]"]')
                            .val();
                        itemAttributes.push({
                            attr_id: attr_id,
                            attr_value: attr_value,
                        });
                    });
                    return false;
                },
                focus: function (event, ui) {
                    event.preventDefault();
                },
            });
            $input.on("focus", function () {
                if (!$(this).val()) {
                    $(this).autocomplete("search", "");
                }
            });
            $input.on("input", function () {
                if (!$(this).val()) {
                    const row = $input.closest("tr");
                    const rowCount = row.find('[name*="row_count"]').val();
                    const attrGroupId = $input.data("attr-group-id");
                    $(
                        `[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`
                    ).val("");
                    qtyEnabledDisabled();
                }
            });
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

// Asset Detail Modal - Open & Populate
$(document).on("click", ".assetDetailBtn", function () {
    const rowCount = $(this).data("row-count");
    const categoryId = $(this).data("asset-cat-id");
    const categoryName = $(this).data("asset-cat-name");
    const assetCode = $(this).data("asset-code");
    const assetName = $(this).data("asset-name");
    const brandName = $(this).data("asset-brand-name");
    const modelNumber = $(this).data("asset-model-number");
    const expectedLife = $(this).data("asset-expected-life");
    const capitalizationDate = $(".document_date").val();
    const salvagePercentage = $(this).data("asset-salvage-perc");

    $(".asset-detail-modal-body").data("row-count", rowCount);

    const $hidden = $(`input[name="components[${rowCount}][assetDetailData]"]`);
    let savedAsset = {};

    if ($hidden.length && $hidden.val()) {
        try {
            savedAsset = JSON.parse($hidden.val());
        } catch (e) {
            console.warn("Invalid saved assetDetailData");
        }
    }

    // Don't recalculate salvage if already saved
    let itemValue = Number(
        $("#itemTable #row_" + rowCount)
            .find("[name*='[item_total_cost]']")
            .val()
    );
    let salvageValue =
        savedAsset.salvage_value ?? (itemValue * salvagePercentage) / 100;

    const assetCodeReadOnly = assetCode ? "readonly" : "";

    let html = `
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label">Asset Name <span class="text-danger">*</span></label>
                <input type="text" name="asset_name" class="form-control" required value="${escapeHTML(
                    savedAsset.asset_name || assetName
                )}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Asset Category <span class="text-danger">*</span></label>
                <input type="text" name="asset_category_name" class="form-control" required readonly value="${escapeHTML(
                    savedAsset.asset_category_name || categoryName
                )}" />
                <input type="hidden" name="asset_category_id" value="${escapeHTML(
                    savedAsset.asset_category_id || categoryId
                )}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Capitalization Date</label>
                <input type="date" name="capitalization_date" class="form-control" value="${escapeHTML(
                    savedAsset.capitalization_date || capitalizationDate
                )}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Brand Name <span class="text-danger">*</span></label>
                <input type="text" name="brand_name" class="form-control" required value="${escapeHTML(
                    savedAsset.brand_name || brandName
                )}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Model Number <span class="text-danger">*</span></label>
                <input type="text" name="model_no" class="form-control" required value="${escapeHTML(
                    savedAsset.model_number || modelNumber
                )}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Estimated Life (in years) <span class="text-danger">*</span></label>
                <input type="number" name="estimated_life" class="form-control" required value="${escapeHTML(
                    savedAsset.estimated_life || expectedLife
                )}" />
            </div>
            <div class="col-md-6">
                <label class="form-label">Salvage Value <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="salvage_value" class="form-control" required value="${escapeHTML(
                    salvageValue
                )}" readonly />
            </div>
        </div>
    `;

    $(".asset-detail-modal-body").html(html);
});

// Save Asset Modal
$(document).on("click", ".submitAssetBtn", function (e) {
    e.preventDefault();
    const $modal = $("#assetDetailModal");
    const rowCount = $modal.find(".asset-detail-modal-body").data("row-count");
    const $inputs = $modal.find("input");
    let isValid = true;
    let data = {};

    $inputs.removeClass("is-invalid");

    $inputs.each(function () {
        const $input = $(this);
        const name = $input.attr("name");
        const value = $input.val();

        // Skip validation for capitalization_date
        if (
            $input.prop("required") &&
            name !== "capitalization_date" &&
            (!value || value.trim() === "")
        ) {
            $input.addClass("is-invalid");
            isValid = false;
        }

        data[name] = value;
    });

    if (!isValid) {
        Swal.fire({
            icon: "error",
            title: "Missing Fields",
            text: "Please fill all required asset detail fields.",
        });
        return;
    }

    const hiddenFieldName = `components[${rowCount}][assetDetailData]`;
    const $targetRow = $(`#row_${rowCount}`);
    let $hidden = $targetRow.find(`input[name="${hiddenFieldName}"]`);

    if ($hidden.length === 0) {
        $targetRow.append(`<input type="hidden" name="${hiddenFieldName}" />`);
        $hidden = $targetRow.find(`input[name="${hiddenFieldName}"]`);
    }

    $hidden.val(JSON.stringify(data));
    $modal.modal("hide");
    $targetRow.find(".assetDetailBtn").addClass("text-success");
});

// Escape utility
function escapeHTML(str) {
    return String(str || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/* =========================
   Item Batches Modal Logic
   ========================= */

let activeBatchRowIndex = null;
let expectedBatchQty = 0;
let canEditBatch = true; // computed per-open
let isExpiry = 0;
// ---- helpers ------------------------------------------------

// Helper: robustly read data-is-batch-number
function getIsBatchEnable($el) {
    // Try the correct name first (camelCase for jQuery .data())
    let raw = $el.data("isBatchNumber");

    // Fallback to dashed (rarely works with .data())
    if (raw === undefined) raw = $el.data("is-batch-number");

    // Fallback to the TYPO currently in your HTML
    if (raw === undefined) raw = $el.attr("data-is-batch-nnumber");

    // Final fallback to the correct dashed attr
    if (raw === undefined) raw = $el.attr("data-is-batch-number");

    // Normalize
    return /^(1|true|yes)$/i.test(String(raw));
}

// Get LotNumber For Locked
function getLotNumberForLocked() {
    const documentDate =
        $(".document_date").val() || new Date().toISOString().split("T")[0];
    const documentNumber = $(".document_number").val() || "";
    const bookCode = $(".book_code").val() || "";
    return `${documentDate}/${bookCode}/${documentNumber}`;
}

function updateBatchTotal() {
    let total = 0;
    $("#itemBatchTable tbody .batch-qty").each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    // Optionally show total somewhere
    // $('#batchTotal').text(total);
    return total;
}

function reindexBatchRows() {
    const $tbody = $("#itemBatchTable tbody");
    $tbody.find("tr").each(function (i) {
        $(this).attr("data-index", i);
        $(this)
            .find(".batch-row-check")
            .attr("data-index", i)
            .prop("disabled", i === 0);

        $(this)
            .find("input[name]")
            .each(function () {
                const name = $(this).attr("name");
                // components[ROW][batches][OLD]
                const newName = name.replace(
                    /\[batches]\[\d+]/,
                    `[batches][${i}]`
                );
                $(this).attr("name", newName);
            });
    });
}

function generateBatchRow(index, opts = {}, allowEdit = true) {
    const {
        batch_number = "",
        manufacturing_year = "",
        expiry_date = "",
        quantity = "",
    } = opts;

    const isFirst = index === 0;
    const ro = allowEdit ? "" : "readonly";
    const dis = allowEdit ? "" : "disabled";

    return `
       <tr data-index="${index}">
         <td>
           <input type="checkbox" class="form-check-input batch-row-check"
                  data-index="${index}" ${isFirst ? "disabled" : ""} ${dis} />
         </td>
         <td>
           <input type="text" class="form-control mw-100 batch-number"
                  name="components[${activeBatchRowIndex}][batches][${index}][batch_number]"
                  value="${batch_number}" required ${ro} ${dis} />
         </td>
         <td>
           <input type="number" class="form-control mw-100 manufacturing-year"
                  name="components[${activeBatchRowIndex}][batches][${index}][manufacturing_year]"
                  min="1900" max="2099" value="${manufacturing_year}" ${ro} ${dis} />
         </td>
         <td>
           <input type="date" class="form-control mw-100 expiry-date"
                  name="components[${activeBatchRowIndex}][batches][${index}][expiry_date]"
                  value="${expiry_date}" ${ro} ${dis} />
         </td>
         <td>
           <input type="number" step="any" class="form-control mw-100 batch-qty"
                  name="components[${activeBatchRowIndex}][batches][${index}][quantity]"
                  value="${quantity}" ${ro} ${dis} />
         </td>
         <td>
           ${
               allowEdit
                   ? `
                <a href="#" class="text-primary add-batch-row-header" data-index="${index}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                </a>
             ${
                 isFirst
                     ? ""
                     : `
                <a href="#" class="text-danger remove-batch-row" data-index="${index}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </a>`
             }
           `
                   : ""
           }
         </td>
       </tr>`;
}

function populateBatchTable(data = [], allowEdit) {
    const $tbody = $("#itemBatchTable tbody");
    $tbody.empty();

    const lotNumber = getLotNumberForLocked();

    // If no saved data, seed one row
    if (!Array.isArray(data) || data.length === 0) {
        data = [
            {
                batch_number: allowEdit ? "" : lotNumber,
                manufacturing_year: "",
                expiry_date: "",
                quantity: expectedBatchQty,
            },
        ];
    }

    data.forEach((row, i) => {
        $tbody.append(
            generateBatchRow(
                i,
                {
                    batch_number:
                        row.batch_number ?? (allowEdit ? "" : lotNumber),
                    manufacturing_year: row.manufacturing_year ?? "",
                    expiry_date: row.expiry_date ?? "",
                    quantity: row.quantity ?? "",
                },
                allowEdit
            )
        );
    });

    updateBatchTotal();
    applyDateGuards();
}

// ---- open modal ------------------------------------------------

$(document).on("click", ".addBatchBtn", function () {
    const $btn = $(this);
    activeBatchRowIndex = $(this).data("row-count");
    isExpiry = $(this).data("is-expiry");

    // IMPORTANT: read flag robustly
    canEditBatch = getIsBatchEnable($btn); // true when data-is-batch-number="1"
    console.log("Batch editing enabled:", canEditBatch);

    // get expected qty from row
    expectedBatchQty =
        Number(
            $(`#itemTable #row_${activeBatchRowIndex}`)
                .find("[name*='[order_qty]']")
                .val()
        ) || 0;

    if (!expectedBatchQty) {
        Swal.fire("Error!", "Please enter quantity first.", "error");
        return;
    }

    // read saved data for this row (if any)
    const $batchInput = $(
        `#itemTable #row_${activeBatchRowIndex} input[name*='[batch_details]']`
    );
    let existing = [];
    if ($batchInput.val()) {
        try {
            existing = JSON.parse($batchInput.val());
        } catch (e) {
            existing = [];
        }
    }

    // Always rebuild rows fresh based on the CURRENT flag
    populateBatchTable(existing, canEditBatch);

    // Toggle header action buttons INSIDE THIS MODAL
    const $modal = $("#itemBatchModal"); // <-- use your actual modal id
    const $hdrBtns = $modal.find(
        ".add-batch-row-header, .delete-batch-row-header"
    );
    $hdrBtns
        .toggleClass("d-none", !canEditBatch)
        .prop("disabled", !canEditBatch);

    $("#itemBatchRowIndex").val(activeBatchRowIndex);
    $("#itemBatchIsExpiry").val(isExpiry); // hidden input for expiry flag
    $modal.modal("show");
});

// ---- add row ---------------------------------------------------

$(document).on("click", ".add-batch-row-header", function (e) {
    if (!canEditBatch) {
        e.preventDefault();
        return false;
    }

    const $tbody = $("#itemBatchTable tbody");
    const index = $tbody.find("tr").length;

    const total = updateBatchTotal();
    if (total >= expectedBatchQty) return;

    const remaining = Math.max(0, expectedBatchQty - total);

    $tbody.append(
        generateBatchRow(
            index,
            {
                batch_number: "",
                manufacturing_year: "",
                expiry_date: "",
                quantity: remaining,
            },
            true
        )
    );

    updateBatchTotal();
    applyDateGuards();
});

// ---- delete selected rows -------------------------------------

$(document).on("click", ".delete-batch-row-header", function (e) {
    if (!canEditBatch) {
        e.preventDefault();
        return false;
    }

    const $tbody = $("#itemBatchTable tbody");
    const $rows = $tbody.find("tr");
    const $checked = $tbody.find(".batch-row-check:checked");

    if ($checked.length === 0) return;
    if ($checked.length >= $rows.length) {
        Swal.fire("Warning", "At least one batch row must remain.", "warning");
        return;
    }

    $checked.each(function () {
        const idx = $(this).data("index");
        if (idx !== 0) $(this).closest("tr").remove();
    });

    reindexBatchRows();
    updateBatchTotal();
});

// ---- delete one row -------------------------------------------

$(document).on("click", ".remove-batch-row", function (e) {
    if (!canEditBatch) {
        e.preventDefault();
        return false;
    }
    e.preventDefault();

    const $tbody = $("#itemBatchTable tbody");
    const totalRows = $tbody.find("tr").length;
    if (totalRows === 1) {
        Swal.fire("Warning", "At least one batch row is required.", "warning");
        return;
    }

    $(this).closest("tr").remove();
    reindexBatchRows();
    updateBatchTotal();
});

// ---- qty change -> total --------------------------------------

$(document).on("input", "#itemBatchTable tbody .batch-qty", function () {
    updateBatchTotal();
});

// ---- save batches ---------------------------------------------

$(document).on("click", "#saveItemBatchBtn", function (e) {
    e.preventDefault();

    const $tbody = $("#itemBatchTable tbody");
    const rows = $tbody.find("tr");
    const now = new Date();
    const currentYear = now.getFullYear();
    const today = new Date(todayISO()); // midnight today local
    const EPS = 1e-6;
    let isExpiry = Number($("#itemBatchIsExpiry").val()) || 0;
    console.log("Expiry flag:", isExpiry);

    // clear old errors
    $tbody.find(".invalid-feedback").remove();
    $tbody.find("input").removeClass("is-invalid");

    let isValid = true;
    let totalQty = 0;
    const payload = [];

    rows.each(function () {
        const $row = $(this);
        const $bn = $row.find(".batch-number");
        const $year = $row.find(".manufacturing-year");
        const $exp = $row.find(".expiry-date");
        const $qty = $row.find(".batch-qty");

        const batch_number = ($bn.val() || "").trim();
        const manufacturing_year = ($year.val() || "").trim();
        const expiry_date = ($exp.val() || "").trim();
        const quantity = parseFloat(($qty.val() || "").trim()) || 0;

        // Batch number required
        if (!batch_number) {
            $bn.addClass("is-invalid").after(
                '<div class="invalid-feedback">Batch number is required.</div>'
            );
            isValid = false;
        }

        // Manufacturing year strictly past (if present)
        if (manufacturing_year) {
            const yr = parseInt(manufacturing_year, 10);
            if (
                !/^\d{4}$/.test(manufacturing_year) ||
                yr <= 1900 ||
                yr >= currentYear
            ) {
                $year
                    .addClass("is-invalid")
                    .after(
                        `<div class="invalid-feedback">Manufacturing year must be a valid past year (1901–${
                            currentYear - 1
                        }).</div>`
                    );
                isValid = false;
            }
        }

        // Expiry date mandatory iff isExpiry = 1 AND strictly future
        if (isExpiry == 1) {
            console.log("expiry_date", expiry_date);

            if (!expiry_date) {
                $exp.addClass("is-invalid").after(
                    '<div class="invalid-feedback">Expiry date is required.</div>'
                );
                isValid = false;
            } else {
                const exp = new Date(expiry_date);
                // must be > today (tomorrow or later)
                if (!(exp > today)) {
                    $exp.addClass("is-invalid").after(
                        '<div class="invalid-feedback">Expiry date must be in the future.</div>'
                    );
                    isValid = false;
                }
            }
        }

        // Quantity must be > 0
        if (!(quantity > 0)) {
            $qty.addClass("is-invalid").after(
                '<div class="invalid-feedback">Quantity must be greater than 0.</div>'
            );
            isValid = false;
        }

        totalQty += quantity;
        payload.push({
            batch_number,
            manufacturing_year,
            expiry_date,
            quantity,
        });
    });

    if (!isValid) {
        Swal.fire(
            "Invalid Input",
            "Please correct the highlighted fields.",
            "error"
        );
        return;
    }

    if (Math.abs(totalQty - expectedBatchQty) > EPS) {
        Swal.fire(
            "Quantity Mismatch",
            `Total batch quantity (${totalQty}) must match order quantity (${expectedBatchQty}).`,
            "error"
        );
        return;
    }

    // persist to hidden input of that row
    const hiddenName = `components[${activeBatchRowIndex}][batch_details]`;
    const $rowWrap = $(`#itemTable #row_${activeBatchRowIndex}`);
    let $hidden = $rowWrap.find(`input[name="${hiddenName}"]`);
    if ($hidden.length === 0) {
        $rowWrap.append(`<input type="hidden" name="${hiddenName}" />`);
        $hidden = $rowWrap.find(`input[name="${hiddenName}"]`);
    }
    $hidden.val(JSON.stringify(payload));

    $("#itemBatchModal").modal("hide");
    $rowWrap.find(".addBatchBtn").addClass("text-success"); // optional visual cue
});

function todayISO() {
    const d = new Date();
    const m = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${d.getFullYear()}-${m}-${day}`;
}
function isoNDaysFromToday(n) {
    const d = new Date();
    d.setDate(d.getDate() + n);
    const m = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${d.getFullYear()}-${m}-${day}`;
}

// Apply min/max guards to all visible inputs inside the modal
function applyDateGuards() {
    const currentYear = new Date().getFullYear();

    // Expiry date: strictly future -> min = tomorrow
    const minExpiry = isoNDaysFromToday(1);
    $("#itemBatchTable tbody .expiry-date").each(function () {
        $(this).attr("min", minExpiry);
    });

    // Manufacturing year (number): strictly past -> max = currentYear - 1
    const maxMfgYear = currentYear - 1;
    $("#itemBatchTable tbody .manufacturing-year").each(function () {
        // Keep your min 1900, adjust max to currentYear-1
        $(this).attr("max", maxMfgYear);
    });
}
