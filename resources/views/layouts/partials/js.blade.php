    <!-- BEGIN: Vendor JS-->
    <script src="{{ url('/app-assets/vendors/js/vendors.min.js') }}"></script>
    <!-- BEGIN Vendor JS-->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- BEGIN: Page Vendor JS-->
    <script src="{{ url('/app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/tables/datatable/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/tables/datatable/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/tables/datatable/datatables.checkboxes.min.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/tables/datatable/datatables.buttons.min.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/tables/datatable/jszip.min.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/tables/datatable/pdfmake.min.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/tables/datatable/vfs_fonts.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/tables/datatable/buttons.html5.min.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/tables/datatable/buttons.print.min.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/tables/datatable/dataTables.rowGroup.min.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/editors/quill/katex.min.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/editors/quill/highlight.min.js') }}"></script>
    <script src="{{ url('/app-assets/vendors/js/editors/quill/quill.min.js') }}"></script>
    <script src="{{ asset('app-assets/js/moment.js') }}"></script>
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src="{{ url('/app-assets/js/core/app-menu.js') }}"></script>
    <script src="{{ url('/app-assets/js/core/app.js') }}"></script>
    <script src="{{ url('/app-assets/js/ajax-script.js') }}"></script>
    <script src="{{ url('/app-assets/js/vendor-customer-script.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ url('/app-assets/js/jquery-ui.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>

    <!-- END: Theme JS-->


    <!-- BEGIN: Page JS-->
    <script src="{{ url('/app-assets/js/scripts/forms/form-quill-editor.js') }}"></script>

    <script src="{{ url('/app-assets/js/scripts/pages/app-email.js') }}"></script>
    <!-- END: Page JS-->


    <script src="{{ url('/app-assets/js/scripts/forms/pickers/form-pickers.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script src="{{ url('/app-assets/vendors/js/forms/select/select2.full.min.js') }}"></script>
    <script src="{{ url('/app-assets/js/scripts/forms/form-select2.js') }}"></script>
    <script src="{{ url('/app-assets/js/common-script.js') }}"></script>
    <script src="https://unpkg.com/feather-icons"></script>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <!-- Then, include Laravel Echo -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>

    <!-- Other Pusher Beams scripts -->
    <script src="https://js.pusher.com/beams/service-worker.js"></script>
    <script src="https://js.pusher.com/beams/1.0/push-notifications-cdn.js"></script>

    <script src="{{ asset('app-assets/summernote/summernote-lite.min.js') }}"></script>


    {{-- Pusher Notification --}}
    {{-- <script defer>
        Pusher.logToConsole = false; // For debugging, enable Pusher logs

        window.addEventListener('DOMContentLoaded', () => {
            Pass authenticated user ID from backend to JavaScript
            var userId = @json(\App\Helpers\Helper::getAuthenticatedUser()->id);
            var type = @json(get_class(\App\Helpers\Helper::getAuthenticatedUser()));

            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '10c23c19df9643f9a945',  // Replace with your actual Pusher key
                cluster: 'mt1',  // Replace with your actual Pusher cluster
                encrypted: true
            });

            // Log Pusher connection state changes (optional for debugging)
            window.Echo.connector.pusher.connection.bind('state_change', (state) => {
                // console.log('Pusher connection state:', state);
            });

            window.Echo.private(`user.${userId}`).notification((notification) => {

                // Prepare the notification HTML
                let notificationHtml = `
                <a class="d-flex"
                    href="{{route('notification.read','')}}+${notification.id}">
                    <div
                        class="list-item d-flex align-items-start unread-notification">
                        <div class="me-1">
                            <div class="avatar">
                                <img src="{{ url('app-assets/images/portrait/small/avatar-s-3.jpg') }}"
                                        alt="avatar" width="32" height="32">
                            </div>
                        </div>
                        <div class="list-item-body flex-grow-1">
                            <p class="media-heading">
                                <span class="fw-bolder">${notification.title}</span><br>
                                ${notification.description || notification.message}
                            </p>
                            <small class="notification-text">${moment(notification.created_at).fromNow()}</small>
                        </div>
                    </div>
                </a>
                `;

                if(notification.notifiable_type === type){
                    $('#list_noti').prepend(notificationHtml); // Prepend it to show at the top
                    $('.count').text(parseFloat($('.count').text())+1);
                    $('.count2').text(parseFloat($('.count').text()));
                }
            });
        })
    </script> --}}

    <script>
        @if (session('error'))
            Swal.fire({
                title: 'Error!',
                text: @json(session('error')),
                icon: 'error',
            });
        @endif

        $(window).on('load', function() {

            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }

            const mainLoader = document.getElementById('erp-overlay-loader');
            $(document).ajaxStop(function() {
                mainLoader.style.display = "none";
            });
            mainLoader.style.display = "none";


            // Call setActiveMenu function to CHighlight active menu
            setActiveMenu('#main-menu-navigation');


            $('.indian-number').each(function () {
                let $el = $(this);
                let value = $el.is('input') ? $el.val() : $el.text();

                if ($.isNumeric(value)) {
                    let formatted = formatIndianNumber(value);
                    $el.is('input') ? $el.val(formatted) : $el.text(formatted);
                }
            });

            // Optional: Format input fields on blur (live formatting)
            $('.indian-number').on('blur', function () {
                let $el = $(this);
                let value = $el.val();
                if ($.isNumeric(value)) {
                    $el.val(formatIndianNumber(value));
                }
            });

        });

        // Summernote
        $('#summernote').summernote({
            placeholder: 'Type your text here...',
            tabsize: 2,
            height: 300,
            width: '100%',
            fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Georgia', 'Times New Roman',
                'Verdana'
            ], // Custom font families
            fontNamesIgnoreCheck: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Georgia',
                'Times New Roman', 'Verdana'
            ], // Ignore check for these fonts
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear', 'fontname', 'fontsize', 'color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'hr', 'codeblock']],
                ['view', ['codeview', 'help']],
                ['align', ['align']],
                ['misc', ['undo', 'redo']]
            ]
        });

        /**
         * Highlight active menu item based on current URL
         * @param {string} menuSelector - jQuery selector for the menu container
         */
        function setActiveMenu(menuSelector) {
            // Get the current URL path (without query params or domain)
            let currentPath = window.location.pathname;

            // Track the best matching menu link
            let $activeLink = null;
            let maxLength = 0;

            // Loop through all menu links inside the given menu container
            $(menuSelector).find('a[href]').each(function () {
                let link = $(this).attr('href');

                // Skip empty or placeholder links
                if (!link || link === "#") return;

                /**
                 * We keep the "longest prefix match"
                 */
                if (currentPath.startsWith(link) && link.length > maxLength) {
                    maxLength = link.length;
                    $activeLink = $(this);
                }
            });

            // If a matching link is found, highlight it and expand parents
            if ($activeLink) {
                $activeLink.addClass('active');                         // highlight the link
                $activeLink.parents('li.has-sub').addClass('open');     // expand parent menus
                $activeLink.parents('li').children('a').addClass('active'); // highlight parent links
            }
        }

        function formatIndianNumber(number) {
            // Ensure the number is a float and round it to 2 decimal places
            number = parseFloat(number).toFixed(2);

            // Split the whole part and decimal part
            let parts = number.split('.');
            let wholePart = parts[0];
            let decimalPart = parts[1] || '00'; // Ensure decimal part exists

            // Remove any existing commas from the whole part
            wholePart = wholePart.replace(/,/g, '');

            // Regular expression to match the Indian format
            let lastThreeDigits = wholePart.slice(-3);
            let restOfTheNumber = wholePart.slice(0, -3);

            if (restOfTheNumber !== '') {
                restOfTheNumber = restOfTheNumber.replace(/\B(?=(\d{2})+(?!\d))/g, ',');
                wholePart = restOfTheNumber + ',' + lastThreeDigits;
            } else {
                wholePart = lastThreeDigits;
            }

            // Return the formatted number with two decimals
            return wholePart + '.' + decimalPart.padEnd(2, '0');
        }

        function removeCommas(input) {
            if (typeof input === 'string' && input.includes(',')) {
                return input.replace(/,/g, ''); // Replace all commas
            }
            return input; // Return the same value if no commas are present
        }

        // Function to format numbers with commas
        function formatNumberWithCommas(value) {
            // Remove any existing commas and non-numeric characters except decimal point
            value = value.replace(/[^0-9.]/g, '');

            // If it's an empty string after cleanup, return it as is
            if (!value) return value;

            // Add commas in the correct places for the number
            let parts = value.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            return parts.join('.');
        }

        function formatDateToDDMMYYYY(dateString) {
            // Create a new Date object from the input string
            var date = new Date(dateString);

            // Get the day, month, and year
            var day = date.getDate();
            var month = date.getMonth() + 1; // Months are 0-indexed
            var year = date.getFullYear();

            // Add leading zeros if day or month is less than 10
            day = day < 10 ? '0' + day : day;
            month = month < 10 ? '0' + month : month;

            // Return the formatted date
            return day + '-' + month + '-' + year;
        }

        $('.modal').on('shown.bs.modal', function () {
            $('.indian-number').each(function () {
                let $el = $(this);
                let value = $el.is('input') ? $el.val() : $el.text();

                if ($.isNumeric(value)) {
                    let formatted = formatIndianNumber(value);
                    $el.is('input') ? $el.val(formatted) : $el.text(formatted);
                }
            });
        });
    </script>
