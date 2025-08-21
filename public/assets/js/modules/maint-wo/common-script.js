let selectedSeries="";
const wo = window.pageData.wo;
const editOrder = window.pageData.editOrder;  // Convert string to boolean
const revNoQuery = window.pageData.revNoQuery;
const woId = window.pageData.woId;
const startDate = window.pageData.startDate;
const endDate = window.pageData.endDate;
const today = window.pageData.today;
let csrfToken = window.pageData.csrf_token;
const menuAlias = window.pageData.menu_alias;
// Assume bookId is already defined

let actionUrl = `${window.routes.docParams}?book_id=${$("#book_id").val()}&document_date=${$("#document_date").val()}`;
let storeUrl = window.routes.storeData;
let revokeUrl = window.routes.revoke;
let serviceSeriesUrl = window.routes.serviceSeries;
let bookDetails = window.routes.bookDetails;
let amendUrl = window.routes.amend;
let getSeries = window.routes.getSeries;
let redirectUrl = window.routes.redirectUrl;
let ApiURL = window.routes.ApiURL;
// Optional: use them in fetch, axios, etc.
$('#document_date').on('blur', function() {
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
    document.getElementById('book_id').innerHTML = '';
}

function disableHeader()
{
    const disabledFields = document.getElementsByClassName('disable_on_edit');
    for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
        disabledFields[disabledIndex].disabled = true;
    }

    let dfButton = document.getElementById('select_defect_button');
    if (dfButton) {
        dfButton.disabled = true;
    }
    let eqButton = document.getElementById('select_eqpt_button');
    if (eqButton) {
        eqButton.disabled = true;
    }

   
}

function enableHeader()
{
    const disabledFields = document.getElementsByClassName('disable_on_edit');
    for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
        disabledFields[disabledIndex].disabled = false;
    }
   let dfButton = document.getElementById('select_defect_button');
    if (dfButton) {
        dfButton.disabled = false;
    }
    let eqButton = document.getElementById('select_eqpt_button');
    if (eqButton) {
        eqButton.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if ((wo && wo.document_status != "draft") || menuAlias != 'pick-list') {
        editScript();
    }
});

function editScript()
{
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
            book_id : (wo && wo?.book_id ? wo.book_id : null)
        },
        success: function(data) {
            if (data.status == 'success') {
                let newSeriesHTML = ``;
                data.data.forEach((book, bookIndex) => {
                    newSeriesHTML += `<option value = "${book.id}" ${bookIndex == 0 ? 'selected' : ''} >${book.book_code}</option>`;
                });
                document.getElementById('book_id').innerHTML = newSeriesHTML;
                getDocNumberByBookId(document.getElementById('book_id'), reset);
            } else {
                document.getElementById('book_id').innerHTML = '';
            }
        },
        error: function(xhr) {
            console.error('Error fetching customer data:', xhr.responseText);
            document.getElementById('book_id').innerHTML = '';
        }
    });
}

function revokeDocument()
    {
        const woId = wo ? wo.id : null;
        if (woId) {
            $.ajax({
            url: revokeUrl,
            method: 'POST',
            dataType: 'json',
            data: {
                id : woId
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
    onServiceChange(document.getElementById('service_id_input'), wo ? false : true);
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
    var selectionSectionEQPT = document.getElementById('equipment_ref_btn');
    if (selectionSectionEQPT) {
        selectionSectionEQPT.style.display = "none";
    }
    var selectionSectionDEF = document.getElementById('defect_ref_btn');
    if (selectionSectionDEF) {
        selectionSectionDEF.style.display = "none";
    }
    if (reset) {
        // Set document_date to the minimum of today and current_financial_year['end_date']
        var today = moment().format("YYYY-MM-DD");
        $("#document_date").val(today);
    }        
    $('#document_date').on('input', function() {
        restrictBothFutureAndPastDates(this);
    });
}

function getDocNumberByBookId(element, reset = true)
{
    resetParametersDependentElements(reset);
    let bookId = element.value;
    let actionUrl = `${window.routes.docParams}?book_id=${$("#book_id").val()}&document_date=${$("#document_date").val()}`;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                $("#book_code_input").val(data.data.book_code);
                if(!data.data.doc.document_number) {
                if (reset) {
                    $("#document_number").val('');
                }
                }
                if (reset) {
                $("#document_number").val(data.data.doc.document_number);
                }
                if(data.data.doc.type == 'Manually') {
                    $("#document_number").attr('readonly', false);
                } else {
                    $("#document_number").attr('readonly', true);
                }
                enableDisableQtButton();
                if (data.data.parameters)
                {
                implementBookParameters(data.data.parameters);
                }
                
               
            }
            if(data.status == 404) {
                if (reset) {
                    $("#book_code_input").val("");
                }
                enableDisableQtButton();
            }
            if(data.status == 500) {
                if (reset) {
                    $("#book_code_input").val("");
                    $("#book_id").val("");
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
    const bookId = document.getElementById('book_id').value;
    const bookCode = document.getElementById('book_code_input').value;
    const documentDate = document.getElementById('document_date').value;
    let eqButton = document.getElementById('equipment_ref_btn');
    let defButton = document.getElementById('defect_ref_btn');

    if (bookId && bookCode && documentDate) {
        if (eqButton) {
            eqButton.disabled = false;
        }
        if (defButton) {
            defButton.disabled = false;
        }
        
    } else {
        if (defButton) {
            defButton.disabled = true;
        }
        if (eqButton) {
            eqButton.disabled = true;
        }
        
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
    let bookId = $("#book_id").val();
    let actionUrl = `${window.routes.docParams}?book_id=${$("#book_id").val()}&document_date=${$("#document_date").val()}`;

    //actionurl let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId + "&document_date=" + $

    $("#document_date").val();
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                $("#book_code_input").val(data.data.book_code);
                if(!data.data.doc.document_number) {
                    $("#document_number").val('');
                }
                $("#document_number").val(data.data.doc.document_number);
                if(data.data.doc.type == 'Manually') {
                    $("#document_number").attr('readonly', false);
                } else {
                    $("#document_number").attr('readonly', true);
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
    selectedSeries = paramData.reference_from_series;
    
   
    // Reference From
    if (selectedRefFromServiceOption) {
        var selectVal = selectedRefFromServiceOption;
        if (selectVal && selectVal.length > 0) {
            selectVal.forEach(selectSingleVal => {
                if (selectSingleVal == 'defect-notification') {
                    var selectionSectionElement = document.getElementById('selection_section');
                    if (selectionSectionElement) {
                        selectionSectionElement.style.display = "";
                    }
                    var selectionPopupElement = document.getElementById('defect_ref_btn');
                    if (selectionPopupElement)
                    {
                        selectionPopupElement.style.display = ""
                    }
                }
                if (selectSingleVal == 'equipment') {
                    var selectionSectionElement = document.getElementById('selection_section');
                    if (selectionSectionElement) {
                        selectionSectionElement.style.display = "";
                    }
                    var selectionPopupElement = document.getElementById('equipment_ref_btn');
                    if (selectionPopupElement)
                    {
                        selectionPopupElement.style.display = ""
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
        $("#document_date").attr('max', endDate);
        $("#document_date").attr('min', startDate);
        $("#document_date").off('input');
    }
    if (backDateAllow && !futureDateAllow) { // Allow only back date
        $("#document_date").removeAttr('min');
        $("#document_date").attr('max', endDate);
        $("#document_date").off('input');
        $('#document_date').on('input', function() {
            restrictFutureDates(this);
        });
    }
    if (!backDateAllow && futureDateAllow) { // Allow only future date
        $("#document_date").removeAttr('max');
        $("#document_date").attr('min', startDate);
        $("#document_date").off('input');
        $('#document_date').on('input', function() {
            restrictPastDates(this);
        });
    }

}


function setApproval()
{
    document.getElementById('action_type').value = "approve";
    document.getElementById('approve_reject_heading_label').textContent = "Approve " + "WO";

}
function setReject()
{
    document.getElementById('action_type').value = "reject";
    document.getElementById('approve_reject_heading_label').textContent = "Reject " + "WO";
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
    if (woId && !editOrder) {
        
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
                $('#book_id').prop('disabled', true);
               
            }
        }
    });
    if(disable)
       $('#equipment_ref_btn').prop('disabled', true);
    else
         $('#equipment_ref_btn').removeAttr('disabled');
    if(disable)
       $('#defect_ref_btn').prop('disabled', true);
    else
         $('#defect_ref_btn').removeAttr('disabled');

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
    if (wo) {
        wo.items.forEach((item, index) => {
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
            menu_alias: window.location.pathname.split('/')[1]+"_"+window.location.pathname.split('/')[2],
            service_alias: 'maint-wo',
            book_id : reset ? null : (wo && wo?.book_id ? wo.book_id : '')
        },
        success: function(data) {
            if (data.status == 'success') {
                let newSeriesHTML = ``;
                data.data.forEach((book, bookIndex) => {
                    newSeriesHTML += `<option value = "${book.id}" ${bookIndex == 0 ? 'selected' : ''} >${book.book_code}</option>`;
                });
                document.getElementById('book_id').innerHTML = newSeriesHTML;
                getDocNumberByBookId(document.getElementById('book_id'), reset);
            } else {
                document.getElementById('book_id').innerHTML = '';
            }
        },
        error: function(xhr) {
            console.error('Error fetching customer data:', xhr.responseText);
            document.getElementById('book_id').innerHTML = '';
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


function renderIcons()
{
    feather.replace()
}



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
    document.getElementById("document_number").value = 12345;
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
function loadModal(type) {
    $('.defect-type-field').show();
    $("#defectTable").empty();
    $("#eqptTable").empty();
    
    $.ajax({
        url: ApiURL,
        type: "GET",
        data: {
            type: type,   // example filter
            book_code: selectedSeries,    // filter by equipment
        },
        dataType: "json",
        success: function(response) {
            if (response.length > 0) {
                if(type=="defect"){
                response.forEach(function(defect) {
                    let row = `
                        <tr class="trail-bal-tabl-none">
                            <td class="customernewsection-form">
                                <div class="form-check form-check-primary custom-radio">
                                    <input type="radio" class="form-check-input" 
                                           name="defect_selection" 
                                           id="defect_row_${defect.id}" 
                                           data-defect-id="${defect.id}"
                                           data-equipment="${defect.equipment?.name ?? 'N/A'}"
                                           data-defect-type="${defect.defect_type?.name ?? 'N/A'}"
                                           data-priority="${defect.priority ?? ''}"
                                           data-problem="${defect.problem ?? ''}"
                                           data-reported-by="${defect.creator?.name ?? 'N/A'}">
                                    <label class="form-check-label" for="defect_row_${defect.id}"></label>
                                </div>
                            </td>
                            <td><strong>${defect.document_date ? moment(defect.document_date).format("DD-MM-YYYY") : 'N/A'}</strong></td>
                            <td>${defect.book?.book_code ?? 'N/A'}</td>
                            <td>${defect.document_number ?? 'N/A'}</td>
                            <td>${defect.equipment?.name ?? 'N/A'}</td>
                            <td>${defect.defect_type?.name ?? 'N/A'}</td>
                            <td>${defect.priority ?? ''}</td>
                            <td>${defect.problem ?? ''}</td>
                            <td>${defect.creator?.name ?? 'N/A'}</td>
                        </tr>`;
                    $("#defectTable").append(row);
                });
            }
            else{
                $('.defect-type-field').hide();
                response.forEach(function(eqpt) {
                let row = `
                
                                        <tr class="trail-bal-tabl-none">
                                        
 											    <th class="customernewsection-form">
													<div class="form-check form-check-primary custom-radio">
														<input type="radio" class="form-check-input equipment-radio" 
                                                        name="equipmentRadio" id="equipment_${eqpt.id}" data-equipment-id="${eqpt.id}" 
                                                        data-equipment-name="${eqpt?.equipment?.name}" 
                                                        data-maintenance-type="${eqpt?.maintenance_type?.id}"
                                                        data-eqpt="${eqpt}">
														<label class="form-check-label" for="equipment_${eqpt.id}"></label>
													</div> 
												</th>
												<td><strong>${eqpt?.equipment?.name}</strong></td> 
                                                <td>${eqpt?.maintenance_type?.name}</td>
												<td>${eqpt?.bom?.bom_name}</td>
												<td>${eqpt?.bom?.book?.book_code}</td>
												<td>${eqpt?.bom?.document_number}</td>
												
											</tr>`;
                                              $("#eqptTable").append(row);
                                            });
                                            
            }
            } 
        }
    });
}


        

