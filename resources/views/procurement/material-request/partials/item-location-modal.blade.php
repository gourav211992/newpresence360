<!-- Store Item Modal Start -->
<div class="modal fade" id="deliveryScheduleModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered modal-lg" >
        <input type="hidden" name="store-row-id" id="store-row-id">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Store Location</h1>
                {{-- <p class="text-center">Enter the details below.</p> --}}
                
                <div class="text-end"> 
                    <a href="javascript:;" class="text-primary add-contactpeontxt mt-50 addTaxItemRow">
                        <i data-feather='plus'></i> Add Store
                    </a>
                </div>
                <div class="table-responsive-md customernewsection-form">
                    <table id="deliveryScheduleTable" class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"> 
                        <thead>
                            <tr>
                                <th width="80px">#</th>
                                <th>Store</th>
                                <th>Rack</th>
                                <th>Shelf</th>
                                <th>Bin</th>
                                <th width="50px">Qty</th>
                                <th>Action</th>
                            </tr>
                    </thead>
                    <tbody>
                        <tr id="deliveryFooter">
                            <td colspan="4"></td>
                            <td class="text-dark"><strong>Total</strong></td>
                            <td class="text-dark"><strong id="total">0.00</strong></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-center">  
                <button type="button" data-bs-dismiss="modal"  class="btn btn-outline-secondary me-1">Cancel</button> 
                <button type="button" class="btn btn-primary itemDeliveryScheduleSubmit">Submit</button>
            </div>
        </div>
    </div>
</div>
<!-- Store Item Modal End -->