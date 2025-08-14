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
    setSelectedAttribute(rowCount);
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
    let isInspection = $(e.target).closest('tr').find("[name*='is_inspection']").val() || '';
    let orderQty = parseFloat(qty.value);
    orderQuantity.val(orderQty.toFixed(2));
    if(isInspection == 1){
        acceptedQuantity.val('0.00');
    } else{
        acceptedQuantity.val(orderQty.toFixed(2));
    }
    rejectedQuantity.val('0.00');
});

/*qty on change*/
$(document).on('blur',"[name*='accepted_qty']",(e) => {
    let tr = e.target.closest('tr');
    let qty = e.target;
    let dataIndex = $(e.target).closest('tr').attr('data-index');
    let itemId = $(e.target).closest('tr').find('[name*=item_id]').val();
    let acceptedQuantity = $(e.target).closest('tr').find("[name*='accepted_qty']");
    let receiptQuantity = $(e.target).closest('tr').find("[name*='order_qty']");
    let rejectedQuantity = $(e.target).closest('tr').find("[name*='rejected_qty']");
    let itemCost = $(e.target).closest('tr').find("[name*='rate']");
    let mrnDetailId = $(e.target).closest('tr').find("[name*='mrn_detail_id']").val() || '';
    let poDetailId = $(e.target).closest('tr').find("[name*='po_detail_id']").val() || '';
    let geDetailId = $(e.target).closest('tr').find("[name*='gate_entry_detail_id']").val() || '';
    let siDetailId = $(e.target).closest('tr').find("[name*='supplier_inv_detail_id']").val() || '';
    let itemValue = $(e.target).closest('tr').find("[name*='basic_value']");
    let isInspection = $(e.target).closest('tr').find("[name*='is_inspection']").val() || '';

    if(mrnDetailId || poDetailId){
        let actionUrl = '/material-receipts/validate-quantity?item_id='+itemId+
        '&mrnDetailId='+mrnDetailId+
        '&poDetailId='+poDetailId+
        '&geDetailId='+geDetailId+
        '&siDetailId='+siDetailId+
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
                    if(isInspection == 1){
                        rq = 0;
                    }
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
                    if(isInspection == 1){
                        rq = 0;
                    }
                    acceptedQuantity.val(aq.toFixed(2));
                    rejectedQuantity.val(rq.toFixed(2));
                }
            });
        });
    }
    let aq = parseFloat(acceptedQuantity.val());
    let rq = 0;
    if(isInspection == 1){
        rq = 0;
    }else{
        rq = parseFloat(receiptQuantity.val()) - parseFloat(acceptedQuantity.val());
    }

    acceptedQuantity.val(aq.toFixed(2));
    rejectedQuantity.val(rq.toFixed(2));
    if (Number(itemCost.val())) {
        let totalItemValue = parseFloat(acceptedQuantity.val()) * parseFloat(itemCost.val());
        itemValue.val(totalItemValue.toFixed(2));
    } else {
        itemValue.val('');
    }
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
        ;
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

setTimeout(() => {
    if($("tr[id*='row_']").length) {
        ;
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
    getItemDetail(tr);
    getItemCostPrice(tr);
    ;
});

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

// 1. Handle location change
$(document).on('change', '.header_store_id', function () {
    const selectedStoreId = $(this).val();
    if (selectedStoreId) {
        getSubStores(selectedStoreId, true); // pass true to check on substore load
        getCostCenters(selectedStoreId);
    }
});

// 2. Handle sub-store change
$(document).on('change', '.sub_store', function () {
    console.log("🔁 Sub-store changed");
    const selectedStoreId = $('.header_store_id').val();
    const selectedSubStoreId = $(this).val();
    const isRequired = $(this).find(':selected').data('warehouse-required');


    if (selectedStoreId && selectedSubStoreId && isRequired) {
        checkWarehouseSetup(selectedStoreId, selectedSubStoreId);
    }
});

// 3. On page load trigger if values already present
const selectedStoreId = $(".header_store_id").val();
const $selectedSubStore = $(".sub_store").find("option:selected");
const selectedSubStoreId = $(".sub_store").val();
const isSubStoreRequired = Number($selectedSubStore.data("warehouse-required")) === 1;

if (selectedStoreId) {
    const path = window.location.pathname;
    const match = path.match(/\/put-away\/edit\/(\d+)/);
    const id = match ? match[1] : null;
    if(id == null)
    {
        getSubStores(selectedStoreId, selectedSubStoreId);
    }
     // avoid double-check
    getCostCenters(selectedStoreId);
}

if (selectedStoreId && selectedSubStoreId && isSubStoreRequired) {
    checkWarehouseSetup(selectedStoreId, selectedSubStoreId);
}
// 4. Get Sub Stores
function getSubStores(storeLocationId, selectedSubStoreId = null)
{
    const storeId = storeLocationId;

    $.ajax({
        url: "/sub-stores/store-wise",
        method: 'GET',
        dataType: 'json',
        data: {
            store_id: storeId,
        },
        success: function(response) {
            if (response.status === 200 && Array.isArray(response.data) && response.data.length) {
                let options = '';

                response.data.forEach(function(location) {
                    const isSelected = selectedSubStoreId && location.id == selectedSubStoreId ? 'selected' : '';
                    options += `<option value="${location.id}" data-warehouse-required="${location.is_warehouse_required}" ${isSelected}>${location.name}</option>`;
                });

                $(".sub_store").html(options);

                if (selectedSubStoreId) {
                    $(".sub_store").val(selectedSubStoreId).trigger('change');
                }

                // ✅ Trigger change manually after population to check warehouse setup
                const $selectedOption = $(".sub_store option:selected");
                const subStoreId = $selectedOption.val();
                const isWarehouseRequired = Number($selectedOption.data("warehouse-required"));

                if (subStoreId && isWarehouseRequired) {
                    checkWarehouseSetup(storeId, subStoreId);
                }
            } else {
                $(".sub_store").empty();
            }
        },
        error: function(xhr) {
            Swal.fire({
                title: 'Error!',
                text: xhr?.responseJSON?.message || 'Something went wrong!',
                icon: 'error',
            });
        }
    });
}

// 5. Warehouse Setup Validation
function checkWarehouseSetup(storeId, subStoreId) {
    $.ajax({
        url: "/put-away/warehouse/check-setup",
        method: 'GET',
        dataType: 'json',
        data: { store_id: storeId, sub_store_id: subStoreId },
        success: function (data) {
            console.log(data);

            if (data.status === 204 && !data.is_setup) {
                Swal.fire({
                    title: 'Warehouse Setup Missing',
                    text: data.message,
                    icon: 'error',
                });
                disableWarehouseActions();
            } else if (data.status === 200 && data.is_setup) {
                enableWarehouseActions();
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'Something went wrong while checking warehouse setup.',
                    icon: 'error',
                });
                disableWarehouseActions();
            }
        },
        error: function (xhr) {
            Swal.fire({
                title: 'Error!',
                text: xhr?.responseJSON?.message || 'Warehouse check failed:',
                icon: 'error',
            });
            disableWarehouseActions();
        }
    });
}

// Utility: Enable or Disable UI elements
function enableWarehouseActions() {
    $(".vendor_name").prop("disabled", false).trigger("change.select2");
    $(".addStoragePointBtn").css({ "pointer-events": "auto", "opacity": "1" }).removeClass("disabled");
    $(".is_warehouse_required").val(1);
}

function disableWarehouseActions() {
    $(".vendor_name").prop("disabled", true).trigger("change.select2");
    $(".addStoragePointBtn").css({ "pointer-events": "none", "opacity": "0.6" }).addClass("disabled");
    $(".is_warehouse_required").val(0);
}

// GLOBALS
let itemStorageMap = {};  // Key = item ID or code, Value = array of storage points
let allStoragePointsList = [];
let activeRowIndex = null;
let expectedInvQty = 0;

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

    let itemId = $("#itemTable #row_" + activeRowIndex).find("[name*='[item_id]']").val();
    // Only extract packets if no storage_packets exists yet
    const existingValue = $(`#itemTable #row_${activeRowIndex} input[name*='[storage_packets]']`).val();
    console.log('existingValue', existingValue);
    
    let parsed = null;
    if(existingValue && existingValue.length) {
        parsed = JSON.parse(existingValue);
    } else {
        // If no existing value, initialize with empty array
        getOldPackets(activeRowIndex);
        const $storageInput = $(`#itemTable #row_${activeRowIndex} input[name*='[storage_packets]']`).val();
        if($storageInput) {
            parsed = JSON.parse($storageInput); // re-parse after rebuild
        }
    }

    if (Array.isArray(parsed) && parsed.length > 0) {
        populatePacketTable(parsed);
        updateAddButtonState();
        $('#storagePointsRowIndex').val(activeRowIndex);
        $('#storagePointsModal').modal('show');
        return;
    }

    const packets = [];
    $('#storagePacketTable tbody tr').each(function () {
        const id = $(this).find('.storage-packet-id').val() || null;
        const item_location_id = $(this).find('.item-location-id').val() || null;
        const qty = parseFloat($(this).find('.storage-packet-qty').val()) || 0;
        const packet_number = $(this).find('.storage-packet-number').val() || null;
        const wh_detail_id = $(this).find('.wh_detail_id').val() || null;
        packets.push(
            { 
                id: id, 
                item_location_id: item_location_id, 
                quantity: qty, 
                packet_number: packet_number,
                wh_detail_id: wh_detail_id 
            }
        );
    });

    $(`#itemTable #row_${activeRowIndex} input[name*='[storage_packets]']`).val(JSON.stringify(packets));
});

// Utility: Extract and save old packets into hidden input
function getOldPackets(rowCount) {
    const packets = [];
    console.log('rowCount', rowCount);
    
    $(`[id="row_${rowCount}"]`).find("[name*='[packet_number]']").each(function(index,item) {
        let key = index +1;
        const id = $(item).closest('tr').find(`[name*='[${key}][id]']`).val() ?? 0;
        const item_location_id = $(item).closest('tr').find(`[name*='[${key}][item_location_id]']`).val() ?? null;
        const packet_number = $(item).closest('tr').find(`[name*='[${key}][packet_number]']`).val() ?? null;
        const quantity = $(item).closest('tr').find(`[name*='[${key}][quantity]']`).val() ?? null;
        const wh_detail_id = $(item).closest('tr').find(`[name*='[${key}][wh_detail_id]']`).val() ?? null;
        if (quantity || wh_detail_id) {
            packets.push(
                { 
                    id: id, 
                    item_location_id: item_location_id, 
                    quantity: quantity, 
                    packet_number: packet_number,
                    wh_detail_id: wh_detail_id 
                }
            );
            expectedInvQty += Number(quantity);
        }
    });
    
    if (packets.length) {
        $(`#itemTable #row_${rowCount} input[name*='[storage_packets]']`).val(JSON.stringify(packets));
    }
}

// Populate the packet table with data
function populatePacketTable(data) {
    const activeRowIndex = $('#storagePointsRowIndex').val();
    let itemId = $("#itemTable #row_" + activeRowIndex).find("[name*='[item_id]']").val();
    let rows = '';
    console.log('allStoragePointsList', allStoragePointsList);
    
    data.forEach((row, i) => {
        let options = ``;
        let selectedId = row.wh_detail_id;
        allStoragePointsList.forEach(point => {
            const selected = selectedId && Number(point.id) === Number(selectedId) ? 'selected' : '';
            console.log('selectedId', selectedId, selected);
            
            const availableWeight = (point.max_weight || 0) - (point.current_weight || 0);
            const availableVolume = (point.max_volume || 0) - (point.current_volume || 0);

            const label = `${point.name} (${point.parents || '-'}) (W: ${availableWeight}/${point.max_weight}, V: ${availableVolume}/${point.max_volume})`;
            options += `<option value="${point.id}" ${selected}>${label}</option>`;
        });
        rows += `
            <tr data-index="${i}">
                <td><input type="checkbox" class="form-check-input packet-row-check" data-index="${i}" /></td>
                <td>
                    <input type="number" step="any" value="${(Number(row.quantity) || 0).toFixed(2)}" class="form-control storage-packet-qty mw-100" name="components[${activeRowIndex}][packets][${i}][quantity]" data-index="${i}" />
                    <input type="hidden" step="any" value="${row.id}" class="form-control storage-packet-id mw-100" name="components[${activeRowIndex}][packets][${i}][packet_id]" data-index="${i}" />
                    <input type="hidden" step="any" value="${row.item_location_id}" class="form-control item-location-id mw-100" name="components[${activeRowIndex}][packets][${i}][item_location_id]" data-index="${i}" />
                </td>
                <td>
                    <input type="text" step="any" value="${row.packet_number}" class="form-control storage-packet-number mw-100" name="components[${activeRowIndex}][packets][${i}][packet_number]" data-index="${i}" />
                </td>
                <td>
                    <select class="form-select wh_detail_id form-select-sm storage-point-dropdown mw-100" name="components[${activeRowIndex}][packets][${i}][wh_detail_id]" data-index="${i}">
                        ${options}
                    </select>
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

    $('#storagePacketTable tbody').html(rows);

    $('#storagePacketTable tfoot').remove();
    $('#storagePacketTable').append(`
        <tfoot>
            <tr>
                <td class="text-end fw-bold">Total</td>
                <td><span id="storagePacketTotal">0</span></td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    `);
    updatePacketTotal();
    updateAddButtonState();
}

// Recalculate total
$(document).on('input', '.storage-packet-qty', function () {
    updatePacketTotal();
    updateAddButtonState();
});

// Delete row
$(document).on('click', '.remove-storage-row', function () {
    $(this).closest('tr').remove();
    updatePacketTotal();
    updateAddButtonState();
});

// Add new row
$(document).on('click', '.add-storage-row-header', function () {
    const activeRowIndex = $('#storagePointsRowIndex').val();
    let total = 0;
    $('.storage-packet-qty').each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    if (total >= expectedInvQty) return;

    const remaining = expectedInvQty - total;
    const index = $('#storagePacketTable tbody tr').length;
    const baseUom = $('#storagePacketTable tbody tr:first-child td:nth-child(4)').text() || 'PCS';
    let options = ``;
        allStoragePointsList.forEach(point => {
            const availableWeight = (point.max_weight || 0) - (point.current_weight || 0);
            const availableVolume = (point.max_volume || 0) - (point.current_volume || 0);

            const label = `${point.name} (${point.parents || '-'}) (W: ${availableWeight}/${point.max_weight}, V: ${availableVolume}/${point.max_volume})`;
            options += `<option value="${point.id}">${label}</option>`;
        });
    const row = `
        <tr data-index="${index}">
            <td>
                <input type="checkbox" class="form-check-input packet-row-check" data-index="${index}" />
            </td>
            <td>
                <input type="number" step="any" value="${remaining}" class="form-control storage-packet-qty mw-100" name="components[${activeRowIndex}][packets][${index}][quantity]" data-index="${index}" />
            </td>
            <td>
                <input type="text" step="any" class="form-control storage-packet-number mw-100" name="components[${activeRowIndex}][packets][${index}][packet_number]" data-index="${index}" />
            </td>
            <td>
                <select class="form-select form-select-sm wh_detail_id storage-point-dropdown mw-100" name="components[${activeRowIndex}][packets][${index}][wh_detail_id]" data-index="${index}">
                    ${options}
                </select>
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

    $('#storagePacketTable tbody').append(row);
    updatePacketTotal();
    updateAddButtonState();
});

// Multi-delete selected
$(document).on('click', '.delete-storage-row-header', function () {
    $('.packet-row-check:checked').each(function () {
        $(this).closest('tr').remove();
    });
    updatePacketTotal();
    updateAddButtonState();
});

function updatePacketTotal() {
    let total = 0;
    $('.storage-packet-qty').each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    $('#storagePacketTotal').text(total);
}

function updateAddButtonState() {
    const total = Number($('#storagePacketTotal').text());
    console.log(`Total packets: ${total}, Expected inventory quantity: ${expectedInvQty}`);

    $('.add-storage-row-header').prop('disabled', total >= expectedInvQty);
}

// Save button validation
$(document).on('click', '#saveStoragePointsBtn', function () {
    const totalQty = Number($('#storagePacketTotal').text());
    if (totalQty !== expectedInvQty) {
        Swal.fire('Error', `Total quantity (${totalQty}) must equal inventory quantity (${expectedInvQty}).`, 'error');
        return;
    }

    const packets = [];
    $('#storagePacketTable tbody tr').each(function () {
        const id = $(this).find('.storage-packet-id').val() || null;
        const item_location_id = $(this).find('.item-location-id').val() || null;
        const qty = parseFloat($(this).find('.storage-packet-qty').val()) || 0;
        const packet_number = $(this).find('.storage-packet-number').val() || null;
        const wh_detail_id = $(this).find('.wh_detail_id').val() || null;
        packets.push(
            { 
                id: id, 
                item_location_id: item_location_id, 
                quantity: qty, 
                packet_number: packet_number,
                wh_detail_id: wh_detail_id 
            }
        );
    });

    $(`#itemTable #row_${activeRowIndex} input[name*='[storage_packets]']`).empty();
    $(`#itemTable #row_${activeRowIndex} input[name*='[storage_packets]']`).val(JSON.stringify(packets));
    $('#storagePointsModal').modal('hide');
});


