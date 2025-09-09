<div class="modal fade text-start" id="soModal" tabindex="-1" aria-labelledby="soModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="soModal">Select Sale Order</h4>
                    <p class="mb-0">Select from the below list</p>
                </div>
                <div class="me-3 d-flex align-items-center">
                    <label for="orderTypeSelect" class="form-label mb-0 me-1">Procurement Type:</label>
                    <div class="me-3">
                        <select class="form-select form-select-sm" id="orderTypeSelect" style="width: auto;">
                            <option value="fg">Buy to Order (FG)</option>
                            <option value="rm" selected>Make to Order (RM)</option>
                        </select>
                    </div>
                    <label for="attributeCheck" class="form-label mb-0 me-1">Show Attributes:</label>
                    <div class="form-check me-3">
                        <input class="form-check-input" type="checkbox" id="attributeCheck">
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Series</label>
                            <input type="text" id="book_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                            <input type = "hidden" id = "book_id_qt_val"></input>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Doc No.</label>
                            <input type="text" id="document_no_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                            <input type = "hidden" id = "document_id_qt_val"></input>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Customer</label>
                            <input type="text" id="customer_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                            <input type = "hidden" id = "customer_id_qt_val"></input>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Item</label>
                            <input type="text" name="item_name_search" id="item_name_search" placeholder="Item Name/Code" class="form-control mw-100" autocomplete="off" value="">
                        </div>
                    </div>
                    <div class="col  mb-1">
                        <label class="form-label">&nbsp;</label><br />
                        <button type="button" class="btn btn-warning btn-sm clearPiFilter"><i data-feather="x-circle"></i> Clear</button>
                        {{-- <button type = "button" class="btn btn-warning btn-sm searchSoBtn"><i data-feather="search"></i> Search</button> --}}
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check form-check-inline me-0">
                                                <input class="form-check-input" type="checkbox" name="podetail" id="inlineCheckbox1">
                                            </div>
                                        </th>
                                        <th>Series</th>
                                        <th>Doc No.</th>
                                        <th>Doc Date</th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th id="soHeaderAttribute" class="d-none">Attributes</th>
                                        <th>Quantity</th>
                                        <th>Customer</th>
                                        {{-- <th>Is Bom</th> --}}
                                    </tr>
                                </thead>
                                <tbody id="soDataTable">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                {{-- <h6 class="m-0 text-left flex-grow-1 text-danger d-none" id="soTrackingText">*SO tracking enabled, only one SO and item pair can be selected.</h6> --}}
                <button type = "button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
                <button type = "button" class="btn btn-primary btn-sm soProcess"><i data-feather="check-circle"></i> Process</button>
                <button type="button" class="btn btn-primary btn-sm analyzeButton"> <i data-feather="check-circle"></i> Analyze</button>
            </div>
        </div>
    </div>
</div>
