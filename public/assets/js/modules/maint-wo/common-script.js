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
              <td>${eqpt?.equipment?.due_date ?? 'N/A'}</td>
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
  console.log("chck the response here", response);
  
  
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
          <td>${eqpt.equipment?.due_date ?? 'N/A'}</td>
        </tr>`;
      $("#eqptTable").append(row);
    });
  } else {
    // Show empty state
    $("#eqptTable").append(`
      <tr>
        <td colspan="7" class="text-center text-muted">
          No equipment found for the selected criteria.
        </td>
      </tr>
    `);
  }
}

// === Checklist Rendering ===
function populateChecklistTable(equipmentData, maintenanceTypeId) {
  console.log('populateChecklistTable called with:', equipmentData, maintenanceTypeId);
  $('.mrntableselectexcel1').empty();
  let checklistsData = equipmentData.equipment?.checklists_data || [];
  console.log('checklistsData:', checklistsData);
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
         <select class="form-control mw-100" name="${fieldName}" id="${fieldId}" ${isRequired}>
              <option value="">Select an option</option>
              <option value="1" ${currentValue == 1 ? 'selected' : ''}>Yes</option>
              <option value="0" ${currentValue == 0 ? 'selected' : ''}>No</option>
            </select>`;
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
// Transform attributes from controller format to data-attr format
function transformAttributesForDataAttr(attributes) {
  if (!attributes || !Array.isArray(attributes)) {
    return [];
  }
  
  return attributes.map(attr => ({
    id: attr.item_attribute_id,
    group_name: attr.group_name,
    values_data: attr.all_values || []
  }));
}

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

  console.log("check the spare parts", sparePartsData);
  const tableBody = $('.mrntableselectexcel');
  tableBody.empty();
  if (!sparePartsData || sparePartsData.length === 0) {
    tableBody.append('<tr><td colspan="7" class="text-center">No spare parts found</td></tr>');
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
          <input required type="text" placeholder="Select" name="item[]" class="item_code form-control mw-100 ledgerselecct mb-25" value="${part.item_code || ''}" data-attr='${JSON.stringify(transformAttributesForDataAttr(part.attributes || []))}' />
        </td>
        <td class="poprod-decpt">
          <input type="text" placeholder="Select" class="item_name form-control mw-100 ledgerselecct mb-25" value="${part.item_name || ''}" />
        </td>
        <td class="poprod-decpt">
          <input type="hidden" class="attribute" value='${JSON.stringify(convertToSimpleFormat(part.attributes || []))}'>
          <input type="hidden" class="attribute-enriched" value='${JSON.stringify(part.attributes || [])}'>
          <div class="d-flex flex-wrap gap-1" id="attribute-badges-${index}">
            ${
              (() => {
                const attributes = part.attributes || [];
                const validAttributes = attributes.filter(attr => 
                  (attr.group_name || attr.type) && (attr.selected_value_name || attr.value)
                );
                
                if (validAttributes.length === 0) {
                  return '';
                }
                
                let badgesHtml = '';
                let displayedCount = 0;
                
                // Show up to 2 badges
                validAttributes.forEach(attr => {
                  if (displayedCount < 2) {
                    displayedCount++;
                    badgesHtml += `<span class="badge rounded-pill badge-light-primary" style="font-size:10px; margin-right:5px;cursor:pointer">
                                    <strong>${attr.group_name || attr.type || 'Type'}</strong>: ${attr.selected_value_name || attr.value || ''}
                                  </span>`;
                  }
                });
                
                // Add "..." if there are more than 2 attributes
                if (validAttributes.length > 2) {
                  badgesHtml += '<span style="font-size:10px; color:black; margin-right:5px;cursor:pointer"><strong>...</strong></span>';
                }
                
                return badgesHtml;
              })()
            }
          </div>
        </td>
        <td>
          <select class="uom form-select mw-100" name="uom[]" required>
            <option value="${part.uom_id || ''}" selected>${part.uom_name || part.uom || ''}</option>
          </select>
        </td>
        <td>
          <input type="number" class="qty form-control mw-100" name="qty[]" value="${part.qty || 0}" required />
        </td>
        <td>
          <input type="number" class="available_stock form-control mw-100" name="available_stock[]" value="${part.available_stock || 0}" readonly />
        </td>
      </tr>`;
    tableBody.append(row);
  });

  // Initialize autocomplete for fetched spare parts
  $('.mrntableselectexcel .item_code').each(function() {
    if (typeof initAutoForItem === 'function') {
      initAutoForItem(this, 'item');
      console.log('Initialized autocomplete for fetched spare part');
    }
  });

  // Use event delegation for AJAX loaded spare parts to work with create.blade.php click handler
  $('.mrntableselectexcel tr[data-index]').off('click').on('click', function () {
    // First handle row selection (same as create.blade.php)
    $(this).addClass('trselected').siblings().removeClass('trselected');
    
    // Then call the part details function if it exists (from create.blade.php)
    if (typeof updateFooterFromSelected === 'function') {
      updateFooterFromSelected();
      console.log('Called updateFooterFromSelected from AJAX handler');
    } else {
      // Fallback to populatePartDetails if updateFooterFromSelected not available
      const index = $(this).data('index');
      const partData = sparePartsData[index];
      if (partData) {
        populatePartDetails(partData);
        console.log('Called populatePartDetails from AJAX handler');
      }
    }
  });

  // Auto-select first row and show its details immediately after AJAX load
  if (sparePartsData && sparePartsData.length > 0) {
    setTimeout(() => {
      const $firstRow = $('.mrntableselectexcel tr[data-index="0"]');
      if ($firstRow.length) {
        // Select first row
        $firstRow.addClass('trselected').siblings().removeClass('trselected');
        
        // Show its details automatically
        if (typeof updateFooterFromSelected === 'function') {
          updateFooterFromSelected();
          console.log('Auto-selected first spare part and updated details');
        } else {
          // Fallback to populatePartDetails
          const firstPartData = sparePartsData[0];
          if (firstPartData) {
            populatePartDetails(firstPartData);
            console.log('Auto-selected first spare part with populatePartDetails');
          }
        }
      }
    }, 100); // Small delay to ensure DOM is ready
  }
}

// ✅ COMMENTED OUT: Spare parts click handler moved to create.blade.php (like maint BOM)
// Event delegation for all spare parts rows (including newly added ones)
/*
$(document).on('click', '#spareParts tr', function () {
  const $row = $(this);
  
  // Skip if it's a header row or doesn't have form inputs
  if ($row.find('.item_code').length === 0) return;
  
  // Get data from the row inputs
  const partData = {
    item_name: $row.find('.item_name').val() || 'N/A',
    item_code: $row.find('.item_code').val() || '',
    uom_name: $row.find('.uom option:selected').text() || 'N/A',
    uom: $row.find('.uom').val() || '',
    qty: $row.find('.qty').val() || '0',
    item_id: $row.find('.item_id').val() || '',
    attributes: []
  };
  
  // Get attributes if available
  const attributeInput = $row.find('.attribute');
  if (attributeInput.length && attributeInput.val()) {
    try {
      const attributes = JSON.parse(attributeInput.val());
      partData.attributes = attributes.map(attr => ({
        group_name: attr.attribute_name || 'Attribute',
        selected_value_name: attr.attribute_value || 'N/A'
      }));
    } catch (e) {
      console.error('Error parsing attributes:', e);
    }
  }
  
  // ✅ FIXED: Set autocomplete field exactly as in maint BOM create (static approach)
  if (partData.item_code && partData.item_name) {
    const $input = $row.find('.item_code');
    
    // Set values exactly as maint BOM select event (lines 912-921)
    $input.attr('data-name', partData.item_name);
    $input.attr('data-code', partData.item_code);
    $input.attr('data-id', partData.item_id);
    if (attributeInput.length && attributeInput.val()) {
      $input.attr('data-attr', attributeInput.val());
    }
    
    // Set form field values (exactly as maint BOM lines 916-918)
    $input.closest('tr').find('.item_id').val(partData.item_id);
    $input.closest('tr').find('.item_name').val(partData.item_name);
    $input.val(partData.item_code);
    
    // Set UOM dropdown (exactly as maint BOM line 920-921)
    // Comment out dynamic data - use static approach
    // const uomId = $row.find('.uom').val();
    // const uomName = $row.find('.uom option:selected').text();
    
    // Use static UOM data from partData
    if (partData.uom && partData.uom_name) {
      let uomOption = `<option value="${partData.uom}">${partData.uom_name}</option>`;
      $input.closest('tr').find('.uom').empty().append(uomOption);
    }
    
    // Trigger attribute button (exactly as maint BOM lines 923-930)
    setTimeout(() => {
      $input.closest('tr').find('.attributeBtn').trigger('click');
      $input.closest('tr').find('.qty').val('').focus();
    }, 100);
  }
  
  // Populate part details
  populatePartDetails(partData);
});
*/

function convertToSimpleFormat(attributesDetailed) {
  if (!attributesDetailed || !Array.isArray(attributesDetailed)) return [];
  return attributesDetailed.map(function (attr) {
    return { item_attribute_id: attr.item_attribute_id, value_id: attr.selected_value_id };
  });
}

function populatePartDetails(partData) {
  console.log("check the partData",partData);
  
  // Populate part name
  const partNameHtml = `<span class="badge rounded-pill badge-light-secondary"><strong>Name</strong>: ${partData.item_name || 'N/A'}</span>`;
  $('#current_part_name').html(partNameHtml);
  
  // Populate UOM
  const uomHtml = `<span class="badge rounded-pill badge-light-secondary"><strong>UOM</strong>: ${partData.uom_name || partData.uom || 'N/A'}</span>`;
  $('#current_part_uom').html(uomHtml);
  
  // Populate quantity
  const qtyHtml = `<span class="badge rounded-pill badge-light-secondary"><strong>Quantity</strong>: ${partData.qty || '0'}</span>`;
  $('#current_part_qty').html(qtyHtml);
  
  // Populate attributes
  const attributesContainer = $('#current_part_attributes');
  attributesContainer.empty();
  
  if (partData.attributes && partData.attributes.length > 0) {
    let attributesHtml = '';
    partData.attributes.forEach(function (attr) {
      attributesHtml += `<span class="badge rounded-pill badge-light-primary me-1 mb-1"><strong>${attr.group_name || attr.group_short_name || 'Attribute'}</strong>: ${attr.selected_value_name || attr.value || 'N/A'}</span>`;
    });
    attributesContainer.html(attributesHtml);
  } else {
    attributesContainer.html('<span class="badge rounded-pill badge-light-secondary">No attributes selected</span>');
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
  console.log('window.equipmentModalData:', window.equipmentModalData);
  console.log('equipmentIndex:', equipmentIndex);
  if (window.equipmentModalData && equipmentIndex !== undefined) {
    const equipmentData = window.equipmentModalData[equipmentIndex];
    console.log('Selected equipmentData:', equipmentData);
    if (equipmentData) {
      const equipment = Array.isArray(equipmentData) ? equipmentData[0] : equipmentData;
      if (equipment && equipment.equipment.category && equipment.equipment.category.name) {
        $('#equipment_category').val(equipment.equipment.category.name);
      }
      
      // Populate equipment_details hidden field
      const equipmentDetails = {
        equipment_id: equipmentId,
        equipment_name: equipmentName,
        equipment_category: equipment?.equipment?.category?.name || '',
        maintenance_type_id: maintenanceTypeId,
        maintenance_type_name: equipment?.maintenance_type?.name || '',
        reference_type: 'equipment'
      };
      $('#equipment_details').val(JSON.stringify(equipmentDetails));
      console.log('Equipment details populated:', equipmentDetails);
      
      populateChecklistTable(equipmentData, maintenanceTypeId);
    } else {
      console.log('No equipmentData found at index:', equipmentIndex);
      $('.mrntableselectexcel1').empty().append(`<tr><td colspan="3" class="text-center text-muted">No checklist data available for this equipment</td></tr>`);
    }
  } else {
    console.log('window.equipmentModalData or equipmentIndex is undefined');
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
  
  // Populate equipment_details hidden field for defect notification
  const equipmentDetails = {
    equipment_id: defect?.equipment?.id ?? '',
    equipment_name: defect?.equipment?.name ?? '',
    equipment_category: defect?.category?.name ?? '',
    defect_notification_id: defect?.id ?? '',
    defect_type: defectTypeName,
    priority: defect?.priority ?? '',
    problem: defect?.problem ?? '',
    report_date_time: defect?.report_date_time ?? '',
    reported_by: defect?.creator?.name ?? '',
    reference_type: 'defect_notification'
  };
  $('#equipment_details').val(JSON.stringify(equipmentDetails));
  console.log('Defect equipment details populated:', equipmentDetails);
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

// === Attribute Badge Functions ===
// Handle attribute button click
$(document).on('click', '.attributeBtn', function (e) {
  let $tr = $(this).closest('tr');
  let $selectElement = $tr.find('.item_code');
  let $attributesTable = $('#attribute_table'); // modal table
  $attributesTable.data('currentRow', $tr);

  if ($selectElement.val() !== "") {
    let $hiddenInput = $tr.find('.attribute-enriched');
    let attributesJSON = [];
    let existingAttributes = [];

    // Scenario 1: Existing row with enriched attribute data
    if ($hiddenInput.length && $hiddenInput.val()) {
      try {
        let attributeData = JSON.parse($hiddenInput.val());
        // Convert from all_values structure to values_data structure
        attributesJSON = attributeData.map(function(attr) {
          return {
            id: attr.item_attribute_id,
            group_name: attr.group_name,
            values_data: attr.all_values || []
          };
        });
        existingAttributes = attributeData.map(function(attr) {
          return {
            item_attribute_id: attr.item_attribute_id,
            value_id: attr.selected_value_id,
            attribute_name: attr.group_name,
            attribute_value: attr.selected_value_name
          };
        });
      } catch (e) {
        console.error('Error parsing enriched attribute data:', e);
      }
    }
    
    // Scenario 2: New row addition (uses data-attr attribute with values_data structure)
    if (!attributesJSON.length && $selectElement.val() !== "") {
      try {
        attributesJSON = JSON.parse($selectElement.attr('data-attr') || '[]');
        let $hiddenInputSimple = $tr.find('.attribute');
        existingAttributes = $hiddenInputSimple.length && $hiddenInputSimple.val()
          ? JSON.parse($hiddenInputSimple.val())
          : [];
      } catch (e) {
        console.error('Error parsing data-attr:', e);
      }
    }

    if (!attributesJSON.length) {
      $attributesTable.html(`
        <tr>
          <td colspan="2" class="text-center">No attributes available</td>
        </tr>
      `);
      return;
    }

    let innerHtml = ``;

    $.each(attributesJSON, function (index, element) {
      let optionsHtml = ``;

      $.each(element.values_data, function (i, value) {
        let isSelected = existingAttributes.some(attr =>
          attr.item_attribute_id === element.id && attr.value_id === value.id
        );

        optionsHtml += `
          <option value='${value.id}' ${isSelected ? 'selected' : ''}>${value.value}</option>
        `;
      });

      innerHtml += `
        <tr>
          <td>
            ${element.group_name}
            <input type="hidden" name="id" value="${element.id}">
          </td>
          <td>
            <select class="form-select" onchange="changeAttributeVal($(this).closest('tr').closest('table').data('currentRow'));">
              <option value="">Select Value</option>
              ${optionsHtml}
            </select>
          </td>
        </tr>
      `;
    });

    $attributesTable.html(innerHtml);
  } else {
    $attributesTable.html(`
      <tr>
        <td colspan="2" class="text-center">Please select an item first</td>
      </tr>
    `);
  }
});

// Function to handle attribute value changes
function changeAttributeVal($row) {
  let hiddenInput = $row.find('.attribute');
  let hiddenInputEnriched = $row.find('.attribute-enriched');

  if (!hiddenInput) return;

  // Find the attributes table and tbody
  const attributesTable = document.getElementById("attribute_table");
  const tbody = attributesTable;

  let selectedAttributes = [];
  let selectedAttributesEnriched = [];

  Array.from(tbody.rows).forEach(row => {
    const hiddenInputAttr = row.querySelector('input[type="hidden"][name="id"]');
    const selectElement = row.querySelector("select");

    if (hiddenInputAttr && selectElement) {
      const attributeId = parseInt(hiddenInputAttr.value, 10);
      const selectedVal = parseInt(selectElement.value, 10);
      
      // Get the attribute name from the row
      const attributeNameCell = row.querySelector('td:first-child');
      const attributeName = attributeNameCell ? attributeNameCell.textContent.trim() : '';
      
      // Get the selected value text
      const selectedOption = selectElement.options[selectElement.selectedIndex];
      const selectedValueText = selectedOption ? selectedOption.textContent.trim() : '';

      if (!isNaN(attributeId) && !isNaN(selectedVal) && selectedVal > 0) {
        // Simple format for backend
        selectedAttributes.push({
          item_attribute_id: attributeId,
          value_id: selectedVal,
          attribute_name: attributeName,
          attribute_value: selectedValueText
        });

        // Enriched format for UI
        selectedAttributesEnriched.push({
          item_attribute_id: attributeId,
          selected_value_id: selectedVal,
          group_name: attributeName,
          selected_value_name: selectedValueText
        });
      }
    }
  });

  // Update hidden inputs with JSON
  hiddenInput.val(JSON.stringify(selectedAttributes));
  if (hiddenInputEnriched.length) {
    hiddenInputEnriched.val(JSON.stringify(selectedAttributesEnriched));
  }
  
  console.log('Selected attributes:', selectedAttributes);
}

// Function to update attribute badges display
function updateAttributeBadges($row) {
  if (!$row) return;

  let $selectElement = $row.find('.item_code');
  let rowIndex = $row.data('index') || $row.index();
  let $badgesContainer = $(`#attribute-badges-${rowIndex}`);
  
  // Fallback if specific container not found
  if (!$badgesContainer.length) {
    $badgesContainer = $row.find('.d-flex.flex-wrap.gap-1');
  }

  if ($selectElement.val() !== "") {
    let $hiddenInput = $row.find('.attribute');
    let existingAttributes = $hiddenInput.length && $hiddenInput.val()
      ? JSON.parse($hiddenInput.val())
      : [];

    if (!existingAttributes.length) {
      $badgesContainer.html('');
      return;
    }

    let badgesHtml = '';
    let totalSelectedCount = 0;

    // Count total selected attributes
    $.each(existingAttributes, function (index, attr) {
      if (attr.attribute_name && attr.attribute_value) {
        totalSelectedCount++;
      }
    });
    console.log('Total selected attributes:', totalSelectedCount);
    

    // Badge display logic based on count
    let displayedCount = 0;
    
    // Show up to 2 badges
    $.each(existingAttributes, function (index, attr) {
      if (attr.attribute_name && attr.attribute_value && displayedCount < 2) {
        displayedCount++;
        badgesHtml += `
          <span class="badge rounded-pill badge-light-primary" style="font-size:10px; margin-right:5px;cursor:pointer">
            <strong>${attr.attribute_name}</strong>: ${attr.attribute_value}
          </span>
        `;
      }
    });
    
    // Add "..." if there are more than 2 attributes
    if (totalSelectedCount > 2) {
      badgesHtml += '<span style="font-size:10px; color:black; margin-right:5px;cursor:pointer"><strong>...</strong></span>';
    }

    $badgesContainer.html(badgesHtml);

  } else {
    $badgesContainer.html('');
  }
}

// Update badges when modal is closed
$(document).on('click', '.submitAttributeBtn', (e) => {
  let $currentRow = $('#attribute_table').data('currentRow');
  if ($currentRow) {
    updateAttributeBadges($currentRow);
  }
  $("#attribute").modal('hide');
});
