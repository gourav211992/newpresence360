<script>
window.pageData = {
    order: {!! json_encode(isset($order) ? $order : null) !!},
    editOrder: {{ (isset($buttons) && ($buttons['draft'] || $buttons['submit'])) ? 'false' : 'true' }},
    revNoQuery: {{ isset(request()->revisionNumber) ? 'true' : 'false' }},
    orderId: {!! json_encode(isset($order) ? $order -> id : null) !!}
};
</script>
<script>
    window.routes = {
        docParams: "{{ route('book.get.doc_no_and_parameters') }}",
        amendSaleOrder: "{{ route('sale.order.amend', isset($order) ? $order->id : 0) }}",
        serviceSeries: "{{ route('book.service-series.get') }}",
        revokePSV: "{{ route('psv.revoke') }}",
        invDets :  "{{route("get_item_inventory_details")}}",
    };
</script>

    