
$(document).ready(function () {

    document.addEventListener('keydown', function(e) {
        const allowedKeys = [
            'Backspace', 'ArrowLeft', 'ArrowRight', 'Delete', 'Tab', 'Enter', 'Escape'
        ];

        // Check if the target is an input of type number
        if (e.target && e.target.matches('input[type="number"]')) {
            if (
                (!allowedKeys.includes(e.key)) && // Allow special keys
                (e.key < '0' || e.key > '9') && // Allow digits
                (e.key !== '.') // Allow decimal point
            ) {
                e.preventDefault(); // Prevent non-numeric and alphabetic input
            }
        }
    });


    $('body').on("keypress", '.numberonly', function(e) {
        var charCode = (e.which) ? e.which : e.keyCode    
        if (String.fromCharCode(charCode).match(/[^0-9]/g))    
            return false;
    }); 
    
    $('body').on("keypress", '.decimal-only', function(evt) {
        return true;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
          if (charCode != 46 && charCode > 31 
            && (charCode < 48 || charCode > 57))
             return false;

          
    });

    $('body').on("keypress", '.time-input', function(e) {
        var charCode = (e.which) ? e.which : e.keyCode    
        if (String.fromCharCode(charCode).match(/[^0-9:]/g))    
        return false;
    });

    $('body').on("keyup change", '.time-input', function(e) {
        $('.time-input-error').remove();
        
        var durationInput = $(this).val();
        var regex = /^([0-3]?[0-9]):([0-5]?[0-9]):([0-5]?[0-9])$/;

        if (!regex.test(durationInput)) {
            $(this).after('<label class="control-label text-danger time-input-error" for="name">Please enter in the format HH:MM:SS</label>')
        }
    });

    // Set dynamic approval history section height
    setTimeout(() => {
        if ($('.basic-information').length) {
            if($('.basic-information').hasClass('col-md-8')) {
                let height = $('.basic-information').outerHeight();
                $(".customerapptimelinesapprovalpo").css('max-height',height-20);
            } else {
                let height = $('.basic-information').outerHeight();
                if($(".customerapptimelinesapprovalpo").length && height) {
                    $(".customerapptimelinesapprovalpo").css('max-height', height);
                }
            }
        }
    },100);

})

function getBaseUrl() {
    const protocol = window.location.protocol;
    const hostname = window.location.hostname; 
    const port = window.location.port ? `:${window.location.port}` : ''; 
    return `${protocol}//${hostname}${port}`;
}

function restrictPastDates(element)
{
    const currentDate = moment();
    const enteredDate = moment(element.value);
    if (enteredDate.isBefore(currentDate, 'day')) {
        element.value = moment().format('YYYY-MM-DD');
        Swal.fire({
            title: 'Error!',
            text: 'Previous date selection is not allowed',
            icon: 'error',
        });
    }
}

function restrictFutureDates(element)
{
    const currentDate = moment();
    const enteredDate = moment(element.value);
    if (currentDate.isBefore(enteredDate, 'day')) {
        element.value = moment().format('YYYY-MM-DD');
        Swal.fire({
            title: 'Error!',
            text: 'Future date selection is not allowed',
            icon: 'error',
        });
    }
}

function restrictBothFutureAndPastDates(element)
{
    const currentDate = moment();
    const enteredDate = moment(element.value);
    if (currentDate.isBefore(enteredDate, 'day') || enteredDate.isBefore(currentDate, 'day')) {
        element.value = moment().format('YYYY-MM-DD');
        Swal.fire({
            title: 'Error!',
            text: 'Future/ Past date selection is not allowed',
            icon: 'error',
        });
    }
}

function allowOnlyUppercase(event)
{
    const input = event.target;
    // Convert the current value to uppercase
    input.value = input.value.toUpperCase();
}

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

$(document).ready(function() {
    const baseUrl = getBaseUrl();
    function initializeAutocomplete(selector, config) {
        if ($(selector).length) {
        $(selector).autocomplete({
            source: function(request, response) {
                const url = typeof config.url === 'function' ? config.url() : config.url;
                const data = {
                    q: request.term,
                    type: config.type,
                    categoryId: config.categoryId ? config.categoryId() : null,
                    ...config.extraParams ? config.extraParams() : {}
                };
                $.ajax({
                    url: baseUrl + url,
                    method: 'GET',
                    dataType: 'json',
                    data: data,
                    success: function(data) {
                        $(selector).next('.text-danger').remove();
                        if (config.type === 'item-name') {
                            let exactMatch = data.find(item => 
                                (item[config.nameField] || '').toLowerCase() === request.term.toLowerCase()
                            );
                            if (exactMatch) {
                                $(selector).after('<span class="text-danger">This item name has already been taken.</span>');
                            }
                        } else {
                            $(selector).next('.text-danger').remove();
                        }
                        response($.map(data, function(item) {
                            var result = {
                                id: item.id,
                                label: (config.codeField && item[config.codeField] ? item[config.codeField] : '') + (config.nameField && item[config.nameField] ? ' - ' + (item[config.nameField].length > 50 ? item[config.nameField].substring(0, 45) + '...' : item[config.nameField]) : '') || item[config.labelField],
                                code: item[config.codeField] || '',
                                name: item[config.nameField] || '',
                                categoryName: item[config.categoryName] || '',
                                cat_initials: item.cat_initials || '',
                                hsn_id: item.hsn_id || '',    
                                sub_cat_initials: item.sub_cat_initials || '',
                                hsn_code: item.hsn_code || '', 
                                full_name: item.full_name || '', 
                                inspection_checklist_id: item.inspection_checklist_id || '',
                                inspection_checklist_name: item.inspection_name || '',  
                                unit_name: item.unit_name || '',  
                                hsn_name: item.description || '',  

                            };
                            if (config.additionalFields) {
                                config.additionalFields.forEach(function(field) {
                                    result[field] = item[field] || '';
                                });
                            }
                            return result;
                        }));
                    },
                    error: function(xhr) {
                        console.error('Error fetching data:', xhr.responseText);
                    }
                });
            },
            minLength: config.minLength || 0,
            select: function(event, ui) {
                if (config.allowSelection === false) {
                    event.preventDefault(); 
                    return false;
                }
                if (config.categoryName) {
                    $(this).val(ui.item.categoryName);
                } else if (ui.item.code) {
                    $(this).val(ui.item.code);
                } else {
                    $(this).val(ui.item.label);
                }
                const hiddenFieldSelector = config.hiddenFieldSelector.call(this);
                if (hiddenFieldSelector && hiddenFieldSelector !== "") { 
                    $(hiddenFieldSelector).val(ui.item.id);
                }
                if (config.onSelect) {
                    config.onSelect(ui.item);
                }
                return false;
            },
            change: function(event, ui) {
                if (!ui.item && $(this).val() === "") {
                    $(this).val("");  
                    const hiddenFieldSelector = config.hiddenFieldSelector.call(this);
                    if (hiddenFieldSelector && hiddenFieldSelector !== "") {
                        $(hiddenFieldSelector).val('');
                    }
                    $(".ledger-group-select").empty().trigger('change');
                    $(".ledger-group-id").val('');
                }
            }
        }).focus(function() {
            if (this.value === "") {
                $(this).autocomplete("search", "");
            }
        });
        }
    }
    initializeAutocomplete(".category-autocomplete", {
        url: '/search',
        type: 'category',
        labelField: 'full_name',
        categoryName: 'name',
        hiddenFieldSelector: function() { return '.category-id'; },
        minLength: 0,
        extraParams: function() {
            return {
                category_type: $('.category-type').val() 
            };
        },
        onSelect: function(selectedItem) {
        if (selectedItem.cat_initials) {
            $('.cat_initials-id').val(selectedItem.cat_initials).change();
        } else if (selectedItem.sub_cat_initials) {
            $('.cat_initials-id').val(selectedItem.sub_cat_initials).change();
        } else {
            $('.cat_initials-id').val(''); 
        }
        $('.subcategory-autocomplete').val(''); 
        $('.subcategory-id').val('');
        if (selectedItem.hsn_id) {
            $('.hsn-id').val(selectedItem.hsn_id); 
            $('.hsn-autocomplete').val(selectedItem.hsn_code);
        } else {
            $('.hsn-id').val(''); 
            $('.hsn-autocomplete').val('');
        }
         if (selectedItem.inspection_checklist_id) {
            $('.inspection_checklist_id').val(selectedItem.inspection_checklist_id);
            $('.inspection-autocomplete').attr('placeholder', selectedItem.inspection_checklist_name);
        } else {
            $('.inspection_checklist_id').val('');
            $('.inspection-autocomplete').val('');
        }
        }
    });
    initializeAutocomplete(".subcategory-autocomplete", {
        url: '/search',
        type: 'subcategory',
        labelField: 'name',
        hiddenFieldSelector: function() { return '.subcategory-id'; },
        minLength: 0,
        categoryId: function() {
            return $('input[name="category_id"]').val();
        },
        extraParams: function() {
            return {
                category_type: $('.category-type').val() 
            };
        },
        onSelect: function(selectedItem) {
            $('.sub_cat_initials-id').val(selectedItem.sub_cat_initials).change(); 
        }
    });

    initializeAutocomplete(".hsn-autocomplete", {
        url: '/search',
        type: 'hsn',
        codeField: 'code',
        nameField: 'description',
        hiddenFieldSelector: function() { return '.hsn-id'; },
        minLength: 0,
        additionalFields: ['description']
    });

    initializeAutocomplete(".item-name-autocomplete", {
        url: '/search',
        type: 'item-name',
        codeField: 'item_code',
        nameField: 'item_name',
        minLength: 3,
        allowSelection: false, 
    });
    initializeAutocomplete(".customer-name-autocomplete", {
        url: '/search',
        type: 'customer-name',
        codeField: 'customer_code',
        nameField: 'company_name',
        minLength: 3,
        allowSelection: false, 
    });
    initializeAutocomplete(".vendor-name-autocomplete", {
        url: '/search',
        type: 'vendor-name',
        codeField: 'vendor_code',
        nameField: 'company_name',
        minLength: 3,
        allowSelection: false, 
    });

    initializeAutocomplete(".inspection-autocomplete", {
        url: '/search',
        type: 'checklist',
        labelField: 'name',
        hiddenFieldSelector: function() { return '.inspection_checklist_id'; },
        minLength: 0,
    });

    initializeAutocomplete(".parent-vendor-autocomplete", {
        url: '/search',
        type: 'vendor',
        labelField: 'company_name',
        hiddenFieldSelector: function() { return '.reld_vendor_id'; },
        minLength: 0,
        extraParams: function() {
            return {
                vendor_id: $('.vendor_id').val() 
            };
        },
    });

    initializeAutocomplete(".contra-ledger-autocomplete", {
        url: '/search',
        type: 'contraLedger',
        labelField: 'name',
        hiddenFieldSelector: function() { return '.contra_ledger_id'; },
        minLength: 0,
    });

    initializeAutocomplete(".parent-customer-autocomplete", {
        url: '/search',
        type: 'customer',
        labelField: 'company_name',
        hiddenFieldSelector: function() { return '.reld_customer_id'; },
        minLength: 0,
        extraParams: function() {
            return {
                customer_id: $('.customer_id').val() 
            };
        },
    });

    initializeAutocomplete(".ladger-autocomplete", {
        url: '/search',
        type: 'ladger',
        labelField: 'name',
        hiddenFieldSelector: function() { return '.ladger-id'; },
        minLength: 0,
        additionalFields: ['description'],
    });

    initializeAutocomplete(".bank-ladger-autocomplete", {
        url: '/search',
        type: 'accounLadger',
        labelField: 'name',
        hiddenFieldSelector: function() { return '.ladger-id'; },
        minLength: 0,
        additionalFields: ['description'],
    });

    initializeAutocomplete(".ledger-group-autocomplete", {
        url: '/search',
        type: 'ledgerGroup',
        labelField: 'name',
        hiddenFieldSelector: function() { return '.ledger-group-id'; },
        minLength: 0,
        additionalFields: ['description'],
        source: []
    });

    initializeAutocomplete("#service_provider_ledger_id", {
        url: '/search',
        type: 'ladger',
        labelField: 'name',
        hiddenFieldSelector: function() { return '#ledger_id_service_provider'; },
        minLength: 0,
        additionalFields: ['description'],
    });
    

    initializeAutocomplete(".sales-person-autocomplete", {
        url: '/search',
        type: 'salesPerson',
        labelField: 'name',
        hiddenFieldSelector: function() { return '.sales-person-id'; },
        minLength: 0,
        additionalFields: ['description']
    });

    initializeAutocomplete(".unit-code-autocomplete", {
        url: '/search',  
        type: 'unit_code', 
        codeField: 'unit_code',
        nameField: 'unit_name',
        hiddenFieldSelector: function() { return '#unit_master_id'; },
        minLength: 0,
        onSelect: function(item) {
            console.log(item);
            $('#unit_code').val(item.code);
            $('#unit_master_id').val(item.id);  
            $('#unit_name').val(''); 
            $('#unit_name').val(item.unit_name);
        }
    });

    $('#unit_code').on('input', function() {
        const unitCodeValue = $(this).val();
        if (unitCodeValue === "") {
            $('#unit_name').val(''); 
        }
    });

    initializeAutocomplete(".hsn-code-autocomplete", {
        url: '/search',  
        type: 'hsn_code', 
        codeField: 'code',
        nameField: 'description',
        hiddenFieldSelector: function() { return '#hsn_master_id'; },
        minLength: 0,
        onSelect: function(item) {
            $('#hsn_code').val(item.code);
            $('#hsn_master_id').val(item.id);  
            $('#hsn_description').val(''); 
            $('#hsn_description').val(item.hsn_name);
        }
    });

    function updateLedgerGroupDropdown(ledgerId) {
        var $ledgerGroupSelect = $(".ledger-group-select");
        $ledgerGroupSelect.empty();

        $.ajax({
            url: '/ledgers/' + ledgerId + '/groups', 
            method: 'GET',
            success: function(data) {
                if (Array.isArray(data) && data.length) {
                    data.forEach(function(group) {
                        var option = new Option(group.name, group.id);
                        $ledgerGroupSelect.append(option);
                    });
                    var preselectedGroupId = $(".ledger-group-id").val();
                    if (preselectedGroupId) {
                        $ledgerGroupSelect.val(preselectedGroupId).trigger('change');
                    }
                } else {
                    console.error('No groups found for this ledger');
                }
            },
            error: function() {
                alert('An error occurred while fetching Ledger Groups.');
            }
        });
    }

    $(".ladger-autocomplete, .bank-ladger-autocomplete").on("autocompleteselect", function(event, ui) {
        var ledgerId = ui.item.id;
        if (ledgerId) {
            $(".ledger-group-select").val("");
            $(".ledger-group-id").val("");
            updateLedgerGroupDropdown(ledgerId); 
        }
    });

    var initialLedgerId = $(".ladger-id").val();
    if (initialLedgerId) {
        updateLedgerGroupDropdown(initialLedgerId);
    }
    // $(document).ready(function() {
    //     function loadSubcategories(categoryId, selectedSubcategoryId, subcategorySelect) {
    //         if (categoryId) {
    //             $.ajax({
    //                 url: '/categories/subcategories/' + categoryId,
    //                 method: 'GET',
    //                 success: function(response) {
    //                     subcategorySelect.empty();
    //                     subcategorySelect.append('<option value="">Select Sub-Category</option>');
    //                     $.each(response, function(index, subcategory) {
    //                         subcategorySelect.append(
    //                             '<option value="' + subcategory.id + '"' + 
    //                             (subcategory.id == selectedSubcategoryId ? ' selected' : '') + '>' + 
    //                             subcategory.name + '</option>'
    //                         );
    //                     });
    //                 },
    //                 error: function() {
    //                     alert('An error occurred while fetching subcategories.');
    //                 }
    //             });
    //         } else {
    //             subcategorySelect.empty();
    //             subcategorySelect.append('<option value="">Select Sub-Category</option>');
    //         }
    //     }
    //     $('select[name="category_id"]').change(function() {
    //         var categoryId = $(this).val();
    //         var subcategorySelect = $('select[name="subcategory_id"]'); 
    //         var selectedSubcategoryId = subcategorySelect.data('selected-id');
    //         loadSubcategories(categoryId, selectedSubcategoryId, subcategorySelect);
    //     });
    
    //     function initializeSubcategories() {
    //         var categoryId = $('select[name="category_id"]').val(); 
    //         var subcategorySelect = $('select[name="subcategory_id"]'); 
    //         var selectedSubcategoryId = subcategorySelect.data('selected-id');
    //         loadSubcategories(categoryId, selectedSubcategoryId, subcategorySelect);
    //     }
 
    //     initializeSubcategories();
    // });

});

function capitalizeFirstLetter(text) {
    return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
}















