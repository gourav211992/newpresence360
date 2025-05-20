<div class="modal fade text-start profilenew-modal" id="rescdule" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Add New</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="location.reload();"></button>
            </div>
            <div class="modal-body">
                <form class="form" role="post-supply-split" method="POST" action="{{ route('prospects.supply-split.store') }}" autocomplete="off">
                @csrf
                    <div class="row">
                        <input type="hidden" name="customer_code" value="{{ $customer->customer_code }}" />
                        <input type="hidden" name="customer_id" value="{{ $customer->id }}" />
                        <input type="hidden" name="organization_id" value="{{ $customer->organization_id }}" />
                        <div class="col-sm col-12">
                            <div class="mb-1">
                                <label class="form-label">Supply Partner</label>
                                <select class="form-select select2" name="supply_partner_id">
                                    <option value="">Select</option>
                                    @forelse($partners as $partner)
                                        <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <div class="col-sm col-12">
                            <div class="mb-sm-1">
                                <label class="form-label" for="fp-range">Supply %</label>
                                <input type="text"  class="form-control" name="supply_percentage"/>
                            </div>
                        </div>
                        <div class="col-sm  mb-1 col-12">
                            <label class="form-label">&nbsp;</label><br/>
                            <button type="button" class="btn btn-primary btn-sm data-submit" data-request="supply-split" data-target="[role=post-supply-split]">Add More</button>
                        </div>
                    </div>
                </form>
                    
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive mb-2">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail"> 
                                <thead>
                                    <tr>
                                        <th>Supply Partner</th>
                                        <th>Supply %</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="render-split-data">
                                    @include('crm.prospects.supply-split-list',[
                                        'splitData' => $splitData
                                    ])
                                </tbody>
                            </table>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>