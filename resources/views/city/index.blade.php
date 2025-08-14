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
                                <h2 class="content-header-title float-start mb-0">City Setup</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a>
                                        </li>
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
                            <button class="btn btn-primary btn-sm"><i data-feather="check-circle"></i> Save</button>
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



                                            <div class="newheader d-flex justify-content-between align-items-end">
                                                <div class="header-left">
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                                <div>
                                                    <a href="#" class="text-primary add-contactpeontxt"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-plus">
                                                            <line x1="12" y1="5" x2="12"
                                                                y2="19"></line>
                                                            <line x1="5" y1="12" x2="19"
                                                                y2="12"></line>
                                                        </svg> Add New Item</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>



                                    <div class="row">
                                        <div class="col-md-12">


                                            <div class="table-responsive">
                                                <table
                                                    class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Country</th>
                                                            <th>State</th>
                                                            <th>State Abbriviation</th>
                                                            <th>City</th>
                                                            <th>Region</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>1</td>
                                                            <td><input type="text" class="form-control mw-100" /></td>
                                                            <td><input type="text" class="form-control mw-100" /></td>
                                                            <td><input type="text" class="form-control mw-100" /></td>
                                                            <td><input type="text" class="form-control mw-100" /></td>
                                                            <td><input type="text" class="form-control mw-100" /></td>
                                                            <td>
                                                                <a href="#" class="text-danger"><i
                                                                        data-feather="trash-2" class="me-50"></i></a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>2</td>
                                                            <td><input type="text" class="form-control mw-100" /></td>
                                                            <td><input type="text" class="form-control mw-100" /></td>
                                                            <td><input type="text" class="form-control mw-100" /></td>
                                                            <td><input type="text" class="form-control mw-100" /></td>
                                                            <td><input type="text" class="form-control mw-100" /></td>
                                                            <td>
                                                                <a href="#" class="text-danger"><i
                                                                        data-feather="trash-2" class="me-50"></i></a>
                                                            </td>
                                                        </tr>





                                                    </tbody>


                                                </table>
                                            </div>











                                        </div>


                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal to add new record -->

                </section>


            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection
