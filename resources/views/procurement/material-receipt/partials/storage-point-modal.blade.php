<!-- Storage Points Modal -->
<div class="modal fade" id="storagePointsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Storage Points</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered" id="storagePointsTable">
                    <thead>
                        <tr>
                            <th>Storage Point</th>
                            <th>Parents</th>
                            <th>Quantity</th>
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
