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
            let so_item_id = $(`#row_${item}`).find("[name*='[so_item_id]']").val() || '';
            let selectedPiIds = localStorage.getItem('selectedSoItemIds');
            if(so_item_id && selectedPiIds) {
                selectedPiIds = JSON.parse(selectedPiIds);
                let updatedIds = selectedPiIds.filter(id => ![so_item_id].includes(id));
                localStorage.setItem('selectedSoItemIds', JSON.stringify(updatedIds));

            }
            $(`#row_${index+1}`).remove();
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
        //   $(".prSelect").prop('disabled',false);
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
      setSelectedAttribute(rowCount);
  });

  /*Edit mode table calculation filled*/
//   if($("#itemTable .mrntableselectexcel tr").length) {
//      setTimeout(()=> {
//         $("[name*='component_item_name[1]']").trigger('focus');
//         $("[name*='component_item_name[1]']").trigger('blur');
//      },100);
//   }
  
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
     let qty = $(`[name="components[${rowCount}][qty]"]`).val() || ''; 
     $(`[name="components[${rowCount}][qty]"]`).val(qty).focus();
  });
  
function updateRowIndex(is_render = false) {
    $("#itemTable tbody tr[id*='row_']").each(function(index, item) {
        let currentIndex = index + 1;
        $(item).attr('id', 'row_' + currentIndex);
        $(item).attr('data-index', currentIndex);
        $(item).find('#Email_'+currentIndex).val(currentIndex);
        $(item).find("td[id*='itemAttribute_']").attr('id','itemAttribute_'+currentIndex);
        $(item).find("td[name*='itemAttribute_']").attr('data-count', currentIndex);
        $(item).find("input, select, button, label").each(function() {
            let nameAttr = $(this).attr("name");
            let idAttr = $(this).attr("id");
            let forAttr = $(this).attr("for");
            let dataRowCount = $(this).attr("data-row-count");
            if (nameAttr) {
                $(this).attr("name", nameAttr.replace(/\[\d+\]/, "[" + currentIndex + "]"));
            }
            if (idAttr) {
                $(this).attr("id", idAttr.replace(/_\d+$/, "_" + currentIndex));
            }
            if (forAttr) {
                $(this).attr("for", forAttr.replace(/_\d+$/, "_" + currentIndex));
            }
            if (dataRowCount) {
                $(this).attr("data-row-count", currentIndex);
            }
        });
    });
    if(is_render) {
        setTimeout(() => {
            $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                let currentIndex = index + 1;
                setAttributesUIHelper(currentIndex,"#itemTable");
            });
        },100);
    }
}