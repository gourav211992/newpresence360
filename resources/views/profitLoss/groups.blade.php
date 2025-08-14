@extends('layouts.app')

@section('content')
<!-- BEGIN: Content-->
<div class="app-content content ">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header pocreate-sticky">
            <div class="row">
                <div class="content-header-left col-md-6 col-6 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">New Group</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('ledger-groups.index') }}">Groups</a>
                                    </li>
                                    <li class="breadcrumb-item active">Add New</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">

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
                                        <div class="newheader border-bottom mb-2 pb-25">
                                            <h4 class="card-title text-theme">Basic Information</h4>
                                            <p class="card-text">Fill the details</p>
                                        </div>
                                    </div>

                                    <div class="col-md-9">
                                        <form action="{{ route('finance.plgroups.store') }}" method="POST">
                                            @csrf

                                            @php
                                                $openingStock=[]; $purchaseAccounts=[]; $directExpenses=[]; $indirectExpenses=[]; $salesAccounts=[]; $directIncome=[]; $indirectIncome=[];
                                                foreach ($plGroups as $plGroup) {
                                                    if($plGroup->name == "Opening Stock"){
                                                        $openingStock=$plGroup->group_ids;
                                                    }
                                                    if($plGroup->name == "Purchase Accounts"){
                                                        $purchaseAccounts=$plGroup->group_ids;
                                                    }
                                                    if($plGroup->name == "Direct Expenses"){
                                                        $directExpenses=$plGroup->group_ids;
                                                    }
                                                    if($plGroup->name == "Indirect Expenses"){
                                                        $indirectExpenses=$plGroup->group_ids;
                                                    }
                                                    if($plGroup->name == "Sales Accounts"){
                                                        $salesAccounts=$plGroup->group_ids;
                                                    }
                                                    if($plGroup->name == "Direct Income"){
                                                        $directIncome=$plGroup->group_ids;
                                                    }
                                                    if($plGroup->name == "Indirect Income"){
                                                        $indirectIncome=$plGroup->group_ids;
                                                    }
                                                }
                                            @endphp

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Opening Stock</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select mw-100 select2 userSelect" name="openingStock[]" multiple required>
                                                        <option disabled value="">Select Group</option>
                                                        @foreach ($groups as $group)
                                                            <option value="{{ $group->id }}" @if(in_array($group->id,$openingStock)) selected @endif>{{ $group->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Purchase Accounts</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select mw-100 select2 userSelect" name="purchaseAccounts[]" multiple required>
                                                        <option disabled value="">Select Group</option>
                                                        @foreach ($groups as $group)
                                                            <option value="{{ $group->id }}" @if(in_array($group->id,$purchaseAccounts)) selected @endif>{{ $group->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Direct Expenses</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select mw-100 select2 userSelect" name="directExpenses[]" multiple required>
                                                        <option disabled value="">Select Group</option>
                                                        @foreach ($groups as $group)
                                                            <option value="{{ $group->id }}" @if(in_array($group->id,$directExpenses)) selected @endif>{{ $group->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Indirect Expenses</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select mw-100 select2 userSelect" name="indirectExpenses[]" multiple required>
                                                        <option disabled value="">Select Group</option>
                                                        @foreach ($groups as $group)
                                                            <option value="{{ $group->id }}" @if(in_array($group->id,$indirectExpenses)) selected @endif>{{ $group->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Sales Accounts</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select mw-100 select2 userSelect" name="salesAccounts[]" multiple required>
                                                        <option disabled value="">Select Group</option>
                                                        @foreach ($groups as $group)
                                                            <option value="{{ $group->id }}" @if(in_array($group->id,$salesAccounts)) selected @endif>{{ $group->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Direct Income</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select mw-100 select2 userSelect" name="directIncome[]" multiple required>
                                                        <option disabled value="">Select Group</option>
                                                        @foreach ($groups as $group)
                                                            <option value="{{ $group->id }}" @if(in_array($group->id,$directIncome)) selected @endif>{{ $group->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Indirect Income</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select mw-100 select2 userSelect" name="indirectIncome[]" multiple required>
                                                        <option disabled value="">Select Group</option>
                                                        @foreach ($groups as $group)
                                                            <option value="{{ $group->id }}" @if(in_array($group->id,$indirectIncome)) selected @endif>{{ $group->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <button type="button" onClick="javascript: history.go(-1)"
                                                    class="btn btn-secondary btn-sm">
                                                    <i data-feather="arrow-left-circle"></i> Back
                                                </button>
                                                <button type="submit" class="btn btn-primary btn-sm ms-1">
                                                    <i data-feather="check-circle"></i> Submit
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<!-- END: Content-->

@section('scripts')
<script>

</script>
@endsection
@endsection
