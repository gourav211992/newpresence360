<div class="modal fade text-start show" id="postvoucher" tabindex="-1" aria-labelledby="postVoucherModal" aria-modal="true"
    role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="postVoucherModal"> Voucher
                        Details</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Series <span class="text-danger">*</span></label>
                            <input id = "voucher_book_code" class="form-control" disabled="">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Voucher No <span class="text-danger">*</span></label>
                            <input id = "voucher_doc_no" class="form-control" disabled="" value="">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Voucher Date <span class="text-danger">*</span></label>
                            <input id = "voucher_date" class="form-control" disabled="" value="">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <input id = "voucher_currency" class="form-control" disabled="" value="">
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
                <button id ="posting_button" type = "button"
                    class="btn btn-primary btn-sm waves-effect waves-float waves-light"><svg
                        xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-check-circle">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg> Submit</button>
            </div>
        </div>
    </div>
</div>
