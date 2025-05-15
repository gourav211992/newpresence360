/*Approve modal*/
$(document).on('click', '#approved-button', (e) => {
   let actionType = 'approve';
   $("#approveModal").find("#action_type").val(actionType);
   $("#approveModal #popupTitle").text("Approve Application");
   $("#approveModal").modal('show');
});
$(document).on('click', '#reject-button', (e) => {
   let actionType = 'reject';
   $("#approveModal").find("#action_type").val(actionType);
   $("#approveModal #popupTitle").text("Reject Application");
   $("#approveModal").modal('show');
});

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
});

/*Check box check and uncheck*/
$(document).on('change','#itemTable > thead .form-check-input',(e) => {
    const isChecked = e.target.checked;
    $("#itemTable > tbody .form-check-input").each(function() {
        if (!$(this).is(':disabled')) { // Only check if the checkbox is not disabled
            $(this).prop('checked', isChecked);
        }
    });
});

$(document).on('change','#itemTable > tbody .form-check-input',(e) => {
    const allChecked = $("#itemTable > tbody .form-check-input:not(:disabled)").length === 
                       $("#itemTable > tbody .form-check-input:checked:not(:disabled)").length;

    $('#itemTable > thead .form-check-input').prop('checked', allChecked);
});

/*Attribute on change*/
$(document).on('change', '[name*="comp_attribute"]', (e) => {
    let rowCount = e.target.closest('tr').querySelector('[name*="row_count"]').value;
    let attrGroupId = e.target.getAttribute('data-attr-group-id');
    $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).val(e.target.value);
    qtyEnabledDisabled();
    setSelectedAttribute(rowCount);
});

/*Edit mode table calculation filled*/
if($("#itemTable .mrntableselectexcel tr").length) {
   setTimeout(()=> {
      $("[name*='component_item_name[1]']").trigger('focus');
      $("[name*='component_item_name[1]']").trigger('blur');
   },100);
}

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
    if(!$("#itemTable #row_"+rowCount).find("[name*='[d_qty]']").length) {        
    let rowHtml = `<tr class="display_delivery_row">
                        <td>1</td>
                        <td>
                            <input type="hidden" name="row_count" value="${rowCount}" id="row_count">
                            <input type="number" name="components[${rowCount}][delivery][1][d_qty]" class="form-control mw-100" />
                        </td>
                        <td>
                            <input type="date" name="components[${rowCount}][delivery][1][d_date]" value="${curDate}" class="form-control mw-100" /></td>
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
            let dQty =  $(item).closest('td').find(`[name='components[${rowCount}][delivery][${index+1}][d_qty]']`).val();
            let dDate =  $(item).closest('td').find(`[name='components[${rowCount}][delivery][${index+1}][d_date]']`).val();
            let id =  $(item).closest('td').find(`[name='components[${rowCount}][delivery][${index+1}][id]']`).val();

            rowHtml+= `<tr class="display_delivery_row">
                        <td>${index+1}</td>
                        <td>
                            <input type="hidden" name="row_count" value="${rowCount}" id="row_count">
                            <input type="number" value="${dQty}" name="components[${rowCount}][delivery][${index+1}][d_qty]" class="form-control mw-100" />
                        </td>
                        <td>
                            <input type="date" name="components[${rowCount}][delivery][${index+1}][d_date]" value="${dDate}" class="form-control mw-100" /></td>
                        <td>
                        <a data-row-count="${rowCount}" data-id="${id}" data-index="${index+1}" href="javascript:;" class="text-danger deleteItemDeliveryRow"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></a>
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
        total = total + Number($(item).val());
    });
    $("#deliveryFooter #total").text(total.toFixed(2));
}

// addTaxItemRow add row
$(document).on('click', '.addTaxItemRow', (e) => {
    let rowCount = $('#deliveryScheduleModal .display_delivery_row').find('#row_count').val();
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
        let curDate = new Date().toISOString().split('T')[0];
        let tblRowCount = $('#deliveryScheduleModal .display_delivery_row').length + 1;
        let rowHtml = `<tr class="display_delivery_row">
                            <td>${tblRowCount}</td>
                            <td>
                                <input type="hidden" name="row_count" value="${rowCount}" id="row_count">
                                <input type="number" name="components[${rowCount}][delivery][${tblRowCount}][d_qty]" class="form-control mw-100" />
                            </td>
                            <td>
                                <input type="date" name="components[${rowCount}][delivery][${tblRowCount}][d_date]" value="${curDate}" class="form-control mw-100" /></td>
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
        event.preventDefault();
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
    let dataId = $(e.target).closest('a').attr('data-id');
    if(dataId) {
        let rowIndex = $(e.target).closest('a').attr('data-index');
        let rowCount = $(e.target).closest('a').attr('data-row-count');
        $("#deleteDeliveryModal").find("#deleteDeliveryConfirm").attr('data-id', dataId);
        $("#deleteDeliveryModal").find("#deleteDeliveryConfirm").attr('data-row-index', rowIndex);
        $("#deleteDeliveryModal").find("#deleteDeliveryConfirm").attr('data-row-count', rowCount);
        $("#deleteDeliveryModal").modal('show');
    } else {
        $(e.target).closest('tr').remove();
        setTimeout(() => {
            let rowCount = $(".display_delivery_row").find('#row_count').val();
            $('.display_delivery_row').each(function(index, item) {
                let a = `components[${rowCount}][delivery][${index+1}][d_qty]`;
                let b = `components[${rowCount}][delivery][${index+1}][d_date]`;
                let c = `components[${rowCount}][delivery][${index+1}][id]`;
                $(item).find("[name*='[d_qty]']").prop('name', a);
                $(item).find("[name*='[d_date]']").prop('name', b);
                $(item).find("[name*='[id]']").prop('name', c);
                $(item).find("td:first").text(index+1);
            });
            totalScheduleQty();
        },0);
    }
});

$(document).on('click','#deleteDeliveryConfirm', (e) => {
   let id = e.target.getAttribute('data-id');
   let rowIndex = e.target.getAttribute('data-row-index');
   let rowCount = e.target.getAttribute('data-row-count');
   $("#deleteDeliveryModal").modal('hide');
   $(`.display_delivery_row:nth-child(${rowIndex})`).remove();
   let ids = JSON.parse(localStorage.getItem('deletedDelivery')) || [];
    if (!ids.includes(id)) {
        ids.push(id);
    }
    localStorage.setItem('deletedDelivery', JSON.stringify(ids));
    $('.display_delivery_row').each(function(index, item) {
        let a = `components[${rowCount}][delivery][${index+1}][d_qty]`;
        let b = `components[${rowCount}][delivery][${index+1}][d_date]`;
        $(item).find("[name*='[d_qty]']").prop('name', a);
        $(item).find("td:first").text(index+1);
    });
    $(`[name*='components[${rowCount}][delivery][${rowIndex}]']`).remove();
    totalScheduleQty();
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

$('#attribute').on('hidden.bs.modal', function () {
   let rowCount = $("[id*=row_].trselected").attr('data-index');
   if ($(`[name="components[${rowCount}][qty]"]`).is('[readonly]')) {
        $(`[name="components[${rowCount}][vendor_code]"]`).trigger('focus');
    } else {
        $(`[name="components[${rowCount}][qty]"]`).trigger('focus');
    }
});

/*Vendor change*/
$(document).on('blur', '[name*="[vendor_code]"]', (e) => {
    if(!e.target.value) {
        $(e.target).closest('tr').find('[name*="[vendor_name]"').val('');
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
        let soId = $(item).find("input[name*='[so_id]']").val();
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
                so_id: soId,
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