<div class="modal fade text-start" id="pickupModal" tabindex="-1" aria-labelledby="pickupModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
        <div class="modal-content">
            <div class="modal-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="pickupModalTitle">Select Trip</h4>
                    <p class="mb-0">Select from the below list</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Vehicle No.</label>
                            <input type="text" id="vehicle_no_input_pu" placeholder="Vehicle No." class="form-control mw-100" autocomplete="off" value="">
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-1">
                            <label class="form-label">Trip ID</label>
                            <input type="text" id="trip_id_input_pu" placeholder="Trip ID" class="form-control mw-100" autocomplete="off" value="">
                        </div>
                    </div>
                    <div class="col  mb-1">
                        <label class="form-label">&nbsp;</label><br>
                        <button type="button" class="btn btn-warning btn-sm clearPiFilter waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg> Clear</button>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive" style="overflow-y: auto;max-height: 200px;">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check form-check-inline me-0">
                                                <input class="form-check-input" type="checkbox" name="podetail" id="inlineCheckbox1">
                                            </div>
                                        </th>
                                        <th>Date</th>
                                        <th>Pickup Schedule</th>
                                        <th>Vehicle No</th>
                                        <th>Trip ID</th>
                                        <th>Champ</th>
                                        <th>Item Count</th>
                                    </tr>
                                </thead>
                                <tbody id="pickupDataTable">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer text-end">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i data-feather="x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm analyzeButton">
                    <i data-feather="check-circle"></i> Select
                </button>
            </div>
        </div>
    </div>
</div>
