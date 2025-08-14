@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Inventory Report</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                    <li class="breadcrumb-item active">Inventory Report</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                            <i data-feather="arrow-left-circle"></i> Back
                        </button>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="card border  overflow-hidden">
                        <div class="row">
                            <div class="col-md-3 bg-light pe-sm-0 border-end po-reportfileterBox">
                                <div class="pofilterhead action-button">
                                    <div>
                                        <h3>Filters</h3>
                                        <p>Apply the Filter</p>
                                    </div>
                                    <!-- <div>
                                        <button data-bs-toggle="modal" data-bs-target="#addcoulmn" 
                                        class="btn btn-outline-primary btn-sm columnfilterbtn">
                                            <i data-feather="plus-square"></i> Add Columns
                                        </button>
                                    </div> -->
                                </div>
                                <div class="pofilterbasicBox">
                                    <div class="pofilterboxcenter">
                                        <label class="form-label">Peroid</label>
                                        <div class="btn-group new-btn-group">
                                            <input type="radio" class="btn-check" name="Peroid" id="Current" checked />
                                            <label class="btn btn-outline-primary" for="Current">Current Month</label>

                                            <input type="radio" class="btn-check" name="Peroid" id="Last" />
                                            <label class="btn btn-outline-primary" for="Last">Last Month</label>

                                            <input type="radio" class="btn-check" name="Peroid" id="Custom" />
                                            <label class="btn btn-outline-primary" for="Custom">Custom</label>
                                        </div>
                                    </div>
                                    <div class="pofilterboxcenter">
                                        <label class="form-label">Status</label>
                                        <div class="demo-inline-spacing customernewsection-form">
                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                <input type="checkbox" class="form-check-input" id="Email" checked>
                                                <label class="form-check-label" for="Email">Approved</label>
                                            </div>
                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                <input type="checkbox" class="form-check-input" id="SMS">
                                                <label class="form-check-label" for="SMS">Pending</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pofilterboxcenter">
                                        <label class="form-label">Item</label>
                                        <select class="form-select mw-100 select2 item_code" name="item_id">
                                            <option value="">Select</option>
                                            @foreach($items as $item)
                                                <option value="{{$item->id}}">
                                                    {{$item->item_code}}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="pofilterboxcenter">
                                        <!-- <label class="form-label">Item Attributes</label> -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Attribute Name</label>
                                                <select class="form-select mw-100 select2 attribute_name" name="attribute_name" id="attribute_name">
                                                    <option value="">Select</option>
                                                    @foreach($attributeGroups as $val)
                                                        <option value="{{$val->id}}">
                                                            {{$val->name}}
                                                        </option>
                                                    @endforeach
                                                </select>            
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Attribute Value</label>
                                                <select class="form-select mw-100 select2 attribute_value" name="attribute_value" id="attribute_value">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pofilterboxcenter">
                                        <!-- <label class="form-label">Item Stores</label> -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Store</label>
                                                <select class="form-select mw-100 select2 store_code" name="store_id" id="store_id">
                                                    <option value="">Select</option>
                                                    @foreach($erpStores as $val)
                                                        <option value="{{$val->id}}">
                                                            {{$val->store_code}}
                                                        </option>
                                                    @endforeach
                                                </select>           
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Rack</label>
                                                <select class="form-select mw-100 select2 rack_code" name="rack_id" id="rack_id">
                                                    <option value="">Select</option>
                                                </select>    
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pofilterboxcenter">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Shelf</label>
                                                <select class="form-select mw-100 select2 shelf_code" name="shelf_id" id="shelf_id">
                                                    <option value="">Select</option>
                                                </select>            
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Bin</label>
                                                <select class="form-select mw-100 select2 bin_code" name="bin_id" id="bin_id">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>                                    
                                </div>
                                <div class="pofilterhead">
                                    <!-- <div>
                                        <button data-bs-toggle="modal" data-bs-target="#addcoulmn" 
                                        class="btn btn-primary btn-sm mb-0 waves-effect">
                                            <i data-feather="filter"></i> Advance Filter
                                        </button>
                                    </div> -->
                                    <div>
                                        <button data-bs-toggle="modal" data-bs-target="#rescdule" 
                                        class="btn btn-outline-primary btn-sm mb-0 waves-effect">
                                            <i data-feather="search"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-9 ps-0">
                                <div class="table-responsive trailbalnewdesfinance po-reportnewdesign">
                                    <table class="datatables-basic table myrequesttablecbox">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Doc. No.</th>
                                                <th>Doc. Date</th>
                                                <th>Item</th>
                                                <th>Attributes</th>
                                                <th>Store</th>
                                                <th>Rack</th>
                                                <th>Shelf</th>
                                                <th>Bin</th>
                                                <th>Available Qty.</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($records as $val)
                                                <tr>
                                                    <td>
                                                        {{ $loop->iteration }}
                                                    </td>
                                                    <td class="fw-bolder text-dark">
                                                        {{ @$val->document_number }}
                                                    </td>
                                                    <td>
                                                        {{ @$val->document_date }}
                                                    </td>
                                                    <td>
                                                        <div data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" 
                                                        title="{{ @$val->item_name }}">
                                                            {{ @$val->item_name }}[{{@$val->item_code}}]
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" 
                                                        title="{{ @$val->item_name }}">
                                                            @foreach($val->attributes as $val1)
                                                                <span class="badge rounded-pill badge-light-secondary badgeborder-radius">
                                                                    {{@$val1->attributeName->name}} : {{@$val1->attributeValue->value}} 
                                                                </span>
                                                            @endforeach    
                                                        </div>
                                                    </td>
                                                    <td class="fw-bolder text-dark">
                                                        {{@$val->store}}
                                                    </td>
                                                    <td class="fw-bolder text-dark">
                                                        {{@$val->rack}}
                                                    </td>
                                                    <td class="fw-bolder text-dark">
                                                        {{@$val->shelf}}
                                                    </td>
                                                    <td class="fw-bolder text-dark">
                                                        {{@$val->bin}}
                                                    </td>
                                                    <td class="fw-bolder text-dark">
                                                        {{@$val->receipt_qty}}
                                                    </td>
                                                    <td>
                                                        <span class="badge rounded-pill badge-light-{{($val->document_status && ($val->document_status == 'approved')) ? 'success' : 'warning'}} badgeborder-radius">
                                                            {{ucFirst($val->document_status)}}
                                                        </span>
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
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        var dt_basic_table = $('.datatables-basic'),
        dt_date_table = $('.dt-date'),
        dt_complex_header_table = $('.dt-complex-header'),
        dt_row_grouping_table = $('.dt-row-grouping'),
        dt_multilingual_table = $('.dt-multilingual'),
        assetPath = '../../../app-assets/';

        if ($('body').attr('data-framework') === 'laravel') {
            assetPath = $('body').attr('data-asset-path');
        }

        // DataTable with buttons
        // --------------------------------------------------------------------
        if (dt_basic_table.length) {
            var dt_basic = dt_basic_table.DataTable({
                order: [],
                dom: 
                    '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                displayLength: 7,
                lengthMenu: [7, 10, 25, 50, 75, 100],
                buttons: [
                    {
                        extend: 'collection',
                        className: 'btn btn-outline-secondary dropdown-toggle',
                        text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + 'Export',
                        buttons: [
                            {
                                extend: 'print',
                                text: feather.icons['printer'].toSvg({ class: 'font-small-4 mr-50' }) + 'Print',
                                className: 'dropdown-item',
                                exportOptions: { columns: [3, 4, 5, 6, 7] }
                            },
                            {
                                extend: 'csv',
                                text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
                                className: 'dropdown-item',
                                exportOptions: { columns: [3, 4, 5, 6, 7] }
                            },
                            {
                                extend: 'excel',
                                text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
                                className: 'dropdown-item',
                                exportOptions: { columns: [3, 4, 5, 6, 7] }
                            },
                            {
                                extend: 'pdf',
                                text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + 'Pdf',
                                className: 'dropdown-item',
                                exportOptions: { columns: [3, 4, 5, 6, 7] }
                            },
                            {
                                extend: 'copy',
                                text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + 'Copy',
                                className: 'dropdown-item',
                                exportOptions: { columns: [3, 4, 5, 6, 7] }
                            }
                        ],
                        init: function(api, node, config) {
                            $(node).removeClass('btn-secondary');
                            $(node).parent().removeClass('btn-group');
                            setTimeout(function() {
                                $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                            }, 50);
                        }
                    }
                ],
                language: {
                    paginate: {
                        previous: '&nbsp;',
                        next: '&nbsp;'
                    }
                }
            });

            $('div.head-label').html('<h6 class="mb-0">Event List</h6>');
        }

        // Flat Date picker
        if (dt_date_table.length) {
            dt_date_table.flatpickr({
                monthSelectorType: 'static',
                dateFormat: 'm/d/Y'
            });
        }

        // Add New record
        // ? Remove/Update this code as per your requirements ?
        var count = 101;
        $('.data-submit').on('click', function() {
            var $new_name = $('.add-new-record .dt-full-name').val(),
                $new_post = $('.add-new-record .dt-post').val(),
                $new_email = $('.add-new-record .dt-email').val(),
                $new_date = $('.add-new-record .dt-date').val(),
                $new_salary = $('.add-new-record .dt-salary').val();

            if ($new_name != '') {
                dt_basic.row.add({
                    responsive_id: null,
                    id: count,
                    full_name: $new_name,
                    post: $new_post,
                    email: $new_email,
                    start_date: $new_date,
                    salary: '$' + $new_salary,
                    status: 5
                }).draw();
                count++;
                $('.modal').modal('hide');
            }
        });

        // Delete Record
        $('.datatables-basic tbody').on('click', '.delete-record', function() {
            dt_basic.row($(this).parents('tr')).remove().draw();
        });
    });
</script>
<script>
    $(document).on('change', '.store_code', function() {
        var store_code_id = $(this).val();
        $('#store_id').val(store_code_id).select2();
        
        var data = {
            store_code_id: store_code_id
        };
        
        $.ajax({
            type: 'POST',
            data: data,
            url: '/material-receipts/get-store-racks',
            success: function(data) {
                $('#rack_id').empty();
                $.each(data.storeRacks, function(key, value) {
                    $('#rack_id').append('<option value="'+ key +'">'+ value +'</option>');
                });
                $('#rack_id').trigger('change');
                
                $('#bin_id').empty();
                $.each(data.storeBins, function(key, value) {
                    $('#bin_id').append('<option value="'+ key +'">'+ value +'</option>');
                });
            }
        });
    });

    $(document).on('change', '.rack_code', function() {
        var rack_code_id = $(this).val();
        $('#rack_id').val(rack_code_id).select2();
        
        var data = {
            rack_code_id: rack_code_id
        };
        
        $.ajax({
            type: 'POST',
            data: data,
            url: '/material-receipts/get-rack-shelfs',
            success: function(data) {
                $('#shelf_id').empty();
                $.each(data.storeShelfs, function(key, value) {
                    $('#shelf_id').append('<option value="'+ key +'">'+ value +'</option>');
                });

                $('#shelf_id').trigger('change');
            }
        });
    });

    // Get Attribute Values
    $(document).on('change', '.attribute_name', function() {
        var attribute_name = $(this).val();
        $('#attribute_name').val(attribute_name).select2();
        
        var data = {
            attribute_name: attribute_name
        };
        
        $.ajax({
            type: 'POST',
            data: data,
            url: '/inventory-reports/get-attribute-values',
            success: function(data) {
                $('#attribute_value').empty();
                $.each(data.attributeValues, function(key, value) {
                    $('#attribute_value').append('<option value="'+ key +'">'+ value +'</option>');
                });
            }
        });
    });
</script>
@endsection
