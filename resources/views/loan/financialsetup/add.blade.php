@extends('layouts.app')

@section('content')
<!-- BEGIN: Content-->
<div class="app-content content ">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">

        <form action="{{ route('loan.financial-setup-create') }}" method="POST">
            @csrf
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">New Account</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('loan.financial-setup') }}">Financial Setup</a></li>
                                        <li class="breadcrumb-item active">Add New</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm"><i
                                    data-feather="arrow-left-circle"></i> Back</button>
                            <button type="submit" class="btn btn-primary btn-sm"><i
                                    data-feather="check-circle"></i>Submit</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">

                            <div class="card">
                                <div class="card-body customernewsection-form">

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="newheader  border-bottom mb-2 pb-25">
                                                <h4 class="card-title text-theme">Basic Information</h4>
                                                <p class="card-text">Fill the details</p>
                                            </div>
                                        </div>
                                        <div class="col-md-9">

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Processing Fee Income Account</label>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-select select2" id="ledger-select" name="pro_ledger_id" required>
                                                        <option value="">Please select</option>
                                                    @foreach ($ledgers as $ledger)
                                                        @php
                                                            // Decode group_ids if it's a JSON string or ensure it's an array
                                                            $groupIds = is_string($ledger->ledger_group_id) ? json_decode($ledger->ledger_group_id) : $ledger->ledger_group_id;
                                                            
                                                            // Convert groupIds array to a comma-separated string for data-group-ids
                                                            $groupIdsString = implode(',', (array) $groupIds);
                                                        @endphp

                                                        <option value="{{ $ledger->id }}" data-group-ids="{{ $groupIdsString }}" 
                                                            @if(old('ledger_id') && in_array($ledger->id, (array) old('pro_ledger_id'))) selected @endif>
                                                            {{ $ledger->name }}
                                                        </option>
                                                    @endforeach

                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-select select2" id="group-select" name="pro_ledger_group_id" required>
                                                    
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Loan Disbursement Account</label>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-select select2" id="dis-select" name="dis_ledger_id" required>
                                                    <option value="">Please select</option>
                                                    @foreach ($ledgers as $ledger)
                                                        @php
                                                            // Decode group_ids if it's a JSON string or ensure it's an array
                                                            $groupIds = is_string($ledger->ledger_group_id) ? json_decode($ledger->ledger_group_id) : $ledger->ledger_group_id;
                                                            
                                                            // Convert groupIds array to a comma-separated string for data-group-ids
                                                            $groupIdsString = implode(',', (array) $groupIds);
                                                        @endphp

                                                        <option value="{{ $ledger->id }}" data-group-ids="{{ $groupIdsString }}" 
                                                            @if(old('ledger_id') && in_array($ledger->id, (array) old('disledger_id'))) selected @endif>
                                                            {{ $ledger->name }}
                                                        </option>
                                                    @endforeach

                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-select select2" id="dis-groupselect" name="dis_ledger_group_id" required>
                                                    
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Interest Income Account</label>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-select select2" id="int-select" name="int_ledger_id" required>
                                                    <option value="">Please select</option>
                                                    @foreach ($ledgers as $ledger)
                                                        @php
                                                            // Decode group_ids if it's a JSON string or ensure it's an array
                                                            $groupIds = is_string($ledger->ledger_group_id) ? json_decode($ledger->ledger_group_id) : $ledger->ledger_group_id;
                                                            
                                                            // Convert groupIds array to a comma-separated string for data-group-ids
                                                            $groupIdsString = implode(',', (array) $groupIds);
                                                        @endphp

                                                        <option value="{{ $ledger->id }}" data-group-ids="{{ $groupIdsString }}" 
                                                            @if(old('ledger_id') && in_array($ledger->id, (array) old('intledger_id'))) selected @endif>
                                                            {{ $ledger->name }}
                                                        </option>
                                                    @endforeach

                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-select select2" id="int-groupselect" name="int_ledger_group_id" required>
                                                    
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Loan Writeoff Account</label>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-select select2" id="wri-select" name="wri_ledger_id" required>
                                                    <option value="">Please select</option>
                                                    @foreach ($ledgers as $ledger)
                                                        @php
                                                            // Decode group_ids if it's a JSON string or ensure it's an array
                                                            $groupIds = is_string($ledger->ledger_group_id) ? json_decode($ledger->ledger_group_id) : $ledger->ledger_group_id;
                                                            
                                                            // Convert groupIds array to a comma-separated string for data-group-ids
                                                            $groupIdsString = implode(',', (array) $groupIds);
                                                        @endphp

                                                        <option value="{{ $ledger->id }}" data-group-ids="{{ $groupIdsString }}" 
                                                            @if(old('ledger_id') && in_array($ledger->id, (array) old('wriledger_id'))) selected @endif>
                                                            {{ $ledger->name }}
                                                        </option>
                                                    @endforeach

                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <select class="form-select select2" id="wri-groupselect" name="wri_ledger_group_id" required>
                                                    
                                                    </select>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="col-md-3 border-start">
                                            <div class="row align-items-center mb-2">
                                                <div class="col-md-12">
                                                    <label
                                                        class="form-label text-primary"><strong>Status</strong></label>
                                                    <div class="demo-inline-spacing">
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="customColorRadio3" value="1"
                                                                name="status" class="form-check-input" checked>
                                                            <label class="form-check-label fw-bolder"
                                                                for="customColorRadio3">Active</label>
                                                        </div>
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="customColorRadio4" value="0"
                                                                name="status" class="form-check-input">
                                                            <label class="form-check-label fw-bolder"
                                                                for="customColorRadio4">Inactive</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </form>

    </div>
</div>
<!-- END: Content-->
 @section('scripts')
 <script>
    
    document.addEventListener('DOMContentLoaded', function () {
    const ledgerSelect = document.getElementById('ledger-select');
    const groupSelect = document.getElementById('group-select');
    const disSelect = document.getElementById('dis-select');
    const disgroupSelect = document.getElementById('dis-groupselect');
    const intSelect = document.getElementById('int-select');
    const intgroupSelect = document.getElementById('int-groupselect');
    const wriSelect = document.getElementById('wri-select');
    const wrigroupSelect = document.getElementById('wri-groupselect');


    if (!ledgerSelect || !groupSelect) {
        console.error('Elements not found: ledger-select or group-select');
        return;
    }

    const allGroupOptions = JSON.parse('@json($groups)');

    // Initialize Select2 for ledger-select
    $('#ledger-select').select2();

    // Attach event listener after initializing Select2
    $('#ledger-select').on('change', function () {
        const selectedLedgerOptions = Array.from(ledgerSelect.selectedOptions);
        const allowedGroupIds = new Set();

        // Collect all allowed group IDs from selected ledgers
        selectedLedgerOptions.forEach(option => {
            const groupIds = option.getAttribute('data-group-ids');
            if (groupIds) {
                groupIds.split(',').forEach(id => allowedGroupIds.add(id));
            }
        });

        console.log(allowedGroupIds);

        // Filter the group select options
        groupSelect.innerHTML = ''; // Clear current options
        allGroupOptions.forEach(group => {
            if (allowedGroupIds.has(String(group.id))) { // Ensure you're comparing the correct data types
                const option = document.createElement('option');
                option.value = group.id;
                option.text = group.name;
                groupSelect.appendChild(option); // Add valid options
            }
        });

        // Refresh Select2 UI for groupSelect
        $('#group-select').select2();
    });

    $('#dis-select').select2();

    // Attach event listener after initializing Select2
    $('#dis-select').on('change', function () {
        const selectedLedgerOptions = Array.from(disSelect.selectedOptions);
        const allowedGroupIds = new Set();

        // Collect all allowed group IDs from selected ledgers
        selectedLedgerOptions.forEach(option => {
            const groupIds = option.getAttribute('data-group-ids');
            if (groupIds) {
                groupIds.split(',').forEach(id => allowedGroupIds.add(id));
            }
        });

        // Filter the group select options
        disgroupSelect.innerHTML = ''; // Clear current options
        allGroupOptions.forEach(group => {
            if (allowedGroupIds.has(String(group.id))) { // Ensure you're comparing the correct data types
                const option = document.createElement('option');
                option.value = group.id;
                option.text = group.name;
                disgroupSelect.appendChild(option); // Add valid options
            }
        });

        // Refresh Select2 UI for groupSelect
        $('#dis-groupselect').select2();
    });

    $('#int-select').select2();

    // Attach event listener after initializing Select2
    $('#int-select').on('change', function () {
        const selectedLedgerOptions = Array.from(intSelect.selectedOptions);
        const allowedGroupIds = new Set();

        // Collect all allowed group IDs from selected ledgers
        selectedLedgerOptions.forEach(option => {
            const groupIds = option.getAttribute('data-group-ids');
            if (groupIds) {
                groupIds.split(',').forEach(id => allowedGroupIds.add(id));
            }
        });

        console.log(allowedGroupIds);

        // Filter the group select options
        intgroupSelect.innerHTML = ''; // Clear current options
        allGroupOptions.forEach(group => {
            if (allowedGroupIds.has(String(group.id))) { // Ensure you're comparing the correct data types
                const option = document.createElement('option');
                option.value = group.id;
                option.text = group.name;
                intgroupSelect.appendChild(option); // Add valid options
            }
        });

        // Refresh Select2 UI for groupSelect
        $('#int-groupselect').select2();
    });

    $('#wri-select').select2();

    // Attach event listener after initializing Select2
    $('#wri-select').on('change', function () {
        const selectedLedgerOptions = Array.from(wriSelect.selectedOptions);
        const allowedGroupIds = new Set();

        // Collect all allowed group IDs from selected ledgers
        selectedLedgerOptions.forEach(option => {
            const groupIds = option.getAttribute('data-group-ids');
            if (groupIds) {
                groupIds.split(',').forEach(id => allowedGroupIds.add(id));
            }
        });

        console.log(allowedGroupIds);

        // Filter the group select options
        wrigroupSelect.innerHTML = ''; // Clear current options
        allGroupOptions.forEach(group => {
            if (allowedGroupIds.has(String(group.id))) { // Ensure you're comparing the correct data types
                const option = document.createElement('option');
                option.value = group.id;
                option.text = group.name;
                wrigroupSelect.appendChild(option); // Add valid options
            }
        });

        // Refresh Select2 UI for groupSelect
        $('#wri-groupselect').select2();
    });
});


</script>

@endsection
@endsection
