<div class="modal fade" id="summaryDiscountModal" tabindex="-1" aria-labelledby="headerDisc" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="headerDisc">Discount</h1>
                {{-- <p class="text-center">Enter the details below.</p> --}}
                <div class="text-end"><a href="javascript:;" class="text-primary add-contactpeontxt mt-50 addDiscountSummary">
                    <i data-feather='plus'></i> Add Discount</a>
                </div>
                <div class="table-responsive-md customernewsection-form">
                    <table id="summaryDiscountTable" class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"> 
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th width="150px">Discount Name</th>
                                <th>Discount %</th>
                                <th>Discount Value</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                            @if(isset($mrn->headerDiscount) && $mrn->headerDiscount->count())
                                @foreach($mrn->headerDiscount as $hd_key => $headerDiscount)    
                                    <tr class="display_summary_discount_row">
                                        <td>{{ ($hd_key+1) }}</td>
                                        <td>
                                            <input type="hidden" value="{{$headerDiscount->id}}" name="disc_summary[{{$hd_key+1}}][d_id]">
                                            <input type="text" value="{{$headerDiscount->ted_name}}" name="disc_summary[{{$hd_key+1}}][d_name]" class="form-control mw-100">
                                        </td>
                                        <td>
                                            <input type="number" {{intval($headerDiscount->ted_percentage) ? '' : 'readonly'}} value="{{intval($headerDiscount->ted_percentage) ?? ''}}" name="disc_summary[{{$hd_key+1}}][d_perc]" class="form-control mw-100 checkNegativeVal" />
                                        </td>
                                        <td>
                                            <input type="number" {{intval($headerDiscount->ted_percentage) ? 'readonly' : ''}} value="{{$headerDiscount->ted_amount}}" name="disc_summary[{{$hd_key+1}}][d_amnt]" class="form-control mw-100 checkNegativeVal" />
                                        </td>
                                        <td>
                                            <a href="javascript:;" class="text-danger deleteSummaryDiscountRow">
                                                <i data-feather="trash-2"></i>
                                            </a>
                                        </td>
                                    </tr>   
                                @endforeach
                                <tr id="disSummaryFooter">
                                    <td colspan="2"></td>
                                    <td class="text-dark">
                                        <strong>Total</strong>
                                    </td>
                                    <td class="text-dark">
                                        <strong id="total" amount="{{$mrn->header_discount}}">
                                            {{number_format($mrn->header_discount,2)}}
                                        </strong>
                                    </td>
                                    <td></td>
                                </tr>
                            @else
                                <tr class="display_summary_discount_row">
                                    <td>1</td>
                                    <td>
                                        <input type="text" name="disc_summary[1][d_name]" class="form-control mw-100">
                                    </td>
                                    <td>
                                        <input type="number" name="disc_summary[1][d_perc]" class="form-control mw-100" />
                                    </td>
                                    <td>
                                        <input type="number" name="disc_summary[1][d_amnt]" class="form-control mw-100" />
                                    </td>
                                    <td>
                                        <a href="javascript:;" class="text-danger deleteSummaryDiscountRow"><i data-feather="trash-2"></i></a>
                                    </td>
                                </tr>   
                                <tr id="disSummaryFooter">
                                    <td colspan="2"></td>
                                    <td class="text-dark"><strong>Total</strong></td>
                                    <td class="text-dark"><strong id="total" amount="">0.00</strong></td>
                                    <td></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-center">  
                <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button> 
                <button type="button" class="btn btn-primary summaryDiscountSubmit">Submit</button>
            </div>
        </div>
    </div>
</div>