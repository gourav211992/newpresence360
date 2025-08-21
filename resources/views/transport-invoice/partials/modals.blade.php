   <div class="modal fade text-start" id="pullPopUpLr" tabindex="-1" aria-labelledby="header_pull_label" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1250px">
         <div class="modal-content">
            <div class="modal-header">
               <div class="col-md-9">
                  <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="header_pull_label">
                     Select
                     Document
                  </h4>
                  <p class="mb-0">Select from the below list</p>
               </div>
               <div class="text-end col-md-3 text-end">
                  <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                     data-feather="x-circle"></i> Cancel</button>
                  <button type="button" class="ml-1 btn btn-primary btn-sm" onclick="processOrder('lr');"
                     data-bs-dismiss="modal"><i data-feather="check-circle"></i> Process</button>
               </div>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <div class="row">
                  <div class="col">
                     <div class="mb-1">
                        <label class="form-label">Consignor Name <span class="text-danger">*</span></label>
                        <input type="text" id="customer_code_input_lr" placeholder="Select"
                           class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off"
                           value="">
                        <input type="hidden" id="customer_id_lr_val"></input>
                     </div>
                  </div>
                  <div class="col">
                     <div class="mb-1">
                        <label class="form-label">Series <span class="text-danger">*</span></label>
                        <input type="text" id="book_code_input_lr" placeholder="Select"
                           class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off"
                           value="">
                        <input type="hidden" id="book_id_lr_val"></input>
                     </div>
                  </div>
                  <div class="col">
                     <div class="mb-1">
                        <label class="form-label">Document No. <span class="text-danger">*</span></label>
                        <input type="text" id="document_no_input_lr" placeholder="Select"
                           class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off"
                           value="">
                        <input type="hidden" id="document_id_lr_val"></input>
                     </div>
                  </div>
                  <div class="col">
                     <div class="mb-1">
                        <label class="form-label">Source. <span class="text-danger">*</span></label>
                        <input type="text" id="source_input_lr" placeholder="Select"
                           class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off"
                           value="">
                        <input type="hidden" id="source_id_lr_val"></input>
                     </div>
                  </div>
                  <!-- <div class="col">
                     <div class="mb-1">
                         <label class="form-label">Item Name <span class="text-danger">*</span></label>
                         <input type="text" id="item_name_input_lr" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                         <input type = "hidden" id = "item_id_lr_val"></input>
                     </div>
                     </div> -->
                  <div class="col mb-1">
                     <label class="form-label">&nbsp;</label><br />
                     <button onclick="clearFilters('lr');" type="button" class="btn btn-danger btn-sm"><i
                        data-feather="trash"></i> Clear</button>
                  </div>
                  <div class="col-md-12">
                     <div class="table-responsive">
                        <table
                           class="dataTables_scroll datatables-basic table-sm table-bordered table myrequesttablecbox pomrnheadtffotsticky"
                           id="lorry_receipt_table">
                           <input type="hidden" id="lorry_receipt_table_value" value="lr">
                           <thead>
                              <tr>
                                 <th>
                                    <!-- <div class="form-check form-check-inline me-0">
                                       <input class="form-check-input" type="checkbox" id="checkAllSOElement" onchange="checkAllSO(this);">
                                       </div> -->
                                 </th>
                                 <th>Series</th>
                                 <th>Doc No.</th>
                                 <th>Doc Date</th>
                                 <th>Currency</th>
                                 <th>Consignor Name</th>
                                 <th>Source</th>
                                 <th>Destination</th>
                                 <th>Total Charges</th>
                              </tr>
                           </thead>
                           <tbody>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="discount" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
      <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
         <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
               <h1 class="text-center mb-1" id="shareProjectTitle">Discount</h1>
               <div class="row">
                  <div class="col-md-4" style="padding-right:0px">
                     <div class="">
                        <label class="form-label">Type<span class="text-danger">*</span></label>
                        <input type="text" id="new_discount_name" placeholder="Select"
                           class="form-control mw-100 ledgerselecct ui-autocomplete-input"
                           autocomplete="off" value=""
                           onblur="resetDiscountOrExpense(this,'new_discount_percentage')">
                        <input type="hidden" id="new_discount_id" />
                     </div>
                  </div>
                  <div class="col-md-2" style="padding-right:0px">
                     <div class="">
                        <label class="form-label">Percentage <span class="text-danger">*</span></label>
                        <input id="new_discount_percentage" oninput="onChangeDiscountPercentage(this);"
                           type="text" class="form-control mw-100 text-end"
                           placeholder="Discount Percentage" />
                     </div>
                  </div>
                  <div class="col-md-4" style="padding-right:0px">
                     <div class="">
                        <label class="form-label">Value <span class="text-danger">*</span></label>
                        <input id="new_discount_value" type="text" class="form-control mw-100 text-end"
                           oninput="onChangeDiscountValue(this);" placeholder="Discount Value" />
                     </div>
                  </div>
                  <div class="col-md-auto mt-1 d-flex align-items-center justify-content-center"
                     style="padding-right:0px">
                     <div>
                        <a href="#" onclick="addDiscount();" class="text-primary can_hide"><i
                           data-feather="plus-square"></i></a>
                     </div>
                  </div>
               </div>
               <!-- <div class="text-end"><a href="#" class="text-primary add-contactpeontxt mt-50"><i data-feather='plus'></i> Add Discount</a></div> -->
               <div class="table-responsive-md customernewsection-form">
                  <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"
                     id="discount_main_table" total-value="0">
                     <thead>
                        <tr>
                           <th>S.No.</th>
                           <th width="150px">Discount Name</th>
                           <th>Discount %</th>
                           <th>Discount Value</th>
                           <th>Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr>
                        </tr>
                        <tr>
                           <td colspan="2"></td>
                           <td class="text-dark"><strong>Total</strong></td>
                           <td class="text-dark" id="total_item_discount"><strong>0.00</strong></td>
                           <td></td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
            <div class="modal-footer justify-content-center">
               <button type="button" class="btn btn-outline-secondary me-1"
                  onclick="closeModal('discount');">Cancel</button>
               <button type="button" class="btn btn-primary"
                  onclick="closeModal('discount');">Submit</button>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="tax" tabindex="-1" aria-labelledby="shareProjectTitle"
      aria-hidden="true">
      <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
         <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
               <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
               <h1 class="text-center mb-1" id="shareProjectTitle">Taxes</h1>
               <!-- <div class="text-end"><a href="#" class="text-primary add-contactpeontxt mt-50"><i data-feather='plus'></i> Add Discount</a></div> -->
               <div class="table-responsive-md customernewsection-form">
                  <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"
                     id="tax_main_table">
                     <thead>
                        <tr>
                           <th>S.No.</th>
                           <th width="150px">Tax Name</th>
                           <th>Tax %</th>
                           <th>Tax Value</th>
                        </tr>
                     </thead>
                     <tbody>
                     </tbody>
                  </table>
               </div>
            </div>
            <div class="modal-footer justify-content-center">
               <button type="button" class="btn btn-outline-secondary me-1"
                  onclick="closeModal('tax');">Cancel</button>
               <button type="button" class="btn btn-primary" onclick="closeModal('tax');">Submit</button>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="discountOrder" tabindex="-1" aria-labelledby="shareProjectTitle"
      aria-hidden="true">
      <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
         <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
               <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
               <h1 class="text-center mb-1" id="shareProjectTitle">Discount</h1>
               <div class="row">
                  <div class="col-md-4" style="padding-right:0px">
                     <div class="">
                        <label class="form-label">Type<span class="text-danger">*</span></label>
                        <input type="text" id="new_order_discount_name" placeholder="Select"
                           class="form-control mw-100 ledgerselecct ui-autocomplete-input"
                           autocomplete="off" value=""
                           onblur="resetDiscountOrExpense(this, 'new_order_discount_percentage')">
                        <input type="hidden" id="new_order_discount_id" />
                     </div>
                  </div>
                  <div class="col-md-2" style="padding-right:0px">
                     <div class="">
                        <label class="form-label">Percentage <span class="text-danger">*</span></label>
                        <input id="new_order_discount_percentage"
                           oninput="onChangeOrderDiscountPercentage(this);" type="text"
                           class="form-control mw-100 text-end" />
                     </div>
                  </div>
                  <div class="col-md-4" style="padding-right:0px">
                     <div class="">
                        <label class="form-label">Value <span class="text-danger">*</span></label>
                        <input id="new_order_discount_value" type="text"
                           class="form-control mw-100 text-end"
                           oninput="onChangeOrderDiscountValue(this);" />
                     </div>
                  </div>
                  <div class="col-md-auto mt-1 d-flex align-items-center justify-content-center"
                     style="padding-right:0px">
                     <div>
                        <a href="#" onclick="addOrderDiscount();" class="text-primary can_hide"><i
                           data-feather="plus-square"></i></a>
                     </div>
                  </div>
               </div>
               <div class="table-responsive-md customernewsection-form">
                  <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"
                     id="order_discount_main_table">
                     <thead>
                        <tr>
                           <th>S.No.</th>
                           <th width="150px">Discount Name</th>
                           <th>Discount %</th>
                           <th>Discount Value</th>
                           <th>Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr>
                        </tr>
                        <tr>
                           <td colspan="2"></td>
                           <td class="text-dark"><strong>Total</strong></td>
                           <td class="text-dark" id="total_order_discount"><strong>0.00</strong></td>
                           <td></td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
            <div class="modal-footer justify-content-center">
               <button type="button" class="btn btn-outline-secondary me-1">Cancel</button>
               <button type="button" class="btn btn-primary"
                  onclick="closeModal('discountOrder');">Submit</button>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="orderTaxes" tabindex="-1" aria-labelledby="shareProjectTitle"
      aria-hidden="true">
      <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
         <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
               <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
               <h1 class="text-center mb-1" id="shareProjectTitle">Taxes</h1>
               <div class="table-responsive-md customernewsection-form">
                  <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"
                     id="order_tax_main_table">
                     <thead>
                        <tr>
                           <th>S.No.</th>
                           <th width="150px">Tax</th>
                           <th>Taxable Amount</th>
                           <th>Tax %</th>
                           <th>Tax Value</th>
                        </tr>
                     </thead>
                     <tbody id="order_tax_details_table">
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="edit-address-shipping" tabindex="-1" aria-labelledby="shareProjectTitle"
      aria-hidden="true">
      <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
         <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
               <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
               <h1 class="text-center mb-1" id="shareProjectTitle">Edit Address</h1>
               <p class="text-center">Enter the details below.</p>
               <div class="row mt-2">
                  <div class="col-md-12 mb-1">
                     <select class="select2 form-select vendor_dependent" id="shipping_address_dropdown"
                        name="shipping_address" oninput="onShippingAddressChange(this);">
                          <option value="">Select</option>
                     </select>
                  </div>
                  <div class="col-md-6 mb-1">
                     <label class="form-label">Country <span class="text-danger">*</span></label>
                     <select class="select2 form-select" id="shipping_country_id_input"
                        onchange="changeDropdownOptions(this, ['shipping_state_id_input'], ['states'], '/states/', null, ['shipping_city_id_input'])">
                        @foreach ($countries as $country)
                        <option value="{{ $country->value }}">{{ $country->label }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-6 mb-1">
                     <label class="form-label">State <span class="text-danger">*</span></label>
                     <select class="select2 form-select" id="shipping_state_id_input"
                        onchange="changeDropdownOptions(this, ['shipping_city_id_input'], ['cities'], '/cities/', null, [])">
                     </select>
                  </div>
                  <div class="col-md-6 mb-1">
                     <label class="form-label">City <span class="text-danger">*</span></label>
                     <select class="select2 form-select" name="shipping_city_id"
                        id="shipping_city_id_input">
                     </select>
                  </div>
                  <div class="col-md-6 mb-1">
                     <label class="form-label w-100">Pincode <span class="text-danger">*</span></label>
                     <input type="text" class="form-control" value=""
                        placeholder="Enter Pincode" name="shipping_pincode" id="shipping_pincode_input" />
                  </div>
                  <div class="col-md-12 mb-1">
                     <label class="form-label">Address <span class="text-danger">*</span></label>
                     <textarea class="form-control" placeholder="Enter Address" name="shipping_address_text"
                        id="shipping_address_input"></textarea>
                  </div>
               </div>
            </div>
            <div class="modal-footer justify-content-center">
               <button type="button" class="btn btn-outline-secondary me-1">Cancel</button>
               <button type="button" onclick="saveAddressShipping();"
                  class="btn btn-primary">Submit</button>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="edit-address-billing" tabindex="-1" aria-labelledby="shareProjectTitle"
      aria-hidden="true">
      <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
         <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
               <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
               <h1 class="text-center mb-1" id="shareProjectTitle">Edit Address</h1>
               <p class="text-center">Enter the details below.</p>
               <div class="row mt-2">
                  <div class="col-md-12 mb-1">
                     <select class="select2 form-select vendor_dependent" id="billing_address_dropdown"
                        name="billing_address" oninput="onBillingAddressChange(this);">
                        <option value="">Select</option>
                     </select>
                  </div>
                  <div class="col-md-6 mb-1">
                     <label class="form-label">Country <span class="text-danger">*</span></label>
                     <select class="select2 form-select" name="billing_country_id"
                        id="billing_country_id_input"
                        onchange="changeDropdownOptions(this, ['billing_state_id_input'], ['states'], '/states/', null, ['billing_city_id_input'])">
                        @foreach ($countries as $country)
                        <option value="{{ $country->value }}">{{ $country->label }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-6 mb-1">
                     <label class="form-label">State <span class="text-danger">*</span></label>
                     <select class="select2 form-select" name="billing_state_id"
                        id="billing_state_id_input"
                        onchange="changeDropdownOptions(this, ['billing_city_id_input'], ['cities'], '/cities/', null, [])">
                     </select>
                  </div>
                  <div class="col-md-6 mb-1">
                     <label class="form-label">City <span class="text-danger">*</span></label>
                     <select class="select2 form-select" name="billing_city_id" id="billing_city_id_input">
                     </select>
                  </div>
                  <div class="col-md-6 mb-1">
                     <label class="form-label w-100">Pincode <span class="text-danger">*</span></label>
                     <input type="text" class="form-control" value=""
                        placeholder="Enter Pincode" name="billing_pincode" id="billing_pincode_input" />
                  </div>
                  <div class="col-md-12 mb-1">
                     <label class="form-label">Address <span class="text-danger">*</span></label>
                     <textarea class="form-control" placeholder="Enter Address" name="billing_address_text"
                        id="billing_address_input"></textarea>
                  </div>
               </div>
            </div>
            <div class="modal-footer justify-content-center">
               <button type="button" class="btn btn-outline-secondary me-1">Cancel</button>
               <button type="button" onclick="saveAddressBilling();" class="btn btn-primary">Submit</button>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="Remarks" tabindex="-1" aria-labelledby="shareProjectTitle"
      aria-hidden="true">
      <div class="modal-dialog  modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
               <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
               <h1 class="text-center mb-1" id="shareProjectTitle">Add/Edit Remarks</h1>
               <p class="text-center">Enter the details below.</p>
               <div class="row mt-2">
                  <div class="col-md-12 mb-1">
                     <label class="form-label">Remarks</label>
                     <textarea class="form-control" current-item="item_remarks_0" onchange="changeItemRemarks(this);"
                        id="current_item_remarks_input" placeholder="Enter Remarks"></textarea>
                  </div>
               </div>
            </div>
            <div class="modal-footer justify-content-center">
               <button type="button" class="btn btn-outline-secondary me-1"
                  onclick="closeModal('Remarks');">Cancel</button>
               <button type="button" class="btn btn-primary"
                  onclick="closeModal('Remarks');">Submit</button>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="expenses" tabindex="-1" aria-labelledby="shareProjectTitle"
      aria-hidden="true">
      <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
         <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
               <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
               <h1 class="text-center mb-1" id="shareProjectTitle">Expenses</h1>
               <div class="row">
                  <div class="col-md-4" style="padding-right:0px">
                     <div class="">
                        <label class="form-label">Type<span class="text-danger">*</span></label>
                        <input type="text" id="order_expense_name" placeholder="Select"
                           class="form-control mw-100 ledgerselecct ui-autocomplete-input"
                           autocomplete="off" value=""
                           onblur="resetDiscountOrExpense(this, 'order_expense_percentage')">
                        <input type="hidden" id="order_expense_id" />
                     </div>
                  </div>
                  <div class="col-md-2" style="padding-right:0px">
                     <div class="">
                        <label class="form-label">Percentage <span class="text-danger">*</span></label>
                        <input type="text" id="order_expense_percentage"
                           oninput="onChangeOrderExpensePercentage(this);"
                           class="form-control mw-100 text-end" />
                     </div>
                  </div>
                  <div class="col-md-4" style="padding-right:0px">
                     <div class="">
                        <label class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="text" id="order_expense_value"
                           oninput="onChangeOrderExpenseValue(this);"
                           class="form-control mw-100 text-end" />
                     </div>
                  </div>
                  <div class="col-md-auto mt-1 d-flex align-items-center justify-content-center"
                     style="padding-right:0px">
                     <div>
                        <a href="#" onclick="addOrderExpense();" class="text-primary can_hide"><i
                           data-feather="plus-square"></i></a>
                     </div>
                  </div>
               </div>
               <div class="table-responsive-md customernewsection-form">
                  <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"
                     id="order_expense_main_table">
                     <thead>
                        <tr>
                           <th>S.No.</th>
                           <th width="150px">Expense Name</th>
                           <th>Expense %</th>
                           <th>Expense Value</th>
                           <th>Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr>
                        </tr>
                        <tr>
                           <td colspan="2"></td>
                           <td class="text-dark"><strong>Total</strong></td>
                           <td class="text-dark" id="total_order_expense"><strong>00.00</strong></td>
                           <td></td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
            <div class="modal-footer justify-content-center">
               <button type="button" class="btn btn-outline-secondary me-1"
                  onclick="closeModal('expenses');">Cancel</button>
               <button type="button" class="btn btn-primary"
                  onclick="closeModal('expenses');">Submit</button>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="location" tabindex="-1" aria-labelledby="shareProjectTitle"
      aria-hidden="true">
      <div class="modal-dialog  modal-dialog-centered" style="max-width: 900px">
         <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
               <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
               <h1 class="text-center mb-1" id="shareProjectTitle">Store Location</h1>
               <p class="text-center">Enter the details below.</p>
               <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"
                  style="display:none;">
                  <tbody>
                     <tr>
                        <td></td>
                        <td>
                           <input type="text" id="new_store_code_input" placeholder="Select Store"
                              class="form-control mw-100 ledgerselecct ui-autocomplete-input"
                              autocomplete="off">
                           <input type="hidden" id="new_store_id_input"></input>
                        </td>
                        <td>
                           <input type="text" id="new_rack_code_input" placeholder="Select Rack"
                              class="form-control mw-100 ledgerselecct ui-autocomplete-input"
                              autocomplete="off">
                           <input type="hidden" id="new_rack_id_input"></input>
                        </td>
                        <td>
                           <input type="text" id="new_shelf_code_input" placeholder="Select Shelf"
                              class="form-control mw-100 ledgerselecct ui-autocomplete-input"
                              autocomplete="off">
                           <input type="hidden" id="new_shelf_id_input"></input>
                        </td>
                        <td>
                           <input type="text" id="new_bin_code_input" placeholder="Select Bin"
                              class="form-control mw-100 ledgerselecct ui-autocomplete-input"
                              autocomplete="off">
                           <input type="hidden" id="new_bin_id_input"></input>
                        </td>
                        <td><input type="text" id="new_location_qty" class="form-control mw-100" />
                        </td>
                        <td>
                           <a href="#" class="text-primary" onclick="addItemStore();"><i
                              data-feather="plus-square"></i></a>
                        </td>
                     </tr>
                  </tbody>
               </table>
               <div class="table-responsive-md customernewsection-form">
                  <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                     <thead>
                        <tr>
                           <th width="80px">S.No</th>
                           <th>Rack</th>
                           <th>Shelf</th>
                           <th>Bin</th>
                           <th width="50px">Qty</th>
                        </tr>
                     </thead>
                     <tbody id="item_location_table" current-item-index='0'>
                     </tbody>
                  </table>
               </div>
            </div>
            <div class="modal-footer justify-content-center">
               <button type="button" class="btn btn-outline-secondary me-1"
                  onclick="closeModal('location');">Cancel</button>
               <button type="button" class="btn btn-primary"
                  onclick="closeModal('location');">Submit</button>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="delivery" tabindex="-1" aria-labelledby="shareProjectTitle"
      aria-hidden="true">
      <div class="modal-dialog  modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
               <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
               <h1 class="text-center mb-1" id="shareProjectTitle">Delivery Schedule</h1>
               <p class="text-center">Enter the details below.</p>
               <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                  <thead>
                     <tr>
                        <td>#</td>
                        <td><input type="text" id="new_item_delivery_qty_input"
                           class="form-control mw-100" />
                        </td>
                        <td><input type="date" id="new_item_delivery_date_input"
                           value="{{ Carbon\Carbon::now()->format('Y-m-d') }}"
                           class="form-control mw-100" /></td>
                        <td>
                           <a href="#" onclick="addDeliveryScheduleRow();" class="text-primary"><i
                              data-feather="plus-square"></i></a>
                        </td>
                     </tr>
                  </thead>
                  <tbody>
                  </tbody>
               </table>
               <div class="table-responsive-md customernewsection-form">
                  <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"
                     id="delivery_schedule_main_table">
                     <thead>
                        <tr>
                           <th>S.No.</th>
                           <th width="150px">Quantity</th>
                           <th>Date</th>
                           <th>Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr>
                        </tr>
                        <tr>
                           <td class="text-dark"><strong>Total</strong></td>
                           <td class="text-dark"><strong id="item_delivery_qty"></strong></td>
                           <td></td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
            <div class="modal-footer justify-content-center">
               <button type="button" class="btn btn-outline-secondary me-1"
                  onclick="closeModal('delivery');">Cancel</button>
               <button type="button" class="btn btn-primary"
                  onclick="closeModal('delivery');">Submit</button>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="attribute" tabindex="-1" aria-labelledby="shareProjectTitle"
      aria-hidden="true">
      <div class="modal-dialog  modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
               <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
               <h1 class="text-center mb-1" id="shareProjectTitle">Select Attribute</h1>
               <p class="text-center">Enter the details below.</p>
               <div class="table-responsive-md customernewsection-form">
                  <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"
                     id="attributes_table_modal" item-index="">
                     <thead>
                        <tr>
                           <th>Attribute Name</th>
                           <th>Attribute Value</th>
                        </tr>
                     </thead>
                     <tbody id="attribute_table">
                     </tbody>
                  </table>
               </div>
            </div>
            <div class="modal-footer justify-content-center">
               <button type="button" class="btn btn-outline-secondary me-1"
                  onclick="closeModal('attribute');">Cancel</button>
               <button type="button" class="btn btn-primary"
                  onclick="submitAttr('attribute');">Select</button>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="amendConfirmPopup" tabindex="-1" aria-labelledby="shareProjectTitle"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
               <div>
                  <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Amend
                     Invoice
                  </h4>
               </div>
               <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
               <input type="hidden" name="action_type" id="action_type_main">
            </div>
            <div class="modal-body pb-2">
               <div class="row mt-1">
                  <div class="col-md-12">
                     <div class="mb-1">
                        <label class="form-label">Remarks</label>
                        <textarea name="amend_remarks" class="form-control cannot_disable"></textarea>
                     </div>
                     <div class="row">
                        <div class="col-md-8">
                           <div class="mb-1">
                              <label class="form-label">Upload Document</label>
                              <input name="amend_attachments[]"
                                 onchange="addFiles(this, 'amend_files_preview')" type="file"
                                 class="form-control cannot_disable" max_file_count="2" multiple />
                           </div>
                        </div>
                        <div class="col-md-4" style="margin-top:19px;">
                           <div class="row" id="amend_files_preview">
                           </div>
                        </div>
                     </div>
                     <span class="text-primary small">{{ __('message.attachment_caption') }}</span>
                  </div>
               </div>
            </div>
            <div class="modal-footer justify-content-center">
               <button type="button" class="btn btn-outline-secondary me-1"
                  onclick="closeModal('amendConfirmPopup');">Cancel</button>
               <button type="button" class="btn btn-primary" onclick="submitAmend();">Submit</button>
            </div>
         </div>
      </div>
   </div>
</form>
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle"
   aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <form class="ajax-submit-2" method="POST" action="{{ route('document.approval.transportInvoice') }}"
            data-redirect="{{ $redirect_url }}" enctype='multipart/form-data'>
            @csrf
            <input type="hidden" name="action_type" id="action_type">
            <input type="hidden" name="id" value="{{ isset($order) ? $order->id : '' }}">
            <div class="modal-header">
               <div>
                  <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal"
                     id="approve_reject_heading_label">
                  </h4>
               </div>
               <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body pb-2">
               <div class="row mt-1">
                  <div class="col-md-12">
                     <div class="mb-1">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control cannot_disable"></textarea>
                     </div>
                     <div class="row">
                        <div class="col-md-8">
                           <div class="mb-1">
                              <label class="form-label">Upload Document</label>
                              <input type="file" name="attachments[]" multiple
                                 class="form-control cannot_disable"
                                 onchange="addFiles(this, 'approval_files_preview');"
                                 max_file_count="2" />
                           </div>
                        </div>
                        <div class="col-md-4" style="margin-top:19px;">
                           <div class="row" id="approval_files_preview">
                           </div>
                        </div>
                     </div>
                     <span class="text-primary small">{{ __('message.attachment_caption') }}</span>
                  </div>
               </div>
            </div>
            <div class="modal-footer justify-content-center">
               <button type="reset" class="btn btn-outline-secondary me-1"
                  onclick="closeModal('approveModal');">Cancel</button>
               <button type="submit" class="btn btn-primary">Submit</button>
            </div>
         </form>
      </div>
   </div>
</div>
<div class="modal fade" id="podModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <form class="ajax-submit-2" method="POST" action="{{ route('sale.invoice.pod') }}"
            data-redirect="{{ $redirect_url }}" enctype='multipart/form-data'>
            @csrf
            <input type="hidden" name="action_type" id="action_type">
            <input type="hidden" name="id" value="{{ isset($order) ? $order->id : '' }}">
            <div class="modal-header">
               <div>
                  <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="pod_heading_label">
                  </h4>
               </div>
               <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body pb-2">
               <div class="row mt-1">
                  <div class="col-md-12">
                     <div class="mb-1">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control cannot_disable"></textarea>
                     </div>
                     <div class="row">
                        <div class="col-md-8">
                           <div class="mb-1">
                              <label class="form-label">Upload Document</label>
                              <input type="file" name="attachments[]" multiple
                                 class="form-control cannot_disable"
                                 onchange="addFiles(this, 'approval_files_preview');"
                                 max_file_count="2" />
                           </div>
                        </div>
                        <div class="col-md-4" style="margin-top:19px;">
                           <div class="row" id="approval_files_preview">
                           </div>
                        </div>
                     </div>
                     <span class="text-primary small">{{ __('message.attachment_caption') }}</span>
                  </div>
               </div>
            </div>
            <div class="modal-footer justify-content-center">
               <button type="reset" class="btn btn-outline-secondary me-1"
                  onclick="closeModal('podModal');">Cancel</button>
               <button type="submit" class="btn btn-primary">Submit</button>
            </div>
         </form>
      </div>
   </div>
</div>
<div class="modal fade" id="sendMail" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <form class="ajax-submit-2" method="POST" action="{{ route('tranport.invoice.eInvoiceMail') }}"
            data-redirect="{{ $redirect_url }}" enctype='multipart/form-data'>
            @csrf
            <input type="hidden" name="action_type" id="action_type">
            <input type="hidden" name="id" value="{{ isset($order) ? $order->id : '' }}">
            <div class="modal-header">
               <div>
                  <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal"
                     id="send_mail_heading_label">
                  </h4>
               </div>
               <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
            </div>
            <div class="modal-body pb-2">
               <div class="row mt-1">
                  {{-- 
                  <div class="col-md-12">
                     <div class="mb-1">
                        <label class="form-label">Email From</label>
                        <input type="text" id='cust_mail' name="email_from" class="form-control cannot_disable">
                     </div>
                  </div>
                  --}}
                  <div class="col-md-12">
                     <div class="mb-1">
                        <label class="form-label">Email To</label>
                        <input type="text" id='cust_mail' name="email_to"
                           class="form-control cannot_disable">
                     </div>
                  </div>
                  <div class="col-md-12">
                     <div class="mb-1">
                        <label class="form-label">CC To</label>
                        <select name="cc_to[]" class="select2 form-control cannot_disable" multiple>
                        @foreach ($users as $index => $user)
                        <option value="{{ $user->email }}"
                        {{ isset($order) && $user->id == $order->created_by ? 'selected' : '' }}>
                        {{ $user->name }}
                        </option>
                        @endforeach
                        </select>
                     </div>
                  </div>
                  <div class="col-md-12">
                     <div class="mb-1">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" id="mail_remarks" class="form-control cannot_disable"
                           placeholder="Please Enter Required Remarks"></textarea>
                     </div>
                  </div>
               </div>
               <div class="modal-footer justify-content-center">
                  <button type="reset" class="btn btn-outline-secondary me-1"
                     onclick="closeModal('sendMail');">Cancel</button>
                  <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i>
                  Send</button>
               </div>
         </form>
         </div>
      </div>
   </div>
</div>
<div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1"
   aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header p-0 bg-transparent">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body alertmsg text-center warning">
            <i data-feather='alert-circle'></i>
            <h2>Are you sure?</h2>
            <p>Are you sure you want to <strong>Amend</strong> this <strong>Invoice</strong>?</p>
            <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
            <button type="button" data-bs-dismiss="modal" onclick="amendConfirm();"
               class="btn btn-primary">Confirm</button>
         </div>
      </div>
   </div>
</div>

<div class="modal fade text-start alertbackdropdisabled" id="generateinvoice" tabindex="-1"
   aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header p-0 bg-transparent">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body alertmsg text-center warning">
            <i data-feather='alert-circle'></i>
            <h2>Are you sure?</h2>
            <p>Are you sure you want to <strong>Performa Invoice</strong> to <strong> Final Invoice</strong>?</p>
            <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
            <button type="button" data-bs-dismiss="modal" onclick="invoiceConfirm();"
               class="btn btn-primary">Confirm</button>
         </div>
      </div>
   </div>
</div>
<div class="modal fade text-start show" id="postvoucher" tabindex="-1" aria-labelledby="postVoucherModal"
   aria-modal="true" role="dialog">
   <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
      <div class="modal-content">
         <div class="modal-header">
            <div>
               <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="postVoucherModal"> Voucher
                  Details
               </h4>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="row">
               <div class="col-md-3">
                  <div class="mb-1">
                     <label class="form-label">Series <span class="text-danger">*</span></label>
                     <input id="voucher_book_code" class="form-control" disabled="">
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="mb-1">
                     <label class="form-label">Voucher No <span class="text-danger">*</span></label>
                     <input id="voucher_doc_no" class="form-control" disabled="" value="">
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="mb-1">
                     <label class="form-label">Voucher Date <span class="text-danger">*</span></label>
                     <input id="voucher_date" class="form-control" disabled="" value="">
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="mb-1">
                     <label class="form-label">Currency <span class="text-danger">*</span></label>
                     <input id="voucher_currency" class="form-control" disabled="" value="">
                  </div>
               </div>
               <div class="col-md-12">
                  <div class="table-responsive">
                     <table
                        class="mt-1 table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                        <thead>
                           <tr>
                              <th>Type</th>
                              <th>Group</th>
                              <th>Leadger Code</th>
                              <th>Leadger Name</th>
                              <th class="text-end">Debit</th>
                              <th class="text-end">Credit</th>
                           </tr>
                        </thead>
                        <tbody id="posting-table">
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
         <div class="modal-footer text-end">
            <button onclick="postVoucher(this);" id="posting_button" type="button"
               class="btn btn-primary btn-sm waves-effect waves-float waves-light">
               <svg
                  xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round" class="feather feather-check-circle">
                  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                  <polyline points="22 4 12 14.01 9 11.01"></polyline>
               </svg>
               Submit
            </button>
         </div>
      </div>
   </div>
</div>
<div class="modal fade" id="BundleInfo" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog  modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header p-0 bg-transparent">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body px-sm-2 mx-50 pb-2">
            <h1 class="text-center mb-1" id="shareProjectTitle">Packing Info</h1>
            <div class="table-responsive-md customernewsection-form">
               <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                  <thead>
                     <tr>
                        <th width="50px"></th>
                        <th>Package</th>
                        <th class="numeric-alignment">Qty</th>
                     </tr>
                  </thead>
                  <tbody id="bundles_info_table" current-item-index="0">
                  </tbody>
               </table>
            </div>
         </div>
         <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-outline-secondary me-1"
               onclick="closeModal('BundleInfo');">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="onBundleSubmit();">Submit</button>
         </div>
      </div>
   </div>
</div>
<div class="modal fade" id="PacketInfo" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog  modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header p-0 bg-transparent">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body px-sm-2 mx-50 pb-2">
            <h1 class="text-center mb-1" id="shareProjectTitle">Packing Info</h1>
            <div class="table-responsive-md customernewsection-form">
               <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                  <thead>
                     <tr>
                        <th>Package</th>
                        <th class="numeric-alignment">Qty</th>
                     </tr>
                  </thead>
                  <tbody id="packing_info_table" current-item-index="0">
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>