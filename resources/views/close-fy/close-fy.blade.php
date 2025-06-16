@extends('layouts.app')
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <div class="content-body">
                <div class="card border">

                    <div class="row">
                        <div class="col-md-12">

                            <div class="row align-items-center po-reportfileterBox">
                                <div class="col-md-12">
                                    <div class="card-header d-block p-1">
                                        <div class="row  align-items-center">
                                            <div class="col-md-4">
                                                <h3>Close Current F.Y.</h3>
                                                <p>Apply the Basic Filter</p>
                                            </div>
                                            <div class="col-md-8 text-sm-end">
                                                @if (isset($financialYear))
                                                    {{-- Show "Close F.Y" button only if FY is not already closed --}}
                                                    @if ($financialYear['fy_close'] == false)
                                                        <button class="btn mt-25 btn-primary btn-sm swal-action-btn"
                                                            data-type="close" data-url="{{ route('post-closefy') }}"
                                                            id="closeFyBtn" type="button">
                                                            <i data-feather="x-circle"></i> Close F.Y
                                                        </button>
                                                    @endif

                                                    {{-- Show "Lock F.Y" button only if it's currently unlocked --}}
                                                    @if ($financialYear['lock_fy'] == false && $financialYear['fy_close'] == true)
                                                        <button class="btn mt-25 btn-danger btn-sm swal-action-btn"
                                                            data-type="lock" data-url="{{ route('close-fy.lock') }}"
                                                            type="submit">
                                                            <i data-feather="lock"></i> Lock F.Y
                                                        </button>
                                                    @endif

                                                    {{-- Show "Unlock F.Y" button only if it's currently locked --}}
                                                    @if ($financialYear['lock_fy'] == true && $financialYear['fy_close'] == true)
                                                        <button class="btn mt-25 btn-success btn-sm swal-action-btn"
                                                            data-type="unlock" data-url="{{ route('close-fy.lock') }}"
                                                            type="submit">
                                                            <i data-feather="unlock"></i> UnLock F.Y
                                                        </button>
                                                    @endif
                                                @endif

                                            </div>
                                        </div>

                                    </div>

                                    <div class="customernewsection-form poreportlistview">
                                        <div class="bg-light border-bottom mb-1  p-1">
                                            <div class="row">

                                                <div class="col-md-3">
                                                    <div class="mb-1 mb-sm-0">
                                                        <label class="form-label">Select Organization</label>
                                                        <select id="organization_id" class="form-select select2">
                                                            <option value="">Select</option>
                                                            @foreach ($companies as $organization)
                                                                <option value="{{ $organization->organization->id }}"
                                                                    {{ $organization->organization->id == $organizationId ? 'selected' : '' }}>
                                                                    {{ $organization->organization->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="mb-1 mb-sm-0">
                                                        <label class="form-label">Select F.Y</label>
                                                        <select id="fyear_id" class="form-select select2">
                                                            <option value="">Select</option>
                                                            @if (isset($past_fyears) && is_iterable($past_fyears))
                                                            @foreach ($past_fyears as $fyear)
                                                                <option value="{{ $fyear['id'] }}"
                                                                data-start="{{ $fyear['start_date'] }}"
                                                                    data-end="{{ $fyear['end_date'] }}"
                                                                    {{ $fyear['id'] == $fyearId ? 'selected' : '' }}>
                                                                    {{ $fyear['range'] }}
                                                                </option>
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>


                                                <div class="col-md-6">
                                                    <div class="mt-sm-2 mb-sm-0">
                                                        <label class="mb-1">&nbsp;</label>
                                                        <button
                                                            class="btn mt-25 btn-warning btn-sm waves-effect waves-float waves-light apply-filter">
                                                            <i data-feather="search"></i> Find</button>
                                                    </div>
                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <div class="px-2 mb-1 mt-1">
                                <div class="step-custhomapp bg-light">
                                    <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#Transfer">Closing Balance</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#Access">Access Management</a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="tab-content ">
                                    <div class="tab-pane active" id="Transfer">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">

                                            </div>
                                            <div class="col-md-4 text-sm-end">
                                                <a href="#" class="trail-exp-allbtnact" id="expand-all">
                                                    <i data-feather='plus-circle'></i> Expand All
                                                </a>
                                                <a href="#" class="trail-col-allbtnact" id="collapse-all">
                                                    <i data-feather='minus-circle'></i> Collapse All
                                                </a>
                                            </div>
                                        </div>
                                        <div class="earn-dedtable trail-balancefinance trailbalnewdesfinance">
                                            <div class="table-responsive">
                                                <table class="table border" id="tranferLedger">
                                                    <thead>
                                                        <tr>
                                                            <th id="company_name"></th>
                                                            <th width="300px" id="fy_range"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tableData"></tbody>

                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="Access">
                                        @php
                                        $locked = $authorized_users['locked'] ?? false;
                                        @endphp
                                        @if (isset($organizationId) && $organizationId)
                                        <div class="text-end mb-2">
                                            @if($financialYear['fy_close'] == true && !$locked)
                                            <a id="saveAccessBy" href="#" class="btn-dark btn-sm access-by">
                                                <i data-feather='check-circle'></i> Save
                                            </a>
                                            @endif
                                        </div>
                                        <div class="table-responsive-md">
                                            <table
                                                class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border" id="accessData">
                                                <thead>
                                                    <tr>
                                                        <th width="50px">#</th>
                                                            <th width="280">User<span
                                                                    class="text-danger">*</span>
                                                        </th>
                                                        <th width="600">Roles</th>
                                                        <th width="100px">Action</th>
                                                    </tr>
                                                </thead>

                                               <tbody>
                                                    @php
                                                        $authorizedUsers = $authorized_users['users'] ?? collect();
                                                        $showAddRowOnly = $authorizedUsers->isEmpty();
                                                        $rowNumber = 1;
                                                    @endphp

                                                    {{-- If all are authorized, show one empty row with plus icon --}}
                                                    @if (is_null($authorized_users))
                                                        <tr>
                                                            <td class="sno">{{ $rowNumber }}</td>
                                                            <td>
                                                                <select class="form-select mw-100 select2 authorize-user" id="authorize_{{ $rowNumber }}">
                                                                    <option value="" disabled selected>Select</option>
                                                                    @foreach ($employees as $employee)
                                                                        @php
                                                                        $authUser = $employee->authUser();
                                                                            $permissions = $authUser ? $authUser->roles->pluck('name')->toArray() : [];
                                                                        @endphp
                                                                        <option
                                                                            value="{{ $employee->id }}"
                                                                            data-permissions='@json($permissions)'
                                                                            data-authenticable-type="{{ $employee->authenticable_type }}">
                                                                            {{ $authUser ? $authUser->name : 'N/A' }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <input type="hidden" class="authenticable-type" name="authenticable_type[]">
                                                            </td>
                                                            <td class="permission-badges"></td>
                                                            <td>
                                                            @if(($financialYear['fy_close'] ?? false) || !($locked === true))
                                                                <a href="#" id="saveCloseFyBtn" class="text-primary"><i data-feather="plus-square"></i></a>
                                                                @endif
                                                            </td>
                                                        </tr>

                                                    {{-- Else show all authorized users --}}
                                                    @elseif(!$showAddRowOnly)
                                                        @foreach ($authorizedUsers as $index => $authorizedUser)
                                                            <tr>
                                                                <td class="sno">{{ $rowNumber }}</td>
                                                                <td>
                                                                    <select class="form-select mw-100 select2 authorize-user" id="authorize_{{ $rowNumber }}"
                                                                        @if(!$financialYear['fy_close'] || $locked) disabled @endif>
                                                                        <option value="" disabled>Select</option>
                                                                        @foreach ($employees as $employee)
                                                                            @php
                                                                                $authUser = $employee->authUser();
                                                                                $permissions = $authUser ? $authUser->roles->pluck('name')->toArray() : [];
                                                                                $isSelected =  $authorizedUser->id ==  $employee->id;
                                                                            @endphp
                                                                            <option
                                                                                value="{{ $employee->id }}"
                                                                                data-permissions='@json($permissions)'
                                                                                data-authenticable-type="{{ $employee->authenticable_type }}"
                                                                                {{ $isSelected ? 'selected' : '' }}>
                                                                                {{ $authUser ? $authUser->name : 'N/A' }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <input type="hidden" class="authenticable-type" name="authenticable_type[]">
                                                                </td>
                                                                <td class="permission-badges"></td>


                                                                 @if($financialYear['fy_close'] == true && !$locked)
                                                                <td>
                                                                    @if ($authorizedUsers->count() === 1 || $loop->first)
                                                                        <a href="#" id="saveCloseFyBtn" class="text-primary"><i data-feather="plus-square"></i></a>
                                                                    @else
                                                                        <a href="#" class="text-danger deleteAuthorize"><i data-feather="trash-2"></i></a>
                                                                    @endif
                                                                </td>
                                                                @endif
                                                            </tr>
                                                            @php $rowNumber++; @endphp
                                                        @endforeach
                                                    @endif

                                                </tbody>



                                            </table>
                                        </div>
                                        @endif
                                    </div>
                                </div>


                            </div>






                        </div>

                    </div>

                </div>




            </div>
        </div>
    </div>
@endsection
<!-- END: Content-->

@section('scripts')
    <script>
// 1. Function to create a new row
    function getNewRowHtml(rowNum) {
            return `
                <tr>
                    <td class="">${rowNum} </td>
                    <td>
                        <select class="form-select mw-100 select2 authorize-user" id="authorize_${rowNum}" name="authorized_users[]" required>
                            <option value="" disabled selected>Select</option>
                            ${getEmployeeOptions()}
                        </select>
                        <input type="hidden" class="authenticable-type" name="authenticable_type[]">
                    </td>
                    <td class="permission-badges"></td>
                    <td>
                        <a href="#" class="text-danger deleteAuthorize"><i data-feather="trash-2"></i></a>
                    </td>
                </tr>
            `;
        }
        function updateSerialNumbers() {
            $('#accessData tbody tr').each(function(index) {
                $(this).find('td.sno').text(index + 1);
            });
        }
        // 2. Blade-generated employee options
        function getEmployeeOptions() {
        return `
            @foreach ($employees as $employee)
                @php
                    $authUser = $employee->authUser();
                    $permissions = $authUser ? $authUser->roles->pluck('name')->toArray() : [];
                @endphp
                <option value="{{ $employee->id }}"
                        data-permissions='@json($permissions)'
                        data-authenticable-type="{{  $employee->authenticable_type  }}">
                    {{ $authUser ? $authUser->name : 'N/A' }}
                </option>
            @endforeach
        `;
}

        // 3. Disable selected users in other rows
        function updateDisabledUsers() {
            // Get all currently selected user IDs (except empty selections)
            const selectedIds = [];
            $('select.authorize-user').each(function() {
                const val = $(this).val();
                if (val) selectedIds.push(val);
            });

            // Update all dropdowns
            $('select.authorize-user').each(function() {
                const currentRowVal = $(this).val();
                $(this).find('option').each(function() {
                    const optionVal = $(this).val();
                    // Disable if:
                    // 1. It's selected in another dropdown (and not the current one)
                    // 2. It's not the empty "Select" option
                    if (optionVal && optionVal !== currentRowVal && selectedIds.includes(optionVal)) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                });

                // Reinitialize Select2 to reflect changes
                $(this).trigger('change.select2');
            });
        }

        $(document).ready(function() {
            // Initialize the first row's permissions if a user is already selected
            // const initialSelected = $('#authorize_1').find('option:selected');
            // console.log(initialSelected)
            // if (initialSelected.val()) {
            //     const permissions = initialSelected.data('permissions') || [];
            //     const $permissionsBox = $('#authorize_1').closest('tr').find('.permissions-box');

            //     $permissionsBox.empty().prop('disabled', true);
            //     const uniquePermissions = [...new Set(permissions)];
            //     uniquePermissions.forEach(p => {
            //         $permissionsBox.append(`<option selected value="${p}">${p}</option>`);
            //     });
            //     $permissionsBox.trigger('change');
            // }

            // 4. Add new row
            $(document).on('click', '#saveCloseFyBtn', function(e) {
                e.preventDefault();
                  // Get all user IDs already selected
                const selectedIds = [];
                $('select.authorize-user').each(function () {
                    const val = $(this).val();
                    if (val) selectedIds.push(val);
                });

                // Get all user IDs available in the select options
                const allUserIds = [];
                $('select.authorize-user:first option').each(function () {
                    const val = $(this).val();
                    if (val) allUserIds.push(val);
                });

                const availableToSelect = allUserIds.filter(id => !selectedIds.includes(id));

                if (availableToSelect.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'All Users Selected',
                        text: 'All available users have already been added.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                let counter = $('#accessData tbody tr').length + 1;
                const newRowHtml = getNewRowHtml(counter);
                $('#accessData tbody').append(newRowHtml);
                $('.select2').select2(); // reinitialize select2
                feather.replace(); // reinitialize icons
                updateSerialNumbers(); // ✅ here
                updateDisabledUsers(); // prevent duplicates
                // counter++;
            });

            // 5. Delete row
            $(document).on('click', '.deleteAuthorize', function(e) {
                e.preventDefault();
                $(this).closest('tr').remove();
                // Renumber the rows
                $('#accessData tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
                updateDisabledUsers(); // refresh available users
            });

            // 6. Handle user selection and populate permissions
            $(document).on('change', '.authorize-user', function() {
                const selected = $(this).find('option:selected');
                const permissions = selected.data('permissions') || [];
                const authType = selected.data('authenticable-type') || '';

                const $row = $(this).closest('tr');
                const $badgeTd = $row.find('.permission-badges');
                const $authTypeInput = $row.find('.authenticable-type'); // hidden input

                // Set auth type in hidden input
                $authTypeInput.val(authType);

                const uniquePermissions = [...new Set(permissions)];

                if (uniquePermissions.length) {
                    const badgesHtml = uniquePermissions.map(p =>
                        `<span class="badge rounded-pill badge-light-primary badgeborder-radius me-25" style="font-size:12px">${p}</span>`
                    ).join('');
                    $badgeTd.html(badgesHtml);
                } else {
                    $badgeTd.html(`<span class="text-muted" style="font-size:12px">No Permissions</span>`);
                }

                updateDisabledUsers();
            });

            // 7. Save button: get all users and permissions
            document.getElementById('saveAccessBy').addEventListener('click', function(event) {
                event.preventDefault();
                $('.preloader').show();

                let users = [];
                const fyValue = $('#fyear_id').val();

                // Collect selected user IDs
                let hasEmptyUser = false;
                $('select.authorize-user').each(function() {
                    const userId = $(this).val();
                    const selectedOption = $(this).find('option:selected');
                    const authType = selectedOption.data('authenticable-type') || null;

                    if (userId) {
                        users.push({
                            user_id: userId,
                            authenticable_type: authType
                        });
                        hasEmptyUser = true;
                    }
                });
                console.log(hasEmptyUser, users)
                // Show alert if any user row is unselected
                if (!hasEmptyUser || users.length === 0) {
                    $('.preloader').hide();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Selection',
                        text: 'Please select at least one user to assign access.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                if (fyValue && fyValue.trim() !== "") {
                    users.fyear = fyValue;
                }

                console.log("Data to be saved:", users);

                const url = "{{ route('close-fy.update-authuser') }}";
                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            users: users,
                            fyear: users.fyear
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        $('.preloader').hide();
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                $('.preloader').show();
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: data.message,
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(err => {
                        $('.preloader').hide();
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Error saving Close FY Authorized Users.',
                            confirmButtonText: 'OK'
                        });
                        console.error(err);
                    });
            });


            // Initialize the first row's user dropdown restrictions
            updateDisabledUsers();
        });
        $(document).ready(function() {

            function updatePermissionsBox(selectElement) {
                const selected = selectElement.find('option:selected');
                const permissions = selected.data('permissions') || [];
                const authType = selected.data('authenticable-type') || '';

                const $row = selectElement.closest('tr');
                const $badgeTd = $row.find('.permission-badges');
                const $authTypeInput = $row.find('.authenticable-type'); // hidden input

                // Set auth type in hidden input
                if ($authTypeInput.length) {
                    $authTypeInput.val(authType);
                }

                const uniquePermissions = [...new Set(permissions)];

                if (uniquePermissions.length) {
                    const badgesHtml = uniquePermissions.map(p =>
                        `<span class="badge rounded-pill badge-light-primary badgeborder-radius me-25" style="font-size:12px">${p}</span>`
                    ).join('');
                    $badgeTd.html(badgesHtml);
                } else {
                    $badgeTd.html(`<span class="text-muted" style="font-size:12px">No Permissions</span>`);
                }
            }

            // Trigger once on page load for all authorize-user dropdowns
            $('.authorize-user').each(function() {
                updatePermissionsBox($(this));
            });

            // Trigger on change for all authorize-user dropdowns
            $(document).on('change', '.authorize-user', function() {
                updatePermissionsBox($(this));
            });

        });
    </script>
    <script>
        var reservesSurplus = '';
        $(document).ready(function() {

            const params = new URLSearchParams(window.location.search);
            const fyearId = params.get('fyear');
            const organizationId = params.get('organization_id');

            // Check if both parameters are present and non-empty
            if (fyearId && organizationId) {

                // Update the displayed labels
                $('#fy_range').text(`F.Y ${$('#fyear_id option:selected').text()} Closing Balance`);
                $('#company_name').text(
                    $('#organization_id option:selected')
                    .map(function() {
                        return $(this).text();
                    })
                    .get()
                    .join(', ')
                );

                // Call the function to fetch filtered data
            getInitialGroups();
            }

            $('#company_name').text(
                $('#organization_id option:selected')
                .map(function() {
                    return $(this).text();
                })
                .get()
                .join(', ')
            );
            const selectedOption = $('#fyear_id option:selected');
            const selectedValue = selectedOption.val()?.trim();

            const selectedText = selectedValue !== "" ? selectedOption.text() : '';

            $('#fy_range').text(`F.Y ${selectedText} Closing Balance`);

            // $('#fy_range').text(`F.Y ${selectedText || '{{ $current_range }}'} Closing Balance`);


            // Filter record
            $(".apply-filter").on("click", function() {
                // Hide modal and reset
                $('.preloader').show();
                $(".modal").modal("hide");
                $('.collapse').click();
                $('#tableData').html('');

                // Get selected values
                const fyearId = $('#fyear_id').val()?.trim();
                const organizationId = $('#organization_id').val() || [];

                // Build URL params
                let params = new URLSearchParams(window.location.search);
                params.set('fyear', fyearId);
                params.set('organization_id', organizationId);
                // params.set('cost_center_id', $('#cost_center_id').val());

                // Update URL in browser without reloading
                const currentUrl = window.location.pathname + '?' + params.toString();
                window.history.pushState({}, '', currentUrl);


                // Update company name label
                if (organizationId.length === 0) {
                    $('#company_name').text('All Companies');
                } else {
                    $('#company_name').text(
                        $('#organization_id option:selected')
                        .map(function() {
                            return $(this).text();
                        })
                        .get()
                        .join(', ')
                    );
                }




                // Validate filters before redirect
                const isValid = fyearId !== "" && organizationId.length > 0;
                if (isValid) {
                    // Update financial year label
                const selectedOption = $('#fyear_id option:selected');
                    const selectedText = fyearId !== "" ? selectedOption.text() : '{{ $current_range }}';
                    // $('#fy_range').text(`F.Y ${selectedText} Closing Balance`);
                    window.location.href = currentUrl; // ✅ Perform redirect
                } else {
                    $('.preloader').hide();
                    Swal.fire({
                        title: 'Not Valid Filters!',
                        text: "Please select both Financial Year and Organization to proceed.",
                        icon: 'error'
                    });
                }
            });
            function formatDate(dateStr) {
                const date = new Date(dateStr);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0'); // months are 0-based
                const year = date.getFullYear();
                return `${day}-${month}-${year}`;
            }

            function getInitialGroups() {


                var obj = {
                    fyear: $('#fyear_id').val(),
                    // cost_center_id: $('#cost_center_id').val(),
                    // currency: $('#currency').val(),
                    '_token': '{!! csrf_token() !!}'
                };
                var selectedValues = $('#organization_id').val();
                // var filteredValues = selectedValues.filter(function(value) {
                //     return value !== null && value.trim() !== '';
                // });
                // if (filteredValues.length > 0) {
                obj.organization_id = selectedValues
                // }
                $('.preloader').show();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "POST",
                    url: "{{ route('getFyInitialGroups') }}",
                    dataType: "JSON",
                    data: obj,
                    success: function(data) {
                        $('.preloader').hide();
                         if (data.message) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Notice',
                                text: data.message,
                                confirmButtonText: 'OK'
                            });
                            return; // Stop further execution if needed
                        }
                        if (data['data'].length > 0) {
                            reservesSurplus = data['profitLoss'];
                            let html = '';

                            var openingDrTotal = 0;
                            var openingCrTotal = 0;
                            var closingDrTotal = 0;
                            var closingCrTotal = 0;
                            var opening_tot = 0

                            for (let i = 0; i < data['data'].length; i++) {
                                var total_debit = data['data'][i].total_debit;
                                var total_credit = data['data'][i].total_credit;

                                var opening = data['data'][i].open;
                                var opening_type = data['data'][i].opening_type;
                                var closingText = '';
                                var closing = opening + (total_debit - total_credit);



                                if (closing != 0) {
                                    closingText = closing > 0 ? 'Dr' : 'Cr';
                                }

                                opening_tot += opening;
                                let close = parseFloat(data['data'][i].open + (data['data'][i]
                                    .total_debit - data['data'][i].total_credit));
                                let closeType = "";
                                if (close < 0)
                                    closeType = "Cr";
                                else
                                    closeType = "Dr";

                                openingDrTotal += parseFloat(data['data'][i].open);

                                closingCrTotal += parseFloat(closing);



                                const groupUrl = "{{ route('close-fy') }}/" + data['data'][i].id;

                                html += `
									<tr class="trail-bal-tabl-none" id="${data['data'][i].id}">
										<input type="hidden" id="check${data['data'][i].id}">
										<td>
											<a href="#" class="trail-open-new-listplus-btn expand exp${data['data'][i].id}" data-id="${data['data'][i].id}"><i data-feather='plus-circle'></i></a>
											<a href="#" class="trail-open-new-listminus-btn collapse"><i data-feather='minus-circle'></i></a>
											<a class="urls" href="${groupUrl}">
												${data['data'][i].name}
											</a>
										</td>
                                        <td class="close_amt">${Math.abs(closing).toLocaleString('en-IN')} ${closingText}</td>
									</tr>`;
                            }

                            var openingTotalType = '';
                            var openingTotalDiff = parseFloat(openingDrTotal) - parseFloat(
                                openingCrTotal);
                            if (openingTotalDiff != 0) {
                                var openingTotalDiff = openingTotalDiff > 0 ? openingTotalDiff : -
                                    openingTotalDiff;
                                if (parseFloat(openingDrTotal) > parseFloat(openingCrTotal)) {
                                    openingTotalType = 'Dr';
                                } else {
                                    openingTotalType = 'Cr';
                                }
                            }

                            var closingTotalType = '';
                            var closingTotalDiff = (parseFloat(closingDrTotal) - parseFloat(
                                closingCrTotal));
                            if (closingTotalDiff != 0) {
                                var closingTotalDiff = closingTotalDiff > 0 ? closingTotalDiff : -
                                    closingTotalDiff;
                                if (parseFloat(closingDrTotal) > parseFloat(closingCrTotal)) {
                                    closingTotalType = 'Dr';
                                } else {
                                    closingTotalType = 'Cr';
                                }
                            }

                            // $('#openingAmt').text(openingTotalDiff.toLocaleString('en-IN')+openingTotalType);
                            // $('#closingAmt').text(closingTotalDiff.toLocaleString('en-IN')+closingTotalType);
                            $('#tableData').empty().append(html);
                            calculate_cr_dr();

                        }

                        $('#startDate').text(data['startDate']);
                        $('#endDate').text(data['endDate']);

                        if (feather) {
                            feather.replace({
                                width: 14,
                                height: 14
                            });
                        }

                        calculate_cr_dr();

                        $('#expand-all').click();
                    }
                });
            }

            function calculate_cr_dr() {
                let cr_sum = 0;
                $('.crd_amt').each(function() {
                    const value = removeCommas($(this).text()) || 0;
                    cr_sum = parseFloat(parseFloat(cr_sum + value).toFixed(2));
                });
                $('#crd_total').text(cr_sum.toLocaleString('en-IN'));

                let dr_sum = 0;
                $('.dbt_amt').each(function() {
                    const value = removeCommas($(this).text()) || 0;
                    dr_sum = parseFloat(parseFloat(dr_sum + value).toFixed(2));
                });
                $('#dbt_total').text(dr_sum.toLocaleString('en-IN'));

                // Opening balance
                let opening_total = 0;
                $('.open_amt').each(function() {
                    const raw = $(this).text().trim();
                    const match = raw.match(/^([\d,.\-]+)\s*(Dr|Cr)?$/i);

                    if (match) {
                        let amount = removeCommas(match[1]);
                        let type = match[2] ? match[2].toLowerCase() : 'dr'
                        console.log("type" + type);

                        if (type.toLowerCase() === 'dr') {
                            opening_total += amount;
                        } else if (type.toLowerCase() === 'cr') {
                            opening_total -= amount;
                        }
                    }
                });

                // $('#openingAmt').text(Math.abs(opening_total).toLocaleString('en-IN') + ' ' + (opening_total >= 0 ? 'Dr' : 'Cr'));

                // Closing balance
                let closing_total = 0;
                $('.close_amt').each(function() {
                    const raw = $(this).text().trim();
                    const match = raw.match(/^([\d,.\-]+)\s*(Dr|Cr)?$/i);

                    if (match) {
                        let amount = removeCommas(match[1]);
                        let type = match[2] ? match[2].toLowerCase() : 'dr'

                        if (type.toLowerCase() === 'dr') {
                            closing_total += amount;
                        } else if (type.toLowerCase() === 'cr') {
                            closing_total -= amount;
                        }
                    }
                });


                // $('#closingAmt').text(Math.abs(closing_total).toLocaleString('en-IN') + ' ' + (closing_total >= 0 ? 'Dr' : 'Cr'));
                $('.urls').each(function() {
                    let currentHref = $(this).attr('href') || '';
                    let baseUrl = currentHref.split('?')[0]; // remove old query params if any

                    // Append new query parameters
                    let updatedUrl =
                        `${baseUrl}?date=${encodeURIComponent($('#fp-range').val())}&cost_center_id=${encodeURIComponent($('#cost_center_id').val())}`;
                    $(this).attr('href', updatedUrl);

                });
                let r_date = "{{ request('date') }}";
                if (r_date != "") {
                    console.log("date" + r_date);

                    $("#fp-range").val(r_date);
                }



            }

            function removeCommas(str) {
                return parseFloat(str.replace(/,/g, ""));
            }

            function getIncrementalPadding(parentPadding) {
                return parentPadding + 10; // Increase padding by 10px
            }

            $(document).on('click', '.expand', function() {
                const id = $(this).attr('data-id');
                const parentPadding = parseInt($(this).closest('td').css('padding-left'));

                if ($('#name' + id).text() == "Reserves & Surplus") {
                    const padding = getIncrementalPadding(parentPadding);

                    let html = `
                    <tr class="trail-sub-list-open parent-${id}">
                        <td style="padding-left: ${padding}px">Profit & Loss</td>
                        <td>${parseFloat(reservesSurplus['closingFinal']).toLocaleString('en-IN')} ${reservesSurplus['closing_type']}</td>
                    </tr>`;
                    $('#' + id).closest('tr').after(html);
                } else {
                    if ($('#check' + id).val() == "") {


                        const selected = $('#fyear_id').find('option:selected');
                        const date1 = formatDate(selected.data('start'));
                        const date2 = formatDate(selected.data('end'));
                        const fullRange = `${date1} to ${date2}`;
                        console.log(fullRange)
                        var obj = {
                            id: id,
                            date: fullRange,
                            cost_center_id: $('#cost_center_id').val(),
                            currency: $('#currency').val(),
                            '_token': '{!! csrf_token() !!}'
                        };
                        var selectedValues = $('#organization_id').val() || [];
                        // var filteredValues = selectedValues.filter(function(value) {
                        //     return value !== null && value.trim() !== '';
                        // });
                        // if (filteredValues.length > 0) {
                        obj.organization_id = selectedValues
                        // }
// console.log(obj)
                        $('.preloader').show();
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            type: "POST",
                            url: "{{ route('getSubGroups') }}",
                            dataType: "JSON",
                            data: obj,
                            success: function(data) {
                                $('.preloader').hide();
                                $('#check' + id).val(id);
                                if (data['data'].length > 0) {
                                    let html = '';
                                    if (data['type'] == "group") {
                                        for (let i = 0; i < data['data'].length; i++) {
                                            const padding = getIncrementalPadding(
                                            parentPadding);
                                            var closingText = '';
                                            const closing = data['data'][i].open + (data['data']
                                                [i].total_debit - data['data'][i]
                                                .total_credit);
                                            if (closing != 0) {
                                                closingText = closing > 0 ? 'Dr' : 'Cr';
                                            }
                                            const groupUrl = "{{ route('trial_balance') }}/" +
                                                data['data'][i].id;

                                            if (data['data'][i].name == "Reserves & Surplus") {
                                                html += `
                                            <tr class="trail-sub-list-open expandable parent-${id}" id="${data['data'][i].id}">
                                                <input type="hidden" id="check${data['data'][i].id}">
                                                <td style="padding-left: ${padding}px">
                                                    <a href="#" class="trail-open-new-listplus-sub-btn text-dark expand exp${data['data'][i].id}" data-id="${data['data'][i].id}">
                                                        <i data-feather='plus-circle'></i>
                                                    </a>
                                                    <a href="#" class="trail-open-new-listminus-sub-btn text-dark collapse" style="display:none;">
                                                        <i data-feather='minus-circle'></i>
                                                    </a>
                                                    <span id="name${data['data'][i].id}">${data['data'][i].name}</span>
                                                </td>
                                                <td>${parseFloat(reservesSurplus['closingFinal']).toLocaleString('en-IN')} ${reservesSurplus['closing_type']}</td>
                                            </tr>`;
                                            } else {
                                                html += `
                                            <tr class="trail-sub-list-open expandable parent-${id}" id="${data['data'][i].id}">
                                                <input type="hidden" id="check${data['data'][i].id}">
                                                <td style="padding-left: ${padding}px">
                                                    <a href="#" class="trail-open-new-listplus-sub-btn text-dark expand exp${data['data'][i].id}" data-id="${data['data'][i].id}">
                                                        <i data-feather='plus-circle'></i>
                                                    </a>
                                                    <a href="#" class="trail-open-new-listminus-sub-btn text-dark collapse" style="display:none;">
                                                        <i data-feather='minus-circle'></i>
                                                    </a>
                                                    <a class="urls" href="${groupUrl}">
                                                        ${data['data'][i].name}
                                                    </a>
                                                </td>
                                                <td>${parseFloat(closing < 0 ? -closing : closing).toLocaleString('en-IN')} ${closingText}</td>
                                            </tr>`;
                                            }
                                        }
                                    } else {
                                        let tot_debt = 0;
                                        let tot_credit = 0;
                                        for (let i = 0; i < data['data'].length; i++) {
                                            const padding = getIncrementalPadding(
                                            parentPadding);
                                            var closingText = '';
                                            const closing = data['data'][i].open + (data['data']
                                                [i].details_sum_debit_amt - data['data'][i]
                                                .details_sum_credit_amt);
                                            if (closing != 0) {
                                                closingText = closing > 0 ? 'Dr' : 'Cr';
                                            }

                                            html += `
                                            <tr class="trail-sub-list-open parent-${id}">
                                                <td style="padding-left: ${padding}px">
														<i data-feather='arrow-right'></i>${data['data'][i].name}
                                                </td>
                                                <td>${parseFloat(closing < 0 ? -closing : closing).toLocaleString('en-IN')} ${closingText}</td>
                                            </tr>`;
                                            tot_debt += data['data'][i].details_sum_debit_amt;
                                            tot_credit += data['data'][i]
                                            .details_sum_credit_amt;
                                        }
                                    }
                                    $('#' + id).closest('tr').after(html);
                                    $('.urls').each(function() {
                                        let currentHref = $(this).attr('href') || '';
                                        let baseUrl = currentHref.split('?')[
                                        0]; // remove old query params if any

                                        // Append new query parameters
                                        let updatedUrl =
                                            `${baseUrl}?date=${encodeURIComponent($('#fp-range').val())}&cost_center_id=${encodeURIComponent($('#cost_center_id').val())}`;
                                        $(this).attr('href', updatedUrl);

                                    });


                                }

                                if (feather) {
                                    feather.replace({
                                        width: 14,
                                        height: 14
                                    });
                                }
                            }
                        });

                    }
                }

                // Expand all direct children of this row
                $('.parent-' + id).show();
                $(this).hide();
                $(this).siblings('.collapse').show();
            });

            $(document).on('click', '.collapse', function() {
                const id = $(this).closest('tr').attr('id');

                // Collapse all children of this row recursively and hide their expand icons
                function collapseChildren(parentId) {
                    $(`.parent-${parentId}`).each(function() {
                        const childId = $(this).attr('id');
                        $(this).hide(); // Hide the child row
                        $(this).find('.collapse').hide(); // Hide the collapse icon
                        $(this).find('.expand').show(); // Show the expand icon
                        collapseChildren(childId); // Recursively collapse the child's children
                    });
                }

                collapseChildren(id);

                $(this).hide();
                $(this).siblings('.expand').show();
            });

            // Expand All rows
            $('#expand-all').click(function() {
                $('.expand').hide();

                var trIds = $('#tranferLedger tbody tr').map(function() {
                    return this.id; // Return the ID of each tr element
                }).get().filter(function(id) {
                    return id !== "" && $('#check' + id).val() == ""; // Filter out any empty IDs
                });

                if (trIds.length > 0) {
                        const selected = $('#fyear_id').find('option:selected');
                        const date1 = formatDate(selected.data('start'));
                        const date2 = formatDate(selected.data('end'));
                        const fullRange = `${date1} to ${date2}`;
                    var obj = {
                        ids: trIds,
                        date: fullRange,
                        cost_center_id: $('#cost_center_id').val(),
                        currency: $('#currency').val(),
                        '_token': '{!! csrf_token() !!}'
                    };
                    var selectedValues = $('#organization_id').val() || [];
                    // var filteredValues = selectedValues.filter(function(value) {
                    //     return value !== null && value.trim() !== '';
                    // });
                    // if (filteredValues.length > 0) {
                    obj.organization_id = selectedValues
                    // }
                    $('.preloader').show();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: "POST",
                        url: "{{ route('getSubGroupsMultiple') }}",
                        dataType: "JSON",
                        data: obj,
                        success: function(res) {
                            $('.preloader').hide();
                            if (res['data'].length > 0) {


                                res['data'].forEach(data => {
                                    let tot_credit = 0;
                                    let tot_debt = 0;

                                    $('#check' + data['id']).val(data['id']);
                                    const parentPadding = parseInt($('.exp' + data[
                                        'id']).closest('td').css(
                                        'padding-left'));

                                    if ($('#name' + data['id']).text() ==
                                        "Reserves & Surplus") {
                                        const padding = getIncrementalPadding(
                                            parentPadding);

                                        let html = `
											<tr class="trail-sub-list-open parent-${data['id']}">
                                            <td style="padding-left: ${padding}px">Profit & Loss</td>
                                            <td>${parseFloat(reservesSurplus['closingFinal']).toLocaleString('en-IN')} ${reservesSurplus['closing_type']}</td>
                                        </tr>>`;
                                        $('#' + data['id']).closest('tr').after(html);
                                    } else {
                                        if (data['data'].length > 0) {
                                            let tot_debt = 0;
                                            let tot_credit = 0;

                                            let html = '';
                                            if (data['type'] == "group") {
                                                for (let i = 0; i < data['data']
                                                    .length; i++) {
                                                    const padding =
                                                        getIncrementalPadding(
                                                            parentPadding);
                                                    var closingText = '';
                                                    const closing = data['data'][i]
                                                        .open + (data['data'][i]
                                                            .total_debit - data['data'][
                                                                i
                                                            ].total_credit);
                                                    if (closing != 0) {
                                                        closingText = closing > 0 ?
                                                            'Dr' : 'Cr';
                                                    }
                                                    const groupUrl =
                                                        "{{ route('trial_balance') }}/" +
                                                        data['data'][i].id;
                                                    if (data['data'][i].name ==
                                                        "Reserves & Surplus") {
                                                        html += `
														<tr class="trail-sub-list-open expandable parent-${data['id']}" id="${data['data'][i].id}">
															<input type="hidden" id="check${data['data'][i].id}">
															<td style="padding-left: ${padding}px">
																<a href="#" class="trail-open-new-listplus-sub-btn text-dark expand exp${data['data'][i].id}" data-id="${data['data'][i].id}">
																	<i data-feather='plus-circle'></i>
																</a>
																<a href="#" class="trail-open-new-listminus-sub-btn text-dark collapse" style="display:none;">
																	<i data-feather='minus-circle'></i>
																</a>
																<span id="name${data['data'][i].id}">${data['data'][i].name}</span>
															</td>
															<td>${parseFloat(reservesSurplus['closingFinal']).toLocaleString('en-IN')} ${reservesSurplus['closing_type']}</td>
														</tr>`;
                                                    } else {
                                                        html += `
														<tr class="trail-sub-list-open expandable parent-${data['id']}" id="${data['data'][i].id}">
															<input type="hidden" id="check${data['data'][i].id}">
															<td style="padding-left: ${padding}px">
																<a href="#" class="trail-open-new-listplus-sub-btn text-dark expand exp${data['data'][i].id}" data-id="${data['data'][i].id}">
																	<i data-feather='plus-circle'></i>
																</a>
																<a href="#" class="trail-open-new-listminus-sub-btn text-dark collapse" style="display:none;">
																	<i data-feather='minus-circle'></i>
																</a>
																<a class="urls" href="${groupUrl}">
																	${data['data'][i].name}
																</a>
															</td>

                                                        <td>${parseFloat(closing < 0 ? -closing : closing).toLocaleString('en-IN')} ${closingText}</td>
														</tr>`;
                                                    }
                                                }
                                            } else {
                                                for (let i = 0; i < data['data']
                                                    .length; i++) {
                                                    const padding =
                                                        getIncrementalPadding(
                                                            parentPadding);
                                                    var closingText = '';
                                                    const closing = data['data'][i]
                                                        .open + (data['data'][i]
                                                            .details_sum_debit_amt -
                                                            data['data'][i]
                                                            .details_sum_credit_amt);
                                                    if (closing != 0) {
                                                        closingText = closing > 0 ?
                                                            'Dr' : 'Cr';
                                                    }
                                                    const ledgerUrl =
                                                        "{{ url('trailLedger') }}/" +
                                                        data['data'][i].group_id;

                                                    html += `
														<tr class="trail-sub-list-open parent-${data['id']}">
															<td style="padding-left: ${padding}px">
																<i data-feather='arrow-right'></i>${data['data'][i].name}
															</td>

                                                        <td>${parseFloat(closing < 0 ? -closing : closing).toLocaleString('en-IN')} ${closingText}</td>
														</tr>`;
                                                    tot_debt += data['data'][i]
                                                        .details_sum_debit_amt;
                                                    tot_credit += data['data'][i]
                                                        .details_sum_credit_amt;
                                                }
                                                console.log(tot_credit, tot_debt);

                                            }


                                            $('#' + data['id']).closest('tr').after(
                                                html);
                                        }
                                    }
                                });
                            }

                            if (feather) {
                                feather.replace({
                                    width: 14,
                                    height: 14
                                });
                            }
                            calculate_cr_dr();
                        }
                    });
                }

                $('.collapse').show();
                $('.expandable').show();
            });

            // Collapse All rows
            $('#collapse-all').click(function() {
                $('#tranferLedger tbody tr').each(function() {
                    const id = $(this).attr('id');
                    if (id) {
                        collapseChildren(id); // Collapse all children for each parent row
                    }
                });
                $('.collapse').hide();
                $('.expand').show();
            });

            // Recursive collapse function
            function collapseChildren(parentId) {
                $(`.parent-${parentId}`).each(function() {
                    const childId = $(this).attr('id');
                    $(this).hide(); // Hide the child row
                    $(this).find('.collapse').hide(); // Hide the collapse icon
                    $(this).find('.expand').show(); // Show the expand icon
                    collapseChildren(childId); // Recursively collapse the child's children
                });
            }
        });
        // selected arrow using down, up key
        $(document).ready(function() {
            let selectedRow = null;

            function setSelectedRow(row) {
                if (selectedRow) {
                    selectedRow.removeClass('trselected');
                }
                selectedRow = row;
                selectedRow.addClass('trselected');
            }

            function expandRow(row) {
                const id = row.attr('id');
                $('.parent-' + id).show();
                row.find('.expand').hide();
                row.find('.collapse').show();
            }

            function collapseRow(row) {
                const id = row.attr('id');
                collapseChildren(id);
                row.find('.expand').show();
                row.find('.collapse').hide();
            }

            function collapseChildren(parentId) {
                $(`.parent-${parentId}`).each(function() {
                    const childId = $(this).attr('id');
                    $(this).hide();
                    $(this).find('.collapse').hide();
                    $(this).find('.expand').show();
                    collapseChildren(childId);
                });
            }

            // Arrow key navigation
            $(document).keydown(function(e) {
                const rows = $('#tranferLedger tbody tr');
                if (rows.length === 0) return;

                let currentIndex = rows.index(selectedRow);
                let nextIndex = currentIndex;

                switch (e.which) {
                    case 38: // Up arrow key
                        if (currentIndex > 0) {
                            nextIndex = currentIndex - 1;
                            while (nextIndex >= 0 && rows.eq(nextIndex).is(':hidden')) {
                                nextIndex--;
                            }
                            if (nextIndex >= 0) {
                                setSelectedRow(rows.eq(nextIndex));
                            }
                        }
                        break;
                    case 40: // Down arrow key
                        if (currentIndex < rows.length - 1) {
                            nextIndex = currentIndex + 1;
                            while (nextIndex < rows.length && rows.eq(nextIndex).is(':hidden')) {
                                nextIndex++;
                            }
                            if (nextIndex < rows.length) {
                                setSelectedRow(rows.eq(nextIndex));
                            }
                        }
                        break;
                    case 37: // Left arrow key
                        if (selectedRow) {
                            collapseRow(selectedRow);
                        }
                        break;
                    case 39: // Right arrow key
                        if (selectedRow) {
                            expandRow(selectedRow);
                        }
                        break;
                }
            });


        });
        document.querySelectorAll('.swal-action-btn').forEach(button => {
            button.addEventListener('click', function() {
                console.log('save button hit')
                let bodyData = {};
                const actionType = this.getAttribute('data-type');
                const url = this.getAttribute('data-url');
                const fyValue = $('#fyear_id').val();

                let swalOptions = {
                    title: 'Are you sure?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                };

                if (actionType === 'close') {
                    swalOptions.text =
                        "You are about to close the Financial Year. This action cannot be undone!";
                    swalOptions.confirmButtonText = 'Yes, close it!';

                } else if (actionType === 'delete') {
                    swalOptions.text = "You are about to remove the authorized Users!";
                    swalOptions.confirmButtonText = 'Yes, remove it!';
                    // const selected = Array.from(document.getElementById('authorize').selectedOptions)
                    //     .map(option => option.value);

                    // console.log(bodyData);
                } else if (actionType === 'lock') {
                    swalOptions.text = "Are you sure you want to lock the current Financial Year?";
                    swalOptions.confirmButtonText = 'Yes, lock it!';
                    bodyData.lock_fy = true; // equivalent to 1
                } else if (actionType === 'unlock') {
                    swalOptions.text = "Are you sure you want to unlock the current Financial Year?";
                    swalOptions.confirmButtonText = 'Yes, unlock it!';
                    bodyData.lock_fy = false; // equivalent to 0
                }

                if (fyValue && fyValue.trim() !== "") {
                    bodyData.fyear = fyValue;
                }

                Swal.fire(swalOptions).then((result) => {
                    if (result.isConfirmed) {
                    $('.preloader').show();
                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify(bodyData)
                            })
                            .then(response => response.json())
                            .then(data => {
                                $('.preloader').hide();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message ||
                                        'Action completed successfully!',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    $('.preloader').show();
                                    setTimeout(() => location.reload(), 1000);
                                });
                            })
                            .catch(error => {
                                $('.preloader').hide();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Something went wrong.',
                                });
                                console.error(error);
                            });
                    }
                });
            });
        });
    </script>
@endsection
