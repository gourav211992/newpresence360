@extends('layouts.app')

@section('styles')
@endsection

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
                        <h2 class="content-header-title float-start mb-0">Vouchers</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>  
                                <li class="breadcrumb-item active">Vocuhers Upload Errors</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                <div class="form-group breadcrumb-right">   
                    <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>  
                </div>
            </div>
        </div>
        <div class="content-body">

            <div class="alert alert-success">
                Vouchers uploaded successfully. Vouchers failed to upload are listed below.
            </div>
            
            <section id="basic-datatable">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            
                            <div class="table-responsive">
                                <table class="datatables-basic table myrequesttablecbox "> 
                                    <thead>
                                        <tr>
                                            <th>Sr. No</th>
                                            <th>Error</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($errors as $index=>$item)
                                            <tr>
                                                <td>{{ $index+1 }}</td>
                                                <td class="text-dark">{{ $item }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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

@section('scripts')
<script>

</script>
@endsection
