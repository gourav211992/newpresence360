<div class="modal fade text-start" id="overheadSummaryPopup" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
      <div class="modal-content">
         <div class="modal-header p-0 bg-transparent">
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <h1 class="text-center mb-1" id="shareProjectTitle">Overheads</h1>
            <h4 class="text-center mb-1">Total : <span id="itemLevelSubTotalPrice">0</span></h4>
            <div class="table-responsive-md">
               <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border" id="headerOverheadTbl"
               data-json-key="header_overhead_json"
               data-row-selector=".display_overhead_row">
                  <thead>
                     <tr>
                        <th width="50px">S.No</th>
                        <th>Overhead</th>
                        <th width="80px">%</th>
                        <th width="150px">Amount</th>
                        <th>Ledger</th>
                        <th>Ledger Group</th>
                        <th width="50px">Action</th>
                     </tr>
                  </thead>
                  <tbody>
                  @include('billOfMaterial.partials.overhead.add-comp-level')
                  </tbody>
               </table>
            </div>
         </div>
         <div class="modal-footer text-end">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
            <button type="button" class="btn btn-primary btn-sm overheadSummaryBtn"><i data-feather="check-circle"></i> Submit</button>
         </div>
      </div>
   </div>
</div>