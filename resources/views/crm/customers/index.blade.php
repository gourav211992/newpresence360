@extends('layouts.app')
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Orders</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('crm.home') }}">Home</a></li>
                                    <li class="breadcrumb-item active">Customer List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                data-feather="arrow-left-circle"></i> Back</button>
                        <a href="{{ route('customers.order-csv') }}{{ Request::getQueryString() ? '?' . Request::getQueryString() : '' }}"
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
                                    'searchPlaceholder' => 'Search by customer code, customer name.',
                                ])
                                <div class="table-responsive">
                                    <table class="table myrequesttablecbox loanapplicationlist">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Code</th>
                                                <th>Customer Name</th>
                                                <th>ORder Count</th>
                                                <th>Order Value</th>
                                                <th>Location</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($customers as $key => $customer)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td class="fw-bolder text-dark">{{ $customer->customer_code }}</td>
                                                    <td><a
                                                            href="{{ route('customers.view', ['customerCode' => $customer->customer_code]) }}">{{ $customer->company_name }}</a>
                                                    </td>
                                                    <td>{{ $customer->distinct_order_count ? $customer->distinct_order_count : 0 }}
                                                    </td>
                                                    <td>{{ $customer->total_order_value_sum ? App\Helpers\Helper::currencyFormat($customer->total_order_value_sum, 'display') : 0 }}
                                                    </td>
                                                    <td>{{ $customer->full_address }}</td>
                                                    <td>
                                                        <a
                                                            href="{{ route('customers.orders', ['customerCode' => $customer->customer_code]) }}{{ !empty(request()->except(['search', 'length', 'auth_type', 'db_name'])) ? '?' . http_build_query(request()->except(['search', 'length', 'auth_type', 'db_name'])) : '' }}">
                                                            <i data-feather="eye" class="me-50"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-danger text-center" colspan="7">No record(s) found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end mx-1 mt-50">
                                    {{-- Pagination --}}
                                    {{ $customers->appends(request()->input())->links('crm.partials.pagination') }}
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
