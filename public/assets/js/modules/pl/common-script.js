const order = window.pageData.order;
const editOrder = window.pageData.editOrder === 'true';  // Convert string to boolean
const revNoQuery = window.pageData.revNoQuery === 'true';
const orderId = window.pageData.orderId;
// Assume bookId is already defined

let actionUrl = `${window.routes.docParams}?book_id=${$("#series_id_input").val()}&document_date=${$("#order_date_input").val()}`;

let amendUrl = window.routes.amendSaleOrder;
let revokeUrl = window.routes.revokePSV;
let serviceSeriesUrl = window.routes.serviceSeries;
let invDets = window.routes.invDets;

// Optional: use them in fetch, axios, etc.

function resetSeries()
{
    document.getElementById('series_id_input').innerHTML = '';
}

function disableHeader()
{
    const disabledFields = document.getElementsByClassName('disable_on_edit');
    for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
        disabledFields[disabledIndex].disabled = true;
    }
}

function enableHeader()
{
    const disabledFields = document.getElementsByClassName('disable_on_edit');
        for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
            disabledFields[disabledIndex].disabled = false;
        }
    // let siButton = document.getElementById('select_si_button');
    // if (siButton) {
    //     siButton.disabled = false;
    // }
    let piButton = document.getElementById('select_pi_button');
    if (piButton) {
        piButton.disabled = false;
    }
    let leaseButton = document.getElementById('select_pwo_button');
    if (leaseButton) {
        leaseButton.disabled = false;
    }
    let orderButton = document.getElementById('select_mfg_button');
    if (orderButton) {
        orderButton.disabled = false;
    }
}
if(order && order.document_status != "draft")
{
    editScript();
}

//Function to set values for edit form
function editScript()
{
    localStorage.setItem('deletedItemDiscTedIds', JSON.stringify([]));
    localStorage.setItem('deletedHeaderDiscTedIds', JSON.stringify([]));
    localStorage.setItem('deletedHeaderExpTedIds', JSON.stringify([]));
    localStorage.setItem('deletedSiItemIds', JSON.stringify([]));
    localStorage.setItem('deletedAttachmentIds', JSON.stringify([]));

    if (order) {
        console.log(order);
        //Disable header fields which cannot be changed
        disableHeader();
        if ($("#store_id_input").length) {
            $("#store_id_input").trigger('change');
        }
        //Item Discount
        order.items.forEach((item, itemIndex) => {
            itemUomsHTML = ``;
            if (item.item.uom && item.item.uom.id) {
                itemUomsHTML += `<option selected value = '${item.item.uom.id}' ${item.item.uom.id == item.uom_id ? "selected" : ""}>${item.item.uom.alias}</option>`;
            }
            document.getElementById('uom_dropdown_' + itemIndex).innerHTML = itemUomsHTML;
            onItemClick(itemIndex);
            setAttributesUI(itemIndex);
        });
        //Disable header fields which cannot be changed
        disableHeader();
        //Set all documents
        // order.media_files.forEach((mediaFile, mediaIndex) => {
        //     appendFilePreviews(mediaFile.file_url, 'main_order_file_preview', mediaIndex, mediaFile.id, order.document_status == 'draft' ? false : true);
        // });
    }
    renderIcons();
    
    let finalAmendSubmitButton = document.getElementById("amend-submit-button");

    viewModeScript(finalAmendSubmitButton ? false : true);

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
    
    const section = document.getElementById('add_item_section');
    if (section) {
        section.style.display = "none";
    }
    $("#order_date_input").attr('max', "<?php echo date('Y-m-d'); ?>");
    $("#order_date_input").attr('min', "<?php echo date('Y-m-d'); ?>");
    $("#order_date_input").off('input');
    if (reset) {
        $("#order_date_input").val(moment().format("YYYY-MM-DD"));
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

                if (data.data.parameters)
                {
                implementBookParameters(data.data.parameters);
                }
            }
            if(data.status == 404) {
                if (reset) {
                    $("#book_code_input").val("");
                    // alert(data.message);
                }

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

            }
            if (reset == false) {
                viewModeScript();
            }
        });
    }); 
}

function onDocDateChange()
{
    let bookId = $("#series_id_input").val();
    let actionUrl = `${window.routes.docParams}?book_id=${$("#series_id_input").val()}&document_date=${$("#order_date_input").val()}`;

    //actionurl let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId + "&document_date=" + $

    ("#order_date_input").val();
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
    
    // Reference From
    if (selectedRefFromServiceOption) {
        var selectVal = selectedRefFromServiceOption;
        if (selectVal && selectVal.length > 0) {
            selectVal.forEach(selectSingleVal => {
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
                if (selectSingleVal == 'd') {
                    document.getElementById('add_item_section').style.display = "";
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
        $("#order_date_input").removeAttr('max');
        $("#order_date_input").removeAttr('min');
        $("#order_date_input").off('input');
    } 
    if (backDateAllow && !futureDateAllow) { // Allow only back date
        $("#order_date_input").removeAttr('min');
        $("#order_date_input").attr('max', "<?php echo date('Y-m-d'); ?>");
        $("#order_date_input").off('input');
        $('#order_date_input').on('input', function() {
            restrictFutureDates(this);
        });
    } 
    if (!backDateAllow && futureDateAllow) { // Allow only future date
        $("#order_date_input").removeAttr('max');
        $("#order_date_input").attr('min', "<?php echo date('Y-m-d'); ?>");
        $("#order_date_input").off('input');
        $('#order_date_input').on('input', function() {
            restrictPastDates(this);
        });
    }

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


$(document).on('click', '#amendmentSubmit', (e) => {
    const actionUrl = amendUrl;
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

function viewModeScript(disable = true)
{
    if ((editOrder || revNoQuery) && order) {
        document.querySelectorAll('input, textarea, select').forEach(element => {
            if (element.id !== 'revisionNumber' && element.type !== 'hidden' && !element.classList.contains('cannot_disable')) {
                // element.disabled = disable;
                element.style.pointerEvents = disable ? "none" : "auto";
                if (disable) {
                    element.setAttribute('readonly', true);
                } else {
                    element.removeAttribute('readonly');
                }
            }
        });
        //Disable all submit and cancel buttons
        document.querySelectorAll('.can_hide').forEach(element => {
            element.style.display = disable ? "none" : "";
        });
        //Remove add delete button
        document.getElementById('add_delete_item_section').style.display = disable ? "none" : "";
    } else {
        return;
    }
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
function onServiceChange(element, reset = true)
{
    resetSeries();
    $.ajax({
        url:serviceSeriesUrl,
        method: 'GET',
        dataType: 'json',
        data: {
            menu_alias: window.location.pathname.split('/')[1],
            service_alias: element.value,
            book_id : reset ? null : ""
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
                window.location.href = redirect;
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

function resetIssueTypeFields()
{
    $("#store_to_id_input").val('');
    $("#vendor_id_input").val('');
    $("#vendor_store_id_input").val('');
    $("#department_id_input").val('');
    $("#user_id_dropdown").val('');
    $("#station_id_input").val('');
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
    if(order && order.document_status != "draft")
    {
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

        let qtDocumentNo = document.getElementById('qt_document_no_'+ itemRowId);
        let qtBookCode = document.getElementById('qt_book_code_'+ itemRowId);
        let qtDocumentDate = document.getElementById('qt_document_date_'+ itemRowId);

        qtDocumentNo = qtDocumentNo?.value ? qtDocumentNo.value : '';
        qtBookCode = qtBookCode?.value ? qtBookCode.value : '';
        qtDocumentDate = qtDocumentDate?.value ? qtDocumentDate.value : '';

        // if (qtDocumentNo && qtBookCode && qtDocumentDate) {
        //     qtDetailsRow.style.display = "table-row";
        //     qtDetails.innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Reference From</strong>:<span class="badge rounded-pill badge-light-primary"><strong>Document No: </strong>: ${qtBookCode + "-" + qtDocumentNo}</span><span class="badge rounded-pill badge-light-primary"><strong>Document Date: </strong>: ${qtDocumentDate}</span>`;

        //     if (parcelName && plotsName) {
        //         qtDetails.innerHTML =  qtDetails.innerHTML + `<span class="badge rounded-pill badge-light-primary"><strong>Land Parcel</strong>: ${parcelName}</span><span class="badge rounded-pill badge-light-primary"><strong>Plots</strong>: ${plotsName}</span>`;
        //     }
        // } else {
        //     qtDetailsRow.style.display = "none";
        //     qtDetails.innerHTML = ``;
        // }
        // document.getElementById('current_item_hsn_code').innerText = hsn_code;
        var innerHTMLAttributes = ``;
        attributes.forEach(element => {
            var currentOption = '';
            element.values_data.forEach(subElement => {
                if (subElement.selected) {
                    currentOption = subElement.value;
                }
            });
            innerHTMLAttributes +=  `<span class="badge rounded-pill badge-light-primary"><strong>${element.group_name}</strong>: ${currentOption}</span>`;
        });
        var specsInnerHTML = ``;
        specs.forEach(spec => {
            specsInnerHTML +=  `<span class="badge rounded-pill badge-light-primary "><strong>${spec.specification_name}</strong>: ${spec.value}</span>`;
        });

        document.getElementById('current_item_attributes').innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Attributes</strong>:` + innerHTMLAttributes;
        if (innerHTMLAttributes) {
            document.getElementById('current_item_attribute_row').style.display = "table-row";
        } else {
            document.getElementById('current_item_attribute_row').style.display = "none";
        }
        document.getElementById('current_item_specs').innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Specifications</strong>:` + specsInnerHTML;
        if (specsInnerHTML) {
            document.getElementById('current_item_specs_row').style.display = "table-row";
        } else {
            document.getElementById('current_item_specs_row').style.display = "none";
        }
        const remarks = document.getElementById('item_remarks_' + itemRowId).value;
        if (specsInnerHTML) {
            document.getElementById('current_item_specs_row').style.display = "table-row";
        } else {
            document.getElementById('current_item_specs_row').style.display = "none";
        }
        document.getElementById('current_item_description').textContent = remarks;
        if (remarks) {
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
        const itemId = document.getElementById('items_dropdown_'+ itemRowId + '_value').value;
        const uomId = document.getElementById('uom_dropdown_'+ itemRowId ).value;
        if (itemId && uomId) {
            $.ajax({
                url: invDets,
                method: 'GET',
                dataType: 'json',
                data: {
                    quantity: document.getElementById('item_picked_qty_' + itemRowId).value,
                    item_id: document.getElementById('items_dropdown_'+ itemRowId + '_value').value,
                    uom_id : document.getElementById('uom_dropdown_' + itemRowId).value,
                    selectedAttr : selectedItemAttr,
                    store_id: $("#store_id_input").val(),
                    sub_store_id : $("#sub_store_id_input").val(),
                    service_alias : 'psv',
                    header_id : order ? order.id : null,
                    detail_id : $("#item_row_" + itemRowId).attr('data-detail-id')
                },
                success: function(data) {
                    
                    if (data?.item && data?.item?.category && data?.item?.sub_category) {
                        document.getElementById('current_item_cat_hsn').innerHTML = `
                        <span class="badge rounded-pill badge-light-primary"><strong>Category</strong>: <span id = "item_category">${ data?.item?.category?.name}</span></span>
                        <span class="badge rounded-pill badge-light-primary"><strong>Sub Category</strong>: <span id = "item_sub_category">${ data?.item?.sub_category?.name}</span></span>
                        <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>: <span id = "current_item_hsn_code">${hsn_code}</span></span>
                        `;
                    }
                    //Stocks
                    if (data?.stocks) {
                        document.getElementById('current_item_stocks_row').style.display = "table-row";
                        document.getElementById('current_item_stocks').innerHTML = `
                        <span class="badge rounded-pill badge-light-primary"><strong>Confirmed Stock</strong>: <span id = "item_sub_category">${data?.stocks?.confirmedStockAltUom}</span></span>
                        <span class="badge rounded-pill badge-light-primary"><strong>Unconfirmed Stock</strong>: <span id = "item_category">${data?.stocks?.pendingStockAltUom}</span></span>
                        `;

                        inputQtyBox.setAttribute('max-stock',data.stocks.confirmedStockAltUom);
                        } 
                        else {
                            // document.getElementById('current_item_stocks_row').style.display = "none";
                        }

                    //     if (data?.lot_details) {
                    //     document.getElementById('current_item_lot_no_row').style.display = "table-row";
                    //     let lotHTML = `<strong style="font-size:11px; color : #6a6a6a;">Lot Number</strong> : `;
                    //     let soHTML = `<strong style="font-size:11px; color : #6a6a6a;">SO Number</strong> : `;
                    //     const soNoGroups = {};
                    //     data?.lot_details.forEach(lot => {
                    //         if (lot.so_no) {
                    //             if (!soNoGroups[lot.so_no]) {
                    //                 soNoGroups[lot.so_no] = 0;
                    //             }
                    //             soNoGroups[lot.so_no] += Number(lot.quantity ?? 0);
                    //         }
                    //         lotHTML += `<span class="badge rounded-pill badge-light-primary"><strong>${lot?.lot_number}</strong>: <span>${lot?.quantity}</span></span>`
                    //     });

                    //     for (const [soNo, totalQty] of Object.entries(soNoGroups)) {
                    //         soHTML += `<span class="badge rounded-pill badge-light-primary"><strong>${soNo}</strong> : ${totalQty}</span>`;
                    //     }

                    //     document.getElementById('current_item_lot_no').innerHTML = lotHTML;
                    //     document.getElementById('current_item_so_no').innerHTML = soHTML;
                    //     } 
                    //  else {
                    //         document.getElementById('current_item_lot_no_row').style.display = "none";
                    //     }


                        
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        }
    }
}

function renderIcons()
{
    feather.replace()
}

function submitAttr(id) {
    var item_index = $('#attributes_table_modal').attr('item-index');
    console.log('item-index',item_index);
    onItemClick(item_index);
    const input = document.getElementById('item_physical_qty_' + item_index);
    console.log(input);
    getStoresData(item_index, input ? (input.value ?? 0) : 0);
    setAttributesUI(item_index);
    closeModal(id);
}

$('#attribute').on('hidden.bs.modal', function () {
setAttributesUI();
});
var currentSelectedItemIndex = null ;
function setAttributesUI(paramIndex = null) {
    let currentItemIndex = null;
    if (paramIndex != null || paramIndex != undefined) {
        currentItemIndex = paramIndex;
    } else {
        currentItemIndex = currentSelectedItemIndex;
    }
    console.log('current-item-index',currentItemIndex);
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
    let attributeUI = `<div data-bs-toggle="modal" id="attribute_button_${currentItemIndex}" onclick = "setItemAttributes('items_dropdown_${currentItemIndex}', ${currentItemIndex});" data-bs-target="#attribute" style = "white-space:nowrap; cursor:pointer;">`;
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
        console.log(attrArr);

        if(attrArr.short_name.length > 0)
        {
            short = true;
        }
        //Retrieve character length of attribute name
        let currentStringLength = short ? Number(attrArr.short_name.length) : Number(attrArr.group_name.length);
        let currentSelectedValue = '';
        attrArr.values_data.forEach((attrVal) => {
            if (attrVal.selected === true) {
                total_selected += 1;
                console.log('in If' , total_selected);
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
    console.log(attributeSection,'section before if');
    if (attributeSection) {
        attributeSection.innerHTML = attributeUI + '</div>';
        console.log(attributeSection,'section after if');
    }
    console.log('before If' , total_selected);
    if(total_selected == 0){
        attributeSection.innerHTML = `
            <button id = "attribute_button_${currentItemIndex}" 
                ${attributesArray.length > 0 ? '' : 'disabled'} 
                type = "button" 
                data-bs-toggle="modal" 
                onclick = "setItemAttributes('items_dropdown_${currentItemIndex}', '${currentItemIndex}', false);" 
                data-bs-target="#attribute" 
                class="btn p-25 btn-sm btn-outline-secondary" 
                style="font-size: 10px">Attributes</button>
            <input type = "hidden" name = "attribute_value_${currentItemIndex}" />
        `;
    }
    
}
