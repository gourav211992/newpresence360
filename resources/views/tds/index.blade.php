@extends('layouts.app')
@section('content')
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
                                        <h3>TDS Report</h3>
                                        <p>{{$fy}}</p>
                                    </div>
                                    <div
                                        class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                        <button class="btn btn-primary btn-sm mb-50 mb-sm-0 me-50" data-bs-target="#filter"
                                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                                    </div>
                                </div>


                            </div>
                            <div class="col-md-12">
                                <div
                                    class="table-responsive trailbalnewdesfinance po-reportnewdesign trailbalnewdesfinancerightpad gsttabreporttotal">
                                    <table class="datatables-basic table myrequesttablecbox">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Vendor Name</th>
                                                <th>PAN</th>
                                                <th>Section</th>
                                                <th>Type of<br />Deductee</th>
                                                <th>Voch. No.</th>
                                                <th class="text-end">Amount<br />Paid/Credited</th>
                                                <th>Paid/Credited<br />Date</th>
                                                <th>Cash With.<br />Exceed. Limit</th>
                                                <th>Deduction<br />Date</th>
                                                <th class="text-end">Deducted<br />Amt</th>
                                                <th>Deduction<br />Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($records as $index=>$row)
                                            @php
                                               $vs =  App\Models\Voucher::where('reference_doc_id',$row->id)->where('reference_service','expense_advice')->first();
                                            @endphp
                                            <tr class="trail-bal-tabl-none">
                                                <td>{{$index+1}}</td>
                                                <td>
                                                    <div style="width: 200px" class="fw-bolder text-dark">{{$row->vendor ? $row->vendor?->company_name : 'N/A';}}
                                                    </div>
                                                </td>
                                                <td>{{$row->vendor ? $row->vendor?->pan_number : 'N/A'}}</td>
                                                <td>
                                                    <div style="width: 200px">
                                                        {{ $row->items->pluck('hsn.tax.tax_group')->filter()->unique()->implode(', ') }}

                                                    </div>
                                                </td>
                                                <td>{{ $row->vendor ? ($row?->vendor?->erpOrganizationType?->name == 'Private Limited' || $row?->vendor?->erpOrganizationType?->name == 'Public Limited' ? 'Company' : 'Non-Company') : 'N/A' }}</td>
                                                <td><a href="#"><span
                                                            class="badge rounded-pill badge-light-secondary badgeborder-radius">{{$vs?->voucher_no}}</span></a>
                                                </td>
                                                <td class="text-end">{{number_format(($row->total_item_amount - $row->total_discount),2)}}</td>
                                                <td>{{$vs?->document_date}}</td>
                                                <td>No</td>
                                                <td>{{date('d/m/Y', strtotime($row->document_date)) ?? 'N/A'}}</td>
                                                <td class="text-end">{{number_format($row->total_taxes,2)}}</td>
                                                <td>{{number_format(($row->total_taxes/($row->total_item_amount - $row->total_discount))*100,1)}}%</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="5" class="text-center">Total</td>
                                                <td class="text-end">&nbsp;</td>
                                                <td id="credited" class="text-end"></td>
                                                <td class="text-end">&nbsp;</td>
                                                <td class="text-end">&nbsp;</td>
                                                <td class="text-end"></td>
                                                <td id="deducted" class="text-end"></td>
                                                <td class="text-end">&nbsp;</td>
                                            </tr>
                                        </tfoot>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
                <!-- ChartJS section end -->

            </div>
        </div>
    </div>

    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0" method="GET" action="{{ route('finance.tds') }}">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Period</label>
                        <!--                        <input type="text" id="fp-default" class="form-control flatpickr-basic" placeholder="YYYY-MM-DD" />-->
                        <input type="text" id="fp-range" name="date" class="form-control flatpickr-range bg-white"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" value="{{$range}}" />
                    </div>



                    <div class="mb-1">
                        <label class="form-label">Organization</label>
                        <select name="organization_filter" id="organization_filter" class="form-select select2">
                            <option value="">Select</option>
                            @foreach ($mappings as $organization)
                        <option value="{{ $organization->organization->id }}"
                            {{ $organization->organization->id == $organization_id ? 'selected' : '' }}>
                            {{ $organization->organization->name }}
                        </option>
                    @endforeach

                        </select>
                    </div>
                     <div class="mb-1">
                        <label class="form-label">Location</label>
                        <select id="location_id" name="location_id" class="form-select select2">
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Cost Center</label>
                        <select id="cost_center_id" class="form-select select2"
                            name="cost_center_id">
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">TDS Section</label>
                        <select class="form-select select2" name="tax_filter">
                            <option value="">Select</option>
                            @foreach($taxTypes as $tax)
                            <option value="{{$tax->id}}">{{$tax->tax_group}}</option>
                        @endforeach
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Vendor Name</label>
                        <select class="form-select select2" name="vendor_filter">
                        @foreach($vendors as $org)
                        <option value="{{$org->id}}" @if($org->id===$vendor_id) selected @endif>{{$org->company_name}}</option>
                        @endforeach
                        </select>
                    </div>



                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection



@section('scripts')
<script>
    const locations = @json($locations);
    const costCenters = @json($cost_centers);
</script>
<script>
    $(window).on('load', function() {
        if (feather) {
            feather.replace({
                width: 14,
                height: 14
            });
        }
    })

    $(function() {
        $(".sortable").sortable();
    });
 function updateLocationsDropdown(selectedOrgId) {
        console.log(selectedOrgId,'selected')
        const filteredLocations = locations.filter(loc =>
            String(loc.organization_id) === String(selectedOrgId)
        );

        const $locationDropdown = $('#location_id');
        $locationDropdown.empty().append('<option value="">Select</option>');
        const selectedLocationId = "{{ $location_id }}";


        filteredLocations.forEach(loc => {
        // const isSelected = String(loc.id) === String(selectedLocationId) ? 'selected' : '';
        $locationDropdown.append(`<option value="${loc.id}" >${loc.store_name}</option>`);
        });

        $locationDropdown.trigger('change');
    }
    function loadCostCenters(locationId) {
            if (locationId) {
               const filteredCenters = costCenters.filter(center => {
                    if (!center.location) return false;

                    const locationArray = Array.isArray(center.location)
                        ? center.location.flatMap(loc => loc.split(','))
                        : [];

                    return locationArray.includes(String(locationId));
                });
            // console.log(filteredCenters,costCenters,locationId);

            const $costCenter = $('#cost_center_id');
            $costCenter.empty();

            if (filteredCenters.length === 0) {
                // $costCenter.prop('required', false);
                $('.cost_center').hide();
            } else {
                $costCenter.append('<option value="">Select Cost Center</option>');
                $('.cost_center').show();

                const selectetedCostId = "{{ $cost_center_id }}";


                filteredCenters.forEach(center => {
                    // const isCostSelected = String(center.id) === String(selectetedCostId) ? 'selected' : '';
                    $costCenter.append(`<option value="${center.id}">${center.name}</option>`);
                });
            }

            $costCenter.trigger('change');
        }
    }

    $(document).ready(function() {
    // On change of organization
        $('#organization_filter').on('change', function () {
             const selectedOrgId = $(this).val(); 
            updateLocationsDropdown(selectedOrgId);
        });

        // On page load, check for preselected orgs
        console.log('preselectedOrgId',$('#organization_filter').val())
        const preselectedOrgId = $('#organization_filter').val();
        if (preselectedOrgId) {
            updateLocationsDropdown(preselectedOrgId);
        }
        // On location change, load cost centers
        $('#location_id').on('change', function () {
            const locationId = $(this).val();
          if (!locationId) {
        $('#cost_center_id').empty().append('<option value="">Select Cost Center</option>');
            // $('.cost_center').hide(); // Optional: hide the section if needed
                return;
            }
            loadCostCenters(locationId);
        });
            $(".open-job-sectab").click(function() {
                $(this).parent().parent().next('tr').show();
                $(this).parent().find('.close-job-sectab').show();
                $(this).parent().find('.open-job-sectab').hide();
            });
    });
    $(function() {
    var dt_basic_table = $('.datatables-basic'),
        assetPath = '../../../app-assets/';

    if ($('body').attr('data-framework') === 'laravel') {
        assetPath = $('body').attr('data-asset-path');
    }

    // DataTable with buttons
    if (dt_basic_table.length) {
        var dt_basic = dt_basic_table.DataTable({
            order: [
                [0, 'asc']
            ],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-3"l><"col-sm-12 col-md-6 withoutheadbuttin dt-action-buttons text-end pe-0"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 8,
            lengthMenu: [8, 10, 25, 50, 75, 100],
            buttons: [{
                extend: 'collection',
                className: 'btn btn-outline-secondary dropdown-toggle',
                text: feather.icons['share'].toSvg({
                    class: 'font-small-3 me-50'
                }) + 'Export',
                buttons: [{
                        extend: 'excel',
                        text: feather.icons['file'].toSvg({
                            class: 'font-small-4 me-50'
                        }) + 'Excel',
                        className: 'dropdown-item',
                        exportOptions: {
                            columns: [3, 4, 5, 6, 7]
                        }
                    },
                    // {
                    //     extend: 'pdf',
                    //     text: feather.icons['clipboard'].toSvg({
                    //         class: 'font-small-4 me-50'
                    //     }) + 'PDF',
                    //     className: 'dropdown-item',
                    //     exportOptions: {
                    //         columns: [3, 4, 5, 6, 7]
                    //     }
                    // }
                ],
                init: function(api, node, config) {
                    $(node).removeClass('btn-secondary');
                    $(node).parent().removeClass('btn-group');
                    setTimeout(function() {
                        $(node).closest('.dt-buttons').removeClass('btn-group')
                            .addClass('d-inline-flex');
                    }, 50);
                }
            }],
            language: {
                search: '',
                searchPlaceholder: "Search...",
                paginate: {
                    previous: '&nbsp;',
                    next: '&nbsp;'
                }
            }
        });

        // Function to update the total values in the footer
                function updateFooterTotals() {
            var totalCredited = 0;
            var totalDebited = 0;

            // Loop through each row on the current page and calculate the totals
            dt_basic.rows({ page: 'current' }).every(function() {
                var data = this.data();
                var credited = parseFloat(data[6].replace(/,/g, '')) || 0; // Assuming the credited amount is in column 6
                var debited = parseFloat(data[10].replace(/,/g, '')) || 0; // Assuming the debited amount is in column 10

                totalCredited += credited;
                totalDebited += debited;
            });

            // Format the totals with commas and 2 decimal places
            var formattedCredited = totalCredited.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            var formattedDebited = totalDebited.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');

            // Update the footer with the formatted totals
            $('#credited').text(formattedCredited); // Update the credited total in the footer
            $('#deducted').text(formattedDebited); // Update the debited total in the footer
        }

        // Update the footer totals on table draw (when a page change occurs)
        dt_basic.on('draw', function() {
            updateFooterTotals();
        });

        // Initial footer update (in case the page is already loaded with data)
        updateFooterTotals();

        $('div.head-label').html('<h6 class="mb-0">Event List</h6>');
    }
});
</script>

@endsection
