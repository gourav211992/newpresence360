@extends('layouts.app')

@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header pocreate-sticky">
            <div class="row">
                <div class="content-header-left col-md-6 mb-2">
                    <h2 class="content-header-title float-start mb-0">Exchange Rates</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Exchange Rates</li>
                        </ol>
                    </div>
                </div>
                <div class="content-header-right text-end col-md-6">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exchangeRateModal" id="addExchangeRateBtn">
                        <i data-feather="plus-circle"></i> Add New
                    </button>
                </div>
            </div>
        </div>
        <div class="content-body">
            <section id="basic-datatable">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="datatables-basic table">
                                        <thead>
                                            <tr>
                                                <th>S.NO.</th>
                                                <th>From</th>
                                                <th>To</th>
                                                <th>Exchange Rate</th>
                                                <th>Effective From</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                       
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="exchangeRateModal" tabindex="-1" aria-labelledby="exchangeRateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-4 mx-50 pb-2">
                <h1 class="text-center mb-1" id="exchangeRateModalLabel">Add Exchange Rate</h1>
                <p class="text-center">Enter the details below.</p>

                <form action="{{ route('exchange-rates.store') }}" class="ajax-input-form" method="POST" id="exchangeRateForm">
                    @csrf
                    <input type="hidden" name="_method" id="method" value="POST">
                    <input type="hidden" name="id" id="rateId" value="">

                    <div class="row mt-2">
                        <div class="col-md-12 mb-1">
                            <label for="fromCurrency" class="form-label">From Currency <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="fromCurrency" name="from_currency_id" required>
                                <option value="">Select Currency</option>
                                @foreach($fromCurrencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-1">
                            <label for="toCurrency" class="form-label">To Currency <span class="text-danger">*</span></label>
                            <select class="form-select select2" id="toCurrency" name="upto_currency_id">
                                <option value="">Select Currency</option>
                                @foreach($toCurrencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-1">
                            <label for="exchangeRate" class="form-label">Exchange Rate <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="exchangeRate" name="exchange_rate" >
                        </div>
                        <div class="col-md-12 mb-1">
                            <label for="effectiveFrom" class="form-label">Effective From <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="effectiveFrom" name="from_date">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn" form="exchangeRateForm">Submit</button>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const today = new Date().toISOString().split('T')[0];
    $('#effectiveFrom').attr('min', today); 
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        const fromId = $(this).data('from-id');
        const toId = $(this).data('to-id');
        const rate = $(this).data('rate');
        const date = $(this).data('date');
        $('#rateId').val(id);
        $('#exchangeRate').val(rate);
        $('#effectiveFrom').val(date);
        $('#fromCurrency').val(fromId); 
        $('#toCurrency').val(toId); 
        $('#exchangeRateModalLabel').text('Edit Exchange Rate');
        $('#submitBtn').text('Update Exchange Rate');
        $('#exchangeRateForm').attr('action', '{{ route('exchange-rates.update', '') }}/' + id);
        $('#method').val('PUT');
        $('#exchangeRateModal').modal('show');
    });
    $('#addExchangeRateBtn').on('click', function() {
        $('#rateId').val('');
        $('#exchangeRate').val('');
        $('#effectiveFrom').val('');
        $('#fromCurrency').val('');
        $('#toCurrency').val('');
        $('#exchangeRateModalLabel').text('Add Exchange Rate');
        $('#submitBtn').text('Add Exchange Rate');
        $('#exchangeRateForm').attr('action', '{{ route('exchange-rates.store') }}');
        $('#method').val('POST');
    });
});
</script>

<script>
   $(document).ready(function() {
    var dt_basic_table = $('.datatables-basic');
    function renderData(data) {
        return data ? data : 'N/A'; 
    }
    if (dt_basic_table.length) {
        var dt_exchange_rate = dt_basic_table.DataTable({ 
            processing: true,
            serverSide: false,
            ajax: '{{ route('exchange-rates.index') }}',

            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'fromCurrency', render: renderData},
                { data: 'uptoCurrency', render: renderData },
                { data: 'exchange_rate', render: renderData },
                { data: 'from_date', render: renderData },
                { data: 'actions', orderable: false, searchable: false }
            ],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
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
                                title: 'Exchange Rates',
                                exportOptions: { columns: [0, 1, 2,3] }
                            },
                            {
                                extend: 'csv',
                                text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
                                className: 'dropdown-item',
                                title: 'Exchange Rates',
                                exportOptions: {columns: [0, 1, 2,3]}
                            },
                            {
                                extend: 'excel',
                                text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
                                className: 'dropdown-item',
                                title: 'Exchange Rates',
                                exportOptions: {columns: [0, 1, 2,3]}
                            },
                            {
                                extend: 'pdf',
                                text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + 'Pdf',
                                className: 'dropdown-item',
                                title: 'Exchange Rates',
                                exportOptions: { columns: [0, 1, 2,3] }
                            },
                            {
                                extend: 'copy',
                                text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + 'Copy',
                                className: 'dropdown-item',
                                title: 'Exchange Rates',
                                exportOptions: { columns: [0, 1, 2,3]}
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
                drawCallback: function() {
                    feather.replace();
                },
                language: {
                    paginate: {
                        previous: '&nbsp;',
                        next: '&nbsp;'
                    }
                },
                search: { 
                    caseInsensitive: true 
                }
        });
    }
});

</script>

@endsection
