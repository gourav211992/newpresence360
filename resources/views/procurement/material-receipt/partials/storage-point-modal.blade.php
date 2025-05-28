<!-- Storage Points Modal -->
<div class="modal fade" id="storagePointsModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="text-center modal-title mb-1" id="shareProjectTitle">Packets</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <h4 class="text-left modal-title mb-1">
                            <b>Item:</b> <span id="packet_item_name" class="packet_item_name" style="padding-right: 2%;"></span>
                        </h4>
                    </div>
                    <div class="col-md-4">
                        <h4 class="text-left modal-title mb-1">
                            <b>Inv UOM Qty:</b> <span id="packet_inv_uom_qty" class="packet_inv_uom_qty"></span>
                        </h4>
                    </div>
                    <div class="col-md-4 text-end">
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
                            <th width="150px;">QR/Bar Code No.</th>
                            <th width="60px;">Unit</th>
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
