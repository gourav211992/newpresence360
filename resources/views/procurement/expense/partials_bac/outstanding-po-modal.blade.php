<div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="myModalLabel17"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">
                        Select Pending PO
                    </h4>
                    <p class="mb-0">
                        Select from the below list
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Select Vendor <span class="text-danger">*</span></label>
                            <select class="select2 form-select" name="po_vendor_id" id="vendor-select">
                                <option value="">Select</option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" data-item="{{ json_encode($vendor) }}">
                                        {{ $vendor->company_name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" id="po_vendor_id" name="po_vendor_id">
                            <div id="vendorNotSelect"></div>
                        </div>
                        <input type="hidden" name="type" id="report-type" value="">
                        <div class="col-md-4">
                            <label class="form-label">Select PO <span class="text-danger">*</span></label>
                            <select name="purchase_order_id" id="po_items" class="select2 form-select">
                                <option value="">-Select PO-</option>
                                @foreach ($purchaseOrders as $val)
                                    <option value="{{ $val->id }}"
                                        data-item="{{ json_encode($val) }}">
                                        {{ $val->document_number }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" id="po_header_id" name="po_id">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check form-check-inline me-0">
                                                <input class="form-check-input" type="checkbox" name="podetail"
                                                    id="po-items-select-all">
                                            </div>
                                        </th>
                                        <th>PO NO.</th>
                                        <th>PO Date</th>
                                        <th>Item Name</th>
                                        <th>Item Remark</th>
                                        <th>Quantity</th>
                                        <th>Balance Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="po-modal-table-body">
                                </tbody>
                                <input type="hidden" id="po-item-ids" name="po_item_ids[]">
                                <div id="notSelect"></div>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer text-end">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                        data-feather="x-circle"></i> Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="process-btn"><i
                        data-feather="check-circle"></i>
                    Process</button>
            </div>
        </div>
    </div>
</div>