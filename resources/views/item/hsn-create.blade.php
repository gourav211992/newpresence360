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
								<h2 class="content-header-title float-start mb-0">New HSN/SAC</h2>
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
						
					</div>
				</div>
			</div>
            <div class="content-body">
                 
                
				
<section id="basic-datatable">
    <div class="row">
        <div class="col-12">  
            <div class="card">
                <div class="card-body customernewsection-form"> 
                      @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-12">
                            <div class="newheader border-bottom mb-2 pb-25"> 
                                <h4 class="card-title text-theme">Basic Information</h4>
                                <p class="card-text">Fill the details</p> 
                            </div>
                        </div> 
                        <div class="col-md-9"> 
                            <form action="{{ route('hsn-codes.store') }}" method="POST">
                                @csrf
                                <div class="row align-items-center mb-1"> 
                                    <div class="col-md-3"> 
                                        <label class="form-label">Code Type</label>  
                                    </div> 
                                    <div class="col-md-5"> 
                                        <div class="demo-inline-spacing">
                                            <div class="form-check form-check-primary mt-25">
                                                <input type="radio" id="customColorRadio1" name="code_type" value="HSN" class="form-check-input" checked="">
                                                <label class="form-check-label fw-bolder" for="customColorRadio1">HSN</label>
                                            </div> 
                                            <div class="form-check form-check-primary mt-25">
                                                <input type="radio" id="customColorRadio2" name="code_type" value="SAC" class="form-check-input">
                                                <label class="form-check-label fw-bolder" for="customColorRadio2">SAC</label>
                                            </div> 
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-1">
                                    <div class="col-md-3"> 
                                        <label class="form-label">HSN/SAC Code</label>  
                                    </div>  
                                    <div class="col-md-5"> 
                                        <input type="text" name="hsn_sac_code" class="form-control" value="{{$nextCode}}" />
                                    </div> 
                                </div>
                                <div class="row align-items-center mb-1"> 
                                    <div class="col-md-3"> 
                                        <label class="form-label">Tax %</label>  
                                    </div> 
                                    <div class="col-md-5">  
                                        <select name="tax" class="form-select">
                                            <option>Select</option>
                                            <option value="0">0</option> 
                                            <option value="3">3</option> 
                                            <option value="6">6</option> 
                                            <option value="9">9</option> 
                                            <option value="12">12</option> 
                                            <option value="18">18</option> 
                                            <option value="28">28</option> 
                                        </select>  
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-md-3"> 
                                        <label class="form-label">Description</label>  
                                    </div>  
                                    <div class="col-md-5"> 
                                        <textarea name="description" class="form-control"></textarea>
                                    </div> 
                                </div>
                                <div class="row align-items-center mb-1"> 
                                    <div class="col-md-3"> 
                                        <label class="form-label">Status</label>  
                                    </div> 
                                    <div class="col-md-5"> 
                                        <div class="demo-inline-spacing">
                                            <div class="form-check form-check-primary mt-25">
                                                <input type="radio" id="customColorRadio3" name="status" value="1" class="form-check-input" checked="">
                                                <label class="form-check-label fw-bolder" for="customColorRadio3">Active</label>
                                            </div> 
                                            <div class="form-check form-check-primary mt-25">
                                                <input type="radio" id="customColorRadio4" name="status" value="0" class="form-check-input">
                                                <label class="form-check-label fw-bolder" for="customColorRadio4">Inactive</label>
                                            </div> 
                                        </div> 
                                    </div>
                                </div>
                                <div class="mt-3">   
                                    <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</button>  
                                    <button type="submit" class="btn btn-primary btn-sm ms-1"><i data-feather="check-circle"></i> Create</button> 
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

   
@endsection