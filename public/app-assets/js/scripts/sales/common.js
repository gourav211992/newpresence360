let customerPhoneInput = document.getElementById('customer_phone_no_input');
let customerEmailInput = document.getElementById('customer_email_input');
let customerGstInInput = document.getElementById('customer_gstin_input');
let sameAsBillAddressCheckBoxUIContainer = document.getElementById('same_checkbox_as_billing');
let sameAsBillAddressCheckBoxUI = `Same as Billing
<input type="checkbox" class="form-check-input" id="same_as_bill_adddress_check_box" oninput = "setShippingAddressFromBillingAddress(this);"/>
`;
let editShipButton = document.getElementById('shipAddressEditBtn');
let billAddressText = document.getElementById('current_billing_address');
let shipAddressText = document.getElementById('current_shipping_address');

function enableDisableCustomerFields(disabled = false)
{
    if (disabled) {
        customerPhoneInput.setAttribute('readonly', disabled);
        customerEmailInput.setAttribute('readonly', disabled);
        customerGstInInput.setAttribute('readonly', disabled);
        sameAsBillAddressCheckBoxUIContainer.innerHTML = ``;
    } else {
        customerPhoneInput.removeAttribute('readonly');
        customerPhoneInput.value = ('');
        customerEmailInput.removeAttribute('readonly');
        customerEmailInput.value = ('');
        customerGstInInput.removeAttribute('readonly');
        customerGstInInput.value = ('');
        sameAsBillAddressCheckBoxUIContainer.innerHTML = sameAsBillAddressCheckBoxUI;
        emptyAddress('billing');
        emptyAddress('shipping');
    }
    
}

function initializeCashCustomerPhoneDropdown()
{
    enableDisableCustomerFields(false);
    $("#customer_phone_no_input").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type:'cash_customer_phone_no',
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: `${item.phone_no ? item.phone_no : ''}`,
                            email: `${item.email ? item.email : ''}`,
                            gstin: `${item.gstin ? item.gstin : ''}`,
                            name: `${item.name ? item.name : ''}`
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function(event, ui) {
            var $input = $(this);
            var itemPhone = ui.item.label;
            var itemEmail = ui.item.email;
            var itemGSTIN = ui.item.gstin;
            var itemName = ui.item.name;
            //Set Phone No and other fields
            $input.val(itemPhone);
            $("#customer_email_input").val(itemEmail ? itemEmail : '');
            $("#consignee_name_input").val(itemName ? itemName : '');
            $("#customer_gstin_input").val(itemGSTIN ? itemGSTIN : '');

            initializeCashCustomerEmailDropdown();
            initializeCashCustomerGstInDropdown();
            initializeCashCustomerConsigneeDropdown();

            changeDropdownOptions(document.getElementById('customer_id_input'), ['billing_address_dropdown','shipping_address_dropdown'], ['billing_addresses', 'shipping_addresses'], '/customer/addresses/', 'vendor_dependent', [], [{key : 'phone_no', value : itemPhone}]);

            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val();
                $("#customer_email_input").val('');
                $("#consignee_name_input").val('');
                $("#customer_gstin_input").val('');
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    });
}

function initializeCashCustomerEmailDropdown()
{
    $("#customer_email_input").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type:'cash_customer_email',
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: `${item.email}`,
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function(event, ui) {
            var $input = $(this);
            var itemEmail = ui.item.label;
           
            //Set Phone No and other fields
            $input.val(itemEmail);

            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val();
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    });
}
function initializeCashCustomerGstInDropdown()
{
    $("#customer_gstin_input").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type:'cash_customer_gstin',
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: `${item.gstin}`,
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function(event, ui) {
            var $input = $(this);
            var itemGSTIN = ui.item.label;
           
            //Set Phone No and other fields
            $input.val(itemGSTIN);

            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val();
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    });
}

function initializeCashCustomerConsigneeDropdown()
{
    $("#consignee_name_input").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type:'cash_customer_consignee',
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: `${item.name}`,
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function(event, ui) {
            var $input = $(this);
            var itemName = ui.item.label;
           
            //Set Phone No and other fields
            $input.val(itemName);

            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val();
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    });
}

function deInitializeCashCustomerFlow()
{
    let customerMainInput = document.getElementById('customer_code_input');

    customerPhoneInput.value = (customerMainInput.getAttribute('phone_no'));
    customerEmailInput.value = (customerMainInput.getAttribute('email'));
    customerGstInInput.value = (customerMainInput.getAttribute('gstin'));

    enableDisableCustomerFields(true);

}

async function setShippingAddressFromBillingAddress(element)
{
    let billingCountryIdInput = $("#billing_country_id_input").val();
    let billingStateIdInput = $("#billing_state_id_input").val();
    let billingCityIdInput = $("#billing_city_id_input").val();
    let billingPincodeInput = $("#billing_pincode_input").val();
    let billingAddressInput = $("#billing_address_input").val();

    if (!(billingCountryIdInput && billingStateIdInput && billingCityIdInput && billingPincodeInput) && element.checked) {
        Swal.fire({
            title: 'Error!',
            text: "Please enter Billing Address first",
            icon: 'error',
        });
        element.checked = false;
    }

    let shippingCountryIdInput = $("#new_shipping_country_id");
    let shippingStateIdInput = $("#new_shipping_state_id");
    let shippingCityIdInput = $("#new_shipping_city_id");
    let shippingPincodeInput = $("#new_shipping_pincode");
    let shippingAddressInput = $("#new_shipping_address");
    let currentShippingCountryIdInput = $("#current_shipping_country_id");
    let currentShippingStateId = $("#current_shipping_state_id");

    if (element.checked) {
        shippingCountryIdInput.val(billingCountryIdInput);
        shippingStateIdInput.val(billingStateIdInput);
        shippingCityIdInput.val(billingCityIdInput);
        shippingPincodeInput.val(billingPincodeInput);
        shippingAddressInput.val(billingAddressInput);
        currentShippingCountryIdInput.val(billingCountryIdInput);   
        currentShippingStateId.val(billingStateIdInput);
        shipAddressText.innerText = billAddressText.innerText;
        editShipButton.style.display = "none";
    } else {
        shippingCountryIdInput.val('');
        shippingStateIdInput.val('');
        shippingCityIdInput.val('');
        shippingPincodeInput.val('');
        shippingAddressInput.val('');
        shipAddressText.innerText = '';
        editShipButton.style.display = "";
    }
}

function emptyAddress(type = 'billing') 
{
    if (type === 'billing') {
        billAddressText.innerText = "";
    } else {
        shipAddressText.innerText = "";
    }
    $("#new_" + type + "_country_id").val('');
    $("#new_" + type + "_state_id").val('');
    $("#new_" + type + "_city_id").val('');
    $("#new_" + type + "_pincode").val('');
    $("#new_" + type + "_address").val('');
    $("#current_" + type + "_country_id").val('');
    $("#current_" + type + "_state_id").val('');
    $("#current_" + type + "_address_id").val('');
}