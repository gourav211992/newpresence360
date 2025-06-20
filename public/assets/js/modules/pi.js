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

function initAutocompVendor(selector, type) {
    $(selector).autocomplete({
        minLength: 0,
        source: function(request, response) {
            let item_id = $(this.element).closest('tr').find("[name*='[item_id]']").val();
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type:'vendor_list',
                    item_id:item_id
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: item.company_name,
                            code: item.vendor_code,
                            addresses: item.addresses
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
            let itemName = ui.item.value;
            let itemId = ui.item.id;
            let itemCode = ui.item.code;
            $input.attr('data-name', itemName);
            $input.val(itemCode);
            $input.closest('tr').find("[name*='[vendor_name]']").val(itemName);
            $input.closest('tr').find("[name*='[vendor_id]']").val(itemId);
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                $(this).attr('data-name', '');
                $(this).closest('tr').find("[name*='[vendor_name]']").val('');
                $(this).closest('tr').find("[name*='[vendor_id]']").val('');
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
            $(this).closest('tr').find("[name*='[vendor_name]']").val('');
            $(this).closest('tr').find("[name*='[vendor_id]']").val('');
        }
    }).on("input", function () {
        if ($(this).val().trim() === "") {
            $(this).removeData("selected");
            $(this).closest('tr').find("[name*='[vendor_name]']").val('');
            $(this).closest('tr').find("[name*='[vendor_id]']").val('');
        }
    });
}
if($("[name*='[vendor_code]']").length) {
    initAutocompVendor("[name*='[vendor_code]']");
}

function updateIndentQty($row) {
    var reqQty = parseFloat($row.find('input[name$="[qty]"]').val()) || 0;
    var avlStock = parseFloat($row.find('input[name$="[avl_stock]"]').val()) || 0;
    var adjQtyInput = $row.find('input[name$="[adj_qty]"]');
    var adjQty = parseFloat(adjQtyInput.val()) || 0;
    if (adjQty >  Math.min(reqQty, avlStock)) {
        adjQty = Math.min(reqQty, avlStock);
        adjQtyInput.val(adjQty);
    }

    var indentQty = reqQty - adjQty;
    $row.find('input[name$="[indent_qty]"]').val(indentQty.toFixed(2));
}

// When adj_qty changes
$(document).on('keyup change', 'input[name^="components"][name$="[adj_qty]"]', function () {
    var $row = $(this).closest('tr');
    updateIndentQty($row);
});

// When req_qty changes
$(document).on('keyup change', 'input[name^="components"][name$="[qty]"]', function () {
    var $row = $(this).closest('tr');
    updateIndentQty($row);
});

document.querySelectorAll('#orderTypeSelect').forEach((radio) => {
    radio.addEventListener('change', function () {
        document.getElementById('procurement_type').value = this.value;
    });
});

$(document).on('change', '#procurement_type', function () {
    let selectedValue = this.value;
    $("#procurement_type").val(selectedValue);
    
});
