<script>
window.pageData = {
    csrf_token : "{!! csrf_token() !!}",
    order: {!! json_encode(isset($order) ? $order : null) !!},
    editOrder: {{ (isset($buttons) && ($buttons['draft'] || $buttons['submit'])) ? 'true' : 'false' }},
    revNoQuery: {{ isset(request()->revisionNumber) ? 'true' : 'false' }},
    orderId: {!! json_encode(isset($order) ? $order -> id : null) !!},
    Stock_store : {!! json_encode(App\Helpers\ConstantHelper::STOCKK) !!},
    Shop_store : {!! json_encode(App\Helpers\ConstantHelper::SHOP_FLOOR) !!},
    startDate:{!! $current_financial_year['start_date'] ? json_encode($current_financial_year['start_date']) : 'null'  !!},
    endDate:{!! $current_financial_year['end_date'] ? json_encode($current_financial_year['end_date']) : 'null' !!},
    today: "{!! Carbon\Carbon::now()->format('Y-m-d') !!}",
    menu_alias : "{!!  request() -> segments()[0] !!}",
    redirectUrl : "{!! isset($redirectUrl) ? $redirectUrl : '' !!}",
};
</script>
<script>
    window.routes = {
        docParams: "{{ route('book.get.doc_no_and_parameters') }}",
        serviceSeries: "{{ route('book.service-series.get') }}",
        revoke: "{{ route("trip-plan.revoke") }}",
        invDets :  "{{route("get_item_inventory_details")}}",
        subStores: "{{ route('subStore.get.from.stores') }}",
        storeData : "{{route('get_store_data')}}",
        bookDetails : "{{route('book.service-series.get')}}",
        amend :  "{{ route('sale.order.amend', isset($order) ? $order -> id : 0) }}",
        calTax : "{!!route('tax.calculate.sales', ['alias' => $order?->book?->service?->alias??" "]) !!}",
        getSeries : "{{ url('get-series') }}/",
    };
</script>

    