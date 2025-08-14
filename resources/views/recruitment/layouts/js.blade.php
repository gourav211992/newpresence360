<!-- BEGIN: Vendor JS-->
<script src="{{ asset('app-assets/vendors/js/vendors.min.js') }}"></script>
<!-- BEGIN Vendor JS-->

<!-- BEGIN: Page Vendor JS-->
<script src="{{ asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('app-assets/vendors/js/forms/select/select2.full.min.js') }}"></script>
<!-- END: Page Vendor JS-->

<!-- BEGIN: Theme JS-->
<script src="{{ asset('app-assets/js/core/app-menu.js') }}"></script>
<script src="{{ asset('app-assets/js/core/app.js') }}"></script>
<!-- END: Theme JS-->

<!-- BEGIN: Page JS-->
<script src="{{ asset('app-assets/js/scripts/forms/pickers/form-pickers.js') }}"></script>
<script src="{{ asset('app-assets/js/scripts/forms/form-select2.js') }}"></script>
<script src="{{ asset('app-assets/js/scripts/forms/form-number-input.js') }}"></script>
<script src="{{ asset('app-assets/js/scripts/sweetalert.js') }}"></script>
<script src="{{asset('app-assets/js/common-script-v2.js')}}"></script>
<script src="{{ asset('app-assets/js/scripts/summernote.min.js') }}"></script>
<!-- END: Page JS-->

<script>
    $(window).on('load', function() {
        if (feather) {
            feather.replace({
                width: 14,
                height: 14
            });
        }

        $.ajaxSetup({
            headers: {
                'Access-Control-Allow-Origin': '*',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    })
</script>