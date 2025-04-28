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
                            <h2 class="content-header-title float-start mb-0">New Customer</h2>
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
                        <button class="btn btn-primary btn-sm"><i data-feather="check-circle"></i> Create</button>
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
                                                <label class="form-label">Customer Code</label>
                                            </div>

                                            <div class="col-md-6">
                                                <input type="text" class="form-control" value="CUST001" />
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Customer Type</label>
                                            </div>

                                            <div class="col-md-5">
                                                <div class="demo-inline-spacing">
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" id="customColorRadio1"
                                                            name="customColorRadio1" class="form-check-input"
                                                            checked="">
                                                        <label class="form-check-label fw-bolder"
                                                            for="customColorRadio1">Organisation</label>
                                                    </div>
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" id="customColorRadio2"
                                                            name="customColorRadio1" class="form-check-input">
                                                        <label class="form-check-label fw-bolder"
                                                            for="customColorRadio2">Individual</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Orgnization Type</label>
                                            </div>

                                            <div class="col-md-5">
                                                <select class="form-select">
                                                    <option>Select</option>
                                                    <option>Public Limited</option>
                                                    <option>Private Limited</option>
                                                    <option>Proprietor</option>
                                                    <option>Partnership</option>
                                                    <option>Small Enterprise</option>
                                                    <option>Medium Enterprise</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Primary Contact</label>
                                            </div>

                                            <div class="col-md-2 pe-sm-0">
                                                <select class="form-select">
                                                    <option>Title</option>
                                                    <option>Mr.</option>
                                                    <option>Mrs.</option>
                                                    <option>Ms.</option>
                                                    <option>Miss.</option>
                                                    <option>Dr.</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Primary Name</label>
                                            </div>

                                            <div class="col-md-3 pe-sm-0">
                                                <input type="text" class="form-control" placeholder="First Name" />
                                            </div>
                                            <div class="col-md-3 pe-sm-0">
                                                <input type="text" class="form-control" placeholder="Middle Name" />
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" placeholder="Last Name" />
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Company Name</label>
                                            </div>

                                            <div class="col-md-6">
                                                <input type="text" class="form-control" />
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Customer Display Name <span
                                                        class="text-danger">*</span></label>
                                            </div>

                                            <div class="col-md-6">
                                                <input type="text" class="form-control" />
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Parent Mapping</label>
                                            </div>

                                            <div class="col-md-6">
                                                <select class="form-select">
                                                    <option>Select</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Category Mapping</label>
                                            </div>

                                            <div class="col-md-3 pe-sm-0">
                                                <select class="form-select">
                                                    <option>Catgeory</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select class="form-select">
                                                    <option>Sub-Category</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Supporting Documents</label>
                                            </div>

                                            <div class="col-md-6">
                                                <input type="file" class="form-control" />
                                            </div>
                                        </div>



                                    </div>

                                    <div class="col-md-3 border-start">
                                        <div class="row align-items-center mb-2">
                                            <div class="col-md-12">
                                                <label class="form-label text-primary"><strong>Status</strong></label>
                                                <div class="demo-inline-spacing">
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" id="customColorRadio3"
                                                            name="customColorRadio3" class="form-check-input"
                                                            checked="">
                                                        <label class="form-check-label fw-bolder"
                                                            for="customColorRadio3">Active</label>
                                                    </div>
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" id="customColorRadio4"
                                                            name="customColorRadio3" class="form-check-input">
                                                        <label class="form-check-label fw-bolder"
                                                            for="customColorRadio4">Inactive</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-2">
                                            <div class="col-md-12">
                                                <label class="form-label text-primary"><strong>Stop
                                                        Billing</strong></label>
                                                <div class="demo-inline-spacing">
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" id="customColorRadio5"
                                                            name="customColorRadio4" class="form-check-input"
                                                            checked="">
                                                        <label class="form-check-label fw-bolder"
                                                            for="customColorRadio5">Yes</label>
                                                    </div>
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" id="customColorRadio6"
                                                            name="customColorRadio4" class="form-check-input">
                                                        <label class="form-check-label fw-bolder"
                                                            for="customColorRadio6">No</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="row align-items-center mb-2">
                                            <div class="col-md-12">
                                                <label class="form-label text-primary"><strong>Stop
                                                        Ordering</strong></label>
                                                <div class="demo-inline-spacing">
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" id="customColorRadio7" name="Ordering"
                                                            class="form-check-input" checked="">
                                                        <label class="form-check-label fw-bolder"
                                                            for="customColorRadio7">Yes</label>
                                                    </div>
                                                    <div class="form-check form-check-primary mt-25">
                                                        <input type="radio" id="customColorRadio8" name="Ordering"
                                                            class="form-check-input">
                                                        <label class="form-check-label fw-bolder"
                                                            for="customColorRadio8">No</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>



                                    </div>
                                </div>





                                <div class="mt-1">
                                    <ul class="nav nav-tabs border-bottom mt-25" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#payment">Other
                                                Details</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#attachment">Address</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#Shipping">Add Shipping
                                                Addresses</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#Financial">Financial</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#amend">Contact Persons</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#schedule">India
                                                compliances</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#send">Bank Info</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#latestrates">Notes</a>
                                        </li>

                                    </ul>

                                    <div class="tab-content pb-1 px-1">
                                        <div class="tab-pane active" id="payment">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Related Party</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                        <input type="checkbox" class="form-check-input" id="Related"
                                                            checked="">
                                                        <label class="form-check-label" for="Related">Yes/No</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Customer Email</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="input-group input-group-merge">
                                                        <span class="input-group-text" id="basic-addon5"><i
                                                                data-feather='mail'></i></span>
                                                        <input type="text" class="form-control" placeholder="">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Customer Phone</label>
                                                </div>

                                                <div class="col-md-2">
                                                    <div class="input-group input-group-merge">
                                                        <span class="input-group-text" id="basic-addon5"><i
                                                                data-feather='phone'></i></span>
                                                        <input type="text" class="form-control"
                                                            placeholder="Work Phone">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="input-group input-group-merge">
                                                        <span class="input-group-text" id="basic-addon5"><i
                                                                data-feather='smartphone'></i></span>
                                                        <input type="text" class="form-control" placeholder="Mobile">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Customer Whatsapp Number</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="input-group input-group-merge">
                                                        <span class="input-group-text" id="basic-addon5"><i
                                                                data-feather='phone'></i></span>
                                                        <input type="text" class="form-control">
                                                    </div>
                                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                        <input type="checkbox" class="form-check-input" id="colorCheck1"
                                                            checked="">
                                                        <label class="form-check-label" for="colorCheck1">Same as Mobile
                                                            No.</label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Notification</label>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="demo-inline-spacing">
                                                        <div
                                                            class="form-check form-check-primary mt-25 custom-checkbox">
                                                            <input type="checkbox" class="form-check-input" id="Email">
                                                            <label class="form-check-label" for="Email">Email</label>
                                                        </div>
                                                        <div
                                                            class="form-check form-check-primary mt-25 custom-checkbox">
                                                            <input type="checkbox" class="form-check-input" id="SMS">
                                                            <label class="form-check-label" for="SMS">SMS</label>
                                                        </div>
                                                        <div
                                                            class="form-check form-check-primary mt-25 custom-checkbox">
                                                            <input type="checkbox" class="form-check-input"
                                                                id="Whatsapp">
                                                            <label class="form-check-label"
                                                                for="Whatsapp">Whatsapp</label>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">PAN</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="text" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Tin No.</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="text" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Aadhar No.</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="text" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Currency</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <select class="form-select">
                                                        <option>Select</option>
                                                        <option selected>INR - Indian Rupee</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Opening Balance</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"
                                                            id="basic-addon1">INR</span>
                                                        <input type="text" class="form-control">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Payment Terms</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <select class="form-select">
                                                        <option>Select</option>
                                                        <option selected>Due on Receipt</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Upload PAN & Aadhar Document</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="file" class="form-control" multiple />
                                                </div>
                                            </div>


                                        </div>
                                        <div class="tab-pane" id="attachment">
                                            <div class="row">
                                                <div class="col-md-6">

                                                    <h5 class="mt-1 mb-4 text-dark"><strong>Billing Address</strong>
                                                    </h5>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Country/Region</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <select class="form-select">
                                                                <option>Select</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Address</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <textarea class="form-control"
                                                                placeholder="Street 1"></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            &nbsp;
                                                        </div>

                                                        <div class="col-md-6">
                                                            <textarea class="form-control"
                                                                placeholder="Street 2"></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">City</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">State</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <select class="form-select">
                                                                <option>Select</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Pin Code</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Phone</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Fax Number</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>


                                                </div>

                                                <div class="col-md-6">

                                                    <div class="mt-1 mb-2 d-flex flex-column">
                                                        <h5 class="text-dark mb-0 me-1"><strong>Primary Shipping
                                                                Address</strong></h5>

                                                        <div
                                                            class="form-check form-check-primary mt-25 custom-checkbox">
                                                            <input type="checkbox" class="form-check-input"
                                                                id="colorCheck2" checked="">
                                                            <label class="form-check-label" for="colorCheck2">Same As
                                                                Billing Address</label>
                                                        </div>
                                                    </div>


                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Country/Region</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <select class="form-select">
                                                                <option>Select</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Address</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <textarea class="form-control"
                                                                placeholder="Street 1"></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            &nbsp;
                                                        </div>

                                                        <div class="col-md-6">
                                                            <textarea class="form-control"
                                                                placeholder="Street 2"></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">City</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">State</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <select class="form-select">
                                                                <option>Select</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Pin Code</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Phone</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Fax Number</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="Shipping">
                                            <div class="table-responsive">
                                                <table
                                                    class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Country/Region</th>
                                                            <th>Address</th>
                                                            <th>City</th>
                                                            <th>State</th>
                                                            <th>Pin Code</th>
                                                            <th>Phone</th>
                                                            <th>Fax Number</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>#</td>
                                                            <td>
                                                                <select class="form-select mw-100">
                                                                    <option>Select</option>
                                                                </select>
                                                            </td>
                                                            <td><input type="text" class="form-control mw-100"></td>
                                                            <td><input type="text" class="form-control mw-100"></td>
                                                            <td>
                                                                <select class="form-select mw-100">
                                                                    <option>Select</option>
                                                                </select>
                                                            </td>
                                                            <td><input type="text" class="form-control mw-100"></td>
                                                            <td><input type="text" class="form-control mw-100"></td>
                                                            <td><input type="text" class="form-control mw-100"></td>
                                                            <td><a href="#" class="text-primary"><i
                                                                        data-feather="plus-square"
                                                                        class="me-50"></i></a></td>
                                                        </tr>

                                                        <tr>
                                                            <td>1</td>
                                                            <td>India</td>
                                                            <td>Plot No. 14</td>
                                                            <td>Gautam Budh Nagar</td>
                                                            <td>Noida</td>
                                                            <td>201301</td>
                                                            <td>9876787656</td>
                                                            <td>-</td>
                                                            <td><a href="#" class="text-danger"><i
                                                                        data-feather="trash-2" class="me-50"></i></a>
                                                            </td>
                                                        </tr>


                                                    </tbody>


                                                </table>
                                            </div>

                                            <a href="#" class="text-primary add-contactpeontxt"><i
                                                    data-feather='plus'></i> Add New Address</a>
                                        </div>
                                        <div class="tab-pane" id="Financial">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Leader Name</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <select class="form-select">
                                                        <option>Select</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Pricing Type</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <select class="form-select">
                                                        <option>Select</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Credit Limit</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="text" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Credit Days</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="text" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Interest %</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="text" class="form-control" />
                                                </div>
                                            </div>



                                        </div>
                                        <div class="tab-pane" id="amend">
                                            <div class="table-responsive">
                                                <table class="table myrequesttablecbox table-striped ">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th class="px-1">Salutation</th>
                                                            <th class="px-1">Name</th>
                                                            <th class="px-1">Email</th>
                                                            <th class="px-1">Mobile</th>
                                                            <th class="px-1">Work Phone</th>
                                                            <th class="px-1">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr valign="top">
                                                            <td>1</td>
                                                            <td class="px-1">
                                                                <select class="form-select">
                                                                    <option>Select</option>
                                                                </select>
                                                            </td>
                                                            <td class="px-1"><input type="text" class="form-control">
                                                            </td>
                                                            <td class="px-1"><input type="text" class="form-control">
                                                            </td>
                                                            <td class="px-1"><input type="text" class="form-control">
                                                            </td>
                                                            <td class="px-1"><input type="text" class="form-control">
                                                            </td>

                                                            <td class="px-1">
                                                                <a href="#" class="text-danger"><i
                                                                        data-feather="trash-2" class="me-50"></i></a>
                                                            </td>
                                                        </tr>

                                                    </tbody>


                                                </table>
                                            </div>

                                            <a href="#" class="text-primary add-contactpeontxt"><i
                                                    data-feather='plus'></i> Add Contact Person</a>
                                        </div>
                                        <div class="tab-pane" id="schedule">
                                            <div class="row">
                                                <div class="col-md-6">

                                                    <h5 class="mt-1 mb-2 text-dark"><strong>TCS Details</strong></h5>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">TCS Applicable</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div
                                                                class="form-check form-check-primary mt-25 custom-checkbox">
                                                                <input type="checkbox" class="form-check-input"
                                                                    id="colorCheck1" checked="">
                                                                <label class="form-check-label"
                                                                    for="colorCheck1">Yes/No</label>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Wef Date</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="date" class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">TCS Certificate No.</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">TCS Tax Percentage</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">TCS Category</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">TCS Value Cab</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Threshold Limit</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Business</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>




                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">TAN Number</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>


                                                </div>

                                                <div class="col-md-6">

                                                    <div class="mt-1 mb-2 d-flex flex-column">
                                                        <h5 class="text-dark mb-0 me-1"><strong>GST Info</strong></h5>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">GST Applicable</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="demo-inline-spacing">
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="Registered" name="gstappl"
                                                                        class="form-check-input" checked="">
                                                                    <label class="form-check-label fw-bolder"
                                                                        for="Registered">Registered</label>
                                                                </div>
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="nonRegistered"
                                                                        name="gstappl" class="form-check-input">
                                                                    <label class="form-check-label fw-bolder"
                                                                        for="nonRegistered">Non-Registered</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">GSTIN No.</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">GST Registered Name</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="text" class="form-control" />
                                                        </div>
                                                    </div>


                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">GSTIN Reg. Date</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="date" class="form-control" />
                                                        </div>

                                                    </div>



                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-4">
                                                            <label class="form-label">Upload Certificate</label>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <input type="file" class="form-control" />
                                                        </div>
                                                    </div>





                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="send">

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Bank Name</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="text" class="form-control" />
                                                </div>
                                            </div>


                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Benificiary Name</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="text" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Account Number</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="text" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Re-enter Account No.</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="text" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">IFSC Code</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="text" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Cancel Cheque</label>
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="file" class="form-control" />
                                                </div>
                                            </div>


                                        </div>
                                        <div class="tab-pane" id="latestrates">
                                            <label class="form-label">Notes (For Internal Use)</label>
                                            <textarea class="form-control" placeholder="Enter Notes...."></textarea>

                                            <div class="table-responsive mt-1">
                                                <table class="table myrequesttablecbox table-striped ">
                                                    <thead>
                                                        <tr>
                                                            <th class="px-1">#</th>
                                                            <th class="px-1">Name</th>
                                                            <th class="px-1">Date</th>
                                                            <th class="px-1">Remarks</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr valign="top">
                                                            <td>1</td>
                                                            <td class="px-1">Nishu Garg</td>
                                                            <td class="px-1">25-07-2024</td>
                                                            <td class="px-1">Customer Info Upadated</td>
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
                </div>
                <!-- Modal to add new record -->

            </section>


        </div>
    </div>
</div>
<!-- END: Content-->
@endsection