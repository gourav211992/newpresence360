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
                                        <h3>Creditors</h3>
                                        <p class="my-25">As on <strong>{{$date2}}</strong></p>
                                    </div>
                                    <div
                                        class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                        <button data-bs-toggle="modal" data-bs-target="#addcoulmn"
                                            class="btn btn-primary btn-sm mb-0 waves-effect"><i data-feather="filter"></i>
                                            Advance Filter</button>
                                    </div>
                                </div>

                                <div class="customernewsection-form poreportlistview p-1">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Group</label>
                                                <select class="form-select select2" id="filter_group">
                                                    <option value="">Select</option>
                                                    @php
                                                        use App\Helpers\Helper;
                                                        $selectedGroupId = request()->query('group'); // Get group_id from URL params
                                                    @endphp
                                                    
                                                    @isset($all_groups)
                                                    @foreach($all_groups as $group)
                                                        <option value="{{$group->id}}" {{ $selectedGroupId == $group->id ? 'selected' : '' }}>
                                                            {{$group->name}}
                                                        </option>
                                                    @endforeach
                                                    @endisset

                                                </select>
                                       </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Ledger</label>
                                                <select class="form-select select2" id="filter_ledger">
                                                    <option value="">Select</option>
                                                    @php
                                                        $selectedLedgerId = request()->query('ledger'); // Get group_id from URL params
                                                    @endphp
                                                    @isset($all_ledgers)
                                                    @foreach($all_ledgers as $ledger)
                                                    <option value="{{$ledger->id}}" {{ $selectedLedgerId == $ledger->id ? 'selected' : '' }}>{{$ledger->name}}</option>
                                                    @endforeach
                                                    @endisset

                                                </select>
                                       </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="fp-range">Select Period</label>
                                            <input type="text" id="fp-range" class="form-control flatpickr-range bg-white flatpickr-input active" value="{{$date}}" placeholder="YYYY-MM-DD to YYYY-MM-DD" readonly="readonly">
                                        </div>

                                        <div class="col-md-2">
                                            <div class="mt-2 mb-sm-0">
                                                <label class="mb-1">&nbsp;</label>
                                                <button class="btn mt-25 btn-warning btn-sm waves-effect waves-float waves-light" onClick="filter()">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-filter"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg> Run Report</button>
                                            </div>
                                            
                                        </div>
                                        


                                        

                                    </div>
                                    <br>
                                        <div class="col-md-3">
                                            @if(request()->hasAny(['ledger', 'age0', 'age1', 'age2', 'age3', 'age4']))  
                                            <a type="button" href="{{ route('voucher.credit.report') }}" class="btn btn-danger">Clear</a>
                                        @endif
                                        </div>



                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="table-responsive trailbalnewdesfinance po-reportnewdesign gsttabreporttotal">
                                    <table class="datatables-basic table tableistlastcolumnfixed myrequesttablecbox tabledebreport">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Group</th>
                                                <th>Ledger</th>
                                                <th>Credit Days</th>
                                                <th class="text-end">Total O/S</th>
                                                <th class="text-end">OVERDUE</th>
                                        
                                                <!-- Define the variables for all age ranges -->
                                                @php
                                                    $age0 = request()->get('age0', 30);
                                                    $age1 = request()->get('age1', 60);
                                                    $age2 = request()->get('age2', 90);
                                                    $age3 = request()->get('age3', 120);
                                                    $age4 = request()->get('age4', 180);
                                                @endphp
                                        
                                                <!-- Display the age ranges dynamically -->
                                                <th class="text-end">0-{{ $age0 }} Days</th>
                                                <th class="text-end">{{ $age0 + 1 }}-{{ $age1 }} Days</th>
                                                <th class="text-end">{{ $age1 + 1 }}-{{ $age2 }} Days</th>
                                                <th class="text-end">{{ $age2 + 1 }}-{{ $age3 }} Days</th>
                                                <th class="text-end">{{ $age3 + 1 }}-{{ $age4 }} Days</th>
                                                <th class="text-end">Above {{ $age4 }} Days</th>
                                        
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                            @foreach ($vendors as $index => $customer)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="fw-bolder text-dark text-nowrap">
                                                    <div 
                                                        data-bs-placement="top" title="{{ $customer?->ledger_parent_name ??"-" }}">
                                                        {{ $customer?->ledger_parent_name ??"-" }}
                                                    </div>
                                                </td>
                                                
                                                <td class="text-nowrap">
                                                    <div 
                                                        data-bs-placement="top" title="{{ $customer?->ledger_name ??"-" }}">
                                                        {{ $customer?->ledger_name ??"-" }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <div 
                                                        data-bs-placement="top" title="{{ $customer?->credit_days ??0}}">
                                                        {{ $customer?->credit_days ??0}}
                                                    </div>
                                                </td>
                                                <td class="text-end text-nowrap">
                                                    <span class="badge rounded-pill badge-light-success">
                                                    {{ number_format(abs($customer->total_outstanding), 2) }}
                                                    <span class="{{ $customer->total_outstanding < 0 ? 'text-danger' : 'text-success' }}">
                                                        {{ $customer->total_outstanding < 0 ? 'Dr' : 'Cr' }}
                                                    </span>
                                                </td>
                                                <td class="text-end text-nowrap">
                                                    <div 
                                                        data-bs-placement="top" title="{{ $customer?->overdue ??0}}">
                                                        {{ Helper::formatIndianNumber($customer?->overdue ??0)}}
                                                    </div>
                                                </td>
                                                
                                                <td class="text-end text-nowrap">
                                                    {{ number_format(abs($customer->days_0_30), 2) }}
                                                        {{ $customer->days_0_30 < 0 ? 'Dr' : 'Cr' }}
                                                </td>
                                                <td class="text-end text-nowrap">
                                                    {{ number_format(abs($customer->days_30_60), 2) }}
                                                        {{ $customer->days_30_60 < 0 ? 'Dr' : 'Cr' }}
                                                </td>
                                                <td class="text-end text-nowrap">
                                                    {{ number_format(abs($customer->days_60_90), 2) }}
                                                        {{ $customer->days_60_90 < 0 ? 'Dr' : 'Cr' }}
                                                </td>
                                                <td class="text-end text-nowrap">
                                                    {{ number_format(abs($customer->days_90_120), 2) }}
                                                        {{ $customer->days_90_120 < 0 ? 'Dr' : 'Cr' }}
                                                </td>
                                                <td class="text-end text-nowrap">
                                                    {{ number_format(abs($customer->days_120_180), 2) }}
                                                        {{ $customer->days_120_180 < 0 ? 'Dr' : 'Cr' }}
                                                </td>
                                                <td class="text-end text-nowrap">
                                                    {{ number_format(abs($customer->days_above_180), 2) }}
                                                        {{ $customer->days_above_180 < 0 ? 'Dr' : 'Cr' }}
                                                </td>
                                                    <td>
                                                       @if($customer->ledger_id)
                                                       <a href="{{ route('crdr.report.ledger.details', ['credit', $customer->ledger_id, $customer->ledger_parent_id]) }}@if(request('date'))?date={{request('date')}}@endif" target="_blank">
                                                        <i class="cursor-pointer" data-feather='eye'></i>
                                                    </a>
                                                    
                                                       @endif
                                             </td>
                                            </tr>
                                   
                                            @endforeach
                                            

                                        </tbody>


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
    <!-- END: Content-->
    <!-- Advance Filter Modal   -->

    <div class="modal fade text-start" id="invoice-view" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">
                            <span id="party"></span>
                            </h4>
                        <p class="mb-0">View the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">


                        <div class="col-md-12">


                            <div class="table-responsive">
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Series</th>
                                            <th>Doc No.</th>
                                            <th>Doc Date</th>
                                            <th>O/S Amount</th>
                                            <th class="text-end">0-30 Days</th>
                                            <th class="text-end">30-60 days</th>
                                            <th class="text-end">60-90 days</th>
                                            <th class="text-end">90-120 days</th>
                                            <th class="text-end">120-180 days</th>
                                            <th class="text-end">Above 180 days</th>
                                        </tr>
                                    </thead>
                                    <tbody id="inovice_tbody">
                             
                                    </tbody>


                                </table>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                            data-feather="x-circle"></i> Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade text-start filterpopuplabel " id="addcoulmn" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Advance
                            Filter</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <!--
            <div class="row">
             <div class="col-md-7 mt-1">
              <div class="form-check form-check-success mb-1">
               <input type="checkbox" class="form-check-input" id="colorCheck1" data-column-index=""  checked="">
               <label class="form-check-label fw-bolder text-dark" for="colorCheck1">All Columns</label>
              </div>
             </div>
            </div>
    -->

                   

                    <div class="tab-content tablecomponentreport">
                        <div class="tab-pane active" id="Employee">
                            

                            
                            <div class="compoenentboxreport" style="margin-top:2%;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check form-check-primary">
                                            <input type="checkbox" class="form-check-input" checked id="selectAllInputAging">
                                            <label class="form-check-label" for="selectAllInputAging">Ageing</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row sortable">
                                    <!-- New input fields for days -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" id="age0" class="form-control aging-input" value="30"
                                                min="0" placeholder="30 Days">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" id="age1" class="form-control aging-input" value="60"
                                                min="0" placeholder="60 Days">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" id="age2" class="form-control aging-input" value="90"
                                                min="0" placeholder="90 Days">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" id="age3" class="form-control aging-input" value="120"
                                                min="0" placeholder="120 Days">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" id="age4" class="form-control aging-input" value="180"
                                                min="0" placeholder="180 Days">
                                        </div>
                                    </div>
                                </div>
                            </div>



                        </div>
                        <div class="tab-pane" id="Bank">
                            <div class="compoenentboxreport advanced-filterpopup customernewsection-form">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check ps-0">
                                            <label class="form-check-label">Add Filter</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Select Category</label>
                                        <select class="form-select select2">
                                            <option>Select</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Select Sub-Category</label>
                                        <select class="form-select select2">
                                            <option>Select</option>
                                        </select>
                                    </div>

                                </div>

                            </div>
                        </div>
                        <div class="tab-pane" id="Location">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="compoenentboxreport advanced-filterpopup customernewsection-form mb-1">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-check ps-0">
                                                    <label class="form-check-label">Add Scheduler</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row camparboxnewcen">
                                            <div class="col-md-8">
                                                <label class="form-label">To</label>
                                                <select class="form-select select2" multiple>
                                                    <option>Select</option>
                                                    <option>Pawan Kuamr</option>
                                                    <option>Deepak Singh</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row camparboxnewcen">
                                            <div class="col-md-4">
                                                <label class="form-label">Type</label>
                                                <select class="form-select">
                                                    <option>Select</option>
                                                    <option>Daily</option>
                                                    <option>Weekly</option>
                                                    <option>Monthly</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label">Select Date</label>
                                                <input type="datetime-local" class="form-select" />
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label">Remarks</label>
                                                <textarea class="form-control" placeholder="Enter Remarks"></textarea>
                                            </div>




                                        </div>

                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>

                </div>

                <div class="modal-footer ">
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary data-submit mr-1" onclick="filter()">Apply</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <!-- BEGIN: Dashboard Custom Code JS-->
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- END: Dashboard Custom Code JS-->

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


        $(function() {
            $(".ledgerselecct").autocomplete({
                source: [
                    "Furniture (IT001)",
                    "Chair (IT002)",
                    "Table (IT003)",
                    "Laptop (IT004)",
                    "Bags (IT005)",
                ],
                minLength: 0
            }).focus(function() {
                if (this.value == "") {
                    $(this).autocomplete("search");
                }
            });
        });
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })


        var dt_basic_table = $('.datatables-basic');
        if (dt_basic_table.length) {
    var dt_basic = dt_basic_table.DataTable({
        order: [[0, 'asc']],
        scrollX: true,
        dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-3"l><"col-sm-12 col-md-6 withoutheadbuttin dt-action-buttons text-end pe-0"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        "drawCallback": function(settings) {
            feather.replace(); // Re-initialize icons if needed
        },
        displayLength: 8,
        lengthMenu: [8, 10, 25, 50, 75, 100],
        buttons: [{
            extend: 'collection',
            className: 'btn btn-outline-secondary dropdown-toggle',
            text: feather.icons['share'].toSvg({
                class: 'font-small-3 me-50'
            }) + 'Export',
            buttons: [
                {
                    extend: 'excel',
                    text: feather.icons['file'].toSvg({
                        class: 'font-small-4 me-50'
                    }) + 'Excel',
                    className: 'dropdown-item',
                    filename: 'Creditors Report',
                    exportOptions: {
                        columns: ':not(:last-child)' // Excludes the last column (Action)
                    }
                },
                {
                    extend: 'pdf',
                    text: feather.icons['clipboard'].toSvg({
                        class: 'font-small-4 me-50'
                    }) + 'Pdf',
                    className: 'dropdown-item',
                    filename: 'Creditors Report',
                    exportOptions: {
                        columns: ':not(:last-child)' // Excludes the last column (Action)
                    }
                },
                {
                    extend: 'copy',
                    text: feather.icons['mail'].toSvg({
                        class: 'font-small-4 me-50'
                    }) + 'Mail',
                    className: 'dropdown-item',
                    filename: 'Creditors Report',
                    exportOptions: {
                        columns: ':not(:last-child)' // Excludes the last column (Action)
                    }
                }
            ],
            init: function(api, node, config) {
                $(node).removeClass('btn-secondary');
                $(node).parent().removeClass('btn-group');
                setTimeout(function() {
                    $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
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

    $('div.head-label').html('<h6 class="mb-0">Event List</h6>');
}
// Flat Date picker (if needed)
    var dt_date_table = $('.dt-date');
    if (dt_date_table.length) {
        dt_date_table.flatpickr({
            monthSelectorType: 'static',
            dateFormat: 'm/d/Y'
        });
    }
    function getDetails(ledger,ledger_group,partyName){
            $.ajax({
                url: "{{ route('voucher.credit_details.report') }}?ledger_id="+ledger+"&ledger_group_id="+ledger_group+"&type=credit", 
           method: 'GET', // Change to POST if necessary
        dataType: 'json',
        success: function(data) {
            // Check if data is not empty
            if (data.length > 0) {
                var tbody = $('#inovice_tbody'); // Get tbody element
                tbody.empty();
                $('#party').text(partyName)
                    
                        // Loop through the response data and append rows to the table
                        $.each(data, function(index, item) {
                    // Function to format amounts with Cr/Dr
                    function formatAmount(amount) {
                        return amount < 0 ? Math.abs(amount) + ' Cr' : amount + ' Dr';
                    }

                    // Create a new row for each item in the response
                    var row = '<tr>';
                    row += '<td>' + (index + 1) + '</td>'; // Row index
                    row += '<td>' + item.bookCode + '</td>'; // Series column
                    row += '<td>' + item.voucher_no + '</td>'; // Doc No. column
                    row += '<td>' + item.document_date + '</td>'; // Doc Date column
                    row += '<td class="text-end">' + formatAmount(item.total_outstanding) + '</td>'; // O/S Amount column
                    row += '<td class="text-end">' + formatAmount(item.days_0_30) + '</td>'; // 0-30 Days column
                    row += '<td class="text-end">' + formatAmount(item.days_30_60) + '</td>'; // 30-60 Days column
                    row += '<td class="text-end">' + formatAmount(item.days_60_90) + '</td>'; // 60-90 Days column
                    row += '<td class="text-end">' + formatAmount(item.days_90_120) + '</td>'; // 90-120 Days column
                    row += '<td class="text-end">' + formatAmount(item.days_120_180) + '</td>'; // 120-180 Days column
                    row += '<td class="text-end">' + formatAmount(item.days_above_180) + '</td>'; // Above 180 Days column
                    row += '</tr>';

                    // Append the new row to the table body
                    tbody.append(row);
                    $('#invoice-view').modal('show');
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data: ', error);
        }
    });


        }

        $(document).ready(function () {
            let urlParams = new URLSearchParams(window.location.search);
        let selectedVoucher = urlParams.get('voucher');

        if (selectedVoucher) {
            $('#filter_voucher').val(selectedVoucher).select2(); // Set selected and trigger change for Select2
        }

    // Set the input fields' values based on the URL parameters, using defaults if the params are not set
    $('#age0').val(urlParams.get('age0') || 30);  // Default to 30 if age0 is not present
    $('#age1').val(urlParams.get('age1') || 60);  // Default to 60 if age1 is not present
    $('#age2').val(urlParams.get('age2') || 90);  // Default to 90 if age2 is not present
    $('#age3').val(urlParams.get('age3') || 120); // Default to 120 if age3 is not present
    $('#age4').val(urlParams.get('age4') || 180); // Default to 180 if age4 is not present

    function toggleColumns() {
        $(".column-toggle").each(function () {
            let colIndex = $(this).data("column-index");
            if ($(this).is(":checked")) {
                $("table th:nth-child(" + colIndex + "), table td:nth-child(" + colIndex + ")").show();
            } else {
                $("table th:nth-child(" + colIndex + "), table td:nth-child(" + colIndex + ")").hide();
            }
        });
    }

    // Select All Checkbox
    $("#selectAll").change(function () {
        $(".column-toggle").prop("checked", $(this).prop("checked"));
        toggleColumns();
    });

    // Individual Column Toggle
    $(".column-toggle").change(function () {
        toggleColumns();
    });

    // Initialize on page load
    toggleColumns();
});

function filter() {
    let ledger = $('#filter_ledger').val(); 
    let group = $('#filter_group').val(); 
    let range = $('#fp-range').val();
    let ages = []; 
    let isAgingChecked = $('#selectAllInputAging').prop('checked');  // Check if the aging checkbox is checked

    // If the aging checkbox is checked, capture the age values
    if (isAgingChecked) {
        $('.aging-input').each(function() {
            ages.push($(this).val());  // Get value of each aging input field
        });
    }

    let currentUrl = new URL(window.location.href);  // Get the current URL

    // Add or update the voucher parameter
    if (ledger !== "") {
        currentUrl.searchParams.set('ledger', ledger);
    } else {
        currentUrl.searchParams.delete('ledger');
    }
    if (group !== "") {
        currentUrl.searchParams.set('group', group);
    } else {
        currentUrl.searchParams.delete('group');
    }
    if (range !== "") {
        currentUrl.searchParams.set('date', range);
    } else {
        currentUrl.searchParams.delete('date');
    }

    // Add age values to the URL only if aging checkbox is checked
    if (isAgingChecked) {
        for (let i = 0; i < ages.length; i++) {
            currentUrl.searchParams.set('age' + i, ages[i]);  // Add or update age0, age1, age2, etc.
        }
    } else {
        // If aging checkbox is not checked, remove any age parameters
        for (let i = 0; i < 5; i++) {
            currentUrl.searchParams.delete('age' + i);
        }
    }

    const ages_v = ['age0', 'age1', 'age2', 'age3', 'age4'];
        let prevValue = 0; // Start comparison from 0
        let isValid = true;

        $.each(ages_v, function (index, id) {
            let value = parseInt($('#' + id).val(), 10);

            // Validation checks
            if (isNaN(value) || value <= prevValue) {
                isValid = false;
                return false; // Break out of loop on failure
            }

            prevValue = value; // Update previous value for next check
        });


    if(isValid)
        window.location.href = currentUrl.toString();
    else{
    Swal.fire({title: 'Not Valid Ageing!',text: "Each age must be a number greater than the previous one.",icon: 'error'});
    }
}



    </script>
@endsection
