/*Tax Detail Display Start*/
$(document).on('click', '.summaryTaxBtn', (e) => {
    getTaxSummary();
});

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

/*Get tax Summary*/
function getTaxSummary()
{
    let taxSummary = {};
    $("#itemTable [id*='row_']").each(function(index, row) {
        row = $(row);        
        let qty = Number(row.find('[name*="[qty]"]').val());
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
        let amount = Number(summary.totalTaxableAmount).toFixed(); 
        taxSummaryHtml += `<tr>
        <td>${rowCount}</td>
        <td>${summary.taxType}</td>
        <td>${Number(amount).toFixed(2)}</td>
        <td>${summary.taxPerc}%</td>
        <td>${summary.totalTaxValue.toFixed(2)}</td>
        </tr>`;
        rowCount++;
    }
    $('#po_tax_details').html(taxSummaryHtml);
    $("#poTaxDetailModal").modal('show');
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
    let rowCount = e.target.closest('tr').querySelector('[name*="row_count"]').value;
    let attrGroupId = e.target.getAttribute('data-attr-group-id');
    $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).val(e.target.value);
    qtyEnabledDisabled();
    setSelectedAttribute(rowCount);
});

/*Each row addDiscountBtn*/
$(document).on('click', '.addDiscountBtn', (e) => {
    $("#new_item_dis_name_select").val('');
    $("#new_item_discount_id").val('');
    $("#new_item_dis_name").val('');
    $("#new_item_dis_perc").val('').prop('readonly',false);
    $("#new_item_dis_value").val('').prop('readonly',false);
    let rowCount = e.target.closest('button').getAttribute('data-row-count');
    let tr = '';
    let totalAmnt = 0;
    $(`[id="row_${rowCount}"]`).find("[name*='[dis_amount]']").each(function(index,item) {
        let key = index +1;
        let id = $(item).closest('tr').find(`[name*='[${key}][id]']`).val();
        let tedId = $(item).closest('tr').find(`[name*='[${key}][ted_id]']`).val();
        let name = $(item).closest('tr').find(`[name*='[${key}][dis_name]']`).val();
        let perc = $(item).closest('tr').find(`[name*='[${key}][dis_perc]']`).val();
        let amnt = Number($(item).val()).toFixed(2);
        totalAmnt+=Number(amnt);
        let tbl_row_count = index + 1;
         tr += `
        <tr class="display_discount_row">
            <td>${tbl_row_count}</td>
            <td>${name}
                <input type="hidden" value="${tedId}" name="disc_item[${tbl_row_count}][ted_id]">
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
    initializeAutocompleteTED("new_item_dis_name_select", "new_item_discount_id", "new_item_dis_name", "po_module_discount", "new_item_dis_perc");
});

function setTableCalculation() {
    let totalItemValue = 0;
    let totalItemDiscount = 0;
    let totalItemCost = 0;
    let totalHeaderDiscount = 0;
    let totalAfterBothDisc = 0;
    let totalTax = 0;
    let totalAfterTax = 0;
    let totalHeaderExp = 0;
    let grandTotal = 0;
    $("#itemTable [id*='row_']").each(function (index, item) {
        let rowCount = Number($(item).attr('data-index'));
        let qty = $(item).find("[name*='[qty]']").val() || 0;
        let rate = $(item).find("[name*='[rate]']").val() || 0;
        let itemValue = (Number(qty) * Number(rate)) || 0;
        totalItemValue+=itemValue;
        $(item).find("[name*='[item_value]']").val(itemValue.toFixed(2));

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

    $("#f_sub_total").attr('amount',totalItemValue.toFixed(2)).text(totalItemValue.toFixed(2));
    $("#f_total_discount").attr('amount',totalItemDiscount.toFixed(2)).text(totalItemDiscount.toFixed(2));

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
            let oldValue = $(`[name='disc_summary[${index + 1}][d_amnt]']`).val() || 0;
            oldValue = oldValue || eachDiscTypePrice;
            $(`[name="disc_summary[${index + 1}][d_amnt]"]`).closest('td').html(`${eachDiscTypePrice.toFixed(2)}
                <input type="hidden" value="${oldValue.toFixed(2)}" name="disc_summary[${index + 1}][d_amnt]">
            `);
            // $(`[name="disc_summary[${index + 1}][d_amnt]"]`).val(eachDiscTypePrice.toFixed(2));
        });
        disHeaderAmnt += eachDiscTypePrice;
    }
    $("#disSummaryFooter #total").attr('amount', disHeaderAmnt.toFixed(2)).text(disHeaderAmnt.toFixed(2));
    $("#f_header_discount").attr('amount',disHeaderAmnt.toFixed(2)).text(disHeaderAmnt.toFixed(2));
    /*Bind summary header Discount*/

    /*Bind header discount item level*/
    $("#itemTable [id*='row_']").each(function (index, item2) {
        let rowCount2 = Number($(item2).attr('data-index'));
        let qty2 = $(item2).find("[name*='[qty]']").val() || 0;
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
        let qty3 = $(item3).find("[name*='[qty]']").val() || 0;
        let rate3 = $(item3).find("[name*='[rate]']").val() || 0;
        let itemValue3 = (Number(qty3) * Number(rate3)) || 0;
        let itemDisc3 = Number($(item3).find("[name*='[discount_amount]']").val()) || 0;
        let itemHeaderDisc = Number($(item3).find("[name*='[discount_amount_header]']").val()) || 0;
        let itemId = $(item3).find('[name*="[sow_id]"]').val();

        let price = itemValue3 - itemDisc3 - itemHeaderDisc;
        if (price > 0 && itemId) {
            if(isTax) {
                let transactionType = 'purchase';
                let partyCountryId = $("#hidden_country_id").val();
                let partyStateId = $("#hidden_state_id").val();
                let locationId = $("[name='store_id']").val();
                let document_date = $("[name='document_date']").val();
                // Construct the query parameters
                let queryParams = new URLSearchParams({
                    price: price,
                    item_id: itemId,
                    transaction_type: transactionType,
                    party_country_id: partyCountryId,
                    party_state_id: partyStateId,
                    location_id: locationId,
                    rowCount: rowCount3,
                    document_date:document_date
                }).toString();
                console.log(queryParams,actionUrlTax);
                let urlWithParams = `${actionUrlTax}?${queryParams}`;
                let promise = fetch(urlWithParams)
                    .then(response => response.json())
                    .then(data => {
                        $(item3).find("[name*='t_d_id']").remove();
                        $(item3).find("[name*='t_code']").remove();
                        $(item3).find("[name*='applicability_type']").remove();
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
            $(item3).find("[name*='applicability_type']").remove();
            $(item3).find("[name*='t_type']").remove();
            $(item3).find("[name*='t_perc']").remove();
            $(item3).find("[name*='t_value']").remove();
        }
    });

    /*Bind Tax*/

    Promise.all(taxPromises).then(() => {

        $("#itemTable [id*='row_']").each(function (index, item4) {
            let rowCount4 = Number($(item4).attr('data-index'));
            let qty4 = $(item4).find("[name*='[qty]']").val() || 0;
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

        $("#f_taxable_value").attr('amount',totalAfterBothDisc.toFixed(2)).text(totalAfterBothDisc.toFixed(2));
        if (totalAfterBothDisc < 0) {
            $("#f_taxable_value").attr('style', 'color: #dc3545 !important;');
        } else {
            $("#f_taxable_value").attr('style', 'color: inherit;');
        }        
        $("#f_tax").attr('amount',totalTax.toFixed(2)).text(totalTax.toFixed(2));
        
        if (totalTax < 0) {
            // $("#f_tax").attr('style', 'color: #dc3545 !important;');
            let taxAbs = Number($("#f_tax").attr('amount'));
            $("#f_tax").attr('amount',Math.abs(taxAbs)).text(Math.abs(taxAbs));
        } else {
            $("#f_tax").attr('style', 'color: inherit;');
        }  

        $("#f_total_after_tax").attr('amount',totalAfterTax.toFixed(2)).text(totalAfterTax.toFixed(2));

        if (totalAfterTax < 0) {
            $("#f_total_after_tax").attr('style', 'color: #dc3545 !important;');
        } else {
            $("#f_total_after_tax").attr('style', 'color: inherit;');
        } 

        /*Bind header Expenses*/
        if($(".display_summary_exp_row").find("[name*='[e_perc]']").length && totalAfterTax) {
            $(".display_summary_exp_row").find("[name*='[e_perc]']").each(function(index,eachItem) {
                let eachExpTypePrice = 0;
                let hiddenPerc = Number($(`[name="exp_summary[${index+1}][hidden_e_perc]"]`).val()) || 0; 
                let expDiscPerc = hiddenPerc || Number($(eachItem).val());
                if(expDiscPerc) {
                    eachExpTypePrice = (totalAfterTax * expDiscPerc) / 100; 
                    // $(`[name="exp_summary[${index+1}][e_amnt]"]`).val(eachExpTypePrice.toFixed(2));
                    $(`[name="exp_summary[${index+1}][e_amnt]"]`).closest('td').html(`
                    ${eachExpTypePrice.toFixed(2)}
                    <input type="hidden" value="${eachExpTypePrice.toFixed(2)}" name="exp_summary[${index+1}][e_amnt]">
                    `);
                } else {
                    eachExpTypePrice = Number($(`[name="exp_summary[${index+1}][e_amnt]"]`).val()) || 0; 
                }
                totalHeaderExp += eachExpTypePrice;
            });
        } else {
           $(".display_summary_exp_row").find("[name*='[e_perc]']").each(function(index,eachItem) {
                let eachExpTypePrice = 0;
                // let expDiscPerc = Number($(eachItem).val());
                // $(`[name="exp_summary[${index+1}][e_amnt]"]`).val(eachExpTypePrice.toFixed(2));
                $(`[name="exp_summary[${index+1}][e_amnt]"]`).closest('td').html(`
                    ${eachExpTypePrice.toFixed(2)}
                    <input type="hidden" value="${eachExpTypePrice.toFixed(2)}" name="exp_summary[${index+1}][e_amnt]">
                    `);
                totalHeaderExp += eachExpTypePrice;
            });
        }
        $("#expSummaryFooter #total").attr('amount',totalHeaderExp.toFixed(2)).text(totalHeaderExp.toFixed(2));
        $("#f_exp").text(totalHeaderExp.toFixed(2));

        if (totalHeaderExp < 0) {
            $("#f_exp").attr('style', 'color: #dc3545 !important;');
        } else {
            $("#f_exp").attr('style', 'color: inherit;');
        } 

        /*Bind header Expenses*/
        grandTotal = totalAfterTax + totalHeaderExp;
        $("#f_total_after_exp").attr('amount',grandTotal.toFixed(2)).text(grandTotal.toFixed(2));
        
        if (grandTotal < 0) {
            $("#f_total_after_exp").attr('style', 'color: #dc3545 !important;');
        } else {
            $("#f_total_after_exp").attr('style', 'color: inherit;');
        } 

        /*Bind header exp item level*/
        let total_net_total = 0;
        $("#itemTable [id*='row_']").each(function (index, item5) {
            let rowCount5 = Number($(item5).attr('data-index'));
            let qty5 = $(item5).find("[name*='[qty]']").val() || 0;
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
            let qty6 = $(item6).find("[name*='[qty]']").val() || 0;
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
    updateTotalAfterExchangeRate();
}
/*Edit mode table calculation filled*/
if($("#itemTable .mrntableselectexcel tr").length) {
   setTimeout(()=> {
      $("[name*='component_item_name[1]']").trigger('focus');
      $("[name*='component_item_name[1]']").trigger('blur');
      setTableCalculation();
   },100);
}
/*itemDiscountSubmit*/
$(document).on('click', '.itemDiscountSubmit', (e) => {
    $("#itemRowDiscountModal").modal('hide');
});
/*Delete deleteItemDiscountRow*/
$(document).on('click', '.deleteItemDiscountRow', (e) => {
    let rowCount = e.target.closest('a').getAttribute('data-row-count') || 0;
    let id = Number(e.target.closest('a').getAttribute('data-id')) || 0;
    if(!id) {
        e.target.closest('tr').remove();
        let hiddenDis = '';
        let totalAmnt = 0;
        $(".display_discount_row").each(function(index,item) {
            let id = $(item).find('[name*="[item_d_id]"]').val(); 
            let name = $(item).find('[name*="[item_d_name]"]').val();
            let perc = $(item).find('[name*="[item_d_perc]"]').val();
            let amnt = $(item).find('[name*="[item_d_amnt]"]').val();
            totalAmnt+=Number(amnt);
            hiddenDis+= `<input type="hidden" value="${id}" name="components[${rowCount}][discounts][${index+1}][id]">
            <input type="hidden" value="${name}" name="components[${rowCount}][discounts][${index+1}][dis_name]">
            <input type="hidden" value="${perc}" name="components[${rowCount}][discounts][${index+1}][dis_perc]">
            <input type="hidden" value="${amnt}" name="components[${rowCount}][discounts][${index+1}][dis_amount]">`;
        });
        $(`[name*="components[${rowCount}][discount_amount]"]`).val(totalAmnt);
        $(`[id*='row_${rowCount}']`).find("[name*='[discounts]'").remove();
        $(`[name*="components[${rowCount}][discount_amount]"]`).after(hiddenDis);
        $("#disItemFooter #total").attr('amount',totalAmnt).text(totalAmnt.toFixed(2));
    }
    setTableCalculation();
});
// addDeliveryScheduleBtn
$(document).on('click', '.addDeliveryScheduleBtn', (e) => {
    let rowCount = e.target.closest('div').getAttribute('data-row-count');
    let qty = Number($("#itemTable #row_"+rowCount).find("[name*='[qty]']").val());
    if(!qty) {
        Swal.fire({
            title: 'Error!',
            text: 'Please enter quanity then you can add delivery schedule.',
            icon: 'error',
        });
        return false;
    }
    $("#deliveryScheduleModal").find("#row_count").val(rowCount);
    let rowHtml = '';
    let curDate = new Date().toISOString().split('T')[0];
    let minDate = $("[name='document_date']").val() ?? curDate;
    if(!$("#itemTable #row_"+rowCount).find("[name*='[d_qty]']").length) {        
    let rowHtml = `<tr class="display_delivery_row">
                        <td>1</td>
                        <td>
                            <input type="hidden" name="row_count" value="${rowCount}" id="row_count">
                            <input type="number" name="components[${rowCount}][delivery][1][d_qty]" class="form-control mw-100" />
                        </td>
                        <td>
                            <input type="date" min="${minDate}" name="components[${rowCount}][delivery][1][d_date]" value="${minDate}" class="form-control mw-100" /></td>
                        <td>
                        <a data-row-count="${rowCount}" data-index="1" href="javascript:;" class="text-danger deleteItemDeliveryRow"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></a>
                       </td>
                    </tr>`;
    $("#deliveryScheduleModal").find('.display_delivery_row').remove();
    $("#deliveryScheduleModal").find('#deliveryFooter').before(rowHtml);
    } else {
        if($("#itemTable #row_"+rowCount).find("[name*=d_qty]").length) {
            $(".display_delivery_row").remove();
        } else {
            $('.display_delivery_row').not(':first').remove();
            $(".display_delivery_row").find("[name*=d_qty]").val('');
        }
        $("#itemTable #row_"+rowCount).find("[name*=d_qty]").each(function(index,item){
            let id =  $(item).closest('td').find(`[name='components[${rowCount}][delivery][${index+1}][id]']`).val();
            let dQty =  $(item).closest('td').find(`[name='components[${rowCount}][delivery][${index+1}][d_qty]']`).val();
            let dDate =  $(item).closest('td').find(`[name='components[${rowCount}][delivery][${index+1}][d_date]']`).val();
            rowHtml+= `<tr class="display_delivery_row">
                        <td>${index+1}</td>
                        <td>
                            <input type="hidden" name="row_count" value="${rowCount}" id="row_count">
                            <input type="number" value="${dQty}" name="components[${rowCount}][delivery][${index+1}][d_qty]" class="form-control mw-100" />
                        </td>
                        <td>
                            <input type="date" min="${minDate}" name="components[${rowCount}][delivery][${index+1}][d_date]" value="${dDate}" class="form-control mw-100" /></td>
                        <td>
                        <a data-id="${id}" data-row-count="${rowCount}" data-index="${index+1}" href="javascript:;" class="text-danger deleteItemDeliveryRow"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></a>
                       </td>
                    </tr>`;

        });
    }
    $("#deliveryScheduleTable").find('#deliveryFooter').before(rowHtml);
    $("#deliveryScheduleTable").find('#deliveryFooter #total').attr('qty',qty);
    $("#deliveryScheduleModal").modal('show');
    totalScheduleQty();
});

/*Total delivery schedule qty*/
function totalScheduleQty()
{
    let total = 0.00;
    $("#deliveryScheduleTable [name*='[d_qty]']").each(function(index, item) {
        total += Number($(item).val());
    });
    $("#deliveryFooter #total").text(total.toFixed(2));
}

// addTaxItemRow add row
$(document).on('click', '.addTaxItemRow', (e) => {
    let curDate = new Date().toISOString().split('T')[0];
    let minDate = $("[name='document_date']").val() ?? curDate;
    // let rowCount = $('#deliveryScheduleModal .display_delivery_row').find('#row_count').val();
    let rowCount = $("tr.trselected").attr('data-index');
    let qty = 0.00;
    $("#deliveryScheduleTable [name*='[d_qty]']").each(function(index, item) {
        qty = qty + Number($(item).val());
    });
    if(!qty && $("#deliveryScheduleTable [name*='[d_qty]']").length) {
        Swal.fire({
            title: 'Error!',
            text: 'Please enter quanity then you can add new row.',
            icon: 'error',
        });
        return false;
    }
    if(!$("#deliveryScheduleTable [name*='[d_qty]']:last").val() && $("#deliveryScheduleTable [name*='[d_qty]']").length) {
        Swal.fire({
            title: 'Error!',
            text: 'Please enter quanity then you can add new row.',
            icon: 'error',
        });
        return false;
    }
    let itemQty = Number($('#deliveryScheduleModal #deliveryFooter #total').attr('qty'));
    if (qty > itemQty) {
        Swal.fire({
            title: 'Error!',
            text: 'You cannot add more than the available item quantity.',
            icon: 'error',
        });
        return false;
    }
    if(qty != itemQty) {        
        let tblRowCount = $('#deliveryScheduleModal .display_delivery_row').length + 1;
        let rowHtml = `<tr class="display_delivery_row">
                            <td>${tblRowCount}</td>
                            <td>
                                <input type="hidden" name="row_count" value="${rowCount}" id="row_count">
                                <input type="number" name="components[${rowCount}][delivery][${tblRowCount}][d_qty]" class="form-control mw-100" />
                            </td>
                            <td>
                                <input type="date" min="${minDate}" name="components[${rowCount}][delivery][${tblRowCount}][d_date]" value="${minDate}" class="form-control mw-100" /></td>
                            <td>
                            <a data-row-count="${rowCount}" data-index="${tblRowCount}" href="javascript:;" class="text-danger deleteItemDeliveryRow"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></a>
                           </td>
                        </tr>`;
        if($("#deliveryScheduleModal").find('.display_delivery_row:last').length) {
            $("#deliveryScheduleModal").find('.display_delivery_row:last').after(rowHtml);
        } else {
            $("#deliveryScheduleModal").find('#deliveryFooter').before(rowHtml);
        }
    } else {
        Swal.fire({
            title: 'Error!',
            text: 'Qunatity not available.',
            icon: 'error',
        });
        return false;
    }
    totalScheduleQty();
});
/*itemDeliveryScheduleSubmit */
$(document).on('click', '.itemDeliveryScheduleSubmit', (e) => {
    let isValid = true;
    document.querySelectorAll('input[name*="[d_qty]"], input[name*="[d_date]"]').forEach(input => {
        if (!input.value) {
            isValid = false;
            input.classList.add('is-invalid');
            input.focus();
        } else {
            input.classList.remove('is-invalid');
        }
    });
    if (!isValid) {
        e.preventDefault();
        Swal.fire({
            title: 'Error!',
            text: 'Please fill out all required fields.',
            icon: 'error',
        });
        return false;
    }
    let rowCount = $('#deliveryScheduleModal .display_delivery_row').find('#row_count').val();    
    let hiddenHtml = '';
    $("#deliveryScheduleTable .display_delivery_row").each(function(index,item){
        let dQty =  $(item).find("[name*='d_qty']").val();
        let dDate = $(item).find("[name*='d_date']").val();
        hiddenHtml+=`<input type="hidden" value="${dQty}" name="components[${rowCount}][delivery][${index+1}][d_qty]"/>
                     <input type="hidden" value="${dDate}" name="components[${rowCount}][delivery][${index+1}][d_date]" />`;

    });
    $("#itemTable #row_"+rowCount).find("[name*='d_qty']").remove();
    $("#itemTable #row_"+rowCount).find("[name*='d_date']").remove();
    // $("#itemTable #row_"+rowCount).find("[name*='t_value']").remove();
   $("#itemTable #row_"+rowCount).find(".addDeliveryScheduleBtn").before(hiddenHtml);
   $("#deliveryScheduleModal").modal('hide');
});

/*Remove delivery row*/
$(document).on('click', '.deleteItemDeliveryRow', (e) => {
    let id = $(e.target).closest('a').attr('data-id') || 0;
    let rowIndex = e.target.getAttribute('data-row-index');
    let rowCount = e.target.getAttribute('data-row-count');
    if (!Number(id)) {        
        $(e.target).closest('tr').remove();
        setTimeout(() => {
            let rowCount = $(".display_delivery_row").find('#row_count').val();
            $('.display_delivery_row').each(function(index, item) {
                let a = `components[${rowCount}][delivery][${index+1}][d_qty]`;
                let b = `components[${rowCount}][delivery][${index+1}][d_date]`;
                $(item).find("[name*='[d_qty]']").prop('name', a);
                $(item).find("[name*='[d_date]']").prop('name', b);
                $(item).find("td:first").text(index+1);
            });
            $(`[name*='components[${rowCount}][delivery][${rowIndex}]']`).remove();
            $(".display_delivery_row").find('#row_count').val(rowCount);
            totalScheduleQty();
        },0);
    }
});
/*Delivery qty on input*/
$(document).on('change input', '.display_delivery_row [name*="d_qty"]', (e) => {
    let itemQty = Number($('#deliveryScheduleModal #deliveryFooter #total').attr('qty'));
    let inputQty = 0;
    $('.display_delivery_row [name*="d_qty"]').each(function(index, item) {
        inputQty = inputQty + Number($(item).val());
        let remainingQty = itemQty - (inputQty - Number($(e.target).val()));
        if (Number($(e.target).val()) > remainingQty) {
            Swal.fire({
                title: 'Error!',
                text: 'You cannot add more than the available item quantity.',
                icon: 'error',
            });
            $(e.target).val(remainingQty);
        }
    });
    totalScheduleQty();
});
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
    let remarkValue = $("#itemTable #row_"+rowCount).find("[name*='remark']");
     let textValue = $("#itemRemarkModal").find("textarea").val();
    if(!remarkValue.length) {
        rowHidden = `<input type="hidden" value="${textValue}" name="components[${rowCount}][remark]" />`;
        $("#itemTable #row_"+rowCount).find('.addRemarkBtn').after(rowHidden);
        
    } else{
        $("#itemTable #row_"+rowCount).find("[name*='remark']").val(textValue);
    }
    $("#itemRemarkModal").modal('hide');
});
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
    if(!Number($(`[name*="[item_value]"]`).val())) {
        Swal.fire({
            title: 'Error!',
            text: 'Please first enter qty & rate in table.',
            icon: 'error'
        });
        return false;
    }
    $("#summaryDiscountModal").modal('show');
    initializeAutocompleteTED("new_dis_name_select", "new_discount_id", "new_dis_name", "po_module_discount", "new_dis_perc");
    return false;
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
    if(!Number($(`[name*="[item_value]"]`).val())) {
        Swal.fire({
            title: 'Error!',
            text: 'Please first enter qty & rate in table.',
            icon: 'error'
        });
        return false;
    }
    $("#summaryExpenModal").modal('show');
    initializeAutocompleteTED("new_exp_name_select", "new_exp_id", "new_exp_name", "po_module_expense", "new_exp_perc");
    return false;
});
/*delete summary exp row*/
$(document).on('click', '.deleteExpRow', (e) => {
    let trId = $(e.target).closest('tr').find('[name*="[e_id]"]').val();
    if(!trId) {
        $(e.target).closest('tr').remove();
        summaryExpTotal();
    }
});
// summaryExpSubmit
$(document).on('click', '.summaryExpSubmit', (e) => {
    $("#summaryExpenModal").modal('hide');
    // setTableCalculation();
    return false;
});
function summaryExpTotal()
{
    let total = 0.00;
    $(".display_summary_exp_row [name*='e_amnt']").each(function(index, item) {
        total = total + Number($(item).val());
    });
    $("#expSummaryFooter #total").attr('amount', total);
    $("#expSummaryFooter #total").text(total.toFixed(2));
}
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
    // let referenceNumber = $("[name='reference_number']").val() || '';
    if(bookId && documentNumber && documentDate/* && referenceNumber*/) {
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
    let shippingId = $("#vendor_address_id").val();
    let billingId = $("#billing_address_id").val();
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
// $('input, select, textarea').on('input change blur', function() {
//     sectionEnabledAndDisabled();
// });
// sectionEnabledAndDisabled();

// function sectionEnabledAndDisabled() {
//     // Check if basic details are filled
//     if (!checkBasicFilledDetail()) {
//         // Disable vendor and item sections and block click events
//         $('#vendor_section :input').prop('disabled', true);
//         $('#vendor_section').on('click', function(e) {
//             e.preventDefault();
//             e.stopPropagation();
//         });
        
//         $('#item_section :input').prop('disabled', true);
//         $('#item_section').on('click', function(e) {
//             e.preventDefault();
//             e.stopPropagation();
//         });
//     } else {
//         $('#vendor_section :input').prop('disabled', false);
//         $('#vendor_section').off('click');        
//         if (!checkVendorFilledDetail()) {
//             $('#item_section :input').prop('disabled', true);
//             $('#item_section').on('click', function(e) {
//                 e.preventDefault();
//                 e.stopPropagation();
//             });
//         } else {
//             $('#item_section :input').prop('disabled', false);
//             $('#item_section').off('click');
//         }
//     }
// }

$('#attribute').on('hidden.bs.modal', function () {
   let rowCount = $("[id*=row_].trselected").attr('data-index');
    if ($(`[name="components[${rowCount}][qty]"]`).is('[readonly]')) {
        $(`[name="components[${rowCount}][rate]"]`).trigger('focus');
    } else {
        $(`[name="components[${rowCount}][qty]"]`).trigger('focus');
    }
});
/*Vendor change update field*/
$(document).on('blur', '#vendor_name', (e) => {
    if(!e.target.value) {
        $("#vendor_id").val('');
        $("#vendor_code").val('');
        $("#vendor_address_id").val('');
        $("#delivery_address_id").val('');
        $("#billing_address_id").val('');
        $("#hidden_state_id").val('');
        $("#hidden_country_id").val('');
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
            text: 'Po is more than indent qty.',
            icon: 'error',
        });
        this.value = maxAmount;
    }
});
//Disable form submit on enter button
document.querySelector("form").addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
        event.preventDefault();
    }
});
$("input[type='text']").on("keydown", function(event) {
    if (event.key === "Enter") {
        event.preventDefault();
    }
});
$("input[type='number']").on("keydown", function(event) {
    if (event.key === "Enter") {
        event.preventDefault();
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
    let totalCost = parseFloat($(`[name*='components[${rowCount}][item_value]']`).val()) || 0;
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
    $("#new_item_dis_name_select").val('');  
    $("#new_item_discount_id").val('');  
    $("#new_item_dis_name").val('');  
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
        let tedId = $(item).find('[name*="[ted_d_id]"]').val(); 
        let name = $(item).find('[name*="[item_d_name]"]').val();
        let perc = $(item).find('[name*="[item_d_perc]"]').val();
        let amnt = $(item).find('[name*="[item_d_amnt]"]').val();
        totalAmnt+=Number(amnt);
        hiddenDis+= `<input type="hidden" value="${id}" name="components[${rowCount}][discounts][${index+1}][id]">
        <input type="hidden" value="${tedId}" name="components[${rowCount}][discounts][${index+1}][ted_id]">
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
    totalCost = parseFloat($(`[name*='components[${rowCount}][item_value]']`).val()) || 0;
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
    const new_dis_id = $("#new_discount_id").val() || '';
    const new_dis_name = $("#new_dis_name").val() || '';
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
            <input type="hidden" value="${new_dis_id}" name="disc_summary[${tbl_row_count}][ted_d_id]">
            <input type="hidden" value="" name="disc_summary[${tbl_row_count}][d_id]">
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
    $("#new_discount_id").val('');  
    $("#new_dis_name_select").val('');  
    $("#new_dis_name").val('');  
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
$(document).on('click', '#add_new_head_exp', (e) => {
    e.preventDefault();
    const new_exp_id = $("#new_exp_id").val() || '';
    const new_exp_name = $("#new_exp_name").val() || '';
    const new_exp_perc = (Number($("#new_exp_perc").val()) || 0).toFixed(2);
    const new_exp_value = (Number($("#new_exp_value").val()) || 0).toFixed(2);
    let _total_head_exp = 0;
    $("[name*='[e_amnt]']").each(function(index,item) {
        _total_head_exp+=Number($(item).val());
    });
    let totalCost = parseFloat($("#f_total_after_tax").attr('amount')) || 0;
    let _total_head_exp_all = _total_head_exp + Number(new_exp_value);
    if(_total_head_exp_all > totalCost) {
        Swal.fire({
            title: 'Error!',
            text: 'You can not give total exp more then after tax value.',
            icon: 'error',
        });
        return false;
    }
    if (!new_exp_name || (!new_exp_perc && !new_exp_value)) return;
    const tbl_row_count = $("#summaryExpTable .display_summary_exp_row").length + 1;
    const tr = `
    <tr class="display_summary_exp_row">
        <td>${tbl_row_count}</td>
        <td>${new_exp_name}
            <input type="hidden" value="${new_exp_id}" name="exp_summary[${tbl_row_count}][ted_e_id]">
            <input type="hidden" value="" name="exp_summary[${tbl_row_count}][e_id]">
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
    </tr>`
    if(!$(".display_summary_exp_row").length) {
        $("#summaryExpTable #expSummaryFooter").before(tr);
    } else {
        $(".display_summary_exp_row:last").after(tr);
    }
    $("#new_exp_name_select").val('');  
    $("#new_exp_id").val('');  
    $("#new_exp_name").val('');  
    $("#new_exp_perc").val('').prop('readonly',false);  
    $("#new_exp_value").val('').prop('readonly',false);
    let total_head_exp = 0;
    $("[name*='[e_amnt]']").each(function(index,item) {
        total_head_exp+=Number($(item).val());
    });
    $("#expSummaryFooter #total").text(total_head_exp.toFixed(2));
    setTableCalculation();  
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
            $(item).find("[name*='[qty]']").attr('readonly',Boolean(qtyDisabled));
            if(qtyDisabled) {
                $(item).find("[name*='[qty]']").val('');
            }
        } else {
            $(item).find("[name*='[qty]']").attr('readonly',false);
        }
    });
}
qtyEnabledDisabled();
setTimeout(() => {
    if($("tr[id*='row_']").length) {
        setTableCalculation();
    }
},0);
$(document).on('blur','[name*="component_item_name"]',(e) => {
    if(!e.target.value) {
        $(e.target).closest('tr').find('[name*="[item_name]"]').val('');
        $(e.target).closest('tr').find('[name*="[item_id]"]').val('');
    }
});
$(document).on('keyup', "input[name*='[qty]']", function (e) {
    validateItems(e.target, false);
});
function validateItems(inputEle, itemChange = false) {
    let items = [];
    $("tr[id*='row_']").each(function (index, item) {
        let itemId = $(item).find("input[name*='[item_id]']").val();
        let uomId = $(item).find("select[name*='[uom_id]']").val();
        if (itemId && uomId) {
            let attr = [];
            $(item).find("input[name*='[attr_name]']").each(function (ind, it) {
                const matches = it.name.match(/components\[\d+\]\[attr_group_id\]\[(\d+)\]\[attr_name\]/);
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
            title: 'Error!',
            text: 'Duplicate item!',
            icon: 'error',
        });
        $(inputEle).val('');
        if(itemChange) {
            $(inputEle).closest('tr').find("input[name*='[item_name]']").val('');
            $(inputEle).closest('tr').find("[name*='[uom_id]']").empty();
        }
    }
}
function hasDuplicateObjects(arr) {
    let seen = new Set();
    return arr.some(obj => {
        let key = JSON.stringify(obj);
        if (seen.has(key)) {
            return true;
        }
        seen.add(key);
        return false;
    });
}
// UOM on change bind rate
function handleRowChange(tr) {
    setTableCalculation();
}
// Debounced handler for select/input changes
let debounceTimer;
$(document).on('input', 'select[name*="[uom_id]"], input[name*="[qty]"]', function(e){
    clearTimeout(debounceTimer);
    const tr = $(e.target).closest('tr');
    debounceTimer = setTimeout(() => {
        handleRowChange(tr);
    }, 300);
});
// Handle attribute button click
$('.submitAttributeBtn').on('click', function(e) {
    let currentTr = $(e.target).closest('tr');
    let row = $('#attribute tbody tr');
    let rowCount = row.find('input[name^="row_count"]').val();
    let tr = $('#row_'+rowCount);
    console.log(tr);
    handleRowChange(tr);
});
$(document).on('change', "select[name='store_id']", (e) => {
    let vendorName = $("#vendor_name").attr("data-name");
    let vendorId = $("#vendor_id").val() || '';
    if(vendorId) {
        const item = { label: vendorName, value: vendorName, id: vendorId };
        $('#vendor_name')
            .val(item.label)
            .data('ui-autocomplete')
            ._trigger('select', null, { item: item });
        $("#vendor_name").val(vendorId).trigger('change');
    }
});
function getLocation(locationId = '')
{
    let actionUrl = getLocationUrl+'?location_id='+locationId;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 200) {
                let options = '';
                data.data.locations.forEach(function(location) {
                    let selected = location.id == locationId ? 'selected' : '';
                    options += `<option value="${location.id}" ${selected}>${location.store_name}</option>`;
                });
                // $("[name='store_id']").html(options).trigger('change');
                $("[name='store_id']").html(options);
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                });
            }
        });
    });
}
/*Vendor drop down*/
function initializeAutocompleteVendor(selector, type) {
    let store_id = $("[name='store_id']").val() || '';
    $(selector).autocomplete({
        minLength: 0,
        source: function(request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type:'vendor_list',
                    store_id: store_id,
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: item.company_name,
                            code: item.vendor_code,
                            // locations_count : item.locations_count,
                            is_store_mapped : item.is_store_mapped
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        },
        select: function(event, ui) {
            if(!ui.item.is_store_mapped) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Sub location is not mapped to this vendor',
                    icon: 'error',
                });
                $(this).val('');
                clearVendorData();
                return false;
            }
            var itemId = ui.item.id;
            
            vendorOnChange(itemId);
            $(".editAddressBtn").removeClass('d-none');
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                clearVendorData();
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
            clearVendorData();
        }
    }).on("input", function () {
        if ($(this).val().trim() === "") {
            $(this).removeData("selected");
            clearVendorData();
        }
    });
}
initializeAutocompleteVendor("#vendor_name");
function clearVendorData() 
{
    $("#vendor_name").val('');
    $("#vendor_id").val('');
    $("#vendor_code").val('');
    $("#hidden_state_id").val('');
    $("#hidden_country_id").val('');
    $("select[name='currency_id']").empty().append('<option value="">Select</option>');
    $("select[name='payment_term_id']").empty().append('<option value="">Select</option>');
    $(".vendor_address").text('-');
    $(".billing_address").text('-');
    $(".delivery_address").text('-');
    $("#vendor_address_id").val('');
    $("#billing_address_id").val('');
    $("#delivery_address_id").val('');
    $(".editAddressBtn").addClass('d-none');
    $("#exchange_rate").val('');
}
// Vendor on chanhge
function vendorOnChange(vendorId) {
    let store_id = $("[name='store_id']").val() || '';
    let actionUrl = `${getAddressOnVendorChangeUrl}?id=${vendorId}&store_id=${store_id}`;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.data?.currency_exchange?.status == false) {
                clearVendorData();
                Swal.fire({
                    title: 'Error!',
                    text: data.data?.currency_exchange.message,
                    icon: 'error',
                });
                return false;
            }                    
            if(data.status == 200) {
                $("#vendor_name").val(data?.data?.vendor?.company_name);
                $("#vendor_id").val(data?.data?.vendor?.id);
                $("#vendor_code").val(data?.data?.vendor?.vendor_code);
                let curOption = `<option value="${data?.data?.currency?.id}">${data?.data?.currency?.name}</option>`;
                let termOption = `<option value="${data?.data?.paymentTerm?.id}">${data?.data?.paymentTerm?.name}</option>`;
                $('[name="currency_id"]').empty().append(curOption);
                $('[name="payment_term_id"]').empty().append(termOption);
                $("#delivery_address_id").val(data?.data?.location_address?.id);
                $(".delivery_address").text(data?.data?.location_address?.display_address);
                $("#vendor_address_id").val(data?.data?.vendor_address?.id);
                $("#billing_address_id").val(data?.data?.location_address?.id);
                $("#hidden_state_id").val(data?.data?.vendor_address?.state.id);
                $("#hidden_country_id").val(data?.data?.vendor_address.country?.id);
                $(".vendor_address").text(data?.data?.vendor_address?.display_address);
                $(".billing_address").text(data?.data?.location_address?.display_address);
                const expRate = data?.data?.currency_exchange?.data;
                if (expRate) {
                    const isDifferentCurrency = expRate.party_currency_id !== expRate.org_currency_id;
                    $("#exchange_rate")
                        .toggleClass('disabled-input', !isDifferentCurrency)
                        .val(expRate.org_currency_exg_rate);
                    $("#exchangeDiv").toggleClass('d-none', !isDifferentCurrency);
                }
                // if(!$("#itemTable tbody").find('tr[id*="row_"]').length) {
                //     $(".prSelect").trigger('click');
                // }
            } else {
                if(data.data.error_message) {
                    clearVendorData();
                    Swal.fire({
                        title: 'Error!',
                        text: data?.data?.error_message || '',
                        icon: 'error',
                    });
                    return false;
                }
            }
        });
    });
}
// Change rate bind
function updateTotalAfterExchangeRate() {
    const gt = Number($("#f_total_after_exp").attr('amount')) || 0;
    const er = Number($("#exchange_rate").val()) || 0;
    const total = gt * er;
    $("#f_total_after_exp_rate").text(total.toFixed(2));
}
$("#exchange_rate").on('input change', updateTotalAfterExchangeRate);

// Get PWO Code
function getIndents() 
{
    let selectedPiIds = localStorage.getItem('selectedPiIds') ?? '[]';
    selectedPiIds = JSON.parse(selectedPiIds);
    selectedPiIds = encodeURIComponent(JSON.stringify(selectedPiIds));
    let document_date = $("[name='document_date']").val() || '';
    let header_book_id = $("#book_id").val() || '';
    let series_id = $("#pwo_book_id_qt_val").val() || '';
    let document_number = $("#pwo_document_no_input_qt").val() || '';
    let store_id = $("#store_id").val() || '';
    let so_book_id = $("#so_book_id_qt_val").val() || '';
    let so_doc_number = $("#so_document_no_input_qt").val() || '';
    let item_search = $("#item_name_search").val();
    let fullUrl = `${getPwoUrl}?series_id=${encodeURIComponent(series_id)}&document_number=${encodeURIComponent(document_number)}&header_book_id=${encodeURIComponent(header_book_id)}&store_id=${encodeURIComponent(store_id)}&selected_pi_ids=${selectedPiIds}&document_date=${document_date}&item_search=${item_search}&so_book_id=${so_book_id}&so_doc_number=${so_doc_number}`;
    fetch(fullUrl).then(response => {
        return response.json().then(data => {
            $(".po-order-detail #prDataTable").empty().append(data.data.pis);
            $('.select2').select2({
                dropdownParent: $('#prModal')
            });
        });
    });
}
$(document).on('keyup', '#item_name_search', (e) => {
    getIndents();
});
$(document).on('keyup', '#pwo_document_no_input_qt', (e) => {
    getIndents();
});
$(document).on('keyup', '#so_document_no_input_qt', (e) => {
    getIndents();
});
$(document).on('click', '.clearPiFilter', (e) => {
    $("#pwo_book_code_input_qt").val('');
    $("#pwo_book_id_qt_val").val('');
    $("#pwo_document_no_input_qt").val('');
    $("#so_book_code_input_qt").val('');
    $("#so_book_id_qt_val").val('');
    $("#so_document_no_input_qt").val('');
    $("#item_name_search").val('');
    getIndents();
});

/*Open Pr model*/
$(document).on('click', '.prSelect', (e) => {
    let vendor = $("#vendor_name");
    if(!vendor.val()) {
        Swal.fire({
            title: 'Error!',
            text: "Please select first vendor.",
            icon: 'error',
        });
        return false;
    }
    $("#prModal").modal('show');
    openPurchaseRequest();
    getIndents();
});

/*searchPiBtn*/
$(document).on('click', '.searchPiBtn', (e) => {
    getIndents();
});

function openPurchaseRequest()
{
    // initializeAutocompleteQt("customer_code_input_qt", "customer_id_qt_val", "customer", "customer_code", "company_name");
    initializeAutocompleteQt("pwo_book_code_input_qt", "pwo_book_id_qt_val", "book_pwo", "book_code", "");
    initializeAutocompleteQt("so_book_code_input_qt", "so_book_id_qt_val", "document_book", "book_code", "");
}

function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "") 
{
    $("#" + selector).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: typeVal,
                    vendor_id : $("#vendor_id_qt_val").val(),
                    header_book_id : $("#book_id").val(),
                    store_id : $("#store_id_po").val() || '',
                    service_alias : typeVal == 'document_book' ? soServiceAlias : '',
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: `${item[labelKey1]}${labelKey2 ? (item[labelKey2] ? '-' + item[labelKey2] : '') : ''}`,
                            code: item[labelKey1] || '', 
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        },
        appendTo : '#prModal',
        minLength: 0,
        select: function(event, ui) {
            var $input = $(this);
            $input.val(ui.item.label);
            $("#" + selectorSibling).val(ui.item.id);
            getIndents();
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                $("#" + selectorSibling).val("");
                getIndents();
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $("#" + selectorSibling).val("");
            getIndents();
            $(this).autocomplete("search", "");
        }
    }).blur(function() {
        if (this.value === "") {
            $("#" + selectorSibling).val("");
            getIndents();
        }
    })
}
window.onload = function () {
    localStorage.removeItem('selectedPiIds');
};
$(document).on("autocompletechange autocompleteselect", "#store_po", function (event, ui) {
    let storeId = ui?.item?.id || '';
    initializeAutocompleteQt("sub_store_po", "sub_store_id_po", "sub_store", "name", "");
});
function initializeAutocompleteTED(selector, idSelector, nameSelector, type, percentageVal) {
    let modalId = '#'+$("#" + selector).closest('.modal').attr('id');
    $("#" + selector).autocomplete({
        source: function(request, response) {
            let ids = [];
            $('.modal.show').find("tbody tr").each(function(index,item){
            let tedId = $(item).find("input[name*='ted_']").val();
            if(tedId) {
                ids.push(tedId);
            }
            });
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type:type,
                    ids: JSON.stringify(ids)
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: `${item.name}`,
                            percentage: `${item.percentage}`,
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        appendTo : modalId,
        select: function(event, ui) {
            var $input = $(this);
            var itemName = ui.item.label;
            var itemId = ui.item.id;
            var itemPercentage = ui.item.percentage;
            $input.val(itemName);
            $("#" + idSelector).val(itemId);
            $("#" + nameSelector).val(itemName);
            $("#" + percentageVal).val(itemPercentage).trigger('keyup');
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                $("#" + idSelector).val("");
                $("#" + nameSelector).val("");
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    });
} 

// Item component
function initAutoForItem(selector, type) {
    $(selector).autocomplete({
        minLength: 0,
        source: function(request, response) {
            let selectedAllItemIds = [];
            $("#itemTable tbody [id*='row_']").each(function(index,item) {
                if(Number($(item).find('[name*="[item_id]"]').val())) {
                selectedAllItemIds.push(Number($(item).find('[name*="[item_id]"]').val()));
            }
        });
            $.ajax({
            url: '/search',
            method: 'GET',
            dataType: 'json',
            data: {
                q: request.term,
                type:'header_item',
                selectedAllItemIds : JSON.stringify(selectedAllItemIds)
            },
            success: function(data) {
                response($.map(data, function(item) {
                    return {
                        id: item.id,
                        label: `${item.item_name} (${item.item_code})`,
                        code: item.item_code || '', 
                        item_id: item.id,
                        item_name:item.item_name,
                        uom_name:item.uom?.name,
                        uom_id:item.uom_id,
                        alternate_u_o_ms:item.alternate_u_o_ms,
                        is_attr:item.item_attributes_count,
                    };
                }));
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
            }
        });
        },
        select: function(event, ui) {
        let $input = $(this);
        let itemCode = ui.item.code;
        let itemName = ui.item.value;
        let itemN = ui.item.item_name;
        let itemId = ui.item.item_id;
        checkBomJobWork(itemId,$input);
        let uomId = ui.item.uom_id;
        let uomName = ui.item.uom_name;
        $input.attr('data-name', itemName);
        $input.attr('data-code', itemCode);
        $input.attr('data-id', itemId);
        $input.closest('tr').find('[name*="[item_id]"]').val(itemId);
        $input.closest('tr').find('[name*=item_code]').val(itemCode);
        $input.closest('tr').find('[name*=item_name]').val(itemN);
        $input.val(itemCode);
        let uomOption = `<option value=${uomId}>${uomName}</option>`;
        if(ui.item?.alternate_u_o_ms) {
            for(let alterItem of ui.item.alternate_u_o_ms) {
            uomOption += `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
            }
        }
        $input.closest('tr').find('[name*=uom_id]').empty().append(uomOption);
        $input.closest('tr').find("input[name*='attr_group_id']").remove();
        setTimeout(() => {
            if(ui.item.is_attr) {
                $input.closest('tr').find('.attributeBtn').trigger('click');
            } else {
                $input.closest('tr').find('.attributeBtn').trigger('click');
                $input.closest('tr').find('[name*="[qty]"]').val('').focus();
            }
        }, 100);

        setTableCalculation();
        validateItems($input, true);
        return false;
    },
    change: function(event, ui) {
        if (!ui.item) {
            $(this).val("");
                // $('#itemId').val('');
            $(this).attr('data-name', '');
            $(this).attr('data-code', '');
            $(this).closest('tr').find("input[name*='[rate]']").val('');
        }
    }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    }).on("input", function () {
        if ($(this).val().trim() === "") {
            $(this).removeData("selected");
            $(this).closest('tr').find("input[name*='component_item_name']").val('');
            $(this).closest('tr').find("input[name*='item_name']").val('');
            $(this).closest('tr').find("td[id*='itemAttribute_']").html(defautAttrBtn);
            $(this).closest('tr').find("input[name*='item_id']").val('');
            $(this).closest('tr').find("input[name*='item_code']").val('');
            $(this).closest('tr').find("input[name*='attr_name']").remove();
        }
    });
}
// Scope Of Work component
function initAutoForSow(selector, type) {
    $(selector).autocomplete({
        minLength: 0,
        source: function(request, response) {
            let selectedAllItemIds = [];
            $("#itemTable tbody [id*='row_']").each(function(index,item) {
                if(Number($(item).find('[name*="[sow]"]').val())) {
                selectedAllItemIds.push(Number($(item).find('[name*="[sow]"]').val()));
            }
        });
            $.ajax({
            url: '/search',
            method: 'GET',
            dataType: 'json',
            data: {
                q: request.term,
                type:'service_item_list',
                selectedAllItemIds : JSON.stringify(selectedAllItemIds),
                vendor_id : $("#vendor_id").val()
            },
            success: function(data) {
                response($.map(data, function(item) {
                    return {
                        id: item.id,
                        label: `${item.item_name} (${item.item_code})`,
                        code: item.item_code || '', 
                        item_id: item.id,
                        item_name:item.item_name,
                        item_price:item.price
                    };
                }));
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
            }
        });
        },
        select: function(event, ui) {
        let $input = $(this);
        let itemCode = ui.item.code;
        let itemName = ui.item.value;
        let itemN = ui.item.item_name;
        let itemId = ui.item.item_id;
        let itemRate = ui.item.item_price;
        $input.val(itemCode);
        $input.closest('tr').find('[name*="[sow_id]"]').val(itemId);
        $input.closest('tr').find('[name*="[sow]"]').val(itemName);
        $input.closest('tr').find('[name*="[rate]"]').val(itemRate);
        
        
        setTableCalculation();
        validateItems($input, true);
        return false;
    },
    change: function(event, ui) {
        if (!ui.item) {
            $(this).val("");
                // $('#itemId').val('');
            $(this).closest('tr').find('input[name*="[sow_id]"]').val('');
            $(this).closest('tr').find('input[name*="[sow]"]').val('');
            $(this).closest('tr').find("input[name*='[rate]']").val('');
        }
    }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    }).on("input", function () {
        if ($(this).val().trim() === "") {
            $(this).removeData("selected");
            $(this).closest('tr').find("input[name*='[sow_id]']").val('');
            $(this).closest('tr').find("input[name*='[sow]']").val('');
            $(this).closest('tr').find("input[name*='[rate]']").val('');
        }
    });
}

function checkBomJobWork(itemId = null,$input) {
    fetch(checkBomJobUrl+'?item_id='+itemId).then((response) => {
        return response.json().then(data => {
            if(!data?.data?.is_bom) {
                Swal.fire({
                    title: 'Error!',
                    text: data?.message || '',
                    icon: 'error',
                });
                $input.closest('tr').find("input[name*='component_item_name']").val('');
                $input.closest('tr').find("input[name*='item_name']").val('');
                $input.closest('tr').find("td[id*='itemAttribute_']").html(defautAttrBtn);
                $input.closest('tr').find("input[name*='item_id']").val('');
                $input.closest('tr').find("input[name*='item_code']").val('');
                $input.closest('tr').find("input[name*='inventoty_uom_id']").val();
                $input.closest('tr').find("input[name*='uom_id']").emopty();
                $input.closest('tr').find("input[name*='attr_name']").remove();
                return false;
            }
        });
    });
}
/*Add New Row*/
$(document).on('click','#addNewItemBtn', (e) => {
    if(!checkBasicFilledDetail()) {
        Swal.fire({
            title: 'Error!',
            text: 'Please fill all the header details first',
            icon: 'error',
        });
        return false;
    }
    if(!checkVendorFilledDetail()) {
        Swal.fire({
            title: 'Error!',
            text: 'Please fill all the header details first',
            icon: 'error',
        });
        return false;
    }
    let rowsLength = $("#itemTable > tbody > tr").length;
    /*Check last tr data shoud be required*/
    let lastRow = $('#itemTable .mrntableselectexcel tr:last');
    let lastTrObj = {
    item_id : "",
    attr_require : true,
    row_length : lastRow.length
    };

    if(lastRow.length == 0) {
    lastTrObj.attr_require = false;
    lastTrObj.item_id = "0";
    }

    if(lastRow.length > 0) {
    let item_id = lastRow.find("[name*='[item_id]']").val();
    if(lastRow.find("[name*='attr_name']").length) {
        var emptyElements = lastRow.find("[name*='attr_name']").filter(function() {
            return $(this).val().trim() === '';
        });
        attr_require = emptyElements?.length ? true : false;
    } else {
        attr_require = true;
    }

    lastTrObj = {
        item_id : item_id,
        attr_require : attr_require,
        row_length : lastRow.length
    };

    if($("tr[id*='row_']:last").find("[name*='[attr_group_id]']").length == 0 && item_id) {
        lastTrObj.attr_require = false;
    }
    }
    let actionUrl = newNewRowUrl
        + '?count=' + rowsLength
        + '&component_item=' + encodeURIComponent(JSON.stringify(lastTrObj));
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                if (rowsLength) {
                    $("#itemTable > tbody > tr:last").after(data.data.html);
                } else {
                    $("#itemTable > tbody").html(data.data.html);
                }
                initAutoForItem(".comp_item_code");
                initAutoForSow(".ser_item_code");
                $("select[name='currency_id']").prop('disabled', true);
                $("select[name='payment_term_id']").prop('disabled', true);
                $("#vendor_name").prop('readonly',true);
                $(".editAddressBtn").addClass('d-none');
            } else if(data.status == 422) {
            Swal.fire({
                title: 'Error!',
                text: data.message || 'An unexpected error occurred.',
                icon: 'error',
            });
        } else {
            console.log("Someting went wrong!");
        }
    });
    });
});

let selectedPwoId = null;
$(document).on('change', '.po-order-detail > tbody .pi_item_checkbox', function (e) {
    const $checkbox = $(this);
    const currentPwoId = $checkbox.data('pwo-id');
    if ($checkbox.is(':checked')) {
        if (!selectedPwoId) {
            selectedPwoId = currentPwoId;
            $('.po-order-detail > tbody .pi_item_checkbox').each(function () {
                if ($(this).data('pwo-id') === selectedPwoId) {
                    $(this).prop('checked', true);
                } else {
                    $(this).prop('checked', false);
                }
            });
        } else if (selectedPwoId !== currentPwoId) {
            $checkbox.prop('checked', false);
            // alert('You can only select rows with the same PWO ID.');
        } else {
            $checkbox.prop('checked', true);
        }
    } else {
        const anyChecked = $('.po-order-detail > tbody .pi_item_checkbox:checked').length > 0;
        if (!anyChecked) {
            selectedPwoId = null;
        }
    }
});
$(document).on('change', '.po-order-detail > thead .form-check-input', function () {
    if ($(this).is(':checked')) {
        const firstCheckbox = $('.po-order-detail > tbody .pi_item_checkbox').first();
        selectedPwoId = firstCheckbox.data('pwo-id');
        $('.po-order-detail > tbody .pi_item_checkbox').each(function () {
            if ($(this).data('pwo-id') === selectedPwoId) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
            }
        });
    } else {
        $('.po-order-detail > tbody .pi_item_checkbox').prop('checked', false);
        selectedPwoId = null;
    }
});

function getSelectedPiIDS()
{
    let ids = [];
    $('.pi_item_checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}
function getFirstNonEmptyPwoId() {
    const inputs = document.querySelectorAll('input[name^="components"][name$="[pwo_id]"]');
    for (const input of inputs) {
        if (input.value.trim() !== "") {
            return input.value;
        }
    }
    return null;
}
// PWO Process
$(document).on('click', '.prProcess', (e) => {
    let ids = getSelectedPiIDS();
    if (!ids.length) {
        $("#prModal").modal('hide');
        Swal.fire({
            title: 'Error!',
            text: 'Please select at least one PWO Item',
            icon: 'error',
        });
        return false;
    }
    if(getFirstNonEmptyPwoId()) {
        let a = getFirstNonEmptyPwoId();
        let b = $('.pi_item_checkbox:checked').first().data('pwo-id');
        if(a!=b) {
            Swal.fire({
                title: 'Error!',
                text: 'We can process in one job order diff PWO.',
                icon: 'error',
            });
            return false;
        }
    }
    ids = JSON.stringify(ids);
    let current_row_count = $("#itemTable tbody tr[id*='row_']").length;
    let actionUrl = pwoProcessUrl
    + '?ids=' + encodeURIComponent(ids)
    + '&current_row_count='+current_row_count;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 200) {
                let newIds = getSelectedPiIDS();
                let existingIds = localStorage.getItem('selectedPiIds');
                if (existingIds) {
                    existingIds = JSON.parse(existingIds);
                    const mergedIds = Array.from(new Set([...existingIds, ...newIds]));
                    localStorage.setItem('selectedPiIds', JSON.stringify(mergedIds));
                } else {
                    localStorage.setItem('selectedPiIds', JSON.stringify(newIds));
                }
                
                let existingIdsUpdate = JSON.parse(localStorage.getItem('selectedPiIds'));
                if ($("#itemTable .mrntableselectexcel").find("tr[id*='row_']").length) {
                    $("#itemTable .mrntableselectexcel tr[id*='row_']:last").after(data.data.pos);
                } else {
                    $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                }
                setTimeout(() => {
                    $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                        let currentIndex = index + 1;
                        setAttributesUIHelper(currentIndex,"#itemTable");
                    });
                },100);
                initAutoForItem(".comp_item_code");
                initAutoForSow(".ser_item_code");
                $("#prModal").modal('hide');
                $("select[name='currency_id']").prop('disabled', true);
                $("select[name='payment_term_id']").prop('disabled', true);
                $("#vendor_name").prop('readonly',true);
                $(".editAddressBtn").addClass('d-none');
                setTimeout(() => {
                    setTableCalculation();
                },500);
            }
            if(data.status == 422) {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                });
                return false;
            }
        });
    });
});

/*Delete Row*/
$(document).on('click','#deleteBtn', (e) => {
    let itemIds = [];
    let editItemIds = [];
    $('#itemTable > tbody .form-check-input').each(function() {
        if ($(this).is(":checked")) {
            if($(this).attr('data-id')) {
               editItemIds.push($(this).attr('data-id'));
            } else {
               itemIds.push($(this).val());
            }
        }
    });

    if (itemIds.length) {
        itemIds.forEach(function(item,index) {
            let piItemHiddenId = $(`#row_${item}`).find("input[name*='[pwo_so_mapping_id]']").val();
            if(piItemHiddenId) {
                let idsToRemove = piItemHiddenId.split(',');
                let selectedPiIds = localStorage.getItem('selectedPiIds');
                if(selectedPiIds) {
                    selectedPiIds = JSON.parse(selectedPiIds);
                    let updatedIds = selectedPiIds.filter(id => !idsToRemove.includes(id));
                    localStorage.setItem('selectedPiIds', JSON.stringify(updatedIds));
                }
            }
            $(`#row_${item}`).remove();
        });
    }

    if(editItemIds.length == 0 && itemIds.length == 0) {
      alert("Please first add & select row item.");
    }

    if (editItemIds.length) {
      $("#deleteComponentModal").find("#deleteConfirm").attr('data-ids',JSON.stringify(editItemIds));
      $("#deleteComponentModal").modal('show');
    }
    if(!$("tr[id*='row_']").length) {
        $("#itemTable > thead .form-check-input").prop('checked',false);
        $(".prSelect").prop('disabled',false);
        $("select[name='currency_id']").prop('disabled', false);
        $("select[name='payment_term_id']").prop('disabled', false);
        $(".editAddressBtn").removeClass('d-none');
        $("#vendor_name").prop('readonly',false);
        getLocation();
    }
    setTableCalculation();
});

/*Delete server side rows*/
if($("#deleteConfirm").length) {
    $(document).on('click','#deleteConfirm', (e) => {
        let ids = e.target.getAttribute('data-ids');
        ids = JSON.parse(ids);
        localStorage.setItem('deletedPiItemIds', JSON.stringify(ids));
        $("#deleteComponentModal").modal('hide');
        if(ids.length) {
            ids.forEach((id,index) => {
                let piItemHiddenId = $(`.form-check-input[data-id='${id}']`).closest('tr').find("input[name*='[pwo_so_mapping_id]']").val();
                if(piItemHiddenId) {
                    let idsToRemove = piItemHiddenId.split(',');
                    let selectedPiIds = localStorage.getItem('selectedPiIds');
                    if(selectedPiIds) {
                        selectedPiIds = JSON.parse(selectedPiIds);
                        let updatedIds = selectedPiIds.filter(id => !idsToRemove.includes(id));
                        localStorage.setItem('selectedPiIds', JSON.stringify(updatedIds));
                    }
                }
                $(`.form-check-input[data-id='${id}']`).closest('tr').remove();
            });
        }
        setTableCalculation();
        if(!$("#itemTable [id*=row_]").length) {
            $("th .form-check-input").prop('checked',false);
            $(".prSelect").prop('disabled',false);
            $("select[name='currency_id']").prop('disabled', false);
            $("select[name='payment_term_id']").prop('disabled', false);
            $(".editAddressBtn").removeClass('d-none');
            $("#vendor_name").prop('readonly',false); 
            getLocation();
            let vendorName = $("#vendor_name").attr("data-name");
            let vendorId = $("#vendor_id").val() || '';
            if(vendorId) {
                const item = { label: vendorName, value: vendorName, id: vendorId };
                $('#vendor_name')
                    .val(item.label)
                    .data('ui-autocomplete')
                    ._trigger('select', null, { item: item });
                $("#vendor_name").val(vendorId).trigger('change');
            }
    
        }
    });
}