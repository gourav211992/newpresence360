<div class="modal fade text-start" id="psModal" tabindex="-1" aria-labelledby="psModal" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal">Production Slips</h4>
                    <p class="mb-0">Select from the below list</p>
                </div>
                <div class="d-flex align-items-start gap-2">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Slip No.</label>
                            <input type="text" id="document_no_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                            <input type="hidden" id="document_id_qt_val"></input>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Item</label>
                            <input type="text" name="item_name_search" id="item_name_search" placeholder="Item Name/Code" class="form-control mw-100" autocomplete="off" value="">
                        </div>
                    </div>
                    <div class="col mb-1">
                        <label class="form-label">&nbsp;</label><br />
                        <button type="button" class="btn btn-warning btn-sm clearPiFilter"><i data-feather="x-circle"></i> Clear</button>
                        <button type="button" class="btn btn-primary btn-sm psProcess"> <i data-feather="check-circle"></i> Process</button>
                    </div>
                    <div class="col-md-12">
                        <div class="po-table-container">
                            <table class="table table-striped table-bordered ps-order-detail myrequesttablecbox nowrap w-100">
                                <thead class="table-light header">
                                    <tr>
                                        <th>ID</th>
                                        <th>
                                            <div class="form-check form-check-inline me-0">
                                                <input class="form-check-input" type="checkbox" name="psdetail" id="inlineCheckbox1">
                                            </div>
                                        </th>
                                        <th>Series</th>
                                        <th>Slip No.</th>
                                        <th>Slip Date</th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th>Attributes</th>
                                        <th>UOM</th>
                                        <th>QTY</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody id="psDataTable"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
