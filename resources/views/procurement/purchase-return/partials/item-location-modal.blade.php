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

                <div class="table-responsive-md customernewsection-form">
                    <table id="deliveryScheduleTable" class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                        <thead>
                            <tr>
                                <th width="80px">S.No.</th>
                                <th>Rack</th>
                                <th>Shelf</th>
                                <th>Bin</th>
                                <th width="50px" class="text-end">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($mrn->itemLocations) && $mrn->itemLocations->count())
                                @foreach($mrn->itemLocations as $key => $location)
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        <td>
                                            {{ @$location->erpRack->rack_code }}
                                        </td>
                                        <td>
                                            {{ @$location->erpShelf->shelf_code }}
                                        </td>
                                        <td>
                                            {{ @$location->erpBin->bin_code }}
                                        </td>
                                        <td>
                                            {{ number_format($location->inventory_uom_qty, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            <tr id="itemLocationFooter">
                                <td colspan="3"></td>
                                <td class="text-dark">
                                    <strong>Total</strong>
                                </td>
                                <td class="text-dark text-end">
                                    <strong id="total" amount=""></strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Store Item Modal End -->
