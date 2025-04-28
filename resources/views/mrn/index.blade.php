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
                                <h2 class="content-header-title float-start mb-0">Material Receipt</h2>
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

                                    <div class="border-bottom mb-2 pb-25">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="newheader ">
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>


                                        </div>

                                    </div>




                                    <div class="row">
                                        <div class="col-md-12">

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Vendor <span
                                                            class="text-danger">*</span></label>
                                                </div>

                                                <div class="col-md-4">
                                                    <select class="form-select">
                                                        <option>DOW CHECMICAL (AUSTRALIA)</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="action-button mt-50">
                                                        <button data-bs-toggle="modal" data-bs-target="#rescdule"
                                                            class="btn btn-outline-primary btn-sm"><i
                                                                data-feather="plus-square"></i> Select Outstanding
                                                            PO</button>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">MRN No.</label>
                                                </div>

                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" value="OB-18" />
                                                </div>

                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">MRN Date</label>
                                                </div>

                                                <div class="col-md-4">
                                                    <input type="date" value="2024-07-24" class="form-control" />
                                                </div>

                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Series</label>
                                                </div>

                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" value="GRN 0B" />
                                                </div>

                                            </div>


                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Gate Entry No.</label>
                                                </div>

                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" />
                                                </div>

                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Gate Entry Date</label>
                                                </div>

                                                <div class="col-md-4">
                                                    <input type="date" class="form-control" />
                                                </div>

                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">E-Way Bill No.</label>
                                                </div>

                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" />
                                                </div>

                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Consignment No.</label>
                                                </div>

                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" />
                                                </div>

                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Supplier Invoice No.</label>
                                                </div>

                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" />
                                                </div>

                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Supplier Invoice Date</label>
                                                </div>

                                                <div class="col-md-4">
                                                    <input type="date" class="form-control" />
                                                </div>

                                            </div>


                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Transporter Name</label>
                                                </div>

                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" />
                                                </div>

                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Vehicle No.</label>
                                                </div>

                                                <div class="col-md-4">
                                                    <input type="text" class="form-control" />
                                                </div>

                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Bill to</label>
                                                </div>

                                                <div class="col-md-4">
                                                    <select class="form-select">
                                                        <option>Select</option>
                                                    </select>
                                                </div>

                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Ship to</label>
                                                </div>

                                                <div class="col-md-4">
                                                    <select class="form-select">
                                                        <option>Select</option>
                                                    </select>
                                                </div>

                                            </div>




                                            <div
                                                class="newheader d-flex justify-content-between align-items-end mt-2 border-top pt-2">
                                                <div class="header-left">
                                                    <h4 class="card-title text-theme">PO Item Wise Detail</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                                <div>
                                                    <a href="#" class="text-primary add-contactpeontxt"><i
                                                            data-feather='plus'></i> Add New Item</a>
                                                </div>
                                            </div>







                                            <div class="table-responsive">
                                                <table
                                                    class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>PO No.</th>
                                                            <th width="300px">Item</th>
                                                            <th>PO Qty</th>
                                                            <th>Receipt Qty</th>
                                                            <th>Acpt. Qty</th>
                                                            <th>Rej. Qty</th>
                                                            <th>Unit</th>
                                                            <th>Price</th>
                                                            <th>Value</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <div class="form-check form-check-inline me-0">
                                                                    <input class="form-check-input" type="radio"
                                                                        name="podetail" id="inlineCheckbox1">
                                                                </div>
                                                            </td>
                                                            <td><input type="text" disabled value="98763"
                                                                    class="form-control mw-100" /></td>
                                                            <td>
                                                                <input type="text" disabled value="POLYOL For Letter"
                                                                    class="form-control mw-100" />
                                                            </td>
                                                            <td><input type="text" disabled value="249.00"
                                                                    class="form-control mw-100" /></td>
                                                            <td><input type="text" value="249.00"
                                                                    class="form-control mw-100" /></td>
                                                            <td><input type="text" value="249.00"
                                                                    class="form-control mw-100" /></td>
                                                            <td><input type="text" value="0.00"
                                                                    class="form-control mw-100" /></td>
                                                            <td><input type="text" disabled value="KG"
                                                                    class="form-control mw-100" /></td>
                                                            <td><input type="text" disabled value="200.00"
                                                                    class="form-control mw-100" /></td>
                                                            <td><input type="text" disabled value="2500.00"
                                                                    class="form-control mw-100" /></td>

                                                            <td><a href="#" class="text-danger"><i
                                                                        data-feather="trash-2" class="me-50"></i></a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <div class="form-check form-check-inline me-0">
                                                                    <input class="form-check-input" type="radio"
                                                                        name="podetail" id="inlineCheckbox1">
                                                                </div>
                                                            </td>
                                                            <td><input type="text" disabled value="98763"
                                                                    class="form-control mw-100" /></td>
                                                            <td>
                                                                <input type="text" disabled value="POLYOL For Letter"
                                                                    class="form-control mw-100" />
                                                            </td>
                                                            <td><input type="text" disabled value="249.00"
                                                                    class="form-control mw-100" /></td>
                                                            <td><input type="text" value="249.00"
                                                                    class="form-control mw-100" /></td>
                                                            <td><input type="text" value="249.00"
                                                                    class="form-control mw-100" /></td>
                                                            <td><input type="text" value="0.00"
                                                                    class="form-control mw-100" /></td>
                                                            <td><input type="text" disabled value="KG"
                                                                    class="form-control mw-100" /></td>
                                                            <td><input type="text" disabled value="200.00"
                                                                    class="form-control mw-100" /></td>
                                                            <td><input type="text" disabled value="2500.00"
                                                                    class="form-control mw-100" /></td>
                                                            <td><a href="#" class="text-danger"><i
                                                                        data-feather="trash-2" class="me-50"></i></a>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td colspan="8">
                                                                <label class="form-label">Item Remarks</label>
                                                                <input type="text" class="form-control mw-100"
                                                                    value="VORANOL 3595 POLYOL" disabled />
                                                            </td>
                                                            <td class="text-end totalcustomer-sub-head"><strong>Sub
                                                                    Total</strong></td>

                                                            <td>0.00</td>
                                                            <td></td>
                                                        </tr>

                                                        <tr>
                                                            <td colspan="9" class="text-end totalcustomer-sub-head">
                                                                <strong>SGST Amt</strong>
                                                            </td>
                                                            <td>0.00</td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="9" class="text-end totalcustomer-sub-head">
                                                                <strong>CGST amt</strong>
                                                            </td>
                                                            <td>0.00</td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="9" class="text-end totalcustomer-sub-head">
                                                                <strong>IGST Amt</strong>
                                                            </td>
                                                            <td>0.00</td>
                                                            <td></td>
                                                        </tr>

                                                        <tr>
                                                            <td colspan="9" class="text-end totalcustomer-sub-head">
                                                                <strong>Discount</strong>
                                                            </td>


                                                            <td>0.00</td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="9" class="text-end totalcustomer-sub-head">
                                                                <strong>Tax</strong>
                                                            </td>
                                                            <td>0.00</td>
                                                            <td></td>
                                                        </tr>
                                                        <tr class="voucher-tab-foot">
                                                            <td colspan="9" class="text-end text-primary"><strong>Total
                                                                    Amt.</strong></td>
                                                            <td>
                                                                <div class="quottotal-bg">
                                                                    <h5>0.00</h5>
                                                                </div>
                                                            </td>
                                                            <td colspan="3"></td>
                                                        </tr>


                                                    </tbody>


                                                </table>
                                            </div>


                                            <div class="row mt-2">


                                                <div class="col-md-12">
                                                    <div class="mb-1">
                                                        <label class="form-label">Final Remarks <span
                                                                class="text-danger">*</span></label>
                                                        <textarea type="text" rows="4" class="form-control" placeholder="Enter Remarks here..." disabled>VORANOL 3595 POLYOL</textarea>

                                                    </div>
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


@section('modals')
    <div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select
                            Pending PO</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">PO No. <span class="text-danger">*</span></label>
                                <select class="form-select">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Code <span class="text-danger">*</span></label>
                                <select class="form-select">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                                <select class="form-select">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <select class="form-select">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="col  mb-1">
                            <label class="form-label">&nbsp;</label><br />
                            <button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                        </div>

                        <div class="col-md-12">


                            <div class="table-responsive">
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                    <thead>
                                        <tr>
                                            <th>
                                                <div class="form-check form-check-inline me-0">
                                                    <input class="form-check-input" type="checkbox" name="podetail"
                                                        id="inlineCheckbox1">
                                                </div>
                                            </th>
                                            <th>PO No.</th>
                                            <th>PO Date</th>
                                            <th>Vendor Code</th>
                                            <th>Vendor Name</th>
                                            <th>Product Specification</th>
                                            <th>Order Qty</th>
                                            <th>Balance Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="form-check form-check-inline me-0">
                                                    <input class="form-check-input" type="checkbox" name="podetail"
                                                        id="inlineCheckbox1">
                                                </div>
                                            </td>
                                            <td>2901</td>
                                            <td>10-04-2023</td>
                                            <td class="fw-bolder text-dark">8765</td>
                                            <td>DOW CHECMICAL (AUSTRALIA)</td>
                                            <td>SPRINGTEK Coir Bond 5 Mattress</td>
                                            <td>200</td>
                                            <td>100</td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <div class="form-check form-check-inline me-0">
                                                    <input class="form-check-input" type="checkbox" name="podetail"
                                                        id="inlineCheckbox1">
                                                </div>
                                            </td>
                                            <td>3312</td>
                                            <td>10-04-2023</td>
                                            <td class="fw-bolder text-dark">4576</td>
                                            <td>DOW CHECMICAL (AUSTRALIA)</td>
                                            <td>2inch Double Coir Sofa</td>
                                            <td>20</td>
                                            <td>15</td>
                                        </tr>





                                    </tbody>


                                </table>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                            data-feather="x-circle"></i> Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i>
                        Process</button>
                </div>
            </div>
        </div>
    </div>
@endsection
