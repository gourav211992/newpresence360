/*Tax Detail Display Start*/
$(document).on('click', '.summaryTaxBtn', (e) => {
    getTaxSummary();
});
selectedCostCenterId = null;
/*Approve modal*/
$(document).on('click', '#approved-button', (e) => {
    let actionType = 'approve';
    $("#approveModal").find("#action_type").val(actionType);
    $("#approveModal #popupTitle").text("Approve Application");
    $("#approveModal").modal('show');
});

/*Reject modal*/
$(document).on('click', '#reject-button', (e) => {
    let actionType = 'reject';
    $("#approveModal #popupTitle").text("Reject Application");
    $("#approveModal").find("#action_type").val(actionType);
    $("#approveModal").modal('show');
});

function getTaxSummary()
{
    let taxSummary = {};
    $("#itemTable [id*='row_']").each(function(index, row) {
        row = $(row);
        let qty = Number(row.find('[name*="[accepted_qty]"]').val());
        let rate = Number(row.find('[name*="[rate]"]').val());
        let itemDisc = Number(row.find('[name*="[discount_amount]"]').val());
        let itemHeaderDisc = Number(row.find('[name*="[discount_amount_header]"]').val());
        let totalItemDisc = itemDisc + itemHeaderDisc;
        let totalItemValue = qty * rate;
        let totalItemValueAfterDisc = totalItemValue - totalItemDisc;
        let processedTaxTypes = {};
        if (totalItemValueAfterDisc) {
            row.find('[name*="[t_type]"]').each(function(taxIndex, TaxRow) {
                // Get tax type, percentage, and value for each tax row
                let tType = $(TaxRow).closest('td').find(`[name*="components[${index+1}][taxes][${taxIndex+1}][t_type]"]`).val();
                let tPerc = Number($(TaxRow).closest('td').find(`[name*="components[${index+1}][taxes][${taxIndex+1}][t_perc]"]`).val());
                let tValue = Number($(TaxRow).closest('td').find(`[name*="components[${index+1}][taxes][${taxIndex+1}][t_value]"]`).val());
                let dynamicKey = `${tType}_${tPerc}`;
                if (taxSummary[dynamicKey]) {
                    taxSummary[dynamicKey].totalTaxValue += tValue;
                } else {
                    taxSummary[dynamicKey] = {
                        taxType: tType,
                        taxPerc: tPerc,
                        totalTaxValue: tValue,
                        totalTaxableAmount: 0
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
    $('#expense_tax_details').html(taxSummaryHtml);
    $("#expenseTaxDetailModal").modal('show');
}

/*Tbl row highlight*/
$(document).on('click', '.mrntableselectexcel tr', (e) => {
    $(e.target.closest('tr')).addClass('trselected').siblings().removeClass('trselected');
});

$(document).on('keydown', function(e) {
    if (e.which == 38) {
        /*bottom to top*/
        $('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
    } else if (e.which == 40) {
        /*top to bottom*/
        $('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
    }
    // if($('.trselected').length) {
    //   $('html, body').scrollTop($('.trselected').offset().top - 200);
    // }
});

$(document).on('change', '.header_store_id', function () {
    const selectedStoreId = $(this).val();
    if (selectedStoreId) {
        getCostCenters(selectedStoreId);
    }
});

// 2. On page load: trigger if already selected
const selectedStoreId = $('.header_store_id').val();
if (selectedStoreId) {
    getCostCenters(selectedStoreId);
}

// Get Cost Centers
function getCostCenters(storeLocationId) {
    $("#cost_center_div").hide(); // Hide by default

    $.ajax({
        url: "/get-cost-centers",
        method: 'GET',
        dataType: 'json',
        data: {
            locationId: storeLocationId,
        },
        success: function(data) {
            if (Array.isArray(data) && data.length > 0) {
                let options = '';

                data.forEach(function(costcenter) {
                    const selected = (costcenter.id == selectedCostCenterId) ? 'selected' : '';
                    options += `<option value="${costcenter.id}" ${selected}>${costcenter.name}</option>`;
                });

                $(".cost_center").html(options);
                $("#cost_center_div").show();
            } else {
                $(".cost_center").empty();
                $("#cost_center_div").hide();
            }
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error!',
                text: xhr?.responseJSON?.message || 'Failed to load cost centers.',
                icon: 'error',
            });
        }
    });
}

/*Check box check and uncheck*/
$(document).on('change','#itemTable > thead .form-check-input',(e) => {
    if (e.target.checked) {
        $("#itemTable > tbody .form-check-input").each(function(){
            $(this).prop('checked',true);
        });
    } else {
        $("#itemTable > tbody .form-check-input").each(function(){
            $(this).prop('checked',false);
        });
    }
});

$(document).on('change','#itemTable > tbody .form-check-input',(e) => {
    if(!$("#itemTable > tbody .form-check-input:not(:checked)").length) {
        $('#itemTable > thead .form-check-input').prop('checked', true);
    } else {
        $('#itemTable > thead .form-check-input').prop('checked', false);
    }
});

/*Attribute on change*/
$(document).on('change', '[name*="comp_attribute"]', (e) => {
    let closestTr = e.target.closest('tr');
    let rowCount = e.target.closest('tr').querySelector('[name*="row_count"]').value;
    let attrGroupId = e.target.getAttribute('data-attr-group-id');
    $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).val(e.target.value);
    // closestTr = $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).closest('tr');
    // getItemDetail(closestTr);
    qtyEnabledDisabled();
    setSelectedAttribute(rowCount);
});

// Check Negative Values
let oldValue;
$(document).on('focus', '.checkNegativeVal', function(e) {
    oldValue = e.target.value;  // Store the old value when the field gains focus
});

/*qty on change*/
$(document).on('change', "[name*='accepted_qty']", async function (e) {
    const $tr = $(e.target).closest('tr');
    const $qtyInput = $(e.target);
    const acceptedQty = parseFloat($qtyInput.val()) || 0;

    const dataIndex = $tr.attr('data-index');
    const itemId = $tr.find("[name*='item_id']").val();

    $qtyInput.val(acceptedQty.toFixed(6));
    checkDuplicateObjects($qtyInput);

    if (acceptedQty <= 0) {
        Swal.fire({ title: 'Error!', text: 'Qty. cannot be zero.', icon: 'error' });
        $qtyInput.val(acceptedQty.toFixed(6));
        return;
    }

    const getVal = (selector) => {
        const el = $tr.find(selector);
        return el.length ? el.val() : '';
    };

    const data = {};
    const safeSet = (key, val) => { if (val) data[key] = val; };

    safeSet('item_id', itemId);
    safeSet('purchase_order_id', getVal("[name*='[purchase_order_id]']"));
    safeSet('po_detail_id', getVal("[name*='[po_detail_id]']"));
    safeSet('job_order_id', getVal("[name*='[job_order_id]']"));
    safeSet('jo_detail_id', getVal("[name*='[jo_detail_id]']"));
    safeSet('detail_id', getVal("[name*='[detail_id]']"));
    safeSet('qty', acceptedQty.toFixed(6));
    safeSet('type', currentProcessType);

    try {
        const response = await fetch(qtyChangeUrl + '?' + new URLSearchParams(data).toString());
        const result = await response.json();
        const resultQty = parseFloat(result.accepted_qty) || 0;
        const finalQty = resultQty.toFixed(6);
        $qtyInput.val(finalQty);

        if (result.status !== 200 && result.message) {
            Swal.fire({ title: 'Error!', text: result.message, icon: 'error' });
            return false;
        }

    } catch (err) {
        console.error(err);
        Swal.fire({ title: 'Error!', text: 'Quantity validation failed.', icon: 'error' });
    }
});

/*rate on change*/
$(document).on('change',"[name*='rate']",(e) => {
    let tr = e.target.closest('tr');
    let rate = e.target;
    let dataIndex = $(e.target).closest('tr').attr('data-index');
    let itemId = $(e.target).closest('tr').find('[name*=item_id]').val();
    let orderQuantity = $(e.target).closest('tr').find("[name*='order_qty']");
    let acceptedQuantity = $(e.target).closest('tr').find("[name*='accepted_qty']");
    let orderRate = $(e.target).closest('tr').find("[name*='rate']");
    let itemValue = $(e.target).closest('tr').find("[name*='basic_value']");
    if (Number(acceptedQuantity.val())) {
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
        let totalItemValue = (itemRate) * (parseFloat(acceptedQuantity.val()));
        totalItemValue = parseFloat(totalItemValue);
        orderRate.val(itemRate.toFixed(6));
        itemValue.val(totalItemValue.toFixed(6));
    } else {
        itemValue.val('');
    }
});

/*Calculate total amount of discount rows for the item*/
function totalItemDiscountAmount()
{
    let total = 0;
    $("#eachRowDiscountTable .display_discount_row").each(function(index,item){
        total = total + Number($(item).find('[name="itemDiscountAmount"]').val());
    });
    $("#disItemFooter #total").text(total.toFixed(2));
}

/*Each row addDiscountBtn*/
$(document).on('click', '.addDiscountBtn', (e) => {
    let rowCount = e.target.closest('button').getAttribute('data-row-count');
    let tr = '';
    let totalAmnt = 0;
    $(`[id="row_${rowCount}"]`).find("[name*='[dis_amount]']").each(function(index,item) {
        let key = index +1;
        let id = $(item).closest('tr').find(`[name*='[${key}][id]']`).val();
        let name = $(item).closest('tr').find(`[name*='[${key}][dis_name]']`).val();
        let perc = $(item).closest('tr').find(`[name*='[${key}][dis_perc]']`).val();
        let amnt = Number($(item).val()).toFixed(2);
        totalAmnt+=Number(amnt);
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
        </tr>`
    });
    $(".display_discount_row").remove();
    $("#eachRowDiscountTable #disItemFooter").before(tr);
    $("#disItemFooter #total").text(totalAmnt);
    $("#disItemFooter #row_count").val(rowCount);
    $('#itemRowDiscountModal').modal('show');
    initializeAutocompleteTED("new_item_dis_name_select", "new_item_discount_id", "new_item_dis_name", "sales_module_discount", "new_item_dis_perc");
});

// Set Each Row Item Calculation
function setTableCalculation() {
    let totalItemValue = 0;
    let totalItemDiscount = 0;
    let totalItemCost = 0;
    let totalHeaderDiscount = 0;
    let totalAfterBothDisc = 0;
    let totalTax = 0;
    let finaltotalTax = 0;
    let totalAfterTax = 0;
    let totalHeaderExp = 0;
    let grandTotal = 0;
    $("#itemTable [id*='row_']").each(function (index, item) {
        let rowCount = Number($(item).attr('data-index'));
        let qty = $(item).find("[name*='[accepted_qty]']").val() || 0;
        let rate = $(item).find("[name*='[rate]']").val() || 0;
        let itemValue = (Number(qty) * Number(rate)) || 0;
        totalItemValue+=itemValue;
        $(item).find("[name*='[basic_value]']").val(itemValue.toFixed(2));

        /*Bind Item Discount*/
        let itemDiscount = 0;
        if ($(item).find("[name*='[dis_perc]']").length && itemValue) {
            $(item).find("[name*='[dis_perc]']").each(function(index, eachItem) {
                let hiddenPerc = Number($(`[name="components[${rowCount}][discounts][${index + 1}][hidden_dis_perc]"]`).val()) || 0;
                let discPerc = hiddenPerc || Number($(eachItem).val());
                let eachDiscAmount = 0;
                if (discPerc) {
                    eachDiscAmount = (itemValue * discPerc) / 100;
                } else {
                    eachDiscAmount = Number($(`[name="components[${rowCount}][discounts][${index + 1}][dis_amount]"]`).val()) || 0;
                }
                itemDiscount += eachDiscAmount;
                $(`[name="components[${rowCount}][discounts][${index + 1}][dis_amount]"]`).val(eachDiscAmount.toFixed(2));
            });
            $(item).find("[name*='[discount_amount]']").val(itemDiscount.toFixed(2));
        } else if (!itemValue) {
            $(item).find("[name*='[discount_amount]']").val("0.00");
        }
        totalItemDiscount+=itemDiscount;

        let itemCost = itemValue - itemDiscount;
        totalItemCost+=itemCost;
        $(item).find("[name*='[item_total_cost]']").val(itemCost.toFixed(2));
        /*Bind Item Discount*/

    });

    /*Bind table footer*/
    $("#totalItemValue").attr('amount',totalItemValue).text(totalItemValue.toFixed(2));
    $("#totalItemDiscount").attr('amount',totalItemDiscount).text(totalItemDiscount.toFixed(2));
    $("#TotalEachRowAmount").attr('amount',totalItemCost).text(totalItemCost.toFixed(2));
    /*Bind table footer*/

    $("#f_sub_total")
        .attr('amount',totalItemValue.toFixed(2))
        .text(totalItemValue.toFixed(2))
        .attr('style', totalItemValue < 0 ? 'color: red !important;' : '');
    $("#f_total_discount")
        .attr('amount',totalItemDiscount.toFixed(2))
        .text(totalItemDiscount.toFixed(2))
        .attr('style', totalItemDiscount < 0 ? 'color: red !important;' : '');

    /*Bind summary header Discount*/
    let totalAmountAfterItemDis = totalItemCost;
    let disHeaderAmnt = 0;
    if ($(".display_summary_discount_row").find("[name*='[d_perc]']").length && totalAmountAfterItemDis) {
        $(".display_summary_discount_row").find("[name*='[d_perc]']").each(function(index, eachItem) {
            let eachDiscTypePrice = 0;
            let hiddenPerc = Number($(`[name="disc_summary[${index + 1}][hidden_d_perc]"]`).val()) || 0;
            let itemDiscPerc = hiddenPerc || Number($(eachItem).val());
            if(itemDiscPerc) {
                eachDiscTypePrice = (totalAmountAfterItemDis * itemDiscPerc) / 100;
            } else {
                eachDiscTypePrice = Number($(`[name="disc_summary[${index + 1}][d_amnt]"]`).val()) || 0;
            }
            $(`[name="disc_summary[${index + 1}][d_amnt]"]`).closest('td').html(`${eachDiscTypePrice.toFixed(2)}
                <input type="hidden" value="${eachDiscTypePrice.toFixed(2)}" name="disc_summary[${index + 1}][d_amnt]">
            `);
            // $(`[name="disc_summary[${index + 1}][d_amnt]"]`).val(eachDiscTypePrice.toFixed(2));
            disHeaderAmnt += eachDiscTypePrice;
        });
    } else {
        let eachDiscTypePrice = 0;
        $(".display_summary_discount_row").find("[name*='[d_perc]']").each(function(index) {
            $(`[name="disc_summary[${index + 1}][d_amnt]"]`).closest('td').html(`${eachDiscTypePrice.toFixed(2)}
                <input type="hidden" value="${eachDiscTypePrice.toFixed(2)}" name="disc_summary[${index + 1}][d_amnt]">
            `);
            // $(`[name="disc_summary[${index + 1}][d_amnt]"]`).val(eachDiscTypePrice.toFixed(2));
        });
        disHeaderAmnt += eachDiscTypePrice;
    }
    $("#disSummaryFooter #total")
        .attr('amount', disHeaderAmnt.toFixed(2))
        .text(disHeaderAmnt.toFixed(2))
        .attr('style', disHeaderAmnt < 0 ? 'color: red !important;' : '');
    $("#f_header_discount")
        .attr('amount',disHeaderAmnt.toFixed(2))
        .text(disHeaderAmnt.toFixed(2))
        .attr('style', disHeaderAmnt < 0 ? 'color: red !important;' : '');
    /*Bind summary header Discount*/

    /*Bind header discount item level*/
    $("#itemTable [id*='row_']").each(function (index, item2) {
        let rowCount2 = Number($(item2).attr('data-index'));
        let qty2 = $(item2).find("[name*='[accepted_qty]']").val() || 0;
        let rate2 = $(item2).find("[name*='[rate]']").val() || 0;
        let itemValue2 =  (Number(qty2) * Number (rate2)) || 0;
        let itemDisc2 = Number($(item2).find("[name*='[discount_amount]']").val()) || 0;
        let itemHeaderDisc = (itemValue2 - itemDisc2) / (totalItemValue - totalItemDiscount) * disHeaderAmnt;
        if(itemHeaderDisc) {
            $(item2).find("[name*='[discount_amount_header]']").val(itemHeaderDisc.toFixed(2));
        } else {
            $(item2).find("[name*='[discount_amount_header]']").val("0.00");
        }
        totalHeaderDiscount+=itemHeaderDisc;
    })
    /*Bind header discount item level*/

    /*Bind Tax*/
    const taxPromises = [];
    let isTax = $("#tax_required").val().trim().toLowerCase() === 'yes';
    $("#itemTable [id*='row_']").each(function (index, item3) {
        let rowCount3 = Number($(item3).attr('data-index'));
        let qty3 = $(item3).find("[name*='[accepted_qty]']").val() || 0;
        let rate3 = $(item3).find("[name*='[rate]']").val() || 0;
        let itemValue3 = (Number(qty3) * Number(rate3)) || 0;
        let itemDisc3 = Number($(item3).find("[name*='[discount_amount]']").val()) || 0;
        let itemHeaderDisc = Number($(item3).find("[name*='[discount_amount_header]']").val()) || 0;
        let itemId = $(item3).find('[name*="[item_id]"]').val();

        let price = itemValue3 - itemDisc3 - itemHeaderDisc;
        if (price > 0 && itemId) {
            if(isTax) {
                let transactionType = $(item3).find("[name*='applicability_type']").val();
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
                    location_id:locationId,
                    document_date:document_date
                }).toString();
                let urlWithParams = `${actionUrlTax}?${queryParams}`;
                let promise = fetch(urlWithParams)
                    .then(response => response.json())
                    .then(data => {
                        $(item3).find("[name*='t_d_id']").remove();
                        $(item3).find("[name*='t_code']").remove();
                        $(item3).find("[name*='t_type']").remove();
                        $(item3).find("[name*='t_perc']").remove();
                        $(item3).find("[name*='t_value']").remove();
                        if (data.status === 200) {
                            $(item3).find("[name*='item_total_cost']").after(data?.data?.html);
                        } else {
                            console.warn("Data status not 200 or HTML not found in response");
                        }
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                    });
                taxPromises.push(promise);
            }
        } else {
            $(item3).find("[name*='t_d_id']").remove();
            $(item3).find("[name*='t_code']").remove();
            $(item3).find("[name*='t_type']").remove();
            $(item3).find("[name*='t_perc']").remove();
            $(item3).find("[name*='t_value']").remove();
        }
    });

    /*Bind Tax*/

    Promise.all(taxPromises).then(() => {

        $("#itemTable [id*='row_']").each(function (index, item4) {
            let rowCount4 = Number($(item4).attr('data-index'));
            let qty4 = $(item4).find("[name*='[accepted_qty]']").val() || 0;
            let rate4 = $(item4).find("[name*='[rate]']").val() || 0;
            let itemValue4 = (Number(qty4) * Number(rate4)) || 0;
            let itemDisc4 = Number($(item4).find("[name*='[discount_amount]']").val()) || 0;
            let itemHeaderDisc = Number($(item4).find("[name*='[discount_amount_header]']").val()) || 0;

            let totalAmountAfterItemDis = itemValue4 - itemDisc4;
            if (isTax) {
                if($(item4).find("[name*='[t_perc]']").length && totalAmountAfterItemDis) {
                    let taxAmountRow = 0.00;
                    $(item4).find("[name*='[t_perc]']").each(function(index,eachItem) {
                        let eachTaxTypePrice = 0;
                        let taxPercTax = Number($(eachItem).val());
                        if(taxPercTax) {
                            eachTaxTypePrice = ((totalAmountAfterItemDis - Number(itemHeaderDisc)) * taxPercTax) / 100;
                            $(item4).find(`[name="components[${rowCount4}][taxes][${index+1}][t_value]"]`).val(eachTaxTypePrice.toFixed(2));
                        } else {
                            $(item4).find(`[name="components[${rowCount4}][taxes][${index+1}][t_value]"]`).val(eachTaxTypePrice.toFixed(2));
                        }
                        if($(item4).find(`[name="components[${rowCount4}][taxes][${index+1}][applicability_type]"]`).val() == 'collection') {
                            taxAmountRow += eachTaxTypePrice;
                        } else {
                            taxAmountRow -= eachTaxTypePrice;
                        }
                    });
                    totalTax += taxAmountRow;
                }
            }
        });

        totalAfterBothDisc = Number(totalItemValue || 0)-Number(totalItemDiscount || 0)-Number(totalHeaderDiscount || 0);
        totalAfterTax = Number(totalItemValue || 0)-Number(totalItemDiscount || 0)-Number(totalHeaderDiscount || 0)+Number(totalTax || 0);
        $("#f_taxable_value")
            .attr('amount',totalAfterBothDisc.toFixed(2))
            .text(totalAfterBothDisc.toFixed(2))
            .attr('style', totalAfterBothDisc < 0 ? 'color: red !important;' : '');
        $("#f_tax")
            .attr('amount',totalTax.toFixed(2))
            .text(totalTax.toFixed(2))
            .attr('style', totalTax < 0 ? 'color: red !important;' : '');
        $("#f_total_after_tax")
            .attr('amount',totalAfterTax.toFixed(2))
            .text(totalAfterTax.toFixed(2))
            .attr('style', totalAfterTax < 0 ? 'color: red !important;' : '');

        /*Bind header Expenses*/
        if($(".display_summary_exp_row").find("[name*='[total]']").length && totalAfterTax) {
            $(".display_summary_exp_row").find("[name*='[total]']").each(function(index,eachItem) {
                let eachExpTypePrice = 0;
                let hiddenPerc = Number($(`[name="exp_summary[${index+1}][total]"]`).val()) || 0;
                let expDiscPerc = hiddenPerc || Number($(eachItem).val());
                if(expDiscPerc) {
                    eachExpTypePrice = expDiscPerc;
                    // $(`[name="exp_summary[${index+1}][total]"]`).val(eachExpTypePrice.toFixed(2));
                    $(`[name="exp_summary[${index+1}][total]"]`).closest('td').html(`
                    ${eachExpTypePrice.toFixed(2)}
                    <input type="hidden" value="${eachExpTypePrice.toFixed(2)}" name="exp_summary[${index+1}][total]">
                    `);
                } else {
                    eachExpTypePrice = Number($(`[name="exp_summary[${index+1}][total]"]`).val()) || 0;
                }
                totalHeaderExp += eachExpTypePrice;
            });
        } else {
           $(".display_summary_exp_row").find("[name*='[e_perc]']").each(function(index,eachItem) {
                let eachExpTypePrice = 0;
                // let expDiscPerc = Number($(eachItem).val());
                // $(`[name="exp_summary[${index+1}][total]"]`).val(eachExpTypePrice.toFixed(2));
                $(`[name="exp_summary[${index+1}][total]"]`).closest('td').html(`
                    ${eachExpTypePrice.toFixed(2)}
                    <input type="hidden" value="${eachExpTypePrice.toFixed(2)}" name="exp_summary[${index+1}][total]">
                    `);
                totalHeaderExp += eachExpTypePrice;
            });
        }
        $("#expSummaryFooter #total").attr('amount',totalHeaderExp.toFixed(2)).text(totalHeaderExp.toFixed(2));
        $("#f_exp").text(totalHeaderExp.toFixed(2));

        /*Bind header Expenses*/
        grandTotal = totalAfterTax + totalHeaderExp;
        $("#f_total_after_exp")
            .attr('amount',grandTotal.toFixed(2))
            .text(grandTotal.toFixed(2))
            .attr('style', grandTotal < 0 ? 'color: red !important;' : '');

        /*Bind header exp item level*/
        let total_net_total = 0;
        $("#itemTable [id*='row_']").each(function (index, item5) {
            let rowCount5 = Number($(item5).attr('data-index'));
            let qty5 = $(item5).find("[name*='[accepted_qty]']").val() || 0;
            let rate5 = $(item5).find("[name*='[rate]']").val() || 0;
            let itemValue5 =  (Number(qty5) * Number (rate5)) || 0;
            let itemDisc5 = Number($(item5).find("[name*='[discount_amount]']").val()) || 0;
            let itemHeaderDisc5 = Number($(item5).find("[name*='[discount_amount_header]']").val()) || 0;
            let itemTax5 = 0;
            if($(item5).find("[name*='[t_value]']").length) {
                $(item5).find("[name*='[t_value]']").each(function(indexing, iteming){
                    itemTax5+= Number($(iteming).val()) || 0;
                })
            }
            total_net_total += itemValue5 - itemDisc5 - itemHeaderDisc5 + itemTax5;
        });

        $("#itemTable [id*='row_']").each(function (index, item6) {
            let each_net_value = 0;
            let exp_header_amnt_item = 0;
            let rowCount6 = Number($(item6).attr('data-index'));
            let qty6 = $(item6).find("[name*='[accepted_qty]']").val() || 0;
            let rate6 = $(item6).find("[name*='[rate]']").val() || 0;
            let itemValue6 =  (Number(qty6) * Number (rate6)) || 0;
            let itemDisc6 = Number($(item6).find("[name*='[discount_amount]']").val()) || 0;
            let itemHeaderDisc6 = Number($(item6).find("[name*='[discount_amount_header]']").val()) || 0;
            let itemTax6 = 0;
            if($(item6).find("[name*='[t_value]']").length) {
                $(item6).find("[name*='[t_value]']").each(function(indexing, iteming){
                    itemTax6+= Number($(iteming).val()) || 0;
                })
            }
            if(totalHeaderExp) {
                each_net_value = itemValue6 - itemDisc6 - itemHeaderDisc6 + itemTax6;
                exp_header_amnt_item = each_net_value / total_net_total * totalHeaderExp;
                $(item6).find("[name*='[exp_amount_header]']").val(exp_header_amnt_item.toFixed(2));
            } else {
                $(item6).find("[name*='[exp_amount_header]']").val(exp_header_amnt_item.toFixed(2));
            }
        });

    });
}

/*itemDiscountSubmit*/
$(document).on('click', '.itemDiscountSubmit', (e) => {
    $("#itemRowDiscountModal").modal('hide');
});

/*Delete deleteItemDiscountRow*/
$(document).on('click', '.deleteItemDiscountRow', (e) => {
    let rowCount = e.target.closest('a').getAttribute('data-row-count') || 0;
    let index = e.target.closest('a').getAttribute('data-index') || 0;
    let id = e.target.closest('a').getAttribute('data-id') || 0;
    let total = 0.00;
    if(!id) {
        e.target.closest('tr').remove();
        $("#eachRowDiscountTable .display_discount_row").each(function(index,item){
            let disAmount = $(item).find("[name='itemDiscountAmount']").val();
            total += Number(disAmount);
        });
        $("#disItemFooter #total").text(total.toFixed(2));
    }
});

// Total Store Quantity
function storeQtyTotal()
{
    let storeQty = 0;
    $(".add-more-store-locations [name*='erp_store_qty']").each(function(index, item) {
        storeQty = storeQty + Number($(item).val());
    });
    $("#storeLocationFooter #store-qty").attr('qty', storeQty);
    $("#storeLocationFooter #store-qty").text(Number(storeQty));
}

/*Open item remark modal*/
$(document).on('click', '.addRemarkBtn', (e) => {
    let rowCount = e.target.closest('div').getAttribute('data-row-count');
    $("#itemRemarkModal #row_count").val(rowCount);
    let remarkValue = $("#itemTable #row_"+rowCount).find("[name*='remark']");

    if(!remarkValue.length) {
        $("#itemRemarkModal textarea").val('');
    } else {
        $("#itemRemarkModal textarea").val(remarkValue.val());
    }
    $("#itemRemarkModal").modal('show');
});

/*Submit item remark modal*/
$(document).on('click', '.itemRemarkSubmit', (e) => {
    let rowCount = $("#itemRemarkModal #row_count").val();
    let remarkValue = $("#itemTable #row_" + rowCount).find("[name*='remark']");
    let textValue = $("#itemRemarkModal").find("textarea").val();

    // Validate if remark length exceeds 250 characters
    if (textValue.length > 250) {
        Swal.fire({
            title: 'Error!',
            text: 'Remark cannot be longer than 250 characters.',
            icon: 'error'
        });
        return false;  // Stop further execution if validation fails
    }

    if (!remarkValue.length) {
        let rowHidden = `<input type="hidden" value="${textValue}" name="components[${rowCount}][remark]" />`;
        $("#itemTable #row_" + rowCount).find('.addRemarkBtn').after(rowHidden);
    } else {
        $("#itemTable #row_" + rowCount).find("[name*='remark']").val(textValue);
    }

    $("#itemRemarkModal").modal('hide');
});

/*Edit mode table calculation filled*/
if($("#itemTable .mrntableselectexcel tr").length) {
    setTimeout(()=> {
       $("[name*='component_item_name[1]']").trigger('focus');
       $("[name*='component_item_name[1]']").trigger('blur');
        setTableCalculation();
    },100);
}

function summaryDisTotal()
{
    let total = 0.00;
    $(".display_summary_discount_row [name*='[d_amnt]']").each(function(index, item) {
        total += Number($(item).val()) || 0;
    });
    $("#disSummaryFooter #total").attr('amount', total.toFixed(2)).text(total.toFixed(2));
}

/*Open summary discount modal*/
$(document).on('click', '.summaryDisBtn', (e) => {
    e.stopPropagation();
    if(!Number($(`[name*="[basic_value]"]`).val())) {
        Swal.fire({
            title: 'Error!',
            text: 'Please first enter qty & rate in table.',
            icon: 'error'
        });
        return false;
    }
    $("#summaryDiscountModal").modal('show');
    initializeAutocompleteTED("new_dis_name_select", "new_discount_id", "new_dis_name", "sales_module_discount", "new_dis_perc");
});

/*summaryDiscountSubmit*/
$(document).on('click', '.summaryDiscountSubmit', (e) => {
    $("#summaryDiscountModal").modal('hide');
    return false;
});

/*delete summary discount row*/
$(document).on('click', '.deleteSummaryDiscountRow', (e) => {
    let trId = $(e.target).closest('tr').find('[name*="[d_id]"]').val();
    if(!trId) {
        $(e.target).closest('tr').remove();
        summaryDisTotal();
        setTableCalculation();
        if (!Number($("#disSummaryFooter #total").attr('amount'))) {
            $("#f_header_discount_hidden").addClass('d-none');
        } else {
            $("#f_header_discount_hidden").removeClass('d-none');
        }
    }
});

/*Open summary expen modal*/
$(document).on('click', '.summaryExpBtn', (e) => {
    e.stopPropagation();
    if(!Number($(`[name*="[basic_value]"]`).val())) {
        Swal.fire({
            title: 'Error!',
            text: 'Please first enter qty & rate in table.',
            icon: 'error'
        });
        return false;
    }
    $("#summaryExpenModal").modal('show');
    initializeAutocompleteTED("new_exp_name_select", "new_exp_id", "new_exp_name", "sales_module_expense", "new_exp_perc");
});

/*delete summary exp row*/
$(document).on('click', '.deleteExpRow', (e) => {
    $("#new_exp_name_select").val("");
    $("#new_exp_id").val("");
    $("#new_exp_value").val("");
    $("#new_exp_tax_amount").val("");
    $("#total_amount_after_tax").val("");
    $("#new_exp_tax_breakup").val("");
    let trId = $(e.target).closest('tr').find('[name*="[e_id]"]').val();
    if(!trId) {
        $(e.target).closest('tr').remove();
        summaryExpTotal();
    }
});

// summaryExpSubmit
$(document).on('click', '.summaryExpSubmit', (e) => {
    $("#summaryExpenModal").modal('hide');
    // return false;
    setTableCalculation();
});


$(document).on('input change', '#itemTable input', (e) => {
    setTableCalculation();
});

/*Check filled all basic detail*/
function checkBasicFilledDetail()
{
    let filled = false;
    let bookId = $("#book_id").val() || '';
    let documentNumber = $("#document_number").val() || '';
    let documentDate = $("[name='document_date']").val() || '';
    if(bookId && documentNumber && documentDate) {
        filled = true;
    }
    return filled;
}

/*Check filled vendor detail*/
function checkVendorFilledDetail()
{
    let filled = false;
    let vName = $("#vendor_name").val();
    let vCurrency = $("[name='currency_id']").val();
    let vPaymentTerm = $("[name='payment_term_id']").val();
    let shippingId = $("#shipping_id").val();
    let billingId = $("#billing_id").val();
    if(vName && vCurrency && vPaymentTerm && shippingId && billingId) {
        filled = true;
    }
    return filled;
}

/*Check filled component*/
function checkComponentRowExist()
{
    let filled = false;
    let rowCount = $("#itemTable [id*='row_']").length;
    if(rowCount) {
        filled = true;
    }
    return filled;
}

$('#attribute').on('hidden.bs.modal', function () {
   let rowCount = $("[id*=row_].trselected").attr('data-index');
   let qty = $(`[name="components[${rowCount}][qty]"]`).val() || '';
     $(`[name="components[${rowCount}][qty]"]`).val(qty).focus();
});

/*Vendor change update field*/
$(document).on('blur', '#vendor_name', (e) => {
    if(!e.target.value) {
        $("#vendor_id").val('');
        $("#vendor_code").val('');
        $("#shipping_id").val('');
        $("#billing_id").val('');
        $("[name='currency_id']").val('').trigger('change');
        $("[name='payment_term_id']").val('').trigger('change');
        $(".shipping_detail").text('-');
        $(".billing_detail").text('-');
    }
});

$(document).on('input', '.qty-input', function() {
    const maxAmount = Number($(this).attr('maxAmount')) || 0;
    if (Number(this.value) > maxAmount) {
        Swal.fire({
            title: 'Error!',
            text: 'Purchase indent quantity is not available.',
            icon: 'error',
        });
        this.value = maxAmount;
    }
});

//Disable form submit on enter button
document.querySelector("form").addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
        event.preventDefault();  // Prevent form submission
    }
});
$("input[type='text']").on("keydown", function(event) {
    if (event.key === "Enter") {
        event.preventDefault();  // Prevent form submission
    }
});
$("input[type='number']").on("keydown", function(event) {
    if (event.key === "Enter") {
        event.preventDefault();  // Prevent form submission
    }
});

/*Add New Summary Discount*/
$(document).on('click', '#add_new_item_dis', (e) => {
    e.preventDefault();
    let rowCount = $("#disItemFooter #row_count").val();
    const new_item_dis_name = $("#new_item_dis_name").val() || '';
    const new_item_dis_id = $("#new_item_discount_id").val() || '';
    const new_item_dis_perc = (Number($("#new_item_dis_perc").val()) || 0).toFixed(2);
    const new_item_dis_value = (Number($("#new_item_dis_value").val()) || 0).toFixed(2);
    let item_dis = 0;
    $(`.display_discount_row`).find('[name*="[item_d_amnt]"]').each(function(index,item) {
        item_dis+=parseFloat($(item).val() || 0);
    });
    let _total_head_dis_all = item_dis +  parseFloat(new_item_dis_value);
    let totalCost = parseFloat($(`[name*='components[${rowCount}][basic_value]']`).val()) || 0;
    if(_total_head_dis_all > totalCost) {
        Swal.fire({
            title: 'Error!',
            text: 'You can not give total discount more then total cost.',
            icon: 'error',
        });
        return false;
    }

    if (!new_item_dis_name || (!new_item_dis_perc && !new_item_dis_value)) return;
    const tbl_row_count = $("#eachRowDiscountTable .display_discount_row").length + 1;
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
    </tr>`
    if(!$(".display_discount_row").length) {
        $("#eachRowDiscountTable #disItemFooter").before(tr);
    } else {
        $(".display_discount_row:last").after(tr);
    }
    $("#new_item_dis_name").val('');
    $("#new_item_discount_id").val('');
    $("#new_item_dis_perc").val('').prop('readonly',false);
    $("#new_item_dis_value").val('').prop('readonly',false);
    let total_head_dis = 0;
    $("[name*='[item_d_amnt]']").each(function(index,item) {
        total_head_dis+=Number($(item).val());
    });
    $("#disItemFooter #total").text(total_head_dis.toFixed(2));
    $(`[id*='row_${rowCount}']`).find("[name*='[discounts]'").remove();

    let hiddenDis = '';
    let totalAmnt = 0;
    $(".display_discount_row").each(function(index,item) {
        let id = $(item).find('[name*="[item_d_id]"]').val();
        let ted_id = $(item).find('[name*="[ted_d_id]"]').val();
        let name = $(item).find('[name*="[item_d_name]"]').val();
        let perc = $(item).find('[name*="[item_d_perc]"]').val();
        let amnt = $(item).find('[name*="[item_d_amnt]"]').val();
        totalAmnt+=Number(amnt);
        hiddenDis+= `<input type="hidden" value="${id}" name="components[${rowCount}][discounts][${index+1}][id]">
        <input type="hidden" value="${ted_id}" name="components[${rowCount}][discounts][${index+1}][ted_id]">
        <input type="hidden" value="${name}" name="components[${rowCount}][discounts][${index+1}][dis_name]">
        <input type="hidden" value="${perc}" name="components[${rowCount}][discounts][${index+1}][dis_perc]">
        <input type="hidden" value="${amnt}" name="components[${rowCount}][discounts][${index+1}][dis_amount]">`;
    });
    $(`[name*="components[${rowCount}][discount_amount]"]`).val(totalAmnt);
    $(`[name*="components[${rowCount}][discount_amount]"]`).after(hiddenDis);
    setTableCalculation();
});

/*Header discount perc change*/
$(document).on('keyup', '#new_item_dis_perc', (e) => {
    e.preventDefault();
    let rowCount = $("#disItemFooter #row_count").val();
    let input = $(e.target);
    input.prop('readonly',false);
    let value = parseFloat(input.val());
    let percAmount = 0;
    let totalCost = 0
    if (isNaN(value)) {
        input.val('');
        value = 0;
    }
    if (value < 0) {
        value = 0;
        input.val(value);
    } else if (value > 100) {
        let _total_perc = 0;
        $(`.display_discount_row`).find('[name*="[item_d_perc]"]').each(function(index,item) {
            _total_perc+=parseFloat($(item).val() || 0);
        });
        value = 100 - _total_perc;
        input.val(value);
        setTimeout(() => {
            Swal.fire({
                title: 'Error!',
                text: 'You cannot add more than 100%.',
                icon: 'error',
            });
        },0);
    }

    totalCost = parseFloat($(`[name*='components[${rowCount}][basic_value]']`).val()) || 0;

    percAmount = parseFloat((totalCost * value) / 100);
    $("#new_item_dis_value").prop('readonly', Boolean(percAmount)).val(percAmount ? percAmount.toFixed(2) : '');
    return false;
});

/*Header discount value change*/
$(document).on('keyup', '#new_item_dis_value', (e) => {
    e.preventDefault();
    let input = $(e.target);
    input.prop('readonly',false);
    let value = parseFloat(input.val());
    $("#new_item_dis_perc").prop('readonly', Boolean(value)).val('');
    return false;
});

/*Add New Summary Discount*/
$(document).on('click', '#add_new_head_dis', (e) => {
    e.preventDefault();
    const new_dis_name = $("#new_dis_name").val() || '';
    const new_dis_id = $("#new_discount_id").val() || '';
    const new_dis_perc = (Number($("#new_dis_perc").val()) || 0).toFixed(2);
    const new_dis_value = (Number($("#new_dis_value").val()) || 0).toFixed(2);

    let _total_head_dis = 0;
    $("[name*='[d_amnt]']").each(function(index,item) {
        _total_head_dis+=Number($(item).val());
    });

    let totalCost = parseFloat($("#TotalEachRowAmount").attr('amount')) || 0;
    let _total_head_dis_all = _total_head_dis + Number(new_dis_value);
    if(_total_head_dis_all > totalCost) {
        Swal.fire({
            title: 'Error!',
            text: 'You can not give total discount more then total cost.',
            icon: 'error',
        });
        return false;
    }

    if (!new_dis_name || (!new_dis_perc && !new_dis_value)) return;
    const tbl_row_count = $("#summaryDiscountTable .display_summary_discount_row").length + 1;
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
    </tr>`
    if(!$(".display_summary_discount_row").length) {
        $("#summaryDiscountTable #disSummaryFooter").before(tr);
    } else {
        $(".display_summary_discount_row:last").after(tr);
    }
    $("#new_dis_name").val('');
    $("#new_discount_id").val('');
    $("#new_dis_perc").val('').prop('readonly',false);
    $("#new_dis_value").val('').prop('readonly',false);
    let total_head_dis = 0;
    $("[name*='[d_amnt]']").each(function(index,item) {
        total_head_dis+=Number($(item).val());
    });
    if(total_head_dis) {
        $('#f_header_discount_hidden').removeClass('d-none');
    } else {
        $('#f_header_discount_hidden').addClass('d-none');
    }
    $("#disSummaryFooter #total").text(total_head_dis.toFixed(2));
    setTableCalculation();
});

/*Header discount perc change*/
$(document).on('keyup', '#new_dis_perc', (e) => {
    e.preventDefault();
    let input = $(e.target);
    input.prop('readonly',false);
    let value = parseFloat(input.val());
    let percAmount = 0;
    let totalCost = 0
    if (isNaN(value)) {
        input.val('');
        value = 0;
    }
    if (value < 0) {
        value = 0;
        input.val(value);
    } else if (value > 100) {
        let _total_perc = 0;
        $("[name*='[d_perc]']").each(function(index,item) {
            _total_perc+=Number($(item).val());
        });
        value = 100 - _total_perc;
        input.val(value);
        Swal.fire({
            title: 'Error!',
            text: 'You cannot add more than 100%.',
            icon: 'error',
        });
    }
    totalCost = parseFloat($("#TotalEachRowAmount").attr('amount')) || 0;
    percAmount = parseFloat((totalCost * value) / 100);
    $("#new_dis_value").prop('readonly', Boolean(percAmount)).val(percAmount ? percAmount.toFixed(2) : '');
    return false;
});

/*Header discount value change*/
$(document).on('keyup', '#new_dis_value', (e) => {
    e.preventDefault();
    let input = $(e.target);
    input.prop('readonly',false);
    let value = parseFloat(input.val());
    $("#new_dis_perc").prop('readonly', Boolean(value)).val('');
    return false;
});

/*Add New Summary Discount*/
$(document).on("click", "#add_new_head_exp", (e) => {
    e.preventDefault();
    // Delay execution to ensure input values are up-to-date
    setTimeout(() => {
        let new_exp_id = $("#new_exp_id").val() || "";
        let new_exp_name = $("#new_exp_name").val() || "";
        let new_exp_value = (Number($("#new_exp_value").val()) || 0).toFixed(2);
        let hsn_id = $("#new_exp_id").attr("data-hsn-id") || 0;
        let locationId = $("[name='header_store_id']").val() || "";

        let new_exp_tax_amount = (
            Number($("#new_exp_tax_amount").val()) || 0
        ).toFixed(2);
        let total_amount_after_tax = (
            Number($("#total_amount_after_tax").val()) || 0
        ).toFixed(2);

        let tax_breakup = $("#new_exp_tax_breakup").val();
        if (!new_exp_name || !new_exp_tax_amount) return;

        let tbl_row_count =
            $("#summaryExpTable .display_summary_exp_row").length + 1;
        let tr = `
            <tr class="display_summary_exp_row">
                <td>${tbl_row_count}</td>
                <td>${new_exp_name}
                    <input type="hidden" name="exp_summary[${tbl_row_count}][hsn_id]" value="${hsn_id}">
                    <input type="hidden" name="exp_summary[${tbl_row_count}][ted_e_id]" value="${new_exp_id}">
                    <input type="hidden" name="exp_summary[${tbl_row_count}][e_id]" value="">
                    <input type="hidden" name="exp_summary[${tbl_row_count}][e_name]" value="${new_exp_name}">
                    <input type="hidden" name="exp_summary[${tbl_row_count}][location_id]" value="${locationId}">
                </td>
                <td class="text-end">${new_exp_value}
                    <input type="hidden" name="exp_summary[${tbl_row_count}][e_amnt]" value="${new_exp_value}">
                </td>
                <td class="text-end">${new_exp_tax_amount}
                    <input type="hidden" name="exp_summary[${tbl_row_count}][tax_amount]" value="${new_exp_tax_amount}">
                </td>
                <td class="text-end">${total_amount_after_tax}
                    <input type="hidden" name="exp_summary[${tbl_row_count}][total]" value="${total_amount_after_tax}">
                </td>
                <td class="text-start">
                    ${renderBreakupHtml(JSON.parse(tax_breakup))}
                    <input type="hidden" name="exp_summary[${tbl_row_count}][tax_breakup]" value='${tax_breakup}'>
                </td>
                <td>
                    <a href="javascript:;" class="text-danger deleteExpRow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </a>
                </td>
            </tr>
        `;

        if (!$(".display_summary_exp_row").length) {
            $("#summaryExpTable #expSummaryFooter").before(tr);
        } else {
            $(".display_summary_exp_row:last").after(tr);
        }
        $("#new_exp_name_select").val("");
        $("#new_exp_id").val("");
        $("#new_exp_name").val("");
        $("#new_exp_perc").val("").prop("readonly", false);
        $("#new_exp_value").val("").prop("readonly", false);
        let total_head_exp = 0;
        $("[name*='[e_amnt]']").each(function (index, item) {
            total_head_exp += Number($(item).val());
        });

        $("#expSummaryFooter #total").text(total_head_exp.toFixed(2));

        summaryExpTotal();
        setTableCalculation();
    }, 500);
});

/*Header discount perc change*/
$(document).on('keyup', '#new_exp_perc', (e) => {
    e.preventDefault();
    let input = $(e.target);
    input.prop('readonly',false);
    let value = parseFloat(input.val());
    let percAmount = 0;
    let totalCost = 0
    if (isNaN(value)) {
        input.val('');
        value = 0;
    }
    if (value < 0) {
        value = 0;
        input.val(value);
    } else if (value > 100) {
        let _total_perc = 0;
        $("[name*='[e_perc]']").each(function(index,item) {
            _total_perc+=Number($(item).val());
        });
        value = 100 - _total_perc;
        input.val(value);
        Swal.fire({
            title: 'Error!',
            text: 'You cannot add more than 100%.',
            icon: 'error',
        });
    }
    // totalCost = parseFloat($("#TotalEachRowAmount").attr('amount')) || 0;
    totalCost = parseFloat($("#f_total_after_tax").attr('amount')) || 0;
    percAmount = parseFloat((totalCost * value) / 100);
    $("#new_exp_value").prop('readonly', Boolean(percAmount)).val(percAmount ? percAmount.toFixed(2) : '');
    return false;
});

/*Header discount value change*/
$(document).on('keyup', '#new_exp_value', (e) => {
    e.preventDefault();
    let input = $(e.target);
    input.prop('readonly',false);
    let value = parseFloat(input.val());
    $("#new_exp_perc").prop('readonly', Boolean(value)).val('');
    return false;
});

/*Qty enabled and disabled*/
function qtyEnabledDisabled() {
    $("tr[id*='row_']").each(function(index,item) {
        let qtyDisabled = false;
        if($(item).find("[name*='[attr_name]']").length) {
            $(item).find("[name*='[attr_name]']").each(function () {
                if ($(this).val().trim() === "") {
                    qtyDisabled = true;
                }
            });
            $(item).find("[name*='[accepted_qty]']").attr('readonly',Boolean(qtyDisabled));
            if(qtyDisabled) {
                $(item).find("[name*='[accepted_qty]']").val('');
            }
        } else {
            $(item).find("[name*='[accepted_qty]']").attr('readonly',false);
        }
    });
}
qtyEnabledDisabled();

setTimeout(() => {
    if($("tr[id*='row_']").length) {
        setTableCalculation();
    }
},0);

function checkDuplicateObjects(inputEle) {
    let items = [];
    $("tr[id*='row_']").each(function(index, item) {
        let itemId = $(item).find("input[name*='[item_id]']").val();
        let attrName = $(item).find("input[name*='[attr_name]']").val();
        let uomId = $(item).find("select[name*='[uom_id]']").val();
        if (itemId && attrName && uomId) {
            let attr = [];
            // Collect attributes
            $(item).find("input[name*='[attr_name]']").each(function(ind, it) {
                const matches = it.name.match(/components\[\d+\]\[attr_group_id\]\[(\d+)\]\[attr_name\]/);
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
        if(hasDuplicateObjects(items)) {
            Swal.fire({
                title: 'Error!',
                text: 'Duplicate item!',
                icon: 'error',
            });
            $(inputEle).val('');
        }
    }
}

function hasDuplicateObjects(array,inputEle) {
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
$(document).on('change', 'select[name*="[uom_id]"]',(e) => {
    let tr = $(e.target).closest('tr');
    getItemCostPrice(tr);
    setTableCalculation();
});

function initAttributeAutocomplete(context = document) {
    $(context).find('.attr-autocomplete').each(function () {
        let $input = $(this);
        $input.autocomplete({
            minLength: 0,
            source: function (request, response) {
                let itemId = $input.closest('tr').find("input[name*='item_id']").val() || '';
                let attrGroupId = $input.data('attr-group-id');
                $.ajax({
                    url: '/search',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        q: request.term,
                        type: "item_attr_value",
                        item_id: itemId,
                        attr_group_id: attrGroupId,
                    },
                    success: function (data) {
                        response($.map(data, function (item) {
                            return {
                                id: item.id,
                                label: item.value,
                                value: item.value
                            };
                        }));
                    },
                    error: function (xhr) {
                        console.error('Error fetching attribute values:', xhr.responseText);
                    }
                });
            },
            select: function (event, ui) {
                const row = $input.closest('tr');
                const rowCount = row.find('[name*="row_count"]').val();
                const attrGroupId = $input.data('attr-group-id');
                $input.val(ui.item.label);
                $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).val(ui.item.id);
                qtyEnabledDisabled();
                setSelectedAttribute(rowCount);
                const itemId = $("#attribute tbody tr").find('[name*="[item_id]"]').val();
                const itemAttributes = [];
                $("#attribute tbody tr").each(function () {
                    const attr_id = $(this).find('[name*="[attribute_id]"]').val();
                    const attr_value = $(this).find('[name*="[attribute_value]"]').val();
                    itemAttributes.push({
                        attr_id: attr_id,
                        attr_value: attr_value
                    });
                });
                return false;
            },
            focus: function (event, ui) {
                event.preventDefault();
            }
        });
        $input.on('focus', function () {
            if (!$(this).val()) {
                $(this).autocomplete("search", "");
            }
        });
        $input.on('input', function () {
            if (!$(this).val()) {
                const row = $input.closest('tr');
                const rowCount = row.find('[name*="row_count"]').val();
                const attrGroupId = $input.data('attr-group-id');
                $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).val('');
                qtyEnabledDisabled();
            }
        });
    });
}

// Auto scroll when row added
function focusAndScrollToLastRowInput(inputSelector = '.comp_item_code', tableSelector = '#itemTable') {
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

function renderBreakupHtml(breakup) {
    if (!breakup || !breakup.length) return "";
    let html = "";
    breakup.forEach((group) => {
        group.taxes.forEach((tax) => {
            html += `${tax.tax_code} (${tax.tax_percent}%) : ${parseFloat(
                tax.tax_amount
            ).toFixed(2)}<br>`;
        });
    });
    return html;
}

/*-------------------------
  Get Tax Params Helper
-------------------------*/
function getTaxParams(el = null) {
    let $row = el ? $(el).closest("tr") : $("tr.active");
    let price = $row.find("[id='new_exp_value']").val();
    let hsn_id = $row.find("[id='new_exp_id']").attr("data-hsn-id") || 0;
    let transactionDate = $("input[name='document_date']").val();
    let locationId = $("[name='header_store_id']").val();

    return {
        hsn_id: hsn_id || 0,
        store_id: locationId || null,
        price: parseFloat(price) || 0,
        from_country: Number($("#country_id").val()) || 0,
        from_state: Number($("#state_id").val()) || 0,
        party_country_id:
            Number($("#party_country_id").val()) ||
            Number($("#hidden_country_id").val()),
        party_state_id:
            Number($("#party_state_id").val()) ||
            Number($("#hidden_state_id").val()),
        transaction_type: $("#transaction_type").val() || "purchase",
        date: "",
    };
}

/*-------------------------
  Ajax Tax Calculation
-------------------------*/
function calculateTaxAndApply(el = null) {
    const params = getTaxParams(el);

    $.ajax({
        url: taxCalUrl,
        method: "GET",
        data: params,
        success: function (response) {
            applyTaxDetails(response, params);
        },
        error: function (xhr) {
            $("#new_exp_name_select").val("");
            $("#new_exp_id").val("");
            $("#new_exp_value").val("");
            $("#new_exp_tax_amount").val("");
            $("#total_amount_after_tax").val("");
            $("#new_exp_tax_breakup").val("");
            console.error("Tax calculation failed:", xhr.responseText);
            Swal.fire(
                "Error!",
                xhr?.responseText?.error ||
                    xhr?.responseText?.message ||
                    xhr?.responseText ||
                    "Tax calculation failed.",
                "error"
            );
        },
    });
}

/*-------------------------
  Apply Tax Details to UI
-------------------------*/
function applyTaxDetails(taxResponse, params) {
    const breakup = taxResponse.group_taxes || [];
    const expenseAmount = parseFloat(taxResponse.price || params.price);
    const totalTax = parseFloat(taxResponse.total_tax || 0);
    const totalAmount = parseFloat(taxResponse.total_amount_after_tax || 0);

    const container = $("#tax_details_container").empty();
    breakup.forEach((group) => {
        group.taxes.forEach((tax) => {
            container.append(`
                <div class="tax-line d-flex justify-content-between">
                    <span>${tax.tax_code} (${tax.tax_percent}%)</span>
                    <span>${parseFloat(tax.tax_amount).toFixed(2)}</span>
                </div>
            `);
        });
    });

    $("#new_exp_value").val(expenseAmount.toFixed(2));
    $("#new_exp_tax_amount").val(totalTax.toFixed(2));
    $("#total_amount_after_tax").val(totalAmount.toFixed(2));
    $("#new_exp_tax_breakup").val(JSON.stringify(breakup));
}

/*-------------------------
  Header Discount Change Event
-------------------------*/
$(document).on("change", "#new_exp_value", function (e) {
    e.preventDefault();
    let $input = $(this);
    let value = parseFloat($input.val()) || 0;

    $input.prop("readonly", false);
    $("#new_exp_perc").prop("readonly", Boolean(value)).val("");

    calculateTaxAndApply(this);

    return false;
});

function summaryExpTotal() {
    let expenseTotal = 0.0;
    let taxTotal = 0.0;
    let grandTotal = 0.0;

    $(".display_summary_exp_row").each(function () {
        let eAmount = parseFloat($(this).find("[name*='e_amnt']").val()) || 0;
        let tAmount =
            parseFloat($(this).find("[name*='tax_amount']").val()) || 0;
        let total = parseFloat($(this).find("[name*='total']").val()) || 0;

        expenseTotal += eAmount;
        taxTotal += tAmount;
        grandTotal += total;
    });

    // Update footer
    $("#expSummaryFooter #expTotal").text(expenseTotal.toFixed(2));
    $("#expSummaryFooter #taxTotal").text(taxTotal.toFixed(2));
    $("#expSummaryFooter #grandTotal").text(grandTotal.toFixed(2));
}

function formatTaxBreakup(breakupJson) {
    let html = "";
    try {
        const breakup = JSON.parse(breakupJson);
        breakup.forEach((group) => {
            if (group.taxes) {
                group.taxes.forEach((tax) => {
                    html += `${tax.tax_code ?? ""} (${
                        tax.tax_percent ?? 0
                    }%) : ${parseFloat(tax.tax_amount ?? 0).toFixed(2)}<br>`;
                });
            }
        });
    } catch (e) {
        console.error("Invalid tax breakup JSON", e);
    }
    return html;
}
