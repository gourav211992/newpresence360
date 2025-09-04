const order = window.pageData.order;
const editOrder = window.pageData.editOrder;  // Convert string to boolean
const revNoQuery = window.pageData.revNoQuery;
const orderId = window.pageData.orderId;
const ShopFloor = window.pageData.Shop_store;
const Stockk = window.pageData.Stock_store;
const startDate = window.pageData.startDate;
const endDate = window.pageData.endDate;
const today = window.pageData.today;
let csrfToken = window.pageData.csrf_token;
const menuAlias = window.pageData.menu_alias;
const calTaxUrl = window.routes.calTax;
// Assume bookId is already defined
let actionUrl = `${window.routes.docParams}?book_id=${$("#series_id_input").val()}&document_date=${$("#order_date_input").val()}`;
let storeUrl = window.routes.storeData;
let revokeUrl = window.routes.revoke;
let serviceSeriesUrl = window.routes.serviceSeries;
let invDets = window.routes.invDets;
let bookDetails = window.routes.bookDetails;
let amendUrl = window.routes.amend;
let getSeries = window.routes.getSeries;
let redirectUrl = window.routes.redirectUrl;
let invConfirmUrl = window.routes.invConfirmUrl;
// Optional: use them in fetch, axios, etc.
$('#order_date_input').on('blur', function() {
    if(checkDateRange(this)){
    }
    else
    {  
        Swal.fire({
            title: 'Error!',
            text: `Date Should Range Between ${startDate} to ${endDate}`,
            icon: 'error',
        });
    }
});

var taxInputs = [];

function getItemTax(itemIndex)
{
    console.log(itemIndex);
    const itemId = document.getElementById(`items_dropdown_${itemIndex}_value`).value;
    const itemQty = document.getElementById('item_qty_' + itemIndex).value;
    const itemValue = document.getElementById('item_value_' + itemIndex).value;
    let discountAmount = document.getElementById('item_discount_' + itemIndex);
    let headerDiscountAmount = document.getElementById('header_discount_' + itemIndex);
    if(discountAmount && headerDiscountAmount)
    {
        discountAmount = discountAmount.value;
        headerDiscountAmount = headerDiscountAmount.value;
    } else {
        discountAmount = 0;
        headerDiscountAmount = 0;
    }
    const totalItemDiscount = parseFloat(discountAmount ? discountAmount : 0) + parseFloat(headerDiscountAmount ? headerDiscountAmount : 0);
    const billToCountryId = $("#current_billing_country_id").val();
    const billToStateId = $("#current_billing_state_id").val();
    console.log(billToCountryId);
    console.log(billToStateId);
    let itemPrice = 0;
    if (itemQty > 0) {
        itemPrice = (parseFloat(itemValue ? itemValue : 0) + parseFloat(totalItemDiscount ? totalItemDiscount : 0)) / parseFloat(itemQty);
    }
    var headerBookId = order ? order.book_id : null;
    $.ajax({
        url: calTaxUrl,
        method: 'GET',
        dataType: 'json',
        data : {
            item_id : itemId,
            price : itemPrice,
            transaction_type : 'sale',
            party_country_id : billToCountryId,
            party_state_id : billToStateId,
            customer_id : $("#customer_id_input").val(),
            header_book_id : headerBookId ? headerBookId : $("#series_id_input").val(),
            store_id : $("#store_id_input").val(),
            document_id : ''
        },
        success: function(data) {
            const taxInput = document.getElementById('item_tax_' + itemIndex);
            const valueAfterDiscount = document.getElementById('value_after_discount_' + itemIndex).value;
            const valueAfterHeaderDiscount = document.getElementById('value_after_header_discount_' + itemIndex).value;
            let TotalItemTax = 0;
            let taxDetails = [];
            data.forEach((tax, taxIndex) => {
                let currentTaxValue = ((parseFloat(tax.tax_percentage ? tax.tax_percentage : 0)/100) * parseFloat(valueAfterHeaderDiscount ? valueAfterHeaderDiscount : 0));
                TotalItemTax = TotalItemTax + currentTaxValue;
                taxDetails.push({
                    'tax_index' : taxIndex,
                    'tax_name' : tax.tax_type,
                    'tax_group' : tax.tax_group,
                    'tax_type' : tax.tax_type,
                    'taxable_value' : valueAfterHeaderDiscount,
                    'tax_percentage' : tax.tax_percentage,
                    'tax_value' : (currentTaxValue).toFixed(2),
                    'tax_applicability_type' : tax.applicability_type,

                });
            });
            taxInput.setAttribute('tax_details', JSON.stringify(taxDetails))
            taxInput.value = (TotalItemTax).toFixed(2);
            const itemTotalInput = document.getElementById('item_total_' + itemIndex);
            itemTotalInput.value = parseFloat(valueAfterHeaderDiscount ? valueAfterHeaderDiscount : 0) +  parseFloat(TotalItemTax ? TotalItemTax : 0);
            //Get All Total Values
            setAllTotalFields();
            updateHeaderExpenses();
        },
        error: function(xhr) {
            console.error('Error fetching customer data:', xhr.responseText);
            const taxInput = document.getElementById('item_tax_' + itemIndex);
            const valueAfterDiscount = document.getElementById('value_after_discount_' + itemIndex).value;
            // const valueAfterHeaderDiscount = parseFloat(valueAfterDiscount ? valueAfterDiscount : 0) - parseFloat(headerDiscountAmount ? headerDiscountAmount : 0);
            const valueAfterHeaderDiscount = document.getElementById('value_after_header_discount_' + itemIndex).value;
            let TotalItemTax = 0;
            let taxDetails = [];
            taxInput.setAttribute('tax_details', JSON.stringify(taxDetails))
            taxInput.value = (TotalItemTax).toFixed(2);
            const itemTotalInput = document.getElementById('item_total_' + itemIndex);
            itemTotalInput.value = parseFloat(valueAfterHeaderDiscount ? valueAfterHeaderDiscount : 0) +  parseFloat(TotalItemTax ? TotalItemTax : 0);
            setAllTotalFields();
            updateHeaderExpenses();
        }
    });
}

function setAllTotalFields() {
    // Item Value
    const itemTotalInputs = document.getElementsByClassName('item_values_input');
    let totalValue = 0;
    for (let input of itemTotalInputs) {
        totalValue += parseFloat(input.value || 0);
    }

    const totalValueEl = document.getElementById('all_items_total_value');
    const totalValueSummaryEl = document.getElementById('all_items_total_value_summary');
    if (totalValueEl) totalValueEl.textContent = totalValue.toFixed(2);
    if (totalValueSummaryEl) {
        totalValueSummaryEl.textContent = totalValue.toFixed(2);
        totalValueSummaryEl.setAttribute('style', totalValue < 0 ? 'color: red !important;' : '');
    }

    // Item Discount
    const itemTotalDiscounts = document.getElementsByClassName('item_discounts_input');
    let totalDiscount = 0;
    for (let input of itemTotalDiscounts) {
        totalDiscount += parseFloat(input.value || 0);
    }

    const totalDiscountEl = document.getElementById('all_items_total_discount');
    const totalDiscountSummaryEl = document.getElementById('all_items_total_discount_summary');
    if (totalDiscountEl) totalDiscountEl.textContent = totalDiscount.toFixed(2);
    if (totalDiscountSummaryEl) {
        totalDiscountSummaryEl.textContent = totalDiscount.toFixed(2);
        totalDiscountSummaryEl.setAttribute('style', totalDiscount < 0 ? 'color: red !important;' : '');
    }

    // Item Tax
    const itemTotalTaxes = document.getElementsByClassName('item_taxes_input');
    let totalTaxes = 0;
    for (let input of itemTotalTaxes) {
        const taxDetails = input.getAttribute('tax_details');
        let tax_detail = taxDetails ? JSON.parse(taxDetails) : null;

        if (tax_detail) {
            for (let tax of tax_detail) {
                let value = parseFloat(tax.tax_value || 0);
                totalTaxes += (tax.tax_applicability_type === "collection") ? value : -value;
            }
        } else {
            totalTaxes += parseFloat(input.value || 0);
        }
    }

    const totalTaxEl = document.getElementById('all_items_total_tax');
    const totalTaxSummaryEl = document.getElementById('all_items_total_tax_summary');
    if (totalTaxEl) totalTaxEl.value = totalTaxes.toFixed(2);
    if (totalTaxSummaryEl) {
        totalTaxSummaryEl.textContent = Math.abs(totalTaxes).toFixed(2);
        totalTaxSummaryEl.setAttribute('style', '');
    }

    // Item Total After Header Discount
    const itemDiscountTotalInputs = document.getElementsByClassName('item_val_after_header_discounts_input');
    let itemDiscountTotalValue = 0;
    for (let input of itemDiscountTotalInputs) {
        itemDiscountTotalValue += parseFloat(input.value || 0);
    }

    // Item Value After Discount
    const itemValueAfterDiscountInputs = document.getElementsByClassName('item_val_after_discounts_input');
    let itemValueAfterDiscountValue = 0;
    for (let input of itemValueAfterDiscountInputs) {
        itemValueAfterDiscountValue += parseFloat(input.value || 0);
    }

    // Order Discount
    const orderDiscountContainer = document.getElementById('order_discount_summary');
    let orderDiscount = orderDiscountContainer ? parseFloat(orderDiscountContainer.textContent || 0) : 0;
    let taxableValue = itemValueAfterDiscountValue - orderDiscount;

    const totalTotalEl = document.getElementById('all_items_total_total');
    const totalTotalSummaryEl = document.getElementById('all_items_total_total_summary');
    if (totalTotalEl) totalTotalEl.textContent = itemValueAfterDiscountValue.toFixed(2);
    if (totalTotalSummaryEl) {
        totalTotalSummaryEl.textContent = taxableValue.toFixed(2);
        totalTotalSummaryEl.setAttribute('style', taxableValue < 0 ? 'color: red !important;' : '');
    }

    // Total After Tax
    const totalAfterTax = (totalTaxes + itemDiscountTotalValue).toFixed(2);
    const totalAfterTaxSummaryEl = document.getElementById('all_items_total_after_tax_summary');
    if (totalAfterTaxSummaryEl) {
        totalAfterTaxSummaryEl.textContent = totalAfterTax;
        totalAfterTaxSummaryEl.setAttribute('style', totalAfterTax < 0 ? 'color: red !important;' : '');
    }

    // Expenses
    const expensesInput = document.getElementById('all_items_total_expenses_summary');
    const expense = expensesInput ? parseFloat(expensesInput.textContent || 0) : 0;

    // Grand Total
    const grandTotalContainer = document.getElementById('grand_total');
    if (grandTotalContainer) {
        const grandTotal = (parseFloat(totalAfterTax) + expense).toFixed(2);
        grandTotalContainer.textContent = grandTotal;
        grandTotalContainer.setAttribute('style', grandTotal < 0 ? 'color: red !important;' : '');
    }
}


function checkDateRange(element) {
    let date = element.value;
    if (date > endDate || date < startDate) {

        element.value = endDate < today ? endDate : today; // Use .value not .val() for DOM input
        return false;
    }
    else{
        return true;  
    }
}

function resetSeries()
{
    document.getElementById('series_id_input').innerHTML = '';
}

function disableHeader()
{
    const itemInputs = document.getElementsByClassName('comp_item_code');
    let itemsPresent = false;
    if (itemInputs.length > 0) {
        itemsPresent = true;
    }
    const disabledFields = document.getElementsByClassName('disable_on_edit');
    for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
        if (disabledFields[disabledIndex].value && itemsPresent) {
            disabledFields[disabledIndex].disabled = true;
        } else {
            disabledFields[disabledIndex].disabled = false;
        }
    }
    const editBillButton = document.getElementById('billAddressEditBtn');
    if (editBillButton) {
        editBillButton.style.display = "none"
    }
    const editShipButton = document.getElementById('shipAddressEditBtn');
    if (editShipButton) {
        editShipButton.style.display = "none";
    }
    const customerSection = document.getElementById('customer_code_input');
    if(customerSection)
    {
        customerSection.disabled = true;
    }
    let siButton = document.getElementById('select_si_button');
    if (siButton) {
        siButton.disabled = true;
    }
    let dnButton = document.getElementById('select_dn_button');
    if (dnButton) {
        dnButton.disabled = true;
    }

    let lrButton = document.getElementById('select_lorry_button');
    if (lrButton) {
        lrButton.disabled = true;
    }
    let piButton = document.getElementById('select_pi_button');
    if (piButton) {
        piButton.disabled = true;
    }
    let leaseButton = document.getElementById('select_pwo_button');
    if (leaseButton) {
        leaseButton.disabled = true;
    }
    let morderButton = document.getElementById('select_mfg_button');
    if (morderButton) {
        morderButton.disabled = true;
    }
    let orderButton = document.getElementById('select_order_button');
    if (orderButton) {
        orderButton.disabled = true;
    }
    let plButton = document.getElementById('pl_button');
    if (plButton) {
        plButton.disabled = true;
    }
    let plistButton = document.getElementById('pack_list_button');
    if (plistButton) {
        plistButton.disabled = true;
    }
    // let orderButton = document.getElementById('select_mfg_button');
    // if (orderButton) {
    //     orderButton.disabled = true;
    // }
    // let orderButton = document.getElementById('select_mfg_button');
    // if (orderButton) {
    //     orderButton.disabled = true;
    // }
    // let orderButton = document.getElementById('select_mfg_button');
    // if (orderButton) {
    //     orderButton.disabled = true;
    // }
   
}

function enableHeader()
{
    const disabledFields = document.getElementsByClassName('disable_on_edit');
    for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
        disabledFields[disabledIndex].disabled = false;
    }
    const editBillButton = document.getElementById('billAddressEditBtn');
    if (editBillButton) {
        editBillButton.style.display = "block"
    }
    const editShipButton = document.getElementById('shipAddressEditBtn');
    if (editShipButton) {
        editShipButton.style.display = "block";
    }
    const customerSection = document.getElementById('customer_code_input');
    if(customerSection)
    {
        customerSection.disabled = false;
    }
    let siButton = document.getElementById('select_si_button');
    if (siButton) {
        siButton.disabled = false;
    }
    let dnButton = document.getElementById('select_dn_button');
    if (dnButton) {
        dnButton.disabled = false;
    }
    let lrButton = document.getElementById('select_lorry_button');
    if (lrButton) {
        lrButton.disabled = false;
    }
    let piButton = document.getElementById('select_pi_button');
    if (piButton) {
        piButton.disabled = false;
    }
    let leaseButton = document.getElementById('select_pwo_button');
    if (leaseButton) {
        leaseButton.disabled = false;
    }
    let morderButton = document.getElementById('select_mfg_button');
    if (morderButton) {
        morderButton.disabled = false;
    }
    let orderButton = document.getElementById('select_order_button');
    if (orderButton) {
        orderButton.disabled = false;
    }
    let plButton = document.getElementById('pl_button');
    if (plButton) {
        plButton.disabled = false;
    }
    let plistButton = document.getElementById('pack_list_button');
    if (plistButton) {
        plistButton.disabled = false;
    }
}
document.addEventListener('DOMContentLoaded', function() {
    if ((order && order.document_status != "draft") || menuAlias != 'pick-list') {
        editScript();
    }
});

function editScript()
{
    localStorage.setItem('deletedItemDiscTedIds', JSON.stringify([]));
    localStorage.setItem('deletedHeaderDiscTedIds', JSON.stringify([]));
    localStorage.setItem('deletedHeaderExpTedIds', JSON.stringify([]));
    localStorage.setItem('deletedSiItemIds', JSON.stringify([]));
    localStorage.setItem('deletedAttachmentIds', JSON.stringify([]));
    if (order) {
        //Item Discount
        order.items.forEach((item, itemIndex) => {
            const totalValue = item.item_discount_amount;
            const table = document.getElementById('discount_main_table');
            if (table) {
                table.setAttribute('total-value', totalValue);
                table.setAttribute('item-row', 'item_value_' + itemIndex);
                table.setAttribute('item-row-index', itemIndex);
            }

            item?.discount_ted?.forEach((ted, tedIndex) => {
                addHiddenInput("item_discount_name_" + itemIndex + "_" + tedIndex, ted.ted_name, `item_discount_name[${itemIndex}][${tedIndex}]`, 'discount_names_hidden_' + itemIndex, 'item_value_' + itemIndex, ted.id);
                addHiddenInput("item_discount_master_id_" + itemIndex + "_" + tedIndex, ted.ted_id, `item_discount_master_id[${itemIndex}][${tedIndex}]`, 'discount_names_hidden_' + itemIndex, 'item_value_' + itemIndex, ted.id);
                addHiddenInput("item_discount_percentage_" + itemIndex + "_" + tedIndex, ted.ted_percentage ? ted.ted_percentage : '', `item_discount_percentage[${itemIndex}][${tedIndex}]`, 'discount_percentages_hidden_' + itemIndex,  'item_value_' + itemIndex, ted.id);
                addHiddenInput("item_discount_value_" + itemIndex + "_" + tedIndex, ted.ted_amount, `item_discount_value[${itemIndex}][${tedIndex}]`, 'discount_values_hidden_' + itemIndex, 'item_value_' + itemIndex, ted.id);
                addHiddenInput("item_discount_id_" + itemIndex + "_" + tedIndex, ted.id, `item_discount_id[${itemIndex}][${tedIndex}]`, 'discount_ids_hidden_' + itemIndex, 'item_value_' + itemIndex);
            });
            //Item Locations
            itemLocations = [];
            item?.item_locations?.forEach((itemLoc, itemLocIndex) => {
                itemLocations.push({
                    store_id : itemLoc.store_id,
                    store_code : itemLoc.store_code,
                    rack_id : itemLoc.rack_id,
                    rack_code : itemLoc.rack_code,
                    shelf_id : itemLoc.shelf_id,
                    shelf_code : itemLoc.shelf_code,
                    bin_id : itemLoc.bin_id,
                    bin_code : itemLoc.bin_code,
                    qty : itemLoc.quantity
                });
            });
            const dataStoresElement = document.getElementById('data_stores_' + itemIndex);
            if (dataStoresElement) {
                dataStoresElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(itemLocations)));
            }            
            //Bundles info
            const bundleDoc = document.getElementById('item_bundles_' + itemIndex);
            if (bundleDoc && item.bundles && item.bundles.length > 0) {
                let checkedBundlesArray = [];
                item.bundles.forEach((bndle) => {
                    checkedBundlesArray.push({
                        bundle_id : bndle.id,
                        checked : true,
                        qty : bndle.qty
                    });
                });
                bundleDoc.setAttribute('checked-bundle', encodeURIComponent(JSON.stringify(checkedBundlesArray)));
            }
            itemUomsHTML = ``;
            if (item.item.uom && item.item.uom.id) {
                itemUomsHTML += `<option value = '${item.item.uom.id}' ${item.item.uom.id == item.uom_id ? "selected" : ""}>${item.item.uom.alias}</option>`;
            }
            item.item.alternate_uoms.forEach(singleUom => {
                if (singleUom.is_selling) {
                    itemUomsHTML += `<option value = '${singleUom.uom.id}' ${singleUom.uom.id == item.uom_id ? "selected" : ""} >${singleUom.uom?.alias}</option>`;
                }
            });
            document.getElementById('uom_dropdown_' + itemIndex).innerHTML = itemUomsHTML;
            const taxesHiddenFields = document.getElementsByClassName('item_taxes_input');

            if (taxesHiddenFields && taxesHiddenFields.length > 0) {
                getItemTax(itemIndex);
            }
            if (itemIndex==0){
                onItemClick(itemIndex);
            }
            setAttributesUI(itemIndex);

        });
        //Order Discount
        order?.discount_ted?.forEach((orderDiscount, orderDiscountIndex) => {
            document.getElementById('new_order_discount_name').value = orderDiscount.ted_name;
            document.getElementById('new_order_discount_id').value = orderDiscount.ted_id;
            document.getElementById('new_order_discount_percentage').value = orderDiscount.ted_percentage ? orderDiscount.ted_percentage : "";
            document.getElementById('new_order_discount_value').value = orderDiscount.ted_amount;
            addOrderDiscount(orderDiscount.id, false);
        });
        //Order Expense
        order?.expense_ted?.forEach((orderExpense, orderExpenseIndex) => {
            document.getElementById('order_expense_name').value = orderExpense.ted_name;
            document.getElementById('order_expense_id').value = orderExpense.ted_id;
            document.getElementById('order_expense_percentage').value = orderExpense.ted_percentage ? orderExpense.ted_percentage : "";
            document.getElementById('order_expense_value').value = orderExpense.ted_amount;
            addOrderExpense(orderExpense.id, false);
        });
       
        if (typeof window.setAllTotalFields === 'function') {
            setAllTotalFields();
        }
            let mainPullHeader = document.getElementById('selection_section');
            if (mainPullHeader) {
                mainPullHeader.classList.add('d-none');
            }
        //Disable header fields which cannot be changed
        disableHeader();
        //Set all documents
        order.media_files.forEach((mediaFile, mediaIndex) => {
            appendFilePreviews(mediaFile.file_url, 'main_order_file_preview', mediaIndex, mediaFile.id, order.document_status == 'draft' ? false : true);
        });
    }
    renderIcons();
   
    let finalAmendSubmitButton = document.getElementById("amend-submit-button");

    viewModeScript(finalAmendSubmitButton ? false : true);

}


   
function onSeriesChange(element, reset = true)
{
    resetSeries();
    implementSeriesChange(element.value);
    $.ajax({
        url: bookDetails,
        method: 'GET',
        dataType: 'json',
        data: {
            menu_alias: menuAlias,
            service_alias: 'ti',
            book_id : (order && order?.book_id ? order.book_id : null)
        },
        success: function(data) {
            if (data.status == 'success') {
                let newSeriesHTML = ``;
                data.data.forEach((book, bookIndex) => {
                    newSeriesHTML += `<option value = "${book.id}" ${bookIndex == 0 ? 'selected' : ''} >${book.book_code}</option>`;
                });
                document.getElementById('series_id_input').innerHTML = newSeriesHTML;
                getDocNumberByBookId(document.getElementById('series_id_input'), reset);
            } else {
                document.getElementById('series_id_input').innerHTML = '';
            }
        },
        error: function(xhr) {
            console.error('Error fetching customer data:', xhr.responseText);
            document.getElementById('series_id_input').innerHTML = '';
        }
    });
}

function revokeDocument()
    {
        const orderId = order ? order.id : null;
        if (orderId) {
            $.ajax({
            url: revokeUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                id : orderId
            },
            success: function(data) {
                if (data.status == 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                    });
                    location.reload();
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                    });
                    window.location.href = redirect_url;
                }
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
                Swal.fire({
                    title: 'Error!',
                    text: 'Some internal error occured',
                    icon: 'error',
                });
            }
        });
        }
    }


document.addEventListener('DOMContentLoaded', function() {
    onServiceChange(document.getElementById('service_id_input'), order ? false : true);
});

function resetParametersDependentElements(reset = true)
{
    var selectionSection = document.getElementById('selection_section');
    if (selectionSection) {
        selectionSection.style.display = "none";
    }
    var selectionSection = document.getElementById('selection_section');
    if (selectionSection) {
        selectionSection.style.display = "none";
    }
    var selectionSectionSO = document.getElementById('sales_order_selection');
    if (selectionSectionSO) {
        selectionSectionSO.style.display = "none";
    }
    var selectionSectionPl = document.getElementById('pl_selection');
    if (selectionSectionPl) {
        selectionSectionPl.style.display = "none";
    }
    var selectionSectionSI = document.getElementById('sales_invoice_selection');
    if (selectionSectionSI) {
        selectionSectionSI.style.display = "none";
    }
    var selectionSectionSR = document.getElementById('sales_return_selection');
    if (selectionSectionSR) {
        selectionSectionSR.style.display = "none";
    }
    var selectionSectionDN = document.getElementById('delivery_note_selection');
    if (selectionSectionDN) {
        selectionSectionDN.style.display = "none";
    }
    var selectionSectionLease = document.getElementById('land_lease_selection');
    if (selectionSectionLease) {
        selectionSectionLease.style.display = "none";
    }
     var selectionSectionLorryReceipt = document.getElementById('lorry_receipt_selection');
    if (selectionSectionLorryReceipt) {
        selectionSectionLorryReceipt.style.display = "none";
    }
    var selectionSectionPlist = document.getElementById('packing_list_selection');
    if (selectionSectionPlist) {
        selectionSectionPlist.style.display = "none";
    }
    const section = document.getElementById('add_item_section');
    if (section) {
        section.style.display = "none";
    }
    $("#order_date_input").attr('max', endDate);
    $("#order_date_input").attr('min', startDate);
    $("#order_date_input").off('input');
    if (reset) {
        // Set order_date_input to the minimum of today and current_financial_year['end_date']
        var today = moment().format("YYYY-MM-DD");
        var fyEnd = endDate;
        var minDate = (today < fyEnd) ? today : fyEnd;
        $("#order_date_input").val(minDate);
    }        
    $('#order_date_input').on('input', function() {
        restrictBothFutureAndPastDates(this);
    });
}

function getDocNumberByBookId(element, reset = true)
{
    resetParametersDependentElements(reset);
    let bookId = element.value;
    let actionUrl = `${window.routes.docParams}?book_id=${$("#series_id_input").val()}&document_date=${$("#order_date_input").val()}`;

//    let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId + "&document_date=" + $("#order_date_input").val();

    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                $("#book_code_input").val(data.data.book_code);
                if(!data.data.doc.document_number) {
                if (reset) {
                    $("#order_no_input").val('');
                }
                }
                if (reset) {
                $("#order_no_input").val(data.data.doc.document_number);
                }
                if(data.data.doc.type == 'Manually') {
                    $("#order_no_input").attr('readonly', false);
                } else {
                    $("#order_no_input").attr('readonly', true);
                }
                enableDisableQtButton();
                if (data.data.parameters)
                {
                implementBookParameters(data.data.parameters);
                }
                if (reset) {
                    implementBookDynamicFields(data.data.dynamic_fields_html, data.data.dynamic_fields);
                }
                if (typeof locationChange === 'function') {
                    locationChange(document.getElementById('store_id_input'));
                }
            }
            if(data.status == 404) {
                if (reset) {
                    $("#book_code_input").val("");
                    // alert(data.message);
                }
                enableDisableQtButton();
            }
            if(data.status == 500) {
                if (reset) {
                    $("#book_code_input").val("");
                    $("#series_id_input").val("");
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                    });
                }
                enableDisableQtButton();
            }
            if (reset == false) {
                viewModeScript();
            }
        });
    });
}
function enableDisableQtButton()
{
    const bookId = document.getElementById('series_id_input').value;
    const bookCode = document.getElementById('book_code_input').value;
    const documentDate = document.getElementById('order_date_input').value;
    let siButton = document.getElementById('select_si_button');
    let miButton = document.getElementById('select_mi_button');
    let pwoButton = document.getElementById('select_pwo_button');
    let moButton = document.getElementById('select_mo_button');
    let joButton = document.getElementById('select_jo_button');
    let piButton = document.getElementById('select_pi_button');
    let dnButton = document.getElementById('select_dn_button');
    let lrButton = document.getElementById('select_lorry_button');
    let leaseButton = document.getElementById('select_lease_button');
    let orderButton = document.getElementById('select_order_button');
    let plButton = document.getElementById('pl_button');
    let plistButton = document.getElementById('pack_list_button');
    let customerSection = document.getElementById('customer_code_input');

    if (bookId && bookCode && documentDate) {
        if (siButton) {
            siButton.disabled = false;
        }
        if (miButton) {
            miButton.disabled = false;
        }
        if (pwoButton) {
            pwoButton.disabled = false;
        }
        if (moButton) {
            moButton.disabled = false;
        }
        if (joButton) {
            joButton.disabled = false;
        }
        if (piButton) {
            piButton.disabled = false;
        }
        if (dnButton) {
            dnButton.disabled = false;
        }
        if (lrButton) {
            lrButton.disabled = false;
        }
        if (leaseButton) {
            leaseButton.disabled = false;
        }
        if (orderButton) {
            orderButton.disabled = false;
        }
        if (plButton) {
            plButton.disabled = false;
        }
        if (plistButton) {
            plistButton.disabled = false;
        }
        if(customerSection)
        {
            customerSection.disabled = false;
        }
    } else {
        if (siButton) {
            siButton.disabled = true;
        }
        if (miButton) {
            miButton.disabled = true;
        }
        if (pwoButton) {
            pwoButton.disabled = true;
        }
        if (moButton) {
            moButton.disabled = true;
        }
        if (joButton) {
            joButton.disabled = true;
        }
        if (piButton) {
            piButton.disabled = true;
        }
        if (dnButton) {
            dnButton.disabled = true;
        }
        if (leaseButton) {
            leaseButton.disabled = true;
        }
        if (orderButton) {
            orderButton.disabled = true;
        }
        if (plistButton) {
            plistButton.disabled = true;
        }
        if(customerSection)
        {
            customerSection.disabled = true;
        }
    }
}

   

function implementBookDynamicFields(html, data)
{
    let dynamicBookSection = document.getElementById('dynamic_fields_section');
    dynamicBookSection.innerHTML = html;
    if (data && data.length > 0) {
        dynamicBookSection.classList.remove('d-none');
    } else {
        dynamicBookSection.classList.add('d-none');
    }
}
/**
 * Restrict all date inputs to current financial year.
 * Assumes `currentfy` is an object like { start: 'YYYY-MM-DD', end: 'YYYY-MM-DD' }
 */

// Make sure to define `window.currentfy` in your Blade template or HTML before this script runs, e.g.:
// <script>window.currentfy = {!! json_encode($currentfy ?? null) !!};</script>
var currentfy = window.currentfy;
function restrictDateInputsToFY(currentfy) {
    if (!currentfy || !currentfy.start || !currentfy.end) return;
    document.querySelectorAll('input[type="date"]').forEach(input => {
        input.setAttribute('min', currentfy.start);
        input.setAttribute('max', currentfy.end);
    });
}

// Example usage: call after DOMContentLoaded or when currentfy is available
// restrictDateInputsToFY(currentfy);
function onDocDateChange()
{
    let bookId = $("#series_id_input").val();
    let actionUrl = `${window.routes.docParams}?book_id=${$("#series_id_input").val()}&document_date=${$("#order_date_input").val()}`;

    //actionurl let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId + "&document_date=" + $

    $("#order_date_input").val();
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                $("#book_code_input").val(data.data.book_code);
                if(!data.data.doc.document_number) {
                    $("#order_no_input").val('');
                }
                $("#order_no_input").val(data.data.doc.document_number);
                if(data.data.doc.type == 'Manually') {
                    $("#order_no_input").attr('readonly', false);
                } else {
                    $("#order_no_input").attr('readonly', true);
                }
            }
            if(data.status == 404) {
                $("#book_code_input").val("");
                alert(data.message);
            }
        });
    });
}


function implementBookParameters(paramData)
{
    var selectedRefFromServiceOption = paramData.reference_from_service;
    var selectedBackDateOption = paramData.back_date_allowed;
    var selectedFutureDateOption = paramData.future_date_allowed;
    var invoiceToFollowParam = paramData?.invoice_to_follow;
    var issueTypeParameters = paramData?.issue_type;
    var itemTypeParam = paramData?.goods_or_services;
    // const generalInfoTab = document.getElementById('general_information_tab');
    // const subStoreCodeInput = document.getElementById('sub_store_code_header');

   
    // Reference From
    if (selectedRefFromServiceOption) {
        var selectVal = selectedRefFromServiceOption;
        if (selectVal && selectVal.length > 0) {
            selectVal.forEach(selectSingleVal => {
                if (selectSingleVal == 'si') {
                    var selectionSectionElement = document.getElementById('selection_section');
                    if (selectionSectionElement) {
                        selectionSectionElement.style.display = "";
                    }
                    var selectionPopupElement = document.getElementById('sales_invoice_selection');
                    if (selectionPopupElement)
                    {
                        selectionPopupElement.style.display = ""
                    }
                }
                if (selectSingleVal == 'so') {
                    var selectionSectionElement = document.getElementById('selection_section');
                    if (selectionSectionElement) {
                        selectionSectionElement.style.display = "";
                    }
                    var selectionPopupElement = document.getElementById('sales_order_selection');
                    if (selectionPopupElement)
                    {
                        selectionPopupElement.style.display = ""
                    }
                }
                if (selectSingleVal == 'pl') {
                    var selectionSectionElement = document.getElementById('selection_section');
                    if (selectionSectionElement) {
                        selectionSectionElement.style.display = "";
                    }
                    var selectionPopupElement = document.getElementById('pl_selection');
                    if (selectionPopupElement)
                    {
                        selectionPopupElement.style.display = ""
                    }
                }
                if (selectSingleVal == 'd') {
                    // document.getElementById('add_item_section').style.display = "";
                }
                if (selectSingleVal == 'sr') {
                    var selectionSectionElement = document.getElementById('selection_section');
                    if (selectionSectionElement) {
                        selectionSectionElement.style.display = "";
                    }
                    var selectionPopupElement = document.getElementById('sales_return_selection');
                    if (selectionPopupElement)
                    {
                        selectionPopupElement.style.display = ""
                    }
                }
                if (selectSingleVal == 'dnote') {
                    var selectionSectionElement = document.getElementById('selection_section');
                    if (selectionSectionElement) {
                        selectionSectionElement.style.display = "";
                    }
                    var selectionPopupElement = document.getElementById('delivery_note_selection');
                    if (selectionPopupElement)
                    {
                        selectionPopupElement.style.display = ""
                    }
                }
                if (selectSingleVal == 'land-lease') {
                    var selectionSectionElement = document.getElementById('selection_section');
                    if (selectionSectionElement) {
                        selectionSectionElement.style.display = "";
                    }
                    var selectionPopupElement = document.getElementById('land_lease_selection');
                    if (selectionPopupElement)
                    {
                        selectionPopupElement.style.display = ""
                    }
                }
                 if (selectSingleVal == 'lr') {
                    var selectionSectionElement = document.getElementById('selection_section');
                    if (selectionSectionElement) {
                        selectionSectionElement.style.display = "";
                    }
                    var selectionPopupElement = document.getElementById('lorry_receipt_selection');
                    if (selectionPopupElement)
                    {
                        selectionPopupElement.style.display = ""
                    }
                }

                if (selectSingleVal == 'mo') {
                    var selectionSectionElement = document.getElementById('selection_section');
                    if (selectionSectionElement) {
                        selectionSectionElement.style.display = "";
                    }
                    var selectionPopupElement = document.getElementById('mfg_order_selection');
                    if (selectionPopupElement)
                    {
                        selectionPopupElement.style.display = ""
                    }
                }
                if (selectSingleVal == 'pwo') {
                    var selectionSectionElement = document.getElementById('selection_section');
                    if (selectionSectionElement) {
                        selectionSectionElement.style.display = "";
                    }
                    var selectionPopupElement = document.getElementById('pwo_order_selection');
                    if (selectionPopupElement)
                    {
                        selectionPopupElement.style.display = ""
                    }
                }
                if (selectSingleVal == 'purchase-indent') {
                    var selectionSectionElement = document.getElementById('selection_section');
                    if (selectionSectionElement) {
                        selectionSectionElement.style.display = "";
                    }
                    var selectionPopupElement = document.getElementById('pi_order_selection');
                    if (selectionPopupElement)
                    {
                        selectionPopupElement.style.display = ""
                    }
                }
                if (selectSingleVal == 'plist') {
                    var selectionSectionElement = document.getElementById('selection_section');
                    if (selectionSectionElement) {
                        selectionSectionElement.style.display = "";
                    }
                    var selectionPopupElement = document.getElementById('packing_list_selection');
                    if (selectionPopupElement)
                    {
                        selectionPopupElement.style.display = ""
                    }
                }
                if (selectSingleVal == 'd') {
                    let addItemSection = document.getElementById('add_item_section');
                    if (addItemSection) {
                        addItemSection.style.display = "";
                    }
                }
            });
        }
    }

    var backDateAllow = false;
    var futureDateAllow = false;

    //Back Date Allow
    if (selectedBackDateOption) {
        var selectVal = selectedBackDateOption;
        if (selectVal && selectVal.length > 0) {
            if (selectVal[0] == "yes") {
                backDateAllow = true;
            } else {
                backDateAllow = false;
            }
        }
    }

    //Future Date Allow
    if (selectedFutureDateOption) {
        var selectVal = selectedFutureDateOption;
        if (selectVal && selectVal.length > 0) {
            if (selectVal[0] == "yes") {
                futureDateAllow = true;
            } else {
                futureDateAllow = false;
            }
        }
    }

    if (backDateAllow && futureDateAllow) { // Allow both ways (future and past)
        $("#order_date_input").attr('max', endDate);
        $("#order_date_input").attr('min', startDate);
        $("#order_date_input").off('input');
    }
    if (backDateAllow && !futureDateAllow) { // Allow only back date
        $("#order_date_input").removeAttr('min');
        $("#order_date_input").attr('max', endDate);
        $("#order_date_input").off('input');
        $('#order_date_input').on('input', function() {
            restrictFutureDates(this);
        });
    }
    if (!backDateAllow && futureDateAllow) { // Allow only future date
        $("#order_date_input").removeAttr('max');
        $("#order_date_input").attr('min', startDate);
        $("#order_date_input").off('input');
        $('#order_date_input').on('input', function() {
            restrictPastDates(this);
        });
    }

    // if (itemTypeParam && itemTypeParam.length > 0) {
    //     if (generalInfoTab && subStoreCodeInput) {
    //         if (itemTypeParam[0] == "Service") {
    //             generalInfoTab.classList.add('d-none');
    //             subStoreCodeInput.classList.add('d-none');
    //         } else {
    //             generalInfoTab.classList.remove('d-none');
    //             subStoreCodeInput.classList.remove('d-none');

    //         }
    //     }
    // }

    //Issue Type
    if (issueTypeParameters && issueTypeParameters.length > 0) {
        const issueTypeInput = document.getElementById('issue_type_input');
        if (issueTypeInput) {
            var issueTypeHtml = ``;
            var firstIssueType = null;
            issueTypeParameters.forEach((issueType, issueTypeIndex) => {
                if (issueTypeIndex == 0) {
                    firstIssueType = issueType;
                }
                issueTypeHtml += `<option value = '${issueType}'> ${issueType} </option>`
            });
            if (order) {
                firstIssueType = order.issue_type;
            }
            issueTypeInput.innerHTML = issueTypeHtml;
            requesterTypeParam = paramData?.requester_type?.[0];
            $("#requester_type_input").val(requesterTypeParam);
            // $("#issue_type_input").val(firstIssueType).trigger('input');
            let editCase = order ? false : true;
            onIssueTypeChange(document.getElementById('issue_type_input'), editCase == 'false' ? false : true);
        }
    }
    requesterTypeParam = paramData?.requester_type?.[0];
    $("#requester_type_input").val(requesterTypeParam);
}


function setApproval()
{
    document.getElementById('action_type').value = "approve";
    document.getElementById('approve_reject_heading_label').textContent = "Approve " + "Invoice";

}
function setReject()
{
    document.getElementById('action_type').value = "reject";
    document.getElementById('approve_reject_heading_label').textContent = "Reject " + "Invoice";
}
function setFormattedNumericValue(element)
{
    element.value = (parseFloat(element.value ? element.value  : 0)).toFixed(4)
}

$(document).on('click', '#amendmentSubmit', (e) => {
    let actionUrl = amendUrl;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success'
                });
                location.reload();
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error'
                });
            }
        });
    });
});
$(document).ready(function() {
    // Event delegation to handle dynamically added input fields
    $(document).on('input', '.decimal-input', function() {
        // Allow only numbers and a single decimal point
        this.value = this.value.replace(/[^0-9.]/g, ''); // Remove non-numeric characters
       
        // Prevent more than one decimal point
        if ((this.value.match(/\./g) || []).length > 1) {
            this.value = this.value.substring(0, this.value.length - 1);
        }

        // Optional: limit decimal places to 2
        if (this.value.indexOf('.') !== -1) {
            this.value = this.value.substring(0, this.value.indexOf('.') + 3);
        }
    });
});
var currentRevNo = $("#revisionNumber").val();

// # Revision Number On Change
$(document).on('change', '#revisionNumber', (e) => {
    e.preventDefault();
    let actionUrl = location.pathname + '?type=' + '&revisionNumber=' + e.target.value;
    $("#revisionNumber").val(currentRevNo);
    window.open(actionUrl, '_blank'); // Opens in a new tab
});

$(document).on('submit', '.ajax-submit-2', function (e) {
    e.preventDefault();
     var submitButton = (e.originalEvent && e.originalEvent.submitter)
                        || $(this).find(':submit');
    var submitButtonHtml = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    submitButton.disabled = true;
    var method = $(this).attr('method');
    var url = $(this).attr('action');
    var redirectUrl = $(this).data('redirect');
    var data = new FormData($(this)[0]);

    var formObj = $(this);
   
    $.ajax({
        url,
        type: method,
        data,
        contentType: false,
        processData: false,
        success: function (res) {
            submitButton.disabled = false;
            submitButton.innerHTML = submitButtonHtml;
            $('.ajax-validation-error-span').remove();
            $(".is-invalid").removeClass("is-invalid");
            $(".help-block").remove();
            $(".waves-ripple").remove();
            Swal.fire({
                title: 'Success!',
                text: res.message,
                icon: 'success',
            });
            setTimeout(() => {
                if (res.store_id) {
                    location.href = `/stores/${res.store_id}/edit`;
                } else if (redirectUrl) {
                    location.href = redirectUrl;
                } else {
                    location.reload();
                }
            }, 1500);
           
        },
        error: function (error) {
            submitButton.disabled = false;
            submitButton.innerHTML = submitButtonHtml;
            $('.ajax-validation-error-span').remove();
            $(".is-invalid").removeClass("is-invalid");
            $(".help-block").remove();
            $(".waves-ripple").remove();
            let res = error.responseJSON || {};
            if (error.status === 422 && res.errors) {
                if (
                    Object.size(res) > 0 &&
                    Object.size(res.errors) > 0
                ) {
                    show_validation_error(res.errors);
                }
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: res.message || 'An unexpected error occurred.',
                    icon: 'error',
                });
            }
        }
    });
});

function viewModeScript(disable = true) {
    if (orderId && !editOrder) {
        
    document.querySelectorAll('input, textarea, select').forEach(element => {
        if (element.id !== 'revisionNumber' && element.type !== 'hidden' && !element.classList.contains('cannot_disable')) {
            
            if (disable) {
                element.setAttribute('disabled', true);
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    element.setAttribute('readonly', true);
                }
                
            } else {
                element.removeAttribute('disabled');
                element.removeAttribute('readonly');
                $('#series_id_input').prop('disabled', true);
               
            }
        }
    });
    if(disable)
       $('#select_lorry_button').prop('disabled', true);
    else
         $('#select_lorry_button').removeAttr('disabled');

    // Toggle submit & cancel buttons
    document.querySelectorAll('.can_hide').forEach(element => {
        element.style.display = disable ? "none" : "";
    });

    // Toggle add/delete section
    const addDeleteSection = document.getElementById('add_delete_item_section');
    if (addDeleteSection) {
        addDeleteSection.style.display = disable ? "none" : "";
    }}
}


function amendConfirm()
{
    viewModeScript(false);
    disableHeader();
    const amendButton = document.getElementById('amendShowButton');
    if (amendButton) {
        amendButton.style.display = "none";
    }
    //disable other buttons
    var printButton = document.getElementById('dropdownMenuButton');
    if (printButton) {
        printButton.style.display = "none";
    }
    var postButton = document.getElementById('postButton');
    if (postButton) {
        postButton.style.display = "none";
    }
    const buttonParentDiv = document.getElementById('buttonsDiv');
    const newSubmitButton = document.createElement('button');
    newSubmitButton.type = "button";
    newSubmitButton.id = "amend-submit-button";
    newSubmitButton.className = "btn btn-primary btn-sm mb-50 mb-sm-0";
    newSubmitButton.innerHTML = `<i data-feather="check-circle"></i> Submit`;
    newSubmitButton.onclick = function() {
        openAmendConfirmModal();
    };

    if (buttonParentDiv) {
        buttonParentDiv.appendChild(newSubmitButton);
    }

    if (feather) {
        feather.replace({
            width: 14,
            height: 14
        });
    }

    reCheckEditScript();
}

function reCheckEditScript()
{
    if (order) {
        order.items.forEach((item, index) => {
            document.getElementById('item_checkbox_' + index).disabled = item?.is_editable ? false : true;
            document.getElementById('items_dropdown_' + index).readonly = item?.is_editable ? false : true;
            document.getElementById('attribute_button_' + index).disabled = item?.is_editable ? false : true;
        });
    }
}

function openAmendConfirmModal()
{
    $("#amendConfirmPopup").modal("show");
}

function submitAmend()
{
    enableHeader();
    let remark = $("#amendConfirmPopup").find('[name="amend_remarks"]').val();
    $("#action_type_main").val("amendment");
    $("#amendConfirmPopup").modal('hide');
    $("#transport_invoice_form").submit();
}

function invoiceConfirm() {
    var orderId = order ? order.id : null;
    
    if (!orderId) {
        Swal.fire({
            title: 'Error!',
            text: 'Order ID missing!',
            icon: 'error',
        });
        return;
    }

    $.ajax({
        url: invConfirmUrl, // Laravel route
        method: 'POST',
        dataType: 'json',
        data: {
            id: orderId,
            _token: "{{ csrf_token() }}"
        },
        success: function(res) {
            if (res.success) {
                Swal.fire({
                    title: 'Success!',
                    text: res.message,
                    icon: 'success',
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: res.message || "Something went wrong!",
                    icon: 'error',
                });
            }
        },
        error: function(xhr, status, error) {
            console.error(error);
            Swal.fire({
                title: 'Error!',
                text: 'Request failed!',
                icon: 'error',
            });
        }
    });
}


let isProgrammaticChange = false; // Flag to prevent recursion

document.addEventListener('input', function (e) {
    if (e.target.classList.contains('text-end')) {
        if (isProgrammaticChange) {
            return; // Prevent recursion
        }
        let value = e.target.value;

        // Remove invalid characters (anything other than digits and a single decimal)
        value = value.replace(/[^0-9.]/g, '');

        // Prevent more than one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts[1];
        }

        // Prevent starting with a decimal (e.g., ".5" -> "0.5")
        if (value.startsWith('.')) {
            value = '0' + value;
        }

        // Limit to 2 decimal places
        if (parts[1]?.length > 6) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }

        // Prevent exceeding the max limit
        const maxNumericLimit = 9999999; // Define your max limit here
        if (value && Number(value) > maxNumericLimit) {
            value = maxNumericLimit.toString();
        }
        isProgrammaticChange = true; // Set flag before making a programmatic change
        // Update the input's value
        e.target.value = value;

        // Manually trigger the change event
        const event = new Event('input', { bubbles: true });
        e.target.dispatchEvent(event);
        const event2 = new Event('change', { bubbles: true });
        e.target.dispatchEvent(event2);
        isProgrammaticChange = false; // Reset flag after programmatic change
    }
});

document.addEventListener('keydown', function (e) {
    if (e.target.classList.contains('text-end')) {
        if ( e.key === 'Tab' ||
            ['Backspace', 'ArrowLeft', 'ArrowRight', 'Delete', '.'].includes(e.key) ||
            /^[0-9]$/.test(e.key)
        ) {
            // Allow numbers, navigation keys, and a single decimal point
            return;
        }
        e.preventDefault(); // Block everything else
    }
});


function onServiceChange(element, reset = true)
{
    resetSeries();
    $.ajax({
        url:serviceSeriesUrl,
        method: 'GET',
        dataType: 'json',
        data: {
            menu_alias: window.location.pathname.split('/')[1],
            service_alias: 'ti',
            book_id : reset ? null : (order && order?.book_id ? order.book_id : '')
        },
        success: function(data) {
            if (data.status == 'success') {
                let newSeriesHTML = ``;
                data.data.forEach((book, bookIndex) => {
                    newSeriesHTML += `<option value = "${book.id}" ${bookIndex == 0 ? 'selected' : ''} >${book.book_code}</option>`;
                });
                document.getElementById('series_id_input').innerHTML = newSeriesHTML;
                getDocNumberByBookId(document.getElementById('series_id_input'), reset);
            } else {
                document.getElementById('series_id_input').innerHTML = '';
            }
        },
        error: function(xhr) {
            console.error('Error fetching customer data:', xhr.responseText);
            document.getElementById('series_id_input').innerHTML = '';
        }
    });
}
$(document).ready(function() {
    // Event delegation to handle dynamically added input fields
    $(document).on('input', '.decimal-input', function() {
        // Allow only numbers and a single decimal point
        this.value = this.value.replace(/[^0-9.]/g, ''); // Remove non-numeric characters
       
        // Prevent more than one decimal point
        if ((this.value.match(/\./g) || []).length > 1) {
            this.value = this.value.substring(0, this.value.length - 1);
        }

        // Optional: limit decimal places to 2
        if (this.value.indexOf('.') !== -1) {
            this.value = this.value.substring(0, this.value.indexOf('.') + 3);
        }
    });
});
function resetIssueTypeFields()
{
    //Empty the from sub store value and HTML
    // $("#sub_store_from_id_input").val();
    // $("#sub_store_from_id_input").html('');
    //Reset the To Location
    $("#store_to_id_input").val('');
    //Empty the to sub store value and HTML
    $("#sub_store_to_id_input").val('');
    $("#sub_store_to_id_input").html('');
    //Reset Vendor details
    $("#vendor_id_input").val('');
    $("#vendor_store_id_input").val('');
    //Reset vendor details
    $("#department_id_input").val('');
    $("#user_id_dropdown").val('');
}


function openModal(id)
{
    $('#' + id).modal('show');
}

function closeModal(id)
{
    $('#' + id).modal('hide');
}

function submitForm(status) {
    // Create FormData object
    enableHeader();
}
function onItemClick(itemRowId)
{
    console.log(itemRowId);
    const docType = $("#service_id_input").val();
    const invoiceToFollowParam = $("invoice_to_follow_input").val() == "yes";

    const hsn_code = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('hsn_code');
    const item_name = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('item-name');
    const attributes = JSON.parse(document.getElementById('items_dropdown_'+ itemRowId).getAttribute('attribute-array'));
    const specs = JSON.parse(document.getElementById('items_dropdown_'+ itemRowId).getAttribute('specs'));
    // const locations = JSON.parse(decodeURIComponent(document.getElementById('data_stores_'+ itemRowId).getAttribute('data-stores')));

    const qtDetailsRow = document.getElementById('current_item_qt_no_row');
    const qtDetails = document.getElementById('current_item_qt_no');

    //Reference From
    const referenceFromLabels = document.getElementsByClassName("reference_from_label_" + itemRowId);
    if (referenceFromLabels && referenceFromLabels.length > 0)
    {
        qtDetailsRow.style.display = "table-row";
        referenceFromLabelsHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Reference From</strong>`;
        for (let index = 0; index < referenceFromLabels.length; index++) {
            referenceFromLabelsHTML += `<span class="badge rounded-pill badge-light-primary">${referenceFromLabels[index].value}</span>`
        }
        qtDetails.innerHTML = referenceFromLabelsHTML;
    }
    else
    {
        qtDetailsRow.style.display = "none";
        qtDetails.innerHTML = ``;
    }
   

    const leaseAgreementDetailsRow = document.getElementById('current_item_land_lease_agreement_row');
    const leaseAgreementDetails = document.getElementById('current_item_land_lease_agreement');
    if(leaseAgreementDetailsRow || leaseAgreementDetails)
    {

        //assign agreement details
        let agreementNo = document.getElementById('land_lease_agreement_no_' + itemRowId)?.value;
        let leaseEndDate = document.getElementById('land_lease_end_date_' + itemRowId)?.value;
        let leaseDueDate = document.getElementById('land_lease_due_date_' + itemRowId)?.value;
        let repaymentPeriodType = document.getElementById('land_lease_repayment_period_' + itemRowId)?.value;

        if (agreementNo && leaseEndDate && leaseDueDate && repaymentPeriodType) {
            leaseAgreementDetails.style.display = "table-row";
            leaseAgreementDetails.innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Agreement Details</strong>:<span class="badge rounded-pill badge-light-primary"><strong>Agreement No</strong>: ${agreementNo}</span><span class="badge rounded-pill badge-light-primary"><strong>Lease End Date</strong>: ${leaseEndDate}</span><span class="badge rounded-pill badge-light-primary"><strong>Repayment Schedule</strong>: ${repaymentPeriodType}</span><span class="badge rounded-pill badge-light-primary"><strong>Due Date</strong>: ${leaseDueDate}</span>`;
        } else {
            leaseAgreementDetails.style.display = "none";
            leaseAgreementDetails.innerHTML = "";
        }
        //assign land plot details
        let parcelName = document.getElementById('land_lease_land_parcel_' + itemRowId)?.value;
        let plotsName = document.getElementById('land_lease_land_plots_' + itemRowId)?.value;
       
        if (parcelName && plotsName) {
            qtDetails.innerHTML =  qtDetails.innerHTML + `<span class="badge rounded-pill badge-light-primary"><strong>Land Parcel</strong>: ${parcelName}</span><span class="badge rounded-pill badge-light-primary"><strong>Plots</strong>: ${plotsName}</span>`;
        }

    }
        let qtTypeElement = document.getElementById('qt_type_id_' + itemRowId);
        let qtDocumentNo = document.getElementById('qt_document_no_'+ itemRowId);
        let qtBookCode = document.getElementById('qt_book_code_'+ itemRowId);
        let qtDocumentDate = document.getElementById('qt_document_date_'+ itemRowId);
   
        qtDocumentNo = qtDocumentNo?.value ? qtDocumentNo.value : '';
        qtBookCode = qtBookCode?.value ? qtBookCode.value : '';
        qtDocumentDate = qtDocumentDate?.value ? qtDocumentDate.value : '';
        type = qtTypeElement?.value ? qtTypeElement.value : '';

    if (qtDocumentNo && qtBookCode && qtDocumentDate && type != 'lr') {
        qtDetailsRow.style.display = "table-row";
        qtDetails.innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Reference From</strong>:<span class="badge rounded-pill badge-light-primary"><strong>Document No: </strong>: ${qtBookCode + "-" + qtDocumentNo}</span><span class="badge rounded-pill badge-light-primary"><strong>Document Date: </strong>: ${qtDocumentDate}</span>`;
    } else {
        qtDetailsRow.style.display = "none";
        qtDetails.innerHTML = ``;
    }
    // document.getElementById('current_item_hsn_code').innerText = hsn_code;
    var innerHTMLAttributes = ``;
    var specsInnerHTML = ``;
    if(type != 'lr'){
    document.getElementById('current_item_attributes').innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Attributes</strong>:` + innerHTMLAttributes;
    }
   
    if (innerHTMLAttributes && type != 'lr') {
        document.getElementById('current_item_attribute_row').style.display = "table-row";
    } else {
        document.getElementById('current_item_attribute_row').style.display = "none";
    }
    document.getElementById('current_item_specs').innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Specifications</strong>:` + specsInnerHTML;
    if (specsInnerHTML && type != 'lr') {
        document.getElementById('current_item_specs_row').style.display = "table-row";
    } else {
        document.getElementById('current_item_specs_row').style.display = "none";
    }
    const remarks = document.getElementById('item_remarks_' + itemRowId).value;
    if (specsInnerHTML && type != 'lr') {
        document.getElementById('current_item_specs_row').style.display = "table-row";
    } else {
        document.getElementById('current_item_specs_row').style.display = "none";
    }
    document.getElementById('current_item_description').textContent = remarks;
    if (remarks && type != 'lr') {
        document.getElementById('current_item_description_row').style.display = "table-row";
    } else {
        document.getElementById('current_item_description_row').style.display = "none";
    }
    let itemAttributes = JSON.parse(document.getElementById(`items_dropdown_${itemRowId}`).getAttribute('attribute-array'));
    let selectedItemAttr = [];
    if (itemAttributes && itemAttributes.length > 0) {
        itemAttributes.forEach(element => {
        element.values_data.forEach(subElement => {
            if (subElement.selected) {
                selectedItemAttr.push(subElement.id);
            }
        });
    });
    }
    const lrId = document.getElementById('lr_id_' + itemRowId)?.value || '';
    const itemId = document.getElementById('items_dropdown_'+ itemRowId + '_value').value;
    const uomId = document.getElementById('uom_dropdown_'+ itemRowId ).value;
    const qtyrow = document.getElementById('item_picked_qty_' + itemRowId) ?? document.getElementById('item_qty_' + itemRowId);
    console.log(itemId,uomId);
    if (itemId && uomId) {
        $.ajax({
            url: invDets,
            method: 'GET',
            dataType: 'json',
            data: {
                quantity: qtyrow.value,
                type: type,
                lrId: lrId,
                item_id: document.getElementById('items_dropdown_'+ itemRowId + '_value').value,
                uom_id : document.getElementById('uom_dropdown_' + itemRowId).value,
                selectedAttr : selectedItemAttr,
                store_id: $("#store_id_input").val() ? $("#store_id_input").val() : $("#store_from_id_input").val(),
                sub_store_id : $("#sub_store_id_input").val() ? $("#sub_store_id_input").val() : $("#sub_store_from_id_input").val(),
                service_alias : 'psv',
                header_id : order ? order.id : null,
                detail_id : $("#item_row_" + itemRowId).attr('data-detail-id')
            },
            success: function(data) {

                if (Array.isArray(data?.lrDetails) && data.lrDetails.length > 0) {
                    let html = '';
                    let lrs = '';
                   
                    data.lrDetails.forEach(lr => {
                        html += `
                            <span class="badge rounded-pill badge-light-primary">
                                <strong>LR Date</strong>: <span>${lr.document_date}</span>
                            </span>
                            <span class="badge rounded-pill badge-light-primary">
                                <strong>Service</strong>: <span>${lr.item_name}</span>
                            </span>
                            <span class="badge rounded-pill badge-light-primary">
                                <strong>HSN Code</strong>: <span id="current_item_hsn_code">${data.item.hsn.code ?? ''}</span>
                            </span> 
                        `;

                        // ✅ now iterate locations of this LR
                       lr.locations.forEach(location => {
                        // Check agar charo value empty/invalid nahi hai
                        if (
                            (location.route_name && location.route_name !== 'null' && location.route_name !== 'undefined') ||
                            (location.no_of_articles && location.no_of_articles !== 'null' && location.no_of_articles !== 'undefined') ||
                            (location.weight && location.weight !== 'null'  && location.weight !== 'undefined')
                        ) {
                            lrs += `
                            <tr>
                                <td class="poprod-decpt">
                                    <div class="row-line">
                                        ${location.route_name && location.route_name !== 'null' && location.route_name !== 'undefined' ? `
                                        <span class="badge rounded-pill badge-light-primary">
                                            <strong>Point Name</strong>: <span>${location.route_name}</span>
                                        </span>` : ''}

                                        ${location.amount && location.amount !== 'null'  && location.amount !== 'undefined' ? `
                                        <span class="badge rounded-pill badge-light-primary">
                                            <strong>Freight Charges</strong>: <span>${location.amount}</span>
                                        </span>` : ''}

                                        ${location.no_of_articles && location.no_of_articles !== 'null' && location.no_of_articles !== 'undefined' ? `
                                        <span class="badge rounded-pill badge-light-primary">
                                            <strong>No Of articles</strong>: <span>${location.no_of_articles}</span>
                                        </span>` : ''}

                                        ${location.weight && location.weight !== 'null' && location.weight !== 'undefined' ? `
                                        <span class="badge rounded-pill badge-light-primary">
                                            <strong>Weight</strong>: <span>${location.weight}</span>
                                        </span>` : ''}
                                    </div>
                                </td>
                            </tr>
                            `;
                        }
                    });
                    });

                    
                    
                if (data?.item && data?.item?.category && data?.item?.sub_category) {
                   
                }
                    document.getElementById('current_item_cat_hsn').innerHTML = html;

                   
                    let node = document.getElementById("current_item_inventory_details").nextElementSibling;
                    while (node && node.id !== "current_item_specs_row") {
                        const next = node.nextElementSibling;
                        node.remove();
                        node = next;
                    }
                    document.getElementById('current_item_inventory_details').insertAdjacentHTML("afterend", lrs);

                    document.getElementById('current_item_stocks_row').style.display = "none";
                    document.getElementById('current_item_lot_no_row').style.display = "none";

                    return;
                }
                if (data.inv_qty && data.inv_uom) {
                    let inventoryDocElement = document.getElementById('current_item_inventory_details');

                    if (inventoryDocElement) {
                        let newRow = `
                            <tr>
                                <td class="poprod-decpt">
                                    <span class="badge rounded-pill badge-light-primary">
                                        <strong>Inv. UOM</strong>: ${data.inv_uom}
                                    </span>
                                    <span class="badge rounded-pill badge-light-primary">
                                        <strong>Qty in ${data.inv_uom}</strong>: ${data.inv_qty}
                                    </span>
                                </td>
                            </tr>
                        `;

                        inventoryDocElement.insertAdjacentHTML("afterend", newRow);
                    }

                }
                if (data?.item && data?.item?.category && data?.item?.sub_category) {
                    document.getElementById('current_item_cat_hsn').innerHTML = `
                    <span class="badge rounded-pill badge-light-primary"><strong>Category</strong>: <span id = "item_category">${ data?.item?.category?.name}</span></span>
                    <span class="badge rounded-pill badge-light-primary"><strong>Sub Category</strong>: <span id = "item_sub_category">${ data?.item?.sub_category?.name}</span></span>
                    `;
                }


                //Stocks
             if (data?.stocks && (data.stocks.confirmedStockAltUom || data.stocks.pendingStockAltUom)) {
                    document.getElementById('current_item_stocks_row').style.display = "table-row";
                    document.getElementById('current_item_stocks').innerHTML = `
                        <span class="badge rounded-pill badge-light-primary"><strong>Confirmed Stock</strong>: <span id="item_sub_category">${data?.stocks?.confirmedStockAltUom}</span></span>
                        <span class="badge rounded-pill badge-light-primary"><strong>Unconfirmed Stock</strong>: <span id="item_category">${data?.stocks?.pendingStockAltUom}</span></span>
                    `;
                } else {
                }
                 


                    if (data?.lot_details && data.lot_details.length) {
                    document.getElementById('current_item_lot_no_row').style.display = "table-row";
                    let lotHTML = `<strong style="font-size:11px; color : #6a6a6a;">Lot Number</strong> : `;
                    let soHTML = `<strong style="font-size:11px; color : #6a6a6a;">SO Number</strong> : `;
                    const soNoGroups = {};
                    data?.lot_details.forEach(lot => {
                        if (lot.so_no) {
                            if (!soNoGroups[lot.so_no]) {
                                soNoGroups[lot.so_no] = 0;
                            }
                            soNoGroups[lot.so_no] += Number(lot.quantity ?? 0);
                        }
                        lotHTML += `<span class="badge rounded-pill badge-light-primary"><strong>${lot?.lot_number}</strong>: <span>${lot?.quantity}</span></span>`
                    });

                    for (const [soNo, totalQty] of Object.entries(soNoGroups)) {
                        soHTML += `<span class="badge rounded-pill badge-light-primary"><strong>${soNo}</strong> : ${totalQty}</span>`;
                    }

                    document.getElementById('current_item_lot_no').innerHTML = lotHTML;
                    document.getElementById('current_item_so_no').innerHTML = soHTML;
                    }
                    else {
                        document.getElementById('current_item_lot_no_row').style.display = "none";
                    }


                   
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
            }
        });
    }

}

function renderIcons()
{
    feather.replace()
}

function submitAttr(id) {
    var item_index = $('#attributes_table_modal').attr('item-index');
    onItemClick(item_index);
    const input = document.getElementById('item_physical_qty_' + item_index);
    getStoresData(item_index, input ? (input.value ? input.value : 0) : 0);
    setAttributesUI(item_index);
    closeModal(id);
}

$('#attribute').on('hidden.bs.modal', function () {
setAttributesUI();
});
var currentSelectedItemIndex = null ;
function setAttributesUI(paramIndex = null) {
    let editDisable = (order && order?.document_status != 'draft');
    let currentItemIndex = null;
    if (paramIndex != null || paramIndex != undefined) {
        currentItemIndex = paramIndex;
    } else {
        currentItemIndex = currentSelectedItemIndex;
    }
    //Attribute modal is closed
    let itemIdDoc = document.getElementById('items_dropdown_' + currentItemIndex);
    if (!itemIdDoc) {
        return;
    }
    //Item Doc is found
    let attributesArray = itemIdDoc.getAttribute('attribute-array');
    if (!attributesArray) {
        return;
    }
    attributesArray = JSON.parse(attributesArray);
    if (attributesArray.length == 0) {
        return;
    }
    let attributeUI = `<div data-bs-toggle="modal" id="attribute_button_${currentItemIndex}" onclick = "setItemAttributes('items_dropdown_${currentItemIndex}', ${currentItemIndex}, ${editDisable});" data-bs-target="#attribute" style = "white-space:nowrap; cursor:pointer;">`;
    let maxCharLimit = 15;
    let attrTotalChar = 0;
    let total_selected = 0;
    let total_atts = 0;
    let addMore = true;
    attributesArray.forEach(attrArr => {
        if (!addMore) {
            return;
        }
        let short = false;
        total_atts += 1;

        if(attrArr?.short_name?.length > 0)
        {
            short = true;
        }
        //Retrieve character length of attribute name
        let currentStringLength = short ? Number(attrArr.short_name.length) : Number(attrArr.group_name.length);
        let currentSelectedValue = '';
        attrArr.values_data.forEach((attrVal) => {
            if (attrVal.selected === true) {
                total_selected += 1;
                // Add character length with selected value
                currentStringLength += Number(attrVal.value.length);
                currentSelectedValue = attrVal.value;
            }
        });
        //Add the attribute in UI only if it falls within the range
        if ((attrTotalChar + Number(currentStringLength)) <= 15) {
            attributeUI += `
            <span class="badge rounded-pill badge-light-primary"><strong>${short ? attrArr.short_name : attrArr.group_name}</strong>: ${currentSelectedValue ? currentSelectedValue :''}</span>
            `;
        } else {
            //Get the remaining length
            let remainingLength =  15 - attrTotalChar;
            //Only show the data if remaining length is greater than 3
            if (remainingLength >= 3) {
                attributeUI += `<span class="badge rounded-pill badge-light-primary"><strong>${short ? attrArr.short_name.substring(0, remainingLength - 1) : attrArr.group_name.substring(0, remainingLength - 1)}..</strong></span>`
            }
            else {
                addMore = false;

                attributeUI += `<i class="ml-2 fa-solid fa-ellipsis-vertical"></i>`;
            }
        }
        attrTotalChar += Number(currentStringLength);
    });
    let attributeSection = document.getElementById('attribute_section_' + currentItemIndex);
    if (attributeSection) {
        attributeSection.innerHTML = attributeUI + '</div>';
    }
    if(total_selected == 0){
        attributeSection.innerHTML = `
            <input type = "hidden" name = "attribute_value_${currentItemIndex}" />
        `;
    }
}
$('#issues').on('change', function() {
    var issue_id = $(this).val();
    var seriesSelect = $('#series');

    seriesSelect.empty(); // Clear any existing options
    seriesSelect.append('<option value="">Select</option>');

    if (issue_id) {
        $.ajax({
            url: getSeries + issue_id,
            type: "GET",
            dataType: "json",
            success: function(data) {
                $.each(data, function(key, value) {
                    seriesSelect.append('<option value="' + key + '">' + value + '</option>');
                });
            }
        });
    }
});

$('#series').on('change', function() {
    var book_id = $(this).val();
    var request = $('#requestno');

    request.val(''); // Clear any existing options
   
    if (book_id) {
        $.ajax({
            url: getSeries + book_id,
            type: "GET",
            dataType: "json",
            success: function(data)
                {
                    if (data.requestno) {
                    request.val(data.requestno);
                }
            }
        });
    }
});


function onChangeSeries(element)
{
    document.getElementById("order_no_input").value = 12345;
}
function onChangeCustomer(selectElementId, reset = false)
{
    const selectedOption = document.getElementById(selectElementId);
    const paymentTermsDropdown = document.getElementById('payment_terms_dropdown');
    const currencyDropdown = document.getElementById('currency_dropdown');
    if (reset && !selectedOption.value) {
        selectedOption.setAttribute('currency_id', '');
        selectedOption.setAttribute('currency', '');
        selectedOption.setAttribute('currency_code', '');

        selectedOption.setAttribute('payment_terms_id', '');
        selectedOption.setAttribute('payment_terms', '');
        selectedOption.setAttribute('payment_terms_code', '');

        document.getElementById('customer_id_input').value = "";
        document.getElementById('customer_code_input_hidden').value = "";
    }
    //Set Currency
    const currencyId = selectedOption.getAttribute('currency_id');
    const currency = selectedOption.getAttribute('currency');
    const currencyCode = selectedOption.getAttribute('currency_code');
    if (currencyId && currency) {
        const newCurrencyValues = `
            <option value = '${currencyId}' > ${currency} </option>
        `;
        currencyDropdown.innerHTML = newCurrencyValues;
        $("#currency_code_input").val(currencyCode);
    }
    else {
        currencyDropdown.innerHTML = '';
        $("#currency_code_input").val("");
    }
    //Set Payment Terms
    const paymentTermsId = selectedOption.getAttribute('payment_terms_id');
    const paymentTerms = selectedOption.getAttribute('payment_terms');
    const paymentTermsCode = selectedOption.getAttribute('payment_terms_code');
    if (paymentTermsId && paymentTerms) {
        const newPaymentTermsValues = `
            <option value = '${paymentTermsId}' > ${paymentTerms} </option>
        `;
        paymentTermsDropdown.innerHTML = newPaymentTermsValues;
        $("#payment_terms_code_input").val(paymentTermsCode);
    }
    else {
        paymentTermsDropdown.innerHTML = '';
        $("#payment_terms_code_input").val("");
    }
    //Set Location address
    const locationElement = document.getElementById('store_id_input');
    if (locationElement) {
        const displayAddress = locationElement.options[locationElement.selectedIndex].getAttribute('display-address');
        $("#current_pickup_address").text(displayAddress);
    }
    //Get Addresses (Billing + Shipping)
    changeDropdownOptions(document.getElementById('customer_id_input'), ['billing_address_dropdown','shipping_address_dropdown'], ['billing_addresses', 'shipping_addresses'], '/customer/addresses/', 'vendor_dependent');
}

function changeDropdownOptions(mainDropdownElement, dependentDropdownIds, dataKeyNames, routeUrl, resetDropdowns = null, resetDropdownIdsArray = [], extraKeysForRequest = [])
{
    const mainDropdown = mainDropdownElement;
    const secondDropdowns = [];
    const dataKeysForApi = [];
    if (Array.isArray(dependentDropdownIds)) {
        dependentDropdownIds.forEach(elementId => {
            if (elementId.type && elementId.type == "class") {
                const multipleUiDropDowns = document.getElementsByClassName(elementId.value);
                const secondDropdownInternal = [];
                for (let idx = 0; idx < multipleUiDropDowns.length; idx++) {
                    secondDropdownInternal.push(document.getElementById(multipleUiDropDowns[idx].id));
                }
                secondDropdowns.push(secondDropdownInternal);
            } else {
                secondDropdowns.push(document.getElementById(elementId));
            }
        });
    } else {
        secondDropdowns.push(document.getElementById(dependentDropdownIds))
    }

    if (Array.isArray(dataKeyNames)) {
        dataKeyNames.forEach(key => {
            dataKeysForApi.push(key);
        })
    } else {
        dataKeysForApi.push(dataKeyNames);
    }

    if (dataKeysForApi.length !== secondDropdowns.length) {
        console.log("Dropdown function error");
        return;
    }

    if (resetDropdowns) {
        const resetDropdownsElement = document.getElementsByClassName(resetDropdowns);
        for (let index = 0; index < resetDropdownsElement.length; index++) {
            resetDropdownsElement[index].innerHTML = `<option value = '0'>Select</option>`;
        }
    }

    if (resetDropdownIdsArray) {
        if (Array.isArray(resetDropdownIdsArray)) {
            resetDropdownIdsArray.forEach(elementId => {
                let currentResetElement = document.getElementById(elementId);
                if (currentResetElement) {
                    currentResetElement.innerHTML = `<option value = '0'>Select</option>`;
                }
            });
        } else {
            const singleResetElement = document.getElementById(resetDropdownIdsArray);
            if (singleResetElement) {
                singleResetElement.innerHTML = `<option value = '0'>Select</option>`;
            }            
        }
    }
    let apiRequestValue = mainDropdown?.value;
    //Append Extra Key for Data
    if (extraKeysForRequest && extraKeysForRequest.length > 0) {
        extraKeysForRequest.forEach((extraData, index) => {
            apiRequestValue += ((index == 0 ? "?" : "&") + extraData.key) + "=" + (extraData.value);
        });
    }
    const apiUrl = routeUrl + apiRequestValue;
    fetch(apiUrl, {
        method : "GET",
        headers : {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
    }).then(response => response.json()).then(data => {
        if (mainDropdownElement.id == "customer_id_input") {
            if (data?.data?.currency_exchange?.status == false || data?.data?.error_message) {
                Swal.fire({
                    title: 'Error!',
                    text: data?.data?.currency_exchange?.message ? data?.data?.currency_exchange?.message : data?.data?.error_message,
                    icon: 'error',
                });
                mainDropdownElement.value = "";
                document.getElementById('currency_dropdown').innerHTML = "";
                document.getElementById('currency_dropdown').value = "";
                document.getElementById('payment_terms_dropdown').innerHTML = "";
                document.getElementById('payment_terms_dropdown').value = "";
                document.getElementById('current_billing_address_id').value = "";
                // document.getElementById('current_shipping_address_id').value = "";
                document.getElementById('current_billing_address').textContent = "";
                // document.getElementById('current_shipping_address').textContent = "";
                document.getElementById('customer_id_input').value = "";
                document.getElementById('customer_code_input').value = "";
                return;
            }
           
        }
        secondDropdowns.forEach((currentElement, idx) => {
            if (Array.isArray(currentElement)) {
                currentElement.forEach(currentElementInternal => {
                    currentElementInternal.innerHTML = `<option value = '0'>Select</option>`;
                    const response = data.data;
                    response?.[dataKeysForApi[idx]]?.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.value;
                        option.textContent = item.label;
                        currentElementInternal.appendChild(option);
                    })
                });
            } else {
               
                currentElement.innerHTML = `<option value = '0'>Select</option>`;
                const response = data.data;
                response?.[dataKeysForApi[idx]]?.forEach((item, idxx) => {
                    if (idxx == 0) {
                        if (currentElement.id == "billing_address_dropdown") {
                            document.getElementById('current_billing_address').textContent = item.label;
                            document.getElementById('current_billing_address_id').value = item.id;
                            document.getElementById('current_billing_country_id').value = item.country_id;
                            document.getElementById('current_billing_state_id').value = item.state_id;
                            // $('#billing_country_id_input').val(item.country_id).trigger('change');
                            // changeDropdownOptions(document.getElementById('billing_country_id_input'), ['billing_state_id_input'], ['states'], '/states/', null, ['billing_city_id_input']);
                        }
                        if (currentElement.id == "shipping_address_dropdown") {
                            // document.getElementById('current_shipping_address').textContent = item.label;
                            // document.getElementById('current_shipping_address_id').value = item.id;
                            // document.getElementById('current_shipping_country_id').value = item.country_id;
                            // document.getElementById('current_shipping_state_id').value = item.state_id;
                        }

                    }
                    const option = document.createElement('option');
                    option.value = item.value;
                    option.textContent = item.label;
                    if (idxx == 0 && (currentElement.id == "billing_address_dropdown" || currentElement.id == "shipping_address_dropdown")) {
                        option.selected = true;
                    }
                    currentElement.appendChild(option);
                })
            }
            $("#" + mainDropdownElement.id).trigger('ApiCompleted');
        });
    }).catch(error => {
        mainDropdownElement.value = "";
        document.getElementById('currency_dropdown').innerHTML = "";
        document.getElementById('currency_dropdown').value = "";
        document.getElementById('payment_terms_dropdown').innerHTML = "";
        document.getElementById('payment_terms_dropdown').value = "";
        document.getElementById('current_billing_address_id').value = "";
        // document.getElementById('current_shipping_address_id').value = "";
        document.getElementById('current_billing_address').textContent = "";
        // document.getElementById('current_shipping_address').textContent = "";
        document.getElementById('customer_id_input').value = "";
        document.getElementById('customer_code_input').value = "";
        $("#" + mainDropdownElement.id).trigger('ApiCompleted');
        console.log("Error : ", error);
        return;
    })
}


function itemOnChange(selectedElementId, index, routeUrl) // Retrieve element and set item attiributes
{
    const selectedElement = document.getElementById(selectedElementId);
    const ItemIdDocument = document.getElementById(selectedElementId + "_value");
    if (selectedElement && ItemIdDocument) {
        ItemIdDocument.value = selectedElement.dataset?.id;
        const apiRequestValue = selectedElement.dataset?.id;
        const apiUrl = routeUrl + apiRequestValue;
        fetch(apiUrl, {
            method : "GET",
            headers : {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
        }).then(response => response.json()).then(data => {
            const response = data.data;
            selectedElement.setAttribute('attribute-array', JSON.stringify(response.attributes));
            selectedElement.setAttribute('item-name', response.item.item_name);
            document.getElementById('items_name_' + index).value = response.item.item_name;
            selectedElement.setAttribute('hsn_code', (response.item_hsn));
            $("#item_qty_" + index).removeAttr('readonly');
            $("#item_qty_" + index).removeAttr('disabled');
            setItemAttributes('items_dropdown_' + index, index);

            onItemClick(index);

            // if (typeof checkStockData === 'function') {
            //     checkStockData(index);
            // }
            // if (typeof getAndSetItemRate === 'function') {
            //     getAndSetItemRate(index);
            // }
            if (menuAlias == 'delivery-note' || menuAlias == 'sale-invoices' || menuAlias == 'delivery-note-cum-invoice' || menuAlias == 'sinv') {
                getAndSetItemRate(index, 'selling');
            }
            if (menuAlias == 'material-issue') {
                getAndSetItemRate(index, 'cost');
            }
        }).catch(error => {
            console.log("Error : ", error);
        })
    }
}

function setItemAttributes(elementId, index, disabled = false)
{
    document.getElementById('attributes_table_modal').setAttribute('item-index',index);
    var elementIdForDropdown = elementId;
    const dropdown = document.getElementById(elementId);
    const attributesTable = document.getElementById('attribute_table');
    if (dropdown) {
        const attributesJSON = JSON.parse(dropdown.getAttribute('attribute-array'));
        var innerHtml = ``;
        attributesJSON.forEach((element, index) => {
            var optionsHtml = ``;
            element.values_data.forEach(value => {
                optionsHtml += `
                <option value = '${value.id}' ${value.selected ? 'selected' : ''}>${value.value}</option>
                `
            });
            innerHtml += `
            <tr>
            <td>
            ${element.group_name}
            </td>
            <td>
            <select ${disabled ? 'disabled' : ''} class="form-select select2" id = "attribute_val_${index}" style = "max-width:100% !important;" onchange = "changeAttributeVal(this, ${elementIdForDropdown}, ${index});">
                <option>Select</option>
                ${optionsHtml}
            </select>
            </td>
            </tr>
            `
        });
        attributesTable.innerHTML = innerHtml;
        if (attributesJSON.length == 0) {
            document.getElementById('item_qty_' + index).focus();
            document.getElementById('attribute_button_' + index).disabled = true;
        } else {
            $("#attribute").modal("show");
            document.getElementById('attribute_button_' + index).disabled = false;
        }
    }

}

function changeAttributeVal(selectedElement, elementId, index)
{
    const attributesJSON = JSON.parse(elementId.getAttribute('attribute-array'));
    const selectedVal = selectedElement.value;
    attributesJSON.forEach((element, currIdx) => {
        if (currIdx == index) {
            element.values_data.forEach(value => {
            if (value.id == selectedVal) {
                value.selected = true;
            } else {
                value.selected = false;
            }
        });
        }
    });
    elementId.setAttribute('attribute-array', JSON.stringify(attributesJSON));
    if (menuAlias == 'delivery-note' || menuAlias == 'sale-invoices' || menuAlias == 'delivery-note-cum-invoice' || menuAlias == 'sinv') {
        getAndSetItemRate(index, 'selling');
    }
    if (menuAlias == 'material-issue') {
        getAndSetItemRate(index, 'cost');
    }
}

function setItemRemarks(elementId) {
    const currentRemarksValue = document.getElementById(elementId).value;
    const modalInput = document.getElementById('current_item_remarks_input');
    modalInput.value = currentRemarksValue;
    modalInput.setAttribute('current-item', elementId);
}

function changeItemRemarks(element)
{
    var newVal = element.value;
    newVal = newVal.substring(0,255);
    element.value = newVal;
    const elementToBeChanged = document.getElementById(element.getAttribute('current-item'));
    if (elementToBeChanged) {
        elementToBeChanged.value = newVal;
    }
}
 function setItemLot(elementId) {
    $('#lot').modal('show');
}
function changeAllItemsTotal() //All items total value
{
    const elements = document.getElementsByClassName('item_values_input');
    var totalValue = 0;
    for (let index = 0; index < elements.length; index++) {
        totalValue += parseFloat(elements[index].value ? elements[index].value : 0);
    }
    document.getElementById('all_items_total_value').innerText = (totalValue).toFixed(2);
    document.getElementById('all_items_total_value').innerText = (totalValue) ;
}
function changeAllItemsDiscount() //All items total discount
{
    const elements = document.getElementsByClassName('item_discounts_input');
    var totalValue = 0;
    for (let index = 0; index < elements.length; index++) {
        totalValue += parseFloat(elements[index].value ? elements[index].value : 0);
    }
    document.getElementById('all_items_total_discount').innerText = (totalValue).toFixed(2);
    changeAllItemsTotalTotal();
}
function changeAllItemsTotalTotal() //All items total
{
    const elements = document.getElementsByClassName('item_totals_input');
    var totalValue = 0;
    for (let index = 0; index < elements.length; index++) {
        totalValue += parseFloat(elements[index].value ? elements[index].value : 0);
    }
    const totalElements = document.getElementsByClassName('all_tems_total_common');
    for (let index = 0; index < totalElements.length; index++) {
        totalElements[index].innerText = (totalValue).toFixed(2);
    }
}

function changeItemRate(element, index)
{
    var inputNumValue = parseFloat(element.value ? element.value  : 0);
    // if (element.hasAttribute('max'))
    // {
    //     var maxInputVal = parseFloat(element.getAttribute('max'));
    //     if (inputNumValue > maxInputVal) {
    //         Swal.fire({
    //             title: 'Error!',
    //             text: 'Amount cannot be greater than ' + maxInputVal,
    //             icon: 'error',
    //         });
    //         element.value = (parseFloat(maxInputVal ? maxInputVal  : 0)).toFixed(2);
    //         itemRowCalculation(index);
    //         return;
    //     }
    // }
    itemRowCalculation(index);
    if (typeof changeItemRateMi === 'function') {
        changeItemRateMi(element, index);
    }
}

function changeAllItemsTotal() //All items total value
{
    const elements = document.getElementsByClassName('item_values_input');
    var totalValue = 0;
    for (let index = 0; index < elements.length; index++) {
        totalValue += parseFloat(elements[index].value ? elements[index].value : 0);
    }
    document.getElementById('all_items_total_value').innerText = (totalValue).toFixed(2);
    document.getElementById('all_items_total_value').innerText = (totalValue) ;
}
function changeAllItemsDiscount() //All items total discount
{
    const elements = document.getElementsByClassName('item_discounts_input');
    var totalValue = 0;
    for (let index = 0; index < elements.length; index++) {
        totalValue += parseFloat(elements[index].value ? elements[index].value : 0);
    }
    document.getElementById('all_items_total_discount').innerText = (totalValue).toFixed(2);
    changeAllItemsTotalTotal();
}
function changeAllItemsTotalTotal() //All items total
{
    const elements = document.getElementsByClassName('item_totals_input');
    var totalValue = 0;
    for (let index = 0; index < elements.length; index++) {
        totalValue += parseFloat(elements[index].value ? elements[index].value : 0);
    }
    const totalElements = document.getElementsByClassName('all_tems_total_common');
    for (let index = 0; index < totalElements.length; index++) {
        totalElements[index].innerText = (totalValue).toFixed(2);
    }
}

function changeItemRate(element, index)
{
    var inputNumValue = parseFloat(element.value ? element.value  : 0);
    itemRowCalculation(index);
}

function changeItemQty(element, index)
{
    var inputNumValue = parseFloat(element.value ? element.value  : 0);
    if (element.hasAttribute('max'))
    {
        var maxInputVal = parseFloat(element.getAttribute('max'));
        if (inputNumValue > maxInputVal) {
            Swal.fire({
                title: 'Error!',
                text: 'Quantity cannot be greater than ' + maxInputVal,
                icon: 'error',
            });
            element.value = (parseFloat(maxInputVal ? maxInputVal  : 0)).toFixed(2)
            return;
        }
    }
    if (element.hasAttribute('max-stock'))
    {
        var maxInputVal = parseFloat(element.getAttribute('max-stock'));
        if (inputNumValue > maxInputVal) {
            Swal.fire({
                title: 'Error!',
                text: 'Qty cannot be greater than confirmed stock',
                icon: 'error',
            });
            element.value = (parseFloat(maxInputVal ? maxInputVal  : 0)).toFixed(4)
            // return;
        }
    }
    itemRowCalculation(index);
    getStoresData(index, element.value);
    if (typeof changeItemQtyMi === 'function') {
        changeItemQtyMi(element, index);
    }

}
function renderIcons()
{
    feather.replace()
}
function addHiddenInput(id, val, name, classname, docId, dataId = null)
{
    const newHiddenInput = document.createElement("input");
    newHiddenInput.setAttribute("type", "hidden");
    newHiddenInput.setAttribute("name", name);
    newHiddenInput.setAttribute("id", id);
    newHiddenInput.setAttribute("value", val);
    newHiddenInput.setAttribute("class", classname);
    newHiddenInput.setAttribute('data-id', dataId ? dataId : '');
    document.getElementById(docId).appendChild(newHiddenInput);
}
function getStoresData(itemRowId, qty = null, callOnClick = true,islocation=false)
{
    const itemDetailId = document.getElementById('item_row_' + itemRowId).getAttribute('data-detail-id');
    const itemId = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('data-id');
    let itemAttributes = JSON.parse(document.getElementById(`items_dropdown_${itemRowId}`).getAttribute('attribute-array'));
    let selectedItemAttr = [];
    if (itemAttributes && itemAttributes.length > 0) {
        itemAttributes.forEach(element => {
        element.values_data.forEach(subElement => {
            if (subElement.selected) {
                selectedItemAttr.push(subElement.id);
            }
            });
        });
    }
    const storeElement = document.getElementById('data_stores_' + itemRowId);

    // document.getElementById('current_item_stores').innerHTML = ``;
    $.ajax({
        url: storeUrl,
        method: 'GET',
        dataType: 'json',
        data : {
            store_id : $("#item_store_"+itemRowId).val()??($('#store_id_input').val()??$("#store_from_id_input").val()),
        },
        success: function(data) {
            if (data?.stores && data?.stores?.store && data.stores.code == 200) {
                var storesArray = [];
                var dataRecords = data?.stores?.store;
                // dataRecords.forEach(storeData => {
                storesArray.push({
                    store_id : dataRecords.id?dataRecords.id:null,
                    store_code : dataRecords.store_code?dataRecords.store_code:null,
                    rack_data : dataRecords.racks?dataRecords.racks:null,
                    shelf_data : dataRecords.shelf?dataRecords.shelf:null,
                    bin_data : dataRecords.bins?dataRecords.bins:null,
                });
                // });
                if(storeElement)
                {
                    storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(storesArray)));
                }
                if (callOnClick) {
                    onItemClick(itemRowId);
                }
                if(islocation){
                    openStoreLocationModal(itemRowId);
                }
            } else if (data?.stores?.code == 202) {
                Swal.fire({
                    title: 'Error!',
                    text: data?.stores?.message,
                    icon: 'error',
                });
                if(storeElement)
                {
                    storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify([])));
                }
                document.getElementById('item_qty_' + itemRowId).value = 0.00;
                if (callOnClick) {
                    onItemClick(itemRowId);
                }
            } else {
                storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify([])));
                if (callOnClick) {
                    onItemClick(itemRowId);
                }
            }  
        },
        error: function(xhr) {
            console.error('Error fetching customer data:', xhr.responseText);
            storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify([])));
        }
    });
   
}
function openStoreLocationModal(index)
{
    const storeElement = document.getElementById('data_stores_' + index);
    const storeTable = document.getElementById('item_from_location_table');
    let storeFooter = `
    <tr>
        <td colspan="3"></td>
        <td class="text-dark"><strong>Total</strong></td>
        <td class="text-dark" id = "total_item_store_qty"><strong>0.00</strong></td>                                  
    </tr>
    `;
    if (storeElement) {
        storeTable.setAttribute('current-item-index', index);
        let storesInnerHtml = ``;
        let totalStoreQty = 0;
        const storesData = JSON.parse(decodeURIComponent(storeElement.getAttribute('data-stores')));
        if (storesData && storesData.length > 0)
        {
            storesData.forEach((store, storeIndex) => {
                storesInnerHtml += `
                <tr id = "item_store_${storeIndex}">
                    <td>${storeIndex + 1}</td>
                    <td>${store.rack_code ? store.rack_code : "N/A"}</td>
                    <td>${store.shelf_code ? store.shelf_code : "N/A"}</td>
                    <td>${store.bin_code ? store.bin_code : "N/A"}</td>
                    <td>${store.qty}</td>
                </tr>
                `;
                totalStoreQty += (parseFloat(store.qty ? store.qty : 0))
            });

            storeTable.innerHTML = storesInnerHtml + storeFooter;
            document.getElementById('total_item_store_qty').textContent = totalStoreQty.toFixed(2);

        } else {
            storeTable.innerHTML = storesInnerHtml + storeFooter;
            document.getElementById('total_item_store_qty').textContent = "0.00";
        }
    } else {
        return;
    }
    renderIcons();
}


var currentRevNo = $("#revisionNumber").val();

// # Revision Number On Change
$(document).on('change', '#revisionNumber', (e) => {
    e.preventDefault();
    let actionUrl = location.pathname + '?type=' + "{{request() -> type ?? 'si'}}" + '&revisionNumber=' + e.target.value;
    $("#revisionNumber").val(currentRevNo);
    window.open(actionUrl, '_blank'); // Opens in a new tab
});


const maxNumericLimit = 9999999;

document.addEventListener('input', function (e) {
    if (e.target.classList.contains('text-end')) {
        let value = e.target.value;

        // Remove invalid characters (anything other than digits and a single decimal)
        value = value.replace(/[^0-9.]/g, '');

        // Prevent more than one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts[1];
        }

        // Prevent starting with a decimal (e.g., ".5" -> "0.5")
        if (value.startsWith('.')) {
            value = '0' + value;
        }

        // Limit to 2 decimal places
        if (parts[1]?.length > 2) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }

        // Prevent exceeding the max limit
        if (value && Number(value) > maxNumericLimit) {
            value = maxNumericLimit.toString();
        }

        e.target.value = value;
    }
});

document.addEventListener('keydown', function (e) {
    if (e.target.classList.contains('text-end')) {
        if ( e.key === 'Tab' ||
            ['Backspace', 'ArrowLeft', 'ArrowRight', 'Delete', '.'].includes(e.key) ||
            /^[0-9]$/.test(e.key)
        ) {
            // Allow numbers, navigation keys, and a single decimal point
            return;
        }
        e.preventDefault(); // Block everything else
    }
});

$(document).on('click','#billAddressEditBtn',(e) => {
    const addressId = document.getElementById('current_billing_address_id').value;
    const apiRequestValue = addressId;
    const apiUrl = "/customer/address/" + apiRequestValue;
    fetch(apiUrl, {
        method : "GET",
        headers : {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
    }).then(response => response.json()).then(data => {
        if (data) {
            $('#billing_country_id_input').val(data.address.country_id).trigger('change');
            $("#current_billing_address_id").val(data.address.id);
            $("#current_billing_country_id").val(data.address.country_id);
            $("#current_billing_state_id").val(data.address.state_id);
            $("#current_billing_address").text(data.address.display_address);
            setTimeout(() => {
               
                $('#billing_state_id_input').val(data.address.state_id).trigger('change');

                setTimeout(() => {
               
                    $('#billing_city_id_input').val(data.address.city_id).trigger('change');
                }, 1000);
            }, 1000);
            $('#billing_pincode_input').val(data.address.pincode)
            $('#billing_address_input').val(data.address.address);

        }

    }).catch(error => {
        console.log("Error : ", error);
    });
    $("#edit-address-billing").modal('show');
});
function sendMailTo() {
        const customerEmail = order ? order.customer_email : "";
        const customerName = order ? order.customer.company_name : "";
        const emailInput = document.getElementById('cust_mail');
        const header = document.getElementById('send_mail_heading_label');
        if (emailInput) {
            emailInput.value = customerEmail;
        }
        if(header)
        {
            header.innerHTML = "Send Mail";
        }
        $("#mail_remarks").val("");
        $('#sendMail').modal('show');
    }
// $(document).on('click','#shipAddressEditBtn',(e) => {
//     const addressId = document.getElementById('current_shipping_address_id').value;
//     const apiRequestValue = addressId;
//     const apiUrl = "/customer/address/" + apiRequestValue;
//     fetch(apiUrl, {
//         method : "GET",
//         headers : {
//             'Content-Type': 'application/json',
//             'X-CSRF-TOKEN': csrfToken
//         },
//     }).then(response => response.json()).then(data => {
//         if (data) {
//             $('#shipping_country_id_input').val(data.address.country_id).trigger('change');
//             $("#current_shipping_address_id").val(data.address.id);
//             $("#current_shipping_country_id").val(data.address.country_id);
//             $("#current_shipping_state_id").val(data.address.state_id);
//             $("#current_shipping_address").text(data.address.display_address);
//             setTimeout(() => {
               
//                 $('#shipping_state_id_input').val(data.address.state_id).trigger('change');

//                 setTimeout(() => {
               
//                     $('#shipping_city_id_input').val(data.address.city_id).trigger('change');
//                 }, 1000);
//             }, 1000);
//             $('#shipping_pincode_input').val(data.address.pincode)
//             $('#shipping_address_input').val(data.address.address);

//         }

//     }).catch(error => {
//         console.log("Error : ", error);
//     });
//     $("#edit-address-shipping").modal('show');
// });

function itemRowCalculation(itemRowIndex)
{
    const itemQtyInput = document.getElementById('item_qty_' + itemRowIndex);
    const itemRateInput = document.getElementById('item_rate_' + itemRowIndex);
    const itemValueInput = document.getElementById('item_value_' + itemRowIndex);
    const itemDiscountInput = document.getElementById('item_discount_' + itemRowIndex);
    const itemTotalInput = document.getElementById('item_total_' + itemRowIndex);
    //ItemValue
    if(itemDiscountInput && itemTotalInput)
    {

    const itemValue = parseFloat(itemQtyInput.value ? itemQtyInput.value : 0) * parseFloat(itemRateInput.value ? itemRateInput.value : 0);
    itemValueInput.value = (itemValue).toFixed(2);
    //Discount
    let discountAmount = 0;
    const discountHiddenPercentageFields = document.getElementsByClassName('discount_percentages_hidden_' + itemRowIndex);
    const discountHiddenValuesFields = document.getElementsByClassName('discount_values_hidden_' + itemRowIndex);
    const mainDiscountInput = document.getElementsByClassName('item_discount_' + itemRowIndex);
    //Multiple Discount
    for (let index = 0; index < discountHiddenPercentageFields.length; index++) {
        if (discountHiddenPercentageFields[index].value)
        {
            let currentDiscountVal = parseFloat(itemValue ? itemValue : 0) * (parseFloat(discountHiddenPercentageFields[index].value ? discountHiddenPercentageFields[index].value : 0)/100);
            discountHiddenValuesFields[index].value = currentDiscountVal.toFixed(2);
            discountAmount+= currentDiscountVal;
        }
        else
        {
            discountAmount+= parseFloat(discountHiddenValuesFields[index].value ? discountHiddenValuesFields[index].value : 0);
        }
    }
    mainDiscountInput.value = discountAmount;
    //Value after discount
    const valueAfterDiscount = document.getElementById('value_after_discount_' + itemRowIndex);
    const valueAfterDiscountValue = (itemValue - mainDiscountInput.value).toFixed(2);
    valueAfterDiscount.value = valueAfterDiscountValue;
    //Get exact discount amount from order
    // let totalHeaderDiscountAmount = 0;
    // const orderDiscountSummary = document.getElementById('order_discount_summary');
    // if (orderDiscountSummary) {
    //     totalHeaderDiscountAmount = parseFloat(orderDiscountSummary.textContent ? orderDiscountSummary.textContent : 0);
    // }

    //Get total for calculating header discount for each item
    const itemTotalValueAfterDiscount = document.getElementsByClassName('item_val_after_discounts_input');
    let totalValueAfterDiscount = 0;
    for (let index = 0; index < itemTotalValueAfterDiscount.length; index++) {
        totalValueAfterDiscount += parseFloat(itemTotalValueAfterDiscount[index].value ? itemTotalValueAfterDiscount[index].value : 0);
    }

    setModalDiscountTotal('item_discount_' + itemRowIndex, itemRowIndex);

    //Set Header Discount
    updateHeaderDiscounts();
    updateHeaderExpenses();

    //Get exact discount amount from order
    totalHeaderDiscountAmount = 0;
    const orderDiscountSummary = document.getElementById('order_discount_summary');
    if (orderDiscountSummary) {
        totalHeaderDiscountAmount = parseFloat(orderDiscountSummary.textContent ? orderDiscountSummary.textContent : 0);
    }
    let itemHeaderDiscount = (parseFloat(valueAfterDiscountValue ? valueAfterDiscountValue : 0)/ totalValueAfterDiscount) * totalHeaderDiscountAmount;
    itemHeaderDiscount = (parseFloat(itemHeaderDiscount ? itemHeaderDiscount : 0)).toFixed(2);
    //Done
    const headerDiscountInput = document.getElementById('header_discount_' + itemRowIndex);
    headerDiscountInput.value = itemHeaderDiscount;

    const valueAfterHeaderDiscount = document.getElementById('value_after_header_discount_' + itemRowIndex);
    valueAfterHeaderDiscount.value = parseFloat(valueAfterDiscountValue ? valueAfterDiscountValue : 0) - itemHeaderDiscount;

    setModalDiscountTotal('item_discount_' + itemRowIndex, itemRowIndex);

    //Set Header Discount
    updateHeaderDiscounts();

    //Tax
    const taxesHiddenFields = document.getElementsByClassName('item_taxes_input');

    if (taxesHiddenFields && taxesHiddenFields.length > 0) {
        getItemTax(itemRowIndex);
    }
}

}

function updateHeaderDiscounts()
{
    const headerPercentages = document.getElementsByClassName('order_discount_percentage_hidden');
    const headerValues = document.getElementsByClassName('order_discount_value_hidden');
    var allItemTotalValue = 0;
    var allItemTotalValueInputs = document.getElementsByClassName('item_values_input');
    for (let idx1 = 0; idx1 < allItemTotalValueInputs.length; idx1++) {
        allItemTotalValue += parseFloat(allItemTotalValueInputs[idx1].value ? allItemTotalValueInputs[idx1].value : 0);
    }
    var totalItemDiscount = 0;
    var totalItemDiscountInputs = document.getElementsByClassName('item_discounts_input');
    for (let idx1 = 0; idx1 < totalItemDiscountInputs.length; idx1++) {
        totalItemDiscount += parseFloat(totalItemDiscountInputs[idx1].value ? totalItemDiscountInputs[idx1].value : 0);
    }
    let totalAfterItemDiscount = parseFloat(allItemTotalValue ? allItemTotalValue : 0) - parseFloat(totalItemDiscount ? totalItemDiscount : 0);

    let discountAmount = 0;
   
    for (let index = 0; index < headerValues.length; index++) {
        if (headerPercentages[index].value) {
            let currentDiscountVal = totalAfterItemDiscount * (parseFloat(headerPercentages[index].value ? headerPercentages[index].value : 0)/100);
            headerValues[index].value = currentDiscountVal.toFixed(2);
            const tableOrderDiscountValue = document.getElementById('order_discount_input_val_' + index);
            if (tableOrderDiscountValue) {
                tableOrderDiscountValue.textContent = parseFloat(currentDiscountVal).toFixed(2);
            }
            discountAmount+= currentDiscountVal;
        } else {
            discountAmount+= parseFloat(headerValues[index].value ? headerValues[index].value : 0);
        }
    }
    getTotalorderDiscounts(false);

}

function updateHeaderExpenses()
{
    const headerPercentages = document.getElementsByClassName('order_expense_percentage_hidden');
    const headerValues = document.getElementsByClassName('order_expense_value_hidden');
    var totalAfterTax = parseFloat(document.getElementById('all_items_total_after_tax_summary').textContent);

    let expenseAmount = 0;
   
    for (let index = 0; index < headerValues.length; index++) {
        if (headerPercentages[index].value) {
            let currentExpenseVal = totalAfterTax * (parseFloat(headerPercentages[index].value ? headerPercentages[index].value : 0)/100);
            headerValues[index].value = currentExpenseVal.toFixed(2);
            const tableOrderExpenseValue = document.getElementById('order_expense_input_val_' + index);
            if (tableOrderExpenseValue) {
                tableOrderExpenseValue.textContent = (currentExpenseVal).toFixed(2);
            }
            expenseAmount+= currentExpenseVal;
        } else {
            expenseAmount+= parseFloat(headerValues[index].value ? headerValues[index].value : 0);
        }
    }
    getTotalOrderExpenses();

}
function getTax(){
  document.querySelectorAll("#item_header tr").forEach((row, index) => {
     getItemTax(index);
});
}