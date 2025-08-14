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
$(document).on('change',"[name*='order_qty']",(e) => {
    let tr = e.target.closest('tr');
    let qty = e.target;
    checkDuplicateObjects(qty);
    let dataIndex = $(e.target).closest('tr').attr('data-index');
    let orderQuantity = $(e.target).closest('tr').find("[name*='order_qty']");
    let acceptedQuantity = $(e.target).closest('tr').find("[name*='accepted_qty']");
    let rejectedQuantity = $(e.target).closest('tr').find("[name*='rejected_qty']");
    let orderQty = parseFloat(qty.value);
    orderQuantity.val(orderQty.toFixed(2));
    acceptedQuantity.val(orderQty.toFixed(2));
    rejectedQuantity.val('0.00');
});

/*qty on change*/
$(document).on('blur',"[name*='accepted_qty']",(e) => {
    let tr = e.target.closest('tr');
    let qty = e.target;
    let dataIndex = $(e.target).closest('tr').attr('data-index');
    let itemId = $(e.target).closest('tr').find('[name*=item_id]').val();
    let receiptQuantity = $(e.target).closest('tr').find("[name*='order_qty']");
    let acceptedQuantity = $(e.target).closest('tr').find("[name*='accepted_qty']");
    let rejectedQuantity = $(e.target).closest('tr').find("[name*='rejected_qty']");
    let mrnDetailId = $(e.target).closest('tr').find("[name*='mrn_detail_id']").val() || '';
    let detailId = $(e.target).closest('tr').find("[name*='detail_id']").val() || '';
    
    if(mrnDetailId || poDetailId){
        let actionUrl = '/inspection/validate-quantity?item_id='+itemId+
        '&mrnDetailId='+mrnDetailId+
        '&detailId='+detailId+
        '&qty='+acceptedQuantity.val();
        
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                console.log('data.data', data.data);
                let aq = parseFloat(acceptedQuantity.val());
                let rq = parseFloat(receiptQuantity.val()) - parseFloat(acceptedQuantity.val());
                if(data.data.error_message) {
                    Swal.fire({
                        title: 'Error!',
                        text: data.data.error_message,
                        icon: 'error',
                    });
                    if(rq < 0){
                        rq = 0;
                    }
                    acceptedQuantity.val(data.data.order_qty);
                    rejectedQuantity.val(rq.toFixed(2));
                    return false;
                } else{
                    if(rq < 0){
                        rq = 0;
                    }
                    acceptedQuantity.val(aq.toFixed(2));
                    rejectedQuantity.val(rq.toFixed(2));
                }
            });
        });
    }
    let aq = parseFloat(acceptedQuantity.val());
    let rq = parseFloat(receiptQuantity.val()) - parseFloat(acceptedQuantity.val());

    acceptedQuantity.val(aq.toFixed(2));
    rejectedQuantity.val(rq.toFixed(2));
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
qtyEnabledDisabled();

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
    getItemCostPrice(tr);
    
});

// 1. Attach change event
$(document).on('change', '.header_store_id', function () {
    const selectedStoreId = $(this).val();
    if (selectedStoreId) {
        getSubStores(selectedStoreId);
    }
});

// 2. On page load: trigger if already selected
const selectedStoreId = $('.header_store_id').val();
if (selectedStoreId) {
    getSubStores(selectedStoreId);
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

// GLOBALS
let itemStorageMap = {};  // Key = item ID or code, Value = array of storage points
let allStoragePointsList = [];
let activeRowIndex = null;

// Open modal on icon/button click
$(document).on('click', '.addStoragePointBtn', function () {
    activeRowIndex = $(this).data('row-count');
    let qty = Number($("#itemTable #row_" + activeRowIndex).find("[name*='[accepted_qty]']").val());
    if(!qty) {
        Swal.fire({
            title: 'Error!',
            text: 'Please enter quantity then you can add store location.',
            icon: 'error',
        });
        return false;
    }

    const $row = $(`#row_${activeRowIndex}`);
    const storagePointsInput = $row.find(`input[name="components[${activeRowIndex}][storage_points]"]`);
    const storageData = JSON.parse(storagePointsInput.val() || '[]');

    if (storageData.length === 0) {
        storageData.push({ id: '', quantity: qty });
    }

    populateStoragePointsTable(storageData);
    $('#storagePointsRowIndex').val(activeRowIndex);
    $('#storagePointsModal').modal('show');
});

// Populate the table initially
function populateStoragePointsTable(data) {
    const tbody = $('#storagePointsTable tbody');
    const acceptedQty = Number($("#itemTable #row_" + activeRowIndex).find("[name*='[accepted_qty]']").val()) || 0;

    const html = generateStorageRow(0, data[0]?.id || '', data[0]?.quantity || acceptedQty, []);

    tbody.html(html);

    $('#storageQtySummary').remove();
    $('#storagePointsTable').after(`
        <div class="mt-2 text-end" id="storageQtySummary">
            <small><b>Total Quantity:</b> <span id="storageQtyTotal">0</span> / ${acceptedQty}</small>
        </div>
    `);

    updateQtyTotal();
    refreshDropdownDisabling();
}

// Generate a new row
function generateStorageRow(index, selectedId = '', qty = '', usedIds = []) {
    let options = `<option value="">Select</option>`;
    allStoragePointsList.forEach(point => {
        const disabled = usedIds.includes(point.id) && point.id !== selectedId ? 'disabled' : '';
        const selected = point.id === selectedId ? 'selected' : '';
        const availableWeight = (point.max_weight || 0) - (point.current_weight || 0);
        const availableVolume = (point.max_volume || 0) - (point.current_volume || 0);

        const label = `${point.name} (${point.parents || '-'}) (W: ${availableWeight}/${point.max_weight}, V: ${availableVolume}/${point.max_volume})`;
        options += `<option value="${point.id}" ${disabled} ${selected}>${label}</option>`;
    });

    const selectedPoint = allStoragePointsList.find(p => p.id === selectedId);
    const parents = selectedPoint?.parents || '-';
    const availableWeight = (selectedPoint?.max_weight || 0) - (selectedPoint?.current_weight || 0);
    const availableVolume = (selectedPoint?.max_volume || 0) - (selectedPoint?.current_volume || 0);

    return `
        <tr data-index="${index}">
            <td>${index + 1}</td>
            <td>
                <select class="form-select form-select-sm storage-point-dropdown" data-index="${index}">
                    ${options}
                </select>
            </td>
            <td class="weight-display">${availableWeight} / ${selectedPoint?.max_weight || 0}</td>
            <td class="volume-display">${availableVolume} / ${selectedPoint?.max_volume || 0}</td>
            <td class="parents-display">${parents}</td>
            <td>
                <input type="number" step="any" class="form-control form-control-sm quantity-input" 
                       data-index="${index}" value="${qty}" />
            </td>
        </tr>`;
}

// Add row from header
$(document).on('click', '.add-storage-row-header', function () {
    const index = $('#storagePointsTable tbody tr').length;
    const acceptedQty = Number($("#itemTable #row_" + activeRowIndex).find("[name*='[accepted_qty]']").val()) || 0;
    let currentTotal = 0;

    $('.quantity-input').each(function () {
        currentTotal += parseFloat($(this).val()) || 0;
    });

    if (currentTotal >= acceptedQty) {
        Swal.fire('Notice', 'Total quantity already equals accepted quantity.', 'info');
        return;
    }

    const remainingQty = acceptedQty - currentTotal;

    const usedIds = $('#storagePointsTable tbody select.storage-point-dropdown')
        .map(function () { return $(this).val(); })
        .get()
        .filter(val => val)
        .map(Number);

    const newRow = generateStorageRow(index, '', remainingQty, usedIds);
    $('#storagePointsTable tbody').append(newRow);
    updateQtyTotal();
    refreshDropdownDisabling();
});

// Add new row (from row-level + button)
$(document).on('click', '.add-storage-row', function () {
    $('.add-storage-row-header').click();
});

// Change parent label on dropdown change
$(document).on('change', '.storage-point-dropdown', function () {
    const selectedId = Number($(this).val());
    const index = $(this).data('index');
    const point = allStoragePointsList.find(p => p.id === selectedId);

    const parents = point?.parents || '-';
    const availableWeight = (point?.max_weight || 0) - (point?.current_weight || 0);
    const availableVolume = (point?.max_volume || 0) - (point?.current_volume || 0);

    const $row = $(`#storagePointsTable tbody tr[data-index="${index}"]`);
    $row.find('.parents-display').text(parents);
    $row.find('.weight-display').text(`${availableWeight} / ${point?.max_weight || 0}`);
    $row.find('.volume-display').text(`${availableVolume} / ${point?.max_volume || 0}`);

    updateQtyTotal();
    refreshDropdownDisabling();
});

// Update total quantity
$(document).on('input', '.quantity-input', function () {
    updateQtyTotal();
});

function updateQtyTotal() {
    let total = 0;
    $('.quantity-input').each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    $('#storageQtyTotal').text(total);

    const acceptedQty = Number($("#itemTable #row_" + activeRowIndex).find("[name*='[accepted_qty]']").val()) || 0;
    const btn = $('.add-storage-row-header');
    btn.prop('disabled', total >= acceptedQty);
}

// Prevent duplicates across rows
function refreshDropdownDisabling() {
    const selectedValues = $('#storagePointsTable select.storage-point-dropdown')
        .map(function () { return $(this).val(); })
        .get()
        .filter(val => val);

    $('#storagePointsTable select.storage-point-dropdown').each(function () {
        const currentSelect = $(this);
        const currentValue = currentSelect.val();

        currentSelect.find('option').each(function () {
            const val = $(this).val();
            if (!val) return;
            if (val !== currentValue && selectedValues.includes(val)) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        });
    });
}

// Validate total on save
$(document).on('click', '#saveStoragePointsBtn', function () {
    const acceptedQty = Number($("#itemTable #row_" + activeRowIndex).find("[name*='[accepted_qty]']").val()) || 0;
    let total = 0;
    $('.quantity-input').each(function () {
        total += parseFloat($(this).val()) || 0;
    });

    if (total !== acceptedQty) {
        Swal.fire({
            title: 'Error!',
            text: `Total quantity (${total}) must equal accepted quantity (${acceptedQty})`,
            icon: 'error'
        });
        return;
    }

    // 🔽 Prepare and store data
    const storageData = [];
    $('#storagePointsTable tbody tr').each(function () {
        const id = $(this).find('select.storage-point-dropdown').val();
        const qty = parseFloat($(this).find('.quantity-input').val()) || 0;
        if (id && qty > 0) {
            storageData.push({ id: Number(id), quantity: qty });
        }
    });

    const $row = $(`#row_${activeRowIndex}`);
    const targetInput = $row.find(`input[name="components[${activeRowIndex}][storage_points_data]"]`);
    if (targetInput.length) {
        targetInput.val(JSON.stringify(storageData));
    }

    $('#storagePointsModal').modal('hide');
    // optionally proceed with saving data
});

