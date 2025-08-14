<div class="modal fade" id="consumptionPopup" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header p-2 bg-light">
            <h5 class="modal-title" id="shareProjectTitle">Consumption Calculation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <input type="hidden" name="consumption_row" value="">
         <div class="modal-body px-3 py-2">
            {{-- <p class="text-center">Enter the details below.</p> --}}

            <div class="table-responsive-md">
               <div class="row align-items-center mb-1">
                  <div class="col-md-4">
                     <label class="form-label">Consumption per unit</label>
                  </div>
                  <div class="col-md-8">
                     <input type="number" name="qty_per_unit" class="form-control" id="qty_per_unit" placeholder="Enter Consumption per unit">
                  </div>
               </div>

               <div class="row align-items-center mb-1">
                  <div class="col-md-4">
                     <label class="form-label">Pieces</label>
                  </div>
                  <div class="col-md-8">
                     <input type="number" name="total_qty" class="form-control" id="total_qty" placeholder="Enter Pieces">
                  </div>
               </div>

               <div class="row align-items-center mb-1">
                  <div class="col-md-4">
                     <label class="form-label">Std Qty</label>
                  </div>
                  <div class="col-md-8">
                     <input type="number" name="std_qty" class="form-control" id="std_qty" placeholder="Enter Std Qty">
                  </div>
               </div>

               <div class="row align-items-center mb-1">
                  <div class="col-md-4">
                     <label class="form-label">Norms</label>
                  </div>
                  <div class="col-md-8">
                     <input type="text" name="output" class="form-control" id="output" placeholder="Calculated Output" readonly>
                  </div>
               </div>
            </div>
         </div>

         <div class="modal-footer justify-content-center">
            <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-2">Cancel</button>
            <button type="button" class="btn btn-primary submit_consumption">Select</button>
         </div>
      </div>
   </div>
</div>
