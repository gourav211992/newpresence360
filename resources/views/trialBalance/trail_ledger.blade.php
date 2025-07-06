@extends('layouts.app')

@section('content')
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Ledger Report</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                    <li class="breadcrumb-item"><a href="javascript:;">Finance</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('trial_balance') }}">Trial Ledger</a></li>
                                    <li class="breadcrumb-item active">Ledger View</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                        {{-- <button class="btn btn-primary btn-sm mb-50 mb-sm-0" onclick="window.print()"><i data-feather="printer"></i> Print</button> --}}
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="newheader">
                                           <div>
                                               <h4 class="card-title text-theme text-dark">Ledger: <strong>{{ $ledger }}</strong></h4>
                                               <p class="card-text">{{ date('d-M-Y', strtotime($startDate)) }} to {{ date('d-M-Y', strtotime($endDate)) }}</p>
                                           </div>
                                       </div>
                                    </div>
                                </div>


                                <div class="row">
                                    <div
                                        class="col-md-12 earn-dedtable flex-column d-flex trail-balancefinance leadger-balancefinance">
                                        <div class="table-responsive">
                                            <table class="table border">
                                                <thead>
                                                    <tr>
                                                        <th width="100px">Date</th>
                                                        <th>Particulars</th>
                                                        <th>Series</th>
                                                        <th>Vch. Type</th>
                                                        <th>Vch. No.</th>
                                                        <th>Debit</th>
                                                        <th>Credit</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php  
                                                        use App\Helpers\Helper;  
                                                        $totalDebit=0;
                                                        $totalCredit=0;
                                                    @endphp 
                                                    @foreach ($data as $voucher)
                                                        @php
                                                            $currentDebit=0;
                                                            $currentCredit=0;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ date('d-m-Y',strtotime($voucher->date)) }}</td>
                                                            <td>
                                                                <table class="table my-25 ledgersub-detailsnew">
                                                                    @foreach ($voucher->items as $item)
                                                                        @if ($item->ledger_id==$id)
                                                                            @php
                                                                                $totalDebit=$totalDebit+$item->debit_amt;
                                                                                $totalCredit=$totalCredit+$item->credit_amt;
                                                                                $currentDebit=$item->debit_amt;
                                                                                $currentCredit=$item->credit_amt;
                                                                            @endphp
                                                                        @else
                                                                            @php
                                                                                $currentBalance = $item->debit_amt - $item->credit_amt;
                                                                                $currentBalanceType = $currentBalance >= 0 ? 'Dr' : 'Cr';
                                                                                $currentBalance = abs($currentBalance);
                                                                            @endphp
                                                                            <tr> 
                                                                                <td  style="font-weight: bold; color:black;">{{ $item->ledger->name }}</td>
                                                                                <td class="text-end">{{Helper::formatIndianNumber($currentBalance)}} {{ $currentBalanceType }}</td> 
                                                                            </tr>
                                                                        @endif
                                                                    @endforeach
                                                                </table>
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('vouchers.edit', ['voucher' => $voucher->id]) }}">
                                                                    {{ $voucher?->series?->service?->name }}
                                                                </a>
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('vouchers.edit', ['voucher' => $voucher->id]) }}">
                                                                    {{ $voucher?->series?->book_code }}
                                                                </a>
                                                            </td>
                                                            <td>{{ $voucher->voucher_no??"" }}</td>
                                                            <td>{{ Helper::formatIndianNumber($currentDebit) }}</td>
                                                            <td>{{ Helper::formatIndianNumber($currentCredit) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>                
                                                
                                                <tfoot>
                                                    <tr class="ledfootnobg">
                                                        <td colspan="5" class="text-end">Current Total</td>
                                                        <td>{{ Helper::formatIndianNumber($totalDebit) }}</td>
                                                        <td>{{ Helper::formatIndianNumber($totalCredit) }}</td>
                                                    </tr>
                                                    <tr class="ledfootnobg">
                                                        <td colspan="5" class="text-end">Opening Balance</td>
                                                        <td>@if($opening && $opening->opening_type=="Dr") {{ Helper::formatIndianNumber($opening->opening) }} @endif</td>
                                                        <td>@if($opening && $opening->opening_type=="Cr") {{ Helper::formatIndianNumber($opening->opening) }} @endif</td>
                                                    </tr>
                                                    @php $closing = ($opening->opening)+ $totalDebit-$totalCredit; 
                                                    $closing_type =$closing<0?"Cr":"Dr";
                                                
                                                @endphp
                                                    <td colspan="5" class="text-end">Closing Balance</td>
                                                    <td>@if($closing && $closing_type=="Dr") {{ Helper::formatIndianNumber($closing) }} @endif</td>
                                                    <td>@if($closing && $closing_type=="Cr") {{ Helper::formatIndianNumber(abs($closing)) }} @endif</td>
                                                    </tr>
                                                </tfoot>
                                                
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>
                </div>
                    <form class="add-new-record" method="GET" action="{{ route('trailLedger', [$id,$group]) }}">
                    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
                        <div class="modal-dialog sidebar-sm">
                            <div class="modal-content pt-0">
                                <div class="modal-header mb-1">
                                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close">×</button>
                                </div>
                                <div class="modal-body flex-grow-1">
                                    <div class="mb-1">
                                        <label class="form-label" for="date">Select Period</label>
                                        <input type="text" id="date" name="date"
                                            class="form-control flatpickr-range bg-white"
                                            placeholder="YYYY-MM-DD to YYYY-MM-DD" value="{{request('date')}}"/>
                                    </div>
                                      <div class="mb-1">
                                        <label class="form-label">Organization</label>
                                        <select id="organization_id" name="organization_id" class="form-select select2">
                                        <option value="" disabled>Select</option>
                                        @foreach ($companies as $organization)
                                            <option value="{{ $organization->organization->id }}"
                                                {{ $organization->organization->id == $organizationId ? 'selected' : '' }}>
                                                {{ $organization->organization->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    </div>
                                     <div class="mb-1">
                                        <label class="form-label">Location</label>
                                        <select id="location_id" class="form-select select2"
                                            name="location_id">
                                            <option value="">Select Location</option>
                                           
                                        </select>
                                    </div>
                                    <div class="mb-1">
                                            <label class="form-label">Cost Group</label>
                                            <select id="cost_group_id" class="form-select select2" name="cost_group_id" required>
                                            </select>
                                        </div>
                                    <div class="mb-1">
                                        <label class="form-label">Cost Center</label>
                                        <select id="cost_center_id" class="form-select select2"
                                            name="cost_center_id">
                                            <option value="">Select</option>
                                           
                                        </select>
                                    </div>

                                  
                                </div>
                                <div class="modal-footer justify-content-start">
                                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                                    <button type="reset" class="btn btn-outline-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection
@section('scripts')
<script>
    $(document).ready(function () {
          $('#cost_center_id').prop('required', false);
                $('.cost_center').hide();
        // Hide preloader after full page load
        $(window).on('load', function () {
            $('.preloader').fadeOut();
        });

         // Show preloader only on form submit that causes reload
        $('form.add-new-record').on('submit', function () {
            $('.preloader').fadeIn();
        });

        // Failsafe in case load never triggers (e.g. network failure or redirect)
        setTimeout(function () {
            $('.preloader').fadeOut();
        }, 15000);

    });
</script>
    <script>

        $(document).ready(function() {
            let urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('organization_id') == "")
                $('#organization_id').val(urlParams.get('organization_id'));

            if (urlParams.get('cost_center_id') == "")
                $('#cost_center_id').val(urlParams.get('cost_center_id'));

            if (urlParams.get('location_id') == "")
                $('#location_id').val(urlParams.get('location_id'));
        });
        const locations = @json($locations);
        const costCenters = @json($cost_centers);
        const costGroups = @json($cost_groups);

        function updateLocationsDropdown(selectedOrgIds) {
            selectedOrgIds = $('#organization_id').val() || [];

            const requestedLocationId = @json(request('location_id')) || "";

            const filteredLocations = locations.filter(loc =>
                selectedOrgIds.includes(String(loc.organization_id))
            );

            const $locationDropdown = $('#location_id');
            $locationDropdown.empty().append('<option value="">Select</option>');


            filteredLocations.forEach(loc => {
                const isSelected = String(loc.id) === String(requestedLocationId) ? 'selected' : '';
                $locationDropdown.append(`<option value="${loc.id}" ${isSelected}>${loc.store_name}</option>`);
            });

            // Load cost centers if location was pre-selected
            if (requestedLocationId) {
                loadCostGroupsByLocation(requestedLocationId);
            }

            $locationDropdown.trigger('change');
        }



        function loadCostGroupsByLocation(locationId) {
        const filteredCenters = costCenters.filter(center => {
            if (!center.location) return false;
            const locationArray = Array.isArray(center.location)
                ? center.location.flatMap(loc => loc.split(','))
                : [];
            return locationArray.includes(String(locationId));
        });

        const costGroupIds = [...new Set(filteredCenters.map(center => center.cost_group_id))];
        
        const filteredGroups = costGroups.filter(group => costGroupIds.includes(group.id));
        console.log(filteredCenters,costGroupIds,filteredGroups);

        const $groupDropdown = $('#cost_group_id');
        $groupDropdown.empty().append('<option value="">Select Cost Group</option>');

        filteredGroups.forEach(group => {
            $groupDropdown.append(`<option value="${group.id}">${group.name}</option>`);
        });

        $('#cost_group_id').trigger('change');
    }

    function loadCostCentersByGroup(locationId, groupId) {
        const costCenter = $('#cost_center_id');
        costCenter.empty();

        const filteredCenters = costCenters.filter(center => center.cost_group_id === groupId);

        if (filteredCenters.length === 0) {
            costCenter.prop('required', false);
            $('#cost_center_id').hide();
        } else {
            costCenter.append('<option value="">Select Cost Center</option>');
            $('#cost_center_id').show();

            filteredCenters.forEach(center => {
                costCenter.append(`<option value="${center.id}">${center.name}</option>`);
            });
        }
        costCenter.val(@json(request('cost_center_id')) || "");
        costCenter.trigger('change');
    }
        $('#organization_id').trigger('change');
        // On change of organization
        $('#organization_id').on('change', function() {
            const selectedOrgIds = $(this).val() || [];
            updateLocationsDropdown(selectedOrgIds);

        });

        // On page load, check for preselected orgs
        const preselectedOrgIds = $('#organization_id').val() || [];
        if (preselectedOrgIds.length > 0) {
            updateLocationsDropdown(preselectedOrgIds);
        }
        // On location change, load cost centers
        $('#location_id').on('change', function() {
            const locationId = $(this).val();
            if (!locationId) {
                // $('#cost_center_id').empty().append('<option value="">Select</option>');
                //   $('#cost_center_id').prop('required', false);
                // $('.cost_center').hide();
                // $('.cost_center').hide(); // Optional: hide the section if needed
                return;
            }
            loadCostGroupsByLocation(locationId);



        });
          $('#cost_group_id').on('change', function () {
                const locationId = $('#location_id').val();
                const groupId = parseInt($(this).val());

                if (!locationId || !groupId) {
                    $('#cost_center_id').empty().append('<option value="">Select Cost Center</option>');
                    return;
                }

                loadCostCentersByGroup(locationId, groupId);
            });
    </script>
    

@endsection
