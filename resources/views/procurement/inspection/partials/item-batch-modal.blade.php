<!-- Storage Points Modal -->
<div class="modal fade" id="itemBatchModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="text-center modal-title mb-1" id="shareProjectTitle">Item Batches</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body itemBatchModal-body">
                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id="itemBatchTable">
                    <thead>
                        <tr>
                            <th width="30px;">#</th>
                            <th width="80px;">Batch Number</th>
                            <th width="60px;">Manufacturing Year</th>
                            <th width="100px;">Expiry Date</th>
                            <th width="60px;">Receipt Qty</th>
                            <th width="60px;">Inspection Qty</th>
                            <th width="60px;">Accepted Qty</th>
                            <th width="60px;">Rejected Qty</th>
                            <!-- <th width="50px;">Action</th> -->
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <input type="hidden" id="itemBatchRowIndex" />
                <input type="hidden" id="itemBatchIsExpiry" value="0" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveItemBatchBtn">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- Store Item Modal End -->
