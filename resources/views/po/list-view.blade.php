@extends('layouts.app')

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
                            <h2 class="content-header-title float-start mb-0">PO</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>  
                                    <li class="breadcrumb-item active">PO</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
                    <div class="form-group breadcrumb-right"> 
                        <a class="btn btn-primary btn-sm" href="create-po.html"><i data-feather="plus-circle"></i>  Create PO</a> 
                    </div>
                </div>
            </div>
            <div class="content-body">
                 
                
                
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                
                                   
                                <div class="table-responsive">
                                    <table class="datatables-basic table myrequesttablecbox "> 
                                        <thead>
                                             <tr>
                                                <th>#</th>
                                                <th>PO No.</th>
                                                <th>Item</th>
                                                <th>Customer Name</th>
                                                <th>Email</th>
                                                <th>Phone No.</th>
                                                <th>Amount</th>
                                                <th>Delivery Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                              </tr>
                                            </thead>
                                            <tbody>
                                                 <tr>
                                                    <td>1</td>
                                                    <td class="fw-bolder text-dark">POO019</td>
                                                    <td><span class="badge rounded-pill badge-light-secondary badgeborder-radius">4</span></td>
                                                    <td>L&T Infotech</td>
                                                    <td>sample@gmail.com</td>
                                                    <td>9876545678</td>
                                                    <td>Rs 2000.00</td>
                                                    <td>20-03-2022</td>
                                                    <td><span class="badge rounded-pill badge-light-success badgeborder-radius">Close</span></td>
                                                    <td class="tableactionnew">
                                                        <div class="dropdown">
                                                            <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                                                <i data-feather="more-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="edit" class="me-50"></i>
                                                                    <span>View Detail</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="edit-3" class="me-50"></i>
                                                                    <span>Edit</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="trash-2" class="me-50"></i>
                                                                    <span>Delete</span>
                                                                </a> 
                                                            </div>
                                                        </div>
                                                    </td>
                                                  </tr>
                                                  <tr>
                                                    <td>2</td>
                                                    <td class="fw-bolder text-dark">POO019</td>
                                                    <td><span class="badge rounded-pill badge-light-secondary badgeborder-radius">4</span></td>
                                                    <td>L&T Infotech</td>
                                                    <td>sample@gmail.com</td>
                                                    <td>9876545678</td>
                                                    <td>Rs 2000.00</td>
                                                    <td>20-03-2022</td>
                                                    <td><span class="badge rounded-pill badge-light-danger badgeborder-radius">Open</span></td>
                                                    <td class="tableactionnew">
                                                        <div class="dropdown">
                                                            <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                                                <i data-feather="more-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="edit" class="me-50"></i>
                                                                    <span>View Detail</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="edit-3" class="me-50"></i>
                                                                    <span>Edit</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="trash-2" class="me-50"></i>
                                                                    <span>Delete</span>
                                                                </a> 
                                                            </div>
                                                        </div>
                                                    </td>
                                                  </tr>
                                                  <tr>
                                                    <td>3</td>
                                                    <td class="fw-bolder text-dark">POO019</td>
                                                    <td><span class="badge rounded-pill badge-light-secondary badgeborder-radius">4</span></td>
                                                    <td>L&T Infotech</td>
                                                    <td>sample@gmail.com</td>
                                                    <td>9876545678</td>
                                                    <td>Rs 2000.00</td>
                                                    <td>20-03-2022</td>
                                                    <td><span class="badge rounded-pill badge-light-danger badgeborder-radius">Open</span></td>
                                                    <td class="tableactionnew">
                                                        <div class="dropdown">
                                                            <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                                                <i data-feather="more-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="edit" class="me-50"></i>
                                                                    <span>View Detail</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="edit-3" class="me-50"></i>
                                                                    <span>Edit</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="trash-2" class="me-50"></i>
                                                                    <span>Delete</span>
                                                                </a> 
                                                            </div>
                                                        </div>
                                                    </td>
                                                  </tr>
                                                  <tr>
                                                    <td>4</td>
                                                    <td class="fw-bolder text-dark">POO019</td>
                                                    <td><span class="badge rounded-pill badge-light-secondary badgeborder-radius">4</span></td>
                                                    <td>L&T Infotech</td>
                                                    <td>sample@gmail.com</td>
                                                    <td>9876545678</td>
                                                    <td>Rs 2000.00</td>
                                                    <td>20-03-2022</td>
                                                    <td><span class="badge rounded-pill badge-light-success badgeborder-radius">Close</span></td>
                                                    <td class="tableactionnew">
                                                        <div class="dropdown">
                                                            <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                                                <i data-feather="more-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="edit" class="me-50"></i>
                                                                    <span>View Detail</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="edit-3" class="me-50"></i>
                                                                    <span>Edit</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="trash-2" class="me-50"></i>
                                                                    <span>Delete</span>
                                                                </a> 
                                                            </div>
                                                        </div>
                                                    </td>
                                                  </tr>
                                                  <tr>
                                                    <td>5</td>
                                                    <td class="fw-bolder text-dark">POO019</td>
                                                    <td><span class="badge rounded-pill badge-light-secondary badgeborder-radius">4</span></td>
                                                    <td>L&T Infotech</td>
                                                    <td>sample@gmail.com</td>
                                                    <td>9876545678</td>
                                                    <td>Rs 2000.00</td>
                                                    <td>20-03-2022</td>
                                                    <td><span class="badge rounded-pill badge-light-success badgeborder-radius">Close</span></td>
                                                    <td class="tableactionnew">
                                                        <div class="dropdown">
                                                            <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                                                <i data-feather="more-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="edit" class="me-50"></i>
                                                                    <span>View Detail</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="edit-3" class="me-50"></i>
                                                                    <span>Edit</span>
                                                                </a>
                                                                <a class="dropdown-item" href="#">
                                                                    <i data-feather="trash-2" class="me-50"></i>
                                                                    <span>Delete</span>
                                                                </a> 
                                                            </div>
                                                        </div>
                                                    </td>
                                                  </tr>
                                               </tbody>


                                    </table>
                                </div>
                                
                                
                                
                                
                                
                            </div>
                        </div>
                    </div>
                    <!-- Modal to add new record -->
                    <div class="modal modal-slide-in fade" id="modals-slide-in">
                        <div class="modal-dialog sidebar-sm">
                            <form class="add-new-record modal-content pt-0">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                                <div class="modal-header mb-1">
                                    <h5 class="modal-title" id="exampleModalLabel">New Record</h5>
                                </div>
                                <div class="modal-body flex-grow-1">
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-fullname">Full Name</label>
                                        <input type="text" class="form-control dt-full-name" id="basic-icon-default-fullname" placeholder="John Doe" aria-label="John Doe" />
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-post">Post</label>
                                        <input type="text" id="basic-icon-default-post" class="form-control dt-post" placeholder="Web Developer" aria-label="Web Developer" />
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-email">Email</label>
                                        <input type="text" id="basic-icon-default-email" class="form-control dt-email" placeholder="john.doe@example.com" aria-label="john.doe@example.com" />
                                        <small class="form-text"> You can use letters, numbers & periods </small>
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-date">Joining Date</label>
                                        <input type="text" class="form-control dt-date" id="basic-icon-default-date" placeholder="MM/DD/YYYY" aria-label="MM/DD/YYYY" />
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label" for="basic-icon-default-salary">Salary</label>
                                        <input type="text" id="basic-icon-default-salary" class="form-control dt-salary" placeholder="$12000" aria-label="$12000" />
                                    </div>
                                    <button type="button" class="btn btn-primary data-submit me-1">Submit</button>
                                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
                 

            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection
