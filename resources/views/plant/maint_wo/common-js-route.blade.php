<script>
window.pageData = {
    csrf_token : "{!! csrf_token() !!}",
    wo: {!! json_encode(isset($wo) ? $wo : null) !!},
    editOrder: {{ (isset($buttons) && ($buttons['draft'] || $buttons['submit'])) ? 'true' : 'false' }},
    revNoQuery: {{ isset(request()->revisionNumber) ? 'true' : 'false' }},
    woId: {!! json_encode(isset($wo) ? $wo -> id : null) !!},
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
        subStores: "{{ route('subStore.get.from.stores') }}",
        storeData : "{{route('get_store_data')}}",
        bookDetails : "{{route('book.service-series.get')}}",
        getSeries : "{{ url('get-series') }}/",
    };
</script>

    