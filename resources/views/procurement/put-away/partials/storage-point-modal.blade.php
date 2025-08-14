<!-- Storage Points Modal -->
<div class="modal fade" id="storagePointsModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="text-center modal-title mb-1" id="shareProjectTitle">Storage Point</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 text-end">
                        <button type="button" class="btn btn-sm btn-outline-primary add-storage-row-header">
                            <i data-feather='plus'></i> Add
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger delete-storage-row-header">
                            <i data-feather='trash'></i> Delete
                        </button>
                    </div>
                </div>
                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id="storagePacketTable">
                    <thead>
                        <tr>
                            <th width="30px;">#</th>
                            <th width="80px;">Quantity</th>
                            <th width="250px;">QR/Bar Code No.</th>
                            <th width="600px;">Storage Point</th>
                            <th width="50px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <input type="hidden" id="storagePointsRowIndex" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveStoragePointsBtn">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- Store Item Modal End -->
