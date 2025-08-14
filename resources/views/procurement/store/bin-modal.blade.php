<div class="modal fade" id="addbin" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-4 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Add/Edit Bin</h1>
                <p class="text-center">Enter the details below.</p>

                <div class="row mt-2">
                    <div class="col-md-12 mb-4">
                        <div class="d-flex align-items-center">
                            <input type="text" id="bin_code" class="form-control" placeholder="Enter Bin No." aria-label="Bin Number">
                            <button class="btn btn-outline-primary ms-2 btn-sm " id="search-bin">
                                <i class="fa fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" id="deleted_bins" name="deleted_bins" value="">

                <div class="table-responsive" style="max-height: 300px">
                    <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Bin No</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="bin-list">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>