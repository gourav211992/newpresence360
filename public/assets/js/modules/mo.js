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
  $(document).on('click', '#reject-button', (e) => {
     let actionType = 'reject';
     $("#approveModal #popupTitle").text("Reject Application");
     $("#approveModal").find("#action_type").val(actionType);
     $("#approveModal").modal('show');
  });
  
  /*Delete Row*/
  $(document).on('click','#deleteBtn', (e) => {
      let itemIds = [];
      let editItemIds = [];
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
            let pwo_mapping_id = $(`#row_${item}`).find("[name*='[pwo_mapping_id]']").val() || '';
            let selectedPiIds = localStorage.getItem('selectedPwoIds');
            if(pwo_mapping_id && selectedPiIds) {
                selectedPiIds = JSON.parse(selectedPiIds);
                let updatedIds = selectedPiIds.filter(id => ![pwo_mapping_id].includes(id));
                localStorage.setItem('selectedPwoIds', JSON.stringify(updatedIds));
            }
              $(`#row_${item}`).remove();
          });
      }
      if(editItemIds.length == 0 && itemIds.length == 0) {
        alert("Please first add & select row item.");
      }

      if (editItemIds.length) {
        $("#deleteComponentModal").find("#deleteConfirm").attr('data-ids',JSON.stringify(editItemIds));
        $("#deleteComponentModal").modal('show');
      }
  
      if(!$("tr[id*='row_']").length) {
          $("#itemTable > thead .form-check-input").prop('checked',false);
          $('#item_code').prop('disabled',false);
          $('#station_id').prop('disabled',false);
          $('#store_id').prop('disabled',false);
      }
    //   let indexData = $("#row_1").attr('data-index');
    //   totalCostEachRow(indexData);
  });
  
  /*Attribute on change*/
  $(document).on('change', '[name*="comp_attribute"]', (e) => {
      let rowCount = e.target.closest('tr').querySelector('[name*="row_count"]').value;
      let attrGroupId = e.target.getAttribute('data-attr-group-id');
      $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).val(e.target.value);
      qtyEnabledDisabled();
  
      let itemId = $("#attribute tbody tr").find('[name*="[item_id]"]').val();
     let itemAttributes = [];
     $("#attribute tbody tr").each(function(index, item) {
        let attr_id = $(item).find('[name*="[attribute_id]"]').val();
        let attr_value = $(item).find('[name*="[attribute_value]"]').val();
        itemAttributes.push({
              'attr_id': attr_id,
              'attr_value': attr_value
          });
     });
  });

  /*Edit mode table calculation filled*/
  if($("#itemTable .mrntableselectexcel tr").length) {
     setTimeout(()=> {
        $("[name*='component_item_name[1]']").trigger('focus');
        $("[name*='component_item_name[1]']").trigger('blur');
     },100);
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
  });
  
  /*Approve modal*/
  $(document).on('click', '#approved-button', (e) => {
     let actionType = 'approve';
     $("#approveModal").find("#action_type").val(actionType);
     $("#approveModal").modal('show');
  });
  $(document).on('click', '#reject-button', (e) => {
     let actionType = 'reject';
     $("#approveModal").find("#action_type").val(actionType);
     $("#approveModal").modal('show');
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
  
  $('#attribute').on('hidden.bs.modal', function () {
     let rowCount = $("[id*=row_].trselected").attr('data-index');
     $(`[name="components[${rowCount}][qty]"]`).val('').focus();
  });

$(document).on('change', '#item_code', (e) => {
    if(!e.target.value) {
        $('.prSelect').prop('disabled',true);
    }
});

// get selected machine get number of sheet
function fetchMachineDetailsForRow($row, machineId = null) {
    let qty = $row.find("input[name*='qty']").val() || 0;
    let $attributeTd = $row.find("td[id*='itemAttribute_']");
    let selectedAttrValIds = [];
    let selectedAttrGroupValIds = [];
    if ($attributeTd.length) {
        let attributes = $attributeTd.attr('attribute-array');
        attributes = JSON.parse(attributes || '[]');
        attributes.forEach(group => {
            if (Array.isArray(group.values_data)) {
                group.values_data.forEach(val => {
                    if (val.selected === true) {
                        selectedAttrValIds.push(val.id);
                        selectedAttrGroupValIds.push(group.attribute_group_id);
                    }
                });
            }
        });
    }
    let attrValueIds = [...new Set(selectedAttrValIds)];
    let attrGroupIds = [...new Set(selectedAttrGroupValIds)];
    fetch(`${getMachineDetailUrl}?machine_id=${machineId}&attr_value_ids=${attrValueIds.join(',')}&attr_ids=${attrGroupIds.join(',')}&qty=${qty}`)
    .then(response => response.json())
    .then(data => {
        console.log(data);
        if (data.status === 200) {
            $row.find("[name*='[sheet]']").val(data.data.sheet);
        } else {
            $row.find("[name*='[sheet]']").val('');
        }
    })
    .catch(() => {
        $row.find("[name*='[sheet]']").val('');
    });
}

$(document).on("change", "select[name*='[machine_id]']", (e) => {
    const $row = $(e.target).closest('tr');
    let machineId = $row.find("select[name*='[machine_id]']").val() || '';
    fetchMachineDetailsForRow($row, machineId);
});

$(document).on('change', "#main_machine_id", (e) => {
    let machineId = e.target.value || '';
    $("select[name*='][machine_id]']").each(function(index, item) {
        $(item).val(machineId).trigger('change');
    });
});