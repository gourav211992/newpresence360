// === Globals & Routes ===
let selectedSeries = "";
const wo = window.pageData.wo;
const editOrder = window.pageData.editOrder;
const revNoQuery = window.pageData.revNoQuery;
const woId = window.pageData.woId;
const startDate = window.pageData.startDate;
const endDate = window.pageData.endDate;
const today = window.pageData.today;
let csrfToken = window.pageData.csrf_token;
const menuAlias = window.pageData.menu_alias;

let actionUrl = `${window.routes.docParams}?book_id=${$("#book_id").val()}&document_date=${$("#document_date").val()}`;
let storeUrl = window.routes.storeData;
let revokeUrl = window.routes.revoke;
let serviceSeriesUrl = window.routes.serviceSeries;
let bookDetails = window.routes.bookDetails;
let amendUrl = window.routes.amend;
let getSeries = window.routes.getSeries;
let redirectUrl = window.routes.redirectUrl;
let ApiURL = window.routes.ApiURL;

$('#document_date').on('blur', function () {
  if (!checkDateRange(this)) {
    Swal.fire({
      title: 'Error!',
      text: `Date Should Range Between ${startDate} to ${endDate}`,
      icon: 'error',
    });
  }
});

var taxInputs = [];

// === Date Helpers ===
function checkDateRange(element) {
  let date = element.value;
  if (date > endDate || date < startDate) {
    element.value = endDate < today ? endDate : today;
    return false;
  }
  return true;
}
function restrictDateInputsToFY(currentfy) {
  if (!currentfy || !currentfy.start || !currentfy.end) return;
  document.querySelectorAll('input[type="date"]').forEach(input => {
    input.setAttribute('min', currentfy.start);
    input.setAttribute('max', currentfy.end);
  });
}
var currentfy = window.currentfy;

// === Header Enable/Disable ===
function resetSeries() {
  document.getElementById('book_id').innerHTML = '';
}
function disableHeader() {
  const disabledFields = document.getElementsByClassName('disable_on_edit');
  for (let i = 0; i < disabledFields.length; i++) disabledFields[i].disabled = true;
  let dfButton = document.getElementById('select_defect_button');
  if (dfButton) dfButton.disabled = true;
  let eqButton = document.getElementById('select_eqpt_button');
  if (eqButton) eqButton.disabled = true;
}
function enableHeader() {
  const disabledFields = document.getElementsByClassName('disable_on_edit');
  for (let i = 0; i < disabledFields.length; i++) disabledFields[i].disabled = false;
  let dfButton = document.getElementById('select_defect_button');
  if (dfButton) dfButton.disabled = false;
  let eqButton = document.getElementById('select_eqpt_button');
  if (eqButton) eqButton.disabled = false;
}

// === Initializers ===
document.addEventListener('DOMContentLoaded', function () {
  if ((wo && wo.document_status != "draft") || menuAlias != 'pick-list') editScript();
});
document.addEventListener('DOMContentLoaded', function () {
  onServiceChange(document.getElementById('service_id_input'), wo ? false : true);
});

// === Edit/View Mode ===
function editScript() {
  renderIcons();
  let finalAmendSubmitButton = document.getElementById("amend-submit-button");
  viewModeScript(finalAmendSubmitButton ? false : true);
}
function viewModeScript(disable = true) {
  if (woId && !editOrder) {
    document.querySelectorAll('input, textarea, select').forEach(el => {
      if (el.id !== 'revisionNumber' && el.type !== 'hidden' && !el.classList.contains('cannot_disable')) {
        if (disable) {
          el.setAttribute('disabled', true);
          if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') el.setAttribute('readonly', true);
        } else {
          el.removeAttribute('disabled');
          el.removeAttribute('readonly');
          $('#book_id').prop('disabled', true);
        }
      }
    });
    if (disable) $('#equipment_ref_btn').prop('disabled', true); else $('#equipment_ref_btn').removeAttr('disabled');
    if (disable) $('#defect_ref_btn').prop('disabled', true); else $('#defect_ref_btn').removeAttr('disabled');
    document.querySelectorAll('.can_hide').forEach(el => el.style.display = disable ? "none" : "");
    const addDeleteSection = document.getElementById('add_delete_item_section');
    if (addDeleteSection) addDeleteSection.style.display = disable ? "none" : "";
  }
}

// === Series/Book Handling ===
function onSeriesChange(element, reset = true) {
  resetSeries();
  implementSeriesChange(element.value);
  $.ajax({
    url: bookDetails,
    method: 'GET',
    dataType: 'json',
    data: { menu_alias: menuAlias, service_alias: 'ti', book_id: (wo && wo?.book_id ? wo.book_id : null) },
    success: function (data) {
      if (data.status == 'success') {
        let newSeriesHTML = ``;
        data.data.forEach((book, i) => { newSeriesHTML += `<option value="${book.id}" ${i == 0 ? 'selected' : ''}>${book.book_code}</option>`; });
        document.getElementById('book_id').innerHTML = newSeriesHTML;
        getDocNumberByBookId(document.getElementById('book_id'), reset);
      } else {
        document.getElementById('book_id').innerHTML = '';
      }
    },
    error: function () {
      document.getElementById('book_id').innerHTML = '';
    }
  });
}
function resetParametersDependentElements(reset = true) {
  var s1 = document.getElementById('selection_section'); if (s1) s1.style.display = "none";
  var s2 = document.getElementById('selection_section'); if (s2) s2.style.display = "none";
  var s3 = document.getElementById('equipment_ref_btn'); if (s3) s3.style.display = "none";
  var s4 = document.getElementById('defect_ref_btn'); if (s4) s4.style.display = "none";
  if (reset) { var today = moment().format("YYYY-MM-DD"); $("#document_date").val(today); }
  $('#document_date').on('input', function () { restrictBothFutureAndPastDates(this); });
}
function getDocNumberByBookId(element, reset = true) {
  resetParametersDependentElements(reset);
  let actionUrl = `${window.routes.docParams}?book_id=${$("#book_id").val()}&document_date=${$("#document_date").val()}`;
  fetch(actionUrl).then(response => {
    return response.json().then(data => {
      if (data.status == 200) {
        $("#book_code_input").val(data.data.book_code);
        if (!data.data.doc.document_number) { if (reset) $("#document_number").val(''); }
        if (reset) $("#document_number").val(data.data.doc.document_number);
        if (data.data.doc.type == 'Manually') $("#document_number").attr('readonly', false); else $("#document_number").attr('readonly', true);
        enableDisableQtButton();
        if (data.data.parameters) implementBookParameters(data.data.parameters);
      }
      if (data.status == 404) { if (reset) $("#book_code_input").val(""); enableDisableQtButton(); }
      if (data.status == 500) {
        if (reset) {
          $("#book_code_input").val(""); $("#book_id").val("");
          Swal.fire({ title: 'Error!', text: data.message, icon: 'error' });
        }
        enableDisableQtButton();
      }
      if (reset == false) { viewModeScript(); }
    });
  });
}
function enableDisableQtButton() {
  const bookId = document.getElementById('book_id').value;
  const bookCode = document.getElementById('book_code_input').value;
  const documentDate = document.getElementById('document_date').value;
  let eqButton = document.getElementById('equipment_ref_btn');
  let defButton = document.getElementById('defect_ref_btn');
  if (bookId && bookCode && documentDate) { if (eqButton) eqButton.disabled = false; if (defButton) defButton.disabled = false; }
  else { if (defButton) defButton.disabled = true; if (eqButton) eqButton.disabled = true; }
}

// === Book Parameters & Date Rules ===
function implementBookParameters(paramData) {
  var selectedRefFromServiceOption = paramData.reference_from_service;
  var selectedBackDateOption = paramData.back_date_allowed;
  var selectedFutureDateOption = paramData.future_date_allowed;
  selectedSeries = paramData.reference_from_series;

  if (selectedRefFromServiceOption) {
    var selectVal = selectedRefFromServiceOption;
    if (selectVal && selectVal.length > 0) {
      selectVal.forEach(val => {
        if (val == 'defect-notification') {
          var section = document.getElementById('selection_section'); if (section) section.style.display = "";
          var btn = document.getElementById('defect_ref_btn'); if (btn) btn.style.display = "";
        }
        if (val == 'equipment') {
          var section2 = document.getElementById('selection_section'); if (section2) section2.style.display = "";
          var btn2 = document.getElementById('equipment_ref_btn'); if (btn2) btn2.style.display = "";
        }
      });
    }
  }

  var backDateAllow = false, futureDateAllow = false;
  if (selectedBackDateOption) { var v = selectedBackDateOption; backDateAllow = (v && v.length > 0 && v[0] == "yes"); }
  if (selectedFutureDateOption) { var v2 = selectedFutureDateOption; futureDateAllow = (v2 && v2.length > 0 && v2[0] == "yes"); }

  if (backDateAllow && futureDateAllow) {
    $("#document_date").attr('max', endDate);
    $("#document_date").attr('min', startDate);
    $("#document_date").off('input');
  }
  if (backDateAllow && !futureDateAllow) {
    $("#document_date").removeAttr('min');
    $("#document_date").attr('max', endDate);
    $("#document_date").off('input');
    $('#document_date').on('input', function () { restrictFutureDates(this); });
  }
  if (!backDateAllow && futureDateAllow) {
    $("#document_date").removeAttr('max');
    $("#document_date").attr('min', startDate);
    $("#document_date").off('input');
    $('#document_date').on('input', function () { restrictPastDates(this); });
  }
}

// === Approvals & Amendment ===
function setApproval() {
  document.getElementById('action_type').value = "approve";
  document.getElementById('approve_reject_heading_label').textContent = "Approve WO";
}
function setReject() {
  document.getElementById('action_type').value = "reject";
  document.getElementById('approve_reject_heading_label').textContent = "Reject WO";
}
function setFormattedNumericValue(element) {
  element.value = (parseFloat(element.value ? element.value : 0)).toFixed(4)
}
$(document).on('click', '#amendmentSubmit', (e) => {
  e.preventDefault();
  let url = new URL(amendUrl, window.location.origin);
  url.searchParams.set('amendment', 1);
  window.location.href = url.toString();
});
$(document).on('click', '#amendmentBtnSubmit', (e) => {
  e.preventDefault();
  $("#amendmentModal").modal('show');
});
$(document).on('click', '#amendmentModalSubmit', (e) => {
  e.preventDefault();
  let remark = $("#amendmentModal").find('[name="amend_remarks"]').val();
  if (!remark) { $("#amendRemarkError").removeClass("d-none"); return false; }
  $("#amendmentModal").modal('hide');
  $("#amendRemarkError").addClass("d-none");
  $('.preloader').show();
  const form = $('#maint-wo-form');
  form.find('input[name="action_type"]').remove();
  $('<input>').attr({ type: 'hidden', name: 'action_type', value: 'amendment' }).appendTo(form);
  form.submit();
});

// === Numeric Inputs (decimal/text-end) ===
$(document).ready(function () {
  $(document).on('input', '.decimal-input', function () {
    this.value = this.value.replace(/[^0-9.]/g, '');
    if ((this.value.match(/\./g) || []).length > 1) this.value = this.value.substring(0, this.value.length - 1);
    if (this.value.indexOf('.') !== -1) this.value = this.value.substring(0, this.value.indexOf('.') + 3);
  });
});
let isProgrammaticChange = false;
document.addEventListener('input', function (e) {
  if (e.target.classList.contains('text-end')) {
    if (isProgrammaticChange) return;
    let value = e.target.value.replace(/[^0-9.]/g, '');
    const parts = value.split('.');
    if (parts.length > 2) value = parts[0] + '.' + parts[1];
    if (value.startsWith('.')) value = '0' + value;
    if (parts[1]?.length > 6) value = parts[0] + '.' + parts[1].substring(0, 2);
    const maxNumericLimit = 9999999;
    if (value && Number(value) > maxNumericLimit) value = maxNumericLimit.toString();
    isProgrammaticChange = true;
    e.target.value = value;
    e.target.dispatchEvent(new Event('input', { bubbles: true }));
    e.target.dispatchEvent(new Event('change', { bubbles: true }));
    isProgrammaticChange = false;
  }
});
document.addEventListener('keydown', function (e) {
  if (e.target.classList.contains('text-end')) {
    if (e.key === 'Tab' || ['Backspace', 'ArrowLeft', 'ArrowRight', 'Delete', '.'].includes(e.key) || /^[0-9]$/.test(e.key)) return;
    e.preventDefault();
  }
});
const maxNumericLimit = 9999999;
document.addEventListener('input', function (e) {
  if (e.target.classList.contains('text-end')) {
    let value = e.target.value.replace(/[^0-9.]/g, '');
    const parts = value.split('.');
    if (parts.length > 2) value = parts[0] + '.' + parts[1];
    if (value.startsWith('.')) value = '0' + value;
    if (parts[1]?.length > 2) value = parts[0] + '.' + parts[1].substring(0, 2);
    if (value && Number(value) > maxNumericLimit) value = maxNumericLimit.toString();
    e.target.value = value;
  }
});
document.addEventListener('keydown', function (e) {
  if (e.target.classList.contains('text-end')) {
    if (e.key === 'Tab' || ['Backspace', 'ArrowLeft', 'ArrowRight', 'Delete', '.'].includes(e.key) || /^[0-9]$/.test(e.key)) return;
    e.preventDefault();
  }
});

// === Revoke ===
function revokeDocument() {
  const id = wo ? wo.id : null;
  if (!id) return;
  $.ajax({
    url: revokeUrl,
    method: 'POST',
    dataType: 'json',
    data: { id: id },
    success: function (data) {
      if (data.status == 'success') {
        Swal.fire({ title: 'Success!', text: data.message, icon: 'success' });
        location.reload();
      } else {
        Swal.fire({ title: 'Error!', text: data.message, icon: 'error' });
        window.location.href = redirect_url;
      }
    },
    error: function () {
      Swal.fire({ title: 'Error!', text: 'Some internal error occured', icon: 'error' });
    }
  });
}

// === Edit Helpers ===
function reCheckEditScript() {
  if (wo) {
    wo.items.forEach((item, index) => {
      document.getElementById('item_checkbox_' + index).disabled = item?.is_editable ? false : true;
      document.getElementById('items_dropdown_' + index).readonly = item?.is_editable ? false : true;
      document.getElementById('attribute_button_' + index).disabled = item?.is_editable ? false : true;
    });
  }
}
function amendConfirm() {
  viewModeScript(false);
  disableHeader();
  const amendButton = document.getElementById('amendShowButton'); if (amendButton) amendButton.style.display = "none";
  var printButton = document.getElementById('dropdownMenuButton'); if (printButton) printButton.style.display = "none";
  var postButton = document.getElementById('postButton'); if (postButton) postButton.style.display = "none";
  if (feather) feather.replace({ width: 14, height: 14 });
  reCheckEditScript();
}

// === Utilities ===
function openModal(id) { $('#' + id).modal('show'); }
function closeModal(id) { $('#' + id).modal('hide'); }
function submitForm() { enableHeader(); }
function renderIcons() { feather.replace(); }
$('#series').on('change', function () {
  var book_id = $(this).val();
  var request = $('#requestno');
  request.val('');
  if (book_id) {
    $.ajax({
      url: getSeries + book_id,
      type: "GET",
      dataType: "json",
      success: function (data) { if (data.requestno) { request.val(data.requestno); } }
    });
  }
});
function onChangeSeries() { document.getElementById("document_number").value = 12345; }
function addHiddenInput(id, val, name, classname, docId, dataId = null) {
  const el = document.createElement("input");
  el.setAttribute("type", "hidden");
  el.setAttribute("name", name);
  el.setAttribute("id", id);
  el.setAttribute("value", val);
  el.setAttribute("class", classname);
  el.setAttribute('data-id', dataId ? dataId : '');
  document.getElementById(docId).appendChild(el);
}

// === Revision Number (single handler) ===
var currentRevNo = $("#revisionNumber").val();
$(document).on('change', '#revisionNumber', (e) => {
  e.preventDefault();
  let url = location.pathname + '?type=' + '&revisionNumber=' + e.target.value;
  $("#revisionNumber").val(currentRevNo);
  window.open(url, '_blank');
});

// === Service Change (series loader) ===
function onServiceChange(element, reset = true) {
  resetSeries();
  $.ajax({
    url: serviceSeriesUrl,
    method: 'GET',
    dataType: 'json',
    data: {
      menu_alias: window.location.pathname.split('/')[1] + "_" + window.location.pathname.split('/')[2],
      service_alias: 'maint-wo',
      book_id: reset ? null : (wo && wo?.book_id ? wo.book_id : '')
    },
    success: function (data) {
      if (data.status == 'success') {
        let newSeriesHTML = ``;
        data.data.forEach((book, i) => { newSeriesHTML += `<option value="${book.id}" ${i == 0 ? 'selected' : ''}>${book.book_code}</option>`; });
        document.getElementById('book_id').innerHTML = newSeriesHTML;
        getDocNumberByBookId(document.getElementById('book_id'), reset);
      } else {
        document.getElementById('book_id').innerHTML = '';
      }
    },
    error: function () { document.getElementById('book_id').innerHTML = ''; }
  });
};

// === Doc Date Change ===
function onDocDateChange() {
  let actionUrl = `${window.routes.docParams}?book_id=${$("#book_id").val()}&document_date=${$("#document_date").val()}`;
  $("#document_date").val();
  fetch(actionUrl).then(response => {
    return response.json().then(data => {
      if (data.status == 200) {
        $("#book_code_input").val(data.data.book_code);
        if (!data.data.doc.document_number) $("#document_number").val('');
        $("#document_number").val(data.data.doc.document_number);
        if (data.data.doc.type == 'Manually') $("#document_number").attr('readonly', false); else $("#document_number").attr('readonly', true);
      }
      if (data.status == 404) {
        $("#book_code_input").val("");
        alert(data.message);
      }
    });
  });
}

// === Modal Data Loader (defect/equipment) ===
function loadModal(type) {
  $('.defect-type-field').show();
  $("#defectTable").empty();
  $("#eqptTable").empty();
  $.ajax({
    url: ApiURL,
    type: "GET",
    data: { type: type, book_code: selectedSeries },
    dataType: "json",
    success: function (response) {
      if (!Array.isArray(response) || response.length === 0) return;
      if (type === "defect") {
        window.defectModalData = response;
        response.forEach(function (defect, idx) {
          let row = `
            <tr class="trail-bal-tabl-none">
              <td class="customernewsection-form">
                <div class="form-check form-check-primary custom-radio">
                  <input type="radio" class="form-check-input defect-radio" 
                         name="defect_selection" 
                         id="defect_row_${defect.id}" 
                         data-index="${idx}"
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
      } else {
        $('.defect-type-field').hide();
        window.equipmentModalData = response;
        response.forEach(function (eqpt, idx) {
          const isSelected = window.selectedEquipmentState && window.selectedEquipmentState.equipmentId == eqpt.id;
          const checkedAttribute = isSelected ? 'checked' : '';
          let row = `
            <tr class="trail-bal-tabl-none">
              <th class="customernewsection-form">
                <div class="form-check form-check-primary custom-radio">
                  <input type="radio" class="form-check-input equipment-radio" 
                         name="equipment_radio" 
                         id="equipment_${eqpt.id}" 
                         value="${eqpt?.equipment?.id ?? eqpt.id}"
                         data-index="${idx}"
                         data-equipment-id="${eqpt?.equipment?.id ?? eqpt.id}" 
                         data-equipment-name="${eqpt?.equipment?.name ?? ''}" 
                         data-maintenance-type="${eqpt?.maintenance_type?.id ?? ''}"
                         data-bom-id="${eqpt?.bom?.id ?? ''}"
                         ${checkedAttribute}>
                  <label class="form-check-label" for="equipment_${eqpt.id}"></label>
                </div> 
              </th>
              <td><strong>${eqpt?.equipment?.name ?? 'N/A'}</strong></td> 
              <td>${eqpt?.maintenance_type?.name ?? 'N/A'}</td>
              <td>${eqpt?.bom?.bom_name ?? 'N/A'}</td>
              <td>${eqpt?.bom?.book?.book_code ?? 'N/A'}</td>
              <td>${eqpt?.bom?.document_number ?? 'N/A'}</td>
            </tr>`;
          $("#eqptTable").append(row);
        });
      }
    }
  });
}

// === Equipment Modal Population ===
function populateEquipmentModal(response) {
  // Clear existing table
  $("#eqptTable").empty();
  
  // Store data globally for later use
  window.equipmentModalData = response;
  
  if (response && response.length > 0) {
    response.forEach(function (eqpt, idx) {
      const isSelected = window.selectedEquipmentState && window.selectedEquipmentState.equipmentId == eqpt.equipment?.id;
      const checkedAttribute = isSelected ? 'checked' : '';
      let row = `
        <tr class="trail-bal-tabl-none">
          <th class="customernewsection-form">
            <div class="form-check form-check-primary custom-radio">
              <input type="radio" class="form-check-input equipment-radio" 
                     name="equipment_radio" 
                     id="equipment_${eqpt.equipment?.id}" 
                     value="${eqpt.equipment?.id}"
                     data-index="${idx}"
                     data-equipment-id="${eqpt.equipment?.id}" 
                     data-equipment-name="${eqpt.equipment?.name ?? ''}" 
                     data-maintenance-type="${eqpt.maintenance_type?.id ?? ''}"
                     data-bom-id="${eqpt.bom?.id ?? ''}"
                     ${checkedAttribute}>
              <label class="form-check-label" for="equipment_${eqpt.equipment?.id}"></label>
            </div> 
          </th>
          <td><strong>${eqpt.equipment?.name ?? 'N/A'}</strong></td> 
          <td>${eqpt.maintenance_type?.name ?? 'N/A'}</td>
          <td>${eqpt.bom?.bom_name ?? 'N/A'}</td>
          <td><span class="badge badge-info">MAINT_BOM</span> ${eqpt.bom?.book?.book_code ?? 'N/A'}</td>
          <td>${eqpt.bom?.document_number ?? 'N/A'}</td>
        </tr>`;
      $("#eqptTable").append(row);
    });
  } else {
    // Show empty state
    $("#eqptTable").append(`
      <tr>
        <td colspan="6" class="text-center text-muted">
          No equipment found for the selected criteria.
        </td>
      </tr>
    `);
  }
}

// === Checklist Rendering ===
function populateChecklistTable(equipmentData, maintenanceTypeId) {
  $('.mrntableselectexcel1').empty();
  let checklistsData = equipmentData.equipment?.checklists_data || [];
  if (checklistsData && checklistsData.length > 0) {
    let checklistIndex = 1;
    checklistsData.forEach(function (group, groupIndex) {
      let headerRow = `
        <tr>
          <td>${checklistIndex}</td>
          <td colspan="2" class="poprod-decpt p-50"><strong class="font-small-4">${group.main_name}</strong></td>
        </tr>`;
      $('.mrntableselectexcel1').append(headerRow);
      if (group.checklist && group.checklist.length > 0) {
        group.checklist.forEach(function (item, itemIndex) {
          let inputField = createChecklistInputField(item, groupIndex, itemIndex);
          let req = item.mandatory ? '<span class="text-danger">*</span>' : '';
          let row = `
            <tr>
              <td></td>
              <td class="ps-1">
                ${item.name} ${req}
                ${item.description ? `<br><small class="text-muted">${item.description}</small>` : ''}
              </td>
              <td class="poprod-decpt">${inputField}</td>
            </tr>`;
          $('.mrntableselectexcel1').append(row);
        });
      }
      checklistIndex++;
    });
  } else {
    $('.mrntableselectexcel1').append(`<tr><td colspan="3" class="text-center text-muted">No checklist data available for this equipment</td></tr>`);
  }
}
function createChecklistInputField(checklistItem, groupIndex, itemIndex) {
  const fieldName = `checklist_data[${groupIndex}][checklist][${itemIndex}][value]`;
  const fieldId = `checklist_${groupIndex}_${itemIndex}`;
  const isRequired = checklistItem.mandatory ? 'required' : '';
  const currentValue = checklistItem.value || '';
  let hiddenFields = `
    <input type="hidden" name="checklist_data[${groupIndex}][main_name]" value="${checklistItem.name}">
    <input type="hidden" name="checklist_data[${groupIndex}][checklist][${itemIndex}][name]" value="${checklistItem.name}">
    <input type="hidden" name="checklist_data[${groupIndex}][checklist][${itemIndex}][data_type]" value="${checklistItem.data_type}">
    <input type="hidden" name="checklist_data[${groupIndex}][checklist][${itemIndex}][mandatory]" value="${checklistItem.mandatory ? 1 : 0}">
  `;
  let inputField = '';
  switch (checklistItem.data_type) {
    case 'list':
      if (checklistItem.values && checklistItem.values.length == 0) {
        inputField = `
          <select class="form-control mw-100" name="${fieldName}" id="${fieldId}" ${isRequired}>
            <option value="">Select an option</option>
            ${checklistItem.values.map(v => `<option value="${v}" ${currentValue === v ? 'selected' : ''}>${v}</option>`).join('')}
          </select>`;
      } else {
        inputField = `<input type="text" class="form-control mw-100" name="${fieldName}" id="${fieldId}" value="${currentValue}" placeholder="Enter value" ${isRequired}>`;
      }
      break;
    case 'number':
      inputField = `<input type="number" class="form-control mw-100" name="${fieldName}" id="${fieldId}" value="${currentValue}" placeholder="Enter number" ${isRequired}>`;
      break;
    case 'boolean':
    case 'checkbox':
      inputField = `
        <div class="form-check form-check-primary custom-checkbox ms-50">
          <input type="checkbox" class="mt-25 form-check-input" name="${fieldName}" id="${fieldId}" value="1" ${currentValue === '1' || currentValue === 'true' ? 'checked' : ''} ${isRequired}>
          <label class="mb-50 mt-25 form-check-label" for="${fieldId}">Yes/No</label>
        </div>`;
      break;
    case 'date':
      inputField = `<input type="date" class="form-control mw-100" name="${fieldName}" id="${fieldId}" value="${currentValue}" ${isRequired}>`;
      break;
    case 'textarea':
      inputField = `<textarea class="form-control mw-100" name="${fieldName}" id="${fieldId}" rows="3" placeholder="Enter details" ${isRequired}>${currentValue}</textarea>`;
      break;
    default:
      inputField = `<input type="text" class="form-control mw-100" name="${fieldName}" id="${fieldId}" value="${currentValue}" placeholder="Enter text" ${isRequired}>`;
      break;
  }
  return hiddenFields + inputField;
}

// === Equipment Selection & Spare Parts ===
window.selectedEquipmentState = null;
$(document).on('change', 'input[name="equipment_radio"]', function () {
  if (this.checked) {
    window.selectedEquipmentData = {
      equipmentId: $(this).val(),
      bomId: $(this).data('bom-id'),
      equipmentName: $(this).data('equipment-name'),
      maintenanceType: $(this).data('maintenance-type')
    };
  }
});
function fetchEquipmentSpareParts(equipmentId, maintenanceTypeId) {
  showLoadingIndicator();
  $.ajax({
    url: '/plant/maint-wo/get-equipment-spare-parts',
    method: 'GET',
    data: { equipment_id: equipmentId, maintenance_type_id: maintenanceTypeId },
    success: function (response) {
      hideLoadingIndicator();
      if (response.success) {
        populateSparePartsTable(response.data.spare_parts);
        $('#selected_equipment_id').val(response.data.equipment_id);
        $('#selected_bom_id').val(response.data.bom_id);
        $('#selected_maintenance_type_id').val(response.data.maintenance_type_id);
      } else {
        showErrorMessage(response.message || 'Failed to fetch spare parts');
      }
    },
    error: function () {
      hideLoadingIndicator();
      showErrorMessage('Error fetching spare parts data');
    }
  });
}
function populateSparePartsTable(sparePartsData) {
  const tableBody = $('#spareParts');
  tableBody.empty();
  if (!sparePartsData || sparePartsData.length === 0) {
    tableBody.append('<tr><td colspan="6" class="text-center">No spare parts found</td></tr>');
    return;
  }
  sparePartsData.forEach(function (part, index) {
    const row = `
      <tr data-item-id="${part.item_id || ''}" data-index="${index}">
        <td class="customernewsection-form">
          <div class="form-check form-check-primary custom-checkbox">
            <input type="checkbox" class="form-check-input row-check" id="row_${index}">
            <label class="form-check-label" for="row_${index}"></label>
          </div>
        </td>
        <td class="poprod-decpt">
          <input type="hidden" class="item_id" value="${part.item_id || ''}">
          <input required type="text" placeholder="Select" name="item[]" class="item_code form-control mw-100 ledgerselecct mb-25" value="${part.item_code || ''}" />
        </td>
        <td class="poprod-decpt">
          <input type="text" placeholder="Select" class="item_name form-control mw-100 ledgerselecct mb-25" value="${part.item_name || ''}" />
        </td>
        <td class="poprod-decpt">
          <input type="hidden" class="attribute" value='${JSON.stringify(convertToSimpleFormat(part.attributes || []))}'>
          <input type="hidden" class="attribute-enriched" value='${JSON.stringify(part.attributes || [])}'>
          <button type="button" data-bs-toggle="modal" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary attributeBtn" style="font-size:10px">Attributes</button>
        </td>
        <td>
          <select class="uom form-select mw-100" name="uom[]" required>
            <option value="${part.uom_id || ''}" selected>${part.uom_name || part.uom || ''}</option>
          </select>
        </td>
        <td>
          <input type="number" class="qty form-control mw-100" name="qty[]" value="${part.qty || 0}" required />
        </td>
      </tr>`;
    tableBody.append(row);
  });
  $('#spareParts tr[data-index]').off('click').on('click', function () {
    const index = $(this).data('index');
    const partData = sparePartsData[index];
    if (partData) populatePartDetails(partData);
  });
}
function convertToSimpleFormat(attributesDetailed) {
  if (!attributesDetailed || !Array.isArray(attributesDetailed)) return [];
  return attributesDetailed.map(function (attr) {
    return { item_attribute_id: attr.item_attribute_id, value_id: attr.selected_value_id };
  });
}
function populatePartDetails(partData) {
  $('#part_name').text(partData.item_name || '');
  $('#uom').text(partData.uom_name || partData.uom || '');
  $('#qty').text(partData.qty || '0');
  const attributesBadges = $('#attributes_badges');
  attributesBadges.empty();
  if (partData.attributes && partData.attributes.length > 0) {
    partData.attributes.forEach(function (attr) {
      const badge = `<span class="badge rounded-pill badge-light-info me-1 mb-1"><strong>${attr.group_short_name}</strong>: ${attr.selected_value_name}</span>`;
      attributesBadges.append(badge);
    });
  } else {
    attributesBadges.append('<span class="badge rounded-pill badge-light-secondary"></span>');
  }
}
function showLoadingIndicator() {
  if ($('#loading-indicator').length === 0) $('body').append('<div id="loading-indicator" class="loading-overlay">Loading spare parts...</div>');
  $('#loading-indicator').show();
}
function hideLoadingIndicator() { $('#loading-indicator').hide(); }
function showErrorMessage(message) {
  if (typeof Swal !== 'undefined') Swal.fire({ title: 'Error!', text: message, icon: 'error' }); else alert(message);
}

// === Equipment Modal Select & Checklist Hook ===
$(document).on('change', '.equipment-radio', function () {
  const equipmentId = $(this).data('equipment-id');
  const equipmentName = $(this).data('equipment-name');
  const maintenanceTypeId = $(this).data('maintenance-type');
  const equipmentIndex = $(this).data('index');
  window.selectedEquipmentState = { equipmentId, equipmentName, maintenanceTypeId, equipmentIndex, radioId: $(this).attr('id') };
  $('#equipment_id').val(equipmentId);
  $('#equipment_name').val(equipmentName);
  $('#maintenance_type').val(maintenanceTypeId);
  $('input[name="maintenance_type_hidden"]').remove();
  $('<input>').attr({ type: 'hidden', name: 'maintenance_type', value: maintenanceTypeId }).appendTo('#maintenance_type').parent();
  $('.equipment-detail-field').hide();
  $('.basic-equipment-field').show();
  $('#equipment_category').prop('readonly', true);
  $('#equipment_name').prop('readonly', true);
  $('#maintenance_type').prop('disabled', true);
  if (window.equipmentModalData && equipmentIndex !== undefined) {
    const equipmentData = window.equipmentModalData[equipmentIndex];
    if (equipmentData) {
      const equipment = Array.isArray(equipmentData) ? equipmentData[0] : equipmentData;
      if (equipment && equipment.equipment.category && equipment.equipment.category.name) {
        $('#equipment_category').val(equipment.equipment.category.name);
      }
      populateChecklistTable(equipmentData, maintenanceTypeId);
    } else {
      $('.mrntableselectexcel1').empty().append(`<tr><td colspan="3" class="text-center text-muted">No checklist data available for this equipment</td></tr>`);
    }
  } else {
    $('.mrntableselectexcel1').empty().append(`<tr><td colspan="3" class="text-center text-muted">Equipment data not available</td></tr>`);
  }
  $('#equipmentModal').modal('hide');
});
$(document).on('change', '#maintenance_type', function () {
  const selectedMaintenanceTypeId = $(this).val();
  const equipmentId = $('#equipment_id').val();
  if (selectedMaintenanceTypeId && equipmentId && window.equipmentModalData) {
    const equipmentData = window.equipmentModalData.find(eqpt => eqpt.equipment.id == equipmentId);
    if (equipmentData) populateChecklistTable(equipmentData, selectedMaintenanceTypeId);
  } else {
    $('.mrntableselectexcel1').empty().append(`<tr><td colspan="3" class="text-center text-muted">Select equipment and maintenance type to view checklist</td></tr>`);
  }
});

// === Defect Fillers ===
function setInputValue(selector, value) { const v = (value ?? '').toString().trim(); $(selector).val(v); }
function setSelectOptions($select, options, selectedValue = "") {
  const html = ['<option value="">Select</option>'].concat((options || []).map(o => `<option value="${o.id}">${o.name}</option>`)).join('');
  $select.html(html);
  if (selectedValue) $select.val(String(selectedValue));
}
function fillFormFromDefect(defect) {
  $('.defect-type-field, #defect_type_field').show();
  setInputValue('#equipment_category', defect?.category?.name ?? 'N/A');
  setInputValue('#equipment_id', defect?.equipment?.id ?? '');
  setInputValue('#equipment_name', defect?.equipment?.name ?? '');
  const mt = defect?.maintenance_types ?? [];
  setSelectOptions($('#maintenance_type'), mt);
  const defectTypeName = defect?.defect_type?.name ?? '';
  const $defectTypeSelect = $('#defect_type_select');
  if (defectTypeName && $defectTypeSelect.find(`option[value="${defectTypeName}"]`).length === 0) {
    $defectTypeSelect.append(`<option value="${defectTypeName}">${defectTypeName}</option>`);
  }
  $defectTypeSelect.val(defectTypeName || '');
  $('#problem_field input').prop('disabled', false);
  setInputValue('#problem_field input', defect?.problem ?? '');
  $('select[name="priority"]').val(defect?.priority ?? '');
  const reportDT = defect?.report_date_time ? moment(defect.report_date_time).format('DD-MM-YYYY | hh:mm A') : '';
  const $reportInput = $('#report_date_field input');
  $reportInput.prop('disabled', false); setInputValue('#report_date_field input', reportDT); $reportInput.prop('disabled', true);
  const $repBy = $('#report_by_field input');
  $repBy.prop('disabled', false); setInputValue('#report_by_field input', defect?.creator?.name ?? ''); $repBy.prop('disabled', true);
}
function processDefectSelection() {
  const $sel = $('input.defect-radio:checked');
  if ($sel.length === 0) { (window.toastr?.warning && toastr.warning('Please select a defect')) || alert('Please select a defect'); return; }
  const idx = Number($sel.data('index'));
  const list = window.defectModalData || [];
  const picked = list[idx];
  if (!picked) return;
  fillFormFromDefect(picked);
  $('#defectlog').modal('hide');
}

// === Checklist Submit Hook ===
function collectChecklistData() {
  let checklistData = [];
  $('input[name*="checklist_data"][name*="[main_name]"]').each(function () {
    const groupIndex = $(this).attr('name').match(/\[(\d+)\]/)[1];
    const mainName = $(this).val();
    let checklistItems = [];
    $(`input[name*="checklist_data[${groupIndex}][checklist]"], select[name*="checklist_data[${groupIndex}][checklist]"], textarea[name*="checklist_data[${groupIndex}][checklist]"]`).each(function () {
      const fieldName = $(this).attr('name');
      if (fieldName.includes('[value]')) {
        const itemIndex = fieldName.match(/\[checklist\]\[(\d+)\]/)[1];
        const name = $(`input[name="checklist_data[${groupIndex}][checklist][${itemIndex}][name]"]`).val();
        const dataType = $(`input[name="checklist_data[${groupIndex}][checklist][${itemIndex}][data_type]"]`).val();
        const mandatory = $(`input[name="checklist_data[${groupIndex}][checklist][${itemIndex}][mandatory]"]`).val();
        let value = '';
        if ($(this).is(':checkbox')) value = $(this).is(':checked') ? '1' : '0'; else value = $(this).val() || '';
        checklistItems.push({ name: name, data_type: dataType, mandatory: mandatory === '1', value: value });
      }
    });
    if (checklistItems.length > 0) checklistData.push({ main_name: mainName, checklist: checklistItems });
  });
  return checklistData;
}
$(document).on('submit', '#maint-wo-form', function () {
  const checklistData = collectChecklistData();
  $('#checklist_data').val(JSON.stringify(checklistData));
});

// === Misc ===
function onDocDateChange() {} // kept for compatibility if referenced elsewhere
