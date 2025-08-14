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
                            <h2 class="content-header-title float-start mb-0">Material Request</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="{{ url('/') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">Material Request List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal">
                            <i data-feather="filter"></i> Filter
                        </button>
                        <a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{ route('material-request.create') }}">
                            <i data-feather="plus-circle"></i> Create</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="table-responsive">
                                    <table class="datatables-basic table myrequesttablecbox tableistlastcolumnfixed">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Mr No.</th>
                                                <th>Mr Date</th>
                                                <th>Item</th>
                                                <th>Item Value</th>
                                                <th>Discount</th>
                                                <th>Taxable Value</th>
                                                <th>Expenses</th>
                                                <th>Total Amt</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($records as $val)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="fw-bolder text-dark">{{ @$val->document_number }}</td>
                                                    <td>{{ @$val->document_date }}</td>
                                                    <td>
                                                        <span class="badge rounded-pill badge-light-secondary badgeborder-radius">
                                                            {{count(@$val->items)}}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        {{ number_format(@$val->total_item_amount, 2) }}
                                                    </td>
                                                    <td>
                                                        {{ number_format(@$val->total_discount, 2) }}
                                                    </td>
                                                    <td>
                                                        {{ number_format((@$val->total_item_amount - @$val->total_discount), 2) }}
                                                    </td>
                                                    <td>
                                                        {{ number_format(@$val->expense_amount, 2) }}
                                                    </td>
                                                    <td>
                                                        {{ number_format(((@$val->total_item_amount - @$val->total_discount) + (@$val->expense_amount)),2) }}
                                                    </td>
                                                    <td>
                                                        <span class="badge rounded-pill
                                                        badge-light-{{App\Helpers\ConstantHelper::DOCUMENT_STATUS_CSS_WO_TEXT[@$val->document_status]}}
                                                        badgeborder-radius">
                                                            {{ucfirst(@$val->document_status)}}
                                                        </span>
                                                    </td>
                                                    <td class="tableactionnew">
                                                        <div class="dropdown">
                                                            <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                                                <i data-feather="more-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item" href="{{route('material-request.edit', $val->id)}}">
                                                                    <i data-feather="edit-3" class="me-50"></i>
                                                                    <span>Edit</span>
                                                                </a>
                                                            </div>
                                                        </div>
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
@endsection
