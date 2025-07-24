<div class="col-md-12">
    {{-- Append Attribute here --}}
    <div class="card">
        <div class="card-body customernewsection-form">
            <div class="border-bottom mb-2 pb-25" id="componentSection">
                <div class="row">
                    <div class="col-md-6">
                        <div class="newheader">
                            <h4 class="card-title text-theme">Order Details</h4>
                            {{-- <p class="card-text">Fill the details</p> --}}
                        </div>
                    </div>
                    {{-- <div class="col-md-6 text-sm-end">
                        <a href="javascript:;" class="btn btn-sm btn-outline-danger me-50" id="deleteBtn">
                        <i data-feather="x-circle"></i> Delete</a>
                        <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary">
                        <i data-feather="plus"></i> Add Items</a>
                    </div> --}}
                </div>
                <div class="row">
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Delivery Date</label>
                            <input type="date" class="bg-white form-control" name="Period" id="delivery_date_filter" />
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">SO No.</label>
                            <input type="text" id="so_document_no_input_qt" class="form-control mw-100">
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Doc Date</label>
                            <input type="date" class="bg-white form-control" name="Period" id="document_date_filter" />
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Customer</label>
                            <input type="text" id="customer_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                            <input type="hidden" id="customer_id_qt_val"></input>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-check form-check-primary custom-checkbox mt-2">
                        <input type="checkbox" class="form-check-input " id="out_of_stock_check" oninput = "loadOrders();">
                            <label class="form-check-label" for="out_of_stock_check" >Show out Of Stock</label>
                        </div>
                    </div>
                    <div class="col mb-1">
                        <label class="form-label">&nbsp;</label><br />
                        {{-- <button type="button" class="btn btn-primary btn-sm searchPiBtn"><i data-feather="search"></i> Search</button> --}}
                        <button type="button" class="btn btn-warning btn-sm clearPiFilter"><i data-feather="x-circle"></i> Clear</button>
                    </div>
                </div>
            </div>
            <div class="table-responsive pomrnheadtffotsticky">
                <table id="itemTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                    <thead>
                        <tr>
                            <th>
                                <div class="form-check form-check-primary custom-checkbox">
                                    <input type="checkbox" class="form-check-input" id="select_all_orders">
                                    <label class="form-check-label" for="select_all_orders"></label>
                                </div>
                            </th>
                            <th>Series</th>
                            <th>Doc No.</th>
                            <th>Doc Date</th>
                            <th>Delivery Date</th>
                            <th>Item Code</th>
                            <th>Item Name</th>
                            <th>Currency</th>
                            <th>Attributes</th>
                            <th>UOM</th>
                            <th class="text-end">Order Qty</th>
                            <th class="text-end">Balance Qty</th>
                            <th class="text-end">Avl Stk</th>
                            <th class="text-end">Pick Qty</th>
                            <th>Rate.</th>
                            <th>Customer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be populated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
