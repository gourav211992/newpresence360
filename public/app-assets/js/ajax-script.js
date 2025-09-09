
function getCsrfToken() {
    const token = $('meta[name="csrf-token"]').attr('content');
    if (!token) {
        console.warn('CSRF token not found');
    }
    return token;
}

$.ajaxSetup({
    headers: {
        'X-CSRF-TOEKN': getCsrfToken()
    }
});

$(document)
    .ajaxStart(function () {
        $('#loader-div').show();
    })
    .ajaxStop(function () {
        $('#loader-div').hide();
    });

$(document).on('submit', '.ajax-input-form', function (e) {

    e.preventDefault();
    const currentFrom = this;
    var formObj = $(this);

    // Disabled input ad select field temp enabled then disabled for send value in request
    formObj.find('input:disabled, select:disabled').each(function () {
        $(this).attr('data-was-disabled', true).prop('disabled', false);
    });

    //Add a basic loader
    const loader = document.getElementById("erp-overlay-loader");
    loader.style.display = "flex";

     var submitButton = (e.originalEvent && e.originalEvent.submitter)
                        || $(this).find(':submit');
    var submitButtonHtml = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    submitButton.disabled = true;
    var method = $(this).attr('method');
    var url = $(this).attr('action');
    var redirectUrl = $(this).data('redirect');
    var data = new FormData($(this)[0]);
    // After enabled again disabled
    formObj.find('[data-was-disabled="true"]').each(function () {
        $(this).prop('disabled', true).removeAttr('data-was-disabled');
    });
    /*Additional append data*/
    const keys = [
        'deletedItemDiscTedIds',
        'deletedHeaderDiscTedIds',
        'deletedHeaderExpTedIds',
        'deletedPiItemIds',
        'deletedSoItemIds',
        'deletedSiItemIds',
        'deletedItemIds', // Common
        'deletedConsItemIds',
        'deletedAttachmentIds',
        'deletedDelivery',
        'deletedMrnItemIds',
        'deletedPRItemIds',
        'deletedPBItemIds',
        'deletedInspItemIds',
        'deletedItemLocationIds'
    ];

    keys.forEach(key => {
        const value = localStorage.getItem(key);
        if (value) {
            data.append(key, value);
            // localStorage.removeItem(key);
        }
    });

    const bomkeys = [
        'deletedItemOverheadIds',
        'deletedHeaderOverheadIds',
        'deletedBomItemIds',
        'deletedProdItemIds',
        'deletedInstructionItemIds'
    ];

    bomkeys.forEach(key => {
        const value = localStorage.getItem(key);
        if (value) {
            data.append(key, value);
            // localStorage.removeItem(key);
        }
    });

    //Only for Sales module
    if (this.classList.contains('sales_module_form')) {
        const items = document.getElementsByClassName('comp_item_code');
        const itemLocations = document.getElementsByClassName('item_store_locations');
        for (let index = 0; index < items.length; index++) {
            data.append(`item_attributes[${index}]`, items[index].getAttribute('attribute-array'));
        }
        for (let index = 0; index < itemLocations.length; index++) {
            data.append(`item_locations[${index}]`, (decodeURIComponent(itemLocations[index].getAttribute('data-stores'))));
        }
    }
    if (this.classList.contains('psv_form')) {
        const items = document.getElementsByClassName('comp_item_code');
        for (let index = 0; index < items.length; index++) {
            data.append(`item_attributes[${index}]`, items[index].getAttribute('selected-attribute'));
        }

    }
    if (this.classList.contains('material_issue')) {
        const items = document.getElementsByClassName('comp_item_code');
        const itemLocations = document.getElementsByClassName('item_locations_to');
        for (let index = 0; index < itemLocations.length; index++) {
            data.append(`item_locations_to[${index}]`, (decodeURIComponent(itemLocations[index].getAttribute('data-stores'))));
        }
    }
    if (this.classList.contains('material_return')) {
        const items = document.getElementsByClassName('comp_item_code');
        const itemLocations = document.getElementsByClassName('item_locations_to');
        for (let index = 0; index < itemLocations.length; index++) {
            data.append(`item_locations_to[${index}]`, (decodeURIComponent(itemLocations[index].getAttribute('data-stores'))));
        }
    }
    if (this.classList.contains('production_slip')) {
        const itemLocations = document.getElementsByClassName('item_locations_to');
        for (let index = 0; index < itemLocations.length; index++) {
            data.append(`item_locations_to[${index}]`, (decodeURIComponent(itemLocations[index].getAttribute('data-stores'))));
        }
        const itemBundles = document.getElementsByClassName('item_bundles');
        for (let index = 0; index < itemBundles.length; index++) {
            data.append(`item_bundles[${index}]`, (decodeURIComponent(itemBundles[index].getAttribute('data-bundles'))));
        }
    }
    // if (this.classList.contains('sales_order')) {
    //     const itemBoms = document.getElementsByClassName('dynamic_bom_div');
    //     for (let index = 0; index < itemBoms.length; index++) {
    //         data.append(`item_bom_details[${index}]`, (JSON.stringify(itemBoms[index].getAttribute('bom_details'))));
    //     }
    // }
    if (this.classList.contains('sales_order')) {
        const itemBoms = document.getElementsByClassName('dynamic_bom_div');
        for (let index = 0; index < itemBoms.length; index++) {
            let bomDetails = itemBoms[index].getAttribute('bom_details');

            try {
                bomDetails = JSON.parse(bomDetails); // Ensure it's parsed JSON
                data.append(`item_bom_details[${index}]`, JSON.stringify(bomDetails));
            } catch (error) {
                console.error("Invalid JSON format in bom_details:", bomDetails, error);
            }
        }
    }
    if (this.classList.contains('sale_invoice')) {
        const itemCheckedBundles = document.getElementsByClassName('item_bundles');
        for (let index = 0; index < itemCheckedBundles.length; index++) {
            data.append(`bundle_ids[${index}]`, (decodeURIComponent(itemCheckedBundles[index].getAttribute('checked-bundle'))));
        }
    }

    if (this.classList.contains('scrap_module_form')) {
        const items = document.getElementsByClassName('attributeBtn');
        for (let index = 0; index < items.length; index++) {
            data.append(`item_attributes[${index}]`, items[index].getAttribute('attribute-array'));
        }
    }

    // if (typeof selectedAttachmentsMain !== 'undefined')
    // {
    //     selectedAttachmentsMain.forEach((element, index) => {
    //         data.append(`attachments[${index}]`, element);
    //     });
    // }

    // This is for use convert form data to json for all module common
    if($(e.target).find('[data-json-key]').length) {
        $(e.target).find('[data-json-key]').each(function () {
            const container = $(this);
            const jsonKey = container.data('json-key');
            const rowSelector = container.data('row-selector') || 'tr';
            const dataModule = $(e.target).data('module');
            appendSerializedFormRows(data, container, jsonKey, {
                rowSelector,
                dataModule
            });
        });
    }

    $.ajax({
        url,
        type: method,
        data,
        contentType: false,
        processData: false,
        success: function (res) {
            // Do not enable button while redirecting or showing a success message
            // submitButton.disabled = true;

            submitButton.innerHTML = submitButtonHtml;
            $('.ajax-validation-error-span').remove();
            $(".is-invalid").removeClass("is-invalid");
            $(".help-block").remove();
            $(".waves-ripple").remove();
            loader.style.display = "none";
            Swal.fire({
                title: 'Success!',
                text: res.message,
                icon: 'success',
            });
            setTimeout(() => {
                if (res.store_id) {
                    location.href = `/stores/${res.store_id}/edit`;
                } else if (res?.redirect_url) {
                    if (res.redirect_url.includes('pdf')) {
                        window.open(res.redirect_url, '_blank');
                        const currentUrl = window.location.origin;
                        const path = window.location.pathname.split('/').filter(part => part);
                        const urlWithFirstSlug = path.length > 0 ? `${currentUrl}/${path[0]}` : currentUrl;
                        location.href = urlWithFirstSlug;
                    } else {
                        if(res?.redirect_url.includes('bill-of-material/import-error')) {
                            $('.download-error-file-url').removeClass('d-none');
                            $('.download-error-file-url').attr('href', res?.redirect_url);
                        }else{
                            location.href = res?.redirect_url;
                        }
                    }
                } else if (redirectUrl) {
                    location.href = redirectUrl;
                } else {
                    location.reload();
                }
            }, 1500);

        },
        error: function (error) {
            if (currentFrom.dataset.completionfunction) {
                window[currentFrom.dataset.completionfunction]();
            }
            if (error.responseJSON && error.responseJSON.refresh_page) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.responseJSON.message || 'Something went wrong!',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
                return;
            }
            submitButton.disabled = false;
            submitButton.innerHTML = submitButtonHtml;
            loader.style.display = "none";
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
                // let errors = res.errors;
                // for (const [key, errorMessages] of Object.entries(errors)) {
                //     var name = key.replace(/\./g, "][").replace(/\]$/, "");
                //     formObj.find(`[name="${name}"]`).parent().append(
                //         `<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">${errorMessages[0]}</span>`
                //     );
                // }
                Object.keys(res.errors).forEach(function (key) {
                    var tabId = getTabId(key);
                    if (tabId) {
                        var tabLink = $('a[href="#' + tabId + '"]');
                        if (tabLink.length) {
                            if (!tabLink.hasClass('text-danger')) {
                                if (!tabLink.find('i').length) {
                                    tabLink.prepend('<i data-feather="alert-triangle" class="text-danger"></i>');
                                }
                                tabLink.addClass('text-danger');
                                feather.replace();
                            }
                        } else {
                            console.log('Tab link with href "' + tabId + '" not found in the DOM.');
                        }
                    }
                });
                function getTabIdForField(field) {
                    let tabId = null;
                    $('.tab-pane').each(function () {
                        const tabPaneId = $(this).attr('id');
                        const isFieldInsideTab = $(this).find('[name="' + field + '"]').length > 0;

                        if (isFieldInsideTab) {
                            tabId = tabPaneId;
                            return false;
                        }
                    });
                    return tabId;
                }
                function getTabIdForNestedField(field) {
                    const fields = field.split('.');
                    let tabId = null;
                    $('.tab-pane').each(function () {
                        const tabPaneId = $(this).attr('id');
                        let isFieldInsideTab = false;

                        $(this).find('input, select').each(function () {
                            const name = $(this).attr('name');
                            if (name && name.startsWith(fields[0])) {
                                isFieldInsideTab = true;
                                return false;
                            }
                        });

                        if (isFieldInsideTab) {
                            tabId = tabPaneId;
                            return false;
                        }
                    });

                    return tabId;
                }
                function getTabId(field) {
                    let tabId = getTabIdForField(field);
                    if (tabId) {
                        return tabId;
                    } else {
                        return getTabIdForNestedField(field);
                    }
                }

                // this function automatically scrolls to the first error field in tab
                if(res.is_tab_exist) {
                    setTimeout(() => {
                        const tabPanes = document.querySelectorAll('.tab-pane');
                        for (const pane of tabPanes) {
                            const hasError = pane.querySelector('.is-invalid, .ajax-validation-error-span');
                            if (hasError) {
                                const tabId = pane.id;
                                const tabButton = document.querySelector(`button[data-bs-target="#${tabId}"]`);
                                if (tabButton) {
                                    // tabButton.classList.add('tab-error-highlight');
                                    const isActive = tabButton.classList.contains('active');
                                    if (isActive) {
                                        hasError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    } else {
                                        tabButton.addEventListener('shown.bs.tab', () => {
                                            hasError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        }, { once: true });
                                        const tab = new bootstrap.Tab(tabButton);
                                        tab.show();
                                    }
                                }
                                break;
                            }
                        }
                    }, 100);
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

/**
 * Serialize form rows (table or divs) into a JSON blob and append to FormData.
 *
 * @param {FormData} formData - The FormData object to append to.
 * @param {string} containerSelector - Selector for the parent container or table (e.g., '.bom_form #itemTable' or '.overhead-container').
 * @param {string} jsonKey - The key under which to store the JSON blob (e.g., 'components_json').
 * @param {object} options - Optional parameters:
 *                           - rowSelector: override for row elements inside the container.
 *                           - cleanupRegex: RegExp to remove related keys from FormData.
 */
function appendSerializedFormRows(formData, containerSelector, jsonKey, options = {}) {
    const {
        rowSelector = 'tr[id^="row_"], .display_overhead_row',
        dataModule = null,
    } = options;
    const $container = $(containerSelector);
    const $rows = $container.find(rowSelector);
    if ($rows.length) {
        const jsonArray = [];
        $rows.each(function () {
            const obj = {};
            let checkbox = $(this).find('input.pi_item_checkbox');
            if (checkbox.length === 0) {
                checkbox = $(this).find('input.form-check-input');
            }
            const isChecked = checkbox.is(':checked');
            obj['is_pi_item_id'] = isChecked;
            $(this).find('input, select, textarea').each(function () {
                const name = $(this).attr('name');
                const value = $(this).val();
                if (name) {
                    obj[name] = value;
                }
            });
            jsonArray.push(obj);
        });
        let cleanupRegex = null;
        let cleanupRegex2 = null;
        let cleanupPatterns = [];
        switch (dataModule) {
            case 'pi':
                cleanupRegex = '^components\\[\\d+\\]\\[.*\\]$';
                break;
            case 'po':
                cleanupRegex = '^components\\[\\d+\\]\\[.*\\]$';
                break;
            case 'jo':
                cleanupRegex = '^components\\[\\d+\\]\\[.*\\]$';
                cleanupRegex2 = '^component\\[\\d+\\]\\[.*\\]$';
                break;
            case 'bom':
                cleanupRegex = '^components\\[\\d+\\]\\[.*\\]$';
                cleanupRegex2 = "^header\[\d+\]\[overhead\]\[\d+\]\[.*\]$";
            case 'pwo':
                cleanupRegex = '^components\\[\\d+\\]\\[.*\\]$';
                break;
            case 'mrn':
                cleanupRegex = '^components\\[\\d+\\]\\[.*\\]$';
                cleanupRegex2 = '^component\\[\\d+\\]\\[.*\\]$';
                break;
            case 'ge':
                cleanupRegex = '^components\\[\\d+\\]\\[.*\\]$';
                cleanupRegex2 = '^component\\[\\d+\\]\\[.*\\]$';
                break;
            case 'mo':
                cleanupPatterns.push(
                    /^components\\[\\d+\\]\\[.*\\]$/,
                    /^component\\[\\d+\\]\\[.*\\]$/,
                    /^instructions\[\d+](\[[^\]]+])+$/,
                );
                break;
            case 'pslip':
                cleanupPatterns.push(
                    /^cons\[\d+\]\[[^\]]+\]$/,
                    /^instructions\[\d+\]\[[^\]]+\]$/,
                    /^so_doc\d+$/,
                    /^customer\d+$/,
                    /^customer_id\d+$/,
                    /^item_code\d+$/,
                    /^item_name\d+$/,
                    /^uom_id\d+$/,
                    /^mo_product_id\d+$/,
                    /^mo_id\d+$/,
                    /^so_id\d+$/,
                    /^so_item_id\d+$/,
                    /^station_id\d+$/,
                    /^station_id\d+$/,
                    /^item_so_qty_\d+$/,
                    /^item_qty\d+$/,
                    /^item_accepted_qty\d+$/,
                    /^item_sub_prime_qty\d+$/,
                    /^item_rejected_qty\d+$/,
                    /^machine_id\d+$/,
                    /^item_remarks\d+$/,
                    /^item_id\\[\\]$/
                    // /^.*_\d+$/
                );
                break;
            case 'scrap':
                cleanupRegex = '^components\\[\\d+\\]\\[.*\\]$';
                cleanupRegex2 = '^component\\[\\d+\\]\\[.*\\]$';
                break;
            default:
                console.warn("No cleanup regex defined for module:", dataModule);
        }
        if(cleanupRegex || cleanupRegex2 || cleanupPatterns.length) {
            removeKeysByRegex(formData, cleanupRegex,cleanupRegex2,cleanupPatterns);
        }
        formData.append(jsonKey, JSON.stringify(jsonArray));
    }
}

function removeKeysByRegex(formData, pattern, pattern2 = '', cleanupPatterns = []) {
    const regex = new RegExp(pattern);
    for (let [key] of Array.from(formData.entries())) {
        if (regex.test(key)) {
            formData.delete(key);
        }
    }
    if(pattern2) {
        const regex2 = new RegExp(pattern2);
        for (let [key2] of Array.from(formData.entries())) {
            if (regex2.test(key2)) {
                formData.delete(key2);
            }
        }
    }

    if(cleanupPatterns.length) {
        Object.keys(formData).forEach(key => {
            if (cleanupPatterns.some(pattern => pattern.test(key))) {
                delete formData[key];
            }
        });
    }
}

$(document).on('click', '.submit-button', (e) => {
    let status = e.target.closest('button').value;
    $('#document_status').val(status);
});

$('#save-draft-button').on('click', function (e) {
    $(this).data('clicked', true);
    document.getElementById('document_status').value = 'draft';
    $('.ajax-input-form').submit();
    $(this).data('clicked', false);
});

$('#submit-button').on('click', function (e) {
    $(this).data('clicked', false);
    document.getElementById('document_status').value = 'submitted';
    $('.ajax-input-form').submit();
});

function show_validation_error(msg) {
    if ($.isPlainObject(msg)) {
        $data = msg;
    } else {
        $data = $.parseJSON(msg);
    }

    $.each($data, function (index, value) {
        var name = index.replace(/\./g, "][");

        if (index.indexOf(".") !== -1) {
            name = name + "]";
            name = name.replace("]", "");
        }
        if (index === "sub_types" || index === "sub_types[]") {
            if ($('form [name="sub_types[]"]:checked').length === 0 && !$('#tradedItemCheckbox').is(':checked') && !$('#assetCheckbox').is(':checked')) {
                var checkboxGroupContainer = $('form [name="sub_types[]"]').first().closest('.demo-inline-spacing');
                if (checkboxGroupContainer.find('.ajax-validation-error-span').length === 0) {
                  checkboxGroupContainer.after(
                    '<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">Please select at least one subtype.</span>'
                  );
                  checkboxGroupContainer.addClass("is-invalid error");
                }
              } else {
                $('.ajax-validation-error-span').remove();
                $('form [name="sub_types[]"]').first().closest('.demo-inline-spacing').removeClass("is-invalid error");
              }

        } else if (name.indexOf("[]") !== -1) {
            $('form [name="' + name + '"]')
                .last()
                .closest("")
                .addClass("is-invalid error");
            $('form [name="' + name + '"]')
                .last()
                // .closest(".input-group")
                .find("")
                .append(
                    '<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">' +
                    value +
                    "</span>"
                );
        } else if ($('form [name="' + name + '[]"]').length > 0) {
            if($('form [name="' + name + '[]"]').next('.select2-container').length > 0) {
                $('form [name="' + name + '[]"]')
                .addClass("is-invalid error");
                $('form [name="' + name + '[]"]')
                    .next('.select2-container')
                    .after(
                        '<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">' +
                        value +
                        "</span>"
                    );
            } else {
                $('form [name="' + name + '[]"]')
                // .closest(".input-group")
                .addClass("is-invalid error");
            $('form [name="' + name + '[]"]')
                .parent()
                .after(
                    '<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">' +
                    value +
                    "</span>"
                );
            }

        } else if ($('form [name="' + name + '"]').length > 0) {
            if ($('form [name="' + name + '"]').is('select')) {
                $('form [name="' + name + '"]').addClass("is-invalid error");
                $('form [name="' + name + '"]').next('.select2-container').after(
                    '<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">' +
                    value +
                    "</span>"
                );
            } else {
                $('form [name="' + name + '"]').addClass("is-invalid");
                $('form [name="' + name + '"]').after(
                    '<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px" role="alert">' +
                    value +
                    "</span>"
                );
            }
        } else {

            if (
                $('form [name="' + name + '"]').attr("type") == "checkbox" ||
                $('form [name="' + name + '"]').attr("type") == "radio") {
                    if (
                        $('form [name="' + name + '"]').attr("type") == "checkbox"
                    ) {

                        $('form [name="' + name + '"]')
                            // .closest(".form-group")
                            .addClass("is-invalid error");
                        $('form [name="' + name + '"]')
                            .parent()
                            .after(
                                '<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">' +
                                value +
                                "</span>"
                            );
                    } else {
                        $('form [name="' + name + '"]')
                            // .closest(".input-group")
                            .addClass("is-invalid error");
                        $('form [name="' + name + '"]')
                            .parent()
                            .parent()
                            .append(
                                '<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">' +
                                value +
                                "</span>"
                            );
                    }
            } else if ($('form [name="' + name + '"]').get(0)) {

                if (
                    $('form [name="' + name + '"]').get(0).tagName == "SELECT"
                ) {

                    $('form [name="' + name + '"]')
                        // .closest(".form-group")
                        .addClass("is-invalid error");
                    $('form [name="' + name + '"]')
                        // .parent()
                        .after(
                            '<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">' +
                            value +
                            "</span>"
                        );
                } else if (
                    $('form [name="' + name + '"]').attr("type") ==
                    "password" &&
                    $('form [name="' + name + '"]').hasClass(
                        "hideShowPassword-field"
                    )
                ) {
                    $('form [name="' + name + '"]')
                        // .closest(".input-group")
                        .addClass("is-invalid error");
                    $('form [name="' + name + '"]')
                        .parent()
                        .after(
                            '<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">' +
                            value +
                            "</span>"
                        );
                } else {
                    let ckeditor = document.querySelector(`div[id="cke_${name}"]`);
                    if (ckeditor) {
                        $(ckeditor).after(
                            '<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px" role="alert">' +
                            value +
                            "</span>"
                        );
                    } else {
                        $('form [name="' + name + '"]')
                            // .closest(".input-group")
                            .addClass("is-invalid");
                        $('form [name="' + name + '"]').after(
                            '<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px" role="alert">' +
                            value +
                            "</span>"
                        );
                    }
                }
            } else {
                $('form [name="' + name + '"]')
                    .closest(".input-group")
                    .addClass("is-invalid error");
                $('form [name="' + name + '"]').after(
                    '<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">' +
                    value +
                    "</span>"
                );
                if (name == 'anwser_required') {
                    toast("warning", value);
                }
                name = name.replace(/\[\d+\]$/, '');
                if ($(`[name='${name}[]']`).length) {
                    $('form [name="' + name + '"]')
                        .closest(".input-group")
                        .addClass("is-invalid error");
                    $('form [name="' + name + '[]' + '"]').after(
                        '<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">' +
                        value +
                        "</span>"
                    );
                }
                //Approvers
                // if (name === 'user' || name === 'level_organization_id' || name === 'min_value' || name === 'rights')
                if (name == "custom_error")
                {
                    Swal.fire({
                        title: 'Error!',
                        text: value,
                        icon: 'error',
                    });
                    return;
                }
            }
        }
        // $('.error-message').html($('.error-message').text().replace(".,",". "));
    });

    /*SCROLLING TO THE INPUT BOX*/
    // scroll();
}

Object.size = function (obj) {
    var size = 0,
        key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

// Global Variables
let originalGstin = '';

$(document).ready(function() {
    originalGstin = $('input[name="compliance[gstin_no]"]').val() || '';
    const statusElem = document.getElementById('documentStatus');
    const status = statusElem ? statusElem.value : null;
    let previousGstApplicable = $('input[name="compliance[gst_applicable]"]:checked').val() === '1' ? 1 : 0;
    if (status === 'submitted' || status === 'approved' || status === 'approval_not_required') {
        disableGstFields(false);
    } else {
        if (previousGstApplicable === 1) {
            enableGstFields();
        } else {
            disableGstFields(true);
        }
    }

    $('#gstinNo').on('input blur', function() {
        handleGstInputOrChange();
    });


     $('input[name="compliance[gst_applicable]"]').on('change', function() {
        const currentGstApplicable = $('input[name="compliance[gst_applicable]"]:checked').val() === '1' ? 1 : 0;
         if (currentGstApplicable === 0 && previousGstApplicable === 1) {
           disableGstFields(true);
        } else if (currentGstApplicable === 1 && previousGstApplicable === 0) {
            handleGstApplicableChange();
            enableGstFields();
        }
        previousGstApplicable = currentGstApplicable;
    });
});


function handleGstApplicableChange() {
    var currentGstin = $('#gstinNo').val().trim();
    const gstApplicable = $('input[name="compliance[gst_applicable]"]:checked').val() === '1' ? 1 : 0;
    if (currentGstin.length === 15 && gstApplicable === 1) {
        fetchGstDetailsByGstin(currentGstin);
    }
}

function handleGstInputOrChange() {
    var currentGstin = $('#gstinNo').val().trim();
    const gstApplicable = $('input[name="compliance[gst_applicable]"]:checked').val() === '1' ? 1 : 0;

    if (!currentGstin || currentGstin !== originalGstin ) {
        resetGstDependentFields();
    }
    if (currentGstin.length === 15 && gstApplicable === 1) {
        fetchGstDetailsByGstin(currentGstin);
    }
}

function disableGstFields(resetValues = true) {
    $('input[name="compliance[gstin_no]"]').prop('disabled', true);
    $('input[name="compliance[gstin_registration_date]"]').prop('disabled', true);
    $('input[name="compliance[gst_registered_name]"]').prop('disabled', true);

    if (resetValues) {
        $('input[name="compliance[gstin_no]"]').val('');
        $('input[name="compliance[gstin_registration_date]"]').val('');
        $('input[name="compliance[gst_registered_name]"]').val('');
    }
}

function enableGstFields() {
    $('input[name="compliance[gstin_no]"]').prop('disabled', false);
    $('input[name="compliance[gstin_registration_date]"]').prop('disabled', false);
    $('input[name="compliance[gst_registered_name]"]').prop('disabled', false);
}
function resetGstDependentFields() {
    $('input[name="compliance[gstin_registration_date]"]').val('');
    $('input[name="compliance[gst_registered_name]"]').val('');
    $('.error-message').remove();
    $('.field-error-message').remove();
    $('.is-invalid').removeClass('is-invalid');
}

$('#fetchGstDetailsBtn').click(function() {
    $('#gstinModal').modal().modal('show');
});

// Fetch GST Details
$('#fetchGstDetails').click(function() {
    var gstinNo = $('#gstinInput').val();
    if (!gstinNo || gstinNo !== originalGstin) {
        resetGstDependentFields();
    }
    fetchGstDetailsByGstin(gstinNo);
});
function updateRowIndexes() {
    var $rows = $('#address-table-body tr');
    $('#address-table-body .address-row').each(function(index) {
        $(this).find('.index').text(index + 1);
        $(this).find('input, select').each(function() {
            $(this).attr('name', $(this).attr('name').replace(/\[\d+\]/, `[${index}]`));
        });
        if ($rows.length === 1) {
            $(this).find('.delete-address').hide();
            $(this).find('.add-address').show();
        } else {
            $(this).find('.delete-address').show();
            $(this).find('.add-address').toggle(index === 0);
        }
    });
}

function initializeGstAddressAutocomplete($row) {
    // Country Autocomplete
    $row.find('.country-input').autocomplete({
        source: function(request, response) {
            $.get('/countries', { term: request.term }, function(data) {
                response(data.data.countries.map(country => ({
                    label: country.label,
                    value: country.value,
                    id: country.value
                })));
            });
        },
        minLength: 0,
        select: function(event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('tr').find('.country-id').val(ui.item.id);
            const $stateInput = $(this).closest('tr').find('.state-input');
            $stateInput.val('').removeAttr('data-state-id');
            const $cityInput = $(this).closest('tr').find('.city-input');
            $cityInput.val('').removeAttr('data-city-id');
            const $pincodeInput = $(this).closest('tr').find('input[name*="[pincode]"]');
            $pincodeInput.val('');
            const $pincodeIdInput = $(this).closest('tr').find('input[name*="[pincode_master_id]"]');
            $pincodeIdInput.val('');
            return false;
        }
    }).focus(function() {
        $(this).autocomplete("search", "");
    });

    // State Autocomplete
    $row.find('.state-input').autocomplete({
        source: function(request, response) {
            const countryId = $(this.element).closest('tr').find('.country-id').val();
            if (!countryId) {
                response([]);
                return;
            }
            $.get(`/states/${countryId}`, { term: request.term }, function(data) {
                response(data.data.states.map(state => ({
                    label: state.label,
                    value: state.value,
                    id: state.value
                })));
            });
        },
        minLength: 0,
        select: function(event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('tr').find('.state-id').val(ui.item.id);
            const $cityInput = $(this).closest('tr').find('.city-input');
            $cityInput.val('').removeAttr('data-city-id');
            const $pincodeInput = $(this).closest('tr').find('input[name*="[pincode]"]');
            $pincodeInput.val('');
            const $pincodeIdInput = $(this).closest('tr').find('input[name*="[pincode_master_id]"]');
            $pincodeIdInput.val('');
            return false;
        }
    }).focus(function() {
        $(this).autocomplete("search", "");
    });

    $row.find('.city-input').autocomplete({
        source: function(request, response) {
            const stateId = $(this.element).closest('tr').find('.state-id').val();
            if (!stateId) {
                response([]);
                return;
            }
            $.get(`/cities/${stateId}`, { term: request.term }, function(data) {
                response(data.data.cities.map(city => ({
                    label: city.label,
                    value: city.value,
                    id: city.value
                })));
            });
        },
        minLength: 0,
        select: function(event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('tr').find('.city-id').val(ui.item.id);
            return false;
        }
    }).focus(function() {
        $(this).autocomplete("search", "");
    });

    $row.find('input[name*="[pincode]"]').autocomplete({
        source: function(request, response) {
            const stateId = $(this.element).closest('tr').find('.state-id').val();
            if (!stateId) {
                response([]);
                return;
            }
            $.get(`/pincodes/${stateId}`, { term: request.term }, function(data) {
                response(data.data.pincodes.map(pincode => ({
                    label: pincode.label,
                    value: pincode.value,
                    id: pincode.value
                })));
            });
        },
        minLength: 0,
        select: function(event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('tr').find('input[name*="[pincode_master_id]"]').val(ui.item.id);
            return false;
        }
    }).focus(function() {
        $(this).autocomplete("search", "");
    });
}

function handleRadioSelection() {
    $('#address-table-body').on('change', 'input[type="radio"][name*="[is_billing]"]', function() {
        $('#address-table-body input[type="radio"][name*="[is_billing]"]').not(this).prop('checked', false);
        $(this).val('1');
    });

    $('#address-table-body').on('change', 'input[type="radio"][name*="[is_shipping]"]', function() {
        $('#address-table-body input[type="radio"][name*="[is_shipping]"]').not(this).prop('checked', false);
        $(this).val('1');
    });
}
function applyCapsLock() {
    $('input[type="text"], input[type="number"]').each(function() {
        $(this).val($(this).val().toUpperCase());
    });
    $('input[type="text"], input[type="number"]').on('input', function() {
        var value = $(this).val().toUpperCase();
        $(this).val(value);
    });
}
function fetchGstDetailsByGstin(gstinNo) {
    const baseUrl = getBaseUrl();
    const token = $('meta[name="csrf-token"]').attr('content');

    if (!gstinNo) {
        alert("Please enter a GSTIN number.");
        return;
    }
    $('.error-message').remove();

    $.ajax({
        type: 'POST',
        url: baseUrl + '/validate-gst',
        data: { gstNumber: gstinNo },
        headers: {
            'Authorization': 'Bearer ' + token,
        },
        success: function(response) {
            if (response.Status === 1) {
                const gstData = JSON.parse(response.checkGstIn);
                const TradeName = gstData.TradeName || '';
                const Gstin = gstData.Gstin || '';
                const AddrPncd = gstData.AddrPncd || '';
                const StateCode = gstData.StateCode || '';
                const DtReg = gstData.DtReg || '';
                const LegalName = gstData.LegalName || '';
                const AddrSt = gstData.AddrSt || '';
                const AddrLoc = gstData.AddrLoc || '';
                const AddrBnm = gstData.AddrBnm || '';
                const AddrBno = gstData.AddrBno || '';
                const AddrFlno = gstData.AddrFlno || '';
                const fullAddress = [AddrBno != 0 ? AddrBno : '',AddrBnm,AddrFlno,AddrSt,AddrLoc].filter(Boolean).join(', ');
                populateFields({
                    company_name: TradeName,
                    'compliance[gstin_no]': Gstin,
                    'compliance[gstin_registration_date]': DtReg,
                    'compliance[gst_registered_name]': LegalName
                });

                getStateIdByCode(StateCode, function(stateId, stateName) {

                    getCountryIdAndNameByState(stateId, function(countryId, countryName) {

                        getPincodeIdByCode(AddrPncd, stateId, function(pincodeMasterId, pincode) {
                            const $existingRow = findMatchingRow(countryId, stateId, pincodeMasterId, fullAddress);
                            if ($existingRow) {
                                populateAddressRow($existingRow, fullAddress,countryId,countryName, stateId,stateName, pincodeMasterId,pincode, false);
                            } else {
                                const $firstRow = $('#address-table-body .address-row').first();
                                const isFirstRowEmpty = !$firstRow.find('input[name*="[country]"]').val() &&
                                                        !$firstRow.find('input[name*="[state]"]').val() &&
                                                        !$firstRow.find('input[name*="[city]"]').val() &&
                                                        !$firstRow.find('input[name*="[pincode]"]').val() &&
                                                        !$firstRow.find('input[name*="[address]"]').val();

                                const $rowToUpdate = isFirstRowEmpty ? $firstRow : addNewRow();
                                populateAddressRow($rowToUpdate, fullAddress, countryId,countryName, stateId,stateName, pincodeMasterId,pincode, isFirstRowEmpty);
                            }
                            $('#gstinModal').modal('hide');
                        });
                    });
                });
            } else {
                handleErrorResponse(response);
            }
        },
        error: function(xhr, status, error) {
            $('#gstinDetails').html('Error fetching details. Please try again later.').css('color', 'red');
        }
    });
}

function findMatchingRow(countryId, stateId, pincodeMasterId, fullAddress) {
    let matchingRow = null;
    $('#address-table-body .address-row').each(function() {
        const $row = $(this);
        const rowCountryId = $row.find('input[name*="[country_id]"]').val();
        const rowStateId = $row.find('input[name*="[state_id]"]').val();
        const rowPincodeMasterId = $row.find('input[name*="[pincode_master_id]"]').val();
        const rowAddress = $row.find('input[name*="[address]"]').val();
        if (rowCountryId == countryId && rowStateId == stateId && rowPincodeMasterId == pincodeMasterId && rowAddress === fullAddress||rowCountryId == countryId && rowStateId == stateId ) {
            matchingRow = $row;
            return false;
        }
    });

    return matchingRow;
}
function populateFields(fields) {
    for (const [key, value] of Object.entries(fields)) {
        const $input = $(`input[name="${key}"]`);
        if ($input.length) {
            $input.val(value);
            if (key === 'company_name') {
                $input.trigger('input');
            }
        } else {
            console.error(`Input field with name="${key}" not found.`);
        }
    }
}
function populateAddressRow($row, formattedAddress,countryId,countryName, stateId,state, pincodeMasterId,pincode, setBillingShipping = false) {
    $row.find('input[name*="[address]"]').val(formattedAddress);
    if (setBillingShipping) {
        $row.find('input[name*="[is_billing]"]').prop('checked', true).val(1);
        $row.find('input[name*="[is_shipping]"]').prop('checked', true).val(1);
    }
    $row.find('input[name*="[state_id]"]').val(stateId);
    $row.find('input[name*="[state]"]').val(state);
    $row.find('input[name*="[state]"]').attr('data-gst-state-id', stateId).attr('data-gst-state', state);
    $row.find('input[name*="[pincode_master_id]"]').val(pincodeMasterId);
    $row.find('input[name*="[pincode]"]').val(pincode);
    $row.find('input[name*="[country_id]"]').val(countryId);
    $row.find('input[name*="[country]"]').val(countryName);
    $row.find('input[name*="[country]"]').attr('data-gst-country-id', countryId).attr('data-gst-country', countryName);
}

function addNewRow() {
    const $lastRow = $('#address-table-body .address-row').last();
    const index = $lastRow.data('index') + 1;
    const $newRow = $lastRow.clone().attr('data-index', index);
    $newRow.find('input').val('');
    $newRow.find('input[type="radio"]').prop('checked', false);

    $('#address-table-body').append($newRow);
    initializeGstAddressAutocomplete($newRow);
    updateRowIndexes();
    handleRadioSelection();
    applyCapsLock();
    return $newRow;
}

function handleErrorResponse(response) {
    const errorMessage = response.checkGstIn?.ErrorDetails?.[0]?.ErrorCode === "3001"
        ? 'Invalid GST Number'
        : response.errorMsg || "Unable to fetch details.";
    $('#gstinDetails').html(errorMessage).css('color', 'red');
}
// Get State ID by Code
function getStateIdByCode(stateCode, callback,rowIndex) {
    const baseUrl = getBaseUrl();
    const token = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        type: 'GET',
        url: baseUrl + '/get-state-id-by-code/' + stateCode,
        headers: {
            'Authorization': 'Bearer ' + token,
        },
        success: function(response) {
            if (response.state_id) {
                callback(response.state_id, response.state_name);
            } else {
                callback(null, null, response.message);
            }
        },
        error: function(xhr, status, error) {
            const errorMessage = xhr.responseJSON?.message || 'Error fetching state details.';
            callback(null, null, errorMessage);
        }
    });
}

function getCountryIdAndNameByState(stateId, callback,rowIndex) {
    const baseUrl = getBaseUrl();
    const token = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        type: 'GET',
        url: baseUrl + '/get-country-id-by-state/' + stateId,
        headers: {
            'Authorization': 'Bearer ' + token,
        },
        success: function(response) {
            if (response.country_id) {
                callback(response.country_id, response.country_name);
            } else {
                callback(null, null, response.message);
            }
        },
        error: function(xhr, status, error) {
            const errorMessage = xhr.responseJSON?.message || 'Error fetching country details.';
            callback(null, null, errorMessage);
        }
    });
}

function getPincodeIdByCode(pincode, stateId, callback,rowIndex) {
    const baseUrl = getBaseUrl();
    const token = $('meta[name="csrf-token"]').attr('content');

    $.ajax({
        type: 'GET',
        url: baseUrl + '/get-pincode-id-by-code/' + stateId + '/' + pincode,
        headers: {
            'Authorization': 'Bearer ' + token,
        },
        success: function(response) {
            if (response.pincode_id) {
                callback(response.pincode_id, response.pincode);
            } else {
                callback(null, null, response.message);
            }
        },
        error: function(xhr, status, error) {
            const errorMessage = xhr.responseJSON?.message || 'Error fetching pincode details.';
            callback(null, null, errorMessage);
        }
    });
}
//gsttin details
$(document).on('click', '.delete-btn', function (e) {
    e.preventDefault();
    let $this = $(this);
    let url = $this.data('url');
    let message = $this.data('message') || 'Are you sure you want to delete this category?';
    let redirectUrl = $this.data('redirect') || window.location.pathname;

    Swal.fire({
        title: 'Alert!',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: () => $('#loaderDiv').show(),
                success: (res) => {
                    $('#loaderDiv').hide();
                    Swal.fire({
                        title: 'Success!',
                        text: res.message,
                        icon: 'success'
                    });

                    $this.closest('tr').fadeOut(500, function () {
                        $(this).remove();
                    });

                    setTimeout(() => {
                        if (redirectUrl) {
                            window.location.replace(redirectUrl);
                        } else {
                            location.reload();
                        }
                    }, 1500);
                },
                error: (error) => {
                    $('#loaderDiv').hide();
                    let res = error.responseJSON || {};
                    Swal.fire({
                        title: 'Error!',
                        text: res.message || 'An unexpected error occurred.',
                        icon: 'error'
                    });
                }
            });
        }
    });
});

$(document).ready(function () {
    function updateFileIcons() {
        $('.file-link').each(function () {
            var fileUrl = $(this).attr('href');
            var fileExtension = fileUrl.split('.').pop().toLowerCase();
            var $iconElement = $(this).find('.file-icon');
            $iconElement.removeClass('fa-file-pdf fa-file-word fa-file-excel fa-file-powerpoint fa-file-image fa-file-alt fa-file');
            switch (fileExtension) {
                case 'pdf':
                    $iconElement.addClass('fa-file-pdf');
                    break;
                case 'doc':
                case 'docx':
                    $iconElement.addClass('fa-file-word');
                    break;
                case 'xls':
                case 'xlsx':
                    $iconElement.addClass('fa-file-excel');
                    break;
                case 'ppt':
                case 'pptx':
                    $iconElement.addClass('fa-file-powerpoint');
                    break;
                case 'jpg':
                case 'jpeg':
                case 'png':
                    $iconElement.addClass('fa-file-image');
                    break;
                case 'txt':
                    $iconElement.addClass('fa-file-alt');
                    break;
                default:
                    $iconElement.addClass('fa-file');
                    break;
            }
        });
    }

    // Update file icons on page load
    updateFileIcons();

    // Handle file input change event
    $('#document-upload').on('change', function () {
        updateFileIcons();
    });
});

$('#save-draft-button').click(function() {
    $('#document_status').val('draft');
});
