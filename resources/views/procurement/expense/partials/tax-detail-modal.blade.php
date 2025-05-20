<div class="modal fade" id="expenseTaxDetailModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Taxes</h1>                    
                <div class="table-responsive-md customernewsection-form">
                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id = "order_tax_main_table"> 
                        <thead>
                             <tr>
                                <th>S.No</th>
                                <th width="150px">Tax</th>
                                <th>Taxable Amount</th>
                                <th>Tax %</th>
                                <th>Tax Value</th>
                              </tr>
                            </thead>
                            <tbody id = "expense_tax_details">
                           </tbody>
                    </table>
                </div>                
            </div>
        </div>
    </div>
</div>