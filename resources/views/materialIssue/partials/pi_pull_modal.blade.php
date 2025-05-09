<div class="modal fade text-start" id="rescdulePi" tabindex="-1" aria-labelledby="header_pull_label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1250px">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="header_pull_label">Select
                        Document</h4>
                    <p class="mb-0">Select from the below list</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Department Name <span class="text-danger">*</span></label>
                            <input type="text" id="department_code_input_qt" placeholder="Select"
                                class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off"
                                value="">
                            <input type="hidden" id="department_id_qt_val"></input>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Series <span class="text-danger">*</span></label>
                            <input type="text" id="book_code_input_pi" placeholder="Select"
                                class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off"
                                value="">
                            <input type="hidden" id="book_id_pi_val"></input>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Document No. <span class="text-danger">*</span></label>
                            <input type="text" id="document_no_input_pi" placeholder="Select"
                                class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off"
                                value="">
                            <input type="hidden" id="document_id_pi_val"></input>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" id="item_name_input_pi" placeholder="Select"
                                class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off"
                                value="">
                            <input type="hidden" id="item_id_pi_val"></input>
                        </div>
                    </div>
                    <div class="col  mb-1">
                        <label class="form-label">&nbsp;</label><br />
                        <button onclick="getOrders('pi');" type="button" class="btn btn-warning btn-sm"><i
                                data-feather="search"></i> Search</button>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check form-check-inline me-0">
                                                <input class="form-check-input" type="checkbox" id="checkAllMoElement"
                                                    onchange="checkAllMo(this);">
                                            </div>
                                        </th>
                                        <th>Series</th>
                                        <th>Document No.</th>
                                        <th>Document Date</th>
                                        <th>Department</th>
                                        <th>Requester</th>
                                        <th>SO No.</th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th>Attributes</th>
                                        <th>UOM</th>
                                        <th>Quantity</th>
                                        <th>Balance Qty</th>
                                        <th>Avl Stock</th>
                                    </tr>
                                </thead>
                                <tbody id="qts_data_table_pi">

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer text-end">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                        data-feather="x-circle"></i> Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="processOrder();"
                    data-bs-dismiss="modal"><i data-feather="check-circle"></i> Process</button>
            </div>
        </div>
    </div>
</div>