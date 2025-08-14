@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-9 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <div class="breadcrumb-wrapper">
                                <h2 class="content-header-title float-start mb-0">{{ $customer->company_name }}</h2>
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('crm.home') }}">Home</a></li>
                                    <li class="breadcrumb-item active">Order List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-3 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                data-feather="arrow-left-circle"></i> Back</button>
                        <a href="{{ route('customers.order-detail.csv', ['customerCode' => $customer->customer_code]) }}{{ Request::getQueryString() ? '?' . Request::getQueryString() : '' }}"
                            class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="file-text"></i> Export to CSV</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                @include('crm.partials.table-header', [
                                    'searchPlaceholder' => 'Search by order number.',
                                ])
                                <div class="table-responsive">
                                    <table class="table myrequesttablecbox loanapplicationlist">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Order Date</th>
                                                <th>Order No.</th>
                                                <th>Store Type</th>
                                                <th>Item Code</th>
                                                <th>Item Name</th>
                                                <th>Uom</th>
                                                <th>Delivery Date</th>
                                                <th>Order Value</th>
                                                <th>Order Qty</th>
                                                <th>Deliver Qty</th>
                                                <th>Balance Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($orderItems as $key => $orderItem)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td class="fw-bolder text-dark">
                                                        {{ $orderItem->order_date ? App\Helpers\GeneralHelper::dateFormat($orderItem->order_date) : '' }}
                                                    </td>
                                                    <td>{{ $orderItem->order_number }}</td>
                                                    <td>{{ $orderItem->store_type }}</td>
                                                    <td>{{ $orderItem->item_code }}</td>
                                                    <td>{{ $orderItem->item_name }}</td>
                                                    <td>{{ $orderItem->uom }}</td>
                                                    <td>{{ $orderItem->delivery_date ? App\Helpers\GeneralHelper::dateFormat($orderItem->delivery_date) : '-' }}
                                                    </td>
                                                    <td>{{ $orderItem->total_order_value ? @$currencyMaster->symbol . '' . $orderItem->total_order_value : '-' }}
                                                    </td>
                                                    <td>{{ $orderItem->order_quantity }}</td>
                                                    <td>{{ $orderItem->delivered_quantity }}</td>
                                                    <td>{{ $orderItem->order_quantity - $orderItem->delivered_quantity }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-danger text-center" colspan="12">No record(s) found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end mx-1 mt-50">
                                    {{-- Pagination --}}
                                    {{ $orderItems->appends(request()->input())->links('crm.partials.pagination') }}
                                    {{-- Pagination End --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection
