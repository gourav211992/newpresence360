<!-- Storage Points Modal -->
<div class="modal fade" id="storagePointsModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="text-center modal-title mb-1" id="shareProjectTitle">Storage Points</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-primary add-storage-row-header">
                        <i data-feather='plus'></i> Add Storage Point
                    </button>
                </div>
                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id="storagePointsTable">
                    <thead>
                        <tr>
                            <th width="60px">S.No.</th>
                            <th width="200px">Storage Point</th>
                            <th>Available/Max. Weight (Kg)</th>
                            <th>Available/Max. Volume (CUM)</th>
                            <th width="200px">Hierarchy</th>
                            <th width="50px">Quantity</th>
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
