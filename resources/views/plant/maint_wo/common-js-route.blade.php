<script>
window.pageData = {
    csrf_token : "{!! csrf_token() !!}",
    wo: {!! json_encode(isset($wo) ? $wo : null) !!},
    editOrder: {{ (isset($buttons) && ($buttons['draft'] || $buttons['submit'])) ? 'true' : 'false' }},
    revNoQuery: {{ isset(request()->revisionNumber) ? 'true' : 'false' }},
    woId: {!! json_encode(isset($wo) ? $wo -> id : null) !!},
    today: "{!! Carbon\Carbon::now()->format('Y-m-d') !!}",
    menu_alias : "plant_maint-wo",
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
        ApiURL: "{{ route('maint-wo.populateModal') }}",
        amend: "{{ isset($data) ? route('maint-wo.edit', $data->id) : '#' }}",
    };
</script>

    