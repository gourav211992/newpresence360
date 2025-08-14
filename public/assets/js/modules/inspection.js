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
    $('#mrn_tax_details').html(taxSummaryHtml);
    $("#mrnTaxDetailModal").modal('show');
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
    let closestTr = e.target.closest('tr');
    let rowCount = e.target.closest('tr').querySelector('[name*="row_count"]').value;
    let attrGroupId = e.target.getAttribute('data-attr-group-id');
    $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).val(e.target.value);
    // closestTr = $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).closest('tr');
    // getItemDetail(closestTr);
    qtyEnabledDisabled();
});

// Check Negative Values
let oldValue;
$(document).on('focus', '.checkNegativeVal', function(e) {
    oldValue = e.target.value;  // Store the old value when the field gains focus
});

/*Order qty on change*/
$(document).on('change', "[name*='order_qty']", async function (e) {
    const $tr = $(e.target).closest('tr');
    const $qtyInput = $(e.target);
    const orderQty = parseFloat($qtyInput.val()) || 0;

    const $poQtyInput = $tr.find(".mrn_qty");
    const poQty = parseFloat($poQtyInput.val()) || 0;

    const $acceptedQtyInput = $tr.find("[name*='accepted_qty']");
    const $rejectedQtyInput = $tr.find("[name*='rejected_qty']");
    const dataIndex = $tr.attr('data-index');
    const itemId = $tr.find("[name*='item_id']").val();

    $qtyInput.val(orderQty.toFixed(2));
    checkDuplicateObjects($qtyInput);

    if (orderQty <= 0) {
        Swal.fire({ title: 'Error!', text: 'Inspection Qty. cannot be zero.', icon: 'error' });
        $qtyInput.val(poQty.toFixed(2));
        return;
    }

    const getVal = (selector) => {
        const el = $tr.find(selector);
        return el.length ? el.val() : '';
    };

    const data = {};
    const safeSet = (key, val) => { if (val) data[key] = val; };

    safeSet('item_id', itemId);
    safeSet('mrn_header_id', getVal("[name*='[mrn_header_id]']"));
    safeSet('mrn_detail_id', getVal("[name*='[mrn_detail_id]']"));
    safeSet('inspection_dtl_id', getVal("[name*='[inspection_dtl_id]']"));
    safeSet('qty', orderQty.toFixed(2));
    safeSet('type', currentProcessType);

    try {
        const response = await fetch(qtyChangeUrl + '?' + new URLSearchParams(data).toString());
        const result = await response.json();

        const resultQty = parseFloat(result.order_qty) || 0;
        const finalQty = resultQty.toFixed(2);
        $qtyInput.val(finalQty);

        let acceptedQty = resultQty;
        let rejectedQty = (resultQty - acceptedQty);

        $acceptedQtyInput.val(acceptedQty.toFixed(2));
        $acceptedQtyInput.trigger('change');
        $rejectedQtyInput.val(rejectedQty.toFixed(2));

        if (result.status !== 200 && result.message) {
            Swal.fire({ title: 'Error!', text: result.message, icon: 'error' });
            return false;
        }

    } catch (err) {
        console.error(err);
        Swal.fire({ title: 'Error!', text: 'Quantity validation failed.', icon: 'error' });
    }
});

/*accepted qty on change*/
$(document).on('change', "[name*='accepted_qty']", function (e) {
    edit = true; // Set edit to true when order_qty changes
    const $tr = $(e.target).closest('tr');
    const $acceptedQtyInput = $tr.find("[name*='accepted_qty']");
    const $orderQtyInput = $tr.find("[name*='order_qty']");
    const $rejectedQtyInput = $tr.find("[name*='rejected_qty']");
    const dataIndex = $tr.attr('data-index');
    const itemId = $tr.find("[name*='item_id']").val();

    let acceptedQty = parseFloat($acceptedQtyInput.val()) || 0;
    const orderQty = parseFloat($orderQtyInput.val()) || 0;

    if (acceptedQty > orderQty) {
        Swal.fire({ title: 'Error!', text: 'Accepted Qty. cannot be greater than Inspection Qty.', icon: 'error' });
        acceptedQty = orderQty;
    }

    let rejectedQty = orderQty - acceptedQty;

    $acceptedQtyInput.val(acceptedQty.toFixed(2));
    $rejectedQtyInput.val(rejectedQty.toFixed(2));

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

    },100);
}

/*Check filled all basic detail*/
function checkBasicFilledDetail()
{
    let filled = false;
    let bookId = $("#book_id").val() || '';
    let documentNumber = $("#document_number").val() || '';
    let documentDate = $("[name='document_date']").val() || '';
    let storeId = $("[name='header_store_id']").val() || '';
    let subStoreId = $("[name='sub_store_id']").val() || '';
    if(bookId && documentNumber && documentDate && storeId && subStoreId) {
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
   // $(`[id*=row_${rowCount}]`).find('.addSectionItemBtn').trigger('click');
   $(`[name="components[${rowCount}][qty]"]`).trigger('focus');
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
            $(item).find("[name*='[order_qty]']").attr('readonly',Boolean(qtyDisabled));
            if(qtyDisabled) {
                $(item).find("[name*='[order_qty]']").val('');
            }
        } else {
            $(item).find("[name*='[order_qty]']").attr('readonly',false);
        }
    });
}

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
    getItemDetail(tr);

});

// 1. Attach change event
$(document).on('change', '.header_store_id', function () {
    const selectedStoreId = $(this).val();
    if (selectedStoreId) {
        getSubStores(selectedStoreId);
        getRejectedSubStores(selectedStoreId);

    }
});

// 2. On page load: trigger if already selected
const selectedStoreId = $('.header_store_id').val();
if (selectedStoreId) {
    getSubStores(selectedStoreId);
    getRejectedSubStores(selectedStoreId);
}

// Get SUb Stores
function getSubStores(storeLocationId)
{
    const storeId = storeLocationId;
    $.ajax({
        url: "/sub-stores/store-wise",
        method: 'GET',
        dataType: 'json',
        data: {
            store_id : storeId,
            sub_type: 'main'
        },
        success: function(data) {
            console.log('data', data);

            if((data.status == 200) && data.data.length) {
                let options = '';
                data.data.forEach(function(location) {
                    options+= `<option value="${location.id}">${location.name}</option>`;
                });
                $(".sub_store").html(options);
            } else {
                // No data found, hide subStore header and cell
                $(".sub_store").empty();
            }
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error!',
                text: xhr?.responseJSON?.message,
                icon: 'error',
            });
        }
    });
}

// Get Rejected Sub Stores
function getRejectedSubStores(storeLocationId)
{
    const storeId = storeLocationId;
    $.ajax({
        url: "/sub-stores/store-wise",
        method: 'GET',
        dataType: 'json',
        data: {
            store_id : storeId,
            sub_type: 'rejected'
        },
        success: function(data) {
            console.log('data', data);

            if((data.status == 200) && data.data.length) {
                let options = '<option value="">select</option>';
                data.data.forEach(function(location) {
                    options+= `<option value="${location.id}">${location.name}</option>`;
                });
                $(".rejected_sub_store").html(options);
            } else {
                // No data found, hide Rejected Sub Store header and cell
                $(".rejected_sub_store").empty();
            }
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error!',
                text: xhr?.responseJSON?.message,
                icon: 'error',
            });
        }
    });
}

// // Inspection Checklist Btn
// $(document).on('click', '.inspectionChecklistBtn', function () {
//     const rawMasterData = $(this).attr('data-checklist');
//     const rawExistingData = $(this).attr('data-existing-checklist');
//     const rowCount = $(this).data('row-count');
//     $('.checklist-modal-body').data('row-count', rowCount);

//     let masterChecklist = {}, existingChecklistMap = {};

//     try {
//         masterChecklist = JSON.parse(rawMasterData);
//     } catch (e) {
//         console.error('Invalid master checklist:', e);
//         $('.checklist-modal-body').html('<div class="text-danger">Unable to load checklist data.</div>');
//         return;
//     }

//     try {
//         const existingChecklist = JSON.parse(rawExistingData || '{}');
//         // Flatten to key: checklist_id-detail_id
//         existingChecklistMap = {};
//         (existingChecklist.existingCheckLists || []).forEach(entry => {
//             const key = `${entry.checklist_id}-${entry.checklist_detail_id}`;
//             existingChecklistMap[key] = entry;
//         });
//     } catch (e) {
//         console.warn('Invalid saved checklist data:', e);
//     }

//     const checklists = masterChecklist.checkLists || [];
//     if (!checklists.length) {
//         $('.checklist-modal-body').html('<div class="text-muted">No checklist available.</div>');
//         return;
//     }

//     let html = '';

//     checklists.forEach((checklist) => {
//         const checklistName = escapeHTML(checklist.name);
//         const checklistId = checklist.id;

//         html += `
//             <div class="mb-3 text-center fw-bold fs-5">${checklistName}</div>
//             <input type="hidden" id="checklist_id" value="${checklistId}">
//             <input type="hidden" id="checklist_name" value="${checklistName}">
//             <div class="table-responsive-md customernewsection-form">
//                 <table class="table table-bordered po-order-detail myrequesttablecbox nowrap w-100 text-center align-middle">
//                     <thead class="table-light">
//                         <tr>
//                             <th width="60%">Parameters</th>
//                             <th>Values</th>
//                             <th>Result</th>
//                         </tr>
//                     </thead>
//                 <tbody>`;

//         (checklist.details || []).forEach((detail, index) => {
//             const paramLabel = escapeHTML(detail.name || '');
//             const type = detail.data_type || 'text';
//             const requiredAttr = detail.mandatory ? 'required' : '';
//             const detailId = detail.id;

//             const namePrefix = `components[${rowCount}][checklist][${index}]`;
//             const paramIdField = `${namePrefix}[parameter_item_id]`;
//             const paramChecklistIdField = `${namePrefix}[parameter_checkl_id]`;
//             const paramNameField = `${namePrefix}[parameter_name]`;
//             const paramValueField = `${namePrefix}[parameter_value]`;
//             const resultField = `${namePrefix}[parameter_result]`;

//             const rowId = `check_${rowCount}_${detailId}`;
//             const savedKey = `${checklistId}-${detailId}`;
//             const saved = existingChecklistMap[savedKey] || {};
//             const savedValue = saved.value || '';
//             const savedChecklistId = saved.id || '';
//             const savedResult = saved.result?.toLowerCase() || '';

//             html += `<tr>
//                 <td class="text-start ps-3">
//                     ${paramLabel} ${detail.mandatory ? '<span class="text-danger">*</span>' : ''}
//                     <input type="hidden" name="${paramNameField}" value="${paramLabel}" />
//                     <input type="hidden" name="${paramIdField}" value="${detailId}" />
//                     <input type="hidden" name="${paramChecklistIdField}" value="${savedChecklistId}" />
//                 </td>`;

//             html += `<td>`;
//             switch (type) {
//                 case 'number':
//                 case 'text':
//                 default:
//                     html += `<input type="${type === 'number' ? 'number' : 'text'}" name="${paramValueField}" value="${escapeHTML(savedValue)}" class="form-control mw-100" ${requiredAttr} />
//                         <div class="invalid-feedback">Required</div>`;
//                     break;
//                 case 'date':
//                     html += `<input type="date" name="${paramValueField}" value="${escapeHTML(savedValue)}" class="form-control mw-100" ${requiredAttr} />
//                         <div class="invalid-feedback">Required</div>`;
//                     break;
//                 case 'list':
//                     html += `<select name="${paramValueField}" class="form-select mw-100" ${requiredAttr}>
//                         <option value="">Select</option>`;
//                     (detail.values || []).forEach(opt => {
//                         const selected = opt.value === savedValue ? 'selected' : '';
//                         html += `<option value="${escapeHTML(opt.value)}" ${selected}>${escapeHTML(opt.value)}</option>`;
//                     });
//                     html += `</select>
//                         <div class="invalid-feedback">Required</div>`;
//                     break;
//                 case 'boolean':
//                     html += `<select name="${paramValueField}" class="form-select mw-100" ${requiredAttr}>
//                         <option value="">Select</option>
//                         <option value="yes" ${savedValue === 'yes' ? 'selected' : ''}>Yes</option>
//                         <option value="no" ${savedValue === 'no' ? 'selected' : ''}>No</option>
//                     </select>
//                     <div class="invalid-feedback">Required</div>`;
//                     break;
//             }
//             html += `</td>`;

//             html += `
//                 <td>
//                     <div class="d-flex justify-content-center gap-3">
//                         <div class="form-check">
//                             <input class="form-check-input" type="radio"
//                                 name="${resultField}" id="${rowId}_pass" value="pass"
//                                 ${savedResult === 'pass' ? 'checked' : ''}
//                                 ${detail.mandatory ? 'data-required="1"' : ''}>
//                             <label class="form-check-label text-success" for="${rowId}_pass">Pass</label>
//                         </div>
//                         <div class="form-check">
//                             <input class="form-check-input" type="radio"
//                                 name="${resultField}" id="${rowId}_fail" value="fail"
//                                 ${savedResult === 'fail' ? 'checked' : ''}
//                                 ${detail.mandatory ? 'data-required="1"' : ''}>
//                             <label class="form-check-label text-danger" for="${rowId}_fail">Fail</label>
//                         </div>
//                     </div>
//                 </td>
//             </tr>`;
//         });

//         html += `</tbody></table></div><hr/>`;
//     });

//     $('.checklist-modal-body').html(html);
// });

// $(document).on('change', '.toggle-pass-check', function () {
//     const $label = $(this).closest('td').find('.pass-label');
//     if ($(this).is(':checked')) {
//         $label.removeClass('d-none');
//     } else {
//         $label.addClass('d-none');
//     }
// });

// // Optional: escape user input if needed
// function escapeHTML(str) {
//     return String(str || '')
//         .replace(/&/g, "&amp;")
//         .replace(/</g, "&lt;")
//         .replace(/>/g, "&gt;")
//         .replace(/"/g, "&quot;")
//         .replace(/'/g, "&#039;");
// }

// // Submit Checklist Button
// $(document).on('click', '.submitChecklistBtn', function (e) {
//     e.preventDefault();

//     const $modal = $('#inspectionChecklistModal');
//     const rowCount = $modal.find('.checklist-modal-body').data('row-count') || $('.inspectionChecklistBtn').data('row-count');

//     const $allRows = $modal.find(`table tbody tr`);
//     let isValid = true;
//     let data = [];

//     const checkListId = $('#checklist_id').val();
//     const checkListName = $('#checklist_name').val();

//     // Reset previous validation states
//     $modal.find('.is-invalid').removeClass('is-invalid');
//     $modal.find('.text-danger.result-feedback').remove(); // remove old error messages

//     $allRows.each(function () {
//         const $row = $(this);

//         const paramInspChckIdInput = $row.find(`[name*="[parameter_checkl_id]"]`);
//         const paramItemIdInput = $row.find(`[name*="[parameter_item_id]"]`);
//         const paramNameInput = $row.find(`[name*="[parameter_name]"]`);
//         const paramValueInput = $row.find(`[name*="[parameter_value]"]`);
//         const resultFieldName = $row.find(`[name*="[parameter_result]"]`).attr('name');
//         const resultValue = $row.find(`[name="${resultFieldName}"]:checked`).val(); // ✅ radio selection

//         const paramInspChckId = paramInspChckIdInput.val();
//         const paramItemId = paramItemIdInput.val();
//         const paramName = paramNameInput.val();
//         const paramValue = paramValueInput.val();
//         const isParamRequired = paramValueInput.prop('required');
//         const isResultRequired = $row.find(`[name="${resultFieldName}"]`).data('required') === 1;

//         // Validate parameter value
//         if (isParamRequired && (!paramValue || paramValue.trim() === '')) {
//             paramValueInput.addClass('is-invalid');
//             isValid = false;
//         }

//         // Validate Pass/Fail selection (radio)
//         if (isResultRequired && !resultValue) {
//             $row.find('td:last').append('<div class="text-danger result-feedback small mt-1">Please select Pass or Fail</div>');
//             isValid = false;
//         }

//         // Push structured data for saving
//         data.push({
//             insp_checklist_id: paramInspChckId,
//             checkList_id: checkListId,
//             checkList_name: checkListName,
//             detail_id: paramItemId,
//             parameter_name: paramName,
//             parameter_value: paramValue,
//             result: resultValue || '' // fallback if not selected
//         });
//     });

//     if (!isValid) {
//         Swal.fire({
//             icon: 'error',
//             title: 'Required Fields Missing',
//             text: 'Please fill all required fields and select Pass/Fail before submitting.',
//         });
//         return;
//     }

//     // Save to hidden input
//     const hiddenFieldName = `components[${rowCount}][inspectionData]`;
//     const $targetRow = $(`#row_${rowCount}`);
//     let $hidden = $targetRow.find(`input[name="${hiddenFieldName}"]`);

//     if ($hidden.length === 0) {
//         $targetRow.append(`<input type="hidden" name="${hiddenFieldName}" />`);
//         $hidden = $targetRow.find(`input[name="${hiddenFieldName}"]`);
//     }

//     $hidden.val(JSON.stringify(data));

//     // Close modal and mark button
//     $modal.modal('hide');
//     $targetRow.find('.inspectionChecklistBtn').addClass('text-success');
// });


