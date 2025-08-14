{{-- Add Section --}}
<div class="modal fade" id="addSectionItemPopup" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog  modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header p-0 bg-transparent">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body px-sm-2 mx-50 pb-2">
            <h1 class="text-center mb-1" id="shareProjectTitle">Section</h1>
            {{-- <p class="text-center">Enter the details below.</p> --}}
            <div class="row">
               <div class="col-md-12 mb-1">
                  <input type="hidden" id="row_count" name="row_count" />
                  <label class="form-label">Select Section <span class="text-danger">*</span></label>
                  <input type="hidden" name="section_id" id="section_id">
                  <input type="hidden" name="section_name" id="section_name">
                  <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" id="product_section" name="product_section" />
               </div>
               <div class="col-md-12 mb-1">
                  <label class="form-label">Select Sub Section <span class="text-danger">*</span></label>
                  <input type="hidden" name="sub_section_id" id="sub_section_id">
                  <input type="hidden" name="sub_section_name" id="sub_section_name">
                  <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" id="product_sub_section" name="product_sub_section" />
               </div>
               <div class="col-md-12 mb-1">
                  <label class="form-label">Station <span class="text-danger">*</span></label>
                  <input type="hidden" name="station_id" id="station_id">
                  <input type="hidden" name="station_name" id="station_name">
                  <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" id="product_station" name="product_station" />
               </div>
            </div>
         </div>
         <div class="modal-footer justify-content-center">  
            <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button> 
            <button type="button" class="btn btn-primary submitStationBtn">Select</button>
         </div>
      </div>
   </div>
</div>