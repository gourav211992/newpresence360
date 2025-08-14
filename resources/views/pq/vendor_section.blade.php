<div class="row">
    <div class="col-md-12">
        <div class="card quation-card">
            <div class="card-header newheader">
                <div>
                    <h4 class="card-title">Vendor Details</h4> 
                </div>
            </div>
            <div class="card-body"> 
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Vendor <span class="text-danger">*</span></label> 
                            <input type="text" id = "vendor_code_input" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input except_draft disable_on_edit" autocomplete="off" value = "{{isset($order) ? $order -> suppliers -> company_name : ''}}" onblur = "onChangeVendor('vendor_code_input', true);">
                            <input type = "hidden" name = "vendor_id" id = "vendor_id_input" value = "{{isset($order) ? $order -> vendor_id : ''}}"></input>
                            <input type = "hidden" name = "vendor_code" id = "vendor_code_input_hidden" value = "{{isset($order) ? $order -> suppliers -> company_name : ''}}"></input>
                            <input type = "hidden" name = "country_id" id = "country_id_input_hidden" value = "{{isset($order) ? $order -> suppliers -> latestBillingAddress() -> country_id : ''}}"></input>
                            <input type = "hidden" name = "state_id" id = "state_id_input_hidden" value = "{{isset($order) ? $order -> suppliers -> latestBillingAddress() -> state_id : ''}}"></input>
                        </div>
                    </div>
                    <div class="col-md-3 d-none">
                        <div class="mb-1">
                            <label class="form-label">Consignee Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control ledgerselecct ui-autocomplete-input" autocomplete="off"  id = "consignee_name_input" name = "consignee_name" value = "{{isset($order) ? $order -> consignee_name : ''}}" /> 
                        </div>
                    </div> 
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                                <select class="form-select disable_on_edit" id = "currency_dropdown" name = "currency_id" readonly>
                                @if (isset($order) && isset($order -> currency_id))
                                    <option value = "{{$order -> currency_id}}">{{$order -> currency_code}}</option>
                                @else
                                    <option value = "">Select</option> 
                                @endif
                            </select> 
                        </div>
                        <input type = "hidden" name = "currency_code" value = "{{isset($order) ? $order -> currency_code : ''}}" id = "currency_code_input"></input>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                            <select class="form-select disable_on_edit" id = "payment_terms_dropdown" name = "payment_terms_id" readonly>
                                @if (isset($order) && isset($order -> payment_terms_id))
                                    <option value = "{{$order -> payment_terms_id}}">{{$order ?-> payment_terms_code}}</option>
                                @else
                                    <option value = "">Select</option> 
                                @endif
                            </select>  
                        </div>
                        <input type = "hidden" name = "payment_terms_code" value = "{{isset($order) ? $order -> payment_terms_code : ''}}" id = "payment_terms_code_input"></input>
                    </div>

                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">GSTIN No.</label>
                            <input type="text" class="form-control ledgerselecct ui-autocomplete-input" autocomplete="off"  id = "vendor_gstin_input" name = "vendor_gstin" value = "{{isset($order) ? $order -> vendor_gstin : ''}}" /> 
                        </div>
                    </div> 
  
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Phone No.<span class="text-danger">*</span></label>
                            <input type="text" class="form-control ledgerselecct ui-autocomplete-input" autocomplete="off" id = "phone_no_input" name = "vendor_phone_no" value = "{{isset($order) ? $order -> vendor_phone : ''}}" /> 
                        </div>
                    </div> 
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Email<span class="text-danger">*</span></label>
                            <input type="text" class="form-control ledgerselecct ui-autocomplete-input" autocomplete="off"  id = "email_input" name = "vendor_email" value = "{{isset($order) ? $order -> vendor_email : ''}}" /> 
                        </div>
                    </div> 
                    
                </div>
                <div class="row"> 
                    <div class="col-md-6">
                        <div class="vendor-billing-section h-100">
                            <p>Vendor Address&nbsp;<span class="text-danger">*</span>
                                <a href="javascript:;" id="billAddressEditBtn" class="float-end"><i data-feather='edit-3'></i></a>
                            </p>
                            <div class="bilnbody">  
                                <div class="genertedvariables genertedvariablesnone">
                                    <div class="mrnaddedd-prim" id = "current_billing_address">{{isset($order) ? $order -> suppliers -> latestBillingAddress() ?-> display_address : ''}}</div>
                                    <input type = "hidden" id = "current_billing_address_id"></input>
                                    <input type = "hidden" id = "current_billing_country_id"></input>
                                    <input type = "hidden" id = "current_billing_state_id"></input>
                                    <input type="hidden" name="new_billing_country_id" id="new_billing_country_id" value="">
                                    <input type="hidden" name="new_billing_state_id" id="new_billing_state_id" value="">
                                    <input type="hidden" name="new_billing_city_id" id="new_billing_city_id" value="">
                                    <input type="hidden" name="new_billing_address" id="new_billing_address" value="">
                                    <input type="hidden" name="new_billing_type" id="new_billing_type" value="">
                                    <input type="hidden" name="new_billing_pincode" id="new_billing_pincode" value="">
                                    <input type="hidden" name="new_billing_phone" id="new_billing_phone" value="">
                                </div>
                            </div>
                        </div>
                    </div> 
                    <div class="col-md-6">
                        <div class="vendor-billing-section">
                            <p>Billing Address&nbsp;<span class="text-danger">*</span>
                            </p>
                        <div class="bilnbody"> 
                            <div class="genertedvariables genertedvariablesnone">
                                <div class="mrnaddedd-prim" id = "current_pickup_address">{{isset($order) ? $order -> location_address_details ?-> display_address : ''}}</div>
                            </div> 
                        </div>
                    </div>
                </div>                                                                                                
            </div>
        </div>
    </div> 
    <div class="col-md-12 {{(isset($order) && count($order -> dynamic_fields)) > 0 ? '' : 'd-none'}}" id = "dynamic_fields_section">
        @if (isset($dynamicFieldsUi))
            {!! $dynamicFieldsUi !!}
        @endif
    </div>
</div>
