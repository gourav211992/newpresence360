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
$(document).on('click', '#deleteBtn', (e) => {
    e.preventDefault();
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to delete this?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        
    if (result.isConfirmed) {
        
    let editItemIds = [];
    let anyChecked = false;
    $('#itemTable > tbody .form-check-input').each(function() {
        if ($(this).is(":checked")) {
            anyChecked = true;
            let dataId = $(this).attr('data-id');
            if (dataId) {
                editItemIds.push(dataId);
            } else {
                $(this).closest('tr').remove();
            }
        }
    });
    if (!anyChecked) {
        alert("Please select at least one row to delete.");
        return;
    }
    if (editItemIds.length > 0) {
        $("#deleteComponentModal").find("#deleteConfirm").attr('data-ids', JSON.stringify(editItemIds));
        $("#deleteComponentModal").modal('show');
    }
    if ($("#itemTable > tbody tr").length === 0) {
        $("#itemTable > thead .form-check-input").prop('checked', false);
        // $(".prSelect").prop('disabled', false); // Uncomment if needed
    }
    }
    });
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

$(document).on('click', '.toggle-expand', function (e) {
    e.preventDefault();
    var targetKey = $(this).data('target');
    var parentLevel = parseInt($(this).closest('tr').data('level'), 10);
    $('tr[data-row-key^="' + targetKey + '-"]').each(function () {
        var rowLevel = parseInt($(this).data('level'), 10);
        if (rowLevel === parentLevel + 1) {
            $(this).removeClass('d-none');
        }
    });
    $(this).addClass('d-none');
    $(this).siblings('.toggle-collapse').removeClass('d-none');
});

$(document).on('click', '.toggle-collapse', function (e) {
    e.preventDefault();
    var targetKey = $(this).data('target');
    $('tr[data-row-key^="' + targetKey + '-"]').addClass('d-none');
    $('tr[data-row-key^="' + targetKey + '-"] .toggle-collapse').addClass('d-none');
    $('tr[data-row-key^="' + targetKey + '-"] .toggle-expand').removeClass('d-none');
    $(this).addClass('d-none');
    $(this).siblings('.toggle-expand').removeClass('d-none');
});

// Autocomplete for store name
function initStoreAutocomplete(context = "#analyzeModal") {
    $(context).find('input[name*="store_name"]').each(function () {
        let $input = $(this);
        if (!$input.closest('.autocomplete-wrapper').length) {
            $input.wrap('<div class="autocomplete-wrapper" style="position:relative;"></div>');
            $input.after('<span class="clear-autocomplete" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); cursor:pointer; font-size:18px; color:#888; display:none;">&times;</span>');
        }
        let $clearBtn = $input.siblings('.clear-autocomplete');
        $input.on('input focus', function () {
            $clearBtn.toggle(!!$input.val());
        });
        $clearBtn.on('click', function () {
            $input.val('').focus();
            $input.closest('td').find('input[name="store_id"]').val('');
            $(this).hide();
        });

        $input.autocomplete({
            minLength: 0,
            source: function (request, response) {
                $.ajax({
                    url: '/search',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        q: request.term,
                        type: "location",
                    },
                    success: function (data) {
                        response($.map(data, function (item) {
                            return {
                                id: item.id,
                                label: item.store_name,
                                value: item.store_name
                            };
                        }));
                    },
                    error: function (xhr) {
                        console.error('Error fetching attribute values:', xhr.responseText);
                    }
                });
            },
            select: function (event, ui) {
                $input.val(ui.item.label);
                $input.closest('td').find('input[name="store_id"]').val(ui.item.id);
                if(!$input.closest('td').find('input[name="store_id"]').length) {
                    $input.closest('td').find('input[name*="store_id"]').val(ui.item.id);
                }
                $clearBtn.show();
                let storeId = ui.item.id;
                let checkBox = $input.closest('tr').find(".analyze_row");
                let itemId = checkBox.data('item-id') || '';
                let uomId = checkBox.data('uom-id') || '';
                let selectAttribute = [];
                let attribute = checkBox.data('attribute') || [];
                if(attribute.length) {
                    selectAttribute = attribute.flatMap(attr =>
                        attr.values_data.filter(v => v.selected).map(v => v.id)
                    );
                }

                checkBox.attr("data-store-name", ui.item.label);
                checkBox.attr("data-store-id", ui.item.id);
                
                if(context == '#analyzeModal') {
                    let tr = $input.closest('tr')[0];
                    getStock(itemId, uomId, selectAttribute, storeId, tr);
                }
                return false;
            },
            focus: function (event, ui) {
                event.preventDefault();
            }
        });
        $input.on('focus', function () {
            if (!$(this).val()) {
                $(this).autocomplete("search", "");
                $(this).closest('td').find('input[name="store_id"]').val("");
                if(!$(this).closest('td').find('input[name="store_id"]').length) {
                    $(this).closest('td').find('input[name*="store_id"]').val("");
                }
                let storeId = "";
                let checkBox = $(this).closest('tr').find(".analyze_row");
                let itemId = checkBox.data('item-id') || '';
                let uomId = checkBox.data('uom-id') || '';
                let selectAttribute = [];
                let attribute = checkBox.data('attribute') || [];
                if(attribute.length) {
                    selectAttribute = attribute.flatMap(attr =>
                        attr.values_data.filter(v => v.selected).map(v => v.id)
                    );
                }
                if(context == '#analyzeModal') {
                    let tr = $input.closest('tr')[0];
                    getStock(itemId, uomId, selectAttribute, storeId, tr);
                }
                checkBox.attr("data-store-name", '');
                checkBox.attr("data-store-id", '');
            }
        });
        $input.on('input', function () {
            if (!$(this).val()) {
                $(this).closest('td').find('input[name="store_id"]').val("");
                if(!$(this).closest('td').find('input[name="store_id"]').length) {
                    $(this).closest('td').find('input[name*="store_id"]').val("");
                }
                $clearBtn.hide();
                let storeId = "";
                let checkBox = $(this).closest('tr').find(".analyze_row");
                let itemId = checkBox.data('item-id') || '';
                let uomId = checkBox.data('uom-id') || '';
                let selectAttribute = [];
                let attribute = checkBox.data('attribute') || [];
                if(attribute.length) {
                    selectAttribute = attribute.flatMap(attr =>
                        attr.values_data.filter(v => v.selected).map(v => v.id)
                    );
                }
                if(context == '#analyzeModal') {
                    let tr = $input.closest('tr')[0];
                    getStock(itemId, uomId, selectAttribute, storeId, tr);
                }
                checkBox.attr("data-store-name", '');
                checkBox.attr("data-store-id", '');
            }
        });
    });
 }

//  Get Stock store change
function getStock(itemId, uomId, selectAttribute, storeId, tr) {
    if (selectAttribute.length) {
        selectAttribute = selectAttribute.join(',');
    } else {
        selectAttribute = '';
    }
    let actionUrl = `${getStockUrl}?item_id=${itemId}&uom_id=${uomId}&store_id=${storeId}&selected_attributes=${selectAttribute}`;
    fetch(actionUrl, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 200) {
            let stock = data?.data?.avl_stock || 0;
            let stockElement = tr.querySelector('.avl_stock');
            if (stockElement) {
                stockElement.innerText = stock;
            }
        } else {
            console.error('Error fetching stock:', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Analyze modal js code
function getSelectedPiIDS()
{
    let ids = [];
    $('.pi_item_checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function getSelectedPiIDS2()
{
    let ids = [];
    $('.analyze_row:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

$(document).on('click', '.analyzeButton', (e) => {
    let ids = getSelectedPiIDS();
    if (!ids.length) {
        $("#prModal").modal('hide');
        Swal.fire({
            title: 'Error!',
            text: 'Please select at least one line item',
            icon: 'error',
        });
        return false;
    }

    ids = JSON.stringify(ids);
    let d_date = $("input[name='document_date']").val() || '';
    let book_id = $("#book_id").val() || '';
    let rowCount = $("#itemTable tbody tr[id*='row_']").length;
    let isAttribute = 0;
    if($("#attributeCheck").is(':checked')) {
        isAttribute = 1;
    } else {
        isAttribute = 0;
    }
    let selectedItems = [];
    if(!isAttribute) {
        $("#prModal .pi_item_checkbox:checked").each(function () {
            selectedItems.push({
                "sale_order_id": Number($(this).val()),
                "item_id": Number($(this).data("item-id"))
            });
        });
    }
    let selectedItemsParam = encodeURIComponent(JSON.stringify(selectedItems));
    let actionUrl = analyzeSoItemUrl +'?ids=' + ids+'&d_date='+d_date+'&book_id='+book_id+'&rowCount='+rowCount+`&is_attribute=${isAttribute}&selected_items=${selectedItemsParam}`;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 200) {
                $("#analyzeDataTable").empty().append(data.data.pos);
                feather.replace();
                $("#prModal").modal('hide');
                $("#analyzeModal").modal('show');
                initStoreAutocomplete();
            }
            if(data.status == 422) {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                });
                return false;
            }
        });
    });
});

$(document).on('click', '.analyzeProcessBtn', (e) => {
    let ids = getSelectedPiIDS2();
    if (!ids.length) {
        // $("#prModal").modal('show');
        Swal.fire({
            title: 'Error!',
            text: 'Please select at least one line item',
            icon: 'error',
        });
        return false;
    }
    
    let isValid = true;
   $('.analyze_row:checked').each(function() {
        const $row = $(this).closest('tr');
        const $storeInput = $row.find("input[name='store_id']");
        const storeId = $storeInput.val();
        if (!storeId) {
            $row.find("input[name='store_name']").addClass('is-invalid'); // Bootstrap error styling
            isValid = false;
            return false;
        } else {
            $row.find("input[name='store_name']").removeClass('is-invalid');
        }
    });

    if (!isValid) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select store for all checked rows.',
            icon: 'error',
        });
        return;
    }

    ids = JSON.stringify(ids);
    let d_date = $("input[name='document_date']").val() || '';
    let book_id = $("#book_id").val() || '';
    let rowCount = $("#itemTable tbody tr[id*='row_']").length;
    let isAttribute = 0;
    if($("#attributeCheck").is(':checked')) {
        isAttribute = 1;
    } else {
        isAttribute = 0;
    }
    // if(!isAttribute) {
    //     $("#prModal .pi_item_checkbox:checked").each(function () {
    //         selectedItems.push({
    //             "sale_order_id": Number($(this).val()),
    //             "item_id": Number($(this).data("item-id"))
    //         });
    //     });
    // }
    let selectedItems = [];
    $('.analyze_row:checked').each(function() {
        let tr = $(this).closest('tr');
        if(!tr.hasClass('d-none')) {
            let $checkbox = $(this);
            let soItemIdsRaw = $checkbox.data('so-item-ids') || '';
            let soItemIds = soItemIdsRaw
                            .toString()
                            .split(',')
                            .map(id => id.trim().replace(/^['"]|['"]$/g, ''))
                            .map(id => Number(id))
                            .filter(id => id > 0);
                selectedItems.push({
                bom_id: Number($checkbox.val()),
                so_id: Number($checkbox.data('so-id')),
                so_item_id: Number($checkbox.data('so-item-id')),
                so_item_ids: soItemIds,
                level: Number($checkbox.data('level')),
                parent_bom_id: Number($checkbox.data('parent-bom-id')),
                item_id: Number($checkbox.data('item-id')),
                item_name: $checkbox.data('item-name'),
                item_code: $checkbox.data('item-code'),
                uom_id: Number($checkbox.data('uom-id')),
                uom_name: $checkbox.data('uom-name'),
                attribute: $checkbox.data('attribute'),
                total_qty: parseFloat($checkbox.data('total-qty')),
                store_name: $checkbox.data('store-name'),
                store_id: Number($checkbox.data('store-id')),
                doc_no: $checkbox.data('doc-no'),
                doc_date: $checkbox.data('doc-date'),
                main_so_item: Number($checkbox.data('main-so-item'))
            });
        }
    });
    let selectedItemsParam = JSON.stringify(selectedItems);
    let postData = {
        ids: ids,
        d_date: d_date,
        book_id: book_id,
        rowCount: rowCount,
        is_attribute: isAttribute,
        selected_items: selectedItems
    };
    fetch(processSoItemUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: JSON.stringify(postData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status == 200) {
            if ($("#itemTable > tbody tr").length) {
                $("#itemTable > tbody > tr:last").after(data.data.pos);
            } else {
                $("#itemTable > tbody").empty().append(data.data.pos);
            }
            updateRowIndex(true);
            $("#prModal").modal('hide');
            initializeAutocomplete2(".comp_item_code");
            initializeAutocompleteCustomer("[name*='[customer_code]']");
            let newIds = [];
            $('input[name^="components"][name$="[so_item_id]"]').each(function () {
                let val = $(this).val();
                if (val && val !== "0" && !newIds.includes(val)) {
                    newIds.push(val);
                }
            });
            let existingIds = localStorage.getItem('selectedSoItemIds');
            if (existingIds) {
                existingIds = JSON.parse(existingIds);
                const mergedIds = Array.from(new Set([...existingIds, ...newIds]));
                localStorage.setItem('selectedSoItemIds', JSON.stringify(mergedIds));
            } else {
                localStorage.setItem('selectedSoItemIds', JSON.stringify(newIds));
            }
            $("#analyzeModal").modal('hide');
        }

        if (data.status == 422) {
            Swal.fire({
                title: 'Error!',
                text: data.message,
                icon: 'error',
            });
            return false;
        }
    });
});