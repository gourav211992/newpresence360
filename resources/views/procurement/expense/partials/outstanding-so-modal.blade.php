<div class="modal fade text-start" id="rescdule-so" tabindex="-1" aria-labelledby="myModalLabel17"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">
                        Select Pending SO
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
                            <label class="form-label">Select Customer<span class="text-danger">*</span></label>
                            <select class="select2 form-select" name="sale_order_id" id="customer-select">
                                <option value="">Select</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" data-item="{{ json_encode($customer) }}">
                                        {{ $customer->company_name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" id="sale_order_id" name="sale_order_id">
                            <div id="customerNotSelect"></div>
                        </div>
                        <input type="hidden" name="type" id="report-type" value="">
                        <div class="col-md-4">
                            <label class="form-label">Select SO <span class="text-danger">*</span></label>
                            <select name="sale_order_id" id="so_items" class="select2 form-select">
                                <option value="">-Select SO-</option>
                                @foreach ($saleOrders as $val)
                                    <option value="{{ $val->id }}"
                                        data-item="{{ json_encode($val) }}">
                                        {{ $val->document_number }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" id="so_header_id" name="so_id">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="mt-1 table myrequesttablecbox table-striped so-order-detail">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check form-check-inline me-0">
                                                <input class="form-check-input" type="checkbox" name="sodetail"
                                                    id="so-items-select-all">
                                            </div>
                                        </th>
                                        <th>SO NO.</th>
                                        <th>SO Date</th>
                                        <th>Item Name</th>
                                        <th>Item Remark</th>
                                        <th>Quantity</th>
                                        <th>Balance Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="so-modal-table-body">
                                </tbody>
                                <input type="hidden" id="so-item-ids" name="so_item_ids[]">
                                <div id="notSelect"></div>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer text-end">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                        data-feather="x-circle"></i> Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="so-process-btn"><i
                        data-feather="check-circle"></i>
                    Process</button>
            </div>
        </div>
    </div>
</div>