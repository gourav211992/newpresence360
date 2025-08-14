@extends('layouts.app')
@section('content')
<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        
            
        <div class="content-body"> 
                
            <section id="basic-datatable">
                <div class="card border  overflow-hidden"> 
                    <div class="row">
                        <div class="col-md-12 bg-light border-bottom mb-1 po-reportfileterBox">
                            <div class="row pofilterhead action-button align-items-center">
                                <div class="col-md-4">
                                    <h3>{{ $type->description ? $type->description : '' }}</h3>
                                    <p>{{ App\Helpers\GeneralHelper::dateFormat2($startDate) }} to {{ App\Helpers\GeneralHelper::dateFormat2($endDate) }}</p>
                                </div>
                                <div class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end"> 
                                    <button class="btn btn-primary btn-sm mb-50 mb-sm-0 me-50" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>   
                                </div>
                            </div>
                            
                                
                        </div>
                        <div class="col-md-12"> 
                            <div class="card">
                                <div class="table-responsive trailbalnewdesfinance po-reportnewdesign trailbalnewdesfinancerightpad gsttabreporttotal">
                                    @include('finance.partials.table-header',[
                                        'searchPlaceholder' => 'Search...'
                                    ])
                                    @include('finance.gstr.'.$type->name,[
                                        'gstrData' => $gstrData
                                    ])
                                </div> 
                                <div class="d-flex justify-content-end mx-1 mt-50">
                                    {{-- Pagination --}}
                                    {{ $gstrData->appends(request()->input())->links('crm.partials.pagination') }}
                                    {{-- Pagination End --}}
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
                    
            </section>
            <!-- ChartJS section end -->

        </div>
    </div>
</div>
<!-- END: Content-->

<!-- BEGIN: Filter Modal -->
<div class="modal modal-slide-in fade filterpopuplabel" id="filter">
    <div class="modal-dialog sidebar-sm">
        <form class="add-new-record modal-content pt-0" id="filterForm"> 
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Period</label>
                        <input type="text" id="fp-range" class="form-control flatpickr-range bg-white" placeholder="YYYY-MM-DD to YYYY-MM-DD" name="date_range" value="{{ request()->get('date_range') }}"/>
                </div>
                
                <div class="mb-1">
                    <label class="form-label">Group</label>
                    <select class="form-select" name="group_id">
                        <option value="" {{ request()->get('group_id') == "" ? 'selected' : '' }}>Select</option>
                        @forelse($groups as $group)
                            <option value="{{ $group->id }}" {{ request()->get('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                        @empty
                        @endforelse
                    </select>
                </div>
                
                <div class="mb-1">
                    <label class="form-label">Company</label>
                    <select class="form-select" name="company_id">
                        <option value="" {{ request()->get('company_id') == "" ? 'selected' : '' }}>Select</option>
                        @forelse($companies as $company)
                            <option value="{{ $company->id }}" {{ request()->get('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @empty
                        @endforelse
                    </select>
                </div> 
                
                <div class="mb-1">
                    <label class="form-label">Organization</label>
                    <select class="form-select" name="organization_id">
                        <option value="" {{ request()->get('organization_id') == "" ? 'selected' : '' }}>Select</option>
                        @forelse($organizationData as $organization)
                            <option value="{{ $organization->id }}" {{ request()->get('organization_id') == $organization->id ? 'selected' : '' }}>{{ $organization->name }}</option>
                        @empty
                        @endforelse
                    </select>
                </div> 

            </div>
            <div class="modal-footer justify-content-start">
                <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                <button type="button" class="btn btn-outline-secondary" onclick="resetForm();">Reset</button>
            </div>
        </form>
    </div>
</div>
<!-- END: Modal -->
@endsection

@section('scripts')
<script>
    function resetForm() {
        var form = document.getElementById('filterForm');
    
        // Reset the input field manually
        form.querySelector('input[name="date_range"]').value = '';  // Reset date_range field

        // Reset the select fields manually
        form.querySelector('select[name="group_id"]').value = '';  // Reset group_id field
        form.querySelector('select[name="company_id"]').value = '';  // Reset company_id field
        form.querySelector('select[name="organization_id"]').value = '';  // Reset organization_id field

        // Optionally trigger a change event for select elements (if needed)
        ['group_id', 'company_id', 'organization_id'].forEach(function(field) {
            var select = form.querySelector(`select[name="${field}"]`);
            var event = new Event('change');
            select.dispatchEvent(event);
        });
    }
</script>
@endsection