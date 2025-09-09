/*Checkbox*/
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

/*Approve modal*/
$(document).on('click', '#approved-button', (e) => {
   let actionType = 'approve';
   $("#approveModal").find("#action_type").val(actionType);
   $("#approveModal #popupTitle").text("Approve Application");
   $("#approveModal").modal('show');
});

// $(document).on('click', '#reject-button', (e) => {
//    $("#rejectModal").modal('show');
// });

/*Delete Row*/
$(document).on('click','#deleteBtn', (e) => {
    let itemIds =  JSON.parse(localStorage.getItem('itemIds')) || [];
    let editItemIds = JSON.parse(localStorage.getItem('editItemIds')) || [];
    
    // let itemIds = [];
    // let editItemIds = [];
    $('#itemTable > tbody .form-check-input').each(function() {
        if ($(this).is(":checked")) {
            if($(this).attr('data-id')) {
               editItemIds.push($(this).attr('data-id'));
            } else {
               itemIds.push($(this).val());
            }
        }
    });

    if (itemIds.length) {
        itemIds.forEach(function(item,index) {
            $(`#itemTable #row_${item}`).remove();
        });
    }
    if(editItemIds.length == 0 && itemIds.length == 0) {
      alert("Please first add & select row item.");
    }
    if (editItemIds.length) {
      $("#deleteComponentModal").find("#deleteConfirm").attr('data-ids',JSON.stringify(editItemIds));
      $("#deleteComponentModal").modal('show');
    }

    if(!$("#itemTable tr[id*='row_']").length) {
        $("#itemTable > thead .form-check-input").prop('checked',false);
        $(".prSelect").prop('disabled',false);
    }
    let indexData = $("#itemTable #row_1").attr('data-index');

   const uniqueItemIds = [...new Set(itemIds)];
   const uniqueEditItemIds = [...new Set(editItemIds)];
     

    localStorage.setItem('itemIds', JSON.stringify(uniqueItemIds));
    localStorage.setItem('editItemIds', JSON.stringify(uniqueEditItemIds));
 
    console.log("editItemIds",editItemIds);
    totalCostEachRow(indexData);
});

$(document).on('click','#deleteInstructionBtn', (e) => {
   let itemIds = [];
   let editItemIds = [];
   $('#itemTable3 > tbody .form-check-input').each(function() {
       if ($(this).is(":checked")) {
           if($(this).attr('data-id')) {
              editItemIds.push($(this).attr('data-id'));
           } else {
              itemIds.push($(this).val());
           }
       }
   });
   if (itemIds.length) {
       itemIds.forEach(function(item,index) {
           $(`#itemTable3 #row_${item}`).remove();
       });
   }
   if(editItemIds.length == 0 && itemIds.length == 0) {
     alert("Please first add & select row item.");
   }
   if (editItemIds.length) {

     $("#deleteInstrunctionComponentModal").find("#deleteInstructionConfirm").attr('data-ids',JSON.stringify(editItemIds));
     $("#deleteInstrunctionComponentModal").modal('show');
   }

   if(!$("#itemTable3 tr[id*='row_']").length) {
       $("#itemTable3 > thead .form-check-input").prop('checked',false);
   }
});

/*Over Head item btn*/
$(document).on('click', '.overheadItemBtn', (e) => {
    $("#overheadItemPopup").modal('hide');
});

/*Overhead Summary Popup*/
$(document).on('click', '.addOverHeadSummaryBtn', (e) => {
   let footerSubTotal = Number($("#totalCostValue").attr('amount')) || 0;
   if(!footerSubTotal) {
      Swal.fire({
         icon: 'error',
         title: 'Oops...',
         text: 'Please add item first.',
         showConfirmButton: true,
         timer: 3000
      });
      return false;
   }
    $('#overheadSummaryPopup').modal('show');
    $("#itemLevelSubTotalPrice").text(footerSubTotal.toFixed(2));
});


/*Submit main overhead*/
$(document).on('click','.overheadSummaryBtn', (e) => {
   $("#overheadSummaryPopup").modal('hide');
});

/*qty on change*/
$(document).on('change input',"table input[name*='qty']",(e) => {
   let tr = e.target.closest('tr');
   let qty = e.target;
   let itemCost = $(e.target).closest('tr').find("[name*='item_cost']");
   let itemValue = $(e.target).closest('tr').find("[name*='item_value']");
   let totalItemValue = Number(qty.value) * Number(itemCost.val());
      itemValue.val(totalItemValue.toFixed(2));
   totalCostEachRow(tr.getAttribute('data-index'));
});

$(document).on('change input',"[name*='item_cost']",(e) => {
   let tr = e.target.closest('tr');
   let itemCost = e.target;
   let qty = $(e.target).closest('tr').find("[name*='qty']");
   let itemValue = $(e.target).closest('tr').find("[name*='item_value']");
   let totalItemValue = Number(qty.val()) * Number(itemCost.value);
   itemValue.val(totalItemValue.toFixed(2));
   totalCostEachRow(tr.getAttribute('data-index'));
});


/*Som each row item cost*/
function totalCostEachRow(rowIndex) {
   setTableCalculation();
};

/*Edit mode table calculation filled*/
if($("#itemTable .mrntableselectexcel tr").length) {
   setTimeout(()=> {
      $("[name*='component_item_name[1]']").trigger('focus');
      $("[name*='component_item_name[1]']").trigger('blur');
   },100);
}

$(document).on('blur','[name*="component_item_name"]',(e) => {
   if(!e.target.value) {
       $(e.target).closest('tr').find('[name*="[item_name]"]').val('');
       $(e.target).closest('tr').find('[name*="[item_id]"]').val('');
   }
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

/*Approve modal*/
$(document).on('click', '#approved-button', (e) => {
   let actionType = 'approve';
   $("#approveModal").find("#action_type").val(actionType);
   $("#approveModal").modal('show');
});
$(document).on('click', '#reject-button', (e) => {
    //    let actionType = 'reject';
    //    $("#approveModal").find("#action_type").val(actionType);
   $("#rejectModal").modal('show');
});

/*Bom detail remark js*/
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


/*Set cost*/
setTimeout(() => {
   $("#itemTable [id*='row_'").each(function(index, item) {
      let rowCount = $(item).attr('data-index');
      let val = $(item).find("[name*='[qty]']").val();
      $(item).find("[name*='[qty]']").trigger('change').val(val);
   });

},100);

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

function setTableCalculation() {
   let totalItemValue = 0;
   let totalItemCost = 0;
   let totalItemOverhead = 0;
   let totalHeaderOverhead = 0;
   let grandTotal = 0;
   /*Detail Level Calculation Set*/
   $("#itemTable [id*='row_']").each(function (index, item) {
      let itemOverheadAmnt = 0;
      let itemCost = 0;
      let rowCount = Number($(item).attr('data-index'));
      let qty = Number($(item).find("[name*='[qty]']").val()) || 0;
      let rate = $(item).find("[name*='[superceeded_cost]']").val() || 0;
      if(!Number(rate)) {
         rate = $(item).find("[name*='[item_cost]']").val() || 0;
      }
      let itemValue = (Number(qty) * Number(rate)) || 0;
      // Calculate item level overhead
      let overheadInputAmnt = 0
      $(item).find("input[name*='[overhead_id]']").each(function(index2, overheadItem) {
         let overheadIndex = index2 + 1;
         let overheadPerc = $(overheadItem).closest('td').find(`input[name="components[${rowCount}][overhead][${overheadIndex}][perc]"]`).val() || 0;
         let overheadAmnt = $(overheadItem).closest('td').find(`input[name="components[${rowCount}][overhead][${overheadIndex}][amnt]"]`).val() || 0;
         if(Number(overheadPerc)) {
            overheadInputAmnt = (Number(overheadPerc) / 100) * itemValue;
         } else {
            overheadInputAmnt = Number(overheadAmnt) || 0;
         }
         $(overheadItem).closest('td').find(`input[name="components[${rowCount}][overhead][${overheadIndex}][amnt]"]`).val(Number(overheadInputAmnt).toFixed(2));
         if($("#overheadItemPopup").find(`input[id*='item_overhead_input_perc_${rowCount}_']`).length && Number(overheadPerc)) {
            $("#overheadItemPopup").find(`input[name='components[${rowCount}][overhead][${overheadIndex}][amnt]']`).val(Number(overheadInputAmnt).toFixed(2));
         }
         itemOverheadAmnt += overheadInputAmnt;
      });
      $(`#item_sub_total_row_${rowCount}`).find("#total").attr('amount', Number(itemOverheadAmnt)).text(Number(itemOverheadAmnt).toFixed(2));
      $(item).find("[name*='[overhead_amount]']").val(Number(itemOverheadAmnt).toFixed(2));

      totalItemValue+=itemValue;
      $(item).find("[name*='[item_value]']").val(itemValue.toFixed(2));
      totalItemOverhead+=itemOverheadAmnt;
      $(item).find("[name*='[overhead_amount]']").val(itemOverheadAmnt.toFixed(2));
      itemCost = itemValue + itemOverheadAmnt;
      totalItemCost+=itemCost;
      $(item).find("[name*='[item_total_cost]']").val(itemCost.toFixed(2));
   });

   $("#totalItemValue").attr('amount',totalItemValue).text( canView ? totalItemValue.toFixed(2) : '0.00');
   $("#totalOverheadAmountValue").attr('amount',totalItemOverhead).text( canView ? totalItemOverhead.toFixed(2) : '0.00');
   $("#totalCostValue").attr('amount',totalItemCost).text(canView ? totalItemCost.toFixed(2) : '0.00');
   /*Header Level Calculation Set*/
   // New Update Overhead
   let tempAmnt = totalItemCost;
   let levels = $("tr.sub_total_row[id*='sub_total_row_']").length || 1;
   let subtotal = 0;
   for (let level = 1; level <= levels; level++) {
      let levelTotal = 0;
      $(`tr.display_overhead_row[data-level="${level}"]`).each(function () {
          let $row = $(this);
          let percInput = $row.find('input[name*="[perc]"]');
          let amntInput = $row.find('input[name*="[amnt]"]');
          let perc = parseFloat(percInput.val()) || 0;
          let amnt = parseFloat(amntInput.val()) || 0;
          if (perc) {
              amnt = (perc / 100) * tempAmnt;
              amntInput.val(amnt.toFixed(2));
          }
          levelTotal += amnt;
      });
      tempAmnt += levelTotal;
      subtotal+=levelTotal;
      $(`#sub_total_row_${level}`).find("#total").attr('amount', tempAmnt).text(canView ? tempAmnt.toFixed(2) : '0.00');
   }
   totalHeaderOverhead = subtotal;
   $("#footerTotalCost").attr('amount',(totalItemCost)).text(canView ? (totalItemCost).toFixed(2) : '0.00');
   $("#footerOverheadHeader").attr('amount',(totalHeaderOverhead)).text(canView ? (totalHeaderOverhead).toFixed(2) : '0.00');
   grandTotal = totalItemCost + totalHeaderOverhead;
   $("#footerGrandTotal").attr('amount',grandTotal).text(canView ? grandTotal.toFixed(2) : '0.00');
 }

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
            if(isEdit){
                $(item).find("[name*='[qty]']").attr('readonly',Boolean(qtyDisabled));
            }
            if(qtyDisabled) {
                $(item).find("[name*='[qty]']").val('');
            }
        } else {
            $(item).find("[name*='[qty]']").attr('readonly',false);
        }
    });
}
qtyEnabledDisabled();

$('#attribute').on('hidden.bs.modal', function () {
   let rowCount = $("[id*=row_].trselected").attr('data-index');
   let tabName = document.querySelector(".nav-link.active").getAttribute("data-bs-target").replace("#", "");
   if(tabName === 'raw-materials') {
      if(!$("#consumption_method").val().includes('manual')) {
         $(`[name="components[${rowCount}][qty]"]`).closest('td').find('.consumption_btn button').trigger('click');
       } else {
        if(isEdit){
            $(`[name="components[${rowCount}][qty]"]`).val('').focus();
       }

       }
   }
});

// Set Calculate Consumption Qty
document.addEventListener("DOMContentLoaded", function () {
   const qtyPerUnit = document.getElementById("qty_per_unit");
   const totalQty = document.getElementById("total_qty");
   const stdQty = document.getElementById("std_qty");
   const output = document.getElementById("output");
   const selectButton = document.querySelector(".submit_consumption");
   function calculateOutput() {
      const qty = parseFloat(qtyPerUnit.value) || 0;
      const total = parseFloat(totalQty.value) || 0;
      const std = parseFloat(stdQty.value) || 0;
      const result = total > 0 ? (std / total * qty) : 0;
      output.value = isNaN(result) ? "0.000000" : result.toFixed(6);
      let rowCount = $("#consumptionPopup input[name='consumption_row']").val();
      $(`[name="components[${rowCount}][qty]"]`).val(output.value);
      let hiddenInput = `
      <input type="hidden" name="components[${rowCount}][qty_per_unit]" value="${isNaN(parseFloat(qtyPerUnit.value)) ? '' : parseFloat(qtyPerUnit.value)}">
      <input type="hidden" name="components[${rowCount}][total_qty]" value="${isNaN(parseFloat(totalQty.value)) ? '' : parseFloat(totalQty.value)}">
      <input type="hidden" name="components[${rowCount}][std_qty]" value="${isNaN(parseFloat(stdQty.value)) ? '' : parseFloat(stdQty.value)}">`;
      $(`[name="components[${rowCount}][qty_per_unit]"]`).remove();
      $(`[name="components[${rowCount}][total_qty]"]`).remove();
      $(`[name="components[${rowCount}][std_qty]"]`).remove();
      $(`[name="components[${rowCount}][qty]"]`).after(hiddenInput);
      setTableCalculation();
   }
   qtyPerUnit.addEventListener("input", calculateOutput);
   totalQty.addEventListener("input", calculateOutput);
   stdQty.addEventListener("input", calculateOutput);
   if(selectButton) {
      selectButton.addEventListener("click", function () {
         let isValid = true;
         [qtyPerUnit, totalQty, stdQty].forEach((field) => {
            if (!field.value.trim()) {
               isValid = false;
               field.classList.add("is-invalid");
            } else {
               field.classList.remove("is-invalid");
            }
         });
         if (!isValid) {
            Swal.fire({
               title: 'Error!',
               text: "Please fill out all required fields." ,
               icon: 'error',
           });
         } else {
            $("#consumptionPopup").modal('hide');
         }
      });
   }
});

// $(document).on('click', '.consumption_btn', (e) => {
//    let rowCount = $(e.target).attr('data-row-count');
//    $("#consumptionPopup").modal('show');
//    $("#consumptionPopup input[name='consumption_row']").val(rowCount);
//    let qty_per_unit = $(`[name="components[${rowCount}][qty_per_unit]"]`).val() || '';
//    let total_qty = $(`[name="components[${rowCount}][total_qty]"]`).val() || '';
//    let std_qty = $(`[name="components[${rowCount}][std_qty]"]`).val() || '';
//    $("#qty_per_unit").val(qty_per_unit);
//    $("#total_qty").val(total_qty);
//    $("#std_qty").val(std_qty);
//    const qty = parseFloat(qty_per_unit) || 0;
//    const total = parseFloat(total_qty) || 0;
//    const std = parseFloat(std_qty) || 0;
//    const result = total > 0 ? (std / total * qty) : 0;
//    const output  = isNaN(result) ? "0.000000" : result.toFixed(6);
//    $("#output").val(output);
// });

$(document).on('click', '.consumption_btn', (e) => {
    const rowCount = $(e.target).attr('data-row-count');
    showConsumptionPopup(rowCount);
});

$(document).on('click', '.submit_consumption', (e) => {
    const rowCount = $(e.target).closest('.modal-content').find('input[name="consumption_row"]').val() || 1;
    showConsumptionPopup(rowCount);
});

function showConsumptionPopup(rowCount) {
    const modal = $("#consumptionPopup");
    modal.modal('show');
    modal.find("input[name='consumption_row']").val(rowCount);
    const qty_per_unit = $(`[name="components[${rowCount}][qty_per_unit]"]`).val() || '';
    const total_qty = $(`[name="components[${rowCount}][total_qty]"]`).val() || '';
    const std_qty = $(`[name="components[${rowCount}][std_qty]"]`).val() || '';
    $("#qty_per_unit").val(qty_per_unit);
    $("#total_qty").val(total_qty);
    $("#std_qty").val(std_qty);
    const qty = parseFloat(qty_per_unit) || 0;
    const total = parseFloat(total_qty) || 0;
    const std = parseFloat(std_qty) || 0;
    const result = total > 0 ? (std / total * qty) : 0;
    const output = isNaN(result) ? Number(0.000000) : result.toFixed(6);
    if(output < 1) {
        const existQty = $("tr").find(`input[name='components[${rowCount}][qty]']`).val() || 0;
         if(existQty) {
             $("tr").find(`input[name='components[${rowCount}][qty]']`).val(existQty);
            } else {
             $("tr").find(`input[name='components[${rowCount}][qty]']`).val(output);
         }
        $("#output").val(output);
    } else {
        $("tr").find(`input[name='components[${rowCount}][qty]']`).val(output);
        $("#output").val(output);
    }
}




// Added for visible seprate tab button
document.addEventListener("DOMContentLoaded", function () {
   function updateButtonVisibility() {
       let activeTab = document.querySelector(".nav-link.active").getAttribute("data-bs-target").replace("#", "");
       document.querySelectorAll(".tab-action").forEach(button => {
           if (button.getAttribute("data-tab") === activeTab) {
               button.classList.remove("d-none");
           } else {
               button.classList.add("d-none");
           }
       });
   }
   updateButtonVisibility();
   document.querySelectorAll(".nav-link").forEach(tab => {
       tab.addEventListener("click", function () {
           setTimeout(updateButtonVisibility, 100);
       });
   });
});

// set max 100% for input
$(document).on('input', '.percentage_input', function () {
   let $input = $(this);
   let max = parseFloat($input.attr('max')) || 100;
   let min = parseFloat($input.attr('min')) || 0;
   let value = parseFloat($input.val());
   if (!isNaN(value)) {
      if (value > max) $input.val(max);
      if (value < min) $input.val(min);
   }
});

setTimeout(() => {
   let overheadLevelCount = $("tr[id*='sub_total_row_']").length || 1;
   $("input[name='orverhead_level_count']").val(overheadLevelCount);
}  , 100);
$(document).on('blur change autocompletechange', 'tr.display_overhead_row input, tr.display_overhead_row select', function () {
   let overheadLevelCount = $("tr[id*='sub_total_row_']").length || 1;
   $("input[name='orverhead_level_count']").val(overheadLevelCount);
});

$('#overheadSummaryPopup').on('hidden.bs.modal', function () {
    let overheadLevelCount = $("tr[id*='sub_total_row_']").length || 1;
    $("input[name='orverhead_level_count']").val(overheadLevelCount);
});
$(document).on('autocompletechange', 'tr.display_overhead_row input', function (e) {
   if(!e.target.value) {
      $(e.target).closest('tr').find("input[name*='[overhead_id]']").val('');
   }
});

setTimeout(() => {
   $('#production_route_id').trigger('change');
}, 0);
$(document).on('change', '#production_route_id', function (e) {
   let selectedOption = $(e.target).find('option:selected');
   let safetyBufferPerc = selectedOption.data('perc');
   $("#safety_buffer_perc").attr('placeholder', safetyBufferPerc);
});

function initAttributeAutocomplete(context = document) {
   $(context).find('.attr-autocomplete').each(function () {
       let $input = $(this);
       $input.autocomplete({
           minLength: 0,
           source: function (request, response) {
               let itemId = $input.closest('tr').find("input[name*='item_id']").val() || '';
               let attrGroupId = $input.data('attr-group-id');
               $.ajax({
                   url: '/search',
                   method: 'GET',
                   dataType: 'json',
                   data: {
                       q: request.term,
                       type: "item_attr_value",
                       item_id: itemId,
                       attr_group_id: attrGroupId,
                   },
                   success: function (data) {
                       response($.map(data, function (item) {
                           return {
                               id: item.id,
                               label: item.value,
                               value: item.value
                           };
                       }));
                   },
                   error: function (xhr) {
                       console.error('Error fetching attribute values:', xhr.responseText);
                   }
               });
           },
           select: function (event, ui) {
               const row = $input.closest('tr');
               const rowCount = row.find('[name*="row_count"]').val();
               const attrGroupId = $input.data('attr-group-id');
               $input.val(ui.item.label);
               $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).val(ui.item.id);
               qtyEnabledDisabled();
               const itemId = $("#attribute tbody tr").find('[name*="[item_id]"]').val();
               const itemAttributes = [];
               $("#attribute tbody tr").each(function () {
                   const attr_id = $(this).find('[name*="[attribute_id]"]').val();
                   const attr_value = $(this).find('[name*="[attribute_value]"]').val();
                   itemAttributes.push({
                       attr_id: attr_id,
                       attr_value: attr_value
                   });
               });
               getBomItemCost(itemId, itemAttributes);
               return false;
           },
           focus: function (event, ui) {
               event.preventDefault();
           }
       });
       $input.on('focus', function () {
           if (!$(this).val()) {
               $(this).autocomplete("search", "");
           }
       });
       $input.on('input', function () {
           if (!$(this).val()) {
               const row = $input.closest('tr');
               const rowCount = row.find('[name*="row_count"]').val();
               const attrGroupId = $input.data('attr-group-id');
               $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).val('');
               qtyEnabledDisabled();
           }
       });
   });
}

// Auto scroll when row added
function focusAndScrollToLastRowInput(inputSelector = '.comp_item_code', tableSelector = '#itemTable') {
//    let $lastRow = $(`${tableSelector} > tbody > tr`).last();
    let $lastRow = $(`${tableSelector} > tbody > tr.trselected`).length
    ? $(`${tableSelector} > tbody > tr.trselected`).next('tr')
    : $(`${tableSelector} > tbody > tr`).last();

   let $input = $lastRow.find(inputSelector);

   if ($input.length) {
        let isSection = false;
        if($lastRow.find("input[name*='product_section']").length) {
            $input = $lastRow.find("input[name*='product_section']");
            isSection = true;
        }
        if($lastRow.find("input[name*='product_sub_section']").length && !isSection) {
            $input = $lastRow.find("input[name*='product_sub_section']");
        }
       $input.focus().autocomplete('search', '');
       $input[0].scrollIntoView({
           behavior: 'smooth',
           block: 'center',
           inline: 'nearest'
       });
   }
}

function getUniqueRowCount() {
    let existingIndexes = [];
    $("#itemTable > tbody > tr").each(function () {
        let index = parseInt($(this).attr("data-index"));
        if (!isNaN(index)) {
            existingIndexes.push(index);
        }
    });
    let rowCount = $("#itemTable > tbody > tr").length + 1;
    while (existingIndexes.includes(rowCount)) {
        rowCount++;
    }
    return rowCount - 1;
}
