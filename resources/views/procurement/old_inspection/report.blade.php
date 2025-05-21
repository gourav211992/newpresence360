@extends('app-new')
@push('head-script')
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/tables/datatable/responsive.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/components.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/extensions/toastr.min.css">
    <link rel="stylesheet" href="/assets/plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="/assets/css/daterangepicker.css">
    <!-- BEGIN: Page CSS-->
    <link rel="stylesheet" type="text/css" href="/app-assets/css/core/menu/menu-types/vertical-menu.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/css/plugins/forms/pickers/form-flat-pickr.css">
    <link rel="stylesheet" type="text/css" href="/app-assets/vendors/css/forms/select/select2.min.css">
    <link rel="stylesheet" type="text/css" href="../../../assets/css/jquery-ui.min.css">

    <link rel="stylesheet" href="/assets/css/daterangepicker.css">

    <!-- END: Page CSS-->
@endpush

@push('content-header')
    <div class="row breadcrumbs-top">
        <div class="col-12">
            <h2 class="content-header-title float-start mb-0">Quotation Report</h2>
            <div class="breadcrumb-wrapper">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('') }}/{{ $alias }}/home">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Quotation Report
                    </li>
                </ol>
            </div>
        </div>
    </div>
@endpush
@section('content')
    <!-- Column Search -->
    <section id="column-search-datatable">
        <!-- Filters -->
        <form method="get" action="" id="filter-form">
            <input type="hidden" name="organization_id" value="{{ $organization->id }}">
            <input type="hidden" name="showModel" id="showModel"
                value="@if (!empty($_GET['showModal'])) {{ $_GET['showModal'] }} @endif">
            <div class="newcontent-header  content-header row" >
                <div class="content-header-left newcontent-header-left col-md-10 col-12 mt-1">
                    <div class="row" style="padding: 10px">
                        @foreach ($reportTemplates as $template)
                            <div class="col-md-4">
                                <div class="card ">
                                    <a onclick="templateFilter({{ $template->id }})">
                                        <div
                                            class="card-body newcard-body p-1 text-center rounded @if ($template->id == $request->template_id) bg-light-danger @endif">
                                            <b>{{ $template->name }}</b>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                        {{-- <div class="col-md-3">
                            <button type="button" id="add" class="btn bg-light-primary" data-bs-toggle="modal"
                                data-bs-target="#templateModal"><i data-feather="plus"></i></button>
                        </div> --}}
                    </div>
                </div>
                <div class="content-header-right text-md-end col-md-2 col-12 d-md-block d-none mt-1">
                    <div class="breadcrumb-right d-flex justify-content-end">
                        <div class="me-2">
                            <button type="button" id="add" class="btn bg-light-primary" data-bs-toggle="modal"
                                data-bs-target="#templateModal"><i data-feather="plus"></i></button>
                        </div>
                        <div class="dropdown">
                            <button class="btn-icon btn btn-primary btn-round btn-sm dropdown-toggle" type="button"
                                data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd" aria-controls="offcanvasEnd"
                                type="button"><i data-feather='filter'></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="offcanvas-end-example">
                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
                    <div class="offcanvas-header">
                        <h5 id="offcanvasEndLabel" class="offcanvas-title">Filter</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                            aria-label="Close"></button>
                    </div>
                    <input type="hidden" id="status" name="status" value="0">
                    <input type="hidden" id="template_id" name="template_id" value="{{ $request->template_id }}">
                    <div class="offcanvas-body my-auto mx-0 flex-grow-0">

                        <div class="col-sm-12 mt-1">
                            <div class="form-label-group">
                                <label class="required">{{ trans('master/master.date_range') }}</label>
                                <input type="text" id="date_range" name="date_range" class="form-control daterangebg"
                                    value="{{ $request->date_range }}">
                                {!! $errors->first('date_range', '<label class="control-label" for="date_range">:message</label>') !!}
                            </div>
                        </div>

                        <div class="col-sm-12 mt-1">
                            <div class="form-label-group">
                                <label class="form-label">Customer</label>
                                <select class="form-control select2" name="customer_id" id="customer_id">
                                    <option value="">Select Customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" data-quotations="{{ $customer->quotations }}"
                                            @if ($request->customer_id == $customer->id) selected @endif>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-sm-12 mt-1" id="quotation_div" style="display:none">
                            <div class="form-label-group">
                                <label class="form-label">Quotation</label>
                                <select class="form-control select2" name="quotation_id" id="quotation_id"
                                    class="form-control">
                                    <option value="">Select Quotation</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit"
                            class="btn btn-primary mt-2 mb-1 d-grid w-100 d-flex justify-content-center"
                            data-bs-dismiss="offcanvas" aria-label="Close">
                            <i data-feather='search' class="me-2"></i>Search
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="row mt-2">
            <div class="col-12">
                <div class="card row reportcard">
                    @if ($request->template_id)
                        <div class="card-datatable  table-responsive">
                            <table class="dt-column-search table table-responsive attendance-reg-table "
                                id="organization">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        @foreach ($finalReportHeaders as $sequence)
                                            @if ($sequence['type'] == 'headers')
                                                @php
                                                    $val = explode('...', $sequence['value']);
                                                    $headersAlias = '';
                                                    if (isset($sequence['alias'])) {
                                                        $headersAlias = $sequence['alias'];
                                                    }
                                                    
                                                    if ($headersAlias) {
                                                        $val = $headersAlias;
                                                    } else {
                                                        if (isset($val[1])) {
                                                            $val = $val[1];
                                                        } else {
                                                            $val = $val[0];
                                                        }
                                                    }
                                                    
                                                @endphp
                                                <th>{{ $val }}</th>
                                            @else
                                                @foreach ($monthData as $m)
                                                    @php
                                                        $val = explode('...', $sequence['value']);
                                                        $componentAlias = '';
                                                        if (isset($sequence['alias'])) {
                                                            $componentAlias = $sequence['alias'];
                                                        }
                                                        if ($componentAlias) {
                                                            $val = $componentAlias;
                                                        } else {
                                                            if (isset($val[1])) {
                                                                $val = $val[1];
                                                            } else {
                                                                $val = $val[0];
                                                            }
                                                        }
                                                        
                                                    @endphp
                                                    @if ($sequence['type'] == 'components')
                                                        @if ($templateData->rate == '1')
                                                            <th class="bg-datehead">{{ $val }} (Rate)</th>
                                                        @endif
                                                    @endif
                                                    <th class="bg-datehead">{{ $val }}
                                                        @if ($sequence['type'] == 'components')
                                                            @if ($templateData->arrear == '1')
                                                    <th class="bg-datehead">{{ $val }} (Arrear)</th>
                                                @endif
                                            @endif
                                            </th>
                                        @endforeach
                    @endif
                    @endforeach
                    </tr>
                    {{-- @if (count($dataList))
                                    <tr>
                                        <th></th>
                                        @foreach ($finalReportHeaders as $sequence)
                                            @if ($sequence['type'] == 'headers')
                                                <th></th>
                                            @else
                                                @foreach ($monthData as $m)
                                                    <th class="bg-datehead">{{ $m }}
                                                    </th>

                                                    @if ($sequence['type'] == 'components')
                                                        @if ($templateData->rate == '1')
                                                            <th class="bg-datehead">{{ $m }}</th>
                                                        @endif
                                                    @endif
                                                    @if ($sequence['type'] == 'components')
                                                        @if ($templateData->arrear == '1')
                                                            <th class="bg-datehead">{{ $m }}</th>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif --}}
                    </thead>
                    <tbody>
                        @foreach ($dataList as $row)
                            @php
                                
                                $data = $row['record'];
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                @foreach ($finalReportHeaders as $sequence)
                                    @if ($sequence['type'] == 'headers')
                                        @php
                                            $val = explode('...', $sequence['value']);
                                            $val = $val[0];
                                        @endphp
                                        <td>{{ @$data->{$val} }}</td>
                                    @else
                                        @foreach ($monthData as $m)
                                            @php
                                                $r = $row['items'][$m];
                                            @endphp
                                            @php
                                                $val = explode('...', $sequence['value']);
                                                $val = $val[0];
                                            @endphp
                                            @if ($sequence['type'] == 'summary')
                                                <td class="bg-datehead">{{ @$r->{$val} }}</td>
                                            @elseif($sequence['type'] == 'components')
                                                @php
                                                    $val1 = $val;
                                                    $val = '';
                                                    $rate = $arrear = 0;
                                                    if (isset($r->slipLogs)) {
                                                        foreach ($r->slipLogs as $logs) {
                                                            if ($logs->type_id == $val1) {
                                                                $val = round($logs->calculate_salary, 2);
                                                                $rate = round($logs->actual_salary, 2);
                                                    
                                                                $arrear = round($logs->arrear_salary, 2);
                                                    
                                                                if (isset($componentTotal[$m][$val1])) {
                                                                    $componentTotal[$m][$val1] += $val;
                                                                } else {
                                                                    $componentTotal[$m][$val1] = $val;
                                                                }
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                @if ($sequence['type'] == 'components')
                                                    @if ($templateData->rate == '1')
                                                        <th class="bg-datehead">{{ $rate }}</th>
                                                    @endif
                                                @endif
                                                <td class="bg-datehead">
                                                    {{ $val }}
                                                </td>
                                                @if ($sequence['type'] == 'components')
                                                    @if ($templateData->arrear == '1')
                                                        <th class="bg-datehead">{{ $arrear }}</th>
                                                    @endif
                                                @endif
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                    {{-- <tfoot>
                                <tr>
                                    <th>Total:</th>
                                    @foreach ($finalReportHeaders as $sequence)
                                            @if ($sequence['type'] == 'headers')
                                            <th></th>
                                            @else
                                                @foreach ($monthData as $m)
                                                        @php 
                                                            $val = explode('...', $sequence['value']);
                                                            $val = $val[0];
                                                        @endphp
                                                        @if ($sequence['type'] == 'summary')
                                                            <th class="bg-datehead"></th>
                                                        @elseif($sequence['type'] == 'components')
                                                            @if ($sequence['type'] == 'components')
                                                                @if ($templateData->rate == '1')
                                                                    <th class="bg-datehead"></th>
                                                                @endif
                                                            @endif
                                                            <th class="bg-datehead">{{ @$componentTotal[$m][$val] }}</th>
                                                            @if ($sequence['type'] == 'components')
                                                                @if ($templateData->arrear == '1')
                                                                    <th class="bg-datehead"></th>
                                                                @endif
                                                            @endif
                                                        @endif
                                                @endforeach
                                        @endif
                                    @endforeach                                     
                                </tr>
                            </tfoot> --}}
                    </table>
                </div>
                @endif
            </div>
        </div>
        </div>
    </section>
    <!-- Earning Config Modal -->
    <div class="modal fade text-start filterpopuplabel" id="templateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="templateModal17">Generate
                            Report</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="template_form" action="/{{ $alias }}/report-template/create-template"
                        onsubmit="return false">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="template_id_temp" id="template_id_temp" value="">
                        <input type="hidden" name="type" value="quotation">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="mb-1">
                                    <label class="form-label" for="name">{{ trans('master/master.report_name') }}
                                        <span class="mendatory-field text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="name"
                                        placeholder="Enter Report Name" />
                                </div>
                            </div>
                            <div class="col-md-7 mt-2">
                                <div class="form-check form-check-success mb-1">
                                    <input type="checkbox" class="form-check-input" id="selectAllHeader"
                                        onchange="selectAll('Header')">
                                    <label class="form-check-label fw-bolder text-dark"
                                        for="selectAllHeader">{{ trans('master/master.all_component') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="nav-vertical">
                                    <ul class="nav nav-tabs nav-left flex-column" role="tablist">
                                        @foreach ($reportHeaders as $key => $reportHeader)
                                            <li class="nav-item">
                                                <a class="nav-link @if ($loop->first) active @endif"
                                                    data-bs-toggle="tab" href="#{{ $reportHeader['header'][0] }}"
                                                    role="tab"><i
                                                        data-feather="users"></i>{{ $reportHeader['header'][1] }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="tab-content tablecomponentreport">
                                        @foreach ($reportHeaders as $key => $reportHeader)
                                            <div class="tab-pane @if ($loop->first) active @endif"
                                                id="{{ $reportHeader['header'][0] }}">
                                                <div class="compoenentboxreport">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-check form-check-primary">
                                                                <input type="checkbox"
                                                                    class="form-check-input HeaderCheck HeadingCheck"
                                                                    id="selectAll{{ $reportHeader['header'][0] }}"
                                                                    checked=""
                                                                    onchange="selectAll('{{ $reportHeader['header'][0] }}')">
                                                                <label class="form-check-label" for="colorCheck1">Select
                                                                    All Component</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row sortable">
                                                        @foreach ($reportHeader['components'] as $key => $val)
                                                            <div class="col-md-6">
                                                                <div class="form-check form-check-secondary">
                                                                    <input type="checkbox" name="headers[]"
                                                                        class="form-check-input  HeaderCheck {{ $reportHeader['header'][0] }}Check"
                                                                        id="{{ $reportHeader['header'][0] }}Headers{{ $key }}"
                                                                        value="{{ $key . '...' . $val }}"
                                                                        onchange="reverseSelect('{{ $reportHeader['header'][0] }}')">
                                                                    <label class="form-check-label"
                                                                        for="{{ $reportHeader['header'][0] }}Headers{{ $key }}">{{ $val }}</label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                </div>

                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal" aria-label="Close">
                        {{ trans('common/common.discard') }}
                    </button>
                    <button type="button" class="btn btn-primary data-submit mr-1"
                        id="add_template">{{ trans('common/common.submit') }}</button>
                </div>
            </div>
        </div>

    </div>


    {{-- Sequence modal --}}
    <div class="modal fade text-start filterpopuplabel" id="sequenceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="sequence-template-form" action="/{{ $alias }}/report-template/sequence-template"
                method="POST" onsubmit="return false">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="update-sequence-modal">
                                Update Sequence</h4>
                            <button type="button" onclick="getTemplate({{ $request->template_id }})"
                                class=" btn btn-outline-primary me-2"><i data-feather="edit-3"></i>Edit</button>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="seq_template_id_temp" id="seq_template_id_temp" value="">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label" for="name">{{ trans('master/master.report_name') }}
                                        <span class="mendatory-field text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" id="report-name"
                                        placeholder="Enter Report Name" />
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="nav-vertical">
                                    <div class="tab-content tablecomponentreport">
                                        <div class="tab-pane active">
                                            <div class="compoenentboxreport component-custom">
                                                <div class="row sortable new-header" id="set-data">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                            aria-label="Close">
                            {{ trans('common/common.discard') }}
                        </button>
                        <button type="button" onclick="submitSequenceForm()"
                            class="btn btn-primary data-submit mr-1">{{ trans('common/common.submit') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Close earning config modal -->
    <!--/ Column Search -->

    <style>
        /* .newcard-body {
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        } */

        /* .newcontent-header {
            z-index: 1 !important;
            height: 99px;
            padding: 3px 10px 10px 10px !important;
            margin-top: 78px;

        } */

        /* .content-header-left {
            overflow-y: scroll;
            overflow-x: hidden;
            height: 70px;
            margin-bottom: 0.25rem!important;

        } */

        /* .card {
            z-index: 0 !important;
        }

        .reportcard {
            margin-top: 30px;
        } */
    </style>
@endsection
@push('footer-script')
    <!-- BEGIN: Page Vendor JS-->
    <script src="/app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/dataTables.bootstrap5.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/responsive.bootstrap5.js"></script>
    <script src="/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js"></script>
    <script src="/app-assets/js/scripts/ui/ui-feather.js"></script>
    <script src="/assets/js/moment.min.js"></script>
    <script src="/assets/js/daterangepicker.js"></script>

    <script src="/assets/plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="/assets/plugins/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>

    <script src="/app-assets/vendors/js/tables/datatable/datatables.checkboxes.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/jszip.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>
    <script src="/app-assets/vendors/js/tables/datatable/dataTables.rowGroup.min.js"></script>
    <!-- END: Page Vendor JS-->
    <script src="/app-assets/vendors/js/extensions/toastr.min.js"></script>


    <!-- BEGIN: Page JS-->
    <script src="/app-assets/js/scripts/tables/table-datatables-basic.js"></script>
    <script src="/app-assets/js/scripts/tables/table-datatables-advanced.js"></script>
    <script src="/app-assets/vendors/js/forms/select/select2.full.min.js"></script>
    <script src="/../assets/js/jquery-ui.min.js"></script>
    <!-- END: Page JS-->

    <script src="/assets/plugins/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
    <script src="/assets/js/daterangepicker.js"></script>



    <script>
        function callDt() {
            var dt_filter_table = $('.dt-column-search');

            if ($('body').attr('data-framework') === 'laravel') {
                assetPath = $('body').attr('data-asset-path');
            }

            if (dt_filter_table.length) {

                var dt_filter = dt_filter_table.DataTable({

                    dom: '<"card-header border-bottom p-1"<"head-label"><"dt-action-buttons text-end"B>><"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    orderCellsTop: true,
                    language: {
                        paginate: {
                            previous: '&nbsp;',
                            next: '&nbsp;'
                        }
                    },
                    buttons: [
                        @if (isset($request->template_id))
                            {
                                text: feather.icons['edit-3'].toSvg({
                                    class: 'font-medium-4'
                                }),
                                className: 'btn btn-outline-warning me-2',
                                attr: {
                                    'type': "button",
                                    'onclick': "getSequence({{ $request->template_id }})",
                                    'title': 'Edit Sequence'
                                },
                                init: function(api, node, config) {
                                    $(node).removeClass('btn-secondary');
                                }
                            }, {
                                text: feather.icons['trash'].toSvg({
                                    class: 'font-medium-4'
                                }),
                                className: 'btn btn-outline-danger me-2',
                                attr: {
                                    'type': "button",
                                    'onclick': "deleteTemplate({{ $request->template_id }})",
                                    'title': 'Delete Template'
                                },
                                init: function(api, node, config) {
                                    $(node).removeClass('btn-secondary');
                                }
                            },
                            //  {
                            //     text: feather.icons['download'].toSvg({
                            //         class: 'font-small-4 me-50'
                            //     }),
                            //     className: 'btn btn-outline-danger me-2',
                            //     attr: {
                            //         'type': "button",
                            //         'onclick': "downloadTextFile()",
                            //         'title': 'Dowload Txt'
                            //     },
                            //     init: function(api, node, config) {
                            //         $(node).removeClass('btn-secondary');
                            //     }
                            // },
                        @endif {
                            extend: 'collection',
                            className: 'btn btn-outline-secondary dropdown-toggle me-2',
                            text: feather.icons['share'].toSvg({
                                class: 'font-small-4 me-50'
                            }) + "{{ trans('manager/manager.export') }}",
                            buttons: [
                                // {
                                //     extend: 'print',
                                //     filename: '{{ $organization->name }}, {{ Auth::user()->name }} - {{ date('d-m-Y H-i-s') }}',
                                //     title: '{{ $organization->name }} - {{ date('d-m-Y H-i-s') }}',
                                //     text: feather.icons['printer'].toSvg({ class: 'font-small-4 me-50' }) + 'Print',
                                //     className: 'dropdown-item',
                                //     exportOptions: { columns: Array.from(Array($("#organization thead tr:nth-child(2) th").length).keys()) },
                                // },
                                // {
                                //     extend: 'csv',
                                //     filename: '{{ $organization->name }}, {{ Auth::user()->name }} - {{ date('d-m-Y H-i-s') }}',
                                //     title: '{{ $organization->name }} - {{ date('d-m-Y H-i-s') }}',
                                //     text: feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) + 'Csv',
                                //     className: 'dropdown-item',
                                //     exportOptions: { columns: Array.from(Array($("#organization thead tr:nth-child(2) th").length).keys()) },
                                // },
                                {
                                    extend: 'excel',
                                    filename: '{{ $organization->name }}, {{ Auth::user()->name }} - {{ date('d-m-Y H-i-s') }}',
                                    title: '{{ $organization->name }} - {{ date('d-m-Y H-i-s') }}',
                                    text: feather.icons['file'].toSvg({
                                        class: 'font-small-4 me-50'
                                    }) + 'Excel',
                                    className: 'dropdown-item',
                                    // exportOptions: { columns: Array.from(Array($("#organization thead tr:nth-child(2) th").length).keys()) },


                                },
                                // {
                                //     extend: 'pdf',
                                //     filename: '{{ $organization->name }} - {{ date('d-m-Y H-i-s') }}',
                                //     title: '{{ $organization->name }} - {{ date('d-m-Y H-i-s') }}',
                                //     orientation:'landscape',
                                //     text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + 'Pdf',
                                //     className: 'dropdown-item',
                                //     exportOptions: { columns: Array.from(Array($("#organization thead tr:nth-child(2) th ").length).keys()) },
                                //         customize: function(doc) {
                                //             doc['footer']=(function() {
                                //                 return {
                                //                     columns: [
                                //                         {
                                //                             alignment: 'center',
                                //                             text: ['Downloaded by: ', { text: '{{ Auth::user()->name }}' }, ' on {{ date('d-m-Y') }} at {{ date('g:i A') }}']
                                //                         }
                                //                     ],
                                //                     margin: 10
                                //                 }
                                //             }); 
                                //         },
                                //     },
                                // {
                                //     extend: 'copy',
                                //     filename: '{{ $organization->name }}, {{ Auth::user()->name }} - {{ date('d-m-Y H-i-s') }}',
                                //     title: '{{ $organization->name }} - {{ date('d-m-Y H-i-s') }}',
                                //     text: feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + 'Copy',
                                //     className: 'dropdown-item',
                                //     exportOptions: { columns: Array.from(Array($("#organization thead tr:nth-child(2) th").length).keys()) }
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
                        },
                    ],

                });
            }

        };

        $(window).on('load', function() {
            $('.select2').select2();
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        });

        function submitForm() {

            var form = $('#template_form');
            var actionUrl = form.attr('action');

            $.ajax({
                type: "POST",
                url: actionUrl,
                data: form.serialize(),
                success: function(data) {
                    var data = data.data;
                    var sequenceAr = data.sequence;
                    $('#templateModal').modal('hide');
                    $('#sequenceModal').modal('show');
                    $('#report-name').val(data.name);
                    $('#seq_template_id_temp').val(data.id);
                    var tHtml = '';
                    $.each(sequenceAr, function(key, value) {
                        value.alias = '';
                        var seqString = value.value;
                        var seqVal = seqString.split('...');

                        tHtml +=
                            '<div class="col-md-12"><div class="form-check form-check-secondary  sequenceRow" data-value=';
                        tHtml += "'" + JSON.stringify(value) + "'>";
                        tHtml += '<div class="row"><div class="col-md-6">';
                        tHtml += '<label class="form-check-label">' + seqVal[1] + '</label>';
                        tHtml += '</div><div class="col-md-6">';
                        tHtml +=
                            '<input type="text" class="form-control custom-component-alias" name="custom_name[]" placeholder="Custom Header Title"/>';
                        tHtml += '</div></div>';
                        tHtml += '</div></div>';


                    });
                    $('#set-data').html(tHtml);
                }
            });

        }

        function submitSequenceForm() {

            var form = $('#sequence-template-form');
            var actionUrl = form.attr('action');
            var dataSet = [];
            $(".sequenceRow").each(function(e, row) {
                dataSet.push(JSON.parse($(row).attr("data-value")));
            });

            var tempId = $('#seq_template_id_temp').val();

            $.ajax({
                type: "POST",
                url: actionUrl,
                data: {
                    'id': tempId,
                    'sequence': dataSet,
                    'rate': $('input[name="rate"]').is(":checked") ? '1' : '0',
                    'arrear': $('input[name="arrear"]').is(":checked") ? '1' : '0',
                },
                success: function(res) {
                    console.log(res);
                    if (res.status == true) {
                        Swal.fire({
                            title: "Success",
                            text: "Successfully Created",
                            icon: "success",
                            confirmButtonColor: "#00c292",
                            confirmButtonText: "Ok",
                        });
                        setTimeout(function() {
                            location.reload();
                        }, 2200);
                    }
                }
            });
        }

        $(document).ready(function() {

            if ($('#showModel').val() == 1) {
                $('#templateModal').modal('show');
            }
            callDt();

            $('#customer_id').trigger('change');
            var status = "{{ $request->status }}";
            if (status == 1) {
                $('#filter-form').submit();
            }

            $('#date_range').daterangepicker({
                timePicker: false,
                autoUpdateInput: false,
                locale: {
                    format: 'MM/DD/YYYY'
                }
            });

            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format(
                    'MM/DD/YYYY'));
            });

            $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        });

        $('#add').click(function() {
            $("#template_id_temp").val('');
            $("#name").val('');
            $('#selectAllHeader').prop('checked', false);
            $('.HeaderCheck').prop('checked', false);
        });

        $('#add_template').click(function(e) {

            var templateId = $('#template_id_temp').val();
            var name = $('#name').val();
            var headers = $('input[name="headers[]"]:checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            var components = $('input[name="components[]"]:checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            var salary_slip_components = $('input[name="salary_slip_components[]"]:checkbox:checked').map(
                function() {
                    return $(this).val();
                }).get();
            $.ajax({
                url: '/{{ $alias }}/report-template/validate-template',
                type: 'POST',
                data: {
                    '_token': "{{ csrf_token() }}",
                    '_method': 'POST',
                    'name': name,
                    'templateId': templateId,
                    'headers': headers,
                    'components': components,
                    'salary_slip_components': salary_slip_components,
                },
                success: function(response) {
                    if (response == '') {
                        submitForm();
                    } else {
                        Swal.fire("Error!", response[0], "error");
                    }
                }
            });
        });

        function getSequence(template_id) {
            $.ajax({
                url: '/{{ $alias }}/report-template/get-template',
                type: 'POST',
                data: {
                    '_token': "{{ csrf_token() }}",
                    '_method': 'POST',
                    'template_id': template_id,

                },
                success: function(response) {
                    var sequenceAr = response.sequence;
                    $('#report-name').val(response.name);
                    $('#seq_template_id_temp').val(response.id);
                    if (response.rate == 1) {
                        $('input[name="rate"]').prop('checked', true);

                    }
                    if (response.arrear == 1) {
                        $('input[name="arrear"]').prop('checked', true);

                    }

                    var tHtml = '';
                    $.each(sequenceAr, function(key, value) {
                        var seqString = value.value;
                        var seqVal = seqString.split('...');
                        if (value.alias == null) {
                            alias = '';
                            value.alias = '';
                        } else {

                            alias = value.alias;

                        }

                        tHtml +=
                            '<div class="col-md-12"><div class="form-check form-check-secondary  sequenceRow" data-value=';
                        tHtml += "'" + JSON.stringify(value) + "'>";
                        tHtml += '<div class="row"><div class="col-md-6">';
                        tHtml += '<label class="form-check-label">' + seqVal[1] + '</label>';
                        tHtml += '</div><div class="col-md-6">';
                        tHtml +=
                            '<input type="text" class="form-control custom-component-alias" name="custom_name[]" value="' +
                            alias + '" placeholder="Custom Header Title" />';
                        tHtml += '</div></div>';
                        tHtml += '</div></div>';
                    });
                    $('#set-data').html(tHtml);
                    $('#sequenceModal').modal('show');
                }
            });
        }

        function setCustomAlisa(event) {
            var data = event.value;
            $oldData = $(event.parents)('.sequenceRow').attr('value');
            console.log($oldData);
        }

        $('body').on('change', '.custom-component-alias', function() {
            const dataValue = JSON.parse($(this).closest('.sequenceRow').attr('data-value'));
            let alias = $(this).val();
            dataValue["alias"] = alias;
            neDataValue = JSON.stringify(dataValue);
            $(this).closest('.sequenceRow').attr('data-value', neDataValue);

        })



        function getTemplate(template_id) {
            $("#template_id_temp").val(template_id);
            $("#template_id_temp_2").val(template_id);
            $.ajax({
                url: '/{{ $alias }}/report-template/get-template',
                type: 'POST',
                data: {
                    '_token': "{{ csrf_token() }}",
                    '_method': 'POST',
                    'template_id': template_id,
                },
                success: function(response) {
                    var name = $('#name').val(response.name);
                    $('.HeadingCheck').prop('checked', false);

                    if (response.headers) {
                        response.headers.map(function(val) {
                            $('input[value="' + val + '"]:checkbox').prop('checked', true).trigger(
                                'change');
                        });
                    }
                    if (response.components) {
                        response.components.map(function(val) {
                            $('input[value="' + val + '"]:checkbox').prop('checked', true).trigger(
                                'change');
                        });
                    }
                    if (response.salary_slip_components) {
                        response.salary_slip_components.map(function(val) {
                            $('input[value="' + val + '"]:checkbox').prop('checked', true).trigger(
                                'change');
                        });
                    }
                    $('#sequenceModal').modal('hide');
                    $('#templateModal').modal('show');
                }
            });
        }

        function deleteTemplate(template_id) {
            Swal.fire({
                text: "Are you sure want to delete the template?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "@lang('messages.deleteConfirmation')",
                cancelButtonText: "@lang('messages.confirmNoArchive')",
            }).then(function(isConfirm) {
                window.location.href = '/{{ $alias }}/report-template/' + template_id + '/delete';
            });
        }

        // function downloadTextFile() {
        //     var href = $('#downloadTxt').attr('href');
        //     window.location.href = href;
        // }

        function selectAll(name) {
            if ($('#selectAll' + name).is(":checked")) {
                $('.' + name + 'Check').prop('checked', true);
            } else {
                $('.' + name + 'Check').prop('checked', false);
            }
        }

        function reverseSelect(name) {
            var boxes = $('.' + name + 'Check:checked');
            if ($('.' + name + 'Check').length == boxes.length) {
                $('#selectAll' + name).prop('checked', true);
            } else {
                $('#selectAll' + name).prop('checked', false);
            }
        }

        $(function() {
            $('body').on('change', '.HeaderCheck', function() {
                var boxes = $('.HeaderCheck:checked');
                if ($('.HeaderCheck').length == boxes.length) {
                    $('#selectAllHeader').prop('checked', true);
                } else {
                    $('#selectAllHeader').prop('checked', false);
                }
            });
        });

        function templateFilter(templateId) {
            $('#template_id').val(templateId);
            $('#filter-form').submit();
        }

        $(function() {
            $(".sortable").sortable({
                cursor: "move"
            })
        });

        $('#customer_id').change(function() {
            var customer_id = $(this).val();

            if (customer_id) {
                var quotations = $(this).find(':selected').data('quotations');
                var html = '<option value="">Select</option>';
                quotations.forEach(quotation => {
                    var selected = '';
                    if (quotation.id == '{{ $request->quotation_id }}') {
                        selected = 'selected';
                    }
                    html += '<option value="' + quotation.id + '" ' + selected + '>' + quotation
                        .quotation_number + '</option>';
                });

                if (quotations.length) {
                    $('#quotation_id').html(html);
                    $('#quotation_div').show();
                } else {
                    $('#quotation_id').html(html);
                    $('#quotation_div').hide();
                }

            } else {
                $('#quotation_id').html('');
                $('#quotation_div').hide();
            }
        });
    </script>
@endpush
