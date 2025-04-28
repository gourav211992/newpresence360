<div class="modal fade" id="summaryDiscountModal" tabindex="-1" aria-labelledby="headerDisc" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="headerDisc">Discount</h1>
                <div class="text-end"></div>
                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                    <thead>
                        <tr>
                            <td>#</td>
                            <td>
                                <input type="text" id="new_dis_name" class="form-control mw-100" />
                            </td>
                            <td>
                                <input step="any" type="number" id="new_dis_perc" class="form-control mw-100" />
                            </td>
                            <td>
                                <input step="any" type="number" id="new_dis_value" class="form-control mw-100" />
                            </td>
                            <td>
                                <a href="javascript:;" id="add_new_head_dis" class="text-primary can_hide">
                                    <i data-feather="plus-square"></i>
                                </a>
                            </td>
                        </tr>
                    </thead>
                </table>
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
                            @if(isset($mr->headerDiscount) && $mr->headerDiscount->count())
                                @foreach($mr->headerDiscount as $hd_key => $headerDiscount)
                                    <tr class="display_summary_discount_row">
                                        <td>{{$hd_key+1}}</td>
                                        <td>{{$headerDiscount->ted_name ?? ''}}
                                            <input type="hidden" value="{{$headerDiscount->id}}" name="disc_summary[{{$hd_key+1}}][d_id]">
                                            <input type="hidden" value="{{$headerDiscount->ted_name}}" name="disc_summary[{{$hd_key+1}}][d_name]">
                                        </td>
                                        <td class="text-end">{{floatval($headerDiscount->ted_percentage) ?? 0}}
                                            <input type="hidden" value="{{floatval($headerDiscount->ted_percentage) ?? ''}}" name="disc_summary[{{$hd_key+1}}][d_perc]" />
                                        </td>
                                        <td class="text-end">{{floatval($headerDiscount->ted_amount) ?? ''}}
                                            <input type="hidden"  value="{{$headerDiscount->ted_amount ?? ''}}" name="disc_summary[{{$hd_key+1}}][d_amnt]" />
                                        </td>
                                        <td>
                                            <a href="javascript:;" class="text-danger deleteSummaryDiscountRow">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            <tr id="disSummaryFooter">
                                <td colspan="2"></td>
                                <td class="text-dark"><strong>Total</strong></td>
                                <td class="text-dark text-end"><strong id="total" amount="{{@$mr->total_header_disc_amount}}">{{number_format(@$mr->total_header_disc_amount,2)}}</strong></td>
                                <td></td>
                            </tr>
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



